# Production launch gate

This gate decides whether the project can move from development to GoDaddy staging, and whether the branch is ready for a pull request. **Nothing was redesigned or re-architected.** It found three functional defects, which were fixed; they are listed at the end.

## Environment

**Local lab:**

- WordPress 7.1.2, PHP 8.4, MariaDB 10.11, Polylang 3.8.9.
- Headless Chromium through Playwright; axe-core; Varnish 7.1 with its default VCL.

**Two installs:**

| Install | Contents |
|---|---|
| **Lab** (`:8080`) | seeded; Polylang with 8 languages; local test providers and offers |
| **Fresh** (`:8081`) | rebuilt from the repo on the final code: `db reset`, install, activate, `wp egypt-roamer seed`; no Polylang |

**GoDaddy:** no account is available from this environment, so every GoDaddy item is **NOT VERIFIED**, with a manual procedure below.

**Scripts:** all are in `tools/qa/`, apart from the one-off steps noted in the table below.

---

## Categories

| # | Category | Status |
|---|---|---|
| 1 | Code | **READY** |
| 2 | Content | **NOT READY** |
| 3 | Affiliate | **NOT READY**: engine READY, business NOT READY |
| 4 | SEO | **NOT VERIFIED**: core layer READY, Rank Math untested |
| 5 | Multilingual | **NOT VERIFIED**: engine READY, translation quality unreviewed |
| 6 | Security | **READY** (code level) |
| 7 | Performance | **NOT READY**: homepage LCP; GoDaddy NOT VERIFIED |
| 8 | GoDaddy | **NOT VERIFIED** |
| 9 | Email | **NOT VERIFIED** |
| 10 | Legal | **NOT READY** |

### 1. CODE READY — READY

**Evidence:**

- **Acceptance:** `acceptance.mjs` passes 14/14.
  1. Log in.
  2. Create a provider.
  3. Create an offer, connect it to an experience, publish.
  4. The CTA shows with `rel=sponsored`.
  5. The disclosure appears next to it.
  6. The click redirects with tracking.
  7. `affiliate_click` and `booking_click` reach the dataLayer.
  8. The click appears in the report.
  9. Change the CTA label without code.
  10. Change the affiliate URL without code.
  11. Change homepage content without code.
  12. Admin screens render: dashboard.
  13. Settings.
  14. Subscribers.
- **Leads:** `leads.mjs` passes. Newsletter sign-up is stored; an instant (bot-speed) submit is rejected; the contact form is stored; the `newsletter_signup`, `contact_submit` and `search` events fire.
- **Fresh install:**
  - no Sample Page or Hello World (`/sample-page/`, `/hello-world/`, `/?p=1` and `/?page_id=2` all return 404);
  - the only indexable URL is `/`, and the sitemap contains only `/`;
  - no test data, prices, ratings or `/go/` links in any crawled page;
  - 0 published offers or providers.
- **Responsive and accessibility:** `wp-matrix.mjs`, 12 page types × 390/430/768/1024/1440/1920: 0 overflow or clipping, 0 JavaScript errors, 0 axe violations.
- **Crawl:** 85 pages across all 8 languages, 0 broken internal links, 0 internal redirects, 20/20 local assets OK.
- All changed PHP passes `php -l`.

**Blockers:** none.

**Required action:** none before staging.

### 2. CONTENT READY — NOT READY

**Evidence:** see the [content gate](#content-gate). Every published item fails the "useful without affiliate links" test:

- 7 destinations have 16–23 words each;
- 8 experiences have 0 words;
- guides (7) and trust/legal pages (9) are drafts.

**Blocker:** no page other than the homepage has real editorial content, and the homepage carries unverified claims.

**Required action:**

1. Editors write the content.
2. Verify or remove the claims ("7 UNESCO sites", "1,200+ fish species", "4 nights · 210 km · 5 temples", travel times, "Editor's pick"/"Iconic").
3. Tick *Ready to index* one page at a time.
4. Until then, keep "Discourage search engines" on (SETUP task 21).

### 3. AFFILIATE READY — NOT READY

| | Status | Evidence |
|---|---|---|
| **Engine ready** | **READY** | acceptance 14/14; `go-architecture.sh` 34/34 and `go-cache-matrix.sh` 22/22 (see [Affiliate redirect architecture](#affiliate-redirect-architecture)); reports |
| **Business ready** | **NOT READY** | fresh install: 0 providers, 0 offers, 0 `/go/` links. The lab's providers and offers are test fixtures on `example.org`, never deployed |

**Blocker:** there are no real affiliate accounts, provider domains, tracked URLs or offers.

**Required action:** the owner signs up with partners. For each account:

1. Create a provider with its allowed redirect domains.
2. Create offers with real tracked URLs.
3. Add prices only with a "checked on" date.
4. Attach each offer to a page that passes the content gate.

### 4. SEO READY — NOT VERIFIED

**Evidence (core layer):**

- The index gate passes for all six types (below).
- Canonicals are self-referencing; filtered archives are `noindex, follow` with a clean canonical; search is noindex.
- Every sitemap URL is 200, indexable and self-canonical.
- `/go/` is disallowed in robots.txt, sends `X-Robots-Tag: noindex, nofollow`, and 0 `/go/` URLs are in the sitemaps.
- The site-wide switch works: with `blog_public=0` every page sends `noindex, nofollow` and the sitemap returns 404.

**Blocker:** Rank Math, the planned production SEO plugin, cannot be installed here.

**Required action (staging):**

1. Install Rank Math and run its wizard.
2. Confirm that any page with *Ready to index* off shows `noindex` in the page source, and that `/sitemap_index.xml` lists no such URL.
3. Confirm there is exactly one canonical and one robots tag per page.

### 5. MULTILINGUAL READY — NOT VERIFIED

**Evidence:**

- **Head and UI audit, all 8 languages** (en, fr, de, it, es, ru, zh, ar), 6 pages each (home, destination, destinations archive, experiences archive, search, 404): 48/48 OK.
  - correct `lang`/`dir`;
  - one title, one canonical, one H1;
  - valid JSON-LD;
  - hreflang reciprocal and pointing at 200 pages in the right language;
  - 0 untranslated UI strings;
  - 0 stray Latin text in zh, ru or ar.
- **Functional check, all 8 languages** (`languages.mjs`): all OK.
  - navigation: 24–28 links, all 200, no cross-language links;
  - footer: 6–10 links;
  - CTA label translated with `rel=sponsored`;
  - filters: `/xx/experiences/?destination=…` is `noindex, follow` with a clean canonical;
  - search: `/xx/?s=<localised Cairo>` returns results in the same language only;
  - the newsletter reply is in the page's language;
  - `ar` is `dir=rtl`, with no overflow at 390.

**Blockers:**

1. The translations are machine or migration output and unreviewed (73 strings in `tools/i18n/new-strings.json`, and the prototype's own translations).
2. Translated content exists only for the Cairo destination and drafts.
3. The contact page exists only in English, so the contact form is tested in English only.

**Required action:** a native speaker reviews each language before it is published. Launch English-only until then.

### 6. SECURITY READY — READY (code level)

**Evidence:**

- `go-architecture.sh` / `go-cache-matrix.sh`: every redirect security case passes (see [Affiliate redirect architecture](#affiliate-redirect-architecture)).
- Anonymous REST requests for offers, providers and messages → 401.
- Privileged `admin-post` actions without a login → 400.
- `?post_type=er_offer` → 404.
- Forms: honeypot, HMAC time trap, rate limit and strict email validation.
- The payload JSON uses `JSON_HEX_TAG`.
- No cookies on public pages.

**Blockers:** none for staging.

**Non-blocking:**

- No automated scanner was run.
- The GoDaddy WAF is NOT VERIFIED.
- LOW: some JavaScript inserts editor titles as HTML; titles are kses-filtered and only editors can set them.

**Required action:** run WPScan (or GoDaddy's scan) on staging.

### 7. PERFORMANCE READY — NOT READY

**Evidence:** see [Performance](#performance). Inner pages are good. **Homepage LCP is 5.0–5.3 s on 4G with 4× CPU throttling locally**, because the approved intro loader and hero fade make the hero copy the LCP element. The approved prototype measured the same.

**Blocker:** homepage LCP is above 4 s (poor) in the lab. Production CWV are NOT VERIFIED.

**Required action:**

1. The owner decides whether to keep, shorten or skip the intro loader for first visits.
2. Measure on staging with real photography (procedure below).

### 8. GODADDY READY — NOT VERIFIED

**Evidence:** none possible here: no account, and godaddy.com and wordpress.org are blocked.

**Blocker:** procedures A–E below have not been run.

**Required action:** run A–E on GoDaddy staging.

### 9. EMAIL READY — NOT VERIFIED

**Evidence:** a contact message is stored even when email fails, and the message is flagged. `wp_mail` returns false in this sandbox, which has no mail transport.

**Blocker:** delivery is untested; no API mailer is configured; SPF/DKIM/DMARC are not set.

**Required action:** procedure B.

### 10. LEGAL READY — NOT READY

See the [legal gate](#legal-gate). All four items are drafts or unverified.

---

## Mandatory tests

### 1. Acceptance suite

- `acceptance.mjs`: **14/14 PASS** on this run. Page errors, excluding Polylang's unbuilt admin JS: none.
- `leads.mjs`: PASS.

### 2. Fresh install

Rebuilt from scratch on the final code.

| Check | Result |
|---|---|
| No Sample Page | PASS (`/sample-page/`, `/?page_id=2` → 404; `/hello-world/`, `/?p=1` → 404) |
| No indexable empty archives | PASS: `/destinations/`, `/experiences/`, `/tours/`, `/activities/`, `/guides/`, `/journal/` are all `noindex, follow` |
| Only `/` indexable | PASS: the sitemap contains only `/` |
| No test data exposed | PASS: 0 hits for `Test Provider`, `Acceptance`, `QA `, `/go/`, `$n`, `€n`, `★`, `ratingValue`, `reviewCount` or `example.org|com` on any page; 0 published offers or providers |
| Menus without Polylang | PASS: primary menu and footer render (Destinations … Journal, Trip Builder) |

### 3. Index gate

Script: `index-gate.py`, lab. One published item per type; flags restored afterwards.

| Type | OFF: robots | OFF: sitemap | ON: robots | ON: sitemap | Canonical | H1 | JSON-LD |
|---|---|---|---|---|---|---|---|
| Destination | noindex, follow | excluded | indexable | included | self | 1 | Breadcrumb (+ TouristDestination when ON) |
| Tour | noindex, follow | excluded | indexable | included | self | 1 | Breadcrumb |
| Experience | noindex, follow | excluded | indexable | included | self | 1 | Breadcrumb |
| Activity | noindex, follow | excluded | indexable | included | self | 1 | Breadcrumb |
| Guide | noindex, follow | excluded | indexable | included | self | 1 | Breadcrumb |
| Article | noindex, follow | excluded | indexable | included | self | 1 | Breadcrumb |

**12/12 PASS.**

### 4. Languages

All 8 languages; see category 5. **All OK after the menu fix.** Before the fix, every language, English included, had empty navigation and footer menus.

### 5. Broken links and assets

- 85 pages crawled across 8 languages.
- 0 broken internal links; 0 internal redirects.
- 0 `/go/` links without `rel=sponsored`.
- Local assets: 20/20 OK.
- External: only `images.unsplash.com` (the stand-in photos), which is blocked here, so NOT VERIFIED.

### 6. Affiliate redirect security

Script at the time: the single-step matrix (since replaced by `go-cache-matrix.sh` and `go-architecture.sh` for the two-step design; see [Affiliate redirect architecture](#affiliate-redirect-architecture), which supersedes this table's 404 rows: unknown, draft and deleted offers now land on the home page).

| Case | Result |
|---|---|
| Valid offer | 302 to the partner. Click logged (1 row); HEAD not logged; bot not logged |
| Open redirect (`?url=`, `?to=`) | ignored: still the configured partner |
| CRLF in `pl`, non-digit `src` | sanitised: normal 302 to the partner |
| Unapproved host, look-alike host (`example.org.evil.test`), `user@host`, `javascript:`, `//host`, `http:///x`, `ftp:`, allowed domain only in the query string | internal fallback |
| Invalid slug | 404 with noindex |
| Uppercase or traversal slug | 404: no `/go/` route matches, so it is a normal WordPress 404 |
| Draft offer | 404 |
| Paused, expired or not-yet-started offer; inactive provider; provider without domains | internal fallback |
| Deleted (trashed) offer | 404; restoring it restores the redirect |
| Tracking parameters | `utm_source` / `utm_medium` / `utm_campaign` and sub-ID placeholders (`subid={placement}-{page}`) are applied; a visitor's own `utm_source` or `subid` is ignored |
| `rel` | every rendered `/go/` link is `sponsored nofollow noopener` (crawl + language test) |

Every `/go/` response carries `X-Robots-Tag: noindex, nofollow` and `Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private`.

### 7. Click tracking vs page caching (local Varnish 7.1, default VCL)

- **Pages:** `/`, `/destinations/cairo/`, the experience page with a CTA, `/fr/`, `/ar/destinations/` and `/journal/` are all a MISS, then a HIT. No `Set-Cookie`. The CTA pages are cacheable: click tracking happens at `/go/` and in the dataLayer, not in the page.
- **`/go/`:** 3 requests → 3 distinct Varnish transactions, none from cache → **3 clicks logged**.
- **URL changes:** a change to the offer URL shows on the very next `/go/` request.
- **POSTs:** newsletter POSTs pass through (303).

### 8. Redirect endpoints not indexable

- `X-Robots-Tag: noindex, nofollow` on every `/go/` response;
- `Disallow: /go/` in robots.txt;
- 0 `/go/` URLs in the sitemaps;
- the links carry `rel="sponsored nofollow"`.

### 9. Query counts

Lab, no object cache, 0 duplicate queries on every page. DB time is the time spent in MariaDB.

| Page | Queries | Of which menus | DB time |
|---|---|---|---|
| Home (EN) | 174 | 35 | 46 ms |
| Destination (Cairo) | 168 | 37 | 41 ms |
| Tour | 142 | 36 | 51 ms |
| Guide | 138 | 36 | 34 ms |
| Article | 137 | 33 | 40 ms |
| Experience | 155 | 36 | 40 ms |
| Search | 137 | 35 | 33 ms |
| Home (FR) | 102 | 19 | 27 ms |
| Destination (AR) | 75 | 21 | 26 ms |

These are higher than in READINESS-AUDIT §15 because the menus now render. Before this gate's optimisation, the menus cost 58–62 queries per page.

**Remaining N+1 patterns (all bounded by what the page displays; none grows with total content):**

1. **Payload builders** (`er_payload_destination`, `er_payload_guide`, `er_payload_experience` → `er_get_related`, `er_offers_for_post`, `er_post_image_payload`). Each shown card loads its post, meta, terms and translation group separately: about 31 × `postmeta IN (id)`, 27 × `posts WHERE ID`, 24 × term lookups on the home page. Inner pages spend about 40 queries building the search overlay's data.
2. **Menus:** WordPress's own cost of about 4 queries per menu × 5 menus, plus one batched page lookup per menu.

**Assessment:** acceptable behind a page cache. Caching the payload in a transient, or priming caches for the listed IDs, would remove most of pattern 1. That is optional work, not a blocker.

### 10. Performance

#### LOCAL (lab; Chromium; uncompressed PHP dev server; stand-in photos blocked)

| Profile | Width | Page | TTFB | LCP | CLS | INP | Weight | Requests |
|---|---|---|---|---|---|---|---|---|
| unthrottled | 390 | home | 104–123 ms | 0.28–0.51 s | 0 | 56–96 ms | 667 KB | 24 |
| unthrottled | 1440 | home | 109–124 ms | 0.34–0.38 s | 0 | 176–320 ms | 651 KB | 24 |
| 4G + 4× CPU | 390 | home | 104–137 ms | **5.02–5.13 s** | ≤ 0.008 | 152–168 ms | 667 KB | 24 |
| 4G + 4× CPU | 1440 | home | 110–123 ms | **5.13–5.29 s** | 0 | 224–392 ms | 651 KB | 24 |
| unthrottled | 390–1440 | destination / tour / guide / article | 83–118 ms | 0.15–0.22 s | ≤ 0.051 | 32–120 ms | 369–445 KB | 18–20 |
| 4G + 4× CPU | 390 / 1440 | destination / tour / guide / article | 79–110 ms | 0.59–0.76 s | ≤ 0.046 | 56–128 ms | 361–404 KB | 18–19 |

- The home rows come from 4 runs.
- **Weight breakdown:**
  - Home: JS 233 KB, CSS 128 KB, fonts 144–160 KB.
  - Inner pages: JS 40 KB, CSS 106 KB.
- Inner-page LCP is text; the photos are blocked here.

#### GODADDY STAGING

**NOT MEASURED. NOT VERIFIED.** There is no staging access. The numbers above must not be used as production figures.

**Procedure:**

1. Run PageSpeed Insights (mobile and desktop) on staging for home, one destination, one tour, one guide and one article, with real photography.
2. Record TTFB, LCP, CLS and INP (TBT in the lab).
3. After launch, read CrUX in Search Console once 28 days of data exist.

---

## GoDaddy staging: manual procedures (all NOT VERIFIED)

### A. `/go/` caching

Run this after uploading Core from commit `a9086ce` or later (two-step `/go/`, see [Affiliate redirect architecture](#affiliate-redirect-architecture)) **and flushing GoDaddy's cache** (WP admin bar → Flush cache). The flush removes `/go/` responses the edge cached from the old plugin.

1. Create one test provider allowing `example.org`, and one live offer pointing to `https://example.org/`, attached to a published page.
2. From two different browsers or devices, open `https://<host>/go/<offer-slug>/` **twice each**, also with `?foo=1` and `?utm_source=test`. Use the plain URL, without `nocache`.
3. **Pass:**
   - Hop 1, `/go/…`, is a 302 to `/wp-admin/admin-post.php?action=er_go&offer=<slug>…`. It may be cached (`cf-cache-status: HIT`); that is harmless.
   - Hop 2, `admin-post.php…`, is a 302 to `example.org`, with `cf-cache-status: DYNAMIC` and `Cache-Control: no-store…`.
   - Egypt Roamer → Click reports shows **one row per click**.
4. Change the offer URL, click again: it goes to the new URL immediately, with no flush. Pause the offer and click again: it goes to the internal fallback page. Unpause it.
5. **Fail:** hop 2 shows a cache HIT, or there are fewer rows than clicks. That is a production blocker; send the evidence to GoDaddy support.
6. Delete the test provider and offer.

Script alternative over SSH: `EDGE=https://<host> HOSTHDR= WP=wp DB=<db> OFFER_ID=… PROVIDER_ID=… tools/qa/go-cache-matrix.sh`, run against the **test** offer only.

### B. Email delivery

1. Install **one** mailer plugin (FluentSMTP or WP Mail SMTP) configured with a transactional provider's **HTTPS API**.
2. Add the provider's SPF and DKIM DNS records and a DMARC record.
3. Send the plugin's test email to a Gmail and an Outlook address. **Pass:** it arrives in the inbox, and the headers show `spf=pass dkim=pass dmarc=pass`.
4. Submit the site's contact form. **Pass:** the notification arrives, and Egypt Roamer → Contact messages shows "Email notification sent".
5. Optional FluentCRM check: subscribe via the footer, confirm a *pending* contact plus a double opt-in email, click it, and confirm the contact becomes *subscribed*.

### C. Plugin compatibility

1. Compare the planned list against GoDaddy's blocklist: <https://www.godaddy.com/help/blocklisted-plugins-8964>. The planned list is Egypt Roamer Core, Rank Math, Polylang, one API mailer, one CMP, and optionally FluentCRM.
2. Install them on staging one at a time. After each one:
   - load home, a destination and wp-admin;
   - check Tools → Site Health;
   - check the PHP error log.
3. **Pass:** no fatal errors, no Site Health criticals, and `wp egypt-roamer health` (WP-CLI over SSH) reports no errors.

### D. PHP version

1. GoDaddy dashboard → Settings → PHP version: select **8.2 or newer** (tested locally on 8.4).
2. **Pass:** Tools → Site Health → Info → Server shows that version, and the site loads without notices in the error log.

### E. WordPress caching behaviour

1. Open a page twice with `curl -sI https://<staging>/destinations/`. **Pass:** the second response shows a cache hit (`Age` > 0 or GoDaddy's cache header) and **no `Set-Cookie`**.
2. Edit that page's text in the admin and save. **Pass:** the change is visible after GoDaddy's purge, or after "Flush cache" in the GoDaddy WP admin bar.
3. Submit the newsletter form on a cached page. **Pass:** "You're on the list" appears, and a new row shows in Egypt Roamer → Subscribers.
4. Rate limit: submit the form 6 times within a few minutes. **Pass:** only the 6th is refused, and a different network is not affected. If every visitor shares one address, `REMOTE_ADDR` is the proxy's address; raise `er_form_rate_limit` and tell the developer.
5. `/go/`: procedure A.

---

## Content gate

**Test applied:** "If all affiliate links were removed, would this page still provide substantial value to a traveler?"

The table below lists every page that is public on a fresh install, which is what deploys to staging.

| Page | Words of own content | Currently | Gate |
|---|---|---|---|
| `/` (home) | hero 19 words + section copy from the prototype | **indexable** (the homepage is never auto-noindexed) | **NOT READY**: unverified claims, stand-in photography, and it links mainly to thin pages. Keep "Discourage search engines" on until fixed |
| `/destinations/cairo/` | 22 | noindex | NOT READY |
| `/destinations/luxor/` | 23 | noindex | NOT READY |
| `/destinations/aswan/` | 18 | noindex | NOT READY |
| `/destinations/alexandria/` | 16 | noindex | NOT READY |
| `/destinations/siwa/` | 23 | noindex | NOT READY |
| `/destinations/hurghada/` | 21 | noindex | NOT READY |
| `/destinations/sharm/` | 20 | noindex | NOT READY |
| 8 × `/experiences/…/` | 0 | noindex | NOT READY |
| `/destinations/`, `/experiences/`, `/tours/`, `/activities/`, `/guides/` | archive listings | noindex (no indexable items) | NOT READY (automatic until their items pass) |
| `/journal/` | empty | noindex (no articles) | NOT READY |

**Pages that pass the gate: 0.** Guides (7) and trust/legal pages (9) are drafts and not public.

## Legal gate

No legal text was written or invented.

| Item | Status | Detail |
|---|---|---|
| Privacy | **DRAFT** | seeded draft with editorial notes only; `/privacy` redirects only once it is published |
| Terms | **DRAFT** | seeded draft with editorial notes only |
| Affiliate Disclosure | **DRAFT** | factual draft of how the site earns (no invented claims); the short inline disclosure beside offers works. Owner review needed |
| Cookie / Consent | **DRAFT** (Cookie Policy) / **NOT VERIFIED** (consent) | no CMP is installed. Core sets Consent Mode defaults to denied, and GTM loads only when an ID is set. A CMP must be installed and tested (no GA hit before consent) |

---

## Defects found and fixed in this gate

| # | Defect | Severity | Fix | Verified |
|---|---|---|---|---|
| 1 | With Polylang active, **every menu was empty in all 8 languages** (header, 3 footer columns, legal). Polylang ignores the theme's menu locations, and the seed only set those | HIGH (navigation) | The seed assigns Polylang's per-language locations. `seed --translations` builds one menu per language: archive links go to `/xx/…` with labels from the theme's translations, and pages link to their translations. Items with no translation are left out, never shown in English. The theme's "only working links" filter now understands `/xx/` prefixes | `languages.mjs` 8/8 OK; fresh install without Polylang unchanged |
| 2 | Translated Journal/Home pages had **English titles** (e.g. `<title>Journal – Egypt Roamer</title>` on `/ru/`): WP-CLI's `translate()` does not load theme translations per locale | MEDIUM (mixed-language metadata) | The seed reads the theme's `.l10n.php` files and retitles structural pages that still carry the English default | `/ru/journal-ru/` → `Журнал – Egypt Roamer` |
| 3 | The menu-link check ran `url_to_postid()` (several queries) for every custom page link: **58–62 queries per page** | LOW (efficiency) | One batched page lookup per menu, plus a per-request cache; `url_to_postid()` only for other URLs | 35–37 queries; same rendered links |

**LOW, left as is:** translated structural page slugs are `journal-fr`, `journal-ru` and so on. They are valid and canonical, just not pretty. An editor can rename them.

---

## Affiliate redirect architecture

**Root cause** (measured on egyptroamer.com, 27 Sep 2026):

1. **Two cache layers.**
   - **Cloudflare edge:** `cf-cache-status`. It is the layer returning `HIT`.
   - **GoDaddy's cache gateway**, behind it: `x-gateway-cache-status`, `x-gateway-skip-cache`.
2. **The edge caches everything and ignores the origin.**
   - It replaces the origin's `Cache-Control: no-store, …, private` with `public, max-age=2678400` (31 days).
   - It caches 200, 301, 302 and 404 responses; `/go/x/` gives MISS, then HIT.
   - This is Cloudflare's documented "Cache Everything + Edge TTL override" behaviour: origin `Cache-Control`, `CDN-Cache-Control`, `Cloudflare-CDN-Cache-Control` and even `Set-Cookie` are ignored. No response header from WordPress can prevent it.
3. **The edge has a fixed bypass list** (`cf-cache-status: DYNAMIC`):
   - a **path containing** `wp-admin`, `wp-login`, `wp-json`, `cart`, `checkout`, or `/my-account/`;
   - a **cookie** `wordpress_logged_in_*`, `comment_author_*`, `wp-postpass_*` or `woocommerce_items_in_cart`;
   - a **query string starting with** `nocache`.

   Our own cookie names are not honoured.
4. **What GoDaddy exposes for Managed Hosting for WordPress:** only "Enable/disable CDN" and "Flush cache" are documented. Per-URL "Non-cache URLs" belong to a different product (Website Security and Backups). A `/go/*` exclusion could only come from GoDaddy support, and its availability is **NOT VERIFIED**. Disabling the CDN is rejected, because it would un-cache the whole site.

**Production solution (option C: WordPress-native, excluded by the host).** `/go/{offer}/` stays the only public affiliate URL. It is a stateless 302 to `/wp-admin/admin-post.php?action=er_go&offer={offer}` (plus `pl`, `src`, `where`, `when`, `adults`).

- **Hop 1 can be cached safely.** It reads no offer data, so a cached copy is always right.
- **Hop 2 is never cached.** It is WordPress's own request handler under `/wp-admin/`, which GoDaddy must exclude for WordPress to work. It was measured live on the existing `admin-post.php` newsletter handler: `cf-cache-status: DYNAMIC`, `x-gateway-cache-status: BYPASS`, and the origin's `no-store` passes through, on every request.
- **Hop 2 does everything that must be fresh:** it resolves, validates, logs the click and redirects to the provider.
- **Public pages keep their full caching:** `/`, `/destinations/` and `/destinations/cairo/` give MISS, then HIT.

**Request flow** (plain CTA URL `/go/{slug}/?pl=…&src=…`; no cache-bypass parameter):

| Step | Response | Cacheable? |
|---|---|---|
| `/go/{slug}/` | **302** → `/wp-admin/admin-post.php?action=er_go&offer={slug}[&pl&src&where&when&adults]`, `X-Robots-Tag: noindex` | yes. At GoDaddy's edge it may be a HIT: a **static hop**, no offer data, no click |
| `/wp-admin/admin-post.php?action=er_go…` | **302** → provider URL with configured tracking (or 302 → internal fallback / home page), `Cache-Control: no-store…`, `X-Robots-Tag: noindex` | **never** (`/wp-admin/` is on the host's bypass list). This is the **dynamic handler**: validation, click log, redirect |

Both redirects are temporary (302), because affiliate destinations change. The redirect status setting can switch the final hop to 307.

**Verification (final code, `nocache` removed).** Everything ran behind a Varnish emulation of the measured edge (cache everything including 302/404 for 31 days, ignore origin headers, same bypass list):

| Test | Result |
|---|---|
| 1. Plain `/go/{slug}/` three times | **PASS**: +1 click each (3/3). Hop 0 is MISS, then HIT, HIT; hop 1 is never cached |
| 2. Edge cache | **PASS**: the cached hop always leads to the dynamic handler, which validates, logs and redirects |
| 3. Offer switched from provider A to provider B, no purge | **PASS**: the same URL lands on provider B |
| 4. Paused after cache, no purge | **PASS**: internal fallback, 0 clicks. Unpaused → provider again |
| 5. Deleted after cache (admin Trash, then permanent delete), no purge | **PASS**: home page, never the provider, 0 clicks |
| 6. Expired / not yet started | **PASS**: fallback, 0 clicks |
| 7. `?utm_source=test&utm_medium=affiliate&utm_campaign=test` | **PASS**: visitor `utm_*` are dropped at the hop (they cannot override attribution). The offer's configured `utm_campaign` and `subid={placement}-{page}` are applied. Click logged with placement, source post and path |
| 8. Security: invalid offer; `?url=`/`?to=`/`?redirect_to=`; `?offer=`/`?action=` injection through the hop; CRLF in `pl`/`src`; `/go/..%2f…`; unapproved, look-alike, `user@`, `javascript:`, `//host`, `http:///`, `ftp:`, allowed-domain-in-query targets; inactive provider; target outside the provider's allow-list | **PASS** (all 18 cases) |
| 9. `admin_post_nopriv_er_go`, anonymous | **PASS**: works without login. Crafted `offer` (`../../`, arrays, uppercase, missing) → home page, 0 clicks, 0 PHP warnings. Unregistered actions → 400. `HEAD`, bots and `POST` are not logged. The action only reads an offer and redirects |
| 10. Status codes | **PASS**: 302 → 302; no 301 |
| 11. `nocache=1` removed from CTA URLs, then everything re-run | **PASS**: `go-architecture.sh` 34/34, `go-cache-matrix.sh` 22/22 through the edge and 22/22 without it, 0 PHP warnings, `leads.mjs` PASS |
| 12. `acceptance.mjs` | **14/14 twice in a row** (browser: CTA → `/go/` → handler → partner; click in the report) |

The old single-step design, run through the same emulation, logged 1 of 3 clicks. That reproduces the live defect.

**`nocache=1` required: NO.** It has been removed from generated links (commit after `a9086ce`). The query-string bypass is not part of the architecture.

**Still to confirm on GoDaddy itself:** procedure A, with a real test offer, after uploading this plugin and flushing the cache once. The emulation reproduces the rules measured on egyptroamer.com, but it is not the host.

**Deployment note.** After uploading this plugin version, **flush GoDaddy's cache once**. Old-plugin `/go/` responses (including 404s for slugs that did not exist yet) can otherwise stay at the edge for 31 days.

## Final decision

# NOT READY FOR PR

The code is ready to deploy to GoDaddy staging; category 1 is READY. Staging is exactly where the High items below get verified. Under the rule "a Critical or High blocker means NOT READY FOR PR", the branch is not ready for a pull request:

| Blocker | Severity |
|---|---|
| No real affiliate accounts or offers (business not ready) | CRITICAL |
| No page passes the content gate | CRITICAL |
| Legal pages are drafts; no consent tool | HIGH |
| `/go/` behaviour on GoDaddy's cache (A) | HIGH, NOT VERIFIED |
| Email delivery (B) | HIGH, NOT VERIFIED |
| Homepage LCP 5.0–5.3 s under throttling (inherited intro loader, owner decision) | HIGH |

## Checklist before the PR

1. [ ] Deploy the plugin and theme to **GoDaddy staging** and set PHP 8.2+ (D).
2. [ ] Run procedures **A** (`/go/` caching), **C** (plugins), **E** (caching) and record the results.
3. [ ] Configure an API mailer with SPF/DKIM/DMARC and pass procedure **B**.
4. [ ] Install Rank Math and confirm noindex pages are not in `/sitemap_index.xml` (category 4).
5. [ ] Owner: create at least one real provider with its allowed domains, and one real offer with a tracked URL.
6. [ ] Editors: bring at least one destination and its linked experiences through the content gate. Verify or remove the homepage claims.
7. [ ] Owner or adviser: finish and publish Privacy, Terms, Cookie Policy and Affiliate Disclosure. Install and test a CMP.
8. [ ] Owner: decide on the homepage intro loader, then measure CWV on staging with real photos (Performance → GoDaddy staging).
9. [ ] Keep "Discourage search engines" on until items 5–7 are done.
10. [ ] Re-run `acceptance.mjs`, `languages.mjs`, `index-gate.py`, `go-architecture.sh` and `go-cache-matrix.sh` against staging.
