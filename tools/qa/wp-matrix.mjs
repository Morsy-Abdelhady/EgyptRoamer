import { chromium } from "playwright";
import fs from "fs";
const B = "http://127.0.0.1:8080";
const axe = fs.readFileSync("node_modules/axe-core/axe.min.js", "utf8");
const pages = ["/", "/destinations/", "/destinations/cairo/", "/experiences/pyramids-of-giza-sphinx-private-tour/", "/experiences/", "/guides/", "/contact/", "/?s=luxor", "/missing-page/", "/fr/", "/fr/destinations/le-caire/", "/ar/"];
const sizes = [390, 430, 768, 1024, 1440, 1920];
const b = await chromium.launch();
const overflow = [], errors = [], axeOut = {};
for (const w of sizes) for (const path of pages) {
  const p = await b.newPage({ viewport: { width: w, height: 900 }, reducedMotion: "reduce" });
  p.on("pageerror", (e) => errors.push(`${w} ${path} ${e}`));
  await p.goto(B + path); await p.waitForTimeout(path.endsWith("/") && path.length < 5 ? 1500 : 400);
  const r = await p.evaluate(() => {
    const vw = document.documentElement.clientWidth, sw = document.documentElement.scrollWidth;
    const clipped = [...document.querySelectorAll(".page-hero__title, .card__title, .facts dd, .btn, .crumbs")].filter((el) => el.scrollWidth > el.clientWidth + 2).map((el) => el.className.split(" ")[0]);
    return { vw, sw, clipped: [...new Set(clipped)] };
  });
  if (r.sw > r.vw || r.clipped.length) overflow.push(`${w} ${path} sw=${r.sw}/${r.vw} clipped=${r.clipped}`);
  if (w === 390 || w === 1440) {
    await p.addScriptTag({ content: axe });
    const v = await p.evaluate(async () => (await axe.run(document, { resultTypes: ["violations"] })).violations.map((v) => `${v.impact}:${v.id}(${v.nodes.length}) ${v.nodes.slice(0, 2).map((n) => n.target.join(" ")).join(" ; ")}`));
    if (v.length) axeOut[`${w} ${path}`] = v;
  }
  await p.close();
}
await b.close();
console.log("OVERFLOW/CLIPPING:", overflow.length ? overflow : "none");
console.log("PAGE ERRORS:", errors.length ? errors : "none");
console.log("AXE:", JSON.stringify(axeOut, null, 1));
