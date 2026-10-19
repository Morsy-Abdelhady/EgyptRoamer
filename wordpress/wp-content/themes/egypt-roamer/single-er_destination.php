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
	// Proposal 4: the place in the guide pages' design, with its trips and hotels (template-parts/guide-p4.php).
	if ( function_exists( 'er_world_on' ) && er_world_on() ) {
		$er_pbody         = er_body();
		$er_pbody['html'] = er_card_photos( (string) $er_pbody['html'], (int) $er_id );
		get_template_part( 'template-parts/guide-p4', null, [ 'body' => $er_pbody, 'mode' => 'place' ] );
		continue;
	}
	$er_things = er_get_referencing( $er_id, [ 'er_experience', 'er_tour' ], '_er_destination', 9 );
	$er_acts   = er_get_referencing( $er_id, 'er_activity', '_er_destination', 6 );
	$er_guides = er_get_referencing( $er_id, 'er_guide', '_er_destination', 6 );
	$er_offers = er_offers_for_post( $er_id, 4 );
	$er_hotels = function_exists( 'er_hotel_offers_for_post' ) ? er_hotel_offers_for_post( $er_id, er_ux2() ? 4 : 2 ) : [];
	// New journey (step A, owner 16 Oct): the same booking panel as on trip pages, with the destination's trips and
	// hotels; it replaces the sidebar box, the in-article boxes and the box after the article.
	$er_new    = er_ux2() && ( $er_offers || $er_hotels );
	$er_tag    = (string) get_post_meta( $er_id, '_er_tagline', true );
	// No "Getting there" fact: its values are travel times, which the editorial fact policy leaves out
	// (owner decision, 2026-09-29). The meta stays for the homepage map and cards.
	$er_facts  = array_filter( [
		er_t( 'Region' )    => (string) get_post_meta( $er_id, '_er_region_label', true ),
		er_t( 'Best time' ) => (string) get_post_meta( $er_id, '_er_best_time', true ),
	] );
	$er_hl     = er_lines( $er_id, '_er_highlights' );

	// Sidebar (wide screens): the booking box first (it stays in view while reading), key facts, the first things to do.
	// It used to end in "Plan My Trip", which left the page for the homepage planner: the only booking buttons
	// were then the cards after the whole article (owner, 15 Oct). Below 1200px the box sits in the article.
	ob_start();
	if ( $er_new ) :
		// Preview: the place's trips, one row each, opening the trip page (inc/ux2-guides.php), as on guides.
		$GLOBALS['er_ux2_panel_trips'] = array_values( array_filter( wp_list_pluck( $er_things, 'ID' ), static fn ( $i ) => 'er_experience' === get_post_type( $i ) ) );
		er_booking_panel( $er_offers, $er_hotels, get_the_title(), 'destination', er_t( 'Trips and hotels in {name}', [ 'name' => get_the_title() ] ), array_values( array_filter( [ ! empty( $er_facts[ er_t( 'Best time' ) ] ) ? er_t( 'Best time' ) . ': ' . $er_facts[ er_t( 'Best time' ) ] : '' ] ) ), false );
	elseif ( $er_offers ) :
		?>
		<div class="offer-box is-wide-only" role="region" aria-labelledby="aside-book-title">
			<p class="t-label" id="aside-book-title"><?php echo esc_html( er_t( 'Book {name} with our partners', [ 'name' => get_the_title() ] ) ); ?></p>
			<?php er_offer_rows( array_slice( $er_offers, 0, 2 ), 'destination-aside' ); ?>
			<?php er_disclosure(); ?>
		</div>
		<?php
	endif;
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
	$er_aside = trim( (string) ob_get_clean() );

	er_page_hero( [
		'eyebrow' => (string) get_post_meta( $er_id, '_er_region_label', true ) ?: er_t( 'Destinations' ),
		'title'   => get_the_title(),
		'intro'   => $er_tag,
		'image'   => (int) get_post_thumbnail_id(),
		'stock'   => er_stock_id_for( (int) get_the_ID() ),
		'measure' => '' === $er_aside,
		'actions' => $er_new ? '<a class="btn btn--primary" href="#book" data-sheet>' . esc_html( er_book_label() ) . ' ' . er_icon( 'i-arrow', 'icon--arrow' ) . '</a>' : ( $er_offers ? '<a class="btn btn--primary" href="#book">' . esc_html( er_t( 'Book {name} with our partners', [ 'name' => get_the_title() ] ) ) . ' ' . er_icon( 'i-arrow', 'icon--arrow' ) . '</a>' : '' ),
	] );
	$er_body = er_body();
	er_section_nav( $er_body['links'], $er_body['label'], '' === $er_aside );
	er_book_bar(); // As on experience pages (owner, 15 Oct).
	// Below 1200px (no sidebar): the booking box inside the article, after its second section, as on guides.
	if ( $er_offers && ! $er_new ) {
		ob_start();
		?>
		<aside class="offer-box offer-box--inline is-narrow-only" aria-labelledby="inline-book-title">
			<p class="t-label" id="inline-book-title"><?php echo esc_html( er_t( 'Book {name} with our partners', [ 'name' => get_the_title() ] ) ); ?></p>
			<?php er_offer_rows( array_slice( $er_offers, 0, 2 ), 'destination-inline' ); ?>
			<?php er_disclosure(); ?>
		</aside>
		<?php
		$er_box   = (string) ob_get_clean();
		$er_first = strpos( $er_body['html'], '<section class="er-sec">' );
		$er_at    = false !== $er_first ? strpos( $er_body['html'], '<section class="er-sec">', $er_first + 1 ) : false;
		$er_at    = false !== $er_at ? strpos( $er_body['html'], '<section class="er-sec">', $er_at + 1 ) : false;
		$er_body['html'] = false !== $er_at ? substr_replace( $er_body['html'], $er_box, $er_at, 0 ) : $er_body['html'] . $er_box;
	}
	// Hotels: their own box at the end of the "Where to stay" section, where the reader picks an area
	// (else after "How long, and when to go", else at the end of the article). Kept out of the tour box.
	if ( $er_hotels && ! $er_new ) {
		ob_start();
		?>
		<aside class="offer-box offer-box--inline offer-box--hotels" aria-labelledby="hotels-book-title">
			<p class="t-label" id="hotels-book-title"><?php echo esc_html( er_t( 'Find a hotel in {name}', [ 'name' => get_the_title() ] ) ); ?></p>
			<?php er_offer_rows( $er_hotels, 'destination-hotels' ); ?>
			<?php er_disclosure(); ?>
		</aside>
		<?php
		$er_box = (string) ob_get_clean();
		$er_at  = false;
		foreach ( [ 'where-to-stay', 'when-to-go' ] as $er_anchor ) {
			$er_h = strpos( $er_body['html'], 'id="' . $er_anchor . '"' );
			if ( false !== $er_h ) {
				$er_at = strpos( $er_body['html'], '</section>', $er_h );
				break;
			}
		}
		$er_body['html'] = false !== $er_at ? substr_replace( $er_body['html'], $er_box, $er_at, 0 ) : $er_body['html'] . $er_box;
	}
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

		<?php if ( $er_offers && ! $er_new ) : ?>
			<section class="related offer-box offer-box--wide" id="book" aria-labelledby="offers-title">
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
