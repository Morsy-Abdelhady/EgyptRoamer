# Phone version of proposal 4 (owner, 16 Oct: desktop approved, "start the mobile version").
# A Desktop / Phone switch puts the site in a 390 px frame. The site's small-screen rules answer to the frame's width
# (container queries on #vp), so the phone layout shown is the one a phone would get. Applied last by build4.py.

CSS = '''
/* ---------- phone frame (prototype chrome) ---------- */
.devsw{display:flex;gap:4px;background:rgba(255,255,255,.08);border-radius:999px;padding:3px}
.devsw button{border:0;background:transparent;color:#f3eee2;border-radius:999px;padding:5px 14px;font-family:var(--ar);font-size:13px}
.devsw button[aria-pressed="true"]{background:#f3eee2;color:var(--charcoal)}
.stage.is-phone .browser{width:410px;justify-self:center;border-radius:44px;padding:10px;background:#16130f;box-shadow:0 30px 70px rgba(16,24,32,.35)}
.stage.is-phone .browser__bar{border-radius:34px 34px 0 0;padding:12px 18px 8px;background:#f3efe6}
.stage.is-phone .browser__dots{display:none}
.stage.is-phone .viewport{height:780px;border-radius:0 0 34px 34px}
#vp{container-type:inline-size;container-name:site}
.hdr__menu{display:none}

/* ---------- the site at phone width ---------- */
@container site (max-width:600px){
  .wrap{padding-inline:18px}
  .hdr .wrap{height:56px;gap:10px}
  .logo b{font-size:16px}.logo small{font-size:7px}
  .hdr #hdr-cta{display:none}
  .vswitch{margin-inline-start:auto!important;font-size:0!important;padding:0!important;width:38px;height:38px;justify-content:center;border-radius:50%!important}
  .vswitch span{font-size:12px}
  .hdr__menu{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;border:1px solid rgba(255,255,255,.3);background:transparent;color:#fff;font-size:16px}
  .hdr.is-open nav{display:flex!important;flex-direction:column;gap:0;position:absolute;top:56px;left:0;right:0;margin:0;background:rgba(16,24,32,.98);padding:4px 18px 12px}
  .hdr.is-open nav a{padding:13px 0;border-bottom:1px solid rgba(255,255,255,.1);font-size:16px}
  .hdr.is-open nav a[aria-current]{border-bottom:1px solid rgba(255,255,255,.1);color:var(--gold)}
  .filters{top:56px!important}
  .vbadge{top:68px}

  /* 1 · home */
  .hero--story{min-height:0}
  .hero__grid{padding-block:110px 34px;grid-template-columns:minmax(0,1fr)!important}
  .hero__grid > div{min-width:0}
  .hero h1{font-size:58px;line-height:.95}
  .hero h1 em{font-size:30px}
  .hero__lead{font-size:15px;margin:12px 0 20px}
  .hero__acts{display:flex;flex-direction:column;gap:10px}
  .hero__acts .btn{justify-content:center}
  .finder2{flex-wrap:nowrap;overflow-x:auto;margin-inline:-18px;padding-inline:18px;scrollbar-width:none}
  .finder2::-webkit-scrollbar{display:none}
  .finder2 > *{flex:none}
  .chapters{display:none}
  .ch{min-height:620px}
  .ch h2{font-size:50px!important}
  .ch__line{font-size:19px}
  .ch__trips{display:flex!important;overflow-x:auto;gap:10px;padding:12px;scroll-snap-type:x mandatory;scrollbar-width:none}
  .ch__trips::-webkit-scrollbar{display:none}
  .ch__trips-h{min-width:0;flex:none;width:100%;display:none}
  .ch__trips .mini{flex:0 0 82%;scroll-snap-align:start}
  .interlude2__q{font-size:28px;margin:12px 0 26px}
  .interlude2__facts{gap:18px}
  .moods2{min-height:0}
  .moods2__head h2{font-size:30px}
  .moods2__tabs{flex-wrap:nowrap;overflow-x:auto;margin-inline:-18px;padding-inline:18px;scrollbar-width:none}
  .moods2__tabs::-webkit-scrollbar{display:none}
  .moods2__tabs > *{flex:none}
  .moods2__word{font-size:40px!important}
  .moods2__desc{font-size:16px}
  .sec__head h2{font-size:30px}
  .dest2__row{display:flex!important;overflow-x:auto;scroll-snap-type:x mandatory;margin-inline:-18px;padding-inline:18px;gap:10px;scrollbar-width:none}
  .dest2__row::-webkit-scrollbar{display:none}
  .dest2__row > *{flex:0 0 46%;scroll-snap-align:start}

  /* 2 · browse */
  .phero h1{font-size:36px}
  .filters .wrap{flex-wrap:nowrap;overflow-x:auto;gap:10px 16px;padding-block:10px;scrollbar-width:none}
  .filters .wrap::-webkit-scrollbar{display:none}
  .filters .wrap > *{flex:none}
  .fgroup .chips{flex-wrap:nowrap}
  .gbanner{height:170px;flex-direction:column;align-items:flex-start;justify-content:flex-end;gap:6px;padding:14px 16px}
  .gbanner h2{font-size:26px}
  .gbanner p{font-size:14px}
  .gbanner > a{font-size:12.5px}

  /* 3 · the trip in its world */
  .wopen{min-height:600px!important}
  .wtxt{padding:96px 18px 28px!important}
  .wtxt h1{font-size:36px!important}
  .wtxt .lead{font-size:15px!important}
  .wq{font-size:16.5px!important}
  .wacts{flex-direction:column}
  .wacts .btn{justify-content:center}
  .jstage{height:640px;min-height:0}
  .jsteps{margin-top:-640px}
  .jstep{min-height:640px;align-items:flex-end;padding:0 12px 90px}
  .jstep article{padding:18px}
  .jstep h3{font-size:25px}
  .jstep p{font-size:15px}
  .jhead{top:20px;inset-inline-end:18px}
  .jhead h2{font-size:26px}
  .path{display:none}
  .wsec{padding:60px 18px}
  .wsec h2,.papy h2{font-size:28px}
  .tablets{display:grid;gap:10px}
  .papy{padding:52px 18px}
  .papy__in{gap:30px}
  .choose2{padding:56px 18px 90px}
  .choose2__photo{aspect-ratio:16/10;position:relative!important;top:auto!important}
  .hint{display:none}
  .choose2 h2{font-size:32px}
  .wopt{grid-template-columns:1fr;gap:12px}
  .wopt .btn{justify-content:center}
  .wtbl{font-size:12.5px}
  .wtbl th,.wtbl td{padding:8px 4px}
  .wfaq{padding:52px 18px}
  .wfaq h2,.wmore h2{font-size:27px}
  .wmore{padding:52px 18px 90px}
  .wmore__row{grid-template-columns:1fr}
  /* the booking capsule becomes one bar across the bottom */
  .capsule{float:none;margin:-74px 10px 0;bottom:10px;justify-content:space-between;width:auto}
  .ftr .wrap{font-size:12px}
}
'''

def apply(tpl):
    css_end = tpl.index('</style>')
    css, rest = tpl[:css_end], tpl[css_end:]
    n = css.count('@media (max-width:900px){')
    assert n >= 4, n
    css = css.replace('@media (max-width:900px){', '@container site (max-width:900px){')
    tpl = css + CSS + rest
    reps = [
        ('    <label class="pbar__notes"><input type="checkbox" id="show-notes" checked> الملاحظات</label>',
         '    <div class="devsw" role="group" aria-label="الجهاز"><button type="button" data-dev="desk" aria-pressed="true">كمبيوتر</button><button type="button" data-dev="phone" aria-pressed="false">موبايل</button></div>\n    <label class="pbar__notes"><input type="checkbox" id="show-notes" checked> الملاحظات</label>'),
        ('<button type="button" class="vswitch" id="vswitch" aria-pressed="true"></button>',
         '<button type="button" class="vswitch" id="vswitch" aria-pressed="true"></button><button type="button" class="hdr__menu" id="menu" aria-expanded="false" aria-label="Menu">☰</button>'),
        ('\nrenderHome(); lazyVideos(document);',
         '''
// prototype: Desktop / Phone
document.querySelectorAll("[data-dev]").forEach(b=>b.addEventListener("click",()=>{ document.querySelectorAll("[data-dev]").forEach(x=>x.setAttribute("aria-pressed",String(x===b))); document.getElementById("stage").classList.toggle("is-phone",b.dataset.dev==="phone"); }));
// phone menu
document.getElementById("menu").addEventListener("click",e=>{ const h=document.getElementById("hdr"); h.classList.toggle("is-open"); e.currentTarget.setAttribute("aria-expanded",String(h.classList.contains("is-open"))); });
document.querySelectorAll("#hdr nav a").forEach(a=>a.addEventListener("click",()=>{ document.getElementById("hdr").classList.remove("is-open"); document.getElementById("menu").setAttribute("aria-expanded","false"); }));
renderHome(); lazyVideos(document);'''),
    ]
    for a, b in reps:
        assert tpl.count(a) == 1, a[:70]
        tpl = tpl.replace(a, b)
    return tpl
