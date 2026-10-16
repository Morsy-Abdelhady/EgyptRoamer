# The live site's chrome in both prototypes (owner, 16 Oct): header with search, favourites and language menu, the
# phone menu, the search overlay, the saved drawer, the Trip assistant and the footer. Captured by chrome-capture.mjs.
#
# Placement: the header and footer scroll with the page inside the frame (#vp). The overlays sit in .lc-ov, a layer over
# the frame that is also the size container, so their own small-screen rules follow the frame width.
import json, re, os

HERE = os.path.dirname(os.path.abspath(__file__))
CAP = json.load(open(os.path.join(HERE, 'live', 'chrome.json'), encoding='utf-8'))

def clean(h):
    h = re.sub(r'<source\b[^>]*>', '', h)
    h = re.sub(r'\s(?:srcset|sizes)="[^"]*"', '', h)
    h = re.sub(r'\sdata-assistant="[^"]*"', '', h)
    h = re.sub(r'\saction="[^"]*"', '', h)
    # links keep their destination for the prototype's router, but never leave the page
    h = re.sub(r'href="(https://egyptroamer\.com[^"]*)"', r'href="#" data-href="\1"', h)
    return h

def css():
    out = []
    for wrap, sel, body in CAP['css']['rules']:
        if sel == ':root':
            body = '; '.join(d for d in body.split('; ') if d.startswith('--') and not d.startswith('--wp'))
            if not body:
                continue
            sel = '.lc-t'
        else:
            # a rule applies only to the live chrome itself (an element marked .lc-t or inside one), never to the
            # prototype's own buttons and containers that share class names with the live site
            parts = []
            for p in sel.split(','):
                p = p.strip()
                m = re.search(r'(::?(?:before|after|placeholder|marker|selection|-webkit-[\w-]+)(?:\([^)]*\))?)$', p)
                pe = m.group(1) if m and not re.search(r':(?:hover|focus|active|checked|not|is|where)', m.group(1)) else ''
                base = p[:len(p) - len(pe)] if pe else p
                parts.append(f'{base}:is(.lc-t, .lc-t *){pe}')
            sel = ','.join(parts)
        body = re.sub(r'100[ds]?vh\b', '100%', body)
        body = re.sub(r'(\d)vw\b', r'\1cqw', body)
        rule = f'{sel}{{{body}}}'
        if wrap is None:
            out.append(rule)
        elif re.fullmatch(r'\((?:max|min)-width: ?\d+px\)', wrap):
            out.append(f'@container site {wrap.replace(" ", "")}{{{rule}}}')
        elif 'reduced-motion' in wrap or 'hover' in wrap or 'pointer' in wrap:
            out.append(f'@media {wrap}{{{rule}}}')
    out += CAP['css']['keyframes']
    return '\n/* ---------- live site chrome (captured 16 Oct) ---------- */\n' + '\n'.join(out) + '''
.browser{transform:translateZ(0)}
.lc-ov{position:absolute;inset:0;container:site / inline-size;pointer-events:none;z-index:40}
.lc-ov > *{pointer-events:auto}
.lc-ov .overlay[hidden]{display:none}
.lc .nav{position:sticky!important;top:0!important;transform:none!important;z-index:30}
.lc-ov .menu{top:var(--navh,72px)!important}
.lc .nav__inner{max-width:none}
.lc .assistant-launch{transition:bottom .25s}
.lc-ov.is-raised .assistant-launch{bottom:92px!important}
.lc .footer a{cursor:pointer}
.lc-toast{position:absolute;left:50%;bottom:24px;transform:translateX(-50%);z-index:70;background:#101820;color:#fff;border-radius:14px;padding:10px 16px;font:500 13.5px/1.4 Inter,system-ui,sans-serif;max-width:86%;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,.3)}
/* Arabic preview: the layout turns right-to-left; the prototype's English lines keep their own word order and punctuation */
#vp[dir=rtl] :is(h1,h2,h3,h4,p,li,a,b,small,span,em,button,dd,dt,summary,figcaption){unicode-bidi:plaintext}
#vp[dir=rtl] .wtxt h1{direction:ltr;text-align:right}
.lc-toast{unicode-bidi:plaintext;max-width:min(86%,420px)!important}
'''

def nav_html():
    return clean(CAP['html']['nav']).replace('class="nav"', 'class="nav lc-t"', 1)

def footer_html():
    return clean(CAP['html']['footer']).replace('class="footer on-dark"', 'class="footer on-dark lc-t"', 1)

def overlays_html():
    h = CAP['html']
    parts = [h['menu'], h['search'], h['saved'], h['launch'], h['assistant']]
    out = []
    for p in parts:
        p = clean(p)
        p = re.sub(r'^(<\w+[^>]*?\sclass=")', r'\1lc-t ', p, count=1)
        assert 'lc-t ' in p[:300], p[:120]
        out.append(p)
    sprite = f'<svg width="0" height="0" style="position:absolute" aria-hidden="true">{CAP["sprite"]}</svg>'
    return f'<div class="lc lc-ov" id="lc-ov">{sprite}{"".join(out)}<div class="lc-toast" id="lc-toast" hidden></div></div>'

ASSIST_I18N = json.loads(re.search(r'data-assistant="([^"]*)"', CAP['html']['assistant']).group(1).replace('&quot;', '"'))['i18n']

JS = r'''
// ---------- live site chrome: behaviour ----------
(function(){
  const vp=document.getElementById("vp"), ov=document.getElementById("lc-ov"), nav=vp.querySelector(".nav");
  const toastEl=document.getElementById("lc-toast"); let tt;
  window.lcToast=(m)=>{ toastEl.textContent=m; toastEl.hidden=false; clearTimeout(tt); tt=setTimeout(()=>toastEl.hidden=true,3200); };
  const setNavH=()=>ov.style.setProperty("--navh",nav.offsetHeight+"px"); setNavH(); new ResizeObserver(setNavH).observe(nav);
  vp.style.setProperty("--navh",nav.offsetHeight+"px"); new ResizeObserver(()=>vp.style.setProperty("--navh",nav.offsetHeight+"px")).observe(nav);
  // header state: transparent at the very top (only where the page starts under it), then dark or light by what is under it
  const dark=el=>{ for(let e=el;e&&e!==vp;e=e.parentElement){ const c=getComputedStyle(e).backgroundColor; const m=c.match(/[\d.]+/g); if(m&&(m[3]===undefined||+m[3]>.5)){ return (.299*m[0]+.587*m[1]+.114*m[2])<128; } if(e.tagName==="IMG"||e.tagName==="VIDEO") return true; } return false; };
  // what is under the bar: the first photo, video or solid background in the stack decides
  const darkAt=(x,y)=>{ for(const e of document.elementsFromPoint(x,y)){ if(e===vp||!vp.contains(e)||nav.contains(e)) continue; if(/^(IMG|VIDEO|CANVAS)$/.test(e.tagName)) return true; const m=getComputedStyle(e).backgroundColor.match(/[\d.]+/g); if(m&&(m[3]===undefined||+m[3]>.5)) return (.299*m[0]+.587*m[1]+.114*m[2])<128; if(getComputedStyle(e).backgroundImage!=="none") return true; } return false; };
  const paint=()=>{ const r=nav.getBoundingClientRect();
    const st=(vp.scrollTop<40&&window.LC_TOP)?"top":(darkAt(r.left+r.width/2, r.bottom+6)?"solid":"light"); if(nav.dataset.state!==st) nav.dataset.state=st; };
  vp.addEventListener("scroll",paint,{passive:true}); new ResizeObserver(paint).observe(vp); setTimeout(paint,50); window.lcPaint=paint;
  // overlays
  const open=id=>{ closeAll(); const e=document.getElementById(id); if(!e) return; e.hidden=false; void e.offsetWidth; e.classList.add("is-open"); if(id==="menu"){ const b=nav.querySelector(".nav__burger"); b&&b.setAttribute("aria-expanded","true"); b&&b.classList.add("is-open"); } const f=e.querySelector("input,button"); f&&f.focus(); };
  const closeAll=()=>{ ov.querySelectorAll(".is-open").forEach(e=>{ e.classList.remove("is-open"); if(e.matches(".overlay,.menu")) e.hidden=true; }); const b=nav.querySelector(".nav__burger"); if(b){ b.setAttribute("aria-expanded","false"); b.classList.remove("is-open"); } };
  window.lcClose=closeAll;
  document.addEventListener("keydown",e=>{ if(e.key==="Escape"){ closeAll(); nav.querySelector(".lang")?.classList.remove("is-open"); } });
  // saved drawer: the live empty state
  const savedList=ov.querySelector("[data-saved-list]"); if(savedList) savedList.innerHTML=`<div class="drawer__empty"><svg class="icon" aria-hidden="true"><use href="#i-heart"></use></svg><p>Tap the heart on any experience, stay or destination to keep it here while you plan.</p><a class="btn btn--dark btn--sm" href="#" data-close data-go="2">Browse experiences</a></div>`;
  // search: filter the live list
  const items=[...ov.querySelectorAll(".search__item")]; const input=ov.querySelector("[data-search-input]");
  input&&input.addEventListener("input",()=>{ const q=input.value.trim().toLowerCase(); items.forEach(i=>i.hidden=!!q&&!i.textContent.toLowerCase().includes(q)); ov.querySelectorAll(".search__group").forEach(g=>{ let n=g.nextElementSibling, any=false; while(n&&!n.classList.contains("search__group")){ if(n.classList.contains("search__item")&&!n.hidden) any=true; n=n.nextElementSibling; } g.hidden=!any; }); });
  // assistant: the live assistant answers with Egypt Roamer pages only; here it answers from the same page list
  const I18N=''' + json.dumps(ASSIST_I18N) + r''';
  const form=ov.querySelector(".assistant__form"), log=ov.querySelector(".assistant__log");
  // the prototype's own pages (trips, guides) join the live list, so a question finds them too
  const index=()=>items.map(i=>({t:i.textContent.replace(/\s+/g," ").trim(),html:i.innerHTML,href:i.dataset.href||""})).concat(window.lcIndex?window.lcIndex():[]);
  const answer=q=>{ const words=q.toLowerCase().split(/\W+/).filter(w=>w.length>2); const hits=index().filter(i=>words.some(w=>i.t.toLowerCase().includes(w))).slice(0,6);
    const me=document.createElement("p"); me.className="assistant__msg assistant__msg--me"; me.textContent=q;
    const box=document.createElement("div"); box.className="assistant__msg"; box.innerHTML=hits.length?`<p>${I18N.intro}</p>`:`<p>${I18N.none}</p>`;
    hits.forEach(h=>{ const a=document.createElement("a"); a.className="search__item"; a.href="#"; if(h.href) a.dataset.href=h.href; a.innerHTML=h.html||`<span>${h.t.replace(/[<&]/g,"")}</span>`; if(h.run) a.addEventListener("click",ev=>{ ev.preventDefault(); ev.stopPropagation(); closeAll(); h.run(); }); box.appendChild(a); });
    log.append(me,box); log.scrollTop=log.scrollHeight; };
  form&&form.addEventListener("submit",e=>{ e.preventDefault(); const i=form.querySelector("input,textarea"); if(i.value.trim()){ answer(i.value.trim()); i.value=""; } });
  ov.querySelectorAll(".assistant__chip").forEach(c=>c.addEventListener("click",()=>answer(c.textContent.trim())));
  // forms on the footer never send anything in the prototype
  document.querySelectorAll(".lc form").forEach(f=>{ if(f!==form) f.addEventListener("submit",e=>{ e.preventDefault(); lcToast("Prototype: nothing is sent. On the site this signs you up for Roamer letters."); }); });
  // clicks
  document.addEventListener("click",e=>{
    const op=e.target.closest(".lc [data-open], .lc .nav__burger"); if(op){ e.preventDefault(); const id=op.dataset.open||"menu"; const t=document.getElementById(id); if(t&&t.classList.contains("is-open")) closeAll(); else open(id); return; }
    if(e.target.closest(".lc [data-close]")&&!e.target.closest("[data-href],[data-go]")){ closeAll(); return; }
    if(e.target.classList&&e.target.classList.contains("overlay")){ closeAll(); return; }
    const lt=e.target.closest(".lc .lang__toggle"); if(lt){ e.preventDefault(); const l=lt.closest(".lang"); const on=!l.classList.contains("is-open"); l.classList.toggle("is-open",on); lt.setAttribute("aria-expanded",String(on)); return; }
    const lo=e.target.closest(".lc [data-lang-url], .lc .menu__lang a"); if(lo){ e.preventDefault(); setLang(lo.dataset.value||lo.getAttribute("lang")||lo.dataset.langUrl, lo.textContent.trim()); return; }
    if(!e.target.closest(".lang")) nav.querySelector(".lang")?.classList.remove("is-open");
    const a=e.target.closest(".lc a[data-href]"); if(a){ e.preventDefault(); closeAll(); window.lcRoute ? window.lcRoute(a.dataset.href, a) : lcToast("Prototype: on the site this opens "+a.dataset.href.replace("https://egyptroamer.com","")); return; }
  }, true);
  // language: the switch works; this prototype's own text is English. Arabic turns the page right-to-left to check the layout.
  function setLang(code,label){
    code=(code||"en").slice(0,2); nav.querySelectorAll("[data-lang-url]").forEach(o=>o.toggleAttribute("aria-current",o.dataset.langUrl===code||o.dataset.value===code));
    nav.querySelector(".lang")?.classList.remove("is-open");
    const t=nav.querySelector(".lang__toggle [data-lang-label], .lang__toggle span"); if(t&&t.textContent.trim().length<=3) t.textContent=code.toUpperCase();
    const rtl=code==="ar"; document.querySelectorAll("#vp, #lc-ov").forEach(el=>{ el.dir=rtl?"rtl":"ltr"; el.lang=code; });
    closeAll(); lcPaint();
    lcToast(rtl?"Arabic: the page is now right-to-left. The prototype's text stays English; on the site every page is shown in its translated version.":`${label}: on the site this opens the ${label} version. The prototype's text stays English.`);
  }
})();
'''
