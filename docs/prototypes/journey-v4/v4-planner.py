# Trip Builder, owner 17 Oct ("fix it, by the user experience"). Tested before: the route and nights worked, but
#  - "What moves you?" changed nothing;
#  - "Travel style" (Smart / Comfort / Luxury) changed nothing (its budget is hidden: no invented prices);
#  - with fewer nights than places, places were silently dropped from the route;
#  - the result was a list of partner offer names, the same whatever the interests.
# Now:
#  - interests rank the trips and mark the ones that match, and suggest places that fit an interest not yet picked;
#  - step 04 asks a question the trips can answer: "Private or shared?" (each trip says whether it has a private option);
#  - if places are dropped for lack of nights, the plan says which and offers "Add N nights";
#  - the result is one card per trip (photo, place, length, options, free cancellation, why it matches), opening
#    the trip page; the plan can be saved with the ♡.
CSS = '''
/* ---------- Trip Builder, 17 Oct ---------- */
.live #planner [data-b-style]{display:none}
.live #planner .pb-priv{display:flex;flex-wrap:wrap;gap:8px}
.live #planner .pb-priv button{min-height:40px;padding:0 16px;border-radius:999px;border:1px solid rgba(255,255,255,.28);background:transparent;color:#fff;font:500 14px var(--font-sans,Inter,sans-serif);cursor:pointer}
.live #planner .pb-priv button[aria-pressed="true"]{background:#fff;color:#101820;border-color:#fff}
.live #planner .pb-hint{margin:8px 0 0;font-size:12.5px;color:rgba(255,255,255,.62)}
.live #planner [data-itin-tours]{display:none!important}
.pb-note{margin:12px 0 0;padding:12px 14px;border-radius:12px;background:rgba(201,162,39,.14);border:1px solid rgba(201,162,39,.45);color:#f3e6c4;font-size:13.5px;line-height:1.5}
.pb-note button{margin-left:6px;border:0;background:#c9a227;color:#101820;border-radius:999px;padding:6px 12px;font:600 12.5px var(--font-sans,Inter,sans-serif);cursor:pointer}
.pb-res{margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.14)}
.pb-res h3{margin:0 0 4px;font:600 12px var(--font-sans,Inter,sans-serif);letter-spacing:.16em;text-transform:uppercase;color:#d8b483}
.pb-res > p{margin:0 0 10px;font-size:13px;color:rgba(255,255,255,.66)}
.pb-list{list-style:none;margin:0;padding:0;display:grid;gap:8px}
.pb-card{all:unset;box-sizing:border-box;display:grid;grid-template-columns:64px minmax(0,1fr);gap:12px;align-items:center;width:100%;padding:8px;border-radius:12px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.05);cursor:pointer;color:#fff}
.pb-card:hover,.pb-card:focus-visible{border-color:#c9a227;background:rgba(201,162,39,.1)}
.pb-card img{width:64px;height:52px;object-fit:cover;border-radius:8px}
.pb-card b{display:block;font:600 15px/1.25 var(--font-serif,Georgia,serif)}
.pb-card span{display:block;font-size:12px;color:rgba(255,255,255,.7);margin-top:2px}
.pb-card em{display:inline-block;font-style:normal;font-size:11.5px;font-weight:600;color:#9fe3d6;margin-top:3px}
.pb-card i{display:inline-block;font-style:normal;font-size:11px;font-weight:600;color:#101820;background:#d8b483;border-radius:999px;padding:1px 8px;margin-left:6px}
.pb-more{margin-top:12px;font-size:13px;color:rgba(255,255,255,.75)}
.pb-more button{border:1px solid rgba(255,255,255,.3);background:transparent;color:#fff;border-radius:999px;padding:6px 12px;margin:4px 4px 0 0;font:500 12.5px var(--font-sans,Inter,sans-serif);cursor:pointer}
.pb-acts{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
.pb-acts .lcf--txt{background:transparent;color:#fff;border-color:rgba(255,255,255,.35)}
.pb-empty{font-size:13.5px;color:rgba(255,255,255,.75);margin:0}
'''

JS = r'''
// ---------- Trip Builder, 17 Oct ----------
const PB_INT={ancient:["valley","giza","abu","alex"],beach:["redsea","ras"],luxury:["nile"],adventure:["white","siwa","redsea"],culture:["food","nile","alex"],food:["food"],nature:["ras","siwa","white"]};
const PB_LABEL={ancient:"Ancient",beach:"Beach",luxury:"Luxury",adventure:"Adventure",culture:"Culture",food:"Food",nature:"Nature"};
let pbPriv=false;
function pbInit(){
  const p=document.querySelector("#live #planner"); if(!p||p.dataset.pb) return; p.dataset.pb=1;
  const form=p.querySelector("[data-builder]"), seg=p.querySelector("[data-b-style]"), itin=p.querySelector(".itin");
  // step 04: a question the trips can answer
  const step=seg.closest("fieldset"); step.querySelector("legend").innerHTML='<span>04</span> Private or shared?';
  step.insertAdjacentHTML("beforeend",'<div class="pb-priv" role="group" aria-label="Private or shared"><button type="button" data-pbpriv="0" aria-pressed="true">Either</button><button type="button" data-pbpriv="1" aria-pressed="false">Private only</button></div><p class="pb-hint">Every trip shows whether it has a private option.</p>');
  step.addEventListener("click",e=>{ const b=e.target.closest("[data-pbpriv]"); if(!b) return; pbPriv=b.dataset.pbpriv==="1"; step.querySelectorAll("[data-pbpriv]").forEach(x=>x.setAttribute("aria-pressed",String(x===b))); pbRender(); });
  const ctas=itin.querySelector(".itin__ctas"); (ctas||itin).insertAdjacentHTML(ctas?"beforebegin":"beforeend",'<div class="pb-out"></div>'); if(ctas) ctas.hidden=true;
  form.addEventListener("change",()=>setTimeout(pbRender,0)); form.addEventListener("input",()=>setTimeout(pbRender,0));
  itin.addEventListener("click",e=>{ const a=e.target.closest("[data-pbadd]"); if(a){ const r=p.querySelector("[data-b-days]"); r.value=Math.min(+r.max,+r.value+(+a.dataset.pbadd)); form.dispatchEvent(new Event("change",{bubbles:true})); return; }
    const d=e.target.closest("[data-pbdest]"); if(d){ const c=p.querySelector(`input[name=dest][value="${d.dataset.pbdest}"]`); if(c){ c.checked=true; form.dispatchEvent(new Event("change",{bubbles:true})); } return; }
    const t=e.target.closest("[data-pbtrip]"); if(t&&!e.target.closest("[data-fav]")){ current=t.dataset.pbtrip; lastViewed=current; go(3); } });
  pbRender();
}
function pbRender(){
  const p=document.querySelector("#live #planner"); if(!p) return; const out=p.querySelector(".pb-out"); if(!out) return;
  const dests=[...p.querySelectorAll("input[name=dest]:checked")].map(i=>i.value);
  const ints=[...p.querySelectorAll("input[name=interest]:checked")].map(i=>i.value);
  const nights=+p.querySelector("[data-b-days]").value;
  const onRoute=[...p.querySelectorAll(".itin__stop .itin__name")].map(n=>n.textContent.trim());
  const routeIds=dests.filter(id=>onRoute.includes(DESTS[id].name));
  const dropped=dests.filter(id=>!routeIds.includes(id));
  let h="";
  if(dropped.length) h+=`<p class="pb-note">${dests.length} places don’t fit in ${nights} ${nights===1?"night":"nights"}: ${dropped.map(id=>DESTS[id].name).join(", ")} ${dropped.length===1?"was":"were"} left out.<button type="button" data-pbadd="${dests.length-nights}">Add ${dests.length-nights} ${dests.length-nights===1?"night":"nights"}</button></p>`;
  const why=t=>ints.filter(i=>(PB_INT[i]||[]).includes(t.id));
  let trips=TRIPS.filter(t=>WORLDS[t.id]&&(TRIP_DESTS[t.id]||[]).some(d=>routeIds.includes(d)));
  const hiddenByPriv=pbPriv?trips.filter(t=>!t.priv).length:0;
  if(pbPriv) trips=trips.filter(t=>t.priv);
  trips.sort((a,b)=>why(b).length-why(a).length);
  if(routeIds.length){
    h+=`<div class="pb-res"><h3>Trips on your route</h3><p>${trips.length?`${trips.length} ${trips.length===1?"trip":"trips"}${ints.length?", the ones that match your interests first":""}. Each opens its page, where the options are compared.`:""}${hiddenByPriv?` ${hiddenByPriv} without a private option ${hiddenByPriv===1?"is":"are"} hidden.`:""}</p>`;
    h+=trips.length?`<ul class="pb-list">${trips.map(t=>{ const w=why(t); return `<li><button type="button" class="pb-card" data-pbtrip="${t.id}"><img src="${IMG[t.k]}" alt=""><span style="margin:0"><b>${esc(t.t)}</b><span>${esc((t.places||[]).join(" · "))} · ${esc(t.time)} · ${t.n} options${t.priv?" · Private option":""}</span><em>✓ Free cancellation</em>${w.length?`<i>${w.map(x=>PB_LABEL[x]).join(" · ")}</i>`:""}</span></button></li>`; }).join("")}</ul>`:`<p class="pb-empty">No trip on this route has a private option yet. Choose “Either” to see them all.</p>`;
    // interests with nothing on the route: suggest the places that have them
    const miss=ints.filter(i=>!trips.some(t=>(PB_INT[i]||[]).includes(t.id)));
    const sugg=miss.map(i=>{ const ok=tid=>!pbPriv||TRIPS.find(t=>t.id===tid)?.priv; const ids=[...new Set((PB_INT[i]||[]).filter(ok).flatMap(tid=>TRIP_DESTS[tid]||[]))].filter(d=>!routeIds.includes(d)); return ids.length?`<div>${PB_LABEL[i]}: ${ids.map(d=>`<button type="button" data-pbdest="${d}">+ ${esc(DESTS[d].name)}</button>`).join("")}</div>`:""; }).join("");
    const noPriv=pbPriv?miss.filter(i=>!(PB_INT[i]||[]).some(tid=>TRIPS.find(t=>t.id===tid)?.priv)).map(i=>PB_LABEL[i]):[];
    if(sugg) h+=`<div class="pb-more">Nothing on this route for some of your interests. Add a place:${sugg}</div>`;
    if(noPriv.length) h+=`<p class="pb-more">No ${noPriv.join(" or ").toLowerCase()} trip has a private option yet.</p>`;
    const title=p.querySelector("[data-itin-title]").textContent;
    const key=routeIds.join("-")+"-"+nights;
    h+=`<div class="pb-acts">${window.lcFavBtn?lcFavBtn({id:"plan:"+key,title:title,meta:"Your plan · "+(trips.length?`${trips.length} trips on the route`:"route"),img:IMG["d_"+routeIds[0]]},true).replace("<button ","<button data-off=\" Save this plan\" data-on=\" Plan saved\" "):""}</div></div>`;
  }
  out.innerHTML=h; window.lcFavPaint&&lcFavPaint();
}
window.addEventListener("er-live-ready",()=>setTimeout(pbInit,0));
const _os=window.lcOpenSaved; window.lcOpenSaved=id=>{ if(id.startsWith("plan:")){ go(1); setTimeout(()=>{ const p=document.querySelector("#live #planner"); p&&vp.scrollTo({top:vp.scrollTop+p.getBoundingClientRect().top-vp.getBoundingClientRect().top-70,behavior:"auto"}); },60); return; } _os(id); };
'''

def apply(tpl):
    a = tpl.index('</style>'); tpl = tpl[:a] + CSS + tpl[a:]
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + JS + tpl[i:]
