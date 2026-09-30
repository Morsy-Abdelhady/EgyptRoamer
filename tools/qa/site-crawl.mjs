// Full-site crawl of every URL in urls.json (site-inventory.php) in a real browser at 1440 px:
// SEO head, header/footer/switcher/dock/mobile-menu signatures, every link, visible text, headings, axe.
//   BASE=http://127.0.0.1:8080 U=urls.json node site-crawl.mjs   (GAP=3000 on production; stops on a challenge)
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const GAP = +(process.env.GAP || 0);
const urls = JSON.parse(fs.readFileSync(process.env.U || "urls.json", "utf8"));
const axe = fs.readFileSync("node_modules/axe-core/axe.min.js", "utf8");
const br = await chromium.launch({ channel: "chrome" });
const ctx = await br.newContext({ viewport: { width: 1440, height: 900 }, serviceWorkers: "block", userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36" });
const out = [];
// Hot-linked stock photos and third-party assets are irrelevant to these checks and slow every load.
await ctx.route(/^https?:\/\/(?!127\.0\.0\.1|egyptroamer\.com)/, (r) => ["image", "media", "font"].includes(r.request().resourceType()) ? r.abort() : r.continue());
for (const u of urls) {
  let rec;
  for (let attempt = 0; attempt < 3 && !rec; attempt++) {
    const p = await ctx.newPage();
    const errs = [], cons = [];
    p.on("pageerror", (e) => errs.push(e.message));
    p.on("console", (m) => m.type() === "error" && cons.push(m.text().slice(0, 160)));
    try {
      const r = await p.goto(B + u.url, { waitUntil: "domcontentloaded", timeout: 60000 });
      if (r.status() === 429 || r.headers()["cf-mitigated"]) { console.error("STOP challenge", u.url); process.exit(2); }
      await p.waitForTimeout(400);
      const d = await p.evaluate(() => {
        const q = (s) => document.querySelector(s);
        const qa = (s) => [...document.querySelectorAll(s)];
        const txt = (e) => (e ? e.textContent.replace(/\s+/g, " ").trim() : "");
        const rel = (h) => (h || "").replace(location.origin, "");
        const links = (root) => (root ? [...root.querySelectorAll("a[href]")].map((a) => ({ t: txt(a) || a.getAttribute("aria-label") || "", h: rel(a.getAttribute("href")), hl: a.getAttribute("hreflang") || "" })) : []);
        const header = q("header.header, header.site-header, body > header, header");
        // visible text segments (leaf-ish blocks) for English-leak comparison
        const segs = new Set();
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        while (walker.nextNode()) {
          const n = walker.currentNode, el = n.parentElement;
          if (!el || el.closest("script,style,noscript,template,svg,[hidden],[aria-hidden=true]")) continue;
          const s = n.textContent.replace(/\s+/g, " ").trim();
          if (s.length > 2) segs.add(s);
        }
        const attrs = qa("[placeholder],[aria-label],[title],img[alt],input[value][type=submit]").map((e) => e.getAttribute("placeholder") || e.getAttribute("aria-label") || e.getAttribute("title") || e.getAttribute("alt") || e.value).filter((s) => s && s.length > 2);
        const ld = qa('script[type="application/ld+json"]').map((s) => { try { const j = JSON.parse(s.textContent); return (Array.isArray(j) ? j : j["@graph"] || [j]).map((x) => x["@type"]).flat().join("/"); } catch (e) { return "PARSE-ERROR"; } });
        const heads = qa("main h1, main h2, main h3, main h4, h1").map((h) => +h.tagName[1]);
        let skip = 0; for (let i = 1; i < heads.length; i++) if (heads[i] > heads[i - 1] + 1) skip++;
        const ids = new Set(qa("[id]").map((e) => e.id));
        const badAnchors = qa('a[href^="#"]').map((a) => a.getAttribute("href").slice(1)).filter((h) => h && !ids.has(decodeURIComponent(h)));
        return {
          lang: document.documentElement.lang, dir: document.documentElement.dir || "ltr",
          title: document.title, desc: q('meta[name="description"]')?.content || "",
          canonical: rel(q('link[rel="canonical"]')?.href), robots: q('meta[name="robots"]')?.content || "",
          hreflang: Object.fromEntries(qa('link[rel="alternate"][hreflang]').map((l) => [l.hreflang, rel(l.href)])),
          og: { title: q('meta[property="og:title"]')?.content || "", locale: q('meta[property="og:locale"]')?.content || "", url: rel(q('meta[property="og:url"]')?.content), image: !!q('meta[property="og:image"]'), type: q('meta[property="og:type"]')?.content || "" },
          tw: q('meta[name="twitter:card"]')?.content || "",
          ld, h1: qa("h1").length, h1t: txt(q("h1")), skip, badAnchors,
          landmarks: { main: qa("main").length, nav: qa("nav").length, header: qa("header").length, footer: qa("footer").length, skip: !!q('a[href="#main"], a.skip-link, a[href^="#content"]') },
          header: header ? { logo: rel(header.querySelector("a[href]")?.getAttribute("href")), links: links(header), buttons: [...header.querySelectorAll("button")].map((b) => b.getAttribute("aria-label") || txt(b)) } : null,
          langSwitch: qa(".lang a[href], [data-lang-switch] a[href]").map((a) => ({ t: txt(a), h: rel(a.getAttribute("href")), hl: a.getAttribute("hreflang") || a.getAttribute("lang") || "" })),
          mobileMenu: links(q("#menu")),
          dock: links(q("nav.dock")).concat([...document.querySelectorAll("nav.dock button")].map((b) => ({ t: txt(b), h: "button" }))),
          footerCols: qa("footer .footer__cols > [data-footer-col]").map((d) => ({ key: d.dataset.footerCol, h: txt(d.querySelector("h3")), links: links(d) })),
          footerLegal: links(q("footer .footer__legal")),
          footerAll: links(q("footer.footer")),
          mainLinks: links(q("main") || document.body),
          allLinks: links(document.body),
          segs: [...segs], attrs,
          imgsNoAlt: qa("img:not([alt])").length,
          overflow: document.documentElement.scrollWidth > innerWidth + 1,
          textLen: txt(q("main")).length,
        };
      });
      await p.addScriptTag({ content: axe });
      const ax = await p.evaluate(async () => (await axe.run(document, { resultTypes: ["violations"] })).violations.map((v) => ({ id: v.id, n: v.nodes.length, impact: v.impact, t: v.nodes[0]?.target?.join(" ") })));
      rec = { ...u, status: r.status(), finalUrl: p.url().replace(B, ""), errs, cons, axe: ax, ...d };
    } catch (e) {
      if (attempt === 2) rec = { ...u, status: 0, error: String(e).slice(0, 200) };
    }
    await p.close();
  }
  out.push(rec);
  fs.appendFileSync((process.env.O || "crawl.json") + "l", JSON.stringify(rec) + String.fromCharCode(10));
  process.stdout.write(`${rec.status} ${u.lang} ${u.url}\n`);
  if (GAP) await new Promise((s) => setTimeout(s, GAP));
}
await br.close();
fs.writeFileSync(process.env.O || "crawl.json", JSON.stringify(out));
