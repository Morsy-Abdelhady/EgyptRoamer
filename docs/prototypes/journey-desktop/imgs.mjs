import fs from "node:fs";
// Approved photos only (prototype Unsplash stand-ins / tagged free Unsplash photos already on the site). No Viator photos.
const ids = {
  hero: ["1734461255961-6288a28a65f0", 1600, 900],
  trip: ["1623417765161-c71d6c83cfee", 1600, 820],
  ras: ["1778400283418-b419be4be7a5", 560, 420], alex: ["1633624646814-3a61ed3e2847", 560, 420],
  giza: ["1678038592327-c5730737f867", 560, 420], nile: ["1761421852464-463fb31f2dc0", 560, 420],
  abu: ["1633163893862-4cdc62de7d82", 560, 420], white: ["1691591403777-128b9a842b8b", 560, 420],
  food: ["1661994215679-cde7c2c5c060", 560, 420], valley: ["1623417765161-c71d6c83cfee", 560, 420],
  siwa: ["1771839535027-865f757ae09c", 560, 420],
  kings: ["1689876021889-b3f6cfd41b95", 480, 300], hatshepsut: ["1683891043389-31a5ca0692f8", 480, 300],
  gyg: ["1695178234643-9656316a0e66", 200, 200], hotel: ["1761056981183-3faf66a62d6e", 200, 200],
};
const out = {};
for (const [k, [id, w, h]] of Object.entries(ids)) {
  const r = await fetch(`https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&h=${h}&q=62&fm=jpg`);
  const b = Buffer.from(await r.arrayBuffer());
  out[k] = "data:image/jpeg;base64," + b.toString("base64");
  console.log(k, r.status, Math.round(b.length / 1024) + "KB");
}
fs.writeFileSync("imgs.json", JSON.stringify(out));
