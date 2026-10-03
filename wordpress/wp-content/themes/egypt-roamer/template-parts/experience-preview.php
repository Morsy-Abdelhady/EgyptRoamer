<?php
/**
 * Experience preview: "moment by moment" photo story, plus an optional short video clip.
 *
 * Placed after the body's first section (what the experience is), before the practical detail. Below the
 * fold by design: every image is lazy, and a clip is only requested after the visitor presses play.
 * Without JavaScript the strip still scrolls (scroll-snap); the buttons and counter are added by moments.js.
 *
 * @package EgyptRoamer
 * @var array $args { preview: er_preview_for() }
 */

defined( 'ABSPATH' ) || exit;

$er_p = (array) ( $args['preview'] ?? [] );
if ( ! $er_p || ( empty( $er_p['moments'] ) && empty( $er_p['video'] ) ) ) {
	return;
}
$er_n      = count( $er_p['moments'] );
$er_lang   = (string) ( $er_p['lang'] ?? '' );
$er_lang_a = $er_lang && $er_lang !== er_lang() ? ' lang="' . esc_attr( $er_lang ) . '"' : '';
$er_flag   = static fn (): string => '<span class="moments__flag">' . esc_html( er_t( 'Illustration, not footage of the place' ) ) . '</span>';
// Frames: 4:5 on phones, 3:2 from 700px. Widths stop near 2× the frame (as for the other crops).
// The section sits close enough to the top that the browser's own lazy loading fetched all five photos
// with the page (+340 KB, LCP +0.9 s on a slow phone). So the photos carry their addresses in data-*
// and moments.js loads each one as it comes within reach; <noscript> keeps them for visitors without JS.
$er_pic = static function ( string $photo, string $alt ): string {
	$sizes  = '(min-width: 1200px) 36rem, (min-width: 700px) 70vw, 84vw';
	$phone  = er_stock_srcset( $photo, [ 480, 640, 828 ], 1.25 );
	$wide   = er_stock_srcset( $photo, [ 600, 900, 1200 ], 2 / 3 );
	$src    = er_stock_url( $photo, 900, 2 / 3 );
	$source = '<source media="(max-width: 699.98px)" %s="' . esc_attr( $phone ) . '" sizes="84vw" />';
	$real   = '<picture class="er-pic">' . sprintf( $source, 'srcset' ) . er_stock_img( $photo, $alt, [ 'sizes' => $sizes, 'loading' => 'lazy', 'decoding' => 'async' ], [ 600, 900, 1200 ], 2 / 3 ) . '</picture>';
	$blank  = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	return '<picture class="er-pic">' . sprintf( $source, 'data-srcset' )
		. sprintf( '<img src="%s" data-src="%s" data-srcset="%s" sizes="%s" alt="%s" decoding="async" data-defer />', $blank, esc_url( $src ), esc_attr( $wide ), esc_attr( $sizes ), esc_attr( $alt ) )
		. '</picture><noscript>' . $real . '</noscript>';
};
?>
<section class="moments" aria-labelledby="moments-title" data-moments<?php echo $er_lang_a; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<header class="moments__head">
		<p class="eyebrow"><?php er_e( 'Experience preview' ); ?></p>
		<h2 id="moments-title" class="moments__title"><?php echo esc_html( $er_p['title'] ); ?></h2>
		<?php if ( ! empty( $er_p['intro'] ) ) : ?>
			<p class="moments__intro"><?php echo esc_html( $er_p['intro'] ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( ! empty( $er_p['video'] ) ) : ?>
		<?php $er_v = $er_p['video']; ?>
		<figure class="moments__film" data-film>
			<div class="moments__film-frame">
				<button type="button" class="moments__play" data-film-play>
					<?php echo $er_v['poster'] ? $er_pic( $er_v['poster'], '' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="moments__play-chip">
						<?php echo er_icon( 'i-play', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php er_e( 'Play the video preview' ); ?></span>
						<?php if ( $er_v['duration'] ) : ?>
							<span class="moments__time"><?php echo esc_html( sprintf( '%d:%02d', intdiv( $er_v['duration'], 60 ), $er_v['duration'] % 60 ) ); ?></span>
						<?php endif; ?>
					</span>
				</button>
				<?php echo $er_v['generated'] ? $er_flag() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<template data-film-src>
					<video class="moments__video" controls muted playsinline preload="none"<?php echo $er_v['poster'] ? ' poster="' . esc_url( er_stock_url( $er_v['poster'], 1200, 9 / 16 ) ) . '"' : ''; ?>>
						<?php if ( $er_v['webm'] ) : ?>
							<source src="<?php echo esc_url( $er_v['webm'] ); ?>" type="video/webm" />
						<?php endif; ?>
						<?php if ( $er_v['mp4'] ) : ?>
							<source src="<?php echo esc_url( $er_v['mp4'] ); ?>" type="video/mp4" />
						<?php endif; ?>
						<?php if ( $er_v['captions'] ) : ?>
							<track kind="captions" src="<?php echo esc_url( $er_v['captions'] ); ?>" srclang="<?php echo esc_attr( $er_lang ?: er_lang() ); ?>" label="<?php echo esc_attr( $er_p['title'] ); ?>" default />
						<?php endif; ?>
					</video>
				</template>
			</div>
			<p class="moments__error" data-film-error role="status" hidden><?php er_e( 'The video could not be loaded.' ); ?></p>
		</figure>
	<?php endif; ?>

	<?php if ( $er_n ) : ?>
		<ol class="moments__track" tabindex="0" aria-labelledby="moments-title">
			<?php foreach ( $er_p['moments'] as $er_i => $er_m ) : ?>
				<li class="moments__item">
					<figure>
						<div class="moments__frame">
							<?php echo $er_pic( $er_m['photo'], $er_m['alt'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo $er_m['generated'] ? $er_flag() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<figcaption>
							<span class="moments__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $er_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<b class="moments__name"><?php echo esc_html( $er_m['title'] ); ?></b>
							<span class="moments__caption"><?php echo esc_html( $er_m['caption'] ); ?></span>
							<?php if ( $er_m['photographer'] ) : ?>
								<?php
								// "name / Unsplash" keeps its own direction (<bdi>), so it doesn't reorder in Arabic.
								$er_credit = str_replace( [ '{name} / Unsplash', '{name}' ], [ '<bdi>' . esc_html( $er_m['photographer'] ) . ' / Unsplash</bdi>', '<bdi>' . esc_html( $er_m['photographer'] ) . '</bdi>' ], esc_html( er_t( 'Photo: {name} / Unsplash' ) ) );
								?>
								<span class="moments__credit">
									<?php if ( $er_m['source'] ) : ?>
										<a href="<?php echo esc_url( $er_m['source'] ); ?>" rel="nofollow noopener"><?php echo $er_credit; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></a>
									<?php else : ?>
										<?php echo $er_credit; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						</figcaption>
					</figure>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( $er_n > 1 ) : ?>
			<div class="moments__nav" data-moments-nav hidden>
				<div class="moments__bars" aria-hidden="true"><?php echo str_repeat( '<span class="moments__bar"></span>', $er_n ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<span class="moments__count" data-moments-count data-template="<?php echo esc_attr( er_t( '{n} of {total}' ) ); ?>" aria-live="polite"><?php echo esc_html( er_t( '{n} of {total}', [ 'n' => 1, 'total' => $er_n ] ) ); ?></span>
				<button type="button" class="moments__btn" data-moments-prev aria-label="<?php echo esc_attr( er_t( 'Previous moment' ) ); ?>"><?php echo er_icon( 'i-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<button type="button" class="moments__btn" data-moments-next aria-label="<?php echo esc_attr( er_t( 'Next moment' ) ); ?>"><?php echo er_icon( 'i-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</section>
