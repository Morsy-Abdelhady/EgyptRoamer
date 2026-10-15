/* ==========================================================================
   Egypt Roamer — inner pages bootstrap (WordPress)
   Only the shared chrome: navigation, search overlay, saved items, reveals,
   scroll progress and affiliate/newsletter behaviour. The cinematic homepage
   components (journey, moods, map, planner…) are not shipped to inner pages.
   ========================================================================== */
import { initFavorites } from "./components/favorites.js";
import { initNav } from "./components/nav.js";
import { initSearch } from "./components/search.js";
import { initSectionNav } from "./components/sections.js";
import { initAssistantLoader } from "./components/assistant-loader.js";
import { initWordFit } from "./components/fit.js";
import { initMoments } from "./components/moments.js";
import { initImmersion } from "./components/immersion.js";
import { initMagnetic, initReveals, initScrollProgress, initAffiliateLinks, initNewsletter, initImageFallback } from "./components/micro.js";

function safe(name, fn) {
  try {
    fn();
  } catch (err) {
    console.error(`[egypt-roamer] ${name} failed`, err);
  }
}

safe("title-fit", initWordFit); // first: before the page is laid out further
safe("images", initImageFallback);
safe("favorites", initFavorites);
safe("nav", initNav);
safe("assistant", initAssistantLoader);
safe("search", initSearch);
safe("sections", initSectionNav);
safe("moments", initMoments);
safe("immersion", initImmersion);
safe("magnetic", initMagnetic);
safe("reveals", initReveals);
safe("progress", initScrollProgress);
safe("affiliate", initAffiliateLinks);
safe("newsletter", initNewsletter);
// Story booking bar: shown once the hero's button has scrolled away, hidden again at the booking section.
safe("bookbar", () => {
  const bar = document.querySelector("[data-bookbar]");
  const end = document.getElementById("plan");
  if (!bar || !end || !("IntersectionObserver" in window)) return;
  // The story pins its hero while the first chapters pass over it, so "past the hero" is read from the scroll.
  let past = false;
  let endIn = false;
  const update = () => { bar.hidden = !past || endIn; };
  const onScroll = () => { const p = window.scrollY > window.innerHeight * 0.8; if (p !== past) { past = p; update(); } };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
  new IntersectionObserver(([e]) => { endIn = e.isIntersecting; update(); }, { threshold: 0.15 }).observe(end);
});
// "#book" links (header, hero, dock): on a guide at 1200px and wider the in-article box is hidden and its copy is
// in the sidebar, so the jump goes there instead of nowhere.
safe("book-jump", () => {
  document.addEventListener("click", (e) => {
    const a = e.target.closest && e.target.closest('a[href="#book"]');
    const t = a && document.getElementById("book");
    if (!a || (t && t.getClientRects().length)) return;
    const box = document.querySelector(".page-aside .offer-box");
    if (!box) return;
    e.preventDefault();
    // The box is in a sticky sidebar: while the reader is inside the layout it is already in view; from
    // outside (past the article), go back to where the layout starts.
    const r = box.getBoundingClientRect();
    if (r.top < 0 || r.bottom > window.innerHeight) {
      const top = (box.closest(".page-layout") || box).getBoundingClientRect().top + window.scrollY - 96;
      window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
    }
    box.setAttribute("tabindex", "-1");
    box.focus({ preventScroll: true });
  });
});
