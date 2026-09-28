# Production deploy (GitHub Actions → GoDaddy)

Workflow: `.github/workflows/deploy-production.yml`. It uses GoDaddy's `godaddy-wordpress/gd-wordpress-deployer@v1`.

## When it runs
- **Only automatically:** on a push to `main` that changes files under `wordpress/wp-content/themes/egypt-roamer/` or `wordpress/wp-content/plugins/egypt-roamer-core/`. There is no manual trigger. Changes to the workflow, docs or tools alone do not deploy.
- Only one production deploy runs at a time. A newer one waits.
- Normal flow: change the theme or Core → run the tests locally → commit → `git push origin main` → Actions runs the tests, then deploys.

## What it deploys
| Repository | Server (under `html/`) |
|---|---|
| `wordpress/wp-content/plugins/egypt-roamer-core/` | `wp-content/plugins/egypt-roamer-core/` |
| `wordpress/wp-content/themes/egypt-roamer/` without `src/` | `wp-content/themes/egypt-roamer/` |

Both folders are deployed together first, then each one is cleaned up separately (see below). Files deleted from git are deleted on the server **only inside those two folders**.

## What it never touches
- `wp-config.php`, `wp-admin/`, `wp-includes/` and WordPress core files.
- `wp-content/uploads/`, other plugins and themes.
- The database. It runs no seed, migration, import, translation or image job. Those stay manual, through the owner's terminal.

## Order and failure
1. **Tests** on PHP 8.1 and 8.3:
   - PHP syntax of the theme and Core;
   - version header matches the constant, for both the theme and Core;
   - `seed.json` is valid;
   - `assets/js/home.js` and `site.js` match `src/js`.

   If any test fails, nothing is deployed.
2. **Stage.** The two folders are copied into `_deploy/wp-content/`, with `src/` excluded. The run stops if anything else is staged, or if WordPress core, config or uploads files appear.
3. **Deploy.** `gd-wordpress-deployer` has no atomic or multi-folder mode, so it runs three times:
   1. Theme and Core together into `wp-content/`, cleanup off, health check on. Both reach their new version in one run, and a failed check rolls that run back. Cleanup must stay off here, because on `wp-content/` it would delete other plugins, themes and uploads.
   2. The Core folder only, cleanup on. This removes files deleted from git. Health check on.
   3. The theme folder only, the same. This is the final health check on the complete state.

   If a step fails, the steps after it don't run.
4. **Live page check.** This only reports. It compares the Core version in git with `track.js?ver=` in the public HTML. A stale cache gives a warning, not a failure. To refresh, use **Flush cache** in the WP admin bar.

Why the combined first step: GoDaddy's ignore list contains `/index.php`, which is anchored at the upload root. With the theme folder as the root, the theme's own `index.php` would never be uploaded.

## Access
- Repository secret `PRIVATE_KEY`: the private half of the key registered in GoDaddy CI/CD.
- Deploy user `git_deployer_71e07e2d98_1284039` on `1284039.eu11.myftpupload.com`.
- GitHub environment `production`. Keep it without required reviewers, or every deploy waits for a manual approval.

## Rollback
Revert the commit on `main` and push. The previous files are deployed again.
