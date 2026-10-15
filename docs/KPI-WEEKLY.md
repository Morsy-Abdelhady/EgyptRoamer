# Weekly KPIs

Fill in one row every Monday (UTC), for the 7 days that end on Sunday. One question: did the funnel move?

## Funnel and sources

| # | Metric | Where to read it |
|---|---|---|
| 1 | Search impressions | Search Console → Performance (property `https://egyptroamer.com/`), last 7 days; Bing Webmaster Tools → Search Performance |
| 2 | Search clicks | Same reports |
| 3 | Page views (real) | wp-admin → Egypt Roamer → Visits (QA traffic is excluded via `er-nocount`; QA days are noted below) |
| 4 | Entrances from search / AI assistants | Same Visits page |
| 5 | Partner-link taps | Visits page ("partner-link taps on the page") |
| 6 | `/go/` redirects | Visits page ("partner redirects logged by /go/"). Our own redirect tests are included: note them |
| 7 | Viator clicks | Viator partner dashboard, by `campaign` (`er-{lang}-{placement}`) |
| 8 | Viator bookings and commission | Viator partner dashboard |
| 9 | Content: published pages per language, live offers | wp-admin lists; `tools/qa/page-coverage.mjs` prints the page count |
| 10 | Release gate | `BASE=https://egyptroamer.com node tools/qa/page-coverage.mjs` → "all ok" |

## Weekly log

### Baseline, 15 Oct 2026

| Metric | Value | Notes |
|---|---|---|
| Search impressions (GSC) | 37 | 28 days to 12 Oct |
| Search clicks (GSC) | 2 | Same period; average position 38.7 |
| Search impressions and clicks (Bing) | — | Imported 15 Oct; IndexNow sent 181 URLs |
| Page views (real) | 0 measured | The counter started 15 Oct; its 29 views that day are QA |
| Partner-link taps | 0 | — |
| `/go/` redirects | 41 | All QA or redirect tests |
| Viator clicks / bookings | to read 17 Oct | — |
| Content | 19 pages per language × 8 at baseline. End of 15 Oct: 26 pages per language (7 destinations, 10 experiences, 9 guides) and 17 live Viator offers. Later on 15 Oct: 31 pages per language (14 guides), then 36 (19 guides) | — |
| Release gate | all ok: 160 pages at baseline, 216, then 240, then 280 (all on 15 Oct) | — |

> **QA note (15 Oct):** a link check followed a few partner links in the new guides by mistake. Discount them:
> - **Where:** the booking boxes of the Aswan temples, desert choice and Valley of the Kings guides, EN and AR.
> - **Campaigns affected:** `er-en-guide-inline` and `er-ar-guide-inline`.
> - **Where they show up:** `/go/` redirects in Visits, and clicks in Viator for those two campaigns.
> - **Fix:** the script now skips `/go/` links.

The first queries with impressions were "اسوان", "أسوان", "great sand sea", "best 7 days in egypt" and "سيوة فين في مصر".

## Speed (PageSpeed Insights, mobile), 15 Oct

| Page | Performance | LCP | CLS | TBT | Accessibility / Best practices / SEO |
|---|---|---|---|---|---|
| Home | 82 | 3.0 s | 0.003 | 150 ms | 100 / 100 / 100 |
| Experience (Nile dinner cruise) | 92 | 3.1 s | 0.001 | 0 ms | 100 / 100 / 100 |
| Guide (best time to visit) | 87 | 3.5 s | 0 | 20 ms | 100 / 100 / 100 |

- **LCP:** above the 2.5 s "good" line on every page tested. The LCP element is the hero photo, hot-linked from images.unsplash.com.
- **Already in place:** preconnect, `fetchpriority="high"`, eager loading and responsive sources.
- **Remaining gain:** own featured images served from our domain/CDN (PM review, recommendation 9).
- **Home Speed Index:** 13.9 s, which comes from the animated intro.
