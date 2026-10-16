# Owner, 16 Oct: the review's fixes in the article template (docs/UX-PROTOTYPE-REVIEW-2026-10-16.md):
# A1 live header/search/favourites/language/assistant/footer, A7 touch targets, A8 small text, A9 full excerpt,
# A10 and suggestion 4 the bottom booking bar on tablet too.
import re, sys, os, importlib.util
spec = importlib.util.spec_from_file_location('chrome', os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'chrome.py'))
CH = importlib.util.module_from_spec(spec); spec.loader.exec_module(CH)

src = 'article.template.html'
s = open(src, encoding='utf-8').read()

CSS = '''
/* ---------- review fixes, 16 Oct ---------- */
.lc .nav{margin-bottom:calc(-1 * var(--navh,72px))}
.secbar{top:var(--navh,72px)!important}
.prose h2{scroll-margin-top:calc(var(--navh,72px) + 64px)!important}
.abox{top:calc(var(--navh,72px) + 70px)!important}
/* A9: the whole summary, never cut */
@container site (max-width:600px){ .ahero .lead{display:block!important;-webkit-line-clamp:unset!important;overflow:visible!important;font-size:14.5px} }
/* A8 */
.credit{font-size:11px!important}
/* A7: 44 px touch targets on phones */
@container site (max-width:600px){
  .crumb a{display:inline-flex;align-items:center;min-height:44px}
  .crumb{margin-bottom:0!important}
  .secbar a{min-height:44px;display:flex;align-items:center}
  .vctl button{width:40px;height:40px}
  .tcard .go,.bk__also,.bk__stay{min-height:44px}
}
/* A10 and suggestion 4: the bottom booking bar also on tablets (the booking box drops under the article there) */
@container site (min-width:601px) and (max-width:900px){
  .mbar{display:flex;align-items:center;gap:12px;position:sticky;bottom:0;z-index:16;margin-top:-72px;height:72px;padding:10px 22px 10px 28px;background:rgba(255,255,255,.97);border-top:1px solid var(--line);box-shadow:0 -8px 24px rgba(16,24,32,.08);transform:translateY(110%);transition:transform .25s}
  .mbar.is-on{transform:none}
  .mbar div{flex:1;min-width:0;line-height:1.3}
  .mbar small{display:block;font-size:12px;color:#145c4c;font-weight:600}
  .mbar b{display:block;font:600 16px var(--serif);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .mbar .btn{flex:none}
}
'''
a = s.index('</style>'); s = s[:a] + CH.css() + CSS + s[a:]

# header and footer
a = s.index('      <header class="hdr" id="hdr">'); e = s.index('</header>', a) + len('</header>')
nav = CH.nav_html(); i = nav.rindex('</header>'); nav = nav[:i] + '<div class="prog" id="prog"></div>' + nav[i:]
s = s[:a] + nav + s[e:]
a = s.index('      <footer class="ftr">'); e = s.index('</footer>', a) + len('</footer>')
s = s[:a] + CH.footer_html() + s[e:]
s = s.replace('<div class="viewport" id="vp">', '<div class="viewport lc" id="vp">', 1)
# overlays after the lightbox, inside the frame
a = '    <div class="lb" id="lb"'
assert s.count(a) == 1
s = s.replace(a, '    ' + CH.overlays_html() + '\n' + a)

reps = [
    ('  $("#hdr").classList.toggle("is-solid", vp.scrollTop > 8);\n', ''),
    ('  if(e.target.closest("#menu")){ const h = $("#hdr"); h.classList.toggle("is-open"); $("#menu").setAttribute("aria-expanded", h.classList.contains("is-open")); return; }\n', ''),
    ('$("#vswitch").onclick = () => setSwitch($("#vswitch").getAttribute("aria-pressed")!=="true");', ''),
    ('  $("#vswitch").hidden = true; videoCtl(); lazyVids();', '  videoCtl(); lazyVids(); window.lcPaint && lcPaint();'),
]
for a, b in reps:
    assert s.count(a) == 1, a[:70]
    s = s.replace(a, b)

JS = CH.JS + r'''
window.LC_TOP=true;
// the assistant and search also find the guides and trips in this prototype
window.lcIndex=()=>Object.entries(DATA).map(([k,a])=>({t:a.title+" "+a.crumb+" "+a.excerpt,html:`<span class="search__item-title">${esc(a.title)}</span>`,run:()=>document.querySelector(`[data-art="${k}"]`).click()}))
  .concat(Object.values(TRIPS).map(t=>({t:t.title+" "+t.place,html:`<span class="search__item-title">${esc(t.title)}</span>`,run:()=>lcToast("Prototype: on the site this opens the trip page (proposal 4).")})));
window.lcRoute=(href)=>{ const p=href.replace("https://egyptroamer.com",""); const k=Object.keys(DATA).find(k=>p.includes("valley-of-the-kings")&&k==="valley"||p.includes("snorkel")&&k==="snorkel"); if(k){ document.querySelector(`[data-art="${k}"]`).click(); return; } lcToast("Prototype: on the site this opens "+p); };
// the assistant button moves up while the booking bar is on screen
setInterval(()=>{ document.getElementById("lc-ov").classList.toggle("is-raised", document.getElementById("mbar").classList.contains("is-on") && getComputedStyle(document.getElementById("mbar")).display!=="none"); },400);
'''
a = '\nrender("valley");'
assert s.count(a) == 1
s = s.replace(a, JS + a)
open(src, 'w', encoding='utf-8', newline='\n').write(s)
print('ok')
