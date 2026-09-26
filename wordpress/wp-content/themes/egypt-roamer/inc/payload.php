<?php
/**
 * window.ER_DATA — CMS content in the shapes the approved components expect
 * (see src/js/data.js). Only published content, only live offers, and prices
 * only when an editor recorded when they were checked.
 */

defined( 'ABSPATH' ) || exit;

/** Image payload for scripts: Media Library first, else the approved stock photo id. */
function er_payload_image( int $attachment_id, string $stock = '' ) {
	$img = $attachment_id && function_exists( 'er_image_payload' ) ? er_image_payload( $attachment_id ) : null;
	return $img ?: $stock;
}

/** Seeded posts remember their prototype photo; used until a featured image is set. */
function er_stock_id_for( int $post_id ): string {
	static $map = null;
	if ( null === $map ) {
		$map  = [];
		$file = defined( 'ER_CORE_DIR' ) ? ER_CORE_DIR . 'data/seed.json' : '';
		$seed = $file && is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
		foreach ( (array) ( $seed['destinations'] ?? [] ) as $d ) {
			$map[ 'dest-' . $d['id'] ] = $d['image'];
		}
		foreach ( [ 'experiences', 'guides' ] as $k ) {
			foreach ( (array) ( $seed[ $k ] ?? [] ) as $x ) {
				$map[ $x['id'] ] = $x['image'];
			}
		}
		foreach ( (array) ( $seed['partnerCategories'] ?? [] ) as $cat ) {
			foreach ( $cat['items'] as $k => $o ) {
				$map[ $cat['id'] . '-' . $k ] = $o['image'];
			}
		}
	}
	$seed_id = (string) get_post_meta( $post_id, '_er_seed_id', true );
	$seed_id = preg_replace( '/-(de|fr|it|es|ru|zh|ar)$/', '', $seed_id );
	return $map[ $seed_id ] ?? '';
}

function er_post_image_payload( int $post_id ) {
	return er_payload_image( (int) get_post_thumbnail_id( $post_id ), er_stock_id_for( $post_id ) );
}

/** Published destinations in display order (featured selection first). */
function er_home_destination_ids(): array {
	$ids = array_map( 'er_translated_post_id', array_map( 'intval', (array) er_home( 'dest_featured' ) ) );
	$ids = array_values( array_filter( $ids, static fn ( $id ) => 'publish' === get_post_status( $id ) ) );
	if ( ! $ids ) {
		$ids = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 12, 'fields' => 'ids', 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ], 'suppress_filters' => false ] );
	}
	return array_map( 'intval', $ids );
}

function er_payload_destination( int $id ): array {
	$lat = get_post_meta( $id, '_er_lat', true );
	$lng = get_post_meta( $id, '_er_lng', true );
	return [
		'id'         => get_post_field( 'post_name', $id ),
		'name'       => get_the_title( $id ),
		'region'     => (string) get_post_meta( $id, '_er_region_label', true ),
		'tagline'    => (string) get_post_meta( $id, '_er_tagline', true ),
		'desc'       => wp_strip_all_tags( get_the_excerpt( $id ) ),
		'highlights' => er_lines( $id, '_er_highlights' ),
		'best'       => (string) get_post_meta( $id, '_er_best_time', true ),
		'reach'      => (string) get_post_meta( $id, '_er_getting_there', true ),
		'mapReach'   => (string) ( get_post_meta( $id, '_er_map_reach', true ) ?: get_post_meta( $id, '_er_getting_there', true ) ),
		'image'      => er_post_image_payload( $id ),
		'coords'     => is_numeric( $lat ) && is_numeric( $lng ) ? [ (float) $lng, (float) $lat ] : null,
		'url'        => get_permalink( $id ),
	];
}

/** Offer → the card/row shape used by partners, moods and the experiences rail. */
function er_payload_offer( int $offer_id, string $placement ): array {
	$o = er_offer_data( $offer_id, $placement, (int) get_queried_object_id() );
	return [
		'id'        => 'offer-' . $offer_id,
		'name'      => $o['title'],
		'title'     => $o['title'],
		'location'  => $o['location'],
		'meta'      => $o['meta'],
		'badge'     => $o['badge'],
		'partner'   => $o['provider'],
		'cta'       => $o['cta'],
		'href'      => $o['go'],
		'priceText' => $o['price_text'],
		'unitText'  => $o['unit_text'],
		'image'     => er_payload_image( $o['image_id'] ?: er_offer_fallback_image( $offer_id ), er_stock_id_for( $offer_id ) ),
		'track'     => [
			'offer'     => $o['slug'],
			'provider'  => $o['provider_id'] ? (string) get_post_field( 'post_name', $o['provider_id'] ) : '',
			'placement' => $placement,
			'cta'       => $o['cta'],
			'intent'    => er_offer_intent( $offer_id ),
		],
	];
}

/** An offer without its own image uses the image of the tour/experience/activity/destination it belongs to. */
function er_offer_fallback_image( int $offer_id ): int {
	foreach ( [ '_er_tour', '_er_experience', '_er_activity', '_er_destination' ] as $key ) {
		$id = (int) get_post_meta( $offer_id, $key, true );
		if ( $id && get_post_thumbnail_id( $id ) ) {
			return (int) get_post_thumbnail_id( $id );
		}
	}
	return 0;
}

/** Tours/experiences/activities for the rail: editorial page + live offer CTA when there is one. */
function er_payload_experience( int $id, int $index ): array {
	$dest   = er_get_related( $id, '_er_destination' );
	$offers = er_offers_for_post( $id, 1 );
	$item   = [
		'id'       => get_post_field( 'post_name', $id ),
		'title'    => get_the_title( $id ),
		'location' => (string) get_post_meta( $id, '_er_location', true ),
		'tag'      => $dest ? $dest[0]->post_name : 'other',
		'duration' => (string) get_post_meta( $id, '_er_duration', true ),
		'badge'    => (string) get_post_meta( $id, '_er_badge', true ),
		'image'    => er_post_image_payload( $id ),
		'url'      => get_permalink( $id ),
		'cta'      => er_t( 'Discover' ),
	];
	if ( $offers ) {
		$o                 = er_payload_offer( $offers[0], 'home-experiences' );
		$item['href']      = $o['href'];
		$item['cta']       = $o['cta'];
		$item['partner']   = $o['partner'];
		$item['priceText'] = $o['priceText'];
		$item['track']     = $o['track'];
	}
	return $item;
}

function er_home_experience_ids(): array {
	$ids = array_map( 'er_translated_post_id', array_map( 'intval', (array) er_home( 'exp_featured' ) ) );
	$ids = array_values( array_filter( $ids, static fn ( $id ) => 'publish' === get_post_status( $id ) ) );
	if ( ! $ids ) {
		$ids = get_posts( [ 'post_type' => [ 'er_experience', 'er_tour', 'er_activity' ], 'post_status' => 'publish', 'numberposts' => 8, 'fields' => 'ids', 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ], 'suppress_filters' => false ] );
	}
	return array_map( 'intval', $ids );
}

function er_home_guide_ids(): array {
	$ids = array_map( 'er_translated_post_id', array_map( 'intval', (array) er_home( 'guide_featured' ) ) );
	$ids = array_values( array_filter( $ids, static fn ( $id ) => 'publish' === get_post_status( $id ) ) );
	if ( ! $ids ) {
		$ids = get_posts( [ 'post_type' => [ 'er_guide', 'post' ], 'post_status' => 'publish', 'numberposts' => 7, 'fields' => 'ids', 'suppress_filters' => false ] );
	}
	return array_map( 'intval', $ids );
}

function er_payload_guide( int $id ): array {
	$topics = get_post_type( $id ) === 'er_guide' ? get_the_terms( $id, 'er_guide_topic' ) : get_the_category( $id );
	return [
		'id'      => 'g-' . $id,
		'href'    => get_permalink( $id ),
		'title'   => get_the_title( $id ),
		'cat'     => $topics && ! is_wp_error( $topics ) ? $topics[0]->name : er_t( 'Travel Guide' ),
		'read'    => er_read_minutes( $id ),
		'excerpt' => has_excerpt( $id ) ? wp_strip_all_tags( get_the_excerpt( $id ) ) : '',
		'image'   => er_post_image_payload( $id ),
	];
}

/** Travel styles → moods, each with its destination and live offers (experience + stay). */
function er_payload_moods(): array {
	$terms = get_terms( [ 'taxonomy' => 'er_travel_style', 'hide_empty' => false, 'meta_key' => '_er_order', 'orderby' => 'meta_value_num', 'order' => 'ASC' ] );
	if ( is_wp_error( $terms ) || ! $terms ) {
		$terms = get_terms( [ 'taxonomy' => 'er_travel_style', 'hide_empty' => false ] );
	}
	$moods = [];
	foreach ( (array) $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$dest_id = (int) get_term_meta( $term->term_id, '_er_destination', true );
		$dest_id = $dest_id ? er_translated_post_id( $dest_id ) : 0;
		$exp     = er_get_offers( [ 'style' => $term->slug, 'limit' => 5 ] );
		$stay    = er_get_offers( [ 'style' => $term->slug, 'type' => 'hotels', 'limit' => 1 ] );
		$exp     = array_values( array_diff( $exp, $stay ) );
		$image   = (int) get_term_meta( $term->term_id, '_er_image', true );
		$moods[] = [
			'id'    => $term->slug,
			'label' => $term->name,
			'word'  => (string) ( get_term_meta( $term->term_id, '_er_word', true ) ?: $term->name ),
			'icon'  => (string) ( get_term_meta( $term->term_id, '_er_icon', true ) ?: 'i-compass' ),
			'tint'  => 'var(--' . ( get_term_meta( $term->term_id, '_er_tint', true ) ?: 'clay' ) . ')',
			'image' => er_payload_image( $image, er_mood_stock( $term->slug ) ),
			'desc'  => wp_strip_all_tags( term_description( $term ) ),
			'recs'  => [
				'dest' => $dest_id && 'publish' === get_post_status( $dest_id ) ? get_post_field( 'post_name', $dest_id ) : '',
				'exp'  => $exp ? er_payload_offer( $exp[0], 'home-mood' ) : null,
				'stay' => $stay ? er_payload_offer( $stay[0], 'home-mood-stay' ) : null,
			],
		];
	}
	return $moods;
}

function er_mood_stock( string $slug ): string {
	$file = defined( 'ER_CORE_DIR' ) ? ER_CORE_DIR . 'data/seed.json' : '';
	$seed = $file && is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
	foreach ( (array) ( $seed['moods'] ?? [] ) as $m ) {
		if ( $m['id'] === $slug ) {
			return $m['image'];
		}
	}
	return '';
}

/** Offer categories with live offers → partner tabs. */
function er_payload_partner_categories(): array {
	$out   = [];
	$stock = er_home_stock()['partners'];
	foreach ( (array) get_terms( [ 'taxonomy' => 'er_offer_type', 'hide_empty' => false ] ) as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$offers = er_get_offers( [ 'type' => $term->slug, 'limit' => 3 ] );
		if ( ! $offers ) {
			continue; // no live offers → no tab (never an empty shop window)
		}
		$compare = (int) get_term_meta( $term->term_id, '_er_compare_offer', true );
		$cat     = [
			'id'       => $term->slug,
			'label'    => $term->name,
			'icon'     => (string) ( get_term_meta( $term->term_id, '_er_icon', true ) ?: 'i-compass' ),
			'headline' => (string) get_term_meta( $term->term_id, '_er_headline', true ),
			'copy'     => (string) ( get_term_meta( $term->term_id, '_er_copy', true ) ?: wp_strip_all_tags( term_description( $term ) ) ),
			'image'    => er_payload_image( (int) get_term_meta( $term->term_id, '_er_image', true ), $stock[ $term->slug ] ?? '' ),
			'trust'    => array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_term_meta( $term->term_id, '_er_trust', true ) ) ), 'strlen' ) ),
			'compare'  => (string) ( get_term_meta( $term->term_id, '_er_compare_label', true ) ?: er_t( 'Compare Options' ) ),
			'allHref'  => '',
			'items'    => array_map( static fn ( $id ) => er_payload_offer( $id, 'home-partners-' . $term->slug ), $offers ),
		];
		if ( $compare && er_offer_is_live( $compare ) ) {
			$c              = er_payload_offer( $compare, 'home-partners-compare' );
			$cat['href']    = $c['href'];
			$cat['track']   = $c['track'];
		}
		$out[] = $cat;
	}
	return $out;
}

/** Budget bands for the trip builder, only when the owner set all three. */
function er_payload_style_rates() {
	$rates = [];
	foreach ( [ 'smart', 'comfort', 'luxury' ] as $style ) {
		$band = (string) er_home( 'budget_' . $style );
		if ( ! preg_match( '/^(\d+)-(\d+)$/', $band, $m ) ) {
			return null;
		}
		$rates[ $style ] = [ (int) $m[1], (int) $m[2] ];
	}
	return $rates;
}

/** Travel-time legs between destinations (editorial logistics from the approved design). */
function er_payload_legs(): array {
	$file = defined( 'ER_CORE_DIR' ) ? ER_CORE_DIR . 'data/seed.json' : '';
	$seed = $file && is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
	$legs = (array) ( $seed['legs'] ?? [] );
	$lang = er_lang();
	return array_merge( $legs, (array) ( $seed['translations'][ $lang ]['legs'] ?? [] ) );
}

/** Build the payload. $context = 'home' (everything) or 'site' (search index only). */
function er_payload( string $context ): array {
	if ( ! function_exists( 'er_get_offers' ) ) {
		return [ 'lang' => er_lang(), 'homeUrl' => home_url( '/' ) ];
	}
	$dest_ids = er_home_destination_ids();
	$data     = [
		'lang'         => er_lang(),
		'homeUrl'      => home_url( '/' ),
		'guidesUrl'    => (string) get_post_type_archive_link( 'er_guide' ),
		'destinations' => array_values( array_map( 'er_payload_destination', $dest_ids ) ),
		'experiences'  => array_map( 'er_payload_experience', er_home_experience_ids(), array_keys( er_home_experience_ids() ) ),
		'guides'       => array_map( 'er_payload_guide', er_home_guide_ids() ),
	];
	if ( 'home' !== $context ) {
		return $data;
	}
	$filters = [ [ 'id' => 'all', 'label' => er_t( 'All' ) ] ];
	$seen    = [];
	foreach ( $data['experiences'] as $x ) {
		if ( 'other' !== $x['tag'] && ! isset( $seen[ $x['tag'] ] ) ) {
			$seen[ $x['tag'] ] = true;
			$dest              = get_page_by_path( $x['tag'], OBJECT, 'er_destination' );
			$filters[]         = [ 'id' => $x['tag'], 'label' => $dest ? get_the_title( er_translated_post_id( $dest->ID ) ) : $x['tag'] ];
		}
	}
	$night_weights = [];
	foreach ( $dest_ids as $id ) {
		$night_weights[ get_post_field( 'post_name', $id ) ] = (float) ( get_post_meta( $id, '_er_nights', true ) ?: 2 );
	}
	$film_captions = [ 'Where history still breathes.', 'A river that wrote a civilization.', 'Columns taller than time.', 'Silence, as far as you can see.', 'Then, a different world.' ];
	$film_kickers  = [ 'Chapter one', 'Chapter two', 'Chapter three', 'Chapter four', 'Chapter five' ];
	$film          = [];
	foreach ( er_home_stock()['film'] as $i => $photo ) {
		$film[] = [ 'image' => $photo, 'kicker' => er_t( $film_kickers[ $i ] ), 'caption' => er_t( $film_captions[ $i ] ) ];
	}
	$data['moods']             = (bool) er_home( 'moods_enabled' ) ? er_payload_moods() : [];
	$data['experienceFilters'] = count( $filters ) > 2 ? $filters : [];
	$data['partnerCategories'] = er_payload_partner_categories();
	$data['routeOrder']        = array_map( static fn ( $id ) => get_post_field( 'post_name', $id ), $dest_ids );
	$data['nightWeights']      = $night_weights;
	$data['legs']              = er_payload_legs();
	$data['styleRates']        = er_payload_style_rates();
	$data['film']              = $film;
	// Map & planner need coordinates; destinations without them stay out of the map only.
	$data['destinations'] = array_values( array_filter( $data['destinations'], static fn ( $d ) => null !== $d['coords'] ) ) ?: $data['destinations'];
	return $data;
}
