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
