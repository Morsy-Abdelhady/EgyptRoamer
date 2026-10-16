// ---------- Six more trips in the four world templates, plus FAQ, cancellation and hotels for all ----------
// Every word is from the trip's page on egyptroamer.com (16 Oct). Cancellation: as each partner's own page states it
// (GetYourGuide and Viator pages read on 16 Oct).
const GYG_CANCEL = "Free cancellation up to 24 hours in advance";
const CAIRO_STAY = {title:"Staying in Cairo?",rows:[["Hotels in Giza, near the pyramids","Giza"],["Hotels in Downtown Cairo (Qasr El Nil)","Downtown Cairo"],["Hotels in Zamalek","Zamalek"],["Hotels in Cairo","Cairo"]]};

Object.assign(WORLDS.valley, {place:"Luxor", faq:[
  ["Is Tutankhamun’s tomb worth the extra ticket?","Its fame is its main draw. The tomb is small, and his treasures are now at the Grand Egyptian Museum in Cairo. Other royal tombs, such as that of Seti I (also a separate ticket), are larger and more richly decorated."],
  ["How long does the West Bank take?","A morning covers the Valley of the Kings, Hatshepsut’s temple and the Colossi of Memnon. A full day allows more tombs and temples."],
  ["Can I visit without a guide?","Yes, but you’ll get much more from the tombs with one, especially on a first visit."]]});
Object.assign(WORLDS.redsea, {place:"Red Sea", stay:{title:"Staying by the Red Sea?",rows:[["Hotels in Hurghada","Hurghada"],["Hotels in El Gouna","El Gouna"]]}, faq:[
  ["Can I dive without any experience?","Yes, as a try-dive with an instructor, at shallow depth, after a briefing. You’ll need to be in reasonable health."],
  ["When is the best time to dive the Red Sea?","It’s possible all year. Water is warmest from late summer into autumn and coolest in late winter, when a thicker wetsuit helps."],
  ["What about sea life encounters?","Reef fish, turtles and rays are common on many sites. Encounters with larger animals depend on the site and the season, and nothing is guaranteed."]]});
Object.assign(WORLDS.white, {place:"Western Desert", faq:[
  ["Is camping in the White Desert safe?","With a licensed local operator and an experienced driver, yes. Don’t try to drive into the desert on your own."],
  ["Are there toilets or showers at camp?","Usually not. Camps are basic, which is part of the experience. Check with your operator."],
  ["Can I do it as a day trip?","Not really. The distances are long, and the formations are at their best at sunset and sunrise. The overnight camp is the point."]]});
Object.assign(WORLDS.nile, {place:"Cairo & Giza", stay:CAIRO_STAY, faq:[
  ["Is a Nile dinner cruise worth it?","For a lively evening with dinner, music and the city lights, yes. For a quieter view of the river, choose a felucca at sunset instead."],
  ["What is the tanoura dance?","A traditional Egyptian dance in which performers spin continuously while turning layered, brightly coloured skirts. It’s related to the whirling of Sufi practice."],
  ["What should I wear?","Smart-casual is fine. Bring a light layer, since it can be cool on the open deck in winter."]]});

Object.assign(WORLDS, {
  giza: {cls:"w-tomb", fx:"tomb", photo:"giza_l", video:"video/giza-sunset.mp4", vcredit:"Giza · Video: Peggy Anke, Pexels", url:"pyramids-of-giza-sphinx-private-tour", place:"Cairo & Giza",
    eyebrow:"Giza · Cairo · Half day", title:"Pyramids of Giza & Sphinx Private Tour",
    lead:"A private tour of the Giza plateau with your own Egyptologist guide and driver. You’ll see the three great pyramids of Khufu, Khafre and Menkaure, the panoramic viewpoint and the Sphinx with its valley temple, at your own pace and without joining a group.",
    quote:"Giza is simple to reach but surprisingly hard to do well.", chips:["Half day","3 options from 2 partners","Private","Pickup offered"], go:"Walk the plateau",
    jEyebrow:"What you actually do", jTitle:"Across the plateau",
    steps:[
      {t:"The Great Pyramid",p:"Walk around the Great Pyramid, the only surviving Wonder of the Ancient World."},
      {t:"Khafre and Menkaure",p:"Visit the pyramids of Khafre and Menkaure and the smaller queens’ pyramids."},
      {t:"The panoramic viewpoint",p:"Stop at the panoramic viewpoint for the classic photo of all the pyramids in a row.",video:"video/giza-camels.mp4",credit:"Near the pyramids of Giza · Video: K, Pexels"},
      {t:"The Sphinx",p:"Descend to the Sphinx and walk through the valley temple beside it.",why:"Optionally, go inside one of the pyramids. This needs an extra ticket; the passages are narrow, steep and hot."}],
    sec:{eyebrow:"Before you go",title:"A good guide makes it simple",ps:["The site is large and exposed, the approaches are crowded with touts, and the story behind the monuments doesn’t explain itself. A good guide handles tickets and the route across the plateau, keeps unwanted sellers at a distance, and turns the stones into the history of the Old Kingdom."],
      tabs:[["𓇳","Timing","Book the earliest start you can. The light is softer and it’s cooler and quieter."],["𓈗","What to bring","Water, a hat, sun protection and comfortable shoes. There’s very little shade."],["𓉐","Inside the pyramids","Not recommended if you’re claustrophobic or have mobility or heart problems."],["𓃗","Camel and horse rides","If you want one, agree the price, the duration and the route in full before you get on. A good guide will help."]]},
    papy:[["Who it suits","Context, without the hassle","First-time visitors who want context and a hassle-free morning, families, and anyone short on time."],
          ["Combine it with","The Grand Egyptian Museum","Right next to the plateau and home to the full Tutankhamun collection. Better as a separate half-day if you can. To the south: Saqqara, Memphis and Dahshur."]],
    sum:["Cairo","About 4–8 hours","Private","Pickup offered"],
    opts:[{n:"Private half-day tour of the Giza pyramids",p:"Viator",f:["Private","About 4–5 hours","Pickup offered"]},
          {n:"Giza pyramids, Sphinx and Grand Egyptian Museum full day",p:"Viator",f:["Private","About 6–8 hours","Pickup offered"]},
          {n:"Cairo: Pyramids & Great Sphinx Private Tour with Camel Ride",p:"GetYourGuide",f:["Private","4–8 hours","Pickup offered"]}],
    stay:CAIRO_STAY,
    faq:[["Is a private tour better than a group tour?","It’s more flexible and calmer. You choose the pace and the stops. Group tours cost less but follow a fixed schedule."],
         ["Can I go inside the Great Pyramid?","Yes, with an extra ticket. The ascending passages are narrow and steep, and the chamber inside is bare. Most visitors find the outside and the viewpoint more impressive."],
         ["How early should I start?","As early as the site opens. Mornings are cooler and the crowds build through the day."]]},

  abu: {cls:"w-tomb", fx:"tomb", photo:"abu_l", url:"abu-simbel-day-trip-from-aswan", place:"Aswan",
    eyebrow:"Aswan · Nubia · Full day", title:"Abu Simbel Day Trip from Aswan",
    lead:"A day trip from Aswan to Abu Simbel, far to the south near the Sudanese border. You’ll see the two rock-cut temples built by Ramesses II: the Great Temple, fronted by four colossal seated statues of the king, and the smaller temple dedicated to Queen Nefertari and the goddess Hathor.",
    chips:["Full day","3 options from 2 partners","Private or small group","Pickup offered"], go:"Make the journey south",
    jEyebrow:"Why it’s worth the effort", jTitle:"Into the rock",
    steps:[
      {t:"Leave in the dark",p:"By road, the classic option, leaving Aswan in the dark before dawn and arriving in the morning. It’s a very long day, most of it in the vehicle.",tone:"night",why:"By air: short flights from Aswan save hours on the road, at a higher cost."},
      {t:"Scale",p:"The façade of the Great Temple is one of the most famous images of ancient Egypt, and it’s even bigger in person.",tone:"warm"},
      {t:"The interiors",p:"Carved halls lead deep into the rock, with battle scenes and statues of the king as a god.",tone:"dark"},
      {t:"The rescue",p:"In the 1960s, as Lake Nasser rose behind the new High Dam, the temples were cut into blocks and reassembled on higher ground. Knowing that as you stand inside makes the visit remarkable.",tone:"warm"}],
    sec:{eyebrow:"The sun festival",title:"When the sun reaches the sanctuary",ps:["On or around 22 February and 22 October, the rising sun shines along the Great Temple’s axis into the innermost sanctuary and lights the seated statues there. These days draw large crowds and events. Book far ahead if you want to see it."],
      tabs:[["𓇳","","Bring breakfast or snacks, water, and a layer for the early morning."],["𓈗","","Sun protection is essential. The forecourt is fully exposed."],["𓁹","","Photography rules inside the temples can change. Follow the guards."]]},
    papy:[["Who it suits","A real interest in ancient Egypt","Anyone with a real interest in ancient Egypt, and travellers staying at least two days in Aswan. If long road journeys are hard for you, fly, or consider a Lake Nasser cruise."],
          ["Combine it with","Philae and a felucca","Philae temple, the Unfinished Obelisk and a felucca in Aswan. Plan them for the days before or after, not the same day."]],
    sum:["Aswan","About 8–10 hours","Private or small group","Pickup offered"],
    opts:[{n:"Private Abu Simbel day tour from Aswan",p:"Viator",f:["Private","About 8–10 hours","Pickup offered"],pick:true},
          {n:"Shared Abu Simbel day trip from Aswan",p:"Viator",f:["Small group","About 10 hours","Pickup offered"]},
          {n:"From Aswan: Abu Simbel Day Tour with Private Guide and Car",p:"GetYourGuide",f:["Private","10 hours","Pickup offered"]}],
    stay:{title:"Staying in Aswan?",rows:[["Hotels in Aswan","Aswan"]]},
    faq:[["Can I visit Abu Simbel without a tour?","Yes, you can arrange your own driver or fly independently. Most travellers still book a tour or transfer for the pre-dawn road journey."],
         ["How long do you spend at the temples?","Usually a few hours, enough for both temples without rushing."],
         ["Is it worth going on the sun festival day?","It’s unforgettable, but very busy. On other days you get far more space in the temples."]]},

  alex: {cls:"w-nile w-city", fx:"nile", photo:"alex_l", video:"video/alexandria-qaitbay.mp4", vcredit:"Qaitbay Citadel, Alexandria · Video: The Instagrapher, Pexels", url:"alexandria-day-trip-from-cairo", place:"Alexandria",
    eyebrow:"Alexandria · The Mediterranean · Full day from Cairo", title:"Alexandria Day Trip from Cairo",
    lead:"A full day from Cairo to Egypt’s Mediterranean city and back. Alexandria was founded by Alexander the Great and became the capital of the Ptolemies.",
    quote:"A side of Egypt that is Greek, Roman and Mediterranean as much as pharaonic.", chips:["About 10–12 hours","Small-group option available","Pickup offered"], go:"Spend the day by the sea",
    jEyebrow:"What you see", jTitle:"Along the Mediterranean",
    steps:[
      {img:"alex_c1",t:"The Citadel of Qaitbay",p:"The 15th-century sea fort built on the site of the ancient Pharos lighthouse, reusing its stone."},
      {img:"alex_c2",t:"The Bibliotheca Alexandrina",p:"The modern library on the seafront, a tribute to the ancient Library of Alexandria."},
      {t:"Kom el-Shoqafa",p:"Roman-era catacombs cut deep into the rock, where Egyptian gods appear in Greco-Roman style.",tone:"dark"},
      {img:"alex_c3",t:"Pompey’s Pillar",p:"A single granite column on the hill of the ancient Serapeum, raised in fact for the emperor Diocletian."},
      {t:"The Corniche and Montaza",p:"The seafront promenade and the former royal gardens at its eastern end.",tone:"warm",why:"Lunch is usually fish, chosen from the ice and grilled or fried as you ask."}],
    sec:{eyebrow:"Making one day count",title:"Four or five sites, chosen well",ps:["The round trip from Cairo takes a large share of the day, so most tours start early and focus on four or five sites. A private car with a guide lets you choose them. Tours differ in which sites they include, so compare the itineraries."],
      tabs:[["𓉐","","Check which sites are visited inside, and which are seen only from outside."],["𓏊","","Ask whether entry tickets and lunch are included."],["𓇳","","Confirm the pickup time and place in Cairo or Giza."]]},
    papy:[["Who it suits","A complete change of scene","Travellers with a few days in Cairo who want a complete change of scene, and anyone interested in the Greek and Roman story of Egypt. If you have more time, two days in Alexandria are better than one."]],
    sum:["Alexandria","About 10–12 hours","Small-group option available","Pickup offered"],
    opts:[{n:"Alexandria day trip from Cairo",p:"Viator",f:["About 10–12 hours","Pickup offered"]},
          {n:"From Cairo: Historical Alexandria Shared Day Tour with Lunch",p:"GetYourGuide",f:["Small group","11 hours","Pickup offered"]}],
    stay:{title:"Staying in Alexandria?",rows:[["Hotels in Alexandria","Alexandria"]]},
    faq:[["Is a day trip to Alexandria worth it?","Yes, if you have a spare day in Cairo. You will see the highlights, though staying a night gives a more relaxed visit."],
         ["Can I still see the ancient Lighthouse or Library?","Neither survives. The Citadel of Qaitbay stands on the lighthouse’s site, and the modern Bibliotheca Alexandrina honours the ancient library."],
         ["Can I go by train instead of a tour?","Yes. Trains run between Cairo and Alexandria throughout the day, and you can move around the city by taxi or ride-hailing app."]]},

  ras: {cls:"w-sea", fx:"sea", photo:"sea_l", video:"video/redsea-reef.mp4", vcredit:"Red Sea, Egypt · Video: Adrien JACTA, Pexels", url:"ras-mohammed-snorkelling-from-sharm", place:"Red Sea",
    eyebrow:"Sharm El Sheikh · South Sinai · About 5–6 hours", title:"Ras Mohammed Snorkelling from Sharm El Sheikh",
    lead:"A half-day or full-day trip from Sharm El Sheikh to Ras Mohammed National Park, at the southern tip of the Sinai Peninsula. Declared in 1983, it was Egypt’s first national park.",
    quote:"The contrast of desert and sea is part of the experience.", chips:["About 5–6 hours","Pickup offered","Snorkellers of every level"], go:"Into the park",
    jEyebrow:"By road or by boat", jTitle:"Two ways to the reef",
    steps:[
      {t:"By road",p:"A bus or jeep trip into the park, with snorkelling from the shore and stops at its viewpoints and coastal landmarks. A good choice if you prefer to stay close to land.",tone:"warm"},
      {t:"By boat",p:"A day boat from Sharm reaches the reefs from the water, with time to snorkel at more than one site. Divers can often join the same boat."},
      {t:"What you see",p:"Coral gardens and steep reef walls close to the surface, with reef fish in great numbers. Turtles and rays are seen on some days, but nothing is guaranteed.",video:"video/redsea-coral.mp4",credit:"Red Sea · Video: Adrien JACTA, Pexels"}],
    sec:{eyebrow:"The park rules",title:"A protected reef",ps:["Ras Mohammed is protected. Never touch or stand on coral, don’t feed fish, and don’t collect anything. Rules on anchoring and fishing are strict, and operators should follow them."],
      tabs:[["𓈗","","Swimwear, a towel and a rash vest or reef-friendly sun protection."],["𓁹","","Your own mask if you have one that fits well."],["𓇳","","Water and a hat. There is little shade on shore."]]},
    papy:[["Who it suits","Every level, beginners too","Snorkellers of every level, including beginners. Tell the operator how well you swim, and ask for a life jacket or snorkel vest if you want one. For diving, see our Red Sea diving trip."]],
    sum:["Sharm El Sheikh","About 5–6 hours","Pickup offered"],
    opts:[{n:"Ras Mohammed snorkeling trip from Sharm El Sheikh",p:"Viator",f:["About 5 hours","Pickup offered"]},
          {n:"From Sharm: White Island and Ras Mohamed Snorkeling Trip",p:"GetYourGuide",f:["6 hours","Pickup offered"]}],
    stay:{title:"Staying in Sharm El Sheikh?",rows:[["Hotels in Sharm El Sheikh","Sharm El Sheikh"]]},
    faq:[["Do I need to be a strong swimmer?","No. Guides accompany beginners, and vests are usually available. Say so when you book."],
         ["Is the park fee included?","It depends on the tour. Check before you book."],
         ["When is the best time to go?","The sea is warm for most of the year. Spring and autumn combine warm water with comfortable air temperatures."]]},

  siwa: {cls:"w-desert", fx:"desert", photo:"siwa_l", url:"great-sand-sea-4-4-hot-springs", place:"Western Desert",
    eyebrow:"Siwa Oasis · The Great Sand Sea · Full day", title:"Great Sand Sea 4×4 & Hot Springs",
    lead:"A 4×4 excursion from Siwa into the Great Sand Sea, one of the largest dune fields in the world, stretching south of the oasis. With a local driver you cross towering dunes, stop to swim in springs in the middle of the desert, try sand-boarding, and watch the sun set over the sand.",
    quote:"Trips usually leave in the afternoon, so you’re in the dunes in the best light.", chips:["Private option available","Pickup offered"], go:"Into the dunes",
    jEyebrow:"What the day is like", jTitle:"Dunes, springs, sunset",
    steps:[
      {t:"Dune driving",p:"Steep climbs and descents that are part rollercoaster, part landscape tour. Tell your driver if you’d prefer a gentler ride.",tone:"warm"},
      {t:"Springs",p:"Hot and cold springs in the desert. A dip in warm water with dunes all around is the highlight for many.",tone:"warm"},
      {t:"Sand-boarding",p:"Sand-boarding down the dunes.",tone:"dark"},
      {t:"Sunset",p:"Sunset from the top of a dune, often with tea made on a small fire.",tone:"night"}],
    sec:{eyebrow:"Choosing an operator",title:"Never go into the dunes on your own",night:true,ps:["Sun protection, sunglasses and water; swimwear and a towel for the springs; and a warm layer, because it gets cold quickly after sunset, especially in winter."],
      tabs:[["𓀀","","A licensed Siwan operator and an experienced desert driver."],["𓊡","","A vehicle in good condition, with water and communication equipment."],["𓉐","","Whether permits for the desert areas are arranged for you."],["𓇳","","Whether the trip includes springs, sand-boarding and sunset, or goes further for a longer day or an overnight camp."]]},
    papy:[["Who it suits","The classic Siwa excursion","Anyone staying in Siwa. The dune driving can be rough, so it’s not suitable if you have back problems or are pregnant. Ask for a calmer route."],
          ["Combine it with","The oasis itself","The Shali fortress, the Oracle of Amun and Cleopatra’s Spring in the oasis itself. See our Siwa guide."]],
    sum:["Siwa","Private option available","Pickup offered"],
    opts:[{n:"Siwa Oasis 3-day trip from Cairo, with a Great Sand Sea safari",p:"Viator",f:["Private","About 3 days","Pickup offered"],pick:true,why:"The only private option"},
          {n:"2 Nights 3 Days at Siwa Oases camp from Cairo",p:"GetYourGuide",f:["3 days","Pickup offered"]}],
    stay:{title:"Staying in Siwa?",rows:[["Hotels in Siwa Oasis","Siwa Oasis"]]},
    faq:[["Is the dune driving scary?","It can be exhilarating. Drivers adjust to their passengers, so just ask for a gentler ride."],
         ["Do I need to book in advance?","In the main season it’s wise to arrange it on arrival or through your lodge. Local operators run trips most days."],
         ["Can I stay overnight in the desert?","Yes, many operators offer an overnight camp in the dunes, for a night under the stars."]]},

  food: {cls:"w-nile w-city", fx:"nile", photo:"food_l", url:"cairo-street-food-tour-by-night", place:"Cairo & Giza",
    eyebrow:"Cairo · After dark · 4 hours", title:"Cairo Street Food Tour by Night",
    lead:"An evening walk through Cairo’s neighbourhoods with a local guide, tasting street food and snacks at small shops, carts and eateries.",
    quote:"Cairo eats late, so the city is at its most alive after dark.", chips:["About 3–4 hours","2 options"], go:"Start tasting",
    jEyebrow:"What you’ll taste", jTitle:"Stop by stop, plate by plate",
    steps:[
      {t:"Koshari",p:"Rice, lentils, pasta, spicy tomato sauce and crispy onions, Cairo’s signature dish.",tone:"warm"},
      {t:"Ful medames and ta’meya",p:"Slow-cooked fava beans, and Egyptian falafel made from fava beans.",tone:"warm"},
      {t:"Hawawshi",p:"Spiced minced meat baked inside bread.",tone:"dark"},
      {t:"Feteer meshaltet",p:"Flaky layered pastry, with savoury or sweet fillings.",tone:"warm"},
      {t:"Sweets and drinks",p:"Basbousa, konafa, or rice pudding; fresh sugarcane juice, mint tea, or coffee in an ahwa.",tone:"night"}],
    sec:{eyebrow:"Why go with a guide",title:"The best food has no English menu",ps:["The best food in Cairo is in places without menus in English, and often without signs at all. A guide knows where to go, orders for you, explains the dishes, and handles hygiene questions."],
      tabs:[["𓏊","","Tell the operator about allergies and dietary needs. Vegetarian eating is easy in Egypt, since many classics are meat-free."],["𓌉","","Come hungry: tastings add up."],["𓂻","","Wear comfortable shoes: you’ll be walking on uneven streets."]]},
    papy:[["Who it suits","A good first evening","First-time visitors, food lovers, and anyone who wants to see local life beyond the monuments. It’s a good first evening in Cairo, when you’re still getting your bearings."],
          ["Combine it with","Islamic Cairo or Downtown","A day in Islamic Cairo or Downtown. See our Cairo guide for the areas and more on Cairo food."]],
    sum:["Cairo","About 3–4 hours"],
    opts:[{n:"Cairo Nights street-food tour",p:"Viator",f:["About 4 hours"]},
          {n:"Cairo evening food tour with 40 tastings",p:"Viator",f:["About 3 hours"]}],
    stay:CAIRO_STAY,
    faq:[["Is Cairo street food safe to eat?","Eating at busy places with high turnover, as guides do, is the best approach. Drink bottled water, and be careful with raw salads if your stomach is sensitive."],
         ["Is it suitable for vegetarians?","Yes. Koshari, ful, ta’meya and feteer are traditionally vegetarian. Tell your guide in advance."],
         ["How late does it run?","Evening tours usually start after sunset and last a few hours. Check the start time with the operator."]]},
});
// Cancellation as each partner states it (all 9 GetYourGuide and all 17 Viator pages read on 16 Oct).
const VIATOR_CANCEL = "Free cancellation up to 24 hours before the experience starts";
Object.values(WORLDS).forEach(W=>{ W.opts.forEach(o=>{ o.cancel = o.p==="GetYourGuide" ? GYG_CANCEL : o.p==="Viator" ? VIATOR_CANCEL : o.cancel; });
  if(W.opts.every(o=>o.cancel) && !W.sum.includes("Free cancellation on every option")) W.sum.push("Free cancellation on every option"); });
