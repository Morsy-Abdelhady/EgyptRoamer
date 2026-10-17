<?php
/**
 * New journey, proposal 4 (owner, 17 Oct): the header and menu, previewed with ?ux=2 only (er_ux2()).
 * Visitors without the preview see no change. From docs/prototypes/journey-v4/chrome2.py and the reviews in
 * docs/UX-EXPERT-REVIEW-2026-10-17.md:
 * - "Destinations" and "Experiences" were unclear (both seemed to open the same thing): "Places" and "Trips & tours";
 *   in the phone menu each says in a line what it holds;
 * - the phone menu starts with search, and its planner link says what the planner does ("Trip Builder");
 * - the menu button turns into a close button while the menu is open;
 * - the floating "Trip assistant" covered page content (trip cards, booking options, footer links): an "Ask" button
 *   in the header instead, at every size.
 */

defined( 'ABSPATH' ) || exit;

/** Which section a menu link points to, from its URL: destinations, experiences or guides. */
function er_ux2_section( string $url ): string {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	return preg_match( '~/(destinations|experiences|guides)/?$~', $path, $m ) ? $m[1] : '';
}

/** The new label and its line for a section ('' when unchanged). */
function er_ux2_label( string $section ): array {
	switch ( $section ) {
		case 'destinations':
			return [ er_t( 'Places' ), er_t( 'Cairo, Alexandria, Siwa, Luxor, Aswan, Hurghada and Sharm El Sheikh' ) ];
		case 'experiences':
			return [ er_t( 'Trips & tours' ), er_t( 'Tours and day trips you can book' ) ];
		case 'guides':
			return [ er_t( 'Travel Guide' ), er_t( 'Plans, tips and what to know before you go' ) ];
	}
	return [ '', '' ];
}

// Labels, in the header, the phone menu and the footer.
add_filter( 'wp_nav_menu_objects', static function ( array $items ): array {
	if ( ! er_ux2() ) {
		return $items;
	}
	foreach ( $items as $item ) {
		[ $label ] = er_ux2_label( er_ux2_section( (string) $item->url ) );
		if ( '' !== $label ) {
			$item->title = $label;
		}
	}
	return $items;
}, 20 );

// The phone menu: a line under each section saying what it holds.
add_filter( 'walker_nav_menu_start_el', static function ( string $out, $item, int $depth, $args ): string {
	if ( ! er_ux2() || ! isset( $args->items_wrap ) || ! str_contains( (string) $args->items_wrap, 'menu__links' ) ) {
		return $out;
	}
	[ , $line ] = er_ux2_label( er_ux2_section( (string) $item->url ) );
	return '' === $line ? $out : str_replace( '</a>', '<small class="ux2-menu__line">' . esc_html( $line ) . '</small></a>', $out );
}, 10, 4 );

add_filter( 'body_class', static function ( array $classes ): array {
	if ( er_ux2() ) {
		$classes[] = 'er-ux2';
	}
	return $classes;
} );

/** The header's "Ask" button (opens the trip assistant). */
function er_ux2_ask_button(): void {
	if ( ! er_ux2() ) {
		return;
	}
	?>
	<button class="ux2-ask" type="button" data-open="assistant" aria-haspopup="dialog" aria-controls="assistant" aria-label="<?php echo esc_attr( er_t( 'Ask the trip assistant' ) ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H10l-4.2 3.6c-.5.4-1.3.1-1.3-.6V16.9A2.5 2.5 0 0 1 4 14.6z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.5 9.5h7M8.5 12.5h4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
		<span><?php er_e( 'Ask' ); ?></span>
	</button>
	<?php
}

/** The phone menu's first row: search, and after the sections, the Trip Builder. */
function er_ux2_menu_search(): void {
	if ( ! er_ux2() ) {
		return;
	}
	?>
	<button type="button" class="ux2-menu__search" data-open="search"><?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Search places, trips and guides' ); ?></span></button>
	<?php
}

function er_ux2_menu_builder( string $planner_url ): void {
	if ( ! er_ux2() || '' === $planner_url ) {
		return;
	}
	?>
	<a class="ux2-menu__builder" href="<?php echo esc_url( $planner_url ); ?>" data-close><?php er_e( 'Trip Builder' ); ?><small class="ux2-menu__line"><?php er_e( 'Choose nights and places; get a route and the trips on it' ); ?></small></a>
	<?php
}

/*
 * Slice 2 (17 Oct): search and the assistant, previewed with ?ux=2 only.
 * - The search box only knew the homepage's featured lists (8 trips, 7 guides): "Valley of the Kings" or "Siwa" could
 *   not be found, and a phrase like "nile cruise" or "snorkeling" found nothing. In the preview it searches every
 *   published trip, place and guide in the page's language, by words, grouped, and says what to do when nothing
 *   matches (src/js/components/search.js).
 * - The assistant: a safety question gets an honest note (we have no page on it) and the team chat; a number of
 *   days gets the Trip Builder (src/js/assistant.js).
 */

/** Every published trip, place and guide in the current language, for the preview's search box. Cached per language. */
function er_ux2_search_index(): array {
	$key   = 'er_ux2_search_' . er_lang();
	$index = get_transient( $key );
	if ( is_array( $index ) ) {
		return $index;
	}
	$index = [];
	$types = [
		'er_experience'  => 'trip',
		'er_destination' => 'place',
		'er_guide'       => 'guide',
	];
	foreach ( $types as $type => $kind ) {
		$ids = get_posts( [ 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => 200, 'fields' => 'ids', 'suppress_filters' => false, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ] ] );
		foreach ( $ids as $id ) {
			$id      = (int) $id;
			$title   = er_payload_title( $id );
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $id ) );
			if ( 'trip' === $kind ) {
				$sub = implode( ' · ', array_filter( [ (string) get_post_meta( $id, '_er_location', true ), (string) get_post_meta( $id, '_er_duration', true ) ] ) );
			} elseif ( 'place' === $kind ) {
				$sub = (string) get_post_meta( $id, '_er_tagline', true );
			} else {
				$sub = er_t( 'Guide' );
			}
			$index[] = [
				'k'     => $kind,
				'title' => $title,
				'sub'   => $sub,
				'url'   => (string) get_permalink( $id ),
				'image' => er_post_image_payload( $id ),
				'text'  => mb_substr( $title . ' ' . $sub . ' ' . (string) get_post_meta( $id, '_er_region_label', true ) . ' ' . $excerpt, 0, 600 ),
			];
		}
	}
	set_transient( $key, $index, 12 * HOUR_IN_SECONDS );
	return $index;
}

// A page is published, changed or removed: the index is rebuilt on the next preview.
add_action( 'save_post', static function ( int $id ): void {
	if ( in_array( get_post_type( $id ), [ 'er_experience', 'er_destination', 'er_guide' ], true ) ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_er\_ux2\_search\_%' OR option_name LIKE '\_transient\_timeout\_er\_ux2\_search\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
} );

/** The preview's search data and strings, read by search.js and assistant.js. */
add_action( 'wp_footer', static function (): void {
	if ( ! er_ux2() ) {
		return;
	}
	$planner = (bool) er_home( 'planner_enabled' ) ? er_home_url() . '#planner' : '';
	$data    = [
		'index'   => er_ux2_search_index(),
		'groups'  => [
			'trip'  => er_t( 'Trips & tours' ),
			'place' => er_t( 'Places' ),
			'guide' => er_t( 'Travel Guide' ),
		],
		'empty'   => er_t( 'Nothing on Egypt Roamer matches “{q}” yet. Try a place or a sight, like Luxor or pyramids.' ),
		'actions' => array_values( array_filter( [
			[ er_t( 'See all trips' ), (string) get_post_type_archive_link( 'er_experience' ) ],
			[ er_t( 'Travel Guide' ), (string) get_post_type_archive_link( 'er_guide' ) ],
			$planner ? [ er_t( 'Trip Builder' ), $planner ] : null,
		] ) ),
		'safety'  => er_t( 'We have no page on safety yet, so we won’t guess. Our team can answer that:' ),
		'team'    => er_t( 'Chat with Egypt Roamer' ),
		'days'    => er_t( 'Draft a route for {n} days in the Trip Builder' ),
		'planner' => $planner,
	];
	echo '<script type="application/json" id="ux2-data">' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}, 5 );
