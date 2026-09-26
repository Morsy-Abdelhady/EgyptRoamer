<?php
/**
 * Affiliate engine: which offers are live, where they may redirect, how the
 * outbound URL is built, and how a CTA is rendered.
 *
 * Templates never contain affiliate URLs — they call er_offer_cta_html() or
 * er_offer_data(), which always point at the site's own /go/{slug}/ endpoint.
 */

defined( 'ABSPATH' ) || exit;

/** Allowed redirect hosts for a provider (lowercase, no scheme). */
function er_provider_domains( int $provider_id ): array {
	$lines = preg_split( '/\s+/', strtolower( (string) get_post_meta( $provider_id, '_er_domains', true ) ) );
	$hosts = [];
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$host = wp_parse_url( str_contains( $line, '://' ) ? $line : 'https://' . $line, PHP_URL_HOST );
		if ( $host && preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $host ) ) {
			$hosts[] = preg_replace( '/^www\./', '', $host );
		}
	}
	return array_values( array_unique( $hosts ) );
}

/** Does $url's host equal an allowed domain or one of its sub-domains? */
function er_host_allowed( string $url, array $domains ): bool {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( '' === $host ) {
		return false;
	}
	foreach ( $domains as $d ) {
		if ( $host === $d || str_ends_with( $host, '.' . $d ) ) {
			return true;
		}
	}
	return false;
}

function er_provider_is_active( int $provider_id ): bool {
	return $provider_id
		&& 'er_provider' === get_post_type( $provider_id )
		&& 'publish' === get_post_status( $provider_id )
		&& 'inactive' !== get_post_meta( $provider_id, '_er_status', true );
}

/** Is the offer published, active, inside its schedule and backed by an active provider? */
function er_offer_is_live( int $offer_id ): bool {
	if ( 'er_offer' !== get_post_type( $offer_id ) || 'publish' !== get_post_status( $offer_id ) ) {
		return false;
	}
	if ( 'paused' === get_post_meta( $offer_id, '_er_status', true ) ) {
		return false;
	}
	$today = current_time( 'Y-m-d' );
	$start = (string) get_post_meta( $offer_id, '_er_start', true );
	$end   = (string) get_post_meta( $offer_id, '_er_end', true );
	if ( ( $start && $start > $today ) || ( $end && $end < $today ) ) {
		return false;
	}
	return er_provider_is_active( (int) get_post_meta( $offer_id, '_er_provider', true ) );
}

/** Term IDs for slugs, expanded to every translation of those terms (Polylang). */
function er_term_group_ids( string $taxonomy, array $slugs ): array {
	static $cache = [];
	$key = $taxonomy . ':' . implode( ',', $slugs );
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$cache[ $key ] = er_term_group_ids_uncached( $taxonomy, $slugs );
	return $cache[ $key ];
}

function er_term_group_ids_uncached( string $taxonomy, array $slugs ): array {
	$ids = get_terms( [ 'taxonomy' => $taxonomy, 'slug' => array_map( 'sanitize_title', $slugs ), 'hide_empty' => false, 'fields' => 'ids', 'lang' => '' ] );
	if ( is_wp_error( $ids ) ) {
		return [];
	}
	$all = array_map( 'intval', $ids );
	if ( function_exists( 'pll_get_term_translations' ) ) {
		foreach ( $ids as $id ) {
			$all = array_merge( $all, array_map( 'intval', array_values( (array) pll_get_term_translations( (int) $id ) ) ) );
		}
	}
	return array_values( array_unique( $all ) );
}

/** Keep sub-ID values safe for any network: letters, digits, dash, underscore. */
function er_subid( string $value ): string {
	return substr( preg_replace( '/[^a-z0-9_-]+/', '-', strtolower( $value ) ), 0, 60 );
}

/**
 * Build and validate the outbound URL for an offer.
 *
 * @param array $ctx placement, page (source slug), search [destination, month, adults]
 * @return string|WP_Error
 */
function er_offer_destination_url( int $offer_id, array $ctx = [] ) {
	$provider_id = (int) get_post_meta( $offer_id, '_er_provider', true );
	if ( ! $provider_id || 'er_provider' !== get_post_type( $provider_id ) ) {
		return new WP_Error( 'er_no_provider', __( 'no provider selected', 'egypt-roamer-core' ) );
	}
	$domains = er_provider_domains( $provider_id );
	if ( ! $domains ) {
		return new WP_Error( 'er_no_domains', __( 'the provider has no allowed redirect domains', 'egypt-roamer-core' ) );
	}

	$url      = '';
	$template = (string) get_post_meta( $offer_id, '_er_search_template', true );
	if ( ! empty( $ctx['search'] ) && $template ) {
		$repl = [];
		foreach ( [ 'destination', 'month', 'adults' ] as $k ) {
			$repl[ '{' . $k . '}' ] = rawurlencode( (string) ( $ctx['search'][ $k ] ?? '' ) );
		}
		$url = strtr( $template, $repl );
	}
	if ( ! $url ) {
		$url = (string) get_post_meta( $offer_id, '_er_affiliate_url', true ) ?: (string) get_post_meta( $offer_id, '_er_target_url', true );
	}
	if ( ! $url ) {
		return new WP_Error( 'er_no_url', __( 'no affiliate or target URL', 'egypt-roamer-core' ) );
	}
	if ( ! in_array( wp_parse_url( $url, PHP_URL_SCHEME ), [ 'http', 'https' ], true ) || false === filter_var( $url, FILTER_VALIDATE_URL ) || wp_parse_url( $url, PHP_URL_USER ) ) {
		return new WP_Error( 'er_bad_url', __( 'the URL is not a valid public http(s) URL', 'egypt-roamer-core' ) );
	}
	if ( ! er_host_allowed( $url, $domains ) ) {
		return new WP_Error( 'er_host', sprintf( /* translators: %s: host */ __( 'host %s is not in the provider’s allowed domains', 'egypt-roamer-core' ), (string) wp_parse_url( $url, PHP_URL_HOST ) ) );
	}

	// Configured tracking: utm_* and extra key=value pairs (never overriding what the URL already carries).
	$params = [];
	foreach ( [ 'utm_source', 'utm_medium', 'utm_campaign' ] as $k ) {
		$v = (string) get_post_meta( $offer_id, '_er_' . $k, true );
		if ( '' !== $v ) {
			$params[ $k ] = $v;
		}
	}
	$placeholders = [
		'{placement}' => er_subid( (string) ( $ctx['placement'] ?? '' ) ),
		'{page}'      => er_subid( (string) ( $ctx['page'] ?? '' ) ),
		'{offer}'     => er_subid( (string) get_post_field( 'post_name', $offer_id ) ),
	];
	foreach ( preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $offer_id, '_er_params', true ) ) as $line ) {
		if ( ! str_contains( $line, '=' ) ) {
			continue;
		}
		[ $k, $v ] = array_map( 'trim', explode( '=', $line, 2 ) );
		if ( preg_match( '/^[A-Za-z0-9_.\-\[\]]{1,64}$/', $k ) ) {
			$params[ $k ] = strtr( $v, $placeholders );
		}
	}
	parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $existing );
	$params = array_diff_key( $params, $existing );
	if ( $params ) {
		$url = add_query_arg( array_map( 'rawurlencode', $params ), $url );
	}
	return $url;
}

/** Public, crawl-safe link to the redirect endpoint. */
function er_offer_go_url( int $offer_id, string $placement = '', int $source_post_id = 0 ): string {
	$slug = get_post_field( 'post_name', $offer_id );
	$url  = rtrim( (string) get_option( 'home' ), '/' ) . '/go/' . rawurlencode( (string) $slug ) . '/';
	$args = [];
	if ( $placement ) {
		$args['pl'] = sanitize_key( $placement );
	}
	if ( $source_post_id ) {
		$args['src'] = $source_post_id;
	}
	return $args ? add_query_arg( $args, $url ) : $url;
}

function er_offer_cta_label( int $offer_id ): string {
	$custom = trim( (string) get_post_meta( $offer_id, '_er_cta_custom', true ) );
	if ( '' !== $custom ) {
		return er_translate_string( $custom );
	}
	$key     = (string) ( get_post_meta( $offer_id, '_er_cta', true ) ?: 'check_availability' );
	$options = er_cta_options();
	return $options[ $key ] ?? $options['check_availability'];
}

/**
 * Display price only when it was actually checked, with the date.
 * Returns null when there is no verifiable price.
 */
function er_offer_price( int $offer_id ): ?array {
	$amount  = get_post_meta( $offer_id, '_er_price_from', true );
	$checked = (string) get_post_meta( $offer_id, '_er_price_checked', true );
	if ( '' === (string) $amount || ! is_numeric( $amount ) || ! $checked ) {
		return null;
	}
	$currency = strtoupper( (string) ( get_post_meta( $offer_id, '_er_currency', true ) ?: 'USD' ) );
	return [
		'amount'   => 0 + $amount,
		'currency' => preg_match( '/^[A-Z]{3}$/', $currency ) ? $currency : 'USD',
		'unit'     => (string) ( get_post_meta( $offer_id, '_er_price_unit', true ) ?: 'person' ),
		'checked'  => $checked,
	];
}

/** Format an amount in the current locale when intl is available. */
function er_format_money( float $amount, string $currency ): string {
	if ( class_exists( 'NumberFormatter' ) ) {
		$fmt = new NumberFormatter( determine_locale(), NumberFormatter::CURRENCY );
		$fmt->setAttribute( NumberFormatter::FRACTION_DIGITS, floor( $amount ) == $amount ? 0 : 2 ); // phpcs:ignore Universal.Operators.StrictComparisons
		$out = $fmt->formatCurrency( $amount, $currency );
		if ( false !== $out ) {
			return $out;
		}
	}
	return $currency . ' ' . number_format_i18n( $amount, floor( $amount ) == $amount ? 0 : 2 ); // phpcs:ignore Universal.Operators.StrictComparisons
}

function er_price_unit_label( string $unit ): string {
	$units = [
		'person' => __( 'person', 'egypt-roamer-core' ),
		'night'  => __( 'night', 'egypt-roamer-core' ),
		'car'    => __( 'car', 'egypt-roamer-core' ),
		'day'    => __( 'day', 'egypt-roamer-core' ),
		'group'  => __( 'group', 'egypt-roamer-core' ),
	];
	return $units[ $unit ] ?? $units['person'];
}

/** Everything a template needs to show an offer. */
function er_offer_data( int $offer_id, string $placement = '', int $source_post_id = 0 ): array {
	$provider_id = (int) get_post_meta( $offer_id, '_er_provider', true );
	$price       = er_offer_price( $offer_id );
	$thumb       = get_post_thumbnail_id( $offer_id );
	return [
		'id'          => $offer_id,
		'slug'        => get_post_field( 'post_name', $offer_id ),
		'title'       => get_the_title( $offer_id ),
		'summary'     => get_the_excerpt( $offer_id ),
		'provider'    => $provider_id ? get_the_title( $provider_id ) : '',
		'provider_id' => $provider_id,
		'go'          => er_offer_go_url( $offer_id, $placement, $source_post_id ),
		'cta'         => er_offer_cta_label( $offer_id ),
		'location'    => (string) get_post_meta( $offer_id, '_er_location', true ),
		'meta'        => (string) get_post_meta( $offer_id, '_er_meta_line', true ),
		'badge'       => (string) get_post_meta( $offer_id, '_er_badge', true ),
		'price'       => $price,
		'price_text'  => $price ? er_format_money( (float) $price['amount'], $price['currency'] ) : '',
		'unit_text'   => $price ? er_price_unit_label( $price['unit'] ) : '',
		'image_id'    => $thumb ? (int) $thumb : 0,
		'placement'   => $placement,
	];
}

/**
 * Live offers, highest priority first.
 *
 * @param array $args destination|tour|experience|activity => post ID, type => offer category slug,
 *                    style => travel style slug, limit => int, ids => int[]
 */
function er_get_offers( array $args = [] ): array {
	static $cache = [];
	$key = md5( wp_json_encode( $args ) . '|' . current_time( 'Y-m-d' ) );
	if ( ! isset( $cache[ $key ] ) ) {
		$cache[ $key ] = er_get_offers_uncached( $args ); // homepage sections ask for the same lists several times
	}
	return $cache[ $key ];
}

function er_get_offers_uncached( array $args ): array {
	$ids = er_live_offer_index();
	foreach ( [ 'destination', 'tour', 'experience', 'activity' ] as $rel ) {
		if ( ! empty( $args[ $rel ] ) ) {
			// Offers attach to one language version; match the page in any language.
			$group = er_translation_group( (int) $args[ $rel ] );
			$ids   = array_filter( $ids, static fn ( $o ) => in_array( $o[ $rel ], $group, true ) );
		}
	}
	// Categories and styles are translatable; offers are not. Match the term in any language.
	foreach ( [ 'type' => 'types', 'style' => 'styles' ] as $arg => $field ) {
		if ( ! empty( $args[ $arg ] ) ) {
			$terms = er_term_group_ids( 'type' === $arg ? 'er_offer_type' : 'er_travel_style', (array) $args[ $arg ] );
			$ids   = array_filter( $ids, static fn ( $o ) => (bool) array_intersect( $o[ $field ], $terms ) );
		}
	}
	if ( ! empty( $args['ids'] ) ) {
		$order = array_map( 'intval', (array) $args['ids'] );
		$ids   = array_filter( $ids, static fn ( $o ) => in_array( $o['id'], $order, true ) );
		usort( $ids, static fn ( $x, $y ) => array_search( $x['id'], $order, true ) <=> array_search( $y['id'], $order, true ) ); // editor's order
	} else {
		usort( $ids, static fn ( $x, $y ) => [ $y['priority'], $y['date'] ] <=> [ $x['priority'], $x['date'] ] );
	}
	return array_slice( array_column( $ids, 'id' ), 0, (int) ( $args['limit'] ?? 10 ) );
}

/**
 * Every live offer with the fields used for matching — loaded once per request
 * (meta and terms primed in bulk), so homepage sections never query per card.
 */
function er_live_offer_index(): array {
	static $index = null;
	if ( null !== $index ) {
		return $index;
	}
	$posts = get_posts( [
		'post_type'        => 'er_offer',
		'post_status'      => 'publish',
		'numberposts'      => 1000,
		'orderby'          => 'date',
		'order'            => 'DESC',
		'suppress_filters' => false,
		'lang'             => '', // offers are language-neutral records
	] );
	$providers = array_unique( array_filter( array_map( static fn ( $p ) => (int) get_post_meta( $p->ID, '_er_provider', true ), $posts ) ) );
	if ( $providers ) {
		_prime_post_caches( $providers, false, true );
	}
	$index = [];
	foreach ( $posts as $p ) {
		if ( ! er_offer_is_live( $p->ID ) ) {
			continue;
		}
		$terms = static fn ( $tax ) => array_map( 'intval', wp_list_pluck( get_the_terms( $p->ID, $tax ) ?: [], 'term_id' ) );
		$index[] = [
			'id'          => $p->ID,
			'date'        => $p->post_date_gmt,
			'priority'    => (int) get_post_meta( $p->ID, '_er_priority', true ),
			'destination' => (int) get_post_meta( $p->ID, '_er_destination', true ),
			'tour'        => (int) get_post_meta( $p->ID, '_er_tour', true ),
			'experience'  => (int) get_post_meta( $p->ID, '_er_experience', true ),
			'activity'    => (int) get_post_meta( $p->ID, '_er_activity', true ),
			'types'       => $terms( 'er_offer_type' ),
			'styles'      => $terms( 'er_travel_style' ),
		];
	}
	return $index;
}

/**
 * The one way to render an affiliate CTA.
 *
 * @param array $args placement, source (post ID), class, label, icon (html)
 */
function er_offer_cta_html( int $offer_id, array $args = [] ): string {
	if ( ! er_offer_is_live( $offer_id ) ) {
		return '';
	}
	$placement = (string) ( $args['placement'] ?? '' );
	$source    = (int) ( $args['source'] ?? get_queried_object_id() );
	$provider  = (int) get_post_meta( $offer_id, '_er_provider', true );
	$label     = (string) ( $args['label'] ?? er_offer_cta_label( $offer_id ) );
	return sprintf(
		'<a class="%1$s" href="%2$s" rel="sponsored nofollow noopener" target="_blank" data-er-offer="%3$s" data-er-provider="%4$s" data-er-placement="%5$s" data-er-cta="%6$s" data-er-intent="%9$s">%7$s%8$s</a>',
		esc_attr( (string) ( $args['class'] ?? 'btn btn--primary' ) ),
		esc_url( er_offer_go_url( $offer_id, $placement, $source ) ),
		esc_attr( (string) get_post_field( 'post_name', $offer_id ) ),
		esc_attr( $provider ? (string) get_post_field( 'post_name', $provider ) : '' ),
		esc_attr( sanitize_key( $placement ) ),
		esc_attr( $label ),
		esc_html( $label ),
		$args['icon'] ?? '', // trusted, theme-supplied SVG markup
		er_offer_intent( $offer_id )
	);
}

/** "booking" for CTAs that hand the visitor to a booking flow, else "offer" (analytics). */
function er_offer_intent( int $offer_id ): string {
	$key = (string) ( get_post_meta( $offer_id, '_er_cta', true ) ?: 'check_availability' );
	return in_array( $key, [ 'book_now', 'book_with_partner', 'check_availability', 'check_price' ], true ) ? 'booking' : 'offer';
}

/** Short disclosure text, translatable. */
function er_disclosure_html( string $class = 'disclosure' ): string {
	$text = er_translate_string( (string) er_settings( 'disclosure_text' ) );
	if ( '' === trim( $text ) ) {
		return '';
	}
	$page = (int) er_settings( 'disclosure_page' );
	$page = $page ? er_translated_post_id( $page ) : 0;
	$link = $page && 'publish' === get_post_status( $page )
		? sprintf( ' <a href="%s">%s</a>', esc_url( get_permalink( $page ) ), esc_html__( 'How we work with partners', 'egypt-roamer-core' ) )
		: '';
	return sprintf( '<p class="%s">%s%s</p>', esc_attr( $class ), esc_html( $text ), $link );
}

/* -------------------------------------------------------------------------- */
/* Shortcodes — for placing offers inside editorial content                    */
/* -------------------------------------------------------------------------- */

/** [er_offer slug="pyramids-sunrise" placement="guide-inline"] */
add_shortcode( 'er_offer', static function ( $atts ) {
	$atts = shortcode_atts( [ 'id' => 0, 'slug' => '', 'placement' => 'inline', 'label' => '' ], $atts, 'er_offer' );
	$id   = (int) $atts['id'];
	if ( ! $id && $atts['slug'] ) {
		$post = get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, 'er_offer' );
		$id   = $post ? $post->ID : 0;
	}
	if ( ! $id ) {
		return '';
	}
	$args = [ 'placement' => $atts['placement'], 'class' => 'btn btn--primary er-offer-inline' ];
	if ( '' !== $atts['label'] ) {
		$args['label'] = sanitize_text_field( $atts['label'] );
	}
	return er_offer_cta_html( $id, $args );
} );

/** [er_disclosure] */
add_shortcode( 'er_disclosure', static fn () => er_disclosure_html() );

/* -------------------------------------------------------------------------- */
/* Admin: offer link preview, list columns, slug uniqueness                    */
/* -------------------------------------------------------------------------- */

function er_render_offer_link_box( WP_Post $post ): void {
	echo '<h3 class="er-fields__heading">' . esc_html__( 'Redirect check', 'egypt-roamer-core' ) . '</h3>';
	if ( 'publish' !== $post->post_status || ! $post->post_name ) {
		echo '<p>' . esc_html__( 'Publish the offer to get its /go/ link.', 'egypt-roamer-core' ) . '</p>';
		return;
	}
	$go    = er_offer_go_url( $post->ID );
	$check = er_offer_destination_url( $post->ID, [ 'placement' => 'admin-preview' ] );
	printf( '<p><strong>%s</strong> <code>%s</code></p>', esc_html__( 'Site link:', 'egypt-roamer-core' ), esc_html( $go ) );
	if ( is_wp_error( $check ) ) {
		printf( '<p class="er-issue er-issue--error">%s %s</p>', esc_html__( 'Cannot redirect:', 'egypt-roamer-core' ), esc_html( $check->get_error_message() ) );
	} else {
		printf( '<p>%s <code>%s</code></p>', esc_html__( 'Redirects to:', 'egypt-roamer-core' ), esc_html( $check ) );
		printf( '<p>%s</p>', er_offer_is_live( $post->ID ) ? esc_html__( 'Status: live — the CTA is shown on the site.', 'egypt-roamer-core' ) : esc_html__( 'Status: not live (paused, outside its dates, or the provider is inactive). The CTA is hidden and /go/ falls back to the related page.', 'egypt-roamer-core' ) );
	}
}

add_filter( 'manage_er_offer_posts_columns', static function ( $cols ) {
	$cols['er_provider'] = __( 'Provider', 'egypt-roamer-core' );
	$cols['er_live']     = __( 'Live', 'egypt-roamer-core' );
	$cols['er_priority'] = __( 'Priority', 'egypt-roamer-core' );
	$cols['er_go']       = __( 'Link', 'egypt-roamer-core' );
	return $cols;
} );
add_action( 'manage_er_offer_posts_custom_column', static function ( $col, $post_id ) {
	switch ( $col ) {
		case 'er_provider':
			$p = (int) get_post_meta( $post_id, '_er_provider', true );
			echo $p ? esc_html( get_the_title( $p ) ) : '—';
			break;
		case 'er_live':
			echo er_offer_is_live( (int) $post_id ) ? '<span class="er-pill er-pill--ok">live</span>' : '<span class="er-pill">off</span>';
			break;
		case 'er_priority':
			echo esc_html( (string) get_post_meta( $post_id, '_er_priority', true ) );
			break;
		case 'er_go':
			echo '<code>/go/' . esc_html( (string) get_post_field( 'post_name', $post_id ) ) . '/</code>';
			break;
	}
}, 10, 2 );

/** Offer and provider slugs share the /go/ namespace, so they must not collide. */
add_filter( 'wp_unique_post_slug', static function ( $slug, $post_id, $status, $post_type ) {
	if ( ! in_array( $post_type, [ 'er_offer', 'er_provider' ], true ) ) {
		return $slug;
	}
	$other = 'er_offer' === $post_type ? 'er_provider' : 'er_offer';
	$base  = $slug;
	$i     = 2;
	while ( get_page_by_path( $slug, OBJECT, $other ) ) {
		$slug = $base . '-' . $i++;
	}
	return $slug;
}, 10, 4 );
