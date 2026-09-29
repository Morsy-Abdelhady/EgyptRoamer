# HSTS recommendation (for owner approval — NOT enabled)

HSTS (`Strict-Transport-Security`) tells browsers to use HTTPS only, for as long as `max-age` says. Once a browser has seen it, the choice can't be undone from the server until `max-age` expires. That's why it's staged.

## Pre-checks (production, 2026-09-29)

| Check | Result |
|---|---|
| `http://egyptroamer.com/…` | 301 → `https://egyptroamer.com/…` ✓ |
| `http://www.egyptroamer.com/` | 301 → `https://www.egyptroamer.com/` ✓ |
| `https://www.egyptroamer.com/` | valid certificate; redirects to the apex ✓ |
| Other subdomains (`mail`, `shop`, `blog`, `staging`) | none resolve |
| `http://` references in the HTML of `/`, `/ar/`, a destination, archives | none ✓ |
| Resources loaded over `http://` | 0 ✓ |
| External assets (Unsplash, `img1.wsimg.com`, Google) | all HTTPS ✓ |
| Current header | none sent |
| Other GoDaddy product | a Website Builder "coming soon" site on `egyptroamer.godaddysites.com`: a different domain, so this domain's HSTS doesn't affect it |
| Email | no `mail.` subdomain found. If a GoDaddy or other email product is activated later, its web or autodiscover hostnames must be HTTPS before `includeSubDomains` |

**Conclusion:** the domain is ready for HSTS **without** `includeSubDomains`, and there's no HTTP-only dependency.

## Recommended rollout (each step a separate go)

1. `Strict-Transport-Security: max-age=300` for about a day. Watch for anything that breaks.
2. `max-age=604800` (1 week) for 1–2 weeks.
3. `max-age=31536000` (1 year).
4. Only if every subdomain that will ever exist is HTTPS: add `includeSubDomains`.
5. **Preload** (`preload` + hstspreload.org) is a long-term commitment and is not recommended now.

## Where to set it

- **Preferred:** Egypt Roamer Core, in the same `send_headers` block as the other headers (Core 1.2.4), as a one-line change and a Core release.
- **Alternative:** the host/CDN level, if GoDaddy's panel offers it.
- Don't set it in both places.

## Risk and rollback

- Before step 3, rollback is just waiting out the short `max-age` after removing the header.
- After step 3, browsers keep enforcing HTTPS for up to a year, which is fine as long as HTTPS stays up. The real risk is losing the certificate; GoDaddy manages it.
