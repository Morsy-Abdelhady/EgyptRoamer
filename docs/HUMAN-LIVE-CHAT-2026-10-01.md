# Chat with the Egypt Roamer team (AI + human), 2026-10-01

Theme 1.2.14, Core 1.2.12. The Trip assistant stays the entry point. It now has a "Chat with Egypt Roamer" option inside the same drawer, and the team answers from **Egypt Roamer → Conversations** in wp-admin. Egypt Roamer stays affiliate-only: there is no booking, payment, price or availability in the chat.

## 1. Architecture
| Part | File | Role |
|---|---|---|
| Server | `plugins/egypt-roamer-core/includes/chat.php` | tables, statuses, REST routes, presence, notifications, retention, privacy export/erase, admin page |
| Visitor UI | `themes/egypt-roamer/src/js/assistant.js` → `assets/js/assistant.js` | the assistant and the chat in one drawer; loaded only when the drawer is first opened |
| Markup and strings | `themes/egypt-roamer/footer.php` | entry block, start form, chat bar; strings in the page's language |
| Team UI | `plugins/egypt-roamer-core/assets/chat-admin.js`, `chat-admin.css` | the inbox (vanilla JS, no build step, loaded on that admin screen only) |
| Settings | Egypt Roamer → Settings → Trip assistant | **Chat with the team**: On/Off; **Delete closed chat conversations after**: 90 days by default |

No third-party chat, realtime or AI service was added. The assistant's own AI mode (Anthropic) stays optional and off until a key is configured.

## 2. Data model
`{prefix}er_chat_conversations`:
- `id`, `public_id` (24 hex, random), `token_hash` (HMAC-SHA256 of the visitor's 64-hex secret);
- `status`, `visitor_name`, `visitor_email` (both optional), `lang`, `entry_url`, `current_url` (paths on this site only);
- `assigned_to`, `agent_read_id`;
- `created_at`, `updated_at`, `last_message_at`, `closed_at`.

`{prefix}er_chat_messages`:
- `id`, `conversation_id`;
- `sender` (visitor, agent, ai or system), `user_id` (team member);
- `body` (plain text, at most 2,000 characters), `meta` (JSON: the assistant's page results);
- `client_id` (idempotency), `internal` (team-only notes and the audit trail), `created_at`.

Not stored: IP address, user agent, cookies. Rate limits use a salted hash of the IP in a short-lived transient (1 minute or 1 hour). The tables are created by `dbDelta` on update (`ER_CORE_DB_VERSION` 2). Messages are not kept in `wp_options`.

## 3. Statuses
`ai` (the assistant answers), `requested` (a person was asked for while the team is online), `waiting` (asked while the team is offline: "leave us a message"), `human` (a team member owns it), `closed`, `archived`.

| Event | → status |
|---|---|
| Visitor starts a chat | `requested` if a team member is online, else `waiting` |
| Team replies or "Take over conversation" | `human` (assigned to that member if unassigned) |
| "Return to AI" | `ai`: each visitor message gets an assistant answer, kept in the conversation |
| Visitor "Ask for the team" (from `ai`) | `requested` / `waiting` |
| "Close" (team) or "End chat" (visitor) | `closed` |
| Visitor writes into a closed chat | back to `requested` / `waiting` (reopened, team notified) |
| "Archive" | `archived` (out of the inbox; deleted with closed ones after the retention period) |

The assistant answers **only** in `ai`. While a person owns the conversation (requested, waiting, human), nothing automatic replies, so there can be no conflicting answers. This is verified in `tools/qa/chat.mjs`.

## 4. Visitor experience
- **Entry:** below the assistant sits "Need personal help? Chat with Egypt Roamer", with the real availability next to it: "Team is online", or "Leave us a message and we'll get back to you." There are no response-time or "24/7" claims.
- **Asking for a person:** a question like "Can I talk to someone?" (keywords in all 8 languages) gets "Of course. I can connect you with the Egypt Roamer team." plus the button, instead of a page search.
- **Start form:** "Before we connect you with our team:" with an optional name, an optional email and the message. A storage notice links the Privacy Policy. Anonymous chat is possible.
- **Context:** the questions asked so far in this visit, and the page titles the assistant answered with, go into the conversation, so the visitor doesn't repeat themselves.
- **The chat:** in the same drawer, the title becomes "Live chat" and the field becomes "Type a message…".
  - The status line reads "Waiting for the team…", "You're chatting with the Egypt Roamer team.", "You're back with the trip assistant…" or "This chat is closed…".
  - The chat bar offers "Ask for the team" and "End chat".
- **Delivery states:** a message shows as pending, then sent. If the server rejects it, it shows "Message couldn't be sent. Try again." with a Retry button, and the retry carries the same id, so there is no duplicate. A poll failure shows "Connection interrupted. Retrying…" and backs off.
- **Persistence:** the conversation id and secret token are kept in this browser's `localStorage` (`er-chat`), so a reload or a new page continues the chat. "End chat" removes them.

## 5. Team experience (Egypt Roamer → Conversations)
- **Inbox:**
  - filters: All, Unread, Active, Waiting, Mine, Assistant, With the team, Closed;
  - search: name, email, ID or message text;
  - sort: last activity, newest, oldest.
  - Unread conversations are marked; each row shows status, language, page and a preview.
- **Conversation:**
  - visitor name, email, language, current page, entry page, status, assignee, start and last activity;
  - the full transcript, including the assistant's earlier answers and team-only notes (the audit trail);
  - actions: Take over conversation, Return to AI, Close, Reopen, Mark unread, Archive, Assign;
  - replies with Ctrl+Enter, showing sending, sent or failed with Retry.
- **Presence:** "Your status" is Online, Away or Offline. Visitors see "Team is online" while at least one member has the inbox open (checked within 150 s) and is set to Online. Away and Offline are kept until changed.
- **Unread badge:** "Conversations (n)" in the menu. It is updated on every admin screen through WordPress's Heartbeat (no extra polling) and on the inbox itself.
- **Mobile:** below 782 px the screen shows the list, then the conversation, with a "← Inbox" back button.

## 6. API (`/wp-json/egypt-roamer/v1/chat/…`)
| Route | Who | Notes |
|---|---|---|
| `POST status` | public | `{online}`; called only when the drawer opens |
| `POST start` | public | honeypot plus "filled in under 1.5 s" check; 10 per IP-hash per hour; returns `{id, token, status, messages}` |
| `POST poll` | visitor token (`X-ER-Chat` header) | `after` = last message id |
| `POST send` | visitor token | `client_id` makes it idempotent; 15 per conversation per minute; reopens a closed chat; an assistant answer in `ai` |
| `POST human`, `POST end` | visitor token | – |
| `GET team/list`, `GET team/{id}`, `POST team/{id}/reply`, `POST team/{id}/action`, `POST team/presence` | `manage_er_conversations` plus the REST nonce (cookie auth) | – |

All visitor routes are POST and answer `Cache-Control: no-store, private` and `X-Robots-Tag: noindex`, so no page cache or CDN stores them. A wrong or missing token gets 404, the same answer as an unknown conversation, so ids can't be probed.

## 7. Real-time mechanism
Short polling, chosen over WebSockets or SSE:
- GoDaddy Managed WordPress has no WebSocket server.
- Long-lived SSE or long-poll requests would tie up PHP workers.
- An external realtime provider would mean a new processor of personal data, a cost and lock-in, and needs the owner's approval.

**Visitor:**
- Every 3 s while there is activity, 8 s after a minute of silence, 15 s after about 5 minutes.
- Exponential backoff on errors.
- **Only** while the drawer is open and the tab is visible; closing the drawer or hiding the tab stops it at once.

**Team:** the list every 5 s and the open conversation every 3 s, only while the inbox tab is visible.

**Cloudflare:** a visitor's polling (≤ 20 requests a minute) is far below what triggered the 429s seen today, which came from automated checks. If Cloudflare rate-limits real chats, the owner can add a WAF exception for `/wp-json/egypt-roamer/v1/chat/*`.

## 8. Security
- **Visitor access:** a token from `random_bytes(32)`, compared in constant time (`hash_equals`) against an HMAC kept server-side. Database ids are never exposed to visitors; the public id is random.
- **Team access:** capability `manage_er_conversations` (administrators and editors), checked on every team route; the REST nonce is required (cookie auth).
- **Input:** all of it is cleaned (`wp_strip_all_tags`, control characters removed, length limits). Pages are kept only if they are this site's own paths, emails only if they pass `is_email`.
- **Output:** `textContent` in both UIs (never `innerHTML`), and `esc_*` in PHP.
- **Abuse:** rate limits, a honeypot and a timing check. Messages are never written to debug logs. The audit trail (taken over, returned, closed, reopened, archived, assigned) stays with the conversation, with the team member's id.
- **Attacks tested** (`chat.mjs`, plus the API smoke test):
  - another conversation's id with your own token → 404;
  - no token → 404;
  - team routes without a login → 401;
  - `<script>` in a message → stripped;
  - 5,000 characters → cut to 2,000;
  - the same `client_id` twice → stored once;
  - 17 quick messages → 429 after 15;
  - the honeypot and too-fast starts → 400.
- **Never public:** no post type, no page, no sitemap entry, no `wp/v2` exposure.

## 9. Privacy
- **Stored:** the messages, an optional name and email, the language, and the page paths.
- **Where:** in the site's own WordPress database (GoDaddy), seen only by team members. No third party receives chats (the optional AI mode is separate and off).
- **Retention:** a daily job deletes closed, archived and assistant-only conversations whose last message is older than the setting (90 days by default). Open conversations are never deleted.
- **Export and erasure:** WordPress Tools → Export/Erase Personal Data covers chat conversations by email address.
- **In the visitor's browser:** the conversation id and token in `localStorage`. This is necessary for the chat they started and is removed by "End chat"; it is not a cookie.

**Privacy Policy: owner and lawyer review needed before the chat is promoted.** The policy has no chat section yet; the chat form shows the storage notice when the data is collected. Proposed English section for "What we handle, and why" (translate through `content/legal/i18n` once approved):

> **Chat with our team.** If you start a chat in the Trip assistant, we keep your messages, the name and email address you choose to give (both optional), the site language, the pages you wrote from, and the questions you asked the assistant in that visit, so that our team can reply and follow up. Chats are stored in our website's administration area; only the Egypt Roamer team can read them. Your browser keeps a random code for the chat in its local storage so that you can continue it; "End chat" removes it. To limit abuse, a scrambled (hashed) form of your IP address is kept for up to one hour, not with your messages.

And for "How long we keep it":

> Chat conversations: deleted automatically 90 days after their last message once they are closed; open conversations are kept until they are closed.

## 10. Localization
- **Interface:** 29 new strings, drafted in 7 languages in `tools/i18n/new-strings.json` and compiled into the theme's `.l10n.php` files (274 of 274 strings covered per language). Like the other WordPress-only strings, they are **not yet reviewed by native speakers**; review before each language launches.
- **RTL:** Arabic mirrors through logical CSS properties. The visitor's bubbles sit on the reading side (right in English, left in Arabic), as verified.
- **Team replies:** the team can write in any language. The inbox shows the visitor's language.

## 11. Accessibility
- **Drawer:** the same dialog and focus trap as before. Opening it focuses the question field, and Escape closes it and returns focus to the launcher.
- **Forms:** every field has a visible `label`. The error uses `role="alert"`, the status line `role="status"`, and the log `aria-live="polite"`.
- **Hidden parts:** hidden sections use `[hidden]`, so they are out of the tab order.
- **axe:** 0 violations on the open chat (en 390 px, ar 375 px) and with every overlay open (`overlays.mjs`).
- **Team inbox:** native buttons, `aria-pressed` filters, `aria-current` on the open row, screen-reader labels on search, sort and reply.

## 12. Performance
- **Page load:** nothing new. No chat request and no chat script before the drawer opens (checked in `chat.mjs`).
- **The drawer's script** (`assistant.js`, loaded on first open): 5.4 KB gzipped, was 1.8 KB.
- **Page HTML:** about 2 KB more (the chat markup and strings, in the footer).
- **`home.js` and `site.js`:** unchanged by the chat.

## 13. Tests
`tools/qa/chat.mjs` (local, 32 checks, all ok):
1. no chat request before opening;
2. start;
3. inbox unread, with language and page;
4. live reply both ways without reload;
5. Return to AI, the AI answers, Ask for the team, Take over, no AI reply while a person owns it, no duplicate bubbles;
6. close;
7. three visitors isolated;
8. offline → "leave us a message";
9. tokens and team routes;
10. Arabic RTL and English layout, plus axe;
11. the inbox on a phone, the unread badge, and no script errors.

**Regression run on this release:** `overlays.mjs`, `assistant.mjs`, `keyboard.mjs`, `launcher-keyboard.mjs`, `responsive.mjs` (see the launch-gate report).

## 14. Production verification
See the launch-gate report (section D14) for what was verified after the deploy.
- **Constraint:** the human side needs a team member logged into wp-admin. I can do that only in the owner's Chrome, and posting in the live inbox is an outward action.
- **Plan:** an anonymous visitor test message labelled "QA test", answered in the owner's inbox, then closed and archived.

## 15. Files changed
- **Core:**
  - `includes/chat.php` (new), `assets/chat-admin.js` (new), `assets/chat-admin.css` (new);
  - `includes/settings.php` (2 settings);
  - `egypt-roamer-core.php` (require, DB version 2, cron cleanup).
- **Theme:**
  - `src/js/assistant.js` → `assets/js/assistant.js`;
  - `footer.php`;
  - `assets/css/pages.css` (chat block);
  - `languages/*.l10n.php`.
- **Tools:** `tools/i18n/new-strings.json`, `tools/qa/chat.mjs` (new).

## 16–17. Commits and deployment
Through `main` → `deploy-production.yml`. The tables are created on the first request after deploy (DB version check). No manual database step.

## 18. Remaining external dependencies
- **Email notifications:** sent with `wp_mail` to Settings → "Send contact messages to", for new and reopened chats, at most one per conversation per 10 minutes, without the message text. They are **not reliable** until the domain's mail is fixed: `egyptroamer.com` has no MX record and no SPF, so delivery from the host is unverified. The inbox and its badge do not depend on email.
- **AI suggested replies:** not built. The assistant's AI mode needs the Anthropic key, budget and owner approval first, and a suggestion feature would add provider cost. The proposed design: an "AI suggestion" box that the agent must edit or approve and that never sends itself.
- **Browser push notifications:** not built (they need explicit permission and a service worker). The badge and email cover it.
- **Safari / iOS:** not tested; only Chrome is available here. The code uses only widely supported APIs: `fetch`, `localStorage`, `MutationObserver`, `CSS.escape` (Safari ≥ 10).
- **Privacy Policy update:** owner and lawyer (section 9).
- **Native-speaker review** of the 29 strings.
