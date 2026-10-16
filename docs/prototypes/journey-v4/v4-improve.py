# All owner-approved improvements (16 Oct): applied by build4.py on top of the earlier patches.
def worlds(w):
    reps = [
        # comparison built from the partner-stated facts, with free cancellation
        ('  const cmp = `<button type="button" class="wcmp" id="wcmp" aria-expanded="false">Compare side by side</button><table class="wtbl" id="wtbl" hidden><thead><tr><th></th>${W.opts.map(o=>`<th>${esc(o.p)}</th>`).join("")}</tr></thead><tbody>${W.cmp.map(([k,...v])=>`<tr><td>${k}</td>${v.map(x=>`<td class="${x==="Yes"?"yes":x==="Not stated"?"no":""}">${esc(x)}</td>`).join("")}</tr>`).join("")}</tbody></table>`;',
         '  const lenOf=o=>o.f.find(x=>/hour|day/.test(x))||"Not stated";\n'
         '  const rowsC=[["Private",o=>o.f.includes("Private")?"Yes":o.f.includes("Small group")?"Small group":"Not stated"],["Length",lenOf],["Pickup",o=>o.f.includes("Pickup offered")?"Yes":"Not stated"],["Free cancellation",o=>o.cancel?"Yes, up to 24 h before":"See "+o.p+"’s page"]];\n'
         '  const cmp = `<button type="button" class="wcmp" id="wcmp" aria-expanded="false">Compare side by side</button><table class="wtbl" id="wtbl" hidden><thead><tr><th></th>${W.opts.map(o=>`<th>${esc(o.n.length>38?o.n.slice(0,36)+"…":o.n)}<br><small>${esc(o.p)}</small></th>`).join("")}</tr></thead><tbody>${rowsC.map(([k,fn])=>`<tr><td>${k}</td>${W.opts.map(o=>{const x=fn(o);return `<td class="${/^Yes|Small/.test(x)?"yes":/^Not stated|^See /.test(x)?"no":""}">${esc(x)}</td>`;}).join("")}</tr>`).join("")}</tbody></table>`;'),
        # free cancellation on the option itself, when the partner states it
        ('${o.f.map(f=>`<span>${esc(f)}</span>`).join("")}</div></div>\n      <button',
         '${o.f.map(f=>`<span>${esc(f)}</span>`).join("")}${o.cancel?`<span class="fc" title="${esc(o.cancel)}">✓ Free cancellation</span>`:""}</div></div>\n      <button'),
        # steps without their own photo: atmosphere, not a picture of another place
        ('style="width:100%;height:100%;object-fit:cover;filter:${s.tone==="night"?"brightness(.45) saturate(.6) hue-rotate(-15deg)":s.tone==="dark"?"brightness(.5) contrast(1.1) saturate(.6)":"brightness(.75) saturate(1.1)"};transform:scale(${1+i*.12}) translate(${i*-2}%,${i*1.5}%)"',
         'style="width:100%;height:100%;object-fit:cover;filter:blur(14px) ${s.tone==="night"?"brightness(.4) saturate(.6) hue-rotate(-15deg)":s.tone==="dark"?"brightness(.45) saturate(.6)":"brightness(.7) saturate(1.1)"};transform:scale(${1.15+i*.1})"'),
        # path marks jump to their step; a way straight to booking
        ('<div class="jhead"><p class="eyebrow">${esc(W.jEyebrow)}</p><h2>${esc(W.jTitle)}</h2></div>',
         '<div class="jhead"><p class="eyebrow">${esc(W.jEyebrow)}</p><h2>${esc(W.jTitle)}</h2><a class="skip" href="#wchoose" data-wjump="wchoose">Skip to booking ↓</a></div>'),
        ('<div class="path">${W.steps.map((s,i)=>`<span class="${i?"":"on"}">${esc(s.t)}</span>`).join("")}</div>',
         '<div class="path">${W.steps.map((s,i)=>`<button type="button" class="${i?"":"on"}" data-pjump="${i}">${esc(s.t)}</button>`).join("")}</div>'),
        ('host.querySelectorAll(".path span").forEach((x,k)=>x.classList.toggle("on",String(k)===i));',
         'host.querySelectorAll(".path button").forEach((x,k)=>x.classList.toggle("on",String(k)===i));'),
        # FAQ before the choice, and more trips in the same place after it
        ('  const capsule = `',
         '  const faq = W.faq?`<section class="wfaq"><div class="wfaq__in"><p class="eyebrow">Questions before you book</p><h2>Frequently asked questions</h2>${W.faq.map(([q,a],i)=>`<details ${i?"":"open"}><summary>${esc(q)}</summary><p>${esc(a)}</p></details>`).join("")}</div></section>`:"";\n'
         '  const others = TRIPS.filter(t=>t.place===W.place&&WORLDS[t.id]!==W);\n'
         '  const more = others.length?`<section class="wmore"><p class="eyebrow">More trips in ${esc(W.place)}</p><h2>Not quite this one?</h2><div class="wmore__row">${others.map(mini).join("")}<a href="#" class="wmore__all" data-place="${esc(W.place)}">All ${esc(W.place)} trips →</a></div></section>`:"";\n'
         '  const capsule = `'),
        ('host.className="world "+W.cls; host.innerHTML=open+journey+sec+papy+choose+capsule+`<div class="wfoot"></div>`;',
         'host.className="world "+W.cls; host.innerHTML=open+journey+sec+papy+faq+choose+more+capsule+`<div class="wfoot"></div>`;'),
        ('  host.querySelector("#wcmp").addEventListener',
         '  host.querySelectorAll("[data-pjump]").forEach(b=>b.addEventListener("click",()=>{const s=host.querySelector(`.jstep[data-js="${b.dataset.pjump}"]`);vp.scrollTo({top:vp.scrollTop+s.getBoundingClientRect().top-vp.getBoundingClientRect().top});}));\n'
         '  host.querySelector("#wcmp").addEventListener'),
        # the capsule waits until the reader is past the opener
        ('const cap=host.querySelector("#wcap"); const io2=new IntersectionObserver(([e])=>cap.style.visibility=e.isIntersecting?"hidden":"visible",{root:vp,threshold:.2}); io2.observe(host.querySelector("#wchoose"));',
         'const cap=host.querySelector("#wcap"); const seen={choose:false,open:true}; const upd=()=>cap.style.visibility=(seen.choose||seen.open)?"hidden":"visible"; const io2=new IntersectionObserver(es=>{es.forEach(e=>{seen[e.target.id==="wchoose"?"choose":"open"]=e.isIntersecting;});upd();},{root:vp,threshold:.2}); io2.observe(host.querySelector("#wchoose")); io2.observe(host.querySelector("#wopen"));'),
        # videos: the reader can pause them all; on a data-saving or slow connection they stay as pictures
        ('function lazyVideos(scope){\n  if(!lazyIO) lazyIO=new IntersectionObserver(es=>es.forEach(e=>{const v=e.target; if(e.isIntersecting){ if(!v.getAttribute("src")) v.src=v.dataset.src; if(!REDUCE) v.play().catch(()=>{}); } else v.pause(); }),{root:vp,rootMargin:"200px 0px",threshold:.1});',
         'function lazyVideos(scope){\n  if(!lazyIO) lazyIO=new IntersectionObserver(es=>es.forEach(e=>{const v=e.target; v._in=e.isIntersecting; if(e.isIntersecting){ if(VIDEOS_OFF) return; if(!v.getAttribute("src")) v.src=v.dataset.src; v.play().catch(()=>{}); } else v.pause(); }),{root:vp,rootMargin:"200px 0px",threshold:.1});'),
        ('const REDUCE=matchMedia("(prefers-reduced-motion: reduce)").matches;',
         'const REDUCE=matchMedia("(prefers-reduced-motion: reduce)").matches;\n'
         'const CONN=navigator.connection||{}; const SAVE=!!CONN.saveData||/(^|-)2g|3g/.test(CONN.effectiveType||"");\n'
         'let VIDEOS_OFF=REDUCE||SAVE;'),
    ]
    for a, b in reps:
        assert w.count(a) == 1, a[:90]
        w = w.replace(a, b)
    # step videos also obey the switch
    a = 'if(x.tagName==="VIDEO"){if(on){if(!x.getAttribute("src"))x.src=x.dataset.jsrc;x.play().catch(()=>{});}else x.pause();}'
    assert w.count(a) == 1
    w = w.replace(a, 'if(x.tagName==="VIDEO"){if(on&&!VIDEOS_OFF){if(!x.getAttribute("src"))x.src=x.dataset.jsrc;x.play().catch(()=>{});}else x.pause();}')
    w += '''
// One switch for every video on the site (motion longer than five seconds must be pausable).
function videoSwitch(){
  const b=document.getElementById("vswitch"); if(!b) return;
  const paint=()=>{b.setAttribute("aria-pressed",String(!VIDEOS_OFF)); b.innerHTML=VIDEOS_OFF?`<span aria-hidden="true">▶</span> ${SAVE&&!REDUCE?"Videos off to save data · Play":"Play videos"}`:`<span aria-hidden="true">❚❚</span> Pause videos`;};
  b.addEventListener("click",()=>{VIDEOS_OFF=!VIDEOS_OFF; document.querySelectorAll("video").forEach(v=>{ if(VIDEOS_OFF) v.pause(); else if(v._in||v.classList.contains("on")||v.id==="mood-vid"){ if(!v.getAttribute("src")) v.src=v.dataset.src||v.dataset.jsrc||""; if(v.getAttribute("src")&&!v.hidden) v.play().catch(()=>{}); } }); paint();});
  paint();
}
// Video credits: a small "i" that opens on hover or focus.
function creditsAsInfo(scope){ (scope||document).querySelectorAll(".vbadge").forEach(e=>{ if(!e.dataset.tip){ e.dataset.tip=e.textContent; e.textContent="i"; e.tabIndex=0; e.setAttribute("role","note"); e.setAttribute("aria-label","Video credit: "+e.dataset.tip);} }); }
'''
    a = '  lazyVideos(host); stopFx.push('
    assert w.count(a) == 1
    w = w.replace(a, '  lazyVideos(host); creditsAsInfo(host); stopFx.push(')
    # every video starts muted and only on screen; respect the switch on the mood video too
    return w

def home(js, body):
    reps = [
        ('{id:"giza",k:"giza",', '{id:"giza",k:"giza",world:true,'),
        ('{id:"food",k:"food",', '{id:"food",k:"food",world:true,'),
        ('{id:"abu",k:"abu",', '{id:"abu",k:"abu",world:true,'),
        ('{id:"alex",k:"alex",', '{id:"alex",k:"alex",world:true,'),
        ('{id:"ras",k:"ras",', '{id:"ras",k:"ras",world:true,'),
        ('{id:"siwa",k:"siwa",', '{id:"siwa",k:"siwa",world:true,'),
        ('if(v.getAttribute("src")){ if(v.getAttribute("src")!==mv[0]) v.src=mv[0]; v.play().catch(()=>{}); }',
         'if(v.getAttribute("src")){ if(v.getAttribute("src")!==mv[0]) v.src=mv[0]; if(!VIDEOS_OFF) v.play().catch(()=>{}); }'),
        ('$("#mood-credit").textContent=mv[1]; $("#mood-credit").hidden=false;',
         '$("#mood-credit").dataset.tip=mv[1]; $("#mood-credit").setAttribute("aria-label","Video credit: "+mv[1]); $("#mood-credit").textContent="i"; $("#mood-credit").tabIndex=0; $("#mood-credit").hidden=false;'),
        ('lazyVideos($("#groups"));', 'lazyVideos($("#groups")); creditsAsInfo($("#groups"));'),
    ]
    reps += [
        ('class="card${t.world?" card--hl":""}"', 'class="card${t.id===lastViewed?" card--hl":""}"'),
        ('class="mini${t.world?" mini--hl":""}"', 'class="mini${t.id===lastViewed?" mini--hl":""}"'),
    ]
    for a, b in reps:
        assert js.count(a) == 1, a[:90]
        js = js.replace(a, b)
    # "Where in Egypt?" straight under the hero's buttons, for those who know where they are going
    a = '<a class="btn btn--ghost-light" href="#ch-1">Start the story <span class="arr">↓</span></a></div>'
    assert body.count(a) == 1
    body = body.replace(a, a + '\n              <div class="finder2" role="group" aria-label="Where in Egypt?"><span>Where in Egypt?</span><a href="#" class="chip2" data-place="Cairo & Giza">Cairo &amp; Giza</a><a href="#" class="chip2" data-place="Luxor">Luxor</a><a href="#" class="chip2" data-place="Aswan">Aswan</a><a href="#" class="chip2" data-place="Alexandria">Alexandria</a><a href="#" class="chip2" data-place="Red Sea">Red Sea</a><a href="#" class="chip2" data-place="Western Desert">Western Desert</a></div>')
    # the video switch, always within reach
    a = '<div class="toast" id="toast" hidden></div>'
    assert body.count(a) == 1
    body = body.replace(a, a + '\n      <button type="button" class="vswitch" id="vswitch" aria-pressed="true"></button>')
    return js, body

CSS = '''
/* ---------- improvements, 16 Oct ---------- */
/* active card: a gold edge that follows the card's own corners, with no gap (owner, 16 Oct) */
.card,.mini{transition:box-shadow .2s,transform .2s}
.card--hl,.mini--hl{outline:none!important;box-shadow:0 0 0 1px var(--gold),0 14px 34px rgba(16,24,32,.12)!important}
.card:hover,.card:focus-visible{outline:none;box-shadow:0 0 0 1px var(--gold),0 14px 34px rgba(16,24,32,.12)}
.mini:hover,.mini:focus-visible{outline:none;box-shadow:0 0 0 1px var(--gold)}
.choose2__photo{position:sticky}
.choose2__photo::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(0,0,0,.7))}
.choose2__photo img{filter:saturate(1.05) contrast(1.03)}
.choose2__cap{position:absolute;z-index:1;inset-inline:18px;bottom:16px;font:500 20px/1.25 var(--serif);color:#fff}
.hdr .btn--gold{background:transparent;border:1.5px solid var(--gold);color:#fff}
.hdr .btn--gold:hover{background:rgba(201,162,39,.15)}
.finder2{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:18px}
.finder2 span{font:600 11.5px var(--sans);letter-spacing:.2em;text-transform:uppercase;color:var(--sand);margin-inline-end:4px}
.chip2{font-size:13.5px;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.4);background:rgba(0,0,0,.25);border-radius:999px;padding:6px 13px}
.chip2:hover{background:rgba(255,255,255,.15)}
.vbadge{font-size:0!important;letter-spacing:0!important;width:24px;height:24px;padding:0!important;display:grid;place-items:center;border-radius:50%!important;cursor:help}
.vbadge::before{content:"i";font:600 12px Georgia,serif;text-transform:none}
.vbadge::after{content:attr(data-tip);position:absolute;inset-inline-end:0;top:30px;white-space:nowrap;font:500 11.5px var(--sans);letter-spacing:0;text-transform:none;background:rgba(0,0,0,.85);color:#fff;border-radius:6px;padding:5px 9px;opacity:0;pointer-events:none;transition:opacity .2s}
.vbadge:hover::after,.vbadge:focus::after{opacity:1}
.vcred{display:none}
.vswitch{position:sticky;bottom:18px;inset-inline-start:18px;float:left;margin-top:-58px;margin-inline-start:18px;z-index:16;display:flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.3);background:rgba(16,24,32,.85);backdrop-filter:blur(8px);color:#fff;border-radius:999px;padding:8px 14px;font:600 12.5px var(--sans)}
.vswitch span{font-size:11px}
.skip{display:inline-block;margin-top:10px;font-size:13px;font-weight:600;color:var(--wc);text-decoration:none;border-bottom:1px solid currentColor}
.path button{all:unset;position:relative;display:flex;align-items:center;gap:14px;font:600 12px var(--sans);letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.5);cursor:pointer;transition:color .3s}
.path button::before{content:"";width:16px;height:16px;border-radius:50%;background:var(--wbg);border:2px solid color-mix(in srgb,var(--wc) 50%,transparent);transition:all .3s}
.path button:hover,.path button:focus-visible{color:#fff}
.path button:focus-visible{outline:2px solid var(--wc);outline-offset:4px}
.path button.on{color:#fff}.path button.on::before{background:var(--wc);border-color:var(--wc);box-shadow:0 0 18px var(--wc)}
.fc{color:#7fd1c7!important;border-color:rgba(127,209,199,.45)!important}
.wtbl td{color:#efe4cc}
.wtbl td:first-child{color:var(--wc);font-weight:600;font-size:12px;letter-spacing:.06em;text-transform:uppercase}
.wtbl td.yes{color:#7fd1c7}.wtbl td.no{color:#9a9284}
.wtbl th small{font-weight:500;letter-spacing:.06em;color:#cfc4ad}
.wtbl th{vertical-align:bottom;text-transform:none!important;letter-spacing:0!important;font-size:12px!important}
.wfaq{padding:80px 56px;background:color-mix(in srgb,var(--wbg) 85%,#fff 3%)}
.wfaq__in{max-width:820px}
.wfaq h2{font-size:36px;margin:8px 0 18px}
.wfaq details{border-top:1px solid rgba(255,255,255,.14);padding:14px 0}
.wfaq details:last-child{border-bottom:1px solid rgba(255,255,255,.14)}
.wfaq summary{cursor:pointer;font:500 19px/1.35 var(--serif);color:#fff;list-style:none;display:flex;justify-content:space-between;gap:16px}
.wfaq summary::after{content:"+";color:var(--wc);font:400 24px/1 var(--sans)}
.wfaq details[open] summary::after{content:"–"}
.wfaq details p{margin:10px 0 0;color:#e3d8c2;font-size:16px;line-height:1.7}
.wmore{padding:70px 56px 90px;border-top:1px solid rgba(255,255,255,.1)}
.wmore h2{font-size:34px;margin:8px 0 18px}
.wmore__row{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;max-width:1180px;align-items:center}
.wmore .mini{background:rgba(255,255,255,.96)}
.wmore__all{color:var(--wc);font-weight:600;font-size:14px}
@media (max-width:900px){.wfaq,.wmore{padding-inline:18px}}
'''
