# Egypt Roamer — Pre-migration audit

Audited: commit `d7bef2b` ("Egypt Roamer"), folder `Egypt Roamer/`, on 2026-09-26.

How it was audited:

- Every source file was read: `index.html`, `styleguide.html`, 6 CSS files, `data.js`, `i18n.js`, `main.js`, `utils.js`, 15 components and 7 locale files.
- Headless Chromium (Playwright) loaded the page in all 8 languages. English ran at 390 / 430 / 768 / 1024 / 1440 / 1920 px; the other 7 languages ran at 390 and 1440 px.
- axe-core scanned EN at 1440 and 390 px and AR at 390 px.
- Pixel sampling checked the logo colours.
- A line-level diff compared `app.js` against the ES-module sources.

Limitation of this sandbox: its network policy blocks `images.unsplash.com` and `cdn.jsdelivr.net`. So the runtime checks ran without the photos and without GSAP/Lenis, which exercises the site's no-motion fallback. **Photo loading and the GSAP scroll choreography are NOT VERIFIED** by this audit. The project's own `qa-shots/` contains screenshots of them from the original author's environment.

## What exists

| Item | Status |
|---|---|
| Pages | **1 real page**: `index.html`, a single-page homepage. Plus `styleguide.html`, an internal visual-system page. |
| Routes | None. Every navigation link is an in-page anchor (`#destinations`, `#partners`, …). |
| Content | All content is hardcoded in `assets/js/data.js`: 7 destinations, 7 moods, 8 experiences, 5 partner categories × 3 offers, 7 guide teasers and 5 film slides. |
| Languages | 8 languages (EN, DE, FR, IT, ES, RU, ZH, AR) switched **client-side** by `?lang=` and localStorage, all on one URL. |
| Build | `app.js` is a generated bundle. `tools/build.py` is referenced in the README but **not committed**. |
| Brand | Approved logo artwork in `assets/img/brand/source/`, plus derived light/dark lockups, the symbol, favicons, app icons and an OG image. |

## A. Working correctly

- **No horizontal page overflow.** `scrollWidth == viewport` at every tested width in every tested language, including RTL Arabic.
- **Exactly one `<h1>`** in every language.
- **JavaScript is clean.** No errors in any run. The only console errors are resource loads blocked by the sandbox.
- **`<html lang>`/`dir` are correct per language.** `dir=rtl` is set for Arabic before first paint.
- **The logo follows the colour rule.**
  - Light lockup: `#101820` wordmark and left pyramid, gold `#C9A226`/`#C9A227`.
  - Dark lockup: `#FFFFFF` wordmark and left pyramid, same gold.
  - Aspect ratio 1759×203 is preserved and the `width`/`height` attributes are set.
  - The ±1 difference on the gold's blue channel comes from anti-aliasing in the approved source, not from recolouring.
- **The bundle matches its sources.** `app.js` is line-for-line in sync with the ES-module sources.
- **The UI translation is essentially complete.** The only runtime "missing" keys are:
  - brand names (Booking.com, Expedia, Hotels.com), which correctly stay untranslated;
  - three initial placeholders ("EN", "Ancient", "8 nights · Cairo, Luxor & Aswan") that JavaScript replaces immediately.
- **Graceful degradation works.** Without GSAP/Lenis the journey falls back to stacked scenes. The page stays usable, as verified in this sandbox.
- **Existing accessibility groundwork:** a skip link, a visually-hidden label on the newsletter email field, and alt text on content images (every `<img>` has an `alt` attribute).
- **Affiliate intent is already honest in copy.** There is a disclosure paragraph in the partners section, "You book directly with them" wording, `rel="sponsored noopener"` on affiliate anchors, and no fake checkout.

## B. Errors

| # | Error | Evidence |
|---|---|---|
| B1 | **Fabricated ratings and review counts are shown as real.** For example "4.9 (2,300)" and "4.8 (1,140)" in the scene picks, and ratings/reviews on every experience and offer card. | `data.js` header: "Prices, ratings and review counts below are SAMPLE values". The page renders them without a sample label. |
| B2 | **Fabricated prices are shown as real** ("from $45", "$420", "$320 / night"). The trip-builder budget ("Est. per person $1,450 – $2,100") comes from invented per-night bands. | `data.js`, `index.html` scene picks, planner `styleRates` |
| B3 | **Unsupported claims:** "Tested by our editors, rated by thousands of travellers", "Live prices from trusted partners", "No hidden mark-ups", "Instant confirmation", "Best price from trusted lines", "Free cancellation…". | `index.html`, `data.js` `trust` arrays. No live pricing integration exists. |
| B4 | **All 28 affiliate links are placeholders** (`#partner`). Clicking one shows a toast instead of navigating. | `micro.js initAffiliateLinks` |
| B5 | **12 footer links point to pages that do not exist** (`/about`, `/contact`, `/privacy`, `/terms`, `/cookies`, `/affiliate-disclosure`, `/how-we-choose`, `/partner-with-us`, `/guide`, 3 × `/guide/*`). The 7 guide teasers also link to missing `/guide/*` pages. | Link inventory in the Playwright run |
| B6 | **Forms don't submit anywhere.** The newsletter shows "You're on the list" but stores nothing. The trip builder shows "Draft saved" but saves nothing. The hero finder shows "Comparing … on partner" but goes nowhere. All three are misleading success messages. | `micro.js initNewsletter`, `planner.js`, `hero.js` |
| B7 | **Two buttons have no accessible name at mobile widths** (axe, *critical*): `.play` and `.finder__go`. Their text labels are hidden by CSS. | axe EN/AR 390 px |
| B8 | **Interactive controls sit inside `role="img"`** on the map SVG (axe, *serious*, `nested-interactive`). | axe `.map__svg` |
| B9 | **Duplicate landmark name**: four `<aside aria-label="Roamer pick">` (axe, *moderate*). | axe |
| B10 | **Currency and number formatting are hardcoded to `en-US`/`$`** in every language (`utils.js money/fmt`). French, for example, shows "$1,450". | `utils.js` |
| B12 | **Clicking any hero finder tab throws `TypeError: t is not a function`** — the tab loop variable `t` shadows the translate function, so the button label never updates. Reproduced in Chromium. | `hero.js` `initFinder` |
| B13 | **Brand colour mismatch:** `--charcoal` is `#111111`, the brand specification (and the logo artwork, `theme-color`) is `#101820`. | `tokens.css` |
| B11 | The README documents `tools/build.py`, `tools/dev_server.py`, `tools/brand_assets.py`, `tools/check_locales.py` and `tools/qa/*`. **None are in the repository**, and `.claude/launch.json` points to the missing dev server. | `ls` |

## C. Missing functionality

- **No affiliate system**: no providers, offers, tracked redirect, click logging or reporting.
- **No CMS**: all content is JavaScript data.
- **No lead capture backend**: no newsletter storage, CRM or contact form.
- **No analytics**: no GA4, GTM or Search Console verification, and no event instrumentation.
- **No real search**: search is an in-memory filter over the same 22 items and navigates to homepage anchors.
- **No consent management** for cookies or analytics.

## D. Missing pages

Every subpage is missing:

- destination, tour, experience, activity, guide and article archives and details;
- search results;
- About, Contact and FAQ;
- Affiliate Disclosure, Privacy, Terms and Cookies;
- 404.

Two further footer-linked pages are missing: "How We Choose" and "Partner With Us".

## E. Duplicate or unnecessary items

- **`styleguide.html`** is an internal design reference and must not be a public, indexable page.
- **`qa-shots/`** (22 MB of screenshots) is a development artefact and must not ship to production.
- **Navigation is duplicated** in three places (desktop links, mobile menu, footer). In WordPress this becomes menus.
- **The language menu is duplicated** as a desktop listbox and mobile buttons. Acceptable, but it must render real links.
- **The same "Explore" destinations/experiences appear in 4 places** (moods recs, destinations, experiences, partners). Fine for a homepage, as long as all of them come from one data source.

## F. Missing content models

These are the models needed; none exist yet.

- Destination
- Tour
- Experience
- Activity
- Guide
- Article
- Affiliate Provider
- Affiliate Offer
- Offer category: hotels / tours / cruises / transfers / cars
- Travel style (the "moods")
- Region
- Click log
- Newsletter subscriber
- Contact message

## G. SEO problems

- **One URL for everything**, so there are no landing pages for any destination, tour or guide intent.
- **Almost all content is injected by JavaScript** from `data.js` (cards, destinations, guides, offers). Without JS the sections are empty containers.
- **Missing head and crawl basics:** no canonical, `robots.txt`, XML sitemap, hreflang or structured data.
- **OG image and manifest use relative URLs** (`og:image` = `assets/img/brand/og-image.jpg`). Social scrapers need absolute URLs.
- **The `<title>` and meta description are rewritten by JavaScript** per language on the same URL. Crawlers see only English.
- **Section titles** (Pyramids, Nile, …) are marketing `h2`s that duplicate each other's intent. That's fine on a homepage, but no page targets those topics.

## H. Affiliate problems

- **No real affiliate links**: placeholders only (B4).
- **Partner names** (GetYourGuide, Viator, Booking.com, Expedia, Hotels.com, Welcome Pickups, Rentalcars.com, DiscoverCars) appear as if the partnerships exist. **Whether any of these accounts actually exist is NOT VERIFIED.** They must not be migrated as active providers without the owner's confirmation.
- **Fabricated prices and ratings sit on affiliate cards** (B1, B2).
- **No click tracking**, so there's no data on which page, CTA or offer converts.
- **The disclosure appears in one section only.** The disclosure page itself is missing.

## I. Language problems

- **All 8 languages share one URL** (`/?lang=xx`, with the choice remembered in localStorage). Search engines cannot index language versions independently, and there is no hreflang.
- **The language choice persists in localStorage.** A visitor who once chose French gets French on a plain `/` URL, so the same URL serves different languages.
- **Editorial content is only partly localised.** UI strings and card copy are translated, but there are no translated pages because no pages exist. Whether the 7 translations were **reviewed by a native speaker is NOT VERIFIED**. Treat them as UI translations, not reviewed editorial content.
- **Currency and number formatting stay US-style** in all languages (B10).
- **All 8 languages ship in the bundle** (~70 % of `app.js`), even though only one is used per visit.

## J. Performance problems

- **`app.js` is 339 KB (96 KB gzip)**, mostly locale dictionaries for 7 unused languages.
- **Render-blocking CSS:** six separate stylesheets, 118 KB raw / 22.6 KB gzip.
- **Every image is hotlinked from `images.unsplash.com`.** There are no local responsive sizes, no Media Library, and no control over availability.
- **Google Fonts load from Google's CDN.** That adds a third-party connection and a GDPR exposure for EU visitors. Self-hosting is recommended.
- **GSAP, ScrollTrigger and Lenis load from jsDelivr** (a third-party CDN).
- **The loader overlay holds the hero for at least 1.3 s** on every page load. That's acceptable on the homepage, but it must not be reused on subpages.

## K. Accessibility problems

- **The three axe violations** B7, B8 and B9.
- **The mood dial uses `role="tablist"`/`role="tab"`** without matching `tabpanel`s or `aria-controls`.
- **The destination auto-advance timer** changes content every 7 s. It pauses on hover, but there's no visible pause control.
- **Contrast of the gold text on light backgrounds is NOT VERIFIED.** axe reported no contrast violation, but images didn't load in this sandbox, so contrast over photos was not tested.

## L. Migration risks

- **The homepage behaviour is deeply coupled to `data.js` shapes.** The WordPress data payload must keep the same shapes, or the components must be edited.
- **The build tool is missing** (B11), so any JS change needs the build recreated.
- **Unsplash photos must go into the Media Library** for image SEO, and their availability needs to be confirmed on the production server.
- **Legacy URLs:** no URL from this project has ever been live on a server (as far as the repository shows). So there is no URL equity to preserve. The only "legacy" paths are the links already written into the design (`/guide/*`), and they will be 301-mapped. **Whether a previous site exists at the production domain is NOT VERIFIED.**
- **The sample prices, ratings and review counts must not be migrated** (B1, B2).

## What must remain unchanged

- **The visual design:** tokens, type (Playfair Display × Inter), components, cinematic homepage journey, RTL styling.
- **Brand assets**, used exactly as supplied.
- **The editorial voice and existing copy** (destination descriptions, section headings), which migrate as editable content.

## Found while migrating (fixed in the WordPress build)

- `journey.js` tweens the optional "Roamer pick" asides and the finder without null checks — once those become optional (only shown for live offers), GSAP throws. Guarded.
- WordPress adds `search` to `<body>` on results pages, which the design uses for the search overlay (`.search`) — caused horizontal overflow at 390/430 px. Class removed.
- A homepage built on "latest posts" is `is_home()`; the empty-archive noindex rule would have noindexed the home page. Excluded explicitly.
