// Chat with the team, end to end (Core includes/chat.php, theme src/js/assistant.js, assets/chat-admin.js).
// A visitor browser and a team member's browser side by side, against the local site (admin/admin, see CLAUDE.md):
//  1 no chat request or chat script before the drawer opens
//  2 visitor: assistant question → "Chat with Egypt Roamer" → name/email/message → conversation created
//  3 team: the inbox shows it unread with language, page and the earlier assistant question; reply
//  4 visitor gets the reply without a reload; visitor answers; the team sees it without a reload
//  5 Return to AI: the assistant answers again; Ask for the team; Take over: no assistant reply while a person owns it
//  6 close → the visitor sees the closed state
//  7 three visitors at once: replies reach only their own conversation
//  8 team offline → "Leave us a message", conversation waiting
//  9 team routes refused to visitors; visitor routes refuse other conversations
// 10 Arabic (RTL, 375 px) and English (390 px): layout, no overflow, axe on the open chat
// 11 inbox on a phone-sized screen
// Usage: node chat.mjs   (BASE=…, USER/PASS for another local site). Clears the chat rate limits first via WP-CLI if WPCLI is set.
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const axeSrc = fs.readFileSync("node_modules/axe-core/axe.min.js", "utf8");
const br = await chromium.launch({ channel: "chrome" });
const results = [];
const RUN = Date.now().toString(36).slice(-5); // unique names: earlier runs' conversations stay in the inbox
const ANN = "Ann QA " + RUN;
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const waitFor = async (fn, ms = 15000, step = 500) => {
  const end = Date.now() + ms;
  while (Date.now() < end) {
    const v = await fn();
    if (v) return v;
    await new Promise((r) => setTimeout(r, step));
  }
  return null;
};
// Local images from the photo host are irrelevant here.
const quiet = async (ctx) => ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media", "font"].includes(r.request().resourceType()) ? r.abort() : r.continue()));

/* ---------- team member ---------- */
const teamCtx = await br.newContext({ viewport: { width: 1440, height: 900 } });
const team = await teamCtx.newPage();
await team.goto(`${B}/wp-login.php`, { timeout: 120000 });
await team.fill("#user_login", process.env.USER || "admin");
await team.fill("#user_pass", process.env.PASS || "admin");
await Promise.all([team.waitForNavigation({ timeout: 120000 }), team.click("#wp-submit")]);
await team.goto(`${B}/wp-admin/admin.php?page=er-conversations`, { timeout: 120000 });
await team.waitForSelector("[data-er-chat]");
await team.waitForTimeout(2500); // presence: online
check(await team.evaluate(() => /online/i.test(document.querySelector("[data-team]").textContent)), "team online in the inbox");

/* ---------- visitor ---------- */
const vCtx = await br.newContext({ viewport: { width: 390, height: 844 } });
await quiet(vCtx);
const v = await vCtx.newPage();
const vReq = [];
v.on("request", (r) => /\/chat\/|assistant\.js/.test(r.url()) && vReq.push(r.url()));
const vErr = [];
v.on("pageerror", (e) => vErr.push(e.message));
await v.goto(`${B}/destinations/cairo/`, { waitUntil: "load", timeout: 120000 });
await v.waitForTimeout(1500);
check(vReq.length === 0, "1 no chat request or assistant script before opening", vReq.join(" "));
await v.click(".assistant-launch");
await v.waitForSelector("#assistant:not([hidden]) [data-chat-open]");
check(await waitFor(() => v.evaluate(() => /online/i.test(document.querySelector("[data-chat-avail]").textContent))), "visitor sees the team online");
await v.click("#assistant [data-assistant-ask]"); // "Pyramids": an assistant answer first
await v.waitForSelector("#assistant .assistant__a", { timeout: 60000 });
await v.click("[data-chat-open]");
await v.fill("[data-chat-form] [name=name]", ANN);
await v.fill("[data-chat-form] [name=email]", "ann.qa@example.com");
await v.fill("[data-chat-form] [name=message]", "I am visiting Cairo for 3 days. Can someone help me plan?");
await v.waitForTimeout(1700);
await v.click("[data-chat-form] [type=submit]");
const started = await waitFor(() => v.evaluate(() => document.getElementById("assistant").classList.contains("is-chat") && document.querySelector("[data-chat-status]").textContent));
check(!!started, "2 conversation started", String(started));
const stored = await v.evaluate(() => JSON.parse(localStorage.getItem("er-chat") || "null"));
check(stored && stored.id && stored.token.length === 64, "visitor holds id + token in this browser only");

/* ---------- team: the new conversation ---------- */
const row = await waitFor(() => team.evaluate((n) => [...document.querySelectorAll(".er-chat__row")].find((r) => r.textContent.includes(n))?.className, ANN), 12000);
check(row && row.includes("is-unread"), "3 inbox shows the conversation unread without reload", row);
await team.evaluate((n) => [...document.querySelectorAll(".er-chat__row button")].find((b) => b.textContent.includes(n)).click(), ANN);
await team.waitForSelector("[data-log] li");
const infoText = await team.evaluate(() => document.querySelector("[data-info]").textContent);
check(/EN/.test(infoText) && infoText.includes("/destinations/cairo/"), "team sees language and current page", infoText.replace(/\s+/g, " ").slice(0, 160));
const logText = await team.evaluate(() => document.querySelector("[data-log]").textContent);
check(/Pyramid/i.test(logText) && logText.includes("3 days"), "team sees the earlier assistant question and the message");
await team.fill("#er-chat-reply", "Absolutely. I can help you plan the three days.");
await team.click("[data-reply] [type=submit]");
await waitFor(() => team.evaluate(() => !document.querySelector("[data-log] .is-pending")));
check(await team.evaluate(() => !document.querySelector("[data-log] .is-failed")), "team reply stored (no failed state)");

/* ---------- visitor gets it live ---------- */
const got = await waitFor(() => v.evaluate(() => [...document.querySelectorAll(".assistant__msg--team")].some((m) => m.textContent.includes("three days"))), 15000);
check(!!got, "4 visitor receives the reply without reload");
check(await v.evaluate(() => /chatting/i.test(document.querySelector("[data-chat-status]").textContent)), "visitor status: with the team");
await v.fill("#assistant-q", "Thank you! We like history and food.");
await v.press("#assistant-q", "Enter");
const back = await waitFor(() => team.evaluate(() => document.querySelector("[data-log]").textContent.includes("history and food")), 12000);
check(!!back, "team receives the visitor's answer without reload");

/* ---------- Return to AI, ask for the team, take over ---------- */
await team.click("[data-action=return_ai]");
await waitFor(() => v.evaluate(() => /back with the trip assistant/i.test(document.querySelector("[data-chat-status]").textContent)), 12000);
check(await v.evaluate(() => !document.querySelector("[data-chat-human]").hidden), "5 Return to AI: visitor sees it, can ask for the team");
const aiBefore = await v.evaluate(() => document.querySelectorAll("#assistant .assistant__msg--ai").length);
await v.fill("#assistant-q", "Luxor");
await v.press("#assistant-q", "Enter");
const aiAnswered = await waitFor(() => v.evaluate((n) => document.querySelectorAll("#assistant .assistant__msg--ai").length > n, aiBefore), 15000);
check(!!aiAnswered, "assistant answers again in AI mode");
await v.click("[data-chat-human]");
await waitFor(() => team.evaluate(() => /Asked for the team|Waiting/.test(document.querySelector("[data-info]").textContent)), 12000);
await team.click("[data-action=takeover]");
await team.waitForTimeout(1200);
const aiCount = await v.evaluate(() => document.querySelectorAll("#assistant .assistant__msg--ai").length);
await v.fill("#assistant-q", "Is the Egyptian Museum worth it?");
await v.press("#assistant-q", "Enter");
await v.waitForTimeout(5000);
check(await v.evaluate((n) => document.querySelectorAll("#assistant .assistant__msg--ai").length === n, aiCount), "no assistant reply while a person owns the conversation");
await team.fill("#er-chat-reply", "Yes, plan half a day for it.");
await team.click("[data-reply] [type=submit]");
check(!!(await waitFor(() => v.evaluate(() => document.querySelector("#assistant [data-assistant-log]").textContent.includes("half a day")), 15000)), "takeover reply reaches the visitor");
const dupes = await v.evaluate(() => { const t = [...document.querySelectorAll(".assistant__msg")].map((m) => m.textContent); return t.length - new Set(t).size; });
check(dupes === 0, "no duplicated bubbles", String(dupes));

/* ---------- close ---------- */
await team.click("[data-action=close]");
check(!!(await waitFor(() => v.evaluate(() => /closed/i.test(document.querySelector("[data-chat-status]").textContent)), 15000)), "6 visitor sees the closed state");

/* ---------- isolation: three visitors ---------- */
const api = (page, path, body, token) => page.evaluate(async ([p, b, tk]) => { const h = { "Content-Type": "application/json" }; if (tk) h["X-ER-Chat"] = tk; const r = await fetch("/wp-json/egypt-roamer/v1/chat/" + p, { method: "POST", credentials: "omit", headers: h, body: JSON.stringify(b) }); return { status: r.status, data: await r.json().catch(() => ({})) }; }, [path, body, token]);
const others = [];
for (const name of ["B", "C", "D"]) {
  const ctx = await br.newContext();
  const pg = await ctx.newPage();
  await pg.goto(`${B}/robots.txt`);
  const r = await api(pg, "start", { name: `Visitor ${name} ${RUN}`, message: "Hello from " + name, elapsed: 3000, lang: "en" });
  others.push({ name, pg, ...r.data });
}
check(others.every((o) => o.token), "7 three more conversations started");
const nonce = await team.evaluate(() => window.erChat.nonce);
const teamApi = (path, body) => team.evaluate(async ([p, b, n]) => { const r = await fetch("/wp-json/egypt-roamer/v1/chat/team/" + p, { method: b ? "POST" : "GET", headers: { "X-WP-Nonce": n, "Content-Type": "application/json" }, body: b ? JSON.stringify(b) : undefined }); return { status: r.status, data: await r.json().catch(() => ({})) }; }, [path, body, nonce]);
const listed = (await teamApi("list?filter=all")).data.rows;
const idOf = (n) => listed.find((r) => r.name === `Visitor ${n} ${RUN}`)?.id;
await teamApi(`${idOf("B")}/reply`, { text: "Only for B", client_id: "isoB12345" });
const polls = {};
for (const o of others) polls[o.name] = (await api(o.pg, "poll", { id: o.id, after: 0 }, o.token)).data.messages.map((m) => m.body);
check(polls.B.includes("Only for B") && !polls.C.includes("Only for B") && !polls.D.includes("Only for B"), "replies reach only their own conversation");
const cross = await api(others[1].pg, "poll", { id: others[0].id, after: 0 }, others[1].token);
check(cross.status === 404, "9 a visitor token cannot read another conversation", String(cross.status));
const anonTeam = await others[2].pg.evaluate(async () => (await fetch("/wp-json/egypt-roamer/v1/chat/team/list", { credentials: "omit" })).status);
check(anonTeam === 401 || anonTeam === 403, "team routes refused without a team login", String(anonTeam));

/* ---------- offline ---------- */
await team.selectOption("[data-presence]", "offline");
await team.waitForTimeout(1500);
const off = await vCtx.newPage();
await off.goto(`${B}/destinations/`, { waitUntil: "load", timeout: 120000 });
await off.evaluate(() => localStorage.removeItem("er-chat"));
await off.reload({ waitUntil: "load" });
await off.waitForTimeout(1500);
await off.click(".assistant-launch").catch(async () => { await off.evaluate(() => scrollTo(0, innerHeight * 1.5)); await off.waitForTimeout(1500); await off.click(".assistant-launch"); });
const offText = await waitFor(() => off.evaluate(() => { const t = document.querySelector("[data-chat-avail]").textContent; return /leave us a message/i.test(t) ? t : null; }), 10000);
check(!!offText, "8 team offline: visitors are asked to leave a message", String(offText));
await off.click("[data-chat-open]");
await off.fill("[data-chat-form] [name=message]", "Offline message: please email me about Aswan.");
await off.waitForTimeout(1700);
await off.click("[data-chat-form] [type=submit]");
check(!!(await waitFor(() => off.evaluate(() => /reply here as soon as we can/i.test(document.querySelector("[data-chat-status]").textContent)), 12000)), "offline conversation waiting, visitor told we'll reply");
await team.selectOption("[data-presence]", "online");

/* ---------- Arabic RTL + axe ---------- */
for (const [lang, w, h] of [["ar", 375, 812], ["en", 390, 844]]) {
  const ctx = await br.newContext({ viewport: { width: w, height: h } });
  await quiet(ctx);
  const pg = await ctx.newPage();
  await pg.goto(`${B}/${lang === "en" ? "" : lang + "/"}contact${lang === "en" ? "" : "-" + lang}/`, { waitUntil: "load", timeout: 120000 }).catch(() => {});
  if (!(await pg.$(".assistant-launch"))) await pg.goto(`${B}/${lang}/`, { waitUntil: "load", timeout: 120000 });
  await pg.waitForTimeout(1500);
  await pg.click(".assistant-launch");
  await pg.waitForSelector("#assistant:not([hidden]) [data-chat-open]");
  await pg.click("[data-chat-open]");
  await pg.fill("[data-chat-form] [name=message]", lang === "ar" ? "أزور القاهرة لثلاثة أيام. هل يمكن لأحد مساعدتي؟" : "Long message test " + "word ".repeat(80));
  await pg.waitForTimeout(1700);
  await pg.click("[data-chat-form] [type=submit]");
  await waitFor(() => pg.evaluate(() => document.getElementById("assistant").classList.contains("is-chat")));
  const geo = await pg.evaluate(() => {
    const me = document.querySelector(".assistant__msg--me").getBoundingClientRect();
    const logBox = document.querySelector("[data-assistant-log]").getBoundingClientRect();
    return { dir: document.documentElement.dir || getComputedStyle(document.documentElement).direction, meLeft: Math.round(me.left - logBox.left), meRight: Math.round(logBox.right - me.right), overflow: document.documentElement.scrollWidth > innerWidth + 1, panelFits: document.querySelector("#assistant .drawer__panel").getBoundingClientRect().width <= innerWidth };
  });
  // My messages sit at the end of the line: the right in English, the left in Arabic.
  const sideOk = lang === "ar" ? geo.meLeft < geo.meRight : geo.meRight < geo.meLeft;
  check(sideOk && !geo.overflow && geo.panelFits, `10 ${lang} ${w}: bubbles on the reading side, no overflow`, JSON.stringify(geo));
  await pg.addScriptTag({ content: axeSrc });
  const ax = await pg.evaluate(async () => (await axe.run("#assistant", { resultTypes: ["violations"] })).violations.map((x) => x.id + "×" + x.nodes.length));
  check(!ax.length, `axe on the open chat (${lang})`, ax.join(", "));
  await pg.screenshot({ path: `chat-${lang}-${w}.png` });
  await ctx.close();
}

/* ---------- inbox on a phone ---------- */
await team.setViewportSize({ width: 390, height: 844 });
await team.goto(`${B}/wp-admin/admin.php?page=er-conversations`, { waitUntil: "load", timeout: 120000 });
await team.waitForSelector(".er-chat__row button", { timeout: 60000 });
await team.click(".er-chat__row button");
await team.waitForSelector("[data-log] li");
const mob = await team.evaluate(() => ({ inboxHidden: getComputedStyle(document.querySelector(".er-chat__inbox")).display === "none", threadShown: !document.querySelector("[data-thread]").hidden, overflow: document.documentElement.scrollWidth > innerWidth + 1 }));
check(mob.inboxHidden && mob.threadShown && !mob.overflow, "11 inbox on a phone: list → conversation, no overflow", JSON.stringify(mob));
await team.click("[data-back]");
check(await team.evaluate(() => getComputedStyle(document.querySelector(".er-chat__inbox")).display !== "none"), "back to the list on a phone");
await team.screenshot({ path: "chat-admin-390.png" });
const badge = await team.evaluate(() => document.querySelector(".er-chat-badge .pending-count")?.textContent);
check(badge != null, "unread badge in the admin menu", "count " + badge);

/* ---------- inbox in a background tab (production finding 2026-10-01) ---------- */
await team.setViewportSize({ width: 1440, height: 900 });
await team.goto(`${B}/wp-admin/admin.php?page=er-conversations`, { waitUntil: "load", timeout: 120000 });
await team.waitForSelector("[data-er-chat]");
await team.evaluate(() => { Object.defineProperty(document, "hidden", { configurable: true, get: () => true }); document.dispatchEvent(new Event("visibilitychange")); });
const bg = await br.newContext();
const bgp = await bg.newPage();
await bgp.goto(`${B}/robots.txt`);
await api(bgp, "start", { name: `Background ${RUN}`, message: "Is anyone there?", elapsed: 3000, lang: "en" });
const seenBg = await waitFor(() => team.evaluate((n) => [...document.querySelectorAll(".er-chat__row")].some((r) => r.textContent.includes(n)) && /^\(\d+\) /.test(document.title), `Background ${RUN}`), 45000, 1000);
check(!!seenBg, "12 inbox in a background tab still updates (list + unread count in the tab title)", await team.title());
const stillOnline = await api(bgp, "status", {});
check(stillOnline.data.online === true, "team still counts as online with the inbox in the background");
await bg.close();

check(vErr.length === 0, "no script errors (visitor)", vErr.join(" | "));
await br.close();
const bad = results.filter((r) => !r).length;
console.log(bad ? `${bad} failing of ${results.length}` : `chat: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
