import { chromium } from "playwright-core"; import path from "path";
// args: protoScreen siteUrl tag sel1=sel1site sel2=... ; captures each pair at the prototype frame's width
const [,,scr,url,tag,...pairs]=process.argv;
const br=await chromium.launch({channel:"chrome"});
const pp=await br.newPage({viewport:{width:1440,height:900}});
await pp.goto("file:///"+path.resolve(process.env.PROTO || "journey-v4.html").split(path.sep).join("/")); await pp.waitForTimeout(2000);
await pp.evaluate(s=>{ if(s==="p") showPlaces(); else if(s==="g") openGuides(); else if(s.startsWith("ga:")) openGuide(s.slice(3)); else if(s.startsWith("w:")){ current=s.slice(2); go(3);} else go(+s); },scr); await pp.waitForTimeout(1500);
const vw=await pp.evaluate(()=>document.getElementById("vp").clientWidth);
const sp=await br.newPage({viewport:{width:vw,height:900}});
for(let k=0;k<4;k++){ try{ await sp.goto(url,{waitUntil:"domcontentloaded",timeout:30000}); break; }catch(e){ if(k===3) throw e; } }
await sp.waitForTimeout(2500);
for(const [i,p] of pairs.entries()){ const [a,b]=p.split("=");
  for(const [pg,s,side] of [[pp,a,"p"],[sp,b,"s"]]){ const el=await pg.$(s); if(!el){console.log("missing",side,s);continue;} await el.scrollIntoViewIfNeeded(); await pg.waitForTimeout(1300); await el.screenshot({path:`ph/pr-${tag}-${i}-${side}.png`}).catch(e=>console.log("shot fail",side,s,e.message.slice(0,80))); } }
console.log("vp width",vw); await br.close();
