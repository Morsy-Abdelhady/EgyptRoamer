# Every trip, as a first-time tourist (owner, 17 Oct: "try all the trips"). Each of the 11 trips was opened, read
# to the end, compared and booked, on a computer and a phone (283 screens). Found and fixed:
# 1. three trips never said how long they are on the trip page: White Desert (2 days), Siwa (full day) and the Red
#    Sea dives (1 day). The card had it; the opener and the booking summary did not. They do now, from the card;
# 2. the Red Sea dives are listed under Sharm El Sheikh too, but all three options leave from Hurghada. A tourist
#    staying in Sharm only found out in the option names. The card, the opener and the summary now say
#    "All options leave from Hurghada";
# 3. trip pages never linked to our guides, although 24 guides link to the trips (Valley of the Kings has 8). A
#    trip page now ends with "Read before you go": the guides about that trip, the most specific first;
# 4. Alexandria had nothing after the booking box (no other trip in Alexandria): its guides now fill that end.
CSS = '''
/* ---------- every trip, 17 Oct ---------- */
.wguides{padding:10px 56px 90px}
.wmore + .wguides{padding-top:0;margin-top:-50px}
#wchoose + .wguides{padding-top:70px;border-top:1px solid rgba(255,255,255,.1)}
.wguides h2{font-size:30px;margin:8px 0 18px}
.wguides__row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;max-width:1180px}
.wguides__row button{all:unset;box-sizing:border-box;cursor:pointer;display:grid;grid-template-columns:96px minmax(0,1fr);gap:12px;align-items:center;padding:8px;border-radius:14px;background:rgba(255,255,255,.96);color:#101820}
.wguides__row button:hover,.wguides__row button:focus-visible{outline:2px solid var(--wc,#c9a227)}
.wguides__row img{width:96px;height:72px;object-fit:cover;border-radius:10px;display:block}
.wguides__row b{display:block;font:600 15px/1.25 var(--serif,Georgia,serif)}
.wguides__row span{display:block;font-size:12.5px;color:#5b6168;margin-top:4px}
@container site (max-width:900px){ .wguides{padding-inline:18px} .wguides__row{grid-template-columns:1fr} }
@container site (max-width:600px){ .wguides h2{font-size:25px} .wmore + .wguides{margin-top:-60px} }
'''
JS = r'''
// ---------- every trip, 17 Oct ----------
(function(){
  const DURX=/(\bhours?\b|\bday\b|\bdays\b|\bnight)/i;
  TRIPS.filter(t=>WORLDS[t.id]).forEach(t=>{ const W=WORLDS[t.id];
    if(!(W.chips||[]).some(c=>DURX.test(c))) W.chips=[t.time,...(W.chips||[])];
    if(W.sum&&!W.sum.some(c=>DURX.test(c))) W.sum.splice(1,0,t.time); });
  const rs=TRIPS.find(t=>t.id==="redsea"), W=WORLDS.redsea;
  if(rs&&W&&W.opts.every(o=>/Hurghada/.test(o.n))){ const f="All options leave from Hurghada"; rs.facts=[f,...rs.facts.filter(x=>x!==f)]; W.chips.splice(1,0,f); W.sum.splice(2,0,f); }
})();
// trip pages end with their guides
const _rw3=renderWorld; renderWorld=function(id){ _rw3(id);
  const host=document.querySelector("#vp .wmore")||document.querySelector("#vp #wchoose"); if(!host||typeof GUIDES==="undefined") return;
  const gs=GUIDES.filter(g=>g.trips.includes(id)).sort((a,b)=>(/days-in-egypt|best-time|hidden-gems/.test(a.slug)-/days-in-egypt|best-time|hidden-gems/.test(b.slug))||a.trips.length-b.trips.length).slice(0,3);
  if(!gs.length) return;
  host.insertAdjacentHTML("afterend",`<section class="wguides"><p class="eyebrow">Read before you go</p><h2>Our guides for this trip</h2><div class="wguides__row">${gs.map(g=>`<button type="button" data-guide-open="${g.slug}"><img src="${IMG[g.photo]||g.photo}" alt="" loading="lazy"><span style="margin:0"><b>${esc(g.title)}</b><span>Guide · ${g.mins} min read</span></span></button>`).join("")}</div></section>`); };
'''


def apply(tpl):
    a = tpl.index('</style>'); tpl = tpl[:a] + CSS + tpl[a:]
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + JS + tpl[i:]
