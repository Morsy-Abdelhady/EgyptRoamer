# Growth plan: towards 1,000,000 visits a month (24 Oct 2026)

The owner asked for "a realistic plan to reach a million visits a month". This plan replaces nothing: it sits on top
of `SEO-RECOVERY-30-60-90-DAY-PLAN.md` (day-to-day SEO) and `CONTENT-PLAN-2026-Q4.md` (guide list).

**Honesty first.** Every number marked **[Estimate]** is my judgement, not data: no keyword-volume tool, no
competitor traffic data and no revenue-per-visit figure is available to us. The only hard data is in §1. The plan is
built so that each stage proves itself before the next one starts.

## 1. Where we are (verified)

| Item | Value | Source |
|---|---|---|
| Page views, 16–22 Oct | 430 a week (about 1,800 a month), part of it our own release checks | `KPI-WEEKLY.md` week 1 |
| Entrances from search | 2 in that week | Visits |
| Google, 28 days | 271 impressions, 2 clicks, average position 61.2 | GSC, 22 Oct |
| Google indexing | 30 indexed, 162 not (142 old "noindex", validation still running) | GSC, 22 Oct |
| Bing | 0 indexed, homepage still shows the 27 Sep noindex crawl | Bing WMT, 20 Oct |
| Content | 384 pages: 47 English bodies (7 destinations, 11 experiences, 29 guides) × 8 languages | page-coverage |
| Partner results | Viator: 1 visitor, 0 bookings (30 days). 0 partner-link taps | Viator, Visits |
| Domain age | indexing opened 2 Oct 2026, so about 3 weeks | `LAUNCH-GATE.md` |

**The gap:** 1,000,000 a month is about **550×** today, and about 33,000 visits a day.

## 2. Is 1M a month realistic, and when?

**Arithmetic.** With today's 384 pages, 1M a month means about 2,600 visits per page per month. Long-tail travel
guides on a young domain don't do that. **[Estimate]** a well-ranked long-tail page earns tens to a few hundred visits
a month. So 1M needs **thousands of indexed, ranking pages plus channels other than Google.**

**My honest answer [Estimate]:**
- **Before the end of 2026:** no, not from search. Google needs months to trust a new domain, and pumping out pages
  fast would trip its "scaled content" policy and hurt the whole site.
- **Through search + social + newsletter, done well:** about **24–36 months**. 12–18 months is possible only if
  content, links and one social channel all take off together. That is the optimistic case, not the plan.
- **With paid ads:** traffic can be bought sooner, but it's only worth it once we know what one visit earns (§6).

So the plan has **milestones with gates**, not a single date:

| Milestone | Visits a month | Target date [Estimate] | Why this number matters |
|---|---|---|---|
| M1 | 10,000 | Mar 2027 | Proof that search works in at least 2 languages |
| M2 | 100,000 | Dec 2027 | GetYourGuide's API threshold (`PARTNER-API-REQUESTS-2026-10-20.md`); real revenue data |
| M3 | 300,000 | Jun 2028 | Ad networks and direct deals with operators become possible |
| M4 | 1,000,000 | 2028–2029 | GetYourGuide's Reading API threshold (with 300 bookings a month) |

If a milestone slips by more than 3 months, the gate in §7 decides what changes.

## 3. Where 1M would come from (target mix at M4) [Estimate]

| Channel | Share | Visits a month | What it takes |
|---|---|---|---|
| Google + Bing, 8 languages | ~60% | ~600,000 | ~4,000–6,000 indexed pages that rank (≈ 500–750 topics × 8 languages), plus links |
| Pinterest | ~15% | ~150,000 | One business account, daily pins from every guide, for 12+ months |
| Short video (YouTube Shorts, Instagram Reels, TikTok) | ~10% | ~100,000 | Owned video: the owner or a local creator filming real places (see §5) |
| Newsletter + returning readers | ~8% | ~80,000 | Email list growing from every page; one useful email a week |
| AI assistants, forums, referrals, direct | ~7% | ~70,000 | Citable pages, original resources, real participation in communities |

No single channel gets to 1M alone. Search is the base; the rest is what makes the timeline shorter.

## 4. Phases

### Phase 0: foundations (now → 30 Nov 2026)
Nothing grows until these are true. Most are already in the 30/60/90 plan; they are listed here as the gate.

1. **Indexing:** ≥ 350 of 384 pages indexed in Google; Bing indexes the homepage and language homes.
   Follow-up checks 29 Oct and 5 Nov (GSC validation, Bing URL Inspection).
2. **Honest measurement:**
   - Filter our own headless QA browsers out of Visits (today they inflate page views).
   - Find out who hits `/go/` (53 in week 1 with 0 taps: mostly bots). Filter known crawlers from the count.
   - Fill `KPI-WEEKLY.md` every Monday (suggested: a weekly scheduled task).
3. **Speed:** LCP is 3.0–3.5 s on mobile (15 Oct). The remaining fix is serving hero photos from our own domain,
   which needs the owner's OK to put them in the Media Library (CLAUDE.md rule).
4. **Owner accounts (only the owner can do these):** Pinterest Business and Instagram (`OWNER-ACTIONS-2026-10-20.md`
   §1), Viator payout details, a transactional mail account for the newsletter.

### Phase 1: content engine + first channel (Dec 2026 → Mar 2027, the winter season) → M1
1. **Pace:** 3–5 new English guides a week (from 2 today), each with sources in `CONTENT-SOURCES.md`, then Arabic,
   then the other 6 languages. Goal by end of March: **~110 English bodies → ~900 pages.**
   The limit is verified sources and translation review, not writing speed. We never publish unsourced pages to go
   faster.
2. **Topic clusters, in this order** (each answers a question asked before booking):
   - **Per destination, deeper:** "things to do in Luxor / Aswan / Hurghada / Sharm / Alexandria / Siwa", "Luxor in
     2 days", "where to stay in Cairo (areas)". New places only with sources: Dahab, Marsa Alam, Fayoum, Kom Ombo
     and Edfu, Port Said.
   - **Planning basics:** visa (official e-Visa portal), money (how to pay, ATMs, tipping customs: no amounts we
     can't source), safety (government advisories, linked and dated), getting around (train, domestic flights,
     ride-hailing: no travel times unless sourced), SIM cards and internet, what to wear, Ramadan travel.
   - **Month guides:** "Egypt in January" … "Egypt in December" (12 pages × 8 languages), from sourced climate and
     calendar facts only.
   - **Comparisons:** "Hurghada or Sharm" already exists; add "Luxor or Aswan", "Nile cruise or Lake Nasser cruise",
     "El Gouna or Hurghada", "Giza or Saqqara first".
3. **Arabic as the lead language:** our best positions so far are Arabic queries (Aswan, Siwa, Great Sand Sea). The
   Arabic results pages are the weakest. Every new guide goes out in Arabic the same day as English.
4. **Refresh before create:** every 2 weeks, pages ranking 5–20 get the title, description and missing answer fixed
   (30/60/90 plan, days 30–60).
5. **Pinterest:** one pin a day from `SOCIAL-POSTS-2026-10-20.md`, then one pin per new guide. Each link carries
   `utm_source` so Visits counts it.
6. **Newsletter:** a sign-up offer on every guide ("Egypt planning checklist" built only from facts already on the
   site). One email a week with the new guides.

### Phase 2: authority (Apr → Dec 2027) → M2
1. **Original, citable resources** (what other sites link to):
   - a maintained table of the major sites with their official ticket sources and a "checked on" date;
   - "what changed in Egypt travel this month" (new museum openings, rule changes), each item linked to its source.
2. **Links, earned only:** offer these resources to travel bloggers, Egyptian tourism communities and forums where
   linking is welcome. Guest articles on travel sites. **No bought links, no link schemes.**
3. **Second language push:** by now GSC shows which of DE, RU, ZH, FR, IT, ES responds. Put a native reviewer on the
   best two (owner action, `OWNER-ACTIONS-2026-10-20.md` §5) and write guides for their own questions, not only
   translations.
4. **Short video starts** (§5).
5. **Content:** ~250 English bodies → ~2,000 pages by end of 2027.

### Phase 3: scale (2028 →) → M3, M4
1. ~500–750 topics × 8 languages, kept fresh (a yearly "checked on" pass on every page).
2. API data from partners once we qualify (GetYourGuide at 100k): prices and ratings from the API on the
   trip pages, which helps conversion.
3. Direct deals with operators, and display ads only if they don't slow the pages or hurt trust.
4. Paid campaigns only where §6 shows they pay back.

## 5. What only the owner can unlock

| Item | Why it matters | Phase |
|---|---|---|
| Pinterest and Instagram business accounts | The second channel; posts are ready | 0 |
| Own photos and video from Egypt (owner or a hired local creator) | Our photo rules allow only brand assets and tagged free Unsplash photos. Owned media lifts every page, unlocks short video, and partner photos can never be copied | 1–2 |
| Native reviewers for 2 languages | Translations that rank, not just exist | 2 |
| Partner accounts: Tiqets, Klook (and their terms) | More offers per page, ticket guides | 1 |
| Budget decision for a paid test (§6) | Only after revenue per visit is known | 2 |

## 6. Paid ads: when, not whether

- **Not now.** With 0 bookings we don't know what one visit earns, so any ad spend is a guess.
- **When:** once ~300 partner clicks a month give a measurable booking rate. Then revenue per visit =
  commission ÷ visits, from the partner dashboards.
- **Test:** a small fixed budget on 3–5 trip pages, one channel (Google or Meta), 4 weeks. Continue only if
  revenue per visit ≥ cost per click.
- Bought traffic counts towards 1M, but the goal is commission, not the number.

## 7. Gates and decisions

| Check | When | If not met |
|---|---|---|
| Phase 0 gate: ≥ 350 pages indexed, honest Visits | 30 Nov 2026 | Fix indexing before writing more |
| A language has 0 impressions after 60 indexed days | rolling | Stop new content in that language; keep its pages |
| M1 (10k) | Mar 2027 | Look at which clusters got impressions; double those, drop the rest |
| A new guide has 0 impressions after 8 weeks | rolling | Rewrite its title and opening for the real query, or merge it |
| Pinterest: < 1,000 visits a month after 6 months of daily pins | rolling | Move the effort to short video |
| M2 (100k) | Dec 2027 | Revisit timeline and the ad test |

## 8. What we will not do
- Mass-produce pages or literal translations to inflate the count (Google's scaled-content policy).
- Buy links or traffic that isn't a measured test.
- Invent prices, opening hours, travel times, ratings, reviews, statistics or photos (CLAUDE.md).
- Copy partner photos or text.

## 9. Weekly routine (suggested)
- **Monday:** fill `KPI-WEEKLY.md` (GSC, Bing, Visits, partner dashboards). A scheduled task can do the reading.
- **Tuesday–Friday:** 3–5 guides through write → source → AR → 6 languages → publish → page-coverage.
- **Every 2 weeks:** refresh pages ranking 5–20.
- **Every month:** check this plan against §7 and update the milestone table.
