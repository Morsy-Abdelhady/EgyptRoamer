/* Affiliate discovery layer — Hotels / Tours / Cruises / Transfers / Cars.
   Each tab = one editorial "hero" + a short, honest shortlist of partner offers. */
import { t, isRTL, lang } from "../i18n.js";
import { $, $$, icon, escapeHtml, fmt, priceOf, affAttrs, on } from "../utils.js";
import { partnerCategories, img } from "../data.js";
import { saveButton, refreshSaveButtons } from "./favorites.js";

function offerRow(o, catId) {
  const price = priceOf(o);
  return `<article class="offer">
    <div class="offer__img">
      <img src="${img(o.image, 420)}" alt="${escapeHtml(o.name)}" loading="lazy" decoding="async" />
      ${o.badge ? `<span class="chip chip--gold">${escapeHtml(o.badge)}</span>` : ""}
    </div>
    <div class="offer__body">
      ${o.location ? `<span class="offer__loc">${icon("i-pin")}${escapeHtml(o.location)}</span>` : ""}
      <h4 class="offer__name">${escapeHtml(o.name)}</h4>
      <div class="offer__meta">
        ${o.rating ? `<span class="rating">${icon("i-star")}${o.rating} <span>(${fmt(o.reviews)})</span></span>` : ""}
        ${o.meta ? `<span>${escapeHtml(o.meta)}</span>` : ""}
      </div>
    </div>
    <div class="offer__side">
      ${price ? `<p class="price">${t("from")}<b>${escapeHtml(price)} <small>/ ${escapeHtml(o.unitText || t(o.unit))}</small></b></p>` : ""}
      <a class="btn btn--outline btn--sm" href="${escapeHtml(o.href || "#partner")}" ${affAttrs(o)} data-affiliate-cat="${catId}">
        ${escapeHtml(t(o.cta))} ${icon("i-arrow-ur", "icon--sm")}
      </a>
      ${o.partner ? `<span class="via">${escapeHtml(t("via {partner}", { partner: t(o.partner) }))}</span>` : ""}
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
          ${c.trust && c.trust.length ? `<ul class="pp-trust">${c.trust.map((line) => `<li>${icon("i-check")}${escapeHtml(line)}</li>`).join("")}</ul>` : ""}
          ${c.href ? `<a class="btn btn--primary" href="${escapeHtml(c.href)}" ${affAttrs(c)} data-magnetic>${escapeHtml(c.compare)} ${icon("i-arrow", "icon--arrow")}</a>` : c.allHref === undefined ? `<a class="btn btn--primary" href="#partner" rel="sponsored noopener" data-affiliate="our partners" data-magnetic>${escapeHtml(c.compare)} ${icon("i-arrow", "icon--arrow")}</a>` : ""}
        </div>
      </div>
      <div class="offers">
        <div class="offers__head">
          <span class="t-label">${t("Editor's shortlist · {n} picks", { n: c.items.length })}</span>
          ${c.allHref ? `<a class="link" href="${escapeHtml(c.allHref)}">${t("See all")} ${icon("i-arrow", "icon--sm")}</a>` : c.allHref === undefined ? `<a class="link" href="#partner" rel="sponsored noopener" data-affiliate="our partners">${lang === "en" ? `See all ${c.label.toLowerCase()}` : t("See all")} ${icon("i-arrow", "icon--sm")}</a>` : ""}
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
  if (!partnerCategories.length) return;
  select(partnerCategories[0].id, { animate: false });
  requestAnimationFrame(placeInk);
}

export { saveButton };
