---
name: seo-page-audit
description: Audit one Egypt Roamer page (or a short list) for SEO and conversion - title, description, intent, internal links, booking path, schema, hreflang, speed - and record findings in growth/seo/page-audit.md. Use before improving an existing page.
---

# SEO page audit

For each URL on the live site (fetch with `node -e "fetch(...)"`, not curl, because of Avast):
1. **Search data**: Search Console clicks, impressions, CTR, position and top queries for the page (28 days).
2. **Head**: `<title>` (under about 60 characters, main query first), meta description, self canonical, `index`,
   hreflang (8 languages plus x-default, each pointing to the equivalent page), Open Graph image.
3. **Content vs intent**: does the page answer what its top queries ask? Which sections are missing? Are facts
   sourced in `docs/CONTENT-SOURCES.md`? Never add a fact without an official source.
4. **Booking path**: booking box visible on phone and desktop, offers relevant to the page, `/go/` links resolve.
5. **Internal links**: links in from related guides and places; links out to the matching experience or comparison page.
6. **Schema**: Breadcrumb, Article and FAQ as Core outputs them.
7. **Speed**: PageSpeed Insights mobile LCP, INP, CLS.

Write one dated section per page in `growth/seo/page-audit.md`: numbers read, problems, fixes ranked by benefit and
effort, confidence. Do not change the page here; improvements go through `content-refresh`.
