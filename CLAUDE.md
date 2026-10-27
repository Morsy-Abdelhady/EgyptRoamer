# Egypt Roamer

An affiliate-first travel publication about Egypt, built on WordPress and live at https://egyptroamer.com (GoDaddy Managed WordPress). The site takes no bookings and no payments.

## Current state: read this first
- **Latest handoff:** `docs/HANDOFF-2026-10-25.md`. Live versions: Theme 1.3.37 / Core 1.2.92. The balloon trip is live in 8 languages with Klook offer #967; page-coverage passed on 392 pages on 25 Oct. The guide-sync title comparison was fixed in Core 1.2.81 (27 Oct). The prototype parity review (`docs/PROTOTYPE-PARITY-2026-10-18.md`) records the main screens as compared on 19 Oct; see the handoff for remaining differences and SEO follow-up dates.
- **Growth plan (owner-approved 2026-10-26):** `docs/GROWTH-PLAN-INTENSIVE-2026-10-26.md`. Goal: 1,000,000 GA4 sessions a month, stretch target December 2027 (very limited ad budget), safer target late 2028; checkpoints in its §4. Its §5 is the current week's work; its §3 lists the six owner decisions applied below.
- **Live partners:**
  - Viator (tours, 17 offers);
  - GetYourGuide (tours, 9 offers);
  - Trip.com (hotels, 13 offers);
  - Klook (tours, 5 offers; AID 140689; `docs/KLOOK-OFFERS-2026-10-25.md`; API access requested 26 Oct).
  - Details: `docs/PARTNER-OPTIONS-2026-10-16.md`.

## Layout
- `wordpress/wp-content/plugins/egypt-roamer-core/`: business logic.
  - Content types, the affiliate engine and `/go/` redirects, clicks, leads, SEO guards.
  - The seed and WP-CLI commands live in `includes/cli.php`.
- `wordpress/wp-content/themes/egypt-roamer/`: the approved design as a theme.
  - Edit JS in `src/js`, then rebuild with `python3 tools/build.py theme`.
- `Egypt Roamer/`: the approved static prototype. It is the design reference and is **never deployed or edited**.
- `docs/`: start with `SETUP.md`. The latest audit is `docs/FULL-AUDIT-*.md`.
- `dist/`: release zips (gitignored). Rebuild them after changes; the theme zip must not include `src/`.

## Hard rules
- **Indexing is on** (since 2026-10-02, after the launch gate in `docs/LAUNCH-GATE.md`). New pages are sent automatically by IndexNow. Google and Bing requests are logged in `docs/SEO-RECOVERY-CHANGELOG.md`.
- **Don't invent anything.** No translations, prices, opening hours, travel times, ratings, reviews, photos, statistics or affiliate providers/URLs.
- **Editorial content** (owner-approved on 2026-09-28):
  - English bodies are written in `content/editorial/en/*.md`, using only verifiable facts. Sources go in `docs/CONTENT-SOURCES.md`.
  - Run `python tools/editorial.py` to compile them into Core's `data/editorial/en/`.
  - On the server, `wp egypt-roamer editorial` fills only empty or untouched seed text.
  - **Official sources first** (owner, 2026-10-26): the Ministry of Tourism and Antiquities (`egymonuments.gov.eg`, `egypt.travel`) and the EEAA (`eeaa.gov.eg`), then UNESCO. Place pages are built from the Ministry's monument/museum pages and the EEAA's protected-area pages, with only the facts those pages state.
  - **Publishing** (owner, 2026-10-26): Claude publishes guides and pages once they pass the release gate (local checks, then `page-coverage.mjs` on production). No separate owner approval per page.
  - **Translations** (owner, 2026-10-26): Claude reviews each translation professionally against the English, sentence by sentence, and the owner's approval stands. Record it truthfully in the front matter: `reviewer: Claude (AI translation review, not a native speaker); approved by the owner on <date>`. Never claim a native-speaker review. Example log: `docs/TRANSLATION-REVIEW-2026-10-26.md`.
- **Photography:** only brand assets are owned. Allowed photos:
  - the prototype's Unsplash photos, approved 2026-09-28 as hot-linked stand-ins;
  - new **free** Unsplash photos whose **location tag names the exact Egyptian place** (owner, 2026-10-03). Record each in `docs/IMAGE-QUALITY-AUDIT-2026-10-02.md` §5.
  - **Wikimedia Commons** photos of the exact place (owner, 2026-10-26: "take the photos from Wikipedia"), free licences only (CC BY, CC BY-SA, public domain). Each shows "Photo: author, Wikimedia Commons, licence" under it: add the file to `er_commons_credit()` in the theme's `inc/template-tags.php` and record it in the audit's §9. Use a `commons:File name.jpg` id in extraStock or `inc/world-guide-photos.php`.

  Rules:
  - **Media Library** (owner, 2026-10-26): the main hero photos may be copied into the Media Library and served from our domain for speed, using only the allowed Unsplash photos above, with their credit kept. Anything else still needs asking first.
  - **Partner photos are never copied** from partner pages. GetYourGuide's T&Cs forbid it; Viator supplies photos only through its Partner API.
  - GetYourGuide offers use a free tagged Unsplash photo of the same subject (owner, 2026-10-16).
- **Partner facts:** offer facts (private or group, duration, pickup) come only from the partner's own product page, in Core `data/offer-facts.json`. No prices, ratings or reviews. Every new offer needs its card text in `data/offers-i18n.json` before it is published.
- **Design (owner, 2026-10-18):** the approved design is now proposal 4 (`docs/prototypes/journey-v4/`, published at https://claude.ai/artifact/8Nni2DJA4aBkoDwc2WxEt7). The theme may be redesigned to match it; the old "no redesign" rule is lifted. New design CSS goes in its own files under `assets/css/`, and nothing is invented (content, facts and photos rules below still apply).
- **Plugins:** at most one SEO plugin (Rank Math, planned). No WooCommerce, direct booking or extra cache plugins.
- **Seed:** re-runs must never overwrite editors' titles, bodies, status or filled fields.
- **Line endings:** files use LF. Python on Windows writes CRLF, so pass `newline="\n"`.

## Production access
- **Owner permissions:** the owner granted all project permissions on 2026-10-16.
  - Claude works in wp-admin and the partner portals through the owner's **Edge** (the claude-in-chrome tools).
  - Still never handle credentials, payment or bank data, purchases or CAPTCHAs.
  - Accepting terms, sending messages and submitting forms each need the owner's explicit OK.
- **SSH** runs in the owner's terminal. The WordPress root is `~/html`; running `wp` from `~` fails.
- **After every deploy,** flush the GoDaddy cache: wp-admin admin bar → `#wp-admin-bar-wpaas-flush-cache a`.
- Theme and Core deploy themselves: a push to `main` that touches them runs `.github/workflows/deploy-production.yml` (see `docs/DEPLOY.md`). There is no manual trigger. Database changes still go through the owner.
- After a theme or Core change passes the local tests, commit and `git push origin main`. Don't stop at a local commit.
- Never ask for, print or store credentials.
- Bump the Core version on each release. `track.js?ver=` in the page source shows which build is live.

## Local testing (Windows)
- `C:\Users\morsy\er`: WordPress 7.1.2 on SQLite, plus Polylang.
- Run `python proxy.py` to serve it at :8080 (login admin/admin).
- `./wp.sh` wraps WP-CLI, and `./reset.sh ml` rebuilds the site the same way production is built.
- The QA scripts in `tools/qa/*.mjs` need Playwright with the system Chrome. Since 2026-10-24: Node 24 LTS and Playwright 1.64, installed with `npm i --no-save playwright axe-core` in `tools/qa` (kept out of git by `.git/info/exclude`).
- Before reporting any UX or release as done, run `BASE=https://egyptroamer.com node tools/qa/page-coverage.mjs` after the deploy. It checks every page in every language; sample pages are not enough.
  - Also look at the changed pages in a browser, on desktop and on a phone, in English and Arabic.
  - Also run `BASE=https://egyptroamer.com node tools/qa/rtl-direction.mjs`: on every Arabic page, arrows must point left (after any CSS mirroring) and number ranges must be isolated so they don't show reversed (owner caught both on 28 Oct).
  - The QA does not cover the `?ux=2` preview or the hotel boxes.
- **Windows quirks:**
  - Build with `PYTHONUTF8=1`, or `tools/build.py` and `tools/build-translations.mjs` crash on Arabic.
  - **Avast** (confirmed 26 Oct 2026): its HTTPS scanning makes Git Bash `curl`, PHP and WP-CLI fail with SSL errors, and its Web Shield cuts about 1 local request in 20 at 64 KB (pages and files on :8080). While it is on, use PowerShell `curl.exe` or `node -e "fetch(...)"`. The fix is the owner's (Avast exceptions or HTTPS scanning off): `docs/SETUP.md` §4. Never change antivirus settings yourself.
  - `.ps1` scripts are blocked by the execution policy; don't change it.
- **UI strings** must be literal `er_t( '…' )` calls, or they are never collected for translation. Add them to `tools/i18n/new-strings.json`, then run `node tools/build-translations.mjs`.

## Working style
- Do one task per request.
- Report results in a few lines; long reports go into `docs/`.
- The owner writes in Egyptian Arabic. Reply in Egyptian Arabic.
- Say plainly what is not done. The owner has caught several overclaims; review the real pages before saying "done".
