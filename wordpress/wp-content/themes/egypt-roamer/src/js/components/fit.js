/* Display words that must stay on one line (hero word, scene titles) keep the approved size while it
   fits and shrink just enough when a translation is wider than its column ("КРАСНОЕ МОРЕ", "ROTES
   MEER" on a phone). Measured on a canvas with the computed font, so scene titles that are hidden
   until their scene plays are fitted too. */
import { $$ } from "../utils.js";

export function initFit() {
  const els = $$(".scene__title, .hero__title .t-display");
  if (!els.length) return;
  const ctx = document.createElement("canvas").getContext("2d");
  const lang = document.documentElement.lang || undefined;
  const run = () => {
    const vw = document.documentElement.clientWidth;
    els.forEach((el) => {
      el.style.fontSize = "";
      const cs = getComputedStyle(el);
      const box = el.closest(".container") || el.parentElement;
      const bs = getComputedStyle(box);
      const outer = box.clientWidth > 0 ? box.clientWidth : Math.min(vw, parseFloat(bs.maxWidth) || vw);
      const avail = outer - (parseFloat(bs.paddingLeft) || 0) - (parseFloat(bs.paddingRight) || 0);
      let text = el.textContent.replace(/\s+/g, " ").trim();
      if (cs.textTransform === "uppercase") text = text.toLocaleUpperCase(lang);
      ctx.font = `${cs.fontStyle} ${cs.fontWeight} ${cs.fontSize} ${cs.fontFamily}`;
      const size = parseFloat(cs.fontSize);
      // The rendered width when the title is laid out; the canvas estimate for hidden scene titles.
      const range = document.createRange();
      range.selectNodeContents(el);
      const rendered = range.getBoundingClientRect().width;
      const width = rendered > 0 ? rendered : ctx.measureText(text).width + (parseFloat(cs.letterSpacing) || 0) * text.length;
      // A measured width is exact; the canvas estimate keeps 4% headroom (per-letter animation spans).
      const limit = rendered > 0 ? avail : avail * 0.96;
      if (width > limit) el.style.fontSize = `${Math.floor((size * limit * 0.98) / width)}px`;
    });
  };
  run();
  // Again once the display font is in (a fallback font measures narrower).
  document.fonts?.ready.then(run);
  document.fonts?.addEventListener?.("loadingdone", run);
  window.addEventListener("load", run);
  let t;
  window.addEventListener("resize", () => {
    clearTimeout(t);
    t = setTimeout(run, 120);
  });
}

/* Page titles wrap, but a single word must still fit its line: a long Russian or German word on a 320 px
   phone ("Индивидуальная") was broken mid-word without a hyphen (browsers on Windows have no Russian
   hyphenation). Shrink the title just enough for its longest word; titles that fit are untouched. */
export function initWordFit() {
  const els = $$(".page-hero__title");
  if (!els.length) return;
  const ctx = document.createElement("canvas").getContext("2d");
  const run = () => {
    els.forEach((el) => {
      el.style.fontSize = "";
      const cs = getComputedStyle(el);
      const avail = el.clientWidth;
      if (!avail) return;
      ctx.font = `${cs.fontStyle} ${cs.fontWeight} ${cs.fontSize} ${cs.fontFamily}`;
      const ls = parseFloat(cs.letterSpacing) || 0;
      // Chinese (and other CJK) text may break between any two characters: each is its own unit, or a whole
      // title with no spaces counts as one "word" and is shrunk to a single line.
      const words = el.textContent.split(/\s+/).flatMap((w) => w.match(/[⺀-鿿가-힯豈-﫿＀-￯]|[^⺀-鿿가-힯豈-﫿＀-￯]+/g) || []);
      const widest = Math.max(...words.map((w) => ctx.measureText(w).width + ls * w.length));
      if (widest > avail) el.style.fontSize = `${Math.floor((parseFloat(cs.fontSize) * avail * 0.97) / widest)}px`;
    });
  };
  run();
  document.fonts?.ready.then(run);
  let t;
  window.addEventListener("resize", () => {
    clearTimeout(t);
    t = setTimeout(run, 120);
  });
}
