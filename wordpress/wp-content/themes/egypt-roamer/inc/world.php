<?php
/**
 * Proposal 4, the trip "world" (owner, 18 Oct: "apply the prototype in full"). The trip page as in
 * docs/prototypes/journey-v4/ (v4-worlds.js): a full-screen opener in the trip's own mood (torch-lit tomb wall,
 * Nile lights, sea light, desert stars), the day step by step on scroll, the practical notes as tablets, who it suits
 * and what to combine on papyrus, then "Choose your way in" with the partner options, and the questions.
 *
 * Every word comes from the page's own body (any language): the opener's lead is its first section, the steps are
 * the first card list, the tablets are its callout, the papyrus is "Who it suits" and "Combine it with". Nothing is
 * typed here except labels, which are translated strings. Booking keeps the panel's tracked links and disclosure.
 *
 * On for visitors with the er_world option (Egypt Roamer → Settings), previewed with ?ux=3.
 */

defined( 'ABSPATH' ) || exit;

function er_world_on(): bool {
	static $on = null;
	if ( null === $on ) {
		$on = (bool) get_option( 'er_world' ) || ( isset( $_GET['ux'] ) && '3' === $_GET['ux'] ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	return $on;
}

/* While previewed, keep the preview on the site's own links and out of search results. */
add_action( 'wp_head', static function (): void {
	if ( ! get_option( 'er_world' ) && er_world_on() ) {
		echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
	}
}, 1 );
add_action( 'wp_footer', static function (): void {
	if ( get_option( 'er_world' ) || ! er_world_on() ) {
		return;
	}
	?>
	<script>document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest("a[href]");if(!a||a.origin!==location.origin||/\/(go|wp-admin)\//.test(a.pathname))return;var u=new URL(a.href);if(u.searchParams.get("ux")!=="3"){u.searchParams.set("ux","3");a.href=u.toString();}},true);</script>
	<?php
} );

/** The trip's mood, by seed id (prototype WORLDS: cls and fx). */
function er_world_style( int $post_id ): array {
	$seed = preg_replace( '/-(ar|de|fr|it|es|ru|zh)$/', '', (string) get_post_meta( $post_id, '_er_seed_id', true ) );
	$map  = [
		'exp-valley'       => [ 'w-tomb', 'tomb' ],
		'exp-giza'         => [ 'w-tomb', 'tomb' ],
		'exp-abu'          => [ 'w-tomb', 'tomb' ],
		'exp-dive'         => [ 'w-sea', 'sea' ],
		'exp-ras-mohammed' => [ 'w-sea', 'sea' ],
		'exp-safari'       => [ 'w-desert', 'desert' ],
		'exp-siwa'         => [ 'w-desert', 'desert' ],
		'exp-dinner'       => [ 'w-nile', 'nile' ],
		'exp-cruise'       => [ 'w-nile', 'nile' ],
		'exp-alexandria'   => [ 'w-nile w-city', 'nile' ],
		'exp-food'         => [ 'w-nile w-city', 'nile' ],
	];
	return $map[ $seed ] ?? [ 'w-nile', 'nile' ];
}

/**
 * The body cut into the world's parts: [ 'lead' => html, 'steps' => [ [title, text_html, img_html] ], 'steps_title',
 * 'notes' => [ 'title', 'intro' => html, 'tabs' => [html] ], 'papy' => [ [label, html] ], 'faq' => [ [q, a_html] ],
 * 'rest' => [ [id, title, html] ] ]. Sections are the body's h2 headings (each with an anchor).
 */
function er_world_parts( string $html ): array {
	$parts = [ 'faq_title' => '', 'lead' => '', 'lead_title' => '', 'steps' => [], 'steps_title' => '', 'steps_id' => '', 'notes' => null, 'papy' => [], 'faq' => [], 'rest' => [] ];
	if ( '' === trim( $html ) ) {
		return $parts;
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="er-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	$root = $doc->getElementById( 'er-root' );
	if ( ! $root ) {
		return $parts;
	}
	$inner = static function ( DOMNode $n ) use ( $doc ): string {
		$s = '';
		foreach ( $n->childNodes as $c ) {
			$s .= $doc->saveHTML( $c );
		}
		return trim( $s );
	};
	// Sections: an h2 and everything up to the next h2 (comments dropped). er_body() may wrap them in <section>.
	$nodes = [];
	$walk  = static function ( DOMNode $n ) use ( &$walk, &$nodes ): void {
		foreach ( $n->childNodes as $c ) {
			if ( XML_ELEMENT_NODE === $c->nodeType && 'section' === strtolower( $c->nodeName ) ) {
				$walk( $c );
			} elseif ( XML_ELEMENT_NODE === $c->nodeType ) {
				$nodes[] = $c;
			}
		}
	};
	$walk( $root );
	$sections = [];
	$cur      = null;
	foreach ( $nodes as $n ) {
		if ( 'h2' === strtolower( $n->nodeName ) ) {
			if ( $cur ) {
				$sections[] = $cur;
			}
			$cur = [ 'id' => $n->getAttribute( 'id' ), 'title' => trim( $n->textContent ), 'nodes' => [] ];
			continue;
		}
		if ( $cur ) {
			$cur['nodes'][] = $n;
		}
	}
	if ( $cur ) {
		$sections[] = $cur;
	}
	$is_cards = static fn ( DOMNode $n ): bool => in_array( strtolower( $n->nodeName ), [ 'ul', 'ol' ], true ) && false === strpos( (string) $n->getAttribute( 'class' ), 'er-check' );
	// The day is the section that says what you do there; failing that, the first list that isn't advice or a plan.
	$step_ids  = [ 'what-you-do', 'dive-day', 'what-its-like', 'on-the-way', 'what-you-see', 'what-youll-taste', 'why', 'road-or-boat' ];
	$not_steps = [ 'who-it-suits', 'combine', 'faq', 'why-a-guide', 'what-to-bring', 'requirements', 'where', 'choosing' ];
	$step_at   = null;
	foreach ( $step_ids as $want ) {
		foreach ( $sections as $k => $s ) {
			if ( $k > 0 && $want === $s['id'] && array_filter( $s['nodes'], $is_cards ) ) {
				$step_at = $k;
				break 2;
			}
		}
	}
	if ( null === $step_at ) {
		foreach ( $sections as $k => $s ) {
			if ( $k > 0 && ! in_array( $s['id'], $not_steps, true ) && array_filter( $s['nodes'], $is_cards ) ) {
				$step_at = $k;
				break;
			}
		}
	}
	foreach ( $sections as $i => $s ) {
		$id = $s['id'];
		// FAQ
		if ( 'faq' === $id ) {
			$parts['faq_title'] = $s['title'];
			foreach ( $s['nodes'] as $n ) {
				foreach ( ( 'details' === strtolower( $n->nodeName ) ? [ $n ] : iterator_to_array( $n->getElementsByTagName( 'details' ) ) ) as $d ) {
					$q = $d->getElementsByTagName( 'summary' )->item( 0 );
					if ( ! $q ) {
						continue;
					}
					$a = '';
					foreach ( $d->childNodes as $c ) {
						if ( $c !== $q ) {
							$a .= $doc->saveHTML( $c );
						}
					}
					$parts['faq'][] = [ trim( $q->textContent ), trim( $a ) ];
				}
			}
			continue;
		}
		// The opener's lead: the first section
		if ( 0 === $i ) {
			$parts['lead_title'] = $s['title'];
			foreach ( $s['nodes'] as $n ) {
				$parts['lead'] .= $doc->saveHTML( $n );
			}
			continue;
		}
		// The day, step by step
		if ( $i === $step_at ) {
			$list = null;
			foreach ( $s['nodes'] as $n ) {
				if ( $is_cards( $n ) ) {
					$list = $n;
					break;
				}
			}
			if ( $list ) {
				$parts['steps_title'] = $s['title'];
				$parts['steps_id']    = $id;
				foreach ( $list->getElementsByTagName( 'li' ) as $li ) {
					$img = '';
					foreach ( iterator_to_array( $li->getElementsByTagName( 'span' ) ) as $sp ) {
						if ( false !== strpos( (string) $sp->getAttribute( 'class' ), 'er-points__img' ) ) {
							$im  = $sp->getElementsByTagName( 'img' )->item( 0 );
							$img = $im ? (string) ( $im->getAttribute( 'src' ) ) : '';
							$sp->parentNode->removeChild( $sp );
						}
					}
					$credit = '';
					foreach ( iterator_to_array( $li->getElementsByTagName( 'small' ) ) as $sm ) {
						if ( false !== strpos( (string) $sm->getAttribute( 'class' ), 'er-points__credit' ) ) {
							$credit = $doc->saveHTML( $sm );
							$sm->parentNode->removeChild( $sm );
						}
					}
					// A bold lead-in is the step's name; a plain item keeps its whole sentence and goes by its number.
					$strong = $li->getElementsByTagName( 'strong' )->item( 0 );
					if ( $strong && 0 !== mb_strpos( ltrim( $li->textContent ), trim( $strong->textContent ) ) ) {
						$strong = null;
					}
					$title  = $strong ? rtrim( trim( $strong->textContent ), ':：.。' ) : '';
					if ( $strong ) {
						$strong->parentNode->removeChild( $strong );
					}
					$text = preg_replace( '/^[\s:：،,–—-]+/u', '', $inner( $li ) );
					$parts['steps'][] = [ $title, $text, $img, $credit ];
				}
				// Text around the list stays, as a short intro of the journey
				$intro = '';
				foreach ( $s['nodes'] as $n ) {
					if ( $n !== $list ) {
						$intro .= $doc->saveHTML( $n );
					}
				}
				$parts['steps_intro'] = trim( $intro );
				continue;
			}
		}
		// Practical notes: the first section with a callout
		if ( ! $parts['notes'] ) {
			$callout = null;
			foreach ( $s['nodes'] as $n ) {
				if ( false !== strpos( (string) $n->getAttribute( 'class' ), 'er-callout' ) ) {
					$callout = $n;
					break;
				}
			}
			if ( $callout ) {
				$tabs = [];
				foreach ( $callout->getElementsByTagName( 'li' ) as $li ) {
					$tabs[] = $inner( $li );
				}
				if ( ! $tabs ) {
					foreach ( $callout->getElementsByTagName( 'p' ) as $p ) {
						$tabs[] = $inner( $p );
					}
				}
				$h3    = $callout->getElementsByTagName( 'h3' )->item( 0 );
				$intro = '';
				foreach ( $s['nodes'] as $n ) {
					if ( $n !== $callout ) {
						$intro .= $doc->saveHTML( $n );
					}
				}
				$parts['notes'] = [ 'eyebrow' => $s['title'], 'title' => $h3 ? trim( $h3->textContent ) : '', 'intro' => trim( $intro ), 'tabs' => $tabs, 'id' => $id ];
				continue;
			}
		}
		// Who it suits, combine it with: papyrus
		if ( in_array( $id, [ 'who-it-suits', 'combine' ], true ) ) {
			$h = '';
			foreach ( $s['nodes'] as $n ) {
				$h .= $doc->saveHTML( $n );
			}
			$parts['papy'][] = [ $s['title'], trim( $h ), $id ];
			continue;
		}
		$h = '';
		foreach ( $s['nodes'] as $n ) {
			$h .= $doc->saveHTML( $n );
		}
		$parts['rest'][] = [ $id, $s['title'], trim( $h ) ];
	}
	return $parts;
}

/** Hieroglyph wall for the tomb opener (prototype wallHTML(), same seed so every page draws the same wall). */
function er_world_wall( int $cols ): string {
	$signs = preg_split( '//u', '𓄿𓇋𓂝𓅱𓃀𓊪𓆑𓅓𓈖𓂋𓉔𓎛𓐍𓄡𓊃𓋴𓈙𓈎𓎡𓎼𓏏𓍿𓂧𓆓𓋹𓂀𓇳𓆣𓁹𓃭𓊹𓋞𓇯𓊖𓉐𓆄𓅃𓀭𓁐𓂻', -1, PREG_SPLIT_NO_EMPTY );
	$seed  = 11;
	$h     = '';
	for ( $c = 0; $c < $cols; $c++ ) {
		$s = '';
		for ( $i = 0; $i < 26; $i++ ) {
			$seed = ( $seed * 16807 ) % 2147483647;
			$s   .= $signs[ (int) floor( $seed / 2147483647 * count( $signs ) ) ];
		}
		$h .= '<span class="col">' . $s . '</span>';
	}
	return '<div class="wall" aria-hidden="true">' . $h . '</div>';
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( ! er_world_on() || ! is_singular( 'er_experience' ) ) {
		return;
	}
	wp_enqueue_style( 'er-world', ER_THEME_URI . '/assets/css/world.css', [ 'er-pages' ], er_asset_ver( 'assets/css/world.css' ) );
	wp_enqueue_script( 'er-world', ER_THEME_URI . '/assets/js/world.js', [], er_asset_ver( 'assets/js/world.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
}, 20 );

add_filter( 'body_class', static function ( array $c ): array {
	if ( er_world_on() && is_singular( 'er_experience' ) ) {
		$c[] = 'er-world';
	}
	return $c;
} );

/*
 * Proposal 4, the homepage story (prototype renderHome): the hero with the story's index, then the four chapters
 * (the approved scenes' own words and photographs from Appearance → Homepage), each ending in the trips that belong
 * to it, so a visitor can book from the chapter they are reading. The sections after the scenes stay as they are.
 */

/** The trips of each chapter, by seed (prototype CHAPTERS; the Nile cruise joined the Nile on 17 Oct). */
function er_world_chapter_trips( int $n ): array {
	$seeds = [
		1 => [ 'exp-giza', 'exp-dinner', 'exp-food' ],
		2 => [ 'exp-valley', 'exp-cruise', 'exp-abu' ],
		3 => [ 'exp-safari', 'exp-siwa' ],
		4 => [ 'exp-dive', 'exp-ras-mohammed' ],
	][ $n ] ?? [];
	$ids = [];
	foreach ( $seeds as $seed ) {
		$found = get_posts( [
			'post_type'        => 'er_experience',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
			'meta_query'       => [ [ 'key' => '_er_seed_id', 'value' => array_merge( [ $seed ], array_map( static fn ( $l ) => $seed . '-' . $l, [ 'ar', 'de', 'fr', 'it', 'es', 'ru', 'zh' ] ) ), 'compare' => 'IN' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
		] );
		if ( $found ) {
			$ids[] = (int) $found[0];
		}
	}
	return $ids;
}

/** A trip as a small card: photo, name, length, how many options. Opens the trip's page. */
function er_world_mini( int $id ): string {
	$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $id, 10 ) : [];
	$len    = function_exists( 'er_ux2_offer_length' ) ? er_ux2_offer_length( $offers ) : '';
	$len    = '' !== $len ? $len : (string) get_post_meta( $id, '_er_duration', true );
	$n      = count( $offers );
	$opts   = $n ? ( 1 === $n ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => $n ] ) ) : '';
	$thumb  = function_exists( 'er_ux2_thumb_url' ) ? er_ux2_thumb_url( $id ) : '';
	return '<a class="mini" href="' . esc_url( get_permalink( $id ) ) . '">'
		. ( '' !== $thumb ? '<img src="' . esc_url( $thumb ) . '" alt="" loading="lazy" width="76" height="76" />' : '<span class="mini__ph" aria-hidden="true"></span>' )
		. '<span class="mini__b"><b>' . esc_html( get_the_title( $id ) ) . '</b><span>' . esc_html( $len ) . ( '' !== $len && '' !== $opts ? ' · ' : '' ) . ( '' !== $opts ? '<em>' . esc_html( $opts ) . '</em>' : '' ) . '</span></span></a>';
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( er_world_on() && is_front_page() ) {
		wp_enqueue_style( 'er-home-story', ER_THEME_URI . '/assets/css/home-story.css', [ 'er-pages' ], er_asset_ver( 'assets/css/home-story.css' ) );
	}
}, 20 );

/** The chapter's place, when the chapter is one place (Pyramids: Cairo; Desert: Siwa), for "See all trips in …". */
function er_world_chapter_place( int $n ): int {
	$seed = [ 1 => 'dest-cairo', 3 => 'dest-siwa' ][ $n ] ?? '';
	if ( '' === $seed ) {
		return 0;
	}
	$found = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'suppress_filters' => false, 'meta_query' => [ [ 'key' => '_er_seed_id', 'value' => array_merge( [ $seed ], array_map( static fn ( $l ) => $seed . '-' . $l, [ 'ar', 'de', 'fr', 'it', 'es', 'ru', 'zh' ] ) ), 'compare' => 'IN' ] ] ] ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $found ? (int) $found[0] : 0;
}

/*
 * Proposal 4, Trips & tours by place (prototype renderTrips): each place opens with its banner (photo, name, the
 * place's own tagline and best time, a link to its guide), then its trips. A trip belongs to its page's place; the
 * desert safari has none on its page and sits with Siwa, as in the prototype (both are Western Desert trips).
 */
function er_world_trip_groups( array $ids ): void {
	$groups = [];
	foreach ( $ids as $id ) {
		$dest = (int) get_post_meta( (int) $id, '_er_destination', true );
		if ( ! $dest && 'exp-safari' === preg_replace( '/-(ar|de|fr|it|es|ru|zh)$/', '', (string) get_post_meta( (int) $id, '_er_seed_id', true ) ) ) {
			$dest = er_world_chapter_place( 3 );
		}
		$dest = $dest && function_exists( 'er_translated_post_id' ) ? (int) er_translated_post_id( $dest ) : $dest;
		$groups[ $dest ][] = (int) $id;
	}
	$order = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 30, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	$keys  = array_merge( array_values( array_intersect( array_map( 'intval', $order ), array_keys( $groups ) ) ), array_diff( array_keys( $groups ), array_map( 'intval', $order ) ) );
	echo '<div class="tgroups">';
	foreach ( $keys as $dest ) {
		echo '<section class="tgroup" aria-labelledby="tg-' . (int) $dest . '">';
		if ( $dest && 'publish' === get_post_status( $dest ) ) {
			$photo = (int) get_post_thumbnail_id( $dest );
			$stock = er_stock_id_for( $dest );
			$img   = $photo ? er_img( $photo, 'er-hero', [ 'alt' => '', 'sizes' => '(max-width: 1240px) 100vw, 1200px', 'loading' => 'lazy' ] ) : ( '' !== $stock ? er_stock_img( $stock, '', [ 'loading' => 'lazy', 'sizes' => '(max-width: 1240px) 100vw, 1200px' ], [ 800, 1200, 1800 ] ) : '' );
			$tag   = (string) get_post_meta( $dest, '_er_tagline', true );
			$best  = (string) get_post_meta( $dest, '_er_best_time', true );
			echo '<div class="tbanner on-dark">' . $img . '<div class="tbanner__t"><h2 id="tg-' . (int) $dest . '">' . esc_html( get_the_title( $dest ) ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput
			if ( '' !== $tag || '' !== $best ) {
				echo '<p>' . esc_html( $tag ) . ( '' !== $best ? ' <small>· ' . esc_html( er_t( 'Best time' ) . ' ' . $best ) . '</small>' : '' ) . '</p>';
			}
			echo '</div><a class="tbanner__go" href="' . esc_url( get_permalink( $dest ) ) . '">' . esc_html( er_t( 'Read the {place} guide', [ 'place' => get_the_title( $dest ) ] ) ) . ' <span aria-hidden="true">→</span></a></div>';
		} else {
			echo '<h2 class="t-h3 tgroup__h" id="tg-0">' . esc_html( er_t( 'More trips' ) ) . '</h2>';
		}
		er_card_grid( $groups[ $dest ], 'archive-experience', 'h3' );
		echo '</section>';
	}
	echo '</div>';
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( er_world_on() && function_exists( 'er_ux2_trips_page' ) && er_ux2_trips_page() ) {
		wp_enqueue_style( 'er-trips-story', ER_THEME_URI . '/assets/css/trips-story.css', [ 'er-pages' ], er_asset_ver( 'assets/css/trips-story.css' ) );
	}
}, 20 );

/** The trips whose page belongs to this place (any language's copy of it), in the current language. */
function er_world_place_trips( int $dest ): array {
	$group = function_exists( 'er_translation_group' ) ? er_translation_group( $dest ) : [ $dest ];
	$ids   = get_posts( [ 'post_type' => 'er_experience', 'post_status' => 'publish', 'numberposts' => 20, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false, 'meta_query' => [ [ 'key' => '_er_destination', 'value' => $group, 'compare' => 'IN', 'type' => 'NUMERIC' ] ] ] ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( er_world_chapter_place( 3 ) === $dest ) {
		$ids = array_merge( er_world_chapter_trips( 3 ), $ids ); // the desert safari has no place on its page (see er_world_trip_groups)
	}
	return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/*
 * Proposal 4, the Places page (prototype showPlaces): each place as a card that already plans: its photo and region,
 * its own tagline and best time, the trips you can book there, then "See all trips" and the place's guide.
 */
function er_world_place_cards( array $ids ): void {
	echo '<div class="pcards">';
	foreach ( $ids as $dest ) {
		$dest   = (int) $dest;
		$photo  = (int) get_post_thumbnail_id( $dest );
		$stock  = er_stock_id_for( $dest );
		$sizes  = '(max-width: 600px) 100vw, (max-width: 1000px) 50vw, 400px';
		$img    = $photo ? er_img( $photo, 'large', [ 'alt' => '', 'sizes' => $sizes, 'loading' => 'lazy' ] ) : ( '' !== $stock ? er_stock_img( $stock, '', [ 'loading' => 'lazy', 'sizes' => $sizes ], [ 500, 800, 1100 ] ) : '' );
		$name   = get_the_title( $dest );
		$url    = get_permalink( $dest );
		$trips  = er_world_place_trips( $dest );
		$n      = count( $trips );
		$region = (string) get_post_meta( $dest, '_er_region_label', true );
		$tag    = (string) get_post_meta( $dest, '_er_tagline', true );
		$best   = (string) get_post_meta( $dest, '_er_best_time', true );
		echo '<article class="pcard"><a class="pcard__img" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . $img; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '' !== $region ? '<span class="pcard__region">' . esc_html( $region ) . '</span>' : '';
		echo '</a><div class="pcard__b"><h2><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></h2>';
		echo '' !== $tag ? '<p class="pcard__tag">' . esc_html( $tag ) . '</p>' : '';
		echo '' !== $best ? '<p class="pcard__best">' . esc_html( er_t( 'Best time' ) . ' ' . $best ) . '</p>' : '';
		if ( $trips ) {
			echo '<div class="pcard__trips">';
			foreach ( $trips as $t ) {
				echo er_world_mini( $t ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</div>';
		}
		echo '<div class="pcard__acts">';
		if ( $n ) {
			$go = 1 === $n ? get_permalink( $trips[0] ) : er_ux2_trip_url( [ 'destination' => $dest, 'len' => '', 'private' => false, 'pickup' => false ] );
			echo '<a class="btn btn--primary btn--sm" href="' . esc_url( $go ) . '">' . esc_html( 1 === $n ? er_t( 'See the trip' ) : er_t( 'See all {n} trips', [ 'n' => (string) $n ] ) ) . ' <span aria-hidden="true">→</span></a>';
		}
		echo '<a class="pcard__about" href="' . esc_url( $url ) . '">' . esc_html( er_t( 'About {place}', [ 'place' => $name ] ) ) . '</a></div></div></article>';
	}
	echo '</div>';
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( er_world_on() && is_post_type_archive( 'er_destination' ) ) {
		wp_enqueue_style( 'er-places-story', ER_THEME_URI . '/assets/css/trips-story.css', [ 'er-pages' ], er_asset_ver( 'assets/css/trips-story.css' ) );
	}
}, 20 );
