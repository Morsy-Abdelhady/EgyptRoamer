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
