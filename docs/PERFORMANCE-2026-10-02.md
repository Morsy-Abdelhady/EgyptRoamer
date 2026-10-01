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

## 6. Production result
_To be filled from PageSpeed Insights after the deploy and a cache flush (section 8)._

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
