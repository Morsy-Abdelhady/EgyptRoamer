# Legal & trust pages — requirements and gaps (2026-09-29)

**These pages are launch blockers.** Nothing here is legal advice, and no legally binding text has been written.
- Privacy, Terms and Cookies need the owner's facts and a legal review before publication.
- Draft text appears only for two non-legal pages (§3), built from verified project policy, and is marked **DRAFT — owner review**.
- Nothing has been published.

## 1. Current state (production, read-only, 2026-09-29)

| Page | Slug | Status | Length | Languages | Assigned in settings |
|---|---|---|---|---|---|
| Privacy Policy | `privacy-policy` | draft | 610 words (likely the WordPress template) | en only | WordPress privacy page; Core `privacy_page` = 3 |
| Terms of Use | `terms` | draft | 13 words (stub) | en only | — |
| Cookie Policy | `cookies` | draft | 20 words (stub) | en only | — |
| Affiliate Disclosure | `affiliate-disclosure` | draft | 93 words | en only | Core `disclosure_page` = 62; a short disclosure line is already shown next to every offer |
| Contact | `contact` | draft | 30 words (editorial note + a working form) | en only | Core `contact_email` is **empty** |
| FAQ | `faq` | draft | 22 words (stub) | en only | — |
| How We Choose | `how-we-choose` | draft | 20 words (stub) | en only | — |
| Our Story | `about` | draft | 19 words (stub) | en only | — |
| Partner With Us | `partner-with-us` | draft | 17 words (stub) | en only | — |

- **Menus and footer:** no links to any of these pages yet.
- **Public URLs:** `/contact/` and `/privacy-policy/` return 404 while the pages are drafts.

## 2. What the site actually processes (facts for the privacy and cookie texts)

From the code and production, 2026-09-29:

| Data / technology | Where | What exactly |
|---|---|---|
| **Newsletter sign-up** | Core `leads.php` | Email, language, chosen interests, source, sign-up date, in an own table. FluentCRM double opt-in is used if FluentCRM is installed (it isn't). Admin can export CSV |
| **Contact form** | Core `leads.php` | Name (optional), email, message: stored as a private "Contact message" in WordPress, then emailed to Core's `contact_email` (**not set**) |
| **Rate limiting** | Core | A hashed IP + date key in a temporary transient (anti-spam), valid for one day. No IP is stored in any table |
| **Affiliate click log** | Core `clicks.php` | Time, offer, provider, destination/tour/experience/activity, source page, placement, CTA, UTM tags, language. **No IP, no user agent, no cookie.** Kept `retention_days` = **730** days, then purged |
| **Outbound affiliate redirect** | `/go/…` | Redirects to the provider with UTM/sub-ID parameters. The provider's own cookies and policy then apply |
| **Browser storage (own)** | theme JS | `localStorage`: chosen language, "saved" items (the heart icon). `sessionStorage`: scroll position. **No own cookies** readable by scripts |
| **Analytics (own)** | Core | Google Tag Manager is supported but **no GTM ID is set**. Consent Mode default = **denied** |
| **GoDaddy Real User Metrics** | host (System Plugin) | Scripts from `img1.wsimg.com`; cookies `_tccl_visitor`, `_tccl_visit`, `_scc_session`; **no consent today**. Recommended off (`docs/PRODUCTION-ONE-OFF-ACTIONS.md` A3) |
| **Site Kit by Google** | plugin | Active. No Google tags seen in the public page source today. Consent needed if Analytics is connected |
| **Akismet** | plugin | Anti-spam; sends submitted comment/form data to Automattic when it checks content (comments are closed) |
| **Hosting / CDN** | GoDaddy Managed WordPress; Cloudflare in front | Server logs and CDN processing by the host |
| **Images** | Unsplash (hot-linked stand-ins) | Visitors' browsers request `images.unsplash.com`, which sees the IP |
| **Fonts** | self-hosted (`fonts.css`) | no third-party font requests |

## 3. Per-page requirements

### Privacy Policy (legal — owner/lawyer)

**Must state:**
- the controller (legal name, address, contact email);
- what data, from §2;
- purposes and legal bases;
- recipients: GoDaddy, Cloudflare, Automattic/Akismet, Google (if Site Kit/Analytics), affiliate providers (after redirect), Unsplash;
- international transfers;
- retention: subscribers until unsubscribe; contact messages (**owner to decide**); clicks 730 days;
- rights (access, deletion, objection, complaint to an authority) and how to exercise them;
- children;
- changes and a last-updated date.

**Missing facts from the owner:**
- the legal entity and address, and the country of establishment (this decides which law applies);
- the privacy contact email;
- contact-message retention;
- whether Site Kit/Analytics will run;
- whether the GoDaddy RUM goes off;
- the target markets (EU visitors trigger the GDPR).

**Translations:** one per language that will be indexed, or an explicit English-only decision.

### Cookie Policy (legal — owner/lawyer)

- **Must list:** every cookie and storage item actually used (§2), with purpose and duration.
- **Consent:** needed for any non-essential tracking. Own storage (language, saved items) is functional.
- **Consent mechanism:** if GoDaddy RUM or Site Kit analytics stay on, a consent banner is required first. Core's consent default is already "denied", but a banner UI isn't built. It's an owner decision (it would be a separate feature, not a plugin by default).

### Terms of Use (legal — owner/lawyer)

- **Must state:**
  - who operates the site;
  - that it's an **independent publication that takes no bookings or payments**, and that bookings are contracts with the providers;
  - editorial content is general information: facts go stale, so check on the day;
  - affiliate links;
  - intellectual property, including the brand assets and the third-party photos used as stand-ins;
  - liability limits;
  - governing law;
  - contact.
- **Missing:** the legal entity, governing law, and the owner's liability wording from the lawyer.

### Affiliate Disclosure (trust, legal-adjacent)

- **Must state:**
  - that Egypt Roamer earns a commission from partners when readers book through its links, at **no extra cost** to the reader (confirm with each programme's terms);
  - that editorial choices aren't paid for (§ How We Choose);
  - which link types are affiliate links (all `/go/` links);
  - that it's updated when partners change.
- **Current draft:** 93 words; the owner must confirm it matches the real programmes. **It must be published before any offer goes live.**

### Contact (trust)

- **Needs:** the intended inbox (owner) → set Core → Settings → `contact_email` → **send test** (email delivery has never been tested).
- **Page text:** a short intro, the response expectation (owner), and a note that it's not a booking desk (bookings go through providers).

### FAQ (trust; can be drafted from project facts)

Suggested questions, with answers limited to verified facts:
- "Do you sell trips?" No: independent publication, affiliate links, no bookings or payments.
- "Who do I contact about a booking?" The provider you booked with.
- "How do you choose what to recommend?" Links to How We Choose.
- "Do you earn money from links?" Links to Affiliate Disclosure.
- "Are prices shown current?" Prices are shown only with the date they were checked, and may change.
- "Which languages?" The 8 site languages.

**The owner confirms before publishing.**

### How We Choose — **DRAFT for owner review** (from `docs/CONTENT-SOURCES.md` policy and the affiliate architecture)

> Egypt Roamer is an independent travel publication about Egypt. We don't sell trips, take bookings or handle payments. When you book, you book directly with the provider.
>
> **What we write about.** Destinations, experiences and guides we can describe from verifiable sources. Facts such as UNESCO listings, museum openings and historical dates are checked against official or reputable sources, and those sources are recorded.
>
> **What we leave out on purpose.** Prices, opening hours, travel times and ratings change too often to keep accurate. We tell you to check them on the day instead of printing numbers that may be wrong.
>
> **How partners appear.** Some links are affiliate links (see our Affiliate Disclosure). A partner can't buy a place in our editorial text. Offers are shown next to the editorial content, clearly labelled, and only from providers we have a real agreement with.
>
> **Opinions are marked as opinions** ("most visitors…", "we'd give it three days").

The owner must confirm every sentence is true for how the business will operate, especially the partner rule.

### Our Story (trust — owner only)

Placeholder only; it needs the owner's own facts: who is behind Egypt Roamer, why, and the connection to Egypt. **Nothing can be drafted without inventing facts.**

### Partner With Us (trust — owner)

- **Needs:** what partnerships the owner accepts (affiliate programmes only; no paid editorial) and a contact route.
- The page may state the editorial rule from How We Choose.

## 4. Footer and menus (before launch)

- **Legal row:** Privacy, Terms, Cookies, Affiliate Disclosure.
- **Trust row:** Contact, FAQ, How We Choose, Our Story, Partner With Us.
- Menus are managed in wp-admin → Appearance → Menus (the theme hides links to empty sections). Add the pages once they're **published** in each language that is indexed.
- **Check:** links on every page in every language; no 404s; the Cookie Policy link sits next to any consent banner.

## 5. Launch checklist (legal / trust)

- [ ] Owner supplies: legal entity, address, country, privacy contact email, contact inbox, retention for contact messages, target markets.
- [ ] Lawyer reviews Privacy, Terms and Cookies; the owner approves the final text.
- [ ] Tracking decisions made (GoDaddy RUM off; Site Kit/Analytics yes or no); consent banner built if any tracking stays.
- [ ] Affiliate Disclosure matches the real programmes; published before the first live offer.
- [ ] How We Choose, FAQ, Our Story and Partner With Us approved and published.
- [ ] Translations of all 9 pages for every indexed language, or an English-only decision recorded.
- [ ] Contact email set; send test received.
- [ ] Footer and menus link all pages in all languages; public 404 check passes.
