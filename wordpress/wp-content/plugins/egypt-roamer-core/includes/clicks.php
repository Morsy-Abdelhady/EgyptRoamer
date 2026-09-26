<?php
/**
 * Affiliate click store. One row per outbound click, with business context
 * only — no IP address, no user agent, no cookies, no user ID.
 */

defined( 'ABSPATH' ) || exit;

function er_clicks_table(): string {
	global $wpdb;
	return $wpdb->prefix . 'er_clicks';
}

function er_install_tables(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = er_clicks_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		clicked_at datetime NOT NULL,
		offer_id bigint(20) unsigned NOT NULL DEFAULT 0,
		provider_id bigint(20) unsigned NOT NULL DEFAULT 0,
		destination_id bigint(20) unsigned NOT NULL DEFAULT 0,
		tour_id bigint(20) unsigned NOT NULL DEFAULT 0,
		experience_id bigint(20) unsigned NOT NULL DEFAULT 0,
		activity_id bigint(20) unsigned NOT NULL DEFAULT 0,
		source_post_id bigint(20) unsigned NOT NULL DEFAULT 0,
		source_path varchar(191) NOT NULL DEFAULT '',
		placement varchar(64) NOT NULL DEFAULT '',
		cta varchar(100) NOT NULL DEFAULT '',
		utm_source varchar(100) NOT NULL DEFAULT '',
		utm_medium varchar(100) NOT NULL DEFAULT '',
		utm_campaign varchar(100) NOT NULL DEFAULT '',
		lang varchar(12) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY clicked_at (clicked_at),
		KEY offer_id (offer_id),
		KEY provider_id (provider_id),
		KEY source_post_id (source_post_id)
	) {$charset};" );
	do_action( 'er_install_tables' );
	update_option( 'er_core_db_version', ER_CORE_DB_VERSION, false );
}

/** Insert one click. $row keys match the table columns (except id). */
function er_log_click( array $row ): bool {
	global $wpdb;
	$defaults = [
		'clicked_at'     => current_time( 'mysql', true ),
		'offer_id'       => 0,
		'provider_id'    => 0,
		'destination_id' => 0,
		'tour_id'        => 0,
		'experience_id'  => 0,
		'activity_id'    => 0,
		'source_post_id' => 0,
		'source_path'    => '',
		'placement'      => '',
		'cta'            => '',
		'utm_source'     => '',
		'utm_medium'     => '',
		'utm_campaign'   => '',
		'lang'           => '',
	];
	$row = array_intersect_key( wp_parse_args( $row, $defaults ), $defaults );
	foreach ( [ 'source_path' => 191, 'placement' => 64, 'cta' => 100, 'utm_source' => 100, 'utm_medium' => 100, 'utm_campaign' => 100, 'lang' => 12 ] as $col => $max ) {
		$row[ $col ] = mb_substr( (string) $row[ $col ], 0, $max );
	}
	$formats = [ '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ];
	return (bool) $wpdb->insert( er_clicks_table(), $row, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/** WHERE clause for a UTC date range (inclusive, Y-m-d). */
function er_clicks_range_sql( string $from, string $to ): string {
	global $wpdb;
	return $wpdb->prepare( 'clicked_at >= %s AND clicked_at < %s', $from . ' 00:00:00', gmdate( 'Y-m-d', strtotime( $to . ' +1 day' ) ) . ' 00:00:00' );
}

function er_clicks_total( string $from, string $to ): int {
	global $wpdb;
	$table = er_clicks_table();
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE " . er_clicks_range_sql( $from, $to ) ); // phpcs:ignore WordPress.DB -- table name is internal, range is prepared
}

/**
 * Clicks grouped by one dimension. $dimension is whitelisted.
 * Returns [ [ 'k' => value, 'n' => count ], … ] ordered by count desc.
 */
function er_clicks_by( string $dimension, string $from, string $to, int $limit = 50 ): array {
	global $wpdb;
	$allowed = [ 'offer_id', 'provider_id', 'destination_id', 'tour_id', 'experience_id', 'activity_id', 'source_post_id', 'source_path', 'placement', 'cta', 'utm_campaign', 'utm_source', 'utm_medium', 'lang', 'day' ];
	if ( ! in_array( $dimension, $allowed, true ) ) {
		return [];
	}
	$table = er_clicks_table();
	$col   = 'day' === $dimension ? 'DATE(clicked_at)' : $dimension;
	$order = 'day' === $dimension ? 'k ASC' : 'n DESC';
	$sql   = "SELECT {$col} AS k, COUNT(*) AS n FROM {$table} WHERE " . er_clicks_range_sql( $from, $to ) . " GROUP BY k ORDER BY {$order} LIMIT %d";
	return $wpdb->get_results( $wpdb->prepare( $sql, $limit ), ARRAY_A ) ?: []; // phpcs:ignore WordPress.DB -- identifiers whitelisted above
}

/** Rows for CSV export. */
function er_clicks_rows( string $from, string $to, int $limit = 50000 ): array {
	global $wpdb;
	$table = er_clicks_table();
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE " . er_clicks_range_sql( $from, $to ) . ' ORDER BY clicked_at ASC LIMIT %d', $limit ), ARRAY_A ) ?: []; // phpcs:ignore WordPress.DB
}

/* Retention: purge old rows daily (WP-Cron; GoDaddy runs WP-Cron on page loads). */
add_action( 'init', static function () {
	if ( ! wp_next_scheduled( 'er_purge_clicks' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'er_purge_clicks' );
	}
} );
add_action( 'er_purge_clicks', static function () {
	global $wpdb;
	$days = (int) er_settings( 'retention_days' );
	if ( $days <= 0 ) {
		return;
	}
	$table = er_clicks_table();
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE clicked_at < %s", gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
} );
