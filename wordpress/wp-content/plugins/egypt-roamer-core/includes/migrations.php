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
	// 20 Oct (owner): the Food mood's line now fits Cairo (it named grilled fish by the Mediterranean).
	if ( empty( $done['food-mood-line-cairo'] ) ) {
		$term = get_term_by( 'slug', 'food', 'er_travel_style' );
		$line = [
			'en' => 'Of koshari at midnight, warm baladi bread and the street stalls of old Cairo.',
			'de' => 'Mit Koshari um Mitternacht, warmem Baladi-Brot und den Straßenständen im alten Kairo.',
			'fr' => 'Faite de koshari à minuit, de pain baladi tout chaud et des échoppes du vieux Caire.',
			'it' => 'Fatto di koshari a mezzanotte, pane baladi caldo e bancarelle del Cairo antico.',
			'es' => 'De koshari a medianoche, pan baladi caliente y los puestos callejeros de El Cairo antiguo.',
			'ru' => 'Кошари в полночь, тёплый хлеб балади и уличные лавки старого Каира.',
			'zh' => '午夜的科沙丽、热乎乎的巴拉迪面包，还有老开罗街头的小摊。',
			'ar' => 'بكشريّها في منتصف الليل، وخبزها البلدي الدافئ، وعربات الأكل في شوارع القاهرة القديمة.',
		];
		if ( $term instanceof WP_Term ) {
			$ids = function_exists( 'pll_get_term_translations' ) ? (array) pll_get_term_translations( $term->term_id ) : [ 'en' => $term->term_id ];
			foreach ( $ids as $lang => $tid ) {
				if ( isset( $line[ $lang ] ) ) {
					wp_update_term( (int) $tid, 'er_travel_style', [ 'description' => $line[ $lang ] ] );
				}
			}
			$done['food-mood-line-cairo'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
} );
