// Per-language functional check: nav, footer, CTA, forms, filters, search, RTL.
import { chromium } from "playwright";
import { execFileSync } from "child_process";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
// usage: node languages.mjs dest-urls.json   (JSON {lang: destination URL}); WP="path/to/wp" clears the form rate limit between languages
const dest = JSON.parse(fs.readFileSync(process.argv[2], "utf8"));
const EN = { cta: null, newsletterOk: "You're on the list", contactOk: "Thank you — your message has reached us" };
const langs = ["en", "fr", "de", "it", "es", "ru", "zh", "ar"];
const b = await chromium.launch();
const ctx = await b.newContext({ userAgent: "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36" });
const status = {};
async function st(u) { if (!(u in status)) { const r = await fetch(u, { redirect: "manual" }); status[u] = r.status; } return status[u]; }
const clearRL = () => process.env.WP && execFileSync(process.env.WP, ["eval", "global $wpdb; $wpdb->query(\"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%er_rl_%'\");"], { stdio: "ignore" });
const out = {};
for (const l of langs) {
  const pre = l === "en" ? "" : `/${l}`;
  const r = (out[l] = { issues: [] });
  const p = await ctx.newPage({ viewport: { width: 1440, height: 900 } });
  const errs = []; p.on("pageerror", (e) => errs.push(e.message));
  // navigation + footer (home)
  await p.goto(`${B}${pre}/`, { waitUntil: "networkidle" }); await p.waitForTimeout(1500);
  const links = await p.evaluate(() => ({
    nav: [...document.querySelectorAll("header a[href], nav a[href]")].map((a) => a.href),
    footer: [...document.querySelectorAll("footer a[href]")].map((a) => a.href),
    footerText: document.querySelector("footer")?.innerText.length || 0,
    dir: document.documentElement.dir || "ltr", lang: document.documentElement.lang,
    bodyDir: getComputedStyle(document.body).direction,
    overflow: document.documentElement.scrollWidth > innerWidth + 1,
  }));
  r.nav = links.nav.length; r.footer = links.footer.length;
  for (const u of [...new Set([...links.nav, ...links.footer])].filter((u) => u.startsWith(B) && !u.includes("#"))) {
    const s = await st(u); if (s >= 400) r.issues.push(`link ${u} → ${s}`);
    const path = u.slice(B.length);
    if (l !== "en" && !path.startsWith(`/${l}/`) && !/^\/(go|wp-|\?)/.test(path) && !/\/(fr|de|it|es|ru|zh|ar)\/$/.test(path) && path !== "/") r.issues.push(`cross-language link ${path}`);
  }
  r.dir = links.dir; r.bodyDir = links.bodyDir;
  if ((l === "ar") !== (links.bodyDir === "rtl")) r.issues.push(`direction ${links.bodyDir}`);
  if (links.overflow) r.issues.push("horizontal overflow 1440");
  // CTA on home and destination
  const ctas = [];
  for (const u of [`${B}${pre}/`, dest[l]]) {
    await p.goto(u, { waitUntil: "networkidle" }); await p.waitForTimeout(800);
    ctas.push(...(await p.evaluate(() => [...document.querySelectorAll('a[href*="/go/"]')].map((a) => ({ rel: a.rel, text: a.innerText.trim().replace(/\s+/g, " ").slice(0, 40) })))));
  }
  r.ctaCount = ctas.length; r.ctaLabels = [...new Set(ctas.map((c) => c.text))].slice(0, 4);
  if (ctas.some((c) => !c.rel.includes("sponsored"))) r.issues.push("CTA without rel=sponsored");
  // filters (experiences archive)
  await p.goto(`${B}${pre}/experiences/`, { waitUntil: "networkidle" });
  const f = await p.evaluate(() => { const s = document.querySelector("form.filters select[name=destination]"); return s ? { opts: s.options.length, vals: [...s.options].map((o) => o.value).filter(Boolean) } : null; });
  if (!f) r.issues.push("no filter form"); else {
    await Promise.all([p.waitForURL(/destination=/, { timeout: 10000 }).catch(() => {}), p.selectOption("form.filters select[name=destination]", f.vals[0])]);
    await p.waitForLoadState("networkidle");
    if (!/destination=/.test(p.url())) { await Promise.all([p.waitForURL(/destination=/), p.evaluate(() => document.querySelector("form.filters").submit())]); await p.waitForLoadState("networkidle"); }
    const fr = await p.evaluate(() => ({ robots: document.querySelector("meta[name=robots]")?.content, cards: document.querySelectorAll("main article, main .card, main li.card").length, canon: document.querySelector("link[rel=canonical]")?.href }));
    r.filter = `${p.url().slice(B.length)} robots=${fr.robots} canonical=${(fr.canon || "—").slice(B.length)}`;
    if (!/noindex/.test(fr.robots || "")) r.issues.push("filtered URL indexable");
    if (fr.canon && /[?&]destination=/.test(fr.canon)) r.issues.push("filtered canonical keeps params");
  }
  // search
  const q = { en: "Cairo", fr: "Caire", de: "Kairo", it: "Cairo", es: "Cairo", ru: "Каир", zh: "开罗", ar: "القاهرة" }[l];
  await p.goto(`${B}${pre}/?s=${encodeURIComponent(q)}`, { waitUntil: "networkidle" });
  const sr = await p.evaluate((pre) => ({ h1: document.querySelector("h1")?.innerText, robots: document.querySelector("meta[name=robots]")?.content, results: [...document.querySelectorAll("main a[href]")].map((a) => a.pathname).filter((x) => /\/destinations\//.test(x)) }), pre);
  r.search = `q=${q} h1="${(sr.h1 || "").replace(/\s+/g, " ").slice(0, 40)}" destLinks=${sr.results.length} robots=${sr.robots}`;
  if (!sr.results.length) r.issues.push("search: no destination result");
  if (l !== "en" && sr.results.some((x) => !decodeURIComponent(x).startsWith(`/${l}/`))) r.issues.push("search result in wrong language");
  // forms: newsletter + contact (contact only if published in this language)
  clearRL();
  await p.goto(dest[l], { waitUntil: "networkidle" });
  const nf = p.locator("form:has(input[name=action][value=er_subscribe])").first();
  if (await nf.count()) {
    await p.waitForTimeout(3500);
    await nf.locator("input[type=email]").fill(`gate-${l}-${Date.now()}@example.test`);
    await Promise.all([p.waitForLoadState("load"), nf.locator("button[type=submit], button:not([type])").first().click()]);
    await p.waitForTimeout(800);
    const msg = await p.evaluate(() => document.querySelector("[role=status], .er-form-msg, .nl-msg, [data-er-msg]")?.innerText || document.body.innerText.match(/.{0,80}(list|liste|Liste|lista|список|名单|القائمة).{0,40}/)?.[0] || "");
    r.newsletter = msg.replace(/\s+/g, " ").slice(0, 80);
    if (!msg) r.issues.push("newsletter: no confirmation"); else if (l !== "en" && msg.includes(EN.newsletterOk)) r.issues.push("newsletter reply in English");
  } else r.issues.push("no newsletter form");
  if (errs.length) r.issues.push(`JS errors: ${errs.join(" | ")}`);
  await p.close();
}
// RTL at 390 for ar
const m = await ctx.newPage({ viewport: { width: 390, height: 844 } });
for (const u of [`${B}/ar/`, dest.ar, `${B}/ar/destinations/`, `${B}/ar/experiences/`]) { await m.goto(u, { waitUntil: "networkidle" }); await m.waitForTimeout(800); const o = await m.evaluate(() => document.documentElement.scrollWidth - innerWidth); if (o > 1) out.ar.issues.push(`RTL overflow ${o}px at 390 on ${u}`); }
await b.close();
for (const [l, r] of Object.entries(out)) console.log(`${l}: nav=${r.nav} footer=${r.footer} dir=${r.dir}/${r.bodyDir} CTAs=${r.ctaCount} ${JSON.stringify(r.ctaLabels)}\n    filter: ${r.filter}\n    search: ${r.search}\n    newsletter: ${r.newsletter}\n    ${r.issues.length ? "ISSUES: " + r.issues.join(" ; ") : "OK"}`);
