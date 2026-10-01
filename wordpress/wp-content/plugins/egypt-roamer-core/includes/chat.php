<?php
/**
 * Live chat with the Egypt Roamer team, inside the Trip assistant (docs/HUMAN-LIVE-CHAT-2026-10-01.md).
 *
 * A visitor who asks for a person starts a conversation; the team answers from
 * Egypt Roamer → Conversations. The same conversation can go back to the assistant ("Return to AI")
 * and the assistant never answers while a person owns it.
 *
 * Statuses: ai (the assistant answers) · requested (a person was asked for, the team is online) ·
 * waiting (asked while the team was offline: "leave us a message") · human (a team member owns it) ·
 * closed · archived (closed and out of the inbox).
 *
 * Visitors have no account: a conversation is reached with its public id plus a random secret token
 * (32 bytes) that only the visitor's browser holds; the server keeps a hash of it. Nothing is public:
 * no post type, no URL, no sitemap; every REST route checks the token (visitor) or the
 * manage_er_conversations capability and the REST nonce (team). Messages are plain text, limited in
 * length and rate; retention is configurable (closed conversations are deleted after N days).
 *
 * Transport: short polling over REST, only while the visitor's chat drawer is open (and the browser tab
 * is visible), and on the team's inbox page. No third-party service and no extra script on page load.
 */

defined( 'ABSPATH' ) || exit;

const ER_CHAT_MAX_CHARS   = 2000;
const ER_CHAT_NAME_CHARS  = 80;
const ER_CHAT_SEND_RATE   = 15; // visitor messages per conversation per minute
const ER_CHAT_START_RATE  = 10; // new conversations per visitor (hashed IP) per hour (shared hotel/office IPs)
const ER_CHAT_START_TOTAL = 60; // new conversations per hour, whole site (a flood from many IPs can't fill the inbox)
const ER_CHAT_ONLINE_SECS = 150; // a team member counts as online this long after their inbox last checked in
const ER_CHAT_STATUSES    = [ 'ai', 'requested', 'waiting', 'human', 'closed', 'archived' ];
const ER_CHAT_CAP         = 'manage_er_conversations';

/* -------------------------------------------------------------------------- */
/* Storage                                                                     */
/* -------------------------------------------------------------------------- */

function er_chat_table( string $which ): string {
	global $wpdb;
	return $wpdb->prefix . ( 'messages' === $which ? 'er_chat_messages' : 'er_chat_conversations' );
}

add_action( 'er_install_tables', 'er_chat_install' );
function er_chat_install(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$c       = er_chat_table( 'conversations' );
	$m       = er_chat_table( 'messages' );
	dbDelta( "CREATE TABLE {$c} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		public_id char(24) NOT NULL,
		token_hash char(64) NOT NULL,
		status varchar(16) NOT NULL DEFAULT 'waiting',
		visitor_name varchar(100) NOT NULL DEFAULT '',
		visitor_email varchar(190) NOT NULL DEFAULT '',
		lang varchar(12) NOT NULL DEFAULT '',
		entry_url varchar(255) NOT NULL DEFAULT '',
		current_url varchar(255) NOT NULL DEFAULT '',
		assigned_to bigint(20) unsigned NOT NULL DEFAULT 0,
		agent_read_id bigint(20) unsigned NOT NULL DEFAULT 0,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		last_message_at datetime NOT NULL,
		closed_at datetime NULL DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY public_id (public_id),
		KEY status (status),
		KEY last_message_at (last_message_at)
	) {$charset};" );
	dbDelta( "CREATE TABLE {$m} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		conversation_id bigint(20) unsigned NOT NULL,
		sender varchar(8) NOT NULL,
		user_id bigint(20) unsigned NOT NULL DEFAULT 0,
		body text NOT NULL,
		meta longtext NULL,
		client_id varchar(40) NOT NULL DEFAULT '',
		internal tinyint(1) NOT NULL DEFAULT 0,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY conversation (conversation_id, id)
	) {$charset};" );
	// The team: administrators and editors may answer conversations (the capability can be given to other roles).
	foreach ( [ 'administrator', 'editor' ] as $role_name ) {
		$role = get_role( $role_name );
		if ( $role && ! $role->has_cap( ER_CHAT_CAP ) ) {
			$role->add_cap( ER_CHAT_CAP );
		}
	}
}

function er_chat_now(): string {
	return gmdate( 'Y-m-d H:i:s' );
}

function er_chat_get( int $id ): ?array {
	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . er_chat_table( 'conversations' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	return $row ?: null;
}

/** A conversation by public id, only when the visitor's token matches (constant-time). */
function er_chat_for_visitor( string $public_id, string $token ): ?array {
	global $wpdb;
	if ( ! preg_match( '/^[a-f0-9]{24}$/', $public_id ) || ! preg_match( '/^[a-f0-9]{64}$/', $token ) ) {
		return null;
	}
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . er_chat_table( 'conversations' ) . ' WHERE public_id = %s', $public_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	if ( ! $row || ! hash_equals( (string) $row['token_hash'], er_chat_token_hash( $token ) ) ) {
		return null;
	}
	return $row;
}

function er_chat_token_hash( string $token ): string {
	return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
}

function er_chat_update( int $id, array $fields ): void {
	global $wpdb;
	$fields['updated_at'] = er_chat_now();
	$wpdb->update( er_chat_table( 'conversations' ), $fields, [ 'id' => $id ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/** Add a message; returns its id. System notes marked internal are shown to the team only. */
function er_chat_add( int $conversation_id, string $sender, string $body, array $extra = [] ): int {
	global $wpdb;
	$now = er_chat_now();
	$wpdb->insert( er_chat_table( 'messages' ), [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		'conversation_id' => $conversation_id,
		'sender'          => $sender,
		'user_id'         => (int) ( $extra['user_id'] ?? 0 ),
		'body'            => $body,
		'meta'            => isset( $extra['meta'] ) ? wp_json_encode( $extra['meta'] ) : null,
		'client_id'       => (string) ( $extra['client_id'] ?? '' ),
		'internal'        => empty( $extra['internal'] ) ? 0 : 1,
		'created_at'      => $now,
	] );
	$id = (int) $wpdb->insert_id;
	if ( empty( $extra['internal'] ) ) {
		er_chat_update( $conversation_id, [ 'last_message_at' => $now ] );
	}
	return $id;
}

/** Messages after an id (oldest first). Visitors never get internal notes or team user ids. */
function er_chat_messages( int $conversation_id, int $after = 0, bool $for_team = false ): array {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . er_chat_table( 'messages' ) . ' WHERE conversation_id = %d AND id > %d ORDER BY id ASC LIMIT 500', $conversation_id, $after ), ARRAY_A ) ?: []; // phpcs:ignore WordPress.DB
	$out  = [];
	foreach ( $rows as $r ) {
		if ( ! $for_team && (int) $r['internal'] ) {
			continue;
		}
		$m = [
			'id'     => (int) $r['id'],
			'sender' => (string) $r['sender'],
			'body'   => (string) $r['body'],
			'time'   => mysql2date( 'c', $r['created_at'] . ' +00:00' ),
			'client' => (string) $r['client_id'],
		];
		if ( $r['meta'] ) {
			$m['meta'] = json_decode( (string) $r['meta'], true );
		}
		if ( $for_team ) {
			$m['internal'] = (bool) $r['internal'];
			if ( (int) $r['user_id'] ) {
				$user        = get_userdata( (int) $r['user_id'] );
				$m['author'] = $user ? $user->display_name : '';
			}
		} elseif ( 'agent' === $r['sender'] ) {
			$user        = get_userdata( (int) $r['user_id'] );
			$m['author'] = $user ? $user->first_name : ''; // first name only, if the team member set one
		}
		$out[] = $m;
	}
	return $out;
}

/** Plain text, line breaks kept, at most $max characters. */
function er_chat_clean( string $text, int $max = ER_CHAT_MAX_CHARS ): string {
	$text = wp_strip_all_tags( $text );
	$text = (string) preg_replace( "/[^\P{C}\n]+/u", '', str_replace( "\r", '', $text ) ); // control characters, but not newlines
	$text = (string) preg_replace( "/\n{3,}/", "\n\n", $text );
	return trim( mb_substr( $text, 0, $max ) );
}

/** Pages of this site only (stored as a path, never a foreign URL). */
function er_chat_clean_url( string $url ): string {
	$url  = esc_url_raw( $url );
	$home = wp_parse_url( home_url( '/' ) );
	$u    = wp_parse_url( $url );
	if ( ! $u || ( $u['host'] ?? '' ) !== ( $home['host'] ?? '' ) ) {
		return '';
	}
	// One leading slash only: "//evil.example/x" would be a protocol-relative link to another site in the inbox.
	$path = '/' . ltrim( (string) ( $u['path'] ?? '/' ), '/' );
	return mb_substr( $path . ( isset( $u['query'] ) ? '?' . $u['query'] : '' ), 0, 250 );
}

/* -------------------------------------------------------------------------- */
/* Team presence                                                               */
/* -------------------------------------------------------------------------- */

/** Team members whose inbox checked in recently and who are not set to away. */
function er_chat_online_agents(): array {
	$users = get_users( [
		'capability' => ER_CHAT_CAP,
		'meta_query' => [ [ 'key' => 'er_chat_seen', 'value' => time() - ER_CHAT_ONLINE_SECS, 'compare' => '>=', 'type' => 'NUMERIC' ] ],
		'fields'     => 'ID',
	] );
	// "Away" and "Offline" are chosen states: the inbox polling (which keeps "seen" fresh) never overrides them.
	return array_values( array_filter( array_map( 'intval', $users ), static fn ( $u ) => ! in_array( get_user_meta( $u, 'er_chat_presence', true ), [ 'away', 'offline' ], true ) ) );
}

function er_chat_team_online(): bool {
	return (bool) er_chat_online_agents();
}

/* -------------------------------------------------------------------------- */
/* Guards                                                                      */
/* -------------------------------------------------------------------------- */

function er_chat_ip_key( string $bucket ): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'er_ch_' . $bucket . '_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 20 );
}

/** A counter in a time window; false once the limit is reached. */
function er_chat_rate( string $key, int $limit, int $window ): bool {
	$n = (int) get_transient( $key );
	if ( $n >= $limit ) {
		return false;
	}
	set_transient( $key, $n + 1, $window );
	return true;
}

function er_chat_error( string $code, string $message, int $status ): WP_Error {
	return new WP_Error( $code, $message, [ 'status' => $status ] );
}

/** A request parameter as a string; anything else (arrays, objects from crafted JSON) becomes ''. */
function er_chat_param( WP_REST_Request $request, string $key ): string {
	$v = $request->get_param( $key );
	return is_scalar( $v ) ? (string) $v : '';
}

function er_chat_visitor_from( WP_REST_Request $request ): ?array {
	$token = (string) $request->get_header( 'x_er_chat' );
	return er_chat_for_visitor( er_chat_param( $request, 'id' ), $token );
}

function er_chat_no_store( $data ) {
	$response = rest_ensure_response( $data );
	$response->header( 'Cache-Control', 'no-store, private' );
	$response->header( 'X-Robots-Tag', 'noindex, nofollow' );
	return $response;
}

/* -------------------------------------------------------------------------- */
/* Visitor endpoints (POST only, so no page or CDN cache ever stores them)     */
/* -------------------------------------------------------------------------- */

/** Chat is part of the Trip assistant: on only while both are on. */
function er_chat_enabled(): bool {
	return 'off' !== er_assistant_mode() && 'off' !== er_settings( 'chat_mode' );
}

add_action( 'rest_api_init', static function () {
	// The team's routes stay available when the chat is switched off, so open conversations can be finished.
	$team = [ 'permission_callback' => static fn () => current_user_can( ER_CHAT_CAP ) ]; // cookie auth + REST nonce
	register_rest_route( 'egypt-roamer/v1', '/chat/team/list', $team + [ 'methods' => 'GET', 'callback' => 'er_chat_rest_team_list' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/team/(?P<cid>\d+)', $team + [ 'methods' => 'GET', 'callback' => 'er_chat_rest_team_get' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/team/(?P<cid>\d+)/reply', $team + [ 'methods' => 'POST', 'callback' => 'er_chat_rest_team_reply' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/team/(?P<cid>\d+)/action', $team + [ 'methods' => 'POST', 'callback' => 'er_chat_rest_team_action' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/team/presence', $team + [ 'methods' => 'POST', 'callback' => 'er_chat_rest_team_presence' ] );
	if ( ! er_chat_enabled() ) {
		return;
	}
	$public = [ 'methods' => 'POST', 'permission_callback' => '__return_true' ]; // checked per request: token + rate limits
	register_rest_route( 'egypt-roamer/v1', '/chat/status', $public + [ 'callback' => static fn () => er_chat_no_store( [ 'online' => er_chat_team_online() ] ) ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/start', $public + [ 'callback' => 'er_chat_rest_start' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/poll', $public + [ 'callback' => 'er_chat_rest_poll' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/send', $public + [ 'callback' => 'er_chat_rest_send' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/human', $public + [ 'callback' => 'er_chat_rest_human' ] );
	register_rest_route( 'egypt-roamer/v1', '/chat/end', $public + [ 'callback' => 'er_chat_rest_end' ] );
} );

/** Start a conversation with the team (optionally carrying the assistant questions asked so far). */
function er_chat_rest_start( WP_REST_Request $request ) {
	// Bots: a hidden field humans leave empty, and a form filled in faster than a person can type.
	if ( '' !== er_chat_param( $request, 'hp' ) || (int) $request->get_param( 'elapsed' ) < 1500 ) {
		return er_chat_error( 'er_chat_rejected', 'Rejected.', 400 );
	}
	$text = er_chat_clean( er_chat_param( $request, 'message' ) );
	if ( '' === $text ) {
		return er_chat_error( 'er_chat_empty', 'Empty message.', 400 );
	}
	if ( ! er_chat_rate( 'er_ch_start_all', ER_CHAT_START_TOTAL, HOUR_IN_SECONDS ) || ! er_chat_rate( er_chat_ip_key( 'start' ), ER_CHAT_START_RATE, HOUR_IN_SECONDS ) ) {
		return er_chat_error( 'er_chat_rate', 'Too many conversations.', 429 );
	}
	$email = sanitize_email( er_chat_param( $request, 'email' ) );
	$langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : [];
	$lang  = sanitize_key( er_chat_param( $request, 'lang' ) );
	$lang  = $langs && ! in_array( $lang, $langs, true ) ? (string) pll_default_language() : $lang;
	$token = bin2hex( random_bytes( 32 ) );
	$now   = er_chat_now();
	$page  = er_chat_clean_url( er_chat_param( $request, 'page' ) );
	global $wpdb;
	$wpdb->insert( er_chat_table( 'conversations' ), [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		'public_id'       => bin2hex( random_bytes( 12 ) ),
		'token_hash'      => er_chat_token_hash( $token ),
		'status'          => er_chat_team_online() ? 'requested' : 'waiting',
		'visitor_name'    => er_chat_clean( er_chat_param( $request, 'name' ), ER_CHAT_NAME_CHARS ),
		'visitor_email'   => is_email( $email ) ? $email : '',
		'lang'            => $lang,
		'entry_url'       => $page,
		'current_url'     => $page,
		'created_at'      => $now,
		'updated_at'      => $now,
		'last_message_at' => $now,
	] );
	$cid = (int) $wpdb->insert_id;
	if ( ! $cid ) {
		return er_chat_error( 'er_chat_store', 'Could not start the conversation.', 500 );
	}
	// The assistant exchange so far (questions and the page titles it answered with), so nobody has to repeat it.
	$transcript = $request->get_param( 'transcript' );
	$transcript = is_array( $transcript ) ? array_slice( $transcript, -10 ) : [];
	foreach ( $transcript as $turn ) {
		if ( ! is_array( $turn ) || ! is_scalar( $turn['q'] ?? null ) ) {
			continue;
		}
		$q = er_chat_clean( (string) ( $turn['q'] ?? '' ), 300 );
		if ( '' === $q ) {
			continue;
		}
		er_chat_add( $cid, 'visitor', $q, [ 'meta' => [ 'assistant' => true ] ] );
		$titles = array_slice( array_filter( array_map( static fn ( $t ) => is_scalar( $t ) ? er_chat_clean( (string) $t, 120 ) : '', is_array( $turn['a'] ?? null ) ? $turn['a'] : [] ) ), 0, 4 );
		er_chat_add( $cid, 'ai', $titles ? implode( "\n", $titles ) : '—', [ 'meta' => [ 'assistant' => true, 'titles' => $titles ] ] );
	}
	er_chat_add( $cid, 'visitor', $text, [ 'client_id' => er_chat_client_id( $request ) ] );
	er_chat_add( $cid, 'system', 'started', [ 'internal' => true ] );
	er_chat_notify( $cid, 'new' );
	$conv = er_chat_get( $cid );
	return er_chat_no_store( er_chat_visitor_view( $conv ) + [ 'token' => $token ] );
}

function er_chat_client_id( WP_REST_Request $request ): string {
	$id = er_chat_param( $request, 'client_id' );
	return preg_match( '/^[A-Za-z0-9_-]{8,40}$/', $id ) ? $id : '';
}

/** What a visitor may see of their own conversation. */
function er_chat_visitor_view( array $conv, int $after = 0 ): array {
	$status = (string) $conv['status'];
	return [
		'id'       => (string) $conv['public_id'],
		'status'   => 'archived' === $status ? 'closed' : $status,
		'online'   => er_chat_team_online(),
		'messages' => er_chat_messages( (int) $conv['id'], $after ),
	];
}

function er_chat_rest_poll( WP_REST_Request $request ) {
	$conv = er_chat_visitor_from( $request );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_forbidden', 'Not found.', 404 );
	}
	return er_chat_no_store( er_chat_visitor_view( $conv, max( 0, (int) $request->get_param( 'after' ) ) ) );
}

function er_chat_rest_send( WP_REST_Request $request ) {
	$conv = er_chat_visitor_from( $request );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_forbidden', 'Not found.', 404 );
	}
	$text = er_chat_clean( er_chat_param( $request, 'text' ) );
	if ( '' === $text ) {
		return er_chat_error( 'er_chat_empty', 'Empty message.', 400 );
	}
	$cid    = (int) $conv['id'];
	$client = er_chat_client_id( $request );
	// A retry of a message that already arrived (lost response): no duplicate.
	if ( '' !== $client ) {
		global $wpdb;
		$dup = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . er_chat_table( 'messages' ) . ' WHERE conversation_id = %d AND client_id = %s', $cid, $client ) ); // phpcs:ignore WordPress.DB
		if ( $dup ) {
			return er_chat_no_store( er_chat_visitor_view( $conv, max( 0, (int) $request->get_param( 'after' ) ) ) );
		}
	}
	if ( ! er_chat_rate( 'er_ch_send_' . $cid, ER_CHAT_SEND_RATE, MINUTE_IN_SECONDS ) ) {
		return er_chat_error( 'er_chat_rate', 'Too many messages.', 429 );
	}
	$page = er_chat_clean_url( er_chat_param( $request, 'page' ) );
	if ( '' !== $page ) {
		er_chat_update( $cid, [ 'current_url' => $page ] );
	}
	er_chat_add( $cid, 'visitor', $text, [ 'client_id' => $client ] );
	$status = (string) $conv['status'];
	if ( in_array( $status, [ 'closed', 'archived' ], true ) ) {
		// Writing again reopens it: back to the team.
		er_chat_update( $cid, [ 'status' => er_chat_team_online() ? 'requested' : 'waiting', 'closed_at' => null ] );
		er_chat_add( $cid, 'system', 'reopened by visitor', [ 'internal' => true ] );
		er_chat_notify( $cid, 'reopened' );
	} elseif ( 'ai' === $status ) {
		er_chat_ai_reply( $cid, $text, (string) $conv['lang'] );
	}
	return er_chat_no_store( er_chat_visitor_view( er_chat_get( $cid ), max( 0, (int) $request->get_param( 'after' ) ) ) );
}

/** In "ai" status the assistant answers (same retrieval as the drawer), and the answer is kept in the conversation. */
function er_chat_ai_reply( int $cid, string $q, string $lang ): void {
	$items  = er_assistant_find( er_assistant_clean( $q ), $lang );
	$answer = 'ai' === er_assistant_mode() && $items ? er_assistant_ai_answer( er_assistant_clean( $q ), $lang, $items ) : null;
	$items  = array_map( static fn ( $i ) => array_diff_key( $i, [ 'context' => 1 ] ), $items );
	er_chat_add( $cid, 'ai', (string) ( $answer ?? '' ), [ 'meta' => [ 'items' => $items, 'browse' => $items ? [] : er_assistant_browse_links( $lang ) ] ] );
}

/** The visitor asks for a person again (after the team handed the conversation back to the assistant). */
function er_chat_rest_human( WP_REST_Request $request ) {
	$conv = er_chat_visitor_from( $request );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_forbidden', 'Not found.', 404 );
	}
	if ( ! er_chat_rate( 'er_ch_send_' . (int) $conv['id'], ER_CHAT_SEND_RATE, MINUTE_IN_SECONDS ) ) {
		return er_chat_error( 'er_chat_rate', 'Too many requests.', 429 );
	}
	if ( in_array( $conv['status'], [ 'ai', 'closed', 'archived' ], true ) ) {
		er_chat_update( (int) $conv['id'], [ 'status' => er_chat_team_online() ? 'requested' : 'waiting', 'closed_at' => null ] );
		er_chat_add( (int) $conv['id'], 'system', 'person requested', [ 'internal' => true ] );
		er_chat_notify( (int) $conv['id'], 'reopened' );
	}
	return er_chat_no_store( er_chat_visitor_view( er_chat_get( (int) $conv['id'] ), max( 0, (int) $request->get_param( 'after' ) ) ) );
}

function er_chat_rest_end( WP_REST_Request $request ) {
	$conv = er_chat_visitor_from( $request );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_forbidden', 'Not found.', 404 );
	}
	if ( ! in_array( $conv['status'], [ 'closed', 'archived' ], true ) ) {
		er_chat_update( (int) $conv['id'], [ 'status' => 'closed', 'closed_at' => er_chat_now() ] );
		er_chat_add( (int) $conv['id'], 'system', 'closed by visitor', [ 'internal' => true ] );
	}
	return er_chat_no_store( er_chat_visitor_view( er_chat_get( (int) $conv['id'] ) ) );
}

/* -------------------------------------------------------------------------- */
/* Team endpoints                                                              */
/* -------------------------------------------------------------------------- */

function er_chat_seen(): void {
	update_user_meta( get_current_user_id(), 'er_chat_seen', time() );
}

/** Inbox rows: filters, search, sort, unread counts. */
function er_chat_rest_team_list( WP_REST_Request $request ) {
	global $wpdb;
	er_chat_seen();
	$c      = er_chat_table( 'conversations' );
	$m      = er_chat_table( 'messages' );
	$filter = sanitize_key( er_chat_param( $request, 'filter' ) );
	$where  = [ '1=1' ];
	$args   = [];
	switch ( $filter ) {
		case 'active':
			$where[] = "c.status IN ('requested','waiting','human')";
			break;
		case 'waiting':
			$where[] = "c.status IN ('requested','waiting')";
			break;
		case 'mine':
			$where[] = 'c.assigned_to = %d';
			$args[]  = get_current_user_id();
			$where[] = "c.status <> 'archived'";
			break;
		case 'closed':
			$where[] = "c.status IN ('closed','archived')";
			break;
		case 'ai':
			$where[] = "c.status = 'ai'";
			break;
		case 'human':
			$where[] = "c.status = 'human'";
			break;
		case 'unread':
			$where[] = "c.status <> 'archived'";
			break;
		default:
			$where[] = "c.status <> 'archived'";
	}
	$q = trim( er_chat_param( $request, 'q' ) );
	if ( '' !== $q ) {
		$like    = '%' . $wpdb->esc_like( mb_substr( $q, 0, 100 ) ) . '%';
		$where[] = "(c.visitor_name LIKE %s OR c.visitor_email LIKE %s OR c.public_id LIKE %s OR CAST(c.id AS CHAR) = %s OR EXISTS (SELECT 1 FROM {$m} s WHERE s.conversation_id = c.id AND s.internal = 0 AND s.body LIKE %s))";
		array_push( $args, $like, $like, $like, $q, $like );
	}
	$order = 'oldest' === $request->get_param( 'sort' ) ? 'c.created_at ASC' : ( 'newest' === $request->get_param( 'sort' ) ? 'c.created_at DESC' : 'c.last_message_at DESC' );
	$sql   = "SELECT c.*, (SELECT COUNT(*) FROM {$m} u WHERE u.conversation_id = c.id AND u.sender = 'visitor' AND u.id > c.agent_read_id) AS unread,
		(SELECT body FROM {$m} l WHERE l.conversation_id = c.id AND l.internal = 0 ORDER BY l.id DESC LIMIT 1) AS preview
		FROM {$c} c WHERE " . implode( ' AND ', $where ) . " ORDER BY {$order} LIMIT 200";
	$rows  = $wpdb->get_results( $args ? $wpdb->prepare( $sql, $args ) : $sql, ARRAY_A ) ?: []; // phpcs:ignore WordPress.DB -- identifiers are fixed above, values prepared
	if ( 'unread' === $filter ) {
		$rows = array_values( array_filter( $rows, static fn ( $r ) => (int) $r['unread'] > 0 ) );
	}
	return er_chat_no_store( [
		'rows'   => array_map( 'er_chat_team_row', $rows ),
		'unread' => er_chat_unread_count(),
		'online' => er_chat_online_agents(),
		'me'     => [ 'id' => get_current_user_id(), 'presence' => get_user_meta( get_current_user_id(), 'er_chat_presence', true ) ?: 'online' ],
	] );
}

function er_chat_team_row( array $r ): array {
	$user = (int) $r['assigned_to'] ? get_userdata( (int) $r['assigned_to'] ) : null;
	return [
		'id'       => (int) $r['id'],
		'public'   => (string) $r['public_id'],
		'status'   => (string) $r['status'],
		'name'     => (string) $r['visitor_name'],
		'email'    => (string) $r['visitor_email'],
		'lang'     => (string) $r['lang'],
		'entry'    => (string) $r['entry_url'],
		'page'     => (string) $r['current_url'],
		'assigned' => $user ? [ 'id' => $user->ID, 'name' => $user->display_name ] : null,
		'created'  => mysql2date( 'c', $r['created_at'] . ' +00:00' ),
		'last'     => mysql2date( 'c', $r['last_message_at'] . ' +00:00' ),
		'unread'   => (int) ( $r['unread'] ?? 0 ),
		'preview'  => isset( $r['preview'] ) ? mb_substr( (string) $r['preview'], 0, 120 ) : '',
	];
}

/** Visitor messages the team has not read, across open conversations. */
function er_chat_unread_count(): int {
	global $wpdb;
	$c = er_chat_table( 'conversations' );
	$m = er_chat_table( 'messages' );
	return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT c.id) FROM {$c} c JOIN {$m} u ON u.conversation_id = c.id AND u.sender = 'visitor' AND u.id > c.agent_read_id WHERE c.status <> 'archived'" ); // phpcs:ignore WordPress.DB
}

function er_chat_rest_team_get( WP_REST_Request $request ) {
	er_chat_seen();
	$conv = er_chat_get( (int) $request['cid'] );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_missing', 'Not found.', 404 );
	}
	$after = max( 0, (int) $request->get_param( 'after' ) );
	$data  = [
		'conversation' => er_chat_team_row( $conv + [ 'unread' => 0 ] ),
		'messages'     => er_chat_messages( (int) $conv['id'], $after, true ),
		'agents'       => array_map( static fn ( $u ) => [ 'id' => $u->ID, 'name' => $u->display_name ], get_users( [ 'capability' => ER_CHAT_CAP, 'fields' => [ 'ID', 'display_name' ] ] ) ),
	];
	// Opening a conversation marks it read (unless the agent explicitly marks it unread again).
	if ( '1' === er_chat_param( $request, 'read' ) ) {
		global $wpdb;
		$last = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(id) FROM ' . er_chat_table( 'messages' ) . ' WHERE conversation_id = %d', (int) $conv['id'] ) ); // phpcs:ignore WordPress.DB
		if ( $last > (int) $conv['agent_read_id'] ) {
			er_chat_update( (int) $conv['id'], [ 'agent_read_id' => $last ] );
		}
	}
	return er_chat_no_store( $data );
}

function er_chat_rest_team_reply( WP_REST_Request $request ) {
	er_chat_seen();
	$conv = er_chat_get( (int) $request['cid'] );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_missing', 'Not found.', 404 );
	}
	$text = er_chat_clean( er_chat_param( $request, 'text' ) );
	if ( '' === $text ) {
		return er_chat_error( 'er_chat_empty', 'Empty message.', 400 );
	}
	$cid    = (int) $conv['id'];
	$client = er_chat_client_id( $request );
	global $wpdb;
	if ( '' !== $client && $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . er_chat_table( 'messages' ) . ' WHERE conversation_id = %d AND client_id = %s', $cid, $client ) ) ) { // phpcs:ignore WordPress.DB
		return er_chat_no_store( [ 'ok' => true, 'duplicate' => true ] );
	}
	// Replying takes the conversation over: the assistant stops answering, the replying member owns it.
	$fields = [];
	if ( 'human' !== $conv['status'] ) {
		$fields['status']    = 'human';
		$fields['closed_at'] = null;
	}
	if ( ! (int) $conv['assigned_to'] ) {
		$fields['assigned_to'] = get_current_user_id();
	}
	if ( $fields ) {
		er_chat_update( $cid, $fields );
	}
	$id = er_chat_add( $cid, 'agent', $text, [ 'user_id' => get_current_user_id(), 'client_id' => $client ] );
	er_chat_update( $cid, [ 'agent_read_id' => max( $id, (int) $conv['agent_read_id'] ) ] );
	return er_chat_no_store( [ 'ok' => true, 'id' => $id ] );
}

function er_chat_rest_team_action( WP_REST_Request $request ) {
	er_chat_seen();
	$conv = er_chat_get( (int) $request['cid'] );
	if ( ! $conv ) {
		return er_chat_error( 'er_chat_missing', 'Not found.', 404 );
	}
	$cid    = (int) $conv['id'];
	$me     = get_current_user_id();
	$action = sanitize_key( er_chat_param( $request, 'action' ) );
	$note   = '';
	switch ( $action ) {
		case 'takeover':
			er_chat_update( $cid, [ 'status' => 'human', 'assigned_to' => $me, 'closed_at' => null ] );
			$note = 'taken over';
			break;
		case 'return_ai':
			er_chat_update( $cid, [ 'status' => 'ai' ] );
			$note = 'returned to the assistant';
			break;
		case 'close':
			er_chat_update( $cid, [ 'status' => 'closed', 'closed_at' => er_chat_now() ] );
			$note = 'closed';
			break;
		case 'reopen':
			er_chat_update( $cid, [ 'status' => (int) $conv['assigned_to'] ? 'human' : 'waiting', 'closed_at' => null ] );
			$note = 'reopened';
			break;
		case 'archive':
			er_chat_update( $cid, [ 'status' => 'archived', 'closed_at' => $conv['closed_at'] ?: er_chat_now() ] );
			$note = 'archived';
			break;
		case 'assign':
			$user = (int) $request->get_param( 'user' );
			if ( $user && ! user_can( $user, ER_CHAT_CAP ) ) {
				return er_chat_error( 'er_chat_user', 'That user cannot answer conversations.', 400 );
			}
			er_chat_update( $cid, [ 'assigned_to' => $user ] );
			$note = $user ? 'assigned to #' . $user : 'unassigned';
			break;
		case 'read':
			global $wpdb;
			er_chat_update( $cid, [ 'agent_read_id' => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(id) FROM ' . er_chat_table( 'messages' ) . ' WHERE conversation_id = %d', $cid ) ) ] ); // phpcs:ignore WordPress.DB
			break;
		case 'unread':
			global $wpdb;
			$last_visitor = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(id) FROM ' . er_chat_table( 'messages' ) . " WHERE conversation_id = %d AND sender = 'visitor'", $cid ) ); // phpcs:ignore WordPress.DB
			er_chat_update( $cid, [ 'agent_read_id' => max( 0, $last_visitor - 1 ) ] );
			break;
		default:
			return er_chat_error( 'er_chat_action', 'Unknown action.', 400 );
	}
	if ( '' !== $note ) {
		// Audit trail: who did what, kept with the conversation (never message contents in logs).
		er_chat_add( $cid, 'system', $note, [ 'internal' => true, 'user_id' => $me ] );
	}
	return er_chat_no_store( [ 'ok' => true, 'conversation' => er_chat_team_row( er_chat_get( $cid ) + [ 'unread' => 0 ] ) ] );
}

function er_chat_rest_team_presence( WP_REST_Request $request ) {
	$state = sanitize_key( er_chat_param( $request, 'state' ) );
	if ( in_array( $state, [ 'online', 'away', 'offline' ], true ) ) {
		update_user_meta( get_current_user_id(), 'er_chat_presence', $state );
	}
	er_chat_seen();
	return er_chat_no_store( [ 'ok' => true, 'online' => er_chat_online_agents(), 'unread' => er_chat_unread_count() ] );
}

/* -------------------------------------------------------------------------- */
/* Notifications                                                               */
/* -------------------------------------------------------------------------- */

/**
 * Email the team about a new or reopened conversation (a notification only: the conversation itself is in
 * WordPress). At most one email per conversation per 10 minutes. Delivery depends on the site's mail setup.
 */
function er_chat_notify( int $cid, string $event ): void {
	if ( get_transient( 'er_ch_mail_' . $cid ) ) {
		return;
	}
	set_transient( 'er_ch_mail_' . $cid, 1, 10 * MINUTE_IN_SECONDS );
	$conv = er_chat_get( $cid );
	$to   = (string) er_settings( 'contact_email' ) ?: (string) get_option( 'admin_email' );
	if ( ! $conv || ! is_email( $to ) ) {
		return;
	}
	$subject = 'new' === $event ? __( 'New chat on Egypt Roamer', 'egypt-roamer-core' ) : __( 'A chat on Egypt Roamer was reopened', 'egypt-roamer-core' );
	$body    = sprintf(
		"%s\n\n%s: %s\n%s: %s\n%s: %s\n\n%s\n%s",
		$subject,
		__( 'Visitor', 'egypt-roamer-core' ),
		$conv['visitor_name'] ?: '—',
		__( 'Language', 'egypt-roamer-core' ),
		$conv['lang'] ?: '—',
		__( 'Page', 'egypt-roamer-core' ),
		$conv['current_url'] ?: '—',
		__( 'Open the conversation:', 'egypt-roamer-core' ),
		admin_url( 'admin.php?page=er-conversations#' . $cid )
	);
	wp_mail( $to, '[Egypt Roamer] ' . $subject, $body ); // the message text itself is not emailed
}

/* -------------------------------------------------------------------------- */
/* Admin: Egypt Roamer → Conversations (with an unread badge kept current by the Heartbeat API)            */
/* -------------------------------------------------------------------------- */

add_action( 'admin_menu', static function () {
	$unread = current_user_can( ER_CHAT_CAP ) ? er_chat_unread_count() : 0;
	$label  = __( 'Conversations', 'egypt-roamer-core' ) . ' <span class="awaiting-mod count-' . $unread . ' er-chat-badge"' . ( $unread ? '' : ' style="display:none"' ) . '><span class="pending-count">' . number_format_i18n( $unread ) . '</span></span>';
	add_submenu_page( 'egypt-roamer', __( 'Conversations', 'egypt-roamer-core' ), $label, ER_CHAT_CAP, 'er-conversations', 'er_chat_render_admin' );
}, 15 );

// The top-level Egypt Roamer menu needs edit_posts; team members get to the inbox through it.
add_filter( 'heartbeat_received', static function ( $response, $data ) {
	if ( ! empty( $data['er_chat'] ) && current_user_can( ER_CHAT_CAP ) ) {
		$response['er_chat'] = [ 'unread' => er_chat_unread_count() ];
	}
	return $response;
}, 10, 2 );

add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	if ( ! current_user_can( ER_CHAT_CAP ) ) {
		return;
	}
	// Every admin screen: keep the menu badge current (WordPress Heartbeat, no extra request).
	wp_enqueue_script( 'heartbeat' );
	wp_add_inline_script( 'heartbeat', "jQuery(document).on('heartbeat-send',function(e,d){d.er_chat=1}).on('heartbeat-tick',function(e,d){if(!d.er_chat)return;var n=d.er_chat.unread|0;document.querySelectorAll('.er-chat-badge').forEach(function(b){b.style.display=n?'':'none';b.className=b.className.replace(/count-\\d+/,'count-'+n);var p=b.querySelector('.pending-count');if(p)p.textContent=n})})" );
	if ( 'egypt-roamer_page_er-conversations' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'er-chat-admin', ER_CORE_URL . 'assets/chat-admin.css', [], ER_CORE_VERSION );
	wp_enqueue_script( 'er-chat-admin', ER_CORE_URL . 'assets/chat-admin.js', [], ER_CORE_VERSION, true );
	wp_localize_script( 'er-chat-admin', 'erChat', [
		'root'  => esc_url_raw( rest_url( 'egypt-roamer/v1/chat/team/' ) ),
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'me'       => get_current_user_id(),
		'presence' => (string) get_user_meta( get_current_user_id(), 'er_chat_presence', true ),
		'home'  => home_url( '/' ),
		'i18n'  => [
			'status'   => [ 'ai' => __( 'Assistant', 'egypt-roamer-core' ), 'requested' => __( 'Asked for the team', 'egypt-roamer-core' ), 'waiting' => __( 'Waiting (left a message)', 'egypt-roamer-core' ), 'human' => __( 'With the team', 'egypt-roamer-core' ), 'closed' => __( 'Closed', 'egypt-roamer-core' ), 'archived' => __( 'Archived', 'egypt-roamer-core' ) ],
			'sender'   => [ 'visitor' => __( 'Visitor', 'egypt-roamer-core' ), 'agent' => __( 'Team', 'egypt-roamer-core' ), 'ai' => __( 'Assistant', 'egypt-roamer-core' ), 'system' => __( 'Note', 'egypt-roamer-core' ) ],
			'empty'    => __( 'No conversations here.', 'egypt-roamer-core' ),
			'pick'     => __( 'Choose a conversation.', 'egypt-roamer-core' ),
			'sending'  => __( 'Sending…', 'egypt-roamer-core' ),
			'failed'   => __( 'Message couldn’t be sent. Try again.', 'egypt-roamer-core' ),
			'retry'    => __( 'Retry', 'egypt-roamer-core' ),
			'offline'  => __( 'Connection interrupted. Retrying…', 'egypt-roamer-core' ),
			'noname'   => __( 'Visitor', 'egypt-roamer-core' ),
			'pages'    => __( 'Pages the assistant suggested', 'egypt-roamer-core' ),
			/* translators: %d: number of team members online */
			'online'   => __( '● %d online: visitors see “Team is online”', 'egypt-roamer-core' ),
			'nobody'   => __( '○ Nobody online: visitors are asked to leave a message', 'egypt-roamer-core' ),
			'fields'   => [ 'name' => __( 'Visitor', 'egypt-roamer-core' ), 'email' => __( 'Email', 'egypt-roamer-core' ), 'lang' => __( 'Language', 'egypt-roamer-core' ), 'page' => __( 'Current page', 'egypt-roamer-core' ), 'entry' => __( 'Entry page', 'egypt-roamer-core' ), 'status' => __( 'Status', 'egypt-roamer-core' ), 'assigned' => __( 'Assigned to', 'egypt-roamer-core' ), 'created' => __( 'Started', 'egypt-roamer-core' ), 'last' => __( 'Last activity', 'egypt-roamer-core' ), 'id' => 'ID' ],
			'confirm'  => __( 'Archive this conversation? It leaves the inbox and is deleted with the other closed conversations after the retention period.', 'egypt-roamer-core' ),
		],
	] );
} );

function er_chat_render_admin(): void {
	if ( ! current_user_can( ER_CHAT_CAP ) ) {
		return;
	}
	$filters = [ 'all' => __( 'All', 'egypt-roamer-core' ), 'unread' => __( 'Unread', 'egypt-roamer-core' ), 'active' => __( 'Active', 'egypt-roamer-core' ), 'waiting' => __( 'Waiting', 'egypt-roamer-core' ), 'mine' => __( 'Mine', 'egypt-roamer-core' ), 'ai' => __( 'Assistant', 'egypt-roamer-core' ), 'human' => __( 'With the team', 'egypt-roamer-core' ), 'closed' => __( 'Closed', 'egypt-roamer-core' ) ];
	?>
	<div class="wrap er-chat" data-er-chat>
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Conversations', 'egypt-roamer-core' ); ?></h1>
		<label class="er-chat__presence"><?php esc_html_e( 'Your status', 'egypt-roamer-core' ); ?>
			<select data-presence>
				<option value="online"><?php esc_html_e( 'Online', 'egypt-roamer-core' ); ?></option>
				<option value="away"><?php esc_html_e( 'Away', 'egypt-roamer-core' ); ?></option>
				<option value="offline"><?php esc_html_e( 'Offline', 'egypt-roamer-core' ); ?></option>
			</select>
		</label>
		<p class="er-chat__team" data-team aria-live="polite"></p>
		<p class="description"><?php esc_html_e( 'Visitors see “Team is online” while at least one member is online here (this page open, status Online). Otherwise they can leave a message. Messages are plain text; closed conversations are deleted after the retention period (Settings).', 'egypt-roamer-core' ); ?></p>
		<div class="er-chat__grid">
			<section class="er-chat__inbox" aria-label="<?php esc_attr_e( 'Inbox', 'egypt-roamer-core' ); ?>">
				<div class="er-chat__tools">
					<label class="screen-reader-text" for="er-chat-q"><?php esc_html_e( 'Search conversations', 'egypt-roamer-core' ); ?></label>
					<input type="search" id="er-chat-q" data-search placeholder="<?php esc_attr_e( 'Name, email, ID or text', 'egypt-roamer-core' ); ?>" />
					<label class="screen-reader-text" for="er-chat-sort"><?php esc_html_e( 'Sort', 'egypt-roamer-core' ); ?></label>
					<select id="er-chat-sort" data-sort>
						<option value="activity"><?php esc_html_e( 'Last activity', 'egypt-roamer-core' ); ?></option>
						<option value="newest"><?php esc_html_e( 'Newest', 'egypt-roamer-core' ); ?></option>
						<option value="oldest"><?php esc_html_e( 'Oldest', 'egypt-roamer-core' ); ?></option>
					</select>
				</div>
				<div class="er-chat__filters" role="group" aria-label="<?php esc_attr_e( 'Filter', 'egypt-roamer-core' ); ?>">
					<?php foreach ( $filters as $key => $label ) : ?>
						<button type="button" class="button" data-filter="<?php echo esc_attr( $key ); ?>" aria-pressed="<?php echo 'all' === $key ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
				<ul class="er-chat__list" data-list aria-live="polite"></ul>
			</section>
			<section class="er-chat__thread" data-thread aria-label="<?php esc_attr_e( 'Conversation', 'egypt-roamer-core' ); ?>" hidden>
				<button type="button" class="button-link er-chat__back" data-back><?php esc_html_e( '← Inbox', 'egypt-roamer-core' ); ?></button>
				<dl class="er-chat__info" data-info></dl>
				<div class="er-chat__actions" data-actions>
					<button type="button" class="button" data-action="takeover"><?php esc_html_e( 'Take over conversation', 'egypt-roamer-core' ); ?></button>
					<button type="button" class="button" data-action="return_ai"><?php esc_html_e( 'Return to AI', 'egypt-roamer-core' ); ?></button>
					<button type="button" class="button" data-action="close"><?php esc_html_e( 'Close', 'egypt-roamer-core' ); ?></button>
					<button type="button" class="button" data-action="reopen"><?php esc_html_e( 'Reopen', 'egypt-roamer-core' ); ?></button>
					<button type="button" class="button" data-action="unread"><?php esc_html_e( 'Mark unread', 'egypt-roamer-core' ); ?></button>
					<button type="button" class="button-link-delete" data-action="archive"><?php esc_html_e( 'Archive', 'egypt-roamer-core' ); ?></button>
					<label><?php esc_html_e( 'Assigned to', 'egypt-roamer-core' ); ?> <select data-assign></select></label>
				</div>
				<ol class="er-chat__log" data-log aria-live="polite"></ol>
				<form class="er-chat__reply" data-reply>
					<label class="screen-reader-text" for="er-chat-reply"><?php esc_html_e( 'Reply', 'egypt-roamer-core' ); ?></label>
					<textarea id="er-chat-reply" rows="3" maxlength="<?php echo (int) ER_CHAT_MAX_CHARS; ?>" placeholder="<?php esc_attr_e( 'Type a reply… (Ctrl+Enter sends)', 'egypt-roamer-core' ); ?>" required></textarea>
					<button class="button button-primary" type="submit"><?php esc_html_e( 'Send', 'egypt-roamer-core' ); ?></button>
					<p class="er-chat__state" data-state role="status"></p>
				</form>
			</section>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------- */
/* Retention, privacy                                                          */
/* -------------------------------------------------------------------------- */

add_action( 'init', static function () {
	if ( ! wp_next_scheduled( 'er_chat_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'er_chat_purge' );
	}
} );

/**
 * Delete conversations nobody needs any more: closed/archived ones, and assistant-only ones, whose last
 * message is older than the retention period. Open conversations (requested, waiting, with the team) are
 * never deleted.
 */
add_action( 'er_chat_purge', 'er_chat_purge' );
function er_chat_purge(): int {
	global $wpdb;
	$days = (int) er_settings( 'chat_retention_days' );
	if ( $days <= 0 ) {
		return 0;
	}
	$c   = er_chat_table( 'conversations' );
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$c} WHERE status IN ('closed','archived','ai') AND last_message_at < %s", gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
	er_chat_delete( array_map( 'intval', $ids ) );
	return count( $ids );
}

function er_chat_delete( array $ids ): void {
	global $wpdb;
	$ids = array_filter( array_map( 'intval', $ids ) );
	if ( ! $ids ) {
		return;
	}
	$in = implode( ',', $ids );
	$wpdb->query( 'DELETE FROM ' . er_chat_table( 'messages' ) . " WHERE conversation_id IN ({$in})" ); // phpcs:ignore WordPress.DB
	$wpdb->query( 'DELETE FROM ' . er_chat_table( 'conversations' ) . " WHERE id IN ({$in})" ); // phpcs:ignore WordPress.DB
}

add_filter( 'wp_privacy_personal_data_exporters', static function ( $exporters ) {
	$exporters['egypt-roamer-chat'] = [
		'exporter_friendly_name' => __( 'Egypt Roamer chat conversations', 'egypt-roamer-core' ),
		'callback'               => static function ( $email ) {
			global $wpdb;
			$items = [];
			foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . er_chat_table( 'conversations' ) . ' WHERE visitor_email = %s', $email ), ARRAY_A ) ?: [] as $conv ) { // phpcs:ignore WordPress.DB
				$lines = array_map( static fn ( $m ) => $m['time'] . ' ' . $m['sender'] . ': ' . $m['body'], er_chat_messages( (int) $conv['id'] ) );
				$items[] = [
					'group_id'    => 'er-chat',
					'group_label' => __( 'Chat conversations', 'egypt-roamer-core' ),
					'item_id'     => 'er-chat-' . $conv['public_id'],
					'data'        => [
						[ 'name' => __( 'Name', 'egypt-roamer-core' ), 'value' => $conv['visitor_name'] ],
						[ 'name' => __( 'Started', 'egypt-roamer-core' ), 'value' => $conv['created_at'] ],
						[ 'name' => __( 'Messages', 'egypt-roamer-core' ), 'value' => implode( "\n", $lines ) ],
					],
				];
			}
			return [ 'data' => $items, 'done' => true ];
		},
	];
	return $exporters;
} );

add_filter( 'wp_privacy_personal_data_erasers', static function ( $erasers ) {
	$erasers['egypt-roamer-chat'] = [
		'eraser_friendly_name' => __( 'Egypt Roamer chat conversations', 'egypt-roamer-core' ),
		'callback'             => static function ( $email ) {
			global $wpdb;
			$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . er_chat_table( 'conversations' ) . ' WHERE visitor_email = %s', $email ) ); // phpcs:ignore WordPress.DB
			er_chat_delete( array_map( 'intval', $ids ) );
			return [ 'items_removed' => (bool) $ids, 'items_retained' => false, 'messages' => [], 'done' => true ];
		},
	];
	return $erasers;
} );
