<?php
/**
 * The trip "world" exactly as the prototype draws it (docs/prototypes/journey-v4, renderWorld): the opener, the day
 * step by step, the section on tablets, the papyrus, the questions, "Choose your way in" with the partner options
 * (tracked links and the disclosure), "More trips in {place}" and "Read before you go". The words and pictures are the
 * prototype's per trip (inc/world-data.php); the options, facts and hotels are the live offers.
 * Arguments from template-parts/single-commercial.php.
 *
 * @var array $args
 */

defined( 'ABSPATH' ) || exit;

$er_id     = (int) get_the_ID();
$W         = (array) $args['world'];
$er_tomb   = 'tomb' === $W['fx'];
$er_title  = get_the_title();
$er_offers = array_map( 'intval', (array) $args['offers'] );
$er_hotels = array_map( 'intval', (array) $args['hotels'] );
$er_facts  = static fn ( int $o ): array => function_exists( 'er_offer_facts' ) ? er_offer_facts( $o ) : [];
// The editors' pick first, as in the booking panel.
$er_pick = 0;
foreach ( $er_offers as $er_o ) {
	if ( ! empty( $er_facts( $er_o )['pick'] ) ) {
		$er_pick = $er_o;
		break;
	}
}
if ( $er_pick ) {
	$er_offers = array_merge( [ $er_pick ], array_values( array_diff( $er_offers, [ $er_pick ] ) ) );
}
$er_source   = $er_id;
$er_n        = count( $er_offers );
$er_partners = count( array_unique( array_filter( array_map( static fn ( $o ) => (int) get_post_meta( $o, '_er_provider', true ), $er_offers ) ) ) );
$er_len      = er_trip_length( $er_id, $er_offers );
$er_summary  = er_booking_summary( $er_offers, false );
$er_pickups  = [ er_t( 'Pickup offered' ), er_t( 'Pickup on some options' ) ];
$er_cancel   = $er_offers && ! array_filter( $er_offers, static fn ( $o ) => empty( $er_facts( $o )['cancel'] ) );
$er_tokens   = [
	'{length}'  => $er_len,
	'{options}' => $er_n ? ( $er_partners > 1 ? er_t( '{n} options from {p} partners', [ 'n' => $er_n, 'p' => $er_partners ] ) : ( 1 === $er_n ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => $er_n ] ) ) ) : '',
	'{group}'   => implode( '', array_diff( $er_summary, $er_pickups ) ),
	'{pickup}'  => implode( '', array_intersect( $er_summary, $er_pickups ) ),
	'{cancel}'  => $er_cancel ? er_t( 'Free cancellation on every option' ) : '',
	'{leave}'   => er_trip_leaves_from( $er_offers ),
];
$er_fill = static fn ( array $list ): array => array_values( array_filter( array_map( static fn ( $c ) => $er_tokens[ $c ] ?? $c, $list ), 'strlen' ) );
$er_chips = $er_fill( (array) $W['chips'] );
$er_sum   = $er_fill( (array) $W['sum'] );

/** A prototype photo: [ 'u' => Unsplash id ] or [ 'commons' => file, 'page' => file page ]. */
$er_src = static function ( ?array $m, int $w, float $ratio = 0.0 ): string {
	if ( ! $m ) {
		return '';
	}
	if ( ! empty( $m['u'] ) ) {
		return er_stock_url( (string) $m['u'], $w, $ratio );
	}
	return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode( (string) $m['commons'] ) . '?width=' . $w;
};
$er_pimg = static function ( ?array $m, string $class = '', string $sizes = '100vw', bool $eager = false ) use ( $er_src ): string {
	if ( ! $m ) {
		return '';
	}
	$srcset = implode( ', ', array_map( static fn ( $w ) => $er_src( $m, $w ) . ' ' . $w . 'w', [ 800, 1400, 2000 ] ) );
	return '<img' . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . ' src="' . esc_url( $er_src( $m, 1400 ) ) . '" srcset="' . esc_attr( $srcset ) . '" sizes="' . esc_attr( $sizes ) . '" alt="" ' . ( $eager ? 'fetchpriority="high"' : 'loading="lazy"' ) . ' decoding="async" />';
};
$er_photo  = (array) $W['photo'];
// The opener and the journey's first stop showed the same photo, one under the other (food: the koshari plate,
// owner 20 Oct, #6). The opener takes the trip's own card photo instead (the prototype's food photo).
$er_open_alt = [ 'food' => [ 'u' => '1661994215679-cde7c2c5c060' ] ];
if ( isset( $er_open_alt[ $W['key'] ] ) && wp_json_encode( $er_photo ) === wp_json_encode( $W['steps'][0]['img'] ?? null ) ) {
	$er_photo = $er_open_alt[ $W['key'] ];
}
$er_poster = $er_src( $er_photo, 1400 );
$er_save   = er_trip_save_data( $er_id, $er_len );
$er_glyphs = static fn ( string $g ): string => esc_html( $g );
$er_ep     = explode( ' · ', (string) $W['eyebrow'] );
?>
<div class="world p4w <?php echo esc_attr( $W['cls'] ); ?>">
	<div class="visually-hidden"><?php er_breadcrumbs(); ?></div>

	<section class="wopen" id="wopen" aria-labelledby="wopen-t">
		<?php echo $er_pimg( $er_photo, $er_tomb ? 'dim' : '', '100vw', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( '' !== $W['video'] ) : ?>
			<?php echo er_world_video( (string) $W['video'], 'wvid wopen__vid', $er_poster, '', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
		<?php if ( $er_tomb ) : ?>
			<div class="wall--dim"><?php echo er_world_wall( 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="lit" id="lit" aria-hidden="true"><?php echo $er_pimg( $er_photo, '', '100vw', true ) . er_world_wall( 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php else : ?>
			<canvas data-fx="<?php echo esc_attr( $W['fx'] ); ?>" aria-hidden="true"></canvas>
		<?php endif; ?>
		<div class="wshade"></div>
		<?php if ( ! empty( $er_photo['commons'] ) && '' === $W['video'] ) : $er_oc = er_commons_credit( 'commons:' . $er_photo['commons'] ); ?>
			<a class="wocred" href="<?php echo esc_url( $er_oc[1] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $er_oc[0] ); ?></a>
		<?php endif; ?>
		<?php if ( '' !== $W['video'] ) : ?>
			<?php echo er_world_video_controls( er_t( 'Video: {name}, Pexels', [ 'name' => (string) ( er_world_video_creators()[ $W['video'] ] ?? '' ) ] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
		<?php if ( $er_tomb ) : ?>
			<p class="hint" aria-hidden="true">✦ <?php er_e( 'Move your torch across the wall' ); ?></p>
		<?php endif; ?>
		<div class="wtxt">
			<p class="eyebrow"><?php foreach ( $er_ep as $er_i => $er_p ) { echo '<span class="ep' . ( $er_i > 1 ? ' ep--x' : '' ) . '">' . ( $er_i ? ' · ' : '' ) . esc_html( $er_p ) . '</span>'; } ?></p>
			<h1 id="wopen-t"><?php echo esc_html( $er_title ); ?></h1>
			<p class="lead"><?php echo esc_html( $W['lead'] ); ?></p>
			<?php if ( '' !== $W['quote'] ) : ?><p class="wq"><?php echo esc_html( $W['quote'] ); ?></p><?php endif; ?>
			<?php if ( $er_chips ) : ?><div class="wchips"><?php foreach ( $er_chips as $c ) { echo '<span>' . esc_html( $c ) . '</span>'; } ?></div><?php endif; ?>
			<div class="wacts">
				<?php if ( $er_offers ) : ?><a class="btn btn--gold" href="#book"><?php er_e( 'Book this trip' ); ?></a><?php endif; ?>
				<?php echo er_save_button( $er_save, 'lcf', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<a class="btn btn--ghost-light" href="#wjourney"><?php echo esc_html( $W['go'] ); ?> ↓</a>
			</div>
		</div>
	</section>

	<section class="journey2" id="wjourney" aria-labelledby="wj-title">
		<div class="jstage">
			<?php foreach ( $W['steps'] as $i => $s ) : ?>
				<?php if ( '' !== $s['video'] ) : ?>
					<?php echo er_world_video( (string) $s['video'], 0 === $i ? 'jv on' : 'jv', $er_poster, 'data-j="' . (int) $i . '" data-vstep', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php elseif ( $s['img'] ) : ?>
					<?php echo str_replace( '<img ', '<img data-j="' . (int) $i . '" ', $er_pimg( $s['img'], 0 === $i ? 'on' : '', '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php elseif ( $s['wall'] ) : ?>
					<div class="jlayer <?php echo 0 === $i ? 'on' : ''; ?>" data-j="<?php echo (int) $i; ?>" style="background:#0c0a07"><?php echo str_replace( 'class="wall"', 'class="wall" style="opacity:.25"', er_world_wall( 30 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php else : ?>
					<div class="jlayer jtone jtone--<?php echo esc_attr( $s['tone'] ); ?> <?php echo 0 === $i ? 'on' : ''; ?>" data-j="<?php echo (int) $i; ?>"><?php echo $er_tomb ? str_replace( 'class="wall"', 'class="wall" style="opacity:.16"', er_world_wall( 30 ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( ! $er_tomb ) : ?><canvas data-fx="<?php echo esc_attr( $W['fx'] ); ?>-j" aria-hidden="true"></canvas><?php endif; ?>
			<div class="jhead"><p class="eyebrow"><?php echo esc_html( $W['j_eyebrow'] ); ?></p><h2 id="wj-title"><?php echo esc_html( $W['j_title'] ); ?></h2><a class="skip" href="#book"><?php er_e( 'Skip to booking' ); ?> ↓</a></div>
			<?php foreach ( $W['steps'] as $i => $s ) : ?>
				<?php if ( '' !== $s['video'] ) : ?>
					<div class="vstepctl <?php echo 0 === $i ? 'on' : ''; ?>" data-j="<?php echo (int) $i; ?>"><?php echo er_world_video_controls( er_t( 'Video: {name}, Pexels', [ 'name' => (string) ( er_world_video_creators()[ $s['video'] ] ?? '' ) ] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="path"><?php foreach ( $W['steps'] as $i => $s ) { echo '<button type="button" class="' . ( 0 === $i ? 'on' : '' ) . '" data-pjump="' . (int) $i . '">' . esc_html( $s['t'] ) . '</button>'; } ?></div>
		</div>
		<div class="jsteps">
			<?php foreach ( $W['steps'] as $i => $s ) : ?>
				<div class="jstep" data-js="<?php echo (int) $i; ?>"><article>
					<span class="n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3><?php echo esc_html( $s['t'] ); ?></h3>
					<p><?php echo esc_html( $s['p'] ); ?></p>
					<?php if ( '' !== $s['why'] ) : ?><p class="why"><?php echo esc_html( $s['why'] ); ?></p><?php endif; ?>
					<?php if ( '' !== $s['credit'] ) : ?><p class="vcred"><?php echo $s['img'] && ! empty( $s['img']['page'] ) ? '<a href="' . esc_url( $s['img']['page'] ) . '" target="_blank" rel="noopener">' . esc_html( $s['credit'] ) . '</a>' : esc_html( $s['credit'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>
				</article></div>
			<?php endforeach; ?>
		</div>
	</section>

	<?php $S = $W['sec']; ?>
	<section class="wsec <?php echo $S['night'] ? 'wsec--night' : ''; ?>">
		<?php echo $S['night'] ? '<canvas data-fx="stars" aria-hidden="true"></canvas>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo $S['wall'] || $er_tomb ? er_world_wall( 34 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="wsec__in">
			<div><p class="eyebrow"><?php echo esc_html( $S['eyebrow'] ); ?></p><h2><?php echo esc_html( $S['title'] ); ?></h2><?php foreach ( $S['ps'] as $p ) { echo '<p>' . esc_html( $p ) . '</p>'; } ?></div>
			<div class="tablets"><?php foreach ( $S['tabs'] as [ $g, $b, $t ] ) { echo '<div class="tablet"><i aria-hidden="true">' . $er_glyphs( $g ) . '</i><span>' . ( '' !== $b ? '<b>' . esc_html( $b ) . '</b>' : '' ) . esc_html( $t ) . '</span></div>'; } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
	</section>

	<?php
	// "Who it suits" and "Combine it with": with a photo, both texts stand on one side and the photo beside them at
	// their full height; the short text no longer sits over an empty half (owner, 20 Oct, #8).
	$er_pmedia = '';
	$er_ptxt   = '';
	foreach ( $W['papy'] as [ $e, $h, $p, $m ] ) {
		$er_ptxt .= '<div><p class="eyebrow">' . esc_html( $e ) . '</p><h2>' . esc_html( $h ) . '</h2><p>' . esc_html( $p ) . '</p></div>';
		if ( $m && ! empty( $m['img'] ) ) {
			$er_pmedia .= '<div class="papy__vid">' . $er_pimg( $m['img'], 'papy__img', '(max-width: 900px) 100vw, 640px' ) . ( '' !== $m['cap'] ? '<small class="papy__cap">' . esc_html( $m['cap'] ) . '</small>' : '' ) . '</div>';
		} elseif ( $m && ! empty( $m['video'] ) ) {
			$er_pmedia .= '<div class="papy__vid">' . $er_pimg( $er_photo, 'papy__img', '(max-width: 900px) 100vw, 640px' ) . er_world_video( (string) $m['video'], 'wvid', $er_poster ) . '<small class="papy__cap">' . esc_html( er_t( 'Karnak Temple, Luxor' ) . ' · ' . er_t( 'Video: {name}, Pexels', [ 'name' => (string) ( er_world_video_creators()[ $m['video'] ] ?? '' ) ] ) ) . '</small></div>';
		}
	}
	?>
	<section class="papy">
		<?php if ( '' !== $er_pmedia ) : ?>
			<div class="papy__in papy__in--media"><div class="papy__txt"><?php echo $er_ptxt; // phpcs:ignore WordPress.Security.EscapeOutput ?></div><div class="papy__media"><?php echo $er_pmedia; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
		<?php else : ?>
			<div class="papy__in"><?php echo $er_ptxt; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</section>

	<?php if ( $W['faq'] ) : ?>
		<section class="wfaq" id="faq">
			<div class="wfaq__in">
				<p class="eyebrow"><?php er_e( 'Questions before you book' ); ?></p>
				<h2><?php er_e( 'Frequently asked questions' ); ?></h2>
				<?php foreach ( $W['faq'] as $er_i => [ $q, $a ] ) : ?>
					<details<?php echo 0 === $er_i ? ' open' : ''; ?>><summary><?php echo esc_html( $q ); ?></summary><p><?php echo esc_html( $a ); ?></p></details>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_offers || $er_hotels ) : ?>
		<section class="choose2" id="wchoose" aria-labelledby="wchoose-t">
			<div class="choose2__grid">
				<div class="choose2__photo"><?php echo $er_pimg( $er_photo, '', '(max-width: 900px) 100vw, 45vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="choose2__cap"><?php echo esc_html( $er_title ); ?></span></div>
				<div class="choose2__b" id="book">
					<p class="eyebrow"><?php er_e( 'Book this trip' ); ?></p>
					<h2 id="wchoose-t"><?php er_e( 'Choose your way in' ); ?></h2>
					<?php if ( $er_sum ) : ?><div class="wsum"><?php foreach ( $er_sum as $c ) { echo '<span>' . esc_html( $c ) . '</span>'; } ?></div><?php endif; ?>
					<?php
					if ( $er_n > 1 ) :
						$er_rows = [
							[ er_t( 'From' ), static function ( array $f, string $p = '', int $o = 0 ): array {
								$l = er_offer_live( $o );
								return isset( $l['price'] ) ? [ er_money( $l['price'], $l['currency'] ), '' ] : [ er_t( 'See {partner}’s page', [ 'partner' => $p ] ), 'no' ];
							} ],
							[ er_t( 'Rating' ), static function ( array $f, string $p = '', int $o = 0 ): array {
								$l = er_offer_live( $o );
								return isset( $l['rating'] ) ? [ '★ ' . number_format_i18n( $l['rating'], 1 ) . ' (' . number_format_i18n( $l['reviews'] ) . ')', 'yes' ] : [ er_t( 'See {partner}’s page', [ 'partner' => $p ] ), 'no' ];
							} ],
							[ er_t( 'Private' ), static function ( array $f ): array {
								$g = (string) ( $f['group'] ?? '' );
								return 'private' === $g ? [ er_t( 'Yes' ), 'yes' ] : ( 'small' === $g ? [ er_t( 'Small group' ), 'yes' ] : [ er_t( 'Not stated' ), 'no' ] );
							} ],
							[ er_t( 'Length' ), static function ( array $f ): array {
								$l = array_values( array_filter( er_offer_facts_line( array_intersect_key( $f, [ 'duration' => 1 ] ) ) ) );
								return $l ? [ $l[0], '' ] : [ er_t( 'Not stated' ), 'no' ];
							} ],
							[ er_t( 'Pickup' ), static fn ( array $f ): array => ! empty( $f['pickup'] ) ? [ er_t( 'Yes' ), 'yes' ] : [ er_t( 'Not stated' ), 'no' ] ],
						];
						$er_inc = array_merge( ...array_map( static fn ( $o ) => array_keys( (array) ( $er_facts( $o )['includes'] ?? [] ) ), $er_offers ) );
						if ( in_array( 'abu', $er_inc, true ) ) {
							$er_rows[] = [ er_t( 'Abu Simbel' ), static fn ( array $f ): array => ! empty( $f['includes']['abu'] ) ? [ er_t( 'Included' ), 'yes' ] : [ er_t( 'Extra' ), '' ] ];
						}
						if ( in_array( 'balloon', $er_inc, true ) ) {
							$er_rows[] = [ er_t( 'Balloon flight' ), static fn ( array $f ): array => ! empty( $f['includes']['balloon'] ) ? [ er_t( 'Included' ), 'yes' ] : [ er_t( 'Extra' ), '' ] ];
						}
						if ( in_array( 'fees', $er_inc, true ) ) {
							$er_rows[] = [ er_t( 'Entrance fees' ), static fn ( array $f ): array => 'included' === ( $f['includes']['fees'] ?? '' ) ? [ er_t( 'Included' ), 'yes' ] : ( 'local' === ( $f['includes']['fees'] ?? '' ) ? [ er_t( 'Paid locally' ), '' ] : [ er_t( 'Extra' ), '' ] ) ];
						}
						$er_rows[] = [ er_t( 'Free cancellation' ), static fn ( array $f, string $p = '' ): array => ! empty( $f['cancel'] ) ? [ er_t( 'Yes, up to 24 h before' ), 'yes' ] : [ er_t( 'See {partner}’s page', [ 'partner' => $p ] ), 'no' ] ];
						?>
						<button type="button" class="wcmp" id="wcmp" aria-expanded="false" aria-controls="wtbl" data-l-show="<?php echo esc_attr( er_t( 'Compare side by side' ) ); ?>" data-l-hide="<?php echo esc_attr( er_t( 'Hide comparison' ) ); ?>"><?php er_e( 'Compare side by side' ); ?></button>
						<div class="wtbl__wrap"><table class="wtbl" id="wtbl" hidden>
							<thead><tr><th></th>
								<?php foreach ( $er_offers as $o ) : $od = er_offer_data( $o ); $nm = (string) $od['title']; ?>
									<th<?php echo $od['title_lang'] ? ' lang="' . esc_attr( $od['title_lang'] ) . '"' : ''; ?>><?php echo esc_html( mb_strlen( $nm ) > 38 ? mb_substr( $nm, 0, 36 ) . '…' : $nm ); ?><br><small><?php echo esc_html( (string) $od['provider'] ); ?></small></th>
								<?php endforeach; ?>
							</tr></thead>
							<tbody>
								<?php foreach ( $er_rows as [ $er_k, $er_fn ] ) : ?>
									<tr><td><?php echo esc_html( $er_k ); ?></td><?php foreach ( $er_offers as $o ) { [ $v, $c ] = $er_fn( $er_facts( $o ), (string) er_offer_data( $o )['provider'], $o ); echo '<td' . ( '' !== $c ? ' class="' . esc_attr( $c ) . '"' : '' ) . '>' . esc_html( $v ) . '</td>'; } ?></tr>
								<?php endforeach; ?>
							</tbody>
						</table></div>
					<?php endif; ?>
					<?php
					foreach ( $er_offers as $o ) :
						$od   = er_offer_data( $o, 'experience-offers', $er_source );
						$slug = $od['provider_id'] ? (string) get_post_field( 'post_name', (int) $od['provider_id'] ) : '';
						$is   = $o === $er_pick;
						$why  = $is ? ( er_pick_reason( $er_pick, $er_offers ) ?: (string) $W['pick_why'] ) : '';
						$gold = $is || ( ! $er_pick && $o === $er_offers[0] );
						$er_ph = er_offer_api_photo( (int) $o );
						?>
						<div class="wopt<?php echo $is ? ' wopt--pick' : ''; ?><?php echo $er_ph ? ' wopt--ph' : ''; ?>">
							<?php if ( $er_ph ) : ?><img class="wopt__ph" src="<?php echo esc_url( $er_ph[0] ); ?>" alt="" width="76" height="76" loading="lazy" decoding="async" /><?php endif; ?>
							<div>
								<?php if ( $is ) : ?><span class="pk">✓ <?php er_e( 'Our pick' ); ?><?php if ( '' !== $why ) : ?><span class="why"><?php echo esc_html( $why ); ?></span><?php endif; ?></span><?php endif; ?>
								<h3<?php echo $od['title_lang'] ? ' lang="' . esc_attr( $od['title_lang'] ) . '" dir="ltr"' : ''; ?>><?php echo esc_html( $od['title'] ); ?></h3>
								<div class="facts"><?php echo er_partner_chip( $slug, (string) $od['provider'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php $er_lc = er_offer_live_chips( $o ); foreach ( $er_lc as $er_k => $er_v ) { echo '<span class="live live--' . esc_attr( $er_k ) . '">' . esc_html( $er_v ) . '</span>'; } ?><?php foreach ( er_offer_facts_line( $er_facts( $o ) ) as $b ) { echo '<span>' . esc_html( $b ) . '</span>'; } ?><?php if ( ! empty( $er_facts( $o )['cancel'] ) ) : ?><span class="fc">✓ <?php er_e( 'Free cancellation' ); ?></span><?php endif; ?></div>
								<?php if ( $er_ph && '' !== $er_ph[1] ) : ?><small class="wopt__credit"><bdi><?php echo esc_html( $er_ph[1] ); ?></bdi></small><?php endif; ?>
							</div>
							<?php echo er_offer_cta_html( $o, [ 'placement' => 'experience-offers', 'source' => $er_source, 'class' => 'btn btn--sm ' . ( $gold ? 'btn--gold' : 'btn--line' ), 'label' => er_t( 'Book' ), 'icon' => ' ↗' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php endforeach; ?>
					<?php if ( array_filter( array_map( 'er_offer_live', $er_offers ) ) ) : ?>
						<p class="wlive"><?php er_e( 'Prices and ratings as the partner states them today. The final price depends on your date and group; you see it on the partner’s page before you pay.' ); ?></p>
					<?php endif; ?>
					<?php if ( $er_hotels ) : ?>
						<div class="wstay">
							<h3><?php echo esc_html( '' !== $W['stay'] ? $W['stay'] : er_t( 'Staying in {name}?', [ 'name' => (string) $args['stay_name'] ] ) ); ?></h3>
							<?php foreach ( $er_hotels as $o ) : $od = er_offer_data( $o, 'experience-hotels', $er_source ); $slug = $od['provider_id'] ? (string) get_post_field( 'post_name', (int) $od['provider_id'] ) : ''; ?>
								<div class="wopt">
									<div><h3 style="font-size:17px"<?php echo $od['title_lang'] ? ' lang="' . esc_attr( $od['title_lang'] ) . '" dir="ltr"' : ''; ?>><?php echo esc_html( $od['title'] ); ?></h3><div class="facts"><?php echo er_partner_chip( $slug, (string) $od['provider'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $od['location'] ? '<span>' . esc_html( $od['location'] ) . '</span>' : ''; ?></div></div>
									<?php echo er_offer_cta_html( $o, [ 'placement' => 'experience-hotels', 'source' => $er_source, 'class' => 'btn btn--line btn--sm', 'label' => er_t( 'See hotels' ), 'icon' => ' ↗' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<div class="wout"><p><?php er_e( 'Each button opens the partner’s site in a new tab.' ); ?></p> <?php er_disclosure(); ?></div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$er_more = [];
	foreach ( (array) $args['dests'] as $er_d ) {
		$er_more = array_merge( $er_more, er_world_place_trips( (int) ( is_object( $er_d ) ? $er_d->ID : $er_d ) ) );
	}
	$er_more = array_values( array_diff( array_unique( $er_more ), [ $er_id ] ) );
	$er_dest = $args['dests'] ? ( is_object( $args['dests'][0] ) ? (int) $args['dests'][0]->ID : (int) $args['dests'][0] ) : 0;
	if ( $er_more ) :
		?>
		<section class="wmore" aria-labelledby="wmore-t">
			<p class="eyebrow"><?php echo esc_html( er_t( 'More trips in {place}', [ 'place' => (string) $W['place'] ] ) ); ?></p>
			<h2 id="wmore-t"><?php er_e( 'Not quite this one?' ); ?></h2>
			<div class="wmore__row">
				<?php foreach ( $er_more as $er_m ) { echo er_world_mini( (int) $er_m ); } // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $er_dest ) : ?><a href="<?php echo esc_url( function_exists( 'er_ux2_trip_url' ) ? er_ux2_trip_url( [ 'destination' => $er_dest, 'len' => '', 'private' => false, 'pickup' => false ] ) : get_post_type_archive_link( 'er_experience' ) ); ?>" class="wmore__all"><?php echo esc_html( er_t( 'All {place} trips', [ 'place' => (string) $W['place'] ] ) ); ?> <?php echo er_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- a fixed arrow character ?></a><?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// As the prototype: the guides about this trip, the ones about fewest trips first, the general itineraries last.
	$er_glist = array_map( static fn ( $g ) => (int) ( is_object( $g ) ? $g->ID : $g ), (array) $args['guides'] );
	$er_gen   = static function ( int $g ): int {
		$en = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $g, 'en' ) : $g;
		return (int) preg_match( '/days-in-egypt|best-time|hidden-gems/', (string) get_post_field( 'post_name', $en ?: $g ) );
	};
	$er_gn = static fn ( int $g ): int => count( er_get_related( $g, '_er_related' ) );
	usort( $er_glist, static fn ( $a, $b ) => ( $er_gen( $a ) <=> $er_gen( $b ) ) ?: ( $er_gn( $a ) <=> $er_gn( $b ) ) );
	$args['guides'] = array_slice( $er_glist, 0, 3 );
	?>
	<?php if ( $args['guides'] ) : ?>
		<section class="wguides" aria-labelledby="wguides-t">
			<p class="eyebrow"><?php er_e( 'Read before you go' ); ?></p>
			<h2 id="wguides-t"><?php er_e( 'Our guides for this trip' ); ?></h2>
			<div class="wguides__row">
				<?php
				foreach ( $args['guides'] as $er_g ) :
					$er_g     = (int) ( is_object( $er_g ) ? $er_g->ID : $er_g );
					$er_gt    = (int) get_post_thumbnail_id( $er_g );
					$er_gs    = er_stock_id_for( $er_g );
					$er_gsrc  = $er_gt ? (string) wp_get_attachment_image_url( $er_gt, 'medium' ) : ( '' !== $er_gs ? er_stock_url( $er_gs, 240, 0.75 ) : '' );
					$er_min   = function_exists( 'er_read_minutes' ) ? er_read_minutes( $er_g ) : 0;
					?>
					<a href="<?php echo esc_url( get_permalink( $er_g ) ); ?>"><?php if ( '' !== $er_gsrc ) : ?><img src="<?php echo esc_url( $er_gsrc ); ?>" alt="" loading="lazy" width="96" height="72" /><?php endif; ?><span><b><?php echo esc_html( get_the_title( $er_g ) ); ?></b><span><?php echo esc_html( er_t( 'Guide' ) . ( $er_min ? ' · ' . er_t( '{n} min read', [ 'n' => $er_min ] ) : '' ) ); ?></span></span></a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_offers ) : ?>
		<a class="capsule" href="#book" id="wcap" data-capsule data-bookbar hidden><span><b><?php echo esc_html( preg_split( '/:| & /u', html_entity_decode( $er_title, ENT_QUOTES, 'UTF-8' ) )[0] ); ?></b><br><small><?php echo esc_html( 1 === $er_n ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => $er_n ] ) ); ?></small></span><span class="btn btn--gold btn--sm"><?php er_e( 'Book this trip' ); ?></span></a>
	<?php endif; ?>
	<div class="wfoot"></div>
</div>
