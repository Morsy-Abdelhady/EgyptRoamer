// Adversarial visual states, as screenshots for inspection (sheets/adv-*.png):
// missing images, reduced motion, logged-in admin bar, keyboard focus inside the drawer, slow loading,
// long German/Arabic chat text.
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
fs.mkdirSync("sheets", { recursive: true });
const br = await chromium.launch({ channel: "chrome" });
const files = [];
const shot = async (p, n) => { const f = `sheets/_adv-${n}.png`; await p.screenshot({ path: f }); files.push(f); };

// 1 missing images (photo host down)
for (const [l, w, h] of [["en", 390, 844], ["ar", 1440, 900]]) {
  const ctx = await br.newContext({ viewport: { width: w, height: h } });
  await ctx.route(/images\.unsplash\.com/, (r) => r.abort());
  const p = await ctx.newPage();
  await p.goto(`${B}/${l === "en" ? "" : l + "/"}`, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(6000);
  await shot(p, `noimg-home-${l}-${w}`);
  await p.goto(`${B}/${l === "en" ? "" : l + "/"}destinations/`, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(2000);
  await p.evaluate(() => scrollTo(0, innerHeight * 0.8)); await p.waitForTimeout(800);
  await shot(p, `noimg-archive-${l}-${w}`);
  await ctx.close();
}
// 2 reduced motion
for (const [l, w, h] of [["ar", 390, 844], ["en", 1440, 900]]) {
  const ctx = await br.newContext({ viewport: { width: w, height: h }, reducedMotion: "reduce" });
  const p = await ctx.newPage();
  await p.goto(`${B}/${l === "en" ? "" : l + "/"}`, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(3000);
  await shot(p, `reduced-top-${l}-${w}`);
  await p.evaluate(() => scrollTo(0, innerHeight * 1.2)); await p.waitForTimeout(1000);
  await shot(p, `reduced-scene-${l}-${w}`);
  await ctx.close();
}
// 3 logged in (admin bar) + drawer
for (const [l, w, h] of [["ar", 390, 844], ["en", 1280, 800]]) {
  const ctx = await br.newContext({ viewport: { width: w, height: h } });
  const p = await ctx.newPage();
  await p.goto(`${B}/wp-login.php`, { timeout: 120000 });
  await p.fill("#user_login", process.env.USER || "admin"); await p.fill("#user_pass", process.env.PASS || "admin");
  await Promise.all([p.waitForNavigation({ timeout: 120000 }), p.click("#wp-submit")]);
  await p.goto(`${B}/${l === "en" ? "" : l + "/"}destinations/`, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(2000);
  await shot(p, `loggedin-${l}-${w}`);
  await p.click(".assistant-launch"); await p.waitForTimeout(1500);
  await shot(p, `loggedin-drawer-${l}-${w}`);
  await ctx.close();
}
// 4 keyboard focus inside the drawer
for (const [l, w, h] of [["ar", 390, 844], ["de", 1280, 800]]) {
  const ctx = await br.newContext({ viewport: { width: w, height: h } });
  const p = await ctx.newPage();
  await p.goto(`${B}/${l}/destinations/`, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(1500);
  await p.focus(".assistant-launch"); await p.waitForTimeout(300); await shot(p, `focus-launcher-${l}-${w}`);
  await p.keyboard.press("Enter"); await p.waitForTimeout(1200);
  await p.keyboard.press("Shift+Tab"); await p.waitForTimeout(200); await p.keyboard.press("Shift+Tab"); await p.waitForTimeout(300); await shot(p, `focus-drawer1-${l}-${w}`);
  await p.focus("[data-chat-open]"); await p.waitForTimeout(300); await shot(p, `focus-chatcta-${l}-${w}`);
  await ctx.close();
}
// 5 slow loading (throttled network): what a visitor sees at 2 s and 5 s
{
  const ctx = await br.newContext({ viewport: { width: 390, height: 844 } });
  const p = await ctx.newPage();
  const cdp = await ctx.newCDPSession(p);
  await cdp.send("Network.enable");
  await cdp.send("Network.emulateNetworkConditions", { offline: false, latency: 400, downloadThroughput: 50000, uploadThroughput: 20000 });
  p.goto(`${B}/ar/`, { waitUntil: "load", timeout: 180000 }).catch(() => {});
  await p.waitForTimeout(2000); await shot(p, "slow-2s-ar-390");
  await p.waitForTimeout(3000); await shot(p, "slow-5s-ar-390");
  await ctx.close();
}
// 6 long German / Arabic chat text
for (const [l, txt] of [["de", "Wir möchten im Frühjahr eine zweiwöchige Rundreise durch Oberägypten machen: Kairo, Luxor, Assuan, Abu Simbel und eine Nilkreuzfahrt – welche Reihenfolge empfehlen Sie, und wie viele Nächte sollten wir jeweils einplanen? Donaudampfschifffahrtsgesellschaftskapitänsmütze."], ["ar", "نخطط لرحلة لمدة أسبوعين في الربيع تشمل القاهرة والأقصر وأسوان وأبو سمبل ورحلة نيلية، ما الترتيب الذي تنصحون به وكم ليلة نحتاج في كل مدينة؟ شكرًا جزيلًا لمساعدتكم."]]) {
  const ctx = await br.newContext({ viewport: { width: 320, height: 700 } });
  const p = await ctx.newPage();
  await p.goto(`${B}/${l}/destinations/`, { waitUntil: "load", timeout: 180000 }); await p.waitForTimeout(1500);
  await p.click(".assistant-launch"); await p.waitForTimeout(1200);
  await p.click("[data-chat-open]"); await p.fill("[data-chat-form] [name=name]", l === "de" ? "Maximiliane Schwarzenberg-Hohenlohe" : "عبد الرحمن بن عبد العزيز الأنصاري");
  await p.fill("[data-chat-form] [name=message]", txt); await p.waitForTimeout(1700);
  await p.click("[data-chat-form] [type=submit]"); await p.waitForTimeout(3000);
  await shot(p, `long-${l}-320`);
  await ctx.close();
}
fs.writeFileSync("sheets/adv.json", JSON.stringify(files));
console.log(files.length, "shots");
await br.close();
