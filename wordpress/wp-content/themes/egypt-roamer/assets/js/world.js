/* Proposal 4, the trip "world" (inc/world.php): the step in view lights its picture and its mark on the path, the
   tomb opener follows a torch (the pointer, else a slow drift), and the canvases draw the trip's mood: dust in
   lamplight (Nile), sea light and bubbles, desert stars and sand. Nothing runs with reduced motion. Ported from
   docs/prototypes/journey-v4/v4-worlds.js. */
(function () {
  var host = document.querySelector(".world");
  if (!host) return;
  var REDUCE = window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches;

  // The day, step by step
  var steps = host.querySelectorAll(".jstep[data-js]");
  if (steps.length && "IntersectionObserver" in window) {
    var marks = host.querySelectorAll(".path span");
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        var i = e.target.getAttribute("data-js");
        host.querySelectorAll("[data-j]").forEach(function (x) { x.classList.toggle("on", x.getAttribute("data-j") === i); });
        marks.forEach(function (m, k) { m.classList.toggle("on", String(k) === i); });
      });
    }, { rootMargin: "-45% 0px -45% 0px" });
    steps.forEach(function (s) { io.observe(s); });
  }

  // The torch on the tomb wall
  var open = host.querySelector(".wopen"), lit = host.querySelector(".lit");
  if (open && lit) {
    var idle = true, t0 = performance.now();
    var set = function (x, y) { lit.style.setProperty("--x", x + "px"); lit.style.setProperty("--y", y + "px"); };
    open.addEventListener("pointermove", function (e) { idle = false; var r = open.getBoundingClientRect(); set(e.clientX - r.left, e.clientY - r.top); });
    open.addEventListener("pointerleave", function () { idle = true; });
    var drift = function (t) {
      if (idle) { var r = open.getBoundingClientRect(), k = (t - t0) / 4200; set(r.width * (0.64 + 0.15 * Math.sin(k)), r.height * (0.4 + 0.13 * Math.sin(k * 1.7))); }
      if (!REDUCE) requestAnimationFrame(drift);
    };
    requestAnimationFrame(drift);
  }

  // The moods
  if (REDUCE) return;
  host.querySelectorAll("canvas[data-fx]").forEach(function (c) {
    var ctx = c.getContext("2d"), W = 0, H = 0, dpr = window.devicePixelRatio || 1, visible = true;
    var base = c.getAttribute("data-fx").replace("-j", "");
    var n = base === "sea" ? 70 : base === "desert" ? 240 : base === "stars" ? 220 : 90;
    var P = [];
    for (var i = 0; i < n; i++) P.push({ x: Math.random(), y: Math.random(), r: Math.random(), v: 0.2 + Math.random() * 0.8, p: Math.random() * 6.28 });
    if ("IntersectionObserver" in window) new IntersectionObserver(function (es) { visible = es[0].isIntersecting; }).observe(c);
    (function frame(t) {
      requestAnimationFrame(frame);
      if (!visible) return;
      if (c.clientWidth * dpr !== W || c.clientHeight * dpr !== H) { W = c.width = c.clientWidth * dpr; H = c.height = c.clientHeight * dpr; }
      ctx.clearRect(0, 0, W, H);
      if (base === "sea") {
        for (var i = 0; i < 6; i++) {
          var g = ctx.createLinearGradient(0, 0, 0, H * 0.75), x = (Math.sin(t / 2600 + i * 1.3) * 0.5 + 0.5) * W;
          g.addColorStop(0, "rgba(170,240,255,.15)"); g.addColorStop(1, "rgba(170,240,255,0)"); ctx.fillStyle = g;
          ctx.beginPath(); ctx.moveTo(x - 40 * dpr, 0); ctx.lineTo(x + 40 * dpr, 0); ctx.lineTo(x + 160 * dpr * Math.sin(t / 3000 + i), H * 0.75); ctx.lineTo(x + 60 * dpr * Math.sin(t / 3000 + i), H * 0.75); ctx.fill();
        }
        P.forEach(function (b) { b.y -= b.v * 0.0016; if (b.y < -0.05) { b.y = 1.05; b.x = Math.random(); } ctx.strokeStyle = "rgba(220,250,255,.5)"; ctx.lineWidth = dpr; ctx.beginPath(); ctx.arc((b.x + Math.sin(t / 900 + b.p) * 0.004) * W, b.y * H, (1 + b.r * 3.5) * dpr, 0, 6.28); ctx.stroke(); });
      } else if (base === "desert" || base === "stars") {
        var night = base === "stars" ? 1 : Math.sin((t / 14000) * Math.PI - Math.PI / 2) * 0.5 + 0.5;
        P.forEach(function (s, i) {
          if (base === "stars" || i < 160) { var a = night * (0.35 + 0.65 * Math.abs(Math.sin(t / 700 + s.p))); ctx.fillStyle = "rgba(255,250,235," + a * 0.9 + ")"; var z = (s.r * 1.6 + 0.5) * dpr; ctx.fillRect(s.x * W, s.y * H * (base === "stars" ? 1 : 0.55), z, z); }
          else { s.x += s.v * 0.0012; if (s.x > 1.05) { s.x = -0.05; s.y = Math.random(); } ctx.fillStyle = "rgba(240,215,170,.35)"; ctx.fillRect(s.x * W, (0.6 + s.y * 0.4) * H, (1 + s.r * 2) * dpr, dpr); }
        });
      } else {
        P.forEach(function (l) { var x = l.x * W, y = (0.62 + l.y * 0.38) * H, w = (10 + l.r * 50) * dpr * (0.7 + 0.3 * Math.sin(t / 400 + l.p)); ctx.fillStyle = "rgba(" + (l.r > 0.5 ? "255,190,110" : "255,235,190") + "," + (0.12 + 0.25 * Math.abs(Math.sin(t / 600 + l.p))) + ")"; ctx.fillRect(x - w / 2, y, w, 1.6 * dpr); });
      }
    })(performance.now());
  });
})();
