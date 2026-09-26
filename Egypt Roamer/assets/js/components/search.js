/* Command-palette style search across destinations, experiences and guides */
import { t, tp } from "../i18n.js";
import { $, escapeHtml, icon, on, emit, scrollToTarget } from "../utils.js";
import { destinations, experiences, guides, img } from "../data.js";
import { closeOverlay } from "./nav.js";

/* Built at init (after content is localised), not at import time. */
const buildIndex = () => [
  ...destinations.map((d) => ({ group: t("Destinations"), title: d.name, sub: d.region, image: d.image, text: `${d.name} ${d.region} ${d.highlights.join(" ")}`, go: () => (emit("destinations:select", d.id), "#destinations") })),
  ...experiences.map((x) => ({ group: t("Experiences"), title: x.title, sub: `${x.location} · ${t("from")} $${x.price}`, image: x.image, text: `${x.title} ${x.location} ${x.tag}`, go: () => (emit("experiences:filter", x.tag), "#experiences") })),
  ...guides.map((g) => ({ group: t("Travel Guide"), title: g.title, sub: `${g.cat} · ${tp(g.read, "{n} min read", "{n} min read")}`, image: g.image, text: `${g.title} ${g.cat}`, go: () => "#guide" })),
];

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
    if (e.key === "Enter") $("[data-hit]", results)?.click();
  });
  results.addEventListener("click", (e) => {
    const a = e.target.closest("[data-hit]");
    if (!a) return;
    e.preventDefault();
    const hash = shown[Number(a.dataset.hit)].go();
    closeOverlay(overlay);
    setTimeout(() => scrollToTarget(document.querySelector(hash)), 120);
  });
  on("overlay:open", (id) => {
    if (id !== "search") return;
    input.value = "";
    render("");
  });
  render("");
}
