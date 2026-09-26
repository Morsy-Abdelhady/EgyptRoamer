import json, re, sys, urllib.request, html
import os
B=os.environ.get("BASE","http://127.0.0.1:8080")
# usage: python3 index-gate.py '{"tour":"<url>",...}' A|B   (A = Ready to index off, B = on)
def get(u):
    try:
        r=urllib.request.urlopen(urllib.request.Request(u, headers={"User-Agent":"qa"}))
        return r.status, r.read().decode()
    except urllib.error.HTTPError as e: return e.code, e.read().decode()
def sitemap_urls():
    _, idx = get(B+"/wp-sitemap.xml"); urls=set()
    for sm in re.findall(r"<loc>([^<]+)</loc>", idx):
        _, x = get(sm); urls |= set(re.findall(r"<loc>([^<]+)</loc>", x))
    return urls
sm = sitemap_urls()
items = json.loads(sys.argv[1]); state = sys.argv[2]
for name, url in items.items():
    st, h = get(url)
    g = lambda re_: re.findall(re_, h)
    robots = (g(r"<meta name='robots' content='([^']+)'") or ["—"])[0]
    canon = (g(r'<link rel="canonical" href="([^"]+)"') or ["—"])[0]
    title = html.unescape((g(r"<title>([^<]*)</title>") or ["—"])[0])
    desc = (g(r'<meta name="description" content="([^"]*)"') or ["—"])[0][:30]
    h1 = len(g(r"<h1[\s>]")); ld = []
    for j in g(r'<script type="application/ld\+json">(.*?)</script>'):
        try: ld.append(json.loads(j)["@type"])
        except Exception: ld.append("INVALID")
    insm = url in sm
    noindex = "noindex" in robots
    ok = (noindex and not insm) if state=="A" else (not noindex and insm)
    ok = ok and canon==url and h1==1 and st==200 and "INVALID" not in ld
    print(f"{state} {name:12} {'PASS' if ok else 'FAIL'} status={st} robots={robots:24} sitemap={'yes' if insm else 'no '} canonical={'ok' if canon==url else canon} h1={h1} title='{title[:40]}' desc='{desc}' ld={ld}")
print("sitemap URL count:", len(sm)); print("\n".join(sorted(sm)))
