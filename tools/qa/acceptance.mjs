// Business acceptance test through the real admin UI (classic meta boxes + block editor save).
import { chromium } from "playwright";
const B = "http://127.0.0.1:8080";
const b = await chromium.launch();
const ctx = await b.newContext({ viewport: { width: 1440, height: 1000 }, userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36" });
const p = await ctx.newPage();
const errs = []; p.on("pageerror", (e) => errs.push(String(e)));
const step = (n, ok, extra = "") => console.log(`${ok ? "PASS" : "FAIL"}  ${n}${extra ? " — " + extra : ""}`);

await p.goto(B + "/wp-login.php");
await p.fill("#user_login", "admin"); await p.fill("#user_pass", "admin"); await p.click("#wp-submit");
await p.waitForURL(/wp-admin/);
step("log in", true);

// Disable the block-editor welcome guide for a clean run
await p.goto(B + "/wp-admin/post-new.php?post_type=er_provider");
await p.waitForLoadState("networkidle");
await p.evaluate(() => { try { wp.data.dispatch("core/preferences").set("core/edit-post", "welcomeGuide", false); } catch (e) {} });
await p.keyboard.press("Escape");

async function save() {
  if (await p.locator("#publish").count()) { // classic edit screen
    await Promise.all([p.waitForNavigation(), p.click("#publish")]);
    return;
  }
  // Title in the editor canvas iframe (WP 6.x) or directly
  await p.locator('button.editor-post-publish-panel__toggle, button.editor-post-publish-button__button').first().click();
  const confirm = p.locator('.editor-post-publish-panel button.editor-post-publish-button');
  if (await confirm.count()) await confirm.first().click();
  await p.waitForSelector('.components-snackbar, .editor-post-publish-panel__postpublish', { timeout: 20000 });
  await p.waitForTimeout(1500); // meta boxes save in a second request
}
async function setTitle(t) {
  if (await p.locator("#title").count()) return p.fill("#title", t);
  const frame = p.frameLocator('iframe[name="editor-canvas"]');
  const inFrame = frame.locator('.editor-post-title__input, h1.wp-block-post-title');
  if (await p.locator('iframe[name="editor-canvas"]').count()) await inFrame.first().fill(t);
  else await p.locator('.editor-post-title__input, h1.wp-block-post-title').first().fill(t);
}

// 6. Provider
await setTitle("Acceptance Provider");
await p.fill('[name="er_meta[_er_domains]"]', "example.org");
await p.fill('[name="er_meta[_er_website]"]', "https://www.example.org/");
await save();
const provUrl = p.url(); const provId = Number(new URL(provUrl).searchParams.get("post")) || null;
step("create affiliate provider (admin UI)", !!provId, provUrl);

// 7-8. Offer connected to the experience
await p.goto(B + "/wp-admin/post-new.php?post_type=er_offer"); await p.waitForLoadState("networkidle"); await p.keyboard.press("Escape");
await setTitle("Giza sunrise acceptance offer");
await p.selectOption('[name="er_meta[_er_provider]"]', { label: "Acceptance Provider" });
await p.fill('[name="er_meta[_er_target_url]"]', "https://tours.example.org/giza-sunrise");
await p.selectOption('[name="er_meta[_er_cta]"]', "check_availability");
await p.selectOption('[name="er_meta[_er_experience]"]', { label: "Pyramids of Giza & Sphinx Private Tour" });
await p.fill('[name="er_meta[_er_utm_campaign]"]', "acceptance");
await p.fill('[name="er_meta[_er_priority]"]', "50");
await save();
const offerId = Number(new URL(p.url()).searchParams.get("post"));
// The block editor saves meta boxes in a second request after publishing: wait for it instead of racing it.
let redirectsTo = "";
for (let i = 0; i < 10 && !redirectsTo.includes("tours.example.org"); i++) {
  await p.waitForTimeout(i ? 1000 : 0); await p.reload(); await p.waitForLoadState("networkidle");
  redirectsTo = await p.locator('p:has-text("Redirects to:") code').first().textContent({ timeout: 2000 }).catch(() => "");
}
step("create offer, connect to experience, publish", !!offerId && redirectsTo.includes("tours.example.org"), redirectsTo);

// 10. CTA displayed on the experience page
const exp = B + "/experiences/pyramids-of-giza-sphinx-private-tour/";
const v = await ctx.newPage(); await v.goto(exp);
const cta = v.locator('a[data-er-offer="giza-sunrise-acceptance-offer"]').first();
const href = await cta.getAttribute("href").catch(() => null);
const rel = await cta.getAttribute("rel").catch(() => null);
step("CTA displayed with label, rel=sponsored", !!href && /sponsored/.test(rel || ""), `${await cta.textContent().catch(() => "")} | ${href} | rel=${rel}`);
const disclosure = await v.locator(".offer-box .disclosure").count();
step("disclosure shown next to the offer", disclosure > 0);

// 11-13. Click → tracked → external redirect (intercept the external hop; example.org is not reachable here)
// The outbound hop is observed as the browser's request to the partner host
// (this sandbox cannot reach external hosts, so that request itself fails).
let redirected = null;
ctx.on("request", (r) => { if (r.url().startsWith("https://tours.example.org/")) redirected = r.url(); });
const [popup] = await Promise.all([v.waitForEvent("popup"), cta.click()]);
await popup.waitForLoadState().catch(() => {});
await popup.waitForTimeout(800);
const dl = await v.evaluate(() => (window.dataLayer || []).filter((e) => e.event === "affiliate_click" || e.event === "booking_click").map((e) => e.event + ":" + e.offer + ":" + e.placement));
step("click → redirect to partner with tracking", !!redirected && redirected.includes("utm_campaign=acceptance"), redirected);
step("dataLayer affiliate_click / booking_click", dl.length === 2, dl.join(", "));

// 14. Report shows the click
await p.goto(B + "/wp-admin/admin.php?page=er-reports&days=7");
const reportText = await p.locator(".er-reports").textContent();
step("click visible in report (offer, provider, destination, placement)", /Giza sunrise acceptance offer/.test(reportText) && /Acceptance Provider/.test(reportText) && /Cairo/.test(reportText) && /experience-offers/.test(reportText));

// 15-16. Update URL and CTA, verify on site
await p.goto(B + `/wp-admin/post.php?post=${offerId}&action=edit`); await p.waitForLoadState("networkidle");
await p.fill('[name="er_meta[_er_target_url]"]', "https://tours.example.org/giza-sunrise-v2");
await p.fill('[name="er_meta[_er_cta_custom]"]', "See sunrise times");
await save();
await v.goto(exp);
const newLabel = await v.locator('a[data-er-offer="giza-sunrise-acceptance-offer"]').first().textContent();
redirected = null;
const [pop2] = await Promise.all([v.waitForEvent("popup"), v.locator('a[data-er-offer="giza-sunrise-acceptance-offer"]').first().click()]);
await pop2.waitForTimeout(800);
step("change CTA label (no code)", /See sunrise times/.test(newLabel), newLabel.trim());
step("update affiliate URL (no code)", !!redirected && redirected.includes("giza-sunrise-v2"), redirected);

// Homepage settings screen saves
await p.goto(B + "/wp-admin/themes.php?page=er-homepage");
await p.fill('[name="er_home[hero_copy]"]', "Acceptance test subtitle.");
await p.click('#submit');
await p.waitForURL(/updated=1/);
const home = await ctx.newPage(); await home.goto(B + "/");
step("update homepage content (no code)", (await home.locator(".hero__copy").textContent()).includes("Acceptance test subtitle."));
await p.goto(B + "/wp-admin/themes.php?page=er-homepage");
await p.fill('[name="er_home[hero_copy]"]', ""); await p.click('#submit'); await p.waitForURL(/updated=1/);

// Dashboard + settings + subscribers render
for (const [n, u] of [["dashboard", "admin.php?page=egypt-roamer"], ["settings", "admin.php?page=er-settings"], ["subscribers", "admin.php?page=er-subscribers"]]) {
  const r = await p.goto(B + "/wp-admin/" + u); step(`admin screen: ${n}`, r.status() === 200 && !(await p.content()).includes("Fatal error"));
}
console.log("page errors (excluding unbuilt Polylang admin JS):", errs.filter((e) => !e.includes("Unexpected token")));
await b.close();
