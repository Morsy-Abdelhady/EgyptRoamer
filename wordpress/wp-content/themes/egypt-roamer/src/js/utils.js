/* Small shared helpers */

export const $ = (sel, root = document) => root.querySelector(sel);
export const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

export const reducedMotion = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches;
export const finePointer = () => window.matchMedia("(hover: hover) and (pointer: fine)").matches;
export const isMobile = () => window.matchMedia("(max-width: 900px)").matches;

/** Inline sprite icon markup. */
export const icon = (id, cls = "") => `<svg class="icon ${cls}" aria-hidden="true"><use href="#${id}"/></svg>`;

const docLang = () => document.documentElement.lang || "en";
export const fmt = (n) => n.toLocaleString(docLang());
/** Money in the page's language (fixes "$1,450" on French/Arabic pages). */
export const money = (n, currency = "USD") => {
  try {
    return new Intl.NumberFormat(docLang(), { style: "currency", currency, maximumFractionDigits: 0 }).format(n);
  } catch {
    return `$${fmt(n)}`;
  }
};
/** Display price: server-formatted (WordPress, only verified prices) or a number (prototype). */
export const priceOf = (x) => (x && x.priceText) || (x && x.price != null ? money(x.price) : "");

/** Attributes for an outbound partner link. WordPress offers carry tracking data (x.track). */
export const affAttrs = (x) => {
  if (x && x.track) {
    const k = x.track;
    return `rel="sponsored nofollow noopener" target="_blank" data-er-offer="${escapeHtml(k.offer)}" data-er-provider="${escapeHtml(k.provider)}" data-er-placement="${escapeHtml(k.placement)}" data-er-cta="${escapeHtml(k.cta)}" data-er-intent="${escapeHtml(k.intent)}"`;
  }
  return `rel="sponsored noopener" data-affiliate="${escapeHtml((x && x.partner) || "")}"`;
};

export const escapeHtml = (s) =>
  String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);

export const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
export const lerp = (a, b, t) => a + (b - a) * t;

/** Tiny event bus so components can talk without importing each other. */
const bus = new EventTarget();
export const emit = (name, detail) => bus.dispatchEvent(new CustomEvent(name, { detail }));
export const on = (name, fn) => bus.addEventListener(name, (e) => fn(e.detail));

/** Toast notification. */
let toastTimer;
export function toast(message, iconId = "i-check") {
  const el = $("[data-toast]");
  if (!el) return;
  el.innerHTML = `${icon(iconId)}<span>${escapeHtml(message)}</span>`;
  el.classList.add("is-on");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove("is-on"), 3200);
}

/** Smooth scroll to an element or y position, using Lenis when present. */
export function scrollToTarget(target, opts = {}) {
  const lenis = window.__lenis;
  const offset = opts.offset ?? 0;
  if (lenis) {
    lenis.scrollTo(target, { offset, duration: opts.duration ?? 1.6, immediate: reducedMotion() });
    return;
  }
  const y = typeof target === "number" ? target : target.getBoundingClientRect().top + window.scrollY + offset;
  window.scrollTo({ top: y, behavior: reducedMotion() ? "auto" : "smooth" });
}
