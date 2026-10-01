// Homepage start-up cost and journey geometry, for before/after comparisons of journey changes.
// Per profile (mobile 390 px at 4x CPU, desktop 1440 px): N loads; long tasks after navigation start (TBT-like:
// sum of time over 50 ms), CLS, LCP, ScrollTrigger refreshes, and the geometry every trigger ends up with
// (start/end of each ScrollTrigger, pin-spacer height, page height). Geometry must be identical between builds.
// Screenshots at fixed scroll positions (remote photos blocked so they compare) go to OUT_DIR for pixel diffs.
//   OUT_DIR=before N=5 node journey-perf.mjs
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const N = +(process.env.N || 5);
const OUT = process.env.OUT_DIR || "journey-perf";
fs.mkdirSync(OUT, { recursive: true });
const br = await chromium.launch({ channel: "chrome" });
const median = (a) => { const s = [...a].sort((x, y) => x - y); return s.length ? s[Math.floor(s.length / 2)] : null; };
const summary = {};
for (const [name, w, h, cpu, lang] of [["mobile", 390, 844, 4, "en"], ["desktop", 1440, 900, 1, "en"], ["mobile-ar", 390, 844, 4, "ar"]]) {
  const runs = [];
  for (let i = 0; i < (name === "mobile-ar" ? 2 : N); i++) {
    const ctx = await br.newContext({ viewport: { width: w, height: h } });
    await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media"].includes(r.request().resourceType()) ? r.abort() : r.continue()));
    const p = await ctx.newPage();
    const cdp = await ctx.newCDPSession(p);
    if (cpu > 1) await cdp.send("Emulation.setCPUThrottlingRate", { rate: cpu });
    const failed = [];
    p.on("requestfailed", (r) => r.url().startsWith(B) && failed.push(r.url()));
    await p.addInitScript(() => {
      window.__m = { lt: [], cls: 0, lcp: 0, refresh: 0 };
      new PerformanceObserver((l) => l.getEntries().forEach((e) => window.__m.lt.push(e.duration))).observe({ type: "longtask", buffered: true });
      new PerformanceObserver((l) => l.getEntries().forEach((e) => { if (!e.hadRecentInput) window.__m.cls += e.value; })).observe({ type: "layout-shift", buffered: true });
      new PerformanceObserver((l) => l.getEntries().forEach((e) => (window.__m.lcp = e.startTime))).observe({ type: "largest-contentful-paint", buffered: true });
      let hooked = false;
      const hook = () => {
        if (hooked || !window.ScrollTrigger) return;
        hooked = true;
        window.ScrollTrigger.addEventListener("refresh", () => window.__m.refresh++);
      };
      document.addEventListener("DOMContentLoaded", hook);
      window.addEventListener("load", hook, { capture: true });
    });
    await p.goto(`${B}/${lang === "en" ? "" : lang + "/"}?jp=${Date.now()}`, { waitUntil: "load", timeout: 180000 });
    await p.waitForTimeout(9000);
    const m = await p.evaluate(() => {
      const st = window.ScrollTrigger?.getAll().map((t) => [Math.round(t.start), Math.round(t.end)]) || [];
      const spacer = document.querySelector(".pin-spacer");
      return {
        live: !!document.querySelector(".journey.is-live"),
        tbt: Math.round(window.__m.lt.reduce((s, d) => s + Math.max(0, d - 50), 0)),
        longest: Math.round(Math.max(0, ...window.__m.lt)),
        cls: +window.__m.cls.toFixed(4),
        lcp: Math.round(window.__m.lcp),
        refresh: window.__m.refresh,
        geometry: JSON.stringify({ st, spacer: spacer ? Math.round(spacer.getBoundingClientRect().height) : null, page: document.documentElement.scrollHeight }),
      };
    });
    if (!m.live || failed.length) {
      console.log(`${name} run ${i + 1}: discarded (${!m.live ? "journey not live" : "local request failed"})`);
      i--;
      await ctx.close();
      if (runs.length + 1 > N * 3) break;
      continue;
    }
    runs.push(m);
    if (i === 0) {
      // Screenshots at fixed scroll positions (scrubbed timeline settled)
      for (const [k, f] of [["0", 0], ["15", 1.5], ["30", 3.0], ["45", 4.5], ["end", 99]]) {
        await p.evaluate((f) => (window.__lenis ? window.__lenis.scrollTo(f === 99 ? document.documentElement.scrollHeight : innerHeight * f, { immediate: true, force: true }) : scrollTo(0, f === 99 ? 1e7 : innerHeight * f)), f);
        await p.waitForTimeout(3500);
        await p.screenshot({ path: `${OUT}/${name}-${k}.png` });
      }
      // In-page anchor: the planner link must land on the planner section
      await p.evaluate(() => (window.__lenis ? window.__lenis.scrollTo(0, { immediate: true, force: true }) : scrollTo(0, 0)));
      await p.waitForTimeout(800);
      const planner = await p.evaluate(async () => {
        const a = [...document.querySelectorAll('a[href*="#planner"]')].find((x) => x.offsetParent);
        if (!a) return "no planner link";
        a.click();
        await new Promise((r) => setTimeout(r, 4000));
        const t = document.querySelector("#planner")?.getBoundingClientRect().top;
        return t == null ? "no #planner" : Math.round(t);
      });
      m.anchor = planner;
    }
    console.log(`${name} run ${i + 1}: tbt ${m.tbt} longest ${m.longest} cls ${m.cls} lcp ${m.lcp} refresh ${m.refresh}${m.anchor != null ? " planner-top " + m.anchor : ""}`);
    await ctx.close();
  }
  summary[name] = {
    tbt: median(runs.map((r) => r.tbt)),
    longest: median(runs.map((r) => r.longest)),
    cls: median(runs.map((r) => r.cls)),
    lcp: median(runs.map((r) => r.lcp)),
    refresh: median(runs.map((r) => r.refresh)),
    geometry: [...new Set(runs.map((r) => r.geometry))],
    anchor: runs[0]?.anchor,
  };
}
fs.writeFileSync(`${OUT}/summary.json`, JSON.stringify(summary, null, 1));
console.log(JSON.stringify(summary, null, 1));
await br.close();
