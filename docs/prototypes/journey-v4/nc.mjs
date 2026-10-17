import { chromium } from "playwright-core";
const sz=process.argv[2]||"desk";
const br=await chromium.launch({channel:"chrome"}); const errs=[];
const pg=await (await br.newContext({viewport:{width:1440,height:960}})).newPage(); pg.on("pageerror",e=>errs.push(e.message)); pg.on("console",m=>{ if(m.type()==="error") errs.push("console: "+m.text()); });
await pg.goto(new URL("journey-v4.html", import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(1200);
await pg.evaluate(()=>{const n=document.querySelector("#show-notes"); if(n.checked) n.click(); try{localStorage.clear()}catch(e){}});
if(sz==="phone"){ await pg.click('[data-dev="phone"]'); await pg.waitForTimeout(600); }
const clip=await pg.evaluate(()=>{const r=document.querySelector(".browser").getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:Math.min(r.height,900)}});
let k=0; const shot=async n=>{k++; await pg.waitForTimeout(700); await pg.screenshot({path:`jr/nc-${sz}-${String(k).padStart(2,"0")}-${n}.png`,clip});};
const out={};
// home: Nile chapter
await pg.evaluate(()=>{const vp=document.querySelector("#vp"); const c=document.getElementById("ch-2"); vp.scrollTo(0,vp.scrollTop+c.getBoundingClientRect().top-vp.getBoundingClientRect().top+c.offsetHeight-vp.clientHeight); vp.dispatchEvent(new Event("scroll"))}); await shot("chapter");
out.chapter=await pg.evaluate(()=>[...document.querySelectorAll("#ch-2 .ch__trips b, #ch-2 .ch__trips strong")].map(e=>e.textContent.trim()).join(" | "));
// trips list, Aswan and Luxor, 2+ days
await pg.evaluate(()=>go(2)); await pg.waitForTimeout(700);
out.all=await pg.evaluate(()=>document.querySelector("#groups").querySelectorAll(".card").length+" cards; count: "+(document.querySelector(".count, #count")?.textContent||""));
out.cruiseCards=await pg.evaluate(()=>document.querySelectorAll('#groups [data-trip="cruise"]').length);
await pg.evaluate(()=>lcPlace("Aswan")); await pg.waitForTimeout(600); out.aswan=await pg.evaluate(()=>[...document.querySelectorAll("#groups .card h3, #groups .card b")].map(e=>e.textContent.trim()).filter(Boolean).slice(0,6).join(" | ")); await shot("aswan");
await pg.evaluate(()=>lcPlace("Luxor")); await pg.waitForTimeout(600); out.luxor=await pg.evaluate(()=>[...document.querySelectorAll("#groups .card h3")].map(e=>e.textContent.trim()).join(" | "));
// the trip
await pg.evaluate(()=>{ current="cruise"; lastViewed="cruise"; go(3); }); await pg.waitForTimeout(1200); out.url=await pg.textContent("#url");
await shot("top"); for(let i=1;i<=7;i++){ await pg.evaluate(()=>{const vp=document.querySelector("#vp");vp.scrollBy(0,vp.clientHeight*.9);vp.dispatchEvent(new Event("scroll"))}); await shot("s"+i); }
await pg.evaluate(()=>{ const b=document.getElementById("wcmp"); b&&b.click(); }); await pg.evaluate(()=>{const t=document.getElementById("wtbl"); t&&t.scrollIntoView({block:"center"})}); await shot("compare");
await pg.evaluate(()=>{const b=document.querySelector('#wchoose [data-wbook]'); b.scrollIntoView({block:"center"}); b.click();}); await shot("booked");
// search, assistant, builder, guide
await pg.click('.lc .nav [data-open="search"]'); await pg.waitForTimeout(300); await pg.keyboard.type("nile cruise"); await pg.waitForTimeout(300); out.search=await pg.evaluate(()=>[...document.querySelectorAll(".lcs-trips .search__item b")].map(e=>e.textContent).slice(0,3).join(" | ")); await shot("search"); await pg.keyboard.press("Escape");
out.builder=await pg.evaluate(()=>{ go(1); return new Promise(r=>setTimeout(()=>{ const p=document.querySelector("#live #planner"); p.querySelector("[data-b-days]").value=8; p.querySelectorAll("input[name=dest]").forEach(c=>c.checked=["cairo","luxor","aswan"].includes(c.value)); p.querySelectorAll("input[name=interest]").forEach(c=>c.checked=c.value==="ancient"); p.querySelector("[data-builder]").dispatchEvent(new Event("change",{bubbles:true})); setTimeout(()=>r([...p.querySelectorAll(".pb-card b")].map(b=>b.textContent).join(" | ")),300); },600)); });
await pg.evaluate(()=>openGuide("nile-cruise-or-hotels-luxor-aswan")); await pg.waitForTimeout(600); out.guide=await pg.evaluate(()=>document.querySelector("#ga .bk small")?.textContent+" / "+document.querySelector("#ga .bk h3")?.textContent); await shot("guide");
console.log(JSON.stringify(out,null,1)); console.log(sz,k,"shots; errors:",errs.length?errs:"none"); await br.close();
