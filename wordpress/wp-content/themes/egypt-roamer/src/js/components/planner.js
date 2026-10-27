/* Trip builder — a visual entry point: four choices produce a live,
   plausible route with paced nights and a budget band. */
import { t, tp, listJoin, isRTL } from "../i18n.js";
import { $, $$, icon, escapeHtml, money, toast, on, emit, scrollToTarget, affAttrs } from "../utils.js";
import { moods, destinations, routeOrder, nightWeights, legs, styleRates, img } from "../data.js";
// A number range ("4–10 hours") keeps its order in Arabic text: a left-to-right isolate (as inc/i18n.php does).
const RANGE = /(\d+\s?–\s?\d+)/g;

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

/* New journey preview (?ux=2, inc/ux2-trips.php): interests rank the trips, "Private or shared?" replaces the travel
   style (no prices behind it), dropped places are named, and the result is the trips on the route. */
const UX2P = (() => {
  try {
    const el = document.getElementById("ux2-planner");
    return el ? JSON.parse(el.textContent) : null;
  } catch (e) {
    return null;
  }
})();

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
  const tours = $("[data-itin-tours]");
  const toursList = $("[data-itin-tours-list]");

  // Partner tours for the stops on the route, in route order (WordPress: live offers per destination).
  function renderTours(plan) {
    if (!tours || !toursList) return;
    const items = plan.flatMap((p) => {
      const d = destinations.find((x) => x.id === p.id);
      return ((d && d.tours) || []).map((o) => ({ o, place: d.name }));
    });
    const seen = new Set();
    const unique = items.filter(({ o }) => !seen.has(o.id) && seen.add(o.id));
    tours.hidden = !unique.length;
    toursList.innerHTML = unique
      .map(
        ({ o, place }) =>
          `<li><a class="itin__tour" href="${escapeHtml(o.href)}" ${affAttrs(o)}><span class="itin__tour-place">${escapeHtml(place)}</span><span class="itin__tour-name">${escapeHtml(o.title)}</span><span class="itin__tour-cta">${escapeHtml(o.cta)}${icon("i-arrow-ur")}</span></a></li>`
      )
      .join("");
  }

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

  // Preview: step 04 asks what the trips can answer, and the trips replace the list of partner offer names.
  let ux2Priv = false;
  let ux2Out = null;
  if (UX2P) {
    const step = seg.closest("fieldset");
    seg.hidden = true;
    seg.style.display = "none"; // the seg's own display rule outranks [hidden]
    step.querySelector("legend").innerHTML = `<span>04</span> ${escapeHtml(UX2P.t.step)}`;
    step.insertAdjacentHTML(
      "beforeend",
      `<div class="ux2-priv" role="group" aria-label="${escapeHtml(UX2P.t.step)}"><button type="button" data-ux2-priv="0" aria-pressed="true">${escapeHtml(UX2P.t.either)}</button><button type="button" data-ux2-priv="1" aria-pressed="false">${escapeHtml(UX2P.t.private)}</button></div><p class="ux2-priv__hint">${escapeHtml(UX2P.t.hint)}</p>`
    );
    step.addEventListener("click", (e) => {
      const b = e.target.closest("[data-ux2-priv]");
      if (!b) return;
      ux2Priv = b.dataset.ux2Priv === "1";
      $$("[data-ux2-priv]", step).forEach((x) => x.setAttribute("aria-pressed", String(x === b)));
      update();
    });
    if (tours) tours.remove();
    route.insertAdjacentHTML("afterend", '<div class="ux2-pb"></div>');
    ux2Out = $(".ux2-pb", form);
    ux2Out.addEventListener("click", (e) => {
      const add = e.target.closest("[data-ux2-add]");
      if (add) {
        days.value = Math.min(Number(days.max), Number(days.value) + Number(add.dataset.ux2Add));
        update();
        return;
      }
      const pl = e.target.closest("[data-ux2-dest]");
      if (pl) {
        const box = $(`input[name=dest][value="${pl.dataset.ux2Dest}"]`, dests);
        if (box) box.checked = true;
        update();
      }
    });
  }

  function renderUx2(plan, chosen, n) {
    if (!ux2Out) return;
    const T = UX2P.t;
    const onRoute = plan.map((p) => p.id);
    const nameOf = (id) => (destinations.find((d) => d.id === id) || {}).name || id;
    const dropped = chosen.filter((id) => !onRoute.includes(id));
    const ints = $$("input[name=interest]:checked", interests).map((i) => i.value);
    const labelOf = (id) => (moods.find((m) => m.id === id) || {}).label || id;
    let h = "";
    if (dropped.length) {
      h += `<p class="ux2-pb__note">${escapeHtml(T.dropped.replace("{places}", listJoin(dropped.map(nameOf))))} <button type="button" data-ux2-add="${dropped.length}">${escapeHtml(T.add.replace("{n}", dropped.length))}</button></p>`;
    }
    if (onRoute.length) {
      const match = (tr) => tr.interests.filter((i) => ints.includes(i));
      let trips = UX2P.trips.filter((tr) => tr.dests.some((d) => onRoute.includes(d)));
      const hidden = ux2Priv ? trips.filter((tr) => !tr.private).length : 0;
      if (ux2Priv) trips = trips.filter((tr) => tr.private);
      trips.sort((a, b) => match(b).length - match(a).length);
      h += `<div class="ux2-pb__res"><p class="t-label">${escapeHtml(T.title)}</p>`;
      if (trips.length) {
        h += `<p class="ux2-pb__sub">${escapeHtml(T.sub)}${hidden ? " " + escapeHtml(T.hidden.replace("{n}", hidden)) : ""}</p><ul class="ux2-pb__list">`;
        h += trips
          .map((tr) => {
            const m = match(tr);
            const meta = [tr.places, tr.len, tr.n ? (tr.n === 1 ? T.opt1 : T.optn.replace("{n}", tr.n)) : "", tr.private ? T.privopt : ""].filter(Boolean).join(" · ").replace(RANGE, document.dir === "rtl" ? "⁦$1⁩" : "$1");
            const live = [tr.rating, tr.price].filter(Boolean).join(" · ");
            return `<li><a class="ux2-pb__card" href="${escapeHtml(tr.url)}"><img src="${img(tr.image, 160)}" alt="" loading="lazy" /><span><b>${escapeHtml(tr.title)}</b><small>${escapeHtml(meta)}</small>${live ? `<span class="ux2-pb__live">${escapeHtml(live)}</span>` : ""}${tr.cancel && T.cancel ? `<i class="ux2-pb__fc">✓ ${escapeHtml(T.cancel)}</i>` : ""}${m.length ? `<em>${escapeHtml(m.map(labelOf).join(" · "))}</em>` : ""}</span></a></li>`;
          })
          .join("");
        h += "</ul>";
      } else {
        h += `<p class="ux2-pb__sub">${escapeHtml(T.nopriv)}</p>`;
      }
      // Interests with nothing on the route: the places whose trips have them.
      const miss = ints.filter((i) => !trips.some((tr) => tr.interests.includes(i)));
      miss.forEach((i) => {
        const places = [...new Set(UX2P.trips.filter((tr) => tr.interests.includes(i) && (!ux2Priv || tr.private)).flatMap((tr) => tr.dests))].filter((d) => !onRoute.includes(d) && destinations.some((x) => x.id === d));
        if (places.length) h += `<p class="ux2-pb__more">${escapeHtml(T.more.replace("{interest}", labelOf(i)))} ${places.map((d) => `<button type="button" data-ux2-dest="${escapeHtml(d)}">+ ${escapeHtml(nameOf(d))}</button>`).join("")}</p>`;
      });
      // Save this plan: kept with the hearts, with a link that reopens it here.
      const q = new URLSearchParams({ plan: chosen.join(","), n: String(n), i: ints.join(",") });
      const item = { id: "plan:" + chosen.join("-") + "-" + n, title: title.textContent, meta: T.planmeta.replace("{n}", String(trips.length)), image: (UX2P.trips.find((tr) => tr.dests.includes(onRoute[0])) || {}).image || "", url: location.pathname + "?" + q.toString() + "#planner" };
      h += `<p class="ux2-pb__save"><button class="save-btn ux2-pb__savebtn" type="button" data-save='${escapeHtml(JSON.stringify(item))}' aria-pressed="false">${icon("i-heart")}<span class="ux2-pb__off">${escapeHtml(T.save)}</span><span class="ux2-pb__on">${escapeHtml(T.saved)}</span></button></p>`;
      h += "</div>";
    }
    ux2Out.innerHTML = h;
    emit("favorites:refresh");
  }

  // A saved plan reopened from the hearts: its places, nights and interests.
  if (UX2P) {
    const qs = new URLSearchParams(location.search);
    if (qs.get("plan")) {
      const ps = qs.get("plan").split(","), is = (qs.get("i") || "").split(",");
      $$("input[name=dest]", dests).forEach((c) => (c.checked = ps.includes(c.value)));
      $$("input[name=interest]", interests).forEach((c) => (c.checked = is.includes(c.value)));
      const nn = Number(qs.get("n"));
      if (nn >= Number(days.min) && nn <= Number(days.max)) days.value = nn;
    }
  }

  // "Start from this route" (homepage, Ready-made routes): fills the builder in place with that route.
  document.addEventListener("click", (e) => {
    const a = e.target.closest("[data-route-plan]");
    if (!a) return;
    const u = new URL(a.href, location.href);
    const ps = (u.searchParams.get("plan") || "").split(",");
    if (!ps[0]) return;
    e.preventDefault();
    $$("input[name=dest]", dests).forEach((c) => (c.checked = ps.includes(c.value)));
    const nn = Number(u.searchParams.get("n"));
    if (nn >= Number(days.min) && nn <= Number(days.max)) days.value = nn;
    update();
    scrollToTarget($("#planner"));
  });

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
      renderTours([]);
      renderUx2(plan, chosen, n);
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

    // Budget bands are owner-configured; without them the estimate is hidden (no invented prices).
    const band = styleRates && styleRates[style];
    const budgetRow = budget.closest("div");
    if (budgetRow) budgetRow.hidden = !band;
    if (band) {
      const round = (v) => Math.round(v / 50) * 50;
      budget.textContent = `${money(round(band[0] * n))} – ${money(round(band[1] * n))}`;
    }
    const coastOnly = plan.every((p) => ["hurghada", "sharm", "alexandria"].includes(p.id));
    season.textContent = t(coastOnly ? "Mar – Nov" : "Oct – Apr");
    renderTours(plan);
    renderUx2(plan, chosen, n);
  }

  form.addEventListener("input", update);
  form.addEventListener("change", update);
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (form.dataset.href) {
      location.href = form.dataset.href; // WordPress: the owner's planning page
      return;
    }
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
