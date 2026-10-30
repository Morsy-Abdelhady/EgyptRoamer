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
	// 20 Oct (owner): an About page and the Sources page every guide promises, in each language.
	if ( empty( $done['trust-pages-3'] ) && function_exists( 'pll_set_post_language' ) ) {
		$pages = (array) require ER_CORE_DIR . 'data/trust-pages.php';
		$ids   = [];
		foreach ( $pages as $kind => $langs ) {
			$group = [];
			foreach ( $langs as $lang => [ $slug, $title, $content ] ) {
				$found = get_posts( [ 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'lang' => '' ] );
				$id    = $found ? (int) $found[0] : (int) wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content ] );
				if ( ! $id ) {
					continue;
				}
				// The seed's untouched placeholder (an unpublished draft with its editorial note) gets this text;
				// a page an editor has written is left as it is.
				if ( $found && 'publish' !== get_post_status( $id ) && false !== strpos( (string) get_post_field( 'post_content', $id ), 'er-editorial-note' ) ) {
					wp_update_post( [ 'ID' => $id, 'post_title' => $title, 'post_content' => $content, 'post_status' => 'publish' ] );
				}
				pll_set_post_language( $id, $lang );
				if ( in_array( $kind, [ 'sources', 'hotels' ], true ) ) {
					update_post_meta( $id, '_wp_page_template', 'templates/' . $kind . '.php' );
				}
				$group[ $lang ] = $id;
			}
			if ( function_exists( 'pll_save_post_translations' ) && $group ) {
				pll_save_post_translations( $group );
			}
			$ids[ $kind ] = (int) ( $group['en'] ?? 0 );
		}
		update_option( 'er_trust_pages', $ids, false );
		$done['trust-pages-3'] = gmdate( 'c' );
		update_option( 'er_migrations', $done, false );
	}
	// 25 Oct (owner): Klook joins the partners named in the About page's "How we work", in each language. Only the
	// partner list is replaced, so the rest of a page an editor has changed is kept.
	if ( empty( $done['about-partners-klook'] ) ) {
		$trust = (array) get_option( 'er_trust_pages', [] );
		$about = (int) ( $trust['about'] ?? 0 );
		if ( $about ) {
			$swap = [
				'Viator, GetYourGuide or Trip.com'     => 'Viator, GetYourGuide, Klook or Trip.com',
				'Viator, GetYourGuide oder Trip.com'   => 'Viator, GetYourGuide, Klook oder Trip.com',
				'Viator, GetYourGuide ou Trip.com'     => 'Viator, GetYourGuide, Klook ou Trip.com',
				'Viator, GetYourGuide o Trip.com'      => 'Viator, GetYourGuide, Klook o Trip.com',
				'Viator, GetYourGuide или Trip.com'    => 'Viator, GetYourGuide, Klook или Trip.com',
				'Viator、GetYourGuide 或 Trip.com'     => 'Viator、GetYourGuide、Klook 或 Trip.com',
				'Viator أو GetYourGuide أو Trip.com'   => 'Viator أو GetYourGuide أو Klook أو Trip.com',
			];
			$ids  = function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $about ) : [ $about ];
			foreach ( array_unique( array_map( 'intval', array_merge( [ $about ], array_values( $ids ) ) ) ) as $id ) {
				$content = (string) get_post_field( 'post_content', $id );
				$new     = strtr( $content, $swap );
				if ( $new !== $content ) {
					wp_update_post( wp_slash( [ 'ID' => $id, 'post_content' => $new ] ) );
				}
			}
			$done['about-partners-klook'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
	if ( empty( $done['about-partners-tiqets'] ) ) {
		$trust = (array) get_option( 'er_trust_pages', [] );
		$about = (int) ( $trust['about'] ?? 0 );
		if ( $about ) {
			$swap = [
				'Klook or Trip.com'   => 'Klook, Tiqets or Trip.com',
				'Klook oder Trip.com' => 'Klook, Tiqets oder Trip.com',
				'Klook ou Trip.com'   => 'Klook, Tiqets ou Trip.com',
				'Klook o Trip.com'    => 'Klook, Tiqets o Trip.com',
				'Klook или Trip.com'  => 'Klook, Tiqets или Trip.com',
				'Klook 或 Trip.com'   => 'Klook、Tiqets 或 Trip.com',
				'Klook أو Trip.com'   => 'Klook أو Tiqets أو Trip.com',
			];
			$ids  = function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $about ) : [ $about ];
			foreach ( array_unique( array_map( 'intval', array_merge( [ $about ], array_values( $ids ) ) ) ) as $id ) {
				$content = (string) get_post_field( 'post_content', $id );
				$new     = strtr( $content, $swap );
				if ( $new !== $content ) {
					wp_update_post( wp_slash( [ 'ID' => $id, 'post_content' => $new ] ) );
				}
			}
			$done['about-partners-tiqets'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
	// 30 Oct (owner): the Affiliate Disclosure names Tiqets too, in every language (the files in data/legal match).
	if ( empty( $done['disclosure-partners-tiqets'] ) ) {
		$en = function_exists( 'er_legal_en_page' ) ? er_legal_en_page( 'affiliate-disclosure' ) : 0;
		if ( $en ) {
			$swap = [
				' and <strong>Klook</strong>' => ', <strong>Klook</strong> and <strong>Tiqets</strong>',
				' und <strong>Klook</strong>' => ', <strong>Klook</strong> und <strong>Tiqets</strong>',
				' et <strong>Klook</strong>'  => ', <strong>Klook</strong> et <strong>Tiqets</strong>',
				' e <strong>Klook</strong>'   => ', <strong>Klook</strong> e <strong>Tiqets</strong>',
				' y <strong>Klook</strong>'   => ', <strong>Klook</strong> y <strong>Tiqets</strong>',
				' и <strong>Klook</strong>'   => ', <strong>Klook</strong> и <strong>Tiqets</strong>',
				' 和 <strong>Klook</strong>'  => '、<strong>Klook</strong> 和 <strong>Tiqets</strong>',
				'و<strong>Klook</strong>'     => 'و<strong>Klook</strong> و<strong>Tiqets</strong>',
			];
			$ids = function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $en ) : [ $en ];
			foreach ( array_unique( array_map( 'intval', array_merge( [ $en ], array_values( $ids ) ) ) ) as $id ) {
				$content = (string) get_post_field( 'post_content', $id );
				if ( false !== strpos( $content, 'Tiqets' ) ) {
					continue;
				}
				$new = strtr( $content, $swap );
				if ( $new !== $content ) {
					wp_update_post( wp_slash( [ 'ID' => $id, 'post_content' => $new ] ) );
				}
			}
			$done['disclosure-partners-tiqets'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
	// 30 Oct (owner): every diving offer leaves from Hurghada, so the diving trip no longer sits under Sharm El Sheikh.
	if ( empty( $done['dive-not-sharm'] ) ) {
		$sharm = get_page_by_path( 'sharm', OBJECT, 'er_destination' );
		if ( $sharm ) {
			$sharms = function_exists( 'pll_get_post_translations' ) ? array_map( 'intval', (array) pll_get_post_translations( $sharm->ID ) ) : [];
			$sharms = array_unique( array_merge( [ (int) $sharm->ID ], $sharms ) );
			$dives  = get_posts( [
				'post_type'   => 'er_experience',
				'post_status' => 'any',
				'numberposts' => 20,
				'fields'      => 'ids',
				'lang'        => '',
				'meta_query'  => [ [ 'key' => '_er_seed_id', 'value' => array_merge( [ 'exp-dive' ], array_map( static fn ( $l ) => 'exp-dive-' . $l, [ 'ar', 'de', 'fr', 'it', 'es', 'ru', 'zh' ] ) ), 'compare' => 'IN' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
			] );
			foreach ( $dives as $dive ) {
				foreach ( $sharms as $s ) {
					delete_post_meta( (int) $dive, '_er_destination', $s );
				}
			}
			$done['dive-not-sharm'] = gmdate( 'c' );
			update_option( 'er_migrations', $done, false );
		}
	}
} );
