// Chat with the team, end to end in all 8 languages (launch gate area 4, 2026-10-02). Per language, a visitor on
// a phone-sized screen and a team member in the inbox:
//   a assistant answer first (the AI step on this site: page suggestions)  b start the chat (double-clicked: one
//   conversation only)  c inbox shows it with the language  d team reply arrives without a reload
//   e three quick visitor messages arrive in order, no duplicates  f drawer closed and reopened: transcript kept;
//   page reloaded: transcript restored  g second tab: same transcript, a team reply reaches both
//   h background tab: no polling while hidden, the reply shows as soon as it is visible again
//   i offline send fails visibly, Retry after reconnecting delivers it once  j long text wraps, no overflow
//   k RTL bubbles on the reading side (ar)  l team closes: visitor sees it  m no script errors
// Usage: node chat-locales.mjs   (LANGS=en,ar… ; clears the local chat rate limits first via WP-CLI)
import { chromium } from "playwright";
import { execFileSync } from "child_process";
const B = process.env.BASE || "http://127.0.0.1:8080";
const WP = process.env.WPCLI || "C:/Users/morsy/er/wp.sh";
const LANGS = (process.env.LANGS || "en,ar,de,fr,it,es,ru,zh").split(",");
const RUN = Date.now().toString(36).slice(-5);
const MSG = {
  en: "We are visiting Cairo for three days. Can someone help us plan?",
  ar: "نزور القاهرة لمدة ثلاثة أيام. هل يمكن لأحد مساعدتنا في التخطيط؟",
  de: "Wir sind drei Tage in Kairo. Kann uns jemand bei der Planung helfen?",
  fr: "Nous visitons Le Caire pendant trois jours. Quelqu’un peut-il nous aider ?",
  it: "Visitiamo il Cairo per tre giorni. Qualcuno può aiutarci a organizzare?",
  es: "Visitamos El Cairo durante tres días. ¿Alguien puede ayudarnos a planificar?",
  ru: "Мы будем в Каире три дня. Может кто-нибудь помочь спланировать поездку?",
  zh: "我们将在开罗待三天。有人能帮我们规划行程吗？",
};
const results = [];
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const waitFor = async (fn, ms = 15000, step = 400) => {
  const end = Date.now() + ms;
  while (Date.now() < end) {
    const v = await fn().catch(() => null);
    if (v) return v;
    await new Promise((r) => setTimeout(r, step));
  }
  return null;
};
execFileSync("sh", [WP, "eval", "global $wpdb; $wpdb->query( \"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%er_ch_%'\" );"], { stdio: "ignore" });

const br = await chromium.launch({ channel: "chrome" });
const quiet = (ctx) => ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media", "font"].includes(r.request().resourceType()) ? r.abort() : r.continue()));

/* team member */
const teamCtx = await br.newContext({ viewport: { width: 1440, height: 900 } });
const team = await teamCtx.newPage();
await team.goto(`${B}/wp-login.php`, { timeout: 120000 });
await team.fill("#user_login", process.env.USER || "admin");
await team.fill("#user_pass", process.env.PASS || "admin");
await Promise.all([team.waitForNavigation({ timeout: 120000 }), team.click("#wp-submit")]);
await team.goto(`${B}/wp-admin/admin.php?page=er-conversations`, { timeout: 120000 });
await team.waitForSelector("[data-er-chat]");
await team.selectOption("[data-presence]", "online").catch(() => {});
await team.waitForTimeout(2000);
const nonce = await team.evaluate(() => window.erChat.nonce);
const teamApi = (path, body) => team.evaluate(async ([p, b, n]) => { const r = await fetch("/wp-json/egypt-roamer/v1/chat/team/" + p, { method: b ? "POST" : "GET", headers: { "X-WP-Nonce": n, "Content-Type": "application/json" }, body: b ? JSON.stringify(b) : undefined }); return { status: r.status, data: await r.json().catch(() => ({})) }; }, [path, body, nonce]);
const teamReply = async (name, text) => {
  const list = (await teamApi("list?filter=all")).data.rows || [];
  const row = list.find((r) => r.name === name);
  if (!row) return null;
  return (await teamApi(`${row.id}/reply`, { text, client_id: "t" + Math.random().toString(36).slice(2, 10) })).status;
};
const bubbles = (p) => p.evaluate(() => [...document.querySelectorAll("#assistant [data-assistant-log] .assistant__msg")].map((m) => m.querySelector("p")?.textContent || ""));

for (const lang of LANGS) {
  const L = (s) => `${lang} ${s}`;
  const name = `QA ${lang} ${RUN}`;
  const w = lang === "ar" ? 375 : 390;
  const ctx = await br.newContext({ viewport: { width: w, height: 812 } });
  await quiet(ctx);
  const v = await ctx.newPage();
  const errs = [];
  v.on("pageerror", (e) => errs.push(e.message));
  let loads = 0;
  v.on("load", () => loads++);
  await v.goto(`${B}/${lang === "en" ? "" : lang + "/"}destinations/`, { waitUntil: "load", timeout: 180000 });
  await v.waitForTimeout(1200);
  await v.click(".assistant-launch");
  await v.waitForSelector("#assistant:not([hidden]) [data-chat-open]", { timeout: 30000 });

  // a: assistant answer
  await v.click("#assistant [data-assistant-ask]");
  check(!!(await v.waitForSelector("#assistant .assistant__a", { timeout: 60000 }).catch(() => null)), L("a assistant answers first"));

  // b: start (double click on submit)
  await v.click("[data-chat-open]");
  await v.fill("[data-chat-form] [name=name]", name);
  await v.fill("[data-chat-form] [name=message]", MSG[lang]);
  await v.waitForTimeout(1700);
  await v.dblclick("[data-chat-form] [type=submit]");
  const started = await waitFor(() => v.evaluate(() => document.getElementById("assistant").classList.contains("is-chat")), 20000);
  check(!!started, L("b conversation started"));
  await v.waitForTimeout(1500);
  const list = (await teamApi("list?filter=all")).data.rows || [];
  const mine = list.filter((r) => r.name === name);
  check(mine.length === 1, L("b double-clicked start: exactly one conversation"), String(mine.length));

  // c: inbox
  const row = await waitFor(() => team.evaluate((n) => [...document.querySelectorAll(".er-chat__row")].find((r) => r.textContent.includes(n))?.textContent.replace(/\s+/g, " "), name), 15000);
  check(!!row, L("c inbox lists it without reload"), (row || "").slice(0, 90));
  await team.evaluate((n) => [...document.querySelectorAll(".er-chat__row button")].find((b) => b.textContent.includes(n))?.click(), name);
  await team.waitForSelector("[data-log] li", { timeout: 20000 }).catch(() => {});
  const info = await team.evaluate(() => document.querySelector("[data-info]")?.textContent.replace(/\s+/g, " ") || "");
  check(info.toUpperCase().includes(lang.toUpperCase()), L("c team sees the visitor's language"), info.slice(0, 120));
  const teamLog = await team.evaluate(() => document.querySelector("[data-log]")?.textContent || "");
  check(teamLog.includes(MSG[lang].slice(0, 12)), L("c message text intact in the inbox (encoding)"));

  // d: reply without reload
  const r1 = `Reply 1 for ${lang} ${RUN}`;
  await team.fill("#er-chat-reply", r1);
  await team.click("[data-reply] [type=submit]");
  const loadsBefore = loads;
  check(!!(await waitFor(async () => (await bubbles(v)).includes(r1), 20000)), L("d visitor receives the team reply"));
  check(loads === loadsBefore, L("d without a page reload"));

  // e: three quick messages
  const seq = [1, 2, 3].map((i) => `${lang} quick ${i} ${RUN}`);
  for (const s of seq) { await v.fill("#assistant-q", s); await v.press("#assistant-q", "Enter"); }
  const inOrder = await waitFor(() => team.evaluate((s) => { const t = document.querySelector("[data-log]").textContent; const idx = s.map((x) => t.indexOf(x)); return idx.every((i) => i >= 0) && idx[0] < idx[1] && idx[1] < idx[2]; }, seq), 20000);
  check(!!inOrder, L("e three quick messages reach the team in order"));
  const vb = await bubbles(v);
  check(seq.every((s) => vb.filter((x) => x === s).length === 1), L("e no duplicate visitor bubbles"));

  // f: close/reopen, reload
  await v.keyboard.press("Escape");
  await v.waitForTimeout(800);
  await v.click(".assistant-launch").catch(async () => { await v.evaluate(() => scrollTo(0, innerHeight)); await v.waitForTimeout(800); await v.click(".assistant-launch"); });
  await v.waitForTimeout(1500);
  const afterReopen = await bubbles(v);
  check(afterReopen.includes(r1) && seq.every((s) => afterReopen.includes(s)), L("f drawer closed and reopened: transcript kept"));
  await v.reload({ waitUntil: "load" });
  await v.waitForTimeout(1200);
  await v.click(".assistant-launch").catch(async () => { await v.evaluate(() => scrollTo(0, innerHeight)); await v.waitForTimeout(800); await v.click(".assistant-launch"); });
  const restored = await waitFor(async () => { const b = await bubbles(v); return b.includes(r1) && seq.every((s) => b.includes(s)); }, 20000);
  check(!!restored, L("f page reloaded: transcript restored"));

  // g: second tab
  const v2 = await ctx.newPage();
  v2.on("pageerror", (e) => errs.push("tab2: " + e.message));
  await v2.goto(`${B}/${lang === "en" ? "" : lang + "/"}experiences/`, { waitUntil: "load", timeout: 180000 });
  await v2.waitForTimeout(1200);
  await v2.click(".assistant-launch").catch(async () => { await v2.evaluate(() => scrollTo(0, innerHeight)); await v2.waitForTimeout(800); await v2.click(".assistant-launch"); });
  check(!!(await waitFor(async () => (await bubbles(v2)).includes(r1), 20000)), L("g second tab shows the same conversation"));
  const r2 = `Reply 2 for ${lang} ${RUN}`;
  await teamReply(name, r2);
  const both = await waitFor(async () => (await bubbles(v)).includes(r2) && (await bubbles(v2)).includes(r2), 25000);
  check(!!both, L("g a team reply reaches both tabs"));
  await v2.close();

  // h: background tab
  await v.evaluate(() => { Object.defineProperty(document, "hidden", { configurable: true, get: () => true }); document.dispatchEvent(new Event("visibilitychange")); });
  let polls = 0;
  const onReq = (r) => /\/chat\/poll/.test(r.url()) && polls++;
  v.on("request", onReq);
  const r3 = `Reply 3 for ${lang} ${RUN}`;
  await teamReply(name, r3);
  await v.waitForTimeout(7000);
  check(polls === 0, L("h hidden tab does not poll"), String(polls));
  await v.evaluate(() => { Object.defineProperty(document, "hidden", { configurable: true, get: () => false }); document.dispatchEvent(new Event("visibilitychange")); });
  const t0 = Date.now();
  check(!!(await waitFor(async () => (await bubbles(v)).includes(r3), 10000, 200)), L("h reply shown when the tab is visible again"), `${Date.now() - t0} ms`);
  v.off("request", onReq);

  // i: offline → failed → retry
  await ctx.setOffline(true);
  const off = `${lang} offline ${RUN}`;
  await v.fill("#assistant-q", off);
  await v.press("#assistant-q", "Enter");
  const failed = await waitFor(() => v.evaluate(() => !!document.querySelector("#assistant .assistant__msg.is-failed button")), 15000);
  check(!!failed, L("i offline: message marked failed with Retry"));
  await ctx.setOffline(false);
  await v.click("#assistant .assistant__msg.is-failed button").catch(() => {});
  const delivered = await waitFor(() => team.evaluate((s) => document.querySelector("[data-log]").textContent.split(s).length - 1, off), 20000);
  check(delivered === 1, L("i after reconnecting, Retry delivers it exactly once"), String(delivered));

  // j/k: long text, layout
  const long = (lang === "de" ? "Donaudampfschifffahrtsgesellschaft " : lang === "zh" ? "金字塔与尼罗河游轮的行程安排" : lang === "ar" ? "رحلة نيلية طويلة بين الأقصر وأسوان " : "Itinerary ") .repeat(25);
  await v.fill("#assistant-q", long);
  await v.press("#assistant-q", "Enter");
  await v.waitForTimeout(2500);
  const geo = await v.evaluate(() => {
    const logBox = document.querySelector("[data-assistant-log]").getBoundingClientRect();
    const mine = [...document.querySelectorAll(".assistant__msg--me")];
    const me = mine[mine.length - 1].getBoundingClientRect();
    const team = [...document.querySelectorAll(".assistant__msg--team")].pop()?.getBoundingClientRect();
    return { overflow: document.documentElement.scrollWidth > innerWidth + 1, inside: me.left >= logBox.left - 1 && me.right <= logBox.right + 1, meLeft: Math.round(me.left - logBox.left), meRight: Math.round(logBox.right - me.right), teamLeft: team ? Math.round(team.left - logBox.left) : null, teamRight: team ? Math.round(logBox.right - team.right) : null, dir: getComputedStyle(document.documentElement).direction };
  });
  check(!geo.overflow && geo.inside, L("j long text wraps inside the bubble, no overflow"), JSON.stringify(geo));
  if (lang === "ar") check(geo.dir === "rtl" && geo.teamRight < geo.teamLeft, L("k RTL: team bubbles start on the right, the visitor's on the left"), JSON.stringify(geo));
  await v.screenshot({ path: `chat-loc-${lang}-${w}.png` });

  // l: close
  const list2 = (await teamApi("list?filter=all")).data.rows || [];
  const id = list2.find((r) => r.name === name)?.id;
  const closedRes = await teamApi(`${id}/action`, { action: "close" });
  // closed: the status line changes and "End chat" disappears (setStatus in assistant.js)
  const closed = await waitFor(() => v.evaluate(() => document.querySelector("[data-chat-end]").hidden && document.querySelector("[data-chat-status]").textContent.trim()), 20000);
  check(closedRes.status === 200 && !!closed, L("l close reaches the visitor"), String(closed));
  check(errs.length === 0, L("m no script errors"), errs.join(" | "));
  await ctx.close();
}
await br.close();
const bad = results.filter((r) => !r).length;
console.log(bad ? `${bad} failing of ${results.length}` : `chat locales: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
