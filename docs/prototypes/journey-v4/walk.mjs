// A tourist's path, by real clicks: land → look around → pick a place → compare → open a trip → read → book.
import { chromium } from "playwright-core";
const sz=process.argv[2];
const br=await chromium.launch({channel:"chrome",args:["--autoplay-policy=no-user-gesture-required"]}); const errs=[];
const pg=await (await br.newContext({viewport:{width:1440,height:1000}})).newPage(); pg.on("pageerror",e=>errs.push(e.message));
await pg.goto(new URL("journey-v4.html", import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(1000);
await pg.evaluate(()=>{const n=document.querySelector("#show-notes"); if(n.checked) n.click(); try{localStorage.clear()}catch(e){}});
if(sz==="phone") await pg.click('[data-dev="phone"]'); await pg.waitForTimeout(500);
const clip=await pg.evaluate(()=>{const r=document.querySelector(".browser").getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:Math.min(r.height,960)}});
let k=0; const shot=async n=>{k++; await pg.waitForTimeout(900); await pg.screenshot({path:`rv/w-${sz}-${String(k).padStart(2,"0")}-${n}.png`,clip});};
const scroll=f=>pg.evaluate(f=>{const vp=document.querySelector("#vp");vp.style.scrollBehavior="auto";vp.scrollBy(0,vp.clientHeight*f);vp.dispatchEvent(new Event("scroll"));},f);
const vclick=async sel=>{ await pg.evaluate(s=>{const e=[...document.querySelectorAll(s)].find(x=>x.getClientRects().length); e.scrollIntoView({block:"center"});},sel); await pg.waitForTimeout(300); await pg.click(sel); };
await shot("land");
await scroll(1); await shot("scroll1"); await scroll(1); await shot("scroll2"); await scroll(1); await shot("scroll3");
// the tourist wants Luxor: uses "Where in Egypt?"
await pg.evaluate(()=>document.querySelector("#vp").scrollTo(0,0)); await pg.waitForTimeout(300);
await vclick('.finder2 [data-place="Luxor"]'); await pg.waitForTimeout(800); await shot("luxor-list");
await scroll(.8); await shot("luxor-list2");
await vclick('#groups [data-trip="valley"]'); await pg.waitForTimeout(1200); await shot("trip-top");
await scroll(1); await shot("trip-2"); await scroll(1); await shot("trip-3"); await scroll(1); await shot("trip-4"); await scroll(1); await shot("trip-5");
await vclick('.wacts .btn--gold, #wcap .btn--gold').catch(()=>{}); await pg.waitForTimeout(900); await shot("book-section");
await scroll(.6); await shot("book-options");
await vclick('#wchoose [data-wbook]'); await pg.waitForTimeout(900); await shot("after-book");
console.log(sz,k,"shots; errors:",errs.length?errs:"none"); await br.close();
