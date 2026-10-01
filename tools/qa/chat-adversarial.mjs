// Chat edge cases (local): reload mid-chat, double "Start chat", language switch mid-chat, network loss while
// sending + Retry (no duplicate), rapid drawer open/close (no polling left running), 2,000-character Arabic +
// emoji message. Usage: node chat-adversarial.mjs (clear the chat rate limits first, see chat.mjs).
import { chromium } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const br = await chromium.launch({ channel: "chrome" });
const results = [];
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const ctx = await br.newContext({ viewport: { width: 390, height: 844 } });
await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media", "font"].includes(r.request().resourceType()) ? r.abort() : r.continue()));
const p = await ctx.newPage();
const starts = [];
p.on("request", (r) => r.url().includes("/chat/start") && starts.push(r.url()));
const openDrawer = async () => {
  await p.click(".assistant-launch");
  await p.waitForSelector("#assistant:not([hidden]) [data-assistant-log]");
};
await p.goto(`${B}/destinations/`, { waitUntil: "load", timeout: 120000 });
await openDrawer();
await p.click("[data-chat-open]");
await p.fill("[data-chat-form] [name=message]", "Edge case test: double click");
await p.waitForTimeout(1700);
// 2 double "Start chat"
await p.dblclick("[data-chat-form] [type=submit]");
await p.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 60000 });
await p.waitForTimeout(1500);
check(starts.length === 1, "double click on Start chat starts one conversation", `${starts.length} start request(s)`);
const id = await p.evaluate(() => JSON.parse(localStorage.getItem("er-chat")).id);

// 1 reload mid-chat: the conversation and its history come back
await p.reload({ waitUntil: "load" });
await openDrawer();
const resumed = await p.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat") && [...document.querySelectorAll(".assistant__msg--me")].some((m) => m.textContent.includes("double click")), null, { timeout: 30000 }).then(() => true).catch(() => false);
check(resumed, "reload mid-chat resumes the conversation and its history");

// 3 language switch mid-chat: same conversation, German interface
await p.goto(`${B}/de/destinations/`, { waitUntil: "load", timeout: 120000 });
await openDrawer();
const de = await p.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat") && document.querySelector(".assistant__msg--me"), null, { timeout: 30000 }).then(() => p.evaluate(() => ({ id: JSON.parse(localStorage.getItem("er-chat")).id, title: document.getElementById("assistant-title").textContent, send: document.querySelector("[data-assistant-submit]").textContent }))).catch(() => null);
check(de && de.id === id && /Live-Chat/.test(de.title) && de.send === "Senden", "language switch keeps the conversation, interface in German", JSON.stringify(de));

// 4 network loss while sending, then Retry: one stored message
await ctx.setOffline(true);
await p.fill("#assistant-q", "Sent while offline");
await p.press("#assistant-q", "Enter");
const failed = await p.waitForSelector(".assistant__msg.is-failed", { timeout: 15000 }).then(() => true).catch(() => false);
check(failed, "offline send shows the failed state with Retry");
await ctx.setOffline(false);
await p.click(".assistant__msg.is-failed button");
await p.waitForFunction(() => !document.querySelector(".assistant__msg.is-failed, .assistant__msg.is-pending"), null, { timeout: 30000 }).catch(() => {});
const stored = await p.evaluate(async () => {
  const c = JSON.parse(localStorage.getItem("er-chat"));
  const r = await fetch("/wp-json/egypt-roamer/v1/chat/poll", { method: "POST", headers: { "Content-Type": "application/json", "X-ER-Chat": c.token }, body: JSON.stringify({ id: c.id, after: 0 }) });
  return (await r.json()).messages.filter((m) => m.body === "Sent while offline").length;
});
check(stored === 1, "Retry after the network came back stores the message once", `${stored} stored`);

// 5 rapid open/close: no polling once the drawer is closed
for (let i = 0; i < 6; i++) {
  await p.keyboard.press("Escape");
  await p.waitForTimeout(120);
  await p.click(".assistant-launch");
  await p.waitForTimeout(120);
}
await p.keyboard.press("Escape");
await p.waitForTimeout(500);
const polls = [];
p.on("request", (r) => r.url().includes("/chat/poll") && polls.push(Date.now()));
await p.waitForTimeout(12000);
check(polls.length === 0, "no polling after the drawer is closed (after rapid open/close)", `${polls.length} poll(s) in 12 s`);

// 6 a 2,000-character Arabic + emoji message
await p.click(".assistant-launch");
await p.waitForSelector("#assistant:not([hidden])");
const long = ("مرحبا بكم في مصر 🐫🌅 ").repeat(120).slice(0, 2000);
await p.fill("#assistant-q", long);
await p.press("#assistant-q", "Enter");
await p.waitForFunction(() => !document.querySelector(".assistant__msg.is-pending"), null, { timeout: 30000 }).catch(() => {});
const geo = await p.evaluate(() => {
  const m = [...document.querySelectorAll(".assistant__msg--me")].pop();
  const panel = document.querySelector("#assistant .drawer__panel").getBoundingClientRect();
  const b = m.getBoundingClientRect();
  return { failed: m.classList.contains("is-failed"), inside: b.left >= panel.left - 1 && b.right <= panel.right + 1, overflow: document.documentElement.scrollWidth > innerWidth + 1, len: m.querySelector("p").textContent.length };
});
check(!geo.failed && geo.inside && !geo.overflow && geo.len >= 1990, "2,000-character Arabic + emoji message sent and wrapped inside the drawer", JSON.stringify(geo));

await br.close();
const bad = results.filter((r) => !r).length;
console.log(bad ? `${bad} failing of ${results.length}` : `chat edge cases: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
