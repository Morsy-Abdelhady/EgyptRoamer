<?php
/**
 * Tiqets API key.
 *
 *   - The owner pastes the key under Egypt Roamer → Tiqets API (or sets ER_TIQETS_API_KEY in
 *     wp-config.php). It is stored privately (option er_tiqets_api_key, not autoloaded) and never shown again.
 *   - Twice a day (and on save) each live Tiqets offer's product is read: rating numbers, "from" price and the
 *     first photo, kept in the er_tiqets_data option and shown on the offer cards like Viator's.
 *
 * @package EgyptRoamerCore
 */

defined( 'ABSPATH' ) || exit;

const ER_TIQETS_API = 'https://api.tiqets.com/v2/products/';

function er_tiqets_key(): string {
	if ( defined( 'ER_TIQETS_API_KEY' ) && '' !== (string) ER_TIQETS_API_KEY ) {
		return (string) ER_TIQETS_API_KEY;
	}
	return (string) get_option( 'er_tiqets_api_key', '' );
}

/** Product id from a tiqets.com product URL (…-p1002656/). */
function er_tiqets_product_id( string $url ): string {
	return preg_match( '/-p(\d+)\/?$/', (string) wp_parse_url( $url, PHP_URL_PATH ), $m ) ? $m[1] : '';
}

/** Linked Tiqets offers: slug => product id. */
function er_tiqets_offers(): array {
	$out   = [];
	$posts = get_posts( [ 'post_type' => 'er_offer', 'post_status' => 'publish', 'numberposts' => 500, 'lang' => '', 'suppress_filters' => false ] );
	foreach ( $posts as $p ) {
		$url = (string) get_post_meta( $p->ID, '_er_target_url', true );
		if ( ! er_offer_is_live( $p->ID ) || ! preg_match( '#^https?://([a-z0-9-]+\.)*tiqets\.com/#i', $url ) ) {
			continue;
		}
		$id = er_tiqets_product_id( $url );
		if ( '' !== $id ) {
			$out[ $p->post_name ] = $id;
		}
	}
	return $out;
}

/** Test the key with one product. Returns the HTTP status, or 0 on a connection error. */
function er_tiqets_test(): int {
	$key = er_tiqets_key();
	$ids = array_values( er_tiqets_offers() );
	if ( '' === $key || ! $ids ) {
		return 0;
	}
	$res = wp_remote_get( ER_TIQETS_API . rawurlencode( $ids[0] ) . '?lang=en', [
		'timeout' => 15,
		'headers' => [ 'Authorization' => 'Token ' . $key, 'Accept' => 'application/json' ],
	] );
	return is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
}

/**
 * One product from GET /v2/products/{id} (developers.tiqets.dev): the average rating and number of ratings
 * (numbers only; review texts are never read, API terms 2.3.3.13), the "from" price in US dollars, and the first
 * image's "small" URL with its credit. Returns these fields or [ error ].
 */
function er_tiqets_fetch( string $id ): array {
	$key = er_tiqets_key();
	if ( '' === $key ) {
		return [ 'error' => 'no key' ];
	}
	$res = wp_remote_get( ER_TIQETS_API . rawurlencode( $id ) . '?lang=en&currency=USD', [
		'timeout' => 8,
		'headers' => [ 'Authorization' => 'Token ' . $key, 'Accept' => 'application/json' ],
	] );
	if ( is_wp_error( $res ) ) {
		return [ 'error' => $res->get_error_message() ];
	}
	$status = (int) wp_remote_retrieve_response_code( $res );
	if ( 200 !== $status ) {
		return [ 'error' => 'HTTP ' . $status ];
	}
	$p   = (array) ( json_decode( (string) wp_remote_retrieve_body( $res ), true )['product'] ?? [] );
	$out = [];
	$r   = (array) ( $p['ratings'] ?? [] );
	if ( isset( $r['average'], $r['total'] ) && (int) $r['total'] > 0 ) {
		$out['rating']  = round( (float) $r['average'], 1 );
		$out['reviews'] = (int) $r['total'];
	}
	if ( is_numeric( $p['price'] ?? null ) && (float) $p['price'] > 0 ) {
		$out['price']    = (float) $p['price'];
		$out['currency'] = (string) ( $p['currency'] ?? 'USD' );
	}
	$img = ( (array) ( $p['images'] ?? [] ) )[0] ?? [];
	$url = '';
	if ( is_string( $img ) ) {
		$url = $img;
	} elseif ( is_array( $img ) ) {
		foreach ( [ 'small', 'medium', 'large', 'extra_large', 'url' ] as $k ) {
			if ( ! empty( $img[ $k ] ) && is_string( $img[ $k ] ) ) {
				$url = $img[ $k ];
				break;
			}
		}
	}
	$url = preg_replace( '#^(https?:)?//#i', 'https://', $url );
	if ( 0 === strpos( $url, 'https://' ) ) {
		$out['url']    = $url;
		$out['credit'] = is_array( $img ) ? (string) ( $img['credits'] ?? '' ) : '';
	} else {
		// Kept for the admin table, so a changed API shape is visible: the first image's type or keys.
		$out['img_shape'] = is_array( $img ) ? implode( ',', array_keys( $img ) ) : gettype( $img ) . ' ' . count( (array) ( $p['images'] ?? [] ) );
	}
	return $out ?: [ 'error' => 'no data' ];
}

/** Refresh every Tiqets offer; keeps the last good values when a refresh fails. */
function er_tiqets_refresh_all(): array {
	$store = (array) get_option( 'er_tiqets_data', [] );
	$log   = [];
	foreach ( er_tiqets_offers() as $slug => $id ) {
		$r   = er_tiqets_fetch( $id );
		$row = (array) ( $store[ $slug ] ?? [] );
		if ( isset( $r['error'] ) ) {
			$row['error'] = $r['error'];
			$log[ $slug ] = $r['error'];
		} else {
			$row = [ 'id' => $id, 'at' => time() ] + $r;
			$log[ $slug ] = 'ok';
		}
		$store[ $slug ] = $row;
	}
	update_option( 'er_tiqets_data', $store, false );
	return $log;
}

function er_tiqets_row( int $offer_id ): array {
	static $store = null;
	if ( null === $store ) {
		$store = (array) get_option( 'er_tiqets_data', [] );
	}
	return (array) ( $store[ (string) get_post_field( 'post_name', $offer_id ) ] ?? [] );
}

/** The stored Tiqets photo URL for an offer, or ''. Images are re-read at least every 14 days (caching rules). */
function er_tiqets_image_for_offer( int $offer_id ): string {
	$row = er_tiqets_row( $offer_id );
	return isset( $row['url'], $row['at'] ) && time() - (int) $row['at'] < 14 * DAY_IN_SECONDS ? (string) $row['url'] : '';
}

/** What Tiqets states for an offer: [ rating, reviews, price, currency, price_at ]; a price older than a day is dropped. */
function er_tiqets_live_for_offer( int $offer_id ): array {
	$row = er_tiqets_row( $offer_id );
	$out = [];
	if ( isset( $row['rating'], $row['reviews'] ) ) {
		$out['rating']  = (float) $row['rating'];
		$out['reviews'] = (int) $row['reviews'];
	}
	if ( isset( $row['price'], $row['at'] ) && time() - (int) $row['at'] < DAY_IN_SECONDS ) {
		$out['price']    = (float) $row['price'];
		$out['currency'] = (string) $row['currency'];
		$out['price_at'] = (int) $row['at'];
	}
	return $out;
}

/* Twice-daily refresh while a key is set, so a shown price is never older than a day. */
add_action( 'er_tiqets_refresh', 'er_tiqets_refresh_all' );
add_action( 'init', static function () {
	if ( '' !== er_tiqets_key() && ! wp_next_scheduled( 'er_tiqets_refresh' ) ) {
		wp_schedule_event( time() + 60, 'twicedaily', 'er_tiqets_refresh' );
	}
} );

/* -------------------------------------------------------------------------- */
/* wp-admin: Egypt Roamer → Tiqets API                                         */
/* -------------------------------------------------------------------------- */

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Tiqets API', 'egypt-roamer-core' ), __( 'Tiqets API', 'egypt-roamer-core' ), 'manage_options', 'er-tiqets', 'er_render_tiqets_page' );
}, 31 );

add_action( 'admin_post_er_tiqets_save', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'egypt-roamer-core' ) );
	}
	check_admin_referer( 'er_tiqets_save' );
	if ( isset( $_POST['er_tiqets_clear'] ) ) {
		delete_option( 'er_tiqets_api_key' );
		$msg = 'cleared';
	} else {
		$key = trim( sanitize_text_field( wp_unslash( (string) ( $_POST['er_tiqets_key'] ?? '' ) ) ) );
		if ( '' !== $key ) {
			update_option( 'er_tiqets_api_key', $key, false );
		}
		$msg = 'test-' . er_tiqets_test();
		er_tiqets_refresh_all();
	}
	wp_safe_redirect( add_query_arg( [ 'page' => 'er-tiqets', 'er_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );

function er_render_tiqets_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$has   = '' !== er_tiqets_key();
	$const = defined( 'ER_TIQETS_API_KEY' ) && '' !== (string) ER_TIQETS_API_KEY;
	$msg   = sanitize_key( (string) ( $_GET['er_msg'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tiqets API', 'egypt-roamer-core' ); ?></h1>
		<p><?php esc_html_e( 'Paste the API token from tiqets.com/affiliate → Tools → API Access. The token is stored privately and never shown here again. Each Tiqets offer then shows its rating, from price and photo, refreshed twice a day.', 'egypt-roamer-core' ); ?></p>
		<?php if ( 0 === strpos( $msg, 'test-' ) ) : ?>
			<?php $code = (int) substr( $msg, 5 ); ?>
			<div class="notice <?php echo 200 === $code ? 'notice-success' : 'notice-warning'; ?>"><p>
				<?php
				echo esc_html( 200 === $code
					? __( 'Token saved and tested: the Tiqets API answered.', 'egypt-roamer-core' )
					/* translators: %d: HTTP status, 0 = no answer */
					: sprintf( __( 'Token saved, but the test did not pass (status %d).', 'egypt-roamer-core' ), $code ) );
				?>
			</p></div>
		<?php elseif ( 'cleared' === $msg ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'Key removed.', 'egypt-roamer-core' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="er_tiqets_save" />
			<?php wp_nonce_field( 'er_tiqets_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="er_tiqets_key"><?php esc_html_e( 'Tiqets API token', 'egypt-roamer-core' ); ?></label></th>
					<td>
						<input type="password" id="er_tiqets_key" name="er_tiqets_key" class="regular-text" autocomplete="off" value="" placeholder="<?php echo $has ? esc_attr__( 'A key is saved — leave empty to keep it', 'egypt-roamer-core' ) : ''; ?>" <?php disabled( $const ); ?> />
						<p class="description"><?php echo $const ? esc_html__( 'Set in wp-config.php (ER_TIQETS_API_KEY).', 'egypt-roamer-core' ) : ( $has ? esc_html__( 'A key is saved.', 'egypt-roamer-core' ) : esc_html__( 'No key yet.', 'egypt-roamer-core' ) ); ?></p>
					</td>
				</tr>
			</table>
			<p>
				<?php submit_button( __( 'Save and test', 'egypt-roamer-core' ), 'primary', 'submit', false ); ?>
				<?php if ( $has && ! $const ) : ?>
					<?php submit_button( __( 'Remove key', 'egypt-roamer-core' ), 'delete', 'er_tiqets_clear', false ); ?>
				<?php endif; ?>
			</p>
		</form>
		<h2><?php esc_html_e( 'Tiqets offers', 'egypt-roamer-core' ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Offer', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Product code', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Photo', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Rating', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'From price', 'egypt-roamer-core' ); ?></th></tr></thead>
			<tbody>
				<?php $store = (array) get_option( 'er_tiqets_data', [] ); ?>
				<?php foreach ( er_tiqets_offers() as $slug => $id ) : ?>
					<?php $row = (array) ( $store[ $slug ] ?? [] ); ?>
					<tr>
						<td><?php echo esc_html( $slug ); ?></td>
						<td><code><?php echo esc_html( $id ); ?></code></td>
						<td><?php echo ! empty( $row['url'] ) ? '<img src="' . esc_url( $row['url'] ) . '" alt="" width="72" height="48" style="object-fit:cover" />' : esc_html( (string) ( $row['error'] ?? $row['img_shape'] ?? '—' ) ); ?></td>
						<td><?php echo isset( $row['rating'] ) ? esc_html( $row['rating'] . ' (' . $row['reviews'] . ')' ) : '—'; ?></td>
						<td><?php echo isset( $row['price'] ) ? esc_html( $row['price'] . ' ' . $row['currency'] ) : '—'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
