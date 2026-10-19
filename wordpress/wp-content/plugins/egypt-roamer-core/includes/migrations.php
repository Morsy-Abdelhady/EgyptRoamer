<?php
/**
 * One-time data updates that the seed's rule (never overwrite editors' content) keeps from reaching a live site.
 * Each runs once, when an administrator opens wp-admin, and records itself in the option er_migrations.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', static function (): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$done = (array) get_option( 'er_migrations', [] );

	// 20 Oct (owner): the Food mood pointed to Alexandria; the only food trip is the Cairo street-food tour.
	if ( empty( $done['food-mood-cairo'] ) ) {
		$term  = get_term_by( 'slug', 'food', 'er_travel_style' );
		$cairo = get_page_by_path( 'cairo', OBJECT, 'er_destination' );
		if ( $term instanceof WP_Term && $cairo ) {
			update_term_meta( $term->term_id, '_er_destination', (int) $cairo->ID );
			$done['food-mood-cairo'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
	// The same for the Food mood in every language (each translation keeps its own copy of the place).
	if ( empty( $done['food-mood-cairo-all'] ) ) {
		$term  = get_term_by( 'slug', 'food', 'er_travel_style' );
		$cairo = get_page_by_path( 'cairo', OBJECT, 'er_destination' );
		if ( $term instanceof WP_Term && $cairo ) {
			$ids = function_exists( 'pll_get_term_translations' ) ? array_values( (array) pll_get_term_translations( $term->term_id ) ) : [];
			foreach ( array_unique( array_merge( [ $term->term_id ], array_map( 'intval', $ids ) ) ) as $tid ) {
				if ( '' !== (string) get_term_meta( (int) $tid, '_er_destination', true ) ) {
					update_term_meta( (int) $tid, '_er_destination', (int) $cairo->ID );
				}
			}
			$done['food-mood-cairo-all'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
} );
