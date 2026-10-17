# Owner, 17 Oct, on a guide page: "is this the best design?" and "you added photos to the template guides only".
# - every guide gets photos of its own places (guide-photos.py);
# - the booking box: only the compact trip card stays in view; the other links move under the article, so the box
#   never runs off the bottom or slides under the section bar;
# - the section bar: when the sections do not fit, its edges fade and arrows scroll it, so no word is cut mid-way.
p = 'v4-guides.py'
s = open(p, encoding='utf-8').read()
reps = [
    # photos
    ("        g.update(EXTRA.get(slug, {}))",
     "        g.update(EXTRA.get(slug, {}))\n        g.update(GP.EXTRA.get(slug, {}))"),
    ("HERE = os.path.dirname(os.path.abspath(__file__))\n",
     "HERE = os.path.dirname(os.path.abspath(__file__))\n"
     "import importlib.util as _iu\n_s = _iu.spec_from_file_location('gp', os.path.join(HERE, 'guide-photos.py')); GP = _iu.module_from_spec(_s); _s.loader.exec_module(GP)\n"
     "def _fetch_photos():\n"
     "    import urllib.request\n"
     "    for i in GP.P:\n"
     "        f = os.path.join(HERE, 'img', i + '.jpg')\n"
     "        if not os.path.exists(f):\n"
     "            urllib.request.urlretrieve('https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1100&q=62&fm=jpg' % i, f)\n"),
    ("    gs = guides()\n", "    _fetch_photos()\n    gs = guides()\n"),
    ('(fig?`<figure><img src="${IMG[fig[0]]}"', '(fig?`<figure><img src="${IMG[fig[0]]||fig[0]}"'),
    ('<img src="${IMG[p[0]]}" alt="${esc(p[1])}" loading="lazy">', '<img src="${IMG[p[0]]||p[0]}" alt="${esc(p[1])}" loading="lazy">'),
    ('lb.querySelector("img").src=IMG[p[0]];', 'lb.querySelector("img").src=IMG[p[0]]||p[0];'),
    # booking box: compact sticky card; the rest goes under the article
    ('<p class="bk__p">From ${esc(main.partners)}</p></div></div>`\n      +trips.slice(1)',
     '<p class="bk__p">From ${esc(main.partners)}</p></div></div>`;\n  const boxMore=!main?"":""\n      +trips.slice(1)'),
    ('<aside class="abox">${route&&trips.length?rbox:box}</aside></div>`',
     '${boxMore?`<div class="gmore">${boxMore}</div>`:""}</article><aside class="abox">${route&&trips.length?rbox:box}</aside></div>`'),
    ('<p class="asrc">Facts in this guide come from official and published sources, listed in our sources page. Opening hours, tickets and rules change; check on the day.</p></article>${boxMore',
     '<p class="asrc">Facts in this guide come from official and published sources, listed in our sources page. Opening hours, tickets and rules change; check on the day.</p>${boxMore'),
    # section bar: arrows when it overflows
    ('`<nav class="secbar" aria-label="In this guide"><ol>${toc}</ol></nav>',
     '`<nav class="secbar" aria-label="In this guide"><button type="button" class="sb-arr sb-arr--l" aria-label="Previous sections" data-sbarr="-1">‹</button><ol>${toc}</ol><button type="button" class="sb-arr sb-arr--r" aria-label="More sections" data-sbarr="1">›</button></nav>'),
    ('  lazyVideos($("#ga")); vp.scrollTo({top:0,behavior:"auto"});',
     '  sbFit(); lazyVideos($("#ga")); vp.scrollTo({top:0,behavior:"auto"});'),
    ('function gScroll(){',
     'function sbFit(){ const n=document.querySelector("#ga .secbar"); if(!n) return; const ol=n.querySelector("ol"); const over=ol.scrollWidth>ol.clientWidth+4; n.classList.toggle("is-over",over); n.classList.toggle("at-start",ol.scrollLeft<4); n.classList.toggle("at-end",ol.scrollLeft+ol.clientWidth>=ol.scrollWidth-4); if(!n.dataset.w){ n.dataset.w=1; ol.addEventListener("scroll",sbFit,{passive:true}); new ResizeObserver(sbFit).observe(ol); } }\nfunction gScroll(){'),
    ('  const toc=e.target.closest("[data-gtoc]");',
     '  const sa=e.target.closest("[data-sbarr]"); if(sa){ const ol=sa.parentElement.querySelector("ol"); ol.scrollBy({left:+sa.dataset.sbarr*ol.clientWidth*.7,behavior:"smooth"}); return; }\n  const toc=e.target.closest("[data-gtoc]");'),
    ('.gd .tcard,.gd .gcard{cursor:pointer}', '''.gd .tcard,.gd .gcard{cursor:pointer}
/* booking box: one compact card stays in view */
.gd .abox{position:static!important;align-self:stretch!important}
.gd .abox > .bk{position:sticky;top:calc(var(--navh,72px) + 80px)}
.gd .bk__img{height:120px}
.gd .gmore{display:grid;gap:10px;margin:34px 0 0;padding-top:24px;border-top:1px solid var(--line)}
.gd .gmore .bk__also,.gd .gmore .bk__stay{margin:0}
.gd .gmore .bk__disc{margin:0}
@container site (max-width:900px){ .gd .abox > .bk{position:static} }
/* section bar: fade and arrows only when the sections do not fit */
.gd .secbar{display:flex;align-items:stretch}
.gd .secbar ol{flex:1;min-width:0;scroll-behavior:smooth}
.gd .sb-arr{display:none;flex:none;width:40px;border:0;background:transparent;color:var(--charcoal);font:400 26px/1 var(--serif);cursor:pointer}
.gd .sb-arr:hover{color:var(--gold-deep)}
.gd .secbar.is-over .sb-arr{display:block}
.gd .secbar.is-over.at-start .sb-arr--l,.gd .secbar.is-over.at-end .sb-arr--r{visibility:hidden}
.gd .secbar.is-over ol{-webkit-mask-image:linear-gradient(90deg,transparent 0,#000 28px,#000 calc(100% - 28px),transparent 100%);mask-image:linear-gradient(90deg,transparent 0,#000 28px,#000 calc(100% - 28px),transparent 100%)}
.gd .secbar.is-over.at-start ol{-webkit-mask-image:linear-gradient(90deg,#000 calc(100% - 28px),transparent 100%);mask-image:linear-gradient(90deg,#000 calc(100% - 28px),transparent 100%)}
.gd .secbar.is-over.at-end ol{-webkit-mask-image:linear-gradient(90deg,transparent 0,#000 28px);mask-image:linear-gradient(90deg,transparent 0,#000 28px)}
@container site (max-width:600px){ .gd .sb-arr{width:32px} }'''),
]
for a, b in reps:
    assert s.count(a) == 1, a[:70]
    s = s.replace(a, b)
# move "also", hotels and the disclosure out of the sticky box
s = s.replace("const boxMore=!main?\"\":\"\"\n      +trips.slice(1)", "const boxMore=!main?\"\":\"\"\n      +trips.slice(1)", 1)
i = s.index('const boxMore=!main?"":""')
j = s.index('    :`<div class="bk"><div class="bk__b"><small>Plan with us</small>', i)
more = s[i:j].rstrip()
s = s[:i] + more + ';\n  const box=main?box0\n' + s[j:]
s = s.replace('  const box=main?`<div class="bk"><div class="bk__img">', '  const box0=`<div class="bk"><div class="bk__img">', 1)
open(p, 'w', encoding='utf-8', newline='\n').write(s)
print('ok')
