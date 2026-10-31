<?php
/**
 * First-party page-view statistics, privacy first.
 *
 * What is stored: daily totals only. One row per (day, event, path, language, page type,
 * source category, referring host, device class); each page view adds 1 to its row.
 * What is never stored: IP address, user agent, cookies, any visitor or session identifier,
 * query strings, the referring page's path. The browser sets no cookie and keeps nothing.
 *
 * Consequences, stated plainly in the report: these are page views, not people. Unique
 * visitors cannot be counted with this design, by choice.
 *
 * Not counted: logged-in users (and browsers where an editor has been logged in), Do Not
 * Track / Global Privacy Control, prerenders, automated browsers (navigator.webdriver),
 * requests whose user agent looks like a bot, and (from Core 1.2.67) pages nobody interacted
 * with: a view is sent on the first scroll, tap, click, key or pointer move. A bot that fakes
 * interaction can still slip through; the report says so.
 *
 * Events: view (page view), cta (click on an affiliate CTA, before the /go/ hop; the /go/
 * handler's own click log stays the authoritative count), go_error (a /go/ link that could
 * not redirect to its partner).
 */

defined( 'ABSPATH' ) || exit;

/** Daily totals are kept this long. The first stored day is kept in the er_visits_since option. */
const ER_VISITS_RETENTION_DAYS = 760; // about 25 months, enough for year-on-year comparisons
const ER_VISITS_MAX_ROWS_PER_DAY = 5000;

function er_visits_table(): string {
	global $wpdb;
	return $wpdb->prefix . 'er_visits';
}

add_action( 'er_install_tables', 'er_visits_install' );
function er_visits_install(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t       = er_visits_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE {$t} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		hkey char(40) NOT NULL,
		day date NOT NULL,
		event varchar(16) NOT NULL DEFAULT 'view',
		path varchar(191) NOT NULL DEFAULT '',
		lang varchar(12) NOT NULL DEFAULT '',
		ptype varchar(32) NOT NULL DEFAULT '',
		source varchar(16) NOT NULL DEFAULT '',
		ref_host varchar(100) NOT NULL DEFAULT '',
		device varchar(8) NOT NULL DEFAULT '',
		hits int(10) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		UNIQUE KEY hkey (hkey),
		KEY day (day)
	) {$charset};" );
}

/* ---------- Classification ---------- */

/**
 * Source category for a referring host (host only, never a path).
 * Returns [category, host-to-store].
 */
function er_visits_source( string $ref_host, string $utm = '' ): array {
	$host = strtolower( preg_replace( '/^www\./', '', $ref_host ) );
	$own  = strtolower( preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
	if ( '' === $host ) {
		return '' !== $utm ? [ 'campaign', $utm ] : [ 'direct', '' ];
	}
	if ( $host === $own ) {
		return [ 'internal', '' ];
	}
	$groups = [
		'ai'     => '/(^|\.)(chatgpt\.com|chat\.openai\.com|openai\.com|perplexity\.ai|copilot\.microsoft\.com|gemini\.google\.com|bard\.google\.com|claude\.ai|you\.com|phind\.com|meta\.ai|deepseek\.com|kimi\.(ai|moonshot\.cn))$/',
		'search' => '/(^|\.)(google\.[a-z.]+|bing\.com|duckduckgo\.com|yahoo\.[a-z.]+|yandex\.[a-z.]+|ya\.ru|baidu\.com|ecosia\.org|naver\.com|seznam\.cz|qwant\.com|startpage\.com|brave\.com|sogou\.com|so\.com|petalsearch\.com)$/',
		'social' => '/(^|\.)(facebook\.com|fb\.com|instagram\.com|t\.co|x\.com|twitter\.com|linkedin\.com|lnkd\.in|reddit\.com|pinterest\.[a-z.]+|youtube\.com|tiktok\.com|whatsapp\.com|telegram\.org|t\.me|vk\.com|weibo\.com|threads\.net|quora\.com)$/',
	];
	foreach ( $groups as $cat => $re ) {
		if ( preg_match( $re, $host ) ) {
			return [ $cat, $host ];
		}
	}
	return [ 'referral', $host ];
}

function er_visits_is_bot_ua(): bool {
	$ua = strtolower( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );
	return '' === $ua || (bool) preg_match( '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|headless|lighthouse|pagespeed|pingdom|monitor|uptime|curl|wget|python|httpclient|scrapy|phantom|selenium|puppeteer|playwright|chrome-lighthouse|gtmetrix|validator|feedfetcher|mediapartners/', $ua );
}

/** Add one to a daily total. All values are already cleaned. */
function er_visits_add( array $row ): bool {
	global $wpdb;
	$row = wp_parse_args( $row, [ 'day' => gmdate( 'Y-m-d' ), 'event' => 'view', 'path' => '', 'lang' => '', 'ptype' => '', 'source' => '', 'ref_host' => '', 'device' => '' ] );
	$row = array_intersect_key( $row, array_flip( [ 'day', 'event', 'path', 'lang', 'ptype', 'source', 'ref_host', 'device' ] ) );
	$k   = sha1( implode( "\x1f", $row ) );
	$t   = er_visits_table();
	// Junk protection: past a generous number of distinct rows in one day, only existing rows are counted.
	$rows_today = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE day = %s", $row['day'] ) ); // phpcs:ignore WordPress.DB
	if ( $rows_today >= ER_VISITS_MAX_ROWS_PER_DAY ) {
		return (bool) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET hits = hits + 1 WHERE hkey = %s", $k ) ); // phpcs:ignore WordPress.DB
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- an aggregate counter; table name is internal
	$ok = $wpdb->query( $wpdb->prepare(
		"INSERT INTO {$t} (hkey, day, event, path, lang, ptype, source, ref_host, device, hits) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, 1) ON DUPLICATE KEY UPDATE hits = hits + 1",
		$k, $row['day'], $row['event'], $row['path'], $row['lang'], $row['ptype'], $row['source'], $row['ref_host'], $row['device']
	) );
	if ( false !== $ok && ! get_option( 'er_visits_since' ) ) {
		update_option( 'er_visits_since', $row['day'], false );
	}
	return false !== $ok;
}

/* ---------- Collection endpoint ---------- */

add_action( 'rest_api_init', static function () {
	register_rest_route( 'egypt-roamer/v1', '/hit', [
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'er_visits_rest_hit',
	] );
} );

function er_visits_rest_hit( WP_REST_Request $request ) {
	$done = static function () {
		$r = new WP_REST_Response( null, 204 );
		$r->header( 'Cache-Control', 'no-store' );
		return $r;
	};
	// Same-site browsers only. Browsers send these headers; scripts usually do not.
	$site = strtolower( (string) $request->get_header( 'sec_fetch_site' ) );
	if ( '' !== $site && 'same-origin' !== $site ) {
		return $done();
	}
	$origin = (string) $request->get_header( 'origin' );
	if ( '' !== $origin && wp_parse_url( $origin, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		return $done();
	}
	if ( er_visits_is_bot_ua() || is_user_logged_in() ) {
		return $done();
	}
	$data = json_decode( (string) $request->get_body(), true );
	if ( ! is_array( $data ) ) {
		return $done();
	}
	$str = static fn ( $key, int $max ) => isset( $data[ $key ] ) && is_string( $data[ $key ] ) ? mb_substr( trim( $data[ $key ] ), 0, $max ) : '';

	$event = $str( 'e', 16 );
	if ( ! in_array( $event, [ 'view', 'cta' ], true ) ) {
		return $done();
	}
	// Path only: no query string or fragment, decoded so Arabic and Chinese paths stay readable.
	$path = (string) wp_parse_url( $str( 'p', 600 ), PHP_URL_PATH );
	$path = rawurldecode( $path );
	if ( '' === $path || '/' !== $path[0] || preg_match( '/[\x00-\x1f<>"\\\\]/', $path ) ) {
		return $done();
	}
	$path = mb_substr( $path, 0, 191 );

	$languages = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : [ 'en' ];
	$lang      = in_array( $str( 'l', 12 ), $languages, true ) ? $str( 'l', 12 ) : '';
	$ptype     = preg_match( '/^[a-z0-9_]{1,32}$/', $str( 't', 32 ) ) ? $str( 't', 32 ) : '';
	$device    = in_array( $str( 'd', 8 ), [ 'mobile', 'tablet', 'desktop' ], true ) ? $str( 'd', 8 ) : '';
	$ref       = strtolower( $str( 'r', 100 ) );
	$ref       = preg_match( '/^[a-z0-9.-]+$/', $ref ) ? $ref : '';
	$utm       = strtolower( $str( 'u', 40 ) );
	$utm       = preg_match( '/^[a-z0-9._-]+$/', $utm ) ? $utm : '';
	[ $source, $host ] = er_visits_source( $ref, $utm );

	if ( 'cta' === $event ) {
		$ptype = preg_match( '/^[a-z0-9_-]{1,32}$/', $str( 'c', 32 ) ) ? $str( 'c', 32 ) : 'offer'; // which CTA (placement)
	}

	er_visits_add( [
		'event'    => $event,
		'path'     => $path,
		'lang'     => $lang,
		'ptype'    => $ptype,
		'source'   => $source,
		'ref_host' => $host,
		'device'   => $device,
	] );
	return $done();
}

/** A /go/ link that could not redirect to its partner: counted, so broken offers show up in the report. */
add_action( 'er_go_failed', static function ( string $slug, string $reason = '' ) {
	if ( er_visits_is_bot_ua() ) {
		return;
	}
	er_visits_add( [
		'event'  => 'go_error',
		'path'   => '/go/' . mb_substr( sanitize_title( $slug ), 0, 80 ) . '/',
		'ptype'  => mb_substr( sanitize_key( $reason ), 0, 32 ),
		'source' => 'internal',
	] );
}, 10, 2 );

/* ---------- Front end ---------- */

add_filter( 'er_track_config', static function ( array $cfg ): array {
	$cfg['hit']  = esc_url_raw( rest_url( 'egypt-roamer/v1/hit' ) );
	$cfg['lang'] = function_exists( 'er_current_lang' ) ? er_current_lang() : '';
	// An editor's browser is excluded from now on, even after logging out (remembered in local storage).
	$cfg['self'] = is_user_logged_in() ? 1 : 0;
	return $cfg;
} );

/* ---------- Retention ---------- */

add_action( 'init', static function () {
	if ( ! wp_next_scheduled( 'er_visits_purge' ) ) {
		wp_schedule_event( time() + 2 * HOUR_IN_SECONDS, 'daily', 'er_visits_purge' );
	}
} );
add_action( 'er_visits_purge', static function () {
	global $wpdb;
	$t = er_visits_table();
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$t} WHERE day < %s", gmdate( 'Y-m-d', strtotime( '-' . ER_VISITS_RETENTION_DAYS . ' days' ) ) ) ); // phpcs:ignore WordPress.DB
} );

/* ---------- Reading ---------- */

function er_visits_sum( string $from, string $to, string $event = 'view', array $where = [] ): int {
	global $wpdb;
	$t   = er_visits_table();
	$sql = $wpdb->prepare( "SELECT COALESCE(SUM(hits),0) FROM {$t} WHERE day >= %s AND day <= %s AND event = %s", $from, $to, $event ); // phpcs:ignore WordPress.DB
	foreach ( $where as $col => $val ) {
		if ( in_array( $col, [ 'source', 'lang', 'device', 'ptype' ], true ) ) {
			$sql .= $wpdb->prepare( " AND {$col} = %s", $val ); // phpcs:ignore WordPress.DB -- whitelisted column
		} elseif ( 'not_source' === $col ) {
			$sql .= $wpdb->prepare( ' AND source <> %s', $val );
		}
	}
	return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB
}

/**
 * Totals grouped by one dimension. $entries_only keeps views that arrived from outside the site
 * (landing pages). Returns [ [ 'k' => value, 'n' => hits ], … ].
 */
function er_visits_by( string $dim, string $from, string $to, string $event = 'view', bool $entries_only = false, int $limit = 20 ): array {
	global $wpdb;
	$cols = [ 'day' => 'day', 'path' => 'path', 'lang' => 'lang', 'ptype' => 'ptype', 'source' => 'source', 'ref_host' => 'ref_host', 'device' => 'device' ];
	if ( ! isset( $cols[ $dim ] ) ) {
		return [];
	}
	$t     = er_visits_table();
	$col   = $cols[ $dim ];
	$order = 'day' === $dim ? 'k ASC' : 'n DESC';
	$sql   = $wpdb->prepare( "SELECT {$col} AS k, SUM(hits) AS n FROM {$t} WHERE day >= %s AND day <= %s AND event = %s", $from, $to, $event ); // phpcs:ignore WordPress.DB
	if ( $entries_only ) {
		$sql .= " AND source <> 'internal'";
	}
	if ( 'ref_host' === $dim ) {
		$sql .= " AND ref_host <> ''";
	}
	$sql .= $wpdb->prepare( " GROUP BY k ORDER BY {$order} LIMIT %d", $limit ); // phpcs:ignore WordPress.DB
	return $wpdb->get_results( $sql, ARRAY_A ) ?: []; // phpcs:ignore WordPress.DB
}

/* ---------- Admin report ---------- */

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Visits', 'egypt-roamer-core' ), __( 'Visits', 'egypt-roamer-core' ), 'edit_others_posts', 'er-visits', 'er_render_visits_page' );
}, 19 );

function er_visits_table_html( string $title, array $rows, int $total, ?callable $label = null ): void {
	echo '<section class="er-report"><h3>' . esc_html( $title ) . '</h3>';
	if ( ! $rows ) {
		echo '<p class="description">' . esc_html__( 'Nothing recorded in this period.', 'egypt-roamer-core' ) . '</p></section>';
		return;
	}
	echo '<table class="widefat striped"><tbody>';
	foreach ( $rows as $row ) {
		$share = $total ? round( 100 * $row['n'] / $total ) : 0;
		$text  = $label ? $label( (string) $row['k'] ) : ( '' === (string) $row['k'] ? '—' : (string) $row['k'] );
		printf( '<tr><td>%s</td><td class="er-num">%s</td><td class="er-bar"><i style="width:%d%%"></i></td></tr>', esc_html( $text ), esc_html( number_format_i18n( (int) $row['n'] ) ), (int) $share );
	}
	echo '</tbody></table></section>';
}

function er_render_visits_page(): void {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}
	[ $from, $to ] = er_report_range();
	$base          = admin_url( 'admin.php?page=er-visits' );
	$since         = (string) get_option( 'er_visits_since', '' );
	$views         = er_visits_sum( $from, $to );
	$entries       = er_visits_sum( $from, $to, 'view', [ 'not_source' => 'internal' ] );
	$search        = er_visits_sum( $from, $to, 'view', [ 'source' => 'search' ] );
	$ai            = er_visits_sum( $from, $to, 'view', [ 'source' => 'ai' ] );
	$cta           = er_visits_sum( $from, $to, 'cta' );
	$clicks        = er_clicks_total( $from, $to );
	$errors        = er_visits_sum( $from, $to, 'go_error' );

	echo '<div class="wrap er-wrap"><h1>' . esc_html__( 'Visits', 'egypt-roamer-core' ) . '</h1>';
	echo '<p class="description">' . esc_html__( 'Page views counted by the site itself: daily totals only, with no cookies, no visitor IDs and no IP addresses. A person who views five pages counts five times; unique visitors are not counted. Logged-in editors, browsers that send Do Not Track or Global Privacy Control, and identifiable bots are left out.', 'egypt-roamer-core' ) . '</p>';
	echo '<p class="description">' . esc_html__( 'Since 24 Oct 2026, a page view counts only after the visitor scrolls, taps, clicks, types or moves the pointer, and a /go/ redirect counts only when someone clicked the link on one of our pages. Earlier days include bots that loaded pages without using them.', 'egypt-roamer-core' ) . '</p>';
	if ( $since ) {
		/* translators: %s: date */
		echo '<p><strong>' . esc_html( sprintf( __( 'Measuring since %s (UTC). There is no visit data for earlier dates.', 'egypt-roamer-core' ), $since ) ) . '</strong></p>';
	} else {
		echo '<p><strong>' . esc_html__( 'Nothing recorded yet.', 'egypt-roamer-core' ) . '</strong></p>';
	}

	echo '<form method="get" class="er-range"><input type="hidden" name="page" value="er-visits" />';
	foreach ( [ 7, 30, 90, 365 ] as $d ) {
		/* translators: %d: number of days */
		printf( '<a class="button" href="%s">%s</a> ', esc_url( add_query_arg( 'days', $d, $base ) ), esc_html( sprintf( __( 'Last %d days', 'egypt-roamer-core' ), $d ) ) );
	}
	printf(
		'<label>%s <input type="date" name="from" value="%s" /></label> <label>%s <input type="date" name="to" value="%s" /></label> <button class="button">%s</button></form>',
		esc_html__( 'From', 'egypt-roamer-core' ),
		esc_attr( $from ),
		esc_html__( 'to', 'egypt-roamer-core' ),
		esc_attr( $to ),
		esc_html__( 'Apply', 'egypt-roamer-core' )
	);

	$cards = [
		[ $views, __( 'page views', 'egypt-roamer-core' ) ],
		[ $entries, __( 'entrances (views that arrived from outside the site)', 'egypt-roamer-core' ) ],
		[ $search, __( 'entrances from search engines', 'egypt-roamer-core' ) ],
		[ $ai, __( 'entrances from AI assistants', 'egypt-roamer-core' ) ],
		[ $cta, __( 'partner-link taps on the page', 'egypt-roamer-core' ) ],
		[ $clicks, __( 'partner redirects logged by /go/', 'egypt-roamer-core' ) ],
		[ $errors, __( 'partner links that failed', 'egypt-roamer-core' ) ],
	];
	echo '<div class="er-cards">';
	foreach ( $cards as [ $n, $label ] ) {
		printf( '<div class="er-card"><b>%s</b><span>%s</span></div>', esc_html( number_format_i18n( $n ) ), esc_html( $label ) );
	}
	echo '</div>';
	/* translators: 1: from date, 2: to date */
	printf( '<p class="description">%s</p>', esc_html( sprintf( __( '%1$s – %2$s, UTC days.', 'egypt-roamer-core' ), $from, $to ) ) );

	$days = er_visits_by( 'day', $from, $to, 'view', false, 400 );
	if ( $days ) {
		$max = max( array_map( static fn ( $r ) => (int) $r['n'], $days ) );
		echo '<section class="er-report er-report--wide"><h3>' . esc_html__( 'Page views per day', 'egypt-roamer-core' ) . '</h3><div class="er-timeline" role="img" aria-label="' . esc_attr__( 'Daily page views', 'egypt-roamer-core' ) . '">';
		foreach ( $days as $r ) {
			printf( '<i style="height:%d%%" title="%s: %d"></i>', (int) round( 100 * $r['n'] / max( 1, $max ) ), esc_attr( $r['k'] ), (int) $r['n'] );
		}
		echo '</div></section>';
	}

	$sources = [
		'direct'   => __( 'Direct or unknown', 'egypt-roamer-core' ),
		'search'   => __( 'Search engines', 'egypt-roamer-core' ),
		'ai'       => __( 'AI assistants', 'egypt-roamer-core' ),
		'social'   => __( 'Social', 'egypt-roamer-core' ),
		'referral' => __( 'Other websites', 'egypt-roamer-core' ),
		'campaign' => __( 'Campaign (utm_source)', 'egypt-roamer-core' ),
		'internal' => __( 'Inside the site', 'egypt-roamer-core' ),
	];
	echo '<div class="er-reports">';
	er_visits_table_html( __( 'Landing pages (entrances)', 'egypt-roamer-core' ), er_visits_by( 'path', $from, $to, 'view', true ), $entries );
	er_visits_table_html( __( 'All pages (views)', 'egypt-roamer-core' ), er_visits_by( 'path', $from, $to ), $views );
	er_visits_table_html( __( 'Where entrances came from', 'egypt-roamer-core' ), er_visits_by( 'source', $from, $to, 'view', true ), $entries, static fn ( $k ) => $sources[ $k ] ?? $k );
	er_visits_table_html( __( 'Referring sites (domain only)', 'egypt-roamer-core' ), er_visits_by( 'ref_host', $from, $to, 'view', true ), $entries );
	er_visits_table_html( __( 'Language', 'egypt-roamer-core' ), er_visits_by( 'lang', $from, $to ), $views );
	er_visits_table_html( __( 'Device (by screen width)', 'egypt-roamer-core' ), er_visits_by( 'device', $from, $to ), $views );
	er_visits_table_html( __( 'Page type', 'egypt-roamer-core' ), er_visits_by( 'ptype', $from, $to ), $views );
	er_visits_table_html( __( 'Partner-link taps by page', 'egypt-roamer-core' ), er_visits_by( 'path', $from, $to, 'cta' ), $cta );
	er_visits_table_html( __( 'Failed partner links', 'egypt-roamer-core' ), er_visits_by( 'path', $from, $to, 'go_error' ), $errors );
	echo '</div>';
	er_go_diag_html();
	echo '</div>';
}

/* ---------- /go/ click diagnosis (31 Oct 2026, one week) ---------- */

/*
 * The week of 25-31 Oct logged 161 /go/ redirects against 90 page views and 1 tap, so some clicks are not people.
 * Until ER_GO_DIAG_UNTIL, the last 300 logged redirects keep the request headers that tell a person from a script
 * (browser string, fetch metadata, referrer path). No IP address. Shown under the Visits report, then dropped.
 */
const ER_GO_DIAG_UNTIL = '2026-11-08';

add_action( 'er_affiliate_click', static function ( int $offer_id ): void {
	if ( gmdate( 'Y-m-d' ) > ER_GO_DIAG_UNTIL ) {
		return;
	}
	$h   = static fn ( string $k, int $max = 40 ): string => mb_substr( sanitize_text_field( wp_unslash( $_SERVER[ $k ] ?? '' ) ), 0, $max );
	$ref = isset( $_SERVER['HTTP_REFERER'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), PHP_URL_PATH ) : '';
	$log = get_option( 'er_go_diag', [] );
	$log = is_array( $log ) ? $log : [];
	$log[] = [
		't'    => gmdate( 'Y-m-d H:i:s' ),
		'go'   => get_post_field( 'post_name', $offer_id ),
		'ua'   => $h( 'HTTP_USER_AGENT', 200 ),
		'site' => $h( 'HTTP_SEC_FETCH_SITE' ),
		'user' => $h( 'HTTP_SEC_FETCH_USER' ),
		'mode' => $h( 'HTTP_SEC_FETCH_MODE' ),
		'ref'  => mb_substr( $ref, 0, 120 ),
		'lang' => $h( 'HTTP_ACCEPT_LANGUAGE', 30 ),
	];
	update_option( 'er_go_diag', array_slice( $log, -300 ), false );
} );

function er_go_diag_html(): void {
	if ( gmdate( 'Y-m-d' ) > ER_GO_DIAG_UNTIL ) {
		delete_option( 'er_go_diag' );
		return;
	}
	$log = get_option( 'er_go_diag', [] );
	echo '<section class="er-report er-report--wide"><h3>' . esc_html__( '/go/ redirects: request details (diagnosis until 8 Nov 2026)', 'egypt-roamer-core' ) . '</h3>';
	if ( ! is_array( $log ) || ! $log ) {
		echo '<p>' . esc_html__( 'None logged yet.', 'egypt-roamer-core' ) . '</p></section>';
		return;
	}
	echo '<table class="widefat striped"><thead><tr><th>UTC</th><th>/go/</th><th>Referrer path</th><th>Sec-Fetch site / user / mode</th><th>Accept-Language</th><th>Browser</th></tr></thead><tbody>';
	foreach ( array_reverse( $log ) as $r ) {
		printf(
			'<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><small>%s</small></td></tr>',
			esc_html( $r['t'] ?? '' ),
			esc_html( $r['go'] ?? '' ),
			esc_html( $r['ref'] ?? '' ),
			esc_html( ( $r['site'] ?? '' ) . ' / ' . ( $r['user'] ?? '' ) . ' / ' . ( $r['mode'] ?? '' ) ),
			esc_html( $r['lang'] ?? '' ),
			esc_html( $r['ua'] ?? '' )
		);
	}
	echo '</tbody></table></section>';
}
