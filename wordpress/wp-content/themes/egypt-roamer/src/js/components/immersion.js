/* ==========================================================================
   Experience story (template-parts/experience-story.php): loads each
   chapter's photograph about a screen before it is reached (the story starts
   right under the hero, where the browser's own lazy loading would fetch
   them all with the page), marks the chapter in view on the rail, and aims
   the walk at the door of the hero photograph, and moves the story's
   animations with the scroll. The story reads in full without this.
   ========================================================================== */

import { deferPhotos } from "./moments.js";

export function initImmersion(root = document) {
  root.querySelectorAll("[data-imm]").forEach((el) => {
    deferPhotos(el, "100% 0px");
    markCurrent(el);
    el.querySelectorAll("[data-walk]").forEach(measureWalk);
    el.querySelectorAll("[data-walk][data-focus]").forEach(aimWalk);
    scrubMotion(el);
  });
}

/* --------------------------------------------------------------------------
   Scroll-driven motion. The CSS (pages.css, "Motion") declares each animation
   paused, 1s long, under .xs-motion; here each one is moved to the point the
   scroll has reached. One table, every browser (phones and tablets included,
   whether or not they have CSS scroll timelines).

   Each animation runs while its subject crosses a stretch of the screen, named
   as in CSS view timelines:
     cover    from the subject's top at the bottom of the screen to its bottom at the top
     contain  while it fills the screen (or, smaller, while it is wholly on it)
     entry    while its top comes up from the bottom of the screen
   with a percentage of that stretch or a length in vh.
   -------------------------------------------------------------------------- */
const MOTION = {
  "xw-leave": [(t) => t.closest(".xw-walk"), "contain", "0vh", "contain", "45vh"],
  "xw-dolly": [(t) => t.closest(".xw-walk"), "contain", "0%", "contain", "100%"],
  "xw-enter": [(t) => t.closest(".xw-walk")?.querySelector(".xw-door"), "entry", "0%", "contain", "100%"],
  "xw-night": [(t) => t.closest(".xw-walk")?.querySelector(".xw-cap--dawn"), "cover", "0%", "cover", "100%"],
  "xw-dark": [(t) => t.closest(".xw-walk")?.querySelector(".xw-door"), "entry", "55%", "contain", "100%"],
  "xw-fill": [(t) => t.closest(".xw-journey"), "contain", "0%", "contain", "100%"],
  "xs-rise": [(t) => t, "entry", "0%", "cover", "35%"],
  "xs-adjust": [(t) => t.closest(".imm-step"), "entry", "70%", "cover", "50%"],
  "xs-lit": [(t) => t.closest(".imm-step"), "cover", "22%", "cover", "55%"],
  "xs-ray": [(t) => t.closest(".imm-step"), "entry", "90%", "cover", "45%"],
  "xs-flash": [(t) => t, "entry", "0%", "cover", "40%"],
  "xs-settle": [(t) => t, "entry", "0%", "cover", "45%"],
  "xs-close": [(t) => t.closest(".imm-step"), "entry", "20%", "cover", "50%"],
  "xs-tilt": [(t) => t.closest(".imm-step"), "entry", "0%", "cover", "60%"],
};

function scrubMotion(root) {
  const quiet = window.matchMedia("(prefers-reduced-motion: reduce)");
  if (quiet.matches || typeof root.getAnimations !== "function") return;
  root.classList.add("xs-motion");

  let items = [];
  let vh = window.innerHeight;
  // Where each subject sits on the page; measured on layout changes, not on every frame.
  const measure = () => {
    vh = window.innerHeight;
    const y = window.scrollY;
    items = root.getAnimations({ subtree: true }).flatMap((anim) => {
      const spec = MOTION[anim.animationName];
      const target = anim.effect && anim.effect.target;
      const subject = spec && target && spec[0](target);
      if (!subject) return [];
      anim.pause();
      const r = subject.getBoundingClientRect();
      const top = r.top + y;
      const h = r.height;
      return [{ anim, a: edge(spec[1], spec[2], top, h, vh), b: edge(spec[3], spec[4], top, h, vh) }];
    });
    update();
  };
  const update = () => {
    const y = window.scrollY;
    for (const { anim, a, b } of items) {
      const p = b > a ? Math.min(1, Math.max(0, (y - a) / (b - a))) : y >= b ? 1 : 0;
      anim.currentTime = p * 1000; // the CSS animations are 1s long
    }
  };
  let queued = false;
  window.addEventListener("scroll", () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => { queued = false; update(); });
  }, { passive: true });
  let timer = 0;
  const later = () => { clearTimeout(timer); timer = setTimeout(measure, 120); };
  window.addEventListener("resize", later, { passive: true });
  window.addEventListener("load", measure);
  if ("ResizeObserver" in window) new ResizeObserver(later).observe(root);
  quiet.addEventListener?.("change", () => { if (quiet.matches) { root.classList.remove("xs-motion"); items = []; } });
  measure();
}

// The scroll position at which a stretch starts or ends: name (cover / contain / entry), offset (% or vh).
function edge(name, offset, top, h, vh) {
  const len = offset.endsWith("vh") ? (parseFloat(offset) / 100) * vh : null;
  const pct = parseFloat(offset) / 100;
  let from;
  let to;
  if (name === "cover") { from = top - vh; to = top + h; }
  else if (name === "entry") { from = top - vh; to = top - vh + Math.min(h, vh); }
  else { from = h >= vh ? top : top - vh + h; to = h >= vh ? top + h - vh : top; } // contain
  return len !== null ? from + len : from + (to - from) * pct;
}

function markCurrent(el) {
  const steps = [...el.querySelectorAll("[data-imm-step]")];
  const links = new Map([...el.querySelectorAll("[data-imm-link]")].map((a) => [a.hash.slice(1), a]));
  if (!steps.length || !links.size || !("IntersectionObserver" in window)) return;
  // The chapter crossing the middle of the screen is the current one.
  const io = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (!e.isIntersecting) return;
      links.forEach((a) => a.removeAttribute("aria-current"));
      const a = links.get(e.target.id);
      if (a) a.setAttribute("aria-current", "step");
    }),
    { rootMargin: "-50% 0px -49% 0px" }
  );
  steps.forEach((s) => io.observe(s));
}

// The walk's hero is held on screen by CSS; when it is taller than the screen, CSS holds it by its bottom edge,
// which needs its height.
function measureWalk(walk) {
  const bg = walk.querySelector(".xw-walk__bg");
  if (!bg || !("ResizeObserver" in window)) return;
  new ResizeObserver(() => walk.style.setProperty("--xw-h", `${bg.offsetHeight}px`)).observe(bg);
}

// data-focus="x y ratio": the door in the original photograph (percent) and its width ÷ height. The file
// served is a centred crop of it, shown with object-fit: cover, so the door moves on screen with both; the
// walk zooms around where it actually is.
function aimWalk(walk) {
  const img = walk.querySelector(".page-hero__media img");
  const [fx, fy, ratio] = walk.dataset.focus.split(" ").map(Number);
  if (!img || !ratio) return;
  const aim = () => {
    if (!img.naturalWidth || !img.offsetHeight) return;
    let x = fx / 100;
    let y = fy / 100;
    const served = img.naturalWidth / img.naturalHeight;
    if (served < ratio) x = 0.5 + (x - 0.5) * (ratio / served);
    else if (served > ratio) y = 0.5 + (y - 0.5) * (served / ratio);
    const box = img.offsetWidth / img.offsetHeight;
    if (box > served) y = 0.5 + (y - 0.5) * (box / served);
    else x = 0.5 + (x - 0.5) * (served / box);
    walk.style.setProperty("--xw-ox", `${(x * 100).toFixed(2)}%`);
    walk.style.setProperty("--xw-oy", `${(y * 100).toFixed(2)}%`);
  };
  if (img.complete) aim();
  img.addEventListener("load", aim);
  window.addEventListener("resize", aim, { passive: true });
}
