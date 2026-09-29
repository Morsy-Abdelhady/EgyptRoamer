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
import { initMagnetic, initReveals, initScrollProgress, initAffiliateLinks, initNewsletter, initImageFallback } from "./components/micro.js";

function safe(name, fn) {
  try {
    fn();
  } catch (err) {
    console.error(`[egypt-roamer] ${name} failed`, err);
  }
}

safe("images", initImageFallback);
safe("favorites", initFavorites);
safe("nav", initNav);
safe("search", initSearch);
safe("sections", initSectionNav);
safe("magnetic", initMagnetic);
safe("reveals", initReveals);
safe("progress", initScrollProgress);
safe("affiliate", initAffiliateLinks);
safe("newsletter", initNewsletter);
