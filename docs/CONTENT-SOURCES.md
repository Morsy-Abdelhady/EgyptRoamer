# Editorial content: sources and fact policy

Sources: `content/editorial/<lang>/*.md`. They are compiled by `tools/editorial.py` into `wordpress/wp-content/plugins/egypt-roamer-core/data/editorial/<lang>/`, and imported on the server with `wp egypt-roamer editorial [--lang=<code>] --dry-run`.

## Translations

- A translation follows the English file: same name, type, sections and anchors, and no facts the English doesn't have. It uses the same sources as this page.
- Each file records the English version it was made from (`source:`) and its review state (`review: pending | approved`).
- Only approved files that are up to date with the English are imported, and only into that language's posts.
- Translations are written as natural editorial text in the target language, not literal or blind machine translation. Arabic is Modern Standard Arabic.
- Current state: every language (ar, de, fr, it, es, ru, zh) has 15 files (destinations and experiences), all approved by the owner on 2026-09-29 (see `docs/MULTILINGUAL-FULL-REVIEW.md`); none imported into production yet. No translated guides yet. See `docs/ARABIC-REVIEW-INDEX.md` and `docs/MULTILINGUAL-REVIEW-INDEX.md`.

## Policy

- **Left out on purpose:** prices, opening hours, travel times, distances, visitor numbers, ratings, reviews, provider claims and "best" rankings. They go stale and can't be verified here. Pages point readers to check on the day instead.
- **Experiences** describe the experience itself and what to look for in an operator. Until a real provider or offer is attached, they never state what a particular tour includes.
- **Editorial opinion** is written as such ("most visitors…", "we'd give it three days"). It is never presented as fact.

## Verified facts and where they were checked (2026-09-28)

| Claim | Source |
|---|---|
| Egypt's 7 World Heritage properties: Historic Cairo; Memphis and its Necropolis – the Pyramid Fields from Giza to Dahshur; Ancient Thebes with its Necropolis; Nubian Monuments from Abu Simbel to Philae; Abu Mena; Saint Catherine Area; Wadi Al-Hitan | https://whc.unesco.org/en/statesparties/eg |
| Grand Egyptian Museum officially opened 1 Nov 2025; public opening 4 Nov 2025 with the full Tutankhamun collection shown together for the first time | Al Jazeera (4 Nov 2025) https://www.aljazeera.com/news/2025/11/4/a-look-inside-egypts-newly-unveiled-grand-egyptian-museum ; Forbes (2 Nov 2025) |
| National Museum of Egyptian Civilization in Fustat; Royal Mummies Hall with 22 royal mummies (incl. Ramesses II, Hatshepsut), moved from the Egyptian Museum on 3 April 2021 | https://egymonuments.gov.eg/news/opening-of-mummies-hall-at-the-nmec/ ; https://sis.gov.eg/en/egypt/tourism/cultural-tourism/museums/the-national-museum-of-egyptian-civilization-nmec/ |
| Alexander the Great consulted the Oracle of Amun at Siwa in 331 BC; temple at Aghurmi | https://www.historyskills.com/classroom/ancient-history/alexander-at-siwa/ ; https://sacredsites.com/africa/egypt/temple_of_amun_siwa_oasis.html |
| Abu Simbel sun alignment on or around 22 February and 22 October; temples relocated in the 1960s | https://www.memphistours.com/egypt/abu-simbel-sun-festival ; https://www.onthegotours.com/Egypt/Guides/Abu-Simbel-Sun-Festival-FAQs |
| Citadel of Qaitbay built 1477 on the site of the Pharos lighthouse, reusing its stone | https://en.wikipedia.org/wiki/Citadel_of_Qaitbay |
| Ras Mohammed declared Egypt's first national park in 1983 | https://iucngreenlist.org/sites/ras-mohammed-national-park/ ; https://en.wikipedia.org/wiki/Ras_Muhammad_National_Park |
| Abydos: the Temple of Seti I; Dendera: the temple built for the goddess Hathor (used in the `g-gems` guide) | https://egymonuments.gov.eg/en/news/completion-of-the-restoration-of-a-chapel-dedicated-to-the-god-amun-ra-in-the-temple-of-seti-i-at-abydos/ ; https://egymonuments.gov.eg/monuments/temple-of-dendera/ (Ministry of Tourism and Antiquities, checked 2026-09-29) |

## Arabic names and where they were checked (2026-09-29)

| Arabic name used | Source |
|---|---|
| «القاهرة التاريخية»; «ممفيس ومقبرتها – منطقة الأهرام من الجيزة إلى دهشور»; «مدينة طيبة القديمة ومقبرتها»; «معالم النوبة من أبو سمبل إلى فيلة»; «منطقة القديسة كاترين» (official UNESCO names, quoted exactly; UNESCO's page drops the dash in the Memphis name, which follows the English "–") | https://whc.unesco.org/ar/list/89 , /86 , /87 , /88 , /954 |
| متحف جاير أندرسون | https://egymonuments.gov.eg/ar/museums/gayer-anderson-museum (Ministry of Tourism and Antiquities) |
| جزيرة أجيلكيا (Philae moved there during the UNESCO Nubia campaign); إيزيس، حتحور | https://egymonuments.gov.eg/ar/archaeological-sites/philae |
| دير الأنبا سمعان | https://egymonuments.gov.eg/ar/monuments/monastery-of-st-simeon |
| أبو سمبل (invariant after prepositions, as in UNESCO's «من أبو سمبل»); نفرتاري، حتحور | https://egymonuments.gov.eg/ar/archaeological-sites/abu-simbel |
| المسلة الناقصة | https://egymonuments.gov.eg/ar/monuments/the-unfinished-obelisk |

UNESCO names in the other languages (checked 2026-09-29):
- **Official lists:** French, Spanish, Russian and Chinese names are quoted exactly from whc.unesco.org/fr|es|ru|zh/list/89, /86, /87, /88, /954.
- **German:** the German names are from the German UNESCO Commission list, unesco.de/orte/welterbe/welterbeliste.
- **Italian:** there are no official Italian names, so the Italian text describes the sites instead of quoting a title.
- Details are in `docs/MULTILINGUAL-REVIEW-INDEX.md`.

The Ministry sometimes spells the final letter with ه (فيله، كوم الدكه). This site uses the standard ة consistently (فيلة، كوم الدكة), as UNESCO does.

## General knowledge used without a separate citation

These are established and consistent across standard references. Re-check any that editors change:
- **Cairo:** Al-Azhar, Bab Zuweila, Al-Muizz Street, Citadel/Muhammad Ali, Sultan Hassan/Al-Rifa'i, Ibn Tulun, Gayer-Anderson; Coptic Cairo (Hanging Church, Ben Ezra, Babylon fortress, Mar Girgis metro); CAI and Sphinx (SPX) airports.
- **Alexandria:** Pompey's Pillar raised for Diocletian; Kom el-Shoqafa; Kom el-Dikka; Rosetta Stone found at Rashid.
- **Luxor:** the Karnak and Luxor temples; Valley of the Kings tickets cover a selection of tombs, with Tutankhamun, Seti I and Nefertari sold separately; Medinet Habu is the temple of Ramesses III.
- **Aswan:** Philae was moved to Agilkia; Qubbet el-Hawa; St Simeon; Kitchener's Island botanical garden; Kom Ombo is a double temple.
- **Siwa:** Shali is built of kershef and was damaged by rain in the 20th century; Siwi (Amazigh) language; route via Marsa Matruh.
- **Red Sea:** Giftun Islands protected area; SS Thistlegorm (WWII); Blue Hole deep-dive danger.
- **Desert:** White Desert Protected Area; Wadi Al-Hitan whale fossils with hind limbs.
- **Abydos and Dendera** (`g-gems`): Abydos's king list in the Temple of Seti I; Dendera's painted ceilings and rooftop chapels.
- **Food:** koshari, ful medames, ta'meya, feteer, hawawshi, molokhia, sayadeya.

## Not written (would need facts we don't have)

| Guide | What it needs |
|---|---|
| What a Trip to Egypt Really Costs in 2026 | real, dated price data |
| Is Egypt Safe for Tourists? An Honest 2026 Guide | current official travel advice from named governments, reviewed at publication |
| The Best Nile Cruises, Compared Cabin by Cabin | real boats, cabins and providers |
