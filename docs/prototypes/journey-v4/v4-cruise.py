# Nile cruise, Aswan to Luxor (owner, 17 Oct: "add the three as one trip in the prototype first").
# Why: the Nile chapter and the map show the classic Aswan–Luxor route, but nothing on it could be booked.
# Sources, nothing else:
#  - text: our published guide "Nile Cruise or Hotels? Luxor to Aswan Explained" (approved editorial);
#  - facts per option: each partner's product page, read 17 Oct (docs/NILE-CRUISE-OFFERS-2026-10-17.md):
#    Viator 29896P3, Viator 57870P4, GetYourGuide 521042. No prices, ratings or reviews;
#  - photos: approved Unsplash photos already used in the guides (cruise ship, Aswan, Kuan L; Philae, Martijn Vonk;
#    Edfu, mana5280; Luxor Temple, Artem Bryzgalov; a felucca, Luxor, Anthony Chen).
JS = r'''
// ---------- Nile cruise, Aswan to Luxor, 17 Oct ----------
Object.assign(IMG,{cruise_l:"img/1778402153163-fc9a5fd0131a.jpg",cruise_philae:"img/1738580426867-03fa8c8b5288.jpg",cruise_edfu:"img/1660705630675-d66d60fdca46.jpg",cruise_luxor:"img/1610331800877-462a21600420.jpg",cruise_felucca:"img/1770251693846-4167f4c049c6.jpg"});
TRIPS.splice(TRIPS.findIndex(t=>t.id==="valley"),0,{id:"cruise",k:"cruise_l",world:true,t:"Nile Cruise from Aswan to Luxor",loc:"Aswan to Luxor",place:"Aswan",dest:"aswan",time:"4 days",len:"multi",facts:["Private option available","Pickup offered"],n:3,priv:true,pick:true,places:["Aswan","Luxor"]});
TRIP_DESTS.cruise=["aswan","luxor"];
T.cruise=TRIPS.find(t=>t.id==="cruise");
WORLDS.cruise={cls:"w-nile", fx:"nile", photo:"cruise_l", url:"nile-cruise-aswan-to-luxor", place:"Aswan",
  eyebrow:"Aswan to Luxor · On the Nile · 4 days", title:"Nile Cruise from Aswan to Luxor",
  lead:"Between Aswan and Luxor, the Nile passes some of the best-preserved temples in Egypt. On a cruise the boat is your hotel and moves between the sites: your cabin, meals and the visits are arranged together, and a guide takes you to the temples at each stop.",
  quote:"Unpacking once, and seeing the river itself.", chips:["4 days","3 options from 2 partners","Private option available","Pickup offered"], go:"Sail the classic route",
  jEyebrow:"On the way", jTitle:"Aswan to Luxor",
  steps:[
    {img:"cruise_philae",t:"Aswan",p:"With your guide, the High Dam and the temple of Philae on its island, before the boat sets off.",tone:"warm"},
    {t:"Kom Ombo",p:"A double temple of the Ptolemaic and Roman periods on a bend of the river, dedicated to two gods at once: Sobek, the crocodile god, and Horus, the falcon-headed god.",tone:"dark"},
    {img:"cruise_edfu",t:"Edfu",p:"The Temple of Horus at Edfu is one of the best-preserved temples in Egypt. It was begun under Ptolemy III in 237 BC and completed in 57 BC.",tone:"warm"},
    {img:"cruise_luxor",t:"Luxor",p:"Karnak and Luxor Temple on the East Bank, then the West Bank: the Valley of the Kings, Hatshepsut’s temple and the Colossi of Memnon.",tone:"dark",why:"Between visits there is time on deck to watch the river: palm groves, villages and desert on either side."}],
  sec:{eyebrow:"Before you book",title:"Same river, different extras",ps:["All three options sail from Aswan to Luxor over four days with an Egyptologist guide. They differ in what is added: Abu Simbel and a hot-air balloon flight over Luxor are part of two options and extras on the private one, and entrance fees are extra on two of them. Check what is included on the partner’s page before you book."],
    tabs:[["𓇳","Early starts","Abu Simbel and the balloon flight start before dawn."],["𓊛","Departures","The Viator options leave Aswan on Monday, Wednesday or Friday."],["𓉐","Entrance fees","Included on the private option; extra, or paid locally, on the other two."]]},
  papy:[["Who it suits","The river as the highlight","Choose a cruise if you want an easy, relaxed trip with the river as the highlight. If the monuments are your priority and you like to set your own pace, staying in hotels may suit you better."],
        ["Combine it with","An extra day in Luxor","Many people add an extra day in Luxor before or after for the sites the boat schedule rushes."]],
  sum:["Aswan to Luxor","4 days","Private option available","Pickup offered","Free cancellation on every option"],
  opts:[{n:"4-day Nile cruise from Aswan to Luxor with Abu Simbel and a balloon flight",p:"Viator",f:["Group","4 days","Abu Simbel included","Balloon flight included","Pickup offered"],pick:true,why:"Abu Simbel and the balloon flight included",fees:"Extra",cancel:"Free cancellation up to 24 hours before the experience starts"},
        {n:"4-day Nile cruise from Aswan to Luxor with a private guide",p:"Viator",f:["Private","4 days","Entrance fees included","Pickup offered"],fees:"Included",cancel:"Free cancellation up to 24 hours before the experience starts"},
        {n:"From Aswan: 4 Days Nile Cruise Luxor Abu Simbel & Air Balloon",p:"GetYourGuide",f:["4 days","Abu Simbel included","Balloon flight included","Pickup included"],fees:"Paid locally",cancel:GYG_CANCEL}],
  stay:{title:"Before or after the cruise",rows:[["Hotels in Aswan","Aswan"],["Hotels in Luxor","Luxor"]]},
  faq:[["Is it better to start in Luxor or in Aswan?","Both work. Starting in Aswan ends the trip in Luxor, which has more to see afterwards. Starting in Luxor ends it in Aswan’s calmer atmosphere. These three options all sail from Aswan to Luxor."],
       ["Does the cruise include Abu Simbel?","On two of the three options, yes, as an early-morning trip from Aswan. On the private option it is an extra."],
       ["Are the temple visits guided?","Yes. On all three options an Egyptologist guide leads the visits."]]};
if(typeof PB_INT!=="undefined"){ PB_INT.ancient.unshift("cruise"); PB_INT.luxury.push("cruise"); }
(function(){ const g=(typeof GUIDES!=="undefined")&&GUIDES.find(x=>x.slug==="nile-cruise-or-hotels-luxor-aswan"); if(g&&!g.trips.includes("cruise")) g.trips.unshift("cruise"); })();
'''


ROWS_OLD = 'const rowsC=[["Private",o=>o.f.includes("Private")?"Yes":o.f.includes("Small group")?"Small group":"Not stated"],["Length",lenOf],["Pickup",o=>o.f.includes("Pickup offered")?"Yes":"Not stated"],'
ROWS_NEW = ('const has=re=>W.opts.some(o=>o.f.some(x=>re.test(x)));'
            'const rowsC=[["Private",o=>o.f.includes("Private")?"Yes":o.f.includes("Small group")?"Small group":o.f.includes("Group")?"No, group":"Not stated"],["Length",lenOf],["Pickup",o=>o.f.some(x=>/^Pickup (offered|included)$/.test(x))?"Yes":"Not stated"],'
            '...(has(/^Abu Simbel/)?[["Abu Simbel",o=>o.f.includes("Abu Simbel included")?"Included":"Extra"]]:[]),'
            '...(has(/^Balloon/)?[["Balloon flight",o=>o.f.includes("Balloon flight included")?"Included":"Extra"]]:[]),'
            '...(has(/^Entrance fees/)?[["Entrance fees",o=>o.fees||"Not stated"]]:[]),')


def apply(tpl):
    assert tpl.count(ROWS_OLD) == 1
    tpl = tpl.replace(ROWS_OLD, ROWS_NEW)
    a = 'trips:["valley","abu"],more:"Explore Nile Cruises"'
    assert tpl.count(a) == 1
    tpl = tpl.replace(a, 'trips:["cruise","valley","abu"],more:"Explore Nile Cruises"')
    a = 'renderHome(); renderLive(); renderHome2();'
    assert tpl.count(a) == 1
    i = tpl.index('\n' + a)
    return tpl[:i] + JS + tpl[i:]
