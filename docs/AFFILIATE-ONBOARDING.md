# Adding the first real affiliate provider and offers (prepared 2026-09-29)

**Status:**
- The architecture is approved and tested end to end (local acceptance run with test data): provider → offer → CTA (`rel="sponsored nofollow noopener"`) → disclosure → `/go/` two-step tracked redirect → click report → edits without code.
- **Production:** 0 providers; 15 offers, all **drafts**. These are prototype placeholders with no provider and no URL, some naming real brands (Sofitel Legend Old Cataract, Marriott Mena House, Adrère Amellal…). **They stay unpublished.** Never publish a brand offer without a verified affiliate relationship.
- **Nothing is invented here:** providers, URLs, commissions, tracking IDs and partnerships all come from the owner's real programme accounts.

## What the owner must supply per provider

| Needed | Why |
|---|---|
| Provider name and website | Provider record |
| The approved affiliate programme (account ID, terms) | Proof of a real relationship; disclosure wording |
| Tracked link format (deep link builder or parameters) | Offer URLs |
| The provider's booking host names (e.g. `www.example-provider.com`) | **Security allow-list:** `/go/` only redirects to these hosts |
| Allowed sub-ID/UTM parameters (from the programme terms) | Tracking fields |
| For each offer: the exact product page and any price you checked, with the date | Offer fields; a price only ever shows with its checked date |

## Steps in wp-admin (Egypt Roamer menu)

1. **Egypt Roamer → Providers → Add New**
   - Title: provider name.
   - **Provider website.**
   - **Allowed redirect domains**, one per line. This is the security gate: sub-domains are allowed, anything else is refused.
   - **Default affiliate URL** (optional): used by `/go/{provider-slug}/`.
   - **Status:** Active. "Inactive" pauses every offer of that provider instantly.
2. **Egypt Roamer → Offers → Add New** (or edit a placeholder only if it truly matches the real product; otherwise create new)
   - **Link:**
     - Provider;
     - **Target URL** (product page);
     - **Affiliate URL** (tracked deep link; wins when set);
     - optional search URL template for the homepage finder.
   - **Call to action:** CTA label (list or custom).
   - **Card text:** location, short facts line, optional badge (only claims you can support).
   - **Price** (optional): price-from, unit, currency and **price checked on**. It's hidden if there's no date.
   - **Where it appears:** destination / tour / experience / activity. This decides the pages and placements.
   - **Tracking:** utm_source / utm_medium / utm_campaign; extra parameters (`key=value`, with `{placement}`, `{page}`, `{offer}` placeholders).
   - **Publish.** The offer then appears on the linked pages with the disclosure line.
3. **Translations:** offers are per language (Polylang). Create the translation, or leave it English-only as the owner decides.
4. **Before the first offer goes live:** publish the **Affiliate Disclosure** page (`docs/LEGAL-TRUST-REQUIREMENTS.md`).

## Verification on production (after the owner publishes the first offer)

1. Open the linked experience page → the CTA shows the label, `rel="sponsored nofollow noopener"` and the disclosure line.
2. Click it → it goes through `/go/{offer}/` → the provider page with the tracking parameters; the host is in the allow-list.
3. Egypt Roamer → Reports → the click appears (offer, provider, page, placement, language). No IP or personal data is logged.
4. A `/go/` link to a host **not** in the allow-list is refused and falls back safely (already tested).
5. Change the CTA label or URL in wp-admin → the live page updates, with no code change.

I can run steps 1–4 read-only once the owner confirms the offer is published.

## What stays true

- No cart, checkout, payment or internal booking exists or will be added.
- Placeholder offers remain drafts. The owner can later delete them or replace them with real equivalents.
