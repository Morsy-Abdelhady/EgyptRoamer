# Multilingual editorial content: review index, all 7 languages (2026-09-29)

**Scope:** ar, de, fr, it, es, ru and zh, with 15 files each (7 destinations, 8 experiences), in `content/editorial/<lang>/`.

**State (2026-09-29): 105 files, all `review: approved`** (reviewer Morsy Abdelhady, 2026-09-29). 77 were approved as written; 28 were approved after the 44 corrections listed in `docs/MULTILINGUAL-FULL-REVIEW.md`. All are up to date with English, and no English file changed. **Nothing is imported into production** and the branch is not merged or pushed. Guides are not translated (decision 5).

The Arabic decisions from 2026-09-29 are already applied: MSA; titles and slugs kept; «بحر الرمال العظيم / الينابيع»; «وأبي الهول»; official UNESCO names; the Mount Sinai first-mention approach; Egyptian month names; facts, travel-time phrases and sensitive references unchanged. They're not re-asked here. The Arabic detail stays in `docs/ARABIC-REVIEW-INDEX.md`.

## How to approve

1. Read the file. Edit it if needed, but keep the `{#anchors}` and the English link paths.
2. Set `review: approved`, `reviewer: <name>` and `reviewed: <YYYY-MM-DD>`.
3. Run `python tools/editorial.py` and commit.
4. The approval ships with the next Core deploy. On the server, run `cd ~/html && wp egypt-roamer editorial --lang=<code> --dry-run` first, and the real import only on your go.

You can approve file by file; an approved file imports even if the others are still pending. `python tools/editorial.py status` shows every file's state.

## Editorial choices per language

| | Voice | Site terms followed (from the existing titles, names and highlights) | UNESCO names |
|---|---|---|---|
| ar | formal MSA | القاهرة، الأقصر، أسوان، خان الخليلي، جبل موسى (جبل سيناء)، بحر الرمال العظيم | official, whc.unesco.org/ar |
| de | *Sie* | Kairo, Gizeh, Assuan, Grand Egyptian Museum, Mosesberg (Berg Sinai), Großes Sandmeer, Hatschepsut | German UNESCO Commission list (UNESCO has no German list) |
| fr | *vous* | Le Caire, Gizeh, Louxor, Assouan, Charm el-Cheikh, Grand Musée égyptien, Abou Simbel, mont Sinaï | official, whc.unesco.org/fr |
| it | *tu* | Il Cairo, Giza, Assuan, Alessandria, Grand Egyptian Museum, Monte Sinai, Grande Mare di Sabbia | none official exist; the sites are described, not quoted as titles |
| es | *tú* | El Cairo, Guiza, Asuán, Filé, Gran Museo Egipcio, Jan el-Jalili, Sharm el-Sheij, monte Sinaí | official, whc.unesco.org/es |
| ru | *вы* | Каир, Гиза, Асуан, Шарм-эш-Шейх, Большой Египетский музей, Хан-эль-Халили, Кайт-бей, гора Моисея (Синай) | official, whc.unesco.org/ru |
| zh | neutral | 开罗, 吉萨, 阿斯旺, 沙姆沙伊赫, 大埃及博物馆, 汗·哈利利市场, 阿布辛贝, 西奈山, 大沙海 | official, whc.unesco.org/zh |

## Items that genuinely need a human decision

Only items that can't be settled from a source are listed. Nothing was changed for them.

**1. Brand voice (one decision for all languages)**
- German *Sie* and French *vous* are formal; Italian *tu* and Spanish *tú* are informal.
- The informal forms are common in current travel writing. Keep them, or make them all formal?

**2. Names with no official source (confirm or correct the spelling)**

| Name | ar | ru | zh | de/fr/it/es |
|---|---|---|---|---|
| Thistlegorm | ثيسلجورم | «Тистлегорм» | 蓟花号 | Thistlegorm |
| kershef | الكرشيف | кершеф | 卡谢夫 | kershef/Kershef |
| Agabat | العجبات | Агабат | 阿加巴特 | Agabat |
| Fatnas (Fantasy Island) | فطناس | Фатнас (Фэнтези-Айленд) | 法特纳斯岛（幻想岛） | Fatnas |
| Aghurmi | أغورمي | Агурми | 阿古尔米 | Aghurmi |
| Gharb Soheil | غرب سهيل | Гарб-Сохейль | 西苏海勒村 | Gharb Soheil |
| Kitchener's Island | جزيرة كتشنر | остров Китченера | 基奇纳岛 | Kitchener-Insel / île Kitchener / isola di Kitchener / isla Kitchener |
| Blue Hole | البلو هول | Голубая дыра | 蓝洞 | Blue Hole |

**3. Official-name nuances (keep as is, or override)**
- **Russian:** UNESCO's official name for Historic Cairo is «Исламский Каир» (literally "Islamic Cairo"), which is narrower than the English. It's quoted exactly.
- **Italian:** there are no official names, so the sites are described. Supply the Italian UNESCO Commission's wording if you want it quoted.
- **German:** the names come from the German UNESCO Commission, not UNESCO itself.

**4. Existing titles with a style (not grammar) question.** These stay unchanged under decision 3 unless you say otherwise.
- fr «Tour street food du Caire by night» mixes in English.
- The Arabic «رحلة يوم إلى أبو سمبل من أسوان» is correct: Abu Simbel is treated as a fixed name, as UNESCO does.

**5. Chinese renderings chosen for readability (confirm)**
- 焖蚕豆与塔梅亚 (ful and ta'meya)
- 千层饼（费提尔） (feteer)
- 坦努拉 (tanoura)
- 阿赫瓦 (ahwa)
- 乌斯特·巴拉德 (Wust el-Balad)
- 美好年代 (belle époque)

**6. One source gap, not a translation issue.** The English `g-gems` guide names Abydos and Dendera, which `docs/CONTENT-SOURCES.md` doesn't list. It isn't translated yet.

## Automated checks (re-run 2026-09-29, all 7 languages)

| Check | ar | de | fr | it | es | ru | zh |
|---|---|---|---|---|---|---|---|
| 15 files; type, destination and H2 anchors match English (`editorial.py check`) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Shape identical to English (headings, lists, itinerary, FAQ, callouts, links, paragraphs) | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 |
| Internal links identical to English | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 |
| No number absent from English (zh: month digits in dates) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| No English leakage | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Terminology rules (banned variants 0, required terms present) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Sourced fact strings present | 15/15 | 28/28 | 28/28 | 28/28 | 28/28 | 28/28 | 28/28 |
| `review: pending` at check time (all approved since 2026-09-29), up to date with English (`current`), LF | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 | 15/15 |
| Local dry run: bodies/excerpts, links pointed at the language | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 | 15/15, 35 |
| Local real run while pending | 0 writes | 0 writes | 0 writes | 0 writes | 0 writes | 0 writes | 0 writes |
| English posts before/after import (md5 of all fields and meta) | unchanged | unchanged | unchanged | unchanged | unchanged | unchanged | unchanged |
| Render: 15 pages × 8 widths (320–1440), `lang`/`dir`, overflow, contents box, anchors, links | 0 issues | 0 issues | 0 issues | 0 issues | 0 issues | 0 issues | 0 issues |
| axe-core (390, 1440) | 0 | 0 | 0 | 0 | 0 | 0 | 0 |

## Files (105)

### العربية (`ar`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | الإسكندرية | 662 words | 8 | approved | yes |
| dest-aswan.md | أسوان | 678 words | 9 | approved | yes |
| dest-cairo.md | القاهرة | 1478 words | 14 | approved | yes |
| dest-hurghada.md | الغردقة | 522 words | 7 | approved | yes |
| dest-luxor.md | الأقصر | 781 words | 10 | approved | yes |
| dest-sharm.md | شرم الشيخ | 568 words | 8 | approved | yes |
| dest-siwa.md | سيوة | 563 words | 7 | approved | yes |
| exp-abu.md | رحلة يوم إلى أبو سمبل من أسوان | 362 words | 7 | approved | yes |
| exp-dinner.md | رحلة عشاء نيلية مع عرض حي | 286 words | 6 | approved | yes |
| exp-dive.md | غوص في البحر الأحمر: غطستان بين الشعاب | 327 words | 5 | approved | yes |
| exp-food.md | جولة طعام الشارع في القاهرة ليلًا | 315 words | 6 | approved | yes |
| exp-giza.md | جولة خاصة إلى أهرامات الجيزة وأبي الهول | 394 words | 7 | approved | yes |
| exp-safari.md | سفاري الصحراء البيضاء والسوداء | 323 words | 7 | approved | yes |
| exp-siwa.md | بحر الرمال العظيم بسيارات الدفع الرباعي والينابيع الساخنة | 301 words | 7 | approved | yes |
| exp-valley.md | وادي الملوك ومعبد حتشبسوت | 341 words | 6 | approved | yes |

### Deutsch (`de`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | Alexandria | 801 words | 8 | approved | yes |
| dest-aswan.md | Assuan | 860 words | 9 | approved | yes |
| dest-cairo.md | Kairo | 1742 words | 14 | approved | yes |
| dest-hurghada.md | Hurghada | 551 words | 7 | approved | yes |
| dest-luxor.md | Luxor | 941 words | 10 | approved | yes |
| dest-sharm.md | Sharm El-Sheikh | 696 words | 8 | approved | yes |
| dest-siwa.md | Siwa | 694 words | 7 | approved | yes |
| exp-abu.md | Tagesausflug nach Abu Simbel ab Assuan | 444 words | 7 | approved | yes |
| exp-dinner.md | Dinner-Kreuzfahrt auf dem Nil mit Live-Show | 350 words | 6 | approved | yes |
| exp-dive.md | Tauchen im Roten Meer: zwei Riff-Tauchgänge | 359 words | 5 | approved | yes |
| exp-food.md | Streetfood-Tour durch Kairo bei Nacht | 347 words | 6 | approved | yes |
| exp-giza.md | Private Tour: Pyramiden von Gizeh & Sphinx | 470 words | 7 | approved | yes |
| exp-safari.md | Safari in die Weiße & Schwarze Wüste | 399 words | 7 | approved | yes |
| exp-siwa.md | Großes Sandmeer im 4×4 & heiße Quellen | 350 words | 7 | approved | yes |
| exp-valley.md | Tal der Könige & Hatschepsut-Tempel | 392 words | 6 | approved | yes |

### Français (`fr`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | Alexandrie | 953 words | 8 | approved | yes |
| dest-aswan.md | Assouan | 938 words | 9 | approved | yes |
| dest-cairo.md | Le Caire | 1995 words | 14 | approved | yes |
| dest-hurghada.md | Hurghada | 707 words | 7 | approved | yes |
| dest-luxor.md | Louxor | 1109 words | 10 | approved | yes |
| dest-sharm.md | Charm el-Cheikh | 821 words | 8 | approved | yes |
| dest-siwa.md | Siwa | 808 words | 7 | approved | yes |
| exp-abu.md | Excursion à Abou Simbel depuis Assouan | 492 words | 7 | approved | yes |
| exp-dinner.md | Dîner-croisière sur le Nil avec spectacle | 399 words | 6 | approved | yes |
| exp-dive.md | Plongée en mer Rouge : deux plongées sur récif | 416 words | 5 | approved | yes |
| exp-food.md | Tour street food du Caire by night | 412 words | 6 | approved | yes |
| exp-giza.md | Visite privée des pyramides de Gizeh et du Sphinx | 559 words | 7 | approved | yes |
| exp-safari.md | Safari Désert blanc & Désert noir | 470 words | 7 | approved | yes |
| exp-siwa.md | Grande mer de sable en 4×4 & sources chaudes | 408 words | 7 | approved | yes |
| exp-valley.md | Vallée des Rois & temple d'Hatchepsout | 481 words | 6 | approved | yes |

### Italiano (`it`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | Alessandria | 885 words | 8 | approved | yes |
| dest-aswan.md | Assuan | 892 words | 9 | approved | yes |
| dest-cairo.md | Il Cairo | 1845 words | 14 | approved | yes |
| dest-hurghada.md | Hurghada | 654 words | 7 | approved | yes |
| dest-luxor.md | Luxor | 1042 words | 10 | approved | yes |
| dest-sharm.md | Sharm el-Sheikh | 764 words | 8 | approved | yes |
| dest-siwa.md | Siwa | 742 words | 7 | approved | yes |
| exp-abu.md | Gita di un giorno ad Abu Simbel da Assuan | 457 words | 7 | approved | yes |
| exp-dinner.md | Crociera con cena sul Nilo e spettacolo dal vivo | 378 words | 6 | approved | yes |
| exp-dive.md | Immersioni nel Mar Rosso: due immersioni in barriera | 383 words | 5 | approved | yes |
| exp-food.md | Street food del Cairo di sera | 379 words | 6 | approved | yes |
| exp-giza.md | Tour privato delle Piramidi di Giza e della Sfinge | 513 words | 7 | approved | yes |
| exp-safari.md | Safari nel Deserto Bianco e Nero | 411 words | 7 | approved | yes |
| exp-siwa.md | Grande Mare di Sabbia in 4×4 e sorgenti calde | 379 words | 7 | approved | yes |
| exp-valley.md | Valle dei Re e Tempio di Hatshepsut | 451 words | 6 | approved | yes |

### Español (`es`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | Alejandría | 952 words | 8 | approved | yes |
| dest-aswan.md | Asuán | 981 words | 9 | approved | yes |
| dest-cairo.md | El Cairo | 1989 words | 14 | approved | yes |
| dest-hurghada.md | Hurghada | 689 words | 7 | approved | yes |
| dest-luxor.md | Luxor | 1105 words | 10 | approved | yes |
| dest-sharm.md | Sharm el-Sheij | 811 words | 8 | approved | yes |
| dest-siwa.md | Siwa | 762 words | 7 | approved | yes |
| exp-abu.md | Excursión de un día a Abu Simbel desde Asuán | 478 words | 7 | approved | yes |
| exp-dinner.md | Crucero con cena por el Nilo y espectáculo en vivo | 386 words | 6 | approved | yes |
| exp-dive.md | Buceo en el mar Rojo: dos inmersiones en arrecife | 416 words | 5 | approved | yes |
| exp-food.md | Tour nocturno de comida callejera en El Cairo | 402 words | 6 | approved | yes |
| exp-giza.md | Tour privado por las pirámides de Guiza y la Esfinge | 541 words | 7 | approved | yes |
| exp-safari.md | Safari por el Desierto Blanco y Negro | 443 words | 7 | approved | yes |
| exp-siwa.md | Gran Mar de Arena en 4×4 y fuentes termales | 384 words | 7 | approved | yes |
| exp-valley.md | Valle de los Reyes y templo de Hatshepsut | 462 words | 6 | approved | yes |

### Русский (`ru`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | Александрия | 701 words | 8 | approved | yes |
| dest-aswan.md | Асуан | 749 words | 9 | approved | yes |
| dest-cairo.md | Каир | 1573 words | 14 | approved | yes |
| dest-hurghada.md | Хургада | 533 words | 7 | approved | yes |
| dest-luxor.md | Луксор | 859 words | 10 | approved | yes |
| dest-sharm.md | Шарм-эш-Шейх | 617 words | 8 | approved | yes |
| dest-siwa.md | Сива | 615 words | 7 | approved | yes |
| exp-abu.md | Поездка в Абу-Симбел из Асуана | 371 words | 7 | approved | yes |
| exp-dinner.md | Круиз с ужином по Нилу и шоу | 295 words | 6 | approved | yes |
| exp-dive.md | Дайвинг в Красном море: два погружения на рифах | 317 words | 5 | approved | yes |
| exp-food.md | Ночной гастротур по Каиру | 325 words | 6 | approved | yes |
| exp-giza.md | Индивидуальная экскурсия к пирамидам Гизы и Сфинксу | 405 words | 7 | approved | yes |
| exp-safari.md | Сафари по Белой и Чёрной пустыне | 341 words | 7 | approved | yes |
| exp-siwa.md | Великое песчаное море на 4×4 и горячие источники | 307 words | 7 | approved | yes |
| exp-valley.md | Долина царей и храм Хатшепсут | 353 words | 6 | approved | yes |

### 中文 (`zh`)

| File | Existing title (unchanged) | Size | H2 | Review | Up to date with English |
|---|---|---|---|---|---|
| dest-alexandria.md | 亚历山大 | 1420 chars | 8 | approved | yes |
| dest-aswan.md | 阿斯旺 | 1446 chars | 9 | approved | yes |
| dest-cairo.md | 开罗 | 3017 chars | 14 | approved | yes |
| dest-hurghada.md | 赫尔格达 | 1001 chars | 7 | approved | yes |
| dest-luxor.md | 卢克索 | 1553 chars | 10 | approved | yes |
| dest-sharm.md | 沙姆沙伊赫 | 1195 chars | 8 | approved | yes |
| dest-siwa.md | 锡瓦 | 1114 chars | 7 | approved | yes |
| exp-abu.md | 从阿斯旺出发的阿布辛贝一日游 | 692 chars | 7 | approved | yes |
| exp-dinner.md | 尼罗河晚餐游船（含现场表演） | 564 chars | 6 | approved | yes |
| exp-dive.md | 红海潜水：两次珊瑚礁潜水 | 614 chars | 5 | approved | yes |
| exp-food.md | 开罗夜间街头美食之旅 | 596 chars | 6 | approved | yes |
| exp-giza.md | 吉萨金字塔与狮身人面像私人游 | 793 chars | 7 | approved | yes |
| exp-safari.md | 白沙漠与黑沙漠探险 | 625 chars | 7 | approved | yes |
| exp-siwa.md | 大沙海四驱越野与温泉 | 569 chars | 7 | approved | yes |
| exp-valley.md | 帝王谷与哈特谢普苏特神庙 | 710 chars | 6 | approved | yes |
