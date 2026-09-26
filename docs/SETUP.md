# Deployment and manual setup

## 1. Install (staging first)

1. Create a **staging** site in GoDaddy Managed WordPress and set PHP to 8.2 or newer.
2. Upload `wordpress/wp-content/plugins/egypt-roamer-core/` and `wordpress/wp-content/themes/egypt-roamer/` (zip them, or use SFTP). **Do not upload** `Egypt Roamer/`, `tools/`, `docs/` or `src/` folders to production. The theme's `src/js` holds only source files; the built bundles are in `assets/js`.
3. Activate **Egypt Roamer Core**, then the **Egypt Roamer** theme.
4. Import the prototype content. This needs WP-CLI (GoDaddy offers SSH with WP-CLI on Managed WordPress; availability on your plan is NOT VERIFIED):
   ```
   wp egypt-roamer seed --with-images
   ```
   The command:
   - creates the destinations, experiences, travel styles, offer categories, draft guides, draft offers (no links), draft legal/trust pages, a static front page, a Journal page and the menus;
   - sets permalinks to `/journal/%postname%/` for articles.

   Without SSH, create the content by hand; everything is editable in the admin.
5. **Settings → Permalinks → Save**, which flushes the rewrite rules for `/destinations/`, `/go/` and the rest.
6. Check the fixed routes: `/go/anything/` returns 404 with `X-Robots-Tag: noindex`, and `/robots.txt` contains `Disallow: /go/`.

## 2. Manual setup tasks (owner)

| # | Task | Where |
|---|---|---|
| 1 | Write and publish: Our Story, How We Choose, Contact intro, FAQ, **Affiliate Disclosure**, Privacy Policy, Terms, Cookie Policy (the seeded drafts contain editorial notes — delete the notes) | Pages |
| 2 | Choose the disclosure page, the privacy page and the contact email; review the short disclosure text | Egypt Roamer → Settings |
| 3 | Create a **provider** for each real affiliate account. Set its **allowed redirect domains** (redirects fail without them) | Egypt Roamer → Affiliate Providers |
| 4 | Create **offers** with real tracked links: attach them to the destination, tour or experience, and set category, travel styles, CTA and sub-ID parameters. Enter a price only with the date you checked it | Egypt Roamer → Affiliate Offers |
| 5 | Expand each destination and experience into real editorial content (what it is, who it suits, practical tips, alternatives, FAQs), then tick **Ready to index** | each item |
| 6 | Write the guides; publish only complete ones | Guides |
| 7 | Replace stand-in photography with owned or licensed images (descriptive filenames, alt text) | Media / featured images / Appearance → Homepage |
| 8 | Homepage: featured items, scene links, "Roamer pick" offers, finder offers (with search URL templates), planner link, and budget bands only if you stand behind them | Appearance → Homepage |
| 9 | Install **Rank Math** (the only SEO plugin): run the setup wizard, enable the sitemap, breadcrumbs schema and Redirections. (Core already redirects `/privacy` once the privacy page is published.) Set the default OG image to the theme's `assets/img/brand/og-image.jpg`. Leave internal search noindexed | Rank Math |
| 10 | Verify the domain in **Search Console** (DNS record or Rank Math) and submit `/sitemap_index.xml` | Search Console |
| 11 | Create a **GTM** container. Configure GA4 in it, with triggers on the dataLayer events `affiliate_click`, `booking_click`, `search`, `filter_use`, `newsletter_signup`, `contact_submit` and `guide_download`. Enter the container ID | Egypt Roamer → Settings |
| 12 | Install a Google-certified **consent tool**. If it sets Consent Mode defaults itself, set Core's Consent Mode default to "off" | Plugins + Settings |
| 13 | Install **one** mailer plugin with an **API** provider; set SPF/DKIM/DMARC; send a test contact message and confirm "Email notification sent" on it | Plugins, DNS |
| 14 | Optional: FluentCRM. Create a list and put its ID in Settings. Enable double opt-in and a welcome sequence. Use tags from `interests` for destination segments | FluentCRM |
| 15 | Languages (when a translation is reviewed): install Polylang and add languages. In **Languages → Settings → URL modifications**, choose "the language is set from the directory name" and enable **"The front page URL contains the language code"**. Then run `wp egypt-roamer seed --translations` to import the prototype's translations as drafts, and have them **reviewed by native speakers** (also review `tools/i18n/new-strings.json`) before publishing. The same command creates one menu per language (e.g. "Primary (fr)") and assigns it in Polylang, because Polylang ignores the theme's menu locations. Items with no translation are left out, so add the translated pages to those menus in **Appearance → Menus** once they are published | Languages |
| 16 | Import GA4 "Pages and screens" CSVs monthly to see traffic vs affiliate clicks | Egypt Roamer → Click reports |
| 17 | GoDaddy cache test for `/go/` (see HOSTING-AND-PLUGINS.md) | staging |
| 18 | Keep Polylang's "Detect browser language" **off** (the language cookie is disabled so pages stay cacheable) | Languages → Settings |
| 19 | Write an intro for each archive (it becomes the archive's meta description) | Egypt Roamer → Settings |
| 20 | Delete the inactive default plugins (Akismet, Hello Dolly) | Plugins |
| 21 | Keep **Settings → Reading → "Discourage search engines from indexing this site"** ticked on staging and on production until at least one page passes the content gate ([LAUNCH-GATE.md](LAUNCH-GATE.md#content-gate)). The homepage is always indexable otherwise. Verified locally: the whole site then sends `noindex, nofollow` and the sitemap returns 404 | Settings → Reading |

## 3. Developer workflow

```
python3 tools/build.py check      # recreated bundler reproduces the prototype app.js
python3 tools/build.py theme      # src/js → assets/js/home.js, site.js, locales/*.js
node tools/export-seed.mjs        # prototype data → plugin data/seed.json (drops fake data)
node tools/build-translations.mjs # locale files + new strings → languages/*.l10n.php
```
