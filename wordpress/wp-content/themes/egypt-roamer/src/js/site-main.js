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
