# Owner, 16 Oct: fix the review's problems A1–A8 and suggestions 1–4, 6 and 7 (docs/UX-PROTOTYPE-REVIEW-2026-10-16.md).
# Applied last by build4.py.
import re, importlib.util, os
spec = importlib.util.spec_from_file_location('chrome', os.path.join(os.path.dirname(os.path.abspath(__file__)), 'chrome2.py'))
CH = importlib.util.module_from_spec(spec); spec.loader.exec_module(CH)

CSS = '''
/* ---------- review fixes, 16 Oct ---------- */
/* Places: where to go, as opposed to Trips & tours (what to book) */
.pl-hero{position:relative;color:#fff;min-height:340px;display:flex;align-items:flex-end;overflow:hidden}
.pl-hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.pl-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(16,24,32,.35),rgba(16,24,32,.85))}
.pl-hero .wrap{position:relative;z-index:1;padding-block:110px 36px}
.pl-hero h1{font-size:56px;line-height:1;margin:8px 0 12px}
.pl-hero p{max-width:640px;margin:0;font-size:16px;opacity:.9}
.pl-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px;padding-block:36px 70px}
.pl-card{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column}
.pl-card img{width:100%;aspect-ratio:16/10;object-fit:cover;display:block}
.pl-card div{padding:16px 18px 18px;display:flex;flex-direction:column;gap:6px;flex:1}
.pl-card small{font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--gold-deep,#a8871f);font-weight:600}
.pl-card h2{font-size:26px;margin:0}
.pl-card p{margin:0;color:var(--muted);font-size:14.5px}
.pl-card .pl-best{font-size:13px;color:var(--muted)}
.pl-card .pl-acts{display:flex;gap:8px;flex-wrap:wrap;margin-top:auto;padding-top:10px}
@container site (max-width:900px){ .pl-grid{grid-template-columns:repeat(2,minmax(0,1fr))} }
@container site (max-width:600px){ .pl-grid{grid-template-columns:1fr;gap:14px} .pl-hero h1{font-size:38px} .pl-hero .wrap{padding-block:96px 26px} .pl-card .pl-acts .btn{flex:1;justify-content:center;min-height:44px} }

/* sticky bars sit under the live header, whatever its height */
.filters,.tabs{top:var(--navh,72px)!important}
.choose2__photo{top:calc(var(--navh,72px) + 20px)}
/* A3: the booking photo is a short banner on tablet and phone, so the options start on the first screen */
@container site (max-width:900px){
  .choose2__photo{aspect-ratio:16/7!important;position:relative!important;top:auto!important}
  .choose2__grid{gap:20px!important}
}
/* A4: two columns of trip cards on tablet */
@container site (min-width:601px) and (max-width:900px){
  .grid,.grid--4{grid-template-columns:repeat(2,minmax(0,1fr))!important}
}
/* A5: on phone and tablet the booking bar is a solid bar across the bottom, and the page leaves room for it */
@container site (max-width:900px){
  .capsule{float:none!important;margin:-72px 0 0!important;bottom:0!important;border-radius:0!important;border-width:1px 0 0!important;background:#0e0c09!important;padding:10px 12px 10px 18px!important;justify-content:space-between;width:auto;box-shadow:0 -8px 24px rgba(0,0,0,.35)!important}
  .wfoot{height:76px}
}
/* A6 and suggestion 7: on phones the opener keeps the place only, and the booking buttons come before the long text */
@container site (max-width:600px){
  .wtxt .eyebrow .ep--x{display:none}
  .wtxt{display:flex;flex-direction:column}
  .wtxt .wacts{order:3;margin:6px 0 18px!important}
  .wtxt .lead{order:4}
  .wtxt .wq{order:5}
  .wtxt .wchips,.wtxt .chips{order:6}
}
/* A7: touch targets of at least 44 px on phones */
@container site (max-width:600px){
  .skip,.wcmp,.gbanner > a,.lc .brand,.crumbs a,.moods2__more{display:inline-flex;align-items:center;min-height:44px}
  .live .map__card .link,.live .rec__cta{display:inline-flex;align-items:center;min-height:44px}
  .wopt .btn,.chip,.chip2{min-height:44px}
  .vctl button{width:40px;height:40px;position:relative}
  .vctl button::after{content:"";position:absolute;inset:-4px}
}
/* A8: no label under 11 px */
.live .rec__kind,.live .map__card-body .t-label{font-size:11px!important}
'''

def apply(tpl):
    a = tpl.index('</style>')
    tpl = tpl[:a] + CH.css() + CSS + tpl[a:]
    # A1, A2, suggestion 1: the live header, menu, overlays and footer replace the prototype's own
    a = tpl.index('      <header class="hdr" id="hdr">'); e = tpl.index('</header>', a) + len('</header>')
    # the header button keeps its role: "Choose a trip" while browsing, "Book this trip" inside a trip
    nav = CH.nav_html('id="hdr-cta" data-go="2"')
    assert 'id="hdr-cta"' in nav
    nav = nav.replace('<a href="#" data-href="https://egyptroamer.com/experiences/"', '<a href="#" id="nav-exp" data-href="https://egyptroamer.com/experiences/"', 1)
    assert 'id="nav-exp"' in nav
    tpl = tpl[:a] + nav + tpl[e:]
    a = tpl.index('<footer class="ftr">'); e = tpl.index('</footer>', a) + len('</footer>')
    tpl = tpl[:a] + CH.footer_html() + tpl[e:]
    a = '      <div class="toast" id="toast" hidden></div>\n    </div>\n  </div>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '      <div class="toast" id="toast" hidden></div>\n    </div>\n    ' + CH.overlays_html() + '\n  </div>')
    tpl = tpl.replace('class="viewport site" id="vp"', 'class="viewport site lc" id="vp"', 1)
    # a real Places screen, so "Places" and "Trips & tours" no longer open the same list
    a2 = '      <!-- 3 & 4 · the trip, in its own world -->'
    assert tpl.count(a2) == 1
    tpl = tpl.replace(a2, '      <section data-screen="p" hidden><div id="places"></div></section>\n\n' + a2)
    # the prototype's own phone menu is gone (the live header has its own)
    for line in ['document.getElementById("menu").addEventListener("click",e=>{', 'document.querySelectorAll("#hdr nav a").forEach(']:
        i = tpl.index(line); j = tpl.index('\n', i) + 1
        tpl = tpl[:i] + tpl[j:]
    # A6: the opener eyebrow keeps its parts separate, so phones can show the place only
    a = '<p class="eyebrow">${esc(W.eyebrow)}</p>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '<p class="eyebrow">${W.eyebrow.split(" · ").map((p,i)=>`<span class="ep${i>1?" ep--x":""}">${i?" · ":""}${esc(p)}</span>`).join("")}</p>')
    # routes for the live links: places and experiences open the prototype's own screens
    js = CH.JS + r'''
window.LC_TOP=false;
const favTrip=(t,txt)=>t&&window.lcFavBtn?lcFavBtn({id:"trip:"+t.id,title:t.t,meta:`${t.place} · ${t.time} · ${t.n} options`,img:IMG[t.k]},txt):"";
window.lcOpenSaved=id=>{ const [k,v]=id.split(":"); if(k==="trip"){ if(WORLDS[v]){ current=v; lastViewed=v; go(3); } else go(2); } else if(k==="place"){ lcPlace(DESTS[v].place); } };
function showPlaces(){
  $$("[data-screen]").forEach(x=>x.hidden=x.dataset.screen!=="p"); $$(".steps button").forEach(b=>b.removeAttribute("aria-current"));
  $("#url").textContent="egyptroamer.com/destinations/"; $("#nav-exp").removeAttribute("aria-current");
  document.querySelectorAll('#vp .nav__links a').forEach(a=>a.toggleAttribute("aria-current",/destinations/.test(a.dataset.href||"")));
  $("#hdr-cta").innerHTML='Choose a trip <span class="arr">→</span>'; $("#hdr-cta").dataset.go="2"; $("#hdr-cta").dataset.wjump="";
  const n=pl=>TRIPS.filter(t=>t.place===pl).length;
  $("#places").innerHTML=`<div class="pl-hero"><img src="${IMG.sc_nile}" alt=""><div class="wrap"><p class="eyebrow">Places</p><h1>Seven places. <i>Seven different Egypts.</i></h1><p>From Cairo's thousand minarets to the silence of Siwa, every region tells its own chapter. Choose a place to read about it, or go straight to its trips.</p></div></div>
    <div class="wrap pl-grid">${Object.entries(DESTS).map(([id,d])=>`<article class="pl-card"><img src="${IMG["d_"+id]}" alt=""><div><small>${esc(d.region)}</small><h2>${esc(d.name)}</h2><p>${esc(d.tag)}</p><span class="pl-best">Best time ${esc(d.best)}</span>
      <div class="pl-acts">${window.lcFavBtn?lcFavBtn({id:"place:"+id,title:d.name,meta:`${d.region} · Best time ${d.best}`,img:IMG["d_"+id]}):""}<a class="btn btn--gold btn--sm" href="#" data-place="${esc(d.place)}">${n(d.place)} ${n(d.place)===1?"trip":"trips"} →</a><button type="button" class="btn btn--line btn--sm" data-guide="${esc(d.name)}">Read about ${esc(d.name)}</button></div></div></article>`).join("")}</div>`;
  vp.scrollTo({top:0,behavior:"auto"}); window.lcPaint&&lcPaint();
}
document.addEventListener("click",e=>{ const g=e.target.closest("[data-guide]"); if(g){ e.preventDefault(); lcToast(`Prototype: on the site this opens the ${g.dataset.guide} page: what it is, when to go, where to stay and its trips.`); } });
window.lcPlace=pl=>{ go(2); const b=document.querySelector(`[data-fplace="${pl}"]`); b&&b.click(); };
window.lcIndex=()=>TRIPS.map(t=>({t:t.t+" "+(t.place||"")+" "+(t.dest||""),card:{img:IMG[t.k],title:esc(t.t),meta:`${esc(t.place||"")} · ${esc(t.time)} · ${t.n} options`,cta:WORLDS[t.id]?"See the trip":"See trips"},run:()=>{ if(WORLDS[t.id]){ current=t.id; lastViewed=t.id; go(3);} else go(2); }}));
window.lcRoute=(href,a)=>{
  const p=href.replace("https://egyptroamer.com","");
  const exp=p.match(/^\/experiences\/([^/]+)\//); if(exp){ const id=Object.keys(WORLDS).find(k=>WORLDS[k].url===exp[1]); if(id){ current=id; lastViewed=id; go(3); return; } }
  const dst=p.match(/^\/destinations\/([^/]+)\//); if(dst){ const pl=LPLACE[dst[1]]; go(2); if(pl){ const b=document.querySelector(`[data-fplace="${pl}"]`); b&&b.click(); } return; }
  if(/^\/destinations\/?$/.test(p)){ showPlaces(); return; }
  if(/^\/experiences\/?$/.test(p)){ go(2); return; }
  if(p==="/"||p===""){ go(1); return; }
  lcToast("Prototype: on the site this opens "+p);
};
// the assistant button moves up while the booking bar is on screen
setInterval(()=>{ const c=document.getElementById("wcap"); const on=!!c&&c.offsetParent!==null&&getComputedStyle(c).visibility==="visible"; document.getElementById("lc-ov").classList.toggle("is-raised",on); },400);
'''
    # hearts: trip cards, the trip opener and the place cards can be saved
    reps2 = [
        ('const card=t=>`<button type="button" class="card', 'const card=t=>`<div class="lcfw"><button type="button" class="card'),
        ('<span class="card__go">See options →</span></div></div></button>`;', '<span class="card__go">See options →</span></div></div></button>${favTrip(t)}</div>`;'),
        ('const mini=t=>`<button type="button" class="mini', 'const mini=t=>`<div class="lcfw"><button type="button" class="mini'),
        ('<div class="wacts"><a class="btn btn--gold" href="#wchoose" data-wjump="wchoose">Book this trip</a>', '<div class="wacts"><a class="btn btn--gold" href="#wchoose" data-wjump="wchoose">Book this trip</a>${favTrip(TRIPS.find(t=>t.id===current),true)}'),
    ]
    for x, y in reps2:
        assert tpl.count(x) == 1, x[:60]
        tpl = tpl.replace(x, y)
    i = tpl.index('const mini=t=>`<div class="lcfw">'); j = tpl.index('</button>`;', i)
    tpl = tpl[:j] + '</button>${favTrip(t)}</div>`;' + tpl[j + len('</button>`;'):]
    a = '\nrenderHome(); renderLive();'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, js + a)
    return tpl
