# Final hardening report (2026-10-01)

Live versions: theme **1.2.17**, Core **1.2.16** (deploys #26–#33, all successful except #27, whose files did deploy; see D13). **The launch gate stays OPEN**: owner decisions and external items remain (section 4). Production indexing is **off**.

Production checks today were deliberately small. Cloudflare rate-limits automated clients from this machine's IP (Playwright, Lighthouse, Python), so production was checked with the anonymous in-app browser, the owner's Chrome, and Google PageSpeed Insights. Each production request is accounted for below.

## 1. Fixed and verified on production
| ID | What | Production evidence |
|---|---|---|
| D12 | Trip assistant launcher no longer covers the journey counter (desktop) or the hero buttons (phones); keyboard reach | Chrome, 3 loads: desktop 1280 no overlap and no legal-link collision; en 320 / de 360 hidden at the top and back on scroll; Tab, Enter, Escape |
| S1, S4 | Homepage Organization + WebSite data; destination titles "<place> – Travel Guide" in 8 languages | `/`, `/de/destinations/kairo/`, `/ar/destinations/القاهرة/`: JSON-LD and titles as built; noindex kept |
| D14 | Chat with the team | anonymous visitor ↔ owner inbox: the reply arrived in 3 s without reload; close, archive; 0 errors |
| D17 | Assistant caption instead of a cut placeholder; chat buttons readable | `/de/destinations/`: German caption complete; "Mit Egypt Roamer chatten" contrast **16.4:1** (was 1.1:1) |
| D16 (behaviour) | Journey set up in 3 tasks | homepage: journey live; ScrollTrigger geometry matches the formula (pin end 3,211 = 4.6 × 698) |
| D18 | Stale-HTML guard | **verified end to end:** a page cached with build `08115d9294a8` reloaded itself once after the 1.2.17 deploy and came back as `de315fa1c25d` (= live) |
| D13 | Deploy no longer fails on Cloudflare 429 | runs #28–#33 succeeded |
| – | Core 1.2.16 live | `track.js?ver=1.2.16` |

| V1–V6 | Visual audit fixes (theme 1.2.17) | see `docs/VISUAL-AUDIT-2026-10-01.md`, production section |

## 2. Fixed and verified locally (deployed; production behaviour not yet observed)
| ID | What | Local evidence |
|---|---|---|
| D15 | Chat inbox keeps working in a background tab | chat.mjs step 12 |
| D16 (numbers) | Phone TBT-like 477 → 370 ms | journey-perf.mjs, median of 5, 4× CPU; geometry, screenshots and anchors identical |
| D18 (reload) | Stale browser copy → one reload; stale edge → `?nocache=`; search pages; content epoch; failing check | stale-html.mjs 7/7 |
| D19 | Chat messages in server order; a late-committed lower id is no longer lost | chat-multi.mjs 11/11 (the late-commit case fails on the old code) |
| D20 | Inbox links only same-site paths (`//host` injection); site-wide cap of 60 new chats/hour | crafted-path and cap tests |
| S2, S3, S5, S6 | Hubs in the sitemap; robots order; quality-based index review; Article data | launch simulation 176 URLs, 0 failures (reverted); index review 120/120 with negative tests |
| – | 233-URL crawl after all changes | 0 issues (5 intentional states listed once) |
| – | Regression | responsive 1,232/0; title fit in 8 languages; keyboard 8 × 2; launcher checks; assistant e2e; chat 34/34; edge cases 7/7; chat i18n 16/16 (with contrast); axe with overlays 0; hygiene 12 pages ok |

## 3. Measured on production (mobile)
Google PageSpeed Insights, Lighthouse 13.5, emulated Moto G Power, slow 4G, one run, 1 Oct 13:54 (GMT+3):

| Page | Perf | FCP | LCP | TBT | CLS | SI |
|---|---|---|---|---|---|---|
| `/` | 82 | 2.0 s | **4.1 s** | 0 ms | 0 | 4.6 s |

- **Mobile LCP is not met** (target ≤ 2.5 s).
- **The largest lever** is render-blocking CSS: 7 stylesheets, an estimated 1.73 s. Merging them into one file with a build step would change no rules; that's the recommended next change.
- **The other pages could not be measured**: destination, experience, Arabic, assistant open. PageSpeed stopped completing runs after the first (its quota or a limit on its fetches), and Cloudflare blocks local automated tools. **Production mobile performance therefore remains only partly verified.**

## 4. Externally blocked / owner decisions
| Item | Who | Next action |
|---|---|---|
| **Indexing switch** | owner | `docs/SEO-AUDIT-2026-10-01.md` section 23: `wp egypt-roamer index` → `--apply` → untick "Discourage search engines" → flush → verify |
| **31-day browser HTML cache** (root cause of stale pages) | owner + GoDaddy | ask for HTML "Browser Cache TTL: respect existing headers" or a short TTL (`docs/CACHE-2026-10-01.md`). The guard mitigates it now; copies cached before 1 Oct expire by about 29 Oct |
| **Cache flush on deploy** | owner + GoDaddy | add a CLI/API flush to the deploy if GoDaddy offers one; until then, "Flush Cache" after each deploy |
| **`src/` on production** (old theme sources, no secrets; also public on GitHub) | owner (SSH) | `rm -rf ~/html/wp-content/themes/egypt-roamer/src` |
| **Privacy and Cookie Policy** for the assistant, chat, hashed-IP rate limits and browser storage | owner + lawyer, then translators | `docs/PRIVACY-CHANGES-2026-10-01.md` (draft) |
| **Native-speaker review** of the WordPress-only strings (incl. 29 chat strings) | owner | `tools/i18n/new-strings.json` is marked unreviewed |
| **Chat staffing**: "Team is online" only while someone has the inbox open and is set to Online | owner | the owner's status was left on Offline after the test |
| **Email** (no MX/SPF): contact form and chat notifications are not reliably delivered | owner/DNS | – |
| **TBT, desktop GTmetrix** (303 ms, last measured before 1.2.15) | – | re-measure (Basic plan quota) |
| **HSTS** staged rollout | owner | `docs/HSTS-RECOMMENDATION.md` |
| **Viator offer, Terms, AI key, analytics consent** | owner / external | unchanged |

## 5. Verification steps still owed
1. **After the next deploy and flush:** open a page this browser visited before (e.g. `/destinations/?ui=1216`, saved with build `08115d9294a8`) without a cache-busting query. It must reload itself once and show the new build id. That's the real-world proof of D18.
2. **When Cloudflare allows automated checks again:**
   - `tools/qa/stale-html.mjs` against production;
   - the production performance sample: destination, experience, Arabic, assistant open;
   - the chat inbox in a background tab (D15).
3. **Re-run GTmetrix desktop** to see TBT after 1.2.15.

## 6. How I'd break it (adversarial pass) and what stands
- **Chat injection, IDOR, token guessing, crafted JSON, floods:** covered (chat.mjs, the smoke tests, D20). Messages are plain text everywhere; tokens are 256-bit with an HMAC; per-conversation, per-IP and site-wide limits apply.
- **Race conditions:** simultaneous takeover (last write wins, both in the audit trail); close versus reply (final status follows the later action); late commits (D19).
- **Stale HTML** loading new JS under old asset URLs: mitigated by the guard. Copies cached before the guard can't be reached; they expire by about 29 October.
- **`?nocache=`** bypasses the edge for anyone (the host's own rule); our pages keep a clean canonical and noindex for search. Origin load from abuse is a Cloudflare matter.
- **Not solved:** a botnet can still post up to 60 junk chats per hour; that's acceptable, and the owner can switch the chat off in Settings. Visitor-side message encryption at rest: not applicable (WordPress database).
