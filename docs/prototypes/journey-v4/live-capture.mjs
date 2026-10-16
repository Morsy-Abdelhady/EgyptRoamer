// Captures the live site's "Find your Egypt" and "The map" sections (owner, 16 Oct: use them in proposal 4 in place of
// the prototype's own versions). Read-only: it loads https://egyptroamer.com, clicks each mood and each map place, and
// records the rendered markup of every state, the CSS rules those elements use, and the icons they reference.
// Images (the site's own hot-linked Unsplash photos) are embedded as data URIs, since the artifact can't load other hosts.
import fs from "node:fs";
import { chromium } from "playwright-core";
const br = await chromium.launch({ channel: "chrome" });
const pg = await (await br.newContext({ viewport: { width: 1440, height: 1000 } })).newPage();
await pg.goto("https://egyptroamer.com/", { waitUntil: "networkidle", timeout: 90000 });
for (const id of ["#moods", "#map"]) { await pg.evaluate(s => document.querySelector(s).scrollIntoView(), id); await pg.waitForTimeout(1500); }
await pg.evaluate(() => document.querySelector("#moods").scrollIntoView()); await pg.waitForTimeout(1200);

// every state of the moods section
const moods = [];
for (const id of await pg.$$eval("#moods [data-mood]", bs => bs.map(b => b.dataset.mood))) {
  await pg.click(`#moods [data-mood="${id}"]`); await pg.waitForTimeout(1200);
  moods.push(await pg.evaluate(id => ({ id, style: document.querySelector("#moods").getAttribute("style"), body: document.querySelector("#moods .moods__body").innerHTML }), id));
}
await pg.click(`#moods [data-mood="${moods[0].id}"]`); await pg.waitForTimeout(1200);
const moodsHTML = await pg.evaluate(() => {
  const m = document.querySelector("#moods").cloneNode(true);
  m.querySelectorAll("img[data-srcset]").forEach(i => { const c = i.dataset.srcset.split(",").map(x => x.trim().split(" ")[0]); i.setAttribute("src", c[Math.min(2, c.length - 1)]); i.removeAttribute("data-srcset"); });
  m.querySelector("[data-mood-bg]").removeAttribute("style");
  return m.outerHTML;
});

// every state of the map
await pg.evaluate(() => document.querySelector("#map").scrollIntoView()); await pg.waitForTimeout(1200);
const places = [];
for (const id of await pg.$$eval("#map [data-map-item]", bs => bs.map(b => b.dataset.mapItem))) {
  await pg.click(`#map [data-map-item="${id}"]`); await pg.waitForTimeout(900);
  places.push(await pg.evaluate(id => ({ id, world: document.querySelector("#map [data-map-world]").innerHTML, list: document.querySelector("#map [data-map-list]").innerHTML, card: document.querySelector("#map [data-map-card]").outerHTML }), id));
}
await pg.click(`#map [data-map-item="${places[0].id}"]`); await pg.waitForTimeout(900);
const mapHTML = await pg.evaluate(() => document.querySelector("#map").outerHTML);

// CSS rules whose selector (without pseudo-classes) matches something in the two sections, media rules included
const css = await pg.evaluate(([moods, places]) => {
  // every captured state takes part in the match, so rules for other moods and other places are kept too
  const box = document.createElement("div"); box.hidden = true; document.body.appendChild(box);
  for (const m of moods) { const c = document.querySelector("#moods").cloneNode(true); c.removeAttribute("id"); c.querySelector(".moods__body").innerHTML = m.body; box.appendChild(c); }
  for (const p of places) { const c = document.querySelector("#map").cloneNode(true); c.removeAttribute("id"); c.querySelector("[data-map-world]").innerHTML = p.world; c.querySelector("[data-map-list]").innerHTML = p.list; c.querySelector("[data-map-card]").outerHTML = p.card; box.appendChild(c); }
  const scope = [document.querySelector("#moods"), document.querySelector("#map"), box];
  const strip = s => s.replace(/::?(hover|focus-visible|focus-within|focus|active|before|after|placeholder|marker|first-letter|first-line|-webkit-[\w-]+)(\([^)]*\))?/g, "").replace(/:not\(\)/g, "") || "*";
  const hits = sel => sel.split(",").some(p => { const q = strip(p.trim()); try { return scope.some(r => r.matches(q) || r.querySelector(q)); } catch { return false; } });
  const out = []; const keys = new Set();
  const walk = (rules, wrap) => { for (const r of rules) {
    if (r.type === 1) { if (r.selectorText === ":root" || /^:root\b/.test(r.selectorText)) out.push([wrap, ".live", r.style.cssText]); else if (!/(^|[\s,>+~(])(html|body)(?![\w-])|\[dir/.test(r.selectorText) && hits(r.selectorText)) out.push([wrap, r.selectorText.split(",").map(p => ".live " + p.trim()).join(","), r.style.cssText]); }
    else if (r.type === 4) walk(r.cssRules, r.conditionText || r.media.mediaText);
    else if (r.type !== 7 && r.cssRules) walk(r.cssRules, wrap);
    else if (r.type === 7) keys.add(r.cssText);
  } };
  for (const s of document.styleSheets) { try { walk(s.cssRules, null); } catch {} }
  box.remove();
  return { rules: out, keyframes: [...keys] };
}, [moods, places]);

// icons the sections use
const sprite = await pg.evaluate(() => {
  const ids = new Set([...document.querySelectorAll("#moods use, #map use")].map(u => u.getAttribute("href")));
  return [...ids].map(h => document.querySelector(h)?.outerHTML || "").join("");
});
const fonts = await pg.evaluate(() => [...document.querySelectorAll('link[href*="fonts.googleapis"]')].map(l => l.href));
await br.close();

// embed images
let all = JSON.stringify({ moodsHTML, moods, mapHTML, places, css, sprite }).replace(/\s(?:data-)?srcset=\\"[^"]*?\\"/g, "").replace(/\ssizes=\\"[^"]*?\\"/g, "");
const urls = [...new Set(all.match(/https:\/\/images\.unsplash\.com\/[^"'\s)\\]+/g) || [])];
const map = {};
for (const u of urls) {
  const clean = u.replace(/&amp;/g, "&");
  const r = await fetch(clean); const b = Buffer.from(await r.arrayBuffer());
  map[u] = "data:image/jpeg;base64," + b.toString("base64");
}
// one copy of each photo: the markup carries a token, the page swaps it for the data URI when it renders
const imgs = []; for (const [u, d] of Object.entries(map)) { all = all.split(u).join("@@L" + imgs.length + "@@"); imgs.push(d); }
fs.writeFileSync("live/live-sections.json", all);
fs.writeFileSync("live/live-imgs.json", JSON.stringify(imgs));
console.log("moods", moods.length, "places", places.length, "rules", css.rules.length, "keyframes", css.keyframes.length, "images", urls.length, "KB", Math.round(all.length / 1024), "imgKB", Math.round(JSON.stringify(imgs).length/1024), "fonts", fonts);
