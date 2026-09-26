/* Affiliate discovery layer — Hotels / Tours / Cruises / Transfers / Cars.
   Each tab = one editorial "hero" + a short, honest shortlist of partner offers. */
import { t, isRTL, lang } from "../i18n.js";
import { $, $$, icon, escapeHtml, fmt, money, on } from "../utils.js";
import { partnerCategories, img } from "../data.js";
import { saveButton, refreshSaveButtons } from "./favorites.js";

function offerRow(o, catId) {
  return `<article class="offer">
    <div class="offer__img">
      <img src="${img(o.image, 420)}" alt="${escapeHtml(o.name)}" loading="lazy" decoding="async" />
      ${o.badge ? `<span class="chip chip--gold">${o.badge}</span>` : ""}
    </div>
    <div class="offer__body">
      <span class="offer__loc">${icon("i-pin")}${escapeHtml(o.location)}</span>
      <h4 class="offer__name">${escapeHtml(o.name)}</h4>
      <div class="offer__meta">
        <span class="rating">${icon("i-star")}${o.rating} <span>(${fmt(o.reviews)})</span></span>
        <span>${escapeHtml(o.meta)}</span>
      </div>
    </div>
    <div class="offer__side">
      <p class="price">${t("from")}<b>${money(o.price)} <small>/ ${t(o.unit)}</small></b></p>
      <a class="btn btn--outline btn--sm" href="#partner" rel="sponsored noopener" data-affiliate="${escapeHtml(o.partner)}" data-affiliate-cat="${catId}">
        ${escapeHtml(t(o.cta))} ${icon("i-arrow-ur", "icon--sm")}
      </a>
      <span class="via">${escapeHtml(t("via {partner}", { partner: t(o.partner) }))}</span>
    </div>
  </article>`;
}

export function initPartners() {
  const root = $("#partners");
  if (!root) return;
  const tabs = $("[data-partner-tabs]", root);
  const panel = $("[data-partner-panel]", root);
  const ink = $(".partners__ink", tabs);

  tabs.insertAdjacentHTML(
    "afterbegin",
    partnerCategories
      .map(
        (c, i) =>
          `<button class="ptab" type="button" role="tab" id="ptab-${c.id}" aria-selected="${i === 0}" aria-controls="partner-panel" data-ptab="${c.id}">${icon(c.icon)}${c.label}</button>`
      )
      .join("")
  );
  panel.id = "partner-panel";

  const placeInk = () => {
    const sel = $('[aria-selected="true"]', tabs);
    if (!sel) return;
    ink.style.transform = `translateX(${sel.offsetLeft}px) scaleX(${sel.offsetWidth / 100})`;
  };

  let current = null;
  function render(c) {
    panel.setAttribute("aria-labelledby", `ptab-${c.id}`);
    panel.innerHTML = `
      <div class="pp-hero">
        <img src="${img(c.image, 1100)}" alt="" loading="lazy" decoding="async" />
        <div class="pp-hero__body">
          <span class="t-label" style="color:var(--sand)">${c.label}</span>
          <h3>${c.headline}</h3>
          <p>${c.copy}</p>
          <ul class="pp-trust">${c.trust.map((t) => `<li>${icon("i-check")}${t}</li>`).join("")}</ul>
          <a class="btn btn--primary" href="#partner" rel="sponsored noopener" data-affiliate="our partners" data-magnetic>${c.compare} ${icon("i-arrow", "icon--arrow")}</a>
        </div>
      </div>
      <div class="offers">
        <div class="offers__head">
          <span class="t-label">${t("Editor's shortlist · {n} picks", { n: c.items.length })}</span>
          <a class="link" href="#partner" rel="sponsored noopener" data-affiliate="our partners">${lang === "en" ? `See all ${c.label.toLowerCase()}` : t("See all")} ${icon("i-arrow", "icon--sm")}</a>
        </div>
        ${c.items.map((o) => offerRow(o, c.id)).join("")}
      </div>`;
  }

  function select(id, { animate = true } = {}) {
    const c = partnerCategories.find((x) => x.id === id);
    if (!c || id === current) return;
    current = id;
    $$(".ptab", tabs).forEach((t) => t.setAttribute("aria-selected", String(t.dataset.ptab === id)));
    placeInk();
    if (!animate) return render(c);
    panel.classList.add("is-swapping");
    setTimeout(() => {
      render(c);
      refreshSaveButtons();
      requestAnimationFrame(() => panel.classList.remove("is-swapping"));
    }, 260);
  }

  tabs.addEventListener("click", (e) => {
    const t = e.target.closest("[data-ptab]");
    if (t) select(t.dataset.ptab);
  });
  tabs.addEventListener("keydown", (e) => {
    if (!["ArrowRight", "ArrowLeft"].includes(e.key)) return;
    const i = partnerCategories.findIndex((c) => c.id === current);
    const n = (i + ((e.key === "ArrowRight") !== isRTL ? 1 : -1) + partnerCategories.length) % partnerCategories.length;
    select(partnerCategories[n].id);
    $(`#ptab-${partnerCategories[n].id}`)?.focus();
  });
  window.addEventListener("resize", placeInk);
  document.fonts?.ready.then(placeInk);

  on("partners:tab", (id) => select(id));
  select("hotels", { animate: false });
  requestAnimationFrame(placeInk);
}

export { saveButton };
