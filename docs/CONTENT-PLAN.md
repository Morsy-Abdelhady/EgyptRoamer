# Content plan: clusters, intent, pages

This is a structure to fill with **original** editorial work. It is not a list of pages to generate. A page is published and ticked "Ready to index" only when it passes the test: *if every affiliate link were removed, would this page still be useful?*

## Pages created vs. not created

| Page | Decision | Why |
|---|---|---|
| Home | built (template + editable settings) | existing design |
| Destination archive + detail | built | core hub type; 7 seeded |
| Tour archive + detail | built (template) | no tours exist yet; the archive is noindex while empty |
| Experience archive + detail | built | 8 seeded |
| Activity archive + detail | built (template) | hub for activities such as snorkelling and diving; none exists yet |
| Guide archive + detail | built | 7 drafts |
| Article (Journal) archive + detail | built | WordPress posts at `/journal/` |
| Search | built | noindex |
| About, Contact, FAQ, Affiliate Disclosure, Privacy, Terms, Cookies, How We Choose, Partner With Us | **draft pages with editorial notes** | legal and trust copy must come from the owner; nothing invented |
| 404 | built | |
| "Best things to do in X" | **not created as separate pages** | the destination hub already carries "Things to do in {name}"; a separate page would compete for the same intent |
| "Best tours in X" | not created | same intent as the destination's "Things to do" section plus the tour archive filtered by destination (noindex filter) |
| Itinerary pages | **as guides** (e.g. `7-days-in-egypt-itinerary`) | real intent, one page per distinct trip length or route, only when written |
| Travel planning pages | as guides (best time, costs, visa, safety) | distinct informational intents |
| Where to stay | not created | needs real hotel knowledge and hotel offers; add as a guide per destination only when researched |
| Destination comparison pages | not created | only worth it for real head-to-head searches (e.g. "Hurghada vs Sharm El Sheikh"); write as a guide when there is material |

## Topic clusters (hub → spokes)

| Cluster | Hub (primary landing page) | Supporting pages (write when you have original material) | Commercial layer |
|---|---|---|---|
| Cairo & Giza | `/destinations/cairo/` | guide: Cairo travel guide · experiences: pyramids, Grand Egyptian Museum, Islamic Cairo, food tour · article: museum visit tips | offers attached to experiences + destination |
| Luxor | `/destinations/luxor/` | guide: Luxor itinerary · experiences: Valley of the Kings, balloon · activity: hot-air ballooning | offers on experiences |
| Aswan & Nubia | `/destinations/aswan/` | experiences: Abu Simbel, Philae · guide: Nile cruise comparison | cruise offers |
| Nile cruises | guide: `best-nile-cruises` | tours: Luxor↔Aswan cruise, dahabiya | cruise offers |
| Red Sea | `/destinations/hurghada/`, `/destinations/sharm/` | activities: diving, snorkelling · experiences: Giftun, Ras Mohammed | dive/snorkel offers |
| Western Desert & Siwa | `/destinations/siwa/` | experiences: White Desert, Great Sand Sea | desert tour offers |
| Planning Egypt | guide: best time to visit | guides: costs, visa & entry, safety, 7-day itinerary | none, or light |

## Intent map and cannibalisation rules

| Query family | Owner page | Must not compete |
|---|---|---|
| "{city} travel / things to do in {city}" | destination hub | guides must target a narrower question (itinerary, where to stay, museums) |
| "{attraction} tour / tickets" | the experience or tour page | the destination hub links to it and does not duplicate it |
| "best time to visit Egypt" | one guide | destination "best time" fields stay short and link to the guide |
| "Nile cruise Luxor Aswan" | the cruises guide (comparison) | individual cruise tours target the specific ship/route |
| "{activity} in Egypt" | the activity page | destinations list where to do it |

**Internal linking is built into the templates:**

- Destination → things to do, activities, guides, offers, other destinations.
- Tour, experience or activity → destination(s), alternatives, related activities, guides.
- Guide → destinations, related experiences.

Editors add contextual links in the body text with natural anchors.
