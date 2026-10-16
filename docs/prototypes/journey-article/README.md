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

## Places strip and inline video (owner, 16 Oct: "تمام" to strips and videos inside the text, rather than a separate gallery)
- **Places strip:**
  - three or four photos of the places a section names, shown after the section's first paragraph;
  - a click opens a lightbox (arrows, Esc, focus returns to the photo);
  - shown only where exactly tagged photos exist.
- **Inline video:**
  - loads and plays only while it is on screen;
  - the header switch pauses every video on the page.
  - The Valley guide has none, because no free video of the valley itself was found.

**New free Unsplash photos (location tags read on each photo page, 16 Oct):**

| Key | Photo | By | Location tag |
|---|---|---|---|
| habu1 (the painted gateway) | https://unsplash.com/photos/brown-concrete-building-under-blue-sky-during-daytime-VrWGR6_umTM (1622962522220-0d9e0d29d62d) | Siddhesh Mangela | "Medinet Habu, Al Bairat, Al Qarna, Egypt" |
| habu2 (the carved columns) | https://unsplash.com/photos/ancient-egyptian-columns-with-hieroglyphs-under-a-clear-blue-sky-i3A6Zm7zbaI (1762858279425-ed77c08d7a1c) | Fatih Beki | "Temple d'Amon (Médinet Habou), Al Bairat, Luxor, Egypt" |
| reef_school | https://unsplash.com/photos/school-of-small-orange-fish-swim-near-coral-reef-GGYYH-Hh03Y | Oliver Schwaneberg | "Hurghada, Ägypten" |
| reef_clown2 | https://unsplash.com/photos/two-clownfish-nestled-in-a-sea-anemone-nQTw3qMvLpw | Oliver Schwaneberg | "Hurghada, Ägypten" |
| reef_yellow | https://unsplash.com/photos/several-bright-yellow-fish-swim-near-a-coral-reef-6efiBO_PQqs | Oliver Schwaneberg | "Hurghada, Ägypten" |
| reef_puffer | https://unsplash.com/photos/a-small-pufferfish-swims-near-coral-reef-JfNX7lorx0o | Oliver Schwaneberg | "Hurghada, Ägypten" |
| reef_coral (video poster) | https://unsplash.com/photos/a-large-green-coral-formation-on-the-ocean-floor-Z-rxB4rOVf0 | Oliver Schwaneberg | "Hurghada, Ägypten" |

`imgs-art.mjs` fetches them. The captions follow each photo's own Unsplash title.

**Not found with an exact tag:** the Valley of the Queens and Nefertari, Deir el-Medina, the Ramesseum, the Colossi of Memnon (tagged only "Egypt"), the Giftun Islands and the Straits of Tiran.

**Video:** redsea-coral.mp4 (Pexels, Adrien JACTA, "Red Sea"), the same file as in proposal 4.

## Phone version (owner, 16 Oct)
- **Switch:** Desktop / Phone in the prototype bar. The layout answers to the frame through container queries.
- **Contents:** one sticky line under the header names the current section; tap it to open the list.
- **Booking:**
  - a bottom bar (trip, "Free cancellation", the options button) appears after the hero;
  - it steps aside whenever the slim trip card, the booking box or "Ready to go?" is on screen;
  - the booking box, hotels and disclosure follow the article.
- **Swiping:** the places strip and "Keep reading" are rows you swipe, and the lightbox swipes between photos.
- **Width:** photos and video run full width.
- **Checked** at 390 px: no sideways scroll and no script errors.
