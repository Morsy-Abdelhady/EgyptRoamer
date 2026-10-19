# The site through a first-time visitor's eyes (20 Oct 2026, theme 1.2.99)

Owner: "review the site as a foreign tourist planning a trip to Egypt; tell me the problems and suggestions for UX
and design". Walked the live site on a 1440×900 desktop and a 390×844 phone: homepage → Trips & tours → a trip
(Giza) → Book / Compare → a guide (7 days) → Places → Luxor. Screenshots: session scratchpad `ph/t-*.png`.
No JS errors; homepage LCP about 2.0 s on desktop and 0.5 s on the phone.

## Fixed during the review
- **Phone homepage:** the chapter trip cards were squeezed into narrow columns, one word per line (a regression
  from 1.2.97, when the heart was added). Fixed in 1.2.99 and checked live.
- **Food mood:** pointed to Alexandria; now Cairo, with a line about Cairo (Core 1.2.56–1.2.58).

## Problems, most important first
1. **No price anywhere.** A visitor compares three options without any idea of cost; the only way to know is to
   leave for the partner. This is the biggest reason a planner leaves without booking.
2. **No ratings or reviews.** Nothing tells the visitor the options are good. Viator's Partner API supplies ratings
   and review counts legitimately (branch `wip/viator-photos`, key pending).
3. **Broken promise in every guide:** "listed in our sources page", but there is no sources page (`/sources/`
   is 404). There is no About page either (`/about/` 404): who is Egypt Roamer, who writes, why trust it.
4. **Place pages (e.g. Luxor) are still the old design:** light article, "Choose a trip" button, old sidebar.
   Everything around them is now proposal 4, so they feel like another site.
5. **Phone: two bars at the bottom** on guides and trips (the dock and the trip bar) take about a fifth of the
   screen.
6. **Homepage is long before the first trip on a desktop:** the four pinned slides take several screens; the
   Desert slide's trips are on screen only briefly.
7. **No dates.** "Book" opens the partner without the visitor's dates; there is no hint of availability or the
   best months on the options.
8. **Hotels are hidden:** there is no "Hotels" in the menu; hotels appear only inside trip pages and at the
   bottom of the homepage.
9. **The saved list and the Trip Builder plan stay in one browser.** They can't be emailed, shared or opened on
   another device.
10. **Translations of the new text are not reviewed** by native speakers (about 430 strings, 7 languages).

## Suggestions, in the order I would do them
1. **"From $X" on each option**, only where the partner's price is read and dated (Viator API, or read on the
   partner page with the date), with "price on the partner's site" otherwise.
2. **Ratings from the Viator API** on Viator options (finish `wip/viator-photos`, paste the key).
3. **An About page and a Sources page** (the sources already exist in `docs/CONTENT-SOURCES.md`); link them from
   the footer and from the line in each guide.
4. **Place pages in the proposal 4 design:** photo opener, its trips as cards, hotels by area, its guides.
5. **One bottom bar on phones:** on trip and guide pages, the dock steps aside while the trip bar shows.
6. **A date field before "Book"** (optional), passed to partners that accept a date in the link.
7. **"Hotels" in the menu**, opening the hotels-by-place section as its own page.
8. **Share / email my plan** for the Saved list and the Trip Builder.
9. **Native review** of the 7 languages, starting with Arabic and German.

## Status, 20 Oct (theme 1.3.0–1.3.1, Core 1.2.59–1.2.60)
| # | Suggestion | Status |
|---|---|---|
| 1 | "From $X" on options | **Done for Viator** (API availability schedule, refreshed hourly, hidden after 24 h). GetYourGuide and Trip.com give this account no data API (links and widgets only). |
| 2 | Ratings | **Done for Viator** (average and count from the API; no review texts). |
| 3 | About + Sources pages | **Done**, 8 languages; footer and every guide link to them. |
| 4 | Place pages in the new design | **Done** (`template-parts/guide-p4.php`, place mode). |
| 5 | One bar on phones | **Done** (the dock steps aside while the trip bar shows). |
| 6 | Date before "Book" | **Not done.** Viator and GetYourGuide document no date parameter for affiliate links; a field that the partner ignores would mislead. The date is chosen on the partner's page. |
| 7 | Hotels in the menu | **Done**: a Hotels page in 8 languages, in the main menu. |
| 8 | Share / email the list | **Done** in the Saved drawer (the Trip Builder's "Save this plan" goes into the same list). |
| 9 | Native review of translations | **Owner**: needs native speakers. |
| — | Homepage length / Desert slide | Not changed: the pinned slides stay as the owner asked (18 Oct). |
