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
  // With the sidebar shown (wide screens), the bar sits over the article column only, never over the sidebar.
  const layout = document.querySelector(".page-layout--aside");
  const aside = layout?.querySelector(".page-aside");
  const fit = () => {
    const col = aside && getComputedStyle(aside).display !== "contents" ? layout.firstElementChild : null;
    if (!col || col === aside) {
      bar.removeAttribute("data-in-column");
      return;
    }
    const r = col.getBoundingClientRect();
    bar.style.setProperty("--bar-x", `${r.left + r.width / 2}px`);
    bar.style.setProperty("--bar-w", `${r.width}px`);
    bar.setAttribute("data-in-column", "");
  };
  const update = () => {
    bar.hidden = !past || endIn;
    if (!bar.hidden) fit();
  };
  window.addEventListener("resize", () => { if (!bar.hidden) fit(); }, { passive: true });
  const onScroll = () => { const p = window.scrollY > window.innerHeight * 0.8; if (p !== past) { past = p; update(); } };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
  if (!box.closest(".page-aside") && "IntersectionObserver" in window) {
    new IntersectionObserver(([e]) => { endIn = e.isIntersecting; update(); }, { threshold: 0.15 }).observe(box);
  }
});
// "#book" links (header, hero, dock, booking bar) while the sidebar shows a booking box (wide screens): that box is
// the one to use. On a guide the in-article box is hidden there; on a destination "#book" is the full list after
// the article, and a jump would take the reader away from where they are reading. So: a sidebar box already in view
// is just called out; otherwise the page goes to where the sidebar starts.
safe("book-jump", () => {
  // Capture phase, ahead of the general in-page anchor scroll (nav.js), which would jump to the pinned box.
  document.addEventListener("click", (e) => {
    const a = e.target.closest && e.target.closest('a[href="#book"]');
    if (!a || a.closest(".menu")) return;
    const aside = document.querySelector(".page-layout--aside > .page-aside");
    const box = aside && getComputedStyle(aside).display !== "contents" ? [...aside.querySelectorAll(".offer-box")].find((b) => b.getClientRects().length) : null;
    if (!box) return;
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
