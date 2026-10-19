<?php
/**
 * Viator product photos through the Viator Partner API (owner, 16 Oct: each Viator offer shows its own trip's
 * photo). Viator supplies product photos to affiliates through its API; copying them from viator.com is not
 * allowed (docs/PARTNER-OPTIONS-2026-10-16.md §4).
 *
 *   - The API key is entered by the owner under Egypt Roamer → Viator photos (or ER_VIATOR_API_KEY in
 *     wp-config.php). It is stored with autoload off and never printed back.
 *   - For each live Viator offer, the product code is read from its target URL (".../d826-224493P18" →
 *     "224493P18"), GET /partner/products/{code} returns its images, and the cover image's URL (Viator's CDN) is
 *     kept in the er_viator_images option. Viator allows single-product responses to be cached for up to 1 hour (Technical Guide), so cron refreshes hourly.
 *   - The cover photo, the average rating and the number of reviews (numbers only), and the lowest price from the
 *     availability schedule. Review texts and "viatorUniqueContent" must not be indexed under the API terms;
 *     they are never read or shown.
 */

defined( 'ABSPATH' ) || exit;

const ER_VIATOR_API = 'https://api.viator.com/partner/products/';

function er_viator_key(): string {
	if ( defined( 'ER_VIATOR_API_KEY' ) && '' !== (string) ER_VIATOR_API_KEY ) {
		return (string) ER_VIATOR_API_KEY;
	}
	return (string) get_option( 'er_viator_api_key', '' );
}

/** "https://www.viator.com/tours/Luxor/Full-Day-Tour/d826-224493P18" → "224493P18". */
function er_viator_product_code( string $url ): string {
	return preg_match( '~/d\d+-([0-9A-Za-z]+P\d+)(?:[/?#]|$)~', $url, $m ) ? $m[1] : '';
}

/** Live offers that point to viator.com: slug → product code. */
function er_viator_offers(): array {
	$out = [];
	foreach ( get_posts( [ 'post_type' => 'er_offer', 'post_status' => 'publish', 'numberposts' => 500, 'lang' => '', 'suppress_filters' => false ] ) as $p ) {
		$url = (string) get_post_meta( $p->ID, '_er_target_url', true );
		if ( ! er_offer_is_live( $p->ID ) || ! preg_match( '#^https?://([a-z0-9-]+\.)*viator\.com/#i', $url ) ) {
			continue;
		}
		$code = er_viator_product_code( $url );
		if ( '' !== $code ) {
			$out[ $p->post_name ] = $code;
		}
	}
	return $out;
}

/**
 * One product's cover photo from the API: the smallest variant at least $min px wide (the booking panel shows it at
 * about 72px), else the largest. Returns [ url ] or [ error ].
 */
function er_viator_fetch_image( string $code, int $min = 240 ): array {
	$key = er_viator_key();
	if ( '' === $key ) {
		return [ 'error' => 'no key' ];
	}
	$res = wp_remote_get( ER_VIATOR_API . rawurlencode( $code ), [
		'timeout' => 8,
		'headers' => [ 'exp-api-key' => $key, 'Accept' => 'application/json;version=2.0', 'Accept-Language' => 'en-US' ],
	] );
	if ( is_wp_error( $res ) ) {
		return [ 'error' => $res->get_error_message() ];
	}
	$status = (int) wp_remote_retrieve_response_code( $res );
	if ( 200 !== $status ) {
		return [ 'error' => 'HTTP ' . $status ];
	}
	$data   = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	$images = (array) ( $data['images'] ?? [] );
	// The rating and the number of reviews (numbers only; review texts are never read or shown, API terms).
	$rev    = (array) ( $data['reviews'] ?? [] );
	$meta   = [
		'rating'  => isset( $rev['combinedAverageRating'] ) ? round( (float) $rev['combinedAverageRating'], 1 ) : null,
		'reviews' => isset( $rev['totalReviews'] ) ? (int) $rev['totalReviews'] : null,
	];
	if ( ! $images ) {
		return [ 'error' => 'no images' ] + $meta;
	}
	$cover = $images[0];
	foreach ( $images as $img ) {
		if ( ! empty( $img['isCover'] ) ) {
			$cover = $img;
			break;
		}
	}
	$variants = (array) ( $cover['variants'] ?? [] );
	usort( $variants, static fn ( $a, $b ) => (int) ( $a['width'] ?? 0 ) <=> (int) ( $b['width'] ?? 0 ) );
	$pick = end( $variants ) ?: [];
	foreach ( $variants as $v ) {
		if ( (int) ( $v['width'] ?? 0 ) >= $min ) {
			$pick = $v;
			break;
		}
	}
	$url = (string) ( $pick['url'] ?? '' );
	return ( 0 === strpos( $url, 'https://' ) ? [ 'url' => $url ] : [ 'error' => 'no image URL' ] ) + $meta;
}

/**
 * The product's lowest price for the coming dates, from its availability schedule (GET
 * /partner/availability/schedules/{code}, summary.fromPrice), in the partner account's currency. [ price, currency ]
 * or [ error ].
 */
function er_viator_fetch_price( string $code ): array {
	$key = er_viator_key();
	if ( '' === $key ) {
		return [ 'error' => 'no key' ];
	}
	$res = wp_remote_get( 'https://api.viator.com/partner/availability/schedules/' . rawurlencode( $code ), [
		'timeout' => 8,
		'headers' => [ 'exp-api-key' => $key, 'Accept' => 'application/json;version=2.0', 'Accept-Language' => 'en-US' ],
	] );
	if ( is_wp_error( $res ) ) {
		return [ 'error' => $res->get_error_message() ];
	}
	$status = (int) wp_remote_retrieve_response_code( $res );
	if ( 200 !== $status ) {
		return [ 'error' => 'HTTP ' . $status ];
	}
	$data  = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	$price = $data['summary']['fromPrice'] ?? null;
	return is_numeric( $price ) && (float) $price > 0 ? [ 'price' => (float) $price, 'currency' => (string) ( $data['currency'] ?? 'USD' ) ] : [ 'error' => 'no price' ];
}

/** Refresh every Viator offer's photo; keeps the last good URL when a refresh fails. */
function er_viator_refresh_all(): array {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 180 ); // ~15 offers, up to 8s each.
	}
	$store = (array) get_option( 'er_viator_images', [] );
	$log   = [];
	foreach ( er_viator_offers() as $slug => $code ) {
		$r    = er_viator_fetch_image( $code );
		$pr   = er_viator_fetch_price( $code );
		$row  = (array) ( $store[ $slug ] ?? [] );
		$row['code'] = $code;
		unset( $row['error'], $row['price_error'] );
		if ( isset( $r['url'] ) ) {
			$row['url'] = $r['url'];
			$row['at']  = time();
			$log[ $slug ] = 'ok';
		} else {
			$row['error'] = $r['error'];
			$log[ $slug ] = $r['error'];
		}
		if ( null !== ( $r['rating'] ?? null ) && ( $r['reviews'] ?? 0 ) > 0 ) {
			$row['rating']  = $r['rating'];
			$row['reviews'] = $r['reviews'];
		}
		if ( isset( $pr['price'] ) ) {
			$row['price']    = $pr['price'];
			$row['currency'] = $pr['currency'];
			$row['price_at'] = time();
		} else {
			$row['price_error'] = $pr['error'];
			// A price older than a day is not shown (er_viator_live_for_offer).
		}
		$store[ $slug ] = $row;
	}
	update_option( 'er_viator_images', $store, false );
	return $log;
}

/** The stored Viator photo URL for an offer, or ''. */
function er_viator_image_for_offer( int $offer_id ): string {
	static $store = null;
	if ( null === $store ) {
		$store = (array) get_option( 'er_viator_images', [] );
	}
	return (string) ( $store[ (string) get_post_field( 'post_name', $offer_id ) ]['url'] ?? '' );
}

/**
 * What Viator states live for an offer: [ rating, reviews, price, currency, price_at ] (each may be missing). A price
 * is given only when it was read in the last 24 hours, so a stale price is never shown.
 */
function er_viator_live_for_offer( int $offer_id ): array {
	static $store = null;
	if ( null === $store ) {
		$store = (array) get_option( 'er_viator_images', [] );
	}
	$row = (array) ( $store[ (string) get_post_field( 'post_name', $offer_id ) ] ?? [] );
	$out = [];
	if ( isset( $row['rating'], $row['reviews'] ) && $row['reviews'] > 0 ) {
		$out['rating']  = (float) $row['rating'];
		$out['reviews'] = (int) $row['reviews'];
	}
	if ( isset( $row['price'], $row['price_at'] ) && time() - (int) $row['price_at'] < DAY_IN_SECONDS ) {
		$out['price']    = (float) $row['price'];
		$out['currency'] = (string) ( $row['currency'] ?? 'USD' );
		$out['price_at'] = (int) $row['price_at'];
	}
	return $out;
}

/* Hourly refresh while a key is set (the 1-hour cache limit). */
add_action( 'er_viator_refresh', 'er_viator_refresh_all' );
add_action( 'init', static function () {
	if ( '' !== er_viator_key() && ! wp_next_scheduled( 'er_viator_refresh' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'er_viator_refresh' );
	}
} );

/* -------------------------------------------------------------------------- */
/* wp-admin: Egypt Roamer → Viator photos                                      */
/* -------------------------------------------------------------------------- */

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Viator photos', 'egypt-roamer-core' ), __( 'Viator photos', 'egypt-roamer-core' ), 'manage_options', 'er-viator', 'er_render_viator_page' );
}, 30 );

add_action( 'admin_post_er_viator_save', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'egypt-roamer-core' ) );
	}
	check_admin_referer( 'er_viator_save' );
	$msg = 'saved';
	if ( isset( $_POST['er_viator_clear'] ) ) {
		delete_option( 'er_viator_api_key' );
		wp_clear_scheduled_hook( 'er_viator_refresh' );
		$msg = 'cleared';
	} else {
		$key = trim( sanitize_text_field( wp_unslash( (string) ( $_POST['er_viator_key'] ?? '' ) ) ) );
		if ( '' !== $key ) {
			update_option( 'er_viator_api_key', $key, false );
		}
		$log = er_viator_refresh_all();
		$ok  = count( array_filter( $log, static fn ( $v ) => 'ok' === $v ) );
		$msg = 'refreshed-' . $ok . '-' . count( $log );
	}
	wp_safe_redirect( add_query_arg( [ 'page' => 'er-viator', 'er_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );

function er_render_viator_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$has   = '' !== er_viator_key();
	$const = defined( 'ER_VIATOR_API_KEY' ) && '' !== (string) ER_VIATOR_API_KEY;
	$store = (array) get_option( 'er_viator_images', [] );
	$msg   = sanitize_key( (string) ( $_GET['er_msg'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Viator photos', 'egypt-roamer-core' ); ?></h1>
		<p><?php esc_html_e( 'Each Viator offer shows its own trip photo, supplied by the Viator Partner API. Paste the production API key from partners.viator.com → Tools → API Access. The key is stored privately and never shown here again.', 'egypt-roamer-core' ); ?></p>
		<?php if ( 0 === strpos( $msg, 'refreshed-' ) ) : ?>
			<?php [ , $ok, $all ] = array_pad( explode( '-', $msg ), 3, '0' ); ?>
			<div class="notice notice-info"><p><?php echo esc_html( sprintf( /* translators: 1: photos found, 2: offers */ __( 'Photos found for %1$s of %2$s Viator offers.', 'egypt-roamer-core' ), $ok, $all ) ); ?></p></div>
		<?php elseif ( 'cleared' === $msg ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'Key removed.', 'egypt-roamer-core' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="er_viator_save" />
			<?php wp_nonce_field( 'er_viator_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="er_viator_key"><?php esc_html_e( 'Viator API key', 'egypt-roamer-core' ); ?></label></th>
					<td>
						<input type="password" id="er_viator_key" name="er_viator_key" class="regular-text" autocomplete="off" value="" placeholder="<?php echo $has ? esc_attr__( 'A key is saved — leave empty to keep it', 'egypt-roamer-core' ) : ''; ?>" <?php disabled( $const ); ?> />
						<p class="description"><?php echo $const ? esc_html__( 'Set in wp-config.php (ER_VIATOR_API_KEY).', 'egypt-roamer-core' ) : ( $has ? esc_html__( 'A key is saved.', 'egypt-roamer-core' ) : esc_html__( 'No key yet.', 'egypt-roamer-core' ) ); ?></p>
					</td>
				</tr>
			</table>
			<p>
				<?php submit_button( __( 'Save and fetch photos', 'egypt-roamer-core' ), 'primary', 'submit', false ); ?>
				<?php if ( $has && ! $const ) : ?>
					<?php submit_button( __( 'Remove key', 'egypt-roamer-core' ), 'delete', 'er_viator_clear', false ); ?>
				<?php endif; ?>
			</p>
		</form>
		<h2><?php esc_html_e( 'Viator offers', 'egypt-roamer-core' ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Offer', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Product code', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Photo', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Rating', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'From price', 'egypt-roamer-core' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( er_viator_offers() as $slug => $code ) : ?>
					<?php $row = (array) ( $store[ $slug ] ?? [] ); ?>
					<tr>
						<td><?php echo esc_html( $slug ); ?></td>
						<td><code><?php echo esc_html( $code ); ?></code></td>
						<td>
							<?php if ( ! empty( $row['url'] ) ) : ?>
								<img src="<?php echo esc_url( $row['url'] ); ?>" alt="" width="72" height="48" style="object-fit:cover;vertical-align:middle" />
								<?php echo esc_html( wp_date( get_option( 'date_format' ), (int) ( $row['at'] ?? 0 ) ) ); ?>
							<?php endif; ?>
							<?php if ( ! empty( $row['error'] ) ) : ?>
								<span style="color:#b32d2e"><?php echo esc_html( (string) $row['error'] ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo isset( $row['rating'] ) ? esc_html( '★ ' . $row['rating'] . ' (' . number_format_i18n( (int) $row['reviews'] ) . ')' ) : '—'; ?></td>
						<td><?php echo isset( $row['price'] ) ? esc_html( ( $row['currency'] ?? '' ) . ' ' . number_format_i18n( (float) $row['price'], 2 ) . ' · ' . wp_date( 'j M H:i', (int) $row['price_at'] ) ) : ( ! empty( $row['price_error'] ) ? '<span style="color:#b32d2e">' . esc_html( (string) $row['price_error'] ) . '</span>' : '—' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
