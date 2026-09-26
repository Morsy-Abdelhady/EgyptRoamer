# Final production-readiness audit

> Superseded for the go/no-go decision by the later [launch gate](LAUNCH-GATE.md), which re-ran every test on the final code and found and fixed three more defects (empty menus with Polylang, English titles on translated Journal/Home pages, menu query cost). Query counts in §15 are from before the menus rendered; see the launch gate for current numbers.

This audit only verified the build; nothing was redesigned or re-architected. The fixes made during it address functional defects the audit found (listed in §24).

**Where it was tested:** a local lab with WordPress 7.1.2, PHP 8.4, MariaDB 10.11 and Polylang 3.8.9. Browser checks used headless Chromium through Playwright, with axe-core for accessibility. Cache checks used Varnish 7.1 with its default VCL.

**Two installs were used:**

- **Lab:** a seeded site holding local test providers, offers and QA items.
- **Fresh:** the repo only, seeded, with no Polylang.

Anything that needs GoDaddy, real business data, external networks or a human is marked **NOT VERIFIED**. Each such item gives the reason and the exact manual check.

The sandbox blocks images.unsplash.com, wordpress.org, godaddy.com, outbound mail and CDNs.

---

## 1. Multilingual QA

**Coverage:** all 8 languages (en, fr, de, it, es, ru, zh, ar). Pages checked in each: home, destination, destinations archive, experiences archive, search and 404, all after JavaScript rendering.

| Check | Result |
|---|---|
| Untranslated UI strings | none found |
| Stray Latin text in zh, ru or ar | none found |
| `lang` / `dir` attributes | correct (`ar` gets `dir="rtl"`, RTL CSS and Arabic fonts) |
| hreflang | valid and reciprocal; only emitted for translations that exist |
| Canonicals | self-referencing per language |
| `<title>` | per language. **Fixed during audit:** archive, search and 404 titles were mixed-language |
| Meta description | home and destinations: yes. Archives: none until the owner writes an archive intro |
| og:locale | not output by the fallback; Rank Math adds it (NOT VERIFIED) |
| Translation quality | **NOT VERIFIED.** 73 strings translated during migration are unreviewed (`tools/i18n/new-strings.json`). Manual check: a native speaker reviews each language before it is published |

## 2. Indexation QA (State A = not ready, State B = ready to index)

**Types tested:** destination, tour, experience, activity, guide and article.

| | State A | State B |
|---|---|---|
| robots | `noindex, follow` | `index, follow` |
| In the sitemap | no | yes |
| Canonical | self | self |
| H1 count | 1 | 1 |
| JSON-LD | BreadcrumbList only | BreadcrumbList + TouristDestination (destinations only), from visible fields |

**All types pass.** Every sitemap URL returns 200, is indexable and is self-canonical (0 problems).

**Fixed during audit:**

- Articles were outside the gate.
- Empty Journal pages and "Uncategorized" appeared in the sitemap.
- On a fresh install, WordPress's Sample Page was indexable.
- CPT archives were indexable when every item in them was noindex.

## 3. SEO source QA

- Exactly one SEO output source at a time: Core's fallback runs only when no SEO plugin is active, and Rank Math takes over through filters (NOT VERIFIED).
- No duplicate canonical or robots tags.
- Archives now carry a canonical and `og:url` without filter parameters.
- Search and filtered archives are `noindex, follow`.
- `/go/` is disallowed in robots.txt and sends `X-Robots-Tag: noindex`.

## 4. Sitemap QA

**Core sitemap (lab):** every URL is 200, indexable and self-canonical. It contains no `/go/` URLs, offers, providers, drafts, noindex items, empty Journal pages or the default category.

**Fresh install:** the sitemap contains only `/`.

**LOW:** Polylang leaves the posts page out of its core sitemap even when that page is indexable. Rank Math's sitemap replaces the core one.

**Rank Math sitemap: NOT VERIFIED.** Manual check on staging: open `/sitemap_index.xml` and confirm it contains no URL that shows `noindex`.

## 5. Affiliate lifecycle and security

**Lifecycle (lab):**

1. Create a provider with allowed domains.
2. Create an offer.
3. Link the offer to a destination and a tour.
4. The CTA renders with `rel="sponsored nofollow noopener"` and the disclosure beside it.
5. A click goes through `/go/{slug}/`, gets a 302 to the partner with UTM and sub-ID, and is logged with offer, provider, destination, placement and CTA.
6. Editing the URL or CTA in the admin takes effect immediately.
7. Pausing, expiring or trashing the offer, or deactivating the provider, removes the CTA; `/go/` falls back to the internal page.

**Acceptance script: 14/14 PASS.**

**Security matrix, all PASS:**

| Input | Result |
|---|---|
| Invalid, uppercase, traversal or trashed slug | 404 |
| Unapproved host, look-alike host (`example.com.evil.test`), `user@host`, `javascript:`, protocol-relative, malformed or `ftp:` URL | internal fallback |
| Missing or deleted provider, or a provider without domains | fallback |
| CRLF in parameters | sanitised |
| `src` parameter that is not digits | ignored (**fixed during audit**) |
| HEAD, POST, bots or prefetch | not logged (**POST fixed during audit**) |
| Visitor query parameters (e.g. `?url=`) | ignored |
| Extra path segment | one 301 on our own host, then 404 |

## 6. Test data vs real data

**The repo contains no fabricated data:**

- The seed has no prices, ratings, reviews, providers or live offers.
- Fresh install: 0 published offers or providers, 0 `/go/` links, 0 prices or ratings. The partner and finder sections are hidden.

**The lab database holds test data that exists only in the sandbox and is never deployed:** "Test Provider", "Acceptance Provider", "QA Lifecycle Provider/Offer", "Pyramids sunrise test", "Giza sunrise acceptance offer", QA pages and the Cairo translations.

**The prototype folder `Egypt Roamer/data.js`** still has the prototype's sample prices and ratings. It is a design reference and is not deployed; SETUP says not to upload it.

## 7. Email and leads

**Verified:**

- Validation, honeypot, HMAC time trap and rate limit all work.
- An email that `sanitize_email` would alter is rejected (**fixed during audit**).
- A repeat sign-up no longer blanks the stored language or interests (**fixed during audit**).
- Contact messages are stored with minimal data (email, topic, language, mail flag; no IP address).

**NOT VERIFIED:**

| Item | Reason | Manual check |
|---|---|---|
| Email delivery | the sandbox has no mail transport (`wp_mail` returns false; the message is still stored and flagged) | On staging, install one API mailer, set SPF/DKIM/DMARC, send a contact message, and confirm the message shows "Email notification sent" and the email arrives and passes DMARC |
| FluentCRM hand-off | FluentCRM is not installable here | Install FluentCRM on staging, set its list ID, subscribe, and confirm a *pending* contact plus a double opt-in email |

## 8. Privacy

- **No cookies on public pages:** the Polylang language cookie is disabled (`PLL_COOKIE=false`, **fixed during audit**, because the cookie also blocked page caching).
- GTM loads only when an ID is set. Consent Mode defaults to denied.
- The clicks table stores no personal data.
- WordPress privacy exporters and erasers now cover subscribers and contact messages (**added during audit**; export and erase were verified).

**NOT VERIFIED:** the CMP and the legal texts. Manual check: install a certified CMP, confirm that no GA hit fires before consent, and have the privacy, cookie and terms texts reviewed by the owner's adviser.

## 9. GoDaddy compatibility

| Plugin / area | Purpose | Required | Compatible | Verified | Potential conflict | Alternative |
|---|---|---|---|---|---|---|
| Egypt Roamer Core | business logic, `/go/` | yes | uses only WordPress APIs; no filesystem writes or SMTP | locally only; GoDaddy NOT VERIFIED | page cache on `/go/` (see §10) | none |
| Rank Math | SEO | yes | unknown | NOT VERIFIED | any second SEO plugin | none (single SEO plugin) |
| Polylang | languages | when a second language launches | unknown | locally (3.8.9) | Polylang's own cookie is now off | WPML (paid) |
| FluentCRM | newsletter | recommended | unknown | NOT VERIFIED | WP-Cron load | Core's stored table + CSV export |
| GTM | analytics | yes | not a plugin | loader and events locally; container NOT VERIFIED | Site Kit (do not install) | none |
| Email (API mailer) | delivery | yes | check the blocklist | NOT VERIFIED | SMTP ports | provider HTTP API |
| Caching | performance | GoDaddy built-in | built in | NOT VERIFIED (Varnish tested locally) | any cache plugin | none |
| Security | WAF, scanning | GoDaddy built-in | built in | NOT VERIFIED | Wordfence-style suites | none |
| Image optimisation | WebP, compression | optional | unknown | NOT VERIFIED | double compression | WordPress sub-sizes |
| Affiliate | redirects | Core | n/a | locally | link-cloaker plugins | none |

**Why NOT VERIFIED:** there is no GoDaddy account, and godaddy.com and wordpress.org are blocked.

**Manual check on GoDaddy staging:**

1. Check the plugin blocklist.
2. Set PHP to 8.2 or newer.
3. Confirm WP-CLI over SSH.
4. Run the `/go/` cache test (§10).
5. Confirm `REMOTE_ADDR` is the visitor IP, not the proxy, because the rate limit depends on it.

## 10. Cache QA (local Varnish 7.1, default VCL)

| Test | Result |
|---|---|
| Public page, second request | HIT (possible only after the PLL cookie fix) |
| `/go/` requested 3 times | never cached: 3 × 302, 3 click rows |
| Offer URL or status change | takes effect immediately at `/go/` |
| POST forms | pass through |
| Paused offer | its CTA may stay visible until the page's cache TTL expires; `/go/` then falls back safely. Acceptable |

**GoDaddy cache: NOT VERIFIED.**

- **Manual check:** click `/go/<slug>/` twice from two browsers and confirm two new rows in Click reports and `Cache-Control: no-store` on the response.
- **If a cached 302 is returned, that is a production blocker.** Add a `/go/*` exclusion in the GoDaddy dashboard.

## 11. Performance (lab, not field)

| Page | TTFB | LCP unthrottled | LCP 4G + 4× CPU | CLS | INP | Weight | Requests |
|---|---|---|---|---|---|---|---|
| Inner pages | 55–110 ms | 0.15–0.21 s | 0.62–0.68 s | ≤ 0.048 | ≤ 160 ms (≤ 120 ms throttled) | 366–442 KB | 18–20 |
| Home | ~100 ms | 0.26–0.46 s, or 3.7–5.3 s when the hero copy is the LCP element | 4.5–5.3 s | ≤ 0.05 | 192–296 ms (search open, 4× CPU) | 648–664 KB | 24 |

**Page weight breakdown:**

- **Inner pages:** JS 40 KB, CSS 106 KB, fonts 98–137 KB.
- **Home:** JS 233 KB, CSS 128 KB.

**Home LCP and INP are inherited from the approved design** (intro loader, hero fade and Lenis smooth scroll). A fair comparison against the prototype, with the same vendored libraries, gives the same numbers. Fixing them requires an owner design decision.

**NOT VERIFIED:**

- **Image LCP:** photos are blocked in the sandbox.
- **Production CWV:** there is no production host.

Manual check: run PageSpeed Insights on staging with real photos at mobile and desktop, then read CrUX data after 28 days.

## 12. Responsive QA

**Coverage:** 13 templates × 6 widths (390, 430, 768, 1024, 1440, 1920).

**Results:**

- no horizontal overflow, no clipped text, no broken local images and no JavaScript errors;
- the mobile menu opens and closes, including with Escape.

## 13. Accessibility

- axe-core: **0 violations** at 390 and 1440.
- Every one of the first 12 tab stops shows a visible focus indicator.
- Colour contrast over photography is **NOT VERIFIED** because the photos are blocked. Manual check: run axe on staging with the real hero images.

## 14. Security

**Verified:**

- Anonymous REST requests for offers, providers and messages → 401.
- REST search returns public types only.
- Anonymous privileged `admin-post` requests → 400.
- `?post_type=er_offer` → 404.
- Offers are not in search or feeds.
- The payload JSON uses `JSON_HEX_TAG`.
- All PHP passes `php -l`.

**LOW:** some JavaScript components insert editor-entered titles as HTML. Titles are kses-filtered and can only be set by editors.

**NOT VERIFIED:** automated scanning (no scanner available). Manual check: rely on GoDaddy's scanning plus a WPScan run on staging.

## 15. Database queries (no object cache)

| Page | Queries | DB time |
|---|---|---|
| Home (EN) | 139 | 20–37 ms |
| Home (FR) | 83 | |
| Destination | 122 | |
| Tour | 106 | |
| Search | 97 | |
| Experiences archive | 90 | |

- 0 duplicate queries.
- Every runtime query is bounded; only the CLI seed uses `-1`.
- About 40 queries on inner pages build the search-overlay payload.
- Analytics writes one row per GET click and none for page views.

Acceptable behind a page cache.

## 16. Content QA

- Brand colours and the logo come from the supplied files; the charcoal token is `#101820`.
- **Claims the owner must verify before publishing:**
  - interlude facts ("7 UNESCO sites", "1,200+ fish species");
  - the river cue "4 nights · 210 km · 5 temples";
  - travel times;
  - the "Editor's pick" and "Iconic" badges.

## 17. Content quality gate ("useful without affiliate links")

**Fails for all seeded items:**

- Destinations have 16–27 words each.
- Experiences have no body text.

They are published because the homepage links them, but they stay noindex until an editor expands them and ticks *Ready to index*. Guides and legal pages are drafts. Nothing thin is indexable.

## 18. URLs and redirects

| From | Result |
|---|---|
| `/index.html` | 301 → `/` |
| `/guide*` | 301 → `/guides/` |
| trailing-slash variants | one-hop 301 |
| `/?lang=xx` | → `/xx/?lang=xx`, which canonicalises to `/xx/` |
| `/privacy` | 301 → `/privacy-policy/` only when that page is published (**added during audit**) |
| `/how-we-choose`, `/terms`, `/cookies` | 404 while they are drafts |
| `/styleguide.html` | 404 |

Every redirect is a single hop.

## 19. Broken links

- **Crawl:** 55 pages across languages.
- **Internal:** 0 broken links, 0 internal redirects; all 20 local assets load.
- **Affiliate:** every `/go/` link is `sponsored`.
- **External:** images.unsplash.com only, NOT VERIFIED because it is blocked here. Manual check: replace the stand-in photos, then run a link checker on staging.

## 20. Final plugin audit

| Environment | Plugins |
|---|---|
| Local | egypt-roamer-core (active), polylang (active), akismet and hello (inactive WordPress defaults: delete them) |
| Production target | Core, Rank Math, Polylang (when a second language launches), one API mailer, one CMP, and FluentCRM (optional) |

**Must not be installed:** a cache, backup, security-suite, statistics or link-cloaker plugin, WooCommerce, or a second SEO plugin.

## 21. Blockers

| Blocker | Severity | Impact | Fix required | Status |
|---|---|---|---|---|
| No real affiliate accounts, providers or offers | **CRITICAL** (business) | the site earns nothing; no CTA is live | owner creates providers with allowed domains and offers with tracked links | open, owner |
| Editorial content too thin; everything is noindex | **CRITICAL** (business) | no organic traffic | editors write real content, then tick *Ready to index* | open, owner |
| `/go/` behaviour on the GoDaddy cache | **HIGH** | a cached 302 would break tracking | §10 test on staging; add an exclusion if needed | NOT VERIFIED |
| Email delivery | **HIGH** | contact messages go unnoticed | API mailer + SPF/DKIM/DMARC (§7) | NOT VERIFIED |
| Legal texts (privacy, terms, cookies, disclosure) | **HIGH** | compliance | adviser review, then publish | open, owner |
| Home LCP 3.7–5.3 s / INP up to 296 ms (throttled lab) | **HIGH** | Core Web Vitals | owner decides on the inherited intro loader and smooth scroll; measure on staging | inherited, owner decision |
| CMP / consent | MEDIUM | EU/UK compliance | install a certified CMP | open |
| Rank Math and FluentCRM integration | MEDIUM | SEO output and CRM | staging test | NOT VERIFIED |
| Native review of translations | MEDIUM | quality of non-English languages | review before publishing a language | open |
| Owned or licensed photography | MEDIUM | licensing, LCP, contrast | replace the Unsplash stand-ins | open |
| Claims to verify (§16) | MEDIUM | accuracy | owner verifies or removes them | open |
| `REMOTE_ADDR` behind the GoDaddy proxy | MEDIUM | rate-limit accuracy | check on staging | NOT VERIFIED |
| Posts page missing from the Polylang core sitemap | LOW | replaced by Rank Math | none | accepted |
| Editor titles inserted as HTML by JS | LOW | kses-filtered | optional hardening | accepted |
| Inactive akismet/hello plugins | LOW | clutter | delete | open |

## 22. Acceptance checklist

| Item | Status |
|---|---|
| Approved frontend unchanged | ✅ |
| All 8 languages: UI translated, `lang`/`dir`, hreflang, titles | ✅ (content review NOT VERIFIED) |
| Indexation State A/B for all types | ✅ |
| Sitemap clean | ✅ (core); Rank Math NOT VERIFIED |
| Affiliate lifecycle and security matrix | ✅ 14/14 + matrix |
| No fabricated prices, ratings, reviews or availability | ✅ |
| No booking, checkout or WooCommerce | ✅ |
| Leads: validation, anti-spam, storage, privacy tools | ✅; email NOT VERIFIED |
| No cookies without consent | ✅ |
| `/go/` never cached | ✅ locally; GoDaddy NOT VERIFIED |
| Responsive, 6 widths | ✅ |
| axe 0 violations | ✅ |
| 0 broken internal links | ✅ |
| Queries bounded, no duplicates | ✅ |
| Real affiliate data and editorial content | ❌ |
| Production email, cache, CWV | NOT VERIFIED |

## 23. Final decision

# NOT READY FOR PR

**The code passes every check that can be run here.** Under the rule "do not choose READY FOR PR if there is a Critical or High production blocker", it still cannot be READY, because §21 has open items:

- **CRITICAL:** real affiliate data and real editorial content do not exist yet.
- **HIGH:** the GoDaddy `/go/` cache and email delivery are NOT VERIFIED, the legal texts are unreviewed, and the home LCP/INP inherited from the approved design needs an owner decision.

None of these can be fixed in code without inventing business data or redesigning the approved frontend, which the brief forbids. They need the owner's input and a GoDaddy staging test.

**Items that would move this to READY FOR PR WITH DOCUMENTED NON-BLOCKING ITEMS:**

1. The §10 `/go/` test and the §7 email test pass on GoDaddy staging.
2. The owner accepts the inherited homepage motion, or approves changing it.
3. At least one real provider and offer are live.
4. The legal pages are published.
5. At least one content item passes the quality gate.

## 24. Defects fixed during this audit

1. Mixed-language titles on archives, search and 404.
2. Articles outside the indexation gate.
3. Empty Journal pages and "Uncategorized" in the sitemap.
4. Pages without a language dropped out of the sitemap.
5. The Polylang cookie blocked page caching.
6. Non-digit `src` and POST requests were logged as clicks.
7. Emails were silently altered by sanitising.
8. A repeat sign-up blanked the stored language and interests.
9. There were no privacy exporters or erasers.
10. Archives had no canonical in the fallback.
11. Archives were indexable when all their items were noindex.
12. WordPress's sample page and post were indexable on a fresh install.
13. `/privacy` legacy redirect.
