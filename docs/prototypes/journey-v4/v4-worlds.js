// ---------- Trip worlds: every word below is from the trip's page on egyptroamer.com (16 Oct) ----------
const WORLDS = {
  valley: {cls:"w-tomb", fx:"tomb", photo:"trip", url:"valley-of-the-kings-hatshepsut-temple",
    eyebrow:"Luxor · The West Bank · Ancient Thebes", title:"Valley of the Kings & Hatshepsut Temple",
    lead:"A guided visit to Luxor’s West Bank, the burial ground of ancient Thebes. You’ll go down into the painted royal tombs of the Valley of the Kings, visit the terraced temple of Hatshepsut beneath the cliffs, and usually stop at the Colossi of Memnon.",
    chips:["6 hours","3 options from 2 partners","Private option available","Pickup offered"], go:"Go down into the valley",
    jEyebrow:"The day, stop by stop", jTitle:"Down the corridor",
    steps:[
      {img:"kv62",t:"Valley of the Kings",p:"A standard ticket covers a set number of the tombs open that day. The most famous, such as Tutankhamun’s, need separate tickets. Inside, long corridors lead down to burial chambers covered in texts and images of the gods, some with their colours remarkably well preserved.",why:"Start early: the valley is a sun trap by late morning."},
      {img:"hat_l",t:"Hatshepsut’s temple",p:"Three great terraces built for the female pharaoh Hatshepsut, with reliefs of her divine birth and the expedition to the land of Punt.",why:"At Deir el-Bahari, beneath the cliffs."},
      {wall:true,t:"Colossi of Memnon",p:"Two giant seated statues of Amenhotep III, all that stands of his vast temple.",why:"It’s all part of the UNESCO site Ancient Thebes with its Necropolis."}],
    sec:{eyebrow:"Why a guide makes a difference",title:"The walls speak. A guide reads them.",ps:["The tombs are dense with symbols and texts that are hard to read without help. A guide explains what you’re seeing: the journey through the underworld, the gods, the king’s rebirth. They also choose the tombs worth visiting on the day."],
      tabs:[["𓇳","","Start early: the valley is a sun trap by late morning."],["𓈗","","Bring water, a hat and sun protection."],["𓂻","","Wear comfortable shoes for the descents."],["𓁹","","Photography rules inside the tombs change. Follow the guards."]],wall:true},
    papy:[["Who it suits","The essential West Bank day","Everyone visiting Luxor. For those with more time or a deeper interest, add Medinet Habu, the Valley of the Queens and the workers’ village of Deir el-Medina."],
          ["Combine it with","Karnak and Luxor Temple","Karnak and Luxor Temple on the East Bank, and a sunrise balloon flight over the West Bank. See our Luxor guide for a three-day plan.","karnak"]],
    sum:["Luxor","About 4–10 hours","Private option available","Pickup offered"],
    opts:[{n:"Private full day on Luxor West Bank",p:"Viator",f:["Private","About 7–10 hours","Pickup offered"],pick:true},
          {n:"Luxor West Bank full day with Medinet Habu and Deir el-Medina",p:"Viator",f:["About 6–8 hours","Pickup offered"]},
          {n:"West Bank Guided Tour: Valley of the Kings",p:"GetYourGuide",f:["Private","4 hours","Pickup offered"]}],
    cmp:[["Private","Yes","Not stated","Yes"],["Length","About 7–10 h","About 6–8 h","4 h"],["Pickup","Yes","Yes","Yes"]],
    stay:{title:"Staying in Luxor?",rows:[["Hotels in Luxor","Luxor"],["Hotels on the West Bank (Al Bairat)","West Bank, Luxor"]]}},

  redsea: {cls:"w-sea", fx:"sea", photo:"sea_l", url:"red-sea-diving-two-reef-dives",
    eyebrow:"Hurghada · Sharm El Sheikh · 1 day · 2 dives", title:"Red Sea Diving: Two Reef Dives",
    lead:"A day on a dive boat with two guided reef dives in the Red Sea, from Hurghada or Sharm El Sheikh. The Red Sea is known for clear water, colourful coral and abundant reef fish, often just a short boat ride from the coast.",
    quote:"Snorkellers can often join the same boat.", chips:["Pickup offered","3 options from 2 partners"], go:"Live the dive day",
    jEyebrow:"What a dive day looks like", jTitle:"From the dock to the reef",
    steps:[
      {t:"Check-in",p:"Check-in at the dive centre or on the boat: paperwork, equipment fitting and a briefing."},
      {img:"hurg_diver",t:"Sail to the first site",p:"The boat sails to the first site. The guide explains the dive plan, the depth and what you might see."},
      {t:"First dive",p:"First dive, then a surface interval on board with lunch or snacks."},
      {t:"Second dive",p:"Second dive at a different reef, then back to port in the afternoon."}],
    sec:{eyebrow:"Beginners and certified divers",title:"Dive at your level",ps:["Look for recognised training-agency affiliation and well-maintained equipment. Ask about group sizes per guide. Choose centres that use fixed moorings rather than anchoring on reefs, and that give a proper briefing."],
      tabs:[["𓈗","No certification","You can do a try-dive (introductory dive) with an instructor at shallow depth after a briefing. It’s a good way to find out whether diving is for you."],["𓆛","Certified divers","Guided dives at depths within your certification level. Bring your certification card and logbook."],["𓋹","Health","You’ll be asked to complete a medical questionnaire. Some conditions require a doctor’s sign-off. Don’t fly within the recommended interval after diving."]]},
    papy:[["Where to dive","From Hurghada","The Giftun Islands and nearby reefs."],["Where to dive","From Sharm El Sheikh","Ras Mohammed National Park and the Straits of Tiran; the Thistlegorm wreck for experienced divers."]],
    sum:["Hurghada","Pickup offered"],
    opts:[{n:"Two boat dives from Hurghada (certified divers)",p:"Viator",f:["About 1 day","Pickup offered"]},
          {n:"Two dives from Hurghada for beginners and certified divers",p:"Viator",f:["About 7 hours","Pickup offered"]},
          {n:"Hurghada: Scuba Diving for Certified Divers or Beginners",p:"GetYourGuide",f:["7 hours","Pickup offered"]}],
    cmp:[["Length","About 1 day","About 7 h","7 h"],["Pickup","Yes","Yes","Yes"]]},

  white: {cls:"w-desert", fx:"desert", photo:"desert_l", url:"white-desert-black-desert-safari",
    eyebrow:"Western Desert · from Bahariya Oasis · 2 days", title:"White Desert & Black Desert Safari",
    lead:"A 4×4 journey into the Western Desert, usually starting from Bahariya Oasis. The route crosses the Black Desert, with its dark volcanic hills, to the White Desert, where wind has carved chalk into giant white mushrooms and pillars.",
    quote:"Most trips include a night camping among the formations.", chips:["Private option available","Pickup offered","Best October to April"], go:"Cross the desert",
    jEyebrow:"What you see", jTitle:"Black to white",
    steps:[
      {t:"The Black Desert",p:"Sand dotted with cone-shaped hills capped in black volcanic stone.",tone:"dark"},
      {t:"Crystal Mountain",p:"A ridge of quartz crystals by the roadside.",tone:"warm"},
      {t:"Agabat",p:"Dramatic valleys and dunes between the two deserts.",tone:"warm"},
      {t:"The White Desert",p:"Chalk formations shaped by the wind, most striking at sunset and sunrise, and ghostly under a full moon. The area is protected as the White Desert Protected Area.",tone:"night"}],
    sec:{eyebrow:"A night in the desert",title:"A sky full of stars, far from any light",night:true,ps:["Camp is usually set up in the shelter of the formations: dinner cooked over a fire, blankets or a simple tent, and a sky full of stars far from any light. Nights in winter are cold, often much colder than visitors expect.","Ask your operator what bedding and equipment they provide, and whether the trip includes transfers from Cairo."],
      tabs:[["𓇳","","Warm layers, a hat and a torch for the night."],["𓇶","","Sun protection and sunglasses for the day."],["𓊡","","A scarf or buff against wind and sand."],["𓋹","","Wet wipes, basic medicine, and any snacks you rely on."]]},
    papy:[["Who it suits","Landscape and adventure","Travellers who want landscape and adventure, and don’t mind simple camping without facilities. It’s not a luxury experience, and the drive from Cairo is long."],
          ["How long and when","Two days, one night","A typical trip is two days and one night from Bahariya, or three days including the drive from Cairo. The best season is October to April, avoiding the extreme summer heat. Be ready for cold nights in mid-winter."]],
    sum:["Western Desert","Private option available","Pickup offered"],
    opts:[{n:"Cairo: 2-Day Bahariya & White Desert Private Camp Adventure",p:"GetYourGuide",f:["Private","2 days","Pickup offered"],pick:true,why:"The only private option"},
          {n:"Black & White Desert: 2 days from Cairo with camping",p:"Viator",f:["About 2 days","Pickup offered"]},
          {n:"Overnight White Desert camping from Cairo",p:"Viator",f:["About 2 days","Pickup offered"]}],
    cmp:[["Private","Yes","Not stated","Not stated"],["Length","2 days","About 2 days","About 2 days"],["Pickup","Yes","Yes","Yes"]]},

  nile: {cls:"w-nile", fx:"nile", photo:"nile_l", video:"video/cairo-nile-sunset.mp4", vcredit:"Video: Nada Ashraf, Pexels · Cairo, the Nile", url:"nile-dinner-cruise-with-live-show",
    eyebrow:"Cairo · An evening on the river · 3 hours", title:"Nile Dinner Cruise with Live Show",
    lead:"An evening on the Nile in Cairo aboard a restaurant boat. You get a dinner buffet or set menu, live music and usually a show, while the boat cruises past the lights of the city.",
    quote:"The mood is festive rather than intimate.", chips:["About 2–4 hours","Private option available","Pickup on some options"], go:"Spend the evening",
    jEyebrow:"What the evening is like", jTitle:"From the Corniche, after dark",
    steps:[
      {t:"Dinner",p:"Dinner, often a buffet of Egyptian and international dishes.",why:"Boats leave from docks along the Corniche and cruise the central stretch of the river."},
      {img:"cairo_nile",t:"Live music",p:"Live music, usually Egyptian and Arabic popular songs."},
      {t:"Tanoura",p:"A tanoura performance: the spinning dance with multi-coloured skirts, related to Sufi whirling."},
      {t:"Belly dance",p:"On many boats, a belly dance show.",why:"Expect other groups, families and celebrations on board."}],
    sec:{eyebrow:"Choosing a boat",title:"Four things to check",ps:["Travellers who want an easy, entertaining evening with a view, families, and groups. If you prefer quiet, a felucca ride at sunset, just the sail, the water and the city, may suit you better. Many visitors do both."],
      tabs:[["𓏊","Food and drink","Buffet or set menu, and whether drinks are included (alcohol is not served on every boat)."],["𓊛","The show programme","Tanoura, belly dance, or both."],["𓉐","Departure","Where the boat departs, and whether transfers from your hotel are included."],["𓇯","Seating","Tables by the windows or on an open deck."]]},
    papy:[["Combine it with","A day in old Cairo","A day in Islamic Cairo or Coptic Cairo, ending with dinner on the river. Our Cairo guide covers the areas and a three-day plan."]],
    sum:["Cairo","About 2–4 hours","Private option available","Pickup on some options"],
    opts:[{n:"Private Nile dinner cruise with belly dance and tanoura show",p:"Viator",f:["Private","About 4 hours","Pickup offered"],pick:true,why:"The only private option"},
          {n:"Nile dinner cruise with tanoura show",p:"Viator",f:["About 2 hours","Pickup offered"]},
          {n:"Cairo: Opal Nile Romantic Dinner Cruise & Live Entertainment",p:"GetYourGuide",f:["2–4 hours"]}],
    cmp:[["Private","Yes","Not stated","Not stated"],["Length","About 4 h","About 2 h","2–4 h"],["Pickup","Yes","Yes","Not stated"]]},
};
const PCHIP={"Viator":"pchip--viator","GetYourGuide":"pchip--gyg","Trip.com":"pchip--trip"};
const SIGNS=[..."𓄿𓇋𓂝𓅱𓃀𓊪𓆑𓅓𓈖𓂋𓉔𓎛𓐍𓄡𓊃𓋴𓈙𓈎𓎡𓎼𓏏𓍿𓂧𓆓𓋹𓂀𓇳𓆣𓁹𓃭𓊹𓋞𓇯𓊖𓉐𓆄𓅃𓀭𓁐𓂻"];
function wallHTML(cols){let seed=11;const r=()=>{seed=(seed*16807)%2147483647;return seed/2147483647;};let h="";for(let c=0;c<cols;c++){let s="";for(let i=0;i<26;i++)s+=SIGNS[Math.floor(r()*SIGNS.length)];h+=`<span class="col">${s}</span>`;}return `<div class="wall">${h}</div>`;}
const words=t=>esc(t).split(" ").map((w,i)=>`<span class="w" style="animation-delay:${200+i*110}ms">${w}</span>`).join(" ");

function renderWorld(id){
  const W=WORLDS[id]; const isTomb=W.fx==="tomb";
  const bg = W.video ? `<video autoplay muted loop playsinline poster="${IMG[W.photo]}" src="${W.video}"></video>` : `<img class="${isTomb?"dim":""}" src="${IMG[W.photo]}" alt="">`;
  const open = `<section class="wopen" id="wopen">${bg}
    ${isTomb?`<div class="wall--dim">${wallHTML(26)}</div><div class="lit" id="lit"><img src="${IMG[W.photo]}" alt="">${wallHTML(26)}</div>`:`<canvas data-fx="${W.fx}"></canvas>`}
    <div class="wshade"></div>${W.video?`<span class="vbadge">${esc(W.vcredit)}</span>`:""}
    ${isTomb?`<p class="hint">✦ Move your torch across the wall</p>`:""}
    <div class="wtxt"><p class="eyebrow">${esc(W.eyebrow)}</p><h1>${words(W.title)}</h1><p class="lead">${esc(W.lead)}</p>${W.quote?`<p class="wq">${esc(W.quote)}</p>`:""}
      <div class="wchips">${W.chips.map(c=>`<span>${esc(c)}</span>`).join("")}</div>
      <div class="wacts"><a class="btn btn--gold" href="#wchoose" data-wjump="wchoose">Book this trip</a><a class="btn btn--ghost-light" href="#wjourney" data-wjump="wjourney">${esc(W.go)} ↓</a></div></div></section>`;
  const layers = W.steps.map((s,i)=> s.img ? `<img class="${i?"":"on"}" data-j="${i}" src="${IMG[s.img]}" alt="">`
      : s.wall ? `<div class="jlayer ${i?"":"on"}" data-j="${i}" style="background:#0c0a07">${wallHTML(30).replace('class="wall"','class="wall" style="opacity:.25"')}</div>`
      : `<div class="jlayer ${i?"":"on"}" data-j="${i}"><img src="${IMG[W.photo]}" alt="" style="width:100%;height:100%;object-fit:cover;filter:${s.tone==="night"?"brightness(.45) saturate(.6) hue-rotate(-15deg)":s.tone==="dark"?"brightness(.5) contrast(1.1) saturate(.6)":"brightness(.75) saturate(1.1)"};transform:scale(${1+i*.12}) translate(${i*-2}%,${i*1.5}%)"></div>`).join("");
  const journey = `<section class="journey2" id="wjourney"><div class="jstage">${layers}${W.fx!=="tomb"?`<canvas data-fx="${W.fx}-j"></canvas>`:""}
      <div class="jhead"><p class="eyebrow">${esc(W.jEyebrow)}</p><h2>${esc(W.jTitle)}</h2></div>
      <div class="path">${W.steps.map((s,i)=>`<span class="${i?"":"on"}">${esc(s.t)}</span>`).join("")}</div></div>
    <div class="jsteps">${W.steps.map((s,i)=>`<div class="jstep" data-js="${i}"><article><span class="n">${String(i+1).padStart(2,"0")}</span><h3>${esc(s.t)}</h3><p>${esc(s.p)}</p>${s.why?`<p class="why">${esc(s.why)}</p>`:""}</article></div>`).join("")}</div></section>`;
  const S=W.sec;
  const sec = `<section class="wsec ${S.night?"wsec--night":""}">${S.night?`<canvas data-fx="stars"></canvas>`:""}${S.wall||isTomb?wallHTML(34):""}<div class="wsec__in">
      <div><p class="eyebrow">${esc(S.eyebrow)}</p><h2>${esc(S.title)}</h2>${S.ps.map(p=>`<p>${esc(p)}</p>`).join("")}</div>
      <div class="tablets">${S.tabs.map(([g,b,t])=>`<div class="tablet"><i>${g}</i><span>${b?`<b>${esc(b)}</b>`:""}${esc(t)}</span></div>`).join("")}</div></div></section>`;
  const papy = `<section class="papy"><div class="papy__in">${W.papy.map(([e,h,p,v])=>`<div><p class="eyebrow">${esc(e)}</p><h2>${esc(h)}</h2><p>${esc(p)}</p>${v&&v.startsWith("img:")?`<div class="wvid"><img src="${IMG[v.slice(4).split("|")[0]]}" alt="" style="width:100%;height:100%;object-fit:cover;display:block"><small>${esc(v.split("|")[1]||"")}</small></div>`:v?`<div class="wvid"><video autoplay muted loop playsinline src="video/karnak-columns.mp4" poster="${IMG.valley}"></video><small>Karnak Temple, Luxor · Video: BOB DK, Pexels</small></div>`:""}</div>`).join("")}</div></section>`;
  const opts = W.opts.map(o=>`<div class="wopt ${o.pick?"wopt--pick":""}"><div>${o.pick?`<span class="pk">✓ Our pick${o.why?`<span class="why">${esc(o.why)}</span>`:""}</span>`:""}<h3>${esc(o.n)}</h3><div class="facts"><span class="pchip ${PCHIP[o.p]}">${esc(o.p)}</span>${o.f.map(f=>`<span>${esc(f)}</span>`).join("")}</div></div>
      <button type="button" class="btn ${o.pick||!W.opts.some(x=>x.pick)&&o===W.opts[0]?"btn--gold":"btn--line"} btn--sm" data-wbook="${esc(o.p)}|${esc(o.n)}">Book ↗</button></div>`).join("");
  const cmp = `<button type="button" class="wcmp" id="wcmp" aria-expanded="false">Compare side by side</button><table class="wtbl" id="wtbl" hidden><thead><tr><th></th>${W.opts.map(o=>`<th>${esc(o.p)}</th>`).join("")}</tr></thead><tbody>${W.cmp.map(([k,...v])=>`<tr><td>${k}</td>${v.map(x=>`<td class="${x==="Yes"?"yes":x==="Not stated"?"no":""}">${esc(x)}</td>`).join("")}</tr>`).join("")}</tbody></table>`;
  const stay = W.stay?`<div class="wstay"><h3>${esc(W.stay.title)}</h3>${W.stay.rows.map(([n,l])=>`<div class="wopt"><div><h3 style="font-size:17px">${esc(n)}</h3><div class="facts"><span class="pchip pchip--trip">Trip.com</span><span>${esc(l)}</span></div></div><button type="button" class="btn btn--line btn--sm" data-wbook="Trip.com|${esc(n)}">See hotels ↗</button></div>`).join("")}</div>`:"";
  const choose = `<section class="choose2" id="wchoose"><div class="choose2__grid"><div class="choose2__photo"><img src="${IMG[W.photo]}" alt=""><span class="choose2__cap">${esc(W.title)}</span></div>
      <div><div class="wdone" id="wdone" hidden><b id="wdone-b"></b><p id="wdone-p"></p><p>Choose your date and see the price there. Egypt Roamer never takes bookings or payments.</p></div>
        <p class="eyebrow">Book this trip</p><h2>Choose your way in</h2><div class="wsum">${W.sum.map(s=>`<span>${esc(s)}</span>`).join("")}</div>
        ${cmp}${opts}${stay}<p class="wout">Each button opens the partner’s site in a new tab. Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you. It never influences what we recommend. <a href="#">How we work with partners</a></p></div></div></section>`;
  const capsule = `<a class="capsule" href="#wchoose" data-wjump="wchoose" id="wcap"><span><b>${esc(W.title.split(":")[0].split(" & ")[0])}</b><br><small style="opacity:.75">${W.opts.length} options</small></span><span class="btn btn--gold btn--sm">Book this trip</span></a>`;
  const host=document.getElementById("world");
  host.className="world "+W.cls; host.innerHTML=open+journey+sec+papy+choose+capsule+`<div class="wfoot"></div>`;
  wireWorld(W);
}
let stopFx=[];
function wireWorld(W){
  stopFx.forEach(f=>f()); stopFx=[];
  const host=document.getElementById("world");
  // the descent / journey: the step in view lights its mark and its picture
  const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){const i=e.target.dataset.js;host.querySelectorAll("[data-j]").forEach(x=>x.classList.toggle("on",x.dataset.j===i));host.querySelectorAll(".path span").forEach((x,k)=>x.classList.toggle("on",String(k)===i));}}),{root:vp,threshold:.55});
  host.querySelectorAll(".jstep").forEach(s=>io.observe(s)); stopFx.push(()=>io.disconnect());
  const cap=host.querySelector("#wcap"); const io2=new IntersectionObserver(([e])=>cap.style.visibility=e.isIntersecting?"hidden":"visible",{root:vp,threshold:.2}); io2.observe(host.querySelector("#wchoose")); stopFx.push(()=>io2.disconnect());
  host.querySelector("#wcmp").addEventListener("click",e=>{const t=host.querySelector("#wtbl");t.hidden=!t.hidden;e.target.setAttribute("aria-expanded",String(!t.hidden));e.target.textContent=t.hidden?"Compare side by side":"Hide comparison";});
  if(W.fx==="tomb") torch();
  host.querySelectorAll("canvas[data-fx]").forEach(c=>stopFx.push(fx(c,c.dataset.fx)));
}
function torch(){
  const open=document.getElementById("wopen"), lit=document.getElementById("lit"); let idle=true, run=true; const t0=performance.now();
  const set=(x,y)=>{lit.style.setProperty("--x",x+"px");lit.style.setProperty("--y",y+"px");};
  open.addEventListener("pointermove",e=>{idle=false;const r=open.getBoundingClientRect();set(e.clientX-r.left,e.clientY-r.top);});
  open.addEventListener("pointerleave",()=>idle=true);
  (function drift(t){if(!run)return;if(idle){const r=open.getBoundingClientRect();const k=(t-t0)/4200;set(r.width*(.64+.15*Math.sin(k)),r.height*(.4+.13*Math.sin(k*1.7)));} if(!REDUCE) requestAnimationFrame(drift);})(t0);
  stopFx.push(()=>run=false);
}
const REDUCE=matchMedia("(prefers-reduced-motion: reduce)").matches;
function fx(c,kind){
  if(REDUCE) return ()=>{};
  const ctx=c.getContext("2d"); let W=0,H=0,run=true; const dpr=devicePixelRatio||1;
  const base=kind.replace("-j",""); const n=base==="sea"?70:base==="desert"?240:base==="stars"?220:90;
  const P=Array.from({length:n},()=>({x:Math.random(),y:Math.random(),r:Math.random(),v:.2+Math.random()*.8,p:Math.random()*6.28}));
  (function frame(t){ if(!run) return; if(c.clientWidth*dpr!==W||c.clientHeight*dpr!==H){W=c.width=c.clientWidth*dpr;H=c.height=c.clientHeight*dpr;} ctx.clearRect(0,0,W,H);
    if(base==="sea"){ for(let i=0;i<6;i++){const g=ctx.createLinearGradient(0,0,0,H*.75);const x=(Math.sin(t/2600+i*1.3)*.5+.5)*W;g.addColorStop(0,"rgba(170,240,255,.15)");g.addColorStop(1,"rgba(170,240,255,0)");ctx.fillStyle=g;ctx.beginPath();ctx.moveTo(x-40*dpr,0);ctx.lineTo(x+40*dpr,0);ctx.lineTo(x+160*dpr*Math.sin(t/3000+i),H*.75);ctx.lineTo(x+60*dpr*Math.sin(t/3000+i),H*.75);ctx.fill();}
      P.forEach(b=>{b.y-=b.v*.0016;if(b.y<-.05){b.y=1.05;b.x=Math.random();}ctx.strokeStyle="rgba(220,250,255,.5)";ctx.lineWidth=dpr;ctx.beginPath();ctx.arc((b.x+Math.sin(t/900+b.p)*.004)*W,b.y*H,(1+b.r*3.5)*dpr,0,6.28);ctx.stroke();}); }
    else if(base==="desert"||base==="stars"){ const night=base==="stars"?1:(Math.sin(t/14000*Math.PI-Math.PI/2)*.5+.5);
      P.forEach((s,i)=>{ if(base==="stars"||i<160){const a=night*(.35+.65*Math.abs(Math.sin(t/700+s.p)));ctx.fillStyle=`rgba(255,250,235,${a*.9})`;const z=(s.r*1.6+.5)*dpr;ctx.fillRect(s.x*W,s.y*H*(base==="stars"?1:.55),z,z);}
        else {s.x+=s.v*.0012;if(s.x>1.05){s.x=-.05;s.y=Math.random();}ctx.fillStyle="rgba(240,215,170,.35)";ctx.fillRect(s.x*W,(.6+s.y*.4)*H,(1+s.r*2)*dpr,dpr);} }); }
    else { P.forEach(l=>{const x=l.x*W,y=(.62+l.y*.38)*H,w=(10+l.r*50)*dpr*(.7+.3*Math.sin(t/400+l.p));ctx.fillStyle=`rgba(${l.r>.5?"255,190,110":"255,235,190"},${.12+.25*Math.abs(Math.sin(t/600+l.p))})`;ctx.fillRect(x-w/2,y,w,1.6*dpr);}); }
    requestAnimationFrame(frame);})(performance.now());
  return ()=>run=false;
}
