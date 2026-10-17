# Tourist walk-through, 17 Oct (owner: "review the whole journey as a tourist and decide what is best"). Found by
# clicking home -> "Where in Egypt? Luxor" -> the trip -> Book, on desktop and phone:
# 1. one trip, three different lengths: card "6 hours", opener "6 hours", booking "About 4-10 hours" (Giza:
#    "Half day" vs "About 4-8 hours"; Abu Simbel: "Full day" vs "About 8-10 hours"). The booking summary, built from the
#    partners' own options, is now the one length shown everywhere;
# 2. "Our pick" gave no reason. Where none is written, the reason is worked out from the options' own facts
#    (the only private one, the longest visit, the only one with pickup);
# 3. after Book, a prototype toast covered the "opened in a new tab" box. The box says it; the toast is gone;
# 4. after choosing a place, its banner filled the first screen and the trip sat below it. A chosen place gets a
#    short banner, and when it has one or two trips, its guides follow ("Plan your Luxor visit").
# 6. (owner, phone home) "Where in Egypt?" chips were cut at the screen edge and their text sat 5px high. The label
#    gets its own line (on a phone too) and on a phone the 7 places wrap into two rows, all visible; the text is centred.
#    The header "Ask" button is 40px like the icons beside it. It stays in the header on phones: a floating button
#    would sit on the trip page's booking bar and on the text the tourist is reading.
# 7. (owner, trip page) "Trip assistant" and the "Book this trip" bar ended at different right edges (20px from the
#    frame vs 24px from the page, which is left of the scrollbar). On a computer both now end 24px from the page edge.
# 5. (owner, guide page) the trip card stuck 80px under the header, a gap below the section bar, and on a short
#    screen its bottom ran off and the assistant button sat on it. It now sticks 16px under the section bar, and
#    when the screen is short it gives up photo height (then the photo) so the whole card, Save included, stays in
#    view above the assistant button; on a very short screen it scrolls with the page instead of being cut.
CSS = '''
/* ---------- tourist walk-through, 17 Oct ---------- */
#groups.is-one .gbanner{height:118px!important;padding:14px 18px}
#groups.is-one .gbanner h2{font-size:28px}
#groups.is-one .gbanner .vctl{display:none}
.gplan{margin:34px 0 10px}
.gplan h3{font:600 26px/1.2 var(--serif);margin:4px 0 14px}
.gplan .eyebrow{color:var(--gold-deep,#a8871f)}
.gplan__g{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.gplan__g button{all:unset;box-sizing:border-box;cursor:pointer;background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.gplan__g button:hover,.gplan__g button:focus-visible{border-color:var(--gold)}
.gplan__g img{width:100%;aspect-ratio:16/9;object-fit:cover}
.gplan__g b{display:block;padding:12px 14px 4px;font:600 16px/1.3 var(--serif);color:var(--charcoal)}
.gplan__g span{display:block;padding:0 14px 14px;font-size:12.5px;color:var(--muted)}
@container site (max-width:900px){ .gplan__g{grid-template-columns:repeat(2,minmax(0,1fr))} }
@container site (max-width:600px){ .gplan__g{grid-template-columns:1fr} }
.gd .abox > .bk.is-tight .bk__img{height:64px}
.gd .abox > .bk.is-tighter .bk__img{display:none}
.gd .abox > .bk.is-tighter .bk__b{padding:14px 18px 12px}
.gd .abox > .bk.is-tighter h3{margin-bottom:6px}
.gd .abox > .bk.is-tighter .btn{min-height:44px;padding-block:10px}
.gd .abox > .bk.is-static{position:static}
.chip2{display:inline-flex;align-items:center;justify-content:center;line-height:1.1}
.finder2 > span{flex:1 1 100%;margin:0 0 2px}
@container site (max-width:600px){
  .finder2{flex-wrap:wrap;overflow:visible;margin-inline:0;padding-inline:0;gap:8px}
  .finder2 > span{flex:1 1 100%;margin:0 0 2px}
  .chip2{padding:0 14px}
  .lc .nav .lc-ai{height:40px;padding:0 14px 0 11px;gap:7px;line-height:1}
  .lc .nav .lc-ai svg{display:block;flex:none}
}
@container site (min-width:901px){ .lc-ov .assistant-launch.lc-t{inset-inline-end:calc(24px + var(--sbw,0px))!important} }
'''
JS = r'''
// ---------- tourist walk-through, 17 Oct ----------
// 1 · one length per trip: the booking summary's
const DUR=/(\bhours?\b|\bday\b|\bdays\b|\bnight)/i;
Object.entries(WORLDS).forEach(([id,W])=>{ const d=(W.sum||[]).find(x=>DUR.test(x)); if(!d) return;
  const t=TRIPS.find(x=>x.id===id); if(t) t.time=d;
  if(W.chips){ const i=W.chips.findIndex(c=>DUR.test(c)||/^(Half|Full) day$/.test(c)); if(i>=0) W.chips[i]=d; } });
// 2 · a reason for "Our pick" when none is written
const hrs=f=>{ const m=(f.find(x=>/hour|day/i.test(x))||"").match(/(\d+)(?:–(\d+))?\s*(hour|day)/i); if(!m) return 0; const v=+(m[2]||m[1]); return /day/i.test(m[3])?v*24:v; };
Object.values(WORLDS).forEach(W=>{ const p=(W.opts||[]).find(o=>o.pick); if(!p||p.why||W.opts.length<2) return;
  const others=W.opts.filter(o=>o!==p), priv=o=>o.f.some(x=>/^Private$/i.test(x)), pick=o=>o.f.some(x=>/Pickup offered/i.test(x));
  const r=[]; if(priv(p)&&!others.some(priv)) r.push("The only private option"); else if(priv(p)) r.push("Private");
  if(hrs(p.f)&&others.every(o=>hrs(o.f)<hrs(p.f))) r.push(r.length?"the longest visit":"The longest visit");
  if(!r.length&&pick(p)&&!others.some(pick)) r.push("The only one with hotel pickup");
  if(r.length) p.why=r.join(", "); });
// 3 · the booking box says it; no toast on top of it
const _book=book; book=function(spec,silent){ _book(spec,true); };
// 4 · a chosen place: short banner, and its guides when it has few trips
const _rt2=renderTrips; renderTrips=function(){ _rt2(); const g=document.getElementById("groups"); if(!g) return;
  const one=state.place!=="all"; g.classList.toggle("is-one",one); g.querySelector(".gplan")?.remove();
  if(!one||typeof GUIDES==="undefined") return; const did=Object.keys(DESTS).find(k=>DESTS[k].name===state.place); if(!did) return;
  const n=g.querySelectorAll(".card").length, gs=GUIDES.filter(x=>x.dests.includes(did)).slice(0,3); if(n>2||!gs.length) return;
  g.insertAdjacentHTML("beforeend",`<section class="gplan"><p class="eyebrow">Before you book</p><h3>Plan your ${esc(DESTS[did].name)} visit</h3><div class="gplan__g">${gs.map(x=>`<button type="button" data-guide-open="${x.slug}"><img src="${x.photo||IMG["d_"+did]}" alt="" loading="lazy"><b>${esc(x.title)}</b><span>Guide · ${x.mins} min read</span></button>`).join("")}</div></section>`); };
// 5 · the guide's trip card: right under the section bar, and always whole
function bkFit(){ const ga=document.getElementById("ga"), bk=ga&&ga.querySelector(".abox > .bk"); if(!bk||!bk.offsetParent) return;
  bk.classList.remove("is-tight","is-tighter","is-static"); bk.style.top="";
  if(getComputedStyle(bk).position!=="sticky") return;
  const sb=ga.querySelector(".secbar"), top=(parseInt(getComputedStyle(vp).getPropertyValue("--navh"))||72)+(sb?sb.offsetHeight:0)+16;
  bk.style.top=top+"px";
  const fab=document.querySelector(".assistant-launch"), fr=fab&&fab.offsetParent?fab.getBoundingClientRect():null, br=bk.getBoundingClientRect();
  const bottom=vp.getBoundingClientRect().bottom-(fr&&fr.left<br.right?vp.getBoundingClientRect().bottom-fr.top:0)-16, avail=bottom-vp.getBoundingClientRect().top-top;
  if(bk.offsetHeight>avail) bk.classList.add("is-tight"); if(bk.offsetHeight>avail) bk.classList.add("is-tighter");
  if(bk.offsetHeight>avail){ bk.classList.remove("is-tight","is-tighter"); bk.style.top=""; bk.classList.add("is-static"); } }
const _og=window.openGuide; window.openGuide=function(){ const r=_og.apply(this,arguments); requestAnimationFrame(bkFit); return r; };
new ResizeObserver(()=>{ bkFit(); document.documentElement.style.setProperty("--sbw",(vp.offsetWidth-vp.clientWidth)+"px"); }).observe(vp);
'''

def apply(tpl):
    a = tpl.index('</style>'); tpl = tpl[:a] + CSS + tpl[a:]
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + JS + tpl[i:]
