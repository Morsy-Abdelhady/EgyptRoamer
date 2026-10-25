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

- No prices, ratings, review counts or photos were copied.
- Each offer uses its trip's own approved photo.
- The offers have priority 2, so the Viator (10) and GetYourGuide (3) options show first.

## Waiting for the owner

| Klook product | Why it is not linked yet |
|---|---|
| The Grand Egyptian Museum Ticket (activity 179694) | There is no trip page for it to sit on. In proposal 4, places show trips and hotels only, and guides show offers only through their trips. The ticket also has **no cancellation**, while the site says "Free cancellation on every trip". |
| Sunrise Hot Air Balloon Ride Experience in Luxor (activity 124011): join-in group, 3 hours, hotel pickup, free cancellation (24 hours notice) | There is no trip page for it. It needs a new balloon trip page in 8 languages, linked from the Luxor balloon guide. |

## Live on 25 Oct (Theme 1.3.17, Core 1.2.68, commit `fc40934`)
- **Live records:** provider **Klook #953**; offers **#955** (Nile dinner cruise) and **#957** (desert camp).
- **Live check, all passed:**
  - Both trip pages show the Klook option in EN, DE, AR and ZH.
  - The About page names Klook in all 8 languages, after the migration ran.
  - `/go/` leads to the Klook product with `?aid=140689`.
  - `page-coverage.mjs`: all ok, 384 pages.
- **QA note:** one test request to `/go/klook-cairo-nile-dinner-cruise-with-pickup/` (placement `experience-offers`) on 25 Oct may show as a click in Visits. Discount it.
