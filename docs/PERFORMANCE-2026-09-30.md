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

## GTmetrix on production (owner's account, Seattle, Chrome desktop, unthrottled; Lighthouse 12.6.1)
| | Before: 1.2.7, 09:28 (owner's run) | After: 1.2.9, run 1, 10:16 | After: 1.2.9, run 2, 10:26 |
|---|---|---|---|
| Grade / Performance / Structure | C / 63 % / 97 % | C / 53 % / 98 % | C / 60 % / 98 % |
| LCP | 4.8 s | 4.4 s | **3.8 s** |
| TBT | 182 ms | 330 ms | 326 ms |
| FCP | 946 ms | 1.3 s | 803 ms |
| TTI | 2.6 s | 2.4 s | 1.6 s |
| Speed Index / fully loaded | – / 4.8 s | – / 4.4 s | – / 3.8 s |
| CLS | 0 | 0 | 0 |
| TTFB | 61 ms | 76 ms | 75 ms |
| Page size / requests | 872 KB / 31 | 1.03 MB / 33 | 1.03 MB / 33 |
| JS | 80.9 KB | 258 KB | 258 KB |
| LCP element | `p.hero__copy`, 99 % render delay | `p.hero__copy`, 98 % render delay (4.3 s) | same |

- **Baseline build:** the 09:28 run loaded assets `?ver=1790775270` (the 1.2.7 cache flush), so it measured 1.2.7. The 1.2.8 fix had been deployed but its HTML was not yet served. The after-runs followed the 1.2.9 deploy and a GoDaddy "Flush Cache".
- **LCP:** 4.8 → 4.4 / 3.8 s. The remaining delay is the approved entrance, confirmed by GTmetrix's own LCP breakdown.
- **TBT went up (182 → ~330 ms) because of a change outside the theme.** Site Kit by Google was connected on production between the runs. It now loads `gtag.js?id=GT-NBJ3VQHR`: 174 KB (88 KB transferred), a 74 ms long task, and requests to google-analytics.com. JS rose from 81 to 258 KB accordingly. The theme's own JS is unchanged at about 85 KB.
- **Same pattern on every run:** long tasks from ScrollTrigger (114 ms), home.js (152 ms, the journey set-up) and gsap (94 ms).
- **Tests used:** 3 of the Basic plan's 5 on-demand tests (the owner's run plus two after). 2 are kept for after the owner's decision below.

## Owner decisions that set the remaining numbers
1. **Hero entrance.** A local measurement, 3 runs each:

   | Hero text | LCP |
   |---|---|
   | Current fade | 2.7 / 3.4 / 3.6 s |
   | Visible (no fade) | 0.9 / 0.8 / 0.9 s |

   With the loader kept, "visible" only moves the metric (the text is painted under the loader), which is not a real improvement. The honest options:
   - (a) no intro loader on the first visit plus a shorter fade;
   - (b) keep the loader but reveal the text as the loader fades (no separate 0.55 s delay and 1.2 s fade).

   Both change the approved intro.
2. **Google Analytics via Site Kit.**
   - **Performance:** about +100–150 ms TBT and +88 KB of JavaScript.
   - **Privacy:** it sets `_ga`/`_ga_*` cookies without consent, which the published Cookie Policy does not mention.
   - **Options:**
     - keep it, but with Consent Mode, a consent banner and policy updates (legal);
     - or use Core's GTM setting, which defaults to consent "denied".
