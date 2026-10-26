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
