# Full-site audit — every public URL × 8 languages (2026-09-30)

Scope: the whole public site, not one component. It follows the footer defect (`docs/FOOTER-PARITY-AUDIT-2026-09-30.md`): single-component QA had let a site-wide inconsistency through, so this audit starts from the URL inventory.

Result: 6 defects found and fixed (theme 1.2.7, commit `1d5ad1f`, deploy run #22). Production was verified after a cache flush. The remaining blockers are owner/external only.

## Method
| Step | Tool (all in `tools/qa/`) | Where |
|---|---|---|
| URL inventory | `site-inventory.php`: published entries of every public type, every archive × language, public terms, search, no results, 404 | local (production inventory compared through the REST API) |
| Rendered crawl | `site-crawl.mjs`: real Chrome at 1440 px, per page: status, lang/dir, title, description, canonical, hreflang, robots, OG, JSON-LD, h1/headings, header/nav/switcher/dock/mobile menu/footer signatures, every link, visible text, axe | 233 URLs, local; 3 full runs (before, after fixes, regression) |
| Analysis | `site-analyze.mjs`: every page against its English counterpart (grouped by hreflang); hreflang reciprocity; wrong-language links; English text; in-page anchors; status of every internal link (225 distinct) | local |
| Responsive | `responsive.mjs`: 14 page types × 8 languages × 320/375/390/414/480/768/834/1024/1280/1440/1600: page/element overflow, clipped text, heading under the header, script errors | 1,232 checks, local |
| Display text | `display-fit.mjs`: one-line display words clipped by their own section (invisible to page-overflow checks) | homepage, 8 × 11 widths |
| Keyboard | `keyboard.mjs`: skip link, focus ring on every header stop, language menu, search overlay, mobile menu (trap, Escape, focus return) | 8 languages × 1440/390 |
| Footer | `footer-parity.mjs` | 9 page types × 8 languages |
| Visual | full-page screenshots, 14 types × en/ar at 1440 and en/ar/de/zh at 390, reviewed side by side; Red Sea scene before/after | local |
| Production | owner's Chrome, one page at a time, 6 s apart (automated clients get a Cloudflare challenge; none was bypassed) | 38 pages, after "Flush Cache" |

## 1–3. Inventory, locales, page types
Local and production have the same published content: 48 pages, 56 destinations, 64 experiences, 0 guides, 0 journal posts (production REST API).

| Type | en | ar | de | fr | it | es | ru | zh |
|---|---|---|---|---|---|---|---|---|
| Destinations (single) | 7 | 7 | 7 | 7 | 7 | 7 | 7 | 7 |
| Experiences (single) | 8 | 8 | 8 | 8 | 8 | 8 | 8 | 8 |
| Pages: home, journal, privacy, cookies, disclosure, contact | 6 | 6 | 6 | 6 | 6 | 6 | 6 | 6 |
| Archives: destinations, experiences, tours*, activities*, guides* | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 |
| Search (results / none), 404 | 3 | 3 | 3 | 3 | 3 | 3 | 3 | 3 |
| Category archive (`/journal/category/uncategorized/`) | 1 | – | – | – | – | – | – | – |

\* Empty: nothing is published yet; they are not linked anywhere.

Total: 233 URLs. Terms (draft) is not public in any language.

## 4–6. Global components, footer, header
Every row was compared with the English page of the same translation group, on all 233 URLs (local). It was also checked on 38 production pages, including every language's homepage and contact page.

| Component | en | ar | de | fr | it | es | ru | zh |
|---|---|---|---|---|---|---|---|---|
| Logo → own homepage | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Primary nav (Destinations, Experiences) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Header buttons (search, saved, language, Plan My Trip, menu) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Mobile menu links + language list | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Dock (Explore, Saved, Search, Plan My Trip) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Language switcher: 8 links, each to this page in that language | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Footer: 3 columns (5 links) + legal row (2) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Newsletter + privacy note → own-language Privacy | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Copyright, logo | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Social links / footer contact block | none in any language (not part of the design) | | | | | | | |
| Search overlay | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Skip link | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

## 7. Translation parity
- **Rendered text:** every page's visible text and attributes (placeholders, labels, alt, aria-label) were compared with its English counterpart. A second pass had no English reference:
  - any Latin phrase on the ar/ru/zh pages;
  - any phrase with several English function words on the de/fr/it/es pages.
- **Result:** no English UI or content left. The only identical strings are intentional:
  - proper names in the approved editorial texts ("Grand Egyptian Museum", "Feteer meshaltet");
  - language names in the switcher;
  - code tokens quoted in the legal pages (`__cf_bm`, `rel="sponsored"`);
  - the brand;
  - the company's Arabic legal name;
  - email and phone.
- **Link counts:** these differ only where intended:
  - translated legal pages have the extra "English original" link;
  - search for "cairo" finds fewer results in languages that spell it differently.

## 8. Link integrity
- All 225 distinct internal URLs linked from any page return 200: no 404s and no redirect chains. The 4–6 transient "status 0" results were local server stalls, rechecked individually.
- Every in-page anchor has a target.
- Wrong-language links:
  - **Before:** 973 locally, a local-only symptom; see D2.
  - **After:** 0 on the 233 local URLs and 0 on the 38 production pages.
- The only link from a translated page to an English page is the intentional translation note (`hreflang="en"`).

## 9. Responsive
- 1,232 checks (14 types × 8 languages × 11 widths): 0 page overflow, 0 element overflow, 0 clipped text, 0 headings under the header, 0 script errors.
- The display-text check found D1, which the page-overflow checks could not see. After the fix: 0 at every width in every language.
- Visual review:
  - the layout mirrors correctly in Arabic;
  - tabs, cards, key facts, footer and contact form are the same across languages;
  - long German and Russian labels wrap cleanly.

## 10. Accessibility
- axe on all 233 pages: 0 violations.
- One h1 per page, no skipped heading levels, no image without alt, landmarks present, and a skip link on every page.
- Keyboard, in 8 languages on desktop and phone, all passing after D3 and D4:
  - skip link reaches `main`;
  - every header stop shows a focus ring;
  - language menu opens with Enter, supports arrow keys, closes on Escape and when focus leaves it;
  - search overlay: focus goes inside, is trapped, and returns on Escape;
  - mobile menu: focus is trapped with the menu button, and Escape closes it.
- `prefers-reduced-motion` is honoured: the responsive and keyboard runs used reduced motion.

## 11. SEO
- Every page is `noindex, nofollow`, as intended; robots.txt disallows only `/wp-admin/`, and the sitemap returns 404 while indexing is off.
- Canonical and hreflang:
  - every translated page is self-canonical with 9 reciprocal hreflang links;
  - every hreflang target is in the right language;
  - `og:url` and `og:locale` match each page.
- JSON-LD is valid on every page. It is BreadcrumbList only, and 16 pages (homepage, search, 404) have none.
- Empty archives have no hreflang or description (Polylang omits them when there is nothing to translate). They are noindexed and not linked.

## 12. Affiliate
- 0 providers and 0 published offers exist, locally and on production (the offers are drafts).
- `/go/<unknown or draft>/` falls back to the homepage (302 → 200), as designed.
- **A real production end-to-end test is impossible:** no Viator offer exists yet (owner: product selection in the Viator Selector, then provider/offer creation). Not marked ready.

## 13. Forms and email
- The contact form renders localized in all 8 languages, with the honeypot and rate limit.
- Email delivery: **BLOCKED**. `egyptroamer.com` has no MX or SPF record (Google DNS, 2026-09-30), and DMARC is `p=quarantine`. Messages are stored in WordPress, but mail to `info@egyptroamer.com` cannot be delivered.

## 14. Legal
- Privacy, Cookies, Affiliate Disclosure and Contact are published in 8 languages (32 pages).
- Each page appears in every language's footer, in its own language.
- Each is self-canonical with 9 hreflang, and each translation links the English original.
- Terms is **BLOCKED** (governing law and liability wording, lawyer). It is a draft, hidden everywhere.

## 15. Security (production)
| Check | Result |
|---|---|
| Security headers | X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, CSP frame-ancestors: all present |
| Server banners | `x-powered-by` absent |
| HSTS | absent (owner decision, open) |
| XML-RPC | blocked by the host's firewall |
| `/wp-json/wp/v2/users` | no route |
| `?author=` | 404 |
| `readme.html`, `wp-content/debug.log` | 404 |
| **Theme `src/` on production** | **served publicly** (`/wp-content/themes/egypt-roamer/src/js/main.js`): a stale copy from an early deploy. The deploy never deletes `src/` by design. It is unminified JS, already public in the bundles, with no credentials. P3; owner: `rm -rf ~/html/wp-content/themes/egypt-roamer/src` |
| Private mailbox, credentials | not present in any page or in the repository |

## 16. Performance
- Measured in the owner's Chrome, which is logged in and therefore uncached (an upper bound). Arabic Luxor page:
  - TTFB 1.0 s, DOMContentLoaded 2.7 s, load 2.9 s;
  - CLS 0;
  - 40 requests;
  - third parties: Unsplash photos (approved stand-ins), plus Gravatar for the admin bar only.
- LCP is not reported in a background tab.
- The cached-visitor measurements from run 1 still apply (desktop LCP: homepage 4.45 s, inner pages 2.3–2.6 s; CLS ≤ 0.003); no asset changes since have been large.

## 17. Local vs production
- Same content inventory; same theme build (`pages.css` version, Core 1.2.9).
- The same signatures were measured on 38 pages covering every page type and every language:
  - header, nav, switcher and footer;
  - canonical, hreflang and robots;
  - h1 count;
  - wrong-language links and overflow.
- One difference, local only: D2.

## 18–19. Defect ledger
| ID | Sev | Component | Locale | Where | Symptom | Root cause | Fix | Regression risk | Verified |
|---|---|---|---|---|---|---|---|---|---|
| D0 | P1 | Footer | ar de fr it es ru zh | every page | 1 column instead of 3 | stale per-language menus | theme 1.2.6 (see footer audit) | menus, Polylang | local ×233 + production 38 |
| D1 | P1 | Homepage display titles | ru (all widths), de (≤480), fr/it/es (≤414) | `/ru/` … | "КРАСНОЕ МОРЕ", "ROTES MEER" clipped; hero word touching the edge | fixed-size one-line display type (the 1.1.2 per-language cap covered only the hero, only de/ru) | `fit.js`: shrink only when wider than the column; en/zh unchanged | homepage animation | display-fit 8 × 11, responsive 1,232, screenshots |
| D2 | P2 | Home links (logo, Plan My Trip, planner, 404, breadcrumbs, search, JS data) | all translated, local only | every page | linked `/` | `home_url()` localized by Polylang only when the caller's file path matches the theme folder (symlinked theme locally) | `er_home_url()` everywhere | header/footer/planner/search | crawl: wrong-language links 973 → 0; production 0 |
| D3 | P2 | Mobile menu | all | phones | Tab moved into the page behind the full-screen menu | no focus trap | trap in overlays and menu | overlays, menu, search | keyboard ×16 |
| D4 | P3 | Language menu | all (seen in ar) | desktop | stayed open after focus left; Escape only worked inside | missing focusout/Escape handling | close on focus out and on Escape anywhere | language menu | keyboard ×16 |
| D5 | P3 | Language switcher | all | empty archives (24 pages) | switched to the homepages | Polylang has no translation link for an empty archive | link the same archive per language | switcher on every page | crawl, production `/zh/tours/` |
| D6 | P3 | Build tool | – | `tools/build.py` | wrote CRLF bundles on Windows | missing `newline="\n"` | LF | bundles | CI bundle check, byte check |
| O1 | P3 | Security | – | production `src/` | stale sources public | early deploy; deploy never deletes `src/` | **owner** (one command) | – | open |
| O2 | P3 | Dock | ru | ≤390 | "Спланировать поездку" wraps to 2 lines | long label | none (legible, not clipped) | – | observed |
| O3 | P3 | Routes | all | empty archives, `/journal/category/uncategorized/` | public "nothing yet" pages (noindex, unlinked) | WordPress defaults | none now; they fill when content is published | – | observed |
| O4 | P3 | SEO | all | – | structured data is breadcrumbs only | not built | Rank Math is planned (CLAUDE.md) | – | observed |

## 20–23. Files, commits, deployment, production
- **Commits:**
  - `ba34a42` + `a5a527a`: footer, theme 1.2.6 / Core 1.2.9, runs #20 #21;
  - `1d5ad1f`: theme 1.2.7, run #22 succeeded.
- **Files:**
  - theme: `header.php`, `footer.php`, `404.php`, `archive.php`, `index.php`, `single-er_destination.php`, `inc/i18n.php`, `inc/template-tags.php`, `inc/payload.php`, `src/js/components/{fit,nav}.js`, `src/js/main.js`, `assets/js/{home,site}.js`, `style.css`, `functions.php`, `CHANGELOG.md`;
  - tools: `tools/build.py`, `tools/qa/*`.
- **Cache:** GoDaddy "Flush Cache" was run after run #22 ("Cache cleared"). Production was then checked page by page: 38 pages, all on the new build.
- **Not verified:** anonymous (cookie-less) HTML through Cloudflare, because automated and cookie-less requests get a bot challenge (429), which was not bypassed. The pages were read in the owner's logged-in session. After the flush there is no stale cache to serve.

## 24–25. Blockers and what could not be verified
| Item | Status | Exact dependency |
|---|---|---|
| Email delivery | BLOCKED | DNS: MX, SPF (and DKIM) for egyptroamer.com; DMARC is `p=quarantine` |
| Affiliate end-to-end on production | BLOCKED | Viator product selection (owner's Selector sign-in), then provider/offer creation |
| Terms (all languages) | BLOCKED | lawyer: governing law, liability |
| Stale `src/` on production | OWNER | one SSH command (above) |
| HSTS | OWNER | decision |
| Native/legal review of the new translations | DEFERRED | owner |
| Anonymous Cloudflare view; LCP on production | NOT VERIFIABLE by this session | bot challenge; background tab |
