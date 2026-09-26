<?php
/**
 * Multilingual bridge (Polylang). Every call is guarded, so the platform works
 * monolingually without it.
 *
 * - Editorial types (destinations, tours, experiences, activities, guides) and
 *   their display taxonomies are translatable: each language has its own URL,
 *   Polylang outputs hreflang only for translations that exist.
 * - Affiliate providers and offers are language-neutral business records: one
 *   offer serves every translation of the page it is attached to, so clicks
 *   aggregate per offer regardless of language.
 * - Free-text labels (disclosure, custom CTA labels) are registered for
 *   Polylang's string translation screen.
 */

defined( 'ABSPATH' ) || exit;

function er_current_lang(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( $lang ) {
			return (string) $lang;
		}
	}
	return substr( determine_locale(), 0, 2 );
}

function er_post_lang( int $post_id ): string {
	if ( function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( $post_id, 'slug' );
		if ( $lang ) {
			return (string) $lang;
		}
	}
	return er_current_lang();
}

/** The current-language version of a post, or the post itself. */
function er_translated_post_id( int $post_id ): int {
	if ( function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( $post_id );
		if ( $tr ) {
			return (int) $tr;
		}
	}
	return $post_id;
}

/** Every translation of a post (including itself) — used to match language-neutral offers. */
function er_translation_group( int $post_id ): array {
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_map( 'intval', array_values( (array) pll_get_post_translations( $post_id ) ) );
		if ( $ids ) {
			return array_values( array_unique( array_merge( [ $post_id ], $ids ) ) );
		}
	}
	return [ $post_id ];
}

function er_translate_string( string $text ): string {
	return function_exists( 'pll__' ) ? (string) pll__( $text ) : $text;
}

add_filter( 'pll_get_post_types', static function ( $types, $is_settings ) {
	foreach ( er_public_type_keys() as $type ) {
		$types[ $type ] = $type;
	}
	unset( $types['er_provider'], $types['er_offer'], $types['er_message'] );
	return $types;
}, 10, 2 );

add_filter( 'pll_get_taxonomies', static function ( $taxonomies, $is_settings ) {
	foreach ( [ 'er_region', 'er_travel_style', 'er_guide_topic', 'er_offer_type' ] as $tax ) {
		$taxonomies[ $tax ] = $tax;
	}
	return $taxonomies;
}, 10, 2 );

add_action( 'admin_init', static function () {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	pll_register_string( 'disclosure_text', (string) er_settings( 'disclosure_text' ), 'Egypt Roamer', true );
	foreach ( er_public_type_keys() as $type ) {
		$intro = (string) er_settings( 'archive_intro_' . $type );
		if ( '' !== $intro ) {
			pll_register_string( 'archive_intro_' . $type, $intro, 'Egypt Roamer archives', true );
		}
	}
	global $wpdb;
	$labels = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_er_cta_custom' AND meta_value <> '' LIMIT 200" ); // phpcs:ignore WordPress.DB
	foreach ( $labels as $label ) {
		pll_register_string( 'cta_' . md5( $label ), $label, 'Egypt Roamer CTAs' );
	}
} );
