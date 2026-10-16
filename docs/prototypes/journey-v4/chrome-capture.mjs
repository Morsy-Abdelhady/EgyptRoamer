// Captures the live site's chrome (owner, 16 Oct: the prototypes must have the search, favourites, language menu,
// Trip assistant and footer that production has): header, phone menu, search overlay, saved drawer, assistant launcher
// and drawer, footer, their CSS and icons. Read-only.
import fs from "node:fs";
import { chromium } from "playwright-core";
const br = await chromium.launch({ channel: "chrome" });
const pg = await (await br.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
await pg.goto("https://egyptroamer.com/", { waitUntil: "networkidle", timeout: 90000 });
const top = await pg.evaluate(() => document.querySelector("#nav").className);
await pg.evaluate(() => window.scrollTo(0, 1500)); await pg.waitForTimeout(1200);
const scrolled = await pg.evaluate(() => document.querySelector("#nav").className);
await pg.evaluate(() => window.scrollTo(0, 0)); await pg.waitForTimeout(800);

const html = await pg.evaluate(() => {
  const g = s => document.querySelector(s)?.outerHTML || "";
  return { nav: g("#nav"), menu: g("#menu"), search: g("#search"), saved: g("#saved"), launch: g(".assistant-launch"), assistant: g("#assistant"), footer: g("footer.footer") };
});

// CSS: every state on, so rules for open overlays and the scrolled header match
const css = await pg.evaluate((scrolledCls) => {
  const els = ["#nav", "#menu", "#search", "#saved", ".assistant-launch", "#assistant", "footer.footer"].map(s => document.querySelector(s));
  scrolledCls.split(" ").forEach(c => c && document.querySelector("#nav").classList.add(c));
  ["#menu", "#search", "#saved", "#assistant", ".lang"].forEach(s => { const e = document.querySelector(s); e.classList.add("is-open"); e.hidden = false; });
  const strip = s => s.replace(/::?(hover|focus-visible|focus-within|focus|active|before|after|placeholder|marker|first-letter|first-line|-webkit-[\w-]+)(\([^)]*\))?/g, "").replace(/:not\(\)/g, "").replace(/\[(?:data-state|aria-[\w-]+|dir)(?:[~|^$*]?=[^\]]*)?\]/g, "") || "*";
  const hits = sel => sel.split(",").some(p => { const q = strip(p.trim()); try { return els.some(r => r && (r.matches(q) || r.querySelector(q))); } catch { return false; } });
  const out = [], keys = new Set();
  const walk = (rules, wrap) => { for (const r of rules) {
    if (r.type === 1) {
      if (/^:root\b/.test(r.selectorText)) out.push([wrap, ":root", r.style.cssText]);
      else if (!/(^|[\s,>+~(])(html|body)(?![\w-])|\[dir/.test(r.selectorText) && hits(r.selectorText)) out.push([wrap, r.selectorText, r.style.cssText]);
    } else if (r.type === 4) walk(r.cssRules, r.conditionText || r.media.mediaText);
    else if (r.type === 7) keys.add(r.cssText);
    else if (r.cssRules) walk(r.cssRules, wrap);
  } };
  for (const s of document.styleSheets) { try { walk(s.cssRules, null); } catch {} }
  return { rules: out, keyframes: [...keys] };
}, scrolled.replace(top, "").trim() || "is-scrolled");

const sprite = await pg.evaluate(() => {
  const ids = new Set([...document.querySelectorAll("#nav use, #menu use, #search use, #saved use, .assistant-launch use, #assistant use, footer use")].map(u => u.getAttribute("href")));
  return [...ids].map(h => document.querySelector(h)?.outerHTML || "").join("");
});
await br.close();

// images (logo and any photos) as data URIs
let all = JSON.stringify({ top, scrolled, html, css, sprite });
const urls = [...new Set(all.match(/https:\/\/(?:images\.unsplash\.com|egyptroamer\.com\/wp-content)[^"'\s)\\]+?\.(?:png|svg|jpe?g|webp)(?:\?[^"'\s)\\]*)?|https:\/\/images\.unsplash\.com\/[^"'\s)\\]+/g) || [])];
for (const u of urls) {
  const r = await fetch(u.replace(/&amp;/g, "&")); const b = Buffer.from(await r.arrayBuffer());
  const t = (r.headers.get("content-type") || "image/jpeg").split(";")[0];
  all = all.split(u).join(`data:${t};base64,` + b.toString("base64"));
}
fs.writeFileSync("live/chrome.json", all);
console.log("top", top, "| scrolled", scrolled, "| rules", css.rules.length, "| images", urls.length, "| KB", Math.round(all.length / 1024));
