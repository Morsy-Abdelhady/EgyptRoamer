# Prototype review: proposal 4 and the article template (16 Oct 2026)

**Owner's request:** review every screen on desktop, tablet and phone, and list the problems and suggestions.

**Method:**
- Playwright captured 90 screens of both prototypes: 1440 px desktop, an 840 px tablet frame and a 390 px phone frame.
- Each screen was checked automatically for sideways scroll, small tap targets and text under 10.5 px, then looked at by eye.

**Automatic checks:**
- No screen scrolls sideways.
- There are no script errors.

## Owner's additions during the review
- **Missing production features:** the prototypes leave out the chat (Trip assistant), favourites, search and the full footer.
- **Language switcher:** the prototypes have none either.

All of these exist on the live site and must be in the approved design before rollout.

## A. Problems (fix before approval)

| # | Where | Size | Problem |
|---|---|---|---|
| A1 | All pages | All | The live site's header tools are missing: search, favourites and the language switcher. So are the floating Trip assistant and the full footer (newsletter, links, partners note, legal). |
| A2 | Header | Tablet (600–900 px) | The nav links are hidden and there is no ☰ menu. Only "Choose a trip" is left, so a tablet visitor cannot reach Destinations or the Travel Guide. |
| A3 | Trip, "Choose your way in" | Tablet and phone | The trip photo is full width and very tall (4:5) above the options, so the visitor scrolls past a whole screen of photo before the first Book button. |
| A4 | Browse, trip cards | Tablet | The cards drop to one column, so each card is a full-width photo; the first card's text sits below the screen. Two columns fit at this width. |
| A5 | Trip pages | Phone | The bottom booking bar covers the last lines of a section (for example "Photography rules…"). The page needs room under the content for the bar. |
| A6 | Trip opener eyebrow | Phone | "Luxor · The West Bank · Ancient Thebes" wraps onto two letter-spaced lines and reads as broken. |
| A7 | Phone tap targets | Phone | Several are too small to tap reliably (under the 44 px guideline): "Skip to booking" (22 px), "Compare side by side" (24 px), "Read the Cairo guide" (20 px), "Explore Destination" (25 px), the breadcrumb links (16 px), and the logo link (31 px). The video buttons are 32 px. |
| A8 | Small text | All | "Where to go" (9.6 px) and "Capital · Nile Valley" (9.3 px) in the live moods and map sections, the photo credits in the article hero (10 px), and the logo strapline (8.5 px). |
| A9 | Article hero excerpt | Phone | The excerpt is cut at four lines with "…", so a visitor never sees the end of the sentence. A shorter summary line is better than a cut one. |
| A10 | Article on tablet | Tablet | The booking box drops under the article, so a tablet visitor reads the whole guide without a booking control. The phone's bottom bar should also show on tablet. |

## B. Suggestions (experience and design)

1. **Bring the live site's header and footer in unchanged** (fixes A1 and A2):
   - search, favourites (♡) and the language menu in the header;
   - one ☰ menu below 900 px holding the nav, the language and search;
   - the Trip assistant button floating at the bottom, moved up when the booking bar is on screen;
   - the live footer.
2. **Language:**
   - the prototype should show the language menu with all 8 languages, and Arabic in RTL for at least one page;
   - the live theme already handles RTL (`rtl.css`), so the prototype's new parts (worlds, article template) should be checked in Arabic before rollout.
3. **Booking section on tablet and phone (A3):**
   - show the trip photo as a short banner (16:7) or a small thumbnail next to the title;
   - the options and the first Book button must be on the first screen of the booking section.
4. **One bottom booking bar on every trip and guide at phone and tablet widths:**
   - the same look and behaviour in both templates;
   - it hides only where a Book button is already on screen;
   - the page gets bottom padding equal to the bar (A5).
5. **Favourites:** a ♡ on every trip card and on the trip opener, since the live site already has favourites. Visitors who are comparing trips can save them and come back.
6. **Tap targets and type (A7, A8):** set a 44 px minimum touch height for links and buttons on phones and an 11 px floor for small labels. Show photo and video credits in the "i" button instead of tiny text.
7. **Phone opener:**
   - shorten the eyebrow to the place only ("Luxor · West Bank");
   - the lead to two sentences;
   - so that "Book this trip" is on the first screen without scrolling.
8. **Tablet browse:** two columns of trip cards; the place banners keep one row.
9. **Trip worlds on phone:**
   - the step scenes are tall (640 px each), so three steps are three screens of mostly photo;
   - on phones, show the step text cards in a row the visitor swipes, over one shorter scene.
10. **Homepage length on phone:**
    - the story, moods, map and four chapters make a long scroll before any trip list;
    - keep "Choose a trip" and "Where in Egypt?" in the hero (both are there) and add a short "Popular trips" row after chapter 1.

## C. What works well (keep)
- **Booking stays in reach:** visitors can book while reading. The capsule or bottom bar is on every trip, and the booking box sits beside every guide.
- **Cancellation is clear:** free cancellation shows on every option, and the comparison table is useful.
- **Media sits with its content:** the videos and the places strip sit next to the text they illustrate, each with its own controls.
- **The live moods and map** now match production exactly.
- **No sideways scroll** at any size.

## Next step
1. Fix A1–A10 and apply suggestions 1–4, 6 and 7 in the prototypes.
2. Show them again on desktop, tablet and phone, in English and Arabic.
3. Suggestions 5, 8, 9 and 10 are the owner's choice.

## Fixes applied (16 Oct, owner: "fix the ten problems and suggestions 1–4, 6 and 7")
Both prototypes were republished. They were checked at 1440 px, 840 px and 390 px, and in Arabic (RTL). There is no sideways scroll and no script errors.

- **A1, A2, suggestion 1:** the live site's chrome is now in both prototypes.
  - It is captured by `chrome-capture.mjs` and placed by `chrome.py`: header (search, ♡ saved, language menu, Plan My Trip), the ☰ menu at tablet and phone widths, the search overlay, the saved drawer, the Trip assistant and the footer.
  - The live CSS applies only to the chrome, never to the prototype's own elements.
  - Search filters the live list. The assistant answers from the live page list plus the prototype's own trips and guides. Footer forms send nothing.
- **Suggestion 2:** the language menu works.
  - Arabic turns the page right-to-left. The prototype's text stays English, and a note says so.
- **A3:** on tablet and phone the booking photo is a 16:7 banner.
- **A4:** two columns of trip cards on tablet.
- **A5, A10, suggestion 4:**
  - in trips, a solid bottom booking bar on tablet and phone, with room left under the page;
  - in guides, the bottom bar also shows on tablet;
  - the assistant button moves up while a bar is on screen.
- **A6, suggestion 7:** on phones the trip opener shows the place only, and the booking buttons come before the long text.
- **A7, suggestion 6:** touch targets of at least 44 px on phones, including the breadcrumbs, section tabs and links. The video buttons are 40 px with a larger hit area.
- **A8:** no label under 11 px.
- **A9:** the guide summary is never cut.

## The owner's review of the live chrome (17 Oct) and the fixes (`chrome2.py`)
The live features had been copied without a design review. The owner found the problems below; each was fixed, then checked on desktop, tablet and phone.

| Problem | Fix |
|---|---|
| The phone menu covered the header, so nothing closed it | The menu opens under the header, and ☰ turns into ✕ (label "Close menu"). |
| No search on phones (also missing on the live site) | 🔍 is in the header at every size, and "Search places, trips and guides" is the first item in the menu. |
| The menu's "Plan My Trip" differed from the header's button | One main button everywhere: "Choose a trip", or "Book this trip" inside a trip. The menu button copies the header's. Plan My Trip is a plain menu link. The header button stays outlined, as approved on 16 Oct. |
| "Destinations" and "Experiences" were unclear | They are now "Places" and "Trips & tours" in the header, the menu and the footer, and each menu item has a line saying what it holds. |
| The assistant answered with bare lines | Answers are trip cards (photo, place, length, options, ✓ Free cancellation, "See the trip"). A place adds "See all ⟨place⟩ trips". With no match, it suggests places. Place chips sit in the welcome message, replacing the second row of suggestions. |

**Found in the self-review and fixed:**
- **Assistant layout:** a trip card overlapped the question box. The conversation now scrolls in its own area.
- **Team chat:** "Cancel" in the team-chat form did nothing. It now returns to the assistant.
- **Search:** the places in the search list had no link; they now open their trips.
  - The search lists popular trips first.
  - Its wording matches the menu.

**Note for the live site:** the missing phone search and the unclear menu labels exist on production now. They need the same changes when the design is rolled out.

## More from the owner (17 Oct)
- **Team chat:** after "Chat with Egypt Roamer", the form ran past the panel, so its buttons could not be reached.
  - Fix: the form now takes the whole panel and scrolls. "Cancel" returns to the assistant.
  - Checked at 700 px and 900 px desktop heights, on phone and on tablet.
- **Footer logo:** it was huge (the live footer draws it up to 880 px wide). It is now at most 260 px.
- **Places and Trips & tours opened the same list:** the prototype had no places page.
  - There is now a **Places** screen: the seven places, each with its region, line and best time, a "N trips" button to its trips, and "Read about …".
  - The difference: **Places** helps the visitor choose where to go, and **Trips & tours** is what they can book.
- **The ♡ did nothing:** nothing could be saved. Now:
  - a ♡ sits on every trip card and mini card, "♡ Save" on the trip opener and on the guide's booking box, and a ♡ on every place card;
  - the ♡ in the header shows a count;
  - "Saved for later" lists each saved item with its photo and facts; it opens the item or removes it.
  - Items are kept in this browser only, as on the live site.

## Full-size review (17 Oct)
**What went wrong before:** earlier reviews looked at shrunken contact sheets and automatic checks (overflow, errors). Problems like the crushed cards were invisible at that size.

**How it was done this time:**
- `tour.mjs` captures every screen of both prototypes (34 per size) on desktop, tablet and phone.
- Every image was read at full size, one by one.

**Found and fixed:**
1. **Places page** (owner's screenshot): the ♡ sat alone on its own row, there were three stacked buttons with no clear main one, and "1 trip →" said nothing.
   - The trip counts were also wrong: Siwa counted the Bahariya safari, and Hurghada and Sharm counted each other's trips.
   - The page was rebuilt:
     - the ♡ sits on the photo;
     - the place's own trips are listed by name with length and options;
     - there is one gold button ("See all N trips" or "See the trip") and a quiet "About ⟨place⟩" link;
     - the cards are equal height.
2. **Chapter trip cards** on phone and tablet were crushed to one word per line. The ♡ wrapper had broken the swipe row. They now show one full card on phones and two on tablets.
3. **Hearts on mini cards** sat over the end of the title. The title now keeps room for the ♡.
4. **Browse:** the first banner's video buttons covered the sticky filter bar and hid "10 trips". They now sit under it.
5. **Trip opener on phones:** three full-width buttons were stacked (Book, Save, Go down). Save is now a ♡ beside "Book this trip".
6. **The floating assistant button** covered body text on phones. It now steps aside while scrolling down and returns on scroll up.
7. **"More on Luxor":** the first guide had an empty glyph panel. It now uses the exact-tagged Hatshepsut temple photo.

**Seen and left as they are:** "Free cancellation on every option" is shown above the options, and each option also carries ✓ Free cancellation. This repeats on purpose; it is the visitor's main safety point.
