# Launch-gate report (2026-09-30, evening)

Status: **BLOCKED**. Updated after the owner's decisions: theme 1.2.10, run #25, Site Kit tag off, GTmetrix B/71 %, LCP 2.0 s. The engineering work that did not need owner input is done and verified; launch waits on owner decisions, legal wording and external providers (below).

Details:
- `docs/FULL-SITE-AUDIT-2026-09-30.md`: inventory, parity, links, SEO, accessibility, security;
- `docs/PERFORMANCE-2026-09-30.md`: forensics and GTmetrix;
- `docs/ASSISTANT-2026-09-30.md`: the trip assistant;
- `docs/FOOTER-PARITY-AUDIT-2026-09-30.md`.

## Releases today (all deployed, CI green)
| Run | Commit | Release |
|---|---|---|
| #20, #21 | `ba34a42`, `a5a527a` | theme 1.2.6 / Core 1.2.9: canonical footer in every language |
| #22 | `1d5ad1f` | theme 1.2.7: display titles, keyboard, home links, switcher |
| #23 | `237fad8` | theme 1.2.8: homepage LCP/TBT (loader timing, one task per section) |
| #24 | `ea6d237` | theme 1.2.9 / Core 1.2.10: trip assistant (search mode), drawer/menu landmarks |

Cache: GoDaddy "Flush Cache" was run after #22 and #24. Anonymous production (clean browser profile):
- Core 1.2.10, the new theme assets and the assistant were served;
- the assistant was verified live (lazy load, focus, answers; Arabic endpoint with Arabic URLs).

## URL inventory (reconciled; local = production REST counts)
| Type | en | ar | de | fr | it | es | ru | zh | Total |
|---|---|---|---|---|---|---|---|---|---|
| Destinations | 7 | 7 | 7 | 7 | 7 | 7 | 7 | 7 | 56 |
| Experiences | 8 | 8 | 8 | 8 | 8 | 8 | 8 | 8 | 64 |
| Pages (home, journal, privacy, cookies, disclosure, contact) | 6 | 6 | 6 | 6 | 6 | 6 | 6 | 6 | 48 |
| Archives (destinations, experiences, tours*, activities*, guides*) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 40 |
| Search results, search no-results, 404 | 3 | 3 | 3 | 3 | 3 | 3 | 3 | 3 | 24 |
| Category archive (WordPress default) | 1 | – | – | – | – | – | – | – | 1 |
| **Total** | 30 | 29 | 29 | 29 | 29 | 29 | 29 | 29 | **233** |

\* Empty: nothing is published yet; they are not linked anywhere. Guides and journal posts are 0 on production (drafts). Terms is a draft.

## Verification summary
| Area | Result |
|---|---|
| Header, footer, switcher, dock, mobile menu parity | 233 URLs local and 38 production pages; identical in 8 languages |
| Links | 225 internal URLs 200; 0 wrong-language links |
| Translation | no unintended English (names and code tokens only) |
| Responsive, 320–1600 | 1,232 checks 0 bad; display titles fit in 8 languages |
| Accessibility | axe 0 on 233 pages and with every overlay open; keyboard: 8 languages × desktop/phone |
| SEO | noindex everywhere; self-canonical; reciprocal hreflang; OG; valid JSON-LD |
| Assistant | 5 languages × widths end to end; production verified |
| Performance | GTmetrix LCP 4.8 → 3.8–4.4 s; see the defect ledger and owner decisions |

## Defect ledger (this session)
| ID | Sev | Subsystem | Problem | Root cause | Fix | Local | Production |
|---|---|---|---|---|---|---|---|
| D0 | P1 | Footer | 1 column instead of 3 in 7 languages | stale per-language menus | canonical menu, localized per item | ✓ | ✓ |
| D1 | P1 | Homepage titles | ru/de/fr/it/es display words clipped | fixed one-line display size | fit.js | ✓ | deployed |
| D2 | P2 | Home links | `/` on translated pages (local only) | Polylang file-path whitelist | er_home_url() | ✓ | ✓ |
| D3 | P2 | Mobile menu | Tab left the menu | no focus trap | trap | ✓ | deployed |
| D4 | P3 | Language menu | stayed open after focus left | handlers | focusout, Escape | ✓ | deployed |
| D5 | P3 | Switcher | empty archives → homepages | Polylang | archive per language | ✓ | ✓ |
| D6 | P3 | Build | CRLF bundles | missing newline | LF | ✓ | – |
| D7 | P1 | Performance | LCP 4.8 s | loader timed from script start, waited for a third-party photo; one long start-up task | 1.2.8 | ✓ | ✓ (3.8–4.4 s) |
| D8 | P2 | Accessibility | drawer `<header>` = second banner when open | markup | div | ✓ | deployed |
| D9 | P3 | Accessibility | mobile menu outside a landmark | markup | nav | ✓ | deployed |
| D10 | P2 | Assistant | drawer footer wider than 320 px | min-content | CSS | ✓ | deployed |
| D11 | P2 | Assistant | launcher covered footer legal links (desktop) | fixed position | footer room | ✓ | deployed |
| O1 | **P1** | Privacy/legal | Google Analytics (Site Kit, GT-NBJ3VQHR) set `_ga` cookies without consent; Cookie/Privacy policies don't mention it | Site Kit connected on production today | owner decision: tag off until consent (Site Kit setting, connection kept) | – | **fixed**: no Google requests (anonymous) |
| O2 | **P1** | Cache | HTML sent with `Cache-Control: public, max-age=2678400` (31 days). Returning visitors' browsers keep old pages for weeks after a deploy and flush; different caches served different copies | GoDaddy CDN replaces the origin's header (known since 28 Sep) | **owner/host setting** | – | open |
| O3 | P1 | Performance | LCP 3.8–4.4 s (target ≤ 2.5 s) | hero entrance (0.55 s delay + 1.2 s fade after the loader) | owner decision: reveal with the loader (theme 1.2.10) | ✓ 2.28 s | **fixed**: GTmetrix LCP 2.0 s, grade B, 71 % |
| O4 | P2 | Performance | TBT ~300 ms (target < 200) | GSAP/ScrollTrigger journey set-up and refreshes (gtag removed) | option: set the journey up on first scroll (owner decision) | – | open |
| O5 | P3 | Security | theme `src/` public on production | stale early deploy | **owner**: one SSH command (the automated deletion was declined by the permission system) | – | open |
| D12 | P2 | Homepage | the Trip assistant launcher covered the journey's scene counter (desktop ≥ 901 px, en and ar, every width) and the hero's play button (320 px in 6 languages, 360 px in German). The hero's right-side crop itself is the approved composition: image geometry identical to the prototype at 320–1600 px | fixed-position launcher in the same corner as the counter and the hero buttons | theme 1.2.11: launcher raised above the counter while the journey is on screen; on phones it steps aside while the hero buttons are under it, back on scroll | ✓ 8 languages × 10 widths 0 overlaps; responsive 1,232 checks 0 bad; display fit; axe with overlays 0; keyboard; assistant e2e | pending deploy |

## Status model
| Track | Status |
|---|---|
| A. Engineering | complete for everything not blocked |
| B. QA | complete: local full audit after the assistant, and production spot checks |
| C. Production verified | yes: anonymous browser, after cache flush, Core 1.2.10 |
| D. Performance verified | GTmetrix C/63 % → **B/71 %**, LCP 4.8 → **2.0 s**, TBT 182 → 303 ms (one run each; O4 open) |
| E. Business | Viator offer (affiliate end to end), email DNS (no MX/SPF) |
| F. Legal | Terms; Privacy/Cookie wording for Google Analytics (O1) and, later, the AI assistant |
| G. Owner decisions | O2 host cache TTL, O4 journey on first scroll, O5 `src/`, HSTS; analytics consent before re-enabling GA |
| H. External provider | AI assistant mode: Anthropic key, budget, daily cap |

## Not verified, and why
- **Production mobile GTmetrix:** mobile devices need GTmetrix PRO, and the account is Basic.
- **More GTmetrix runs:** the Basic plan's 5 tests are used up, so the final result is a single run.
- **Affiliate end to end:** there is no live offer.
- **Email delivery:** there is no MX record.
- **AI mode:** there is no provider key.
