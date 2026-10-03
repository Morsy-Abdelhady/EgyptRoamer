/* ==========================================================================
   Experience preview ("moment by moment"): previous/next buttons, a counter
   and progress bars for the scroll-snap photo strip, and a play-on-intent
   video clip. The strip works without this (it scrolls on its own); the clip
   is created only when the visitor presses play, so nothing is downloaded
   before that.
   ========================================================================== */

export function initMoments(root = document) {
  root.querySelectorAll("[data-moments]").forEach((el) => {
    deferPhotos(el);
    setupStrip(el);
  });
  root.querySelectorAll("[data-film]").forEach(setupFilm);
}

// The photos carry their addresses in data-* (the browser's own lazy loading fetched all of them with
// the page, the section being near the top). Load each when it is within 200px of the screen; a photo
// further along the strip is clipped by it, so it waits until it scrolls in or is next (see show()).
function load(img) {
  if (!img || !img.hasAttribute("data-defer")) return;
  img.removeAttribute("data-defer");
  const source = img.parentElement.querySelector("source[data-srcset]");
  if (source) source.srcset = source.dataset.srcset;
  img.srcset = img.dataset.srcset;
  img.src = img.dataset.src;
}

// Also used by immersion.js, with a wider margin (its photos fill the screen).
export function deferPhotos(el, margin = "200px 0px") {
  const imgs = [...el.querySelectorAll("img[data-defer]")];
  if (!("IntersectionObserver" in window)) {
    imgs.forEach(load);
    return;
  }
  const io = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (e.isIntersecting) {
        load(e.target);
        io.unobserve(e.target);
      }
    }),
    { rootMargin: margin }
  );
  imgs.forEach((img) => io.observe(img));
}

function setupStrip(el) {
  const track = el.querySelector(".moments__track");
  const nav = el.querySelector("[data-moments-nav]");
  if (!track || !nav) return;
  const items = [...track.children];
  const prev = nav.querySelector("[data-moments-prev]");
  const next = nav.querySelector("[data-moments-next]");
  const count = nav.querySelector("[data-moments-count]");
  const bars = [...nav.querySelectorAll(".moments__bar")];
  const template = count.dataset.template || "{n} / {total}";
  const reduce = window.matchMedia("(prefers-reduced-motion: reduce)");
  let current = 0;

  const show = (i, preload = true) => {
    current = i;
    // Once the visitor moves along the strip, the next photo starts loading before they ask for it.
    if (preload) [items[i], items[i + 1]].forEach((it) => it && load(it.querySelector("img[data-defer]")));
    count.textContent = template.replace("{n}", i + 1).replace("{total}", items.length);
    bars.forEach((b, k) => b.classList.toggle("is-on", k <= i));
    prev.disabled = i === 0;
    next.disabled = i === items.length - 1;
  };

  // Scroll the strip (only the strip: never the page) so item i sits at its start edge, in LTR and RTL.
  const go = (i) => {
    const item = items[Math.max(0, Math.min(items.length - 1, i))];
    const pad = parseFloat(getComputedStyle(track).scrollPaddingInlineStart) || 0;
    const rtl = getComputedStyle(track).direction === "rtl";
    const a = item.getBoundingClientRect();
    const t = track.getBoundingClientRect();
    const delta = rtl ? a.right - (t.right - pad) : a.left - (t.left + pad);
    track.scrollBy({ left: delta, behavior: reduce.matches ? "auto" : "smooth" });
  };

  prev.addEventListener("click", () => go(current - 1));
  next.addEventListener("click", () => {
    [items[current + 1], items[current + 2]].forEach((it) => it && load(it.querySelector("img[data-defer]")));
    go(current + 1);
  });

  // The item that is most in view is the current one.
  const ratios = new Map();
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => ratios.set(e.target, e.intersectionRatio));
      let best = current;
      let max = 0;
      items.forEach((it, k) => {
        const r = ratios.get(it) || 0;
        if (r > max + 0.01) {
          max = r;
          best = k;
        }
      });
      if (best !== current) show(best);
    },
    { root: track, threshold: [0.25, 0.5, 0.75, 1] }
  );
  items.forEach((it) => io.observe(it));

  show(0, false);
  nav.hidden = false;
}

function setupFilm(fig) {
  const play = fig.querySelector("[data-film-play]");
  const tpl = fig.querySelector("template[data-film-src]");
  const error = fig.querySelector("[data-film-error]");
  if (!play || !tpl) return;

  play.addEventListener("click", () => {
    const video = tpl.content.firstElementChild.cloneNode(true);
    let failed = false;
    const fail = () => {
      if (failed) return;
      failed = true;
      video.remove();
      play.hidden = false;
      if (error) error.hidden = false;
      play.focus();
    };
    // <source> errors don't reach the video: the last source failing means none could play.
    const sources = [...video.querySelectorAll("source")];
    if (sources.length) sources[sources.length - 1].addEventListener("error", fail);
    video.addEventListener("error", fail);
    if (error) error.hidden = true;
    play.hidden = true;
    play.after(video);
    video.focus();
    const p = video.play();
    if (p && p.catch) p.catch(() => {}); // autoplay refusal: the visitor still has the controls
  });
}
