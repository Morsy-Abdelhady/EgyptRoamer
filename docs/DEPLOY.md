# Production deploy (GitHub Actions → GoDaddy, rsync over SSH)

Workflow: `.github/workflows/deploy-production.yml`. It copies the theme and Core with rsync over SSH, using the normal production SSH account. GoDaddy's CI/CD deployer action (`gd-wordpress-deployer`) is no longer used: its `git_deployer_*` users stopped accepting logins after working for a while (Runs #6–#8).

## When it runs
- **Only automatically:** on a push to `main` that changes files under `wordpress/wp-content/themes/egypt-roamer/` or `wordpress/wp-content/plugins/egypt-roamer-core/`. There is no manual trigger. Changes to the workflow, docs or tools alone do not deploy.
- Only one production deploy runs at a time. A newer one waits.
- Normal flow: change the theme or Core → run the tests locally → commit → `git push origin main` → Actions runs the tests, then deploys.

## What it deploys
| Repository | Server |
|---|---|
| `wordpress/wp-content/plugins/egypt-roamer-core/` | `~/html/wp-content/plugins/egypt-roamer-core/` |
| `wordpress/wp-content/themes/egypt-roamer/` without `src/` | `~/html/wp-content/themes/egypt-roamer/` |

Files deleted from git are deleted on the server **only inside those two folders**, and at most 30 per folder per deploy.

## What it never touches
- `wp-config.php`, `.htaccess`, `wp-admin/`, `wp-includes/` and WordPress core files.
- `wp-content/uploads/`, other plugins and themes, and anything else under `~/html`.
- The database. It runs no seed, editorial import, migration, translation, image job or cache flush. Those stay separate tasks, run only when explicitly requested (for the editorial import: `--dry-run` first).

## Order and failure
1. **Tests** on PHP 8.1 and 8.3:
   - PHP syntax of the theme and Core;
   - version header matches the constant, for both the theme and Core;
   - `seed.json` is valid, and the editorial data matches `content/editorial`;
   - `assets/js/home.js` and `site.js` match `src/js`.

   If any test fails, nothing is deployed.
2. **Stage.** The two folders are copied into `_deploy/wp-content/`, with `src/` excluded. The run stops if anything else is staged, or if WordPress core, config, uploads, database dumps or symlinks appear.
3. **Host key.** GoDaddy's ed25519 host key must match the pinned fingerprint `SHA256:oxYa4nr7BL5hXCIG5j/OOk54R6yNokpACBoa3tn+Kp4`. Strict host key checking stays on.
4. **Preflight (read-only).** Logs in (up to 3 tries; the first login can take ~40 s) and checks that `~/html` is the WordPress root, that `wp-content/`, `themes/` and `plugins/` exist, that both targets are real, writable directories inside `~/html/wp-content` (not symlinks elsewhere) and that WordPress is installed. Nothing is transferred if any check fails.
5. **Transfer.** rsync, Core first, then the theme. Each folder gets a dry run that lists every change, then the real transfer with `--delete-after --max-delete=30 --delay-updates`. The destination must be exactly one of the two target folders, so a deletion can never reach `~/html` or `~/html/wp-content`. Names such as `wp-config.php`, `.htaccess`, `uploads/`, `*.sql` and `src/` are neither sent nor deleted.
6. **Health check on the server (read-only).** Core and theme versions on disk and through WP-CLI, WordPress loads, `egypt-roamer` is the active theme, Core is active, and `blog_public` is `1` (the site has been public since the 2026-10-02 launch; a `0` would mean indexing was switched off by mistake). Any mismatch fails the run.
7. **Health check on public pages.** HTTP 200 without a PHP error for `/`, `/ar/`, `/destinations/`, Cairo (English and Arabic), `/experiences/` and `/journal/`. The Core version in `track.js?ver=` and the theme version in the public `style.css` are compared too, but only as warnings, because GoDaddy's page cache can serve older HTML for a while.

If a step fails, the steps after it don't run. **There is no automatic rollback.** A failure during or after the transfer can leave the new files in place; the run log says which step failed.

## Cache
Versions on disk (step 6) are authoritative. If they are right but the public page still shows the old `track.js?ver=`, the HTML is cached: use **Flush cache** in the WP admin bar (logged in as the owner), then reload.

## Access
- Repository secrets `PROD_SSH_USER` and `PROD_SSH_PASSWORD`: the normal production SSH/SFTP account. They are passed to `sshpass -e` through the environment, never on a command line, and are masked in the logs.
- Host `1284039.eu11.ssh.myftpupload.com`, port 22. The WordPress root is `~/html`.
- GitHub environment `production`. Keep it without required reviewers, or every deploy waits for a manual approval.
- `PRIVATE_KEY` (the old GoDaddy CI/CD key) is no longer used by this workflow.

## Rollback
Revert the commit on `main` and push. The previous files are deployed again the same way.
