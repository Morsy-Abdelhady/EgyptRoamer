/* ==========================================================================
   Egypt Roamer — homepage bootstrap
   Each section is an independent component in ./components/.
   ========================================================================== */
import { translateStatic, localizeData } from "./i18n.js";
import * as data from "./data.js";
import { initFavorites } from "./components/favorites.js";
import { initNav } from "./components/nav.js";
import { initLoader, initHero } from "./components/hero.js";
import { initJourney } from "./components/journey.js";
import { initMoods } from "./components/moods.js";
import { initDestinations } from "./components/destinations.js";
import { initMap } from "./components/map.js";
import { initPartners } from "./components/partners.js";
import { initExperiences } from "./components/experiences.js";
import { initGuide } from "./components/guide.js";
import { initPlanner } from "./components/planner.js";
import { initSearch } from "./components/search.js";
import { initFilm } from "./components/film.js";
import {
  initMagnetic,
  initReveals,
  initScrollProgress,
  initParallax,
  initAffiliateLinks,
  initNewsletter,
  initImageFallback,
} from "./components/micro.js";
import { reducedMotion } from "./utils.js";

function initSmoothScroll() {
  if (!window.Lenis || reducedMotion()) return;
  const lenis = new Lenis({ duration: 1.15, easing: (t) => 1 - Math.pow(1 - t, 3.2), smoothWheel: true });
  window.__lenis = lenis;
  if (window.gsap && window.ScrollTrigger) {
    lenis.on("scroll", ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
  } else {
    const raf = (t) => {
      lenis.raf(t);
      requestAnimationFrame(raf);
    };
    requestAnimationFrame(raf);
  }
}

/** After a language switch, return the reader to where they were. */
function restoreScroll() {
  let y = null;
  try {
    y = sessionStorage.getItem("ei:restore-scroll");
    sessionStorage.removeItem("ei:restore-scroll");
  } catch {
    /* ignore */
  }
  if (y == null) return;
  const top = Number(y) || 0;
  if (window.__lenis) window.__lenis.scrollTo(top, { immediate: true, force: true });
  else window.scrollTo(0, top);
}

function safe(name, fn) {
  try {
    fn();
  } catch (err) {
    console.error(`[egypt-roamer] ${name} failed`, err);
  }
}

// Language first: static copy + content must be localised before components render
safe("i18n", () => {
  translateStatic();
  localizeData(data);
});
safe("images", initImageFallback);
safe("favorites", initFavorites);
safe("smooth-scroll", initSmoothScroll);
safe("nav", initNav);
safe("hero", initHero);
safe("moods", initMoods);
safe("destinations", initDestinations);
safe("map", initMap);
safe("partners", initPartners);
safe("experiences", initExperiences);
safe("guide", initGuide);
safe("planner", initPlanner);
safe("search", initSearch);
safe("film", initFilm);
safe("magnetic", initMagnetic);
safe("reveals", initReveals);
safe("progress", initScrollProgress);
safe("affiliate", initAffiliateLinks);
safe("newsletter", initNewsletter);
safe("journey", initJourney);
safe("parallax", initParallax);
initLoader().then(restoreScroll);

window.addEventListener("load", () => window.ScrollTrigger?.refresh());
