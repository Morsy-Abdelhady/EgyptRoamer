import { chromium } from "playwright-core";
const [,,url,sel,out,wd="1440"]=process.argv; const w=+wd;
const br=await chromium.launch({channel:"chrome"}); const ctx=await br.newContext({viewport:{width:w,height:w<600?844:900},isMobile:w<600,hasTouch:w<600}); const pg=await ctx.newPage();
for(let k=0;k<4;k++){ try{ await pg.goto(url,{waitUntil:"domcontentloaded",timeout:30000}); break; }catch(e){ if(k===3) throw e; } }
await pg.waitForTimeout(2500); const el=await pg.$(sel); if(!el){console.log("no el");process.exit(1)} await el.scrollIntoViewIfNeeded(); await pg.waitForTimeout(1500); await el.screenshot({path:out}); await br.close();
