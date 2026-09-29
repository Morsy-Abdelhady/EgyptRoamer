# SEO plugin recommendation (2026-09-29) — nothing installed

**Owner decision (2026-09-29):**
- Don't install or activate Yoast, Rank Math or any other SEO plugin yet.
- Site Kit stays undecided until analytics and consent are settled.

## What the site already does (Core + theme + Polylang), verified on production

| Function | Implementation | Production evidence |
|---|---|---|
| `<title>` | WordPress core, "Page – Egypt Roamer" | ✓ |
| Meta description | Core fallback: the excerpt (singular), the editable archive intro (archives), the Journal excerpt | ✓ on destinations (the editorial excerpt) |
| Canonical | WordPress core self-canonical | ✓ |
| hreflang + x-default | Polylang, plus Core adding x-default | 9 alternates ✓ |
| Open Graph | Core: `og:title`, `og:description`, `og:type`, `og:site_name`, `og:locale` + 7 alternates; theme: `og:url`, `og:image` (brand default) | ✓ |
| Twitter card | `summary_large_image` | ✓ |
| Structured data | `BreadcrumbList` on inner pages; `TouristDestination` (with geo) on destinations ticked "Ready to index" | ✓ breadcrumbs; destinations appear once ticked |
| Robots | Per-item **"Ready to index"** gate; noindex on filter/sort URLs, empty Journals and unready editorial content; `blog_public` = 0 now | `noindex, nofollow` everywhere today ✓ |
| Sitemap | WordPress core sitemap, filtered by Core to indexable items only. It's off while indexing is off | 404 today, as intended |
| robots.txt | `/go/` kept out of crawling | ✓ |
| Redirects | Legacy static-site paths → WordPress (one 301 hop) | ✓ |
| **Rank Math integration** | Already built: robots filter, sitemap-entry filter, and the Core fallback meta switches itself off when Rank Math is active | ready, unused |

## Duplication / conflict risk

- **Rank Math:** designed for, so the duplicate-tag risk is low. Rank Math's own hreflang must stay off (Polylang handles it). Its Analytics module should stay off if Site Kit is kept.
- **Yoast:** not integrated. It would duplicate the description, OG and robots tags and bypass the "Ready to index" sitemap filter. **Not recommended.**
- **Site Kit:** analytics and Search Console only, with no tag duplication. The question is consent: Core's own consent default is **denied**, and Site Kit's tags would need to respect it.

## What's missing compared with a full SEO plugin

1. A per-page SEO title and description separate from the excerpt. Today the excerpt is the description, and editors already write it.
2. A redirect manager beyond the legacy map.
3. An XML sitemap with hreflang and image entries. The core sitemap lists URLs only; hreflang is in the HTML, which search engines accept.
4. Article schema for guides.
5. On-page SEO analysis (optional).

## Recommendation

- **Launch without an SEO plugin.** The custom stack covers everything search engines need, and it enforces the project's "Ready to index" rule, which a plugin could bypass.
- **At the launch gate:** verify the core sitemap turns on with indexing and lists only "Ready to index" items in every language. `tools/qa/index-gate.py` checks this.
- **Add Rank Math later, if at all,** only when editors need items 1–2. The hooks are ready; configure it with hreflang off and Analytics off.
- Small Core additions (e.g. Article schema for guides) can be done without any plugin if wanted.
- **Site Kit:** decide together with the consent approach (see `docs/LEGAL-TRUST-REQUIREMENTS.md` → cookies). Until then, leave it active, or pause it if it loads tracking without consent. Its front-end tags weren't observed in the page source today (only the generator meta).
