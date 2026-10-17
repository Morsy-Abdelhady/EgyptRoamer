# Moving proposal 4 into the theme, behind ?ux=2 (started 17 Oct 2026)

**Owner, 17 Oct:** "Move the fixes into the theme and start with them behind ?ux=2."

**Rules:**
- Everything is gated by `er_ux2()` (the `?ux=2` preview, or the `er_ux2` option once the owner switches it on). Visitors without the preview see no change.
- No invented content. New UI strings go into `tools/i18n/new-strings.json` for all 8 languages and are marked unreviewed.
- Each slice is tested locally on a phone and a computer, in English and Arabic, and passes `tools/qa/page-coverage.mjs`.
- Each slice is pushed only after the owner's OK.

**Source:** the prototype modules in `docs/prototypes/journey-v4/`. The changes and their reasons are logged in `docs/UX-EXPERT-REVIEW-2026-10-17.md` §1–12.

## Slices

| # | Slice | Prototype source | Status |
|---|---|---|---|
| 1 | Header and menu: Places / Trips & tours with a line each, search first in the phone menu, Trip Builder link, ✕ on the menu button, "Ask" in the header instead of the floating assistant | `chrome2.py`, `v4-round7.py`, `v4-tourist2.py` §3/§5 | **Live, theme 1.2.80** (`inc/ux2-chrome.php`), preview only |
| 2 | Search and assistant: word search across trips, guides and places; grouped results; an empty state with a way on; assistant answers from the same search, safety → team chat | `v4-tourist2.py` §1–2, `chrome2.py` | **Live, theme 1.2.81**, preview only |
| 3 | Trips & tours archive: page title, place filter row with the 7 places, length / private / pickup filters, short place banners, a guide row when a place has one or two trips | `v4-round6.py`, `v4-round7.py`, `v4-tourist.py` §4 | **Live, theme 1.2.82**, preview only |
| 4 | Trip page: one length everywhere, a reason for "Our pick", a comparison that shows real differences, "Read before you go" guides, Book bar alignment | `v4-tourist.py` §1–2, `v4-cruise.py` (comparison rows), `v4-tourist3.py` | |
| 5 | Guides: "Trips in this guide" versus "route", no repeated card photos, a compact sticky trip card | `v4-tourist2.py` §6–7, `guides-fix.py`, `v4-tourist.py` §5 | |
| 6 | Trip Builder: interests rank trips, "Private or shared?", dropped-places notice, trip cards, save plan | `v4-planner.py` | |
| 7 | Homepage: "Where in Egypt?" with the 7 places, shorter page | `v4-round7.py`, `v4-tourist.py` §6 | |

**Needs the owner's decision, not just code:**
- **Nile cruise trip:** 3 offers in Core plus `offer-facts.json` and `offers-i18n.json` (`docs/NILE-CRUISE-OFFERS-2026-10-17.md`).
- **Wikimedia Commons photos** on the live site (`docs/prototypes/journey-v4/wpick.json`).
- **The prototype's videos:** Pexels, prototype only so far.

## Slice 1: done locally, 17 Oct
- **Files:**
  - `inc/ux2-chrome.php` (new);
  - `header.php` (the Ask button);
  - `footer.php` (menu search, Trip Builder link, search placeholder);
  - `functions.php`;
  - `assets/css/pages.css` (WordPress-only CSS; the approved CSS is untouched), with the CSS bundles rebuilt;
  - `tools/i18n/new-strings.json`, with 10 strings in 8 languages and the `.l10n.php` files rebuilt.
- **Labels:** changed by a `wp_nav_menu_objects` filter only when `er_ux2()` is on. The menus in the database are untouched.
- **Checked locally:**
  - without `?ux=2`, the header, menu and floating assistant are unchanged;
  - with `?ux=2`, Ask opens the assistant and the menu's search opens search;
  - Arabic is right to left;
  - phone and desktop screenshots were looked at.
- **Not done in this slice:** the Trips & tours page still has the heading "Experiences" (slice 3).
- **Visitors:** without `?ux=2`, 8 pages give the same HTML as 1.2.79. The only differences are the build stamp, the CSS version and the per-request form token.
- **"Trip Builder":** uses the approved translations (e.g. Arabic "مخطِّط الرحلة"), not new ones.
- **`tools/qa/page-coverage.mjs`:** run locally across 256 pages in 8 languages. It reports 1,722 problems, all from the local database: it has 5 offers, among them a test "Giza sunrise acceptance offer", so pages have no booking box. These checks don't involve the header or menu, and the visitor HTML is unchanged, so this slice did not cause them. No JavaScript errors were reported.
- **Production, 17 Oct:** pushed and deployed (1.2.80), GoDaddy cache flushed. `page-coverage.mjs` on production: 336 pages in 8 languages, all ok. Live checks: visitors unchanged; with `?ux=2` Ask opens the assistant and the menu search works; Arabic is right to left.

## Slice 2: done locally, 17 Oct (theme 1.2.81)
- **Search (preview):**
  - covers every published trip, place and guide in the page's language, not just the homepage's featured lists;
  - matches by the start of words;
  - groups results;
  - shows an empty state with a way on.
- **Tested locally on a phone and a computer:**
  - nile cruise, snorkeling, valley of the kings, siwa and camel ride xyz, plus Arabic "الأقصر";
  - Enter opens the first result;
  - the assistant answers "Is Egypt safe? 8 days first time" with the safety note and the team chat, the 7- and 10-day guides, and the Trip Builder link.
- **Visitors:** without `?ux=2` the HTML of 8 pages is unchanged, and the old search behaves as before.
- **Not in this slice:**
  - assistant answers with photos (the Core endpoint returns none);
  - the menu re-open bug in `nav.js` (it is on the live site too).
- **Production, 17 Oct:** 1.2.81 deployed and the GoDaddy cache flushed.
  - The live test passed on a phone and a computer, in English and Arabic.
  - Visitors still get the old search.
  - `page-coverage.mjs`: 336 pages in 8 languages, all ok.

## Slice 3: done locally, 17 Oct (theme 1.2.82)
- **Done:**
  - title "Trips & tours";
  - place buttons;
  - length, private and pickup filters from partner facts;
  - a result count;
  - an empty state.
- **Tested locally:** desktop, phone and Arabic, all with the right counts and active buttons.
- **Two phone bugs found and fixed before commit:**
  - the button rows had no height (`flex: 1 1 0` in a column);
  - the labels took 4.5rem of height.
- **Private and pickup:** these read the partners' offers, which the local database mostly lacks. The live results to check after the push, worked out from the live trip pages:
  - "Up to 5 hours": 4 trips (Ras Mohammed, Pyramids, Nile dinner, food tour);
  - plus "Private option": 2 (Pyramids, Nile dinner);
  - "2+ days": 2 (White Desert, Siwa).
- **Noticed:** Siwa's trip line says "Full day", but both of its options are 3 days, so in the preview it is listed under "2+ days". The trip's length line needs a content fix; that is for the owner (slice 4 shows one length per trip).
- **Not in this slice:**
  - the prototype's per-place banners and guide rows (the cards stay in one grid);
  - the swipe-row hint.
- **Production, 17 Oct:** 1.2.82 deployed and the GoDaddy cache flushed.
  - **Live results, which follow the partners' stated facts:**
    - Up to 5 hours: 5 trips. Valley of the Kings is included through its 4-hour private GetYourGuide option; the expected list above missed that option.
    - Up to 5 hours + Private option: 3 trips (Pyramids, Nile dinner, Valley).
    - 2+ days: 2 trips (White Desert, Siwa).
    - Private option: 6 trips.
    - Full day: 6 trips.
  - Visitors still see "Experiences" and the old dropdown.
  - `page-coverage.mjs`: 336 pages in 8 languages, all ok.
