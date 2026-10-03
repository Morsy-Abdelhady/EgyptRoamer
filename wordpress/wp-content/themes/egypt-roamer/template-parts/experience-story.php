<?php
/**
 * Experience story: the whole experience page told as one narrative, under the hero.
 *
 *   opening (why this journey matters + the route at a glance)
 *   → the journey: stages of different weight (a dark pause, the full-bleed reveal, smaller detail beats,
 *     a photo held beside its text, the peak, the closing beat)
 *   → chapters on the light ground: who it suits → getting there and the facts → what to combine it with
 *     (+ the related destination and guides) → the next step (partner offer or planner) → questions.
 *
 * Every sentence is the page's own approved body, in its own language: er_body_parts() splits the body by
 * heading anchor and the story (Core er_preview_story()) says where each section goes. Sections a journey
 * stage tells (its `from`) are not repeated as chapters. Sections the story does not name are kept as
 * chapters before the questions, so nothing an editor writes is lost.
 *
 * Below the hero by design: the hero stays the only first-view image; stage photos carry their addresses in
 * data-* and immersion.js loads each about a screen ahead (<noscript> keeps them without JavaScript).
 * Everything reads without JavaScript and without motion; the transitions are CSS, only when motion is welcome.
 *
 * @package EgyptRoamer
 * @var array $args { preview: er_preview_for(), parts: er_body_parts(), facts: label => value, offers: offer ids,
 *                    help: next-step HTML when there is no offer, related: post ids, who/not/tips: HTML lists }
 */

defined( 'ABSPATH' ) || exit;

$er_s     = (array) ( $args['preview']['story'] ?? [] );
$er_parts = (array) ( $args['parts'] ?? [] );
if ( ! $er_s || empty( $er_s['stages'] ) ) {
	return;
}
$er_map  = (array) ( $er_s['sections'] ?? [] );
$er_part = static fn ( string $key ) => $er_parts[ (string) ( $er_map[ $key ] ?? '' ) ] ?? null;
$er_num  = static fn ( int $i ): string => str_pad( (string) $i, 2, '0', STR_PAD_LEFT );

// Body text for a stage: "why#2" is the second item of the "why" section, "sun-festival" the whole section.
$er_used = [];
$er_from = static function ( string $from ) use ( $er_parts, &$er_used ): string {
	if ( '' === $from ) {
		return '';
	}
	[ $id, $n ] = array_pad( explode( '#', $from, 2 ), 2, '' );
	$html       = '' !== $n ? (string) ( $er_parts[ $id ]['items'][ (int) $n - 1 ] ?? '' ) : (string) ( $er_parts[ $id ]['html'] ?? '' );
	if ( '' !== $html ) {
		$er_used[ $id ][] = '' !== $n ? (int) $n : 0;
	}
	return $html;
};
$er_steps = [];
foreach ( $er_s['stages'] as $er_st ) {
	$er_st['html'] = $er_from( $er_st['from'] );
	if ( ! $er_st['photo'] && '' === $er_st['html'] && '' === $er_st['title'] ) {
		continue;
	}
	$er_steps[] = $er_st;
}
// A section counts as told by the journey only when every one of its items (or the whole of it) was used.
$er_told = [];
foreach ( (array) ( $er_map['absorbed'] ?? [] ) as $er_id ) {
	$er_p = $er_parts[ $er_id ] ?? null;
	$er_u = $er_used[ $er_id ] ?? [];
	if ( $er_p && ( in_array( 0, $er_u, true ) || ( $er_p['items'] && count( array_unique( $er_u ) ) >= count( $er_p['items'] ) ) ) ) {
		$er_told[] = $er_id;
	}
}
$er_total = count( $er_steps );

// Frames per layout: [ phone ratio, phone widths, wide ratio, wide widths, wide sizes ]. Widths stop near 2× the frame.
$er_frames = [
	'reveal' => [ 1.25, [ 480, 640, 828, 1080 ], 0.56, [ 960, 1280, 1600, 1920 ], '100vw' ],
	'detail' => [ 1.25, [ 400, 600, 800 ], 1.25, [ 480, 720, 960 ], '(min-width: 1024px) 34vw, 78vw' ],
	'split'  => [ 1.25, [ 480, 640, 828, 1080 ], 0.9, [ 720, 960, 1280, 1600 ], '(min-width: 1024px) 58vw, 100vw' ],
	'peak'   => [ 1.25, [ 400, 600, 800 ], 1.25, [ 480, 720, 960 ], '(min-width: 1024px) 30rem, 86vw' ],
];
$er_pic = static function ( string $photo, string $alt, string $layout ) use ( $er_frames ): string {
	[ $pr, $pw, $wr, $ww, $wsizes ] = $er_frames[ $layout ] ?? $er_frames['split'];
	$phone_sizes                   = 'detail' === $layout ? '78vw' : ( 'peak' === $layout ? '86vw' : '100vw' );
	$phone                         = er_stock_srcset( $photo, $pw, $pr );
	$wide                          = er_stock_srcset( $photo, $ww, $wr );
	$src                           = er_stock_url( $photo, $ww[1], $wr );
	$source                        = '<source media="(max-width: 1023.98px)" %s="' . esc_attr( $phone ) . '" sizes="' . esc_attr( $phone_sizes ) . '" />';
	$real                          = '<picture class="er-pic">' . sprintf( $source, 'srcset' ) . er_stock_img( $photo, $alt, [ 'sizes' => $wsizes, 'loading' => 'lazy', 'decoding' => 'async' ], $ww, $wr ) . '</picture>';
	$blank                         = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	return '<picture class="er-pic">' . sprintf( $source, 'data-srcset' )
		. sprintf( '<img src="%s" data-src="%s" data-srcset="%s" sizes="%s" alt="%s" decoding="async" data-defer />', $blank, esc_url( $src ), esc_attr( $wide ), esc_attr( $wsizes ), esc_attr( $alt ) )
		. '</picture><noscript>' . $real . '</noscript>';
};
$er_credit = static function ( array $st ): string {
	if ( ! $st['photographer'] ) {
		return '';
	}
	// "name / Unsplash" keeps its own direction (<bdi>), so it doesn't reorder in Arabic.
	$text = str_replace( [ '{name} / Unsplash', '{name}' ], [ '<bdi>' . esc_html( $st['photographer'] ) . ' / Unsplash</bdi>', '<bdi>' . esc_html( $st['photographer'] ) . '</bdi>' ], esc_html( er_t( 'Photo: {name} / Unsplash' ) ) );
	return '<p class="imm-step__credit">' . ( $st['source'] ? '<a href="' . esc_url( $st['source'] ) . '" rel="nofollow noopener">' . $text . '</a>' : $text ) . '</p>';
};

// Opening: the story's statement (its own language) or, without it, the opening section's own heading.
$er_open   = $er_part( 'opening' );
$er_open_t = $er_s['title'] ?: (string) ( $er_open['title'] ?? '' );
$er_open_id = (string) ( $er_map['opening'] ?? 'story-title' );
// Anchors of the sections the journey tells, kept on the stage that tells them (links into the old page still land).
$er_anchor = [];
foreach ( $er_told as $er_id ) {
	foreach ( $er_steps as $er_k => $er_st ) {
		if ( 0 === strpos( $er_st['from'], $er_id ) && ! isset( $er_anchor[ $er_k ] ) ) {
			$er_anchor[ $er_k ] = $er_id;
			break;
		}
	}
}

// Chapters in reading order; sections the story does not place come before the questions.
$er_placed = array_merge( array_values( array_filter( array_map( static fn ( $k ) => is_string( $er_map[ $k ] ?? null ) ? $er_map[ $k ] : '', [ 'opening', 'fit', 'plan', 'combine', 'faq' ] ) ) ), $er_told );
$er_rest   = array_diff_key( $er_parts, array_flip( $er_placed ) );
$er_chap   = 0;
$er_chapter = static function ( ?array $part, string $id, string $extra_before = '', string $extra_after = '', string $mod = '', string $outside = '' ) use ( &$er_chap, $er_num ): void {
	if ( ! $part && '' === $extra_before . $extra_after . $outside ) {
		return;
	}
	++$er_chap;
	?>
	<section class="chap<?php echo $mod ? ' chap--' . esc_attr( $mod ) : ''; ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>">
		<div class="chap__inner container">
			<header class="chap__head">
				<span class="chap__n" aria-hidden="true"><?php echo esc_html( $er_num( $er_chap ) ); ?></span>
				<h2 class="chap__title" id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( html_entity_decode( (string) ( $part['title'] ?? '' ), ENT_QUOTES, 'UTF-8' ) ); ?></h2>
			</header>
			<div class="chap__body">
				<div class="prose">
					<?php echo $extra_before; // phpcs:ignore WordPress.Security.EscapeOutput -- built by the caller from escaped parts ?>
					<?php echo (string) ( $part['html'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?>
					<?php echo $extra_after; // phpcs:ignore WordPress.Security.EscapeOutput -- built by the caller from escaped parts ?>
				</div>
				<?php echo $outside; // phpcs:ignore WordPress.Security.EscapeOutput -- cards built by er_card_grid() ?>
			</div>
		</div>
	</section>
	<?php
};
?>
<div class="exp-story" data-imm>
	<section class="story-open on-dark" aria-labelledby="<?php echo esc_attr( $er_open_id ); ?>">
		<div class="story-open__inner container">
			<?php if ( $er_s['full'] && $er_s['label'] ) : ?>
				<p class="eyebrow"><?php echo esc_html( $er_s['label'] ); ?></p>
			<?php endif; ?>
			<h2 class="story-open__title" id="<?php echo esc_attr( $er_open_id ); ?>"><?php echo esc_html( html_entity_decode( $er_open_t, ENT_QUOTES, 'UTF-8' ) ); ?></h2>
			<?php if ( $er_open ) : ?>
				<div class="story-open__lede"><?php echo $er_open['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
			<?php endif; ?>
			<?php if ( $er_total > 2 ) : ?>
				<ol class="imm__route" aria-label="<?php echo esc_attr( $er_s['label'] ?: $er_open_t ); ?>">
					<?php foreach ( $er_steps as $er_i => $er_st ) : ?>
						<?php if ( $er_st['eyebrow'] || $er_st['title'] ) : ?>
							<li><a href="#imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-link><span class="imm__route-n" aria-hidden="true"><?php echo esc_html( $er_num( $er_i + 1 ) ); ?></span><?php echo esc_html( $er_st['eyebrow'] ?: $er_st['title'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>
	</section>

	<ol class="imm on-dark" aria-labelledby="<?php echo esc_attr( $er_open_id ); ?>">
		<?php
		$er_details = 0;
		foreach ( $er_steps as $er_i => $er_st ) :
			$er_layout = $er_st['photo'] ? ( in_array( $er_st['layout'], [ 'reveal', 'detail', 'split', 'peak' ], true ) ? $er_st['layout'] : 'split' ) : 'text';
			$er_alt    = 'detail' === $er_layout && 1 === ( $er_details++ % 2 );
			$er_cls    = 'imm-step imm-step--' . $er_layout . ( 'text' === $er_layout ? ' imm-step--' . ( $er_st['tone'] ?: 'plain' ) : '' ) . ( $er_alt ? ' imm-step--alt' : '' );
			?>
			<li class="<?php echo esc_attr( $er_cls ); ?>" id="imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-step>
				<?php if ( $er_st['photo'] ) : ?>
					<figure class="imm-step__media">
						<?php echo $er_pic( $er_st['photo'], $er_st['alt'], $er_layout ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( $er_st['generated'] ) : ?>
							<span class="moments__flag"><?php er_e( 'Illustration, not footage of the place' ); ?></span>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
				<div class="imm-step__text">
					<?php echo isset( $er_anchor[ $er_i ] ) ? '<span class="imm-step__anchor" id="' . esc_attr( $er_anchor[ $er_i ] ) . '"></span>' : ''; ?>
					<p class="imm-step__eyebrow"><span class="imm-step__n"><?php echo esc_html( $er_num( $er_i + 1 ) ); ?><span class="imm-step__of"> / <?php echo esc_html( $er_num( $er_total ) ); ?></span></span><?php echo $er_st['eyebrow'] ? ' <span>' . esc_html( $er_st['eyebrow'] ) . '</span>' : ''; ?></p>
					<?php if ( $er_st['title'] ) : ?>
						<h3 class="imm-step__title"><?php echo esc_html( $er_st['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $er_st['html'] ) : ?>
						<div class="imm-step__body"><?php echo wp_kses_post( wpautop( $er_st['html'] ) ); ?></div>
					<?php elseif ( $er_st['text'] ) : ?>
						<div class="imm-step__body"><p><?php echo esc_html( $er_st['text'] ); ?></p></div>
					<?php endif; ?>
					<?php echo $er_credit( $er_st ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>

	<div class="story-chapters">
		<?php
		// Who it suits (+ the editor's "Best for / may prefer something else" lists, when filled).
		$er_chapter( $er_part( 'fit' ), (string) ( $er_map['fit'] ?? 'fit' ), '', (string) ( $args['who'] ?? '' ) );

		// Getting there, with the facts in one line above it (+ the editor's "Good to know" list, when filled).
		$er_facts = array_filter( (array) ( $args['facts'] ?? [] ) );
		$er_dl    = '';
		if ( $er_facts ) {
			$er_dl = '<dl class="chap__facts">';
			foreach ( $er_facts as $er_label => $er_value ) {
				$er_dl .= '<div><dt>' . esc_html( (string) $er_label ) . '</dt><dd>' . esc_html( (string) $er_value ) . '</dd></div>';
			}
			$er_dl .= '</dl>';
		}
		$er_chapter( $er_part( 'plan' ), (string) ( $er_map['plan'] ?? 'plan' ), $er_dl, (string) ( $args['tips'] ?? '' ) );

		// What to combine it with, and where: the related destination and guides as one set of cards.
		$er_rel = array_slice( array_values( array_unique( array_map( 'intval', (array) ( $args['related'] ?? [] ) ) ) ), 0, 3 );
		$er_cards = '';
		if ( $er_rel ) {
			ob_start();
			echo '<div class="chap__cards chap__cards--' . count( $er_rel ) . '">';
			er_card_grid( $er_rel, 'experience-story', 'h3' );
			echo '</div>';
			$er_cards = (string) ob_get_clean();
		}
		$er_chapter( $er_part( 'combine' ), (string) ( $er_map['combine'] ?? 'combine' ), '', '', '', $er_cards );

		// Anything else the editor wrote, in body order.
		foreach ( $er_rest as $er_id => $er_p ) {
			$er_chapter( $er_p, (string) $er_id );
		}
		?>
	</div>

	<?php if ( ! empty( $args['offers'] ) || ! empty( $args['help'] ) ) : ?>
		<section class="story-next on-dark" aria-labelledby="story-next-title">
			<div class="story-next__inner container">
				<?php if ( ! empty( $args['offers'] ) ) : ?>
					<h2 class="story-next__title" id="story-next-title"><?php er_e( 'Book with our partners' ); ?></h2>
					<div class="story-next__offers">
						<?php er_offer_rows( (array) $args['offers'], 'experience-offers' ); ?>
						<?php er_disclosure(); ?>
					</div>
				<?php else : ?>
					<h2 class="story-next__title" id="story-next-title"><?php er_e( 'Need personal help?' ); ?></h2>
					<?php echo $args['help']; // phpcs:ignore WordPress.Security.EscapeOutput -- built by the caller from escaped parts ?>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php $er_chapter( $er_part( 'faq' ), (string) ( $er_map['faq'] ?? 'faq' ), '', '', 'faq' ); ?>
</div>
