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
- **Short video** (owner, 30 Oct: the owner makes it in Gemini, Claude uploads it): 3 hooks, one 30–40 s script with a
  time/photo/voice/on-screen table using only the page's facts, a photo pack (allowed Unsplash and Commons photos
  only, each with download link and credit), a YouTube title and description (credits plus "Made with AI editing and
  voice"), and an Instagram/TikTok caption. Save in `growth/social/video-scripts/<slug>.md` (see `abu-simbel.md`).
  Gemini works only from the pack: no AI-generated scenes of real places, no photos fetched elsewhere, no partner
  photos.

## Rules
Prepare freely; publishing happens in the scheduled run or when the owner asks. Never say a post is live without
checking the public profile. Log each post in `growth/social/social-calendar.csv`.
