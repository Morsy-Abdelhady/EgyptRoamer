/* Roamer Journal — magazine layout: one feature story + a numbered index */
import { t, tp } from "../i18n.js";
import { $, $$, icon, escapeHtml } from "../utils.js";
import { guides, img } from "../data.js";

export function initGuide() {
  const root = $("#guide");
  if (!root) return;
  const cats = $("[data-guide-cats]", root);
  const feature = $("[data-guide-feature]", root);
  const list = $("[data-guide-list]", root);

  if (!guides.length) return;
  // A guide in another language than the page (English-only guides on translated pages) says so.
  const hl = (g) => (g.lang ? ` hreflang="${escapeHtml(g.lang)}"` : "");
  // dir="ltr" too: English titles inside an Arabic page otherwise reorder ("7 Days…" → "Days… 7").
  const tl = (g) => (g.lang ? ` lang="${escapeHtml(g.lang)}" dir="ltr"` : "");
  const foreign = guides.find((g) => g.lang);
  const tag = (g) => (g.lang ? ` <span class="lang-tag" lang="${escapeHtml(g.lang)}">${escapeHtml(g.lang.toUpperCase())}</span>` : "");

  const ALL = "__all";
  const categories = [ALL, ...new Set(guides.map((g) => g.cat))];
  cats.innerHTML = categories
    .map((c, i) => `<button class="filter" type="button" aria-pressed="${i === 0}" data-cat="${c}">${c === ALL ? t("All") : foreign ? `<span lang="${escapeHtml(foreign.lang)}" dir="ltr">${c}</span>` : c}</button>`)
    .join("");

  function render(cat) {
    const set = cat === ALL ? guides : guides.filter((g) => g.cat === cat);
    const pool = set.length ? set : guides;
    const [lead, ...rest] = pool;
    const others = (rest.length ? rest : guides.filter((g) => g !== lead)).slice(0, 6);

    feature.innerHTML = `<a class="story-feature" href="${lead.href}"${hl(lead)} data-reveal="fade">
      <div class="media media--hover"><img src="${img(lead.image, 1300)}" srcset="${img(lead.image, 800)} 800w, ${img(lead.image, 1300)} 1300w, ${img(lead.image, 1800)} 1800w" sizes="(max-width: 900px) 100vw, 56vw" alt="" loading="lazy" decoding="async" /></div>
      <div class="story-feature__meta"><span class="t-label"><span${tl(lead)}>${lead.cat}</span>${tag(lead)}</span><span>${icon("i-clock", "icon--sm")} ${tp(lead.read, "{n} min read", "{n} min read")}</span></div>
      <h3${tl(lead)}>${escapeHtml(lead.title)}</h3>
      ${lead.excerpt ? `<p${tl(lead)}>${escapeHtml(lead.excerpt)}</p>` : ""}
      <span class="link" style="justify-self:start">${t("Read the guide")} ${icon("i-arrow", "icon--sm")}</span>
    </a>`;

    list.innerHTML =
      others
        .map(
          (g, i) => `<li class="story"><a href="${g.href}"${hl(g)}>
            <span class="story__num">${String(i + 1).padStart(2, "0")}</span>
            <span class="story__body">
              <span class="story__meta"><span${tl(g)}>${g.cat}</span> · ${tp(g.read, "{n} min read", "{n} min read")}${tag(g)}</span>
              <span class="story__title"${tl(g)}>${escapeHtml(g.title)}</span>
            </span>
            <span class="story__thumb"><img src="${img(g.image, 200)}" alt="" loading="lazy" /></span>
          </a></li>`
        )
        .join("") +
      `<li class="guide__all"><a class="btn btn--outline" href="${escapeHtml((window.ER_DATA && window.ER_DATA.guidesUrl) || "/guide")}">${t("Explore the Journal")} ${icon("i-arrow", "icon--arrow")}</a></li>`;
    requestAnimationFrame(() => $$("[data-reveal]", feature).forEach((el) => el.classList.add("is-in")));
  }

  cats.addEventListener("click", (e) => {
    const b = e.target.closest("[data-cat]");
    if (!b) return;
    $$(".filter", cats).forEach((x) => x.setAttribute("aria-pressed", String(x === b)));
    render(b.dataset.cat);
  });
  render(ALL);
}
