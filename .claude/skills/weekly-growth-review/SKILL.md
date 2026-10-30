---
name: weekly-growth-review
description: Weekly KPI review for Egypt Roamer (every Monday, or when the owner asks for the weekly or KPI review). Reads Search Console, Bing, GA4, the wp-admin Visits page and partner dashboards, fills one row in docs/KPI-WEEKLY.md and writes a short weekly report.
---

# Weekly growth review

Goal it serves: `docs/PLAN-2026-10-30.md` ($1,000 a month in affiliate revenue; checkpoints Mar/Jun/Sep/Dec 2027).
Read `CLAUDE.md` and the latest `docs/HANDOFF-*.md` first. Reply to the owner in Egyptian Arabic.

## Period
The 7 days ending last Sunday (UTC). Use the same range in every source, and state it in the report.

## Read (owner's Edge via claude-in-chrome; read only, never change settings)
1. **Search Console** (`sc-domain:egyptroamer.com`) → Performance, last 7 days and the 7 before: clicks, impressions,
   CTR, average position; top 10 queries and pages; countries; devices. Pages report: indexed and not-indexed counts.
2. **Bing Webmaster Tools**: clicks, impressions, indexed URLs.
3. **GA4** (`G-DFEC08WLQ6`, behind Complianz consent, so it undercounts): sessions, users, engaged sessions,
   engagement rate, top landing pages, source/medium. Never mix GA4 sessions with Search Console clicks.
4. **wp-admin → Egypt Roamer → Visits**: real page views, entrances from search and AI assistants, partner-link taps,
   `/go/` redirects (note our own tests), top pages and languages.
5. **Partner dashboards** (Viator, GetYourGuide, Klook, Tiqets, Trip.com): clicks, bookings, commission. If one is not
   reachable, write "not read".
6. **Social**: Pinterest analytics (impressions, outbound clicks), Facebook page and Instagram insights if shown.

## Write
- One row in `docs/KPI-WEEKLY.md` → "Weekly log", with only numbers actually read. Missing = "not read".
- `growth/reports/weekly/YYYY-MM-DD.md`, at most one screen:
  1. Summary (3 lines). 2. Traffic. 3. Search. 4. Best pages. 5. Keyword openings (queries with impressions at
  position 4–20). 6. Technical issues. 7. Content published or updated. 8. Social. 9. Affiliate clicks and bookings.
  10. Spend (`growth/management/budget-log.csv`). 11. Done. 12. Delayed. 13. Next week's 3 priorities.
- Every recommendation: benefit, effort, confidence, evidence, priority. Mark estimates `[Estimate]`.
- Compare with the plan's checkpoint for the month. Far below the line: say so plainly.

## Finish
Commit only these files (`git -c user.name=Morsy -c user.email=morsi.altukhy@gmail.com commit`, trailer
`Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`), push to main, and tell the owner the three numbers that
matter (search clicks, partner clicks, bookings) and next week's priorities.
