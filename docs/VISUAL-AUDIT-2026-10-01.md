# Visual + UX audit (2026-10-01, evening)

Trigger: the owner's production screenshot showed two defects that the automated suites had not caught:
- an invisible "Chat with Egypt Roamer" button in the Arabic Trip Assistant;
- an Arabic hero that looked cut on the right.

**Automated checks (axe, geometry, overflow, status codes) are not a visual audit.** This audit inspected real rendered screenshots, assembled into contact sheets and looked at state by state, using:
- `tools/qa/visual-sheets.mjs` (hero, hero states, assistant states, templates, navigation);
- `tools/qa/visual-adversarial.mjs`;
- `tools/qa/sheets.py`.

## Coverage
| Sheet | Languages | Widths | States |
|---|---|---|---|
| Homepage hero | en, ar | 320, 360, 390, 430, 768, 1024, 1280, 1440, 1920 | after the intro |
| Homepage hero | de, fr, it, es, ru, zh | 320, 1440 (+360 for fr, ru, de, es) | after the intro; phone dock |
| Hero states | en, ar | 390, 1440 | pinned mid-scroll (Pyramids), a later scene (Nile), drawer open over the hero |
| Trip Assistant | ar (full); every language by `chat-i18n.mjs` | 320, 390, 768, 1440 | closed, open, suggestion answer, chat form, waiting for the team, failed/offline (+ with the team, back to AI, closed in chat-i18n) |
| Templates | en, ar | 390, 1440 | destinations archive, destination (en), experiences archive, 404 |
| Navigation | en, ar | 390, 1440 | mobile menu, language switcher, footer |
| Adversarial | en, ar, de | 320–1440 | photos missing, reduced motion, logged in (toolbar) with drawer and menu, keyboard focus in the drawer, slow network (2 s / 5 s), long German and Arabic chat text, long names |

## Defects found → root cause → fix → evidence
| # | Defect (what the screenshot showed) | Root cause | Fix | After |
|---|---|---|---|---|
| V1 | **Arabic hero:** text block over the Great Pyramid (right side covered or cut); bare sun glaring under the note (note looked cut) | the RTL layout mirrors text, shade and note, **but not the photo**, whose subject is on its right and sun on its left | RTL mirror of the opening photos (`scale: -1 1`, composes with GSAP) | hero-ar sheet at 320–1440: subject opposite the text, sun under the shade, note legible. **Trade-off for the owner:** the photo of a real landmark is shown mirrored in Arabic |
| V2 | **Invisible chat button (owner's screenshot)** | the screenshot is a page cached before 1.2.15 (ghost button 1.1:1); current production has the outline button (16.4:1, verified on `/ar/` and `/de/`). Such copies have no stale-page guard and live up to 31 days | (a) the assistant script, fetched fresh when the drawer opens, reloads a page without a build id once; (b) the ghost style inside the drawer is now readable even on old markup | `legacy.mjs`: old page → one reload → current page; with the reload suppressed, the button text is dark |
| V3 | Empty drawer on opening (no welcome) | not designed | welcome line (1 new string, drafted in 7 languages, unreviewed) | assistant and hero-states sheets |
| V4 | Privacy Policy link in the chat form indistinguishable from text | `color: inherit`, no underline | underline | – |
| V5 | **fr 320:** "Planifier mon voyage" cut by the dock button; **ru 320:** label touching the edges | fixed 48 px height, 0.82 rem label | ≤ 400 px: 0.74 rem, line-height 1.15, `min-height`, inline padding | dock crops fr/ru/de/es at 320 and 360 |
| V6 | **Logged-in editors:** the toolbar covered the drawers' title and close button; below 600 px it covered the header (menu button not clickable) | drawers at `inset: 0`; header returned to `top: 0` below 600 px while the toolbar was still on screen | drawers and menu below the toolbar; toolbar fixed on small screens too | logged-in sheet: ar 390, en 700, en 1280 |
| V7 | **ar 320:** the round launcher 12 px from the play button read as a third hero button | overlap test counted true overlaps only | "too close" (20 px) counts; it steps aside and returns on scroll | herohit2: ar 320 hidden at the top, shown from 0.6 screen heights |

## Checked visually and found right
- **Hero:** title, text and buttons inside the composition at every width and language; nothing clipped at either edge; pinned states (titles, Nile route card mirrored, counter); drawer open over the hero.
- **Templates:** headers, breadcrumbs, titles, filters, search, 404 actions, mirrored in Arabic.
- **Navigation:** mobile menu (RTL order), language switcher, footer columns, newsletter, legal row; the launcher never covers the legal links.
- **Assistant:** suggestion results, form fields and focus ring, chat bubbles on the reading side (right in LTR, left in RTL), failed state with retry, status lines.
- **Adversarial:**
  - photos missing: neutral fallbacks, text readable;
  - reduced motion: the stacked journey;
  - keyboard focus rings visible on the light drawer;
  - long German compound words and long Arabic messages wrap inside the bubbles;
  - slow network: blank for about 2 s while the HTML arrives, then the loader.

## Not visually covered (remaining)
- **Other locales' assistant states:** de, fr, it, es, ru and zh are checked by `chat-i18n.mjs` (translated, not clipped, contrast ≥ 4.5:1) at 320 and 1440, but only the Arabic states were inspected by eye, plus the German and Arabic long-text states.
- **Experience single pages in non-English languages** (the English template and the archives were inspected).
- **Safari/iOS:** not available here.
- **Production screenshots** after 1.2.17: Cloudflare rate-limits automated clients from this IP. The in-app browser works for a few pages.

## Production verification (theme 1.2.17, run #34, after a GoDaddy flush)
Anonymous in-app browser and the owner's Chrome. Cloudflare still rate-limits automated tools from this IP.

| Item | Evidence on production |
|---|---|
| V1 Arabic hero, 1440 px | screenshot: Great Pyramid free on the left, title/text/buttons on the right, note legible on clear sky (the first frame caught the title's entrance animation; the title was confirmed present and visible) |
| V2 stale page heals | the in-app browser held `/destinations/?ui=1216` from build `08115d9294a8`; on revisit it **reloaded itself once** (navigation type `reload`, marker = old build) and showed build `de315fa1c25d` = the live `/build` endpoint |
| V2/V3 Arabic drawer, 390 px | screenshot: welcome line; "تحدث مع Egypt Roamer" dark outline on light, clearly visible |
| V5 French dock, 320 px | screenshot: "Planifier mon voyage" inside the button |
| V6 logged in (owner's Chrome, Arabic, desktop) | toolbar bottom 32 px = drawer header top 32 px = site header top 32 px |
| V7 | local only (herohit2) |
