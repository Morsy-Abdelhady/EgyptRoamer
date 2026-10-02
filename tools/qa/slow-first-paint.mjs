// tools/qa/slow-first-paint.mjs — usage: P=/ar/ V=all,title,none node slow-first-paint.mjs
// Real first paint / LCP on a throttled phone connection (CDP: 150 ms RTT, 1.6 Mbps down, 4x CPU), with the
// font preloads as served (all), only the page script's own faces (own), or none — median of 3.
import { chromium } from "playwright";
const path = process.env.P || "/ar/";
const br = await chromium.launch({ channel: "chrome" });
const out = {};
for (const variant of (process.env.V || "all,own,none").split(",")) {
  const runs = [];
  for (let i = 0; i < 3; i++) {
    const ctx = await br.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
    await ctx.route(/127\.0\.0\.1:8080\/(ar\/|zh\/|de\/)?(\?|$)/, async (route) => {
      const r = await route.fetch();
      let body = await r.text();
      if (variant === "none") body = body.replace(/<link rel="preload" href="[^"]+" as="font"[^>]*>\s*/g, "");
      if (variant === "title") body = body.replace(/<link rel="preload" href="[^"]+" as="font"[^>]*>\s*/g, (m) => (/playfair-display-(latin|cyrillic)-400-normal|noto-naskh-arabic-arabic-400/.test(m) ? m : ""));
      if (variant === "own") body = body.replace(/<link rel="preload" href="[^"]+(inter|playfair)[^"]+" as="font"[^>]*>\s*/g, "");
      await route.fulfill({ response: r, body });
    });
    await ctx.addInitScript(() => { window.__lcp = 0; new PerformanceObserver((l) => l.getEntries().forEach((e) => (window.__lcp = Math.round(e.startTime)))).observe({ type: "largest-contentful-paint", buffered: true }); });
    const p = await ctx.newPage();
    const cdp = await ctx.newCDPSession(p);
    await cdp.send("Network.enable");
    await cdp.send("Network.emulateNetworkConditions", { offline: false, latency: 150, downloadThroughput: (1.6 * 1024 * 1024) / 8, uploadThroughput: (750 * 1024) / 8 });
    await cdp.send("Emulation.setCPUThrottlingRate", { rate: 4 });
    await p.goto("http://127.0.0.1:8080" + path + "?v=" + Date.now(), { waitUntil: "load", timeout: 180000 }).catch(() => {});
    await p.waitForTimeout(1500);
    runs.push(await p.evaluate(() => ({ fcp: Math.round(performance.getEntriesByName("first-contentful-paint")[0]?.startTime || 0), lcp: window.__lcp })));
    await ctx.close();
  }
  runs.sort((a, b) => a.lcp - b.lcp);
  out[variant] = runs[1];
}
console.log(path, JSON.stringify(out));
await br.close();
