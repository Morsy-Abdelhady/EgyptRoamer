<?php
/**
 * /go/{slug}/ — the single affiliate redirect endpoint.
 *
 * 1. resolve the slug to a configured offer (or a provider's default link)
 * 2. validate: offer live, URL http(s), host in the provider's allow-list
 * 3. log the click (business context only; bots and prefetches are skipped)
 * 4. apply configured tracking parameters
 * 5. redirect
 *
 * Visitors can never choose the destination: query parameters only feed the
 * click log and whitelisted search placeholders. Responses are noindex and
 * uncacheable so page caching can never swallow a click.
 */

defined( 'ABSPATH' ) || exit;

function er_register_redirect_rewrites(): void {
	add_rewrite_rule( '^go/([a-z0-9-]+)/?$', 'index.php?er_go=$matches[1]', 'top' );
}
add_action( 'init', 'er_register_redirect_rewrites' );

add_filter( 'query_vars', static function ( $vars ) {
	$vars[] = 'er_go';
	return $vars;
} );

/** Crawlers, link checkers and previews should not count as clicks. */
function er_is_bot_request(): bool {
	$ua = strtolower( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );
	if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|whatsapp|telegram|headless|lighthouse|pingdom|monitor|curl|wget|python-requests|httpclient|scrapy/', $ua ) ) {
		return true;
	}
	$purpose = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '' ) ) );
	$method  = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) );
	return str_contains( $purpose, 'prefetch' ) || 'HEAD' === $method;
}

/** First non-empty relation, falling back through the linked tour/experience/activity. */
function er_click_context( int $offer_id ): array {
	$ctx = [];
	foreach ( [ 'destination', 'tour', 'experience', 'activity' ] as $rel ) {
		$ctx[ $rel . '_id' ] = (int) get_post_meta( $offer_id, '_er_' . $rel, true );
	}
	if ( ! $ctx['destination_id'] ) {
		foreach ( [ 'tour_id', 'experience_id', 'activity_id' ] as $k ) {
			if ( $ctx[ $k ] ) {
				$dest = get_post_meta( $ctx[ $k ], '_er_destination', false );
				if ( $dest ) {
					$ctx['destination_id'] = (int) $dest[0];
					break;
				}
			}
		}
	}
	return $ctx;
}

/** Where the visitor clicked: a published post ID and a path on this site (never a query string). */
function er_click_source(): array {
	$src  = isset( $_GET['src'] ) ? absint( $_GET['src'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public link, logged only
	$post = $src ? get_post( $src ) : null;
	if ( ! $post || 'publish' !== $post->post_status || ! is_post_type_viewable( $post->post_type ) ) {
		$src  = 0;
		$post = null;
	}
	$path = '';
	$ref  = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	if ( $ref && wp_parse_url( $ref, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		$path = (string) wp_parse_url( $ref, PHP_URL_PATH );
	} elseif ( $post ) {
		$path = (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
	}
	return [
		'source_post_id' => $src,
		'source_path'    => '' === $path ? '/' : $path,
		'page'           => $post ? $post->post_name : ( '/' === $path || '' === $path ? 'home' : trim( $path, '/' ) ),
		'lang'           => $post ? er_post_lang( $post->ID ) : er_current_lang(),
	];
}

/** Whitelisted search values for offers with a search URL template. */
function er_finder_search(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public GET, values are whitelisted
	$search = [];
	$where  = isset( $_GET['where'] ) ? sanitize_text_field( wp_unslash( $_GET['where'] ) ) : '';
	if ( '' !== $where && mb_strlen( $where ) <= 60 && preg_match( '/^[\p{L}\p{N} &\'.,-]+$/u', $where ) ) {
		$search['destination'] = $where;
	}
	$when = isset( $_GET['when'] ) ? sanitize_text_field( wp_unslash( $_GET['when'] ) ) : '';
	if ( preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $when ) ) {
		$search['month'] = $when;
	}
	$adults = isset( $_GET['adults'] ) ? absint( $_GET['adults'] ) : 0;
	if ( $adults >= 1 && $adults <= 12 ) {
		$search['adults'] = $adults;
	}
	// phpcs:enable
	return $search;
}

/** Safe internal fallback when an offer cannot redirect: its related page, else the home page. */
function er_offer_fallback_url( int $offer_id ): string {
	foreach ( [ '_er_tour', '_er_experience', '_er_activity', '_er_destination' ] as $key ) {
		$id = (int) get_post_meta( $offer_id, $key, true );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			return get_permalink( $id );
		}
	}
	return home_url( '/' );
}

function er_send_redirect_headers(): void {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true ); // honoured by most page caches
	}
	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}

add_action( 'template_redirect', static function () {
	$slug = get_query_var( 'er_go' );
	if ( ! $slug ) {
		return;
	}
	$slug = sanitize_title( (string) $slug );
	er_send_redirect_headers();
	$status = (int) er_settings( 'redirect_status' ) === 307 ? 307 : 302;

	$offer    = get_page_by_path( $slug, OBJECT, 'er_offer' );
	$provider = $offer ? null : get_page_by_path( $slug, OBJECT, 'er_provider' );

	if ( $offer && 'publish' === $offer->post_status ) {
		if ( ! er_offer_is_live( $offer->ID ) ) {
			wp_safe_redirect( er_offer_fallback_url( $offer->ID ), 302 );
			exit;
		}
		$source = er_click_source();
		$place  = isset( $_GET['pl'] ) ? substr( sanitize_key( wp_unslash( $_GET['pl'] ) ), 0, 64 ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$place  = '' === $place ? 'direct' : $place;
		$url    = er_offer_destination_url( $offer->ID, [ 'placement' => $place, 'page' => $source['page'], 'search' => er_finder_search() ] );
		if ( is_wp_error( $url ) ) {
			wp_safe_redirect( er_offer_fallback_url( $offer->ID ), 302 );
			exit;
		}
		if ( ! er_is_bot_request() ) {
			er_log_click( er_click_context( $offer->ID ) + [
				'offer_id'       => $offer->ID,
				'provider_id'    => (int) get_post_meta( $offer->ID, '_er_provider', true ),
				'source_post_id' => $source['source_post_id'],
				'source_path'    => $source['source_path'],
				'placement'      => $place,
				'cta'            => er_offer_cta_label( $offer->ID ),
				'utm_source'     => (string) get_post_meta( $offer->ID, '_er_utm_source', true ),
				'utm_medium'     => (string) get_post_meta( $offer->ID, '_er_utm_medium', true ),
				'utm_campaign'   => (string) get_post_meta( $offer->ID, '_er_utm_campaign', true ),
				'lang'           => $source['lang'],
			] );
			do_action( 'er_affiliate_click', $offer->ID, $url );
		}
		wp_redirect( $url, $status, 'Egypt Roamer' ); // phpcs:ignore WordPress.Security.SafeRedirect -- host validated against the provider allow-list
		exit;
	}

	if ( $provider && 'publish' === $provider->post_status && er_provider_is_active( $provider->ID ) ) {
		$url     = (string) get_post_meta( $provider->ID, '_er_default_url', true );
		$domains = er_provider_domains( $provider->ID );
		if ( $url && $domains && er_host_allowed( $url, $domains ) && in_array( wp_parse_url( $url, PHP_URL_SCHEME ), [ 'http', 'https' ], true ) ) {
			if ( ! er_is_bot_request() ) {
				$source = er_click_source();
				er_log_click( [
					'provider_id'    => $provider->ID,
					'source_post_id' => $source['source_post_id'],
					'source_path'    => $source['source_path'],
					'placement'      => isset( $_GET['pl'] ) ? substr( sanitize_key( wp_unslash( $_GET['pl'] ) ), 0, 64 ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'cta'            => 'provider',
					'lang'           => $source['lang'],
				] );
			}
			wp_redirect( $url, $status, 'Egypt Roamer' ); // phpcs:ignore WordPress.Security.SafeRedirect -- validated above
			exit;
		}
	}

	// Unknown or unusable slug: a real 404 (noindex) so broken links show up in Search Console and logs.
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
}, 0 );
