// full-size tour of every screen: node tour.mjs <desk|tab|phone>
import { chromium } from "playwright-core";
const sz=process.argv[2];
const br=await chromium.launch({channel:"chrome",args:["--autoplay-policy=no-user-gesture-required"]}); const errs=[];
async function open(file){ const pg=await (await br.newContext({viewport:{width:1440,height:1000}})).newPage(); pg.on("pageerror",e=>errs.push(file+": "+e.message));
  await pg.goto(new URL(file, import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(800);
  await pg.evaluate(()=>{const n=document.querySelector("#show-notes"); if(n.checked) n.click(); try{localStorage.clear()}catch(e){}});
  if(sz==="phone") await pg.click('[data-dev="phone"]');
  if(sz==="tab") await pg.evaluate(()=>{const b=document.querySelector(".browser");b.style.width="840px";b.style.margin="0 auto";document.querySelector("#vp").style.height="900px";});
  await pg.waitForTimeout(500); pg.clip=await pg.evaluate(()=>{const r=document.querySelector(".browser").getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:Math.min(r.height,960)}}); return pg; }
let k=0;
const shot=async(pg,pre,name)=>{ k++; await pg.waitForTimeout(900); await pg.screenshot({path:`rv/t-${sz}-${String(k).padStart(2,"0")}-${pre}-${name}.png`,clip:pg.clip}); };
const to=(pg,sel,off=0)=>pg.evaluate(([s,o])=>{const vp=document.querySelector("#vp");vp.style.scrollBehavior="auto";const el=[...document.querySelectorAll(s)].find(e=>e.getClientRects().length);if(!el)return false;vp.scrollTo({top:vp.scrollTop+el.getBoundingClientRect().top-vp.getBoundingClientRect().top+o});vp.dispatchEvent(new Event("scroll"));return true;},[sel,off]);
const page=(pg,f)=>pg.evaluate((f)=>{const vp=document.querySelector("#vp");vp.style.scrollBehavior="auto";vp.scrollBy(0,vp.clientHeight*f);vp.dispatchEvent(new Event("scroll"));},f);
let pg=await open("journey-v4.html");
await shot(pg,"v4","home-top");
for(const [s,n,o] of [["#ch-1","ch1",-56],["#ch-1 .ch__trips","ch1-trips",-300],["#ch-2","ch2",-56],[".interlude2","interlude",-56],["#live #moods","moods",-56],["#live .moods__body","moods-body",-90],["#live #map","map",-56],["#live .map__canvas","map-canvas",-90]]) { await to(pg,s,o); await shot(pg,"v4",n); }
await pg.evaluate(()=>{const vp=document.querySelector("#vp");vp.scrollTo(0,vp.scrollHeight)}); await shot(pg,"v4","footer");
await pg.evaluate(()=>go(2)); await shot(pg,"v4","browse-top"); await to(pg,"#groups",-120); await shot(pg,"v4","browse-groups"); await page(pg,.85); await shot(pg,"v4","browse-more");
await pg.evaluate(()=>showPlaces()); await shot(pg,"v4","places-top");
await pg.evaluate(()=>{current="valley";go(3)}); await pg.waitForTimeout(800); await shot(pg,"v4","trip-open");
for(const [s,n,o] of [['.jstep[data-js="0"]',"trip-step1",0],['.jstep[data-js="2"]',"trip-step3",0],[".wsec","trip-sec",-56],[".papy","trip-papy",-56],[".wfaq","trip-faq",-56],["#wchoose","trip-choose",-56],["#wchoose .wopt","trip-opts",-140],[".wmore","trip-more",-56]]) { await to(pg,s,o); await shot(pg,"v4",n); }
await pg.close();
pg=await open("article/journey-article.html");
await shot(pg,"ar","hero");
for(const [s,n,o] of [[".prose","body",-140],[".strip","strip",-240],[".tcard","tcard",-280],[".tip","tip",-200],[".afaq","faq",-140],[".abox","box",-140],[".aend","end",-56],[".amore","more",-56]]) { await to(pg,s,o); await shot(pg,"ar",n); }
await pg.click('[data-art="snorkel"]'); await pg.waitForTimeout(900); await shot(pg,"ar","snork-hero"); await to(pg,".ivid",-200); await shot(pg,"ar","snork-video");
await pg.close();
console.log(k,"shots; errors:",errs.length?errs:"none"); await br.close();
