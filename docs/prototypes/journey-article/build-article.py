# Article template prototype (owner, 16 Oct: "the 10 trips keep their own designs; the articles get one shared template").
# Two real guides from content/editorial/en are rendered through the same template.
import re, json, html, sys

REPO = sys.argv[1]           # repo root (content/editorial/en)
PROTO = sys.argv[2]          # scratchpad proto folder (images)

def md_inline(s):
    s = html.escape(s, quote=False)
    s = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', s)
    s = re.sub(r'(?<![\w*])\*(?!\s)(.+?)\*(?!\w)', r'<em>\1</em>', s)
    def link(m):
        text, url = m.group(1), m.group(2)
        exp = re.match(r'/experiences/([^/]+)/', url)
        cls = ' class="tlink" data-trip="%s"' % SLUG2TRIP[exp.group(1)] if exp and exp.group(1) in SLUG2TRIP else ''
        return '<a href="#"%s title="%s">%s</a>' % (cls, html.escape(url), text)
    s = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', link, s)
    return s

def parse(path):
    raw = open(path, encoding='utf-8').read()
    fm, body = re.match(r'---\n(.*?)\n---\n(.*)', raw, re.S).groups()
    meta = dict(re.findall(r'^(\w+): (.*)$', fm, re.M))
    blocks = re.split(r'\n\s*\n', body.strip())
    out, toc, faq, words = [], [], [], 0
    for b in blocks:
        words += len(re.findall(r'\w+', b))
        if b.strip() == '[[toc]]':
            continue
        m = re.match(r'## (.+?) \{#([\w-]+)\}$', b)
        if m:
            toc.append((m.group(2), m.group(1)))
            out.append(('h2', m.group(2), m.group(1)))
            continue
        if b.startswith('?? '):
            q, a = b[3:].split('\n', 1)
            faq.append((q.strip(), md_inline(a.strip())))
            continue
        if b.startswith('> '):
            lines = [l[2:] if l.startswith('> ') else l.lstrip('>') for l in b.split('\n')]
            title = re.match(r'\*\*(.+)\*\*', lines[0]).group(1)
            rest = lines[1:]
            if rest and rest[0].startswith('- '):
                inner = '<ul>' + ''.join('<li>%s</li>' % md_inline(l[2:]) for l in rest) + '</ul>'
            else:
                inner = '<p>%s</p>' % md_inline(' '.join(rest))
            out.append(('tip', title, inner))
            continue
        if b.startswith('- '):
            items = [l[2:] for l in b.split('\n')]
            out.append(('ul', [md_inline(i) for i in items]))
            continue
        out.append(('p', md_inline(b.replace('\n', ' '))))
    return meta, out, toc, faq, words

SLUG2TRIP = {'valley-of-the-kings-hatshepsut-temple': 'valley', 'ras-mohammed-snorkelling-from-sharm': 'ras',
             'red-sea-diving-two-reef-dives': 'redsea'}

# Trip facts are the prototype's own (from the site's offers and the partners' pages, 16 Oct).
TRIPS = {
    'valley': dict(title='Valley of the Kings & Hatshepsut Temple', img='trip', world='tomb', place='Luxor · The West Bank',
                   facts=['About 4–10 hours', 'Private option available', 'Pickup offered'], n=3, partners='Viator · GetYourGuide',
                   go='Go down into the valley', opts=[('Private full day on Luxor West Bank','Viator',['Private','About 7–10 hours','Pickup offered']),('Luxor West Bank full day with Medinet Habu and Deir el-Medina','Viator',['About 6–8 hours','Pickup offered']),('West Bank Guided Tour: Valley of the Kings','GetYourGuide',['Private','4 hours','Pickup offered'])]),
    'ras': dict(title='Ras Mohammed Snorkelling from Sharm El Sheikh', img='ras_turq', world='sea', place='Sharm El Sheikh · South Sinai',
                facts=['About 5–6 hours', 'Pickup offered', 'Every level'], n=2, partners='Viator · GetYourGuide',
                go='Into the park', opts=[('Ras Mohammed snorkeling trip from Sharm El Sheikh','Viator',['About 5 hours','Pickup offered']),('From Sharm: White Island and Ras Mohamed Snorkeling Trip','GetYourGuide',['6 hours','Pickup offered'])]),
    'redsea': dict(title='Red Sea Diving: Two Reef Dives', img='sea_l', world='sea', place='Hurghada',
                   facts=['About 7 hours to 1 day', 'Pickup offered', 'Beginners and certified'], n=3, partners='Viator · GetYourGuide',
                   go='Live the dive day', opts=[('Two boat dives from Hurghada (certified divers)','Viator',['About 1 day','Pickup offered']),('Two dives from Hurghada for beginners and certified divers','Viator',['About 7 hours','Pickup offered']),('Hurghada: Scuba Diving for Certified Divers or Beginners','GetYourGuide',['7 hours','Pickup offered'])]),
}

ARTICLES = {
    'valley': dict(file='g-valley-kings.md', crumb='Luxor', hero=('img', 'kv62', 'KV62, the tomb of Tutankhamun, Valley of the Kings · Photo: Unsplash'),
                   figs={'west-bank': ('hat_l', 'The Mortuary Temple of Hatshepsut, Deir el-Bahari · Photo: Unsplash')},
                   stay=('Staying in Luxor?', 'Hotels in Luxor and on the West Bank', 'Trip.com'),
                   more=[('g-west-bank.md', None), ('g-east-bank.md', 'd_luxor'), ('g-nile-cruise.md', 'sc_nile')]),
    'snorkel': dict(file='g-snorkelling.md', crumb='Red Sea', hero=('video', 'ras_turq', 'Red Sea, Egypt · Video: Adrien JACTA, Pexels', 'video/redsea-reef.mp4'),
                    figs={'hurghada': ('d_hurghada', 'The coast at Hurghada · Photo: Unsplash'),
                          'sharm': ('d_sharm', 'A bay at Sharm El Sheikh · Photo: Unsplash')},
                    stay=('Staying in Sharm El Sheikh?', 'Hotels in Sharm El Sheikh', 'Trip.com'),
                    more=[('g-red-sea-dives.md', 'hurg_diver'), ('g-sinai.md', None), ('g-10-days.md', 'sc_nile')]),
}

data = {}
for key, a in ARTICLES.items():
    meta, blocks, toc, faq, words = parse(f'{REPO}/content/editorial/en/{a["file"]}')
    more = []
    for f, img in a['more']:
        m2 = parse(f'{REPO}/content/editorial/en/{f}')
        more.append(dict(title=m2[0]['title'], excerpt=m2[0]['excerpt'], img=img, mins=max(1, round(m2[4] / 220))))
    related = [r.strip() for r in meta['related'].split(',')]
    data[key] = dict(title=meta['title'], excerpt=meta['excerpt'], topic=meta['topic'], crumb=a['crumb'], hero=a['hero'],
                     mins=max(1, round(words / 220)), blocks=blocks, toc=toc, faq=faq, figs=a['figs'], stay=a['stay'],
                     trips=[r.replace('exp-', '').replace('dive', 'redsea').replace('ras-mohammed', 'ras') for r in related],
                     more=more)

imgs = json.load(open(f'{PROTO}/imgs6.json'))
imgs.update({k: v for k, v in json.load(open(f'{PROTO}/imgs3.json')).items() if k in ('sea_l', 'nile_l')})
used = {'kv62', 'hat_l', 'd_luxor', 'sc_nile', 'trip', 'ras_turq', 'sea_l', 'd_hurghada', 'd_sharm', 'hurg_diver'}
imgs = {k: v for k, v in imgs.items() if k in used}
assert used <= set(imgs), used - set(imgs)

tpl = open(f'{PROTO}/article/article.template.html', encoding='utf-8').read()
out = (tpl.replace('{{data}}', json.dumps(data, ensure_ascii=False))
          .replace('{{trips}}', json.dumps(TRIPS, ensure_ascii=False))
          .replace('{{imgmap}}', json.dumps(imgs)))
assert '{{' not in out
open(f'{PROTO}/article/journey-article.html', 'w', encoding='utf-8', newline='\n').write(out)
print(round(len(out) / 1024), 'KB')
