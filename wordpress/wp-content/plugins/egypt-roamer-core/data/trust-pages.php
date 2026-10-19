<?php
/**
 * The About and Sources pages (owner, 20 Oct: "who is Egypt Roamer, why trust it"; every guide promises a sources
 * page). Every sentence states how the site works, as in CLAUDE.md and docs/CONTENT-SOURCES.md; nothing else.
 * Created once in each language by includes/migrations.php. The Sources page's list comes from data/sources.json.
 * Translations not yet reviewed by native speakers.
 */

defined( 'ABSPATH' ) || exit;

$p = static fn ( array $lines ): string => implode( "\n\n", array_map( static fn ( $l ) => 0 === strpos( $l, '<' ) ? $l : '<!-- wp:paragraph --><p>' . $l . '</p><!-- /wp:paragraph -->', $lines ) );
$h = static fn ( string $t ): string => '<!-- wp:heading --><h2 class="wp-block-heading">' . $t . '</h2><!-- /wp:heading -->';
$l = static fn ( array $items ): string => '<!-- wp:list --><ul class="wp-block-list">' . implode( '', array_map( static fn ( $i ) => '<!-- wp:list-item --><li>' . $i . '</li><!-- /wp:list-item -->', $items ) ) . '</ul><!-- /wp:list -->';

return [
	'about' => [
		'en' => [ 'about', 'About Egypt Roamer', $p( [
			'Egypt Roamer is an independent travel publication about Egypt, in eight languages. We write about Egypt’s places, trips and seasons, and each guide links to the trips it talks about.',
			$h( 'How we work' ),
			$l( [
				'We take no bookings and no payments. Each “Book” button opens the partner’s own site (Viator, GetYourGuide or Trip.com), where you choose your date and see the final price.',
				'When you book through these links we may earn a small commission, at no extra cost to you. It never decides what we recommend.',
				'Our guides are written from official and published sources, such as UNESCO, Egypt’s Ministry of Tourism and Antiquities and Britannica. Each fact is listed on our sources page.',
				'We leave out what goes stale and can’t be checked here, such as opening hours and ticket prices, and ask you to check them on the day.',
				'The ratings, reviews counts and prices on trip options are the partner’s own, read from the partner.',
			] ),
		] ) ],
		'de' => [ 'ueber-uns', 'Über Egypt Roamer', $p( [
			'Egypt Roamer ist ein unabhängiges Reisemagazin über Ägypten, in acht Sprachen. Wir schreiben über Ägyptens Orte, Touren und Jahreszeiten, und jeder Guide verlinkt die Touren, über die er spricht.',
			$h( 'So arbeiten wir' ),
			$l( [
				'Wir nehmen keine Buchungen und keine Zahlungen entgegen. Jeder „Buchen“-Knopf öffnet die Seite des Partners (Viator, GetYourGuide oder Trip.com), wo Sie Ihr Datum wählen und den endgültigen Preis sehen.',
				'Wenn Sie über diese Links buchen, erhalten wir möglicherweise eine kleine Provision, ohne Mehrkosten für Sie. Sie entscheidet nie, was wir empfehlen.',
				'Unsere Guides beruhen auf offiziellen und veröffentlichten Quellen wie der UNESCO, dem ägyptischen Ministerium für Tourismus und Altertümer und der Britannica. Jede Angabe steht auf unserer Quellenseite.',
				'Was schnell veraltet und sich hier nicht prüfen lässt, etwa Öffnungszeiten und Ticketpreise, lassen wir weg und bitten Sie, es am Tag selbst zu prüfen.',
				'Bewertungen, Anzahl der Rezensionen und Preise bei den Angeboten stammen vom Partner selbst.',
			] ),
		] ) ],
		'fr' => [ 'a-propos', 'À propos d’Egypt Roamer', $p( [
			'Egypt Roamer est une publication de voyage indépendante sur l’Égypte, en huit langues. Nous écrivons sur les lieux, les excursions et les saisons de l’Égypte, et chaque guide renvoie aux excursions dont il parle.',
			$h( 'Notre façon de travailler' ),
			$l( [
				'Nous ne prenons ni réservations ni paiements. Chaque bouton « Réserver » ouvre le site du partenaire (Viator, GetYourGuide ou Trip.com), où vous choisissez votre date et voyez le prix final.',
				'Lorsque vous réservez via ces liens, nous pouvons toucher une petite commission, sans frais supplémentaires pour vous. Elle ne décide jamais de nos recommandations.',
				'Nos guides s’appuient sur des sources officielles et publiées, comme l’UNESCO, le ministère égyptien du Tourisme et des Antiquités et Britannica. Chaque fait figure sur notre page de sources.',
				'Nous laissons de côté ce qui se périme vite et ne peut être vérifié ici, comme les horaires et les prix des billets, et vous invitons à les vérifier le jour même.',
				'Les notes, le nombre d’avis et les prix des options sont ceux du partenaire, lus chez le partenaire.',
			] ),
		] ) ],
		'it' => [ 'chi-siamo', 'Chi siamo', $p( [
			'Egypt Roamer è una pubblicazione di viaggio indipendente sull’Egitto, in otto lingue. Scriviamo dei luoghi, delle escursioni e delle stagioni dell’Egitto, e ogni guida rimanda alle escursioni di cui parla.',
			$h( 'Come lavoriamo' ),
			$l( [
				'Non accettiamo prenotazioni né pagamenti. Ogni pulsante «Prenota» apre il sito del partner (Viator, GetYourGuide o Trip.com), dove scegliete la data e vedete il prezzo finale.',
				'Quando prenotate tramite questi link potremmo ricevere una piccola commissione, senza costi aggiuntivi per voi. Non decide mai cosa consigliamo.',
				'Le nostre guide si basano su fonti ufficiali e pubblicate, come l’UNESCO, il Ministero egiziano del Turismo e delle Antichità e Britannica. Ogni dato è elencato nella nostra pagina delle fonti.',
				'Tralasciamo ciò che invecchia in fretta e qui non si può verificare, come orari e prezzi dei biglietti, e vi chiediamo di controllarlo il giorno stesso.',
				'Valutazioni, numero di recensioni e prezzi delle opzioni sono quelli del partner, letti dal partner.',
			] ),
		] ) ],
		'es' => [ 'sobre-nosotros', 'Sobre Egypt Roamer', $p( [
			'Egypt Roamer es una publicación de viajes independiente sobre Egipto, en ocho idiomas. Escribimos sobre los lugares, las excursiones y las estaciones de Egipto, y cada guía enlaza las excursiones de las que habla.',
			$h( 'Cómo trabajamos' ),
			$l( [
				'No aceptamos reservas ni pagos. Cada botón «Reservar» abre la web del socio (Viator, GetYourGuide o Trip.com), donde eliges la fecha y ves el precio final.',
				'Cuando reservas a través de estos enlaces podemos recibir una pequeña comisión, sin coste adicional para ti. Nunca decide lo que recomendamos.',
				'Nuestras guías se basan en fuentes oficiales y publicadas, como la UNESCO, el Ministerio de Turismo y Antigüedades de Egipto y Britannica. Cada dato figura en nuestra página de fuentes.',
				'Dejamos fuera lo que caduca pronto y no se puede comprobar aquí, como horarios y precios de entradas, y te pedimos que lo compruebes el mismo día.',
				'Las valoraciones, el número de opiniones y los precios de las opciones son del socio, leídos del socio.',
			] ),
		] ) ],
		'ru' => [ 'o-proekte', 'О проекте Egypt Roamer', $p( [
			'Egypt Roamer — независимое издание о путешествиях по Египту на восьми языках. Мы пишем о местах, поездках и сезонах Египта, и каждый путеводитель ведёт к поездкам, о которых рассказывает.',
			$h( 'Как мы работаем' ),
			$l( [
				'Мы не принимаем бронирования и платежи. Каждая кнопка «Забронировать» открывает сайт партнёра (Viator, GetYourGuide или Trip.com), где вы выбираете дату и видите окончательную цену.',
				'Когда вы бронируете по этим ссылкам, мы можем получить небольшую комиссию — без доплаты с вашей стороны. Она никогда не определяет, что мы рекомендуем.',
				'Наши путеводители написаны по официальным и опубликованным источникам, таким как ЮНЕСКО, Министерство туризма и древностей Египта и Britannica. Каждый факт указан на нашей странице источников.',
				'Мы не публикуем то, что быстро устаревает и не может быть проверено здесь, например часы работы и цены билетов, и просим уточнять это в день визита.',
				'Оценки, число отзывов и цены вариантов — данные самого партнёра.',
			] ),
		] ) ],
		'zh' => [ 'about-zh', '关于 Egypt Roamer', $p( [
			'Egypt Roamer 是一家关于埃及的独立旅行出版物，提供八种语言。我们介绍埃及的地点、行程和季节，每篇指南都链接到其中提到的行程。',
			$h( '我们的工作方式' ),
			$l( [
				'我们不接受预订，也不收取任何付款。每个“预订”按钮都会打开合作伙伴的网站（Viator、GetYourGuide 或 Trip.com），您在那里选择日期并查看最终价格。',
				'您通过这些链接预订时，我们可能获得少量佣金，您无需支付额外费用。佣金从不决定我们的推荐。',
				'我们的指南依据官方和已出版的资料撰写，例如联合国教科文组织、埃及旅游和文物部以及大英百科全书。每条信息都列在我们的资料来源页面上。',
				'我们不写容易过时、在此无法核实的内容，例如开放时间和门票价格，请您当天确认。',
				'行程选项上的评分、评价数量和价格均来自合作伙伴本身。',
			] ),
		] ) ],
		'ar' => [ 'an-egypt-roamer', 'عن Egypt Roamer', $p( [
			'Egypt Roamer منصة سفر مستقلة عن مصر، بثماني لغات. نكتب عن أماكن مصر ورحلاتها ومواسمها، وكل دليل يربط بالرحلات التي يتحدث عنها.',
			$h( 'كيف نعمل' ),
			$l( [
				'لا نستقبل حجوزات ولا مدفوعات. كل زر «احجز» يفتح موقع الشريك نفسه (Viator أو GetYourGuide أو Trip.com)، حيث تختار تاريخك وترى السعر النهائي.',
				'عندما تحجز عبر هذه الروابط قد نحصل على عمولة صغيرة، دون أي تكلفة إضافية عليك. ولا تحدد العمولة أبدًا ما نوصي به.',
				'أدلتنا مكتوبة من مصادر رسمية ومنشورة، مثل اليونسكو ووزارة السياحة والآثار المصرية وموسوعة بريتانيكا. وكل معلومة مذكورة في صفحة مصادرنا.',
				'نترك ما يتغير بسرعة ولا يمكن التحقق منه هنا، مثل مواعيد العمل وأسعار التذاكر، ونطلب منك التحقق منه في اليوم نفسه.',
				'التقييمات وعدد المراجعات والأسعار في خيارات الرحلات هي بيانات الشريك نفسه.',
			] ),
		] ) ],
	],
	'hotels'  => [
		'en' => [ 'hotels', 'Hotels in Egypt, place by place', '' ],
		'de' => [ 'hotels-de', 'Hotels in Ägypten, Ort für Ort', '' ],
		'fr' => [ 'hotels-fr', 'Hôtels en Égypte, lieu par lieu', '' ],
		'it' => [ 'hotel-it', 'Hotel in Egitto, luogo per luogo', '' ],
		'es' => [ 'hoteles', 'Hoteles en Egipto, lugar a lugar', '' ],
		'ru' => [ 'oteli', 'Отели в Египте по местам', '' ],
		'zh' => [ 'hotels-zh', '埃及酒店，按地点挑选', '' ],
		'ar' => [ 'fanadiq', 'فنادق مصر مكانًا بمكان', '' ],
	],
	'sources' => [
		'en' => [ 'sources', 'Our sources', '' ],
		'de' => [ 'quellen', 'Unsere Quellen', '' ],
		'fr' => [ 'nos-sources', 'Nos sources', '' ],
		'it' => [ 'fonti', 'Le nostre fonti', '' ],
		'es' => [ 'fuentes', 'Nuestras fuentes', '' ],
		'ru' => [ 'istochniki', 'Наши источники', '' ],
		'zh' => [ 'sources-zh', '我们的资料来源', '' ],
		'ar' => [ 'masadir', 'مصادرنا', '' ],
	],
];
