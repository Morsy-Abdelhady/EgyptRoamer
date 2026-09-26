/* Egypt Roamer — dataLayer events. No dependencies, no personal data.
   Events: affiliate_click, booking_click, search, filter_use,
           newsletter_signup, contact_submit, guide_download */
(function () {
  "use strict";
  var dl = (window.dataLayer = window.dataLayer || []);
  var cfg = window.erTrack || {};
  var push = function (event, params) {
    var o = { event: event, page_type: cfg.pageType || "" };
    for (var k in params) if (params[k] !== undefined && params[k] !== "") o[k] = params[k];
    dl.push(o);
  };

  document.addEventListener(
    "click",
    function (e) {
      var a = e.target.closest && e.target.closest("[data-er-offer]");
      if (a) {
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
