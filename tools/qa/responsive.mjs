// Responsive sweep: each page type (types.json: {type: {lang: url}}) × 8 languages, one load, viewport
// stepped through 320–1600: page/element overflow, clipped text, heading under the header, script errors.
//   node responsive.mjs   (IMAGES=1 to load photos; SHOT="type:lang:width,…" for full-page screenshots)
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const W = [320, 375, 390, 414, 480, 768, 834, 1024, 1280, 1440, 1600];
const LANGS = (process.env.LANGS || "en,ar,de,fr,it,es,ru,zh").split(",");
const T = JSON.parse(fs.readFileSync("types.json", "utf8")); // {type: {lang: url}}
const SHOT = new Set((process.env.SHOT || "").split(",")); // "type:lang:width"
fs.mkdirSync("shots", { recursive: true });
const br = await chromium.launch({ channel: "chrome" });
const res = [];
for (const [type, map] of Object.entries(T)) for (const l of LANGS) {
  const url = map[l]; if (!url) continue;
  const ctx = await br.newContext({ viewport: { width: 1440, height: 900 }, serviceWorkers: "block", reducedMotion: "reduce" });
  if (!process.env.IMAGES) await ctx.route(/^https?:\/\/(?!127\.0\.0\.1|egyptroamer\.com)/, (r) => ["image", "media"].includes(r.request().resourceType()) ? r.abort() : r.continue());
  const p = await ctx.newPage(); const errs = []; p.on("pageerror", (e) => errs.push(e.message));
  try { await p.goto(B + url, { waitUntil: "load", timeout: 90000 }); } catch (e) { res.push({ type, l, url, err: String(e).slice(0, 100) }); await ctx.close(); continue; }
  await p.waitForTimeout(2500); // homepage loader
  for (const w of W) {
    await p.setViewportSize({ width: w, height: w < 768 ? 800 : 900 });
    await p.waitForTimeout(250);
    const r = await p.evaluate(() => {
      const vw = document.documentElement.clientWidth;
      const docOver = document.documentElement.scrollWidth - vw;
      const vis = (e) => { const s = getComputedStyle(e); return s.visibility !== "hidden" && s.display !== "none" && +s.opacity !== 0; };
      const inScroller = (e) => { for (let a = e.parentElement; a && a !== document.body; a = a.parentElement) { const s = getComputedStyle(a); if (/(auto|scroll|hidden|clip)/.test(s.overflowX)) return true; } return false; };
      const over = [], clipped = [];
      for (const e of document.querySelectorAll("body *")) {
        if (e.closest("svg,[hidden],[aria-hidden=true],.loader,.overlay,.menu,.drawer,.toast,.visually-hidden,.skip-link,.lang__menu,script,style")) continue;
        const b = e.getBoundingClientRect(); if (!b.width || !b.height || !vis(e)) continue;
        if ((b.right > vw + 1 || b.left < -1) && !inScroller(e)) over.push((e.className?.toString() || e.tagName).slice(0, 40));
        const s = getComputedStyle(e);
        if (e.children.length === 0 && e.textContent.trim() && /(hidden|clip)/.test(s.overflowX) && e.scrollWidth > e.clientWidth + 2 && s.textOverflow !== "ellipsis") clipped.push((e.className?.toString() || e.tagName).slice(0, 30) + ":" + e.textContent.trim().slice(0, 20));
      }
      // Overlap: header/fixed elements vs main heading at scroll top; buttons overlapping text in cards.
      const h1 = document.querySelector("h1"), nav = document.querySelector("header.nav");
      let h1Under = false;
      if (h1 && nav && vis(h1)) { const a = h1.getBoundingClientRect(), n = nav.getBoundingClientRect(); h1Under = a.top < n.bottom - 4 && a.bottom > n.top && scrollY === 0 && a.top > 0 && getComputedStyle(h1).opacity !== "0"; }
      const footer = document.querySelector("footer.footer")?.getBoundingClientRect();
      return { docOver, over: [...new Set(over)].slice(0, 5), clipped: [...new Set(clipped)].slice(0, 5), h1Under, fh: footer ? Math.round(footer.height) : 0 };
    });
    res.push({ type, l, url, w, ...r, errs: errs.length });
    if (SHOT.has(`${type}:${l}:${w}`) || SHOT.has(`${type}:*:${w}`) || SHOT.has(`*:${l}:${w}`)) await p.screenshot({ path: `shots/${type}-${l}-${w}.png`, fullPage: true });
  }
  process.stdout.write(`${type} ${l}\n`);
  await ctx.close();
}
await br.close();
fs.writeFileSync(process.env.O || "resp.json", JSON.stringify(res));
const bad = res.filter((r) => r.err || r.docOver > 1 || r.over?.length || r.clipped?.length || r.h1Under || r.errs);
for (const r of bad) console.log(`BAD ${r.type} ${r.l} ${r.w} over=${r.docOver} ${r.over?.join(",") || ""} clip=${r.clipped?.join(",") || ""} h1Under=${r.h1Under} errs=${r.errs} ${r.err || ""}`);
console.log(`${res.length} checks, ${bad.length} bad`);
