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

// Experience story (template-parts/experience-story.php): when the experience has one, everything below the hero is
// that one narrative, built from the same approved body. The layout below (tabs, sidebar, card sections) is not used.
$er_preview = function_exists( 'er_preview_for' ) ? er_preview_for( $er_id ) : [];
$er_story   = (array) ( $er_preview['story'] ?? [] );
if ( $er_story ) {
	$er_help_mode = function_exists( 'er_assistant_mode' ) ? er_assistant_mode() : 'off';
	$er_help_chat = 'off' !== $er_help_mode && function_exists( 'er_chat_enabled' ) && er_chat_enabled();
	$er_help      = '';
	if ( $er_help_chat ) {
		$er_help .= '<p class="xs-end__lede">' . esc_html( er_t( 'Have a question? Talk to our team.' ) ) . '</p>';
	}
	$er_btns = '';
	if ( $er_help_chat ) {
		$er_btns .= '<button type="button" class="btn btn--ghost" data-open="assistant" data-assistant-chat aria-haspopup="dialog" aria-controls="assistant">' . esc_html( er_t( 'Chat with Egypt Roamer' ) ) . '</button>';
	} elseif ( 'off' !== $er_help_mode ) {
		$er_btns .= '<button type="button" class="btn btn--ghost" data-open="assistant" aria-haspopup="dialog" aria-controls="assistant">' . er_icon( 'i-sparkle', 'icon--sm' ) . ' ' . esc_html( er_t( 'Trip assistant' ) ) . '</button>';
	}
	if ( er_home( 'planner_enabled' ) ) {
		$er_btns .= '<a class="btn btn--primary" href="' . esc_url( er_home_url() . '#planner' ) . '">' . esc_html( er_t( 'Plan My Trip' ) ) . ' ' . er_icon( 'i-arrow', 'icon--arrow' ) . '</a>';
	}
	$er_help .= $er_btns ? '<div class="xs-end__actions">' . $er_btns . '</div>' : '';

	$er_decide = '';
	if ( $er_who || $er_not ) {
		$er_decide = '<div class="decide__cols">'
			. ( $er_who ? '<div><h3 class="t-label">' . esc_html( er_t( 'Best for' ) ) . '</h3>' . er_check_list( $er_who ) . '</div>' : '' )
			. ( $er_not ? '<div><h3 class="t-label">' . esc_html( er_t( 'You may prefer something else if' ) ) . '</h3>' . er_check_list( $er_not ) . '</div>' : '' )
			. '</div>';
	}
	// Hero actions: the next step (the partner offer further down, else the planner), and the way into the journey.
	$er_jump = (string) ( $er_story['labels']['journey_cta'] ?? '' );
	$er_acts_html = $er_offers
		? '<a class="btn btn--primary" href="#plan">' . esc_html( er_t( 'Book with our partners' ) ) . ' ' . er_icon( 'i-arrow', 'icon--arrow' ) . '</a>'
		: ( er_home( 'planner_enabled' ) ? '<a class="btn btn--primary" href="' . esc_url( er_home_url() . '#planner' ) . '">' . esc_html( er_t( 'Plan My Trip' ) ) . ' ' . er_icon( 'i-arrow', 'icon--arrow' ) . '</a>' : '' );
	if ( '' !== $er_jump ) {
		$er_acts_html .= '<a class="btn btn--ghost" href="#journey">' . esc_html( $er_jump ) . '</a>';
	}
	er_page_hero( [
		'eyebrow'  => $er_eyebrows[ $er_type ] ?? '',
		'title'    => get_the_title(),
		'intro'    => has_excerpt() ? get_the_excerpt() : '',
		'image'    => (int) get_post_thumbnail_id(),
		'stock'    => er_stock_id_for( (int) get_the_ID() ),
		'meta'     => $er_meta,
		'actions'  => $er_acts_html,
		'modifier' => 'story',
	] );
	get_template_part( 'template-parts/experience-story', null, [
		'preview' => $er_preview,
		'parts'   => er_body_parts( er_body()['html'] ),
		'facts'   => $er_facts,
		'offers'  => $er_offers,
		'help'    => $er_help,
		'hero'    => [ 'image' => (int) get_post_thumbnail_id(), 'stock' => er_stock_id_for( (int) get_the_ID() ) ],
		'related' => array_merge( wp_list_pluck( $er_dests, 'ID' ), wp_list_pluck( $er_guides, 'ID' ), wp_list_pluck( $er_alts, 'ID' ), wp_list_pluck( $er_acts, 'ID' ) ),
		'who'     => $er_decide,
		'tips'    => $er_tips ? er_check_list( $er_tips ) : '',
	] );
	return;
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
else :
	// No live offer: the page used to end at the facts, with no next step. Offer the team chat (or the
	// assistant) and the trip planner, in strings every language already has.
	$er_help_mode = function_exists( 'er_assistant_mode' ) ? er_assistant_mode() : 'off';
	$er_help_chat = 'off' !== $er_help_mode && function_exists( 'er_chat_enabled' ) && er_chat_enabled();
	$er_help_plan = (bool) er_home( 'planner_enabled' );
	if ( 'off' !== $er_help_mode || $er_help_plan ) :
		?>
		<div class="offer-box offer-box--help">
			<?php if ( $er_help_chat ) : ?>
				<p><b><?php er_e( 'Need personal help?' ); ?></b> <?php er_e( 'Have a question? Talk to our team.' ); ?></p>
			<?php endif; ?>
			<div class="offer-box__actions">
				<?php if ( $er_help_chat ) : ?>
					<button type="button" class="btn btn--outline btn--sm" data-open="assistant" data-assistant-chat aria-haspopup="dialog" aria-controls="assistant"><?php er_e( 'Chat with Egypt Roamer' ); ?></button>
				<?php elseif ( 'off' !== $er_help_mode ) : ?>
					<button type="button" class="btn btn--outline btn--sm" data-open="assistant" aria-haspopup="dialog" aria-controls="assistant"><?php echo er_icon( 'i-sparkle', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php er_e( 'Trip assistant' ); ?></button>
				<?php endif; ?>
				<?php if ( $er_help_plan ) : ?>
					<a class="btn btn--primary btn--sm" href="<?php echo esc_url( er_home_url() . '#planner' ); ?>"><?php er_e( 'Plan My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	endif;
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

// Experience preview (photo strip / clip): after the first section, once the reader knows what the experience
// is, and before the practical detail. Inside .prose, so the section numbers keep counting.
if ( $er_preview ) {
	ob_start();
	get_template_part( 'template-parts/experience-preview', null, [ 'preview' => $er_preview ] );
	$er_preview_html = (string) ob_get_clean();
	$er_at           = strpos( $er_body['html'], '<section class="er-sec">', (int) strpos( $er_body['html'], '<section class="er-sec">' ) + 1 );
	$er_at           = false !== $er_at ? $er_at : strpos( $er_body['html'], '<h2' );
	$er_body['html'] = false !== $er_at ? substr_replace( $er_body['html'], $er_preview_html, $er_at, 0 ) : $er_body['html'] . $er_preview_html;
}

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
