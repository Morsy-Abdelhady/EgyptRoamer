/* ==========================================================================
   Experience story (template-parts/experience-story.php): loads each
   chapter's photograph about a screen before it is reached (the story starts
   right under the hero, where the browser's own lazy loading would fetch
   them all with the page), marks the chapter in view on the rail, and aims
   the walk at the door of the hero photograph. The story reads in full
   without this; the motion is CSS.
   ========================================================================== */

import { deferPhotos } from "./moments.js";

export function initImmersion(root = document) {
  root.querySelectorAll("[data-imm]").forEach((el) => {
    deferPhotos(el, "100% 0px");
    markCurrent(el);
    el.querySelectorAll("[data-walk]").forEach(measureWalk);
    el.querySelectorAll("[data-walk][data-focus]").forEach(aimWalk);
  });
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
