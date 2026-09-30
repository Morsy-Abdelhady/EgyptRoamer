# Trip assistant — architecture, privacy, cost, tests (2026-09-30)

## Decision
The owner asked for an AI travel assistant that must never invent prices, availability, tours, hotels or pages, and must stay affiliate-only. There is no AI provider account or key yet; creating one, choosing the budget and updating the Privacy Policy are owner/legal decisions. It is therefore built in two modes behind one setting (Egypt Roamer → Settings → Trip assistant):

| Mode | What answers | Data leaving the site | Status |
|---|---|---|---|
| **search** (default) | Retrieval over the site's own published pages in the visitor's language; the reply lists those pages and any live offers attached to them | none | **live after this release** |
| **ai** | The same retrieval, plus a short answer written by Anthropic (`claude-haiku-4-5-20251001`) from those pages only | the question and the matched published page texts, to Anthropic | **built, off**: needs `ER_ASSISTANT_API_KEY` in `wp-config.php`, a daily cap, and a Privacy Policy update |
| off | – | – | – |

Without the key, "ai" behaves exactly like "search".

## How it works
- **Server** (Core `includes/assistant.php`): `POST /wp-json/egypt-roamer/v1/assistant {q, lang}`.
  - **Input:** the question is stripped of tags, reduced to one line and capped at 300 characters. `lang` must be one of Polylang's languages, else the default.
  - **Corpus:** published destinations, experiences, guides and pages in that language (homepages and journal pages excluded), cached per language, rebuilt on save.
  - **Scoring:** a title hit counts more than a summary hit, which counts more than a body hit. Terms are weighted by rarity (IDF). Question words are dropped, and Chinese is matched on character pairs. Arabic words also match without the article «ال».
  - **Filtering:** a page must match more than half of the question's words, or half with one of them in its title, so inflected Russian matches. It must also score at least 40 % of the best result. At most 4 pages.
  - **Offers:** only live offers attached to a returned page (`er_get_offers`). Their links are the site's own `/go/<slug>/?pl=assistant&src=<page>` URLs.
  - **No match:** the reply links the language's destination and experience archives.
  - **AI mode:**
    - the system prompt restricts the model to the numbered pages, bans prices/availability/discounts/hours/travel times/bookings/invented places, and treats the visitor's text as a question, never as instructions;
    - the output is plain text with URLs stripped, at most 1,200 characters;
    - all links shown come from retrieval, never from the model;
    - on any provider error or when the daily cap is reached, the reply is pages only.
- **Front end:**
  - The launcher and drawer are server-rendered in `footer.php`, with localized strings.
  - `assets/js/assistant.js` (4.6 KB) is injected on first open (`assistant-loader.js` in both bundles, about 1 KB). Questions asked while it loads are queued.
  - Rendering uses `textContent` only. Links are followed only when they are same-origin, and offers only when they are `/go/`.
  - The fetch omits cookies. Nothing is kept in cookies, localStorage or sessionStorage, and the conversation disappears on reload.

## Privacy
- **Search mode:**
  - the question is not written to the database, to logs by WordPress, or to the browser;
  - the rate limiter stores a counter under a salted HMAC of the IP, expiring after 10 minutes; the IP and the question are not stored;
  - host and CDN access logs record the request path, not the POST body;
  - the notice shown says: "Answers come only from pages published on Egypt Roamer. Your question is not saved."
- **AI mode:**
  - the question and the matched published page texts go to Anthropic;
  - the notice switches to "Answers are written by AI (Anthropic) … Your question is sent to Anthropic; Egypt Roamer does not save it.";
  - **the Privacy Policy must name this processor before AI mode is switched on** (owner/legal; no wording was invented here).

## Security
- **Input:** strings from the question are escaped by construction (`textContent`); no HTML from the server is ever parsed.
- **Links:** they come only from `get_permalink()` or `/go/`, and the client re-checks the origin.
- **Prompt injection:** the model never produces links. Its text is shown as plain text, and it is instructed to treat the visitor's text as a question. The worst outcome is a wrong sentence, flagged by the AI notice.
- **Abuse:**
  - 20 questions per visitor per 10 minutes (HTTP 429);
  - 300-character cap;
  - `ER_ASSISTANT_DAILY_LIMIT` for AI calls (default 300 per day);
  - responses are `no-store`.
- **Secrets:** the key exists only as a PHP constant in `wp-config.php`. It is never in the database, the page, the REST response or git.

## Cost (AI mode, when enabled)
- **Per question:** one Haiku call, with about 1.5–2.5 k input tokens (system prompt plus up to 4 pages × 1,500 characters) and at most 350 output tokens.
- **Monthly cost:** driven by the number of questions. The daily cap bounds it (`ER_ASSISTANT_DAILY_LIMIT`), and the search mode costs nothing.
- **Owner decisions:** the provider account, a budget, and the cap.

## Tests (local)
- **Endpoint:**
  - every language returns pages in that language with localized URLs: ar الأهرامات → Cairo, Giza tour; zh 金字塔 → Giza tour, Cairo; ru Нильский круиз → Nile dinner cruise; fr, de, es, it and en likewise;
  - no-match questions ("weather in Tokyo", "When to go", "xyzzy") → no pages, archive links;
  - "hotel prices in Cairo" → pages only, and no price appears anywhere (the reply contains no generated text in search mode);
  - empty question → 400; unknown language → default; 400-character input → capped;
  - 21st question in 10 minutes → 429, and the UI says "Please try again in a few minutes.";
  - a `<script>` in the question → stripped.
- **Browser** (`tools/qa/assistant.mjs`; en 1440, ar 390, zh 390, ru 1024, de 320), all passing:
  - `assistant.js` is not requested before opening;
  - the launcher is visible, inside the screen, and clear of the dock;
  - Enter on the launcher puts focus in the question field;
  - typed and chip questions are both answered;
  - every link is in the page's language;
  - Tab stays in the dialog;
  - axe on the open dialog: 0;
  - Escape closes it and returns focus to the launcher.
- **Overlays** (`tools/qa/overlays.mjs`): axe 0 with the Saved drawer, search overlay, mobile menu or assistant open, in en, ar and zh.
- **Fitting:** at 320 px the drawer's footer fitted only after a fix (it was wider than the drawer). At the end of the page on desktop, the launcher no longer covers the legal links.
- **Hallucination:** in search mode there is no generated text, so nothing can be invented; prices, availability, tours, hotels and pages can only come from published content and live offers. AI-mode hallucination tests need a key (blocked).

## Not done / blocked
- **AI mode:** blocked on the owner's provider account and key, budget and daily cap, and the Privacy Policy wording (legal). All AI-mode tests, including hallucination prompts, run once the key exists.
- **Affiliate CTA in the assistant:** shown only for live offers. There are none yet, so it cannot be tested end to end (Viator, owner).
- **New UI strings:** 10 strings in 7 languages are drafts pending native review.
