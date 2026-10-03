# Guide visual fix + Valley media / CLS — pre-rollout report — 2026-10-03

Production is unchanged: theme 1.2.35 / Core 1.2.21. **Nothing in this pass was committed, pushed or deployed.** Everything below was built and measured on the local copy (WordPress 7.1.2, same content).

Labels: **OBSERVED** means measured or seen. **INFERRED** means reasoned from observations. **RECOMMENDED** means a proposal.

## Summary

| Item | Verdict |
|---|---|
| Guide hero photo | PASS WITH NOTE: one coherent hero treatment. The guide's LCP becomes the photo, like destinations (phone ≈ 2.3–2.7 s, was text ≈ 1.3 s). |
| Guide alignment | PASS: one axis at every width ≥ 1024; mobile unchanged |
| Desktop hero file size (found while measuring) | PASS: portrait photos were sent at full height site-wide. Capped, with identical framing: −40–55% bytes. |
| Valley photos | PASS WITH NOTE: Karnak photo replaced (local); the 4-moment preview set verified. KV9 relies on the photographer's description. |
| Current CLS fix (metric fallbacks) | REJECTED: **worse than baseline** under real loading (22 vs 14 combinations > 0.01) |
| Proposed CLS fix (preload 2 fonts) | PASS WITH NOTE: 24 → 5 combinations > 0.01, worst 0.231 → 0.053, no layout or typography change. **Costs ≈ +190 ms first paint on a throttled phone.** Needs your approval. |

## 1. Guide hero change (OBSERVED)

- `single-er_guide.php` now passes the guide's stand-in photo to the shared `er_page_hero()`, the same call destinations make (`'stock' => er_stock_id_for( $er_id )`).
  - A featured image still wins when one is set.
  - The photos are the guides' existing seed photos, already shown on their cards.
- No new component, library, preload or loading strategy. The hero `<picture>` is the existing one (eager, `fetchpriority="high"`, responsive crops), exactly as on every destination and experience.
- Typography, eyebrow, byline, date, "Updated" and reading time are unchanged.

## 2. Guide alignment change (OBSERVED)

CSS scoped to `.single-er_guide`, from 1024 px only (`pages.css`, last block):
- **Card sections** ("Experiences / Destinations in this guide") sit on the guide's reading column: centred, `max-width: var(--measure)` (736 px), two cards per row by the existing grid.
- **Title** starts on the column's left edge but may run to the site's usual 18ch, instead of being squeezed into 736 px.
- **Unchanged:** intro, meta, tabs and body (they keep the centred column); phones and tablets (the column is already the full width); destinations and experiences (not matched by the selector).

Left edges, from the production-equivalent measurement:

| Width | Title / body before → after | Cards before → after |
|---|---|---|
| 320 / 390 / 430 / 768 | 16 / 16 / 17 / 31: unchanged | unchanged |
| 1024 | 144 / 144 | 41 → **144** |
| 1280 | 272 / 272 | 51 → **272** |
| 1440 | 352 / 352 | 56 → **352** |
| 1920 | 592 / 592 | 296 → **592** |

## 3. Guide before/after metrics

Local harness: phone 412 px @1.75 and desktop 1440 px, Slow 4G + 4× CPU, cold cache, median of 5.

| Guide | Device | FCP ms | LCP ms | CLS | Hero px | First paragraph y |
|---|---|---|---|---|---|---|
| Cairo | phone | 1328 → 1372 | 1328 → 2664 | 0.003 → 0.039 | 592 → 592 | 681 → 681 |
| 7 Days | phone | 1280 → 1312 | 1280 → 2488 | 0.016 → 0.056 | 592 → 592 | 681 → 681 |
| Hidden Gems | phone | 1212 → 1260 | 1212 → 2296 | 0 → 0.006 | 592 → 592 | 681 → 681 |
| Best Time | phone | 1280 → 1352 | 1280 → 2500 | 0.018 → 0.022 | 593 → 593 | 683 → 683 |
| Cairo | desktop | 1356 → 1440 | 1356 → 2920 | 0.004 → 0.010 | 815 → **703** | 951 → **839** |
| 7 Days | desktop | 1372 → 1424 | 1372 → 3096 | 0.003 → 0.011 | 815 → **703** | 951 → **839** |
| Hidden Gems | desktop | 1384 → 1388 | 1384 → 3004 | 0.033 → **0.002** | 927 → **703** | 1063 → **839** |
| Best Time | desktop | 1404 → 1424 | 1404 → 3700 | 0.032 → **0.004** | 896 → **784** | 1032 → **920** |

For reference, the same harness on destinations and an experience with photo heroes:
- phone LCP: Cairo 1640, Luxor 2144, Abu Simbel 2376;
- desktop LCP: Cairo 2368, Luxor 2544.

**INFERRED:**
- On desktop the first paragraph is now above the 900 px fold on all four guides (before: below on all four).
- LCP moves from text to photo, the same trade every destination and experience page already makes. The guide photos are slightly heavier (176–220 KB on phones).
- Phone CLS rises on two guides (0.039, 0.056; still "good" below 0.1): a photo hero bottom-aligns its text, so the font swap moves it more. With the proposed font preload (§5) it measures **0.007 and 0**. **Ship the guide hero together with the font fix.**

### Desktop hero file size (a site-wide finding while measuring)

**OBSERVED.** The hero pipeline crops photos to the frame only on phones and tablets (≤ 900 px). On wider screens it requested the photo at its full height, so a portrait photo arrived several times taller than the band shows. Examples: the Best Time guide at 1280 px on a 2× screen was **2,328 KB**; the Siwa destination (portrait since 1.2.35) was 733 KB.

**Fix (local):** the desktop `<img>` candidates get `max-h` = 0.8 × width (imgix "crop only the height"). A portrait photo loses only rows the band never shows; a landscape or panorama photo is untouched (the same file).
- A first attempt with a fixed crop ratio changed the framing of landscape photos. It was measured, rejected and replaced.
- Verified by pixel comparison of the hero at 1024@1×, 1280@2×, 1440@1× and 1920@1×: framing difference 0.00–0.46 (JPEG noise) for Luxor, Siwa, Best Time and Abu Simbel.

| Hero file | Before | After |
|---|---|---|
| Best Time, 1440 / 1280@2× / 1920 | 731 / 2328 / 1278 KB | **403 / 1262 / 696** |
| Siwa, 1440 / 1280@2× / 1920 | 337 / 733 / 488 KB | **183 / 404 / 268** |
| Luxor, Abu Simbel (landscape) | 148, 278 KB | 150, 281 (same files) |

This changes delivery for every page hero (destinations, experiences, guides), never the framing. The homepage's full-screen scenes are not affected.

## 4. Valley image provenance

**The Karnak photo.** `pvFtrzwuc6g` (CDN `1566288592443`) is tagged "karnak, louxor". It was the Valley experience's hero and card photo, and the 2026-10-02 image audit had already noted it as "Karnak-like". **Replaced locally** in Core `seed.json` with:

| Use | Photo | Photographer | Location tag | Photographer's description | Shows |
|---|---|---|---|---|---|
| Hero / card | [Y2Fy0trQ-VM](https://unsplash.com/photos/Y2Fy0trQ-VM) | Siddhesh Mangela | "Mortuary Temple of Hatshepsut, Kings Valley Road" (25.738, 32.607) | "Mortuary temple of Hatshepsut in Panoramic view." | the terraced temple beneath the cliffs, visitors walking up (approved text: "the terraced temple of Hatshepsut at Deir el-Bahari beneath the cliffs") |

The crops were checked at the wide hero, the phone hero and the 4:4.6 card. Free photo, asset type "photo", Nikon D5300.

**Preview set (local only, not deployed): 4 moments.** The 5th candidate (another wide view of Hatshepsut's temple) was dropped because the hero now shows that view.

| # | Photo | Source evidence | Shows | Approved text it repeats |
|---|---|---|---|---|
| 1 | [2iPGg1MJSXw](https://unsplash.com/photos/2iPGg1MJSXw) (Lynn Van den Broeck) | Tag "Valley of the Kings, Luxor" (25.740, 32.601) | visitor on the paved path into the valley | "go down into the painted royal tombs"; "start early: the valley is a sun trap by late morning" |
| 2 | [5aEHOQrb2Qk](https://unsplash.com/photos/5aEHOQrb2Qk) (Dmitrii Zhodzishskii) | Description "Tomb KV9 in Egypt's Valley of the Kings for Pharaohs Ramesses V and VI", tag "valley of the kings". The location tag is only "Luxor" (city point). | painted corridor descending into KV9 | "long corridors lead down to burial chambers covered in texts and images of the gods"; "photography rules inside the tombs change: follow the guards" |
| 3 | [liGCdETi0kc](https://unsplash.com/photos/liGCdETi0kc) (2H Media) | Tag "Valley of the Kings"; description "hieroglyphics decorate the walls of the Tomb of Seti I in the Valley of the Kings" | coloured figures of gods and hieroglyphs | "some with their colours remarkably well preserved"; Seti I "larger and more richly decorated", separate ticket |
| 4 | [Q8qFGF43Pro](https://unsplash.com/photos/Q8qFGF43Pro) (Mahmoud Refaat) | Tag "Mortuary Temple of Hatshepsut" (25.738, 32.606) | colossal statues along a colonnade of the temple | "three great terraces built for the female pharaoh Hatshepsut, with reliefs of her divine birth and the expedition to the land of Punt" |

**Notes:**
- Tomb photos are used only where the photographer's own description names the tomb.
- The moment 2 caption repeats the approved photography rule.
- No caption claims a photo shows something its source doesn't support. Moment 4's caption describes the temple's terraces and reliefs; its alt text describes what the photo shows (statues along a colonnade).
- Moment 2: PASS WITH NOTE (identified by the photographer's description, not GPS).

## 5. Valley CLS investigation

**OBSERVED chain:**
1. Inner pages preload no fonts. That was a deliberate 2026-10-02 decision: preloading every first-view face delayed first paint on slow connections (homepage en 1.7 → 2.3 s).
2. So the title face (Playfair Display) and the body face (Inter) are found only after the stylesheet. On a slow phone connection they arrive after first paint.
3. Text is first drawn in the fallback font. When the web font swaps in, the **hero intro** (Inter) and the **title** (Playfair) rewrap: the intro gains or loses a line, the title changes line count, and the page below moves.
   - Arabic: the title in Noto Naskh (3 lines vs 4 in the fallback).
   - Russian: digits and Latin letters come from Inter's separate Latin file.
4. Title-fit (`fit.js`) plays no part. The 1.2.35 report blamed it; that was wrong.

**Candidates measured** (Valley page; 9 widths × 8 languages; real loading on a throttled phone / desktop profile; a local-only switch injects each candidate; preview off, as on production):

| Variant | > 0.01 | > 0.05 | > 0.1 | Worst | Median FCP |
|---|---|---|---|---|---|
| Baseline (production) | 14 | 4 | 1 | 0.135 | 1332 |
| Metric-adjusted fallbacks (the current fix) | **22** | 6 | 3 | 0.183 | 1314 |
| Preload title face | 14 | 4 | 3 | 0.145 | 1354 |
| Preload title + body faces | **7** | **1** | **0** | 0.053 | 1382 |
| Metric fallbacks + preload title face | 9 | 2 | 1 | 0.130 | 1340 |

(Screening: 1 run per combination.)

**INFERRED:**
- The metric fallbacks make real shifts **worse**. They change wrapping in as many places as they fix, as the earlier geometry test (19 → 21 combinations moved) already hinted.
- Preloading only the face that actually rewraps first is what works, and it needs both faces: body-only preload was also measured below and is clearly worse.

## 6. Baseline vs current fix vs proposed fix

Confirmation run for baseline and proposal: **80 combinations** (9 widths + 412 px, the width of the production measurement × 8 languages), **2 runs each, worst run counted**. The proposal adds the Latin body face on Russian pages. The current fix is shown from the screening run.

| | Baseline | Current fix (metric) | **Proposed (preload title + body faces)** | Body face only |
|---|---|---|---|---|
| Combinations > CLS 0.01 | 24 / 80 | 22 / 72 | **5 / 80** (all Arabic) | 14 / 80 |
| > 0.05 / > 0.1 | 10 / 6 | 6 / 3 | **1 / 0** | 7 / 2 |
| Worst CLS | 0.231 (DE 412) | 0.183 | **0.053** | 0.172 |
| Mean CLS | 0.0208 | — | **0.0028** | 0.0121 |
| Largest title / body movement | 27 / 814 px | — | **12 / 26 px** | 12 / 797 px |
| EN at 412 (production showed 0.177) | 0.127 | — | **0.0002** | — |
| FCP median / slowest 10% | 1346 / 1512 ms | 1314 / 1536 | **1540 / 1700** | 1532 / 1748 |
| LCP median | 2622 ms | 2692 | 2506 | 2466 |

Per language, combinations > 0.01 / worst (baseline → proposal):

| Language | Baseline → proposal |
|---|---|
| en | 3 / 0.127 → 0 / 0.001 |
| de | 5 / 0.231 → 0 / 0.001 |
| fr | 3 / 0.173 → 0 / 0.002 |
| it | 2 / 0.023 → 0 / 0.001 |
| es | 1 / 0.130 → 0 / 0.001 |
| ru | 2 / 0.038 → 0 / 0.002 |
| zh | 0 / 0.002 → 0 / 0.002 |
| ar | 8 / 0.053 → 5 / 0.053 |

Arabic residual: the Noto Naskh title file arrives after first paint even when preloaded, at the test's connection speed. **No worse than today.**

**Acceptance criteria:**

| Criterion | Proposed fix |
|---|---|
| 1. Fewer affected combinations than baseline | **PASS**: 24 → 5 |
| 2. No new responsive regressions | **PASS**: it adds `<link rel=preload>` only, no CSS or layout change |
| 3. No unacceptable typography degradation | **PASS**: the same fonts, just earlier |
| 4. No clipping / 5. no overflow | **PASS**: nothing changes layout |
| 6. Arabic RTL correct | **PASS WITH NOTE**: unchanged, 5 small residual shifts (≤ 0.053) |
| 7. German / Russian long titles / 8. Chinese | **PASS**: DE/RU 0 affected; Chinese unchanged (its Google Fonts are not preloaded) |

**Cost:** first paint about **+190 ms** on a throttled phone (median and 90th percentile). That's the effect §9.4 of the 2026-10-02 performance work warned about, smaller here (2 faces, not 6). LCP is not worse.

**Proposed code (RECOMMENDED, not applied):** in `inc/assets.php`, extend the existing `wp_head` preload to inner pages, per script:
- Latin: `playfair-display-latin-400-normal` + `inter-latin-400-normal`;
- Cyrillic: the two Cyrillic faces + `inter-latin-400-normal`;
- Arabic: `noto-naskh-arabic-arabic-400-normal` + `ibm-plex-sans-arabic-arabic-400-normal`;
- Chinese: none.

The homepage keeps its current rule.

## 7. Responsive results (OBSERVED, local)

- **Guides**, 4 × 8 widths (320, 390, 430, 768, 1024, 1280, 1440, 1920): document width = viewport everywhere; one axis from 1024; phones unchanged except the photo. Reviewed by eye on contact sheets; hero crops frame the subjects.
- **Regression suite:** responsive 1,232 checks, 0 bad; display-fit (8 languages) OK; mobile-first view 40/40; journey nav OK; overlays OK; keyboard OK.
- **Stale-page guard:** 6 of 7 scenarios pass. Scenario F stops at the local `wp eval` step (SQLite lock in this environment), as in earlier runs.

## 8. Accessibility (OBSERVED)

- axe **0 violations**:
  - the 22 standard pages (390 / 1440);
  - the 4 guides, the guide archive, Luxor and Valley (390 / 1440).
- Heading outline unchanged (H1, then H2s).
- The guide hero photo is decorative (`alt=""`, as on every page hero). The title's contrast is carried by the existing hero gradient.

## 9. Performance

- **Guides:** see §3. The trade is text LCP → photo LCP, the same as every destination and experience.
- **Hero file cap:** −40–55% bytes for portrait heroes at desktop widths; landscape unchanged.
- **Proposed font preload:** +≈190 ms FCP (throttled phone), CLS much lower, LCP not worse.
- No new JavaScript, framework, video, font or global CSS. The only CSS added is the scoped guide block (≈ 0.5 KB before compression).

## 10. Exact files changed (all local, uncommitted)

| File | Change | Part of |
|---|---|---|
| `themes/egypt-roamer/single-er_guide.php` | `stock` photo passed to the hero (+3 lines) | Guide fix |
| `themes/egypt-roamer/assets/css/pages.css` (+ the two rebuilt bundles) | scoped guide alignment block (+20 lines) | Guide fix |
| `themes/egypt-roamer/inc/template-tags.php` | optional `max_ratio` (imgix `max-h`) in the stock-image helpers; desktop hero `<img>` capped at 0.8 | Hero file size |
| `plugins/egypt-roamer-core/data/seed.json` | Valley experience photo → `1623417765161-c71d6c83cfee` | Valley media |
| `plugins/egypt-roamer-core/data/previews.json` | `exp-valley` entry (4 moments, 8 languages) | Valley preview (V1.1) |
| `plugins/egypt-roamer-core/includes/previews.php`, `meta.php`, `cli.php` | preview editor fields + `wp egypt-roamer previews` migrate / verify / enable / disable (V1.1) | Editor fields (V1.1) |
| `docs/IMMERSIVE-PREVIEW-NATIVE-QA-2026-10-03.md` (new) | native-language review package | V1.1 |

Removed from the local tree: the metric-fallback CSS (kept in a scratch file only). Local-only test switches (`mu-plugins/er-cls-lab.php`, `er-test-video.php`) are outside the repository.

## 11. What remains local only

- The Valley preview (4 moments) and the V1.1 editor-field system (verified locally: Abu Simbel fields = file in 8 languages, byte-identical HTML).
- The Valley hero/card photo replacement.
- The guide fix and the hero file cap.
- The font-preload proposal (as measurements and code guidance; not written into the theme).

## 12. Recommendation for next step

1. **Approve, or not, the font preload** (+≈190 ms first paint on slow phones for CLS 0.231 → 0.053 worst, 24 → 5 affected).
   - If approved, release it **together with** the guide hero, the guide alignment, the hero file cap and the Valley photo replacement. That set improves guide desktop layout and cuts desktop hero bytes.
   - If not approved, the guide hero alone raises two guides' phone CLS to 0.04–0.06 (still "good").
2. **Valley preview:** after a native check of its text (it reuses approved sentences; titles, intro and alt texts are new), roll out through the V1.1 editor fields (`wp egypt-roamer previews --verify` before `--enable`).
3. **Not touched in this pass:** Giza and Great Sand Sea previews (Giza sourcing ready; Great Sand Sea blocked on provenance); the duplicate-navigation incident (not reproduced; waiting for your screenshot).
