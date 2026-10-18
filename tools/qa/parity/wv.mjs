import { chromium } from "playwright-core";
const slug=process.argv[2]; const br=await chromium.launch({channel:"chrome"}); const pg=await br.newPage({viewport:{width:1440,height:900}}); const errs=[]; pg.on("pageerror",e=>errs.push(e.message));
for(let k=0;k<4;k++){ try{ await pg.goto("http://127.0.0.1:8080/experiences/"+slug+"/?ux=3",{waitUntil:"domcontentloaded",timeout:30000}); break; }catch(e){ if(k===3) throw e; } }
await pg.waitForTimeout(3000);
const st=()=>pg.evaluate(()=>[...document.querySelectorAll(".world video")].map(v=>(v.getAttribute("data-src")||"").split("/").pop().split(".")[0]+":"+(v.paused?"P":"PLAY")+(v.className.includes("on")?"(on)":"")).join(" "));
const out=["top "+await st()];
const steps=await pg.$$(".jstep[data-js]");
for(const s of steps){ await s.scrollIntoViewIfNeeded(); await pg.evaluate(()=>scrollBy(0,0)); await pg.waitForTimeout(2200); out.push("step "+await st()); }
const pv=await pg.$(".papy__vid"); if(pv){ await pv.scrollIntoViewIfNeeded(); await pg.waitForTimeout(2500); out.push("papy "+await st()); await pg.screenshot({path:`ph/wv-${slug.slice(0,6)}-papy.png`}); }
// pause test on the opener
await pg.evaluate(()=>scrollTo(0,0)); await pg.waitForTimeout(1500);
const b=await pg.$(".wopen [data-vplay]"); if(b){ await b.click(); await pg.waitForTimeout(600); out.push("paused-click "+await st()+" label="+await b.getAttribute("aria-label")); }
console.log(out.join("\n")); console.log(errs); await br.close();
