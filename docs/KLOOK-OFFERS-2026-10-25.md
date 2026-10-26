# Klook offers (2026-10-25)

**Owner decision (2026-10-25):** link Klook as the third trips partner, starting with 4 offers. 2 are linked; 2 are waiting for the owner (below).

## Account
- **Account:** approved on 25 Oct. Affiliate ID (AID) **140689**; it appears in every link, so it is public.
- **Website:** Egypt Roamer, `https://egyptroamer.com`, registered as Travel Blog/Guide with target region Germany.
- **Financial info:** empty. Bank, tax and ID details are for the owner to enter. Klook says changes must be made before the 8th of each month.
- **Link format:** Klook's own page (My Ads → Link converter) says to add `?aid=<AID>` to a `www.klook.com` link, and that `s.klook.com` links can't be tracked.
  - So each offer's tracking parameter is `aid=140689`.
  - Klook's link converter also has three labels for sub-IDs. Their parameter names are not confirmed (it needs one saved link), so for now there is no per-language or per-placement campaign. Klook clicks can't be split by page yet.

## Offers linked (facts read on klook.com, 25 Oct)

| Offer slug | Our trip | Klook product (title as shown) | Facts |
|---|---|---|---|
| `klook-cairo-nile-dinner-cruise-with-pickup` | `exp-dinner` | Cairo Nile River Dinner Cruise Experience with pickup, https://www.klook.com/en-US/activity/118903-private-dinner-cruise-on-the-nile-river-in-cairo-with-pickup/ | Join-in or private groups (so no group fact), cruise 2 hours, hotel pickup in Cairo or Giza, free cancellation (24 hours notice) |
| `klook-2-day-white-and-black-desert-camp-from-cairo` | `exp-safari` | Cairo 2 Days Black and White Desert Camp Star Watching, 4WD w/ BBQ, https://www.klook.com/en-US/activity/135009-cairo-2-days-black-and-white-desert-camp-star-watching-4wd-w-bbq/ | Small group (1-20), 2 days, hotel pickup in Cairo or Giza, free cancellation (24 hours notice) |
| `klook-luxor-sunrise-hot-air-balloon` | `exp-balloon` (new trip, below) | Sunrise Hot Air Balloon Ride Experience in Luxor, https://www.klook.com/en-US/activity/124011-sunrise-hot-air-balloon-ride-experience-in-luxor-with-pickup/ | Join-in group (so no group fact), 3 hours, hotel pickup in Luxor, free cancellation (24 hours notice) |

- No prices, ratings, review counts or photos were copied.
- Each offer uses its trip's own approved photo.
- The offers have priority 2, so the Viator (10) and GetYourGuide (3) options show first.

## Waiting for the owner

| Klook product | Why it is not linked yet |
|---|---|
| The Grand Egyptian Museum Ticket (activity 179694) | There is no trip page for it to sit on. In proposal 4, places show trips and hotels only, and guides show offers only through their trips. The ticket also has **no cancellation**, while the site says "Free cancellation on every trip". |

## Live on 25 Oct (Theme 1.3.17, Core 1.2.68, commit `fc40934`)
- **Live records:** provider **Klook #953**; offers **#955** (Nile dinner cruise) and **#957** (desert camp).
- **Live check, all passed:**
  - Both trip pages show the Klook option in EN, DE, AR and ZH.
  - The About page names Klook in all 8 languages, after the migration ran.
  - `/go/` leads to the Klook product with `?aid=140689`.
  - `page-coverage.mjs`: all ok, 384 pages.
- **QA note:** one test request to `/go/klook-cairo-nile-dinner-cruise-with-pickup/` (placement `experience-offers`) on 25 Oct may show as a click in Visits. Discount it.

## Balloon trip live on 25 Oct (Theme 1.3.18, Core 1.2.69, commit `2e3bd00`)
- **New trip:** `exp-balloon`, "Hot-Air Balloon over Luxor at Sunrise".
  - English #959, translations #960–966, created by Egypt Roamer → Guide translations.
  - Translations are `review: pending`.
- **The sync also "updated" 6 English guides:** `g-cairo-museums`, `g-east-bank`, `g-great-sand-sea`, `g-red-sea-dives`, `g-sinai` and `g-west-bank`.
  - Their live bodies already matched the files, character for character.
  - The "update" comes from a curly apostrophe in each title (`get_the_title()` gives `’`, the file has `'`), so every run rewrites the same title and content.
  - Nothing a visitor sees changes, but it shows as a pending update on every run.
- **Offer:** Klook **#967**, `klook-luxor-sunrise-hot-air-balloon`, on trip #959, with destination Luxor.
- **Guide:** `g-luxor-balloon` (#907) is related to the trip.
  - The meta was set through the REST API, so the body was not re-saved and stays syncable.
  - The source file has the same relation.
- **Live check, all passed:**
  - All 8 languages return 200 with the translated title and the Klook option (3 hours · pickup · free cancellation). No English is left in the translated worlds.
  - The guide, the Luxor place page and Trips & tours link to the trip.
  - EN desktop and AR phone were viewed in a browser: no overflow, RTL correct.
  - `page-coverage.mjs`: all ok, 392 pages.

## Two more trips (owner, 26 Oct 2026)

Owner: "add the two Klook trips as new trips in 8 languages, without ratings, and link them to the Wadi Al-Hitan and
Wadi El-Gemal pages". Facts read on klook.com on 26 Oct; no prices, ratings, reviews or photos copied.

| Offer slug | Our trip | Klook product | Facts |
|---|---|---|---|
| `klook-wadi-el-hitan-and-wadi-el-rayan-from-cairo` | `exp-hitan` | Full-Day Tour to Wadi El Hitan & Wadi El Rayan from Cairo, https://www.klook.com/en-US/activity/124305-cario-tour-to-whales-valley-wadi-el-rayan-waterfalls-in-el-fayoum/ | Private group, 10 hours, hotel pickup in Cairo or Giza, free cancellation (24 hours notice). Itinerary: Wadi El Hitan, Wadi El Rayan waterfalls, Tunis village, lunch |
| `klook-wadi-el-gemal-safari-from-marsa-alam` | `exp-gemal` | Discover the Beauty of Wadi El Gemal Tours, https://www.klook.com/en-US/activity/134012-discover-the-beauty-of-wadi-el-gemal-tours/ | Join-in group (so no group fact), hotel pickup in Marsa Alam, 4×4 jeep, guide and lunch, free cancellation (24 hours notice). **No duration:** the page states both "7hr" and "5 hour(s)" |

- Guides: `g-wadi-hitan` → `exp-hitan`, `g-wadi-gemal` → `exp-gemal` (source files; the live meta is set once, as for the balloon).
- API: on 26 Oct the owner sent Klook a request for the Activity Search API (contact form, 200-character limit), to show ratings and prices the way Viator's API does. Until Klook answers, Klook options show no ratings.
