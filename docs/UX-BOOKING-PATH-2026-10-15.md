# Booking path review (browser): 15 Oct 2026, theme 1.2.50

Reviewed on production in real Chrome at 1366 px and 390 px: experience, destination, guide and home planner pages.

## Findings before the change

| Where | Problem |
|---|---|
| Experience / destination pages, below 1200 px | The booking box came after the whole article. The sidebar is only sticky on wide screens. |
| Guides | There was no booking link inside the article; the cards came only at the very end, after the FAQ. |
| Mobile dock | The main button was always "Plan My Trip", even on a page about one bookable trip. |
| Home trip planner | It drafted a route but ended in "Explore Egypt". Nothing connected it to the partner tours. |
| Viator landing | With affiliate parameters, Viator opens its "Explore {city}" page with the chosen trip pinned first. This is Viator's design (see VIATOR-CONTENT-MATCHING-REPORT.md). |

## Changes (theme 1.2.50)

- **Hero:** a "Book with our partners" button that jumps to the page's booking box (`#book`, or `#plan` on the Abu Simbel story).
- **Mobile dock:** on every page with live offers, the main button is "Check Availability" and goes to the booking box, which shows the disclosure.
- **Guides:** a booking box after the second section, with the offers of the guide's experiences.
- **Planner:** the drafted route lists the partner tours for its stops (up to 2 per destination), with `/go/` links (placement `home-planner`) and the disclosure.

## Production check

| What was checked | Result |
|---|---|
| Experience, destination, Abu Simbel story and guide pages | Hero CTA and dock CTA go to the right box |
| Guide box, in AR, DE and ZH | Translated |
| Planner tours, in EN, AR, DE and ZH | Translated, with correct `/go/` slugs |
| JS errors | None |

## Visits (15 Oct)

- **First-party counter:** it started on 15 Oct, and its 29 views are QA traffic. The 41 `/go/` redirects include our redirect tests.
- **Search Console:** over 28 days (data to 12 Oct), 2 clicks, 37 impressions and an average position of 38.7. Queries include "اسوان" and "great sand sea".
- **Context:** the site has been indexable only since 2 Oct, and Google's noindex validation started on 15 Oct.

## Next checks

| Date | Check |
|---|---|
| 16 Oct | Submit the remaining 49 URLs to Bing |
| 17 Oct | Visitors in the Viator dashboard |
| 22 Oct | GSC validation and impressions |
