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
