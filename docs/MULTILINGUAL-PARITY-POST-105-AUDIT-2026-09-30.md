# Multilingual parity audit — user-facing additions after the 105-file review (2026-09-30)

Baseline: the 105 approved editorial files (`content/editorial/{ar,de,fr,it,es,ru,zh}`, approved 2026-09-29) and their compiled data are **unchanged** (`git diff` on `content/editorial`, `data/editorial`, `seed.json`: 0 files; `tools/editorial.py check` passes).

Method: the project's string extractor (`tools/i18n-strings.py`) against every theme/Core `.l10n.php` file; a read of every template/Core function that renders visitor text added since (`git log 2d949bc..`); the new page contents; a local render of every item in all 8 languages.

Classes: **A** must localize · **B** should localize · **C** intentionally English/original · **D** not user-facing.

## Inventory

| # | Item | Where | Class | Result |
|---|---|---|---|---|
| 1 | "On this page" (section-tab bar label) | theme `inc/sections.php` | A | Localized (theme l10n, 7 languages; same approved labels as `tools/editorial.py`) — done in 1.2.0 |
| 2 | Section tab labels | H2 text of each page | A | Come from the page's own (approved/localized) headings |
| 3 | Key facts card: "Key facts", "Things to do in {name}", "Plan My Trip", "Highlights" | theme | A | Already in the approved theme strings (all 7) |
| 4 | Offer UI: "Book with our partners", "from", "Price checked {date}", "via {partner}", CTA labels | theme/Core | A | Already localized (extractor: 0 missing in 7 languages) |
| 5 | **Affiliate disclosure sentence** under offers | Core setting `disclosure_text` (Polylang string, untranslated) | A | **Was English in all languages.** Now: Polylang translation if present, else the Core-shipped translation (`data/ui-copy.json`) while the setting holds the stock wording, else the English setting (a disclosure never disappears) |
| 6 | "How we work with partners" link | Core | A | Already localized |
| 7 | **Archive intros** (destinations, experiences, guides) | new | A | **Localized** in 8 languages (`data/ui-copy.json`, `er_archive_intro()`); an editor's text in Settings still overrides (English + Polylang translations, never shown in English on a translated archive). Guides intro only while guides are published in that language |
| 8 | **Privacy Policy** | page 3 (English, published) | A | **Localized** ×7 (`content/legal/i18n/*.json` → Core `data/legal/<lang>/privacy-policy.html`) |
| 9 | **Cookie Policy** | page 64 | A | **Localized** ×7 |
| 10 | **Affiliate Disclosure** | page 62 | A | **Localized** ×7 |
| 11 | **Contact** page text | page 60 | A | **Localized** ×7, new two-column layout (English updated too, only if unchanged since publication) |
| 12 | Contact form labels, topics, note, button, success and error messages, "Privacy Policy" link | Core `[er_contact_form]` | A | Already localized (Core l10n; extractor: 0 missing) |
| 13 | Form validation | browser (`required`, `type=email`) | A | Browser-native messages, in the visitor's browser language |
| 14 | **Footer legal row** | theme | A | **Localized**: built from the pages in the page’s language, each with its own title. **The footer columns were not at parity** (English 3 columns, other languages 1): fixed in theme 1.2.6, see `docs/FOOTER-PARITY-AUDIT-2026-09-30.md` |
| 15 | Terms of Use | page 63 (draft) | B | **BLOCKED — owner/lawyer**: governing law and liability wording. Not published in any language; hidden from every footer until published |
| 16 | Company legal name and address | legal pages | C | Kept in the official Arabic form in every language (legal identity, `lang="ar" dir="rtl"`) |
| 17 | Email `info@egyptroamer.com`, phone `+20 10 6049 4260` | legal/contact | C | Same everywhere; shown left-to-right in Arabic |
| 18 | Brand "Egypt Roamer" | everywhere | C | Latin in every language (as in the approved theme strings) |
| 19 | Translation note "This page is a translation of the English original" + link | translated legal pages | A | Localized; links the English original with `hreflang="en"` |
| 20 | Archive eyebrow "Egypt Roamer" | theme archive hero | C | Existing approved string, unchanged |
| 21 | Legal-sync admin screen (Egypt Roamer → Legal pages), CLI output | Core | D | English (admin) |
| 22 | 404, search, empty states | theme | A | Already localized (approved theme strings) |

Nothing user-facing remains silently English: items 15–18 and 20 are the only English/original ones, each with the reason above.

## Localized pages (created by the Core sync)

| Page | ar | de | fr | it | es | ru | zh |
|---|---|---|---|---|---|---|---|
| Privacy | سياسة الخصوصية | Datenschutzerklärung | Politique de confidentialité | Informativa sulla privacy | Política de privacidad | Политика конфиденциальности | 隐私政策 |
| Cookies | سياسة ملفات تعريف الارتباط | Cookie-Richtlinie | Politique relative aux cookies | Informativa sui cookie | Política de cookies | Политика использования файлов cookie | Cookie 政策 |
| Disclosure | الإفصاح عن الروابط التابعة | Offenlegung zu Partnerlinks | Divulgation des liens affiliés | Informativa sui link di affiliazione | Divulgación de afiliados | Раскрытие информации о партнёрских ссылках | 联盟链接披露 |
| Contact | اتصل بنا | Kontakt | Contact | Contatti | Contacto | Контакты | 联系我们 |

URLs: `/<lang>/<slug>-<lang>/` (the site's existing convention for translated pages, e.g. `/ar/journal-ar/`). Each is a Polylang translation of the English page (hreflang set of 9; self-canonical).

**Language strategy:** the translated legal texts render the approved English meaning block by block (same headings, lists, links, facts) and state that they are translations of the English original. They were drafted on 2026-09-30 and are **pending native-speaker and legal review**, like the UI strings in `tools/i18n/new-strings.json`. Voice follows the approved editorial files (de *Sie*, es *tú*, it *tu*, fr *vous*, ru *вы*; Arabic MSA).

## Local verification (8 languages)
- All 32 legal/contact pages: 200, correct `lang` (Arabic `dir="rtl"`), self-canonical, hreflang 9; footer rows and archive intros in the page language; guides intro hidden while no guide is published.
- Sync: dry run 28 create + 1 English update; real run the same; second run 29 current (idempotent); Polylang groups complete (en + 7).
- Page matrix: 24 page types (incl. ar/de/zh/ru legal + contact) × 390–1920 → overflow none, page errors none, axe 0; 320px: no overflow or clipping, cookie table stacks.
- Found and fixed: the form honeypot was hidden with `left:-9999px`, which in RTL made `/ar/contact-ar/` 11,439px wide; now clipped in place (Core 1.2.8).

## Production (2026-09-30)
- Commit `cfb3c27` → deploy run #19 success (Core 1.2.8, theme 1.2.5).
- Egypt Roamer → Legal pages (owner's wp-admin session): preview identical to local (28 create + English Contact #60 matching its published hash) → run: **28 pages created (IDs 431–459), Contact #60 updated**; re-preview: 29 current (idempotent).
- Live (fresh HTML, 3s between requests): ar/de/zh/ru/fr/it/es legal and contact pages 200, correct `lang`/`dir`, self-canonical, hreflang 9, `noindex, nofollow`, section tabs on long documents (Privacy 7, Cookies 4, Disclosure 4), localized footer rows in every language checked, localized archive intros (ar, de), no `-9999px` honeypot; Contact (en, ar) in the two-column layout with LTR email/phone; the private receiving mailbox appears nowhere.
- `/ar/contact-ar/` at 390px, real browser: no overflow, 0 page errors, axe 0.
- Cache: normal URLs `cf-cache-status: MISS` with the 1.2.5 CSS version (GoDaddy flushed on the content change).
- Indexing unchanged (noindex everywhere).

## Remaining
- Terms (all languages): BLOCKED — owner/lawyer (governing law, liability wording).
- Native-speaker and legal review of the 28 translated pages, the archive intros and the disclosure sentence: owner (non-blocking for the site to work; edit in WordPress — the sync never overwrites an edited page).
