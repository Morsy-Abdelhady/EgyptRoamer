# Egypt Roamer theme — changelog

Versions match `Version:` in `style.css` and `ER_THEME_VERSION` in `functions.php`. CI fails if they differ.

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
