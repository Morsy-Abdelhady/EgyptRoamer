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
