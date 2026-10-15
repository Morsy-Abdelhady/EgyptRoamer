# Analytics implementation report (2026-10-15)

**What changed:** Core **1.2.31** (commit `8f4446c`) adds first-party, cookieless page-view totals.

**Reliable measurement starts:** **2026-10-15 (UTC)**, the `er_visits_since` option on production.

**There is no visit data for any earlier date.** Nothing before this date may be quoted as a visit count. Search Console clicks and impressions remain the only earlier evidence of search traffic.

Labels: **[Fact]** verified, **[Data]** a measured number, **[Decision]** a design choice, **[Owner]** needs the account owner.

## 1. Why

- **[Fact]** Until today the site had no measurement of human visits:
  - no GA4 and no Tag Manager container (`gtm_id` empty);
  - no server log access in wp-admin.
- **[Fact]** The privacy policy said "we do not use advertising or analytics trackers".
- **[Diagnosis]** The owner's "zero visits for a month" was therefore **unmeasured**, not measured zero.
- **[Decision]** (mission brief, 15 Oct): build a lightweight first-party, cookieless system. No GA4 and no consent banner for this first step.

## 2. Design

| Question | Answer |
|---|---|
| What is stored | One row per **day × event × path × language × page type × source category × referring domain × device class**, with a counter. Nothing else |
| Events | `view` (page view); `cta` (tap on a partner link, before the `/go/` hop); `go_error` (a `/go/` link that could not redirect) |
| Path | `location.pathname` only. The query string and fragment are dropped in the browser **and** on the server; Arabic and Chinese paths are stored decoded |
| Referrer | **Host only** (e.g. `google.com.eg`), never the referring page. It is classified as `search`, `ai` (chatgpt.com, perplexity.ai, copilot, gemini…), `social`, `referral`, `direct`, `internal` (same site), or `campaign` (`utm_source` with no referrer) |
| Device | `mobile` / `tablet` / `desktop` from the screen width (<768, <1100). The user agent is not stored |
| Language and page type | From the server-rendered page: Polylang language, page type |
| Cookies | **None** set by this system |
| Identifiers | **None**: no visitor ID, session ID or fingerprint |
| IP address | **Not stored** in the analytics table (the table has no column for it) |
| Unique visitors | **Not counted, by design.** The numbers are page views and entrances (views whose referrer is not this site) |
| Retention | 760 days (about 25 months), purged daily by WP-Cron |
| Abuse limits | Same-origin only (`Sec-Fetch-Site` / `Origin` checked); at most 5,000 distinct rows per day, after which only existing rows are incremented; values are allow-listed and length-capped |

### What is not counted

- Logged-in users. A browser where an editor has been logged in also stays excluded later, through a local-storage flag `er-nocount` that only editors' browsers receive.
- Browsers sending **Do Not Track** or **Global Privacy Control**.
- Automation (`navigator.webdriver`).
- User agents matching bot, crawler, headless, Lighthouse, curl, python and similar patterns. The server checks this too.
- Prerendered pages (counted only once shown), and background tabs (counted when first shown).

### Known limits

- A bot that runs JavaScript **and** pretends to be a normal browser can still be counted.
- People using privacy settings (DNT/GPC) are deliberately not counted, so totals are a **floor**.
- Each reload is one more view.

## 3. Where IP addresses are still processed

**[Fact]** IP addresses are still processed outside the analytics table:
- **Cloudflare** receives every request, sets `__cf_bm` (already in the Cookie Policy) and may log IPs.
- **GoDaddy** (hosting) receives every request and may keep IPs in technical logs. The retention isn't visible from wp-admin.
- The beacon itself is an HTTP request, so the hosting and CDN layers see its IP like any other request. WordPress does not store it.

The privacy policy now says so in every language (section "Services that process data for us").

## 4. Files

| File | Change |
|---|---|
| `plugins/egypt-roamer-core/includes/visits.php` | **New.** Table `wp_er_visits` (DB version 3), REST endpoint `POST /wp-json/egypt-roamer/v1/hit` (204, `no-store`), `er_go_failed` hook, retention cron, **Egypt Roamer → Visits** report |
| `assets/track.js` | One `fetch(…, {keepalive:true})` per page view; CTA tap event; exclusions above |
| `includes/analytics.php` | `erTrack` config passes through the `er_track_config` filter |
| `includes/redirect.php` | `do_action('er_go_failed', $slug, $reason)` on unknown / not-live / invalid-URL offers |
| `content/legal/en/privacy-policy.html` + `content/legal/i18n/*.json` + `tools/legal.py` | Privacy Policy changes (§6) |

## 5. The report (Egypt Roamer → Visits)

**Cards:**
- page views;
- entrances (views from outside the site);
- entrances from search engines;
- entrances from AI assistants;
- partner-link taps on the page;
- partner redirects logged by `/go/` (the existing click log);
- failed partner links.

**Tables:**
- landing pages;
- all pages;
- entrance sources;
- referring domains;
- language;
- device;
- page type;
- taps by page;
- failed links.

The date range is 7, 30, 90 or 365 days, or a custom range, in UTC days. The report states the first measured day, and that earlier dates have no data.

## 6. Privacy Policy (8 languages)

**Changes:**
1. **New item: "Page-view statistics".** It says exactly what is sent, that it is a daily total, and that there is no cookie, identifier, IP address or browser details. It also says that DNT/GPC is honoured and why the totals are kept.
2. **Changed sentence:** "we do not use advertising or analytics trackers" became "we do not use advertising trackers or outside analytics services".
3. **Hosting and CDN:** GoDaddy and Cloudflare receive the IP address and browser details and may keep technical logs.
4. **Retention:** "Page-view totals: 25 months, then deleted automatically."
5. **Date:** "Last updated: 15 October 2026".

**How it was applied:**
- **[Done]** English changes are applied by hash: they replace exactly the 30 Sep text, `sha1 2f80a433…`.
- **[Done]** Translations are applied by the legal sync, which updates only untouched sync-created pages.
- **[Done]** Production sync on 15 Oct: `{"created":0,"updated":8,"current":22,"kept":0,"skipped":0}`.

**[Owner] Native review:** the 7 translations of the new sentences follow the existing legal translations, which were "drafted 2026-09-30, pending native review". They carry the same translation note and need the same native review.

**[Not legal advice]** Choices that need the owner:
- **Legitimate interest:** the design keeps no identifiers and stores nothing on the device, which is why no consent banner was added. Whether that is enough in every market the site serves (EU/UK ePrivacy, Egypt's PDPL) is the owner's legal decision. No text claims the site is "exempt" from consent.
- **Records of processing:** if the owner keeps one, add this processing to it.

## 7. Validation

### Local (127.0.0.1:8080, real Chrome via Playwright)

| Test | Result |
|---|---|
| Page view recorded (desktop and phone) | ✅ 1 POST per page, 204; rows with the right path, language, page type and device |
| Query string removed | ✅ `/…/?q=secret` stored as the path only; `?utm_x=1` dropped |
| Referrer → category | ✅ `www.google.com.eg` → search / google.com.eg; `chatgpt.com` → ai; `www.bing.com` → search; own host → internal; `utm_source=Newsletter` with no referrer → campaign / newsletter |
| Arabic path stored readable | ✅ `/ar/destinations/أسوان/` |
| Upsert | ✅ the same combination counted 2, not 2 rows |
| Bot UA, `HeadlessChrome`, `Googlebot` | ✅ not counted |
| Cross-site POST, foreign `Origin`, unknown event, `javascript:` path, bad language or device | ✅ ignored (204, no row) |
| `navigator.webdriver` / DNT / GPC | ✅ no request sent |
| Logged-in editor; the same browser after logout | ✅ not counted (`er-nocount` set) |
| Cookies set by the system | ✅ none (`[]`) |
| `/go/no-such-offer/` | ✅ `go_error` row `/go/no-such-offer/`, reason `unknown` |
| Report grouping | ✅ (found and fixed a `k` alias clash before release: the hash column is now `hkey`) |
| Existing QA: `acceptance.mjs`, `leads.mjs` | ✅ all PASS (only the known Polylang editor console noise) |

### Production (15 Oct)

| Check | Result |
|---|---|
| Deployed version | ✅ `track.js?ver=1.2.31` |
| Table and report | ✅ Egypt Roamer → Visits present |
| Labelled live view `/ar/?utm_source=er-qa` (phone width) | ✅ POST 204 `{"e":"view","p":"/ar/","l":"ar","t":"home","d":"mobile","u":"er-qa"}`; the report shows 1 view, "Measuring since 2026-10-15" |
| Cookies after a visit | ✅ only Cloudflare's `__cf_bm` (already disclosed) |

**Test data in production:**
- 1 page view: source `campaign`, ref `er-qa`, 15 Oct.
- 11 `/go/` clicks from the offer verification on 15 Oct (§ Viator report).
- **Subtract them from 15 Oct when reading the numbers.**

## 8. How to read it

- **Week 1:** confirm the totals move at all (any non-zero count of `search` entrances proves the end-to-end path).
- **From day 7:** compare against Search Console clicks for the same days. Visits counted as "search" should be close to, usually a little below, GSC clicks: DNT/GPC users aren't counted, and GSC counts clicks, not page loads.
- **From day 28:** use 28-day windows. Do not judge from single days.
