/* Trip assistant: the drawer and its launcher are server-rendered (footer.php); the assistant's own
   script (assets/js/assistant.js) loads only when the drawer is first opened, so it costs nothing on
   page load. Questions asked before the script arrives are queued, not lost. */
import { $, on } from "../utils.js";

export function initAssistantLoader() {
  const root = $("#assistant");
  if (!root) return;
  // Homepage: while the journey stage is on screen, its scene counter ("01 / 04") sits in the same
  // corner as the launcher; the launcher moves up above it (CSS: body.assistant-raised).
  const stage = $("#journey-stage");
  if (stage && "IntersectionObserver" in window) {
    new IntersectionObserver(([entry]) => document.body.classList.toggle("assistant-raised", entry.isIntersecting)).observe(stage);
  }
  // Phones: on narrow screens the hero's buttons (play) reach the launcher's corner; the launcher steps
  // aside while they are actually visible there (the hero is pinned by the journey, so this checks
  // geometry and opacity rather than viewport intersection). CSS: body.assistant-clear.
  const ctas = $(".hero__ctas");
  const launch = $(".assistant-launch");
  if (ctas && launch && window.matchMedia) {
    const phone = window.matchMedia("(max-width: 900px)");
    let queued = false;
    const check = () => {
      queued = false;
      let hide = false;
      // Never while it has keyboard focus or its drawer is open (focus returns to it on close).
      if (phone.matches && document.activeElement !== launch && root.hidden) {
        // Too close counts too (20px): right next to the hero buttons, the round launcher reads as one of them.
        const r = launch.getBoundingClientRect();
        const b = { left: r.left - 20, right: r.right + 20, top: r.top - 20, bottom: r.bottom + 20 };
        const hit = [...ctas.querySelectorAll("a, button")].some((el) => {
          const a = el.getBoundingClientRect();
          return !(a.right <= b.left || a.left >= b.right || a.bottom <= b.top || a.top >= b.bottom);
        });
        if (hit) {
          // the row's own entrance fade doesn't count (the launcher should not appear, then leave);
          // the journey fades the hero through its ancestors
          hide = true;
          for (let e = ctas.parentElement; e && e !== document.body; e = e.parentElement) {
            if (parseFloat(getComputedStyle(e).opacity) < 0.05) hide = false;
          }
        }
      }
      document.body.classList.toggle("assistant-clear", hide);
    };
    const schedule = () => {
      if (!queued) {
        queued = true;
        requestAnimationFrame(check);
      }
    };
    // The journey's scrubbed animation keeps fading the hero after the last scroll event: check again
    // once scrolling has settled.
    let settle;
    const onScroll = () => {
      schedule();
      clearTimeout(settle);
      settle = setTimeout(schedule, 700);
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", schedule);
    launch.addEventListener("blur", schedule);
    on("overlay:close", schedule);
    phone.addEventListener?.("change", schedule);
    schedule();
  }
  const queue = (window.__erAssistantQueue = window.__erAssistantQueue || []);
  let loaded = false;
  const load = () => {
    if (loaded) return;
    loaded = true;
    let cfg = {};
    try {
      cfg = JSON.parse(root.dataset.assistant || "{}");
    } catch (e) {}
    if (!cfg.script) return;
    const s = document.createElement("script");
    s.src = cfg.script;
    s.async = true;
    document.head.appendChild(s);
  };
  on("overlay:open", (id) => {
    if (id !== "assistant") return;
    load();
    // Focus the question field, not the close button (the drawer's first control).
    setTimeout(() => $("#assistant-q")?.focus({ preventScroll: true }), 90);
  });
  // Until the script is ready: keep the question and stop the form from reloading the page.
  root.addEventListener("submit", (e) => {
    if (window.__erAssistant) return;
    e.preventDefault();
    queue.push(e.target.q.value);
  });
  root.addEventListener("click", (e) => {
    const chip = e.target.closest("[data-assistant-ask]");
    if (chip && !window.__erAssistant) queue.push(chip.textContent);
  });
}
