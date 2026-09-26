/* ==========================================================================
   i18n — language detection, string lookup, static-page translation and
   content localisation.

   Adding a language: create locales/<code>.js with the same shape as ar.js,
   import it into DICTS and add it to LANGS. English is the source language,
   so every key is the English text itself.
   ========================================================================== */
import { ar } from "./locales/ar.js";
import { de } from "./locales/de.js";
import { fr } from "./locales/fr.js";
import { it } from "./locales/it.js";
import { es } from "./locales/es.js";
import { ru } from "./locales/ru.js";
import { zh } from "./locales/zh.js";

const DICTS = { ar, de, fr, it, es, ru, zh };

export const LANGS = {
  en: { label: "English", short: "EN", dir: "ltr", locale: "en-US" },
  de: { label: "Deutsch", short: "DE", dir: "ltr", locale: "de-DE" },
  fr: { label: "Français", short: "FR", dir: "ltr", locale: "fr-FR" },
  it: { label: "Italiano", short: "IT", dir: "ltr", locale: "it-IT" },
  es: { label: "Español", short: "ES", dir: "ltr", locale: "es-ES" },
  ru: { label: "Русский", short: "RU", dir: "ltr", locale: "ru-RU" },
  zh: { label: "中文", short: "中", dir: "ltr", locale: "zh-CN" },
  ar: { label: "العربية", short: "ع", dir: "rtl", locale: "ar-EG-u-nu-latn" },
};

function detect() {
  try {
    const q = new URLSearchParams(location.search).get("lang");
    if (q && LANGS[q]) return q;
    const s = localStorage.getItem("ei:lang");
    if (s && LANGS[s]) return s;
  } catch {
    /* storage unavailable */
  }
  return "en";
}

export const lang = detect();
export const isRTL = LANGS[lang].dir === "rtl";
export const locale = LANGS[lang].locale;
const dict = DICTS[lang] || null;

const norm = (s) => s.replace(/\s+/g, " ").trim();
const fill = (s, vars) => s.replace(/\{(\w+)\}/g, (m, k) => (vars[k] ?? m));
const missing = new Set();
window.__i18nMissing = missing; // inspected by the QA audit

/** Translate an English source string. `vars` fill {placeholders}. */
export function t(key, vars = {}) {
  let s = key;
  if (dict) {
    const v = dict.ui[key];
    if (typeof v === "string") s = v;
    else if (v && typeof v === "object") s = v.other;
    else missing.add(key);
  }
  return fill(s, vars);
}

/** Plural-aware translation: tp(n, "{n} night", "{n} nights"). */
export function tp(n, one, other, vars = {}) {
  const all = { n, ...vars };
  if (dict) {
    const v = dict.ui[other];
    if (v && typeof v === "object") {
      const cat = new Intl.PluralRules(lang).select(n);
      return fill(v[cat] ?? v.other, all);
    }
    if (typeof v === "string") return fill(v, all);
    missing.add(other);
  }
  return fill(n === 1 ? one : other, all);
}

/** "a, b and c" in the current language. */
export function listJoin(items) {
  if (items.length < 2) return items.join("");
  if (lang === "ar") return `${items.slice(0, -1).join("، ")} ${t("and")}${items.at(-1)}`;
  if (lang === "en") return `${items.slice(0, -1).join(", ")} & ${items.at(-1)}`;
  try {
    return new Intl.ListFormat(locale, { type: "conjunction" }).format(items);
  } catch {
    return `${items.slice(0, -1).join(", ")} ${t("and")} ${items.at(-1)}`;
  }
}

/** Translate the static HTML: text nodes, rich fragments and attributes. */
export function translateStatic(root = document.body) {
  document.documentElement.lang = lang;
  document.documentElement.dir = LANGS[lang].dir;
  if (!dict) return;

  root.querySelectorAll("[data-i18n-html]").forEach((el) => {
    const v = dict.html[el.dataset.i18nHtml];
    if (v != null) el.innerHTML = v;
  });

  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
    acceptNode: (n) =>
      n.parentElement?.closest("script, style, defs, [data-i18n-skip], [data-i18n-html]")
        ? NodeFilter.FILTER_REJECT
        : NodeFilter.FILTER_ACCEPT,
  });
  const nodes = [];
  while (walker.nextNode()) nodes.push(walker.currentNode);
  nodes.forEach((n) => {
    const key = norm(n.data);
    if (!key || !/[A-Za-z]/.test(key)) return;
    let v = dict.ui[key];
    if (v && typeof v === "object") v = v.other;
    if (typeof v !== "string") {
      missing.add(key);
      return;
    }
    const lead = n.data.match(/^\s*/)[0];
    const trail = n.data.match(/\s*$/)[0];
    n.data = lead + v + trail;
  });

  const ATTRS = ["aria-label", "placeholder", "alt", "title", "data-prefix"];
  root.querySelectorAll(ATTRS.map((a) => `[${a}]`).join(",")).forEach((el) =>
    ATTRS.forEach((a) => {
      const val = el.getAttribute(a);
      if (!val || !/[A-Za-z]/.test(val)) return;
      const v = dict.ui[norm(val)];
      if (typeof v === "string") el.setAttribute(a, v);
      else missing.add(norm(val));
    })
  );

  document.title = dict.meta.title;
  document.querySelector('meta[name="description"]')?.setAttribute("content", dict.meta.description);
}

/** Merge per-id content overrides into the data modules (in place). */
export function localizeData(d) {
  if (!dict) return;
  const c = dict.content;
  d.destinations.forEach((x) => Object.assign(x, c.destinations[x.id] || {}));
  d.moods.forEach((m) => {
    const o = c.moods[m.id];
    if (!o) return;
    const { recs, ...rest } = o;
    Object.assign(m, rest);
    if (recs?.exp) Object.assign(m.recs.exp, recs.exp);
    if (recs?.stay) Object.assign(m.recs.stay, recs.stay);
  });
  d.experiences.forEach((x) => Object.assign(x, c.experiences[x.id] || {}));
  d.experienceFilters.forEach((f) => (f.label = c.filters[f.id] ?? f.label));
  d.partnerCategories.forEach((cat) => {
    const o = c.partners[cat.id];
    if (!o) return;
    const { items, ...rest } = o;
    Object.assign(cat, rest);
    items?.forEach((it, i) => cat.items[i] && Object.assign(cat.items[i], it));
  });
  d.guides.forEach((g) => Object.assign(g, c.guides[g.id] || {}));
  d.film.forEach((f, i) => Object.assign(f, c.film[i] || {}));
  Object.assign(d.legs, c.legs);
}

/** Switch language: remember it, keep the reader's place, reload. */
export function setLang(next) {
  if (!LANGS[next] || next === lang) return;
  try {
    localStorage.setItem("ei:lang", next);
    sessionStorage.setItem("ei:restore-scroll", String(Math.round(window.scrollY)));
  } catch {
    /* ignore */
  }
  const url = new URL(location.href);
  url.searchParams.set("lang", next);
  location.replace(url.toString());
}
