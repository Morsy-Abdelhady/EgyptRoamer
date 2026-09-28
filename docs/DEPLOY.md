# Production deploy (GitHub Actions → GoDaddy)

Workflow: `.github/workflows/deploy-production.yml`. It uses GoDaddy's `godaddy-wordpress/gd-wordpress-deployer@v1`.

## When it runs
- **Automatically** on a push to `main` that changes the theme, Core or the workflow itself.
- **By hand:** Actions → Deploy production → Run workflow. This only deploys when run on `main`.
- Only one production deploy runs at a time. A newer one waits.

`main` does not contain the WordPress build yet. The first deploy happens when the working branch is merged into `main`.

## What it deploys
| Repository | Server (under `html/`) |
|---|---|
| `wordpress/wp-content/plugins/egypt-roamer-core/` | `wp-content/plugins/egypt-roamer-core/` |
| `wordpress/wp-content/themes/egypt-roamer/` without `src/` | `wp-content/themes/egypt-roamer/` |

Core is deployed first, then the theme. Files deleted from git are deleted on the server **only inside those two folders**.

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
2. **Stage.** Only the two folders are copied, and `src/` is excluded. The run stops if WordPress core, config or uploads files appear.
3. **Deploy Core, then the theme.** GoDaddy's health check runs after each step and rolls that step back if WordPress is unhealthy.
   - If Core fails, the theme is not deployed.
   - If the theme fails, the theme rolls back and the new Core stays live. Core 1.2.0 works with the previous theme.
4. **Live version check.** This step only warns. If the page still shows the old `track.js?ver=`, flush GoDaddy's cache (WP admin bar → Flush cache).

## Access
- Repository secret `PRIVATE_KEY`: the private half of the key registered in GoDaddy CI/CD.
- Deploy user `git_deployer_71e07e2d98_1284039` on `1284039.eu11.myftpupload.com`.
- GitHub environment `production`. Add required reviewers there to approve each deploy by hand.

## Rollback
Revert the commit on `main` and push. The previous files are deployed again.
