import re, json
import importlib.util

def load(name, path):
    spec = importlib.util.spec_from_file_location(name, path)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod

VP = load('vp', 'v4-video-patches.py')   # video placements
IM = load('im', 'v4-improve.py')         # first round of improvements
F2 = load('f2', 'v4-fix2.py')            # second review fixes
MB = load('mb', 'v4-mobile.py')          # phone version
R4 = load('r4', 'v4-round4.py')
H2 = load('h2', 'v4-home2.py')            # production sections the prototype had dropped
NC = load('nc', 'v4-cruise.py')            # Nile cruise trip (owner OK, 17 Oct)
WP = load('wp', 'v4-wphotos.py')           # Commons photos for steps without one
TR = load('tr', 'v4-tourist.py')            # tourist walk-through fixes
T2 = load('t2', 'v4-tourist2.py')
T3 = load('t3', 'v4-tourist3.py')           # every trip as a tourist           # first-time tourist review
PL = load('pl', 'v4-planner.py')            # Trip Builder fixes
R7 = load('r7', 'v4-round7.py')            # expert review suggestions
GD = load('gd', 'v4-guides.py')            # Travel Guide merged in
R6 = load('r6', 'v4-round6.py')            # 7 places, footer pages, food photos
R5 = load('r5', 'v4-round5.py')            # review fixes and the live chrome          # per-section video controls, booking bar
LV = load('lv', 'v4-live.py')            # live site's Find your Egypt + map
CAP = json.load(open('live/live-sections.json', encoding='utf-8'))

body = open('v2-body.html', encoding='utf-8').read()
body = body.replace('الاقتراح التاني: القصة + الحجز الواضح<small>نسخة الكمبيوتر · نموذج للاعتماد · المحتوى حقيقي من الموقع</small>',
                    'الاقتراح الرابع: القصة + عوالم الرحلات<small>نسخة الكمبيوتر · نموذج للاعتماد · المحتوى حقيقي من الموقع · فيديوهات مجانية من Pexels</small>')
a = body.index('<!-- 3 & 4 · Choose / Book'); b = body.index('<footer class="ftr">')
body = body[:a] + '<!-- 3 & 4 · the trip, in its own world -->\n      <section data-screen="3" hidden><div id="world" class="world"></div></section>\n\n      ' + body[b:]
a = body.index('<aside class="notes" id="notes">'); b = body.index('</aside>', a) + 8
body = body[:a] + open('v4-notes.html', encoding='utf-8').read() + body[b:]

js = open('v2.js', encoding='utf-8').read()
reps = [
    ('{id:"redsea",k:"ras",', '{id:"redsea",k:"ras",world:true,'),
    ('{id:"white",k:"white",', '{id:"white",k:"white",world:true,'),
    ('{id:"nile",k:"nile",', '{id:"nile",k:"nile",world:true,'),
    ('{id:"valley",k:"valley",', '{id:"valley",k:"valley",world:true,'),
    ('class="card${t.open?" card--hl":""}"', 'class="card${t.world?" card--hl":""}"'),
    ('class="mini${t.open?" mini--hl":""}"', 'class="mini${t.world?" mini--hl":""}"'),
    ('{n:"01",eye:"Timeless wonders",name:"Pyramids",', '{n:"01",video:"video/giza-camels-clear.mp4",vcredit:"Giza · Video: JORGE MONCADA, Pexels",eye:"Timeless wonders",name:"Pyramids",'),
    ('<section class="ch on-dark" id="ch-${i+1}"><img class="ch__img" src="${IMG[c.img]}" alt="">',
     '<section class="ch on-dark" id="ch-${i+1}">${c.video?`<video class="ch__img" autoplay muted loop playsinline poster="${IMG[c.img]}" src="${c.video}"></video><span class="vbadge">${esc(c.vcredit)}</span>`:`<img class="ch__img" src="${IMG[c.img]}" alt="">`}'),
]
for x, y in reps:
    assert js.count(x) == 1, x
    js = js.replace(x, y)
js, body = VP.patch_home(js, body)
js, body = IM.home(js, body)
js, body = F2.home(js, body)
body = LV.body(body)
js = js[:js.index('let step=1;')] + F2.nav(open('v4-nav.js', encoding='utf-8').read())
js, _ = LV.js(js, CAP)

v2 = open('journey-story.template.html', encoding='utf-8').read()
head = v2[:v2.index('<style>')].replace('<title>Egypt Roamer Story Journey</title>', '<title>Egypt Roamer Story Worlds</title>')
head = head.replace('family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap', 'family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Noto+Sans+Egyptian+Hieroglyphs&display=swap')
css = v2[v2.index('<style>') + 7:v2.index('</style>')] + open('v4-worlds.css', encoding='utf-8').read() + VP.EXTRA_CSS + IM.CSS + F2.CSS
clock = re.search(r'const CLOCK = `([\s\S]*?)`;', v2).group(1)
js = js.replace('{{clock}}', clock)
worlds = F2.worlds(IM.worlds(VP.patch_worlds(open('v4-worlds.js', encoding='utf-8').read())))
more = F2.more(open('v4-worlds-more.js', encoding='utf-8').read())
tpl = (head + '<style>' + css + '</style>\n' + body + '\n<script>\n' + js + '\n' + worlds + '\n' + more
       + '\nrenderHome(); lazyVideos(document); creditsAsInfo(document); videoSwitch(); go(1);\n</script>\n')
tpl = MB.apply(tpl)
tpl = LV.apply(tpl, CAP)
tpl = R4.apply(tpl)
tpl = R5.apply(tpl)
tpl = H2.apply(tpl)
tpl = R6.apply(tpl)
tpl = GD.apply(tpl)
tpl = R7.apply(tpl)
tpl = PL.apply(tpl)
tpl = NC.apply(tpl)
tpl = WP.apply(tpl)
tpl = TR.apply(tpl)
tpl = T2.apply(tpl)
tpl = T3.apply(tpl)
open('v4.template.html', 'w', encoding='utf-8', newline='\n').write(tpl)

imgs = json.load(open('imgs6.json'))
imgs.update({k: v for k, v in json.load(open('imgs3.json')).items() if k in ('sea_l', 'desert_l', 'nile_l')})
# keep every image the page names: "key", IMG.key, IMG["key"], and "img:key|caption"
used = {k for k in imgs if re.search(r'(["\'.\[ ]|img:)' + re.escape(k) + r'(["\'\]|])', tpl) or k.startswith('d_')}
imgs = {k: v for k, v in imgs.items() if k in used}
out = tpl.replace('{{imgmap}}', json.dumps(imgs)).replace('{{liveimgs}}', open('live/live-imgs.json').read())
assert '{{' not in out, out[out.index('{{') - 60:out.index('{{') + 40]
open('journey-v4.html', 'w', encoding='utf-8', newline='\n').write(out)
print(round(len(out) / 1024), 'KB', len(imgs), 'images')
