---
name: technical-seo-review
description: Technical SEO check of egyptroamer.com - indexability, canonical, hreflang, sitemap, robots, redirects, schema, internal links, images, Core Web Vitals, in 8 languages. Use monthly, after big releases, or when Search Console shows indexing problems.
---

# Technical SEO review

Inspect the real implementation first: the theme in `wordpress/wp-content/themes/egypt-roamer/` and the Core plugin in
`wordpress/wp-content/plugins/egypt-roamer-core/` (SEO guards live in Core). Fetch with `node -e "fetch(...)"`.

Check, with evidence (URL and what was seen):
1. `robots.txt`, `/sitemap.xml` and `/wp-sitemap.xml`: published pages only, no noindex URLs listed.
2. A sample of each page type in all 8 languages: status 200, self canonical, `index`, a complete and reciprocal
   hreflang set, x-default.
3. Filter and paged URLs are noindex (Search Console lists about 1,137 as excluded on purpose).
4. Old URLs in Search Console that now return 404: list them. A 301 needs the owner's OK.
5. Schema (Breadcrumb, Article, FAQ) present and valid.
6. Broken internal links, and every `/go/` partner link resolves.
7. Images: sizes, lazy loading, LCP image priority; PageSpeed Insights mobile for the home page, one guide and one
   experience.
8. Search Console → Pages: "Discovered" and "Crawled, not indexed" counts with examples.

Write a dated section in `growth/seo/technical-seo-audit.md`: issue, evidence, impact, fix, effort, priority. Fix in
code only what passes local tests; release as in `CLAUDE.md`, then run `page-coverage.mjs`.
