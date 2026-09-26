// Export the static prototype's content (data.js + locale content overrides) to the
// Egypt Roamer Core seed file. Sample prices, ratings and review counts are dropped:
// they were placeholders and must never be published as facts.
//
//   node tools/export-seed.mjs
import fs from "fs";
import path from "path";
import { fileURLToPath, pathToFileURL } from "url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const src = path.join(root, "Egypt Roamer/assets/js");
const out = path.join(root, "wordpress/wp-content/plugins/egypt-roamer-core/data/seed.json");
const load = (f) => import(pathToFileURL(path.join(src, f)).href);

const data = await load("data.js");
const FAKE = new Set(["price", "rating", "reviews", "href", "unit"]);
// Badges that assert booking data we do not have (editorial badges like "Editor's pick" stay).
const CLAIM = /bestseller|sell out|most booked|top rated/i;
const clean = (o) =>
  Object.fromEntries(Object.entries(o).filter(([k, v]) => !FAKE.has(k) && !(k === "badge" && CLAIM.test(String(v)))));

const seed = {
  source: "Egypt Roamer static prototype (commit d7bef2b), assets/js/data.js",
  destinations: data.destinations.map(clean),
  moods: data.moods.map(({ recs, ...m }) => ({ ...m, dest: recs.dest })),
  experiences: data.experiences.map(clean),
  partnerCategories: data.partnerCategories.map(({ items, trust, ...c }) => ({ ...c, items: items.map(clean) })),
  guides: data.guides.map(({ href, ...g }) => ({ ...clean(g), slug: href.split("/").filter(Boolean).pop() })),
  film: data.film,
  routeOrder: data.routeOrder,
  nightWeights: data.nightWeights,
  legs: data.legs,
  translations: {},
};

for (const code of ["de", "fr", "it", "es", "ru", "zh", "ar"]) {
  const mod = await load(`locales/${code}.js`);
  const c = mod[code].content;
  seed.translations[code] = {
    destinations: c.destinations,
    moods: Object.fromEntries(Object.entries(c.moods || {}).map(([k, { recs, ...v }]) => [k, v])),
    experiences: Object.fromEntries(
      Object.entries(c.experiences || {}).map(([k, v]) => {
        const kept = clean(v);
        if (!seed.experiences.find((x) => x.id === k)?.badge) delete kept.badge; // claim dropped in English
        return [k, kept];
      })
    ),
    guides: c.guides,
    partners: Object.fromEntries(Object.entries(c.partners || {}).map(([k, { items, trust, ...v }]) => [k, v])),
    legs: c.legs,
  };
}
fs.mkdirSync(path.dirname(out), { recursive: true });
fs.writeFileSync(out, JSON.stringify(seed, null, 1) + "\n");
console.log("wrote", path.relative(root, out), fs.statSync(out).size, "bytes");
