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
	$er_facts  = array_filter( [
		er_t( 'Region' )        => (string) get_post_meta( $er_id, '_er_region_label', true ),
		er_t( 'Best time' )     => (string) get_post_meta( $er_id, '_er_best_time', true ),
		er_t( 'Getting there' ) => (string) get_post_meta( $er_id, '_er_map_reach', true ) ?: (string) get_post_meta( $er_id, '_er_getting_there', true ),
	] );
	$er_hl     = er_lines( $er_id, '_er_highlights' );

	er_page_hero( [
		'eyebrow' => (string) get_post_meta( $er_id, '_er_region_label', true ) ?: er_t( 'Destinations' ),
		'title'   => get_the_title(),
		'intro'   => $er_tag,
		'image'   => (int) get_post_thumbnail_id(),
		'stock'   => er_stock_id_for( (int) get_the_ID() ),
	] );
	?>
	<div class="page-body container">
		<div class="page-layout page-layout--single">
			<article class="page-main">
				<?php if ( $er_facts ) : ?>
					<dl class="facts" aria-label="<?php echo esc_attr( er_t( 'Key facts' ) ); ?>">
						<?php foreach ( $er_facts as $er_label => $er_value ) : ?>
							<div><dt><?php echo esc_html( $er_label ); ?></dt><dd><?php echo esc_html( $er_value ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
				<?php if ( $er_hl ) : ?>
					<div class="chips" aria-label="<?php echo esc_attr( er_t( 'Highlights' ) ); ?>">
						<?php foreach ( $er_hl as $er_h ) : ?>
							<span class="chip"><?php echo esc_html( $er_h ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<div class="prose"><?php the_content(); ?></div>
			</article>
		</div>

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
