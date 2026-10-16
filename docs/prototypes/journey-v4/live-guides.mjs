// The production Travel Guide index: every published guide with its title, link, excerpt and photo. Read-only.
import fs from "node:fs"; import { chromium } from "playwright-core";
const br=await chromium.launch({channel:"chrome"}); const pg=await (await br.newContext({viewport:{width:1440,height:900}})).newPage();
await pg.goto("https://egyptroamer.com/guides/",{waitUntil:"networkidle",timeout:90000});
const hero=await pg.evaluate(()=>({title:document.querySelector(".page-hero h1")?.textContent.trim(),lead:document.querySelector(".page-hero p")?.textContent.trim()}));
const grab=()=>pg.evaluate(()=>{ const seen=new Map(); document.querySelectorAll('.page-body a[href*="/guides/"]').forEach(a=>{ const h=a.href; if(/\/guides\/?$/.test(h)) return; const card=a.closest("article,li,.card")||a; const img=card.querySelector("img"); const t=(card.querySelector("h2,h3")||a).textContent.trim(); if(!seen.has(h)||(!seen.get(h).img&&img)) seen.set(h,{href:h,title:t,excerpt:(card.querySelector("p")||{}).textContent?.trim()||"",img:img?(img.currentSrc||img.src):""}); }); return [...seen.values()]; });
let cards=[]; for(let n=1;n<=4;n++){ if(n>1){ const r=await pg.goto(`https://egyptroamer.com/guides/page/${n}/`,{waitUntil:"networkidle"}); if(!r||r.status()!==200) break; } for(const c of await grab()) if(!/\/page\/\d+\/?$/.test(c.href)&&!cards.some(x=>x.href===c.href)) cards.push(c); }
for(const c of cards){ const m=c.img.match(/photo-(\d{10,13}-[0-9a-f]{12})/); c.photo=m?m[1]:""; if(c.photo&&!fs.existsSync(`img/${c.photo}.jpg`)){ const r=await fetch(`https://images.unsplash.com/photo-${c.photo}?auto=format&fit=crop&w=1000&q=62&fm=jpg`); fs.writeFileSync(`img/${c.photo}.jpg`,Buffer.from(await r.arrayBuffer())); } delete c.img; }
fs.writeFileSync("live/guides.json",JSON.stringify({hero,cards})); console.log(hero, cards.length); cards.forEach(c=>console.log(c.href.split("/guides/")[1], "|", c.photo||"-", "|", c.title.slice(0,50)));
await br.close();
