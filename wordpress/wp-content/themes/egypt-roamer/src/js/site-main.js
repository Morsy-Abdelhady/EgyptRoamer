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
// Experience booking bar: shown past the first screen, hidden again at the booking box it leads to (the story's
// closing section). A box pinned in the sidebar is not a reason to hide it: the bar stays, as the owner asked
// for every experience page (15 Oct), and remains after the article, where the sidebar ends.
safe("bookbar", () => {
  const bar = document.querySelector("[data-bookbar]");
  const href = bar?.querySelector("a")?.getAttribute("href") || "";
  const box = href.startsWith("#") ? document.getElementById(href.slice(1)) : null;
  if (!bar || !box) return;
  // The story pins its hero while the first chapters pass over it, so "past the hero" is read from the scroll.
  let past = false;
  let endIn = false;
  const update = () => { bar.hidden = !past || endIn; };
  const onScroll = () => { const p = window.scrollY > window.innerHeight * 0.8; if (p !== past) { past = p; update(); } };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
  if (!box.closest(".page-aside") && "IntersectionObserver" in window) {
    new IntersectionObserver(([e]) => { endIn = e.isIntersecting; update(); }, { threshold: 0.15 }).observe(box);
  }
});
// "#book" links (header, hero, dock, booking bar) when the box is in the sidebar (wide screens): on a guide the
// in-article box is hidden there, and a jump to a pinned box would scroll the reader back to where the sidebar
// starts. So: a box already in view is just called out; otherwise the page goes to where the sidebar starts.
safe("book-jump", () => {
  // Capture phase, ahead of the general in-page anchor scroll (nav.js), which would jump to the pinned box.
  document.addEventListener("click", (e) => {
    const a = e.target.closest && e.target.closest('a[href="#book"]');
    if (!a || a.closest(".menu")) return;
    const t = document.getElementById("book");
    if (t && t.getClientRects().length && !t.closest(".page-aside")) return;
    const box = t && t.getClientRects().length ? t : document.querySelector(".page-aside .offer-box");
    if (!box || getComputedStyle(box.closest(".page-aside") || box).display === "contents") return;
    e.preventDefault();
    e.stopPropagation();
    box.classList.remove("is-called");
    void box.offsetWidth;
    box.classList.add("is-called");
    setTimeout(() => box.classList.remove("is-called"), 1700);
    // The box is in a sticky sidebar: while the reader is inside the layout it is already in view; from
    // outside (past the article), go back to where the layout starts.
    const r = box.getBoundingClientRect();
    if (r.top < 0 || r.top > window.innerHeight * 0.5) {
      const top = (box.closest(".page-layout") || box).getBoundingClientRect().top + window.scrollY - 96;
      window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
    }
    box.setAttribute("tabindex", "-1");
    box.focus({ preventScroll: true });
  }, true);
});
