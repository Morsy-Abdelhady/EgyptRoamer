<?php
/**
 * SEO guards. Rank Math SEO owns titles, descriptions, canonicals, the sitemap,
 * Open Graph and schema. This file only adds what it cannot know:
 *
 * - the editorial "Ready to index" gate (noindex + sitemap exclusion until ticked)
 * - noindex for internal search, filtered archives and empty archives
 * - /go/ kept out of crawling
 * - 301s for the paths written into the static prototype
 * - TouristDestination data built strictly from visible destination fields
 * - a minimal meta description / Open Graph fallback ONLY when no SEO plugin is active
 */

defined( 'ABSPATH' ) || exit;

function er_seo_plugin_active(): bool {
	return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/** Editorial types need the explicit "Ready to index" tick; everything else defers to the SEO plugin. */
function er_is_indexable( int $post_id ): bool {
	if ( ! array_key_exists( (string) get_post_type( $post_id ), er_public_types() ) ) {
		return true;
	}
	return (bool) get_post_meta( $post_id, '_er_indexable', true );
}

/** Query-string keys that only filter or sort a listing (never a distinct page). */
function er_filter_query_keys(): array {
	return apply_filters( 'er_filter_query_keys', [ 'destination', 'style', 'region', 'topic', 'duration', 'type', 'sort', 'orderby', 'order' ] );
}

/** Should the current request be noindex? */
function er_request_noindex(): bool {
	if ( is_search() ) {
		return true;
	}
	if ( is_singular() ) {
		return ! er_is_indexable( (int) get_queried_object_id() );
	}
	if ( is_post_type_archive( array_keys( er_public_types() ) ) || is_home() || is_category() || is_tag() ) {
		foreach ( er_filter_query_keys() as $key ) {
			if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return true;
			}
		}
		global $wp_query;
		return 0 === (int) $wp_query->found_posts;
	}
	return false;
}

// Core robots meta (used when Rank Math is not active).
add_filter( 'wp_robots', static function ( array $robots ) {
	if ( er_request_noindex() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'], $robots['max-image-preview'] );
	}
	return $robots;
} );

// Rank Math robots meta.
add_filter( 'rank_math/frontend/robots', static function ( $robots ) {
	if ( er_request_noindex() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
} );

// Rank Math sitemap: drop editorial entries that are not ready to index.
add_filter( 'rank_math/sitemap/entry', static function ( $url, $type, $object ) {
	if ( 'post' === $type && $object instanceof WP_Post && ! er_is_indexable( $object->ID ) ) {
		return false;
	}
	return $url;
}, 10, 3 );

// Core sitemap (fallback when no SEO plugin provides one).
add_filter( 'wp_sitemaps_posts_query_args', static function ( $args, $post_type ) {
	if ( array_key_exists( $post_type, er_public_types() ) ) {
		$args['meta_query'] = [ [ 'key' => '_er_indexable', 'value' => '1' ] ];
	}
	return $args;
}, 10, 2 );
add_filter( 'wp_sitemaps_add_provider', static function ( $provider, $name ) {
	return 'users' === $name ? false : $provider; // author archives are not a content strategy here
}, 10, 2 );

// Keep the redirect endpoint out of crawling (it also sends X-Robots-Tag: noindex).
add_filter( 'robots_txt', static function ( $output, $public ) {
	if ( $public ) {
		$output .= "\n# Egypt Roamer affiliate redirects\nDisallow: /go/\n";
	}
	return $output;
}, 20, 2 );

/* -------------------------------------------------------------------------- */
/* Legacy static-site paths → WordPress URLs (one hop, 301)                    */
/* -------------------------------------------------------------------------- */

function er_legacy_redirect_target( string $path ): ?string {
	$path = '/' . trim( $path, '/' );
	if ( '/index.html' === $path ) {
		return home_url( '/' );
	}
	if ( '/guide' === $path ) {
		return get_post_type_archive_link( 'er_guide' ) ?: null;
	}
	if ( preg_match( '#^/guide/([a-z0-9-]+)$#', $path, $m ) ) {
		$guide = get_page_by_path( $m[1], OBJECT, 'er_guide' );
		return $guide && 'publish' === $guide->post_status ? get_permalink( $guide ) : ( get_post_type_archive_link( 'er_guide' ) ?: null );
	}
	return null;
}

add_action( 'template_redirect', static function () {
	if ( ! is_404() || ! er_settings( 'legacy_redirects' ) ) {
		return;
	}
	$path   = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH );
	$home   = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$path   = '/' . ltrim( substr( $path, strlen( rtrim( $home, '/' ) ) ), '/' );
	$target = er_legacy_redirect_target( $path );
	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}, 1 );

/* -------------------------------------------------------------------------- */
/* Structured data                                                             */
/* -------------------------------------------------------------------------- */

add_action( 'wp_head', static function () {
	if ( ! is_singular( 'er_destination' ) || ! er_settings( 'place_schema' ) ) {
		return;
	}
	$id = (int) get_queried_object_id();
	if ( ! er_is_indexable( $id ) ) {
		return;
	}
	$data = [
		'@context'    => 'https://schema.org',
		'@type'       => 'TouristDestination',
		'name'        => get_the_title( $id ),
		'url'         => get_permalink( $id ),
		'description' => wp_strip_all_tags( get_the_excerpt( $id ) ),
	];
	$image = get_the_post_thumbnail_url( $id, 'large' );
	if ( $image ) {
		$data['image'] = $image;
	}
	$lat = get_post_meta( $id, '_er_lat', true );
	$lng = get_post_meta( $id, '_er_lng', true );
	if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
		$data['geo'] = [ '@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng ];
	}
	$data['containedInPlace'] = [ '@type' => 'Country', 'name' => 'Egypt' ];
	echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $data ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 20 );

/* Minimal fallback while no SEO plugin is installed — removed automatically once Rank Math is active. */
add_action( 'wp_head', static function () {
	if ( er_seo_plugin_active() ) {
		return;
	}
	$desc  = get_bloginfo( 'description' );
	$image = '';
	if ( is_singular() ) {
		$post  = get_queried_object();
		$desc  = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
		$image = (string) get_the_post_thumbnail_url( $post, 'large' );
	} elseif ( is_post_type_archive() ) {
		$obj  = get_queried_object();
		$desc = $obj && ! empty( $obj->description ) ? $obj->description : $desc;
	}
	$image = $image ?: (string) apply_filters( 'er_default_share_image', '' );
	$desc  = trim( wp_strip_all_tags( (string) $desc ) );
	if ( $desc ) {
		printf( "<meta name=\"description\" content=\"%s\" />\n", esc_attr( $desc ) );
		printf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $desc ) );
	}
	printf( "<meta property=\"og:title\" content=\"%s\" />\n", esc_attr( wp_get_document_title() ) );
	printf( "<meta property=\"og:type\" content=\"%s\" />\n", is_singular( [ 'post', 'er_guide' ] ) ? 'article' : 'website' );
	printf( "<meta property=\"og:site_name\" content=\"%s\" />\n", esc_attr( get_bloginfo( 'name' ) ) );
	if ( is_singular() ) {
		printf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( get_permalink() ) );
	}
	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\" />\n<meta name=\"twitter:card\" content=\"summary_large_image\" />\n", esc_url( $image ) );
	}
}, 2 );
