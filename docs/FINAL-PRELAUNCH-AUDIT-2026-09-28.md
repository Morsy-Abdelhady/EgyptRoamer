# Egypt Roamer — final pre-launch audit (2026-09-28)

Production: https://egyptroamer.com. Code: `main`, deployed automatically by `.github/workflows/deploy-production.yml`.

Evidence is what was measured on production today. Where production could not be reached from here, the local install (`C:\Users\morsy\er`, same code) was used.

**Launch decision: NOT READY.** The blockers are listed in §X.

---

## A. Executive summary

The code and platform are in good shape. The following all passed on production:
- 176 pages in 8 languages: all 200, correct `lang`/`dir`, self-canonicals, hreflang with x-default, one H1 per page, one consistent robots directive.
- 108 page views across 6 widths: no layout overflow and no JavaScript errors.
- axe: **0 violations**.
- CLS 0, TTFB about 245 ms, first paint about 300 ms.
- Affiliate redirects resist open-redirect and path attacks.
- User enumeration is closed.
- Automatic CI/CD works: 3 successful production deploys today.

What blocks launch is **content, trust and owner actions, not code**:
- Every legal and trust page (Privacy, Terms, Cookies, Affiliate Disclosure, Contact, FAQ, Our Story, How We Choose) is still a draft.
- 0 affiliate providers, so no live offers.
- 0 owned photographs.
- Translations are live but not reviewed by native speakers.
- The single admin has no 2FA.
- Email delivery is untested.

### Fixed and deployed in this audit

| Commit | Change | Verified on production |
|---|---|---|
| `7a974ba` (Core 1.2.2, theme 1.1.1) | `og:locale` + 7 `og:locale:alternate` on every page (was missing on 176/176) | en_US/de_DE/ar_AR/zh_CN/ru_RU correct, 7 alternates each |
| `7a974ba` | Archive meta description from the editable archive intro; Journal from its excerpt | code path live; the intros themselves are still empty (content) |
| `7a974ba` | GoDaddy's `godaddy-launch` mu-plugin enqueued `wp-components`, `wp-theme` and `godaddy-styles` on the front end, render-blocking and unused. They are now dropped for logged-out visitors | the 3 stylesheets are absent; page renders unchanged |
| `ede7f71` (theme 1.1.2) | Hero word cut off at the right edge: "ÄGYPTEN" at 360–552 px (up to 45 px), "ЕГИПЕТ" at 360–430 px. WordPress-only size cap below 900 px | de/ru/en fit 320–900 px (glyph bounds, after Run #4) |

Earlier today, also live: Core 1.2.0/1.2.1 and theme 1.1.0. They include the language-neutral map/mood ids, mood photos in every language, stand-in photos on inner pages, menus hiding empty sections, and the Nile scene linking to Aswan.

## B. Static reference vs WordPress

The reference is `Egypt Roamer/` (static, never deployed). Homepage section order and components match (see FULL-AUDIT §B). There are two intentional differences:
- **Partners, finder, "Roamer pick" and ratings are hidden.** There are 0 live offers, and the ratings were fabricated.
- **Guide/journal on the homepage is hidden.** There are 0 published articles.

The language switcher now exists, as in the reference (8 languages). One reference defect was inherited and fixed: the hero word overflow in German and Russian (§L).

## C. Sitemap (production, crawled)

| Route | Status |
|---|---|
| `/` + 7 language homes | 200, full |
| `/destinations/` + 7 destination pages, × 8 languages | 200 |
| `/experiences/` + 8 experience pages, × 8 languages | 200; pages are thin (no body text, §E) |
| `/tours/`, `/activities/`, `/guides/` × 8 | 200 "Nothing published here yet"; **not linked** anywhere (menus hide them), no hreflang |
| `/journal/` (+ `/de/journal-de/` …) | 200, empty; not linked |
| `/contact/`, `/privacy-policy/`, `/terms/`, `/cookies/`, `/affiliate-disclosure/`, `/faq/`, `/our-story/`, `/how-we-choose/`, `/partner-with-us/` | **drafts → 404**; not linked |
| `/?s=`, 404 page | 200 / 404 correct |
| `/go/{slug}/` | 302 → handler; unknown slug → home |

## D. Missing pages created

None. The trust and legal pages already exist as drafts, and the seed never auto-publishes them. They need the owner's real texts: legal content, company facts, the selection method. Writing them here would be invented content.

## E. Content quality

- **Destinations (7):** useful facts (region, best time, getting there, highlights) and an excerpt.
- **Experiences (8):** **no body, excerpt or summary**, only title, location and duration. Too thin to launch or index. This is the main editorial gap.
- **Guides (7):** drafts with titles only.
- **Tours / activities / articles:** none.
- **Meta descriptions:** missing on 104/176 pages. These are exactly the thin experience pages and the archives with empty intros. The fix is content: experience excerpts, and the archive intros under Egypt Roamer → Settings.

## F. Multilingual (8 languages: en, de, fr, it, es, ru, zh, ar)

The languages were confirmed from the static source and Polylang.

| Check | Result |
|---|---|
| Homepages load in their language | 8/8 |
| `html lang` / Arabic `dir="rtl"` | 176/176 correct |
| Switcher maps to the translated page (not the home) | ✓ e.g. Cairo ↔ Kairo ↔ Le Caire ↔ القاهرة |
| Navigation stays in language | ✓ (only switcher links cross languages) |
| hreflang incl. x-default, self-referencing | ✓ on all pages with translations; missing only on the 24 empty archives |
| Canonical self, query strings dropped | ✓ |
| og:locale | ✓ (fixed today) |
| Menus | per-language menus assigned |

## G. Translation quality — NOT READY

The UI and content translations are the prototype's and seed's machine/AI translations. None has been reviewed by a native speaker. They are **live on production**. Proper nouns are consistent (Kairo, Le Caire, Каир, 开罗, القاهرة). A native review per language is a launch blocker, under the project rule against publishing unreviewed translations as final.

## H. Language switcher

Header and mobile menu both work, with correct targets in every language (see F).

## I. Media

| | Count |
|---|---|
| Owned brand assets (logo, icons, OG image) | 25, deployed |
| Owned or licensed photographs | **0** |
| Media Library | 2 (site icon) |
| Photos shown | prototype Unsplash photos, hot-linked, **owner-approved as stand-ins** on 2026-09-28 |

Launch needs owned or licensed photography, uploaded as featured images. The theme prefers them automatically, with srcset.

## J–K. UI / UX

- Design tokens, typography (Playfair Display × Inter) and colours (#C9A227, #101820, #FFFFFF) are unchanged from the reference.
- The value proposition is clear in the hero.
- Discovery paths work: destinations, map, moods → destination/planner.
- Commercial CTAs are absent because there are no offers. That's correct: no dead or fake CTAs.
- Empty sections are hidden rather than shown empty.
- A dead end remains: experience pages have almost no content (§E).

## L. Responsive

| Test | Result |
|---|---|
| Production: 18 page types × 390/430/768/1024/1280/1440 | no horizontal overflow, no clipped headings |
| Glyph-accurate text overflow, 29 pages × 360/390, all 8 languages (local, with fix) | **none** |
| Hero word before the fix (production, glyph bounds) | de clipped 360–552 px, ru 360–430 px |
| After the fix | local: every language fits 320–900 px; production after Run #4: de, ru, en fit 320–900 px |
| Arabic | `dir=rtl` on every page; carousels peek from the correct side |

Lesson: box-based overflow checks missed the hero, because the glyphs overflowed a box that was itself within the screen. The QA scripts now also measure glyph bounds.

## M. Accessibility

axe-core on production, 18 page types × 390 and 1440, all 8 languages: **0 violations** (critical/serious/moderate/minor). There is one H1 per page, and every image has an `alt` attribute (decorative ones empty).

## N. Performance (production, lab)

TTFB 245 ms · FCP 300 ms · **CLS 0** · HTML 21 KB · about 600 KB total (images 299 KB, fonts 136 KB, JS 105 KB, CSS 43 KB). The LCP hero image is preloaded with `fetchpriority=high`.

Removed today: 3 render-blocking GoDaddy stylesheets.

Remaining render-blocking resources:
- The theme's own CSS (9 files, approved structure).
- GoDaddy's `tccl-tti.min.js`, which the host injects.

LCP and INP were not measured in the field. The site is noindex, so there's no CrUX data. **NOT VERIFIED.**

**Caching finding:** production HTML is sent with `Cache-Control: public, max-age=2678400` (31 days, set by GoDaddy). Returning visitors' browsers may show week-old pages after a deploy. Forms are unaffected (no nonces). This is a GoDaddy cache setting to review; the theme doesn't override host caching.

## O. SEO

- Titles are unique within a language.
- Canonicals are self-referencing and drop parameters.
- One robots directive (`noindex, nofollow`).
- `robots.txt` doesn't block crawling.
- The core sitemap is off while the site is non-public (expected).
- JSON-LD: BreadcrumbList on inner pages; TouristDestination when the setting is on and the page is indexable.
- og:image and og:locale are present.
- **Gaps (content):** 104 missing descriptions, thin experience pages.
- **Rank Math:** planned, not installed. The Core fallback covers titles, descriptions, canonicals and OG until then.

## P. Security

| Check | Result |
|---|---|
| `/wp-json/wp/v2/users`, `?author=N`, `/author/*` | 404 |
| oEmbed | no author fields |
| `xmlrpc.php`, `wp-config*`, `.env`, `.git/` | 403 |
| `readme.html`, `license.txt`, `debug.log` | 404 |
| Directory listing (`uploads/`, `plugins/`) | 403 |
| `/go/` unknown slug; `offer=https://evil.example` | → homepage (no open redirect) |
| Path traversal in `offer` | 403 (WAF) |
| XSS probe in search | 403 (WAF) |
| Administrators | **1 (`morsy`), no 2FA plugin: blocker** |
| `seed.json` and theme `src/js` publicly readable | low risk: no secrets, only prototype content and source |
| Old SSH credentials | rotation **NOT VERIFIED** |

## Q. WordPress settings (read in wp-admin today)

| Setting | Value | |
|---|---|---|
| Title / tagline | Egypt Roamer / More than a destination | ✓ |
| URLs | https://egyptroamer.com | ✓ |
| Timezone | Africa/Cairo | ✓ |
| Reading | static Home, Journal = posts page, **Discourage indexing ON** | ✓ |
| Permalinks | `/journal/%postname%/` (malformed `//%postname%/` fixed) | ✓ |
| Discussion | comments, pingbacks and trackbacks off | ✓ |
| Privacy page | assigned ("Privacy Policy", still draft) | content |

## R. Plugins

| Plugin | Status | Verdict |
|---|---|---|
| Egypt Roamer Core 1.2.2 | active | required |
| Polylang 3.8.10 | active | required (only multilingual plugin) |
| Akismet 5.7.2 | active | **unused**: comments are off; forms use a honeypot, a signed time trap and a rate limit. Deactivate (owner; my attempt was blocked by the permission check) |
| SEO plugin | none | Rank Math planned; install exactly one before launch |

## S. Affiliate readiness

The engine was verified: `/go/` hop → admin-post handler, validation, click log, noindex, no open redirect.

| | Count |
|---|---|
| Providers | **0** |
| Offers | 15 drafts from the prototype (no provider/URL) |

E2E on production is **NOT VERIFIED**: there's no real provider or URL, and none may be invented. The local acceptance suite passes the whole flow (provider → offer → CTA → click → report → edit).

## T. Forms / email

The contact form and newsletter work locally, including the success state, the bot-speed rejection and the analytics events. On production the Contact page is a draft. Email delivery is **NOT VERIFIED**: no real send tested.

## U. 404 / redirects

- Unknown page → 404 template (200 for existing, 404 otherwise).
- `/go/` unknown → home.
- Query variants canonicalise.
- No redirect chains observed.

## V. CI/CD

Push to `main` → tests (PHP 8.1/8.3 lint, versions, seed JSON, bundles) → GoDaddy deployer (theme + Core together, then per-folder cleanup) → live version check. Runs today: #1 failed (wrong SSH host, fixed); #2 (Core 1.2.1), #3 (Core 1.2.2, theme 1.1.1) and #4 (theme 1.1.2) succeeded, each with the live check "expected Core".

**Finding: cleanup doesn't delete.** Run #2's theme step listed 27 `src/js/*` files as "to be DELETED". GoDaddy's server deployer logged "file sync done … Cleanup done" without deleting them, and they still exist on the origin. The server deployer also logs no health-check line. So "files removed from git are removed on the server" and "health check ran" are **NOT VERIFIED**.

Workaround until GoDaddy answers: delete removed files by hand (SFTP / File Manager). Current leftovers: `wp-content/themes/egypt-roamer/src/` (harmless source).

## W. Production verification

Every fix above was checked on production after its deploy (versions, meta tags, stylesheets, hero fit). The site answers 200 on all 176 URLs with no fatal errors.

## X. Remaining blockers (priority order)

1. **P0 — Admin security:** add a second administrator with 2FA (e.g. the "Two Factor" plugin), verify it, then demote or remove `morsy`. Confirm the old SSH credentials were rotated.
2. **P0 — Legal/trust pages:** write and publish Privacy, Terms, Cookies and Affiliate Disclosure, then Contact, FAQ, Our Story and How We Choose. Publish their translations only after review.
3. **P0 — Translations:** native review of the 7 non-English languages (UI + content) before launch.
4. **P1 — Affiliate:** add the real providers and offers (URLs, tracking), then run the E2E test on production.
5. **P1 — Content:** experience pages need real bodies/excerpts; archive intros; guides written and published.
6. **P1 — Photography:** owned/licensed photos as featured images (Unsplash stand-ins are temporary).
7. **P1 — Email:** send a real test from the contact form and confirm delivery.
8. **P2 — SEO plugin:** install Rank Math (only one), configure it, keep noindex until the gate passes.
9. **P2 — Hosting:** review the 31-day HTML browser cache; ask GoDaddy about deployer cleanup and health-check logging; delete the stale theme `src/`.
10. **P2 — Deactivate Akismet.**
11. **Gate:** only then untick "Discourage search engines" (LAUNCH-GATE.md).
