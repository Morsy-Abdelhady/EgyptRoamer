# Multilingual navigation consistency report (2026-10-15)

**Scope:** the live site (egyptroamer.com) in all 8 languages, before theme 1.2.46 / Core 1.2.32.

**Method:**
- Playwright drove real Chrome, logged out and excluded from Visits.
- Viewports: 320, 360, 390, 412, 768, 1024, 1280 and 1440 px.
- At each one: the desktop menu, the mobile drawer opened by tapping, and the language menu opened. Screenshots were checked by eye.
- Then the existing QA suites, run on the local copy.

**Labels:**
- **[Bug]** confirmed bug
- **[Intentional]** intentional difference
- **[Gap]** editorial gap
- **[Fix]** technical fix
- **[Tested]**
- **[Not tested]**

> **Correction (theme 1.2.47, same day).** Finding #1 below was wrongly classed as intentional. The brief required equivalent navigation in every language, and the owner rightly pointed out that the English menu still had 3 items while the other 7 had 2.
>
> **Classification:** it is a **[Bug]**, not a gap. Every language's menu already contains the translated "Travel Guide" item, and the theme dropped it.
>
> **Fixed in 1.2.47:** items, footer links and the homepage Journal section whose content exists only in English are now kept in every language. They keep their translated label, link the English content, and are marked `hreflang="en"` with a visible "EN" tag.
>
> **Verified:**
> - navigation (desktop and drawer) is 3 items in all 8 languages;
> - homepage sections are identical in all 8 languages;
> - footer parity passes on 9 pages × 7 languages;
> - 64 responsive renders are clean;
> - keyboard, axe and hygiene suites all pass.
>
> Production results: §8.

## 1. Navigation matrix (homepage, production, before 1.2.47)

| Language | Desktop menu (1440) | Mobile drawer (390) | Items, order | Localized URLs | Direction | Language menu | Overflow 320–1440 | Status |
|---|---|---|---|---|---|---|---|---|
| EN | Destinations · Experiences · Travel Guide | same 3 + Plan My Trip + 8 languages | 3 | `/destinations/` `/experiences/` `/guides/` | LTR | 8 links, English marked current | 0 | OK |
| AR | الوجهات · التجارب | same 2 + خطّط رحلتي + 8 languages | 2 | `/ar/destinations/` `/ar/experiences/` | RTL (mirrored header, drawer, menu) | Arabic current | 0 | OK |
| DE | Reiseziele · Erlebnisse | same | 2 | `/de/…` | LTR | DE current | 0 | OK |
| FR | Destinations · Expériences | same | 2 | `/fr/…` | LTR | FR current | 0 | OK |
| IT | Destinazioni · Esperienze | same | 2 | `/it/…` | LTR | IT current | 0 | OK |
| ES | Destinos · Experiencias | same | 2 | `/es/…` | LTR | ES current | 0 | OK |
| RU | Направления · Впечатления | same | 2 | `/ru/…` | LTR | RU current | 0 | OK |
| ZH | 目的地 · 体验 | same | 2 | `/zh/…` | LTR | ZH current | 0 | OK |

In every language:
- **Header CTA:** translated, pointing to `#planner`.
- **Drawer:** opens, with no overflow.
- **Language menu:** each language's link points to that language's version of the page (matching the page's hreflang).

## 2. Findings

| # | Finding | Class | Root cause | Action |
|---|---|---|---|---|
| 1 | "Travel Guide" is in the English menu only; the guide links in the footer are English only | ~~[Intentional]~~ **[Bug]**, fixed in 1.2.47 | Guides exist only in English. The theme drops menu items whose target is an empty archive or has no translation (`er_localize_menu_item`, the "only working links" filter in `inc/template-tags.php`) rather than show English links on translated pages | None in code. **[Gap]** Translated guides need native-reviewed content (see `ARABIC-EDITORIAL-PLAN.md`); the item appears automatically once a guide is published in a language |
| 2 | English homepage has a "Guide" section; the other 7 do not | ~~[Intentional]~~ **[Bug]**, fixed in 1.2.47 | Same as #1: the section renders only when the language has guides | None |
| 3 | Desktop language menu on LTR pages: "العربية" sat on the right edge while the other 7 names were left-aligned | **[Bug]** CSS | The Arabic `<li>` carries `dir="rtl"` (needed so the name renders correctly), and that also set its alignment | **[Fix]** `pages.css`: `html:not([dir="rtl"]) .lang__menu li[dir="rtl"] { text-align: left }`. Arabic pages are unchanged. Tested: local, all items at the same x position |
| 4 | Arabic opening scene (≥ 901 px): the text block sits on the left of the photo, while the note stays top right | **[Intentional]** | Documented design decision in `pages.css`: the Great Pyramid is on the photo's right, so the Arabic text (right-aligned, read right to left) sits on the open sky. Phones stack it at the bottom. The note and the progress rail do not overlap (verified at 1024 and 1440) | None |
| 5 | Archives (`/xx/destinations/`, `/xx/experiences/`): any query string (e.g. `?utm_source=…`) was copied into every hreflang alternate | **[Bug]** SEO | Polylang builds archive translations from the current URL | **[Fix]** Core `multilingual.php`: hreflang alternates drop query and fragment. The language menu keeps the query, so a search term survives a language switch. Tested locally |
| 6 | Offer titles are English (partner product names) on all 8 languages | **[Intentional]** | Offers are language-neutral records; product names are not translated, so they don't imply provider content in that language | **[Fix]** (accessibility) `lang="en"` on the title on translated pages |

**Not found** (checked, no issue):
- duplicated menus;
- a menu visible at the wrong breakpoint;
- clipped or overlapping header items;
- missing language links;
- wrong current-language marking;
- wrong localized URLs;
- broken RTL in the header or drawer;
- horizontal overflow at any of the 8 widths;
- JavaScript errors.

## 3. Homepage sections (production, 390 and 1440)

**All 8 languages, in this order:**
- hero (4 scenes)
- interlude
- moods
- destinations (15 items)
- map
- experiences (8)
- planner
- footer

**English only:** a "guide" section (finding #2).
- No empty or duplicated sections.
- Same item counts in every language.
- The hero h1 is translated in every language.

**Horizontally scrolling rails** (moods, destinations, experiences): they extend past the viewport by design and are not page overflow (`scrollWidth − innerWidth = 0` at every width).

## 4. Core page types (production, every hreflang alternate)

**Pages:**
- 2 destinations (Luxor, Siwa);
- 3 experiences (Abu Simbel, Great Sand Sea, Red Sea diving);
- 1 guide;
- 2 legal pages (Privacy, Affiliate Disclosure);
- 2 archives.

**Coverage:** 8 languages each, except the guide (English only).

**Results:**
- 200 everywhere.
- Canonical = self.
- 9 hreflang links (8 languages + x-default).
- `lang` and `dir` are correct (Arabic `rtl`).
- No `noindex` on these pages.
- Valid JSON-LD (TouristDestination / TouristTrip / Article / BreadcrumbList).
- No overflow at 390 or 1440.
- Every page with a partner link shows the disclosure.
- Translated CTA labels: Verfügbarkeit prüfen, Voir les disponibilités, Verifica disponibilità, Ver disponibilidad, Проверить наличие, 查看空位, تحقّق من التوفّر.

## 5. Regression and accessibility (local, after the fixes)

| Suite | Result |
|---|---|
| `keyboard.mjs`: 8 languages × 1440 / 390, skip link, focus rings, language menu, overlays, menu focus trap | **all ok** |
| `overlays.mjs`: axe with saved / search / menu / assistant open (en 1440, ar 390, zh 1440) | **0 violations** |
| `hygiene.mjs`: duplicate ids, orphan labels, unsafe `_blank`, inline handlers, unnamed links (12 pages, en + ar) | **all ok** |
| `footer-parity.mjs` | 14 differences per page, all finding #1 (Guides links absent where no guide exists in that language). **[Intentional]**, unchanged by this release |
| `acceptance.mjs`, `leads.mjs` (earlier today, Core 1.2.31) | PASS |

**[Not tested]:**
- real iOS/Android devices (only emulated widths);
- no-JavaScript mode for the drawer (the drawer requires JS; the desktop links are plain HTML).

## 6. Screenshots

Taken during the audit and kept with the QA run (scratchpad `pwt/nav/`, not committed):
- `menu-{lang}-390.png`: open drawers, 8 languages;
- `header-{en,ar,zh}-{1024,1440}.png`: language menu open;
- `hero-{en,ar}-1440.png`;
- `home-{lang}-{390,1440}.png`: full pages;
- `langdd-en-after.png`: the fix for #3.

## 7. Remaining editorial work

- **[Gap]** Translated guides: none of the 7 languages has a guide, so "Travel Guide" and the footer guide links stay hidden there. Each guide needs native-reviewed text before publishing (owner rule).
- **[Gap]** Legal translations are still "drafted, pending native review", including the 15 Oct privacy sentences.

## 8. Production after theme 1.2.47 (commit `0fcea37`, cache flushed)

| Language | Desktop menu | Drawer | Homepage sections | Footer parity |
|---|---|---|---|---|
| EN | Destinations · Experiences · Travel Guide | same | top, interlude, moods, destinations, map, experiences, guide, planner | reference |
| AR | الوجهات · التجارب · دليل السفر `EN` | same | identical | ✓ |
| DE | Reiseziele · Erlebnisse · Reiseführer `EN` | same | identical | ✓ |
| FR | Destinations · Expériences · Guide de voyage `EN` | same | identical | ✓ |
| IT | Destinazioni · Esperienze · Guida di viaggio `EN` | same | identical | ✓ |
| ES | Destinos · Experiencias · Guía de viaje `EN` | same | identical | ✓ |
| RU | Направления · Впечатления · Путеводитель `EN` | same | identical | ✓ |
| ZH | 目的地 · 体验 · 旅行指南 `EN` | same | identical | ✓ |

**Production checks:**
- Footer parity on `/`, `/destinations/cairo/`, `/experiences/` and `/privacy-policy/`: pass for all 7 languages.
- The first run on `/de/` showed the pre-release copy from the edge cache; after the flush it passed.

**Local, same code:**
- 64 responsive renders: 0 problems.
- `keyboard.mjs`, `overlays.mjs` and `hygiene.mjs` all pass. The first axe run flagged contrast on the EN tag at 75 % opacity; the opacity was removed.

**Remaining [Gap]:** native-language guides. When a guide is published in a language, the menu, footer and homepage switch to it automatically and the EN tag disappears.

## 9. Guides translated and published (theme 1.2.48 / Core 1.2.33, commit `e1c200d`)

**Owner decision (15 Oct):** translate the guides and publish them right away with a review note, instead of linking the English.

- **The translations:** 28 guide translations (4 guides × 7 languages), faithful to the English, in `content/editorial/<lang>/g-*.md` (`review: pending`).
- **Publishing:** done on production through **Egypt Roamer → Guide translations**: `{"created":28,…}`, re-run `{"current":28}`.
- **The review note:** each opens with "translated from the English original, not yet reviewed by a native speaker; the English version is the reference", linked to the English guide.
- **Indexing:** IndexNow sent the 28 URLs (answer 200). Each language's sitemap now has a guides sitemap.

**Production after the release:**
- **Menu:** Destinations · Experiences · Travel Guide in all 8 languages, each linking its own `/xx/guides/`, with **no "EN" tag** any more.
- **Homepage:** the Journal shows guides in the page's language, in all 8.
- **Guide pages:** indexable, 9 hreflang links, internal links pointing to the same language, no overflow at 390 or 1440.
- **Fixes found while checking:**
  - reading time was "1 min" in Arabic and Russian (Latin-only word count), now 3 min in every language;
  - dates were "October 15, 2026" on every language, now in the page's language ("15 أكتوبر 2026", "15. Oktober 2026", "2026年10月15日").

**Regression (local):**
- footer parity: pass on 9 pages × 7 languages;
- keyboard, axe and hygiene: pass;
- 64 responsive renders: 0 problems.

**Remaining [Gap]:** native review of the 28 translations. When a reviewer approves one, set `review: approved` (+ reviewer and date) and re-run the sync; the note then changes to a plain "translated from the English original".

## 10. Translations reviewed, note removed (Core 1.2.34, commit `8d7b1b5`)

**Owner request (15 Oct):** remove the review note and check the translations professionally.

**Review pass on the 28 translations:**
- **Terminology** aligned with the translations the owner approved on 29 Sep. The Abu Simbel sun event uses each language's approved term (ظاهرة تعامد الشمس, Sonnenwunder, phénomène solaire, fenomeno del sole, fenómeno solar, солнечный феномен, 太阳奇观), not "festival".
- **Proper names** are spelled as on the approved pages.
- **Wording:** awkward phrasings rewritten (5 Arabic, 1 Russian, 1 Italian).

**Status:**
- All files are `review: approved` (AI review; publication approved by the owner). An approved translation has no note.
- Synced on production: `updated 28`.

**Deploy:** the first run failed at the SSH preflight (nothing transferred). A re-run of the failed job succeeded.

**Production check of all 28 guides:** 200, correct `lang`, indexable, **no note**, no overflow at 390, approved terms present.
