# Booking journey review: 2026-10-16 (evening)

Owner-facing summary (Arabic): https://claude.ai/artifact/3ZEYDCsFA6RUgs7hTNubRm

**Owner request:** review the whole user journey, keep it simple but rich in the information and decisions a traveller needs, and propose improvements.

**Method.** I measured the live site (theme 1.2.73, Core 1.2.50) with Playwright, using `journey-audit.mjs` in the session scratchpad.
- **Pages:** 8 page types (home, destinations archive, Luxor, experiences archive, Valley of the Kings, the Abu Simbel story, guides archive, Best time to visit).
- **Variants:** English and Arabic, at 1440×900 and 390×844, the current design and `?ux=2`.
- **Per page:** page length in screens, the screen where the first partner link appears, every gold button (label, target, position), the fixed bars, and the section order.
- **Also:** screenshots, plus the text of the cards, the planner and the panel.

No code was changed. These are proposals for the owner to approve.

## 1. What a visitor meets today (measured)

| Page | Length, phone (screens) | First partner link, phone | First partner link, desktop |
|---|---|---|---|
| Home | 19.8 | screen **11** | screen 11.6 |
| Destinations archive | 6 | none | none |
| Luxor (destination) | 19.6 | 3.6 (the "Book Luxor" section is at 11.5) | 1.1 (sidebar) |
| Experiences archive | 7.7 | 1.2 (direct off-site button on each card) | 1.3 |
| Valley of the Kings, current | 11.1 | 2.7 | 1.2 |
| Valley of the Kings, `?ux=2` | 10.4 | **4.9** (the panel now comes after the FAQ) | 1.2 |
| Abu Simbel story | 17.2 | **10.8** | 11.2 |
| Guides archive | 9.5 | none | none |
| Best time to visit (guide) | 14.5 | 3.7 ("Experiences in this guide" at 7.4) | 1.1 |

Arabic gives the same picture, with pages 5–10% shorter. No JavaScript errors on any page.

## 2. Findings

1. **Trips arrive late.**
   - On the homepage the first trip is 11 screens down a 19-screen page.
   - On the Abu Simbel story it is about 10 screens down.
   - On a destination page on a phone, "Book Luxor with our partners" is at screen 11.5.
2. **`?ux=2` moves the booking panel lower on phones.** On the Valley of the Kings it moves from 2.7 to 4.9 screens. The bar opens the bottom sheet, but a reader who scrolls meets the FAQ before the options. This needs fixing before `er_ux2` goes on.
3. **Too many words for one action.**
   - English gold labels: Plan My Trip, Explore Egypt, Build My Trip, Check Availability, Book Luxor with our partners, Book with our partners, Book this trip, Book, See hotels.
   - Arabic: خطّط رحلتي، صمّم رحلتي، استكشف مصر، تحقّق من التوفّر، احجز عبر شركائنا، احجز الرحلة، احجز، شوف الفنادق.
   - The header button changes with the page (Plan My Trip / Check Availability / Book this trip). On the archives, "Plan My Trip" goes to `https://egyptroamer.com/#planner`, which leaves the page for the homepage.
4. **Archive cards skip the decision.**
   - Each card's gold "Check Availability" goes straight to `/go/` (the partner), so the visitor never sees the options, the facts or the hotels.
   - The cards show only place, title and "via Viator": no duration, no private or group, no number of options.
5. **Phone chrome takes about a quarter of the screen.** The booking bar (53px), the dock (62px) and the assistant button (46px) sit on top of each other: about 160–200px of 844.
6. **The trip page has the decision facts, but in two places.** The hero shows place and duration; the panel shows each option's private or group status, duration and pickup. Nothing at the top says "3 options from 2 partners, from 4 to 10 hours, pickup offered".
7. **Destination and guide pages have no panel yet** (`?ux=2` changes nothing there). Guides list their trips near the end.
8. **The planner still shows travel times** ("1h flight", "4-night Nile cruise or 3h drive", from `seed.json` `legs`, via `er_payload_legs()`). The 2026-09-29 decision removed travel times from destination facts and the homepage map. The owner needs to say whether the planner keeps them.
9. **What works:**
   - **The trip panel:** one title, a photo per option (now Viator's own), the partner chip, partner-stated facts, one gold button per row, and hotels below.
   - **The phone bottom sheet.**
   - **Arabic:** translations and layout are complete.

## 3. Proposals (in order)

The aim is one simple path: **choose a place or an activity, open one trip page, compare the options, book with the partner.** Every proposal uses only facts we already hold: approved editorial text and partner-stated facts.

| # | Proposal | Effect |
|---|---|---|
| P1 | **Trip page, phone:** put the booking panel right after the hero summary, not after the FAQ (fixes finding 2). | First option at about 1.2 screens instead of 4.9 |
| P2 | **"Before you book" summary** under the hero, from existing data: place, the page's duration, number of options and partners, private and/or group, pickup offered (when partners state it), best months (from the destination). | The decision in 10 seconds |
| P3 | **"Our pick" with a reason**, a partner-stated fact only (e.g. "the only private option with pickup"). | A decision, not just a list |
| P4 | **Archive cards open the trip page** (earlier decision 2). Each card shows duration, "3 options · 2 partners" and the photo. No off-site button on cards. | The visitor sees the options before leaving |
| P5 | **Three action words only:** "احجز الرحلة / Book this trip" (opens the panel), "احجز / Book" (goes to the partner), "شوف الفنادق / See hotels". The header button is "Book this trip" on trip pages and "Choose a trip" everywhere else, going to the experiences archive, not the homepage. | Removes 6 labels |
| P6 | **Homepage:** a first screen with 3 choices ("I know where I'm going" → destinations; "I want an experience" → trips; "Help me plan" → planner), trips right after the hero, and the page reduced from about 19 to about 10 screens. Same approved CSS and sections, reordered and trimmed. | First trip at screen 2 instead of 11 |
| P7 | **Phones: one fixed bar.** On trip pages: the "Book this trip" button plus the assistant icon. Elsewhere: the dock. Never both. | About 100px of screen back |
| P8 | **Destination pages:** a "Top trips in Luxor" panel (3 options plus hotels) right after the intro, and the full list where it is now. | Booking while reading |
| P9 | **Guides:** a compact "Trips in this guide" box in the sidebar (desktop) and after the short answer (phone). | Guides lead to trips |
| P10 | **Abu Simbel story:** the panel after the first chapter, as on other trips. | Partner at about 2 screens instead of 10 |
| P11 | **Planner:** ends with one trip and one hotel per city (already partly there), and the travel-time decision (finding 8). | A bookable plan |

**Suggested rollout:** P1 + P2 + P3 (trip page), then P4 + P5, P7, P6, P8–P11. After each step: page-coverage on every page and language, plus a browser check on desktop and phone, English and Arabic.

## 4. Decisions needed from the owner

1. Approve the trip page (`?ux=2`) with P1–P3, then switch on `er_ux2`.
2. Archive cards with no off-site button (P4).
3. The three action words (P5).
4. Phones: one fixed bar (P7). This replaces the bar plus dock that the owner asked for earlier.
5. Planner travel times: keep or remove (finding 8).

## 5. Status (16 Oct, after the owner's go-ahead)

The owner said: go ahead on judgement, without visitor data (there is no traffic yet).

- **Done in the `?ux=2` preview (theme 1.2.74 and 1.2.75):** P1, P2 and P3. Visitors see no change until `er_ux2` is switched on.
- **P1:** on phones the panel sits at screen 0.8–1.0, measured on all 18 trip pages in English and Arabic. Before, it was at 3.3–4.7. Desktop is unchanged (sidebar at about 1 screen).
- **P2 and P3** are checked on the same pages. The pick shows a reason on 3 trips (Nile dinner cruise, White Desert, Great Sand Sea). On the others no single partner-stated fact sets the pick apart, so no reason is shown.
- **Not done:**
  - the GoDaddy cache flush. Edge was disconnected, so cached pages still link the old CSS; the check above used fresh pages.
  - P4–P11.

**Later on 16 Oct (theme 1.2.76–1.2.77):** P4, P5 and P7 are done in the preview. The owner asked for decisions by tour-site practice, not visitor data, since the site is still being indexed.
- **Checked fresh (cache-busted):** home, experiences archive, Luxor, Best time, Valley of the Kings; English and Arabic; 390px and 1440px.
  - The cards (server-built and the homepage rail) open the trip page with "See options" and carry no partner link.
  - The header, menu and dock say "Choose a trip" (to the trips or the page's box) and "Book this trip" on trip pages; nothing goes to the homepage.
  - On a trip page on a phone the dock is gone and the bar sits at the bottom.
  - The preview keeps `?ux=2` on internal links and is `noindex`.
  - page-coverage passes.
- **Not done:**
  - ~~the GoDaddy flush~~: done once Edge reconnected; the cached preview pages were rechecked and pass.
  - The homepage rail cards show no shared facts, because the approved JS renders them.
  - P6 and P8–P11.

## 6. Second review (16 Oct, evening, after P1–P5 and P7): the next step to approve

**Re-measured** in the `?ux=2` preview with the same script, every page type, English and Arabic, 390 and 1440 px.

| Page (phone) | Screens | First trip / partner | Booking box |
|---|---|---|---|
| Trip page (Valley of the Kings) | 10.4 | **1.2** (was 4.9) | new panel, first |
| Home | 19.6 | first trip card **12.2**; first partner link 11 (the "Compare options" partner panel) | none |
| Luxor (destination) | 19.4 | 3.6, old inline box ("Check Availability · via Viator", no photo or facts); the trip cards are at 10.7; full box at 11.3 | old box |
| Best time (guide) | 14.3 | 3.7, old inline box; trip cards at 7.6 | old box |
| Abu Simbel story | 17.2 | 10.8; the panel at 14.4 | old box |

**What is left, in order of harm:**
1. **Two booking designs side by side.** Trip pages have the new panel (photo, partner chip, partner-stated facts, "Book", hotels, one bar on phones). Destinations, guides and the Abu Simbel story keep the old box, with "Check Availability · via Viator", no photos or facts, a separate hotel box, and the bar plus the dock on phones. A visitor going from Luxor to a Luxor trip meets two ways to book. This was the owner's first complaint (16 Oct, "inconsistent, I don't know where to go").
2. **Home: the first trip is 12 screens down.** The pinned scene sequence takes about 5 screens. Then come the moods (6.3), destinations (7.4), the map (8.6), the partners panel (9.8), the trips (11.7), the journal (13) and the planner (15.4).
3. **Abu Simbel story:** the booking panel is 14 screens down.

**Recommendation, step A (approve first): one booking panel on every page.**
- **What:** destinations, guides and the Abu Simbel story use the same panel as the trip page.
  - Destinations: the top 3 trips plus the destination's hotels.
  - Guides: the trips the guide covers, plus hotels when the guide is about one destination.
  - The story: its own trips and hotels.
- **Placement:** in the sidebar on desktop. On phones, right after the intro (destinations, guides) or after the first chapter (the story).
- **Phones:** the same single bar as on trip pages.
- **Unchanged:** the full "Things to do" list stays where it is.
- **Why first:** every page books the same way, which fixes finding 1 and finding 3. Then `er_ux2` can be switched on for visitors without mixing two designs.
- **Risk:** small. It reuses the panel already checked on 18 trip pages and is built in the preview first.

**Step B, after A: the homepage.** It is the approved prototype, so the owner chooses:
- **B1 (recommended):** move the "Handpicked experiences" rail up to right after the scene sequence, so the first trip is at about screen 6 instead of 12. Only the section order changes; sections, CSS and animation stay.
- **B2:** B1, plus three choices in the hero ("I know where I'm going" / "I want an experience" / "Help me plan") in place of "Explore Egypt / Watch the film".
- **B3 (not recommended):** shorten the pinned scene sequence. That changes the approved design itself.

**Not proposed now:** P11 (planner) and the travel-time decision, until A and B are done.
