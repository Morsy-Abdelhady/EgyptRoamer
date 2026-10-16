# Owner, 17 Oct: "merge the article template into the main template". The Travel Guide is now part of proposal 4:
# - a Travel Guide page listing every guide production publishes (24, with production's own titles and photos),
#   filterable by the seven places;
# - every guide opens in the approved article template, with its real text (content/editorial/en), its trips
#   from proposal 4's own trip data (facts, options, partners, free cancellation), saved with the same ♡, and links
#   that move inside the prototype (trips, places, other guides).
# The article template's CSS is the approved one, scoped under .gd so it cannot change any other screen.
import json, os, re, html, base64

HERE = os.path.dirname(os.path.abspath(__file__))
REPO = r'C:\Users\morsy\Desktop\EgyptRoamer'
ED = os.path.join(REPO, 'content', 'editorial', 'en')
IDX = json.load(open(os.path.join(HERE, 'live', 'guides.json'), encoding='utf-8'))

EXP = {'valley-of-the-kings-hatshepsut-temple': 'valley', 'ras-mohammed-snorkelling-from-sharm': 'ras',
       'red-sea-diving-two-reef-dives': 'redsea', 'white-desert-black-desert-safari': 'white',
       'nile-dinner-cruise-with-live-show': 'nile', 'pyramids-of-giza-sphinx-private-tour': 'giza',
       'abu-simbel-day-trip-from-aswan': 'abu', 'alexandria-day-trip-from-cairo': 'alex',
       'great-sand-sea-4-4-hot-springs': 'siwa', 'cairo-street-food-tour-by-night': 'food'}
REL = {'exp-valley': 'valley', 'exp-abu': 'abu', 'exp-giza': 'giza', 'exp-dive': 'redsea', 'exp-ras-mohammed': 'ras',
       'exp-safari': 'white', 'exp-siwa': 'siwa', 'exp-dinner': 'nile', 'exp-food': 'food', 'exp-alexandria': 'alex'}
NOSLUG = {'g-7-days.md': '7-days-in-egypt-itinerary', 'g-best-time.md': 'best-time-to-visit-egypt',
          'g-cairo.md': 'cairo-travel-guide', 'g-gems.md': 'hidden-gems-in-egypt'}

def md_inline(s):
    s = html.escape(s, quote=False)
    s = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', s)
    s = re.sub(r'(?<![\w*])\*(?!\s)(.+?)\*(?!\w)', r'<em>\1</em>', s)
    def link(m):
        text, url = m.group(1), m.group(2)
        exp = re.match(r'/experiences/([^/]+)/', url)
        trip = f' class="tlink" data-trip="{EXP[exp.group(1)]}"' if exp and exp.group(1) in EXP else ''
        href = url if url.startswith('http') else 'https://egyptroamer.com' + url
        return f'<a href="#"{trip} data-href="{html.escape(href)}">{text}</a>'
    return re.sub(r'\[([^\]]+)\]\(([^)]+)\)', link, s)

def parse(path):
    raw = open(path, encoding='utf-8').read().replace('\r\n', '\n')
    fm, body = re.match(r'---\n(.*?)\n---\n(.*)', raw, re.S).groups()
    meta = dict(re.findall(r'^(\w+): (.*)$', fm, re.M))
    out, toc, faq, words = [], [], [], 0
    for b in re.split(r'\n\s*\n', body.strip()):
        words += len(re.findall(r'\w+', b))
        if b.strip() == '[[toc]]':
            continue
        m = re.match(r'## (.+?) \{#([\w-]+)\}$', b)
        if m:
            if m.group(2) != 'faq':
                toc.append((m.group(2), m.group(1))); out.append(['h2', m.group(2), m.group(1)])
            continue
        if b.startswith('?? '):
            q, a = b[3:].split('\n', 1); faq.append([q.strip(), md_inline(a.strip())]); continue
        if b.startswith('> '):
            lines = [l[2:] if l.startswith('> ') else l.lstrip('>') for l in b.split('\n')]
            t = re.match(r'\*\*(.+)\*\*', lines[0]); title = t.group(1) if t else ''
            rest = lines[1:] if t else lines
            inner = ('<ul>' + ''.join(f'<li>{md_inline(l[2:])}</li>' for l in rest) + '</ul>') if rest and rest[0].startswith('- ') else f'<p>{md_inline(" ".join(rest))}</p>'
            out.append(['tip', title, inner]); continue
        if b.startswith('- '):
            out.append(['ul', [md_inline(l[2:]) for l in b.split('\n') if l.startswith('- ')]]); continue
        if re.match(r'^\d+\. ', b):
            out.append(['ul', [md_inline(re.sub(r'^\d+\. ', '', l)) for l in b.split('\n') if re.match(r'^\d+\. ', l)]]); continue
        if b.startswith('### '):
            out.append(['h3', md_inline(b[4:].strip())]); continue
        out.append(['p', md_inline(b.replace('\n', ' '))])
    return meta, out, toc, faq, words

# richer media for the two guides first built with the template (approved 16–17 Oct)
EXTRA = {
    'valley-of-the-kings-tombs-and-tickets': dict(hero=['img', 'kv62', 'KV62, the tomb of Tutankhamun, Valley of the Kings · Photo: Unsplash'],
        strips={'west-bank': ['See the places', [['hat_l', 'Hatshepsut’s temple, Deir el-Bahari', 'Mahmoud Refaat'], ['habu1', 'Medinet Habu, the painted gateway', 'Siddhesh Mangela'], ['habu2', 'Medinet Habu, the carved columns', 'Fatih Beki']]]}),
    'snorkelling-hurghada-or-sharm': dict(hero=['video', 'ras_turq', 'Red Sea, Egypt · Video: Adrien JACTA, Pexels', 'video/redsea-reef.mp4'],
        figs={'sharm': ['d_sharm', 'A bay at Sharm El Sheikh · Photo: Unsplash']},
        strips={'hurghada': ['On Hurghada’s reefs', [['reef_school', 'Small orange fish over a coral reef, Hurghada', 'Oliver Schwaneberg'], ['reef_clown2', 'Two clownfish in a sea anemone, Hurghada', 'Oliver Schwaneberg'], ['reef_yellow', 'Yellow fish on a coral reef, Hurghada', 'Oliver Schwaneberg'], ['reef_puffer', 'A small pufferfish by the reef, Hurghada', 'Oliver Schwaneberg']]]},
        videos={'boat-or-shore': ['video/redsea-coral.mp4', 'reef_coral', 'Red Sea · Video: Adrien JACTA, Pexels']}),
}

def guides():
    by_slug = {}
    for f in sorted(os.listdir(ED)):
        if not f.startswith('g-'):
            continue
        meta, blocks, toc, faq, words = parse(os.path.join(ED, f))
        slug = meta.get('slug') or NOSLUG.get(f)
        by_slug[slug] = (meta, blocks, toc, faq, words)
    out = []
    for c in IDX['cards']:
        slug = c['href'].rstrip('/').split('/')[-1]
        if slug not in by_slug:
            continue
        meta, blocks, toc, faq, words = by_slug[slug]
        dests = [d.strip() for d in meta.get('destination', '').split(',') if d.strip()]
        trips = [REL[r.strip()] for r in meta.get('related', '').split(',') if r.strip() in REL]
        g = dict(slug=slug, title=c['title'], excerpt=meta.get('excerpt', c['excerpt']), topic=meta.get('topic', 'Planning'),
                 dests=dests, trips=trips, photo=f"img/{c['photo']}.jpg" if c['photo'] else '', mins=max(1, round(words / 220)),
                 blocks=blocks, toc=toc, faq=faq)
        g.update(EXTRA.get(slug, {}))
        out.append(g)
    return out

def article_images():
    # the reef and Medinet Habu photos (exact tags, 16 Oct) become files next to the page
    art = json.load(open(os.path.join(HERE, 'article', 'imgs-art.json')))
    os.makedirs(os.path.join(HERE, 'img'), exist_ok=True)
    m = {}
    for k in ('habu1', 'habu2', 'reef_school', 'reef_clown2', 'reef_yellow', 'reef_puffer', 'reef_coral'):
        p = f'img/a_{k}.jpg'
        open(os.path.join(HERE, p), 'wb').write(base64.b64decode(art[k].split(',', 1)[1]))
        m[k] = p
    return m

def scope_css(css, prefix, drop):
    """Prefix every selector with `prefix`; drop rules whose selectors all match `drop`."""
    out, i = [], 0
    while i < len(css):
        j = css.find('{', i)
        if j < 0:
            break
        head = css[i:j].strip()
        depth, k = 1, j + 1
        while depth:
            depth += {'{': 1, '}': -1}.get(css[k], 0); k += 1
        body = css[j + 1:k - 1]
        head = re.sub(r'/\*.*?\*/', '', head, flags=re.S).strip()
        if head.startswith('@media') or head.startswith('@container') or head.startswith('@supports'):
            inner = scope_css(body, prefix, drop)
            if inner.strip():
                out.append(f'{head}{{{inner}}}')
        elif head.startswith('@'):
            out.append(f'{head}{{{body}}}')
        else:
            sels = [s.strip() for s in head.split(',') if s.strip() and not re.match(drop, s.strip())]
            if sels:
                out.append(','.join(f'{prefix} {s}' for s in sels) + f'{{{body}}}')
        i = k
    return '\n'.join(out)

def css():
    s = open(os.path.join(HERE, 'article', 'article.template.pre-chrome.html'), encoding='utf-8').read()
    c = s[s.index('<style>') + 7:s.index('</style>')]
    tokens = re.search(r':root\{(.*?)\}', c, re.S).group(1).replace('color-scheme:light;', '')
    c = c[c.index('/* site */'):]
    drop = r'^(\.hdr|\.logo|\.vswitch|\.prog|\.ftr|\.devsw|\.stage|\.browser|\.viewport|#vp|\.notes|\.mtoc|\.pbar|\.steps|\.hdr__menu)\b'
    return ('\n/* ---------- Travel Guide: the approved article template, scoped to the guide screens ---------- */\n'
            f'.gd{{{tokens}}}\n' + scope_css(c, '.gd', drop) + '''
.gd .ahero .wrap{padding-top:64px}
.gd .secbar{top:var(--navh,72px)!important}
.gd .abox{top:calc(var(--navh,72px) + 70px)!important}
.gd .prose h2{scroll-margin-top:calc(var(--navh,72px) + 64px)!important}
.gd .prose h3{font:600 21px/1.3 var(--serif);margin:28px 0 10px}
.gd .tcard,.gd .gcard{cursor:pointer}
.gd .rtl{list-style:none;margin:6px 0 14px;padding:0;display:grid;gap:8px}
.gd .rtl li{margin:0;padding:0}.gd .rtl li::before{content:none}
.gd .rtl a{display:grid;grid-template-columns:64px minmax(0,1fr);gap:12px;align-items:center;padding:8px;border:1px solid var(--line);border-radius:12px;text-decoration:none;color:var(--charcoal);background:#fff;min-height:44px}
.gd .rtl a:hover{border-color:var(--gold)}
.gd .rtl img{width:64px;height:52px;object-fit:cover;border-radius:8px}
.gd .rtl b{display:block;font:600 14.5px/1.25 var(--serif)}
.gd .rtl span span{display:block;font-size:12px;color:var(--muted);margin-top:2px}
.gd .rtl em{display:block;font-style:normal;font-size:11.5px;font-weight:600;color:#145c4c;margin-top:2px}
.gd .rtl--dark a{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.16);color:#fff}
.gd .rtl--dark span span{color:#d9d0bd}.gd .rtl--dark em{color:#9fe3d6}
.gd .rt-end{align-items:start}
.gd-lb{position:absolute;inset:0;z-index:80;pointer-events:auto}
/* the guide index */
.gi-hero{position:relative;color:#fff;min-height:360px;display:flex;align-items:flex-end;overflow:hidden;background:#101820}
.gi-hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.55}
.gi-hero .wrap{position:relative;width:100%;padding-block:120px 34px}
.gi-hero h1{font-size:56px;line-height:1;margin:8px 0 12px}
.gi-hero p{max-width:640px;margin:0;font-size:16.5px;opacity:.9}
.gi-bar{position:sticky;top:var(--navh,72px);z-index:12;background:rgba(248,245,237,.97);border-bottom:1px solid var(--line);backdrop-filter:blur(8px)}
.gi-bar .wrap{display:flex;align-items:center;gap:8px;overflow-x:auto;padding-block:12px;scrollbar-width:none}
.gi-bar .wrap::-webkit-scrollbar{display:none}
.gi-bar span{font:600 11px var(--sans);letter-spacing:.18em;text-transform:uppercase;color:var(--muted);margin-right:6px;flex:none}
.gi-bar button{flex:none;min-height:40px}
.gi-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px;padding-block:30px 70px}
.gi-card{all:unset;box-sizing:border-box;cursor:pointer;background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .2s,transform .2s}
.gi-card:hover,.gi-card:focus-visible{box-shadow:0 14px 34px rgba(16,24,32,.1);transform:translateY(-2px);outline:none}
.gi-card:focus-visible{box-shadow:0 0 0 2px var(--gold)}
.gi-card img{width:100%;aspect-ratio:16/10;object-fit:cover;display:block}
.gi-card div{padding:16px 18px 18px;display:flex;flex-direction:column;gap:6px;flex:1}
.gi-card small{font:600 11px var(--sans);letter-spacing:.16em;text-transform:uppercase;color:var(--gold-deep,#a8871f)}
.gi-card b{font:600 20px/1.25 var(--serif);color:var(--charcoal)}
.gi-card p{margin:0;color:var(--muted);font-size:14.5px;line-height:1.55;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.gi-card em{font-style:normal;margin-top:auto;padding-top:8px;font-size:13px;color:var(--muted)}
@container site (max-width:900px){ .gi-grid{grid-template-columns:repeat(2,minmax(0,1fr))} }
@container site (max-width:600px){ .gi-grid{grid-template-columns:1fr;gap:14px} .gi-hero h1{font-size:38px} .gi-hero .wrap{padding-block:96px 26px} .gi-bar button{min-height:44px} }
''')

JS = r'''
// ---------- Travel Guide (merged into the main prototype, 17 Oct) ----------
const GUIDES=__GUIDES__;
const GIDX=__GIDX__;
Object.assign(IMG,__ARTIMG__);
const GLYPHS="𓀀𓁐𓂀𓃒𓄿𓅓𓆣𓇋𓈖𓉐𓊪𓋴𓌳𓍯𓎛𓏏𓐍𓇳𓋹𓊽𓂻𓁹𓆛𓈗";
const DNAME=id=>DESTS[id]?DESTS[id].name:"";
const gPlace=g=>g.dests.length===1?DNAME(g.dests[0]):g.dests.length===2?g.dests.map(DNAME).join(" & "):"Egypt";
const gPhoto=g=>g.photo||IMG["d_"+(g.dests[0]||"cairo")]||IMG.sc_nile;
function tripInfo(id){ const W=WORLDS[id], t=TRIPS.find(x=>x.id===id); if(!W||!t) return null;
  const partners=[...new Set(W.opts.map(o=>o.p))];
  return {id,W,t,title:W.title,img:IMG[t.k]||IMG[W.photo],place:(t.places||[]).join(" · ")||W.place,facts:W.sum.slice(1).filter(f=>!/cancellation/i.test(f)),n:W.opts.length,partners:partners.join(" · "),go:W.go,opts:W.opts,world:W.fx==="tomb"?"tomb":W.fx==="sea"?"sea":"sand"}; }
const gchips=T=>`<ul class="chips">${T.facts.map(f=>`<li>${esc(f)}</li>`).join("")}<li class="fc" title="Free cancellation up to 24 hours before, on every option (the partners' own terms)">✓ Free cancellation</li></ul>`;
const openTrip=(id,book)=>{ current=id; lastViewed=id; go(3); if(book) setTimeout(()=>{ const c=document.getElementById("wchoose"); c&&vp.scrollTo({top:vp.scrollTop+c.getBoundingClientRect().top-vp.getBoundingClientRect().top-60,behavior:"auto"}); },80); };
let gFilter="all";
function showOnly(screen){ $$("[data-screen]").forEach(x=>x.hidden=x.dataset.screen!==screen); $$(".steps button").forEach(b=>b.removeAttribute("aria-current"));
  document.querySelectorAll('#vp .nav__links a').forEach(a=>a.toggleAttribute("aria-current",/\/guides\//.test(a.dataset.href||""))); $("#nav-exp")&&$("#nav-exp").removeAttribute("aria-current");
  $("#hdr-cta").innerHTML='Choose a trip <span class="arr">→</span>'; $("#hdr-cta").dataset.go="2"; $("#hdr-cta").dataset.wjump=""; }
window.openGuides=function(place){ if(place!==undefined) gFilter=place; showOnly("g"); $("#url").textContent="egyptroamer.com/guides/";
  const list=GUIDES.filter(g=>gFilter==="all"||g.dests.includes(gFilter));
  $("#gi").innerHTML=`<div class="gi-hero"><img src="${IMG.sc_nile}" alt=""><div class="wrap"><p class="eyebrow">Travel Guide</p><h1>Plan it well. <i>Then go.</i></h1><p>${GUIDES.length} guides to Egypt's places, trips and seasons, written from published sources. Each one links to the trips it talks about.</p></div></div>
    <div class="gi-bar"><div class="wrap"><span>Where</span><button type="button" class="chip" data-gfilter="all" aria-pressed="${gFilter==="all"}">All</button>${Object.entries(DESTS).map(([id,d])=>`<button type="button" class="chip" data-gfilter="${id}" aria-pressed="${gFilter===id}">${esc(d.name)}</button>`).join("")}</div></div>
    <div class="wrap gi-grid">${list.map(g=>`<button type="button" class="gi-card" data-guide-open="${g.slug}"><img src="${gPhoto(g)}" alt="" loading="lazy"><div><small>${esc(gPlace(g))} · ${esc(g.topic)}</small><b>${esc(g.title)}</b><p>${esc(g.excerpt)}</p><em>${g.mins} min read${g.trips.length?` · ${g.trips.length} ${g.trips.length===1?"trip":"trips"} inside`:""}</em></div></button>`).join("")}</div>`;
  vp.scrollTo({top:0,behavior:"auto"}); window.lcPaint&&lcPaint(); };
let gLB=[], gLbi=0, gLast=null, gMain=null;
window.openGuide=function(slug){ const A=GUIDES.find(g=>g.slug===slug); if(!A){ openGuides(); return; } gLast=null;
  showOnly("ga"); $("#url").textContent=`egyptroamer.com/guides/${A.slug}/`;
  const trips=A.trips.map(tripInfo).filter(Boolean);
  // itineraries and whole-country guides name several trips: they show the trips on the route, not one trip (expert review, 17 Oct)
  const route=/days-in-egypt|best-time|hidden-gems|day-trips/.test(A.slug)||trips.length>=3;
  const main=route?null:trips[0]; gMain=main;
  const h=A.hero||["img",null,""]; const heroSrc=h[1]?IMG[h[1]]:gPhoto(A);
  const hero=h[0]==="video"?`<video muted loop playsinline preload="none" poster="${heroSrc}" data-src="${h[3]}"></video>`:`<img src="${heroSrc}" alt="">`;
  let n=0, shown=new Set(), pStrip=null, pVid=null; gLB=[];
  const body=A.blocks.map(b=>{
    if(b[0]==="h2"){ n++; const fig=(A.figs||{})[b[1]]; pStrip=(A.strips||{})[b[1]]; pVid=(A.videos||{})[b[1]];
      return `<h2 id="g-${b[1]}"><small>${String(n).padStart(2,"0")}</small>${esc(b[2])}</h2>`+(fig?`<figure><img src="${IMG[fig[0]]}" alt="" loading="lazy"><figcaption>${esc(fig[1])}</figcaption></figure>`:""); }
    if(b[0]==="h3") return `<h3>${b[1]}</h3>`;
    if(b[0]==="tip") return `<div class="tip" data-g="${main&&main.world==="tomb"?"𓁹":"𓈗"}">${b[1]?`<b>${esc(b[1])}</b>`:""}${b[2]}</div>`;
    let x=b[0]==="ul"?`<ul>${b[1].map(i=>`<li>${i}</li>`).join("")}</ul>`:`<p>${b[1]}</p>`;
    [...x.matchAll(/data-trip="(\w+)"/g)].map(m=>m[1]).filter(k=>!shown.has(k)).forEach(k=>{ shown.add(k); const T=tripInfo(k); if(!T) return;
      x+=main&&k===main.id?`<a class="tcard tcard--slim" href="#" data-gtrip="${k}"><div><small>The trip in this guide</small><h4>${esc(T.title)}</h4></div><span class="btn btn--gold btn--sm">See the ${T.n} options →</span></a>`
        :`<a class="tcard" href="#" data-gtrip="${k}"><img src="${T.img}" alt=""><div><small>The trip · ${esc(T.place)}</small><h4>${esc(T.title)}</h4>${gchips(T)}<span class="go">See the ${T.n} options →</span></div></a>`; });
    if(pStrip){ const [t,ph]=pStrip; pStrip=null; const base=gLB.length; gLB=gLB.concat(ph);
      x+=`<div class="strip"><p class="strip__t">${esc(t)}</p><div class="strip__g" style="--n:${ph.length}">${ph.map((p,i)=>`<button type="button" data-glb="${base+i}" aria-label="Open photo: ${esc(p[1])}"><img src="${IMG[p[0]]}" alt="${esc(p[1])}" loading="lazy"><span>${esc(p[1])}</span></button>`).join("")}</div><p class="strip__c">Photos: ${esc([...new Set(ph.map(p=>p[2]))].join(", "))}, Unsplash · Open a photo to see it large</p></div>`; }
    if(pVid){ const [src,poster,cap]=pVid; pVid=null; x+=`<figure class="ivid"><video muted loop playsinline preload="none" poster="${IMG[poster]}" data-src="${src}"></video><figcaption>${esc(cap)}</figcaption></figure>`; }
    return x; }).join("");
  const toc=A.toc.map(([id,t])=>`<li><a href="#" data-gtoc="g-${id}">${esc(t)}</a></li>`).join("")+(A.faq.length?`<li><a href="#" data-gtoc="g-faq">Questions</a></li>`:"");
  const faq=A.faq.length?`<section class="afaq"><h2 id="g-faq"><small>${String(n+1).padStart(2,"0")}</small>Frequently asked questions</h2>${A.faq.map(([q,a],i)=>`<details ${i?"":"open"}><summary>${esc(q)}</summary><p>${a}</p></details>`).join("")}</section>`:"";
  const fav=main&&window.lcFavBtn?lcFavBtn({id:"trip:"+main.id,title:main.title,meta:`${main.place} · ${main.facts[0]||""} · ${main.n} options`,img:main.img},true):"";
  const stay=main&&main.W.stay?main.W.stay:null;
  const box=main?`<div class="bk"><div class="bk__img"><img src="${main.img}" alt=""><span>The trip for this guide</span></div><div class="bk__b"><small>${esc(main.place)}</small><h3>${esc(main.title)}</h3>${gchips(main)}<a class="btn btn--gold" href="#" data-gtrip="${main.id}" data-gbook>See the ${main.n} options →</a><div style="display:flex;justify-content:center;margin-top:10px">${fav}</div><p class="bk__p">From ${esc(main.partners)}</p></div></div>`
      +trips.slice(1).map(T=>`<a class="bk__also" href="#" data-gtrip="${T.id}"><small>Also in this guide</small><b>${esc(T.title)}</b> →</a>`).join("")
      +(stay?`<a class="bk__stay" href="#" data-gstay><span>${esc(stay.title)}<small>${esc(stay.rows[0][0])}</small></span><em>Trip.com ↗</em></a>`:"")
      +`<p class="bk__disc">Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you.</p>`
    :`<div class="bk"><div class="bk__b"><small>Plan with us</small><h3>Find the trips for this plan</h3><p style="margin:0 0 12px;color:var(--muted);font-size:14px">Tours and day trips across Egypt, each with its options side by side.</p><a class="btn btn--gold" href="#" data-go="2">Choose a trip →</a></div></div>`;
  const rcard=T=>`<li><a href="#" data-gtrip="${T.id}"><img src="${T.img}" alt=""><span><b>${esc(T.title)}</b><span>${esc(T.place)} · ${esc(T.facts[0]||"")} · ${T.n} options</span><em>✓ Free cancellation</em></span></a></li>`;
  const rbox=`<div class="bk" id="gbox"><div class="bk__b"><small>Trips on this route</small><h3>${trips.length} trips for this plan</h3><ul class="rtl">${trips.map(rcard).join("")}</ul><a class="btn btn--gold" href="#" data-go="2">See all trips →</a></div></div><p class="bk__disc">Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you.</p>`;
  const rend=`<section class="aend aend--tomb"><div class="glyphs" aria-hidden="true">${GLYPHS.repeat(30)}</div><div class="wrap rt-end"><div><p class="eyebrow">Ready to go?</p><h2>Trips on this route</h2><p>Each trip has its own page, where its options are compared side by side. Free cancellation up to 24 hours before, on every option.</p></div><ul class="rtl rtl--dark">${trips.map(rcard).join("")}</ul></div></section>`;
  const end=route&&trips.length?rend:main?`<section class="aend aend--${main.world==="tomb"?"tomb":"sea"}">${main.world==="tomb"?`<div class="glyphs" aria-hidden="true">${GLYPHS.repeat(30)}</div>`:""}<div class="wrap"><div><p class="eyebrow">Ready to go?</p><h2>${esc(main.title)}</h2>${gchips(main)}<p>Compare the options side by side on the trip page, then book on the partner’s site. Free cancellation up to 24 hours before, on every option.</p><div class="acts"><a class="btn btn--gold" href="#" data-gtrip="${main.id}">${esc(main.go)} →</a><a class="btn btn--light" href="#" data-place="${esc(main.t.places[0]||"")}">All ${esc(main.t.places[0]||"")} trips</a></div></div><div><p class="aopts__t">${main.n} ways to do it</p><ul class="aopts">${main.opts.map(o=>`<li><div><b>${esc(o.n)}</b><small><i>${esc(o.p)}</i>${o.f.map(esc).join(" · ")}</small></div><a class="btn btn--line btn--sm" href="#" data-gtrip="${main.id}" data-gbook>Book ↗</a></li>`).join("")}</ul></div></div></section>`:"";
  const more=GUIDES.filter(g=>g!==A&&g.dests.some(d=>A.dests.includes(d))).slice(0,3);
  const moreH=more.length?`<section class="amore"><div class="wrap"><p class="eyebrow" style="color:var(--gold-deep)">Keep reading</p><h2>More on ${esc(gPlace(A))}</h2><div class="amore__g">${more.map(g=>`<a class="gcard" href="#" data-guide-open="${g.slug}"><img src="${gPhoto(g)}" alt="" loading="lazy"><div><small>Guide · ${g.mins} min read</small><h3>${esc(g.title)}</h3><p>${esc(g.excerpt)}</p></div></a>`).join("")}</div></div></section>`:"";
  $("#ga").innerHTML=`<section class="ahero">${hero}<div class="wrap"><p class="crumb"><a href="#" data-guide-index>Travel Guide</a><span>›</span><a href="#" data-guide-index="${A.dests.length===1?A.dests[0]:"all"}">${esc(gPlace(A))}</a></p><p class="eyebrow">${esc(A.topic)} guide</p><h1>${esc(A.title)}</h1><p class="lead">${esc(A.excerpt)}</p><div class="ameta"><span>${A.mins} min read</span><span>${A.toc.length} sections</span><span>Sources listed</span></div></div>${h[2]?`<span class="credit">${esc(h[2])}</span>`:""}</section>`
    +`<nav class="secbar" aria-label="In this guide"><ol>${toc}</ol></nav><div class="abody"><article class="prose">${body}${faq}<p class="asrc">Facts in this guide come from official and published sources, listed in our sources page. Opening hours, tickets and rules change; check on the day.</p></article><aside class="abox">${route&&trips.length?rbox:box}</aside></div>`+end+moreH
    +(route&&trips.length?`<div class="mbar" id="gmbar"><div><small>✓ Free cancellation on every trip</small><b>${trips.length} trips on this route</b></div><a class="btn btn--gold btn--sm" href="#" data-gtoc="gbox">See the trips</a></div>`:"")
    +(main?`<div class="mbar" id="gmbar"><div><small>✓ Free cancellation</small><b>${esc(main.title)}</b></div><a class="btn btn--gold btn--sm" href="#" data-gtrip="${main.id}" data-gbook>See ${main.n} options</a></div>`:"");
  lazyVideos($("#ga")); vp.scrollTo({top:0,behavior:"auto"}); window.lcPaint&&lcPaint(); window.lcFavPaint&&lcFavPaint(); gScroll(); };
function gScroll(){ const s=document.querySelector('[data-screen="ga"]'); if(!s||s.hidden) return;
  const vr=vp.getBoundingClientRect(), lim=vr.top+vp.clientHeight*.35; let cur=null;
  s.querySelectorAll(".prose h2[id]").forEach(h=>{ if(h.getBoundingClientRect().top<lim) cur=h.id; });
  s.querySelectorAll("[data-gtoc]").forEach(a=>a.classList.toggle("on",a.dataset.gtoc===cur));
  const on=s.querySelector(".secbar a.on"); if(on&&cur!==gLast){ gLast=cur; on.closest("ol").scrollTo({left:on.offsetLeft-40,behavior:"auto"}); }
  const bar=s.querySelector("#gmbar"); if(bar){ const seen=[...s.querySelectorAll(".aend,.bk,.tcard--slim")].some(x=>{ const r=x.getBoundingClientRect(); return r.top<vr.bottom-72&&r.bottom>vr.top+56; }); bar.classList.toggle("is-on",vp.scrollTop>420&&!seen); } }
vp.addEventListener("scroll",gScroll,{passive:true});
function gLbShow(i){ gLbi=(i+gLB.length)%gLB.length; const p=gLB[gLbi]; const lb=document.getElementById("gd-lb"); lb.querySelector("img").src=IMG[p[0]]; lb.querySelector("img").alt=p[1]; lb.querySelector("p").innerHTML=`${esc(p[1])}<small>Photo: ${esc(p[2])}, Unsplash · ${gLbi+1} of ${gLB.length}</small>`; }
document.addEventListener("click",e=>{
  const go_=e.target.closest("[data-guide-open]"); if(go_){ e.preventDefault(); openGuide(go_.dataset.guideOpen); return; }
  const gi=e.target.closest("[data-guide-index]"); if(gi){ e.preventDefault(); openGuides(gi.dataset.guideIndex||"all"); return; }
  const gf=e.target.closest("[data-gfilter]"); if(gf){ openGuides(gf.dataset.gfilter); return; }
  const gt=e.target.closest("[data-gtrip]"); if(gt&&!e.target.closest("[data-fav]")){ e.preventDefault(); e.stopPropagation(); openTrip(gt.dataset.gtrip, gt.hasAttribute("data-gbook")); return; }
  const tl=e.target.closest("#ga a.tlink[data-trip]"); if(tl){ e.preventDefault(); e.stopPropagation(); openTrip(tl.dataset.trip); return; }
  if(e.target.closest("[data-gstay]")){ e.preventDefault(); lcToast("Prototype: on the site this opens Trip.com hotels in a new tab."); return; }
  const toc=e.target.closest("[data-gtoc]"); if(toc){ e.preventDefault(); const t=document.getElementById(toc.dataset.gtoc); t&&vp.scrollTo({top:vp.scrollTop+t.getBoundingClientRect().top-vp.getBoundingClientRect().top-(parseInt(getComputedStyle(vp).getPropertyValue("--navh"))||72)-60,behavior:"smooth"}); return; }
  const lb=e.target.closest("[data-glb]"); if(lb){ gLbShow(+lb.dataset.glb); document.getElementById("gd-lb").hidden=false; document.querySelector("#gd-lb .lb__x").focus(); return; }
  if(e.target.closest("#gd-lb .lb__x")||e.target.id==="gd-lb"){ document.getElementById("gd-lb").hidden=true; return; }
  if(e.target.closest("#gd-lb .lb__p")){ gLbShow(gLbi-1); return; } if(e.target.closest("#gd-lb .lb__n")){ gLbShow(gLbi+1); return; }
}, true);
document.addEventListener("keydown",e=>{ const lb=document.getElementById("gd-lb"); if(!lb||lb.hidden) return; if(e.key==="Escape") lb.hidden=true; if(e.key==="ArrowRight") gLbShow(gLbi+1); if(e.key==="ArrowLeft") gLbShow(gLbi-1); });
'''

def apply(tpl):
    gs = guides()
    assert len(gs) >= 20, len(gs)
    a = tpl.index('</style>'); tpl = tpl[:a] + css() + tpl[a:]
    a = '      <section data-screen="info" hidden>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '      <section data-screen="g" hidden><div id="gi" class="gd"></div></section>\n      <section data-screen="ga" hidden><div id="ga" class="gd"></div></section>\n' + a)
    a = '<div class="lc-toast" id="lc-toast" hidden></div></div>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '<div class="lc-toast" id="lc-toast" hidden></div><div class="gd"><div class="lb gd-lb" id="gd-lb" hidden role="dialog" aria-modal="true" aria-label="Photo"><img alt=""><p></p><button type="button" class="lb__x" aria-label="Close">✕</button><button type="button" class="lb__p" aria-label="Previous photo">←</button><button type="button" class="lb__n" aria-label="Next photo">→</button></div></div></div>')
    js = JS.replace('__GUIDES__', json.dumps(gs, ensure_ascii=False)).replace('__GIDX__', json.dumps(IDX['hero'])).replace('__ARTIMG__', json.dumps(article_images()))
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + js + tpl[i:]
