# SEO recovery changelog

Every search-recovery action, with what it is expected to do and how to check it.

## 2026-10-15

| Change | Where | Verified | Expected effect | Check on |
|---|---|---|---|---|
| Started "Validate fix" for "Excluded by 'noindex' tag" (142 URLs, all last crawled 29 Sep–1 Oct, before indexing opened on 2 Oct) | Search Console | "Validation started 10/15/26" | Google recrawls them and indexes about 135. The 7 non-English guides archives stay noindex by design | 22 Oct, 29 Oct |
| Requested indexing: `/ar/`, `/destinations/luxor/`, `/destinations/aswan/`, `/experiences/abu-simbel-day-trip-from-aswan/` | Search Console | "Indexing requested" for each | Priority crawl | 22 Oct |
| IndexNow in Core 1.2.30: all 181 indexable URLs sent once, then each change | Core `includes/indexnow.php`, commit `a98ea05` | Dashboard "181 URLs sent… answer 202"; key file verified | Bing (and Yandex, Seznam, Naver) discover and index the site | `site:egyptroamer.com` on Bing, from 22 Oct |
| `data-nosnippet` on the footer, menus, overlays, assistant and dock labels | theme 1.2.44 `footer.php` | 12 occurrences in production HTML | Snippets come from page content or the meta description, not "Trip assistant…" or "© 2026…" | The brand search on Google after the recrawl |

| Bing Webmaster Tools: site imported from GSC (owner verified via Google), sitemap present ("Success", 33 URLs discovered) | Bing WMT | Sitemaps page | Bing crawls the sitemap | 22 Oct |
| Bing URL Inspection `/`: **"Not indexed due to NOINDEX directive", last crawl 27 Sep** (stale, pre-launch); live test "can be indexed" → Request indexing | Bing WMT | "URL submitted successfully" | Recrawl of the homepage | 18 Oct |
| Bing URL Submission: 99 priority URLs (7 language homes, all EN destinations/experiences/guides/archives, all AR destinations/experiences, other-language destinations, DE + 3 FR experiences). **Remaining 49 (other-language experiences)**: submit on 16 Oct: the FR experiences after the first 3, and all IT, ES, RU and ZH experiences, plus the remaining language archives (sitemap URLs not in the list above) | Bing WMT | Submitted list, "Today at 03:01" | Bing indexes the priority set | 18 Oct, 22 Oct |
| First-party page-view totals (Core 1.2.31, `8f4446c`) and Privacy Policy in 8 languages | Core, legal sync | Production: 1 labelled view recorded, "Measuring since 2026-10-15"; sync updated 8 pages | Visits become measurable | daily for 7 days |
| Viator provider + 7 offers published; `/go/` → Viator with `pid/mcid/medium/campaign` | wp-admin | 10 pages × redirect chain checked; 11 QA clicks logged | First affiliate path from organic pages | 17 Oct (Viator dashboard) |
| Disclosure next to partner links in card grids and the homepage rail (theme 1.2.45, `6e32619`) | theme | Production: home, /ar/, /zh/experiences/, search, Great Sand Sea show the disclosure | Compliance | — |

**Test data on production (15 Oct):** 1 page view (`utm_source=er-qa`) and 11 `/go/` clicks. Exclude them from 15 Oct numbers.

**Baseline at the start** (Search Console, 29 Sep–12 Oct):
- 2 clicks, 37 impressions, average position 38.7, CTR 5.4%;
- 30 pages indexed, 162 not.
