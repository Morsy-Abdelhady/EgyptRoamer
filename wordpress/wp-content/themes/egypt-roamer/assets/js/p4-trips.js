/* Trips & tours (proposal 4): the filters work in place. Every trip is on the page (archive.php); a filter only
   hides the trips and places that don't match, updates the count and the address (so the view can be shared or
   reloaded), and never reloads the page (owner, 20 Oct, #5). Without JavaScript the buttons stay plain links. */
(function () {
  var bar = document.querySelector("[data-trip-filters]");
  var groups = document.querySelector("[data-trip-groups]");
  if (!bar || !groups) return;
  var empty = document.querySelector("[data-trip-empty]");
  var count = bar.querySelector("[data-count]");
  var q = new URLSearchParams(location.search);
  var state = {
    destination: bar.dataset.destNow || "0",
    len: q.get("len") || "",
    private: !!q.get("private"),
    pickup: !!q.get("pickup"),
    sort: q.get("sort") === "rated" ? "rated" : "",
  };
  // "Top rated": every shown trip in one list, by the partner's rating, then its number of reviews (a copy of each
  // card; the place groups stay as they are for "By place").
  var flat = document.createElement("ul");
  flat.className = "grid-cards tflat";
  flat.hidden = true;
  groups.parentNode.insertBefore(flat, groups);

  // Recently viewed: the trips this browser opened lately (assets/js/world.js), one tap away (20 Oct).
  try {
    var seenTrips = JSON.parse(localStorage.getItem("er:recent") || "[]").filter(function (x) { return x && x.url && x.title; });
    if (seenTrips.length) {
      var esc = function (t) { var d = document.createElement("div"); d.textContent = t == null ? "" : String(t); return d.innerHTML; };
      var pic = function (im) { return /^https?:/.test(im || "") ? im : im ? "https://images.unsplash.com/photo-" + im + "?auto=format&fit=crop&w=160&h=160&q=70" : ""; };
      var box = document.createElement("section");
      box.className = "trecent";
      box.setAttribute("aria-label", bar.dataset.lRecent);
      box.innerHTML = '<div class="trecent__h"><h2>' + esc(bar.dataset.lRecent) + '</h2><button type="button" class="trecent__x">' + esc(bar.dataset.lClear) + "</button></div>" +
        '<div class="trecent__row">' + seenTrips.slice(0, 6).map(function (x) {
          return '<a class="mini" href="' + esc(x.url) + '">' + (pic(x.image) ? '<img src="' + esc(pic(x.image)) + '" alt="" loading="lazy" width="76" height="76">' : '<span class="mini__ph"></span>') + '<span class="mini__b"><b>' + esc(x.title) + "</b><span>" + esc(x.meta) + "</span></span></a>";
        }).join("") + "</div>";
      groups.parentNode.insertBefore(box, flat);
      box.querySelector(".trecent__x").addEventListener("click", function () { try { localStorage.removeItem("er:recent"); } catch (e) {} box.remove(); });
    }
  } catch (e) {}

  var paint = function () {
    bar.querySelectorAll(".chip[data-f]").forEach(function (c) {
      c.setAttribute("aria-pressed", String(state[c.dataset.f] === c.dataset.v));
    });
    bar.querySelectorAll(".toggle[data-f]").forEach(function (t) {
      t.setAttribute("aria-checked", String(state[t.dataset.f]));
    });
    var seen = {};
    groups.querySelectorAll(".tgroup").forEach(function (g) {
      var placeOk = state.destination === "0" || g.dataset.dest === state.destination;
      var any = false;
      g.querySelectorAll(".tcard[data-trip]").forEach(function (card) {
        var ok =
          placeOk &&
          (!state.len || (" " + card.dataset.len + " ").indexOf(" " + state.len + " ") > -1) &&
          (!state.private || card.dataset.private === "1") &&
          (!state.pickup || card.dataset.pickup === "1");
        card.hidden = !ok;
        if (ok) { any = true; seen[card.dataset.trip] = 1; }
      });
      g.hidden = !any;
    });
    var n = Object.keys(seen).length;
    if (state.sort === "rated") {
      var pick = {};
      groups.querySelectorAll(".tcard[data-trip]:not([hidden])").forEach(function (c) { pick[c.dataset.trip] = pick[c.dataset.trip] || c; });
      var list = Object.keys(pick).map(function (k) { return pick[k]; });
      list.sort(function (a, b) { return (+b.dataset.rating || 0) - (+a.dataset.rating || 0) || (+b.dataset.reviews || 0) - (+a.dataset.reviews || 0); });
      flat.innerHTML = "";
      list.forEach(function (c) { var d = c.cloneNode(true); d.classList.add("is-in"); flat.appendChild(d); });
    }
    flat.hidden = state.sort !== "rated" || !n;
    groups.hidden = state.sort === "rated";
    if (count) count.textContent = n === 1 ? bar.dataset.one : bar.dataset.many.replace("{n}", n);
    if (empty) empty.hidden = n > 0;
  };

  var address = function () {
    var p = new URLSearchParams(location.search);
    ["destination", "len", "private", "pickup", "sort"].forEach(function (k) { p.delete(k); });
    if (state.sort) p.set("sort", state.sort);
    if (state.destination !== "0") p.set("destination", state.destination);
    if (state.len) p.set("len", state.len);
    if (state.private) p.set("private", "1");
    if (state.pickup) p.set("pickup", "1");
    var s = p.toString();
    history.replaceState(null, "", location.pathname + (s ? "?" + s : ""));
  };

  var keepInView = function () {
    // After a change the list starts right under the bar, not somewhere further down the old, longer list.
    var top = (state.sort === "rated" ? flat : groups).getBoundingClientRect().top + window.scrollY - bar.offsetHeight - 90;
    if (window.scrollY > top) window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
  };

  // The chips are links (they work without JS) that act as toggle buttons here: Space presses them too.
  bar.addEventListener("keydown", function (e) {
    if (e.key === " " && e.target.closest(".chip[data-f]")) { e.preventDefault(); e.target.closest(".chip[data-f]").click(); }
  });
  bar.addEventListener("click", function (e) {
    var b = e.target.closest("[data-f]");
    if (!b || e.metaKey || e.ctrlKey || e.shiftKey) return;
    e.preventDefault();
    var k = b.dataset.f;
    if (k === "private" || k === "pickup") state[k] = !state[k];
    else if (k === "sort") state.sort = b.dataset.v;
    else state[k] = b.dataset.v;
    paint();
    address();
    keepInView();
  });
  if (empty) {
    empty.addEventListener("click", function (e) {
      if (!e.target.closest("a")) return;
      e.preventDefault();
      state = { destination: "0", len: "", private: false, pickup: false, sort: state.sort };
      paint();
      address();
      keepInView();
    });
  }
  paint();
})();
