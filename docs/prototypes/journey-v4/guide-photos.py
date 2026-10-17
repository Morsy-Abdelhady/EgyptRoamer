# Photos for every guide (owner, 17 Oct: "you added photos to the template guides only").
# All free Unsplash photos whose location tag names the place, read on each photo page on 17 Oct, and looked at one
# by one. Captions say what the tag and the picture show; credits are the photographers'.
P = {  # id: (tag as read, photographer)
 '1669521762357-966bfdcbde1d': ('Temple of Hatshepsut, Kurna', 'Hongbin'),
 '1661270288617-f3168c7a18be': ('Mount Sinai, Saint Catherine', 'Artem Labunsky'),
 '1628231354449-18c8bb735b23': ('Mount Sinai, Saint Catherine', 'Raimond Klavins'),
 '1660430279942-a26bb4c7d4be': ('Mount Sinai, Saint Catherine', 'Artem Labunsky'),
 '1603889947015-f3305fc399ea': ('Saint Catherine', 'Shady Hassan'),
 '1622962522206-7b7aa3dc5067': ('Pyramid of Djoser', 'Siddhesh Mangela'),
 '1738512156462-408182814b21': ('Pyramid of Djoser', 'Martijn Vonk'),
 '1708785033343-cd54bbb7d2cc': ('Saqqara', 'oCDr'),
 '1634464905162-68ba20cb9a06': ('Bent Pyramid', 'Jens Aber'),
 '1681403402826-c514f373a156': ('Dahshur', 'Jordi Orts Segalés'),
 '1678388104119-18fcd08762f5': ('Fortress of Babylon, Old Cairo', '2H Media'),
 '1678747365854-a0a68fd24725': ('The Hanging Church, Old Cairo', '2H Media'),
 '1709485400031-6094ea4f0bad': ('The Hanging Church, Old Cairo', 'Dilip Poddar'),
 '1666041382402-f428986aea0a': ('The Hanging Church, Old Cairo', 'Moaz Elsayed'),
 '1761561044700-4262beec7d63': ('Giza Pyramid Complex', 'Thomas K'),
 '1603889844276-06c9d85b7594': ('Pyramid of Menkaure', 'Shady Hassan'),
 '1760650454610-0a4c9a5d0bb6': ('Pyramids Gardens, Giza', 'Fatih Beki'),
 '1761688179162-4c42356857ee': ('Pyramids of Giza', 'Fatih Beki'),
 '1689189830710-1cb48a6efbe3': ('Siwa', 'Faqih Abdul'),
 '1612442494156-a5fd9a1c3602': ('Siwa Oasis', 'Omar Mohamed'),
 '1767938072548-112727a13266': ('Grand Egyptian Museum', 'Suzi Kim'),
 '1662655558673-4e628102f545': ('Karnak Temples, Luxor', '2H Media'),
 '1581248708422-db978024f382': ('Karnak, Luxor', 'Kristina'),
 '1693654547147-24d94b4ed4ea': ('Luxor Temple', 'Hongbin'),
 '1610331800877-462a21600420': ('Luxor Temple', 'Artem Bryzgalov'),
 '1596130535979-eedea4278d29': ('Luxor Temple', 'Artem Bryzgalov'),
 '1584114130913-0852aeb8fe8c': ('Siwa Oasis', 'Flo P'),
 '1758720944577-559b852e8493': ('Siwa', 'kai muro'),
 '1626692880062-35c360fb6afc': ('Cairo', 'Roaming Pictures'),
 '1707172889437-dc6f210ea44a': ('Khan el-Khalili, Cairo', 'Thales Botelho de Sousa'),
 '1760973519064-fc146406c871': ('Al-Azhar Mosque, Cairo', 'Fatih Beki'),
 '1674724545219-29637e41ae04': ('Cairo Tower, Zamalek', 'Ahmed Assem'),
 '1633033254409-bd538e785f51': ('Philae, Aswan', 'Dmitrii Zhodzishskii'),
 '1738580426867-03fa8c8b5288': ('Philae, Aswan', 'Martijn Vonk'),
 '1775225819853-1dc8c22cf4d1': ('Aswan', 'Jean Pierre Cabáni'),
 '1660705630675-d66d60fdca46': ('Edfu', 'mana5280'),
 '1681403419483-a30ff02a385f': ('The Temple of Horus at Edfu', 'Jordi Orts Segalés'),
 '1649775827146-1d67524b9465': ('Abu Simbel', 'Jorge Fernández Salas'),
 '1633163973863-d44691f969b4': ('Abu Simbel', 'Dmitrii Zhodzishskii'),
 '1502250493741-939d1c76eaad': ('Abu Simbel Temples', 'AussieActive'),
 '1702909171830-2c4dca2ac090': ('Abu Simbel', 'Michael Starkie'),
 '1778402153163-fc9a5fd0131a': ('Aswan', 'Kuan L'),
 '1770251693846-4167f4c049c6': ('Luxor', 'Anthony Chen'),
 '1594808815295-52034d585f56': ('Alexandria', 'Flo P'),
 '1668647014988-fc7434eb6e01': ('Alexandria', 'Noel Schläfli'),
 '1601816500593-8f1276479ea6': ('Alexandria', 'George Youssef'),
 '1595979904086-471704dc0e81': ('Al-Rifa’i Mosque, Cairo', 'Omar Adel'),
}

def ph(i, cap):
    return ['img/%s.jpg' % i, cap, P[i][1]]

def fig(i, cap):
    return ['img/%s.jpg' % i, '%s · Photo: %s, Unsplash' % (cap, P[i][1])]

HAB1 = ['habu1', 'Medinet Habu, the painted gateway', 'Siddhesh Mangela']
HAB2 = ['habu2', 'Medinet Habu, the carved columns', 'Fatih Beki']
EXTRA = {
 'luxor-west-bank-temples-and-tombs': dict(
     strips={'deir-el-bahari': ['See the place', [ph('1669521762357-966bfdcbde1d', 'A balloon over Hatshepsut’s temple, Deir el-Bahari'), ['hat_l', 'Hatshepsut’s temple, the statue terrace', 'Mahmoud Refaat']]],
             'medinet-habu': ['Medinet Habu', [HAB1, HAB2]]}),
 'mount-sinai-saint-catherine': dict(
     strips={'the-mountain': ['On the mountain', [ph('1661270288617-f3168c7a18be', 'The Sinai mountains from Mount Sinai'), ph('1628231354449-18c8bb735b23', 'Sunrise from Mount Sinai'), ph('1660430279942-a26bb4c7d4be', 'Walkers resting near the summit')]]},
     figs={'monastery': fig('1603889947015-f3305fc399ea', 'Saint Catherine, South Sinai')}),
 'saqqara-and-dahshur-pyramids': dict(
     strips={'saqqara': ['Saqqara', [ph('1738512156462-408182814b21', 'The Step Pyramid of Djoser'), ph('1622962522206-7b7aa3dc5067', 'The colonnade of the Djoser complex'), ph('1708785033343-cd54bbb7d2cc', 'Painted reliefs in a Saqqara tomb')]],
             'dahshur': ['Dahshur', [ph('1634464905162-68ba20cb9a06', 'The Bent Pyramid'), ph('1681403402826-c514f373a156', 'A pyramid at Dahshur')]]}),
 'red-sea-famous-dive-sites': dict(
     figs={'ras-mohammed': ['ras_turq', 'Ras Mohammed National Park · Photo: Bogdan Nanescu, Unsplash']},
     strips={'before-you-book': ['On the reefs', [['reef_school', 'Small orange fish over a coral reef, Hurghada', 'Oliver Schwaneberg'], ['reef_clown2', 'Two clownfish in a sea anemone, Hurghada', 'Oliver Schwaneberg'], ['reef_puffer', 'A small pufferfish by the reef, Hurghada', 'Oliver Schwaneberg']]]}),
 'coptic-cairo-guide': dict(
     figs={'babylon': fig('1678388104119-18fcd08762f5', 'The walls of the Fortress of Babylon, Old Cairo')},
     strips={'hanging-church': ['The Hanging Church', [ph('1709485400031-6094ea4f0bad', 'The Hanging Church and its twin towers'), ph('1666041382402-f428986aea0a', 'The steps up to the Hanging Church'), ph('1678747365854-a0a68fd24725', 'Wooden screens inside the Hanging Church')]]}),
 'inside-the-pyramids-of-giza': dict(
     strips={'the-pyramids': ['The plateau', [ph('1761561044700-4262beec7d63', 'The three pyramids of Giza'), ph('1603889844276-06c9d85b7594', 'The pyramid of Menkaure'), ph('1760650454610-0a4c9a5d0bb6', 'Camels before the pyramids')]]}),
 'great-sand-sea': dict(
     figs={'what-it-is': fig('1612442494156-a5fd9a1c3602', 'A lake at Siwa Oasis'), 'safari': fig('1689189830710-1cb48a6efbe3', 'Sandboards ready by a 4×4, Siwa')}),
 'grand-egyptian-museum-visit': dict(
     strips={'what-you-will-see': ['In the museum', [ph('1767938072548-112727a13266', 'Tutankhamun’s golden mask'), ['gem', 'A colossal statue in the museum’s hall', 'Aleksei Filimonov']]]},
     figs={'with-the-pyramids': fig('1761688179162-4c42356857ee', 'The pyramids of Giza')}),
 'karnak-and-luxor-temple': dict(
     strips={'karnak': ['Karnak', [ph('1662655558673-4e628102f545', 'The ram-headed sphinxes, Karnak'), ph('1581248708422-db978024f382', 'Karnak from the sacred lake')]],
             'luxor-temple': ['Luxor Temple', [ph('1693654547147-24d94b4ed4ea', 'A colossal statue, Luxor Temple'), ph('1610331800877-462a21600420', 'The colonnade at sunset'), ph('1596130535979-eedea4278d29', 'The pylon and obelisk')]]}),
 '14-days-in-egypt-itinerary': dict(
     strips={'at-a-glance': ['On the route', [ph('1761561044700-4262beec7d63', 'Giza'), ph('1601816500593-8f1276479ea6', 'Alexandria'), ph('1610331800877-462a21600420', 'Luxor Temple'), ph('1738580426867-03fa8c8b5288', 'Philae, Aswan')]]}),
 'siwa-or-white-desert': dict(
     strips={'siwa': ['Siwa', [ph('1758720944577-559b852e8493', 'The mud-brick old town, Siwa'), ph('1584114130913-0852aeb8fe8c', 'Sunset over the old town'), ph('1612442494156-a5fd9a1c3602', 'A lake at Siwa Oasis')]]},
     figs={'white-desert': ['desert_l', 'The White Desert · Photo: Unsplash']}),
 'cairo-at-night': dict(
     figs={'dinner-cruise': fig('1626692880062-35c360fb6afc', 'Boats on the Nile, Cairo'), 'felucca': fig('1674724545219-29637e41ae04', 'The Nile at sunset by Cairo Tower')},
     strips={'street-food': ['Street food', [['f_bread', 'Baladi bread, Cairo', 'Alex Block'], ['f_sweets', 'Egyptian biscuits, Cairo', 'mohmed Afify']]],
             'old-city': ['The old city', [ph('1707172889437-dc6f210ea44a', 'A gateway in Khan el-Khalili'), ph('1760973519064-fc146406c871', 'The minarets of Al-Azhar')]]}),
 'temples-of-aswan-and-lake-nasser': dict(
     strips={'aswan': ['In and around Aswan', [ph('1738580426867-03fa8c8b5288', 'The temple of Philae'), ph('1633033254409-bd538e785f51', 'Philae from the water'), ph('1775225819853-1dc8c22cf4d1', 'Feluccas at sunset, Aswan')]],
             'north': ['Edfu', [ph('1660705630675-d66d60fdca46', 'The temple of Horus, Edfu'), ph('1681403419483-a30ff02a385f', 'Light in the temple of Horus')]],
             'abu-simbel': ['Abu Simbel', [ph('1502250493741-939d1c76eaad', 'The Great Temple, Abu Simbel'), ph('1633163973863-d44691f969b4', 'The sanctuary, Abu Simbel')]]}),
 '10-days-in-egypt-itinerary': dict(
     strips={'at-a-glance': ['On the route', [ph('1761561044700-4262beec7d63', 'Giza'), ph('1610331800877-462a21600420', 'Luxor Temple'), ph('1502250493741-939d1c76eaad', 'Abu Simbel'), ['d_hurghada', 'The coast at Hurghada', 'Unsplash']]]}),
 'white-desert-night': dict(figs={'route': ['desert_l', 'The White Desert · Photo: Unsplash']}),
 'nile-cruise-or-hotels-luxor-aswan': dict(
     figs={'cruise': fig('1778402153163-fc9a5fd0131a', 'A cruise ship on the Nile, Aswan'), 'smaller-boats': fig('1770251693846-4167f4c049c6', 'A felucca on the Nile, Luxor')},
     strips={'temples': ['On the way', [ph('1660705630675-d66d60fdca46', 'The temple of Horus, Edfu'), ph('1738580426867-03fa8c8b5288', 'Philae, Aswan')]]}),
 'day-trips-from-cairo': dict(
     figs={'giza': fig('1761688179162-4c42356857ee', 'The pyramids of Giza'), 'saqqara': fig('1738512156462-408182814b21', 'The Step Pyramid of Djoser, Saqqara')},
     strips={'alexandria': ['Alexandria', [['alex', 'Qaitbay Citadel', 'Juan Nino'], ph('1594808815295-52034d585f56', 'Fishing boats in the Eastern Harbour'), ph('1668647014988-fc7434eb6e01', 'A tram in Alexandria')]]}),
 'abu-simbel-from-aswan': dict(
     strips={'what-you-will-see': ['The temples', [ph('1502250493741-939d1c76eaad', 'The Great Temple of Ramesses II'), ph('1649775827146-1d67524b9465', 'The colossi, close up'), ph('1633163973863-d44691f969b4', 'The sanctuary')]]},
     figs={'rescue': fig('1702909171830-2c4dca2ac090', 'Abu Simbel')}),
 'best-time-to-visit-egypt': dict(
     strips={'regions': ['Region by region', [ph('1610331800877-462a21600420', 'Luxor'), ['d_hurghada', 'Hurghada', 'Unsplash'], ph('1612442494156-a5fd9a1c3602', 'Siwa Oasis'), ph('1601816500593-8f1276479ea6', 'Alexandria')]]}),
 '7-days-in-egypt-itinerary': dict(
     strips={'at-a-glance': ['On the route', [ph('1761561044700-4262beec7d63', 'Giza'), ph('1610331800877-462a21600420', 'Luxor Temple'), ph('1738580426867-03fa8c8b5288', 'Philae, Aswan')]]}),
 'cairo-travel-guide': dict(
     strips={'old-city-walk': ['The old city', [ph('1760973519064-fc146406c871', 'Al-Azhar Mosque'), ph('1707172889437-dc6f210ea44a', 'Khan el-Khalili'), ph('1595979904086-471704dc0e81', 'The Al-Rifa’i and Sultan Hassan mosques')]]},
     figs={'on-the-river': fig('1674724545219-29637e41ae04', 'The Nile by Cairo Tower')}),
 'hidden-gems-in-egypt': dict(
     strips={'near-cairo': ['Near Cairo', [ph('1634464905162-68ba20cb9a06', 'The Bent Pyramid, Dahshur'), ph('1738512156462-408182814b21', 'The Step Pyramid, Saqqara')]],
             'nile-valley': ['In the Nile Valley', [HAB1, ph('1660705630675-d66d60fdca46', 'Edfu')]],
             'north-and-west': ['North and west', [ph('1758720944577-559b852e8493', 'The old town, Siwa')]]}),
}
