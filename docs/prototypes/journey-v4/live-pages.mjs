// The footer's pages on production (owner, 17 Oct: footer links led nowhere in the prototype). Read-only.
import fs from "node:fs"; import { chromium } from "playwright-core";
const P={affiliate:"/affiliate-disclosure/",contact:"/contact/",privacy:"/privacy-policy/",cookies:"/cookies/"};
const br=await chromium.launch({channel:"chrome"}); const pg=await (await br.newContext({viewport:{width:1440,height:900}})).newPage(); const out={};
for(const [k,u] of Object.entries(P)){ await pg.goto("https://egyptroamer.com"+u,{waitUntil:"networkidle",timeout:90000});
  out[k]=await pg.evaluate(()=>{ const h=document.querySelector(".page-hero"); const b=document.querySelector(".page-body")||document.querySelector("main");
    const c=b.cloneNode(true); c.querySelectorAll("script,style,iframe,.er-hp").forEach(e=>e.remove()); c.querySelectorAll("img").forEach(e=>e.remove());
    return {title:(h?.querySelector("h1")||document.querySelector("h1"))?.textContent.trim(), lead:h?.querySelector("p")?.textContent.trim()||"", html:c.innerHTML.replace(/\s+/g," ")}; });
  console.log(k, out[k].title, out[k].html.length); }
fs.writeFileSync("live/pages.json",JSON.stringify(out)); await br.close();
// the new food photos (free Unsplash, location tag "Cairo, Egypt", read 17 Oct)
fs.mkdirSync("img",{recursive:true});
for(const [id,w] of [["1738245868177-8c9977608031",1600],["1521791697570-e1f13d0b81d0",1200],["1590657140810-25898a847305",1200],["1668964413056-d6a503257522",1400],["1652022262085-d454346a353e",1200]]){
  const f=`img/${id}.jpg`; if(!fs.existsSync(f)){ const r=await fetch(`https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&q=66&fm=jpg`); fs.writeFileSync(f,Buffer.from(await r.arrayBuffer())); } console.log(f, Math.round(fs.statSync(f).size/1024)+"KB"); }
