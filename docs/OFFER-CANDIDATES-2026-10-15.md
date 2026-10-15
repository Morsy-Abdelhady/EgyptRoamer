# Second Viator offer per experience: candidates for owner approval (15 Oct 2026)

**Goal:** a choice on each experience page, to sit next to the current offer (PM review, recommendation 5). The current offers are mostly private tours, so each candidate is a different format: group, budget, or a combined itinerary.

**Source:** viator.com search results, read in Edge on 15 Oct. The ratings and review counts are as Viator showed them. Prices are not copied (they change).

**Before publishing, for each approved line:**
- open the product page;
- check the itinerary, inclusions and start point against our page, as in `VIATOR-CONTENT-MATCHING-REPORT.md`;
- create the offer;
- add its 7 translations to Core `data/offers-i18n.json`;
- run `tools/qa/page-coverage.mjs`.

| Experience page | Current offer (format) | Candidate (product code) | Rating seen | Why it fits |
|---|---|---|---|---|
| Pyramids of Giza & Sphinx | Private half day (308879P1) | Giza Pyramids, Sphinx & Grand Egyptian Museum Tour, full day (d782-41437P6) | 4.9 (651) | Pyramids + GEM on the same day, as on day 2 of our 7-day itinerary |
| Abu Simbel day trip from Aswan | Private full day (221454P2) | Full Day Tour to Abu Simbel Temples from Aswan (d796-128688P15) | 4.4 (359) | A lower-cost shared option. Check that it is shared, not private |
| Red Sea Diving: two reef dives | Certified divers only (13172P2) | Red Sea Diving Trip from Hurghada: Beginner to Advanced Divers (d800-66938P5) | 4.9 (593) | Our page also covers beginners, which the current offer excludes |
| Valley of the Kings & Hatshepsut | Private full day (224493P18) | Full Day Tour to Luxor West Bank (d826-224493P76) | 5.0 (146) | Same operator, lower-cost format. Check whether it is a group tour |
| Nile Dinner Cruise with Live Show | 2-hour cruise (37291P188) | Cairo Dinner Cruise in Nile River With Belly Dancer show (d782-110498P6) | 4.9 (829) | The highest-rated cruise. Check that the tanoura show is included (the URL mentions it) |
| Cairo Street Food Tour by Night | Evening tour, max 8 guests (69673P17) | Cairo Food Tour adventures +40 Tastings (d782-476624P1) or Cairo Street Food with a Local Family (d782-147895P4) | 4.9 (252) / 5.0 (187) | Check which one runs in the evening, as our page describes |
| White Desert & Black Desert Safari | 2 days, 1 night (139021P1) | Overnight White Desert Safari Camping from Cairo (d782-10124P65) | 4.7 (93) | A second operator for the same overnight trip |
| Great Sand Sea 4×4 & Hot Springs | 3 days from Cairo (387546P1) | — | — | No candidate. Siwa day safaris on Viator have very few reviews (see the main report) |

## Status (15 Oct)

The owner approved all of them. Each was checked on its product page, then published:

| Offer | Product | Product-page check |
|---|---|---|
| #579 | 41437P6 | The itinerary includes the GEM, 3 h, ticket included |
| #581 | 128688P15 | Shared shuttle, max 15, entrance fees included |
| #583 | 66938P5 | Beginners to advanced, 2 dives, instructors |
| #585 | 224493P76 | Private; Medinet Habu, Deir el-Medina, Valley of the Kings |
| #587 | 110498P6 | Belly dance and tanoura with live music confirmed |
| #589 | 476624P1 | 6:00 pm start; chosen over 147895P4, whose start time is not shown |
| #591 | 10124P65 | Bahariya, Black Desert, Crystal Mountain, White Desert, overnight camp |

- **Display:** each new offer shows second on its experience page (priority 5), translated in 8 languages.
- **Release gate:** 168 pages, all ok.
