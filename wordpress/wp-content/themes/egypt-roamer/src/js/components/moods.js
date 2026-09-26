/* "What kind of Egypt are you looking for?" — mood dial that re-grades the
   section, swaps imagery and updates recommendations. */
import { t, isRTL } from "../i18n.js";
import { $, $$, icon, escapeHtml, money, emit } from "../utils.js";
import { moods, destinations, img } from "../data.js";
import { saveButton, refreshSaveButtons } from "./favorites.js";

const ringSvg = `<svg class="mood__ring" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="48"/><circle cx="50" cy="50" r="48"/></svg>`;

function recCard(kind, data) {
  return `<article class="rec is-entering">
    <div class="rec__img"><img src="${img(data.image, 300)}" alt="" loading="lazy" /></div>
    <div class="rec__body">
      <span class="rec__kind">${kind}</span>
      <p class="rec__title">${escapeHtml(data.title)}</p>
      <p class="rec__meta">${data.meta}</p>
    </div>
    <a class="rec__cta" href="${data.href}" ${data.affiliate ? `rel="sponsored noopener" data-affiliate="${escapeHtml(data.partner)}"` : `data-dest-link="${data.destId}"`}>${escapeHtml(data.cta)} ${icon("i-arrow", "icon--sm")}</a>
  </article>`;
}

export function initMoods() {
  const section = $("#moods");
  if (!section) return;
  const list = $("[data-mood-list]", section);
  const bg = $("[data-mood-bg]", section);
  const word = $("[data-mood-word]", section);
  const desc = $("[data-mood-desc]", section);
  const recs = $("[data-mood-recs]", section);
  const build = $("[data-mood-build]", section);

  list.innerHTML = moods
    .map(
      (m, i) => `<button class="mood" type="button" role="tab" id="mood-${m.id}" aria-selected="${i === 0}" data-mood="${m.id}">
        <span class="mood__disc">${ringSvg}<img src="${img(m.image, 220, 70)}" alt="" loading="lazy" />${icon(m.icon)}</span>
        <span class="mood__label">${m.label}</span>
      </button>`
    )
    .join("");

  bg.innerHTML = moods
    .map((m) => `<img class="moods__layer" data-layer="${m.id}" alt="" loading="lazy" decoding="async" src="${img(m.image, 1800, 72)}" />`)
    .join("");

  let current = null;
  let swapTimer;

  function select(id, { announce = true } = {}) {
    if (id === current) return;
    const m = moods.find((x) => x.id === id);
    if (!m) return;
    current = id;

    $$(".mood", list).forEach((b) => b.setAttribute("aria-selected", String(b.dataset.mood === id)));
    $$(".moods__layer", bg).forEach((l) => l.classList.toggle("is-on", l.dataset.layer === id));
    section.style.setProperty("--mood", m.tint);
    document.documentElement.style.setProperty("--mood", m.tint);

    word.classList.add("is-swapping");
    desc.classList.add("is-swapping");
    clearTimeout(swapTimer);
    swapTimer = setTimeout(() => {
      word.textContent = m.word || m.label;
      desc.textContent = m.desc;
      word.classList.remove("is-swapping");
      desc.classList.remove("is-swapping");
    }, 280);

    const d = destinations.find((x) => x.id === m.recs.dest);
    recs.innerHTML = [
      recCard(t("Where to go"), {
        title: `${d.name} — ${d.tagline}`,
        meta: `${d.region} · ${t("Best {best}", { best: d.best })}`,
        image: d.image,
        cta: t("Discover"),
        href: "#destinations",
        destId: d.id,
      }),
      recCard(t("What to do"), {
        ...m.recs.exp,
        meta: `${icon("i-star", "icon--sm")} ${m.recs.exp.rating} · ${t("from")} <b>${money(m.recs.exp.price)}</b> · ${t("via {partner}", { partner: m.recs.exp.partner })}`,
        cta: t(m.recs.exp.cta),
        href: "#partner",
        affiliate: true,
      }),
      recCard(t("Where to stay"), {
        ...m.recs.stay,
        meta: `${icon("i-star", "icon--sm")} ${m.recs.stay.rating} · ${t("from")} <b>${money(m.recs.stay.price)}</b>/${t("night")} · ${t("via {partner}", { partner: m.recs.stay.partner })}`,
        cta: t(m.recs.stay.cta),
        href: "#partner",
        affiliate: true,
      }),
    ].join("");
    $$(".rec", recs).forEach((r, i) => setTimeout(() => r.classList.remove("is-entering"), 90 * i + 60));
    refreshSaveButtons();
    if (announce) emit("mood:change", id);
  }

  list.addEventListener("click", (e) => {
    const b = e.target.closest("[data-mood]");
    if (b) select(b.dataset.mood);
  });
  // Hover previews on desktop, click commits (both select — hover is a soft preview)
  list.addEventListener("pointerover", (e) => {
    if (e.pointerType !== "mouse") return;
    const b = e.target.closest("[data-mood]");
    if (b) select(b.dataset.mood);
  });
  list.addEventListener("keydown", (e) => {
    if (!["ArrowRight", "ArrowLeft"].includes(e.key)) return;
    const i = moods.findIndex((m) => m.id === current);
    const n = (i + ((e.key === "ArrowRight") !== isRTL ? 1 : -1) + moods.length) % moods.length;
    select(moods[n].id);
    $(`#mood-${moods[n].id}`)?.focus();
  });

  build?.addEventListener("click", () => emit("planner:mood", current));

  select(moods[0].id, { announce: false });
}

export { saveButton };
