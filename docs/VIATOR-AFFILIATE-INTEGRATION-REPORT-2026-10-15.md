# Viator affiliate integration report (2026-10-15)

> **Update, 15 Oct afternoon:** the offers now point to verified **products**, not listing pages, and Great Sand Sea has an offer. The current mapping is in `VIATOR-CONTENT-MATCHING-REPORT.md`, which supersedes §2–§4 below.

**Outcome:** the site's first live affiliate path.
- **[Done]** Provider **Viator** (#493) and **7 offers** are published on production, one for each of 7 of the 8 experiences.
- **[Done]** Each CTA goes through Core's `/go/{slug}/` two-step redirect to a Viator page carrying the account's tracking parameters.
- **[Done]** Each click lands in the existing click log.
- **[Gap]** Great Sand Sea has no offer (§4).

Labels: **[Fact]**, **[Done]**, **[Not tested]**, **[Owner]**.

## 1. Account (partners.viator.com, 15 Oct)

- **[Fact] Account:** active Partner account "Egypt Roamer – USD". The partner ID starts `P00…`; it is not repeated here, although it appears publicly in every affiliate link.
- **[Data] Dashboard, last 30 days:** 0 visitors, 0 bookings, $0.00.
- **[Fact] Tools available:**
  - Link creation;
  - Widgets (up to 30 products);
  - Banners;
  - Viator Shop;
  - Viator Selector, which needs a separate password sign-in, so it was **not used**;
  - API Access, not used: it would mean handling an API key.
- **[Fact] Link format from Viator's own Link tool:** `<viator page URL>?pid=<partner ID>&mcid=42383&medium=link`, plus `&campaign=<label>` when campaign tracking is used.
  - Checked by comparing the tool's generated link field by field, without copying it out.
  - Core builds the same link from the offer's Target URL plus four "extra tracking parameters":
    - `pid`
    - `mcid=42383`
    - `medium=link`
    - `campaign=er-{lang}-{placement}`, where Core fills the language and the placement, e.g. `er-ar-experience-offers`, so Viator's reports split by language and page area.
- **[Owner] Payout details are not set.** The dashboard says commissions are then paid in USD by default. Enter payout details under **Finance**; that's an owner task, and it involves banking data.

## 2. Matching decision: Viator listing pages, not single products

**[Fact] www.viator.com was unreachable from this machine on 15 Oct:**
- `ERR_CONNECTION_RESET` in Chrome and in a second browser;
- the scripted fetch was also reset.

So no individual product page could be opened to check its operator, itinerary, inclusions or availability. The mission forbids publishing an unverified product.

**What was done instead:** each experience links to the matching **Viator-curated listing page** for that place or activity (an attraction page or category page).
- Its relevance was verified from Viator's own indexed page title and URL (web search, 15 Oct).
- No product-level claim is made anywhere. There is no price, rating, review, duration, availability or "best" claim.
- **Card copy:** "{Place} tours on Viator", the location, and the CTA "Compare Options" (translated in every language). Comparing a list is literally what the page offers.

**Product-level deep links** convert better, but they need someone who can open viator.com to verify each product.

**Recommendation:** within 2 weeks, once the owner (or I, from a network that reaches viator.com) has verified one specific product per experience, put its URL in the offer's **Target URL**. Nothing else changes: same slug, same tracking, same CTA.

## 3. Offers (production)

| # | Experience (EN id) | Offer (slug) | Viator page (Target URL) | Verified location / activity | Tracking | Redirect test (15 Oct) | Status |
|---|---|---|---|---|---|---|---|
| 495 | Abu Simbel Day Trip from Aswan (27) | `abu-simbel-temples-tours-on-viator` | `/Aswan-attractions/Abu-Simbel-Temples/d796-a1566` | Viator attraction "Abu Simbel Temples, Aswan" | pid, mcid, medium, campaign | ✅ 302 → 302 viator.com, `campaign=er-en-experience-offers`; AR page → `er-ar-…` | Published |
| 497 | Valley of the Kings & Hatshepsut Temple (31) | `valley-of-the-kings-tours-on-viator` | `/Luxor-attractions/Valley-of-the-Kings/d826-a2141` | Viator attraction "Valley of the Kings, Luxor" | ✅ | ✅ (EN, DE → `er-de-…`) | Published |
| 499 | Pyramids of Giza & Sphinx Private Tour (25) | `giza-pyramids-tours-on-viator` | `/Giza-attractions/Giza-Pyramids/d23032-a1506` | Viator attraction "Giza Pyramids, Giza" | ✅ | ✅ | Published |
| 501 | Nile Dinner Cruise with Live Show (26) | `cairo-nile-dinner-cruises-on-viator` | `/Cairo-tours/Dinner-Cruises/d782-g3-c5640` | Viator category "Cairo Dinner Cruises" | ✅ | ✅ | Published |
| 503 | Red Sea Diving: Two Reef Dives (28) | `hurghada-scuba-diving-trips-on-viator` | `/Hurghada-tours/Scuba-Diving/d800-g17-c28901` | Viator category "Hurghada Scuba Diving" | ✅ | ✅ | Published |
| 505 | Cairo Street Food Tour by Night (30) | `cairo-food-tours-on-viator` | `/Cairo-tours/Food-Tours/d782-g6-c80` | Viator category "Cairo Food Tours" | ✅ | ✅ | Published |
| 507 | White Desert & Black Desert Safari (29) | `bahariya-oasis-tours-on-viator` | `/Western-Desert-attractions/Bahariya-Oasis/d24114-a20309` | Viator attraction "Bahariya Oasis, Western Desert". Bahariya is the gateway oasis for the Black and White Desert trips; Viator has no separate White Desert page in the results | ✅ | ✅ | Published |
| — | Great Sand Sea 4×4 & Hot Springs (32) | — | — | No Viator listing page for Siwa or the Great Sand Sea was found; only individual products, which can't be verified from here | — | — | **Gap, no offer** |

**Common to all 7:**
- **Provider and status:** provider Viator, allowed domain `viator.com`, offer status Active, priority 10, CTA `compare_options`.
- **Not filled:** no price, no badge, no image.
- **Destination links,** so each offer also shows on its destination page:
  - Cairo (18): Giza Pyramids, Nile dinner cruise, food tours;
  - Aswan (20): Abu Simbel;
  - Luxor (19): Valley of the Kings;
  - Hurghada (21): diving.
- **Bahariya** has no destination page, so it has no destination link.
- **Languages:** offers are language-neutral in Core, so each shows on all 8 language versions of its experience. The CTA label is translated; the offer title stays English, because it names a Viator page.
- **Placeholders untouched:** the 15 old draft placeholder offers are still drafts.

## 4. Verification on production (15 Oct, a logged-out Chrome via Playwright)

| Check | Result |
|---|---|
| CTA on each experience page, EN | ✅ 7/7 pages show their offer: "Compare Options", `rel="sponsored nofollow noopener"`, `target=_blank` |
| Other languages | ✅ AR Abu Simbel ("قارن الخيارات"), DE Valley of the Kings ("Optionen vergleichen") |
| Disclosure next to the offer box | ✅ on every experience and destination page with an offer box |
| `/go/` hop | ✅ `/go/{slug}/` → `admin-post.php?action=er_go…` → 302 `www.viator.com/<page>?pid=…&mcid=42383&medium=link&campaign=er-{lang}-{placement}`. No loop; the host is in the allow-list |
| Click recorded | ✅ Click reports: 11 clicks, all by Viator, by offer, experience, destination and placement. **All 11 are QA test clicks from 15 Oct** |
| Viator landing page loads | **[Not tested]** viator.com is unreachable from this network |
| Viator dashboard counts the visit | **[Not tested yet]** The dashboard updates with a delay. Recheck 16–17 Oct for "Visitors" > 0. The test clicks never reached Viator (redirect read, not followed), so they will not show there |

### Problem found and fixed: theme 1.2.45 (commit `6e32619`)

- **The issue:** offers also appear on **cards** (homepage experiences rail, archives, search, "Alternatives to compare"), and those grids printed **no disclosure**. Example: Great Sand Sea has no offer box, but its "Alternatives" card linked to Viator with no disclosure on the page.
- **The fix:** `er_card_grid()` and the homepage rail now print the disclosure whenever a card links to a partner.
- **Tested locally:** home, archive, search and Great Sand Sea each show ≥ 1 disclosure when there are partner links. The production check is in the changelog.

## 5. Owner actions

1. **[Owner] Click one CTA** from your own phone or network, e.g. Abu Simbel → Compare Options, to confirm the Viator page opens. A logged-in browser does not count in Visits, but the `/go/` click log records it.
2. **[Owner] Viator → Finance:** enter payout details.
3. **[Owner, optional] Products:** choose one Viator product per experience (or let me do it from a network that reaches viator.com). Its URL goes in Target URL. Criteria: same activity and start point, an operator with a track record, inclusions that match what our page says, bookable.
4. **[Owner, optional] Great Sand Sea:** decide whether to link a Siwa multi-day product once it can be verified, or to keep the gap.

## 6. What to watch

| Metric | Where | First read |
|---|---|---|
| Partner taps on page | Egypt Roamer → Visits | weekly |
| `/go/` redirects (minus the 11 QA clicks on 15 Oct) | Egypt Roamer → Click reports | weekly |
| Failed partner links | Visits → "Failed partner links" | weekly; must stay 0 |
| Visitors / bookings / commission | Viator → Performance | from 17 Oct, weekly |
| Campaign split (`er-{lang}-{placement}`) | Viator → Performance, by campaign | after the first 50 clicks |
