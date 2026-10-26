<?php
/**
 * A Travel Guide article exactly as the prototype draws it (docs/prototypes/journey-v4, openGuide): the photo
 * opener, the sticky section bar, the article (drop cap, numbered headings, gold bullets, the callout on papyrus,
 * "See the place" photos, the trip cards where the text names a trip), the trip card pinned beside it (or the trips
 * on the route), "Ready to go?" with the partner options, and "Keep reading". Everything is the guide's own content
 * and the live offers (tracked /go/ links, the disclosure). Called from single-er_guide.php while proposal 4 is on.
 *
 * @var array $args
 */

defined( 'ABSPATH' ) || exit;

$er_id    = (int) get_the_ID();
$er_en    = function_exists( 'pll_get_post' ) ? ( (int) pll_get_post( $er_id, 'en' ) ?: $er_id ) : $er_id;
$er_slug  = (string) get_post_field( 'post_name', $er_en );
// A place page (Luxor, Cairo…) uses the same layout, with the place's trips and hotels (owner, 20 Oct).
$er_isplace = 'place' === ( $args['mode'] ?? '' );
$er_dests   = $er_isplace ? [ get_post( $er_id ) ] : er_get_related( $er_id, '_er_destination' );
$er_trips   = $er_isplace ? er_world_place_trips_any( $er_id ) : array_values( array_filter( array_map( 'intval', wp_list_pluck( er_get_related( $er_id, '_er_related' ), 'ID' ) ), static fn ( $i ) => 'publish' === get_post_status( $i ) && 'er_experience' === get_post_type( $i ) ) );
// Itineraries and whole-country guides name several trips: they show the trips on the route, not one trip.
$er_route = $er_isplace || (bool) preg_match( '/days-in-egypt|best-time|hidden-gems|day-trips/', $er_slug ) || count( $er_trips ) >= 3;
$er_plan  = (bool) preg_match( '/days-in-egypt/', $er_slug );
$er_main  = ! $er_route && $er_trips ? $er_trips[0] : 0;
$er_place = count( $er_dests ) && count( $er_dests ) <= 2 ? implode( ' & ', array_map( 'get_the_title', $er_dests ) ) : er_t( 'Egypt' );
$er_terms = $er_isplace ? false : get_the_terms( $er_id, 'er_guide_topic' );
$er_topic = $er_terms && ! is_wp_error( $er_terms ) ? $er_terms[0]->name : '';
$er_pname = get_the_title( $er_id );
$er_min   = function_exists( 'er_read_minutes' ) ? er_read_minutes( $er_id ) : 0;
$er_body  = (array) $args['body'];
$er_html  = (string) $er_body['html'];
$er_links = (array) $er_body['links'];
$er_guide = er_world_guide_index();

/** A trip's facts as the prototype's chips: length, group, pickup, ✓ Free cancellation. */
$er_trip = static function ( int $t ): array {
	$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $t, 10 ) : [];
	$len    = er_trip_length( $t, $offers );
	$facts  = array_values( array_filter( array_merge( [ $len ], er_booking_summary( $offers, false ) ) ) );
	$cancel = $offers && ! array_filter( $offers, static fn ( $o ) => empty( er_offer_facts( (int) $o )['cancel'] ) );
	$parts  = array_values( array_unique( array_filter( array_map( static fn ( $o ) => (string) er_offer_data( (int) $o )['provider'], $offers ) ) ) );
	$dests  = er_get_related( $t, '_er_destination' );
	$thumb  = (int) get_post_thumbnail_id( $t );
	$stock  = er_stock_id_for( $t );
	$world  = function_exists( 'er_world_data' ) ? er_world_data( er_world_seed( $t ) ) : [];
	return [
		'id'       => $t,
		'title'    => get_the_title( $t ),
		'url'      => get_permalink( $t ),
		'offers'   => $offers,
		'n'        => count( $offers ),
		'len'      => $len,
		'facts'    => $facts,
		'cancel'   => $cancel,
		'partners' => $parts,
		'place'    => (string) get_post_meta( $t, '_er_location', true ),
		'dest'     => $dests ? (int) $dests[0]->ID : 0,
		'img'      => $thumb ? (string) wp_get_attachment_image_url( $thumb, 'large' ) : ( '' !== $stock ? er_stock_url( $stock, 900, 0.6 ) : '' ),
		'world'    => $world,
	];
};
$er_chips = static function ( array $T ): string {
	$h = '<ul class="chips">';
	foreach ( $T['facts'] as $f ) {
		$h .= '<li>' . esc_html( $f ) . '</li>';
	}
	return $h . ( $T['cancel'] ? '<li class="fc">✓ ' . esc_html( er_t( 'Free cancellation' ) ) . '</li>' : '' ) . '</ul>';
};
$er_opts = static fn ( array $T ): string => 1 === $T['n'] ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => $T['n'] ] );
$M       = $er_main ? $er_trip( $er_main ) : null;
$er_all  = array_map( $er_trip, $er_trips );

// The article: numbered headings, the callout as a papyrus tip, the first question open.
$er_n    = 0;
$er_html = (string) preg_replace_callback( '#<h2 class="wp-block-heading" id="([^"]+)">#', static function ( $m ) use ( &$er_n ) {
	$er_n++;
	return '<h2 class="wp-block-heading" id="' . $m[1] . '"><small>' . sprintf( '%02d', $er_n ) . '</small>';
}, $er_html );
$er_glyph = $M && 'tomb' === ( $M['world']['fx'] ?? '' ) ? '𓁹' : '𓈗';
$er_html  = str_replace( 'class="wp-block-group er-callout', 'data-g="' . $er_glyph . '" class="tip wp-block-group er-callout', $er_html );
$er_html  = (string) preg_replace( '#<details class="wp-block-details#', '<details open class="wp-block-details', $er_html, 1 );
$er_faq   = false !== strpos( $er_html, 'id="faq"' );
// Where the text names a trip, its card follows that paragraph (the guide's own trip as the slim card).
foreach ( $er_all as $T ) {
	$path = (string) wp_parse_url( $T['url'], PHP_URL_PATH );
	$at   = '' !== $path ? strpos( $er_html, 'href="' . $path . '"' ) : false;
	$at   = false === $at ? strpos( $er_html, 'href="' . $T['url'] . '"' ) : $at;
	if ( false === $at ) {
		continue;
	}
	$ends = array_filter( [ strpos( $er_html, '</p>', $at ), strpos( $er_html, '</ul>', $at ) ], 'is_int' );
	if ( ! $ends ) {
		continue;
	}
	$end  = min( $ends );
	$end += '</p>' === substr( $er_html, $end, 4 ) ? 4 : 5;
	// Inside a two-column box (er-subgrid) the card was squeezed into one narrow column (Hurghada, owner 20 Oct,
	// #4): it follows the whole box instead.
	$grid = strrpos( substr( $er_html, 0, $at ), '<div class="er-subgrid' );
	if ( false !== $grid && preg_match_all( '#<div\b|</div>#', $er_html, $er_tags, PREG_OFFSET_CAPTURE, $grid ) ) {
		$depth = 0;
		foreach ( $er_tags[0] as [ $er_tag, $er_pos ] ) {
			$depth += '</div>' === $er_tag ? -1 : 1;
			if ( 0 === $depth ) {
				if ( $er_pos > $at ) {
					$end = $er_pos + 6;
				}
				break;
			}
		}
	}
	$card = $M && $T['id'] === $M['id']
		? '<a class="tcard tcard--slim" href="' . esc_url( $T['url'] ) . '"><div><small>' . esc_html( er_t( 'The trip in this guide' ) ) . '</small><h3>' . esc_html( $T['title'] ) . '</h3></div><span class="btn btn--gold btn--sm">' . esc_html( er_t( 'See the {n} options', [ 'n' => $T['n'] ] ) ) . ' →</span></a>'
		: '<a class="tcard" href="' . esc_url( $T['url'] ) . '">' . ( '' !== $T['img'] ? '<img src="' . esc_url( $T['img'] ) . '" alt="" loading="lazy" />' : '' ) . '<div><small>' . esc_html( er_t( 'The trip · {place}', [ 'place' => $T['place'] ] ) ) . '</small><h3>' . esc_html( $T['title'] ) . '</h3>' . $er_chips( $T ) . '<span class="go">' . esc_html( er_t( 'See the {n} options', [ 'n' => $T['n'] ] ) ) . ' →</span></div></a>';
	$er_html = substr_replace( $er_html, $card, $end, 0 );
}

// The opener's photo: the prototype's own for two guides, else the guide's photo.
$er_hero = [
	'valley-of-the-kings-tombs-and-tickets' => [ 'img', '1779366244195-c11f7819693c', 'KV62, the tomb of Tutankhamun, Valley of the Kings · Photo: Unsplash' ],
	'snorkelling-hurghada-or-sharm'         => [ 'video', '1616967304670-b75cf9ab4582', '', 'redsea-reef' ],
][ $er_slug ] ?? null;
$er_thumb = (int) get_post_thumbnail_id( $er_id );
$er_stock = ! $er_isplace && function_exists( 'er_ux2_guide_photo' ) && '' !== er_ux2_guide_photo( $er_id ) ? er_ux2_guide_photo( $er_id ) : er_stock_id_for( $er_id );
$er_himg  = $er_hero ? er_stock_img( $er_hero[1], '', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ], [ 720, 960, 1400, 2000 ] ) : ( $er_thumb ? er_img( $er_thumb, 'er-hero', [ 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ] ) : ( '' !== $er_stock ? er_stock_img( $er_stock, '', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ], [ 720, 960, 1400, 2000 ] ) : '' ) );
// A Wikimedia Commons photo carries the credit and licence it requires (er_commons_credit).
$er_hcred = ! $er_hero && ! $er_thumb && 0 === strpos( $er_stock, 'commons:' ) ? er_commons_credit( $er_stock ) : null;
$er_gidx  = (string) get_post_type_archive_link( $er_isplace ? 'er_destination' : 'er_guide' );
$er_pidx  = ! $er_isplace && 1 === count( $er_dests ) ? add_query_arg( 'destination', (int) $er_dests[0]->ID, $er_gidx ) : $er_gidx;
$er_src   = (int) get_queried_object_id();
$er_disc  = er_t( 'Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you.' );
?>
<div class="gd" data-lb-close="<?php echo esc_attr( er_t( 'Close' ) ); ?>" data-lb-prev="<?php echo esc_attr( er_t( 'Previous' ) ); ?>" data-lb-next="<?php echo esc_attr( er_t( 'Next' ) ); ?>">
	<section class="ahero">
		<?php echo $er_himg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $er_hero && 'video' === $er_hero[0] ) : ?>
			<?php echo er_world_video( $er_hero[3], 'wvid', er_stock_url( $er_hero[1], 1400 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
		<div class="wrap">
			<div class="visually-hidden"><?php er_breadcrumbs(); ?></div>
			<?php if ( $er_isplace ) : ?>
				<p class="crumb"><a href="<?php echo esc_url( $er_gidx ); ?>"><?php er_e( 'Places' ); ?></a><span>›</span><span><?php echo esc_html( $er_pname ); ?></span></p>
				<?php $er_reg = (string) get_post_meta( $er_id, '_er_region_label', true ); ?>
				<?php if ( '' !== $er_reg ) : ?><p class="eyebrow"><?php echo esc_html( $er_reg ); ?></p><?php endif; ?>
			<?php else : ?>
				<p class="crumb"><a href="<?php echo esc_url( $er_gidx ); ?>"><?php er_e( 'Travel Guide' ); ?></a><span>›</span><a href="<?php echo esc_url( $er_pidx ); ?>"><?php echo esc_html( $er_place ); ?></a></p>
				<?php if ( '' !== $er_topic ) : ?><p class="eyebrow"><?php echo esc_html( er_t( '{topic} guide', [ 'topic' => $er_topic ] ) ); ?></p><?php endif; ?>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php $er_lead = has_excerpt() ? get_the_excerpt() : (string) get_post_meta( $er_id, '_er_tagline', true ); ?>
			<?php if ( '' !== $er_lead ) : ?><p class="lead"><?php echo esc_html( $er_lead ); ?></p><?php endif; ?>
			<div class="ameta">
				<?php $er_best = $er_isplace ? (string) get_post_meta( $er_id, '_er_best_time', true ) : ''; ?>
				<?php if ( '' !== $er_best ) : ?><span><?php echo esc_html( er_t( 'Best time' ) . ' ' . $er_best ); ?></span><?php endif; ?>
				<?php if ( $er_isplace && $er_all ) : ?><span><?php echo esc_html( 1 === count( $er_all ) ? er_t( '1 trip' ) : er_t( '{n} trips', [ 'n' => (string) count( $er_all ) ] ) ); ?></span><?php endif; ?>
				<?php if ( $er_min ) : ?><span><?php echo esc_html( er_t( '{n} min read', [ 'n' => $er_min ] ) ); ?></span><?php endif; ?>
				<?php if ( ! $er_isplace ) : ?><span><?php echo esc_html( er_t( '{n} sections', [ 'n' => count( array_filter( $er_links, static fn ( $l ) => 'faq' !== $l[0] ) ) ] ) ); ?></span><?php endif; ?>
				<span><?php er_e( 'Sources listed' ); ?></span>
				<time class="visually-hidden" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( er_local_date() ); ?></time>
			</div>
			<?php if ( $er_isplace && $er_all ) : ?>
				<div class="acts ahero__acts"><a class="btn btn--gold" href="#book"><?php echo esc_html( 1 === count( $er_all ) ? er_t( 'See the trip' ) : er_t( 'See all {n} trips', [ 'n' => (string) count( $er_all ) ] ) ); ?> →</a></div>
			<?php endif; ?>
		</div>
		<?php if ( $er_hero && '' !== $er_hero[2] ) : ?><span class="credit"><?php echo esc_html( $er_hero[2] ); ?></span><?php endif; ?>
		<?php if ( $er_hcred ) : ?><a class="credit" href="<?php echo esc_url( $er_hcred[1] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $er_hcred[0] ); ?></a><?php endif; ?>
	</section>

	<?php if ( $er_links ) : ?>
		<nav class="secbar" aria-label="<?php echo esc_attr( er_t( 'In this guide' ) ); ?>" data-secbar>
			<button type="button" class="sb-arr sb-arr--l" aria-label="<?php echo esc_attr( er_t( 'Previous sections' ) ); ?>" data-sbarr="-1">‹</button>
			<ol>
				<?php foreach ( $er_links as [ $er_aid, $er_alabel ] ) : ?>
					<li><a href="#<?php echo esc_attr( $er_aid ); ?>"><?php echo esc_html( 'faq' === $er_aid ? er_t( 'Questions' ) : $er_alabel ); ?></a></li>
				<?php endforeach; ?>
			</ol>
			<button type="button" class="sb-arr sb-arr--r" aria-label="<?php echo esc_attr( er_t( 'More sections' ) ); ?>" data-sbarr="1">›</button>
		</nav>
	<?php endif; ?>

	<div class="abody">
		<article class="prose">
			<?php echo $er_html; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?>
			<p class="asrc"><?php er_e( 'Facts in this guide come from official and published sources, listed in our sources page. Opening hours, tickets and rules change; check on the day.' ); ?><?php $er_sp = er_trust_page( 'sources' ); ?><?php if ( $er_sp ) : ?> <a href="<?php echo esc_url( $er_sp[0] ); ?>"><?php echo esc_html( $er_sp[1] ); ?> →</a><?php endif; ?></p>
			<?php if ( $M ) : ?>
				<div class="gmore">
					<?php foreach ( array_slice( $er_all, 1 ) as $T ) : ?>
						<a class="bk__also" href="<?php echo esc_url( $T['url'] ); ?>"><small><?php er_e( 'Also in this guide' ); ?></small><b><?php echo esc_html( $T['title'] ); ?></b> →</a>
					<?php endforeach; ?>
					<?php
					$er_h = $M['dest'] && function_exists( 'er_hotel_offers_for_post' ) ? er_hotel_offers_for_post( $M['dest'], 1 ) : [];
					if ( $er_h ) :
						$er_ho = er_offer_data( (int) $er_h[0], 'guide-stay', $er_src );
						echo er_offer_cta_html( (int) $er_h[0], [ 'placement' => 'guide-stay', 'source' => $er_src, 'class' => 'bk__stay', 'label' => '', 'icon' => '<span>' . esc_html( '' !== ( $M['world']['stay'] ?? '' ) ? $M['world']['stay'] : er_t( 'Staying in {name}?', [ 'name' => get_the_title( $M['dest'] ) ] ) ) . '<small>' . esc_html( (string) $er_ho['title'] ) . '</small></span><em>' . esc_html( (string) $er_ho['provider'] ) . ' ↗</em>' ] ); // phpcs:ignore WordPress.Security.EscapeOutput
					endif;
					?>
					<p class="bk__disc"><?php echo esc_html( $er_disc ); ?></p>
				</div>
			<?php endif; ?>
		</article>
		<aside class="abox">
			<?php if ( $M ) : ?>
				<div class="bk" id="book">
					<div class="bk__img"><?php if ( '' !== $M['img'] ) : ?><img src="<?php echo esc_url( $M['img'] ); ?>" alt="" loading="lazy" /><?php endif; ?><span><?php er_e( 'The trip for this guide' ); ?></span></div>
					<div class="bk__b">
						<small><?php echo esc_html( $M['place'] ); ?></small>
						<h3><?php echo esc_html( $M['title'] ); ?></h3>
						<?php echo $er_chips( $M ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<a class="btn btn--gold" href="<?php echo esc_url( $M['url'] . '#book' ); ?>"><?php echo esc_html( er_t( 'See the {n} options', [ 'n' => $M['n'] ] ) ); ?> →</a>
						<div class="bk__save"><?php echo er_save_button( er_trip_save_data( $M['id'], $M['len'] ), 'lcf', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<?php if ( $M['partners'] ) : ?><p class="bk__p"><?php echo esc_html( er_t( 'From {partners}', [ 'partners' => implode( ' · ', $M['partners'] ) ] ) ); ?></p><?php endif; ?>
					</div>
				</div>
			<?php elseif ( $er_all ) : ?>
				<div class="bk" id="book">
					<div class="bk__b">
						<small><?php echo esc_html( $er_isplace ? er_t( 'Trips in {place}', [ 'place' => $er_pname ] ) : ( $er_plan ? er_t( 'Trips on this route' ) : er_t( 'Trips in this guide' ) ) ); ?></small>
						<h3><?php echo esc_html( $er_isplace ? ( 1 === count( $er_all ) ? er_t( '1 trip you can book' ) : er_t( '{n} trips you can book', [ 'n' => count( $er_all ) ] ) ) : ( $er_plan ? er_t( '{n} trips for this plan', [ 'n' => count( $er_all ) ] ) : ( 1 === count( $er_all ) ? er_t( '1 trip mentioned here' ) : er_t( '{n} trips mentioned here', [ 'n' => count( $er_all ) ] ) ) ) ); ?></h3>
						<ul class="rtl"><?php foreach ( $er_all as $T ) { echo er_world_guide_rcard( $T ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></ul>
						<a class="btn btn--gold" href="<?php echo esc_url( $er_isplace ? er_ux2_trip_url( [ 'destination' => $er_id, 'len' => '', 'private' => false, 'pickup' => false ] ) : (string) get_post_type_archive_link( 'er_experience' ) ); ?>"><?php echo esc_html( $er_isplace ? er_t( 'All {place} trips', [ 'place' => $er_pname ] ) : er_t( 'See all trips' ) ); ?> →</a>
						<?php $er_ph = $er_isplace && function_exists( 'er_hotel_offers_for_post' ) ? er_hotel_offers_for_post( $er_id, 6 ) : []; ?>
						<?php if ( $er_ph ) : ?>
							<p class="bk__h"><?php echo esc_html( er_t( 'Staying in {name}?', [ 'name' => $er_pname ] ) ); ?></p>
							<?php
							foreach ( $er_ph as $er_hh ) {
								$er_ho = er_offer_data( (int) $er_hh, 'destination-hotels', $er_src );
								echo er_offer_cta_html( (int) $er_hh, [ 'placement' => 'destination-hotels', 'source' => $er_src, 'class' => 'bk__stay', 'label' => '', 'icon' => '<span>' . esc_html( (string) $er_ho['title'] ) . '<small>' . esc_html( (string) $er_ho['location'] ) . '</small></span><em>' . esc_html( (string) $er_ho['provider'] ) . ' ↗</em>' ] ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						<?php endif; ?>
					</div>
				</div>
				<p class="bk__disc"><?php echo esc_html( $er_disc ); ?></p>
			<?php else : ?>
				<div class="bk">
					<div class="bk__b"><small><?php er_e( 'Plan with us' ); ?></small><h3><?php er_e( 'Find the trips for this plan' ); ?></h3><p class="bk__intro"><?php er_e( 'Tours and day trips across Egypt, each with its options side by side.' ); ?></p><a class="btn btn--gold" href="<?php echo esc_url( (string) get_post_type_archive_link( 'er_experience' ) ); ?>"><?php er_e( 'Choose a trip' ); ?> →</a></div>
				</div>
			<?php endif; ?>
		</aside>
	</div>

	<?php if ( $er_route && $er_all ) : ?>
		<section class="aend aend--tomb">
			<div class="glyphs" aria-hidden="true"><?php echo esc_html( str_repeat( '𓂀𓇳𓋹𓆣𓊹𓁹𓃭𓈗', 30 ) ); ?></div>
			<div class="wrap rt-end">
				<div><p class="eyebrow"><?php er_e( 'Ready to go?' ); ?></p><h2><?php echo esc_html( $er_isplace ? er_t( 'Trips in {place}', [ 'place' => $er_pname ] ) : ( $er_plan ? er_t( 'Trips on this route' ) : er_t( 'Trips in this guide' ) ) ); ?></h2><p><?php er_e( 'You book and pay on Viator, GetYourGuide, Klook or Trip.com. Free cancellation is shown when the partner offers it.' ); ?></p></div>
				<ul class="rtl rtl--dark"><?php foreach ( $er_all as $T ) { echo er_world_guide_rcard( $T ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></ul>
			</div>
		</section>
	<?php elseif ( $M ) : ?>
		<?php $er_tomb = 'tomb' === ( $M['world']['fx'] ?? '' ); ?>
		<section class="aend aend--<?php echo $er_tomb ? 'tomb' : 'sea'; ?>">
			<?php if ( $er_tomb ) : ?><div class="glyphs" aria-hidden="true"><?php echo esc_html( str_repeat( '𓂀𓇳𓋹𓆣𓊹𓁹𓃭𓈗', 30 ) ); ?></div><?php endif; ?>
			<div class="wrap">
				<div>
					<p class="eyebrow"><?php er_e( 'Ready to go?' ); ?></p>
					<h2><?php echo esc_html( $M['title'] ); ?></h2>
					<?php echo $er_chips( $M ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<p><?php er_e( 'You book and pay on Viator, GetYourGuide, Klook or Trip.com. Free cancellation is shown when the partner offers it.' ); ?></p>
					<div class="acts">
						<a class="btn btn--gold" href="<?php echo esc_url( $M['url'] ); ?>"><?php echo esc_html( '' !== ( $M['world']['go'] ?? '' ) ? $M['world']['go'] : er_t( 'See the trip' ) ); ?> →</a>
						<?php if ( $M['dest'] ) : ?><a class="btn btn--light" href="<?php echo esc_url( er_ux2_trip_url( [ 'destination' => $M['dest'], 'len' => '', 'private' => false, 'pickup' => false ] ) ); ?>"><?php echo esc_html( er_t( 'All {place} trips', [ 'place' => get_the_title( $M['dest'] ) ] ) ); ?></a><?php endif; ?>
					</div>
				</div>
				<div>
					<p class="aopts__t"><?php echo esc_html( er_t( '{n} ways to do it', [ 'n' => $M['n'] ] ) ); ?></p>
					<ul class="aopts">
						<?php foreach ( $M['offers'] as $er_o ) : $er_od = er_offer_data( (int) $er_o, 'guide-end', $er_src ); ?>
							<li><div><b<?php echo $er_od['title_lang'] ? ' lang="' . esc_attr( $er_od['title_lang'] ) . '" dir="ltr"' : ''; ?>><?php echo esc_html( $er_od['title'] ); ?></b><small><i><?php echo esc_html( (string) $er_od['provider'] ); ?></i><?php echo esc_html( implode( ' · ', er_offer_facts_line( er_offer_facts( (int) $er_o ) ) ) ); ?></small></div><?php echo er_offer_cta_html( (int) $er_o, [ 'placement' => 'guide-end', 'source' => $er_src, 'class' => 'btn btn--line btn--sm', 'label' => er_t( 'Book' ), 'icon' => ' ↗' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// Keep reading: other guides about the same place (prototype order).
	$er_dids  = array_map( static fn ( $d ) => (int) $d->ID, $er_dests );
	$er_place = $er_isplace ? $er_pname : $er_place;
	$er_more = [];
	foreach ( er_world_guide_order( $er_guide ) as $er_g ) {
		if ( $er_g !== $er_id && array_intersect( $er_dids, array_map( static fn ( $d ) => (int) $d->ID, er_get_related( $er_g, '_er_destination' ) ) ) ) {
			$er_more[] = $er_g;
		}
		if ( count( $er_more ) >= 3 ) {
			break;
		}
	}
	if ( $er_more ) :
		?>
		<section class="amore">
			<div class="wrap">
				<p class="eyebrow"><?php er_e( 'Keep reading' ); ?></p>
				<h2><?php echo esc_html( er_t( 'More on {place}', [ 'place' => $er_place ] ) ); ?></h2>
				<div class="amore__g">
					<?php
					foreach ( $er_more as $er_g ) :
						$er_gt = (int) get_post_thumbnail_id( $er_g );
						$er_gs = function_exists( 'er_ux2_guide_photo' ) && '' !== er_ux2_guide_photo( $er_g ) ? er_ux2_guide_photo( $er_g ) : er_stock_id_for( $er_g );
						$er_gi = $er_gt ? (string) wp_get_attachment_image_url( $er_gt, 'large' ) : ( '' !== $er_gs ? er_stock_url( $er_gs, 700, 0.55 ) : '' );
						?>
						<a class="gcard" href="<?php echo esc_url( get_permalink( $er_g ) ); ?>"><?php if ( '' !== $er_gi ) : ?><img src="<?php echo esc_url( $er_gi ); ?>" alt="" loading="lazy" /><?php endif; ?><div><small><?php echo esc_html( er_t( 'Guide' ) . ' · ' . er_t( '{n} min read', [ 'n' => er_read_minutes( $er_g ) ] ) ); ?></small><h3><?php echo esc_html( get_the_title( $er_g ) ); ?></h3><p><?php echo esc_html( get_the_excerpt( $er_g ) ); ?></p></div></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $M ) : ?>
		<div class="mbar" data-mbar data-bookbar><div><?php if ( ! empty( $M['cancel'] ) ) : ?><small>✓ <?php er_e( 'Free cancellation' ); ?></small><?php endif; ?><b><?php echo esc_html( $M['title'] ); ?></b></div><a class="btn btn--gold btn--sm" href="<?php echo esc_url( $M['url'] . '#book' ); ?>"><?php echo esc_html( er_t( 'See {n} options', [ 'n' => $M['n'] ] ) ); ?></a></div>
	<?php elseif ( $er_route && $er_all ) : ?>
		<div class="mbar" data-mbar data-bookbar><div><b><?php echo esc_html( $er_isplace ? er_t( 'Trips in {place}', [ 'place' => $er_pname ] ) : er_t( '{n} trips on this route', [ 'n' => count( $er_all ) ] ) ); ?></b></div><a class="btn btn--gold btn--sm" href="#book"><?php er_e( 'See the trips' ); ?></a></div>
	<?php endif; ?>
</div>
