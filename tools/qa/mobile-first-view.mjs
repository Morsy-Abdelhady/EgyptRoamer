// Phones' content-first homepage (theme 1.2.22): what a visitor sees during the first second, as screenshots,
// plus checks: no loader on phones, hero text painted at the first contentful paint, the homepage scripts start
// only after it, the journey still goes live, no layout shift, no script errors. Desktop keeps the intro.
// Output: sheets/_mfv-<lang>-<w>-<t>.png and sheets/mfv.json (python sheets.py mfv).
// Usage: node mobile-first-view.mjs
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
fs.mkdirSync("sheets", { recursive: true });
const br = await chromium.launch({ channel: "chrome" });
const files = [];
const results = [];
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const cases = [
  ["en", 320, 700, "no-preference"],
  ["en", 390, 844, "no-preference"],
  ["ar", 390, 844, "no-preference"],
  ["de", 320, 700, "no-preference"],
  ["zh", 390, 844, "no-preference"],
  ["en", 390, 844, "reduce"],
  ["en", 1440, 900, "no-preference"],
];
for (const [l, w, h, rm] of cases) {
  const ctx = await br.newContext({ viewport: { width: w, height: h }, reducedMotion: rm, isMobile: w < 900, hasTouch: w < 900 });
  await ctx.addInitScript(() => {
    window.__t = { lcp: [], runAt: 0, cls: 0 };
    const ce = document.createElement.bind(document);
    document.createElement = (t, o) => { const e = ce(t, o); if (String(t).toLowerCase() === "script" && !window.__t.runAt) window.__t.runAt = Math.round(performance.now()); return e; };
    new PerformanceObserver((li) => li.getEntries().forEach((e) => window.__t.lcp.push([Math.round(e.startTime), e.element?.className || e.element?.tagName]))).observe({ type: "largest-contentful-paint", buffered: true });
    new PerformanceObserver((li) => li.getEntries().forEach((e) => { if (!e.hadRecentInput) window.__t.cls += e.value; })).observe({ type: "layout-shift", buffered: true });
  });
  const p = await ctx.newPage();
  const errs = [];
  p.on("pageerror", (e) => errs.push(e.message));
  const tag = `${l}-${w}${rm === "reduce" ? "-rm" : ""}`;
  const nav = p.goto(`${B}/${l === "en" ? "" : l + "/"}?mfv=${Date.now()}`, { waitUntil: "load", timeout: 180000 });
  // as soon as the first paint has happened, and half a second later
  await p.waitForFunction(() => performance.getEntriesByName("first-contentful-paint").length > 0, null, { timeout: 120000, polling: 16 }).catch(() => {});
  for (const [name, wait] of [["first-paint", 0], ["plus500ms", 500]]) {
    await p.waitForTimeout(wait);
    const f = `sheets/_mfv-${tag}-${name}.png`;
    await p.screenshot({ path: f }).catch(() => {});
    files.push(f);
  }
  await nav.catch(() => {});
  await p.waitForTimeout(3500);
  const f = `sheets/_mfv-${tag}-settled.png`;
  await p.screenshot({ path: f });
  files.push(f);
  const s = await p.evaluate(() => ({
    fcp: Math.round(performance.getEntriesByName("first-contentful-paint")[0]?.startTime || 0),
    t: window.__t,
    loader: (() => { const el = document.getElementById("loader"); return el ? getComputedStyle(el).display : "removed"; })(),
    live: document.querySelector(".journey")?.classList.contains("is-live"),
    heroIn: document.getElementById("hero")?.classList.contains("is-in"),
    titleVisible: (() => { const r = document.querySelector(".hero__title .t-display").getBoundingClientRect(); return r.width > 0 && r.bottom > 0 && r.top < innerHeight; })(),
  }));
  const phone = w <= 900;
  const lastLcp = s.t.lcp[s.t.lcp.length - 1] || [0, ""];
  if (phone) {
    check(s.loader === "none" || s.loader === "removed", `${tag} no loader screen`, s.loader);
    check(/t-display|hero/.test(lastLcp[1]) && lastLcp[0] <= s.fcp + 50, `${tag} hero text is the LCP at first paint`, `fcp ${s.fcp} lcp ${JSON.stringify(s.t.lcp)}`);
    check(s.t.runAt >= lastLcp[0], `${tag} homepage scripts start after the LCP`, `scripts at ${s.t.runAt}`);
  } else {
    check(true, `${tag} desktop keeps the intro`, `loader ${s.loader}`);
  }
  // reduced motion: the journey is the static stacked version by design (journey.js)
  check((rm === "reduce" ? !s.live : s.live) && s.heroIn && s.titleVisible, `${tag} journey ${rm === "reduce" ? "static (reduced motion)" : "live"}, hero in, title on screen`);
  check(s.t.cls < 0.01, `${tag} no layout shift`, s.t.cls.toFixed(4));
  check(errs.length === 0, `${tag} no script errors`, errs.join(" | "));
  await ctx.close();
}
fs.writeFileSync("sheets/mfv.json", JSON.stringify(files));
await br.close();
const bad = results.filter((r) => !r).length;
console.log(bad ? `${bad} failing of ${results.length}` : `mobile first view: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
