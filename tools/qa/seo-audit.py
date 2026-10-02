# Full technical SEO audit of the whole inventory (launch gate SEO workstream, 2026-10-02).
#
# Fetches every URL of the inventory (tools/qa/site-inventory.php → urls.json), every URL listed in the XML
# sitemaps, and a set of probe URLs (utility, duplicate, legacy and edge cases), without following redirects.
# For each page: status, X-Robots-Tag, meta robots, canonical, hreflang, html lang/dir, title, description,
# Open Graph, H1–H3 outline, JSON-LD, internal links and images. Then checks the site as a whole:
#   indexability classes · sitemap = indexable set · canonical/hreflang matrix and reciprocity ·
#   title/description uniqueness and quality · headings · schema validity and consistency · internal links
#   (status, redirects, noindex targets, wrong language, orphans, depth) · images · affiliate and chat routes.
# Writes seo-audit.json (everything) and prints the findings. Exit code 1 when an error-level finding exists.
#
#   python seo-audit.py [BASE] [urls.json]          BASE defaults to http://127.0.0.1:8080
#   On production: GAP=2 python seo-audit.py https://egyptroamer.com urls.json   (stops on 429/challenge)
import html as htmllib, json, os, re, sys, time, urllib.error, urllib.parse, urllib.request
from collections import Counter, defaultdict, deque
from html.parser import HTMLParser

B = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8080").rstrip("/")
INV = sys.argv[2] if len(sys.argv) > 2 else "urls.json"
GAP = float(os.environ.get("GAP", "0"))
HOST = urllib.parse.urlsplit(B).netloc
LANGS = ["en", "de", "fr", "it", "es", "ru", "zh", "ar"]
DEF = "en"
UA = "Mozilla/5.0 (compatible; er-seo-audit/1.0)"


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


opener = urllib.request.build_opener(NoRedirect)
cache = {}


def fetch(url, method="GET"):
    key = (method, url)
    if key in cache:
        return cache[key]
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Language": "en"}, method=method)
    for attempt in range(4):
        try:
            r = opener.open(req, timeout=180)
            res = {"status": r.status, "headers": {k.lower(): v for k, v in r.headers.items()}, "body": r.read().decode("utf-8", "replace") if method == "GET" else ""}
            break
        except urllib.error.HTTPError as e:
            res = {"status": e.code, "headers": {k.lower(): v for k, v in e.headers.items()}, "body": e.read().decode("utf-8", "replace") if method == "GET" else ""}
            break
        except (urllib.error.URLError, ConnectionError, OSError):
            if attempt == 3:
                res = {"status": 0, "headers": {}, "body": ""}
            time.sleep(2)
    if res["status"] == 429 or res["headers"].get("cf-mitigated"):
        print("STOP: rate limited / challenged at", url)
        sys.exit(2)
    if GAP:
        time.sleep(GAP)
    cache[key] = res
    return res


def absu(u, base=None):
    return urllib.parse.urljoin(base or B + "/", htmllib.unescape(u or ""))


def norm(u):
    """Comparable form: scheme+host+decoded path(+query)."""
    s = urllib.parse.urlsplit(u)
    path = urllib.parse.unquote(s.path)
    return f"{s.scheme}://{s.netloc}{path}" + (f"?{s.query}" if s.query else "")


def rel(u):
    s = urllib.parse.urlsplit(u)
    return urllib.parse.unquote(s.path) + (f"?{s.query}" if s.query else "")


def lang_of_path(p):
    seg = urllib.parse.unquote(urllib.parse.urlsplit(p).path).strip("/").split("/")[0]
    return seg if seg in LANGS else DEF


class Page(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.title = ""
        self._in = []
        self.meta = defaultdict(list)
        self.links = []          # (href, rel, text, context)
        self.alts = []           # hreflang
        self.canon = []
        self.headings = []       # (level, text)
        self.imgs = []
        self.jsonld = []
        self.html_lang = ""
        self.html_dir = ""
        self._script_ld = False
        self._buf = ""
        self._hbuf = None
        self._abuf = None
        self._ctx = []           # header/nav/footer/main/aside context stack
        self.text_main = []
        self._in_body = False

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == "html":
            self.html_lang, self.html_dir = a.get("lang", ""), a.get("dir", "")
        if tag in ("header", "nav", "footer", "main", "aside", "dialog") or (tag == "div" and ("drawer" in (a.get("class") or "") or a.get("role") == "dialog")):
            self._ctx.append(tag if tag != "div" else "dialog")
        else:
            self._ctx.append(None)
        if tag == "title" and not self.title and not any(c for c in self._ctx[:-1]) and not self._in_body:
            self._in.append("title")  # the document title only (not an SVG <title> in the body)
        if tag == "body":
            self._in_body = True
        if tag == "meta":
            k = a.get("name") or a.get("property")
            if k:
                self.meta[k.lower()].append(a.get("content", ""))
        if tag == "link":
            r = (a.get("rel") or "").lower()
            if r == "canonical":
                self.canon.append(a.get("href", ""))
            if r == "alternate" and a.get("hreflang"):
                self.alts.append((a.get("hreflang"), a.get("href", "")))
        if tag == "script" and (a.get("type") or "") == "application/ld+json":
            self._script_ld, self._buf = True, ""
        if tag in ("h1", "h2", "h3", "h4"):
            self._hbuf = [int(tag[1]), ""]
        if tag == "a" and a.get("href") is not None:
            ctx = next((c for c in reversed(self._ctx) if c), "main?")
            self._abuf = [a.get("href"), (a.get("rel") or ""), "", ctx, a.get("hreflang"), a.get("data-er-lang") or a.get("lang")]
        if tag == "img":
            ctx = next((c for c in reversed(self._ctx) if c), "main?")
            self.imgs.append({"src": a.get("src", ""), "alt": a.get("alt"), "w": a.get("width"), "h": a.get("height"), "loading": a.get("loading"), "ctx": ctx, "aria_hidden": a.get("aria-hidden"), "cls": a.get("class", "")})

    def handle_endtag(self, tag):
        if self._ctx:
            self._ctx.pop()
        if tag == "title" and self._in:
            self._in.pop()
        if tag == "script" and self._script_ld:
            self._script_ld = False
            try:
                self.jsonld.append(json.loads(self._buf))
            except Exception as e:  # noqa
                self.jsonld.append({"__error__": str(e), "__raw__": self._buf[:200]})
        if tag in ("h1", "h2", "h3", "h4") and self._hbuf:
            self.headings.append((self._hbuf[0], re.sub(r"\s+", " ", self._hbuf[1]).strip()))
            self._hbuf = None
        if tag == "a" and self._abuf:
            self._abuf[2] = re.sub(r"\s+", " ", self._abuf[2]).strip()
            self.links.append(tuple(self._abuf))
            self._abuf = None

    def handle_data(self, d):
        if self._in and self._in[-1] == "title":
            self.title += d
        if self._script_ld:
            self._buf += d
        if self._hbuf is not None:
            self._hbuf[1] += d
        if self._abuf is not None:
            self._abuf[2] += d
        ctx = next((c for c in reversed(self._ctx) if c), None)
        if ctx == "main":
            self.text_main.append(d)


findings = []


def F(level, code, url, msg):
    findings.append({"level": level, "code": code, "url": url, "msg": msg})


# ---------------------------------------------------------------- inventory + sitemaps
inv = json.load(open(INV, encoding="utf-8"))
urls = {}
for u in inv:
    urls[rel(absu(u["url"]))] = {"type": u["type"], "lang": u.get("lang") or lang_of_path(u["url"]), "src": "inventory"}

robots = fetch(B + "/robots.txt")
sm_urls = {}
sm_index = fetch(B + "/wp-sitemap.xml")
children = re.findall(r"<loc>([^<]+)</loc>", sm_index["body"]) if sm_index["status"] == 200 else []
for c in children:
    r = fetch(htmllib.unescape(c))
    if r["status"] != 200 or "<urlset" not in r["body"]:
        F("error", "sitemap-child", c, f"status {r['status']}")
        continue
    for block in re.findall(r"<url>(.*?)</url>", r["body"], re.S):
        loc = htmllib.unescape(re.search(r"<loc>([^<]+)</loc>", block).group(1))
        lm = re.search(r"<lastmod>([^<]+)</lastmod>", block)
        k = rel(loc)
        if k in sm_urls:
            F("error", "sitemap-duplicate", k, "listed twice")
        sm_urls[k] = {"sitemap": rel(c), "lastmod": lm.group(1) if lm else None, "loc": loc}
for k in sm_urls:
    if k not in urls:
        urls[k] = {"type": "sitemap-only", "lang": lang_of_path(k), "src": "sitemap"}

# Probe URLs: utility, duplicates and edge cases that must not become indexable pages.
first_dest = next((k for k, v in urls.items() if v["type"] == "er_destination" and v["lang"] == "en"), "/destinations/cairo/")
first_exp = next((k for k, v in urls.items() if v["type"] == "er_experience" and v["lang"] == "en"), "/experiences/")
probes = {
    first_dest.rstrip("/"): "no trailing slash → 301 to slash",
    first_dest + "?utm_source=x&utm_medium=y": "tracking parameters → clean canonical",
    first_dest.upper() if first_dest.isascii() else first_dest: "upper case path",
    first_dest + "feed/": "post feed",
    first_dest + "amp/": "amp endpoint",
    first_dest + "page/2/": "paged single",
    "/destinations/page/2/": "archive page beyond range",
    "/destinations/?sort=name": "sorted archive (noindex)",
    "/destinations/?destination=cairo": "filtered archive (noindex)",
    "/?s=cairo": "search (noindex)",
    "/search/cairo/": "pretty search (noindex)",
    "/?s=": "empty search",
    "/feed/": "site feed",
    "/comments/feed/": "comments feed",
    "/de/feed/": "language feed",
    "/author/admin/": "author archive",
    "/2026/": "date archive",
    "/2026/10/": "month archive",
    "/category/uncategorized/": "uncategorized",
    "/tag/egypt/": "tag",
    "/?p=1": "post id query",
    "/?page_id=2": "page id query",
    "/?attachment_id=1": "attachment",
    "/no-such-page-xyz/": "unknown (404)",
    "/destinations/no-such-destination/": "unknown destination (404)",
    "/de/no-such-page-xyz/": "unknown, German (404)",
    "/xx/": "unknown language prefix",
    "/en/": "default language prefix",
    "/DE/": "upper-case language prefix",
    "//destinations//": "double slashes",
    "/index.php": "index.php",
    "/index.html": "legacy index.html (301)",
    "/privacy": "legacy /privacy (301)",
    "/guide/": "legacy /guide (301)",
    "/wp-json/": "REST root",
    "/wp-json/egypt-roamer/v1/build": "build endpoint",
    "/wp-json/egypt-roamer/v1/chat/status": "chat route (GET)",
    "/wp-login.php": "login",
    "/xmlrpc.php": "xmlrpc",
    "/wp-admin/": "admin",
    "/go/": "affiliate root",
    "/go/no-such-offer/": "affiliate unknown",
    "/wp-sitemap-users-1.xml": "users sitemap (removed)",
    "/wp-sitemap-taxonomies-category-1.xml": "category sitemap",
}
for p, why in probes.items():
    if p not in urls:
        urls[p] = {"type": "probe", "lang": lang_of_path(p), "src": "probe", "why": why}

# ---------------------------------------------------------------- fetch + parse
pages = {}
for i, (u, meta) in enumerate(sorted(urls.items())):
    r = fetch(B + urllib.parse.quote(u, safe="/?&=%:#+"))
    rec = {"url": u, **meta, "status": r["status"], "location": r["headers"].get("location"), "xrobots": r["headers"].get("x-robots-tag", ""), "ctype": r["headers"].get("content-type", "")}
    if r["status"] == 200 and "html" in rec["ctype"]:
        p = Page()
        try:
            p.feed(r["body"])
        except Exception as e:  # noqa
            F("error", "parse", u, str(e))
        rec.update(
            title=htmllib.unescape(p.title.strip()),
            desc=(p.meta.get("description") or [""])[0],
            descs=len(p.meta.get("description", [])),
            robots=",".join(p.meta.get("robots", [])),
            canon=p.canon,
            alts=p.alts,
            lang=p.html_lang or rec["lang"],
            plang=rec["lang"],
            dir=p.html_dir,
            og={k: v for k, v in p.meta.items() if k.startswith("og:") or k.startswith("twitter:")},
            headings=p.headings,
            links=p.links,
            imgs=p.imgs,
            jsonld=p.jsonld,
            words=len(re.findall(r"\w+", " ".join(p.text_main))),
            text_main=re.sub(r"\s+", " ", " ".join(p.text_main))[:4000],
        )
    pages[u] = rec
    if i % 40 == 0:
        print(f"  fetched {i}/{len(urls)}", file=sys.stderr)


def is_noindex(rec):
    return "noindex" in (rec.get("robots") or "").lower() or "noindex" in (rec.get("xrobots") or "").lower()


def canon_of(rec):
    return rel(absu(rec["canon"][0])) if rec.get("canon") else None


indexable = {u for u, r in pages.items() if r["status"] == 200 and r.get("title") is not None and not is_noindex(r) and canon_of(r) == u}

# ---------------------------------------------------------------- indexability & sitemap
for u, r in pages.items():
    src, st = r["src"], r["status"]
    if src == "inventory" and r["type"].startswith(("er_", "page", "archive")) and st not in (200, 301, 302, 404):
        F("error", "status", u, f"status {st}")
    if st == 200 and r.get("title") is not None:
        if len(r["canon"]) > 1:
            F("error", "canonical-multiple", u, f"{len(r['canon'])} canonical tags")
        if r.get("descs", 0) > 1:
            F("error", "description-multiple", u, f"{r['descs']} meta descriptions")
        c = canon_of(r)
        if not is_noindex(r):
            if not c:
                F("error", "canonical-missing", u, "indexable page without canonical")
            elif c != u:
                tgt = pages.get(c)
                F("warn" if r["src"] == "probe" else "error", "canonical-other", u, f"canonical → {c} ({tgt['status'] if tgt else 'not fetched'})")
        if c and c != u and c in pages and (pages[c]["status"] != 200 or is_noindex(pages[c])):
            F("error", "canonical-target-bad", u, f"canonical target {c} is {pages[c]['status']}{' noindex' if is_noindex(pages[c]) else ''}")
        if c and not absu(r["canon"][0]).startswith(B):
            F("error", "canonical-host", u, f"canonical on another host: {r['canon'][0]}")
for u in sm_urls:
    r = pages.get(u)
    if not r:
        continue
    if r["status"] != 200:
        F("error", "sitemap-status", u, f"in sitemap with status {r['status']} → {r.get('location')}")
    elif is_noindex(r):
        F("error", "sitemap-noindex", u, "in sitemap but noindex")
    elif canon_of(r) != u:
        F("error", "sitemap-not-canonical", u, f"in sitemap but canonical → {canon_of(r)}")
    if sm_urls[u]["sitemap"].split("/")[1] in LANGS and lang_of_path(u) != sm_urls[u]["sitemap"].split("/")[1]:
        F("error", "sitemap-language", u, f"listed in {sm_urls[u]['sitemap']}")
for u in indexable:
    if u not in sm_urls:
        F("error", "indexable-not-in-sitemap", u, f"type {pages[u]['type']}")
# lastmod sanity
for u, m in sm_urls.items():
    if m["lastmod"] and not re.match(r"^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d([+-]\d\d:\d\d|Z)$", m["lastmod"]):
        F("warn", "sitemap-lastmod-format", u, m["lastmod"])
    if m["lastmod"] and m["lastmod"][:4] > time.strftime("%Y"):
        F("error", "sitemap-lastmod-future", u, m["lastmod"])

# ---------------------------------------------------------------- probes: expectations
for u, r in pages.items():
    if r["src"] != "probe":
        continue
    st, loc, why = r["status"], r.get("location"), r.get("why", "")
    ok_noindex = st != 200 or is_noindex(r) or (canon_of(r) and canon_of(r) != u)
    expect_404 = "(404)" in why or why.startswith("unknown")
    if expect_404 and st != 404:
        F("error", "soft-404", u, f"{why}: status {st} → {loc}")
    if "301" in why and st != 301:
        F("error", "redirect-expected", u, f"{why}: status {st}")
    if st in (301, 302, 307, 308) and loc:
        t = rel(absu(loc, B + u))
        t2 = pages.get(t) or {"status": fetch(B + urllib.parse.quote(t, safe='/?&=%:#+'))["status"]}
        if t2["status"] in (301, 302, 307, 308):
            # the affiliate route is a two-step redirect by design, inside a path robots.txt disallows
            F("info" if u.startswith("/go/") else "error", "redirect-chain", u, f"{u} → {t} → again")
    if st == 200 and not ok_noindex and "noindex" in why:
        F("error", "probe-indexable", u, f"{why}: indexable 200 page")
    if st == 200 and "html" in r.get("ctype", "") and not ok_noindex and why not in ("tracking parameters → clean canonical",):
        F("warn", "probe-indexable", u, f"{why}: 200, indexable, self-canonical")
    if u.startswith(("/wp-json", "/go")) and st == 200 and "noindex" not in r.get("xrobots", ""):
        F("warn", "utility-xrobots", u, f"{why}: 200 without X-Robots-Tag noindex")

# ---------------------------------------------------------------- hreflang matrix
hl_codes = set()
for u in sorted(indexable):
    r = pages[u]
    alts = {}
    for code, href in r["alts"]:
        hl_codes.add(code)
        if code in alts:
            F("error", "hreflang-duplicate", u, code)
        alts[code] = rel(absu(href))
    if not alts:
        F("error", "hreflang-missing", u, "no alternates")
        continue
    mine = [c for c, h in alts.items() if h == u and c != "x-default"]
    if not mine:
        F("error", "hreflang-self", u, "own URL not among alternates")
    if "x-default" not in alts:
        F("error", "hreflang-x-default", u, "no x-default")
    for code, h in alts.items():
        t = pages.get(h)
        if not t:
            F("error", "hreflang-unfetched", u, f"{code} → {h} (not in inventory or sitemap)")
            continue
        if t["status"] != 200 or is_noindex(t) or canon_of(t) != h:
            F("error", "hreflang-target-bad", u, f"{code} → {h}: {t['status']}{' noindex' if is_noindex(t) else ''} canonical {canon_of(t)}")
        if code != "x-default":
            tl = (t.get("lang") or "").split("-")[0].lower()
            if tl and tl != code.split("-")[0].lower():
                F("error", "hreflang-wrong-language", u, f"{code} → {h} has lang={t.get('lang')}")
            back = dict((c, rel(absu(x))) for c, x in t.get("alts", []))
            if back.get(mine[0] if mine else "") != u:
                F("error", "hreflang-not-reciprocal", u, f"{code} → {h} does not point back")
    if len([c for c in alts if c != "x-default"]) < len(LANGS) and pages[u]["type"] not in ("page",):
        F("info", "hreflang-partial", u, f"{len(alts)} alternates")
bad_codes = [c for c in hl_codes if c != "x-default" and not re.match(r"^[a-z]{2}(-[A-Z]{2}|-Hans|-Hant)?$", c)]
for c in bad_codes:
    F("error", "hreflang-code", "-", c)

# ---------------------------------------------------------------- metadata
by_lang_title, by_lang_desc = defaultdict(list), defaultdict(list)
for u in indexable:
    r = pages[u]
    by_lang_title[(r["plang"], r["title"])].append(u)
    by_lang_desc[(r["plang"], r["desc"])].append(u)
    t, d = r["title"], r["desc"]
    cjk = r["plang"] in ("zh",)
    tl = len(t)
    if not t:
        F("error", "title-missing", u, "")
    elif (not cjk and (tl < 15 or tl > 65)) or (cjk and (tl < 6 or tl > 34)):
        F("warn", "title-length", u, f"{tl} chars: {t}")
    if t.count("Egypt Roamer") > 1:
        F("warn", "title-brand-repeat", u, t)
    if not d:
        F("error", "description-missing", u, "")
    else:
        dl = len(d)
        if (not cjk and (dl < 70 or dl > 165)) or (cjk and (dl < 30 or dl > 90)):
            F("warn", "description-length", u, f"{dl} chars: {d[:90]}")
        if d.strip() == t.strip():
            F("warn", "description-equals-title", u, d)
        if d.endswith("…") or d.endswith("..."):
            F("info", "description-truncated", u, d[-60:])
    h1 = [h for h in r["headings"] if h[0] == 1]
    if len(h1) != 1:
        F("error", "h1-count", u, f"{len(h1)} H1")
    elif h1[0][1] and t and h1[0][1].split(" ")[0].lower() not in t.lower() and r["type"] not in ("page",) and u not in ("/",) and not re.match(r"^/[a-z]{2}/$", u):
        F("info", "h1-title-mismatch", u, f"H1 '{h1[0][1]}' vs title '{t}'")
    lv = [h[0] for h in r["headings"]]
    for a_, b_ in zip(lv, lv[1:]):
        if b_ > a_ + 1:
            F("warn", "heading-skip", u, f"H{a_} → H{b_}")
            break
    og = r["og"]
    for k in ("og:title", "og:description", "og:url", "og:image", "og:locale", "og:type"):
        if not og.get(k):
            F("warn", "og-missing", u, k)
    if og.get("og:url") and rel(absu(og["og:url"][0])) != u:
        F("error", "og-url", u, f"og:url {og['og:url'][0]}")
    if og.get("og:locale"):
        loc = og["og:locale"][0]
        if loc.split("_")[0] != r["plang"]:
            F("error", "og-locale", u, f"og:locale {loc} on a {r['plang']} page")
    if (r.get("lang") or "").split("-")[0] != r["plang"]:
        F("error", "html-lang", u, f"lang={r.get('lang')} for {r['plang']}")
    if r["plang"] == "ar" and r.get("dir") != "rtl":
        F("error", "rtl", u, "Arabic page without dir=rtl")
for (lg, t), us in by_lang_title.items():
    if len(us) > 1:
        F("error", "title-duplicate", us[0], f"{lg}: '{t}' on {len(us)} pages: {us[:4]}")
for (lg, d), us in by_lang_desc.items():
    if len(us) > 1 and d:
        F("error", "description-duplicate", us[0], f"{lg}: on {len(us)} pages: {us[:4]}")

# ---------------------------------------------------------------- structured data
for u, r in pages.items():
    if r["status"] != 200 or r.get("jsonld") is None:
        continue
    types = []
    for block in r["jsonld"]:
        if "__error__" in block:
            F("error", "jsonld-parse", u, block["__error__"])
            continue
        items = block.get("@graph", [block]) if isinstance(block, dict) else block
        for it in items:
            t = it.get("@type")
            types.append(t)
            if "@context" not in block:
                F("error", "jsonld-context", u, str(t))
            if t == "BreadcrumbList":
                el = it.get("itemListElement", [])
                for n, e in enumerate(el, 1):
                    if e.get("position") != n:
                        F("error", "breadcrumb-position", u, f"{e.get('position')} at {n}")
                    item = e.get("item")
                    iu = item if isinstance(item, str) else (item or {}).get("@id")
                    if iu:
                        k = rel(absu(iu))
                        t2 = pages.get(k)
                        if t2 and (t2["status"] != 200 or (u in indexable and is_noindex(t2))):
                            F("error", "breadcrumb-target", u, f"{k}: {t2['status']}{' noindex' if is_noindex(t2) else ''}")
                    elif n < len(el):
                        F("error", "breadcrumb-item", u, f"position {n} without item")
                if el and u in indexable and rel(absu(el[-1].get("item") if isinstance(el[-1].get("item"), str) else (el[-1].get("item") or {}).get("@id", u))) not in (u,):
                    F("warn", "breadcrumb-last", u, "last item is not the page itself")
            if t in ("TouristDestination", "Article"):
                if rel(absu(it.get("url", ""))) != (canon_of(r) or u):
                    F("error", "schema-url", u, f"{t}.url {it.get('url')} vs canonical {canon_of(r)}")
                h1 = [h[1] for h in r.get("headings", []) if h[0] == 1]
                nm = it.get("name") or it.get("headline")
                if h1 and nm and nm.strip() != h1[0].strip():
                    F("warn", "schema-name-vs-h1", u, f"{t} '{nm}' vs H1 '{h1[0]}'")
                if it.get("inLanguage") and it["inLanguage"].split("-")[0] != r["plang"]:
                    F("error", "schema-language", u, f"inLanguage {it['inLanguage']}")
            if t == "Organization" and it.get("logo"):
                lg = fetch(absu(it["logo"]), "HEAD")
                if lg["status"] != 200:
                    F("error", "schema-logo", u, f"logo {lg['status']}")
            if t in ("Product", "Offer", "AggregateRating", "Review", "FAQPage", "Event"):
                F("error", "schema-unexpected", u, f"{t} (no owned inventory / not visible)")
    dup = [t for t, n in Counter(map(str, types)).items() if n > 1]
    if dup:
        F("error", "schema-duplicate-type", u, ",".join(dup))
    if u in indexable and not types:
        F("warn", "schema-none", u, "no structured data")

# ---------------------------------------------------------------- internal links
inlinks = defaultdict(set)
link_problems = Counter()
for u, r in pages.items():
    if u not in indexable:
        continue
    for href, relattr, text, ctx, hreflang, data_lang in r["links"]:
        if not href or href.startswith(("#", "mailto:", "tel:", "javascript:")):
            continue
        a = absu(href, B + u)
        s = urllib.parse.urlsplit(a)
        if s.netloc != HOST:
            if "sponsored" not in relattr and "/go/" not in a and s.netloc and "unsplash" not in s.netloc:
                F("info", "external-link", u, f"{a} rel='{relattr}' ({ctx})")
            continue
        k = rel(a)
        if k.startswith("/go/"):
            if "sponsored" not in relattr or "nofollow" not in relattr:
                F("error", "affiliate-rel", u, f"{k} rel='{relattr}'")
            continue
        if k.startswith(("/wp-admin", "/wp-login", "/feed", "/wp-json")):
            F("warn", "link-utility", u, k)
            continue
        kk = k.split("#")[0]
        t = pages.get(kk)
        if t is None:
            res = fetch(B + urllib.parse.quote(kk, safe="/?&=%:#+"))
            t = {"status": res["status"], "location": res["headers"].get("location"), "robots": "", "xrobots": res["headers"].get("x-robots-tag", "")}
            if res["status"] == 200 and "html" in res["headers"].get("content-type", ""):
                p = Page(); p.feed(res["body"]); t["robots"] = ",".join(p.meta.get("robots", [])); t["canon"] = p.canon
            pages.setdefault(kk, {"url": kk, "type": "discovered", "lang": lang_of_path(kk), "src": "link", **t})
        if t["status"] in (301, 302, 307, 308):
            link_problems["redirect"] += 1
            F("error", "link-redirect", u, f"→ {kk} ({t['status']} → {t.get('location')}) [{ctx}] '{text[:40]}'")
        elif t["status"] != 200:
            F("error", "link-broken", u, f"→ {kk} ({t['status']}) [{ctx}] '{text[:40]}'")
        elif is_noindex(t) and "?s=" not in kk:
            F("warn", "link-noindex", u, f"→ {kk} (noindex) [{ctx}] '{text[:40]}'")
        lp, lk = pages[u]["plang"], lang_of_path(kk)
        if lk != lp and not hreflang and not data_lang and ctx not in ("dialog",) and "lang" not in (relattr or ""):
            # the language switcher is the only intended cross-language link
            if not re.search(r"lang|switch", text.lower()) and text.strip().lower() not in ("english", "deutsch", "français", "italiano", "español", "русский", "中文", "العربية"):
                F("error", "link-wrong-language", u, f"{lp} page → {kk} ({lk}) [{ctx}] '{text[:40]}'")
        if kk in indexable and kk != u and ctx in ("main", "main?"):
            inlinks[kk].add(u)
        if kk != u and "/" + k.lstrip("/") != k:
            pass
# orphans (in-content) and click depth from each language homepage
for u in indexable:
    if u in ("/",) or re.match(r"^/[a-z]{2}/$", u):
        continue
    if not inlinks.get(u):
        F("warn", "orphan-content", u, "no in-content link from another indexable page (header/footer only)")
graph = defaultdict(set)
for u in indexable:
    for href, *_ in pages[u]["links"]:
        k = rel(absu(href or "", B + u)).split("#")[0]
        if k in indexable:
            graph[u].add(k)
depth = {}
for lg in LANGS:
    root = "/" if lg == DEF else f"/{lg}/"
    if root not in indexable:
        continue
    q = deque([(root, 0)])
    seen = {root}
    while q:
        n, d = q.popleft()
        depth[n] = min(depth.get(n, 99), d)
        for m in graph[n]:
            if m not in seen:
                seen.add(m)
                q.append((m, d + 1))
for u in indexable:
    if depth.get(u, 99) > 3:
        F("warn", "click-depth", u, f"depth {depth.get(u)}")

# ---------------------------------------------------------------- images
for u in indexable:
    for im in pages[u]["imgs"]:
        if im["alt"] is None:
            F("error", "img-alt-missing", u, f"{im['src'][:80]} [{im['ctx']}]")
        elif len(im["alt"]) > 125:
            F("warn", "img-alt-long", u, im["alt"][:80])
        if not im["w"] or not im["h"]:
            if not im["src"].startswith("data:"):
                F("info", "img-no-dimensions", u, f"{im['src'][:70]} [{im['ctx']}] class={im['cls'][:30]}")

# ---------------------------------------------------------------- robots.txt
rb = robots["body"]
if robots["status"] != 200:
    F("error", "robots", "/robots.txt", f"status {robots['status']}")
if "Disallow: /go/" not in rb:
    F("error", "robots-go", "/robots.txt", "no Disallow: /go/")
if re.search(r"Disallow:\s*/(\s|$)", rb):
    F("error", "robots-block-all", "/robots.txt", "Disallow: /")
for blocked in re.findall(r"Disallow:\s*(\S+)", rb):
    for u in indexable:
        if u.startswith(blocked):
            F("error", "robots-blocks-indexable", u, blocked)
if "Sitemap:" not in rb:
    F("warn", "robots-sitemap", "/robots.txt", "no Sitemap line")

# ---------------------------------------------------------------- report
lv = Counter(f["level"] for f in findings)
codes = Counter((f["level"], f["code"]) for f in findings)
summary = {
    "base": B, "fetched": len(pages), "inventory": sum(1 for v in urls.values() if v["src"] == "inventory"),
    "sitemap_urls": len(sm_urls), "indexable": len(indexable),
    "indexable_by_lang": dict(Counter(pages[u]["plang"] for u in indexable)),
    "indexable_by_type": dict(Counter(pages[u]["type"] for u in indexable)),
    "levels": dict(lv), "codes": {f"{a}:{b}": n for (a, b), n in sorted(codes.items())},
}
json.dump({"summary": summary, "findings": findings, "pages": {u: {k: v for k, v in r.items() if k not in ("text_main",)} for u, r in pages.items()}, "sitemap": sm_urls, "inlinks": {k: sorted(v) for k, v in inlinks.items()}, "depth": depth}, open("seo-audit.json", "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print(json.dumps(summary, ensure_ascii=False, indent=1))
for f in findings:
    if f["level"] in ("error", "warn"):
        print(f"{f['level'].upper():5} {f['code']:28} {f['url'][:70]:70} {f['msg'][:160]}")
sys.exit(1 if lv.get("error") else 0)
