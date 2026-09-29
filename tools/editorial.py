#!/usr/bin/env python3
"""Compile the editorial sources (content/editorial/<lang>/*.md) into WordPress block markup.

    python tools/editorial.py              # every language -> wordpress/wp-content/plugins/egypt-roamer-core/data/editorial/<lang>/
    python tools/editorial.py --lang ar    # one language
    python tools/editorial.py check        # fails if any compiled file is stale or a translation is malformed

Then, on the server: `wp egypt-roamer editorial [--lang=ar] --dry-run` (fills only empty or untouched seed text).

Translations (any language but English) are named like the English file they translate and must keep its
type, destination and section anchors. Extra front matter:

    source: 1a2b3c4d5e6f          # printed by this script: the English file it was translated from
    review: pending               # pending | approved. Only approved, up-to-date files are imported
    reviewer: Name                # who approved it
    reviewed: 2026-09-29          # when

Internal links keep the English paths (/destinations/luxor/); the import points them at the translation.

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
import hashlib
import html
import json
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
SRC_ROOT = ROOT / "content" / "editorial"
OUT_ROOT = ROOT / "wordpress" / "wp-content" / "plugins" / "egypt-roamer-core" / "data" / "editorial"
# The one fixed UI string the compiler writes. A language needs its label here before it can be compiled.
TOC_LABEL = {
    "en": "On this page",
    "de": "Auf dieser Seite",
    "fr": "Sur cette page",
    "it": "In questa pagina",
    "es": "En esta página",
    "ru": "На этой странице",
    "zh": "本页内容",
    "ar": "في هذه الصفحة",
}


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


def compile_file(path: pathlib.Path, lang: str = "en"):
    raw = path.read_text(encoding="utf-8")
    m = re.match(r"^---\n(.*?)\n---\n(.*)$", raw, re.S)
    if not m:
        raise SystemExit(f"{path.name}: missing front matter")
    meta = dict(re.findall(r"^(\w+):\s*(.+)$", m.group(1), re.M))
    lines = m.group(2).split("\n")
    parts = blocks([l for l in lines if l.strip() != "[[toc]]"])
    if any(l.strip() == "[[toc]]" for l in lines):
        heads = re.findall(r'<h2 class="wp-block-heading" id="([^"]+)">(.*?)</h2>', "\n".join(parts))
        toc = group(para(TOC_LABEL[lang]) + lst([f"[{html.unescape(re.sub('<[^>]+>', '', t))}](#{a})" for a, t in heads]), "er-toc")
        parts.insert(0, toc)
    return meta, "\n\n".join(parts) + "\n"


def source_hash(path: pathlib.Path) -> str:
    """Short fingerprint of an English source file; a translation records the one it was made from."""
    return hashlib.sha256(path.read_bytes()).hexdigest()[:12]


def anchors(body: str):
    return re.findall(r'<h2 class="wp-block-heading" id="([^"]+)">', body)


def build(lang="en", write=True) -> bool:
    if lang not in TOC_LABEL:
        raise SystemExit(f"{lang}: add its 'On this page' label to TOC_LABEL first")
    src_dir, out_dir = SRC_ROOT / lang, OUT_ROOT / lang
    index, stale = {}, False
    out_dir.mkdir(parents=True, exist_ok=True)
    for src in sorted(src_dir.glob("*.md")):
        meta, body = compile_file(src, lang)
        seed_id = src.stem
        index[seed_id] = {k: meta[k] for k in ("type", "excerpt", "destination") if k in meta}
        if lang != "en":
            en = SRC_ROOT / "en" / src.name
            if not en.exists():
                raise SystemExit(f"{lang}/{src.name}: no English file with that name")
            en_meta, en_body = compile_file(en)
            for key in ("type", "destination"):
                if meta.get(key) != en_meta.get(key):
                    raise SystemExit(f"{lang}/{src.name}: {key} must match the English file")
            if anchors(body) != anchors(en_body):
                raise SystemExit(f"{lang}/{src.name}: section anchors differ from the English file")
            if not meta.get("excerpt"):
                raise SystemExit(f"{lang}/{src.name}: excerpt is missing")
            review = meta.get("review", "")
            if review not in ("pending", "approved"):
                raise SystemExit(f"{lang}/{src.name}: review must be pending or approved")
            if review == "approved" and not (meta.get("reviewer") and meta.get("reviewed")):
                raise SystemExit(f"{lang}/{src.name}: an approved file needs reviewer and reviewed")
            index[seed_id]["review"] = review
            # The English changed since this was translated: re-review before it can be imported.
            index[seed_id]["current"] = meta.get("source") == source_hash(en)
        target = out_dir / f"{seed_id}.html"
        if not target.exists() or target.read_text(encoding="utf-8") != body:
            stale = True
            if write:
                target.write_text(body, encoding="utf-8", newline="\n")
    idx = json.dumps(index, ensure_ascii=False, indent=1) + "\n"
    target = out_dir / "index.json"
    if not target.exists() or target.read_text(encoding="utf-8") != idx:
        stale = True
        if write:
            target.write_text(idx, encoding="utf-8", newline="\n")
    return stale


def status(lang: str) -> None:
    """Review state of each translation, and whether its English source changed since."""
    for src in sorted((SRC_ROOT / lang).glob("*.md")):
        meta, _ = compile_file(src, lang)
        en = SRC_ROOT / "en" / src.name
        current = en.exists() and meta.get("source") == source_hash(en)
        print(f"{lang}/{src.stem:12} {meta.get('review', '?'):9} {'current' if current else 'OUTDATED (English source ' + (source_hash(en) if en.exists() else 'missing') + ')'}")


if __name__ == "__main__":
    args = sys.argv[1:]
    langs = sorted(d.name for d in SRC_ROOT.iterdir() if d.is_dir())
    if "--lang" in args:
        langs = [args[args.index("--lang") + 1]]
    if "check" in args:
        stale = [l for l in langs if build(l, write=False)]
        sys.exit(f"editorial HTML is stale ({', '.join(stale)}): run python tools/editorial.py" if stale else 0)
    if "status" in args:
        for l in langs:
            if l != "en":
                status(l)
        sys.exit(0)
    for l in langs:
        build(l)
        print(f"editorial compiled ({l}):", len(list((SRC_ROOT / l).glob("*.md"))), "items ->", (OUT_ROOT / l).relative_to(ROOT))
