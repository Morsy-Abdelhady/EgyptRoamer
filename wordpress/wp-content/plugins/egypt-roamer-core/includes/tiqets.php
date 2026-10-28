<?php
/**
 * Tiqets API key.
 *
 *   - The owner pastes the key under Egypt Roamer → Tiqets API (or sets ER_TIQETS_API_KEY in
 *     wp-config.php). It is stored privately (option er_tiqets_api_key, not autoloaded) and never shown again.
 *   - For now the page only stores the key and tests it against one linked offer's product; showing
 *     live product data on the site comes in a later release.
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
	$posts = get_posts( [ 'post_type' => 'er_offer', 'post_status' => 'publish', 'numberposts' => -1 ] );
	foreach ( $posts as $p ) {
		$url = (string) get_post_meta( $p->ID, '_er_target_url', true );
		if ( false === strpos( $url, 'tiqets.com' ) ) {
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
		<p><?php esc_html_e( 'Paste the API token from tiqets.com/affiliate → Tools → API Access. The token is stored privately and never shown here again. For now it is only stored and tested; nothing from the API is shown on the site yet.', 'egypt-roamer-core' ); ?></p>
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
			<thead><tr><th><?php esc_html_e( 'Offer', 'egypt-roamer-core' ); ?></th><th><?php esc_html_e( 'Product code', 'egypt-roamer-core' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( er_tiqets_offers() as $slug => $id ) : ?>
					<tr><td><?php echo esc_html( $slug ); ?></td><td><code><?php echo esc_html( $id ); ?></code></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
