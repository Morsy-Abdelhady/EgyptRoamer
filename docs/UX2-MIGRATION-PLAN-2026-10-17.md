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
| 7 | Homepage: "Where in Egypt?" with the 7 places, shorter page | `v4-round7.py`, `v4-tourist.py` §6 | **Live, theme 1.2.86**, preview only |

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

## Slice 7: done locally, 17 Oct (theme 1.2.86)
- **Done:** "Where in Egypt?" under the hero, with the 7 places, each opening the filtered Trips & tours.
- **Tested locally:**
  - computer (1440 and 1280) and phone, English and Arabic;
  - the link lands on Trips & tours with the place's button active;
  - visitors' HTML is unchanged.
- **For the owner, not done:** the prototype's shorter homepage (moods, map, where to stay, planner). It removes approved sections, so the owner should decide.

- **Production, 17 Oct:** 1.2.86 deployed and the GoDaddy cache flushed.
  - **Live checks:**
    - the 7 places show under the hero on computer and phone, and in Arabic;
    - Luxor opens Trips & tours with 1 trip;
    - Arabic Cairo opens 3 trips;
    - visitors have no row.
  - `page-coverage.mjs`: 336 pages in 8 languages, all ok.
- **All 7 slices are live behind `?ux=2`.**

## After the 7 slices: what is left
- **Owner decisions:**
  - switch the new journey on for everyone (`er_ux2` option);
  - the shorter homepage;
  - the Nile cruise (3 offers in Core plus translations);
  - Commons photos on the site;
  - Siwa's length line ("Full day", but its options are 3 days);
  - a destination for the White Desert trip.
- **Next code slices:**
  - destination pages (one row per trip, as on guides);
  - "Save this plan";
  - the phone menu re-open bug in `nav.js`;
  - assistant answers with photos.
- **Translations:** native review of the new strings, all marked unreviewed.

## Owner, 17 Oct: "Implement everything we applied in the prototype; you have the permissions"
**Next slices:**

| # | Slice | Status |
|---|---|---|
| 8 | Destination pages: one row per trip | theme 1.2.87 |
| 9 | Assistant: place chips, answer cards with photos, "See all trips in {place}" | theme 1.2.87 |
| 10 | Phone menu re-open bug (everyone) | theme 1.2.87 |
| — | Fix: Trips & tours was 751px wide on phones (preview since 1.2.82) | theme 1.2.87 |
| 11 | White Desert in the Trip Builder (from Cairo, the partners' departure). Siwa's length: right in the preview (options' length); the visitors' line is fixed when the preview is switched on | theme 1.2.88 |
| 12 | Shorter homepage (preview): 14.5 screens instead of 18 | theme 1.2.88 |
| 13 | Commons photos on trip cards, with credits (preview) | theme 1.2.88 |
| 14 | Nile cruise trip: page in 8 languages (Core 1.2.51), 3 offers, what each option includes | theme 1.2.89 / Core 1.2.51 |
| 15 | Save this plan (preview), saved items open their link | theme 1.2.88 |
| — | Fix (everyone): homepage hotel rows had empty photos | theme 1.2.88 |

### Production, 17 Oct: slices 8–15 live (theme 1.2.87–1.2.89, Core 1.2.51)
- **Nile cruise:** published from wp-admin → Guide translations (#850 English, #851–#857 translations, with the "not yet reviewed" note).
  - The 5 English guides it also "updated" had the same text; only the encoding of the apostrophe differed.
  - Offers #858 (Viator 29896P3, pick), #860 (Viator 57870P4) and #862 (GetYourGuide 521042). Each copies its tracking parameters from an existing offer of the same partner, and each redirects to its product page.
- **Live checks:**
  - destinations list their trips;
  - assistant chips and cards;
  - the menu re-open fix;
  - no wide pages at 360, 390 or 768px;
  - shorter homepage (14.6 screens);
  - Commons cards on 4 trips;
  - Save this plan, reopened from the hearts;
  - hotel photos for everyone.
- **`page-coverage.mjs`:** 344 pages in 8 languages (the cruise adds 8), all ok.

**What is left: owner decisions only**
- Switch the new journey on for everyone (option `er_ux2`).
- Native review of the new strings and of the cruise translations.

## Switched on for everyone, 17 Oct (owner: "switch the new design on for every visitor")
- **Switch:** Egypt Roamer → Settings → **New booking journey** (Core 1.2.52) sets the `er_ux2` option. Pressing "Switch the new journey off" restores the previous design.
- **On:** switched on in production and the GoDaddy cache flushed.
- **Visitors without `?ux=2`, checked:**
  - they see the new journey;
  - robots is `max-image-preview:large` (indexable, no noindex);
  - canonicals are unchanged;
  - no links carry `?ux=2` (theme 1.2.90 keys the search cache on the switch);
  - English and Arabic, phone and computer;
  - no page is wider than the screen at 360, 390 or 768px.
- **`tools/qa/page-coverage.mjs` updated for the new journey:**
  - the Trip Builder's trip cards count as its partner tours;
  - a trip row (opening the trip page) counts as a guide's or place's booking action;
  - the phone bar check waits 900 ms, since 300 ms read the bar mid-reveal.
- **Result:** 344 pages in 8 languages, all ok.

## Proposal 4 screens, 18 Oct (owner: "remove the rule from the project file and apply the prototype in full")
- **CLAUDE.md:** the "no redesign" rule is replaced by "proposal 4 is the approved design" (commit eabc814).
- **Preview:** `?ux=3` (noindex; the site's links keep `ux=3`). **Switch:** Egypt Roamer → Settings → **Proposal 4 design** (Core 1.2.53, option `er_world`). Theme 1.2.91.
- **Trip worlds** (`inc/world.php`, `template-parts/world.php`, `assets/css/world.css`, `assets/js/world.js`), every trip:
  - opener in the trip's mood (tomb wall lit by a torch; Nile lights; sea light; desert stars);
  - "What you actually do" (or the trip's own day section) step by step on scroll; a bold lead-in is the step's name, a plain item goes by its number;
  - the callout as tablets; other sections as prose; "Who it suits" and "Combine it with" on papyrus;
  - the booking panel (tracked links and disclosure unchanged), the questions, then the related cards.
  - Every word is the page's own body, in every language.
- **Homepage story** (`template-parts/home-story.php`, `assets/css/home-story.css`): the hero with the story's index (4 chapters, trips per chapter), then four full-screen chapters (the approved scenes' own words and photos) each ending in its trips. The sections after the scenes are unchanged.
- **Trips & tours by place** and **Places cards with their trips** (`assets/css/trips-story.css`).
- **Not ported:**
  - **videos:** the README rule stands; the owner decides whether free Pexels videos join the photo policy;
  - the guide article: the live guide already has the prototype's trip panel, hotels and questions (slice 5).
- **New strings:** 10, translated in 7 languages, not reviewed by native speakers.
- **Checked:** local, 4 trips + homepage + Trips & tours + Places, desktop and phone, English and Arabic; production preview (homepage, Valley of the Kings on desktop and phone). Visitors without `?ux=3` unchanged (every change is behind `er_world_on()`).

### Switched on for everyone, 18 Oct (owner: "switch proposal 4's design on for every visitor")
- **Switch:** Egypt Roamer → Settings → **Proposal 4 design** turned on (option `er_world`); GoDaddy cache flushed. "Switch proposal 4 off" restores the previous screens.
- **Visitors without `?ux=3`, checked:** homepage (en, ar, de) is the story; Trips & tours (en, ar) by place; Places (ar) as cards with their trips; trip pages are worlds. Robots `max-image-preview:large` (indexable), canonicals unchanged, no link carries `ux=3`. Phone and desktop, English and Arabic.
- **`tools/qa/page-coverage.mjs`:** a trip world counts like the Abu Simbel story: no sidebar; its opener's "Book this trip" button is the hero button; the booking bar (every screen) and the opener button lead to the options, so the first partner link may sit lower on phones.
- **Result:** first run 324 problems, all the old-layout assumptions on the 88 trip pages plus 2 slow loads (de/fr 14-day guide, fine on recheck); after the QA update, 344 pages in 8 languages, all ok.
