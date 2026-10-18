<?php
/**
 * The trip "world" (inc/world.php). Arguments from template-parts/single-commercial.php.
 *
 * @var array $args
 */

defined( 'ABSPATH' ) || exit;

$er_id     = (int) get_the_ID();
[ $er_cls, $er_fx ] = er_world_style( $er_id );
$er_tomb   = 'tomb' === $er_fx;
$er_parts  = er_world_parts( (string) $args['body'] );
$er_offers = (array) $args['offers'];
$er_dests  = (array) $args['dests'];
$er_title  = get_the_title();
$er_photo  = (int) get_post_thumbnail_id( $er_id );
$er_stock  = er_stock_id_for( $er_id );
$er_img    = static function ( string $sizes = '100vw', string $class = '' ) use ( $er_photo, $er_stock ): string {
	$attrs = [ 'alt' => '', 'sizes' => $sizes, 'loading' => 'eager' ];
	if ( '' !== $class ) {
		$attrs['class'] = $class;
	}
	if ( $er_photo ) {
		return er_img( $er_photo, 'full', $attrs );
	}
	return '' !== $er_stock ? er_stock_img( $er_stock, '', $attrs, [ 900, 1400, 2000 ] ) : '';
};
$er_place  = $er_dests ? implode( ' · ', array_map( 'get_the_title', $er_dests ) ) : (string) get_post_meta( $er_id, '_er_location', true );
$er_len    = function_exists( 'er_ux2_offer_length' ) ? er_ux2_offer_length( $er_offers ) : '';
$er_len    = '' !== $er_len ? $er_len : (string) get_post_meta( $er_id, '_er_duration', true );
$er_chips  = array_values( array_filter( array_merge( [ $er_len ], function_exists( 'er_booking_summary' ) ? er_booking_summary( $er_offers, false ) : [] ) ) );
$er_lead   = '' !== trim( wp_strip_all_tags( $er_parts['lead'] ) ) ? $er_parts['lead'] : ( has_excerpt() ? '<p>' . esc_html( get_the_excerpt() ) . '</p>' : '' );
$er_steps  = $er_parts['steps'];
$er_glyphs = [ '𓇳', '𓈗', '𓂻', '𓁹', '𓊹', '𓋹' ];
$er_seed   = er_world_seed( $er_id );
$er_ovid   = er_world_video_for( 'opener', $er_seed );
$er_svids  = (array) er_world_video_for( 'steps', $er_seed );
$er_pvid   = er_world_video_for( 'papy', $er_seed );
$er_poster = $er_photo ? (string) wp_get_attachment_image_url( $er_photo, 'large' ) : ( '' !== $er_stock ? er_stock_url( $er_stock, 1400, 0.72 ) : '' );
?>
<div class="world <?php echo esc_attr( $er_cls ); ?>">

	<section class="wopen" id="wopen" aria-label="<?php echo esc_attr( $er_title ); ?>">
		<?php echo $er_img( '100vw', $er_tomb ? 'dim' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $er_tomb ) : ?>
			<div class="wall--dim"><?php echo er_world_wall( 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="lit" id="lit" aria-hidden="true"><?php echo $er_img( '100vw' ) . er_world_wall( 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php else : ?>
			<?php echo $er_ovid ? er_world_video( $er_ovid, 'wvid wopen__vid' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<canvas data-fx="<?php echo esc_attr( $er_fx ); ?>" aria-hidden="true"></canvas>
		<?php endif; ?>
		<div class="wshade"></div>
		<div class="wtxt">
			<?php er_breadcrumbs(); ?>
			<p class="eyebrow"><?php echo esc_html( implode( ' · ', array_filter( [ $er_place, $er_len ] ) ) ); ?></p>
			<h1><?php echo esc_html( $er_title ); ?></h1>
			<div class="lead"><?php echo wp_kses_post( $er_lead ); ?></div>
			<?php if ( $er_chips ) : ?>
				<div class="wchips"><?php foreach ( $er_chips as $c ) { echo '<span>' . esc_html( $c ) . '</span>'; } ?></div>
			<?php endif; ?>
			<div class="wacts">
				<?php if ( $er_offers ) : ?>
					<a class="btn btn--gold" href="#book"><?php er_e( 'Book this trip' ); ?></a>
				<?php endif; ?>
				<?php if ( $er_steps ) : ?>
					<a class="btn btn--ghost-light" href="#wjourney"><?php echo esc_html( $er_parts['steps_title'] ); ?> ↓</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $er_steps ) : ?>
		<section class="journey2" id="wjourney" aria-labelledby="wj-title">
			<div class="jstage">
				<?php foreach ( $er_steps as $i => $s ) : ?>
					<?php if ( isset( $er_svids[ $i ] ) ) : ?>
						<?php echo er_world_video( $er_svids[ $i ], 0 === $i ? 'on' : '', '' !== $s[2] ? $s[2] : $er_poster, 'data-j="' . (int) $i . '" data-vstep', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php elseif ( '' !== $s[2] ) : ?>
						<img class="<?php echo 0 === $i ? 'on' : ''; ?>" data-j="<?php echo (int) $i; ?>" src="<?php echo esc_url( $s[2] ); ?>" alt="" loading="lazy" />
					<?php else : ?>
						<div class="jlayer <?php echo 0 === $i ? 'on' : ''; ?> jtone--<?php echo esc_attr( [ 'warm', 'dark', 'night' ][ $i % 3 ] ); ?>" data-j="<?php echo (int) $i; ?>" aria-hidden="true"><?php echo $er_img( '100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ( ! $er_tomb ) : ?><canvas data-fx="<?php echo esc_attr( $er_fx ); ?>-j" aria-hidden="true"></canvas><?php endif; ?>
				<div class="jhead"><p class="eyebrow"><?php echo esc_html( $er_title ); ?></p><h2 id="wj-title"><?php echo esc_html( $er_parts['steps_title'] ); ?></h2></div>
				<?php foreach ( $er_svids as $i => $f ) : ?>
					<div class="vstepctl <?php echo 0 === $i ? 'on' : ''; ?>" data-j="<?php echo (int) $i; ?>"><?php echo er_world_video_controls( er_t( 'Video: {name}, Pexels', [ 'name' => (string) ( er_world_video_creators()[ $f ] ?? '' ) ] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endforeach; ?>
				<div class="path" aria-hidden="true"><?php foreach ( $er_steps as $i => $s ) { echo '<span class="' . ( 0 === $i ? 'on' : '' ) . '">' . esc_html( '' !== $s[0] ? $s[0] : str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ) . '</span>'; } ?></div>
			</div>
			<div class="jsteps">
				<?php if ( ! empty( $er_parts['steps_intro'] ) ) : ?>
					<div class="jstep jstep--intro"><article><?php echo wp_kses_post( $er_parts['steps_intro'] ); ?></article></div>
				<?php endif; ?>
				<?php foreach ( $er_steps as $i => $s ) : ?>
					<div class="jstep" data-js="<?php echo (int) $i; ?>"><article>
						<span class="n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php if ( '' !== $s[0] ) : ?><h3><?php echo esc_html( $s[0] ); ?></h3><?php endif; ?>
						<p><?php echo wp_kses_post( $s[1] ); ?></p>
						<?php echo wp_kses_post( str_replace( 'er-points__credit', 'vcred', $s[3] ) ); ?>
					</article></div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_parts['notes'] ) : $n = $er_parts['notes']; ?>
		<section class="wsec" id="<?php echo esc_attr( $n['id'] ); ?>">
			<?php echo $er_tomb ? er_world_wall( 34 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="wsec__in">
				<div><p class="eyebrow"><?php echo esc_html( $n['eyebrow'] ); ?></p><h2><?php echo esc_html( '' !== $n['title'] ? $n['title'] : $n['eyebrow'] ); ?></h2><?php echo wp_kses_post( $n['intro'] ); ?></div>
				<div class="tablets"><?php foreach ( $n['tabs'] as $k => $t ) { echo '<div class="tablet"><i aria-hidden="true">' . esc_html( $er_glyphs[ $k % count( $er_glyphs ) ] ) . '</i><span>' . wp_kses_post( $t ) . '</span></div>'; } ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php foreach ( $er_parts['rest'] as [ $rid, $rtitle, $rhtml ] ) : ?>
		<section class="wprose" id="<?php echo esc_attr( $rid ); ?>">
			<div class="wprose__in"><h2><?php echo esc_html( $rtitle ); ?></h2><?php echo wp_kses_post( $rhtml ); ?></div>
		</section>
	<?php endforeach; ?>

	<?php if ( $er_parts['papy'] ) : ?>
		<section class="papy">
			<div class="papy__in">
				<?php foreach ( $er_parts['papy'] as [ $plabel, $phtml, $pid ] ) : ?>
					<div id="<?php echo esc_attr( $pid ); ?>"><p class="eyebrow"><?php echo esc_html( $plabel ); ?></p><?php echo wp_kses_post( $phtml ); ?><?php if ( $er_pvid && $er_pvid[0] === $pid ) : ?><div class="papy__vid"><?php echo er_world_video( $er_pvid[1], 'wvid', $er_poster ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?></div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $args['panel'] ) : ?>
		<section class="choose2" id="wchoose">
			<div class="choose2__grid">
				<div class="choose2__photo"><?php echo $er_img( '(max-width: 900px) 100vw, 45vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="choose2__cap"><?php echo esc_html( $er_title ); ?></span></div>
				<div class="choose2__panel"><?php echo $args['panel']; // phpcs:ignore WordPress.Security.EscapeOutput -- er_booking_panel() output ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_parts['faq'] ) : ?>
		<section class="wfaq" id="faq">
			<div class="wfaq__in">
				<h2><?php echo esc_html( $er_parts['faq_title'] ); ?></h2>
				<?php foreach ( $er_parts['faq'] as [ $q, $a ] ) : ?>
					<details><summary><?php echo esc_html( $q ); ?></summary><?php echo wp_kses_post( $a ); ?></details>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php echo $args['after']; // phpcs:ignore WordPress.Security.EscapeOutput -- related sections, this template's own markup ?>
</div>
