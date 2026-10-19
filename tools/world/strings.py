"""Merge the trip worlds' translations into tools/i18n/new-strings.json.

- tools/world/aligned.json (tools/world/align.py): strings read from the pages' own translated bodies;
- tools/world/manual-*.json: strings the prototype wrote itself, by their number in tools/world/need-manual.json
  (translated for this release, not yet reviewed by native speakers).

Existing entries are never changed. Usage: PYTHONUTF8=1 python tools/world/strings.py
"""
import glob, json, os

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
W = os.path.join(ROOT, 'tools/world')
need = json.load(open(os.path.join(W, 'need-manual.json'), encoding='utf-8'))
manual = {}
for f in sorted(glob.glob(os.path.join(W, 'manual-*.json'))):
    for k, v in json.load(open(f, encoding='utf-8')).items():
        assert len(v) == 7, (k, len(v))
        manual[need[int(k)]] = v
missing = [s for s in need if s not in manual]
assert not missing, missing
json.dump(manual, open(os.path.join(W, 'manual.json'), 'w', encoding='utf-8', newline='\n'), ensure_ascii=False, indent=1)
aligned = json.load(open(os.path.join(W, 'aligned.json'), encoding='utf-8'))['aligned']
p = os.path.join(ROOT, 'tools/i18n/new-strings.json')
d = json.load(open(p, encoding='utf-8'))
n = 0
for src in (aligned, manual):
    for k, v in src.items():
        if k not in d['strings']:
            d['strings'][k] = v
            n += 1
open(p, 'w', encoding='utf-8', newline='\n').write(json.dumps(d, ensure_ascii=False, indent=1) + '\n')
print('added', n)
