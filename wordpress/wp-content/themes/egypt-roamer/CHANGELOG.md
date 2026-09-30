# Egypt Roamer theme — changelog

Versions match `Version:` in `style.css` and `ER_THEME_VERSION` in `functions.php`. CI fails if they differ.

## 1.2.1 — 2026-09-30
Performance, from measurements on production (desktop LCP: homepage 4.45 s, inner pages 2.3–2.6 s; CLS ≤ 0.003):
- Homepage intro loader: waits at most 2.2 s for the hero photo (was 3.5 s) and plays once per browser session; later homepage views in the session show the page at once. The look is unchanged.
- Inner-page hero photos: 640/960/1280/1600/2000/2560 widths (was 900/1400/2000), so a 1440px screen downloads the 1600px file instead of 2000px.
- `preconnect` to the stock-photo host on every page (it was on the homepage only).

## 1.2.0 — 2026-09-30
One reading layout for every inner page (fixes the audit of 2026-09-29: experience pages kept an empty 400px sidebar track, and destination bodies were centred on a different axis from their hero).
- Layout system in `pages.css`: `--measure` (46rem reading column), `--aside` (21rem), `--layout-gap`.
  - `.page-layout` is one column on the container edge, the same axis as the hero text, tab bar and related sections.
  - `.page-layout--aside` is used only when a template has sidebar content (`er_layout()` decides). At ≥1200px the sidebar sits on the opposite container edge and is sticky; below, the key facts open the body and the rest follows it.
  - `.page-layout--single` (guides, articles, pages) centres the column, and the hero text (`er_page_hero( [ 'measure' => true ] )`) and tabs sit on the same column.
- Destinations and experiences: key facts (and highlights) move into a "Key facts" card in the sidebar; destinations also list their first three things to do and the planner CTA (desktop).
- Sticky section tabs built from the body's H2 anchors, with scroll spy (`src/js/components/sections.js`). They replace the in-body "On this page" box and reuse its translated label; experience pages (no box) use the new theme string "On this page" (the approved labels from `tools/editorial.py`).
- Structured body at render time (`inc/sections.php`; stored content and the block editor unchanged): numbered sections; "**Term.** text" lists and runs of H3 + paragraph become card grids; callouts become a dark panel; FAQs share one box.
- Phones: image heroes are at most 62% of the screen, so the first content shows on the first screen.
- Homepage: experience and guide titles with "&" no longer show "&#038;" (the page data sends plain text to fields the scripts escape).
- Guides archive: no topic filter over an empty archive.

## 1.1.8 — 2026-09-29
- The homepage no longer shows destination travel times, matching 1.1.7's key-facts change (owner decision of 2026-09-29), in every language:
  - the "Getting there" fact in the destination panel;
  - the line under each destination in the map list;
  - the first line of the map card.
- The homepage data no longer includes `reach`/`mapReach`, so travel times are gone from the page source too. The stored meta is kept.
- Experience and activity durations are unchanged.

## 1.1.7 — 2026-09-29
- Destination key facts no longer show "Getting there". Its values were travel times (flights, drives, trains), which the editorial fact policy leaves out. This follows the owner's decision of 2026-09-29 and applies to every language.
- Region and Best time remain. Experience durations are unchanged.
- The stored values are kept, and the homepage map and destination cards still use them.
- Measured on 56 destination pages at 320–1440 px: the editorial text starts 30–90 px higher on average. There is no new overflow, and the contents box stays before the first section.

## 1.1.6 — 2026-09-28
- Release that ships 1.1.4 and 1.1.5 to production. No code changes.
- First release through the direct deploy: rsync over SSH with the normal production account (see `docs/DEPLOY.md`). GoDaddy's CI/CD deploy users stopped accepting logins (Runs #6–#8), so 1.1.4 and 1.1.5 never reached production.

## 1.1.5 — 2026-09-28 (not deployed; shipped in 1.1.6)
- Release that ships 1.1.4 to production. No code changes.
- 1.1.4 never deployed: GoDaddy refused the old CI/CD deploy user (Runs #6 and #7). GoDaddy CI/CD was re-enabled with a new deploy user, and this is the first release through it.

## 1.1.4 — 2026-09-28 (not deployed; shipped in 1.1.5)
- Destination and guide pages: the "On this page" box now renders after the introduction, just before the first section. This happens on output only; the stored content is unchanged.
- On phones, the contents links become one swipeable row.
- On phones, key facts with three or more items render as label/value rows (RTL-mirrored).
- Result: the first editorial text on destinations moved from 949 px to 382 px below the hero at 360 px wide.

## 1.1.3 — 2026-09-28
- Editorial components for `.prose`: contents box, planning callout, FAQ accordion (`details`), day-by-day itinerary, list markers. All are RTL-safe.

## 1.1.2 — 2026-09-28
- The hero word fits phone screens in German ("ÄGYPTEN") and Russian ("ЕГИПЕТ"), below 900 px.

## 1.1.1 — 2026-09-28
- GoDaddy's unused front-end stylesheets (`wp-components`, `wp-theme`, `godaddy-styles`) are dequeued for logged-out visitors.

## 1.1.0 — 2026-09-28
- Moods keep their photos, icons and destination in every language (language-neutral ids).
- Inner pages and cards use the approved stand-in photos until a featured image is set.
- Menus hide links to empty archives and to an empty Journal.

## 1.0.0 — 2026-09-26
- The approved Egypt Roamer design as a WordPress theme: CMS-driven homepage, inner templates, translations.
