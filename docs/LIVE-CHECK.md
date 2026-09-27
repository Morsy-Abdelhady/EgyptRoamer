# Live check: https://egyptroamer.com (27 Sep 2026)

This check was read-only and ran from the cloud session: curl, plus headless Chromium through the session's egress proxy.

- **Nothing was changed on the site.** No logins, no form submissions, no content edits.
- **Settings and data were not readable.** There was no WP admin access, so anything that needs the dashboard is **NOT VERIFIED**.

## What is deployed

| Item | Finding |
|---|---|
| Domain | **`egyptroamer.com` is the primary domain, not a staging URL.** `www` → 301 → apex |
| Build | Egypt Roamer theme (38 theme assets on the homepage) and Egypt Roamer Core (`track.js`) are active; `window.ER_DATA` is present |
| WordPress | 7.1.2 (core asset versions) |
| PHP | **NOT VERIFIED** (not exposed publicly; check Site Health) |
| Other plugins | GoDaddy's own `godaddy-launch` must-use plugin. **Polylang: not active** (no hreflang; `/fr/ … /ar/` all 404). **Rank Math: not active** (the description meta comes from Core's fallback) |
| Hosting edge | Cloudflare, in front of GoDaddy's cache gateway |
| SSL | valid: `CN=egyptroamer.com`, issued by Google Trust Services (WE1), expires 25 Dec 2026. Plain `http://` could not be tested (the session proxy refuses it) |

## Findings

| # | Severity | Finding | Evidence | Action |
|---|---|---|---|---|
| 1 | **CRITICAL** | **GoDaddy's CDN caches `/go/` for 31 days.** Core sends `Cache-Control: no-store, …, private`, but the edge replaces it with `public, max-age=2678400`. The first request is a MISS; every later request is a HIT. With a live offer, only the first visitor's click would reach WordPress and be logged. Later clicks would get a stale redirect, and pausing an offer or changing its URL would not apply for up to 31 days | `/go/gate-8570/` #1 MISS, #2 HIT (`age: 0`); the same for `?pl=`, `?src=`. The edge **bypasses** its cache only when the query string starts with `nocache`: `/go/x/?nocache=1&pl=hero&src=12` was DYNAMIC ×3 with the origin's `no-store` intact, while `?pl=hero&nocache=1` was still cached | **Root cause and final fix: see [LAUNCH-GATE.md → Affiliate redirect architecture](LAUNCH-GATE.md#affiliate-redirect-architecture)** (commit `a9086ce`): `/go/{slug}/` is now a stateless hop to WordPress's uncached `admin-post.php` handler, so the plain URL works behind this cache. The interim `?nocache=1` on CTA links (commit `99300d3`) stays until the live test passes. It passed locally: acceptance 14/14 on rerun (one first-run failure right after a cold server restart, not reproduced), and the click is logged with its placement. **Re-upload the plugin**, then verify with a test offer (below). Also ask GoDaddy support for a cache exclusion on `/go/*`: the `nocache` bypass is GoDaddy behaviour we measured, not a documented setting |
| 2 | HIGH | **The site is on the primary domain.** The plan was staging | domain above | Keep "Discourage search engines" on (it is on). Decide whether this domain is the staging site, or create a GoDaddy staging copy |
| 3 | HIGH | **The site title is GoDaddy's default:** "1284039.eu11.myftpupload.com Managed WordPress Site". It appears in every `<title>` and in the REST index | every page title | Settings → General → Site Title = "Egypt Roamer" (tagline optional). A content setting, not code |
| 4 | HIGH | **The content structure was not created** (`wp egypt-roamer seed` was not run). There are no destinations (`/destinations/cairo/` 404), experiences, Journal, trust or legal pages, or menus. The homepage is set to "Your latest posts" (`body.home.blog`). The primary menu is empty; the footer has one link | page inventory; homepage source | Run the seed over SSH/WP-CLI (STAGING-DEPLOY.md step 8), or create the pages and menus by hand. Set Settings → Reading → static front page |
| 5 | MEDIUM | **WordPress samples are published:** `/sample-page/` and `/hello-world/` (200). The homepage links to "Hello world!" | inventory | The seed trashes them; otherwise trash them by hand |
| 6 | MEDIUM | **No languages:** Polylang is not installed, so only English exists | `/fr/`…`/ar/` 404 | STAGING-DEPLOY.md step 9, only when translations are reviewed |
| 7 | MEDIUM | **The admin username is public** (`/wp-json/wp/v2/users` lists `morsy`). This is WordPress's default behaviour | REST response | Use a display name that differs from the login name, or a separate admin login; strong password + 2FA |
| 8 | INFO | Indexing is off: every page is `noindex, nofollow`, and `/wp-sitemap.xml` and `/sitemap_index.xml` return 404 | headers + source | Keep it this way. robots.txt has no `Disallow: /go/` line; Core adds it only while the site is public, by design |
| 9 | INFO | Pages are cached at the edge (`cf-cache-status: HIT`, `max-age` 31 days). No WordPress cookies are set; only Cloudflare's `__cf_bm` bot cookie | headers | After content edits, use "Flush cache" in the GoDaddy admin bar if a change does not show |
| 10 | INFO | Core security on the live host: `/wp-json/wp/v2/er_offer`, `er_provider` and `er_message` → 401 for anonymous users. `?post_type=er_offer` shows nothing (it falls back to the homepage). `xmlrpc.php` → 403 (GoDaddy). `/wp-content/debug.log` → 404. `/go/unknown/` → 404 with `X-Robots-Tag: noindex, nofollow` | curl | none |
| 11 | INFO | GoDaddy injects its own scripts from `img1.wsimg.com` into every page | page requests | GoDaddy platform behaviour; the session network blocks that host, so its effect is NOT VERIFIED |

## NOT VERIFIED (and why)

| Item | Reason | Manual check |
|---|---|---|
| Affiliate CTA → `/go/` → click recorded → provider, twice | no offer exists on the site | After re-uploading the plugin, create a test provider (allowed domain `example.org`) and a test offer (`https://example.org/`), attached to one published page. Open the page in a private window and click the CTA **twice**. **Pass:** the link starts `/go/<slug>/?nocache=1&`, both clicks land on example.org, and Egypt Roamer → Click reports shows **2 rows**. Then change the URL and pause the offer, click again each time, and check the new target and then the fallback. Delete the test data afterwards |
| Responsive / visual QA (390–1920), mobile menu, logo rendering | the session proxy randomly drops the site's CSS and font requests (`ERR_TOO_MANY_RETRIES`, `ERR_TUNNEL_CONNECTION_FAILED`), so browser results were not trustworthy. curl fetches the same assets with 200. Logos that did load had the expected ratios | Check in your own browser at 390 / 768 / 1440 wide (DevTools device toolbar). The identical packages passed 11 page types × 6 widths locally with 0 overflow, 0 JS errors and 0 axe violations |
| PHP version, active plugin list, Site Health | needs WP admin | Tools → Site Health → Info |
| Email | no mailer configured (unknown) and no admin access | SETUP task 13 / STAGING-DEPLOY check 13 |
| Rank Math, Polylang | not installed | install on staging per SETUP |
| Languages, RTL, filters with content, destination/tour/guide/article detail pages | no such content exists yet | re-test after the seed/content |

## Status

**NOT READY.** The code is deployed, but:

- `/go/` caching needs the re-uploaded plugin plus a click test;
- the site title, content structure, samples and languages are not set up;
- it runs on the primary domain.
