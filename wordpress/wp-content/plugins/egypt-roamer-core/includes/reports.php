<?php
/**
 * Click reports for marketing decisions.
 *
 * Page traffic is NOT tracked here (that would duplicate GA4). Instead the
 * owner imports a GA4 "Pages and screens" CSV; the report joins its views with
 * logged clicks to find pages that get traffic but no clicks, and the reverse.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Click reports', 'egypt-roamer-core' ), __( 'Click reports', 'egypt-roamer-core' ), 'edit_others_posts', 'er-reports', 'er_render_reports_page' );
}, 20 );

/** Selected range from the query string, defaulting to the last 30 days. */
function er_report_range(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only report filters
	$days = isset( $_GET['days'] ) ? absint( $_GET['days'] ) : 30;
	$from = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
	$to   = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
	// phpcs:enable
	$valid = static fn ( $d ) => (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d );
	if ( ! $valid( $from ) || ! $valid( $to ) || $from > $to ) {
		$days = in_array( $days, [ 7, 30, 90, 365 ], true ) ? $days : 30;
		$to   = gmdate( 'Y-m-d' );
		$from = gmdate( 'Y-m-d', strtotime( '-' . ( $days - 1 ) . ' days' ) );
	}
	return [ $from, $to ];
}

/** Human label for a grouped dimension value. */
function er_report_label( string $dimension, $value ): string {
	if ( str_ends_with( $dimension, '_id' ) ) {
		$id = (int) $value;
		if ( ! $id ) {
			return '—';
		}
		$title = get_the_title( $id );
		return '' !== $title ? $title : '#' . $id;
	}
	return '' === (string) $value ? '—' : (string) $value;
}

function er_render_report_table( string $title, string $dimension, string $from, string $to, int $total, int $limit = 10 ): void {
	$rows = er_clicks_by( $dimension, $from, $to, $limit );
	echo '<section class="er-report"><h3>' . esc_html( $title ) . '</h3>';
	if ( ! $rows ) {
		echo '<p class="description">' . esc_html__( 'No clicks in this period.', 'egypt-roamer-core' ) . '</p></section>';
		return;
	}
	echo '<table class="widefat striped"><tbody>';
	foreach ( $rows as $row ) {
		$share = $total ? round( 100 * $row['n'] / $total ) : 0;
		$label = er_report_label( $dimension, $row['k'] );
		if ( 'source_path' === $dimension ) {
			$label = $row['k'];
		}
		printf(
			'<tr><td>%s</td><td class="er-num">%s</td><td class="er-bar"><i style="width:%d%%"></i></td></tr>',
			esc_html( $label ),
			esc_html( number_format_i18n( (int) $row['n'] ) ),
			(int) $share
		);
	}
	echo '</tbody></table></section>';
}

function er_render_reports_page(): void {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}
	[ $from, $to ] = er_report_range();
	$total         = er_clicks_total( $from, $to );
	$base          = admin_url( 'admin.php?page=er-reports' );

	echo '<div class="wrap er-wrap"><h1>' . esc_html__( 'Affiliate click reports', 'egypt-roamer-core' ) . '</h1>';

	// Range picker.
	echo '<form method="get" class="er-range"><input type="hidden" name="page" value="er-reports" />';
	foreach ( [ 7, 30, 90, 365 ] as $d ) {
		/* translators: %d: number of days */
		printf( '<a class="button" href="%s">%s</a> ', esc_url( add_query_arg( 'days', $d, $base ) ), esc_html( sprintf( __( 'Last %d days', 'egypt-roamer-core' ), $d ) ) );
	}
	printf(
		'<label>%s <input type="date" name="from" value="%s" /></label> <label>%s <input type="date" name="to" value="%s" /></label> <button class="button">%s</button>',
		esc_html__( 'From', 'egypt-roamer-core' ),
		esc_attr( $from ),
		esc_html__( 'to', 'egypt-roamer-core' ),
		esc_attr( $to ),
		esc_html__( 'Apply', 'egypt-roamer-core' )
	);
	if ( current_user_can( 'manage_options' ) ) {
		printf(
			' <a class="button" href="%s">%s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=er_export_clicks&from=' . $from . '&to=' . $to ), 'er_export_clicks' ) ),
			esc_html__( 'Export CSV', 'egypt-roamer-core' )
		);
	}
	echo '</form>';

	printf(
		'<div class="er-cards"><div class="er-card"><b>%s</b><span>%s</span></div></div>',
		esc_html( number_format_i18n( $total ) ),
		/* translators: 1: from date, 2: to date */
		esc_html( sprintf( __( 'affiliate clicks, %1$s – %2$s (UTC, bots and prefetches excluded)', 'egypt-roamer-core' ), $from, $to ) )
	);

	// Over time.
	$days = er_clicks_by( 'day', $from, $to, 400 );
	if ( $days ) {
		$max = max( array_map( static fn ( $r ) => (int) $r['n'], $days ) );
		echo '<section class="er-report er-report--wide"><h3>' . esc_html__( 'Clicks over time', 'egypt-roamer-core' ) . '</h3><div class="er-timeline" role="img" aria-label="' . esc_attr__( 'Daily clicks', 'egypt-roamer-core' ) . '">';
		foreach ( $days as $r ) {
			printf( '<i style="height:%d%%" title="%s: %d"></i>', (int) round( 100 * $r['n'] / max( 1, $max ) ), esc_attr( $r['k'] ), (int) $r['n'] );
		}
		echo '</div></section>';
	}

	echo '<div class="er-reports">';
	er_render_report_table( __( 'By provider', 'egypt-roamer-core' ), 'provider_id', $from, $to, $total );
	er_render_report_table( __( 'By offer', 'egypt-roamer-core' ), 'offer_id', $from, $to, $total );
	er_render_report_table( __( 'By destination', 'egypt-roamer-core' ), 'destination_id', $from, $to, $total );
	er_render_report_table( __( 'By tour', 'egypt-roamer-core' ), 'tour_id', $from, $to, $total );
	er_render_report_table( __( 'By experience', 'egypt-roamer-core' ), 'experience_id', $from, $to, $total );
	er_render_report_table( __( 'By activity', 'egypt-roamer-core' ), 'activity_id', $from, $to, $total );
	er_render_report_table( __( 'By page (where the click happened)', 'egypt-roamer-core' ), 'source_path', $from, $to, $total );
	er_render_report_table( __( 'By CTA placement', 'egypt-roamer-core' ), 'placement', $from, $to, $total );
	er_render_report_table( __( 'By CTA label', 'egypt-roamer-core' ), 'cta', $from, $to, $total );
	er_render_report_table( __( 'By campaign', 'egypt-roamer-core' ), 'utm_campaign', $from, $to, $total );
	er_render_report_table( __( 'By language', 'egypt-roamer-core' ), 'lang', $from, $to, $total );
	echo '</div>';

	er_render_traffic_analysis();
	echo '</div>';
}

/* -------------------------------------------------------------------------- */
/* Traffic vs clicks (GA4 CSV import)                                          */
/* -------------------------------------------------------------------------- */

function er_percentile( array $sorted, float $p ): float {
	if ( ! $sorted ) {
		return 0.0;
	}
	$i = ( count( $sorted ) - 1 ) * $p;
	$lo = (int) floor( $i );
	$hi = (int) ceil( $i );
	return $sorted[ $lo ] + ( $sorted[ $hi ] - $sorted[ $lo ] ) * ( $i - $lo );
}

/** Rows: path, views, clicks, rate, flag. */
function er_traffic_rows(): array {
	$data = get_option( 'er_pageviews' );
	if ( ! is_array( $data ) || empty( $data['rows'] ) ) {
		return [];
	}
	$clicks = [];
	foreach ( er_clicks_by( 'source_path', $data['from'], $data['to'], 5000 ) as $r ) {
		$clicks[ untrailingslashit( $r['k'] ) ?: '/' ] = (int) $r['n'];
	}
	$rows = [];
	foreach ( $data['rows'] as $path => $views ) {
		if ( $views < 20 ) {
			continue; // too little traffic to judge
		}
		$key    = untrailingslashit( $path ) ?: '/';
		$n      = $clicks[ $key ] ?? 0;
		$rows[] = [ 'path' => $path, 'views' => (int) $views, 'clicks' => $n, 'rate' => $n / $views, 'flag' => '' ];
	}
	if ( count( $rows ) >= 4 ) {
		$views = array_column( $rows, 'views' );
		$rates = array_column( $rows, 'rate' );
		sort( $views );
		sort( $rates );
		$v75 = er_percentile( $views, .75 );
		$v50 = er_percentile( $views, .5 );
		$r25 = er_percentile( $rates, .25 );
		$r75 = er_percentile( $rates, .75 );
		foreach ( $rows as &$row ) {
			if ( $row['views'] >= $v75 && $row['rate'] <= $r25 ) {
				$row['flag'] = 'high-traffic-low-clicks';
			} elseif ( $row['views'] <= $v50 && $row['rate'] >= $r75 && $row['clicks'] >= 3 ) {
				$row['flag'] = 'low-traffic-high-rate';
			}
		}
		unset( $row );
	}
	usort( $rows, static fn ( $a, $b ) => $b['views'] <=> $a['views'] );
	return $rows;
}

function er_render_traffic_analysis(): void {
	echo '<h2>' . esc_html__( 'Traffic vs affiliate clicks', 'egypt-roamer-core' ) . '</h2>';
	echo '<p class="description">' . esc_html__( 'Import a GA4 export (Reports → Engagement → Pages and screens → Share → Download CSV, dimension “Page path”). Clicks are counted for the same period you enter.', 'egypt-roamer-core' ) . '</p>';

	if ( current_user_can( 'manage_options' ) ) {
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="er-range">';
		wp_nonce_field( 'er_import_pageviews' );
		echo '<input type="hidden" name="action" value="er_import_pageviews" />';
		printf(
			'<label>%s <input type="date" name="from" required /></label> <label>%s <input type="date" name="to" required /></label> <input type="file" name="csv" accept=".csv,text/csv" required /> <button class="button">%s</button>',
			esc_html__( 'GA4 period from', 'egypt-roamer-core' ),
			esc_html__( 'to', 'egypt-roamer-core' ),
			esc_html__( 'Import', 'egypt-roamer-core' )
		);
		echo '</form>';
	}

	$data = get_option( 'er_pageviews' );
	$rows = er_traffic_rows();
	if ( ! $rows ) {
		echo '<p>' . esc_html__( 'No traffic data imported yet.', 'egypt-roamer-core' ) . '</p>';
		return;
	}
	/* translators: 1: from, 2: to */
	echo '<p>' . esc_html( sprintf( __( 'Period %1$s – %2$s. Pages with fewer than 20 views are left out.', 'egypt-roamer-core' ), $data['from'], $data['to'] ) ) . '</p>';
	$flags = [
		'high-traffic-low-clicks' => __( 'High traffic, low clicks — improve the offer or CTA placement', 'egypt-roamer-core' ),
		'low-traffic-high-rate'   => __( 'Low traffic, high click rate — worth promoting / linking to more', 'egypt-roamer-core' ),
	];
	echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Page', 'egypt-roamer-core' ) . '</th><th class="er-num">' . esc_html__( 'Views', 'egypt-roamer-core' ) . '</th><th class="er-num">' . esc_html__( 'Clicks', 'egypt-roamer-core' ) . '</th><th class="er-num">' . esc_html__( 'Click rate', 'egypt-roamer-core' ) . '</th><th>' . esc_html__( 'Signal', 'egypt-roamer-core' ) . '</th></tr></thead><tbody>';
	foreach ( array_slice( $rows, 0, 200 ) as $r ) {
		printf(
			'<tr class="%s"><td>%s</td><td class="er-num">%s</td><td class="er-num">%s</td><td class="er-num">%s%%</td><td>%s</td></tr>',
			esc_attr( $r['flag'] ? 'er-flag er-flag--' . $r['flag'] : '' ),
			esc_html( $r['path'] ),
			esc_html( number_format_i18n( $r['views'] ) ),
			esc_html( number_format_i18n( $r['clicks'] ) ),
			esc_html( number_format_i18n( 100 * $r['rate'], 1 ) ),
			esc_html( $flags[ $r['flag'] ] ?? '' )
		);
	}
	echo '</tbody></table>';
}

/** Parse a GA4 CSV export: skip "#" comment lines, find the path and views columns. */
function er_parse_ga4_csv( string $file ): array {
	$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! $handle ) {
		return [];
	}
	$rows = [];
	$path_col = null;
	$view_col = null;
	while ( ( $line = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
		if ( ! $line || null === $line[0] || str_starts_with( (string) $line[0], '#' ) || ( 1 === count( $line ) && '' === trim( (string) $line[0] ) ) ) {
			continue;
		}
		if ( null === $path_col ) {
			foreach ( $line as $i => $h ) {
				$h = strtolower( trim( (string) $h ) );
				if ( null === $path_col && ( str_contains( $h, 'page path' ) || str_contains( $h, 'landing page' ) || 'page' === $h ) ) {
					$path_col = $i;
				}
				if ( null === $view_col && ( 'views' === $h || str_contains( $h, 'pageviews' ) || 'sessions' === $h ) ) {
					$view_col = $i;
				}
			}
			if ( null === $path_col || null === $view_col ) {
				$path_col = null;
				$view_col = null;
			}
			continue;
		}
		$path  = (string) wp_parse_url( trim( (string) ( $line[ $path_col ] ?? '' ) ), PHP_URL_PATH );
		$views = (int) preg_replace( '/[^0-9]/', '', (string) ( $line[ $view_col ] ?? '' ) );
		if ( '' !== $path && str_starts_with( $path, '/' ) && $views > 0 && count( $rows ) < 5000 ) {
			$path          = mb_substr( sanitize_text_field( $path ), 0, 191 );
			$rows[ $path ] = ( $rows[ $path ] ?? 0 ) + $views;
		}
	}
	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return $rows;
}

add_action( 'admin_post_er_import_pageviews', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_import_pageviews' );
	$from = sanitize_text_field( wp_unslash( $_POST['from'] ?? '' ) );
	$to   = sanitize_text_field( wp_unslash( $_POST['to'] ?? '' ) );
	$file = $_FILES['csv']['tmp_name'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- temp path from PHP, checked below
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) || ! $file || ! is_uploaded_file( $file ) ) {
		wp_die( esc_html__( 'Please choose a CSV file and the GA4 period.', 'egypt-roamer-core' ), 400 );
	}
	$rows = er_parse_ga4_csv( $file );
	update_option( 'er_pageviews', [ 'from' => $from, 'to' => $to, 'rows' => $rows, 'imported' => current_time( 'mysql' ) ], false );
	wp_safe_redirect( admin_url( 'admin.php?page=er-reports&imported=' . count( $rows ) ) );
	exit;
} );

add_action( 'admin_post_er_export_clicks', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_export_clicks' );
	$_GET['days'] = 0;
	[ $from, $to ] = er_report_range();
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="egypt-roamer-clicks-' . $from . '-' . $to . '.csv"' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fputcsv( $out, [ 'clicked_at_utc', 'offer', 'provider', 'destination', 'tour', 'experience', 'activity', 'source_page', 'placement', 'cta', 'utm_source', 'utm_medium', 'utm_campaign', 'lang' ], ',', '"', '\\' );
	foreach ( er_clicks_rows( $from, $to ) as $r ) {
		$title = static fn ( $id ) => $id ? get_the_title( (int) $id ) : '';
		$row   = [ $r['clicked_at'], $title( $r['offer_id'] ), $title( $r['provider_id'] ), $title( $r['destination_id'] ), $title( $r['tour_id'] ), $title( $r['experience_id'] ), $title( $r['activity_id'] ), $r['source_path'], $r['placement'], $r['cta'], $r['utm_source'], $r['utm_medium'], $r['utm_campaign'], $r['lang'] ];
		// Neutralise spreadsheet formula injection.
		$row = array_map( static fn ( $v ) => preg_match( '/^[=+\-@\t\r]/', (string) $v ) ? "'" . $v : $v, $row );
		fputcsv( $out, $row, ',', '"', '\\' );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
} );
