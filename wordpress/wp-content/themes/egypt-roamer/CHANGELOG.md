# Egypt Roamer theme — changelog

Versions match `Version:` in `style.css` and `ER_THEME_VERSION` in `functions.php`. CI fails if they differ.

## 1.2.38 — 2026-10-03 (with Core 1.2.24): structured data for answer engines
- **Destinations and experiences describe themselves as web pages with real dates.** Each indexable destination and experience now carries a `WebPage` node with:
  - its language;
  - the real `datePublished` and `dateModified`;
  - the site it belongs to and its publisher (the same Organization and WebSite ids as the homepage).
  Before, only guides exposed dates or a publisher. Theme files are unchanged apart from the version.
- **Experiences are described as a `TouristTrip`.** It holds only what the page shows: the title, the summary, and the destinations under "Where it happens" as its itinerary, in the page's language. No offers, prices, ratings or provider: the site sells nothing.
- **Destination data gets an `@id`.** The existing `TouristDestination` gains `@id …#place`, which the page's `about` points to. Nothing else changes.
- The same gating as before applies: only without an SEO plugin, and only on pages ticked "Ready to index".

## 1.2.37 — 2026-10-03 (with Core 1.2.23): Experience Immersion (Abu Simbel)
- **Experiences can open as a journey instead of a photo strip.** A reusable "Experience Immersion" section (`template-parts/experience-immersion.php`) sits between the hero and the practical detail, on the hero's dark ground:
  - the opening: a short statement and the route at a glance (Before dawn → Arrival → The first view → Inside → The highlight → The second temple → Afterwards), each a link to its stage;
  - the journey, stage by stage: a full-width 4:5 photograph with its text beneath on phones; from 1024px the photograph holds still beside the text while the text passes. Where no verified photograph exists (before dawn, afterwards) the stage is typographic and the light of the hour is drawn by the background, not by a picture;
  - "What it feels like" (pace, time on site, setting, crowds), labelled as general expectations, not promises;
  - "What to know" on the light ground (location, duration, getting there, sun festival, what to bring, inside the temples), then the next step: the partner's own offer when one is live (with the disclosure), otherwise "Plan My Trip", and a link to the full guide below. The sidebar's Key facts are not repeated.
- **Same content system.** The story extends the existing preview entry (`data/previews.json` → `er_preview_story()`) and is built on the same verified, location-tagged photographs. Every sentence comes from the approved English text. Languages without the story text (all but English for now) show the same sequence of photographs with their approved, translated moment titles and captions: no mixed languages, no invented translation. Experiences without a story keep the previous strip.
- **No cost at first paint.** The hero stays the only first-view image. Stage photos are requested about a screen before they are reached (`immersion.js`; `<noscript>` without JavaScript). Abu Simbel, throttled phone and desktop: same first-view bytes (+2 KB of HTML/CSS/JS), same FCP and LCP, CLS 0. The image transition is CSS (scroll-driven), only where supported and only when motion is welcome; nothing depends on it.

## 1.2.36 — 2026-10-03 (with Core 1.2.22): guide hero and alignment, stable first render, lighter desktop heroes, Valley photo
- **Guides get their photo hero.** The guide template was copied from the article template and never passed its stand-in photo to the shared hero, so guides had a text-only dark band. They now show the photo their cards already use, like destinations and experiences. Byline, date, reading time, long-form body and the centred reading column are unchanged.
- **One alignment axis on guides from 1024px.** The "Experiences / Destinations in this guide" cards sat on the page gutter while the title and body used the centred reading column. They now share the column (two cards per row). The title starts on the column's edge but may run to the usual 18ch, so it no longer wraps to 3–4 lines: hero 815–927px → 703–784px at 1440, and the first paragraph is above the fold.
- **Text no longer jumps when the web fonts arrive** (inner pages). The page title's face and the body face are now preloaded per script (Latin; Cyrillic + Latin digits; Arabic; Chinese none). Valley of the Kings, 8 languages × 10 widths, throttled: combinations with CLS > 0.01 24 → 5, worst 0.231 → 0.053, for ≈ 0.19 s later first paint on a slow phone. The metric-adjusted fallback fonts tried earlier made real shifts worse and are not used.
- **Lighter desktop heroes, same framing.** Above 900px, page-hero photos asked for the full image height, so a portrait photo arrived several times taller than the band (Best Time guide 2.3 MB at 1280px on a 2× screen). The height is now capped at 0.8 × width (imgix `max-h`): portrait files are 40–55% smaller; landscape files are identical, and the visible framing is unchanged.
- **Valley of the Kings photo shows the West Bank.** The experience's photo was tagged "Karnak, Luxor" (East Bank). It is now the terraced temple of Hatshepsut beneath the cliffs, tagged and described as such by its photographer.

## 1.2.35 — 2026-10-03 (with Core 1.2.21): internal links, guide language switcher, Siwa photo, experience preview prototype
- **Internal links through the existing editorial pipeline.** Editorial front matter gains language-neutral relations (`destination`, `related`, `alternatives`) that `wp egypt-roamer editorial` adds when missing and never removes. They are left out of the translation fingerprint, so the 105 approved translations stay current.
  - Guides now list their destinations (each destination page shows "Plan your trip to …" for the English guides) and related experiences.
  - Red Sea Diving is also linked to Sharm El Sheikh, as its text already says.
  - Two pairs of "Alternatives to compare": Nile dinner cruise ↔ Cairo street food tour, White & Black Desert safari ↔ Great Sand Sea 4×4.
  - The four published English guides link words they already contain to the matching experience and destination pages. No wording changed.
- **Language switcher on archives.** A language whose archive has nothing published (the guides, English-only for now) is linked to its homepage instead of an empty, noindex archive. Archives with content still link to the same archive in each language.
- **Siwa photo with a verified location.** The Siwa destination photo (no location tag) is replaced by a free photo of Shali fortress tagged "Siwa, Egypt" by its photographer.
- **Experience preview (prototype, Abu Simbel only).** A "moment by moment" strip of five photographs after the page's first section, all location-tagged "Abu Simbel" by their photographers. Captions repeat the approved text, and credits link each photo's source.
  - Scroll-snap strip with previous/next buttons, a counter and progress bars. It works without JavaScript and in RTL, uses smooth scrolling only when motion is allowed, and lazy-loads every photo below the fold.
  - Video support is built but unused: poster first, the clip created only on play, muted, with native controls and a failure message. Media marked as generated is labelled "Illustration, not footage of the place". The other seven experiences have no preview until the prototype is approved.

## 1.2.34 — 2026-10-03 (with Core 1.2.20): photo provenance, honest newsletter, guide byline
- **Every photo now shows the place it claims.** A provenance check of all 52 stand-in photos against their Unsplash location tags found six public slots showing other countries or an unverifiable place. They are replaced (owner-approved 2026-10-03) by free photos whose photographer tagged the exact Egyptian place:
  - Red Sea Diving: NEOM, Saudi Arabia → a Hurghada reef with divers;
  - homepage Red Sea scene: NEOM → a Hurghada reef; its caption now gives Hurghada's coordinates instead of Ras Mohammed's, and the alt text describes what the photo shows;
  - film shot 5: NEOM → an emperor angelfish, Hurghada;
  - "Luxury" travel style: an Abu Dhabi resort → a Nile boat deck at Aswan;
  - Great Sand Sea 4×4 and "Adventure": Abu Dhabi → 4×4 tracks in the Siwa dunes;
  - White & Black Desert Safari: an untagged white-dune photo (tagged "winter") → a chalk arch tagged "White Desert, Bawiti".

  The car-rental samples (hidden drafts) no longer carry their Mojave and Dubai photos. Same crop pipeline as 1.2.33.
- **Newsletter promise matches reality.** Subscriptions are stored, but no monthly letter is sent. "One beautiful email a month… the deals worth knowing about" and "first letter arrives next month" are now "Hear from us when it matters", "New routes and guides, sent only when we have something worth your time" and "We'll write when there's something worth reading". Applied in all 8 languages.
- **Guides without a named author** read "By Egypt Roamer", the publication, as the Article structured data already says, instead of a bare "By".

## 1.2.33 — 2026-10-02: honest copy, sharp photos, lighter Arabic fonts
- **Copy that matched what the site can do today.** No partner offers are live, so the homepage no longer says visitors can "book it all" or that experiences are "bookable with our partners". It now reads: "Discover and plan the best experiences in Egypt — all in one place." (hero and homepage description), "1 place to discover & plan it all", and "Handpicked by our editors, with the practical details to plan them." "Hover a place to step inside it" (meaningless on a phone) is now "Choose a place to step inside it." Applied in all 8 languages. Partner-only wording ("Book with our partners", the finder) still only shows when an offer is live.
- **Sharp photos on phones, tablets and Retina screens, at the same weight.** Landscape photos sit in tall frames (`object-fit: cover`), so a phone was sent a 1200 px landscape file of which a third is visible, stretched about 3× (cards on a 2× laptop got 0.6× of the pixels they show). Unsplash now crops each photo to the frame's shape (`w` + `h` + `fit=crop`, centred like the CSS):
  - full-screen homepage scenes, planner and moods: 2:3 on phones, 1:1 on portrait tablets (`<picture>` sources, `er_stock_picture()`);
  - page heroes: their own phone and tablet shapes;
  - cards: 4:4.6, the card frame; phone destination cards: 1:1.3.

  Each crop is at least as wide, for its height, as any frame it serves, so the same part of the photo shows (verified on screenshots). On a 390 px iPhone the homepage photos weigh 379 KB instead of 470 KB.
- **The homepage hero no longer downloads twice.** The preload listed other widths than the image (900/1400/2000/2800 vs 900/1200/1600/2000/2800), so phones, tablets and 1280 px laptops fetched two files. There is now one preload per screen shape, listing exactly the image's candidates.
- **Arabic fonts 31-52 % smaller, pixel-identical.** IBM Plex Sans Arabic and Noto Naskh Arabic are cut to the basic Arabic block (U+0600-06FF: every letter, mark and digit, Persian letters included) with every shaping feature kept. Rendered pixel-identical on all 256 code points and 66,000 characters of the site's Arabic text in all 7 weights. The Arabic first view loads about 85 KB less. The Plex subset is renamed "ER Sans Arabic" inside the file, as the font licence (OFL, Reserved Font Name) asks for modified versions.
- **Phone destination cards on pale photos:** the region label sat on the bare sky (Cairo's haze). A steadier shade and a soft text shadow fix it, as on the phone page heroes.

## 1.2.32 — 2026-10-02
- **Inner-page heroes on phones stay readable on pale photos.** The shade faded out a third of the way down the hero, where the eyebrow and the title sit on a phone, so the gold eyebrow on the White Desert photo was nearly invisible. There is now a steadier shade plus a soft shadow under the text. Desktop is unchanged.

## 1.2.31 — 2026-10-02
- **Arabic and Chinese pages: one render-blocking stylesheet instead of three.** The Arabic `@font-face` rules (1 KB) and the RTL/CJK rules (3 KB) are printed inside the page after the bundle, in the same cascade order. PageSpeed counted 1.6 s of render blocking on the Arabic homepage on a phone. The Arabic font URLs are absolute, and both files count in the stale-page build ID.

## 1.2.30 — 2026-10-02 (with Core 1.2.19): post-launch improvements
- **Mood section on phones: one background picture instead of seven.** The hidden mood layers (one was 457 KB) loaded with the visible one, about 900 KB on a phone. Now only the visible one loads. The others load when the dial is touched or focused, or on a desktop when the section nears the screen. A new mood's picture replaces the old one only once it is decoded (no dark flash).
- **Scene images: closer srcset steps** (1200/1600/1800 px added), so a 1440 px desktop no longer receives 2,000–2,400 px files. The sharpness of what is shown is unchanged.
- **Section tabs:** a soft fade on the side that has more tabs, instead of a label cut mid-word. A tab reached with the keyboard stops clear of the fade, and the fade follows the reading side in Arabic.
- **Experience pages with no live offer end with a next step:** "Need personal help?", which opens the team chat (or the Trip assistant), plus Plan My Trip. All the strings are already translated in every language.
- **Chinese page titles at full size:** the title fitting treated a Chinese title (no spaces) as one unbreakable word and shrank it (48 → about 30 px on phones). Each CJK character is now its own unit.
- **Logged-in editors:** the sticky section tabs no longer slide under the WordPress toolbar.

## 1.2.29 — 2026-10-02 (with Core 1.2.18): SEO workstream
- **Homepage title states the search intent:** "Egypt – Travel Guide – Egypt Roamer", in every language from approved strings ("مصر – دليل السفر", "Ägypten – Reiseführer" …). The slogan stays in the hero and the description.
- **Long titles drop the brand suffix** instead of being cut in search results at about 60 characters. The site name shows separately there.
- **Headlines no longer run words together.** "What kind of Egypt<br>are you looking for?" read as "Egyptare" to search engines and assistive tools; a space now precedes every line break (`er_spaced_breaks`).
- **Destination structured data names the country in the page's language** (`er_country_name`: "مصر", "Ägypten" …).

## 1.2.28 — 2026-10-02
- **Homepage font preloads: only the hero word's display face** (Latin/Cyrillic Playfair 400). None on Arabic and Chinese pages.
  - Measured in a real browser on a throttled phone connection (1.6 Mbps, 4× CPU, median of 3), preloading every first-view face made the first paint later: the fonts competed with the stylesheet.
    - English: 2.32 s with all, 1.71 s title face only.
    - Arabic: 2.84 s with all, 2.00 s with none.
  - Lighthouse agrees for English: local LCP 3.0 → 2.55 s.
  - This replaces 1.2.23/1.2.25's "preload everything". The other faces load from the stylesheet and swap in.
- **Homepage scripts in a background tab:** a tab opened in the background never paints, so the phone runner now starts the scripts from its 2 s fallback directly instead of waiting for an animation frame that only comes when the tab is shown.

## 1.2.27 — 2026-10-02
- **Chinese pages on phones use the device's CJK font; Noto SC stays on desktop.** 1.2.26's non-blocking, `optional` copy was not enough on production. Google's font servers are fast, so in PageSpeed's unthrottled pass all ~100 Noto SC slices (several MB) finished before the first paint and were counted in it (still 15 s).
  - **Phones:** neither the 212 KB stylesheet nor its slices are fetched. A stylesheet with a non-matching media query would still be downloaded, so a one-line script adds it on screens wider than 900 px only, with a `<noscript>` fallback. Android's CJK system font is the same Noto/Source Han design.
  - **Desktop:** Noto SC, swap, under the intro loader.
  - The Latin faces are preloaded on Chinese pages too (spaces, digits, Latin words).
  - Local mobile: Perf 90, LCP 3.35 s, TBT 34 ms; zero Google font requests on phones.

## 1.2.26 — 2026-10-02
- **Chinese pages on phones no longer wait for Google's font stylesheet.** The Noto SC stylesheet is 212 KB (every unicode-range slice of seven faces) and was render-blocking: PageSpeed measured a **15.1 s** first paint on the Chinese homepage.
  - **Phones (≤ 900 px):** a non-blocking copy with `display=optional`. Chinese text is drawn at once in the device's CJK font, and Noto SC is used when it is already at hand (a returning visitor's cache). With `swap`, re-laying out the page for each of ~100 slices cost 530 ms of blocking time.
  - **Desktop:** unchanged (blocking, `swap`; the intro loader covers the load).
  - The font host is preconnected.
  - Local mobile: LCP 3.08 s, TBT 65 ms.

## 1.2.25 — 2026-10-02
- **Arabic homepage: the Latin faces are preloaded too.** The font stacks start with the Latin family, so the spaces and digits inside Arabic text are drawn from (and fetch) Inter and Playfair. On production these files were found only after the stylesheet: a 1.5 s chain in PageSpeed's dependency tree. Local mobile FCP 3.43 → 1.74 s; LCP is unchanged (4.7 → 4.6 s), because it is bound by the eleven font files' bytes (see `docs/PERFORMANCE-2026-10-02.md`).

## 1.2.24 — 2026-10-02
- **The mood section's background photos fit the screen.** They were requested at a fixed 1800 px on every device: up to 1.35 MB each, about 2 MB that phones didn't need (PageSpeed, "Improve image delivery"). They now have a responsive `srcset` (600–1800 px, `sizes="100vw"`): a phone takes the 900 px versions, and desktop is unchanged.

## 1.2.23 — 2026-10-02
- **The stale-page check waits for the first paint and runs at low priority.** PageSpeed listed its request on the homepage's critical path (452 ms). It decides whether to reload a stale copy, never what is drawn, so it no longer competes with the first view. `stale-html.mjs` 7/7.
- **Every face the homepage's first view draws is preloaded** (Latin: 6; Cyrillic: 6; Arabic: 5), not just two. The rest were found only after the stylesheet arrived. Local mobile FCP 2.29 → 1.63 s.

## 1.2.22 — 2026-10-02
**Phones: content-first homepage.** Desktop is unchanged; it keeps the cinematic intro. Before this, phone visitors saw nothing but the loader until the intro's 1.3 s hold, the hero photo, every font and ~86 KB of homepage JavaScript had finished. PageSpeed measured 3.2 s of "render delay" on the hero text (`docs/PERFORMANCE-2026-10-02.md`).
- **No loader screen on phones (≤ 900 px, the hero's phone layout).** The hero is drawn at the first paint and plays its own entrance in CSS:
  - the eyebrow rises out of its mask;
  - the title settles into place, fully drawn from the first frame;
  - text and buttons fade up.
  The brand mark stays in the header. `hero.js` hands over at once on these widths. Reduced motion: no movement.
- **Homepage scripts on phones run after the first view is painted.** GSAP, ScrollTrigger, Lenis, the language file and the homepage bundle wait for the first contentful paint and the first-view fonts (at most 2 s). They were deferred scripts that ran before anything was shown. They still download early (`preload`); on desktop they run as before. Without them the page stays the static, stacked fallback.
- **The journey's pin spacer is part of the markup** (`.journey__spacer`). ScrollTrigger used to re-parent the stage when pinning, and Chrome then reported the Arabic hero title a second time as a new, later Largest Contentful Paint.
- The phone hero text settles in like the title (drawn from the first frame). In Arabic it is the largest element on screen, and its fade from transparent made it a later LCP. The homepage launcher on phones waits for its first placement instead of flashing next to the play button.
- **Logos download only where they show.** The full logo isn't downloaded on phones, the mark isn't downloaded on wider screens, and the loader logo isn't downloaded on phones (`<picture>` with a 1 px source for the hidden breakpoint). The light-background variants load lazily and are fetched after page load, so the header never shows an empty logo when it turns light. Phones save ~71 KB before the first paint.

## 1.2.21 — 2026-10-02
Production accessibility pass (axe on live pages):
- **The chat transcript is reachable by keyboard.** A long transcript scrolls, but the log could not be focused, so keyboard users could not scroll it (axe "scrollable-region-focusable", serious). It is now a labelled `role="log"` region with `tabindex="0"`.
- **No nested complementary landmarks on the homepage.** The draft itinerary and the scene "Roamer pick" cards were `<aside>` elements inside `<main>`. They are now labelled `<section>`s (axe "landmark-complementary-is-top-level"). No visual change.

## 1.2.20 — 2026-10-02 (with Core 1.2.17)
- **Chat messages keep their order.** Messages typed in quick succession were sent as concurrent requests and could reach the team out of order (seen in the 8-language test: 1, 3, 2). The visitor's messages now go out one at a time; each bubble still appears at once.
- **A stalled request no longer freezes the chat.** Visitor requests give up after 20 s (a failed message shows Retry, and later messages still go out). In Core 1.2.17, the team inbox's requests give up after 15 s: before, one unanswered request stopped all inbox refreshes until a reload. Test: `tools/qa/chat-stall.mjs`.
- **Arabic and Chinese labels** (the hero's eyebrow, scene kickers, card and footer labels) are 12.8 px instead of 11 px. Without Latin capitals and tracking, 11 px Arabic or Chinese read as a faint, cramped line.
- **Phones:** the hero's buttons clear the bottom dock. The play button's pulse ring crossed the dock's edge.
- **Phones: the Trip assistant launcher no longer covers text.**
  - On inner pages it steps aside while the page header's text is under it (as on the homepage hero). It covered the experience intro at 320 px.
  - The footer's legal links end above it (it covered "Cookie-Richtlinie" in German).
- **Page headers over photos get a side shade under the text** (left in LTR, right in RTL), as on the homepage scenes. The Arabic experience page's gold label sat on a bright sky at about 1.4:1 contrast.
- **Long words in page titles fit their line.** The title shrinks just enough for its longest word. On a 320 px phone the Russian "Индивидуальная" broke mid-word without a hyphen. `hyphens: auto` was also added, for browsers that hyphenate the language.

## 1.2.19 — 2026-10-02
- **Arabic hero: the landmark photo is no longer mirrored.** 1.2.17 flipped the Giza photo in RTL so the text would not sit on the Great Pyramid. Instead, on screens wider than 900 px the opening scene keeps the photo's own composition in Arabic: the Arabic text block (right-aligned, read right to left) sits on the open sky on the left under the shade, the note and pick card on the right. Phones stack the text at the bottom and needed no change. `pages.css`; screenshots 320–1920.
- **One stylesheet per template.** The six or seven render-blocking stylesheets are served as one file built by `tools/build.py theme` (`bundle-home.css`, `bundle-site.css`): the same rules in the same order, comments and indentation removed (−20%). Computed styles of every element are identical to the separate files (12 pages × 2 widths). The source files stay the ones to edit; CI fails if a bundle is stale. Arabic fonts and `rtl.css` still load separately.
- **Homepage fonts preloaded:** the hero word's serif and the hero text's light sans (per script: Latin, Cyrillic, Arabic) start downloading with the stylesheet instead of after it.
- Measured (Lighthouse mobile, local, median of 3): homepage FCP 2.73 → 1.68 s, LCP 5.16 → 4.65 s; destination FCP 2.79 → 2.04 s; experiences 2.48 → 1.86 s. Details: `docs/PERFORMANCE-2026-10-02.md`.

## 1.2.18 — 2026-10-01
- **Homepage banner, bottom right (owner's screenshot):** the dune foreground ended 2% short of the right edge, leaving a hard vertical seam next to the Trip assistant button at every width. `journey.css` sizes it 104% wide from −2%, but the base reset (`img, svg { max-width: 100% }`) capped it at 100%. The cap is lifted for this layer (`pages.css`); it now overhangs both edges as designed. Measured −18 → 930 px at 912 px (was −18 → 894), −29 → 1469 at 1440; no horizontal overflow (homepage 44 checks, 4 languages).

## 1.2.17 — 2026-10-01
Visual audit (rendered screenshots in 8 languages × 320–1920 px, every assistant state, logged-in and adversarial states; `tools/qa/visual-sheets.mjs`, `visual-adversarial.mjs`):
- **Arabic hero composition.** The layout mirrors in RTL but the photo did not: the hero text sat on the Great Pyramid (the photo's subject, on its right), and the bare sun glared under the note on the left. The opening photos are now mirrored in RTL (CSS `scale: -1 1`, which composes with GSAP's transforms): subject opposite the text, sun under the text's shade, as in the other languages.
- **Outdated cached pages heal themselves.** Pages cached in browsers before 1.2.15 (no build id, 31-day host cache) still showed the 1.1:1 ghost "Chat with Egypt Roamer" button. The assistant script, fetched fresh when the drawer opens, reloads such a page once. And the ghost style inside the drawer is readable even if an old page gets the new stylesheet.
- **Welcome line** in the assistant drawer (it opened as an empty panel). One new string, drafted in 7 languages (unreviewed).
- The Privacy Policy link in the chat form is underlined (it looked like plain muted text).
- **Phone dock:** "Planifier mon voyage" (fr) was cut by the button's fixed height at 320 px; "Спланировать" (ru) touched its edges. A smaller, tighter label, with a minimum height instead of a fixed one.
- **Logged-in editors:** the WordPress toolbar covered the drawers' title and close button, and below 600 px it covered the header (the menu button could not be clicked). The drawers and the menu now sit below the toolbar, and the toolbar stays fixed on small screens.
- **Launcher at 320 px (Arabic):** 12 px from the play button, it read as a third hero button. "Too close" (20 px) now counts as an overlap, so it steps aside there too and returns on scroll.

## Core 1.2.16 — 2026-10-01 (no theme change)
Adversarial pass on the chat:
- A visitor-supplied page path like `//evil.example/x` passed the same-host check and appeared in the team inbox as a protocol-relative link to another site. Paths are now stored with a single leading slash, and the inbox links only same-site paths.
- A site-wide cap of 60 new conversations per hour, besides the 10 per IP: a flood from many IPs can't fill the inbox or the database.

## 1.2.16 — 2026-10-01
Chat message order and completeness under concurrency (with Core 1.2.15; `tools/qa/chat-multi.mjs` 11/11):
- **Order.** The visitor who sent a message saw it after a team reply that the server had stored first; other views showed the server order. Messages are now placed by their server id in both the visitor UI and the team inbox; a message being sent stays at the end until stored.
- **No message lost.** Views polled "after the newest id seen". With concurrent inserts (MySQL), a lower id can be committed after a higher one was read, and was then never fetched; the inbox also dropped any id at or below the newest seen. Polls now re-read a small overlap (the last 20 ids) and skip ids already shown. Reproduced by inserting a lower id late: before, the inbox never showed it; now both views do, in order.

## 1.2.15 — 2026-10-01
With Core 1.2.14.
- **Homepage start-up (TBT), no visible change** (`docs/PERFORMANCE-2026-09-30.md`):
  - The journey sets itself up in three tasks (titles and initial states / timeline / particles, rail and pin) instead of one.
  - The duplicate `ScrollTrigger.refresh()` on `load` is gone (ScrollTrigger refreshes on load by itself).
  - Local, 4× CPU phone, median of 5: TBT-like 477 → 370 ms, longest task 282 → 246 ms, refreshes 4 → 3 (desktop 4 → 2).
  - Identical ScrollTrigger geometry (every start and end, the pin spacer, the page height), screenshots identical at 5 scroll positions (only the random particles differ), `#planner` lands identically, CLS 0.0003 / 0.0019.
- **Stale-HTML guard** (`docs/CACHE-2026-10-01.md`): every page carries its build id (`<meta name="er-build">`). A 0.7 KB inline script compares it with Core's `/wp-json/egypt-roamer/v1/build`, at once for an unknown id and otherwise at most every 30 minutes. An outdated page (the host keeps pages in browsers for 31 days) reloads once, or loads once with `?nocache=` when the edge copy is outdated too. Static asset caching is unchanged.

- **Assistant and chat in 8 languages** (`tools/qa/chat-i18n.mjs`, every state at 320 and 1440 px):
  - The input's prompt is a visible caption above the field instead of a placeholder. The translated prompts ("Ask about a place, a trip or an experience": 186–464 px) didn't fit the 119–238 px field on any phone in 7 of 8 languages; the cut was there since 1.2.9. The caption wraps, uses only existing translations, and is better for screen readers.
  - "Chat with Egypt Roamer" and "Cancel" used the light-on-dark ghost button on the drawer's light background: contrast 1.1:1. They now use the outline button. axe had only listed them as "needs review", because of the button's backdrop blur.

Core 1.2.14: `includes/freshness.php` (build id, content epoch on editor saves, `/build` endpoint).

## 1.2.14 — 2026-10-01
Chat with the Egypt Roamer team, inside the Trip assistant (with Core 1.2.12; `docs/HUMAN-LIVE-CHAT-2026-10-01.md`):
- The drawer offers "Chat with Egypt Roamer", with the team's real availability ("Team is online" or "Leave us a message"). Asking the assistant for a person offers the same.
- A short start form (optional name and email, the message, a storage notice with the Privacy Policy link) starts a conversation; the questions asked so far go with it.
- In the chat, the drawer becomes "Live chat": bubbles for the visitor, the team and the assistant; pending, sent and failed states with Retry (no duplicates); "Ask for the team" and "End chat"; resumed after a reload.
- New messages are fetched only while the drawer is open and the tab visible (every 3 s, slowing to 15 s when quiet). Nothing new loads on the page until the drawer is opened.
- 29 new UI strings in 8 languages (drafted, unreviewed: `tools/i18n/new-strings.json`); Arabic mirrored.

Core 1.2.13 (same day): the inbox keeps working in a background tab (polling every 30 s / 15 s, presence continues, unread count in the tab title), found in the production test. Visitor parameters accept only scalar values (crafted JSON arrays are ignored, no PHP warnings).

Core 1.2.12: tables `er_chat_conversations` and `er_chat_messages` (DB version 2); visitor and team REST routes; Egypt Roamer → Conversations (inbox, filters, search, take over, return to AI, close, reopen, assign, archive, presence, unread badge through Heartbeat); capability `manage_er_conversations` (administrators, editors); settings "Chat with the team" and retention (90 days); email notification of new or reopened chats; daily purge; privacy export and erasure.

## 1.2.13 — 2026-10-01
SEO, with Core 1.2.11 (`docs/SEO-AUDIT-2026-10-01.md`). Indexing stays off; nothing here changes what visitors see on the page:
- Destination titles follow the search intent: "Kairo – Reiseführer – Egypt Roamer", built only from approved strings (the destination's title and the theme's "Travel Guide" in each language).
- The theme provides the logo for Core's Organization data (`er_brand_logo`).

Core 1.2.11:
- Homepage structured data: Organization (name, URL, logo) and WebSite (languages), true facts only: no contact point (the public mailbox has no MX record yet), no sameAs, no retired SearchAction.
- Article data for guides and journal posts once they are published and ready to index (real dates; the publication as author; the image only when the article has its own).
- The core sitemap now lists the indexable hubs (/destinations/, /experiences/, and /guides/ etc. once they have an indexable item), one per language, with the newest item's date as lastmod.
- robots.txt: `Disallow: /go/` sits in the `User-agent: *` block, before the Sitemap line.
- `wp egypt-roamer index`: the launch switch, step 1. Reviews (or with `--apply`, ticks "Ready to index" on) published destinations and experiences in every language on quality, not length: own description, in-content links, a translation complete against its English original (sections, FAQ items, list items, links) and written in its language's script. Local launch simulation: 176 sitemap URLs, 0 failures (`tools/qa/launch-sim.py`).

## 1.2.12 — 2026-10-01
Trip assistant launcher, keyboard safety for the 1.2.11 phone behaviour:
- The launcher never steps aside while it has keyboard focus or while its drawer is open (focus returns to it when the drawer closes), and it re-checks when it loses focus or an overlay closes.
- New check `tools/qa/launcher-keyboard.mjs` (320/360 px, en/de/ar): reached by Tab, visible whenever focused, never focused while hidden, Enter opens the drawer with focus in the question field, Escape returns focus, stays visible when scrolled back to the hero while focused.

## 1.2.11 — 2026-10-01
Homepage: the Trip assistant launcher no longer covers hero or journey content (the hero's right-side crop is the approved composition: the image geometry matches the prototype at every width, so the hero CSS is unchanged):
- Desktop (≥ 901 px): the launcher sat on the journey's scene counter ("01 / 04", same corner; mirrored in Arabic) at every width. While the journey is on screen it now sits above the counter (`body.assistant-raised`), and drops back at the footer.
- Phones: at 320 px (6 languages) and 360 px (German) the launcher covered the hero's play button. It now steps aside while those buttons are visible under it (`body.assistant-clear`) and returns on scroll.
- `assistant-loader.js` sets the classes; the rules are in `pages.css`. The approved stylesheets are unchanged.

## 1.2.10 — 2026-09-30
Homepage entrance, owner decision of 2026-09-30 ("reveal the text with the loader"):
- The hero comes in as the loader's wipe starts. Before, it came 250 ms after the loader, the paragraph waited another 0.55 s, and the fade lasted 1.2 s. Now it is one 0.6 s fade with a light stagger (overrides in `pages.css`; the approved stylesheets are unchanged).
- The hero paragraph was the Largest Contentful Paint (GTmetrix: 98-99 % render delay). Local LCP went from 2.7-3.6 s to 2.28 s, including with a slow third-party hero photo.

## 1.2.9 — 2026-09-30
Trip assistant (with Core 1.2.10; `docs/ASSISTANT-2026-09-30.md`):
- A "Trip assistant" button on every page (bottom corner, mirrored in Arabic; above the dock on phones) opens a drawer. The drawer reuses the Saved drawer: focus trap, Escape, focus return.
- The visitor asks a question, or taps a suggestion (Pyramids, Nile, Desert, Red Sea: the approved scene titles). The answer lists the published pages that match, in the page's language, with live offers when a page has any (through /go/, `rel="sponsored"`). With no match, it offers the destination and experience archives.
- The script (`assets/js/assistant.js`, 4.6 KB) loads only when the drawer is first opened. Questions asked while it loads are queued.
- New UI strings: drafted in 7 languages in `tools/i18n/new-strings.json` (unreviewed, like the other WordPress-only strings).
- Accessibility found while testing:
  - the drawers' `<header>` counted as a second page banner while open (the Saved drawer too); it is now a `div`;
  - the mobile menu's links were outside any landmark; the menu is now a `<nav>`.
- Desktop: room under the footer's last row, so the launcher never covers the legal links.

## 1.2.8 — 2026-09-30
Homepage performance (measurements in `docs/PERFORMANCE-2026-09-30.md`; the look and the entrance animation are unchanged):
- The intro loader's minimum (1.3 s) and its cap count from the start of the page, not from when the script runs. The loader is on screen from the first paint, so a slow phone no longer sits through it twice.
- The loader waits for the hero photo at most 1.6 s from the start of the page (was 2.2 s after the script started). The photo is hot-linked from a third-party host, and a slow response held the whole hero back. The photo still fades in when it arrives.
- The intro starts as soon as the hero is ready, not after every homepage section has been set up.
- The homepage sections are set up one task at a time, in the same order: one ~450 ms task on a mid-range phone became separate ones, so the page stays responsive while they load.

## 1.2.7 — 2026-09-30
From the full-site audit (every public URL × 8 languages; `docs/FULL-SITE-AUDIT-2026-09-30.md`):
- Homepage display titles fit their column in every language: "КРАСНОЕ МОРЕ" was clipped at every width (desktop included), "ROTES MEER" on phones up to 480 px, and "ÉGYPTE", "EGITTO" and "EGIPTO" touched the edge on small phones. A fitter (`src/js/components/fit.js`) keeps the approved size while the word fits and shrinks it just enough when it does not; English and Chinese are unchanged at every width.
- Keyboard:
  - Tab and Shift+Tab stay inside an open overlay (search, saved) or the mobile menu, with the menu button, instead of moving to the page hidden behind it.
  - The language menu closes when focus leaves it, and Escape closes it wherever focus is.
- Home links (logo, Plan My Trip, planner links, 404, breadcrumbs, search overlay, script data) use `er_home_url()`, the language's homepage, explicitly. `home_url( '/' )` was localized by Polylang only when the calling file's path matched the theme folder: correct on production, English on an installation whose theme is a symlink.
- Language switcher on an archive with nothing published yet (tours, activities, guides) links the same archive in each language instead of the homepages.
- `tools/build.py` writes LF on Windows (it wrote CRLF bundles).

## 1.2.6 — 2026-09-30
Global footer parity (with Core 1.2.9). The English footer had three columns (Explore, Plan, Egypt Roamer) and a two-link legal row; the seven other languages had only Explore, with Affiliate Disclosure and Contact moved into the legal row. The columns came from per-language menus the seed had generated, which left out every item it could not translate at the time: the "Trip Builder" anchor, and the Affiliate Disclosure and Contact pages (English-only then).
- One canonical footer: every language renders the default language's footer menus, localized item by item (`er_localize_menu_item()`): pages → their translation, archives → the language's archive, homepage anchors → the language's homepage, labels → the approved prototype translations (else the translated page's title). An item with no version in a language is left out, never shown in English. Polylang's swap to a per-language menu is undone for these locations.
- Theme translations: the footer menu labels ("Trip Builder", "Affiliate Disclosure", "Contact", "Our Story", …) from the prototype's approved locale files (`tools/i18n-strings.py` now reads the seed's menu labels).
- `er_t_strict()`: a theme string only when this language has a translation.
- Footer columns carry `data-footer-col` (the menu location) for the parity test `tools/qa/footer-parity.mjs`.
- Menu locations are labelled "(all languages)" in Appearance → Menus.

## 1.2.5 — 2026-09-30
Multilingual parity for everything added after the 105-file translation review (with Core 1.2.8):
- Legal/contact pages (`page.php`): documents read on the centred column with section tabs (three or more sections), a quiet "last updated" line, hairline section dividers, compact headings, a bordered table that stacks into label/value blocks on phones, and a translation note on translated pages. No cards.
- Contact: intro, details (email, phone) and booking note beside the form on desktop (container width, one axis with the hero); stacked on phones; email/phone kept left-to-right in Arabic.
- Footer legal row built from the pages (Privacy, Terms, Cookies, Affiliate Disclosure, Contact) in the page's language with each page's own title, no longer from the hand-edited menus.
- Archive intros: `er_archive_intro()` (Core), localized in 8 languages; the guides intro only while guides are published in that language.
- `er_body()` option `cards => false` for documents.

## 1.2.4 — 2026-09-30
- Menu link check: it compared links with `home_url( '/' )`, which Polylang turns into `/de/`, `/fr/` … on translated pages, so links to unprefixed pages were treated as external and never checked. A German footer link to the unpublished Terms page (404) was shown. It now compares with the site's own origin.

## 1.2.3 — 2026-09-30
Legal/trust launch (the pages were published on production the same day; the texts are in `content/legal/en/`):
- Footer legal row (`er_legal_links()`): the "legal" menu, completed with every published legal/trust page (Privacy, Terms, Cookies, Affiliate Disclosure, Contact) not already in the footer columns. Every language reaches the English-only pages directly.
- Menus: a language-prefixed link to a page that exists only in another language (`/ar/privacy-policy/`) now links the page itself instead of a URL that only redirects.
- `page.php` pages (legal, contact): the article was both a 3rem-gap grid and `.prose`, doubling the space between every paragraph. Plain prose flow now.
- Archive intros and their meta descriptions use Core's `er_translate_string_strict()`: shown in English only until a real translation exists, so no English text on a translated archive.
- Footer newsletter note: its Privacy link is underlined (axe `link-in-text-block`, shown on every page once the Privacy Policy was published).

## 1.2.2 — 2026-09-30
Keyboard audit on production (every page, every language):
- The closed language menu was only transparent: its 8 links were invisible tab stops. It is now hidden until opened (the fade is kept).
- In-page links (section tabs, contents links) scrolled the heading under the sticky bars: the anchor handler now respects the target's `scroll-margin-top`, and moves focus to the section so the next Tab continues there.

## 1.2.1 — 2026-09-30
Performance, from measurements on production (desktop LCP: homepage 4.45 s, inner pages 2.3–2.6 s; CLS ≤ 0.003):
- Homepage intro loader: waits at most 2.2 s for the hero photo (was 3.5 s) and plays once per browser session; later homepage views in the session show the page at once. The look is unchanged.
- Inner-page hero photos: 640/960/1280/1600/2000/2560 widths (was 900/1400/2000), so a 1440px screen downloads the 1600px file instead of 2000px.
- `preconnect` to the stock-photo host on every page (it was on the homepage only).

## 1.2.0 — 2026-09-30
One reading layout for every inner page (fixes the audit of 2026-09-29: experience pages kept an empty 400px sidebar track, and destination bodies were centred on a different axis from their hero).
- Layout system in `pages.css`: `--measure` (46rem reading column), `--aside` (21rem), `--layout-gap`.
  - `.page-layout` is one column on the container edge, the same axis as the hero text, tab bar and related sections.
  - `.page-layout--aside` is used only when a template has sidebar content (`er_layout()` decides). At ≥1200px the sidebar sits on the opposite container edge and is sticky; below, the key facts open the body and the rest follows it.
  - `.page-layout--single` (guides, articles, pages) centres the column, and the hero text (`er_page_hero( [ 'measure' => true ] )`) and tabs sit on the same column.
- Destinations and experiences: key facts (and highlights) move into a "Key facts" card in the sidebar; destinations also list their first three things to do and the planner CTA (desktop).
- Sticky section tabs built from the body's H2 anchors, with scroll spy (`src/js/components/sections.js`). They replace the in-body "On this page" box and reuse its translated label; experience pages (no box) use the new theme string "On this page" (the approved labels from `tools/editorial.py`).
- Structured body at render time (`inc/sections.php`; stored content and the block editor unchanged): numbered sections; "**Term.** text" lists and runs of H3 + paragraph become card grids; callouts become a dark panel; FAQs share one box.
- Phones: image heroes are at most 62% of the screen, so the first content shows on the first screen.
- Homepage: experience and guide titles with "&" no longer show "&#038;" (the page data sends plain text to fields the scripts escape).
- Guides archive: no topic filter over an empty archive.

## 1.1.8 — 2026-09-29
- The homepage no longer shows destination travel times, matching 1.1.7's key-facts change (owner decision of 2026-09-29), in every language:
  - the "Getting there" fact in the destination panel;
  - the line under each destination in the map list;
  - the first line of the map card.
- The homepage data no longer includes `reach`/`mapReach`, so travel times are gone from the page source too. The stored meta is kept.
- Experience and activity durations are unchanged.

## 1.1.7 — 2026-09-29
- Destination key facts no longer show "Getting there". Its values were travel times (flights, drives, trains), which the editorial fact policy leaves out. This follows the owner's decision of 2026-09-29 and applies to every language.
- Region and Best time remain. Experience durations are unchanged.
- The stored values are kept, and the homepage map and destination cards still use them.
- Measured on 56 destination pages at 320–1440 px: the editorial text starts 30–90 px higher on average. There is no new overflow, and the contents box stays before the first section.

## 1.1.6 — 2026-09-28
- Release that ships 1.1.4 and 1.1.5 to production. No code changes.
- First release through the direct deploy: rsync over SSH with the normal production account (see `docs/DEPLOY.md`). GoDaddy's CI/CD deploy users stopped accepting logins (Runs #6–#8), so 1.1.4 and 1.1.5 never reached production.

## 1.1.5 — 2026-09-28 (not deployed; shipped in 1.1.6)
- Release that ships 1.1.4 to production. No code changes.
- 1.1.4 never deployed: GoDaddy refused the old CI/CD deploy user (Runs #6 and #7). GoDaddy CI/CD was re-enabled with a new deploy user, and this is the first release through it.

## 1.1.4 — 2026-09-28 (not deployed; shipped in 1.1.5)
- Destination and guide pages: the "On this page" box now renders after the introduction, just before the first section. This happens on output only; the stored content is unchanged.
- On phones, the contents links become one swipeable row.
- On phones, key facts with three or more items render as label/value rows (RTL-mirrored).
- Result: the first editorial text on destinations moved from 949 px to 382 px below the hero at 360 px wide.

## 1.1.3 — 2026-09-28
- Editorial components for `.prose`: contents box, planning callout, FAQ accordion (`details`), day-by-day itinerary, list markers. All are RTL-safe.

## 1.1.2 — 2026-09-28
- The hero word fits phone screens in German ("ÄGYPTEN") and Russian ("ЕГИПЕТ"), below 900 px.

## 1.1.1 — 2026-09-28
- GoDaddy's unused front-end stylesheets (`wp-components`, `wp-theme`, `godaddy-styles`) are dequeued for logged-out visitors.

## 1.1.0 — 2026-09-28
- Moods keep their photos, icons and destination in every language (language-neutral ids).
- Inner pages and cards use the approved stand-in photos until a featured image is set.
- Menus hide links to empty archives and to an empty Journal.

## 1.0.0 — 2026-09-26
- The approved Egypt Roamer design as a WordPress theme: CMS-driven homepage, inner templates, translations.
