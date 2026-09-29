# Egypt Roamer — final release blockers and decision list (updated 2026-09-29, after the owner decisions)

This is the single list for the project owner. Supporting detail:
- `docs/PRODUCTION-ONE-OFF-ACTIONS.md`
- `docs/LEGAL-TRUST-REQUIREMENTS.md`
- `docs/AFFILIATE-ONBOARDING.md`
- `docs/HSTS-RECOMMENDATION.md`
- `docs/PHP-UPGRADE-PLAN.md`
- `docs/SEO-PLUGIN-RECOMMENDATION.md`
- `docs/MULTILINGUAL-REVIEW-INDEX.md` (on the `arabic-editorial` branch)

**Indexing stays OFF** (`blog_public` = 0; `noindex, nofollow` served on every page).

## Production state (read 2026-09-29, after this round)

| Item | Value |
|---|---|
| WordPress | 7.1.2 |
| Theme | **1.1.8** (deploy runs #10 and #11: success) |
| Core | **1.2.4**, security headers (run #12: success) |
| Security headers | `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-Frame-Options: SAMEORIGIN`, `Content-Security-Policy: frame-ancestors 'self'`, `Permissions-Policy` (camera, microphone, geolocation, payment, usb, browsing-topics off). **No HSTS** (owner decision pending) |
| Travel times | Removed from destination key facts (56 pages) and from the homepage (panel, map list, map card, page data), in all 8 languages. Experience durations kept (64 pages) |
| Admin display name | "Egypt Roamer Editorial" (login unchanged) |
| 2FA | GoDaddy 2-Step Verification available; **off, not enrolled** |
| GoDaddy RUM | **still on**: `wsimg.com` scripts and cookies `_tccl_visitor`, `_tccl_visit`, `_scc_session` |
| Stale `src/` | **still public** (200) |
| PHP | 8.2.33 |
| Multilingual | Importer (now numbered **Core 1.2.5**) and 105 drafts are on the `arabic-editorial` branch only; nothing imported |

## A. READY (verified)

- **Code and deploys:**
  - theme 1.1.7/1.1.8 and Core 1.2.4 live;
  - CI green (PHP 8.1 and 8.3 lint, seed, editorial check, bundle check);
  - rsync scoped to the theme and Core; health checks passed.
- **Whole site on production after the headers:** 8 languages × 8 page types × 390/1440 = 128 renders, **0** layout, JS or `lang`/`dir` errors, **axe 0**, intended 404s only.
- **Homepage on production:** 8 languages × 2 widths, one destination fact (Best time), map cards without travel lines, axe 0.
- **Destination key facts on production:** 56/56 pages show exactly Region + Best time; 64/64 experience pages keep durations.
- **Security:**
  - username enumeration closed; oEmbed author-free; XML-RPC endpoint blocked upstream;
  - sensitive files 403/404; directory listings 403; drafts not readable via REST;
  - new headers live;
  - the feed author is no longer the login name.
- **HSTS readiness:** HTTP→HTTPS 301 on the apex and `www`; `https://www` valid; no `http://` references or loads; external assets HTTPS. The recommendation is ready (not enabled).
- **SEO implementation:** complete for launch without a plugin (see the recommendation doc); nothing installed.
- **Affiliate architecture:**
  - intact and tested end to end locally;
  - `/go/` two-step redirect with a per-provider host allow-list;
  - click log without personal data;
  - no cart, checkout or payment.
- **Translations:** 105 files (7 languages), all technically validated, all `review: pending`, all up to date with English; the importer refuses unapproved or outdated files; English is proven unchanged by imports.
- **English guides:** the 4 written guides contain no prices, hours, distances or travel times; all links target published pages; the Abydos and Dendera facts are now sourced (Ministry of Tourism and Antiquities).

## B. SAFE FIXES READY TO MERGE

| Branch | What | State |
|---|---|---|
| `arabic-editorial` | Core **1.2.5**: `--lang` importer, multi-language compiler and labels, compiled data for 7 languages, the Arabic Giza title in `seed.json`, 105 source files, docs. Merged with the current `main` (theme 1.1.8, Core 1.2.4 headers); lint, editorial check and the pending-gate re-verified | **Hold until the approved translation set is ready** (owner decision) |

Nothing else is pending: `keyfacts-no-travel-times`, `homepage-no-travel-times` and `security-headers` are merged and deployed.

## C. HUMAN APPROVAL REQUIRED

1. **Translations:** approve files (per-file metadata), and decide the open items in the review index: voice (formal or informal), unverified name spellings, Russian/Italian UNESCO-name nuances, one French title, Chinese renderings.
2. **HSTS:** approve the staged rollout (`max-age=300` → 1 week → 1 year; no `includeSubDomains` or preload yet).
3. **XML-RPC toggle** (GoDaddy → Tools): switch off? Recommended; not in the approvals.
4. **Site Kit:** keep, connect or pause, decided together with consent (see E3).
5. **Consent banner:** needed only if any tracking stays (GoDaddy RUM, or Site Kit Analytics). Build it as a small feature, or avoid tracking.
6. **PHP 8.3:** go ahead with the staging-first plan.
7. **English guides:** publish the 4 ready guides after your editorial read; supply data for costs, safety and cruises (or keep them unpublished).
8. **Placeholder offers:** keep the 15 as drafts (current), or delete them later.

## D. PRODUCTION ONE-OFF ACTIONS (details and rollbacks in `docs/PRODUCTION-ONE-OFF-ACTIONS.md`)

| # | Action | Approved | Blocked on |
|---|---|---|---|
| A1 | Move `src/` to `~/er-backups/…` (reversible) | yes | **your SSH session** (no SSH from here). 3 commands in the doc |
| A2 | Arabic Giza title, post 202 (`…وأبو الهول` → `…وأبي الهول`) | yes, as a separate action | **explicit production-action go** |
| A3 | GoDaddy "Experience Improvement Program" → off (removes RUM scripts and cookies) | yes (default off) | **your GoDaddy sign-in**: the panel asked for re-authentication. 5 clicks; up to 24 h to take effect |
| A4 | 2-Step Verification enrolment | yes | **you, with your phone**: GoDaddy → Tools → 2-Step Verification. Also enable 2-step on the GoDaddy account |
| A5 | XML-RPC toggle off | not yet | C3 |
| A6 | Translation imports (dry run first) | after the translation approvals + Core 1.2.5 deploy | C1 |

After A1–A3, tell me and I'll re-verify on production (404 for `src/`; the title; no `wsimg.com` or `_tccl_*` cookies; the performance/privacy re-check).

## E. COMMERCIAL / LEGAL BLOCKERS

| # | Blocker | Needed from you |
|---|---|---|
| E1 | **0 affiliate providers.** 15 draft placeholder offers, some naming real brands (must stay unpublished) | At least one real programme: provider, tracked-link format, booking hosts, sub-ID rules, real product pages. Steps in `docs/AFFILIATE-ONBOARDING.md` |
| E2 | **9 legal/trust pages are drafts, English only, not linked**; `/contact/` and `/privacy-policy/` return 404 | Legal entity, address, country, privacy contact, target markets; a lawyer for Privacy/Terms/Cookies; approval of the How We Choose draft; your own Our Story. Checklist in `docs/LEGAL-TRUST-REQUIREMENTS.md` |
| E3 | **Tracking without consent today** (GoDaddy RUM cookies) | A3; then Site Kit and the consent decision (C4–C5); the cookie policy must match what loads |
| E4 | **Contact email not set** (Core setting empty); email delivery never tested | The inbox address; then I'll run a send test |
| E5 | **Affiliate Disclosure** (draft, 93 words) must match the real programmes and be published before the first live offer | Confirm the wording against the programme terms |

## F. TRANSLATION APPROVAL STATUS

| Language | Files | pending | approved | Up to date with English | Imported |
|---|---|---|---|---|---|
| العربية | 15 | 15 | 0 | 15 | 0 |
| Deutsch | 15 | 15 | 0 | 15 | 0 |
| Français | 15 | 15 | 0 | 15 | 0 |
| Italiano | 15 | 15 | 0 | 15 | 0 |
| Español | 15 | 15 | 0 | 15 | 0 |
| Русский | 15 | 15 | 0 | 15 | 0 |
| 中文 | 15 | 15 | 0 | 15 | 0 |
| **Total** | **105** | **105** | **0** | **105** | **0** |

**Process:**
1. Approve per file (`review: approved`, `reviewer`, `reviewed`) → `python tools/editorial.py` → commit on `arabic-editorial`.
2. When the approved set is ready: merge and push (Core 1.2.5) → production dry run per language → review the output → import only on your go.

The English source hashes are checked by the compiler on every build.

## G. FINAL LAUNCH GATE (all must be true before indexing is enabled)

**Security and hosting**
- [ ] 2FA enrolled for every administrator (A4); GoDaddy account 2-step on.
- [ ] Stale `src/` moved out of the web root (A1).
- [ ] HSTS decision made and, if approved, at least the 1-year step reached (C2).
- [ ] PHP upgraded via staging, or a documented decision to stay on 8.2 until its support ends (31 Dec 2026) (C6).
- [ ] XML-RPC decision (C3).

**Privacy and consent**
- [ ] GoDaddy RUM off (A3), or consent-gated.
- [ ] Site Kit/Analytics decision; a consent banner if any tracking stays (C4–C5).
- [ ] Cookie Policy lists exactly what loads; re-checked on production.

**Legal and trust**
- [ ] Privacy, Terms, Cookies and Affiliate Disclosure published (every indexed language, or English-only recorded) and linked in the footer (E2, E5).
- [ ] Contact page published; `contact_email` set; send test received (E4).
- [ ] How We Choose, FAQ, Our Story and Partner With Us published.

**Commercial**
- [ ] At least one real provider with published offers; CTA, disclosure, `/go/` redirect and report verified on production (E1).
- [ ] No placeholder or brand offer published without a relationship.

**Content**
- [ ] Translations approved and imported for every language to be indexed (F), each with a dry run first.
- [ ] Arabic Giza title corrected (A2).
- [ ] English guides published or deliberately held (C7); archive intros filled (Egypt Roamer → Settings).
- [ ] No stub or placeholder text on any public page.

**Final checks**
- [ ] Full production matrix: every page type × 8 languages × 320–1440, 0 layout/JS/axe errors.
- [ ] Launch-gate script (`docs/LAUNCH-GATE.md`, `tools/qa/index-gate.py`): robots, canonical, sitemap (only "Ready to index" items), H1, JSON-LD.
- [ ] Final production audit passes.
- [ ] **Only then**, on your explicit go: untick "Discourage search engines" and verify robots and the sitemap.
