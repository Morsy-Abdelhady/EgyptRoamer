// Page hygiene on representative pages (en + ar), the things axe and the crawl don't cover: duplicate ids,
// labels pointing at missing fields, target=_blank without noopener, images without dimensions (layout shift),
// http:// resources, inline event handlers, empty links, console warnings.
import { chromium } from "playwright";
const B = process.env.BASE || "http://127.0.0.1:8080";
const PATHS = ["/", "/destinations/", "/destinations/cairo/", "/experiences/", "/experiences/abu-simbel-day-trip-from-aswan/", "/contact/", "/privacy-policy/", "/?s=cairo", "/no-such-page-xyz/",
  "/ar/", "/ar/destinations/", "/ar/contact-ar/"];
const br = await chromium.launch({ channel: "chrome" });
let bad = 0;
for (const path of PATHS) {
  const ctx = await br.newContext({ viewport: { width: 1280, height: 900 } });
  const p = await ctx.newPage();
  const warns = [];
  p.on("console", (m) => m.type() === "warning" && !/GSAP target|DevTools/.test(m.text()) && warns.push(m.text().slice(0, 100)));
  const insecure = [];
  p.on("request", (r) => r.url().startsWith("http://") && !r.url().startsWith(B) && insecure.push(r.url().slice(0, 80)));
  await p.goto(B + path, { waitUntil: "load", timeout: 180000 }).catch(() => {});
  await p.waitForTimeout(2500);
  const r = await p.evaluate(() => {
    const ids = {};
    document.querySelectorAll("[id]").forEach((e) => (ids[e.id] = (ids[e.id] || 0) + 1));
    const dup = Object.entries(ids).filter(([, n]) => n > 1).map(([k, n]) => `${k}×${n}`);
    const badFor = [...document.querySelectorAll("label[for]")].filter((l) => !document.getElementById(l.htmlFor)).map((l) => l.htmlFor);
    const blank = [...document.querySelectorAll("a[target=_blank]")].filter((a) => !/noopener|noreferrer/.test(a.rel)).map((a) => a.href.slice(0, 60));
    // An image without width/height only shifts layout when its box takes its size from the image:
    // not when it isn't rendered at this width, is absolutely positioned, or fills a frame (object-fit, aspect ratio).
    const framed = (i) => !i.getClientRects().length || !i.getBoundingClientRect().width || getComputedStyle(i).position === "absolute" || getComputedStyle(i).objectFit !== "fill" || getComputedStyle(i.parentElement).aspectRatio !== "auto";
    const noDim = [...document.querySelectorAll("img")].filter((i) => !(i.getAttribute("width") && i.getAttribute("height")) && !i.closest("[hidden]") && !framed(i)).map((i) => (i.getAttribute("src") || "").split("/").pop().slice(0, 40));
    const inline = [...document.querySelectorAll("*")].filter((e) => [...e.attributes].some((a) => /^on[a-z]+$/.test(a.name))).map((e) => e.tagName);
    // (a card's image link duplicating its title link is hidden from assistive tech and the tab order: fine)
    const emptyLinks = [...document.querySelectorAll("a[href]")].filter((a) => !(a.getAttribute("aria-hidden") === "true" && a.tabIndex === -1) && !a.textContent.trim() && !a.getAttribute("aria-label") && !a.querySelector("img[alt]:not([alt=''])") && !a.getAttribute("title")).map((a) => a.href.slice(0, 50));
    return { dup, badFor, blank, noDim, inline, emptyLinks };
  });
  const probs = Object.entries(r).filter(([, v]) => v.length).map(([k, v]) => `${k}: ${[...new Set(v)].slice(0, 4).join(", ")}`);
  if (insecure.length) probs.push("http resources: " + insecure.slice(0, 3).join(", "));
  if (warns.length) probs.push("console warnings: " + [...new Set(warns)].slice(0, 2).join(" | "));
  if (probs.length) bad++;
  console.log(`${probs.length ? "FAIL" : "ok  "} ${path}${probs.length ? "\n      " + probs.join("\n      ") : ""}`);
  await ctx.close();
}
await br.close();
console.log(bad ? `${bad} pages with findings` : "hygiene: all ok");
