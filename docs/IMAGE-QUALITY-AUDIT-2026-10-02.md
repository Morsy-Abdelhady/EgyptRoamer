# Image quality audit — 2026-10-02

Scope: every photograph the live site uses (homepage, destinations, experiences, cards, travel-style "moods",
heroes, journal/guides, partner tabs) — 60 placements, 52 distinct photos, all Unsplash stand-ins approved by the
owner on 2026-09-28 (hot-linked, no Media Library images are set yet). The assistant UI uses no photography.

Method
- **Source resolution** from the Unsplash/imgix metadata endpoint (`?fm=json`, PixelWidth/PixelHeight).
- **Rendered vs delivered pixels** measured in Chrome on production at 390×3, 768×2, 1440×2 and 1920×1
  (rendered CSS box × device pixel ratio, accounting for `object-fit: cover`, against the width of the file the
  browser actually picked; `naturalWidth` is density-corrected for `srcset` images, so the `w=` of the chosen URL
  is used).
- **1:1 device-pixel crops** of the phone hero, a destination hero, cards and phone destination cards, before and
  after, from screenshots at the real device pixel ratio.
- **Art direction**: every photo reviewed on a labelled contact sheet for subject, landmark clarity, crop/focal
  point, grade and whether it looks generic or cheap.

## 1. What was actually wrong

Sources are never the bottleneck — the smallest master is 2,045 px wide, most are 4,000–8,000 px. The softness
came from **delivery**: landscape photos sit in portrait or tall frames with `object-fit: cover`, so only part of
the downloaded width is visible and the browser, choosing by width, fetched far too few pixels.

| Placement | Frame (phone) | Before: delivered ÷ needed | After |
|---|---|---|---|
| Homepage hero + journey scenes (phone) | 421×912 | 0.29× at 3× (1200 px landscape file stretched ~3×) | crop 2:3, 828 w: ~0.98× at 2×, 0.65× at 3× (≈2× density) |
| Homepage scenes (portrait tablet) | 829×1106 | 0.48× | crop 1:1, 1366 w: ~0.8× at 2× |
| Moods background (phone) | 390×961 | 0.32× | crop 2:3 |
| Planner background (phone) | 390×1971 | 0.11× | crop 2:3 |
| Phone destination cards | 312×480 | 0.37× | crop 1.3, up to 1000 w |
| Cards everywhere (4:4.6 frame) | 302×347 desktop | 0.58–0.69× at 2× (720 px landscape file) | crop 4:4.6, 800 w at 2× (1.3×) |
| Page heroes (phone) | 390×523 | 0.54× at 3× | crop 1.2, 1200 w |
| Desktop heroes / scenes at 1×–2× | — | 0.9–1.0× | unchanged (already right) |

A second defect: **the homepage hero downloaded twice** at 390–1280 px wide (the preload listed 900/1400/2000/2800,
the image 900/1200/1600/2000/2800 — different picks, 62–104 KB wasted, and the high-priority preload went unused).

## 2. Pipeline now in place (theme 1.2.33)

master (Unsplash original, 2–8k px) → Unsplash/imgix crop to the frame's shape (`w` + `h` + `fit=crop`, centred
like the CSS) → AVIF/WebP by `auto=format` → responsive widths → `<picture>` sources by screen shape / `srcset` +
`sizes` → correct priority (hero eager + `fetchpriority=high`, one preload per screen shape that lists exactly the
image's candidates; everything else lazy).

Framing is preserved by construction: each crop is at least as wide, for its height, as every frame its media query
covers, so `cover` shows exactly the same region (full height, centred) — verified on screenshots at 390, 768,
1440 (pyramids, Cairo skyline, cards and destination cards land on the same pixels before and after). Red Sea's
`object-position: 50% 40%` is vertical only and the crops keep the full height, so it is unaffected.

Bytes (homepage photos at first load, production URLs, AVIF):

| Screen | Before | After |
|---|---|---|
| 390×3 (iPhone-class) | 470 KB, hero fetched twice | 379 KB, each once, ~3× more detail |
| 412×2.6 (Android) | 445 KB, hero twice | 379 KB |
| 360×2 (small Android) | 242 KB (900 px landscape stretched 3×) | ~360 KB (720–828 px crops; right-sized, not oversized) |
| 768×2 (iPad portrait) | 486 KB, hero twice | 341 KB |
| 1440×2, 1920×1 | unchanged | unchanged |

Cards: 8–14 KB each (AVIF), up from ~7 KB, lazy-loaded below the fold.

Rejected: raising `sizes` to make the browser fetch 2,000–2,800 px landscape files on phones (sharp, but 3–4× the
bytes — the opposite of the 1.2.30 weight fix).

## 3. Per-image classification

KEEP = good subject, grade and resolution (delivery now fixed). OPTIMIZE = good photo, delivery was wrong (fixed
in 1.2.33). NEEDS BETTER SOURCE = the photo itself is weak or its subject can't be verified; no existing approved
asset is verifiably better for the same subject, so nothing was swapped (no landmark was replaced by another).

| Placement | Photo id | Master | Verdict | Notes |
|---|---|---|---|---|
| Home hero (scene 1) | 1734461255961 | 3415×2283 | OPTIMIZE ✔ | Giza pyramids under a dramatic sky; strong. Phone delivery was ~3× upscaled. |
| Home hero layer B | 1678038592492 | 6240×4160 | OPTIMIZE ✔ | Pyramids at red dusk. |
| Scene 2 · Nile | 1684100096410 | 6000×4000 | OPTIMIZE ✔ | Backlit felucca; excellent. |
| Scene 3 · Desert | 1771839534998 | 4500×3000 | OPTIMIZE ✔ | Clean dune line; minimal, on-brand. |
| Scene 4 · Red Sea | 1682687982049 | 8024×5349 | OPTIMIZE ✔ | Snorkeller over coral; most detailed (largest file). |
| Planner background | 1761205930594 | 4032×2268 | OPTIMIZE ✔ | Feluccas at dusk; heavily shaded, now sharp on phones. |
| Film 1–5 | 1771325676184 … | 4032–8192 w | KEEP | Strong, consistent warm grade. |
| Partner tabs (hidden until offers go live) | 5 ids | 3648–6336 w | KEEP | Not shown today. |
| Cairo | 1679238211153 | 5421×3614 | **NEEDS BETTER SOURCE** | Hazy, low-contrast skyline; pyramids tiny; AVIF banding in the flat sky (less visible after the fix). Weakest landmark image on the site; as a page hero it reads as murky. |
| Luxor | 1761056981183 | 6240×4160 | KEEP | Low-angle temple columns; dramatic (same photo as the "Ancient" mood). |
| Aswan | 1644517270263 | 6000×4000 | KEEP | Felucca under the Tombs of the Nobles; clear. |
| Hurghada | 1722264222007 | 3992×2242 | KEEP | Aerial coast; also the "Beach" mood. |
| Sharm El Sheikh | 1681158077449 | 7418×4945 | KEEP | Turquoise bay. |
| Siwa | 1640341742389 | 4000×6000 | KEEP | Portrait framed view of Shali; art-directed. |
| Alexandria | 1633624646814 | 5953×3969 | KEEP | Qaitbay Citadel; clear landmark. |
| Exp · Giza tour | 1678038592327 | 6240×4160 | OPTIMIZE ✔ | Silhouettes at sunset; dark but strong. Card was 0.6× at 2×. |
| Exp · Nile dinner cruise | 1761421852464 | 6240×4160 | NEEDS BETTER SOURCE (minor) | Beautiful felucca at dusk, but a dinner cruise is a large restaurant boat — subject mismatch. |
| Exp · Abu Simbel | 1633163893862 | 5791×3861 | OPTIMIZE ✔ | Great Temple; clear. |
| Exp · Red Sea diving | 1682687981907 | 7475×4983 | OPTIMIZE ✔ | Diver and anthias; vivid. |
| Exp · White & Black Desert safari | 1514975440715 | 5472×3648 | **NEEDS BETTER SOURCE** | Smooth white dunes; Egypt's White Desert is known for chalk rock formations. Location can't be verified (no GPS/IPTC, no provenance in the prototype). Verify or replace with a verified photo. |
| Exp · Cairo street food | 1637189315455 | 4032×3024 | KEEP | Table spread; generic but appropriate (also the "Food" mood). |
| Exp · Valley of the Kings | 1566288592443 | 3333×5000 | KEEP | Hypostyle columns (Karnak-like); acceptable for a Luxor West Bank tour but not the Valley itself — consider a verified tomb/valley photo. |
| Exp · Great Sand Sea 4×4 | 1661994215679 | 7104×4741 | KEEP | Also the "Adventure" mood. |
| Moods (7) | — | 3840–7104 w | OPTIMIZE ✔ | Full-bleed on phones was 0.32×; now crops. |
| Guides (7, drafts) | — | 2045–6642 w | KEEP / note | `g-costs` (2045×3088, generic food table) is the weakest; fine until the guide exists. |
| Offer sample · car rental 0 | 1499357526729 | 2750×2200 | **NEEDS BETTER SOURCE** | Joshua trees through a windscreen — Mojave, not Egypt. Hidden (no live offers); must not go live with this photo. |

## 4. Remaining recommendations (owner)

1. **Cairo hero**: set a Media Library featured image (or approve a clearer existing photo). It is the site's most
   visited destination and its weakest image.
2. **White Desert safari**: confirm the photo's location or provide a verified White Desert photo (chalk formations).
3. **Car rental sample photo**: replace before the car-rental offers go live.
4. When featured images move to the Media Library, `er_img()` already serves WordPress sizes; register a
   portrait size (e.g. `er-portrait`, 828×1242) so phones keep the sharp crop.

## 5. New photos added later (photo policy of 2026-10-03: free Unsplash, location tag names the exact place)

| Date | Page | Photo id | Unsplash page | Location tag | Licence | Note |
|---|---|---|---|---|---|---|
| 2026-10-16 | `g-sinai` (Mount Sinai and Saint Catherine) | 1603889947015-f3305fc399ea | https://unsplash.com/photos/people-walking-near-beige-concrete-building-during-daytime-MnmRtMkjK-c (Shady Hassan) | "Saint Catherine, Saint Catherine, Egypt" | Unsplash License (free) | The monastery bell tower and the mountain. A portrait photo: used as `1603889947015-f3305fc399ea:top` so the crop keeps the tower and cross (theme 1.2.63 adds crop positions). Hot-linked, nothing downloaded |
| 2026-10-16 | Hotel offer `tripcom-hotels-zamalek` (booking panel) | 1561778498-2d8ef2ade19e | https://unsplash.com/photos/ferries-along-the-city-river-banks-near-bridge-with-crossing-cars-7Xmf7zix9K8 (Ahmed Ezzat) | "Cairo Tower, Zamalek, Egypt" | Unsplash License (free) | The Nile and Zamalek from Cairo Tower. Hot-linked |
| 2026-10-16 | Hotel offer `tripcom-hotels-downtown-cairo` | 1699781895588-d5822c2dd3d6 | https://unsplash.com/photos/a-group-of-cars-driving-down-a-street-next-to-tall-buildings-Dxzmkphj3W8 (Faqih Abdul) | "Downtown Cairo, Bab Al Louq, Abdeen, Egypt" | Unsplash License (free) | A Downtown square. Hot-linked |
| 2026-10-16 | Hotel offer `tripcom-hotels-el-gouna` | 1511371241697-fbcaef9dd576 | https://unsplash.com/photos/reflection-of-buildings-in-water-3VsSOtf26j0 (Levi Morsy) | "El Gouna, Egypt" | Unsplash License (free) | Lagoon-front buildings. Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-white-island-and-ras-mohamed-snorkeling-trip` (booking panel) | 1616967304670-b75cf9ab4582 | https://unsplash.com/photos/2-people-surfing-on-water-during-daytime-HSDY5PHqAOw (Bogdan Nanescu) | "Ras Mohammed National Park, Egypt" | Unsplash License (free) | Turquoise water at Ras Mohammed, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-historical-alexandria-shared-day-tour-from-cairo` (booking panel) | 1583146464420-6bc4fe5eb279 | https://unsplash.com/photos/people-on-beach-during-daytime-IZgqzDMGkW0 (Lea Kobal) | "Citadel of Qaitbay, As Sayalah Sharq, Qesm Al Gomrok, Egipt" | Unsplash License (free) | The harbour by the citadel, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-pyramids-great-sphinx-private-tour-camel-ride` (booking panel) | 1704998250995-37d969c80e03 | https://unsplash.com/photos/a-camel-with-a-saddle-sits-in-front-of-the-pyramids-aWx7N6qpZbM (Marco D'Abramo) | "Giza, Egypt" | Unsplash License (free) | A saddled camel before the pyramids, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-opal-nile-dinner-cruise-live-entertainment` (booking panel) | 1626692880062-35c360fb6afc | https://unsplash.com/photos/white-and-blue-boat-on-water-near-city-buildings-during-daytime-1Ng-7ESDjvk (Roaming Pictures) | "Cairo, Egypt" | Unsplash License (free) | Nile boats and the Cairo skyline, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-abu-simbel-day-tour-private-guide-and-car` (booking panel) | 1748292951610-1ed2f59c9c20 | https://unsplash.com/photos/the-temples-of-abu-simbel-stand-majestically-Zv_l0sTAp4M (c gom) | "Abu Simbel, Égypte" | Unsplash License (free) | The Great Temple façade, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-hurghada-scuba-diving-certified-or-beginners` (booking panel) | 1777552550371-e05232b1c9c0 | https://unsplash.com/photos/pufferfish-swims-near-coral-reef-with-diver-in-background-rwIwviGVvzY (OLIVER SCHWANEBERG) | "Hurghada, Ägypten" | Unsplash License (free) | A reef with a diver, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-2-day-bahariya-and-white-desert-private-camp` (booking panel) | 1631750025018-2a2cad86d641 | https://unsplash.com/photos/a-couple-of-trucks-that-are-sitting-in-the-sand-XndW6QDl4L8 (Amira Shawky) | "Bahariya Oasis, Al Wahat Al Baharia, Egypt" | Unsplash License (free) | 4×4s in the desert, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-siwa-oasis-camp-2-nights-from-cairo` (booking panel) | 1584114189754-83d744ee2de8 | https://unsplash.com/photos/silhouette-of-palm-tree-near-body-of-water-during-sunset-h9KDJzszMnQ (Flo P) | "Siwa Oasis, Siwa, Ägypten" | Unsplash License (free) | Palms at sunset, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | GetYourGuide offer `gyg-west-bank-guided-tour-valley-of-the-kings` (booking panel) | 1695178234643-9656316a0e66 | https://unsplash.com/photos/a-mountain-range-in-the-middle-of-a-desert-TEC9kkg_6BQ (Sam Plummer) | "Valley Of The Kings, Kurna, Egypt" | Unsplash License (free) | The valley's cliffs, chosen to resemble the subject of GYG's own product photo (not copied). Hot-linked |
| 2026-10-16 | `exp-valley` card: Valley of the Kings | 1689876021889-b3f6cfd41b95 | https://unsplash.com/photos/a-man-walking-down-a-path-in-the-desert-2iPGg1MJSXw (Lynn Van den Broeck) | "Valley of the Kings, Luxor, Egypt" | Unsplash License (free) | The path into the valley. Hot-linked, `data/card-photos.json` |
| 2026-10-16 | `exp-valley` card: Hatshepsut's temple | 1683891043389-31a5ca0692f8 | https://unsplash.com/photos/a-row-of-statues-of-pharaohs-in-front-of-a-building-Q8qFGF43Pro (Mahmoud Refaat) | "Mortuary Temple of Hatshepsut, Al Qarna Desert, Egypt" | Unsplash License (free) | The Osiride statues. Hot-linked, `data/card-photos.json` |
| 2026-10-16 | `exp-alexandria` card: Citadel of Qaitbay | 1738511574956-9529fd9f84f1 | https://unsplash.com/photos/a-large-castle-with-a-flag-on-top-of-it-KhwdpRP0sss (Martijn Vonk) | "Qaitbay Citadel, As Sayalah Sharq, Al Gomrok, Egypt" | Unsplash License (free) | The fort. Hot-linked, `data/card-photos.json` |
| 2026-10-16 | `exp-alexandria` card: Bibliotheca Alexandrina | 1669329330758-cb84ab85113d | https://unsplash.com/photos/a-large-white-building-with-a-fence-and-trees-in-front-of-it-RpyeTn5xYHc (Abdallah M) | "Bibliotheca Alexandrina, Alexandria, Egypt" | Unsplash License (free) | The library building. Hot-linked, `data/card-photos.json` |
| 2026-10-16 | `exp-alexandria` card: Pompey's Pillar | 1709486725346-7c4cf3d9c0c8 | https://unsplash.com/photos/a-stairway-leading-up-to-a-monument-with-a-sky-background-_d600luHTXw (Dilip Poddar) | "Pompey's Pillar, Sultan Mosque, Karmouz, Egypt" | Unsplash License (free) | The column and its stairway. Hot-linked, `data/card-photos.json` |

Reused approved photos on hotel offers (2026-10-16): Giza hotels use the `exp-giza` pyramids photo; Luxor West Bank hotels use the `exp-valley` photo of Hatshepsut's temple, which stands on the West Bank.

Searched and **not** used on 2026-10-16:
- **Card photos without an exact tag:** Colossi of Memnon ("Egypt", "Luxor Governorate"), Kom el-Shoqafa, Montaza, the Black Desert, Crystal Mountain, Agabat and the White Desert (results tagged "Egypt", "Sahara Desert" or the Bahariya Oasis in general). Those cards stay without a photo.
- **Found but not placed:** The Grand Egyptian Museum (1777189978576-08b21689fcc9, Aleksei Filimonov) and the Pyramid of Djoser (1622962522206-7b7aa3dc5067, Siddhesh Mangela), both tagged exactly. The Giza page's "Combine it with" list is not shown as cards, so there is no card to hold them.
- **Zamalek (other results):** tagged only with the keyword "Zamalek", with no location, or "Cairo, Egypt".
- **Mount Sinai:** the free results were tagged only "Egypt".
- **Coptic Cairo and the Hanging Church:** the free results were tagged only "Egypt" or "Cairo, Egypt", which does not support the place.

`g-coptic-cairo` keeps the Cairo stand-in until a photo tagged with the exact place is found.
| 2026-10-17 | Guide card and hero `inside-the-pyramids-of-giza` (preview `?ux=2` only, theme 1.2.84) | 1761561044700-4262beec7d63 | location tag read on the photo page on 17 Oct (Thomas K); list in `docs/prototypes/journey-v4/guide-photos.py` | "Giza Pyramid Complex" | Unsplash (free) | Replaces a photo repeated on the Travel Guide index |
| 2026-10-17 | Guide card and hero `grand-egyptian-museum-visit` (preview `?ux=2` only, theme 1.2.84) | 1761688179162-4c42356857ee | location tag read on the photo page on 17 Oct (Fatih Beki); list in `docs/prototypes/journey-v4/guide-photos.py` | "Pyramids of Giza" | Unsplash (free) | Replaces a photo repeated on the Travel Guide index |
| 2026-10-17 | Guide card and hero `temples-of-aswan-and-lake-nasser` (preview `?ux=2` only, theme 1.2.84) | 1738580426867-03fa8c8b5288 | location tag read on the photo page on 17 Oct (Martijn Vonk); list in `docs/prototypes/journey-v4/guide-photos.py` | "Philae, Aswan" | Unsplash (free) | Replaces a photo repeated on the Travel Guide index |
| 2026-10-17 | Guide card and hero `10-days-in-egypt-itinerary` (preview `?ux=2` only, theme 1.2.84) | 1610331800877-462a21600420 | location tag read on the photo page on 17 Oct (Artem Bryzgalov); list in `docs/prototypes/journey-v4/guide-photos.py` | "Luxor Temple" | Unsplash (free) | Replaces a photo repeated on the Travel Guide index |
| 2026-10-17 | Guide card and hero `day-trips-from-cairo` (preview `?ux=2` only, theme 1.2.84) | 1738512156462-408182814b21 | location tag read on the photo page on 17 Oct (Martijn Vonk); list in `docs/prototypes/journey-v4/guide-photos.py` | "Pyramid of Djoser" | Unsplash (free) | Replaces a photo repeated on the Travel Guide index |

## 6. Wikimedia Commons photos on trip cards (owner approved, 17 Oct; preview `?ux=2` only, theme 1.2.88)
Hot-linked from Commons, credited under each photo with a link to its file page. The licences are CC BY or CC BY-SA, and the credit names the photographer and the licence.

| Trip, card | Commons file | Author | Licence |
|---|---|---|---|
| Food tour, Koshari | File:Egyptian food Koshary.jpg | Dina Said | CC BY-SA 4.0 |
| Food tour, Ful medames | File:Foul Mohamed Ahmed - foul.jpg | Marsupium | CC BY-SA 4.0 |
| Food tour, Hawawshi | File:Hawawshi Main 1 Mohamed Moheyeldin.jpg | Mohamed Moheyeldin | CC BY-SA 4.0 |
| Food tour, Feteer | File:Egyptian-food-16.jpg | Mohamed Ouda | CC BY-SA 4.0 |
| Valley, Colossi of Memnon | File:Colosos de Memnón, Luxor, Egipto, 2022-04-03, DD 131.jpg | Diego Delso | CC BY-SA 4.0 |
| Alexandria, Kom el-Shoqafa | File:Catacombs of Kom El Shoqafa, Alexandria, Egypt - 50852603281.jpg | Following Hadrian | CC BY-SA 2.0 |
| Alexandria, Montaza | File:Montaza Palace, Alexandria, Egypt - Murat Özsoy 2018.jpg | Murat Özsoy 1958 | CC BY-SA 4.0 |
| White Desert, Black Desert | File:Black Desert, Egypt 17.jpg | Viktor Lazić | CC BY-SA 4.0 |
| White Desert, Crystal Mountain | File:Crystal Mountain, White Desert, Egypt 28.jpg | Viktor Lazić | CC BY-SA 4.0 |
| White Desert, the White Desert | File:White Desert, Rock formations in desert landscape, Egypt.jpg | Vyacheslav Argenberg | CC BY 4.0 |
