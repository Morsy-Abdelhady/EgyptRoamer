// Display text clipped by its own section: every heading/display word, 8 languages × 320–1600, whose glyphs
// run past an ancestor that hides overflow (carousels and other scrollers excluded). The page-level overflow
// checks cannot see this: the section hides the overflow, so the page never scrolls sideways.
//   BASE=http://127.0.0.1:8080 node display-fit.mjs [/path/]     (default: the homepage of each language)
import { chromium } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const PATH = (process.argv[2] || "/").replace(/^\//, "");
const br = await chromium.launch({ channel: "chrome" });
const W = [320, 375, 390, 414, 480, 768, 834, 1024, 1280, 1440, 1600];
for (const l of ["en", "ar", "de", "fr", "it", "es", "ru", "zh"]) {
  const ctx = await br.newContext({ viewport: { width: 1440, height: 900 }, reducedMotion: "reduce" });
  await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => ["image", "media"].includes(r.request().resourceType()) ? r.abort() : r.continue());
  const p = await ctx.newPage();
  await p.goto(`${B}${l === "en" ? "/" : `/${l}/`}${PATH}`, { waitUntil: "load", timeout: 120000 }); await p.waitForTimeout(2500);
  const bad = [];
  for (const w of W) {
    await p.setViewportSize({ width: w, height: 850 }); await p.waitForTimeout(500);
    const r = await p.evaluate(() => {
      const vw = document.documentElement.clientWidth, out = [];
      // Every heading and display text on the page: does its text extend beyond the viewport or its own box?
      for (const e of document.querySelectorAll("h1,h2,h3,[class*=title],[class*=word],[class*=display]")) {
        const b = e.getBoundingClientRect(); if (!b.width || getComputedStyle(e).visibility === "hidden") continue;
        const range = document.createRange(); range.selectNodeContents(e); const t = range.getBoundingClientRect();
        let clipBox = { left: 0, right: vw }, scroller = false;
        for (let a = e.parentElement; a && a !== document.documentElement; a = a.parentElement) {
          const s = getComputedStyle(a);
          if (/(auto|scroll)/.test(s.overflowX)) { scroller = true; break; }
          if (/(hidden|clip)/.test(s.overflowX)) { const ab = a.getBoundingClientRect(); clipBox = { left: Math.max(clipBox.left, ab.left), right: Math.min(clipBox.right, ab.right) }; }
        }
        if (scroller || b.right < 0 || b.left > vw) continue;
        if (t.width && (t.right > clipBox.right + 1 || t.left < clipBox.left - 1)) out.push(`${e.tagName}.${String(e.className).slice(0, 25)} "${e.textContent.trim().slice(0, 18)}" text ${Math.round(t.left)}–${Math.round(t.right)} vw ${vw}`);
      }
      return out;
    });
    for (const x of r) bad.push(`${w}: ${x}`);
  }
  console.log(l, bad.length ? "\n  " + bad.join("\n  ") : "ok");
  await ctx.close();
}
await br.close();
