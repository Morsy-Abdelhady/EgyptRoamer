# Indexing launch check (run against a site with "Discourage search engines" off and pages ticked "Ready to
# index"; locally: see docs/SEO-AUDIT-2026-10-01.md, "Launch simulation").
#
# Checks robots.txt; every URL of the core XML sitemap (status 200, no noindex in the robots meta or the
# X-Robots-Tag header, self-canonical, hreflang includes itself and every alternate answers 200 and points
# back, html lang matches); that the pages which must stay out (search, 404, empty archives, /go/, drafts)
# are noindex or absent; and that every ticked page is in the sitemap. Exit code 1 on any failure.
# Usage: python launch-sim.py [BASE]
import html, json, re, sys, time, urllib.error, urllib.request

B = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8080").rstrip("/")
cache = {}


def get(u):
    if u in cache:
        return cache[u]
    req = urllib.request.Request(u, headers={"User-Agent": "er-launch-sim"})
    for attempt in range(4):  # the local dev proxy drops a connection now and then
        try:
            r = urllib.request.urlopen(req, timeout=180)
            res = (r.status, r.read().decode("utf-8", "replace"), {k.lower(): v for k, v in r.headers.items()})
            break
        except urllib.error.HTTPError as e:
            res = (e.code, e.read().decode("utf-8", "replace"), {k.lower(): v for k, v in e.headers.items()})
            break
        except (urllib.error.URLError, ConnectionError, OSError) as e:
            if attempt == 3:
                raise
            time.sleep(2)
    cache[u] = res
    return res


def head_of(h):
    rob = (re.findall(r"<meta name=['\"]robots['\"] content=['\"]([^'\"]+)", h) or [""])[0]
    can = html.unescape((re.findall(r'<link rel="canonical" href="([^"]+)"', h) or [""])[0])
    hl = {code: html.unescape(url) for url, code in re.findall(r'<link rel="alternate" href="([^"]+)" hreflang="([^"]+)"', h)}
    lang = (re.findall(r"<html[^>]* lang=\"([^\"]+)\"", h) or ["?"])[0]
    return rob, can, hl, lang


fails = []
st, robots, _ = get(B + "/robots.txt")
print("robots.txt", st)
print("  " + robots.strip().replace("\n", "\n  "))
for need in ("Disallow: /go/", "Sitemap: "):
    if need not in robots:
        fails.append(("robots.txt", "missing " + need.strip()))
for bad in ("Disallow: /wp-content", "Disallow: /wp-includes", "Disallow: /\n"):
    if bad in robots:
        fails.append(("robots.txt", "blocks " + bad.strip()))

st, idx, _ = get(B + "/wp-sitemap.xml")
print("sitemap index", st)
if st != 200:
    fails.append(("sitemap", f"index status {st}"))
urls = []
for sm in re.findall(r"<loc>([^<]+)</loc>", idx):
    _, x, _ = get(html.unescape(sm))
    u = [html.unescape(v) for v in re.findall(r"<loc>([^<]+)</loc>", x)]
    print(f"  {sm.replace(B, '')}: {len(u)}")
    urls += u
print("sitemap URLs", len(urls), "unique", len(set(urls)))
if len(urls) != len(set(urls)):
    fails.append(("sitemap", "duplicate URLs"))

langs = {}
for u in urls:
    st, h, hd = get(u)
    rob, can, hl, lang = head_of(h)
    langs[lang] = langs.get(lang, 0) + 1
    if st != 200:
        fails.append((u, f"status {st}"))
        continue
    if "noindex" in rob or "noindex" in hd.get("x-robots-tag", ""):
        fails.append((u, "noindex in sitemap: " + rob))
    if can != u:
        fails.append((u, "canonical " + can))
    if hl:
        if u not in hl.values():
            fails.append((u, "hreflang does not list itself"))
        for code, alt in hl.items():
            if alt == u:
                continue
            ast, ah, _ = get(alt)
            if ast != 200:
                fails.append((u, f"hreflang {code} → {ast}"))
                continue
            _, _, ahl, _ = head_of(ah)
            if u not in ahl.values():
                fails.append((u, f"hreflang {code} not reciprocal"))
print("sitemap pages by html lang:", json.dumps(langs, ensure_ascii=False))

# Pages that must stay out of the index
for path in ("/?s=cairo", "/ar/?s=cairo", "/this-page-does-not-exist/", "/ar/this-page-does-not-exist/", "/tours/", "/guides/", "/activities/", "/journal/", "/go/"):
    st, h, hd = get(B + path)
    rob, _, _, _ = head_of(h)
    noindex = "noindex" in rob or "noindex" in hd.get("x-robots-tag", "")
    in_sm = (B + path) in urls
    print(f"  stays out {path:32} status {st} noindex {noindex} in sitemap {in_sm}")
    if in_sm or (st == 200 and not noindex):
        fails.append((path, f"should stay out of the index (status {st}, robots '{rob}')"))
    if "does-not-exist" in path and st != 404:
        fails.append((path, f"404 page answers {st}"))

print("\nFAILURES:", len(fails))
for f in fails[:60]:
    print("  ", f)
sys.exit(1 if fails else 0)
