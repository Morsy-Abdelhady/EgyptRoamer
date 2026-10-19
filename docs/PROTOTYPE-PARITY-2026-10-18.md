# Proposal 4: side-by-side review of the live site against the prototype (18 Oct 2026)

The owner caught that the cards did not match the prototype, and that the scroll between the homepage's first
slides had to stay like the old design. This is the component-by-component comparison that should have come first.
Each pair was captured at the prototype frame's width (1048 px). The prototype is on the left, the live site on
the right (`scratchpad` screenshots `PR-*`).

Not copied on purpose: headings the prototype made up for itself ("The walls speak. A guide reads them.",
"Go down into the valley"), because the content rules forbid invented text. The site keeps the pages' own headings.

## Homepage
| Part | Prototype | Live before | Fix |
|---|---|---|---|
| Slides 1–4 | the old pinned scroll | a stacked version (1.2.91–1.2.92) | old pinned scenes back, each ending in its trips; index in the hero |
| After the slides | "Egypt isn't a checklist" + 4 facts | missing | shown right after the slides |
| Moods, map | the live sections | same | — |
| "Moments we'd book again" | not there | there | removed in proposal 4 (the chapters carry the trips) |
| Hotels | "Hotels near your trips, place by place": a card per place, Trip.com rows | the old two-hotel box | the place grid |
| Trip Builder | trips show "✓ Free cancellation" | no cancellation mark | after the cancellation fact (below) |

## Trip page (world)
| Part | Prototype | Live before | Fix |
|---|---|---|---|
| Headings | Playfair (serif) | Inter (sans) | serif for h1–h3 and questions |
| Small labels | gold, no line | white with a line | gold, no line |
| Opener | chips incl. "3 options from 2 partners", Save, torch hint | breadcrumb, no Save | as the prototype |
| Order | questions before booking | after | questions first |
| Questions | "Questions before you book", first open | no label, all closed | as the prototype |
| Booking | "Choose your way in": dark option rows, ✓ Our pick + reason, partner chips, Compare side by side, free cancellation, hotels rows, disclosure | the white panel | rebuilt on the dark background (same tracked links, same disclosure) |
| Ending | "More trips in {place} / Not quite this one?" + "Read before you go / Our guides for this trip" | old light sections | as the prototype |

## Trips & tours
| Part | Prototype | Live before | Fix |
|---|---|---|---|
| Trip card | white framed card; clay place label; clock + length "About 4–8 hours"; beige chips; "**3 options** to compare" + gold "See options →"; heart | unframed; grey label with pin; plain facts; "Half day"; grey "3 options" | the prototype card |
| **Status, 19 Oct (theme 1.2.94–1.2.96)** | | | **Done and compared.** `er_trip_card()` + `assets/css/p4-cards.css`, with values read from the prototype's computed styles. Side-by-side shots at 1048 px and 390 px: card 304×480 against the prototype's 305×478; the text of all 11 cards is identical to the prototype's; the order in all 7 place groups is identical (two-place trips appear in both groups, first). Arabic checked (heart on the left, arrow mirrored). The heart saves to Saved; the whole card opens the trip page. |

## Places
| Part | Prototype | Live before | Fix |
|---|---|---|---|
| Top | photo hero "Seven places. *Seven different Egypts.*" | dark text header "Places" | photo hero |
| Cards | heart on the photo | no heart | heart |

## Travel Guide
| Part | Prototype | Live before | Fix |
|---|---|---|---|
| Top | photo hero "Plan it well. *Then go.*" | dark text header | photo hero |
| Filter | "Where" chips | "Topic" select + Apply | "Where" chips |
| Cards | white framed; gold "Luxor · Planning"; excerpt; "3 min read · 1 trip inside" | unframed; grey "3 min read"; "Discover" | the prototype card |

## Guide page
| Part | Prototype | Live before | Fix |
|---|---|---|---|
| Sidebar | "The trip for this guide" card: photo, place, title, chips, "See the 3 options →", Save, "From Viator · GetYourGuide" | the full booking panel | the trip card (guides about one trip); guides about several trips keep their trip list |
| End | "Ready to go?" options with "Book ↗", free cancellation, one line on comparing | close, "Check Availability" | "Book ↗", cancellation chip, the line |
| After | "Keep reading / More on {place}": guide cards | "Experiences / Destinations in this guide" | the guide cards |

## Partner fact added
`cancel` in Core `data/offer-facts.json` for all 29 tour offers: "free up to 24 hours before", as read on each
partner's product page (26 on 16 Oct, `docs/prototypes/journey-v4/README.md`; the 3 cruise offers on 17 Oct,
`docs/NILE-CRUISE-OFFERS-2026-10-17.md`). Trip.com hotel areas: none.

## Status, 19 Oct (theme 1.2.97–1.2.98, Core 1.2.54): every screen redone and compared
Owner (19 Oct): "the theme isn't right; review every section against the prototype: style, photos, videos, UX, 100%".
Each screen was captured top to bottom at the prototype frame's width (1048 px) and on a phone (390 px), prototype
left, site right (`scratchpad/pwt/survey.mjs` + `svsheet.py`), and key parts compared by computed style
(`stylediff.mjs`).

| Screen | Done | Kept on purpose / not identical |
|---|---|---|
| Trip pages (11) | The prototype's own world per trip (`inc/world-data.php`): eyebrow, lead, quote, steps with its photos and videos, tablets, papyrus, questions first, the dark "Choose your way in" with Compare side by side and ✓ Free cancellation, More trips, Our guides, the capsule. Header button outlined. | Options, facts and hotels are the live offers (the prototype's were a copy of them). Hotel rows follow the live order. |
| Homepage | Chapter trips box as the prototype, Pyramids and Red Sea videos, quote + facts, moods, map, hotels place by place, Trip Builder with ✓ Free cancellation. | **The old pinned slides and their scroll stay** (owner, 18 Oct), so the slide titles keep the old big capitals and the rail. |
| Trips & tours | Photo header, sticky filter bar, banners at 132 px, the trip card (1.2.94–96). | Filters are links (the page reloads) rather than instant. |
| Places | Photo hero with the prototype's words, heart on each place. | — |
| Travel Guide | Photo hero, "Where" chips, the prototype's cards and order. | — |
| Guide pages (24) | Rebuilt to the prototype (`template-parts/guide-p4.php`). | Body text is each guide's own; "See the place" photos are the prototype's strips without the lightbox. |
| Footer | Wordmark at the prototype's 260 px. | — |

New strings: 390 for the trip worlds (234 read from each page's own translated body, 156 translated) and about 40
for the other screens, in 7 languages, not yet reviewed by native speakers.
