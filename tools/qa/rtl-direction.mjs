// Arabic text direction on every Arabic page (owner, 28 Oct): arrows that point the way the text reads (after any
// CSS mirroring), and number ranges ("4–10") wrapped in a left-to-right isolate so bidi does not show "10–4".
// English pages are checked the other way round (no arrow pointing left). Desktop and phone.
// Usage: BASE=https://egyptroamer.com node tools/qa/rtl-direction.mjs
import { chromium } from "playwright";

const B = (process.env.BASE || "http://127.0.0.1:8080").replace(/\/$/, "");
const en = new Set(["/"]);
for (const a of ["/destinations/", "/experiences/", "/guides/"]) {
  en.add(a);
  for (let n = 1; n < 20; n++) {
    const r = await fetch(B + a + (n > 1 ? `page/${n}/` : "")); if (r.status >= 400) break;
    const before = en.size; for (const m of (await r.text()).matchAll(/href="([^"#?]+)"/g)) { const u = new URL(m[1], B); if (u.origin === new URL(B).origin && /^\/(destinations|experiences|guides)\/[^/]+\/$/.test(u.pathname) && u.pathname.startsWith(a)) en.add(u.pathname); }
    if (en.size === before) break;
  }
}
const urls = [];
for (const path of en) { urls.push(["en", B + path]); const t = await (await fetch(B + path)).text(); const m = t.match(/hreflang="ar[^"]*" href="([^"]+)"/) || t.match(/href="([^"]+)" hreflang="ar/); if (m) urls.push(["ar", m[1]]); }
console.log("pages:", urls.filter((u) => u[0] === "ar").length, "ar,", urls.filter((u) => u[0] === "en").length, "en");
const b = await chromium.launch({ channel: "chrome" });
let bad = 0;
const run = async ([lang, u], o, d) => {
  const c = await b.newContext(o); await c.addInitScript(() => { try { localStorage.setItem("er-nocount", "1"); } catch (e) {} });
  const p = await c.newPage(); await p.goto(u, { waitUntil: "load", timeout: 120000 }); await p.waitForTimeout(1200);
  const r = await p.evaluate((lang) => {
    const out = []; const w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    const flipped = el => { let f = false; for (let e = el; e && e !== document.body; e = e.parentElement) { const cs = getComputedStyle(e); const m = cs.transform; if (m && m !== "none") { const a = m.match(/matrix\(([^,]+)/); if (a && parseFloat(a[1]) < 0) f = !f; } if (cs.scale && cs.scale.startsWith("-1")) f = !f; } return f; };
    while (w.nextNode()) { const n = w.currentNode, el = n.parentElement; if (!el || el.closest("script,style,noscript,[hidden]")) continue; const rect = el.getBoundingClientRect(); if (!rect.width) continue;
      const s = n.nodeValue;
      for (const ch of s.match(/[←→]/g) || []) { if (/[A-Za-z؀-ۿ]\s*[←→]\s*[A-Za-z؀-ۿ]/.test(s) && !/[←→]\s*$/.test(s.trim()) ) continue; // "A → B" routes
        let right = ch === "→"; if (flipped(el)) right = !right; if ((lang === "ar") === right) out.push("arrow " + (right ? "→" : "←") + " in: " + (el.closest("a,button")?.innerText || s).trim().replace(/\s+/g, " ").slice(0, 60)); }
      if (lang === "ar") for (const m of s.matchAll(/(?<![\d.])\d+(?:\.\d+)?\s?[–-]\s?\d+/g)) { const before = s.slice(0, m.index); if (!before.endsWith("⁦") && !/^\d{4}-\d/.test(m[0]) && getComputedStyle(el).direction === "rtl") out.push("range not isolated: " + s.trim().slice(0, 60)); }
    } return [...new Set(out)];
  }, lang);
  if (r.length) { bad += r.length; console.log(lang, d, decodeURIComponent(u).replace(B, "")); r.slice(0, 6).forEach((x) => console.log("   ", x)); }
  await c.close();
};
for (const [d, o] of [["desk", { viewport: { width: 1440, height: 900 } }], ["phone", { viewport: { width: 390, height: 844 }, isMobile: true }]]) {
  for (let i = 0; i < urls.length; i += 4) await Promise.all(urls.slice(i, i + 4).map((x) => run(x, o, d)));
}
console.log(bad ? `rtl-direction: ${bad} problems` : "rtl-direction: all ok");
await b.close();
process.exit(bad ? 1 : 0);
