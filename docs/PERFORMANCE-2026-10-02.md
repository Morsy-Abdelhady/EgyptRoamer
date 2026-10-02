# Mobile performance: root cause of the 4.1 s LCP, the fix, and what is left (2026-10-02)

Theme 1.2.19. Follows `docs/PERFORMANCE-2026-09-30.md`.

## 1. Starting point (production, PageSpeed Insights, Lighthouse 13.5, Moto G Power, slow 4G)
| Page | Perf | FCP | LCP | TBT | CLS | SI |
|---|---|---|---|---|---|---|
| `/` (theme 1.2.17) | 82 | 2.0 s | **4.1 s** | 0 ms | 0 | 4.6 s |

PageSpeed's own estimate for "render-blocking resources" was 1,730 ms (7 stylesheets).

## 2. Measurement method
- **Lab:** Lighthouse 10.4 locally (mobile preset, simulated slow 4G and 4× CPU, the same model PageSpeed uses), 3 runs, median.
- **Compression:** the local front (`C:\Users\morsy\er\proxy.py`) now gzips text like the production edge. Without that, transfer sizes were 3–4× too large.
- **Absolute numbers** are higher locally than on production: TTFB is about 0.5 s, and the front speaks HTTP/1.0 (6 connections instead of HTTP/2). **Compare the deltas**, then confirm on production (section 6).
- **LCP candidates:** a `PerformanceObserver` trace in Chrome (`lcp.mjs`).

## 3. Findings
### 3.1 What the LCP element is
On phones the LCP element is **the hero text** (`p.hero__copy`), not the photo:
- Chrome never reports the full-screen hero photo as a candidate.
- The hero word "EGYPT" paints under the loader at first paint, but it's smaller.
- The hero text is `opacity: 0` until the intro loader hands over, so it becomes the LCP when it fades in.

| Phase | Timing (local baseline) |
|---|---|
| TTFB | 0.64 s |
| Load delay / load time | 0 (text) |
| **Render delay** | **4.48 s (87%)** |

The render delay is everything that has to happen before the intro hands over:
- the stylesheets;
- the fonts;
- the deferred JS bundle (the hand-off lives in it);
- the intro's minimum hold of 1.3 s.

Lighthouse simulates LCP from every request that finished, and every CPU task that ran, before the observed LCP.

### 3.2 What is downloaded before the LCP (homepage, English, gzip)
| Type | Bytes | Notes |
|---|---|---|
| Fonts | 141 KB | 6 Latin faces: Inter 300/400/500/600, Playfair 400 and 400 italic. All six are used in the first view. |
| Images | 94 KB | hero 33 KB (900 w), loader logo 20 KB, 4 header logo variants (only one is visible) |
| Scripts | 86 KB | GSAP 28, ScrollTrigger 18, Lenis 4, homepage bundle 35 |
| **Stylesheets** | **36 KB in 7 requests** | fonts, tokens, base, layout, journey, sections, pages: all render-blocking, all needed for the first paint |
| HTML | 22 KB | |

On Arabic pages, fonts are **373 KB**: five Arabic faces of 43–53 KB each, plus the Latin faces needed for Latin text.

### 3.3 The seven stylesheets
- **None of them can be deferred without a flash of unstyled content.** Every template needs all of them for its first view: tokens, base and layout for the header and loader; journey for the hero; sections and pages for the overrides that apply to the hero and header.
- **The cost is the number of requests, and a request chain:** the fonts are only discovered once `fonts.css` is parsed.
- **Inlining critical CSS** would duplicate the approved CSS and could not be verified rule for rule. It was not chosen.

## 4. Experiments (local lab, median of 3)
| Variant | FCP | LCP | Kept? |
|---|---|---|---|
| Baseline (1.2.18) | 2.73 s | 5.16 s | – |
| C: one concatenated stylesheet | 2.47 | 4.74 | **yes** |
| C + L: low priority for the hidden light logo variants | 2.47 | 4.71 | no (no measurable gain) |
| C + P: also preload the two first-view font faces | 1.83 | 4.52 | **yes** |
| A: intro hand-off from a tiny inline script instead of the bundle | 3.12 | 5.42 | no lab gain; see 7 |
| B: no intro hold (hero shown at first paint) | 3.07 | 4.48 | **owner decision** (design) |
| C + P + B | 2.10 | 3.66 | owner decision |
| **1.2.19 as built (C + P)** | **1.68** | **4.65** | – |
| 1.2.19 + B | 1.98 | 3.81 | owner decision |

1.2.19 on other pages:

| Page | FCP | LCP |
|---|---|---|
| Arabic homepage | 4.28 → 3.25 s | 6.69 → 6.46 s |
| Destination (Cairo) | 2.79 → 2.04 s | 3.81 → 3.57 s |
| Experiences archive | 2.48 → 1.86 s | 3.51 → 3.20 s |
| Desktop homepage | 0.57 s | 1.25 s (CLS 0.002) |

The render-blocking estimate went from 2,135 ms to 699 ms (local).

## 5. What changed (theme 1.2.19)
1. **One stylesheet per template.**
   - `tools/build.py theme` writes `assets/css/bundle-home.css` (7 sources) and `bundle-site.css` (6 sources), in the same cascade order.
   - Only comments, indentation and blank lines are removed: 160 KB → 129 KB raw.
   - The source files are still the ones to edit. The deploy's test job fails if a bundle is stale.
   - Without a bundle, the theme falls back to the separate files.
   - **Fidelity check:** 36 computed properties of every element, plus `::before`/`::after`, are identical with the bundle and with the separate files. Tested on 12 pages (en, ar, ru, zh, fr homepages; archives; destination; experiences in de; 404; privacy) at 390 and 1440 px: 0 differences.
2. **Font preload on the homepage:** the display serif and the light sans of the page's script (Latin, Cyrillic or Arabic; none for Chinese, which uses Google Fonts). They now download alongside the stylesheet instead of after it.

## 6. Production result (theme 1.2.19, deploy #36, after a GoDaddy flush)
PageSpeed Insights, mobile, homepage. Two runs; PSI runs vary by ±0.3 s and ±50 ms TBT between runs.

| Run | Perf | FCP | LCP | TBT | CLS | SI | Render-blocking est. |
|---|---|---|---|---|---|---|---|
| before (1.2.17, 1 Oct 13:54) | 82 | 2.0 s | 4.1 s | 0 ms | 0 | 4.6 s | 1,730 ms |
| 1.2.19 run 1 (19:35, first request after the flush: edge MISS) | 76 | 2.2 s | 4.4 s | 110 ms | 0 | 6.3 s | 500 ms |
| 1.2.19 run 2 (19:38) | 83 | **1.7 s** | 4.2 s | 10 ms | 0 | 4.5 s | – |

PSI's LCP breakdown on production:
- LCP element: `p.hero__copy`;
- time to first byte: 0 ms;
- **element render delay: 3,230 ms**.

**Verdict:**
- The CSS work removed most of the render-blocking cost (estimate 1,730 → 500 ms) and improved FCP (2.0 → 1.7 s in the warm run).
- **It did not improve LCP.** 4.1 → 4.2 s is within run-to-run noise. On production, LCP is decided by the intro hand-off (the render delay), not by CSS.
- So performance is **not fixed** by 1.2.19. See section 7.

## 7. Why LCP stays above 2.5 s, and what would close the gap
The rest of the LCP comes from three deliberate design choices, not from defects:
1. **The intro loader.**
   - It holds the hero for at least 1.3 s, and the hand-off runs in the bundle after the fonts and the hero photo.
   - Lab LCP counts everything that happened before the hero text appears.
   - Removing the hold (B) saves about 0.85 s in the lab.
   - This changes the approved first impression, so it is the **owner's decision**.
   - Possible middle ground: skip the hold on phones only, or on slow connections only.
2. **Font payload.** Six Latin faces (141 KB), or five Arabic faces (≈ 230 KB), are all visible in the first view. Options for the owner/designer:
   - a variable Inter (one file instead of four, about −50 KB);
   - fewer weights;
   - Arabic subsets limited to the used glyphs (risky for editorial text).
3. **The cinematic journey's JS** (GSAP, ScrollTrigger, Lenis: 50 KB gz). It is set up during the intro so that the scroll story is ready when the hero appears. Deferring it until after the hero would only move the cost: the work would land right after the reveal, and the journey would not be live on the first scroll.

**Not done, on purpose:** making the hero text visible underneath the loader (for example, `opacity: 0.01` instead of 0). It would make Chrome report an early LCP while the visitor still sees the loader. That would improve the score, not the experience.

**A (inline hand-off)** would help slow real phones, whose JS arrives late, but no lab number. It was not shipped because the hero word's fit-to-width runs in the bundle: an early reveal could show the German, Russian or French word resizing on phones.

## 8. Reproduce
- `bash lh3.sh <url> <tag>` (3 runs, median)
- `node lcp.mjs <url>` (LCP candidates)
- `node cssdiff.mjs write|compare` (bundle fidelity)

These are in the session scratch folder. The method is described above.

## 9. Content-first mobile homepage (themes 1.2.22–1.2.27): implemented and measured on production
The decision from section 7 was implemented as responsive behaviour:
- **Desktop (> 900 px)** keeps the approved cinematic intro: the loader, the hold, the scripts as before.
- **Phones (≤ 900 px)** get a content-first first view.

### 9.1 What changed on phones, and why each step was needed
| # | Change | Why (evidence) |
|---|---|---|
| 1 | **No loader screen; no intro hold.** The hero's entrance runs in CSS from the first paint: eyebrow rising from its mask, title and text settling (drawn from the first frame), buttons fading up. Reduced motion: no movement | PSI: 3,230 ms "element render delay" on the hero text. Local LCP 4.70 → 4.11 s |
| 2 | **Homepage scripts run after the first contentful paint and the first-view fonts** (`preload` keeps the downloads early; at most 2 s) | Trace: the deferred scripts ran before the first paint (FCP at ~800 ms), so Lighthouse counted all 86 KB of JS. Local 4.11 → 3.13 s |
| 3 | **Title and hero text drawn at full size in the first frame** (no masked rise for them on phones) | A masked rise starts fully clipped: Chrome logged the LCP a frame later, after the scripts had started |
| 4 | **Logos only where they show** (`<picture>` with a 1 px source for the hidden breakpoint; light variants lazy, warmed after load) | 4 header variants + loader logo = 71 KB before the first paint on phones; 1 is visible. Local 3.13 → 2.94 s |
| 5 | **Journey pin spacer in the markup** | ScrollTrigger re-parented the stage when pinning; Chrome re-reported the Arabic title as a new, later LCP (Arabic only) |
| 6 | **Stale-page check after the first paint, low priority** | PSI listed `/v1/build` (452 ms) on the critical path |
| 7 | **All first-view faces preloaded** (6 Latin; Arabic adds its 5) | The other faces were found only after the CSS; Arabic's Latin faces (spaces, digits) formed a 1.5 s chain. Local FCP en 2.29 → 1.63 s, ar 3.43 → 1.74 s |
| 8 | **Mood photos responsive** (`srcset`, 600–1800 px) | PSI "Improve image delivery": 2 MB of 1800 px images on phones (not on the LCP path; a data and bandwidth saving) |
| 9 | **Chinese: Noto SC on desktop only; phones use the device's CJK font** | PSI on `/zh/`: FCP 15.1 s, LCP 15.8–17.0 s. A 212 KB render-blocking stylesheet, then ~100 font slices (several MB) that finish before the first paint on Google's fast servers. A non-blocking `optional` copy (1.2.26) was not enough on production |

**Not done, on purpose:**
- **Faint text behind a loader:** not used.
- **`content-visibility` on below-the-fold sections:** saves only ~80 ms of main-thread time at 4× CPU, and estimated sizes would land anchor jumps in the wrong place.
- **Arabic font subsetting:** needs new tooling, changes glyph coverage for future editorial text, and needs a typography review.

### 9.2 Production results (PageSpeed Insights, mobile, Moto G Power emulation, slow 4G)
Each run follows a GoDaddy cache flush and a warm-up request, so the run measures the edge copy of the deployed version.

| Page | Version | Perf | FCP | LCP | TBT | CLS | SI |
|---|---|---|---|---|---|---|---|
| `/` baseline | 1.2.17–1.2.20 | 82 / 76 / 83 / 79 | 2.0 / 2.2 / 1.7 / 2.3 s | **4.1 / 4.4 / 4.2 / 4.2 s** | 0 / 110 / 10 / 20 ms | 0 | 4.6 / 6.3 / 4.5 / 5.9 s |
| `/` | 1.2.22 | 92 | 1.8 s | 3.1 s | 0 ms | 0.003 | 3.4 s |
| `/` run 1 | 1.2.23 | 95 | 1.2 s | **2.9 s** | 0 ms | 0 | 1.9 s |
| `/` run 2 | 1.2.23 | 95 | 1.3 s | **2.9 s** | 0 ms | 0 | 2.0 s |
| `/` run 3 | 1.2.23 | 95 | 1.2 s | **2.9 s** | 0 ms | 0 | 1.7 s |
| `/` run 4 | 1.2.27 | 95 | 1.2 s | **2.9 s** | 0 ms | 0 | 1.7 s |
| `/?psi=2` (bypasses the edge: origin TTFB) | 1.2.23 | 93 | 1.4 s | 3.2 s | 0 ms | 0 | 1.7 s |
| `/de/` | 1.2.25 | 89 | 1.4 s | 3.2 s | 10 ms | 0 | 4.9 s |
| `/ar/` | 1.2.24 | 81 | 2.0 s | 4.4 s | 0 ms | 0.001 | 4.0 s |
| `/ar/` | 1.2.27 | 77 | 1.7 s | 4.7 s | 30 ms | 0 | 5.9 s |
| `/zh/` | 1.2.25 (before) | 55 | 15.1 s | 15.8 s | 0 ms | 0.002 | 15.1 s |
| `/zh/` | 1.2.27 | 88 | **1.5 s** | **3.3 s** | 50 ms | 0 | 4.9 s |
| `/` **desktop** | 1.2.27 | 96 | 0.4 s | 0.9 s | 10 ms | 0.002 | 1.8 s |

**Discarded run:** `/de/` read 10.0 s while the edge still held the previous version (the deploy had not been flushed yet). The stale-page guard reloaded the page during the test, so it measured two loads.

**Variance:**
- **Before 1.2.22**, LCP depended on when the intro hand-off ran. That swung with network timing (4.1–4.4 s) and TBT (0–110 ms).
- **Since then, English is stable:** four runs at 2.9 s. LCP equals the first paint of the hero, so it no longer waits on timers or scripts.
- **The remaining spread** comes from:
  - edge vs origin (cold edge: +0.3 s);
  - SI, which varies with when the photo arrives.

### 9.3 Result against the target (≤ 2.5 s)
- **English: 2.9 s** (was 4.1–4.4). Perf 95, TBT 0, CLS 0. **Not ≤ 2.5 s.**
  - What remains before the first paint is the first view's own bytes:
    - six font faces, all drawn on the first screen (141 KB);
    - CSS (22 KB);
    - HTML (22 KB);
    - main-thread layout of a long page (×4 in the simulation).
  - PSI's own LCP breakdown on production shows only **230 ms** of element render delay. The rest is the simulated slow-4G transfer of those bytes.
  - **Next safe step (needs approval):** replace Inter's four static weight files with one variable Inter file (same typeface, about −48 KB, est. −0.25 s). It means downloading a new font file from Fontsource (OFL) and comparing the rendering side by side.
  - Beyond that, only fewer weights on the first screen (a design decision).
- **German: 3.2 s** (one run). Same files as English, longer text; the Speed Index is higher.
- **Chinese: 3.3 s** (was 15.8–17.0 s).
- **Arabic: 4.4–4.7 s**, bound by its eleven font files (~373 KB):
  - five Arabic faces (IBM Plex Sans Arabic 300/400/500/600, Noto Naskh 400);
  - the six Latin faces its spaces and digits use.
  - **Options (owner/designer):**
    - fewer Arabic weights;
    - subset the Arabic fonts (tooling + typography review);
    - on phones, the device's Arabic font, as for Chinese. That would bring Arabic close to the English 2.9 s, but changes Arabic typography on phones.
- **Desktop:** 0.9 s LCP, Perf 96, with the cinematic intro.
