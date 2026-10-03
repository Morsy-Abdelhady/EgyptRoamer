# Duplicate navigation incident — audit — 2026-10-03

Reported: <https://egyptroamer.com/destinations/luxor/> shows duplicated navigation ("nav" appears twice).

Scope: inspection only. **Nothing was changed, edited or deployed.** Production is theme 1.2.35 / Core 1.2.21.

Labels: **OBSERVED** means measured or seen. **INFERRED** means reasoned from observations. **RECOMMENDED** means a proposal.

## Verdict

**NOT REPRODUCED.** I could not find duplicated navigation on Luxor, or on any other production page, in any state I could trigger:

- fresh browser and your logged-in Chrome;
- 19 widths from 320 to 1920 px;
- scrolling down and back up;
- 8 languages;
- 228 URLs.

Each navigation component has exactly one render path in the code. **No root cause can be named or fix proposed honestly yet.** I need the state you saw (see §7).

## 1. Luxor (OBSERVED)

**Server HTML** of `/destinations/luxor/`, one of each:

| Component | Element | Rendered by |
|---|---|---|
| Site header | `header.nav#nav` | `header.php:66` |
| Primary links (desktop) | `nav.nav__links[aria-label=Primary]` | `header.php:72` |
| Breadcrumb | `nav.crumbs` | `er_breadcrumbs()` in `inc/template-tags.php:391`, called once by `er_page_hero()` |
| Section tabs ("On this page") | `nav.secnav` | `er_section_nav()` in `inc/sections.php:131`, called once by `single-er_destination.php:63` |
| "Things to do in Luxor" box | `nav.glance.glance--things` | `single-er_destination.php:37` (sidebar) |
| Footer links | `nav.footer__cols` | `footer.php:49` |
| Mobile quick-action dock | `nav.dock` | `footer.php:74` |
| Mobile menu | `nav.menu#menu[hidden]` | `footer.php:83` |
| Assistant button | `button.assistant-launch` | `footer.php:166` |

**Rendered page.** I checked 19 widths (320, 360, 390, 414, 600, 768, 834, 900, 960, 1024, 1100, 1180, 1199, 1200, 1280, 1366, 1440, 1536, 1920), after scrolling the whole page so every script ran:
- 1 visible header, 1 breadcrumb, 1 set of section tabs at every width;
- the desktop primary links are visible only at ≥ 1280 px; below that, 0;
- the mobile menu is hidden at every width (it opens only from the menu button);
- the dock is visible only at ≤ 900 px; the sidebar "Things to do" box only at ≥ 1200 px.

**While scrolling** (real mouse-wheel scrolling at 320, 390, 768, 1280 and 1440):
- going down, the header hides and the section tabs stick at the top: 1 bar;
- going up, the header comes back and the tabs sit **below** it (64 px on phones, 84 px on desktop): 2 different bars, by design (`pages.css:846`, plus `pages.css:658` with the toolbar).

**Your Chrome** (logged in, WordPress toolbar, 1528 px wide):
- top of the page, scrolled down and scrolled up: same result.
- One mid-scroll frame showed the header in its "light" state. That state is 86% opaque with an 18 px background blur (`layout.css:105`), so blurred page content showed faintly through the bar while the colours were still changing. The logo is swapped with `display`, not cross-faded (`layout.css:157`), so two logos can't render. INFERRED: this is a translucency effect, not a second navigation.

**Other checks:**
- no stray "nav" text in the visible page (EN, DE, Cairo);
- no script creates or clones a navigation element (`src/js`: no `createElement('nav')`; the only `cloneNode` is the preview video).

## 2. Exact duplicate component

None found. Interpretations considered and **not** confirmed:

| Interpretation | Finding |
|---|---|
| Two navigation bars at the top after scrolling up (header + section tabs) | Intended stacked state, two different components (site navigation + page sections). Could *look* like "nav twice". |
| Primary links and mobile menu both shown | Never at the same time, at any of 19 widths |
| "Things to do in Luxor" twice (sidebar box + section further down) | Two presentations of the same list at ≥ 1200 px: sidebar shortcut (first 3) + full card section. It's content repetition, not a duplicated component. |
| Two landmarks named "Primary" | OBSERVED: `nav.nav__links` and the hidden `nav#menu` share the label "Primary" (English only; other languages translate it). A screen reader or audit tool listing landmarks may show "Primary navigation" twice. The menu is `hidden`, so it's only exposed while open. |
| Header "ghost" mid-scroll | Translucent blurred bar (see above) |

## 3. Root cause

**Not established.** Each component has exactly one render path:
- the section tabs are called once in each of `single-er_destination.php`, `template-parts/single-commercial.php`, `single-er_guide.php` and `page.php`;
- the breadcrumb is called once in `er_page_hero()`;
- the header is in `header.php`; the dock and menu are in `footer.php`.

A template-level double render would need a second call, and there isn't one.

## 4. Affected templates

None confirmed.

## 5. Affected pages: site-wide audit (OBSERVED)

228 production URLs at 390 and 1280 px = **456 page/width checks, 0 violations**:
- all 7 destinations and 8 experiences in 8 languages;
- the 4 English guides and the guide archive;
- every page (legal, contact), every archive;
- search and empty search, and the 404 page in 8 languages.

Rules checked on each:
- at most 1 visible header, primary navigation, breadcrumb, section tabs, "On this page" box, sidebar glance, dock and assistant button;
- desktop primary links and mobile menu never visible together;
- section tabs and an "On this page" box never visible together;
- no two visible navigation elements with the same text.

Visual review: full-page screenshots of all 7 destinations, 8 experiences and 3 guides at 390, 768, 1280 and 1440 (72 images). No duplicated navigation. In the phone captures the dock and assistant button appear mid-page; that's an artefact of full-page screenshots of fixed elements.

## 6. Other duplicates

- **Landmark label "Primary" used twice** (desktop links + mobile menu). The menu is hidden unless open, so it isn't a visible defect.
  - RECOMMENDED (small, low risk, after your approval): give the mobile menu its own label, e.g. "Menu", so landmark lists don't show "Primary" twice.
- **"Things to do in {destination}" heading text used twice** on desktop (sidebar label + section heading). This is intentional (sidebar shortcut). Flagged only because it may read as repetition.

## 7. Proposed fix

None until the defect is reproduced. To pin it down I need one of:

1. **A screenshot** of the duplicated navigation (the whole browser window).
2. **Context:** device and browser, the window width (or "phone"), whether you were logged in, and when it appeared (on load, after scrolling down, after scrolling back up, after opening the menu, after changing language, after rotating a phone).
3. **The language** of the page (English or a translation).

If it came from a tool (screen reader landmark list, WAVE, Lighthouse) rather than the screen, the likely match is the double "Primary" landmark in §6.

## 8. Regression-risk assessment

No change was made, so there is no risk from this audit. Any later fix should be judged against the counts above: header, tabs and sidebar show and hide at different widths, and the header/tab stacking depends on the `#nav.is-hidden` state.

## 9. Test to prevent recurrence (RECOMMENDED)

The structural check used here (`navaudit2.mjs`, local QA tooling):
- loads each page and scrolls it once;
- for each component (header, primary navigation, breadcrumb, section tabs, "On this page" box, sidebar glance, mobile menu, dock, assistant button, footer navigation, journey navigation) counts the elements in the DOM and the visible ones;
- fails when more than one of a kind is visible, when the desktop links and the open-only mobile menu are visible together, when tabs and an "On this page" box are visible together, or when two visible navigation elements carry the same text.

Responsive variants that exist in the DOM but aren't visible at that width pass, as required. RECOMMENDED: add it to `tools/qa/` as `nav-structure.mjs` and to the regression run, at 390, 768, 1280 and 1440, scrolled down and then up.
