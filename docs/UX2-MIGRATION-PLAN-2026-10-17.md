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
| 4 | Trip page: one length everywhere, a reason for "Our pick", a comparison that shows real differences, "Read before you go" guides, Book bar alignment | `v4-tourist.py` §1–2, `v4-cruise.py` (comparison rows), `v4-tourist3.py` | **Live, theme 1.2.83**, preview only |
| 5 | Guides: "Trips in this guide" versus "route", no repeated card photos, a compact sticky trip card | `v4-tourist2.py` §6–7, `guides-fix.py`, `v4-tourist.py` §5 | **Live, theme 1.2.84**, preview only |
| 6 | Trip Builder: interests rank trips, "Private or shared?", dropped-places notice, trip cards, save plan | `v4-planner.py` | **Live, theme 1.2.85**, preview only |
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

## Slice 4: done locally, 17 Oct (theme 1.2.83)
- **Done:**
  - one length per trip, from the partners' facts, in the hero and the booking box;
  - lengths in days in the booking box;
  - a reason for "Our pick" from the facts;
  - the label "Trips & tours" above the title.
- **To check live after the push:**
  - Valley: hero "About 4–10 hours", pick reason "Private, the longest visit";
  - Siwa: hero and box "About 3 days";
  - White Desert: "About 2 days".
- **Not in this slice:**
  - the comparison table's extra rows (Abu Simbel, balloon, fees), which only matter for the Nile cruise, and the cruise is not on the site yet;
  - "Read before you go", which the live "Plan it with our guides" already covers.
- **For the owner (content):** Siwa's own length line says "Full day", but both options are 3 days. The preview now shows the options' length; the page's line should be corrected.
- **Production, 17 Oct:** 1.2.83 deployed and the GoDaddy cache flushed.
  - **Live checks:**
    - Valley: hero and box "About 4–10 hours", pick "Private, the longest visit" (computer and phone).
    - Siwa: "About 3 days".
    - White Desert: "About 2 days".
    - Abu Simbel: "About 8–10 hours", no reason.
    - Red Sea: the page's own "1 day · 2 dives", because its options mix hours and days.
    - Label: "Trips & tours".
  - Visitors still see "Experiences" and "6 hours".
  - `page-coverage.mjs`: 336 pages in 8 languages, all ok.

## Slice 5: done locally, 17 Oct (theme 1.2.84)
- **Done:**
  - one photo per guide, with no repeats on the index, and the guide hero matching its card;
  - guides covering several trips list the trips, each opening its page.
- **Tested locally:** the index has 0 repeated photos in the preview (visitors still have their 1); the 7-day guide lists 3 trips on desktop and phone.
- **To check live after the push:** the panels of the guides covering several trips (local data has no offers for most of them).
- **Not in this slice:**
  - "route" versus "trips in this guide" wording (the live panel already says "Trips in this guide" for every guide);
  - a compact sticky trip card.
- **Same issue elsewhere:** destination pages mix several trips' options in one list. That is for the destination slice, after this plan.
- **Production, 17 Oct:** 1.2.84 deployed and the GoDaddy cache flushed.
  - **Index photos:** in the preview, 0 repeated photos on both index pages; visitors still see their 3 repeats.
  - **Guides covering several trips:**
    - Best time: 2 trips (Abu Simbel "About 8–10 hours", White Desert "About 2 days");
    - 7 days: 3 trips;
    - 14 days: 5 trips.
    - Each trip opens its trip page.
  - **Guides about one trip:** Valley and Inside the Pyramids keep their options and hotels.
  - `page-coverage.mjs`: 336 pages in 8 languages, all ok.

## Slice 6: done locally, 17 Oct (theme 1.2.85)
- **Done:**
  - interests rank the trips and mark them;
  - "Private or shared?" replaces the travel style;
  - dropped places are named, with "Add the missing nights";
  - the result is trip cards opening the trip pages;
  - suggestions for interests with nothing on the route.
- **Tested locally on desktop and phone:**
  - default;
  - 3 nights with 4 places (Hurghada dropped, then added back);
  - Cairo with food and beach (Sharm and Hurghada suggested, then Sharm added);
  - Private only (locally empty, since the local data has no offers; check live).
- **Not in this slice:** "Save this plan" (the prototype's ♡ list is not on the site yet).
- **Note:** White Desert has no destination in Core, so it never appears on a route. Linking it to a place is a content decision for the owner.
- **Production, 17 Oct:** 1.2.85 deployed and the GoDaddy cache flushed.
  - **Live checks:**
    - "Private only" in Cairo keeps the Pyramids and the Nile dinner and hides the food tour;
    - dropped Hurghada is named, and "+1 night" adds it back;
    - interests rank the trips;
    - visitors still see "Travel style" and the partner list.
  - **`page-coverage.mjs`:** the first run stopped on a page that navigated while it was being read (a script error, not a page failure); the rerun passed: 336 pages in 8 languages, all ok.
