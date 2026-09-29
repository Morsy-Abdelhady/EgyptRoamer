# PHP upgrade plan (prepared 2026-09-29 — not executed)

## Current state

- **Production:** PHP **8.2.33**. Site Health lists this under "recommended" (older PHP); GoDaddy's dashboard shows "Update PHP".
- **Security support (php.net supported-versions policy):**
  - 8.2 until **31 Dec 2026**;
  - 8.3 until 31 Dec 2027;
  - 8.4 until 31 Dec 2028.
- **CI:** every deploy already lints the theme and Core on **PHP 8.1 and 8.3**.
- **Local test install:** PHP 8.2 only. A runtime test on 8.3/8.4 should use GoDaddy's staging site, not production.

## Target

**PHP 8.3** is the conservative choice:
- already covered by CI lint;
- supported by current WordPress, Polylang, Akismet and Site Kit;
- a year more security support than 8.2.

8.4 is possible later, after 8.3 has run cleanly. Check which versions GoDaddy offers under wp-admin → GoDaddy → Tools → **PHP Version Update** → Manage.

## Steps (each needs the owner's GoDaddy sign-in; no uncontrolled production change)

1. **Backup.** GoDaddy → Tools → Backups → create a manual backup, or confirm today's automatic one.
2. **Staging.** The Deluxe plan includes a one-click staging site: create or refresh it from production.
3. **Staging to PHP 8.3** in the staging site's settings.
4. **Smoke test staging.** Give me the staging URL; no credentials are needed for the public checks.
   - Full page-type matrix (8 languages × 8 page types × widths): status, `lang`/`dir`, overflow, JS errors, axe.
   - Homepage map, destination panel, `/go/` redirect, search, 404.
   - Site Health on staging (owner, or read-only in the owner's Chrome): no critical issues; PHP 8.3 shown.
   - wp-admin screens load: dashboard, Egypt Roamer settings, reports, destinations, offers.
   - WP-CLI on staging over SSH: `wp egypt-roamer editorial --dry-run`.
5. **Production to 8.3** only if step 4 is clean. Do it at a quiet hour.
6. **Verify production** right after: re-run the matrix and Site Health; check `track.js?ver=` is unchanged and `blog_public` = 0.
7. **Rollback:** switch production back to 8.2 in the same GoDaddy screen. Code needs no change; it's lint-clean on 8.1/8.3.

## Not needed

No code changes are expected: Core and the theme lint clean on 8.3 in CI on every deploy.
