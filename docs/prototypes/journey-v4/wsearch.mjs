// Search Wikimedia Commons for photos of exact places; keep free licences, landscape, large.
import fs from "fs";
const Q={
 colossi:"Colossi of Memnon",
 blackdesert:"Black Desert Egypt",
 crystal:"Crystal Mountain Farafra",
 agabat:"Agabat White Desert",
 whitedesert:"White Desert Farafra",
 sphinx:"Great Sphinx of Giza",
 abu_inside:"Abu Simbel interior hall Osiride",
 abu_move:"Abu Simbel relocation",
 shoqafa:"Catacombs of Kom el Shoqafa",
 montaza:"Montaza Palace Alexandria",
 komombo:"Kom Ombo temple",
 siwa_dunes:"Great Sand Sea",
 siwa_spring:"Siwa spring",
 siwa_sunset:"Siwa sunset",
 hurg_marina:"Hurghada marina",
 ras_road:"Ras Muhammad",
 abu_road:"Abu Simbel road",
 belly:"belly dance Cairo",
 siwa_board:"sandboarding Egypt",
};
const api="https://commons.wikimedia.org/w/api.php?";
const out=fs.existsSync("wcand.json")?JSON.parse(fs.readFileSync("wcand.json","utf8")):{};
const ONLY=process.argv.slice(2);
for(const [k,q] of Object.entries(Q)){ if(ONLY.length&&!ONLY.includes(k)) continue; await new Promise(r=>setTimeout(r,1500));
  const u=api+new URLSearchParams({action:"query",format:"json",generator:"search",gsrsearch:q+" filetype:bitmap",gsrnamespace:"6",gsrlimit:"25",prop:"imageinfo",iiprop:"url|size|extmetadata",iiurlwidth:"1600",origin:"*"});
  let r={}; for(let t=0;t<4;t++){ const res=await fetch(u,{headers:{"User-Agent":"EgyptRoamerPrototype/1.0 (owner review)"}}); const txt=await res.text(); try{ r=JSON.parse(txt); if(r.query) break; console.log(k,"no query",res.status,txt.slice(0,120)); }catch(e){ console.log(k,"bad",res.status,txt.slice(0,120)); } await new Promise(z=>setTimeout(z,6000)); }
  const pages=Object.values(r.query?.pages||{}).sort((a,b)=>a.index-b.index);
  out[k]=pages.map(p=>{const i=p.imageinfo?.[0]; if(!i) return null; const m=i.extmetadata||{}; const lic=(m.LicenseShortName?.value||""); const artist=(m.Artist?.value||"").replace(/<[^>]+>/g,"").trim();
    return {title:p.title,w:i.width,h:i.height,lic,artist,thumb:i.thumburl,page:i.descriptionurl,desc:(m.ImageDescription?.value||"").replace(/<[^>]+>/g,"").slice(0,120)};})
   .filter(x=>x&&x.w>=1400&&x.w>x.h*1.15&&/^(CC BY|CC BY-SA|CC0|Public domain|PD)/i.test(x.lic)&&!/NC|ND/.test(x.lic)).slice(0,6);
  console.log(k, out[k].length);
}
fs.writeFileSync("wcand.json",JSON.stringify(out,null,1));
