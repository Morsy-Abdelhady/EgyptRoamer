# Arabic editorial content: mapping and import plan (2026-09-29)

**Status (2026-09-29): implemented and tested locally. Nothing has been imported into production.**
- Tooling: `tools/editorial.py` compiles every language (`--lang`, `check`, `status`); the Arabic contents label is "في هذه الصفحة".
- Core 1.2.5: `wp egypt-roamer editorial --lang=ar [--dry-run]`. It was numbered 1.2.4 until 2026-09-29, when Core 1.2.4 shipped the security headers instead.
- Content: 15 Arabic files in `content/editorial/ar/` (7 destinations, 8 experiences). All are `review: approved` (owner, 2026-09-29).
- Guides: no Arabic guides (owner decision 5).
- The production import waits for the owner's per-file approval and an explicit go (§5.4 step 6).

This plan builds on `docs/HANDOFF-2026-09-28.md` §5–§7 and §16C. The English content stays exactly as it is.

Sources are marked **[prod]** (public pages read on 2026-09-29), **[repo]** (this repository) and **[local]** (the local test install, which is built by the same seed as production).

## 1. English source: where it lives

| Layer | Location | Notes |
|---|---|---|
| Source (19 files) | `content/editorial/en/<seed-id>.md` | Front matter: `type`, `excerpt`, optional `destination` (guides). Body is Markdown-like: `[[toc]]`, `## Heading {#anchor}`, `###`, lists, `+` itinerary, `> ` callout, `?? ` FAQ, links to `/destinations/…` and `/experiences/…` [repo] |
| Compiled | `wordpress/wp-content/plugins/egypt-roamer-core/data/editorial/en/<seed-id>.html` + `index.json` | Core blocks only. Produced by `tools/editorial.py`; CI runs `tools/editorial.py check` [repo] |
| Database | `post_content` and `post_excerpt` of the **English** post, found by `_er_seed_id` | Written by `wp egypt-roamer editorial`. It always resolves to the default-language original (`pll_get_post(…, pll_default_language())`), so today it cannot write Arabic [repo: `includes/cli.php`] |
| Card and fact data | Post meta, not the body | Destinations: `_er_tagline`, `_er_region_label`, `_er_best_time`, `_er_getting_there`, `_er_map_reach`, `_er_highlights`. Experiences: `_er_location`, `_er_duration`, `_er_badge`, `_er_destination`. Shared: `_er_lat`, `_er_lng`, `_er_nights`, featured image [repo: seed] |

## 2. How Arabic was created

Arabic was created by `wp egypt-roamer seed --translations --publish-translations` (`ER_CLI::translations()`) [repo]:

- **Destinations** (seed id `dest-<id>-ar`):
  - Title, slug and `post_excerpt` come from the prototype's Arabic `desc`.
  - `post_content` is one paragraph, `<p>{Arabic desc}</p>`.
  - The translated meta: tagline, region, best time, getting there, map reach, highlights.
  - lat/lng/nights are copied from English, and the featured image is shared.
  - Status: publish.
- **Experiences** (seed id `<exp-id>-ar`):
  - Title and slug are translated.
  - **`post_content` and `post_excerpt` are empty.**
  - The meta: `_er_location`, `_er_duration`, `_er_badge`, and `_er_destination` copied from English, which still points at the **English** destination IDs.
  - Status: publish.
- **Guides:** **no Arabic translations exist** [local: every guide has `ar` = none]. The seed never created them.
- Every item is linked to its English original with `pll_save_post_translations` [repo].

Relations are not a problem. `er_get_related()` and `er_get_referencing()` resolve across the whole translation group (`er_translation_group`, `er_translated_post_id`), so an Arabic experience pointing at the English Cairo still appears under the Arabic Cairo [repo: `includes/api.php`]. **No relation data needs to change.**

## 3. Mapping on production: English source → Arabic target

The IDs come from each page's shortlink. The Arabic URL comes from the English page's `hreflang="ar"`. All pages return 200, and every Arabic page has `lang="ar"` and `dir="rtl"`. Word counts are for the `.prose` body [prod].

### Destinations

| Seed id (EN → AR) | EN ID | EN body now | AR ID | AR URL | AR body now |
|---|---|---|---|---|---|
| `dest-cairo` → `dest-cairo-ar` | 18 | 1,825 words, 14 H2, 6 FAQ, contents box | 195 | `/ar/destinations/القاهرة/` | 19 words (seed paragraph) |
| `dest-luxor` → `dest-luxor-ar` | 19 | 1,042 w, 10 H2, 4 FAQ | 196 | `/ar/destinations/الأقصر/` | 15 w |
| `dest-aswan` → `dest-aswan-ar` | 20 | 891 w, 9 H2, 4 FAQ | 197 | `/ar/destinations/أسوان/` | 18 w |
| `dest-alexandria` → `dest-alexandria-ar` | 24 | 870 w, 8 H2, 4 FAQ | 201 | `/ar/destinations/الإسكندرية/` | 15 w |
| `dest-siwa` → `dest-siwa-ar` | 23 | 739 w, 7 H2, 4 FAQ | 200 | `/ar/destinations/سيوة/` | 22 w |
| `dest-hurghada` → `dest-hurghada-ar` | 21 | 615 w, 7 H2, 4 FAQ | 198 | `/ar/destinations/الغردقة/` | 17 w |
| `dest-sharm` → `dest-sharm-ar` | 22 | 740 w, 8 H2, 4 FAQ | 199 | `/ar/destinations/شرم-الشيخ/` | 17 w |

### Experiences

| Seed id (EN → AR) | EN ID | EN body now | AR ID | AR URL (slug) | AR body now |
|---|---|---|---|---|---|
| `exp-giza` → `exp-giza-ar` | 25 | 493 w, 7 H2, 3 FAQ | 202 | `/ar/experiences/جولة-خاصة-إلى-أهرامات-الجيزة-وأبو-الهو/` | 0 (empty) |
| `exp-dinner` → `exp-dinner-ar` | 26 | 344 w, 6 H2 | 203 | `/ar/experiences/رحلة-عشاء-نيلية-مع-عرض-حي/` | 0 |
| `exp-abu` → `exp-abu-ar` | 27 | 434 w, 7 H2 | 204 | `/ar/experiences/رحلة-يوم-إلى-أبو-سمبل-من-أسوان/` | 0 |
| `exp-dive` → `exp-dive-ar` | 28 | 359 w, 5 H2 | 205 | `/ar/experiences/غوص-في-البحر-الأحمر-غطستان-بين-الشعاب/` | 0 |
| `exp-safari` → `exp-safari-ar` | 29 | 396 w, 7 H2 | 206 | `/ar/experiences/سفاري-الصحراء-البيضاء-والسوداء/` | 0 |
| `exp-food` → `exp-food-ar` | 30 | 352 w, 6 H2 | 207 | `/ar/experiences/جولة-طعام-الشارع-في-القاهرة-ليلًا/` | 0 |
| `exp-valley` → `exp-valley-ar` | 31 | 427 w, 6 H2 | 208 | `/ar/experiences/وادي-الملوك-ومعبد-حتشبسوت/` | 0 |
| `exp-siwa` → `exp-siwa-ar` | 32 | 360 w, 7 H2 | 209 | `/ar/experiences/بحر-الرمال-العظيم-بسيارات-الدفع-الربا/` | 0 |

The Arabic experience meta descriptions are empty today. The Arabic destination excerpts are the one-line seed `desc`.

### Guides

| Seed id | EN status | AR target |
|---|---|---|
| `g-best-time`, `g-7-days`, `g-cairo`, `g-gems` | draft, English body written | **does not exist**; must be created as an Arabic **draft** linked in Polylang |
| `g-costs`, `g-safe`, `g-cruises` | draft, no body (facts missing) | none; out of scope until the English exists |

The English guide IDs on production aren't visible from outside, because they're drafts. The import finds items by `_er_seed_id`, so it doesn't need IDs.

Local IDs differ from production (e.g. local Cairo EN 5 → AR 180). **Never hard-code IDs.** Always resolve by seed id and Polylang.

## 4. What must NOT be translated or overwritten

The Arabic import may write **only `post_content` and `post_excerpt`** of Arabic posts, and only under the "empty or untouched seed text" rule (§5). Everything else is off-limits:

| Field | Why it stays |
|---|---|
| Every English post (all fields) | English is done and live |
| `post_title` (Arabic) | Already translated. Changing it changes the H1, cards and menus. A title review is a separate editorial decision |
| `post_name` (Arabic slugs) | Live URLs, hreflang and internal links depend on them |
| `post_status` | Destinations and experiences are already published. New Arabic guides are created as **draft** only |
| "Ready to index" flag, `blog_public` | Indexing stays off until the launch gate |
| Polylang language and translation group | Architecture. The import only uses existing groups (and adds the Arabic guide to its group). Production sync is off for every field [prod, verified 2026-09-29] |
| `_er_seed_id` | The import's own key |
| `_er_destination` and other relations | Resolve across translations already (§2) |
| `_er_lat`, `_er_lng`, `_er_nights`, featured image, `menu_order`, terms | Language-neutral |
| Arabic card/fact meta (`_er_tagline`, `_er_region_label`, `_er_best_time`, `_er_getting_there`, `_er_map_reach`, `_er_highlights`, `_er_location`, `_er_duration`, `_er_badge`) | Out of scope for the body import; see finding 1 |
| Heading anchors (`#why-go`, `#giza`…) | Keep the English ASCII anchors in Arabic, so the contents box and links stay stable across languages |
| Proper nouns | Use the established Arabic names already on the site (القاهرة، الأقصر، أسوان، سيوة، شرم الشيخ، الغردقة، الإسكندرية، خان الخليلي، أبو سمبل…) and official Arabic names of sites and museums (e.g. المتحف المصري الكبير، المتحف القومي للحضارة المصرية) |
| The fact policy | No prices, opening hours, travel times, distances, ratings, provider claims or "best" rankings. Same sources as `docs/CONTENT-SOURCES.md`; no new facts that the English doesn't have |

**Finding 1 (owner decision, not part of this task):**
- The key facts shown on both English and Arabic destination pages come from the prototype's seed meta, and contain travel times: "1h flight or overnight train from Cairo", "8h drive…", "ساعة طيران أو قطار ليلي من القاهرة", "8 ساعات بالسيارة…".
- Experience facts contain durations: "3 hours", "3 ساعات".
- This predates the editorial fact policy and exists in both languages.
- Decide whether to keep, soften or remove these values. That would be a separate, language-wide change.

## 5. Proposed mechanism

The design keeps one pipeline. It is dry-run first, review-gated and never overwrites. It extends what exists instead of adding a new system.

### 5.1 Source files
- `content/editorial/ar/<same-seed-id>.md`, one per English file. Same front matter and structure, same anchors and sections, Arabic text.
- New front-matter fields:
  - `source: <sha of the English .md it translates>`, so an outdated translation is detectable when the English changes;
  - `review: pending | approved`;
  - `reviewer: <name>`;
  - `reviewed: <date>`.
- Internal links keep the **English paths** (`/destinations/luxor/`). The import rewrites them to the Arabic translation's permalink (resolve with `url_to_postid` → `pll_get_post(…, 'ar')` → `get_permalink`). This avoids hand-typing percent-encoded Arabic slugs. If an Arabic target is missing or unpublished, the link is left as plain text and reported.

### 5.2 Compiler (`tools/editorial.py`, tooling only, not deployed)
- Add `--lang <code>`. It reads `content/editorial/<lang>/`, writes `…/data/editorial/<lang>/`, and localises the one fixed UI string: "On this page" → "في هذه الصفحة".
- `check` covers every language present. CI already runs `check`.

### 5.3 Import command (a Core change, `includes/cli.php`)
`wp egypt-roamer editorial --lang=ar [--dry-run] [--create-missing-guides]`:
- **Target:** for each file, find the English original by `_er_seed_id`, then its Arabic translation with `pll_get_post( $id, 'ar' )`. Write only to that post; never to English.
- **Guard:** write `post_content` / `post_excerpt` only when they are empty or still exactly the Arabic seed text (the Arabic `desc` from `seed.json` `translations.ar.destinations[<id>].desc`). Anything an editor changed is kept and reported.
- **Review gate:** files with `review: pending` are skipped, unless the run is `--dry-run`. Unreviewed Arabic can never be imported.
- **Guides:** only with `--create-missing-guides`. The Arabic guide is created as **draft**, language `ar`, added to the English guide's Polylang group, and linked to the Arabic destination where the English one is.
- **Output:** counts (bodies, excerpts, guides created, links rewritten, links left as text, kept, skipped-unreviewed, missing), like the English command.
- **Never:** status (except new guide drafts), titles, slugs, meta, relations, "Ready to index", other languages.
- **Version:** Core 1.2.5 (renumbered from 1.2.4), shipped through the normal deploy. The deploy itself never runs the command.

### 5.4 Order of work
1. Owner confirms this plan, and names the **Arabic reviewer** (native speaker).
2. Implement §5.2 and §5.3. Test on the local install:
   - dry run;
   - real run;
   - rerun (0 changes);
   - an edited-body case (kept);
   - a pending-review case (skipped);
   - an English post untouched (hash before and after);
   - RTL rendering of the contents box, callouts, FAQ and itinerary.
3. Write Arabic drafts, Cairo first, then the other destinations, the experiences and the 4 guides. They should be Modern Standard Arabic, natural rather than literal, following the English structure and fact policy. They're committed with `review: pending`.
4. The reviewer approves or edits per file, and the files are marked `approved`.
5. Polylang synchronisation on production: **verified off** (read-only in wp-admin, 2026-09-29). All 11 options are unticked: taxonomies, custom fields, comment/ping status, sticky, published date, post format, page parent/template/order, featured image. Every Core post type and taxonomy is translatable (locked on by Core). Writing an Arabic post therefore cannot change its English original. Re-check only if the Polylang settings change.
6. On production, only when the owner asks: `cd ~/html && wp egypt-roamer editorial --lang=ar --dry-run`, show the counts, then the real run.
7. Verify the Arabic pages live: word counts, anchors, rewritten links, RTL layout at 360–1440 px, no English leakage.

## 6. Owner decisions (2026-09-29)

1. **Reviewer:** the owner is the final Arabic reviewer and approves file by file. To approve a file, set `review: approved`, `reviewer:` and `reviewed:` in its front matter, run `python tools/editorial.py`, then commit.
2. **Register:** Modern Standard Arabic, in a polished and natural editorial travel-magazine style. No colloquial Egyptian and no literal translation.
3. **Titles and slugs:** Arabic titles and slugs stay as they are. A change needs a documented, evidence-based issue.
4. **Travel times:** remove them from destination key facts in every language, as a separate task. Experience durations stay.
5. **Arabic guides:** not yet. Finish and approve the English guides first. The importer skips `er_guide` files for translations and never creates posts.

## 7. How it was implemented (differences from §5)

- **Review gate:**
  - A translation is imported only when it is `approved` **and** its `source:` matches the current English file.
  - `current` in `index.json` is computed by the compiler; `python tools/editorial.py status` lists outdated files.
  - A dry run reports every file and marks the unapproved ones.
- **Compiler checks** (`check` fails CI):
  - `type` and `destination` must match the English file;
  - the H2 anchors must be identical and in the same order;
  - an excerpt is required;
  - `review` must be `pending` or `approved`, and an approved file needs `reviewer` and `reviewed`.
- **No `--create-missing-guides`:** replaced by decision 5; guide files are reported as skipped.
- **Links:**
  - Links are stored root-relative (`/ar/destinations/…/`), so they work on any host.
  - A link whose Arabic target is missing or unpublished becomes plain text and is reported.

## 8. Local test evidence (2026-09-29, local install; importer then numbered 1.2.4, now 1.2.5)

| Test | Result |
|---|---|
| English compile unchanged by the new compiler | byte-identical `data/editorial/en/` |
| Dry run, all pending | 15 would-update, flagged "not approved" |
| Real run, all pending | 0 writes, 15 skipped |
| `source` outdated (simulated) | skipped: "the English changed since it was translated" |
| Real run, approved (simulated in the compiled index only) | bodies 15, excerpts 15, 35 links pointed at Arabic pages |
| Rerun | 0 writes (15 kept) |
| Arabic target unpublished (simulated) | the link becomes plain text and is reported |
| Unknown language `xx` | error, no writes |
| English posts (title, slug, status, body, excerpt, all meta) | md5 identical before and after |
| Content QA | no Latin words except the airport codes CAI/SPX; no number absent from the English; all internal links match the English |

## 9. How the Arabic content reaches production (release gate 1, verified 2026-09-29)

**The server never reads `content/editorial/`.** The Markdown sources are authoring files only. The importer reads the compiled files inside Core, so the normal Theme/Core deploy delivers everything it needs. No architecture change is needed.

```
content/editorial/ar/*.md                       (repo only; never deployed)
      │  python tools/editorial.py              (local; CI re-runs `check` and fails if stale)
      ▼
plugins/egypt-roamer-core/data/editorial/ar/    (15 × .html + index.json, committed)
      │  deploy-production.yml: rsync of the Core folder only
      ▼
~/html/wp-content/plugins/egypt-roamer-core/data/editorial/ar/
      │  wp egypt-roamer editorial --lang=ar    (reads ER_CORE_DIR . 'data/editorial/ar/')
      ▼
Arabic posts (only approved files, only empty or seed bodies)
```

### Evidence

| Check | Result |
|---|---|
| Importer source path | `cli.php`: `$dir = ER_CORE_DIR . 'data/editorial/' . $lang . '/'`, where `ER_CORE_DIR = plugin_dir_path(__FILE__)`. No code in `wordpress/` references `content/editorial` |
| Deploy scope | the Core folder is staged and rsynced whole. The excludes (`wp-config.php`, `.htaccess`, `wp-admin/`, `wp-includes/`, `uploads/`, core WP files, `*.sql*`, `*.sqlite`, `*.db`, `*.wpress`, `.git/`, `.github/`, `src/`) match nothing in `data/editorial/` |
| Files tracked in git | all 16 files in `data/editorial/ar/` are committed and not gitignored |
| The same path works on production today | the public files `…/egypt-roamer-core/data/editorial/en/` on egyptroamer.com (index.json with 19 items, dest-cairo.html, g-cairo.html, exp-giza.html) are byte-identical (SHA-256) to the repo. The English import used this route. `data/editorial/ar/index.json` is 404 today, as expected before the deploy |
| Local deploy emulation | Core staged with the workflow's exclude rules: 16 Arabic files present, byte-identical, no `.md` sources in the package |
| Local import without sources | with `content/editorial/` moved away, `wp egypt-roamer editorial --lang=ar --dry-run` reported bodies 15, excerpts 15, 35 links pointed at Arabic pages, not approved 15 (real run: 0 writes) |

### Approval takes effect only through a deploy

The review state travels in the compiled `index.json`. After the owner approves files, run `python tools/editorial.py` and commit. The next Core deploy then ships the approval. Until then, a production real run skips every file. A dry run works before approval and shows what would change.

### Owner note: the compiled files are publicly readable

- Files under the plugin folder can be fetched over HTTP, as `data/seed.json` and the English files already are.
- After the deploy, the unreviewed Arabic drafts will be readable at `/wp-content/plugins/egypt-roamer-core/data/editorial/ar/…`. They are unlinked, and the site is noindex.
- A `.htaccess` deny rule would not deploy, because `.htaccess` is deliberately excluded by the workflow.
- If this matters, the alternative is to commit compiled Arabic files only once they are approved. The cost is that production could no longer dry-run the pending files. **Owner decision; nothing changed.**
