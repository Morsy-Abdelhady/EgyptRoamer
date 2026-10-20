# What only the owner can do (20 Oct 2026)

The owner said "do everything, you have the authority". The items below need the owner's own accounts. I can't
create accounts, sign in, enter passwords or payment details, or accept partner terms for you, so they stay with
you. Each one says what I do once you're done.

## 1. Pinterest and Instagram
- **What you do:** create a business account on each (Pinterest Business is free).
- **Then:** post from `docs/SOCIAL-POSTS-2026-10-20.md`: 10 trips and 3 routes, ready to copy, each link tagged so
  Visits counts it. Pinterest first, one pin a day.

## 2. New partners
| Partner | Why | Where to apply | Then I do |
|---|---|---|---|
| Tiqets | Museum and site tickets (for the Grand Egyptian Museum and Giza guides), if Tiqets sells them for Egypt: check its catalogue first | tiqets.com, Affiliate programme | Add verified offers with facts from their product pages, as with GetYourGuide |
| Klook | Strong in Asia; the owner's target includes China | klook.com, Affiliate | Same |
| Booking.com (via CJ) | Deferred on 16 Oct (4% and a session-only cookie; one region per application). Worth it only if Trip.com stays without data | cj.com | Hotel boxes beside Trip.com |

I have not checked commission rates or Egypt coverage for Tiqets and Klook today. I'll read their programme pages
when you apply, and add nothing that isn't on their own pages.

## 3. Newsletter sending (optional, recommended)
- **Now:** the newsletter works (Core 1.2.62). Confirmation and welcome emails go through the host's mail.
- **Problem:** host mail often lands in spam.
- **Fix:** a free transactional mail account, such as Brevo or Mailgun, set in GoDaddy's mail or an SMTP plugin.
- **You do:** create the account and enter the key yourself; I never handle keys.
- **Then I:** test delivery with a test address.

## 4. Google Business Profile: not possible
Google's guidelines don't give a Business Profile to online-only businesses without a place customers visit or a
service area. Egypt Roamer is an online publication, so it doesn't qualify. Nothing to do.

## 5. Native-speaker review of the translations
German, Russian and Chinese first: the target markets. A reviewer can work straight on the live pages, or on
`tools/i18n/new-strings.json` and `content/editorial/<lang>/`.

## 6. Waiting for partners' replies
- **GetYourGuide API:** ticket 22892318.
- **Trip.com API:** your email of 20 Oct.

When either replies with access: you enter the key in wp-admin, and I add their prices and ratings as with Viator.

## Decided against, for now: installing Rank Math
- The site already outputs a description, Open Graph, a canonical, the sitemap and schema on every page (Core
  `seo.php`).
- Installing Rank Math now would replace all of them at once during the indexing recovery, and move the sitemap to a
  new address that would need resubmitting in Google and Bing.
- **Revisit:** after the 22 Oct Search Console review, if titles need per-page control that Core can't give.
