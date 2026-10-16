# Article template prototype (desktop)

Artifact: https://claude.ai/artifact/NKhjbc25Dk3RruhqcgwugM

**Owner's brief (16 Oct):** the 10 main trips keep their proposal-4 designs. Every other article uses one shared template.

**Build:** `python build-article.py <repo> <proto folder>`. It reads the guides straight from `content/editorial/en/*.md` and the images from the prototype image maps.

**Samples:** two real guides rendered through the same template:
- `g-valley-kings.md` (antiquities);
- `g-snorkelling.md` (sea).

## How the template works
- **Hero:**
  - a photo or a video of the guide's own place, with its credit;
  - breadcrumb, topic, title and excerpt;
  - reading time (words ÷ 220) and the number of sections.
- **Body:**
  - contents on the left: the current section is marked, and a gold line under the header shows reading progress;
  - a 680 px reading column with a lead paragraph and numbered sections (the guide's own order);
  - tips from `>` blocks on a papyrus card;
  - photos between sections, each captioned with its place and source.
- **Booking while reading:**
  - a sticky box for the guide's first related trip: duration, private or group, pickup, "Free cancellation" (every partner option offers it up to 24 hours before), the options count and the partners;
  - other related trips appear under the box as one line each;
  - a hotel row links to Trip.com;
  - the affiliate disclosure sits under the box.
- **Trip cards inside the text:** a card appears after the paragraph that links to a trip. For the main trip it is a slim bar, since the box is already beside it.
- **End of the page:**
  - the FAQ as accordions (from `??` blocks);
  - "Ready to go?" in the trip world's tone (glyph wall for antiquities, deep blue for the sea), with every option, its partner and its facts;
  - "Keep reading": three guides on the same place. A guide with no exactly tagged photo gets a tone card.

## Media
| Use | Source | Location tag |
|---|---|---|
| Valley hero | Unsplash 1779366244195-c11f7819693c | "KV62 - Tomb of Tutankhamun, Kings Valley Road, Qena Desert, Egypt" |
| Valley figure | Unsplash 1683891043389-31a5ca0692f8 | "Mortuary Temple of Hatshepsut, Al Qarna Desert, Egypt" |
| Valley trip box | Unsplash 1623417765161-c71d6c83cfee | the site's Valley experience photo |
| Snorkelling hero | Pexels video (redsea-reef), Adrien JACTA | "Red Sea, Egypt" |
| Snorkelling figures | site destination photos for Hurghada and Sharm El Sheikh | see IMAGE-QUALITY-AUDIT |

No prices, ratings or reviews. The trip facts are the same as in proposal 4.
