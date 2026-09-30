// Analyse site-crawl.mjs output: HTTP, SEO/hreflang reciprocity, header/footer/switcher/dock parity against
// the English page, English leaks, wrong-language links, anchors, JS errors, axe, and every internal link's status.
//   BASE=http://127.0.0.1:8080 I=crawl.json node site-analyze.mjs   → issues.json + summary
import fs from "fs";
import { request } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const C = JSON.parse(fs.readFileSync(process.env.I || "crawl.json", "utf8"));
const LANGS = ["en", "ar", "de", "fr", "it", "es", "ru", "zh"];
const issues = [];
const add = (sev, comp, rec, msg) => issues.push({ sev, comp, lang: rec?.lang, url: rec?.url, msg });
const byUrl = Object.fromEntries(C.map((r) => [r.url, r]));
const key = (h) => (h || "").split("?")[0].replace(/^\/(ar|de|fr|it|es|ru|zh)(\/|$)/, "/").replace(/-(ar|de|fr|it|es|ru|zh)\/$/, "/").replace(/\/diario\/$/, "/journal/");
const isUtil = (r) => ["search", "search-empty", "404"].includes(r.type);

for (const r of C) {
  if (!r || r.status === 0) { add("P1", "crawl", r, "not loaded: " + r?.error); continue; }
  if (r.type === "404" ? r.status !== 404 : r.status !== 200) add("P1", "http", r, `status ${r.status}`);
  if (r.finalUrl !== r.url && !isUtil(r)) add("P2", "redirect", r, `redirected to ${r.finalUrl}`);
}
for (const r of C.filter((x) => x && x.status)) {
  const L = r.url.match(/^\/(ar|de|fr|it|es|ru|zh)(\/|\?|$)/)?.[1] || "en";
  r.L = L;
  if (!r.lang.startsWith(L)) add("P1", "lang", r, `html lang=${r.lang}`);
  if ((L === "ar") !== (r.dir === "rtl")) add("P1", "dir", r, `dir=${r.dir}`);
  // SEO
  if (!/noindex/.test(r.robots)) add("P0", "seo", r, `robots="${r.robots}" (indexing must stay off)`);
  if (!r.title) add("P2", "seo", r, "no <title>");
  if (!isUtil(r)) {
    if (!r.desc) add("P2", "seo", r, "no meta description");
    if (r.canonical !== r.url) add("P2", "seo", r, `canonical ${r.canonical}`);
    const hl = r.hreflang; const n = Object.keys(hl).length;
    if (r.type !== "tax:category" && n !== 9) add("P2", "seo", r, `hreflang count ${n}`);
    if (hl[r.lang] && hl[r.lang] !== r.url) add("P1", "seo", r, `self hreflang ${hl[r.lang]}`);
    for (const [code, href] of Object.entries(hl)) {
      const t = byUrl[href];
      if (!t) { add("P2", "seo", r, `hreflang ${code} → ${href} not in inventory`); continue; }
      if (code !== "x-default" && !t.lang.startsWith(code.slice(0, 2))) add("P1", "seo", r, `hreflang ${code} → ${href} is ${t.lang}`);
      if (code !== "x-default" && !Object.values(t.hreflang || {}).includes(r.url)) add("P1", "seo", r, `hreflang ${code} → ${href} not reciprocal`);
    }
    if (!r.og.title || !r.og.url) add("P3", "seo", r, "og:title/og:url missing");
    if (r.og.url && r.og.url !== r.url) add("P2", "seo", r, `og:url ${r.og.url}`);
    if (r.og.locale && !r.og.locale.toLowerCase().startsWith(L)) add("P2", "seo", r, `og:locale ${r.og.locale}`);
    if (r.ld.includes("PARSE-ERROR")) add("P1", "seo", r, "JSON-LD parse error");
    if (r.h1 !== 1) add("P2", "a11y", r, `${r.h1} h1`);
    if (r.skip) add("P3", "a11y", r, `${r.skip} heading level skip(s)`);
  }
  if (r.badAnchors.length) add("P2", "links", r, `in-page anchors without target: ${[...new Set(r.badAnchors)].join(",")}`);
  if (r.errs.length) add("P1", "js", r, "page errors: " + r.errs.join(" | "));
  const cons = r.cons.filter((c) => !c.includes("net::ERR_FAILED")); // photos blocked by the crawler
  if (cons.length) add("P3", "js", r, "console errors: " + [...new Set(cons)].slice(0, 2).join(" | "));
  if (r.overflow) add("P1", "layout", r, "horizontal overflow at 1440");
  for (const v of r.axe) add(v.impact === "critical" || v.impact === "serious" ? "P2" : "P3", "a11y", r, `axe ${v.id} ×${v.n} (${v.impact}) ${v.t}`);
  if (r.imgsNoAlt) add("P2", "a11y", r, `${r.imgsNoAlt} img without alt`);
  if (!r.landmarks.skip) add("P3", "a11y", r, "no skip link");
  // Locale correctness of every internal link
  for (const a of r.allLinks) {
    if (!a.h.startsWith("/") || a.h.startsWith("//")) continue;
    if (a.h.startsWith("/wp-") || a.h.startsWith("/go/")) continue;
    const inLang = L === "en" ? !/^\/(ar|de|fr|it|es|ru|zh)(\/|$)/.test(a.h) : a.h.startsWith(`/${L}/`) || a.h === `/${L}`;
    const inSwitcher = r.langSwitch.some((s) => s.h === a.h);
    if (!inLang && !inSwitcher && !a.hl) add("P1", "links", r, `wrong-language link "${a.t.slice(0, 40)}" → ${a.h}`);
  }
}
// Global component parity against the English page of the same group.
const groups = {};
for (const r of C.filter((x) => x && x.status && x.L)) {
  // Group by the English URL of the hreflang set (translated slugs differ per language).
  const k = isUtil(r) ? r.type : (r.hreflang?.en || r.hreflang?.["en-US"] || Object.entries(r.hreflang || {}).find(([c]) => c.startsWith("en"))?.[1] || key(r.url));
  (groups[k] ||= {})[r.L] = r;
}
const sig = (links) => links.map((a) => key(a.h)).join(" ");
for (const [k, g] of Object.entries(groups)) {
  const en = g.en; if (!en) { add("P2", "parity", Object.values(g)[0], `no English counterpart for group ${k}`); continue; }
  for (const L of LANGS.slice(1)) {
    const r = g[L]; if (!r) { add("P1", "parity", en, `group ${k}: no ${L} version`); continue; }
    const cmp = (name, a, b) => { if (a !== b) add("P1", name, r, `${name} [${b}] ≠ en [${a}]`); };
    cmp("header-nav", sig(en.header?.links || []), sig(r.header?.links || []));
    cmp("mobile-menu", sig(en.mobileMenu), sig(r.mobileMenu));
    cmp("dock", en.dock.map((d) => d.h === "button" ? "button" : key(d.h)).join(" "), r.dock.map((d) => d.h === "button" ? "button" : key(d.h)).join(" "));
    cmp("header-buttons", en.header?.buttons.length, r.header?.buttons.length);
    cmp("footer-cols", en.footerCols.map((c) => c.key + ":" + sig(c.links)).join(" | "), r.footerCols.map((c) => c.key + ":" + sig(c.links)).join(" | "));
    cmp("footer-legal", sig(en.footerLegal), sig(r.footerLegal));
    cmp("switcher-count", en.langSwitch.length, r.langSwitch.length);
    cmp("main-link-count", en.mainLinks.filter((a) => a.h.startsWith("/")).length, r.mainLinks.filter((a) => a.h.startsWith("/")).length);
    cmp("h1-count", en.h1, r.h1);
    cmp("jsonld", en.ld.join(","), r.ld.join(","));
    // English leaks: text segments identical to the English page's that contain 2+ English words.
    const enSegs = new Set(en.segs);
    const allow = /^(Egypt Roamer|GetYourGuide|Viator|Klook|Booking\.com|Unsplash|info@egyptroamer\.com|\+20[\d ]+|©.*|[A-Z][a-z]+ [A-Z][a-z]+)$/;
    const leaks = r.segs.filter((s) => enSegs.has(s) && /[a-z]{3,} [a-z]{3,}/i.test(s) && !/[^\x00-ɏ]/.test(s) && !allow.test(s));
    for (const s of [...new Set(leaks)].slice(0, 8)) add("P1", "english-leak", r, `"${s.slice(0, 90)}"`);
    const enAttrs = new Set(en.attrs);
    for (const s of [...new Set(r.attrs.filter((s) => enAttrs.has(s) && /[a-z]{3,} [a-z]{3,}/i.test(s) && !/[^\x00-ɏ]/.test(s) && !allow.test(s)))].slice(0, 5)) add("P2", "english-leak-attr", r, `"${s.slice(0, 90)}"`);
  }
  // Language switcher: from each page, each language link must go to that group's page in that language.
  for (const L of LANGS) {
    const r = g[L]; if (!r || isUtil(r)) continue;
    for (const s of r.langSwitch) {
      const code = s.hl.slice(0, 2);
      const target = g[code];
      if (target && s.h !== target.url && !s.h.startsWith(target.url)) add("P1", "switcher", r, `switcher ${code} → ${s.h}, expected ${target.url}`);
    }
  }
}
// Link status: every distinct internal URL.
const all = new Map();
for (const r of C.filter((x) => x && x.status)) for (const a of r.allLinks) if (a.h.startsWith("/") && !a.h.startsWith("//")) { const u = a.h.split("#")[0]; if (!all.has(u)) all.set(u, r); }
const RQ = await request.newContext({ maxRedirects: 0 });
const statuses = {};
for (const [u, from] of all) {
  if (u.startsWith("/go/")) continue;
  const res = await RQ.get(B + u, { maxRedirects: 0 }).catch((e) => ({ status: () => 0, headers: () => ({}) }));
  statuses[u] = res.status();
  if (res.status() >= 400 || res.status() === 0) add("P1", "broken-link", from, `${u} → ${res.status()}`);
  else if (res.status() >= 300) add("P2", "redirect-link", from, `${u} → ${res.status()} ${res.headers().location}`);
}
fs.writeFileSync(process.env.OUT || "issues.json", JSON.stringify(issues, null, 1));
const cnt = {}; for (const i of issues) cnt[i.comp + " " + i.sev] = (cnt[i.comp + " " + i.sev] || 0) + 1;
console.log(Object.entries(cnt).sort().map(([k, v]) => `${k}: ${v}`).join("\n"));
console.log("distinct internal URLs checked:", Object.keys(statuses).length);
