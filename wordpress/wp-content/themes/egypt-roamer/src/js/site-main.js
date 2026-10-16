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
// Booking bar: shown past the first screen (phones: from the first screen), hidden again while the booking box it
// leads to is on screen. That includes a box pinned in the sidebar (16 Oct): at 1366x768 the centred bar sat on
// the sidebar box's last "Check Availability" button. The bar so takes over once the reader is past the sidebar.
safe("bookbar", () => {
  const bar = document.querySelector("[data-bookbar]");
  const href = bar?.querySelector("a")?.getAttribute("href") || "";
  const box = href.startsWith("#") ? document.getElementById(href.slice(1)) : null;
  if (!bar || !box) return;
  // The story pins its hero while the first chapters pass over it, so "past the hero" is read from the scroll.
  let past = false;
  let endIn = false;
  let footIn = false;
  // While the bar shows, it is the page's one booking button: on phones the dock's gold button (same label, same
  // target) gives way, so two identical buttons are not stacked over the page (16 Oct). The bar also steps aside
  // over the footer, where it covered the newsletter form.
  const update = () => {
    bar.hidden = !past || endIn || footIn;
    document.documentElement.classList.toggle("has-bookbar", !bar.hidden);
  };
  // Phones show it from the first screen (owner, 15 Oct: opened at the top, the bar was not there); wide screens
  // once the reader is past the first screen.
  const phone = window.matchMedia("(max-width: 900px)");
  const onScroll = () => { const p = phone.matches || window.scrollY > window.innerHeight * 0.8; if (p !== past) { past = p; update(); } };
  window.addEventListener("scroll", onScroll, { passive: true });
  phone.addEventListener?.("change", onScroll);
  onScroll();
  if ("IntersectionObserver" in window) {
    // A sidebar box is not the bar's target on phones (the sidebar is one column there, after the article).
    const aside = [...document.querySelectorAll(".page-aside .offer-box")].find((b) => b !== box) || null;
    const seen = new Map();
    const io = new IntersectionObserver((es) => {
      es.forEach((e) => seen.set(e.target, e.isIntersecting && e.target.getClientRects().length > 0));
      endIn = [...seen.values()].some(Boolean);
      update();
    }, { threshold: 0.15 });
    io.observe(box);
    if (aside) io.observe(aside);
  }
  const foot = document.getElementById("footer");
  if (foot && "IntersectionObserver" in window) {
    new IntersectionObserver(([e]) => { footIn = e.isIntersecting; update(); }).observe(foot);
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
