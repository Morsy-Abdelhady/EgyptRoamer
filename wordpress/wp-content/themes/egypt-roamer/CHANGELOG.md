# Egypt Roamer theme — changelog

Versions match `Version:` in `style.css` and `ER_THEME_VERSION` in `functions.php`. CI fails if they differ.

## 1.2.89 — 2026-10-17: proposal 4, slice 14: the Nile cruise trip (with Core 1.2.51)
- **New trip page,** "Nile Cruise from Aswan to Luxor" (`exp-cruise`), in 8 languages.
  - Written from the sourced cruise guide and the three partner pages (`docs/CONTENT-SOURCES.md`).
  - Translations are marked pending: they open with the "not yet reviewed" note.
  - Published from wp-admin → Egypt Roamer → Guide translations, and linked to Aswan and Luxor.
- **Three offers, facts read on 17 Oct:**
  - Viator 29896P3 (Abu Simbel and a balloon flight included, entrance fees extra, Our pick);
  - Viator 57870P4 (private guide, entrance fees included, Abu Simbel and the balloon extra);
  - GetYourGuide 521042 (Abu Simbel and the balloon included, entrance fees paid locally).
  - Each offer's card text is in all 8 languages.
- **Theme (preview):** an option's row lists what it includes (offer-facts `includes`), since the cruise options differ on exactly that.
  - The trip is in the Trip Builder (Ancient, Luxury).
  - 7 new strings in all 8 languages, unreviewed.
- **Visitors:** the theme change is preview-only. The new page and offers are new content for everyone.

## 1.2.88 — 2026-10-17: proposal 4, slices 11–13 and 15: shorter homepage, Commons card photos, Save this plan, hotel photos
- **Fix, hotel photos on the homepage (everyone):** the "Find a place worth staying for" rows showed empty boxes, because hotel offers have no seed photo. They now use the area photo from offer-facts, or else the approved photo of their place (`er_offer_stock_fallback()`).
- **Slice 12, a shorter homepage (preview):** 18 screens on a computer and 20 on a phone became 14.5 and 15, as in the prototype. Left out: the quote interlude, the destinations cards (the map lists every place, and the menu has Places) and the Journal (the Travel Guide is in the menu, and each trip and place links its guides). Kept: hero, trips, moods, map, where to stay and the Trip Builder.
- **Slice 13, Commons photos on trip cards (preview):** cards that had no photo get one from Wikimedia Commons, showing the exact place or dish under a free licence. Each is credited under the photo, linking to its Commons page.
  - Food tour: koshari, ful, hawawshi, feteer.
  - Valley: the Colossi of Memnon.
  - Alexandria: Kom el-Shoqafa, Montaza.
  - White Desert: Black Desert, Crystal Mountain, White Desert.
  - The photos are hot-linked from Commons; nothing is copied into the Media Library.
- **Slice 15, Save this plan (preview):** the Trip Builder's result ends with a heart button. The plan joins the saved list, and its link reopens the builder with the same places, nights and interests. Saved items with a link now open it (for everyone, though only plans have one).
- **Slice 11, the White Desert (preview):** the trip has no place, so it never showed in the Trip Builder. Both partner options leave from Cairo ("2 days from Cairo", "Cairo: 2-Day…"), so it now shows on routes through Cairo as "From Cairo". No place is invented for it.
- **New strings:** 4, in all 8 languages, unreviewed.
- **Visitors:** the HTML of 7 pages is unchanged apart from the hotel photo fix in the homepage data. No preview page is wider than the screen (360, 390 and 768px, English and Arabic).

## 1.2.87 — 2026-10-17: proposal 4, slices 8–10: destinations, the assistant, a menu fix, and a phone overflow fix
The owner asked for everything applied in the prototype, with full permissions. These slices are behind `?ux=2` except where marked "everyone".
- **Slice 8, destinations (preview):** a place page listed up to 4 partner options of different trips, all with gold buttons. As on guides, it now lists the place's trips (photo, length, options, "See options" to the trip page), then its hotels. The "opens the partner's site" line is dropped when trips are listed; the hotel buttons keep their ↗.
- **Slice 9, the assistant (preview):**
  - its first suggestions are the 7 places, as one swipeable row;
  - answers are cards with the page's photo and what it is (trip, place, guide);
  - a question naming a place ends with "See all trips in {place}", opening the filtered Trips & tours.
- **Slice 10, the phone menu (everyone):** reopening the menu within 0.8 s of closing it hid it while open. The delayed `hidden` now runs only if the menu is still closed.
- **Fix, Trips & tours on phones (preview, live since 1.2.82):** the filter rows grew to their swipe rows' full width, so the page was 751px wide and the menu button sat off-screen. The rows now keep the screen's width. Every preview page type was measured at 360, 390 and 768px, in English and Arabic: none is wider than the screen.
- **New strings:** 1 ("See all trips in {place}"), in all 8 languages, unreviewed.
- **Visitors:** the HTML of 6 pages is unchanged. The only visitor-facing change is the menu fix in `nav.js`.

## 1.2.86 — 2026-10-17: proposal 4, slice 7: the homepage (preview `?ux=2` only)
- **"Where in Egypt?"** under the hero's buttons: the hero offered only "Explore Egypt" (the scenes) and the film, so a tourist who already knew where they were going had no way in. The places are now listed there, and each opens Trips & tours filtered to that place.
  - Computers: one row of buttons. Phones: one swipeable row, above the bottom bar.
  - Arabic: right to left, with no letter-spacing on the label.
- **Not changed:**
  - the pinned scenes and the order of sections (approved in B1, 1.2.79);
  - the page length: the prototype's shorter homepage drops approved sections, which needs the owner's decision.
- **New strings:** 1, in all 8 languages, unreviewed.
- **Visitors:** without `?ux=2` the homepage HTML in English, Arabic, German and Chinese, plus trips and guides, is unchanged.

## 1.2.85 — 2026-10-17: proposal 4, slice 6: the Trip Builder (preview `?ux=2` only)
What a test of the homepage builder found, and what the preview does now:
- **"What moves you?" changed nothing.** The interests now rank the trips on the route, and each trip shows the interests it matches. When an interest has nothing on the route, the builder offers the places whose trips have it ("+ Hurghada"). The grouping is editorial, by each trip's own subject (`ER_UX2_TRIP_INTERESTS`). Luxury maps to no trip, so it suggests nothing rather than something untrue.
- **"Travel style" (Smart / Comfort / Luxury) changed nothing,** because there are no prices behind it. It became "Private or shared?" (Either / Private only), which the partners' facts can answer. When the filter hides trips, the builder says how many.
- **Places were silently dropped.** With fewer nights than places, the builder now says which place was left out and offers "Add the missing nights (+n)".
- **The result was a list of partner offer names,** the same whatever the interests. It is now one card per trip on the route (photo, places, the length the partners state, number of options, private option, matching interests), opening the trip page where the options are compared.
- **Code:** `src/js/components/planner.js` (the preview branch reads `#ux2-planner`), plus the trip data in `inc/ux2-trips.php` (front page in the preview only).
- **New strings:** 11, in all 8 languages, unreviewed.
- **Visitors:** without `?ux=2` the HTML of 7 pages (home in English, Arabic and German, trips, a trip, a guide, a destination) is unchanged, and the builder keeps "Travel style" and the partner list.

## 1.2.84 — 2026-10-17: proposal 4, slice 5: the guides (preview `?ux=2` only)
- **No repeated photos:** the Travel Guide index showed the same photo on neighbouring guides (Coptic Cairo and Inside the Pyramids, Saqqara and the Grand Egyptian Museum, three Valley of the Kings photos). Each of the 24 guides now has its own photo, of its own place, and the guide's hero uses the same one.
  - 19 of these photos are already approved and used in Core data.
  - 5 are free Unsplash photos whose location tag names the place, chosen in the prototype and recorded in `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5.
  - A featured image set by an editor still wins.
- **A guide covering several trips** (the 7-, 10- and 14-day itineraries, best time, hidden gems, day trips) listed up to 4 partner options of different trips, each with its own gold "Book", so a reader couldn't tell which option went with which trip. The panel now lists the trips: photo, name, the length the partners state, the number of options, and "See options" to the trip page, where the options are compared.
  - The line "Each button opens the partner's site" is dropped there, since these buttons open trip pages.
  - On phones the button is an arrow, so the trip names keep the width.
  - A guide about one trip keeps its options and "Our pick".
- **Code:** `inc/ux2-guides.php` (new). No new strings.
- **Visitors:** without `?ux=2` the HTML of 11 pages (guides index, page 2, 3 guides, trips, a trip, a destination, Arabic and German) is unchanged.

## 1.2.83 — 2026-10-17: proposal 4, slice 4: the trip page (preview `?ux=2` only)
- **One length per trip:**
  - The hero's length came from the page's own line ("6 hours", Siwa "Full day"), while the booking box said "About 4–10 hours" and Siwa's options are both 3 days. Both now show the length the partners state for the trip's options (`er_ux2_offer_length()`).
  - When options mix hours and days (Red Sea dives), the page's own line stays, since no single honest line exists.
- **Trips measured in days:** White Desert and Siwa now show their length in the booking box ("About 2 days", "About 3 days"); before, only hour ranges were shown.
- **"Our pick":** when no fact belongs to the pick alone, it now says "Private, the longest visit" or "The longest visit", from the stated facts. On Valley of the Kings: "Private, the longest visit". Where nothing sets the pick apart (Abu Simbel), no reason is invented.
- **Label above the title:** "Trips & tours" instead of "Experiences".
- **Already live, nothing changed:** "Plan it with our guides" at the end of trip pages, which the prototype's "Read before you go" needed.
- **Tests:**
  - lengths and reasons were checked against the real `offer-facts.json` with a standalone PHP test, because the local database has only test offers;
  - visitors: without `?ux=2` the HTML of 10 pages (3 trip pages among them) is unchanged.
- **New strings:** 2, in all 8 languages, unreviewed.

## 1.2.82 — 2026-10-17: proposal 4, slice 3: the Trips & tours page (preview `?ux=2` only)
- **Title:** the page and its breadcrumb say "Trips & tours", as the menu does (it was "Experiences"). Destinations are "Places".
- **Filters:** a dropdown plus an Apply button became link buttons, so each choice is one tap and works without JavaScript:
  - **Where:** All, then the 7 places;
  - **Length:** Any length, Up to 5 hours, Full day, 2+ days;
  - **Comfort:** Private option and Hotel pickup.
- **Where the facts come from:** length, private and pickup are read only from the partners' stated facts (Core `offer-facts.json`), never guessed. A trip matches a length when any of its options does. The trip's length line is used only when no option states a duration.
- **Results:** a line says how many trips are shown. When nothing matches, the page says so and offers "See all trips".
- **Phones:** each filter is one swipeable row, so the trips start high on the screen.
- **Code:** `inc/ux2-trips.php` (new). In the preview, the archive lists every trip, so the filters see them all.
- **New strings:** 10, in all 8 languages, unreviewed.
- **Visitors:** without `?ux=2` the HTML of 10 pages is unchanged, including the trips page and its old filter.

## 1.2.81 — 2026-10-17: proposal 4, slice 2: search and assistant (preview `?ux=2` only)
- **Search knew only part of the site.** It covered the homepage's featured lists: 8 trips and 7 guides. "Valley of the Kings" or "Siwa" could not be found, and "nile cruise" or "snorkeling" found nothing. In the preview it searches every published trip, place and guide in the page's language, matched by the start of words, grouped as Trips & tours, Places and Travel Guide. With nothing typed it lists the trips. When nothing matches it says so and offers All trips, the Travel Guide and the Trip Builder. The index comes from `inc/ux2-chrome.php`, cached per language and rebuilt whenever a page is saved.
- **Assistant:**
  - a question about safety gets an honest note: we have no page on safety, and a button opens the team chat;
  - a number of days gets "Draft a route for {n} days in the Trip Builder";
  - the answer opens at its question, so that note is read first.
- **New strings:** 4, in all 8 languages, unreviewed.
- **Visitors:** checked locally. Without `?ux=2` the HTML of the same 8 pages is unchanged; the only differences are the build stamp, asset versions and the per-request form token. The search and assistant scripts behave as before when the preview data is absent.
- **Noticed, not changed:** the phone menu hides itself if it is reopened within 0.8 s of closing (the delayed `hidden` in `nav.js`). The live site has this too; it is listed for a later fix.

## 1.2.80 — 2026-10-17: proposal 4, slice 1: header and menu (preview `?ux=2` only)
- **Owner:** move the proposal 4 fixes into the theme behind `?ux=2`. Plan: `docs/UX2-MIGRATION-PLAN-2026-10-17.md`.
- **Labels:** "Places" and "Trips & tours" replace "Destinations" and "Experiences" in the header, the phone menu and the footer. They are changed by a menu filter while `er_ux2()` is on; the menus in the database are untouched.
- **Phone menu:**
  - search comes first;
  - each section has a line saying what it holds;
  - a "Trip Builder" link (approved translation) with a line saying what it does;
  - the menu button becomes ✕ while the menu is open.
- **Assistant:** an "Ask" button in the header, at every size, replaces the floating "Trip assistant", which covered trip cards, booking options and footer links. Below 380px it shows the icon only.
- **Code:** `inc/ux2-chrome.php` (new). WordPress-only CSS goes in `pages.css`; the approved CSS is untouched.
- **New strings:** 9, in all 8 languages, unreviewed.
- **Visitors:** checked locally. Without `?ux=2` the HTML of 8 pages (home, trips, a trip, a destination, a guide, the guides index, Arabic home, German trips) is the same as 1.2.79. The only differences are the build stamp, the CSS version and the per-request form token.

## 1.2.79 — 2026-10-16: B1, homepage trips right after the scenes (preview `?ux=2` only)
- **Owner:** approved B1. Move the "Handpicked experiences" rail to right after the pinned scenes, in the preview only.
- **Order in the preview:** scenes, experiences, then the quote, moods, destinations, map, partners, journal and planner. Only the order changes: sections, approved CSS and animation are as they were. The "Explore Egypt" button (`#journey-start`) now lands on the trips.
- **Measured locally:** the first trip card moves from screen 12.0 to 6.1 on phones and from 12.8 to 6.8 on desktop, in English and Arabic. No JavaScript errors, no horizontal overflow.
- **Visitors:** without `?ux=2` the homepage HTML is byte-for-byte the same as 1.2.78 in all 8 languages.

## 1.2.78 — 2026-10-16: step A, one booking panel on every page (preview `?ux=2` only)
- **Owner:** approved step A of the second review: one booking panel on every page, then B1 for the homepage. Abu Simbel should be like the other trips. Nothing may change for visitors: everything stays behind `?ux=2`.
- **Destinations:** the trip page's panel, titled "Trips and hotels in {name}", with the destination's trips (up to 4) and hotels (up to 4), plus its best time. It replaces the sidebar box, the in-article trip and hotel boxes and the box after the article. No "Our pick": the trips differ, so a pick between them says nothing. Hero button "Choose a trip" opens the panel (the sheet on phones).
- **Guides:** the same panel, "Trips in this guide", with the trips the guide covers, and hotels when it is about one destination. "Our pick" only when the guide covers one trip.
- **Abu Simbel:** in the preview it uses the trip template, like every other trip (panel, facts, one phone bar), instead of the story layout. Without `?ux=2` the story is unchanged.
- **Phones:** one fixed bar on every page with the panel (P7 extended to destinations and guides).
- **Visitors:** checked locally. Without `?ux=2` the HTML of 12 pages (destinations, guides, trips, Arabic, home) is byte-for-byte the same as 1.2.77.
- 2 new UI strings in all 8 languages.

## 1.2.77 — 2026-10-16: homepage trip cards follow P4 (preview `?ux=2`)
- Found while checking 1.2.76 in a browser: the homepage rail is rebuilt by its script from the page data (`er_payload_experience()`), which still gave each card its partner link. In the preview the data now gives "See options" to the trip page and no partner link. The script-built cards show title, place and duration, but not the shared facts that the server-built cards show.

## 1.2.76 — 2026-10-16: proposals 4, 5 and 7 of the journey review (preview `?ux=2`)
- **Owner:** decide by the established practice of tour-booking sites, not visitor data (the site is still being indexed).
- **P4, trip cards** (experience archive, homepage, related cards): the card opens the trip page and says what its options offer: the number of options and the partner-stated facts they share ("Private option available", "Pickup offered"), beside the duration. The card has no button to the partner any more; the hand-off happens on the trip page, after the options are compared, as on tour marketplaces. With no partner link on the card, the card grid shows no disclosure.
- **P5, one label set:** "Book this trip" on a trip page with offers; "Choose a trip" everywhere else (header, menu, dock). A page with no booking box links to the trips, never to the homepage planner. Fixed on the way: on the Experiences archive the header said "Book this trip", because the check read the first post in the loop; it now uses `is_singular()`.
- **P7, phones:** a trip page has one fixed bar ("Book this trip"). The dock steps aside there, and the bar and the assistant button take its place at the bottom. Other pages keep the dock.
- **Preview:** while `er_ux2` is off, `?ux=2` stays on the site's own links, so the whole journey can be walked in the preview, and preview pages are `noindex, nofollow`.
- 4 new UI strings in all 8 languages.

## 1.2.75 — 2026-10-16: the panel really comes first on phones
- 1.2.74's rule lost to the older "everything else after the article" rule (higher specificity), so on phones the panel still followed the article (measured at 3.3–4.7 screens on every experience). The rule is now more specific.

## 1.2.74 — 2026-10-16: trip page, proposals 1–3 of the journey review (preview `?ux=2`)
- **Owner:** go ahead with the measured journey review (`docs/UX-JOURNEY-REVIEW-2026-10-16.md`), on judgement, since there is no traffic yet to test with.
- **P1, phones and tablets:** the booking panel comes right after the hero, before the article. In the preview it sat after the FAQ, 4.9 screens down on the Valley of the Kings.
- **P2, "Before you book":** under the panel title, the place, what the trip's options share and the page's best time. Read from the partner-stated facts only (`er_booking_summary()`): the range of the options' durations (in hours only), private or small group ("Private option available" when only some are), and pickup ("Pickup on some options" when only some have it).
- **P3, why our pick:** under "Our pick", a partner-stated fact that only the pick has ("The only private option", "The only small-group option", "The only option with pickup"; `er_pick_reason()`). With no such fact, no reason is shown.
- 8 new UI strings in all 8 languages.

## 1.2.73 (Core 1.2.50) — 2026-10-16: Viator offers can show their own trip photo
- **Owner:** each offer in the booking panel shows its own trip's photo from the partner. Viator supplies product photos only through its Partner API (docs/PARTNER-OPTIONS-2026-10-16.md §4).
- **Core `includes/viator.php`:** wp-admin → Egypt Roamer → **Viator photos**, where the owner pastes the API key (password field, never shown again; option with autoload off, or `ER_VIATOR_API_KEY`). For each live Viator offer the product code is read from its URL, `GET /partner/products/{code}` returns its images, and the cover photo's URL (Viator's CDN) is stored. Refreshed hourly: Viator's Technical Guide allows single-product responses to be cached for up to 1 hour. Only image URLs are used, never reviews or other content.
- **Theme:** `er_offer_thumb()` shows the Viator photo first; an offer with an explicit stock photo keeps it. With no key, or no photo yet, nothing changes.
- Fixed before release: the product-code regex used `#` as its delimiter while its class contained `#`. A slow API can no longer push the save past the PHP time limit (8 s per request, 180 s limit for the refresh).

## 1.2.72 (Core 1.2.49) — 2026-10-16: GetYourGuide offers get their own photo
- **Owner:** keep GetYourGuide offers in our design, with a free photo as close as possible to GYG's own (GYG's terms forbid copying its photos; it has no self-serve API).
- Each of the 9 GYG offers now shows a free Unsplash photo tagged with the exact place, matched to the subject of GYG's product photo: Ras Mohammed water, the Qaitbay harbour, a camel at the pyramids, Nile boats and the Cairo skyline, the Abu Simbel façade, a Hurghada reef with a diver, 4×4s at Bahariya, palms at sunset in Siwa, the Valley of the Kings' cliffs. The source of each is in `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5; `offer-facts.json` "stock".
- Viator offers keep the experience's photo until the Viator API key is in place.

## 1.2.72 — 2026-10-16: photos in the content cards (preview `?ux=2`)
- **Step 3 of the photo plan:** the cards that describe each place show its photo at the top (`er_card_photos()`, Core 1.2.48 `data/card-photos.json`, matched by section and card position in every language).
- **Placed:** the Valley of the Kings and Hatshepsut's temple (Valley of the Kings page), and the Citadel of Qaitbay, the Bibliotheca Alexandrina and Pompey's Pillar (Alexandria). All are free Unsplash photos tagged with the exact place.
- **No photo:** cards whose place has no exactly tagged free photo, such as the Colossi of Memnon and the desert sights (`docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5).

## 1.2.71 — 2026-10-16: photos back on the booking panel (preview `?ux=2`)
- **Owner:** the old design was better as UI because it showed the place, so the reader knew what to expect. Put the trip's photo back, the same photo on each partner's offer.
- **Trip rows:** the experience's photo on every offer, beside the partner chip and the partner-stated facts (`er_offer_thumb()`).
- **Hotel rows:** each area's own photo (Core 1.2.47 `offer-facts.json` "stock"): Giza (the pyramids), Luxor West Bank (Hatshepsut's temple), and three new free Unsplash photos tagged with the exact place (Zamalek, Downtown Cairo, El Gouna; `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5). Other areas use their destination's photo.
- **Type:** larger panel title, offer names and facts line.

## 1.2.70 — 2026-10-16: panel durations translated; no page duration in the panel
- Found while reviewing the Arabic preview on a phone: durations read "حوالي 7–10 hours". The duration strings were passed to `er_t()` through a variable, so `tools/i18n-strings.py` never collected them; they are now literal calls and translated in all 8 languages.
- The panel's first line keeps the place only. The page's own duration ("6 hours") sat above offers stating "About 7–10 hours" and read as a contradiction.

## 1.2.69 — 2026-10-16: booking panel, round 2 (preview `?ux=2`)
- **Owner:** apply all six proposed improvements.
- **Facts per offer instead of the repeated city.** Private or small group, duration ("About …" when the partner says approx.) and "Pickup offered", from Core 1.2.46 `data/offer-facts.json`. All 26 trip offers were read on Viator and GetYourGuide's own product pages on 2026-10-16; a fact the partner page doesn't state is left out.
- **Our pick.** One offer per experience where a stated fact sets it apart (private, with pickup): Valley of the Kings, Abu Simbel, the dinner cruise, the White Desert and Siwa. It comes first, is labelled, and has the only filled gold button; the others are gold outlines. Its facts line is the reason.
- **No separate key-facts box.** It only repeated the hero; duration and place now open the panel.
- **Phone sheet.** A grab handle and a head with the trip's name and a larger close button, kept in view while the sheet scrolls.
- **Hotels by area**, from the areas the destination guides recommend and Trip.com's own area pages (offers #842–#848): Downtown Cairo (Qasr El Nil), Zamalek, Luxor's West Bank (Al Bairat), El Gouna, beside Giza and the city lists. Up to four per panel.
- **Partner chips in the site's own colours** (teal, clay, Nile blue), not the partners' brand colours.

## 1.2.68 — 2026-10-16: booking journey redesign, step 1 (experience page), preview only
- **Owner request:** one coherent journey from the homepage to booking a trip or a hotel, built on a review of the site as a concept (no traffic data yet). Proposal: artifact "رحلة الحجز في Egypt Roamer".
- **Preview:** add `?ux=2` to an experience URL. Visitors see the current page until the owner approves; then the `er_ux2` option turns it on for everyone.
- **The page in the new journey:**
  - One booking panel, "Book this trip" (`er_booking_panel()`): the trip's partner offers, then "Staying in {city}?" with the destination's hotels. It replaces the sidebar box, the in-article box on narrow screens and the separate hotel box.
  - Each row shows the partner's name as a coloured chip instead of the same repeated photo, so offers from two partners don't look like one, and has one gold button: "Book" for trips, "See hotels" for hotels. A line says each button opens the partner's site in a new tab.
  - The header, hero, menu, dock and bar all say "Book this trip" (`er_book_label()`).
  - On phones and narrow screens, "Book this trip" opens the panel as a sheet from the bottom of the screen, so the reader keeps their place in the article.
- New UI strings in all 8 languages (unreviewed, as the other template strings).

## 1.2.67 — 2026-10-16: wider sidebar; guides listed in every language
- **Owner report (1920px screen):** a wide empty gap between the article and the sidebar's white booking box, and the box too narrow to read well. The sidebar column now takes spare width up to 30rem (was a fixed 21rem pushed to the far edge); offer photos 64px.
- **Owner report:** the Arabic Abu Simbel page showed one card under "Combine it with" where the English showed three. Cause, in Core 1.2.45: guide translations keep their links to experiences and destinations on the English guide only, so every translated page that lists the guides pointing at it ("Plan it with our guides", "Plan your trip to …", the story's cards) found none. `er_get_referencing()` now matches the relation in any language and shows each guide's translation in the page's language; a guide without one is left out, never shown in English.

## 1.2.66 — 2026-10-16: sidebar offer button no longer cut off
- 1.2.65 put the sidebar button and "via …" on one line that could not wrap: with "via GetYourGuide" the button was pushed out of the box ("eck Availability"). The line now wraps, and the button never shrinks.

## 1.2.65 — 2026-10-16: hotels on experience pages; the booking bar no longer covers a button
- **Owner report:** none of the experience pages offered a hotel, and "many UX problems". Checked in the browser at 1366×768 and on a phone.
- **Hotels on experiences:** the hotel offers of the experience's destination ("Find a hotel in {name}") before the questions at the end of the article, at every width (placement `experience-hotels`). The Abu Simbel story shows them in its closing booking section, after the trips.
- **Desktop, sidebar:** the centred booking bar sat on the sidebar box's third "Check Availability" button. The bar now steps aside while the sidebar's booking box is on screen, and takes over after the article. The sidebar's offer rows are compact (smaller photo and title, the button beside "via …"), so three offers and the disclosure fit the pinned sidebar.
- **Phones:** the bar and the dock showed two identical gold "Check Availability" buttons, one above the other. While the bar shows, the dock's gold button gives way.
- **Footer:** the bar steps aside over the footer, where it covered the newsletter form.

## 1.2.64 — 2026-10-16: a hotel box on destination pages
- **Where:** at the end of the destination's "Where to stay" section, where the reader chooses an area. Destinations without that section get it after "How long, and when to go", else at the end of the article. It shows at every width.
- **What:** up to two live offers in the "Hotels" category for the destination (Core 1.2.44, `er_hotel_offers_for_post()`), labelled "Find a hotel in {name}" in all 8 languages, with the disclosure. Placement `destination-hotels`.
- Hotel offers no longer appear in the tour booking boxes (sidebar, inline, `#book`): `er_offers_for_post()` leaves the "Hotels" category out.
- **First partner:** Trip.com city hotel lists (see `docs/PARTNER-OPTIONS-2026-10-16.md`).

## 1.2.63 — 2026-10-16: stand-in photos can keep an edge when cropped
- A stand-in photo id may now end in `:top`, `:bottom`, `:left`, `:right`, `:faces` or `:entropy`. The Unsplash URL then crops toward that edge or subject instead of the centre: `er_stock_url()` in PHP, `img()` in `src/js/data.js`. Anything else is ignored.
- **First use:** the Mount Sinai guide's new photo, a portrait of Saint Catherine's bell tower (Unsplash, location "Saint Catherine, Egypt", free licence; see `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5).
  - A centred landscape crop cut off the tower; `:top` keeps the tower, the cross and the mountain.
- `src/js/data.js` was the one source file with CRLF line endings; it is now LF, per the project rule.

## 1.2.62 — 2026-10-15: the phone booking bar from the first screen
- **Owner report (Ras Mohammed on a phone):** "the bar is not on mobile". It only appeared after scrolling past the first screen, so a reader at the top never saw it.
- **Fix:** on phones (900 px and below) the bar is shown from the first screen. It still steps aside while the booking box it leads to is on screen. Wide screens keep showing it once past the first screen.

## 1.2.61 — 2026-10-15: the booking bar centred again, and on phones
- **Owner decision:** keep the bar centred on the screen as before (reverts 1.2.59's fit to the article column); the Trip assistant stays as it is.
- **Phones:** the bar is shown on phones too (it was wide screens only). It sits just above the dock, full width except the corner of the Trip assistant button, which it leaves clear in both directions (`inset-inline`).
  - The "Book with our partners" label gives way to the page's name and "Check Availability".
  - It is hidden while the menu is open.
- **QA:** the sidebar-overlap check is dropped. On phones every page must show the bar once scrolled, clear of the dock and the assistant button.

## 1.2.60 — 2026-10-15: the booking bar on destinations and guides
- **Owner request:** the same bar on destination and guide pages.
- `er_book_bar()` is now called after the section tabs on destinations and guides, as on experiences. It is wide screens only, and sits over the article column when the sidebar is shown.
  - **Destinations:** it hides when the full list of offers after the article is in view.
  - **Guides:** at 1200 px and wider their in-article box is hidden, so the bar stays.
- **Clicks on "#book" while the sidebar shows a booking box:** that box is used, called out with a gold ring if it is on screen, otherwise reached by scrolling to the start of the layout. Before, a destination's "#book" jumped past the article to the full list.
- **QA:** every page except the homes must have the bar, and on no page may it cover the sidebar.

## 1.2.59 — 2026-10-15: the booking bar keeps off the sidebar
- **Owner request:** narrow the bar so it does not cover the sidebar.
- **Before:** the bar was centred on the screen and up to 760 px wide. At 1440 px it covered the sidebar's edge (the key facts) on 53 of the 80 experience pages.
- **Fix:**
  - When the sidebar is shown, `site-main.js` measures the article column. The bar is then centred on that column and no wider (`data-in-column`, `--bar-x`/`--bar-w`, also in RTL).
  - Without a sidebar (the Abu Simbel story, or screens below 1200 px) it stays centred on the screen.
- **QA:** fails an experience page whose bar, once shown, overlaps the sidebar.

## 1.2.58 — 2026-10-15: the booking bar on every experience page
- **Owner report:** only Abu Simbel had the booking bar. 1.2.57 moved the booking boxes on the other experience pages but did not give them the bar, and the report wrongly said the Abu Simbel experience was applied to them.
- **`er_book_bar()`** (`inc/template-tags.php`) now renders the bar on every experience page with offers (story and regular), leading to the page's booking box.
  - Wide screens only; phones have the dock.
  - It shows once the reader is past the first screen.
  - On the story it hides at the closing booking section. On regular pages, whose box is pinned in the sidebar, it stays, including after the article where the sidebar ends.
- **"#book" to a box already on screen (the pinned sidebar):** the general anchor scroll jumped the reader back to where the sidebar starts. The box now gets a short gold ring and focus instead, and the page stays put. A box out of view still scrolls to the start of the layout.
- **QA:** every experience page must have the bar.

## 1.2.57 — 2026-10-15: the same booking path on every experience page
- **Owner request:** give the other experience pages the booking experience of the Abu Simbel page.
- **Wide screens:** the sidebar opens with the booking box, then the key facts. Before, the facts came first and the pinned sidebar (about 920 px) pushed the second offer below the screen.
- **Phones:** a booking box inside the article after its second section (the Giza tour: first partner link at about 23% of the page instead of 53%). The full box still follows the article, and the dock and menu stay.
- **Fix, also for destinations (1.2.55):**
  - On phones the key facts open the page only when they were the sidebar's first child.
  - With the booking box first, they had dropped below the article.
  - They now move up wherever they sit in the sidebar.
- **QA:** regular experience pages now get the sidebar-box and phone halfway checks (the long Abu Simbel story keeps its bar, menu and dock). Every page also fails if the key facts come after the article on phones.

## 1.2.56 — 2026-10-15: Abu Simbel story books from anywhere; phone menu books too
- **Owner request:** the easiest possible path to booking, and a smooth, clear Abu Simbel page.
- **Before:** the Abu Simbel story is about 12,600 px long, and its partner offers appeared only at about 10,900 px. Between the hero button and the end, nothing on a wide screen led to booking.
- **Story booking bar (wide screens):** a slim bar at the bottom of the screen with the experience's name, "Book with our partners" and "Check Availability" (→ `#plan`).
  - It appears once the reader has scrolled past the first screen, and hides at the booking section.
  - Phones keep the dock.
- **"What to know":** the partner offers now also sit under the heading, beside the practical detail (road or air, timing). On phones that brings the first partner link from 88% to about 65% of the page.
- **Phone menu:** its gold button went to the homepage planner on every page. On a page with offers it is now "Check Availability" to the booking box, as in the header.
  - Closing the menu returned focus to its button, which lost the jump. In-page links in the menu now close it first, then scroll.
- **QA:** fails a page whose phone-menu button doesn't go to the booking box, and a story page without its booking bar.

## 1.2.55 — 2026-10-15: booking reachable while reading; gold buttons no longer send readers home
- **Owner report (Hurghada):** no way to book until the end of the page, and the gold buttons led to the homepage.
- **Cause:**
  - The header's "Plan My Trip" and the destination sidebar's "Plan My Trip" both went to the homepage planner.
  - The destination's only partner cards came after the whole article (about 4,500 px down on desktop, two-thirds down on phones).
  - The QA gate only checked that a booking box existed somewhere on the page.
- **Fix:**
  - **Header:** on any page with partner offers, the gold button reads "Check Availability" and goes to the page's booking box. Pages without offers keep "Plan My Trip".
  - **Destinations, wide screens:** the sidebar opens with a booking box (two offers and the disclosure), sticky while reading, then key facts and things to do. The sidebar's "Plan My Trip" is removed.
  - **Destinations, below 1200px:** the same box sits in the article after its second section, as on guides.
  - **`#book` on guides at 1200px+:** the in-article box is hidden there, so the jump goes to the sidebar box (`site-main.js`).
  - **QA (`tools/qa/page-coverage.mjs`):** it now fails a page if any of these is true:
    - the header button doesn't go to the booking box;
    - a gold button in the page leads to the homepage planner;
    - a destination or guide has no sidebar booking box;
    - on phones, a destination's or guide's first partner link is more than halfway down.

## 1.2.54 — 2026-10-16: no duplicate "On this page" for signed-in editors
- **Owner report:** two navigations doing the same job on Alexandria: the sticky section tab bar and the "On this page" box.
- **Cause:**
  - The tab bar replaces the box, which the theme removes by matching its markup.
  - For signed-in editors, a plugin adds `data-sd-*` attributes before `class`.
  - The patterns expected `<div class="…` straight away, so the box stayed for editors.
  - Visitors were not affected.
- **Fix:**
  - The block patterns for the box, callouts and h3 groups accept other attributes before `class`: `<div[^>]*\sclass="…"`.
  - Files: `inc/sections.php` and `inc/template-tags.php`.

## Core 1.2.39 — 2026-10-16: five more guides
- **New guides, in 8 languages:**
  - "The Temples of Aswan and Lake Nasser" (Abu Simbel offers);
  - "Siwa or the White Desert?" (Siwa and White Desert offers);
  - "The Valley of the Kings: Tombs, Tickets and Tips" (West Bank offers);
  - "10 Days in Egypt" (Giza, West Bank, Abu Simbel and Hurghada offers);
  - "Cairo at Night" (Nile dinner cruise and food tour offers).
- **Topics chosen** from the first queries with impressions (e.g. "معابد اسوان") and from bookable intent. Each guide answers a search intent that no existing page covers.
- **Sources:** Britannica (Siwa, Valley of the Kings) and UNESCO (Nubian Monuments and the rescue campaign), in `docs/CONTENT-SOURCES.md`.
- **Photos:** `extraStock` stand-ins.

## 1.2.53 / Core 1.2.38 — 2026-10-15: content plan delivered (4 guides, 2 experiences)
- **New experience pages,** in 8 languages:
  - "Alexandria Day Trip from Cairo" (`exp-alexandria`, linked to offer #574);
  - "Ras Mohammed Snorkelling from Sharm El Sheikh" (`exp-ras-mohammed`, linked to offer #577).
- **New guides,** in 8 languages:
  - "Nile Cruise or Hotels? Luxor to Aswan Explained";
  - "The Best Day Trips from Cairo";
  - "Snorkelling in Egypt: Hurghada or Sharm El Sheikh?";
  - "A Night in the White Desert: What to Expect".
- **Sources:** Britannica (Edfu, Kom Ombo, Saqqara) and UNESCO (Wadi Al-Hitan). The other facts were already sourced on the site. See `docs/CONTENT-SOURCES.md`.
- **Abu Simbel guide:** the distance "280 km" was removed in all 8 languages. The fact policy leaves distances out.
- **Core sync:**
  - It now also creates new experiences (with a translated `location`), not just guides.
  - It updates a page it created from its file only while that page is untouched in WordPress (content hash, or never modified since creation).
- **Theme:** `er_stock_id_for()` reads `extraStock` from the seed for the stand-in photos of pages written after the seed.
- **Chinese:** offer cards use 拜哈里耶, as on the approved pages.

## 1.2.52 — 2026-10-15: guide booking box shows the choices
- **Guide booking boxes now show up to 4 offers in all:** every offer of the experiences the guide covers when it covers few, and the first offer of each when it covers several.
- **Why:** the Abu Simbel guide compares private and shared tours, but its box showed only the private one.

## Core 1.2.37 — 2026-10-15: first new guide, 7 more Viator offers
- **Owner authority, 2026-10-15:** publish and edit guides, and add all 7 offer candidates.
- **New guide "Abu Simbel from Aswan: How to Plan the Day"** (`g-abu-simbel`), published in 8 languages.
  - Facts from Britannica and UNESCO, listed in `docs/CONTENT-SOURCES.md`.
  - Related to the Abu Simbel experience and Aswan, with the experience's approved photo (seed entry).
- **Guide sync creates new English guides.** The guide-translations sync now also creates a new English guide from its editorial file when the file has `slug` and `title` and no guide has the seed id yet. It then creates the translations in the same run.
  - `tools/editorial.py` carries `slug` and `topic`.
  - Existing guides are never touched by this step.
- **7 second offers, verified on their Viator product pages,** with translations in `offers-i18n.json`:

  | Offer | Product | Format |
  |---|---|---|
  | #579 | 41437P6 | Giza, Sphinx and the Grand Egyptian Museum, full day |
  | #581 | 128688P15 | Abu Simbel, shared |
  | #583 | 66938P5 | Hurghada dives, beginners welcome |
  | #585 | 224493P76 | Luxor West Bank with Medinet Habu and Deir el-Medina |
  | #587 | 110498P6 | Private Nile dinner cruise with tanoura |
  | #589 | 476624P1 | Cairo food tour, 6 pm start |
  | #591 | 10124P65 | White Desert overnight |

- **Chinese:** the Abu Simbel offer title now uses 阿布辛贝, as on the approved experience page.

## 1.2.51 / Core 1.2.36 — 2026-10-15: owner's second UX review
- **Alexandria had no booking action.**
  - A verified Viator offer was added in wp-admin (#574): "Alexandria Day Trip From Cairo" (59626P4), supplied by Ramasside Tours, 4.7 from 1,489 reviews. It visits the Library, the amphitheatre, Qaitbay and Montazah.
  - Core `offers-i18n.json` has its 7 translations, plus those for the planned Sharm offer (Ras Mohammed, 5616599P11).
- **Trip assistant:** each partner link shows the trip's name. Two bare "Check Availability · Viator" buttons could not be told apart. A result with offers is one card: the page link, then one row per trip.
- **Guides use the two-column layout of the other templates.**
  - A sticky sidebar holds the booking box and the guide's destinations. The text no longer sits in a narrow centred column.
  - Below 1200px the in-article box replaces the sidebar, so the box never shows twice.
- **Cards in inner-page grids:**
  - The photo is landscape (it was a tall portrait).
  - A partner card says "via {partner}", and its CTA is a button (it was a text link).
  - A card that is alone in its grid lies on its side instead of leaving the row empty.

## 1.2.50 — 2026-10-15: booking within one tap, planner connected to Viator
- **Owner review:** the owner asked why a reader finishes an article without finding a booking button, and what the trip planner is connected to.
- **Experience, tour, activity and destination pages:**
  - The hero has a "Book with our partners" button. It jumps to the page's booking box (`#book`, or `#plan` on the Abu Simbel story).
  - Below 1200px the booking box used to come only after the whole article.
- **Guides:** a booking box sits inside the article, after its second section. It holds the offers of the experiences the guide covers, with the disclosure. The cards at the end stay.
- **Mobile dock:** on any page with partner offers, the main button is "Check Availability" (Core's translated CTA). It goes to the booking box. Pages without offers keep "Plan My Trip".
- **Trip planner:**
  - The drafted route lists the partner tours for its stops under "Book with our partners": up to 2 per destination, with direct `/go/` links (placement `home-planner`) and the disclosure.
  - Before this, the card ended in "Explore Egypt" only.
  - On screens shorter than 1000px the card no longer stays pinned once it holds tours.

## 1.2.49 / Core 1.2.35 — 2026-10-15: offer cards translated
- The offer titles and locations in all 8 languages come from Core `data/offers-i18n.json`.
- A card without its own image uses the photo of its experience or destination.

## Core 1.2.34 — 2026-10-15: guide translations reviewed, review note removed
- **Owner request:** remove the "not yet reviewed" note and check the translations professionally in every language.
- **Review pass on the 28 guide translations.** Wording and terminology aligned with the translations the owner approved on 29 Sep:
  - **Abu Simbel sun event:** the approved terms are "ظاهرة تعامد الشمس", "Sonnenwunder", "phénomène solaire", "fenomeno del sole", "fenómeno solar", "солнечный феномен" and "太阳奇观". They replace "festival / Fest / fête / праздник / 太阳节".
  - **Proper names:** spelled as in the approved pages ("Medinet Habou", "Хан-эль-Халили"). "Grand Egyptian Museum" is kept where the approved DE/IT pages use it.
  - **Arabic phrasing:** five awkward phrasings rewritten (e.g. "كايت سيرف", "المحور الرئيسي للقاهرة الفاطمية", "رسوخًا في الذاكرة").
  - **RU/IT:** two awkward phrasings fixed.
- **Status:** all 28 files are now `review: approved` (reviewer: AI review, publication approved by the owner, 2026-10-15). An approved translation carries **no note**. The sync updated the 28 pages in place, since none had been edited in WordPress.

## 1.2.48 — 2026-10-15 (with Core 1.2.33): the 4 travel guides in all 8 languages
- **Owner decision (15 Oct):** translate the guides and publish them right away, with a review note, instead of linking the English.
- **28 translations** (4 guides × ar, de, fr, it, es, ru, zh) in `content/editorial/<lang>/g-*.md`. Each is a faithful translation of the English text: same sections, facts and links, no added claims. They carry `review: pending`; `tools/editorial.py` now also carries the title.
- **Core: Egypt Roamer → Guide translations** (`includes/guide-translations.php`), the same model as the legal sync:
  - creates each missing translation, publishes it and links it in Polylang;
  - each one opens with "translated from the English original, not yet reviewed by a native speaker; the English version is the reference", linking the English guide;
  - internal links point at the translated pages;
  - it copies the photo, seed id and "Ready to index" from the English;
  - it never overwrites a translation edited in WordPress (content hash).

  When a file is marked `review: approved`, the note becomes a plain "translated from the English original".
- **Effect:** the menu, footer and homepage Journal now link the guides in each language, so the "EN" fallback tags from 1.2.47 disappear automatically.
- **Core:** reading time counts words in every script (`str_word_count()` counted only Latin letters, so Arabic and Russian guides said "1 min").
- **Guide dates** are written in the page's language ("15 أكتوبر 2026", "15. Oktober 2026", "2026年10月15日") through PHP intl; WordPress had printed "October 15, 2026" on every language.

## 1.2.47 — 2026-10-15: the same navigation and homepage in all 8 languages
- **The owner's complaint was right:** the English menu had 3 items (Destinations, Experiences, Travel Guide) and the other 7 languages had 2. 1.2.46 wrongly classed this as intentional.
- **Root cause:** every language's Polylang menu already has the translated "Travel Guide" item (Reiseführer, دليل السفر, 旅行指南 …). The "only working links" filter dropped it because guides exist only in English, so the archive is empty in the other languages. The same rule removed the footer guide links and the homepage "Journal" section.
- **Fix:** an item, link or section whose content exists only in the default language is kept, with its translated label, and links to the English content, marked with `hreflang="en"` and a small visible "EN" tag (`.lang-tag`). English titles inside RTL pages carry `lang="en" dir="ltr"` so they don't reorder. Applies to:
  - the menu (desktop and drawer);
  - the footer ("Travel Guide", "Best time to visit");
  - the homepage Journal section;
  - "Explore the Journal".

  The navigation and homepage sections are now identical in all 8 languages.
- **Guide topic names** (Cities, Itineraries, Planning, Hidden Gems) stay English, marked `lang="en"`: they describe English articles and have no approved translation.
- **QA:** `tools/qa/footer-parity.mjs` now accepts a link to another language's content only when it carries hreflang and the visible tag; unmarked fallbacks still fail.

## 1.2.46 — 2026-10-15 (with Core 1.2.32): multilingual consistency fixes, product-level Viator offers
- **Audit (production, 8 languages × 8 widths):** navigation, drawers, language switcher, homepage sections, destination / experience / guide / legal / archive pages. Report: `docs/MULTILINGUAL-NAVIGATION-CONSISTENCY-REPORT.md`.
- **Language menu:** on LTR pages "العربية" sat on the right edge of the list (its `dir="rtl"` set the alignment). It now lines up with the other languages; Arabic pages are unchanged.
- **Core: hreflang alternates never carry a query string.** Archive translations were built from the current URL, so `?utm_source=…` (or any query) was copied into every hreflang link while the canonical stayed clean.
- **Offer titles** (a partner's product name, written in the default language) carry `lang="en"` on translated pages, for screen readers.
- ~~Intentional, unchanged: "Travel Guide" only where a guide exists in that language~~ (wrong; fixed in 1.2.47). The Arabic opening scene keeps its text on the open sky (documented in pages.css).
- **Viator (wp-admin, no code):** the 7 offers now point to verified products, and Great Sand Sea has its first offer. Report: `docs/VIATOR-CONTENT-MATCHING-REPORT.md`.

## 1.2.45 — 2026-10-15 (with Core 1.2.31): first partner offers, disclosure everywhere they appear
- **Core 1.2.31: first-party page-view totals** (`includes/visits.php`, Egypt Roamer → Visits). The site had no measurement of visits at all. Each page view adds 1 to a daily total per page, language, page type, source category (search, AI assistant, social, other site, direct), referring domain and device class. No cookie, no identifier, no IP address or user agent is stored; editors, Do Not Track / Global Privacy Control, automation and bot user agents are not counted. Partner-link taps and failed `/go/` links are counted too. Privacy Policy updated in 8 languages. Measuring since 2026-10-15.
- **Disclosure next to every partner link.** The first live offers (Viator, 15 Oct) also show on cards: the homepage experiences rail, archives, search and "Alternatives to compare". Those grids had no disclosure line, so a page such as Great Sand Sea (no offer of its own, but an alternative with one) showed a partner link with no disclosure. `er_card_grid()` and the homepage rail now print the disclosure when one of their cards links to a partner.
- Report: `docs/VIATOR-AFFILIATE-INTEGRATION-REPORT-2026-10-15.md`, `docs/ANALYTICS-IMPLEMENTATION-REPORT-2026-10-15.md`.

## 1.2.44 — 2026-10-15 (with Core 1.2.30): search recovery, first fixes
- **Snippets:** Google was showing site furniture as the snippet for brand searches ("Trip assistant. Hello! I can point you…", "© 2026 Egypt Roamer…"). The footer, menus, overlays, the assistant and the dock labels now carry `data-nosnippet`, which Google honours on div, span and section. The visible page is unchanged.
- **Core: IndexNow** (`includes/indexnow.php`). Bing had no page of the site in its index (`site:egyptroamer.com`: 0 results, 15 Oct), and there was no Bing Webmaster Tools access. The site now tells the IndexNow engines (Bing, Yandex, Seznam, Naver) about changed URLs:
  - once in full (every indexable URL in all languages: items, homepages, section archives);
  - then every time an indexable item is published, changed, unpublished or ticked "Ready to index".
  - The key file is served at `/{key}.txt`. Nothing is sent while "Discourage search engines" is ticked.
  - The last submission and its HTTP answer appear in the dashboard health checks.
- Evidence and the full audit: `docs/ZERO-TRAFFIC-RECOVERY-AUDIT-2026-10-15.md`.

## 1.2.43 — 2026-10-05 (with Core 1.2.29): the walk moves on phones and tablets too
- **The motion ran only where the browser has CSS scroll timelines** (Chrome, Edge), so tablets and phones on Safari before 26, and Firefox, saw the still version. Now `immersion.js` moves the same CSS animations with the scroll in every browser: each is declared paused, 1s long, and set to the point the scroll has reached, from one table of scroll ranges (cover / contain / entry, as in CSS view timelines). Subjects are measured on layout changes, and the animations are updated once per frame while scrolling.
- Same rules as before: only with prefers-reduced-motion: no-preference; the walk holds the hero only on windows at least 36rem tall; without JavaScript the page is the still sequence.
- Checked at 390 (phone), 820×1180 (tablet) and 1440. axe, no-JS and reduced-motion runs (EN/DE/AR × 320/390/768/1440) report 0 issues. Mobile LCP 1.5 s, TBT 0, CLS 0.

## 1.2.42 — 2026-10-05 (with Core 1.2.28): Abu Simbel as a walk into the temple (concept A)
- **The owner chose concept A, "Walk into the rock".** The page now moves the way the visit does:
  - **the walk:** the hero photograph stays on screen while the opening and the first chapters pass over it. The light changes with them (night before dawn, then day), and at the end the camera walks through the temple door into the dark. `immersion.js` aims the zoom at the door's real position in the served crop (story `walk`: the door in the stock photo).
  - **inside:** the hall photograph starts black and the eyes adjust while the words pass over it;
  - **the highlight:** a ray of sunlight crosses the screen and the sanctuary statues light up;
  - **back into daylight:** a light flash, then Nefertari's temple beside its words;
  - **afterwards:** the rescue (cut into blocks, reassembled on higher ground) shown literally: the gaps between the blocks close while the colossi photograph tilts up and rises into place;
  - then "What it feels like" (now the façade with visitors, for scale), "What to know", "Combine it with", the FAQ and the closing CTA, as before.
- **Where you are:** a thin chapter rail at mid-height on wide screens, filling in gold as you go, with the current chapter marked. It replaces the separate "The journey" band and its chapter line.
- **Motion only where it's welcome and supported** (CSS scroll timelines and prefers-reduced-motion: no-preference). Elsewhere it's the same sequence, still: the hero, then each chapter as a dark section. The hall and sanctuary photographs still hold the screen while their words pass. A hero taller than the window is held by its bottom edge, so nothing is cut off.
- **Unchanged:**
  - the approved text, each sentence still in one place;
  - the hero is still the only first-view image (355 KB local, LCP 1.4 s, CLS 0);
  - the other experiences and pages are byte-identical apart from the build markers;
  - axe, no-JS and reduced-motion runs (EN/DE/AR × 320/390/768/1440) report 0 issues.

## 1.2.41 — 2026-10-04 (with Core 1.2.27): Abu Simbel rebuilt to the reference design
- **The owner's Abu Simbel mockup is the layout.** Same sequence, same bands, same compositions, built from the real content:
  - full-screen hero: three-line title, summary, two CTAs, and a bottom row of four labelled facts with gold icons;
  - a light, centred magazine intro;
  - a dark "The journey" intro ("From the desert to the temples") with a thin gold chapter line (vertical on phones);
  - the moments, each composed differently: a dawn-lit text panel, the full-bleed first view with its words on the dark side (the climax), text beside an edge-to-edge interior photograph, the sanctuary centred and lit, Nefertari's temple beside its words, a text-only ending;
  - "What it feels like" over a full-width photograph;
  - a light "What to know" with icons and the "Plan the day" checklist;
  - "Combine it with" as picture links with caption bars;
  - a dark FAQ with thin dividers;
  - the closing CTA over the hero photograph ("Explore our guides", chat, Plan My Trip).
- **Chapter typography as in the reference:** number in gold, serif name, gold uppercase line, then the body.
- **One home per sentence, still:**
  - the opening section is split by sentence between the light intro and the dark journey intro (story `split`);
  - the hero's extra facts are condensed from the approved text, and only in the story's languages;
  - all 30 approved sentences appear exactly once.
- **Not drawn as in the mockup, because it would invent content:**
  - an "Across the desert" chapter (no approved text or verified photo);
  - "Activity level", "Best time" and "Physical effort" (no approved data);
  - Nile Cruise / Abu Simbel guide cards (no such pages);
  - golden-hour and traveller photos (not verified).
- **Scope:** only Abu Simbel has a story. Every other page renders as before.

## 1.2.40 — 2026-10-04 (with Core 1.2.26): Abu Simbel visual redesign (cinematic editorial)
- **A real redesign of the experience story**, following the owner's visual direction:
  - a cinematic hero with its own CTAs;
  - a light opening with the chapters as a line to follow;
  - a dark journey built from different treatments;
  - an image-led "What it feels like";
  - a scannable "What to know";
  - picture links;
  - a dark editorial FAQ;
  - a closing CTA over the hero's photograph.
- **Hero.** Taller (up to 94% of the screen), with a larger title, the summary, then "Plan My Trip" (or "Book with our partners" when an offer is live) and "The journey". Location and duration sit under a hairline. Other pages' heroes are unchanged: `er_page_hero()` only gained optional `actions` / `modifier` arguments.
- **The journey, with a narrative arc** (quiet → reveal → immersion → peak → reflection):
  - "Before dawn": a quiet, minimal text chapter;
  - the Great Temple: full-bleed, with its words over the photograph (the climax of the arrival);
  - "Inside": the photo holds on the right while the text passes;
  - the sanctuary: centred in the dark, lit from within;
  - Nefertari's temple: edge to edge beside its words;
  - "Afterwards": a slow, spacious serif conclusion.
  Phones get the same arc as a vertical film, with the chapter line vertical.
- **Every approved sentence still has one home:**
  - "Who it suits" is now "What it feels like", beside the colossi photograph;
  - road, air, the time at the temples and "Plan the day" are now "What to know": a two-column fact list, a note and a checklist (`er_body_parts()` exposes a section's paragraphs, list terms and callout);
  - "Combine it with" shows Aswan and the two guides as picture links.
  All 30 sentences appear exactly once. Other languages use their own approved text and headings; stages with no name of their own stay off the chapter line.
- **Same performance budget.** The hero is still the only first-view image: phone 353 KB, desktop 512 KB, CLS 0. Throttled phone FCP 1.54 s, LCP 2.33 s. The closing CTA reuses the hero's cached photo files.
- Only Abu Simbel has a story. The other seven experiences and every other page render as before.

## 1.2.39 — 2026-10-03 (with Core 1.2.25): Abu Simbel as one experience story
- **One narrative instead of two pages.** 1.2.37 put the journey on top of the old experience layout, so the page told its story twice: the journey, "What it feels like" and "What to know", then the same body again with tabs, a sidebar and card sections. When an experience has a story, everything below the hero is now that one story (`template-parts/experience-story.php`):
  - the opening (why the journey matters, with the route at a glance);
  - the journey;
  - chapters on the light ground: who it suits, getting there, what to combine it with (with the related destination and guides);
  - the next step on the dark ground: the partner offer, or chat and the planner;
  - the questions.
  The duplicated "What it feels like" / "What to know" lists, the second intro, the tabs and the sidebar facts are gone.
- **Every approved sentence has exactly one home, in every language.** The page is built from its own approved body, split by section anchor (`er_body_parts()`):
  - "What it is" opens the page;
  - the three "Why it's worth the effort" points become the arrival, the inside stage and the closing beat;
  - "The sun festival" is the journey's peak;
  - "Who it suits", "Getting there" (with its "Plan the day" box), "Combine it with" and the questions become chapters.
  Checked on the English page: all 30 sentences of the approved text appear exactly once. German, Arabic and the other languages get the same structure from their own approved translations. Only the English stage titles are new structural labels, and other languages use their approved photo titles. Old links to `#why`, `#sun-festival`, `#who-it-suits` and the other anchors still land.
- **A journey with rhythm, not a list.** Each stage has its own weight:
  - a dark pause before dawn;
  - the Great Temple reveal, edge to edge (the strongest photograph);
  - a smaller inset detail of the colossi;
  - the interior held beside its text;
  - the sanctuary centred in the dark as the peak;
  - Nefertari's temple as a mirrored detail;
  - a closing beat on the 1960s rescue.
  Phones get it as a vertical film.
- **"Combine it with" no longer leaves empty space.** The related destination and guides are one row of cards sized to how many there are. The "Plan it with our guides" section with its half-empty grid is gone from this page.
- **Same first view, lighter scroll.** The hero stays the only first-view image (phone 375 KB, desktop 534 KB, CLS 0). A full scroll costs 1.1 MB on a phone and 0.9 MB on desktop, down from 1.3 / 1.2 MB.
- **Scope.** The other seven experiences have no story and keep their template exactly (HTML identical apart from the build id). The superseded `experience-immersion.php` is removed.

## 1.2.38 — 2026-10-03 (with Core 1.2.24): structured data for answer engines
- **Destinations and experiences describe themselves as web pages with real dates.** Each indexable destination and experience now carries a `WebPage` node with:
  - its language;
  - the real `datePublished` and `dateModified`;
  - the site it belongs to and its publisher (the same Organization and WebSite ids as the homepage).
  Before, only guides exposed dates or a publisher. Theme files are unchanged apart from the version.
- **Experiences are described as a `TouristTrip`.** It holds only what the page shows: the title, the summary, and the destinations under "Where it happens" as its itinerary, in the page's language. No offers, prices, ratings or provider: the site sells nothing.
- **Destination data gets an `@id`.** The existing `TouristDestination` gains `@id …#place`, which the page's `about` points to. Nothing else changes.
- The same gating as before applies: only without an SEO plugin, and only on pages ticked "Ready to index".

## 1.2.37 — 2026-10-03 (with Core 1.2.23): Experience Immersion (Abu Simbel)
- **Experiences can open as a journey instead of a photo strip.** A reusable "Experience Immersion" section (`template-parts/experience-immersion.php`) sits between the hero and the practical detail, on the hero's dark ground:
  - the opening: a short statement and the route at a glance (Before dawn → Arrival → The first view → Inside → The highlight → The second temple → Afterwards), each a link to its stage;
  - the journey, stage by stage: a full-width 4:5 photograph with its text beneath on phones; from 1024px the photograph holds still beside the text while the text passes. Where no verified photograph exists (before dawn, afterwards) the stage is typographic and the light of the hour is drawn by the background, not by a picture;
  - "What it feels like" (pace, time on site, setting, crowds), labelled as general expectations, not promises;
  - "What to know" on the light ground (location, duration, getting there, sun festival, what to bring, inside the temples), then the next step: the partner's own offer when one is live (with the disclosure), otherwise "Plan My Trip", and a link to the full guide below. The sidebar's Key facts are not repeated.
- **Same content system.** The story extends the existing preview entry (`data/previews.json` → `er_preview_story()`) and is built on the same verified, location-tagged photographs. Every sentence comes from the approved English text. Languages without the story text (all but English for now) show the same sequence of photographs with their approved, translated moment titles and captions: no mixed languages, no invented translation. Experiences without a story keep the previous strip.
- **No cost at first paint.** The hero stays the only first-view image. Stage photos are requested about a screen before they are reached (`immersion.js`; `<noscript>` without JavaScript). Abu Simbel, throttled phone and desktop: same first-view bytes (+2 KB of HTML/CSS/JS), same FCP and LCP, CLS 0. The image transition is CSS (scroll-driven), only where supported and only when motion is welcome; nothing depends on it.

## 1.2.36 — 2026-10-03 (with Core 1.2.22): guide hero and alignment, stable first render, lighter desktop heroes, Valley photo
- **Guides get their photo hero.** The guide template was copied from the article template and never passed its stand-in photo to the shared hero, so guides had a text-only dark band. They now show the photo their cards already use, like destinations and experiences. Byline, date, reading time, long-form body and the centred reading column are unchanged.
- **One alignment axis on guides from 1024px.** The "Experiences / Destinations in this guide" cards sat on the page gutter while the title and body used the centred reading column. They now share the column (two cards per row). The title starts on the column's edge but may run to the usual 18ch, so it no longer wraps to 3–4 lines: hero 815–927px → 703–784px at 1440, and the first paragraph is above the fold.
- **Text no longer jumps when the web fonts arrive** (inner pages). The page title's face and the body face are now preloaded per script (Latin; Cyrillic + Latin digits; Arabic; Chinese none). Valley of the Kings, 8 languages × 10 widths, throttled: combinations with CLS > 0.01 24 → 5, worst 0.231 → 0.053, for ≈ 0.19 s later first paint on a slow phone. The metric-adjusted fallback fonts tried earlier made real shifts worse and are not used.
- **Lighter desktop heroes, same framing.** Above 900px, page-hero photos asked for the full image height, so a portrait photo arrived several times taller than the band (Best Time guide 2.3 MB at 1280px on a 2× screen). The height is now capped at 0.8 × width (imgix `max-h`): portrait files are 40–55% smaller; landscape files are identical, and the visible framing is unchanged.
- **Valley of the Kings photo shows the West Bank.** The experience's photo was tagged "Karnak, Luxor" (East Bank). It is now the terraced temple of Hatshepsut beneath the cliffs, tagged and described as such by its photographer.

## 1.2.35 — 2026-10-03 (with Core 1.2.21): internal links, guide language switcher, Siwa photo, experience preview prototype
- **Internal links through the existing editorial pipeline.** Editorial front matter gains language-neutral relations (`destination`, `related`, `alternatives`) that `wp egypt-roamer editorial` adds when missing and never removes. They are left out of the translation fingerprint, so the 105 approved translations stay current.
  - Guides now list their destinations (each destination page shows "Plan your trip to …" for the English guides) and related experiences.
  - Red Sea Diving is also linked to Sharm El Sheikh, as its text already says.
  - Two pairs of "Alternatives to compare": Nile dinner cruise ↔ Cairo street food tour, White & Black Desert safari ↔ Great Sand Sea 4×4.
  - The four published English guides link words they already contain to the matching experience and destination pages. No wording changed.
- **Language switcher on archives.** A language whose archive has nothing published (the guides, English-only for now) is linked to its homepage instead of an empty, noindex archive. Archives with content still link to the same archive in each language.
- **Siwa photo with a verified location.** The Siwa destination photo (no location tag) is replaced by a free photo of Shali fortress tagged "Siwa, Egypt" by its photographer.
- **Experience preview (prototype, Abu Simbel only).** A "moment by moment" strip of five photographs after the page's first section, all location-tagged "Abu Simbel" by their photographers. Captions repeat the approved text, and credits link each photo's source.
  - Scroll-snap strip with previous/next buttons, a counter and progress bars. It works without JavaScript and in RTL, uses smooth scrolling only when motion is allowed, and lazy-loads every photo below the fold.
  - Video support is built but unused: poster first, the clip created only on play, muted, with native controls and a failure message. Media marked as generated is labelled "Illustration, not footage of the place". The other seven experiences have no preview until the prototype is approved.

## 1.2.34 — 2026-10-03 (with Core 1.2.20): photo provenance, honest newsletter, guide byline
- **Every photo now shows the place it claims.** A provenance check of all 52 stand-in photos against their Unsplash location tags found six public slots showing other countries or an unverifiable place. They are replaced (owner-approved 2026-10-03) by free photos whose photographer tagged the exact Egyptian place:
  - Red Sea Diving: NEOM, Saudi Arabia → a Hurghada reef with divers;
  - homepage Red Sea scene: NEOM → a Hurghada reef; its caption now gives Hurghada's coordinates instead of Ras Mohammed's, and the alt text describes what the photo shows;
  - film shot 5: NEOM → an emperor angelfish, Hurghada;
  - "Luxury" travel style: an Abu Dhabi resort → a Nile boat deck at Aswan;
  - Great Sand Sea 4×4 and "Adventure": Abu Dhabi → 4×4 tracks in the Siwa dunes;
  - White & Black Desert Safari: an untagged white-dune photo (tagged "winter") → a chalk arch tagged "White Desert, Bawiti".

  The car-rental samples (hidden drafts) no longer carry their Mojave and Dubai photos. Same crop pipeline as 1.2.33.
- **Newsletter promise matches reality.** Subscriptions are stored, but no monthly letter is sent. "One beautiful email a month… the deals worth knowing about" and "first letter arrives next month" are now "Hear from us when it matters", "New routes and guides, sent only when we have something worth your time" and "We'll write when there's something worth reading". Applied in all 8 languages.
- **Guides without a named author** read "By Egypt Roamer", the publication, as the Article structured data already says, instead of a bare "By".

## 1.2.33 — 2026-10-02: honest copy, sharp photos, lighter Arabic fonts
- **Copy that matched what the site can do today.** No partner offers are live, so the homepage no longer says visitors can "book it all" or that experiences are "bookable with our partners". It now reads: "Discover and plan the best experiences in Egypt — all in one place." (hero and homepage description), "1 place to discover & plan it all", and "Handpicked by our editors, with the practical details to plan them." "Hover a place to step inside it" (meaningless on a phone) is now "Choose a place to step inside it." Applied in all 8 languages. Partner-only wording ("Book with our partners", the finder) still only shows when an offer is live.
- **Sharp photos on phones, tablets and Retina screens, at the same weight.** Landscape photos sit in tall frames (`object-fit: cover`), so a phone was sent a 1200 px landscape file of which a third is visible, stretched about 3× (cards on a 2× laptop got 0.6× of the pixels they show). Unsplash now crops each photo to the frame's shape (`w` + `h` + `fit=crop`, centred like the CSS):
  - full-screen homepage scenes, planner and moods: 2:3 on phones, 1:1 on portrait tablets (`<picture>` sources, `er_stock_picture()`);
  - page heroes: their own phone and tablet shapes;
  - cards: 4:4.6, the card frame; phone destination cards: 1:1.3.

  Each crop is at least as wide, for its height, as any frame it serves, so the same part of the photo shows (verified on screenshots). On a 390 px iPhone the homepage photos weigh 379 KB instead of 470 KB.
- **The homepage hero no longer downloads twice.** The preload listed other widths than the image (900/1400/2000/2800 vs 900/1200/1600/2000/2800), so phones, tablets and 1280 px laptops fetched two files. There is now one preload per screen shape, listing exactly the image's candidates.
- **Arabic fonts 31-52 % smaller, pixel-identical.** IBM Plex Sans Arabic and Noto Naskh Arabic are cut to the basic Arabic block (U+0600-06FF: every letter, mark and digit, Persian letters included) with every shaping feature kept. Rendered pixel-identical on all 256 code points and 66,000 characters of the site's Arabic text in all 7 weights. The Arabic first view loads about 85 KB less. The Plex subset is renamed "ER Sans Arabic" inside the file, as the font licence (OFL, Reserved Font Name) asks for modified versions.
- **Phone destination cards on pale photos:** the region label sat on the bare sky (Cairo's haze). A steadier shade and a soft text shadow fix it, as on the phone page heroes.

## 1.2.32 — 2026-10-02
- **Inner-page heroes on phones stay readable on pale photos.** The shade faded out a third of the way down the hero, where the eyebrow and the title sit on a phone, so the gold eyebrow on the White Desert photo was nearly invisible. There is now a steadier shade plus a soft shadow under the text. Desktop is unchanged.

## 1.2.31 — 2026-10-02
- **Arabic and Chinese pages: one render-blocking stylesheet instead of three.** The Arabic `@font-face` rules (1 KB) and the RTL/CJK rules (3 KB) are printed inside the page after the bundle, in the same cascade order. PageSpeed counted 1.6 s of render blocking on the Arabic homepage on a phone. The Arabic font URLs are absolute, and both files count in the stale-page build ID.

## 1.2.30 — 2026-10-02 (with Core 1.2.19): post-launch improvements
- **Mood section on phones: one background picture instead of seven.** The hidden mood layers (one was 457 KB) loaded with the visible one, about 900 KB on a phone. Now only the visible one loads. The others load when the dial is touched or focused, or on a desktop when the section nears the screen. A new mood's picture replaces the old one only once it is decoded (no dark flash).
- **Scene images: closer srcset steps** (1200/1600/1800 px added), so a 1440 px desktop no longer receives 2,000–2,400 px files. The sharpness of what is shown is unchanged.
- **Section tabs:** a soft fade on the side that has more tabs, instead of a label cut mid-word. A tab reached with the keyboard stops clear of the fade, and the fade follows the reading side in Arabic.
- **Experience pages with no live offer end with a next step:** "Need personal help?", which opens the team chat (or the Trip assistant), plus Plan My Trip. All the strings are already translated in every language.
- **Chinese page titles at full size:** the title fitting treated a Chinese title (no spaces) as one unbreakable word and shrank it (48 → about 30 px on phones). Each CJK character is now its own unit.
- **Logged-in editors:** the sticky section tabs no longer slide under the WordPress toolbar.

## 1.2.29 — 2026-10-02 (with Core 1.2.18): SEO workstream
- **Homepage title states the search intent:** "Egypt – Travel Guide – Egypt Roamer", in every language from approved strings ("مصر – دليل السفر", "Ägypten – Reiseführer" …). The slogan stays in the hero and the description.
- **Long titles drop the brand suffix** instead of being cut in search results at about 60 characters. The site name shows separately there.
- **Headlines no longer run words together.** "What kind of Egypt<br>are you looking for?" read as "Egyptare" to search engines and assistive tools; a space now precedes every line break (`er_spaced_breaks`).
- **Destination structured data names the country in the page's language** (`er_country_name`: "مصر", "Ägypten" …).

## 1.2.28 — 2026-10-02
- **Homepage font preloads: only the hero word's display face** (Latin/Cyrillic Playfair 400). None on Arabic and Chinese pages.
  - Measured in a real browser on a throttled phone connection (1.6 Mbps, 4× CPU, median of 3), preloading every first-view face made the first paint later: the fonts competed with the stylesheet.
    - English: 2.32 s with all, 1.71 s title face only.
    - Arabic: 2.84 s with all, 2.00 s with none.
  - Lighthouse agrees for English: local LCP 3.0 → 2.55 s.
  - This replaces 1.2.23/1.2.25's "preload everything". The other faces load from the stylesheet and swap in.
- **Homepage scripts in a background tab:** a tab opened in the background never paints, so the phone runner now starts the scripts from its 2 s fallback directly instead of waiting for an animation frame that only comes when the tab is shown.

## 1.2.27 — 2026-10-02
- **Chinese pages on phones use the device's CJK font; Noto SC stays on desktop.** 1.2.26's non-blocking, `optional` copy was not enough on production. Google's font servers are fast, so in PageSpeed's unthrottled pass all ~100 Noto SC slices (several MB) finished before the first paint and were counted in it (still 15 s).
  - **Phones:** neither the 212 KB stylesheet nor its slices are fetched. A stylesheet with a non-matching media query would still be downloaded, so a one-line script adds it on screens wider than 900 px only, with a `<noscript>` fallback. Android's CJK system font is the same Noto/Source Han design.
  - **Desktop:** Noto SC, swap, under the intro loader.
  - The Latin faces are preloaded on Chinese pages too (spaces, digits, Latin words).
  - Local mobile: Perf 90, LCP 3.35 s, TBT 34 ms; zero Google font requests on phones.

## 1.2.26 — 2026-10-02
- **Chinese pages on phones no longer wait for Google's font stylesheet.** The Noto SC stylesheet is 212 KB (every unicode-range slice of seven faces) and was render-blocking: PageSpeed measured a **15.1 s** first paint on the Chinese homepage.
  - **Phones (≤ 900 px):** a non-blocking copy with `display=optional`. Chinese text is drawn at once in the device's CJK font, and Noto SC is used when it is already at hand (a returning visitor's cache). With `swap`, re-laying out the page for each of ~100 slices cost 530 ms of blocking time.
  - **Desktop:** unchanged (blocking, `swap`; the intro loader covers the load).
  - The font host is preconnected.
  - Local mobile: LCP 3.08 s, TBT 65 ms.

## 1.2.25 — 2026-10-02
- **Arabic homepage: the Latin faces are preloaded too.** The font stacks start with the Latin family, so the spaces and digits inside Arabic text are drawn from (and fetch) Inter and Playfair. On production these files were found only after the stylesheet: a 1.5 s chain in PageSpeed's dependency tree. Local mobile FCP 3.43 → 1.74 s; LCP is unchanged (4.7 → 4.6 s), because it is bound by the eleven font files' bytes (see `docs/PERFORMANCE-2026-10-02.md`).

## 1.2.24 — 2026-10-02
- **The mood section's background photos fit the screen.** They were requested at a fixed 1800 px on every device: up to 1.35 MB each, about 2 MB that phones didn't need (PageSpeed, "Improve image delivery"). They now have a responsive `srcset` (600–1800 px, `sizes="100vw"`): a phone takes the 900 px versions, and desktop is unchanged.

## 1.2.23 — 2026-10-02
- **The stale-page check waits for the first paint and runs at low priority.** PageSpeed listed its request on the homepage's critical path (452 ms). It decides whether to reload a stale copy, never what is drawn, so it no longer competes with the first view. `stale-html.mjs` 7/7.
- **Every face the homepage's first view draws is preloaded** (Latin: 6; Cyrillic: 6; Arabic: 5), not just two. The rest were found only after the stylesheet arrived. Local mobile FCP 2.29 → 1.63 s.

## 1.2.22 — 2026-10-02
**Phones: content-first homepage.** Desktop is unchanged; it keeps the cinematic intro. Before this, phone visitors saw nothing but the loader until the intro's 1.3 s hold, the hero photo, every font and ~86 KB of homepage JavaScript had finished. PageSpeed measured 3.2 s of "render delay" on the hero text (`docs/PERFORMANCE-2026-10-02.md`).
- **No loader screen on phones (≤ 900 px, the hero's phone layout).** The hero is drawn at the first paint and plays its own entrance in CSS:
  - the eyebrow rises out of its mask;
  - the title settles into place, fully drawn from the first frame;
  - text and buttons fade up.
  The brand mark stays in the header. `hero.js` hands over at once on these widths. Reduced motion: no movement.
- **Homepage scripts on phones run after the first view is painted.** GSAP, ScrollTrigger, Lenis, the language file and the homepage bundle wait for the first contentful paint and the first-view fonts (at most 2 s). They were deferred scripts that ran before anything was shown. They still download early (`preload`); on desktop they run as before. Without them the page stays the static, stacked fallback.
- **The journey's pin spacer is part of the markup** (`.journey__spacer`). ScrollTrigger used to re-parent the stage when pinning, and Chrome then reported the Arabic hero title a second time as a new, later Largest Contentful Paint.
- The phone hero text settles in like the title (drawn from the first frame). In Arabic it is the largest element on screen, and its fade from transparent made it a later LCP. The homepage launcher on phones waits for its first placement instead of flashing next to the play button.
- **Logos download only where they show.** The full logo isn't downloaded on phones, the mark isn't downloaded on wider screens, and the loader logo isn't downloaded on phones (`<picture>` with a 1 px source for the hidden breakpoint). The light-background variants load lazily and are fetched after page load, so the header never shows an empty logo when it turns light. Phones save ~71 KB before the first paint.

## 1.2.21 — 2026-10-02
Production accessibility pass (axe on live pages):
- **The chat transcript is reachable by keyboard.** A long transcript scrolls, but the log could not be focused, so keyboard users could not scroll it (axe "scrollable-region-focusable", serious). It is now a labelled `role="log"` region with `tabindex="0"`.
- **No nested complementary landmarks on the homepage.** The draft itinerary and the scene "Roamer pick" cards were `<aside>` elements inside `<main>`. They are now labelled `<section>`s (axe "landmark-complementary-is-top-level"). No visual change.

## 1.2.20 — 2026-10-02 (with Core 1.2.17)
- **Chat messages keep their order.** Messages typed in quick succession were sent as concurrent requests and could reach the team out of order (seen in the 8-language test: 1, 3, 2). The visitor's messages now go out one at a time; each bubble still appears at once.
- **A stalled request no longer freezes the chat.** Visitor requests give up after 20 s (a failed message shows Retry, and later messages still go out). In Core 1.2.17, the team inbox's requests give up after 15 s: before, one unanswered request stopped all inbox refreshes until a reload. Test: `tools/qa/chat-stall.mjs`.
- **Arabic and Chinese labels** (the hero's eyebrow, scene kickers, card and footer labels) are 12.8 px instead of 11 px. Without Latin capitals and tracking, 11 px Arabic or Chinese read as a faint, cramped line.
- **Phones:** the hero's buttons clear the bottom dock. The play button's pulse ring crossed the dock's edge.
- **Phones: the Trip assistant launcher no longer covers text.**
  - On inner pages it steps aside while the page header's text is under it (as on the homepage hero). It covered the experience intro at 320 px.
  - The footer's legal links end above it (it covered "Cookie-Richtlinie" in German).
- **Page headers over photos get a side shade under the text** (left in LTR, right in RTL), as on the homepage scenes. The Arabic experience page's gold label sat on a bright sky at about 1.4:1 contrast.
- **Long words in page titles fit their line.** The title shrinks just enough for its longest word. On a 320 px phone the Russian "Индивидуальная" broke mid-word without a hyphen. `hyphens: auto` was also added, for browsers that hyphenate the language.

## 1.2.19 — 2026-10-02
- **Arabic hero: the landmark photo is no longer mirrored.** 1.2.17 flipped the Giza photo in RTL so the text would not sit on the Great Pyramid. Instead, on screens wider than 900 px the opening scene keeps the photo's own composition in Arabic: the Arabic text block (right-aligned, read right to left) sits on the open sky on the left under the shade, the note and pick card on the right. Phones stack the text at the bottom and needed no change. `pages.css`; screenshots 320–1920.
- **One stylesheet per template.** The six or seven render-blocking stylesheets are served as one file built by `tools/build.py theme` (`bundle-home.css`, `bundle-site.css`): the same rules in the same order, comments and indentation removed (−20%). Computed styles of every element are identical to the separate files (12 pages × 2 widths). The source files stay the ones to edit; CI fails if a bundle is stale. Arabic fonts and `rtl.css` still load separately.
- **Homepage fonts preloaded:** the hero word's serif and the hero text's light sans (per script: Latin, Cyrillic, Arabic) start downloading with the stylesheet instead of after it.
- Measured (Lighthouse mobile, local, median of 3): homepage FCP 2.73 → 1.68 s, LCP 5.16 → 4.65 s; destination FCP 2.79 → 2.04 s; experiences 2.48 → 1.86 s. Details: `docs/PERFORMANCE-2026-10-02.md`.

## 1.2.18 — 2026-10-01
- **Homepage banner, bottom right (owner's screenshot):** the dune foreground ended 2% short of the right edge, leaving a hard vertical seam next to the Trip assistant button at every width. `journey.css` sizes it 104% wide from −2%, but the base reset (`img, svg { max-width: 100% }`) capped it at 100%. The cap is lifted for this layer (`pages.css`); it now overhangs both edges as designed. Measured −18 → 930 px at 912 px (was −18 → 894), −29 → 1469 at 1440; no horizontal overflow (homepage 44 checks, 4 languages).

## 1.2.17 — 2026-10-01
Visual audit (rendered screenshots in 8 languages × 320–1920 px, every assistant state, logged-in and adversarial states; `tools/qa/visual-sheets.mjs`, `visual-adversarial.mjs`):
- **Arabic hero composition.** The layout mirrors in RTL but the photo did not: the hero text sat on the Great Pyramid (the photo's subject, on its right), and the bare sun glared under the note on the left. The opening photos are now mirrored in RTL (CSS `scale: -1 1`, which composes with GSAP's transforms): subject opposite the text, sun under the text's shade, as in the other languages.
- **Outdated cached pages heal themselves.** Pages cached in browsers before 1.2.15 (no build id, 31-day host cache) still showed the 1.1:1 ghost "Chat with Egypt Roamer" button. The assistant script, fetched fresh when the drawer opens, reloads such a page once. And the ghost style inside the drawer is readable even if an old page gets the new stylesheet.
- **Welcome line** in the assistant drawer (it opened as an empty panel). One new string, drafted in 7 languages (unreviewed).
- The Privacy Policy link in the chat form is underlined (it looked like plain muted text).
- **Phone dock:** "Planifier mon voyage" (fr) was cut by the button's fixed height at 320 px; "Спланировать" (ru) touched its edges. A smaller, tighter label, with a minimum height instead of a fixed one.
- **Logged-in editors:** the WordPress toolbar covered the drawers' title and close button, and below 600 px it covered the header (the menu button could not be clicked). The drawers and the menu now sit below the toolbar, and the toolbar stays fixed on small screens.
- **Launcher at 320 px (Arabic):** 12 px from the play button, it read as a third hero button. "Too close" (20 px) now counts as an overlap, so it steps aside there too and returns on scroll.

## Core 1.2.16 — 2026-10-01 (no theme change)
Adversarial pass on the chat:
- A visitor-supplied page path like `//evil.example/x` passed the same-host check and appeared in the team inbox as a protocol-relative link to another site. Paths are now stored with a single leading slash, and the inbox links only same-site paths.
- A site-wide cap of 60 new conversations per hour, besides the 10 per IP: a flood from many IPs can't fill the inbox or the database.

## 1.2.16 — 2026-10-01
Chat message order and completeness under concurrency (with Core 1.2.15; `tools/qa/chat-multi.mjs` 11/11):
- **Order.** The visitor who sent a message saw it after a team reply that the server had stored first; other views showed the server order. Messages are now placed by their server id in both the visitor UI and the team inbox; a message being sent stays at the end until stored.
- **No message lost.** Views polled "after the newest id seen". With concurrent inserts (MySQL), a lower id can be committed after a higher one was read, and was then never fetched; the inbox also dropped any id at or below the newest seen. Polls now re-read a small overlap (the last 20 ids) and skip ids already shown. Reproduced by inserting a lower id late: before, the inbox never showed it; now both views do, in order.

## 1.2.15 — 2026-10-01
With Core 1.2.14.
- **Homepage start-up (TBT), no visible change** (`docs/PERFORMANCE-2026-09-30.md`):
  - The journey sets itself up in three tasks (titles and initial states / timeline / particles, rail and pin) instead of one.
  - The duplicate `ScrollTrigger.refresh()` on `load` is gone (ScrollTrigger refreshes on load by itself).
  - Local, 4× CPU phone, median of 5: TBT-like 477 → 370 ms, longest task 282 → 246 ms, refreshes 4 → 3 (desktop 4 → 2).
  - Identical ScrollTrigger geometry (every start and end, the pin spacer, the page height), screenshots identical at 5 scroll positions (only the random particles differ), `#planner` lands identically, CLS 0.0003 / 0.0019.
- **Stale-HTML guard** (`docs/CACHE-2026-10-01.md`): every page carries its build id (`<meta name="er-build">`). A 0.7 KB inline script compares it with Core's `/wp-json/egypt-roamer/v1/build`, at once for an unknown id and otherwise at most every 30 minutes. An outdated page (the host keeps pages in browsers for 31 days) reloads once, or loads once with `?nocache=` when the edge copy is outdated too. Static asset caching is unchanged.

- **Assistant and chat in 8 languages** (`tools/qa/chat-i18n.mjs`, every state at 320 and 1440 px):
  - The input's prompt is a visible caption above the field instead of a placeholder. The translated prompts ("Ask about a place, a trip or an experience": 186–464 px) didn't fit the 119–238 px field on any phone in 7 of 8 languages; the cut was there since 1.2.9. The caption wraps, uses only existing translations, and is better for screen readers.
  - "Chat with Egypt Roamer" and "Cancel" used the light-on-dark ghost button on the drawer's light background: contrast 1.1:1. They now use the outline button. axe had only listed them as "needs review", because of the button's backdrop blur.

Core 1.2.14: `includes/freshness.php` (build id, content epoch on editor saves, `/build` endpoint).

## 1.2.14 — 2026-10-01
Chat with the Egypt Roamer team, inside the Trip assistant (with Core 1.2.12; `docs/HUMAN-LIVE-CHAT-2026-10-01.md`):
- The drawer offers "Chat with Egypt Roamer", with the team's real availability ("Team is online" or "Leave us a message"). Asking the assistant for a person offers the same.
- A short start form (optional name and email, the message, a storage notice with the Privacy Policy link) starts a conversation; the questions asked so far go with it.
- In the chat, the drawer becomes "Live chat": bubbles for the visitor, the team and the assistant; pending, sent and failed states with Retry (no duplicates); "Ask for the team" and "End chat"; resumed after a reload.
- New messages are fetched only while the drawer is open and the tab visible (every 3 s, slowing to 15 s when quiet). Nothing new loads on the page until the drawer is opened.
- 29 new UI strings in 8 languages (drafted, unreviewed: `tools/i18n/new-strings.json`); Arabic mirrored.

Core 1.2.13 (same day): the inbox keeps working in a background tab (polling every 30 s / 15 s, presence continues, unread count in the tab title), found in the production test. Visitor parameters accept only scalar values (crafted JSON arrays are ignored, no PHP warnings).

Core 1.2.12: tables `er_chat_conversations` and `er_chat_messages` (DB version 2); visitor and team REST routes; Egypt Roamer → Conversations (inbox, filters, search, take over, return to AI, close, reopen, assign, archive, presence, unread badge through Heartbeat); capability `manage_er_conversations` (administrators, editors); settings "Chat with the team" and retention (90 days); email notification of new or reopened chats; daily purge; privacy export and erasure.

## 1.2.13 — 2026-10-01
SEO, with Core 1.2.11 (`docs/SEO-AUDIT-2026-10-01.md`). Indexing stays off; nothing here changes what visitors see on the page:
- Destination titles follow the search intent: "Kairo – Reiseführer – Egypt Roamer", built only from approved strings (the destination's title and the theme's "Travel Guide" in each language).
- The theme provides the logo for Core's Organization data (`er_brand_logo`).

Core 1.2.11:
- Homepage structured data: Organization (name, URL, logo) and WebSite (languages), true facts only: no contact point (the public mailbox has no MX record yet), no sameAs, no retired SearchAction.
- Article data for guides and journal posts once they are published and ready to index (real dates; the publication as author; the image only when the article has its own).
- The core sitemap now lists the indexable hubs (/destinations/, /experiences/, and /guides/ etc. once they have an indexable item), one per language, with the newest item's date as lastmod.
- robots.txt: `Disallow: /go/` sits in the `User-agent: *` block, before the Sitemap line.
- `wp egypt-roamer index`: the launch switch, step 1. Reviews (or with `--apply`, ticks "Ready to index" on) published destinations and experiences in every language on quality, not length: own description, in-content links, a translation complete against its English original (sections, FAQ items, list items, links) and written in its language's script. Local launch simulation: 176 sitemap URLs, 0 failures (`tools/qa/launch-sim.py`).

## 1.2.12 — 2026-10-01
Trip assistant launcher, keyboard safety for the 1.2.11 phone behaviour:
- The launcher never steps aside while it has keyboard focus or while its drawer is open (focus returns to it when the drawer closes), and it re-checks when it loses focus or an overlay closes.
- New check `tools/qa/launcher-keyboard.mjs` (320/360 px, en/de/ar): reached by Tab, visible whenever focused, never focused while hidden, Enter opens the drawer with focus in the question field, Escape returns focus, stays visible when scrolled back to the hero while focused.

## 1.2.11 — 2026-10-01
Homepage: the Trip assistant launcher no longer covers hero or journey content (the hero's right-side crop is the approved composition: the image geometry matches the prototype at every width, so the hero CSS is unchanged):
- Desktop (≥ 901 px): the launcher sat on the journey's scene counter ("01 / 04", same corner; mirrored in Arabic) at every width. While the journey is on screen it now sits above the counter (`body.assistant-raised`), and drops back at the footer.
- Phones: at 320 px (6 languages) and 360 px (German) the launcher covered the hero's play button. It now steps aside while those buttons are visible under it (`body.assistant-clear`) and returns on scroll.
- `assistant-loader.js` sets the classes; the rules are in `pages.css`. The approved stylesheets are unchanged.

## 1.2.10 — 2026-09-30
Homepage entrance, owner decision of 2026-09-30 ("reveal the text with the loader"):
- The hero comes in as the loader's wipe starts. Before, it came 250 ms after the loader, the paragraph waited another 0.55 s, and the fade lasted 1.2 s. Now it is one 0.6 s fade with a light stagger (overrides in `pages.css`; the approved stylesheets are unchanged).
- The hero paragraph was the Largest Contentful Paint (GTmetrix: 98-99 % render delay). Local LCP went from 2.7-3.6 s to 2.28 s, including with a slow third-party hero photo.

## 1.2.9 — 2026-09-30
Trip assistant (with Core 1.2.10; `docs/ASSISTANT-2026-09-30.md`):
- A "Trip assistant" button on every page (bottom corner, mirrored in Arabic; above the dock on phones) opens a drawer. The drawer reuses the Saved drawer: focus trap, Escape, focus return.
- The visitor asks a question, or taps a suggestion (Pyramids, Nile, Desert, Red Sea: the approved scene titles). The answer lists the published pages that match, in the page's language, with live offers when a page has any (through /go/, `rel="sponsored"`). With no match, it offers the destination and experience archives.
- The script (`assets/js/assistant.js`, 4.6 KB) loads only when the drawer is first opened. Questions asked while it loads are queued.
- New UI strings: drafted in 7 languages in `tools/i18n/new-strings.json` (unreviewed, like the other WordPress-only strings).
- Accessibility found while testing:
  - the drawers' `<header>` counted as a second page banner while open (the Saved drawer too); it is now a `div`;
  - the mobile menu's links were outside any landmark; the menu is now a `<nav>`.
- Desktop: room under the footer's last row, so the launcher never covers the legal links.

## 1.2.8 — 2026-09-30
Homepage performance (measurements in `docs/PERFORMANCE-2026-09-30.md`; the look and the entrance animation are unchanged):
- The intro loader's minimum (1.3 s) and its cap count from the start of the page, not from when the script runs. The loader is on screen from the first paint, so a slow phone no longer sits through it twice.
- The loader waits for the hero photo at most 1.6 s from the start of the page (was 2.2 s after the script started). The photo is hot-linked from a third-party host, and a slow response held the whole hero back. The photo still fades in when it arrives.
- The intro starts as soon as the hero is ready, not after every homepage section has been set up.
- The homepage sections are set up one task at a time, in the same order: one ~450 ms task on a mid-range phone became separate ones, so the page stays responsive while they load.

## 1.2.7 — 2026-09-30
From the full-site audit (every public URL × 8 languages; `docs/FULL-SITE-AUDIT-2026-09-30.md`):
- Homepage display titles fit their column in every language: "КРАСНОЕ МОРЕ" was clipped at every width (desktop included), "ROTES MEER" on phones up to 480 px, and "ÉGYPTE", "EGITTO" and "EGIPTO" touched the edge on small phones. A fitter (`src/js/components/fit.js`) keeps the approved size while the word fits and shrinks it just enough when it does not; English and Chinese are unchanged at every width.
- Keyboard:
  - Tab and Shift+Tab stay inside an open overlay (search, saved) or the mobile menu, with the menu button, instead of moving to the page hidden behind it.
  - The language menu closes when focus leaves it, and Escape closes it wherever focus is.
- Home links (logo, Plan My Trip, planner links, 404, breadcrumbs, search overlay, script data) use `er_home_url()`, the language's homepage, explicitly. `home_url( '/' )` was localized by Polylang only when the calling file's path matched the theme folder: correct on production, English on an installation whose theme is a symlink.
- Language switcher on an archive with nothing published yet (tours, activities, guides) links the same archive in each language instead of the homepages.
- `tools/build.py` writes LF on Windows (it wrote CRLF bundles).

## 1.2.6 — 2026-09-30
Global footer parity (with Core 1.2.9). The English footer had three columns (Explore, Plan, Egypt Roamer) and a two-link legal row; the seven other languages had only Explore, with Affiliate Disclosure and Contact moved into the legal row. The columns came from per-language menus the seed had generated, which left out every item it could not translate at the time: the "Trip Builder" anchor, and the Affiliate Disclosure and Contact pages (English-only then).
- One canonical footer: every language renders the default language's footer menus, localized item by item (`er_localize_menu_item()`): pages → their translation, archives → the language's archive, homepage anchors → the language's homepage, labels → the approved prototype translations (else the translated page's title). An item with no version in a language is left out, never shown in English. Polylang's swap to a per-language menu is undone for these locations.
- Theme translations: the footer menu labels ("Trip Builder", "Affiliate Disclosure", "Contact", "Our Story", …) from the prototype's approved locale files (`tools/i18n-strings.py` now reads the seed's menu labels).
- `er_t_strict()`: a theme string only when this language has a translation.
- Footer columns carry `data-footer-col` (the menu location) for the parity test `tools/qa/footer-parity.mjs`.
- Menu locations are labelled "(all languages)" in Appearance → Menus.

## 1.2.5 — 2026-09-30
Multilingual parity for everything added after the 105-file translation review (with Core 1.2.8):
- Legal/contact pages (`page.php`): documents read on the centred column with section tabs (three or more sections), a quiet "last updated" line, hairline section dividers, compact headings, a bordered table that stacks into label/value blocks on phones, and a translation note on translated pages. No cards.
- Contact: intro, details (email, phone) and booking note beside the form on desktop (container width, one axis with the hero); stacked on phones; email/phone kept left-to-right in Arabic.
- Footer legal row built from the pages (Privacy, Terms, Cookies, Affiliate Disclosure, Contact) in the page's language with each page's own title, no longer from the hand-edited menus.
- Archive intros: `er_archive_intro()` (Core), localized in 8 languages; the guides intro only while guides are published in that language.
- `er_body()` option `cards => false` for documents.

## 1.2.4 — 2026-09-30
- Menu link check: it compared links with `home_url( '/' )`, which Polylang turns into `/de/`, `/fr/` … on translated pages, so links to unprefixed pages were treated as external and never checked. A German footer link to the unpublished Terms page (404) was shown. It now compares with the site's own origin.

## 1.2.3 — 2026-09-30
Legal/trust launch (the pages were published on production the same day; the texts are in `content/legal/en/`):
- Footer legal row (`er_legal_links()`): the "legal" menu, completed with every published legal/trust page (Privacy, Terms, Cookies, Affiliate Disclosure, Contact) not already in the footer columns. Every language reaches the English-only pages directly.
- Menus: a language-prefixed link to a page that exists only in another language (`/ar/privacy-policy/`) now links the page itself instead of a URL that only redirects.
- `page.php` pages (legal, contact): the article was both a 3rem-gap grid and `.prose`, doubling the space between every paragraph. Plain prose flow now.
- Archive intros and their meta descriptions use Core's `er_translate_string_strict()`: shown in English only until a real translation exists, so no English text on a translated archive.
- Footer newsletter note: its Privacy link is underlined (axe `link-in-text-block`, shown on every page once the Privacy Policy was published).

## 1.2.2 — 2026-09-30
Keyboard audit on production (every page, every language):
- The closed language menu was only transparent: its 8 links were invisible tab stops. It is now hidden until opened (the fade is kept).
- In-page links (section tabs, contents links) scrolled the heading under the sticky bars: the anchor handler now respects the target's `scroll-margin-top`, and moves focus to the section so the next Tab continues there.

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
