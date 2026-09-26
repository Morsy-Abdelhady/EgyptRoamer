<?php
/**
 * Theme setup: supports, menus, image sizes, head hygiene.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', static function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'editor-styles' );
	add_editor_style( [ 'assets/css/tokens.css', 'assets/css/editor.css' ] );

	register_nav_menus( [
		'primary'        => __( 'Primary navigation', 'egypt-roamer' ),
		'footer_explore' => __( 'Footer — Explore', 'egypt-roamer' ),
		'footer_plan'    => __( 'Footer — Plan', 'egypt-roamer' ),
		'footer_company' => __( 'Footer — Egypt Roamer', 'egypt-roamer' ),
		'legal'          => __( 'Legal links', 'egypt-roamer' ),
	] );

	// Full-width, uncropped sizes feed responsive srcsets (WordPress adds WebP when the server supports it).
	add_image_size( 'er-hero', 2400, 0 );
	add_image_size( 'er-card', 720, 900, true );
} );

/** Output WebP sub-sizes for JPEG uploads when the server's image library supports it. */
add_filter( 'image_editor_output_format', static function ( $formats ) {
	if ( wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ) {
		$formats['image/jpeg'] = 'image/webp';
	}
	return $formats;
} );

/** The theme renders data from Egypt Roamer Core; say so if it is missing. */
add_action( 'admin_notices', static function () {
	if ( ! function_exists( 'er_get_offers' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'The Egypt Roamer theme needs the Egypt Roamer Core plugin to be active.', 'egypt-roamer' ) . '</p></div>';
	}
} );

/** Clean archive titles ("Destinations", not "Archives: Destinations"). */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/** No emoji polyfill (performance; every supported browser renders emoji natively). */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/** Load block CSS only for blocks present on the page. */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

add_filter( 'excerpt_length', static fn () => 28 );
add_filter( 'excerpt_more', static fn () => '…' );

/** Page-type body classes the CSS/JS use. */
add_filter( 'body_class', static function ( $classes ) {
	$classes[] = is_front_page() ? 'er-home' : 'er-inner';
	// WordPress adds "search" on results pages; the design uses .search for the search overlay.
	return array_values( array_diff( $classes, [ 'search' ] ) );
} );

/** Archives list 12 items per page. */
add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_guide' ] ) ) {
		$q->set( 'posts_per_page', 12 );
		$q->set( 'orderby', [ 'menu_order' => 'ASC', 'date' => 'DESC' ] );
		// Whitelisted filters only (these URLs are noindex via Egypt Roamer Core).
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$dest = isset( $_GET['destination'] ) ? absint( $_GET['destination'] ) : 0;
		if ( $dest && ! $q->is_post_type_archive( 'er_destination' ) ) {
			$q->set( 'meta_query', [ [ 'key' => '_er_destination', 'value' => er_translation_group( $dest ), 'compare' => 'IN', 'type' => 'NUMERIC' ] ] );
		}
		$style = isset( $_GET['style'] ) ? sanitize_key( wp_unslash( $_GET['style'] ) ) : '';
		if ( $style && taxonomy_exists( 'er_travel_style' ) ) {
			$q->set( 'tax_query', [ [ 'taxonomy' => 'er_travel_style', 'field' => 'slug', 'terms' => $style ] ] );
		}
		$topic = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( $_GET['topic'] ) ) : '';
		if ( $topic && $q->is_post_type_archive( 'er_guide' ) ) {
			$q->set( 'tax_query', [ [ 'taxonomy' => 'er_guide_topic', 'field' => 'slug', 'terms' => $topic ] ] );
		}
		// phpcs:enable
	}
	// Internal search covers the editorial types and articles (never affiliate records).
	if ( $q->is_search() ) {
		$q->set( 'post_type', [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_guide', 'post', 'page' ] );
		$q->set( 'posts_per_page', 12 );
	}
} );
