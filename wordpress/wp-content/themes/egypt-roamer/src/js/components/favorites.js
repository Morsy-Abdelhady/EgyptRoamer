/* Favourites — heart toggles on any [data-save] button, persisted per browser */
import { t } from "../i18n.js";
import { $$, icon, escapeHtml, toast, emit } from "../utils.js";
import { img } from "../data.js";

const KEY = "ei:saved";
let saved = new Map();

function load() {
  try {
    const raw = JSON.parse(localStorage.getItem(KEY) || "[]");
    saved = new Map(raw.map((it) => [it.id, it]));
  } catch {
    saved = new Map();
  }
}
function persist() {
  try {
    localStorage.setItem(KEY, JSON.stringify([...saved.values()]));
  } catch {
    /* storage unavailable — keep in memory */
  }
}

/** Markup for a save toggle. `item` = { id, title, image, meta } */
export const saveButton = (item) =>
  `<button class="save-btn" type="button" data-save='${escapeHtml(JSON.stringify(item))}' aria-pressed="${saved.has(item.id)}" aria-label="${escapeHtml(t("Save {title}", { title: item.title }))}">${icon("i-heart")}</button>`;

function syncButtons() {
  $$("[data-save]").forEach((btn) => {
    try {
      const { id } = JSON.parse(btn.dataset.save);
      btn.setAttribute("aria-pressed", String(saved.has(id)));
    } catch {}
  });
  $$("[data-fav-count]").forEach((el) => {
    el.textContent = saved.size ? String(saved.size) : "";
    el.classList.toggle("is-visible", saved.size > 0);
  });
}

export function renderSaved() {
  const list = document.querySelector("[data-saved-list]");
  if (!list) return;
  if (!saved.size) {
    list.innerHTML = `<div class="drawer__empty">${icon("i-heart")}<p>${t("Tap the heart on any experience, stay or destination to keep it here while you plan.")}</p><a class="btn btn--dark btn--sm" href="#experiences" data-close>${t("Browse experiences")}</a></div>`;
    return;
  }
  list.innerHTML = [...saved.values()]
    .map(
      (it) => `<article class="saved-item">
        <img src="${img(it.image, 200)}" alt="" loading="lazy" />
        <div><b>${escapeHtml(it.title)}</b><small>${escapeHtml(it.meta || "")}</small></div>
        <button class="save-btn" type="button" style="background:var(--stone);color:var(--clay)" data-save='${escapeHtml(JSON.stringify(it))}' aria-pressed="true" aria-label="${escapeHtml(t("Remove {title}", { title: it.title }))}">${icon("i-heart")}</button>
      </article>`
    )
    .join("");
}

export function initFavorites() {
  load();
  syncButtons();
  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-save]");
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    let item;
    try {
      item = JSON.parse(btn.dataset.save);
    } catch {
      return;
    }
    if (saved.has(item.id)) {
      saved.delete(item.id);
    } else {
      saved.set(item.id, item);
      toast(t("Saved “{title}”", { title: item.title }), "i-heart");
    }
    persist();
    syncButtons();
    if (btn.closest("[data-saved-list]")) renderSaved();
    emit("favorites:change", saved.size);
  });
}

/** Call after components render new save buttons. */
export const refreshSaveButtons = syncButtons;
