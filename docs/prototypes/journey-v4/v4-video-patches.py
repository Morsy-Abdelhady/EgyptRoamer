# Video placements for proposal 4 (owner, 16 Oct: more videos, chosen to fit the content). Applied by build4.py.
VIDEOS = {
    "giza-sunset":        ("Giza", "Peggy Anke"),
    "cairo-nile-sunset":  ("Cairo, the Nile", "Nada Ashraf"),
    "karnak-columns":     ("Karnak Temple, Luxor", "BOB DK"),
    "redsea-reef":        ("Red Sea, Egypt", "Adrien JACTA"),
    "redsea-coral":       ("Red Sea", "Adrien JACTA"),
    "redsea-moray":       ("Egypt's Red Sea", "Adrien JACTA"),
    "alexandria-qaitbay": ("Qaitbay Citadel, Alexandria", "The Instagrapher"),
    "tanoura":            ("Egyptian tanoura dance, city not stated", "Magda Ehlers"),
    "giza-camels":        ("Near the pyramids of Giza", "K"),
}

def credit(k):
    place, who = VIDEOS[k]
    return f"{place} · Video: {who}, Pexels"

def patch_home(js, body):
    reps = [
        # chapter videos load when on screen
        ('<video class="ch__img" autoplay muted loop playsinline poster="${IMG[c.img]}" src="${c.video}"></video>',
         '<video class="ch__img" muted loop playsinline preload="none" poster="${IMG[c.img]}" data-src="${c.video}"></video>'),
        ('{n:"04",eye:"A different world",name:"Red Sea",',
         '{n:"04",video:"video/redsea-reef.mp4",vcredit:"' + credit("redsea-reef") + '",eye:"A different world",name:"Red Sea",'),
        # moods: video where an exactly located one exists
        ('function mood(i){\n  const [id,label,desc,dest]=MOODS[i], d=DESTS[dest], trips=TRIPS.filter(t=>t.dest===dest);',
         'const MOOD_VIDEO={ancient:["video/karnak-columns.mp4","' + credit("karnak-columns") + '"],beach:["video/redsea-reef.mp4","' + credit("redsea-reef") + '"],food:["video/alexandria-qaitbay.mp4","' + credit("alexandria-qaitbay") + '"]};\n'
         'function mood(i){\n  const [id,label,desc,dest]=MOODS[i], d=DESTS[dest], trips=TRIPS.filter(t=>t.dest===dest);\n'
         '  const mv=MOOD_VIDEO[id], v=$("#mood-vid"); if(mv){ v.dataset.src=mv[0]; v.hidden=false; if(v.getAttribute("src")){ if(v.getAttribute("src")!==mv[0]) v.src=mv[0]; v.play().catch(()=>{}); } $("#mood-credit").textContent=mv[1]; $("#mood-credit").hidden=false; } else { v.pause(); v.hidden=true; $("#mood-credit").hidden=true; }'),
        # browsing: each place opens with a moving banner where a located video exists
        ('["Cairo & Giza","d_cairo",', '["Cairo & Giza","d_cairo","video/giza-camels.mp4|' + credit("giza-camels") + '",'),
        ('["Luxor","d_luxor",', '["Luxor","d_luxor","video/karnak-columns.mp4|' + credit("karnak-columns") + '",'),
        ('["Aswan","d_aswan",', '["Aswan","d_aswan","",'),
        ('["Alexandria","d_alexandria",', '["Alexandria","d_alexandria","video/alexandria-qaitbay.mp4|' + credit("alexandria-qaitbay") + '",'),
        ('["Red Sea","d_hurghada",', '["Red Sea","d_hurghada","video/redsea-coral.mp4|' + credit("redsea-coral") + '",'),
        ('["Western Desert","sc_desert",', '["Western Desert","sc_desert","",'),
        ('$("#groups").innerHTML=GROUPS.map(([p,img,line,sub,guide])=>',
         '$("#groups").innerHTML=GROUPS.map(([p,img,vid,line,sub,guide])=>'),
        ('<section class="group"><div class="group__head"><img src="${IMG[img]}" alt=""><div><h2>${esc(p)}</h2><p>${esc(line)} <small>· ${esc(sub)}</small></p></div><a href="#">Read the ${esc(guide)} →</a></div>',
         '<section class="group"><div class="gbanner">${vid?`<video muted loop playsinline preload="none" poster="${IMG[img]}" data-src="${vid.split("|")[0]}"></video><span class="vbadge">${esc(vid.split("|")[1])}</span>`:`<img src="${IMG[img]}" alt="">`}<div class="gbanner__t"><h2>${esc(p)}</h2><p>${esc(line)} <small>· ${esc(sub)}</small></p></div><a href="#">Read the ${esc(guide)} →</a></div>'),
        ('$("#f-count").textContent=`${total} ${total===1?"trip":"trips"}`;',
         '$("#f-count").textContent=`${total} ${total===1?"trip":"trips"}`; lazyVideos($("#groups"));'),
        ('$$("#mood-tabs button").forEach', '$$("#mood-tabs button").forEach'),
    ]
    for a, b in reps:
        assert js.count(a) == 1, a[:80]
        js = js.replace(a, b)
    a = '<img class="moods2__bg" id="mood-bg" alt="">'
    assert body.count(a) == 1
    body = body.replace(a, a + '\n          <video class="moods2__bg" id="mood-vid" muted loop playsinline preload="none" hidden></video><span class="vbadge" id="mood-credit" hidden></span>')
    return js, body

def patch_worlds(w):
    reps = [
        ('redsea: {cls:"w-sea", fx:"sea", photo:"sea_l", url:',
         'redsea: {cls:"w-sea", fx:"sea", photo:"sea_l", video:"video/redsea-reef.mp4", vcredit:"' + credit("redsea-reef") + '", url:'),
        ('{t:"First dive",p:"First dive, then a surface interval on board with lunch or snacks."}',
         '{t:"First dive",p:"First dive, then a surface interval on board with lunch or snacks.",video:"video/redsea-coral.mp4",credit:"' + credit("redsea-coral") + '"}'),
        ('{t:"Second dive",p:"Second dive at a different reef, then back to port in the afternoon."}',
         '{t:"Second dive",p:"Second dive at a different reef, then back to port in the afternoon.",video:"video/redsea-moray.mp4",credit:"' + credit("redsea-moray") + '"}'),
        ('{t:"Tanoura",p:"A tanoura performance: the spinning dance with multi-coloured skirts, related to Sufi whirling."}',
         '{t:"Tanoura",p:"A tanoura performance: the spinning dance with multi-coloured skirts, related to Sufi whirling.",video:"video/tanoura.mp4",credit:"' + credit("tanoura") + '"}'),
        ('{t:"Dinner",p:"Dinner, often a buffet of Egyptian and international dishes.",why:',
         '{t:"Dinner",p:"Dinner, often a buffet of Egyptian and international dishes.",video:"video/cairo-nile-sunset.mp4",credit:"' + credit("cairo-nile-sunset") + '",why:'),
        # opener and step videos load lazily; step videos play only while their step is on
        ('`<video autoplay muted loop playsinline poster="${IMG[W.photo]}" src="${W.video}"></video>`',
         '`<video muted loop playsinline preload="none" poster="${IMG[W.photo]}" data-src="${W.video}"></video>`'),
        ('const layers = W.steps.map((s,i)=> s.img ?',
         'const layers = W.steps.map((s,i)=> s.video ? `<video class="jv ${i?"":"on"}" data-j="${i}" muted loop playsinline preload="none" poster="${IMG[W.photo]}" data-jsrc="${s.video}"></video>` : s.img ?'),
        ('${s.why?`<p class="why">${esc(s.why)}</p>`:""}</article>',
         '${s.why?`<p class="why">${esc(s.why)}</p>`:""}${s.credit?`<p class="vcred">${esc(s.credit)}</p>`:""}</article>'),
        ('<video autoplay muted loop playsinline src="video/karnak-columns.mp4" poster="${IMG.valley}"></video>',
         '<video muted loop playsinline preload="none" data-src="video/karnak-columns.mp4" poster="${IMG.valley}"></video>'),
        ('<small>Karnak Temple, Luxor · Video: BOB DK, Pexels</small>', '<small>' + credit("karnak-columns") + '</small>'),
        # step observer: switch the active layer's video on, the others off
        ('const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){const i=e.target.dataset.js;host.querySelectorAll("[data-j]").forEach(x=>x.classList.toggle("on",x.dataset.j===i));',
         'const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){const i=e.target.dataset.js;host.querySelectorAll("[data-j]").forEach(x=>{const on=x.dataset.j===i;x.classList.toggle("on",on);if(x.tagName==="VIDEO"){if(on){if(!x.getAttribute("src"))x.src=x.dataset.jsrc;x.play().catch(()=>{});}else x.pause();}});'),
        ('  if(W.fx==="tomb") torch();', '  lazyVideos(host); stopFx.push(()=>host.querySelectorAll("video").forEach(v=>v.pause()));\n  if(W.fx==="tomb") torch();'),
    ]
    for a, b in reps:
        assert w.count(a) == 1, a[:80]
        w = w.replace(a, b)
    w += '''
// Videos load only when they come on screen, and pause when they leave it.
let lazyIO=null;
function lazyVideos(scope){
  if(!lazyIO) lazyIO=new IntersectionObserver(es=>es.forEach(e=>{const v=e.target; if(e.isIntersecting){ if(!v.getAttribute("src")) v.src=v.dataset.src; if(!REDUCE) v.play().catch(()=>{}); } else v.pause(); }),{root:vp,rootMargin:"200px 0px",threshold:.1});
  (scope||document).querySelectorAll("video[data-src]").forEach(v=>lazyIO.observe(v));
}
'''
    return w

EXTRA_CSS = '''
.gbanner{position:relative;height:190px;border-radius:16px;overflow:hidden;margin-bottom:18px;display:flex;align-items:flex-end;justify-content:space-between;gap:18px;padding:18px 22px;color:#fff}
.gbanner > img,.gbanner > video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.gbanner::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(16,24,32,.78),rgba(16,24,32,.15) 70%)}
.gbanner__t,.gbanner > a{position:relative;z-index:1}
.gbanner h2{font-size:34px;color:#fff}
.gbanner p{margin:2px 0 0;font:italic 500 16px var(--serif);color:#f3ead6}
.gbanner small{font-style:normal;font-family:var(--sans);font-size:12.5px;opacity:.8}
.gbanner > a{font-size:13.5px;font-weight:600;color:#fff;white-space:nowrap}
.gbanner .vbadge{top:12px;z-index:2}
.moods2 .vbadge{top:20px;z-index:2}
.ch .vbadge{top:86px;z-index:2}
.vcred{margin:10px 0 0!important;font-size:11.5px!important;color:#b8b2a6!important;letter-spacing:.02em}
'''
