"""List UI strings used by the theme (er_t/er_e) and the plugin's front end."""
import re, pathlib, json, sys
root = pathlib.Path(__file__).resolve().parent.parent
theme = root / "wordpress/wp-content/themes/egypt-roamer"
plugin = root / "wordpress/wp-content/plugins/egypt-roamer-core"
lit = r"""(?:'((?:[^'\\]|\\.)*)'|"((?:[^"\\]|\\.)*)")"""
def unq(m):
    return (m[0].replace("\\'", "'") if m[0] else m[1].replace('\\"', '"'))
theme_strings = []
for f in sorted(theme.rglob("*.php")):
    for m in re.findall(r"er_[te]\(\s*" + lit, f.read_text()):
        theme_strings.append(unq(m))
# Strings passed to er_t() through variables (scene defaults, alt texts, finder tabs, film).
for f in ("inc/home-settings.php", "front-page.php", "inc/payload.php"):
    text = (theme / f).read_text()
    for block in re.findall(r"(?:\[|,)\s*(?:\d+\s*=>\s*)?\[([^\[\]]+)\]", text):
        for m in re.findall(lit, block):
            v = unq(m)
            if re.match(r"^[A-Z0-9]", v) and re.search(r"[A-Za-z]{2}", v) and not re.search(r"[#=\"]|__|\d+vw$", v):
                theme_strings.append(v)
    for m in re.finditer(r"\$em\(\s*" + lit + r"\s*,\s*" + lit, text):
        g = m.groups()
        theme_strings += [unq(g[0:2]), unq(g[2:4])]
    for arr in re.findall(r"\$(?:film_captions|film_kickers|alts)\s*=\s*\[([^;]+)\];", text):
        theme_strings += [unq(m) for m in re.findall(lit, arr)]
# Plugin strings visitors can see (admin screens follow the admin user's language).
plugin_strings = []
text = (plugin / "includes/leads.php").read_text()
start = text.index("add_shortcode( 'er_contact_form'")
for m in re.findall(r"(?:esc_html__|esc_html_e)\(\s*" + lit, text[start:text.index("function er_handle_contact")]):
    plugin_strings.append(unq(m))
aff = (plugin / "includes/affiliate.php").read_text()
plugin_strings += [unq(m) for m in re.findall(r"__\(\s*" + lit + r"\s*,\s*'egypt-roamer-core'\s*\)\s*,?\s*$", aff[aff.index("function er_price_unit_label"):aff.index("/** Everything a template needs")], re.M)]
plugin_strings.append("How we work with partners")
# Post type names appear in archive titles (front end).
pt = (plugin / "includes/post-types.php").read_text()
pt = pt[pt.index("function er_public_types"):pt.index("function er_public_type_keys")]
plugin_strings += [unq(m) for m in re.findall(r"__\(\s*" + lit, pt)]
meta = (plugin / "includes/meta.php").read_text()
plugin_strings += [unq(m) for m in re.findall(r"=>\s*__\(\s*" + lit, meta[meta.index("function er_cta_options"):meta.index("/** Field schema per post type")])]
# Menu labels from the seed's menus (the footer renders the default-language menu localized in every
# language, er_localize_menu_item(), labelled with these translations).
cli = (plugin / "includes/cli.php").read_text()
cli = cli[cli.index("private function menus("):cli.index("set_theme_mod( 'nav_menu_locations', $locations );")]
theme_strings += [unq(m) for m in re.findall(r"\[\s*" + lit + r"\s*,\s*\$home", cli)]
out = {"theme": sorted(set(theme_strings)), "plugin": sorted(set(plugin_strings))}
json.dump(out, sys.stdout, ensure_ascii=False, indent=1)
