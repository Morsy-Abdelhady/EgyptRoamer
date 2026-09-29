# Multilingual editorial content: review index (2026-09-29)

This covers the six languages after Arabic (see `docs/ARABIC-REVIEW-INDEX.md`): Deutsch, Français, Italiano, Español, Русский, 中文.

- Each language has 15 files in `content/editorial/<lang>/` (7 destinations, 8 experiences), all **`review: pending`**.
- Nothing has been imported and production has not been touched. No English file changed.
- Guides are not translated (owner decision 5).

## How to approve a file

The workflow is the same as for Arabic:
1. Read `content/editorial/<lang>/<file>` and edit if needed. Keep the `{#anchors}` and the English link paths.
2. Set `review: approved`, `reviewer:` and `reviewed:` in the front matter.
3. Run `python tools/editorial.py`, then commit.
4. The approval ships with the next Core deploy. Then, on the server:
   ```
   cd ~/html && wp egypt-roamer editorial --lang=<lang> --dry-run
   ```
   Run the real import only after that dry run and the owner's go.

`python tools/editorial.py status` lists every translation's review state and whether its English source changed.

## Editorial choices per language

| | Voice | Established site terms followed (from the existing seed titles, highlights and names) | UNESCO site names |
|---|---|---|---|
| de | *Sie* | Kairo, Gizeh, Assuan, Grand Egyptian Museum, Mosesberg (Berg Sinai) on first mention, Großes Sandmeer, Hatschepsut, Festung Shali | German UNESCO Commission list (unesco.de/orte/welterbe/welterbeliste): *Historisches Kairo*, *Memphis und seine Nekropole – die Pyramidenfelder von Giseh bis Dahschur*, *Antikes Theben mit seiner Nekropole*, *Nubische Denkmäler von Abu Simbel bis Philae*, *Katharinenkloster*. UNESCO itself publishes no German names |
| fr | *vous* | Le Caire, Gizeh, Louxor, Assouan, Charm el-Cheikh, Grand Musée égyptien, Abou Simbel, mont Sinaï, Grande mer de sable, Hatchepsout | whc.unesco.org/fr (official): *Le Caire historique*, *Memphis et sa nécropole – les zones des pyramides de Guizeh à Dahchour*, *Thèbes antique et sa nécropole*, *Monuments de Nubie d’Abou Simbel à Philae*, *Zone Sainte-Catherine* |
| it | *tu* | Il Cairo, Giza, Assuan, Alessandria, Grand Egyptian Museum, Monte Sinai, Grande Mare di Sabbia | **No official Italian names exist** (UNESCO publishes none; only unofficial sources were found). The sites are described in plain lowercase wording ("il Cairo storico", "Menfi con la sua necropoli…"), not presented as official titles |
| es | *tú* | El Cairo, Guiza, Asuán, Filé, Gran Museo Egipcio, Jan el-Jalili, Sharm el-Sheij, monte Sinaí, Gran Mar de Arena | whc.unesco.org/es (official): *El Cairo histórico*, *Menfis y su necrópolis – Zonas de las pirámides desde Guizeh hasta Dahshur*, *Antigua Tebas y su necrópolis*, *Monumentos de Nubia, desde Abu Simbel hasta Philae*, *Zona de Santa Catalina*. UNESCO's «Philae»/«Guizeh» appear only inside these quoted names; the running text keeps the site's Filé/Guiza |
| ru | *вы* | Каир, Гиза, Асуан, Шарм-эш-Шейх, Большой Египетский музей, Хан-эль-Халили, Кайт-бей, гора Моисея (Синай) on first mention, Великое песчаное море | whc.unesco.org/ru (official): «Исламский Каир», «Мемфис и его некрополи – район пирамид от Гизы до Дахшура», «Древние Фивы с их некрополями», «Памятники Нубии от Абу-Симбел до Филэ», «Монастырь Св. Екатерины с окрестностями». UNESCO's " - " separator is written as "–". Running text keeps the site's Филе |
| zh | neutral | 开罗, 吉萨, 阿斯旺, 沙姆沙伊赫, 大埃及博物馆, 汗·哈利利市场, 阿布辛贝, 西奈山, 大沙海, 盖贝依城堡, 穆罕默德角 | whc.unesco.org/zh (official): “开罗古城”, “孟菲斯及其墓地金字塔”, “底比斯古城及其墓地”, “阿布辛拜勒至菲莱的努比亚遗址”, “圣卡特琳娜地区”. Running text keeps the site's 阿布辛贝 |

## Items that deserve human review

None of these was changed. Each is the owner's call.

1. **Italian UNESCO names.** There are no official Italian forms, so they're described rather than quoted. If you want to use the Italian UNESCO Commission's wording, give me the source.
2. **Russian official name for Historic Cairo.** UNESCO's Russian name is «Исламский Каир» ("Islamic Cairo"), which is narrower than the English "Historic Cairo". It's quoted exactly as official, and the running text also calls the district «исламский Каир».
3. **Existing French title:** «Tour street food du Caire by night» mixes in English ("street food", "by night"). This is a style choice, not a grammar error, so it stays (decision 3).
4. **Unverified transliterations.** These are consistent within each language, but no official source was found for them:
   - Thistlegorm: de/fr/it/es *Thistlegorm*, ru «Тистлегорм», zh 蓟花号 (the name used in the Chinese diving community).
   - Also: Kershef / кершеф / 卡谢夫, Agabat, Fatnas, Aghurmi, Gharb Soheil and the Blue Hole in each language (ru Голубая дыра, zh 蓝洞).
5. **Chinese renderings chosen for readability:**
   - 焖蚕豆与塔梅亚 (ful medames and ta'meya);
   - 千层饼（费提尔） (feteer);
   - 坦努拉 (tanoura);
   - 阿赫瓦 (ahwa);
   - 乌斯特·巴拉德 (Wust el-Balad).
6. **Voice.** German *Sie* and French *vous* are formal. Italian *tu* and Spanish *tú* are the informal forms common in current travel writing. Change these if the brand prefers otherwise.
7. **Same as Arabic (decisions 11 and 12):**
   - The body-text travel-time phrases (Siwa "most of a day each way"; Abu Simbel by road "a very long day") are kept as in the English.
   - The alcohol, belly dance and bars mentions are kept as in the English.

## Automated checks (all six languages)

| Check | de | fr | it | es | ru | zh |
|---|---|---|---|---|---|---|
| 15 files, one per English destination and experience | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Compiler `check`: type, destination and H2 anchors match English; excerpt; review field | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Shape identical to English (H2, H3, list items, itinerary, FAQ, callouts, links, paragraphs) | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 |
| Internal links identical to English | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 |
| No number absent from English (zh: month digits in dates allowed, each occurrence listed) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| No English leakage (Latin languages: English function words; ru/zh: no Latin script except CAI, SPX and Roman numerals) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Terminology rules (banned variants, required official and site terms) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Fact strings carried from `CONTENT-SOURCES.md` | 28/28 | 28/28 | 28/28 | 28/28 | 28/28 | 28/28 |
| `review: pending`, LF line endings | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Local dry run: bodies/excerpts that would be written; links pointed at the language | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 |
| Local real run while pending | 0 writes | 0 writes | 0 writes | 0 writes | 0 writes | 0 writes |
| Local run with approval simulated in the compiled index only, then rerun | 15 → 0 | 15 → 0 | 15 → 0 | 15 → 0 | 15 → 0 | 15 → 0 |
| English posts before/after every import (md5 of title, slug, status, body, excerpt, all meta) | unchanged | unchanged | unchanged | unchanged | unchanged | unchanged |
| Render: 15 pages × 320/360/375/390/414/768/1024/1440 (`lang`, `dir=ltr`, no overflow, contents box after the intro on all 7 destinations, anchors resolve, links under `/<lang>/`) | 0 issues | 0 issues | 0 issues | 0 issues | 0 issues | 0 issues |
| axe-core at 390 and 1440 | 0 | 0 | 0 | 0 | 0 | 0 |

## Files

### Deutsch (`de`)

| File | Existing title (unchanged) | Size | H2 sections | Review |
|---|---|---|---|---|
| dest-alexandria.md | Alexandria | 801 words | 8 | pending |
| dest-aswan.md | Assuan | 860 words | 9 | pending |
| dest-cairo.md | Kairo | 1742 words | 14 | pending |
| dest-hurghada.md | Hurghada | 551 words | 7 | pending |
| dest-luxor.md | Luxor | 941 words | 10 | pending |
| dest-sharm.md | Sharm El-Sheikh | 696 words | 8 | pending |
| dest-siwa.md | Siwa | 694 words | 7 | pending |
| exp-abu.md | Tagesausflug nach Abu Simbel ab Assuan | 444 words | 7 | pending |
| exp-dinner.md | Dinner-Kreuzfahrt auf dem Nil mit Live-Show | 350 words | 6 | pending |
| exp-dive.md | Tauchen im Roten Meer: zwei Riff-Tauchgänge | 359 words | 5 | pending |
| exp-food.md | Streetfood-Tour durch Kairo bei Nacht | 347 words | 6 | pending |
| exp-giza.md | Private Tour: Pyramiden von Gizeh & Sphinx | 470 words | 7 | pending |
| exp-safari.md | Safari in die Weiße & Schwarze Wüste | 399 words | 7 | pending |
| exp-siwa.md | Großes Sandmeer im 4×4 & heiße Quellen | 350 words | 7 | pending |
| exp-valley.md | Tal der Könige & Hatschepsut-Tempel | 392 words | 6 | pending |

### Français (`fr`)

| File | Existing title (unchanged) | Size | H2 sections | Review |
|---|---|---|---|---|
| dest-alexandria.md | Alexandrie | 953 words | 8 | pending |
| dest-aswan.md | Assouan | 938 words | 9 | pending |
| dest-cairo.md | Le Caire | 1995 words | 14 | pending |
| dest-hurghada.md | Hurghada | 707 words | 7 | pending |
| dest-luxor.md | Louxor | 1109 words | 10 | pending |
| dest-sharm.md | Charm el-Cheikh | 821 words | 8 | pending |
| dest-siwa.md | Siwa | 808 words | 7 | pending |
| exp-abu.md | Excursion à Abou Simbel depuis Assouan | 492 words | 7 | pending |
| exp-dinner.md | Dîner-croisière sur le Nil avec spectacle | 399 words | 6 | pending |
| exp-dive.md | Plongée en mer Rouge : deux plongées sur récif | 416 words | 5 | pending |
| exp-food.md | Tour street food du Caire by night | 412 words | 6 | pending |
| exp-giza.md | Visite privée des pyramides de Gizeh et du Sphinx | 559 words | 7 | pending |
| exp-safari.md | Safari Désert blanc & Désert noir | 470 words | 7 | pending |
| exp-siwa.md | Grande mer de sable en 4×4 & sources chaudes | 408 words | 7 | pending |
| exp-valley.md | Vallée des Rois & temple d'Hatchepsout | 481 words | 6 | pending |

### Italiano (`it`)

| File | Existing title (unchanged) | Size | H2 sections | Review |
|---|---|---|---|---|
| dest-alexandria.md | Alessandria | 885 words | 8 | pending |
| dest-aswan.md | Assuan | 892 words | 9 | pending |
| dest-cairo.md | Il Cairo | 1845 words | 14 | pending |
| dest-hurghada.md | Hurghada | 654 words | 7 | pending |
| dest-luxor.md | Luxor | 1042 words | 10 | pending |
| dest-sharm.md | Sharm el-Sheikh | 764 words | 8 | pending |
| dest-siwa.md | Siwa | 742 words | 7 | pending |
| exp-abu.md | Gita di un giorno ad Abu Simbel da Assuan | 457 words | 7 | pending |
| exp-dinner.md | Crociera con cena sul Nilo e spettacolo dal vivo | 378 words | 6 | pending |
| exp-dive.md | Immersioni nel Mar Rosso: due immersioni in barriera | 383 words | 5 | pending |
| exp-food.md | Street food del Cairo di sera | 379 words | 6 | pending |
| exp-giza.md | Tour privato delle Piramidi di Giza e della Sfinge | 513 words | 7 | pending |
| exp-safari.md | Safari nel Deserto Bianco e Nero | 411 words | 7 | pending |
| exp-siwa.md | Grande Mare di Sabbia in 4×4 e sorgenti calde | 379 words | 7 | pending |
| exp-valley.md | Valle dei Re e Tempio di Hatshepsut | 451 words | 6 | pending |

### Español (`es`)

| File | Existing title (unchanged) | Size | H2 sections | Review |
|---|---|---|---|---|
| dest-alexandria.md | Alejandría | 952 words | 8 | pending |
| dest-aswan.md | Asuán | 981 words | 9 | pending |
| dest-cairo.md | El Cairo | 1989 words | 14 | pending |
| dest-hurghada.md | Hurghada | 689 words | 7 | pending |
| dest-luxor.md | Luxor | 1105 words | 10 | pending |
| dest-sharm.md | Sharm el-Sheij | 811 words | 8 | pending |
| dest-siwa.md | Siwa | 762 words | 7 | pending |
| exp-abu.md | Excursión de un día a Abu Simbel desde Asuán | 478 words | 7 | pending |
| exp-dinner.md | Crucero con cena por el Nilo y espectáculo en vivo | 386 words | 6 | pending |
| exp-dive.md | Buceo en el mar Rojo: dos inmersiones en arrecife | 416 words | 5 | pending |
| exp-food.md | Tour nocturno de comida callejera en El Cairo | 402 words | 6 | pending |
| exp-giza.md | Tour privado por las pirámides de Guiza y la Esfinge | 541 words | 7 | pending |
| exp-safari.md | Safari por el Desierto Blanco y Negro | 443 words | 7 | pending |
| exp-siwa.md | Gran Mar de Arena en 4×4 y fuentes termales | 384 words | 7 | pending |
| exp-valley.md | Valle de los Reyes y templo de Hatshepsut | 462 words | 6 | pending |

### Русский (`ru`)

| File | Existing title (unchanged) | Size | H2 sections | Review |
|---|---|---|---|---|
| dest-alexandria.md | Александрия | 701 words | 8 | pending |
| dest-aswan.md | Асуан | 749 words | 9 | pending |
| dest-cairo.md | Каир | 1573 words | 14 | pending |
| dest-hurghada.md | Хургада | 533 words | 7 | pending |
| dest-luxor.md | Луксор | 859 words | 10 | pending |
| dest-sharm.md | Шарм-эш-Шейх | 617 words | 8 | pending |
| dest-siwa.md | Сива | 615 words | 7 | pending |
| exp-abu.md | Поездка в Абу-Симбел из Асуана | 371 words | 7 | pending |
| exp-dinner.md | Круиз с ужином по Нилу и шоу | 295 words | 6 | pending |
| exp-dive.md | Дайвинг в Красном море: два погружения на рифах | 317 words | 5 | pending |
| exp-food.md | Ночной гастротур по Каиру | 325 words | 6 | pending |
| exp-giza.md | Индивидуальная экскурсия к пирамидам Гизы и Сфинксу | 405 words | 7 | pending |
| exp-safari.md | Сафари по Белой и Чёрной пустыне | 341 words | 7 | pending |
| exp-siwa.md | Великое песчаное море на 4×4 и горячие источники | 307 words | 7 | pending |
| exp-valley.md | Долина царей и храм Хатшепсут | 353 words | 6 | pending |

### 中文 (`zh`)

| File | Existing title (unchanged) | Size | H2 sections | Review |
|---|---|---|---|---|
| dest-alexandria.md | 亚历山大 | 1420 chars | 8 | pending |
| dest-aswan.md | 阿斯旺 | 1446 chars | 9 | pending |
| dest-cairo.md | 开罗 | 3017 chars | 14 | pending |
| dest-hurghada.md | 赫尔格达 | 1001 chars | 7 | pending |
| dest-luxor.md | 卢克索 | 1553 chars | 10 | pending |
| dest-sharm.md | 沙姆沙伊赫 | 1195 chars | 8 | pending |
| dest-siwa.md | 锡瓦 | 1114 chars | 7 | pending |
| exp-abu.md | 从阿斯旺出发的阿布辛贝一日游 | 692 chars | 7 | pending |
| exp-dinner.md | 尼罗河晚餐游船（含现场表演） | 564 chars | 6 | pending |
| exp-dive.md | 红海潜水：两次珊瑚礁潜水 | 614 chars | 5 | pending |
| exp-food.md | 开罗夜间街头美食之旅 | 596 chars | 6 | pending |
| exp-giza.md | 吉萨金字塔与狮身人面像私人游 | 793 chars | 7 | pending |
| exp-safari.md | 白沙漠与黑沙漠探险 | 625 chars | 7 | pending |
| exp-siwa.md | 大沙海四驱越野与温泉 | 569 chars | 7 | pending |
| exp-valley.md | 帝王谷与哈特谢普苏特神庙 | 710 chars | 6 | pending |
