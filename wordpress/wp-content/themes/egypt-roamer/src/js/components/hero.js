/* Hero: loader hand-off, entrance, affiliate quick-compare ("finder") */
import { t, locale } from "../i18n.js";
import { $, $$, toast, finePointer, reducedMotion } from "../utils.js";

const PARTNERS = {
  hotels: "Booking.com",
  tours: "GetYourGuide",
  cruises: "Viator",
  transfers: "Welcome Pickups",
  cars: "Rentalcars.com",
};

export function initLoader() {
  const loader = $("#loader");
  const hero = $("#hero");
  const heroImg = $(".scene--pyramids .scene__img--a");
  // The intro plays once per visit: later homepage views in the same session skip the wait.
  let seen = false;
  try {
    seen = sessionStorage.getItem("er-intro") === "1";
    sessionStorage.setItem("er-intro", "1");
  } catch (e) {}
  // Both times count from the start of the page (performance.now()), not from when this script runs:
  // the loader is on screen from the first paint, so a slow phone must not sit through it twice.
  const since = (ms) => Math.max(0, ms - performance.now());
  // Phones (the hero's phone layout): no loader screen and no wait; the hero's entrance already runs in
  // CSS from the first paint (pages.css, "content-first homepage"). Desktop keeps the cinematic intro.
  if (window.matchMedia?.("(max-width: 900px)").matches) {
    loader?.remove();
    hero?.classList.add("is-in");
    return Promise.resolve();
  }
  const minTime = new Promise((r) => setTimeout(r, since(seen ? 0 : reducedMotion() ? 200 : 1300)));
  const imgReady = heroImg?.complete
    ? Promise.resolve()
    : new Promise((r) => {
        heroImg?.addEventListener("load", r, { once: true });
        heroImg?.addEventListener("error", r, { once: true });
      });
  const fonts = document.fonts?.ready ?? Promise.resolve();
  // Never hold the page longer than this for the photo: a slow image host must not delay the first
  // content. 1.6 s from the start of the page (was 2.2 s from script start); the photo still fades in
  // when it arrives. Measured: docs/PERFORMANCE-2026-09-30.md.
  const maxTime = new Promise((r) => setTimeout(r, since(seen ? 0 : 1600)));

  return Promise.race([Promise.all([minTime, imgReady, fonts]), maxTime]).then(() => {
    loader?.classList.add("is-done");
    // The hero comes in with the loader's wipe, not after it (owner decision 2026-09-30; see pages.css).
    requestAnimationFrame(() => hero?.classList.add("is-in"));
    setTimeout(() => loader?.remove(), 1500);
  });
}

function initFinder() {
  const form = $("#finder");
  if (!form) return;
  const tabs = $$("[data-finder]", form);
  let active = tabs.find((x) => x.getAttribute("aria-pressed") === "true")?.dataset.finder || "hotels";

  tabs.forEach((tab) =>
    tab.addEventListener("click", () => {
      tabs.forEach((x) => x.setAttribute("aria-pressed", String(x === tab)));
      active = tab.dataset.finder;
      const go = $(".finder__go span", form);
      if (go) go.textContent = t(active === "tours" ? "Explore Tours" : active === "cruises" ? "Compare Cruises" : "Compare Options");
    })
  );

  // Upcoming 12 months
  const months = $("[data-months]", form);
  if (months) {
    const now = new Date();
    const opts = [`<option value="">${t("Flexible dates")}</option>`];
    for (let i = 0; i < 12; i++) {
      const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
      const value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
      opts.push(`<option value="${value}">${d.toLocaleString(locale, { month: "long", year: "numeric" })}</option>`);
    }
    months.innerHTML = opts.join("");
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const go = tabs.find((x) => x.dataset.finder === active)?.dataset.go;
    if (go) {
      // WordPress: the site's own tracked /go/ link; the server validates every value
      const url = new URL(go, location.href);
      if (form.where.value) url.searchParams.set("where", form.where.value);
      if (form.when.value) url.searchParams.set("when", form.when.value);
      if (form.who.value) url.searchParams.set("adults", form.who.value);
      location.href = url.toString();
      return;
    }
    const sel = form.where;
    const where = sel.value ? sel.options[sel.selectedIndex].text : t("Egypt");
    // In production: build the partner deep link with these params and open it.
    toast(t("Comparing {where} on {partner} — opens with our partner", { where, partner: PARTNERS[active] }), "i-arrow-ur");
  });
}

/** Subtle pointer parallax on the opening frame (desktop only). */
function initPointerParallax() {
  if (!finePointer() || reducedMotion() || !window.gsap) return;
  const imgs = $$(".scene--pyramids .scene__img");
  const note = $(".hero__note");
  const xTo = imgs.map((el) => gsap.quickTo(el, "x", { duration: 1.4, ease: "power3.out" }));
  const yTo = imgs.map((el) => gsap.quickTo(el, "y", { duration: 1.4, ease: "power3.out" }));
  const nx = note ? gsap.quickTo(note, "x", { duration: 1.6, ease: "power3.out" }) : null;
  window.addEventListener(
    "pointermove",
    (e) => {
      if (window.scrollY > window.innerHeight * 2) return;
      const dx = e.clientX / window.innerWidth - 0.5;
      const dy = e.clientY / window.innerHeight - 0.5;
      xTo.forEach((f) => f(dx * -22));
      yTo.forEach((f) => f(dy * -14));
      nx?.(dx * 10);
    },
    { passive: true }
  );
}

export function initHero() {
  initFinder();
  initPointerParallax();
}
