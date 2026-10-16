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

### Status (2026-10-16)
- **Approved** as an Individual affiliate (Industry: Content; method: Click-out).
- **Account ID (Allianceid):** 11023279. **Site:** "Egypt Roamer", SID 333624383, URL https://egyptroamer.com (set in Account → Manage My Sites).
- **Link format,** confirmed in the portal's Affiliate Link tool: `<any trip.com URL>?Allianceid=11023279&SID=333624383&trip_sub1=<label>&trip_sub3=D20253592`.
  - `trip_sub1` is our tracking label, e.g. `er-{lang}-{placement}`. `trip_sub3` is added by the tool.
  - These IDs appear in every public link; they are not secrets.
- **Bank details:** empty on 2026-10-16. The owner fills them; we never do.
- **Live on the site (2026-10-16, theme 1.2.64, Core 1.2.44):**
  - Provider **Trip.com #823** (allowed domain `trip.com`).
  - Eight offers in the "Hotels" category, one per destination, plus Giza on Cairo (the Cairo page advises one or two nights near Giza): #826 Cairo (priority 2), #828 Giza, #830 Luxor, #832 Aswan, #834 Hurghada, #836 Sharm El Sheikh, #838 Alexandria, #840 Siwa.
  - Each points to Trip.com's city hotel list (`/hotels/<city>-hotels-list-<id>/`, every one opened and checked on 2026-10-16). CTA "Compare Options". No prices or ratings copied.
  - Language: `locale@<lang>=` lines open Trip.com in the reader's language (en-XX, ar-SA, de-DE, fr-FR, it-IT, es-ES, ru-RU, zh-HK; zh-HK is Traditional Chinese, the closest Trip.com has). `trip_sub1=er-{lang}-{placement}`.
  - Shown in a "Find a hotel in {name}" box at the end of "Where to stay" (Cairo, Luxor, Hurghada), else after "How long, and when to go".
  - Experience pages (theme 1.2.65, owner request the same day): the hotels of the experience's destination before the FAQ ("Find a hotel in {name}"); the Abu Simbel story shows them in its closing booking section. 9 of 10 experiences; the White & Black Desert safari has no destination (a camping trip from Cairo), so no hotel box.
  - Area offers added the same day from the areas the destination guides recommend and Trip.com's own area pages: #842 Downtown Cairo (`/hotels/cairo-hotels-list-332/location696398/`, Qasr El Nil), #844 Zamalek (`…/location252856/`), #846 Luxor West Bank (`/hotels/al-bairat-hotels-list-369885/`), #848 El Gouna (`/hotels/el-gouna-hotels-list-700842/`). Each page was opened and checked on 2026-10-16.
  - Redirects checked with a non-counting request, including the Russian page (`trip_sub1=er-ru-destination-hotels&locale=ru-RU`). `page-coverage.mjs`: 336 pages all ok.

### Plan
1. **Trip.com** as the hotel partner for all languages. The owner signs up, then sends the affiliate ID.
2. **ZH pages:**
   - Point to Trip.com (English or Traditional Chinese) and say so honestly.
   - Mainland China has no workable programme for us today.
3. **Yandex Travel:** revisit for the RU pages after Trip.com, if Egypt hotels and non-resident payouts are confirmed with Yandex support.

## 4. Partner product photos (2026-10-16)

The owner asked for each offer to show its own photo from the partner's product page. Copying photos from partner pages is not allowed:
- **GetYourGuide's Partner T&Cs:**
  - No scraping or extracting content from its website.
  - No copying, storing or caching it.
  - GYG Platform Content may be displayed only "as provided by GetYourGuide", that is, through its API or widgets.
- **Viator:** product photos reach affiliates through the Partner API.

**Viator API.** With the owner's approval, Claude accepted the API terms on 2026-10-16 (Tools → API Access → Start your development, six terms):
- Viator content may be used only to drive affiliate traffic to Viator.com, and only on our own domain.
- No bidding on the Viator brand in search ads.
- "Viator unique content" must not be indexed. Per the API docs this means reviews and the viatorUniqueContent element only; photos are not part of it.

The result:
- A **sandbox key** exists (Basic Access).
- The **production key** needs the owner's contact details first (Tools → API Access → "Go to contact details"). The owner fills them in.
- Keys are never read or recorded by Claude.

**GetYourGuide.** The partner portal has no self-serve API. Its Tools are Links and the Activity, City and Availability widgets. Third-party sources describe API access as a gated certification project. The official way to show GYG product photos is the Activity widget, which uses GYG's own design.

