#!/usr/bin/env python3
"""Compile the English editorial sources (content/editorial/en/*.md) into WordPress block markup.

    python tools/editorial.py          # writes wordpress/wp-content/plugins/egypt-roamer-core/data/editorial/en/
    python tools/editorial.py check    # fails if the compiled files are stale

Then, on the server: `wp egypt-roamer editorial` (fills only empty or untouched seed text).

Source format (one file per item, named after its seed id):

    ---
    type: er_destination            # er_destination | er_experience | er_guide
    excerpt: One or two sentences for cards and the meta description.
    destination: cairo              # optional: guide → destination link
    ---
    [[toc]]                         # "On this page" box built from the ## headings
    ## Heading {#anchor}            # h2 with an anchor (anchor optional: derived from the text)
    ### Sub-heading                 # h3
    Paragraph text with **bold**, *italic* and [links](/destinations/luxor/).
    - bullet                        # unordered list
    1. step                         # ordered list
    + **Day 1 — Title** text        # itinerary (day-by-day)
    > **Callout title**             # planning note box; every line starts with "> "
    > Callout paragraph or - list
    ?? Question?                    # FAQ item (accordion); the following lines are the answer
    Answer paragraph.
"""
import html
import json
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
SRC = ROOT / "content" / "editorial" / "en"
OUT = ROOT / "wordpress" / "wp-content" / "plugins" / "egypt-roamer-core" / "data" / "editorial" / "en"


def inline(text: str) -> str:
    text = html.escape(text, quote=False)
    text = re.sub(r"\[([^\]]+)\]\(([^)\s]+)\)", lambda m: f'<a href="{html.escape(m.group(2))}">{m.group(1)}</a>', text)
    text = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", text)
    text = re.sub(r"(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?!\w)", r"<em>\1</em>", text)
    return text


def slug(text: str) -> str:
    return re.sub(r"[^a-z0-9]+", "-", text.lower()).strip("-")


def para(text: str) -> str:
    return f"<!-- wp:paragraph -->\n<p>{inline(text)}</p>\n<!-- /wp:paragraph -->"


def lst(items, ordered=False, cls=""):
    tag = "ol" if ordered else "ul"
    attrs = {}
    if ordered:
        attrs["ordered"] = True
    if cls:
        attrs["className"] = cls
    head = f"<!-- wp:list {json.dumps(attrs)} -->" if attrs else "<!-- wp:list -->"
    klass = "wp-block-list" + (f" {cls}" if cls else "")
    body = "\n".join(f"<!-- wp:list-item -->\n<li>{inline(i)}</li>\n<!-- /wp:list-item -->" for i in items)
    return f"{head}\n<{tag} class=\"{klass}\">{body}</{tag}>\n<!-- /wp:list -->"


def heading(text: str, level: int, anchor: str) -> str:
    attrs = {"level": level} if level != 2 else {}
    if anchor:
        attrs["anchor"] = anchor
    head = f"<!-- wp:heading {json.dumps(attrs)} -->" if attrs else "<!-- wp:heading -->"
    idattr = f' id="{anchor}"' if anchor else ""
    return f"{head}\n<h{level} class=\"wp-block-heading\"{idattr}>{inline(text)}</h{level}>\n<!-- /wp:heading -->"


def group(inner: str, cls: str) -> str:
    return f'<!-- wp:group {{"className":"{cls}","layout":{{"type":"constrained"}}}} -->\n<div class="wp-block-group {cls}">{inner}</div>\n<!-- /wp:group -->'


def blocks(lines):
    """Paragraphs, lists, headings, callouts and FAQ items from a list of source lines."""
    out, i = [], 0
    while i < len(lines):
        line = lines[i].rstrip()
        if not line.strip():
            i += 1
            continue
        if line.startswith("> "):
            inner = []
            while i < len(lines) and lines[i].startswith(">"):
                inner.append(lines[i][2:] if lines[i].startswith("> ") else "")
                i += 1
            parts = inner
            title = ""
            m = re.match(r"^\*\*(.+)\*\*$", parts[0].strip()) if parts else None
            if m:
                title, parts = m.group(1), parts[1:]
            body = (heading(title, 3, "") if title else "") + "".join(blocks(parts))
            out.append(group(body, "er-callout"))
            continue
        if line.startswith("?? "):
            q = line[3:].strip()
            i += 1
            ans = []
            while i < len(lines) and lines[i].strip() and not lines[i].startswith(("?? ", "## ", "### ")):
                ans.append(lines[i].strip())
                i += 1
            inner = "".join(para(a) for a in [" ".join(ans)])
            out.append(f"<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{inline(q)}</summary>{inner}</details>\n<!-- /wp:details -->")
            continue
        m = re.match(r"^(#{2,3}) (.+?)(?: \{#([a-z0-9-]+)\})?$", line)
        if m:
            level = len(m.group(1))
            anchor = m.group(3) or (slug(m.group(2)) if level == 2 else "")
            out.append(heading(m.group(2), level, anchor))
            i += 1
            continue
        for marker, ordered, cls in (("- ", False, ""), ("1. ", True, ""), ("+ ", True, "er-itinerary")):
            pat = r"^\d+\. " if marker == "1. " else re.escape(marker)
            if re.match(pat, line):
                items = []
                while i < len(lines) and re.match(pat, lines[i]):
                    items.append(re.sub(pat, "", lines[i], count=1).strip())
                    i += 1
                out.append(lst(items, ordered, cls))
                break
        else:
            buf = []
            while i < len(lines) and lines[i].strip() and not re.match(r"^(#{2,3} |- |\d+\. |\+ |> |\?\? |\[\[toc\]\])", lines[i]):
                buf.append(lines[i].strip())
                i += 1
            if buf:
                out.append(para(" ".join(buf)))
            else:
                i += 1
    return out


def compile_file(path: pathlib.Path):
    raw = path.read_text(encoding="utf-8")
    m = re.match(r"^---\n(.*?)\n---\n(.*)$", raw, re.S)
    if not m:
        raise SystemExit(f"{path.name}: missing front matter")
    meta = dict(re.findall(r"^(\w+):\s*(.+)$", m.group(1), re.M))
    lines = m.group(2).split("\n")
    parts = blocks([l for l in lines if l.strip() != "[[toc]]"])
    if any(l.strip() == "[[toc]]" for l in lines):
        heads = re.findall(r'<h2 class="wp-block-heading" id="([^"]+)">(.*?)</h2>', "\n".join(parts))
        toc = group(para("On this page") + lst([f"[{html.unescape(re.sub('<[^>]+>', '', t))}](#{a})" for a, t in heads]), "er-toc")
        parts.insert(0, toc)
    return meta, "\n\n".join(parts) + "\n"


def build(write=True) -> bool:
    index, stale = {}, False
    OUT.mkdir(parents=True, exist_ok=True)
    for src in sorted(SRC.glob("*.md")):
        meta, body = compile_file(src)
        seed_id = src.stem
        index[seed_id] = {k: meta[k] for k in ("type", "excerpt", "destination") if k in meta}
        target = OUT / f"{seed_id}.html"
        if not target.exists() or target.read_text(encoding="utf-8") != body:
            stale = True
            if write:
                target.write_text(body, encoding="utf-8", newline="\n")
    idx = json.dumps(index, ensure_ascii=False, indent=1) + "\n"
    target = OUT / "index.json"
    if not target.exists() or target.read_text(encoding="utf-8") != idx:
        stale = True
        if write:
            target.write_text(idx, encoding="utf-8", newline="\n")
    return stale


if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "check":
        sys.exit("editorial HTML is stale: run python tools/editorial.py" if build(write=False) else 0)
    build()
    print("editorial compiled:", len(list(SRC.glob("*.md"))), "items ->", OUT.relative_to(ROOT))
