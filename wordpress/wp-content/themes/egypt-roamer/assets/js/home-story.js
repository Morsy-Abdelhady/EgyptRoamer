/* Proposal 4, the homepage story's banner (template-parts/home-story.php): the old hero's animation (owner, 18 Oct).
   The entrance is hero.js (it adds .is-in to #hero after the loader). Here, as in journey.js's "hero hands over to
   PYRAMIDS": on scroll the photo zooms, the second photo fades in, the sun sinks and fades, the dune rises and the text
   drifts up and fades; on desktop the photos follow the pointer a little. Nothing moves with reduced motion. */
(function () {
  var hero = document.getElementById("hero");
  if (!hero || !hero.classList.contains("hstory__hero")) return;
  if (window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  var media = hero.querySelector("[data-hs-media]");
  var imgs = hero.querySelectorAll(".hstory__img");
  var imgB = hero.querySelector(".hstory__img--b");
  var sun = hero.querySelector("[data-hs-sun]");
  var dune = hero.querySelector("[data-hs-dune]");
  var content = hero.querySelector("[data-hs-content]");
  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };

  var queued = false;
  var paint = function () {
    queued = false;
    var h = hero.offsetHeight || window.innerHeight;
    var p = clamp(window.scrollY / h);
    if (media) media.style.transform = "scale(" + (1 + 0.22 * p).toFixed(4) + ")";
    if (imgB) imgB.style.opacity = clamp((p - 0.12) / 0.5).toFixed(3);
    if (sun) { sun.style.transform = "translateY(" + (35 * p).toFixed(2) + "%)"; sun.style.opacity = (1 - 0.55 * p).toFixed(3); }
    if (dune) dune.style.transform = "translateY(" + (-30 * p).toFixed(2) + "%)";
    if (content) {
      var c = clamp(p / 0.45);
      content.style.opacity = (1 - c).toFixed(3);
      content.style.transform = c ? "translateY(" + (-70 * c).toFixed(1) + "px)" : "";
    }
  };
  var onScroll = function () { if (!queued) { queued = true; requestAnimationFrame(paint); } };
  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll);
  paint();

  // Pointer parallax on the opening frame (hero.js initPointerParallax: -22 px / -14 px), desktop only.
  if (!(window.matchMedia && matchMedia("(pointer: fine)").matches)) return;
  var tx = 0, ty = 0, x = 0, y = 0, running = false;
  var step = function () {
    x += (tx - x) * 0.06; y += (ty - y) * 0.06;
    imgs.forEach(function (im) { im.style.transform = "translate(" + x.toFixed(2) + "px," + y.toFixed(2) + "px) scale(1.04)"; });
    if (Math.abs(tx - x) > 0.1 || Math.abs(ty - y) > 0.1) requestAnimationFrame(step); else running = false;
  };
  window.addEventListener("pointermove", function (e) {
    if (window.scrollY > window.innerHeight) return;
    tx = (e.clientX / window.innerWidth - 0.5) * -22;
    ty = (e.clientY / window.innerHeight - 0.5) * -14;
    if (!running) { running = true; requestAnimationFrame(step); }
  }, { passive: true });
})();
