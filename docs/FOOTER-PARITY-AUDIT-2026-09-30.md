# Global footer parity audit (2026-09-30)

Defect reported by the owner: the English footer differed from the footer in the other languages. Confirmed on production and locally; fixed in theme 1.2.6 / Core 1.2.9 (commits `ba34a42`, `a5a527a`).

## Where the footer comes from
One template, `footer.php`, for every language:

| Part | Source | Per language via |
|---|---|---|
| Newsletter (eyebrow, title, copy, form, privacy note) | Homepage settings + theme strings; note links `privacy_page` | Polylang strings / theme l10n; `er_translated_post_id()` |
| Columns Explore / Plan / Egypt Roamer | WordPress menus in locations `footer_explore`, `footer_plan`, `footer_company` via `er_menu()` | **before:** Polylang's per-language menu for each location · **now:** the default language's menu, localized item by item |
| Logo | brand asset, alt from theme l10n | theme l10n |
| Copyright | theme string | theme l10n |
| Legal row | `er_legal_links()`: Privacy, Terms, Cookies, Disclosure, Contact pages not already in a column | translated page when published |
| Language switcher | not in the footer (header and mobile menu) | — |
| Social links | none in any language | — |

## Root cause
The seed (`wp egypt-roamer seed --languages`) generated one menu per language and location from the English menus (`language_menus()`), leaving out every item it could not translate **at that time**:
- `Footer — Plan`: "Trip Builder" is a homepage anchor (`/#planner`); anchors were skipped, so the menu was empty and never created.
- `Footer — Egypt Roamer`: Our Story, How We Choose, Partner With Us, Affiliate Disclosure, Contact were English-only pages then, so the menu was empty and never created.
- Polylang assigns menus per language (`nav_menus[theme][location][lang]`) and swaps any location menu for the language's copy (`wp_nav_menu_args`). With no copy, the location rendered nothing.

The legal row hides pages already in a column. English had Disclosure and Contact in its column, so its legal row showed Privacy and Cookies only. The other languages had no column, so their legal row showed all four pages. The legal pages being translated later (28 pages, run 3) never reached the stale per-language menus.

## Differences found (production, rendered DOM, homepage; en ar de fr it es ru captured, zh blocked by a Cloudflare challenge, local zh identical to the others)

| Component | English | Other 7 languages | Cause | Intentional? |
|---|---|---|---|---|
| Columns | Explore, Plan, Egypt Roamer (3) | Explore (1) | stale per-language menus | defect |
| Plan column | Trip Builder → `/#planner` | missing | anchor skipped by the menu generator | defect |
| Egypt Roamer column | Affiliate Disclosure, Contact | missing | pages untranslated at seed time | defect |
| Legal row | Privacy Policy, Cookie Policy | Privacy, Cookies, Disclosure, Contact | de-duplication against the missing column | defect (consequence) |
| Footer links total | 5 column + 2 legal | 2 column + 4 legal | as above | defect |
| Newsletter, logo, copyright | present | present, localized | — | same |
| Terms | hidden (draft) | hidden | project decision (Terms blocked on the lawyer) | intentional, consistent |

## Fix
- `er_menu()`: footer locations render `er_canonical_menu_id()` (the default language's menu) in every language. A late `wp_nav_menu_args` filter undoes Polylang's swap for them.
- `er_localize_menu_item()` (at `wp_nav_menu_objects`, priority 5, before the existing link check):
  - page/post → its translation;
  - archive → the language's archive;
  - homepage anchor → the language's homepage + anchor;
  - label → the approved UI label (`er_t_strict()`), else the translated page's title.
  - An item with no version in the language is left out, never shown in English.
  - External links are unchanged.
- Labels: "Trip Builder", "Affiliate Disclosure", "Contact", "Our Story", "How We Choose", "Partner With Us", "Best Time to Visit", "Egypt Travel Costs", "Terms", "Cookies" were added to the theme's translation files from the **approved prototype locale files** (`tools/i18n-strings.py` now extracts the seed's menu labels; `tools/build-translations.mjs`). Nothing new was translated.
- Seed: no more per-language footer menus. The old ones stay in the database, unused (they can be deleted in Appearance → Menus; nothing depends on them).
- No database change was needed on production: the English footer menus are the canonical ones.
- Editors: change the footer once, in the default-language menus (locations are now labelled "(all languages)").

## Parity matrix

Before (production and local):

|  | EN | AR | DE | FR | IT | ES | RU | ZH |
|---|---|---|---|---|---|---|---|---|
| Logo | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Newsletter + privacy note | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Section count | 3 | 1 | 1 | 1 | 1 | 1 | 1 | 1 |
| Section order | E·P·C | E | E | E | E | E | E | E |
| Column links | 5 | 2 | 2 | 2 | 2 | 2 | 2 | 2 |
| Legal-row links | 2 | 4 | 4 | 4 | 4 | 4 | 4 | 4 |
| Privacy | legal | legal | legal | legal | legal | legal | legal | legal |
| Cookies | legal | legal | legal | legal | legal | legal | legal | legal |
| Affiliate Disclosure | column | legal | legal | legal | legal | legal | legal | legal |
| Contact | column | legal | legal | legal | legal | legal | legal | legal |
| Trip Builder | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Terms | hidden | hidden | hidden | hidden | hidden | hidden | hidden | hidden |
| Social | none | none | none | none | none | none | none | none |
| Copyright | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Language switcher | header | header | header | header | header | header | header | header |

After (every row identical across the 8 languages):

|  | EN | AR | DE | FR | IT | ES | RU | ZH |
|---|---|---|---|---|---|---|---|---|
| Section count / order | 3 E·P·C | 3 E·P·C | 3 | 3 | 3 | 3 | 3 | 3 |
| Column links | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 |
| Legal-row links | 2 | 2 | 2 | 2 | 2 | 2 | 2 | 2 |
| Targets in own language | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Privacy · Cookies (legal) · Disclosure · Contact (column) · Trip Builder | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Terms | hidden | hidden | hidden | hidden | hidden | hidden | hidden | hidden |
| English text where a translation exists | — | none | none | none | none | none | none | none |

Localized column labels (approved prototype copy):

| | EN | AR | DE | FR | IT | ES | RU | ZH |
|---|---|---|---|---|---|---|---|---|
| Trip Builder | Trip Builder | مخطِّط الرحلة | Reiseplaner | Planificateur | Pianificatore | Planificador | Планировщик | 行程规划 |
| Affiliate Disclosure | Affiliate Disclosure | إفصاح الشراكة | Affiliate-Hinweis | Liens d’affiliation | Informativa affiliazioni | Aviso de afiliación | Партнёрские ссылки | 联盟披露 |
| Contact | Contact | تواصل معنا | Kontakt | Contact | Contatti | Contacto | Контакты | 联系我们 |

The "Egypt Roamer" column heading is the brand in every language (the approved string).

## Tests (local)
- **`tools/qa/footer-parity.mjs`** compares the rendered footer of each English page with its 7 translations (found from the page's hreflang links). It checks:
  - columns;
  - links per column and in the legal row: targets and order;
  - every link in `/<lang>/`, and no `hreflang` pointing to another language;
  - no English text where the locale has a translation;
  - headings, logo, newsletter privacy link, copyright;
  - Arabic in RTL.
- **Parity test on the old code:** it fails with the reported defect (`columns [footer_explore] ≠ English [footer_explore,footer_plan,footer_company]`, …).
- **Parity test on the new code:** all 9 page types (home, destinations, experiences, contact, privacy, cookies, disclosure, a destination, an experience) × 7 languages pass.
- **Responsive:** 8 languages × 320/390/768/1024/1440 (contact page).
  - No page or footer overflow and no script errors; axe on the footer: 0 violations.
  - All footer links take focus with a visible outline.
  - 3 columns everywhere: two rows below 1024 px, one row from 1024 px.
  - Arabic mirrors (column 1 on the right) with RTL text.
  - Footer height at 1440 px is 795–888 px; Russian is tallest because of its longer labels.
- **Visual:** en/ar/de at 1440 and en/ar/zh at 390 plus ru at 320 compared side by side: the same information architecture in every language.
- **Other checks:** PHP lint, `editorial.py check`, `legal.py check` and versions all pass. The `#planner` anchor exists on translated homepages, and the header menu is unchanged.

Observation (not changed): footer text links are about 17 px tall (the approved design's link style); axe reports no target-size issue because of their spacing.

## Production
- **Deploys:** runs #20 and #21 (theme 1.2.6, Core 1.2.9); run #22 (theme 1.2.7), after which GoDaddy "Flush Cache" was run.
- **Before the fix (live):** English showed 3 columns; ar, de, fr, it, es and ru showed 1. zh was challenged by Cloudflare and was identical locally.
- **After the fix:** read page by page in the owner's Chrome, 6 s apart, 38 pages:
  - every language's homepage and contact page;
  - legal pages, archives, destinations, experiences, search, 404.
- **Result:** every page has the same footer, `explore: destinations + experiences | plan: #planner | company: affiliate-disclosure + contact || privacy-policy + cookies`, with every link in the page's own language. Header, brand and switcher were checked on the same pages.
- **Not verifiable:** anonymous HTML through Cloudflare (bot challenge; not bypassed). The cache was flushed after the last deploy.
- **Full-site context:** `docs/FULL-SITE-AUDIT-2026-09-30.md`.
