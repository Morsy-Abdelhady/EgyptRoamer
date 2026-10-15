// Release gate: every published destination, experience and guide, in every language, plus each language's home.
// Covers the misses of 15 Oct (a destination with no booking action; guides in a narrow single column) by checking
// every page instead of samples:
//   - a booking action: the box (#book / #plan) on the page; at 390px the dock's main button points at it, and the
//     box is displayed. Pages without live offers are listed as failures, not skipped;
//   - the hero shortcut on destinations and experiences;
//   - offer cards: no English title on a translated page (lang="en" marker), Arabic/Russian/Chinese titles and
//     locations not in Latin script, and every card image loaded;
//   - layout: destinations, experiences and guides use the sidebar layout on wide screens;
//   - homes: the planner lists partner tours;
//   - no JavaScript errors.
// Usage: BASE=https://egyptroamer.com node tools/qa/page-coverage.mjs   (sets er-nocount: QA views are not counted)
import { chromium } from "playwright";

const B = (process.env.BASE || "http://127.0.0.1:8080").replace(/\/$/, "");
const CONC = Number(process.env.CONC || 4);
const NON_LATIN = ["ar", "ru", "zh"];

const br = await chromium.launch({ channel: "chrome" });
const mk = (mobile) =>
  br.newContext(mobile ? { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true } : { viewport: { width: 1440, height: 900 } }).then(async (c) => {
    await c.addInitScript(() => { try { localStorage.setItem("er-nocount", "1"); } catch (e) {} });
    return c;
  });
const desk = await mk(false);
const mob = await mk(true);

// Discover: English archives → English pages → their translations (hreflang), plus every language's home.
const disc = await desk.newPage();
const en = new Set();
// Archives are paginated: follow /page/N/ until a page adds nothing new.
for (const a of ["/destinations/", "/experiences/", "/guides/"]) {
  for (let n = 1; n < 20; n++) {
    const r = await disc.goto(B + a + (n > 1 ? `page/${n}/` : ""), { waitUntil: "domcontentloaded", timeout: 120000 });
    if (!r || r.status() >= 400) break;
    const before = en.size;
    for (const h of await disc.$$eval("a[href]", (xs) => xs.map((x) => x.href))) {
      const u = new URL(h);
      if (u.origin === new URL(B).origin && /^\/(destinations|experiences|guides)\/[^/]+\/$/.test(u.pathname) && u.pathname.startsWith(a)) en.add(u.pathname);
    }
    if (en.size === before) break;
  }
}
const pages = [];
const homes = new Map();
for (const p of en) {
  await disc.goto(B + p, { waitUntil: "domcontentloaded", timeout: 120000 });
  const alts = await disc.$$eval("link[rel=alternate][hreflang]", (ls) => ls.filter((l) => l.hreflang !== "x-default").map((l) => [l.hreflang.slice(0, 2), l.href]));
  for (const [lang, href] of alts) {
    pages.push({ lang, url: href, kind: p.split("/")[1] });
    if (!homes.has(lang)) homes.set(lang, new URL(href).origin + (lang === "en" ? "/" : `/${lang}/`));
  }
}
for (const [lang, url] of homes) pages.push({ lang, url, kind: "home" });
await disc.close();

const fails = [];
const fail = (pg, msg) => fails.push(`${pg.lang} ${decodeURI(new URL(pg.url).pathname)}: ${msg}`);

async function check(pg) {
  // Wide screen
  let p = await desk.newPage();
  const errs = [];
  p.on("pageerror", (e) => errs.push(String(e).slice(0, 120)));
  await p.goto(pg.url, { waitUntil: "networkidle", timeout: 120000 }).catch(() => {});
  await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); } });
  await p.waitForTimeout(1200);
  // Lazy images still loading are given time (up to 15 s) before they are judged missing.
  await p.evaluate(() => Promise.all([...document.querySelectorAll(".offer__img img")].filter((i) => !i.complete).map((i) => { i.loading = "eager"; return Promise.race([i.decode().catch(() => {}), new Promise((r) => setTimeout(r, 15000))]); })));
  const d = await p.evaluate(() => {
    const vis = (e) => !!e && getComputedStyle(e).display !== "none" && e.getClientRects().length > 0;
    const offers = [...document.querySelectorAll(".offer")].filter(vis).map((o) => {
      const img = o.querySelector(".offer__img img");
      const name = o.querySelector(".offer__name");
      return { name: name?.textContent.trim() || "", nameLang: name?.getAttribute("lang") || "", loc: o.querySelector(".offer__loc")?.textContent.trim() || "", img: !!img && img.complete && img.naturalWidth > 0 };
    });
    return {
      layout: document.querySelector(".page-layout")?.className || "",
      story: !!document.querySelector(".page-hero--story"),
      hero: document.querySelector(".page-hero__actions a")?.getAttribute("href") || "",
      box: ["#book", "#plan"].some((s) => vis(document.querySelector(s))) || vis(document.querySelector(".page-aside .offer-box")),
      offers,
      tours: document.querySelectorAll(".itin__tour").length,
    };
  });
  await p.close();

  if (pg.kind === "home") {
    if (!d.tours) fail(pg, "planner shows no partner tours");
  } else {
    if (!d.box) fail(pg, "no booking box on the page");
    if (pg.kind !== "guides" && !/^#(book|plan)$/.test(d.hero)) fail(pg, "no booking button in the hero");
    if (!d.story && !/page-layout--aside/.test(d.layout)) fail(pg, `layout is "${d.layout}", expected the sidebar layout`);
  }
  for (const o of d.offers) {
    if (!o.img) fail(pg, `offer image missing: ${o.name}`);
    if (pg.lang !== "en" && o.nameLang) fail(pg, `untranslated offer title: ${o.name}`);
    if (NON_LATIN.includes(pg.lang) && /^[\x00-ɏ\s\p{P}\d]+$/u.test(o.name)) fail(pg, `offer title in Latin script: ${o.name}`);
    if (NON_LATIN.includes(pg.lang) && o.loc && /^[\x00-ɏ\s\p{P}\d]+$/u.test(o.loc)) fail(pg, `offer location in Latin script: ${o.loc}`);
  }

  // Phone: the dock's main button leads to a displayed booking box.
  if (pg.kind !== "home") {
    p = await mob.newPage();
    p.on("pageerror", (e) => errs.push(String(e).slice(0, 120)));
    await p.goto(pg.url, { waitUntil: "networkidle", timeout: 120000 }).catch(() => {});
    const m = await p.evaluate(() => {
      const href = document.querySelector(".dock__plan")?.getAttribute("href") || "";
      const t = href.startsWith("#") ? document.querySelector(href) : null;
      return { href, shown: !!t && getComputedStyle(t).display !== "none" && t.getClientRects().length > 0 };
    });
    await p.close();
    if (!/^#(book|plan)$/.test(m.href)) fail(pg, `phone dock goes to "${m.href}", not the booking box`);
    else if (!m.shown) fail(pg, `phone dock target ${m.href} is not displayed`);
  }
  for (const e of [...new Set(errs)]) fail(pg, `JS error: ${e}`);
}

const queue = [...pages];
await Promise.all(Array.from({ length: CONC }, async () => { while (queue.length) await check(queue.shift()); }));
await br.close();

const byKind = pages.reduce((a, p) => ((a[p.kind] = (a[p.kind] || 0) + 1), a), {});
console.log(`pages: ${pages.length} (${Object.entries(byKind).map(([k, n]) => `${k} ${n}`).join(", ")}), languages: ${homes.size}`);
if (fails.length) {
  console.log(fails.sort().join("\n"));
  console.log(`page-coverage: ${fails.length} problem(s)`);
  process.exit(1);
}
console.log("page-coverage: all ok");
