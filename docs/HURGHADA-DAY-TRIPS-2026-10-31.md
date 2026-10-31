# Day trips from Hurghada (2026-10-31)

**Owner decision (31 Oct):** start the higher-value products with day trips from the Red Sea resorts, beginning with Luxor from Hurghada.

## Shortlist (public partner search pages, 31 Oct; review counts were used only to choose, never shown on the site)

| Trip | Partner | Length (partner) | Reviews at the time |
|---|---|---|---|
| Luxor Day Trip from Hurghada Small Group & Tutankhamun Tomb (t485960) | GetYourGuide | 14 hours – 1 day | 8,908 |
| Cairo by Big Bus full day from Hurghada (d800-256742P3) | Viator | 18–21 hours | 739 |
| From Hurghada: Cairo's Pyramids & Grand Museum (d800-14976P7) | Viator | 18 hours | 577 |
| Private Day Trip to Luxor from Hurghada and Valley of the Kings (d800-481754P5) | Viator | 12–16 hours | 255 |
| Full-Day Private Tour to Luxor from Hurghada with Lunch (d800-224493P11) | Viator | 9–12 hours | 253 |
| Private Day Tour to Cairo with flights and lunch from Hurghada (d800-128688P52) | Viator | 8–14 hours | 160 |

## Live on 31 Oct (Theme 1.3.55, Core 1.2.107, commit `87ce288`)

- **Trip:** `exp-luxor-hurghada`, "Luxor Day Trip from Hurghada", English #1559, translations #1560–1566 (Guide translations page).
- **Offers:**
  - Viator **#1567** `private-luxor-day-trip-from-hurghada` (private, about 12–16 hours, pickup, free cancellation), priority 10.
  - GetYourGuide **#1569** `gyg-luxor-day-trip-from-hurghada-small-group` (small group, 14 hours, pickup, free cancellation), priority 3; card photo is the approved Valley of the Kings Unsplash photo.
  - Both: destination Hurghada (#21), experience #1559, the provider's usual tracking lines copied from an existing offer of the same partner.
- **Live check:** all 8 language pages return 200 with the translated title and both offers; `/go/` reaches the Viator and GetYourGuide product pages with tracking; the Hurghada place page and the Valley of the Kings guide link to the trip; `rtl-direction`: 87 Arabic pages all ok. `page-coverage`: 672 pages (experiences 120), 1 problem (phone booking bar on the Arabic balloon page, not this trip); rechecked twice on a phone viewport, the bar shows, so a slow load during the run.
- **QA note:** one test redirect per offer with placement `qa-check` on 31 Oct. Discount them in Visits.

## Cairo trip live on 31 Oct (Theme 1.3.56, Core 1.2.108, commit `c684886`)

- **Owner's OK:** "Yes, Cairo next" (31 Oct). By flight, since the two strongest options fly (GetYourGuide t1122404 had 4,417 reviews at the time).
- **Trip:** `exp-cairo-hurghada`, "Cairo Day Trip from Hurghada by Flight", English #1571, translations #1572–1578.
- **Offers:** Viator **#1579** `private-cairo-day-trip-from-hurghada-by-flight` (private, about 8–14 hours, pickup, free cancellation), priority 10; GetYourGuide **#1581** `gyg-cairo-day-trip-from-hurghada-by-flight` (16–17 hours, pickup, free cancellation; no group size stated, so no group fact), priority 3. Destination Hurghada (#21), experience #1571. The browser connection dropped once while publishing; the offers list shows no duplicate.
- **Live check:** all 8 pages 200 with both offers; `/go/` reaches both partners with tracking; the Hurghada page and the Grand Egyptian Museum guide link to the trip; `rtl-direction` 88 Arabic pages all ok; `page-coverage` 680 pages all ok.
- **QA note:** one test redirect per offer with placement `qa-check`.

## Next (not done)
- Klook and Tiqets were not searched for these trips yet.

## Klook and Tiqets options (checked 31 Oct 2026, partner pages in the owner's Edge)

**Luxor from Hurghada**
- Tiqets p1136071 "Luxor: Guided Day Tour from Hurghada + Lunch": 14 h, start 04:00, max group 35, hotel pickup, entry to the Valley of the Kings, Hatshepsut, Colossi and Karnak included, lunch, free cancellation up to 12 h. Not included: Tutankhamun and Ramses tombs, Nile boat, drinks, a per-person tax. Recommended (only option found with entrance fees included).
- Tiqets p1101699 "Luxor Valley: Guided Tour + Roundtrip Transfer from Hurghada + Lunch": 15 h, start 03:00, max 100, meeting point Hurghada City Center (pickup outside the centre costs extra), entrance fees not included, free cancellation 24 h.
- Tiqets p1003648 "Valley of Kings, Hatshepsut, Karnak from Hurghada + Lunch": 17 h, hotel pickup in Hurghada, free cancellation 19 h.
- Klook 151126 "Hurghada Luxor Ancient Sites Full-Day Small-Group Tour": 14 h (04:00-18:00), small group (1-20, max 8 per van), hotel pickup in Hurghada, Sahl Hasheesh, Makadi, El Gouna, Safaga, Soma Bay, admissions optional, free cancellation 24 h. Recommended.

**Cairo from Hurghada by flight**
- Tiqets: no Cairo day trip from Hurghada found (searched "Cairo Hurghada", "Cairo by plane from Hurghada").
- Klook: only road trips (minivan or car), e.g. 161075 "Hurghada Giza Pyramids & Grand Egyptian Museum Full-Day Tour" (car, small group 2-8). None by flight, so none fits the page as written.
