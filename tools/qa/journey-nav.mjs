// Homepage journey behaviour: rail navigation ("journey:goto") lands on each scene, the counter follows, the
// pinned stage works, and with reduced motion the stacked fallback is used, without errors. en/ar/de, desktop/phone.
import { chromium } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const br = await chromium.launch({ channel: "chrome" });
let bad = 0;
const cases = [["en", 1440, 900, "no-preference"], ["ar", 390, 844, "no-preference"], ["de", 1280, 800, "reduce"], ["en", 390, 844, "reduce"], ["zh", 1024, 768, "no-preference"], ["ru", 414, 896, "no-preference"]];
for (let ci = 0, retried = false; ci < cases.length; ci++) {
  const [lang, w, h, motion] = cases[ci];
  const ctx = await br.newContext({ viewport: { width: w, height: h }, reducedMotion: motion });
  await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media"].includes(r.request().resourceType()) ? r.abort() : r.continue()));
  const p = await ctx.newPage();
  const errs = [];
  p.on("pageerror", (e) => errs.push(e.message));
  const failed = []; // a local file the dev proxy dropped: environment failure, the case runs once more
  p.on("requestfailed", (r) => r.url().startsWith(B) && failed.push(r.url()));
  await p.goto(`${B}/${lang === "en" ? "" : lang + "/"}?jn=${Date.now()}`, { waitUntil: "load", timeout: 180000 });
  await p.waitForTimeout(5000);
  const live = await p.evaluate(() => !!document.querySelector(".journey.is-live"));
  const probs = [];
  if ((motion === "reduce") === live) probs.push(`live=${live} with ${motion}`);
  const rails = (await Promise.all((await p.$$("[data-rail]")).map(async (r) => ((await r.isVisible()) ? r : null)))).filter(Boolean);
  const navH = await p.evaluate(() => parseFloat(getComputedStyle(document.documentElement).scrollPaddingTop) || 0);
  const seen = [];
  if (rails.length) {
    // Rail navigation (desktop): lands on each scene; with motion the pinned timeline rests on it.
    for (let i = 0; i < Math.min(4, rails.length); i++) {
      await rails[i].click();
      await p.waitForTimeout(3200);
      const s = await p.evaluate(() => ({
        count: document.getElementById("journey-count")?.textContent,
        active: [...document.querySelectorAll(".scene")].findIndex((x) => x.classList.contains("is-active")),
        tops: [...document.querySelectorAll(".scene")].map((x) => Math.round(x.getBoundingClientRect().top)),
      }));
      seen.push(live ? `${i}:${s.count}/${s.active}` : `${i}:top${s.tops[i]}`);
      if (live && (s.count !== String(i + 1).padStart(2, "0") || s.active !== i)) probs.push(`rail ${i} → count ${s.count}, active ${s.active}`);
      // stacked fallback: the scene stops just below the fixed header (html scroll-padding-top)
      // (the first scene starts at the top of the page, so it may stop anywhere between 0 and the header)
      if (!live && (s.tops[i] < -8 || s.tops[i] > navH + 8 || (i > 0 && Math.abs(s.tops[i] - navH) > 8))) probs.push(`rail ${i} → scene top ${s.tops[i]} (header ${navH})`);
    }
  } else if (live) {
    // Phones (no rail): scrolling through the pinned stage moves the scene counter 01 → 04 in order.
    const counts = [];
    for (let y = 0; y <= 5.2; y += 0.4) {
      await p.evaluate((y) => (window.__lenis ? window.__lenis.scrollTo(innerHeight * y, { immediate: true, force: true }) : scrollTo(0, innerHeight * y)), y);
      await p.waitForTimeout(900);
      counts.push(await p.evaluate(() => document.getElementById("journey-count")?.textContent));
    }
    const order = [...new Set(counts)];
    seen.push("scroll " + order.join("→"));
    if (order.join() !== "01,02,03,04") probs.push("counter order " + order.join(","));
  }
  if (errs.length) probs.push("errors " + errs.join(" | "));
  if (probs.length && failed.length && !retried) {
    console.log(`retry ${lang} ${w}: local requests failed`);
    retried = true; ci--;
    await ctx.close();
    continue;
  }
  retried = false;
  if (probs.length) bad++;
  console.log(`${probs.length ? "FAIL" : "ok  "} ${lang} ${w} motion=${motion} live=${live} rail ${seen.join(" ")}${rails.length ? "" : " (no rail)"} ${probs.join("; ")}`);
  await ctx.close();
}
await br.close();
console.log(bad ? `${bad} failing` : "journey nav: all ok");
