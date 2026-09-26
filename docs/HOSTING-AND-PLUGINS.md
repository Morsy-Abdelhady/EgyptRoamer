# GoDaddy Managed WordPress, plugins, email

**Nothing in this file was verified on GoDaddy.** No GoDaddy account was available, and this sandbox's network blocks godaddy.com and wordpress.org. Every compatibility cell therefore reads **NOT VERIFIED** unless it was tested locally, and local tests are marked as such. Before installing anything, check GoDaddy's current blocklist: <https://www.godaddy.com/help/blocklisted-plugins-8964>.

## Plugin audit

| Plugin | Purpose | Required | GoDaddy compatible | Duplicate functionality? | Performance impact | Decision |
|---|---|---|---|---|---|---|
| **Egypt Roamer Core** (this repo) | content models, affiliate engine, `/go/`, click log, reports, leads, SEO guards, analytics events | **yes** | NOT VERIFIED (plain WordPress APIs; no filesystem writes, no SMTP, no custom cron server) | none | small: 1 custom table, request-level caches | install |
| **Rank Math SEO** (free) | titles, descriptions, canonicals, sitemap, schema, OG, redirections, Search Console verification | yes | NOT VERIFIED | must be the **only** SEO plugin (no Yoast/AIOSEO) | moderate; disable unused modules | install |
| **Polylang** (free) | languages, translated URLs, hreflang, string translation | yes, if more than English launches | NOT VERIFIED on GoDaddy; **tested locally** (3.8.9 with WordPress 7.1.2): per-language URLs, reciprocal hreflang, translated front pages | none | small | install when the first translation is reviewed |
| **FluentCRM** | newsletter lists, segmentation (destination interests), welcome sequences, double opt-in | recommended | NOT VERIFIED | none (Core hands sign-ups to it) | moderate (admin); runs WP-Cron | install when email marketing starts |
| **Transactional email** (e.g. FluentSMTP or WP Mail SMTP, set to an **API mailer**: Brevo, Postmark, SendGrid, Amazon SES, Mailgun) | reliable delivery of contact notifications and CRM email | **yes** | NOT VERIFIED — check the blocklist; use the provider's HTTP API, not SMTP ports | one mailer plugin only | small | install one |
| **Consent management** (Google-certified CMP, e.g. Complianz or CookieYes) | cookie banner, Google Consent Mode v2 updates | yes, for EU/UK visitors | NOT VERIFIED | Core only sets Consent Mode **defaults**; set Core to "off" if the CMP sets them | small | install one |
| Google Site Kit | GA4/Search Console dashboards | **no** | — | duplicates GTM/GA4 setup | — | do not install |
| WooCommerce / Bookings | direct booking | **no** | — | out of scope (affiliate-only) | — | do not install |
| ThirstyAffiliates, Pretty Links, other link cloakers | affiliate redirects | **no** | — | duplicates Core `/go/` (only one redirect system) | — | do not install |
| WP Rocket, W3TC, other page caches | caching | **no** | — | GoDaddy provides server caching | — | do not install |
| UpdraftPlus and other backup plugins | backups | **no** | — | GoDaddy provides backups | — | do not install |
| Wordfence and other full security suites | firewall / scanning | **no** | — | GoDaddy provides WAF and malware scanning | — | do not install |
| Jetpack Stats, MonsterInsights and other statistics plugins | statistics | **no** | — | GA4 through GTM | — | do not install |
| ACF | fields | **no** | — | Core has its own field engine | — | not needed |
| Image optimisation plugin | WebP, compression | **optional** | NOT VERIFIED | the theme outputs WebP sub-sizes when the server's image library supports it; GoDaddy's CDN/optimisation is NOT VERIFIED | — | decide after measuring on GoDaddy |

## Compatibility report (per area)

| Area | Implementation | Status |
|---|---|---|
| SEO | Rank Math + Core guards (`rank_math/frontend/robots`, `rank_math/sitemap/entry` filters) | NOT VERIFIED (Rank Math not installed here); core-sitemap fallback verified locally |
| Multilingual | Polylang | verified locally; on GoDaddy NOT VERIFIED |
| Affiliate tracking | `/go/` in PHP with `no-store`, `DONOTCACHEPAGE`, noindex | verified locally. **Must be tested on GoDaddy:** click `/go/…` twice from two browsers and confirm two rows in the report. If GoDaddy's cache serves the 302 from cache, add a cache exclusion for `/go/*` in the GoDaddy dashboard. NOT VERIFIED |
| CRM | FluentCRM hand-off (`FluentCrmApi('contacts')->createOrUpdate`, status "pending" + double opt-in email) | code path NOT VERIFIED (FluentCRM not installed here); the local-table fallback was verified |
| Analytics | GTM snippet + Consent Mode defaults + dataLayer events | events verified locally in the browser; GTM/GA4 container NOT VERIFIED |
| Image optimisation | WordPress responsive sizes + WebP sub-sizes if supported | NOT VERIFIED on GoDaddy |
| Caching | GoDaddy server cache only; no plugin | NOT VERIFIED on GoDaddy. Tested locally with Varnish 7.1 (default VCL): pages HIT, `/go/` never cached (3 requests = 3 clicks). Public pages set no cookies (Polylang's cookie is disabled by Core). Public forms avoid nonces so cached pages keep working |
| Rate limit | per-IP limit on forms uses `REMOTE_ADDR` | NOT VERIFIED: confirm on staging that `REMOTE_ADDR` is the visitor IP behind GoDaddy's proxy/CDN |
| Email | see below | NOT VERIFIED |
| Security | GoDaddy WAF / scanning + Core's capability, nonce, escaping and allow-list measures | Core measures verified locally; GoDaddy layer NOT VERIFIED |
| Cron | click retention purge via WP-Cron (daily) | NOT VERIFIED on GoDaddy |
| PHP | Core and theme require PHP ≥ 8.1 (tested on 8.4) | GoDaddy PHP version NOT VERIFIED; select 8.2+ in the dashboard |

## Email delivery architecture

Nothing assumes SMTP from the web server.

1. **Contact form.** The message is **stored first** (Egypt Roamer → Contact messages), then `wp_mail()` sends a notification.
   - The message screen shows whether the email was sent.
   - Verified locally: storage works. `wp_mail` failed in the sandbox (no mail transport), and the message was still kept and flagged.
2. **Mail transport.** One mailer plugin, configured with a transactional provider's **HTTPS API**. That avoids SMTP ports GoDaddy may block, as reported for GoDaddy environments in the sources below. Authenticate the sending domain with SPF, DKIM and DMARC at the DNS provider.
3. **Newsletter.**
   - With FluentCRM, contacts are created as *pending* and FluentCRM sends the double opt-in email through the same API mailer.
   - Without it, sign-ups are stored **unconfirmed** (Egypt Roamer → Subscribers, CSV export), and the site tells the visitor "You're on the list". It does **not** claim a confirmation email was sent.

Sources consulted, not verified against this account: [GoDaddy — Blocklisted plugins](https://www.godaddy.com/help/blocklisted-plugins-8964) · [GoDaddy — Managed WordPress security](https://www.godaddy.com/help/understand-managed-wordpress-security-40956) · [WP Mail SMTP — WordPress not sending email on GoDaddy](https://wpmailsmtp.com/wordpress-not-sending-email-godaddy/) · [Mailtrap — GoDaddy SMTP](https://mailtrap.io/blog/godaddy-smtp/)
