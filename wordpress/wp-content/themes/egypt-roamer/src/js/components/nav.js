/* Navigation: transparent → solid/light states, hide on scroll, active link
   indicator, language selector, mobile menu and generic overlays. */
import { $, $$, scrollToTarget, emit, on } from "../utils.js";
import { renderSaved } from "./favorites.js";
import { lang, LANGS, setLang } from "../i18n.js";

const nav = $("#nav");
const dock = $(".dock");

/* ---------------- State by section under the bar ---------------- */
function sectionTheme() {
  const probe = nav.offsetHeight * 0.6;
  const dark = $$(".journey, .interlude, .moods, .map, .planner, .footer");
  for (const el of dark) {
    const r = el.getBoundingClientRect();
    if (r.top <= probe && r.bottom > probe) return "dark";
  }
  return "light";
}

let lastY = window.scrollY;
function onScroll() {
  const y = window.scrollY;
  const theme = sectionTheme();
  const state = y < 40 ? "top" : theme === "dark" ? "solid" : "light";
  if (nav.dataset.state !== state) nav.dataset.state = state;

  const menuOpen = $("#menu")?.classList.contains("is-open");
  const goingDown = y > lastY + 4;
  const goingUp = y < lastY - 4;
  if (!menuOpen) {
    if (goingDown && y > 240) nav.classList.add("is-hidden");
    if (goingUp || y < 240) nav.classList.remove("is-hidden");
  }
  lastY = y;
}

/* ---------------- Active link indicator ---------------- */
function initIndicator() {
  const wrap = $(".nav__links");
  const ink = $(".nav__indicator");
  if (!wrap || !ink) return;
  const links = $$("a", wrap);
  const place = (a) => {
    if (!a) {
      ink.style.opacity = "0";
      return;
    }
    const wr = wrap.getBoundingClientRect();
    const r = a.getBoundingClientRect();
    ink.style.opacity = "1";
    ink.style.transform = `translateX(${r.left - wr.left + 14}px) scaleX(${(r.width - 28) / 100})`;
  };
  let current = null;
  links.forEach((a) => {
    a.addEventListener("mouseenter", () => place(a));
    a.addEventListener("focus", () => place(a));
  });
  wrap.addEventListener("mouseleave", () => place(current));

  const map = { destinations: "destinations", map: "destinations", experiences: "experiences", partners: "partners", guide: "guide" };
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        const key = map[en.target.id];
        current = key ? links.find((l) => l.dataset.nav === key) : null;
        links.forEach((l) => l.classList.toggle("is-active", l === current));
        place(current);
      });
    },
    { rootMargin: "-45% 0px -50% 0px" }
  );
  ["top", "moods", "destinations", "map", "partners", "experiences", "guide", "planner"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) io.observe(el);
  });
  window.addEventListener("resize", () => place(current));
}

/* ---------------- Language ---------------- */
function initLang() {
  // Mobile menu buttons
  $$("[data-set-lang]").forEach((b) => {
    b.setAttribute("aria-pressed", String(b.dataset.setLang === lang));
    b.addEventListener("click", () => setLang(b.dataset.setLang));
  });

  const root = $("[data-lang]");
  if (!root) return;
  const toggle = $(".lang__toggle", root);
  const current = $("[data-lang-current]", root);
  const options = $$("[role=option]", root);
  current.textContent = LANGS[lang].short;
  options.forEach((o) => o.setAttribute("aria-selected", String(o.dataset.value === lang)));

  const open = (state) => {
    root.classList.toggle("is-open", state);
    toggle.setAttribute("aria-expanded", String(state));
    if (state) (options.find((o) => o.dataset.value === lang) || options[0]).focus();
  };
  toggle.addEventListener("click", (e) => {
    e.stopPropagation();
    open(!root.classList.contains("is-open"));
  });
  options.forEach((opt, i) => {
    opt.addEventListener("click", () => {
      open(false);
      setLang(opt.dataset.value);
    });
    opt.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        opt.click();
      } else if (e.key === "ArrowDown" || e.key === "ArrowUp") {
        e.preventDefault();
        options[(i + (e.key === "ArrowDown" ? 1 : -1) + options.length) % options.length].focus();
      } else if (e.key === "Escape") {
        open(false);
        toggle.focus();
      }
    });
  });
  document.addEventListener("click", (e) => {
    if (!root.contains(e.target)) open(false);
  });
}

/* ---------------- Overlays ---------------- */
let lastFocus = null;
export function openOverlay(id) {
  const el = document.getElementById(id);
  if (!el) return;
  lastFocus = document.activeElement;
  if (id === "saved") renderSaved();
  el.hidden = false;
  void el.offsetWidth; // commit the unhidden state so the open transition runs
  el.classList.add("is-open");
  window.__lenis?.stop();
  if (id !== "menu") document.body.style.overflow = "hidden";
  const focusable = el.querySelector("input, button, a[href]");
  setTimeout(() => focusable?.focus({ preventScroll: true }), 60);
  if (id === "menu") {
    $$('[data-open="menu"]').forEach((b) => b.setAttribute("aria-expanded", "true"));
    nav.classList.remove("is-hidden");
    dock?.classList.add("is-hidden");
  }
  emit("overlay:open", id);
}
export function closeOverlay(el) {
  if (!el || el.hidden) return;
  el.classList.remove("is-open");
  const id = el.id;
  setTimeout(() => (el.hidden = true), id === "menu" ? 800 : 450);
  window.__lenis?.start();
  document.body.style.overflow = "";
  if (id === "menu") {
    $$('[data-open="menu"]').forEach((b) => b.setAttribute("aria-expanded", "false"));
    dock?.classList.remove("is-hidden");
  }
  emit("overlay:close", id);
  lastFocus?.focus?.({ preventScroll: true });
}

function initOverlays() {
  document.addEventListener("click", (e) => {
    const opener = e.target.closest("[data-open]");
    if (opener) {
      e.preventDefault();
      const id = opener.dataset.open;
      const el = document.getElementById(id);
      if (id === "menu" && el?.classList.contains("is-open")) closeOverlay(el);
      else openOverlay(id);
      return;
    }
    const closer = e.target.closest("[data-close]");
    if (closer) {
      closeOverlay(closer.closest(".overlay, .menu"));
      return;
    }
    // click on backdrop
    if (e.target.classList?.contains("overlay") && !e.target.classList.contains("film")) closeOverlay(e.target);
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") $$(".overlay.is-open, .menu.is-open").forEach(closeOverlay);
    const typing = /INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName);
    if ((e.key === "/" && !typing) || ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k")) {
      e.preventDefault();
      openOverlay("search");
    }
  });
}

/* ---------------- In-page anchors (smooth, Lenis-aware) ---------------- */
function initAnchors() {
  document.addEventListener("click", (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a || a.matches("[data-affiliate], [href='#partner']")) return;
    const hash = a.getAttribute("href");
    if (hash === "#" || hash.length < 2) return;
    const target = document.querySelector(hash);
    if (!target) return;
    e.preventDefault();
    const menu = $("#menu");
    if (menu?.classList.contains("is-open")) closeOverlay(menu);
    if (a.dataset.partnerTab) emit("partners:tab", a.dataset.partnerTab);
    if (a.dataset.destLink) emit("destinations:select", a.dataset.destLink);
    if (a.dataset.expFilter) emit("experiences:filter", a.dataset.expFilter);
    if (a.dataset.mapFocus) emit("map:focus", a.dataset.mapFocus);
    if (hash === "#top") return scrollToTarget(0);
    if (a.dataset.scrollTo === "journey") return emit("journey:goto", 0);
    // scenes live inside the pinned journey — delegate
    if (a.dataset.rail !== undefined) return emit("journey:goto", Number(a.dataset.rail));
    scrollToTarget(target, { offset: 0 });
    history.replaceState(null, "", hash);
  });
}

/* Collapse the links into the menu when they don't fit (label length varies by language). */
function initNavFit() {
  const inner = $(".nav__inner");
  if (!inner) return;
  const fit = () => {
    nav.classList.remove("nav--compact");
    if (inner.scrollWidth > inner.clientWidth + 1) nav.classList.add("nav--compact");
  };
  fit();
  document.fonts?.ready.then(fit);
  window.addEventListener("resize", fit);
}

export function initNav() {
  initNavFit();
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });
  initIndicator();
  initLang();
  initOverlays();
  initAnchors();
  on("overlay:open", () => nav.classList.remove("is-hidden"));
}
