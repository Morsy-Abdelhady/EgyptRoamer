# QA scripts (Playwright)

A release is audited site-wide, not per component: run the inventory, crawl, analysis, responsive and keyboard scripts on the local copy, then `site-crawl.mjs` with `GAP=3000` against production (it stops at the first Cloudflare challenge).

Run against a local install (`http://127.0.0.1:8080`, admin/admin) or the static prototype (`python3 -m http.server 5173` in `Egypt Roamer/`).

```
npm i playwright axe-core
node prototype-audit.mjs   # prototype: 8 languages × viewports, errors, overflow, i18n gaps
node wp-matrix.mjs         # WordPress: 12 page types × 390/430/768/1024/1440/1920, overflow, errors, axe
node acceptance.mjs        # business acceptance: provider → offer → CTA → click → report → edit URL/CTA → homepage
node leads.mjs             # newsletter, bot-speed rejection, contact form, analytics events
node languages.mjs dest.json               # every language: nav, footer, CTA, filters, search, newsletter reply, RTL
wp eval-file site-inventory.php > urls.json   # every public URL × language (entries, archives, terms, search, 404)
node site-crawl.mjs && node site-analyze.mjs   # full-site audit: HTTP, SEO/hreflang, header/footer/switcher parity, English leaks, links, axe
node responsive.mjs                          # page types × 8 languages × 320–1600: overflow, clipping, overlap (types.json)
node display-fit.mjs [/path/]                # one-line display words (hero, scene titles) clipped by their section, 8 languages × 320–1600
node keyboard.mjs                            # 8 languages × desktop/phone: skip link, focus rings, language menu, overlays, menu focus trap
node overlays.mjs                            # axe with saved / search / mobile menu / assistant open (en, ar, zh)
node assistant.mjs                           # trip assistant end to end: lazy load, keyboard, languages, links, axe, Escape
node launcher-keyboard.mjs                    # homepage launcher on phones, keyboard only: Tab reach, never focused while hidden, Enter/Escape, focus kept at the hero
node chat.mjs                                 # chat with the team end to end: visitor + logged-in team browser, live replies both ways, AI hand-back/takeover, offline, isolation, tokens, RTL, axe, phone inbox
node chat-adversarial.mjs                     # chat edge cases: reload mid-chat, double start, language switch, offline send + Retry, rapid open/close, 2,000-character Arabic
python launch-sim.py [BASE]                   # indexing launch state: robots.txt, every sitemap URL (200, no noindex, self-canonical, reciprocal hreflang), pages that must stay out
node footer-parity.mjs [/path/ …]           # global footer: same columns, links, order and legal row in all 8 languages, localized (GAP=3000 on production)
python3 index-gate.py '{"tour":"<url>"}' A # Ready to index OFF (A) / ON (B): robots, canonical, sitemap, H1, JSON-LD
EDGE=.. HOSTHDR=.. WP=wp DB=wp FALLBACK_ID=.. ./go-architecture.sh   # /go/ two-step design through a cache: clicks, freshness, security (own test data)
EDGE=.. HOSTHDR=.. WP=wp DB=wp OFFER_ID=.. PROVIDER_ID=.. ./go-cache-matrix.sh   # same checks on an existing TEST offer (restores it); EDGE=origin for no cache
```

`acceptance.mjs` uses a desktop user agent: headless browsers are (correctly) excluded from click logging.
