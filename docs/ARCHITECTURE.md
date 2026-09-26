# Egypt Roamer — WordPress architecture

```
Google / social ─► landing page (destination · guide · tour/experience/activity)
                   editorial value ─► decision support (who it suits, tips, alternatives)
                   ─► affiliate CTA  /go/{offer}/?pl={placement}&src={page}
                   ─► Egypt Roamer Core: validate → log click → add tracking → 302
                   ─► partner site: availability, booking, payment, cancellation

Visitor ─► newsletter / contact ─► FluentCRM (double opt-in) or local table ─► segments, campaigns
```

**No direct booking.** There is no WooCommerce, no checkout, no payment, no customer accounts and no availability data. The platform only discovers, explains, compares, tracks and hands the visitor over to the partner.

## Repository layout

| Path | Contents |
|---|---|
| `Egypt Roamer/` | The approved static prototype, kept unchanged as the design reference. Not deployed. |
| `wordpress/wp-content/plugins/egypt-roamer-core/` | **Business logic.** The data survives a theme change. |
| `wordpress/wp-content/themes/egypt-roamer/` | **Presentation.** Hybrid classic theme (PHP templates + `theme.json`, block-editor content). |
| `tools/` | `build.py` (JS bundler; `check` proves it reproduces the prototype), `export-seed.mjs`, `build-translations.mjs`, `i18n-strings.py`, `i18n/new-strings.json`. |
| `docs/` | Audit, this file, migration, content plan, hosting/plugins, setup, final report. |

## Theme / plugin separation

| Concern | Lives in | Survives a theme change? |
|---|---|---|
| Content types, taxonomies, fields, relations | Core | yes |
| Affiliate providers, offers, `/go/` redirects, click log, reports | Core | yes |
| Newsletter + contact storage, analytics events, SEO guards, legacy 301s | Core | yes |
| Homepage layout + its section settings, templates, CSS/JS, fonts | Theme | no (presentation) |

The theme reads data only through Core's template API (`inc/api.php`, `er_get_offers()`, `er_offer_cta_html()`, `er_offer_data()`). **No template contains an affiliate URL.**

## Content models (Core)

| Type | URL | Public | Key fields |
|---|---|---|---|
| Destination `er_destination` | `/destinations/{slug}/` | yes | tagline, region label, best time, getting there, highlights, lat/lng, trip-builder nights weight |
| Tour `er_tour` | `/tours/{slug}/` | yes | location, duration, meeting point, best time, destinations, activities, who it is for / who may prefer something else / good to know, alternatives, badge |
| Experience `er_experience` | `/experiences/{slug}/` | yes | same as Tour |
| Activity `er_activity` | `/activities/{slug}/` | yes | destinations, best time, who for / not for / tips |
| Guide `er_guide` | `/guides/{slug}/` | yes | related destinations, related tours/experiences/activities, topic |
| Article (`post`) | `/journal/{slug}/` | yes | categories, author, dates |
| Affiliate Provider `er_provider` | none | admin only | website, **allowed redirect domains**, default affiliate URL, status, logo (featured image), description (excerpt) |
| Affiliate Offer `er_offer` | `/go/{slug}/` (redirect only) | admin only | provider, target URL, affiliate URL, search-URL template, CTA label (list or custom), location, facts line, badge, price-from + currency + unit + **date checked**, destination, tour, experience, activity, utm source/medium/campaign, extra params with `{placement}` `{page}` `{offer}` sub-IDs, status, priority, start/end dates, offer category, travel styles |
| Contact message `er_message` | none | admin only | stored before email is attempted |

**Taxonomies.** These are used for grouping and filtering only; none is publicly queryable, so there are no thin term pages:

- `er_region`
- `er_travel_style` (the homepage "moods")
- `er_offer_type` (hotels, tours, cruises, transfers, cars; the homepage partner tabs)
- `er_guide_topic`

**"Ready to index".** Every editorial item has this checkbox. Until it is ticked, the page is published with `noindex, follow` and kept out of both the Rank Math and the core sitemaps. An "Index" column and dashboard health checks keep those pages visible to editors.

## Affiliate engine

**A CTA is shown only when its offer is live:**

- the offer is published and its status is "active";
- today falls within its start and end dates;
- its provider is published and active.

**`/go/{slug}/` (`includes/redirect.php`):**

1. **Resolve** the slug to an offer (or to a provider's default link).
2. **Validate:**
   - the offer is live;
   - the URL is `http(s)`, has no `user@` part, and passes `FILTER_VALIDATE_URL`;
   - the host equals, or is a subdomain of, one of the provider's **allowed redirect domains**.
3. **Log** the click. Bots, link previews, `HEAD` and prefetch requests are skipped.
4. **Append** the configured `utm_*` and extra parameters, never overriding parameters already in the URL.
5. **Redirect** with 302 (or 307).

**Failure handling.** If validation fails, the endpoint makes a 302 to the related tour/experience/destination page on this site (or the home page). An unknown slug gets a real 404.

**Visitors can never choose the destination.** Query parameters only feed the click log (`pl`, `src`). The finder values (`where`, `when`, `adults`) are whitelisted and URL-encoded into an **admin-defined** template.

**Caching and crawling.** Responses send `X-Robots-Tag: noindex, nofollow` and `Cache-Control: no-store`, and define `DONOTCACHEPAGE`. `robots.txt` disallows `/go/`. Links carry `rel="sponsored nofollow noopener"` and `target="_blank"`.

**The click log** is the `{prefix}er_clicks` table. Each row records:

- time
- offer, provider, destination, tour, experience and activity (the destination is derived from the tour or experience when the offer has none)
- source post ID and path (never a query string)
- placement, the CTA label at click time
- utm source, medium and campaign
- language

It stores **no IP address, user agent, cookie or user ID**. Retention defaults to 730 days (daily WP-Cron purge).

**Reports** live under Egypt Roamer → Click reports:

- **Date ranges:** 7, 30, 90 or 365 days, or a custom range.
- **Totals and a daily chart.**
- **Breakdowns** by provider, offer, destination, tour, experience, activity, page, CTA placement, CTA label, campaign and language.
- **CSV export**, protected against formula injection.
- **Traffic vs clicks:** the owner imports a GA4 "Pages and screens" CSV. The report flags *high traffic / low clicks* (top-quartile views, bottom-quartile click rate) and *low traffic / high click rate*. Page views are **not** tracked by the plugin, so there is no second statistics system.

**Offer matching.** Offers are language-neutral. They match a page in any language through Polylang translation groups, and match a category or travel style through term translation groups. All live offers load once per request with their meta and terms primed. Homepage queries went from 176 to 125.

## Rendering

- **Homepage** (`front-page.php`) keeps the approved cinematic markup. Every text, image, link and featured list comes from **Appearance → Homepage** (per language) and from CMS content.
  - The scripts receive `window.ER_DATA` in exactly the shapes the approved components expect.
  - Lists the scripts enhance (destinations, experiences, journal) are also server-rendered as real links.
  - Sections without real content are omitted: no live offers means no partner tabs, no finder and no "Roamer pick"; the planner budget is hidden without owner-set bands.
- **Inner pages:**
  - Destination hub: facts, highlights, content, things to do, activities, partner offers with disclosure, guides, more destinations.
  - Tour, experience and activity pages: facts, content, "Is it right for you?", good to know, partner offers with disclosure, alternatives, where it happens, related guides.
  - Guides and articles: author, publication and update dates, reading time, author bio, related items.
  - Pages, archives with GET-form filters, search and 404.
- **JavaScript:**
  - The homepage loads `home.js` (106 KB), vendored GSAP, ScrollTrigger and Lenis, plus **one** locale file.
  - Inner pages load `site.js` (38 KB raw, 11.9 KB gzip).
  - The prototype shipped a single 339 KB bundle with all 8 languages.
  - All content and links work without JavaScript.

## Multilingual (Polylang)

- **Content:** editorial types and display taxonomies are translatable. Each language has its own URL (`/fr/destinations/le-caire/`), `<html lang>` and `dir`.
- **hreflang:** Polylang outputs hreflang only for translations that exist, and verification showed reciprocal tags.
- **Language switcher:** real links; no browser-language redirect is required.
- **Interface strings:** `er_t()` uses the prototype's English keys, and the translation files (`.l10n.php`) are generated from the prototype's locale files plus 68 new strings. Those 68 are **flagged as not reviewed by native speakers**.
- **JavaScript** gets only the current language's dictionary. Locale-aware money and number formatting fixes the prototype's `$1,450` on French pages.
- **Translated content** imported by the seed (`--translations`) stays **draft** until reviewed.

## SEO split

**Rank Math SEO** is the one SEO plugin. It owns titles, descriptions, canonicals, the XML sitemap, Open Graph, schema (Organization, WebSite, Article, BreadcrumbList) and Search Console verification. **Core only adds what Rank Math cannot know:**

- the "Ready to index" gate;
- noindex for internal search, filtered archives and empty archives (never the front page);
- `/go/` excluded from crawling;
- one-hop 301s for the prototype's `/guide/*` paths and `/index.html`;
- `TouristDestination` data built only from visible destination fields;
- a minimal description/Open Graph fallback that switches itself off once an SEO plugin is active.

## Analytics

GTM is loaded only when a container ID is set, with Google Consent Mode defaults (denied by default). `assets/track.js` pushes these events:

- `affiliate_click` (offer, provider, placement, CTA, page type)
- `booking_click` (for booking-intent CTAs)
- `search` (the term, with emails and long numbers redacted)
- `filter_use`
- `newsletter_signup`
- `contact_submit`
- `guide_download` (from `data-er-download` links)

**No personal data is sent.** GA4 is configured inside GTM.

## Security

- **Admin forms and screens:** capability checks, nonces, per-field sanitisation, and escaped output.
- **Database:** prepared SQL with whitelisted identifiers.
- **Redirects:** allow-listed hosts only.
- **REST:** anonymous REST reads of offers, providers and messages are blocked.
- **CSV export:** formula-injection neutralised.
- **Public forms:** a honeypot, an HMAC-signed time trap and a 10-minute rate limit. WordPress nonces are deliberately not used, because they expire on cached pages.
