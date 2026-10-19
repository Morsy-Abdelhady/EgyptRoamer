<?php
/**
 * New journey, proposal 4, slice 3 (owner, 17 Oct): the Trips & tours page, previewed with ?ux=2 only.
 * From docs/prototypes/journey-v4/ (v4-round6.py, v4-round7.py) and docs/UX-EXPERT-REVIEW-2026-10-17.md:
 * - the page was titled "Experiences" while the menu says "Trips & tours";
 * - the place filter was a dropdown plus an Apply button: the places are now one row of buttons, every place
 *   one tap away, "All" first;
 * - a tourist also chooses by time and comfort: "Up to 5 hours", "Full day", "2+ days", "Private option" and
 *   "Hotel pickup" filters, read only from the partners' stated facts (Core data/offer-facts.json), never guessed;
 * - the number of trips shown, and a way back when a filter leaves nothing.
 * The filters are plain links (the preview is noindex), so they work without JavaScript.
 */

defined( 'ABSPATH' ) || exit;

/** True on the Trips & tours archive in the preview. */
function er_ux2_trips_page(): bool {
	return er_ux2() && is_post_type_archive( 'er_experience' );
}

// The preview's archive lists every trip, so the filters below see them all (10 per language today).
add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'er_experience' ) && er_ux2() ) {
		$q->set( 'posts_per_page', 100 );
	}
}, 20 );

/**
 * What a trip's options offer, from the partners' own pages only: which lengths (short: up to 5 hours, full: a day,
 * multi: 2 days or more), whether any option is private, whether any offers pickup.
 */
function er_ux2_trip_traits( int $post_id ): array {
	$t = [ 'len' => [], 'private' => false, 'pickup' => false ];
	$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $post_id, 10 ) : [];
	foreach ( $offers as $offer ) {
		$f = function_exists( 'er_offer_facts' ) ? er_offer_facts( (int) $offer ) : [];
		if ( 'private' === ( $f['group'] ?? '' ) ) {
			$t['private'] = true;
		}
		if ( ! empty( $f['pickup'] ) ) {
			$t['pickup'] = true;
		}
		if ( ! empty( $f['duration']['n'] ) ) {
			preg_match_all( '/\d+(?:\.\d+)?/', (string) $f['duration']['n'], $m );
			$n    = $m[0] ? (float) max( $m[0] ) : 0;
			$unit = (string) ( $f['duration']['unit'] ?? 'hours' );
			if ( 'hours' === $unit && $n ) {
				$t['len'][] = $n <= 5 ? 'short' : 'full';
			} elseif ( $n ) {
				$t['len'][] = $n >= 2 ? 'multi' : 'full';
			}
		}
	}
	// No stated duration on any option: the trip page's own length line.
	if ( ! $t['len'] ) {
		$d = strtolower( (string) get_post_meta( $post_id, '_er_duration', true ) );
		if ( preg_match( '/(\d+)\s*days/', $d, $m ) && (int) $m[1] >= 2 ) {
			$t['len'][] = 'multi';
		} elseif ( preg_match( '/full day|1 day/', $d ) ) {
			$t['len'][] = 'full';
		} elseif ( preg_match( '/half day/', $d ) || ( preg_match( '/(\d+)\s*hours?/', $d, $m ) && (int) $m[1] <= 5 ) ) {
			$t['len'][] = 'short';
		} elseif ( preg_match( '/(\d+)\s*hours?/', $d ) ) {
			$t['len'][] = 'full';
		}
	}
	$t['len'] = array_values( array_unique( $t['len'] ) );
	return $t;
}

/** The current filters, from the URL. */
function er_ux2_trip_filters(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters
	$len = isset( $_GET['len'] ) ? sanitize_key( wp_unslash( $_GET['len'] ) ) : '';
	return [
		'destination' => isset( $_GET['destination'] ) ? absint( $_GET['destination'] ) : 0,
		'len'         => in_array( $len, [ 'short', 'full', 'multi' ], true ) ? $len : '',
		'private'     => ! empty( $_GET['private'] ),
		'pickup'      => ! empty( $_GET['pickup'] ),
	];
	// phpcs:enable
}

/** The archive URL with these filters (ux=2 kept by the preview's link script, and here for no-JS visits). */
function er_ux2_trip_url( array $f ): string {
	$args = array_filter( [
		'destination' => $f['destination'] ?: null,
		'len'         => $f['len'] ?: null,
		'private'     => $f['private'] ? 1 : null,
		'pickup'      => $f['pickup'] ? 1 : null,
		'ux'          => get_option( 'er_ux2' ) ? null : 2,
	] );
	return add_query_arg( $args, (string) get_post_type_archive_link( 'er_experience' ) );
}

/** The trips that pass the length, private and pickup filters (the place is filtered by the query). */
function er_ux2_filter_trips( array $ids ): array {
	$f = er_ux2_trip_filters();
	if ( ! $f['len'] && ! $f['private'] && ! $f['pickup'] ) {
		return $ids;
	}
	return array_values( array_filter( $ids, static function ( $id ) use ( $f ): bool {
		$t = er_ux2_trip_traits( (int) $id );
		return ( ! $f['len'] || in_array( $f['len'], $t['len'], true ) ) && ( ! $f['private'] || $t['private'] ) && ( ! $f['pickup'] || $t['pickup'] );
	} ) );
}

/** The filter bar: places, length, private, pickup, and how many trips are shown. */
function er_ux2_trip_filter_bar( int $shown ): void {
	$f     = er_ux2_trip_filters();
	$dests = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 50, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	$chip  = static function ( string $label, string $url, bool $on ): string {
		return '<a class="ux2-chip" href="' . esc_url( $url ) . '"' . ( $on ? ' aria-current="true"' : '' ) . '>' . esc_html( $label ) . '</a>';
	};
	$lens  = [
		''      => er_t( 'Any length' ),
		'short' => er_t( 'Up to 5 hours' ),
		'full'  => er_t( 'Full day' ),
		'multi' => er_t( '2+ days' ),
	];
	?>
	<nav class="ux2-filters" aria-label="<?php echo esc_attr( er_t( 'Filter' ) ); ?>">
		<div class="ux2-filters__row">
			<span class="ux2-filters__label"><?php er_e( 'Where' ); ?></span>
			<div class="ux2-filters__chips">
				<?php
				echo $chip( er_t( 'All' ), er_ux2_trip_url( array_merge( $f, [ 'destination' => 0 ] ) ), ! $f['destination'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				foreach ( $dests as $d ) {
					echo $chip( get_the_title( $d ), er_ux2_trip_url( array_merge( $f, [ 'destination' => (int) $d->ID ] ) ), (int) $d->ID === $f['destination'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		</div>
		<div class="ux2-filters__row">
			<span class="ux2-filters__label"><?php er_e( 'Length' ); ?></span>
			<div class="ux2-filters__chips">
				<?php
				foreach ( $lens as $v => $label ) {
					echo $chip( $label, er_ux2_trip_url( array_merge( $f, [ 'len' => $v ] ) ), $v === $f['len'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				echo $chip( er_t( 'Private option' ), er_ux2_trip_url( array_merge( $f, [ 'private' => ! $f['private'] ] ) ), $f['private'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo $chip( er_t( 'Hotel pickup' ), er_ux2_trip_url( array_merge( $f, [ 'pickup' => ! $f['pickup'] ] ) ), $f['pickup'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
			<p class="ux2-filters__count" role="status"><?php echo esc_html( er_t( 'Trips shown: {n}', [ 'n' => (string) $shown ] ) ); // one form for every language's plurals ?></p>
		</div>
	</nav>
	<?php
}

/** Nothing left after the filters: say so, and offer the way back. */
function er_ux2_trip_empty(): void {
	?>
	<div class="empty-state">
		<p class="t-h3"><?php er_e( 'No trip matches all of these.' ); ?></p>
		<p class="muted"><?php er_e( 'Remove a filter, or see every trip.' ); ?></p>
		<p><a class="btn btn--outline" href="<?php echo esc_url( er_ux2_trip_url( [ 'destination' => 0, 'len' => '', 'private' => false, 'pickup' => false ] ) ); ?>"><?php er_e( 'See all trips' ); ?></a></p>
	</div>
	<?php
}

/*
 * Slice 4 (17 Oct): the trip page, previewed with ?ux=2 only.
 * - One length per trip: the hero said "6 hours" while the booking box said "About 4–10 hours" (Siwa: "Full day"
 *   above two 3-day options). Both now show the length the partners state for the trip's options.
 * - Trips measured in days (White Desert, Siwa) now show their length in the booking box too.
 * - "Our pick" always says why when the facts allow: "Private, the longest visit" (from the partners' facts only).
 */

/** The trip's length from its options' stated durations: "About 4–10 hours", "3 days", "2–3 days"; '' when mixed or unknown. */
function er_ux2_offer_length( array $offers ): string {
	if ( ! $offers || ! function_exists( 'er_offer_facts' ) ) {
		return '';
	}
	$lo = null; $hi = null; $approx = false; $unit = '';
	foreach ( $offers as $id ) {
		$d = (array) ( er_offer_facts( (int) $id )['duration'] ?? [] );
		if ( empty( $d['n'] ) || ! preg_match_all( '/\d+(?:\.\d+)?/', (string) $d['n'], $m ) ) {
			return '';
		}
		$u = 'hours' === ( $d['unit'] ?? 'hours' ) ? 'hours' : 'days';
		if ( '' !== $unit && $u !== $unit ) {
			return ''; // hours and days mixed: no single honest line
		}
		$unit   = $u;
		$lo     = null === $lo ? (float) $m[0][0] : min( $lo, (float) $m[0][0] );
		$hi     = null === $hi ? (float) end( $m[0] ) : max( $hi, (float) end( $m[0] ) );
		$approx = $approx || ! empty( $d['approx'] );
	}
	$n = $lo === $hi ? (string) $lo : $lo . '–' . $hi;
	if ( 'hours' === $unit ) {
		$txt = er_t( '{n} hours', [ 'n' => $n ] );
	} else {
		$txt = '1' === $n ? er_t( '{n} day', [ 'n' => $n ] ) : er_t( '{n} days', [ 'n' => $n ] );
	}
	return $approx ? er_t( 'About {duration}', [ 'duration' => $txt ] ) : $txt;
}

/** The longest stated visit among the options, in hours (a day counts as 24), 0 when unknown. */
function er_ux2_offer_hours( array $f ): float {
	$d = (array) ( $f['duration'] ?? [] );
	if ( empty( $d['n'] ) || ! preg_match_all( '/\d+(?:\.\d+)?/', (string) $d['n'], $m ) ) {
		return 0;
	}
	$v = (float) max( $m[0] );
	return 'hours' === ( $d['unit'] ?? 'hours' ) ? $v : $v * 24;
}

/** A reason for "Our pick" when er_pick_reason() finds no fact only the pick has: private and/or the longest visit. */
function er_ux2_pick_reason( int $pick, array $offers ): string {
	if ( ! $pick || count( $offers ) < 2 || ! function_exists( 'er_offer_facts' ) ) {
		return '';
	}
	$mine    = er_offer_facts( $pick );
	$others  = array_map( static fn ( $id ): array => er_offer_facts( (int) $id ), array_values( array_diff( array_map( 'intval', $offers ), [ $pick ] ) ) );
	$h       = er_ux2_offer_hours( $mine );
	$longest = $h > 0 && ! array_filter( $others, static fn ( $f ): bool => er_ux2_offer_hours( $f ) >= $h );
	$private = 'private' === ( $mine['group'] ?? '' );
	if ( $private && $longest ) {
		return er_t( 'Private, the longest visit' );
	}
	return $longest ? er_t( 'The longest visit' ) : '';
}

/*
 * Slice 6 (17 Oct): the Trip Builder on the homepage, previewed with ?ux=2 only (src/js/components/planner.js).
 * Tested on the live site before: the route and the nights worked, but
 * - "What moves you?" changed nothing;
 * - "Travel style" (Smart / Comfort / Luxury) changed nothing, since there are no prices behind it (none are invented);
 * - with fewer nights than places, places were dropped from the route without a word;
 * - the result was a list of partner offer names, the same whatever the interests.
 * In the preview: interests rank the trips and mark the ones that match; step 04 asks a question the trips can
 * answer, "Private or shared?"; dropped places are named, with a button to add the nights; the result is one card
 * per trip on the route, opening the trip page where its options are compared.
 */

/**
 * Which interests a trip serves: an editorial grouping by the trip's own subject (default-language slugs), used only
 * to order the trips and mark matches. No interest is given a trip that does not have that subject.
 */
const ER_UX2_TRIP_INTERESTS = [
	'valley-of-the-kings-hatshepsut-temple'  => [ 'ancient' ],
	'pyramids-of-giza-sphinx-private-tour'   => [ 'ancient' ],
	'abu-simbel-day-trip-from-aswan'         => [ 'ancient' ],
	'alexandria-day-trip-from-cairo'         => [ 'ancient', 'culture' ],
	'red-sea-diving-two-reef-dives'          => [ 'beach', 'adventure' ],
	'ras-mohammed-snorkelling-from-sharm'    => [ 'beach', 'nature' ],
	'white-desert-black-desert-safari'       => [ 'adventure', 'nature' ],
	'great-sand-sea-4-4-hot-springs'         => [ 'adventure', 'nature' ],
	'cairo-street-food-tour-by-night'        => [ 'food', 'culture' ],
	'nile-dinner-cruise-with-live-show'      => [ 'culture' ],
	'nile-cruise-aswan-to-luxor'             => [ 'ancient', 'luxury' ],
];

/** Trips without a place: where every option leaves from (partner pages: "2 days from Cairo", "Cairo: 2-Day…"). */
const ER_UX2_TRIP_FROM = [ 'white-desert-black-desert-safari' => 'cairo' ];

add_action( 'wp_footer', static function (): void {
	if ( ! er_ux2() || ! is_front_page() ) {
		return;
	}
	$trips = [];
	$ids   = get_posts( [ 'post_type' => 'er_experience', 'post_status' => 'publish', 'numberposts' => 100, 'fields' => 'ids', 'suppress_filters' => false, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ] ] );
	foreach ( $ids as $id ) {
		$id     = (int) $id;
		$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $id, 6 ) : [];
		$t      = er_ux2_trip_traits( $id );
		$slug   = er_neutral_slug( $id );
		// A trip with no place of its own (the White Desert): where all its options leave from, as the partners state.
		$from      = er_get_related( $id, '_er_destination' ) ? '' : ( ER_UX2_TRIP_FROM[ $slug ] ?? '' );
		$from_post = $from ? get_page_by_path( $from, OBJECT, 'er_destination' ) : null;
		$from_id   = $from_post && function_exists( 'pll_get_post' ) ? ( (int) pll_get_post( $from_post->ID ) ?: $from_post->ID ) : ( $from_post ? $from_post->ID : 0 );
		$from_name = $from_id ? get_the_title( $from_id ) : '';
		$from      = $from_name ? $from : '';
		$trips[] = [
			'title'     => er_payload_title( $id ),
			'url'       => (string) get_permalink( $id ),
			'image'     => er_post_image_payload( $id ),
			'dests'     => $from ? [ $from ] : array_map( 'er_neutral_slug', wp_list_pluck( er_get_related( $id, '_er_destination' ), 'ID' ) ),
			'places'    => $from ? er_t( 'From {place}', [ 'place' => $from_name ] ) : implode( ' · ', array_map( 'get_the_title', er_get_related( $id, '_er_destination' ) ) ),
			'len'       => function_exists( 'er_ux2_offer_length' ) ? er_ux2_offer_length( $offers ) : '',
			'n'         => count( $offers ),
			'private'   => $t['private'],
			// Proposal 4: "✓ Free cancellation" when every option's partner page states it (Core offer-facts "cancel").
			'cancel'    => $offers && function_exists( 'er_offer_facts' ) && ! array_filter( $offers, static fn ( $o ) => empty( er_offer_facts( (int) $o )['cancel'] ) ),
			'interests' => ER_UX2_TRIP_INTERESTS[ $slug ] ?? [],
		];
	}
	$data = [
		'trips' => $trips,
		't'     => [
			'step'      => er_t( 'Private or shared?' ),
			'either'    => er_t( 'Either' ),
			'private'   => er_t( 'Private only' ),
			'hint'      => er_t( 'Every trip shows whether it has a private option.' ),
			'title'     => er_t( 'Trips on your route' ),
			'sub'       => er_t( 'The ones that match your interests come first. Each opens its page, where the options are compared.' ),
			'dropped'   => er_t( 'Not enough nights for every place: {places} left out.' ),
			'add'       => er_t( 'Add the missing nights (+{n})' ),
			'nopriv'    => er_t( 'No trip on this route has a private option yet.' ),
			'hidden'    => er_t( 'Trips without a private option are hidden: {n}' ),
			'more'      => er_t( 'Nothing on this route for {interest}. Add a place:' ),
			'opt1'      => er_t( '1 option' ),
			'optn'      => er_t( '{n} options' ),
			'privopt'   => er_t( 'Private option' ),
			'cancel'    => er_t( 'Free cancellation' ),
			'see'       => er_t( 'See options' ),
			'save'      => er_t( 'Save this plan' ),
			'saved'     => er_t( 'Plan saved' ),
			'planmeta'  => er_t( 'Your plan · {n} trips on the route' ),
		],
	];
	echo '<script type="application/json" id="ux2-planner">' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}, 6 );

/*
 * Slice 7 (17 Oct): the homepage, previewed with ?ux=2 only. The hero offered only "Explore Egypt" (the scenes) and
 * the film: a tourist who already knows where they are going had no way in. Under the hero's buttons, "Where in
 * Egypt?" lists the places, each opening Trips & tours filtered to that place (prototype v4-improve.py, v4-tourist.py §6).
 */
function er_ux2_hero_places(): void {
	if ( ! er_ux2() ) {
		return;
	}
	$dests = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 20, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	if ( ! $dests ) {
		return;
	}
	echo '<nav class="ux2-where" data-hero-fade aria-label="' . esc_attr( er_t( 'Where in Egypt?' ) ) . '"><span class="ux2-where__label">' . esc_html( er_t( 'Where in Egypt?' ) ) . '</span><span class="ux2-where__chips">';
	foreach ( $dests as $d ) {
		echo '<a href="' . esc_url( er_ux2_trip_url( [ 'destination' => (int) $d->ID, 'len' => '', 'private' => false, 'pickup' => false ] ) ) . '">' . esc_html( get_the_title( $d ) ) . '</a>';
	}
	echo '</span></nav>';
}

/*
 * Slice 12 (17 Oct): a shorter homepage in the preview (prototype v4-round7.py: hero, trips, moods, map, where to
 * stay, the Trip Builder). The homepage was 18 screens on a computer and 20 on a phone. Left out in the preview:
 * the quote interlude, the destinations cards (the map lists every place, each opening its page) and the Journal
 * (the Travel Guide is in the menu, and each trip and place links its guides).
 */
function er_ux2_short_home( string $html ): string {
	foreach ( [ '<section class="interlude', '<section class="section destinations"' ] as $start ) {
		$a = strpos( $html, $start );
		if ( false === $a ) {
			continue;
		}
		$e = strpos( $html, '</section>', $a );
		if ( false !== $e ) {
			$html = substr( $html, 0, $a ) . substr( $html, $e + strlen( '</section>' ) );
		}
	}
	return $html;
}

/*
 * Slice 13 (17 Oct): Wikimedia Commons photos for trip-page cards that had none (owner, 17 Oct: "the ones without
 * photos, look on Wikipedia"; dish photos approved the same day). Each shows the exact place or dish, under a free
 * licence, credited under the photo with a link to its Commons page. Preview only (?ux=2). Sources:
 * docs/prototypes/journey-v4/wpick.json and the dish files listed in docs/IMAGE-QUALITY-AUDIT-2026-10-02.md §6.
 */
const ER_UX2_COMMONS_CARDS = [
	'exp-valley' => [
		'what-you-do' => [
			2 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/1/10/Colosos_de_Memn%C3%B3n%2C_Luxor%2C_Egipto%2C_2022-04-03%2C_DD_131.jpg/960px-Colosos_de_Memn%C3%B3n%2C_Luxor%2C_Egipto%2C_2022-04-03%2C_DD_131.jpg', 'Photo: Diego Delso, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Colosos_de_Memn%C3%B3n,_Luxor,_Egipto,_2022-04-03,_DD_131.jpg' ],
		],
	],
	'exp-food' => [
		'what-youll-taste' => [
			0 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/e/e9/Egyptian_food_Koshary.jpg/960px-Egyptian_food_Koshary.jpg', 'Photo: Dina Said, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Egyptian_food_Koshary.jpg' ],
			1 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/58/Foul_Mohamed_Ahmed_-_foul.jpg/960px-Foul_Mohamed_Ahmed_-_foul.jpg', 'Photo: Marsupium, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Foul_Mohamed_Ahmed_-_foul.jpg' ],
			2 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/0/0f/Hawawshi_Main_1_Mohamed_Moheyeldin.jpg/960px-Hawawshi_Main_1_Mohamed_Moheyeldin.jpg', 'Photo: Mohamed Moheyeldin, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Hawawshi_Main_1_Mohamed_Moheyeldin.jpg' ],
			3 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/26/Egyptian-food-16.jpg/960px-Egyptian-food-16.jpg', 'Photo: Mohamed Ouda, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Egyptian-food-16.jpg' ],
		],
	],
	'exp-alexandria' => [
		'what-you-see' => [
			2 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/a/a3/Catacombs_of_Kom_El_Shoqafa%2C_Alexandria%2C_Egypt_-_50852603281.jpg/960px-Catacombs_of_Kom_El_Shoqafa%2C_Alexandria%2C_Egypt_-_50852603281.jpg', 'Photo: Following Hadrian, Wikimedia Commons, CC BY-SA 2.0', 'https://commons.wikimedia.org/wiki/File:Catacombs_of_Kom_El_Shoqafa,_Alexandria,_Egypt_-_50852603281.jpg' ],
			4 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/c6/Montaza_Palace%2C_Alexandria%2C_Egypt_-_Murat_%C3%96zsoy_2018.jpg/960px-Montaza_Palace%2C_Alexandria%2C_Egypt_-_Murat_%C3%96zsoy_2018.jpg', 'Photo: Murat Özsoy 1958, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Montaza_Palace,_Alexandria,_Egypt_-_Murat_%C3%96zsoy_2018.jpg' ],
		],
	],
	'exp-safari' => [
		'what-you-see' => [
			0 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/e/ed/Black_Desert%2C_Egypt_17.jpg/960px-Black_Desert%2C_Egypt_17.jpg', 'Photo: Viktor Lazić, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Black_Desert,_Egypt_17.jpg' ],
			1 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/b/b0/Crystal_Mountain%2C_White_Desert%2C_Egypt_28.jpg/960px-Crystal_Mountain%2C_White_Desert%2C_Egypt_28.jpg', 'Photo: Viktor Lazić, Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Crystal_Mountain,_White_Desert,_Egypt_28.jpg' ],
			3 => [ 'https://thumb.wikimedia.org/wikipedia/commons/thumb/1/16/White_Desert%2C_Rock_formations_in_desert_landscape%2C_Egypt.jpg/960px-White_Desert%2C_Rock_formations_in_desert_landscape%2C_Egypt.jpg', 'Photo: Vyacheslav Argenberg, Wikimedia Commons, CC BY 4.0', 'https://commons.wikimedia.org/wiki/File:White_Desert,_Rock_formations_in_desert_landscape,_Egypt.jpg' ],
		],
	],
];
