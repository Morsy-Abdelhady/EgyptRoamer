<?php
/**
 * Tour / Experience / Activity — editorial first, conversion layer second.
 *
 * Order: what it is (content) → facts → who it suits / who may prefer something
 * else → practical tips → partner offers (with disclosure) → alternatives →
 * where it happens → related guides. Remove every affiliate link and the page
 * still answers the traveller's questions.
 */

defined( 'ABSPATH' ) || exit;

$er_id       = get_the_ID();
$er_type     = get_post_type();
$er_dests    = er_get_related( $er_id, '_er_destination' );
$er_offers   = er_offers_for_post( $er_id, 6 );
$er_facts    = array_filter( [
	er_t( 'Location' )      => (string) get_post_meta( $er_id, '_er_location', true ),
	er_t( 'Duration' )      => (string) get_post_meta( $er_id, '_er_duration', true ),
	er_t( 'Best time' )     => (string) get_post_meta( $er_id, '_er_best_time', true ),
	er_t( 'Starting point' ) => (string) get_post_meta( $er_id, '_er_meeting', true ),
] );
$er_who      = er_lines( $er_id, '_er_who_for' );
$er_not      = er_lines( $er_id, '_er_not_for' );
$er_tips     = er_lines( $er_id, '_er_good_to_know' );
$er_alts     = er_get_related( $er_id, '_er_alternatives' );
$er_acts     = 'er_activity' === $er_type ? [] : er_get_related( $er_id, '_er_activity' );
$er_guides   = er_get_referencing( $er_id, 'er_guide', '_er_related', 3 );
$er_hosted   = 'er_activity' === $er_type ? er_get_referencing( $er_id, [ 'er_tour', 'er_experience' ], '_er_activity', 6 ) : [];
$er_eyebrows = [ 'er_tour' => er_t( 'Tours' ), 'er_experience' => er_t( 'Experiences' ), 'er_activity' => er_t( 'Activities' ) ];

$er_meta = '';
if ( $er_dests ) {
	$er_meta .= '<span>' . er_icon( 'i-pin', 'icon--sm' ) . implode( ', ', array_map( static fn ( $d ) => '<a href="' . esc_url( get_permalink( $d ) ) . '">' . esc_html( get_the_title( $d ) ) . '</a>', $er_dests ) ) . '</span>';
}
if ( ! empty( $er_facts[ er_t( 'Duration' ) ] ) ) {
	$er_meta .= '<span>' . er_icon( 'i-clock', 'icon--sm' ) . esc_html( $er_facts[ er_t( 'Duration' ) ] ) . '</span>';
}

ob_start();
er_glance( $er_facts );
if ( $er_offers ) :
	?>
	<div class="offer-box" role="region" aria-labelledby="offers-title">
		<h2 id="offers-title" class="t-label"><?php er_e( 'Book with our partners' ); ?></h2>
		<?php er_offer_rows( $er_offers, str_replace( 'er_', '', $er_type ) . '-offers' ); ?>
		<?php er_disclosure(); ?>
	</div>
	<?php
endif;
$er_aside = trim( (string) ob_get_clean() );

er_page_hero( [
	'eyebrow' => $er_eyebrows[ $er_type ] ?? '',
	'title'   => get_the_title(),
	'intro'   => has_excerpt() ? get_the_excerpt() : '',
	'image'   => (int) get_post_thumbnail_id(),
	'stock'   => er_stock_id_for( (int) get_the_ID() ),
	'meta'    => $er_meta,
	'measure' => '' === $er_aside,
] );
$er_body = er_body();
er_section_nav( $er_body['links'], $er_body['label'], '' === $er_aside );

ob_start();
?>
<div class="prose"><?php echo $er_body['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
<?php if ( $er_who || $er_not ) : ?>
	<section class="decide" aria-labelledby="decide-title">
		<h2 id="decide-title" class="t-h3"><?php er_e( 'Is it right for you?' ); ?></h2>
		<div class="decide__cols">
			<?php if ( $er_who ) : ?>
				<div><h3 class="t-label"><?php er_e( 'Best for' ); ?></h3><?php echo er_check_list( $er_who ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
			<?php if ( $er_not ) : ?>
				<div><h3 class="t-label"><?php er_e( 'You may prefer something else if' ); ?></h3><?php echo er_check_list( $er_not ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( $er_tips ) : ?>
	<section class="tips" aria-labelledby="tips-title">
		<h2 id="tips-title" class="t-h3"><?php er_e( 'Good to know' ); ?></h2>
		<?php echo er_check_list( $er_tips ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</section>
<?php endif; ?>
<?php
$er_main = (string) ob_get_clean();
?>
<div class="page-body container">
	<?php er_layout( $er_main, $er_aside ); ?>

	<?php if ( $er_hosted ) : ?>
		<section class="related" aria-labelledby="hosted-title">
			<h2 id="hosted-title" class="t-h2"><?php er_e( 'Ways to do it' ); ?></h2>
			<?php er_card_grid( wp_list_pluck( $er_hosted, 'ID' ), 'activity-related' ); ?>
		</section>
	<?php endif; ?>

	<?php if ( $er_alts ) : ?>
		<section class="related" aria-labelledby="alts-title">
			<h2 id="alts-title" class="t-h2"><?php er_e( 'Alternatives to compare' ); ?></h2>
			<?php er_card_grid( wp_list_pluck( $er_alts, 'ID' ), 'alternatives' ); ?>
		</section>
	<?php endif; ?>

	<?php if ( $er_acts ) : ?>
		<section class="related" aria-labelledby="acts-title">
			<h2 id="acts-title" class="t-h2"><?php er_e( 'Related activities' ); ?></h2>
			<?php er_card_grid( wp_list_pluck( $er_acts, 'ID' ), 'related-activities' ); ?>
		</section>
	<?php endif; ?>

	<?php if ( $er_dests ) : ?>
		<section class="related" aria-labelledby="dest-title">
			<h2 id="dest-title" class="t-h2"><?php echo 'er_activity' === $er_type ? esc_html( er_t( 'Where to do it' ) ) : esc_html( er_t( 'Where it happens' ) ); ?></h2>
			<?php er_card_grid( wp_list_pluck( $er_dests, 'ID' ), 'destination-link' ); ?>
		</section>
	<?php endif; ?>

	<?php if ( $er_guides ) : ?>
		<section class="related" aria-labelledby="guides-title">
			<h2 id="guides-title" class="t-h2"><?php er_e( 'Plan it with our guides' ); ?></h2>
			<?php er_card_grid( wp_list_pluck( $er_guides, 'ID' ), 'guide-link' ); ?>
		</section>
	<?php endif; ?>
</div>
