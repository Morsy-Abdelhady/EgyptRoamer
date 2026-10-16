# Desktop journey, proposal 4: story home + trip worlds (2026-10-16)

**Owner's brief:** proposal 2 (story homepage and browsing) with the trips from proposal 3. Every trip should be like the Valley of the Kings page, with content the traveller lives in, not just an intro. Also try free videos.

- **Published:** https://claude.ai/artifact/8Nni2DJA4aBkoDwc2WxEt7.
  - Proposal 1: https://claude.ai/artifact/SdbGnRYkXTKQDyMsA73cJG
  - Proposal 2: https://claude.ai/artifact/4y7hqAxdKHubDFabkoLxQe
  - Proposal 3: https://claude.ai/artifact/2zzhquCDFh3rW6AMyfgy3h
- **Home and browse:** as proposal 2. The Pyramids chapter has a video background.
- **Four complete trip worlds,** each with the same structure:
  1. an opener;
  2. the day, step by step on scroll;
  3. practical tips;
  4. who it suits / combine with;
  5. "Choose your way in": options, our pick with a partner-stated reason, a comparison, hotels where we have them, and the disclosure;
  6. a booking capsule throughout.

  | Trip | World | The day, step by step | Tips | Who it suits / combine |
  |---|---|---|---|---|
  | Valley of the Kings | antiquities: a hieroglyph wall lit by a torch | 3 stops | — | Combine with a Karnak video |
  | Red Sea diving | sea light | 4 steps of the dive day | Beginners / certified / health | Where to dive, from Hurghada and from Sharm |
  | White & Black Desert | desert | 4 sights | A night in the desert, under stars | Who it suits; how long and when |
  | Nile dinner cruise | the Cairo Nile video | 4 parts of the evening | Choosing a boat | Combine it with |

- **Content:** every word comes from the trips' pages on egyptroamer.com (`v4-worlds.js`). Photos are approved only (`../journey-story/imgs2.mjs`, `../journey-worlds/imgs3.mjs`).

## Videos (free, Pexels licence; downloaded with the owner's OK on 16 Oct; used in this prototype only, not on the site)
| File | Pexels video | Creator | Place as stated on Pexels | Size |
|---|---|---|---|---|
| giza-sunset.mp4 (960×540) | https://www.pexels.com/video/timelapse-sunset-pyramids-in-gizeh-egypt-27433645/ | Peggy Anke | "Stunning sunset view of the Pyramids of Giza, Egypt"; tags Giza | 5.8 MB |
| cairo-nile-sunset.mp4 (1280×720) | https://www.pexels.com/video/sunset-river-cruise-on-cairo-nile-35600486/ | Nada Ashraf | "along the Nile in Cairo" | 5.8 MB |
| karnak-columns.mp4 (1280×720) | https://www.pexels.com/video/majestic-columns-of-karnak-temple-luxor-egypt-38144362/ | BOB DK | "Karnak Temple in Luxor, Egypt" | 1.7 MB |

**Rejected:**
- *Scuba diving adventure in vibrant coral reef* (37951030): no stated location (only tags), and 26 MB even at 540p.
- *Person legs underwater* (7318866): a resort pool, not the Red Sea.
- *Ancient Egyptian hieroglyphs on temple wall* (32537450): no named temple.


### Added later on 16 Oct (owner: "the videos made the site interactive; add more, chosen with great care"). Downloaded with the owner's OK.
| File | Pexels video | Creator | Place as stated on Pexels | Used for | Size |
|---|---|---|---|---|---|
| redsea-reef.mp4 (960×540) | https://www.pexels.com/video/vibrant-coral-reef-in-red-sea-egypt-31991096/ | Adrien JACTA | "Red Sea, Egypt" | home Red Sea chapter; Beach mood; dive opener | 11.0 MB |
| redsea-coral.mp4 (960×540) | https://www.pexels.com/video/colorful-coral-reefs-in-the-red-sea-31991101/ | Adrien JACTA | "Red Sea" | dive step "First dive"; Red Sea group banner | 10.5 MB |
| redsea-moray.mp4 (640×360) | https://www.pexels.com/video/vibrant-coral-reef-with-moray-eel-in-the-red-sea-31991100/ | Adrien JACTA | "Egypt's Red Sea" | dive step "Second dive" | 8.4 MB |
| alexandria-qaitbay.mp4 (960×540) | https://www.pexels.com/video/tracking-shot-of-the-citadel-of-qaitbay-7579787/ | The Instagrapher | "Qaitbay Citadel in Alexandria, Egypt" | Food mood (Alexandria); Alexandria group banner | 7.0 MB |
| tanoura.mp4 (960×540) | https://www.pexels.com/video/traditional-egyptian-tanoura-dance-performance-35112875/ | Magda Ehlers | "Egyptian"; **no city stated** (it shows the dance, not a place) | Nile dinner, step "Tanoura" | 11.1 MB |
| giza-camels.mp4 (1280×720) | https://www.pexels.com/video/camel-ride-near-the-great-pyramids-of-giza-29622798/ | K (@kelly) | "Giza, Egypt" | Cairo & Giza group banner | 6.6 MB |

**Also reused:** karnak-columns for the Ancient mood and the Luxor group banner; cairo-nile-sunset for the Nile step "Dinner".

**Rejected:**
- Nile felucca 35879466, Cairo bazaar 37846449 and Sinai camel ride 28617951: portrait 9:16.
- Divers 37981480, Hatshepsut-like temple 30843329 and obelisk 32537391: no stated place.
- Aswan aerials 29233187 and 29233301: 17 MB or more even at 640×360.

**Loading:** videos load only when on screen (IntersectionObserver), and pause when they leave it. Step videos play only while their step is active.

**No exactly located video was found** for the Valley of the Kings, Hatshepsut's temple, Red Sea diving or the White Desert.

**Before any video goes on the live site:** the owner decides whether free videos join the photo policy, and under what rule (the location named exactly).

## Improvements applied (owner: "apply all the suggestions", 16 Oct)

**UX**
- **Free cancellation:** shown on each option and in the comparison, only where the partner's own page states it.
  - All 9 GetYourGuide offers, read on 16 Oct, state "Free cancellation · Cancel up to 24 hours in advance for a full refund". The pages read:
    - t599849 (Valley of the Kings)
    - t1196989 (Hurghada diving)
    - t444057 (White Desert camp)
    - t1270141 (Opal Nile cruise)
    - t459032 (Pyramids with camel ride)
    - t447617 (Abu Simbel)
    - t922070 (Alexandria)
    - t556707 (Ras Mohammed)
    - t661189 (Siwa camp)
  - All 17 Viator offers, read in the owner's Edge on 16 Oct, state "Free cancellation up to 24 hours before the experience starts (local time)". The pages read:
    - 224493P18, 224493P76 (Luxor West Bank)
    - 13172P2, 66938P5 (Hurghada diving)
    - 139021P1, 10124P65 (White Desert)
    - 37291P188, 110498P6 (Nile dinner)
    - 308879P1, 41437P6 (Giza)
    - 221454P2, 128688P15 (Abu Simbel)
    - 59626P4 (Alexandria)
    - 5616599P11 (Ras Mohammed)
    - 387546P1 (Siwa)
    - 69673P17, 476624P1 (Cairo food)
  - So all 26 offers in the prototype are free to cancel up to 24 hours ahead. The trip summary says "Free cancellation on every option".
  - **For the live site:** add a `cancel` fact to Core `data/offer-facts.json` per offer (partner-stated only).
- **The journey within a trip:** the path marks are buttons that jump to their step, and there is "Skip to booking".
- **More trips in {place}:** shown after the booking section.
- **FAQ:** 3 questions per trip, the answers taken from each page, placed before booking.
- **Home:** "Where in Egypt?" chips under the hero's buttons.
- **All 10 trips** now open in a world. The 4 templates:
  - antiquities: Valley, Giza, Abu Simbel;
  - sea: Red Sea diving, Ras Mohammed;
  - desert: White Desert, Siwa Great Sand Sea;
  - city and Nile: Nile dinner, Alexandria, Cairo food.

  Steps without a photo of their own get a blurred, atmospheric layer, so another place's photo is never shown as them. Headings such as "Across the plateau" are editorial labels written for the prototype. The facts under them are the pages' own.

**UI**
- **Video switch:** one pause/play control for every video on the site. Motion longer than 5 seconds must be pausable (WCAG 2.2.2).
- **Video credits:** a small "i" that opens on hover or focus.
- **Gold buttons:** the header button is outlined. Gold is kept for the main action in the content, and the booking capsule stays hidden while the opener or the booking section is on screen.

**Performance**
- **Compression:** the videos went through ffmpeg 9.0.2 (Gyan build, installed with winget on the owner's OK): H.264 at up to 960 px, no audio, at most 14 s, CRF 30, faststart. The 9 files dropped from 55 MB to about 11 MB (0.3 to 2.7 MB each).
- **Data saver:** with Save-Data on, or a 2G/3G connection, or reduced motion, videos stay as their poster images until the reader presses "Play videos".

## Photo review (owner, 16 Oct: "choose beautiful, fitting, attractive photos")
All photos are free Unsplash photos whose location tag names the exact place, read on each photo's page on 16 Oct. They are used in the prototype only. Before any of them goes on the live site, record it in `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5.

| Where in the prototype | Replaces | Photo id | Location tag on Unsplash |
|---|---|---|---|
| Valley of the Kings, step "Valley of the Kings" | the path photo, with a man in the foreground | 1779366244195-c11f7819693c | "KV62 - Tomb of Tutankhamun, Kings Valley Road, Qena Desert, Egypt" |
| Pyramids trip photo | a dark, low-resolution silhouette | 1704998250995-37d969c80e03 | "Giza, Egypt" (already in §5) |
| Pyramids, "Combine it with" | none (new) | 1777189978576-08b21689fcc9 | Grand Egyptian Museum (already in §5, "found but not placed") |
| Abu Simbel, step "Scale" | a blurred layer | 1748292951610-1ed2f59c9c20 | "Abu Simbel, Égypte" (already in §5) |
| Alexandria, step "Bibliotheca Alexandrina" | a street-side view | 1649203945407-93bfe807f376 | "Bibliotheca Alexandrina, Al Azaritah WA Ash Shatebi, Bab Sharqi, Egypt" |
| Alexandria, step "Pompey’s Pillar" | the stairway close-up | 1671203984228-178b1efb6bb4 | "помпейская колонна, Alexandria, Egypt" (Pompey's Pillar) |
| Red Sea diving, step "Sail to the first site" | a blurred layer | 1777552550371-e05232b1c9c0 | "Hurghada, Ägypten" (already in §5) |
| Ras Mohammed trip photo and step "By boat" | the reef photo reused from diving | 1616967304670-b75cf9ab4582 | "Ras Mohammed National Park, Egypt" (already in §5) |
| Nile dinner, step "Live music" | a blurred layer | 1626692880062-35c360fb6afc | "Cairo, Egypt" (already in §5) |
| Siwa, "Combine it with" | none (new) | 1652377170273-72152e714b96 | "Siwa, Egypt" |
| Luxor destination card | the same column shot as the Ancient mood | 1710886324980-997f7742f16c | "Luxor Temple, Luxor, Egypt" |
| Aswan destination card | a felucca photo cropped to a bare hill | 1738580426867-03fa8c8b5288 | "Philae, Aswan 1, Egypt" |
| Luxury mood (Aswan) | boat cushions | 1644517270263-4112379d97ca | "Aswan, Egypt" (the photo the Aswan card used, now uncropped) |
| Ancient mood poster (Luxor) | a column close-up | 1689876405589-666038511bf9 | "Karnak, Luxor, Egypt" |

**Not found:**
- the Great Sand Sea, the White Desert and the Colossi of Memnon: free results were tagged only "Egypt" or not tagged;
- extra videos: Pexels' Luxor and Philae results are Karnak clips by the same creator as the one in use, or have no stated place.

**Active card:** the gold edge is now a box-shadow that follows the card's corners, with no gap (it was an outline with an offset).

## Second full review (16 Oct): defects fixed (`v4-fix2.py`)
1. **Broken images:** the Grand Egyptian Museum and the Siwa lake were missing. The build's image filter did not see keys written as `img:key|caption`.
2. **Giza opener:** the camel photo appeared inside the torch light, over a sunset video. The opener is now the photo alone. The sunset video plays in the step "The Great Pyramid", and the approved pyramids photo illustrates "Khafre and Menkaure".
3. **Steps without their own photo:** they now show a designed panel in the world's colours (carved hieroglyphs in the antiquities world), not a blurred photo.
4. **The video switch** moved into the header; it used to cover content at the bottom left.
5. **The opener's buttons** now fit the first screen (the opener height allows for the header).
6. **Contrast:** stronger shading behind the destination cards' region names and the hero's top line.
7. **Moods:** the label sits on its own line, followed by "An Egypt of torch-lit tombs…", the site's own words.
8. **After "Book":** the chosen option is marked and its button reads "Opened ✓".
