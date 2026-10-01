// Chat concurrency (local): two team members, two visitor tabs, simultaneous replies, stale assignment,
// close vs reply, reconnect after missed messages. Every view must end with the same messages, in server
// order, none duplicated or lost, each attributed to the right author.
// A temporary editor account is created with a random password (never printed) and deleted afterwards.
import { chromium } from "playwright";
import { execFileSync } from "child_process";
import crypto from "crypto";
const B = process.env.BASE || "http://127.0.0.1:8080";
const WP = process.env.WPCLI || "C:/Users/morsy/er/wp.sh";
const wp = (...a) => execFileSync("sh", [WP, ...a], { encoding: "utf8", stdio: ["ignore", "pipe", "ignore"] }).trim();
const br = await chromium.launch({ channel: "chrome" });
const results = [];
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const waitFor = async (fn, ms = 15000) => {
  const end = Date.now() + ms;
  while (Date.now() < end) {
    const v = await fn();
    if (v) return v;
    await new Promise((r) => setTimeout(r, 400));
  }
  return null;
};

// Second team member (temporary)
const pass = crypto.randomBytes(18).toString("base64url");
try { wp("user", "delete", "qa_agent2", "--yes"); } catch (e) {}
wp("user", "create", "qa_agent2", "qa_agent2@example.invalid", "--role=editor", "--display_name=QA Agent Two", `--user_pass=${pass}`);
wp("eval", "global $wpdb; $wpdb->query( \"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient%er_ch_%'\" );");

async function teamPage(user, pw) {
  const ctx = await br.newContext({ viewport: { width: 1280, height: 860 } });
  const p = await ctx.newPage();
  await p.goto(`${B}/wp-login.php`, { timeout: 120000 });
  await p.fill("#user_login", user);
  await p.fill("#user_pass", pw);
  await Promise.all([p.waitForNavigation({ timeout: 120000 }), p.click("#wp-submit")]);
  await p.goto(`${B}/wp-admin/admin.php?page=er-conversations`, { timeout: 120000 });
  await p.waitForSelector("[data-er-chat]");
  const nonce = await p.evaluate(() => window.erChat.nonce);
  const api = (path, body) => p.evaluate(async ([pa, b, n]) => {
    const r = await fetch("/wp-json/egypt-roamer/v1/chat/team/" + pa, { method: b ? "POST" : "GET", headers: { "X-WP-Nonce": n, "Content-Type": "application/json" }, body: b ? JSON.stringify(b) : undefined });
    return { status: r.status, data: await r.json().catch(() => ({})) };
  }, [path, body, nonce]);
  return { ctx, p, api };
}
const A = await teamPage(process.env.USER || "admin", process.env.PASS || "admin");
const Bm = await teamPage("qa_agent2", pass);

// Visitor with two tabs on the same conversation
const vctx = await br.newContext({ viewport: { width: 390, height: 844 } });
await vctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (r) => (["image", "media", "font"].includes(r.request().resourceType()) ? r.abort() : r.continue()));
const t1 = await vctx.newPage();
await t1.goto(`${B}/destinations/`, { waitUntil: "load", timeout: 120000 });
await t1.click(".assistant-launch");
await t1.waitForSelector("#assistant:not([hidden]) [data-chat-open]");
await t1.click("[data-chat-open]");
const RUN = Date.now().toString(36).slice(-5);
await t1.fill("[data-chat-form] [name=name]", "Multi " + RUN);
await t1.fill("[data-chat-form] [name=message]", "m1 visitor first");
await t1.waitForTimeout(1700);
await t1.click("[data-chat-form] [type=submit]");
await t1.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 60000 });
const t2 = await vctx.newPage();
await t2.goto(`${B}/experiences/`, { waitUntil: "load", timeout: 120000 });
await t2.click(".assistant-launch");
await t2.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 30000 });
check(true, "second tab opens the same conversation");

const cid = (await A.api("list?filter=all&q=" + encodeURIComponent("Multi " + RUN))).data.rows[0].id;

// 1 stale assignment: both members take over at the same moment (neither has refreshed)
const [ta, tb] = await Promise.all([A.api(`${cid}/action`, { action: "takeover" }), Bm.api(`${cid}/action`, { action: "takeover" })]);
const after = (await A.api(`${cid}`)).data;
const notes = after.messages.filter((m) => m.internal && m.body === "taken over").map((m) => m.author);
check(ta.status === 200 && tb.status === 200 && after.conversation.assigned && notes.length === 2, "simultaneous takeover: one owner, both actions in the audit trail", `owner ${after.conversation.assigned?.name}; notes by ${notes.join(", ")}`);

// 2 simultaneous replies by two members + a visitor message at the same time
await Promise.all([
  A.api(`${cid}/reply`, { text: "r-A1 from admin", client_id: "rA1" + RUN }),
  Bm.api(`${cid}/reply`, { text: "r-B1 from agent two", client_id: "rB1" + RUN }),
  t1.evaluate(() => { document.getElementById("assistant-q").value = "m2 visitor during replies"; document.querySelector("[data-assistant-form]").requestSubmit(); }),
]);
// a duplicate send of the same client id (lost response retried)
await Bm.api(`${cid}/reply`, { text: "r-B1 from agent two", client_id: "rB1" + RUN });

// 3 visitor tab 2 goes offline, team writes, then it reconnects
await t2.context().setOffline(true);
await A.api(`${cid}/reply`, { text: "r-A2 while visitor offline", client_id: "rA2" + RUN });
await t2.waitForTimeout(4000);
await t2.context().setOffline(false);

// 4 close by one member while the other replies: the reply is kept and reopens it with the team
await Promise.all([A.api(`${cid}/action`, { action: "close" }), Bm.api(`${cid}/reply`, { text: "r-B2 racing the close", client_id: "rB2" + RUN })]);

const server = (await A.api(`${cid}`)).data;
const visible = server.messages.filter((m) => !m.internal).map((m) => m.body);
const expectBodies = ["m1 visitor first", "r-A1 from admin", "r-B1 from agent two", "m2 visitor during replies", "r-A2 while visitor offline", "r-B2 racing the close"];
check(expectBodies.every((b) => visible.filter((x) => x === b).length === 1), "server: every message stored exactly once", visible.join(" | "));
const authors = Object.fromEntries(server.messages.filter((m) => m.sender === "agent").map((m) => [m.body, m.author]));
check(authors["r-A1 from admin"] !== authors["r-B1 from agent two"] && authors["r-B1 from agent two"] === "QA Agent Two", "replies attributed to the right member", JSON.stringify(authors));

// Every view converges on the server's order
const viewOf = (pg) => pg.evaluate(() => [...document.querySelectorAll("#assistant .assistant__msg")].map((m) => m.querySelector("p")?.textContent).filter(Boolean));
const okView = async (pg) => waitFor(async () => { const v = await viewOf(pg); return JSON.stringify(v) === JSON.stringify(visible) ? v : null; }, 20000);
check(!!(await okView(t1)), "visitor tab 1: same messages, same order", JSON.stringify(await viewOf(t1)));
check(!!(await okView(t2)), "visitor tab 2 (after reconnect): same messages, same order", JSON.stringify(await viewOf(t2)));
for (const [name, T] of [["member A", A], ["member B", Bm]]) {
  await T.p.evaluate((id) => { location.hash = String(id); }, cid);
  await T.p.reload();
  await T.p.waitForSelector("[data-log] li", { timeout: 60000 });
  const log = await T.p.evaluate(() => [...document.querySelectorAll("[data-log] li:not(.is-internal) p")].map((x) => x.textContent));
  check(JSON.stringify(log) === JSON.stringify(visible), `${name}'s inbox: same messages, same order`, JSON.stringify(log));
}
// Close and reply raced: either order is legitimate; the final status must follow the one processed last.
const fin = (await A.api(`${cid}`)).data;
const closeId = Math.max(...fin.messages.filter((m) => m.internal && m.body === "closed").map((m) => m.id));
const replyId = fin.messages.find((m) => m.body === "r-B2 racing the close").id;
const expected = closeId > replyId ? "closed" : "human";
check(fin.conversation.status === expected, "close racing a reply: final status follows the action processed last", `${fin.conversation.status} (close #${closeId}, reply #${replyId})`);

// 5 a message committed late with a LOWER id than one already shown (concurrent inserts under MySQL):
// it must still appear, in id order, in every view (polls re-read a small overlap).
// (open, so that every view keeps polling: a closed chat rightly stops the visitor's polling)
if ((await A.api(`${cid}`)).data.conversation.status !== "human") await A.api(`${cid}/action`, { action: "reopen" });
await t1.waitForTimeout(4000);
const maxId = Math.max(...(await A.api(`${cid}`)).data.messages.map((m) => m.id));
const insert = (id, body) => wp("eval", `global $wpdb; $wpdb->insert( er_chat_table( 'messages' ), [ 'id' => ${id}, 'conversation_id' => ${cid}, 'sender' => 'agent', 'user_id' => 1, 'body' => '${body}', 'created_at' => gmdate( 'Y-m-d H:i:s' ) ] );`);
insert(maxId + 10, "late-high");
await t1.waitForTimeout(5000); // views poll and see id+10
insert(maxId + 5, "late-low");
const wantTail = ["late-low", "late-high"];
const tailOk = async (getter) => waitFor(async () => { const v = await getter(); return JSON.stringify(v.slice(-2)) === JSON.stringify(wantTail) ? v : null; }, 20000);
check(!!(await tailOk(() => viewOf(t1))), "late-committed lower id: shown, in order (visitor)", JSON.stringify((await viewOf(t1)).slice(-3)));
const inbox = () => A.p.evaluate(() => [...document.querySelectorAll("[data-log] li:not(.is-internal) p")].map((x) => x.textContent));
check(!!(await tailOk(inbox)), "late-committed lower id: shown, in order (team inbox, no reload)", JSON.stringify((await inbox()).slice(-3)));

await Promise.all([A.ctx.close(), Bm.ctx.close(), vctx.close()]);
await br.close();
wp("user", "delete", "qa_agent2", "--yes");
const bad = results.filter((x) => !x).length;
console.log(bad ? `${bad} failing of ${results.length}` : `chat concurrency: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
