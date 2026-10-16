# Owner, 16 Oct: fix the review's problems A1–A8 and suggestions 1–4, 6 and 7 (docs/UX-PROTOTYPE-REVIEW-2026-10-16.md).
# Applied last by build4.py.
import re, importlib.util, os
spec = importlib.util.spec_from_file_location('chrome', os.path.join(os.path.dirname(os.path.abspath(__file__)), 'chrome.py'))
CH = importlib.util.module_from_spec(spec); spec.loader.exec_module(CH)

CSS = '''
/* ---------- review fixes, 16 Oct ---------- */
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
    nav = CH.nav_html()
    # the header button keeps its role: "Choose a trip" while browsing, "Book this trip" inside a trip
    nav = re.sub(r'<a href="#planner" class="(btn btn--primary btn--sm nav__cta)"', r'<a href="#" id="hdr-cta" data-go="2" class="\1"', nav, count=1)
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
window.lcIndex=()=>TRIPS.map(t=>({t:t.t+" "+(t.place||"")+" "+(t.dest||""),html:`<span class="search__item-title">${esc(t.t)}</span>`,run:()=>{ if(WORLDS[t.id]){ current=t.id; lastViewed=t.id; go(3);} else go(2); }}));
window.lcRoute=(href,a)=>{
  const p=href.replace("https://egyptroamer.com","");
  const exp=p.match(/^\/experiences\/([^/]+)\//); if(exp){ const id=Object.keys(WORLDS).find(k=>WORLDS[k].url===exp[1]); if(id){ current=id; lastViewed=id; go(3); return; } }
  const dst=p.match(/^\/destinations\/([^/]+)\//); if(dst){ const pl=LPLACE[dst[1]]; go(2); if(pl){ const b=document.querySelector(`[data-fplace="${pl}"]`); b&&b.click(); } return; }
  if(/^\/experiences\/?$|^\/destinations\/?$/.test(p)){ go(2); return; }
  if(p==="/"||p===""){ go(1); return; }
  lcToast("Prototype: on the site this opens "+p);
};
// the assistant button moves up while the booking bar is on screen
setInterval(()=>{ const c=document.getElementById("wcap"); const on=!!c&&c.offsetParent!==null&&getComputedStyle(c).visibility==="visible"; document.getElementById("lc-ov").classList.toggle("is-raised",on); },400);
'''
    a = '\nrenderHome(); renderLive();'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, js + a)
    return tpl
