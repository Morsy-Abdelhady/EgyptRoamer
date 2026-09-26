/* Trip builder — a visual entry point: four choices produce a live,
   plausible route with paced nights and a budget band. */
import { t, tp, listJoin, isRTL } from "../i18n.js";
import { $, $$, icon, escapeHtml, fmt, toast, on, scrollToTarget } from "../utils.js";
import { moods, destinations, routeOrder, nightWeights, legs, styleRates } from "../data.js";

const DEFAULT_DESTS = ["cairo", "luxor", "aswan"];
const DEFAULT_INTERESTS = ["ancient", "culture"];

function planRoute(ids, nights) {
  const ordered = routeOrder.filter((id) => ids.includes(id));
  if (!ordered.length) return [];
  const stops = ordered.slice(0, Math.max(1, Math.min(ordered.length, nights)));
  const total = stops.reduce((s, id) => s + nightWeights[id], 0);
  let alloc = stops.map((id) => Math.max(1, Math.round((nightWeights[id] / total) * nights)));
  // fix rounding so the sum equals nights
  let diff = nights - alloc.reduce((a, b) => a + b, 0);
  let i = 0;
  while (diff !== 0 && i < 100) {
    const k = i % alloc.length;
    if (diff > 0) {
      alloc[k] += 1;
      diff -= 1;
    } else if (alloc[k] > 1) {
      alloc[k] -= 1;
      diff += 1;
    }
    i++;
  }
  return stops.map((id, k) => ({ id, nights: alloc[k] }));
}

const legText = (a, b) => legs[`${a}>${b}`] || legs[`${b}>${a}`] || t("Short domestic flight");

export function initPlanner() {
  const form = $("[data-builder]");
  if (!form) return;
  const days = $("[data-b-days]", form);
  const daysOut = $("[data-b-days-out]", form);
  const daysUnit = $("[data-b-days-unit]", form);
  const interests = $("[data-b-interests]", form);
  const dests = $("[data-b-dests]", form);
  const seg = $("[data-b-style]", form);
  const title = $("[data-itin-title]");
  const route = $("[data-itin-route]");
  const budget = $("[data-itin-budget]");
  const season = $("[data-itin-season]");

  interests.innerHTML = moods
    .map(
      (m) => `<label class="bchip"><input type="checkbox" name="interest" value="${m.id}" ${DEFAULT_INTERESTS.includes(m.id) ? "checked" : ""} /><span>${icon(m.icon)}${m.label}</span></label>`
    )
    .join("");
  dests.innerHTML = destinations
    .map(
      (d) => `<label class="bchip"><input type="checkbox" name="dest" value="${d.id}" ${DEFAULT_DESTS.includes(d.id) ? "checked" : ""} /><span>${d.name}</span></label>`
    )
    .join("");

  const segInk = $("i", seg);
  const placeSeg = () => {
    const radios = $$("input", seg);
    const i = radios.findIndex((r) => r.checked);
    segInk.style.transform = `translateX(${(isRTL ? -1 : 1) * i * 100}%)`;
  };

  function update() {
    const n = Number(days.value);
    daysOut.textContent = n;
    daysUnit.textContent = tp(n, "night", "nights");
    const nightsLabel = tp(n, "{n} night", "{n} nights");
    days.style.setProperty("--p", `${((n - days.min) / (days.max - days.min)) * 100}%`);
    const chosen = $$("input[name=dest]:checked", dests).map((i) => i.value);
    const style = $("input[name=style]:checked", seg).value;
    const plan = planRoute(chosen, n);
    placeSeg();

    if (!plan.length) {
      title.textContent = t("{nights} · choose a destination", { nights: nightsLabel });
      route.innerHTML = `<li class="itin__empty">${t("Pick at least one place and we'll pace the route for you.")}</li>`;
      budget.textContent = "—";
      season.textContent = "—";
      return;
    }
    const names = plan.map((p) => destinations.find((d) => d.id === p.id).name);
    title.textContent = t("{nights} · {places}", { nights: nightsLabel, places: listJoin(names) });

    route.innerHTML = plan
      .map((p, k) => {
        const d = destinations.find((x) => x.id === p.id);
        const leg = k < plan.length - 1 ? `<li class="itin__leg" style="--i:${k}">${escapeHtml(legText(p.id, plan[k + 1].id))}</li>` : "";
        return `<li class="itin__stop" style="--i:${k}"><span class="itin__dot"></span><span class="itin__name">${d.name}</span><span class="itin__nights">${tp(p.nights, "{n} night", "{n} nights")}</span></li>${leg}`;
      })
      .join("");

    const [lo, hi] = styleRates[style];
    const round = (v) => Math.round(v / 50) * 50;
    budget.textContent = `$${fmt(round(lo * n))} – $${fmt(round(hi * n))}`;
    const coastOnly = plan.every((p) => ["hurghada", "sharm", "alexandria"].includes(p.id));
    season.textContent = t(coastOnly ? "Mar – Nov" : "Oct – Apr");
  }

  form.addEventListener("input", update);
  form.addEventListener("change", update);
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const picked = $$("input[name=interest]:checked", interests).map((i) => i.value);
    // In production: route to /plan with these params, or hand to a planning partner.
    const extra = picked.length ? ` · ${tp(picked.length, "{n} interest", "{n} interests")}` : "";
    toast(t("Draft saved · {title}", { title: title.textContent + extra }), "i-route");
  });

  on("planner:mood", (moodId) => {
    const box = $(`input[name=interest][value="${moodId}"]`, interests);
    if (box) box.checked = true;
    const mood = moods.find((m) => m.id === moodId);
    const destBox = mood && $(`input[name=dest][value="${mood.recs.dest}"]`, dests);
    if (destBox) destBox.checked = true;
    update();
  });

  update();
}
