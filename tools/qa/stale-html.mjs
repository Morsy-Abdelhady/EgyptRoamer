// Stale-HTML guard (Core includes/freshness.php + theme inc/assets.php), simulated cache states, local site.
// A stale copy is simulated by rewriting the page's build id in the response, as an old cached copy would carry.
import { chromium } from "playwright";
import { execFileSync } from "child_process";
const B = process.env.BASE || "http://127.0.0.1:8080";
const WP = process.env.WPCLI || "C:/Users/morsy/er/wp.sh"; // to make a real content change (scenario F)
const br = await chromium.launch({ channel: "chrome" });
const results = [];
const check = (ok, name, detail = "") => {
  results.push(ok);
  console.log(`${ok ? "ok  " : "FAIL"} ${name}${detail ? " — " + detail : ""}`);
};
const OLD = "000000000000";
// stale: "first" = only the first document response is stale (browser copy; the edge is current),
//        "always" = every document response without ?nocache is stale (the edge copy is stale too)
async function visit(ctx, path, stale = null, failBuild = false) {
  const p = await ctx.newPage();
  const docs = [];
  const builds = [];
  let served = 0;
  await p.route("**/*", async (route) => {
    const req = route.request();
    if (req.url().includes("/egypt-roamer/v1/build")) {
      builds.push(req.url());
      if (failBuild) return route.fulfill({ status: 500, body: "{}" });
      return route.continue();
    }
    if (req.resourceType() !== "document") return /127\.0\.0\.1/.test(req.url()) || !["image", "media"].includes(req.resourceType()) ? route.continue() : route.abort();
    docs.push(req.url().replace(B, ""));
    const res = await route.fetch();
    let body = await res.text();
    const isStale = stale === "always" ? !/[?&]nocache=/.test(req.url()) : stale === "first" && served === 0;
    served++;
    if (isStale) body = body.replace(/<meta name="er-build" content="[a-f0-9]+"/, `<meta name="er-build" content="${OLD}"`);
    return route.fulfill({ response: res, body });
  });
  await p.goto(B + path, { waitUntil: "load", timeout: 180000 });
  await p.waitForTimeout(4000);
  const state = await p.evaluate(() => ({ url: location.pathname + location.search, build: document.querySelector("meta[name=er-build]")?.content }));
  await p.close();
  return { docs, builds, ...state };
}

// A fresh browser
let ctx = await br.newContext();
let r = await visit(ctx, "/destinations/");
check(r.docs.length === 1 && r.builds.length === 1, "A fresh browser: one build check, no reload", JSON.stringify(r));
// B second page within 30 minutes: no check
r = await visit(ctx, "/experiences/");
check(r.docs.length === 1 && r.builds.length === 0, "B next page within 30 min: no request", JSON.stringify(r));
// C browser copy stale (the edge is current): exactly one reload
r = await visit(ctx, "/destinations/", "first");
check(r.docs.length === 2 && r.build !== OLD && !r.url.includes("nocache"), "C stale browser copy: one reload, then the current page", JSON.stringify(r));
await ctx.close();

// D edge stale too: reload, then ?nocache= once, address bar cleaned, no loop
ctx = await br.newContext();
r = await visit(ctx, "/destinations/", "always");
check(r.docs.length === 3 && /nocache=/.test(r.docs[2]) && r.build !== OLD && !r.url.includes("nocache"), "D stale edge: reload, then one ?nocache= load; clean address bar", JSON.stringify(r));
await ctx.close();
// E search page: nocache goes first in the query (the edge's bypass rule)
ctx = await br.newContext();
r = await visit(ctx, "/?s=cairo", "always");
check(r.docs.length === 3 && /^\/\?nocache=[a-f0-9]+&s=cairo/.test(r.docs[2]) && r.url === "/?s=cairo", "E search page: ?nocache=… first, query kept", JSON.stringify(r));
await ctx.close();
// G build check failing: nothing happens, no loop
ctx = await br.newContext();
r = await visit(ctx, "/destinations/", "always", true);
check(r.docs.length === 1, "G build check failing: no reload", JSON.stringify(r));
await ctx.close();

// F a real content change (an editor saves a page): pages built before it reload once
ctx = await br.newContext();
const before = await visit(ctx, "/destinations/");
execFileSync("sh", [WP, "eval", "update_option( 'er_home_qa_touch', (string) time() ); er_bump_content_epoch();"], { stdio: "ignore" });
// the browser still holds the page built before the change (simulated: same html, current build check)
const p = await ctx.newPage();
const docs = [];
await p.route("**/*", async (route) => {
  if (route.request().resourceType() === "document") {
    docs.push(route.request().url());
    if (docs.length === 1) {
      const res = await route.fetch();
      const body = (await res.text()).replace(/<meta name="er-build" content="[a-f0-9]+"/, `<meta name="er-build" content="${before.build}"`);
      return route.fulfill({ response: res, body });
    }
  }
  return route.continue();
});
// The browser last confirmed this page as current more than 30 minutes ago (within 30 minutes it doesn't
// re-check: an editor's change reaches a browser at its next check, at most 30 minutes later).
await ctx.addInitScript(() => {
  try {
    const s = JSON.parse(localStorage.getItem("er-build") || "{}");
    if (s.at) localStorage.setItem("er-build", JSON.stringify({ cur: s.cur, at: s.at - 31 * 60 * 1000 }));
  } catch (e) {}
});
await p.goto(B + "/destinations/", { waitUntil: "load", timeout: 180000 });
await p.waitForTimeout(4000);
const after = await p.evaluate(() => document.querySelector("meta[name=er-build]")?.content);
check(after !== before.build && docs.length === 2, "F after an editor's change, a page built before it reloads once", `${before.build} → ${after}, ${docs.length} loads`);
execFileSync("sh", [WP, "eval", "delete_option( 'er_home_qa_touch' );"], { stdio: "ignore" });
await ctx.close();

await br.close();
const bad = results.filter((x) => !x).length;
console.log(bad ? `${bad} failing of ${results.length}` : `stale html: all ${results.length} ok`);
process.exit(bad ? 1 : 0);
