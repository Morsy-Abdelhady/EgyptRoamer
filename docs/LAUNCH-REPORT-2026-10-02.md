# Launch report (2026-10-02)

Live: theme **1.2.21**, Core **1.2.17**. Production indexing is **OFF** (`noindex, nofollow` on all 16 sampled pages; `blog_public = 0` is checked by every deploy).

**Verdict: not launch-ready yet.**
- **Mobile LCP is still 4.2 s on production** (target ≤ 2.5 s). Section 2 explains why no further internal change can close that gap without the owner's design decisions, and gives the measured options.
- **Owner and external items remain** (sections C and D).

Detail:
- performance: `docs/PERFORMANCE-2026-10-02.md`
- visual language audit: `docs/VISUAL-AUDIT-2026-10-02.md`
- cache: `docs/CACHE-2026-10-02.md`
- previous state: `docs/FINAL-HARDENING-2026-10-01.md`

## 1. Areas 1–6 at a glance
| Area | Result |
|---|---|
| 1 Mobile performance | **Partly.** Render-blocking cut from 7 stylesheets to 1 (PSI estimate 1,730 → 500 ms) and homepage fonts preloaded. Production **LCP unchanged** (4.1 → 4.2 / 4.4 / 4.2 s). The remaining 3.2 s "render delay" is the intro and the first view's payload (section 2) |
| 2 Arabic hero | **Done.** The real photo is no longer mirrored; the Arabic text sits on the open sky. Verified on production |
| 3 Visual audit, 8 languages | **Done.** 284 screenshots inspected (320/390/768/1440 × 9 states × 8 languages); 7 visual defects fixed |
| 4 Human Chat, 8 languages | **Done locally** (169/169 + 3 stall checks). **Production:** Arabic end to end, plus English on 1 Oct |
| 5 HTML cache | **Root cause documented; deploy check added.** The cause is the host edge's 31-day browser TTL, an owner/GoDaddy setting. The automatic flush on deploy was refused here as a production write: owner decision |
| 6 Independent audit | **Done on production:** SEO and metadata consistency in 8 languages; axe on 3 live pages (2 issues found → fixed in 1.2.21); stale-page guard seen working twice |

## 2. Mobile LCP: why it stays above 2.5 s
On phones the LCP element is the **hero text**. It appears when the intro loader hands over. PSI on production: TTFB 0 ms, **element render delay 3,230 ms**.

Lighthouse (and Chrome's field LCP) counts everything that happens before that moment:
- the stylesheet;
- six first-view font faces (141 KB; Arabic 373 KB);
- the cinematic JS (GSAP, ScrollTrigger, Lenis and the bundle: 86 KB gz);
- the hero and logo images;
- the intro's minimum hold of 1.3 s.

Everything that could be improved without changing the design was done and measured (`PERFORMANCE-2026-10-02.md` §4):
- one stylesheet;
- font preload;
- low priority for hidden logos (no gain, not shipped);
- an inline intro hand-off (no lab gain, not shipped).

One option was deliberately not shipped: making the hero text "visible" under the loader would lower the number without changing what visitors see.

**Owner decisions that would move it (measured locally, mobile, median of 3; local runs read about 0.5 s higher than production):**

| Option | Local LCP | Effect on the design |
|---|---|---|
| Today (1.2.20) | 4.70 s | – |
| **No intro splash on phones** (hero shown at first paint) | **3.81 s** | the branded loader no longer shows on phones |
| + one variable Inter instead of four weight files (≈ −50 KB) | est. −0.2 s | same typeface; rendering to be compared |
| + cinematic JS after the first paint | est. −0.4 s | the scroll journey is live a moment later |

Even all three together are estimated at about 2.8–3.1 s on production. Reaching ≤ 2.5 s would also need a lighter first view: fewer font weights or a smaller script stack. That is a design scope decision, not a bug fix.

## A) Verified on production
| Item | Evidence |
|---|---|
| Theme 1.2.21 / Core 1.2.17 live after deploy and flush | style.css version, `track.js?ver=`, build `/wp-json/egypt-roamer/v1/build` = page `er-build` on `/`, `/ar/` |
| One stylesheet per template; homepage font preloads | page source: `bundle-home.css` / `bundle-site.css`, 2 `preload as=font` |
| Arabic hero: real photo orientation, text on the open sky | screenshot at 1440 px |
| SEO consistency, 8 languages × home + destinations archive | 200; live build; self-canonical; 9 hreflang; `noindex, nofollow`; 1 H1; JSON-LD; `lang`/`dir` correct |
| Human Chat in Arabic, end to end | visitor (anonymous, 375 px) → team inbox (owner's account): language AR and page shown; reply arrived 0.5 s after the tab became visible, no reload; three quick messages in order; drawer closed and reopened, page reloaded: transcript kept; team closed: visitor saw it |
| Background tab | the hidden visitor tab did not poll; the reply showed as soon as it was visible |
| Stale-page guard | twice: `/ar/` from build `32e86…` reloaded once into `71396…` |
| Accessibility (axe) | experience page: 0. Arabic homepage: 1 moderate. Destinations with the chat open: 1 serious. Both fixed in 1.2.21 (production re-check owed) |
| PSI mobile | three runs after the change: LCP 4.4 / 4.2 / 4.2 s, FCP 2.2 / 1.7 / 2.3 s, TBT 110 / 10 / 20 ms, CLS 0 |

## B) Verified locally only
| Item | Evidence |
|---|---|
| Chat in all 8 languages | `chat-locales.mjs` 169/169: AI answer, double-click start → one conversation, inbox with language, live reply without reload, quick messages in order, close/reopen/reload, two tabs, background tab, offline + Retry once, long text, RTL, close |
| Chat robustness | `chat-stall.mjs` 3/3 (a stalled request no longer freezes the inbox or the visitor's queue); `chat.mjs` 34/34; concurrency 11/11; edge cases 7/7 |
| Bundle = separate files | computed styles of every element identical (12 pages × 2 widths) |
| Visual fixes L2–L7 | screenshots (`VISUAL-AUDIT-2026-10-02.md`) |
| Regression after 1.2.20 | responsive 1,232/0; title fit 8 languages; keyboard; launcher keyboard; overlays axe 0; hygiene; journey nav; assistant e2e; chat i18n 16/16 |

## C) Externally blocked
| Item | Blocker |
|---|---|
| Automated production QA (Playwright, Lighthouse, crawls) | Cloudflare rate-limits this machine's IP; production checks were done by hand in the browsers |
| 31-day browser HTML cache | host edge setting (GoDaddy) |
| Safari/iOS check | no Apple device here |
| Email delivery (no MX/SPF) | DNS |
| Viator offer, AI provider key | external accounts |

## D) Owner decisions required
1. **Mobile intro / first-view weight** (section 2): keep the splash on phones, or trade it for about 0.9 s of LCP; variable font; deferred journey JS.
2. **Cache:** ask GoDaddy for HTML "Browser Cache TTL: respect existing headers". Then the one-line Core change sending `no-cache` for HTML. Add `wp wpaas cache flush` to the deploy, or keep flushing by hand (`CACHE-2026-10-02.md` §3).
3. **Indexing switch:** stays off until you approve (`SEO-AUDIT-2026-10-01.md` §23).
4. **Legal:** Privacy/Cookie Policy update for the assistant and chat (`PRIVACY-CHANGES-2026-10-01.md`), and Terms.
5. **Native-speaker review** of the WordPress-only strings (`tools/i18n/new-strings.json`, unreviewed).
6. **Chat staffing:** "Team is online" only while someone has the inbox open and set to Online. Your status is Offline after the test. The Arabic test conversation "اختبار الإطلاق (QA)" is closed; archive it in Conversations.
7. **`src/` on production:** `rm -rf ~/html/wp-content/themes/egypt-roamer/src` (SSH).
8. **HSTS** rollout (`HSTS-RECOMMENDATION.md`); **analytics consent** before any GA.

## Compared with the previous audit (1 Oct)
New findings in this pass that the earlier audits and suites missed:
- **Chat:** quick messages out of order; one stalled request froze the inbox; the chat transcript could not be scrolled by keyboard.
- **Visual:** Arabic and Chinese labels too small; the launcher covering header text and legal links on phones; header labels on bright photos; a Russian title broken mid-word; the play ring crossing the dock.
- **Accessibility:** nested landmarks.

All of these are fixed.

The earlier audit's open items are unchanged except:
- the Arabic hero: un-mirrored;
- render-blocking CSS: done, but LCP was not improved;
- multi-language chat: proven.
