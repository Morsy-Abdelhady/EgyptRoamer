// Static-site audit harness: console errors, overflow, i18n gaps, headings, links, images.
import { chromium } from "playwright";
import fs from "fs";

const BASE = process.env.BASE || "http://127.0.0.1:5173/index.html";
const LANGS = ["en", "de", "fr", "it", "es", "ru", "zh", "ar"];
const SIZES = [[390, 844], [430, 932], [768, 1024], [1024, 1366], [1440, 900], [1920, 1080]];
const out = [];
const browser = await chromium.launch();

for (const lang of LANGS) {
  for (const [w, h] of SIZES) {
    if (lang !== "en" && ![390, 1440].includes(w)) continue; // full matrix for EN, 2 sizes for others
    const page = await browser.newPage({ viewport: { width: w, height: h } });
    const errors = [];
    const failed = [];
    page.on("pageerror", (e) => errors.push(String(e)));
    page.on("console", (m) => m.type() === "error" && errors.push(m.text()));
    page.on("requestfailed", (r) => failed.push(new URL(r.url()).host));
    await page.goto(`${BASE}?lang=${lang}`, { waitUntil: "load" });
    await page.waitForTimeout(3800);
    // scroll through to trigger lazy content/reveals
    const H = await page.evaluate(() => document.documentElement.scrollHeight);
    for (let y = 0; y < H; y += h) { await page.evaluate((y) => window.scrollTo(0, y), y); await page.waitForTimeout(60); }
    const r = await page.evaluate(() => {
      const vw = document.documentElement.clientWidth;
      const over = [];
      document.querySelectorAll("body *").forEach((el) => {
        const b = el.getBoundingClientRect();
        if (b.width && (b.right > vw + 1 || b.left < -1)) {
          const cs = getComputedStyle(el);
          if (cs.position === "fixed" || el.closest("[hidden],.rail-scroller,.dest__index,.moods__dial,.partners__tabs,.finder__tabs,.guide__cats,.exp__filters,svg,.scene,.journey")) return;
          over.push(el.tagName.toLowerCase() + "." + [...el.classList].join("."));
        }
      });
      const localImgs = [...document.images].filter((i) => !i.src.includes("unsplash"));
      return {
        scrollW: document.documentElement.scrollWidth, vw,
        overflow: [...new Set(over)].slice(0, 8),
        missing: [...(window.__i18nMissing || [])],
        h1: document.querySelectorAll("h1").length,
        title: document.title, htmlLang: document.documentElement.lang, dir: document.dir,
        brokenLocalImg: localImgs.filter((i) => i.complete && i.naturalWidth === 0).map((i) => i.src),
        imgNoAlt: [...document.images].filter((i) => !i.hasAttribute("alt")).length,
        links: [...document.querySelectorAll("a[href]")].map((a) => a.getAttribute("href")),
        sponsored: [...document.querySelectorAll("a[data-affiliate],a[href='#partner']")].length,
      };
    });
    out.push({ lang, w, errors: [...new Set(errors)].slice(0, 6), failedHosts: [...new Set(failed)], ...r });
    if (lang === "en" || w === 390) await page.screenshot({ path: `shot-${lang}-${w}.png` });
    await page.close();
  }
}
await browser.close();
fs.writeFileSync("result.json", JSON.stringify(out, null, 1));
for (const o of out) {
  console.log(`${o.lang} ${o.w}: scrollW=${o.scrollW}/${o.vw} h1=${o.h1} dir=${o.dir} lang=${o.htmlLang} err=${o.errors.length} missing=${o.missing.length} overflow=${o.overflow.length} brokenLocal=${o.brokenLocalImg.length} noAlt=${o.imgNoAlt}`);
  if (o.errors.length) console.log("   errors:", o.errors.slice(0, 3));
  if (o.missing.length) console.log("   missing:", o.missing.slice(0, 10));
  if (o.overflow.length) console.log("   overflow:", o.overflow);
}
const links = [...new Set(out[0].links)].sort();
console.log("\nEN links:", links.join("  "));
console.log("failed hosts:", [...new Set(out.flatMap((o) => o.failedHosts))]);
