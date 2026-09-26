# Egypt Roamer — Homepage

A cinematic, editorial homepage for Egypt Roamer, an independent Egypt travel-discovery and affiliate platform.

## Brand assets

Approved logo artwork lives in `assets/img/brand/source/` and is never redrawn. Web
variants (dark/light lockup, symbol, favicons, app icons, share image) are derived from
it by `python tools/brand_assets.py` — crops, scaling and, for the light variant only,
a colour swap of the white artwork to #101820.
Static HTML/CSS/JS — no build step.

## Run locally

Just open `index.html` — double-clicking it works (no server needed).

For development, a no-cache server is nicer:

```bash
python tools/dev_server.py 5173
```

### Build step (after editing any JavaScript)

The sources are ES modules in `assets/js/`. Browsers block modules on `file://`,
so the page loads a bundled copy, `assets/js/app.js`. After changing any file in
`assets/js/`, rebuild it:

```bash
python tools/build.py
```

## Languages (English · Deutsch · Français · Italiano · Español · Русский · 中文 · العربية)

The language menu switches the whole page — right-to-left layout for Arabic, and
Arabic / Chinese web fonts that are only downloaded when those languages are chosen.
The choice is remembered, and `?lang=ar` / `?lang=en` links work too.

- `assets/js/locales/*.js` — one file per language (de, fr, it, es, ru, zh, ar): UI text (keyed
  by the English source text), rich fragments, and per-id content (destinations,
  experiences, offers…). Run `python tools/check_locales.py` to confirm every
  language has all the keys.
- `assets/js/i18n.js` — detection, `t()` / plural-aware `tp()`, static-page translation.
- `assets/css/rtl.css` — mirrored layout for Arabic plus Arabic/Chinese typography
  (Noto Naskh Arabic + IBM Plex Sans Arabic; Noto Serif/Sans SC).

To add a language, copy `locales/de.js` to `locales/<code>.js`, translate it, then
register it in `DICTS` and `LANGS` in `i18n.js` (menu order follows `LANGS`), add the
code to the list in the `<head>` script (plus a font entry if the script needs one)
and to the two language menus in `index.html`, then rebuild.

## Structure

```
index.html                  Semantic page shell + icon sprite
styleguide.html             Visual system (palette, type, buttons, icons, principles)
assets/css/
  tokens.css                Brand colours, type scale, spacing, radii, motion
  base.css                  Reset, typography, buttons, chips, reveal utilities
  layout.css                Loader, nav, journey rail, dock, overlays, footer
  journey.css               Hero + pinned Pyramids → Nile → Desert → Red Sea story
  sections.css              Moods, destinations, map, partners, experiences, guide, planner, interlude
  rtl.css                   Right-to-left overrides + Arabic typography
assets/js/
  data.js                   ALL content + affiliate data (replace with CMS / partner API)
  utils.js                  Helpers, event bus, toast, smooth scroll
  main.js                   Boots each component
  i18n.js, locales/ar.js    Language system + Arabic translation
  app.js                    GENERATED bundle (python tools/build.py) — do not edit
  components/               One file per section (journey, moods, destinations, map, …)
```

## Plugging in real affiliate data

Every card, offer row and recommendation is rendered from `assets/js/data.js`.
Keep the object shapes and set `href` to the tracked partner deep link — links whose `href`
is anything other than the `#partner` placeholder navigate normally (`rel="sponsored noopener"`).
Prices, ratings and review counts in `data.js` are sample values.

## Motion

GSAP + ScrollTrigger and Lenis load from jsDelivr. Without them — or with
`prefers-reduced-motion` — the journey falls back to stacked full-screen scenes.

Photography: Unsplash (see `data.js` / `index.html` for photo IDs).

## QA (responsive matrix, audit, screenshots)

Headless Chrome/Edge harness in `tools/qa/` — needs the dev server running.

```bash
python tools/qa/run.py audit                      # 11 viewports × 14 scroll positions
python tools/qa/run.py audit --rm                 # prefers-reduced-motion
QA_BROWSER=edge python tools/qa/run.py audit      # Microsoft Edge
python tools/qa/run.py audit --targets footer,s4,s3,s2,s1,top   # reverse scroll
python tools/qa/run.py shots --sizes 1440x900     # screenshots → qa-shots/<size>/
python tools/qa/summary.py -v                     # grouped issues from the last audit
python tools/qa/sheet.py 1440x900                 # contact sheet of a size
```

The audit (`tools/qa/audit.js`) checks horizontal overflow, clipped/offscreen UI,
text overlaps, pinned-stage collisions, wrapped/truncated controls, heading orphans,
touch targets, broken images/alt text, accessible names, duplicate ids, icon/label
alignment, button-size consistency and exact journey-rail geometry.
