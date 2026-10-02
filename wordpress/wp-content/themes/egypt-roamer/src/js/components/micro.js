/* Micro-interactions: magnetic CTAs, button light, reveals, scroll progress,
   gentle parallax, affiliate link handling, newsletter. */
import { t } from "../i18n.js";
import { $, $$, finePointer, reducedMotion, toast } from "../utils.js";

export function initMagnetic() {
  if (!finePointer() || reducedMotion()) return;
  document.addEventListener("pointermove", (e) => {
    const btn = e.target.closest?.(".btn");
    if (btn) {
      const r = btn.getBoundingClientRect();
      btn.style.setProperty("--mx", `${e.clientX - r.left}px`);
      btn.style.setProperty("--my", `${e.clientY - r.top}px`);
    }
  });
  const attach = (el) => {
    if (el.__magnetic) return;
    el.__magnetic = true;
    const strength = 0.28;
    el.addEventListener("pointermove", (e) => {
      const r = el.getBoundingClientRect();
      const x = (e.clientX - (r.left + r.width / 2)) * strength;
      const y = (e.clientY - (r.top + r.height / 2)) * strength;
      el.style.transform = `translate(${x}px, ${y}px)`;
    });
    el.addEventListener("pointerleave", () => {
      el.style.transition = "transform .6s cubic-bezier(.16,1,.3,1)";
      el.style.transform = "";
      setTimeout(() => (el.style.transition = ""), 600);
    });
  };
  $$("[data-magnetic]").forEach(attach);
  new MutationObserver(() => $$("[data-magnetic]").forEach(attach)).observe(document.body, { childList: true, subtree: true });
}

/* Reveal-on-scroll driven by scroll position rather than IntersectionObserver
   alone: anything in view OR above the viewport is revealed, so instant jumps
   (anchors, search, reload mid-page) and reverse scrolling never leave hidden content. */
export function initReveals() {
  let pending = $$("[data-reveal]:not(.is-in)");
  if (reducedMotion()) {
    pending.forEach((el) => el.classList.add("is-in"));
    return;
  }
  let queued = false;
  const sweep = () => {
    queued = false;
    const line = window.innerHeight * 0.92;
    pending = pending.filter((el) => {
      if (el.getBoundingClientRect().top < line) {
        el.classList.add("is-in");
        return false;
      }
      return true;
    });
  };
  const onScroll = () => {
    if (pending.length && !queued) {
      queued = true;
      requestAnimationFrame(sweep);
      setTimeout(() => queued && sweep(), 120); // fallback when rAF is throttled
    }
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll);
  window.__lenis?.on("scroll", onScroll);
  // Late-rendered components add their own [data-reveal] nodes
  new MutationObserver(() => {
    const fresh = $$("[data-reveal]:not(.is-in)").filter((el) => !pending.includes(el));
    if (fresh.length) {
      pending.push(...fresh);
      onScroll();
    }
  }).observe(document.body, { childList: true, subtree: true });
  sweep();
  setTimeout(sweep, 0); // rAF can be throttled in background tabs
}

export function initScrollProgress() {
  const bar = $("#scroll-progress");
  if (!bar) return;
  const update = () => {
    const max = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.transform = `scaleX(${max > 0 ? window.scrollY / max : 0})`;
  };
  window.addEventListener("scroll", update, { passive: true });
  window.addEventListener("resize", update);
  update();
}

/** Gentle parallax on large section backgrounds (GSAP, scrubbed). */
export function initParallax() {
  if (!window.gsap || !window.ScrollTrigger || reducedMotion()) return;
  const planner = $(".planner__bg img");
  if (planner) {
    gsap.fromTo(planner, { yPercent: -12 }, { yPercent: 0, ease: "none", scrollTrigger: { trigger: ".planner", start: "top bottom", end: "bottom top", scrub: true } });
  }
  $$(".moods__bg").forEach((bg) =>
    gsap.fromTo(bg, { yPercent: -6 }, { yPercent: 6, ease: "none", scrollTrigger: { trigger: "#moods", start: "top bottom", end: "bottom top", scrub: true } })
  );
  // (The footer logo is the approved brand artwork — it stays on the grid, no drift.)
}

/** Affiliate links are placeholders here — show where they'd go instead of navigating. */
export function initAffiliateLinks() {
  document.addEventListener("click", (e) => {
    const a = e.target.closest('a[data-affiliate], a[href="#partner"]');
    if (!a) return;
    const href = a.getAttribute("href");
    if (href && href !== "#partner") return; // real tracked link → let it through
    e.preventDefault();
    const partner = a.dataset.affiliate || "our partner";
    toast(t("Affiliate link · opens {partner} in a new tab", { partner: t(partner) }), "i-arrow-ur");
  });
}

export function initNewsletter() {
  const form = $("[data-subscribe]");
  if (form?.getAttribute("action")) return; // WordPress: the form posts to the server
  form?.addEventListener("submit", (e) => {
    e.preventDefault();
    toast(t("You're on the list — first letter arrives next month."), "i-mail");
    form.reset();
  });
}

/** Images: retry a failed CDN load once, then fade to the neutral backdrop
    instead of showing a broken-image icon. */
export function initImageFallback() {
  // add a cache-buster to every URL (srcset candidates included), with or without an existing query
  const bust = (url) => url.replace(/(^|,\s*)([^\s,]+)/g, (m, sep, u) => sep + u + (u.includes("?") ? "&" : "?") + "retry=1");
  const handle = (img) => {
    if (img.dataset.retried) {
      img.classList.add("img-failed");
      return;
    }
    img.dataset.retried = "1";
    setTimeout(() => {
      if (img.srcset) img.srcset = bust(img.srcset);
      if (img.parentElement?.tagName === "PICTURE") img.parentElement.querySelectorAll("source").forEach((s) => (s.srcset = bust(s.srcset)));
      if (img.getAttribute("src")) img.src = bust(img.getAttribute("src"));
    }, 1500);
  };
  document.addEventListener(
    "error",
    (e) => {
      if (e.target instanceof HTMLImageElement) handle(e.target);
    },
    true
  );
  // images that already failed before this module ran
  $$("img").forEach((img) => img.complete && img.getAttribute("src") && img.naturalWidth === 0 && handle(img));
}
