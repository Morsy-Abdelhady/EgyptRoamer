// axe with each overlay open (saved, search, mobile menu, assistant): the page-level runs only see them closed.
import { chromium } from "playwright";
import fs from "fs";
const axe = fs.readFileSync("node_modules/axe-core/axe.min.js", "utf8");
const br = await chromium.launch({ channel: "chrome" });
for (const [l, w, path] of [["en", 1440, "/contact/"], ["ar", 390, "/ar/contact-ar/"], ["zh", 1440, "/zh/"]]) {
  const p = await (await br.newContext({ viewport: { width: w, height: 820 } })).newPage();
  await p.goto((process.env.BASE || "http://127.0.0.1:8080") + path, { waitUntil: "load", timeout: 120000 }); await p.waitForTimeout(path.length <= 4 ? 3500 : 800);
  await p.addScriptTag({ content: axe });
  const out = [];
  for (const id of ["saved", "search", "menu", "assistant"]) {
    if (id === "menu" && w >= 1024) continue;
    const ok = await p.evaluate((id) => { const b = [...document.querySelectorAll(`[data-open="${id}"]`)].find((b) => b.offsetParent || getComputedStyle(b).position === "fixed"); b?.click(); return !!b; }, id);
    await p.waitForTimeout(1000);
    const v = await p.evaluate(async () => (await axe.run(document, { resultTypes: ["violations"] })).violations.map((x) => x.id + "×" + x.nodes.length));
    out.push(`${id}${ok ? "" : "(no opener)"}: ${v.join(",") || "0"}`);
    await p.keyboard.press("Escape"); await p.waitForTimeout(1000);
  }
  console.log(l, w, out.join(" | "));
}
await br.close();
