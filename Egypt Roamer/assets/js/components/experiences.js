/* Featured experiences — draggable horizontal rail of affiliate cards */
import { t, isRTL } from "../i18n.js";
import { $, $$, icon, escapeHtml, fmt, money, on, clamp } from "../utils.js";
import { experiences, experienceFilters, img } from "../data.js";
import { saveButton, refreshSaveButtons } from "./favorites.js";

/** One experience card. Shape matches data.experiences — swap in partner API data. */
export const experienceCard = (x) => `<li class="card" data-tag="${x.tag}">
  <a href="${x.href}" class="media media--hover" rel="sponsored noopener" data-affiliate="${escapeHtml(x.partner)}" tabindex="-1" aria-hidden="true">
    <img src="${img(x.image, 700)}" srcset="${img(x.image, 420)} 420w, ${img(x.image, 700)} 700w" sizes="(max-width: 700px) 78vw, 330px" alt="" loading="lazy" decoding="async" />
  </a>
  <div class="card__top">
    ${x.badge ? `<span class="chip chip--gold card__badge">${x.badge}</span>` : "<span></span>"}
    ${saveButton({ id: x.id, title: x.title, image: x.image, meta: `${x.location} · ${t("from")} ${money(x.price)}` })}
  </div>
  <div class="card__body">
    <span class="card__loc">${icon("i-pin", "icon--sm")}${escapeHtml(x.location)}</span>
    <h3 class="card__title">${escapeHtml(x.title)}</h3>
    <div class="card__meta">
      <span class="rating">${icon("i-star")}${x.rating} <span>(${fmt(x.reviews)})</span></span>
      <span>${icon("i-clock")}${escapeHtml(x.duration)}</span>
    </div>
    <div class="card__foot">
      <p class="price">${t("from")}<b>${money(x.price)}</b></p>
      <div style="display:grid;justify-items:end;gap:.2rem">
        <a class="link" href="${x.href}" rel="sponsored noopener" data-affiliate="${escapeHtml(x.partner)}">${escapeHtml(t(x.cta))} ${icon("i-arrow", "icon--sm")}</a>
        <span class="via">${escapeHtml(t("via {partner}", { partner: x.partner }))}</span>
      </div>
    </div>
  </div>
</li>`;

export function initExperiences() {
  const root = $("#experiences");
  if (!root) return;
  const track = $("[data-exp-track]", root);
  const filters = $("[data-exp-filters]", root);
  const scroller = $("[data-rail-scroller]", root);
  const bar = $("[data-exp-progress]", root);
  const prev = $("[data-exp-prev]", root);
  const next = $("[data-exp-next]", root);

  track.innerHTML = experiences.map(experienceCard).join("");
  filters.innerHTML = experienceFilters
    .map((f, i) => `<button class="filter" type="button" aria-pressed="${i === 0}" data-filter="${f.id}">${f.label}</button>`)
    .join("");
  // Make the whole card clickable for the title too
  $$(".card", track).forEach((c) => {
    const t = c.querySelector(".card__title");
    t.style.cursor = "pointer";
    t.addEventListener("click", () => c.querySelector(".card__foot .link")?.click());
  });

  const updateProgress = () => {
    const max = scroller.scrollWidth - scroller.clientWidth;
    const pos = Math.abs(scroller.scrollLeft); // scrollLeft runs negative in RTL
    const p = max > 0 ? pos / max : 1;
    const visible = max > 0 ? scroller.clientWidth / scroller.scrollWidth : 1;
    const dir = isRTL ? -1 : 1;
    bar.style.transform = `translateX(${dir * p * (1 - visible) * 100}%) scaleX(${visible})`;
    bar.style.transformOrigin = isRTL ? "right" : "left";
    prev.disabled = pos < 8;
    next.disabled = pos > max - 8;
  };
  // progress bar: width = visible share, slides with scroll
  bar.parentElement.style.position = "relative";
  bar.style.width = "100%";
  scroller.addEventListener("scroll", updateProgress, { passive: true });
  window.addEventListener("resize", updateProgress);

  const step = () => ($(".card:not(.is-hidden)", track)?.offsetWidth || 300) + 24;
  const forward = isRTL ? -1 : 1;
  prev.addEventListener("click", () => scroller.scrollBy({ left: -forward * step() * 2, behavior: "smooth" }));
  next.addEventListener("click", () => scroller.scrollBy({ left: forward * step() * 2, behavior: "smooth" }));

  function applyFilter(id) {
    $$(".filter", filters).forEach((b) => b.setAttribute("aria-pressed", String(b.dataset.filter === id)));
    $$(".card", track).forEach((c) => c.classList.toggle("is-hidden", id !== "all" && c.dataset.tag !== id));
    scroller.scrollTo({ left: 0, behavior: "smooth" });
    requestAnimationFrame(updateProgress);
  }
  filters.addEventListener("click", (e) => {
    const b = e.target.closest("[data-filter]");
    if (b) applyFilter(b.dataset.filter);
  });
  on("experiences:filter", applyFilter);

  /* Drag to scroll (mouse only; touch uses native momentum) */
  let down = false;
  let startX = 0;
  let startLeft = 0;
  let moved = 0;
  scroller.addEventListener("pointerdown", (e) => {
    if (e.pointerType !== "mouse" || e.button !== 0) return;
    down = true;
    moved = 0;
    startX = e.clientX;
    startLeft = scroller.scrollLeft;
  });
  window.addEventListener("pointermove", (e) => {
    if (!down) return;
    const dx = e.clientX - startX;
    moved = Math.max(moved, Math.abs(dx));
    if (moved > 6) scroller.classList.add("is-dragging");
    scroller.scrollLeft = startLeft - dx;
  });
  window.addEventListener("pointerup", () => {
    if (!down) return;
    down = false;
    setTimeout(() => scroller.classList.remove("is-dragging"), 0);
    // snap to nearest card
    const w = step();
    const snapped = Math.round(scroller.scrollLeft / w) * w;
    const left = isRTL ? clamp(snapped, -scroller.scrollWidth, 0) : clamp(snapped, 0, scroller.scrollWidth);
    scroller.scrollTo({ left, behavior: "smooth" });
  });
  scroller.addEventListener("dragstart", (e) => e.preventDefault());

  refreshSaveButtons();
  updateProgress();
}
