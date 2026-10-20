/* Proposal 4, the guide article (template-parts/guide-p4.php), as the prototype's openGuide: the section bar marks
   the section being read and scrolls with arrows when it overflows; on phones the trip bar rises once the opener is
   passed and steps aside at the end of the page. */
(function () {
  var gd = document.querySelector(".gd");
  if (!gd) return;
  var bar = gd.querySelector("[data-secbar]");
  if (bar) {
    var ol = bar.querySelector("ol");
    var links = [].slice.call(bar.querySelectorAll("a"));
    var fit = function () {
      var over = ol.scrollWidth > ol.clientWidth + 4;
      bar.classList.toggle("is-over", over);
      bar.classList.toggle("at-start", ol.scrollLeft < 4);
      bar.classList.toggle("at-end", ol.scrollLeft + ol.clientWidth >= ol.scrollWidth - 4);
    };
    ol.addEventListener("scroll", fit, { passive: true });
    window.addEventListener("resize", fit);
    fit();
    bar.querySelectorAll("[data-sbarr]").forEach(function (b) {
      b.addEventListener("click", function () { ol.scrollBy({ left: +b.getAttribute("data-sbarr") * ol.clientWidth * 0.7, behavior: "smooth" }); });
    });
    if ("IntersectionObserver" in window) {
      var mark = function (id) {
        links.forEach(function (a) {
          var on = a.getAttribute("href") === "#" + id;
          a.classList.toggle("on", on);
          if (on && a.scrollIntoView) { var r = a.getBoundingClientRect(), o = ol.getBoundingClientRect(); if (r.left < o.left || r.right > o.right) ol.scrollBy({ left: r.left - o.left - 20, behavior: "smooth" }); }
        });
      };
      var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) mark(e.target.id); }); }, { rootMargin: "-20% 0px -70% 0px" });
      links.forEach(function (a) { var t = document.getElementById(a.getAttribute("href").slice(1)); if (t) io.observe(t); });
    }
  }
  var m = gd.querySelector("[data-mbar]"), hero = gd.querySelector(".ahero"), end = gd.querySelector(".aend");
  if (m && hero && "IntersectionObserver" in window) {
    var s = { hero: true, end: false };
    var paint = function () { var on = !s.hero && !s.end; m.classList.toggle("is-on", on); document.documentElement.classList.toggle("has-p4bar", on && window.matchMedia("(max-width: 900px)").matches); };
    new IntersectionObserver(function (es) { s.hero = es[0].isIntersecting; paint(); }).observe(hero);
    // From "Ready to go?" to the end of the page (the footer too) the bar stays aside.
    if (end) {
      var tick = false, check = function () { tick = false; var e = end.getBoundingClientRect().top < window.innerHeight; if (e !== s.end) { s.end = e; paint(); } };
      window.addEventListener("scroll", function () { if (!tick) { tick = true; requestAnimationFrame(check); } }, { passive: true });
      check();
    }
  }

  // The photos open a viewer, one after another through the whole guide, as the prototype's strip (owner, 20 Oct,
  // #12): arrows, the keyboard (← → Esc) and a swipe on phones.
  var figs = [].slice.call(gd.querySelectorAll(".gplace figure"));
  if (figs.length) {
    var lb = document.createElement("div");
    lb.className = "glb";
    lb.hidden = true;
    lb.setAttribute("role", "dialog");
    lb.setAttribute("aria-modal", "true");
    lb.innerHTML = '<button type="button" class="glb__x" aria-label="' + gd.dataset.lbClose + '">✕</button>' +
      '<button type="button" class="glb__p" aria-label="' + gd.dataset.lbPrev + '">‹</button>' +
      '<figure><img alt=""><figcaption></figcaption></figure>' +
      '<button type="button" class="glb__n" aria-label="' + gd.dataset.lbNext + '">›</button>';
    document.body.appendChild(lb);
    var at = 0, back = null, img = lb.querySelector("img"), cap = lb.querySelector("figcaption");
    var show = function (i) {
      at = (i + figs.length) % figs.length;
      var f = figs[at], src = f.querySelector("img");
      img.src = f.dataset.full || src.currentSrc || src.src;
      img.alt = src.alt;
      var t = f.querySelector("figcaption span"), c = f.querySelector("figcaption small");
      cap.innerHTML = "";
      if (t) { var b = document.createElement("b"); b.textContent = t.textContent; cap.appendChild(b); }
      var sm = document.createElement("small");
      sm.textContent = (c ? c.textContent + " · " : "") + (at + 1) + " / " + figs.length;
      cap.appendChild(sm);
      lb.classList.toggle("glb--one", figs.length < 2);
    };
    var open = function (i) { back = document.activeElement; show(i); lb.hidden = false; document.documentElement.classList.add("glb-open"); lb.querySelector(".glb__x").focus(); };
    var close = function () { lb.hidden = true; document.documentElement.classList.remove("glb-open"); if (back) back.focus(); };
    figs.forEach(function (f, i) {
      f.addEventListener("click", function () { open(i); });
      f.addEventListener("keydown", function (e) { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); open(i); } });
    });
    lb.addEventListener("click", function (e) {
      if (e.target.closest(".glb__x") || e.target === lb) close();
      else if (e.target.closest(".glb__p")) show(at - 1);
      else if (e.target.closest(".glb__n")) show(at + 1);
    });
    document.addEventListener("keydown", function (e) {
      if (lb.hidden) return;
      if (e.key === "Escape") close();
      else if (e.key === "ArrowLeft") show(at + (document.dir === "rtl" ? 1 : -1));
      else if (e.key === "ArrowRight") show(at + (document.dir === "rtl" ? -1 : 1));
    });
    var x0 = null;
    lb.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0; x0 = null;
      if (Math.abs(dx) > 40) show(at + (dx < 0 ? 1 : -1) * (document.dir === "rtl" ? -1 : 1));
    });
  }
})();
