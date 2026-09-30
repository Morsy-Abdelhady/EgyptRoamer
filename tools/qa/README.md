# QA scripts (Playwright)

Run against a local install (`http://127.0.0.1:8080`, admin/admin) or the static prototype (`python3 -m http.server 5173` in `Egypt Roamer/`).

```
npm i playwright axe-core
node prototype-audit.mjs   # prototype: 8 languages × viewports, errors, overflow, i18n gaps
node wp-matrix.mjs         # WordPress: 12 page types × 390/430/768/1024/1440/1920, overflow, errors, axe
node acceptance.mjs        # business acceptance: provider → offer → CTA → click → report → edit URL/CTA → homepage
node leads.mjs             # newsletter, bot-speed rejection, contact form, analytics events
node languages.mjs dest.json               # every language: nav, footer, CTA, filters, search, newsletter reply, RTL
node footer-parity.mjs [/path/ …]           # global footer: same columns, links, order and legal row in all 8 languages, localized (GAP=3000 on production)
python3 index-gate.py '{"tour":"<url>"}' A # Ready to index OFF (A) / ON (B): robots, canonical, sitemap, H1, JSON-LD
EDGE=.. HOSTHDR=.. WP=wp DB=wp FALLBACK_ID=.. ./go-architecture.sh   # /go/ two-step design through a cache: clicks, freshness, security (own test data)
EDGE=.. HOSTHDR=.. WP=wp DB=wp OFFER_ID=.. PROVIDER_ID=.. ./go-cache-matrix.sh   # same checks on an existing TEST offer (restores it); EDGE=origin for no cache
```

`acceptance.mjs` uses a desktop user agent: headless browsers are (correctly) excluded from click logging.
