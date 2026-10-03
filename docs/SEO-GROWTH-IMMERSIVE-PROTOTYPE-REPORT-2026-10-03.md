# SEO growth + immersive preview prototype — 2026-10-03

Release: **theme 1.2.35 / Core 1.2.21** (commit 77de06b). CI tests pass on PHP 8.1 and 8.3, the deploy succeeded, and the owner ran `wp egypt-roamer editorial` on production. The cache was flushed and every edge copy carries the live build (`750d3de98805`).

Labels: **OBSERVED** means measured or seen. **INFERRED** means reasoned from observations. **RECOMMENDED** means a proposal, not done.

## Summary

| Area | Status |
|---|---|
| 1. Internal links | PASS. Live on production; the experience → experience gap is closed for 4 of 8 experiences |
| 2. Guide archive language switcher | PASS. link-noindex warnings 14 → 0 |
| 3. Siwa photo | PASS. Replaced by a Shali fortress photo tagged "Siwa, Egypt" |
| 4–7. Experience preview prototype | PASS WITH NOTE. Abu Simbel only. Photo story live; video support built but unused (no verified clip) |
| 8. Performance | PASS. No first-load regression after a fix (the first version had one) |
| 9–12. Accessibility, responsive, multilingual, visual | PASS. 8 new UI strings need a native check |
| Rollout to the other 7 experiences | DEFERRED until you approve |

---

## 1. Internal-link changes

**How it was done (OBSERVED).** Everything goes through the existing editorial pipeline:

- The front matter of `content/editorial/en/*.md` gained three relation keys (`destination`, `related`, `alternatives`).
  - `tools/editorial.py` compiles them into `index.json`.
  - `wp egypt-roamer editorial` adds the missing ones on the English original. It also adds them to any translation that keeps its own copy (experience translations copy their destinations).
  - **Nothing is ever removed.**
- Relation lines are left out of the translation fingerprint, so `editorial.py status` still shows **105/105 translations approved and current**.
- No URL, slug, sitemap, canonical or hreflang code was touched.

**Changes:**

| Item | Change |
|---|---|
| 7 days in Egypt | destinations Cairo, Luxor, Aswan. Related: Giza tour, Valley of the Kings, Abu Simbel. Body links on words already in the text: "the pyramids and the Sphinx", "Valley of the Kings, Hatshepsut's temple", "pre-dawn trip to Abu Simbel", "Hurghada", "Alexandria", "Siwa and the Western Desert" |
| Best time to visit | destinations: all 7. Related: Abu Simbel, White Desert safari. Body links: "Sharm El Sheikh", "Siwa", "the White Desert", "Alexandria", "Abu Simbel" (sun festival) |
| Hidden gems | destinations Cairo, Luxor, Aswan, Alexandria, Siwa. Related: Giza, Valley of the Kings. Body links: "Giza" (Dahshur entry), "a West Bank day" |
| Cairo guide | related: Giza, dinner cruise, food tour. Body link: "the pyramids" |
| Red Sea Diving | + destination Sharm El Sheikh (the text says "from Hurghada or Sharm El Sheikh"), in all 8 languages |
| Alternatives | dinner cruise ↔ food tour (two Cairo evenings), White & Black Desert safari ↔ Great Sand Sea 4×4 (two desert trips), in all 8 languages |

No experience body or translated text was changed. Body links were only added to the 4 English guides, which have no translations. Every link is on words that were already there.

**Production results (OBSERVED, crawl of 2026-10-03).** In-content inlinks from other indexable English pages (header, footer and nav excluded):

| Page | Before | After | From experiences (before → after) | From guides (before → after) |
|---|---|---|---|---|
| Sharm El Sheikh | 4 | 5 | 1 → 1 | 0 → 1 |
| Alexandria | 9 | 11 | 0 → 0 | 1 → 3 |
| Siwa | 12 | 14 | 2 → 2 | 1 → 3 |
| Hurghada | 7 | 8 | 1 → 1 | 1 → 2 |
| Giza tour | 3 | 6 | 0 → 0 | 0 → 3 |
| Abu Simbel | 3 | 5 | 0 → 0 | 0 → 2 |
| Valley of the Kings | 3 | 5 | 0 → 0 | 0 → 2 |
| Dinner cruise / food tour | 4 / 4 | 5 / 5 | 0 → 1 / 0 → 1 | 1 → 1 / 1 → 1 |
| White Desert / Great Sand Sea | 3 / 3 | 5 / 4 | 0 → 1 / 0 → 1 | 0 → 1 / 0 → 0 |
| Red Sea Diving | 4 | 4 | 0 → 0 | 0 → 0 |

On the destination pages (all 8 languages, OBSERVED):
- Sharm now lists diving under "Things to do", for example the German page links `tauchen-im-roten-meer-…`.
- Every English destination page shows "Plan your trip to …" with the guides.

Spot-checked on production: the body links in the 7-days, best-time and hidden-gems guides, Red Sea Diving's "Where it happens" (Hurghada and Sharm), and the guide pages' "Experiences in this guide" and "Destinations in this guide".

**INFERRED:**
- Sharm stays the least connected page, because Sinai has no second experience or guide.
- Red Sea Diving is the only experience with no new inlink: no guide mentions diving.
- Both gaps need new content, not new links.

## 2. Guide archive language switcher

**OBSERVED.**
- `er_languages()` used to point every language at its own archive. Now it does so only where that archive has a published item in that language; otherwise it keeps Polylang's homepage link.
- On production `/guides/` now links en → `/guides/` and de/fr/it/es/ru/zh/ar → `/de/` … `/ar/`.
- `/destinations/` and `/experiences/` still link to `/de/destinations/` and so on, in all 8 languages.
- Crawl: **link-noindex 14 → 0**.
- No archive was published. No indexing rule, robots tag or sitemap changed. The empty archives stay noindex.
- The same rule covers the empty tour/activity archives, which now also send visitors to the homepages.

## 3. Siwa image decision

**OBSERVED.**
- The old photo `1640341742389` had no location tag.
- A search found 46 free (non-Plus) photos tagged Siwa.
- The replacement is [`upSQtGaatd8`](https://unsplash.com/photos/upSQtGaatd8): CDN id `1771236469626-323bb7a34bac`, by waa towaw, 4016×6016 portrait, location "Siwa, エジプト" (Siwa, Egypt).
  - It shows Shali fortress and the mud-brick old town, which are named in the destination's highlights.
  - It's a portrait original like the old photo, so the phone crops lose no detail.
- Checked at 390, 768, 1280 and 1440 (destination hero and destinations card): Shali stays centred and recognisable in every crop.
- Delivered files are ≥ 1.78× the displayed size.
- The only change is one id in `data/seed.json`, and the old id no longer appears on the page.

## 4. Prototype experience selected

**Abu Simbel Day Trip from Aswan.**

| Criterion | Abu Simbel | White & Black Desert safari | Others |
|---|---|---|---|
| Verified imagery (OBSERVED: Unsplash search plus location tags of free photos) | 16 photos tagged "Abu Simbel"; several from one photographer with an explicit description | 9 tagged photos, all "Bahariya Oasis" or "Farafra"; none tagged White Desert, Black Desert or Crystal Mountain | Not searched in this pass |
| Visual sequence | Approach → façade → colossi → halls → sanctuary → Nefertari's temple, all in the approved text | Strong in principle (4×4 → Black Desert → White Desert → camp), but the stages can't be verified photo by photo | — |
| Location confidence | A unique, unmistakable monument | Lower: the tags name the oasis, not the sites | INFERRED: diving photos are rarely location-tagged; Cairo street scenes are mixed |
| Appeal | One of the most famous images of Egypt (approved text) | High | — |

The choice was not based on affiliate potential: no offer is live anywhere.

## 5. Media source and provenance

All 5 are free Unsplash photos (not Plus or premium). Each has asset type "photo" and real camera EXIF. They are hot-linked through the same pipeline as the other stand-ins: nothing downloaded and nothing in the Media Library. Each is credited under the photo with a link to its source (`rel="nofollow noopener"`).

| # | Photo | Photographer | Location tag | Camera | Shows |
|---|---|---|---|---|---|
| 1 | [sdOQl33RPLU](https://unsplash.com/photos/sdOQl33RPLU) | c gom | Abu Simbel, Égypte (22.112, 31.674) | Nikon Z 8 | Great Temple façade with visitors |
| 2 | [xLCogBnMjSg](https://unsplash.com/photos/xLCogBnMjSg) | Sofia Cancela | Abu Simbel, Egypt (22.361, 31.610) | Samsung SM-G900F | Colossi seen from below |
| 3 | [cOihXsrJFRc](https://unsplash.com/photos/cOihXsrJFRc) | Dmitrii Zhodzishskii | Abu Simbel; description "The Great Temple of Ramesses II" | Sony ILCE-7C | Pillared hall with statues of the king as a god |
| 4 | [BoOebId7nJM](https://unsplash.com/photos/BoOebId7nJM) | Dmitrii Zhodzishskii | as above | Sony ILCE-7C | Seated statues in the sanctuary |
| 5 | [Kc2LMhBBT78](https://unsplash.com/photos/Kc2LMhBBT78) | Dmitrii Zhodzishskii | Abu Simbel; description "the Small Temple of Hathor and Nefertari" | Sony ILCE-7C | Small temple façade |

**Captions only repeat the approved text** of `exp-abu.md`: four colossal seated statues, the 1960s rescue as Lake Nasser rose, carved halls with statues of the king as a god, the 22 February / 22 October sun alignment, and the temple of Nefertari and Hathor. The sanctuary caption describes the yearly event. It does not claim the photo shows it. No generated media was used.

## 6. Video implementation

**Status: built, unused. No verified clip exists in an approved source.** The approved photo source has no video. A new provider is a third-party source that needs your approval, so no stock clip was used.

**Data model (section 13 of the brief, OBSERVED).**
- The experience post type could hold these fields as registered meta (the Core field system supports text, URL, image and line fields), with captions on each translation.
- For the prototype the entry is kept in Core `data/previews.json`, keyed by seed id like the stock photos in `seed.json`:
  - media is shared by all languages;
  - text is per language;
  - each item records `source`, `photographer`, `location_tag` and `generated`;
  - `video` holds `{mp4, webm, poster, duration, captions{lang}, generated}`.
- No table, post meta, plugin or SaaS.
- Reading is done by `er_preview_for()` in `includes/previews.php`, with an `er_preview` filter.

**Player behaviour (OBSERVED in local tests with a generated 6-second test pattern, never deployed):**
- Poster first; the video element is created only when Play is pressed.
- **0 video requests before that.**
- Muted, `playsinline`, native controls, `preload="none"`.
- Captions track loads (1 track).
- Pause works.
- A failed source shows "The video could not be loaded.", brings the poster back and returns focus to the button.
- Media marked `generated` gets an "Illustration, not footage of the place" label on the image.
- Frame is 4:5 on phones and 3:2 from 700 px.
- No autoplay, no background video, no framework.

## 7. Image story implementation

**OBSERVED.**
- Template `template-parts/experience-preview.php` is inserted by `single-commercial.php` after the body's first section ("What it is"), before the practical sections. Order: hero → key facts → introduction → **preview** → why it's worth it / road or air / sun festival / FAQ → partner box or Plan My Trip.
- Content: eyebrow "Experience preview", title "Abu Simbel, moment by moment", a one-line intro, and 5 numbered moments (photo, title, caption, credit).
- Horizontal strip with scroll snapping:
  - **Phone:** runs to the screen edges, with the next photo peeking in.
  - **Desktop:** 78% wide items inside the reading column.
  - **Controls:** previous/next buttons (44 px), progress bars and a counter ("3 of 5", localized).
- Captions sit under the photos, never over them. Colours and fonts are the site's own (gold progress, clay labels, Playfair titles, Inter text).
- No-JS: the strip scrolls natively and the photos come from `<noscript>`.
- JS: `src/js/components/moments.js`, added to `site.js` (+1–2 KB after compression).
- Images use the existing crop pipeline: 4:5 crops at 480/640/828 for phones and 3:2 crops at 600/900/1200 above that, delivered at ≥ 1.05× the displayed size up to 2×.

## 8. Performance before / after

Harness: `perf2` with 412×823 @1.75, Slow 4G (150 ms, 1.6 Mbit/s), 4× CPU, cold cache, median of 5.

**First attempt (OBSERVED, fixed before deploy).** The preview sits near the top of the page, so Chrome's native lazy loading fetched all 5 photos with the page. That added **+340 KB of images and +0.9 s LCP** (1896 → 2784 ms). The photos now load through JavaScript only when they come within 200 px of the screen, or when they're next in the strip.

**Local, the same page and harness (OBSERVED):**

| Abu Simbel | FCP ms | LCP ms | TBT | CLS | Total KB | Doc | JS | Images KB |
|---|---|---|---|---|---|---|---|---|
| EN before | 1344 | 1896 | 0 | 0 | 231 | 19 | 18 | 171 |
| EN after | 1424 | 1892 | 0 | 0 | 235 | 21 | 19 | 171 |
| EN delta | +80 | −4 | 0 | 0 | **+4** | +2 | +1 | **0** |
| AR before | 1500 | 1908 | 0 | 0 | 241 | 22 | 24 | 171 |
| AR after | 1472 | 1860 | 0 | 0 | 246 | 25 | 26 | 171 |
| AR delta | −28 | −48 | 0 | 0 | **+5** | +3 | +2 | **0** |

The FCP/LCP deltas are within run-to-run noise: the same page with the module switched off measured LCP 1816 / 1912. Local fonts don't load (a `localhost` vs `127.0.0.1` cross-origin issue in the test setup), so font bytes are 0 locally.

**Production (OBSERVED):**

| Abu Simbel EN | TTFB | FCP | LCP | TBT | CLS | Total KB | Images KB | Fonts KB |
|---|---|---|---|---|---|---|---|---|
| Before (1.2.34) | — | 1520 | 2740 (2284–6272) | 0 | 0.003 | 368 | 172 | 139 |
| After, round 1 | 450 | 1340 | 2404 | 0 | 0.004 | 372 | 171 | 139 |
| After, round 2 | 436 | 1288 | 2368 | 0 | 0.003 | 372 | 171 | 139 |

- The Arabic page went from 498 KB before to 503 KB after.
- A first AFTER run right after the cache flush was slow (FCP 3272) on every page. A rerun with TTFB recorded, interleaved with two control pages, gave the figures above. The control pages' timings moved the same way. INFERRED: server warm-up after the flush, not the module.

**Preview photo bytes by visitor intent (OBSERVED, local):**

| Screen | On load | When the visitor reaches the module (2 photos) | Whole strip (5) |
|---|---|---|---|
| Phone 390 @1.75× | 0 KB | 170 KB | 451 KB |
| Phone 390 @3× | 0 KB | 277 KB | 738 KB |
| Desktop 1280 @1× | 0 KB | 96 KB | 235 KB |
| Desktop 1440 @2× | 0 KB | 340 KB | 832 KB |

**Video bytes: 0** (no clip).

**Budget, now met (RECOMMENDED for the rollout):**
- 0 preview bytes on first load;
- ≤ 2 photos when the module is reached;
- ≤ 175 KB per photo;
- no change to LCP, CLS or TBT;
- any clip ≤ 2.5 MB on phones and ≤ 5 MB on desktop, fetched only on Play.

**Slow-network check (400 kbit/s, scrolled to the module at 4 s), OBSERVED:**
- Frames keep their size while photos arrive, on the site's sand-coloured placeholder.
- Before the loading fix, CLS was 0.038. The cause was a web-font swap in text above the module, delayed because the photos competed for bandwidth.
- After the fix: **CLS 0.0025**.
- Two labels in the module were also given fixed line heights.

## 9. Accessibility results

**OBSERVED:**
- **axe: 0 violations** on the Abu Simbel page and on the module alone, in all 8 languages at 390 and 1440.
  - The first run found one: the gold moment numbers were 3.07:1. They now use the eyebrow's clay colour.
- **State tests 12/12:**
  - **Keyboard, EN and AR:** the strip is focusable with a visible ring; arrow keys scroll it (RTL too); Tab reaches the credits, then Previous and Next; Enter and Space advance by one.
  - **Reduced motion:** instant jumps, no smooth scrolling.
  - **Names:** the section is labelled by its title; the strip is labelled; 5 alt texts; named buttons; a polite live counter; decorative bars and numbers hidden from screen readers.
  - **Other states:** no-JS, slow network, video play / failure / illustration label, no-video fallback, logged-in toolbar.
  - **Assistant launcher:** at the normal reading position, neither the assistant button nor the dock covers the Previous/Next buttons. With the buttons at the very bottom of the screen the dock covers them, as it does for any content there.
- **Regression suite (local):**
  - responsive: 1,232 checks, 0 bad;
  - display-fit and mobile-first view: OK in 8 languages / 40 views;
  - journey nav, overlays and keyboard: OK;
  - axe on the 22 standing pages: 0.
- **Stale-page guard:** 6 of 7 scenarios pass. Scenario F (an editor saving a page) stops at the local `wp eval` step: an SQLite lock in this environment. Same as last phase; not run.

## 10. Responsive results

**OBSERVED: 72 of 72 combinations pass** (9 widths: 320, 360, 390, 430, 768, 1024, 1280, 1440, 1920 × 8 languages). Checks per combination:
- no page overflow (before and after walking the strip);
- strip inside the viewport;
- correct text direction;
- controls visible;
- counter walks 1 → 5 → 4 with Next disabled at the end;
- last photo fully in view at the end;
- no clipped text;
- buttons ≥ 44 px;
- no JS errors;
- photo detail ≥ 1.05× the displayed size (up to 2×).

On production the same check passed at 390, 768, 1280 and 1440 (EN) and at 390 (AR).

## 11. Multilingual results

**OBSERVED:**
- **Shared media, translated text.** The media is the same for all languages; only text is translated.
- **Captions** come from the approved translated sentences of `exp-abu` wherever they exist. For example, the "Into the rock" caption is the approved sentence in all 8 languages.
- **Composed text.** Title, intro, short moment titles and alt texts were written for this module in 7 languages. They are flagged for a native check in the `previews.json` note.
- **8 new UI strings in 7 languages** went through `tools/i18n/new-strings.json` (282/282 strings per language). The file's existing note marks them unreviewed:
  - "Experience preview";
  - "Previous moment" / "Next moment";
  - "{n} of {total}";
  - "Photo: {name} / Unsplash";
  - "Play the video preview";
  - "The video could not be loaded.";
  - "Illustration, not footage of the place".
- **Arabic:** RTL strip; the first photo sits on the right and the next peeks in from the left; arrows mirror. The credit isolates "name / Unsplash" with `<bdi>`, so it reads "تصوير: c gom / Unsplash".
- **Chinese:** full-width punctuation; counter "第 1 张，共 5 张".
- **German, French, Russian:** long titles wrap cleanly at 320 px (no clipping in the matrix).

## 12. Visual QA

Screenshots reviewed: Arabic 320, German 768, Russian 360, Chinese 1920, English 390 and 1280, plus every state screenshot and production at 390 and 1440.

Fixed during review (OBSERVED):
- the prose's decimal list markers showing ("2." before "02");
- the Arabic credit reordering;
- the illustration label colliding with the play chip (it moved to the top);
- low-contrast numbers;
- two labels whose height changed when the web font loaded.

Crops: the Great Temple, the colossi, the halls, the sanctuary and Nefertari's temple all read well at 4:5 and 3:2. No important part is covered: captions are outside the photos. The phone strip shows the next photo's edge as a cue to swipe.

## 13. Remaining risks

| Risk | Status |
|---|---|
| 8 UI strings and the preview's titles, intro and alt texts are not native-reviewed | NEEDS REVIEW |
| Preview photos are hot-linked from Unsplash: if a photographer deletes one, that moment shows the sand placeholder | Accepted stand-in policy (2026-09-28). RECOMMENDED: replace with owned photography later |
| Walking the whole strip on a 3× phone costs up to ~740 KB | Only on intent. Acceptable under the budget in §8 |
| Valley of the Kings page: **CLS 0.177** on production and locally. The long hero title is resized by the existing title-fit script after first paint (OBSERVED attribution: hero title, intro and body move 27–52 px) | **Pre-existing, not caused by this release** (it touched no hero code or CSS). RECOMMENDED as a separate fix |
| Preview text lives in a versioned JSON file, not the editor | Fine for one experience. RECOMMENDED: move to registered meta before the rollout |
| Stale-page scenario F not run locally | Environment (SQLite lock). Unchanged since last phase |
| Production timings swing after cache flushes | Measure with TTFB recorded (done here) |

## 14. Recommendation for the other 7 experiences

**Visual impact (INFERRED from review):**
- It answers "what does this feel like?" without leaving the page or changing the design.
- The strongest moments are the ones visitors can't picture from the hero: the interior halls and the sanctuary.

**User value:**
- It sits after the introduction, so it supports the decision rather than replacing the text.
- Credits and location tags make the photos trustworthy.

**Performance:** zero first-load cost. Photos load only on reach.

**Complexity:** for each new experience, one JSON entry (5 photos plus text in 8 languages). No code change.

**Media sourcing per experience (INFERRED; only Abu Simbel and the White Desert were searched):**

| Experience | Likely source | Confidence |
|---|---|---|
| Valley of the Kings & Hatshepsut | Unsplash, tagged "Valley of the Kings", "Deir el-Bahari", "Luxor". Note: photography rules in tombs | High |
| Pyramids of Giza | Plentiful Giza tags | High |
| Nile dinner cruise | Few tagged night-boat photos. Shows vary by boat | Medium–low |
| Cairo street food | People and food photos, often untagged or unattributed | Low: needs owned photography |
| White & Black Desert | Tags name only Bahariya/Farafra; the stages can't be verified | Medium: a photographer's own descriptions or owned photos needed |
| Great Sand Sea 4×4 | Several Siwa-tagged dune and 4×4 photos (seen during the Siwa search) | Medium–high |
| Red Sea Diving | Underwater photos rarely carry a location | Low: owned or partner-verified photography needed |

**Future production cost:**
- Stand-in photo stories: about 1–2 hours per experience (search, verify, 8-language text, QA).
- Real video means either owned footage (a shoot or licensed footage, a cost to approve) or a partner's footage with rights confirmed. Generated video is allowed only as a labelled illustration, never as the place itself.

**RECOMMENDED next steps, in order, after your approval of this prototype:**
1. Native review of the new strings and preview text.
2. Valley of the Kings, then Giza and the Great Sand Sea: high-confidence photo stories under the same budget.
3. Move preview text to editor fields.
4. Decide on a video source before building any clip.
5. Leave food, diving and the dinner cruise until owned or verified photography exists.

The rollout is **not started**: the other 7 experiences have no preview data and show nothing new.
