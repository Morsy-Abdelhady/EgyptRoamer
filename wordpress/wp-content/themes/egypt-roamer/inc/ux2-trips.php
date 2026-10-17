<?php
/**
 * New journey, proposal 4, slice 3 (owner, 17 Oct): the Trips & tours page, previewed with ?ux=2 only.
 * From docs/prototypes/journey-v4/ (v4-round6.py, v4-round7.py) and docs/UX-EXPERT-REVIEW-2026-10-17.md:
 * - the page was titled "Experiences" while the menu says "Trips & tours";
 * - the place filter was a dropdown plus an Apply button: the places are now one row of buttons, every place
 *   one tap away, "All" first;
 * - a tourist also chooses by time and comfort: "Up to 5 hours", "Full day", "2+ days", "Private option" and
 *   "Hotel pickup" filters, read only from the partners' stated facts (Core data/offer-facts.json), never guessed;
 * - the number of trips shown, and a way back when a filter leaves nothing.
 * The filters are plain links (the preview is noindex), so they work without JavaScript.
 */

defined( 'ABSPATH' ) || exit;

/** True on the Trips & tours archive in the preview. */
function er_ux2_trips_page(): bool {
	return er_ux2() && is_post_type_archive( 'er_experience' );
}

// The preview's archive lists every trip, so the filters below see them all (10 per language today).
add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'er_experience' ) && er_ux2() ) {
		$q->set( 'posts_per_page', 100 );
	}
}, 20 );

/**
 * What a trip's options offer, from the partners' own pages only: which lengths (short: up to 5 hours, full: a day,
 * multi: 2 days or more), whether any option is private, whether any offers pickup.
 */
function er_ux2_trip_traits( int $post_id ): array {
	$t = [ 'len' => [], 'private' => false, 'pickup' => false ];
	$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $post_id, 10 ) : [];
	foreach ( $offers as $offer ) {
		$f = function_exists( 'er_offer_facts' ) ? er_offer_facts( (int) $offer ) : [];
		if ( 'private' === ( $f['group'] ?? '' ) ) {
			$t['private'] = true;
		}
		if ( ! empty( $f['pickup'] ) ) {
			$t['pickup'] = true;
		}
		if ( ! empty( $f['duration']['n'] ) ) {
			preg_match_all( '/\d+(?:\.\d+)?/', (string) $f['duration']['n'], $m );
			$n    = $m[0] ? (float) max( $m[0] ) : 0;
			$unit = (string) ( $f['duration']['unit'] ?? 'hours' );
			if ( 'hours' === $unit && $n ) {
				$t['len'][] = $n <= 5 ? 'short' : 'full';
			} elseif ( $n ) {
				$t['len'][] = $n >= 2 ? 'multi' : 'full';
			}
		}
	}
	// No stated duration on any option: the trip page's own length line.
	if ( ! $t['len'] ) {
		$d = strtolower( (string) get_post_meta( $post_id, '_er_duration', true ) );
		if ( preg_match( '/(\d+)\s*days/', $d, $m ) && (int) $m[1] >= 2 ) {
			$t['len'][] = 'multi';
		} elseif ( preg_match( '/full day|1 day/', $d ) ) {
			$t['len'][] = 'full';
		} elseif ( preg_match( '/half day/', $d ) || ( preg_match( '/(\d+)\s*hours?/', $d, $m ) && (int) $m[1] <= 5 ) ) {
			$t['len'][] = 'short';
		} elseif ( preg_match( '/(\d+)\s*hours?/', $d ) ) {
			$t['len'][] = 'full';
		}
	}
	$t['len'] = array_values( array_unique( $t['len'] ) );
	return $t;
}

/** The current filters, from the URL. */
function er_ux2_trip_filters(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters
	$len = isset( $_GET['len'] ) ? sanitize_key( wp_unslash( $_GET['len'] ) ) : '';
	return [
		'destination' => isset( $_GET['destination'] ) ? absint( $_GET['destination'] ) : 0,
		'len'         => in_array( $len, [ 'short', 'full', 'multi' ], true ) ? $len : '',
		'private'     => ! empty( $_GET['private'] ),
		'pickup'      => ! empty( $_GET['pickup'] ),
	];
	// phpcs:enable
}

/** The archive URL with these filters (ux=2 kept by the preview's link script, and here for no-JS visits). */
function er_ux2_trip_url( array $f ): string {
	$args = array_filter( [
		'destination' => $f['destination'] ?: null,
		'len'         => $f['len'] ?: null,
		'private'     => $f['private'] ? 1 : null,
		'pickup'      => $f['pickup'] ? 1 : null,
		'ux'          => get_option( 'er_ux2' ) ? null : 2,
	] );
	return add_query_arg( $args, (string) get_post_type_archive_link( 'er_experience' ) );
}

/** The trips that pass the length, private and pickup filters (the place is filtered by the query). */
function er_ux2_filter_trips( array $ids ): array {
	$f = er_ux2_trip_filters();
	if ( ! $f['len'] && ! $f['private'] && ! $f['pickup'] ) {
		return $ids;
	}
	return array_values( array_filter( $ids, static function ( $id ) use ( $f ): bool {
		$t = er_ux2_trip_traits( (int) $id );
		return ( ! $f['len'] || in_array( $f['len'], $t['len'], true ) ) && ( ! $f['private'] || $t['private'] ) && ( ! $f['pickup'] || $t['pickup'] );
	} ) );
}

/** The filter bar: places, length, private, pickup, and how many trips are shown. */
function er_ux2_trip_filter_bar( int $shown ): void {
	$f     = er_ux2_trip_filters();
	$dests = get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 50, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
	$chip  = static function ( string $label, string $url, bool $on ): string {
		return '<a class="ux2-chip" href="' . esc_url( $url ) . '"' . ( $on ? ' aria-current="true"' : '' ) . '>' . esc_html( $label ) . '</a>';
	};
	$lens  = [
		''      => er_t( 'Any length' ),
		'short' => er_t( 'Up to 5 hours' ),
		'full'  => er_t( 'Full day' ),
		'multi' => er_t( '2+ days' ),
	];
	?>
	<nav class="ux2-filters" aria-label="<?php echo esc_attr( er_t( 'Filter' ) ); ?>">
		<div class="ux2-filters__row">
			<span class="ux2-filters__label"><?php er_e( 'Where' ); ?></span>
			<div class="ux2-filters__chips">
				<?php
				echo $chip( er_t( 'All' ), er_ux2_trip_url( array_merge( $f, [ 'destination' => 0 ] ) ), ! $f['destination'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				foreach ( $dests as $d ) {
					echo $chip( get_the_title( $d ), er_ux2_trip_url( array_merge( $f, [ 'destination' => (int) $d->ID ] ) ), (int) $d->ID === $f['destination'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		</div>
		<div class="ux2-filters__row">
			<span class="ux2-filters__label"><?php er_e( 'Length' ); ?></span>
			<div class="ux2-filters__chips">
				<?php
				foreach ( $lens as $v => $label ) {
					echo $chip( $label, er_ux2_trip_url( array_merge( $f, [ 'len' => $v ] ) ), $v === $f['len'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				echo $chip( er_t( 'Private option' ), er_ux2_trip_url( array_merge( $f, [ 'private' => ! $f['private'] ] ) ), $f['private'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo $chip( er_t( 'Hotel pickup' ), er_ux2_trip_url( array_merge( $f, [ 'pickup' => ! $f['pickup'] ] ) ), $f['pickup'] ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
			<p class="ux2-filters__count" role="status"><?php echo esc_html( er_t( 'Trips shown: {n}', [ 'n' => (string) $shown ] ) ); // one form for every language's plurals ?></p>
		</div>
	</nav>
	<?php
}

/** Nothing left after the filters: say so, and offer the way back. */
function er_ux2_trip_empty(): void {
	?>
	<div class="empty-state">
		<p class="t-h3"><?php er_e( 'No trip matches all of these.' ); ?></p>
		<p class="muted"><?php er_e( 'Remove a filter, or see every trip.' ); ?></p>
		<p><a class="btn btn--outline" href="<?php echo esc_url( er_ux2_trip_url( [ 'destination' => 0, 'len' => '', 'private' => false, 'pickup' => false ] ) ); ?>"><?php er_e( 'See all trips' ); ?></a></p>
	</div>
	<?php
}
