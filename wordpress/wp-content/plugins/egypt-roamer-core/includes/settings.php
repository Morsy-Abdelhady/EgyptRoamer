<?php
/**
 * Plugin settings (one option array) and the Egypt Roamer dashboard with
 * content/affiliate health checks.
 */

defined( 'ABSPATH' ) || exit;

function er_settings_fields(): array {
	return [
		'h_disc'             => [ 'type' => 'heading', 'label' => __( 'Affiliate disclosure', 'egypt-roamer-core' ) ],
		'disclosure_page'    => [ 'type' => 'post', 'post_type' => 'page', 'label' => __( 'Affiliate Disclosure page', 'egypt-roamer-core' ) ],
		'disclosure_text'    => [ 'type' => 'textarea', 'label' => __( 'Short disclosure shown next to affiliate offers', 'egypt-roamer-core' ), 'default' => 'Egypt Roamer is independent. When you book through our partner links we may earn a small commission, at no extra cost to you. It never influences what we recommend.' ],
		'h_redirect'         => [ 'type' => 'heading', 'label' => __( 'Affiliate redirects (/go/)', 'egypt-roamer-core' ) ],
		'redirect_status'    => [ 'type' => 'select', 'label' => __( 'Redirect status code', 'egypt-roamer-core' ), 'options' => [ '302' => '302 Found', '307' => '307 Temporary Redirect' ], 'default' => '302' ],
		'retention_days'     => [ 'type' => 'number', 'label' => __( 'Keep click records for (days, 0 = forever)', 'egypt-roamer-core' ), 'step' => '1', 'default' => 730 ],
		'h_analytics'        => [ 'type' => 'heading', 'label' => __( 'Analytics', 'egypt-roamer-core' ) ],
		'gtm_id'             => [ 'type' => 'text', 'label' => __( 'Google Tag Manager container ID', 'egypt-roamer-core' ), 'help' => __( 'Format GTM-XXXXXXX. Configure GA4 inside GTM. Leave empty to load no tag. Do not also add GA4 through another plugin.', 'egypt-roamer-core' ) ],
		'consent_default'    => [ 'type' => 'select', 'label' => __( 'Google Consent Mode default', 'egypt-roamer-core' ), 'options' => [ 'denied' => __( 'Denied until the visitor consents (recommended for EU/UK traffic)', 'egypt-roamer-core' ), 'granted' => __( 'Granted', 'egypt-roamer-core' ), 'off' => __( 'Do not set defaults (a consent plugin sets them)', 'egypt-roamer-core' ) ], 'default' => 'denied' ],
		'h_leads'            => [ 'type' => 'heading', 'label' => __( 'Lead capture', 'egypt-roamer-core' ) ],
		'contact_email'      => [ 'type' => 'text', 'label' => __( 'Send contact messages to (email)', 'egypt-roamer-core' ), 'help' => __( 'Messages are always stored under Egypt Roamer → Contact messages, even if email delivery fails.', 'egypt-roamer-core' ) ],
		'privacy_page'       => [ 'type' => 'post', 'post_type' => 'page', 'label' => __( 'Privacy Policy page (linked from forms)', 'egypt-roamer-core' ) ],
		'fluentcrm_list'     => [ 'type' => 'number', 'label' => __( 'FluentCRM list ID for newsletter sign-ups (if FluentCRM is active)', 'egypt-roamer-core' ), 'step' => '1' ],
		'h_seo'              => [ 'type' => 'heading', 'label' => __( 'SEO guards', 'egypt-roamer-core' ) ],
		'legacy_redirects'   => [ 'type' => 'checkbox', 'label' => __( 'Redirect legacy static-site paths (/guide/*, /about …) with 301', 'egypt-roamer-core' ), 'default' => 1 ],
		'place_schema'       => [ 'type' => 'checkbox', 'label' => __( 'Add TouristDestination structured data to destination pages', 'egypt-roamer-core' ), 'default' => 1 ],
	];
}

/** Current settings merged with defaults. */
function er_settings( ?string $key = null ) {
	static $settings = null;
	if ( null === $settings ) {
		$stored   = get_option( 'er_settings', [] );
		$settings = [];
		foreach ( er_settings_fields() as $k => $field ) {
			if ( 'heading' === $field['type'] ) {
				continue;
			}
			$settings[ $k ] = is_array( $stored ) && array_key_exists( $k, $stored ) ? $stored[ $k ] : ( $field['default'] ?? '' );
		}
	}
	return null === $key ? $settings : ( $settings[ $key ] ?? null );
}

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Settings', 'egypt-roamer-core' ), __( 'Settings', 'egypt-roamer-core' ), 'manage_options', 'er-settings', 'er_render_settings_page' );
}, 30 );

add_action( 'admin_post_er_save_settings', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_save_settings' );
	$input = isset( $_POST['er_settings'] ) && is_array( $_POST['er_settings'] ) ? wp_unslash( $_POST['er_settings'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field
	$clean = [];
	foreach ( er_settings_fields() as $k => $field ) {
		if ( 'heading' !== $field['type'] ) {
			$clean[ $k ] = er_sanitize_field( $field, $input[ $k ] ?? '' );
		}
	}
	$clean['gtm_id'] = preg_match( '/^GTM-[A-Z0-9]{4,12}$/', strtoupper( (string) $clean['gtm_id'] ) ) ? strtoupper( $clean['gtm_id'] ) : '';
	$clean['contact_email'] = sanitize_email( (string) $clean['contact_email'] );
	update_option( 'er_settings', $clean, false );
	wp_safe_redirect( add_query_arg( [ 'page' => 'er-settings', 'updated' => 1 ], admin_url( 'admin.php' ) ) );
	exit;
} );

function er_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap er-wrap"><h1>' . esc_html__( 'Egypt Roamer settings', 'egypt-roamer-core' ) . '</h1>';
	if ( isset( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'egypt-roamer-core' ) . '</p></div>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="er-fields er-fields--settings">';
	wp_nonce_field( 'er_save_settings' );
	echo '<input type="hidden" name="action" value="er_save_settings" />';
	$values = er_settings();
	foreach ( er_settings_fields() as $k => $field ) {
		if ( 'heading' === $field['type'] ) {
			echo '<h2 class="er-fields__heading">' . esc_html( $field['label'] ) . '</h2>';
			continue;
		}
		er_render_field( 'er-s-' . $k, 'er_settings[' . $k . ']', $field, $values[ $k ] );
	}
	submit_button();
	echo '</form></div>';
}

/* -------------------------------------------------------------------------- */
/* Dashboard                                                                   */
/* -------------------------------------------------------------------------- */

/** Health checks an owner should see without digging. Returns [severity, message, edit link][]. */
function er_health_checks(): array {
	$issues = [];

	// Published editorial pages still marked noindex.
	$noindex = get_posts( [
		'post_type'   => array_keys( er_public_types() ),
		'post_status' => 'publish',
		'numberposts' => 50,
		'meta_query'  => [ [ 'key' => '_er_indexable', 'compare' => 'NOT EXISTS' ] ],
		'lang'        => '',
	] );
	foreach ( $noindex as $p ) {
		$issues[] = [ 'info', sprintf( /* translators: %s: title */ __( '“%s” is published but not marked “Ready to index” (noindex, not in sitemap).', 'egypt-roamer-core' ), $p->post_title ), get_edit_post_link( $p->ID, 'raw' ) ];
	}

	// Providers without allowed domains can never redirect.
	foreach ( get_posts( [ 'post_type' => 'er_provider', 'post_status' => 'publish', 'numberposts' => 200 ] ) as $p ) {
		if ( ! er_provider_domains( $p->ID ) ) {
			$issues[] = [ 'error', sprintf( /* translators: %s: provider */ __( 'Provider “%s” has no allowed redirect domains — its offers cannot redirect.', 'egypt-roamer-core' ), $p->post_title ), get_edit_post_link( $p->ID, 'raw' ) ];
		}
	}

	// Offers whose link fails validation, or whose schedule has ended.
	$today = current_time( 'Y-m-d' );
	foreach ( get_posts( [ 'post_type' => 'er_offer', 'post_status' => 'publish', 'numberposts' => 500 ] ) as $p ) {
		$check = er_offer_destination_url( $p->ID );
		if ( is_wp_error( $check ) ) {
			$issues[] = [ 'error', sprintf( /* translators: 1: offer, 2: reason */ __( 'Offer “%1$s” cannot redirect: %2$s', 'egypt-roamer-core' ), $p->post_title, $check->get_error_message() ), get_edit_post_link( $p->ID, 'raw' ) ];
			continue;
		}
		$end = (string) get_post_meta( $p->ID, '_er_end', true );
		if ( $end && $end < $today ) {
			$issues[] = [ 'warning', sprintf( /* translators: %s: offer */ __( 'Offer “%s” has ended and is no longer shown.', 'egypt-roamer-core' ), $p->post_title ), get_edit_post_link( $p->ID, 'raw' ) ];
		} elseif ( $end && $end <= gmdate( 'Y-m-d', strtotime( $today . ' +14 days' ) ) ) {
			$issues[] = [ 'warning', sprintf( /* translators: 1: offer, 2: date */ __( 'Offer “%1$s” ends on %2$s.', 'egypt-roamer-core' ), $p->post_title, $end ), get_edit_post_link( $p->ID, 'raw' ) ];
		}
		$checked = (string) get_post_meta( $p->ID, '_er_price_checked', true );
		if ( '' !== (string) get_post_meta( $p->ID, '_er_price_from', true ) && ( ! $checked || $checked < gmdate( 'Y-m-d', strtotime( $today . ' -90 days' ) ) ) ) {
			$issues[] = [ 'warning', sprintf( /* translators: %s: offer */ __( 'Offer “%s” shows a price that was not checked in the last 90 days — re-check it or clear it.', 'egypt-roamer-core' ), $p->post_title ), get_edit_post_link( $p->ID, 'raw' ) ];
		}
	}

	if ( ! er_settings( 'disclosure_page' ) ) {
		$issues[] = [ 'error', __( 'No Affiliate Disclosure page is selected in Settings.', 'egypt-roamer-core' ), admin_url( 'admin.php?page=er-settings' ) ];
	}
	if ( ! er_seo_plugin_active() ) {
		$issues[] = [ 'warning', __( 'No SEO plugin detected. Rank Math SEO is the planned SEO layer (titles, descriptions, canonicals, sitemap, schema).', 'egypt-roamer-core' ), admin_url( 'plugins.php' ) ];
	}
	return $issues;
}

function er_render_dashboard_page(): void {
	$clicks30 = er_clicks_total( gmdate( 'Y-m-d', strtotime( '-29 days' ) ), gmdate( 'Y-m-d' ) );
	$counts   = [];
	foreach ( [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_guide', 'er_provider', 'er_offer' ] as $type ) {
		$counts[ $type ] = (int) ( wp_count_posts( $type )->publish ?? 0 );
	}
	echo '<div class="wrap er-wrap"><h1>' . esc_html__( 'Egypt Roamer', 'egypt-roamer-core' ) . '</h1>';
	echo '<div class="er-cards">';
	printf( '<div class="er-card"><b>%s</b><span>%s</span></div>', esc_html( number_format_i18n( $clicks30 ) ), esc_html__( 'affiliate clicks, last 30 days', 'egypt-roamer-core' ) );
	foreach ( $counts as $type => $n ) {
		$obj = get_post_type_object( $type );
		printf( '<a class="er-card" href="%s"><b>%s</b><span>%s</span></a>', esc_url( admin_url( 'edit.php?post_type=' . $type ) ), esc_html( number_format_i18n( $n ) ), esc_html( $obj ? $obj->labels->name : $type ) );
	}
	echo '</div>';

	echo '<h2>' . esc_html__( 'Health checks', 'egypt-roamer-core' ) . '</h2>';
	$issues = er_health_checks();
	if ( ! $issues ) {
		echo '<p>' . esc_html__( 'Nothing needs attention.', 'egypt-roamer-core' ) . '</p>';
	} else {
		echo '<ul class="er-issues">';
		foreach ( $issues as [ $sev, $msg, $link ] ) {
			printf( '<li class="er-issue er-issue--%s"><span>%s</span> <a href="%s">%s</a></li>', esc_attr( $sev ), esc_html( $msg ), esc_url( $link ), esc_html__( 'Fix', 'egypt-roamer-core' ) );
		}
		echo '</ul>';
	}
	echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=er-reports' ) ) . '">' . esc_html__( 'Open click reports', 'egypt-roamer-core' ) . '</a></p>';
	echo '</div>';
}
