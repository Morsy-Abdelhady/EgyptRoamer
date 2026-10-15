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

**Baseline at the start** (Search Console, 29 Sep–12 Oct):
- 2 clicks, 37 impressions, average position 38.7, CTR 5.4%;
- 30 pages indexed, 162 not.
