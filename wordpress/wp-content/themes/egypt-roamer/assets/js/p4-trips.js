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
  };

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
    if (count) count.textContent = n === 1 ? bar.dataset.one : bar.dataset.many.replace("{n}", n);
    if (empty) empty.hidden = n > 0;
  };

  var address = function () {
    var p = new URLSearchParams(location.search);
    ["destination", "len", "private", "pickup"].forEach(function (k) { p.delete(k); });
    if (state.destination !== "0") p.set("destination", state.destination);
    if (state.len) p.set("len", state.len);
    if (state.private) p.set("private", "1");
    if (state.pickup) p.set("pickup", "1");
    var s = p.toString();
    history.replaceState(null, "", location.pathname + (s ? "?" + s : ""));
  };

  var keepInView = function () {
    // After a change the list starts right under the bar, not somewhere further down the old, longer list.
    var top = groups.getBoundingClientRect().top + window.scrollY - bar.offsetHeight - 90;
    if (window.scrollY > top) window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
  };

  bar.addEventListener("click", function (e) {
    var b = e.target.closest("[data-f]");
    if (!b || e.metaKey || e.ctrlKey || e.shiftKey) return;
    e.preventDefault();
    var k = b.dataset.f;
    if (k === "private" || k === "pickup") state[k] = !state[k];
    else state[k] = b.dataset.v;
    paint();
    address();
    keepInView();
  });
  if (empty) {
    empty.addEventListener("click", function (e) {
      if (!e.target.closest("a")) return;
      e.preventDefault();
      state = { destination: "0", len: "", private: false, pickup: false };
      paint();
      address();
      keepInView();
    });
  }
  paint();
})();
