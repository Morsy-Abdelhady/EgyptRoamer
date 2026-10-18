# Prototype parity checks (18 Oct 2026)

Side-by-side screenshots of the proposal 4 prototype and the live site, component by component.
Run from a folder that has a `ph/` subfolder; needs `playwright-core` with the system Chrome.

- `PROTO`: path to the built prototype page, `journey-v4.html`. It is built from
  `docs/prototypes/journey-v4/` and needs its `img/` and `video/` folders next to it. The last build was in the
  18 Oct session's scratchpad (`.../scratchpad/proto/journey-v4.html`). The published copy is
  https://claude.ai/artifact/8Nni2DJA4aBkoDwc2WxEt7.
- `node pairs.mjs <screen> <site-url> <tag> "<proto-sel>=<site-sel>" ...` captures each pair at the prototype
  frame's width (1048 px). Screens: `1` home, `2` Trips & tours, `w:<world>` a trip (valley, redsea, white, nile,
  giza, abu, siwa, ras, alex, food, cruise), `p` Places, `g` Travel Guide, `ga:<guide-slug>` a guide.
  Then run `python pairsheet.py <tag> 0.5`; it writes `ph/PR-<tag>-<n>.png` (prototype left, site right).
- `pg.mjs <url> <tag> [width]` scrolls a page and takes one shot per screen; `sheet.py` makes a contact sheet.
- `wv.mjs <trip-slug>` checks which trip videos play as each step is reached.
- `cssx.py "<class,...>" out.css` pulls a component's rules out of the prototype's CSS.

Note: elements taller than the prototype frame come out blank with `pairs.mjs`. Use `pg.mjs`-style scrolled
screenshots for those.
