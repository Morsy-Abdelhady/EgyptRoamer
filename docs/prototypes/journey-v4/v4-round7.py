# Owner, 17 Oct: "apply the seven suggestions of the expert review" (docs/UX-EXPERT-REVIEW-2026-10-17.md §3).
# 1. Shorter homepage: story, moods, the map (the owner asked for it on 16 Oct) as the one place picker, hotels and
#    the Trip Builder. "Seven places" lives on Places; "Moments we'd book again" repeated the trips already on
#    Trips & tours; the Journal repeated the Travel Guide. They leave the homepage.
# 2. Hotels: the partners block had one empty tab. It becomes "Where to stay", Trip.com's hotel links per place,
#    built from the same hotel rows the trips show.
# 3. One trip behaviour: every trip opens our trip page first, where its options are compared (Trip Builder and the
#    map's place card included; partner links only on the trip page).
# 4. One vocabulary: "Places" and "trips" in the sections that came from production too.
# 5. Phones: the Trip assistant is an icon in the header next to ♡, not a floating button over the text.
# 6. Trip Builder: its partner tours open the matching trip page.
# 7. Route guides (itineraries, best time, hidden gems, day trips): "Trips on this route" (in v4-guides.py).
import re

CSS = '''
/* ---------- expert review, 17 Oct ---------- */
.stay2{background:var(--stone,#f8f5ed);padding:84px 0 90px}
.stay2 .eyebrow{color:var(--gold-deep,#a8871f)}
.stay2 h2{font:600 44px/1.1 var(--serif);margin:10px 0 12px;max-width:760px}
.stay2 h2 em{font-style:italic}
.stay2 > .wrap > p{max-width:640px;color:var(--muted);margin:0 0 30px;font-size:16px}
.stay2__g{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;align-items:start}
.stay2__c{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column}
.stay2__c img{width:100%;aspect-ratio:16/10;object-fit:cover;display:block}
.stay2__c div{padding:14px 16px 16px;display:flex;flex-direction:column;gap:8px;flex:1}
.stay2__c h3{font:600 20px/1.2 var(--serif);margin:0}
.stay2__c ul{list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:6px}
.stay2__c a{display:inline-flex;justify-content:space-between;gap:8px;align-items:center;min-height:40px;padding:8px 12px;border-radius:10px;background:var(--stone,#f8f5ed);text-decoration:none;color:var(--charcoal);font-size:14px}
.stay2__c a:hover{background:#efe6d3}
.stay2__c a em{font-style:normal;font-weight:600;color:var(--nile,#0e3a5b);white-space:nowrap;font-size:13px}
.stay2__note{margin:22px 0 0;font-size:12.5px;color:var(--muted)}
@container site (max-width:900px){ .stay2__g{grid-template-columns:repeat(2,minmax(0,1fr))} .stay2 h2{font-size:34px} }
@container site (max-width:600px){ .stay2{padding:56px 0 60px} .stay2__g{grid-template-columns:1fr} .stay2 h2{font-size:28px} .stay2__c a{min-height:44px} }
/* the assistant: a header icon on phones */
.lc .nav .lc-ai{display:none}
@container site (max-width:600px){
  .lc .nav .lc-ai{display:inline-grid}
  .lc-ov .assistant-launch{display:none!important}
}
'''

def apply(tpl):
    a = tpl.index('</style>'); tpl = tpl[:a] + CSS + tpl[a:]
    reps = [
        # 1 and 2 · the homepage keeps the map, hotels and the Trip Builder
        ('  host.querySelector("#map").insertAdjacentHTML("beforebegin",fix(HOME2.destinations));\n', ''),
        ('host.querySelector("#map").insertAdjacentHTML("afterend",fix(HOME2.partners)+fix(HOME2.experiences)+fix(HOME2.guide)+fix(HOME2.planner));',
         'host.querySelector("#map").insertAdjacentHTML("afterend",stayHTML()+fix(HOME2.planner));'),
        # 4 · labels
        ('<p class="crumbs">Home / Experiences</p>', '<p class="crumbs">Home / Trips &amp; tours</p>'),
        ('<h1>Experiences</h1>', '<h1>Trips &amp; tours</h1>'),
    ]
    for x, y in reps:
        assert tpl.count(x) == 1, x[:60]
        tpl = tpl.replace(x, y)
    # 5 · the assistant icon sits next to the heart in the header
    a = '<button class="icon-btn" type="button" data-open="saved"'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '<button class="icon-btn lc-ai" type="button" data-open="assistant" aria-label="Trip assistant"><svg class="icon" aria-hidden="true"><use href="#i-sparkle"></use></svg></button>' + a)

    js = r'''
// ---------- expert review, 17 Oct ----------
const STAY_PLACES={cairo:["Cairo","Giza","Zamalek"],alexandria:["Alexandria"],siwa:["Siwa"],luxor:["Luxor"],aswan:["Aswan"],hurghada:["Hurghada","Red Sea"],sharm:["Sharm"]};
function stayHTML(){
  const rows=[]; Object.values(WORLDS).forEach(W=>(W.stay?.rows||[]).forEach(r=>{ if(!rows.some(x=>x[0]===r[0])) rows.push(r); }));
  const cards=Object.entries(DESTS).map(([id,d])=>{ const mine=rows.filter(r=>STAY_PLACES[id].some(k=>(r[0]+" "+r[1]).includes(k))); if(!mine.length) return "";
    return `<article class="stay2__c"><img src="${IMG["d_"+id]}" alt="" loading="lazy"><div><h3>${esc(d.name)}</h3><ul>${mine.map(r=>`<li><a href="#" data-stay="${esc(r[0])}">${esc((s=>s.charAt(0).toUpperCase()+s.slice(1))(r[0].replace(/^Hotels (in|on|near) (the )?/,"")))}<em>Trip.com ↗</em></a></li>`).join("")}</ul></div></article>`; }).join("");
  return `<section class="stay2" id="stay"><div class="wrap"><p class="eyebrow">Where to stay</p><h2>Hotels near your trips, <em>place by place.</em></h2><p>Our hotel partner is Trip.com. Pick an area to see its hotels; you choose, book and pay on Trip.com.</p><div class="stay2__g">${cards}</div><p class="stay2__note">Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you.</p></div></section>`;
}
document.addEventListener("click",e=>{ const s=e.target.closest("[data-stay]"); if(!s) return; e.preventDefault(); lcToast(`Prototype: on the site this opens Trip.com, ${s.dataset.stay.toLowerCase()}, in a new tab.`); }, true);
// 3 and 6 · a partner tour from the Trip Builder opens the trip it belongs to
function tripForOffer(name){ const n=(name||"").trim().toLowerCase(); return Object.keys(WORLDS).find(k=>WORLDS[k].opts.some(o=>o.n.trim().toLowerCase()===n)); }
function retarget(scope){ (scope||document).querySelectorAll("#live #planner a.itin__tour").forEach(a=>{ if(a.dataset.gtrip) return;
  const id=tripForOffer(a.querySelector(".itin__tour-name")?.textContent); if(!id){ a.closest("li").hidden=true; return; }
  a.dataset.gtrip=id; a.setAttribute("href","#"); a.removeAttribute("target"); a.removeAttribute("rel");
  const cta=a.querySelector(".itin__tour-cta"); if(cta) cta.firstChild.textContent="Compare options "; }); }
window.addEventListener("er-live-ready",()=>{ const p=document.querySelector("#live #planner"); if(!p) return; retarget(p); new MutationObserver(()=>retarget(p)).observe(p,{childList:true,subtree:true}); });
// 4 · the map's place card and the moods' "Where to go" use the same words as the menu
window.addEventListener("er-live-ready",()=>{ const host=document.getElementById("live"); const words=()=>host.querySelectorAll("#map .map__card .link, #moods .rec__cta").forEach(a=>{ const t=[...a.childNodes].find(n=>n.nodeType===3&&n.textContent.trim()); if(t&&/Explore Destination|Discover/.test(t.textContent)) t.textContent=a.closest("#map")?"See the place and its trips ":"See its trips "; });
  host.querySelectorAll("#planner *").forEach(e=>{ if(e.childElementCount===0&&/^Book with our partners$/i.test(e.textContent.trim())) e.textContent="Trips on your route"; });
  words(); new MutationObserver(words).observe(host,{childList:true,subtree:true}); });
'''
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + js + tpl[i:]
