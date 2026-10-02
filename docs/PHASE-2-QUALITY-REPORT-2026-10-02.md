# Phase 2 — product quality, SEO, performance and graphics — 2026-10-02/03

Released as **theme 1.2.33** (Core unchanged at 1.2.19), commit `c9148a2`, deployed by the workflow (run
37076409268, success), cache flushed, verified live. Companion document: `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md`.

Indexing architecture untouched: same 176 indexable URLs, sitemap, canonical/hreflang, robots, slugs (re-verified
on production after the deploy, §10). Nothing in Search Console was touched.

## 1. Baseline (production, before any change)

Mobile lab harness, identical before and after: Chrome, 412×823 @1.75 (PageSpeed's phone), Slow 4G
(150 ms RTT, 1.6 Mbps), 4× CPU, cold cache, **median of 5 runs** per page (`perf2.mjs`).

| Page | FCP | LCP | TBT | CLS | Total | Fonts | Images | JS | CSS |
|---|---|---|---|---|---|---|---|---|---|
| `/` | 1896 | 1896 | 172 | 0.003 | 679 KB | 139 | 411 | 82 | 21 |
| `/ar/` | 2276 | 2276 | 310 | 0.003 | 919 KB | 368 | 411 | 88 | 21 |
| `/de/` | 1988 | 1988 | 185 | 0.003 | 535 KB | 139 | 261 | 88 | 21 |
| `/zh/` | 2120 | 2120 | 419 | 0 | 689 KB | 139 | 411 | 88 | 21 |
| `/ru/` | 2228 | 2228 | 267 | 0.003 | 746 KB | 196 | 411 | 88 | 21 |
| Cairo | 1540 | 2228 | 0 | 0.001 | 262 KB | 139 | 62 | 17 | 19 |
| Cairo (ar) | 1420 | 1420 | 0 | 0 | 411 KB | 278 | 69 | 23 | 19 |
| Abu Simbel | 1460 | 2436 | 0 | 0.003 | 319 KB | 139 | 124 | 17 | 19 |

PageSpeed (mobile, single runs): `/ar/` FCP 3.0 s, LCP 4.2 s, SI 4.0 s; `/` LCP 2.9–3.3 s across earlier runs,
537 KiB.

## 2. Findings, priority, root cause, fix

| # | Pri | Finding | Root cause | Fix (1.2.33) |
|---|---|---|---|---|
| 1 | P1 trust | Homepage claimed "book it all", "Discover, compare and book" (also the homepage meta description in 8 languages) and "bookable with our partners" while no partner offer is live | Approved prototype copy written for a live-inventory product | "Discover and plan the best experiences in Egypt — all in one place.", "1 place to discover & plan it all", "Handpicked by our editors, with the practical details to plan them." All 8 languages; partner-only wording (finder, "Book with our partners") already shows only with a live offer |
| 2 | P1 UX | "Hover a place to step inside it" on touch screens | Desktop-only verb | "Choose a place to step inside it." (8 languages) |
| 3 | P1 image | Photos soft on phones, tablets and 2× laptops: phone hero ~3× upscaled; cards 0.6× on Retina; moods/planner 0.1–0.3× | Landscape photos in tall frames (`object-fit: cover`): the browser picks by width and only part of the width is visible | Unsplash crops to the frame's shape (`w`+`h`+`fit=crop`, centred like the CSS); `<picture>` sources by screen shape; card/destination-card/page-hero crops. Framing identical by construction (crop ≥ frame aspect) and on screenshots |
| 4 | P1 perf | Homepage hero downloaded twice at 390–1280 px (62–104 KB wasted; the high-priority preload unused) | Preload `imagesrcset` (900/1400/2000/2800) ≠ image `srcset` (900/1200/1600/2000/2800) after 1.2.30 | One preload per screen shape listing exactly the image's candidates (`media` attributes) |
| 5 | P1 perf | Arabic pages: 368 KB of fonts, LCP ~380 ms behind English | Arabic files carry Persian/Urdu/African/Quranic extensions and ~600 presentation-form code points the site never uses | fontTools subset to U+0600–06FF, all OpenType features and hinting kept: **pixel-identical** (all 7 faces, all 256 code points + joined forms + 66,000 characters of site text), 31–52 % smaller. Plex renamed internally ("ER Sans Arabic") per the OFL Reserved Font Name |
| 6 | P2 visual | Phone destination cards: the gold region label vanished on pale skies (Cairo) | Shade covered only the lower 65 % | Steadier shade to the top + soft text shadow (pages.css), as for phone page heroes in 1.2.32 |
| 7 | — | 46 KB host-injected CSS (`wp-site-designer-contrast-fallback`) on every page, 28 of 29 rules for block-theme markup this theme lacks | GoDaddy Site Designer mu-plugin | **Rejected removal**: A/B 5 runs × 3 pages showed no measurable effect (−68…+40 ms, noise); gzip makes it a few KB. Not worth touching host markup |
| 8 | — | Inter re-subset | — | **Rejected**: only ~10 % (3 KB/file); Fontsource already trimmed it |
| 9 | — | Variable Inter | Needs a download not approved | Not done (owner decision) |

Not changed on purpose: approved descriptions >160 characters (115, not shortened), the newsletter promise (see §11),
landmark photos (no swap without a verified better source).

## 5. Fix details worth knowing

- `er_stock_url()`, `er_stock_srcset()`, `er_stock_crops()`, `er_stock_picture()` in `inc/template-tags.php`;
  `img(id, w, q, ratio)` / `srcset(id, widths, ratio, q)` in `src/js/data.js`. Media Library images (`er_img()`) are
  unaffected.
- Crops: full-screen frames 2:3 (viewport ≤ 2/3, widths 640/720/828) and 1:1 (≤ 1/1, 768/1024/1366); page heroes
  1:1.2 (≤ 480 px) and 1:0.8 (≤ 900 px); cards 1:1.15 (400–1000); phone destination cards 1:1.3. Width caps keep
  a 3× phone at ~2× density instead of a heavier page.
- Image retry (`micro.js`) now also refreshes `<picture>` sources.

## 6. Before / after (production)

Same harness, median of 5:

| Page | LCP before → after | FCP before → after | Fonts | Images | TBT |
|---|---|---|---|---|---|
| `/` | 1896 → **1688** | 1896 → 1688 | 139 → 139 | 411 → 605 | 172 → 206 |
| `/ar/` | 2276 → **1816** (−460) | 2276 → 1816 | **368 → 286** | 411 → 605 | 310 → 350 |
| `/de/` | 1988 → **1752** | 1988 → 1752 | 139 | 261 → 397 | 185 → 179 |
| `/zh/` | 2120 → **2000** | 2120 → 2000 | 139 | 411 → 605 | 419 → 579 |
| `/ru/` | 2228 → **1912** | 2228 → 1912 | 196 | 411 → 605 | 267 → 383 |
| Cairo | 2228 → **1460** | 1540 → 1420 | 139 | 62 → 69 | 0 → 74 |
| Cairo (ar) | 1420 → **1196** | 1420 → 1196 | **278 → 210** | 69 | 0 |
| Abu Simbel | 2436 → **2356** | 1460 → 1252 | 139 | 124 → 171 | 0 |

LCP improved on every page; the Arabic homepage gap to English fell from 380 ms to 128 ms. PageSpeed after
(mobile): `/ar/` **LCP 4.2 → 3.8 s, Speed Index 4.0 → 2.6 s**, TBT 0, score 85; `/` LCP 3.0 s, FCP 1.9 s, TBT
10 ms, score 92.

Honest costs: homepage photo bytes on this phone profile rose (411 → 605 KB; PageSpeed payload `/` 537 → 897 KiB)
because the photos now carry the pixels they display (they were upscaled 2.7×; now ~1.3×). On a 390 px iPhone the
homepage photos fell instead (470 → 379 KB, the double hero download gone). TBT readings rose on zh/ru in the lab
(4× CPU; PageSpeed shows 0–10 ms); watch field data before drawing conclusions.

## 7. Images reviewed

60 placements, 52 distinct photos: source resolution of every master (all ≥ 2,045 px), delivered vs needed
pixels at 4 device profiles on 6 pages, 1:1 device-pixel before/after crops, labelled contact sheets for art
direction. Classification in `IMAGE-QUALITY-AUDIT-2026-10-02.md`: 9 OPTIMIZE (fixed), most KEEP, 3 NEEDS BETTER
SOURCE (Cairo hero murky; White Desert safari photo location unverifiable — smooth white dunes vs the White Desert's
chalk formations; hidden car-rental sample shows Mojave Joshua trees), 1 minor subject mismatch (dinner cruise shown
as a felucca). No landmark was swapped.

## 8. Screenshots reviewed (by eye)

- Homepage hero, 8 languages × 10 widths (320, 360, 390, 414, 768, 834, 1024, 1280, 1440, 1920) — 80.
- Templates (destinations/experiences archives, destination, 404), 8 × 10 × 3–4 — 240.
- Navigation: mobile menu, language switcher, footer, 8 languages — 168.
- Trip assistant: launcher, open, answer, human chat, failed state, en/ar/de/zh × 10 widths — ~190.
- Single destination, experience (hero, sticky section tabs, no-offer help box) and contact, 8 × 10 — 400.
- Adversarial: photo host down, reduced motion, logged-in toolbar, keyboard focus in drawer, slow network, long
  German/Arabic chat text — 22.
- Before/after image crops (hero, Cairo, cards, phone destination cards, tablet framing) — 14.

Findings: the phone destination-card label (fixed, #6). Archive cards appearing blank in some sheets were
lazy-loading timing (verified: 7/7 and 8/8 images load in en/ar/zh at 390 and 1440). Arabic RTL composition,
German/Russian wrapping, Chinese titles at full size, 320 px layouts and floating controls: no defects found.

## 9. Files changed

Theme: `inc/template-tags.php`, `inc/assets.php`, `inc/home-settings.php`, `front-page.php`, `assets/css/pages.css`,
`assets/css/fonts-arabic.css`, 7 Arabic `.woff2`, `src/js/{data.js, components/moods.js, destinations.js,
experiences.js, micro.js}`, rebuilt bundles, `languages/*.l10n.php` (4 strings × 7), `functions.php`, `style.css`,
`CHANGELOG.md`. Tools: `tools/i18n/new-strings.json`. Docs: this report, `IMAGE-QUALITY-AUDIT-2026-10-02.md`.

Local regression before deploy: hygiene, display-fit (8 languages), mobile first view (40/40), journey nav,
responsive (1,232 checks, 0 bad), overlays, keyboard, languages (8/8 incl. forms and RTL), axe (22 page/width
combinations, 0 violations), `tools/build.py check` (bundles reproduce), PHP lint.

## 10. Production verification (after deploy + flush)

- `style.css` Version 1.2.33; `track.js?ver=1.2.19`.
- Homepage in all 8 languages: new copy and meta description, no "book it all / bookable / Hover / compare and
  book" anywhere, 5 `<picture>` crops, exactly 3 hero preloads (one per screen shape).
- Each screen downloads the hero once (360×2, 390×3, 412×2.6, 768×2, 1280, 1440×2, 1920).
- Subset Arabic font served (Noto Naskh 400: 25,212 bytes).
- SEO crawl: see §10a.
- Trip assistant: 29/29 realistic, previously fixed and adversarial questions correct in 8 languages (relevant pages; nothing invented for out-of-scope or prompt-injection questions), ~300 ms; rate limit (20 / 10 min) verified.

### 10a. SEO regression crawl

`tools/qa/seo-audit.py` against production (274 fetches, 233-URL inventory, 2 s gap) plus the independent check:
**0 errors; 176 indexable = 176 sitemap URLs (22 per language); every indexable page self-canonical; hreflang
targets all inside the indexable set; every sitemap entry 200; no old-slug leaks; no utility/search/feed/`/go/`
URL indexable.** The 124 warnings are byte-identical to the post-launch crawl (approved descriptions over 160
characters; contact pages linked only from header/footer). The only "unexpected indexable" URL is the deliberate
`?utm_source=x` probe (canonicalised to the clean URL). Search Console readiness unchanged.

## 11. Remaining blockers and owner decisions

1. **Partner inventory**: no affiliate providers/offers are live, so the affiliate journey ends at the honest
   next step (team chat, Plan My Trip). Configure providers in Egypt Roamer → Affiliate Offers when ready.
2. **Guides (7 drafts, none published)** — publication blockers:
   - Only English bodies exist; 0 of 7 in the other 7 languages.
   - 3 have no body at all: "Is Egypt Safe" (needs carefully sourced safety facts), "Best Nile Cruises, Compared
     Cabin by Cabin" and "What a Trip Really Costs in 2026" (need real cruise/price data — cannot be written
     without inventing).
   - The 4 written (7 days 637 words, best time 684, Cairo 619, hidden gems 503) are publishable in English;
     all internal links resolve (200). Missing links worth adding: 7-days → Abu Simbel, Valley of the Kings, Giza
     tour, Nile dinner cruise experiences and the best-time guide; hidden gems → Abu Simbel/Luxor experiences.
   - Publishing them creates the destination → guide cluster ("Plan your trip to {name}" appears on destination
     pages only when guides are published) — the main SEO growth lever.
3. **Images**: Cairo hero, White Desert safari photo, car-rental sample (see image audit).
4. **Newsletter promise** ("One beautiful email a month… the deals worth knowing about"): subscriptions are stored,
   but nothing in the stack sends a letter. Keep only if a monthly letter will actually be sent.
5. **Hosting / security** (verified 2026-10-02):
   - `/wp-content/themes/egypt-roamer/src/` is still public (left from an early manual upload; the deploy excludes
     `src/` and therefore never deletes it, and 31 files would trip the workflow's 30-deletion cap). Safest fix,
     one SSH command: `rm -rf ~/html/wp-content/themes/egypt-roamer/src`.
   - **HSTS missing.** Correct place: Cloudflare → SSL/TLS → Edge Certificates → HSTS: max-age 6 months, no
     preload, include subdomains only if every subdomain serves HTTPS. Recorded owner decision.
   - **HTML cached 31 days** (`Cache-Control: public, max-age=2678400` from the host, honoured by Cloudflare). A
     returning browser may show a page up to 31 days old; the stale-page check (build ID + reload) already corrects
     that on the next view, and deploys are followed by a flush. Safe reduction without breaking it: a Cloudflare
     Cache Rule for HTML (not `/wp-content/`) with Browser TTL ≈ 10 minutes, Edge TTL kept long (purged on deploy).
     Effect: browsers revalidate within minutes; edge hit rate unchanged.
   - Security headers present: CSP `frame-ancestors 'self'`, `X-Content-Type-Options`, `X-Frame-Options`,
     `Referrer-Policy`, `Permissions-Policy`. `xmlrpc.php` 403, user enumeration 404, no `debug.log`/`readme.html`.
   - Production PHP error logs need SSH (not checked).
6. **Variable Inter** (one file instead of 4 weights) needs a download approval.
7. **Translations** of the 4 new strings were written by Claude (as all of `tools/i18n/new-strings.json`):
   native review recommended.

## 12. Recommended next phase

1. Publish the 4 written guides in English after an editorial read; commission the 3 missing bodies and the
   translations; add the internal links listed above.
2. Configure the first affiliate providers (hotels, tours, cruises) so the decision pages have a real CTA; the
   trip assistant and the help box already route the rest.
3. Replace the Cairo hero (Media Library featured image) and verify the White Desert photo.
4. Owner hosting actions: remove `/src/`, enable HSTS, HTML browser TTL rule.
5. When Search Console has field data (Core Web Vitals report, ~28 days), compare with this lab baseline; revisit
   the homepage photo bytes if real-user LCP on phones regresses.
6. Thin experience archives (85–203 words): a short approved editorial intro per language.
