import fs from "node:fs";
// Free Unsplash photos whose location tag names the exact place (read on each photo page, 16 Oct).
const want = {
  habu1:["1622962522220-0d9e0d29d62d",900,640], habu2:["1762858279425-ed77c08d7a1c",900,640], habu3:["1761143551108-d4cb55afd1da",900,640],
  reef_school:["1777551881568-50b1beab7d91",700,520], reef_clown2:["1777552955794-77aa990a47df",700,520], reef_yellow:["1777552771426-9ac9f515dc40",700,520],
  reef_clown:["1777552157135-f1e4b65865d5",700,520], reef_puffer:["1777552995899-b0c030a3ef92",700,520], reef_coral:["1777551747256-0bb44c8086fc",700,520],
};
const out = {};
for (const [k,[id,w,h]] of Object.entries(want)) {
  const r = await fetch(`https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&h=${h}&q=62&fm=jpg`);
  const b = Buffer.from(await r.arrayBuffer()); out[k] = "data:image/jpeg;base64," + b.toString("base64");
  console.log(k, r.status, Math.round(b.length/1024)+"KB");
}
fs.writeFileSync("imgs-art.json", JSON.stringify(out));
fs.writeFileSync("sheet-art.html", "<body style='margin:0;display:grid;grid-template-columns:repeat(3,1fr);gap:6px;background:#222;color:#fff;font:14px sans-serif'>" + Object.entries(out).map(([k,v])=>`<div><img src="${v}" style="width:100%"><br>${k}</div>`).join("") + "</body>");
