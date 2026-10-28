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

## 2026-10-26 (night): Klook trips for Wadi Al-Hitan and Wadi El-Gemal

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Core 1.2.76 / Theme 1.3.21 (`373a06c`): trips `exp-hitan` (`/experiences/wadi-al-hitan-day-trip-from-cairo/`) and `exp-gemal` (`/experiences/wadi-el-gemal-safari-from-marsa-alam/`) in 8 languages. Guide sync: **16 created** (English #1081, #1082). Klook offers **#1098** (Wadi Al-Hitan, Cairo, priority 2) and **#1100** (Wadi El-Gemal, no destination), `aid=140689`. Guides #1052 and #1051 related to their trips through the REST API; cache flushed | Core, theme, wp-admin | All 32 trip and guide pages 200 in 8 languages, each with the Klook option and translated facts, Commons credits present; EN desktop and AR phone viewed. **page-coverage: 440 pages, all ok** (the 96 problems of the evening run are gone) | 16 new indexable trip URLs; the two place guides now lead to a booking | KPI week 3 (2 Nov); Klook clicks in the Klook portal |
| Theme 1.3.22 (`1b1b818`): guide trip cards, the main button and the phone bar say "See the option" for a trip with one option and "See the trip" with none (was "See the 1 options"), in 8 languages | Theme | Cache flushed. Live: Wadi Al-Hitan and Wadi El-Gemal guides show the singular in all 8 languages; the balloon and West Bank guides still show "See the 3 options" for their 3-option trips | — | — |

## 2026-10-26 (night): Complianz and GA4 live (intensive plan, week 1 item 4)

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| GA4 property egyptroamer.com (account 410293844, property 556865439, stream G-DFEC08WLQ6) checked: Data Processing Terms accepted 30 Sep 2026, all 4 data-sharing options off, Google signals off, retention 2 months (events) / 14 months (users). Email preferences: all unchecked (owner OK) | Google Analytics | Read in Edge | — | — |
| Complianz: default law EU (GDPR), targets Germany/Austria/Belgium/Spain (owner), statistics "Google Analytics" by Complianz with G-DFEC08WLQ6, Consent Mode V2 **off** (Basic: nothing before consent), attestations 1 and 2 ticked (DPA accepted; no use for other Google services), IP attestation **not** ticked (owner). Complianz's Site Kit integration off; Site Kit's own snippet stays off. Banner and blocker enabled with Finish | Complianz | Live tests (fresh browsers, EN/AR/DE): **0 Google requests before consent**; Accept → gtag.js + `g/collect` for G-DFEC08WLQ6; Deny → no Google request; cookies `cmplz_*` 365 days, `_ga`/`_ga_DFEC08WLQ6` after consent. GA4 Realtime showed the test visit | First GA4 sessions from consenting visitors | KPI week 3 (2 Nov): GA4 sessions next to Visits |
| Core 1.2.77 (`9eb3f14`): Privacy Policy and Cookie Policy in 8 languages for GA4 after consent. Legal sync: **16 updated**, next preview all 32 current | Core, wp-admin | Sync output | — | — |
| Open: banner texts are English in all 8 languages; banner colours are Complianz's default purple; policies say `_ga` lasts "2 years" while Chrome caps it at 400 days | Complianz, legal texts | Live screenshots | — | Done below |

## 2026-10-26 (late night): cookie banner in 8 languages and the site's colours; `_ga` duration

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Core 1.2.78 (`8092b6f`): Cookie Policy says `_ga` and `_ga_DFEC08WLQ6` last "up to 2 years" (Chrome caps cookies at 400 days), 8 languages. Legal sync: **8 updated** (cookies only), next preview all 32 current | Core, wp-admin | Sync output | — | — |
| Banner texts translated in Polylang → Strings translations (group `complianz`, 21 strings × 7 languages: title, message, Accept/Deny/View/Save preferences, 5 category names and their descriptions). Translations by Claude (AI, not a native speaker), owner-approved process | wp-admin (database) | Live HTML in all 8 languages | Visitors read the banner in their language | — |
| Theme 1.3.23 (`c9c05c1`) and 1.3.24 (`5a1c4aa`): `assets/css/consent.css` (in both CSS bundles) sets Complianz's colour variables to the site palette (white card, gold Accept with charcoal text, stone Deny/preferences, gold-deep links and active toggle) and the site font; "Always active" in ru/zh/ar via `gettext_complianz-gdpr` (Complianz ships de/fr/it/es) | Theme | Built-in browser: AR desktop (banner + preferences), ZH and DE phone; GA4 script still `type="text/plain"` before consent | — | — |

## 2026-10-27: brand profiles on the site, first pins and posts

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Theme 1.3.25 / Core 1.2.79 (`30712f6`): "Follow us" with Instagram (instagram.com/egyptroamer) and Pinterest (pinterest.com/egyptroamer) icons in the footer, 8 languages; both as `sameAs` of the homepage Organization | Theme, Core | Live HTML EN/DE/AR/ZH; page-coverage 440 pages, 1 problem (zh Wadi El-Gemal trip, phone booking bar) re-checked 3× on that page and its English twin: bar shown every time (timing) | Google links the profiles to the brand | — |
| Pinterest: boards "Ancient Egypt: Temples and Tombs" and "Egypt's Nature: Deserts and the Red Sea"; 4 pins (Edfu, Hatshepsut, Wadi Al-Hitan, Wadi El-Gemal), each linking to its guide, Commons photo with credit in the description (owner-approved plan) | Pinterest | Profile shows 4 pins; each pin page links to the right guide | Referral visits from Pinterest | KPI week 3 (2 Nov): social referrals in Visits |
| Instagram: first post (Edfu) with credit and "Full guide on egyptroamer.com"; plan: Hatshepsut, Wadi Al-Hitan, Wadi El-Gemal one a day. The profile has no bio or website link yet (needs owner OK) | Instagram | "Your post has been shared" | — | Next days |
| Theme 1.3.26 / Core 1.2.80 (`147c5da`): Facebook page (profile.php?id=61595813675400) added to the footer icons (8 languages) and the homepage `sameAs` (owner: full access to the site's social pages, 27 Oct) | Theme, Core | Live HTML EN/AR/FR | — | — |
| Pinterest profile: About ("Independent travel guide to Egypt in 8 languages: places, trips and practical tips, written from official sources.") and website egyptroamer.com | Pinterest | Re-read after save | — | — |
| Instagram bio: typed and submitted, but **not saved**: the public profile shows no bio and the Edge session was logged out afterwards. The website link can only be set in the Instagram phone app | Instagram | Public profile | — | Owner logs back in |
| Theme 1.3.27 (`187f822`): the JS image helper understands `commons:` ids (the Wadi Al-Hitan trip card in the homepage plan had no photo; owner report) | Theme | Live: the photo loads (500px) | — | — |
| Core 1.2.81 (`5e20a80`): guide sync compares the stored title instead of `get_the_title()` (texturized "Egypt's" never matched), so unchanged guides no longer show as updates | Core | Live preview: "Everything is up to date", 0 updated / 273 current | Real updates stand out | — |

## 2026-10-27: place pages, batch 2 (Kom Ombo, Philae; Karnak and Valley of the Kings updated)

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Core 1.2.82 / Theme 1.3.28 (`40a443d`): new guides `g-kom-ombo` (/guides/kom-ombo-temple/) and `g-philae` (/guides/philae-temple-of-isis/) in 8 languages from the Ministry's pages, Commons photos credited. Karnak and Valley of the Kings already had guides, so (owner's choice) no duplicate pages: `g-east-bank` (Karnak, avenue) and `g-valley-kings` (what the valley is) rewritten from the Ministry's pages in 8 languages, correcting Britannica figures (central columns 21 m, not 24; 1550–1069 BC, not 1539–1075; over 60 tombs plus 20 unfinished, not 62) | Core, Theme, wp-admin | Guide sync: 16 created, 16 updated, then 7 Kom Ombo link refreshes; preview "Everything is up to date" (287) | New entry pages for Kom Ombo and Philae searches | Search Console, 2 weeks |
| Theme 1.3.29 (`b876647`): Philae's Isis-section photo replaced (the first pick showed Trajan's Kiosk) | Theme | Live HTML | — | — |
| Release gate | — | page-coverage: **456 pages, all ok**; Kom Ombo EN desktop and AR phone, Philae EN desktop checked in the browser | — | — |

## 2026-10-27: snorkelling guide answers the searches it appears for

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Search Console (28 days): 790 impressions, 3 clicks, average position 64; non-English pages and niche topics already reach page 1 (Great Sand Sea ar/fr/de 9–10, best time ar 7, zh 11); the snorkelling guide had the most impressions (EN 81, DE 51) at positions 72–82 for "best time to snorkel in hurghada", "beste schnorchelplätze hurghada" | Search Console | Read in Edge | — | — |
| Core 1.2.83 (`efd0a76`): `g-snorkelling` gains "When to snorkel" and "The best snorkelling spots" (Ras Mohammed, Nabq, Abu Galum from the EEAA; Giftun; Wadi El-Gemal) and two FAQ answers, in 8 languages (the editorial check refuses outdated translations with different sections) | Core, wp-admin | Sync: 8 updated, then up to date (287); DE page checked; page-coverage **456 all ok** | Better positions for snorkelling queries in EN/DE | Search Console, 2–3 weeks |

## 2026-10-27: Nile cruise page answers "how many days" and "which temples"

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Theme 1.3.30 / Core 1.2.84 (`32ea926`): the trip page's FAQ (proposal 4 renders `inc/world-data.php`, generated from `docs/prototypes/journey-v4/world-data.json`, not the article body) gains "How many days is a Nile cruise from Aswan to Luxor?" (about 4 days and 3 nights, from the three partner pages) and "Which temples do you see…?", 8 languages via `tools/i18n/new-strings.json`. The `exp-cruise` article also gains route links to the new place guides and a "How long it takes" section (8 languages), kept in step with the page | Theme, Core, wp-admin | Live FAQ shows both questions; sync 8 updated, then up to date (287) | Better positions for "aswan to luxor cruise" (EN 61, FR 56) | Search Console, 2–3 weeks |

## 2026-10-27: pyramids guide expanded; social posts

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Core 1.2.85 (`0f0a4cb`): `g-inside-pyramids` gains "Khafre's and Menkaure's pyramids" (heights, casing, Sphinx in Khafre's complex, queens' pyramids; Meresankh III and Workers' Cemetery tickets) and "How tall are the pyramids of Giza?", 8 languages, from the Ministry's pages. The Ministry's pages do not describe their interiors, so nothing is said about them | Core, wp-admin | Sync 8 updated, then up to date (287); local page shows the section | Better positions for "inside pyramid of giza" (EN 72) and near-page-1 IT/AR/DE queries | Search Console, 2–3 weeks |
| Instagram: Hatshepsut post ("Full guide: link in bio"; the owner added https://egyptroamer.com to the bio). Facebook page: first post, Edfu, as a link post to the guide (Facebook's preview uses the page's own image), credit in the text, published as the page, no boost | Instagram, Facebook | "Post shared"; page shows the post | Referral visits | KPI week 3 |
| Pinterest: Kom Ombo and Philae pins (board "Ancient Egypt"), each linking to its guide. On the first try the Pinterest session in Edge was the LIORIVE account: nothing was published there; one unpublished Kom Ombo draft was left in that account (expires in 30 days) | Pinterest | Profile shows 6 pins, each pin page links to its guide | Referral visits | — |

| Instagram: Wadi Al-Hitan post (Fayoum fossil whales, UNESCO 2005; "Full guide: link in bio"; credit Medomadrex, CC BY-SA 4.0) | Instagram | Profile shows 3 posts | Referral visits | KPI week 3 |
| page-coverage for the snorkelling, cruise and pyramids batch (Core 1.2.83–1.2.85, Theme 1.3.30): 456 pages, all ok | — | `page-coverage: all ok` | — | — |

## 2026-10-27: Egypt visa guide

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| Core 1.2.86 (`d6aa2c9`): new guide `g-visa`, "Egypt Visa: e-Visa, Visa on Arrival and What to Bring" (`/guides/egypt-visa-guide/`), 8 languages, only from the Ministry of Interior's e-Visa portal, the Visa on Arrival platform and the Embassy of Egypt in Washington. Official e-Visa fees (US$30/65) stated as an owner-approved exception, dated "last checked 27 October 2026". No Sinai-only stamp (no official source) | Core, wp-admin | Sync created 8 (#1190–#1197), then up to date (294); live EN/DE/AR pages 200, links localized, FAQ present; EN desktop and AR phone checked by eye. page-coverage not run yet for this release | Impressions for "egypt visa", "egypt e-visa fee", "visa on arrival egypt" queries | Search Console, 2–3 weeks; re-check the portal fees every few months |
| Core 1.2.87 (`6ab7a39`): `g-best-time`, `g-7-days`, `g-cairo` link to the visa guide (one sentence in the intro), 8 languages. Translations through Guide translations (21 updated). The 3 English bodies are seeded pages, which that sync does not touch: Core 1.2.88 (`f80da72`) lists their earlier shipped hashes in `data/editorial/replaces.json`, and Editorial updates replaced them (none had been edited in WordPress) | Core, wp-admin | Live EN and AR pages link to the visa guide | Internal links to the new guide | — |
| Theme 1.3.31 (`b4dba94`): the "Recently viewed" card on Trips & tours built Commons ids as Unsplash links, so the Wadi Al-Hitan trip showed no photo (owner's screenshot). `assets/js/p4-trips.js` now builds the Commons link as `src/js/data.js` does. The 27 Oct homepage fix (Theme 1.3.27) had not covered this card | Theme | Trip visited, then Trips & tours: the card's photo loads (500 px) | — | — |
| Pinterest: visa pin (new board "Egypt Travel Planning: Visas, Itineraries and Tips", the guide's own Unsplash pyramids photo) and snorkelling pin (board "Egypt's Nature: Deserts and the Red Sea", the guide's Unsplash photo of the coast at Hurghada). Facebook page: visa guide link post, as the page, no boost | Pinterest, Facebook | Profile shows 8 pins; page shows the post | Referral visits | KPI week 3 |
| page-coverage for the visa batch (Core 1.2.86–1.2.88, Theme 1.3.31): 464 pages, all ok | — | `page-coverage: all ok` | — | — |

## 2026-10-27: Medinet Habu and Qaitbay Citadel guides

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| Core 1.2.89 / Theme 1.3.32 (`61c5df0`): new guides `g-medinet-habu` (`/guides/medinet-habu-temple-of-ramesses-iii/`) and `g-qaitbay` (`/guides/qaitbay-citadel-alexandria/`), 8 languages, only from the Ministry's pages; Commons hero and section photos with credits. The money/customs guide was not written: its figures appear only in a 2022 Ministry of Finance manual, and egypt.travel did not resolve. Esna Temple left out (no Ministry monument page) | Core, theme, wp-admin | Sync created 16 (#1222–#1237), then up to date (308); live EN/DE/AR pages 200 with credits; all photos load; AR phone checked by eye. page-coverage not run yet for this release | Queries for "medinet habu", "qaitbay citadel" in 8 languages | Search Console, 2–3 weeks |
| Instagram: Wadi El-Gemal post ("Full guide: link in bio"; credit Panegyrics of Granovetter, CC BY-SA 2.0), posted on 27 Oct at the owner's request instead of the next day; the one-time scheduled task for 28 Oct was deleted | Instagram | Profile shows 4 posts | Referral visits | KPI week 3 |
| Core 1.2.90 (`0bbe392`): `g-west-bank`, `g-valley-kings`, `dest-luxor` link to the Medinet Habu guide and `g-day-trips-cairo`, `dest-alexandria` to the Qaitbay guide (one sentence after the first mention), 8 languages. The French Medinet Habu guide now uses the site's French spelling, Médinet Habou. Guide translations updated 25; Editorial updates replaced the 16 destination bodies (earlier shipped hashes added to `replaces.json`; none had been edited). A daily scheduled check (`egypt-roamer-money-guide-source-check`, 10:00) watches egypt.travel and customs.gov.eg and starts the money guide when one opens | Core, wp-admin | Live EN, FR and AR pages link to the new guides. page-coverage not run yet for 1.2.89–1.2.90 | Internal links to the new guides | — |
| Pinterest: Medinet Habu and Qaitbay pins (board "Ancient Egypt: Temples and Tombs"; credits Vyacheslav Argenberg, CC BY 4.0 and Argenberg, CC BY 2.0), each linking to its guide. The Qaitbay pin first stayed a draft; re-selecting the board and publishing again worked (checked after an Edge restart). Facebook page: Medinet Habu guide link post, as the page, no boost | Pinterest, Facebook | Profile shows 10 pins; page shows the post | Referral visits | KPI week 3 |

## 2026-10-27: Mummification Museum and Alexandria National Museum guides; Instagram

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| Core 1.2.91 / Theme 1.3.33 (`60b597c`): new guides `g-mummification-museum` (`/guides/mummification-museum-luxor/`) and `g-alexandria-museum` (`/guides/alexandria-national-museum/`), 8 languages, only from the Ministry's museum pages (hours, evening hours and ticket prices on those pages left out); Commons hero and section photos with credits | Core, theme, wp-admin | Sync created 16; live EN/FR pages 200 with credits; all photos load (including the accented file name); AR phone checked by eye | Queries for "mummification museum luxor", "alexandria national museum" | Search Console, 2–3 weeks |
| Instagram: Kom Ombo (credit Masgalal, CC BY-SA 4.0) and Philae (credit Panegyrics of Granovetter, CC BY-SA 2.0) posts, "Full guide: link in bio", at the owner's request | Instagram | Profile shows 6 posts | Referral visits | KPI week 3 |
| page-coverage for the batch Core 1.2.89–1.2.91 / Theme 1.3.32–1.3.33 (Medinet Habu, Qaitbay, the links to them, the two museum guides): 496 pages, all ok | — | `page-coverage: all ok` | — | — |

## 2026-10-27: two-week traffic experiment (owner-approved), until 10 Nov 2026

Baseline, Search Console, last 28 days (data to 25 Oct): **3 clicks, 865 impressions, average position 64.6**; 328 queries, only 15 within position 30 (5 in 1–10, 5 in 11–20, 5 in 21–30), almost all with a single impression; the queries with volume rank far down (Aswan–Luxor cruise 61.8, Egypt itinerary 94–98, snorkelling 75–92; the snorkelling and cruise expansions of 27 Oct are not in this data yet). Pinterest baseline before the experiment: 10 pins, 0 followers.

| Experiment | Target on 10 Nov | Stop rule |
|---|---|---|
| 1. Pinterest: 5 pins a day on the guides, keyword titles, credited photos | 10,000 impressions and 100 outbound clicks (Pinterest analytics) | under a quarter of that: stop |
| 2. Strengthen pages Google already shows | Google clicks from 3 to at least 30 (28 days) | no rise: stop |

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| Day 1: 5 pins — Aswan–Luxor cruise, 7-day itinerary (board "Egypt Travel Planning"), Great Sand Sea ("Egypt's Nature"), Mummification Museum and Alexandria National Museum ("Ancient Egypt"), each linking to its page | Pinterest | Profile shows 15 pins | Pinterest impressions and clicks | 10 Nov |
| Daily queue for the remaining 31 pages written to `C:\Users\morsy\.claude\scheduled-tasks\egypt-roamer-pinterest-daily\queue.json`; the scheduled task itself was refused by the automatic safety check, so the daily pins are run by hand from that queue | — | — | — | — |

## 2026-10-27: homepage speed, no intro loader on desktop (Theme 1.3.34)

GTmetrix (desktop, Seattle, 26 Oct): grade D, performance 44%, LCP 3.7 s, TBT 548 ms. Cause in the code: the desktop homepage held the hero behind a black logo loader for 1.3-1.6 s plus its wipe (phones had no loader since 2 Oct). Owner approved removing it. PageSpeed API quota was exhausted, so before/after were measured with Playwright + system Chrome (3 runs, median; mobile = 412 px, 4x CPU, 1.6 Mbps, 150 ms).

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| Loader screen removed on every width; desktop gets the phones' CSS entrance | `header.php`, `src/js/components/hero.js`, `pages.css` | Desktop LCP 2.02 s → 0.94 s; mobile LCP 2.80 s → 2.74 s, TBT 820 → 582 ms; no page errors; hero checked desktop + phone, en + ar | Better desktop Core Web Vitals; GTmetrix LCP | GTmetrix + PageSpeed 28 Oct |

## 2026-10-28: Egypt itinerary planner (Core 1.2.92)

Search Console (28 days to 25 Oct) showed the top pages already match their queries (titles, sections, FAQs); their positions (60-98) point to the site's age and missing backlinks, not on-page gaps. One gap: the "egypt itinerary" cluster (egypt itinerary 16, egypt trip itinerary 15, egypt travel itinerary 13 impressions; positions 95-98) was served by the 14-day page.

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| New hub `/guides/egypt-itinerary/` in 8 languages (only restates the 7/10/14-day plans), linked from `g-7-days`, `g-10-days`, `g-14-days` in every language | Guide translations sync (8 created, 23 updated); Editorial updates (g-7-days en) | All 8 pages 200 with hreflang; 7/10/14 link to the hub; desktop + phone, en + ar, no errors | The hub takes over the "egypt itinerary" queries from the 14-day page | Search Console 10 Nov |

## 2026-10-28: Pinterest day 2

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| 5 pins from the queue: best time to visit ("Egypt Travel Planning"), Valley of the Kings and inside the pyramids ("Ancient Egypt"), White Desert night ("Egypt's Nature"), Grand Egyptian Museum ("Egypt Travel Planning"); each links to its guide, "Photo: Unsplash" | Pinterest (Egypt Roamer account; the browser had LIORIVE active and was switched before pinning) | Profile shows 20 pins, drafts empty | Pinterest impressions and clicks | 10 Nov |

PageSpeed Insights after Theme 1.3.34 (homepage, 28 Oct; pagespeed.web.dev, the API quota was exhausted): desktop 99 (FCP 0.5 s, LCP 0.8 s, TBT 0 ms, CLS 0.013); mobile 80 (FCP 2.6 s, LCP 4.1 s, TBT 0 ms, CLS 0.111). Mobile LCP and CLS (just over the 0.1 limit) are the next speed items.

## 2026-10-28: Arabic text direction (Theme 1.3.35-1.3.37)

The owner found two errors on Arabic pages that earlier checks missed (they looked at layout, overflow and errors, not at the reading direction of the text):

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| "Go on" arrows after labels pointed right in Arabic: `er_arrow()` gives ← on Arabic pages in 11 template spots and the assistant; 4 spans already mirrored by CSS keep → (1.3.36 fixed the double flip from 1.3.35) | `inc/i18n.php`, `template-parts/guide-p4.php`, `world-p4.php`, `inc/world.php`, `src/js/assistant.js` | New `tools/qa/rtl-direction.mjs`: 66 Arabic + 66 English pages, desktop and phone, all ok | Arabic links read naturally | Each release |
| Number ranges ("4–10 ساعات", "4–7") showed reversed ("10–4"): wrapped in a left-to-right isolate on Arabic pages (server output) and in the homepage planner cards (JS) | `inc/i18n.php`, `src/js/components/planner.js` | Same script | Durations and ranges read correctly | Each release |

## 2026-10-28: mobile speed, homepage (Theme 1.3.38-1.3.40)

Diagnosis (PageSpeed mobile + throttled-phone runs): the LCP on phones is the hero word ("EGYPT"), painted at first paint; its "render delay" came from bandwidth competition, not from the word itself. CLS 0.111 (not in every run) came from the web-font swap: the cookie banner grew (0.081) and the hero moved (0.030).

| Change | Where | Check | Expected effect | Re-check |
|---|---|---|---|---|
| Script preloads (GSAP, ScrollTrigger, Lenis, home bundle, locale, ~86 KB) on desktop only; phones fetch them after first paint | `inc/assets.php` | Throttled phone: first paint 2.35 → 1.99 s (median of 3) | Earlier hero paint on phones | — |
| Complianz's two stylesheets printed inline (2 fewer render-blocking requests) | `inc/assets.php` | Only the theme bundle is still a linked stylesheet | Earlier first paint | — |
| Metric-matched fallback faces (Arial/Liberation/Arimo and Roboto for Inter; Times/Liberation Serif/Tinos and Noto Serif for Playfair; Latin range only) | `assets/css/fonts.css`, `tokens.css` | Values measured from the woff2 files; homepage unchanged visually (en + ar, phone) | No shift when fonts swap | CrUX / Search Console Core Web Vitals |

PageSpeed mobile, homepage: before 80 (LCP 4.0-4.1 s, CLS 0-0.111); after 85 and 85 (LCP 3.6-3.7 s, CLS 0.086). Still open: LCP above 2.5 s, mostly the 30 KB render-blocking theme bundle (critical CSS would be the next step).
