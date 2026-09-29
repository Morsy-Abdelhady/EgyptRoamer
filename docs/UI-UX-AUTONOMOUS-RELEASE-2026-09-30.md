# UI/UX release — 2026-09-30 (theme 1.2.0)

Decision log for the autonomous UI/UX/QC run. Core, content, translations, URLs, settings and the database are unchanged. Indexing stays OFF.

## Baseline

| Item | Value |
|---|---|
| Deployed `main` before this run | `2d949bc` (theme 1.1.8, Core 1.2.5), tagged `baseline-2026-09-30` (rollback: revert the merge commit on `main` and push) |
| Work branch | `ui-release-2026-09-30`, merged into `main` |
| Uncommitted work found | The destination-page redesign prototype implemented on 2026-09-29 after the owner's "تمام طبقه" (section tabs, sidebar, structured body). Kept and reworked into the layout system below |
| Launch blockers | `docs/FINAL-RELEASE-BLOCKERS.md`: every open item needs the owner (affiliate programme, legal entity, contact inbox, 2FA, SSH for `src/`, GoDaddy RUM, HSTS, PHP, Arabic Giza title A2). None can be closed from code |

## Findings (measured on production, theme 1.1.8)

| # | Finding | Evidence | Root cause |
|---|---|---|---|
| F1 | Experience pages reserve an empty sidebar; content hugs the start edge | 64/64 experience pages (8 languages); at 1440 main 856px with 528px empty; at 1024 main 491px | `single-commercial.php` always used `.page-layout` (2 tracks) but printed the aside only with offers (0 live offers) |
| F2 | Reading width shrinks as the screen grows | experience main 707px @768 → 436px @961 → 856px @1440 | the 960px breakpoint switched the empty track on |
| F3 | Destination body on a different axis from its hero and related sections | hero H1 x=56, body x=340, related x=56 @1440 (56/56 pages) | `.page-layout--single` centred the body; the hero and related sections use the container edge |
| F4 | "On this page" did not read as navigation; experiences had none | inline wrapping links; 0 contents on 64 experience pages with 7–8 H2s | content-level box, no component |
| F5 | Long bodies were one undifferentiated column of headings and bullets | screenshots | no presentation for the content's own structure (term lists, H3 runs, callouts, FAQ) |
| F6 | Homepage experience card showed `Pyramids of Giza &#038; Sphinx…` | local and page source: payload JSON carried `&#038;` | `get_the_title()` (HTML) sent to a script field that escapes again |
| F7 | Image heroes filled 78% of a phone screen; no content on the first screen | 658px hero on 390×844 | one min-height for all widths |
| F8 | Guides archive showed a topic filter over an empty archive | `/guides/` | filter rendered regardless of results |

## Decisions

1. **One reading-column system, not per-template fixes** (`pages.css`): tokens `--measure: 46rem`, `--aside: 21rem`, `--layout-gap`.
   - Base `.page-layout`: one column on the container edge, the same axis as the hero text, tab bar and related sections.
   - `.page-layout--aside`: only when a template has sidebar content (`er_layout()` renders the aside only if non-empty). At ≥1200px the sidebar sits on the opposite container edge (sticky); the breakpoint is 1200, not 961/1024, so the column never drops below ~700px when the sidebar appears.
   - `.page-layout--single` (no sidebar at all: guides, articles, pages): centred column, and the hero text and tabs are put on the same column (`'measure' => true`), so each page has one axis.
   - Why not `:has()`-only CSS: the template knows whether it has sidebar content; an explicit modifier is clearer and works in every browser. Old cached HTML with the new CSS degrades to the single column (no empty track).
2. **Key facts belong in the sidebar** (destinations and experiences): a "Key facts" card with the facts and highlights. It is real content, so the sidebar is never empty. Phones and tablets: the card opens the body.
3. **Destination sidebar also links the first three things to do and the planner** (desktop only; below 1200 the full sections follow the body). Shortens the path destination → experience → offer without an extra CTA in the text.
4. **Sticky section tabs** from the body's H2 anchors with scroll spy; they replace the in-body box and reuse its approved translated label (new theme string "On this page" for experiences: the same approved labels as `tools/editorial.py`). Labels are cut at the first colon ("Where to stay: the coast…" → "Where to stay").
5. **Structure at render time only** (`inc/sections.php`): no stored content, block markup, translation or URL changes. Proven on all 120 compiled bodies: the visible text is identical except the punctuation after a card title.
6. **Payload titles**: plain text only for fields the scripts escape (experience and guide titles); destination names and filter labels, which the scripts insert as HTML, keep `get_the_title()`.

## Files changed

Theme only: `inc/sections.php` (new), `src/js/components/sections.js` (new), `assets/js/site.js` (rebuilt), `src/js/site-main.js`, `assets/css/pages.css`, `inc/template-tags.php`, `inc/payload.php`, `single-er_destination.php`, `template-parts/single-commercial.php`, `single-er_guide.php`, `single.php`, `page.php`, `archive.php`, `functions.php`, `style.css`, `CHANGELOG.md`, `languages/*.l10n.php` (one string); `tools/i18n/new-strings.json`.

## Verification

**Before the push (local, `reset.sh ml` build = production content):**
- CI steps run locally: PHP lint 54 files, `seed.json`, `tools/editorial.py check`, JS bundles match `src/js`: all pass.
- 120 compiled bodies through `er_sections_wrap()`: visible text identical (except the punctuation after card titles), sections balanced, no card lists inside callouts.
- 120 singles × 390/1440: tabs = H2s (912), all targets exist, scroll spy follows, bar sticks, no sidebar/body overlap, no overflow, axe 0.
- Page matrix (14 page types × 390–1920): no overflow, no page errors, axe 0.

**Geometry after (production, fresh HTML), destination and experience identical:**

| Width | Hero H1 x | Tabs x | Reading column | Sidebar | Related x |
|---|---|---|---|---|---|
| 390 | 16 | 4 (scrolls) | 16 + 358 | facts card first, in the column | 16 |
| 768 | 31 | 31 | 31 + 707 | in the column | 31 |
| 961 / 1024 | 38 / 41 | same | x + 736 | in the column | same |
| 1280 | 51 | 51 | 51 + 736 | 893 + 336 (ends at the container edge) | 51 |
| 1440 | 56 | 56 | 56 + 736 | 1048 + 336 (ends at 1384 = 1440 − 56) | 56 |

Before (1.1.8, 1440): experience main 56 + 856 with a 528px empty track; destination body 340 + 760 against a hero at 56. Phone hero 523px instead of 658px; the tabs (523) and the key facts (613) are on the first 390×844 screen.

**Deploy:** merge `c45ebbb` on `main` → run #14 "Deploy production": success (tests, rsync of theme and Core, health checks).

**Production (after run #14):**
- 120/120 singles, 8 languages, 390 + 1440, fresh HTML: 0 issues (tabs 912, overflow, overlap, console, axe 0).
- 14 page types × 6 widths: no overflow, no page errors, axe 0.
- Head on 15 sampled pages in 8 languages: canonical = clean URL, `noindex, nofollow`, full hreflang set.
- Indexing unchanged: sitemap 404, default robots.txt, noindex on every page.
- Homepage data: the Giza title is sent as plain text (`&`).
- **Cloudflare:** pages cached before the deploy (for example `/destinations/hurghada/`, the homepage) keep the 1.1.8 HTML with its own 1.1.8 CSS until they expire or the cache is flushed: measured, they render exactly as before (no mixed state). **Owner action: GoDaddy → Flush Cache** to show 1.2.0 everywhere now.

## Deferred (need the owner; unchanged from `docs/FINAL-RELEASE-BLOCKERS.md`)
Affiliate programme (E1), legal pages and entity data (E2), consent/RUM (A3, E3), contact inbox (E4), 2FA (A4), stale `src/` (A1, SSH), HSTS (C2), PHP (C6), Arabic Giza title (A2), archive intros (settings), guides publication (C7).

## Final decision
Layout, navigation and body presentation defects from the audit are fixed on production for all 120 singles. **KEEP INDEXING OFF**: the launch gate in `docs/FINAL-RELEASE-BLOCKERS.md` G is not met (commercial, legal, privacy and security items above).
