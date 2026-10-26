# Growth plan: towards 1,000,000 sessions a month (24 Oct 2026)

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

### Live recheck, 25 Oct 2026

This replaces the older traffic snapshot above for decisions made from 25 Oct onward; the historical values remain for context.

| Signal | Latest observed value | What it means / limitation |
|---|---|---|
| First-party Visits, 24–25 Oct | 20 page views; 12 entrances; 0 search / AI entrances; 0 partner-link taps; 10 `/go/` redirects; 0 failed links | The counter was tightened on 24 Oct to require a human interaction and exclude identifiable bots. It counts page views, not unique visitors or sessions; this two-day sample cannot be projected as a monthly rate. Code review found that logged-in editors were excluded from CTA beacons but not `/go/` redirect logs, so logged-in QA could explain the mismatch; the historical ten redirects cannot be attributed without request-level records. |
| Google Search, latest 28-day report | 695 impressions, 3 clicks, 0.4% CTR, average position 64.4; data through 23 Oct | Search visibility is still the main measured acquisition bottleneck. Sitewide average position is not the rank of any one page. |
| Google pages with most impressions | EN snorkelling guide 68 (0 clicks); EN inside-pyramids guide 67 (0); DE snorkelling guide 47 (0); 14-day itinerary 37 (0); Mount Sinai guide 26 (0); AR Great Sand Sea guide 23 (0); Nile cruise experience 21 (0). The DE inside-pyramids guide had 12 impressions and 1 click. | Refresh these pages against their actual query rows before making more pages for the same topics. |
| Bing Search Performance, 25 Oct | 0 clicks and 0 impressions, 25 Jul–24 Oct | Bing is not yet a contributing channel in the current report. |
| Google Page indexing | Last report update 16 Oct: 217 indexed; 162 not indexed (139 noindex, 18 discovered-not-indexed, 2 crawled-not-indexed, 3 redirects) | The report is stale and includes intentional noindex pages; recheck before deciding that 162 pages are a current technical failure. |
| Measurement configuration | Site Kit lists Analytics as connected, but its settings say **“Snippet is not inserted”**; Egypt Roamer GTM container ID field is blank. Complianz 7.5.6 is installed and active. Existing English Cookie Policy and Privacy Policy are now selected in its Documents step; country set to United Kingdom; DNT/GPC respect enabled. GTM is selected as the intended statistics tool, but Complianz will not proceed without three unchecked Google legal attestations. | GA4 is still not collecting through the site. A cookie banner/blocker has not been proven active on the public page; a live visit while signed in showed no banner. Do not treat “connected” as proof that sessions are being measured. |

### Search Console recheck, 25 Oct 2026

| Signal | Current UI value | What it means / limitation |
|---|---|---|
| Google Search performance | 5 clicks, 736 impressions, 0.7% CTR, average position 63; period shown 29 Sep–23 Oct; report says last updated 4 hours before check | Small sitewide totals; positions are averages across queries and pages, not stable rank positions. |
| Search Console overview, checked again 25 Oct | 217 indexed / 159 not indexed; Google recommends inspecting the snorkelling guide after a recent impressions increase | Reconciles the current 3-month performance view with the landing-page opportunity Google itself surfaced; indexing total still includes intentional exclusions and needs page-level checks. |
| Pages with most impressions | EN snorkelling guide: 68 impressions, 0 clicks, average position 71.5; EN inside-pyramids guide: 67, 0 clicks, position 72.6; DE snorkelling guide: 47; EN 14-day itinerary: 37; EN Mount Sinai guide: 26 | These are early discovery signals, but rankings around 70 mean snippet-only edits are unlikely to solve the main issue. |
| Queries for EN snorkelling page | “best time to snorkel in hurghada” (9), “snorkeling in egypt” (8), “snorkeling sharm el sheikh” (8), “snorkeling” (6), “hurghada snorkelling” (6); all 0 clicks | The page already includes a “Can I snorkel all year?” answer, but the page is far down the results. Data supports strengthening useful, sourced page content and authority before title tinkering. |
| Queries for EN inside-pyramids page | “interior of great pyramid of giza” (5), “inside pyramid of giza” (5), “pyramid of giza interior” (3), “inside of pyramid of giza” (3), “inside the great pyramid of giza” (3); all 0 clicks | Search intent matches the page title and existing interior description. Average position 72.6 points to a ranking/authority gap, not an obvious title mismatch. |
| Google indexing report | 217 indexed, 159 not indexed; last updated 16 Oct. Reasons: 139 excluded by noindex (validation started), 18 discovered-not-indexed, 2 crawled-not-indexed | The aggregate report is stale by nine days; URL Inspection is newer and must be checked per page before action. |

The 18 “discovered, not indexed” examples in the 16 Oct report include policy/archive URLs and potentially valuable localized pages: German Aswan; Spanish Aswan, Abu Simbel day trip, Cairo night food tour and Valley of the Kings; French Siwa and White Desert safari; Italian, Russian and Chinese Aswan; and the English “best time to visit Egypt” guide. All showed `Last crawled: N/A` in that old report; it does not establish their current status.

### URL Inspection follow-up, 25 Oct 2026

| URL | Current indexed state | Live test | Action |
|---|---|---|---|
| `/guides/best-time-to-visit-egypt/` | URL is on Google | Not needed | This was listed among discovered URLs in the 16 Oct aggregate report; it has since been indexed. No request needed. |
| `/es/destinations/asuan/` | Not on Google; “Discovered – currently not indexed”; sitemap found; referring pages include English and Arabic Aswan | Available to Google; page can be indexed; one valid breadcrumb | Requested indexing; Google confirmed it entered the priority crawl queue. |
| `/es/experiences/excursion-de-un-dia-a-abu-simbel-desde-asuan/` | Not on Google; “Discovered – currently not indexed”; sitemap found; internal referrals include EN/AR product pages and the ES home/archive | Available to Google; page can be indexed; one valid breadcrumb | Requested indexing; Google confirmed it entered the priority crawl queue. |
| `/fr/destinations/siwa-fr/` | Not on Google; URL unknown to Google; no sitemap or referring page reported | Available to Google; page can be indexed; one valid breadcrumb | Requested indexing; Google confirmed it entered the priority crawl queue. |

These are crawl requests, not proof of indexing or ranking. Do not submit duplicates; recheck after Google refreshes its data. Continue URL Inspection for the remaining high-value examples, and distinguish valid, indexable commercial/editorial pages from intentional legal/archive exclusions before requesting anything else.

**Decision from this recheck:** do not spend the next SEO cycle on title tweaks for the two high-impression English guides; their titles already match the observed intents and their average positions are about 72. Use URL Inspection on high-value examples from the stale indexing report; the two Spanish URLs and French Siwa page above are now in Google's priority crawl queue. Verify remaining commercial/editorial URLs before any action, and exclude intentional noindex pages. A sustained lift in impressions and indexed URLs is needed before traffic forecasts become meaningful.

### Content change, 25 Oct 2026

Google Search Console's 3-month report now shows “great sand sea” (14 impressions), “aswan to luxor cruise” (13), “egypt itinerary” (12), “egypt trip itinerary” (11), “egypt travel itinerary” (10), and snorkelling queries (9, 8, 8 impressions) among its top queries. The Overview also recommends checking the English snorkelling comparison after an impressions increase. Updated its opening to state the comparison's decision intent and point to the existing dive-sites guide; removed an unsourced seasonal water-temperature claim from its FAQ, replacing it with advice to check current conditions; qualified the Straits of Tiran conditions and added an EEAA source for Ras Mohammed's 1983 designation and protected reefs. The page title was left intact. Source: `content/editorial/en/g-snorkelling.md`, `docs/CONTENT-SOURCES.md`. Also removed live price and rating chips from the shared guide-ending offer list; project policy excludes prices and ratings from editorial guide content. The live EN page still needs the complete release gate; all seven translations exist but are now stale against the edited English source and need native review before a multilingual release. This source edit alone does not change production.

The same report gives “great sand sea” 14 impressions and no click. Updated `content/editorial/en/g-great-sand-sea.md` against Egypt's official tourism and protected-area sources: clarified the Siwa/dunes intent, removed unsupported fixed excursion stops, permit claims, month recommendations and Cairo-trip claims, and directs readers to confirm current route and access details with the operator. The title is unchanged. The seven translations exist and will now need native review against the revised English before any multilingual release. Compiled the updated English body only; no production change or indexing request was made.

Review of the current shared guide template found copy that promises free cancellation on every option and in the mobile route bar. That overstates partner terms, which vary by option. Updated the template to reuse the existing translated disclosure (“Free cancellation is shown when the partner offers it”), show an individual cancellation badge only when all options for that trip qualify, and remove the route-wide promise. Also corrected the matching social draft. These are local changes only; verify all 8 languages and affected desktop/mobile pages through the full release gate before deployment.

Also corrected the English Siwa destination page's Great Sand Sea section: removed the unsupported claim that a 4×4 trip is essential and that operators are licensed/handle permits, and replaced fixed activity/stop promises with current-offer confirmation advice. Updated the source register and compiled the editorial data; the seven destination translations need native review against this source change. Production remains unchanged.

### Consent-aware analytics setup, 25 Oct 2026

- The owner explicitly authorized preparing GA4 via GTM for page views, sessions, acquisition source and affiliate clicks, **only after visitor consent**.
- Complianz was installed and activated in WordPress. Its setup has not been completed or verified; do not claim visitor consent is working yet.
- Selected the UK as Complianz's default region based on the operating company's published UK registration. This is a configuration choice, not a legal-compliance determination.
- Complianz says its Site Kit integration supports only Advanced Consent Mode. Google's documentation states that Advanced mode can send cookieless measurements while consent is denied; that conflicts with the owner's instruction. Do not use that integration. Google Basic Consent Mode blocks tags until consent and sends no pre-consent data.
- GTM is not set up in the signed-in Google account (the GTM page offered “Create Account”). Creating it requires accepting Google's Terms of Service at that moment. Complianz also asks the owner to confirm the Google data-processing amendment, restrictions on Google's reuse of data, and IP-address blocking. Those legal attestations remain for the owner.
- In the Complianz Documents step, selected the existing English Cookie Policy and Privacy Policy. No existing Imprint or Disclaimer page was confirmed; leave those unconfigured rather than generating or publishing legal text without review. The company-country selector had defaulted to United States; changed it to United Kingdom, consistent with the operator's published registered office. Enabled Respect Do Not Track and Global Privacy Control, matching the current Privacy Policy. Complianz's separate website-scan terms prompt was declined.
- Continued the Complianz wizard to the statistics step and selected Google Tag Manager as the intended tool. Its next step requires the owner to attest that the Google data-processing amendment has been accepted, Google cannot reuse analytics data for other Google services, and IP acquisition is blocked. Left all three unchecked; the wizard remains at 56% and cannot continue. These are not established facts and must not be affirmed by Codex.
- The visitor-facing homepage showed no consent banner while signed into WordPress, and the wizard is incomplete. Therefore the setting to enable banner/blocker is not evidence that it is currently active. GA4 remains off; do not add a GTM ID or enable tags until consent behavior and all eight language versions are checked.
- Edge review of the live English Privacy Policy on 25 Oct found it still says the site does not use outside analytics and identifies Viator as the only partner. The project handoff lists four live partners (Viator, GetYourGuide, Trip.com and Klook). Align the policy and its seven translations with the actual partner relationships and any consent-based analytics before enabling GA4; do not expose analytics to visitors until the translated disclosures and consent behavior are verified.
- Next: select the verified existing Cookie Policy and Privacy Policy pages and enter only confirmed company data in Complianz; investigate whether Imprint/Disclaimer fields are actually mandatory for this setup. Do not invent or publish legal pages. Complete and verify the banner/blocker in the eight supported site languages, then create the GTM account after the owner accepts Google's terms and confirms the CMP legal attestations. Use **Basic** consent only; configure one GA4 Google Tag with the existing Measurement ID and approved events, and test that no Google requests occur before opt-in. After consent, verify page views, sessions, source and partner-click events; preserve the site's first-party counter.

**Metric definition for the goal:** “1,000,000 visits/month” must be reported as **GA4 sessions per calendar month** (with users reported separately). Do not use first-party page views, partner redirects, or Search Console impressions as a substitute. The current first-party counter cannot identify unique visitors or sessions.

### Immediate execution order from the live evidence

1. Reconcile the 10 `/go/` redirects against 0 partner-link taps for 24–25 Oct. Check source page, referrer and user agent in available logs; keep crawler/test traffic out of the human acquisition KPI.
2. The owner has authorized preparing GA4 only after visitor consent. Align the Privacy Policy across all eight languages with the live partner relationships and planned analytics; complete and verify the consent tool; then create/configure GTM after the owner confirms Google's terms and CMP's legal attestations. Keep all Google tags blocked before consent; then verify page views and sessions in GA4 before using them as the baseline.
3. Export Search Console's page and query rows for the pages above. Refresh titles, opening answers and internal links only where the actual query, current page text and verified sources support the edit. Preserve page languages and re-review translations when their English source changes.
4. Recheck Bing Site Explorer and Google indexing from the live tools; the current Bing report is zero and Google's index report is nine days old.
5. Only after a clean 28-day GA4 baseline, set channel-specific targets (organic search, social/referral, newsletter, paid) and a monthly sessions dashboard. The milestone dates and channel shares in §§2–3 remain estimates, not forecasts.

**The gap:** 1,000,000 sessions a month is about **33,300 sessions a day**. We cannot responsibly state the current multiple until GA4's 28-day sessions baseline is verified. The old 550× comparison used 430 weekly page views that included QA and must not be used as today's audience baseline.

## 2. Is 1M a month realistic, and when?

**Arithmetic.** With the 384 pages recorded at the time of this plan, 1M sessions a month means about 2,600 visits per page per month if every page contributed equally. Long-tail travel
guides on a young domain don't do that. **[Estimate]** a well-ranked long-tail page earns tens to a few hundred visits
a month. So 1M needs **thousands of indexed, ranking pages plus channels other than Google.**

**My honest answer [Estimate]:**
- **Before the end of 2026:** no, not from search. Google needs months to trust a new domain, and pumping out pages
  fast would trip its "scaled content" policy and hurt the whole site.
- **Through search + social + newsletter, done well:** about **24–36 months**. 12–18 months is possible only if
  content, links and one social channel all take off together. That is the optimistic case, not the plan.
- **With paid ads:** traffic can be bought sooner, but it's only worth it once we know what one visit earns (§6).

So the plan has **milestones with gates**, not a single date:

| Milestone | Sessions a month | Target date [Estimate] | Why this number matters |
|---|---|---|---|
| M1 | 10,000 | Mar 2027 | Proof that search works in at least 2 languages |
| M2 | 100,000 | Dec 2027 | GetYourGuide's API threshold (`PARTNER-API-REQUESTS-2026-10-20.md`); real revenue data |
| M3 | 300,000 | Jun 2028 | Ad networks and direct deals with operators become possible |
| M4 | 1,000,000 | 2028–2029 | GetYourGuide's Reading API threshold (with 300 bookings a month) |

If a milestone slips by more than 3 months, the gate in §7 decides what changes.

## 3. Where 1M would come from (target mix at M4) [Estimate]

| Channel | Share | Sessions a month | What it takes |
|---|---|---|---|
| Google + Bing, 8 languages | ~60% | ~600,000 | ~4,000–6,000 indexed pages that rank (≈ 500–750 topics × 8 languages), plus links |
| Pinterest | ~15% | ~150,000 | One business account, daily pins from every guide, for 12+ months |
| Short video (YouTube Shorts, Instagram Reels, TikTok) | ~10% | ~100,000 | Owned video: the owner or a local creator filming real places (see §5) |
| Newsletter + returning readers | ~8% | ~80,000 | Email list growing from every page; one useful email a week |
| AI assistants, forums, referrals, direct | ~7% | ~70,000 | Citable pages, original resources, real participation in communities |

No single channel gets to 1M alone. Search is the base; the rest is what makes the timeline shorter.

## 4. Phases

### Phase 0: foundations (now → 30 Nov 2026)
Growth work can continue, but don't claim a traffic baseline or indexing recovery until these are verified.

1. **Indexing:** use the current production inventory of **392 pages** (handoff, 25 Oct), not the older 384-page count. The last aggregate GSC snapshot (217 indexed / 159 not indexed) is dated 16 Oct and is stale. Refresh GSC and Bing reports, then inspect representative indexable pages in each language; separate intentional noindex pages from valuable destination, experience and guide pages. Do not use “350 indexed” as a gate until the denominator and report are current.
2. **Honest measurement:**
   - The historical 10 `/go/` redirects may include logged-in QA: the beacon excluded logged-in editors while redirect logs did not. The redirect code now applies the same logged-in exclusion locally. After release, compare a non-logged-in real CTA with the Visits and partner reports; don't relabel the historical sample without request-level evidence.
   - Continue the existing first-party Visits report for page views and entrances; it does not measure sessions or unique visitors.
   - GA4 remains blocked until the consent banner/blocker is complete and verified, the privacy disclosures are updated consistently, and the owner confirms the outstanding Google/CMP legal statements and accepts account terms. After that, verify no analytics request before opt-in and a working session after opt-in.
   - Fill `KPI-WEEKLY.md` from fresh reports every Monday; don't carry forward stale weekly snapshots as current.
3. **Speed:** LCP is 3.0–3.5 s on mobile (15 Oct). The remaining fix is serving hero photos from our own domain,
   which needs the owner's OK to put them in the Media Library (CLAUDE.md rule).
4. **Owner accounts (only the owner can do these):** Pinterest Business and Instagram (`OWNER-ACTIONS-2026-10-20.md`
   §1), Viator payout details, a transactional mail account for the newsletter.

### Phase 1: content engine + first channel (Dec 2026 → Mar 2027, the winter season) → M1
1. **Pace:** prepare sourced English guide drafts from demonstrated search demand, then prepare translations for
   native review. Guides stay drafts until the owner publishes them. Do not set a weekly publishing quota or count
   draft language versions as live/indexable pages. The limit is verified sources and translation review, not writing
   speed; never publish unsourced pages to go faster.
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
5. **Pinterest:** once the owner has created and connected the business account, publish one reviewed pin a day
   from `SOCIAL-POSTS-2026-10-20.md`. Recheck every claim and destination URL against the current live offer before
   posting; each link carries `utm_source` so Visits can attribute the campaign.
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
| Phase 0 gate: fresh 392-page inventory, current indexability review, honest Visits | 30 Nov 2026 | Fix confirmed technical/indexing issues; keep stale aggregate counts labelled stale |
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
- **Tuesday–Friday:** prepare sourced guide drafts and translation-review packages from measured search demand; keep guides in draft until owner publication approval, then run the full multilingual release gate.
- **Every 2 weeks:** refresh pages ranking 5–20.
- **Every month:** check this plan against §7 and update the milestone table.
