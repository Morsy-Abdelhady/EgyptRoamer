# AI search query dataset and visibility baseline (2026-10-03)

This dataset is the companion to `EXPERIENCE-TEMPLATE-GEO-AUDIT-2026-10-03.md` (§9, §12). It has 110 real-world Egypt travel questions across 11 intents and 8 languages, each mapped to the Egypt Roamer page that answers it today.

**Method**
- Queries are written the way travellers ask, per language. They are not translations of the English set: each language uses its own common phrasing and spelling of place names. Non-English phrasing should get a native check before it is used for paid tracking.
- There is no search-volume data (no keyword tool access). Relevance is an editorial judgement.
- Coverage is read from the live pages' sections (production crawl, 2026-10-03):
  - **Page:** a dedicated page answers it.
  - **Section:** a section of a page answers it.
  - **None:** a gap.
- Non-English guides do not exist yet (the guides are English-only), so planning questions outside English are gaps.

## English (70)

| # | Intent | Query | Best page today | Coverage |
|---|---|---|---|---|
| 1 | destination | What are the best things to do in Cairo? | /destinations/cairo/ | Page |
| 2 | destination | What are the best things to do in Luxor? | /destinations/luxor/ | Page |
| 3 | destination | Is Aswan worth visiting? | /destinations/aswan/ ("Why go") | Page |
| 4 | destination | What is there to do in Alexandria? | /destinations/alexandria/ | Page |
| 5 | destination | What are the best experiences in Siwa Oasis? | /destinations/siwa/ + Great Sand Sea 4×4 | Page |
| 6 | destination | How do I get to Siwa Oasis? | /destinations/siwa/ ("Getting there and around") | Section |
| 7 | destination | What is the difference between the East Bank and West Bank in Luxor? | /destinations/luxor/ ("The two banks, explained") | Section |
| 8 | destination | What is there to see in Giza besides the pyramids? | /destinations/cairo/ (Giza, GEM, Saqqara sections) | Section |
| 9 | destination | Should I visit Saqqara and Dahshur? | /destinations/cairo/ ("Beyond Giza") + hidden gems | Section |
| 10 | destination | What is Islamic Cairo? | /guides/cairo-travel-guide/ + /destinations/cairo/ | Section |
| 11 | destination | What is Dahab like? | /destinations/sharm/ ("Dahab and beyond") | Section |
| 12 | destination | Can you climb Mount Sinai at sunrise? | /destinations/sharm/ ("Mount Sinai and St Catherine") | Section |
| 13 | destination | Where is the Great Sand Sea? | /experiences/great-sand-sea-4-4-hot-springs/ | Page |
| 14 | destination | Is the White Desert worth it? | /experiences/white-desert-black-desert-safari/ | Page |
| 15 | itinerary | How many days do I need in Egypt? | /guides/7-days-in-egypt-itinerary/ | Section |
| 16 | itinerary | Can I visit Egypt in 7 days? | /guides/7-days-in-egypt-itinerary/ | Page |
| 17 | itinerary | What is a good 10-day Egypt itinerary? | none | None |
| 18 | itinerary | What is a good 2-week Egypt itinerary? | none | None |
| 19 | itinerary | How many days do I need in Luxor? | /destinations/luxor/ ("How many days you need") | Section |
| 20 | itinerary | How many days should I spend in Cairo? | /destinations/cairo/ | Section |
| 21 | itinerary | Should I fly or take the train from Cairo to Luxor? | /guides/7-days-in-egypt-itinerary/ | Section |
| 22 | itinerary | Can I combine the Nile Valley and the Red Sea in one trip? | /guides/7-days-in-egypt-itinerary/ ("Variations") | Section |
| 23 | comparison | Luxor or Aswan: which is better? | Luxor + Aswan destinations (no direct comparison) | Section |
| 24 | comparison | Hurghada or Sharm El Sheikh? | Hurghada + Sharm destinations (no direct comparison) | Section |
| 25 | comparison | Nile cruise or hotels in Luxor and Aswan? | /destinations/luxor/ ("Nile cruises from Luxor") | Section |
| 26 | comparison | Abu Simbel by road or by plane? | /experiences/abu-simbel-day-trip-from-aswan/ | Page |
| 27 | comparison | White Desert or Great Sand Sea? | both experiences ("Alternatives to compare") | Page |
| 28 | comparison | Grand Egyptian Museum or the Egyptian Museum in Tahrir? | /destinations/cairo/ (both sections) | Section |
| 29 | comparison | Hurghada or Marsa Alam for diving? | none (Marsa Alam not covered) | None |
| 30 | planning | What is the best time to visit Egypt? | /guides/best-time-to-visit-egypt/ | Page |
| 31 | planning | When is it too hot to visit Egypt? | /guides/best-time-to-visit-egypt/ | Section |
| 32 | planning | What is Egypt like during Ramadan? | /guides/best-time-to-visit-egypt/ ("Ramadan and holidays") | Section |
| 33 | planning | Is Egypt worth visiting for the first time? | /guides/7-days-in-egypt-itinerary/ | Section |
| 34 | planning | Do I need a visa for Egypt? | none | None |
| 35 | planning | Is Egypt safe for tourists? | none | None |
| 36 | planning | What currency should I bring to Egypt? | none | None |
| 37 | planning | How do I get around Egypt? | none (fragments in the itinerary) | None |
| 38 | planning | How much should I tip in Egypt? | none | None |
| 39 | planning | What should I pack for Egypt? | Abu Simbel "What to know" (one trip only) | Section |
| 40 | planning | Do I need a guide to visit Egyptian sites? | Giza + Valley experiences ("Why a guide…") | Section |
| 41 | experience | How do I visit the Valley of the Kings? | /experiences/valley-of-the-kings-hatshepsut-temple/ | Page |
| 42 | experience | What should I do in Abu Simbel? | /experiences/abu-simbel-day-trip-from-aswan/ | Page |
| 43 | experience | When is the Abu Simbel sun festival? | /experiences/abu-simbel-day-trip-from-aswan/ | Section |
| 44 | experience | Is a private Giza pyramids tour worth it? | /experiences/pyramids-of-giza-sphinx-private-tour/ | Page |
| 45 | experience | Where can I go snorkeling in Egypt? | /destinations/hurghada/ ("Under the water"), /destinations/sharm/ ("The sea") | Section |
| 46 | experience | Can beginners dive in the Red Sea? | /experiences/red-sea-diving-two-reef-dives/ | Page |
| 47 | experience | Is a Nile dinner cruise in Cairo worth it? | /experiences/nile-dinner-cruise-with-live-show/ | Page |
| 48 | experience | Is a hot-air balloon ride in Luxor worth it? | /destinations/luxor/ ("Sunrise balloons") | Section |
| 49 | experience | What is a felucca ride in Aswan? | /destinations/aswan/ ("On the water") | Section |
| 50 | experience | How do I visit Philae temple? | /destinations/aswan/ ("Philae and the monuments") | Section |
| 51 | experience | What is a Luxor to Aswan Nile cruise like? | none | None |
| 52 | experience | Can you climb Mount Sinai with a guide? | /destinations/sharm/ (section only) | Section |
| 53 | cultural | What should I wear in Egypt? | none | None |
| 54 | cultural | Can I visit a Nubian village in Aswan? | /destinations/aswan/ ("Nubian villages") | Section |
| 55 | cultural | What local customs should I respect in Siwa? | /destinations/siwa/ ("Local life and customs") | Section |
| 56 | cultural | What is Coptic Cairo? | /destinations/cairo/ + Cairo guide | Section |
| 57 | cultural | What is a tanoura show? | /experiences/nile-dinner-cruise-with-live-show/ | Section |
| 58 | cultural | What are Egypt's lesser-known ancient sites? | /guides/hidden-gems-in-egypt/ | Page |
| 59 | food | What food should I try in Egypt? | /experiences/cairo-street-food-tour-by-night/ ("What you'll taste") | Section |
| 60 | food | What is koshari? | /experiences/cairo-street-food-tour-by-night/ | Section |
| 61 | food | Is a Cairo street food tour worth it? | /experiences/cairo-street-food-tour-by-night/ | Page |
| 62 | food | Where can I eat seafood in Alexandria? | /destinations/alexandria/ ("Food: the sea on the table") | Section |
| 63 | family | Is Egypt good for a family holiday with kids? | none | None |
| 64 | family | Which Egypt experiences suit children? | experiences' "Who it suits" (scattered) | Section |
| 65 | family | Is the Red Sea good for families? | none | None |
| 66 | couples | Is Egypt good for a honeymoon? | none | None |
| 67 | couples | What are romantic things to do in Egypt? | none (dinner cruise, felucca scattered) | None |
| 68 | couples | Where should a couple stay on the Red Sea? | /destinations/hurghada/ ("Where to stay") | Section |
| 69 | adventure | Can you sleep in the desert in Egypt? | /experiences/white-desert-black-desert-safari/ ("A night in the desert") | Page |
| 70 | adventure | Can you go sandboarding in Egypt? | /experiences/great-sand-sea-4-4-hot-springs/ | Page |

## Other languages (40)

The URLs below are the language's own translation of the page named. The guides exist only in English.

| # | Lang | Intent | Query | Best page today | Coverage |
|---|---|---|---|---|---|
| 71 | ar | destination | ما هي أفضل الأماكن السياحية في الأقصر؟ | Luxor (ar) | Page |
| 72 | ar | itinerary | كم يوما أحتاج لزيارة مصر؟ | none in Arabic | None |
| 73 | ar | planning | ما هو أفضل وقت لزيارة مصر؟ | none in Arabic | None |
| 74 | ar | experience | رحلة أبو سمبل من أسوان | Abu Simbel (ar) | Page |
| 75 | ar | experience | أين أمارس الغوص في الغردقة؟ | Red Sea diving (ar) + Hurghada (ar) | Page |
| 76 | ar | adventure | رحلة الصحراء البيضاء من الواحات البحرية | White Desert safari (ar) | Page |
| 77 | ar | experience | عشاء في رحلة نيلية بالقاهرة | Nile dinner cruise (ar) | Page |
| 78 | de | planning | Beste Reisezeit Ägypten | none in German | None |
| 79 | de | itinerary | Ägypten Rundreise 7 Tage | none in German | None |
| 80 | de | experience | Tal der Könige besichtigen Tipps | Valley (de) | Page |
| 81 | de | experience | Abu Simbel Tagesausflug ab Assuan | Abu Simbel (de) | Page |
| 82 | de | comparison | Hurghada oder Sharm El Sheikh | Hurghada (de) + Sharm (de) | Section |
| 83 | de | destination | Oase Siwa Sehenswürdigkeiten | Siwa (de) | Page |
| 84 | fr | destination | Que faire à Louxor ? | Luxor (fr) | Page |
| 85 | fr | planning | Quand partir en Égypte ? | none in French | None |
| 86 | fr | experience | Croisière sur le Nil Louxor Assouan | none | None |
| 87 | fr | experience | Visite des pyramides de Gizeh avec guide | Giza private tour (fr) | Page |
| 88 | fr | experience | Plongée à Hurghada pour débutant | Red Sea diving (fr) | Page |
| 89 | fr | adventure | Excursion dans le Désert Blanc | White Desert safari (fr) | Page |
| 90 | it | destination | Cosa vedere al Cairo | Cairo (it) | Page |
| 91 | it | itinerary | Quanti giorni servono per visitare l'Egitto? | none in Italian | None |
| 92 | it | experience | Abu Simbel da Assuan in giornata | Abu Simbel (it) | Page |
| 93 | it | destination | Cosa fare a Sharm el Sheikh | Sharm (it) | Page |
| 94 | it | destination | Come arrivare all'oasi di Siwa | Siwa (it) | Section |
| 95 | es | destination | Qué ver en Asuán | Aswan (es) | Page |
| 96 | es | planning | Mejor época para viajar a Egipto | none in Spanish | None |
| 97 | es | experience | Cómo visitar el Valle de los Reyes | Valley (es) | Page |
| 98 | es | experience | Bucear en Hurghada | Red Sea diving (es) | Page |
| 99 | es | itinerary | Itinerario por Egipto en 7 días | none in Spanish | None |
| 100 | es | destination | Qué ver en Alejandría | Alexandria (es) | Page |
| 101 | ru | destination | Что посмотреть в Луксоре | Luxor (ru) | Page |
| 102 | ru | planning | Когда лучше ехать в Египет | none in Russian | None |
| 103 | ru | comparison | Хургада или Шарм-эль-Шейх | Hurghada (ru) + Sharm (ru) | Section |
| 104 | ru | experience | Экскурсия в Абу-Симбел из Асуана | Abu Simbel (ru) | Page |
| 105 | ru | adventure | Белая пустыня Египет экскурсия | White Desert safari (ru) | Page |
| 106 | zh | planning | 埃及旅游最佳时间 | none in Chinese | None |
| 107 | zh | destination | 卢克索必去景点 | Luxor (zh) | Page |
| 108 | zh | itinerary | 埃及七天行程 | none in Chinese | None |
| 109 | zh | food | 开罗夜间美食之旅 | Cairo street food (zh) | Page |
| 110 | zh | experience | 赫尔格达红海潜水 | Red Sea diving (zh) | Page |

## Coverage summary

| Set | Page | Section | None |
|---|---|---|---|
| English (70) | 20 | 36 | 14 |
| Other languages (40) | 25 | 3 | 12 |
| **All (110)** | **45** | **39** | **26** |

**Gaps, most valuable first**
1. **Planning essentials:** visa, safety, money, getting around, tipping, what to wear.
2. **Planning in every language but English:** best time and itineraries exist only in English.
3. **Missing pillars:**
   - Luxor–Aswan Nile cruise;
   - 10/14-day itineraries;
   - family;
   - couples.
4. **Comparisons answered only implicitly:** Luxor vs Aswan, Hurghada vs Sharm.

## Visibility benchmark (2026-10-03)

| Query set | Platform | Egypt Roamer mentioned | Cited | URL cited | Competitors cited | Notes |
|---|---|---|---|---|---|---|
| brand: `site:egyptroamer.com`, `"egyptroamer.com"` | Bing web | no | no | none | not applicable | no Egypt Roamer URL in results; not indexed yet |
| any | Google / AI Overviews | NOT TESTED | NOT TESTED | none | none | CAPTCHA, not bypassed |
| any | ChatGPT Search | NOT TESTED | NOT TESTED | none | none | needs the owner's account |
| any | Perplexity | NOT TESTED | NOT TESTED | none | none | needs the owner's account |
| any | Copilot | NOT TESTED | NOT TESTED | none | none | needs the owner's account |
| any | Bing Webmaster AI Performance | NOT AVAILABLE | none | none | none | site not verified in Bing Webmaster Tools |

The site went public on 2026-10-02. Re-run this table monthly from the owner's accounts, starting once Bing and Google show the site indexed. Record the query, platform, date, whether Egypt Roamer is mentioned and cited, the URL cited, the competitors cited and the context.
