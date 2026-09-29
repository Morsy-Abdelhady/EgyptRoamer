<?php
/**
 * Inner-page sections (destinations, tours/experiences/activities, guides).
 *
 * Works on the rendered body only, so the stored content, the block editor and
 * every language stay as they are:
 *   - one <section class="er-sec"> per H2, numbered in CSS;
 *   - lists whose every item opens with a bold term become cards (.er-points);
 *   - runs of H3 + paragraph become a card grid (.er-subgrid);
 *   - consecutive FAQ <details> share one box (.er-faq);
 *   - the in-body "On this page" box is replaced by a sticky tab bar built from
 *     the H2 anchors (er_section_nav()), labelled with that box's own text.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The current post's body, restructured.
 *
 * @return array{html:string,links:array<int,array{0:string,1:string}>,label:string}
 */
function er_body(): array {
	$html  = (string) apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals -- core hook
	$html  = str_replace( ']]>', ']]&gt;', $html );
	$label = er_t( 'On this page' );

	$links = [];
	if ( preg_match_all( '#<h2\b[^>]*\bid="([^"]+)"[^>]*>(.*?)</h2>#s', $html, $hs, PREG_SET_ORDER ) ) {
		foreach ( $hs as $h ) {
			$text = trim( html_entity_decode( wp_strip_all_tags( $h[2] ), ENT_QUOTES, 'UTF-8' ) );
			// "Where to stay: the coast, area by area" → "Where to stay" (also the full-width colon).
			$short = trim( (string) preg_split( '/\s*[:：]\s*/u', $text, 2 )[0] );
			$links[] = [ $h[1], mb_strlen( $short ) >= 3 ? $short : $text ];
		}
	}

	if ( count( $links ) >= 3 ) {
		// The tab bar replaces the body's own contents box; keep that box's (translated) label.
		if ( preg_match( '#<div class="wp-block-group er-toc[^"]*">\s*<p[^>]*>(.*?)</p>.*?</ul>\s*</div>#s', $html, $m ) ) {
			$label = trim( wp_strip_all_tags( $m[1] ) ) ?: $label;
			$html  = str_replace( $m[0], '', $html );
		}
		$html = er_sections_wrap( $html );
	}
	return [ 'html' => trim( $html ), 'links' => $links, 'label' => $label ];
}

/** Wrap each H2 and what follows it in a section; mark lists, H3 runs and FAQs. */
function er_sections_wrap( string $html ): string {
	$parts = preg_split( '#(?=<h2\b[^>]*\bid=")#', $html );
	$out   = er_sections_mark( (string) array_shift( $parts ) );
	foreach ( $parts as $part ) {
		$out .= '<section class="er-sec">' . er_sections_mark( $part ) . '</section>';
	}
	return $out;
}

function er_sections_mark( string $chunk ): string {
	// Callouts keep their plain lists: set them aside while the lists are marked.
	$kept  = [];
	$chunk = preg_replace_callback(
		'#<div class="wp-block-group er-callout[^"]*">.*?</div>#s',
		static function ( $m ) use ( &$kept ) {
			$kept[] = $m[0];
			return '<!--er-callout-' . ( count( $kept ) - 1 ) . '-->';
		},
		$chunk
	);

	// Lists where every item starts with a bold term.
	$chunk = preg_replace_callback(
		'#<ul class="wp-block-list">(.*?)</ul>#s',
		static function ( $m ) {
			preg_match_all( '#<li\b[^>]*>(.*?)</li>#s', $m[1], $items );
			if ( count( $items[1] ) < 2 ) {
				return $m[0];
			}
			// A term is a bold opening that ends in . or : (inside or right after the bold), or is
			// followed by a bracket: "**El Gouna:** …", "**Timing**: …", "**Hurghada town** (…)".
			$term = '#^\s*<strong>((?:(?!</strong>).)+?)([.:：。]?)</strong>(\s*[:：]\s*|\s*(?=[(（])|\s+)#su';
			$out  = '';
			foreach ( $items[0] as $i => $li_html ) {
				if ( ! preg_match( $term, $items[1][ $i ], $t ) || ( '' === $t[2] && ! preg_match( '#[:：(（]#u', $t[3] . mb_substr( substr( $items[1][ $i ], strlen( $t[0] ) ), 0, 1 ) ) ) ) {
					return $m[0];
				}
				$out .= '<li><strong>' . $t[1] . '</strong> <span class="er-points__text">' . substr( $items[1][ $i ], strlen( $t[0] ) ) . '</span></li>';
			}
			return '<ul class="wp-block-list er-points">' . $out . '</ul>';
		},
		$chunk
	);
	$chunk = preg_replace_callback( '#<!--er-callout-(\d+)-->#', static fn ( $m ) => $kept[ (int) $m[1] ], $chunk );

	// Two or more H3 + one or two paragraphs, ending the section or followed by a callout/list-free block.
	$chunk = preg_replace_callback(
		'#((?:<h3 class="wp-block-heading"[^>]*>(?:(?!</h3>).)*</h3>\s*(?:<p\b[^>]*>(?:(?!</p>).)*</p>\s*){1,2}){2,})(?=<div class="wp-block-group er-callout|<h2|\s*$)#s',
		static function ( $m ) {
			$units = preg_split( '#(?=<h3 class="wp-block-heading")#', trim( $m[1] ), -1, PREG_SPLIT_NO_EMPTY );
			return '<div class="er-subgrid">' . implode( '', array_map( static fn ( $u ) => '<div class="er-sub">' . trim( $u ) . '</div>', $units ) ) . '</div>';
		},
		$chunk
	);

	// FAQ answers in one box.
	return preg_replace( '#((?:<details\b.*?</details>\s*)+)#s', '<div class="er-faq">$1</div>', $chunk );
}

/**
 * The reading layout: one column, plus a sidebar only when there is something to put in it.
 * No sidebar at all: the column is centred (pass the same flag to er_page_hero() and
 * er_section_nav() so the hero text and tabs share its axis).
 */
function er_layout( string $main, string $aside = '' ): void {
	$aside = trim( $aside );
	echo '<div class="page-layout ' . ( '' !== $aside ? 'page-layout--aside' : 'page-layout--single' ) . '">';
	echo '<article class="page-main">' . $main . '</article>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built by the templates from escaped parts
	if ( '' !== $aside ) {
		echo '<aside class="page-aside">' . $aside . '</aside>'; // phpcs:ignore WordPress.Security.EscapeOutput -- as above
	}
	echo '</div>';
}

/** Sticky tab bar for the H2 sections. */
function er_section_nav( array $links, string $label, bool $measure = false ): void {
	if ( count( $links ) < 3 ) {
		return;
	}
	?>
	<nav class="secnav" aria-label="<?php echo esc_attr( $label ); ?>">
		<div class="secnav__inner container<?php echo $measure ? ' secnav__inner--measure' : ''; ?>">
			<ul class="secnav__list">
				<?php foreach ( $links as $i => $l ) : ?>
					<li><a href="#<?php echo esc_attr( $l[0] ); ?>" class="secnav__link<?php echo 0 === $i ? ' is-active' : ''; ?>"><?php echo esc_html( $l[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</nav>
	<?php
}

/** "Key facts" card for the sidebar. */
function er_glance( array $facts, array $highlights = [] ): void {
	if ( ! $facts && ! $highlights ) {
		return;
	}
	?>
	<div class="glance">
		<p class="glance__label"><?php er_e( 'Key facts' ); ?></p>
		<?php if ( $facts ) : ?>
			<dl class="glance__facts">
				<?php foreach ( $facts as $er_label => $er_value ) : ?>
					<div><dt><?php echo esc_html( $er_label ); ?></dt><dd><?php echo esc_html( $er_value ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
		<?php if ( $highlights ) : ?>
			<div class="chips" aria-label="<?php echo esc_attr( er_t( 'Highlights' ) ); ?>">
				<?php foreach ( $highlights as $er_h ) : ?>
					<span class="chip"><?php echo esc_html( $er_h ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
