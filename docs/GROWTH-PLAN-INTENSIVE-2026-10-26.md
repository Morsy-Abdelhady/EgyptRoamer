# Intensive growth plan: towards 1,000,000 sessions a month (26 Oct 2026)

Discussed with the owner in chat and approved on 26 Oct 2026, with the six decisions in §3. It supersedes the
milestones and pace in `GROWTH-PLAN-1M-2026-10-24.md`. That file keeps the evidence and measurement notes of 24–25 Oct.

**Honesty first.** Every number marked **[Estimate]** is a judgement, not data. No keyword volumes, competitor
traffic or revenue per visit are available. Targets are revised after the first 28 days of GA4 sessions.

**Metric:** the goal is **GA4 sessions per calendar month**, with users reported separately. First-party page views,
`/go/` redirects and Search Console impressions are not substitutes.

## 1. Where we are (review of 15–26 Oct)

**Working:**
- 392 pages in 8 languages; page-coverage all ok (25 Oct).
- Four partners: Viator, GetYourGuide, Trip.com and Klook. Payout details entered by the owner (26 Oct).
- The Visits counter counts people only (Core 1.2.67).
- All 336 translations approved (Claude review, owner approval, 26 Oct: `TRANSLATION-REVIEW-2026-10-26.md`).
- Local test site restored, with its scripts in `tools/local/`.

**Blocking growth:**

| Problem | Evidence | Fix in this plan |
|---|---|---|
| Rankings around position 63 | GSC, 28 days to 23 Oct: 736 impressions, 5 clicks | Content depth (§2.2), links (§2.3) |
| No external links | GSC Links report: 0 | §2.3 |
| Bing has 0 pages indexed | Bing WMT, 24–25 Oct; homes submitted 24 Oct | Check 31 Oct (scheduled task) |
| No sessions measured | GA4 not collecting; consent tool incomplete | §2.1 |
| Thin topic coverage | 48 English topics; official sources (Ministry, EEAA) in about 14 of 75 source rows | §2.2 |
| Slow mobile LCP | 3.0–3.5 s, hot-linked Unsplash heroes | §2.5 |
| Privacy Policy out of date | Lists Viator only and "no outside analytics" | §2.1 |
| About 90 changed files not released | 25–26 Oct editorial, template, Core and docs changes | Week 1 |

## 2. The plan

### 2.1 Measurement (week 1)
- Align the Privacy Policy in 8 languages with the four partners and consent-based analytics.
- Finish Complianz; GA4 through GTM with **Basic** consent mode (no Google request before opt-in).
- Before ticking Complianz's Google attestations, make them true in GA4/Google settings (data processing terms
  accepted, data sharing off, IP handling as stated). Each acceptance in Edge needs the owner's OK at that step.
- Verify: no Google request before consent; page views, sessions, source and partner clicks after consent.
- `KPI-WEEKLY.md` every Monday, from fresh reports.

### 2.2 Content from official sources (the largest lever)
- **Place pages from official lists.** One page per monument or museum on the Ministry's site (`egymonuments.gov.eg`)
  and per protected area on the EEAA site (`eeaa.gov.eg`), using only facts on those pages (plus UNESCO where it
  applies). **[Estimate]** about 150 monuments and museums and about 30 protected areas: about 180 topics × 8 languages,
  about 1,400 pages. Pace: 20 places a week.
- **Guides from real demand:** 5 a week, chosen from Search Console queries and real questions (best time, visas,
  getting around, comparisons).
- **Source review of existing pages:** check the 48 current topics against Ministry and EEAA pages, starting with the
  pages with most impressions: snorkelling, inside the pyramids, 14 days, Mount Sinai, Great Sand Sea. Record every
  source in `CONTENT-SOURCES.md`.
- **Languages:** English, Arabic and German on the same day; the other five within the week. Claude reviews
  translations; the owner approves (decision 2).
- Rules stay: no invented prices, hours, travel times, ratings, reviews, statistics or photos.

### 2.3 Links (earned, never bought)
- One citable resource: a table of the major sites with their official ticket and hours sources and a "checked on"
  date, built from the Ministry's pages.
- Offer it to travel bloggers, Egyptian tourism communities and forums where linking is welcome.
- **[Estimate]** target: 10 referring sites in 3 months, 50 in 12 months.

### 2.4 Social
- Pinterest: one pin a day from each guide and place page once the owner's accounts exist (posts ready in
  `SOCIAL-POSTS-2026-10-20.md`).
- If Arabic leads in GA4: Facebook and TikTok. Short video only from the owner's own footage or licensed free clips.

### 2.5 Speed and technical
- Serve the main hero photos from our own domain (Media Library), using only the approved Unsplash photos (decision 4).
  Target: mobile LCP under 2.5 s.
- Bing: follow up on 31 Oct; contact Bing support with the owner's OK if it still shows the 27 Sep crawl.

### 2.6 Paid
- Not before GA4 and first bookings. Then one 4-week test on 3–5 trip pages, continued only if revenue per visit is at
  least the cost per click.

## 3. Owner decisions (26 Oct 2026)

| # | Decision | Applied in |
|---|---|---|
| 1 | Claude publishes guides and pages after they pass the release gate | `CLAUDE.md` |
| 2 | Translations: Claude's professional review plus the owner's approval is the standard, recorded as AI review | `CLAUDE.md`, editorial front matter |
| 3 | Place pages from the Ministry and EEAA official lists, about 20 a week | §2.2 |
| 4 | Main hero photos move into the Media Library, approved Unsplash photos only | `CLAUDE.md`, §2.5 |
| 5 | Google/GTM terms and Complianz attestations go through Edge, each with the owner's OK, after the settings make them true | §2.1 |
| 6 | The pending changes are tested and released together first | Week 1 |

## 4. Milestones [Estimate]

| Milestone | Sessions a month | Target |
|---|---|---|
| M1 | 10,000 | Feb 2027 |
| M2 | 100,000 | Sep 2027 (GetYourGuide API threshold) |
| M3 | 300,000 | Mar 2028 |
| M4 | 1,000,000 | late 2028 |

Even at this pace, 1M before 2028 is not realistic without heavy paid spend. If a milestone slips by more than three
months: double down on the content clusters that earned impressions, and drop the rest.

## 5. Week 1 (26 Oct – 1 Nov 2026)

1. Test and release the pending changes (decision 6): local checks, version bump, deploy, page-coverage, EN/AR desktop
   and phone checks.
2. Record KPI week 2 (19–25 Oct).
3. Privacy Policy in 8 languages: four partners, consent-based analytics.
4. Complianz and GA4 (Basic consent), with the Edge acceptances in §2.1.
5. Source review of the five pages with most impressions against Ministry/EEAA pages.
6. First 20 place pages from the official lists: drafts, sources recorded.
7. 31 Oct: Bing and Visits check (scheduled task).

## 6. What we will not do
- Pages without an official or authoritative source, or literal translations to inflate the count.
- Bought links or link schemes.
- Invented prices, hours, travel times, ratings, reviews, statistics or photos.
- Partner photos or text copied.
