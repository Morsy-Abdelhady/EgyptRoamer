// Keyboard-only review, all 8 languages: skip link, header tab order, language menu, search overlay,
// mobile menu (focus trap, Escape, focus return), visible focus on every stop.
import { chromium } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const LANGS = ["en", "ar", "de", "fr", "it", "es", "ru", "zh"];
const br = await chromium.launch({ channel: "chrome" });
let bad = 0;
const focusInfo = (p) => p.evaluate(() => {
  const a = document.activeElement; if (!a || a === document.body) return { tag: "body" };
  const s = getComputedStyle(a);
  const ring = (s.outlineStyle !== "none" && parseFloat(s.outlineWidth) > 0) || s.boxShadow !== "none";
  const b = a.getBoundingClientRect();
  return { tag: a.tagName, cls: String(a.className).slice(0, 30), text: (a.getAttribute("aria-label") || a.textContent).trim().slice(0, 25), ring, visible: b.width > 0 && b.height > 0 && b.bottom > 0 && b.top < innerHeight, inOverlay: !!a.closest(".overlay,#menu") };
});
for (const l of LANGS) for (const w of [1440, 390]) {
  const ctx = await br.newContext({ viewport: { width: w, height: 850 }, serviceWorkers: "block", reducedMotion: "reduce" });
  await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => ["image", "media"].includes(r.request().resourceType()) ? r.abort() : r.continue());
  const p = await ctx.newPage(); const errs = []; p.on("pageerror", (e) => errs.push(e.message));
  const url = B + (l === "en" ? "/contact/" : `/${l}/contact-${l}/`);
  await p.goto(url, { waitUntil: "load" });
  const probs = [];
  // 1. Skip link is the first stop and moves focus to main.
  await p.keyboard.press("Tab");
  let f = await focusInfo(p);
  if (!/skip-link/.test(f.cls) || !f.ring || !f.visible) probs.push(`first Tab: ${JSON.stringify(f)}`);
  await p.keyboard.press("Enter");
  const onMain = await p.evaluate(() => location.hash === "#main" || document.activeElement?.id === "main" || !!document.activeElement?.closest("main"));
  if (!onMain) probs.push("skip link does not reach main");
  // 2. Header tab order: every stop visible with a ring, none hidden.
  await p.evaluate(() => { window.scrollTo(0, 0); document.activeElement?.blur(); });
  await p.goto(url, { waitUntil: "load" });
  const stops = [];
  for (let i = 0; i < 12; i++) { await p.keyboard.press("Tab"); f = await focusInfo(p); stops.push(f); if (f.tag === "body") break; }
  const header = stops.slice(1, 10).filter((s) => s.tag !== "body");
  for (const s of header) if (!s.visible || !s.ring) probs.push(`stop without visible focus: ${s.tag}.${s.cls} "${s.text}" ring=${s.ring} visible=${s.visible}`);
  // 3. Language menu by keyboard (desktop).
  if (w >= 1024) {
    const tog = p.locator(".lang__toggle"); await tog.focus(); await p.keyboard.press("Enter");
    const open = await p.evaluate(() => document.querySelector(".lang__toggle").getAttribute("aria-expanded"));
    if (open !== "true") probs.push("language menu does not open with Enter");
    await p.keyboard.press("Tab"); f = await focusInfo(p);
    const inMenu = await p.evaluate(() => !!document.activeElement.closest(".lang__menu"));
    // Opening focuses the current language; Tab moves to the next one, or out of the menu (which then closes).
    const closedAfterLeaving = !inMenu && (await p.evaluate(() => document.querySelector(".lang__toggle").getAttribute("aria-expanded"))) === "false";
    if ((!inMenu && !closedAfterLeaving) || !f.ring) probs.push(`Tab after opening language menu: ${JSON.stringify(f)}`);
    if (closedAfterLeaving) { await tog.focus(); await p.keyboard.press("Enter"); }
    await p.keyboard.press("Escape");
    const closed = await p.evaluate(() => document.querySelector(".lang__toggle").getAttribute("aria-expanded"));
    if (closed !== "false") probs.push("Escape does not close language menu");
  }
  // 4. Search overlay: opens, focus inside, Tab stays inside, Escape closes and returns focus.
  const sb = p.locator(w < 1024 ? 'nav.dock [data-open="search"]' : 'header [data-open="search"]'); await sb.focus(); await p.keyboard.press("Enter"); await p.waitForTimeout(400);
  f = await focusInfo(p); if (!f.inOverlay) probs.push(`search: focus not in overlay (${f.tag})`);
  for (let i = 0; i < 6; i++) { await p.keyboard.press("Tab"); f = await focusInfo(p); if (!f.inOverlay) { probs.push("search: Tab escapes the overlay"); break; } }
  await p.keyboard.press("Escape"); await p.waitForTimeout(700);
  const back = await p.evaluate(() => document.activeElement?.matches('[data-open="search"]') && document.querySelector("#search").hidden);
  if (!back) probs.push("search: Escape does not close/return focus");
  // 5. Mobile menu (phones).
  if (w < 1024) {
    const mb = p.locator(".nav__burger"); await mb.focus(); await p.keyboard.press("Enter"); await p.waitForTimeout(500);
    const exp = await p.evaluate(() => document.querySelector(".nav__burger").getAttribute("aria-expanded"));
    f = await focusInfo(p);
    if (exp !== "true") probs.push("burger aria-expanded not true");
    for (let i = 0; i < 20; i++) { await p.keyboard.press("Tab"); f = await focusInfo(p); if (!f.inOverlay && f.tag !== "BUTTON") { probs.push(`menu: Tab escapes to ${f.tag}.${f.cls}`); break; } if (!f.ring) { probs.push(`menu: no ring on ${f.text}`); break; } }
    await p.keyboard.press("Escape"); await p.waitForTimeout(1000);
    const closed = await p.evaluate(() => document.querySelector("#menu").hidden && document.querySelector(".nav__burger").getAttribute("aria-expanded") === "false");
    if (!closed) probs.push("menu: Escape does not close");
  }
  if (errs.length) probs.push("page errors: " + errs.join("|"));
  if (probs.length) bad++;
  console.log(`${probs.length ? "FAIL" : "ok  "} ${l} ${w} ${probs.join(" ; ")}`);
  await ctx.close();
}
await br.close();
console.log(bad ? `${bad} failing` : "keyboard: all ok");
