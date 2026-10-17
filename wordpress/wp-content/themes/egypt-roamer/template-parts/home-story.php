<?php
/**
 * Proposal 4, the homepage story (inc/world.php): the hero with the story's index, then the four chapters, each
 * ending in its trips. Words and photographs are the approved scenes' own (Appearance → Homepage).
 *
 * @var array $args [ 'img' => scene image closure ] from front-page.php
 */

defined( 'ABSPATH' ) || exit;

$er_img      = $args['img'];
$er_chapters = [];
foreach ( [ 1, 2, 3, 4 ] as $er_n ) {
	$er_chapters[ $er_n ] = er_world_chapter_trips( $er_n );
}
$er_trips_url = (string) get_post_type_archive_link( 'er_experience' );
?>
<section class="hstory" id="top" aria-label="<?php echo esc_attr( er_t( 'Egypt as a journey' ) ); ?>">
	<div class="hstory__hero on-dark">
		<?php echo $er_img( 1, 'hstory__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="container hstory__grid">
			<div class="hstory__copy">
				<p class="eyebrow"><?php echo esc_html( er_home( 'hero_eyebrow' ) ); ?></p>
				<h1 class="hstory__title"><span class="t-display"><?php echo esc_html( er_home( 'hero_title' ) ); ?></span><span class="hstory__sub"><?php echo er_spaced_breaks( wp_kses( er_home( 'hero_sub' ), er_inline_kses_safe() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></h1>
				<p class="hstory__lead"><?php echo esc_html( er_home( 'hero_copy' ) . ' ' . er_home( 'hero_copy_more' ) ); ?></p>
				<div class="hstory__acts">
					<a class="btn btn--primary" href="<?php echo esc_url( $er_trips_url ); ?>"><?php er_e( 'Choose a trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<a class="btn btn--ghost" href="#ch-1"><?php er_e( 'Start the story' ); ?> <span aria-hidden="true">↓</span></a>
				</div>
				<?php er_ux2_hero_places(); ?>
			</div>
			<nav class="hstory__index" aria-label="<?php echo esc_attr( er_t( 'Journey chapters' ) ); ?>">
				<p class="hstory__index-t"><?php echo esc_html( er_t( 'The story · {n} chapters', [ 'n' => '4' ] ) ); ?></p>
				<ol>
					<?php foreach ( $er_chapters as $er_n => $er_ids ) : ?>
						<li><a href="#ch-<?php echo (int) $er_n; ?>"><b><?php echo esc_html( sprintf( '%02d', $er_n ) ); ?></b><span><?php echo esc_html( er_home( "scene{$er_n}_title" ) ); ?></span><?php if ( $er_ids ) : ?><small><?php echo esc_html( 1 === count( $er_ids ) ? er_t( '1 trip' ) : er_t( '{n} trips', [ 'n' => (string) count( $er_ids ) ] ) ); ?></small><?php endif; ?></a></li>
					<?php endforeach; ?>
				</ol>
			</nav>
		</div>
	</div>

	<?php foreach ( $er_chapters as $er_n => $er_ids ) : ?>
		<section class="ch on-dark ch--<?php echo (int) $er_n; ?>" id="ch-<?php echo (int) $er_n; ?>" aria-labelledby="ch-<?php echo (int) $er_n; ?>-t">
			<?php echo $er_img( $er_n, 'ch__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="container ch__wrap">
				<div>
					<p class="ch__n"><?php echo esc_html( sprintf( '%02d', $er_n ) . ' · ' . er_home( "scene{$er_n}_kicker" ) ); ?></p>
					<h2 id="ch-<?php echo (int) $er_n; ?>-t"><?php echo esc_html( er_home( "scene{$er_n}_title" ) ); ?></h2>
					<p class="ch__line"><?php echo esc_html( er_home( "scene{$er_n}_line" ) ); ?></p>
					<?php if ( 2 === $er_n ) : ?>
						<p class="route" aria-label="<?php echo esc_attr( er_t( 'Classic Nile cruise route from Aswan to Luxor' ) ); ?>"><span><?php er_e( 'The classic route' ); ?></span><b><?php er_e( 'Aswan' ); ?></b><i></i><b><?php er_e( 'Kom Ombo' ); ?></b><i></i><b><?php er_e( 'Edfu' ); ?></b><i></i><b><?php er_e( 'Luxor' ); ?></b></p>
					<?php endif; ?>
					<p class="ch__geo"><?php echo esc_html( er_home( "scene{$er_n}_coords" ) ); ?></p>
				</div>
				<?php if ( $er_ids ) : ?>
					<div class="ch__trips">
						<div class="ch__trips-h">
							<p><?php er_e( 'Trips in this chapter' ); ?></p>
							<strong><?php echo esc_html( 1 === count( $er_ids ) ? er_t( '1 trip' ) : er_t( '{n} trips', [ 'n' => (string) count( $er_ids ) ] ) ); ?></strong>
							<?php $er_place = er_world_chapter_place( $er_n ); ?>
							<a href="<?php echo esc_url( $er_place ? er_ux2_trip_url( [ 'destination' => $er_place, 'len' => '', 'private' => false, 'pickup' => false ] ) : $er_trips_url ); ?>"><?php echo esc_html( $er_place ? er_t( 'See all trips in {place}', [ 'place' => get_the_title( $er_place ) ] ) : er_t( 'See all trips' ) ); ?> <span aria-hidden="true">→</span></a>
						</div>
						<div class="ch__list">
							<?php foreach ( $er_ids as $er_id ) { echo er_world_mini( $er_id ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endforeach; ?>
</section>
<span id="journey-start" aria-hidden="true"></span>
