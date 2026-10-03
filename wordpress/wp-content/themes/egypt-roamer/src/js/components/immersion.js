/* ==========================================================================
   Experience Immersion (template-parts/experience-immersion.php): loads each
   stage's photograph about a screen before it is reached (the section starts
   right under the hero, where the browser's own lazy loading would fetch
   them all with the page), and marks the stage in view on the route at the
   top. The story reads in full without this; the motion is CSS.
   ========================================================================== */

import { deferPhotos } from "./moments.js";

export function initImmersion(root = document) {
  root.querySelectorAll("[data-imm]").forEach((el) => {
    deferPhotos(el, "100% 0px");
    markCurrent(el);
  });
}

function markCurrent(el) {
  const steps = [...el.querySelectorAll("[data-imm-step]")];
  const links = new Map([...el.querySelectorAll("[data-imm-link]")].map((a) => [a.hash.slice(1), a]));
  if (!steps.length || !links.size || !("IntersectionObserver" in window)) return;
  // The stage crossing the middle of the screen is the current one.
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
