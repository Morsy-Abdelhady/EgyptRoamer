/* ==========================================================================
   Egypt Roamer — content & affiliate data
   Everything rendered by the homepage components comes from here. Replace the
   sample objects with a CMS / partner API response of the same shape.

   Affiliate fields:
     partner   — display name of the booking partner
     href      — the tracked affiliate deep link (placeholder "#partner")
     cta       — button copy (View Deal, Check Availability, See Hotel …)
   Prices, ratings and review counts below are SAMPLE values.
   ========================================================================== */

/** Build an Unsplash CDN url at a given width. */
export const img = (id, w = 1200, q = 75) =>
  `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&q=${q}`;

/** srcset helper for responsive images. */
export const srcset = (id, widths = [480, 800, 1200, 1800]) =>
  widths.map((w) => `${img(id, w)} ${w}w`).join(", ");

/* ------------------------------------------------------------------------ */
/* Destinations                                                              */
/* ------------------------------------------------------------------------ */
export const destinations = [
  {
    id: "cairo",
    name: "Cairo",
    region: "Capital · Nile Valley",
    tagline: "The city of a thousand minarets",
    desc: "A thousand-year-old capital that never sleeps — the pyramids on its doorstep, the Grand Egyptian Museum, and the lantern-lit lanes of Islamic Cairo.",
    highlights: ["Pyramids of Giza", "Grand Egyptian Museum", "Khan el-Khalili"],
    best: "Oct – Apr",
    reach: "International hub",
    image: "1679238211153-546821b75f98",
    mapReach: "The capital & international gateway",
    coords: [31.24, 30.04],
  },
  {
    id: "luxor",
    name: "Luxor",
    region: "Upper Egypt",
    tagline: "The world's greatest open-air museum",
    desc: "Karnak's forest of columns, the tombs of the Valley of the Kings, and hot-air balloons drifting over the West Bank at dawn.",
    highlights: ["Valley of the Kings", "Karnak Temple", "Dawn balloon flight"],
    best: "Oct – Mar",
    reach: "1h flight from Cairo",
    image: "1761056981183-3faf66a62d6e",
    mapReach: "1h flight or overnight train from Cairo",
    coords: [32.64, 25.69],
  },
  {
    id: "aswan",
    name: "Aswan",
    region: "Nubia · Upper Egypt",
    tagline: "Egypt at its most serene",
    desc: "Granite islands, colour-washed Nubian villages and feluccas drifting at golden hour — the gateway to Abu Simbel and Philae.",
    highlights: ["Philae Temple", "Nubian villages", "Abu Simbel"],
    best: "Oct – Mar",
    reach: "1h 25m flight from Cairo",
    image: "1644517270263-4112379d97ca",
    mapReach: "1h 25m flight · or sail from Luxor",
    coords: [32.9, 24.09],
  },
  {
    id: "hurghada",
    name: "Hurghada",
    region: "Red Sea Coast",
    tagline: "Reefs, islands and endless sun",
    desc: "A reef-fringed coastline with easy diving, island days on Giftun, and the design-led lagoons of El Gouna just up the shore.",
    highlights: ["Giftun Island", "Reef diving", "El Gouna marina"],
    best: "Year-round",
    reach: "1h flight from Cairo",
    image: "1722264222007-3e4f1808db3e",
    mapReach: "1h flight · 4h drive from Luxor",
    coords: [33.81, 27.26],
  },
  {
    id: "sharm",
    name: "Sharm El Sheikh", // non-breaking: never strand "Sheikh"
    region: "South Sinai",
    tagline: "Where the desert meets the reef",
    desc: "World-class diving at Ras Mohammed, Bedouin dinners under the stars, and a sunrise climb to the summit of Mount Sinai.",
    highlights: ["Ras Mohammed", "Mount Sinai sunrise", "Tiran Island"],
    best: "Year-round",
    reach: "1h flight from Cairo",
    image: "1681158077449-77f23f629f0d",
    mapReach: "1h flight · fast ferry from Hurghada",
    coords: [34.33, 27.91],
  },
  {
    id: "siwa",
    name: "Siwa",
    region: "Western Desert",
    tagline: "An oasis at the edge of the map",
    desc: "Salt lakes you float in, mud-brick citadels and hot springs among the palms — closer to Libya than to Cairo, and worth every mile.",
    highlights: ["Shali fortress", "Salt-lake floating", "Great Sand Sea"],
    best: "Oct – Apr",
    reach: "8h scenic drive",
    image: "1640341742389-dd75ffb38d49",
    mapReach: "8h drive via the Mediterranean coast",
    coords: [25.52, 29.2],
  },
  {
    id: "alexandria",
    name: "Alexandria",
    region: "Mediterranean",
    tagline: "The pearl of the Mediterranean",
    desc: "Cosmopolitan and sea-breezy — the Bibliotheca, the Citadel of Qaitbay and the best seafood on the Corniche.",
    highlights: ["Bibliotheca", "Qaitbay Citadel", "Seafood on the Corniche"],
    best: "Apr – Oct",
    reach: "2h 30m drive",
    image: "1633624646814-3a61ed3e2847",
    mapReach: "2h 30m drive or train from Cairo",
    coords: [29.92, 31.2],
  },
];

/* ------------------------------------------------------------------------ */
/* Travel moods — "What kind of Egypt are you looking for?"                  */
/* ------------------------------------------------------------------------ */
export const moods = [
  {
    id: "ancient",
    label: "Ancient",
    icon: "i-temple",
    tint: "var(--clay)",
    image: "1761056981183-3faf66a62d6e",
    desc: "Of torch-lit tombs, temple columns taller than time, and the first light of dawn on the Giza plateau.",
    recs: {
      dest: "luxor",
      exp: { title: "Valley of the Kings & Karnak with an Egyptologist", price: 65, rating: 4.9, partner: "GetYourGuide", cta: "View Tour", image: "1566288592443-0a0d6853dddc" },
      stay: { title: "Sofitel Winter Palace, Luxor", price: 240, rating: 4.7, partner: "Booking.com", cta: "See Hotel", image: "1557640047-75c97a5f1ea4" },
    },
  },
  {
    id: "beach",
    label: "Beach",
    icon: "i-palm",
    tint: "var(--teal)",
    image: "1722264222007-3e4f1808db3e",
    desc: "Of reef-edge mornings, barefoot dinners and water so clear it barely looks real.",
    recs: {
      dest: "hurghada",
      exp: { title: "Giftun Island snorkel cruise with lunch", price: 35, rating: 4.8, partner: "Viator", cta: "View Deal", image: "1682687982470-8f1b0e79151a" },
      stay: { title: "Beach resort in El Gouna", price: 160, rating: 4.8, partner: "Expedia", cta: "See Hotel", image: "1722264219947-725d4e20ef23" },
    },
  },
  {
    id: "luxury",
    label: "Luxury",
    icon: "i-diamond",
    tint: "var(--gold)",
    image: "1764730358483-38395275edb5",
    desc: "Of palace hotels, private Egyptologists and first-class cabins gliding down the Nile.",
    recs: {
      dest: "aswan",
      exp: { title: "Private sunset felucca with dinner on board", price: 120, rating: 4.9, partner: "GetYourGuide", cta: "Check Availability", image: "1770251693846-4167f4c049c6" },
      stay: { title: "Sofitel Legend Old Cataract", price: 320, rating: 4.8, partner: "Booking.com", cta: "See Hotel", image: "1633033254409-bd538e785f51" },
    },
  },
  {
    id: "adventure",
    label: "Adventure",
    icon: "i-mountain",
    tint: "var(--clay)",
    image: "1637189315455-3e7e629a05b9",
    desc: "Of dune runs, desert camps, canyon hikes and nights beneath an unfiltered Milky Way.",
    recs: {
      dest: "sharm",
      exp: { title: "Mount Sinai sunrise trek with Bedouin guide", price: 45, rating: 4.8, partner: "Viator", cta: "View Tour", image: "1655815226495-68d7d78894c4" },
      stay: { title: "Desert camp, White Desert", price: 90, rating: 4.9, partner: "GetYourGuide", cta: "Check Availability", image: "1514975440715-7b6852af4ee7" },
    },
  },
  {
    id: "culture",
    label: "Culture",
    icon: "i-lantern",
    tint: "var(--nile)",
    image: "1710211288826-b7df3ab71588",
    desc: "Of old bazaars, Nubian villages, Sufi music and cafés that never quite close.",
    recs: {
      dest: "cairo",
      exp: { title: "Islamic Cairo & Khan el-Khalili after dark", price: 30, rating: 4.9, partner: "GetYourGuide", cta: "View Tour", image: "1652022262085-d454346a353e" },
      stay: { title: "Marriott Mena House, Giza", price: 210, rating: 4.7, partner: "Expedia", cta: "See Hotel", image: "1734461255986-048992c9d15d" },
    },
  },
  {
    id: "food",
    label: "Food",
    icon: "i-food",
    tint: "var(--clay)",
    image: "1661994215679-cde7c2c5c060",
    desc: "Of koshari at midnight, warm baladi bread and grilled fish by the Mediterranean.",
    recs: {
      dest: "alexandria",
      exp: { title: "Downtown Cairo street-food tour", price: 39, rating: 4.9, partner: "Viator", cta: "View Tour", image: "1746274394124-141a1d1c5af3" },
      stay: { title: "Steigenberger Cecil, Alexandria", price: 110, rating: 4.5, partner: "Booking.com", cta: "See Hotel", image: "1594808815295-52034d585f56" },
    },
  },
  {
    id: "nature",
    label: "Nature",
    icon: "i-leaf",
    tint: "var(--teal)",
    image: "1753488819968-756a12153bae",
    desc: "Of palm oases, salt lakes, protected reefs and the quiet green ribbon of the Nile.",
    recs: {
      dest: "siwa",
      exp: { title: "Ras Mohammed National Park snorkel day", price: 65, rating: 4.8, partner: "GetYourGuide", cta: "View Deal", image: "1708649290066-5f617003b93f" },
      stay: { title: "Adrère Amellal eco-lodge, Siwa", price: 450, rating: 4.9, partner: "Hotels.com", cta: "See Hotel", image: "1584114130913-0852aeb8fe8c" },
    },
  },
];

/* ------------------------------------------------------------------------ */
/* Featured experiences (affiliate cards)                                    */
/* ------------------------------------------------------------------------ */
export const experiences = [
  { id: "exp-giza", title: "Pyramids of Giza & Sphinx Private Tour", location: "Giza, Cairo", tag: "cairo", duration: "Half day", rating: 4.9, reviews: 2318, price: 45, badge: "Bestseller", image: "1678038592327-c5730737f867", partner: "GetYourGuide", cta: "Check Availability", href: "#partner" },
  { id: "exp-dinner", title: "Nile Dinner Cruise with Live Show", location: "Cairo", tag: "cairo", duration: "3 hours", rating: 4.7, reviews: 1204, price: 38, image: "1761421852464-463fb31f2dc0", partner: "Viator", cta: "View Deal", href: "#partner" },
  { id: "exp-abu", title: "Abu Simbel Day Trip from Aswan", location: "Aswan", tag: "aswan", duration: "Full day", rating: 4.9, reviews: 864, price: 120, badge: "Likely to sell out", image: "1633163893862-4cdc62de7d82", partner: "GetYourGuide", cta: "Check Availability", href: "#partner" },
  { id: "exp-dive", title: "Red Sea Diving: Two Reef Dives", location: "Hurghada", tag: "redsea", duration: "1 day · 2 dives", rating: 4.8, reviews: 1532, price: 55, image: "1682687981907-170c006e3744", partner: "Viator", cta: "View Deal", href: "#partner" },
  { id: "exp-safari", title: "White Desert & Black Desert Safari", location: "Western Desert", tag: "desert", duration: "2 days", rating: 4.9, reviews: 612, price: 140, badge: "Editor's pick", image: "1514975440715-7b6852af4ee7", partner: "GetYourGuide", cta: "Check Availability", href: "#partner" },
  { id: "exp-food", title: "Cairo Street Food Tour by Night", location: "Downtown Cairo", tag: "cairo", duration: "4 hours", rating: 4.9, reviews: 978, price: 39, image: "1661994215679-cde7c2c5c060", partner: "Viator", cta: "View Deal", href: "#partner" },
  { id: "exp-valley", title: "Valley of the Kings & Hatshepsut Temple", location: "Luxor", tag: "luxor", duration: "6 hours", rating: 4.8, reviews: 2011, price: 55, image: "1566288592443-0a0d6853dddc", partner: "GetYourGuide", cta: "Check Availability", href: "#partner" },
  { id: "exp-siwa", title: "Great Sand Sea 4×4 & Hot Springs", location: "Siwa Oasis", tag: "desert", duration: "Full day", rating: 4.7, reviews: 233, price: 75, image: "1637189315455-3e7e629a05b9", partner: "Viator", cta: "View Deal", href: "#partner" },
];

export const experienceFilters = [
  { id: "all", label: "All" },
  { id: "cairo", label: "Cairo" },
  { id: "luxor", label: "Luxor" },
  { id: "aswan", label: "Aswan" },
  { id: "redsea", label: "Red Sea" },
  { id: "desert", label: "Desert" },
];

/* ------------------------------------------------------------------------ */
/* Affiliate discovery categories                                            */
/* ------------------------------------------------------------------------ */
export const partnerCategories = [
  {
    id: "hotels",
    label: "Hotels",
    icon: "i-bed",
    headline: "Find a place worth staying for.",
    copy: "From palace hotels on the Nile to candle-lit desert lodges — stays our editors would book themselves.",
    image: "1760261598144-dddd7e1a3ef9",
    compare: "Compare Hotels",
    trust: ["Free cancellation on most stays", "Verified guest reviews", "Pay on the partner's secure site"],
    items: [
      { name: "Sofitel Legend Old Cataract", location: "Aswan", meta: "Historic palace · Nile views", rating: 4.8, reviews: 1200, price: 320, unit: "night", partner: "Booking.com", cta: "See Hotel", image: "1633033254409-bd538e785f51", badge: "Iconic" },
      { name: "Marriott Mena House", location: "Giza", meta: "Pyramid-view rooms · Garden pool", rating: 4.7, reviews: 2960, price: 210, unit: "night", partner: "Expedia", cta: "Check Availability", image: "1734461255986-048992c9d15d" },
      { name: "Adrère Amellal", location: "Siwa Oasis", meta: "Eco-lodge · No electricity, by design", rating: 4.9, reviews: 210, price: 450, unit: "night", partner: "Hotels.com", cta: "View Deal", image: "1584114130913-0852aeb8fe8c" },
    ],
  },
  {
    id: "tours",
    label: "Tours & Activities",
    icon: "i-compass",
    headline: "Experiences worth leaving the hotel for.",
    copy: "Small groups, expert Egyptologists and the kind of access that turns a sight into a story.",
    image: "1559527012-3b0fca356de0",
    compare: "Explore Tours",
    trust: ["Free cancellation up to 24h", "Licensed local guides", "Instant confirmation"],
    items: [
      { name: "Luxor East & West Banks, private", location: "Luxor", meta: "Full day · Egyptologist guide", rating: 4.9, reviews: 1480, price: 65, unit: "person", partner: "GetYourGuide", cta: "View Tour", image: "1693654547147-24d94b4ed4ea", badge: "Top rated" },
      { name: "Sunrise camel ride at the pyramids", location: "Giza", meta: "2 hours · Small group", rating: 4.8, reviews: 3120, price: 30, unit: "person", partner: "Viator", cta: "Check Availability", image: "1705628080778-f86b2f90a114" },
      { name: "Philae Temple sound & light", location: "Aswan", meta: "Evening · Boat transfer included", rating: 4.6, reviews: 540, price: 25, unit: "person", partner: "GetYourGuide", cta: "View Tour", image: "1731024677293-2ca0c03bbf13" },
    ],
  },
  {
    id: "cruises",
    label: "Nile Cruises",
    icon: "i-ship",
    headline: "See Egypt from the river.",
    copy: "Five-star ships, intimate dahabiyas and the slow magic of watching temples appear from the water.",
    image: "1774223146816-8472905a40b8",
    compare: "Compare Cruises",
    trust: ["Full-board & guided visits", "Cabin-level comparison", "Best price from trusted lines"],
    items: [
      { name: "Luxor → Aswan, 4 nights", location: "5★ river ship", meta: "Full board · 5 temples", rating: 4.8, reviews: 1140, price: 420, unit: "person", partner: "Viator", cta: "View Cruise", image: "1778402153163-fc9a5fd0131a", badge: "Most booked" },
      { name: "Dahabiya sailing cruise, 5 nights", location: "Esna → Aswan", meta: "Only 8 cabins · Sails, not engines", rating: 4.9, reviews: 312, price: 980, unit: "person", partner: "GetYourGuide", cta: "View Cruise", image: "1770251693846-4167f4c049c6" },
      { name: "Lake Nasser to Abu Simbel, 3 nights", location: "Aswan → Abu Simbel", meta: "Remote temples · Sunset at Abu Simbel", rating: 4.8, reviews: 188, price: 760, unit: "person", partner: "Booking.com", cta: "Check Availability", image: "1502250663587-0437415428a2" },
    ],
  },
  {
    id: "transfers",
    label: "Transfers",
    icon: "i-route",
    headline: "Arrive. Move. Explore.",
    copy: "Fixed-price private transfers with English-speaking drivers — airport to hotel, city to coast, temple to temple.",
    image: "1774425329088-36801b6f09be",
    compare: "Compare Transfers",
    trust: ["Fixed prices, no haggling", "Flight tracking & free waiting", "Meet & greet in arrivals"],
    items: [
      { name: "Cairo Airport → Giza hotels", location: "Private sedan · up to 3", meta: "~55 min · Meet & greet", rating: 4.8, reviews: 2400, price: 28, unit: "car", partner: "Welcome Pickups", cta: "Book with Partner", image: "1679238211153-546821b75f98" },
      { name: "Hurghada Airport → El Gouna", location: "Private minivan · up to 6", meta: "~30 min · Child seats on request", rating: 4.9, reviews: 860, price: 22, unit: "car", partner: "Welcome Pickups", cta: "Book with Partner", image: "1722264219947-725d4e20ef23" },
      { name: "Luxor → Aswan scenic road trip", location: "Private car & driver", meta: "Stops at Edfu & Kom Ombo", rating: 4.8, reviews: 420, price: 95, unit: "car", partner: "GetYourGuide", cta: "View Deal", image: "1648139210599-aa27a54177a4" },
    ],
  },
  {
    id: "cars",
    label: "Car Rental",
    icon: "i-car",
    headline: "Take the road less traveled.",
    copy: "Self-drive the Red Sea coast or the oasis loop. Compare every rental desk at once, with full insurance options.",
    image: "1654676428515-94b5bf7a1ac4",
    compare: "Compare Cars",
    trust: ["Free cancellation up to 48h", "Full-coverage options", "No hidden fees at the desk"],
    items: [
      { name: "Compact · Hyundai Accent or similar", location: "Cairo Airport", meta: "Automatic · Unlimited km", rating: 4.5, reviews: 1320, price: 24, unit: "day", partner: "Rentalcars.com", cta: "Compare Options", image: "1499357526729-b2f0842540ce" },
      { name: "SUV · Toyota Fortuner or similar", location: "Hurghada", meta: "Automatic · 7 seats", rating: 4.6, reviews: 540, price: 58, unit: "day", partner: "DiscoverCars", cta: "Compare Options", image: "1624062999726-083e5268525d" },
      { name: "4×4 with driver · Western Desert", location: "Bahariya → Siwa", meta: "Camping gear available", rating: 4.9, reviews: 176, price: 120, unit: "day", partner: "Local partner", cta: "View Deal", image: "1640342105347-3e2699b5fbb3" },
    ],
  },
];

/* ------------------------------------------------------------------------ */
/* Travel guide (editorial)                                                  */
/* ------------------------------------------------------------------------ */
export const guides = [
  { id: "g-best-time", href: "/guide/best-time-to-visit-egypt", title: "The Best Time to Visit Egypt, Month by Month", cat: "Planning", read: 8, excerpt: "Winter sun on the temples, spring on the reefs, and the one month we'd always avoid. A season-by-season guide to getting the timing right.", image: "1762530162773-c99f38d4d5d3" },
  { id: "g-7-days", href: "/guide/7-days-in-egypt-itinerary", title: "7 Days in Egypt: The Perfect First Itinerary", cat: "Itineraries", read: 12, image: "1767797865870-0fd4d82608f7" },
  { id: "g-safe", href: "/guide/is-egypt-safe-for-tourists", title: "Is Egypt Safe for Tourists? An Honest 2026 Guide", cat: "Safety", read: 9, image: "1652022262085-d454346a353e" },
  { id: "g-cruises", href: "/guide/best-nile-cruises", title: "The Best Nile Cruises, Compared Cabin by Cabin", cat: "Cruises", read: 11, image: "1778402153163-fc9a5fd0131a" },
  { id: "g-cairo", href: "/guide/cairo-travel-guide", title: "Cairo Travel Guide: Beyond the Pyramids", cat: "Cities", read: 14, image: "1647108694450-67be2c64ba3e" },
  { id: "g-costs", href: "/guide/egypt-travel-costs", title: "What a Trip to Egypt Really Costs in 2026", cat: "Budget", read: 7, image: "1746274394124-141a1d1c5af3" },
  { id: "g-gems", href: "/guide/hidden-gems-in-egypt", title: "10 Hidden Gems Most Visitors Never See", cat: "Hidden Gems", read: 10, image: "1714229722648-3a90c3ef229f" },
];

/* ------------------------------------------------------------------------ */
/* Trip builder logic data                                                    */
/* ------------------------------------------------------------------------ */
export const routeOrder = ["cairo", "alexandria", "siwa", "luxor", "aswan", "hurghada", "sharm"];
export const nightWeights = { cairo: 3, alexandria: 1.5, siwa: 2.5, luxor: 2.5, aswan: 2, hurghada: 3, sharm: 3 };
export const legs = {
  "cairo>alexandria": "2h 30m drive",
  "alexandria>siwa": "8h desert road",
  "siwa>luxor": "Fly via Cairo",
  "cairo>luxor": "1h flight",
  "cairo>siwa": "Fly via Alexandria or 8h drive",
  "luxor>aswan": "4-night Nile cruise or 3h drive",
  "aswan>hurghada": "Fly via Cairo",
  "luxor>hurghada": "4h drive",
  "hurghada>sharm": "Fast ferry, 2h",
  "cairo>aswan": "1h 25m flight",
  "cairo>hurghada": "1h flight",
  "cairo>sharm": "1h flight",
  "aswan>sharm": "Fly via Cairo",
  "luxor>sharm": "Fly via Cairo",
  "alexandria>luxor": "Fly via Cairo",
};
export const styleRates = {
  smart: [90, 140],
  comfort: [170, 260],
  luxury: [420, 720],
};

/* ------------------------------------------------------------------------ */
/* "Watch the Film" — a cinematic sequence of scenes                          */
/* ------------------------------------------------------------------------ */
export const film = [
  { image: "1771325676184-44d8035e3cd1", kicker: "Chapter one", caption: "Where history still breathes." },
  { image: "1761205930594-64096d9d2baa", kicker: "Chapter two", caption: "A river that wrote a civilization." },
  { image: "1762530162773-c99f38d4d5d3", kicker: "Chapter three", caption: "Columns taller than time." },
  { image: "1655815226495-68d7d78894c4", kicker: "Chapter four", caption: "Silence, as far as you can see." },
  { image: "1682686581295-7364cabf5511", kicker: "Chapter five", caption: "Then, a different world." },
];
