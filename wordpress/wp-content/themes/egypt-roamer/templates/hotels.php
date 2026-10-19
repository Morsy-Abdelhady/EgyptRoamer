<?php
/**
 * Template Name: Hotels
 *
 * Hotels by place (owner, 20 Oct: hotels were only inside trip pages and at the end of the homepage): the photo
 * header, then each place with its Trip.com areas (inc/world.php er_world_stay_section, tracked partner links).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="p4hotels">
	<div class="p4hero p4hero--places on-dark">
		<?php echo er_stock_img( '1761056981183-3faf66a62d6e', '', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ], [ 960, 1400, 2000 ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- the homepage's approved Luxor photo ?>
		<div class="container">
			<div class="visually-hidden"><?php er_breadcrumbs(); ?></div>
			<p class="p4hero__eyebrow"><?php er_e( 'Where to stay' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<p><?php er_e( 'Our hotel partner is Trip.com. Pick an area to see its hotels; you choose, book and pay on Trip.com.' ); ?></p>
		</div>
	</div>
	<?php echo function_exists( 'er_world_stay_section' ) ? er_world_stay_section() : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- this theme's own escaped markup ?>
</main>
<?php
get_footer();
