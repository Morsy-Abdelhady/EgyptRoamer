# Translation review, 26 Oct 2026

The owner asked on 26 Oct 2026: "راجع الترجمات بشكل احترافى واعتمدها" (review the translations professionally and approve them).

**Who reviewed.** Claude, as an AI translation review. This is **not** a native-speaker review. The owner approved publication on 26 Oct 2026. Each approved file records this in its front matter:

```
review: approved
reviewer: Claude (AI translation review, not a native speaker); approved by the owner on 2026-10-26
reviewed: 2026-10-26
```

**Status.** Nothing is committed, released or deployed. There is no Core or Theme version bump. These files wait for the owner to read this log. They sit in the same working copy as the uncommitted 25 Oct changes (English sources, `guide-p4.php`, `redirect.php`, docs), which this review did not touch.

## Scope and result

| Group | Files | Approved | Not approved |
|---|---|---|---|
| Outdated after the 25 Oct English edits: `dest-siwa`, `g-great-sand-sea`, `g-snorkelling` × ar, de, es, fr, it, ru, zh | 21 | 21 | 0 |
| Pending review: `exp-balloon` (posts #960–966) and `exp-cruise` × 7 languages | 14 | 14 | 0 |
| **Total** | **35** | **35** | **0** |

`exp-cruise` was the only other `review: pending` file. Afterwards, `python tools/editorial.py status` lists all 336 translations as `approved current`. `PYTHONUTF8=1 python tools/editorial.py` compiled 48 items per language into Core `data/editorial/`.

## How each file was checked

- The translation was compared with the current English source, sentence by sentence.
- Checks:
  - meaning;
  - nothing added or dropped;
  - place and site names in their usual form in the language;
  - numbers and dates;
  - natural tone and a consistent form of address;
  - script;
  - Arabic punctuation (، ؟);
  - links pointing at the same internal paths as the English. The site maps these paths per language.
- No facts, prices, opening hours, travel times or ratings were added.
- For the 21 outdated files, the changed English paragraphs were translated again first. The `source:` fingerprint was then updated to the current English:
  - `dest-siwa`: a42d126b8e56;
  - `g-great-sand-sea`: c5778eb6a7e1;
  - `g-snorkelling`: 80b6ad39b256.
- The English diff since the last translation is only the 25 Oct working-copy change. The translations' old fingerprints matched the English at HEAD.

## Changes brought in from the 25 Oct English (all 7 languages)

- **dest-siwa:** the "Great Sand Sea" section was rewritten to match the new English:
  - Siwa as the base for excursions;
  - the Ministry of Tourism and Antiquities highlights sandboarding;
  - routes, stops and requirements vary, so confirm them with the operator;
  - new link to `/guides/great-sand-sea/`.
  - Removed claims the English no longer makes: "south of the oasis", "essential", hot and cold springs, sunset, "licensed operators handle permits".
- **g-great-sand-sea:** the whole body was rebuilt on the new English, keeping the same sections and anchors:
  - new excerpt and new introduction (Egyptian Tourism Authority);
  - "What it is", "Safari" and the two-item safety callout;
  - "What to bring" and "When to go" are now prose; the lists, the October–April season and the claims about extent and Libya are gone;
  - two new FAQ items (where excursions start; what activities). The White Desert FAQ is kept.
- **g-snorkelling:**
  - new introduction, with a link to `/guides/red-sea-famous-dive-sites/`;
  - Sharm paragraph: "well-known option", known for its coral reefs, conditions vary by site. Removed "Egypt's first national park", "coral walls close to the surface" and "stronger currents";
  - the "all year" FAQ answer no longer gives water-temperature seasons.

## Per-file log

### Arabic (ar)
- `dest-siwa`: Great Sand Sea section updated (above). Rest checked, no other fix.
- `g-great-sand-sea`: rebuilt (above). Title aligned with the English title: «بحر الرمال العظيم: محيط الكثبان في مصر». It was «أين يقع، وكيف تزوره من سيوة».
- `g-snorkelling`: three paragraphs updated (above). Rest checked, no fix.
- `exp-balloon`: "pick-up" changed from «الاستلام» (sounds like receiving goods) to «موعد الاصطحاب». Dates (13 March 2026, 1 June–30 August 2026, 2013) checked.
- `exp-cruise`: "extra" changed from «إضافة مدفوعة» (paid; the English doesn't say so) to «إضافة اختيارية», in two places. The Abu Simbel sentence was smoothed («على مسافة بعيدة منها»).

### German (de)
- `dest-siwa`: section updated. Also fixed:
  - "distinctive": «eigenwilligsten» → «unverwechselbarsten»;
  - "a commitment": «ist ein Entschluss» → «erfordert einigen Aufwand»;
  - "bring layers": «Schichten» → «mehrere Lagen Kleidung».
- `g-great-sand-sea`: rebuilt.
- `g-snorkelling`: updated. This also removes the old grammar error «ist der Höhepunkt der Nationalpark».
- `exp-balloon`: «Die Abholung ist» → «erfolgt».
- `exp-cruise`: "extras": «gegen Aufpreis» (adds a price notion) → «als Extra». «Folgen Sie den Wärtern» → «den Anweisungen der Wärter».

### Spanish (es)
- `dest-siwa`, `g-great-sand-sea`, `g-snorkelling`: updated or rebuilt; the rest is fine.
- `exp-balloon`: the page mixed *usted* with a *tú* heading, and the other es pages use *tú*. The whole page now uses *tú* (13 verb and pronoun forms).
- `exp-cruise`: no fix needed.

### French (fr)
- `dest-siwa`: section updated. «désert occidental» → «désert Occidental» (2×), as in the other fr pages.
- `g-great-sand-sea`, `g-snorkelling`: updated or rebuilt.
- `exp-balloon`: no fix needed.
- `exp-cruise`:
  - Edfu: dangling participle fixed («Commencé…, il présente un plan simple…»);
  - «Partir d'Assouan fait finir le voyage» → «En partant d'Assouan, on termine à Louxor».

### Italian (it)
- `dest-siwa`, `g-great-sand-sea`, `g-snorkelling`: updated or rebuilt; the rest is fine.
- `exp-balloon`: the page used *voi*, but the other it experience pages use *tu*; it now uses *tu*. Also fixed «Il prelievo» (means a blood draw or withdrawal) → «Ti vengono a prendere».
- `exp-cruise`: changed to *tu*. «guida egittologa» (implies a female guide) → «guida specializzata in egittologia».

### Russian (ru)
- `dest-siwa`: section updated. «Добраться сюда — это решение» → «Дорога сюда требует усилий… очарование Сивы».
- `g-great-sand-sea`: rebuilt.
- `g-snorkelling`: updated. Fixed the ungrammatical reef rule («не вставайте … кораллы») → «не трогайте кораллы и ракушки, не вставайте на них и не собирайте их».
- `exp-balloon`:
  - «Трансфер забирает» → «За вами заезжают»;
  - briefing: «о позе при посадке, и соблюдайте её».
- `exp-cruise`:
  - excerpt: «какие дни на борту» → «как проходят дни на борту»;
  - "extra": «доплата» → «предлагают дополнительно» / «дополнительная опция»;
  - case agreement fixed in "Combine it with";
  - «переходит» → «перемещается».

### Chinese (zh)
- `dest-siwa`, `g-great-sand-sea`: updated or rebuilt.
- `g-snorkelling`: updated. The mask sentence was made natural («一副贴合脸型的面镜会让体验大不一样»).
- `exp-balloon`: no fix needed.
- `exp-cruise`: «只需打开一次行李» → «行李只需打开一次».

## Left as they are (for the owner to decide)

- **Titles of the other files were not changed.** Changing a title makes guide-sync update the page. The only title changed is ar `g-great-sand-sea`, where the old title no longer matched the new content.
- **Already-approved files outside this scope still mix forms of address:**
  - es `g-luxor-balloon` and `g-cairo-museums` use *usted*;
  - it `g-luxor-balloon`, `g-cairo-museums` and `g-el-gouna` use *voi*.

  They were not part of this review.
- **Names of the Egyptian authorities:**
  - The Arabic pages keep «سلطة الطيران المدني المصرية», as in the approved ar `g-luxor-balloon`. The authority's own name is «سلطة الطيران المدني المصري». Change both together if wanted.
  - The Egyptian Tourism Authority is rendered as «الهيئة المصرية العامة للتنشيط السياحي». In de, fr, it and ru the English name follows in brackets.

## Before release

- These are content and data changes in Core `data/editorial/`. Releasing them needs a Core version bump, a commit and a push, after the 25 Oct release gate in `docs/HANDOFF-2026-10-25.md`.
- On the server, `wp egypt-roamer editorial` fills only empty or untouched seed text. Live pages that editors changed keep their text.
- Another session may still change the English sources after a Ministry of Tourism and EEAA review. If it does, `tools/editorial.py status` will show these files as OUTDATED again, and they need the same pass.

## Addendum, 26 Oct 2026 (afternoon): official-source review of three guides

The English of `g-inside-pyramids`, `g-sinai` and `g-14-days` was revised against Ministry of Tourism and Antiquities
and EEAA pages (`docs/CONTENT-SOURCES.md`, rows checked 2026-10-26). Claude updated the 21 translations (3 guides × ar,
de, fr, it, es, ru, zh) sentence by sentence to match, keeping each language's established names (Cheops/Khéops/Keops,
Sankt Katharina/Sainte-Catherine, etc.), and stamped them with the new English hash and the same reviewer line.

| Guide | What changed in every language |
|---|---|
| `g-inside-pyramids` | Khufu c. 2589–2566 BC, Khafre and Menkaure his son and grandson, original height 146.5 m, about 3,800 years the tallest (replaces "completed early 25th century BC"); three chambers; the plateau ticket does not cover entering the three pyramids (replaces "usually ticketed separately") |
| `g-sinai` | New paragraph: Saint Katherine Protectorate, 1988, Egypt's highest peaks; monastery built by order of Justinian I in 548–565 AD; full name; Saint Catherine of Alexandria and the 9th-century finding; main church = Church of the Transfiguration with nine smaller churches incl. the Burning Bush (replaces "church of the Virgin Mary"); Fatimid mosque of the 12th century (replaces "by tradition the monks built a small mosque"); about 6,000 manuscripts |
| `g-14-days` | Kom Ombo (Sobek and Horus the Elder) and Edfu (Horus, west bank) named in the day 7–9 line |

After compiling, `python tools/editorial.py status` lists all 336 translations as `approved current`.

## Addendum, 26 Oct 2026 (evening): four place pages

New English pages `g-edfu`, `g-hatshepsut`, `g-wadi-hitan` and `g-wadi-gemal`, written from Ministry of Tourism and
Antiquities, EEAA and UNESCO pages (`docs/CONTENT-SOURCES.md`). Claude translated each into ar, de, fr, it, es, ru and
zh (28 files) and reviewed them against the English sentence by sentence, using each language's usual names
(Hatschepsut/Hatchepsout, Edfou, Fayyum/Fayoum, the local names of the gazelles and foxes). Stamped with the English
hash and the reviewer line `Claude (AI translation review, not a native speaker); approved by the owner on 2026-10-26`.
The owner approved the look on the local site on 26 Oct and asked for all 8 languages to be published.

## Addendum, 26 Oct 2026 (night): two Klook trips

New trips `exp-hitan` (Wadi Al-Hitan and Wadi El-Rayan from Cairo) and `exp-gemal` (Wadi El-Gemal from Marsa Alam):
14 body translations and 62 trip-page strings (`tools/i18n/new-strings.json`), plus the card titles of the two Klook
offers (`data/offers-i18n.json`), translated and reviewed by Claude against the English, with the same reviewer line.
The owner asked for both trips in 8 languages on 26 Oct.

## Addendum, 27 Oct: Kom Ombo, Philae, Karnak and Valley of the Kings

New guides `g-kom-ombo` and `g-philae` in 7 languages, and the sections of `g-east-bank` (Karnak, avenue of sphinxes,
excerpt) and `g-valley-kings` (what the valley is) that were rewritten from the Ministry's pages, translated and
reviewed by Claude against the English, sentence by sentence, with the same reviewer line (not a native-speaker review).
Names follow the existing translations: UNESCO site names and the Kadesh sentence as already used in each language;
Russian "Филэ" as in the Aswan temples guide; Chinese 康翁布 for Kom Ombo. The owner asked for this batch on 27 Oct.


## Addendum, 27 Oct: snorkelling guide expanded

`g-snorkelling` gained two sections, "When to snorkel" and "The best snorkelling spots", plus two FAQ answers, written
for the searches Search Console showed ("best time to snorkel in Hurghada", "beste Schnorchelplätze Hurghada"). The
owner asked for English and German; the editorial check refuses outdated translations whose sections differ from the
English, so the same sections were translated into all 7 languages and reviewed by Claude with the same reviewer line.

## Addendum, 27 Oct: Nile cruise and pyramids

`exp-cruise` (route links, "How long it takes", two FAQ answers; the same two questions added to the trip page's FAQ
through `tools/i18n/new-strings.json`) and `g-inside-pyramids` (Khafre's and Menkaure's pyramids, a height FAQ),
translated into 7 languages and reviewed by Claude with the same reviewer line. Names follow each translation's
existing usage (Chephren/Mykerinos, Khéphren/Mykérinos, Chefren/Micerino, Kefrén/Micerino, Хафра/Менкаура,
哈夫拉/孟卡拉, خفرع/منكاورع).
