/* Egypt Roamer → Conversations: the team's inbox (includes/chat.php).
   Short polling: the list every 5 s and the open conversation every 3 s while the tab is in front; every
   30 s / 15 s while it is in the background (teams keep the inbox in a background tab: it keeps the member
   "online" and shows the unread count in the tab title). Every message is rendered with textContent. */
(function () {
  "use strict";
  const cfg = window.erChat;
  const app = document.querySelector("[data-er-chat]");
  if (!cfg || !app) return;
  const t = cfg.i18n;
  const $ = (s, r = app) => r.querySelector(s);
  const list = $("[data-list]");
  const thread = $("[data-thread]");
  const log = $("[data-log]");
  const info = $("[data-info]");
  const form = $("[data-reply]");
  const area = form.querySelector("textarea");
  const state = $("[data-state]");
  const assign = $("[data-assign]");
  const team = $("[data-team]");
  let filter = "all";
  let current = null; // conversation id
  let lastId = 0;
  let conv = null;
  let listTimer = 0;
  let convTimer = 0;
  let failures = 0;
  const baseTitle = document.title.replace(/^\(\d+\) /, "");

  const el = (tag, attrs = {}, text) => {
    const n = document.createElement(tag);
    for (const [k, v] of Object.entries(attrs)) if (v != null) n.setAttribute(k, v);
    if (text != null) n.textContent = text;
    return n;
  };
  const when = (iso) => {
    const d = new Date(iso);
    const mins = Math.round((Date.now() - d) / 60000);
    if (mins < 1) return "now";
    if (mins < 60) return mins + " min";
    if (mins < 1440) return Math.round(mins / 60) + " h";
    return d.toLocaleDateString();
  };
  const cid = () => "a" + Date.now().toString(36) + Math.random().toString(36).slice(2, 10);

  async function api(path, opts = {}) {
    const res = await fetch(cfg.root + path, {
      credentials: "same-origin",
      ...opts,
      headers: { "X-WP-Nonce": cfg.nonce, ...(opts.body ? { "Content-Type": "application/json" } : {}) },
    });
    if (!res.ok) throw new Error(String(res.status));
    return res.json();
  }

  function connection(ok) {
    failures = ok ? 0 : failures + 1;
    if (failures >= 2) team.textContent = t.offline;
  }

  /* ---------- inbox ---------- */
  async function loadList() {
    clearTimeout(listTimer);
    const q = $("[data-search]").value.trim();
    const sort = $("[data-sort]").value;
    try {
      const data = await api(`list?filter=${encodeURIComponent(filter)}&sort=${encodeURIComponent(sort)}${q ? "&q=" + encodeURIComponent(q) : ""}`);
      connection(true);
      renderList(data.rows);
      team.textContent = data.online.length ? t.online.replace("%d", data.online.length) : t.nobody;
      document.title = (data.unread ? `(${data.unread}) ` : "") + baseTitle;
      document.querySelectorAll(".er-chat-badge").forEach((b) => {
        b.style.display = data.unread ? "" : "none";
        const p = b.querySelector(".pending-count");
        if (p) p.textContent = data.unread;
      });
    } catch (e) {
      connection(false);
    }
    listTimer = setTimeout(loadList, document.hidden ? 30000 : 5000);
  }

  function renderList(rows) {
    list.replaceChildren();
    if (!rows.length) {
      list.append(el("li", { class: "er-chat__empty" }, t.empty));
      return;
    }
    for (const r of rows) {
      const li = el("li", { class: "er-chat__row" + (r.unread ? " is-unread" : "") + (r.id === current ? " is-current" : "") });
      const b = el("button", { type: "button", "data-open": r.id, "aria-current": r.id === current ? "true" : null });
      const top = el("span", { class: "er-chat__row-top" });
      top.append(el("strong", {}, (r.unread ? "● " : "") + (r.name || t.noname) + " #" + r.id), el("span", { class: "er-chat__when" }, when(r.last)));
      b.append(top);
      b.append(el("span", { class: "er-chat__meta" }, [t.status[r.status] || r.status, (r.lang || "").toUpperCase(), r.page].filter(Boolean).join(" · ")));
      if (r.preview) b.append(el("span", { class: "er-chat__preview" }, r.preview));
      li.append(b);
      list.append(li);
    }
  }

  list.addEventListener("click", (e) => {
    const b = e.target.closest("[data-open]");
    if (b) open(Number(b.dataset.open));
  });
  app.querySelectorAll("[data-filter]").forEach((b) =>
    b.addEventListener("click", () => {
      filter = b.dataset.filter;
      app.querySelectorAll("[data-filter]").forEach((x) => x.setAttribute("aria-pressed", String(x === b)));
      loadList();
    })
  );
  let searchTimer;
  $("[data-search]").addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadList, 300);
  });
  $("[data-sort]").addEventListener("change", loadList);

  /* ---------- presence ---------- */
  const presence = $("[data-presence]");
  async function sendPresence() {
    try {
      await api("presence", { method: "POST", body: JSON.stringify({ state: presence.value }) });
    } catch (e) {}
  }
  presence.addEventListener("change", sendPresence);
  setInterval(() => presence.value !== "offline" && sendPresence(), 60000); // also in a background tab

  /* ---------- conversation ---------- */
  async function open(id) {
    current = id;
    lastId = 0;
    log.replaceChildren();
    thread.hidden = false;
    app.classList.add("is-reading");
    location.hash = String(id);
    await loadConv(true);
    area.focus();
    loadList();
  }

  async function loadConv(markRead) {
    clearTimeout(convTimer);
    if (!current) return;
    const id = current;
    try {
      // From a little before the newest id seen: with concurrent writes a lower id can be committed late.
      const data = await api(`${id}?after=${Math.max(0, lastId - 20)}&read=1`);
      if (id !== current) return;
      connection(true);
      conv = data.conversation;
      renderInfo(conv, data.agents);
      for (const m of data.messages) appendMessage(m);
    } catch (e) {
      connection(false);
    }
    convTimer = setTimeout(() => loadConv(false), document.hidden ? 15000 : 3000);
  }

  function renderInfo(c, agents) {
    info.replaceChildren();
    const f = t.fields;
    const rows = [
      ["name", c.name || t.noname],
      ["email", c.email || "—"],
      ["lang", (c.lang || "—").toUpperCase()],
      ["page", c.page || "—"],
      ["entry", c.entry || "—"],
      ["status", t.status[c.status] || c.status],
      ["assigned", c.assigned ? c.assigned.name : "—"],
      ["created", new Date(c.created).toLocaleString()],
      ["last", new Date(c.last).toLocaleString()],
      ["id", "#" + c.id],
    ];
    for (const [k, v] of rows) {
      info.append(el("dt", {}, f[k]));
      const dd = el("dd");
      if ((k === "page" || k === "entry") && v.startsWith("/")) dd.append(el("a", { href: new URL(v, cfg.home).href, target: "_blank", rel: "noopener" }, v));
      else if (k === "email" && v.includes("@")) dd.append(el("a", { href: "mailto:" + v }, v));
      else dd.textContent = v;
      info.append(dd);
    }
    const s = c.status;
    const show = { takeover: s !== "human" || (c.assigned && c.assigned.id !== cfg.me), return_ai: s === "human" || s === "requested" || s === "waiting", close: s !== "closed" && s !== "archived", reopen: s === "closed" || s === "archived", unread: true, archive: s === "closed" };
    app.querySelectorAll("[data-action]").forEach((b) => (b.hidden = !show[b.dataset.action]));
    if (agents && assign.options.length !== agents.length + 1) {
      assign.replaceChildren(el("option", { value: "0" }, "—"));
      for (const a of agents) assign.append(el("option", { value: a.id }, a.name));
    }
    assign.value = c.assigned ? String(c.assigned.id) : "0";
  }

  function appendMessage(m) {
    // Ids already shown are skipped (polls overlap: see loadConv); new ones go in server order.
    if (log.querySelector(`[data-id="${m.id}"]`)) return;
    lastId = Math.max(lastId, m.id);
    // A reply sent from this page is already shown: swap the pending bubble for the stored one.
    const pending = m.client ? log.querySelector(`[data-client="${CSS.escape(m.client)}"]`) : null;
    const li = el("li", { class: `er-chat__msg er-chat__msg--${m.sender}${m.internal ? " is-internal" : ""}`, "data-id": m.id });
    const head = el("span", { class: "er-chat__who" }, `${t.sender[m.sender] || m.sender}${m.author ? " · " + m.author : ""} · ${new Date(m.time).toLocaleTimeString()}${m.meta && m.meta.assistant ? " · " + t.pages : ""}`);
    li.append(head);
    let body = m.body;
    if (m.sender === "ai" && m.meta && m.meta.items && m.meta.items.length) body = [m.body, ...m.meta.items.map((i) => "• " + i.title)].filter(Boolean).join("\n");
    li.append(el("p", {}, body));
    if (pending) pending.remove();
    const later = [...log.children].find((c) => (c.dataset.id && Number(c.dataset.id) > m.id) || c.classList.contains("is-pending") || c.classList.contains("is-failed"));
    if (later) log.insertBefore(li, later);
    else log.append(li);
    log.scrollTop = log.scrollHeight;
  }

  async function sendReply(text, client, bubble) {
    bubble.querySelector(".er-chat__who").textContent = t.sending;
    bubble.classList.remove("is-failed");
    state.textContent = t.sending;
    try {
      await api(`${current}/reply`, { method: "POST", body: JSON.stringify({ text, client_id: client }) });
      state.textContent = "";
      loadConv(false);
    } catch (e) {
      bubble.classList.add("is-failed");
      bubble.querySelector(".er-chat__who").textContent = t.failed;
      const retry = el("button", { type: "button", class: "button-link" }, t.retry);
      retry.addEventListener("click", () => {
        retry.remove();
        sendReply(text, client, bubble);
      });
      bubble.append(retry);
      state.textContent = t.failed;
    }
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const text = area.value.trim();
    if (!text || !current) return;
    area.value = "";
    const client = cid();
    const bubble = el("li", { class: "er-chat__msg er-chat__msg--agent is-pending", "data-client": client });
    bubble.append(el("span", { class: "er-chat__who" }, t.sending), el("p", {}, text));
    log.append(bubble);
    log.scrollTop = log.scrollHeight;
    sendReply(text, client, bubble);
  });
  area.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && (e.ctrlKey || e.metaKey)) form.requestSubmit();
  });

  app.querySelectorAll("[data-action]").forEach((b) =>
    b.addEventListener("click", async () => {
      if (b.dataset.action === "archive" && !window.confirm(t.confirm)) return;
      try {
        const data = await api(`${current}/action`, { method: "POST", body: JSON.stringify({ action: b.dataset.action }) });
        renderInfo(data.conversation);
        loadConv(false);
        loadList();
      } catch (e) {
        state.textContent = t.failed;
      }
    })
  );
  assign.addEventListener("change", async () => {
    try {
      const data = await api(`${current}/action`, { method: "POST", body: JSON.stringify({ action: "assign", user: Number(assign.value) }) });
      renderInfo(data.conversation);
      loadConv(false);
    } catch (e) {
      state.textContent = t.failed;
    }
  });
  $("[data-back]").addEventListener("click", () => {
    app.classList.remove("is-reading");
    thread.hidden = true;
    current = null;
    history.replaceState(null, "", location.pathname + location.search);
    loadList();
  });

  // Back in front: refresh at once (the timers themselves keep running, slower, in the background).
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
      loadList();
      loadConv(false);
    }
  });

  presence.value = ["away", "offline"].includes(cfg.presence) ? cfg.presence : "online"; // a chosen Away/Offline is kept
  sendPresence();
  loadList();
  const fromHash = Number(location.hash.slice(1));
  if (fromHash) open(fromHash);
})();
