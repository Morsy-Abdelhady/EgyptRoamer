# Egypt Roamer

An affiliate-first travel publication about Egypt, built on WordPress and live at https://egyptroamer.com (GoDaddy Managed WordPress). The site takes no bookings and no payments.

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
- **Keep indexing off.** "Discourage search engines" stays ticked until the launch gate in `docs/LAUNCH-GATE.md` passes.
- **Don't invent anything.** No content, translations, prices, ratings, reviews, photos, statistics or affiliate providers/URLs.
- **Photography:** only brand assets are owned. On 2026-09-28 the owner approved the prototype's Unsplash photos as hot-linked stand-ins on every page until a featured image is set. No other photos, and nothing is downloaded into the Media Library without asking.
- **No redesign, and don't touch the approved CSS.** WordPress-only CSS goes in `assets/css/pages.css`.
- **Plugins:** at most one SEO plugin (Rank Math, planned). No WooCommerce, direct booking or extra cache plugins.
- **Seed:** re-runs must never overwrite editors' titles, bodies, status or filled fields.
- **Line endings:** files use LF. Python on Windows writes CRLF, so pass `newline="\n"`.

## Production access
- Everything production-side goes through the owner:
  - wp-admin in their Chrome;
  - SSH in their terminal. The WordPress root is `~/html`; running `wp` from `~` fails.
- Theme and Core deploy themselves: a push to `claude/dreamy-bardeen-6qqk2h` runs `.github/workflows/deploy.yml` (see `docs/DEPLOY.md`). Database changes still go through the owner.
- Never ask for, print or store credentials.
- Bump the Core version on each release. `track.js?ver=` in the page source shows which build is live.

## Local testing (Windows)
- `C:\Users\morsy\er`: WordPress 7.1.2 on SQLite, plus Polylang.
- Run `python proxy.py` to serve it at :8080 (login admin/admin).
- `./wp.sh` wraps WP-CLI, and `./reset.sh ml` rebuilds the site the same way production is built.
- The QA scripts in `tools/qa/*.mjs` need Playwright with the system Chrome (Node 16, so use playwright@1.40).

## Working style
- Do one task per request.
- Report results in a few lines; long reports go into `docs/`.
