// one screen, full size: node shot.mjs <file> <desk|tab|phone> <name> <js-to-run-before> [scrollSelector] [offset]
import { chromium } from "playwright-core";
const [file,sz,name,pre,sel,off]=process.argv.slice(2);
const br=await chromium.launch({channel:"chrome",args:["--autoplay-policy=no-user-gesture-required"]}); const errs=[];
const pg=await (await br.newContext({viewport:{width:1440,height:1000}})).newPage(); pg.on("pageerror",e=>errs.push(e.message));
await pg.goto(new URL(file, import.meta.url).href,{waitUntil:"load"}); await pg.waitForTimeout(800);
await pg.evaluate(()=>{const n=document.querySelector("#show-notes"); if(n.checked) n.click(); try{localStorage.clear()}catch(e){}});
if(sz==="phone") await pg.click('[data-dev="phone"]');
if(sz==="tab") await pg.evaluate(()=>{const b=document.querySelector(".browser");b.style.width="840px";b.style.margin="0 auto";document.querySelector("#vp").style.height="900px";});
await pg.waitForTimeout(400);
if(pre) { await pg.evaluate(pre); await pg.waitForTimeout(1200); }
if(sel) { await pg.evaluate(([s,o])=>{const vp=document.querySelector("#vp");vp.style.scrollBehavior="auto";const el=[...document.querySelectorAll(s)].find(e=>e.getClientRects().length);vp.scrollTo({top:vp.scrollTop+el.getBoundingClientRect().top-vp.getBoundingClientRect().top+(+o||0)});vp.dispatchEvent(new Event("scroll"));},[sel,off]); await pg.waitForTimeout(1000); }
const clip=await pg.evaluate(()=>{const r=document.querySelector(".browser").getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:Math.min(r.height,960)}});
await pg.screenshot({path:`rv/s-${name}.png`,clip}); console.log(name, errs.length?errs:"ok"); await br.close();
