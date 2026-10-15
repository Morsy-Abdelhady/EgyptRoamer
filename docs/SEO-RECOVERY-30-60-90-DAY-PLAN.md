# SEO recovery: 30 / 60 / 90-day plan (from 2026-10-15)

**Goal:** move Egypt Roamer through each stage and measure every stage separately:

> discovery → indexing → impressions → competitive positions → organic clicks → engaged visits → affiliate clicks → commission opportunity

No rankings or visitor numbers are promised. Each step below has a measurement and a decision rule.

## Where we start (15 Oct, verified)

| Stage | State | Source |
|---|---|---|
| Discovery | Google: sitemap of 181 URLs read. Bing: imported 15 Oct, 100 URLs submitted, IndexNow 181 URLs (202) | GSC, Bing WMT |
| Indexing | Google: 30 indexed; 142 stale "noindex" (crawled 29 Sep–1 Oct, validation started 15 Oct); 20 discovered. Bing: homepage last crawled 27 Sep while noindex; 0 indexed | GSC, Bing URL Inspection |
| Impressions / positions | 37 impressions, average position 38.7, 29 Sep–12 Oct. Best: Arabic Aswan / Siwa / Great Sand Sea queries, positions 5–13 for some pages | GSC |
| Organic clicks | 2 (29 Sep–12 Oct) | GSC |
| Engaged visits | **Measured from 15 Oct only** (first-party totals) | Egypt Roamer → Visits |
| Affiliate clicks | Path live since 15 Oct: 7 Viator offers. 11 QA clicks on 15 Oct | Click reports, Viator |
| AI citations | 1 test query, not cited; others not tested | `AI-VISIBILITY-BASELINE-2026-10-15.md` |

## Days 0–30 (to 14 Nov): get indexed, prove measurement

| When | Action | Measure | Decision rule |
|---|---|---|---|
| 16 Oct | Submit the remaining 49 URLs in Bing (other-language experiences and archives) | Bing URL Submission list | — |
| 17 Oct | Viator dashboard: Visitors > 0? | Viator Performance | If still 0 after the owner's own test click, check the link with Viator support |
| 18 Oct | Bing URL Inspection on `/`, `/ar/`, `/destinations/luxor/` | "Indexed" vs still noindex | If still showing the 27 Sep crawl, request indexing again for those 3 only |
| 22 Oct | GSC: validation progress; indexed count; pages per language; sitemap status | Page indexing report | If validation "Failed" on some URLs, inspect 3 of them live and fix the cause; never re-request all |
| 22 Oct | Visits: first 7 days. Search entrances vs GSC clicks for the same days | Visits, GSC | If search entrances are 0 while GSC shows clicks, debug the beacon on that landing page |
| weekly | Update `SEO-QUERY-MAP-ALL-LANGUAGES.md` with GSC queries by language | GSC | — |
| by 31 Oct | **Arabic planning content, first piece.** One substantial guide where the Arabic results page is weakest and we already show: Abu Simbel from Aswan (how to get there, the convoy and flight options, timing). Verified, dated sources only; native review before publishing | GSC impressions for that cluster | Publish only after native review |
| by 14 Nov | Owner picks verified Viator products, one per experience; swap the Target URLs | Click → booking rate in Viator | — |

**Success at day 30** is not traffic. It is three things:
- ≥ 150 of the 174 intended pages indexed in Google;
- Bing has indexed the homepage and the language homes;
- Visits show non-zero search entrances that roughly agree with GSC.

## Days 30–60 (to 14 Dec): grow impressions where we already appear

1. **Pick pages from data, not guesses.**
   - Take the GSC queries per language with impressions over 28 days.
   - Work first on pages ranking 5–20 that have > 20 impressions. For these:
     - improve the title and description to match the query wording;
     - add the missing answer the results page shows;
     - add internal links from related destinations and guides.
2. **Arabic second piece** (Siwa: location, access, best season), only if piece 1 earns impressions.
3. **English:** long-tail questions only. Examples:
   - "Abu Simbel road or flight";
   - "sun festival Abu Simbel dates";
   - "Egypt in August".

   These are month- or question-specific, with sourced answers. **No head terms.**
4. **Gaps from the 110-question dataset,** clustered and only where there is a verified authoritative source:
   - Egypt visas (official e-Visa portal);
   - safety (government travel advisories, linked and dated, no statistics of our own);
   - money and costs;
   - Luxor–Aswan Nile cruise planning.

   Each is **one substantial page**, not many thin ones.
5. **Authority, legitimately.** One original, citable resource, e.g. a maintained table of verified opening times and ticket sources for the major sites, with sources and a "checked on" date. Offer it to relevant travel forums and Egyptian tourism communities where linking is welcome. **No bought links.**

**Decision at day 60:** if a language has 0 impressions after 60 indexed days, stop creating content for it. Keep its pages and put the effort into the languages that respond.

## Days 60–90 (to 13 Jan): clicks and affiliate intent

1. **CTR work:** pages with > 100 impressions and a CTR below the site median get a title and description test. Compare 28 days before and after.
2. **Conversion work:** compare views to partner taps on each experience page (Visits + Click reports).
   - Pages with views but no taps: is the offer relevant? is the CTA visible on mobile?
   - Pages with taps but no Viator bookings: is the product a good match?
3. **AI benchmark, round 2:**
   - 16 queries from the dataset (2 per language), on ChatGPT search, Copilot, Google AI Mode and Perplexity (if signed in);
   - plus the Bing WMT **AI Performance** report.
   - Record citations, not impressions.
4. **Review this plan:** keep what moved a stage, drop what did not.

## What we will not do

- Mass-produce articles or literal translations as SEO pages.
- Buy links or use link schemes.
- Fabricate reviews, ratings, prices or statistics.
- Repeatedly request indexing for every URL.
- Add AI-only files or tricks.
- Add tracking that contradicts the privacy policy.

## Reporting

**Weekly (one line each in `SEO-RECOVERY-CHANGELOG.md`):**
- GSC clicks, impressions, CTR, average position (7 days and 28 days);
- indexed count;
- Bing indexed count;
- Visits page views, entrances and search entrances;
- partner taps and `/go/` clicks;
- Viator visitors and bookings.

Use 7- and 28-day windows; 90-day once available. Never judge from one day.
