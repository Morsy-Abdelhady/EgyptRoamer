#!/usr/bin/env python3
"""Build the legal/trust page translations for Egypt Roamer Core.

Sources:
  content/legal/en/*.html          the English pages (edited in WordPress; kept here for review)
  content/legal/i18n/<lang>.json   translated texts, one key per block of the English page
Output (shipped with Core, applied by `wp egypt-roamer legal` / Egypt Roamer → Legal pages):
  wordpress/wp-content/plugins/egypt-roamer-core/data/legal/<lang>/<slug>.html
  .../data/legal/en/contact.html   the new English Contact layout
  .../data/legal/index.json        titles, slugs, and the English content the Contact update replaces

Every translated page has exactly the English page's blocks, anchors and links, so the structure
stays reviewable against English. Inline syntax in the texts: **bold**, `code`, [text](target) with
target = privacy | cookies | disclosure | en (this page's English original), and the placeholders
{name} {address} {email} {phone} {book}.

  python tools/legal.py          build
  python tools/legal.py check    fail if the output is not up to date
"""
import html
import json
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
SRC = ROOT / "content" / "legal"
OUT = ROOT / "wordpress" / "wp-content" / "plugins" / "egypt-roamer-core" / "data" / "legal"
THEME_L10N = ROOT / "wordpress" / "wp-content" / "themes" / "egypt-roamer" / "languages"
LANGS = {"ar": "ar", "de": "de_DE", "fr": "fr_FR", "it": "it_IT", "es": "es_ES", "ru": "ru_RU", "zh": "zh_CN"}
SLUGS = {"privacy": "privacy-policy", "cookies": "cookies", "disclosure": "affiliate-disclosure", "contact": "contact"}
EN_URL = {"privacy": "/privacy-policy/", "cookies": "/cookies/", "disclosure": "/affiliate-disclosure/", "contact": "/contact/"}

NAME = '<span lang="ar" dir="rtl">شركة يوزين لخدمات الإنترنت والتسوق (ش. ذ. م. م.)</span>'
ADDRESS = '<span lang="ar" dir="rtl">شارع متفرع من شارع القسم - أمام مدرسة الحسن ابن الهيثم - العامرية أول - الإسكندرية</span>'
EMAIL = '<a href="mailto:info@egyptroamer.com">info@egyptroamer.com</a>'
PHONE = '<a href="tel:+201060494260" dir="ltr">+20 10 6049 4260</a>'


def book_label(locale: str) -> str:
    text = (THEME_L10N / f"{locale}.l10n.php").read_text(encoding="utf-8")
    return re.search(r"'Book with our partners' => '((?:[^'\\]|\\.)*)'", text).group(1).replace("\\'", "'")


def inline(text: str, lang: str, doc: str) -> str:
    def link(m):
        target = m.group(2)
        url = EN_URL[doc] if target == "en" else f"/{lang}/{SLUGS[target]}-{lang}/"
        attr = ' hreflang="en"' if target == "en" else ""
        return f'<a href="{url}"{attr}>{m.group(1)}</a>'

    out = html.escape(text, quote=False).replace("&lt;br&gt;", "<br>")
    out = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", out)
    out = re.sub(r"`(.+?)`", lambda m: "<code>" + m.group(1).replace("&quot;", '"') + "</code>", out)
    out = re.sub(r"\[([^\]]+)\]\((privacy|cookies|disclosure|en)\)", link, out)
    for key, value in (("{name}", NAME), ("{address}", ADDRESS), ("{email}", EMAIL), ("{phone}", PHONE)):
        out = out.replace(key, value)
    return out


def p(text, cls=""):
    attrs = f' {{"className":"{cls}"}}' if cls else ""
    klass = f' class="{cls}"' if cls else ""
    return f"<!-- wp:paragraph{attrs} -->\n<p{klass}>{text}</p>\n<!-- /wp:paragraph -->"


def h2(text, anchor):
    return f'<!-- wp:heading {{"anchor":"{anchor}"}} -->\n<h2 class="wp-block-heading" id="{anchor}">{text}</h2>\n<!-- /wp:heading -->'


def ul(items, cls=""):
    attrs = f' {{"className":"{cls}"}}' if cls else ""
    klass = f"wp-block-list {cls}".strip()
    inner = "\n".join(f"<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->" for i in items)
    return f'<!-- wp:list{attrs} -->\n<ul class="{klass}">{inner}</ul>\n<!-- /wp:list -->'


def contact(t):
    """Contact: intro, details and booking note beside the form (theme: .er-contact)."""
    email_ltr = EMAIL.replace("<a ", '<a dir="ltr" ')
    details = ul([f"<span>{t('l_email')}</span> {email_ltr}", f"<span>{t('l_phone')}</span> {PHONE}"], "er-contact__details")
    info = "\n\n".join([p(t("intro")), details, p(t("booking"))])
    form = "\n\n".join([h2(t("h_form"), "write-to-us"), "<!-- wp:shortcode -->\n[er_contact_form]\n<!-- /wp:shortcode -->"])
    return (
        '<!-- wp:group {"className":"er-contact"} -->\n<div class="wp-block-group er-contact">'
        '<!-- wp:group {"className":"er-contact__info"} -->\n<div class="wp-block-group er-contact__info">' + info + "</div>\n<!-- /wp:group -->\n\n"
        '<!-- wp:group {"className":"er-contact__form"} -->\n<div class="wp-block-group er-contact__form">' + form + "</div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->"
    )


def privacy(t, note):
    return "\n\n".join([
        p(f"<em>{t('updated')}</em>"), p(t("intro")), note,
        h2(t("h_who"), "who-we-are"), p(t("who_1")), p(t("who_2")),
        h2(t("h_what"), "what-we-handle"), ul([t(k) for k in ("li_contact", "li_newsletter", "li_clicks", "li_spam", "li_storage")]), p(t("what_after")),
        h2(t("h_services"), "who-else"), ul([t(k) for k in ("li_godaddy", "li_cloudflare", "li_unsplash", "li_partners")]), p(t("services_after")),
        h2(t("h_long"), "how-long"), ul([t(k) for k in ("li_long_contact", "li_long_news", "li_long_clicks", "li_long_spam")]),
        h2(t("h_choices"), "your-choices"), p(t("choices")),
        h2(t("h_children"), "children"), p(t("children")),
        h2(t("h_changes"), "changes"), p(t("changes")),
    ])


def cookies(t, note):
    table = (
        '<!-- wp:table -->\n<figure class="wp-block-table"><table><thead><tr>'
        + "".join(f"<th>{t(k)}</th>" for k in ("th_name", "th_setby", "th_purpose", "th_duration"))
        + "</tr></thead><tbody><tr><td><code>__cf_bm</code></td>"
        + "".join(f"<td>{t(k)}</td>" for k in ("td_setby", "td_purpose", "td_duration"))
        + "</tr></tbody></table></figure>\n<!-- /wp:table -->"
    )
    return "\n\n".join([
        p(f"<em>{t('updated')}</em>"), p(t("intro")), note,
        h2(t("h_cookies"), "cookies"), table,
        h2(t("h_storage"), "browser-storage"), p(t("storage_intro")), ul([t("li_local"), t("li_session")]),
        h2(t("h_partners"), "partners"), p(t("partners")),
        h2(t("h_control"), "control"), p(t("control")), p(t("questions")),
    ])


def disclosure(t, note):
    return "\n\n".join([
        p(t("intro")), note,
        h2(t("h_partners"), "partners"), p(t("partners")),
        h2(t("h_how"), "how-links-work"), ul([t("li_offers"), t("li_go"), t("li_rel")]),
        h2(t("h_sell"), "we-do-not-sell"), p(t("sell")), p(t("endorse")),
        h2(t("h_indep"), "independence"), p(t("indep")), p(t("questions")),
    ])


def build() -> dict:
    files = {}
    index = {"_note": "Built by tools/legal.py from content/legal. Translations drafted 2026-09-30, pending native review.", "pages": {}, "en_updates": {}}
    for lang, locale in LANGS.items():
        data = json.loads((SRC / "i18n" / f"{lang}.json").read_text(encoding="utf-8"))
        book = book_label(locale)
        for doc, slug in SLUGS.items():
            texts = data[doc]

            def t(key, _texts=texts, _doc=doc):
                return inline(_texts[key].replace("{book}", book), lang, _doc)

            note = p(t("note"), "er-translation-note") if doc != "contact" else ""
            body = {"privacy": privacy, "cookies": cookies, "disclosure": disclosure}[doc](t, note) if doc != "contact" else contact(t)
            files[f"{lang}/{slug}.html"] = body + "\n"
            index["pages"].setdefault(slug, {})[lang] = {"title": texts["title"], "slug": f"{slug}-{lang}"}
    # English Contact: the new layout, replacing exactly the published version from git history.
    en_new = contact(lambda k: inline({
        "intro": "Questions about travelling in Egypt, a correction to one of our pages, or a partnership idea: write to us. We read every message and reply personally.",
        "l_email": "Email", "l_phone": "Phone",
        "booking": "**About a booking?** We do not take bookings or payments. If you booked through one of our partner links, the partner you booked with handles changes, cancellations and refunds: please contact them directly.",
        "h_form": "Send us a message",
    }[k], "en", "contact"))
    files["en/contact.html"] = en_new + "\n"
    # sha1 of the English Contact content published on 2026-09-30 (git cf01c79:content/legal/en/contact.html, trimmed).
    index["en_updates"]["contact"] = {"file": "en/contact.html", "replaces_sha1": "f3a53c653c371213f9cb2ea16f6ff0d4c0698009"}
    files["index.json"] = json.dumps(index, ensure_ascii=False, indent=1) + "\n"
    return files


def main():
    files = build()
    if len(sys.argv) > 1 and sys.argv[1] == "check":
        stale = [k for k, v in files.items() if not (OUT / k).is_file() or (OUT / k).read_text(encoding="utf-8") != v]
        if stale:
            sys.exit("legal data is stale; run python tools/legal.py: " + ", ".join(stale))
        print(f"legal data up to date ({len(files)} files)")
        return
    for rel, text in files.items():
        (OUT / rel).parent.mkdir(parents=True, exist_ok=True)
        (OUT / rel).write_text(text, encoding="utf-8", newline="\n")
    (SRC / "en" / "contact.html").write_text(files["en/contact.html"], encoding="utf-8", newline="\n")
    print(f"wrote {len(files)} files to {OUT.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
