// Trip assistant launcher on the homepage, keyboard only, narrow phones (theme 1.2.11: the launcher steps aside
// while the hero buttons are under it). Tab from the top of the page until the launcher has focus: it must be
// visible whenever focused and never focused while hidden; Enter opens the drawer with focus in the question
// field; Escape closes it and returns focus to a visible launcher; scrolling back to the hero while the launcher
// has focus must not hide it. Usage: node launcher-keyboard.mjs (BASE=… for another host).
import { chromium } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const br = await chromium.launch({ channel: "chrome" });
let bad = 0;
const cases = [["en", 320, 700], ["de", 320, 700], ["de", 360, 740], ["ar", 320, 700], ["en", 360, 740]];
for (let ci = 0, retried = false; ci < cases.length; ci++) {
  const [l, w, h] = cases[ci];
  const p = await (await br.newContext({ viewport: { width: w, height: h } })).newPage();
  // A local file that failed to load (the dev proxy drops connections now and then) is an environment
  // failure, not a product one: such a case is run once more.
  const failed = [];
  p.on("requestfailed", (r) => r.url().startsWith(B) && failed.push(r.url().replace(B, "")));
  await p.goto(`${B}/${l === "en" ? "" : l + "/"}`, { waitUntil: "load", timeout: 180000 });
  await p.waitForTimeout(4000);
  const probs = [];
  const startHidden = await p.evaluate(() => getComputedStyle(document.querySelector(".assistant-launch")).visibility === "hidden");
  let tabs = 0, reached = false;
  for (; tabs < 400; tabs++) {
    await p.keyboard.press("Tab");
    const s = await p.evaluate(() => {
      const L = document.querySelector(".assistant-launch");
      return { on: document.activeElement === L, vis: getComputedStyle(L).visibility };
    });
    if (s.on) {
      await p.waitForTimeout(400);
      const vis = await p.evaluate(() => getComputedStyle(document.querySelector(".assistant-launch")).visibility);
      if (vis === "hidden") probs.push(`focused while hidden (tab ${tabs + 1})`);
      reached = true;
      break;
    }
  }
  if (!reached) probs.push("launcher never reached by Tab");
  else {
    await p.keyboard.press("Enter"); await p.waitForTimeout(800);
    const open = await p.evaluate(() => ({ hidden: document.getElementById("assistant").hidden, focus: document.activeElement?.id }));
    if (open.hidden || open.focus !== "assistant-q") probs.push("Enter: " + JSON.stringify(open));
    await p.keyboard.press("Escape"); await p.waitForTimeout(800);
    const back = await p.evaluate(() => { const L = document.querySelector(".assistant-launch"); return { focus: document.activeElement === L, vis: getComputedStyle(L).visibility }; });
    if (!back.focus || back.vis === "hidden") probs.push("Escape: " + JSON.stringify(back));
    // focused launcher, page back at the hero: must stay visible and focused
    await p.evaluate(() => scrollTo(0, 0)); await p.waitForTimeout(1500);
    const top = await p.evaluate(() => { const L = document.querySelector(".assistant-launch"); return { focus: document.activeElement === L, vis: getComputedStyle(L).visibility }; });
    if (!top.focus || top.vis === "hidden") probs.push("focused at the hero: " + JSON.stringify(top));
    // focus leaves: it may step aside again (only if it would cover the hero buttons)
    await p.evaluate(() => document.activeElement.blur()); await p.waitForTimeout(600);
  }
  if (probs.length && failed.length && !retried) {
    console.log(`retry ${l} ${w}: local requests failed (${failed.slice(0, 3).join(", ")})`);
    await p.context().close();
    retried = true; ci--;
    continue;
  }
  retried = false;
  if (probs.length) bad++;
  console.log(`${probs.length ? "FAIL" : "ok  "} ${l} ${w} hidden at top: ${startHidden} | reached after ${tabs + 1} Tab | ${probs.join(" ; ")}`);
  await p.context().close();
}
await br.close();
console.log(bad ? `${bad} failing` : "launcher keyboard: all ok");
