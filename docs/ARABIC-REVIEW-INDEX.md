# Arabic editorial content: review index (2026-09-29)

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
| dest-cairo.md | dest-cairo.md (d29034d09df9) | القاهرة | 1478 / 1850 | 14: لماذا تزورها · الجيزة: الأهرامات والمتحف المصري الكبير · ما بعد الجيزة: سقارة ومنف ودهشور · القاهرة الإسلامية · القاهرة القبطية والفسطاط · وسط البلد والتحرير والمتحف المصري · النيل · الطعام والحياة المحلية · أين تقيم · كم يومًا تحتاج · برنامج مقترح: ثلاثة أيام · الوصول والتنقّل · متى تزورها · أسئلة شائعة | identical | pending |
| dest-luxor.md | dest-luxor.md (48dfac96eb26) | الأقصر | 781 / 1054 | 10: الضفّتان: دليل مختصر · البر الشرقي: الكرنك ومعبد الأقصر · البر الغربي: الوديان والمعابد · المناطيد عند الشروق · رحلات النيل من الأقصر · أين تقيم · كم يومًا تحتاج · برنامج مقترح: ثلاثة أيام · متى تزورها وكيف تصل · أسئلة شائعة | identical | pending |
| dest-aswan.md | dest-aswan.md (899175404b9a) | أسوان | 679 / 898 | 9: لماذا تزورها · على الماء · فيلة والآثار · القرى النوبية · أبو سمبل · بين أسوان والأقصر · كم تبقى ومتى تزورها · كيف تصل · أسئلة شائعة | identical | pending |
| dest-alexandria.md | dest-alexandria.md (9e6b07589923) | الإسكندرية | 663 / 879 | 8: لماذا تزورها · طبقات من التاريخ: ماذا ترى · الكورنيش وحياة المدينة · الطعام: البحر على المائدة · رحلات اليوم الواحد · كم تبقى ومتى تزورها · الوصول والتنقّل · أسئلة شائعة | identical | pending |
| dest-siwa.md | dest-siwa.md (2dd0c1475bbb) | سيوة | 564 / 748 | 7: هل سيوة مناسبة لك؟ · ماذا ترى · بحر الرمال الأعظم · الحياة المحلية والعادات · كم تبقى ومتى تزورها · الوصول والتنقّل · أسئلة شائعة | identical | pending |
| dest-hurghada.md | dest-hurghada.md (58c12100abd1) | الغردقة | 521 / 632 | 7: لماذا تزورها · تحت الماء · أين تقيم: الساحل منطقةً منطقة · ما وراء الشاطئ · كم تبقى ومتى تزورها · كيف تصل · أسئلة شائعة | identical | pending |
| dest-sharm.md | dest-sharm.md (7b794a12f4bf) | شرم الشيخ | 569 / 748 | 8: لماذا تزورها · البحر · جبل موسى وسانت كاترين · خليج نعمة وحياة المدينة · دهب وما بعدها · كم تبقى ومتى تزورها · كيف تصل · أسئلة شائعة | identical | pending |
| exp-giza.md | exp-giza.md (3ee5ad6375d6) | جولة خاصة إلى أهرامات الجيزة وأبو الهول | 393 / 532 | 7: ما هي هذه التجربة · لماذا يصنع المرشد فرقًا هنا · ماذا تفعل فعلًا · لمن تناسب · قبل أن تذهب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-dinner.md | exp-dinner.md (5149373e0b50) | رحلة عشاء نيلية مع عرض حي | 286 / 372 | 6: ما هي هذه التجربة · كيف تبدو الأمسية · لمن تناسب · اختيار المركب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-abu.md | exp-abu.md (74ad81ad490c) | رحلة يوم إلى أبو سمبل من أسوان | 362 / 463 | 7: ما هي هذه التجربة · لماذا تستحق العناء · الوصول: برًّا أم جوًّا · ظاهرة تعامد الشمس · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-dive.md | exp-dive.md (427d7ee0dd08) | غوص في البحر الأحمر: غطستان بين الشعاب | 327 / 390 | 5: ما هي هذه التجربة · كيف يبدو يوم الغوص · المبتدئون والغوّاصون الحاصلون على شهادات · أين تغوص · أسئلة شائعة | identical | pending |
| exp-safari.md | exp-safari.md (82c1e5ba7f71) | سفاري الصحراء البيضاء والسوداء | 323 / 428 | 7: ما هي هذه التجربة · ماذا ترى · ليلة في الصحراء · لمن تناسب · كم تستغرق ومتى · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-food.md | exp-food.md (bd0a2d2d49a4) | جولة طعام الشارع في القاهرة ليلًا | 315 / 380 | 6: ما هي هذه التجربة · ماذا ستتذوّق · لماذا تذهب مع مرشد · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-valley.md | exp-valley.md (eadd9c3d01a0) | وادي الملوك ومعبد حتشبسوت | 340 / 454 | 6: ما هي هذه التجربة · ماذا تفعل فعلًا · لماذا يصنع المرشد فرقًا · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |
| exp-siwa.md | exp-siwa.md (18a508992ce2) | بحر الرمال العظيم بسيارات الدفع الرباعي والينابيع الساخنة | 301 / 390 | 7: ما هي هذه التجربة · كيف يبدو اليوم · اختيار المنظّم · ما تحمله معك · لمن تناسب · اجمعها مع · أسئلة شائعة | identical | pending |

## Items that deserve human review

These are the owner's calls. None of them was changed.

### Terminology

1. **Great Sand Sea** (`dest-siwa`, `exp-siwa`)
   - The existing title of `exp-siwa` says **بحر الرمال العظيم** and **الينابيع الساخنة**.
   - The bodies say **بحر الرمال الأعظم** and **العيون**. Both are in common use; «الأعظم» and «العيون» are the usual names in Egypt and Siwa.
   - Titles are frozen (decision 3), so choose one: align the bodies with the title, or document a title change.
2. **Title grammar** (`exp-giza`): «إلى أهرامات الجيزة وأبو الهول» would grammatically be «وأبي الهول» after «إلى». The body uses «أبي الهول». This is in the frozen title and needs your decision.
3. **UNESCO names** are rendered in Arabic, not quoted from UNESCO's official Arabic list. Please check them against it:
   - «القاهرة التاريخية»;
   - «منف وجبّانتها: حقول الأهرامات من الجيزة إلى دهشور»;
   - «طيبة القديمة وجبّانتها»;
   - «آثار النوبة من أبو سمبل إلى فيلة»;
   - «منطقة سانت كاترين».
4. **Transliterated names**, to check against your preferred spellings:
   - ثيسلجورم (Thistlegorm)
   - جاير آندرسون (Gayer-Anderson)
   - جزيرة كتشنر (Kitchener's Island)
   - أجيليكا (Agilkia)
   - غرب سهيل (Gharb Soheil)
   - البلو هول (Blue Hole)
   - العجبات (Agabat)
   - جزيرة فطناس (Fatnas)
   - أغورمي (Aghurmi)
   - الكرشيف (kershef)
   - دير الأنبا سمعان (St Simeon)
5. **Mount Sinai** (`dest-sharm`, `dest-hurghada`): rendered as «جبل موسى (جبل سيناء)» on first mention, then «جبل موسى».
6. **Loose renderings**, not literal:
   - "belle-époque" appears as «الزمن الجميل» (Cairo "Why go") and «الحقبة الجميلة» (Downtown);
   - *ahwa* appears as «قهوة» شعبية;
   - "Abu Simbel sun festival" appears as «ظاهرة تعامد الشمس».
7. **Month names** use the Egyptian forms (أكتوبر، أبريل، نوفمبر) rather than the Levantine ones (تشرين، نيسان).

### Facts to confirm (carried from the English; none are new)

Each has a source in `docs/CONTENT-SOURCES.md`. The Arabic repeats them exactly:
- Grand Egyptian Museum fully opened in November 2025.
- NMEC Royal Mummies Hall: 22 royal mummies, moved in April 2021.
- Oracle of Amun: Alexander the Great in 331 BC.
- Abu Simbel sun alignment: around 22 February and 22 October; relocated in the 1960s.
- Qaitbay: 15th century, on the Pharos site.
- Ras Mohammed: first national park, 1983.

### Content the owner may want to weigh

- **Travel-time phrases** (vague, as in the English):
  - Siwa: «تستغرق معظم اليوم في كل اتجاه» ("most of a day each way");
  - Abu Simbel by road: «يوم طويل جدًا».
  - These are in the English bodies too. The key-facts travel-time cleanup (decision 4) is a separate task and doesn't cover body text.
- **Sensitive items, same as the English:** `exp-dinner` mentions alcohol («لا تُقدَّم المشروبات الكحولية على كل المراكب») and belly dance; `dest-sharm` mentions bars in Naama Bay.

## Automated checks already passed

- Structure matches the English in all 15 files. The compiler also enforces it: same type, destination and H2 anchors.
- No Latin words except the airport codes CAI/SPX.
- No number that isn't in the English.
- Every internal link matches the English.
- Local rendering at 320–1440 px:
  - fully RTL, with no sideways page scrolling;
  - the contents box sits after the intro, before the first section;
  - all anchors resolve;
  - no axe violations.
