# Owner, 17 Oct, after looking at the live chrome in the prototypes:
# 1. the phone menu covered the header, so nothing closed it -> the menu opens under the header and ☰ turns into ✕;
# 2. no search on phones (also true on the live site) -> 🔍 in the header at every size, and search first in the menu;
# 3. the menu's "Plan My Trip" differed from the header's button -> one main button everywhere ("Choose a trip",
#    "Book this trip" inside a trip); Plan My Trip stays as a plain menu link;
# 4. "Destinations" and "Experiences" were unclear -> "Places" and "Trips & tours", each with a line saying what it holds;
# 5. the assistant answered with bare lines -> trip cards (photo, length, options, free cancellation, a button), and
#    place suggestions when nothing matches.
# This module wraps chrome.py; both prototypes load it instead.
import re, os, importlib.util
spec = importlib.util.spec_from_file_location('chrome', os.path.join(os.path.dirname(os.path.abspath(__file__)), 'chrome.py'))
CH = importlib.util.module_from_spec(spec); spec.loader.exec_module(CH)

ICON = lambda i: f'<svg class="icon" aria-hidden="true"><use href="#{i}"></use></svg>'
LINKS = [
    ('https://egyptroamer.com/destinations/', 'Places', 'Cairo, Luxor, Aswan, Alexandria, the Red Sea and the desert'),
    ('https://egyptroamer.com/experiences/', 'Trips &amp; tours', 'Tours and day trips you can book'),
    ('https://egyptroamer.com/guides/', 'Travel Guide', 'Plans, tips and what to know before you go'),
]

def nav_html(cta_attrs='data-href="https://egyptroamer.com/experiences/"'):
    n = CH.nav_html()
    n = n.replace('data-href="https://egyptroamer.com/destinations/">Destinations</a>', 'data-href="https://egyptroamer.com/destinations/">Places</a>', 1)
    n = n.replace('data-href="https://egyptroamer.com/experiences/">Experiences</a>', 'data-href="https://egyptroamer.com/experiences/">Trips &amp; tours</a>', 1)
    m = re.search(r'<a href="#planner" class="btn btn--primary btn--sm nav__cta"[^>]*>.*?</a>', n, re.S)
    assert m, 'nav cta'
    n = n[:m.start()] + f'<a href="#" {cta_attrs} class="btn btn--primary btn--sm nav__cta">Choose a trip {ICON("i-arrow")}</a>' + n[m.end():]
    assert 'Places</a>' in n and 'Trips &amp; tours</a>' in n
    return n

def menu_html():
    langs = re.search(r'<div class="menu__lang".*?</div>', CH.CAP['html']['menu'], re.S).group(0)
    links = ''.join(f'<li><a href="#" data-href="{h}">{t}<small>{s}</small></a></li>' for h, t, s in LINKS)
    links += '<li><a href="#" data-href="https://egyptroamer.com/#planner">Plan My Trip<small>Build a day-by-day plan for your dates</small></a></li>'
    return (f'<nav class="menu lc-t" id="menu" aria-label="Primary" hidden><div class="menu__inner">'
            f'<button type="button" class="lcm-search" data-open="search">{ICON("i-search")}<span>Search places, trips and guides</span></button>'
            f'<ul class="menu__links lcm-links">{links}</ul>'
            f'<a href="#" class="btn btn--primary btn--lg btn--block lcm-cta">Choose a trip {ICON("i-arrow")}</a>'
            + CH.clean(langs) + '</div></nav>')

def overlays_html():
    o = CH.overlays_html()
    a = o.index('<nav class="lc-t menu"'); e = o.index('</nav>', a) + len('</nav>')
    return o[:a] + menu_html() + o[e:]

def footer_html():
    f = CH.footer_html()
    return f.replace('>Destinations</a>', '>Places</a>', 1).replace('>Experiences</a>', '>Trips &amp; tours</a>', 1)

CSS = CH.css() + '''
/* ---------- chrome, owner review 17 Oct ---------- */
/* the menu opens under the header, so the ✕ in the header closes it */
.lc-ov .menu{top:var(--navb,64px)!important;padding-top:20px!important}
.lc .nav__burger.is-open{background:rgba(255,255,255,.12)}
/* search at every size */
.lc .nav__actions [data-open="search"]{display:inline-grid!important}
/* menu: search first, then the sections with a line each, then the one main button */
.lc .lcm-search{display:flex;align-items:center;gap:12px;width:100%;min-height:52px;padding:0 16px;margin:0 0 18px;border-radius:14px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);color:#fff;font:500 16px/1.2 var(--font-sans,Inter,sans-serif);text-align:left;cursor:pointer}
.lc .lcm-search .icon{width:20px;height:20px;opacity:.8}
.lc .lcm-links a{display:block;padding:12px 0!important;line-height:1.1}
.lc .lcm-links small{display:block;margin-top:6px;font:400 14px/1.35 var(--font-sans,Inter,sans-serif);color:rgba(255,255,255,.62);letter-spacing:0}
.lc .lcm-links li:last-child a{font:600 17px/1.2 var(--font-sans,Inter,sans-serif)!important;letter-spacing:0;padding:16px 0!important}
.lc .lcm-links li:last-child small{font-size:13.5px}
/* the header button stays outlined; gold fill is kept for the main action inside the page (approved 16 Oct) */
.lc .nav .nav__cta{background:transparent!important;border:1.5px solid #c9a227!important;color:inherit!important;box-shadow:none!important}
.lc .nav[data-state="light"] .nav__cta{color:#101820!important}
/* assistant: the card's action on its own line; place chips in the welcome; no second row of suggestions */
.lc .lca-card em,.lc .lca-card i{display:block;margin-left:0}
.lc .lca-card i{margin-top:4px}
.lc .assistant__welcome .lca-chips{margin-top:12px}
.lc .assistant__try{display:none!important}
.lc .lca-all{display:block;width:100%;text-align:left;border:1px dashed rgba(16,24,32,.25);background:transparent;border-radius:12px;padding:12px 14px;font:600 14px var(--font-sans,Inter,sans-serif);color:#a8871f;cursor:pointer;min-height:44px}
/* assistant: the conversation scrolls inside its own area, never under the question box */
.lc .assistant .drawer__panel{display:flex!important;flex-direction:column!important}
.lc .assistant__log{flex:1 1 auto!important;min-height:140px;overflow-y:auto!important}
.lc .assistant__foot{flex:none}
.lc .assistant.is-chat .assistant__form,.lc .assistant.is-chat .assistant__foot > .assistant__note{display:none!important}
/* team chat: the form takes the whole panel and scrolls, so "Start chat" and "Cancel" are always reachable */
.lc .assistant.is-chat .assistant__log{display:none!important}
.lc .assistant.is-chat .assistant__foot{flex:1 1 auto!important;min-height:0;overflow-y:auto!important;border-top:0!important}
.lc .assistant__foot{max-height:75%;overflow-y:auto}
/* ---------- saved trips and places (owner, 17 Oct: "what is the heart for if I cannot save a trip?") ---------- */
.lcf{display:inline-grid;place-items:center;width:40px;height:40px;border-radius:50%;border:1px solid rgba(16,24,32,.15);background:rgba(255,255,255,.92);color:#101820;cursor:pointer;padding:0;transition:transform .15s}
.lcf svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:1.8}
.lcf[aria-pressed="true"]{color:#c0392b;border-color:rgba(192,57,43,.35)}
.lcf[aria-pressed="true"] svg{fill:currentColor}
.lcf:active{transform:scale(.9)}
.lcfw{position:relative;display:flex;min-width:0}
.lcfw > .card,.lcfw > .mini{flex:1;min-width:0}
.lcfw > .lcf{position:absolute;top:10px;right:10px;z-index:3}
.lcfw > .mini + .lcf{top:50%;transform:translateY(-50%);right:8px;width:34px;height:34px}
.lcf--txt{width:auto;padding:0 16px;gap:8px;grid-auto-flow:column;font:600 14px/1 var(--font-sans,Inter,sans-serif);border-radius:999px;height:44px}
.lc .lcs-item{display:grid;grid-template-columns:72px minmax(0,1fr) auto;gap:12px;align-items:center;background:#fff;border:1px solid rgba(16,24,32,.1);border-radius:14px;padding:8px}
.lc .lcs-item img{width:72px;height:56px;object-fit:cover;border-radius:10px}
.lc .lcs-item b{display:block;font:600 15px/1.25 var(--font-serif,Georgia,serif);color:#101820}
.lc .lcs-item small{display:block;font-size:12.5px;color:#4e555c;margin-top:3px}
.lc .lcs-item button.lcs-open{all:unset;cursor:pointer;display:block;min-width:0}
.lc .lcs-note{font-size:13px;color:#4e555c;margin:4px 0 0}
.lc .icon-btn__badge:empty{display:none}
.lc .icon-btn__badge:not(:empty){transform:scale(1)!important}
.lcf--txt{display:inline-flex!important;align-items:center;justify-content:center}
/* footer logo: a signature, not a banner (owner, 17 Oct: "the logo is huge") */
.lc .footer__logo{width:min(100%,260px)!important}
/* search: the prototype's trips first, then places; the list scrolls inside the panel */
.lc .search__results{max-height:min(70cqh,560px);overflow-y:auto}
.lc .lcs-trips .search__item{display:flex;gap:12px;align-items:center}
.lc .lcs-trips img{width:56px;height:42px;object-fit:cover;border-radius:8px}
.lc .lcm-cta{margin-top:22px}
/* assistant answers */
.lc .lca-q{align-self:flex-end;margin:14px 0 8px auto;max-width:80%;width:max-content;background:#101820;color:#fff;border-radius:16px 16px 4px 16px;padding:10px 14px;font-size:14.5px}
.lc .lca-a{margin:0 0 6px;font-size:14.5px;color:#2c3640}
.lc .lca-a > p{margin:0 0 10px}
.lc .lca-card{display:grid;grid-template-columns:76px minmax(0,1fr);gap:12px;align-items:center;background:#fff;border:1px solid rgba(16,24,32,.1);border-radius:14px;padding:8px;margin:0 0 8px;text-decoration:none;color:#101820;cursor:pointer;text-align:left;width:100%;font:inherit}
.lc .lca-card:hover{border-color:#c9a227}
.lc .lca-card img{width:76px;height:64px;object-fit:cover;border-radius:10px;display:block}
.lc .lca-card b{display:block;font:600 15px/1.25 var(--font-serif,Georgia,serif)}
.lc .lca-card span{display:block;font-size:12.5px;color:#4e555c;margin-top:3px}
.lc .lca-card em{display:inline-block;font-style:normal;font-size:12px;color:#145c4c;font-weight:600;margin-top:4px}
.lc .lca-card i{font-style:normal;font-size:12.5px;font-weight:600;color:#a8871f;margin-left:6px}
.lc .lca-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.lc .lca-chips button{border:1px solid rgba(16,24,32,.18);background:#fff;border-radius:999px;padding:8px 12px;font:500 13px var(--font-sans,Inter,sans-serif);cursor:pointer;min-height:36px}
'''

JS = CH.JS.replace(
    # remember where the header ends, so the menu opens under it
    'const paint=()=>{ const r=nav.getBoundingClientRect();',
    'const setNavB=()=>ov.style.setProperty("--navb",(nav.getBoundingClientRect().bottom-ov.getBoundingClientRect().top)+"px");\n  const paint=()=>{ setNavB(); const r=nav.getBoundingClientRect();'
).replace(
    # ☰ turns into ✕ while the menu is open
    'if(id==="menu"){ const b=nav.querySelector(".nav__burger"); b&&b.setAttribute("aria-expanded","true"); b&&b.classList.add("is-open"); }',
    'if(id==="menu"){ setNavB(); const b=nav.querySelector(".nav__burger"); if(b){ b.setAttribute("aria-expanded","true"); b.classList.add("is-open"); b.setAttribute("aria-label","Close menu"); b.dataset.icon=b.dataset.icon||b.innerHTML; b.innerHTML=\'<svg class="icon" aria-hidden="true"><use href="#i-close"></use></svg>\'; } }'
).replace(
    'const b=nav.querySelector(".nav__burger"); if(b){ b.setAttribute("aria-expanded","false"); b.classList.remove("is-open"); } };',
    'const b=nav.querySelector(".nav__burger"); if(b){ b.setAttribute("aria-expanded","false"); b.classList.remove("is-open"); b.setAttribute("aria-label","Open menu"); if(b.dataset.icon) b.innerHTML=b.dataset.icon; } };'
)
# assistant: cards instead of bare lines
i = JS.index('  const answer=q=>{'); e = JS.index('log.append(me,box); log.scrollTop=log.scrollHeight; };', i) + len('log.append(me,box); log.scrollTop=log.scrollHeight; };')
JS = JS[:i] + r'''  const PLACES=["Cairo & Giza","Luxor","Aswan","Alexandria","Red Sea","Western Desert"];
  const answer=q=>{ const words=q.toLowerCase().split(/[^a-z0-9&]+/).filter(w=>w.length>2&&!["the","and","for","with","what","where","trip","trips","tour","tours"].includes(w));
    const all=index(); let hits=all.filter(i=>i.card&&words.some(w=>i.t.toLowerCase().includes(w)));
    if(!hits.length) hits=all.filter(i=>words.some(w=>i.t.toLowerCase().includes(w)));
    hits=hits.slice(0,4);
    const me=document.createElement("p"); me.className="lca-q"; me.textContent=q;
    const box=document.createElement("div"); box.className="lca-a"; box.innerHTML=`<p>${hits.length?I18N.intro:"Nothing on Egypt Roamer matches that yet. Try a place:"}</p>`;
    hits.forEach(h=>{ const c=document.createElement("button"); c.type="button"; c.className="lca-card";
      c.innerHTML=h.card?`<img src="${h.card.img}" alt=""><div><b>${h.card.title}</b><span>${h.card.meta}</span><em>✓ Free cancellation</em><i>${h.card.cta} →</i></div>`:`<span style="grid-column:1/-1">${h.html||h.t}</span>`;
      c.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); closeAll(); if(h.run) h.run(); else if(h.href&&window.lcRoute) lcRoute(h.href); }); box.appendChild(c); });
    const pl=PLACES.find(p=>words.some(w=>p.toLowerCase().includes(w))); if(pl&&window.lcPlace){ const b=document.createElement("button"); b.type="button"; b.className="lca-all"; b.textContent=`See all ${pl} trips →`; b.addEventListener("click",()=>{ closeAll(); lcPlace(pl); }); box.appendChild(b); }
    if(!hits.length){ const ch=document.createElement("div"); ch.className="lca-chips"; PLACES.forEach(p=>{ const b=document.createElement("button"); b.type="button"; b.textContent=p; b.addEventListener("click",()=>answer(p)); ch.appendChild(b); }); box.appendChild(ch); }
    log.append(me,box); log.scrollTop=log.scrollHeight; };''' + JS[e:]
# the menu's main button mirrors the header's main button
JS += r'''
// saved trips and places: kept in this browser only
(function(){
  const KEY="er-proto-saved"; let saved=[]; try{ saved=JSON.parse(localStorage.getItem(KEY)||"[]"); }catch(e){}
  const store=()=>{ try{ localStorage.setItem(KEY,JSON.stringify(saved)); }catch(e){} };
  const HEART='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 8 3.6 4.5 7.2 4.5c2 0 3.6 1.1 4.8 2.8 1.2-1.7 2.8-2.8 4.8-2.8 3.6 0 5.8 3.5 4.5 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
  const has=id=>saved.some(x=>x.id===id);
  const badge=()=>{ document.querySelectorAll("[data-fav-count]").forEach(b=>{ const t=String(saved.length||""); if(b.textContent!==t) b.textContent=t; }); };
  const paintAll=()=>{ document.querySelectorAll("[data-fav]").forEach(b=>{ const on=has(b.dataset.fav); b.setAttribute("aria-pressed",String(on)); b.setAttribute("aria-label",(on?"Remove from saved: ":"Save: ")+b.dataset.favTitle); if(b.classList.contains("lcf--txt")){ const t=on?" Saved":" Save"; if(b.lastChild.textContent!==t) b.lastChild.textContent=t; } }); badge(); };
  const list=document.querySelector("#lc-ov [data-saved-list]");
  const drawList=()=>{ if(!list) return; if(!saved.length){ list.innerHTML=`<div class="drawer__empty"><svg class="icon" aria-hidden="true"><use href="#i-heart"></use></svg><p>Tap the heart on any trip or place to keep it here while you plan.</p><a class="btn btn--dark btn--sm" href="#" data-close data-go="2">Browse trips</a></div>`; return; }
    list.innerHTML=""; saved.forEach(x=>{ const r=document.createElement("div"); r.className="lcs-item"; r.innerHTML=`<img src="${x.img}" alt=""><button type="button" class="lcs-open"><b>${x.title}</b><small>${x.meta}</small></button><button type="button" class="lcf" data-fav="${x.id}" data-fav-title="${x.title}" aria-pressed="true">${HEART}</button>`;
      r.querySelector(".lcs-open").addEventListener("click",()=>{ window.lcClose(); window.lcOpenSaved&&lcOpenSaved(x.id); }); list.appendChild(r); });
    const n=document.createElement("p"); n.className="lcs-note"; n.textContent="Saved in this browser only."; list.appendChild(n); };
  window.lcFavBtn=(item,txt)=>`<button type="button" class="lcf${txt?" lcf--txt":""}" data-fav="${item.id}" data-fav-title="${item.title.replace(/"/g,"&quot;")}" data-fav-item='${JSON.stringify(item).replace(/'/g,"&#39;")}' aria-pressed="${has(item.id)}">${HEART}${txt?"<span> Save</span>":""}</button>`;
  window.lcFavPaint=paintAll;
  document.addEventListener("click",e=>{ const b=e.target.closest("[data-fav]"); if(!b) return; e.preventDefault(); e.stopPropagation();
    const id=b.dataset.fav; if(has(id)){ saved=saved.filter(x=>x.id!==id); lcToast("Removed from saved"); } else { let it={}; try{ it=JSON.parse(b.dataset.favItem||"{}"); }catch(_){} if(!it.id) return; saved.unshift(it); lcToast(`Saved “${it.title}”. Find it under ♡ at the top.`); }
    store(); paintAll(); drawList(); }, true);
  document.addEventListener("click",e=>{ if(e.target.closest('.lc [data-open="saved"]')) setTimeout(drawList,0); }, true);
  new MutationObserver(ms=>{ if(ms.some(m=>[...m.addedNodes].some(n=>n.nodeType===1&&(n.matches?.("[data-fav]")||n.querySelector?.("[data-fav]"))))) paintAll(); }).observe(document.getElementById("vp"),{childList:true,subtree:true});
  paintAll(); drawList();
})();
// assistant welcome: start from a place
(function(){ const w=document.querySelector("#lc-ov .assistant__welcome"); if(!w) return; const ch=document.createElement("div"); ch.className="lca-chips";
  ["Cairo & Giza","Luxor","Aswan","Alexandria","Red Sea","Western Desert"].forEach(p=>{ const b=document.createElement("button"); b.type="button"; b.textContent=p; b.addEventListener("click",()=>{ const i=document.querySelector("#lc-ov .assistant__form input"); i.value=p; i.form.requestSubmit(); }); ch.appendChild(b); }); w.appendChild(ch); })();
// "Chat with Egypt Roamer" opens the live contact form (nothing is sent in the prototype)
document.addEventListener("click",e=>{ const as=document.querySelector("#lc-ov #assistant"), f=document.querySelector("#lc-ov [data-chat-form]"), en=document.querySelector("#lc-ov [data-chat-entry]");
  if(e.target.closest(".lc [data-chat-open]")){ e.preventDefault(); f.hidden=false; en.hidden=true; as.classList.add("is-chat"); f.querySelector("textarea,input:not([type=hidden])")?.focus(); return; }
  if(e.target.closest(".lc [data-chat-cancel]")){ e.preventDefault(); f.hidden=true; en.hidden=false; as.classList.remove("is-chat"); } }, true);
// search: places open their trips; the prototype's trips are listed too
(function(){ const ov=document.getElementById("lc-ov"); const SLUG={cairo:"cairo",alexandria:"alexandria",siwa:"siwa",luxor:"luxor",aswan:"aswan",hurghada:"hurghada",sharm:"sharm"};
  ov.querySelectorAll(".search__item").forEach(i=>{ const n=(i.querySelector("b")||i).textContent.trim().toLowerCase().split(/\s/)[0]; if(SLUG[n]&&!i.dataset.href) i.dataset.href=`https://egyptroamer.com/destinations/${SLUG[n]}/`; });
  const res=ov.querySelector("[data-search-results]"), inp=ov.querySelector("[data-search-input]"); if(!res||!inp) return;
  const box=document.createElement("div"); box.className="lcs-trips"; res.prepend(box);
  const draw=()=>{ const q=inp.value.trim().toLowerCase(); const list=(window.lcIndex?window.lcIndex():[]).filter(x=>x.card&&(!q||x.t.toLowerCase().includes(q))).slice(0,q?6:4);
    box.innerHTML=list.length?`<p class="search__group">${q?"Trips":"Popular trips"}</p>`:""; list.forEach(x=>{ const a=document.createElement("a"); a.href="#"; a.className="search__item"; a.innerHTML=`<img src="${x.card.img}" alt=""><span><b>${x.card.title}</b><small>${x.card.meta}</small></span>`; a.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); window.lcClose(); x.run&&x.run(); }); box.appendChild(a); }); };
  inp.addEventListener("input",draw); inp.addEventListener("focus",draw); setTimeout(draw,0); })();
(function(){ const src=document.querySelector("#vp .nav__cta"), dst=document.querySelector("#lc-ov .lcm-cta"); if(!src||!dst) return;
  const sync=()=>{ dst.innerHTML=src.innerHTML; }; sync(); new MutationObserver(sync).observe(src,{childList:true,subtree:true,characterData:true});
  dst.addEventListener("click",e=>{ e.preventDefault(); e.stopPropagation(); window.lcClose(); src.click(); }, true); })();
'''

def _search_words(o):
    o = o.replace('placeholder="Search destinations, tours, cruises, guides…"', 'placeholder="Search places and trips"')
    return o.replace('<p class="search__group">Destinations</p>', '<p class="search__group">Places</p>')

_ov = overlays_html
def overlays_html():
    o = _search_words(_ov())
    assert 'placeholder="Search places and trips"' in o
    return o

def css():
    return CSS

# every replacement above must have landed
assert 'setNavB' in JS and 'lcFavBtn' in JS and 'Close menu' in JS and 'b.dataset.icon' in JS and 'lca-card' in JS
