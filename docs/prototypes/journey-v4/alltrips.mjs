// Every trip, as a tourist: open, read down, compare, book. Plus checks a tourist would feel.
import { chromium } from "playwright-core";
const sz=process.argv[2]||"desk"; const only=process.argv[3];
const br=await chromium.launch({channel:"chrome"}); const errs=[];
const pg=await (await br.newContext({viewport:{width:1440,height:960}})).newPage(); pg.on("pageerror",e=>errs.push(e.message));
await pg.goto(new URL("journey-v4.html", import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(1200);
await pg.evaluate(()=>{const n=document.querySelector("#show-notes"); if(n.checked) n.click();});
if(sz==="phone"){ await pg.click('[data-dev="phone"]'); await pg.waitForTimeout(600); }
const clip=await pg.evaluate(()=>{const r=document.querySelector(".browser").getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:Math.min(r.height,900)}});
const ids=await pg.evaluate(()=>TRIPS.filter(t=>WORLDS[t.id]).map(t=>t.id));
const report={};
for(const id of ids){ if(only&&id!==only) continue;
  await pg.evaluate(id=>{ current=id; lastViewed=id; go(3); },id); await pg.waitForTimeout(1300);
  let k=0; const shot=async n=>{k++; await pg.waitForTimeout(650); await pg.screenshot({path:`at/${sz}-${id}-${String(k).padStart(2,"0")}-${n}.png`,clip});};
  await shot("top");
  const H=await pg.evaluate(()=>{const vp=document.querySelector("#vp");return Math.ceil(vp.scrollHeight/(vp.clientHeight*.9));});
  for(let i=1;i<Math.min(H,16);i++){ await pg.evaluate(()=>{const vp=document.querySelector("#vp");vp.scrollBy(0,vp.clientHeight*.9);vp.dispatchEvent(new Event("scroll"))}); await shot("s"+i); }
  report[id]=await pg.evaluate(()=>{ const ga=document.querySelector("#vp"); const W=WORLDS[current];
    const broken=[...ga.querySelectorAll("img")].filter(i=>i.complete&&i.naturalWidth===0&&i.getClientRects().length).map(i=>i.getAttribute("src").slice(-40));
    const notStated=[...ga.querySelectorAll("#wtbl td")].filter(td=>/Not stated/.test(td.textContent)).length;
    const cells=[...ga.querySelectorAll("#wtbl td")].length;
    const over=[...ga.querySelectorAll("h1,h2,h3,p,b,span,a,button")].filter(e=>e.getClientRects().length&&e.scrollWidth>e.clientWidth+2&&getComputedStyle(e).overflow!=="visible"&&getComputedStyle(e).textOverflow!=="ellipsis").length;
    return {title:W.title,opts:W.opts.length,n:TRIPS.find(t=>t.id===current).n,pick:(W.opts.find(o=>o.pick)||{}).why||"-",steps:W.steps.length,noImgSteps:W.steps.filter(s=>!s.img&&!s.video).map(s=>s.t),faq:(W.faq||[]).length,stay:!!W.stay,broken,notStated:notStated+"/"+cells,clipped:over,chips:W.chips.join(" · "),sum:(W.sum||[]).join(" · ")}; });
  await pg.evaluate(()=>{const b=document.querySelector('#wchoose [data-wbook]'); b.scrollIntoView({block:"center"}); b.click();}); await shot("booked");
}
console.log(JSON.stringify(report,null,1)); console.log(sz,"errors:",errs.length?errs:"none"); await br.close();
