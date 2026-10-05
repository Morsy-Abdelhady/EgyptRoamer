<?php
/**
 * Experience story: an experience told as one walk through the place (the owner's chosen concept A, "Walk into
 * the rock"). The page moves the way the visit does:
 *
 *   the walk (hero)          the hero photograph stays on screen while the opening and the first chapters pass
 *                            over it; the light changes with them (night before dawn, then day), and at the end
 *                            the camera walks through the door into the dark
 *   inside (adjust)          the photograph starts black and the eyes adjust while the words pass over it
 *   the highlight (sun)      a ray of sunlight crosses the screen and lights the statues
 *   daylight (band)          out into the light again, photograph beside its words
 *   afterwards (rebuild)     the gaps between the blocks close as the photograph rises into place
 *   what it feels like, what to know, combine it with, questions, the end
 *
 * Every sentence is the page's own approved body, in its own language: er_body_parts() splits the body by
 * heading anchor and the story (Core er_preview_story()) says where each section goes. A section a chapter tells
 * (its `from`) is not repeated. Sections the story does not name stay before the questions, so nothing an editor
 * writes is lost. Structural labels exist only in the story's own languages.
 *
 * Motion is driven by the scroll itself (CSS scroll timelines), only when the browser has them and motion is
 * welcome. Without them the page is the same sequence, still: the hero, then each chapter as its own dark
 * section. The hero is the only first-view image; chapter photos carry their addresses in data-* and
 * immersion.js loads each about a screen ahead (<noscript> keeps them without JavaScript).
 *
 * @package EgyptRoamer
 * @var array $args { preview, parts, facts: label => value, offers: offer ids, help: next-step buttons HTML,
 *                    related: post ids, who/tips: HTML lists, hero: [ image id, stock photo id ],
 *                    hero_html: the page hero, walk_focus: [ x %, y %, width ÷ height ] of the door in the stock photo }
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
// Each chapter's place: told over the hero photograph (walk: no photo of its own), or a scene of its own.
$er_steps = [];
foreach ( $er_s['stages'] as $er_st ) {
	$er_st['html'] = $er_from( $er_st['from'] );
	if ( ! $er_st['photo'] && '' === $er_st['html'] && '' === $er_st['title'] ) {
		continue;
	}
	$er_st['layout'] = $er_st['photo']
		? ( in_array( $er_st['layout'], [ 'adjust', 'sun', 'band', 'rebuild' ], true ) ? $er_st['layout'] : 'band' )
		: 'walk';
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

// The opening section opens the walk, whole.
$er_open    = $er_part( 'opening' );
$er_open_t  = $er_s['title'] ?: (string) ( $er_open['title'] ?? '' );
$er_open_id = (string) ( $er_map['opening'] ?? 'story-title' );

// Deferred photo: [ phone ratio, phone widths, wide ratio, wide widths, wide sizes, phone sizes ].
// Full-screen scenes are cropped to the screen's shape; "rebuild" keeps a tall crop, so the photograph can tilt up.
$er_frames = [
	'adjust'  => [ 1.9, [ 480, 640, 828, 1080 ], 0.62, [ 960, 1280, 1600, 1920 ], '100vw', '100vw' ],
	'sun'     => [ 1.9, [ 480, 640, 828, 1080 ], 0.62, [ 960, 1280, 1600, 1920 ], '100vw', '100vw' ],
	'band'    => [ 1.0, [ 480, 640, 828, 1080 ], 0.95, [ 720, 960, 1280, 1600 ], '(min-width: 1024px) 58vw, 100vw', '100vw' ],
	'rebuild' => [ 1.6, [ 480, 640, 828, 1080 ], 1.6, [ 600, 800, 1000, 1200 ], '(min-width: 1024px) 46vw, 100vw', '100vw' ],
	'feel'    => [ 1.3, [ 480, 640, 828, 1080 ], 0.5, [ 960, 1280, 1600, 1920 ], '100vw', '100vw' ],
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
// A chapter's words: its number, name (serif), its line in gold, the body, the photo credit.
$er_words = static function ( array $st, int $i ) use ( $er_anchor, $er_num, $er_credit ): string {
	$name = $st['eyebrow'] ?: $st['title'];
	$line = $st['eyebrow'] ? $st['title'] : '';
	$out  = isset( $er_anchor[ $i ] ) ? '<span class="imm-step__anchor" id="' . esc_attr( $er_anchor[ $i ] ) . '"></span>' : '';
	$out .= '<p class="imm-step__n">' . esc_html( $er_num( $i + 1 ) ) . '</p>';
	$out .= '' !== $name ? '<h3 class="imm-step__title">' . esc_html( $name ) . '</h3>' : '';
	$out .= '' !== $line ? '<p class="imm-step__line">' . esc_html( $line ) . '</p>' : '';
	if ( '' !== $st['html'] ) {
		$out .= '<div class="imm-step__body">' . wp_kses_post( wpautop( $st['html'] ) ) . '</div>';
	} elseif ( $st['text'] ) {
		$out .= '<div class="imm-step__body"><p>' . esc_html( $st['text'] ) . '</p></div>';
	}
	return $out . $er_credit( $st );
};

$er_feel   = $er_part( 'feel' );
$er_know   = $er_part( 'know' );
$er_comb   = $er_part( 'combine' );
$er_faq    = $er_part( 'faq' );
$er_placed = array_merge( array_values( array_filter( array_map( static fn ( $k ) => is_string( $er_map[ $k ] ?? null ) ? $er_map[ $k ] : '', [ 'opening', 'feel', 'know', 'combine', 'faq' ] ) ) ), $er_told );
$er_rest   = array_diff_key( $er_parts, array_flip( $er_placed ) );

$er_walk  = array_filter( $er_steps, static fn ( $st ) => 'walk' === $st['layout'] );
$er_scene = array_filter( $er_steps, static fn ( $st ) => 'walk' !== $st['layout'] );
// The door the walk goes through: where it is in the stock photograph (only that photograph; a featured image
// replacing it has its own composition, and the camera then walks into the centre).
$er_focus = (array) ( $args['walk_focus'] ?? [] );
$er_focus = 3 === count( $er_focus ) && empty( $args['hero']['image'] ) ? implode( ' ', array_map( 'floatval', $er_focus ) ) : '';
?>
<div class="exp-story" data-imm>

	<div class="xw-journey on-dark">
		<?php /* Where you are: the chapters as a line beside the walk (wide screens). */ ?>
		<nav class="xw-rail" aria-labelledby="xw-rail-title">
			<div class="xw-rail__inner">
				<p class="xw-rail__title" id="xw-rail-title"><?php echo $er_txt( $er_lbl['journey_title'] ?? (string) ( $args['preview']['title'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<span class="xw-rail__line" aria-hidden="true"><span class="xw-rail__fill"></span></span>
				<ol class="xw-rail__list">
					<?php foreach ( $er_steps as $er_i => $er_st ) : ?>
						<?php
						$er_name = $er_st['eyebrow'] ?: $er_st['title'];
						if ( '' === $er_name ) {
							continue; // A chapter with no name of its own (a language without the story's labels) stays off the line.
						}
						?>
						<li><a href="#imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-link><?php echo esc_html( $er_name ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</nav>

		<?php /* The walk: the hero photograph holds while the opening and the first chapters pass over it. */ ?>
		<section class="xw-walk<?php echo $er_walk ? '' : ' xw-walk--short'; ?>" data-walk<?php echo '' !== $er_focus ? ' data-focus="' . esc_attr( $er_focus ) . '"' : ''; ?> aria-label="<?php echo esc_attr( get_the_title() ); ?>">
			<div class="xw-walk__bg">
				<?php echo (string) ( $args['hero_html'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- er_page_hero() output ?>
				<span class="xw-walk__night" aria-hidden="true"></span>
				<span class="xw-walk__dark" aria-hidden="true"></span>
			</div>
			<div class="xw-cap xw-cap--open" id="journey">
				<div class="xw-cap__panel">
					<h2 class="xw-cap__title" id="<?php echo esc_attr( $er_open_id ); ?>"><?php echo $er_txt( $er_open_t ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( ! empty( $er_open['html'] ) ) : ?>
						<div class="xw-cap__text"><?php echo $er_open['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
					<?php endif; ?>
				</div>
			</div>
			<?php foreach ( $er_walk as $er_i => $er_st ) : ?>
				<div class="xw-cap xw-cap--<?php echo esc_attr( $er_st['tone'] ?: 'day' ); ?>" id="imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-step>
					<div class="xw-cap__panel"><?php echo $er_words( $er_st, $er_i ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></div>
				</div>
			<?php endforeach; ?>
			<div class="xw-door" aria-hidden="true"></div>
		</section>

		<?php /* The scenes inside and after. */ ?>
		<?php if ( $er_scene ) : ?>
			<ol class="imm">
				<?php foreach ( $er_scene as $er_i => $er_st ) : ?>
					<li class="imm-step imm-step--<?php echo esc_attr( $er_st['layout'] ); ?>" id="imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-step>
						<figure class="imm-step__media">
							<?php echo $er_pic( $er_st['photo'], $er_st['alt'], $er_st['layout'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php if ( 'sun' === $er_st['layout'] ) : ?>
								<span class="imm-step__ray" aria-hidden="true"></span>
							<?php endif; ?>
							<?php if ( $er_st['generated'] ) : ?>
								<span class="moments__flag"><?php er_e( 'Illustration, not footage of the place' ); ?></span>
							<?php endif; ?>
						</figure>
						<div class="imm-step__text"><?php echo $er_words( $er_st, $er_i ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>

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
