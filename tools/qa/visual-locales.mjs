// Full visual language audit (launch gate area 3, 2026-10-02): real screenshots for inspection by eye, every
// language × 320/390/768/1440. Per width: homepage (after the intro), homepage footer, a destination, an experience,
// mobile menu (where the menu button shows), Trip Assistant answer, Human Chat started, 404, search with no
// results; plus the loading state on a slow network (390 only).
// Output: sheets/_loc-<lang>-<w>-<state>.png and sheets/loc-<lang>-<w>.json (python sheets.py loc-<lang>-<w>).
// Usage: node visual-locales.mjs [langs] [widths]
import { chromium } from "playwright";
import { execFileSync } from "child_process";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const WP = process.env.WPCLI || "C:/Users/morsy/er/wp.sh";
const LANGS = (process.argv[2] || "en,ar,de,fr,it,es,ru,zh").split(",");
const WIDTHS = (process.argv[3] || "320,390,768,1440").split(",").map(Number);
const H = { 320: 700, 390: 844, 768: 1024, 1440: 900 };
const pfx = (l) => (l === "en" ? "" : l + "/");
fs.mkdirSync("sheets", { recursive: true });
const br = await chromium.launch({ channel: "chrome" });
const clearLimits = () => execFileSync("sh", [WP, "eval", "global $wpdb; $wpdb->query( \"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%er_ch_%'\" );"], { stdio: "ignore" });
const errors = [];

for (const l of LANGS) {
  clearLimits();
  // a destination and an experience in this language, from the archives
  const probe = await br.newPage();
  const firstLink = async (path, seg) => {
    await probe.goto(`${B}/${pfx(l)}${path}`, { waitUntil: "load", timeout: 180000 });
    return probe.evaluate((seg) => [...document.querySelectorAll("main a[href]")].map((a) => a.href).find((h) => { const p = decodeURI(new URL(h).pathname).split("/").filter(Boolean); const i = p.indexOf(seg); return i >= 0 && p.length === i + 2; }), seg);
  };
  const dest = await firstLink("destinations/", "destinations");
  const exp = await firstLink("experiences/", "experiences");
  await probe.close();
  for (const w of WIDTHS) {
    const files = [];
    const ctx = await br.newContext({ viewport: { width: w, height: H[w] || 900 } });
    const p = await ctx.newPage();
    p.on("pageerror", (e) => errors.push(`${l} ${w}: ${e.message}`));
    const shot = async (state) => { const f = `sheets/_loc-${l}-${w}-${state}.png`; await p.screenshot({ path: f }); files.push(f); };
    const go = async (url, ms = 1500) => { await p.goto(url, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(ms); };
    try {
      await go(`${B}/${pfx(l)}?vl=${Date.now()}`, 5500);
      await shot("1home");
      await p.evaluate(() => (window.__lenis ? window.__lenis.scrollTo(document.documentElement.scrollHeight, { immediate: true, force: true }) : scrollTo(0, document.documentElement.scrollHeight)));
      await p.waitForTimeout(2000);
      await shot("2footer");
      if (dest) { await go(dest); await shot("3destination"); } else errors.push(`${l}: no destination link`);
      if (exp) { await go(exp); await shot("4experience"); } else errors.push(`${l}: no experience link`);
      const burger = await p.$("[data-open=menu]");
      if (burger && (await burger.isVisible())) { await burger.click(); await p.waitForTimeout(1000); await shot("5menu"); await p.keyboard.press("Escape"); await p.waitForTimeout(600); }
      await go(`${B}/${pfx(l)}destinations/`);
      const launch = await p.$(".assistant-launch");
      if (!(await launch.isVisible())) { await p.evaluate(() => scrollTo(0, innerHeight)); await p.waitForTimeout(800); }
      await p.click(".assistant-launch"); await p.waitForTimeout(1200);
      await p.click("#assistant [data-assistant-ask]"); await p.waitForSelector("#assistant .assistant__a", { timeout: 60000 }).catch(() => {}); await p.waitForTimeout(600);
      await shot("6assistant");
      await p.click("[data-chat-open]"); await p.fill("[data-chat-form] [name=name]", "QA " + l);
      await p.fill("[data-chat-form] [name=message]", "Visual audit " + l + " " + w); await p.waitForTimeout(1700);
      await p.click("[data-chat-form] [type=submit]");
      await p.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 30000 }).catch(() => errors.push(`${l} ${w}: chat did not start`));
      await p.waitForTimeout(1200);
      await shot("7chat");
      await p.evaluate(() => localStorage.removeItem("er-chat"));
      await go(`${B}/${pfx(l)}no-such-page-xyz/`); await shot("8-404");
      await go(`${B}/${pfx(l)}?s=zzqxv`); await shot("9search-empty");
      if (w === 390) {
        const cdp = await ctx.newCDPSession(p);
        await cdp.send("Network.enable");
        await cdp.send("Network.emulateNetworkConditions", { offline: false, latency: 300, downloadThroughput: 90000, uploadThroughput: 40000 });
        p.goto(`${B}/${pfx(l)}?slow=${Date.now()}`, { waitUntil: "load", timeout: 180000 }).catch(() => {});
        await p.waitForTimeout(2500); await shot("0loading");
      }
    } catch (e) {
      errors.push(`${l} ${w}: ${e.message.split("\n")[0]}`);
    }
    await ctx.close();
    fs.writeFileSync(`sheets/loc-${l}-${w}.json`, JSON.stringify(files));
    console.log(l, w, files.length, "shots");
  }
}
await br.close();
console.log(errors.length ? "errors:\n" + errors.join("\n") : "no errors");
