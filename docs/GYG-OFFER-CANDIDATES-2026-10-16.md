# GetYourGuide offer candidates (2026-10-16)

**Owner decision (2026-10-16):** start the second partner with GetYourGuide. See `docs/PARTNER-OPTIONS-2026-10-16.md`.

**Status:** the candidates below are ready, waiting for the owner's partner account. Nothing is live.

**How each product was checked (2026-10-16):** the page opens on getyourguide.com with its own title and description.
- A URL that shows GetYourGuide's generic homepage (no product title) is treated as unavailable and is not used.
- No prices, ratings or review counts are copied, per the facts policy.

| Our experience | GetYourGuide product (title as shown) | URL | Alternative |
|---|---|---|---|
| `exp-abu`: Abu Simbel day trip from Aswan | From Aswan: Abu Simbel Day Tour with Private Guide and Car | https://www.getyourguide.com/aswan-l543/from-aswan-private-day-trip-to-abu-simbel-temple-with-guide-t447617/ | none checked live |
| `exp-giza`: Pyramids of Giza & Sphinx private tour | Cairo: Pyramids & Great Sphinx Private Tour with Camel Ride | https://www.getyourguide.com/cairo-l92/cairo-pyramids-great-sphinx-private-tour-with-camel-ride-t459032/ | none checked live |
| `exp-valley`: Valley of the Kings & Hatshepsut | West Bank Guided Tour: Valley of the Kings (Valley of the Kings, Colossi of Memnon, Hatshepsut Temple) | https://www.getyourguide.com/luxor-l109/guided-tour-to-west-bank-visit-valley-of-the-kings-t599849/ | Luxor: West Bank Kings and Queens All-Inclusive Tour & Lunch (private): …-t497266/ |
| `exp-alexandria`: Alexandria day trip from Cairo | From Cairo: Historical Alexandria Shared Day Tour with Lunch | https://www.getyourguide.com/cairo-l92/from-cairo-historical-alexandria-shared-day-tour-with-lunch-t922070/ | From Cairo: Alexandria Private Day Tour with Guide and Lunch: …-t532178/ |
| `exp-ras-mohammed`: Ras Mohammed snorkelling | From Sharm: White Island and Ras Mohamed Snorkeling Trip | https://www.getyourguide.com/sharm-el-sheikh-l408/from-sharm-white-island-and-ras-mohamed-snorkeling-trip-t556707/ | |
| `exp-dive`: Red Sea diving, two reef dives | Hurghada: Scuba Diving for Certified Divers or Beginners | https://www.getyourguide.com/hurghada-l403/hurghada-certified-diving-trip-with-lunch-transfers-gear-t1196989/ | |
| `exp-safari`: White & Black Desert safari | Cairo: 2-Day Bahariya & White Desert Private Camp Adventure | https://www.getyourguide.com/cairo-l92/cairo-2-day-bahariya-white-desert-private-camp-adventure-t444057/ | |
| `exp-siwa`: Great Sand Sea 4×4 & hot springs | 2 Nights 3 Days at Siwa Oases camp from Cairo (description mentions the Great Sand Sea) | https://www.getyourguide.com/cairo-l92/2-nights-3-days-at-siwa-oases-camp-from-cairo-t661189/ | Siwa Oasis 3-Day Private Sandboarding Safari from Cairo: …-t1495461/ |
| `exp-dinner`: Nile dinner cruise with live show | Cairo: Opal Nile Romantic Dinner Cruise & Live Entertainment | https://www.getyourguide.com/cairo-l92/cairo-opal-nile-romantic-dinner-cruise-live-entertainment-t1270141/ | |
| `exp-food`: Cairo street food tour by night | **none found.** The only Cairo candidate (…-t1036708) showed the generic page. | | |

Not used (generic page on 2026-10-16): t695430, t431588 (Abu Simbel); t454032, t515380, t213024 (Luxor); t411175, t847823 (Siwa); t281882 (Giza); t1036708 (Cairo food).

## When the owner's account is approved

1. **Provider in Core:** create "GetYourGuide" with allowed domain `getyourguide.com`.
2. **Link format:** confirm it in the partner portal's link tool (Tools → Links).
   - Third-party guides show `partner_id=<ID>` plus an optional `cmp=<campaign>`, but this is not yet confirmed from GetYourGuide's own documentation.
   - Planned offer parameters: `partner_id=<ID>` and `cmp=er-{lang}-{placement}`.
3. **Re-check each product page on the day,** then publish the offers in all 8 languages.
4. **Run `tools/qa/page-coverage.mjs`.**
5. **Terms reminder:** no GetYourGuide brand in paid search ads, and no paid traffic from third-party platforms (Partner T&Cs).
