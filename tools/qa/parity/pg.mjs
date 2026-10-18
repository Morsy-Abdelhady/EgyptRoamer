import { chromium } from "playwright-core";
const [,,url,tag,wd="1440"]=process.argv; const w=+wd,h=w<600?844:900;
const br=await chromium.launch({channel:"chrome"}); const ctx=await br.newContext({viewport:{width:w,height:h},isMobile:w<600,hasTouch:w<600}); const pg=await ctx.newPage(); const errs=[];
pg.on("pageerror",e=>errs.push(e.message));
for(let k=0;k<4;k++){ try{ await pg.goto(url,{waitUntil:"load"}); break; }catch(e){ if(k===3) throw e; } } await pg.waitForTimeout(2500);
const H=await pg.evaluate(()=>document.documentElement.scrollHeight); console.log("H",H,(H/h).toFixed(1),"sw",await pg.evaluate(()=>document.documentElement.scrollWidth));
for(let y=0,k=0;y<H&&k<24;y+=h*0.95,k++){await pg.evaluate(y=>scrollTo(0,y),y);await pg.waitForTimeout(1400);await pg.screenshot({path:`ph/${tag}-${String(k).padStart(2,"0")}.png`});}
console.log("errors",errs); await br.close();
