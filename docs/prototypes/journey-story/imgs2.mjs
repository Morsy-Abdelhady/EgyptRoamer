import fs from "node:fs";
// Approved homepage photos (prototype stand-ins / tagged free Unsplash), as used on egyptroamer.com today. No partner photos.
const base = JSON.parse(fs.readFileSync("imgs.json", "utf8"));
const more = {
  sc_pyr: ["1734461255961-6288a28a65f0", 1600, 860], sc_nile: ["1684100096410-fd39cdff91a3", 1600, 860],
  sc_desert: ["1771839534998-d5892d756064", 1600, 860], sc_sea: ["1777551881568-50b1beab7d91", 1600, 860],
  m_ancient: ["1761056981183-3faf66a62d6e", 1200, 760], m_beach: ["1722264222007-3e4f1808db3e", 1200, 760],
  m_luxury: ["1767790768726-60892377d4a4", 1200, 760], m_adventure: ["1771839535027-865f757ae09c", 1200, 760],
  m_culture: ["1710211288826-b7df3ab71588", 1200, 760], m_food: ["1661994215679-cde7c2c5c060", 1200, 760],
  m_nature: ["1753488819968-756a12153bae", 1200, 760],
  d_cairo: ["1679238211153-546821b75f98", 640, 820], d_alexandria: ["1633624646814-3a61ed3e2847", 640, 820],
  d_siwa: ["1771236469626-323bb7a34bac", 640, 820], d_luxor: ["1761056981183-3faf66a62d6e", 640, 820],
  d_aswan: ["1644517270263-4112379d97ca", 640, 820], d_hurghada: ["1722264222007-3e4f1808db3e", 640, 820],
  d_sharm: ["1681158077449-77f23f629f0d", 640, 820],
  kings_l: ["1689876021889-b3f6cfd41b95", 1100, 760], hat_l: ["1683891043389-31a5ca0692f8", 1100, 760],
};
for (const [k, [id, w, h]] of Object.entries(more)) {
  const r = await fetch(`https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&h=${h}&q=60&fm=jpg`);
  const b = Buffer.from(await r.arrayBuffer());
  base[k] = "data:image/jpeg;base64," + b.toString("base64");
  console.log(k, r.status, Math.round(b.length / 1024) + "KB");
}
fs.writeFileSync("imgs2.json", JSON.stringify(base));
console.log("total", Math.round(fs.statSync("imgs2.json").size / 1024) + "KB");
