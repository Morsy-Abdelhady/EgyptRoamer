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

## 4. Local test site (Windows)

A copy of the site on this PC, at `http://127.0.0.1:8080` (login `admin` / `admin`, local only). WordPress 7.1.2 on
SQLite with Polylang, in `C:\Users\morsy\er`. The theme and Core are linked to this repo, so local edits show at once.
The scripts live in `tools/local/`; copy them into `C:\Users\morsy\er`. That folder holds only downloads and the local
database, so it can be rebuilt from these steps.

**1. Tools** (PowerShell, from the winget source):

```
winget install --id PHP.PHP.8.3 --source winget --exact
winget install --id OpenJS.NodeJS.LTS --source winget --exact
winget install --id Google.Chrome --source winget --exact
```

- PHP has no `php.ini`: copy `php.ini-development` to `php.ini` in its folder, set `extension_dir = "ext"`, enable
  `curl fileinfo gd intl mbstring openssl pdo_sqlite sqlite3 zip`, and set `memory_limit = 512M`.
- Add `curl.cainfo` and `openssl.cafile` pointing at `cacert.pem` from https://curl.se/ca/ (check it against
  `cacert.pem.sha256`). While Avast's HTTPS scanning is on, PHP, WP-CLI and Git Bash still fail on SSL (see "Avast"
  below); then download with PowerShell `curl.exe`, as in step 2.

**2. WordPress** (Git Bash; `C:\Users\morsy\er` must not exist or be empty):

```
mkdir -p /c/Users/morsy/er/dl && cd /c/Users/morsy/er
cp /c/Users/morsy/Desktop/EgyptRoamer/tools/local/{proxy.py,serve.php,wp.sh,reset.sh} .
powershell.exe -NoProfile -Command "curl.exe -sSL -o wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; curl.exe -sSL -o dl/wp.zip https://wordpress.org/wordpress-7.1.2.zip; curl.exe -sSL -o dl/sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.zip; curl.exe -sSL -o dl/polylang.zip https://downloads.wordpress.org/plugin/polylang.zip"
unzip -q dl/wp.zip && unzip -q dl/sqlite.zip -d wordpress/wp-content/plugins && unzip -q dl/polylang.zip -d wordpress/wp-content/plugins
```

- Check the downloads: `wp-cli.phar.sha512` from the same WP-CLI address, and `wordpress-7.1.2.zip.sha1` from wordpress.org.
- SQLite drop-in: copy `wordpress/wp-content/plugins/sqlite-database-integration/db.copy` to `wordpress/wp-content/db.php`.
  Replace `{SQLITE_IMPLEMENTATION_FOLDER_PATH}` with `' . WP_CONTENT_DIR . '/plugins/sqlite-database-integration` and
  `{SQLITE_PLUGIN}` with `sqlite-database-integration/load.php`.
- `wordpress/wp-config.php`:
  - `DB_DIR` = `__DIR__ . '/wp-content/database/'`, `DB_FILE` = `.ht.sqlite`.
  - `WP_HOME` and `WP_SITEURL` = `http://127.0.0.1:8080`, `WP_ENVIRONMENT_TYPE` = `local`, `DISABLE_WP_CRON` = true.
  - `WP_DEBUG` and `WP_DEBUG_LOG` = true (errors go to `wp-content/debug.log`).
  - New random salts. Never copy production's.
- Link the repo's theme and Core (cmd):

  ```
  mklink /J C:\Users\morsy\er\wordpress\wp-content\themes\egypt-roamer C:\Users\morsy\Desktop\EgyptRoamer\wordpress\wp-content\themes\egypt-roamer
  mklink /J C:\Users\morsy\er\wordpress\wp-content\plugins\egypt-roamer-core C:\Users\morsy\Desktop\EgyptRoamer\wordpress\wp-content\plugins\egypt-roamer-core
  ```

**3. Build and run:**

```
./reset.sh ml                      # build all 8 languages like production; backs up any existing database first
./wp.sh option update timezone_string UTC
python proxy.py                    # http://127.0.0.1:8080, 6 PHP workers on 8081-8086; Ctrl+C stops all
```

- **Timezone:** use a named timezone. With the default "+00:00", destination pages crash: PHP intl rejects it in
  `er_local_date()`.
- **Guides:** `reset.sh` keeps guides as the seed made them (drafts). To match production, publish locally only the
  guides that are live, then run the guide sync: `./wp.sh eval 'er_guide_translations_sync( false );'`.
- **Offers:** local trips have no offers (they live in the production database). Test booking parts on production.
- **QA scripts:** `npm i --no-save playwright axe-core` in `tools/qa`. Against the local site, leave `BASE` unset.

**Avast: the cause of dropped local requests and SSL errors (confirmed 26 Oct 2026).** Avast Antivirus on this PC
inspects traffic in two ways:
- **Web Shield** inspects HTTP, local connections included: about 1 request in 20 was cut off at 64 KB, and a static
  file took about 1.4 s instead of 0.01 s.
- **HTTPS scanning** replaces sites' certificates with Avast's own. PHP (curl), WP-CLI and Git Bash `curl` don't trust
  it, so they fail with "unable to get local issuer certificate". PowerShell `curl.exe` and browsers use Windows'
  certificate store and work.

With Avast switched off, all three problems went away: 0 drops in 100 requests, all 273 local sitemap pages returned 200
twice with 8 parallel requests, and PHP, WP-CLI and Git Bash reached wordpress.org over HTTPS.

**Fix** (owner: antivirus settings are the owner's; don't leave Avast switched off):
- **Dropped local requests:** in Avast, add an exception for the local site (`127.0.0.1` / `http://127.0.0.1:8080/*`),
  or turn off Web Shield while testing locally.
- **SSL errors:** in Avast's Web Shield settings, turn off "Enable HTTPS scanning", or add exceptions for
  `wordpress.org`, `downloads.wordpress.org`, `api.wordpress.org` and `raw.githubusercontent.com`.
- The exceptions themselves have not been tested yet: after setting them, rerun 40 requests to a static file and
  `./wp.sh core check-update`.
- `proxy.py` also retries a dropped GET on another worker, which hides most drops if Web Shield stays on.
