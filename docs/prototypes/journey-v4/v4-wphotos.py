# Trip steps without a photo (owner, 17 Oct: "the ones without photos, look on Wikipedia").
# Each photo is from Wikimedia Commons, of the exact place, under a free licence (CC BY, CC BY-SA, CC0 or public
# domain), credited on the step with the author and licence. Chosen by looking at each candidate; the list, with
# file pages, is wpick.json (copied to docs/prototypes/journey-v4/). Steps left without one, on purpose:
#  - "Leave in the dark", "By road", "Check-in" style steps where no photo shows the place itself... except the
#    Hurghada marina, which is where dive boats leave from;
#  - Agabat: Commons has no photo of Egypt's Agabat;
#  - belly dance: no photo of a performer is used;
#  - sand-boarding: the only Commons photos are from Fayoum and Sinai, not Siwa.
import json, os
HERE = os.path.dirname(os.path.abspath(__file__))
P = json.load(open(os.path.join(HERE, 'wpick.json'), encoding='utf-8'))
ARTIST = {'w_abu_move': 'Museo Egizio, Turin (photo archive)', 'w_siwa_dunes': 'Walaa', 'w_siwa_sunset': 'Walaa'}
CAPTION = {'w_abu_move': 'The Great Temple shortly before the move'}


def credit(k, v):
    who = ARTIST.get(k) or v['artist']
    lic = 'public domain' if v['lic'] in ('public domain', 'CC0') else v['lic']
    pre = CAPTION[k] + ' · ' if k in CAPTION else ''
    return f'{pre}Photo: {who}, Wikimedia Commons, {lic}'


def apply(tpl):
    rows = [{'k': k, 'trip': v['trip'], 'i': v['step'] - 1, 'credit': credit(k, v)} for k, v in P.items()]
    js = ('\n// ---------- Commons photos for steps that had none, 17 Oct ----------\n'
          'Object.assign(IMG,' + json.dumps({k: v['file'] for k, v in P.items()}) + ');\n'
          + json.dumps(rows, ensure_ascii=False) + '.forEach(r=>{ const s=WORLDS[r.trip]&&WORLDS[r.trip].steps[r.i]; if(s&&!s.img&&!s.video){ s.img=r.k; s.credit=r.credit; } });\n')
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + js + tpl[i:]
