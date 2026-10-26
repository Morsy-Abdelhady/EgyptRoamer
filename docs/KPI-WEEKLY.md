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
| Viator clicks / bookings | 20 Oct (last 30 days): 1 visitor, 0 bookings, $0 | — |
| Content | 19 pages per language × 8 at baseline. End of 15 Oct: 26 pages per language (7 destinations, 10 experiences, 9 guides) and 17 live Viator offers. Later on 15 Oct: 31 pages per language (14 guides), then 36 (19 guides), then 41 (24 guides, night of 15–16 Oct) | — |
| Release gate | all ok: 160 pages at baseline, 216, then 240, then 296 (all on 15 Oct), then 336 (16 Oct). From the third batch on, the gate follows archive pagination: before that it missed the guides past page 1 of `/guides/`, so 240 was an undercount (those pages were not checked) | — |

> **Booking path, 15 Oct evening (theme 1.2.55 → 1.2.62):** after the owner's Hurghada report, every destination, experience and guide gets a way to book while reading:
> - a sidebar booking box first on wide screens, and an in-article box on phones;
> - the header's and the phone menu's gold buttons go to the page's booking box;
> - a booking bar: centred on wide screens; on phones above the dock, from the first screen.
>
> Partner taps before and after this date are not comparable.

> **Second partner, 16 Oct:** 9 GetYourGuide offers are live, alongside Viator on the same experiences (no food tour). Their campaigns are `er-{lang}-{placement}`, reported in the GetYourGuide partner portal under "cmp". Compare GetYourGuide and Viator clicks per experience from this date.

> **QA note (15 Oct):** a link check followed a few partner links in the new guides by mistake. Discount them:
> - **Where:** the booking boxes of the Aswan temples, desert choice and Valley of the Kings guides, EN and AR.
> - **Campaigns affected:** `er-en-guide-inline` and `er-ar-guide-inline`.
> - **Where they show up:** `/go/` redirects in Visits, and clicks in Viator for those two campaigns.
> - **Fix:** the script now skips `/go/` links.

The first queries with impressions were "اسوان", "أسوان", "great sand sea", "best 7 days in egypt" and "سيوة فين في مصر".

### Week 1, 16–22 Oct 2026 (review of 22 Oct)

| Metric | Value | Notes |
|---|---|---|
| Search impressions (GSC, domain property) | 271 (28 days); the 7-day view shows the same total, so nearly all came this week | 20 Oct check: 109. Baseline 15 Oct: 37 (URL-prefix property). GSC data reaches 19 Oct |
| Search clicks (GSC) | 2 (28 days), CTR 0.7%, average position 61.2 | Both clicks on guides: DE inside the pyramids (pos 21.7), ES Siwa or the White Desert (31.5) |
| Queries | 126 with impressions; 7 in the top 30: "great sand sea" (8.0), "egypt red sea" (10), "snorkelling" (10), "اين يقع بحر الرمال العظيم" (12), "abu simbel trip from aswan" (15), "sokar pyramiden" (28), "the red sea" (30) | Most impressions are itinerary and snorkelling queries at positions 65–100 |
| Pages indexed (GSC Page indexing, URL-prefix property) | 30 indexed, 162 not indexed (142 "noindex", validation still **Started**; 20 "Discovered – not indexed") | The report's data has not moved since about 8 Oct; the domain property says "Processing data" |
| Indexing checks | `/guides/abu-simbel-from-aswan/` and `/guides/day-trips-from-cairo/` (requested 15 Oct): **indexed** | — |
| Page views (Visits) | 430 | 241 entrances, 2 from search engines (google.com), 0 from AI assistants. Mostly `/experiences/` in 8 languages and `/` |
| Partner-link taps | 0 | — |
| `/go/` redirects | 53 | With 0 taps on the page and 1 Viator visitor, these are mostly not people (crawlers following `/go/`, or tests). Worth checking which user agents hit `/go/` |
| Viator (last 30 days) | 1 visitor, 0 bookings, $0 commission | Payout details still not entered (owner) |
| Content | 384 pages (232 guides): 5 new guides in 8 languages published 22 Oct | Google indexing requested for the 5 English guides |
| Release gate | all ok, 384 pages | theme 1.3.15, Core 1.2.65 |

### Week 2, 19–25 Oct 2026 (read 26 Oct)

> **Counter change on 24 Oct (Core 1.2.67):** from then on a page view counts only after the visitor interacts, and a
> `/go/` redirect only when someone clicked the link on our page. 19–23 Oct still include bots; compare only 24 Oct on.
> Logged-in users were excluded from `/go/` only from 26 Oct (Core 1.2.70), so 24–25 Oct redirects can include our own
> QA clicks (the Klook release was tested on 25 Oct).

| Metric | Value | Notes |
|---|---|---|
| Search impressions (GSC, domain property, 19–25 Oct) | 664 | Week 1: 271 (28-day view). 283 queries with impressions |
| Search clicks (GSC) | 2, CTR 0.3%, average position 66 | 28 days to 24 Oct: 3 clicks, 790 impressions, position 64.1 |
| Top queries (week) | "aswan to luxor cruise" 20, "egypt itinerary" 13, "egypt trip itinerary" 11, "snorkeling in hurghada" 10, "great sand sea" 9, "croisière assouan louxor" 9; all 0 clicks | Itinerary, Nile cruise and snorkelling demand; French cruise query appears |
| Top pages (28 days) | EN snorkelling guide 81 impressions; EN inside the pyramids 73; DE snorkelling 51; EN 14 days 37; EN Mount Sinai 32; AR Great Sand Sea 25; Nile cruise trip 25 | Clicks: DE inside the pyramids 1, ES Siwa or White Desert 1, EN Nile cruise or hotels 1 |
| Bing | 0 clicks, 0 impressions (17–24 Oct); Site Explorer 0 indexed URLs | Homes submitted 24 Oct; check 31 Oct |
| Page views (Visits, 19–25 Oct) | 278; 132 entrances; 2 from search (google.com); 0 from AI | Mostly the 8 `/experiences/` archives and "tablet" before 24 Oct: bots |
| Page views, cleaned counter only (24–25 Oct) | **21**; 13 entrances; 0 from search or AI | Mobile 16, tablet 3, desktop 2. Landing pages: experience archives (ES, AR, DE), balloon trip (EN, AR), Nile dinner cruise (AR), Abu Simbel and snorkelling guides |
| Partner-link taps | 0 | — |
| `/go/` redirects | 77 (19–25 Oct); 17 on 24–25 Oct | 24–25 Oct likely include our own logged-in QA |
| Viator (last 30 days) | 1 visitor, 0 bookings, $0 | The dashboard still shows "Enter your payout details" on 26 Oct |
| GetYourGuide (last 30 complete days) | 3 visitors, 0 bookings, $0 | — |
| Klook (last 7 days) | 1 click, 0 tickets, $0 | Live since 25 Oct |
| Trip.com (18–25 Oct) | 3 clicks, 3 unique visitors, 0 bookings | — |
| Content | 392 pages (232 guides, 96 experiences, 56 destinations, 8 homes) | Balloon trip added 25 Oct |
| Release gate | all ok, 392 pages (25 Oct and 26 Oct) | Theme 1.3.19, Core 1.2.70 on 26 Oct |

**Read:** impressions grew (664 in one week vs 271 in the first), but the average position (66) still gives almost no
clicks. Real visitors are about 10 page views a day. No partner has a booking yet. Growth plan checkpoint for
Jan 2027: 10,000 sessions a month (stretch) or 5,000 (safer).

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
