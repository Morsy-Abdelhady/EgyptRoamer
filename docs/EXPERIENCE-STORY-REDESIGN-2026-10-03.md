# Abu Simbel: one experience story (2026-10-03)

**Release:** theme 1.2.39 / Core 1.2.25, commit `dafe593`, live build `7b7779a17866`. **Verdict: ACCEPTED AND DEPLOYED** (Abu Simbel only).

**Scope:** Abu Simbel only, in all 8 languages. The other seven experiences, Giza, Great Sand Sea and the Valley preview are untouched (§6).

## 1. The problem

1.2.37 put the immersive journey on top of the old experience layout. The page told its story twice:

- **First:** the journey, then "What it feels like" and "What to know".
- **Then:** the same body again, with section tabs, a sidebar with facts and help, a 1-card "Where it happens" and a half-empty "Plan it with our guides".

It read as two pages joined together.

## 2. One narrative: where every approved section now lives

The page is built from its own approved body, split by section anchor (`er_body_parts()`). The story data (`previews.json` → `er_preview_story()`) only arranges it.

| Approved section | Its one home now |
|---|---|
| Hero (H1, summary, Aswan · Full day) | unchanged: answers **what is this?** |
| What it is | **Opening** (the statement "From Aswan in the dark to the sanctuary of Ramesses II" + this paragraph): answers **why it matters**, with the route at a glance |
| Why it's worth the effort: Scale | Journey **02 Arrival**: the Great Temple reveal |
| Why it's worth the effort: The interiors | Journey **04 Inside** |
| Why it's worth the effort: The rescue | Journey **07 Afterwards**: the one emotional conclusion ("What stays with you") |
| The sun festival | Journey **05 The highlight**: the sanctuary, the peak |
| Who it suits | Chapter **01** (experience fit) |
| Getting there: road or air + "Plan the day" | Chapter **02** (practical planning) |
| Combine it with + Where it happens + guides | Chapter **03**: the text and **one** row of 3 cards (Aswan, Best time, 7 days) |
| Next step | Dark band: the partner offer with disclosure when live; otherwise chat + Plan My Trip (affiliate-only, no booking) |
| Frequently asked questions | Chapter **04**, last |

**Removed because each repeated something above:**

- "What it feels like" (4 items);
- "What to know" (6 items);
- the separate lede;
- the tabs;
- the sidebar Key facts and help box;
- "Where it happens";
- "Plan it with our guides".

**Verified:** all 30 sentences of the approved English text appear **exactly once** on the page. "A few hours" appears twice only because the approved text itself says it in "Getting there" and in an FAQ answer.

**Old links still land:** the anchors `#what-it-is`, `#why`, `#sun-festival`, `#who-it-suits`, `#road-or-air`, `#combine` and `#faq` all still exist.

**Other languages:**
- They get the same structure from their own approved translations: German "Worum es geht / Für wen es sich eignet …", Arabic "ما هي هذه التجربة …", RTL correct.
- Only the English stage titles and eyebrows are new structural labels. Other languages use their approved photo titles.
- The dawn pause has no translation, so it is left out of those languages.
- No language shows English.

## 3. The journey's rhythm (open → build → reveal → peak → aftermath)

| # | Treatment | Phone | Desktop |
|---|---|---|---|
| 01 Before dawn | dark pause, the light of the hour as a gradient (no stand-in photo) | short centred panel | centred, 66vh |
| 02 Arrival: the Great Temple | **reveal**: the strongest photograph, edge to edge, fading into the dark where its title begins | full-width 4:5 | full-bleed 88vh band, title 5.6rem over the fade |
| 03 Looking up: the colossi | **detail** beat, inset, smaller | 78% width, inset left | 4 of 12 columns, text beside |
| 04 Inside | **split**: photo held while the text passes | full width | 58/42, sticky photo |
| 05 The highlight: the sanctuary | **peak**: centred in the dark, lit from within (gold glow) | 86% centred | 30rem centred, large title |
| 06 The second temple | **detail**, mirrored | inset right | columns mirrored |
| 07 Afterwards | dark closing beat (dusk), the rescue | centred | centred |

**Motion:** scroll-driven CSS only (photos settle; the reveal scales from 1.16). There is none under reduced motion, and the page is complete without JavaScript.

## 4. Acceptance test

| Question | Answer |
|---|---|
| 1. One coherent page? | **Yes.** One sequence from hero to FAQ. The light ground arrives as numbered chapters in the journey's own type, not as an article template |
| 2. Is the journey the core story? | **Yes.** The body's own "why" and "sun festival" sections now live inside it |
| 3. Old repetition eliminated? | **Yes.** Each approved sentence appears exactly once (§2) |
| 4. Clear purpose per section? | **Yes.** Opening = why it matters; journey = what it's like; chapters = fit, plan, combine; band = next step; FAQ |
| 5. Consolidated, not duplicated? | **Yes.** Three card sections and the sidebar are merged into one row of cards. Location and duration appear once (hero) |
| 6. Rhythm changes? | **Yes.** Five different treatments across seven stages (§3) |
| 7. Strongest moment strongest? | **Yes.** The Great Temple is the only full-bleed image, larger than every other stage. The peak has its own centred treatment |
| 8. Premium / editorial? | **Yes.** Numerals, serif titles and hairlines. No card grid, slider or badges |
| 9. Different from a generic tourism site? | **Yes.** No booking UI, prices or ratings. A film-like sequence instead of a listing |
| 10. Memorable? | **Yes.** Dark → reveal → sanctuary → dusk is a sequence, not a gallery |

## 5. QA (local release candidate)

**Accessibility and modes**
- axe: EN / DE / AR × 320 / 390 / 768 / 1440 = **0 violations**.
- Overflow, no-JS and reduced-motion runs (36 in total, same matrix): **0 issues**. Without JS every stage shows its photo; reduced motion disables the animation.

**Visual**
- Section-by-section screenshots at 1440 and 390 (EN) and 390 (AR, RTL), all reviewed.
- Fixed during review:
  - a CSS name collision with the homepage `.story` component, which turned links into grids (wrapper renamed `exp-story`);
  - prose list styles leaking into the cards (cards moved out of `.prose`);
  - a mask that painted nothing (replaced by a gradient overlay);
  - hero width (the full-width hero restored: 815 → 703 px).

**Performance** (throttled: Slow 4G, 4× CPU, medians)

| | Phone 412 | Desktop 1440 |
|---|---|---|
| First-view transfer | 375 KB (before: 373–375) | 534 KB (before: 532–534) |
| FCP / LCP | 1728 / 2668 (TTFB 510; before 1612–1684 / 2412) | 1792 / 3228 (before 1724–1776 / 3104–3192) |
| CLS | 0 | 0 |
| Long tasks | 0 ms | 0 ms |
| Images after a full scroll | **1109 KB** (1.2.37: 1313) | **899 KB** (1.2.37: 1193) |

The hero remains the LCP and the only first-view image.

**Note:** the first request for each new crop size took Unsplash up to 32 s to render (cold CDN). After that, the same file arrived in about 1.5 s.

## 6. Scope verification

- **HTML comparison** against the pre-immersion baseline: the 7 other experiences, Luxor and the Cairo guide are **identical** apart from the form honeypot timestamp and the build id.
- **Template path:** experiences without a story render through the same code path as 1.2.36.
- **Removed:** `template-parts/experience-immersion.php`.
- **Unchanged:** Giza, Great Sand Sea and the Valley preview data (still in `stash@{0}`, unpublished).

## 7. Production (verified 2026-10-03)

**Deploy**
- CI and deploy for `dafe593` passed (19:29 UTC); the cache was flushed via the admin bar.
- Live build `7b7779a17866`, `track.js?ver=1.2.25`, the same at the edge on every sampled page.

**Structure**

| Page | Result |
|---|---|
| Abu Simbel EN | 7 stages, chapters, no section tabs, WebPage + TouristTrip structured data |
| Abu Simbel DE | 6 stages (the untranslated dawn pause is left out), own headings |
| Valley, Giza, Luxor | original template with tabs, unchanged |
| Homepage | unchanged |

**Checks**
- axe + overflow + no-JS + reduced motion, EN / DE / AR × 320 / 390 / 768 / 1440 (36 runs): **0 issues**.
- Sweep (console, failed requests, broken images, preloads), Abu Simbel EN / DE / AR, Valley, White Desert, Aswan × 390 / 1440: **0 issues**.
- Throttled performance (3 runs):

  | | First view | FCP | LCP | CLS |
  |---|---|---|---|---|
  | Phone | 374 KB | 1708 ms | 3280 ms (one 8 s server outlier; best 2836) | 0 |
  | Desktop | 535 KB | 1944 ms | 3360 ms | 0 |

  The hero remains the LCP.
- **Images:** all 5 stage photos and 3 related cards load on a 2× phone. The very first request for each new crop size rendered slowly at Unsplash's CDN (no errors). The second run was fully cached.

**Visual:** production screenshots at 390 and 1440 match the local review (§5).

## 8. Which experiences are ready for the story next

Criteria: verified media, approved editorial, and a natural journey in the body.

| Experience | Verified media | Approved editorial | Natural journey in the body | Ready? |
|---|---|---|---|---|
| Valley of the Kings | 4 verified moments (in `stash@{0}`) | yes (495 words; "What you actually do") | yes: West Bank → descend into tombs → Hatshepsut's terraces | **Next, after the native-speaker review** of its preview text (gate still open) |
| White & Black Desert safari | none verified | yes ("What you see", "A night in the desert") | **strong:** Bahariya → Black Desert → Crystal Mountain → White Desert → night camp | **needs 4–6 location-tagged photos** |
| Red Sea diving | 1 (Hurghada reef hero, verified 1.2.34) | yes ("What a dive day looks like") | yes: boat → briefing → two dives | needs 3–4 more photos (Hurghada-tagged) |
| Cairo street food | none | yes ("What you'll taste") | yes: evening walk dish by dish | needs photos (food photos tagged to Cairo are rare: sourcing risk) |
| Nile dinner cruise | none | yes ("What the evening is like") | yes: boarding → dinner → tanoura show | needs photos |
| Giza private tour | not reviewed (out of scope) | yes | yes | **do not touch** until Abu Simbel is accepted |
| Great Sand Sea 4×4 | hero tag only "Siwa dunes" | yes | yes | **do not touch** (out of scope) |

**Recommended order once you accept Abu Simbel:**
1. Valley (media exists; the native review is the only gate).
2. White Desert (best journey, needs photos).
3. Red Sea diving.

Each one needs only:
- a `story` entry (stage order, layout and `from` anchors);
- verified photos;
- no new copy, because the body text is reused.

## 9. Next: AI search / GEO (separate track)

The priority content gaps from `AI-QUERY-DATASET-2026-10-03.md` are unchanged:

- visas, safety and money (sources required);
- a Luxor–Aswan Nile cruise page;
- non-English planning content (the guides are English-only).

Bing Webmaster Tools verification (owner action) remains the first measurement step.
