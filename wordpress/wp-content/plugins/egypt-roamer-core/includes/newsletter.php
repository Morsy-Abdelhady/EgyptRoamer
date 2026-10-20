<?php
/**
 * Newsletter double opt-in and welcome email (owner, 20 Oct: "do everything on your side"), for the sign-ups stored in
 * {prefix}er_subscribers when FluentCRM is not active (includes/leads.php).
 *
 * - After sign-up: one email to the address typed, in the visitor's language, with a link to confirm. Nothing else is
 *   ever sent to an address that did not confirm.
 * - After confirming: a welcome email with the site's ready-made routes (the 7, 10 and 14-day guides), the Trip
 *   builder and Trips & tours, and a one-click unsubscribe link (which deletes the address).
 * - Mail goes through wp_mail (the host's mail); delivery depends on the site's mail setup.
 */

defined( 'ABSPATH' ) || exit;

/** Add the confirmation columns (dbDelta through er_install_tables). */
add_action( 'er_install_tables', static function (): void {
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
		token varchar(64) NOT NULL DEFAULT '',
		confirmed_at datetime NULL DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email)
	) {$wpdb->get_charset_collate()};" );
}, 20 );

/** The plugin's strings in a language (REST and admin-post run in the default locale). */
function er_newsletter_use_language( string $lang ): void {
	$GLOBALS['er_nl_lang'] = $lang;
	if ( ! $lang || ! function_exists( 'PLL' ) || ! PLL()->model->get_language( $lang ) ) {
		return;
	}
	$mo = ER_CORE_DIR . 'languages/egypt-roamer-core-' . PLL()->model->get_language( $lang )->locale . '.mo';
	unload_textdomain( 'egypt-roamer-core' );
	if ( file_exists( substr( $mo, 0, -3 ) . '.l10n.php' ) ) {
		load_textdomain( 'egypt-roamer-core', $mo );
	} else {
		load_plugin_textdomain( 'egypt-roamer-core', false, dirname( plugin_basename( ER_CORE_FILE ) ) . '/languages' );
	}
}

function er_newsletter_home( string $lang ): string {
	return function_exists( 'pll_home_url' ) && $lang ? (string) pll_home_url( $lang ) : home_url( '/' );
}

/** A plain, readable HTML email: heading, paragraphs, links as buttons, the footer line. */
function er_newsletter_html( string $title, array $paras, array $links, string $foot ): string {
	$rtl = 'ar' === ( $GLOBALS['er_nl_lang'] ?? '' );
	$h   = '<!doctype html><html' . ( $rtl ? ' dir="rtl" lang="ar"' : '' ) . '><body style="margin:0;' . ( $rtl ? 'direction:rtl;text-align:right;' : '' ) . 'background:#f8f5ed;font-family:Arial,Helvetica,sans-serif;color:#101820">';
	$h .= '<div style="max-width:560px;margin:0 auto;padding:28px 22px"><p style="font:600 13px Arial;letter-spacing:.18em;color:#a8871f;margin:0 0 14px">EGYPT ROAMER</p>';
	$h .= '<h1 style="font:500 26px Georgia,serif;margin:0 0 16px">' . esc_html( $title ) . '</h1>';
	foreach ( $paras as $p ) {
		$h .= '<p style="font-size:15px;line-height:1.6;margin:0 0 14px">' . esc_html( $p ) . '</p>';
	}
	foreach ( $links as [ $label, $url, $primary ] ) {
		$h .= '<p style="margin:0 0 10px"><a href="' . esc_url( $url ) . '" style="display:inline-block;padding:11px 18px;border-radius:999px;text-decoration:none;font-weight:600;font-size:14px;' . ( $primary ? 'background:#c9a227;color:#101820' : 'border:1px solid #c9a227;color:#0e3a5b' ) . '">' . esc_html( $label ) . '</a></p>';
	}
	return $h . '<p style="font-size:12px;line-height:1.5;color:#6b6b6b;margin:26px 0 0">' . $foot . '</p></div></body></html>';
}

function er_newsletter_send( string $to, string $subject, string $html ): bool {
	$headers = [ 'Content-Type: text/html; charset=UTF-8', 'From: Egypt Roamer <' . sanitize_email( (string) get_option( 'admin_email' ) ) . '>' ];
	return (bool) wp_mail( $to, $subject, $html, $headers );
}

/** After a sign-up stored in the table: the confirmation email (only once per address until confirmed). */
add_action( 'er_newsletter_subscribed', static function ( string $email, array $interests, string $lang ): void {
	if ( function_exists( 'FluentCrmApi' ) ) {
		return; // FluentCRM sends its own double opt-in
	}
	global $wpdb;
	$table = er_subscribers_table();
	$row   = $wpdb->get_row( $wpdb->prepare( "SELECT id, token, confirmed_at FROM {$table} WHERE email = %s", $email ) ); // phpcs:ignore WordPress.DB
	if ( ! $row || $row->confirmed_at ) {
		return;
	}
	$token = $row->token ?: wp_generate_password( 32, false, false );
	if ( ! $row->token ) {
		$wpdb->update( $table, [ 'token' => $token ], [ 'id' => (int) $row->id ] ); // phpcs:ignore WordPress.DB
	} elseif ( get_transient( 'er_nl_sent_' . (int) $row->id ) ) {
		return; // one confirmation email per address per day
	}
	er_newsletter_use_language( $lang );
	$confirm = add_query_arg( [ 'action' => 'er_nl_confirm', 'i' => (int) $row->id, 't' => $token ], admin_url( 'admin-post.php' ) );
	$html    = er_newsletter_html(
		__( 'Please confirm your subscription', 'egypt-roamer-core' ),
		[ __( 'Someone, hopefully you, asked to receive the Egypt Roamer letter at this address. Press the button to confirm.', 'egypt-roamer-core' ), __( 'If it was not you, ignore this email: nothing more will be sent.', 'egypt-roamer-core' ) ],
		[ [ __( 'Confirm my subscription', 'egypt-roamer-core' ), $confirm, true ] ],
		esc_html__( 'Egypt Roamer is an independent travel publication about Egypt.', 'egypt-roamer-core' )
	);
	if ( er_newsletter_send( $email, __( 'Confirm your Egypt Roamer subscription', 'egypt-roamer-core' ), $html ) ) {
		set_transient( 'er_nl_sent_' . (int) $row->id, 1, DAY_IN_SECONDS );
		$GLOBALS['er_nl_pending'] = true;
	}
}, 10, 3 );

// The sign-up message says "check your inbox" when the confirmation email went out.
add_filter( 'wp_redirect', static function ( $location ) {
	if ( ! empty( $GLOBALS['er_nl_pending'] ) && str_contains( (string) $location, 'er=subscribed' ) && ! str_contains( (string) $location, 'er=subscribed-pending' ) ) {
		$location = str_replace( 'er=subscribed', 'er=subscribed-pending', (string) $location );
	}
	return $location;
} );

/** The subscriber row behind a link (id and token must match). */
function er_newsletter_row_from_link(): ?object {
	global $wpdb;
	$id = isset( $_GET['i'] ) ? absint( $_GET['i'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- signed by the token
	$t  = isset( $_GET['t'] ) ? sanitize_key( wp_unslash( $_GET['t'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $id || strlen( $t ) < 20 ) {
		return null;
	}
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . er_subscribers_table() . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB
	return $row && $row->token && hash_equals( strtolower( (string) $row->token ), $t ) ? $row : null;
}

/** Confirm: record it, send the welcome email once, back to the site with a message. */
function er_newsletter_confirm(): void {
	global $wpdb;
	$row = er_newsletter_row_from_link();
	if ( ! $row ) {
		wp_safe_redirect( add_query_arg( 'er', 'subscribe-error', home_url( '/' ) ) . '#newsletter', 303 );
		exit;
	}
	$lang = (string) $row->lang;
	$home = er_newsletter_home( $lang );
	if ( ! $row->confirmed_at ) {
		$wpdb->update( er_subscribers_table(), [ 'confirmed_at' => current_time( 'mysql', true ) ], [ 'id' => (int) $row->id ] ); // phpcs:ignore WordPress.DB
		er_newsletter_use_language( $lang );
		$links = [];
		foreach ( [ '7-days-in-egypt-itinerary', '10-days-in-egypt-itinerary', '14-days-in-egypt-itinerary' ] as $slug ) {
			$en = get_posts( [ 'post_type' => 'er_guide', 'name' => $slug, 'post_status' => 'publish', 'numberposts' => 1, 'lang' => 'en' ] );
			$id = $en ? (int) $en[0]->ID : 0;
			$id = $id && function_exists( 'pll_get_post' ) && $lang ? ( (int) pll_get_post( $id, $lang ) ?: $id ) : $id;
			if ( $id && 'publish' === get_post_status( $id ) ) {
				$links[] = [ html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ), (string) get_permalink( $id ), false ];
			}
		}
		$links[] = [ __( 'Plan your route in the Trip builder', 'egypt-roamer-core' ), $home . '#planner', true ];
		// The archive in the subscriber's language (admin-post has no current language, so the link is built).
		$trips = get_post_type_archive_link( 'er_experience' );
		if ( $trips && function_exists( 'PLL' ) && $lang && PLL()->model->get_language( $lang ) ) {
			$trips = PLL()->links_model->switch_language_in_link( (string) $trips, PLL()->model->get_language( $lang ) );
		}
		if ( $trips ) {
			$links[] = [ __( 'Compare the trips you can book', 'egypt-roamer-core' ), (string) $trips, false ];
		}
		$unsub = add_query_arg( [ 'action' => 'er_nl_unsub', 'i' => (int) $row->id, 't' => (string) $row->token ], admin_url( 'admin-post.php' ) );
		$html  = er_newsletter_html(
			__( 'Welcome to Egypt Roamer', 'egypt-roamer-core' ),
			[ __( 'Thank you for confirming. We write only when there is something worth reading: new guides and new trips.', 'egypt-roamer-core' ), __( 'To start, three ready-made routes: one week, ten days or two weeks, each with the trips you can book on the way.', 'egypt-roamer-core' ) ],
			$links,
			esc_html__( 'You receive this because you confirmed your subscription on egyptroamer.com.', 'egypt-roamer-core' ) . ' <a href="' . esc_url( $unsub ) . '" style="color:#6b6b6b">' . esc_html__( 'Unsubscribe', 'egypt-roamer-core' ) . '</a>'
		);
		er_newsletter_send( (string) $row->email, __( 'Welcome to Egypt Roamer: three routes to start', 'egypt-roamer-core' ), $html );
	}
	wp_safe_redirect( add_query_arg( 'er', 'subscribe-confirmed', $home ) . '#newsletter', 303 );
	exit;
}
add_action( 'admin_post_nopriv_er_nl_confirm', 'er_newsletter_confirm' );
add_action( 'admin_post_er_nl_confirm', 'er_newsletter_confirm' );

/** Unsubscribe: the address is deleted. */
function er_newsletter_unsubscribe(): void {
	global $wpdb;
	$row = er_newsletter_row_from_link();
	if ( $row ) {
		$wpdb->delete( er_subscribers_table(), [ 'id' => (int) $row->id ], [ '%d' ] ); // phpcs:ignore WordPress.DB
	}
	wp_safe_redirect( add_query_arg( 'er', $row ? 'unsubscribed' : 'subscribe-error', er_newsletter_home( $row ? (string) $row->lang : '' ) ) . '#newsletter', 303 );
	exit;
}
add_action( 'admin_post_nopriv_er_nl_unsub', 'er_newsletter_unsubscribe' );
add_action( 'admin_post_er_nl_unsub', 'er_newsletter_unsubscribe' );
