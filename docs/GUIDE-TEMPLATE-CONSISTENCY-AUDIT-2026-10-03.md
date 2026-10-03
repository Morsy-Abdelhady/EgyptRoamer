# Guide vs Destination vs Experience — template consistency audit — 2026-10-03

Production: theme 1.2.35 / Core 1.2.21. **Audit only: nothing was changed, deployed or published.**

Evidence:
- **Production measurements** (rendered DOM) at 320, 390, 430, 768, 1024, 1280, 1440 and 1920 px:
  - Luxor and Cairo (destinations), Abu Simbel and Valley of the Kings (experiences), the Cairo, 7 Days and Hidden Gems guides;
  - plus the other destinations and experiences at 390, 768, 1280 and 1440.
- **Luxor and Abu Simbel in all 8 languages** at 390 and 1280.
- **Full-page screenshots** reviewed by eye (90 images).
- **axe** on all three guides, the guide archive, Luxor and Valley at 390 and 1440.
- **Code reading** of the three templates and their partials.

Labels: **OBSERVED** means measured or seen. **INFERRED** means reasoned from observations. **RECOMMENDED** means a proposal.

## 1. Executive summary

**Why do the guides look different?**
- **OBSERVED:** the three templates share one visual system: the same header, hero function, breadcrumb, typography scale, section tabs, prose styles, cards, FAQ and footer. There is **no guide-specific CSS**.
- The guides look different for **three template-level reasons**, two of them accidental:

1. **The hero has no photo (accidental).**
   - `single-er_guide.php` was copied from the article template (`single.php`). It passes only a Media Library image to `er_page_hero()`, never the seed stand-in photo (`stock`) that destinations and experiences pass.
   - The guides have stand-in photos (they show on their cards), but the hero falls back to a text-only dark band. OBSERVED on production: "text-only hero" is still true.
2. **The guides use a centred reading column, with no sidebar (intentional).** The card sections below don't follow that column (accidental):
   - from 1024 px the title, intro, tabs and body move to a centred 736 px column (left edge 144 / 272 / 352 / 592 px at 1024 / 1280 / 1440 / 1920);
   - "Experiences / Destinations in this guide" stay on the page gutter (41 / 51 / 56 / 296 px). **Two alignment axes on one page.**
3. **No next step (needs a product decision).** Destinations end their sidebar with "Plan My Trip"; experiences have the partner or help box. Guides end at the FAQ and two card grids.

A side effect of 1 and 2: the 92 px title is held to the 736 px column, so it wraps to 3–4 lines. The text-only hero then grows taller at desktop instead of shorter (Hidden Gems: 927 px at 1440, against ~702 px for photo heroes), pushing the first paragraph below the fold.

**What should change (RECOMMENDED, smallest safe set):**
- give the guide hero its stand-in photo (§15, step 1);
- put the guide's card sections on the guide's own reading axis (step 2);
- decide whether guides end with the existing "Plan My Trip / ask us" box (step 3).

Everything else is either shared already or an intentional editorial difference.

## 2. Rendering architecture (OBSERVED)

```
Destination  single-er_destination.php
  → er_page_hero(stock photo, measure: no sidebar? → false)      inc/template-tags.php:435
      → er_breadcrumbs()                                         template-tags.php:391
  → er_body() → er_section_nav(tabs)                             inc/sections.php:25 / :131
  → er_layout(prose, aside: er_glance(facts, highlights) + "Things to do" nav + Plan My Trip)
  → related: Things to do · Activities · Partner offers · Plan your trip (guides) · More of Egypt  (er_card_grid)

Experience   single-er_experience.php → template-parts/single-commercial.php
  → er_page_hero(stock photo, meta: destination · duration)
  → er_body() → er_section_nav(tabs) → experience preview (template-parts/experience-preview.php, Abu Simbel only)
  → er_layout(prose + "Is it right for you?" + "Good to know", aside: er_glance(facts) + partner box | help box)
  → related: Ways to do it · Alternatives · Related activities · Where it happens · Plan it with our guides

Guide        single-er_guide.php   (a near copy of single.php, the article template)
  → er_page_hero(featured image only, meta: By · date · updated · read time, measure: true)
  → er_body() → er_section_nav(tabs, measure: true)
  → page-layout--single (centred prose, author box if the author has a bio)
  → related: Experiences in this guide · Destinations in this guide
```

Shared by all three: `header.php` (header, primary links), `footer.php` (footer, dock, mobile menu, assistant), `er_card()`, `er_section_nav()`, `er_page_hero()`, `.prose` styles in `pages.css`.

## 3. Destination template (OBSERVED)

- **Hero:** photo hero, min-height ~702 px on desktop and 558 px on phones; the title reaches up to 18ch (995 px at 1280).
- **Body:** one left axis on the gutter (51 px at 1280), with the sidebar on the right from 1200 px: key facts, highlights, a "Things to do" shortcut, Plan My Trip.
- **Ending:** related card sections, all on the same gutter.
- **Content:** a serif lead paragraph before the first numbered section.

## 4. Experience template (OBSERVED)

- **Hero:** the same photo hero and axis as destinations. The hero meta row shows destination and duration.
- **Sidebar:** key facts, plus the partner box (or a help box with "Chat / Plan My Trip").
- **Body:** extra "Is it right for you?" and "Good to know" blocks. The body starts directly at section 01 (no lead paragraph). Body text is Inter 17 px; destinations open with a 20.5 px serif lead, a difference in content structure, not template.
- **Preview:** the experience preview sits after section 01 (Abu Simbel).

## 5. Guide template (OBSERVED)

- **Hero:** text-only (no `stock` passed), on the dark band. The meta row shows byline, date, "Updated" and reading time.
  - Title, intro, tabs and body sit on a centred 736 px column from 1024 px.
  - No sidebar, no key facts, no partner, help or Plan box.
- **Related:** "Experiences / Destinations in this guide" grids at full container width on the gutter. The cards fade in on scroll, as everywhere.
- **Code:** `single-er_guide.php` differs from `single.php` only by the byline fallback and the section tabs. Its header comment still says "same editorial template as articles".
- The guide archive (`/guides/`) uses the shared archive template.

## 6. Exact visual differences

Measured on production at 1280 px (left edges in px; 390 / 768 behave the same for all three):

| Area | Destination (Luxor) | Experience (Valley) | Guide (7 Days) | Classification |
|---|---|---|---|---|
| Hero | photo, 702 high | photo, 702 | **text-only, 759 (815 at 1440, Gems 927)** | **ACCIDENTAL / INCONSISTENT** (stock photo not passed) |
| Title edge / max width | 51 / 995 | 51 / 995 | **272 / 736** | INTENTIONAL (reading column), but see hero height |
| Intro, meta, crumbs edge | 51 | 51 | 272 | INTENTIONAL |
| Tabs edge | 66 (= gutter + 14 link padding) | 66 | 286 (= column + 14) | SHARED SYSTEM (same offset) |
| Body edge / width | 51 / 736 | 51 / 736 | 272 / 736 | SHARED width; axis INTENTIONAL |
| Sidebar | 893 | 893 | none | INTENTIONAL |
| Related cards edge | 51 | 51 | **51** (body at 272) | **ACCIDENTAL / INCONSISTENT** |
| H1 | Playfair 92.16 / 99.5 | same | same | SHARED |
| H2 (sections) | Playfair 38.4 / 41.5, numbered 01… | same | same | SHARED |
| Body text | lead serif 20.5; sections Inter 17 | Inter 17 | lead serif 20.5; sections Inter 17 | SHARED (content-driven) |
| Eyebrow | Inter 500 11 px, uppercase, 3.5 px tracking | same | same | SHARED |
| Hero meta | none | Inter 14 (destination · duration) | Inter 14 (by · date · read time) | INTENTIONAL |
| Tabs text | Inter 600 14 | same | same | SHARED |
| CTA | Plan My Trip (sidebar) | partner / help box | **none** | **NEEDS PRODUCT DECISION** |
| FAQ | accordion | accordion | accordion (2 of 3 guides) | SHARED |
| Cards | `er_card` | `er_card` | `er_card` | SHARED |
| Footer | shared | shared | shared | SHARED |

Phones (≤ 430 px): all three share one axis (16–17 px). The only visible difference is the guide's photo-less hero.

## 7. Intentional differences (KEEP)

- **Guide:** byline, date, "Updated", reading time; text-led long-form body in one centred reading column; no key facts; no partner offers; the author box when an author has a bio.
- **Experience:** key facts (location, duration); the "Is it right for you?" and "Good to know" blocks; the partner / help box; the experience preview; alternatives.
- **Destination:** key facts and highlights; the "Things to do" sidebar shortcut; "Things to do", "Plan your trip", "More of Egypt" sections.

## 8. Accidental inconsistencies

1. **Guide hero without its photo.** OBSERVED: `single-er_guide.php` passes `'image' => thumbnail` but no `'stock'`; destinations and experiences pass `er_stock_id_for()`. INFERRED: inherited from the article template, where posts have no stand-in photos.
2. **Two alignment axes on guide pages from 1024 px.** OBSERVED: body at 144–592 px, cards at 41–296 px. INFERRED: `er_card_grid()` sections sit in `.page-body.container`, outside the `.page-layout--single` column.
3. **Tall text-only hero at desktop.** OBSERVED: 759–927 px, against ~702 px for photo heroes. The 92 px H1 is limited to the 736 px column (3–4 lines). INFERRED: a consequence of 1 + the centred hero; the H1 scale itself is shared.

## 9. Shared design-system recommendations

| Element | Current | Target | Templates | Risk | Why |
|---|---|---|---|---|---|
| Hero media | dest/exp: stand-in photo; guide: none | all three: photo when one exists (featured image, else stand-in) | guide | Low. One argument. Performance: +1 hero image like every other page (§14) | One brand hero; guides already have verified stand-ins |
| Related-card axis | guide cards on the page gutter, body centred | guide cards on the guide's own column (2 columns in 736 px ≈ 360 px cards, the size cards have elsewhere) | guide | Low. Scoped CSS class | One axis per page |
| Reading width | 736 px everywhere | keep | — | — | Already shared |
| Typography, eyebrow, tabs, cards, FAQ, footer | shared | keep | — | — | Already shared |
| Next step | guides: none | the existing help box ("Plan My Trip / Ask us") after the body | guide | Low. Existing component and strings | Decision for you (§15, step 3) |

## 10. Things that should remain different

- Guide byline / date / reading time; no key facts; centred long-form column; no partner box.
- Experience facts, preview, decision blocks, partner / help box.
- Destination facts, highlights, discovery sections.
- No single merged template.

## 11. Technical duplication

| Item | Classification |
|---|---|
| `single-er_guide.php` ≈ `single.php` (75 vs 72 lines; differs only in byline fallback + section tabs) | REQUIRES REFACTOR (optional): one shared partial for "editorial single" would stop the two drifting (the tabs already differ: articles don't get them) |
| Hero, tabs, cards, prose, FAQ CSS | SAFE: already shared (no guide-specific CSS; `.guide` in `sections.css` is the homepage section) |
| Related-section markup repeated in 3 templates (`<section class="related">` + `er_card_grid`) | KEEP SEPARATE (titles and sources differ per type) |

## 12. Responsive findings (OBSERVED)

| Width | Destination / Experience | Guide |
|---|---|---|
| 320 | one axis at 16; photo hero 558–619 | one axis at 16; text hero 701 (title 557 high) |
| 390 / 430 | axis 16 / 17 | axis 16 / 17; text hero 592 |
| 768 | axis 31; photo hero 702 | axis 31; text hero 493 |
| 1024 | axis 41; no sidebar yet (sidebar from 1200) | body 144 / cards 41 |
| 1280 | axis 51, sidebar 893 | body 272 / cards 51 |
| 1440 | axis 56, sidebar 1048 | body 352 / cards 56; Gems hero 927 |
| 1920 | axis 296, sidebar 1288 | body 592 / cards 296 |

Languages (Luxor + Abu Simbel, 8 languages, 390 and 1280):
- one type system: H1 48 → 92.16 px, the same body sizes;
- Chinese adds letter-spacing; Arabic has no negative tracking and mirrors (sidebar on the left);
- German, Russian, French, Spanish and Italian titles wrap to more lines (Abu Simbel title 409–475 px high on phones) with no overflow (document width = viewport everywhere).

Guides exist only in English.

## 13. Accessibility findings (OBSERVED)

- axe: **0 violations** on the three guides, the guide archive, Luxor and Valley (390 and 1440).
- Headings: one H1, then H2s, on all of them.
- Note (from the duplicate-navigation audit): the desktop links and the hidden mobile menu share the landmark name "Primary" on English pages.

## 14. Performance implications

- **Hero photo for guides:** +1 image per guide page, through the same responsive crop pipeline and `fetchpriority="high"` as the other heroes. Expected cost, the same as a destination hero: ~120–250 KB, LCP becomes the image. INFERRED: guide LCP would rise to the level of destination pages (production Cairo destination LCP ~1.5 s in the 2026-10-03 baseline), from today's text-LCP guide (~1.26 s). That is the trade for one hero treatment. Measure before and after on the 3 guides.
- **Card axis and help box:** CSS only, no new JS, no extra downloads (the help box exists and uses existing strings).
- Nothing proposed adds global JS, frameworks, video or new fonts.

## 15. Proposed smallest safe implementation (RECOMMENDED; not done)

1. **Guide hero photo:** in `single-er_guide.php`, pass `'stock' => er_stock_id_for( $er_id )` to `er_page_hero()`, as destinations do. One line. The hero gets its existing verified stand-in. Featured images still win when set.
2. **One axis per guide page:** give the guide's related sections the reading column (a scoped class, e.g. `related--measure`: centred, `max-width: var(--measure)`, 2-column grid) in `pages.css`. Scoped to guides, so destinations and experiences are untouched.
3. **Product decision, guide next step:** reuse the experience help box (`offer-box--help`: "Plan My Trip" + "Ask us") after the body. Only if you want guides to convert; editorially it's optional.
4. **Optional:** fold `single.php` and `single-er_guide.php` into one shared editorial partial (no visual change). Later, low priority.

Not recommended: copying the experience template, adding key facts to guides, or changing the H1 scale.

## 16. Risks

- **Step 1 changes guide LCP from text to image.** It needs a before/after measurement and a crop check of the 4 guide stand-ins at 390 / 768 / 1280 / 1440. Their provenance is already recorded in the seed (Phase 2.1 audit).
- **Step 2 changes card width on guides** (3 columns full width → 2 columns in the reading column). It needs a visual pass in the archive-card states.
- **No SEO effect:** no URL, canonical, hreflang, schema or sitemap change in any step.

**Out-of-scope finding (NEEDS REVIEW):** the Valley of the Kings experience's photo (`pvFtrzwuc6g`, hero and card) is tagged "karnak, louxor". It shows Karnak's columns (East Bank), not the Valley of the Kings or Hatshepsut's temple (West Bank). The Phase 2.1 provenance check matched it at city level. A verified Valley photo exists (see the V1.1 Valley preview sourcing). Replacing it needs your approval.

## 17. Implementation order

1. Decide step 3 (guide next step: yes or no).
2. Step 1 (hero photo), with before/after performance on the 3 guides and a crop check.
3. Step 2 (card axis) and a visual pass at 1024 / 1280 / 1440 / 1920.
4. Regression (responsive, axe, keyboard) and deploy.
5. Optional later: step 4 (shared editorial partial).
