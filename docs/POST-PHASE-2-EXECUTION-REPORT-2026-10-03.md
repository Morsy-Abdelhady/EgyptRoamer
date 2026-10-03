# Post Phase-2.1 execution report — 2026-10-03

Released as **theme 1.2.34 / Core 1.2.20**, commit `1cefb22`. The deploy workflow (run 37094798299) succeeded; the
cache was flushed; production was verified. Four English guides were published in wp-admin with your approval.

Status vocabulary: PASS · PASS WITH NOTE · NEEDS REVIEW · BLOCKED · DEFERRED.

## 1. Executive summary

- **Trust:**
  - A provenance check of every stand-in photo against its Unsplash location tag found six public slots showing
    Saudi Arabia (NEOM), Abu Dhabi, or an untagged "White Desert". All six now show photos tagged with the exact
    Egyptian place.
  - The newsletter no longer promises a monthly letter that nothing sends.
- **Content:**
  - The 4 written English guides passed the publishing gate after two fixes (byline, meta description) and are live
    and indexable.
  - The 3 empty guides are blocked on sourced data; requirements are documented.
- **Infrastructure:**
  - HSTS is now sent (1 week, host only).
  - `/src/` is confirmed safe to delete; this needs one SSH command from you.
  - The 31-day HTML cache is host-controlled and deferred, with evidence.
- **SEO:** the architecture is unchanged. The intended growth is 176 → 181 indexable URLs (4 guides + the English
  guide archive); sitemap = indexable.

## 2. Phase 1 baseline (before changes)

| # | Item | Finding |
|---|---|---|
| 1 | Versions | Theme 1.2.33, Core 1.2.19 |
| 2 | Guides | 7 English drafts in production: 4 with bodies (636/682/588/479 words), 3 empty. All "(no author)", none ticked "Ready to index", no featured image (seed stand-in used). No translations. |
| 3 | Image sources | Unsplash hot-links resolved at runtime from Core `data/seed.json` (destinations, experiences, guides, moods, offer samples) and theme `er_home_stock()` (homepage). No Media Library images. |
| 4 | Newsletter | Real capture: the footer form posts to Core `leads.php`, which stores to `{prefix}er_subscribers`, or to FluentCRM if active. Production plugins: Akismet, Core, Polylang, Site Kit. **No sender exists.** |
| 5 | Hosting/cache | GoDaddy Managed WordPress behind GoDaddy's Cloudflare-based gateway. DNS at GoDaddy (`ns07/08.domaincontrol.com`). Your Cloudflare account has 0 zones. HTML `Cache-Control: public, max-age=2678400` set at the host layer; flush via the admin bar. Stale-page guard (Core build id + reload). |
| 6 | SEO/indexing | Per-post "Ready to index" gate (`_er_indexable`); sitemap = indexable; self-canonical; hreflang between published translations only; 176 indexable at the start. |
| 7 | Files to change | `data/seed.json`, `includes/cli.php`, `includes/api.php` (Core); `inc/home-settings.php`, `front-page.php`, `inc/template-tags.php`, `single-er_guide.php`, `languages/*` (theme); `tools/i18n/new-strings.json` |

## 3. Changes actually made

| Change | Where | Status |
|---|---|---|
| 6 photo replacements + 2 hidden sample photos removed | Core `seed.json`, theme `er_home_stock()` | PASS |
| Red Sea scene caption → Hurghada coordinates; alt describes the photo (8 languages) | `home-settings.php`, `front-page.php`, l10n | PASS |
| Newsletter title, copy and confirmation (8 languages) | `home-settings.php`, `template-tags.php`, l10n | PASS (idiom review: §5) |
| Guide byline "By Egypt Roamer" when no named author | `single-er_guide.php` | PASS |
| `sideload()` ignores an empty photo id | Core `cli.php` | PASS |
| HSTS `max-age=604800` | Core `api.php` | PASS |
| Best-time guide meta description → approved editorial excerpt | wp-admin (REST, your session) | PASS |
| 4 guides published + "Ready to index" | wp-admin (REST, your session) | PASS |

## 4. Image replacements and provenance decisions

Method:
- Each CDN id was mapped to its Unsplash page (`/napi/search/photos`), then the page's `location` was read
  (`/napi/photos/<id>`).
- 47 of 52 photos were matched. Unmatched or untagged photos count as unverifiable.
- Only free photos were used (Unsplash+ candidates were rejected).
- New sources were approved by you on 2026-10-03 and recorded in memory.

| Slot | Was (tag) | Now (tag) | Decision |
|---|---|---|---|
| Red Sea Diving experience | NEOM, Saudi Arabia | `1778400283418` reef with divers — "Hurghada, Egypt" | REPLACED |
| Homepage scene 4 (Red Sea) | NEOM, Saudi Arabia (caption claimed Ras Mohammed) | `1777551881568` anthias over reef — "Hurghada, Ägypten"; caption now Hurghada | REPLACED |
| Homepage film shot 5 | NEOM account | `1779548117227` emperor angelfish — "Hurghada, Egypt" | REPLACED |
| "Luxury" travel style | Qasr Al Sarab, Abu Dhabi | `1767790768726` Nile boat deck — "Asuán, Egipto" | REPLACED |
| Great Sand Sea 4×4 + "Adventure" | Al Khatim Desert, Abu Dhabi | `1771839535027` 4×4 tracks in dunes — "Siwa, Egypt" | REPLACED |
| White & Black Desert Safari | untagged; tags "winter", "winter wallpaper" (smooth white dunes) | `1691591403777` chalk arch — "White Desert, Bawiti, Egypt" | REPLACED |
| Car-rental samples 0/1 (hidden drafts) | Mojave Joshua trees / Dubai | none | REMOVED (photo reference only; the drafts stay unpublished) |
| Cairo destination | "Zamalek, Egypt" (verified) | unchanged | KEEP. The only alternative in the approved set is greyer, with pylons; the defects were delivery (fixed in 1.2.33). |
| Siwa destination | untagged | unchanged | NEEDS REVIEW: the subject (Shali through a window) is consistent with Siwa, but unverified. Tagged Siwa alternatives exist. |
| Film shot 4 (pale dunes), street-food table, homepage hotels/cars tabs (hidden) | untagged | unchanged | PASS WITH NOTE: no location claim in the caption/slot, or hidden |
| All other public photos (Giza ×5, Karnak ×3, Luxor Temple, Aswan ×5, Abu Simbel ×3, Philae, Siwa ×3, Wadi El Hitan, Khan el-Khalili, Cairo ×3, Hurghada ×2, Sharm, Alexandria) | tags match the page | unchanged | PASS |

Framing and quality: same crop pipeline (`er_stock_picture`, ratio crops). New photos checked in their slots at
390/1440 locally and 390/768/1280/1440 on production (hero text legible, subject held in portrait and landscape crops).

## 5. Homepage translation QA

`docs/HOMEPAGE-COPY-NATIVE-QA-2026-10-03.md`: **37 PASS · 6 PASS WITH NOTE · 7 NEEDS NATIVE REVIEW** (idiom only).
No string in any language promises booking, a monthly letter or availability. The suggested rewordings (de/ru
"Choose a place", newsletter title ar/fr/ru/zh, the ar fact line) are listed but not applied, pending a native reviewer.

## 6. Guide audit

`docs/GUIDE-PUBLISHING-AUDIT-2026-10-03.md`:
- Best time, 7 days, Cairo and hidden gems: NEEDS EDIT → **READY** after the byline and excerpt fixes.
- Safety, cruises and costs: **BLOCKED — MISSING DATA**.

## 7. Guides published

| URL | HTTP | robots | canonical | sitemap | Structured data | Byline | Status |
|---|---|---|---|---|---|---|---|
| /guides/best-time-to-visit-egypt/ | 200 | index | self | yes | Article, BreadcrumbList | By Egypt Roamer | PASS |
| /guides/7-days-in-egypt-itinerary/ | 200 | index | self | yes | Article, BreadcrumbList | By Egypt Roamer | PASS |
| /guides/cairo-travel-guide/ | 200 | index | self | yes | Article, BreadcrumbList | By Egypt Roamer | PASS |
| /guides/hidden-gems-in-egypt/ | 200 | index | self | yes | Article, BreadcrumbList | By Egypt Roamer | PASS |
| /guides/ (English archive) | 200 | index | self | yes (archives) | — | — | PASS |

Language behaviour:
- English only. There are no hreflang alternates, because no translation exists; my auditor reports this as
  "hreflang-missing" by its parity rule. **PASS WITH NOTE.**
- Other languages' navigation does not link to their empty guide archives, which stay `noindex` with an honest empty
  state.
- The language switcher on `/guides/` points to those archives. **NEEDS REVIEW** (minor UX): consider pointing it to
  the language homepage until translations exist.

Layout: checked on production at 390/768/1280/1440 (title, byline, tabs, body, mobile bar). The guide hero is
text-only, the template's design; photos appear on the guide cards. Guide CLS 0.052 (< 0.1, "good"): PASS WITH NOTE.

## 8. Guides blocked, and why

Safety, Nile cruises and costs have no body. Writing them would require prices, schedules, advisories or vessel data
that the project does not have. **BLOCKED.** The exact inputs, sources and editorial work are listed in
`docs/GUIDE-DATA-REQUIREMENTS-2026-10-03.md`.

## 9. Newsletter decision

**PASS.**
- No sender is installed, so the promise was removed in 8 languages.
- The capture stays functional and exportable (no fake form, no new provider).
- New copy: "Hear from us when it matters." / "New routes and guides, sent only when we have something worth your
  time. No spam, ever."
- Confirmation: "You're on the list. We'll write when there's something worth reading."
- The privacy note ("We only use your email to send the letter…") was unchanged.

## 10. /src/ decision

**BLOCKED (owner action).** The folder is safe to delete:
- `/wp-content/themes/egypt-roamer/src/` is a stale copy of the theme's JavaScript source; `src/js/assistant.js`
  returns 404, so it predates the assistant.
- No production page and no PHP file references it (0 references in the home/Cairo/ar HTML; theme PHP mentions it
  only in a comment).
- No secrets, keys or source maps (scanned).
- The same code is already public, minified, in the bundles.

Why the deploy doesn't remove it: rsync's `--exclude=src/` also protects it from `--delete`, and a deletion via the
workflow would exceed its 30-file cap. Exact command (SSH, from the WordPress root):

```
rm -r ~/html/wp-content/themes/egypt-roamer/src
```

Verify afterwards: `https://egyptroamer.com/wp-content/themes/egypt-roamer/src/js/main.js` returns 404, and the homepage
still loads normally.

## 11. HSTS decision

**PASS.**
- The site's edge is GoDaddy's, not your Cloudflare account (0 zones), so HSTS is sent by Core with the other
  security headers.
- Checks:
  - `http://egyptroamer.com/` → 301 → `https://` in one hop;
  - no MX record or other subdomain resolves (checked: mail, webmail, ftp, cpanel, api, staging, dev, blog, shop,
    cdn, autodiscover, m); `www` is a CNAME to the apex.
- Configuration: `Strict-Transport-Security: max-age=604800` (1 week), no `includeSubDomains`, no `preload`.
- Verified live.
- **Next step (after 2026-10-10, if there are no HTTPS issues):** raise to `max-age=15552000` (6 months) in Core
  `includes/api.php`.

## 12. HTML cache decision

**DEFERRED.**
- The 31-day `max-age` is applied by GoDaddy's gateway layer, which versions its cache keys on each flush
  (`x-gateway-cache-key: <flush-stamp>|…`).
- WordPress-sent headers do pass through: Core's `no-store, private` on the build endpoint arrives unchanged.
- So a WordPress `Cache-Control` override is possible. But its effect on the gateway's edge TTL (`s-maxage` support)
  can only be tested on production, and getting it wrong lowers the edge hit rate or breaks purge behaviour.
- The stale-page guard already corrects outdated browser copies: A–E and G pass locally today; F passed in the last
  full run, and its code is unchanged.
- Recommended: ask GoDaddy support whether Managed WordPress can set the HTML browser TTL to about 10 minutes while
  keeping the edge TTL. Otherwise, a supervised production test with an immediate rollback.

## 13. SEO growth opportunities

`docs/SEO-GROWTH-OPPORTUNITIES-2026-10-03.md`:
- Experiences are leaves (inlinks only from their own destination, 0 from experiences), and Sharm is the least
  connected destination.
- Implemented: guide publication.
- Proposed (an editorial sync is needed): link the "Combine it with" mentions; add experience links in the 7-days and
  hidden-gems guides; set the guides' destination relation.

## 14. Affiliate UX status

**PASS.**
- No booking claims, fake partners, prices or availability anywhere.
- Experience pages without an offer end with "Need personal help? Have a question? Talk to our team." plus "Chat with
  Egypt Roamer" and "Plan My Trip". Partner wording and the finder appear only with a live offer.
- On phones the help box follows the FAQ, and the "Plan My Trip" bar is always visible.
- No UI change was needed.

## 15. Performance before/after (production, same harness: 412×823 @1.75, Slow 4G, 4× CPU, median of 5)

| Page | LCP (1.2.33) | LCP (1.2.34) | TBT 1.2.33 → 1.2.34 | Total | Fonts | Images |
|---|---|---|---|---|---|---|
| `/` | 1688 | 1716 | 206 → 196 | 853 KB | 139 | 583 |
| `/ar/` | 1816 | 1860 | 350 → 355 | 1011 KB | 286 | 583 |
| `/zh/` | 2000 | 1952 | 579 → 395 | 638 KB | 139 | 358 |
| `/ru/` | 1912 | 1876 | 383 → 315 | 919 KB | 196 | 583 |
| Cairo | 1460 | 1520 | 74 → 42 | 270 KB | 139 | 69 |
| 7-days guide | — | 1260 | 12 | 237 KB | 162 | 18 |

- Differences of ±50 ms are run-to-run noise (no performance-relevant code changed).
- The zh/ru blocking-time spikes seen after 1.2.33 did not reproduce, which confirms they were lab noise.
- No performance change was made. The image quality from Phase 2 is kept.
- **VARIABLE INTER = DEFERRED**: re-cutting Inter saved ~3 KB per file; there is no measured reason to take on
  typography risk.
- Real-user data: watch Search Console's Core Web Vitals once there is enough traffic. Search Console was not touched.

## 16. Regression results (local, before deploy)

| Suite | Result |
|---|---|
| hygiene | all ok |
| display-fit (8 languages) | 8/8 ok |
| mobile first view | 40/40 ok |
| journey navigation | all ok |
| responsive (8 languages, archives, destinations, experiences, search) | 1,232 checks, 0 bad |
| overlays, keyboard | ok |
| axe (22 page × width combinations) | 0 violations |
| stale-HTML guard | A–E, G ok. F not run: its local `wp eval` step was replaced with a no-op, so its "fail" is by construction; code unchanged. |
| Trip Assistant | 29/29 (Phase 2, same index; nothing changed in Core search) |
| build check, PHP lint | pass |

Visual review (by eye):
- Local: the 3 new experience heroes at 390/1440; the new photos' portrait and landscape crops.
- Production after deploy: homepage, Arabic homepage, Red Sea scene, footer, Cairo, Siwa, diving, a Russian archive,
  the 7-days guide, the Chinese Cairo page, the mobile menu and the assistant drawer, at 390/768/1280/1440.
- No defects found. Minor and pre-existing: at 1280 px the "Trip assistant" pill sits next to the journey counter.

## 17. Production verification

- Theme 1.2.34, Core 1.2.20 (`style.css`, `track.js?ver=`).
- HSTS header present.
- New photos and the Hurghada caption live; the old NEOM photo absent; the new newsletter copy in 8 languages; the old
  promise absent.
- Production crawl (`tools/qa/seo-audit.py`, 278 fetches):
  - **181 indexable = 181 sitemap URLs** (en 27, other languages 22 each). The +5 are exactly the 4 guides and
    `/guides/`.
  - 0 unexpected noindex; all sitemap entries 200; no old-slug leaks; hreflang targets inside the indexable set.
  - The only "errors" are hreflang-missing on the 5 English-only guide URLs (expected, §7).
  - The new warnings are the guides' ~200-character approved descriptions and the language switcher on `/guides/`.

## 18. Remaining risks

- Translations of the new strings are self-reviewed (7 items need a native check).
- Siwa destination photo unverified (NEEDS REVIEW).
- `/src/` still public until you run the command.
- HSTS at 1 week until raised.
- The guides' language switcher leads to empty archives in other languages.
- Guide CLS 0.052 (good, but the highest on the site).

## 19. Deferred work

- HTML browser TTL (host).
- Variable Inter.
- Guide translations (after English approval).
- The editorial internal-link work (SEO growth items 2–4).
- Blocked guides (sourced data).

## 20. Exact next recommended actions

1. SSH: `rm -r ~/html/wp-content/themes/egypt-roamer/src`, then confirm `/src/js/main.js` returns 404.
2. After 2026-10-10 with no HTTPS issues: raise HSTS to `max-age=15552000` (I can make that one-line Core change).
3. Have a native speaker review the 7 items in `HOMEPAGE-COPY-NATIVE-QA-2026-10-03.md`.
4. Approve the editorial link pass (experiences' "Combine it with", the guides' experience links and destination
   relations). It runs through `content/editorial` and then `wp egypt-roamer editorial`.
5. Provide or approve dated sources for the safety guide first (requirements doc §1).
6. Decide on the Siwa destination photo (keep, or replace with a Siwa-tagged photo).
7. Ask GoDaddy support about the HTML browser TTL.
