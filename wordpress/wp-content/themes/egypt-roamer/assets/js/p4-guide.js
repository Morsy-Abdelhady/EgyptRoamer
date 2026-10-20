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
    if (end) new IntersectionObserver(function (es) { s.end = es[0].isIntersecting; paint(); }).observe(end);
  }
})();
