import fs from "node:fs";
// Approved photos only: each trip's own photo on egyptroamer.com, plus the Valley page's card photos. No partner photos.
const v2 = JSON.parse(fs.readFileSync("imgs2.json", "utf8"));
const keep = ["trip","kings_l","hat_l","gyg","hotel","valley"];
const out = Object.fromEntries(keep.map(k => [k, v2[k]]));
const more = { sea_l:["1778400283418-b419be4be7a5",1600,900], desert_l:["1691591403777-128b9a842b8b",1600,900], nile_l:["1761421852464-463fb31f2dc0",1600,900] };
for (const [k,[id,w,h]] of Object.entries(more)) {
  const r = await fetch(`https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&h=${h}&q=62&fm=jpg`);
  const b = Buffer.from(await r.arrayBuffer()); out[k] = "data:image/jpeg;base64," + b.toString("base64");
  console.log(k, r.status, Math.round(b.length/1024)+"KB");
}
fs.writeFileSync("imgs3.json", JSON.stringify(out));
console.log("total", Math.round(fs.statSync("imgs3.json").size/1024)+"KB");
