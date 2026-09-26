/* ==========================================================================
   The Journey — one pinned stage, scrubbed by scroll:
   Hero → Pyramids → Nile → Desert → Red Sea
   Built with GSAP + ScrollTrigger. Falls back to stacked scenes without it
   (or when the visitor prefers reduced motion).
   ========================================================================== */
import { $, $$, on, reducedMotion, isMobile, clamp } from "../utils.js";
import { ParticleField } from "./fx.js";

const SCENE_TIMES = [1.45, 3.7, 5.75, 8.0]; // where each scene "rests" on the timeline
const pad = (n) => String(n).padStart(2, "0");

function splitTitles(root) {
  $$("[data-split]", root).forEach((el) => {
    const text = el.textContent.trim();
    el.setAttribute("aria-label", text);
    el.innerHTML = text
      .split(" ")
      .map(
        (word) =>
          // Arabic is a joined script: splitting glyphs breaks letter shaping, so animate whole words
          `<span class="word" aria-hidden="true">${
            /[؀-ۿ]/.test(word) ? `<span class="char">${word}</span>` : [...word].map((c) => `<span class="char">${c}</span>`).join("")
          }</span>`
      )
      .join(" ");
  });
}

/** Pin the track exactly between the first and last dot centres. */
function layoutRail(rail) {
  const track = $(".rail__track", rail);
  const dots = $$(".rail__dot", rail);
  if (!track || dots.length < 2) return;
  const origin = rail.getBoundingClientRect().top;
  const centre = (el) => {
    const r = el.getBoundingClientRect();
    return r.top + r.height / 2 - origin;
  };
  const top = centre(dots[0]);
  track.style.top = `${top}px`;
  track.style.height = `${centre(dots[dots.length - 1]) - top}px`;
}

function railController() {
  const rail = $("#rail");
  const links = $$("[data-rail]");
  const fill = $("#rail-fill");
  const count = $("#journey-count");
  let last = null;
  if (rail) {
    const relayout = () => layoutRail(rail);
    relayout();
    document.fonts?.ready.then(relayout);
    window.addEventListener("resize", relayout);
  }
  return {
    rail,
    set(idx, progress, travelling) {
      if (fill) fill.style.transform = `scaleY(${progress})`;
      rail?.classList.toggle("is-travelling", travelling);
      if (idx === last) return;
      last = idx;
      links.forEach((a, i) => {
        a.classList.toggle("is-active", i === idx);
        if (i === idx) a.setAttribute("aria-current", "step");
        else a.removeAttribute("aria-current");
      });
      if (count) count.textContent = pad(Math.max(idx, 0) + 1);
    },
  };
}

/* ---------------- Fallback: stacked scenes ----------------
   Scroll-position driven (not IntersectionObserver) so jumps and reverse
   scrolling always leave the rail in the right state. */
function initFallback() {
  const rc = railController();
  const scenes = $$(".scene");
  const journey = $(".journey");
  let queued = false;
  const update = () => {
    queued = false;
    const mid = window.innerHeight / 2;
    let idx = 0;
    scenes.forEach((s, i) => {
      if (s.getBoundingClientRect().top <= mid) idx = i;
    });
    rc.set(idx, (idx + 1) / scenes.length, window.scrollY > 40);
    rc.rail?.classList.toggle("is-away", journey.getBoundingClientRect().bottom < window.innerHeight * 0.35);
  };
  const onScroll = () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(update);
    setTimeout(() => queued && update(), 120);
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll);
  update();
  on("journey:goto", (i) => scenes[i]?.scrollIntoView({ behavior: reducedMotion() ? "auto" : "smooth" }));
  $$(".scene__canvas").forEach((c) => (c.style.display = "none"));
}

export function initJourney() {
  const section = $(".journey");
  const stage = $("#journey-stage");
  if (!section || !stage) return;

  if (!window.gsap || !window.ScrollTrigger || reducedMotion()) {
    initFallback();
    return;
  }

  gsap.registerPlugin(ScrollTrigger);
  section.classList.add("is-live");
  splitTitles(stage);

  const [pyr, nile, desert, sea] = $$(".scene", stage);
  const q = (scene, sel) => scene.querySelector(sel);
  const parts = (scene) => ({
    kicker: q(scene, ".scene__kicker"),
    chars: $$(".char", scene),
    line: q(scene, ".scene__line"),
    btn: q(scene, ".scene__content .btn"),
    pick: q(scene, ".scene__pick"),
    coords: q(scene, ".scene__coords"),
  });
  const hero = $("#hero");
  const heroContent = q(hero, ".hero__content");
  const finder = q(hero, ".finder") || []; // optional: hidden when no partner search is configured
  const heroNote = q(hero, ".hero__note");
  const grade = q(stage, ".journey__grade");
  const bars = $$(".journey__letterbox i", stage);
  const riverCue = q(nile, ".river-cue");
  const riverDraw = q(nile, ".river-cue__draw");
  const surface = q(sea, ".scene__surface");
  const mobile = isMobile();

  /* ----- initial states ----- */
  gsap.set(nile, { clipPath: "inset(50% 0% 50% 0%)" });
  gsap.set(desert, { clipPath: "inset(0% 0% 0% 100%)" });
  gsap.set(sea, { yPercent: 100 });
  [pyr, nile, desert, sea].forEach((s) => {
    const c = parts(s);
    gsap.set(c.chars, { yPercent: 118 });
    gsap.set([c.kicker, c.line, c.btn].filter(Boolean), { autoAlpha: 0, y: 26 });
    gsap.set([c.pick, c.coords].filter(Boolean), { autoAlpha: 0, y: 16 });
  });
  gsap.set(riverCue, { autoAlpha: 0, x: 30 });

  const tl = gsap.timeline({ defaults: { ease: "none" } });

  const textIn = (scene, at) => {
    const c = parts(scene);
    tl.to(c.kicker, { autoAlpha: 1, y: 0, duration: 0.4, ease: "power2.out" }, at)
      .to(c.chars, { yPercent: 0, duration: 0.6, stagger: 0.035, ease: "power3.out" }, at + 0.05)
      .to([c.line, c.btn], { autoAlpha: 1, y: 0, duration: 0.45, stagger: 0.08, ease: "power2.out" }, at + 0.28)
      .to([c.pick, c.coords].filter(Boolean), { autoAlpha: 1, y: 0, duration: 0.45, stagger: 0.1, ease: "power2.out" }, at + 0.42); // "Roamer pick" is optional (WordPress: only for live offers)
  };
  const textOut = (scene, at) => {
    const c = parts(scene);
    tl.to(c.chars, { yPercent: -118, duration: 0.42, stagger: 0.025, ease: "power2.in" }, at).to(
      [c.kicker, c.line, c.btn, c.pick, c.coords].filter(Boolean),
      { autoAlpha: 0, y: -18, duration: 0.34, ease: "power2.in" },
      at
    );
  };
  const letterbox = (at) => {
    tl.to(bars, { scaleY: mobile ? 0.45 : 1, duration: 0.5, ease: "power2.inOut" }, at).to(
      bars,
      { scaleY: 0, duration: 0.5, ease: "power2.inOut" },
      at + 0.65
    );
  };
  const tint = (color, opacity, at, duration = 1) =>
    tl.to(grade, { backgroundColor: color, opacity, duration, ease: "power1.inOut" }, at);

  /* ----- 0 → 1.4 · Hero hands over to PYRAMIDS ----- */
  const noLazy = { immediateRender: false };
  tl.fromTo(heroContent, { autoAlpha: 1, y: 0 }, { autoAlpha: 0, y: -70, duration: 0.6, ease: "power2.in", ...noLazy }, 0)
    .fromTo(finder, { autoAlpha: 1, y: 0 }, { autoAlpha: 0, y: 90, duration: 0.55, ease: "power2.in", ...noLazy }, 0)
    .fromTo(heroNote, { autoAlpha: 1 }, { autoAlpha: 0, duration: 0.4, ...noLazy }, 0)
    .to(hero, { autoAlpha: 0, duration: 0.5 }, 0.35)
    .to(q(pyr, ".scene__media"), { scale: 1.22, duration: 2.4 }, 0)
    .to(q(pyr, ".scene__img--b"), { opacity: 1, duration: 1.3 }, 0.35)
    .fromTo(q(pyr, ".scene__sun"), { yPercent: 0, opacity: 1 }, { yPercent: 35, opacity: 0.45, duration: 2.4 }, 0)
    .to(q(pyr, ".scene__fg--dune"), { yPercent: -30, duration: 2.4 }, 0);
  tint("#C9A227", 0.16, 0, 1.2);
  textIn(pyr, 0.55);

  /* ----- 2.0 → 3.3 · PYRAMIDS → NILE (horizon opens like a lens) ----- */
  textOut(pyr, 2.05);
  letterbox(2.05);
  tl.to(q(pyr, ".scene__media"), { scale: 1.45, yPercent: -4, duration: 1.1, ease: "power2.in" }, 2.05)
    .to(nile, { clipPath: "inset(0% 0% 0% 0%)", duration: 1.0, ease: "power3.inOut" }, 2.2)
    .fromTo(q(nile, ".scene__media"), { scale: 1.32 }, { scale: 1.06, duration: 1.5, ease: "power2.out" }, 2.2)
    .to(riverCue, { autoAlpha: 1, x: 0, duration: 0.5, ease: "power2.out" }, 2.95)
    .to(riverDraw, { strokeDashoffset: 0, duration: 1.3 }, 3.0)
    .to(q(nile, ".scene__img"), { xPercent: -3.5, duration: 2.2 }, 2.8);
  tint("#A45E38", 0.12, 2.2);
  textIn(nile, 2.8);

  /* ----- 4.3 → 5.5 · NILE → DESERT (a horizontal sweep of wind) ----- */
  textOut(nile, 4.3);
  letterbox(4.3);
  tl.to(riverCue, { autoAlpha: 0, x: 30, duration: 0.35, ease: "power2.in" }, 4.3)
    .to(q(nile, ".scene__media"), { xPercent: -9, duration: 1.1, ease: "power2.in" }, 4.3)
    .to(desert, { clipPath: "inset(0% 0% 0% 0%)", duration: 1.05, ease: "power3.inOut" }, 4.4)
    .fromTo(q(desert, ".scene__media"), { xPercent: 7 }, { xPercent: -7, duration: 3.1 }, 4.4);
  tint("#D8B483", 0.16, 4.5);
  textIn(desert, 5.0);

  /* ----- 6.5 → 7.7 · DESERT → RED SEA (the camera dives) ----- */
  textOut(desert, 6.5);
  tl.to(desert, { yPercent: -32, duration: 1.15, ease: "power2.in" }, 6.55)
    .set(surface, { opacity: 0.9 }, 6.55)
    .to(sea, { yPercent: 0, duration: 1.15, ease: "power3.inOut" }, 6.55)
    .fromTo(q(sea, ".scene__media"), { yPercent: -16, scale: 1.22 }, { yPercent: 0, scale: 1.06, duration: 1.7, ease: "power2.out" }, 6.55)
    .to(surface, { opacity: 0, yPercent: -60, duration: 0.7 }, 7.3)
    .to(q(sea, ".scene__media"), { scale: 1.0, duration: 1.2 }, 8.2)
    .to({}, { duration: 0.4 }, 8.8);
  tint("#0F6B7A", 0.34, 6.6, 1.1);
  textIn(sea, 7.2);

  /* ----- Particle fields ----- */
  const sand = new ParticleField(q(desert, "[data-fx=sand]"), "sand");
  const bubbles = new ParticleField(q(sea, "[data-fx=bubbles]"), "bubbles");

  /* ----- Pin + scrub ----- */
  const scenes = [pyr, nile, desert, sea];
  const rc = railController();
  let inView = true;

  const update = () => {
    const t = tl.time();
    const idx = t < 0.9 ? -1 : t < 2.65 ? 0 : t < 4.85 ? 1 : t < 7.05 ? 2 : 3;
    rc.set(idx, clamp(t / tl.duration(), 0, 1), t > 0.25);
    scenes.forEach((s, i) => s.classList.toggle("is-active", i === Math.max(idx, 0)));
    const sandOn = inView && t > 4.35 && t < 7.4;
    const bubblesOn = inView && t > 6.6;
    sandOn ? sand.start() : sand.stop();
    bubblesOn ? bubbles.start() : bubbles.stop();
  };
  tl.eventCallback("onUpdate", update);

  const st = ScrollTrigger.create({
    trigger: section,
    start: "top top",
    end: () => `+=${Math.round(window.innerHeight * (mobile ? 4.6 : 5.4))}`,
    pin: stage,
    scrub: 0.9,
    animation: tl,
    anticipatePin: 1,
    invalidateOnRefresh: true,
    onToggle: (self) => {
      inView = self.isActive || self.progress < 1;
      update();
    },
  });

  ScrollTrigger.create({
    trigger: section,
    start: "top top",
    end: "bottom 35%",
    onLeave: () => {
      rc.rail?.classList.add("is-away");
      inView = false;
      update();
    },
    onEnterBack: () => {
      rc.rail?.classList.remove("is-away");
      inView = true;
      update();
    },
  });

  on("journey:goto", (i) => {
    const y = st.start + (SCENE_TIMES[i] / tl.duration()) * (st.end - st.start);
    const lenis = window.__lenis;
    if (lenis) lenis.scrollTo(y, { duration: 2.2 });
    else window.scrollTo({ top: y, behavior: "smooth" });
  });

  // After the hero entrance, stop CSS transitions fighting GSAP's inline styles.
  setTimeout(() => hero.classList.add("is-settled"), 3800);
  update();
}
