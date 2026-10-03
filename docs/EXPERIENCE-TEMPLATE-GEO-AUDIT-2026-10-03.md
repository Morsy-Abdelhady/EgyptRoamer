# Experience Immersion, template system and AI search (GEO): audit, build and baseline (2026-10-03)

This report covers phases A to L of the "Experience Immersion + Template System + AI Search/GEO" mission.

**Releases**

| Release | What | Commit | Live build |
|---|---|---|---|
| Theme 1.2.37 / Core 1.2.23 | Experience Immersion (Abu Simbel) | `44d1c05` | `da65a2d6636a` |
| Theme 1.2.38 / Core 1.2.24 | GEO structured data | `281b750` | `342a846d9b2b` |

The query dataset and the AI-visibility benchmark are in [`AI-QUERY-DATASET-2026-10-03.md`](AI-QUERY-DATASET-2026-10-03.md).

**Verdict**

- **Experience Immersion: ACCEPTED AND DEPLOYED** for Abu Simbel.
- **Generalising it to the other experiences: BLOCKED.** There are no verified photographs for them, and the owner has not approved their previews (§7).
- **GEO:**
  - baseline done;
  - one structured-data improvement deployed;
  - the remaining roadmap needs editorial work or owner access (§11).

---

## 1. Phase A: what existed before this phase

Inspected in code (`template-parts/`, `includes/previews.php`, `data/previews.json`), in `git stash list`, and on production (HTML and screenshots, build `14fc896bf353`).

### IMPLEMENTED

| Item | Where | Deployed |
|---|---|---|
| Preview data model: one entry per experience seed id, per-language title, intro, captions and alt text, plus per-photo provenance (photographer, source, location tag, `generated` flag) | Core `data/previews.json` | yes (1.2.35) |
| Reader `er_preview_for()`: resolves the seed id across translations, English fallback with a `lang` attribute | Core `includes/previews.php` | yes |
| "Moment by moment" strip: scroll-snap, previous/next buttons, counter, progress bars, RTL, no-JS, photos deferred (`moments.js`) | theme `template-parts/experience-preview.php`, `src/js/components/moments.js` | yes |
| Video support: poster first, clip created only on play, muted, native controls, captions track, failure message, "Illustration, not footage" label | same | built, **unused** (no verified clip) |
| Abu Simbel content: 5 photographs, all tagged "Abu Simbel" by their photographers, captions in 8 languages | `previews.json` | yes |

### PARTIALLY IMPLEMENTED

| Item | State |
|---|---|
| Editor management | `_er_preview_*` editor fields, a meta-first reader and the `wp egypt-roamer previews` CLI exist only in `stash@{0}` ("V1.1 local work"). **Not deployed.** |
| Valley of the Kings preview | 4 verified photos and 8-language text exist only in the stash. **Not published**: the native-speaker gate is not satisfied (18 strings NEED NATIVE REVIEW). |
| Narrative | Each photo had a title and a caption, but there was no sequence, no "before" and no "after". |
| Non-English preview text | Self-reviewed only. A native check is still pending (documented in 1.2.35). |

### NOT IMPLEMENTED (the original intent)

- **Journey structure:** before → arrival → first moment → immersion → highlight → after.
- **Sensory layer:** "What it feels like".
- **Practical transition:** "What to know", then the next step.
- **Scroll storytelling:** cinematic, mobile-first composition.
- **Reuse:** a system other experiences could use.

The strip sat inside the 736 px reading column, between "What it is" and "Why it's worth the effort". On a phone it showed one 4:5 photo with a peek of the next. It was the "photos in a slider" pattern this mission rejects (production screenshot at 1440: a 574 px card strip with "1 of 5" controls).

### Templates touched by earlier work

| Template | Changed for previews | Other recent changes |
|---|---|---|
| Experience (`template-parts/single-commercial.php`; also renders tours and activities) | yes: strip injected after the first body section | 1.2.36: none specific |
| Destination (`single-er_destination.php`) | no | 1.2.36: shared hero height cap only |
| Guide (`single-er_guide.php`) | no | 1.2.36: photo hero, one alignment axis |

### Where preview data lives

| Source | Holds |
|---|---|
| `seed.json` | Hero photo id, title, location, duration, partner name, CTA label |
| `previews.json` | Moments (photos, provenance, 8-language text) and, now, the story |
| Editor fields | Stash only, not deployed |
| PHP | Layout, UI strings (`er_t`), crops |
| Editorial `content/editorial/en/exp-*.md` | The page body, the only approved source for story text |
| Post meta | `_er_location`, `_er_duration`; for Abu Simbel `_er_best_time`, `_er_who_for` and `_er_good_to_know` are empty |

### Shared vs. Experience-specific

- **Shared:** the hero (`er_page_hero`), section tabs (`er_section_nav`), body sectioning (`er_body`), the layout and aside (`er_layout`), Key facts (`er_glance`), cards, the FAQ box, the offer rows and the CSS bundles.
- **Experience-specific:**
  - the preview (strip, now the Immersion);
  - "Is it right for you?";
  - "Good to know";
  - "Ways to do it";
  - "Alternatives to compare".

---

## 2. Phase B: template matrix

**Legend:**
- **S** = shared (same component).
- **I** = intentional difference.
- **B** = bug.
- **M** = missing.

| | Guide (editorial) | Destination (discovery) | Experience (immersion + planning) |
|---|---|---|---|
| Hero | S: dark photo band, H1, intro. Byline, date and reading time (I) | S. Eyebrow is the region, intro is a tagline (I) | S. Intro is the page summary, meta shows location and duration (I) |
| Purpose | Reading | Identity + planning | Feeling + planning |
| Key facts | none (I: an editorial page, correct as is) | Region, best time, things to do, "Plan My Trip" (I) | Location and duration. With a story, they move into "What to know" (I, **new**) |
| Tabs | S (centred column) | S | S, placed **after** the Immersion (I, **new**) |
| TOC | the tabs replace the in-body TOC (S) | S | S |
| Immersion | none (I) | none (I) | **New**: journey, feel, know (I). Experiences without a story: the old strip, or nothing (M for 7 of 8) |
| Editorial body | S (sections, cards, FAQ box) | S | S |
| Planning | "Plan the details" in the body | "How long, and when to go", "Getting there" | "What to know" (**new**) + body sections |
| Media | hero photo (1.2.36) | hero photo | hero + journey photographs (Abu Simbel) |
| CTA | none in the body (I) | "Book {name} with our partners" when offers exist; planner otherwise | partner offer (with disclosure) or planner, now also at the end of "What to know" |
| FAQ | S (3 of 4 guides) | S (all) | S (all) |
| Related | "Experiences / Destinations in this guide" | Things to do, activities, guides, more of Egypt | Alternatives, where it happens, guides |

**Findings**

- **No bugs.** The template differences are intentional.
- The duplicate-navigation regression was not reproduced (`DUPLICATE-NAV-AUDIT-2026-10-03.md`).
- Guide alignment was fixed in 1.2.36.
- **Missing:** the Immersion for 7 of the 8 experiences. That is a content gap, not a template gap (§7).
- **Implemented:**
  - Experience now differs from Destination where it should: cinematic journey first, planning second.
  - No Destination layout with extra cards.
- **Not implemented, deliberately:**
  - no Key facts on guides;
  - no Immersion on destinations.

---

## 3. Phase C: Experience Immersion architecture

### Product structure

The same structure for every experience; only the content changes.

1. **Enter.**
   - Eyebrow and title: a statement of the journey.
   - Lede: 2–3 sentences.
   - The route at a glance: the numbered stage names, each linking to its stage. A reader skimming only this knows the whole day.
2. **The journey.** 5–8 stages, each with an eyebrow (the stage's role: Before dawn, Arrival, Inside, The highlight, Afterwards), a title, 1–3 sentences and either:
   - a **verified photograph** (credit linked to its source); or
   - a **typographic stage** when no verified photo exists. The hour of the day is drawn by the background gradient, never by a stand-in picture.
3. **In motion.**
   - **When a verified clip exists:** the existing play-on-intent video component (poster, muted, captions, no autoplay with sound) slots in as a stage.
   - **Otherwise:** none. No fake footage.
4. **What it feels like.** Pace, time on site, setting, crowds, physical effort and social setting, only where the approved text supports them. Labelled "General expectations from our guide, not promises."
5. **What to know** (light ground: the switch to planning).
   - Facts from post meta (location, duration), then practical items from the approved text.
   - The next step:
     - the partner's own CTA (affiliate link via `er_offer_cta_html`, placement `experience-immersion`, with the disclosure) when an offer is live;
     - otherwise "Plan My Trip" and "Read the full guide".
   - No cart, booking or payment.
6. The full editorial body, tabs, FAQ and related content follow, unchanged.

### Content model (one system, not two)

The story extends the existing preview entry in `previews.json`. Stages reference the entry's verified moments by index, so provenance stays in one place:

```json
"story": {
  "label":  {"en": "The journey"},
  "title":  {"en": "…"},
  "lede":   {"en": "…"},
  "stages": [
    {"tone": "dawn", "eyebrow": {...}, "title": {...}, "text": {...}},
    {"moment": 0, "eyebrow": {...}, "title": {...}, "text": {...}}
  ],
  "feel": {"title": {...}, "note": {...}, "items": [{"label": {...}, "text": {...}}]},
  "know": {"title": {...}, "more": {...}, "items": [{"label": {...}, "text": {...}}]}
}
```

`er_preview_story()` (Core) returns the story in the page's own language, never another's:

- **The language has the story text:** the full journey.
- **It doesn't:** the same photographs in the same order, with their approved translated moment titles and captions.
  - Typographic stages, feel and know are omitted.
  - The heading falls back to the translated preview title and intro.
  - A page never mixes languages, and no translation is invented.
- **Entry without a story:** the old strip.

**Editor management, next step:** port the stashed V1.1 editor fields to this shape (the meta-first reader already exists in the stash). That is a separate, approved-scope change. It is not done here, so as not to deploy the stash.

### Technical design

| Concern | Decision |
|---|---|
| Placement | Full width between the hero and the tabs, on the hero's dark ground (one cinematic opening). "What to know" returns to the light ground. |
| Mobile first | Each stage is a full-width 4:5 photo with its text **beneath** (never over the photo), max 82svh. Text stages are 70svh typographic panels. No interaction is needed to understand the story. |
| Desktop (≥ 1024 px) | 58/42 grid. The photograph is `position: sticky` beside the text while the text passes (rows 128vh). Pure CSS, no pinning script. |
| Motion | Scroll-driven CSS (`animation-timeline: view()`): photos settle (scale 1.08 → 1, opacity 0.4 → 1), text stages rise. Only under `prefers-reduced-motion: no-preference` and `@supports`. Otherwise everything is static and complete. |
| JavaScript | `immersion.js` (≈ 1 KB): defers each photo until about one screen away (reuses `moments.js` `deferPhotos`) and marks the stage in view on the route (`aria-current="step"`). Without JS, `<noscript>` provides the photos. |
| Images | Phone crop 1.25 at 480/640/828/1080 w (`sizes=100vw`). Desktop crop 0.9 at 720/960/1280/1600 w (`sizes=58vw`). No 2000–2400 px files for phone slots. |
| Accessibility | One H2 for the section and H3 per stage. Route links are real anchors. Credits in `<bdi>` for RTL. "What to know" is a `<dl>`. |

---

## 4. Phase D: the Abu Simbel prototype

Every sentence of the story is taken from the approved `content/editorial/en/exp-abu.md`. No new facts were added.

| # | Eyebrow | Title | Media |
|---|---|---|---|
| 01 | Before dawn | Leave Aswan in the dark | typographic (dawn) |
| 02 | Arrival | The Great Temple | c gom / Unsplash (tag: Abu Simbel) |
| 03 | The first view | Four colossi of the king | Sofia Cancela / Unsplash |
| 04 | Inside | Into the rock | Dmitrii Zhodzishskii / Unsplash |
| 05 | The highlight | The sanctuary (22 Feb / 22 Oct sun) | Dmitrii Zhodzishskii / Unsplash |
| 06 | The second temple | Nefertari and Hathor | Dmitrii Zhodzishskii / Unsplash |
| 07 | Afterwards | What stays with you (the 1960s rescue) | typographic (dusk) |

- **Feel:**
  - Pace: early start, a very long day, mostly on the road unless you fly.
  - At the temples: a few hours.
  - Setting: exposed forecourt, then carved halls.
  - Crowds: festival days vs. other days.
- **Know:**
  - Location and Duration (meta);
  - getting there;
  - the sun festival;
  - what to bring;
  - photography rules inside.
- **Next step:** Abu Simbel has no live offer, so "Plan My Trip" and "Read the full guide".

**Media provenance.** All 5 photographs were verified earlier (`SEO-GROWTH-IMMERSIVE-PROTOTYPE-REPORT-2026-10-03.md`):

- free Unsplash photos, location-tagged "Abu Simbel" by their photographers;
- documentary photographs, not illustrations (`generated: false`).

There is no video: no verified clip exists. No AI imagery is used.

### Acceptance (Part 18)

| Question | Answered by | Without reading the whole page |
|---|---|---|
| Where am I? | Hero H1 + location | yes |
| What is happening? | Route at a glance (7 named stages) | yes |
| What will I see? | Stages 02–06 (photo + one sentence) | yes |
| What will it feel like? | Feel block (4 items) | yes |
| What is the journey? | Route + stage order (dawn → afterwards) | yes |
| What should I know? | "What to know" (6 items) | yes |
| What can I do next? | Partner offer or Plan My Trip, "Read the full guide" | yes |

It is not "5 photos in a slider": there is no carousel or strip, and it has a sequence, place, time of day, a human expectation and a practical transition.

---

## 5. Phase E: tests (local, release candidate)

| Check | Result |
|---|---|
| axe-core, EN / DE / AR × 320 / 390 / 768 / 1440 | **0 violations** (12 runs) |
| Horizontal overflow, same matrix, also no-JS and reduced motion | **0** (36 runs) |
| No-JS | every stage shows exactly one photograph (`<noscript>`), all text present |
| Reduced motion | `animation-name: none`, layout complete |
| Console / page errors | 0 |
| Heading order (EN) | H1 → H2 (journey) → H3 × 7 → H3 (feel) → H2 (know) → H2 body sections |
| RTL (Arabic 390) | stages, numerals and credits read right to left, no mixing |
| Languages without story text (DE, AR) | 5 photo stages with the approved translated captions. No English, no feel/know. Key facts kept in the sidebar. |
| Other templates | HTML of 10 other pages (7 experiences, Luxor, Cairo guide, …) **identical** to the previous code apart from the build id and the form honeypot timestamp |
| Bundles, PHP lint, editorial check, LF | pass |

### Performance

Same local server, A/B against the committed code. Slow 4G (150 ms, 1.6 Mbps), 4× CPU, 5 runs, medians.

| Abu Simbel EN | Old (strip) | New (Immersion) |
|---|---|---|
| Phone 412: first-view transfer | 373 KB | 375 KB (+2 KB HTML/CSS/JS, 0 image bytes) |
| Phone: FCP / LCP / CLS | 1684 / 2412 / 0 | 1612 / 2412 / 0 |
| Desktop 1440: first-view transfer | 532 KB | 534 KB |
| Desktop: FCP / LCP / CLS | 1724 / 3104 / 0.001 | 1776 / 3192 / 0 (re-run; the first run had two network outliers) |
| Main thread: long tasks during load and full scroll | 0 ms | 0 ms |
| Images after a full scroll, phone 390 | 745 KB (strip: only the photos you swipe to) | 1312 KB (every stage photo is seen) |
| Images after a full scroll, desktop 1440 | 574 KB | 1405 KB → **1193 KB** after the width fix (835 px frames took 1100 px files; 720/960 steps added; measured on production) |

**Trade-off.** First paint is untouched: the hero remains the LCP and the only first-view image. A reader who scrolls the whole journey downloads about 0.6–0.8 MB more than with the strip, because they now actually see each photograph. Each photo is fetched about one screen ahead, never at load.

---

## 6. Phases K–L: deploy and production verification (1.2.37 / 1.2.23)

| Step | Evidence |
|---|---|
| Commit, push | `44d1c05` |
| CI (lint, versions, JSON, editorial, legal, bundles) + deploy | "Deploy production: success", 2026-10-03 17:36 UTC |
| Cache flush | admin bar "Flush Cache" in the owner's Chrome |
| Live build | `/wp-json/egypt-roamer/v1/build` = `da65a2d6636a`, the same in the HTML of Abu Simbel (EN, DE), Valley, Luxor and the homepage. `track.js?ver=1.2.23` |
| Abu Simbel EN | 7 stages, "What to know" present, old strip gone |
| Abu Simbel DE | 5 photo stages, no "What to know" (no story text), Key facts kept |
| Valley, Luxor, homepage | no Immersion, unchanged. The Valley preview is still **unpublished** |
| axe + overflow + no-JS + reduced motion, EN/DE/AR × 4 widths | **0 issues** (36 runs) |
| Sweep (console, failed requests, broken images, preload use), Abu EN/DE/AR, Valley, archives, Aswan × 390/1440 | **0 issues** (12) |
| Production, phone 412: first-view transfer | 375 KB (production before: 371 KB). FCP and LCP are dominated by server noise during the run (TTFB 629–2452 ms); CLS 0 |
| Production, desktop 1440: first-view transfer | 534 KB (local A/B: 532 → 534 KB). CLS 0.003. FCP and LCP are dominated by server noise (TTFB 544–2382 ms) |
| Production, images after a full scroll | phone 390: 1313 KB; desktop 1440: 1193 KB. Long tasks: 0–85 ms |

No rollback was needed.

---

## 7. Phase F: generalising to the other experiences

The component is generic. A new experience needs only a `story` in its preview entry. It is **not** rolled out to the other 7, for two reasons:

1. **No verified media.** Only Abu Simbel has location-verified moment photographs in the released data. Valley's 4 are in the stash, behind the native-review gate. The other six have none:
   - Giza;
   - diving;
   - the White/Black Desert safari;
   - the Cairo food tour;
   - the dinner cruise;
   - Great Sand Sea.

   Part 10 forbids using unverified assets, and the photo policy (approved 2026-10-03) allows only new free photos with an exact Egyptian location tag.
2. **Owner approval.** The other experiences' previews were held until the prototype is approved (decision of 2026-10-03). Giza and Great Sand Sea are also outside this phase's scope.

### Decision needed from the owner

> Is the Abu Simbel Immersion approved as the pattern? If yes, the next step per experience is:
> 1. source 4–6 location-tagged photos (policy above);
> 2. write the story from that experience's approved editorial text;
> 3. QA it;
> 4. deploy it.
>
> Suggested order: Valley (after its native review), White Desert, Red Sea diving, Cairo food, dinner cruise.

**Also pending:** the story text exists only in English. Translating it needs the same native-review gate as the Valley copy.

---

## 8. Phase G: AI search / GEO technical baseline (production, 2026-10-03)

Crawl of every URL in `wp-sitemap.xml`: 181 URLs. Raw data: `geo/crawl.json` (scratch).

**Coverage**

| Type | URLs |
|---|---|
| Destinations | 7 × 8 languages |
| Experiences | 8 × 8 languages |
| English guides | 4, plus the guide archive |
| Homepages | 8 |
| Pages and archives | 48 |

**Signals**

| Signal | Result |
|---|---|
| Status | 181 / 181 return 200 |
| Indexing | `robots: max-image-preview:large` on all, no `X-Robots-Tag`. The site has been public since the 2026-10-02 launch, and the deploy asserts `blog_public = 1`. **CLAUDE.md still says "keep indexing off": that line is out of date.** |
| Crawler access | The same 200 page (same bytes, edge HIT) for a browser, Googlebot, Bingbot, GPTBot, OAI-SearchBot, ChatGPT-User, PerplexityBot and ClaudeBot. `robots.txt` disallows only `/wp-admin/` and `/go/`. No AI crawler is blocked. |
| Canonical | 181 / 181 self-canonical |
| hreflang | 25–26 links per translated page; 16 on English-only guides |
| H1 | exactly 1 on every page |
| Meta description | present on all |
| Semantic HTML | `main`, one `article` per page, H2 sections, `details` FAQ, `dl` facts |
| Structured data | Home: Organization + WebSite. Destinations: TouristDestination + BreadcrumbList. **Experiences: BreadcrumbList only.** Guides: Article (dates, author and publisher = Egypt Roamer) + BreadcrumbList |
| Freshness | `dateModified` only on guides (4). Destinations and experiences expose no dates. Their real `post_modified` values track content operations (editorial import 09-29, slug fix 10-02), so they are honest. |
| Author / publisher | Guides: byline "By Egypt Roamer" + Organization in Article. Other types: none |
| FAQ | `details` FAQ on every destination and experience, 3 of 4 guides. No FAQPage schema (deliberately not added: Google limits FAQ rich results, and the mission says not to add FAQs for their own sake) |
| Internal links (in `<main>`) | Every content page has ≥ 3 in-content inbound links (experiences median 4, destinations 8, guides 8). Only contact and cookie pages rely on the footer. No orphans. |
| Answer-first | Guides open with direct answers ("The short answer", "The plan at a glance"). Experience heroes summarise the page. **Destination heroes are taglines** ("The world's greatest open-air museum"), so the direct answer (where, how long, when) sits lower in the body. |
| Tables | none in content (the itinerary and best-time guides would suit one) |

---

## 9. Phase H: query dataset and topic authority

The dataset (110 queries, 8 languages, mapped to pages) is in `AI-QUERY-DATASET-2026-10-03.md`.

### Topic graph: coverage today

| Topic | Pillar | Supporting | Gap |
|---|---|---|---|
| Cairo | Destination (2170 words) + Cairo guide | Giza private tour, street food, dinner cruise | none |
| Giza | none (inside Cairo + Giza experience) | Giza experience | **Missing pillar:** Giza / pyramids planning page |
| Luxor | Destination | Valley + Hatshepsut | Karnak, balloon, West Bank: sections only |
| Aswan | Destination | Abu Simbel | Philae, felucca, Nubian village: sections only |
| Abu Simbel | Experience (now Immersion) | Aswan | none |
| Siwa | Destination | Great Sand Sea 4×4 | none |
| White Desert | Experience only | none | **Missing pillar:** Western Desert / Bahariya |
| Hurghada | Destination | Red Sea diving | **Snorkeling** answered only inside sections |
| Sharm / Sinai | Destination (Mount Sinai section) | Diving (also Sharm) | **Mount Sinai** experience/guide missing; **Dahab** section only |
| Nile | Dinner cruise (Cairo) | Luxor and Aswan sections | **Missing pillar:** Luxor–Aswan Nile cruise |
| Itineraries | 7 days | none | 10/14-day, Cairo + Red Sea |
| Planning | Best time | none | **Missing:** visa / entry, money, safety, getting around, etiquette and dress (need sources) |
| Experiences | 8 pages | none | family, couples and food guides |

---

## 10. Phase I: GEO improvement implemented (Core 1.2.24)

A WebPage + TouristTrip graph on destinations and experiences, built only from what the page shows (`includes/seo.php`).

- **Every indexable destination and experience:** a `WebPage` node with `@id`, name, description, `inLanguage`, `datePublished` and `dateModified` (the real post dates), `isPartOf` the WebSite and `publisher` the Organization (same `@id`s as the homepage graph). Its `about` points to the page's subject.
- **Destinations:** the existing TouristDestination gains `@id …#place` (the `about` target). Nothing else changes.
- **Experiences:** a `TouristTrip` with the visible title and summary, the URL and an `itinerary` listing the "Where it happens" destinations (name + URL, in the page's language).
- **No offers, prices, ratings or provider.** The site sells nothing, and partner terms are the partners'.
- **Gating:** only when no SEO plugin is active and the page is "Ready to index", the same as the existing schema.

**Production (after deploy and flush):**
- Live build `342a846d9b2b`, `track.js?ver=1.2.24`.
- Full re-crawl of the 181 sitemap URLs:
  - 56 / 56 destinations: TouristDestination + WebPage + BreadcrumbList;
  - 64 / 64 experiences: WebPage + TouristTrip + BreadcrumbList;
  - guides, homepages and other pages unchanged;
  - 0 invalid JSON-LD; all 200.
- Itineraries: 64 TouristTrips, 0 language mismatches. 8 have no itinerary: the White Desert safari in 8 languages, which links no destination, so none is invented.

**Local test:**
- EN experience: WebPage + TouristTrip (itinerary: Aswan).
- DE experience: German name and description, itinerary "Assuan" → `/de/destinations/assuan/`.
- Luxor: TouristDestination `#place` + WebPage `about #place`.
- Guides and homepage: unchanged.

---

## 11. Prioritized GEO roadmap

| # | Action | Impact | Owner / effort | Status |
|---|---|---|---|---|
| 1 | WebPage / TouristTrip graph with real dates and publisher | Medium: entity, freshness and publisher signals on 120 pages | done (Core 1.2.24) | **done** |
| 2 | **Bing Webmaster Tools:** verify the site, submit the sitemap, enable IndexNow; then read its AI Performance report. Bing currently returns no Egypt Roamer result (§12), and Bing's index feeds Copilot and is used by other AI search engines | High | owner (account); 15 min | open |
| 3 | Answer-first summary on destinations: "Luxor in brief" (where, how many days, best time, getting there, who it suits) from the approved text, as a short list under the hero | High: the most-asked questions extracted from the first screen | editorial (EN), then the translation gate | open |
| 4 | Pillar pages from §9: Luxor–Aswan Nile cruise, Giza, Western Desert / Bahariya, Mount Sinai, Red Sea snorkeling, Egypt planning essentials (visa, money, safety, getting around: **sources required**) | High: covers the largest gaps in the query set | editorial, one page per request, sources in `CONTENT-SOURCES.md` | open |
| 5 | Translate the 4 guides (Arabic first) after English approval | High outside English (guides are EN-only; 0 non-English planning pages) | translation + native gate | open (existing plan) |
| 6 | Tables where they answer better: best-time month × region, 7-day plan at a glance | Medium | editorial | open |
| 7 | Visible "Updated" date on destinations and experiences (as guides have) | Low–medium | theme, small | open |
| 8 | Monthly AI-visibility benchmark (query set, 4 platforms) once indexed | measurement | owner accounts or an approved tool | open |
| — | FAQPage schema, `llms.txt`, mass AI articles | low or negative | not recommended | rejected |

---

## 12. Phase J: benchmark (2026-10-03)

| Platform | Result |
|---|---|
| Bing (web, unauthenticated, built-in browser) | `site:egyptroamer.com` and `"egyptroamer.com"` return no Egypt Roamer URL (only unrelated results). **Not indexed yet.** |
| Google Search / AI Overviews | **NOT TESTED**: Google answered with a CAPTCHA ("unusual traffic"), which is not bypassed |
| ChatGPT Search | **NOT TESTED**: needs the owner's account (queries would be sent as the owner) |
| Perplexity | **NOT TESTED**: same |
| Copilot | **NOT TESTED**: same |
| Bing Webmaster AI Performance | **NOT AVAILABLE**: the site is not verified in Bing Webmaster Tools (roadmap #2) |

The site went public on 2026-10-02. Not being cited after one day is the expected baseline, not a finding about content quality.

---

## 13. Remaining issues

- **Story language:** English only. DE / FR / IT / ES / RU / ZH / AR show the translated photo sequence without feel/know until the story is translated and native-reviewed.
- **Bandwidth:** a full scroll of the journey costs about 0.6–0.8 MB more images than the strip did. First view is unchanged.
- **Previous-release residuals:**
  - Arabic desktop CLS ≈ 0.05 (font swap);
  - the font-preload first-paint trade-off (see `GUIDE-AND-VALLEY-ROLLOUT-REPORT-2026-10-03.md`).
- **Stash:** `stash@{0}` (V1.1: editor fields, Valley data, native QA package) is still unreleased by design.
- **CLAUDE.md:** "Keep indexing off" is outdated. The site is public, and the deploy asserts it. This is a documentation fix for the owner.
