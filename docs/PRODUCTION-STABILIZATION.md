# Production stabilization runbook: egyptroamer.com

The session that wrote this had **no WP-admin, SSH/WP-CLI or GoDaddy access**, so none of these steps have been performed on production. Every step below is for the owner, in WP admin (or WP-CLI over SSH). Nothing here enables indexing: keep **Settings → Reading → "Discourage search engines"** ticked throughout.

## 0. Upload the security fix first

Upload `egypt-roamer-core-<commit>.zip` (from this branch; it replaces the current Core; same steps as [STAGING-DEPLOY.md](STAGING-DEPLOY.md)). Then click **Flush cache**.

**Why.** Production exposes the login `morsy` in three places:

- `/wp-json/wp/v2/users` → `[(1,'morsy','morsy')]`;
- `/?author=1` → 301 to `/author/morsy/`;
- oEmbed `author_name: morsy`.

All three come back for **any** account that publishes content, including a new admin who runs the seed. So renaming or replacing the account alone is not enough.

**What this Core version changes** (verified locally):

- For anonymous visitors, `/wp/v2/users` and `/wp/v2/users/{id}` return 404, and they are absent from the REST index. Logged-in users, and so the block editor, keep them.
- `/?author=N` and `/author/{login}/` return 404, and author links point to `/`.
- oEmbed no longer includes `author_name` or `author_url`.
- The rest of the REST API is unchanged. Acceptance passes 14/14 and the lead tests pass.

**Verify on production afterwards:**

1. `https://egyptroamer.com/wp-json/wp/v2/users` → 404.
2. `/?author=1` → 404.

## 1. Site identity

**Settings → General:**

- Site Title = `Egypt Roamer`
- Tagline = `More than a destination`

Save, then **Flush cache**.

**Verify:** the page source of `/` contains `<title>Egypt Roamer – More than a destination</title>` and `og:site_name" content="Egypt Roamer"`. The string `myftpupload` must not appear anywhere.

## 2. Content structure: what `wp egypt-roamer seed` does (reviewed line by line)

Run it over SSH: `wp egypt-roamer seed`. Do **not** add `--with-images` (it would import Unsplash stand-ins) or `--translations` (see §6).

| Creates | Status | Public? | Contains |
|---|---|---|---|
| 7 destinations: Cairo, Luxor, Aswan, Alexandria, Siwa, Hurghada, Sharm El Sheikh | **published** | yes, **noindex** (not "Ready to index"), not in the sitemap | name, tagline, 16–23-word description, highlights, best time, how to get there, region: the approved prototype copy |
| 8 experiences | **published** | yes, **noindex** | title, location, duration, tag; **no body text** |
| 7 travel styles, offer categories | terms | on the homepage | labels and short descriptions from the prototype |
| 7 guides | **draft** | no | titles and excerpts only |
| 15 offers | **draft, paused, no link** | no | titles/locations only. One carries "5★ river ship": verify it before any offer is ever published |
| 8 trust/legal pages (Our Story, How We Choose, Partner With Us, Contact, FAQ, Affiliate Disclosure, Terms, Cookie Policy) + Privacy Policy | **draft** | no | editorial notes to replace, **no legal text** |
| Home and Journal pages | **published** | yes | empty; they set **Settings → Reading → static front page = Home** and the posts page = Journal |
| 5 menus | — | — | archive links, plus page links that stay hidden until those pages are published |
| WordPress "Sample Page" / "Hello world!" | **trashed** if untouched | — | — |

**The seed contains no prices, ratings, reviews, availability claims or affiliate URLs** (all checked). It fixes items 2 (homepage), 3 (default content), 7 (navigation) and 12 (legal page structures, as drafts) in one run.

**If it is run by hand instead:**

- Settings → Reading → "A static page": Homepage = a new empty page "Home", Posts page = "Journal".
- Trash Sample Page and Hello world!.
- Create the menus in Appearance → Menus from [ARCHITECTURE.md](ARCHITECTURE.md).

## 3. Real content that exists in the project

This is **all** of it; nothing else should be invented.

| Type | Real content available | Result on the site |
|---|---|---|
| Destinations | 7, with short prototype copy | published, noindex until editors expand them and tick "Ready to index" |
| Experiences | 8 titles with metadata, no body | published, noindex |
| Guides | 7 titles and excerpts, no articles written | drafts |
| Tours | **none** | archive empty and noindex |
| Activities | **none** | archive empty and noindex |
| Articles (Journal) | **none** | Journal noindex; its menu link hides itself |
| Homepage | hero, moods, destinations, experiences, interludes and film captions from the prototype | shows once seeded. Partner and finder sections stay hidden until real offers exist, so there are no empty cards |
| Photography | **none owned.** The prototype used hot-linked Unsplash stand-ins | real, owned or licensed images must be supplied and uploaded (Media, featured images, Appearance → Homepage) |
| Claims to verify before launch | "7 UNESCO sites", "1,200+ fish species", "4 nights · 210 km · 5 temples", travel times, "Editor's pick"/"Iconic" | owner verifies or removes them |

## 4. Account security (in addition to §0)

1. Users → Add New: a **non-obvious** login (not `admin`, not the domain or a name), role Administrator, a strong unique password, your email.
2. Log in as that user and enable 2FA:
   - Use GoDaddy's WordPress security features if your plan has them.
   - Otherwise install the official **Two Factor** plugin (by WordPress contributors), then Users → Profile → enable TOTP.
   - Check GoDaddy's blocklist first.
3. Users → `morsy` → Delete → "Attribute all content to" the new user. Only do this after confirming the new account can log in with 2FA.

## 5. Legal pages

The seed creates them as **drafts containing only editorial notes**. Keep them drafts until the owner or an adviser supplies the final text. Nothing in this project is legal advice or approved copy.

## 6. Multilingual

Install Polylang only when at least one translation has been **reviewed by a native speaker**. Then follow SETUP task 15:

- add the languages;
- directory URLs;
- "front page contains the language code";
- browser detection **off**;
- run `wp egypt-roamer seed --translations`.

That creates translations as **drafts** (only each language's Home and Journal pages are published) and per-language menus. Publish a translation only after review.

## 7. Email

**NOT VERIFIED** until one API mailer is configured with SPF/DKIM/DMARC and a contact message is received (SETUP task 13).

## 8. After the steps: what to verify

1. `/`: `<title>Egypt Roamer – …`; the Egypt Roamer homepage with the destinations and experiences sections; no "Hello world!".
2. `/sample-page/` and `/hello-world/` → 404.
3. The menus link only to 200 pages.
4. Every page is still `noindex`, and `/wp-sitemap.xml` returns 404.
5. `/wp-json/wp/v2/users` → 404.
6. Then ask for the production re-check. The public side can be verified from the session; Click reports and settings need you.
