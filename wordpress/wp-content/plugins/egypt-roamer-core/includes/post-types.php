<?php
/**
 * Content models. Public editorial types get clean archive URLs:
 *   /destinations/{slug}/  /tours/{slug}/  /experiences/{slug}/
 *   /activities/{slug}/    /guides/{slug}/
 * Affiliate providers and offers are private admin records — never public URLs.
 * Articles are standard WordPress posts.
 */

defined( 'ABSPATH' ) || exit;

/** Public editorial post types => [singular, plural, slug, icon]. */
function er_public_types(): array {
	return [
		'er_destination' => [ __( 'Destination', 'egypt-roamer-core' ), __( 'Destinations', 'egypt-roamer-core' ), 'destinations', 'dashicons-location-alt' ],
		'er_tour'        => [ __( 'Tour', 'egypt-roamer-core' ), __( 'Tours', 'egypt-roamer-core' ), 'tours', 'dashicons-flag' ],
		'er_experience'  => [ __( 'Experience', 'egypt-roamer-core' ), __( 'Experiences', 'egypt-roamer-core' ), 'experiences', 'dashicons-star-filled' ],
		'er_activity'    => [ __( 'Activity', 'egypt-roamer-core' ), __( 'Activities', 'egypt-roamer-core' ), 'activities', 'dashicons-palmtree' ],
		'er_guide'       => [ __( 'Guide', 'egypt-roamer-core' ), __( 'Guides', 'egypt-roamer-core' ), 'guides', 'dashicons-book-alt' ],
	];
}

/** Public editorial type keys (no labels — safe to call before init). */
function er_public_type_keys(): array {
	return [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_guide' ];
}

/** Types that carry a commercial (affiliate) layer on top of editorial content. */
function er_commercial_types(): array {
	return [ 'er_tour', 'er_experience', 'er_activity' ];
}

function er_type_labels( string $singular, string $plural ): array {
	return [
		'name'               => $plural,
		'singular_name'      => $singular,
		/* translators: %s: singular post type name */
		'add_new_item'       => sprintf( __( 'Add new %s', 'egypt-roamer-core' ), $singular ),
		/* translators: %s: singular post type name */
		'edit_item'          => sprintf( __( 'Edit %s', 'egypt-roamer-core' ), $singular ),
		/* translators: %s: singular post type name */
		'new_item'           => sprintf( __( 'New %s', 'egypt-roamer-core' ), $singular ),
		/* translators: %s: singular post type name */
		'view_item'          => sprintf( __( 'View %s', 'egypt-roamer-core' ), $singular ),
		/* translators: %s: plural post type name */
		'search_items'       => sprintf( __( 'Search %s', 'egypt-roamer-core' ), $plural ),
		'not_found'          => __( 'Nothing found.', 'egypt-roamer-core' ),
		'not_found_in_trash' => __( 'Nothing found in Trash.', 'egypt-roamer-core' ),
		/* translators: %s: plural post type name */
		'all_items'          => sprintf( __( 'All %s', 'egypt-roamer-core' ), $plural ),
		'menu_name'          => $plural,
	];
}

function er_register_content_types(): void {
	foreach ( er_public_types() as $type => [ $singular, $plural, $slug, $icon ] ) {
		register_post_type( $type, [
			'labels'        => er_type_labels( $singular, $plural ),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => $slug,
			'rewrite'       => [ 'slug' => $slug, 'with_front' => false ],
			'menu_icon'     => $icon,
			'menu_position' => 21,
			'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'page-attributes', 'custom-fields' ],
			'hierarchical'  => false,
		] );
	}

	// Affiliate records: admin-only, never publicly queryable (no thin pages, no leaks).
	$private = [
		'er_provider' => [ __( 'Affiliate Provider', 'egypt-roamer-core' ), __( 'Affiliate Providers', 'egypt-roamer-core' ), [ 'title', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ] ],
		'er_offer'    => [ __( 'Affiliate Offer', 'egypt-roamer-core' ), __( 'Affiliate Offers', 'egypt-roamer-core' ), [ 'title', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ] ],
	];
	foreach ( $private as $type => [ $singular, $plural, $supports ] ) {
		register_post_type( $type, [
			'labels'              => er_type_labels( $singular, $plural ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'egypt-roamer',
			'show_in_rest'        => true, // block editor; REST access is capability-gated below
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => $supports,
			'capability_type'     => 'post',
		] );
	}

	// Private lead records.
	register_post_type( 'er_message', [
		'labels'          => er_type_labels( __( 'Contact message', 'egypt-roamer-core' ), __( 'Contact messages', 'egypt-roamer-core' ) ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'egypt-roamer',
		'show_in_rest'    => false,
		'rewrite'         => false,
		'query_var'       => false,
		'supports'        => [ 'title', 'editor', 'custom-fields' ],
		'capability_type' => 'post',
		'capabilities'    => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'    => true,
	] );

	// Taxonomies — used for filtering and grouping, not as standalone thin pages.
	$tax_common = [
		'show_ui'            => true,
		'show_in_rest'       => true,
		'show_admin_column'  => true,
		'public'             => false,
		'publicly_queryable' => false,
		'rewrite'            => false,
		'query_var'          => false,
	];
	register_taxonomy( 'er_region', [ 'er_destination' ], $tax_common + [
		'hierarchical' => true,
		'labels'       => [ 'name' => __( 'Regions', 'egypt-roamer-core' ), 'singular_name' => __( 'Region', 'egypt-roamer-core' ) ],
	] );
	register_taxonomy( 'er_travel_style', [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_offer' ], $tax_common + [
		'hierarchical' => true,
		'labels'       => [ 'name' => __( 'Travel styles', 'egypt-roamer-core' ), 'singular_name' => __( 'Travel style', 'egypt-roamer-core' ) ],
	] );
	register_taxonomy( 'er_offer_type', [ 'er_offer' ], $tax_common + [
		'hierarchical' => true,
		'labels'       => [ 'name' => __( 'Offer categories', 'egypt-roamer-core' ), 'singular_name' => __( 'Offer category', 'egypt-roamer-core' ) ],
	] );
	register_taxonomy( 'er_guide_topic', [ 'er_guide' ], $tax_common + [
		'hierarchical' => true,
		'labels'       => [ 'name' => __( 'Guide topics', 'egypt-roamer-core' ), 'singular_name' => __( 'Guide topic', 'egypt-roamer-core' ) ],
	] );
}
add_action( 'init', 'er_register_content_types', 5 );

/** Offer categories used by the homepage partner tabs. Created once, editable after. */
add_action( 'init', static function () {
	if ( get_option( 'er_default_terms' ) ) {
		return;
	}
	$terms = [
		'hotels'    => __( 'Hotels', 'egypt-roamer-core' ),
		'tours'     => __( 'Tours & Activities', 'egypt-roamer-core' ),
		'cruises'   => __( 'Nile Cruises', 'egypt-roamer-core' ),
		'transfers' => __( 'Transfers', 'egypt-roamer-core' ),
		'cars'      => __( 'Car Rental', 'egypt-roamer-core' ),
	];
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'er_offer_type' ) ) {
			wp_insert_term( $name, 'er_offer_type', [ 'slug' => $slug ] );
		}
	}
	update_option( 'er_default_terms', 1 );
}, 20 );

/** Top-level admin menu that groups affiliate records, reports, leads and settings. */
add_action( 'admin_menu', static function () {
	add_menu_page(
		__( 'Egypt Roamer', 'egypt-roamer-core' ),
		__( 'Egypt Roamer', 'egypt-roamer-core' ),
		'edit_posts',
		'egypt-roamer',
		'er_render_dashboard_page',
		'dashicons-chart-line',
		20
	);
}, 5 );

/** Keep private affiliate records out of anonymous REST reads. */
add_filter( 'rest_pre_dispatch', static function ( $result, $server, $request ) {
	$route = $request->get_route();
	if ( preg_match( '#^/wp/v2/(er_provider|er_offer|er_message)\b#', $route ) && ! current_user_can( 'edit_posts' ) ) {
		return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'egypt-roamer-core' ), [ 'status' => rest_authorization_required_code() ] );
	}
	return $result;
}, 10, 3 );
