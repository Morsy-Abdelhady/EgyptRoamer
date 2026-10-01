# Privacy and Cookie Policy changes required by the 1 October releases (draft)

**Status: DRAFT. Not legal advice and not approved.** Each item needs:
- **(L)** review by the owner and a lawyer, before it's published;
- **(T)** native-speaker translation into the 7 languages, through `content/legal/i18n/<lang>.json` and `python tools/legal.py`, after (L).

The published policies were last updated on 30 September 2026. They describe the contact form, the newsletter, partner-link clicks, spam protection and browser storage, but not the features below.

## What changed, with the facts the text must reflect
| Feature | Data | Where | How long | Third parties |
|---|---|---|---|---|
| Trip assistant (search mode, the current setting) | the question, processed to find matching pages; **not stored** | server memory only | none | none |
| Trip assistant: rate limit | hashed (HMAC, salted) IP address | WordPress transient | 10 minutes | none |
| Trip assistant, **AI mode** (not active; needs a key) | the question and the matching page texts | sent to **Anthropic** (USA) to write the answer; not stored by Egypt Roamer | Anthropic's API retention terms apply | **Anthropic** |
| Chat with the team | messages; name and email (optional); site language; entry and current page path; the assistant questions and result titles of that visit; time of each message | WordPress database (GoDaddy) | closed conversations deleted **90 days** after the last message (setting); open ones kept until closed | GoDaddy (hosting), Cloudflare (delivery) |
| Chat: email notification | visitor name (if given), language, page, link to the inbox (**not** the message text) | email to the team's address | the team's mailbox | the team's mail provider |
| Chat: rate limits | hashed IP address | transients | 1 hour (new chats), 1 minute (messages, keyed to the conversation) | none |
| Chat: browser storage | `er-chat`: the conversation's random id and secret code | the visitor's browser (`localStorage`) | until "End chat" or the browser's storage is cleared | none |
| Stale-page check | `er-build` (the last site version seen, with a time), `er-build-reload` (one-reload marker) | browser storage (`localStorage`, `sessionStorage`) | `er-build` until cleared; `er-build-reload` for the browser session | none |
| Realtime | none: the chat uses short polling to this website only; **no realtime provider** | – | – | none |

## Proposed text: Privacy Policy, "What we handle, and why" (add)
> **Trip assistant.** Questions you ask the Trip assistant are matched against the pages of this site to answer you; we do not store them. To limit abuse, a scrambled (hashed) form of your IP address is kept for 10 minutes. **(L)**

> **Chat with our team.** If you start a chat in the Trip assistant, we keep your messages, the name and email address you choose to give (both optional), the site language, the pages you wrote from, and the questions you asked the assistant in that visit, so that our team can reply and follow up. Chats are stored in our website's administration area; only the Egypt Roamer team can read them. When a chat starts or reopens, our team gets an email notice with your name if you gave one, the language and the page, but not your message. To limit abuse, a scrambled (hashed) form of your IP address is kept for up to one hour, not with your messages. **(L)**

Only if AI answers are switched on (`assistant_mode = ai` with an Anthropic key), and before that happens:
> **AI answers.** When AI answers are on, your question and the text of the matching pages on this site are sent to Anthropic, PBC (USA) to write a short answer. Anthropic processes them under its own terms; we do not store your question. **(L)**

## Proposed text: Privacy Policy, "How long we keep it" (add)
> Chat conversations: deleted automatically 90 days after their last message once they are closed; open conversations are kept until they are closed. Spam-protection data for the Trip assistant and the chat: 10 minutes to 1 hour. **(L)**

## Proposed text: Privacy Policy, "Services that process data for us"
- No new service in search mode, and none for the chat (both run on our own website).
- If AI answers are switched on: add **Anthropic** (see above). **(L)**

## Proposed text: Cookie Policy, the browser-storage section (add to the list)
> - `er-chat` (local storage), only if you start a chat: a random code that lets you continue your conversation. Removed when you end the chat.
> - `er-build` (local storage) and `er-build-reload` (session storage): the version of the site your browser last saw, so that an outdated copy of a page is refreshed automatically. They contain no personal information. **(L)**

## Also to decide (owner)
- Whether chat email notifications should include the message text. Today they don't, which is the more private default.
- Whether to show the retention period in the chat's start form. Today it links the Privacy Policy.
- **(T)** The 29 interface strings of the chat (`tools/i18n/new-strings.json`) are drafted, not reviewed. The policy texts above have no translations yet; they must not be machine-published.
