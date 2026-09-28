# EGYPT ROAMER — FULL AUDIT & REMEDIATION REPORT (28 Sep 2026)

## Method and access

- **Repository:** branch `claude/dreamy-bardeen-6qqk2h` at `a63c93a`, plus the uncommitted changes listed in §I.
- **Production (anonymous):**
  - a crawl of every internal link from `/` and the archives (24 URLs, 23 local assets);
  - Playwright + axe-core runs over 12 page types at 390/430/768/1024/1440/1920 px.
- **Production (admin):**
  - read-only inspection through the owner's logged-in wp-admin session, which covered settings, users, plugins, themes, menus, pages, Site Health and the Core health checks;
  - three settings changes (§E).
- **SSH:** the owner ran `wp egypt-roamer seed` in `~/html` and `wp cache flush`; I read the output.
- **Not available:**
  - PHP and WordPress locally, so the PHP test suites were not re-run (§I);
  - uploading the plugin or theme packages. The permalink change was also blocked from this session, and the owner has to do it (§K).

## A. Static version identified

- **Reference used:** `Egypt Roamer/` in the repo, added in `d7bef2b`.
- **How it relates to the other copy:** it is identical to the local working folder `Desktop/Egypt Insider/` (a recursive diff shows only the local-only `tools/` folder), which is where it was built. There is no other static copy.
- **What it contains:** a single cinematic homepage (`index.html`) plus `styleguide.html`, with content in 8 client-side languages. It has no subpages. Its links pointed to anchors, `#partner` placeholders or non-existent paths (`/guide/*`), and those paths now 301 to the WordPress routes.

## B. Parity status

**Homepage.** Section by section, comparing section ids in the static reference against production:

| Static section | Production | Verdict |
|---|---|---|
| loader, nav, rail, hero, journey (Pyramids/Nile/Desert/Red Sea) | present | parity |
| moods | present, plus an accessible `mood-panel` (a fix) | parity |
| destinations | present (7) | parity |
| map | present | parity |
| experiences | present (8, with images) | parity |
| partners (compare hotels/tours/cruises…) | **hidden** | intended: shown only when live offers exist (0 offers). Showing it would mean fake cards |
| guide / journal | **hidden** | intended: 0 published guides or articles (7 guides are drafts) |
| hero finder, "Roamer pick", ratings | hidden / removed | intended: fabricated ratings removed; finder needs real offers |
| language switcher | absent | intended: English only until translations are reviewed (§C) |
| planner, newsletter, footer, menu, search, saved, film | present | parity |

**Subpages.** The static site had none. WordPress adds:

- templates for destinations (7), experiences (8), and the tour, activity, guide and journal archives;
- search and 404 pages;
- draft trust and legal pages.

All return 200 with one H1. Unknown URLs return a branded 404.

**Visual.**

- The brand files served are byte-identical to the approved artwork (PRODUCTION-CHECK §5). The mobile header uses the icon-only mark, as in the static site.
- The inner pages have no photography (§D), so their cards show the approved neutral fallback.

**Fixes completed:** see §I.

## C. Multilingual status

| | Result |
|---|---|
| Languages in the static site | EN, DE, FR, IT, ES, RU, ZH, AR (client-side, one bundle) |
| Configured on production | **English only**. Polylang is not installed; `/fr/`, `/ar/` → 404 and `/?lang=fr` shows English |
| Translation status | The theme and plugin carry translations of all UI strings for the 7 languages, but none has been reviewed by a native speaker. 68 of them were written during the migration (`tools/i18n/new-strings.json`). Content translations exist only as seed drafts |
| Decision | Kept English only. Publishing unreviewed machine or AI translations would break the "no invented translations" rule. **NOT READY** until a native reviewer signs off; then follow SETUP task 15 |

## D. Media status

| | Count |
|---|---|
| Project-owned image files in the repo | 25 brand files (logos, marks, icons, OG image). **All are deployed** with the theme and byte-identical |
| Photography owned or licensed by the project | **0** |
| Photos the static design used | 30+ Unsplash photos, hot-linked by URL, never stored in the repo |
| Media Library on production | 2 items (the site icon and its crop) |
| Uploaded or assigned in this audit | 0 |

**What production shows now:**

- The homepage (hero, scenes, destination and experience cards) still hot-links the same Unsplash photos as the approved static site. They come from the Appearance → Homepage defaults.
- Destination and experience **pages and archives** have no featured images, so their cards show the neutral fallback. This inconsistency is the most visible parity gap.

Nothing was uploaded, because no owned photography exists and the brief forbids pulling photos from the web. The owner has two options:

- **(a)** Supply owned or licensed photos, then upload them and set featured images and alt text.
- **(b)** Approve the Unsplash stand-ins already used by the approved static design, then run `wp egypt-roamer seed --with-images`. Their licence permits free commercial use but does not grant ownership. Before running it, check that it only fills empty featured images.

## E. Settings status

| Setting | Before | Now | Who |
|---|---|---|---|
| Site title / tagline | Egypt Roamer / More than a destination | unchanged | ✓ |
| WordPress / Site address | https://egyptroamer.com | unchanged | ✓ |
| Timezone | UTC+0 | **Africa/Cairo** | changed in this audit |
| Reading | static Home (#65), Journal (#66), "Discourage search engines" **on** | unchanged | ✓ |
| Discussion | comments and pingbacks open by default, although the theme renders no comments (a spam path only) | **comments, pingbacks and trackbacks off for new content** | changed in this audit |
| Permalinks | **`//%postname%/`** (malformed; article URLs would get a double slash, and the seed skips it) | unchanged: **owner action** (§K) | blocked here |
| Privacy page | #3 (WordPress's draft) | unchanged; still draft | owner writes it |
| Media sizes | 150 / 300 / 1024 | unchanged (theme uses its own sizes) | ✓ |
| Menus | 5 locations, each assigned (Primary, Footer Explore/Plan/Company, Legal) | ✓ | seed |
| Plugins | egypt-roamer-core 1.1.0, **Akismet active but not configured and unused** | unchanged | owner may remove (SETUP task 20) |
| Themes | Egypt Roamer (active), Twenty Twenty-Five (fallback) | ✓ | |
| PHP / WordPress | 8.2.33 / 7.1.2 | ✓ | |
| Cache | GoDaddy edge (Cloudflare); `/go/` hop cacheable, handler BYPASS/no-store | ✓ | |

## F. Security status

| Check | Result |
|---|---|
| `/wp/v2/users`, `/users/1`, `?author=1`, `/author/morsy/` | 404 ✓ |
| oEmbed author fields | none ✓ |
| xmlrpc.php, wp-config.php | 403 (GoDaddy) ✓ |
| readme.html, license.txt, debug.log | 404 ✓ |
| Directory listing `/wp-content/uploads/`, `/wp-includes/` | 403 ✓ |
| Theme and plugin file editors | disabled ✓ |
| WP_DEBUG | off ✓ |
| Users | **1 account: `morsy` (Administrator), a guessable login**. The new admin, 2FA and deletion of `morsy` are the owner's job (§K) |
| 2FA | **NOT VERIFIED / not enabled** (no 2FA plugin; GoDaddy-level 2FA not visible) |
| SSH password | **exposed in terminal output on 28 Sep**; rotate it (§K) |
| Security headers (HSTS, X-Frame-Options…) | not sent (GoDaddy default). Advisory only |

## G. SEO status

| Item | Result |
|---|---|
| Titles | correct ("{Page} – Egypt Roamer"; home "Egypt Roamer – More than a destination") ✓ |
| Meta descriptions | home and destinations ✓. **Experiences have none**: they have no body or excerpt (content gap, not invented) |
| Canonical | singular ✓. **Archives echoed visitor query strings** (`/destinations/?utm_source=x`), which is **fixed in code** (§I), not yet deployed |
| Robots | every page `noindex` (site-wide switch plus the content gate). Gated pages printed the contradictory `noindex, nofollow, follow`; **fixed in code**, not yet deployed |
| hreflang | none (single language) ✓ |
| Sitemap | `/wp-sitemap.xml` 404 while discouraged ✓ |
| Schema | BreadcrumbList ✓. TouristDestination appears only once a destination is "Ready to index" (by design) |
| OG / Twitter | OG image = approved brand image; `summary_large_image` ✓ |
| Indexing | **kept OFF** per launch policy ✓ |
| SEO plugin | none installed (Rank Math planned; not installed in this audit) |

## H. Affiliate status

| Item | Result |
|---|---|
| `/go/{slug}/` | 302 → `admin-post.php?action=er_go&offer=…` ✓ |
| Handler | 302, `Cache-Control: no-store…private` ✓ |
| Providers / offers | **0 / 0** (15 offer drafts, paused, no link) |
| Click tracking, provider validation, paused/expired offers, open-redirect protection | code verified earlier: go-architecture 34/34, cache matrix 22/22 (LAUNCH-GATE). Production end-to-end is **NOT VERIFIED**: no real offer exists, and none was invented |

## I. Changes made in this audit and test results

**Code (uncommitted on the branch, packaged in `dist/`):**

1. `plugins/egypt-roamer-core/includes/seo.php`:
   - archive canonical and `og:url` now drop the whole query string, not only filter keys;
   - the robots filter no longer adds `follow` when WordPress already set `nofollow`.
2. `plugins/egypt-roamer-core/includes/cli.php`: the seed now collapses repeated slashes before deciding whether the permalink structure is the default, so `//%postname%/` is recognised and replaced with `/journal/%postname%/`.
3. `plugins/egypt-roamer-core/egypt-roamer-core.php`: version **1.1.1**, visible as `track.js?ver=1.1.1`.
4. `themes/egypt-roamer/assets/css/pages.css`: the fixed navigation sits below the WordPress toolbar for logged-in editors. The approved CSS files are untouched. The fix was verified by injecting the same CSS into production in the owner's session: nav top = toolbar bottom = 32 px.

**Packages:**

- `dist/egypt-roamer-core-1.1.1.zip`
- `dist/egypt-roamer-theme.zip` (without `src/`, per SETUP)

**Settings changed on production:** timezone → Africa/Cairo; comments, pingbacks and trackbacks off by default.

**Test results:**

| Test | Result |
|---|---|
| PHP syntax, all 53 theme + plugin files (php-parser 3, PHP 8.2 grammar; a negative control was caught) | **0 errors** |
| `php -l`, acceptance 14/14, leads, go-architecture 34/34, cache matrix 22/22, seed idempotency, fresh install | **NOT RE-RUN**: no PHP/WordPress/Docker on this machine. The last passing run was in the previous session, at `5f142e7`. The changes above don't touch the code paths those suites cover, except the seed permalink branch |
| Production crawl | 24 URLs, **0 internal errors**, 23 assets, **0 broken** |
| Responsive (12 pages × 6 widths) | **0 horizontal overflow, 0 clipped text, 0 broken images, 0 JS errors** |
| axe-core (12 pages at 390 and 1440) | **0 violations** |
| Seed on production | Destinations 7, Travel styles 7, Experiences 8, Guides (draft) 7, Offer drafts 15, samples trashed, Reading set, legal pages (draft) 8, Menus 5 |

## J. Remaining blockers

1. **No real affiliate providers or offers.** The site earns nothing yet, and the partner, finder and "Roamer pick" sections stay hidden.
2. **No owned photography.** The inner pages and cards have no images (§D).
3. **Content is prototype-length.** Experiences have no body text, and nothing is marked "Ready to index".
4. **Legal pages are drafts.** Privacy, terms, cookies and disclosure all need the owner's or an adviser's text.
5. **Account security:** a single guessable admin login (`morsy`), no 2FA, and the SSH password was exposed.
6. **Email delivery:** no mailer configured, so it is NOT VERIFIED.
7. **Translations are not reviewed**, so the site is English only.
8. **The permalink structure is malformed** (a one-minute owner fix).

## K. Manual actions required (owner)

1. **Rotate the SSH password.** GoDaddy → Settings → SFTP/SSH.
2. **Fix the permalink structure.** Settings → Permalinks → Custom Structure = `/journal/%postname%/` → Save.
3. **Deploy Core 1.1.1.**
   - Plugins → Add New → Upload `dist/egypt-roamer-core-1.1.1.zip` → "Replace current with uploaded".
   - Upload `dist/egypt-roamer-theme.zip` the same way (Appearance → Themes → Add New → Upload → Replace).
   - Flush the cache.
   - I then verify `track.js?ver=1.1.1`, the archive canonicals and the robots tag from outside.
4. **Secure the accounts.**
   - Create a new Administrator with a non-obvious login.
   - Enable 2FA on it.
   - Delete `morsy`, attributing its content to the new account.
5. **Decide on photography** (§D option a or b).
6. **Content, offers, legal and email:** SETUP tasks 1–8 and 13.
7. **Optional:** delete Akismet (unused).
8. **Keep "Discourage search engines" ticked.**

## Final status matrix

| AREA | STATUS | EVIDENCE | NEXT ACTION |
|---|---|---|---|
| Static parity | PASS | every static section present, or hidden only where real data is missing (§B) | re-check once offers and guides exist |
| Homepage | PASS | seeded Home is the front page; hero/journey/moods/destinations/map/experiences/planner/footer render; 0 errors | — |
| Subpages | PASS | 24 URLs 200, one H1 each; branded 404 | — |
| Destinations | NOT READY | 7 published, noindex; prototype-length copy; no images | editorial expansion + photos + "Ready to index" |
| Experiences | NOT READY | 8 published, noindex; no body, no description, no images | write content |
| Tours | NOT READY | archive 200, 0 items (none exist in the project) | create when real tours exist |
| Activities | NOT READY | archive 200, 0 items | create when real content exists |
| Guides | NOT READY | 7 drafts (titles and excerpts only) | write and publish |
| Journal | NOT READY | `/journal/` 200, 0 articles | write articles |
| Travel Styles | PASS | 7 terms, shown in the homepage moods | — |
| Multilingual | NOT READY | English only; no Polylang; translations not reviewed | native review → SETUP task 15 |
| Navigation | PASS | 5 menu locations assigned; all links 200; unpublished pages hidden | — |
| Images | NOT READY | 0 owned photos; inner cards use the fallback | §D decision |
| Media | NOT READY | Media Library holds only the site icon | §D decision |
| Branding | PASS | served brand files byte-identical to the approved artwork; mobile icon-only mark | — |
| WordPress settings | FAIL | permalink structure `//%postname%/` (timezone and discussion fixed) | §K step 2 |
| Security | FAIL | enumeration, editors and debug all pass; single guessable admin; SSH password exposed | §K steps 1 and 4 |
| Admin | FAIL | only user `morsy` (Administrator) | §K step 4 |
| 2FA | NOT VERIFIED | no 2FA plugin; GoDaddy 2FA not visible | enable on the new admin |
| Affiliate engine | NOT VERIFIED | `/go/` hop 302 and handler no-store PASS live; 0 providers/offers, so no end-to-end test | add a real provider + offer, then procedure A |
| SEO | FAIL | archive canonical echoes query strings (fixed in 1.1.1, not deployed); experiences lack descriptions | §K step 3; content |
| Indexing | PASS | intentionally off: "Discourage search engines" = Yes; every page noindex | keep until the launch gate |
| Sitemap | PASS | 404 while discouraged (expected) | Rank Math at launch |
| Robots | PASS | robots.txt default; noindex everywhere; contradictory tag fixed in 1.1.1 | deploy 1.1.1 |
| Legal | NOT READY | 8 legal/trust pages + privacy are drafts with notes | owner or adviser text |
| Email | NOT VERIFIED | no mailer plugin; no test send | SETUP task 13 |
| Performance | NOT VERIFIED | no field or lab measurement from a real browser on production | PageSpeed Insights on `/` and `/destinations/` |
| Mobile | PASS | 390/430/768: 0 overflow, 0 clipping, 0 JS errors | — |
| Accessibility | PASS | axe 0 violations on 12 pages at 390/1440 | — |
| 404 / redirects | PASS | unknown URL 404 + noindex; `/guide/`, `/index.html` 301 → targets; `/privacy/` 404 until the privacy page is published (by design) | — |
