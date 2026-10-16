import re, json

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
    ('{n:"01",eye:"Timeless wonders",name:"Pyramids",', '{n:"01",video:"video/giza-sunset.mp4",vcredit:"Video: Peggy Anke, Pexels · Giza",eye:"Timeless wonders",name:"Pyramids",'),
    ('<section class="ch on-dark" id="ch-${i+1}"><img class="ch__img" src="${IMG[c.img]}" alt="">',
     '<section class="ch on-dark" id="ch-${i+1}">${c.video?`<video class="ch__img" autoplay muted loop playsinline poster="${IMG[c.img]}" src="${c.video}"></video><span class="vbadge">${esc(c.vcredit)}</span>`:`<img class="ch__img" src="${IMG[c.img]}" alt="">`}'),
]
for x, y in reps:
    assert js.count(x) == 1, x
    js = js.replace(x, y)
js = js[:js.index('let step=1;')] + open('v4-nav.js', encoding='utf-8').read()

v2 = open('journey-story.template.html', encoding='utf-8').read()
head = v2[:v2.index('<style>')].replace('<title>Egypt Roamer Story Journey</title>', '<title>Egypt Roamer Story Worlds</title>')
head = head.replace('family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap', 'family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Noto+Sans+Egyptian+Hieroglyphs&display=swap')
css = v2[v2.index('<style>') + 7:v2.index('</style>')] + open('v4-worlds.css', encoding='utf-8').read()
clock = re.search(r'const CLOCK = `([\s\S]*?)`;', v2).group(1)
js = js.replace('{{clock}}', clock)
tpl = head + '<style>' + css + '</style>\n' + body + '\n<script>\n' + js + '\n' + open('v4-worlds.js', encoding='utf-8').read() + '\nrenderHome(); go(1);\n</script>\n'
open('v4.template.html', 'w', encoding='utf-8', newline='\n').write(tpl)

imgs = json.load(open('imgs2.json'))
imgs.update({k: v for k, v in json.load(open('imgs3.json')).items() if k in ('sea_l', 'desert_l', 'nile_l')})
keys = set(imgs)
used = {k for k in keys if re.search(r'["\'.\[ ]' + k + r'["\'\]]', tpl) or ('"' + k + '"') in tpl or ('m_' in k) or ('d_' in k)}
imgs = {k: v for k, v in imgs.items() if k in used}
out = tpl.replace('{{imgmap}}', json.dumps(imgs))
assert '{{' not in out, out[out.index('{{') - 60:out.index('{{') + 40]
open('journey-v4.html', 'w', encoding='utf-8', newline='\n').write(out)
print(round(len(out) / 1024), 'KB', len(imgs), 'images:', sorted(imgs))
