/* "What kind of Egypt are you looking for?" — mood dial that re-grades the
   section, swaps imagery and updates recommendations. */
import { t, isRTL } from "../i18n.js";
import { $, $$, icon, escapeHtml, priceOf, affAttrs, emit } from "../utils.js";
import { moods, destinations, img, srcset } from "../data.js";
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
    <a class="rec__cta" href="${escapeHtml(data.href)}" ${data.affiliate ? affAttrs(data) : data.href.startsWith("#") ? `data-dest-link="${data.destId}"` : ""}>${escapeHtml(data.cta)} ${icon("i-arrow", "icon--sm")}</a>
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
      (m, i) => `<button class="mood" type="button" role="tab" id="mood-${m.id}" aria-selected="${i === 0}" aria-controls="mood-panel" data-mood="${m.id}">
        <span class="mood__disc">${ringSvg}<img src="${img(m.image, 220, 70)}" alt="" loading="lazy" />${icon(m.icon)}</span>
        <span class="mood__label">${m.label}</span>
      </button>`
    )
    .join("");

  // On tall screens, crops shaped like the frame (as er_stock_crops( 'screen' ) in PHP): the same view, sharp.
  const crop = matchMedia("(max-aspect-ratio: 2/3)").matches ? [1.5, [640, 720, 828]] : matchMedia("(max-aspect-ratio: 1/1)").matches ? [1, [768, 1024, 1366]] : null;
  bg.innerHTML = moods
    // Full-bleed backgrounds: the width the screen needs (a phone took the 1800 px files, up to 1.35 MB each).
    // Only the visible layer has a source; the others are stacked at opacity 0 and loaded all at once with it
    // (about 900 KB on a phone for one visible picture), so they wait for warm().
    .map((m) => `<img class="moods__layer" data-layer="${m.id}" alt="" loading="lazy" decoding="async" data-src="${img(m.image, 1800, 72)}" data-srcset="${crop ? srcset(m.image, crop[1], crop[0], 72) : srcset(m.image, [600, 900, 1400, 1800])}" sizes="100vw" />`)
    .join("");
  const warm = (layer) => {
    if (!layer?.dataset.src) return;
    layer.srcset = layer.dataset.srcset;
    layer.src = layer.dataset.src;
    delete layer.dataset.src;
  };
  const warmAll = () => $$(".moods__layer", bg).forEach(warm);
  // The other moods are fetched when the visitor reaches for the dial (touch, focus) or, on a desktop, when the
  // section comes near the screen — so a hover preview has its picture ready.
  ["pointerdown", "focusin"].forEach((ev) => list.addEventListener(ev, warmAll, { once: true }));
  if (matchMedia("(min-width: 901px)").matches && "IntersectionObserver" in window) {
    const io = new IntersectionObserver((entries) => {
      if (entries.some((e) => e.isIntersecting)) { io.disconnect(); warmAll(); }
    }, { rootMargin: "400px 0px" });
    io.observe(section);
  }

  let current = null;
  let swapTimer;

  function select(id, { announce = true } = {}) {
    if (id === current) return;
    const m = moods.find((x) => x.id === id);
    if (!m) return;
    current = id;

    $$(".mood", list).forEach((b) => b.setAttribute("aria-selected", String(b.dataset.mood === id)));
    // Keep the previous picture until the new one is decoded (no dark flash while it downloads).
    const layer = $(`.moods__layer[data-layer="${id}"]`, bg);
    const fresh = Boolean(layer?.dataset.src);
    warm(layer);
    const show = () => { if (current === id) $$(".moods__layer", bg).forEach((l) => l.classList.toggle("is-on", l === layer)); };
    if (!fresh || !$(".moods__layer.is-on", bg) || !layer.decode) show();
    else Promise.race([layer.decode(), new Promise((r) => setTimeout(r, 1500))]).then(show, show);
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
    // Only verified facts: ratings and prices appear only when the data carries them.
    const offerMeta = (o, unit) => {
      const price = priceOf(o);
      return [
        o.rating ? `${icon("i-star", "icon--sm")} ${o.rating}` : "",
        price ? `${t("from")} <b>${escapeHtml(price)}</b>${unit ? `/${escapeHtml(o.unitText || t(unit))}` : ""}` : "",
        o.partner ? escapeHtml(t("via {partner}", { partner: o.partner })) : "",
      ].filter(Boolean).join(" · ");
    };
    recs.innerHTML = [
      d &&
        recCard(t("Where to go"), {
          title: `${d.name} — ${d.tagline}`,
          meta: escapeHtml(`${d.region} · ${t("Best {best}", { best: d.best })}`),
          image: d.image,
          cta: t("Discover"),
          href: d.url || "#destinations",
          destId: d.id,
        }),
      m.recs.exp &&
        recCard(t("What to do"), {
          ...m.recs.exp,
          meta: offerMeta(m.recs.exp, ""),
          cta: t(m.recs.exp.cta),
          href: m.recs.exp.href || "#partner",
          affiliate: true,
        }),
      m.recs.stay &&
        recCard(t("Where to stay"), {
          ...m.recs.stay,
          meta: offerMeta(m.recs.stay, "night"),
          cta: t(m.recs.stay.cta),
          href: m.recs.stay.href || "#partner",
          affiliate: true,
        }),
    ]
      .filter(Boolean)
      .join("");
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

  if (!moods.length) return;
  select(moods[0].id, { announce: false });
}

export { saveButton };
