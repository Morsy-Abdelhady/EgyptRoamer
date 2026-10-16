# Second review (16 Oct): defects found walking the whole prototype. Applied by build4.py after v4-improve.py.
def worlds(w):
    reps = [
        # steps without a photo of their own: a designed panel in the world's colours (glyphs in the antiquities world),
        # instead of a blurred photo that read as mud
        ('      : `<div class="jlayer ${i?"":"on"}" data-j="${i}"><img src="${IMG[W.photo]}" alt="" style="width:100%;height:100%;object-fit:cover;filter:blur(14px) ${s.tone==="night"?"brightness(.4) saturate(.6) hue-rotate(-15deg)":s.tone==="dark"?"brightness(.45) saturate(.6)":"brightness(.7) saturate(1.1)"};transform:scale(${1.15+i*.1})"></div>`).join("");',
         '      : `<div class="jlayer jtone jtone--${s.tone||"warm"} ${i?"":"on"}" data-j="${i}">${W.fx==="tomb"?wallHTML(30).replace(\'class="wall"\',\'class="wall" style="opacity:.16"\'):""}</div>`).join("");'),
        # a step may carry its own video (Giza: the Great Pyramid at sunset)
        # (already supported by s.video)
        # tomb worlds with a video: the torch shows the carved wall only, never a second picture over the video
        ('${isTomb?`<div class="wall--dim">${wallHTML(26)}</div><div class="lit" id="lit"><img src="${IMG[W.photo]}" alt="">${wallHTML(26)}</div>`',
         '${isTomb?`<div class="wall--dim">${wallHTML(26)}</div><div class="lit" id="lit">${W.video?"":`<img src="${IMG[W.photo]}" alt="">`}${wallHTML(26)}</div>`'),
    ]
    for a, b in reps:
        assert w.count(a) == 1, a[:90]
        w = w.replace(a, b)
    # after "Book": the chosen option is marked
    a = 'function book(spec,silent){'
    if a in w:
        pass
    return w

def more(m):
    reps = [
        # Giza: the bright camel photo opens the trip (no video behind the torch); the sunset video moves to the
        # Great Pyramid step, and the approved pyramids photo takes Khafre and Menkaure
        ('giza: {cls:"w-tomb", fx:"tomb", photo:"giza_camel", video:"video/giza-sunset.mp4", vcredit:"Giza · Video: Peggy Anke, Pexels",',
         'giza: {cls:"w-tomb", fx:"tomb", photo:"giza_camel",'),
        ('{t:"The Great Pyramid",p:"Walk around the Great Pyramid, the only surviving Wonder of the Ancient World."}',
         '{t:"The Great Pyramid",p:"Walk around the Great Pyramid, the only surviving Wonder of the Ancient World.",video:"video/giza-sunset.mp4",credit:"Giza · Video: Peggy Anke, Pexels"}'),
        ('{t:"Khafre and Menkaure",p:', '{img:"sc_pyr",t:"Khafre and Menkaure",p:'),
    ]
    for a, b in reps:
        assert m.count(a) == 1, a[:90]
        m = m.replace(a, b)
    return m

def nav(n):
    a = '  $("#wdone").hidden=false; $("#wdone-b").textContent=`${partner} opened in a new tab`; $("#wdone-p").textContent=`“${name}”`;'
    assert n.count(a) == 1
    return n.replace(a, a + '\n  document.querySelectorAll("#wchoose [data-wbook]").forEach(b=>{const on=b.dataset.wbook===spec; b.closest(".wopt")?.classList.toggle("is-opened",on); if(on){b.dataset.label=b.dataset.label||b.innerHTML; b.innerHTML="Opened ✓";} else if(b.dataset.label){b.innerHTML=b.dataset.label;} });')

def home(js, body):
    # moods: "An Egypt of torch-lit tombs…", the site's own words in an order that reads
    a = '$("#mood-word").textContent=label; $("#mood-desc").textContent=desc;'
    assert js.count(a) == 1
    js = js.replace(a, '$("#mood-word").textContent=label; $("#mood-desc").textContent="An Egypt "+desc.charAt(0).toLowerCase()+desc.slice(1);')
    a = '<p class="moods2__word">An Egypt <span id="mood-word"></span></p>'
    assert body.count(a) == 1
    body = body.replace(a, '<p class="moods2__word"><span id="mood-word"></span></p>')
    # the video switch moves into the header, out of the content's way
    a = '\n      <button type="button" class="vswitch" id="vswitch" aria-pressed="true"></button>'
    assert body.count(a) == 1
    body = body.replace(a, '')
    a = '<a class="btn btn--gold btn--sm" href="#" id="hdr-cta" data-go="2">'
    assert body.count(a) == 1
    body = body.replace(a, '<button type="button" class="vswitch" id="vswitch" aria-pressed="true"></button>' + a)
    return js, body

CSS = '''
/* ---------- second review, 16 Oct ---------- */
.vswitch{position:static!important;float:none!important;margin:0!important;background:transparent!important;border:1px solid rgba(255,255,255,.25)!important;padding:7px 12px!important;font-size:12px!important;backdrop-filter:none!important}
.hdr .wrap{gap:18px}
.jtone{background:radial-gradient(ellipse at 70% 30%,color-mix(in srgb,var(--wc) 26%,transparent),transparent 60%),linear-gradient(160deg,color-mix(in srgb,var(--wbg) 70%,#2a2016),var(--wbg))}
.jtone--night{background:radial-gradient(ellipse at 60% 10%,rgba(80,100,170,.35),transparent 60%),linear-gradient(180deg,#0b1020,var(--wbg))}
.jtone--dark{background:radial-gradient(ellipse at 30% 70%,color-mix(in srgb,var(--wc) 14%,transparent),transparent 55%),var(--wbg)}
.jtone .wall{color:var(--wc)}
.wopen{height:auto!important;min-height:max(560px,calc(min(80vh,880px) - 68px));display:flex;align-items:flex-end}
.wtxt{position:relative!important;inset:auto!important;bottom:auto!important;padding:96px 56px 30px;max-width:720px}
.wtxt .lead{font-size:16px!important;margin:0 0 12px!important}
.wq{margin:0 0 12px!important;font-size:18px!important}
.wacts{margin-top:14px!important}
.wtxt h1{margin:8px 0 12px!important}
.wtxt h1{font-size:54px!important}
.hero .eyebrow,.ch__n,.wtxt .eyebrow{text-shadow:0 1px 10px rgba(0,0,0,.6)}
.hero::after{background:linear-gradient(90deg,rgba(16,24,32,.55),transparent 60%),linear-gradient(180deg,rgba(16,24,32,.35) 0%,rgba(16,24,32,.15) 35%,rgba(16,24,32,.82) 100%)!important}
.dcard::after{background:linear-gradient(180deg,rgba(16,24,32,.05) 15%,rgba(16,24,32,.92) 70%)!important}
.dcard small{color:#f1d7a6!important;text-shadow:0 1px 6px rgba(0,0,0,.6)}
.moods2__word{font-size:64px}
.moods2__word span{font-style:italic;color:var(--sand)}
.wopt.is-opened{border-color:#7fd1c7!important;background:rgba(15,107,122,.18)!important}
@media (max-width:900px){.wtxt{padding:100px 18px 36px}.wtxt h1{font-size:40px!important}}
'''
