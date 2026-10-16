let step=1, current="valley";
function go(n,opts={}){
  step=n;
  $$("[data-screen]").forEach(s=>s.hidden=!(s.dataset.screen==n||(n==4&&s.dataset.screen=="3")));
  $$(".steps button").forEach(b=>b.removeAttribute("aria-current"));
  $(`.steps button[data-go="${n}"]`)?.setAttribute("aria-current","step");
  $$("[data-note]").forEach(x=>x.hidden=x.dataset.note!=n);
  $("#url").textContent=n>=3?`egyptroamer.com/experiences/${WORLDS[current].url}/`:["egyptroamer.com","egyptroamer.com/experiences/"][n-1];
  $("#hdr-cta").innerHTML=n>=3?'Book this trip <span class="arr">↓</span>':'Choose a trip <span class="arr">→</span>';
  $("#hdr-cta").dataset.go=n>=3?"":"2"; $("#hdr-cta").dataset.wjump=n>=3?"wchoose":"";
  $("#nav-exp").toggleAttribute("aria-current",n>=2);
  if(n===2) renderTrips();
  if(n===3) renderWorld(current);
  if(n===4){ if(!document.getElementById("wchoose")) renderWorld(current); const o=WORLDS[current].opts.find(x=>x.pick)||WORLDS[current].opts[0]; book(o.p+"|"+o.n,true); return; }
  if(n<3){ stopFx.forEach(f=>f()); stopFx=[]; }
  vp.scrollTo({top:0,behavior:"auto"});
  $("#prev").disabled=n===1; $("#nextb").textContent="التالي";
}
function book(spec,silent){
  const [partner,name]=spec.split("|");
  $("#wdone").hidden=false; $("#wdone-b").textContent=`${partner} opened in a new tab`; $("#wdone-p").textContent=`“${name}”`;
  if(!silent) toast(`Prototype: here ${partner} would open in a new tab`);
  step=4; $$(".steps button").forEach(b=>b.removeAttribute("aria-current")); $('.steps button[data-go="4"]').setAttribute("aria-current","step");
  $$("[data-note]").forEach(x=>x.hidden=x.dataset.note!="4"); $("#nextb").textContent="من الأول";
  vp.scrollTo({top:Math.max(0,$("#wchoose").offsetTop-20),behavior:"auto"});
}
let tt; function toast(m){const t=$("#toast");t.textContent=m;t.hidden=false;t.style.top=(vp.scrollTop+80)+"px";clearTimeout(tt);tt=setTimeout(()=>t.hidden=true,2800);}
document.addEventListener("click",e=>{
  const wj=e.target.closest("[data-wjump]"); if(wj&&wj.dataset.wjump){e.preventDefault();const t=document.getElementById(wj.dataset.wjump);if(t)vp.scrollTo({top:t.offsetTop-10});return;}
  const g=e.target.closest("[data-go]"); if(g&&g.dataset.go){e.preventDefault();go(+g.dataset.go);return;}
  const p=e.target.closest("[data-place]"); if(p){e.preventDefault();state.place=p.dataset.place;go(2);return;}
  const m=e.target.closest("[data-mood]"); if(m){mood(+m.dataset.mood);return;}
  const fp=e.target.closest("[data-fplace]"); if(fp){state.place=fp.dataset.fplace;renderTrips();return;}
  const fl=e.target.closest("[data-flen]"); if(fl){state.len=fl.dataset.flen;renderTrips();return;}
  const c=e.target.closest("[data-trip]"); if(c){ if(WORLDS[c.dataset.trip]){current=c.dataset.trip;go(3);} else {toast("Prototype: four trips open in full: Valley of the Kings, Red Sea diving, White Desert, Nile dinner cruise.");} return;}
  const b=e.target.closest("[data-wbook]"); if(b){book(b.dataset.wbook);return;}
  const a=e.target.closest('a[href^="#"]'); if(a&&a.getAttribute("href").length>1){const t=document.getElementById(a.getAttribute("href").slice(1)); if(t&&vp.contains(t)){e.preventDefault();vp.scrollTo({top:Math.max(0,t.offsetTop-68)});}}
});
$("#f-private").addEventListener("change",e=>{state.priv=e.target.checked;renderTrips();});
$("#f-pickup").addEventListener("change",e=>{state.pickup=e.target.checked;renderTrips();});
$("#show-notes").addEventListener("change",e=>$("#stage").classList.toggle("no-notes",!e.target.checked));
$("#prev").addEventListener("click",()=>go(Math.max(1,step-1)));
$("#nextb").addEventListener("click",()=>{ if(step===4){go(1);return;} if(step===3){const o=WORLDS[current].opts.find(x=>x.pick)||WORLDS[current].opts[0];book(o.p+"|"+o.n);return;} if(step===1){state.place="all";} go(step+1); });
$$("[data-img]").forEach(i=>i.src=IMG[i.dataset.img]);
