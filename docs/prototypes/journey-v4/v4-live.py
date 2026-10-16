# The live site's "Find your Egypt" and "The map" sections replace the prototype's own moods and destinations
# (owner, 16 Oct). live-capture.mjs records them from https://egyptroamer.com: every mood, every map place, the CSS
# rules they use and their icons. Applied by build4.py after the home patches.
import json, re

PLACE = {'cairo': 'Cairo & Giza', 'luxor': 'Luxor', 'aswan': 'Aswan', 'alexandria': 'Alexandria',
         'hurghada': 'Red Sea', 'sharm': 'Red Sea', 'siwa': 'Western Desert'}

def css(cap):
    out = []
    for wrap, sel, body in cap['css']['rules']:
        if sel == '.live':
            # only the design tokens from :root; never the page background or WordPress admin values
            body = '; '.join(d for d in body.split('; ') if d.startswith('--') and not d.startswith('--wp'))
            if not body:
                continue
        rule = f'{sel}{{{body}}}'
        if wrap is None:
            out.append(rule)
        elif re.fullmatch(r'\(max-width: ?\d+px\)', wrap):
            out.append(f'@container site {wrap.replace(" ", "")}{{{rule}}}')   # small-screen rules follow the frame
        elif 'reduced-motion' in wrap:
            out.append(f'@media {wrap}{{{rule}}}')
    # sizes written against the window follow the site frame instead (the prototype shows the site inside a frame)
    out = [re.sub(r'(\d)vw\b', r'\1cqw', r) for r in out]
    out += cap['css']['keyframes']
    return '\n/* ---------- live site: Find your Egypt + The map (captured 16 Oct) ---------- */\n' + '\n'.join(out) + '''
.live{display:block;font-family:var(--font-sans)}
.live .moods__hint{display:none}
@container site (max-width:600px){.live .moods{min-height:0}.live .moods__dial{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr));overflow:visible!important;margin-inline:0!important;padding-inline:0!important;gap:18px 4px!important}.live .mood{width:auto!important;min-width:0}.live .mood__disc{width:62px!important;height:62px!important;margin-inline:auto}}
'''

def body(b):
    a = b.index('<section class="moods2 on-dark" id="moods">')
    e = b.index('</div></section>', b.index('<p class="later">')) + len('</div></section>')
    return b[:a] + '<div class="live" id="live"></div>' + b[e:]

def js(j, cap):
    # the old moods and destination cards are gone from the page
    for a in ['  $("#mood-tabs").innerHTML=MOODS.map(([id,l],i)=>`<button type="button" role="tab" data-mood="${i}" aria-selected="${i===0}">${l}</button>`).join("");\n  mood(0);\n',
              ]:
        assert j.count(a) == 1, a[:60]
        j = j.replace(a, '')
    a = j.index('  $("#dest-row").innerHTML='); e = j.index('\n', a) + 1
    j = j[:a] + j[e:]
    a = 'const m=e.target.closest("[data-mood]"); if(m){mood(+m.dataset.mood);return;}'
    assert j.count(a) == 1
    j = j.replace(a, 'const m=e.target.closest("[data-mood]"); if(m&&!m.closest(".live")){mood(+m.dataset.mood);return;}')
    j += '''
// ---------- live sections ----------
const LIVE=''' + json.dumps({k: cap[k] for k in ('moodsHTML', 'moods', 'mapHTML', 'places', 'sprite')}) + ''';
const LIMG=''' + '{{liveimgs}}' + ''';
const LPLACE=''' + json.dumps(PLACE) + ''';
// links point inside the prototype: a destination opens its trips, the planner opens browsing
const lfix=h=>h.replace(/@@L(\\d+)@@/g,(m,i)=>LIMG[+i])
  .replace(/href="https:\\/\\/egyptroamer\\.com\\/destinations\\/([a-z-]+)\\/"/g,(m,s)=>`href="#" data-place="${LPLACE[s]||"all"}"`)
  .replace(/href="#planner"/g,'href="#" data-go="2"');
function renderLive(){
  const host=document.getElementById("live");
  host.innerHTML=`<svg width="0" height="0" style="position:absolute" aria-hidden="true">${LIVE.sprite}</svg>`+lfix(LIVE.moodsHTML)+lfix(LIVE.mapHTML);
  host.querySelectorAll("[data-reveal]").forEach(e=>e.classList.add("is-visible","is-in","revealed"));
  // the whole country stays in view, as on the live page before a place is zoomed
  const fit=()=>host.querySelector("[data-map-svg]").setAttribute("viewBox","0 0 560 470"); fit();
  host.addEventListener("click",e=>{
    const md=e.target.closest(".live [data-mood]");
    if(md){ const s=LIVE.moods.find(x=>x.id===md.dataset.mood), sec=host.querySelector("#moods");
      sec.setAttribute("style",s.style||""); sec.querySelector(".moods__body").innerHTML=lfix(s.body);
      sec.querySelectorAll("[data-mood]").forEach(b=>b.setAttribute("aria-selected",String(b===md)));
      sec.querySelectorAll(".moods__layer").forEach(l=>l.classList.toggle("is-on",l.dataset.layer===s.id)); return; }
    const mi=e.target.closest("[data-map-item],[data-point]");
    if(mi){ const id=mi.dataset.mapItem||mi.dataset.point, s=LIVE.places.find(x=>x.id===id); if(!s) return; const m=host.querySelector("#map");
      m.querySelector("[data-map-world]").innerHTML=lfix(s.world); m.querySelector("[data-map-list]").innerHTML=lfix(s.list);
      const c=m.querySelector("[data-map-card]"); c.outerHTML=lfix(s.card); fit(); }
  });
}
'''
    a = '\nrenderHome(); lazyVideos(document);'
    return j, a

def apply(tpl, cap):
    a = tpl.index('</style>')
    tpl = tpl[:a] + css(cap) + tpl[a:]
    a = 'renderHome(); lazyVideos(document);'
    assert tpl.count(a) == 1
    return tpl.replace(a, 'renderHome(); renderLive(); lazyVideos(document);')
