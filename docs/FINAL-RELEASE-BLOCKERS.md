# Egypt Roamer — final release blockers and decision list (2026-09-29)

This is the single list for the project owner. It supersedes the action list in `docs/FINAL-RELEASE-AUDIT-2026-09-29.md` §G; that document keeps the full evidence.

**Current state:**
- Production is **unchanged**: WordPress 7.1.2, theme 1.1.6, Core 1.2.3, indexing **OFF** (`blog_public` = 0, `noindex, nofollow` served).
- `main` = `origin/main` = `db16363`.
- Nothing has been merged, pushed, deployed or imported, and there have been no database writes.

---

## A. READY (fully verified)

| Item | Evidence |
|---|---|
| **Code quality** | PHP lint, seed JSON, `editorial.py check` and version consistency (theme 1.1.7 / Core 1.2.4 on the branches); the release diff touches only `content/`, `tools/`, `docs/`, Core and the theme; no CRLF, no secrets |
| **Editorial importer (Core 1.2.4)** | Per-language import with review and outdated-source gates. Writes only empty or seed bodies and excerpts of that language. Never touches English, titles, slugs, status, meta, relations or indexing. Never creates guides. English md5-identical before and after in every test |
| **Import source on production** | Core ships `data/editorial/<lang>/`. Proven byte-identical for English on production; a local dry run works without the Markdown sources |
| **Translations: technical QA** | 105 files (7 languages × 15): structure, links, numbers, leakage, terminology and facts all pass; dry runs 15/15 per language; render QA 0 issues; axe 0 |
| **Whole site (local release candidate)** | 8 languages × 8 page types × 8 widths = 512 renders: 0 layout, JS, `lang`/`dir`, H1 or image errors; axe 0 violations |
| **Destination key facts without travel times** | 56 destination pages × 5 widths before and after: stable, contents box unchanged, no overflow; experience durations still shown in all 8 languages |
| **Affiliate-only architecture** | Local end-to-end test: provider → offer → CTA (`rel="sponsored nofollow noopener"`) → disclosure → `/go/` tracked redirect → dataLayer `affiliate_click` → report → edits without code. On production, unknown `/go/` slugs fall back to the homepage, and the offer and provider REST endpoints need login (401). No cart, checkout, payment or internal booking anywhere |
| **Security, production (read-only)** | Users REST, `?rest_route=`, `?author=`, author archives → 404; oEmbed has no author fields; `xmlrpc.php` 403; `.git`, `.env`, `wp-config*` backups, `.htaccess` → 403; directory listings 403; `debug.log`, `readme.html`, `license.txt` 404; draft pages not readable via REST; debug output off |
| **SEO implementation** | Self-canonicals, 9 hreflang alternates (8 + x-default), `og:locale` per language, translated titles and descriptions, `noindex, nofollow`, WordPress sitemap off while indexing is off |
| **WordPress configuration** | Registration off; comments and pings closed; timezone Africa/Cairo; front page and posts page set; permalinks `/journal/%postname%/`; 1 admin; 4 active plugins, no updates pending; Site Health: no critical issues; Polylang: 8 languages, English default, browser detection off, sync off |
| **Performance (production homepage)** | 26 requests, about 349 KB, HTML 21 KB, DOM ready about 1.1 s, CLS 0, 50/56 images lazy |
| **Consent default** | Core `consent_default` = denied; no GTM ID set |

## B. SAFE FIXES READY TO MERGE (on your approval only)

| Branch | Commits | What it changes | Deploy effect |
|---|---|---|---|
| `keyfacts-no-travel-times` | `996562e` (on `main`) | Theme **1.1.7**: removes the "Getting there" row (travel times) from destination key facts in all languages. 4 files: the destination template, the version in `style.css`/`functions.php`, and `CHANGELOG.md`. Meta values are kept; experience templates untouched | Push to `main` deploys the theme only. No database change |
| `arabic-editorial` | every commit from `ccb768c` to the branch head (includes this document) | Core **1.2.4**: `--lang` importer, multi-language compiler and labels, the Arabic `exp-giza` title fixed in `seed.json` (source only), compiled data for 7 languages (inert until an approved import), 105 source files (not deployed), docs | Push deploys Core only: code and data files. **No content changes until you run an import** |

- The two branches are independent and don't conflict. The local `release-candidate` branch (both merged) is for testing only and must not be pushed.
- **Order:** either branch can go first. Each push runs the full CI (tests, rsync, health checks).

## C. HUMAN APPROVAL REQUIRED

1. **Merge and push `keyfacts-no-travel-times`** (theme 1.1.7).
2. **Merge and push `arabic-editorial`** (Core 1.2.4). Note: the compiled translation drafts become publicly readable at `/wp-content/plugins/egypt-roamer-core/data/editorial/<lang>/`, as `seed.json` is today. They're unlinked and noindexed. The alternative is to commit compiled files only after approval.
3. **Translation approvals**, file by file (F). Also the translation decisions in `docs/MULTILINGUAL-REVIEW-INDEX.md` → "Items that genuinely need a human decision": voice, unverified name spellings, official-name nuances, one French title, Chinese renderings.
4. **Homepage travel times.** The map card, map list and destination cards still show them (`_er_map_reach`, `_er_getting_there`). Remove as well, or keep?
5. **Two-factor login** for the only administrator. The WordPress profile has no 2FA section; GoDaddy's login layer isn't verifiable from here.
6. **GoDaddy analytics scripts** (`img1.wsimg.com/traffic-assets/tccl-tti`, `signals/scc-c2`) load on every page without consent. Turn them off in the GoDaddy panel, or cover them in the cookie and privacy policy.
7. **Site Kit vs an SEO plugin.** Site Kit 1.188.0 is active; Rank Math is planned but not installed. Choose; don't install blindly.
8. **PHP version.** Production runs 8.2.33 (Site Health "recommended"); upgrading is a hosting setting.
9. **Security headers.** None are sent (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy), and HTML is cached for 31 days at Cloudflare. A small, safe Core change could add `nosniff`, `SAMEORIGIN` and `strict-origin-when-cross-origin`. HSTS is a long-lived HTTPS commitment and needs your yes. Implement?
10. **Author display name.** The feed prints the author's display name (`dc:creator`) once Journal posts exist. Make sure it isn't the login name (Profile → "Display name publicly as").
11. **English guides.** 4 drafts are ready to publish (`g-best-time`, `g-7-days`, `g-gems`, `g-cairo`); `g-gems` cites Abydos and Dendera, which aren't in `CONTENT-SOURCES.md`. 3 are unwritten (costs, safety, cruises) and need real data. Translated guides wait for these.

## D. PRODUCTION ONE-OFF ACTIONS (prepared, not executed)

Each runs only after a separate go. Run them from the owner's SSH session in `~/html` (running `wp` from `~` fails). Each has a read-only pre-check, the change, a check and a rollback.

### D1. Arabic Giza title (database write: 1 field of 1 post)
```
cd ~/html
wp post get 202 --field=post_title          # expect: جولة خاصة إلى أهرامات الجيزة وأبو الهول
wp eval 'echo pll_get_post_language(202);'  # expect: ar
wp post update 202 --post_title='جولة خاصة إلى أهرامات الجيزة وأبي الهول'
wp post get 202 --field=post_title          # verify
wp post get 202 --field=post_name           # slug must be unchanged
```
- **Rollback:** `wp post update 202 --post_title='جولة خاصة إلى أهرامات الجيزة وأبو الهول'`.
- **Afterwards:** flush the cache (admin bar), then check the page H1.
- **Alternative:** edit the title in wp-admin, but not the slug.

### D2. Stale public theme `src/` folder (move out of the web root; not a delete)
**Evidence:**
- `/wp-content/themes/egypt-roamer/src/js/main.js` and `i18n.js` return 200.
- No page references `src/`; the theme loads only `assets/`.
- No theme PHP references `src/`.
- The deploy excludes `src/`, so rsync never removes it.
```
cd ~/html
ls -la wp-content/themes/egypt-roamer/src          # confirm what's there
mkdir -p ~/er-backups
mv wp-content/themes/egypt-roamer/src ~/er-backups/egypt-roamer-src-2026-09-29
```
- **Checks:** `https://egyptroamer.com/wp-content/themes/egypt-roamer/src/js/main.js` → 404 after a cache flush; `/`, `/ar/` and a destination → 200.
- **Rollback:** `mv ~/er-backups/egypt-roamer-src-2026-09-29 ~/html/wp-content/themes/egypt-roamer/src`.
- Delete the backup after a week with no issues.
- The deployment boundaries stay unchanged.

### D3. Translation imports (per language, after approvals and the Core 1.2.4 deploy)
```
cd ~/html
wp egypt-roamer editorial --lang=ar --dry-run   # expect "not approved" for every pending file
wp egypt-roamer editorial --lang=ar             # only after reviewing the dry run and giving the go
```
- **Afterwards:** flush the cache. Check the language's destination pages: body, contents box, links under `/ar/`, RTL.
- **Rollback:** the importer overwrote only seed text. WordPress revisions keep the previous body, so restore per post from Revisions.

### D4. Cache flush after any of the above
Use "Flush cache" in the admin bar. HTML is otherwise cached for up to 31 days.

## E. COMMERCIAL / LEGAL BLOCKERS

| # | Blocker | Exact state (production, 2026-09-29) | What's needed from you |
|---|---|---|---|
| E1 | **Affiliate providers** | **0 providers** | Real affiliate accounts: for each, the provider name, programme terms and tracking link format. Nothing can be invented |
| E2 | **Offers** | **15 offers, all drafts**, none linked to a provider and none with a URL. They're prototype placeholders, some naming real brands (Sofitel Legend Old Cataract, Marriott Mena House, Adrère Amellal) | Publish an offer only with a real provider relationship and URL; delete or keep the others as drafts. Never publish brand offers without a relationship |
| E3 | **Legal pages** | Privacy Policy (610 words, likely the WordPress template; assigned as the privacy page), Terms of Use (13 words), Cookie Policy (20): **all drafts, English only** | Your approved legal text; translations per language, or an explicit English-only decision |
| E4 | **Trust pages** | Affiliate Disclosure (93 words, draft; assigned in Core as the disclosure page), Contact (30, draft; the form works), FAQ (22), Our Story (19), How We Choose (20), Partner With Us (17): **all drafts, English only** | Your text; publish; add links to the footer |
| E5 | **Navigation** | Menus link only Home, Destinations, Experiences and the languages; no footer links to legal or trust pages; `/contact/` and `/privacy-policy/` return 404 publicly | Follows from E3 and E4 |
| E6 | **Contact email** | Core setting `contact_email` is **empty**, so messages are only stored in wp-admin | An inbox address, then a send test (email delivery is untested) |
| E7 | **Consent and privacy** | Core consent defaults to denied, with no GTM. But the GoDaddy scripts (C6) and Site Kit (C7) run on the host side | A cookie policy that matches what actually loads; a consent banner if any tracking stays on |
| E8 | **Disclosure placement** | Core has disclosure text and shows it next to offers (verified locally). The linked disclosure page is a draft | Publish E4's Affiliate Disclosure before any offer goes live |

## F. TRANSLATION APPROVAL STATUS

| Language | Files | pending | approved | Up to date with English | Imported |
|---|---|---|---|---|---|
| العربية (ar) | 15 | 15 | 0 | 15 | 0 |
| Deutsch (de) | 15 | 15 | 0 | 15 | 0 |
| Français (fr) | 15 | 15 | 0 | 15 | 0 |
| Italiano (it) | 15 | 15 | 0 | 15 | 0 |
| Español (es) | 15 | 15 | 0 | 15 | 0 |
| Русский (ru) | 15 | 15 | 0 | 15 | 0 |
| 中文 (zh) | 15 | 15 | 0 | 15 | 0 |
| **Total** | **105** | **105** | **0** | **105** | **0** |

- Per-file tables: `docs/MULTILINGUAL-REVIEW-INDEX.md` (all 7 languages), with Arabic detail in `docs/ARABIC-REVIEW-INDEX.md`.
- **Guides:** 0 translated, waiting for the English guides (decision 5).

## G. FINAL LAUNCH GATE (all must be true before indexing is enabled)

Indexing stays **OFF** until every box is ticked and you give an explicit go. The formal gate is also in `docs/LAUNCH-GATE.md`.

**Code and platform**
- [ ] Theme 1.1.7 and Core 1.2.4 deployed; CI green; `track.js?ver=1.2.4` live.
- [ ] Stale `src/` moved out of the web root (D2).
- [ ] Security headers decided (C9); 2FA active on every administrator (C5).
- [ ] PHP version decided (C8).
- [ ] Cache flushed after the last deploy; full-site matrix re-run on production (0 layout, JS or axe errors).

**Content**
- [ ] Translations approved and imported for every language that will be indexed (F), each with a dry run first (D3).
- [ ] Arabic Giza title corrected (D1).
- [ ] English guides published or deliberately left unpublished (C11).
- [ ] Homepage travel-time decision applied (C4).
- [ ] Archive introductions filled (Core settings, currently empty), so archives have their own meta descriptions.
- [ ] No placeholder or stub text on any public page.

**Legal, trust and consent**
- [ ] Privacy, Terms, Cookies and Affiliate Disclosure published in every language that will be indexed (or an English-only decision recorded), linked from the footer (E3–E5).
- [ ] Contact page published, contact email set, send test passed (E4, E6).
- [ ] Cookie policy matches the real scripts; consent handled for GoDaddy/Site Kit tracking (C6, E7).
- [ ] Author display name isn't the login (C10).

**Business**
- [ ] At least one real provider with live, published offers; disclosure visible next to each; `/go/` click recorded in the report on production (E1, E2, E8).
- [ ] No draft brand offers published without a relationship.

**SEO switch-on (the last step, after everything above)**
- [ ] SEO plugin or Site Kit decision implemented (C7).
- [ ] The launch gate script from `docs/LAUNCH-GATE.md` / `tools/qa/index-gate.py` passes: robots, canonical, sitemap, H1 and JSON-LD.
- [ ] Only then: untick "Discourage search engines", verify `robots` and the sitemap, and submit the sitemap.

---

## DECISION REQUIRED FROM OWNER

1. **Deploy:** push `keyfacts-no-travel-times` (theme 1.1.7)? Push `arabic-editorial` (Core 1.2.4; the drafts become readable as data files)?
2. **Translations:** approve files (105 pending). Decide on voice (formal or informal), the unverified name spellings, the Russian and Italian UNESCO-name nuances, the French title, and the Chinese renderings.
3. **One-off production actions:** go for D1 (Arabic Giza title), D2 (move `src/`) and, after approvals, D3 (imports)?
4. **Homepage travel times:** remove as well, or keep?
5. **Security:** enable 2FA; add security headers (and HSTS or not); set a public display name.
6. **Hosting and tracking:** GoDaddy analytics scripts on or off; PHP upgrade.
7. **SEO tooling:** Site Kit, Rank Math, or both.
8. **Legal and trust:** supply and approve the text for Privacy, Terms, Cookies, Affiliate Disclosure, Contact, FAQ, Our Story, How We Choose and Partner With Us; English-only or translated; set the contact email.
9. **Commercial:** real affiliate providers and offers; what to do with the 15 draft placeholder offers.
10. **Guides:** publish the 4 English drafts; supply data for costs, safety and cruises.
