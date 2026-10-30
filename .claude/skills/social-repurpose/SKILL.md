---
name: social-repurpose
description: Turn one Egypt Roamer page into social content - Pinterest pins, Facebook and Instagram posts, and short-video scripts (YouTube Shorts, Reels, TikTok) - using only allowed photos and the page's facts. Use when preparing social content or refilling the Pinterest queue.
---

# Social repurpose

Accounts: Pinterest, the Facebook page (`profile.php?id=61595813675400`) and Instagram `@egyptroamer`. Always post as
Egypt Roamer (Edge is often signed in to LIORIVE: switch first). Daily posting runs as the scheduled task at 19:00.

## For one page, prepare
- **Pinterest**: a title with the search phrase, a 1–2 sentence description, the board (Ancient Egypt, Egypt's Nature
  or Travel Planning), the page's own allowed photo, and the Commons credit. Add it to
  `C:\Users\morsy\.claude\scheduled-tasks\egypt-roamer-pinterest-daily\queue.json` with `done:false, pinned:false`.
- **Facebook and Instagram**: one post each, credit in the text, "Full guide: link in bio" on Instagram.
- **Short video** (only when the owner will film or edit; we own no footage): 3 hooks and 3 scripts of 20–40 seconds
  using only the page's facts, a YouTube title and description, and a caption. Save them in
  `growth/social/video-scripts/<slug>.md`. Never use partner photos, others' footage or invented claims.

## Rules
Prepare freely; publishing happens in the scheduled run or when the owner asks. Never say a post is live without
checking the public profile. Log each post in `growth/social/social-calendar.csv`.
