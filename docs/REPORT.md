# Egypt Roamer — implementation report

This report covers only what was implemented and verified. It was verified on a local WordPress 7.1.2 install (PHP 8.4, MariaDB 10.11, Polylang 3.8.9) with headless Chromium. Anything that could not be tested there is marked **NOT VERIFIED**.

## A. Existing project audit

See [AUDIT.md](AUDIT.md). The prototype is a single static homepage with 8 client-side languages; all content lives in `data.js`. The sandbox blocked Unsplash and jsDelivr, so photography and the prototype's CDN motion libraries are NOT VERIFIED in the prototype audit.

## B. Errors found

The full list (B1–B13) is in the audit:

- fabricated ratings, prices and review counts;
- unsupported claims;
- 28 placeholder affiliate links;
- 12 dead footer links;
- three forms with fake success messages;
- three axe violations;
- hard-coded `$`/`en-US` formatting;
- the missing build tools;
- a `TypeError` on every hero finder tab click;
- a charcoal token that doesn't match the brand.

## C. Errors fixed (in the WordPress build)

| # | Fix |
|---|---|
| B1–B3 | Ratings, review counts and data-claim badges removed. Prices appear only with a "checked on" date. Unsupported trust claims are not migrated. |
| B4 | Every CTA is a tracked `/go/` link to a live offer; there are no placeholders. |
| B5 | Every page type exists. Menus hide links to unpublished or missing pages. |
| B6 | The newsletter posts to the server (FluentCRM or a stored table). Contact messages are stored and emailed. The planner button links to an owner-chosen page or is hidden. The finder goes to a tracked offer or is hidden. |
| B7–B9 | Buttons have accessible names. The map uses `role="group"`. "Roamer pick" labels are unique. Mood tabs are linked to a tabpanel. |
| B10 | Locale-aware currency and number formatting. |
| B11 | `tools/build.py` recreated (`check` reproduces the prototype bundle module-for-module). |
| B12 | Finder `TypeError` fixed (shadowed variable). |
| B13 | `--charcoal` set to `#101820`. |
| found during migration | GSAP null targets; body-class collision causing overflow; homepage noindex edge case; language switcher `nested-interactive`; duplicate banner landmark; heading order on archives; unsized search icon |

**The prototype folder itself is left unchanged** as the approved reference. Its bugs are fixed in the theme, not in `Egypt Roamer/`.

## D. Missing pages created

- **Templates:**
  - Destination archive and detail; tour, experience, activity and guide archives and details.
  - Article archive (`/journal/`) and detail.
  - Page, search and 404.
- **Draft pages** with editorial notes: Our Story, How We Choose, Partner With Us, Contact (with a working form), FAQ, Affiliate Disclosure (factual draft), Terms, Cookie Policy, Privacy Policy.
- **Structural pages:** a static front page and a Journal page, per language.

## E. Pages intentionally not created

"Best things to do" / "Best tours" pages (covered by destination hubs), where-to-stay pages, comparison pages, a visa guide and any mass keyword variants. The reasons are in [CONTENT-PLAN.md](CONTENT-PLAN.md).

## F–I. Architecture, theme, plugin, content models

See [ARCHITECTURE.md](ARCHITECTURE.md).

## J–L. Affiliate architecture, tracking, redirects

See [ARCHITECTURE.md](ARCHITECTURE.md#affiliate-engine). **Verified:**

**Security matrix** (curl against the local site):

- An open-redirect attempt (`?url=`) is ignored.
- A disallowed host, a look-alike host (`example.com.evil.test`) and a `user@host` URL all fall back to the internal page.
- A paused, expired, not-yet-started or inactive-provider offer falls back to its related page.
- A draft offer or unknown slug returns 404.
- A `javascript:` search template is rejected on save.
- Finder values are whitelisted.
- Bots, `HEAD` requests and prefetches are not logged.

**Acceptance run (14/14 PASS, browser, admin UI):**

1. create a provider;
2. create an offer and attach it to an experience;
3. the CTA shows its label with `rel=sponsored`;
4. the disclosure appears next to it;
5. a click redirects to the partner with tracking;
6. `affiliate_click` and `booking_click` reach the dataLayer;
7. the click appears in the report with offer, provider, derived destination, placement and CTA;
8. changing the CTA label and the URL in the admin takes effect on the site;
9. editing the homepage text takes effect on the site;
10. the dashboard, settings and subscribers screens render.

The external partner host is unreachable from the sandbox. The redirect target was asserted from the browser's outbound request.

## M–N. Marketing and CRM

- **Funnel:**
  - Discovery: destination hubs, guides, activities.
  - Consideration: decision-support blocks, alternatives, itineraries as guides.
  - Conversion: editable CTAs placed in scenes, partner tabs, offer rows and cards.
  - Retention: newsletter with interest tags, then FluentCRM.
- **Verified:** a newsletter sign-up is stored with source and language; a bot-speed submission is rejected; the contact message is stored and the email failure is flagged.
- **NOT VERIFIED:** the FluentCRM hand-off (FluentCRM not installed in the sandbox).

## O. Analytics

- **Verified in the browser:** GTM loader with Consent Mode defaults (only when an ID is set), and the `affiliate_click`, `booking_click`, `search`, `newsletter_signup` and `contact_submit` events.
- **Implemented but not exercised:** `filter_use` and `guide_download`.
- **NOT VERIFIED:** the GA4/GTM container configuration.

## P. SEO implementation

**Verified locally without an SEO plugin:**

- the homepage is indexable;
- unready pages, search, filtered archives and empty archives are `noindex, follow`;
- canonicals are correct;
- `/go/` is disallowed and noindexed;
- one-hop 301s for legacy paths;
- `TouristDestination` JSON-LD comes only from visible fields;
- breadcrumbs, with BreadcrumbList data only when no SEO plugin is active;
- exactly one H1 per page;
- no image without an `alt` attribute.

**NOT VERIFIED:** the Rank Math integration (not installable in the sandbox).

## Q. Multilingual

**Verified with Polylang:**

- `/fr/`, `/ar/` and `/fr/destinations/le-caire/`;
- correct `lang`/`dir` attributes;
- reciprocal hreflang only for existing translations (Luxor, which has no translation, gets none);
- the switcher lists only languages with content;
- French UI fully translated on home and destination pages;
- Arabic loads RTL CSS, Arabic fonts and the Arabic dictionary;
- `/?lang=fr` 301-redirects to `/fr/`.

**Translation quality:**

- The 224 theme strings and 29 plugin strings have translations in all 7 languages.
- **68 of those strings were translated during this migration and are NOT reviewed by native speakers**; they are listed in `tools/i18n/new-strings.json`.
- Whether the prototype's own translations were reviewed is **NOT VERIFIED**.

## R. GoDaddy compatibility

**NOT VERIFIED** throughout; see [HOSTING-AND-PLUGINS.md](HOSTING-AND-PLUGINS.md). The design avoids GoDaddy-duplicating plugins (cache, backup, security, statistics) and SMTP-from-server.

## S. Performance (local, uncompressed)

**JavaScript:**

| | Before | After |
|---|---|---|
| Homepage | one 339 KB bundle with 8 languages | 106 KB theme bundle + ~130 KB vendored GSAP/ScrollTrigger/Lenis + one ~13 KB locale file |
| Inner pages | same 339 KB bundle | 38 KB (11.9 KB gzip), no motion libraries |

**Fonts:** self-hosted, split by `unicode-range`, `font-display: swap`. Arabic faces load only on Arabic pages.

**Homepage LCP image:** preloaded with `fetchpriority=high`.

**Database queries (no object cache):**

| Page | Queries |
|---|---|
| Homepage | 176 → 125 (after loading offers once per request) |
| Destination page | 102 |
| Experience page | 95 |

**NOT VERIFIED:** Core Web Vitals on real hosting with real photography.

## T. Accessibility

axe-core reports **0 violations** on 12 page types (EN/FR/AR) at 390 and 1440 px. In the responsive matrix (390/430/768/1024/1440/1920), there is **no horizontal overflow or clipped text** and no JavaScript errors. Colour contrast over photography is NOT VERIFIED (photos blocked in the sandbox).

## U. Security

See [ARCHITECTURE.md](ARCHITECTURE.md#security). Every PHP file passes `php -l`. No automated security scanner was run: NOT VERIFIED beyond manual review and the redirect matrix.

## V. Redirect migration map

See [MIGRATION.md](MIGRATION.md#1-redirect-map).

## W. Remaining blockers

1. **No real affiliate accounts or links exist.** Providers and offers must be created by the owner, so no CTA is live on a fresh install.
2. **Editorial content** is short (prototype copy) or missing (guides, legal pages). Nothing is indexable until an editor ticks "Ready to index".
3. **Legal texts** (privacy, terms, cookies, disclosure) need the owner's or their adviser's review.
4. **Native review** of translations before publishing any language other than English.
5. **Owned or licensed photography** in the Media Library (the stand-ins are hot-linked Unsplash images; `seed --with-images` can import them, NOT VERIFIED).
6. **GoDaddy:** blocklist check, PHP version, `/go/` cache behaviour, WP-CLI availability. All NOT VERIFIED.
7. **Rank Math and FluentCRM** integration testing on staging. NOT VERIFIED.

## X. Manual setup tasks

See [SETUP.md](SETUP.md#2-manual-setup-tasks-owner).

---

## Final QA checklist

| Item | Status |
|---|---|
| Existing project audited again | done ([AUDIT.md](AUDIT.md)) |
| Existing errors identified / fixed | done (in the WordPress build; prototype left as reference) |
| Missing subpages identified / created | done (templates; legal and trust pages as drafts) |
| Existing design preserved | done: approved CSS copied unchanged except the charcoal token; inner pages use the same components |
| Approved branding preserved / logo not redesigned | done: supplied files used as-is; colours verified by pixel sampling |
| English checked | done |
| French checked where implemented | done (UI and seeded destination); content translations await review |
| No mixed-language UI | done for theme and plugin front-end strings (EN/FR/AR checked); admin screens follow the admin user's language |
| hreflang reviewed | done (Polylang, verified reciprocal, no tags for missing translations) |
| SEO / canonicals / robots / sitemap reviewed | done locally; Rank Math NOT VERIFIED |
| Schema reviewed | done (only TouristDestination + fallback breadcrumbs; no reviews, ratings, prices or events) |
| Internal links / URLs / redirects reviewed | done |
| Affiliate system, tracking, secure redirects | done and tested |
| rel=sponsored where appropriate | done (`sponsored nofollow noopener`) |
| Affiliate disclosure implemented | done (inline next to offers + draft page) |
| Thin affiliate pages avoided / no mass-generated pages | done ("Ready to index" gate; no generated pages) |
| CMS content, homepage and navigation editable | done |
| Analytics events implemented | done |
| CRM architecture | implemented (FluentCRM hand-off NOT VERIFIED) |
| Email delivery architecture documented | done |
| GoDaddy plugin compatibility checked | **NOT VERIFIED** (no access) |
| No duplicate caching / backup / statistics plugin | done (none installed or required) |
| Responsive / accessibility QA | done (local; photography NOT VERIFIED) |
| Security QA | redirect matrix + review done; scanner NOT VERIFIED |
| Performance QA | done locally; field CWV NOT VERIFIED |
| No horizontal overflow / broken images / broken links | done locally (Unsplash stand-ins cannot load in the sandbox) |
| No accidental noindex / indexation of redirects | done |
| No fabricated data, reviews, ratings, availability | done |
| No booking system, checkout, WooCommerce | done |
| No unrelated redesign | done |
