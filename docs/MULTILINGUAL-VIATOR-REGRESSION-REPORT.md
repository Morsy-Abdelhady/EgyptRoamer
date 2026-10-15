# Multilingual + Viator regression report (2026-10-15)

**Release:** theme **1.2.46** / Core **1.2.32**, commit `9886fd2`.
- **Deploy:** GitHub Actions on push. The live `track.js?ver=1.2.32` was confirmed on production.
- **Cache:** flushed (GoDaddy, `#wp-admin-bar-wpaas-flush-cache`).
- **Offers:** Viator offer changes were made in wp-admin (data only).

**Labels:**
- **[Tested]** tested on production unless marked "local"
- **[Not tested]**
- **[External]** external blocker
- **[User action]**

## 1. All eight languages: production smoke test after deploy

| Check | en | ar | de | fr | it | es | ru | zh |
|---|---|---|---|---|---|---|---|---|
| Homepage 200, translated h1, same sections (en adds Guide) | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| Desktop menu (localized URLs), after theme 1.2.47 | 3 items | 3 (`EN` guide) | 3 | 3 | 3 | 3 | 3 | 3 |
| Mobile drawer opens, same items, CTA, 8 languages | ✔ | ✔ RTL | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| Language menu: current language marked, links = hreflang | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| Arabic item aligned with the list (fix) | ✔ (`left`) | ✔ (start, RTL) | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| Destination, experience, legal, archive pages: 200, canonical = self, 9 hreflang, valid JSON-LD, no noindex | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| Offer CTA translated + disclosure next to it | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| Offer title `lang="en"` on translated pages (fix) | n/a | ✔ | (same template) | | | | | |
| hreflang without query string on archives (fix) | ✔ (0 of 9 with `?`, on `/ar/destinations/?utm_source=…`) | | | | | | | |

**Correction:** the menu and Guide-section difference was a bug, fixed in theme 1.2.47; all 8 languages are now identical. See `MULTILINGUAL-NAVIGATION-CONSISTENCY-REPORT.md`.

## 2. Responsive (homepage, production, after deploy)

**Widths:** 320, 360, 390, 412, 768, 1024, 1280, 1440 × 8 languages = **64 renders**.

**Problems found: none.**
- Horizontal overflow: 0 in every render.
- No clipped headings or buttons.
- No JavaScript errors.
- All 200.

**Rails:** the horizontal rails (moods, destinations, experiences) extend past the viewport by design.

**Text under 11 px:** 2–9 small labels per page. These are the approved design's eyebrow and counter labels, unchanged in this release. Not a regression.

Before the release, the same 64 renders were taken plus the open drawers at 390, all clean.

## 3. Navigation and language switching (production)

- **Language switch by click:** 16/16 correct. Each switch went from `/destinations/aswan/` and from `/experiences/valley-of-the-kings-hatshepsut-temple/` to all 8 languages. Each landed on the translated page (correct URL, `lang` and h1), e.g. `/ru/destinations/асуан/`, `/ar/experiences/وادي-الملوك-ومعبد-حتشبسوت/`.
- **Drawer:** opens by tap in all 8 languages and shows the menu, CTA and 8 language links.
- **Keyboard (local, `keyboard.mjs`):** 8 languages × 1440 and 390, all ok. Covers the skip link, focus rings, the language menu, overlays and the menu focus trap with return of focus.

## 4. Accessibility and hygiene (local, same code)

| Suite | Result |
|---|---|
| `overlays.mjs` (axe with saved / search / menu / assistant open; en 1440, ar 390, zh 1440) | 0 violations |
| `hygiene.mjs` (12 pages, en + ar) | all ok |
| `footer-parity.mjs` | only the intentional guide-link difference (§1) |
| Reduced motion, no-JS | Not re-run in this release. No motion or JS code changed (CSS rule, PHP attribute, Core filter only) |

## 5. Viator redirects and tracking (production)

- **Offers:** all 8 offers redirect `/go/{slug}/` → `admin-post.php?action=er_go` → 302 to the verified product, with `pid`, `mcid=42383`, `medium=link` and `campaign=er-{lang}-{placement}`.
- **After deploy:** re-checked on the Arabic Valley of the Kings page, landing on `campaign=er-ar-experience-offers`.
- **Failure cases:** an unknown slug goes safely to the home page and is counted as `go_error`; there is no open redirect (provider host allow-list).
- **Full mapping:** `VIATOR-CONTENT-MATCHING-REPORT.md`.
- **[External]** The Viator landing page was not loaded: viator.com is unreachable from this network.
- **[User action]** Tap one CTA on your phone.

## 6. Test data: exclude from business numbers

| Store | 15 Oct values | Origin |
|---|---|---|
| Click reports (`/go/` log) | **26 clicks**, all Viator | All QA: offer and redirect verification |
| Visits | **27 page views / 27 entrances** | QA: one verification script did not set the "don't count" flag (fixed for future runs). Treat **all of 15 Oct** as test traffic; real measurement effectively starts **16 Oct** |
| Visits CTA taps | 0 | — |

## 7. Not tested and blockers

- **[External]** viator.com unreachable from this network (product pages, booking flow).
- **[Not tested]** Physical iOS/Android devices: widths were emulated in desktop Chrome.
- **[External]** Bing URL Submission: quota 1 left today. The remaining 49 URLs go on 16 Oct.

## 8. Offer cards translated (theme 1.2.49 / Core 1.2.35, 15 Oct)

- **Bug (owner report):** on non-English pages the offer card showed the English title, an English location and an empty photo box.
- **Fix:**
  - Titles and locations now come from Core `data/offers-i18n.json`, keyed by offer slug and language. Place names match the approved destination translations.
  - A card without its own image now uses the photo of the related experience or destination.
- **Production check (deploy #68, cache flushed):**
  - 121 pages: the homepage, plus every experience and destination page in all 8 languages.
  - 120 offer cards (15 per language) covering all 8 offers.
  - 0 failures: every title is translated with no English fallback marker, every location is localised, and every image loads.
