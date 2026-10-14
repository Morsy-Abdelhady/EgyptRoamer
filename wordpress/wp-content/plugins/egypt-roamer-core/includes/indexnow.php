<?php
/**
 * IndexNow: tells Bing (and the other IndexNow engines: Yandex, Seznam, Naver…) which URLs changed, so they need
 * not wait to discover them. Google does not use IndexNow; it has the sitemap and Search Console.
 *
 * - The key is generated once and stored in the er_indexnow_key option; the engines verify it at /{key}.txt.
 * - A published, indexable item that is saved (or stops being published) is queued and sent a minute later, in one
 *   batch with whatever else changed.
 * - Once per "round" (ER_INDEXNOW_ROUND) every indexable URL is sent, e.g. after the site first became indexable.
 * - Nothing is sent while "Discourage search engines" is ticked. No personal data is involved: only public URLs.
 *
 * @package EgyptRoamerCore
 */

defined( 'ABSPATH' ) || exit;

/** Bump to send every indexable URL once more (one full submission per round). */
const ER_INDEXNOW_ROUND = '2026-10-15';

/** The site's IndexNow key (32 hex characters), created on first use. */
function er_indexnow_key(): string {
	$key = (string) get_option( 'er_indexnow_key', '' );
	if ( ! preg_match( '/^[a-f0-9]{32}$/', $key ) ) {
		$key = bin2hex( random_bytes( 16 ) );
		update_option( 'er_indexnow_key', $key, true );
	}
	return $key;
}

function er_indexnow_enabled(): bool {
	return '1' === (string) get_option( 'blog_public' ) && (bool) apply_filters( 'er_indexnow_enabled', true );
}

// The key file the engines fetch to verify ownership: https://egyptroamer.com/{key}.txt
add_action( 'parse_request', static function () {
	$path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$key  = (string) get_option( 'er_indexnow_key', '' );
	if ( '' === $key || '/' . $key . '.txt' !== $path ) {
		return;
	}
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex', true );
	echo $key; // phpcs:ignore WordPress.Security.EscapeOutput -- 32 hex characters
	exit;
}, 0 );

/** Every URL that should be in search: published, indexable items in every language, and each language's homepage. */
function er_indexnow_all_urls(): array {
	$ids = get_posts( [
		'post_type'      => array_merge( [ 'page' ], er_gated_types() ),
		'post_status'    => 'publish',
		'has_password'   => false,
		'posts_per_page' => 2000,
		'fields'         => 'ids',
		'lang'           => '',
	] );
	$skip = function_exists( 'er_empty_posts_page_ids' ) ? er_empty_posts_page_ids() : [];
	$urls = [];
	foreach ( $ids as $id ) {
		if ( ! in_array( (int) $id, $skip, true ) && er_is_indexable( (int) $id ) ) {
			$urls[] = (string) get_permalink( (int) $id );
		}
	}
	// Each language's homepage and its section archives (an archive is indexable only while it lists an indexable item).
	$langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : [ '' ];
	foreach ( $langs as $lang ) {
		$urls[] = function_exists( 'pll_home_url' ) && '' !== $lang ? (string) pll_home_url( (string) $lang ) : home_url( '/' );
		foreach ( er_public_type_keys() as $type ) {
			$listed = get_posts( [ 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_er_indexable', 'meta_value' => '1', 'lang' => (string) $lang ] );
			$link   = $listed ? (string) get_post_type_archive_link( $type ) : '';
			if ( '' !== $link && '' !== $lang && function_exists( 'PLL' ) && PLL()->model->get_language( (string) $lang ) ) {
				$link = (string) PLL()->links_model->switch_language_in_link( $link, PLL()->model->get_language( (string) $lang ) );
			}
			if ( '' !== $link ) {
				$urls[] = $link;
			}
		}
	}
	return array_values( array_unique( array_filter( $urls ) ) );
}

/** Send URLs (same host) to the IndexNow endpoint; records the outcome for the dashboard. */
function er_indexnow_send( array $urls ): void {
	$host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$urls = array_values( array_unique( array_filter( $urls, static fn ( $u ) => $host === wp_parse_url( (string) $u, PHP_URL_HOST ) ) ) );
	if ( ! $urls || ! er_indexnow_enabled() ) {
		return;
	}
	$key = er_indexnow_key();
	foreach ( array_chunk( $urls, 10000 ) as $chunk ) {
		$response = wp_remote_post( 'https://api.indexnow.org/indexnow', [
			'timeout' => 20,
			'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
			'body'    => wp_json_encode( [
				'host'        => $host,
				'key'         => $key,
				'keyLocation' => home_url( '/' . $key . '.txt' ),
				'urlList'     => $chunk,
			] ),
		] );
		update_option( 'er_indexnow_last', [
			'time'  => time(),
			'count' => count( $chunk ),
			'code'  => is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response ),
			'error' => is_wp_error( $response ) ? $response->get_error_message() : '',
		], false );
	}
}

// Changed items: queue their URLs and send them together a minute later.
function er_indexnow_queue( string $url ): void {
	$queue   = (array) get_option( 'er_indexnow_queue', [] );
	$queue[] = $url;
	update_option( 'er_indexnow_queue', array_values( array_unique( $queue ) ), false );
	if ( ! wp_next_scheduled( 'er_indexnow_flush' ) ) {
		wp_schedule_single_event( time() + 60, 'er_indexnow_flush' );
	}
}

add_action( 'transition_post_status', static function ( $new, $old, $post ) {
	if ( ! $post instanceof WP_Post || wp_is_post_revision( $post ) || ! in_array( $post->post_type, array_merge( [ 'page' ], er_gated_types() ), true ) ) {
		return;
	}
	// Published and indexable: tell the engines it changed. Unpublished: tell them, so they re-check it (now a 404).
	if ( ( 'publish' === $new && er_is_indexable( $post->ID ) ) || ( 'publish' === $old && 'publish' !== $new ) ) {
		er_indexnow_queue( (string) get_permalink( $post ) );
	}
}, 20, 3 );

// "Ready to index" ticked on a published item: it is new to search.
add_action( 'updated_post_meta', 'er_indexnow_on_ready', 10, 4 );
add_action( 'added_post_meta', 'er_indexnow_on_ready', 10, 4 );
function er_indexnow_on_ready( $meta_id, $post_id, $key, $value ): void {
	if ( '_er_indexable' === $key && $value && 'publish' === get_post_status( (int) $post_id ) ) {
		er_indexnow_queue( (string) get_permalink( (int) $post_id ) );
	}
}

add_action( 'er_indexnow_flush', static function () {
	$queue = (array) get_option( 'er_indexnow_queue', [] );
	delete_option( 'er_indexnow_queue' );
	er_indexnow_send( $queue );
} );

// One full submission per round, in the background (the first page load after a release schedules it).
add_action( 'init', static function () {
	if ( ER_INDEXNOW_ROUND === get_option( 'er_indexnow_round' ) || ! er_indexnow_enabled() ) {
		return;
	}
	update_option( 'er_indexnow_round', ER_INDEXNOW_ROUND, true );
	er_indexnow_key();
	wp_schedule_single_event( time() + 30, 'er_indexnow_full' );
} );
add_action( 'er_indexnow_full', static function () {
	er_indexnow_send( er_indexnow_all_urls() );
} );
