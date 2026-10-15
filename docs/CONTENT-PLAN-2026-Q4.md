# Content plan: Oct–Dec 2026

**Pace:** 2 guides a week, Arabic and English first, then the other 6 languages through the usual translation review.

**Rules (CLAUDE.md):**
- Bodies in `content/editorial/en/*.md`, verifiable facts only, sources in `docs/CONTENT-SOURCES.md`.
- No prices, opening hours, travel times or ratings.
- Publishing: the owner gave full authority to publish and edit guides (2026-10-15). Each guide goes live in all 8 languages, then `tools/qa/page-coverage.mjs` runs.

**Choosing a guide:** each one answers a question someone asks before booking, and it links to a verified offer (or a destination page with one).

| # | Guide (working title) | Search intent it answers | Links to (booking) | Facts to source before writing |
|---|---|---|---|---|
| 1 ✅ published 15 Oct (`g-abu-simbel`, 8 languages) | Abu Simbel from Aswan: how to plan the day | "abu simbel from aswan", "رحلة أبو سمبل من أسوان" (Aswan is our first query with impressions) | Abu Simbel offer (221454P2) + candidate shared tour | UNESCO Nubian Monuments listing; Ministry of Tourism and Antiquities site page; the sun-alignment dates (22 Feb / 22 Oct), from an official source |
| 2 ✅ published 15 Oct (`g-nile-cruise`, 8 languages) | Nile cruise or hotels: Luxor to Aswan | "nile cruise luxor aswan", "كروز نيلي من الأقصر لأسوان" | Luxor and Aswan destination offers; Valley of the Kings offer | Stops between Luxor and Aswan (Esna, Edfu, Kom Ombo) from official/UNESCO sources. No durations or prices |
| 3 ✅ published 15 Oct (`g-day-trips-cairo`, 8 languages) | Day trips from Cairo | "day trips from cairo", "رحلات يوم واحد من القاهرة" | Alexandria (59626P4), Giza (308879P1), White Desert (139021P1), food tour | Site facts for Alexandria (Bibliotheca Alexandrina, Qaitbay), Saqqara/Dahshur (UNESCO Memphis and its Necropolis) |
| 4 ✅ published 15 Oct (`g-snorkelling`, 8 languages) | Snorkelling in Egypt: Hurghada or Sharm El Sheikh | "snorkeling hurghada or sharm", "سنوركلينج شرم ولا الغردقة" | Ras Mohammed (5616599P11), Hurghada diving (13172P2) + beginner candidate | Ras Mohammed National Park (declared 1983, Egypt's first protected area: EEAA source); Giftun Islands protected area |
| 5 ✅ published 15 Oct (`g-white-desert`, 8 languages) | A night in the White Desert: what to expect | "white desert camping", "الصحراء البيضاء تخييم" | White & Black Desert offer (139021P1) + candidate | White Desert Protected Area (EEAA); Bahariya as the gateway (already sourced for the experience page) |

## Also on the list

- ✅ **Experience pages for Alexandria and Sharm,** published 15 Oct in 8 languages (`exp-alexandria` #601, `exp-ras-mohammed` #602). They are linked to offers #574 and #577.
- ✅ **Second batch, published 16 Oct** (8 languages): `g-aswan-temples`, `g-desert-choice`, `g-valley-kings`, `g-10-days`, `g-cairo-night`.
- **Next:** pick the next 5 guides from the GSC queries after the 22 Oct check (scheduled task).
- **Arabic first for guide 1.** The site already gets impressions for "اسوان" and "معابد اسوان".

## Measure

For each guide, 4 weeks after it is published:
- GSC impressions and clicks for its target queries;
- partner taps from it (Visits → partner-link taps by page);
- Viator clicks on its campaigns (`er-{lang}-guide-inline`, `er-{lang}-guide-aside`, `er-{lang}-guide-related`).

Record them in `docs/KPI-WEEKLY.md`.
