<?php
/**
 * Stale-HTML guard (docs/CACHE-2026-10-01.md).
 *
 * The host's edge (Cloudflare in front of GoDaddy) caches every page and replaces WordPress's
 * Cache-Control with `public, max-age=2678400`: a browser that saw a page keeps showing that copy for up
 * to 31 days without asking again, even after a deploy and a cache flush. No response header from
 * WordPress can change that (measured 2026-09-27 and 2026-10-01).
 *
 * So each page carries a build id (code versions + a content epoch that changes when editors save), and
 * a tiny script compares it with the current one from GET /wp-json/egypt-roamer/v1/build, which the edge
 * never caches. A stale page reloads once; if the edge itself is stale, the page is fetched once with
 * ?nocache=… (the edge's documented bypass). Static assets keep their long caching (they are versioned).
 */

defined( 'ABSPATH' ) || exit;

/** The id of what the site would serve right now (short hash). The theme adds its asset versions. */
function er_build_id(): string {
	$parts = apply_filters( 'er_build_id_parts', [
		'core'    => ER_CORE_VERSION,
		'content' => (string) get_option( 'er_content_epoch', '0' ),
	] );
	return substr( md5( wp_json_encode( $parts ) ), 0, 12 );
}

/** Editors changed something visitors see: new content epoch. */
function er_bump_content_epoch(): void {
	static $done = false; // once per request (a save fires many hooks)
	if ( $done ) {
		return;
	}
	$done = true;
	update_option( 'er_content_epoch', (string) time(), true );
}

add_action( 'save_post', static function ( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'auto-draft' === $post->post_status ) {
		return;
	}
	// Only what visitors can see; never visitor submissions (contact messages) or internal records.
	$visible = array_merge( array_values( get_post_types( [ 'public' => true ] ) ), [ 'er_offer', 'er_provider', 'polylang_mo', 'nav_menu_item', 'wp_block', 'wp_navigation' ] );
	if ( in_array( $post->post_type, $visible, true ) ) {
		er_bump_content_epoch();
	}
}, 10, 2 );
add_action( 'deleted_post', static function ( $post_id ) {
	if ( in_array( get_post_type( $post_id ), array_values( get_post_types( [ 'public' => true ] ) ), true ) ) {
		er_bump_content_epoch();
	}
} );
add_action( 'wp_update_nav_menu', 'er_bump_content_epoch' );
add_action( 'updated_option', static function ( $option ) {
	// Settings that change pages. Not counters, rate limits or this option itself.
	if ( in_array( $option, [ 'er_settings', 'polylang', 'page_on_front', 'page_for_posts', 'show_on_front', 'blogname', 'blogdescription', 'wp_page_for_privacy_policy', 'sidebars_widgets' ], true ) || str_starts_with( $option, 'er_home_' ) ) {
		er_bump_content_epoch();
	}
} );

add_action( 'rest_api_init', static function () {
	register_rest_route( 'egypt-roamer/v1', '/build', [
		'methods'             => 'GET',
		'permission_callback' => '__return_true', // public: a short hash, nothing else
		'callback'            => static function () {
			$response = rest_ensure_response( [ 'build' => er_build_id() ] );
			$response->header( 'Cache-Control', 'no-store, private' );
			$response->header( 'X-Robots-Tag', 'noindex, nofollow' );
			return $response;
		},
	] );
} );
