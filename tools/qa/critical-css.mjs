// Homepage critical CSS (owner, 28 Oct: mobile LCP). Writes assets/css/critical-home.css: the rules of
// bundle-home.css that style what is on the first screen, so the homepage can print them inline and load the
// bundle without blocking the first paint (inc/assets.php).
//
// A rule is kept when one of its selectors (pseudo-elements and :hover/:focus-style states stripped) matches an
// element that intersects the first screen, in any of 8 languages × 7 widths, before and after the page script
// runs. @font-face, :root custom properties and @keyframes used by kept rules are kept too. Media and supports
// wrappers are kept around kept rules. The output's first line is the sha1 of bundle-home.css: the theme uses
// the file only while it matches the bundle on disk, so a stale file falls back to the blocking stylesheet.
//
// Usage: BASE=https://egyptroamer.com node tools/qa/critical-css.mjs   (rerun after any change to the bundle)
import { chromium } from "playwright";
import { createHash } from "node:crypto";
import { readFileSync, writeFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";

const B = (process.env.BASE || "http://127.0.0.1:8080").replace(/\/$/, "");
const CSS_DIR = join(dirname(fileURLToPath(import.meta.url)), "../../wordpress/wp-content/themes/egypt-roamer/assets/css");
const bundle = readFileSync(join(CSS_DIR, "bundle-home.css"));
const sha = createHash("sha1").update(bundle).digest("hex");

const LANGS = ["", "/ar", "/de", "/fr", "/it", "/es", "/ru", "/zh"];
const SIZES = [[360, 740], [390, 844], [412, 915], [768, 1024], [1280, 800], [1440, 900], [1920, 1080]];

const br = await chromium.launch({ channel: "chrome" });
const kept = new Set();
const live = await (await fetch(B + "/?x=" + Date.now())).text();
const liveSha = (live.match(/bundle-home\.css\?ver=([^'"&]+)/) || [])[1];
console.log("bundle sha1", sha, "| live bundle ver", liveSha);

// Three states of the first screen: no JavaScript; the page's inline scripts only (the "js" class set, the homepage
// bundle not run yet: the state in which the critical CSS is on its own, since the bundle scripts wait for the
// stylesheet); and after the homepage scripts ran.
const collect = async (lang, [w, h], mode) => {
  const c = await br.newContext({ viewport: { width: w, height: h }, isMobile: w < 900, hasTouch: w < 900, javaScriptEnabled: mode !== "nojs" });
  await c.addInitScript(() => { try { localStorage.setItem("er-nocount", "1"); } catch (e) {} });
  const p = await c.newPage();
  if (mode === "inline") await p.route(/(gsap|ScrollTrigger|lenis)\.min\.js|\/js\/home\.js|\/js\/locales\//, (r) => r.abort());
  await p.goto(B + lang + "/?x=" + Date.now(), { waitUntil: "load", timeout: 120000 });
  await p.waitForTimeout(mode === "full" ? 2500 : 800);
  const paths = await p.evaluate(() => {
    const sheet = [...document.styleSheets].find((s) => s.href && s.href.includes("bundle-home.css"));
    if (!sheet) return null;
    document.getElementById("er-critical-css")?.remove(); // measure against the bundle alone, not a previous output
    const H = innerHeight, W = innerWidth;
    const inFold = () => { const f = new Set(); for (const el of document.querySelectorAll("body *")) { const r = el.getBoundingClientRect(); const cs = getComputedStyle(el);
      if (r.bottom > 0 && r.top < H && r.right > 0 && r.left < W && (r.width || r.height) && cs.visibility !== "hidden" && cs.opacity !== "0") f.add(el); } return f; };
    const strip = (s) => s.replace(/::?(before|after|placeholder|marker|selection|backdrop|first-letter|first-line|-webkit-[a-z-]+|-moz-[a-z-]+)\b(\([^)]*\))?/g, "")
      .replace(/:(hover|focus|focus-visible|focus-within|active|visited|target|checked|disabled|placeholder-shown)\b/g, "").trim() || "*";
    const K = new Set();
    const walk = (rules, prefix, fold) => {
      [...rules].forEach((r, i) => {
        const key = prefix + i;
        if (K.has(key)) return;
        if (r instanceof CSSStyleRule) {
          const always = /^(:root|html|body)\b/.test(r.selectorText);
          if (always || r.selectorText.split(",").some((one) => { try { for (const el of document.querySelectorAll(strip(one))) if (fold.has(el)) return true; } catch (e) {} return false; })) K.add(key);
        } else if (r instanceof CSSMediaRule) { if (matchMedia(r.conditionText).matches) walk(r.cssRules, key + ".", fold); }
        else if (r instanceof CSSSupportsRule) { if (CSS.supports(r.conditionText)) walk(r.cssRules, key + ".", fold); }
        else if (r instanceof CSSFontFaceRule) K.add(key);
      });
    };
    const emit = (rules, prefix) => { let s = ""; [...rules].forEach((r, i) => { const key = prefix + i;
      if (r instanceof CSSMediaRule || r instanceof CSSSupportsRule) { const inner = emit(r.cssRules, key + "."); if (inner) s += (r instanceof CSSMediaRule ? "@media " : "@supports ") + r.conditionText + "{" + inner + "}"; }
      else if (K.has(key)) s += r.cssText; }); return s; };
    // 1. what the full stylesheet shows on the first screen
    walk(sheet.cssRules, "", inFold());
    // 2. repeat with only the kept rules applied (the state before the bundle arrives) until nothing new shows up
    const tmp = document.createElement("style"); sheet.ownerNode.after(tmp);
    for (let round = 0; round < 8; round++) {
      const before = K.size;
      tmp.textContent = emit(sheet.cssRules, ""); sheet.disabled = true;
      const f = inFold();
      sheet.disabled = false; walk(sheet.cssRules, "", f);
      if (K.size === before) break;
    }
    tmp.remove();
    return [...K];
  });
  await c.close();
  if (!paths) throw new Error("bundle-home.css not found on " + lang);
  paths.forEach((k) => kept.add(k));
};

const jobs = [];
for (const lang of LANGS) for (const size of SIZES) for (const mode of ["nojs", "inline", "full"]) jobs.push([lang, size, mode]);
for (let i = 0; i < jobs.length; i += 6) await Promise.all(jobs.slice(i, i + 6).map((j) => collect(...j)));

// Serialize from the live bundle in its original order: kept rules, their wrappers, and used @keyframes.
const p = await (await br.newContext()).newPage();
await p.goto(B + "/?x=" + Date.now(), { waitUntil: "load" });
const css = await p.evaluate((keys) => {
  const K = new Set(keys);
  const sheet = [...document.styleSheets].find((s) => s.href && s.href.includes("bundle-home.css"));
  const anims = new Set();
  const emit = (rules, prefix) => {
    let s = "";
    [...rules].forEach((r, i) => {
      const key = prefix + i;
      if (r instanceof CSSMediaRule || r instanceof CSSSupportsRule) {
        const inner = emit(r.cssRules, key + ".");
        if (inner) s += (r instanceof CSSMediaRule ? "@media " : "@supports ") + r.conditionText + "{" + inner + "}";
      } else if (K.has(key)) {
        s += r.cssText;
        const a = r.style && (r.style.animationName || r.style.animation);
        if (a) a.split(/[\s,]+/).forEach((n) => anims.add(n));
      }
    });
    return s;
  };
  let out = emit(sheet.cssRules, "");
  for (const r of sheet.cssRules) if (r instanceof CSSKeyframesRule && anims.has(r.name)) out += r.cssText;
  return out;
}, [...kept]);
await br.close();

if (liveSha && !live.includes(sha.slice(0, 8)) && process.env.ALLOW_VER_MISMATCH !== "1") {
  // The live ?ver= is a timestamp, not a hash: compare the files instead.
  const liveCss = Buffer.from(await (await fetch(B + "/wp-content/themes/egypt-roamer/assets/css/bundle-home.css?x=" + Date.now())).arrayBuffer());
  if (createHash("sha1").update(liveCss).digest("hex") !== sha) {
    console.error("The live bundle differs from the local one: deploy first, then rerun.");
    process.exit(1);
  }
}
const file = join(CSS_DIR, "critical-home.css");
writeFileSync(file, `/* bundle-home.css sha1 ${sha} — GENERATED by tools/qa/critical-css.mjs, do not edit */\n` + css.replace(/\s*\n\s*/g, " ") + "\n");
console.log("critical-home.css", Buffer.byteLength(css), "bytes from", kept.size, "rules; bundle", bundle.length, "bytes");
