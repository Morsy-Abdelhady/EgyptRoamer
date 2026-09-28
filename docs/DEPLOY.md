# Auto-deploy (GitHub Actions → production)

Every push to `claude/dreamy-bardeen-6qqk2h` that changes the theme or Core runs `.github/workflows/deploy.yml`:

1. PHP lint of the theme and Core (a syntax error stops the deploy).
2. `rsync` of the theme (without `src/`) and of Core into `~/html/wp-content/…` over SSH.
3. `wp cache flush`.
4. A check that the live page shows the new `track.js?ver=`.

It touches only those two folders. It never touches the database, content, settings, other plugins or uploads. It can also be started by hand: GitHub → Actions → Deploy → Run workflow.

## One-time setup (owner)

Nothing below is ever pasted into a chat. The private key goes only into GitHub.

**1. Create a deploy key** on your PC (PowerShell), with no passphrase:

```
ssh-keygen -t ed25519 -f $HOME\.ssh\egyptroamer_deploy -N '""' -C github-deploy
```

**2. Allow it on the server.** Replace `USER@HOST` with the SSH login you already use:

```
type $HOME\.ssh\egyptroamer_deploy.pub | ssh USER@HOST "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
```

If GoDaddy manages SSH keys in its panel instead, add the contents of `egyptroamer_deploy.pub` there.

**3. Record the server fingerprint:**

```
ssh-keyscan HOST
```

**4. Add the secrets.** Go to GitHub → the repository → Settings → Secrets and variables → Actions → New repository secret.

| Secret | Value |
|---|---|
| `SSH_HOST` | the SSH host |
| `SSH_USER` | the SSH user |
| `SSH_PORT` | only if it is not 22 |
| `SSH_PRIVATE_KEY` | the whole content of `egyptroamer_deploy` (the file **without** `.pub`) |
| `SSH_KNOWN_HOSTS` | the output of step 3 |

**5. Test it.** Go to Actions → Deploy → Run workflow and check that the run is green.

## Rollback

Revert the commit and push. The previous files are deployed again:

```
git revert <sha> && git push
```

## Notes

- GoDaddy's page cache may keep serving old HTML for a while. If the last step warns, click **Flush cache** in the WP admin bar.
- Database changes (settings, permalinks, seed runs) are not part of the deploy. They still go through the owner's terminal.
- Revoke access at any time by removing the key line from `~/.ssh/authorized_keys` and deleting the secrets.
