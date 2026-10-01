// Trip assistant + chat, rendered in all 8 languages: every visible state's text is translated (not the English
// source), nothing overflows or is clipped (320 and 1440 px), accessible names are translated, Arabic is RTL.
// States are reached through the real UI; team-side states (with the team, back to AI, closed) are set on the
// server with WP-CLI. Screenshots: chat-i18n-<lang>-<w>-<state>.png.
import { chromium } from "playwright";
import { execFileSync } from "child_process";
const B = process.env.BASE || "http://127.0.0.1:8080";
const WP = process.env.WPCLI || "C:/Users/morsy/er/wp.sh";
const LANGS = (process.env.LANGS || "en,ar,de,fr,it,es,ru,zh").split(",");
const br = await chromium.launch({ channel: "chrome" });
const bad = [];
const setStatus = (pid, status) => execFileSync("sh", [WP, "eval", `global $wpdb; $wpdb->update( er_chat_table( 'conversations' ), [ 'status' => '${status}' ], [ 'public_id' => '${pid}' ] );`], { stdio: "ignore" });

// The English texts of each state, collected first, to detect untranslated text in the other languages.
const english = {};
const paths = { en: "/destinations/", ar: "/ar/destinations/", de: "/de/destinations/", fr: "/fr/destinations/", it: "/it/destinations/", es: "/es/destinations/", ru: "/ru/destinations/", zh: "/zh/destinations/" };

const snap = (p) =>
  p.evaluate(() => {
    const root = document.getElementById("assistant");
    const vis = (el) => el && !el.closest("[hidden]") && el.getClientRects().length;
    const texts = {};
    const pick = (k, sel) => {
      const el = root.querySelector(sel) || document.querySelector(sel);
      if (vis(el)) texts[k] = (el.textContent || el.placeholder || "").trim().replace(/\s+/g, " ");
    };
    pick("title", "#assistant-title");
    pick("label", "[data-assistant-label]");
    pick("avail", "[data-chat-avail]");
    pick("open", "[data-chat-open]");
    pick("submit", "[data-assistant-submit]");
    pick("note", "[data-assistant-note]");
    pick("status", "[data-chat-status]");
    pick("end", "[data-chat-end]");
    pick("human", "[data-chat-human]");
    pick("intro", ".assistant__start-intro");
    pick("start", "[data-chat-form] [type=submit]");
    pick("cancel", "[data-chat-cancel]");
    pick("failed", ".assistant__msg.is-failed .assistant__who");
    pick("retry", ".assistant__msg.is-failed button");
    const input = root.querySelector("#assistant-q");
    texts.labels = [...root.querySelectorAll("[data-chat-form] label")].filter(vis).map((l) => l.childNodes[0]?.textContent.trim()).join(" | ");
    texts.close = root.querySelector("[data-close]")?.getAttribute("aria-label");
    texts.launcher = (document.querySelector(".assistant-launch")?.textContent || "").trim();
    // Overflow / clipping of controls and text inside the drawer
    const clipped = [];
    for (const el of root.querySelectorAll("button, a, label, p, input, .assistant__chip, .assistant__msg")) {
      if (!vis(el) || el.closest(".visually-hidden, .er-hp")) continue; // clipped on purpose (screen-reader only, honeypot)
      if (el.scrollWidth > el.clientWidth + 2 && getComputedStyle(el).overflowX !== "visible") clipped.push((el.className || el.tagName) + ":" + (el.textContent || "").trim().slice(0, 20));
      const r = el.getBoundingClientRect();
      const panel = root.querySelector(".drawer__panel").getBoundingClientRect();
      if (r.right > panel.right + 1 || r.left < panel.left - 1) clipped.push("outside:" + (el.className || el.tagName) + ":" + (el.textContent || "").trim().slice(0, 20));
    }
    // Text contrast of the visible buttons against the drawer (axe reports blurred buttons only as "incomplete")
    // Any CSS colour format (rgb, oklab, color(srgb …)) → sRGB bytes and alpha, through a 1×1 canvas
    const cv = document.createElement("canvas").getContext("2d", { willReadFrequently: true });
    const rgba = (c) => { cv.clearRect(0, 0, 1, 1); cv.fillStyle = "#000"; cv.fillStyle = c; cv.fillRect(0, 0, 1, 1); return [...cv.getImageData(0, 0, 1, 1).data]; };
    const lum = (c) => { const [r, g, b] = rgba(c).slice(0, 3).map((v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4; }); return 0.2126 * r + 0.7152 * g + 0.0722 * b; };
    const bgOf = (el) => { for (let e = el; e; e = e.parentElement) { const c = getComputedStyle(e).backgroundColor; if (rgba(c)[3] > 128) return c; } return "rgb(255,255,255)"; };
    const lowContrast = [];
    for (const b of root.querySelectorAll("button, .btn")) {
      if (!vis(b) || b.closest(".visually-hidden, .er-hp")) continue;
      const L1 = lum(getComputedStyle(b).color), L2 = lum(bgOf(b));
      const ratio = (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05);
      if (ratio < 4.5) lowContrast.push(`${(b.textContent || b.getAttribute("aria-label") || "").trim().slice(0, 20)} ${ratio.toFixed(1)}:1`);
    }
    return { texts, clipped, lowContrast, dir: getComputedStyle(root).direction, overflow: document.documentElement.scrollWidth > innerWidth + 1 };
  });

for (const lang of LANGS) {
  for (const [w, h] of [[320, 700], [1440, 900]]) {
    const ctx = await br.newContext({ viewport: { width: w, height: h } });
    await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media", "font"].includes(r.request().resourceType()) ? r.abort() : r.continue()));
    const p = await ctx.newPage();
    await p.goto(B + paths[lang], { waitUntil: "load", timeout: 180000 });
    await p.waitForTimeout(1200);
    await p.click(".assistant-launch");
    await p.waitForSelector("#assistant:not([hidden]) [data-chat-open]");
    await p.waitForTimeout(2000);
    const states = {};
    states.assistant = await snap(p);
    await p.click("[data-chat-open]");
    states.form = await snap(p);
    // 16 conversations from one IP would hit the 10-per-hour limit on new chats: clear it for the test
    execFileSync("sh", [WP, "eval", "global $wpdb; $wpdb->query( \"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%er_ch_start%'\" );"], { stdio: "ignore" });
    await p.fill("[data-chat-form] [name=message]", `i18n QA ${lang} ${w}`);
    await p.waitForTimeout(1700);
    await p.click("[data-chat-form] [type=submit]");
    await p.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 60000 });
    await p.waitForTimeout(800);
    states.waiting = await snap(p);
    const pid = await p.evaluate(() => JSON.parse(localStorage.getItem("er-chat")).id);
    for (const st of ["human", "ai", "closed"]) {
      setStatus(pid, st);
      await p.waitForTimeout(4500); // next poll
      states[st] = await snap(p);
      if (w === 320) await p.screenshot({ path: `chat-i18n-${lang}-${w}-${st}.png` });
    }
    setStatus(pid, "human");
    await p.waitForTimeout(4000);
    await ctx.setOffline(true);
    await p.fill("#assistant-q", "offline");
    await p.press("#assistant-q", "Enter");
    await p.waitForSelector(".assistant__msg.is-failed", { timeout: 15000 }).catch(() => {});
    states.failed = await snap(p);
    if (w === 320) await p.screenshot({ path: `chat-i18n-${lang}-${w}-failed.png` });
    await ctx.setOffline(false);
    // checks
    const problems = [];
    for (const [state, s] of Object.entries(states)) {
      if (s.overflow) problems.push(`${state}: page overflow`);
      if (s.clipped.length) problems.push(`${state}: clipped ${[...new Set(s.clipped)].slice(0, 3).join(", ")}`);
      if (s.lowContrast.length) problems.push(`${state}: low contrast ${[...new Set(s.lowContrast)].join(", ")}`);
      if ((lang === "ar") !== (s.dir === "rtl")) problems.push(`${state}: dir ${s.dir}`);
      for (const [k, v] of Object.entries(s.texts)) {
        if (!v) continue;
        if (lang === "en") (english[`${state}.${k}`] ||= v);
        else if (english[`${state}.${k}`] && english[`${state}.${k}`] === v && !/^Egypt Roamer$/.test(v)) problems.push(`${state}.${k} untranslated: "${v.slice(0, 50)}"`);
      }
    }
    if (problems.length) bad.push(`${lang} ${w}`);
    console.log(`${problems.length ? "FAIL" : "ok  "} ${lang} ${w} | waiting: "${states.waiting.texts.status}" | human: "${states.human.texts.status}" | failed: "${states.failed.texts.failed}" / "${states.failed.texts.retry}"${problems.length ? "\n      " + problems.join("\n      ") : ""}`);
    await ctx.close();
  }
}
await br.close();
console.log(bad.length ? `FAILING: ${bad.join(", ")}` : `chat i18n: all ${LANGS.length * 2} ok`);
process.exit(bad.length ? 1 : 0);
