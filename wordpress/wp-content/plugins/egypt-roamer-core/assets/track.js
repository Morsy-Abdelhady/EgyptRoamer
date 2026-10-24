/* Egypt Roamer — first-party page-view totals and dataLayer events. No dependencies, no personal data.
   dataLayer events (only used if a GTM container is configured): affiliate_click, booking_click, search,
   filter_use, newsletter_signup, contact_submit, guide_download */
(function () {
  "use strict";
  var dl = (window.dataLayer = window.dataLayer || []);
  var cfg = window.erTrack || {};
  var push = function (event, params) {
    var o = { event: event, page_type: cfg.pageType || "" };
    for (var k in params) if (params[k] !== undefined && params[k] !== "") o[k] = params[k];
    dl.push(o);
  };

  // First-party page-view totals (Core includes/visits.php). No cookie, no identifier, nothing kept in
  // the browser except the "this is an editor's browser" flag. Only the referrer's host is sent.
  var hit = (function () {
    var off = false;
    try {
      if (String(cfg.self) === "1") localStorage.setItem("er-nocount", "1"); // localized values arrive as strings
      off = localStorage.getItem("er-nocount") === "1";
    } catch (err) {
      /* storage blocked: count normally */
    }
    if (
      off ||
      !cfg.hit ||
      navigator.webdriver ||
      navigator.doNotTrack === "1" ||
      window.doNotTrack === "1" ||
      navigator.globalPrivacyControl === true ||
      !window.fetch
    )
      return function () {};
    var w = Math.min(window.screen ? screen.width : 0, window.innerWidth || 9999) || window.innerWidth || 0;
    var device = w && w < 768 ? "mobile" : w && w < 1100 ? "tablet" : "desktop";
    var ref = "";
    try {
      ref = document.referrer ? new URL(document.referrer).hostname : "";
    } catch (err) {
      ref = "";
    }
    var utm = "";
    try {
      utm = new URL(location.href).searchParams.get("utm_source") || "";
    } catch (err) {
      utm = "";
    }
    return function (event, extra) {
      var body = { e: event, p: location.pathname, l: cfg.lang || "", t: cfg.pageType || "", d: device, r: ref, u: utm };
      for (var k in extra) body[k] = extra[k];
      try {
        fetch(cfg.hit, { method: "POST", body: JSON.stringify(body), keepalive: true, credentials: "same-origin", headers: { "Content-Type": "text/plain" } }).catch(function () {});
      } catch (err) {
        /* never break the page */
      }
    };
  })();
  // A view counts only once a person scrolls, taps, clicks, types or moves the pointer. Bots that load a
  // page (and run its scripts) but never interact are left out; most people interact within seconds.
  var sendView = function () {
    var kinds = ["scroll", "wheel", "pointerdown", "pointermove", "touchstart", "keydown"];
    var done = false;
    var onUse = function () {
      if (done) return;
      done = true;
      kinds.forEach(function (k) {
        window.removeEventListener(k, onUse, true);
      });
      hit("view", {});
    };
    kinds.forEach(function (k) {
      window.addEventListener(k, onUse, { capture: true, passive: true });
    });
  };
  if (document.prerendering) document.addEventListener("prerenderingchange", sendView, { once: true });
  else if (document.visibilityState === "hidden") {
    // Opened in a background tab: count it when it is first shown.
    var onShow = function () {
      if (document.visibilityState === "visible") {
        document.removeEventListener("visibilitychange", onShow);
        sendView();
      }
    };
    document.addEventListener("visibilitychange", onShow);
  } else sendView();

  document.addEventListener(
    "click",
    function (e) {
      var a = e.target.closest && e.target.closest("[data-er-offer]");
      if (a) {
        hit("cta", { c: (a.getAttribute("data-er-placement") || "offer").toLowerCase().slice(0, 32) });
        var params = {
          offer: a.getAttribute("data-er-offer"),
          provider: a.getAttribute("data-er-provider"),
          placement: a.getAttribute("data-er-placement"),
          cta: a.getAttribute("data-er-cta"),
        };
        push("affiliate_click", params);
        if (a.getAttribute("data-er-intent") === "booking") push("booking_click", params);
        return;
      }
      var f = e.target.closest && e.target.closest("[data-er-filter]");
      if (f) {
        push("filter_use", { filter_name: f.getAttribute("data-er-filter"), filter_value: f.getAttribute("data-er-filter-value") || "" });
        return;
      }
      var d = e.target.closest && e.target.closest("[data-er-download]");
      if (d) push("guide_download", { file: d.getAttribute("data-er-download") });
    },
    true
  );

  // Filters rendered as <select data-er-filter> (archives)
  document.addEventListener("change", function (e) {
    var s = e.target;
    if (s && s.matches && s.matches("select[data-er-filter]")) push("filter_use", { filter_name: s.getAttribute("data-er-filter"), filter_value: s.value });
  });

  if (cfg.pageType === "search") push("search", { search_term: cfg.search, results: cfg.results });

  // Server-confirmed form outcomes arrive as ?er=… after the redirect; report once, then tidy the URL.
  try {
    var url = new URL(location.href);
    var state = url.searchParams.get("er");
    var map = { subscribed: "newsletter_signup", "subscribed-pending": "newsletter_signup", "contact-sent": "contact_submit" };
    if (map[state]) {
      push(map[state], {});
      url.searchParams.delete("er");
      history.replaceState(null, "", url.pathname + url.search + url.hash);
    }
  } catch (err) {
    /* URL API unavailable — skip */
  }
})();
