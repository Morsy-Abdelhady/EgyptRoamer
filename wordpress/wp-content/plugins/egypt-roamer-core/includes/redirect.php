<?php
/**
 * /go/{slug}/ — the single public affiliate redirect URL.
 *
 * Two steps, so that no CDN or page cache can ever swallow a click:
 *
 * A. /go/{slug}/ is stateless: it forwards to the dynamic handler with the slug
 *    and the whitelisted click parameters, without reading any offer. A cached
 *    copy of this hop is therefore always correct.
 * B. /wp-admin/admin-post.php?action=er_go&offer={slug} is the dynamic handler
 *    (WordPress's own request endpoint, which managed hosts exclude from page
 *    and CDN caching — measured on GoDaddy: cf-cache-status DYNAMIC, gateway BYPASS):
 *    1. resolve the slug to a configured offer (or a provider's default link)
 *    2. validate: offer live, URL http(s), host in the provider's allow-list
 *    3. log the click (business context only; bots and prefetches are skipped)
 *    4. apply configured tracking parameters
 *    5. redirect
 *
 * Visitors can never choose the destination: query parameters only feed the
 * click log and whitelisted search placeholders. Responses are noindex and
 * uncacheable. Hosts without an edge cache can serve step B directly at /go/
 * with add_filter( 'er_go_via_dynamic_endpoint', '__return_false' ).
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
	return str_contains( $purpose, 'prefetch' ) || 'GET' !== $method; // only real navigations count as clicks
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
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public link, logged only; digits only (no "-5abc" → 5)
	$src  = isset( $_GET['src'] ) && is_string( $_GET['src'] ) && ctype_digit( wp_unslash( $_GET['src'] ) ) ? (int) $_GET['src'] : 0;
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
	$adults = isset( $_GET['adults'] ) && is_scalar( $_GET['adults'] ) ? absint( $_GET['adults'] ) : 0;
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

/** Click parameters the public /go/ hop may pass on to the dynamic handler (values are validated there). */
function er_go_forward_args(): array {
	$args = [];
	foreach ( [ 'pl', 'src', 'where', 'when', 'adults' ] as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public link; every value is validated by the handler
		if ( isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) {
			$args[ $key ] = substr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), 0, 64 );
		}
	}
	return $args;
}

/** The dynamic handler URL for a slug. */
function er_go_endpoint_url( string $slug, array $args = [] ): string {
	return add_query_arg( array_map( 'rawurlencode', [ 'action' => 'er_go', 'offer' => $slug ] + $args ), admin_url( 'admin-post.php' ) );
}

/** Resolve, validate, log and redirect one click. Always exits. */
function er_handle_go( string $slug, bool $themed_404 ): void {
	er_send_redirect_headers();
	$status = (int) er_settings( 'redirect_status' ) === 307 ? 307 : 302;

	$offer    = $slug ? get_page_by_path( $slug, OBJECT, 'er_offer' ) : null;
	$provider = $slug && ! $offer ? get_page_by_path( $slug, OBJECT, 'er_provider' ) : null;

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

	if ( $themed_404 ) {
		// Unknown or unusable slug on /go/ itself: a real 404 (noindex) so broken links show up in logs.
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		return;
	}
	// Dynamic endpoint: unknown, draft, trashed or deleted offer → the home page, never a partner.
	wp_safe_redirect( home_url( '/' ), 302 );
	exit;
}

// A. Public /go/{slug}/.
add_action( 'template_redirect', static function () {
	$slug = get_query_var( 'er_go' );
	if ( ! $slug ) {
		return;
	}
	$slug = sanitize_title( (string) $slug );
	if ( apply_filters( 'er_go_via_dynamic_endpoint', true ) ) {
		// Stateless hop: reads no offer, so even a cached copy stays correct.
		er_send_redirect_headers();
		wp_redirect( er_go_endpoint_url( $slug, er_go_forward_args() ), 302, 'Egypt Roamer' ); // phpcs:ignore WordPress.Security.SafeRedirect -- own admin URL
		exit;
	}
	er_handle_go( $slug, true );
}, 0 );

// B. Dynamic handler (anonymous visitors and logged-in editors alike).
$er_go_handler = static function () {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public link; the slug only selects a configured offer
	$raw  = isset( $_GET['offer'] ) && is_string( $_GET['offer'] ) ? wp_unslash( $_GET['offer'] ) : '';
	$slug = preg_match( '/^[a-z0-9-]{1,200}$/', $raw ) ? $raw : '';
	er_handle_go( $slug, false );
};
add_action( 'admin_post_nopriv_er_go', $er_go_handler );
add_action( 'admin_post_er_go', $er_go_handler );
