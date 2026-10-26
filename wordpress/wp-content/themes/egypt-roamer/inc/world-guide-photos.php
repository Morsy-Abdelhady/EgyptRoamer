<?php
/**
 * Proposal 4: the photos inside each guide ("See the place"), by the guide's English slug and section anchor.
 * Free Unsplash photos whose location tag names the place, read on each photo page on 16-17 Oct
 * (docs/prototypes/journey-v4/guide-photos.py, v4-guides.py); recorded in docs/IMAGE-QUALITY-AUDIT-2026-10-02.md §7.
 * Each: [ Unsplash photo id, caption (English), photographer ].
 */

defined( 'ABSPATH' ) || exit;

return [
	// Place pages (26 Oct): Wikimedia Commons photos (owner, 26 Oct), credited by er_commons_credit().
	'temple-of-edfu' => [
		'what-you-will-see' => [ [ 'commons:Edfu Temple pylon from inner court.jpg', 'The court and the pylon, from inside the temple', '' ], [ 'commons:Edfu-Relief.jpg', 'A relief in the temple of Horus', '' ] ],
	],
	'temple-of-hatshepsut-deir-el-bahari' => [
		'what-you-will-see' => [ [ 'commons:Thebes, Luxor, Egypt, Temple of Hatshepsut, Deir el-Bahari.jpg', 'The temple under the cliffs of Deir el-Bahari', '' ], [ 'commons:Cavetto and Osiris statue, Mortuary Temple of Hatshepsut, Deir el-Bahari, Egypt - 20110516.jpg', 'A statue of Hatshepsut as Osiris', '' ] ],
	],
	'wadi-al-hitan-whale-valley' => [
		'what-it-is' => [ [ 'commons:The whales fossils at Wadi El-Hitan, Egypt 2017.jpg', 'A fossil whale skeleton at sunset', '' ], [ 'commons:Dorudon atrox fossil at Wadi El-Hitan, Egypt, March 2008.jpg', 'The fossil of a Dorudon whale', '' ] ],
		'wadi-el-rayan' => [ [ 'commons:Fennec FOX - Valley of the Whales - Wadi al Hitan 2.jpg', 'A fennec fox at Wadi Al-Hitan', '' ] ],
	],
	'kom-ombo-temple' => [
		'design' => [ [ 'commons:Temple of Sobek and Haroeris.jpg', 'The double entrance: Sobek on the right, Horus on the left', '' ], [ 'commons:Temple of Kom Ombo, Egypt.jpg', 'Columns and capitals of the temple', '' ] ],
	],
	'philae-temple-of-isis' => [
		'isis' => [ [ 'commons:27138- Philae.jpg', 'The temple of Isis, Philae', '' ] ],
		'what-you-will-see' => [ [ 'commons:Agilkia Trajankiosk 01.JPG', 'Trajan’s Kiosk on Agilkia island', '' ] ],
	],
	'wadi-el-gemal-national-park' => [
		'what-you-will-see' => [ [ 'commons:WadiElGimal3.jpg', 'An acacia tree in Wadi El-Gemal', '' ], [ 'commons:Sooty Gull in Ras Baghdadi - Wadi El-Gemal National Park, Red Sea 7.jpg', 'A sooty gull on the coast of the park', '' ] ],
	],
	'luxor-west-bank-temples-and-tombs' => [
		'deir-el-bahari' => [ [ '1669521762357-966bfdcbde1d', 'A balloon over Hatshepsut’s temple, Deir el-Bahari', 'Hongbin' ], [ '1683891043389-31a5ca0692f8', 'Hatshepsut’s temple, the statue terrace', 'Mahmoud Refaat' ] ],
		'medinet-habu' => [ [ '1622962522220-0d9e0d29d62d', 'Medinet Habu, the painted gateway', 'Siddhesh Mangela' ], [ '1762858279425-ed77c08d7a1c', 'Medinet Habu, the carved columns', 'Fatih Beki' ] ],
	],
	'mount-sinai-saint-catherine' => [
		'monastery' => [ [ '1603889947015-f3305fc399ea', 'Saint Catherine, South Sinai', 'Shady Hassan' ] ],
		'the-mountain' => [ [ '1661270288617-f3168c7a18be', 'The Sinai mountains from Mount Sinai', 'Artem Labunsky' ], [ '1628231354449-18c8bb735b23', 'Sunrise from Mount Sinai', 'Raimond Klavins' ], [ '1660430279942-a26bb4c7d4be', 'Walkers resting near the summit', 'Artem Labunsky' ] ],
	],
	'saqqara-and-dahshur-pyramids' => [
		'saqqara' => [ [ '1738512156462-408182814b21', 'The Step Pyramid of Djoser', 'Martijn Vonk' ], [ '1622962522206-7b7aa3dc5067', 'The colonnade of the Djoser complex', 'Siddhesh Mangela' ], [ '1708785033343-cd54bbb7d2cc', 'Painted reliefs in a Saqqara tomb', 'oCDr' ] ],
		'dahshur' => [ [ '1634464905162-68ba20cb9a06', 'The Bent Pyramid', 'Jens Aber' ], [ '1681403402826-c514f373a156', 'A pyramid at Dahshur', 'Jordi Orts Segalés' ] ],
	],
	'red-sea-famous-dive-sites' => [
		'ras-mohammed' => [ [ '1616967304670-b75cf9ab4582', 'Ras Mohammed National Park', 'Bogdan Nanescu' ] ],
		'before-you-book' => [ [ '1777551881568-50b1beab7d91', 'Small orange fish over a coral reef, Hurghada', 'Oliver Schwaneberg' ], [ '1777552955794-77aa990a47df', 'Two clownfish in a sea anemone, Hurghada', 'Oliver Schwaneberg' ], [ '1777552995899-b0c030a3ef92', 'A small pufferfish by the reef, Hurghada', 'Oliver Schwaneberg' ] ],
	],
	'coptic-cairo-guide' => [
		'babylon' => [ [ '1678388104119-18fcd08762f5', 'The walls of the Fortress of Babylon, Old Cairo', '2H Media' ] ],
		'hanging-church' => [ [ '1709485400031-6094ea4f0bad', 'The Hanging Church and its twin towers', 'Dilip Poddar' ], [ '1666041382402-f428986aea0a', 'The steps up to the Hanging Church', 'Moaz Elsayed' ], [ '1678747365854-a0a68fd24725', 'Wooden screens inside the Hanging Church', '2H Media' ] ],
	],
	'inside-the-pyramids-of-giza' => [
		'the-pyramids' => [ [ '1761561044700-4262beec7d63', 'The three pyramids of Giza', 'Thomas K' ], [ '1603889844276-06c9d85b7594', 'The pyramid of Menkaure', 'Shady Hassan' ], [ '1760650454610-0a4c9a5d0bb6', 'Camels before the pyramids', 'Fatih Beki' ] ],
	],
	'great-sand-sea' => [
		'what-it-is' => [ [ '1612442494156-a5fd9a1c3602', 'A lake at Siwa Oasis', 'Omar Mohamed' ] ],
		'safari' => [ [ '1689189830710-1cb48a6efbe3', 'Sandboards ready by a 4×4, Siwa', 'Faqih Abdul' ] ],
	],
	'grand-egyptian-museum-visit' => [
		'with-the-pyramids' => [ [ '1761688179162-4c42356857ee', 'The pyramids of Giza', 'Fatih Beki' ] ],
		'what-you-will-see' => [ [ '1767938072548-112727a13266', 'Tutankhamun’s golden mask', 'Suzi Kim' ], [ '1777189978576-08b21689fcc9', 'A colossal statue in the museum’s hall', 'Aleksei Filimonov' ] ],
	],
	'karnak-and-luxor-temple' => [
		'karnak' => [ [ '1662655558673-4e628102f545', 'The ram-headed sphinxes, Karnak', '2H Media' ], [ '1581248708422-db978024f382', 'Karnak from the sacred lake', 'Kristina' ], [ 'commons:134 Säulen in 16 Reihen geordnet trugen das Dach der Halle. 12.jpg', 'The Great Hypostyle Hall of Karnak', '' ] ],
		'luxor-temple' => [ [ '1693654547147-24d94b4ed4ea', 'A colossal statue, Luxor Temple', 'Hongbin' ], [ '1610331800877-462a21600420', 'The colonnade at sunset', 'Artem Bryzgalov' ], [ '1596130535979-eedea4278d29', 'The pylon and obelisk', 'Artem Bryzgalov' ] ],
	],
	'14-days-in-egypt-itinerary' => [
		'at-a-glance' => [ [ '1761561044700-4262beec7d63', 'Giza', 'Thomas K' ], [ '1601816500593-8f1276479ea6', 'Alexandria', 'George Youssef' ], [ '1610331800877-462a21600420', 'Luxor Temple', 'Artem Bryzgalov' ], [ '1738580426867-03fa8c8b5288', 'Philae, Aswan', 'Martijn Vonk' ] ],
	],
	'siwa-or-white-desert' => [
		'white-desert' => [ [ '1691591403777-128b9a842b8b', 'The White Desert', '' ] ],
		'siwa' => [ [ '1758720944577-559b852e8493', 'The mud-brick old town, Siwa', 'kai muro' ], [ '1584114130913-0852aeb8fe8c', 'Sunset over the old town', 'Flo P' ], [ '1612442494156-a5fd9a1c3602', 'A lake at Siwa Oasis', 'Omar Mohamed' ] ],
	],
	'valley-of-the-kings-tombs-and-tickets' => [
		'what-it-is' => [ [ 'commons:King valley.jpg', 'The East Valley of the Kings', '' ] ],
		'west-bank' => [ [ '1683891043389-31a5ca0692f8', 'Hatshepsut’s temple, Deir el-Bahari', 'Mahmoud Refaat' ], [ '1622962522220-0d9e0d29d62d', 'Medinet Habu, the painted gateway', 'Siddhesh Mangela' ], [ '1762858279425-ed77c08d7a1c', 'Medinet Habu, the carved columns', 'Fatih Beki' ] ],
	],
	'cairo-at-night' => [
		'dinner-cruise' => [ [ '1626692880062-35c360fb6afc', 'Boats on the Nile, Cairo', 'Roaming Pictures' ] ],
		'felucca' => [ [ '1674724545219-29637e41ae04', 'The Nile at sunset by Cairo Tower', 'Ahmed Assem' ] ],
		'street-food' => [ [ '1521791697570-e1f13d0b81d0', 'Baladi bread, Cairo', 'Alex Block' ], [ '1590657140810-25898a847305', 'Egyptian biscuits, Cairo', 'mohmed Afify' ] ],
		'old-city' => [ [ '1707172889437-dc6f210ea44a', 'A gateway in Khan el-Khalili', 'Thales Botelho de Sousa' ], [ '1760973519064-fc146406c871', 'The minarets of Al-Azhar', 'Fatih Beki' ] ],
	],
	'temples-of-aswan-and-lake-nasser' => [
		'aswan' => [ [ '1738580426867-03fa8c8b5288', 'The temple of Philae', 'Martijn Vonk' ], [ '1633033254409-bd538e785f51', 'Philae from the water', 'Dmitrii Zhodzishskii' ], [ '1775225819853-1dc8c22cf4d1', 'Feluccas at sunset, Aswan', 'Jean Pierre Cabáni' ] ],
		'north' => [ [ '1660705630675-d66d60fdca46', 'The temple of Horus, Edfu', 'mana5280' ], [ '1681403419483-a30ff02a385f', 'Light in the temple of Horus', 'Jordi Orts Segalés' ] ],
		'abu-simbel' => [ [ '1502250493741-939d1c76eaad', 'The Great Temple, Abu Simbel', 'AussieActive' ], [ '1633163973863-d44691f969b4', 'The sanctuary, Abu Simbel', 'Dmitrii Zhodzishskii' ] ],
	],
	'10-days-in-egypt-itinerary' => [
		'at-a-glance' => [ [ '1761561044700-4262beec7d63', 'Giza', 'Thomas K' ], [ '1610331800877-462a21600420', 'Luxor Temple', 'Artem Bryzgalov' ], [ '1502250493741-939d1c76eaad', 'Abu Simbel', 'AussieActive' ], [ '1722264222007-3e4f1808db3e', 'The coast at Hurghada', 'Unsplash' ] ],
	],
	'white-desert-night' => [
		'route' => [ [ '1691591403777-128b9a842b8b', 'The White Desert', '' ] ],
	],
	'snorkelling-hurghada-or-sharm' => [
		'sharm' => [ [ '1681158077449-77f23f629f0d', 'A bay at Sharm El Sheikh', '' ] ],
		'hurghada' => [ [ '1777551881568-50b1beab7d91', 'Small orange fish over a coral reef, Hurghada', 'Oliver Schwaneberg' ], [ '1777552955794-77aa990a47df', 'Two clownfish in a sea anemone, Hurghada', 'Oliver Schwaneberg' ], [ '1777552771426-9ac9f515dc40', 'Yellow fish on a coral reef, Hurghada', 'Oliver Schwaneberg' ], [ '1777552995899-b0c030a3ef92', 'A small pufferfish by the reef, Hurghada', 'Oliver Schwaneberg' ] ],
	],
	'nile-cruise-or-hotels-luxor-aswan' => [
		'cruise' => [ [ '1778402153163-fc9a5fd0131a', 'A cruise ship on the Nile, Aswan', 'Kuan L' ] ],
		'smaller-boats' => [ [ '1770251693846-4167f4c049c6', 'A felucca on the Nile, Luxor', 'Anthony Chen' ] ],
		'temples' => [ [ '1660705630675-d66d60fdca46', 'The temple of Horus, Edfu', 'mana5280' ], [ '1738580426867-03fa8c8b5288', 'Philae, Aswan', 'Martijn Vonk' ] ],
	],
	'day-trips-from-cairo' => [
		'giza' => [ [ '1761688179162-4c42356857ee', 'The pyramids of Giza', 'Fatih Beki' ] ],
		'saqqara' => [ [ '1738512156462-408182814b21', 'The Step Pyramid of Djoser, Saqqara', 'Martijn Vonk' ] ],
		'alexandria' => [ [ '1633624646814-3a61ed3e2847', 'Qaitbay Citadel', 'Juan Nino' ], [ '1594808815295-52034d585f56', 'Fishing boats in the Eastern Harbour', 'Flo P' ], [ '1668647014988-fc7434eb6e01', 'A tram in Alexandria', 'Noel Schläfli' ] ],
	],
	'abu-simbel-from-aswan' => [
		'rescue' => [ [ '1702909171830-2c4dca2ac090', 'Abu Simbel', 'Michael Starkie' ] ],
		'what-you-will-see' => [ [ '1502250493741-939d1c76eaad', 'The Great Temple of Ramesses II', 'AussieActive' ], [ '1649775827146-1d67524b9465', 'The colossi, close up', 'Jorge Fernández Salas' ], [ '1633163973863-d44691f969b4', 'The sanctuary', 'Dmitrii Zhodzishskii' ] ],
	],
	'best-time-to-visit-egypt' => [
		'regions' => [ [ '1610331800877-462a21600420', 'Luxor', 'Artem Bryzgalov' ], [ '1722264222007-3e4f1808db3e', 'Hurghada', 'Unsplash' ], [ '1612442494156-a5fd9a1c3602', 'Siwa Oasis', 'Omar Mohamed' ], [ '1601816500593-8f1276479ea6', 'Alexandria', 'George Youssef' ] ],
	],
	'7-days-in-egypt-itinerary' => [
		'at-a-glance' => [ [ '1761561044700-4262beec7d63', 'Giza', 'Thomas K' ], [ '1610331800877-462a21600420', 'Luxor Temple', 'Artem Bryzgalov' ], [ '1738580426867-03fa8c8b5288', 'Philae, Aswan', 'Martijn Vonk' ] ],
	],
	'cairo-travel-guide' => [
		'on-the-river' => [ [ '1674724545219-29637e41ae04', 'The Nile by Cairo Tower', 'Ahmed Assem' ] ],
		'old-city-walk' => [ [ '1760973519064-fc146406c871', 'Al-Azhar Mosque', 'Fatih Beki' ], [ '1707172889437-dc6f210ea44a', 'Khan el-Khalili', 'Thales Botelho de Sousa' ], [ '1595979904086-471704dc0e81', 'The Al-Rifa’i and Sultan Hassan mosques', 'Omar Adel' ] ],
	],
	'hidden-gems-in-egypt' => [
		'near-cairo' => [ [ '1634464905162-68ba20cb9a06', 'The Bent Pyramid, Dahshur', 'Jens Aber' ], [ '1738512156462-408182814b21', 'The Step Pyramid, Saqqara', 'Martijn Vonk' ] ],
		'nile-valley' => [ [ '1622962522220-0d9e0d29d62d', 'Medinet Habu, the painted gateway', 'Siddhesh Mangela' ], [ '1660705630675-d66d60fdca46', 'Edfu', 'mana5280' ] ],
		'north-and-west' => [ [ '1758720944577-559b852e8493', 'The old town, Siwa', 'kai muro' ] ],
	],
];
