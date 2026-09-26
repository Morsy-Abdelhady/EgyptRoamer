/* "Watch the Film" — a full-screen cinematic sequence (Ken Burns + captions).
   Swap for a real <video> when the brand film exists. */
import { $, $$, on } from "../utils.js";
import { film, img } from "../data.js";

const SLIDE_MS = 4600;

export function initFilm() {
  const stage = $("[data-film-stage]");
  const caption = $("[data-film-caption]");
  const bar = $("[data-film-bar]");
  if (!stage) return;
  let timer = null;
  let i = 0;
  let startedAt = 0;
  let raf = null;

  const show = (n) => {
    i = n % film.length;
    $$("img", stage).forEach((el, k) => el.classList.toggle("is-on", k === i));
    caption.style.opacity = "0";
    setTimeout(() => {
      caption.innerHTML = `<small>${film[i].kicker}</small>${film[i].caption}`;
      caption.style.transition = "opacity .9s";
      caption.style.opacity = "1";
    }, 350);
  };
  const progress = () => {
    const p = ((performance.now() - startedAt) / (SLIDE_MS * film.length)) % 1;
    bar.style.transform = `scaleX(${p})`;
    raf = requestAnimationFrame(progress);
  };

  on("overlay:open", (id) => {
    if (id !== "film") return;
    if (!stage.children.length) {
      stage.innerHTML = film.map((f) => `<img src="${img(f.image, 2000, 78)}" alt="" />`).join("");
    }
    startedAt = performance.now();
    show(0);
    timer = setInterval(() => show(i + 1), SLIDE_MS);
    progress();
  });
  on("overlay:close", (id) => {
    if (id !== "film") return;
    clearInterval(timer);
    cancelAnimationFrame(raf);
  });
}
