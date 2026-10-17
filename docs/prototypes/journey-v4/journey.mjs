// First-time foreign tourist, 8-10 days, wants pyramids + Luxor + maybe Nile/Red Sea. Real clicks, one shot per step.
import { chromium } from "playwright-core";
const sz=process.argv[2]||"desk";
const br=await chromium.launch({channel:"chrome"}); const errs=[];
const pg=await (await br.newContext({viewport:{width:1440,height:960}})).newPage(); pg.on("pageerror",e=>errs.push(e.message));
await pg.goto(new URL("journey-v4.html", import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(1200);
await pg.evaluate(()=>{const n=document.querySelector("#show-notes"); if(n.checked) n.click(); try{localStorage.clear()}catch(e){}});
if(sz==="phone"){ await pg.click('[data-dev="phone"]'); await pg.waitForTimeout(600); }
const clip=await pg.evaluate(()=>{const r=document.querySelector(".browser").getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:Math.min(r.height,900)}});
let k=0; const shot=async n=>{k++; await pg.waitForTimeout(800); await pg.screenshot({path:`jr/${sz}-${String(k).padStart(2,"0")}-${n}.png`,clip});};
const scroll=f=>pg.evaluate(f=>{const vp=document.querySelector("#vp");vp.style.scrollBehavior="auto";vp.scrollBy(0,vp.clientHeight*f);vp.dispatchEvent(new Event("scroll"));},f);
const top=()=>pg.evaluate(()=>{const vp=document.querySelector("#vp");vp.scrollTo(0,0);vp.dispatchEvent(new Event("scroll"))});
const vclick=async sel=>{ await pg.evaluate(s=>{const e=[...document.querySelectorAll(s)].find(x=>x.getClientRects().length); e&&e.scrollIntoView({block:"center"});},sel); await pg.waitForTimeout(300); await pg.click(sel); };
const step=async(n,f)=>{ try{ await f(); }catch(e){ errs.push(n+": "+e.message.split("\n")[0]); } };
// 1. landing and the whole home page
await shot("home"); for(let i=1;i<=9;i++){ await scroll(.9); await shot("home-s"+i); }
// 2. Trips & tours from the header
await top(); await step("trips",async()=>{ if(sz==="phone"){ await pg.click(".lc .nav__burger"); await pg.waitForTimeout(400); await shot("menu"); await pg.click('.lcm-links a[data-href*="experiences"]'); } else await pg.click('#vp .nav__links a[data-href*="experiences"]'); });
await shot("trips"); await scroll(.9); await shot("trips-s1"); await scroll(.9); await shot("trips-s2"); await scroll(.9); await shot("trips-s3");
// 3. Places
await step("places",async()=>{ if(sz==="phone"){ await pg.click(".lc .nav__burger"); await pg.waitForTimeout(400); await pg.click('.lcm-links a[data-href*="destinations"]'); } else await pg.click('#vp .nav__links a[data-href*="destinations"]'); });
await shot("places"); await scroll(.9); await shot("places-s1");
// 4. Trip Builder: Cairo + Luxor + Aswan, ancient
await step("planner",async()=>{ await pg.evaluate(()=>go(1)); await pg.waitForTimeout(600); await pg.evaluate(()=>{const p=document.querySelector("#live #planner");const vp=document.querySelector("#vp");vp.scrollTo(0,vp.scrollTop+p.getBoundingClientRect().top-vp.getBoundingClientRect().top-80)}); });
await shot("planner");
await step("planner-pick",async()=>{ for(const v of ["luxor","aswan"]) await pg.click(`#live #planner label:has(input[name=dest][value=${v}])`); await pg.click(`#live #planner label:has(input[name=interest][value=ancient])`); });
await shot("planner-picked"); await scroll(.6); await shot("planner-res"); await scroll(.6); await shot("planner-res2");
// 5. a trip from the builder: the pyramids
await step("pbcard",async()=>{ await vclick('.pb-card[data-pbtrip="giza"]'); });
await shot("giza"); for(let i=1;i<=6;i++){ await scroll(.9); await shot("giza-s"+i); }
await step("book",async()=>{ await vclick('#wchoose [data-wbook]'); }); await shot("after-book");
// 6. guides
await step("guides",async()=>{ if(sz==="phone"){ await pg.click(".lc .nav__burger"); await pg.waitForTimeout(400); await pg.click('.lcm-links a[data-href*="guides"]'); } else await pg.click('#vp .nav__links a[data-href*="/guides/"]'); });
await shot("guides"); await scroll(.9); await shot("guides-s1");
await step("guide",async()=>{ await pg.evaluate(()=>openGuide("best-time-to-visit-egypt")); }); await shot("guide"); await scroll(.9); await shot("guide-s1"); await scroll(2.5); await shot("guide-s2");
// 7. search
await step("search",async()=>{ await pg.click('.lc .nav [data-open="search"]'); await pg.waitForTimeout(400); await pg.keyboard.type("nile cruise"); }); await shot("search"); await pg.keyboard.press("Escape");
// 8. assistant
await step("assist",async()=>{ if(sz==="phone") await pg.click(".lc .nav .lc-ai"); else await pg.click(".lc .assistant-launch"); await pg.waitForTimeout(500); await shot("assist"); const i=await pg.$(".lc .assistant__form input"); await i.fill("Is Egypt safe? 8 days first time"); await pg.keyboard.press("Enter"); }); await shot("assist-ans"); await pg.keyboard.press("Escape");
// 9. footer
await step("footer",async()=>{ await pg.evaluate(()=>{const vp=document.querySelector("#vp");vp.scrollTo(0,vp.scrollHeight)}); }); await shot("footer");
console.log(sz,k,"shots; errors:",errs.length?errs:"none"); await br.close();
