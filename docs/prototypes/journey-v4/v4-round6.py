# Owner, 17 Oct:
# 1. "The filter has 6 places but the site has 7" and "Sharm has two trips, you show one": places now follow
#    production exactly: Cairo, Alexandria, Siwa, Luxor, Aswan, Hurghada, Sharm El Sheikh, and each trip belongs to
#    the places production links it to (the dive day to Hurghada and Sharm, the White Desert safari to Siwa).
# 2. "The footer has links with no pages": every footer link now opens its page in the prototype. The legal and
#    contact pages show production's own text (live-pages.mjs). Trip Builder opens the Trip Builder section, and
#    Best Time to Visit opens that guide (v4-guides.py).
# 3. "The Cairo food trip's photo is not Egyptian food, and its page has no photos": exact-tagged Cairo photos
#    (free Unsplash, location "Cairo, Egypt") for the trip, two of its stops and "Combine it with". The same
#    non-Egyptian photo is used on production (food trip and "Food" mood), so production needs this change too.
import json, os, re
HERE = os.path.dirname(os.path.abspath(__file__))
PAGES = json.load(open(os.path.join(HERE, 'live', 'pages.json'), encoding='utf-8'))

FOOD_MAIN = 'img/1762631934548-7b83d4bdcaa1.jpg'      # joe boshra: feteer meshaltet with white cheese and salad, Cairo
FOOD_SELLER = 'img/1738245868177-8c9977608031.jpg'    # Omar Abozeid: a bread seller in a Cairo street
FOOD_BREAD = 'img/1521791697570-e1f13d0b81d0.jpg'     # Alex Block: baladi bread, Cairo
FOOD_SWEETS = 'img/1590657140810-25898a847305.jpg'    # mohmed Afify: Egyptian biscuits and sweets, Cairo
FOOD_STREET = 'img/1652022262085-d454346a353e.jpg'    # Ahmed Assem: a Cairo street
OLD_FOOD = '1661994215679-cde7c2c5c060'

GROUPS = '''const GROUPS = [
  ["Cairo","d_cairo","video/giza-aerial.mp4|Giza and Cairo · Video: Blanka OG, Pexels","The city of a thousand minarets","Best time Oct – Apr","Cairo guide"],
  ["Alexandria","d_alexandria","video/alexandria-qaitbay.mp4|Qaitbay Citadel, Alexandria · Video: The Instagrapher, Pexels","The pearl of the Mediterranean","Best time Apr – Oct","Alexandria guide"],
  ["Siwa","d_siwa","","An oasis at the edge of the map","Best time Oct – Apr","Siwa guide"],
  ["Luxor","d_luxor","video/karnak-columns.mp4|Karnak Temple, Luxor · Video: BOB DK, Pexels","The world's greatest open-air museum","Best time Oct – Mar","Luxor guide"],
  ["Aswan","d_aswan","","Egypt at its most serene","Best time Oct – Mar","Aswan guide"],
  ["Hurghada","d_hurghada","video/redsea-coral.mp4|Red Sea · Video: Adrien JACTA, Pexels","Reefs, islands and endless sun","Best time year-round","Hurghada guide"],
  ["Sharm El Sheikh","d_sharm","","Where the desert meets the reef","Best time year-round","Sharm El Sheikh guide"],
];
// which places each trip belongs to, as on egyptroamer.com/destinations/<place>/
const TRIP_DESTS={giza:["cairo"],nile:["cairo"],food:["cairo"],alex:["alexandria"],siwa:["siwa"],white:["siwa"],valley:["luxor"],abu:["aswan"],redsea:["hurghada","sharm"],ras:["sharm"]};
// older links name a region; each stands for its places
const PALIAS={"Cairo & Giza":["Cairo"],"Red Sea":["Hurghada","Sharm El Sheikh"],"Western Desert":["Siwa"]};
const inPlace=(t,p)=>(PALIAS[p]||[p]).some(x=>(t.places||[]).includes(x));'''

CSS = '''
.wcred{position:absolute;right:16px;bottom:12px;z-index:3;font-size:11.5px;color:rgba(255,255,255,.8);text-decoration:none;background:rgba(0,0,0,.4);padding:3px 9px;border-radius:999px}
.wcred:hover{color:#fff}
.jstep .vcred{display:block!important;margin-top:12px!important;font-size:11.5px!important;color:#b8b2a6!important}
.wcred{right:auto!important;left:56px;bottom:auto!important;top:84px}
@container site (max-width:600px){.wcred{left:18px;top:68px}}
/* ---------- footer pages (production text) ---------- */
.info-hero{background:#101820;color:#fff;padding:120px 0 40px}
.info-hero h1{font-size:48px;margin:6px 0 10px}
.info-hero p{max-width:640px;margin:0;opacity:.85;font-size:16px}
.info-body{max-width:760px;margin:0 auto;padding:40px 40px 80px;font-size:16.5px;line-height:1.75;color:var(--ink,#121c26)}
.info-body h2{font:600 26px/1.25 var(--serif);margin:34px 0 10px}
.info-body h3{font:600 20px/1.3 var(--serif);margin:24px 0 8px}
.info-body a{color:var(--nile,#0e3a5b);text-decoration:underline;text-decoration-color:rgba(201,162,39,.7);text-underline-offset:3px}
.info-body ul{padding-left:20px}
.info-body input,.info-body textarea,.info-body select{width:100%;padding:12px 14px;border:1px solid var(--line-strong,rgba(16,24,32,.22));border-radius:10px;font:inherit;background:#fff;margin:6px 0 14px}
.info-body label{display:block;font-weight:600;font-size:14.5px}
.info-body button,.info-body [type=submit]{background:var(--gold,#c9a227);color:#101820;border:0;border-radius:999px;padding:12px 22px;font-weight:600;cursor:pointer;min-height:44px}
@container site (max-width:600px){ .info-hero{padding:96px 0 28px} .info-hero h1{font-size:34px} .info-body{padding:28px 18px 60px} }
'''

def apply(tpl):
    # 1 · places
    a = tpl.index('const GROUPS = ['); e = tpl.index('];', a) + 2
    tpl = tpl[:a] + GROUPS + tpl[e:]
    reps = [
        ('(state.place==="all"||t.place===state.place)', '(state.place==="all"||inPlace(t,state.place))'),
        ('const list=TRIPS.filter(t=>t.place===p&&ok(t));total+=list.length;', 'const list=(state.place==="all"||(PALIAS[state.place]||[state.place]).includes(p))?TRIPS.filter(t=>(t.places||[]).includes(p)&&ok(t)):[];list.forEach(t=>seen.add(t.id));'),
        ('  let total=0;', '  let total=0; const seen=new Set();'),
        ('$("#f-count").textContent=`${total} ${total===1?"trip":"trips"}`;', 'total=seen.size; $("#f-count").textContent=`${total} ${total===1?"trip":"trips"}`;'),
        ('data-fplace="${esc(v)}" aria-pressed="${state.place===v}"', 'data-fplace="${esc(v)}" aria-pressed="${state.place===v||(PALIAS[state.place]||[]).includes(v)}"'),
        ('const others = TRIPS.filter(t=>t.place===W.place&&WORLDS[t.id]!==W);', 'const wid = Object.keys(WORLDS).find(k=>WORLDS[k]===W); const others = TRIPS.filter(t=>WORLDS[t.id]!==W&&(TRIP_DESTS[t.id]||[]).some(d=>(TRIP_DESTS[wid]||[]).includes(d)));'),
        ('const here=id=>TRIPS.filter(t=>t.dest===id);', 'const here=id=>TRIPS.filter(t=>(TRIP_DESTS[t.id]||[]).includes(id));'),
        ('data-place="${esc(d.place)}">See all', 'data-place="${esc(d.name)}">See all'),
    ]
    for x, y in reps:
        assert tpl.count(x) == 1, x[:60]
        tpl = tpl.replace(x, y)
    m = re.search(r'const LPLACE=\{[^}]*\};', tpl); assert m
    tpl = tpl[:m.start()] + 'const LPLACE={"cairo":"Cairo","alexandria":"Alexandria","siwa":"Siwa","luxor":"Luxor","aswan":"Aswan","hurghada":"Hurghada","sharm":"Sharm El Sheikh"};' + tpl[m.end():]
    m = re.search(r'<div class="finder2" role="group" aria-label="Where in Egypt\?"><span>Where in Egypt\?</span>.*?</div>', tpl); assert m
    chips = ''.join(f'<a href="#" class="chip2" data-place="{p}">{p}</a>' for p in ["Cairo", "Alexandria", "Siwa", "Luxor", "Aswan", "Hurghada", "Sharm El Sheikh"])
    tpl = tpl[:m.start()] + f'<div class="finder2" role="group" aria-label="Where in Egypt?"><span>Where in Egypt?</span>{chips}</div>' + tpl[m.end():]

    # 3 · food photos (trip, stops, combine-with; production's food photo also in the live sections)
    reps = [
        ('{t:"Koshari",p:', '{img:"c_koshary",credit:"Photo: Dina Said, Wikimedia Commons, CC BY-SA 4.0",t:"Koshari",p:'),
        ('{t:"Ful medames and ta’meya",p:', '{img:"c_ful",credit:"Photo: Marsupium, Wikimedia Commons, CC BY-SA 4.0",t:"Ful medames and ta’meya",p:'),
        ('{t:"Hawawshi",p:', '{img:"c_hawawshi",credit:"Photo: Mohamed Moheyeldin, Wikimedia Commons, CC BY-SA 4.0",t:"Hawawshi",p:'),
        ('{t:"Sweets and drinks",p:', '{img:"f_sweets",credit:"Photo: mohmed Afify, Unsplash",t:"Sweets and drinks",p:'),
        ('{t:"Feteer meshaltet",p:', '{img:"c_feteer",credit:"Photo: Mohamed Ouda, Wikimedia Commons, CC BY-SA 4.0",t:"Feteer meshaltet",p:'),
        ('["Combine it with","Islamic Cairo or Downtown","A day in Islamic Cairo or Downtown. See our Cairo guide for the areas and more on Cairo food."]',
         '["Combine it with","Islamic Cairo or Downtown","A day in Islamic Cairo or Downtown. See our Cairo guide for the areas and more on Cairo food.","img:f_street|A Cairo street · Photo: Ahmed Assem, Unsplash"]'),
    ]
    for x, y in reps:
        assert tpl.count(x) == 1, x[:60]
        tpl = tpl.replace(x, y)
    tpl = tpl.replace(f'"image":"{OLD_FOOD}"', '"image":"1762631934548-7b83d4bdcaa1"')

    # 2 · footer pages
    a = '      <!-- 3 & 4 · the trip, in its own world -->'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, '      <section data-screen="info" hidden><div id="info"></div></section>\n\n' + a)
    a = tpl.index('</style>'); tpl = tpl[:a] + CSS + tpl[a:]

    js = r'''
// ---------- places, food photos, footer pages (owner, 17 Oct) ----------
TRIPS.forEach(t=>{ t.places=(TRIP_DESTS[t.id]||[]).map(d=>DESTS[d].name); });
Object.assign(IMG,{food:"''' + FOOD_MAIN + '''",food_l:"img/c_koshary.jpg",c_koshary:"img/c_koshary.jpg",c_ful:"img/c_ful.jpg",c_hawawshi:"img/c_hawawshi.jpg",c_feteer:"img/c_feteer.jpg",f_bread:"''' + FOOD_BREAD + '''",f_feteer:"''' + FOOD_MAIN + '''",f_seller:"''' + FOOD_SELLER + '''",f_sweets:"''' + FOOD_SWEETS + '''",f_street:"''' + FOOD_STREET + r'''"});
const INFO=''' + json.dumps(PAGES) + r''';
const INFO_URL={affiliate:"/affiliate-disclosure/",contact:"/contact/",privacy:"/privacy-policy/",cookies:"/cookies/"};
function showInfo(k){ const p=INFO[k]; if(!p) return;
  $$("[data-screen]").forEach(x=>x.hidden=x.dataset.screen!=="info"); $$(".steps button").forEach(b=>b.removeAttribute("aria-current"));
  $("#url").textContent="egyptroamer.com"+INFO_URL[k];
  $("#info").innerHTML=`<div class="info-hero"><div class="wrap"><h1>${esc(p.title)}</h1>${p.lead?`<p>${esc(p.lead)}</p>`:""}</div></div><div class="info-body">${p.html.replace(/href="(https:\/\/egyptroamer\.com[^"]*)"/g,'href="#" data-href="$1"')}</div>`;
  $("#info").querySelectorAll("form").forEach(f=>f.addEventListener("submit",e=>{ e.preventDefault(); lcToast("Prototype: nothing is sent. On the site this sends your message to our team."); }));
  vp.scrollTo({top:0,behavior:"auto"}); window.lcPaint&&lcPaint(); }
// the food trip's opener photo is a Commons photo: its credit sits on the photo
const _rw=renderWorld; renderWorld=function(id){ _rw(id); if(id==="food"){ const o=document.getElementById("wopen"); if(o&&!o.querySelector(".wcred")) o.insertAdjacentHTML("beforeend",'<a class="wcred" href="https://commons.wikimedia.org/wiki/File:Egyptian_food_Koshary.jpg" target="_blank" rel="noopener">Koshari · Photo: Dina Said, Wikimedia Commons, CC BY-SA 4.0</a>'); } };
function toPlanner(){ go(1); setTimeout(()=>{ const p=document.querySelector("#live #planner"); if(p) vp.scrollTo({top:vp.scrollTop+p.getBoundingClientRect().top-vp.getBoundingClientRect().top-70,behavior:"auto"}); },60); }
window.lcPlace=pl=>{ state.place=pl; go(2); };
const _route=window.lcRoute;
window.lcRoute=(href,a)=>{
  const p=href.replace("https://egyptroamer.com","").replace(/[?#].*$/,m=>m.startsWith("#planner")?"#planner":"");
  if(/#planner$/.test(p)){ toPlanner(); return; }
  for(const [k,u] of Object.entries(INFO_URL)) if(p===u){ showInfo(k); return; }
  const g=p.match(/^\/guides\/(?:([^/]+)\/)?$/); if(g&&window.openGuides){ g[1]?openGuide(g[1]):openGuides(); return; }
  _route(href,a);
};
// production's food photo (not Egyptian food) is replaced wherever the live sections show it
(function(){ const host=document.getElementById("live"); if(!host) return; const layer=host.querySelector('.moods__layer[data-layer="food"]'); const old=layer&&layer.getAttribute("src"); if(!old) return;
  const swap=()=>host.querySelectorAll("img").forEach(i=>{ if(i.getAttribute("src")===old) i.setAttribute("src","''' + FOOD_MAIN + r'''"); });
  new MutationObserver(swap).observe(host,{childList:true,subtree:true}); swap(); })();
'''
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, js + '\n' + a.replace('renderHome2();', 'renderHome2(); window.dispatchEvent(new Event("er-live-ready"));'))
    # the food-photo swap needs the live sections rendered first
    tpl = tpl.replace("(function(){ const host=document.getElementById(\"live\"); if(!host) return; const layer=", "window.addEventListener(\"er-live-ready\",function(){ const host=document.getElementById(\"live\"); if(!host) return; const layer=", 1)
    tpl = tpl.replace("new MutationObserver(swap).observe(host,{childList:true,subtree:true}); swap(); })();", "new MutationObserver(swap).observe(host,{childList:true,subtree:true}); swap(); });", 1)
    return tpl
