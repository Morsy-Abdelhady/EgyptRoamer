# Nile cruise: candidate offers (17 Oct 2026)

**Why:** in the first-time tourist review (`docs/UX-EXPERT-REVIEW-2026-10-17.md` §11), the Nile chapter and the map show the classic Aswan–Luxor route, but the site has no Nile cruise to book. A search for "nile cruise" found only the Cairo dinner cruise.

**Status:** nothing is added yet. Adding an offer needs the owner's OK.

**Method:** product pages were read on 17 Oct in the owner's browser. Only what each page states is recorded. Prices, ratings and reviews are noted here only to help the owner choose; they never go on the site.

## Recommended: one new trip, "Nile Cruise from Aswan to Luxor (4 days)", with 3 options

### 1. Viator: 4-Day 3-Night Nile Cruise from Aswan to Luxor & Abu Simbel + Balloon
- **URL:** https://www.viator.com/tours/Aswan/Nile-Cruise-3-nights-from-Aswan-to-Luxor/d796-29896P3 (product code 29896P3)
- **Supplier:** Imperialegypt
- **Duration:** 4 days (approx.), 3 nights on board
- **Departures:** Aswan, Monday, Wednesday or Friday; ends in Luxor
- **Pickup:** offered (hotel or Aswan airport; meeting point Aswan International Airport)
- **Group:** group tour (Abu Simbel is "by group tour")
- **Includes, per the page:**
  - Abu Simbel, Kom Ombo, Edfu, Karnak and Luxor temples;
  - a sunrise hot-air balloon flight;
  - the West Bank;
  - an Egyptologist guide;
  - full board.
- **Entrance fees:** listed separately ("Entrance Fees … $110.00 per person"); several stops say "Admission Ticket Not Included".
- **Cancellation:** free up to 24 hours before.
- **Popularity:** Viator's #1 most booked in Aswan in the last 48 hours; 2,883 reviews.

### 2. Viator: 4-Day 3-Night Nile Cruise from Aswan to Luxor – Private Tour
- **URL:** https://www.viator.com/tours/Aswan/4-Day-3-Night-Nile-Cruise-from-Aswan-to-Luxor-Private-Tour/d796-57870P4 (product code 57870P4)
- **Supplier:** Egypt Unlimited Tours
- **Duration:** 4 days (approx.)
- **Departures:** Monday, Wednesday or Friday; ends in Luxor around 12:00 on day 4
- **Pickup:** offered (any Aswan hotel, Aswan airport or train station)
- **Private:** private-guide option offered ("Double Room Private Guide"), or join a group
- **Includes, per the page:**
  - Philae, the High Dam, the Unfinished Obelisk, Kom Ombo, Edfu, Karnak, Luxor Temple and the West Bank, admission included;
  - full board.
- **Not included:** Abu Simbel and the balloon are optional extras.
- **Note:** passport details are required at booking.
- **Cancellation:** free up to 24 hours before.
- **Popularity:** 468 reviews.

### 3. GetYourGuide: From Aswan: 4 Days Nile Cruise Luxor Abu Simbel & Air Balloon
- **URL:** https://www.getyourguide.com/aswan-l543/4days-3-nights-nile-cruise-including-abu-simbel-air-balloon-t521042/ (product ID 521042)
- **Supplier:** Mody Egypt Tours
- **Duration:** 4 days, 3 nights from Aswan to Luxor
- **Pickup:** included (Aswan airport, railway station or hotel; drop-off in Luxor)
- **Includes, per the page:**
  - Abu Simbel, Kom Ombo, Edfu, Luxor Temple and the West Bank;
  - a hot-air balloon flight;
  - an Egyptologist guide.
- **Entrance fees:** "paid locally".
- **Ends:** in Luxor around 4–5 pm.
- **Cancellation:** free up to 24 hours before.
- **Popularity:** 543 reviews.
- **Photo:** GetYourGuide's photos may not be copied. Use the approved Unsplash photo already in the guides: "A cruise ship on the Nile, Aswan" by Kuan L (`1778402153163-fc9a5fd0131a`). Record it in `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5.

## Why these three
- They cover the classic route the site already describes: Aswan → Kom Ombo → Edfu → Luxor.
- They give the traveller a real choice:
  - an all-in group trip with Abu Simbel and the balloon (Viator, or GetYourGuide for the second partner);
  - a private guide with Abu Simbel left optional (Viator).
- Every option has free cancellation up to 24 hours before.

## What the trip page would say: facts only, from the pages above
- **Length:** 4 days, 3 nights.
- **Route:** Aswan to Luxor.
- **Departures:** Mon / Wed / Fri (Viator options).
- **Pickup:** offered.
- **Private:** private option available.
- **Cancellation:** free on every option.
- **Before you book:** entrance fees are extra on two options, and Abu Simbel and the balloon are extras on the private option.

## Next steps, after the owner's OK
1. Add the three offers in Core:
   - the providers already exist (Viator, GetYourGuide);
   - add the facts to `data/offer-facts.json`;
   - add the card text to `data/offers-i18n.json` in all languages.
2. Link the trip from:
   - the Nile chapter;
   - Luxor and Aswan;
   - the "Nile Cruise or Hotels?" guide;
   - the 7-, 10- and 14-day itineraries.
3. Add it to the prototype first (proposal 4).

## Added to the prototype (owner OK, 17 Oct)
The three offers are now one trip, "Nile Cruise from Aswan to Luxor", in proposal 4 (`docs/prototypes/journey-v4/v4-cruise.py`). It is not on the live site.

**Content and sources:**
- **Page text:** from our guide "Nile Cruise or Hotels? Luxor to Aswan Explained".
- **Option facts:** from the three product pages above.
- **Photos:** approved Unsplash photos already used in the guides.

**Comparison table:** it now also shows Abu Simbel, the balloon flight and entrance fees (included, extra or paid locally) whenever a trip's options differ on them.

**Where it appears:**
- the Nile chapter (now 3 trips);
- Trips & tours under Aswan and Luxor, and in the "2+ days" filter;
- search ("nile cruise" lists it first);
- the assistant;
- the Trip Builder (Ancient and Luxury interests) on routes through Aswan or Luxor;
- the "Nile Cruise or Hotels?" guide.

**For production later:** add the 3 offers in Core, with `offer-facts.json` and `offers-i18n.json` in all languages, and an experience page.
