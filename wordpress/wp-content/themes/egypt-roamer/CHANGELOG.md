# Egypt Roamer theme — changelog

Versions match `Version:` in `style.css` and `ER_THEME_VERSION` in `functions.php`. CI fails if they differ.

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
