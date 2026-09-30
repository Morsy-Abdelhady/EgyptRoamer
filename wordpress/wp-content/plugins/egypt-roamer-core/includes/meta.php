<?php
/**
 * Structured fields per content type, rendered in native meta boxes (no ACF
 * dependency). Long-form editorial content lives in the block editor; these
 * fields hold the facts templates and the affiliate engine need.
 *
 * Relations with several values ("posts") are stored as one meta row per ID so
 * they can be queried with a plain meta_query.
 */

defined( 'ABSPATH' ) || exit;

function er_cta_options(): array {
	return [
		'check_price'        => __( 'Check Price', 'egypt-roamer-core' ),
		'check_availability' => __( 'Check Availability', 'egypt-roamer-core' ),
		'view_offer'         => __( 'View Offer', 'egypt-roamer-core' ),
		'view_deal'          => __( 'View Deal', 'egypt-roamer-core' ),
		'view_tour'          => __( 'View Tour', 'egypt-roamer-core' ),
		'view_cruise'        => __( 'View Cruise', 'egypt-roamer-core' ),
		'see_hotel'          => __( 'See Hotel', 'egypt-roamer-core' ),
		'compare_options'    => __( 'Compare Options', 'egypt-roamer-core' ),
		'book_now'           => __( 'Book Now', 'egypt-roamer-core' ),
		'book_with_partner'  => __( 'Book with Partner', 'egypt-roamer-core' ),
	];
}

/** Field schema per post type. */
function er_meta_fields( string $post_type ): array {
	$indexable = [
		'_er_indexable' => [
			'type'  => 'checkbox',
			'label' => __( 'Ready to index — this page has real, original content', 'egypt-roamer-core' ),
			'help'  => __( 'Until ticked, the page is published with noindex and kept out of the XML sitemap.', 'egypt-roamer-core' ),
		],
	];
	$commercial = [
		'h_facts'          => [ 'type' => 'heading', 'label' => __( 'Facts', 'egypt-roamer-core' ) ],
		'_er_location'     => [ 'type' => 'text', 'label' => __( 'Location', 'egypt-roamer-core' ), 'help' => __( 'e.g. Giza, Cairo', 'egypt-roamer-core' ) ],
		'_er_duration'     => [ 'type' => 'text', 'label' => __( 'Duration', 'egypt-roamer-core' ), 'help' => __( 'e.g. Half day, 4 hours, 2 days', 'egypt-roamer-core' ) ],
		'_er_meeting'      => [ 'type' => 'text', 'label' => __( 'Starting point / meeting point', 'egypt-roamer-core' ) ],
		'_er_best_time'    => [ 'type' => 'text', 'label' => __( 'Best time', 'egypt-roamer-core' ) ],
		'_er_badge'        => [ 'type' => 'text', 'label' => __( 'Badge (optional)', 'egypt-roamer-core' ), 'help' => __( 'Only claims you can support, e.g. Editor\'s pick.', 'egypt-roamer-core' ) ],
		'_er_destination'  => [ 'type' => 'posts', 'post_type' => 'er_destination', 'label' => __( 'Destinations', 'egypt-roamer-core' ) ],
		'_er_activity'     => [ 'type' => 'posts', 'post_type' => 'er_activity', 'label' => __( 'Related activities', 'egypt-roamer-core' ) ],
		'h_decide'         => [ 'type' => 'heading', 'label' => __( 'Decision support (original editorial — not copied from providers)', 'egypt-roamer-core' ) ],
		'_er_who_for'      => [ 'type' => 'lines', 'label' => __( 'Who it is for (one per line)', 'egypt-roamer-core' ) ],
		'_er_not_for'      => [ 'type' => 'lines', 'label' => __( 'Who may prefer something else (one per line)', 'egypt-roamer-core' ) ],
		'_er_good_to_know' => [ 'type' => 'lines', 'label' => __( 'Good to know / practical tips (one per line)', 'egypt-roamer-core' ) ],
		'_er_alternatives' => [ 'type' => 'posts', 'post_type' => er_commercial_types(), 'label' => __( 'Alternatives to compare', 'egypt-roamer-core' ) ],
	];

	$fields = [
		'er_destination' => [
			'_er_tagline'       => [ 'type' => 'text', 'label' => __( 'Tagline', 'egypt-roamer-core' ), 'help' => __( 'e.g. The city of a thousand minarets', 'egypt-roamer-core' ) ],
			'_er_region_label'  => [ 'type' => 'text', 'label' => __( 'Region label', 'egypt-roamer-core' ), 'help' => __( 'e.g. Capital · Nile Valley', 'egypt-roamer-core' ) ],
			'_er_best_time'     => [ 'type' => 'text', 'label' => __( 'Best time to visit', 'egypt-roamer-core' ), 'help' => __( 'e.g. Oct – Apr', 'egypt-roamer-core' ) ],
			'_er_getting_there' => [ 'type' => 'text', 'label' => __( 'Getting there (short)', 'egypt-roamer-core' ), 'help' => __( 'e.g. 1h flight from Cairo', 'egypt-roamer-core' ) ],
			'_er_map_reach'     => [ 'type' => 'text', 'label' => __( 'Getting there (map card)', 'egypt-roamer-core' ) ],
			'_er_highlights'    => [ 'type' => 'lines', 'label' => __( 'Highlights (one per line)', 'egypt-roamer-core' ) ],
			'_er_lat'           => [ 'type' => 'number', 'label' => __( 'Latitude', 'egypt-roamer-core' ), 'step' => '0.0001' ],
			'_er_lng'           => [ 'type' => 'number', 'label' => __( 'Longitude', 'egypt-roamer-core' ), 'step' => '0.0001' ],
			'_er_nights'        => [ 'type' => 'number', 'label' => __( 'Trip builder: relative nights weight', 'egypt-roamer-core' ), 'step' => '0.5', 'help' => __( 'How long visitors typically stay, relative to other destinations (e.g. Cairo 3, Alexandria 1.5).', 'egypt-roamer-core' ) ],
		] + $indexable,
		'er_tour'        => $commercial + $indexable,
		'er_experience'  => $commercial + $indexable,
		'er_activity'    => [
			'_er_destination'  => [ 'type' => 'posts', 'post_type' => 'er_destination', 'label' => __( 'Where to do it (destinations)', 'egypt-roamer-core' ) ],
			'_er_best_time'    => [ 'type' => 'text', 'label' => __( 'Best time', 'egypt-roamer-core' ) ],
			'_er_who_for'      => [ 'type' => 'lines', 'label' => __( 'Who it is for (one per line)', 'egypt-roamer-core' ) ],
			'_er_not_for'      => [ 'type' => 'lines', 'label' => __( 'Who may prefer something else (one per line)', 'egypt-roamer-core' ) ],
			'_er_good_to_know' => [ 'type' => 'lines', 'label' => __( 'Good to know / practical tips (one per line)', 'egypt-roamer-core' ) ],
		] + $indexable,
		'er_guide'       => [
			'_er_destination' => [ 'type' => 'posts', 'post_type' => 'er_destination', 'label' => __( 'Related destinations', 'egypt-roamer-core' ) ],
			'_er_related'     => [ 'type' => 'posts', 'post_type' => er_commercial_types(), 'label' => __( 'Related tours, experiences & activities', 'egypt-roamer-core' ) ],
		] + $indexable,
		'post'           => $indexable,
		'er_provider'    => [
			'_er_website'   => [ 'type' => 'url', 'label' => __( 'Provider website', 'egypt-roamer-core' ) ],
			'_er_domains'   => [ 'type' => 'lines', 'label' => __( 'Allowed redirect domains (one per line)', 'egypt-roamer-core' ), 'help' => __( 'Security: /go/ links for this provider may only redirect to these hosts and their sub-domains, e.g. getyourguide.com. Required.', 'egypt-roamer-core' ) ],
			'_er_default_url' => [ 'type' => 'url', 'label' => __( 'Default affiliate URL (optional)', 'egypt-roamer-core' ), 'help' => __( 'Used by /go/{provider-slug}/ — e.g. your tracked link to the provider home page.', 'egypt-roamer-core' ) ],
			'_er_status'    => [ 'type' => 'select', 'label' => __( 'Status', 'egypt-roamer-core' ), 'options' => [ 'active' => __( 'Active', 'egypt-roamer-core' ), 'inactive' => __( 'Inactive — pause every offer', 'egypt-roamer-core' ) ], 'default' => 'active' ],
		],
		'er_offer'       => [
			'h_link'         => [ 'type' => 'heading', 'label' => __( 'Link', 'egypt-roamer-core' ) ],
			'_er_provider'   => [ 'type' => 'post', 'post_type' => 'er_provider', 'label' => __( 'Provider', 'egypt-roamer-core' ) ],
			'_er_target_url' => [ 'type' => 'url', 'label' => __( 'Target URL (product page at the provider)', 'egypt-roamer-core' ) ],
			'_er_affiliate_url' => [ 'type' => 'url', 'label' => __( 'Affiliate URL (tracked deep link)', 'egypt-roamer-core' ), 'help' => __( 'Used when set; otherwise the target URL is used with the tracking parameters below.', 'egypt-roamer-core' ) ],
			'_er_search_template' => [ 'type' => 'url_template', 'label' => __( 'Search URL template (optional, for the homepage finder)', 'egypt-roamer-core' ), 'help' => __( 'Placeholders: {destination} {month} {adults}. Example: https://partner.example/search?q={destination}&date={month}', 'egypt-roamer-core' ) ],
			'h_cta'          => [ 'type' => 'heading', 'label' => __( 'Call to action', 'egypt-roamer-core' ) ],
			'_er_cta'        => [ 'type' => 'select', 'label' => __( 'CTA label', 'egypt-roamer-core' ), 'options' => er_cta_options(), 'default' => 'check_availability' ],
			'_er_cta_custom' => [ 'type' => 'text', 'label' => __( 'Custom CTA label (overrides the list)', 'egypt-roamer-core' ) ],
			'_er_location'   => [ 'type' => 'text', 'label' => __( 'Location shown on the card', 'egypt-roamer-core' ) ],
			'_er_meta_line'  => [ 'type' => 'text', 'label' => __( 'Short facts line', 'egypt-roamer-core' ), 'help' => __( 'e.g. Full day · Egyptologist guide', 'egypt-roamer-core' ) ],
			'_er_badge'      => [ 'type' => 'text', 'label' => __( 'Badge (optional)', 'egypt-roamer-core' ), 'help' => __( 'Only claims you can support, e.g. Editor\'s pick.', 'egypt-roamer-core' ) ],
			'_er_price_from' => [ 'type' => 'number', 'label' => __( 'Price from (optional)', 'egypt-roamer-core' ), 'step' => '0.01', 'help' => __( 'Shown only together with the date you checked it at the provider.', 'egypt-roamer-core' ) ],
			'_er_price_unit' => [ 'type' => 'select', 'label' => __( 'Price unit', 'egypt-roamer-core' ), 'options' => [ 'person' => __( 'per person', 'egypt-roamer-core' ), 'night' => __( 'per night', 'egypt-roamer-core' ), 'car' => __( 'per car', 'egypt-roamer-core' ), 'day' => __( 'per day', 'egypt-roamer-core' ), 'group' => __( 'per group', 'egypt-roamer-core' ) ], 'default' => 'person' ],
			'_er_currency'   => [ 'type' => 'text', 'label' => __( 'Currency (ISO code)', 'egypt-roamer-core' ), 'default' => 'USD' ],
			'_er_price_checked' => [ 'type' => 'date', 'label' => __( 'Price checked on', 'egypt-roamer-core' ) ],
			'h_rel'          => [ 'type' => 'heading', 'label' => __( 'Where this offer appears', 'egypt-roamer-core' ) ],
			'_er_destination' => [ 'type' => 'post', 'post_type' => 'er_destination', 'label' => __( 'Destination', 'egypt-roamer-core' ) ],
			'_er_tour'       => [ 'type' => 'post', 'post_type' => 'er_tour', 'label' => __( 'Tour', 'egypt-roamer-core' ) ],
			'_er_experience' => [ 'type' => 'post', 'post_type' => 'er_experience', 'label' => __( 'Experience', 'egypt-roamer-core' ) ],
			'_er_activity'   => [ 'type' => 'post', 'post_type' => 'er_activity', 'label' => __( 'Activity', 'egypt-roamer-core' ) ],
			'h_track'        => [ 'type' => 'heading', 'label' => __( 'Tracking', 'egypt-roamer-core' ) ],
			'_er_utm_source' => [ 'type' => 'text', 'label' => __( 'Source (utm_source)', 'egypt-roamer-core' ) ],
			'_er_utm_medium' => [ 'type' => 'text', 'label' => __( 'Medium (utm_medium)', 'egypt-roamer-core' ) ],
			'_er_utm_campaign' => [ 'type' => 'text', 'label' => __( 'Campaign (utm_campaign)', 'egypt-roamer-core' ) ],
			'_er_params'     => [ 'type' => 'lines', 'label' => __( 'Extra tracking parameters (key=value, one per line)', 'egypt-roamer-core' ), 'help' => __( 'Sub-ID placeholders: {placement} {page} {offer} {lang} {provider}. Example: campaign=er-{lang}-{placement}-{page}', 'egypt-roamer-core' ) ],
			'h_sched'        => [ 'type' => 'heading', 'label' => __( 'Status & schedule', 'egypt-roamer-core' ) ],
			'_er_status'     => [ 'type' => 'select', 'label' => __( 'Offer status', 'egypt-roamer-core' ), 'options' => [ 'active' => __( 'Active', 'egypt-roamer-core' ), 'paused' => __( 'Paused', 'egypt-roamer-core' ) ], 'default' => 'active' ],
			'_er_priority'   => [ 'type' => 'number', 'label' => __( 'Priority (higher shows first)', 'egypt-roamer-core' ), 'step' => '1', 'default' => 10 ],
			'_er_start'      => [ 'type' => 'date', 'label' => __( 'Start date (optional)', 'egypt-roamer-core' ) ],
			'_er_end'        => [ 'type' => 'date', 'label' => __( 'End date (optional)', 'egypt-roamer-core' ) ],
		],
	];

	return $fields[ $post_type ] ?? [];
}

/** Every post type that has fields. */
function er_meta_post_types(): array {
	return [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_guide', 'post', 'er_provider', 'er_offer' ];
}

/** Register meta for REST/the block editor so fields are real, typed data. */
add_action( 'init', static function () {
	foreach ( er_meta_post_types() as $type ) {
		foreach ( er_meta_fields( $type ) as $key => $field ) {
			if ( 'heading' === $field['type'] ) {
				continue;
			}
			$multi = 'posts' === $field['type'];
			$schema_type = match ( $field['type'] ) {
				'number' => 'number',
				'checkbox', 'post', 'posts', 'image' => 'integer',
				default => 'string',
			};
			register_post_meta( $type, $key, [
				'type'          => $schema_type,
				'single'        => ! $multi,
				'show_in_rest'  => 'er_provider' !== $type && 'er_offer' !== $type,
				'auth_callback' => static fn () => current_user_can( 'edit_posts' ),
				'sanitize_callback' => static fn ( $value ) => $multi ? absint( $value ) : er_sanitize_field( $field, $value ),
			] );
		}
	}
}, 15 );

/** Read a field value (multi relations return an array of IDs). */
function er_meta( int $post_id, string $key, ?array $field = null ) {
	$field = $field ?? ( er_meta_fields( (string) get_post_type( $post_id ) )[ $key ] ?? [ 'type' => 'text' ] );
	if ( 'posts' === $field['type'] ) {
		return array_map( 'intval', get_post_meta( $post_id, $key, false ) );
	}
	$value = get_post_meta( $post_id, $key, true );
	if ( '' === $value && isset( $field['default'] ) && ! metadata_exists( 'post', $post_id, $key ) ) {
		return $field['default'];
	}
	return $value;
}

add_action( 'add_meta_boxes', static function ( $post_type ) {
	if ( ! er_meta_fields( $post_type ) ) {
		return;
	}
	add_meta_box( 'er_fields', __( 'Egypt Roamer details', 'egypt-roamer-core' ), 'er_render_meta_box', $post_type, 'normal', 'high' );
} );

function er_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'er_save_meta', 'er_meta_nonce' );
	echo '<div class="er-fields">';
	foreach ( er_meta_fields( $post->post_type ) as $key => $field ) {
		if ( 'heading' === $field['type'] ) {
			echo '<h3 class="er-fields__heading">' . esc_html( $field['label'] ) . '</h3>';
			continue;
		}
		er_render_field( 'er-' . $key, 'er_meta[' . $key . ']', $field, er_meta( $post->ID, $key, $field ) );
	}
	if ( 'er_offer' === $post->post_type ) {
		er_render_offer_link_box( $post );
	}
	echo '</div>';
}

add_action( 'save_post', static function ( $post_id, $post ) {
	if ( ! isset( $_POST['er_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['er_meta_nonce'] ) ), 'er_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$input = isset( $_POST['er_meta'] ) && is_array( $_POST['er_meta'] ) ? wp_unslash( $_POST['er_meta'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field below
	foreach ( er_meta_fields( $post->post_type ) as $key => $field ) {
		if ( 'heading' === $field['type'] ) {
			continue;
		}
		$value = er_sanitize_field( $field, $input[ $key ] ?? ( 'posts' === $field['type'] ? [] : '' ) );
		if ( 'posts' === $field['type'] ) {
			delete_post_meta( $post_id, $key );
			foreach ( $value as $id ) {
				add_post_meta( $post_id, $key, $id );
			}
		} elseif ( '' === $value || ( 'checkbox' === $field['type'] && ! $value ) ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}, 10, 2 );

/** "Index" column so unindexed published pages are visible at a glance. */
add_action( 'admin_init', static function () {
	foreach ( er_gated_types() as $type ) {
		add_filter( "manage_{$type}_posts_columns", static function ( $cols ) {
			$cols['er_index'] = __( 'Index', 'egypt-roamer-core' );
			return $cols;
		} );
		add_action( "manage_{$type}_posts_custom_column", static function ( $col, $post_id ) {
			if ( 'er_index' === $col ) {
				echo er_is_indexable( (int) $post_id ) ? '<span class="er-pill er-pill--ok">index</span>' : '<span class="er-pill">noindex</span>';
			}
		}, 10, 2 );
	}
} );
