# Owner, 16 Oct (phone review):
# - the header's play/pause and the "i" credit badge were off-centre on phones: one header switch also confused visitors.
#   Every section with video now carries its own small control: an "i" for the credit (tap to show) and play/pause,
#   both drawn as SVG so they sit centred on every device. The header switch is gone.
# - the booking bar inside a trip now appears as soon as the opener's own "Book this trip" button leaves the screen,
#   and stays until the booking section is on screen.
CSS = '''
/* ---------- per-section video controls ---------- */
#vswitch{display:none!important}
.vctl{position:absolute;z-index:6;inset-inline-end:16px;top:16px;display:flex;gap:6px;align-items:flex-start}
.ch .vctl{top:84px}
.wopen .vctl{top:84px}
.jstage .vctl{top:auto;bottom:18px}
.vctl button{width:32px;height:32px;border-radius:50%;border:1px solid rgba(255,255,255,.35);background:rgba(0,0,0,.45);color:#fff;display:grid;place-items:center;padding:0;cursor:pointer;backdrop-filter:blur(6px)}
.vctl button:hover{background:rgba(0,0,0,.65)}
.vctl svg{width:14px;height:14px;display:block;fill:currentColor}
.vctl__tip{position:absolute;inset-inline-end:0;top:38px;white-space:nowrap;font:500 11.5px var(--sans);background:rgba(0,0,0,.85);color:#fff;border-radius:6px;padding:5px 9px}
.jstage .vctl__tip{top:auto;bottom:38px}
@container site (max-width:600px){ .hdr__menu{margin-inline-start:auto} .live .map__list{flex-wrap:wrap!important;overflow:visible!important;margin-inline:0!important;padding-inline:0!important} .ch .vctl,.wopen .vctl{top:68px} .vctl{inset-inline-end:12px} .vctl__tip{white-space:normal;width:max-content;max-width:240px} }
'''

JS = r'''
// ---------- one control per section with video ----------
const ICON={play:'<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4.5 2.8v10.4L13 8z"/></svg>',pause:'<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 2.5h3v11H4zM9 2.5h3v11H9z"/></svg>',info:'<svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="4.2" r="1.3"/><path d="M6.6 6.6h2.6v6.2h1.2v1.2H5.6v-1.2h1.2V7.8h-.2z"/></svg>'};
function videoControls(scope){
  (scope||document).querySelectorAll("#vp video").forEach(v=>{
    const box=v.closest(".ch,.gbanner,.jstage,.wopen,.wvid,.ahero,.ivid")||v.parentElement;
    if(box.querySelector(":scope > .vctl")) return;
    if(getComputedStyle(box).position==="static") box.style.position="relative";
    const badge=box.querySelector(":scope > .vbadge"); const credit=badge?(badge.dataset.tip||badge.textContent):"";
    if(badge) badge.remove();
    const c=document.createElement("div"); c.className="vctl";
    c.innerHTML=(credit?`<button type="button" class="vctl__i" aria-expanded="false" aria-label="Video credit">${ICON.info}</button><span class="vctl__tip" hidden>${esc(credit)}</span>`:"")+`<button type="button" class="vctl__pp"></button>`;
    box.appendChild(c);
    if(VIDEOS_OFF) box.dataset.voff="1";
    const pp=c.querySelector(".vctl__pp");
    const paint=()=>{const off=!!box.dataset.voff; pp.innerHTML=off?ICON.play:ICON.pause; pp.setAttribute("aria-label",off?"Play video":"Pause video");};
    pp.addEventListener("click",()=>{ if(box.dataset.voff){ delete box.dataset.voff; box.querySelectorAll("video").forEach(x=>{ if(x.classList.contains("jv")&&!x.classList.contains("on")) return; if(!x.getAttribute("src")) x.src=x.dataset.src||x.dataset.jsrc||""; x.play().catch(()=>{}); }); } else { box.dataset.voff="1"; box.querySelectorAll("video").forEach(x=>x.pause()); } paint(); });
    const ib=c.querySelector(".vctl__i"); if(ib) ib.addEventListener("click",()=>{ const t=c.querySelector(".vctl__tip"); t.hidden=!t.hidden; ib.setAttribute("aria-expanded",String(!t.hidden)); });
    paint();
  });
}
const _lazyVideos=lazyVideos; lazyVideos=function(scope){ _lazyVideos(scope); videoControls(scope); };
'''

def apply(tpl):
    a = tpl.index('</style>')
    tpl = tpl[:a] + CSS + tpl[a:]
    reps = [
        # a section the visitor paused stays paused while it scrolls in and out
        ('if(VIDEOS_OFF) return;', 'if(VIDEOS_OFF||v.closest("[data-voff]")) return;'),
        ('on&&!VIDEOS_OFF', 'on&&!VIDEOS_OFF&&!x.closest("[data-voff]")'),
        # booking bar: hidden only while the opener's own booking button or the booking section is on screen
        ('io2.observe(host.querySelector("#wopen"))', 'io2.observe(host.querySelector("#wopen .wacts"))'),
        ('seen[e.target.id==="wchoose"?"choose":"open"]', 'seen[e.target.id==="wchoose"?"choose":"open"]'),
        ('\nrenderHome(); renderLive(); lazyVideos(document);', JS + '\nrenderHome(); renderLive(); lazyVideos(document);'),
    ]
    for a, b in reps:
        assert tpl.count(a) == 1, a[:60]
        tpl = tpl.replace(a, b)
    return tpl
