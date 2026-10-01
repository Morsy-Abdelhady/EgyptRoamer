// A request that never answers must not freeze the chat (found 2026-10-02: one stalled inbox request stopped
// all inbox refreshes until a reload). The first matching request is held unanswered; the page must keep going.
//   1 team inbox: list refreshes continue after one stalled list request
//   2 visitor: polling continues after one stalled poll, and messages typed behind a stalled send still go out
// Usage: node chat-stall.mjs   (local site, admin/admin)
import { chromium } from "playwright";
import { execFileSync } from "child_process";
const B = process.env.BASE || "http://127.0.0.1:8080";
const WP = process.env.WPCLI || "C:/Users/morsy/er/wp.sh";
execFileSync("sh", [WP, "eval", "global $wpdb; $wpdb->query( \"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%er_ch_%'\" );"], { stdio: "ignore" });
const results = [];
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const br = await chromium.launch({ channel: "chrome" });

/* 1 inbox */
const tctx = await br.newContext({ viewport: { width: 1280, height: 800 } });
const team = await tctx.newPage();
await team.goto(`${B}/wp-login.php`, { timeout: 120000 });
await team.fill("#user_login", process.env.USER || "admin");
await team.fill("#user_pass", process.env.PASS || "admin");
await Promise.all([team.waitForNavigation({ timeout: 120000 }), team.click("#wp-submit")]);
let held = false;
let lists = 0;
await team.route(/chat\/team\/list/, (r) => {
  lists++;
  if (!held) { held = true; return; } // never answered
  r.continue();
});
await team.goto(`${B}/wp-admin/admin.php?page=er-conversations`, { timeout: 120000 });
await team.waitForTimeout(30000);
check(lists >= 3, "1 inbox keeps refreshing after a stalled request", `${lists} list requests in 30 s`);
await team.unroute(/chat\/team\/list/);

/* 2 visitor */
const vctx = await br.newContext({ viewport: { width: 390, height: 844 } });
const v = await vctx.newPage();
await v.goto(`${B}/destinations/`, { waitUntil: "load", timeout: 120000 });
await v.waitForTimeout(1200);
await v.click(".assistant-launch");
await v.waitForSelector("#assistant:not([hidden]) [data-chat-open]");
await v.click("[data-chat-open]");
await v.fill("[data-chat-form] [name=name]", "Stall QA");
await v.fill("[data-chat-form] [name=message]", "Testing a stalled connection");
await v.waitForTimeout(1700);
await v.click("[data-chat-form] [type=submit]");
await v.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 30000 });
let heldPoll = false;
let polls = 0;
await vctx.route(/chat\/poll/, (r) => {
  polls++;
  if (!heldPoll) { heldPoll = true; return; }
  r.continue();
});
await v.waitForTimeout(35000);
check(polls >= 2, "2 visitor polling continues after a stalled poll", `${polls} polls in 35 s`);
let heldSend = false;
await vctx.route(/chat\/send/, (r) => {
  if (!heldSend) { heldSend = true; return; }
  r.continue();
});
await v.fill("#assistant-q", "first (stalled)");
await v.press("#assistant-q", "Enter");
await v.fill("#assistant-q", "second");
await v.press("#assistant-q", "Enter");
const state = await (async () => {
  const end = Date.now() + 40000;
  while (Date.now() < end) {
    const s = await v.evaluate(() => [...document.querySelectorAll(".assistant__msg--me")].map((b) => b.querySelector("p").textContent + ":" + (b.classList.contains("is-failed") ? "failed" : b.classList.contains("is-pending") ? "pending" : "sent")));
    if (s.some((x) => x === "second:sent")) return s;
    await v.waitForTimeout(1000);
  }
  return await v.evaluate(() => [...document.querySelectorAll(".assistant__msg--me")].map((b) => b.querySelector("p").textContent + ":" + (b.classList.contains("is-failed") ? "failed" : b.classList.contains("is-pending") ? "pending" : "sent")));
})();
check(state.includes("first (stalled):failed") && state.includes("second:sent"), "2 a stalled send fails visibly (Retry) and the next message still goes out", state.join(", "));
await br.close();
const bad = results.filter((r) => !r).length;
console.log(bad ? `${bad} failing of ${results.length}` : `chat stall: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
