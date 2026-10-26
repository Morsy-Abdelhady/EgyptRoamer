# SEO recovery changelog

Every search-recovery action, with what it is expected to do and how to check it.

## 2026-10-15

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Started "Validate fix" for "Excluded by 'noindex' tag" (142 URLs, all last crawled 29 Sep–1 Oct, before indexing opened on 2 Oct) | Search Console | "Validation started 10/15/26" | Google recrawls them and indexes about 135. The 7 non-English guides archives stay noindex by design | 22 Oct, 29 Oct |
| Requested indexing: `/ar/`, `/destinations/luxor/`, `/destinations/aswan/`, `/experiences/abu-simbel-day-trip-from-aswan/` | Search Console | "Indexing requested" for each | Priority crawl | 22 Oct |
| IndexNow in Core 1.2.30: all 181 indexable URLs sent once, then each change | Core `includes/indexnow.php`, commit `a98ea05` | Dashboard "181 URLs sent… answer 202"; key file verified | Bing (and Yandex, Seznam, Naver) discover and index the site | `site:egyptroamer.com` on Bing, from 22 Oct |
| `data-nosnippet` on the footer, menus, overlays, assistant and dock labels | theme 1.2.44 `footer.php` | 12 occurrences in production HTML | Snippets come from page content or the meta description, not "Trip assistant…" or "© 2026…" | The brand search on Google after the recrawl |

| 56 new pages (4 guides, 2 experiences, Abu Simbel guide translations; 8 languages) sent by IndexNow automatically on publication | Core `indexnow.php` | Core status: "IndexNow: 56 URLs sent on 2026-10-15 15:57, answer 200"; all in `wp-sitemap.xml` (272 URLs) | Bing, Yandex, Seznam and Naver discover them | Bing Site Explorer, 22 Oct |
| Requested indexing in Google: Abu Simbel guide (AR, EN) and Day trips from Cairo guide (AR, EN) | Search Console URL Inspection | "Indexing requested" ×4; all four were "URL is unknown to Google" before | Priority crawl of the new guides that target our first queries ("اسوان") | 22 Oct |
| Bing URL Submission: used the last slot of the day for the Arabic Abu Simbel guide (99 URLs submitted earlier today) | Bing WMT | "URL submitted successfully"; quota resets in 11 h | Recrawl | 18 Oct |
| Viator dashboard, last 30 days: visitors 0, bookings 0, commission $0. Payout details not entered yet (owner action) | partners.viator.com | Read 15 Oct | Data lags; real traffic only from 15 Oct | 17 Oct |
| Second batch: 40 new guide pages (5 guides × 8 languages) sent by IndexNow on publication | Core `indexnow.php` | "IndexNow: 40 URLs sent on 2026-10-15 18:47, answer 200" | Bing and the other IndexNow engines discover them | Bing Site Explorer, 18 Oct |
| Third batch: 40 new guide pages (Grand Egyptian Museum, inside the pyramids, Karnak and Luxor Temple, Great Sand Sea, 14 days; 8 languages) sent by IndexNow on publication | Core `indexnow.php` | "IndexNow: 40 URLs sent on 2026-10-15 19:55, answer 200" | Bing and the other IndexNow engines discover them | 18 Oct; Google requests in the 16 Oct task (list updated: GEM guide first) |
| Fourth batch: 40 new guide pages (Luxor West Bank, Saqqara and Dahshur, Red Sea dive sites, Mount Sinai and Saint Catherine, Coptic Cairo; 8 languages) sent by IndexNow on publication | Core `indexnow.php` | "IndexNow: 42 URLs sent on 2026-10-16 00:01, answer 200" (40 new + 2 updated) | Bing and the other IndexNow engines discover them | 18 Oct; Google requests after the 16 Oct list, then 22 Oct |
| GSC Page indexing snapshot: 30 indexed, 162 not indexed. Of those, 142 "Excluded by noindex" (validation **Started**, the pre-launch noindex) and 20 "Discovered – currently not indexed". Request for the Arabic Aswan temples guide refused: "Quota Exceeded" (daily) | Search Console | Read 15 Oct evening | Validation can take up to ~2 weeks | 16 Oct (scheduled task `egypt-roamer-indexing-requests-oct16`), 22 Oct |
| Bing Webmaster Tools: site imported from GSC (owner verified via Google), sitemap present ("Success", 33 URLs discovered) | Bing WMT | Sitemaps page | Bing crawls the sitemap | 22 Oct |
| Bing URL Inspection `/`: **"Not indexed due to NOINDEX directive", last crawl 27 Sep** (stale, pre-launch); live test "can be indexed" → Request indexing | Bing WMT | "URL submitted successfully" | Recrawl of the homepage | 18 Oct |
| Bing URL Submission: 99 priority URLs (7 language homes, all EN destinations/experiences/guides/archives, all AR destinations/experiences, other-language destinations, DE + 3 FR experiences). **Remaining 49 (other-language experiences)**: submit on 16 Oct: the FR experiences after the first 3, and all IT, ES, RU and ZH experiences, plus the remaining language archives (sitemap URLs not in the list above) | Bing WMT | Submitted list, "Today at 03:01" | Bing indexes the priority set | 18 Oct, 22 Oct |
| First-party page-view totals (Core 1.2.31, `8f4446c`) and Privacy Policy in 8 languages | Core, legal sync | Production: 1 labelled view recorded, "Measuring since 2026-10-15"; sync updated 8 pages | Visits become measurable | daily for 7 days |
| Viator provider + 7 offers published; `/go/` → Viator with `pid/mcid/medium/campaign` | wp-admin | 10 pages × redirect chain checked; 11 QA clicks logged | First affiliate path from organic pages | 17 Oct (Viator dashboard) |
| Disclosure next to partner links in card grids and the homepage rail (theme 1.2.45, `6e32619`) | theme | Production: home, /ar/, /zh/experiences/, search, Great Sand Sea show the disclosure | Compliance | — |

**Test data on production (15 Oct):** all of 15 Oct is QA (final count 26 `/go/` clicks, 27 page views; see the afternoon entry).

## 2026-10-15 (afternoon): multilingual consistency + product-level Viator offers

| Change | Where | Verified | Check on |
|---|---|---|---|
| Viator offers switched from listing pages to verified products (7), plus a new Great Sand Sea offer (#516, 3-day Siwa trip from Cairo). Slugs unchanged | wp-admin (data) | 8/8 redirects → product URL with `pid/mcid/medium/campaign` | 17 Oct (Viator dashboard) |
| Language menu: Arabic item aligned on LTR pages | theme 1.2.46 `pages.css` | Production: all items at the same x | — |
| hreflang alternates never carry a query string (archives copied `?utm_…`) | Core 1.2.32 `multilingual.php` | Production: 0 of 9 alternates with `?` | GSC hreflang report, 22 Oct |
| Offer titles `lang="en"` on translated pages | theme 1.2.46 | Production (AR) | — |

- **Commit:** `9886fd2`, theme **1.2.46** / Core **1.2.32**, deployed and cache flushed.
- **Reports:**
  - `MULTILINGUAL-NAVIGATION-CONSISTENCY-REPORT.md`
  - `VIATOR-CONTENT-MATCHING-REPORT.md`
  - `MULTILINGUAL-VIATOR-REGRESSION-REPORT.md`
- **Theme 1.2.47** (`0fcea37`): the same navigation, footer and homepage sections in all 8 languages. English-only guides are linked from every language and marked "EN". This corrects 1.2.46, which had classed the 3-vs-2 menu as intentional. Verified on production.
- **Theme 1.2.48 / Core 1.2.33** (`e1c200d`): the 4 guides published in all 7 other languages (28 pages, review note), IndexNow 28 URLs (200). The menu and homepage are now native in every language. Watch these URLs in GSC from 22 Oct.
- **Test data, 15 Oct:** 26 `/go/` clicks and 27 page views. Treat all of 15 Oct as QA; real measurement effectively starts 16 Oct.
- **Bing email (welcome, 15 Oct):** it recommends sitemap and URL submission. Both are done. The remaining 49 URLs wait for quota on 16 Oct.

## 2026-10-16: indexing requests (scheduled task `egypt-roamer-indexing-requests-oct16`)

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Requested indexing for 8 guides that were "URL is unknown to Google": AR Grand Egyptian Museum, EN Temples of Aswan and Lake Nasser, EN 14 days, EN Valley of the Kings, EN Great Sand Sea, AR Cairo at Night, EN Luxor West Bank, EN Red Sea dive sites | Search Console URL Inspection | "Indexing requested" for each | Priority crawl | 22 Oct |
| Already on Google, no request needed: EN Inside the pyramids, EN and AR 10 days, EN Saqqara and Dahshur, EN Mount Sinai, EN Coptic Cairo, AR Valley of the Kings, AR Great Sand Sea | Search Console URL Inspection | "URL is on Google" | — | — |
| Bing URL Submission: 89 URLs. The 15 Oct leftovers: 7 FR experiences (all except the 3 sent on 15 Oct), all IT, ES, RU and ZH experiences (40), and the destinations/experiences/guides archives in DE, FR, IT, ES, RU and ZH (18). Then the 11 EN guides from the Google list, their 11 AR versions, plus AR GEM and AR Cairo at Night | Bing WMT | "URLs submitted today 89", list now 190; quota left 11 | Bing crawls the rest of the multilingual set | 18 Oct, 22 Oct |

- **Left for 22 Oct:** no Google quota error was hit. The remaining Arabic versions of the fourth-batch guides (Luxor West Bank, Saqqara, Red Sea, Mount Sinai, Coptic Cairo) were not inspected in Google; they are in Bing's list and IndexNow. Other-language guides (DE, FR, IT, ES, RU, ZH) rely on IndexNow and the sitemap.

**Baseline at the start** (Search Console, 29 Sep–12 Oct):
- 2 clicks, 37 impressions, average position 38.7, CTR 5.4%;
- 30 pages indexed, 162 not.

## 2026-10-18: Bing follow-up (scheduled task `egypt-roamer-bing-check-oct18`), blocked

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| **Not done:** Search Performance, Site Explorer, URL Inspection (`/` and `/guides/abu-simbel-from-aswan/`), URL Submission and the wp-admin IndexNow line | Bing WMT, wp-admin | Claude in Chrome was not connected (no browser listed, two retries) | — | Next run, once Edge is connected |
| Pre-checked the 12 URLs to submit: each returns 200 with no `noindex`; Arabic URLs taken from `hreflang="ar"` | Production, public fetch | 12/12 `200` | Ready to paste into URL Submission | Next run |

URLs ready for Bing URL Submission (12):

```
https://egyptroamer.com/guides/day-trips-from-cairo/
https://egyptroamer.com/guides/nile-cruise-or-hotels-luxor-aswan/
https://egyptroamer.com/guides/snorkelling-hurghada-or-sharm/
https://egyptroamer.com/guides/white-desert-night/
https://egyptroamer.com/experiences/alexandria-day-trip-from-cairo/
https://egyptroamer.com/experiences/ras-mohammed-snorkelling-from-sharm/
https://egyptroamer.com/ar/guides/%d8%a3%d9%81%d8%b6%d9%84-%d8%b1%d8%ad%d9%84%d8%a7%d8%aa-%d8%a7%d9%84%d9%8a%d9%88%d9%85-%d8%a7%d9%84%d9%88%d8%a7%d8%ad%d8%af-%d9%85%d9%86-%d8%a7%d9%84%d9%82%d8%a7%d9%87%d8%b1%d8%a9/
https://egyptroamer.com/ar/guides/%d8%b1%d8%ad%d9%84%d8%a9-%d9%86%d9%8a%d9%84%d9%8a%d8%a9-%d8%a3%d9%85-%d9%81%d9%86%d8%a7%d8%af%d9%82%d8%9f-%d9%85%d9%86-%d8%a7%d9%84%d8%a3%d9%82%d8%b5%d8%b1-%d8%a5%d9%84%d9%89/
https://egyptroamer.com/ar/guides/%d8%a7%d9%84%d8%b3%d9%86%d9%88%d8%b1%d9%83%d9%84%d9%8a%d9%86%d8%ac-%d9%81%d9%8a-%d9%85%d8%b5%d8%b1-%d8%a7%d9%84%d8%ba%d8%b1%d8%af%d9%82%d8%a9-%d8%a3%d9%85-%d8%b4%d8%b1%d9%85/
https://egyptroamer.com/ar/guides/%d9%84%d9%8a%d9%84%d8%a9-%d9%81%d9%8a-%d8%a7%d9%84%d8%b5%d8%ad%d8%b1%d8%a7%d8%a1-%d8%a7%d9%84%d8%a8%d9%8a%d8%b6%d8%a7%d8%a1-%d9%85%d8%a7%d8%b0%d8%a7-%d8%aa%d8%aa%d9%88%d9%82%d8%b9/
https://egyptroamer.com/ar/experiences/%d8%b1%d8%ad%d9%84%d8%a9-%d9%8a%d9%88%d9%85-%d8%a5%d9%84%d9%89-%d8%a7%d9%84%d8%a5%d8%b3%d9%83%d9%86%d8%af%d8%b1%d9%8a%d8%a9-%d9%85%d9%86-%d8%a7%d9%84%d9%82%d8%a7%d9%87%d8%b1%d8%a9/
https://egyptroamer.com/ar/experiences/%d8%b3%d9%86%d9%88%d8%b1%d9%83%d9%84%d9%8a%d9%86%d8%ac-%d9%81%d9%8a-%d8%b1%d8%a3%d8%b3-%d9%85%d8%ad%d9%85%d8%af-%d9%85%d9%86-%d8%b4%d8%b1%d9%85-%d8%a7%d9%84%d8%b4%d9%8a%d8%ae/
```

## 2026-10-20: the 17 and 18 Oct follow-ups, redone (owner: "check the tasks, I'm not sure they ran")
The scheduled Viator run (17 Oct) stopped after a few seconds without reading anything, and the Bing run (18 Oct)
was blocked (browser not connected). Both done by hand today, in the owner's browser.

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Viator Performance Snapshot, last 30 days: **1 visitor, 0 bookings, 0% conversion, $0 booking value**. Payout details still not entered (owner action) | partners.viator.com/dashboard | read on screen | — | 22 Oct |
| Site visits, 16–19 Oct: 285 page views, 160 entrances, **1 from a search engine (google.com)**, 0 from AI assistants, 8 `/go/` redirects, 0 failed links. Most views are the Trips & tours pages in 8 languages, mainly our own release checks (headless browsers are not all filtered out) | wp-admin → Visits | read on screen | — | 22 Oct |
| Bing Search Performance: 0 clicks, 0 impressions. Site Explorer: no indexed URLs | Bing WMT | read on screen | — | 22 Oct |
| Bing URL Inspection `/`: still "Not indexed due to NOINDEX directive", last crawl **27 Sep** (pre-launch). Live page has no noindex (`robots: max-image-preview:large`, robots.txt allows). **Request indexing** sent | Bing WMT | "URL submitted successfully" | Bing recrawls the homepage and drops the stale noindex | 22 Oct |
| Bing URL Submission: the 12 URLs listed on 18 Oct (4 guides + 2 experiences, EN and AR) | Bing WMT | "12 URLs submitted successfully"; quota 99 → 87 | Indexing of the new pages | 22 Oct |
| IndexNow line: "13 URLs sent on 2026-10-17 21:37, answer 200" | wp-admin → Egypt Roamer | read on screen | — | — |
| **22 Oct:** 5 new guides published in 8 languages (40 pages, #903–#942): `luxor-hot-air-balloon`, `cairo-museums-which-to-visit`, `islamic-cairo-guide`, `dendera-and-abydos-temples`, `el-gouna-guide`. Indexable, hreflang to all 8 languages; IndexNow sends them on publish | Egypt Roamer → Guide translations (theme 1.3.15, Core 1.2.65) | 200, `max-image-preview:large`, checked live | New long-tail pages for Luxor, Cairo and the Red Sea | 29 Oct |
| **22 Oct review:** GSC (domain property, 28 days): 2 clicks, 271 impressions, CTR 0.7%, average position 61.2 (20 Oct: 109 impressions; baseline 15 Oct: 37). Page indexing (URL-prefix): 30 indexed, 162 not indexed, noindex validation still Started; report data stops about 8 Oct. Abu Simbel and Day trips guides (EN) now indexed. Indexing requested for the 5 new EN guides. Pages ranked 10–30: 8, mostly 1–9 impressions and anonymised queries; one title changed: AR Great Sand Sea → "بحر الرمال العظيم: أين يقع، وكيف تزوره من سيوة" for "اين يقع بحر الرمال العظيم" (position 12) | GSC; content/editorial/ar/g-great-sand-sea.md (Core 1.2.66) | Indexing requested ×5 | Better CTR on the AR query; the 5 guides indexed | 29 Oct |

## 2026-10-24: Bing still at 0 indexed; Visits counter cleaned

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Bing state: sitemap read 19 Oct (415 URLs discovered), IndexNow received 126 URLs to 22 Oct, Site Explorer **0 indexed**, `site:egyptroamer.com` 0 results. URL Inspection `/`: still the **27 Sep** crawl ("NOINDEX"); the live test today says "URL can be indexed by Bing". Crawl Control at default | Bing WMT | read on screen | — | — |
| URL Submission: `/` and the 7 language homes (`/ar/`, `/de/`, `/fr/`, `/it/`, `/es/`, `/ru/`, `/zh/`) | Bing WMT | listed "Today at 16:40"; quota 100 → 92 | Bing recrawls the homes and drops the stale noindex | 31 Oct. If still the 27 Sep crawl, ask Bing Webmaster support (owner's OK) |
| GSC (domain property, 28 days to 22 Oct): 2 clicks, 580 impressions, CTR 0.3%, average position 63.3. Page indexing: 217 indexed, 162 not (139 "noindex", mostly legal/contact pages crawled 1 Oct; report updated 16 Oct). Links report: **0 external links** | Search Console | read on screen | — | 29 Oct |
| Visits counter (Core 1.2.67, `39a566b`): a page view is sent only after the visitor scrolls, taps, clicks, types or moves the pointer; `/go/` logs a click only with `Sec-Fetch-Site: same-origin` + `Sec-Fetch-User: ?1` (or an own-site referrer). Before this, Visits showed 795 views in 30 days with 3 search entrances, spread evenly over the 8 `/experiences/` archives, mostly "tablet": bots, not people | Core | 465 sitemap URLs 200 on 1.2.67; a non-logged-in browser sends no hit until it scrolls, then 204; `/go/` still redirects to Viator | Visits shows people, not bots, from 24 Oct | 31 Oct |

## 2026-10-25: Google recheck of stale discovered URLs

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| GSC performance, 3 months (29 Sep–23 Oct): 5 clicks, 736 impressions, CTR 0.7%, avg position 63; report refreshed 4 hours before check | Search Console → Performance | read on screen | — | 29 Oct |
| URL Inspection showed `/guides/best-time-to-visit-egypt/` **is on Google**, although it appears in the 16 Oct “Discovered – currently not indexed” examples | Search Console → URL Inspection | indexed status shown | Confirms the 16 Oct examples are stale; inspect URLs individually | — |
| `/es/destinations/asuan/`: not indexed, discovered-not-indexed; sitemap and EN/AR Aswan referring pages found. Live test: available to Google, can be indexed, 1 valid breadcrumb. Requested indexing | Search Console → URL Inspection | “Indexing requested”; URL added to priority crawl queue | Google may crawl and index the Spanish destination page | Recheck after report refresh |
| `/es/experiences/excursion-de-un-dia-a-abu-simbel-desde-asuan/`: not indexed, discovered-not-indexed; sitemap and EN/AR/ES referring pages found. Live test: available to Google, can be indexed, 1 valid breadcrumb. Requested indexing | Search Console → URL Inspection | “Indexing requested”; URL added to priority crawl queue | Google may crawl and index the Spanish product page | Recheck after report refresh |
| `/fr/destinations/siwa-fr/`: URL was unknown to Google; no sitemap or referring page reported. Live test: available to Google, can be indexed, 1 valid breadcrumb. Requested indexing | Search Console → URL Inspection | “Indexing requested”; URL added to priority crawl queue | Google may discover, crawl and index the French Siwa destination page | Recheck after report refresh |
| GSC now shows 736 impressions, 5 clicks, 0.7% CTR, avg position 63 for 29 Sep–23 Oct; top queries include “great sand sea” (14), “aswan to luxor cruise” (13), itinerary variants (12/11/10), and snorkelling queries (9/8/8). Overview specifically recommended checking the English snorkelling comparison after a recent impressions increase | Search Console → Performance and Overview | read on screen | Use actual demand signals to prioritize updates | 29 Oct |
| Updated English snorkelling guide opening and related internal link; qualified the Straits of Tiran conditions; replaced its uncited seasonal water-temperature answer with advice to verify current conditions; added an EEAA citation for Ras Mohammed. Removed the live price/rating chips from the `guide-p4.php` guide-ending offer list, which were contrary to the project's no-prices/no-ratings content rule | `content/editorial/en/g-snorkelling.md`, `docs/CONTENT-SOURCES.md`, `wordpress/wp-content/themes/egypt-roamer/template-parts/guide-p4.php`, growth plan | local edits; editorial build passed; production still has old copy; full page-coverage QA timed out twice before output; no release made | Improve the landing-page answer and enforce the no-prices/no-ratings rule across guide endings | Full QA and visual review before release |
| Reworked the English Great Sand Sea guide (14 impressions, 0 clicks in the current query sample) against Ministry of Tourism and EEAA sources. Removed unsupported season advice, itinerary/permit assertions and Cairo day-trip claims; directs visitors to confirm current trip details. Title left unchanged | `content/editorial/en/g-great-sand-sea.md`, `docs/CONTENT-SOURCES.md`, compiled English HTML, growth plan | English compilation passed; production unchanged; 7 existing translations are now stale pending native review | Better answer the observed “Great Sand Sea” intent with sourced, current-safe information | Review translations and use the full release gate before publishing |
| Aligned the English Siwa destination's Great Sand Sea section with the official tourism source; removed unsupported blanket claims about essential 4×4 excursions, licensed operators, permits and fixed activity stops | `content/editorial/en/dest-siwa.md`, `docs/CONTENT-SOURCES.md`, compiled editorial data | Editorial compilation passed; production unchanged; 7 existing translations need native review before syncing | Keep related destination and guide pages consistent and evidence-based | Native review and full release gate before publishing |
| Removed the shared guide template's blanket promise of free cancellation on every offer and on route-wide mobile booking bars. The template now uses the existing translated disclosure that cancellation is shown when the partner offers it; an individual trip badge remains only when every option has verified cancellation. Corrected the matching social draft | `template-parts/guide-p4.php`, `languages/*.l10n.php`, `docs/SOCIAL-POSTS-2026-10-20.md` | Local source change; disclosure exists in all 7 translation catalogs plus English fallback; no release made | Set accurate cancellation expectations and avoid misleading visitors before they click through | Full page-coverage and desktop/mobile review in all languages before deploy |
| Matched `/go/` redirect logging with Visits CTA reporting by excluding authenticated WordPress users from redirect click totals. Code review showed the beacon already excludes logged-in users, while redirects did not; this can explain the 10 redirects / 0 taps in the 24–25 Oct sample, but does not prove who generated those historical redirects | `wordpress/wp-content/plugins/egypt-roamer-core/includes/redirect.php`, growth plan | Local source change only; no request-level historical data to attribute the 10 redirects | Keep logged-in QA clicks out of partner-click KPIs | Verify after release using a non-logged-in live click and the reports |

## 2026-10-26: 25–26 Oct changes released (intensive plan, week 1 item 1)

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Theme 1.3.19 / Core 1.2.70 (`d8dad88`): snorkelling and Great Sand Sea guides and the Siwa destination revised from official sources; 35 translations reviewed by Claude and approved by the owner; guide template without blanket free-cancellation promises or live price/rating chips; logged-in users no longer counted as `/go/` clicks | GitHub deploy; GoDaddy cache flushed | Local CI checks (80 PHP files, seed, editorial, legal, bundles) passed; live `track.js?ver=1.2.70`; page-coverage **392 pages, all ok**; EN desktop and AR phone snorkelling guide checked by eye (new partner-terms line, RTL, no overflow) | Accurate, sourced pages; honest booking copy | — |
| Guide translations sync in wp-admin: **35 updated** (EN snorkelling, Great Sand Sea; DE/FR/IT/ES/RU/ZH/AR snorkelling, Great Sand Sea, balloon trip, Nile cruise), 0 created, 0 skipped | Egypt Roamer → Guide translations | "Done", counts `{"updated":35}` | Live bodies match the reviewed sources | — |
| **Not updated in the database:** the Siwa destination page (8 languages). The guide sync covers guides and newer experiences only, and `wp egypt-roamer editorial` fills only untouched seed text | — | — | Needs a manual edit in wp-admin, or an owner SSH run if the body is still seed text | Week 1 |
| The guide sync preview still lists 6 English guides as updates after the run (Cairo museums, East Bank, Great Sand Sea, Red Sea, Sinai, West Bank): the known apostrophe comparison bug (handoff 25 Oct) | — | Preview after the sync | Fix in a later release | — |

## 2026-10-26 (afternoon): week 1 items 2–3 and the Siwa page

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| KPI week 2 recorded | `KPI-WEEKLY.md` | GSC, Bing, Visits, Viator, GetYourGuide, Klook, Trip.com read in Edge | — | 2 Nov |
| Core 1.2.71 (`6a1436a`): Privacy Policy and Affiliate Disclosure list all four partners in 8 languages; Privacy Policy dated 26 October 2026. Legal sync: **16 updated** (2 pages × 8 languages), next preview "Everything is up to date" | Egypt Roamer → Legal pages | Live: both pages name Viator, GetYourGuide, Klook and Trip.com in all 8 languages | Policy matches the live partners | When GA4 goes live, change the "no outside analytics" line in the same release |
| Core 1.2.72 (`dc024b8`): `wp egypt-roamer editorial` may replace an earlier shipped body that was never edited (`data/editorial/replaces.json`); first use: the Siwa destination | Core | Tested locally: old body → would update; one word edited → kept. page-coverage 392 pages all ok | Siwa page gets the revised, sourced text | After the owner's SSH run |
| Search Console email "Blocked by robots.txt": 1 URL, a `/go/` partner link | GSC Page indexing | URL list | Intentional (`Disallow: /go/`); no action | — |
| GSC Page indexing (domain property): 262 indexed; "Excluded by noindex" 1,137, almost all trip-filter combinations (`/experiences/?destination=…&private=1&pickup=1&len=…`), some with a `nocache=` parameter | GSC Page indexing | Examples list | Noindex by design, but they use crawl time. Follow-up: stop crawlers following filter links and find where `nocache=` links come from | Next week |
| Core 1.2.73 (`5607479`): **Egypt Roamer → Editorial updates** in wp-admin runs the `replaces.json` update without SSH (a new SSH login would disable the one the GitHub deploy uses). Preview: 8 × "Would update", 0 kept; run: **dest-siwa updated in 8 languages** (#23, #200, #110, #125, #140, #155, #170, #185); next preview "Everything is up to date" | wp-admin; GoDaddy cache flushed | Live: the revised Great Sand Sea sentences found on the Siwa page in all 8 languages; the old "4×4 excursion is essential" line gone (EN) | Siwa page matches its official sources | — |
| Core 1.2.74 (`c4150e6`): inside the pyramids, Mount Sinai and 14 days revised from Ministry of Tourism and EEAA pages (snorkelling and Great Sand Sea were already revised on 25 Oct: no change); 21 translations updated. Guide sync: **29 updated** (24 + the 5 apostrophe-bug entries); Mount Sinai (EN) now also shows in the apostrophe-bug preview because of "Catherine's" | Guide translations; cache flushed | Live: the new sentences found in all 3 guides × 8 languages; old EN lines gone; page-coverage 392 all ok | Pages with most impressions now cite official sources | Impressions/CTR of these pages on 2 Nov |

## 2026-10-26 (evening): first place pages (intensive plan, week 1)

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Core 1.2.75 / Theme 1.3.20 (`983c5df`): four new guides in 8 languages, written from Ministry of Tourism and Antiquities, EEAA and UNESCO pages: Temple of Edfu (`/guides/temple-of-edfu/`), Temple of Hatshepsut (`/guides/temple-of-hatshepsut-deir-el-bahari/`), Wadi Al-Hitan (`/guides/wadi-al-hitan-whale-valley/`), Wadi El-Gemal National Park (`/guides/wadi-el-gemal-national-park/`). Wikimedia Commons photos with author and licence under each (owner, 26 Oct). Arabic guides lose the drop cap that split the first word | Core, theme | Guide sync in wp-admin: **32 created**, 6 updated (the known apostrophe bug), 0 skipped; cache flushed. All 32 pages 200, indexable, hreflang complete, Commons photos and credits present; EN and AR checked on desktop and phone | 32 new indexable URLs, sent by IndexNow on publish; first pages built for "temple / protected area" searches | GSC and Bing on 2 Nov (KPI week 3) |
| page-coverage on production after the release: 424 pages, **96 problems, all on Wadi Al-Hitan and Wadi El-Gemal** (2 pages × 8 languages × 6 checks): no booking box or bar, and the header, dock and phone menu buttons go to the trips list. These two pages have no related trip: no partner offer covers Wadi Al-Hitan or Wadi El-Gemal. Edfu and Hatshepsut (and every other page) pass. The first run crashed on a page that navigated mid-check while the cache flush ran; the re-run completed | `tools/qa/page-coverage.mjs` | Output of the re-run | — | Owner decision: add a matching partner offer, or let place pages without a trip pass the check |
