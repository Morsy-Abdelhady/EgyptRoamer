# AI visibility baseline (2026-10-15)

## Eligibility (verified on production, 15 Oct)

| Requirement | State |
|---|---|
| OAI-SearchBot (ChatGPT search) can fetch pages | Yes: 200, indexable, no robots rule |
| GPTBot (OpenAI training) | Allowed (no robots rule). Owner choice; it does not affect ChatGPT search citations |
| PerplexityBot, ClaudeBot | Allowed, 200 |
| Bing index (feeds Copilot; one of the sources some AI search tools use. Each platform is assessed on its own; none is assumed to depend only on Bing) | **0 pages** (`site:egyptroamer.com`, 15 Oct). IndexNow submitted 181 URLs on 15 Oct (answer 202). Bing WMT imported 15 Oct: the homepage's last Bing crawl was **27 Sep, while it was noindex**; indexing requested, plus 99 priority URLs |
| Google index (AI Overviews use it) | 30 pages (report of 9 Oct). Recrawl of 142 requested on 15 Oct |
| Server-rendered text, canonical, hreflang, sitemap | OK on all 181 URLs |
| Structured data | WebPage, TouristTrip, BreadcrumbList (5 valid breadcrumbs in GSC); validated in earlier audits |

**What matters:** Google states that its AI features use the normal index and the usual SEO basics. There is no special AI file to add. The levers are being indexed (Google and Bing), useful pages, and authority.

## Benchmark

| Date | Platform | Lang | Query | ER mentioned | ER cited | Cited sources | Notes |
|---|---|---|---|---|---|---|---|
| 2026-10-15 | ChatGPT (web search, temporary chat) | EN | Is the Abu Simbel day trip from Aswan worth it? | No | No | egymonuments.gov.eg | The temporary chat can still read the account's memory, so this is a possible bias |
| 2026-10-15 | ChatGPT | AR | هل تستحق رحلة أبو سمبل من أسوان؟ | — | — | — | **NOT TESTED:** the query did not send |
| 2026-10-15 | Perplexity | EN | same | — | — | — | **NOT TESTED:** requires sign-in |
| — | Google AI Overviews / AI Mode | — | — | — | — | — | **NOT TESTED** |
| — | Bing Copilot | — | — | — | — | — | **NOT TESTED.** It cannot cite the site while Bing has 0 pages indexed |

**Baseline:** Egypt Roamer is not cited (1 of 1 measured query). This is expected while the site is barely indexed.

## Bing AI Performance

- **[Fact]** The Bing Webmaster Tools "AI Performance" report (beta) is available since the site was added on 15 Oct.
- **[Data]** It can't show citations while Bing has indexed nothing. First read: when Bing URL Inspection shows the homepage as indexed.
- It is one observed data source for Copilot/Bing grounding. It is not a universal AI metric.

## Why the benchmark was not widened today

A representative run (16+ queries across platforms) on 15 Oct would measure only that the site is barely indexed. Google holds 30 pages; Bing has none. That result is already known.

The run is planned for the point where it can show differences, after Google re-indexes the 142 pages and Bing indexes the language homes (see `SEO-RECOVERY-30-60-90-DAY-PLAN.md`, days 60–90). The platforms stay **NOT TESTED** until then.

## Next run

- **When:** once Bing shows pages, and Google shows the language homepages as indexed.
- **What:** take 16 queries from `AI-QUERY-DATASET-2026-10-03.md`, 2 per language. Use the ones whose pages already have impressions (Aswan, Siwa, Great Sand Sea, Abu Simbel), run them on ChatGPT search, Copilot and Google, and fill the table above.
- **Who:** the owner signs in to Perplexity and Bing if those platforms should be tracked.
