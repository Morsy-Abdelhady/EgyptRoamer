# Final launch preparation — 2026-09-30

Decision log for the "final autonomous launch preparation" run. Status ledger: `docs/AUTONOMOUS-EXECUTION-LEDGER-2026-09-30.md`. **Indexing stays OFF.**

## Baseline
- `main` = `6402c3a`, clean tree. Production: theme 1.2.2, Core 1.2.6 (deploy #16), cache flushed after #16.
- Owner facts supplied in this run: legal entity, legal form, country, address, commercial registration 17855, public email `info@egyptroamer.com`, phone `+20 10 6049 4260`, private receiving mailbox (not published anywhere).

## Production data changes (all through the owner's signed-in wp-admin, REST API, one object at a time)

| Object | Before | After | Verified |
|---|---|---|---|
| er_experience 202 (Arabic Giza) title | «جولة خاصة إلى أهرامات الجيزة وأبو الهول» | «جولة خاصة إلى أهرامات الجيزة وأبي الهول» | slug and body byte-identical; live H1 shows the new title |
| page 62 `affiliate-disclosure` | draft, 93-word draft text | **published**, `content/legal/en/affiliate-disclosure.html` | stored content = file; 200 |
| page 3 `privacy-policy` | draft, WordPress template | **published**, `content/legal/en/privacy-policy.html` | = file; 200 |
| page 64 `cookies` | draft stub | **published**, `content/legal/en/cookies.html` | = file; 200 |
| page 60 `contact` | draft (note + form) | **published**, `content/legal/en/contact.html` | = file; 200; form present |
| page 63 `terms` | draft stub | still **draft**, `content/legal/en/terms.html` | missing: governing law, liability wording (owner/lawyer) |
| Core setting `contact_email` | empty | the owner's receiving mailbox | other settings unchanged (disclosure 62, privacy 3, retention 730) |
| Legal menus (de 334, fr 337) | privacy item → `/de/privacy-policy/` etc. | privacy item → `/privacy-policy/`; items added: de 424–427 (Cookies, Affiliate Disclosure, Contact, Terms), fr 428–430 (Cookies, Affiliate Disclosure, Contact) | a batch script stopped with an error after these two menus; ar, es, it, ru, zh untouched (theme 1.2.3 completes their row in code). The de "Terms" item is hidden by theme 1.2.4 while Terms is a draft |

After these, further wp-admin writes from this session were refused by the session's permission check (an automated "unrequested commit in a connected app" rule), so the remaining admin items below are owner steps.

## Facts behind the legal texts (checked, not assumed)
- Cookies on a real visit (production, 2026-09-30): only `__cf_bm` (Cloudflare bot management). No Google/GTM, no GoDaddy RUM (`_tccl_*` absent, no `wsimg.com` requests). Browser storage: `localStorage` (language, saved items), `sessionStorage` `er-intro`.
- **Consent decision:** no non-essential cookies or trackers are set, so no consent banner is added; the Cookie Policy lists exactly what is set and promises consent before any non-essential cookie is added.
- Code facts: contact messages stored as private `er_message` posts, emailed with `Reply-To` = sender; form rate limit keeps a hashed IP+date key for **10 minutes** (not a day, as an older doc said); click log without IP/UA/cookie, 730 days; newsletter sign-ups stored unconfirmed (no CRM, nothing sent yet).
- No compliance or legal-basis claims; no governing law invented; the registration date and any tax number are not published.

## Email / DNS (read-only lookups, 2026-09-30)
| Record | Value |
|---|---|
| MX `egyptroamer.com` | **none** → `info@egyptroamer.com` cannot receive mail today |
| SPF (TXT) | **none** |
| DKIM | none at `default`, `selector1`, `google` |
| DMARC | `v=DMARC1; p=quarantine; adkim=r; aspf=r; rua=…onsecureserver.net` |
Consequence: contact-form notifications (sent as `wordpress@egyptroamer.com`) are likely to be quarantined by Gmail under the DMARC policy. Messages are never lost: each is stored under Egypt Roamer → Contact messages, with a "notification sent/FAILED" flag. **Owner:** set up mail for the domain (GoDaddy email forwarding for `info@` to the receiving mailbox, or a mailbox), which creates MX/SPF; add DKIM for the sending service (or an SMTP plugin sending as `info@`); then send a test from `/contact/`.

## Viator
- Account (read-only in the owner's Chrome, earlier today): `pid=P00322579`, `mcid=42383`, `medium=link`, `campaign=<label>`; links/widgets/banners/Selector/Shop; **no API key**.
- Chosen: affiliate links through Provider → Offer → `/go/`. No widgets (third-party JS, cookies/consent, layout, RTL), no API (not available).
- Security tests (local, real parameters, TEST data removed): array params, XSS in `pl`, huge `src`, injected `pid`/`mcid`/`campaign`/`url` → tracking kept, placement `direct`; unknown offer stays on the site; traversal 404; **paused offer, inactive provider, deleted offer → `/go/` stays on the site and the offer box disappears; the page and Key facts remain.**
- **Owner steps (in this order):**
  1. Egypt Roamer → Providers → Add: title `Viator`, website `https://www.viator.com/`, allowed domains `viator.com`, status Active.
  2. Per experience, choose the Viator page (Selector, signed in; or copy a viator.com product/attraction URL).
  3. Egypt Roamer → Offers → Add: provider Viator; Target URL = that page; Tracking → extra parameters, one per line: `pid=P00322579`, `mcid=42383`, `medium=link`, `campaign=er-{lang}-{placement}-{page}`; CTA "Check availability"; link the experience; publish.
  4. Tell me the offer slug: I verify CTA → `/go/` → click row → Viator on production.

## Content (owner steps; texts ready)
**Archive intros** (Egypt Roamer → Settings; English — translations only when reviewed, the theme hides untranslated intros in other languages since 1.2.3):
- Destinations: *Seven places, seven different Egypts: Cairo and Giza, Alexandria on the Mediterranean, Luxor and Aswan on the Nile, Hurghada and Sharm El Sheikh on the Red Sea, and the Siwa oasis in the Western Desert. Each guide covers what to see, when to go and the questions travellers ask most.*
- Experiences: *Tours and day trips across Egypt, from a private visit to the Giza plateau to an early start for Abu Simbel. Each page explains what the experience is, who it suits and what to know before you book.*
- Guides (only after step below): *Planning guides for Egypt: the best time to go, a first week's itinerary, Cairo beyond the pyramids, and ten places most visitors miss.*

**Guides ready to publish** (English, sourced, no prices/hours/travel times; `docs/CONTENT-SOURCES.md`): 33 *The Best Time to Visit Egypt, Month by Month*, 34 *7 Days in Egypt: The Perfect First Itinerary*, 37 *Cairo Travel Guide: Beyond the Pyramids*, 39 *10 Hidden Gems Most Visitors Never See*. **Keep as drafts:** 35 safety, 36 Nile cruises, 38 costs (no verified data).

**WordPress sample content:** none present (no posts; no "Sample Page").

## Cache
Deploys do not flush. After the final deploy, flush once (GoDaddy → Quick Links → Flush Cache) so every cached page shows the new footer; until then cached pages keep their consistent older version.
