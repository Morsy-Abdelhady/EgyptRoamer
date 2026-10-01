// Visual audit: real rendered screenshots assembled into contact sheets for human inspection.
// node visual-sheets.mjs <sheet> [langs]   sheets: hero | hero-states | assistant | templates | nav | locales
// Output: sheets/<sheet>-<lang>.png (one column per width, scaled). Photos load from the live image host.
import { chromium } from "playwright";
import fs from "fs";
const B = process.env.BASE || "http://127.0.0.1:8080";
const sheet = process.argv[2] || "hero";
const LANGS = (process.argv[3] || "en,ar").split(",");
const WIDTHS = (process.env.W || "320x700,360x740,390x844,430x932,768x1024,1024x768,1280x800,1440x900,1920x1080").split(",").map((s) => s.split("x").map(Number));
fs.mkdirSync("sheets", { recursive: true });
const br = await chromium.launch({ channel: "chrome" });
const pfx = (l) => (l === "en" ? "" : l + "/");
const shots = [];
const shoot = async (p, name) => { const f = `sheets/_${name}.png`; await p.screenshot({ path: f }); shots.push(f); return f; };
const settle = (p, ms = 4500) => p.waitForTimeout(ms);
const gotoHome = async (p, l) => { await p.goto(`${B}/${pfx(l)}?vs=${Date.now()}`, { waitUntil: "load", timeout: 180000 }); await settle(p, 5500); };
const scrollTo = (p, f) => p.evaluate((f) => (window.__lenis ? window.__lenis.scrollTo(innerHeight * f, { immediate: true, force: true }) : scrollTo(0, innerHeight * f)), f);
const openAssistant = async (p) => { await p.evaluate(() => { const L = document.querySelector(".assistant-launch"); if (getComputedStyle(L).visibility === "hidden") window.scrollTo(0, innerHeight * 1.5); }); await p.waitForTimeout(800); await p.click(".assistant-launch"); await p.waitForTimeout(1500); };

for (const l of LANGS) {
  const files = [];
  for (const [w, h] of WIDTHS) {
    const ctx = await br.newContext({ viewport: { width: w, height: h } });
    const p = await ctx.newPage();
    const tag = `${sheet}-${l}-${w}`;
    try {
      if (sheet === "hero") {
        await gotoHome(p, l);
        files.push(await shoot(p, tag));
      } else if (sheet === "hero-states") {
        await gotoHome(p, l);
        await scrollTo(p, 0.5); await settle(p, 2000); files.push(await shoot(p, tag + "-a"));
        await scrollTo(p, 2.2); await settle(p, 2500); files.push(await shoot(p, tag + "-b"));
        await scrollTo(p, 0); await settle(p, 2000); await openAssistant(p); files.push(await shoot(p, tag + "-c"));
      } else if (sheet === "assistant") {
        await p.goto(`${B}/${pfx(l)}destinations/?vs=${Date.now()}`, { waitUntil: "load", timeout: 180000 });
        await settle(p, 1500);
        files.push(await shoot(p, tag + "-0closed"));
        await openAssistant(p); files.push(await shoot(p, tag + "-1open"));
        await p.click("#assistant [data-assistant-ask]"); await p.waitForSelector("#assistant .assistant__a", { timeout: 60000 }).catch(() => {}); await settle(p, 800);
        files.push(await shoot(p, tag + "-2answer"));
        await p.click("[data-chat-open]"); await settle(p, 600); files.push(await shoot(p, tag + "-3form"));
        await p.fill("[data-chat-form] [name=message]", l === "ar" ? "أزور القاهرة لثلاثة أيام، هل يمكن لأحد مساعدتي في التخطيط؟" : l === "de" ? "Ich besuche Kairo drei Tage lang. Kann mir jemand bei der Planung helfen?" : "I am visiting Cairo for 3 days. Can someone help me plan?");
        await p.waitForTimeout(1700); await p.click("[data-chat-form] [type=submit]");
        await p.waitForFunction(() => document.getElementById("assistant").classList.contains("is-chat"), null, { timeout: 60000 }).catch(() => {});
        await settle(p, 1200); files.push(await shoot(p, tag + "-4waiting"));
        await ctx.setOffline(true); await p.fill("#assistant-q", l === "ar" ? "رسالة بدون اتصال" : "offline message"); await p.press("#assistant-q", "Enter");
        await p.waitForSelector(".assistant__msg.is-failed", { timeout: 15000 }).catch(() => {}); files.push(await shoot(p, tag + "-5failed"));
        await ctx.setOffline(false);
      } else if (sheet === "templates") {
        for (const [k, path] of [["dest", "destinations/"], ["destination", l === "en" ? "destinations/cairo/" : null], ["exp", "experiences/"], ["404", "no-such-page-xyz/"]]) {
          if (!path) continue;
          await p.goto(`${B}/${pfx(l)}${path}?vs=${Date.now()}`, { waitUntil: "load", timeout: 180000 }); await settle(p, 1500);
          files.push(await shoot(p, `${tag}-${k}`));
        }
      } else if (sheet === "nav") {
        await p.goto(`${B}/${pfx(l)}destinations/?vs=${Date.now()}`, { waitUntil: "load", timeout: 180000 }); await settle(p, 1500);
        const burger = await p.$("[data-open=menu]");
        if (burger && (await burger.isVisible())) { await burger.click(); await settle(p, 1000); files.push(await shoot(p, tag + "-menu")); await p.keyboard.press("Escape"); await settle(p, 600); }
        const sw = await p.$(".lang-switch button, [data-lang-toggle], .lang__toggle");
        if (sw && (await sw.isVisible())) { await sw.click(); await settle(p, 600); files.push(await shoot(p, tag + "-lang")); await p.keyboard.press("Escape"); }
        await p.evaluate(() => scrollTo(0, document.documentElement.scrollHeight)); await settle(p, 1500); files.push(await shoot(p, tag + "-footer"));
      }
    } catch (e) {
      console.log("error", tag, e.message.split("\n")[0]);
    }
    await ctx.close();
  }
  console.log(sheet, l, files.length, "shots");
  fs.writeFileSync(`sheets/${sheet}-${l}.json`, JSON.stringify(files));
}
await br.close();
