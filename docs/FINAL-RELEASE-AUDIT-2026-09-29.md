# Egypt Roamer — final release audit (2026-09-29)

> **Update (2026-09-29, later):** translations are no longer blocked. The owner approved all 105 files (`review: approved`; 28 after corrections, see `docs/MULTILINGUAL-FULL-REVIEW.md`). None is imported into production yet. The statements below that say "pending" record the state at the time of this audit.

Production: https://egyptroamer.com. This audit follows the master execution instruction (phases 1–18).

**Evidence sources:**
- **Production:** read-only, from public pages and a read-only wp-admin session in the owner's Chrome.
- **Local release candidate:** the `release-candidate` branch, which is `arabic-editorial` merged with `keyfacts-no-travel-times`, on the local install built by the same seed as production.

Nothing was deployed, imported or changed on production in this audit.

**Launch decision: NOT READY.** Only the owner can clear the blockers (§D and §G). Code and platform checks pass.

---

## A. DONE (verified complete)

| Area | Evidence |
|---|---|
| Arabic editorial workflow | Compiler `--lang`/`check`/`status`. Importer `wp egypt-roamer editorial --lang=<code> [--dry-run]` (Core 1.2.4) with a review gate, an outdated-source gate, a seed-text guard, link rewriting, no guide creation, and English untouched. Tests: `docs/ARABIC-EDITORIAL-PLAN.md` §8 |
| Production source delivery | The importer reads Core's `data/editorial/<lang>/`, which the normal Core deploy ships. Proven: English files on production are byte-identical to the repo; a local dry run works with the Markdown sources removed (§9 of the plan) |
| Translations prepared | ar, de, fr, it, es, ru, zh: 15 files each (7 destinations, 8 experiences), **105 files, all `review: pending`**. Review indexes: `docs/ARABIC-REVIEW-INDEX.md`, `docs/MULTILINGUAL-REVIEW-INDEX.md` |
| Translation QA, per language | Same shape as English (headings, lists, FAQs, callouts, links, paragraphs); no new numbers; no English leakage; official UNESCO names where they exist; 28–33 sourced fact strings present; dry run 15/15 with 35 links pointed at the language; English posts md5-identical before and after every import |
| Render QA of translated pages | 7 languages × 15 pages × 8 widths (320–1440): 0 issues, 0 axe violations; the contents box sits after the intro on every destination (the theme 1.1.6 fix holds) |
| Travel-time cleanup | Theme 1.1.7 (branch `keyfacts-no-travel-times`): "Getting there" removed from destination key facts in all languages. Measured on 56 pages × 5 widths before and after |
| Full-site matrix (release candidate) | 8 languages × 8 page types (home, destinations, destination, experiences, experience, guides, search, 404) × 8 widths = 512 renders. **0** overflow, clipped text, wrong `lang`/`dir`, H1 errors, broken images or JS errors. **0 axe violations**. HTTP 200 on 56 pages; 404 on the 8 intentional 404 URLs |
| Affiliate flow (local, test data) | Provider → offer → CTA (`rel="sponsored nofollow noopener"`) → disclosure → `/go/` tracked redirect → dataLayer `affiliate_click` → report → CTA label and URL edited without code. All steps executed passed (10/10 in this run before a local-proxy timeout; the remaining homepage and admin-screen steps passed in the previous full run, 14/14). No cart, checkout or payment code anywhere |
| Security (production) | `/wp-json/wp/v2/users`, `?rest_route=/wp/v2/users`, `?author=1` and `/author/…` → 404; oEmbed has no author fields; `xmlrpc.php` → 403; `readme.html` and `debug.log` → 404; debug output off (Site Health) |
| SEO (production) | `noindex, nofollow` on every page sampled; self-canonicals; 9 hreflang alternates (8 + x-default); `og:locale` per language; translated titles and descriptions; `robots.txt` present; WordPress sitemap off while indexing is off |
| wp-admin (production, read-only) | Search engines discouraged ✓; registration off ✓; comments and pings closed ✓; timezone Africa/Cairo ✓; front page Home, posts page Journal ✓; permalinks `/journal/%postname%/` ✓; privacy page assigned ✓; 1 user (administrator) ✓; plugins: Akismet, Egypt Roamer Core, Polylang, Site Kit (all active, no updates pending) + 2 GoDaddy must-use plugins + Redis object cache; theme Egypt Roamer active, Twenty Twenty-Five inactive (fallback); Polylang: 8 languages, English default, browser detection off; Site Health: **no critical issues** |
| Performance (production homepage) | 26 requests, about 349 KB transferred (HTML 21 KB), DOM ready about 1.1 s, CLS 0, 50/56 images lazy-loaded |
| Repository | The release diff touches only `content/`, `tools/`, `docs/`, Core (importer, version, one seed title, compiled data) and the theme (one template, version, changelog). No CRLF, no secrets, PHP lint clean, seed JSON valid, editorial check clean, versions consistent |

## B. FIXED AUTOMATICALLY

| Fix | Where | Test |
|---|---|---|
| Travel times removed from destination key facts (all 8 languages) | theme 1.1.7, branch `keyfacts-no-travel-times` (`996562e`) | 56 pages × 5 widths: 2 facts everywhere, no overflow, contents box unchanged, editorial text 30–90 px higher on average |
| Arabic grammar in a title: «…وأبو الهول» → «…وأبي الهول» | `seed.json` (the source for new installs) | local H1 and `<title>` render; slug unchanged. **The live title needs a separate approved database edit (§G)** |
| Arabic terminology made consistent (Great Sand Sea, springs, Mount Sinai first mention, belle époque, the Abu Simbel sun event) | `content/editorial/ar` | terminology check: 0 failures |
| UNESCO names and Ministry spellings verified (Arabic), official names used for fr/es/ru/zh and the German Commission list for de | content files, `docs/CONTENT-SOURCES.md` | per-language required-term checks |

## C. ADDED

- **Tooling:**
  - multi-language compiler with structure checks and `status`;
  - `--lang` importer with review and outdated-source gates;
  - contents-box labels for all 8 languages.
- **Content:** 105 translated editorial drafts (7 languages × 15).
- **Docs:**
  - `docs/ARABIC-EDITORIAL-PLAN.md` §6–§9;
  - `docs/ARABIC-REVIEW-INDEX.md`;
  - `docs/MULTILINGUAL-REVIEW-INDEX.md`;
  - Arabic and UNESCO naming sources in `docs/CONTENT-SOURCES.md`;
  - this report.

## D. REMAINING ISSUES (genuine blockers or human decisions only)

| # | Issue | Why it's not fixed here |
|---|---|---|
| 1 | **Translations not approved.** 105 files are pending; the production import can't run until they're approved | Owner review (decision 1) |
| 2 | **Legal and trust pages are drafts** (Privacy, Terms, Cookies, Affiliate Disclosure, Contact…). No footer or menu links; `/contact/` and `/privacy-policy/` return 404 on production | Legal text is the owner's |
| 3 | **0 affiliate providers or offers on production.** Experience pages have no CTA, so the business model can't earn yet | Real provider accounts and URLs can't be invented |
| 4 | **No WordPress 2FA** on the only administrator (the profile shows no two-factor section). GoDaddy's login layer may add its own; not verifiable from here | Security account change |
| 5 | **English guides unpublished:** 4 written drafts (`g-best-time`, `g-7-days`, `g-gems`, `g-cairo`) are technically ready; 3 guides (costs, safety, cruises) are unwritten and need real data. Translated guides wait for this (decision 5) | Owner approval; facts not available |
| 6 | **Homepage still shows travel times** (map card, map list, destination cards), from the same `_er_map_reach`/`_er_getting_there` values | Decision 4 covered destination key facts only |
| 7 | **Stale theme `src/` on production** (`/wp-content/themes/egypt-roamer/src/js/*.js` return 200). The deploy excludes `src/`, so it's never deleted | Server deletion needs approval |
| 8 | **GoDaddy tracking scripts** (`img1.wsimg.com/traffic-assets/tccl-tti`, `signals/scc-c2`) load on every page without a consent prompt | Host setting and privacy policy |
| 9 | **SEO plugin and analytics.** Site Kit 1.188.0 is active; Rank Math is planned but not installed | Owner decision (never install blindly) |
| 10 | **PHP 8.2.33** (Site Health "recommended") | Hosting setting |
| 11 | **Arabic `exp-giza` title** on production still reads «…وأبو الهول» | Database write; approved separately |
| 12 | **Italian UNESCO names:** none official exist, so the sites are described. Russian UNESCO's name for Historic Cairo is «Исламский Каир» | Editorial choice, flagged in the review index |
| 13 | **Sources:** `g-gems` mentions Abydos (Temple of Seti I) and Dendera (Temple of Hathor), which are not listed in `CONTENT-SOURCES.md` | Approved English left unchanged |
| 14 | **Email delivery untested; HTML cache length** (from the 2026-09-28 audit) | Not re-tested here |

## E. PRODUCTION STATE (read on 2026-09-29)

| Item | Value |
|---|---|
| WordPress | 7.1.2 (feed generator) |
| Theme | Egypt Roamer **1.1.6**, active (`style.css`) |
| Core | Egypt Roamer Core **1.2.3**, active (`track.js?ver=1.2.3`) |
| Other active plugins | Polylang, Akismet, Site Kit by Google 1.188.0; GoDaddy must-use plugins; Redis object cache drop-in |
| Indexing | **OFF**: "Discourage search engines" ticked; `noindex, nofollow` served |
| Languages | 8 (en default, de, fr, it, es, ru, zh, ar); Polylang sync off (verified 2026-09-29) |
| Users | 1 administrator; registration closed |
| Site Health | no critical issues; 2 recommended (PHP version, indexing off by design) |
| Deployment | GitHub Actions → tests → rsync over SSH (theme and Core only) → health checks. **Not triggered by this work**: `main` is unchanged at `db16363` |
| Pending code | Branch `arabic-editorial` (Core 1.2.4 + all translations + docs) and branch `keyfacts-no-travel-times` (theme 1.1.7). Both are local and unpushed |

## F. QUALITY STATUS

| Area | Status | Evidence |
|---|---|---|
| Code | **PASS** | PHP lint, seed JSON, editorial check, version consistency; scoped diff; importer tests (gates, reruns, English md5) |
| Design | **PASS** | No redesign; 0 layout defects in 512 + 840 + 280 renders; RTL correct; key facts simplified (theme 1.1.7) |
| UX | **PASS** | Navigation, language switcher, search and 404 work in 8 languages; contents box placement holds at 320–1440 |
| Content | **NEEDS WORK** | English destinations and experiences are live; guides unpublished; legal and trust pages are drafts (D2, D5) |
| Translations | **BLOCKED** | 105 drafts technically validated, but all pending owner review (D1) |
| SEO | **PASS** (indexing intentionally off) | canonical, hreflang, og:locale, robots. The plugin decision is open (D9) |
| Accessibility | **PASS** | axe: 0 violations on every page type in 8 languages at 390 and 1440, and on all 105 translated pages |
| Security | **NEEDS WORK** | Enumeration closed, XML-RPC blocked, debug off; but no WordPress 2FA (D4), stale `src/` (D7), third-party tracking without consent (D8) |
| Performance | **PASS** | 26 requests / about 349 KB, CLS 0, lazy images |
| Affiliate / business model | **BLOCKED** | End-to-end flow passes with test data; 0 real providers or offers on production (D3) |
| WordPress configuration | **PASS** | Settings, users, plugins, themes, Polylang and Site Health as in §E |
| Launch readiness | **BLOCKED** | D1–D4 must be resolved, then the launch gate in `docs/LAUNCH-GATE.md` |

## G. FINAL ACTION LIST

**Owner decisions and content:**
1. Review and approve translation files. Set `review: approved`, `reviewer:` and `reviewed:`, then run `python tools/editorial.py` and commit. Start with `content/editorial/ar/dest-cairo.md`.
2. Approve the push of `keyfacts-no-travel-times` (theme 1.1.7). It deploys on its own.
3. Approve the merge and push of `arabic-editorial` (Core 1.2.4). This adds code and data files only and changes no content. Then, on the server, for each language with approved files: `cd ~/html && wp egypt-roamer editorial --lang=<code> --dry-run`, and the real import only after your go.
4. Approve the Arabic title edit: `cd ~/html && wp post update 202 --post_title='جولة خاصة إلى أهرامات الجيزة وأبي الهول'` (or edit it in wp-admin).
5. Publish the legal and trust pages and add them to the footer.
6. Add real affiliate providers and offers.
7. Enable 2FA for the administrator.
8. Publish or edit the 4 English guides; supply data for the 3 unwritten ones.
9. Decide on the homepage travel times (D6), the GoDaddy tracking scripts (D8), the SEO plugin and Site Kit (D9), and PHP (D10).
10. Approve a one-off deletion of `~/html/wp-content/themes/egypt-roamer/src/`.

**After any deploy:** check `/`, `/ar/`, a destination in each language, `track.js?ver=` and `blog_public`=0 (the workflow's health checks already cover these), then flush the cache.

**Before launch:** run the launch gate in `docs/LAUNCH-GATE.md`. Indexing stays off until then.
