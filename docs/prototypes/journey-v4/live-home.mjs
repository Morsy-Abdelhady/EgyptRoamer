// Owner, 17 Oct: "review the production content and see what was forgotten or removed". The prototype's homepage had
// dropped five production sections: Seven places (#destinations), the partner comparison (#partners), Moments we'd
// book again (#experiences), the Roamer Journal (#guide) and the Trip Builder (#planner). This captures their
// markup, the CSS rules they use, their icons, the page's data (window.ER_DATA) and every photo they show, so the
// prototype can run the theme's own components on them (see v4-home2.py). Read-only.
import fs from "node:fs";
import crypto from "node:crypto";
import { chromium } from "playwright-core";
const IDS = ["#destinations", "#partners", "#experiences", "#guide", "#planner"];
const br = await chromium.launch({ channel: "chrome" });
const pg = await (await br.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
await pg.goto("https://egyptroamer.com/", { waitUntil: "networkidle", timeout: 90000 });
for (const id of IDS) { await pg.evaluate(s => document.querySelector(s).scrollIntoView(), id); await pg.waitForTimeout(900); }
const html = await pg.evaluate(ids => Object.fromEntries(ids.map(s => [s, document.querySelector(s).outerHTML]).concat([["toast", document.querySelector("[data-toast]")?.outerHTML || ""]])), IDS);
const data = await pg.evaluate(() => window.ER_DATA);
const css = await pg.evaluate((ids) => {
  const scope = ids.map(s => document.querySelector(s));
  const strip = s => s.replace(/::?(hover|focus-visible|focus-within|focus|active|before|after|placeholder|marker|first-letter|first-line|-webkit-[\w-]+)(\([^)]*\))?/g, "").replace(/:not\(\)/g, "").replace(/\[(?:data-state|aria-[\w-]+|dir)(?:[~|^$*]?=[^\]]*)?\]/g, "").replace(/\.is-[\w-]+/g, "") || "*";
  const hits = sel => sel.split(",").some(p => { const q = strip(p.trim()); try { return scope.some(r => r.matches(q) || r.querySelector(q)); } catch { return false; } });
  const out = [], keys = new Set();
  const walk = (rules, wrap) => { for (const r of rules) {
    if (r.type === 1) { if (!/(^|[\s,>+~(])(html|body|:root)(?![\w-])|\[dir/.test(r.selectorText) && hits(r.selectorText)) out.push([wrap, r.selectorText, r.style.cssText]); }
    else if (r.type === 4) walk(r.cssRules, r.conditionText || r.media.mediaText);
    else if (r.type === 7) keys.add(r.cssText);
    else if (r.cssRules) walk(r.cssRules, wrap);
  } };
  for (const s of document.styleSheets) { try { walk(s.cssRules, null); } catch {} }
  return { rules: out, keyframes: [...keys] };
}, IDS);
const sprite = await pg.evaluate(() => [...document.querySelectorAll("svg symbol")].map(s => s.outerHTML).join(""));
await br.close();

// photos: every Unsplash id in the data, and every Unsplash URL in the markup, saved once under img/
fs.mkdirSync("img", { recursive: true });
const ids = new Set();
JSON.stringify(data).replace(/"(\d{10,13}-[0-9a-f]{12})(?::\w+)?"/g, (m, id) => { ids.add(id); return m; });
let htmlAll = JSON.stringify(html);
(htmlAll.match(/https:\/\/images\.unsplash\.com\/photo-(\d{10,13}-[0-9a-f]{12})[^"'\s)\\]*/g) || []).forEach(u => ids.add(u.match(/photo-(\d{10,13}-[0-9a-f]{12})/)[1]));
let kb = 0;
for (const id of ids) {
  const f = `img/${id}.jpg`;
  if (!fs.existsSync(f)) { const r = await fetch(`https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=1000&q=62&fm=jpg`); const b = Buffer.from(await r.arrayBuffer()); fs.writeFileSync(f, b); }
  kb += fs.statSync(f).size / 1024;
}
htmlAll = htmlAll.replace(/https:\/\/images\.unsplash\.com\/photo-(\d{10,13}-[0-9a-f]{12})[^"'\s)\\]*/g, (m, id) => `img/${id}.jpg`);
fs.writeFileSync("live/home-sections.json", JSON.stringify({ html: JSON.parse(htmlAll), data, css, sprite }));
console.log("sections", Object.keys(html).length, "rules", css.rules.length, "photos", ids.size, Math.round(kb), "KB");
