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
| 2026-10-22 | Guide card and hero `g-cairo-museums` (cairo-museums-which-to-visit) | 1553835568-c48d3b90ed70 | https://unsplash.com/photos/pink-and-white-painted-building-5lf4KfVRHso (Spencer Davis) | "Museum of Egypt, Cairo, Egypt" | Unsplash License (free) | The Egyptian Museum's facade, Tahrir. Tag first read 20 Oct, rechecked 22 Oct. Hot-linked |
| 2026-10-22 | Guide card and hero `g-dendera-abydos` (dendera-and-abydos-temples) | 1723997326348-d4953661a61a | https://unsplash.com/photos/the-columns-of-the-temple-are-decorated-with-egyptian-art-vqPp5gK06uQ (dleonard1210) | "Dandarah, Qena, Qena Governorate, Egypt" | Unsplash License (free) | Hathor-headed columns at Dendera. Tag first read 20 Oct, rechecked 22 Oct. Hot-linked |

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

| 2026-10-17 | Experience `exp-cruise` (seed extraStock) and GetYourGuide offer `gyg-nile-cruise-aswan-luxor-abu-simbel-balloon` | 1778402153163-fc9a5fd0131a | free Unsplash photo by Kuan L, location tag "Aswan" read on the photo page on 17 Oct (also used in `g-nile-cruise`, prototype `guide-photos.py`) | "Aswan" | Unsplash (free) | A cruise ship on the Nile at Aswan |
| 2026-10-25 | Trip `exp-balloon`: opener and card (seed extraStock) | 1669521762357-966bfdcbde1d | already approved for `g-luxor-balloon` (Hongbin) | "Temple of Hatshepsut, Kurna" | Unsplash (free) | A balloon over Hatshepsut’s temple |
| 2026-10-25 | Trip `exp-balloon`, step 1 "Before dawn" | 1759211316311-64deca1dea7f | https://unsplash.com/photos/zRwHJL24LrY (Sebastiano Musmeci), location tag read on the photo page on 25 Oct | "Porto delle Mongolfiere, Tarik Al Sheikh Agwa, Gurna, Luxor, Egitto" | Unsplash License (free) | Balloons being prepared at the launch site. Hot-linked |
| 2026-10-25 | Trip `exp-balloon`, step 2 "Over the fields" | 1703902770170-7cc56c8c3067 | https://unsplash.com/photos/DWd6P-g12aE (Hongbin), location tag read on 25 Oct | "Luxor, Luxor City, Luxor, Egypt" | Unsplash License (free) | A balloon over the green fields, the Theban hills behind. Hot-linked |
| 2026-10-25 | Trip `exp-balloon`, step 3 "The West Bank below" | 1759212369677-cbd24ca4e7f8 | https://unsplash.com/photos/oAy_xXe8NeM (Sebastiano Musmeci), location tag read on 25 Oct | "Porto delle Mongolfiere, Tarik Al Sheikh Agwa, Gurna, Luxor, Egitto" | Unsplash License (free) | A balloon over the West Bank desert and ruins. Hot-linked |

## 7. Proposal 4 on the live site (18 Oct 2026, owner: "apply everything")

### Videos (free Pexels videos, served from the theme: `assets/video/`)
Compressed for the prototype on 16 Oct (≤14 s, ≤960 px, no audio). Each has its own pause button and its credit ("Video: name, Pexels"); with Data Saver or reduced motion it stays a still photo until play is pressed.

| File | Pexels page | Creator | Place as stated | Used for |
|---|---|---|---|---|
| giza-camels-clear.mp4 | https://www.pexels.com/video/camels-and-pyramids-under-clear-sky-35695240/ | JORGE MONCADA | Camels resting in front of the iconic Pyramids of Giza | home chapter Pyramids; Giza step 3 |
| giza-day.mp4 | https://www.pexels.com/video/breathtaking-view-of-giza-pyramids-in-egypt-32537727/ | K (@kelly) | the Giza Pyramids under a bright sky in Egypt | Giza step 1 |
| giza-aerial.mp4 | https://www.pexels.com/video/aerial-view-on-cairo-and-pyramids-10719817/ | Blanka OG | aerial view of Giza Pyramids with Cairo city skyline | Cairo banner (Trips & tours); Giza step 2 |
| cairo-nile-sunset.mp4 | https://www.pexels.com/video/sunset-river-cruise-on-cairo-nile-35600486/ | Nada Ashraf | along the Nile in Cairo | Nile dinner opener and step 1 |
| karnak-columns.mp4 | https://www.pexels.com/video/majestic-columns-of-karnak-temple-luxor-egypt-38144362/ | BOB DK | Karnak Temple in Luxor, Egypt | Luxor banner; Ancient mood; Valley 'Combine it with' |
| redsea-reef.mp4 | https://www.pexels.com/video/vibrant-coral-reef-in-red-sea-egypt-31991096/ | Adrien JACTA | Red Sea, Egypt | home chapter Red Sea; Beach mood; diving and Ras Mohammed openers |
| redsea-coral.mp4 | https://www.pexels.com/video/colorful-coral-reefs-in-the-red-sea-31991101/ | Adrien JACTA | Red Sea | Hurghada banner; diving step 'First dive' |
| redsea-moray.mp4 | https://www.pexels.com/video/vibrant-coral-reef-with-moray-eel-in-the-red-sea-31991100/ | Adrien JACTA | Egypt's Red Sea | diving step 'Second dive' |
| alexandria-qaitbay.mp4 | https://www.pexels.com/video/tracking-shot-of-the-citadel-of-qaitbay-7579787/ | The Instagrapher | Qaitbay Citadel in Alexandria, Egypt | Alexandria banner; Food mood; Alexandria opener |
| tanoura.mp4 | https://www.pexels.com/video/traditional-egyptian-tanoura-dance-performance-35112875/ | Magda Ehlers | Egyptian (shows the dance, no city) | Nile dinner step 'A tanoura performance' |

### Guide photos ("See the place", `inc/world-guide-photos.php`)
Free Unsplash photos whose location tag names the place, read on each photo page on 16–17 Oct (`docs/prototypes/journey-v4/guide-photos.py`). Hot-linked from images.unsplash.com, credited "Photo: name, Unsplash"; the caption shows on English pages.

| Photo id | Location tag as read | Photographer | Caption | Guide#section |
|---|---|---|---|---|
| 1502250493741-939d1c76eaad | Abu Simbel Temples | AussieActive | The Great Temple, Abu Simbel | temples-of-aswan-and-lake-nasser#abu-simbel, 10-days-in-egypt-itinerary#at-a-glance, abu-simbel-from-aswan#what-you-will-see |
| 1521791697570-e1f13d0b81d0 | see journey-v4 README / imgs3 (16 Oct) | Alex Block | Baladi bread, Cairo | cairo-at-night#street-food |
| 1581248708422-db978024f382 | Karnak, Luxor | Kristina | Karnak from the sacred lake | karnak-and-luxor-temple#karnak |
| 1584114130913-0852aeb8fe8c | Siwa Oasis | Flo P | Sunset over the old town | siwa-or-white-desert#siwa |
| 1590657140810-25898a847305 | see journey-v4 README / imgs3 (16 Oct) | mohmed Afify | Egyptian biscuits, Cairo | cairo-at-night#street-food |
| 1594808815295-52034d585f56 | Alexandria | Flo P | Fishing boats in the Eastern Harbour | day-trips-from-cairo#alexandria |
| 1595979904086-471704dc0e81 | Al-Rifa’i Mosque, Cairo | Omar Adel | The Al-Rifa’i and Sultan Hassan mosques | cairo-travel-guide#old-city-walk |
| 1596130535979-eedea4278d29 | Luxor Temple | Artem Bryzgalov | The pylon and obelisk | karnak-and-luxor-temple#luxor-temple |
| 1601816500593-8f1276479ea6 | Alexandria | George Youssef | Alexandria | 14-days-in-egypt-itinerary#at-a-glance, best-time-to-visit-egypt#regions |
| 1603889844276-06c9d85b7594 | Pyramid of Menkaure | Shady Hassan | The pyramid of Menkaure | inside-the-pyramids-of-giza#the-pyramids |
| 1603889947015-f3305fc399ea | Saint Catherine | Shady Hassan | Saint Catherine, South Sinai | mount-sinai-saint-catherine#monastery |
| 1610331800877-462a21600420 | Luxor Temple | Artem Bryzgalov | The colonnade at sunset | karnak-and-luxor-temple#luxor-temple, 14-days-in-egypt-itinerary#at-a-glance, 10-days-in-egypt-itinerary#at-a-glance, best-time-to-visit-egypt#regions, 7-days-in-egypt-itinerary#at-a-glance |
| 1612442494156-a5fd9a1c3602 | Siwa Oasis | Omar Mohamed | A lake at Siwa Oasis | great-sand-sea#what-it-is, siwa-or-white-desert#siwa, best-time-to-visit-egypt#regions |
| 1616967304670-b75cf9ab4582 | see journey-v4 README / imgs3 (16 Oct) | Bogdan Nanescu | Ras Mohammed National Park | red-sea-famous-dive-sites#ras-mohammed |
| 1622962522206-7b7aa3dc5067 | Pyramid of Djoser | Siddhesh Mangela | The colonnade of the Djoser complex | saqqara-and-dahshur-pyramids#saqqara |
| 1622962522220-0d9e0d29d62d | see journey-v4 README / imgs3 (16 Oct) | Siddhesh Mangela | Medinet Habu, the painted gateway | luxor-west-bank-temples-and-tombs#medinet-habu, valley-of-the-kings-tombs-and-tickets#west-bank, hidden-gems-in-egypt#nile-valley |
| 1626692880062-35c360fb6afc | Cairo | Roaming Pictures | Boats on the Nile, Cairo | cairo-at-night#dinner-cruise |
| 1628231354449-18c8bb735b23 | Mount Sinai, Saint Catherine | Raimond Klavins | Sunrise from Mount Sinai | mount-sinai-saint-catherine#the-mountain |
| 1633033254409-bd538e785f51 | Philae, Aswan | Dmitrii Zhodzishskii | Philae from the water | temples-of-aswan-and-lake-nasser#aswan |
| 1633163973863-d44691f969b4 | Abu Simbel | Dmitrii Zhodzishskii | The sanctuary, Abu Simbel | temples-of-aswan-and-lake-nasser#abu-simbel, abu-simbel-from-aswan#what-you-will-see |
| 1633624646814-3a61ed3e2847 | see journey-v4 README / imgs3 (16 Oct) | Juan Nino | Qaitbay Citadel | day-trips-from-cairo#alexandria |
| 1634464905162-68ba20cb9a06 | Bent Pyramid | Jens Aber | The Bent Pyramid | saqqara-and-dahshur-pyramids#dahshur, hidden-gems-in-egypt#near-cairo |
| 1649775827146-1d67524b9465 | Abu Simbel | Jorge Fernández Salas | The colossi, close up | abu-simbel-from-aswan#what-you-will-see |
| 1660430279942-a26bb4c7d4be | Mount Sinai, Saint Catherine | Artem Labunsky | Walkers resting near the summit | mount-sinai-saint-catherine#the-mountain |
| 1660705630675-d66d60fdca46 | Edfu | mana5280 | The temple of Horus, Edfu | temples-of-aswan-and-lake-nasser#north, nile-cruise-or-hotels-luxor-aswan#temples, hidden-gems-in-egypt#nile-valley |
| 1661270288617-f3168c7a18be | Mount Sinai, Saint Catherine | Artem Labunsky | The Sinai mountains from Mount Sinai | mount-sinai-saint-catherine#the-mountain |
| 1662655558673-4e628102f545 | Karnak Temples, Luxor | 2H Media | The ram-headed sphinxes, Karnak | karnak-and-luxor-temple#karnak |
| 1666041382402-f428986aea0a | The Hanging Church, Old Cairo | Moaz Elsayed | The steps up to the Hanging Church | coptic-cairo-guide#hanging-church |
| 1668647014988-fc7434eb6e01 | Alexandria | Noel Schläfli | A tram in Alexandria | day-trips-from-cairo#alexandria |
| 1669521762357-966bfdcbde1d | Temple of Hatshepsut, Kurna | Hongbin | A balloon over Hatshepsut’s temple, Deir el-Bahari | luxor-west-bank-temples-and-tombs#deir-el-bahari |
| 1674724545219-29637e41ae04 | Cairo Tower, Zamalek | Ahmed Assem | The Nile at sunset by Cairo Tower | cairo-at-night#felucca, cairo-travel-guide#on-the-river |
| 1678388104119-18fcd08762f5 | Fortress of Babylon, Old Cairo | 2H Media | The walls of the Fortress of Babylon, Old Cairo | coptic-cairo-guide#babylon |
| 1678747365854-a0a68fd24725 | The Hanging Church, Old Cairo | 2H Media | Wooden screens inside the Hanging Church | coptic-cairo-guide#hanging-church |
| 1681158077449-77f23f629f0d | see journey-v4 README / imgs3 (16 Oct) | — | A bay at Sharm El Sheikh | snorkelling-hurghada-or-sharm#sharm |
| 1681403402826-c514f373a156 | Dahshur | Jordi Orts Segalés | A pyramid at Dahshur | saqqara-and-dahshur-pyramids#dahshur |
| 1681403419483-a30ff02a385f | The Temple of Horus at Edfu | Jordi Orts Segalés | Light in the temple of Horus | temples-of-aswan-and-lake-nasser#north |
| 1683891043389-31a5ca0692f8 | see journey-v4 README / imgs3 (16 Oct) | Mahmoud Refaat | Hatshepsut’s temple, the statue terrace | luxor-west-bank-temples-and-tombs#deir-el-bahari, valley-of-the-kings-tombs-and-tickets#west-bank |
| 1689189830710-1cb48a6efbe3 | Siwa | Faqih Abdul | Sandboards ready by a 4×4, Siwa | great-sand-sea#safari |
| 1691591403777-128b9a842b8b | see journey-v4 README / imgs3 (16 Oct) | — | The White Desert | siwa-or-white-desert#white-desert, white-desert-night#route |
| 1693654547147-24d94b4ed4ea | Luxor Temple | Hongbin | A colossal statue, Luxor Temple | karnak-and-luxor-temple#luxor-temple |
| 1702909171830-2c4dca2ac090 | Abu Simbel | Michael Starkie | Abu Simbel | abu-simbel-from-aswan#rescue |
| 1707172889437-dc6f210ea44a | Khan el-Khalili, Cairo | Thales Botelho de Sousa | A gateway in Khan el-Khalili | cairo-at-night#old-city, cairo-travel-guide#old-city-walk |
| 1708785033343-cd54bbb7d2cc | Saqqara | oCDr | Painted reliefs in a Saqqara tomb | saqqara-and-dahshur-pyramids#saqqara |
| 1709485400031-6094ea4f0bad | The Hanging Church, Old Cairo | Dilip Poddar | The Hanging Church and its twin towers | coptic-cairo-guide#hanging-church |
| 1722264222007-3e4f1808db3e | see journey-v4 README / imgs3 (16 Oct) | Unsplash | The coast at Hurghada | 10-days-in-egypt-itinerary#at-a-glance, best-time-to-visit-egypt#regions |
| 1738512156462-408182814b21 | Pyramid of Djoser | Martijn Vonk | The Step Pyramid of Djoser | saqqara-and-dahshur-pyramids#saqqara, day-trips-from-cairo#saqqara, hidden-gems-in-egypt#near-cairo |
| 1738580426867-03fa8c8b5288 | Philae, Aswan | Martijn Vonk | Philae, Aswan | 14-days-in-egypt-itinerary#at-a-glance, temples-of-aswan-and-lake-nasser#aswan, nile-cruise-or-hotels-luxor-aswan#temples, 7-days-in-egypt-itinerary#at-a-glance |
| 1758720944577-559b852e8493 | Siwa | kai muro | The mud-brick old town, Siwa | siwa-or-white-desert#siwa, hidden-gems-in-egypt#north-and-west |
| 1760650454610-0a4c9a5d0bb6 | Pyramids Gardens, Giza | Fatih Beki | Camels before the pyramids | inside-the-pyramids-of-giza#the-pyramids |
| 1760973519064-fc146406c871 | Al-Azhar Mosque, Cairo | Fatih Beki | The minarets of Al-Azhar | cairo-at-night#old-city, cairo-travel-guide#old-city-walk |
| 1761561044700-4262beec7d63 | Giza Pyramid Complex | Thomas K | The three pyramids of Giza | inside-the-pyramids-of-giza#the-pyramids, 14-days-in-egypt-itinerary#at-a-glance, 10-days-in-egypt-itinerary#at-a-glance, 7-days-in-egypt-itinerary#at-a-glance |
| 1761688179162-4c42356857ee | Pyramids of Giza | Fatih Beki | The pyramids of Giza | grand-egyptian-museum-visit#with-the-pyramids, day-trips-from-cairo#giza |
| 1762858279425-ed77c08d7a1c | see journey-v4 README / imgs3 (16 Oct) | Fatih Beki | Medinet Habu, the carved columns | luxor-west-bank-temples-and-tombs#medinet-habu, valley-of-the-kings-tombs-and-tickets#west-bank |
| 1767938072548-112727a13266 | Grand Egyptian Museum | Suzi Kim | Tutankhamun’s golden mask | grand-egyptian-museum-visit#what-you-will-see |
| 1770251693846-4167f4c049c6 | Luxor | Anthony Chen | A felucca on the Nile, Luxor | nile-cruise-or-hotels-luxor-aswan#smaller-boats |
| 1775225819853-1dc8c22cf4d1 | Aswan | Jean Pierre Cabáni | Feluccas at sunset, Aswan | temples-of-aswan-and-lake-nasser#aswan |
| 1777189978576-08b21689fcc9 | see journey-v4 README / imgs3 (16 Oct) | Aleksei Filimonov | A colossal statue in the museum’s hall | grand-egyptian-museum-visit#what-you-will-see |
| 1777551881568-50b1beab7d91 | see journey-v4 README / imgs3 (16 Oct) | Oliver Schwaneberg | Small orange fish over a coral reef, Hurghada | red-sea-famous-dive-sites#before-you-book, snorkelling-hurghada-or-sharm#hurghada |
| 1777552771426-9ac9f515dc40 | see journey-v4 README / imgs3 (16 Oct) | Oliver Schwaneberg | Yellow fish on a coral reef, Hurghada | snorkelling-hurghada-or-sharm#hurghada |
| 1777552955794-77aa990a47df | see journey-v4 README / imgs3 (16 Oct) | Oliver Schwaneberg | Two clownfish in a sea anemone, Hurghada | red-sea-famous-dive-sites#before-you-book, snorkelling-hurghada-or-sharm#hurghada |
| 1777552995899-b0c030a3ef92 | see journey-v4 README / imgs3 (16 Oct) | Oliver Schwaneberg | A small pufferfish by the reef, Hurghada | red-sea-famous-dive-sites#before-you-book, snorkelling-hurghada-or-sharm#hurghada |
| 1778402153163-fc9a5fd0131a | Aswan | Kuan L | A cruise ship on the Nile, Aswan | nile-cruise-or-hotels-luxor-aswan#cruise |


## 8. Proposal 4 trip pages, as the prototype (19 Oct 2026, theme 1.2.97)
The trip pages now use the prototype's own photos for the opener, each step and the papyrus (`inc/world-data.php`, from `docs/prototypes/journey-v4/world-data.json`). Unsplash photos are the prototype's approved stand-ins (matched to their ids by picture, `tools/world/imgmap.json`); Commons files are the prototype's credited picks (`docs/prototypes/journey-v4/wpick.json`, author and licence on each step). Nothing is downloaded; Unsplash is hot-linked, Commons is served through `Special:FilePath`.

| Trip | Where | Source | Credit shown |
|---|---|---|---|
| exp-valley | opener | Unsplash 1623417765161-c71d6c83cfee | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-valley | step 1 (Valley of the Kings) | Unsplash 1779366244195-c11f7819693c | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-valley | step 2 (Hatshepsut’s temple) | Unsplash 1683891043389-31a5ca0692f8 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-valley | step 3 (Colossi of Memnon) | Commons Colosos_de_Memnón,_Luxor,_Egipto,_2022-04-03,_DD_131.jpg | Photo: Diego Delso, Wikimedia Commons, CC BY-SA 4.0 |
| exp-dive | opener | Unsplash 1778400283418-b419be4be7a5 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-dive | step 1 (Check-in) | Commons Hurghada_Marina_002.jpg | Photo: Amr F.Nagy, Wikimedia Commons, public domain |
| exp-dive | step 2 (Sail to the first site) | Unsplash 1777552550371-e05232b1c9c0 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-safari | opener | Unsplash 1691591403777-128b9a842b8b | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-safari | step 1 (The Black Desert) | Commons Black_Desert,_Egypt_17.jpg | Photo: Viktor Lazić, Wikimedia Commons, CC BY-SA 4.0 |
| exp-safari | step 2 (Crystal Mountain) | Commons Crystal_Mountain,_White_Desert,_Egypt_28.jpg | Photo: Viktor Lazić, Wikimedia Commons, CC BY-SA 4.0 |
| exp-safari | step 4 (The White Desert) | Commons White_Desert,_Rock_formations_in_desert_landscape,_Egypt.jpg | Photo: Vyacheslav Argenberg, Wikimedia Commons, CC BY 4.0 |
| exp-dinner | opener | Unsplash 1761421852464-463fb31f2dc0 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-dinner | step 2 (Live music) | Unsplash 1626692880062-35c360fb6afc | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-giza | opener | Unsplash 1704998250995-37d969c80e03 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-giza | step 4 (The Sphinx) | Commons Great_Sphinx_of_Giza_(أبو_الهول).jpg | Photo: Petar Milošević, Wikimedia Commons, CC BY-SA 4.0 |
| exp-giza | papyrus (The Grand Egyptian Museum) | Unsplash 1777189978576-08b21689fcc9 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-abu | opener | Unsplash 1633163893862-4cdc62de7d82 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-abu | step 2 (Scale) | Unsplash 1748292951610-1ed2f59c9c20 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-abu | step 3 (The interiors) | Commons Interior_of_the_Great_Temple_of_Abu_Simbel_-_panoramio_(1606).jpg | Photo: turizm-art, Wikimedia Commons, CC BY 3.0 |
| exp-abu | step 4 (The rescue) | Commons Nubia,_Temples_of_Abu_Simbel,_Pictures,_photo_4_of_7_-_Archivio_fotografico_Museo_Egizio,_Turin_Album2_092.jpg | The Great Temple shortly before the move · Photo: Museo Egizio, Turin (photo archive), Wikimedia Commons, public domain |
| exp-alexandria | opener | Unsplash 1633624646814-3a61ed3e2847 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-alexandria | step 1 (The Citadel of Qaitbay) | Unsplash 1738511574956-9529fd9f84f1 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-alexandria | step 2 (The Bibliotheca Alexandrina) | Unsplash 1649203945407-93bfe807f376 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-alexandria | step 3 (Kom el-Shoqafa) | Commons Catacombs_of_Kom_El_Shoqafa,_Alexandria,_Egypt_-_50852603281.jpg | Photo: Following Hadrian, Wikimedia Commons, CC BY-SA 2.0 |
| exp-alexandria | step 4 (Pompey’s Pillar) | Unsplash 1671203984228-178b1efb6bb4 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-alexandria | step 5 (The Corniche and Montaza) | Commons Montaza_Palace,_Alexandria,_Egypt_-_Murat_Özsoy_2018.jpg | Photo: Murat Özsoy 1958, Wikimedia Commons, CC BY-SA 4.0 |
| exp-ras-mohammed | opener | Unsplash 1616967304670-b75cf9ab4582 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-ras-mohammed | step 2 (By boat) | Unsplash 1616967304670-b75cf9ab4582 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-siwa | opener | Unsplash 1771839535027-865f757ae09c | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-siwa | step 1 (Dune driving) | Commons Great_Sand_Sea8.jpg | Photo: Walaa, Wikimedia Commons, public domain |
| exp-siwa | step 2 (Springs) | Commons Siwa_Oasis,_Cleopatra's_Bath,_Egypt.jpg | Photo: Vyacheslav Argenberg, Wikimedia Commons, CC BY 4.0 |
| exp-siwa | step 4 (Sunset) | Commons Sunset_at_Great_Sand_Sea3.jpg | Photo: Walaa, Wikimedia Commons, public domain |
| exp-siwa | papyrus (The oasis itself) | Unsplash 1652377170273-72152e714b96 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-food | opener | Commons Egyptian_food_Koshary.jpg |  |
| exp-food | step 1 (Koshari) | Commons Egyptian_food_Koshary.jpg | Photo: Dina Said, Wikimedia Commons, CC BY-SA 4.0 |
| exp-food | step 2 (Ful medames and ta’meya) | Commons Foul_Mohamed_Ahmed_-_foul.jpg | Photo: Marsupium, Wikimedia Commons, CC BY-SA 4.0 |
| exp-food | step 3 (Hawawshi) | Commons Hawawshi_Main_1_Mohamed_Moheyeldin.jpg | Photo: Mohamed Moheyeldin, Wikimedia Commons, CC BY-SA 4.0 |
| exp-food | step 4 (Feteer meshaltet) | Commons Egyptian-food-16.jpg | Photo: Mohamed Ouda, Wikimedia Commons, CC BY-SA 4.0 |
| exp-food | step 5 (Sweets and drinks) | Unsplash 1590657140810-25898a847305 | Photo: mohmed Afify, Unsplash |
| exp-food | papyrus (Islamic Cairo or Downtown) | Unsplash 1652022262085-d454346a353e | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-cruise | opener | Unsplash 1778402153163-fc9a5fd0131a | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-cruise | step 1 (Aswan) | Unsplash 1738580426867-03fa8c8b5288 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-cruise | step 2 (Kom Ombo) | Commons Kom_Ombo_Portal_03.JPG | Photo: Olaf Tausch, Wikimedia Commons, CC BY 3.0 |
| exp-cruise | step 3 (Edfu) | Unsplash 1660705630675-d66d60fdca46 | prototype photo (journey-v4 imgs, approved 18 Oct) |
| exp-cruise | step 4 (Luxor) | Unsplash 1610331800877-462a21600420 | prototype photo (journey-v4 imgs, approved 18 Oct) |

## 9. Wikimedia Commons photos on the place pages (owner, 26 Oct 2026: "الصور خدها من ويكيبيديا"; theme 1.3.20)

Free-licensed Commons photos only (CC BY, CC BY-SA, public domain), hot-linked from `upload.wikimedia.org` at a
standard thumbnail width. Author and licence were read from each file's Commons metadata on 26 Oct; the credit
"Photo: author, Wikimedia Commons, licence" sits under every photo (hero: linked to the file page). Theme:
`er_stock_url()` and `er_commons_credit()` in `inc/template-tags.php`; heroes are `commons:` entries in Core
`data/seed.json` extraStock; section photos in `inc/world-guide-photos.php`.

| Page | Place | Commons file | Author | Licence |
|---|---|---|---|---|
| `g-edfu` | hero | Temple_of_Edfu,_Pylon,_Egypt.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-edfu` | what-you-will-see | Edfu_Temple_pylon_from_inner_court.jpg | Matthias Kabel | CC BY-SA 3.0 |
| `g-edfu` | what-you-will-see | Edfu-Relief.jpg | Steven C. Price | CC BY-SA 4.0 |
| `g-hatshepsut` | hero | Templo_funerario_de_Hatshepsut,_Luxor,_Egipto,_2022-04-03,_DD_13.jpg | Diego Delso | CC BY-SA 4.0 |
| `g-hatshepsut` | what-you-will-see | Thebes,_Luxor,_Egypt,_Temple_of_Hatshepsut,_Deir_el-Bahari.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-hatshepsut` | what-you-will-see | Cavetto_and_Osiris_statue,_Mortuary_Temple_of_Hatshepsut,_Deir_el-Bahari,_Egypt_-_20110516.jpg | Alexander Baranov | CC BY 2.0 |
| `g-wadi-hitan` | hero | Wadi_Hitan_Sunset.jpg | Medomadrex | CC BY-SA 4.0 |
| `g-wadi-hitan` | what-it-is | The_whales_fossils_at_Wadi_El-Hitan,_Egypt_2017.jpg | AhmedMosaad | CC BY-SA 4.0 |
| `g-wadi-hitan` | what-it-is | Dorudon_atrox_fossil_at_Wadi_El-Hitan,_Egypt,_March_2008.jpg | Christoph Rohner | CC BY-SA 3.0 |
| `g-wadi-hitan` | wadi-el-rayan | Fennec_FOX_-_Valley_of_the_Whales_-_Wadi_al_Hitan_2.jpg | Khaled Mokhtar | CC BY-SA 4.0 |
| `g-wadi-gemal` | hero | 27057-_Wadi_el_Gemal_landscape.jpg | Panegyrics of Granovetter | CC BY-SA 2.0 |
| `g-wadi-gemal` | what-you-will-see | WadiElGimal3.jpg | Roland Unger | CC BY-SA 4.0 |
| `g-wadi-gemal` | what-you-will-see | Sooty_Gull_in_Ras_Baghdadi_-_Wadi_El-Gemal_National_Park,_Red_Sea_7.jpg | Mohammad Chawki | CC BY-SA 4.0 |
| `g-kom-ombo` (27 Oct) | hero | At_the_Temple_of_Kom_Ombo.jpg | Masgalal | CC BY-SA 4.0 |
| `g-kom-ombo` | design | Temple_of_Sobek_and_Haroeris.jpg | Ahmed Yousry Mahfouz | CC BY-SA 4.0 |
| `g-kom-ombo` | design | Temple_of_Kom_Ombo,_Egypt.jpg | Carole Raddato | CC BY-SA 2.0 |
| `g-philae` (27 Oct) | hero | 27133-_approach_to_the_island_of_Agilkia.jpg | Panegyrics of Granovetter | CC BY-SA 2.0 |
| `g-philae` | isis | Agilkia_Isis-Tempel_44.jpg | Olaf Tausch | CC BY 3.0 |
| `g-philae` | what-you-will-see | Agilkia_Trajankiosk_01.JPG | Olaf Tausch | CC BY 3.0 |
| `g-east-bank` (27 Oct, added to the existing Unsplash photos) | karnak | 134_Säulen_in_16_Reihen_geordnet_trugen_das_Dach_der_Halle._12.jpg | Holger Uwe Schmitt | CC BY-SA 4.0 |
| `g-valley-kings` (27 Oct) | what-it-is | King_valley.jpg | Dmitri tissot | CC BY-SA 4.0 |
| `g-medinet-habu` (27 Oct) | hero | Thebes,_Medinet_Habu,_Egypt,_Temple_of_Ramesses_III,_First_Pylon.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-medinet-habu` | reliefs | Thebes,_Medinet_Habu,_Egypt,_Temple_of_Ramesses_III,_Reliefs.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-qaitbay` (27 Oct) | hero | Citadel_of_Qaitbay,_Alexandria,_Egypt.jpg | Argenberg | CC BY 2.0 |
| `g-qaitbay` | what-you-will-see | A_view_of_Alexandria_from_the_Citadel_of_Qaitbay.jpg | Slyronit | CC BY-SA 4.0 |
| `g-luxor-temple` (29 Oct) | hero | Templo_de_Luxor,_Luxor,_Egipto,_2022-04-01,_DD_01.jpg | Diego Delso | CC BY-SA 4.0 |
| `g-luxor-temple` | inside | Luxor_Temple,_The_Colonnade_of_Amenhotep_III,_Luxor,_Egypt.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-egyptian-museum` (29 Oct) | hero | Facade_of_the_Egyptian_Museum,_Tahrir_Square,_Cairo,_Egypt1.jpg | Diego Delso | CC BY-SA 3.0 |
| `g-egyptian-museum` | highlights | The_Narmer_Palette,_early_First_Dynasty,_ca._3000_BCE;_Egyptian_Museum,_Cairo_(1).jpg | Richard Mortel | CC BY 2.0 |
| `g-cairo-citadel` (29 Oct) | hero | Cairo,_Cairo_Citadel,_Muhammad_Ali_Mosque,_Egypt.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-cairo-citadel` | history | The_massive_walls_of_the_Cairo_Citadel_(14815621603).jpg | Jorge Láscar | CC BY 2.0 |
| `g-nubia-museum` (29 Oct) | hero | Aswan_Nubian_Museum_entrance.jpg | Marc Ryckaert | CC BY 3.0 |
| `g-nubia-museum` | inside | Nubian_Museum,_Aswan,_Egypt,_Egyptian_antiquities.jpg | Vyacheslav Argenberg | CC BY 4.0 |
| `g-islamic-art-museum` (29 Oct) | hero | Museum_of_Islamic_Art_Cairo_DSCF7680.jpg | R Prazeres | CC BY-SA 4.0 |
| `g-royal-jewelry-museum` (29 Oct) | hero | Royal_Jewelry_Museum_Alexandria.jpg (a painted ceiling in the palace) | Mohamed abdelzaher | CC BY-SA 4.0 |
| `g-kalabsha` (29 Oct) | hero | Neu-Kalabscha_Tempel_04.JPG | Olaf Tausch | CC BY 3.0 |
| `g-kalabsha` | rescue | Kalabsha_temple_on_Lake_Nasser_(2828516333).jpg | kmf164 | CC BY-SA 2.0 |
| `g-hurghada-museum` (29 Oct) | hero | Hurghada_Museum_building_in_September_2023.jpg | Mosbatho | CC BY 4.0 |
| `g-ras-mohammed` (29 Oct) | hero | Paisaje_marino_en_el_parque_nacional_Ras_Muhammad,_Egipto,_2022-03-29,_DD_45.jpg | Diego Delso | CC BY-SA 4.0 |
| `g-wadi-el-rayan` (29 Oct) | hero | Wadi_el-Raiyan_by_Hatem_Moushir_3.JPG | Hatem Moushir | CC BY-SA 3.0 |
| `g-wadi-degla` (29 Oct) | hero | Wadi_Degla_7.JPG | Waelfouda | CC BY-SA 4.0 |
| `g-petrified-forest` (29 Oct) | hero | MaadiForestWood1.jpg (800 px wide, the only free photo found of the site) | Roland Unger | CC BY-SA 3.0 |
| `g-nabq` (29 Oct) | hero | Avicennia_marina_-_Nabq_by_Hatem_Moushir_9.JPG (mangroves at Nabq) | Hatem Moushir | CC BY-SA 3.0 |
| `g-abu-galum` (29 Oct) | hero | Beach_Abu_Galoum_Protected.jpg | Mohamed3171979 | CC BY-SA 4.0 |
| `g-lake-qarun` (29 Oct) | hero | A_bout_in_Qarun_Lake.jpg | Aya Gallab | CC BY-SA 4.0 |
| `g-saint-catherine-protectorate` (29 Oct) | hero | Sant_katrin_mountain_sinai_04.jpg | OmarMoghazy99 | CC BY-SA 4.0 |
| `g-siwa-protectorate` (29 Oct) | hero | The_hill_of_the_dead_-_panoramio.jpg (Gebel al-Mawta, Siwa) | Vojife | CC BY 3.0 |
| `g-taba-protectorate` (29 Oct) | hero | Wadi_Ghazala_00_(61).jpg (Wadi Ghazala, named in the EEAA text) | لا روسا | CC BY-SA 4.0 |
| `g-burullus` (29 Oct) | hero | Burullus_Lake.jpg | Mohamed Mostafa Abdelraheem | CC BY-SA 4.0 |
| `g-omayed` (29 Oct) | hero | Omayed_Protected_Area_in_2017_,_photo_by_Hatem_Moushir_4.jpg (a wild plant in the protected area) | Hatem Moushir | CC BY-SA 4.0 |
| `g-mummification-museum` (27 Oct) | hero | Musée_Momification_-_Louxor_(EG)_-_2025-12-10_-_1.jpg | Chabe01 | CC BY-SA 4.0 |
| `g-mummification-museum` | what-you-will-see | Mummification_museum_Luxor.jpg | MoGamal11 | CC BY-SA 4.0 |
| `g-alexandria-museum` (27 Oct) | hero | Musee_national_-_alexandrie_facade_vue_large.JPG | Papillus | CC BY-SA 3.0 |
| `g-alexandria-museum` | what-you-will-see | EWUG_visit_to_Alexandria_National_Museum,_April_2025_-_007.jpg | Amr F.Nagy | Public domain |

Guide cards elsewhere (lists, related guides) show the hero photo without a credit line; the credit is on the page itself.

Trip pages `exp-hitan` and `exp-gemal` (26 Oct, theme 1.3.21): Commons photos through `docs/prototypes/journey-v4/wpick.json`
(world opener and steps; the opener now shows its credit, linked to the file page) and `commons:` card photos in Core extraStock.

| Trip | Place | Commons file | Author | Licence |
|---|---|---|---|---|
| `exp-hitan` | opener | Wadi_Hitan_Sunset.jpg | Medomadrex | CC BY-SA 4.0 |
| `exp-hitan` | step 1, card | The_whales_fossils_at_Wadi_El-Hitan,_Egypt_2017.jpg | AhmedMosaad | CC BY-SA 4.0 |
| `exp-hitan` | step 2 | Wadi_El_Hitan_Rockforms.jpg | Tom Horton | CC BY-SA 2.0 |
| `exp-hitan` | step 3 | Waterfalls_of_Wadi_El_Rayan,_Fayoum,_Egypt.jpg | Ibrahim El-Mezayen | CC BY-SA 4.0 |
| `exp-gemal` | opener | 27057-_Wadi_el_Gemal_landscape.jpg | Panegyrics of Granovetter | CC BY-SA 2.0 |
| `exp-gemal` | step 1 | WadiElGimal2.jpg | Roland Unger | CC BY-SA 4.0 |
| `exp-gemal` | step 2, card | WadiElGimal3.jpg | Roland Unger | CC BY-SA 4.0 |
| `exp-gemal` | step 3 | Sooty_Gull_in_Ras_Baghdadi_-_Wadi_El-Gemal_National_Park,_Red_Sea_7.jpg | Mohammad Chawki | CC BY-SA 4.0 |

Trip page `exp-luxor-hurghada` (31 Oct 2026, theme 1.3.55): only photos already approved and in use, no new files.

| Trip | Place | Photo | Approved as |
|---|---|---|---|
| `exp-luxor-hurghada` | opener, card, GetYourGuide offer | Unsplash 1623417765161-c71d6c83cfee | `exp-valley` opener (prototype photo, 18 Oct) |
| `exp-luxor-hurghada` | step Karnak | Unsplash 1761056981183-3faf66a62d6e | Luxor temple columns (§ Luxor, KEEP) |
| `exp-luxor-hurghada` | steps Valley of the Kings, Hatshepsut | prototype keys `kv62`, `hat_l` (as `exp-valley`) | `exp-valley` steps |
| `exp-luxor-hurghada` | step Colossi | Colosos_de_Memnón,_Luxor,_Egipto,_2022-04-03,_DD_131.jpg, Diego Delso, CC BY-SA 4.0 | `exp-valley` step 3 |

