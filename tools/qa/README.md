# QA scripts (Playwright)

Run against a local install (`http://127.0.0.1:8080`, admin/admin) or the static prototype (`python3 -m http.server 5173` in `Egypt Roamer/`).

```
npm i playwright axe-core
node prototype-audit.mjs   # prototype: 8 languages × viewports, errors, overflow, i18n gaps
node wp-matrix.mjs         # WordPress: 12 page types × 390/430/768/1024/1440/1920, overflow, errors, axe
node acceptance.mjs        # business acceptance: provider → offer → CTA → click → report → edit URL/CTA → homepage
node leads.mjs             # newsletter, bot-speed rejection, contact form, analytics events
```

`acceptance.mjs` uses a desktop user agent: headless browsers are (correctly) excluded from click logging.
