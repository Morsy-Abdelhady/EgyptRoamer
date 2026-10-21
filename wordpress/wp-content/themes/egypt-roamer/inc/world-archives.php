<?php
/**
 * Proposal 4, the archive tops and the Travel Guide cards (docs/prototypes/journey-v4): the photo headers of Trips &
 * tours (.phero--img), Places (.pl-hero) and the Travel Guide (.gi-hero), Trips & tours' sticky filter bar
 * (.filters), the guides' "Where" chips (.gi-bar) and the guide card (.gi-card). CSS: assets/css/p4-archives.css.
 */

defined( 'ABSPATH' ) || exit;

/** The photo header: the prototype's Nile scene (IMG.sc_nile, the homepage's approved photo). */
function er_world_archive_hero( string $type, string $title, string $intro ): void {
	$img = er_stock_img( '1684100096410-fd39cdff91a3', '', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ], [ 960, 1400, 2000 ] );
	if ( 'er_experience' === $type ) {
		echo '<div class="p4hero p4hero--trips on-dark">' . $img . '<div class="container"><div class="p4hero__crumbs">'; // phpcs:ignore WordPress.Security.EscapeOutput
		er_breadcrumbs();
		echo '</div><h1>' . esc_html( $title ) . '</h1>' . ( '' !== $intro ? '<p>' . esc_html( $intro ) . '</p>' : '' ) . '</div></div>';
		return;
	}
	if ( 'er_destination' === $type ) {
		$h1 = er_t( 'Seven places. <i>Seven different Egypts.</i>' );
		$p  = er_t( 'From Cairo’s thousand minarets to the silence of Siwa, every region tells its own chapter. Pick a place to see what it is like and the trips you can book there.' );
		$ey = er_t( 'Places' );
	} else {
		$n  = function_exists( 'pll_count_posts' ) ? (int) pll_count_posts( er_lang(), [ 'post_type' => 'er_guide' ] ) : (int) wp_count_posts( 'er_guide' )->publish;
		$h1 = er_t( 'Plan it well. <i>Then go.</i>' );
		$p  = er_t( '{n} guides to Egypt’s places, trips and seasons, written from published sources. Each one links to the trips it talks about.', [ 'n' => (string) $n ] );
		$ey = er_t( 'Travel Guide' );
	}
	echo '<div class="p4hero p4hero--' . ( 'er_destination' === $type ? 'places' : 'guides' ) . ' on-dark">' . $img . '<div class="container"><div class="visually-hidden">'; // phpcs:ignore WordPress.Security.EscapeOutput
	er_breadcrumbs();
	echo '</div><p class="p4hero__eyebrow">' . esc_html( $ey ) . '</p><h1>' . wp_kses( $h1, [ 'i' => [] ] ) . '</h1><p>' . esc_html( $p ) . '</p></div></div>';
}

/** Trips & tours' filters as the prototype's sticky bar: Where, Length, the two toggles and the count (links, as before). */
function er_world_trip_filter_bar( int $shown ): void {
	$f     = er_ux2_trip_filters();
	// A link may name the place in another language (the English place's ID): the chips use this language's copy.
	$f['destination'] = $f['destination'] && function_exists( 'er_translated_post_id' ) ? (int) er_translated_post_id( $f['destination'] ) : $f['destination'];
	$dests = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 50, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	// Each button is a link (works without JavaScript); assets/js/p4-trips.js filters the page in place instead.
	$chip  = static fn ( string $label, string $url, bool $on, string $k, string $v ): string => '<a class="chip" href="' . esc_url( $url ) . '" aria-pressed="' . ( $on ? 'true' : 'false' ) . '" data-f="' . esc_attr( $k ) . '" data-v="' . esc_attr( $v ) . '">' . esc_html( $label ) . '</a>';
	$tog   = static fn ( string $label, string $url, bool $on, string $k ): string => '<a class="toggle" href="' . esc_url( $url ) . '" role="checkbox" aria-checked="' . ( $on ? 'true' : 'false' ) . '" data-f="' . esc_attr( $k ) . '"><span class="toggle__box" aria-hidden="true"></span>' . esc_html( $label ) . '</a>';
	$lens  = [ '' => er_t( 'Any' ), 'short' => er_t( 'Up to 5 hours' ), 'full' => er_t( 'Full day' ), 'multi' => er_t( '2+ days' ) ];
	echo '<nav class="p4filters" aria-label="' . esc_attr( er_t( 'Filter' ) ) . '" data-trip-filters data-l-recent="' . esc_attr( er_t( 'Recently viewed' ) ) . '" data-l-clear="' . esc_attr( er_t( 'Clear' ) ) . '" data-dest-now="' . (int) $f['destination'] . '" data-base="' . esc_url( (string) get_post_type_archive_link( 'er_experience' ) ) . '" data-one="' . esc_attr( er_t( '1 trip' ) ) . '" data-many="' . esc_attr( er_t( '{n} trips', [ 'n' => '{n}' ] ) ) . '"><div class="container p4filters__in">';
	echo '<div class="fgroup frow"><span>' . esc_html( er_t( 'Where' ) ) . '</span><div class="chips">';
	echo $chip( er_t( 'All' ), er_ux2_trip_url( array_merge( $f, [ 'destination' => 0 ] ) ), ! $f['destination'], 'destination', '0' ); // phpcs:ignore WordPress.Security.EscapeOutput
	foreach ( $dests as $d ) {
		echo $chip( get_the_title( $d ), er_ux2_trip_url( array_merge( $f, [ 'destination' => (int) $d->ID ] ) ), (int) $d->ID === $f['destination'], 'destination', (string) (int) $d->ID ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div></div><div class="fgroup frow"><span>' . esc_html( er_t( 'Length' ) ) . '</span><div class="chips">';
	foreach ( $lens as $v => $label ) {
		echo $chip( $label, er_ux2_trip_url( array_merge( $f, [ 'len' => $v ] ) ), $v === $f['len'], 'len', (string) $v ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div><div class="ftogs">';
	echo $tog( er_t( 'Private option' ), er_ux2_trip_url( array_merge( $f, [ 'private' => ! $f['private'] ] ) ), $f['private'], 'private' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo $tog( er_t( 'Hotel pickup' ), er_ux2_trip_url( array_merge( $f, [ 'pickup' => ! $f['pickup'] ] ) ), $f['pickup'], 'pickup' ); // phpcs:ignore WordPress.Security.EscapeOutput
	// Sort (20 Oct, as tour marketplaces): the places' order, or every trip by the partner's rating.
	$er_sort = isset( $_GET['sort'] ) && 'rated' === $_GET['sort']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only
	echo '</div><div class="fsort"><span>' . esc_html( er_t( 'Sort' ) ) . '</span><div class="chips">';
	echo $chip( er_t( 'By place' ), add_query_arg( 'sort', false, er_ux2_trip_url( $f ) ), ! $er_sort, 'sort', '' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo $chip( er_t( 'Top rated' ), add_query_arg( 'sort', 'rated', er_ux2_trip_url( $f ) ), $er_sort, 'sort', 'rated' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div></div><p class="count" role="status" data-count>' . esc_html( 1 === $shown ? er_t( '1 trip' ) : er_t( '{n} trips', [ 'n' => (string) $shown ] ) ) . '</p></div></div></nav>';
}

/** The Travel Guide's "Where" chips (the archive filters guides by ?destination=). */
function er_world_guide_bar(): void {
	$on    = isset( $_GET['destination'] ) ? absint( $_GET['destination'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter
	$base  = (string) get_post_type_archive_link( 'er_guide' );
	$dests = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 50, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	echo '<nav class="gi-bar" aria-label="' . esc_attr( er_t( 'Filter' ) ) . '"><div class="container gi-bar__in"><span>' . esc_html( er_t( 'Where' ) ) . '</span>';
	echo '<a class="chip" href="' . esc_url( $base ) . '" aria-pressed="' . ( $on ? 'false' : 'true' ) . '">' . esc_html( er_t( 'All' ) ) . '</a>';
	foreach ( $dests as $d ) {
		echo '<a class="chip" href="' . esc_url( add_query_arg( 'destination', (int) $d->ID, $base ) ) . '" aria-pressed="' . ( (int) $d->ID === $on ? 'true' : 'false' ) . '">' . esc_html( get_the_title( $d ) ) . '</a>';
	}
	echo '</div></nav>';
}

/** A guide as the prototype's card: photo, "Luxor · Planning", title, excerpt, "3 min read · 1 trip inside". */
function er_world_guide_card( int $id ): string {
	$thumb = (int) get_post_thumbnail_id( $id );
	$stock = er_stock_id_for( $id );
	$img   = $thumb ? er_img( $thumb, 'er-card', [ 'alt' => '', 'sizes' => '(max-width: 600px) 100vw, 340px', 'loading' => 'lazy' ] ) : ( '' !== $stock ? er_stock_img( $stock, '', [ 'sizes' => '(max-width: 600px) 100vw, 340px' ], [ 400, 640, 900 ], 0.625 ) : '<span class="media__empty"></span>' );
	// As the prototype (gPlace): one place, two places joined, else "Egypt".
	$dests = er_get_related( $id, '_er_destination' );
	$place = count( $dests ) && count( $dests ) <= 2 ? implode( ' & ', array_map( 'get_the_title', $dests ) ) : er_t( 'Egypt' );
	$terms = get_the_terms( $id, 'er_guide_topic' );
	$topic = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	$trips = count( array_filter( er_get_related( $id, '_er_related' ), static fn ( $p ) => 'er_experience' === get_post_type( $p ) ) );
	$min   = function_exists( 'er_read_minutes' ) ? er_read_minutes( $id ) : 0;
	$meta  = array_filter( [ $min ? er_t( '{n} min read', [ 'n' => $min ] ) : '', $trips ? ( 1 === $trips ? er_t( '1 trip inside' ) : er_t( '{n} trips inside', [ 'n' => $trips ] ) ) : '' ] );
	return '<li class="gi-card"><a href="' . esc_url( get_permalink( $id ) ) . '">' . $img . '<div>' . ( '' !== $place || '' !== $topic ? '<small>' . esc_html( implode( ' · ', array_filter( [ $place, $topic ] ) ) ) . '</small>' : '' ) . '<b>' . esc_html( get_the_title( $id ) ) . '</b><p>' . esc_html( get_the_excerpt( $id ) ) . '</p><em>' . esc_html( implode( ' · ', $meta ) ) . '</em></div></a></li>';
}

/** The guides in the prototype's order (its GUIDES list, by English slug); any other guide after them. */
function er_world_guide_order( array $ids ): array {
	$order = [ 'luxor-hot-air-balloon', 'cairo-museums-which-to-visit', 'islamic-cairo-guide', 'dendera-and-abydos-temples', 'el-gouna-guide', 'luxor-west-bank-temples-and-tombs', 'mount-sinai-saint-catherine', 'saqqara-and-dahshur-pyramids', 'red-sea-famous-dive-sites', 'coptic-cairo-guide', 'inside-the-pyramids-of-giza', 'great-sand-sea', 'grand-egyptian-museum-visit', 'karnak-and-luxor-temple', '14-days-in-egypt-itinerary', 'siwa-or-white-desert', 'valley-of-the-kings-tombs-and-tickets', 'cairo-at-night', 'temples-of-aswan-and-lake-nasser', '10-days-in-egypt-itinerary', 'white-desert-night', 'snorkelling-hurghada-or-sharm', 'nile-cruise-or-hotels-luxor-aswan', 'day-trips-from-cairo', 'abu-simbel-from-aswan', 'best-time-to-visit-egypt', '7-days-in-egypt-itinerary', 'cairo-travel-guide', 'hidden-gems-in-egypt' ];
	$rank = static function ( int $id ) use ( $order ): int {
		$en = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $id, 'en' ) : $id;
		$i  = array_search( (string) get_post_field( 'post_name', $en ?: $id ), $order, true );
		return false === $i ? 999 : (int) $i;
	};
	$ids = array_map( 'intval', $ids );
	usort( $ids, static fn ( $a, $b ) => $rank( $a ) <=> $rank( $b ) );
	return $ids;
}

/** Every published guide in this language. */
function er_world_guide_index(): array {
	return array_map( 'intval', get_posts( [ 'post_type' => 'er_guide', 'post_status' => 'publish', 'numberposts' => 100, 'fields' => 'ids', 'suppress_filters' => false ] ) );
}

/** A trip row in a guide's list of trips (prototype rcard): photo, name, place · length · options, ✓ Free cancellation. */
function er_world_guide_rcard( array $T ): string {
	$opts = 1 === $T['n'] ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => $T['n'] ] );
	return '<li><a href="' . esc_url( $T['url'] ) . '">' . ( '' !== $T['img'] ? '<img src="' . esc_url( $T['img'] ) . '" alt="" loading="lazy" />' : '<span></span>' ) . '<span><b>' . esc_html( $T['title'] ) . '</b><span>' . esc_html( implode( ' · ', array_filter( [ $T['place'], $T['len'], $T['n'] ? $opts : '' ] ) ) ) . '</span>' . ( $T['cancel'] ? '<em>✓ ' . esc_html( er_t( 'Free cancellation' ) ) . '</em>' : '' ) . '</span></a></li>';
}

/* The Travel Guide lists every guide on one page, as the prototype (24 today). */
add_action( 'pre_get_posts', static function ( WP_Query $q ): void {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'er_guide' ) && er_world_on() ) {
		$q->set( 'posts_per_page', 100 );
	}
}, 20 );

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( er_world_on() && is_singular( [ 'er_guide', 'er_destination' ] ) ) {
		wp_enqueue_style( 'er-p4-guide', ER_THEME_URI . '/assets/css/p4-guide.css', [ 'er-pages' ], er_asset_ver( 'assets/css/p4-guide.css' ) );
		wp_enqueue_style( 'er-p4-archives', ER_THEME_URI . '/assets/css/p4-archives.css', [ 'er-pages' ], er_asset_ver( 'assets/css/p4-archives.css' ) );
		wp_enqueue_script( 'er-p4-guide', ER_THEME_URI . '/assets/js/p4-guide.js', [], er_asset_ver( 'assets/js/p4-guide.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	}
	if ( is_page_template( 'templates/hotels.php' ) ) {
		wp_enqueue_style( 'er-p4-archives', ER_THEME_URI . '/assets/css/p4-archives.css', [ 'er-pages' ], er_asset_ver( 'assets/css/p4-archives.css' ) );
		wp_enqueue_style( 'er-home-story', ER_THEME_URI . '/assets/css/home-story.css', [ 'er-pages' ], er_asset_ver( 'assets/css/home-story.css' ) );
	}
	if ( is_page_template( 'templates/sources.php' ) ) {
		wp_enqueue_style( 'er-p4-archives', ER_THEME_URI . '/assets/css/p4-archives.css', [ 'er-pages' ], er_asset_ver( 'assets/css/p4-archives.css' ) );
	}
	if ( er_world_on() && ( is_post_type_archive( [ 'er_experience', 'er_destination', 'er_guide' ] ) ) ) {
		wp_enqueue_style( 'er-p4-archives', ER_THEME_URI . '/assets/css/p4-archives.css', [ 'er-pages' ], er_asset_ver( 'assets/css/p4-archives.css' ) );
	}
	if ( er_world_on() && function_exists( 'er_ux2_trips_page' ) && er_ux2_trips_page() ) {
		wp_enqueue_script( 'er-p4-trips', ER_THEME_URI . '/assets/js/p4-trips.js', [], er_asset_ver( 'assets/js/p4-trips.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	}
}, 21 );

/** The About, Sources and Hotels pages in the current language (Core option er_trust_pages), as [ url, title ]. */
function er_trust_page( string $kind ): array {
	$id = (int) ( ( (array) get_option( 'er_trust_pages', [] ) )[ $kind ] ?? 0 );
	$id = $id && function_exists( 'er_translated_post_id' ) ? (int) er_translated_post_id( $id ) : $id;
	return $id && 'publish' === get_post_status( $id ) ? [ (string) get_permalink( $id ), get_the_title( $id ) ] : [];
}

/* "Hotels" in the main menu (header and phone menu), after the Travel Guide, in every language. */
add_filter( 'wp_nav_menu_objects', static function ( array $items, $args ): array {
	$loc = (string) ( $args->theme_location ?? '' );
	if ( ! er_world_on() || 0 !== strpos( $loc, 'primary' ) || ! $items ) {
		return $items;
	}
	$page = er_trust_page( 'hotels' );
	if ( ! $page || array_filter( $items, static fn ( $i ) => (string) $i->url === $page[0] ) ) {
		return $items;
	}
	$item                   = clone end( $items );
	$item->ID               = 990001;
	$item->db_id            = 990001;
	$item->title            = er_t( 'Hotels' );
	$item->url              = $page[0];
	$item->menu_item_parent = '0';
	$item->object           = 'page';
	$item->type             = 'custom';
	$item->classes          = [ 'menu-item' ];
	$item->current          = is_page_template( 'templates/hotels.php' );
	if ( $item->current ) {
		$item->classes[] = 'current-menu-item';
	}
	$items[] = $item;
	return $items;
}, 30, 2 );
