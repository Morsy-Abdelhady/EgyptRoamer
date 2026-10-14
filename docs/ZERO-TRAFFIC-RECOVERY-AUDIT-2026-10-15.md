# Zero-traffic recovery audit (2026-10-15)

Production at the start of this audit: theme 1.2.43 / Core 1.2.29. At the end: **theme 1.2.44 / Core 1.2.30** (commit `a98ea05`), deployed, cache flushed, verified.

Each statement is labelled with what kind of claim it is:

| Label | Meaning |
|---|---|
| **[Fact]** | Verified directly: dashboard, live HTTP response, code, Git history |
| **[Data]** | A number read from a dashboard, with its date range |
| **[Diagnosis]** | A conclusion drawn from facts and data |
| **[Hypothesis]** | Plausible, not yet proven |
| **[Recommendation]** | Proposed work |
| **[Done]** | Change made and verified on production |
| **[Owner]** | Needs the account owner |

---

## 1. Executive summary

**"Zero traffic" is not a measured fact.**
- **The site has no analytics** of any kind. [Fact]
- **Google Search Console does measure search.** Over the 90-day maximum it shows **2 clicks and 37 impressions** (29 Sep–12 Oct 2026), so search traffic is not zero, but it is close to it. [Data]

**The main reason is time, not a broken site.**
- **[Fact]** Every page carried `noindex, nofollow` until the launch on **2 Oct 2026** ("Discourage search engines" was ticked on purpose until the launch gate passed).
- **[Fact]** Google crawled most pages on **29 Sep–1 Oct**, while they still said noindex, and has not been back to them since.
- **[Data]** On the last report (9 Oct), **142 pages** are "Excluded by 'noindex' tag", **20** are "Discovered – currently not indexed", and only **30 are indexed**.
- **[Diagnosis]** The site has been indexable for **13 days**, and Google still holds a stale picture of most of it.

**What the live site looks like, technically**
- **[Fact]** It is sound. A full production crawl of all 181 sitemap URLs found:
  - every URL returns 200 and is indexable;
  - every canonical points to itself;
  - every hreflang set is complete;
  - no AI or search crawler is blocked;
  - the server had no problems in the last 90 days.

**Other confirmed blockers to the business outcome**
1. **Bing had not indexed a single page** (`site:egyptroamer.com`: 0 results), and there is no Bing Webmaster Tools access. [Fact]
2. **No affiliate offer is live:** 0 providers, every offer a draft, 0 `/go/` links on the site. **Even with traffic, no affiliate click is possible.** [Fact]
   - A Viator Partner account exists and is active, but it is not connected to the site: 0 visitors over 30 days. [Fact]
3. **No external site links to Egypt Roamer:** the Search Console links report shows 0. [Data]
4. **Google showed site furniture as snippets**, for example "Trip assistant. Hello! I can point you…" and "© 2026 Egypt Roamer…". [Fact]

**Done today** [Done]
- Asked Google to **validate the noindex fix** (a recrawl of all 142 pages), started 15 Oct.
- **Requested indexing** for the four most important pages: `/ar/`, `/destinations/luxor/`, `/destinations/aswan/` and `/experiences/abu-simbel-day-trip-from-aswan/`.
- Deployed **IndexNow**. The first submission sent 181 URLs and got **HTTP 202**, and the key file is verified on production.
- Deployed **`data-nosnippet`** on the footer, menus, overlays, the assistant and the dock.

**What the owner must decide or do:** see §26.

---

## 2. Is zero traffic verified?

**No.** Each part of "zero" has a different status:

| Claim | Status |
|---|---|
| Zero human visits | **MISSING MEASUREMENT.** No analytics is installed. The only GA trace is a leftover `dns-prefetch` to googletagmanager.com, and no tag loads. |
| Zero Google search clicks | **Measured, not zero:** 2 clicks over 29 Sep–12 Oct (both from Egypt, on desktop; they could be the owner or the team). |
| Zero Google impressions | **Measured, not zero:** 37. |
| Zero Bing traffic | **NOT AVAILABLE.** There is no Bing Webmaster access, and Bing had 0 pages indexed. |
| Zero affiliate clicks | **MEASURED ZERO** (0 clicks, 15 Sep–14 Oct, in the Core click log). There is also nothing to click: no offer is live. |
| Server logs / hosting statistics | **NOT AVAILABLE** from here. GoDaddy hosting is reached through the owner. |
| AI citations | **Not cited in the 1 query tested** (ChatGPT, §19). The other platforms are **NOT TESTED**. |

---

## 3. Measurement reliability

| Metric | Source | Date range | Observed value | Reliability | Interpretation |
|---|---|---|---|---|---|
| Google organic clicks | GSC Performance (web) | 28 days / 90 days (data 29 Sep–12 Oct) | 2 / 2 | High | Real, but tiny. Both from Egypt, desktop, through the homepage and Cairo; may be internal |
| Google impressions | GSC | same | 37 | High | The site is starting to appear, mostly in Arabic |
| Average position | GSC | same | 38.7 | High | Mostly page 3–10; a few Arabic and other non-English pages are at 5–13 |
| Organic CTR | GSC | same | 5.4% | Low (n=37) | Too few impressions to judge |
| Indexed pages | GSC Page indexing (last update 9 Oct) | as of 9 Oct | 30 indexed, 162 not | High (but stale) | Before the recrawl |
| Bing clicks / impressions | Bing WMT | — | NOT AVAILABLE | — | No access; 0 pages in Bing's index |
| Sessions / users | — | — | MISSING MEASUREMENT | — | No analytics |
| Human page requests | Server logs | — | NOT AVAILABLE | — | Owner/GoDaddy |
| Affiliate redirect clicks | Core click log | 15 Sep–14 Oct | 0 | High | No live offers, so 0 by construction |
| Viator partner visitors | Viator dashboard | last 30 days | 0 | High | Not linked from the site |
| Crawl requests | GSC Crawl stats | last 90 days | 334 total, 99% 200 OK, avg 242 ms | High | Healthy host; most of it around launch |
| External links | GSC Links | all | 0 | Medium (GSC lags) | No discovered backlinks |

---

## 4. Google Search Console evidence

- **Property:** `https://egyptroamer.com/` (URL prefix). A domain property is not verified for this account. [Fact]
- **Sitemap:** `/wp-sitemap.xml`, submitted 2 Oct, last read 14 Oct, status Success, 181 pages discovered. [Data]
- **Host status:** no problems in the last 90 days. [Data]
- **Crawl stats:** 334 requests, 5 MB downloaded, 242 ms average response; by purpose, 87% refresh and 13% discovery. [Data]

**Page indexing (report of 9 Oct)** [Data]

| Reason | Pages | Notes |
|---|---|---|
| Indexed | 30 | Mostly Arabic, ES, FR, DE, IT, ZH and RU experiences and destinations crawled 2–8 Oct, plus `/`, `/destinations/cairo/`, 2 EN guides, 2 EN experiences, contact and cookies |
| Excluded by 'noindex' tag | 142 | **All last crawled 29 Sep (120), 30 Sep (2) or 1 Oct (20), before indexing was switched on (2 Oct).** Includes every language homepage except `/`, all 7 EN destinations bar Cairo, 6 EN experiences, all legal pages in 7 languages, and the guides archives |
| Discovered – currently not indexed | 20 | Not crawled yet, among them `/experiences/abu-simbel-day-trip-from-aswan/`, `/guides/best-time-to-visit-egypt/`, `/guides/hidden-gems-in-egypt/` and several ES/FR/IT/RU/ZH Aswan pages |
| Crawled – currently not indexed | 0 | — |

- **URL Inspection, `/ar/`:** "Excluded by 'noindex' tag", last crawl 29 Sep 2026. Live today: 200, `max-image-preview:large`, self-canonical. **The noindex is stale, not current.** [Fact]
- **Sitemap discovery:** URL Inspection reports "Temporary processing error" for the sitemap on inspected URLs, and "No referring sitemaps detected" for the Abu Simbel page, while the sitemap report itself says Success with 181 URLs. [Data] Likely Google-side processing lag on a new property, to be rechecked in a week. [Hypothesis]

**Intended noindex in that list.** Seven pages will stay noindex on purpose: the guides archives in DE, FR, IT, ES, RU, ZH and AR, because no guide in those languages is "Ready to index" yet. They will make the validation end as "failed" for those URLs. That is expected; the other 135 should pass. [Diagnosis]

**Search performance (all available data, 29 Sep–12 Oct)** [Data]

| Query | Impressions | Position | Language |
|---|---|---|---|
| اسوان | 6 | 33.2 | AR |
| أسوان | 3 | 12.3 | AR |
| اسوان مصر | 3 | 13.0 | AR |
| great sand sea | 2 | 92.0 | EN |
| best 7 days in egypt | 2 | 98.0 | EN |
| سيوة فين في مصر | 1 | 10.0 | AR |
| بحر الرمال العظيم في مصر | 1 | 11.0 | AR |
| معابد اسوان | 1 | 58.0 | AR |
| dove si trova hurghada | 1 | 99.0 | IT |
| barriera corallina hurghada | 1 | 100.0 | IT |
| egypt itinerary 7 days | 1 | 100.0 | EN |

- **Pages with impressions:**
  - `/ar/destinations/أسوان/`: 12 impressions, position 22.9.
  - `/ar/experiences/بحر-الرمال-العظيم-الدفع-الرباعي/`: 3 impressions, position 8.7.
  - `/ru/experiences/великое-песчаное-море-на-4x4/`: 2 impressions, position 6.0.
  - EN guides and experiences sit at positions 52–99.
- **Countries:** Egypt 17 impressions (2 clicks), USA 4, Spain 3, Belgium 2, Italy 2, then 1 each for nine others.
- **Devices:** desktop 18 (position 56), mobile 18 (position 22.6), tablet 1.

**Diagnosis** [Diagnosis]
- The only pages reaching page 1–2 are **non-English, mostly Arabic**, on low-competition queries.
- English head terms show the site at positions 50–100, behind established brands (§14).

## 5. Bing Webmaster evidence

- [Fact] **No access:** the Bing Webmaster Tools session in Chrome is not signed in.
- [Fact] `site:egyptroamer.com` on Bing (15 Oct): **"There are no results"**.
- [Fact] **Not a crawler block:** bingbot, YandexBot and the other crawlers get 200 and indexable pages (§18), and `robots.txt` names the sitemap.
- [Done] **IndexNow** is live: 181 URLs submitted 15 Oct 02:37, answer 202. The key file is verified on production.
- [Owner] **Sign in to Bing Webmaster Tools** and add the site with **"Import from Google Search Console"**. This verifies ownership and brings in the sitemap. It then shows IndexNow receipts, crawl state and the AI Performance report.

## 6. Analytics findings

**What exists** [Fact]
- `assets/track.js` pushes `affiliate_click`, `filter_use` and other events into `window.dataLayer`, but nothing consumes them.
- `<link rel='dns-prefetch' href='//www.googletagmanager.com'>` is still in the head, with no tag behind it.
- The Privacy Policy states: "we do not use advertising or analytics trackers on this site".

**Diagnosis** [Diagnosis]
- Visits cannot be measured today. Search Console, which is reliable, is the only traffic source.

**Options** [Owner]: any of them needs the Privacy Policy changed, in 8 languages.
1. **Cookieless, aggregate page counter in Core** (smallest change, my recommendation):
   - **What it records:** a `sendBeacon` per page view with path, language and referrer host, kept as daily counts. No cookie, no IP stored, bots filtered.
   - **Why it fits:** it matches how partner clicks are already recorded and described in the policy.
   - **What it needs:** one policy sentence per language. I can draft the English; the translations need your usual review.
2. **GA4 with a consent banner.** Richer data, but it needs a consent mechanism and changes to the cookie and privacy policies. Visitors who decline are invisible.
3. **GoDaddy / CDN statistics,** if your hosting plan shows them: check the GoDaddy dashboard.

**Not done:** no tracking was added, because it would contradict the published Privacy Policy.

## 7. Server / log evidence

**NOT AVAILABLE.** Server access logs and GoDaddy or CDN statistics are behind the owner's hosting account. They are not needed for the current diagnosis: Search Console's crawl stats already show a healthy host.

## 8. Crawlability [Fact]

Production, 15 Oct, with the user agents of Googlebot, bingbot, OAI-SearchBot, GPTBot, PerplexityBot, ClaudeBot and YandexBot:
- `/` and `/destinations/luxor/` return 200 with `max-image-preview:large` for every one of them; no 403, 429 or challenge was seen.
- **robots.txt:** disallows `/wp-admin/` and `/go/`, allows `admin-ajax.php`, and names the sitemap. It has no AI-specific rules.
- **Sitemap index:** 33 child sitemaps (pages, destinations and experiences in 8 languages, EN guides, archives in 8 languages), **181 URLs**.
- **Full crawl of all 181:**
  - every URL returns 200 with no noindex and a self canonical;
  - 25 hreflang links on each page (26 on some archives, 16 on EN-only guides);
  - no wrong-language links.
- **Cloudflare (via GoDaddy)** challenges rapid scripted requests from one IP; this was seen on 5 Oct during my polling. Real crawlers are unaffected: GSC reports no host problems and 99% 200s.

## 9. Indexability [Fact]

| Rule (Core `seo.php`) | Effect | Pages |
|---|---|---|
| Not "Ready to index" | noindex, out of the sitemap | none of the 181 |
| Internal search | noindex | intended |
| Filter or sort query strings | noindex | intended |
| Archives with no indexable item in that language | noindex | `/{de,fr,it,es,ru,zh,ar}/guides/` (intended) |

## 10. Search impressions and queries

See §4. In short:
- 37 impressions, 11 queries.
- 7 of the 11 queries are Arabic: Aswan, Siwa and the Great Sand Sea.
- English queries sit beyond position 90.

## 11. Results by language [Data]

| Language | Indexed pages (9 Oct) | Page impressions* | Best page position | Notes |
|---|---|---|---|---|
| EN | 8 (`/`, Cairo, 2 guides, 2 experiences, contact, cookies) | 18 | 2.0 (homepage, probably brand) | Head terms at 52–99 |
| AR | 8 | 21 | 5.0 | The strongest; Arabic SERPs are weaker (§14) |
| ES | 4 | 2 | 6.0 | |
| RU | 3 | 2 | 6.0 | |
| FR | 2 | 0 | — | |
| DE | 2 | 0 | — | Homepage noindex until recrawl |
| IT | 2 | 2 | 99.5 | |
| ZH | 1 | 0 | — | Length not measurable by word count (no spaces in Chinese); not checked |

\* By page, so the sum differs from the 37 query impressions (Search Console counts these separately).

## 12. Technical SEO problems (confirmed)

| ID | Severity | Evidence | Root cause | Fix | Status |
|---|---|---|---|---|---|
| T1 | **P0** | 142 URLs "Excluded by noindex", last crawled 29 Sep–1 Oct | Pre-launch noindex seen by Google before indexing opened on 2 Oct | Validate fix; request indexing for priority URLs | **[Done]** validation started 15 Oct; 4 URLs requested |
| T2 | **P0** | `site:egyptroamer.com` on Bing = 0 | New site, no Bing WMT, no IndexNow | IndexNow in Core | **[Done]** 181 URLs, 202 |
| T3 | **P0 (measurement)** | No analytics | Deliberate (privacy policy) | Owner decision (§6) | **[Owner]** |
| T4 | P1 | Google snippets used footer and assistant text | Boilerplate text in every page's HTML | `data-nosnippet` on the furniture | **[Done]** 1.2.44 |
| T5 | P2 | URL Inspection: "Temporary processing error" for the sitemap | Likely Google-side lag | Recheck 22 Oct; resubmit if it persists | Watch |
| T6 | P3 | Meta descriptions 180–215 characters on EN destinations, experiences and guides | Excerpts used as descriptions | Shorten in the editorial source (8 languages) | Later |
| T7 | P3 | Leftover `dns-prefetch` to googletagmanager.com | Site Kit residue | Remove (harmless) | Later |

**No confirmed bugs** in canonical, hreflang, sitemap, robots, status codes or internal links.

## 13. Content gaps

**Inventory** [Fact]
- 7 destinations and 8 experiences in 8 languages.
- 4 EN guides (5 guide URLs in the crawl, counting the `/guides/` archive).
- No guides in the other 7 languages.

**Gaps** [Diagnosis]
- **Planning topics that searchers ask about** (visa, safety, money, the Luxor–Aswan Nile cruise; `SEO-GROWTH-OPPORTUNITIES-2026-10-03.md`) **are still missing.** They need verified, dated sources: the visa rules, the US/UK government travel advice, the official ticket prices on egymonuments.gov.eg, which ChatGPT already cites (§19).
- **Arabic planning content is the most achievable gap.**
  - The Arabic SERPs are weak (Facebook posts, small sites).
  - The Arabic pages already rank at 5–13 for low-competition queries.
  - The owner writes Modern Standard Arabic natively, so quality control is feasible.

## 14. Competitor analysis (Google, 15 Oct, live results) [Data]

| Query | Top results | Difficulty for ER [Diagnosis] |
|---|---|---|
| abu simbel day trip from aswan (EN, US) | tripadvisor.com, viator.com, getyourguide.com, emotoursegypt.com, egypttoursportal.com, adventures-abroad.com, facebook.com, nicetours.net | Very high: booking marketplaces plus local tour operators |
| best time to visit egypt (EN, US) | audleytravel.com, intrepidtravel.com, insightvacations.com, lonelyplanet.com, egypttoursplus.com, adventure-life.com, cntraveler.com | Very high: established travel brands |
| رحلة أبو سمبل من أسوان (AR, EG) | getyourguide.com, booking.com, facebook.com ×2, eg.wego.com, haqqitours.com, lokalee.app, marsaalamtours.com | Medium: few editorial answers, two Facebook posts in the top 6 |
| اسوان (AR, EG) | ar.wikipedia.org, tripadvisor.com.eg, aswan.gov.eg, facebook.com, agazatmasr.com, sis.gov.eg, youm7.com | Medium-high for the head term; long-tail Arabic questions are open |

[Diagnosis]
- A 13-day-old domain with no links will not outrank Lonely Planet or TripAdvisor on English head terms.
- The realistic path is long-tail, question-shaped queries, Arabic first, with pages that answer better than the Facebook posts and small sites now ranking.

## 15. Internal-link analysis [Data]

- **Average inbound in-content links per page:**
  - EN destinations: 10.4;
  - EN experiences: 4.9;
  - EN guides: 7.8;
  - homepages: 21–26;
  - other languages: destinations 7.7, experiences 3.6.
- **No orphan pages.** Contact pages are linked only from the footer, as intended.
- **Experiences have the weakest internal linking** (3.6 in non-English languages).

[Recommendation, P2] Link each destination to its experiences in every language. Most already are; check the non-English pages with fewer than 4 inbound links.

## 16. Backlink / authority analysis

- [Data] GSC "External links: Total 0".
- [Fact] A `"egypt roamer"` search shows the site's own pages, plus an unrelated T-shirt product ("Power of Egypt Roamer", bordering.co.uk).
- [Diagnosis] There is no external authority yet.

[Recommendation] A legitimate plan; no bought links, no directories:
1. **Social profiles:** create the brand's own profiles and link them both ways. This gives the brand entity and `sameAs` links.
2. **Original planning resources** that others can cite, such as an Arabic Abu Simbel sun-festival planner with official sources.
3. **Partnerships** with Egypt-based guides and operators who would link to an honest guide.
4. **Viator** (the existing partner account): its Shop page can link back to the site.

## 17. Design and UX issues

- The site was audited visually many times before launch (`VISUAL-AUDIT-*`, `UI-UX-*`).
- No new evidence suggests design is what limits traffic. **No redesign is warranted for SEO now.**
- The real conversion blocker is **no live affiliate offers** (§1).

## 18. AI crawler status [Fact]

| Crawler | robots.txt | Live response |
|---|---|---|
| OAI-SearchBot (ChatGPT search) | allowed (no rule) | 200, indexable |
| GPTBot (OpenAI training) | allowed (no rule) | 200 |
| PerplexityBot | allowed | 200 |
| ClaudeBot | allowed | 200 |
| bingbot (feeds Copilot and partly ChatGPT) | allowed | 200, but **0 pages indexed** |
| Googlebot (AI Overviews use the normal index) | allowed | 200 |

[Recommendation]
- **Keep OAI-SearchBot allowed:** it is what makes ChatGPT search able to cite the site.
- **GPTBot (training) is a business choice** [Owner]. Allowing it has no proven effect on citations. Blocking it would not affect search. Current state: allowed.
- The biggest AI-visibility lever is **being in Bing's index**: this is what IndexNow and Bing WMT address.

## 19. AI citation benchmark (15 Oct)

| Date | Platform | Language | Query | ER mentioned | ER cited | Cited sources | Verified |
|---|---|---|---|---|---|---|---|
| 2026-10-15 | ChatGPT (web search, temporary chat) | EN | Is the Abu Simbel day trip from Aswan worth it? | No | No | egymonuments.gov.eg (Ministry of Tourism and Antiquities) | Yes. The temporary chat can still read the account's memory, which is a possible bias, but ER was not cited |
| 2026-10-15 | ChatGPT | AR | هل تستحق رحلة أبو سمبل من أسوان؟ | — | — | — | **NOT TESTED** (the UI did not send the query) |
| 2026-10-15 | Perplexity | EN | same as above | — | — | — | **NOT TESTED** (requires sign-in) |
| — | Google AI features, Bing/Copilot | — | — | — | — | — | **NOT TESTED.** Copilot cannot cite the site while Bing has 0 pages indexed |

The full 110-query benchmark is in `AI-VISIBILITY-BASELINE-2026-10-15.md`, with the plan to run it.

## 20. Root causes (ranked)

1. **[Diagnosis] Age and the indexing switch.** The site was noindex until 2 Oct. Google's picture of 142 of its pages dates from the noindex days. Bing had nothing.
2. **[Fact] No measurement** of visits, so "zero" could not be checked.
3. **[Data] No authority:** 0 external links, a brand-new domain.
4. **[Diagnosis] Competition:** English head terms are owned by marketplaces and big publishers.
5. **[Fact] No monetisation path:** no live offers, so traffic would not turn into affiliate clicks yet.

## 21. Fixes implemented [Done]

| Fix | Where | Verified |
|---|---|---|
| GSC validation of "Excluded by noindex" (142 URLs) | GSC | "Validation started 10/15/26" shown |
| Indexing requested: `/ar/`, `/destinations/luxor/`, `/destinations/aswan/`, `/experiences/abu-simbel-day-trip-from-aswan/` | GSC URL Inspection | "Indexing requested" confirmation for each |
| IndexNow (Core `includes/indexnow.php`) | production | Dashboard: "IndexNow: 181 URLs sent on 2026-10-15 02:37, answer 202"; key file fetched from production and matches |
| `data-nosnippet` on footer, menus, overlays, assistant, dock labels | theme `footer.php` | 12 occurrences in the production homepage HTML |
| Dashboard health line for IndexNow; "Fix" link only when there is something to open | Core `settings.php` | seen in wp-admin |

## 22. Files and configuration changed

- `wordpress/wp-content/plugins/egypt-roamer-core/includes/indexnow.php` (new)
- `wordpress/wp-content/plugins/egypt-roamer-core/egypt-roamer-core.php` (include; version 1.2.30)
- `wordpress/wp-content/plugins/egypt-roamer-core/includes/settings.php` (health line)
- `wordpress/wp-content/themes/egypt-roamer/footer.php` (`data-nosnippet`)
- `style.css`, `functions.php` (1.2.44), `CHANGELOG.md`
- **Production options created by the code:** `er_indexnow_key`, `er_indexnow_round`, `er_indexnow_last`.
- **Search Console:** a validation started and 4 indexing requests. Nothing else changed: no settings, no removals.

## 23. Tests

- PHP lint on every changed file.
- CI checks run locally: bundles match their sources, editorial and legal data are up to date, `git diff --check`, LF line endings.
- Local:
  - the key file answers `200 text/plain` with the key;
  - the URL list covers items, language homepages and indexable archives;
  - nothing is sent while `blog_public = 0`;
  - `/`, `/ar/` and `/destinations/luxor/` render with `data-nosnippet` and no PHP errors.

## 24. Deployment

- **Commit:** `a98ea05` "fix(seo): IndexNow for Bing, nosnippet on site furniture (theme 1.2.44 / Core 1.2.30)", pushed to `main`, which runs the deploy workflow.

## 25. Production verification

- **Homepage:** `track.js?ver=1.2.30`, 12 `data-nosnippet`.
- **Edge cache:** flushed through the admin bar.
- **IndexNow:** dashboard shows 181 URLs sent, answer 202; key file KEYFILE_OK.

## 26. Remaining blockers and owner actions

| # | Action | Why |
|---|---|---|
| 1 | **Bing Webmaster Tools:** sign in at bing.com/webmasters, Add site, Import from Google Search Console | Bing has 0 pages indexed; it unlocks Bing data and the AI Performance report |
| 2 | **Analytics decision** (§6): cookieless counter (recommended) or GA4 with consent | Without it, visits stay unmeasured |
| 3 | **Affiliate:** connect the existing Viator Partner account. Pick the Viator products that match each experience, create the provider in Core (allowed domains) and publish the offers. I can do the wiring once you confirm which products to use | No live offers means 0 possible revenue |
| 4 | Optional: **GSC domain property** (DNS TXT) | Covers http/www variants; not urgent |
| 5 | **Recheck on 22 Oct:** validation progress, the sitemap "Temporary processing error", newly indexed counts | Recrawl takes days to weeks |

## 27. 30 / 60 / 90-day roadmap

**Days 0–30: get indexed and measured**
- Watch the GSC validation and the indexed count weekly (target: the 174 non-intentional pages indexed).
- Owner: Bing WMT, analytics decision, Viator wiring.
- Publish the first **Arabic planning pieces** where the SERP is weak (Abu Simbel from Aswan, Aswan temples, Siwa location and access), each with verified, dated sources.
- Shorten the meta descriptions in the editorial source (P3).

**Days 30–60: grow impressions where they are already appearing**
- Use the first 30 days of GSC query data by language to pick pages to deepen. Today Arabic Aswan, Siwa and the Great Sand Sea lead.
- English: target long-tail questions, not head terms.
- Start the legitimate authority plan (§16).

**Days 60–90: clicks and affiliate intent**
- Measure CTR on pages with more than 100 impressions and improve their titles and descriptions.
- Offers live on the pages with traffic; read the Core click reports alongside GSC.
- Run the AI benchmark again (§19) once Bing indexes the site.

**What not to do:** no mass articles, no literal translations as SEO pages, no bought links, no repeated indexing requests, no AI-only files.
