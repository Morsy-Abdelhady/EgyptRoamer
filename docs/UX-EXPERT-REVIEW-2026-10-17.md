# Proposal 4 against production: content review and expert UX/UI review (17 Oct 2026)

**Owner's requests:**
1. Seven places, not six.
2. Sharm has two trips.
3. The footer has links with no pages.
4. Merge the article template into the main template.
5. The Cairo food trip's photo is not Egyptian food, and its page has no photos.
6. Review production's content for what was forgotten or removed.
7. Review the whole site as a UX and UI expert.

**Method:**
- Production was read with Playwright: homepage sections, `window.ER_DATA`, every destination page and its trips, all 24 guides, and the footer pages.
- The prototype was then captured screen by screen with `tour.mjs`: 46 screens each at 1440 px, 840 px and 390 px, 138 in total. Every screen was looked at full size.
- **Checks passed:** no script errors, and no sideways scroll.

## 1. Fixed in the prototype

| Owner's point | Fix |
|---|---|
| 6 places in the filter | **Seven places, as on production:** Cairo, Alexandria, Siwa, Luxor, Aswan, Hurghada and Sharm El Sheikh, in the filter, "Where in Egypt?", the groups and the Places page. Each trip belongs to the places production links it to: the dive day to Hurghada and Sharm, and the White Desert safari to Siwa. Sharm El Sheikh now shows its 2 trips. A trip listed under two places is counted once ("10 trips"). |
| Footer links lead nowhere | **Every link opens:** Places, Trips & tours, Travel Guide, Trip Builder (scrolls to the Trip Builder) and Best Time to Visit (the guide). Affiliate Disclosure, Contact, Privacy Policy and Cookie Policy show production's own text. Forms send nothing in the prototype. |
| Merge the article template | **Travel Guide is part of the main prototype.** The guide index lists all 24 production guides with production's photos and filters by place. Every guide opens in the approved article template with its real text, its trips from the trip data (facts, options, partners, free cancellation), the ♡, and links that stay inside the prototype. |
| Food trip photo | **New photos.** All are free Unsplash photos tagged "Cairo, Egypt", checked on 17 Oct: a bread seller in a Cairo street (Omar Abozeid) for the trip, the home "Food" mood and the experiences rail; baladi bread (Alex Block) and Egyptian biscuits (mohmed Afify) for two of the stops; a Cairo street (Ahmed Assem) for "Combine it with". |

**Note for production:** the same non-Egyptian photo (`1661994215679-cde7c2c5c060`) is used on the live site for this trip and for the "Food" mood. It needs the same replacement there.

## 2. Production content the prototype had dropped (now restored)
Production's homepage has five sections that proposal 4 had removed:
- Seven places (`#destinations`);
- Everything you need, compared (`#partners`);
- Moments we'd book again (`#experiences`);
- The Roamer Journal (`#guide`);
- Ready for your Egypt story?, the Trip Builder (`#planner`).

They are back in production's order, with production's data and photos. They run on the theme's own components, bundled with `tools/build.py`, with three adaptations: photos load from files, "phone width" follows the site frame, and the ♡ uses the one saved list.

**Also restored:** the legal and contact pages, the full guide list (24, not 2) and "Best Time to Visit".

## 3. Expert review: problems to decide (not changed yet)
These are design choices, not bugs, so they wait for the owner.

1. **The homepage is very long.**
   - It has: the story (4 chapters), the interlude, moods, seven places, the map, partners, experiences, the journal and the Trip Builder.
   - On a phone that is about 25 screens before the footer.
   - The visitor is offered a place four times: "Where in Egypt?", the moods' "Where to go", the seven places and the map.
   - **Suggestion:**
     - keep the story, the moods, one place picker (the seven places or the map, not both) and the Trip Builder;
     - move "Moments we'd book again" to Trips & tours;
     - move the journal to the Travel Guide.
2. **The partners section has one tab ("Hotels") with no offers in it.** On production it renders the editorial hero only. One tab looks unfinished.
   - **Suggestion:** until there are hotel offers, replace it with the Trip.com hotel rows the trips already show ("Staying in Luxor?").
3. **Two ways to say "trips":** "Moments we'd book again" (production cards with "Check Availability" buttons that go straight to the partner) and the chapter and browse cards ("See options" on our own page).
   - **Suggestion:** every trip card opens our trip page first, where the options are compared. One behaviour everywhere.
4. **Production's "Experiences" and "Destinations" labels** are still used inside the restored sections ("All destinations", "Handpicked experiences"), while the menu says Places and Trips & tours. These need to match on production too.
5. **The floating "Trip assistant" button** still covers text on phones while the page is still. It hides only while the visitor scrolls down.
   - **Suggestion:** on phones, show it as an icon in the header next to ♡ instead of a floating button.
6. **The Trip Builder drafts a route but cannot book it.** Its result lists trips; each should open the trip page. Production behaves the same.
7. **Guides with several places** ("Egypt", "Hurghada & Sharm El Sheikh") show the first trip from their list as "The trip for this guide". For itineraries (7, 10 and 14 days) one trip is too narrow.
   - **Suggestion:** itinerary guides show a "Trips on this route" list instead of a single trip.

## 4. Same problems on production today
- No search on phones.
- Unclear menu labels (Destinations vs Experiences).
- The Cairo food photo.
- The partners section with an empty tab.

These need fixing on the live site when the design is rolled out.

## 5. Applied (owner: "apply the seven suggestions", 17 Oct)
Built by `v4-round7.py`, with the route guides in `v4-guides.py`. Checked with `tour.mjs`: 43 screens each at 1440, 840 and 390 px, all looked at full size. There are no script errors.

1. **Shorter homepage:** story, interlude, moods, the map (the owner asked for it on 16 Oct), Where to stay and the Trip Builder.
   - "Seven places" is on the Places page; "Moments we'd book again" repeated Trips & tours; the Journal repeated the Travel Guide. All three left the homepage. The homepage is now about 9,000 px tall.
2. **Where to stay** replaces the empty partners tab: Trip.com's hotel areas for each of the seven places, from the same hotel rows the trips show (Cairo: Giza, Downtown, Zamalek, Cairo; Luxor: Luxor, West Bank; and so on).
3. **One trip behaviour:** every trip opens our trip page, where the options are compared. Partner links appear only on the trip page.
4. **One vocabulary:**
   - "Trips & tours" on the browse page;
   - the map card says "See the place and its trips";
   - the moods' place card says "See its trips";
   - the Trip Builder's list says "Trips on your route".
5. **Phones:** the Trip assistant is an icon in the header between 🔍 and ♡. The floating button is gone, so nothing covers the text.
6. **Trip Builder:** each partner tour on the route opens its trip ("Compare options"). Tours with no trip page are not listed.
7. **Route guides** (7, 10 and 14 days, best time, hidden gems, day trips, and any guide with three or more trips):
   - the box, the end band and the phone bar show "Trips on this route", each with its facts and free cancellation, instead of one trip;
   - single-trip guides are unchanged.
