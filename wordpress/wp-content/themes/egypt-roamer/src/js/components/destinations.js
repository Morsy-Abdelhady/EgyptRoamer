/* Destinations — editorial index (left) + cinematic stage (right).
   Desktop: hover/focus a name to reveal it; auto-advances while in view.
   Mobile: the index becomes a horizontal rail of full-bleed cards. */
import { t } from "../i18n.js";
import { $, $$, icon, escapeHtml, on, isMobile, reducedMotion } from "../utils.js";
import { destinations, img, srcset } from "../data.js";
import { saveButton, refreshSaveButtons } from "./favorites.js";

const AUTO_MS = 7000;
const pad = (n) => String(n).padStart(2, "0");

export function initDestinations() {
  const root = $("#destinations");
  if (!root) return;
  const list = $("[data-dest-list]", root);
  const frames = $("[data-dest-frames]", root);
  const panel = $("[data-dest-panel]", root);
  const count = $("[data-dest-count]", root);
  const stage = $("[data-dest-stage]", root);

  list.innerHTML = destinations
    .map(
      (d, i) => `<li class="dest__item" data-i="${i}">
        <button class="dest__btn" type="button" aria-controls="dest-panel" data-dest="${d.id}">
          <span class="dest__num">${pad(i + 1)}</span>
          <span class="dest__name">${d.name}</span>
          <span class="dest__region">${d.region}</span>
        </button>
        <article class="dest-card">
          <img src="${img(d.image, 800)}" alt="${escapeHtml(d.name)}" loading="lazy" decoding="async" />
          <span class="dest-card__num">${pad(i + 1)} / ${pad(destinations.length)}</span>
          <div class="dest-card__body">
            <span class="t-label" style="color:var(--sand)">${d.region}</span>
            <h3>${d.name}</h3>
            <p>${d.desc}</p>
            <div class="dest__hl">${d.highlights.map((h) => `<span class="chip chip--glass">${h}</span>`).join("")}</div>
            <a class="link" ${d.url ? `href="${escapeHtml(d.url)}"` : `href="#map" data-map-focus="${d.id}"`} style="justify-self:start;margin-top:.4rem">${t("Explore {name}", { name: d.name })} ${icon("i-arrow", "icon--sm")}</a>
          </div>
        </article>
      </li>`
    )
    .join("");

  frames.innerHTML = destinations
    .map(
      (d) => `<div class="dest__frame" data-frame="${d.id}">
        <img src="${img(d.image, 1400)}" srcset="${srcset(d.image, [700, 1100, 1600, 2200])}" sizes="(max-width: 900px) 100vw, 58vw" alt="${escapeHtml(d.name)}, ${escapeHtml(d.region)}" loading="lazy" decoding="async" />
      </div>`
    )
    .join("");
  panel.id = "dest-panel";

  const items = $$(".dest__item", list);
  const frameEls = $$(".dest__frame", frames);
  let current = -1;
  let timer = null;
  let visible = false;
  let paused = false;

  function renderPanel(d) {
    panel.innerHTML = `
      <span class="t-label dest__region-lg">${d.region}</span>
      <h3 class="dest__title">${d.name}<em class="serif">${d.tagline}</em></h3>
      <p class="dest__desc">${d.desc}</p>
      <div class="dest__hl">${d.highlights.map((h) => `<span class="chip chip--glass">${h}</span>`).join("")}</div>
      <div class="dest__foot">
        <div class="dest__facts">
          <span>${t("Best time")}<b>${d.best}</b></span>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center">
          ${saveButton({ id: `dest-${d.id}`, title: d.name, image: d.image, meta: d.region })}
          <a ${d.url ? `href="${escapeHtml(d.url)}"` : `href="#map" data-map-focus="${d.id}"`} class="btn btn--ghost btn--sm">${t("Discover {name}", { name: d.name })} ${icon("i-arrow", "icon--arrow")}</a>
        </div>
      </div>`;
  }

  function setActive(i, { user = false } = {}) {
    if (i === current) return;
    const prev = current;
    current = i;
    const d = destinations[i];
    items.forEach((el, k) => el.classList.toggle("is-active", k === i));
    frameEls.forEach((f, k) => {
      f.classList.remove("was-on");
      if (k === prev) f.classList.add("was-on");
      f.classList.toggle("is-on", k === i);
    });
    count.textContent = `${pad(i + 1)} / ${pad(destinations.length)}`;

    if (prev === -1) {
      renderPanel(d);
    } else {
      panel.classList.add("is-swapping");
      setTimeout(() => {
        renderPanel(d);
        refreshSaveButtons();
        requestAnimationFrame(() => panel.classList.remove("is-swapping"));
      }, 320);
    }
    if (user) restartTimer();
  }

  function restartTimer() {
    clearInterval(timer);
    // restart the CSS progress line
    const active = items[current];
    if (active) {
      active.classList.remove("is-active");
      void active.offsetWidth;
      active.classList.add("is-active");
    }
    if (!visible || paused || reducedMotion() || isMobile()) return;
    timer = setInterval(() => setActive((current + 1) % destinations.length), AUTO_MS);
  }

  list.addEventListener("pointerover", (e) => {
    const b = e.target.closest(".dest__btn");
    if (b && e.pointerType === "mouse") setActive(Number(b.closest(".dest__item").dataset.i), { user: true });
  });
  list.addEventListener("click", (e) => {
    const b = e.target.closest(".dest__btn");
    if (b) setActive(Number(b.closest(".dest__item").dataset.i), { user: true });
  });
  list.addEventListener("focusin", (e) => {
    const b = e.target.closest(".dest__btn");
    if (b) setActive(Number(b.closest(".dest__item").dataset.i), { user: true });
  });

  const pause = (state) => {
    paused = state;
    items.forEach((el) => el.classList.toggle("is-paused", state));
    if (state) clearInterval(timer);
    else restartTimer();
  };
  [stage, list].forEach((el) => {
    el.addEventListener("mouseenter", () => pause(true));
    el.addEventListener("mouseleave", () => pause(false));
  });

  new IntersectionObserver(
    ([en]) => {
      visible = en.isIntersecting;
      if (visible) restartTimer();
      else clearInterval(timer);
    },
    { threshold: 0.35 }
  ).observe(root);

  root.style.setProperty("--dest-timer", `${AUTO_MS}ms`);
  if (!destinations.length) return;
  setActive(0);

  on("destinations:select", (id) => {
    const i = destinations.findIndex((d) => d.id === id);
    if (i < 0) return;
    setActive(i, { user: true });
    if (isMobile()) items[i]?.scrollIntoView({ behavior: "smooth", inline: "start", block: "nearest" });
  });
}
