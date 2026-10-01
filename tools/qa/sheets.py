# Contact sheets from visual-sheets.mjs: python sheets.py <sheet-lang> [height] [per_row]
# Each screenshot is scaled to the same height, labelled with its file name, and laid out in rows.
import json, sys
from PIL import Image, ImageDraw

name = sys.argv[1]
H = int(sys.argv[2]) if len(sys.argv) > 2 else 520
per_row = int(sys.argv[3]) if len(sys.argv) > 3 else 6
files = json.load(open(f"sheets/{name}.json", encoding="utf-8"))
tiles = []
for f in files:
    im = Image.open(f).convert("RGB")
    w = max(1, int(im.width * H / im.height))
    im = im.resize((w, H))
    label = Image.new("RGB", (w, 22), "white")
    ImageDraw.Draw(label).text((4, 4), f.split("_", 1)[1].replace(".png", ""), fill="black")
    tile = Image.new("RGB", (w, H + 22), "white")
    tile.paste(label, (0, 0))
    tile.paste(im, (0, 22))
    tiles.append(tile)
rows = [tiles[i:i + per_row] for i in range(0, len(tiles), per_row)]
W = max(sum(t.width for t in r) + 10 * (len(r) - 1) for r in rows)
out = Image.new("RGB", (W, sum(r[0].height for r in rows) + 10 * (len(rows) - 1)), (200, 200, 200))
y = 0
for r in rows:
    x = 0
    for t in r:
        out.paste(t, (x, y))
        x += t.width + 10
    y += r[0].height + 10
out.save(f"sheets/{name}.png")
print(f"sheets/{name}.png", out.size)
