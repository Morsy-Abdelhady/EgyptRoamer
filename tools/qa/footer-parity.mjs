// Global footer parity across the 8 languages, from the rendered DOM.
// For each English page, its 7 translations (from the page's hreflang links) must render the same
// footer: same columns in the same order, the same links (by target) in the same order, the same legal
// row, every link in the page's own language, and no English text where a translation exists.
//
//   BASE=http://127.0.0.1:8080 node footer-parity.mjs [/path/ …]      (exit 1 on any failure)
//   GAP=3000 for production (milliseconds between page loads; stop on a 429 or a challenge)
import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const B = (process.env.BASE || "http://127.0.0.1:8080").replace(/\/$/, "");
const GAP = +(process.env.GAP || 0);
const LANGS = ["ar", "de", "fr", "it", "es", "ru", "zh"];
const LOCALE = { ar: "ar", de: "de_DE", fr: "fr_FR", it: "it_IT", es: "es_ES", ru: "ru_RU", zh: "zh_CN" };
const PAGES = process.argv.slice(2).length ? process.argv.slice(2) : ["/", "/destinations/", "/experiences/", "/contact/", "/privacy-policy/", "/cookies/", "/affiliate-disclosure/", "@destination", "@experience"];

// The theme's translations: English text that is legitimately identical in a language (fr "Contact") passes.
const theme = process.env.THEME_LANG || path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../../wordpress/wp-content/themes/egypt-roamer/languages");
const dict = {};
for (const l of LANGS) {
  dict[l] = {};
  const src = fs.readFileSync(path.join(theme, `${LOCALE[l]}.l10n.php`), "utf8");
  for (const m of src.matchAll(/^\t\t'((?:[^'\\]|\\.)*)' => '((?:[^'\\]|\\.)*)',$/gm)) dict[l][m[1].replace(/\\'/g, "'")] = m[2].replace(/\\'/g, "'");
}

// A link's target independent of language: /ar/contact-ar/ → contact, /de/destinations/ → destinations, /fr/#planner → #planner.
const key = (href) => {
  if (!href.startsWith("/")) return href;
  const [p, frag] = href.split("#");
  let s = p.replace(/^\/(ar|de|fr|it|es|ru|zh)(\/|$)/, "/").replace(/-(ar|de|fr|it|es|ru|zh)\/$/, "/");
  return s + (frag !== undefined ? "#" + frag : "");
};

const br = await chromium.launch({ channel: "chrome" });
const ctx = await br.newContext({ viewport: { width: 1440, height: 900 }, serviceWorkers: "block" });
const wait = () => GAP && new Promise((r) => setTimeout(r, GAP));

async function footer(url) {
  const p = await ctx.newPage();
  const r = await p.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
  if (r.status() === 429 || r.headers()["cf-mitigated"]) {
    console.error(`STOP: ${r.status()} ${r.headers()["cf-mitigated"] || ""} at ${url}`);
    process.exit(2);
  }
  const sig = await p.evaluate(() => {
    const f = document.querySelector("footer.footer");
    if (!f) return null;
    const txt = (e) => (e ? e.textContent.replace(/\s+/g, " ").trim() : "");
    const links = (root) => [...root.querySelectorAll("a[href]")].map((a) => ({ t: txt(a), h: a.getAttribute("href").replace(location.origin, ""), hl: a.getAttribute("hreflang") || "" }));
    return {
      lang: document.documentElement.lang, dir: document.documentElement.dir || "ltr",
      colsDir: getComputedStyle(f.querySelector(".footer__cols") || f).direction,
      alternates: Object.fromEntries([...document.querySelectorAll('link[rel="alternate"][hreflang]')].map((l) => [l.hreflang, l.href])),
      cols: [...f.querySelectorAll(".footer__cols > [data-footer-col]")].map((d) => ({ key: d.dataset.footerCol, h: txt(d.querySelector("h3")), links: links(d) })),
      legal: f.querySelector(".footer__legal") ? links(f.querySelector(".footer__legal")) : [],
      letter: { eyebrow: txt(f.querySelector(".footer__letter .eyebrow")), form: !!f.querySelector("form.subscribe"), note: f.querySelector(".subscribe__note a")?.getAttribute("href")?.replace(location.origin, "") || "" },
      logo: !!f.querySelector(".footer__logo"),
      copyright: txt(f.querySelector(".footer__bottom > p")),
      firstLink: document.querySelector("a.dest-card, .card a[href*='/destinations/'], a[href*='/destinations/'][class*='card']")?.href || "",
      firstExp: document.querySelector("a[href*='/experiences/'][class*='card'], .card a[href*='/experiences/']")?.href || "",
    };
  });
  await p.close();
  await wait();
  return sig && { ...sig, status: r.status(), url };
}

let fail = 0;
const rows = [];
const problem = (page, l, msg) => { fail++; console.log(`FAIL ${page} [${l}] ${msg}`); };
const pick = async (archive, field) => {
  const s = await footer(B + archive);
  const u = s?.[field];
  return u ? new URL(u).pathname : null;
};

for (let page of PAGES) {
  if (page === "@destination") page = await pick("/destinations/", "firstLink");
  if (page === "@experience") page = await pick("/experiences/", "firstExp");
  if (!page) continue;
  const en = await footer(B + page);
  if (!en) { problem(page, "en", "no footer"); continue; }
  const enCols = en.cols.map((c) => c.key).join(",");
  const row = { page, en: `${en.cols.length} cols/${en.cols.reduce((n, c) => n + c.links.length, 0)}+${en.legal.length} links` };
  for (const l of LANGS) {
    const alt = en.alternates[l] || en.alternates[LOCALE[l].replace("_", "-")] || Object.entries(en.alternates).find(([k]) => k.startsWith(l))?.[1];
    if (!alt) { problem(page, l, "no hreflang alternate on the English page"); row[l] = "–"; continue; }
    const s = await footer(alt);
    if (!s) { problem(page, l, "no footer"); row[l] = "✗"; continue; }
    const before = fail;
    if (s.status !== 200) problem(page, l, `HTTP ${s.status}`);
    if (!s.lang.startsWith(l)) problem(page, l, `html lang=${s.lang}`);
    if (l === "ar" && (s.dir !== "rtl" || s.colsDir !== "rtl")) problem(page, l, `not RTL (dir=${s.dir}, footer=${s.colsDir})`);
    // Columns: same set and order.
    const cols = s.cols.map((c) => c.key).join(",");
    if (cols !== enCols) problem(page, l, `columns [${cols}] ≠ English [${enCols}]`);
    // Links per column and the legal row: same targets in the same order, localized.
    const groups = [...en.cols.map((c) => [c.key, c.links, s.cols.find((x) => x.key === c.key)?.links || []]), ["legal", en.legal, s.legal]];
    for (const [g, a, b] of groups) {
      const ka = a.map((x) => key(x.h)).join(" "), kb = b.map((x) => key(x.h)).join(" ");
      if (ka !== kb) problem(page, l, `${g}: links [${kb}] ≠ English [${ka}]`);
      b.forEach((x, i) => {
        if (x.h.startsWith("/") && !x.h.startsWith(`/${l}/`)) problem(page, l, `${g}: "${x.t}" → ${x.h} is not in /${l}/`);
        if (x.hl && x.hl !== l) problem(page, l, `${g}: "${x.t}" links a ${x.hl} page (English fallback)`);
        const e = a[i];
        if (e && x.t === e.t && dict[l][e.t] !== x.t) problem(page, l, `${g}: "${x.t}" is still English`);
      });
    }
    // Headings, newsletter, copyright, logo.
    s.cols.forEach((c) => { const e = en.cols.find((x) => x.key === c.key); if (e && c.h === e.h && dict[l][e.h] !== c.h) problem(page, l, `heading "${c.h}" is still English`); });
    if (!s.logo) problem(page, l, "no logo");
    if (s.letter.form !== en.letter.form) problem(page, l, "newsletter form differs");
    if (en.letter.note && key(s.letter.note) !== key(en.letter.note)) problem(page, l, `newsletter privacy link ${s.letter.note}`);
    if (s.letter.note && !s.letter.note.startsWith(`/${l}/`)) problem(page, l, `newsletter privacy link not in /${l}/: ${s.letter.note}`);
    if (s.copyright === en.copyright) problem(page, l, "copyright is still English");
    row[l] = fail === before ? "✓" : "✗";
  }
  rows.push(row);
}
await br.close();
console.log("\npage".padEnd(34) + "en".padEnd(22) + LANGS.map((l) => l.padEnd(4)).join(""));
for (const r of rows) console.log(r.page.padEnd(33) + r.en.padEnd(22) + LANGS.map((l) => (r[l] || "").padEnd(4)).join(""));
console.log(fail ? `\n${fail} failure(s)` : "\nfooter parity: pass");
process.exit(fail ? 1 : 0);
