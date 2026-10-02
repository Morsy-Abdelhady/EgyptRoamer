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
	if ( ! in_array( (string) get_post_type( $post_id ), er_gated_types(), true ) ) {
		return true;
	}
	return (bool) get_post_meta( $post_id, '_er_indexable', true );
}

/** The Journal (posts page) in every language. */
function er_posts_page_ids(): array {
	$id = (int) get_option( 'page_for_posts' );
	return $id && function_exists( 'er_translation_group' ) ? er_translation_group( $id ) : ( $id ? [ $id ] : [] );
}

/** Is there at least one published article ticked "Ready to index" (optionally in one language)? */
function er_has_indexable_articles( string $lang = '' ): bool {
	static $cache = [];
	if ( ! isset( $cache[ $lang ] ) ) {
		$cache[ $lang ] = (bool) get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_er_indexable', 'meta_value' => '1', 'lang' => $lang ] );
	}
	return $cache[ $lang ];
}

/** Journal pages (any language) whose language has no indexable article — noindex, so never in a sitemap. */
function er_empty_posts_page_ids(): array {
	return array_values( array_filter( er_posts_page_ids(), static function ( $id ) {
		$lang = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id, 'slug' ) : '';
		return ! er_has_indexable_articles( $lang );
	} ) );
}

/** Does a content type have at least one published item ticked "Ready to index" (current language)? */
function er_type_has_indexable( string $post_type ): bool {
	static $cache = [];
	if ( ! isset( $cache[ $post_type ] ) ) {
		$cache[ $post_type ] = (bool) get_posts( [ 'post_type' => $post_type, 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_er_indexable', 'meta_value' => '1', 'suppress_filters' => false ] );
	}
	return $cache[ $post_type ];
}

/** Query-string keys that only filter or sort a listing (never a distinct page). */
function er_filter_query_keys(): array {
	return apply_filters( 'er_filter_query_keys', [ 'destination', 'style', 'region', 'topic', 'duration', 'type', 'sort', 'orderby', 'order' ] );
}

/** Should the current request be noindex? */
function er_request_noindex(): bool {
	if ( is_front_page() && ! is_paged() ) {
		return false; // the homepage is never subject to the empty/filtered-archive rules
	}
	if ( is_search() ) {
		return true;
	}
	if ( is_singular() ) {
		return ! er_is_indexable( (int) get_queried_object_id() );
	}
	if ( is_category( (int) get_option( 'default_category' ) ) ) {
		return true; // "Uncategorized" is never a destination for searchers
	}
	// An archive whose items are all still "not ready" is a hub of thin pages: keep it out too.
	if ( is_post_type_archive( er_public_type_keys() ) && ! er_type_has_indexable( (string) get_query_var( 'post_type' ) ) ) {
		return true;
	}
	if ( is_home() && ! er_has_indexable_articles( function_exists( 'er_current_lang' ) ? er_current_lang() : '' ) ) {
		return true;
	}
	if ( is_post_type_archive( er_public_type_keys() ) || is_home() || is_category() || is_tag() ) {
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
		if ( empty( $robots['nofollow'] ) ) { // "Discourage search engines" already set nofollow: don't contradict it
			$robots['follow'] = true;
		}
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
	if ( 'post' === $type && $object instanceof WP_Post && in_array( $object->ID, er_empty_posts_page_ids(), true ) ) {
		return false;
	}
	if ( 'term' === $type && $object instanceof WP_Term && (int) get_option( 'default_category' ) === (int) $object->term_id ) {
		return false;
	}
	return $url;
}, 10, 3 );

// Core sitemap (fallback when no SEO plugin provides one).
add_filter( 'wp_sitemaps_posts_query_args', static function ( $args, $post_type ) {
	if ( in_array( $post_type, er_gated_types(), true ) ) {
		$args['meta_query'] = [ [ 'key' => '_er_indexable', 'value' => '1' ] ];
	}
	if ( 'page' === $post_type && er_empty_posts_page_ids() ) {
		$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? [] ), er_empty_posts_page_ids() ); // an empty Journal is noindex
	}
	return $args;
}, 10, 2 );
add_filter( 'wp_sitemaps_taxonomies_query_args', static function ( $args, $taxonomy ) {
	if ( 'category' === $taxonomy ) {
		$args['exclude'] = array_merge( (array) ( $args['exclude'] ?? [] ), [ (int) get_option( 'default_category' ) ] );
	}
	return $args;
}, 10, 2 );
add_filter( 'wp_sitemaps_add_provider', static function ( $provider, $name ) {
	return 'users' === $name ? false : $provider; // author archives are not a content strategy here
}, 10, 2 );

/*
 * Core sitemap: the content-type hubs (/destinations/, /experiences/ …) are indexable pages with their own
 * intro and links, but WordPress's sitemap lists no post-type archives. Listed here, per language, only
 * while the archive is indexable (at least one published item ticked "Ready to index").
 */
add_action( 'init', static function () {
	if ( er_seo_plugin_active() || ! class_exists( 'WP_Sitemaps_Provider' ) ) {
		return;
	}
	if ( ! class_exists( 'ER_Sitemaps_Archives' ) ) {
		/** Post-type archives provider for the core sitemap. */
		class ER_Sitemaps_Archives extends WP_Sitemaps_Provider {
			public function __construct() {
				$this->name        = 'archives';
				$this->object_type = 'archive';
			}

			public function get_url_list( $page_num, $object_subtype = '' ) {
				if ( 1 !== (int) $page_num ) {
					return [];
				}
				// Polylang serves one sitemap per language (/de/wp-sitemap-archives-1.xml …): list that language only.
				$langs = [ function_exists( 'pll_current_language' ) ? (string) ( pll_current_language() ?: pll_default_language() ) : '' ];
				$urls  = [];
				foreach ( er_public_type_keys() as $type ) {
					foreach ( $langs as $lang ) {
						$latest = get_posts( [ 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => 1, 'meta_key' => '_er_indexable', 'meta_value' => '1', 'lang' => $lang, 'orderby' => 'modified', 'order' => 'DESC' ] );
						if ( ! $latest ) {
							continue; // an archive with nothing ready to index is noindex (er_request_noindex)
						}
						$link = er_post_type_archive_link_in( $type, (string) $lang );
						if ( $link ) {
							$urls[] = [ 'loc' => $link, 'lastmod' => get_post_modified_time( DATE_W3C, true, $latest[0] ) ];
						}
					}
				}
				return $urls;
			}

			public function get_max_num_pages( $object_subtype = '' ) {
				return 1;
			}
		}
	}
	wp_register_sitemap_provider( 'archives', new ER_Sitemaps_Archives() );
}, 20 );

/** A post-type archive's URL in one language (Polylang directory URLs; the default language has no prefix). */
function er_post_type_archive_link_in( string $type, string $lang ): string {
	$link = (string) get_post_type_archive_link( $type );
	if ( ! $link || '' === $lang || ! function_exists( 'pll_languages_list' ) ) {
		return $link;
	}
	// Polylang prefixes the current request's language: strip any language prefix, then add $lang's.
	$path  = (string) wp_parse_url( $link, PHP_URL_PATH );
	$root  = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$rest  = ltrim( substr( $path, strlen( $root ) ), '/' );
	$slugs = (array) pll_languages_list();
	$first = strtok( $rest, '/' );
	if ( false !== $first && in_array( $first, $slugs, true ) ) {
		$rest = (string) substr( $rest, strlen( $first ) + 1 );
	}
	$prefix = pll_default_language() === $lang ? '' : $lang . '/';
	return home_url( '/' . $prefix . $rest ); // home_url() itself is never language-prefixed
}

// Keep the redirect endpoint out of crawling (it also sends X-Robots-Tag: noindex). Placed in the
// "User-agent: *" block, before WordPress's Sitemap line, so every robots.txt parser reads it as a rule.
add_filter( 'robots_txt', static function ( $output, $public ) {
	if ( $public ) {
		$rule = "Disallow: /go/\n";
		$output = str_contains( $output, "\nSitemap:" )
			? preg_replace( '/\n(?=\nSitemap:)/', "\n" . $rule, $output, 1 )
			: $output . $rule;
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
	if ( '/privacy' === $path ) {
		$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
		return $privacy && 'publish' === get_post_status( $privacy ) ? get_permalink( $privacy ) : null;
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
		'name'        => trim( str_replace( "\u{00A0}", ' ', html_entity_decode( wp_strip_all_tags( get_the_title( $id ) ), ENT_QUOTES, 'UTF-8' ) ) ), // as the visible H1 reads
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
	$data['containedInPlace'] = [ '@type' => 'Country', 'name' => (string) apply_filters( 'er_country_name', 'Egypt' ) ]; // the page's language (theme)
	echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $data ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 20 );

/*
 * Guides and journal articles (once published and ticked "Ready to index"): Article with the real dates.
 * The author is the publication itself (no named editor is published on the site), the image only when the
 * article has its own featured image.
 */
add_action( 'wp_head', static function () {
	if ( er_seo_plugin_active() || ! is_singular( [ 'er_guide', 'post' ] ) ) {
		return;
	}
	$id = (int) get_queried_object_id();
	if ( ! er_is_indexable( $id ) ) {
		return;
	}
	$org  = [ '@type' => 'Organization', '@id' => home_url( '/' ) . '#organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ];
	$data = [
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'headline'         => wp_strip_all_tags( get_the_title( $id ) ),
		'description'      => wp_strip_all_tags( get_the_excerpt( $id ) ),
		'url'              => get_permalink( $id ),
		'mainEntityOfPage' => get_permalink( $id ),
		'datePublished'    => get_post_time( DATE_W3C, true, $id ),
		'dateModified'     => get_post_modified_time( DATE_W3C, true, $id ),
		'inLanguage'       => str_replace( '_', '-', get_locale() ),
		'author'           => $org,
		'publisher'        => $org,
	];
	$image = get_the_post_thumbnail_url( $id, 'large' );
	if ( $image ) {
		$data['image'] = $image;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $data ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 20 );

/*
 * Homepage (every language): the brand as an Organization and the site as a WebSite, until an SEO plugin
 * provides its own. Only facts that are true today: no contactPoint (the public mailbox has no MX record
 * yet), no sameAs (no social profiles), no SearchAction (Google retired the sitelinks search box).
 */
add_action( 'wp_head', static function () {
	if ( er_seo_plugin_active() || ! is_front_page() || is_paged() ) {
		return;
	}
	$root  = home_url( '/' );
	$langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list( [ 'fields' => 'locale' ] ) : [ get_locale() ];
	$org   = [
		'@type' => 'Organization',
		'@id'   => $root . '#organization',
		'name'  => get_bloginfo( 'name' ),
		'url'   => $root,
	];
	$logo = (string) apply_filters( 'er_brand_logo', '' );
	if ( $logo ) {
		$org['logo'] = $logo;
	}
	$site = [
		'@type'      => 'WebSite',
		'@id'        => $root . '#website',
		'name'       => get_bloginfo( 'name' ),
		'url'        => $root,
		'publisher'  => [ '@id' => $root . '#organization' ],
		'inLanguage' => array_values( array_map( static fn ( $l ) => str_replace( '_', '-', (string) $l ), array_filter( $langs ) ) ),
	];
	echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => [ $org, $site ] ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
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
		$desc  = has_excerpt( $post ) ? get_the_excerpt( $post ) : er_description_from_content( (string) $post->post_content );
		$image = (string) get_the_post_thumbnail_url( $post, 'large' );
	} elseif ( is_post_type_archive() ) {
		// The same editable intro the archive's page hero shows (Egypt Roamer → Settings), then the type description.
		$type  = (string) get_query_var( 'post_type' );
		$intro = er_archive_intro( $type );
		$obj   = get_queried_object();
		$desc  = $intro ?: ( $obj && ! empty( $obj->description ) ? $obj->description : $desc );
	} elseif ( is_home() && (int) get_option( 'page_for_posts' ) && has_excerpt( (int) get_option( 'page_for_posts' ) ) ) {
		$desc = get_the_excerpt( (int) get_option( 'page_for_posts' ) );
	}
	$desc  = (string) apply_filters( 'er_fallback_description', (string) $desc );
	$image = $image ?: (string) apply_filters( 'er_default_share_image', '' );
	$desc  = trim( wp_strip_all_tags( (string) $desc ) );
	if ( $desc ) {
		printf( "<meta name=\"description\" content=\"%s\" />\n", esc_attr( $desc ) );
		printf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $desc ) );
	}
	printf( "<meta property=\"og:title\" content=\"%s\" />\n", esc_attr( wp_get_document_title() ) );
	printf( "<meta property=\"og:type\" content=\"%s\" />\n", is_singular( [ 'post', 'er_guide' ] ) ? 'article' : 'website' );
	printf( "<meta property=\"og:site_name\" content=\"%s\" />\n", esc_attr( get_bloginfo( 'name' ) ) );
	// og:locale needs language_TERRITORY; WordPress's Arabic locale is plain "ar".
	$og_locale = static fn ( string $l ): string => str_contains( $l, '_' ) ? $l : ( 'ar' === $l ? 'ar_AR' : $l . '_' . strtoupper( $l ) );
	$locale    = get_locale();
	printf( "<meta property=\"og:locale\" content=\"%s\" />\n", esc_attr( $og_locale( $locale ) ) );
	if ( function_exists( 'pll_languages_list' ) ) {
		foreach ( (array) pll_languages_list( [ 'fields' => 'locale' ] ) as $alt ) {
			if ( $alt && $alt !== $locale ) {
				printf( "<meta property=\"og:locale:alternate\" content=\"%s\" />\n", esc_attr( $og_locale( (string) $alt ) ) );
			}
		}
	}
	if ( is_singular() ) {
		printf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( get_permalink() ) );
	} elseif ( ( is_post_type_archive() || is_home() || is_category() || is_tag() ) && ! is_search() && ! er_request_noindex() ) {
		// A noindex view (filtered, sorted or empty) gets no canonical: noindex plus a canonical to another URL
		// are contradictory signals.
		// Core only prints canonicals for singular pages; archives get one here (page kept). The whole query
		// string is dropped, not only the filter keys: get_pagenum_link() echoes any visitor parameter
		// (?utm_source=…, cache busters), which made every tracked visit its own canonical URL.
		$canonical = strtok( get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ), false ), '?' );
		printf( "<link rel=\"canonical\" href=\"%s\" />\n<meta property=\"og:url\" content=\"%s\" />\n", esc_url( $canonical ), esc_url( $canonical ) );
	}
	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\" />\n<meta name=\"twitter:card\" content=\"summary_large_image\" />\n", esc_url( $image ) );
	}
}, 2 );

/**
 * A description for a page without an excerpt (legal and contact pages): its first real paragraph, never the
 * "Last updated" line or the translation note, cut at the end of a sentence (or a word) at about 160
 * characters. The body used to be trimmed to 28 words, which gave "Last updated: 30 September 2026 This page
 * explains…" and, on translated pages, the translation note and table cells.
 */
function er_description_from_content( string $content, int $max = 160 ): string {
	$content = strip_shortcodes( $content );
	preg_match_all( '#<p\b([^>]*)>(.*?)</p>#is', $content, $m, PREG_SET_ORDER );
	$text = '';
	foreach ( $m as $p ) {
		if ( str_contains( $p[1], 'er-translation-note' ) ) {
			continue;
		}
		$inner = trim( $p[2] );
		if ( preg_match( '#^<(em|i|small)\b[^>]*>.*</\1>$#is', $inner ) ) {
			continue; // a date line or another aside set entirely in italics
		}
		$plain = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $inner ) ) );
		if ( mb_strlen( $plain ) >= 30 ) {
			$text = $plain;
			break;
		}
	}
	if ( '' === $text ) {
		$text = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $content ) ) );
	}
	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $max );
	// the last sentence end that still leaves a useful description (Latin, Arabic and CJK punctuation)
	if ( ( preg_match( '/^(.{90,}[.!?。！？؟])(?=\s|$)/us', $cut, $s ) || preg_match( '/^(.{20,}[。！？])/us', $cut, $s ) ) ) {
		return trim( $s[1] );
	}
	$space = mb_strrpos( $cut, ' ' );
	return rtrim( false !== $space && $space > 60 ? mb_substr( $cut, 0, $space ) : $cut, " ,;:–-" ) . '…';
}

// Feeds are a reading channel, not search landing pages (and stay empty while nothing is published).
add_action( 'template_redirect', static function () {
	if ( is_feed() && ! headers_sent() ) {
		header( 'X-Robots-Tag: noindex, follow', true );
	}
}, 0 );

// /<page>/page/2/ on an unpaginated single answered 200 with the same content: one hop back to the page.
add_action( 'template_redirect', static function () {
	if ( ! is_singular() || max( (int) get_query_var( 'page' ), (int) get_query_var( 'paged' ) ) < 2 ) {
		return;
	}
	$post = get_queried_object();
	if ( $post instanceof WP_Post && ! str_contains( (string) $post->post_content, '<!--nextpage-->' ) ) {
		wp_safe_redirect( get_permalink( $post ), 301 );
		exit;
	}
}, 5 );

/**
 * Slugs never end mid-word. WordPress caps a slug at 200 bytes, and a URL-encoded Arabic or Cyrillic letter
 * takes 4–6 of them, so long titles were cut inside a word (".../وأبي-الهو/" for "وأبي الهول", ".../к-пирамида/"
 * for "к пирамидам"). When the cap was reached and the last part of the slug is not a whole word of the title,
 * that part is dropped. Existing posts: `wp egypt-roamer slugs` (WordPress keeps the old slug as a redirect).
 */
function er_slug_trim_partial( string $slug, string $raw_title ): string {
	if ( strlen( $slug ) < 180 || ! str_contains( $slug, '-' ) ) {
		return $slug; // far from the cap: never touched
	}
	$words = array_values( array_filter( array_map( static fn ( $w ) => urldecode( sanitize_title_with_dashes( $w, '', 'save' ) ), preg_split( '/[\s\-–—:;,.!?،؛]+/u', $raw_title ) ) ) );
	$parts = explode( '-', urldecode( $slug ) );
	$last  = count( $parts ) - 1;
	if ( isset( $words[ $last ] ) && $parts[ $last ] !== $words[ $last ] ) {
		array_pop( $parts );
		// nor on a dangling one- or two-letter connector ("…-к", "…-и", "…-de")
		while ( count( $parts ) > 2 && mb_strlen( (string) end( $parts ) ) <= 2 ) {
			array_pop( $parts );
		}
		return implode( '-', array_map( static fn ( $p ) => utf8_uri_encode( $p ), $parts ) );
	}
	return $slug;
}
add_filter( 'sanitize_title', static function ( $title, $raw_title = '', $context = 'display' ) {
	return 'save' === $context && is_string( $title ) && '' !== (string) $raw_title ? er_slug_trim_partial( $title, (string) $raw_title ) : $title;
}, 11, 3 );
