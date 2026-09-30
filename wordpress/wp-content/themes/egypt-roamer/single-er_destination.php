<?php
/**
 * Destination — the hub of a topic cluster: editorial overview, facts,
 * things to do (tours/experiences/activities), guides, partner offers.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();
	$er_id     = get_the_ID();
	$er_things = er_get_referencing( $er_id, [ 'er_experience', 'er_tour' ], '_er_destination', 9 );
	$er_acts   = er_get_referencing( $er_id, 'er_activity', '_er_destination', 6 );
	$er_guides = er_get_referencing( $er_id, 'er_guide', '_er_destination', 6 );
	$er_offers = er_offers_for_post( $er_id, 4 );
	$er_tag    = (string) get_post_meta( $er_id, '_er_tagline', true );
	// No "Getting there" fact: its values are travel times, which the editorial fact policy leaves out
	// (owner decision, 2026-09-29). The meta stays for the homepage map and cards.
	$er_facts  = array_filter( [
		er_t( 'Region' )    => (string) get_post_meta( $er_id, '_er_region_label', true ),
		er_t( 'Best time' ) => (string) get_post_meta( $er_id, '_er_best_time', true ),
	] );
	$er_hl     = er_lines( $er_id, '_er_highlights' );

	$er_plan = (bool) er_home( 'planner_enabled' ) ? er_home_url() . '#planner' : '';

	// Sidebar (desktop): key facts, the first things to do, the planner. On phones the facts
	// open the page and the rest is left to the full sections below.
	ob_start();
	er_glance( $er_facts, $er_hl );
	if ( $er_things ) :
		?>
		<nav class="glance glance--things" aria-labelledby="glance-things">
			<p class="glance__label" id="glance-things"><?php echo esc_html( er_t( 'Things to do in {name}', [ 'name' => get_the_title() ] ) ); ?></p>
			<ul class="glance__list">
				<?php foreach ( array_slice( $er_things, 0, 3 ) as $er_thing ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $er_thing ) ); ?>"><?php echo esc_html( get_the_title( $er_thing ) ); ?></a><?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	endif;
	if ( $er_plan ) :
		?>
		<a href="<?php echo esc_url( $er_plan ); ?>" class="btn btn--primary btn--block glance__cta"><?php er_e( 'Plan My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php
	endif;
	$er_aside = trim( (string) ob_get_clean() );

	er_page_hero( [
		'eyebrow' => (string) get_post_meta( $er_id, '_er_region_label', true ) ?: er_t( 'Destinations' ),
		'title'   => get_the_title(),
		'intro'   => $er_tag,
		'image'   => (int) get_post_thumbnail_id(),
		'stock'   => er_stock_id_for( (int) get_the_ID() ),
		'measure' => '' === $er_aside,
	] );
	$er_body = er_body();
	er_section_nav( $er_body['links'], $er_body['label'], '' === $er_aside );
	?>
	<div class="page-body container">
		<?php er_layout( '<div class="prose">' . $er_body['html'] . '</div>', $er_aside ); ?>

		<?php if ( $er_things ) : ?>
			<section class="related" aria-labelledby="things-title">
				<h2 id="things-title" class="t-h2"><?php echo esc_html( er_t( 'Things to do in {name}', [ 'name' => get_the_title() ] ) ); ?></h2>
				<?php er_card_grid( wp_list_pluck( $er_things, 'ID' ), 'destination-things' ); ?>
			</section>
		<?php endif; ?>

		<?php if ( $er_acts ) : ?>
			<section class="related" aria-labelledby="acts-title">
				<h2 id="acts-title" class="t-h2"><?php er_e( 'Activities' ); ?></h2>
				<?php er_card_grid( wp_list_pluck( $er_acts, 'ID' ), 'destination-activities' ); ?>
			</section>
		<?php endif; ?>

		<?php if ( $er_offers ) : ?>
			<section class="related offer-box offer-box--wide" aria-labelledby="offers-title">
				<h2 id="offers-title" class="t-h2"><?php echo esc_html( er_t( 'Book {name} with our partners', [ 'name' => get_the_title() ] ) ); ?></h2>
				<?php er_offer_rows( $er_offers, 'destination-offers' ); ?>
				<?php er_disclosure(); ?>
			</section>
		<?php endif; ?>

		<?php if ( $er_guides ) : ?>
			<section class="related" aria-labelledby="guides-title">
				<h2 id="guides-title" class="t-h2"><?php echo esc_html( er_t( 'Plan your trip to {name}', [ 'name' => get_the_title() ] ) ); ?></h2>
				<?php er_card_grid( wp_list_pluck( $er_guides, 'ID' ), 'destination-guides' ); ?>
			</section>
		<?php endif; ?>

		<?php
		$er_others = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 3, 'post__not_in' => [ $er_id ], 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
		if ( $er_others ) :
			?>
			<section class="related" aria-labelledby="more-title">
				<h2 id="more-title" class="t-h2"><?php er_e( 'More of Egypt' ); ?></h2>
				<?php er_card_grid( wp_list_pluck( $er_others, 'ID' ), 'destination-more' ); ?>
			</section>
		<?php endif; ?>
	</div>
<?php endwhile; ?>
</main>
<?php
get_footer();
