// every screen: gap between the first content block and the sticky bar/header, and between the last block and the footer
import { chromium } from "playwright-core";
const br=await chromium.launch({channel:"chrome"});
for(const sz of ["desk","phone"]){ const pg=await (await br.newContext({viewport:{width:1440,height:1000}})).newPage();
await pg.goto(new URL("journey-v4.html", import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(1200);
if(sz==="phone") await pg.click('[data-dev="phone"]'); await pg.waitForTimeout(400);
const out=[];
for(const [name,js] of [["home","go(1)"],["browse","go(2)"],["places","showPlaces()"],["guides","openGuides()"],["guide","openGuide('valley-of-the-kings-tombs-and-tickets')"],["route","openGuide('7-days-in-egypt-itinerary')"],["trip","current='valley';go(3)"],["contact","lcRoute('https://egyptroamer.com/contact/')"]]){
  await pg.evaluate(js); await pg.waitForTimeout(500);
  out.push(name+": "+await pg.evaluate(()=>{ const s=[...document.querySelectorAll("#vp [data-screen]")].find(x=>!x.hidden); const f=document.querySelector("#vp footer.footer"); 
    const blocks=[...s.querySelectorAll("*")].filter(e=>e.children.length===0&&e.getBoundingClientRect().height>0&&getComputedStyle(e).visibility!=="hidden"); const last=blocks.reduce((m,e)=>e.getBoundingClientRect().bottom>m?e.getBoundingClientRect().bottom:m,0);
    return "last content → footer: "+Math.round(f.getBoundingClientRect().top-last)+"px"; }));
}
console.log(sz, out.join(" | ")); await pg.close(); }
await br.close();
