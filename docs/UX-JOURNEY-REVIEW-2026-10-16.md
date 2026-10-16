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
