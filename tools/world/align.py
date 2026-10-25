"""Translations for the trip "worlds" (proposal 4) from the pages' own translated bodies.

The prototype (docs/prototypes/journey-v4) built each trip page from that page's English body: a paragraph, a list
item, a callout line, or a run of sentences from one of them. The bodies are translated into every language with
the same structure (Core data/editorial/<lang>/exp-*.html), so each prototype string is looked up in the English
body and the same place is read in each translation. Strings the prototype wrote itself (headings, labels) are not
in any body; they come from tools/world/manual.json (translated by hand, not yet reviewed by native speakers).

Usage: python tools/world/align.py  ->  tools/world/aligned.json  {english: [de, fr, it, es, ru, zh, ar]} and a
list of the strings still without a translation.
"""
import json, os, re, html, sys

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
ED = os.path.join(ROOT, 'wordpress/wp-content/plugins/egypt-roamer-core/data/editorial')
LANGS = ['de', 'fr', 'it', 'es', 'ru', 'zh', 'ar']
SEEDS = {'valley': 'exp-valley', 'redsea': 'exp-dive', 'white': 'exp-safari', 'nile': 'exp-dinner', 'giza': 'exp-giza',
         'abu': 'exp-abu', 'alex': 'exp-alexandria', 'ras': 'exp-ras-mohammed', 'siwa': 'exp-siwa', 'food': 'exp-food',
         'cruise': 'exp-cruise', 'balloon': 'exp-balloon'}


def text(h):
    h = re.sub(r'<!--.*?-->', '', h, flags=re.S)
    return re.sub(r'\s+', ' ', html.unescape(re.sub(r'<[^>]+>', '', h))).strip()


def units(path):
    """[(key, text)] in reading order; key = section/kind/index[/part]."""
    src = open(path, encoding='utf-8').read()
    out = []
    parts = re.split(r'<h2[^>]*id="([^"]+)"[^>]*>(.*?)</h2>', src, flags=re.S)
    # parts: [pre, id, title, body, id, title, body, ...]
    for k in range(1, len(parts), 3):
        sid, title, body = parts[k], text(parts[k + 1]), parts[k + 2]
        out.append((f'{sid}/h', title))
        co = re.search(r'<div class="wp-block-group er-callout">(.*?)</div>\s*<!-- /wp:group -->', body, flags=re.S)
        if co:
            cb = co.group(1)
            h3 = re.search(r'<h3[^>]*>(.*?)</h3>', cb, flags=re.S)
            if h3:
                out.append((f'{sid}/coh', text(h3.group(1))))
            for i, li in enumerate(re.findall(r'<li>(.*?)</li>', cb, flags=re.S)):
                add_li(out, f'{sid}/co{i}', li)
            if not re.search(r'<li>', cb):
                for i, p in enumerate(re.findall(r'<p>(.*?)</p>', cb, flags=re.S)):
                    add_li(out, f'{sid}/co{i}', p)
            body = body[:co.start()] + body[co.end():]
        for i, (q, a) in enumerate(re.findall(r'<summary>(.*?)</summary>(.*?)</details>', body, flags=re.S)):
            out.append((f'{sid}/q{i}', text(q)))
            out.append((f'{sid}/a{i}', text(a)))
        body = re.sub(r'<details.*?</details>', '', body, flags=re.S)
        for i, p in enumerate(re.findall(r'<p>(.*?)</p>', body, flags=re.S)):
            out.append((f'{sid}/p{i}', text(p)))
        for i, li in enumerate(re.findall(r'<li>(.*?)</li>', body, flags=re.S)):
            add_li(out, f'{sid}/li{i}', li)
    return out


def add_li(out, key, li):
    out.append((key, text(li)))
    m = re.match(r'\s*<strong>(.*?)</strong>(.*)$', li, flags=re.S)
    if m:
        out.append((key + '/lead', re.sub(r'[\s:：.。,،]+$', '', text(m.group(1)))))
        rest = re.sub(r'^[\s:：،,–—-]+', '', text(m.group(2)))
        out.append((key + '/rest', rest[:1].upper() + rest[1:] if rest else rest))


SENT = re.compile(r'(?<=[.!?。！？])\s*(?=\S)')


def sents(t):
    return [s.strip() for s in SENT.split(t) if s.strip()]


def norm(s):
    s = s.replace('’', "'").replace('‘', "'").replace('“', '"').replace('”', '"').replace('–', '-').replace('—', '-')
    s = re.sub(r'\s+', ' ', s).strip()
    return s[:1].upper() + s[1:]


def find(s, en):
    """Where an English prototype string sits in the English body: (key, None) or (key, (i, j)) sentences."""
    ns = norm(s)
    for key, t in en:
        if norm(t) == ns or norm(re.sub(r'[\s:：.。]+$', '', t)) == norm(re.sub(r'[\s:：.。]+$', '', s)):
            return key, None
    for key, t in en:
        ss = [norm(x) for x in sents(t)]
        want = [norm(x) for x in sents(s)]
        idx, j = [], 0
        for w_ in want:
            while j < len(ss) and ss[j] != w_:
                j += 1
            if j == len(ss):
                break
            idx.append(j)
            j += 1
        if len(idx) == len(want):
            return key, idx
    return None


def main():
    d = json.load(open(os.path.join(ROOT, 'docs/prototypes/journey-v4/world-data.json'), encoding='utf-8'))
    manual = json.load(open(os.path.join(ROOT, 'tools/world/manual.json'), encoding='utf-8')) if os.path.exists(os.path.join(ROOT, 'tools/world/manual.json')) else {}
    aligned, missing, where = {}, [], {}
    for wk, w in d['WORLDS'].items():
        seed = SEEDS[wk]
        en = units(os.path.join(ED, 'en', seed + '.html'))
        tr = {l: dict(units(os.path.join(ED, l, seed + '.html'))) for l in LANGS}
        for s in strings_of(w):
            if s in aligned or s in manual:
                continue
            hit = find(s, en)
            if not hit:
                missing.append(s)
                continue
            key, rng = hit
            vals = []
            for l in LANGS:
                t = tr[l].get(key)
                if t is None:
                    vals = None
                    break
                if rng:
                    ts = sents(t)
                    if len(ts) != len(sents(dict(en)[key])):
                        vals = None
                        break
                    t = ('' if l == 'zh' else ' ').join(ts[i] for i in rng)
                vals.append(t)
            if vals:
                aligned[s] = vals
                where[s] = f'{seed}:{key}' + (f'{rng}' if rng else '')
            else:
                missing.append(s)
    json.dump({'aligned': aligned, 'where': where, 'missing': missing}, open(os.path.join(ROOT, 'tools/world/aligned.json'), 'w', encoding='utf-8', newline='\n'), ensure_ascii=False, indent=1)
    print(len(aligned), 'aligned,', len(missing), 'missing,', len(manual), 'manual')


def strings_of(w):
    out = [w.get('eyebrow'), w.get('lead'), w.get('quote'), w.get('go'), w.get('jEyebrow'), w.get('jTitle')]
    for s in w['steps']:
        out += [s.get('t'), s.get('p'), s.get('why')]
    out += [w['sec'].get('eyebrow'), w['sec'].get('title')] + list(w['sec'].get('ps', []))
    for g, b, t in w['sec']['tabs']:
        out += [b, t]
    for p in w['papy']:
        out += [p[0], p[1], p[2]]
    for q, a in w.get('faq') or []:
        out += [q, a]
    if w.get('stay'):
        out.append(w['stay']['title'])
    return [s for s in out if s]


if __name__ == '__main__':
    main()
