# Guide publishing audit — 2026-10-03

Sources:
- the editorial bodies in `content/editorial/en/g-*.md`;
- the 7 production drafts, read through the authenticated REST API (read only, no edit locks);
- `docs/CONTENT-SOURCES.md`;
- photo provenance from the Unsplash location tags (see `IMAGE-QUALITY-AUDIT-2026-10-02.md` §5).

Production state before this audit: 7 `er_guide` drafts in English, all with "(no author)", none ticked "Ready to index",
no featured image (the seed stand-in photo is used). There are no translations in any language.

## Gate criteria

Each guide is checked for:
- completeness and supportable facts;
- search intent, title, meta description, H1/H2/H3;
- in-content links to destinations, experiences and other guides;
- FAQ quality and a verified image;
- no placeholders, prices or unsupported claims;
- the house style.

## Verdicts

| Guide | Words (prod) | Verdict before fixes | Blockers found | After fixes (1.2.34 + wp-admin) |
|---|---|---|---|---|
| The Best Time to Visit Egypt, Month by Month (`best-time-to-visit-egypt`) | 682 | NEEDS EDIT | "By" with no name; the meta description (a prototype teaser) promised "the one month we'd always avoid", which the guide never names | READY |
| 7 Days in Egypt: The Perfect First Itinerary (`7-days-in-egypt-itinerary`) | 636 | NEEDS EDIT | "By" with no name | READY |
| Cairo Travel Guide: Beyond the Pyramids (`cairo-travel-guide`) | 588 | NEEDS EDIT | "By" with no name | READY |
| 10 Hidden Gems Most Visitors Never See (`hidden-gems-in-egypt`) | 479 | NEEDS EDIT | "By" with no name | READY |
| Is Egypt Safe for Tourists? An Honest 2026 Guide | 0 | BLOCKED — MISSING DATA | No body; needs dated official sources (see `GUIDE-DATA-REQUIREMENTS-2026-10-03.md`) | BLOCKED |
| The Best Nile Cruises, Compared Cabin by Cabin | 0 | BLOCKED — MISSING DATA | No body; a cabin-by-cabin comparison needs real vessel and partner data | BLOCKED |
| What a Trip to Egypt Really Costs in 2026 | 0 | BLOCKED — MISSING DATA | No body; needs dated, sourced prices | BLOCKED |

### Per-guide notes (the 4 written guides)

**Best time to visit.**
- Intent: informational ("best time to visit Egypt", seasonal planning), answered in the first section ("The short answer").
- Structure: H2 short answer → regions (H3 per region) → through the year → Ramadan → how to choose → FAQ (3).
- Facts are conservative and qualitative, with no temperatures or statistics. The Abu Simbel sun festival "on or
  around 22 February / 22 October" matches the sources.
- Links: Cairo, Luxor, Aswan, Hurghada.
- Image: Karnak columns, tagged "Karnak Temple Complex, Luxor".
- Fix: the meta description is now the editorial excerpt.

**7 days in Egypt.**
- Intent: itinerary.
- Structure: plan at a glance → day by day (7) → variations (H3 ×3) → what to leave → plan the details → FAQ (3).
- No prices or schedules; transport is described as options only.
- Links: Cairo, Luxor, Aswan.
- Image: feluccas, tagged "Asuán, Egipto".
- Growth note: no links yet to the matching experiences (Giza tour, Abu Simbel day trip, Valley of the Kings). See
  `SEO-GROWTH-OPPORTUNITIES-2026-10-03.md` §3.

**Cairo travel guide.**
- Intent: city guide beyond the pyramids.
- Structure: walking route (7 numbered steps) → neighbourhoods (H3 ×5) → everyday Cairo → river → FAQ (3).
- Links: Cairo destination, Nile dinner cruise, street-food tour.
- Image: a market scene, tagged "Cairo, Egypt".
- The safety FAQ is ordinary-precaution guidance, not a statistic.

**Hidden gems.**
- Intent: off-the-beaten-path list.
- Structure: 10 numbered H3 under 3 regional H2s, plus a "fitting them in" box.
- Facts:
  - UNESCO: Dahshur is part of the Memphis necropolis site with Giza; Wadi Al-Hitan is Egypt's natural World Heritage Site.
  - Abydos and Dendera are sourced.
- Links: Siwa, Cairo, Luxor, Aswan, Alexandria.
- Image: tagged "Wadi El Hitan, Al Faiyum", one of the ten places in the guide.

Common to all 4:
- no placeholder text, prices, partner claims or booking language;
- the house style matches the approved destination and experience bodies;
- English only, so their translations are not published, as requested.

## Gate fixes applied

1. **Byline:** with no named author, the guide reads "By Egypt Roamer", the publication, as the Article structured
   data already declares. Theme 1.2.34, `single-er_guide.php`.
2. **Best-time meta description:** replaced in wp-admin with the approved editorial excerpt.

Publication itself is recorded in `POST-PHASE-2-EXECUTION-REPORT-2026-10-03.md` §7.
