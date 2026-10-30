---
name: keyword-research
description: Find keyword openings for Egypt Roamer from real Search Console data (queries with impressions, positions 4-20, low CTR), map each to one existing or planned page, and update growth/seo/keyword-map.csv. Use when choosing what to write or improve next.
---

# Keyword research (free tools only)

No paid SEO tools. Sources: Search Console (owner's Edge), Bing Webmaster Tools keyword research, Google autocomplete
and "People also ask" in a normal search, Google Trends. Never invent search volumes: write "unknown" unless a tool
shows a number, and name the tool.

## Steps
1. Search Console → Performance → Queries, last 28 days and last 3 months: queries with impressions.
2. Flag: position 4–20 (closest wins), impressions with no clicks (title or description problem), queries landing on
   the wrong page (cannibalization).
3. Check intent by searching the query: what ranks (guides, tour lists, forums)? Can our page type compete?
4. Map each keyword to ONE page in `growth/seo/keyword-map.csv` (existing URL, or "planned"). Before planning a new
   page, grep `content/editorial/en/` and the live `/guides/` list: never two pages for one intent.
   Example: "Abu Simbel guide" (information) is not "Abu Simbel day trip from Aswan" (compare and book).
5. Rank by the plan's order: commercial value first (cruise, multi-day tour, hotel, comparison), then pre-trip
   traffic pages.

## Output
The updated CSV, and 5 lines to the owner in Egyptian Arabic: the top 5 actions and why. Mark guesses `[Estimate]`.
