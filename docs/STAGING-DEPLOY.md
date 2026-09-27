# GoDaddy staging deployment runbook

This runbook deploys the tested build to **GoDaddy Managed WordPress staging** only. It never touches production, and it keeps indexing off throughout.

## Build being deployed

| | |
|---|---|
| Branch | `claude/dreamy-bardeen-6qqk2h` |
| Commit | `c670b125468047224ce59c886db060c97e661b84` |
| Plugin package | **`egypt-roamer-core-a9086ce0098d.zip`** (commit `a9086ce`: `/go/` served through a stateless hop to WordPress's uncached `admin-post.php` handler; see [LAUNCH-GATE.md](LAUNCH-GATE.md#affiliate-redirect-architecture)). SHA-256 `f75ade152029770cc1f5a27e2127cda7d38ad326fe0cd4ac66203becf4c1903f`. Replaces the earlier `c670b1254680` and `99300d3c6eee` packages. **After uploading it, flush GoDaddy's cache once** |
| Theme package | `egypt-roamer-theme-c670b1254680.zip` (127 files; the theme's `src/` source folder is excluded). SHA-256 `7d68d2b5b0efd08a91723f0a5ff78674b7901d9244e3624861c361b103f6e419` |

**Rebuilding the packages.** Both are built with `git archive` from the commit above; nothing is added or changed:

```
git archive --format=zip --prefix=egypt-roamer-core/ -o egypt-roamer-core-c670b1254680.zip c670b125:wordpress/wp-content/plugins/egypt-roamer-core
git archive c670b125:wordpress/wp-content/themes/egypt-roamer | tar -x -C egypt-roamer && rm -rf egypt-roamer/src && zip -r egypt-roamer-theme-c670b1254680.zip egypt-roamer
```

## Pre-deployment checks (done locally on this commit)

| Check | Result |
|---|---|
| Working tree equals the pushed branch head | yes (0 uncommitted changes; local HEAD = origin) |
| Secrets in the packages (API keys, passwords, tokens, private keys, cloud keys) | none (the only "token" hits are CSS design tokens) |
| Debug output (`WP_DEBUG`, `var_dump`, `print_r`, `console.log`, `SAVEQUERIES`, `display_errors`) | none |
| Hardcoded local hosts (`127.0.0.1`, `localhost`, dev ports) | none |
| Test data in the seed (`example.org/com`, test providers, QA items) | none. The seed has no prices, ratings, reviews, providers or live offers |
| Local lab test data (test providers/offers, QA pages) | lives only in the lab database, which is **not** deployed |
| Clean-install test of the zips (fresh WP 7.1.2, PHP 8.4) | installed via `wp plugin/theme install <zip> --activate` and seeded with no errors; 11 page types × 390/430/768/1024/1440/1920: 0 overflow, 0 JS errors, 0 axe violations, no PHP warnings. With "Discourage search engines" on: every page `noindex`, sitemap 404, `/go/` sends `X-Robots-Tag: noindex` |

**Known LOW issue, not changed during deploy:** while "Discourage search engines" is on, gated pages output `robots: noindex, nofollow, follow`. The directives contradict each other, but they are harmless: crawlers apply the most restrictive one. This is cosmetic and is fixed separately, not during this deployment.

## Deployment steps (GoDaddy dashboard + WP admin)

1. **Backup.** In the GoDaddy dashboard, create a staging site if none exists (Managed WordPress → your site → Staging → Create). Then take a backup of staging (Backups → Create backup) and write down its timestamp.
2. **PHP.** Set staging PHP to **8.2 or newer** (Settings → PHP version).
3. **Search visibility.** In staging WP admin → Settings → Reading, tick **"Discourage search engines from indexing this site"** and save. Do this before anything else.
4. **Blocklist.** Compare the plugins below with GoDaddy's blocklist: <https://www.godaddy.com/help/blocklisted-plugins-8964>.
5. **Plugin.** Plugins → Add New → Upload → `egypt-roamer-core-c670b1254680.zip` → Activate.
6. **Theme.** Appearance → Themes → Add New → Upload → `egypt-roamer-theme-c670b1254680.zip` → Activate.
7. **Permalinks.** Settings → Permalinks → Save (this flushes the rewrite rules).
8. **Content structure.** In SSH/WP-CLI, if the plan includes it, run `wp egypt-roamer seed`. It creates:
   - published: destinations and experiences (noindex), a static front page and a Journal page;
   - drafts: guides, trust/legal pages, and offers (paused, no link);
   - menus.

   It trashes WordPress's untouched sample page and post. Without SSH, create these by hand; everything is editable in the admin. Do **not** use `--with-images` (it would hot-load Unsplash stand-ins).
9. **Polylang.** Install it from Plugins → Add New ("Polylang", free) and activate it. In Languages:
   - Add English (default), then French, German, Italian, Spanish, Russian, Chinese (zh_CN) and Arabic.
   - Settings → URL modifications: "The language is set from the directory name in pretty permalinks", and **tick** "The front page URL contains the language code".
   - Leave **"Detect browser language" off**.
   - Then run `wp egypt-roamer seed --translations`. It assigns existing content to English, creates **draft** translations (nothing is published beyond each language's front page and Journal page) and per-language menus.

   **Do not publish any translation before native review.**
10. **Flush.** Settings → Permalinks → Save again, then flush GoDaddy's cache (WP admin bar → "Flush cache").
11. **Do not** install cache, backup, security, statistics or link-cloaker plugins. Rank Math, the mailer and the CMP are separate steps (SETUP tasks 9, 12 and 13).

## Test affiliate data (staging only)

Use this to exercise `/go/`. Remove it after testing.

1. Affiliate Providers → Add: title **"STAGING TEST – do not publish"**, website `https://example.org/`, allowed domains `example.org`, status active.
2. Affiliate Offers → Add: title **"STAGING TEST offer"**:
   - affiliate URL `https://example.org/`;
   - provider = the test provider;
   - attach it to one destination;
   - UTM campaign `staging-test`.

   Publish.

   Search engines are discouraged and the offer points to `example.org` (reserved for documentation), so no real traffic is misdirected.
3. **After testing:** trash the offer and the provider, then empty the trash.

## Verification on staging

Record every result. Do not mark anything verified that was not run.

| # | Test | Command / action | Pass criterion |
|---|---|---|---|
| 1 | Versions | Tools → Site Health → Info | record the WordPress version, PHP version, active theme and active plugins |
| 2 | SSL | open `https://<staging-host>/` | valid certificate; no mixed-content warnings in the console |
| 3 | Theme | homepage, header, footer, logo at 390 and 1440 | renders; logo not distorted; mobile menu opens and closes; no console errors |
| 4 | Core | wp-admin → Egypt Roamer → Dashboard, Settings, Subscribers, Click reports; Destinations, Tours, Experiences, Activities, Guides, Offers, Providers | every screen loads; no PHP fatal in the error log |
| 5 | Indexing off | `curl -s https://<staging>/ \| grep robots` | `noindex` on every page; `/wp-sitemap.xml` returns 404 |
| 6 | `/go/` not cached | `curl -sI https://<staging>/go/<test-offer-slug>/` **twice**, then once more from another network/browser | every response is `302` to `https://example.org/?utm_campaign=staging-test`, with `Cache-Control: no-store…` and `X-Robots-Tag: noindex`; **no** `Age:` > 0 or cache-HIT header; Click reports shows **one new row per request** (use a normal browser user-agent; curl's default user-agent is treated as a bot and not logged: add `-A "Mozilla/5.0 …Chrome/126"`) |
| 7 | No stale redirect | change the test offer URL to `https://example.org/v2`, save, request `/go/…` again | the new target is served immediately |
| 8 | Fallback | pause the test offer, request `/go/…` | 302 to the attached destination page; unpause afterwards |
| 9 | Security | `/go/<slug>/?url=https://evil.test` | still the configured target (no open redirect) |
| 10 | CTA | the attached destination page | the CTA link has `rel="sponsored nofollow noopener"`; disclosure text next to it |
| 11 | Page cache | `curl -sI https://<staging>/destinations/` twice | the second request is a cache hit; **no `Set-Cookie`** |
| 12 | Forms | submit the footer newsletter | "You're on the list"; a row in Egypt Roamer → Subscribers |
| 13 | Email | none (no mailer configured) | **NOT VERIFIED**. The contact message is stored and flagged "email not sent". Configure it per SETUP task 13 |
| 14 | Languages | `/fr/ /de/ /it/ /es/ /ru/ /zh/ /ar/` | `lang`/`dir` correct (`ar` = rtl); menus in each language; no English UI strings |
| 15 | Responsive | 390, 430, 768, 1024, 1440, 1920 on home, destinations, destination, tours, experiences, activities, guides, journal, search, 404, `/ar/` | no horizontal scroll, no broken images/assets, no console errors |

**Automated checks:** from any machine with Node and Playwright that can reach staging, run:

```
cd tools/qa && npm i playwright axe-core
BASE=https://<staging-host> node languages.mjs dest-urls.json   # dest-urls.json: {"en": "https://<staging>/destinations/cairo/", ...}
BASE=https://<staging-host> python3 index-gate.py '{"destination":"https://<staging>/destinations/cairo/"}' A
```

`redirect-matrix.sh` edits offers through WP-CLI, so run it over SSH on staging and only against the test offer. `acceptance.mjs` and `wp-matrix.mjs` target `127.0.0.1:8080` with admin/admin; edit their `B` constant and credentials before using them on staging.

## Rollback

GoDaddy dashboard → Backups → restore the backup from step 1. Or deactivate the Egypt Roamer theme and plugin; content stays in the database.
