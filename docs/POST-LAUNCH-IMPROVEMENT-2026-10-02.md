# Post-launch improvement audit (2026-10-02)

Releases:
- **Theme:** 1.2.29 → **1.2.32**.
- **Core:** 1.2.18 → **1.2.19**.
- **Deploy check:** fixed in commit 815259a.

All are deployed, flushed and verified on production. The indexing architecture is unchanged:
- 176 indexable pages and 176 sitemap URLs;
- the same canonical/hreflang logic;
- no URL changes.

## A. Baseline (production, before changes)

| | Value |
|---|---|
| Versions | Core 1.2.18, theme 1.2.29 |
| Indexing | on: `blog_public` 1, 176 pages "Ready to index" |
| Sitemap | `/wp-sitemap.xml`: 32 sub-sitemaps, 176 URLs (22 per language) |
| robots.txt | `/wp-admin/` disallowed, admin-ajax allowed, `/go/` disallowed, Sitemap line |
| Canonical / hreflang | 0 errors on all 176 pages (earlier crawl today) |
| Search Console | property connected. Sitemap shows "Couldn't fetch / 0", which looks like "not processed yet": the server side returns a valid 200 (separate report today) |
| PageSpeed, mobile, `/` | Perf 87 · FCP 2.2 s · **LCP 3.3 s** · TBT 40 ms · CLS 0 · SI 4.8 s · **page weight 3,097 KiB**, "improve image delivery" 1,001 KiB |
| PageSpeed, desktop, `/` | FCP 0.5 s · LCP 0.8 s · TBT 10 ms · CLS 0.007 · SI 1.6 s |
| PageSpeed, mobile, `/ar/` | Perf 78 · FCP 3.0 s · LCP 4.4 s · weight 1,300 KiB · 3 render-blocking stylesheets |
| Homepage payload (decoded) | CSS 128 KB (1 file) · fonts 137 KB (6) · JS 247 KB (5) · HTML 142 KB (23 KB gzip) |

The GTmetrix and PageSpeed API quota is exhausted for today, so I used the PageSpeed web UI.

## B–E. Findings, priority, root cause, fix

| # | Priority | Finding | Root cause | Fix |
|---|---|---|---|---|
| 1 | **High** (perf) | Phones downloaded ~900 KB of hidden pictures on the homepage. One mood layer alone was 457 KB. | The 7 mood backgrounds are stacked at opacity 0 with `loading=lazy`. When the section nears the screen, all 7 load, though only one is visible. | Only the visible layer has a source. The others load on the first touch or focus of the dial, or, on desktop, when the section is 400 px away. A swap waits for `decode()` (no dark flash). `moods.js` |
| 2 | Medium (perf) | Desktop received 2,000–2,400 px scene files for ~1,500 px slots. | Coarse srcset steps (900/1400/2000, 1000/2400/3200) | Added 1200/1600/1800 steps. The browser still picks a file at least as large as the slot, so sharpness is unchanged. `front-page.php` |
| 3 | Medium (perf) | The Arabic homepage loaded 3 render-blocking stylesheets. | `fonts-arabic.css` (1 KB) and `rtl.css` (3 KB) were separate requests. | Printed inline after the bundle, in the same cascade order. Font URLs are absolute, and both files count in the stale-page build ID. `inc/assets.php` |
| 4 | **High** (UX, conversion) | An experience page with no live offer ended at the facts, with no next step. Every experience has no offer today. | The aside rendered only the offer box. | "Need personal help?" opens the team chat (or the Trip assistant when chat is off), plus Plan My Trip. All strings already exist in 8 languages. `single-commercial.php`, `assistant-loader.js` |
| 5 | **High** (visual, zh) | Chinese page titles were shrunk to about 30 px on phones (48 px in other languages). | `initWordFit` splits on spaces, so a Chinese title with no spaces counts as one "word" and is scaled to fit one line. | Each CJK character is its own unit. All 8 zh experience titles are now 48 px. `fit.js` |
| 6 | Medium (visual) | The section tab bar cut the last label mid-word ("Wh…") at 1280 px, with no scroll cue. | Overflowing flex list with a hidden scrollbar | An edge fade on the side with more tabs (physical sides, so it works in RTL). Keyboard focus stops clear of the fade. `sections.js`, `pages.css` |
| 7 | Medium (visual) | On phones, the gold eyebrow on pale hero photos (White Desert) was nearly invisible, and the white title was weak. | Both shades fade out about a third of the way down, where the text sits on a phone. | A steadier phone shade plus a soft text shadow. Desktop is unchanged. All 15 phone heroes reviewed. `pages.css` |
| 8 | Low (editors) | For logged-in editors, the sticky section tabs slid under the WordPress toolbar. | `top: 0` / `top: var(--nav-h)` ignore the 32/46 px toolbar. | Toolbar-aware `top` values. `pages.css` |
| 9 | Medium (assistant) | "snorkeling" found nothing, though 3 pages cover snorkelling. "cruises" missed the Nile Dinner Cruise. Arabic questions pulled in Hurghada/Sharm because of "best / time / visit". | Substring matching with no spelling or inflection variants; the first matching variant was scored, not the best; no Arabic stopwords. | Latin stems (min 5 letters, so "diving" is not "div"), best-variant scoring, Arabic question stopwords. 30 realistic, adversarial and multilingual queries re-checked. `assistant.php` (Core) |
| 10 | **High** (reliability) | The 1.2.30 deploy ended "failed" after the files were deployed. | The server health check still asserted `blog_public = 0` (pre-launch). | The check now expects `1`, so it catches indexing being switched off by mistake. `deploy-production.yml`, `DEPLOY.md` |

### Reviewed and deliberately not changed
- **Arabic hero at 834–1920 px:** the text block sits left of centre over the sky. This is intentional (the approved Arabic hero fix): the photo isn't mirrored, and the big pyramid fills the right.
- **Legal entity name (Arabic) in translated legal pages:** it renders correctly as an isolated RTL run.
- **The launcher passing over text** while scrolling on phones: a floating control, with nothing clipped (checked at 360 px).
- **Homepage H1 "Egypt You Feel It":** approved slogan. The title carries the intent ("Egypt – Travel Guide").
- **115 descriptions over 160 characters:** approved excerpts (your earlier decision).
- **Logo 600 px file at 320 px** (17 KB): too small to matter.

## F. Before / after (production, PageSpeed mobile)

| Page | Run | Perf | FCP | LCP | TBT | CLS | SI | Weight |
|---|---|---|---|---|---|---|---|---|
| `/` before | 1 | 87 | 2.2 s | 3.3 s | 40 ms | 0 | 4.8 s | **3,097 KiB** |
| `/` after | 1 | 87 | 2.2 s | 3.3 s | 40 ms | 0 | 4.9 s | **537 KiB** |
| `/` after | 2 | 87 | 2.2 s | 3.3 s | 30 ms | 0 | 4.9 s | 537 KiB |
| `/` after | 3 | 93 | 1.8 s | 2.9 s | 10 ms | 0.003 | 3.3 s | – |
| `/ar/` before | 1 | 78 | 3.0 s | 4.4 s | 20 ms | 0.004 | 4.1 s | 1,300 KiB |
| `/ar/` after (1.2.31) | 1 | 78 | 3.1 s | 4.3 s | 60 ms | 0 | 4.2 s | – |

**What moved:**
- Mobile homepage weight is down 83%: 3,097 → 537 KiB, and "improve image delivery" fell from 1,001 to 94 KiB.
- On a real phone that saves data and decoding work.

**What didn't move:**
- LCP and FCP are unchanged: on PageSpeed the LCP is the hero text, and its render delay is font-bound (about 2.2 s on `/`).
- On `/ar/`, removing two render-blocking requests did not measurably change LCP. Its first view is bound by about 345 KB of Arabic web fonts (7 files: Noto Naskh 400/500/600 and IBM Plex Arabic 300/400/500/600).
- The run-to-run spread (2.9–3.3 s) is PageSpeed's network simulation variance.

The remaining levers need your decision (section J).

## G. Screenshots reviewed

**Local, all 8 languages:**
- 6 new widths (360, 414, 834, 1024, 1280, 1920) × 9 states: home, footer, destination, experience, menu, assistant answer, live chat, 404, empty search.
- Plus the loading state at 390.
- 448 screenshots in contact sheets.
- The French 834 set was retaken after a local proxy error.

**Extra states:**
- Archives (destinations, experiences, empty guides) and legal/contact pages in en/ar/de at 390/1440.
- Reduced motion, keyboard focus (home and destination), and the logged-in toolbar at 390/700/1440.
- All 15 phone heroes before and after fix 7.
- The section tabs at start and end of scroll (en/ar).
- The help box in en/ar/de, including the chat opening.
- The mood dial before and after a tap.

**Production:**
- Full-page phone screenshots of the homepage and the White Desert page, used for the second, independent pass.

Yesterday's 320/390/768/1440 matrix (`VISUAL-AUDIT-2026-10-02.md`) still stands for the layouts these changes didn't touch.

## H. Files changed

**Theme** (`wordpress/wp-content/themes/egypt-roamer/`):
- `src/js/components/{moods,sections,fit,assistant-loader}.js` and the rebuilt `assets/js/{home,site}.js`.
- `assets/css/pages.css` and the rebuilt `bundle-{home,site}.css`.
- `front-page.php`, `template-parts/single-commercial.php`, `inc/assets.php`, `functions.php`, `style.css`, `CHANGELOG.md`.

**Core:** `includes/assistant.php`, `egypt-roamer-core.php`.

**CI and docs:** `.github/workflows/deploy-production.yml`, `docs/DEPLOY.md`.

## I. Production verification

- **Deploys:**
  - 1.2.31 and 1.2.32 deployed with **success**, including the corrected health check.
  - 1.2.30 deployed its files, then failed only on the outdated check (fix 10).
  - The cache was flushed after each.
- **Live build:**
  - `track.js?ver=1.2.19`, `style.css` reads 1.2.32, and the phone hero rule is in the live `bundle-site.css`.
  - Pages answer `cf-cache-status: MISS` after the flushes.
- **Mood section (Playwright, 390 px, production):** 1 background request after scrolling to the section; 7 after a tap.
- **Help box:** present on the live experience pages. Chat opening is verified locally in en/ar/de.
- **Arabic and Chinese:** 1 stylesheet plus inline rules; the Arabic font URL returns 200 `font/woff2`.
- **SEO after deploy:**
  - robots.txt unchanged;
  - `/sitemap.xml` → `/wp-sitemap.xml` with 176 unique URLs;
  - the 4 old Arabic and Russian experience slugs (shortened during this launch) still 301 in one hop to 200, indexable pages;
  - the full 233-URL crawl result is in section M.
- **Security headers:** unchanged (nosniff, SAMEORIGIN, referrer, permissions, `frame-ancestors`).
- **Local regression before each deploy:**
  - hygiene, display-fit (8 languages), mobile-first-view (40/40), journey-nav, stale-html (7/7);
  - responsive: 1,212 checks; the 2 pages the proxy dropped were re-checked by hand, all OK;
  - axe: 0 violations on 11 pages × 390/1440, and on the drawers with each one open;
  - Trip assistant E2E in 5 languages × devices;
  - chat edge cases 7/7: reload, double start, language switch, offline + retry with no duplicate, polling stop, 2,000-character Arabic message.

## J. Remaining limitations and owner decisions

1. **Arabic first view (LCP 4.3 s on PageSpeed):** 7 Arabic font files, about 345 KB. Options, all needing your approval:
   - (a) system Arabic font on phones;
   - (b) fewer weights (for example, drop Plex 300 and 500), which slightly changes the approved typography;
   - (c) subsetting the existing files with `fontTools`, which isn't installed here; installing it is a download.
2. **Latin first view (LCP 2.9–3.3 s):** the variable Inter font needs a download (pending approval). The display-face preload trade-off is documented in `PERFORMANCE-2026-10-02.md` §9.4.
3. **Copy that over-promises while no partner offers are live:**
   - "1 place to discover, compare & book it all";
   - "Discover, compare and book the best experiences";
   - "Handpicked experiences … bookable with our partners".

   Also, "Hover a place to step inside it" is shown on touch phones. All of this is approved homepage copy in 8 languages, so I left it unchanged. I recommend rewording it until offers exist.
4. **No affiliate offers or providers configured:** the commercial journey ends at editorial plus help. Providers are yours to choose; none were invented.
5. **Infrastructure, unchanged and open:**
   - the HTML edge cache is 31 days (`max-age=2678400`, host setting);
   - `/src/` is still publicly readable (owner command `rm -rf ~/html/wp-content/themes/egypt-roamer/src`);
   - no HSTS (recorded owner decision);
   - `http://www` takes 2 hops.
6. **Not tested:**
   - real iOS Safari or Android devices (Chromium emulation only);
   - Search Console data (no access from here);
   - GTmetrix (credentials unused, per your instruction);
   - guide pages (all 7 are drafts, so not public);
   - production server error logs (SSH is yours).
7. **The local proxy drops a request under parallel load** (an `ERR_EMPTY_RESPONSE` now and then). This is a test-harness issue, not a site issue; affected checks were re-run.

## K. Recommended next phase

1. Decide the Arabic and Latin font options (J1–J2); they're the only real lever left on mobile LCP.
2. Reword the over-promising homepage lines (J3), or configure the first partner offers (J4).
3. Shorten the HTML cache TTL and remove `/src/` (J5).
4. Publish the 7 guide drafts after review: they add the planning-intent pages (itineraries, getting around) that the destination pages link toward.
5. In 2–4 weeks, read Search Console coverage and queries, then tune titles and descriptions for the pages that get impressions without clicks.

## L. Second, independent pass

The question asked: "What could still make this site feel unfinished, slow, cheap, confusing, weak or untrustworthy to a real user?"

This pass was a fresh look at full production pages on a phone, done after the fixes above.
- **Found and fixed in this pass:** the pale-hero readability problem (fix 7).
- **Remaining:** trust copy that over-promises bookings (J3) and the empty commercial layer (J4). Both are owner decisions.
- **Nothing else major:**
  - no clipping or overflow in 8 languages × 10 widths;
  - RTL is mirrored correctly;
  - focus is visible (2 px gold outline);
  - axe is clean;
  - the assistant answers realistic questions in every language tested;
  - chat survives reload, offline and language switching.

## M. Production SEO crawl after the deploys (theme 1.2.32, Core 1.2.19)

The full read-only crawl covered 274 URLs: the 233-URL inventory rebuilt from production plus test URLs, at a 2 s gap, with no 429 responses.

**The auditor:** **0 errors**.
- **Indexable:** 176, which is 22 per language: 56 destinations, 64 experiences, 40 pages, and 16 hub pages (`/destinations/` and `/experiences/` in each language).
- **Sitemap:** 176 URLs.
- **Warnings, all as at launch:**
  - the 115 approved long descriptions;
  - 8 contact pages linked only from the header/footer;
  - the `?utm_` test URL canonicalizing to the clean page.

**The independent check:** expected = indexable = sitemap = self-canonical (176 each).
- 0 hreflang targets outside the set.
- 0 old-slug leaks.
- 0 indexable search, archive, feed or `/go/` URLs.
- The only "wrong-language" links are the language switcher's own alternates.

This is the same state as the launch verification, so the improvements did not touch the indexing architecture.
