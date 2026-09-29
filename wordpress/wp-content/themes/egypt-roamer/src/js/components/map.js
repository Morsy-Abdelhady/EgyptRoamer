/* Interactive dark map of Egypt — hand-projected SVG (no map SDK).
   Hover a point for a preview, click to zoom in, reset with the compass. */
import { t } from "../i18n.js";
import { $, $$, icon, on, lerp, reducedMotion, escapeHtml } from "../utils.js";
import { destinations, img } from "../data.js";

/* Equirectangular projection tuned to Egypt's bounding box */
const K = 40;
const px = ([lon, lat]) => [(lon - 24.2) * K + 12, (32.2 - lat) * K + 18];
const path = (pts, close = false) =>
  pts.map((p, i) => `${i ? "L" : "M"}${px(p).map((n) => n.toFixed(1)).join(" ")}`).join("") + (close ? "Z" : "");
const smooth = (pts) => {
  // Catmull-Rom → cubic Bézier for a natural river line
  const P = pts.map(px);
  let d = `M${P[0][0].toFixed(1)} ${P[0][1].toFixed(1)}`;
  for (let i = 0; i < P.length - 1; i++) {
    const p0 = P[i - 1] || P[i];
    const p1 = P[i];
    const p2 = P[i + 1];
    const p3 = P[i + 2] || p2;
    const c1 = [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6];
    const c2 = [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6];
    d += `C${c1[0].toFixed(1)} ${c1[1].toFixed(1)} ${c2[0].toFixed(1)} ${c2[1].toFixed(1)} ${p2[0].toFixed(1)} ${p2[1].toFixed(1)}`;
  }
  return d;
};

/* Simplified outline of Egypt (lon, lat) */
const EGYPT = [
  [25.15, 31.65], [25.9, 31.62], [27.25, 31.36], [28.4, 31.08], [29.05, 30.92], [29.92, 31.21], [30.35, 31.48],
  [30.9, 31.58], [31.4, 31.6], [31.82, 31.53], [32.3, 31.28], [32.9, 31.12], [33.8, 31.16], [34.22, 31.32],
  [34.9, 29.5], [34.72, 29.05], [34.55, 28.5], [34.43, 28.05], [34.25, 27.73], [33.95, 28.1], [33.55, 28.55],
  [33.1, 29.1], [32.6, 29.95], [32.38, 29.6], [32.65, 28.95], [33.2, 28.05], [33.84, 27.2], [33.95, 26.72],
  [34.28, 26.1], [34.85, 25.1], [35.45, 24.1], [35.82, 23.45], [36.35, 22.75], [36.9, 22.0], [31.3, 22.0],
  [25.0, 22.0], [25.0, 29.2], [24.72, 30.2], [24.92, 31.0],
];
const NILE = [
  [32.88, 23.98], [32.9, 24.09], [32.93, 24.45], [32.87, 24.98], [32.55, 25.3], [32.64, 25.69], [32.72, 26.16],
  [32.25, 26.06], [31.9, 26.33], [31.7, 26.56], [31.18, 27.18], [30.84, 27.72], [30.75, 28.1], [31.1, 29.07],
  [31.25, 29.6], [31.24, 30.04], [31.15, 30.25],
];
const ROSETTA = [[31.15, 30.25], [30.9, 30.7], [30.62, 31.1], [30.42, 31.46]];
const DAMIETTA = [[31.15, 30.25], [31.35, 30.6], [31.55, 31.0], [31.82, 31.48]];
const NASSER = [[31.35, 22.0], [31.55, 22.45], [31.95, 22.75], [32.35, 23.1], [32.6, 23.5], [32.88, 23.98]];


const FULL = { x: 0, y: 0, w: 560, h: 470 };

export function initMap() {
  const root = $("[data-map]");
  if (!root) return;
  const svg = $("[data-map-svg]", root);
  const world = $("[data-map-world]", root);
  const card = $("[data-map-card]", root);
  const list = $("[data-map-list]");
  const reset = $("[data-map-reset]", root);

  /* ----- draw ----- */
  let grat = "";
  for (let lon = 25; lon <= 37; lon += 2) {
    const [x] = px([lon, 0]);
    grat += `<line class="map-grat" x1="${x}" y1="0" x2="${x}" y2="470"/>`;
    grat += `<text class="map-grat-label" x="${x + 3}" y="462">${lon}°E</text>`;
  }
  for (let lat = 22; lat <= 32; lat += 2) {
    const [, y] = px([0, lat]);
    grat += `<line class="map-grat" x1="0" y1="${y}" x2="560" y2="${y}"/>`;
    grat += `<text class="map-grat-label" x="4" y="${y - 3}">${lat}°N</text>`;
  }

  const cairo = destinations.find((d) => d.id === "cairo");
  const routes = destinations
    .filter((d) => d.id !== "cairo")
    .map((d) => {
      const [x1, y1] = px(cairo.coords);
      const [x2, y2] = px(d.coords);
      const mx = (x1 + x2) / 2;
      const my = (y1 + y2) / 2;
      const dx = x2 - x1;
      const dy = y2 - y1;
      const bend = 0.18;
      const cx = mx - dy * bend;
      const cy = my + dx * bend;
      return `<path class="map-route" data-route="${d.id}" d="M${x1} ${y1}Q${cx.toFixed(1)} ${cy.toFixed(1)} ${x2} ${y2}"/>`;
    })
    .join("");

  const labelPos = {
    cairo: [10, 4], alexandria: [-8, -12, "end"], siwa: [10, 4], luxor: [-10, 4, "end"],
    aswan: [-10, 4, "end"], hurghada: [10, 4], sharm: [10, 4],
  };
  const points = destinations
    .map((d) => {
      const [x, y] = px(d.coords);
      const [lx, ly, anchor = "start"] = labelPos[d.id];
      return `<g class="map-point" data-point="${d.id}" transform="translate(${x} ${y})" tabindex="0" role="button" aria-label="${escapeHtml(t("{name} — preview", { name: d.name }))}">
        <circle class="map-point__hit" r="22"/>
        <circle class="map-point__halo" r="16"/>
        <circle class="map-point__ring" r="10"/>
        <circle class="map-point__core" r="4.2"/>
        <text class="map-point__label" x="${lx}" y="${ly}" text-anchor="${anchor}">${d.name}</text>
      </g>`;
    })
    .join("");

  const [medX, medY] = px([27.6, 32.6]);
  const [redX, redY] = px([35.6, 25.6]);
  world.innerHTML = `
    ${grat}
    <text class="map-sea-label" x="${medX}" y="${medY + 6}">${t("Mediterranean Sea")}</text>
    <text class="map-sea-label" x="${redX}" y="${redY}" transform="rotate(58 ${redX} ${redY})">${t("Red Sea")}</text>
    <path class="map-land" d="${path(EGYPT, true)}"/>
    <path class="map-land-dots" d="${path(EGYPT, true)}"/>
    <path class="map-lake" d="${smooth(NASSER)}" style="fill:none;stroke:#0F6B7A;stroke-width:6;stroke-linecap:round;opacity:.8"/>
    <path class="map-nile" d="${smooth(NILE)}"/>
    <path class="map-nile" d="${smooth(ROSETTA)}" style="stroke-width:1.4"/>
    <path class="map-nile" d="${smooth(DAMIETTA)}" style="stroke-width:1.4"/>
    ${routes}
    ${points}`;

  list.innerHTML = destinations
    .map(
      (d) => `<li><button type="button" data-map-item="${d.id}"><span class="pip"></span><span class="map__li-name">${d.name}</span></button></li>`
    )
    .join("");

  /* ----- viewBox tween ----- */
  let vb = { ...FULL };
  let target = { ...FULL };
  let raf = null;
  const applyVB = () => svg.setAttribute("viewBox", `${vb.x.toFixed(2)} ${vb.y.toFixed(2)} ${vb.w.toFixed(2)} ${vb.h.toFixed(2)}`);
  const tick = () => {
    const k = reducedMotion() ? 1 : 0.12;
    vb = { x: lerp(vb.x, target.x, k), y: lerp(vb.y, target.y, k), w: lerp(vb.w, target.w, k), h: lerp(vb.h, target.h, k) };
    applyVB();
    if (Math.abs(vb.w - target.w) + Math.abs(vb.x - target.x) + Math.abs(vb.y - target.y) > 0.05) raf = requestAnimationFrame(tick);
    else raf = null;
  };
  const zoomTo = (t) => {
    target = t;
    if (!raf) raf = requestAnimationFrame(tick);
  };

  /* ----- preview card ----- */
  let activeId = null;
  let zoomed = false;
  function show(id) {
    const d = destinations.find((x) => x.id === id);
    if (!d) return;
    const changed = activeId !== id;
    activeId = id;
    $$(".map-point", svg).forEach((p) => p.classList.toggle("is-active", p.dataset.point === id));
    $$(".map-route", svg).forEach((r) => r.classList.toggle("is-on", r.dataset.route === id || (id === "aswan" && r.dataset.route === "luxor")));
    $$("[data-map-item]", list).forEach((b) => b.classList.toggle("is-active", b.dataset.mapItem === id));
    if (changed) {
      card.hidden = false;
      card.innerHTML = `
        <div class="map__card-img"><img src="${img(d.image, 500)}" alt="" /></div>
        <div class="map__card-body">
          <span class="t-label">${d.region}</span>
          <h3>${d.name}</h3>
          <p>${t("Best time · {best}", { best: d.best })}</p>
          <a class="link" ${d.url ? `href="${escapeHtml(d.url)}"` : `href="#destinations" data-dest-link="${d.id}"`}>${t("Explore Destination")} ${icon("i-arrow", "icon--sm")}</a>
        </div>`;
      card.style.animation = "none";
      void card.offsetWidth;
      card.style.animation = "";
    }
  }

  function focus(id) {
    const d = destinations.find((x) => x.id === id);
    if (!d) return;
    const [x, y] = px(d.coords);
    const w = 300;
    const h = w * (470 / 560);
    zoomed = true;
    // Frame the point up-and-right of centre so the south-west corner stays free for the preview card
    zoomTo({ x: Math.max(-40, Math.min(x - w * 0.58, 560 - w + 40)), y: Math.max(-20, Math.min(y - h * 0.4, 470 - h + 20)), w, h });
    show(id);
  }

  function resetView() {
    zoomed = false;
    zoomTo({ ...FULL });
  }

  svg.addEventListener("pointerover", (e) => {
    const g = e.target.closest(".map-point");
    if (g) show(g.dataset.point);
  });
  svg.addEventListener("click", (e) => {
    const g = e.target.closest(".map-point");
    if (g) focus(g.dataset.point);
  });
  svg.addEventListener("keydown", (e) => {
    const g = e.target.closest(".map-point");
    if (g && (e.key === "Enter" || e.key === " ")) {
      e.preventDefault();
      focus(g.dataset.point);
    }
  });
  svg.addEventListener("focusin", (e) => {
    const g = e.target.closest(".map-point");
    if (g) show(g.dataset.point);
  });
  list.addEventListener("click", (e) => {
    const b = e.target.closest("[data-map-item]");
    if (b) focus(b.dataset.mapItem);
  });
  list.addEventListener("pointerover", (e) => {
    const b = e.target.closest("[data-map-item]");
    if (b && e.pointerType === "mouse" && !zoomed) show(b.dataset.mapItem);
  });
  reset.addEventListener("click", resetView);

  on("map:focus", (id) => setTimeout(() => focus(id), 700));

  show("cairo");
}
