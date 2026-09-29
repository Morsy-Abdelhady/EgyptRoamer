# Arabic editorial content: review index (2026-09-29, updated after review pass 1)

The owner is the final reviewer (decision 1). All 15 files are **`review: pending`**. Nothing is approved and nothing has been imported.

## How to approve a file

1. Read `content/editorial/ar/<file>` and edit the text if needed. Keep the `{#anchors}` and the link paths (`/destinations/…/`) as they are.
2. In the front matter, set:
   ```
   review: approved
   reviewer: <name>
   reviewed: <YYYY-MM-DD>
   ```
3. Run `python tools/editorial.py`, then commit. The approval ships with the next Core deploy.
4. Only approved files whose English source (`source:`) is unchanged are imported. `python tools/editorial.py status` shows each file's state.

## Files

- **Titles** are the existing Arabic post titles on production. They are left unchanged (decision 3).
- **Words** counts the body only.
- **Shape** compares each file with its English source: H2/H3 headings, list items, itinerary days, FAQs, callouts, internal links and paragraphs.
- **Arabic runs 75–85% of the English word count.** Arabic packs articles, prepositions and pronouns into single words, so this is expected. The shape check confirms no section or item was dropped.

| File | English source (sha) | Arabic title | Words AR / EN | Sections (H2) | Shape | Review |
|---|---|---|---|---|---|---|
| dest-cairo.md | dest-cairo.md (d29034d09df9) | القاهرة | 1479 / 1850 | 14: لماذا تزورها · الجيزة: الأهرامات والمتحف المصري الكبير · ما بعد الجيزة: سقارة ومنف ودهشور · القاهرة الإسلامية · القاهرة القبطية والفسطاط · وسط البلد والتحرير والمتحف المصري · النيل · الطعام والحياة المحلية · أين تقيم · كم يومًا تحتاج · برنامج مقترح: ثلاثة أيام · الوصول والتنقّل · متى تزورها · أسئلة شائعة | identical | pending |
| dest-luxor.md | dest-luxor.md (48dfac96eb26) | الأقصر | 782 / 1054 | 10: الضفّتان: دليل مختصر · البر الشرقي: الكرنك ومعبد الأقصر · البر الغربي: الوديان والمعابد · المناطيد عند الشروق · رحلات النيل من الأقصر · أين تقيم · كم يومًا تحتاج · برنامج مقترح: ثلاثة أيام · متى تزورها وكيف تصل · أسئلة شائعة | identical | pending |
| dest-aswan.md | dest-aswan.md (899175404b9a) | أسوان | 679 / 898 | 9: لماذا تزورها · على الماء · فيلة والآثار · القرى النوبية · أبو سمبل · بين أسوان والأقصر · كم تبقى ومتى تزورها · كيف تصل · أسئلة شائعة | identical | pending |
| dest-alexandria.md | dest-alexandria.md (9e6b07589923) | الإسكندرية | 663 / 879 | 8: لماذا تزورها · طبقات من التاريخ: ماذا ترى · الكورنيش وحياة المدينة · الطعام: البحر على المائدة · رحلات اليوم الواحد · كم تبقى ومتى تزورها · الوصول والتنقّل · أسئلة شائعة | identical | pending |
| dest-siwa.md | dest-siwa.md (2dd0c1475bbb) | سيوة | 564 / 748 | 7: هل سيوة مناسبة لك؟ · ماذا ترى · بحر الرمال العظيم · الحياة المحلية والعادات · كم تبقى ومتى تزورها · الوصول والتنقّل · أسئلة شائعة | identical | pending |
| dest-hurghada.md | dest-hurghada.md (58c12100abd1) | الغردقة | 523 / 632 | 7: لماذا تزورها · تحت الماء · أين تقيم: الساحل منطقةً منطقة · ما وراء الشاطئ · كم تبقى ومتى تزورها · كيف تصل · أسئلة شائعة | identical | pending |
| dest-sharm.md | dest-sharm.md (7b794a12f4bf) | شرم الشيخ | 569 / 748 | 8: لماذا تزورها · البحر · جبل موسى وسانت كاترين · خليج نعمة وحياة المدينة · دهب وما بعدها · كم تبقى ومتى تزورها · كيف تصل · أسئلة شائعة | identical | pending |
| exp-giza.md | exp-giza.md (3ee5ad6375d6) | جولة خاصة إلى أهرامات الجيزة وأبي الهول (corrected title, see §Review pass 1) | 394 / 532 | 7: ما هي هذه التجربة · لماذا يصنع المرشد فرقًا هنا · ماذا تفعل فعلًا · لمن تناسب · قبل أن تذهب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-dinner.md | exp-dinner.md (5149373e0b50) | رحلة عشاء نيلية مع عرض حي | 286 / 372 | 6: ما هي هذه التجربة · كيف تبدو الأمسية · لمن تناسب · اختيار المركب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-abu.md | exp-abu.md (74ad81ad490c) | رحلة يوم إلى أبو سمبل من أسوان | 362 / 463 | 7: ما هي هذه التجربة · لماذا تستحق العناء · الوصول: برًّا أم جوًّا · ظاهرة تعامد الشمس · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-dive.md | exp-dive.md (427d7ee0dd08) | غوص في البحر الأحمر: غطستان بين الشعاب | 327 / 390 | 5: ما هي هذه التجربة · كيف يبدو يوم الغوص · المبتدئون والغوّاصون الحاصلون على شهادات · أين تغوص · أسئلة شائعة | identical | pending |
| exp-safari.md | exp-safari.md (82c1e5ba7f71) | سفاري الصحراء البيضاء والسوداء | 323 / 428 | 7: ما هي هذه التجربة · ماذا ترى · ليلة في الصحراء · لمن تناسب · كم تستغرق ومتى · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-food.md | exp-food.md (bd0a2d2d49a4) | جولة طعام الشارع في القاهرة ليلًا | 315 / 380 | 6: ما هي هذه التجربة · ماذا ستتذوّق · لماذا تذهب مع مرشد · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-valley.md | exp-valley.md (eadd9c3d01a0) | وادي الملوك ومعبد حتشبسوت | 341 / 454 | 6: ما هي هذه التجربة · ماذا تفعل فعلًا · لماذا يصنع المرشد فرقًا · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-siwa.md | exp-siwa.md (18a508992ce2) | بحر الرمال العظيم بسيارات الدفع الرباعي والينابيع الساخنة | 301 / 390 | 7: ما هي هذه التجربة · كيف يبدو اليوم · اختيار المنظّم · ما تحمله معك · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |

## Review pass 1 (2026-09-29): owner decisions applied

All 15 files were reviewed. Only the corrections the owner approved were made. Every file is still `review: pending`, nothing was imported, production was not touched, and no English file changed.

### Corrections made

| # | Decision | Change | Files |
|---|---|---|---|
| 3 | One Great Sand Sea terminology, as in the existing title | «بحر الرمال الأعظم» → **«بحر الرمال العظيم»** (8×). Springs: «العيون / عيون» → **«الينابيع / ينابيع»** (excerpts, headings, body, list label, link text). «عين كليوباترا» is kept: it is the spring's proper name | dest-siwa, exp-siwa |
| 4 | Title grammar | `seed.json` Arabic title of exp-giza → **«جولة خاصة إلى أهرامات الجيزة وأبي الهول»**. The slug is unchanged. The body was already correct: «يتّجه أبو الهول» is the nominative subject, and the other cases use «أبي / أبا الهول» | Core `data/seed.json` |
| 5 | UNESCO official Arabic names (verified on whc.unesco.org/ar) | «منف وجبّانتها: حقول الأهرامات…» → **«ممفيس ومقبرتها – منطقة الأهرام من الجيزة إلى دهشور»**; «طيبة القديمة وجبّانتها» → **«مدينة طيبة القديمة ومقبرتها»**; «آثار النوبة…» → **«معالم النوبة من أبو سمبل إلى فيلة»**; «منطقة سانت كاترين» → **«منطقة القديسة كاترين»** (the quoted site name only). «القاهرة التاريخية» was already correct | dest-cairo, exp-giza, dest-luxor, exp-valley, dest-aswan, exp-abu, dest-sharm |
| 6 | Transliterations (verified on the Ministry of Tourism and Antiquities site) | «جاير آندرسون» → **«جاير أندرسون»**; «أجيليكا» → **«أجيلكيا»**. «دير الأنبا سمعان» and «المسلّة الناقصة» were already correct | dest-cairo, dest-aswan |
| 7 | Mount Sinai first-mention approach | «جبل موسى (جبل سيناء)» moved to the **first** mention: the excerpt and the first body mention in dest-sharm (it was only in a later section), and the only mention in dest-hurghada | dest-sharm, dest-hurghada |
| 8 | Consistent editorial terms | belle époque → **«الحقبة الجميلة»** in both places (was «الزمن الجميل» once); Abu Simbel sun event → **«ظاهرة تعامد الشمس»** everywhere (the Aswan excerpt said «مهرجان الشمس»); ahwa → **«قهوة» شعبية**, which was already identical in both files | dest-cairo, dest-aswan |

### Terminology decisions

| Term | Used form | Why |
|---|---|---|
| Great Sand Sea / springs | بحر الرمال العظيم / الينابيع | Owner decision 3. Matches the existing exp-siwa title and the site's existing Arabic data (the Siwa highlight «بحر الرمال العظيم» and the description «ينابيع ساخنة») |
| Cleopatra's Spring | عين كليوباترا | Proper name, not the generic word for springs |
| UNESCO sites | the official names above, in «…» | UNESCO Arabic list |
| Memphis (the place, outside the UNESCO name) | منف | Standard Egyptian usage. The UNESCO quote keeps «ممفيس» |
| St Catherine's (the monastery and area, outside the UNESCO name) | دير سانت كاترين / منطقة سانت كاترين | Standard Egyptian usage. The UNESCO quote keeps «القديسة كاترين» |
| Abu Simbel after prepositions | أبو سمبل (invariant) | UNESCO's official «من أبو سمبل» and the Ministry both treat it as a fixed name. The existing title «رحلة يوم إلى أبو سمبل» therefore stays |
| Mount Sinai | جبل موسى (جبل سيناء) at first mention, then جبل موسى | Owner decision 7 |
| ta marbuta | فيلة، كوم الدكة | Standard MSA, as UNESCO uses. The Ministry sometimes writes فيله، كوم الدكه |
| Months | أبريل، أكتوبر، نوفمبر، فبراير | Owner decision 9 |

### Facts verified

- 33 checks pass: every fact from `docs/CONTENT-SOURCES.md` that appears in these files is present in the source table, in the English file, and identically in the Arabic.
  - GEM (November 2025; full Tutankhamun collection).
  - NMEC: Fustat; 22 royal mummies including Ramesses II and Hatshepsut; April 2021.
  - Siwa: Alexander in 331 BC; Aghurmi; Shali kershef and 20th-century rains.
  - Abu Simbel: 22 February / 22 October; relocated in the 1960s.
  - Qaitbay: 15th century; Pharos site; reused stone.
  - Ras Mohammed: 1983, first national park.
  - Five UNESCO names.
  - Diocletian; Rosetta; Agilkia; Medinet Habu; tomb tickets; Thistlegorm; Blue Hole; White Desert Protected Area; Giftun; Kom Ombo.
- No Arabic file contains a number that isn't in its English source.
- **No discrepancy with the sources was found.**
- One note: the Royal Mummies "procession through the city" (English and Arabic) isn't spelled out in the source table, which cites the move date only. It's widely documented, but add a citation if you want every clause sourced.

### Unresolved items (owner)

1. **Production title change (exp-giza, Arabic post 202).**
   - The seed never overwrites existing titles and the importer never writes titles. The live title changes only through a separate, approved database edit: in wp-admin, or `cd ~/html && wp post update 202 --post_title='جولة خاصة إلى أهرامات الجيزة وأبي الهول'`.
   - The slug stays. Not done.
2. **Unverified transliterations.** These are kept; each is spelled one way everywhere. No official Arabic page was found for them:
   - ثيسلجورم, جزيرة كتشنر, غرب سهيل, البلو هول, العجبات, جزيرة فطناس, أغورمي, الكرشيف.
3. **Theme copy of the prototype locale.** `themes/egypt-roamer/src/js/locales/ar.js` still has the old exp-giza title. WordPress doesn't use it: `localizeData()` returns early for server data. It's a copy of the prototype, which is never edited. It was left alone to avoid a theme release for dead text.
4. **Travel-time phrases in body text** (Siwa "most of a day each way", Abu Simbel by road "a very long day") are unchanged (decision 11). They belong to the separate travel-time task.
5. **Sensitive references** (alcohol, belly dance, bars) are unchanged (decision 12). No editorial or legal reason to flag them was found.
6. **Approval:** all 15 files stay `review: pending` until the owner explicitly approves them.

### Tests passed (after the corrections)

| Test | Result |
|---|---|
| Compiler `check` (type, destination and H2 anchors match English; excerpt; review field) | pass, all languages |
| Structure vs English (H2, H3, list items, itinerary, FAQ, callouts, links, paragraphs) | identical in 15/15 |
| Internal links vs English | identical in 15/15 |
| Latin script / new numbers | none (only CAI, SPX) / none |
| Terminology consistency (13 banned variants, 12 required forms at exact counts) | 0 failures |
| Fact/source consistency | 33/33 |
| `review: pending` in all files; LF line endings | 15/15 |
| English sources and compiled English | 0 diff lines |
| Local import (approval simulated in the compiled index only, then restored) | bodies 15, excerpts 15, 35 links pointed at Arabic pages; rerun: 0 writes |
| English posts before/after import | md5 identical |
| Render QA, 15 Arabic pages at 320/360/375/390/414/768/1024/1440 | see the next section |
