# SEO growth opportunities — 2026-10-03

The technical layer is stable and was not changed: 176 indexable URLs, sitemap, self-canonicals, hreflang, redirects,
robots and the index gate. This document is about the **content graph**, measured from the production crawl of
2026-10-03 (`tools/qa/seo-audit.py`). Inlinks count in-content links from other indexable pages; header, footer and
navigation are excluded.

## 1. Current content graph (English; every language mirrors it)

| Page | From destinations | From experiences | From guides | Other (home, archive) |
|---|---|---|---|---|
| Cairo | 6 | 4 | 0 | 2 |
| Luxor | 6 | 1 | 0 | 2 |
| Alexandria | 6 | 0 | 0 | 2 |
| Siwa | 6 | 2 | 0 | 2 |
| Aswan | 3 | 1 | 0 | 2 |
| Hurghada | 2 | 1 | 0 | 2 |
| **Sharm El Sheikh** | **1** | 1 | 0 | 2 |
| Each of the 8 experiences | 1 (2 for Red Sea diving) | **0** | **0** | 2 |

Clusters that exist today:
- **Nile Valley:** Cairo ↔ Luxor ↔ Aswan, with the Giza, Valley of the Kings, Abu Simbel and dinner-cruise experiences.
- **Western Desert:** Siwa, with the Great Sand Sea and White/Black Desert experiences.
- **Red Sea:** Hurghada and Sharm, with diving.
- **Mediterranean:** Alexandria, with no experience.

## 2. Weak spots

1. **Experiences are leaves.** Each one is reached from one destination, the archive and the homepage rail. No
   experience links to another, although every experience body has a "Combine it with" section naming related ones
   (e.g. Abu Simbel → Philae, Aswan; Valley of the Kings → Karnak). Those mentions are plain text.
2. **No guide layer.** All 7 guides were drafts until today, so the planning cluster (best time, itinerary, city
   guide), the strongest informational intent, was missing. Destination pages render "Plan your trip to {name}"
   only when published guides relate to the destination.
3. **Sharm El Sheikh** is the least connected destination: one destination inlink. Sinai has no guide or second
   experience.
4. **Alexandria** has no experience, so the Mediterranean cluster is one page.
5. **Experience archive pages are thin** (85–203 words: cards only).
6. **Multilingual:** destination and experience pages exist in 8 languages (22 indexable URLs per language), but
   guides exist only in English.

## 3. Opportunities, ranked by value and risk

| # | Opportunity | Value | Risk | Status |
|---|---|---|---|---|
| 1 | Publish the 4 ready English guides (best time, 7 days, Cairo, hidden gems): +4 indexable URLs; adds guide→destination/experience links and the planning intent | High | Low (index gate, sitemap and canonical are unchanged) | Done today (execution report §7) |
| 2 | Link the "Combine it with" mentions in experience bodies to the existing experience/destination pages (8 bodies × 8 languages) | High (experiences stop being leaves) | Low, but it's an editorial body change: owner sync (`wp egypt-roamer editorial`) and the 7 translations | Proposed: edit `content/editorial/en/exp-*.md`, then the translations |
| 3 | 7-days guide: add links to the Giza tour, Abu Simbel day trip, Valley of the Kings and Nile dinner cruise experiences; hidden gems: add links to Valley of the Kings (Medinet Habu, Deir el-Medina) and Abu Simbel/Aswan | Medium | Low | Proposed (same sync path; the guides are live without them) |
| 4 | Give the 4 guides a destination relation (`destination:` front matter exists only for the Cairo guide), so destination pages show "Plan your trip to …" | Medium | Low | Proposed (editorial field) |
| 5 | Safety guide, then costs, then cruises | High search intent | Blocked on sourced data | See `GUIDE-DATA-REQUIREMENTS-2026-10-03.md` |
| 6 | A Sinai cluster: Dahab/St Catherine or a Ras Mohammed snorkel experience linked from Sharm | Medium | Needs real content | Proposed |
| 7 | An Alexandria experience (e.g. a city walk) | Medium | Needs real content | Proposed |
| 8 | Short approved intros on the experience archives (all 8 languages) | Low–medium | Low | Proposed |
| 9 | Translate the 4 published guides, after English approval, starting with Arabic | High for non-English search | Medium (translation QA, index-gate parity rules) | Deferred until English is approved |

Not recommended:
- shortening the 115 approved descriptions longer than 160 characters (no evidence of harm, and they are approved);
- changing slugs, canonical or hreflang;
- tag or archive pages for indexation.

## 4. Implemented in this phase (low risk, evidence-backed)

- The 4 guides published and made indexable through the existing CMS gate (execution report §7).
- Nothing else was changed in the link graph automatically. Items 2–4 are editorial body changes that go through the
  approved editorial pipeline and its translations.
