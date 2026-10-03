# Guide + Valley rollout report (2026-10-03)

**Verdict: ACCEPTED AND DEPLOYED.** No rollback was needed. Evidence: §§5–13.

| | |
|---|---|
| Release | Theme **1.2.36** / Core **1.2.22**, commit **`615a8f6`** |
| Live build | `14fc896bf353` (until it was superseded by 1.2.37 / `da65a2d6636a`, see `EXPERIENCE-TEMPLATE-GEO-AUDIT-2026-10-03.md`) |
| Pre-rollout evidence | `GUIDE-AND-VALLEY-PRE-ROLLOUT-REPORT-2026-10-03.md` |

**Metric-adjusted fallback fonts were rejected and were not deployed.**

## 1. Implementation summary

| Approved item | Shipped |
|---|---|
| Guide visual/alignment fix | From 1024 px, guide related cards share the centred reading column, and the title starts on the column edge (max 18ch) |
| Guide hero images | Guides pass their seed stand-in photo to the shared hero (`single-er_guide.php`) |
| Desktop portrait-image height cap | The page-hero `<img>` (> 900 px) uses imgix `max-h` = 0.8 × width. Landscape files are byte-identical; portrait files are capped |
| Font preload | Inner pages preload the title and body faces per script (Latin; Cyrillic + Latin; Arabic; Chinese none). The homepage keeps its single preload |
| Valley verified photo | Experience photo `1566288592443` (Karnak) → `1623417765161` (Hatshepsut temple) |
| Rejected, not shipped | Metric-adjusted fallback fonts (made real CLS worse: 22 vs 14 combinations > 0.01) |

## 2. Files changed (`615a8f6`)

- **Theme:**
  - `inc/assets.php` (font preload);
  - `inc/template-tags.php` (`er_stock_url/srcset/img` `$max_ratio`, hero cap);
  - `single-er_guide.php` (stock hero);
  - `assets/css/pages.css` + rebuilt `bundle-home.css` and `bundle-site.css` (guide axis);
  - `style.css`, `functions.php` (1.2.36);
  - `CHANGELOG.md`.
- **Core:**
  - `data/seed.json` (Valley photo);
  - `egypt-roamer-core.php` (1.2.22).

## 3. Deployed versions

- **Versions:** theme 1.2.36, Core 1.2.22 (`track.js?ver=1.2.22`).
- **CI:** PHP 8.1 and 8.3 lint, version agreement, JSON, editorial, legal and bundle checks, then deploy: all passed.

## 4. Before/after measurements

Production, throttled phone 412 (Slow 4G, 4× CPU), 3 runs, medians:

| Page | TTFB | FCP | LCP | CLS |
|---|---|---|---|---|
| Cairo guide | 532 → 845 | 1412 → 1888 | 1412 → 3004 | 0.037 → **0** |
| 7 days | 629 → 913 | 1508 → 1948 | 1508 → 3176 | 0.062 → **0.006** |
| Hidden gems | 458 → 491 | 1248 → 1600 | 1248 → 2356 | 0 → 0 |
| Best time | 445 → 444 | 1280 → 1612 | 1280 → 2436 | 0.003 → 0.001 |
| Valley EN | 460 → 477 | 1368 → 1648 | 2272 → 2448 | 0.177 → **0** |
| Valley AR | 445 → 434 | 1452 → 1856 | 2820 → 3040 | 0.02 → **0** |
| Valley DE | 448 → 460 | 1304 → 1564 | 2348 → 2476 | 0.231 → **0.001** |

**Why guide LCP rose:** guides now have a photo hero. Before, their LCP was the title text.

Desktop 1440:

| Page | Hero height / first paragraph |
|---|---|
| Cairo and 7 days | 815 → 703 / 951 → 839 |
| Hidden gems | 927 → 703 / 1063 → 839, CLS 0.03 → 0 |
| Best time | 927 → 815 / 1063 → 951, CLS 0.032 → 0.001 |

Valley desktop hero file: 401 → 173 KB.

## 5. CLS results

| Matrix | Combinations | > 0.01 | > 0.05 | > 0.1 | Worst |
|---|---|---|---|---|---|
| Lab, before (Valley, 8 langs × 10 widths) | 80 | 24 | n/a | n/a | 0.231 |
| Lab, release candidate | 80 | 5 (all Arabic) | 1 | 0 | 0.053 |
| **Production** (Valley, 8 langs × 7 widths × 2 runs) | 112 | 7 | 3 | **0** | 0.076 |

**Production detail**

| Language | Runs > 0.01 |
|---|---|
| English, Italian, Spanish, Russian, Chinese | 0 (max ≤ 0.0025) |
| Arabic | 5: desktop 768–1440 (0.046–0.053), as in the lab |
| French | 1: 320 px at 0.076, in a run with FCP 3448 ms (slow server); the paired run was 0.0000 |
| German | 1: 430 px at 0.055, single run. An earlier German outlier (0.051) re-tested at 0.0004 / 0.0001 / 0.0004 |

## 6. First-paint trade-off

FCP increase after subtracting the TTFB change (production, phone):

| Page | ms |
|---|---|
| Cairo guide | +163 |
| 7 days | +156 |
| Hidden gems | +319 |
| Best time | +332 |
| Valley EN | +263 |
| Valley DE | +248 |
| Valley AR | +415 |

**Context**

- Lab expectation: +190 ms.
- The guide figures also include the new hero image competing for bandwidth, and Valley's figures the larger phone photo.

**Judgement:** the same order of magnitude as tested (1.3–2.2×, confounded), against layout-shift gains of up to 0.23. The stop condition "font preload causes materially worse production behavior than tested" is **not met**, so no rollback.

A 5-run re-measure was attempted, but production TTFB spiked to 1.9–5.1 s during it, so its numbers are not usable. Re-measure on a quiet server before any further font work.

## 7. Guide visual results

**Production screenshots** (4 guides, 390 / 412 / 1440):
- photo hero present;
- title on the column edge at 1440;
- related cards two per row on the reading axis;
- no overflow (sweep).

**Phones:** guide layout unchanged apart from the hero photo.

## 8. Image-cap results

| | Before | After |
|---|---|---|
| Landscape heroes | identical | identical (same files) |
| Portrait hero, desktop | full height (Best Time 2.3 MB at 1280 @2×; Siwa 733 KB) | capped at 0.8 × width: 40–55% smaller |
| Framing | unchanged (pixel diff ≤ 0.46 vs. the uncapped crop) | |

## 9. Valley photo provenance

[Y2Fy0trQ-VM](https://unsplash.com/photos/Y2Fy0trQ-VM) by Siddhesh Mangela (free Unsplash licence).
- Location tag: "Mortuary Temple of Hatshepsut, Kings Valley Road" (25.738, 32.607).
- Description: "Mortuary temple of Hatshepsut in Panoramic view."
- It replaces `pvFtrzwuc6g`, which was tagged "karnak, louxor" (East Bank).
- Production check: the Valley page contains 0 references to the Karnak id.

## 10. Valley Preview status and native-speaker gate

| | |
|---|---|
| Valley Preview | **UNPUBLISHED.** Production Valley pages have no `data-moments` and no Immersion |
| Native-speaker gate | **NOT SATISFIED.** Only a self-review exists (`IMMERSIVE-PREVIEW-NATIVE-QA-2026-10-03.md`, in `stash@{0}`: 175 strings, 157 PASS, 18 NEED NATIVE REVIEW). The KV9 photo relies on the photographer's description, tagged only "Luxor" (city point). Title, intro and alt texts are new and await review |

## 11. Regression and accessibility

| Scope | Result |
|---|---|
| Local, release candidate | responsive 1,232 checks / 0 bad; display-fit 8 languages; mobile-first 40/40; journey-nav, overlays and keyboard OK; axe 0 on 22 standard pages + 4 guides, guide archive, Luxor and Valley |
| Destinations / experiences vs. 1.2.35 | 0 geometry, typography or component differences (16 checks) |
| Production axe (guides × 4, guide archive, Luxor, Valley; 390 + 1440) | **0 violations** |
| Production sweep (homepage, 4 guides, Valley EN/DE, Luxor, Abu Simbel, archives × 390/1440) | **22 / 22 OK**: no console errors, failed requests, broken images or unused preloads |
| Arabic / RTL | no breakage. Residual Arabic desktop CLS ≈ 0.05 (font swap), as tested |

## 12. Production QA

All items in §§4–11 were measured on `https://egyptroamer.com` after the flush. The Valley pages were checked in English, Arabic and German (the long-language locale).

## 13. Cache verification

- **Flush:** cache flushed via the admin bar in the owner's Chrome.
- **Live build:** `/wp-json/egypt-roamer/v1/build` = `14fc896bf353`, the same as the `er-build` meta on every sampled edge copy (homepage, guides, Valley EN/AR/DE, Luxor).

## 14. Remaining known issues

- Arabic desktop CLS ≈ 0.05 (font swap). A future fix needs a different approach than metric overrides.
- First-paint cost of the preload (§6). Re-measure on a quiet server.
- Stale-HTML scenario F failed locally only (SQLite / proxy, environmental). Scenarios A–E and G pass.
- Valley Preview waits for native review. The KV9 location tag is only "Luxor".
- Duplicate navigation: not reproduced (`DUPLICATE-NAV-AUDIT-2026-10-03.md`). The landmark label "Primary" is used twice; cosmetic.
- Great Sand Sea hero provenance is limited (tag "Siwa dunes"); not changed, per scope.
