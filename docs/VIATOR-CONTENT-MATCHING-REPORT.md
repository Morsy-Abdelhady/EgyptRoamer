# Viator content-matching report (2026-10-15)

**What it covers:** the page-to-product mapping for all 8 Experience pages. It supersedes the listing-page offers described in `VIATOR-AFFILIATE-INTEGRATION-REPORT-2026-10-15.md` §3.

**Offer slugs:** unchanged. Only each offer's Target URL, title and CTA changed. The Siwa offer is new.

## 1. How each product was verified

1. **viator.com can't be reached from this network:** `ERR_CONNECTION_RESET` in two browsers, and a CAPTCHA for page readers.
2. **Tripadvisor sells the same Viator inventory under the same product code.** For each candidate, its live Tripadvisor product page was opened in Chrome, and these were read:
   - title;
   - operator;
   - rating and number of reviews;
   - duration;
   - "What's included";
   - itinerary or description;
   - booking calendar, showing it's bookable for upcoming dates;
   - recent-booking hint;
   - the product code: the single `contentId` on the page, the same as "reference the product code: …".
3. **The Viator URL for that exact code** came from one of two places:
   - the Wayback Machine index (`web.archive.org/cdx`) of real viator.com URLs, with status and date;
   - Viator's own indexed page, via web search.
4. **Selection rules:**
   - same activity and place as our page, same format where possible;
   - the best-reviewed product among the true matches;
   - small-group products were rejected where our page describes a private tour, and products with weak ratings (e.g. 3.9) were rejected.
5. **On our site:**
   - no price, rating, review count, inclusion, duration or availability is shown;
   - the card shows only a descriptive title, the location and a translated "Check Availability" CTA;
   - the affiliate disclosure is next to it.

## 2. Mapping

| Experience (EN page) | Viator product (code), operator | Provider URL (Target URL) | Affiliate URL / slug | Location / activity match, evidence | CTA, disclosure | Redirect test (prod) | Class | Status |
|---|---|---|---|---|---|---|---|---|
| Abu Simbel Day Trip from Aswan | "Abu Simbel Private Full-Day Tour from Aswan" (221454P2), Egypt wonderful tour | `/tours/Aswan/day-tour-to-abusimble-private/d796-221454P2` | `/go/abu-simbel-temples-tours-on-viator/` | Private full day from Aswan; Egyptologist guide, A/C car, water, "all fees and taxes" included; 4.9 (117); URL archived 2024–Aug 2025 (200) | Check Availability (8 languages); disclosure ✔ | ✔ 302 → 302 → product, `pid mcid medium campaign=er-{lang}-{placement}` | **VERIFIED SPECIFIC OFFER** | Published |
| Valley of the Kings & Hatshepsut Temple | "Full Day Tour of Luxor West Bank Temples and Tombs (Private)" (224493P18), Luxor Egypt Tours | `/tours/Luxor/Full-Day-Tour-of-Luxor-West-Bank-Temples-and-Tombs/d826-224493P18` | `/go/valley-of-the-kings-tours-on-viator/` | Private; Valley of the Kings, Hatshepsut, Colossi of Memnon, Valley of the Queens (our page mentions these add-ons); 4.9 (716); 7–10 h | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** | Published |
| Pyramids of Giza & Sphinx Private Tour | "Half Day Tour Giza Pyramids & Great Sphinx with Private Tour Guide" (308879P1), Gem Explore Egypt | `/tours/Cairo/Cairo-Half-Day-Tour-to-Giza-Pyramids-and-Sphinx/d782-308879P1` | `/go/giza-pyramids-tours-on-viator/` | Private, half day (4–5 h), licensed Egyptologist, hotel pickup, A/C; shopping stop optional ("can be skipped"); 4.9 (1,864); URL used by other Viator partners in 2025 | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** | Published |
| Nile Dinner Cruise with Live Show | "Cairo Dinner Cruise in Nile River With Belly Dancer & Tanora show" (37291P188), Hesham Egypt Tour Guide | `/tours/Cairo/Cairo-dinner-cruise-7-to-9-pm/d782-37291P188` | `/go/cairo-nile-dinner-cruises-on-viator/` | Cairo; buffet, belly dancing and tanoura (our page names the tanoura); hotel transfer; 4.8 (296). The cruise ticket is a booking option, not in the base price | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** | Published |
| Red Sea Diving: Two Reef Dives | "2-Dive adventure: Safe & Top-rated boat for Certified Divers" (13172P2), Blue Paradise Diving Center | `/tours/Hurghada/DAY-OF-SCUBA-DIVING-BY-BOAT-FOR-CERTIFIED-DIVERS/d800-13172P2` | `/go/hurghada-scuba-diving-trips-on-viator/` | Hurghada; 2 guided dives, 2 × 12 L tanks, PADI guides, hotel pickup; 4.9 (250). **Certified divers only**, stated in the offer title. Our page also covers beginners and Sharm, which this product does not | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** | Published |
| Cairo Street Food Tour by Night | "Cairo Nights Food Tour with 15+ Tastings" (69673P17), A Chef's Tour (Cairo) | `/tours/Cairo/Old-Cairo-Food-Tour-for-Eight-Exclusive-Guests/d782-69673P17` | `/go/cairo-food-tours-on-viator/` | Evening walk, 4 h, old-city backstreets, licensed guide, max 8 guests, starts at Al-Azhar Mosque; 4.9 (111); URL archived Aug 2025 (200) | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** | Published |
| White Desert & Black Desert Safari | "Black and White Desert Tour 2 Days 1 Night" (139021P1), Egypt Desert Star | `/tours/Cairo/Black-and-White-Desert-Tour-2-Days-1-Night/d782-139021P1` | `/go/bahariya-oasis-tours-on-viator/` | 2 days from Cairo via Bahariya, Crystal Mountain, Black and White Deserts, camping; meals included, park ticket extra; 4.8 (132); bookable | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** | Published |
| Great Sand Sea 4×4 & Hot Springs | "Siwa Oasis Tour All inclusive 3 Days Experience from Cairo & Giza" (387546P1), Luxury Tours Egypt | `/tours/Cairo/Siwa-Oasis-Tour-From-Cairo-3-days-Relax/d782-387546P1` | `/go/siwa-oasis-3-day-trip-from-cairo-with-a-great-sand-sea-safari/` | Includes the Great Sand Sea tour, a night at a hot-spring camp, Cleopatra's Pool, Temple of Amun; 4.7 (313). **Format differs:** our page is a day trip from Siwa, this is 3 days from Cairo. The title says so. The only Siwa day safaris on Viator have 2–6 reviews | ✔ | ✔ | **VERIFIED SPECIFIC OFFER** (different format, disclosed in the title) | Published (#516) |

**Not suitable** (rejected):
- Abu Simbel 8672P158: 3 reviews.
- Abu Simbel 22031P2: the guide and tickets are options only.
- Giza 88784P2: small group, not private.
- Giza 37291P185: includes a bazaar.
- Cruise 26835P1: 3.9 rating.
- Valley of the Kings 3124LUX01: 7 reviews.
- Siwa 118089P20 and 387546P9: 6 and 2 reviews.
- White Desert "d22954587": no Viator product code, so not bookable through Viator.

## 3. Behaviour tests (production, 15 Oct)

| Test | Result |
|---|---|
| CTA on the intended page, `/go/{slug}/` href, `rel="sponsored nofollow noopener"` | ✔ all 8 experiences, plus destination pages (Cairo, Luxor, Aswan, Hurghada, Siwa) |
| Redirect: `/go/` → `admin-post.php?action=er_go` → 302 to the verified product URL | ✔ 8/8 (redirect target read, not followed: viator.com is unreachable from here) |
| Tracking parameters preserved: `pid`, `mcid=42383`, `medium=link`, `campaign=er-{lang}-{placement}` | ✔ (e.g. `er-ar-experience-offers`, `er-de-experience-offers`, `er-en-destination-things`) |
| Click recorded in Click reports (offer, experience, destination, placement, language) | ✔ |
| Unknown or invalid slug fails safely | ✔ `/go/no-such-offer/` → home page (302), counted as `go_error` |
| Open redirect | ✔ none: the destination comes only from the offer record, and the host must be in the provider allow-list (`viator.com`) |
| Disclosure next to every partner link (offer boxes, card grids, homepage rail) | ✔ (theme 1.2.45) |
| Translated CTA in 8 languages | ✔ |
| CTA tap vs redirect | Two different metrics, never added together: "partner-link taps on the page" (Visits, a beacon before the hop) and "partner redirects logged by /go/" (the click log) |
| Real booking flow on viator.com | **[Not tested]**: unreachable from this network. **[User action]** Tap one CTA on your phone |

**QA test data, to exclude from business numbers:**
- **Visits:** 1 view (`utm_source=er-qa`).
- **`/go/` click log, 15 Oct (all QA):**
  - 11 clicks during the first offer verification;
  - 7 after the product update;
  - 4 during the Siwa and destination checks;
  - plus the destination and experience page checks in `types-audit`, which only read pages and do not click.

## 4. Content alignment

Checked: introduction, journey and imagery against the product, CTA wording, no unconfirmed promises.

| Page | Alignment | Note |
|---|---|---|
| Abu Simbel | ✔ | The immersive story (approved concept A) stays; offer box at the end ("Book with our partners") and hero button "Book with our partners → #plan" |
| Valley of the Kings | ✔ | Our page suggests what to add with more time; the product adds the Valley of the Queens |
| Pyramids | ✔ | Our page warns about touts on the plateau. The product's shopping stop is optional; the card makes no claim about it |
| Nile cruise | ✔ | Tanoura named on both |
| Red Sea diving | ✔ with a caveat | The product is for certified divers in Hurghada; the title says "certified divers". Beginners have no verified product yet (**[Gap]**) |
| Cairo street food | ✔ | Evening, about 4 h, guide, both match |
| White & Black Desert | ✔ | Both describe the same route |
| Great Sand Sea | ✔ with a caveat | Different format (multi-day from Cairo), stated in the title. A verified Siwa day safari would fit better (**[Gap]**) |

**Structure:**
- No experience page has duplicated sections, legacy narrative remnants or empty blocks (production audit of the 8 pages).
- No page promises prices, availability or inclusions.

## 5. Owner actions

1. Tap one CTA on your phone to confirm the Viator page opens. It is unreachable from my network.
2. Viator → Finance: payout details.
3. Optional: a beginner "discover scuba" product for the diving page, and a Siwa-based day safari when one has a real review record.

## Landing page on Viator (checked 15 Oct)

- **What happens:** with `pid`/`mcid` on the link, Viator sends every product link to its "Explore {city}" page (`/{City}/d…-ttd/p-{code}`).
  - The chosen trip is pinned first as "You selected", and related trips follow.
  - Without the affiliate parameters, the same URL opens the normal product page.
- **Viator's explanation:** its Partner Resource Center FAQ (create-links) says this landing page came out of A/B testing, and that "there is no way to redirect to a standard product display page".
- **What this means for us:**
  - Our `/go/` redirects send the exact product URL; all 8 were re-checked.
  - There is nothing to change on our side.

## Added 15 Oct (theme 1.2.51 / Core 1.2.36)

| Destination | Viator product (code), supplier | Target URL | Evidence | Status |
|---|---|---|---|---|
| Alexandria | "Alexandria Day Trip From Cairo" (59626P4), Ramasside Tours | `/tours/Cairo/Alexandria-Day-Trip-From-Cairo/d782-59626P4` | Viewed on viator.com in Edge: 4.7 from 1,489 reviews. Covers the Library, the Roman amphitheatre, the Corniche, Qaitbay and Montazah. Includes Egyptologist guide (option), hotel pickup and A/C car | Published, offer #574. The redirect test reaches the product with pid/mcid/campaign |
| Sharm El Sheikh | "Sharm El Sheikh: Ras Mohammed Snorkeling, Allah Gate & Magic Lake" (5616599P11), NASS TOURS | `/tours/Sharm-el-Sheikh/Sharm-el-Sheikh-Ras-Mohammed-Park-Magic-Lake-and-Snorkeling-Tour/d827-5616599P11` | Viewed on viator.com: 4.9 from 771 reviews. Bus from Sharm, snorkelling in Ras Mohammed National Park, the park's first highlight on our Sharm page | Published 15 Oct, after the owner's approval ("اعتمد"): offer #577, slug `ras-mohammed-snorkeling-trip-from-sharm-el-sheikh`. All 8 languages show the translated card with an image. The redirect test reaches the product with pid/mcid/campaign |
