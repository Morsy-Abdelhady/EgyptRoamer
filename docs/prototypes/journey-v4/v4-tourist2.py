# First-time foreign tourist, full review, 17 Oct (owner: "review the whole experience as a typical foreign tourist").
# Walked by real clicks on a computer and a phone (75 screens). Found and fixed:
# 1. search found nothing for "nile cruise" and said nothing: it matched the whole phrase against trip names only.
#    It now matches words (cruise, cruises, pyramid...) across trips, guides and places, groups them, and says so
#    when nothing matches, with a way on (Trip Builder, all trips);
# 2. the assistant answered "Is Egypt safe? 8 days first time" with Luxor and Aswan in broken cards. It now uses the
#    same search, so it answers with the "7 days" and "10 days" itineraries; on safety it says plainly that we have
#    no page on it yet and points to the team chat; every answer is a card with a photo, a line and an action, and
#    the panel opens at the question, not at the bottom of the answer;
# 3. on a computer the floating "Trip assistant" sat on content: the chapter trips' save buttons, the hotel links,
#    the trip's step cards, the booking options and the footer links. It is now an "Ask" button in the header, as on
#    phones: one place on every page and every size, covering nothing;
# 4. on a trip page the "Book this trip" bar covered the bottom of the step cards and the video's pause button;
#    both now sit above it;
# 5. the menu promised "a day-by-day plan for your dates"; the Trip Builder drafts a route by nights, with the trips
#    on it. The menu now says so, under the same name as the footer ("Trip Builder");
# 6. "Best time to visit" showed "Trips on this route · 2 trips for this plan"; it is not a route. Only the day-by-day
#    itineraries say "route"; the other guides say "Trips in this guide";
# 7. the Travel Guide index showed the same photo on neighbouring guides (Coptic Cairo and Inside the Pyramids, ...).
#    A guide whose photo is already used takes the next of its own photos;
# 8. "Trips & tours" opened every place with a 190px video banner, even for one trip: 7 banners for 10 trips. The
#    banners are shorter, so the trips come first.
CSS = '''
/* ---------- first-time tourist review, 17 Oct ---------- */
.lc .assistant-launch{display:none!important}
@container site (min-width:601px){
  .lc .nav .lc-ai{display:inline-flex;align-items:center;gap:7px;height:40px;padding:0 15px 0 12px;border-radius:999px;border:1px solid currentColor;background:transparent;color:inherit;font:600 14px/1 var(--font-sans,Inter,sans-serif);cursor:pointer;opacity:.95}
  .lc .nav .lc-ai svg{width:18px;height:18px;display:block;flex:none}
  .lc .nav .lc-ai span{display:inline}
  .lc .nav .lc-ai:hover{opacity:1;background:rgba(255,255,255,.08)}
}
@container site (min-width:601px){ .jstep{padding-bottom:104px} .journey2 .vctl{bottom:96px!important} }
#groups .gbanner{height:132px}
@container site (max-width:600px){ #groups .gbanner{height:128px} }
.lc .search__results.has-q > :not(.lcs-trips){display:none!important}
.lc .lcs-empty{padding:14px 18px 6px;font-size:14px;color:#4e555c;margin:0}
.lc .lcs-empty b{color:#101820}
.lc .lcs-acts{display:flex;flex-wrap:wrap;gap:8px;padding:8px 18px 16px}
.lc .lcs-acts button{border:1px solid rgba(16,24,32,.18);background:#fff;border-radius:999px;padding:8px 14px;font:500 13.5px var(--font-sans,Inter,sans-serif);cursor:pointer;min-height:40px;color:#101820}
.lc .lcs-trips .search__item small{display:block}
.lc .lca-note{margin:0 0 10px;padding:10px 12px;border-radius:12px;background:#fff6dd;border:1px solid #ecd9a0;font-size:13.5px;line-height:1.45;color:#3d3a2e}
.lc .lca-note button{all:unset;cursor:pointer;color:#a8871f;font-weight:600;text-decoration:underline}
'''

JS = r'''
// ---------- first-time tourist review, 17 Oct ----------
// one search for the search box and the assistant: words, not the whole phrase; trips, guides and places
(function(){
  const STOP=new Set("egypt egyptian the and for with what where when how is are was it its to of in on at a an i me my we our you your can could should would do does did be will want need get go going any some about from this that there here please trip trips tour tours day days night nights week weeks".split(" "));
  const stem=w=>w.replace(/(ings|ing|es|s)$/,"").slice(0,7);
  const words=q=>q.toLowerCase().replace(/[’']/g,"").split(/[^a-z0-9]+/).filter(w=>w.length>2&&!STOP.has(w)&&!/^\d+$/.test(w));
  const score=(ws,fields)=>ws.reduce((s,w)=>{ const st=stem(w); let best=0; fields.forEach(([txt,wt])=>{ if(txt&&txt.toLowerCase().includes(st)) best=Math.max(best,wt); }); return s+best; },0);
  window.lcFind=q=>{
    const ws=words(q), days=(q.match(/(\d+)\s*(day|night)/i)||[])[1];
    const trips=TRIPS.filter(t=>WORLDS[t.id]).map(t=>{ const W=WORLDS[t.id]; return {t,s:score(ws,[[t.t,3],[(t.places||[]).join(" ")+" "+(TRIP_DESTS[t.id]||[]).map(d=>DESTS[d]?.name).join(" "),2],[(W.sum||[]).concat(W.chips||[]).join(" ")+" "+(W.kicker||""),1]])}; });
    const guides=(typeof GUIDES!=="undefined"?GUIDES:[]).map(g=>({g,s:score(ws,[[g.title,3],[g.dests.map(d=>DESTS[d]?.name).join(" "),2],[g.excerpt,1]])+(days&&/days-in-egypt/.test(g.slug)?4:0)}));
    const places=Object.entries(DESTS).map(([id,d])=>({id,d,s:score(ws,[[d.name,3],[d.tag||d.tagline||"",1]])}));
    const top=(a,n)=>a.filter(x=>x.s>=2||(ws.length===1&&x.s>=1)).sort((x,y)=>y.s-x.s).slice(0,n);
    return {ws,days,trips:top(trips,4),guides:top(guides,4),places:top(places,3),safety:/\b(safe|safety|security|danger|dangerous|scam|scams)\b/i.test(q)};
  };
  window.lcCards=r=>[
    ...r.trips.map(({t,s})=>({s,img:IMG[t.k],title:t.t,meta:`${(t.places||[]).join(" · ")} · ${t.time} · ${t.n} options`,cta:"See the trip",free:true,run:()=>{ current=t.id; lastViewed=t.id; go(3); }})),
    ...r.guides.map(({g,s})=>({s,img:IMG[g.photo]||g.photo,title:g.title,meta:`Guide · ${g.mins} min read`,cta:"Read the guide",run:()=>openGuide(g.slug)})),
    ...r.places.map(({d,s})=>({s,img:IMG["d_"+Object.keys(DESTS).find(k=>DESTS[k]===d)],title:d.name,meta:d.tag||d.tagline||"Place",cta:"See its trips",run:()=>lcPlace(d.name)}))].sort((a,b)=>(b.s||0)-(a.s||0));
})();
// guides whose photo is already used by an earlier guide take their next own photo
(function(){ if(typeof GUIDES==="undefined") return; const used=new Set();
  GUIDES.forEach(g=>{ const own=[g.photo,...Object.values(g.figs||{}).map(f=>f[0]),...Object.values(g.strips||{}).flatMap(s=>(s[1]||[]).map(p=>p[0]))].filter(Boolean).map(p=>IMG[p]||p);
    const pick=own.find(p=>!used.has(p))||own[0]; if(pick){ g.photo=pick; used.add(pick); } }); })();
'''

SEARCH_OLD = '''const list=(window.lcIndex?window.lcIndex():[]).filter(x=>x.card&&(!q||x.t.toLowerCase().includes(q))).slice(0,q?6:4);
    box.innerHTML=list.length?`<p class="search__group">${q?"Trips":"Popular trips"}</p>`:""; list.forEach(x=>{ const a=document.createElement("a"); a.href="#"; a.className="search__item"; a.innerHTML=`<img src="${x.card.img}" alt=""><span><b>${x.card.title}</b><small>${x.card.meta}</small></span>`; a.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); window.lcClose(); x.run&&x.run(); }); box.appendChild(a); }); };'''
SEARCH_NEW = '''res.classList.toggle("has-q",!!q); box.innerHTML="";
    const item=c=>{ const a=document.createElement("a"); a.href="#"; a.className="search__item"; a.innerHTML=`<img src="${c.img}" alt=""><span><b>${esc(c.title)}</b><small>${esc(c.meta)}</small></span>`; a.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); window.lcClose(); c.run(); }); return a; };
    const group=(label,cards)=>{ if(!cards.length) return; const p=document.createElement("p"); p.className="search__group"; p.textContent=label; box.appendChild(p); cards.forEach(c=>box.appendChild(item(c))); };
    if(!q){ group("Popular trips",lcCards({trips:TRIPS.filter(t=>WORLDS[t.id]).slice(0,4).map(t=>({t})),guides:[],places:[]})); return; }
    const r=lcFind(q); group("Trips",lcCards({trips:r.trips,guides:[],places:[]})); group("Guides",lcCards({trips:[],guides:r.guides,places:[]})); group("Places",lcCards({trips:[],guides:[],places:r.places}));
    if(!box.children.length){ box.innerHTML=`<p class="lcs-empty">Nothing on Egypt Roamer matches <b>“${esc(q)}”</b> yet. Try a place or a sight, like Luxor or pyramids.</p><div class="lcs-acts"><button type="button" data-lcs="2">See all trips</button><button type="button" data-lcs="g">Travel Guide</button><button type="button" data-lcs="p">Trip Builder</button></div>`;
      box.querySelectorAll("[data-lcs]").forEach(b=>b.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); window.lcClose(); const k=b.dataset.lcs; if(k==="2") go(2); else if(k==="g") openGuides("all"); else toPlanner(); })); } };'''

ANSWER_NEW = r'''  const answer=q=>{ const r=lcFind(q); const cards=lcCards(r).slice(0,5);
    const me=document.createElement("p"); me.className="lca-q"; me.textContent=q;
    const box=document.createElement("div"); box.className="lca-a";
    if(r.safety) box.insertAdjacentHTML("beforeend",`<p class="lca-note">We have no page on safety yet, so we won’t guess. Our team can answer that: <button type="button" data-chat-open>chat with Egypt Roamer</button>.</p>`);
    box.insertAdjacentHTML("beforeend",`<p>${cards.length?(r.days?`Pages that fit a trip of about ${r.days} days:`:I18N.intro):(r.safety?"":"Nothing on Egypt Roamer matches that yet. Try a place:")}</p>`);
    cards.forEach(h=>{ const c=document.createElement("button"); c.type="button"; c.className="lca-card";
      c.innerHTML=`<img src="${h.img}" alt=""><div><b>${esc(h.title)}</b><span>${esc(h.meta)}</span>${h.free?"<em>✓ Free cancellation</em>":""}<i>${h.cta} →</i></div>`;
      c.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); closeAll(); h.run(); }); box.appendChild(c); });
    if(r.days&&typeof toPlanner==="function"){ const b=document.createElement("button"); b.type="button"; b.className="lca-all"; b.textContent=`Draft ${/^(8|11|18)$/.test(r.days)?"an":"a"} ${r.days}-day route in the Trip Builder →`; b.addEventListener("click",()=>{ closeAll(); toPlanner(); setTimeout(()=>{ const s=document.querySelector("#live #planner [data-b-days]"); if(s){ s.value=Math.max(+s.min,Math.min(+s.max,+r.days-1)); s.dispatchEvent(new Event("input",{bubbles:true})); s.dispatchEvent(new Event("change",{bubbles:true})); } },400); }); box.appendChild(b); }
    if(!cards.length&&!r.safety){ const ch=document.createElement("div"); ch.className="lca-chips"; PLACES.forEach(p=>{ const b=document.createElement("button"); b.type="button"; b.textContent=p; b.addEventListener("click",()=>answer(p)); ch.appendChild(b); }); box.appendChild(ch); }
    log.append(me,box); log.scrollTop=Math.max(0,me.offsetTop-log.offsetTop-12); };'''


def apply(tpl):
    a = tpl.index('</style>'); tpl = tpl[:a] + CSS + tpl[a:]
    # search
    assert tpl.count(SEARCH_OLD) == 1
    tpl = tpl.replace(SEARCH_OLD, SEARCH_NEW)
    tpl = tpl.replace('placeholder="Search places and trips"', 'placeholder="Search places, trips and guides"')
    # assistant
    i = tpl.index('  const answer=q=>{ const words=')
    e = tpl.index('log.append(me,box); log.scrollTop=log.scrollHeight; };', i) + len('log.append(me,box); log.scrollTop=log.scrollHeight; };')
    tpl = tpl[:i] + ANSWER_NEW + tpl[e:]
    # menu
    a = 'Plan My Trip<small>Build a day-by-day plan for your dates</small>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, 'Trip Builder<small>Choose nights and places; get a route and the trips on it</small>')
    # guide labels: "route" only for the day-by-day itineraries
    a = '<small>Trips on this route</small><h3>${trips.length} trips for this plan</h3>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '<small>${/days-in-egypt/.test(A.slug)?"Trips on this route":"Trips in this guide"}</small><h3>${trips.length} ${/days-in-egypt/.test(A.slug)?"trips for this plan":(trips.length===1?"trip mentioned here":"trips mentioned here")}</h3>')
    a = '<h2>Trips on this route</h2>'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '<h2>${/days-in-egypt/.test(A.slug)?"Trips on this route":"Trips in this guide"}</h2>')
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + JS + tpl[i:]
