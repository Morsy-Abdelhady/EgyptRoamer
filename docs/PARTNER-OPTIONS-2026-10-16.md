# Second partner: options checked on 2026-10-16

**Why:** every booking today goes through one programme, Viator. See `docs/PM-REVIEW-2026-10-15.md`, recommendation 8.

**Order agreed with the owner:**
1. A hotel partner.
2. A second tours partner.
3. Transfers and car hire, later.

Only what the official pages say is recorded below. Third-party directories disagree on rates and cookie windows, so their figures are not used.

## 1. Booking.com (hotels and more)

- **Official page:** https://www.booking.com/affiliate-program/v2/index.html (read 2026-10-16).
- **Sign-up route:** "Register" on that page, select your region, then complete the registration with **CJ**. The page says registration is free.
- **Products:** accommodations, plus transportation options and attractions.
- **Pays:** "a commission on qualified bookings made through your integrations".
- **CJ programme page:** https://www.cj.com/en-gb/publisher/partners/booking.com (FAQ read 2026-10-16).
  - **Applying:** "select your primary traffic region and complete the application form". One region per application.
  - **Eligibility:** an active website, blog or social profile; social-only publishers are not accepted. Cashback and coupon restrictions apply in some regions.
  - **Commission:** starts at 4% for completed accommodation stays, 6% for car rentals, 4% for attractions, £2/€2 per flight and 4% for taxis. It may vary with performance and volume.
  - **Tracking is session based:** commission counts only if the user completes the booking in the same browser session as the click. There is no multi-day cookie, so a traveller who books a hotel days later is not credited. This weighs against Booking.com for a planning-stage site like ours.
  - **Vouchers:** Booking.com gives none. Publishers listing discount vouchers are removed.
  - **App bookings:** tracked.
- **Russia:** Booking Holdings suspended travel services in Russia and Belarus in March 2022, and no resumption was found (Phocuswire, Meduza). Russian-speaking visitors in Russia likely cannot book through these links, so the RU pages need another hotel partner.
- **Region chosen for our audience (owner: Europe, Russia, China):** United Kingdom, since the English pages are the base of every language and are read across Europe.
- **On our site:** a booking box in each destination's "Where to stay" section, and later on the itinerary guides. It complements Viator: a hotel is a separate spend from a tour.

## 2. GetYourGuide (tours and activities)

- **Official page:** https://partner.getyourguide.com/ (read 2026-10-16). Sign-up is direct, from "Sign up" on that page.
- **Rate and payment:** "a minimum commission rate of 8%, paid monthly".
- **Terms:** https://www.getyourguide.com/c/partner-terms-and-conditions. Points that matter for us:
  - **Non-exclusive:** we can promote Viator alongside it. Competitors' prices, if shown, must be accurate.
  - **No paid search on their brand:** GetYourGuide's name may not be used in paid search ads (keywords, misspellings, or brand plus generic terms). This matters if we ever run Google Ads.
  - **No paid traffic from third-party platforms:** driving traffic to GetYourGuide from paid search or other advertising on platforms we don't own is prohibited.
  - **Brand use:** no domains, handles or names confusingly similar to GetYourGuide's.
  - **Tracking window:** not stated on the official page or in the terms.
- **On our site:** a second offer on the experiences where it has the same trip. It also ranks for our key queries, such as Abu Simbel in AR and EN (`docs/SEO-QUERY-MAP-ALL-LANGUAGES.md`).

## What the owner does, and what we do

- **Owner:**
  - signs up in each programme (account, tax and payout details);
  - sends the affiliate ID or approved links.
  - Credentials and payment details never pass through us.
- **We:**
  - add the provider in Core;
  - add each offer only after checking its product page;
  - tag campaigns per language and placement, as for Viator;
  - run `tools/qa/page-coverage.mjs`.

## 3. Hotels for Russian and Chinese travellers (researched 2026-10-16)

The owner's target is tourists from Europe, Russia and China. Booking.com does not serve Russia (suspended since March 2022), so these were checked on official pages.

### Trip.com (recommended: one hotel partner for every language, Russian included)
- **Official programme page:** https://www.trip.com/partners/index
  - Free to join.
  - "7% max. basic commission".
  - 30-day website cookie (7 days for the app).
  - 1.7M+ accommodation options.
- **Official help page:** https://www.trip.com/ask/questions/trip.com-affliate-program.html
  - **Hotel commission tiers:** 5% for 0–199 completed bookings a month, 6% for 200–999, 7% for 1,000+.
  - **Payment:** paid on completed trips, by bank transfer in USD or HKD, minimum payout USD 200.
- **Language versions** (hreflang on trip.com): ru, de, fr, it, es, ar-SA, and Traditional Chinese (hk.trip.com, tw.trip.com).
  - There is no Simplified Chinese version. Mainland China is served by Ctrip.
- **Russian payments:** blogs report that some Russian cards (Mir, except Sberbank's) and SBP work. This is not official, so check before promising anything on the site.
- **Versus Booking.com for our site:** 30-day tracking instead of session-only, 5% instead of 4% on hotels, and Russian-language coverage.

### Ctrip (携程开放平台, u.ctrip.com): not feasible
- The official beginner's guide requires:
  - an identity check with a Chinese ID card (身份证) for individuals, or a Chinese business licence for companies;
  - a bank account in the same name;
  - invoices (发票) from company accounts.
- Order tracking lasts one week.
- An Egypt-based owner cannot meet these requirements.

### Yandex Travel (Яндекс Путешествия): possible, not verified for Egypt
- **Official page:** https://yandex.ru/support/travel-distr/ru/
  - Sites, blogs and channels can join.
  - Non-residents of Russia may join, but pay their own taxes (page "Налоги").
- **Not checked:**
  - whether it sells hotels in Egypt: travel.yandex.ru showed a robot check, which we do not bypass;
  - how non-residents are paid.

### Ostrovok.ru: only through Travelpayouts
- Travelpayouts lists 6%. This is not confirmed on Ostrovok's own pages.

### Plan
1. **Trip.com** as the hotel partner for all languages. The owner signs up, then sends the affiliate ID.
2. **ZH pages:**
   - Point to Trip.com (English or Traditional Chinese) and say so honestly.
   - Mainland China has no workable programme for us today.
3. **Yandex Travel:** revisit for the RU pages after Trip.com, if Egypt hotels and non-resident payouts are confirmed with Yandex support.
