---
name: content-writing
description: Write and publish one Egypt Roamer guide from an approved brief - English markdown in content/editorial/en, 7 translations reviewed sentence by sentence, sources, photo credit, release gate, push and wp-admin publish. Use after content-brief.
---

# Content writing and publishing

Follow `CLAUDE.md` exactly. Owner rule: Claude publishes once the release gate passes; no separate approval per page.

1. Write `content/editorial/en/g-<slug>.md` (front matter like `g-visa.md`; `[[toc]]`; H2 `{#anchors}`; FAQ with `??`).
   Only facts from the brief's sources; figures as "according to <source>". No invented experiences, prices, hours,
   travel times, ratings or statistics.
2. Translate into de, fr, it, es, ru, zh, ar. Front matter: `source: <hash>` (python `editorial.source_hash`),
   `review: approved`, `reviewer: Claude (AI translation review, not a native speaker); approved by the owner on <date>`,
   `reviewed: <date>`. Review each sentence against the English and log it in
   `docs/TRANSLATION-REVIEW-2026-10-26.md`. Arabic: isolate number ranges; arrows point left.
3. Sources in `docs/CONTENT-SOURCES.md` and Core `data/sources.json`. Photo in the seed's `extraStock`; Commons credit
   in `er_commons_credit()` and the image audit §9.
4. `PYTHONUTF8=1 python tools/editorial.py`, then its `check`. Bump Core (both places), the theme if touched, and add
   a CHANGELOG entry.
5. Commit, push `main`, wait for `track.js?ver=<new>` on the live site, then wp-admin →
   `admin.php?page=er-guide-translations` → "Publish / update guide translations", and flush the cache.
6. Check the EN and AR pages live; run `page-coverage.mjs` and `rtl-direction.mjs` once per batch. Add a row to
   `growth/content/published-content.csv` and the page to the Pinterest queue. Report what is live and what is not.
