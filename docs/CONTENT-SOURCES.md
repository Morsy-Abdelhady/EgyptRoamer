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
| Abu Simbel guide (`g-abu-simbel`), each fact below. Ramesses II (r. 1279–1213 BC). Temples carved in a sandstone cliff on the west bank. Four seated statues about 20 m high, with figures of Nefertari, his mother and children at their feet. Greek mercenaries' graffiti, 6th c. BC. Great Temple dedicated to Amun-Ra and Ra-Horakhty, three halls about 56 m into the cliff, Osiride statues, Battle of Kadesh scenes. Sun reaches the sanctuary about 22 Feb and 22 Oct. Small Temple dedicated to Nefertari for the worship of Hathor, statues about 10.5 m. Rescue 1963–1968, funded by more than 50 countries, rebuilt about 60 m higher | Britannica, "Abu Simbel" https://www.britannica.com/place/Abu-Simbel (checked 2026-10-15) |
| (Distance not used on the site, per the policy above: removed 15 Oct.) Abu Simbel 280 km south of Aswan; temples of Abu Simbel reassembled in situ but 60 m above their original site; Amada and Wadi es-Sebua temples reassembled near their former sites (on Lake Nasser) | UNESCO, "The Rescue of Nubian Monuments and Sites" https://whc.unesco.org/en/activities/173/ (checked 2026-10-15) |
| Nubian Monuments from Abu Simbel to Philae: inscribed 1979, ten component parts, UNESCO International Campaign 1960–1980 | https://whc.unesco.org/en/list/88/ (checked 2026-10-15) |
| Edfu: temple of Horus, begun under Ptolemy III in 237 BC, completed in 57 BC; its plan along one main axis is the classic example of an Egyptian temple (`g-nile-cruise`) | Britannica, "Idfū" https://www.britannica.com/place/Idfu (checked 2026-10-15) |
| Kom Ombo: double temple of the Ptolemaic and Roman periods, dedicated to Sobek (crocodile god) and Horus (falcon-headed god) (`g-nile-cruise`) | Britannica, "Kawm Umbū" https://www.britannica.com/place/Kawm-Umbu (checked 2026-10-15) |
| Saqqara: Step Pyramid of Djoser, designed by Imhotep as a pyramid in six stages; the Memphis area (Saqqara, Abusir, Dahshur, Abu Rawash, Giza) designated a UNESCO World Heritage site in 1979 (`g-day-trips-cairo`) | Britannica, "Ṣaqqārah" https://www.britannica.com/place/Saqqarah (checked 2026-10-15) |
| Wadi Al-Hitan: inscribed 2005; fossils of the earliest whales (Archaeoceti), the youngest in the last stages of losing their hind limbs (`g-day-trips-cairo`) | https://whc.unesco.org/en/list/1186/ (checked 2026-10-15) |
| Siwa: near the Libyan frontier; about 200 springs; old walled settlements of Siwa and Aghurmi on rock outcrops; dates and olives; oracle temple of Amun consulted by Alexander, ruins at Aghurmi (`g-desert-choice`) | Britannica, "Siwa Oasis" https://www.britannica.com/place/Siwa-Oasis (checked 2026-10-15) |
| Valley of the Kings: burial site of almost all kings of the 18th–20th dynasties (c. 1539–1075 BC), in the hills behind Deir el-Bahari; 62 known tombs; corridor, chambers and burial chamber; part of the Ancient Thebes World Heritage site (1979); Tutankhamun's tomb alone escaped pillage, treasures exhumed from 1922 (`g-valley-kings`) | Britannica, "Valley of the Kings" https://www.britannica.com/place/Valley-of-the-Kings (checked 2026-10-15) |
| Philae described by UNESCO as the final bastion of ancient Egyptian religion; the Aswan quarries and unfinished obelisk as the basis of knowledge of quarrying; Qubbet el-Hawa tombs of officials in charge of Nubian affairs; the Monastery of St Simeon one of the biggest in Egypt; Kalabsha, Beit el-Wali and Kiosk of Qertassi reassembled near the High Dam; Dakka, Maharraqa and Wadi es-Sebua, and Amada and Derr, rebuilt near their former sites (`g-aswan-temples`) | https://whc.unesco.org/en/list/88/ ; https://whc.unesco.org/en/activities/173/ (checked 2026-10-15) |
| Grand Egyptian Museum: called the world's largest archaeological museum; collection from Predynastic to Greco-Roman times, gathered from institutions across Egypt incl. the Egyptian Museum; designed by Heneghan Peng (Dublin); triangle-patterned facade and sloping ceilings echoing the pyramids; translucent alabaster entrance facade, shaded atrium with a colossal statue of Ramesses II over 3,200 years old, grand staircase of statues of gods and royals, tall windows facing the pyramids (`g-gem`) | Britannica, "Grand Egyptian Museum" https://www.britannica.com/topic/Grand-Egyptian-Museum (checked 2026-10-15); opening dates as in the GEM row above |
| Giza: three 4th-dynasty pyramids for Khufu, Khafre and Menkaure; the Great Pyramid, oldest and largest, completed early 25th century BC; counted among the Seven Wonders, the only one still standing; entrance on the north face; descending passage to an unfinished subterranean chamber; ascending passage to the Queen's Chamber and the corbel-vaulted Grand Gallery (about 46 m); King's Chamber lined with granite, holding a sarcophagus; five compartments above it, likely to relieve pressure; robbed in antiquity (`g-inside-pyramids`) | Britannica, "Pyramids of Giza" https://www.britannica.com/topic/Pyramids-of-Giza and "Great Pyramid of Giza" https://www.britannica.com/topic/Great-Pyramid-of-Giza (checked 2026-10-15) |
| Great Sphinx dated by most scholars to the reign of Khafre (`g-inside-pyramids`) | Britannica, "Great Sphinx" https://www.britannica.com/topic/Great-Sphinx (checked 2026-10-15) |
| Entry inside a pyramid sold as a separate ticket from the plateau ticket (`g-inside-pyramids`, worded as "usually") | Viator listing 41437P6, ticket inclusions (checked 2026-10-15) |
| Karnak: one of the largest temple complexes; great temple of Amun-Ra with the precincts of Mut and Montu; built over many centuries without a single plan; Great Hypostyle Hall between the 2nd and 3rd pylons, decorated under Seti I and Ramesses II, 12 central columns about 24 m, 134 columns in all; outer-wall reliefs of Seti I's victories and Ramesses II at Kadesh (`g-east-bank`) | Britannica, "Karnak" https://www.britannica.com/place/Karnak (checked 2026-10-15) |
| Luxor Temple: dedicated to Amun, Mut and Khonsu; commissioned by Amenhotep III, completed under Tutankhamun and Horemheb, extended by Ramesses II (court, pylon, obelisks); colonnade of 14 columns about 16 m; avenue of sphinxes to Karnak (`g-east-bank`) | Britannica, "Luxor" https://www.britannica.com/place/Luxor (checked 2026-10-15) |
| Great Sand Sea: an erg in the Western Desert reaching from western Egypt into eastern Libya, mostly covered by dunes; Siwa on its eastern side (`g-great-sand-sea`) | https://en.wikipedia.org/wiki/Great_Sand_Sea (checked 2026-10-15) |
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
- **Nile boats:** dahabiyas (traditional sailing boats, far fewer passengers than cruise ships); felucca (lateen-sailed boat).
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
