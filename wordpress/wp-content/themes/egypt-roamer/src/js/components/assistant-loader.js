/* Trip assistant: the drawer and its launcher are server-rendered (footer.php); the assistant's own
   script (assets/js/assistant.js) loads only when the drawer is first opened, so it costs nothing on
   page load. Questions asked before the script arrives are queued, not lost. */
import { $, on } from "../utils.js";

export function initAssistantLoader() {
  const root = $("#assistant");
  if (!root) return;
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
