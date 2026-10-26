<?php
/**
 * Editorial updates from wp-admin (Egypt Roamer → Editorial updates), for revised bodies the seed-only
 * `wp egypt-roamer editorial` would keep.
 *
 * Only the items listed in data/editorial/replaces.json are touched. A body is replaced when its plain text
 * (tags stripped, entities decoded, whitespace collapsed) still hashes to one of the earlier shipped versions
 * listed there, i.e. it was imported and never edited since. Any edit in WordPress changes the hash and the
 * body is kept. Translations need an approved, current file; their internal links point at the published
 * translation, or become text when there is none (as the CLI command does).
 */

defined( 'ABSPATH' ) || exit;

/** Plain text of a body, as compared by the editorial commands. */
function er_editorial_plain( string $html ): string {
	return trim( (string) preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' ) ) );
}

/** Point a translated body's internal links at the published translation in $lang; others become text. */
function er_editorial_tr_links( string $body, string $lang ): string {
	return (string) preg_replace_callback(
		'~<a href="(/[^"#]*)(#[^"]*)?">(.*?)</a>~s',
		static function ( $m ) use ( $lang ) {
			$source = url_to_postid( home_url( $m[1] ) );
			$target = $source ? (int) pll_get_post( $source, $lang ) : 0;
			if ( $target && 'publish' === get_post_status( $target ) ) {
				return '<a href="' . esc_attr( wp_make_link_relative( (string) get_permalink( $target ) ) . ( $m[2] ?? '' ) ) . '">' . $m[3] . '</a>';
			}
			return $m[3];
		},
		$body
	);
}

/**
 * @return array{lines: string[], counts: array<string,int>}
 */
function er_editorial_updates_sync( bool $dry = true ): array {
	$lines    = [];
	$counts   = [ 'updated' => 0, 'current' => 0, 'kept' => 0, 'skipped' => 0 ];
	$replaces = json_decode( (string) @file_get_contents( ER_CORE_DIR . 'data/editorial/replaces.json' ), true ) ?: []; // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
	if ( ! function_exists( 'pll_get_post' ) ) {
		return [ 'lines' => [ 'Polylang is not active.' ], 'counts' => $counts ];
	}
	$default = (string) pll_default_language();
	foreach ( $replaces as $seed_id => $by_lang ) {
		if ( '_' === substr( (string) $seed_id, 0, 1 ) || ! is_array( $by_lang ) ) {
			continue;
		}
		$en = 0;
		foreach ( get_posts( [ 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_er_seed_id', 'meta_value' => (string) $seed_id, 'lang' => '' ] ) as $id ) {
			if ( pll_get_post_language( (int) $id ) === $default ) {
				$en = (int) $id;
			}
		}
		if ( ! $en ) {
			++$counts['skipped'];
			$lines[] = "Skipped {$seed_id}: no {$default} original.";
			continue;
		}
		foreach ( $by_lang as $lang => $hashes ) {
			$lang  = sanitize_key( (string) $lang );
			$is_tr = $lang !== $default;
			$dir   = ER_CORE_DIR . 'data/editorial/' . ( $is_tr ? $lang : 'en' ) . '/';
			$index = is_readable( $dir . 'index.json' ) ? ( json_decode( (string) file_get_contents( $dir . 'index.json' ), true ) ?: [] ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
			$item  = $index[ $seed_id ] ?? null;
			$file  = $dir . $seed_id . '.html';
			if ( ! $item || ! is_readable( $file ) || ( $is_tr && ( 'approved' !== ( $item['review'] ?? '' ) || empty( $item['current'] ) ) ) ) {
				++$counts['skipped'];
				$lines[] = "Skipped {$seed_id} ({$lang}): no approved, current file.";
				continue;
			}
			$id = $is_tr ? (int) pll_get_post( $en, $lang ) : $en;
			if ( ! $id || pll_get_post_language( $id ) !== $lang ) {
				++$counts['skipped'];
				$lines[] = "Skipped {$seed_id} ({$lang}): no translation.";
				continue;
			}
			$body    = trim( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$new     = $is_tr ? er_editorial_tr_links( $body, $lang ) : $body;
			$current = (string) get_post_field( 'post_content', $id );
			if ( er_editorial_plain( $current ) === er_editorial_plain( $new ) ) {
				++$counts['current'];
				continue;
			}
			if ( ! in_array( sha1( er_editorial_plain( $current ) ), (array) $hashes, true ) ) {
				++$counts['kept'];
				$lines[] = "Kept {$seed_id} ({$lang}, #{$id}): edited in WordPress.";
				continue;
			}
			if ( ! $dry ) {
				wp_update_post( wp_slash( [ 'ID' => $id, 'post_content' => $new ] ) );
			}
			++$counts['updated'];
			$lines[] = ( $dry ? 'Would update' : 'Updated' ) . " {$seed_id} ({$lang}, #{$id}).";
		}
	}
	return [ 'lines' => $lines, 'counts' => $counts ];
}

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Editorial updates', 'egypt-roamer-core' ), __( 'Editorial updates', 'egypt-roamer-core' ), 'manage_options', 'er-editorial-updates', 'er_render_editorial_updates_page' );
}, 21 );

function er_render_editorial_updates_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$key     = 'er_editorial_upd_report_' . get_current_user_id();
	$report  = get_transient( $key );
	$preview = er_editorial_updates_sync( true );
	echo '<div class="wrap"><h1>' . esc_html__( 'Editorial updates', 'egypt-roamer-core' ) . '</h1>';
	echo '<p>' . esc_html__( 'Replaces revised bodies shipped with Egypt Roamer Core (destinations and other seeded pages, in every language) when the live body is still an earlier shipped version. Bodies edited in WordPress are never overwritten.', 'egypt-roamer-core' ) . '</p>';
	if ( $report ) {
		delete_transient( $key );
		echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Done.', 'egypt-roamer-core' ) . '</strong></p><pre>' . esc_html( implode( "\n", (array) $report ) ) . '</pre></div>';
	}
	echo '<h2>' . esc_html__( 'Preview', 'egypt-roamer-core' ) . '</h2><pre>' . esc_html( implode( "\n", $preview['lines'] ) ?: __( 'Everything is up to date.', 'egypt-roamer-core' ) ) . '</pre>';
	printf( '<p>%s</p>', esc_html( wp_json_encode( $preview['counts'] ) ) );
	if ( $preview['counts']['updated'] ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'er_editorial_upd' );
		echo '<input type="hidden" name="action" value="er_editorial_upd" />';
		submit_button( __( 'Update bodies', 'egypt-roamer-core' ) );
		echo '</form>';
	}
	echo '</div>';
}

add_action( 'admin_post_er_editorial_upd', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_editorial_upd' );
	$result = er_editorial_updates_sync( false );
	set_transient( 'er_editorial_upd_report_' . get_current_user_id(), array_merge( $result['lines'], [ wp_json_encode( $result['counts'] ) ] ), 300 );
	wp_safe_redirect( admin_url( 'admin.php?page=er-editorial-updates' ) );
	exit;
} );
