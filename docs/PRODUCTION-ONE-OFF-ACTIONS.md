# Production one-off actions (prepared 2026-09-29)

Each action is separate. None is part of a code deploy.

**Rules for every action:**
- Run it only after a go for that action.
- Run each step in the order given.
- For SSH steps, work from the owner's terminal in `~/html` (running `wp` from `~` fails).
- Never paste passwords or keys into any file or chat.

| # | Action | Approved | Status |
|---|---|---|---|
| A1 | Move the stale theme `src/` out of the web root | yes (2026-09-29) | **waiting for the owner's SSH session** (no SSH access from here) |
| A2 | Arabic Giza title, post 202 | yes, as a separate one-off | **prepared; do not run without a production-action go** |
| A3 | Turn off GoDaddy Real User Metrics (tracking scripts and cookies) | yes (default OFF) | **waiting for the owner's GoDaddy sign-in** (the panel asked for re-authentication) |
| A4 | Enrol 2-Step Verification for the administrator | yes | **owner action** (needs the owner's phone) |
| A5 | XML-RPC toggle → off | **recommendation, not yet approved** | — |
| A6 | Translation imports | only after approvals and the Core 1.2.5 deploy | not started |

---

## A1. Stale public `src/` folder

**Why:**
- `/wp-content/themes/egypt-roamer/src/js/*.js` is publicly readable (HTTP 200).
- No page loads it: the theme uses `assets/` only (checked on `/`, `/ar/`, a destination, archives and search), and no theme PHP references `src/`.
- The deploy excludes `src/`, so rsync never removes it.

```bash
cd ~/html
ls -la wp-content/themes/egypt-roamer/src
mkdir -p ~/er-backups
mv wp-content/themes/egypt-roamer/src ~/er-backups/egypt-roamer-src-2026-09-29
ls wp-content/themes/egypt-roamer
```

- **Check:** flush the cache (A7), then `https://egyptroamer.com/wp-content/themes/egypt-roamer/src/js/main.js` should return 404, and `/`, `/ar/` and `/destinations/cairo/` should return 200.
- **Rollback:** `mv ~/er-backups/egypt-roamer-src-2026-09-29 ~/html/wp-content/themes/egypt-roamer/src`.
- **Afterwards:** delete `~/er-backups/egypt-roamer-src-2026-09-29` after a week, and only with a separate go.
- The deploy workflow is not changed.

## A2. Arabic Giza title (database: one field of one post)

The approved correction is `…وأبو الهول` → `…وأبي الهول`. The source (`seed.json`) is already corrected on `main`. The live post keeps the old title until this runs, because neither the seed nor the importer ever writes titles.

```bash
cd ~/html
wp post get 202 --field=post_title              # expect: جولة خاصة إلى أهرامات الجيزة وأبو الهول
wp eval 'echo pll_get_post_language(202), "\n";' # expect: ar
wp post get 202 --field=post_name               # note the slug; it must not change
wp post update 202 --post_title='جولة خاصة إلى أهرامات الجيزة وأبي الهول'
wp post get 202 --field=post_title              # verify
wp post get 202 --field=post_name               # same slug as before
```

- **Check:** flush the cache (A7); the page H1 and `<title>` on `/ar/experiences/…` show «وأبي الهول».
- **Rollback:** `wp post update 202 --post_title='جولة خاصة إلى أهرامات الجيزة وأبو الهول'`.
- **Alternative:** in wp-admin → Experiences → the Arabic Giza post, edit the title only and don't touch the slug.

## A3. GoDaddy Real User Metrics off (the "Experience Improvement Program")

**What it is:** GoDaddy's System Plugin prints these into every page footer:
- a `_trfq` tracker configuration;
- a click listener;
- `img1.wsimg.com/signals/js/clients/scc-c2/scc-c2.min.js`;
- `img1.wsimg.com/traffic-assets/js/tccl-tti.min.js`.

On production they set the cookies `_tccl_visitor`, `_tccl_visit` and `_scc_session` **without consent**. They also feed the "Site Views" counter on the GoDaddy dashboard.

**Control:** GoDaddy's documented switch ([Disable Real User Metrics (RUM)](https://www.godaddy.com/help/disable-real-user-metrics-rum-40648)):
1. GoDaddy → My Products → Managed Hosting for WordPress → **Manage All**.
2. On egyptroamer.com, click **Settings**.
3. Under **Production Site**, next to **Experience Improvement Program**, click **Change**.
4. Choose "No, I don't want to participate in the program" and click **Confirm**.

It can take up to 24 hours to take effect.

- **Rollback:** the same setting → participate.
- **Check afterwards:** no `wsimg.com` in the page source; no `_tccl_*` or `_scc_*` cookies; request count one or two lower. I'll re-run the performance and privacy checks when told it's done.
- The GoDaddy "Site Views" counter will stop counting.

## A4. 2-Step Verification (administrator)

- **Where:** wp-admin → **GoDaddy** → **Tools** → **2-Step Verification** → **Manage**. It's GoDaddy's module in the System Plugin, and **currently off** (`wpsec_two_fa_status: false`).
- **Methods available:** authenticator app, email code, YubiKey.
- **Steps (owner, with the phone at hand):**
  1. Open the page, choose **Authenticator app**, scan the QR code, and enter the 6-digit code.
  2. Store the backup or recovery method GoDaddy offers somewhere safe (not in this repo).
  3. If offered, enforce 2FA for the **Administrator** role.
  4. Log out and back in to confirm the second step is asked.
- **Why this path:** "Login with GoDaddy" is ON (admin can also sign in through the GoDaddy account), but the ordinary password login at `wp-login.php` is still available. The GoDaddy account itself should also have 2-step verification (GoDaddy account → Login & PIN).
- **I don't do this step:** enrolment exposes an authentication secret (the QR code), and switching it on before enrolment could lock the account.

## A5. XML-RPC (recommendation)

- GoDaddy → Tools shows **XML-RPC: on** ("Allow connection from external locations").
- The public endpoint returns 403 (blocked upstream), and the site uses nothing that needs XML-RPC: no Jetpack, no mobile app publishing.
- **Recommendation:** switch the toggle off. It's reversible with the same toggle. Not changed without a go.

## A6. Translation imports

- These happen only after the translations are approved and Core 1.2.5 is deployed. See `docs/FINAL-RELEASE-BLOCKERS.md` §D3.
- Always run the dry run first.

## A7. Cache flush (after any change above)

wp-admin → GoDaddy → Tools → **Flush Cache**, or "Flush cache" in the admin bar.
