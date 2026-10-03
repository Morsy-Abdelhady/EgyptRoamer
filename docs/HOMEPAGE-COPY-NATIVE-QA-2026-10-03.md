# Homepage copy — native-language QA — 2026-10-03

Scope: every visitor-facing string changed in theme 1.2.33 (trust copy) and 1.2.34 (newsletter, Red Sea caption/alt),
in the 7 translated languages. English is the source and is not rewritten.

Business meaning to preserve: Egypt Roamer is **editorial + trip planning + affiliate discovery**. It is not a booking
engine, checkout or live marketplace. No string may promise booking, a monthly letter, or partner availability.

Reviewer: Claude (translations written by Claude, in `tools/i18n/new-strings.json`, like all WordPress-only strings).
That is a self-review, so anything idiomatic is marked **NEEDS NATIVE REVIEW** rather than PASS. Confidence: H/M/L.
No booking promise was found in any language. Every string below keeps the intended meaning.

## 1. Hero subtitle — "Discover and plan the best experiences in Egypt — all in one place." (also the homepage meta description)

| Lang | Current | Conf. | Status | Note |
|---|---|---|---|---|
| ar | اكتشف أفضل التجارب في مصر وخطّط لها — كل ذلك في مكان واحد. | H | PASS | Built from the approved prototype sentence with "قارن/احجز" removed. |
| de | Entdecken und planen Sie die besten Erlebnisse in Ägypten — alles an einem Ort. | H | PASS | |
| fr | Découvrez et planifiez les meilleures expériences en Égypte — au même endroit. | H | PASS | |
| it | Scopri e pianifica le migliori esperienze in Egitto — tutto in un unico posto. | H | PASS | |
| es | Descubre y planea las mejores experiencias en Egipto — todo en un solo lugar. | H | PASS | "planifica" is equally correct; "planea" matches the existing "Planear mi viaje". |
| ru | Открывайте и планируйте лучшие впечатления в Египте — всё в одном месте. | H | PASS | |
| zh | 在同一个地方，发现并规划埃及最好的体验。 | H | PASS | |

## 2. Fact line — "1 | place to discover & plan it all"

| Lang | Current (after "1") | Conf. | Status | Note |
|---|---|---|---|---|
| ar | مكان واحد لاكتشاف كل شيء والتخطيط له | M | NEEDS NATIVE REVIEW | Reads "1 one place…". This redundancy comes from the approved prototype pattern. A native editor may prefer dropping «واحد». |
| de | Ort, um alles zu entdecken & zu planen | H | PASS | |
| fr | seul endroit pour tout découvrir et planifier | H | PASS | |
| it | posto per scoprire e pianificare tutto | H | PASS | |
| es | lugar para descubrirlo y planearlo todo | H | PASS | |
| ru | место, чтобы всё найти и спланировать | H | PASS | |
| zh | 站式发现与规划 | M | PASS WITH NOTE | Renders "1站式…" (one-stop), the prototype's approved device. |

## 3. Experiences intro — "Handpicked by our editors, with the practical details to plan them."

| Lang | Current | Conf. | Status |
|---|---|---|---|
| ar | اختارها محررونا، مع التفاصيل العملية للتخطيط لها. | H | PASS |
| de | Von unserer Redaktion ausgewählt, mit den praktischen Details für Ihre Planung. | H | PASS |
| fr | Sélectionnées par notre rédaction, avec les détails pratiques pour les planifier. | H | PASS |
| it | Scelte dalla nostra redazione, con i dettagli pratici per pianificarle. | H | PASS |
| es | Elegidas por nuestra redacción, con los detalles prácticos para planearlas. | H | PASS |
| ru | Отобраны нашей редакцией — с практическими деталями для планирования. | M | PASS WITH NOTE (slightly formal; correct) |
| zh | 由编辑精选，并附上规划所需的实用信息。 | H | PASS |

## 4. Destinations intro, last sentence — "Choose a place to step inside it." (replaces "Hover a place…")

Works for touch and desktop: on every device, choosing a destination opens it.

| Lang | Current | Conf. | Status | Proposed (not applied) |
|---|---|---|---|---|
| ar | اختر أي مكان لتدخله. | H | PASS | — |
| de | Wählen Sie einen Ort, um einzutreten. | M | NEEDS NATIVE REVIEW | "Wählen Sie einen Ort und treten Sie ein." (more natural rhythm) |
| fr | Choisissez un lieu pour y entrer. | H | PASS | — |
| it | Scegli un luogo per entrarci. | H | PASS | — |
| es | Elige un lugar para entrar en él. | H | PASS | — |
| ru | Выберите место, чтобы войти в него. | M | NEEDS NATIVE REVIEW | "Выберите место — и загляните внутрь." (the literal "войти в него" is stiff) |
| zh | 选择一个地方，走进它。 | M | PASS WITH NOTE | Literal but natural enough; same device as the approved original. |

## 5. Newsletter (1.2.34) — title "Hear from us / when it matters.", copy, confirmation

No letter is sent today, so the copy promises nothing periodic: "New routes and guides, sent only when we have something
worth your time. No spam, ever." The confirmation is "You're on the list. We'll write when there's something worth reading."

| Lang | Title (line 1 / line 2) | Conf. | Status | Note |
|---|---|---|---|---|
| ar | نكتب إليك / حين يستحق الأمر. | M | NEEDS NATIVE REVIEW | Meaning right; a native editor may prefer «نراسلك». |
| de | Post von uns, / wenn es sich lohnt. | H | PASS | |
| fr | Des nouvelles de nous, / quand cela compte. | M | NEEDS NATIVE REVIEW | Correct; "Des nouvelles de notre part" is more idiomatic. |
| it | Notizie da noi, / quando contano. | M | PASS WITH NOTE | |
| es | Noticias nuestras, / cuando importan. | M | PASS WITH NOTE | |
| ru | Мы напишем вам, / когда будет о чём. | M | NEEDS NATIVE REVIEW | Colloquial register; fits the brand voice but should be confirmed. |
| zh | 有值得分享的内容时，/ 我们再写信给你。 | M | NEEDS NATIVE REVIEW | Clause order differs from English by design (Chinese puts the condition first). |

Copy and confirmation: all 7 are faithful (H) and **PASS**, apart from register checks in ar/zh, which belong in the same
native review.

## 6. Homepage Red Sea scene caption and alt (1.2.34)

- Caption: "27.2579° N · 33.8116° E — Hurghada". The coordinates follow each language's existing format: decimal
  comma in de/fr/it/es/ru, «شمالًا/شرقًا» in ar, 北纬/东经 in zh. Place names come from the site's own destination names.
  Status: **PASS** (H).
- Alt: "A shoal of small orange fish over a coral reef in the Red Sea". Deliberately no species name, because the
  photo is tagged only "Hurghada". Status: **PASS** (H), all 7.

## Summary

PASS 37 · PASS WITH NOTE 6 · NEEDS NATIVE REVIEW 7 (ar fact line, de/ru "Choose a place", newsletter title ar/fr/ru/zh).
None of the NEEDS NATIVE REVIEW items changes the business meaning or makes a booking promise. They are about idiom
only, so the copy is safe to keep live while a native reviewer confirms it. Suggestions are listed above and were not applied.
