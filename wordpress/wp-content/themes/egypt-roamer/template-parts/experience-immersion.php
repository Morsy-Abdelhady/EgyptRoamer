<?php
/**
 * Experience Immersion: the experience told as a journey, between the hero and the practical detail.
 *
 *   enter (title + lede, the route at a glance) → the journey, stage by stage (before → arrival → inside →
 *   highlight → after; photographs where verified ones exist, typographic stages where none do) → what it
 *   feels like → what to know + the next step (partner offer or planner) → the full guide below.
 *
 * One system for every experience: the content changes (data/previews.json → er_preview_story()), the
 * structure does not. Below the hero by design: the hero stays the only first-view image; each stage's
 * photo carries its address in data-* and immersion.js loads it about a screen ahead (<noscript> keeps it
 * for visitors without JS). Everything reads without JavaScript and without motion: scripts only defer
 * the photos and mark the stage in view; the image transitions are CSS, and only when motion is welcome.
 *
 * @package EgyptRoamer
 * @var array $args { preview: er_preview_for(), facts: label => value, offer: offer id|0, more: anchor }
 */

defined( 'ABSPATH' ) || exit;

$er_s = (array) ( $args['preview']['story'] ?? [] );
if ( ! $er_s || empty( $er_s['stages'] ) ) {
	return;
}
$er_lang   = (string) ( $args['preview']['lang'] ?? '' );
$er_lang_a = ! $er_s['full'] && $er_lang && $er_lang !== er_lang() ? ' lang="' . esc_attr( $er_lang ) . '"' : '';
$er_steps  = $er_s['stages'];
$er_total  = count( $er_steps );
$er_num    = static fn ( int $i ): string => str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );

// Frames: 4:5 on phones and tablets (full width), a tall frame beside the text from 1024px (≈ 58vw × the
// screen height, cropped to 0.9 of its width). Widths stop near 2× the frame, as for the other crops.
$er_pic = static function ( string $photo, string $alt ): string {
	$sizes  = '(min-width: 1024px) 58vw, 100vw';
	$phone  = er_stock_srcset( $photo, [ 480, 640, 828, 1080 ], 1.25 );
	$wide   = er_stock_srcset( $photo, [ 720, 960, 1280, 1600 ], 0.9 );
	$src    = er_stock_url( $photo, 960, 0.9 );
	$source = '<source media="(max-width: 1023.98px)" %s="' . esc_attr( $phone ) . '" sizes="100vw" />';
	$real   = '<picture class="er-pic">' . sprintf( $source, 'srcset' ) . er_stock_img( $photo, $alt, [ 'sizes' => $sizes, 'loading' => 'lazy', 'decoding' => 'async' ], [ 720, 960, 1280, 1600 ], 0.9 ) . '</picture>';
	$blank  = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	return '<picture class="er-pic">' . sprintf( $source, 'data-srcset' )
		. sprintf( '<img src="%s" data-src="%s" data-srcset="%s" sizes="%s" alt="%s" decoding="async" data-defer />', $blank, esc_url( $src ), esc_attr( $wide ), esc_attr( $sizes ), esc_attr( $alt ) )
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
$er_facts = array_filter( (array) ( $args['facts'] ?? [] ) );
$er_know  = (array) ( $er_s['know'] ?? [] );
?>
<section class="imm on-dark" id="journey" aria-labelledby="imm-title" data-imm<?php echo $er_lang_a; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="imm__open container">
		<p class="eyebrow"><?php echo esc_html( $er_s['label'] ?: er_t( 'Experience preview' ) ); ?></p>
		<h2 id="imm-title" class="imm__title"><?php echo esc_html( $er_s['title'] ); ?></h2>
		<?php if ( $er_s['lede'] ) : ?>
			<p class="imm__lede"><?php echo esc_html( $er_s['lede'] ); ?></p>
		<?php endif; ?>
		<?php if ( $er_s['full'] && $er_total > 2 ) : ?>
			<ol class="imm__route" aria-label="<?php echo esc_attr( $er_s['label'] ); ?>">
				<?php foreach ( $er_steps as $er_i => $er_st ) : ?>
					<li><a href="#imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-link><span class="imm__route-n" aria-hidden="true"><?php echo esc_html( $er_num( $er_i ) ); ?></span><?php echo esc_html( $er_st['eyebrow'] ?: $er_st['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>

	<ol class="imm__steps">
		<?php foreach ( $er_steps as $er_i => $er_st ) : ?>
			<li class="imm-step <?php echo $er_st['photo'] ? 'imm-step--photo' : 'imm-step--text imm-step--' . esc_attr( $er_st['tone'] ?: 'plain' ); ?>" id="imm-<?php echo esc_attr( (string) ( $er_i + 1 ) ); ?>" data-imm-step>
				<?php if ( $er_st['photo'] ) : ?>
					<figure class="imm-step__media">
						<?php echo $er_pic( $er_st['photo'], $er_st['alt'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( $er_st['generated'] ) : ?>
							<span class="moments__flag"><?php er_e( 'Illustration, not footage of the place' ); ?></span>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
				<div class="imm-step__text">
					<p class="imm-step__eyebrow"><span class="imm-step__n"><?php echo esc_html( $er_num( $er_i ) ); ?><span class="imm-step__of"> / <?php echo esc_html( $er_num( $er_total - 1 ) ); ?></span></span><?php echo $er_st['eyebrow'] ? ' <span>' . esc_html( $er_st['eyebrow'] ) . '</span>' : ''; ?></p>
					<h3 class="imm-step__title"><?php echo esc_html( $er_st['title'] ); ?></h3>
					<?php if ( $er_st['text'] ) : ?>
						<p class="imm-step__body"><?php echo esc_html( $er_st['text'] ); ?></p>
					<?php endif; ?>
					<?php echo $er_credit( $er_st ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>

	<?php if ( ! empty( $er_s['feel']['items'] ) ) : ?>
		<div class="imm__feel container">
			<h3 class="imm__feel-title"><?php echo esc_html( $er_s['feel']['title'] ); ?></h3>
			<dl class="imm__feel-list">
				<?php foreach ( $er_s['feel']['items'] as $er_f ) : ?>
					<div><dt><?php echo esc_html( $er_f['label'] ); ?></dt><dd><?php echo esc_html( $er_f['text'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<?php if ( $er_s['feel']['note'] ) : ?>
				<p class="imm__note"><?php echo esc_html( $er_s['feel']['note'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>

<?php if ( $er_know ) : ?>
	<section class="imm-know container" aria-labelledby="imm-know-title">
		<h2 id="imm-know-title" class="imm-know__title"><?php echo esc_html( $er_know['title'] ); ?></h2>
		<dl class="imm-know__list">
			<?php foreach ( $er_facts as $er_label => $er_value ) : ?>
				<div><dt><?php echo esc_html( (string) $er_label ); ?></dt><dd><?php echo esc_html( (string) $er_value ); ?></dd></div>
			<?php endforeach; ?>
			<?php foreach ( $er_know['items'] as $er_k ) : ?>
				<div><dt><?php echo esc_html( $er_k['label'] ); ?></dt><dd><?php echo esc_html( $er_k['text'] ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
		<div class="imm-know__next">
			<?php
			$er_offer = (int) ( $args['offer'] ?? 0 );
			if ( $er_offer && function_exists( 'er_offer_data' ) ) {
				// The partner's own call to action (affiliate link, tracked as this placement), with the disclosure.
				echo er_offer_cta_html( $er_offer, [ 'placement' => 'experience-immersion', 'class' => 'btn btn--primary', 'icon' => ' ' . er_icon( 'i-arrow-ur', 'icon--arrow' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- built with esc_* in the plugin
			} elseif ( er_home( 'planner_enabled' ) ) {
				printf( '<a class="btn btn--primary" href="%s">%s %s</a>', esc_url( er_home_url() . '#planner' ), esc_html( er_t( 'Plan My Trip' ) ), er_icon( 'i-arrow', 'icon--arrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( $er_know['more'] && ! empty( $args['more'] ) ) {
				printf( '<a class="link imm-know__more" href="#%s">%s %s</a>', esc_attr( (string) $args['more'] ), esc_html( $er_know['more'] ), er_icon( 'i-arrow', 'icon--sm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
		<?php if ( $er_offer ) : ?>
			<?php er_disclosure(); ?>
		<?php endif; ?>
	</section>
<?php endif; ?>
