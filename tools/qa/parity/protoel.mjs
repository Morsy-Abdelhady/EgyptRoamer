import { chromium } from "playwright-core"; import path from "path";
// screenshot one prototype element: args screen, selector, out, [width]
const [,,scr,sel,out,wd="1440"]=process.argv;
const br=await chromium.launch({channel:"chrome"}); const pg=await br.newPage({viewport:{width:+wd,height:900}});
await pg.goto("file:///"+path.resolve(process.env.PROTO || "journey-v4.html").split(path.sep).join("/")); await pg.waitForTimeout(2000);
await pg.evaluate(s=>{ if(s==="p") showPlaces(); else if(s==="g") openGuides(); else if(s.startsWith("ga:")) openGuide(s.slice(3)); else if(s.startsWith("w:")){ current=s.slice(2); go(3);} else go(+s); },scr); await pg.waitForTimeout(1500);
const el=await pg.$(sel); if(!el){console.log("no el");process.exit(1)} await el.scrollIntoViewIfNeeded(); await pg.waitForTimeout(1200); await el.screenshot({path:out}); await br.close();
