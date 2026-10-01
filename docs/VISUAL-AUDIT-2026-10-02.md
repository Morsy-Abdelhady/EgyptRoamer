# Visual language audit: all 8 languages (2026-10-02)

Launch-gate area 3. Follows `docs/VISUAL-AUDIT-2026-10-01.md`, which covered English and Arabic in full and the other languages only at the hero.

## Method
- **Screenshots:** `tools/qa/visual-locales.mjs` took real screenshots of the local site (same theme and Core as production) in **en, ar, de, fr, it, es, ru and zh at 320, 390, 768 and 1440 px**.
- **States per width:**
  - homepage after the intro;
  - homepage footer;
  - a destination;
  - an experience;
  - the mobile menu (where its button shows);
  - a Trip Assistant answer;
  - Human Chat after starting;
  - 404;
  - search with no results;
  - the loading state on a slow network (390 px).
- **Total:** 284 screenshots, assembled into contact sheets per language and width (`python tools/qa/sheets.py loc-<lang>-<w>`) and **inspected by eye**. Header and CTAs are part of every state.
- **Other states:**
  - Reduced motion, logged-in, missing photos and keyboard focus: `visual-adversarial.mjs`.
  - Every chat state in every language: `chat-i18n.mjs`.
  - The full chat flow in every language: `chat-locales.mjs` (169 checks, with screenshots).
- **Production spot checks** in the in-app browser (anonymous): Arabic homepage at 1440 px, English homepage and destinations at 375 px.

## Defects found → fix
| # | Where | What the screenshot showed | Root cause | Fix (theme 1.2.20) |
|---|---|---|---|---|
| L1 | ar, 1440 (production) | Arabic hero mirrored the Giza photo (1.2.17's fix) | the photo was flipped to fit the RTL layout | 1.2.19: the photo stays unmirrored; the Arabic text block sits on the open sky (verified on production) |
| L2 | en/all, phones (production) | the play button's pulse ring crossed the bottom dock's edge | hero buttons ended 4 px above the dock; the ring reaches 14 px below the button | 0.7 rem more room under the hero on phones |
| L3 | ar, zh, all widths | eyebrow, scene kickers, card and footer labels at 9–11 px: faint and cramped | the label size is tuned for spaced Latin capitals; Arabic drops the tracking, and CJK at 11 px is small | label size 12.8 px for Arabic and Chinese |
| L4 | all, 320–390, inner pages | the round Trip assistant launcher sat on the page header's text (experience intro) at first view | the "step aside" logic only knew the homepage hero buttons | it also steps aside over the inner-page header text |
| L5 | de/all, phones, end of page | the launcher covered the end of the legal row ("Cookie-Richtlinie") | desktop had room under the footer; phones didn't | 3 rem under the footer's last row on phones |
| L6 | ar experience, 1440 | gold label on a bright sky, about 1.4:1 | page-header photos were shaded top and bottom only | side shade under the text (LTR left, RTL right) |
| L7 | ru, 320 | "Индивидуальная" broken mid-word without a hyphen | `overflow-wrap: anywhere`; no Russian hyphenation in Chrome on Windows | titles shrink just enough for their longest word; `hyphens: auto` added |
| C1 | all (8-language chat test) | three quick messages reached the team as 1, 3, 2 | concurrent sends | sends go out one at a time (chat section) |
| C2 | team inbox | the inbox stopped refreshing after one unanswered request | no request timeout | 15 s timeout (Core 1.2.17); visitor side 20 s |

## Checked and found right (all 8 languages)
- **Homepage hero:**
  - translated title fits (fit-to-width);
  - eyebrow, subtitle and CTAs in the composition;
  - dock labels inside their buttons, including fr "Planifier mon voyage" and ru "Спланировать поездку".
- **Header:** logo, heart, menu, language switcher. **Mobile menu:** two destinations, the CTA, all 8 language names; Arabic right-aligned.
- **Footer:** translated column headings, links and legal row; the newsletter and its privacy note.
- **Destination and experience pages:**
  - breadcrumbs, kickers, titles, intro, meta;
  - section tabs (scrollable on phones);
  - key-facts card;
  - Arabic mirrored throughout.
- **Trip Assistant:** welcome line, answer cards, chips, privacy note, "Chat with Egypt Roamer". **Human Chat:** bubbles on the reading side (left in Arabic), status line, "End chat", privacy note.
- **404 and empty search:** translated copy, search box, quick links; Chinese quotes 「」, Spanish and Italian «».
- **Loading (slow network):** the branded loader. On the slowest frames the logo is still on its way and a dark screen with the gold bar shows.

## Accepted (not defects)
- On phones, the launcher floats over content while scrolling, like any floating button. It is only moved aside where text sits under it at rest (L4, L5).
- Section tabs are cut at the right edge on phones: it's a horizontally scrolling tab bar.
- The French assistant answer in one capture read "Veuillez réessayer dans quelques minutes": the assistant's per-IP rate limit, triggered by this run's own volume.
- Arabic inner-page headers keep the text on the right (standard RTL). Their photos vary per page, so with the side shade (L6) the text stays legible on any photo.

## Not covered
- **Safari/iOS** (not available here).
- **Production screenshots of every state:** Cloudflare rate-limits automated tools from this IP, so production was checked by spot checks only.
