---
name: content-brief
description: Write a content brief for one new Egypt Roamer page (pre-trip guide, comparison page) before drafting - target query, intent, official sources, outline, internal links, offers to show. Use before content-writing.
---

# Content brief

Save to `growth/content/briefs/<slug>.md`, then give the owner a 5-line summary in chat.

1. **Fit with the plan** (`docs/PLAN-2026-10-30.md`): which priority (higher-value product, comparison page, pre-trip
   page)? New place guides at the old pace are not allowed.
2. **No duplication**: grep `content/editorial/en/*.md` and `growth/seo/keyword-map.csv`; name the closest existing
   page and why this one is different.
3. **Target**: main query, 3–5 secondary queries, intent, evidence (Search Console, autocomplete, Trends).
4. **Sources**: official first (egymonuments.gov.eg, egypt.travel, eeaa.gov.eg, the relevant ministry), then UNESCO.
   List the exact pages. A fact no official source states is left out.
5. **Outline**: H2s with `{#anchors}`, a FAQ, and what each section must answer.
6. **Booking**: which existing partner offers fit (Core `data/offer-facts.json`). Comparison pages use only facts from
   the partner's page (private or group, duration, pickup), never our own prices, ratings or reviews.
7. **Links**: at least 3 links in from existing pages; links out to the matching experience or comparison page.
8. **Photo**: an allowed Unsplash photo (location tag names the exact place) or a Wikimedia Commons file (free
   licence, credited).
