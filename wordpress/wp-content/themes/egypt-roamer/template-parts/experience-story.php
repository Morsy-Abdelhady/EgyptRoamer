<?php
/**
 * Experience story: everything below the hero of an experience that has a story, as one editorial sequence
 * (the owner's Abu Simbel reference design). Grounds alternate on purpose:
 *
 *   intro (light)            the opening statement, centred, magazine-style
 *   journey intro (dark)     what the journey is, and its chapters as a line to follow
 *   moments (dark/cinema)    each chapter composed differently: a dark text panel, the full-bleed climax, text
 *                            beside an edge-to-edge photo, the peak centred in the dark, a photo beside its
 *                            text, a text-only ending
 *   what it feels like       a full-width photograph with its words over it
 *   what to know (light)     the plan, as an editorial information layout
 *   combine it with (light)  picture links
 *   questions (dark)         an editorial accordion
 *   the end (image)          the next step over the hero's own photograph
 *
 * Every sentence is the page's own approved body, in its own language: er_body_parts() splits the body by
 * heading anchor and the story (Core er_preview_story()) says where each section — or sentence of the opening —
 * goes. A section a chapter tells (its `from`) is not repeated. Sections the story does not name stay before the
 * questions, so nothing an editor writes is lost. Structural labels exist only in the story's own languages.
 *
 * The hero is the only first-view image. Chapter photos carry their addresses in data-* and immersion.js loads
 * each about a screen ahead (<noscript> keeps them without JavaScript); the other photos are lazy.
 *
 * @package EgyptRoamer
 * @var array $args { preview, parts, facts: label => value, offers: offer ids, help: next-step buttons HTML,
 *                    related: post ids, who/tips: HTML lists, hero: [ image id, stock photo id ] }
 */

defined( 'ABSPATH' ) || exit;

$er_s     = (array) ( $args['preview']['story'] ?? [] );
$er_parts = (array) ( $args['parts'] ?? [] );
if ( ! $er_s || empty( $er_s['stages'] ) ) {
	return;
}
$er_map  = (array) ( $er_s['sections'] ?? [] );
$er_lbl  = (array) ( $er_s['labels'] ?? [] );
$er_part = static fn ( string $key ) => $er_parts[ (string) ( $er_map[ $key ] ?? '' ) ] ?? null;
$er_num  = static fn ( int $i ): string => str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
$er_txt  = static fn ( string $s ): string => esc_html( html_entity_decode( $s, ENT_QUOTES, 'UTF-8' ) );
$er_eye  = static fn ( string $key, string $class = 'eyebrow' ): string => ! empty( $er_lbl[ $key ] ) ? '<p class="' . esc_attr( $class ) . '">' . esc_html( $er_lbl[ $key ] ) . '</p>' : '';

// Body text for a chapter: "why#2" is the second item of the "why" section, "sun-festival" the whole section.
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
// The anchors of told sections stay on the chapter that tells them, so links into the old page still land.
$er_anchor = [];
foreach ( $er_told as $er_id ) {
	foreach ( $er_steps as $er_k => $er_st ) {
		if ( 0 === strpos( $er_st['from'], $er_id ) && ! isset( $er_anchor[ $er_k ] ) ) {
			$er_anchor[ $er_k ] = $er_id;
			break;
		}
	}
}

// The opening section, split by sentence between the intro and the journey intro (story `split`), when its
// paragraph has those sentences; otherwise the whole of it opens the page.
$er_open   = $er_part( 'opening' );
$er_intro  = (string) ( $er_open['html'] ?? '' );
$er_jtext  = '';
$er_split  = (array) ( $er_s['split'] ?? [] );
if ( $er_open && 1 === count( $er_open['paras'] ) && ! empty( $er_split['intro'] ) && ! empty( $er_split['journey'] ) ) {
	$er_sent = preg_split( '/(?<=[.!?؟])\s+(?=\S)|(?<=。)/u', trim( $er_open['paras'][0] ), -1, PREG_SPLIT_NO_EMPTY );
	$er_need = max( array_merge( $er_split['intro'], $er_split['journey'] ) );
	if ( count( $er_sent ) >= $er_need ) {
		$er_pick  = static fn ( array $nums ): string => implode( ' ', array_map( static fn ( $n ) => trim( $er_sent[ $n - 1 ] ), $nums ) );
		$er_intro = '<p>' . $er_pick( $er_split['intro'] ) . '</p>';
		$er_jtext = '<p>' . $er_pick( $er_split['journey'] ) . '</p>';
	}
}
$er_open_t  = $er_s['title'] ?: (string) ( $er_open['title'] ?? '' );
$er_open_id = (string) ( $er_map['opening'] ?? 'story-title' );

// Deferred photo: [ phone ratio, phone widths, wide ratio, wide widths, wide sizes, phone sizes ].
$er_frames = [
	'reveal' => [ 1.3, [ 480, 640, 828, 1080 ], 0.6, [ 960, 1280, 1600, 1920 ], '100vw', '100vw' ],
	'split'  => [ 1.0, [ 480, 640, 828, 1080 ], 0.95, [ 720, 960, 1280, 1600 ], '(min-width: 1024px) 58vw, 100vw', '100vw' ],
	'band'   => [ 1.0, [ 480, 640, 828, 1080 ], 0.95, [ 720, 960, 1280, 1600 ], '(min-width: 1024px) 58vw, 100vw', '100vw' ],
	'peak'   => [ 1.25, [ 400, 600, 800 ], 1.25, [ 480, 720, 960 ], '(min-width: 1024px) 30rem, 86vw', '86vw' ],
	'detail' => [ 1.25, [ 400, 600, 800 ], 1.25, [ 480, 720, 960 ], '(min-width: 1024px) 34vw, 78vw', '78vw' ],
	'feel'   => [ 1.3, [ 480, 640, 828, 1080 ], 0.5, [ 960, 1280, 1600, 1920 ], '100vw', '100vw' ],
];
$er_pic = static function ( string $photo, string $alt, string $layout ) use ( $er_frames ): string {
	[ $pr, $pw, $wr, $ww, $wsizes, $psizes ] = $er_frames[ $layout ] ?? $er_frames['band'];
	$phone  = er_stock_srcset( $photo, $pw, $pr );
	$wide   = er_stock_srcset( $photo, $ww, $wr );
	$src    = er_stock_url( $photo, $ww[1], $wr );
	$source = '<source media="(max-width: 1023.98px)" %s="' . esc_attr( $phone ) . '" sizes="' . esc_attr( $psizes ) . '" />';
	$real   = '<picture class="er-pic">' . sprintf( $source, 'srcset' ) . er_stock_img( $photo, $alt, [ 'sizes' => $wsizes, 'loading' => 'lazy', 'decoding' => 'async' ], $ww, $wr ) . '</picture>';
	$blank  = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	return '<picture class="er-pic">' . sprintf( $source, 'data-srcset' )
		. sprintf( '<img src="%s" data-src="%s" data-srcset="%s" sizes="%s" alt="%s" decoding="async" data-defer />', $blank, esc_url( $src ), esc_attr( $wide ), esc_attr( $wsizes ), esc_attr( $alt ) )
		. '</picture><noscript>' . $real . '</noscript>';
};
$er_credit = static function ( array $st, string $class = 'xs-credit' ): string {
	if ( empty( $st['photographer'] ) ) {
		return '';
	}
	// "name / Unsplash" keeps its own direction (<bdi>), so it doesn't reorder in Arabic.
	$text = str_replace( [ '{name} / Unsplash', '{name}' ], [ '<bdi>' . esc_html( $st['photographer'] ) . ' / Unsplash</bdi>', '<bdi>' . esc_html( $st['photographer'] ) . '</bdi>' ], esc_html( er_t( 'Photo: {name} / Unsplash' ) ) );
	return '<p class="' . esc_attr( $class ) . '">' . ( ! empty( $st['source'] ) ? '<a href="' . esc_url( $st['source'] ) . '" rel="nofollow noopener">' . $text . '</a>' : $text ) . '</p>';
};

$er_feel   = $er_part( 'feel' );
$er_know   = $er_part( 'know' );
$er_comb   = $er_part( 'combine' );
$er_faq    = $er_part( 'faq' );
$er_placed = array_merge( array_values( array_filter( array_map( static fn ( $k ) => is_string( $er_map[ $k ] ?? null ) ? $er_map[ $k ] : '', [ 'opening', 'feel', 'know', 'combine', 'faq' ] ) ) ), $er_told );
$er_rest   = array_diff_key( $er_parts, array_flip( $er_placed ) );
?>
<div class="exp-story" data-imm>

	<?php /* 02 Intro: the opening statement, centred. */ ?>
	<section class="xs-intro" aria-labelledby="<?php echo esc_attr( $er_open_id ); ?>">
		<div class="xs-intro__inner container">
			<h2 class="xs-intro__title" id="<?php echo esc_attr( $er_open_id ); ?>"><?php echo $er_txt( $er_open_t ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( '' !== $er_intro ) : ?>
				<div class="xs-intro__text"><?php echo $er_intro; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* 03–04 The journey: what it is, and its chapters. */ ?>
	<section class="xs-journey on-dark" id="journey" aria-labelledby="xs-journey-title">
		<div class="xs-journey__inner container">
			<div class="xs-journey__head">
				<?php echo $er_eye( 'journey_eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<h2 class="xs-journey__title" id="xs-journey-title"><?php echo $er_txt( $er_lbl['journey_title'] ?? (string) ( $args['preview']['title'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<?php if ( '' !== $er_jtext ) : ?>
				<div class="xs-journey__text"><?php echo $er_jtext; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
			<?php endif; ?>
			<ol class="xs-route">
				<?php foreach ( $er_steps as $er_i => $er_st ) : ?>
					<?php
					$er_name = $er_st['eyebrow'] ?: $er_st['title'];
					if ( '' === $er_name ) {
						continue; // A chapter with no name of its own (a language without the story's labels) stays off the line.
					}
					?>
					<li class="xs-route__item">
						<a href="#imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-link>
							<span class="xs-route__dot" aria-hidden="true"></span>
							<span class="xs-route__n" aria-hidden="true"><?php echo esc_html( $er_num( $er_i + 1 ) ); ?></span>
							<span class="xs-route__name"><?php echo esc_html( $er_name ); ?></span>
							<?php if ( $er_st['eyebrow'] && $er_st['title'] ) : ?>
								<span class="xs-route__sub"><?php echo esc_html( $er_st['title'] ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<?php /* 05–06 The moments. */ ?>
	<ol class="imm on-dark" aria-labelledby="xs-journey-title">
		<?php
		foreach ( $er_steps as $er_i => $er_st ) :
			$er_layout = $er_st['photo'] ? ( in_array( $er_st['layout'], [ 'reveal', 'split', 'peak', 'band', 'detail' ], true ) ? $er_st['layout'] : 'band' ) : 'text';
			$er_cls    = 'imm-step imm-step--' . $er_layout . ( 'text' === $er_layout ? ' imm-step--' . ( $er_st['tone'] ?: 'plain' ) : '' );
			// Name (serif) and, under it, the chapter's line in gold — or, without the story's labels, the photo's own title.
			$er_name = $er_st['eyebrow'] ?: $er_st['title'];
			$er_line = $er_st['eyebrow'] ? $er_st['title'] : '';
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
					<p class="imm-step__n"><?php echo esc_html( $er_num( $er_i + 1 ) ); ?></p>
					<?php if ( '' !== $er_name ) : ?>
						<h3 class="imm-step__title"><?php echo esc_html( $er_name ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $er_line ) : ?>
						<p class="imm-step__line"><?php echo esc_html( $er_line ); ?></p>
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

	<?php /* 07 What it feels like: a full-width photograph with its words over it. */ ?>
	<?php if ( $er_feel ) : ?>
		<?php $er_feel_id = (string) $er_map['feel']; ?>
		<section class="xs-feel<?php echo ! empty( $er_s['feel']['photo'] ) ? ' xs-feel--image on-dark' : ''; ?>" aria-labelledby="<?php echo esc_attr( $er_feel_id ); ?>">
			<?php if ( ! empty( $er_s['feel']['photo'] ) ) : ?>
				<figure class="xs-feel__media"><?php echo $er_pic( $er_s['feel']['photo'], $er_s['feel']['alt'], 'feel' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
			<?php endif; ?>
			<div class="xs-feel__inner container">
				<div class="xs-feel__text">
					<?php echo $er_eye( 'feel_eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h2 class="xs-h2" id="<?php echo esc_attr( $er_feel_id ); ?>"><?php echo $er_txt( $er_lbl['feel_title'] ?? $er_feel['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<div class="xs-feel__body"><?php echo $er_feel['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
					<?php echo (string) ( $args['who'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built by the caller ?>
					<?php echo $er_credit( $er_s['feel'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* 08 What to know: the plan. */ ?>
	<?php
	$er_info = [];
	foreach ( array_filter( (array) ( $args['facts'] ?? [] ) ) as $er_label => $er_value ) {
		$er_icon   = er_t( 'Duration' ) === $er_label ? 'clock' : ( er_t( 'Location' ) === $er_label ? 'pin' : ( er_t( 'Best time' ) === $er_label ? 'calendar' : 'route' ) );
		$er_info[] = [ $er_icon, (string) $er_label, esc_html( (string) $er_value ) ];
	}
	foreach ( (array) ( $er_know['items'] ?? [] ) as $er_k => $er_item ) {
		$er_info[] = [ 0 === $er_k ? 'car' : 'route', (string) ( $er_know['terms'][ $er_k ] ?? '' ), wp_kses_post( $er_item ) ];
	}
	foreach ( (array) ( $er_know['paras'] ?? [] ) as $er_para ) {
		$er_info[] = [ 'temple', (string) ( $er_lbl['know_note'] ?? '' ), wp_kses_post( $er_para ) ];
	}
	if ( $er_info || ! empty( $er_know['callout']['items'] ) ) :
		$er_know_id = (string) ( $er_map['know'] ?? 'know' );
		?>
		<section class="xs-know" aria-labelledby="<?php echo esc_attr( $er_know_id ); ?>">
			<div class="xs-know__inner container">
				<div class="xs-know__head">
					<?php echo $er_eye( 'know_eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h2 class="xs-h2" id="<?php echo esc_attr( $er_know_id ); ?>"><?php echo $er_txt( $er_lbl['know_title'] ?? ( $er_know['title'] ?? er_t( 'Good to know' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				</div>
				<div class="xs-know__body">
					<?php if ( $er_info ) : ?>
						<dl class="xs-info">
							<?php foreach ( $er_info as [ $er_icon, $er_label, $er_value ] ) : ?>
								<div class="xs-info__item">
									<?php echo er_icon( 'i-' . $er_icon, 'xs-info__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php if ( '' !== $er_label ) : ?>
										<dt><?php echo esc_html( $er_label ); ?></dt>
									<?php endif; ?>
									<dd><?php echo $er_value; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<?php if ( ! empty( $er_know['callout']['items'] ) ) : ?>
						<div class="xs-check">
							<?php if ( $er_know['callout']['title'] ) : ?>
								<h3 class="xs-check__title"><?php echo $er_txt( $er_know['callout']['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
							<?php endif; ?>
							<ul class="xs-check__list">
								<?php foreach ( $er_know['callout']['items'] as $er_item ) : ?>
									<li><?php echo er_icon( 'i-check', 'xs-check__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo wp_kses_post( $er_item ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php echo (string) ( $args['tips'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built by the caller ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* 09 Combine it with: picture links. */ ?>
	<?php
	$er_rel = array_slice( array_values( array_unique( array_map( 'intval', (array) ( $args['related'] ?? [] ) ) ) ), 0, 3 );
	if ( $er_comb || $er_rel ) :
		$er_comb_id = (string) ( $er_map['combine'] ?? 'combine' );
		?>
		<section class="xs-combine" aria-labelledby="<?php echo esc_attr( $er_comb_id ); ?>">
			<div class="xs-combine__inner container">
				<div class="xs-combine__head">
					<?php echo $er_eye( 'combine_eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h2 class="xs-h2" id="<?php echo esc_attr( $er_comb_id ); ?>"><?php echo $er_txt( (string) ( $er_comb['title'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( $er_comb ) : ?>
						<div class="xs-combine__text"><?php echo $er_comb['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
					<?php endif; ?>
				</div>
				<?php if ( $er_rel ) : ?>
					<ul class="xs-links xs-links--<?php echo count( $er_rel ); ?>">
						<?php
						foreach ( $er_rel as $er_rid ) :
							$er_type  = get_post_type( $er_rid );
							$er_thumb = get_post_thumbnail_id( $er_rid );
							$er_stock = function_exists( 'er_stock_id_for' ) ? er_stock_id_for( $er_rid ) : '';
							$er_img   = $er_thumb
								? er_img( (int) $er_thumb, 'er-card', [ 'alt' => '', 'sizes' => '(min-width: 1024px) 24vw, 90vw', 'loading' => 'lazy' ] )
								: ( $er_stock ? er_stock_img( $er_stock, '', [ 'sizes' => '(min-width: 1024px) 24vw, 90vw', 'loading' => 'lazy' ], [ 480, 720, 960 ], 0.7 ) : '' );
							$er_sub   = 'er_guide' === $er_type && function_exists( 'er_read_minutes' )
								? er_t( '{n} min read', [ 'n' => er_read_minutes( $er_rid ) ] )
								: (string) get_post_meta( $er_rid, 'er_destination' === $er_type ? '_er_region_label' : '_er_location', true );
							?>
							<li class="xs-link">
								<a href="<?php echo esc_url( get_permalink( $er_rid ) ); ?>">
									<span class="xs-link__media" aria-hidden="true"><?php echo $er_img; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by the image helpers ?></span>
									<span class="xs-link__bar">
										<span class="xs-link__text">
											<span class="xs-link__title"><?php echo esc_html( get_the_title( $er_rid ) ); ?></span>
											<?php if ( $er_sub ) : ?>
												<span class="xs-link__sub"><?php echo esc_html( $er_sub ); ?></span>
											<?php endif; ?>
										</span>
										<?php echo er_icon( 'i-arrow', 'xs-link__arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* Anything else the editor wrote, in body order. */ ?>
	<?php foreach ( $er_rest as $er_id => $er_p ) : ?>
		<section class="xs-more" aria-labelledby="<?php echo esc_attr( (string) $er_id ); ?>">
			<div class="xs-more__inner container">
				<h2 class="xs-h2" id="<?php echo esc_attr( (string) $er_id ); ?>"><?php echo $er_txt( $er_p['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<div class="prose"><?php echo $er_p['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
			</div>
		</section>
	<?php endforeach; ?>

	<?php /* 10 Questions. */ ?>
	<?php if ( $er_faq ) : ?>
		<?php $er_faq_id = (string) $er_map['faq']; ?>
		<section class="xs-faq on-dark" aria-labelledby="<?php echo esc_attr( $er_faq_id ); ?>">
			<div class="xs-faq__inner container">
				<div class="xs-faq__head">
					<?php echo $er_eye( 'faq_eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h2 class="xs-h2" id="<?php echo esc_attr( $er_faq_id ); ?>"><?php echo $er_txt( $er_faq['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				</div>
				<div class="xs-faq__list"><?php echo $er_faq['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* 11 The end: the next step over the hero's photograph (the same files, already cached). */ ?>
	<?php if ( ! empty( $args['offers'] ) || ! empty( $args['help'] ) ) : ?>
		<section class="xs-end on-dark" id="plan" aria-labelledby="xs-end-title">
			<?php
			$er_hero = (array) ( $args['hero'] ?? [] );
			if ( ! empty( $er_hero['image'] ) ) {
				echo '<div class="xs-end__media" aria-hidden="true">' . er_img( (int) $er_hero['image'], 'er-hero', [ 'loading' => 'lazy', 'sizes' => '100vw', 'alt' => '' ] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			} elseif ( ! empty( $er_hero['stock'] ) ) {
				echo '<div class="xs-end__media" aria-hidden="true">' . er_stock_picture( (string) $er_hero['stock'], '', [ 'loading' => 'lazy' ], [ 640, 960, 1280, 1600, 2000, 2560 ], 'hero' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
			<div class="xs-end__inner container">
				<?php echo $er_eye( 'cta_eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( ! empty( $args['offers'] ) ) : ?>
					<h2 class="xs-end__title" id="xs-end-title"><?php er_e( 'Book with our partners' ); ?></h2>
					<div class="xs-end__offers">
						<?php er_offer_rows( (array) $args['offers'], 'experience-offers' ); ?>
						<?php er_disclosure(); ?>
					</div>
				<?php else : ?>
					<h2 class="xs-end__title" id="xs-end-title"><?php echo $er_txt( $er_lbl['cta_title'] ?? er_t( 'Need personal help?' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php
					$er_guides = ! empty( $er_lbl['cta_guides'] ) && function_exists( 'er_has_published' ) && er_has_published( 'er_guide' ) ? get_post_type_archive_link( 'er_guide' ) : '';
					$er_help   = (string) $args['help'];
					if ( $er_guides ) {
						// The guides first (this ending's own promise), then the usual next steps.
						$er_link = '<a class="btn btn--primary" href="' . esc_url( $er_guides ) . '">' . esc_html( $er_lbl['cta_guides'] ) . ' ' . er_icon( 'i-arrow', 'icon--arrow' ) . '</a>';
						$er_help = false !== strpos( $er_help, '<div class="xs-end__actions">' )
							? str_replace( '<div class="xs-end__actions">', '<div class="xs-end__actions">' . $er_link, str_replace( 'btn btn--primary', 'btn btn--ghost', $er_help ) )
							: $er_help . '<div class="xs-end__actions">' . $er_link . '</div>';
					}
					echo $er_help; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts
					?>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</div>
