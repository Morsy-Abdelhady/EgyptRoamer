# Owner, 17 Oct: "review production and see what was forgotten or removed". The prototype's homepage had dropped
# five production sections. They come back here as production has them, run by the theme's own components:
#   Seven places (#destinations), the partner comparison (#partners), Moments we'd book again (#experiences),
#   the Roamer Journal (#guide) and the Trip Builder (#planner).
# live-home.mjs captures their markup, CSS, icons, data and photos; this module bundles the theme's own JS for them
# (tools/build.py's bundler) with three small adaptations for the prototype frame:
#   - photos come from img/ (the artifact cannot load images from other sites);
#   - "phone width" and scrolling follow the site frame (#vp), not the browser window;
#   - the heart is the prototype's one saved list (chrome2.py), so there is a single "Saved for later".
import json, os, re, sys, shutil, importlib.util

HERE = os.path.dirname(os.path.abspath(__file__))
REPO = r'C:\Users\morsy\Desktop\EgyptRoamer'
CAP = json.load(open(os.path.join(HERE, 'live', 'home-sections.json'), encoding='utf-8'))

def _patch_sources():
    src = os.path.join(HERE, 'livejs')
    shutil.rmtree(src, ignore_errors=True)
    shutil.copytree(os.path.join(REPO, 'wordpress', 'wp-content', 'themes', 'egypt-roamer', 'src', 'js'), src)
    def edit(rel, a, b):
        p = os.path.join(src, rel); s = open(p, encoding='utf-8').read()
        assert s.count(a) == 1, (rel, a[:50]); open(p, 'w', encoding='utf-8', newline='\n').write(s.replace(a, b))
    edit('data.js', 'export const img = (id, w = 1200, q = 75, ratio = 0) => {\n',
         'export const img = (id, w = 1200, q = 75, ratio = 0) => {\n'
         '  if (typeof id === "string" && id.startsWith("data:")) return id;\n'
         '  { const m = typeof id === "string" && id.match(/^(\\d{10,13}-[0-9a-f]{12})/); if (m) return `img/${m[1]}.jpg`; }\n')
    edit('data.js', 'export const srcset = (id, widths = [480, 800, 1200, 1800], ratio = 0, q = 75) => {\n',
         'export const srcset = (id, widths = [480, 800, 1200, 1800], ratio = 0, q = 75) => {\n  return "";\n')
    edit('utils.js', 'export const isMobile = () => window.matchMedia("(max-width: 900px)").matches;',
         'export const isMobile = () => (document.getElementById("vp")?.clientWidth || window.innerWidth) <= 900;')
    edit('utils.js', 'export function scrollToTarget(target, opts = {}) {\n',
         'export function scrollToTarget(target, opts = {}) {\n'
         '  const vp = document.getElementById("vp");\n'
         '  if (vp) { const y = typeof target === "number" ? target : target.getBoundingClientRect().top - vp.getBoundingClientRect().top + vp.scrollTop + (opts.offset ?? 0); vp.scrollTo({ top: y, behavior: reducedMotion() ? "auto" : "smooth" }); return; }\n')
    open(os.path.join(src, 'components', 'favorites.js'), 'w', encoding='utf-8', newline='\n').write(
        '/* Prototype: the heart uses the prototype\'s one saved list (chrome2.py). */\n'
        'import { img } from "../data.js";\n'
        'export const saveButton = (item) => (window.lcFavBtn ? window.lcFavBtn({ id: String(item.id), title: item.title, meta: item.meta || "", img: img(item.image, 400) }) : "");\n'
        'export function renderSaved() {}\n'
        'export function initFavorites() {}\n'
        'export const refreshSaveButtons = () => window.lcFavPaint && window.lcFavPaint();\n')
    open(os.path.join(src, 'proto-home.js'), 'w', encoding='utf-8', newline='\n').write(
        'import * as data from "./data.js";\n'
        'import { localizeData } from "./i18n.js";\n'
        'import { emit } from "./utils.js";\n'
        'import { initDestinations } from "./components/destinations.js";\n'
        'import { initPartners } from "./components/partners.js";\n'
        'import { initExperiences } from "./components/experiences.js";\n'
        'import { initGuide } from "./components/guide.js";\n'
        'import { initPlanner } from "./components/planner.js";\n'
        'window.erHome = {\n'
        '  emit,\n'
        '  init() {\n'
        '    try { localizeData(data); } catch (e) { console.error(e); }\n'
        '    for (const [n, f] of [["destinations", initDestinations], ["partners", initPartners], ["experiences", initExperiences], ["guide", initGuide], ["planner", initPlanner]]) {\n'
        '      try { f(); } catch (e) { console.error("[prototype] " + n, e); }\n'
        '    }\n'
        '  },\n'
        '};\n')
    return src

def bundle():
    src = _patch_sources()
    spec = importlib.util.spec_from_file_location('erbuild', os.path.join(REPO, 'tools', 'build.py'))
    B = importlib.util.module_from_spec(spec); spec.loader.exec_module(B)
    import pathlib
    return B.bundle(pathlib.Path(src), 'proto-home.js')

def css():
    out = []
    for wrap, sel, body in CAP['css']['rules']:
        sel = ','.join('.live ' + p.strip() for p in sel.split(','))
        body = re.sub(r'100[ds]?vh\b', '100%', body)
        body = re.sub(r'(\d)vw\b', r'\1cqw', body)
        rule = f'{sel}{{{body}}}'
        if wrap is None:
            out.append(rule)
        elif re.fullmatch(r'\((?:max|min)-width: ?\d+px\)', wrap):
            out.append(f'@container site {wrap.replace(" ", "")}{{{rule}}}')
        elif 'reduced-motion' in wrap or 'hover' in wrap or 'pointer' in wrap:
            out.append(f'@media {wrap}{{{rule}}}')
    out += CAP['css']['keyframes']
    return '\n/* ---------- live site: the five homepage sections the prototype had dropped ---------- */\n' + '\n'.join(out) + '''
.live [data-reveal]{opacity:1!important;transform:none!important}
.live .lcf{z-index:4}
.live .card .lcf,.live .offer .lcf,.live .dest-stage .lcf{position:absolute;top:12px;right:12px}
'''

def js():
    h = CAP['html']
    sections = {k.lstrip('#'): v for k, v in h.items() if k.startswith('#')}
    return ('\n// ---------- live site: the five homepage sections ----------\n'
            'window.ER_DATA=' + json.dumps(CAP['data']) + ';\n'
            'const HOME2=' + json.dumps(sections) + ';\n'
            'const HOME2_TOAST=' + json.dumps(h.get('toast', '')) + ';\n'
            + bundle() +
            r'''
function renderHome2(){
  const host=document.getElementById("live"); if(!host||host.querySelector("#destinations")) return;
  const fix=s=>s.replace(/href="(https:\/\/egyptroamer\.com[^"]*)"/g,'href="#" data-href="$1"');
  host.querySelector("#map").insertAdjacentHTML("beforebegin",fix(HOME2.destinations));
  host.querySelector("#map").insertAdjacentHTML("afterend",fix(HOME2.partners)+fix(HOME2.experiences)+fix(HOME2.guide)+fix(HOME2.planner));
  // the captured markup is production's rendered page: the parts the components add themselves are removed first
  host.querySelectorAll("#partners .ptab").forEach(b=>b.remove());
  window.erHome.init();
  // sections render more content later; nothing waits for a scroll reveal in the prototype
  new MutationObserver(()=>host.querySelectorAll("[data-reveal]:not(.is-in)").forEach(e=>e.classList.add("is-in"))).observe(host,{childList:true,subtree:true});
  host.querySelectorAll("[data-reveal]").forEach(e=>e.classList.add("is-in"));
}
// every link to the live site stays inside the prototype; partner links say where they go
document.addEventListener("click",e=>{
  const a=e.target.closest('#vp a[href^="https://egyptroamer.com"], #vp a[data-href]'); if(!a||e.defaultPrevented) return;
  const href=a.dataset.href||a.getAttribute("href"); e.preventDefault();
  if(/\/go\//.test(href)){ lcToast("Prototype: on the site this opens the partner's page in a new tab."); return; }
  window.lcRoute?lcRoute(href,a):lcToast("Prototype: on the site this opens "+href.replace("https://egyptroamer.com",""));
});
// "Build My Trip" in the moods section opens the Trip Builder with that mood
document.addEventListener("click",e=>{ const b=e.target.closest("#live [data-mood-build]"); if(!b) return; e.preventDefault(); e.stopPropagation();
  const cur=document.querySelector('#live #moods [aria-selected="true"]')?.dataset.mood; const p=document.querySelector("#live #planner"); if(!p) return;
  if(cur) window.erHome.emit("planner:mood",cur); const vp=document.getElementById("vp"); vp.scrollTo({top:vp.scrollTop+p.getBoundingClientRect().top-vp.getBoundingClientRect().top-70,behavior:"smooth"}); }, true);
''')

def apply(tpl):
    a = tpl.index('</style>')
    tpl = tpl[:a] + css() + tpl[a:]
    a = 'renderHome(); renderLive();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    tpl = tpl[:i] + js() + tpl[i:]
    return tpl.replace(a, 'renderHome(); renderLive(); renderHome2();')
