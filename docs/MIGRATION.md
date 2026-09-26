# Migration: static prototype → WordPress

## 1. Redirect map

As far as the repository shows, no URL of the prototype was ever served publicly. **Whether an older site exists on the production domain is NOT VERIFIED.** If one exists, crawl it before launch and add its URLs to this table as one-hop 301s.

| Old URL (prototype) | New URL | Status | Redirect | Canonical | Indexability | Notes |
|---|---|---|---|---|---|---|
| `/` , `/index.html` | `/` | live | `/index.html` → `/` **301** (Core) | self | index | |
| `/?lang=fr` (etc.) | `/fr/` | live when FR is published | **301** by Polylang (`/?lang=fr` → `/fr/`, verified) | `/fr/` | — | JS language switching replaced by real URLs |
| `#destinations` `#experiences` `#partners` `#guide` `#planner` | same anchors on `/` + `/destinations/`, `/experiences/`, `/guides/` | live | — | — | — | menus now point to archives |
| `/guide` | `/guides/` | live | **301** (Core, one hop) | self | index when not empty | |
| `/guide/best-time-to-visit-egypt` | `/guides/best-time-to-visit-egypt/` | **draft** | **301** to the guide if published, else to `/guides/` | self | noindex until "Ready to index" | title only in prototype |
| `/guide/7-days-in-egypt-itinerary` | `/guides/7-days-in-egypt-itinerary/` | draft | 301 (same rule) | self | noindex until ready | |
| `/guide/is-egypt-safe-for-tourists` | `/guides/is-egypt-safe-for-tourists/` | draft | 301 | self | noindex until ready | |
| `/guide/best-nile-cruises` | `/guides/best-nile-cruises/` | draft | 301 | self | noindex until ready | |
| `/guide/cairo-travel-guide` | `/guides/cairo-travel-guide/` | draft | 301 | self | noindex until ready | see cannibalisation note in CONTENT-PLAN |
| `/guide/egypt-travel-costs` | `/guides/egypt-travel-costs/` | draft | 301 | self | noindex until ready | |
| `/guide/hidden-gems-in-egypt` | `/guides/hidden-gems-in-egypt/` | draft | 301 | self | noindex until ready | |
| `/guide/egypt-visa-and-entry` | `/guides/egypt-visa-and-entry/` | not created | 301 → `/guides/` until written | — | — | linked in prototype footer, no teaser existed |
| `/about` | `/about/` | draft | WordPress adds the slash (301) | self | index when published | |
| `/how-we-choose`, `/partner-with-us`, `/contact`, `/affiliate-disclosure`, `/privacy`, `/terms`, `/cookies` | `/how-we-choose/`, `/partner-with-us/`, `/contact/`, `/affiliate-disclosure/`, `/privacy-policy/`, `/terms/`, `/cookies/` | draft | trailing-slash 301 by WordPress; `/privacy` → `/privacy-policy/` **add in Rank Math → Redirections** | self | index when published | legal copy must be written/reviewed |
| `/styleguide.html` | — | not migrated | none (404) | — | — | internal design page |
| `/qa-shots/*` | — | not migrated | none | — | — | dev artefacts |

Every Core redirect is one hop; nothing chains. Verified locally: `/guide/best-time-to-visit-egypt` → `301` → `/guides/`.

## 2. Content migration

`wp egypt-roamer seed [--with-images] [--translations]` is idempotent. Its source is `data/seed.json`, exported from the prototype by `tools/export-seed.mjs`.

| Prototype source | WordPress | Status after seed |
|---|---|---|
| 7 destinations (`data.js`) | Destination CPT (+ tagline, region, best time, reach, highlights, coords, nights weight) | published, **noindex** (one-paragraph copy) |
| 7 moods | Travel styles (word, icon, tint, image, destination) | created |
| 8 featured experiences | Experience CPT (title, location, duration, destination, editorial badge only) | published, **noindex**, no offer attached |
| 5 partner categories | Offer categories (icon, headline, copy, compare label) | created; *trust bullets not migrated* (unsupported claims) |
| 15 partner shortlist items | Affiliate Offers | **draft, paused, no provider, no URL**; partner named in the prototype kept as a private note only |
| 7 guide teasers | Guide CPT + topic | **draft** (titles only) |
| Film slides, travel legs | homepage payload / seed data | used as-is (editorial copy) |
| Locale content (7 languages) | Polylang translations of destinations, experiences, styles, categories | **draft** until reviewed (`--translations`) |
| Sample prices, ratings, review counts, "Bestseller", "Likely to sell out", "Most booked", "Top rated", "Live prices…", "No hidden mark-ups", "Tested by our editors, rated by thousands…" | — | **not migrated** (fabricated / unsupported) |
| Unsplash photos | Media Library (`--with-images`, descriptive filenames + alt text) | NOT VERIFIED here (Unsplash blocked in this sandbox) |
| Homepage section copy | Appearance → Homepage (defaults = approved copy, translated) | editable |

## 3. Indexation matrix (seeded state, verified on the local install)

| URL | Type | Language | Canonical | Index | Sitemap | Primary intent | Status |
|---|---|---|---|---|---|---|---|
| `/` | home | en | `/` | index | yes | brand / "Egypt travel" discovery | published |
| `/fr/`, `/ar/` | home | fr, ar | self | index | yes | same, per language | published (structural) |
| `/destinations/` | archive | en | self | index | yes | "Egypt destinations" | published |
| `/destinations/cairo/` | destination | en | self | **noindex** until ticked | no until ticked | "Cairo travel" hub | published (short copy) |
| `/fr/destinations/le-caire/` | destination | fr | self | index (ticked in test) | yes when ticked | "Le Caire voyage" | test only — seeded translations are drafts |
| `/experiences/{slug}/` | experience | en | self | noindex until ticked | no | commercial-investigational | published (short) |
| `/tours/`, `/activities/` | archive | en | self | **noindex** while empty | no | — | empty |
| `/guides/`, `/journal/` | archive | en | self | **noindex** while empty | no | — | empty |
| `/guides/{slug}/` | guide | en | self | — | no | informational | draft |
| `/experiences/?destination=…&style=…` | filtered archive | any | archive | **noindex** | no | — | filter |
| `/?s=…` | search | any | — | **noindex** | no | — | utility |
| `/go/{slug}/` | redirect | — | — | `X-Robots-Tag: noindex` + robots.txt `Disallow` | no | — | endpoint |
| `/about/`, `/contact/`, `/affiliate-disclosure/`, legal | page | en | self | index once published | yes once published | trust | draft |
| offers, providers, messages | private | — | — | not publicly queryable | no | — | admin only |

Verified locally without Rank Math (core sitemap + Core guards): the home page is `index`; Cairo is `noindex` until ticked; search and filtered archives are `noindex`; empty archives are `noindex`. **With Rank Math, the same rules apply through `rank_math/frontend/robots` and `rank_math/sitemap/entry`, but that is NOT VERIFIED here (Rank Math not installable in this sandbox).**
