<?php
/**
 * Legal & trust page translations (Privacy, Cookies, Affiliate Disclosure, Contact).
 *
 * The English pages are edited in WordPress. Their translations ship with Core in
 * data/legal/<lang>/<slug>.html (+ data/legal/index.json for titles and slugs) and are created or
 * updated by a sync, run from Egypt Roamer → Legal pages or `wp egypt-roamer legal [--dry-run]`:
 *   - a missing translation is created, published, and linked to the English page in Polylang;
 *   - a translation the sync created is updated only while nobody has edited it (content hash);
 *   - an edited or hand-made translation is left alone;
 *   - an English page is changed only where index.json names the exact content it replaces.
 * Pages missing from index.json (Terms, while it is a draft) are never touched.
 */

defined( 'ABSPATH' ) || exit;

function er_legal_index(): array {
	$file = ER_CORE_DIR . 'data/legal/index.json';
	return is_readable( $file ) ? ( json_decode( (string) file_get_contents( $file ), true ) ?: [] ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/** The published English page with this slug (Polylang's language filter off). */
function er_legal_en_page( string $slug ): int {
	$ids = get_posts( [ 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'lang' => '', 'suppress_filters' => false ] );
	return $ids ? (int) $ids[0] : 0;
}

/**
 * @return array{lines: string[], counts: array<string,int>}
 */
function er_legal_sync( bool $dry = true ): array {
	$index  = er_legal_index();
	$lines  = [];
	$counts = [ 'created' => 0, 'updated' => 0, 'current' => 0, 'kept' => 0, 'skipped' => 0 ];
	if ( ! $index || ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return [ 'lines' => [ 'Nothing to do: no data/legal/index.json or no Polylang.' ], 'counts' => $counts ];
	}
	$languages = (array) pll_languages_list();
	$default   = (string) pll_default_language();
	$read      = static fn ( string $rel ): string => trim( (string) file_get_contents( ER_CORE_DIR . 'data/legal/' . $rel ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	foreach ( (array) ( $index['pages'] ?? [] ) as $slug => $langs ) {
		$en = er_legal_en_page( (string) $slug );
		if ( ! $en || 'publish' !== get_post_status( $en ) ) {
			++$counts['skipped'];
			$lines[] = "Skipped {$slug}: the English page is not published.";
			continue;
		}

		// English: only a replacement named by the exact content it replaces.
		$swap = $index['en_updates'][ $slug ] ?? null;
		if ( $swap ) {
			$current = (string) get_post_field( 'post_content', $en );
			$new     = $read( (string) $swap['file'] );
			if ( trim( $current ) === $new ) {
				++$counts['current'];
			} elseif ( sha1( trim( $current ) ) === (string) $swap['replaces_sha1'] ) {
				if ( ! $dry ) {
					wp_update_post( wp_slash( [ 'ID' => $en, 'post_content' => $new ] ) );
				}
				++$counts['updated'];
				$lines[] = ( $dry ? 'Would update' : 'Updated' ) . " {$slug} (en, #{$en}).";
			} else {
				++$counts['kept'];
				$lines[] = "Kept {$slug} (en, #{$en}): edited in WordPress.";
			}
		}

		foreach ( (array) $langs as $lang => $meta ) {
			if ( $lang === $default || ! in_array( $lang, $languages, true ) ) {
				continue;
			}
			$file = "{$lang}/{$slug}.html";
			if ( ! is_readable( ER_CORE_DIR . 'data/legal/' . $file ) ) {
				continue;
			}
			$body  = $read( $file );
			$title = (string) $meta['title'];
			$tr    = (int) pll_get_post( $en, $lang );
			if ( $tr && pll_get_post_language( $tr ) === $lang ) {
				$content = trim( (string) get_post_field( 'post_content', $tr ) );
				$hash    = (string) get_post_meta( $tr, '_er_legal_hash', true );
				if ( $content === $body && get_the_title( $tr ) === $title ) {
					++$counts['current'];
				} elseif ( $hash && sha1( $content ) === $hash ) {
					if ( ! $dry ) {
						wp_update_post( wp_slash( [ 'ID' => $tr, 'post_title' => $title, 'post_content' => $body ] ) );
						update_post_meta( $tr, '_er_legal_hash', sha1( trim( (string) get_post_field( 'post_content', $tr ) ) ) );
					}
					++$counts['updated'];
					$lines[] = ( $dry ? 'Would update' : 'Updated' ) . " {$slug} ({$lang}, #{$tr}).";
				} else {
					++$counts['kept'];
					$lines[] = "Kept {$slug} ({$lang}, #{$tr}): edited in WordPress or not created by this sync.";
				}
				continue;
			}
			if ( $dry ) {
				++$counts['created'];
				$lines[] = "Would create {$slug} ({$lang}): \"{$title}\", /{$lang}/{$meta['slug']}/";
				continue;
			}
			$id = wp_insert_post( wp_slash( [
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => (string) $meta['slug'],
				'post_content' => $body,
				'post_author'  => (int) get_post_field( 'post_author', $en ),
			] ), true );
			if ( is_wp_error( $id ) ) {
				$lines[] = "Could not create {$slug} ({$lang}): " . $id->get_error_message();
				continue;
			}
			pll_set_post_language( (int) $id, $lang );
			$group             = (array) pll_get_post_translations( $en );
			$group[ $default ] = $en; // a page without translations yet has an empty group
			$group[ $lang ]    = (int) $id;
			pll_save_post_translations( $group );
			update_post_meta( (int) $id, '_er_legal_hash', sha1( trim( (string) get_post_field( 'post_content', (int) $id ) ) ) );
			++$counts['created'];
			$lines[] = "Created {$slug} ({$lang}, #{$id}): " . get_permalink( (int) $id );
		}
	}
	return [ 'lines' => $lines, 'counts' => $counts ];
}

/* -------------------------------------------------------------------------- */
/* wp-admin: Egypt Roamer → Legal pages                                        */
/* -------------------------------------------------------------------------- */

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Legal pages', 'egypt-roamer-core' ), __( 'Legal pages', 'egypt-roamer-core' ), 'manage_options', 'er-legal', 'er_render_legal_page' );
}, 20 );

function er_render_legal_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$report = get_transient( 'er_legal_report_' . get_current_user_id() );
	$preview = er_legal_sync( true );
	echo '<div class="wrap"><h1>' . esc_html__( 'Legal page translations', 'egypt-roamer-core' ) . '</h1>';
	echo '<p>' . esc_html__( 'Creates the missing translations of the published Privacy, Cookies, Affiliate Disclosure and Contact pages from the texts shipped with Egypt Roamer Core, and links them in Polylang. Translations edited in WordPress are never overwritten. Terms is not included while it is a draft.', 'egypt-roamer-core' ) . '</p>';
	if ( $report ) {
		delete_transient( 'er_legal_report_' . get_current_user_id() );
		echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Done.', 'egypt-roamer-core' ) . '</strong></p><pre>' . esc_html( implode( "\n", (array) $report ) ) . '</pre></div>';
	}
	echo '<h2>' . esc_html__( 'Preview', 'egypt-roamer-core' ) . '</h2><pre>' . esc_html( implode( "\n", $preview['lines'] ) ?: __( 'Everything is up to date.', 'egypt-roamer-core' ) ) . '</pre>';
	printf( '<p>%s</p>', esc_html( wp_json_encode( $preview['counts'] ) ) );
	if ( $preview['counts']['created'] || $preview['counts']['updated'] ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'er_legal_sync' );
		echo '<input type="hidden" name="action" value="er_legal_sync" />';
		submit_button( __( 'Create / update translations', 'egypt-roamer-core' ) );
		echo '</form>';
	}
	echo '</div>';
}

add_action( 'admin_post_er_legal_sync', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_legal_sync' );
	$result = er_legal_sync( false );
	set_transient( 'er_legal_report_' . get_current_user_id(), array_merge( $result['lines'], [ wp_json_encode( $result['counts'] ) ] ), 300 );
	wp_safe_redirect( admin_url( 'admin.php?page=er-legal' ) );
	exit;
} );
