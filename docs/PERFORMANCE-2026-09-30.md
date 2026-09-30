# Homepage performance — forensics and fixes (2026-09-30)

Trigger: owner's GTmetrix run on production. Grade C, performance 51 %, LCP 4.8 s, TBT 395 ms, Speed Index 3.2 s, FCP 1.1 s, TTI 1.8 s, CLS 0, TTFB 70 ms.

## How it was measured
| Source | Used for | Limits |
|---|---|---|
| Lighthouse 10.4 on the local copy, 3 runs per case, median, **observed throttling** (`--throttling-method=devtools`) | before/after comparison | local PHP server: TTFB ≈ 0.5 s and occasional stalls (outliers shown). Lighthouse's default simulated mode ignores JavaScript timers, so it cannot see the loader; observed mode was used |
| Chrome performance observers (LCP entries, long tasks), local, with the hero photo delayed 0 / 3 s | LCP mechanics | – |
| Anonymous browser on production (clean profile) | the production LCP element and TTFB | Cloudflare challenged it after 2 pages; not bypassed |
| GTmetrix / PageSpeed Insights | – | GTmetrix needs an account (not created); the keyless PageSpeed API quota was exhausted. **The owner's GTmetrix re-run is the production measurement** |

## Forensics
- **LCP element:** `p.hero__copy`, the hero paragraph ("Ancient wonders. Endless adventures…"). It is text, not the photo.
  - On production's first anonymous visit the LCP entry was the loader logo (`img.loader__logo`), because the hero text had not appeared by the time the entry was read.
  - LCP phases: TTFB 8–9 %, load delay 0, load time 0, **render delay 91–92 %**.
- **Why the text is late:** it only appears after this chain.
  1. The deferred scripts run (gsap, ScrollTrigger, lenis, home.js).
  2. The intro loader waits at least 1.3 s, **counted from when the script runs, not from the first paint**.
  3. It also waits for the hero photo (hot-linked from images.unsplash.com) and the fonts, capped at 2.2 s, **again counted from script start**.
  4. The hero is revealed 250 ms later.
  5. The paragraph fades in with a 0.55 s delay and a 1.2 s transition.
- **The FCP→LCP gap (1.1 → 4.8 s)** is this chain. FCP is the loader logo; LCP is the hero paragraph.
  - On a fast desktop the chain is about 1.3 s (loader minimum) + 0.8 s (reveal and delay) + the fade, which matches the local 4.2 s.
  - When the third-party photo is slow, the loader waits for its cap.
- **Main thread:** home.js set up every homepage section in one task. A 4× CPU profile gave:

  | Section | Time |
  |---|---|
  | journey (scroll scenes) | 270 ms |
  | map | 80 ms |
  | experiences | 29 ms |
  | reveals | 22 ms |
  | **Total, one task** | **≈450 ms** |

- **Other contributors** (not changed):
  - 7 render-blocking stylesheets (≈145 KB uncompressed, fonts, tokens, base, layout, journey, sections, pages);
  - gsap 72 KB and ScrollTrigger 43 KB;
  - GSAP/ScrollTrigger layout refreshes;
  - 6 self-hosted font files.
  - About 1 MB total per homepage load, most of it Unsplash photos.
  - Third-party origins: images.unsplash.com only.

## Fixes (theme 1.2.8)
1. **Loader timing counts from page start.** The loader minimum (1.3 s) and its cap count from `performance.now()`, not from script start, so a slow device does not sit through the intro twice. The intro still lasts at least 1.3 s.
2. **Photo wait capped at 1.6 s.** The loader waits for the third-party hero photo at most 1.6 s from page start (was 2.2 s after script start). The photo still fades in when it arrives.
3. **Intro starts earlier.** The intro starts right after the hero is ready, not after every section.
4. **Sections set up one task each**, in the same order (`scheduler.yield` or `setTimeout(0)`).

The entrance choreography, the loader, the photos and the CSS are unchanged.

## Results (local, observed throttling, median of 3)
| Case | Before: LCP / TBT / SI / score | After: LCP / TBT / SI / score |
|---|---|---|
| Homepage, desktop | **4.16 s** / 30 ms / 2.65 s / 0.72 | **2.2–2.45 s** (photo fast) / 9 ms / 2.2 s / 0.83–0.88; one median of 4.23 s (runs 4.23 / 4.23 / 2.45 s) was measured before fix 2, while the photo host was slow |
| Homepage, desktop, photo delayed 3 s (performance observer, 3 runs) | 4.23 s | **3.63 s** |
| Homepage, mobile (4× CPU, slow 4G) | 9.9 s / 1,189 ms | 9.0 s / 884 ms |
| Destination (Luxor), mobile | – | 2.8 s / 108 ms / score 0.90 |
| Experience (Great Sand Sea), mobile | – | 2.7 s / 20 ms / score 0.91 |

CLS stayed at 0–0.03 in every run.

**Regression checks after the fixes:**
- Homepage display titles fit in 8 languages × 11 widths.
- Homepage links identical in 8 languages.
- Responsive homepage 88 checks, 0 bad.
- Scroll journey plays, 0 script errors (en 1440, ar 390).
- Filmstrip of a throttled phone load: no flash, no layout shift.

## What limits it now, and owner decisions
- **Entrance choreography (design).** After the loader lifts, the hero paragraph counts as LCP about 1.8 s later (250 ms reveal, 0.55 s delay, 1.2 s fade). Bringing desktop LCP reliably under 2.5 s means one of:
  - (a) starting the paragraph with the title (delay 0);
  - (b) a shorter fade;
  - (c) no intro loader on the first visit.

  Each changes the approved entrance, so it is the owner's call.
- **Hot-linked hero photo.** Self-hosting that one photo (an approved stand-in, served from our own cache) would make the loader's photo wait predictable. It needs the owner's approval to download the photo.
- **Mobile main thread.** The cinematic scroll journey (GSAP + ScrollTrigger, pinned scenes) is the largest cost: set-up ≈270–440 ms and layout refreshes of 100–200 ms. Reducing it means simplifying the approved journey on phones (owner decision).
- **Render-blocking CSS** (7 files). Merging them into one file would save round trips on slow networks without changing any rule. Not done in this release; it needs a build step and a CI check.

## To do after deploy (owner)
- Run GTmetrix on `https://egyptroamer.com/` 3 times, desktop (default) and mobile, and record the median. Use the same test location as the first run.
