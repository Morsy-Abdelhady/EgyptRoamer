// Trip assistant end to end, 5 languages × desktop/phone: script not loaded before opening, launcher visible and
// clear of the dock, keyboard open → focus in the field, typed question and chip answered, links in the page's
// language, Tab stays in the dialog, axe on the open dialog, Escape closes and returns focus. Clear the local
// rate limit first (20 questions / 10 min): wp eval "delete er_as_* transients".
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const axe = fs.readFileSync("node_modules/axe-core/axe.min.js", "utf8");
const br = await chromium.launch({ channel: "chrome" });
const cases = [["en", 1440, "/contact/", "Luxor"], ["ar", 390, "/ar/", "الأهرامات"], ["zh", 390, "/zh/destinations/", "金字塔"], ["ru", 1024, "/ru/contact-ru/", "Нильский круиз"], ["de", 320, "/de/", "xyzzy"]];
let bad = 0;
for (const [l, w, path, q] of cases) {
  const ctx = await br.newContext({ viewport: { width: w, height: 820 } });
  await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => ["image", "media"].includes(r.request().resourceType()) ? r.abort() : r.continue());
  const p = await ctx.newPage(); const errs = []; p.on("pageerror", (e) => errs.push(e.message)); p.on("console", (m) => m.type() === "error" && !m.text().includes("net::") && errs.push(m.text()));
  const scripts = []; p.on("request", (r) => r.url().includes("assistant.js") && scripts.push(r.url()));
  await p.goto(B + path, { waitUntil: "load", timeout: 120000 }); await p.waitForTimeout(path.split("/").length <= 3 ? 3500 : 800);
  const probs = [];
  // Homepage on narrow phones: the launcher steps aside while the hero's buttons are under it (theme 1.2.11).
  // It must then be out of reach (hidden, so not focusable), and back once the hero has scrolled away.
  let stepped = "";
  if (await p.evaluate(() => document.body.classList.contains("assistant-clear"))) {
    const hidden = await p.evaluate(() => getComputedStyle(document.querySelector(".assistant-launch")).visibility === "hidden");
    if (!hidden) probs.push("launcher stepped aside but still visible");
    await p.evaluate(() => scrollTo(0, innerHeight * 1.5)); await p.waitForTimeout(1500);
    if (await p.evaluate(() => document.body.classList.contains("assistant-clear"))) probs.push("launcher did not come back after scrolling");
    stepped = " (stepped aside over the hero, back after scrolling)";
  }
  const loadedEarly = scripts.length;
  if (loadedEarly) probs.push("assistant.js loaded before opening");
  const launch = await p.evaluate(() => { const b = document.querySelector(".assistant-launch"); const r = b.getBoundingClientRect(); const d = document.querySelector("nav.dock")?.getBoundingClientRect(); const vis = getComputedStyle(b).display !== "none"; return { vis, r: [Math.round(r.left), Math.round(r.top), Math.round(r.right), Math.round(r.bottom)], overlapDock: d && d.height && getComputedStyle(document.querySelector("nav.dock")).display !== "none" ? !(r.bottom <= d.top || r.top >= d.bottom || r.right <= d.left || r.left >= d.right) : false, inView: r.right <= innerWidth && r.left >= 0 && r.bottom <= innerHeight, label: b.getAttribute("aria-label") || b.textContent.trim() }; });
  if (!launch.vis || launch.overlapDock || !launch.inView) probs.push("launcher " + JSON.stringify(launch));
  // keyboard: focus launcher, Enter
  await p.locator(".assistant-launch").focus(); await p.keyboard.press("Enter"); await p.waitForTimeout(700);
  const focusIn = await p.evaluate(() => document.activeElement?.id);
  if (focusIn !== "assistant-q") probs.push("focus after open: " + focusIn);
  await p.keyboard.type(q); await p.keyboard.press("Enter");
  await p.waitForSelector("#assistant .assistant__a", { timeout: 60000 }).catch(() => probs.push("no answer rendered"));
  const res = await p.evaluate((L) => { const a = [...document.querySelectorAll("#assistant .assistant__a")].pop(); if (!a) return { text: "(none)", links: [], wrongLang: [] }; const links = [...a.querySelectorAll("a")].map((x) => x.getAttribute("href").replace(location.origin, "")); return { text: a.textContent.slice(0, 90), links, wrongLang: links.filter((h) => L === "en" ? /^\/(ar|de|fr|it|es|ru|zh)\//.test(h) : !h.startsWith("/" + L + "/")) }; }, l);
  if (res.wrongLang.length) probs.push("wrong-language links " + res.wrongLang);
  // chip
  await p.locator("#assistant [data-assistant-ask]").first().click(); await p.waitForTimeout(2500);
  const answers = await p.locator("#assistant .assistant__a").count();
  if (answers < 2) probs.push("chip did not answer");
  // tab trap inside dialog
  let escaped = false; for (let i = 0; i < 25; i++) { await p.keyboard.press("Tab"); if (!(await p.evaluate(() => !!document.activeElement.closest("#assistant")))) { escaped = true; break; } }
  if (escaped) probs.push("Tab escaped the dialog");
  await p.addScriptTag({ content: axe });
  const ax = await p.evaluate(async () => (await axe.run("#assistant", { resultTypes: ["violations"] })).violations.map((v) => v.id + "×" + v.nodes.length));
  if (ax.length) probs.push("axe " + ax);
  const over = await p.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1);
  if (over) probs.push("page overflow");
  await p.screenshot({ path: `asst-${l}-${w}.png` });
  await p.keyboard.press("Escape"); await p.waitForTimeout(700);
  const back = await p.evaluate(() => document.activeElement?.classList.contains("assistant-launch") && document.getElementById("assistant").hidden);
  if (!back) probs.push("Escape did not close/return focus");
  if (errs.length) probs.push("errors " + errs.join("|"));
  if (probs.length) bad++;
  console.log(`${probs.length ? "FAIL" : "ok  "} ${l} ${w} launcher="${launch.label}"${stepped} q="${q}" → ${res.text.replace(/\s+/g, " ")} | links ${res.links.length} | ${probs.join(" ; ")}`);
  await ctx.close();
}
await br.close();
console.log(bad ? `${bad} failing` : "assistant: all ok");
