/* Inner pages: the sticky section tabs follow the reader (scroll spy) and keep
   the active tab in view inside the horizontally scrolling bar. */
import { $, $$ } from "../utils.js";

export function initSectionNav() {
  const bar = $(".secnav");
  if (!bar) return;
  const list = $(".secnav__list", bar);
  const links = $$(".secnav__link", bar);
  const pairs = links
    .map((a) => [a, document.getElementById(decodeURIComponent(a.hash.slice(1)))])
    .filter(([, el]) => el);
  if (!pairs.length) return;

  let current = null;
  const activate = (a) => {
    if (a === current) return;
    current = a;
    links.forEach((l) => {
      l.classList.toggle("is-active", l === a);
      if (l === a) l.setAttribute("aria-current", "location");
      else l.removeAttribute("aria-current");
    });
    const lr = a.getBoundingClientRect();
    const br = list.getBoundingClientRect();
    if (lr.left < br.left) list.scrollLeft -= br.left - lr.left + 24;
    else if (lr.right > br.right) list.scrollLeft += lr.right - br.right + 24;
  };

  let ticking = false;
  const update = () => {
    ticking = false;
    const line = bar.getBoundingClientRect().bottom + window.innerHeight * 0.25;
    let active = pairs[0][0];
    for (const [a, el] of pairs) {
      if (el.getBoundingClientRect().top <= line) active = a;
    }
    activate(active);
  };
  window.addEventListener(
    "scroll",
    () => {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    },
    { passive: true }
  );
  window.addEventListener("resize", update);
  update();

  // A soft fade on the side that has more tabs, so a tab cut by the edge reads as "scroll for more"
  // (physical sides from the tabs' positions, so the same code serves Arabic).
  const edges = () => {
    const br = list.getBoundingClientRect();
    const rs = links.map((l) => l.getBoundingClientRect());
    list.toggleAttribute("data-more-left", Math.min(...rs.map((r) => r.left)) < br.left - 1);
    list.toggleAttribute("data-more-right", Math.max(...rs.map((r) => r.right)) > br.right + 1);
  };
  list.addEventListener("scroll", () => requestAnimationFrame(edges), { passive: true });
  window.addEventListener("resize", edges);
  edges();
}
