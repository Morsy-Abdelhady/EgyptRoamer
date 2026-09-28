<?php
/**
 * Template API — the only functions a theme needs. Keeping queries here means
 * a replacement theme gets the same relations, offers and rules for free.
 */

defined( 'ABSPATH' ) || exit;

/** Published, current-language posts referenced by a relation field. */
function er_get_related( int $post_id, string $key ): array {
	$ids = get_post_meta( $post_id, $key, false );
	if ( ! $ids ) {
		// Translations share relations: fall back to the relation on any sibling translation.
		foreach ( er_translation_group( $post_id ) as $sibling ) {
			$ids = get_post_meta( $sibling, $key, false );
			if ( $ids ) {
				break;
			}
		}
	}
	$out = [];
	foreach ( array_unique( array_map( 'intval', (array) $ids ) ) as $id ) {
		$id   = er_translated_post_id( $id );
		$post = get_post( $id );
		if ( $post && 'publish' === $post->post_status ) {
			$out[ $id ] = $post;
		}
	}
	return array_values( $out );
}

/** Posts of $types whose $key relation points at $post_id (any translation). */
function er_get_referencing( int $post_id, $types, string $key = '_er_destination', int $limit = 12 ): array {
	return get_posts( [
		'post_type'        => (array) $types,
		'post_status'      => 'publish',
		'numberposts'      => $limit,
		'meta_query'       => [ [ 'key' => $key, 'value' => er_translation_group( $post_id ), 'compare' => 'IN', 'type' => 'NUMERIC' ] ],
		'orderby'          => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
		'suppress_filters' => false,
	] );
}

/** Live offers attached to a page, by the page's type. */
function er_offers_for_post( int $post_id, int $limit = 6 ): array {
	$map  = [ 'er_destination' => 'destination', 'er_tour' => 'tour', 'er_experience' => 'experience', 'er_activity' => 'activity' ];
	$type = $map[ get_post_type( $post_id ) ] ?? '';
	return $type ? er_get_offers( [ $type => $post_id, 'limit' => $limit ] ) : [];
}

/** Estimated reading time in minutes (≈ 220 words per minute, CJK-aware). */
function er_read_minutes( $post ): int {
	$text  = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post ) ) );
	$words = str_word_count( $text );
	$cjk   = preg_match_all( '/[\x{4E00}-\x{9FFF}]/u', $text );
	return max( 1, (int) round( ( $words + $cjk / 2 ) / 220 ) );
}

/** Line fields ("one per line") as an array. */
function er_lines( int $post_id, string $key ): array {
	$lines = preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $post_id, $key, true ) );
	return array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
}

/** Attachment → { src, alt, w, h, sizes{width:url} } for scripts that need responsive sources. */
function er_image_payload( int $attachment_id ): ?array {
	if ( ! $attachment_id ) {
		return null;
	}
	$full = wp_get_attachment_image_src( $attachment_id, 'full' );
	if ( ! $full ) {
		return null;
	}
	$sizes = [];
	foreach ( get_intermediate_image_sizes() as $size ) {
		$src = wp_get_attachment_image_src( $attachment_id, $size );
		if ( $src && ! empty( $src[1] ) && abs( $src[1] / max( 1, $src[2] ) - $full[1] / max( 1, $full[2] ) ) < 0.05 ) {
			$sizes[ (int) $src[1] ] = $src[0]; // same aspect ratio only (no hard crops)
		}
	}
	$sizes[ (int) $full[1] ] = $full[0];
	ksort( $sizes );
	return [
		'src'   => $full[0],
		'alt'   => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		'w'     => (int) $full[1],
		'h'     => (int) $full[2],
		'sizes' => $sizes,
	];
}

/* -------------------------------------------------------------------------- */
/* Term fields: travel styles (homepage moods) and offer categories (tabs)     */
/* -------------------------------------------------------------------------- */

function er_term_fields( string $taxonomy ): array {
	$icons = [
		'i-temple' => 'Temple', 'i-palm' => 'Palm', 'i-diamond' => 'Diamond', 'i-mountain' => 'Mountain', 'i-lantern' => 'Lantern',
		'i-food' => 'Food', 'i-leaf' => 'Leaf', 'i-bed' => 'Bed', 'i-compass' => 'Compass', 'i-ship' => 'Ship', 'i-route' => 'Route',
		'i-car' => 'Car', 'i-wave' => 'Wave', 'i-sun' => 'Sun',
	];
	$fields = [
		'er_travel_style' => [
			'_er_word'        => [ 'type' => 'text', 'label' => __( 'Headline word (e.g. Ancient)', 'egypt-roamer-core' ) ],
			'_er_icon'        => [ 'type' => 'select', 'label' => __( 'Icon', 'egypt-roamer-core' ), 'options' => $icons, 'default' => 'i-compass' ],
			'_er_tint'        => [ 'type' => 'select', 'label' => __( 'Colour grade', 'egypt-roamer-core' ), 'options' => [ 'clay' => 'Clay', 'teal' => 'Teal', 'gold' => 'Gold', 'nile' => 'Nile' ], 'default' => 'clay' ],
			'_er_image'       => [ 'type' => 'image', 'label' => __( 'Image', 'egypt-roamer-core' ) ],
			'_er_destination' => [ 'type' => 'post', 'post_type' => 'er_destination', 'label' => __( 'Where to go (destination)', 'egypt-roamer-core' ) ],
		],
		'er_offer_type'   => [
			'_er_icon'     => [ 'type' => 'select', 'label' => __( 'Icon', 'egypt-roamer-core' ), 'options' => $icons, 'default' => 'i-compass' ],
			'_er_headline' => [ 'type' => 'text', 'label' => __( 'Headline', 'egypt-roamer-core' ) ],
			'_er_copy'     => [ 'type' => 'textarea', 'label' => __( 'Intro copy', 'egypt-roamer-core' ) ],
			'_er_image'    => [ 'type' => 'image', 'label' => __( 'Image', 'egypt-roamer-core' ) ],
			'_er_trust'    => [ 'type' => 'lines', 'label' => __( 'Trust points (one per line — only verifiable facts)', 'egypt-roamer-core' ) ],
			'_er_compare_offer' => [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( 'Main “compare” offer (e.g. the provider search page)', 'egypt-roamer-core' ) ],
			'_er_compare_label' => [ 'type' => 'text', 'label' => __( 'Main button label', 'egypt-roamer-core' ) ],
		],
	];
	return $fields[ $taxonomy ] ?? [];
}

function er_term_meta( int $term_id, string $key ) {
	$field = er_term_fields( (string) get_term_field( 'taxonomy', $term_id ) )[ $key ] ?? [];
	$value = get_term_meta( $term_id, $key, true );
	return ( '' === $value && isset( $field['default'] ) ) ? $field['default'] : $value;
}

foreach ( [ 'er_travel_style', 'er_offer_type' ] as $er_tax ) {
	add_action( "{$er_tax}_edit_form_fields", static function ( $term, $taxonomy ) {
		wp_nonce_field( 'er_save_term', 'er_term_nonce' );
		foreach ( er_term_fields( $taxonomy ) as $key => $field ) {
			echo '<tr class="form-field"><th scope="row"></th><td>';
			er_render_field( 'er-t-' . $key, 'er_term[' . $key . ']', $field, er_term_meta( (int) $term->term_id, $key ) );
			echo '</td></tr>';
		}
	}, 10, 2 );
	add_action( "{$er_tax}_add_form_fields", static function ( $taxonomy ) {
		wp_nonce_field( 'er_save_term', 'er_term_nonce' );
		foreach ( er_term_fields( $taxonomy ) as $key => $field ) {
			er_render_field( 'er-t-' . $key, 'er_term[' . $key . ']', $field, $field['default'] ?? '' );
		}
	} );
	$save = static function ( $term_id ) use ( $er_tax ) {
		if ( ! isset( $_POST['er_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['er_term_nonce'] ) ), 'er_save_term' ) || ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		$input = isset( $_POST['er_term'] ) && is_array( $_POST['er_term'] ) ? wp_unslash( $_POST['er_term'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- per field
		foreach ( er_term_fields( $er_tax ) as $key => $field ) {
			$value = er_sanitize_field( $field, $input[ $key ] ?? '' );
			if ( '' === $value || 0 === $value ) {
				delete_term_meta( $term_id, $key );
			} else {
				update_term_meta( $term_id, $key, $value );
			}
		}
	};
	add_action( "created_{$er_tax}", $save );
	add_action( "edited_{$er_tax}", $save );
}

/* -------------------------------------------------------------------------- */
/* User enumeration: logins must not be discoverable by anonymous visitors     */
/* -------------------------------------------------------------------------- */

// REST: /wp/v2/users lists every user with published content (login slug included). Visitors
// never need it; logged-in editors keep it for the block editor.
add_filter( 'rest_endpoints', static function ( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( str_starts_with( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

// Author archives (/?author=1 → /author/{login}/) are not part of this site's structure.
add_action( 'template_redirect', static function () {
	if ( is_author() || ( isset( $_GET['author'] ) && ! is_admin() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}, 1 );
add_filter( 'author_link', static fn() => home_url( '/' ) );

// oEmbed responses name the author and link to the author archive.
add_filter( 'oembed_response_data', static function ( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
} );
