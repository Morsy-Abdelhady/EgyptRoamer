# Production verification: https://egyptroamer.com (28 Sep 2026)

**Method.** Anonymous, read-only HTTP requests from the cloud session: curl, plus headless Chromium through the session's egress proxy.

- There was no WP-admin, GoDaddy, SSH or click-report access, so anything that needs them is **NOT VERIFIED**.
- **Nothing was changed on production.** The only side effect is that a few random `/go/verify-…/` and `/go/mk…/` probe URLs now sit in the edge cache as hops to `admin-post.php`, which then redirects to the home page. They are harmless and expire on their own.

## 1. What is deployed (evidence from production)

| Item | Evidence | Result |
|---|---|---|
| Egypt Roamer theme | 38 theme assets on `/`; `site.js`, `home.js` and `tokens.css` are byte-identical (SHA-256) to the repo | **deployed** |
| **New** Egypt Roamer Core (two-step `/go/`) | `/go/<unknown>/` → **302** to `/wp-admin/admin-post.php?action=er_go&offer=<slug>`. `admin-post.php?action=er_go` → **302** home (the old Core returned 404 and 400). `track.js` is byte-identical | **deployed** |
| CTA URL format | no CTA exists to render (0 published offers; no `/go/` link anywhere on the site) | NOT VERIFIED |
| Multilingual (Polylang) | no hreflang; `/fr/ /de/ /it/ /es/ /ru/ /zh/ /ar/` → 404 | **not deployed** (English only) |
| Rank Math / other SEO plugin | no Rank Math, Yoast or AIOSEO markup; `/sitemap_index.xml` 404; the description, OG and breadcrumbs come from Core's fallback | **NOT INSTALLED** |
| WordPress | 7.1.2 (core asset versions) | — |
| PHP | not exposed | NOT VERIFIED |
| Content | REST: 1 published page (`sample-page`), 1 post (`hello-world`), **0** destinations, experiences, tours and guides | seed not run |

## 2. Affiliate architecture on the live edge

| Request | Status | Cache | Headers |
|---|---|---|---|
| `/go/<slug>/` #1 | 302 → `admin-post.php?action=er_go&offer=<slug>` | `cf-cache-status: MISS`, `x-gateway-cache-status: MISS` | `Cache-Control: public, max-age=2678400` (set by the edge), `X-Robots-Tag: noindex, nofollow` |
| `/go/<slug>/` #2, #3 | 302 → the same | **HIT** (`age: 0`) | the same |
| `admin-post.php?action=er_go&offer=<slug>` ×2 | 302 | **`cf-cache-status: DYNAMIC`, `x-gateway-cache-status: BYPASS`, `x-gateway-skip-cache: 1`** | `Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private`, `X-Robots-Tag: noindex, nofollow` |

This is the designed behaviour. The static hop may be cached; the dynamic handler never is. Both are temporary 302s; there is no 301.

**Marketing parameters, observed on the live edge only.** GoDaddy's cache layer re-appends visitor `utm_*`, `gclid` and `fbclid` (but not `mc_cid` or `ref`) to the `Location` of **cacheable** redirects. It does this to the `/go/` hop and to legacy 301s. WordPress itself drops them (locally: `/go/x/?utm_source=evil&gclid=abc&pl=card` → `…&offer=x&pl=card`). The dynamic handler's redirect gets nothing appended (the live Location is exactly `https://egyptroamer.com/`).

The handler ignores visitor `utm_*`; the configured `utm_*` and sub-ID come from the offer. Locally, a visitor's `utm_source=evil&utm_campaign=evil&gclid=abc` on a live offer still produced `…?utm_campaign=acceptance`. **Attribution cannot be overridden. No action needed.**

**Security, live (these need no offer):**

| Case | Result |
|---|---|
| unknown offer | → home |
| `?url=` / `?to=` / `?redirect_to=` on `/go/` | not forwarded |
| `?offer=` / `?action=` / `subid` injection through the hop | not forwarded |
| `/go/..%2f..%2fwp-config.php/` | **400** (edge) |
| `/go/UPPER-Case/` | 404 |
| `offer=../../wp-config` | **403** (GoDaddy firewall) |
| `offer[]=` / `src[]=` / `adults[]=` | → home, no error |
| CRLF `Set-Cookie` injection | not injected |
| unregistered action | 400 |

Everything fails safely.

**NOT VERIFIED on production:** there is no test offer and no admin access, so these could not run:

- 3 clicks = 3 records;
- offer switched from provider A to B after cache;
- paused, expired or deleted offer after cache;
- unapproved and look-alike provider domains;
- `rel="sponsored"` on a rendered CTA;
- configured tracking parameters on the final redirect.

These all passed locally behind an emulation of this edge (`go-architecture.sh` 34/34; see LAUNCH-GATE.md). They still need **procedure A** on production or staging with a temporary test offer, which the owner has to create.

## 3. Indexing safety (public signals)

| Check | Result |
|---|---|
| `<meta name='robots'>` on every crawled page | `noindex, nofollow` (gated pages: `noindex, nofollow, follow`, contradictory but harmless: the most restrictive wins) |
| `/wp-sitemap.xml`, `/sitemap_index.xml` | 404; `/sitemap.xml` → 301 → 404 |
| robots.txt | WordPress default; no `Disallow: /go/` while the site is private (added automatically once public) |
| `/go/` | `X-Robots-Tag: noindex, nofollow`; in no sitemap |
| "Discourage search engines" checkbox | **NOT VERIFIED** (no admin access). The public output is exactly what WordPress produces when it is ticked |

## 4. SEO output (rendered HTML)

- **Correct:**
  - one H1 per page;
  - self-canonicals on `/` and archives;
  - `og:url` / `og:type` / `og:image` (the approved OG image);
  - `twitter:card`;
  - BreadcrumbList JSON-LD and visible breadcrumbs on archives;
  - no duplicate SEO-plugin output;
  - search is `noindex`.
- **Wrong:** every `<title>`, `og:title` and `og:site_name` carries GoDaddy's default site name: **"1284039.eu11.myftpupload.com Managed WordPress Site"**.
- **Not checkable:** empty archives are noindex through the site-wide switch. The per-page index gate cannot be exercised because there is no content.

## 5. Branding

All seven logo and mark files served in production (dark and light wordmarks at 600/1200/full size, both marks, the favicon) are **byte-identical** to the approved repo files, so the colours are unchanged from the approved artwork. They were pixel-sampled locally earlier:

- dark background: `#FFFFFF` + `#C9A227`;
- light background: `#101820` + `#C9A227`.

## 6. Links and errors

- **Links:** no internal link returns an error; every crawled page links only to 200 pages.
- **Samples:** `/sample-page/` and `/hello-world/` are published and linked from the homepage.
- **JavaScript errors:** none in the loads that completed.

## 7. Performance

| Measure | Value | Reliability |
|---|---|---|
| TTFB (curl, edge HIT) | 0.15–0.37 s for `/`, `/destinations/`, `/tours/`, `/guides/`; ~0.40 s uncached | measured from the cloud session through its proxy, **not** a visitor's view |
| HTML size | `/` 100 KB; archives 85 KB (uncompressed) | exact |
| LCP / INP / CLS / page weight in a browser | the session proxy dropped CSS, font and logo requests and retried page loads (e.g. "TTFB 9.4 s", `ERR_TOO_MANY_RETRIES`) | **invalid, NOT VERIFIED** |

Required: run PageSpeed Insights on `https://egyptroamer.com/` and `/destinations/` (mobile and desktop) from a normal browser, and read CrUX after launch. The lab figures are in LAUNCH-GATE.md; they are not production figures.
