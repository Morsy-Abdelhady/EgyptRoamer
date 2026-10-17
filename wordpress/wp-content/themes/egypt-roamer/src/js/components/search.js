/* Command-palette style search across destinations, experiences and guides */
import { t, tp } from "../i18n.js";
import { $, escapeHtml, icon, on, emit, scrollToTarget, priceOf } from "../utils.js";
import { destinations, experiences, guides, img } from "../data.js";
import { closeOverlay } from "./nav.js";

/* Built at init (after content is localised), not at import time. */
const buildIndex = () => [
  ...destinations.map((d) => ({ group: t("Destinations"), title: d.name, sub: d.region, image: d.image, text: `${d.name} ${d.region} ${d.highlights.join(" ")}`, go: () => d.url || (emit("destinations:select", d.id), "#destinations") })),
  ...experiences.map((x) => ({ group: t("Experiences"), title: x.title, sub: [x.location, priceOf(x) && `${t("from")} ${priceOf(x)}`].filter(Boolean).join(" · "), image: x.image, text: `${x.title} ${x.location} ${x.tag}`, go: () => x.url || (emit("experiences:filter", x.tag), "#experiences") })),
  ...guides.map((g) => ({ group: t("Travel Guide"), title: g.title, sub: `${g.cat} · ${tp(g.read, "{n} min read", "{n} min read")}`, image: g.image, text: `${g.title} ${g.cat}`, go: () => (window.ER_DATA && g.href ? g.href : "#guide") })),
];

/* New journey preview (?ux=2): every published trip, place and guide in this language (inc/ux2-chrome.php), matched
   by words, not as one phrase, so "nile cruise", "snorkeling" or "valley of the kings" find their pages. */
const UX2 = (() => {
  try {
    const el = document.getElementById("ux2-data");
    return el ? JSON.parse(el.textContent) : null;
  } catch (e) {
    return null;
  }
})();
const STOP = new Set("the and for with what where when how are was its from this that there here about into near can could should would does did will want need get going any some please trip trips tour tours day days egypt".split(" "));
const stem = (w) => w.replace(/(ings|ing|ies|es|s)$/u, "").slice(0, 7);
const words = (q) => q.toLowerCase().replace(/[’']/g, "").split(/[^\p{L}\p{N}]+/u).filter((w) => (w.length > 2 || /[^\x00-\x7f]/.test(w)) && !STOP.has(w) && !/^\d+$/.test(w));
function ux2Find(q) {
  const ws = words(q).map(stem).filter(Boolean);
  if (!ws.length) return [];
  const order = { trip: 0, place: 1, guide: 2 };
  return UX2.index
    .map((it) => {
      const title = it.title.toLowerCase(), text = it.text.toLowerCase();
      let s = 0, all = true;
      for (const w of ws) {
        // a word's start, so "kings" never matches inside "snorkeling" (Chinese has no spaces: anywhere)
        const re = new RegExp((/[㐀-鿿]/.test(w) ? "" : "(^|[^\\p{L}])") + w.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "u");
        if (re.test(title)) s += 3;
        else if (re.test(text)) s += 1;
        else all = false;
      }
      return { it, s: s + (all ? 2 : 0) };
    })
    .filter((x) => x.s >= (ws.length > 1 ? 3 : 1))
    .sort((a, b) => b.s - a.s || order[a.it.k] - order[b.it.k])
    .slice(0, 12)
    .sort((a, b) => order[a.it.k] - order[b.it.k] || b.s - a.s)
    .map(({ it }) => ({ group: UX2.groups[it.k], title: it.title, sub: it.sub, image: it.image, go: () => it.url }));
}

export function initSearch() {
  const index = buildIndex();
  const overlay = $("#search");
  const input = $("[data-search-input]");
  const results = $("[data-search-results]");
  if (!overlay || !input) return;
  let shown = [];

  function render(qs) {
    const q = qs.trim().toLowerCase();
    shown = q ? index.filter((it) => it.text.toLowerCase().includes(q)).slice(0, 12) : index.filter((it) => it.group === t("Destinations"));
    if (UX2) {
      shown = q ? ux2Find(q) : UX2.index.filter((it) => it.k === "trip").slice(0, 6).map((it) => ({ group: UX2.groups.trip, title: it.title, sub: it.sub, image: it.image, go: () => it.url }));
      if (!shown.length) {
        results.innerHTML = `<p class="search__empty">${escapeHtml(UX2.empty.replace("{q}", qs.trim()))}</p><p class="ux2-search__acts">${UX2.actions.map(([l, u]) => `<a href="${escapeHtml(u)}">${escapeHtml(l)}</a>`).join("")}</p>`;
        return;
      }
    }
    if (!shown.length) {
      results.innerHTML = `<p class="search__empty">${escapeHtml(t("Nothing for “{q}” yet — try “Luxor”, “diving” or “cruise”.", { q: qs }))}</p>`;
      return;
    }
    let group = "";
    results.innerHTML = shown
      .map((it, i) => {
        const head = it.group !== group ? `<p class="search__group">${(group = it.group)}</p>` : "";
        return `${head}<a class="search__item" href="#" data-hit="${i}"><img src="${img(it.image, 120)}" alt="" loading="lazy" /><span><b>${escapeHtml(it.title)}</b><small>${escapeHtml(it.sub)}</small></span></a>`;
      })
      .join("");
  }

  input.addEventListener("input", () => render(input.value));
  input.addEventListener("keydown", (e) => {
    if (e.key !== "Enter") return;
    const hit = $("[data-hit]", results);
    if (hit && (input.value.trim() || !input.form)) {
      e.preventDefault();
      hit.click();
    } // otherwise the search form submits to the full results page (WordPress)
  });
  results.addEventListener("click", (e) => {
    const a = e.target.closest("[data-hit]");
    if (!a) return;
    e.preventDefault();
    const target = shown[Number(a.dataset.hit)].go();
    closeOverlay(overlay);
    if (!target.startsWith("#")) {
      location.href = target;
      return;
    }
    const el = document.querySelector(target);
    if (el) setTimeout(() => scrollToTarget(el), 120);
    else location.href = ((window.ER_DATA && window.ER_DATA.homeUrl) || "/") + target;
  });
  on("overlay:open", (id) => {
    if (id !== "search") return;
    input.value = "";
    render("");
  });
  render("");
}
