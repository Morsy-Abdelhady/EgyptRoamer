# Launch report (2026-10-02, updated after the mobile performance work)

Live: theme **1.2.28**, Core **1.2.17**, deployed, cache flushed, verified. Production indexing is **OFF**: `noindex, nofollow` on every sampled page, and `blog_public = 0` is checked by every deploy.

**Verdict: the launch gate stays OPEN.**
- **Mobile performance decision: implemented and measured on production.**
  - **Phones** get a content-first homepage; **desktop** keeps the cinematic intro.
  - **English mobile LCP: 4.1–4.4 s → 2.9 s** (four runs, Perf 95, TBT 0, CLS 0).
  - **Chinese: 15.8 s → 3.3 s.**
  - **Desktop: 0.9 s.**
- **Not at ≤ 2.5 s.**
  - **English:** what remains is the first view's own font bytes. The next safe step needs your approval: a variable Inter font file.
  - **Arabic:** LCP is 4.4–4.7 s, bound by its eleven font files. The options are design decisions.
  - Details: `docs/PERFORMANCE-2026-10-02.md` §9.
- **Owner and external items remain** (sections C and D).

Detail:
- performance: `docs/PERFORMANCE-2026-10-02.md`
- visual language audit: `docs/VISUAL-AUDIT-2026-10-02.md`
- cache: `docs/CACHE-2026-10-02.md`
- previous state: `docs/FINAL-HARDENING-2026-10-01.md`

## 1. Areas 1–6 at a glance
| Area | Result |
|---|---|
| 1 Mobile performance | **Content-first phones (1.2.22–1.2.27).** No loader on phones; hero drawn at the first paint; scripts after it; logos and fonts trimmed or preloaded; Chinese without Google's fonts on phones. **Production LCP:** en 4.1–4.4 → **2.9 s** (×4), de 3.2 s, zh 15.8 → 3.3 s, ar 4.4–4.7 s; desktop 0.9 s. Target ≤ 2.5 s **not reached** (section 2) |
| 2 Arabic hero | **Done.** The real photo is no longer mirrored; the Arabic text sits on the open sky. Verified on production |
| 3 Visual audit, 8 languages | **Done.** 284 screenshots inspected (320/390/768/1440 × 9 states × 8 languages); 7 visual defects fixed |
| 4 Human Chat, 8 languages | **Done locally** (169/169 + 3 stall checks). **Production:** Arabic end to end, plus English on 1 Oct |
| 5 HTML cache | **Root cause documented; deploy check added.** The cause is the host edge's 31-day browser TTL, an owner/GoDaddy setting. The automatic flush on deploy was refused here as a production write: owner decision |
| 6 Independent audit | **Done on production:** SEO and metadata consistency in 8 languages; axe on 3 live pages (2 issues found → fixed in 1.2.21); stale-page guard seen working twice |

## 2. Mobile LCP: what was done and what remains
**Before:**
- On phones the hero text waited for the intro loader: its 1.3 s hold, the photo, every font and 86 KB of scripts.
- PSI measured a 3,230 ms "element render delay".

**Now:**
- On phones the hero is drawn at the first paint, and the scripts run after it.
- PSI on production: element render delay **230 ms**; LCP 2.9 s in English.

The full step-by-step evidence is in `PERFORMANCE-2026-10-02.md` §9.

**What still separates English from 2.5 s** is the first view's own bytes on simulated slow 4G:
- six font faces drawn on the first screen (141 KB);
- CSS (22 KB);
- HTML (22 KB).

No further change was found that is safe without your approval:
1. **Variable Inter:** one file instead of four (same typeface, about −48 KB, est. −0.25 s). It requires downloading a new font file (Fontsource, OFL) and a side-by-side rendering check.
2. **Fewer weights on the first screen:** a design decision.

**Arabic (4.4–4.7 s)** carries eleven font files (~373 KB): five Arabic faces, plus the Latin faces its spaces and digits use. Options:
- fewer Arabic weights;
- subset the Arabic fonts;
- the device's Arabic font on phones (as now for Chinese). That would bring it close to English, but changes Arabic typography on phones.

## A) Verified on production
| Item | Evidence |
|---|---|
| Theme 1.2.21 / Core 1.2.17 live after deploy and flush | style.css version, `track.js?ver=`, build `/wp-json/egypt-roamer/v1/build` = page `er-build` on `/`, `/ar/` |
| One stylesheet per template; homepage font preloads | page source: `bundle-home.css` / `bundle-site.css`, 2 `preload as=font` |
| Arabic hero: real photo orientation, text on the open sky | screenshot at 1440 px |
| SEO consistency, 8 languages × home + destinations archive | 200; live build; self-canonical; 9 hreflang; `noindex, nofollow`; 1 H1; JSON-LD; `lang`/`dir` correct |
| Human Chat in Arabic, end to end | visitor (anonymous, 375 px) → team inbox (owner's account): language AR and page shown; reply arrived 0.5 s after the tab became visible, no reload; three quick messages in order; drawer closed and reopened, page reloaded: transcript kept; team closed: visitor saw it |
| Background tab | the hidden visitor tab did not poll; the reply showed as soon as it was visible |
| Stale-page guard, both paths | **stale browser copy:** `/ar/` from build `32e86…` reloaded once into `71396…`. **Stale edge** (1.2.21 deployed, edge not flushed): `/destinations/` → one reload → `?nocache=4655bb1ffa75` → live 1.2.21 page, address bar clean |
| Accessibility (axe) | **1.2.20:** experience page 0; Arabic homepage 1 moderate (nested landmark); destinations with the chat open 1 serious (log not focusable). **1.2.21:** destinations with the assistant open: **0**. The homepage fix shows once the edge is flushed |
| PSI mobile (1.2.23–1.2.28, after flush and warm-up) | en: **LCP 2.9 s in six runs** (Perf 93–95, FCP 1.2–1.9 s, TBT 0–10 ms, CLS 0). de: 3.2 s. zh: 3.3 s (was 15.8). ar: 4.4 / 4.7 / 4.3 s. Desktop: 0.9 s, Perf 96. The FCP difference between versions is explained in PERFORMANCE §9.4 |

## B) Verified locally only
| Item | Evidence |
|---|---|
| Content-first phone homepage | first-view screenshots (en 320/390, ar 390, de 320, zh 390, reduced motion) and `mobile-first-view.mjs` 40/40: no loader, hero text is the LCP at the first paint, scripts start after it, journey live, CLS < 0.01, no errors; desktop keeps the intro |
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
