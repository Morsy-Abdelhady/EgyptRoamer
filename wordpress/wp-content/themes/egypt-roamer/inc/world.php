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
		'exp-balloon'      => [ 'w-desert', 'desert' ],
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
	wp_enqueue_style( 'er-p4-world', ER_THEME_URI . '/assets/css/p4-world.css', [ 'er-world' ], er_asset_ver( 'assets/css/p4-world.css' ) );
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

/** A trip as a small card: photo, name, length, how many options, and its heart (prototype mini + lcf). */
function er_world_mini( int $id, bool $heart = true ): string {
	$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $id, 10 ) : [];
	$len    = er_trip_length( $id, $offers );
	$n      = count( $offers );
	$opts   = $n ? ( 1 === $n ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => $n ] ) ) : '';
	$thumb  = function_exists( 'er_ux2_thumb_url' ) ? er_ux2_thumb_url( $id ) : '';
	return '<div class="lcfw"><a class="mini" href="' . esc_url( get_permalink( $id ) ) . '">'
		. ( '' !== $thumb ? '<img src="' . esc_url( $thumb ) . '" alt="" loading="lazy" width="76" height="76" />' : '<span class="mini__ph" aria-hidden="true"></span>' )
		. '<span class="mini__b"><b>' . esc_html( get_the_title( $id ) ) . '</b><span>' . esc_html( $len ) . ( '' !== $len && '' !== $opts ? ' · ' : '' ) . ( '' !== $opts ? '<em>' . esc_html( $opts ) . '</em>' : '' ) . '</span></span></a>'
		. ( $heart ? er_save_button( er_trip_save_data( $id, $len ) ) : '' ) . '</div>';
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
		// A trip in two places (the Nile cruise: Aswan and Luxor; Red Sea diving: Hurghada and Sharm) is shown under
		// each, as in the prototype.
		$dests = array_values( array_unique( array_filter( array_map( 'intval', (array) get_post_meta( (int) $id, '_er_destination', false ) ) ) ) );
		// A translated trip whose own copy lost its place (Arabic Nile cruise, Alexandria and Ras Mohammed, 20 Oct)
		// takes the place of another language's copy, so it is not left under "More trips".
		if ( ! $dests && function_exists( 'er_translation_group' ) ) {
			foreach ( er_translation_group( (int) $id ) as $er_copy ) {
				$dests = array_values( array_unique( array_filter( array_map( 'intval', (array) get_post_meta( (int) $er_copy, '_er_destination', false ) ) ) ) );
				if ( $dests ) {
					break;
				}
			}
		}
		if ( ! $dests && 'exp-safari' === preg_replace( '/-(ar|de|fr|it|es|ru|zh)$/', '', (string) get_post_meta( (int) $id, '_er_seed_id', true ) ) ) {
			$dests = [ (int) er_world_chapter_place( 3 ) ];
		}
		foreach ( $dests ?: [ 0 ] as $dest ) {
			$dest = $dest && function_exists( 'er_translated_post_id' ) ? (int) er_translated_post_id( $dest ) : $dest;
			$groups[ $dest ][] = (int) $id;
		}
	}
	// As the prototype: in each place, a trip that spans two places comes first (the cruise in Luxor and Aswan, the
	// diving trip in Sharm El Sheikh).
	foreach ( $groups as $er_k => $er_list ) {
		$er_multi = array_filter( $er_list, static fn ( $x ) => count( array_unique( (array) get_post_meta( (int) $x, '_er_destination', false ) ) ) > 1 );
		$groups[ $er_k ] = array_values( array_merge( $er_multi, array_diff( $er_list, $er_multi ) ) );
	}
	$order = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 30, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	$keys  = array_merge( array_values( array_intersect( array_map( 'intval', $order ), array_keys( $groups ) ) ), array_diff( array_keys( $groups ), array_map( 'intval', $order ) ) );
	$f     = er_ux2_trip_filters();
	$shown = er_world_trips_shown( $ids );
	$fdest = $f['destination'] && function_exists( 'er_translated_post_id' ) ? (int) er_translated_post_id( $f['destination'] ) : $f['destination'];
	echo '<div class="tgroups" data-trip-groups>';
	foreach ( $keys as $dest ) {
		$er_vis = array_intersect( $groups[ $dest ], $shown ) && ( ! $fdest || $fdest === (int) $dest );
		echo '<section class="tgroup" aria-labelledby="tg-' . (int) $dest . '" data-dest="' . (int) $dest . '"' . ( $er_vis ? '' : ' hidden' ) . '>';
		if ( $dest && 'publish' === get_post_status( $dest ) ) {
			$photo = (int) get_post_thumbnail_id( $dest );
			$stock = er_stock_id_for( $dest );
			$img   = $photo ? er_img( $photo, 'er-hero', [ 'alt' => '', 'sizes' => '(max-width: 1240px) 100vw, 1200px', 'loading' => 'lazy' ] ) : ( '' !== $stock ? er_stock_img( $stock, '', [ 'loading' => 'lazy', 'sizes' => '(max-width: 1240px) 100vw, 1200px' ], [ 800, 1200, 1800 ] ) : '' );
			$tag   = (string) get_post_meta( $dest, '_er_tagline', true );
			$best  = (string) get_post_meta( $dest, '_er_best_time', true );
			$vid = er_world_video_for( 'place', er_world_seed( $dest ) );
			echo '<div class="tbanner on-dark">' . $img . ( $vid ? er_world_video( $vid, 'wvid tbanner__vid' ) : '' ) . '<div class="tbanner__t"><h2 id="tg-' . (int) $dest . '">' . esc_html( get_the_title( $dest ) ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput
			if ( '' !== $tag || '' !== $best ) {
				echo '<p>' . esc_html( $tag ) . ( '' !== $best ? ' <small>· ' . esc_html( er_t( 'Best time' ) . ' ' . $best ) . '</small>' : '' ) . '</p>';
			}
			echo '</div><a class="tbanner__go" href="' . esc_url( get_permalink( $dest ) ) . '">' . esc_html( er_t( 'Read the {place} guide', [ 'place' => get_the_title( $dest ) ] ) ) . ' <span aria-hidden="true">→</span></a></div>';
		} else {
			echo '<h2 class="t-h3 tgroup__h" id="tg-0">' . esc_html( er_t( 'More trips' ) ) . '</h2>';
		}
		$er_cards = '';
		foreach ( $groups[ $dest ] as $er_id ) {
			$t     = er_ux2_trip_traits( (int) $er_id );
			$attrs = ' data-len="' . esc_attr( implode( ' ', $t['len'] ) ) . '" data-private="' . ( $t['private'] ? '1' : '0' ) . '" data-pickup="' . ( $t['pickup'] ? '1' : '0' ) . '" data-trip="' . (int) $er_id . '"' . ( in_array( (int) $er_id, $shown, true ) ? '' : ' hidden' );
			$er_cards .= (string) preg_replace( '#^(\s*<li class="tcard\b[^"]*")#', '$1' . $attrs, er_card( (int) $er_id, 'archive-experience', 'h3' ), 1 );
		}
		echo '<ul class="grid-cards">' . $er_cards . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside er_card
		echo '</section>';
	}
	echo '</div>';
	echo '<div data-trip-empty' . ( $shown ? ' hidden' : '' ) . '>';
	er_ux2_trip_empty();
	echo '</div>';
}

/** The trips the URL's filters keep (place, length, private, pickup); the rest stay on the page, hidden. */
function er_world_trips_shown( array $ids ): array {
	$f   = er_ux2_trip_filters();
	$ids = er_ux2_filter_trips( array_map( 'intval', $ids ) );
	if ( ! $f['destination'] || ! function_exists( 'er_translation_group' ) ) {
		return $ids;
	}
	$group = array_map( 'intval', er_translation_group( $f['destination'] ) );
	return array_values( array_filter( $ids, static function ( $id ) use ( $group ): bool {
		$d = array_map( 'intval', (array) get_post_meta( $id, '_er_destination', false ) );
		foreach ( $d ? [] : er_translation_group( $id ) as $copy ) {
			$d = $d ?: array_map( 'intval', (array) get_post_meta( (int) $copy, '_er_destination', false ) );
		}
		return (bool) array_intersect( $d, $group ) || (bool) array_intersect( array_map( static fn ( $x ) => function_exists( 'er_translated_post_id' ) ? (int) er_translated_post_id( $x ) : $x, $d ), $group );
	} ) );
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
		echo '</a>' . er_save_button( [ 'id' => 'place:' . $dest, 'title' => $name, 'image' => $photo ? (string) wp_get_attachment_image_url( $photo, 'medium' ) : $stock, 'meta' => implode( ' · ', array_filter( [ $region, '' !== $best ? er_t( 'Best time' ) . ' ' . $best : '' ] ) ), 'url' => $url ], 'lcf pcard__save' ) . '<div class="pcard__b"><h2><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></h2>';
		echo '' !== $tag ? '<p class="pcard__tag">' . esc_html( $tag ) . '</p>' : '';
		echo '' !== $best ? '<p class="pcard__best">' . esc_html( er_t( 'Best time' ) . ' ' . $best ) . '</p>' : '';
		if ( $trips ) {
			echo '<div class="pcard__trips">';
			foreach ( $trips as $t ) {
				echo er_world_mini( $t, false ); // phpcs:ignore WordPress.Security.EscapeOutput -- the prototype's place rows have no heart
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

/*
 * Proposal 4's videos (owner, 18 Oct: "apply everything"). Free Pexels videos whose page names the place, chosen and
 * compressed for the prototype (docs/prototypes/journey-v4/README.md; ≤14 s, ≤960 px, no audio), served from the
 * theme. Each one loads only on screen, has its own pause button and its credit, and stays a still photo with Data
 * Saver or reduced motion until the reader presses play (assets/js/videos.js). Record: docs/IMAGE-QUALITY-AUDIT §7.
 */

/** File => creator, as on its Pexels page. */
function er_world_video_creators(): array {
	return [
		'giza-camels-clear'  => 'JORGE MONCADA',
		'giza-day'           => 'K',
		'giza-aerial'        => 'Blanka OG',
		'cairo-nile-sunset'  => 'Nada Ashraf',
		'karnak-columns'     => 'BOB DK',
		'redsea-reef'        => 'Adrien JACTA',
		'redsea-coral'       => 'Adrien JACTA',
		'redsea-moray'       => 'Adrien JACTA',
		'alexandria-qaitbay' => 'The Instagrapher',
		'tanoura'            => 'Magda Ehlers',
	];
}

function er_world_video_url( string $file ): string {
	return ER_THEME_URI . '/assets/video/' . $file . '.mp4?ver=' . rawurlencode( (string) er_asset_ver( 'assets/video/' . $file . '.mp4' ) );
}

/** The seed of a page, without its language suffix. */
function er_world_seed( int $post_id ): string {
	return (string) preg_replace( '/-(ar|de|fr|it|es|ru|zh)$/', '', (string) get_post_meta( $post_id, '_er_seed_id', true ) );
}

/**
 * A video over the photo it belongs to, with its controls. $poster: an image URL shown until it plays (for places
 * with no photo behind it). $attrs: extra attributes on the <video> (data-j for a trip step).
 */
function er_world_video( string $file, string $class = 'wvid', string $poster = '', string $attrs = '', bool $controls = true ): string {
	$who = er_world_video_creators()[ $file ] ?? '';
	if ( '' === $who ) {
		return '';
	}
	$credit = er_t( 'Video: {name}, Pexels', [ 'name' => $who ] );
	$html   = '<video class="' . esc_attr( $class ) . '" muted loop playsinline preload="none" aria-hidden="true" data-src="' . esc_url( er_world_video_url( $file ) ) . '"' . ( '' !== $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . ( '' !== $attrs ? ' ' . $attrs : '' ) . '></video>';
	if ( $controls ) {
		$html .= er_world_video_controls( $credit );
	}
	return $html;
}

/** A section's video controls: pause / play, and the credit behind an "i". */
function er_world_video_controls( string $credit ): string {
	static $n = 0;
	$n++;
	return '<div class="vctl" data-vctl><button type="button" class="vctl__btn" data-vplay aria-pressed="false" aria-label="' . esc_attr( er_t( 'Pause video' ) ) . '" data-l-pause="' . esc_attr( er_t( 'Pause video' ) ) . '" data-l-play="' . esc_attr( er_t( 'Play video' ) ) . '">'
		. '<svg viewBox="0 0 24 24" aria-hidden="true" class="vctl__pause"><rect x="6.5" y="5" width="3.6" height="14" rx="1"/><rect x="13.9" y="5" width="3.6" height="14" rx="1"/></svg>'
		. '<svg viewBox="0 0 24 24" aria-hidden="true" class="vctl__play"><path d="M8 5.5v13l11-6.5z"/></svg></button>'
		. '<button type="button" class="vctl__i" aria-label="' . esc_attr( er_t( 'Video credit' ) ) . '" aria-describedby="vctl-tip-' . $n . '">i</button>'
		. '<span class="vctl__tip" role="tooltip" id="vctl-tip-' . $n . '">' . esc_html( $credit ) . '</span></div>';
}

/** Where each video plays (prototype v4-worlds.js, CHAPTERS, GROUPS, MOOD_VIDEO). */
function er_world_video_for( string $where, string $key ) {
	$map = [
		'chapter' => [ 1 => 'giza-camels-clear', 4 => 'redsea-reef' ],
		'place'   => [ 'dest-cairo' => 'giza-aerial', 'dest-alexandria' => 'alexandria-qaitbay', 'dest-luxor' => 'karnak-columns', 'dest-hurghada' => 'redsea-coral' ],
		'opener'  => [ 'exp-dinner' => 'cairo-nile-sunset', 'exp-alexandria' => 'alexandria-qaitbay', 'exp-dive' => 'redsea-reef', 'exp-ras-mohammed' => 'redsea-reef' ],
		'steps'   => [ 'exp-giza' => [ 0 => 'giza-day', 1 => 'giza-aerial', 2 => 'giza-camels-clear' ], 'exp-dive' => [ 2 => 'redsea-coral', 3 => 'redsea-moray' ], 'exp-dinner' => [ 0 => 'cairo-nile-sunset', 2 => 'tanoura' ] ],
		'papy'    => [ 'exp-valley' => [ 'combine', 'karnak-columns' ] ],
		'mood'    => [ 'ancient' => 'karnak-columns', 'beach' => 'redsea-reef', 'food' => 'alexandria-qaitbay' ],
	];
	return $map[ $where ][ $key ] ?? null;
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( ! er_world_on() ) {
		return;
	}
	wp_enqueue_style( 'er-videos', ER_THEME_URI . '/assets/css/videos.css', [ 'er-pages' ], er_asset_ver( 'assets/css/videos.css' ) );
	wp_enqueue_script( 'er-videos', ER_THEME_URI . '/assets/js/videos.js', [], er_asset_ver( 'assets/js/videos.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	if ( is_front_page() ) {
		$moods = [];
		foreach ( [ 'ancient', 'beach', 'food' ] as $m ) {
			$f           = er_world_video_for( 'mood', $m );
			$moods[ $m ] = [ er_world_video_url( $f ), er_t( 'Video: {name}, Pexels', [ 'name' => er_world_video_creators()[ $f ] ] ) ];
		}
		wp_add_inline_script( 'er-videos', 'window.erMoodVideos=' . wp_json_encode( $moods ) . ';window.erVideoLabels=' . wp_json_encode( [ 'pause' => er_t( 'Pause video' ), 'play' => er_t( 'Play video' ), 'credit' => er_t( 'Video credit' ) ] ) . ';', 'before' );
	}
}, 21 );

/*
 * Proposal 4, the guide article (prototype openGuide): "See the place" photos inside the sections, and "Ready to go?"
 * at the end, with the trip's options (tracked partner links) or the guide's trips.
 */

/** The guide's photos, after the first paragraph of each section that has some (inc/world-guide-photos.php). */
function er_world_guide_photos( string $html, int $post_id ): string {
	static $all = null;
	if ( null === $all ) {
		$all = (array) require __DIR__ . '/world-guide-photos.php';
	}
	$en   = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $post_id, 'en' ) : $post_id;
	$slug = (string) get_post_field( 'post_name', $en ?: $post_id );
	if ( empty( $all[ $slug ] ) ) {
		return $html;
	}
	$english = 'en' === er_lang();
	foreach ( $all[ $slug ] as $sec => $photos ) {
		$at = strpos( $html, 'id="' . $sec . '"' );
		if ( false === $at ) {
			continue;
		}
		$next = strpos( $html, '<h2', $at + 4 );
		$p    = strpos( $html, '</p>', $at );
		$h2   = strpos( $html, '</h2>', $at );
		$pos  = false !== $p && ( false === $next || $p < $next ) ? $p + 4 : ( false !== $h2 ? $h2 + 5 : false );
		if ( false === $pos ) {
			continue;
		}
		// That paragraph can sit inside a box of the section's two-column grid (er-subgrid); the photos then shrank to
		// the box (Grand Egyptian Museum, owner 20 Oct, #13). They go before the grid instead, at the article's width.
		$grid = strrpos( substr( $html, 0, $pos ), '<div class="er-subgrid' );
		if ( false !== $grid && $grid > $at && preg_match_all( '#<div' . chr( 92 ) . 'b|</div>#', $html, $er_tags, PREG_OFFSET_CAPTURE, $grid ) ) {
			$depth = 0;
			foreach ( $er_tags[0] as [ $er_tag, $er_pos ] ) {
				$depth += '</div>' === $er_tag ? -1 : 1;
				if ( 0 === $depth ) {
					if ( $er_pos > $pos ) {
						$pos = $grid;
					}
					break;
				}
			}
		}
		$row = '<div class="gplace gplace--' . min( 3, count( $photos ) ) . '"><p class="gplace__t">' . esc_html( er_t( 'See the place' ) ) . '</p><div class="gplace__g">';
		foreach ( $photos as [ $id, $caption, $who ] ) {
			$base = 'https://images.unsplash.com/photo-' . $id . '?auto=format&fit=crop&q=72';
			$url  = static fn ( int $w ) => $base . '&w=' . $w . '&h=' . (int) round( $w * 2 / 3 );
			$who  = 'Photo: ' . ( '' !== $who ? $who . ', ' : '' ) . 'Unsplash';
			if ( 0 === strpos( $id, 'commons:' ) ) {
				// A Wikimedia Commons photo (er_stock_url), with the credit and licence it requires.
				$url = static fn ( int $w ) => er_stock_url( $id, $w );
				$who = er_commons_credit( $id )[0];
			}
			// Each photo opens the guide's photo viewer (assets/js/p4-guide.js), as in the prototype (owner, 20 Oct, #12).
			$row .= '<figure tabindex="0" role="button" data-full="' . esc_url( 0 === strpos( $id, 'commons:' ) ? er_stock_url( $id, 1920 ) : $base . '&w=1800' ) . '"><img src="' . esc_url( $url( 1200 ) ) . '" srcset="' . esc_attr( $url( 600 ) . ' 600w, ' . $url( 900 ) . ' 900w, ' . $url( 1200 ) . ' 1200w, ' . $url( 1600 ) . ' 1600w' ) . '" sizes="(max-width: 700px) 90vw, 860px" alt="' . esc_attr( $english ? $caption : '' ) . '" loading="lazy" decoding="async" width="900" height="600" />'
				. '<figcaption>' . ( $english ? '<span>' . esc_html( $caption ) . '</span>' : '' ) . '<small>' . esc_html( $who ) . '</small></figcaption></figure>';
		}
		$row .= '</div></div>';
		$html = substr_replace( $html, $row, $pos, 0 );
	}
	return $html;
}

/** "Ready to go?": the trip's options when the guide is about one trip, else the guide's trips. */
function er_world_guide_end( array $offers, array $related, array $dests ): string {
	$trips = array_values( array_filter( array_map( 'intval', wp_list_pluck( $related, 'ID' ) ), static fn ( $i ) => 'publish' === get_post_status( $i ) && 'er_experience' === get_post_type( $i ) ) );
	if ( ! $trips && ! $offers ) {
		return '';
	}
	$one   = 1 === count( $trips );
	$place = 1 === count( $dests ) ? (int) $dests[0]->ID : 0;
	$all   = $place ? er_ux2_trip_url( [ 'destination' => $place, 'len' => '', 'private' => false, 'pickup' => false ] ) : (string) get_post_type_archive_link( 'er_experience' );
	ob_start();
	?>
	<section class="gend on-dark" aria-labelledby="gend-t">
		<div class="gend__in">
			<div class="gend__copy">
				<p class="eyebrow"><?php er_e( 'Ready to go?' ); ?></p>
				<h2 id="gend-t"><?php echo esc_html( $one ? get_the_title( $trips[0] ) : er_t( 'Trips in this guide' ) ); ?></h2>
				<?php if ( $one && function_exists( 'er_booking_summary' ) ) : $sum = er_booking_summary( $offers, true ); ?>
					<?php if ( $sum ) : ?><div class="gend__chips"><?php foreach ( $sum as $c ) { echo '<span>' . esc_html( $c ) . '</span>'; } ?></div><?php endif; ?>
				<?php endif; ?>
				<div class="gend__acts">
					<?php if ( $one ) : ?><a class="btn btn--primary" href="<?php echo esc_url( get_permalink( $trips[0] ) ); ?>"><?php er_e( 'See the trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( $all ); ?>"><?php echo esc_html( $place ? er_t( 'See all trips in {place}', [ 'place' => get_the_title( $place ) ] ) : er_t( 'See all trips' ) ); ?></a>
				</div>
			</div>
			<div class="gend__list">
				<?php if ( $one && $offers ) : ?>
					<?php
					$source = (int) get_queried_object_id();
					foreach ( $offers as $offer ) {
						$o     = er_offer_data( (int) $offer, 'guide-end', $source );
						$facts = function_exists( 'er_offer_facts' ) && function_exists( 'er_offer_facts_line' ) ? er_offer_facts_line( (array) er_offer_facts( (int) $offer ) ) : [];
						$cta   = er_offer_cta_html( (int) $offer, [ 'placement' => 'guide-end', 'class' => 'btn btn--light btn--sm', 'icon' => ' ' . er_icon( 'i-arrow-ur', 'icon--sm' ) ] );
						if ( '' === $cta ) {
							continue;
						}
						echo '<div class="gend__opt"><div><b' . ( ! empty( $o['title_lang'] ) ? ' lang="' . esc_attr( $o['title_lang'] ) . '" dir="ltr"' : '' ) . '>' . esc_html( $o['title'] ) . '</b><small>' . esc_html( implode( ' · ', array_filter( array_merge( [ $o['provider'] ], $facts ) ) ) ) . '</small></div>' . $cta . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
					}
					?>
					<?php er_disclosure(); ?>
				<?php else : ?>
					<?php foreach ( $trips as $t ) { echo er_world_mini( $t ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( er_world_on() && is_singular( 'er_guide' ) ) {
		wp_enqueue_style( 'er-guide-story', ER_THEME_URI . '/assets/css/guide-story.css', [ 'er-pages' ], er_asset_ver( 'assets/css/guide-story.css' ) );
	}
}, 20 );

/*
 * Proposal 4 on the old pinned scenes (owner, 18 Oct: "the scroll between the slides stays like the old design"):
 * each scene ends in its chapter's trips, where the old "Roamer pick" sat, so the strip comes and goes with the
 * scene's own animation (journey.js animates .scene__pick); the hero shows the story's index.
 */
function er_world_scene_trips( int $n ): void {
	$ids = er_world_chapter_trips( $n );
	if ( ! $ids ) {
		return;
	}
	$place = er_world_chapter_place( $n );
	$url   = $place ? er_ux2_trip_url( [ 'destination' => $place, 'len' => '', 'private' => false, 'pickup' => false ] ) : (string) get_post_type_archive_link( 'er_experience' );
	echo '<section class="scene__pick scene__trips" aria-label="' . esc_attr( er_t( 'Trips in this chapter' ) ) . '"><div class="scene__trips-h"><p>' . esc_html( er_t( 'Trips in this chapter' ) ) . '</p><strong>' . esc_html( er_t( '{n} trips to book', [ 'n' => (string) count( $ids ) ] ) ) . '</strong>'
		. '<a href="' . esc_url( $url ) . '">' . esc_html( (string) er_home( "scene{$n}_cta" ) ) . ' <span aria-hidden="true">→</span></a></div><div class="scene__trips-list" style="--n:' . (int) count( $ids ) . '">';
	foreach ( $ids as $id ) {
		echo er_world_mini( $id ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div></section>';
}

function er_world_story_index(): void {
	echo '<nav class="story-index" data-hero-fade aria-label="' . esc_attr( er_t( 'Journey chapters' ) ) . '"><p class="story-index__t">' . esc_html( er_t( 'The story · {n} chapters', [ 'n' => '4' ] ) ) . '</p><ol>';
	foreach ( [ 1 => 'pyramids', 2 => 'nile', 3 => 'desert', 4 => 'redsea' ] as $n => $scene ) {
		$c = count( er_world_chapter_trips( $n ) );
		echo '<li><a href="#scene-' . $scene . '" data-rail="' . (int) ( $n - 1 ) . '"><b>' . esc_html( sprintf( '%02d', $n ) ) . '</b><span>' . esc_html( er_home( "scene{$n}_title" ) ) . '</span>' . ( $c ? '<small>' . esc_html( 1 === $c ? er_t( '1 trip' ) : er_t( '{n} trips', [ 'n' => (string) $c ] ) ) . '</small>' : '' ) . '</a></li>';
	}
	echo '</ol></nav>';
}

add_filter( 'body_class', static function ( array $c ): array {
	if ( er_world_on() && is_front_page() ) {
		$c[] = 'er-story';
	}
	return $c;
} );

/*
 * Proposal 4, the homepage's hotels (prototype stayHTML, "expert review, 17 Oct"): a card per place with its photo and
 * its Trip.com areas, each one a tracked partner link. Replaces the old "compared in one place" hotels box.
 */
function er_world_stay_section(): string {
	if ( ! function_exists( 'er_hotel_offers_for_post' ) ) {
		return '';
	}
	$places = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 30, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	$cards  = '';
	$source = (int) get_option( 'page_on_front' );
	$en     = 'en' === er_lang();
	foreach ( $places as $dest ) {
		$dest   = (int) $dest;
		$hotels = er_hotel_offers_for_post( $dest, 10 );
		if ( ! $hotels ) {
			continue;
		}
		$photo = (int) get_post_thumbnail_id( $dest );
		$stock = er_stock_id_for( $dest );
		$img   = $photo ? er_img( $photo, 'large', [ 'alt' => '', 'sizes' => '(max-width: 600px) 100vw, 300px', 'loading' => 'lazy' ] ) : ( '' !== $stock ? er_stock_img( $stock, '', [ 'loading' => 'lazy', 'sizes' => '(max-width: 600px) 100vw, 300px' ], [ 400, 640, 900 ], 0.625 ) : '' );
		$rows  = '';
		foreach ( $hotels as $h ) {
			$o    = er_offer_data( (int) $h, 'home-stay', $source );
			$area = (string) $o['title'];
			if ( $en ) {
				$area = (string) preg_replace( '/^Hotels (in|on|near) (the )?/', '', $area );
				$area = mb_strtoupper( mb_substr( $area, 0, 1 ) ) . mb_substr( $area, 1 );
			}
			$rows .= '<li>' . er_offer_cta_html( (int) $h, [ 'placement' => 'home-stay', 'source' => $source, 'class' => 'stay2__a', 'label' => $area, 'icon' => '<em>' . esc_html( (string) $o['provider'] ) . ' ↗</em>' ] ) . '</li>';
		}
		$cards .= '<article class="stay2__c">' . $img . '<div><h3>' . esc_html( get_the_title( $dest ) ) . '</h3><ul>' . $rows . '</ul></div></article>';
	}
	if ( '' === $cards ) {
		return '';
	}
	return '<section class="stay2" id="stay" aria-labelledby="stay2-t"><div class="container"><p class="eyebrow">' . esc_html( er_t( 'Where to stay' ) ) . '</p><h2 id="stay2-t">' . wp_kses( er_t( 'Hotels near your trips, <em>place by place.</em>' ), [ 'em' => [] ] ) . '</h2><p class="stay2__intro">' . esc_html( er_t( 'Our hotel partner is Trip.com. Pick an area to see its hotels; you choose, book and pay on Trip.com.' ) ) . '</p><div class="stay2__g">' . $cards . '</div><p class="stay2__note">' . esc_html( er_t( 'Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you.' ) ) . '</p></div></section>';
}

/** The homepage between the scenes and the Trip Builder, as the prototype: the quote and facts, the moods, the map, the hotels by place. */
function er_world_short_home( string $html ): string {
	foreach ( [ '<section class="section destinations"', '<section class="section partners"' ] as $start ) {
		$a = strpos( $html, $start );
		if ( false === $a ) {
			continue;
		}
		$e = strpos( $html, '</section>', $a );
		if ( false !== $e ) {
			// The places section (shown again in the story) gives way to "How Egypt Roamer works"; the partners to the hotels.
			$html = substr( $html, 0, $a ) . ( false !== strpos( $start, 'partners' ) ? er_world_stay_section() : er_world_how_section() ) . substr( $html, $e + strlen( '</section>' ) );
		}
	}
	return $html;
}

/**
 * A place's trips in this language, also when the translated trips don't name the place: then the English place's
 * trips, each in this language (the Alexandria day trip in the seven translations, 20 Oct).
 */
function er_world_place_trips_any( int $dest ): array {
	$ids = er_world_place_trips( $dest );
	if ( $ids || ! function_exists( 'pll_get_post' ) ) {
		return $ids;
	}
	$en = (int) pll_get_post( $dest, 'en' );
	if ( ! $en || $en === $dest ) {
		return $ids;
	}
	$found = get_posts( [ 'post_type' => 'er_experience', 'post_status' => 'publish', 'numberposts' => 20, 'fields' => 'ids', 'lang' => 'en', 'meta_query' => [ [ 'key' => '_er_destination', 'value' => $en, 'compare' => '=' ] ] ] ); // phpcs:ignore WordPress.DB.SlowDBQuery
	$out   = [];
	foreach ( $found as $t ) {
		$tr = (int) pll_get_post( (int) $t, er_lang() );
		if ( $tr && 'publish' === get_post_status( $tr ) ) {
			$out[] = $tr;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * "How Egypt Roamer works" (20 Oct, ideas from egyroam.com and the big travel sites): four steps that say plainly
 * what the site does and where the booking happens. Only what is true of every page: places with their trips, the
 * options side by side, booking on the partner's site, the hearts.
 */
function er_world_how_section(): string {
	$steps = [
		[ er_t( 'Choose a place or a trip' ), er_t( 'Seven places, each with the trips you can book there, and guides written from published sources.' ) ],
		[ er_t( 'Compare the options' ), er_t( 'Every trip page puts its partners’ options side by side: length, private or group, pickup, and the partner’s rating and price.' ) ],
		[ er_t( 'Book on the partner’s site' ), er_t( 'You book and pay on Viator, GetYourGuide, Klook, Tiqets or Trip.com. Free cancellation is shown when the partner offers it.' ) ],
		[ er_t( 'Keep your plan' ), er_t( 'Save trips and plans with the heart, and share your list with the people you travel with.' ) ],
	];
	$h  = '<section class="how" aria-labelledby="how-t"><div class="container"><p class="how__eyebrow">' . esc_html( er_t( 'How Egypt Roamer works' ) ) . '</p><h2 id="how-t">' . esc_html( er_t( 'From an idea to a booked day, in four steps' ) ) . '</h2><ol class="how__steps">';
	foreach ( $steps as $i => [ $t, $p ] ) {
		$h .= '<li><span class="how__n">' . sprintf( '%02d', $i + 1 ) . '</span><h3>' . esc_html( $t ) . '</h3><p>' . esc_html( $p ) . '</p></li>';
	}
	return $h . '</ol><p class="how__note">' . esc_html( er_t( 'Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you.' ) ) . '</p></div></section>';
}

/**
 * "Ready-made routes": the three itinerary guides, each with "Read the route" and "Start from this route", which opens
 * the Trip builder with that route's places (as the guide's own summary names them) and nights.
 */
function er_world_routes_section(): string {
	$routes = [
		'7-days-in-egypt-itinerary'  => [ 'cairo,luxor,aswan', 6 ],
		'10-days-in-egypt-itinerary' => [ 'cairo,luxor,aswan,hurghada', 9 ],
		'14-days-in-egypt-itinerary' => [ 'cairo,alexandria,luxor,aswan,hurghada', 13 ],
	];
	$cards = '';
	foreach ( $routes as $slug => [ $places, $nights ] ) {
		$en = get_page_by_path( $slug, OBJECT, 'er_guide' );
		if ( ! $en && function_exists( 'pll_get_post' ) ) {
			$found = get_posts( [ 'post_type' => 'er_guide', 'name' => $slug, 'lang' => 'en', 'numberposts' => 1 ] );
			$en    = $found ? $found[0] : null;
		}
		$id = $en && function_exists( 'pll_get_post' ) ? (int) pll_get_post( $en->ID ) : ( $en ? (int) $en->ID : 0 );
		if ( ! $id || 'publish' !== get_post_status( $id ) ) {
			continue;
		}
		$thumb = (int) get_post_thumbnail_id( $id );
		$stock = er_stock_id_for( $id );
		$img   = $thumb ? er_img( $thumb, 'er-card', [ 'alt' => '', 'sizes' => '(max-width: 700px) 90vw, 400px', 'loading' => 'lazy' ] ) : ( '' !== $stock ? er_stock_img( $stock, '', [ 'sizes' => '(max-width: 700px) 90vw, 400px' ], [ 400, 640, 900 ], 0.6 ) : '' );
		$plan  = add_query_arg( [ 'plan' => $places, 'n' => $nights ], home_url( '/' ) ) . '#planner';
		$cards .= '<li class="route"><a class="route__img" href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1" aria-hidden="true">' . $img . '</a><div class="route__b"><h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3><p>' . esc_html( wp_trim_words( get_the_excerpt( $id ), 30, '…' ) ) . '</p><div class="route__acts"><a class="btn btn--primary btn--sm" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( er_t( 'Read the route' ) ) . '</a><a class="route__plan" href="' . esc_url( $plan ) . '" data-route-plan>' . esc_html( er_t( 'Start from this route' ) ) . ' ' . er_arrow() . '</a></div></div></li>';
	}
	if ( '' === $cards ) {
		return '';
	}
	return '<section class="routes" aria-labelledby="routes-t"><div class="container"><p class="how__eyebrow">' . esc_html( er_t( 'Ready-made routes' ) ) . '</p><h2 id="routes-t">' . esc_html( er_t( 'One week, ten days or two weeks' ) ) . '</h2><ul class="routes__g">' . $cards . '</ul></div></section>';
}
