/* Proposal 4's videos (inc/world.php er_world_video). Each video loads only when it is on screen and pauses when it
   leaves. A trip step's video plays only while its step is the one shown. Every section has its own pause / play
   button (WCAG 2.2.2) and the credit behind an "i"; a section the reader paused stays paused. With Data Saver on or
   reduced motion, videos stay as their still photo until the reader presses play. The homepage moods get their video
   here too (window.erMoodVideos, printed by inc/world.php). */
(function () {
  var mq = window.matchMedia ? matchMedia("(prefers-reduced-motion: reduce)") : null;
  var still = (mq && mq.matches) || !!(navigator.connection && navigator.connection.saveData);
  var L = window.erVideoLabels || { pause: "Pause video", play: "Play video", credit: "Video credit" };
  var scopeOf = function (el) { return el.closest(".tbanner, .papy__vid, section") || document.body; };
  // "1": the reader paused it; "0": the reader pressed play; unset: plays unless Data Saver / reduced motion.
  var paused = function (scope) { var a = scope.getAttribute("data-vpaused"); return a === "1" || (a === null && still); };
  var seen = new WeakMap();

  var wanted = function (v) {
    if (!seen.get(v)) return false;
    if (v.hasAttribute("data-vstep") && !v.classList.contains("on")) return false;
    if (v.hasAttribute("data-vmood") && !v.classList.contains("is-on")) return false;
    return !paused(scopeOf(v));
  };
  var sync = function (v) {
    if (wanted(v)) {
      if (!v.getAttribute("src")) { v.src = v.getAttribute("data-src"); }
      var p = v.play();
      if (p && p.catch) p.catch(function () {});
    } else if (!v.paused) {
      v.pause();
    }
  };
  var paintCtl = function (scope) {
    var off = paused(scope);
    scope.querySelectorAll("[data-vplay]").forEach(function (b) {
      b.setAttribute("aria-pressed", String(off));
      b.setAttribute("aria-label", off ? b.getAttribute("data-l-play") : b.getAttribute("data-l-pause"));
      b.classList.toggle("is-off", off);
    });
  };

  var io = "IntersectionObserver" in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { seen.set(e.target, e.isIntersecting); sync(e.target); });
  }, { threshold: 0.01 }) : null;

  var watch = function (v) {
    v.addEventListener("playing", function () { v.classList.add("is-playing"); });
    if (io) io.observe(v); else { seen.set(v, true); sync(v); }
    // a step's or a mood's video follows its "on" class (world.js, the moods below)
    if (v.hasAttribute("data-vstep") || v.hasAttribute("data-vmood")) new MutationObserver(function () { sync(v); }).observe(v, { attributes: true, attributeFilter: ["class"] });
  };

  // The homepage moods: a video layer over the mood's photo, for the moods that have one.
  var moodBg = document.querySelector(".moods [data-mood-bg]");
  if (moodBg && window.erMoodVideos) {
    var section = moodBg.closest("section");
    var credit = document.createElement("div");
    credit.className = "vctl vctl--mood";
    credit.setAttribute("data-vctl", "");
    credit.hidden = true;
    credit.innerHTML = '<button type="button" class="vctl__btn" data-vplay aria-pressed="false" data-l-pause="" data-l-play=""><svg viewBox="0 0 24 24" aria-hidden="true" class="vctl__pause"><rect x="6.5" y="5" width="3.6" height="14" rx="1"/><rect x="13.9" y="5" width="3.6" height="14" rx="1"/></svg><svg viewBox="0 0 24 24" aria-hidden="true" class="vctl__play"><path d="M8 5.5v13l11-6.5z"/></svg></button><button type="button" class="vctl__i" aria-describedby="vctl-tip-mood">i</button><span class="vctl__tip" role="tooltip" id="vctl-tip-mood"></span>';
    var pb = credit.querySelector("[data-vplay]");
    pb.setAttribute("data-l-pause", L.pause); pb.setAttribute("data-l-play", L.play); pb.setAttribute("aria-label", L.pause);
    credit.querySelector(".vctl__i").setAttribute("aria-label", L.credit);
    section.appendChild(credit);
    var vids = {};
    Object.keys(window.erMoodVideos).forEach(function (k) {
      var v = document.createElement("video");
      v.className = "wvid moods__vid";
      v.muted = true; v.loop = true; v.playsInline = true; v.preload = "none";
      v.setAttribute("muted", ""); v.setAttribute("playsinline", ""); v.setAttribute("aria-hidden", "true");
      v.setAttribute("data-src", window.erMoodVideos[k][0]);
      v.setAttribute("data-vmood", k);
      moodBg.appendChild(v);
      vids[k] = v;
    });
    var current = null;
    var follow = function () {
      var on = moodBg.querySelector(".moods__layer.is-on");
      var k = on ? on.getAttribute("data-layer") : "";
      if (k === current) return; // only a change of mood touches the videos (their own class changes come back here)
      current = k;
      Object.keys(vids).forEach(function (m) {
        if (m === k) vids[m].classList.add("is-on");
        else if (vids[m].classList.contains("is-on") || vids[m].classList.contains("is-playing")) vids[m].classList.remove("is-on", "is-playing");
      });
      credit.hidden = !vids[k];
      if (vids[k]) credit.querySelector(".vctl__tip").textContent = window.erMoodVideos[k][1];
    };
    new MutationObserver(function (rs) {
      if (rs.some(function (r) { return r.target.tagName !== "VIDEO"; })) follow();
    }).observe(moodBg, { attributes: true, subtree: true, attributeFilter: ["class"] });
    follow();
  }

  document.querySelectorAll("video[data-src]").forEach(watch);
  var scopes = new Set();
  document.querySelectorAll("[data-vctl]").forEach(function (c) { scopes.add(scopeOf(c)); });
  scopes.forEach(paintCtl);

  document.addEventListener("click", function (e) {
    var b = e.target.closest && e.target.closest("[data-vplay]");
    if (!b) return;
    var scope = scopeOf(b);
    scope.setAttribute("data-vpaused", paused(scope) ? "0" : "1");
    paintCtl(scope);
    scope.querySelectorAll("video[data-src]").forEach(sync);
  });
})();
