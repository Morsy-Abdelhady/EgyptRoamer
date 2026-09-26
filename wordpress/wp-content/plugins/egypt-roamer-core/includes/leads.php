<?php
/**
 * Lead capture: newsletter sign-up and contact form.
 *
 * - Newsletter → FluentCRM (status "pending" = double opt-in) when it is active,
 *   otherwise stored in {prefix}er_subscribers for export/import into a CRM.
 * - Contact → always stored as a private "Contact message" first, then emailed.
 *   Email delivery depends on the site's mail setup (see docs: API-based
 *   transactional provider, not SMTP from the host).
 *
 * Public forms sit on cached pages, so WordPress nonces (which expire) are not
 * used; abuse is limited by a honeypot, a signed time trap and a rate limit.
 * Nothing beyond email, optional name/message and chosen interests is stored.
 */

defined( 'ABSPATH' ) || exit;

function er_subscribers_table(): string {
	global $wpdb;
	return $wpdb->prefix . 'er_subscribers';
}

add_action( 'er_install_tables', 'er_install_subscribers_table' );
function er_install_subscribers_table(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table = er_subscribers_table();
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL,
		lang varchar(12) NOT NULL DEFAULT '',
		interests varchar(255) NOT NULL DEFAULT '',
		source varchar(64) NOT NULL DEFAULT '',
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email)
	) {$wpdb->get_charset_collate()};" );
}

/** Hidden fields every public form carries. */
function er_form_guard_fields(): string {
	$t = (string) time();
	return sprintf(
		'<div class="er-hp" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden"><label>Website <input type="text" name="er_website" value="" tabindex="-1" autocomplete="off" /></label></div><input type="hidden" name="er_t" value="%s" /><input type="hidden" name="er_ts" value="%s" />',
		esc_attr( $t ),
		esc_attr( wp_hash( 'er_form|' . $t ) )
	);
}

/** Reject bots and floods. Returns true when the submission may proceed. */
function er_form_guard_passes( string $bucket ): bool {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form, see file header
	if ( ! empty( $_POST['er_website'] ) ) {
		return false;
	}
	$t  = isset( $_POST['er_t'] ) ? absint( $_POST['er_t'] ) : 0;
	$ts = isset( $_POST['er_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['er_ts'] ) ) : '';
	// phpcs:enable
	if ( ! $t || ! hash_equals( wp_hash( 'er_form|' . $t ), $ts ) || time() - $t < 3 ) {
		return false;
	}
	// Rate limit per network address, hashed and kept only for 10 minutes.
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key  = 'er_rl_' . $bucket . '_' . substr( wp_hash( $ip . gmdate( 'Y-m-d' ) ), 0, 20 );
	$hits = (int) get_transient( $key );
	// Keyed on REMOTE_ADDR: if the host's proxy/CDN hides client IPs, raise this limit (see docs/HOSTING-AND-PLUGINS.md).
	if ( $hits >= (int) apply_filters( 'er_form_rate_limit', 5, $bucket ) ) {
		return false;
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );
	return true;
}

/** Back to the page the form was on, with a status flag the theme/analytics read. */
function er_form_return( string $state, string $anchor = '' ): void {
	$back = wp_get_referer() ?: home_url( '/' );
	$back = remove_query_arg( 'er', $back );
	wp_safe_redirect( add_query_arg( 'er', $state, $back ) . ( $anchor ? '#' . $anchor : '' ), 303 );
	exit;
}

/* -------------------------------------------------------------------------- */
/* Newsletter                                                                  */
/* -------------------------------------------------------------------------- */

function er_newsletter_action_url(): string {
	return admin_url( 'admin-post.php' );
}

function er_handle_subscribe(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form, guarded below
	if ( ! er_form_guard_passes( 'sub' ) ) {
		er_form_return( 'subscribe-error', 'newsletter' );
	}
	$raw   = isset( $_POST['email'] ) ? trim( (string) wp_unslash( $_POST['email'] ) ) : '';
	$email = sanitize_email( $raw );
	if ( ! is_email( $email ) || strtolower( $email ) !== strtolower( $raw ) ) {
		er_form_return( 'subscribe-invalid', 'newsletter' ); // never store an address the visitor did not type
	}
	$interests = isset( $_POST['interests'] ) ? array_slice( array_map( 'sanitize_key', (array) wp_unslash( $_POST['interests'] ) ), 0, 10 ) : [];
	$source    = isset( $_POST['source'] ) ? substr( sanitize_key( wp_unslash( $_POST['source'] ) ), 0, 64 ) : '';
	$lang      = isset( $_POST['lang'] ) ? substr( sanitize_key( wp_unslash( $_POST['lang'] ) ), 0, 12 ) : '';
	// phpcs:enable

	$handled = false;
	if ( function_exists( 'FluentCrmApi' ) ) {
		$list    = (int) er_settings( 'fluentcrm_list' );
		$payload = [ 'email' => $email, 'status' => 'pending', 'source' => 'egypt-roamer:' . $source ];
		if ( $list ) {
			$payload['lists'] = [ $list ];
		}
		$contact = FluentCrmApi( 'contacts' )->createOrUpdate( $payload );
		if ( $contact ) {
			if ( method_exists( $contact, 'sendDoubleOptinEmail' ) && 'pending' === $contact->status ) {
				$contact->sendDoubleOptinEmail();
			}
			$handled = true;
		}
	}
	if ( ! $handled ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'INSERT INTO ' . er_subscribers_table() . " (email, lang, interests, source, created_at) VALUES (%s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE interests = IF(VALUES(interests) = '', interests, VALUES(interests)), lang = IF(VALUES(lang) = '', lang, VALUES(lang))",
			$email,
			$lang,
			implode( ',', $interests ),
			$source,
			current_time( 'mysql', true )
		) );
	}
	do_action( 'er_newsletter_subscribed', $email, $interests, $lang );
	// "pending" only when a confirmation email really went out (FluentCRM double opt-in).
	er_form_return( $handled ? 'subscribed-pending' : 'subscribed', 'newsletter' );
}
add_action( 'admin_post_nopriv_er_subscribe', 'er_handle_subscribe' );
add_action( 'admin_post_er_subscribe', 'er_handle_subscribe' );

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Subscribers', 'egypt-roamer-core' ), __( 'Subscribers', 'egypt-roamer-core' ), 'manage_options', 'er-subscribers', 'er_render_subscribers_page' );
}, 25 );

function er_render_subscribers_page(): void {
	global $wpdb;
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$table = er_subscribers_table();
	$rows  = $wpdb->get_results( "SELECT email, lang, interests, source, created_at FROM {$table} ORDER BY created_at DESC LIMIT 500", ARRAY_A ); // phpcs:ignore WordPress.DB
	echo '<div class="wrap er-wrap"><h1>' . esc_html__( 'Newsletter subscribers', 'egypt-roamer-core' ) . '</h1>';
	echo '<p class="description">' . esc_html( function_exists( 'FluentCrmApi' ) ? __( 'FluentCRM is active: new sign-ups go to FluentCRM (double opt-in). This table only holds sign-ups from before it was installed.', 'egypt-roamer-core' ) : __( 'No CRM is active. Sign-ups are stored here unconfirmed; import them into your CRM and send a confirmation email before any marketing.', 'egypt-roamer-core' ) ) . '</p>';
	printf( '<p><a class="button" href="%s">%s</a></p>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=er_export_subscribers' ), 'er_export_subscribers' ) ), esc_html__( 'Export CSV', 'egypt-roamer-core' ) );
	echo '<table class="widefat striped"><thead><tr><th>Email</th><th>' . esc_html__( 'Language', 'egypt-roamer-core' ) . '</th><th>' . esc_html__( 'Interests', 'egypt-roamer-core' ) . '</th><th>' . esc_html__( 'Form', 'egypt-roamer-core' ) . '</th><th>' . esc_html__( 'Date (UTC)', 'egypt-roamer-core' ) . '</th></tr></thead><tbody>';
	foreach ( (array) $rows as $r ) {
		printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( $r['email'] ), esc_html( $r['lang'] ), esc_html( $r['interests'] ), esc_html( $r['source'] ), esc_html( $r['created_at'] ) );
	}
	echo '</tbody></table></div>';
}

add_action( 'admin_post_er_export_subscribers', static function () {
	global $wpdb;
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_export_subscribers' );
	$table = er_subscribers_table();
	$rows  = $wpdb->get_results( "SELECT email, lang, interests, source, created_at FROM {$table} ORDER BY created_at ASC", ARRAY_A ); // phpcs:ignore WordPress.DB
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="egypt-roamer-subscribers.csv"' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fputcsv( $out, [ 'email', 'lang', 'interests', 'source', 'created_at_utc' ], ',', '"', '\\' );
	foreach ( (array) $rows as $r ) {
		fputcsv( $out, array_map( static fn ( $v ) => preg_match( '/^[=+\-@\t\r]/', (string) $v ) ? "'" . $v : $v, $r ), ',', '"', '\\' );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
} );

/* -------------------------------------------------------------------------- */
/* Contact form: [er_contact_form]                                             */
/* -------------------------------------------------------------------------- */

add_shortcode( 'er_contact_form', static function () {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display state only
	$state   = isset( $_GET['er'] ) ? sanitize_key( wp_unslash( $_GET['er'] ) ) : '';
	$privacy = (int) er_settings( 'privacy_page' );
	$privacy = $privacy ? er_translated_post_id( $privacy ) : 0;
	ob_start();
	if ( 'contact-sent' === $state ) {
		echo '<p class="er-form__notice er-form__notice--ok" role="status">' . esc_html__( 'Thank you — your message has reached us. We reply to every message personally.', 'egypt-roamer-core' ) . '</p>';
	} elseif ( 'contact-error' === $state ) {
		echo '<p class="er-form__notice er-form__notice--error" role="alert">' . esc_html__( 'Sorry, your message could not be sent. Please check the form and try again.', 'egypt-roamer-core' ) . '</p>';
	}
	?>
	<form class="er-form" id="contact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="er_contact" />
		<?php echo er_form_guard_fields(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in builder ?>
		<p class="er-form__row">
			<label for="er-c-name"><?php esc_html_e( 'Your name', 'egypt-roamer-core' ); ?></label>
			<input id="er-c-name" type="text" name="name" autocomplete="name" maxlength="100" required />
		</p>
		<p class="er-form__row">
			<label for="er-c-email"><?php esc_html_e( 'Email address', 'egypt-roamer-core' ); ?></label>
			<input id="er-c-email" type="email" name="email" autocomplete="email" maxlength="190" required />
		</p>
		<p class="er-form__row">
			<label for="er-c-topic"><?php esc_html_e( 'Topic', 'egypt-roamer-core' ); ?></label>
			<select id="er-c-topic" name="topic">
				<option value="question"><?php esc_html_e( 'A travel question', 'egypt-roamer-core' ); ?></option>
				<option value="correction"><?php esc_html_e( 'A correction to our content', 'egypt-roamer-core' ); ?></option>
				<option value="partnership"><?php esc_html_e( 'Partnerships', 'egypt-roamer-core' ); ?></option>
				<option value="other"><?php esc_html_e( 'Something else', 'egypt-roamer-core' ); ?></option>
			</select>
		</p>
		<p class="er-form__row">
			<label for="er-c-message"><?php esc_html_e( 'Message', 'egypt-roamer-core' ); ?></label>
			<textarea id="er-c-message" name="message" rows="6" maxlength="5000" required></textarea>
		</p>
		<p class="er-form__note">
			<?php esc_html_e( 'We use your details only to reply to you. Bookings are made with our partners, not with us.', 'egypt-roamer-core' ); ?>
			<?php if ( $privacy && 'publish' === get_post_status( $privacy ) ) : ?>
				<a href="<?php echo esc_url( get_permalink( $privacy ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'egypt-roamer-core' ); ?></a>
			<?php endif; ?>
		</p>
		<button class="btn btn--primary" type="submit"><?php esc_html_e( 'Send message', 'egypt-roamer-core' ); ?></button>
	</form>
	<?php
	return (string) ob_get_clean();
} );

function er_handle_contact(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form, guarded below
	if ( ! er_form_guard_passes( 'contact' ) ) {
		er_form_return( 'contact-error', 'contact' );
	}
	$name    = isset( $_POST['name'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST['name'] ) ), 0, 100 ) : '';
	$raw     = isset( $_POST['email'] ) ? trim( (string) wp_unslash( $_POST['email'] ) ) : '';
	$email   = strtolower( sanitize_email( $raw ) ) === strtolower( $raw ) ? sanitize_email( $raw ) : '';
	$message = isset( $_POST['message'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ), 0, 5000 ) : '';
	$topic   = isset( $_POST['topic'] ) ? sanitize_key( wp_unslash( $_POST['topic'] ) ) : 'other';
	// phpcs:enable
	if ( '' === $name || ! is_email( $email ) || '' === trim( $message ) ) {
		er_form_return( 'contact-error', 'contact' );
	}
	$topic = in_array( $topic, [ 'question', 'correction', 'partnership', 'other' ], true ) ? $topic : 'other';

	$id = wp_insert_post( [
		'post_type'    => 'er_message',
		'post_status'  => 'private',
		'post_title'   => sprintf( '%s — %s', $name, $topic ),
		'post_content' => $message,
		'meta_input'   => [ '_er_email' => $email, '_er_topic' => $topic, '_er_lang' => er_current_lang() ],
	], true );
	if ( is_wp_error( $id ) ) {
		er_form_return( 'contact-error', 'contact' );
	}

	$to = (string) er_settings( 'contact_email' ) ?: (string) get_option( 'admin_email' );
	$sent = wp_mail(
		$to,
		sprintf( '[Egypt Roamer] %s — %s', $topic, $name ),
		$message . "\n\n— " . $name . ' <' . $email . '>',
		[ 'Reply-To: ' . $name . ' <' . $email . '>' ]
	);
	update_post_meta( (int) $id, '_er_mailed', $sent ? 1 : 0 );
	er_form_return( 'contact-sent', 'contact' );
}
add_action( 'admin_post_nopriv_er_contact', 'er_handle_contact' );
add_action( 'admin_post_er_contact', 'er_handle_contact' );

/** Show the sender and delivery status on stored messages. */
add_action( 'add_meta_boxes_er_message', static function () {
	add_meta_box( 'er_message_meta', __( 'Sender', 'egypt-roamer-core' ), static function ( $post ) {
		$email = (string) get_post_meta( $post->ID, '_er_email', true );
		printf( '<p><a href="mailto:%1$s">%1$s</a></p><p>%2$s</p>', esc_attr( $email ), get_post_meta( $post->ID, '_er_mailed', true ) ? esc_html__( 'Email notification sent.', 'egypt-roamer-core' ) : esc_html__( 'Email notification FAILED — check the mail delivery setup.', 'egypt-roamer-core' ) );
	}, 'er_message', 'side' );
} );

/* -------------------------------------------------------------------------- */
/* Privacy: Tools → Export / Erase Personal Data                               */
/* -------------------------------------------------------------------------- */

add_filter( 'wp_privacy_personal_data_exporters', static function ( $exporters ) {
	$exporters['egypt-roamer'] = [
		'exporter_friendly_name' => __( 'Egypt Roamer newsletter & contact messages', 'egypt-roamer-core' ),
		'callback'               => static function ( $email ) {
			global $wpdb;
			$items = [];
			$sub   = $wpdb->get_row( $wpdb->prepare( 'SELECT email, lang, interests, source, created_at FROM ' . er_subscribers_table() . ' WHERE email = %s', $email ), ARRAY_A ); // phpcs:ignore WordPress.DB
			if ( $sub ) {
				$items[] = [ 'group_id' => 'er-newsletter', 'group_label' => __( 'Newsletter', 'egypt-roamer-core' ), 'item_id' => 'er-sub-' . md5( $email ), 'data' => array_map( static fn ( $k, $v ) => [ 'name' => $k, 'value' => $v ], array_keys( $sub ), $sub ) ];
			}
			foreach ( get_posts( [ 'post_type' => 'er_message', 'post_status' => 'any', 'numberposts' => 100, 'meta_key' => '_er_email', 'meta_value' => $email ] ) as $m ) {
				$items[] = [ 'group_id' => 'er-messages', 'group_label' => __( 'Contact messages', 'egypt-roamer-core' ), 'item_id' => 'er-msg-' . $m->ID, 'data' => [ [ 'name' => __( 'Date', 'egypt-roamer-core' ), 'value' => $m->post_date ], [ 'name' => __( 'Message', 'egypt-roamer-core' ), 'value' => $m->post_content ] ] ];
			}
			return [ 'data' => $items, 'done' => true ];
		},
	];
	return $exporters;
} );

add_filter( 'wp_privacy_personal_data_erasers', static function ( $erasers ) {
	$erasers['egypt-roamer'] = [
		'eraser_friendly_name' => __( 'Egypt Roamer newsletter & contact messages', 'egypt-roamer-core' ),
		'callback'             => static function ( $email ) {
			global $wpdb;
			$removed = (int) $wpdb->delete( er_subscribers_table(), [ 'email' => $email ], [ '%s' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			foreach ( get_posts( [ 'post_type' => 'er_message', 'post_status' => 'any', 'numberposts' => 100, 'fields' => 'ids', 'meta_key' => '_er_email', 'meta_value' => $email ] ) as $id ) {
				$removed += wp_delete_post( $id, true ) ? 1 : 0;
			}
			return [ 'items_removed' => $removed > 0, 'items_retained' => false, 'messages' => [], 'done' => true ];
		},
	];
	return $erasers;
} );
