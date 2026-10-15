<?php
/**
 * Guide translations, published with a review note (owner decision, 2026-10-15).
 *
 * The translated guide bodies ship with Core in data/editorial/<lang>/g-*.html (built by tools/editorial.py
 * from content/editorial/<lang>/g-*.md, front matter `review: pending|approved`). This sync, run from
 * Egypt Roamer → Guide translations:
 *   - creates a missing translation of each published English guide, publishes it and links it in Polylang;
 *   - opens the body with a note (translated from English, not yet reviewed, link to the original) while the
 *     file is `review: pending`; an approved translation carries no note;
 *   - updates a translation it created only while nobody has edited it (content hash), so an editor's
 *     corrections are never overwritten;
 *   - skips translations whose English source changed since they were translated (`current` false).
 * Internal links point at the translation when one is published, else at the English page.
 */

defined( 'ABSPATH' ) || exit;

/** "Translated, not yet reviewed" note per language; {en} is the link to the English original. */
function er_guide_tr_note( string $lang, string $en_url, bool $reviewed ): string {
	$pending = [
		'ar' => 'هذا الدليل مترجم عن الأصل الإنجليزي ولم يراجعه بعدُ متحدث أصلي باللغة، والمرجع هو {en}.',
		'de' => 'Dieser Ratgeber ist eine Übersetzung des englischen Originals und wurde noch nicht von Muttersprachlern geprüft. Maßgeblich ist die {en}.',
		'fr' => 'Ce guide est une traduction de l’original anglais et n’a pas encore été relu par un locuteur natif. La {en} fait foi.',
		'it' => 'Questa guida è una traduzione dell’originale inglese e non è ancora stata rivista da un madrelingua. Fa fede la {en}.',
		'es' => 'Esta guía es una traducción del original en inglés y aún no ha sido revisada por un hablante nativo. La referencia es la {en}.',
		'ru' => 'Этот путеводитель — перевод английского оригинала, ещё не проверенный носителем языка. Основной считается {en}.',
		'zh' => '本指南译自英文原文，尚未经母语人士审校，以{en}为准。',
	];
	$done = [
		'ar' => 'هذا الدليل مترجم عن الأصل الإنجليزي: {en}.',
		'de' => 'Dieser Ratgeber ist eine Übersetzung des englischen Originals: {en}.',
		'fr' => 'Ce guide est une traduction de l’original anglais : {en}.',
		'it' => 'Questa guida è una traduzione dell’originale inglese: {en}.',
		'es' => 'Esta guía es una traducción del original en inglés: {en}.',
		'ru' => 'Этот путеводитель — перевод английского оригинала: {en}.',
		'zh' => '本指南译自英文原文：{en}。',
	];
	$label = [
		'ar' => 'النسخة الإنجليزية',
		'de' => 'englische Fassung',
		'fr' => 'version anglaise',
		'it' => 'versione inglese',
		'es' => 'versión en inglés',
		'ru' => 'английская версия',
		'zh' => '英文版',
	];
	// Owner decision (2026-10-15): once a translation is approved, the page carries no note at all.
	if ( $reviewed ) {
		return '';
	}
	$text = $pending[ $lang ] ?? '';
	if ( '' === $text ) {
		return '';
	}
	$link = '<a href="' . esc_url( $en_url ) . '" hreflang="en">' . esc_html( $label[ $lang ] ?? 'English' ) . '</a>';
	$html = str_replace( '{en}', $link, esc_html( $text ) );
	return '<!-- wp:paragraph {"className":"er-translation-note"} -->' . "\n" . '<p class="er-translation-note">' . $html . "</p>\n<!-- /wp:paragraph -->";
}

/** Point internal links at the published translation, else at the page itself (English). */
function er_guide_tr_links( string $body, string $lang ): string {
	return (string) preg_replace_callback(
		'~<a href="(/[^"#]*)(#[^"]*)?">~',
		static function ( $m ) use ( $lang ) {
			$source = url_to_postid( home_url( $m[1] ) );
			if ( ! $source ) {
				return $m[0];
			}
			$target = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $source, $lang ) : 0;
			$id     = $target && 'publish' === get_post_status( $target ) ? $target : $source;
			$attr   = $id === $source && $target !== $source ? ' hreflang="' . esc_attr( (string) pll_get_post_language( $source ) ) . '"' : '';
			return '<a href="' . esc_attr( wp_make_link_relative( (string) get_permalink( $id ) ) . ( $m[2] ?? '' ) ) . '"' . $attr . '>';
		},
		$body
	);
}

/**
 * @return array{lines: string[], counts: array<string,int>}
 */
/**
 * New guides written after the seed (owner authority to publish guides, 2026-10-15): an English editorial file with
 * `slug` and `title` and no guide carrying its seed id yet is created and published here, with its topic, its
 * relations (`destination`, `related`: seed ids), the indexing flag of the other guides, and the body from
 * data/editorial/en. An existing guide is never touched by this step; its translations follow in the same run.
 */
function er_guide_create_english( string $default, bool $dry, array &$lines, array &$counts ): void {
	$dir   = ER_CORE_DIR . 'data/editorial/en/';
	$index = is_readable( $dir . 'index.json' ) ? ( json_decode( (string) file_get_contents( $dir . 'index.json' ), true ) ?: [] ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
	$by_seed = static function ( string $seed ) use ( $default ): int {
		foreach ( get_posts( [ 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_er_seed_id', 'meta_value' => $seed, 'lang' => '' ] ) as $id ) {
			if ( pll_get_post_language( (int) $id ) === $default ) {
				return (int) $id;
			}
		}
		return 0;
	};
	foreach ( $index as $seed_id => $item ) {
		if ( 'er_guide' !== ( $item['type'] ?? '' ) || empty( $item['slug'] ) || empty( $item['title'] ) || ! is_readable( $dir . $seed_id . '.html' ) ) {
			continue;
		}
		if ( get_posts( [ 'post_type' => 'er_guide', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_er_seed_id', 'meta_value' => (string) $seed_id, 'lang' => '' ] ) ) {
			continue;
		}
		if ( $dry ) {
			++$counts['created'];
			$lines[] = "Would create {$seed_id} ({$default}): \"{$item['title']}\"";
			continue;
		}
		$id = wp_insert_post( wp_slash( [
			'post_type'    => 'er_guide',
			'post_status'  => 'publish',
			'post_title'   => (string) $item['title'],
			'post_name'    => sanitize_title( (string) $item['slug'] ),
			'post_content' => trim( (string) file_get_contents( $dir . $seed_id . '.html' ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions
			'post_excerpt' => (string) ( $item['excerpt'] ?? '' ),
			'post_author'  => get_current_user_id(),
		] ), true );
		if ( is_wp_error( $id ) ) {
			$lines[] = "Could not create {$seed_id} ({$default}): " . $id->get_error_message();
			continue;
		}
		$id = (int) $id;
		pll_set_post_language( $id, $default );
		update_post_meta( $id, '_er_seed_id', (string) $seed_id );
		update_post_meta( $id, '_er_indexable', '1' );
		foreach ( [ '_er_destination' => 'destination', '_er_related' => 'related' ] as $key => $field ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', (string) ( $item[ $field ] ?? '' ) ) ) ) as $seed ) {
				$rel = $by_seed( 'destination' === $field ? 'dest-' . $seed : $seed );
				if ( $rel ) {
					add_post_meta( $id, $key, $rel );
				}
			}
		}
		if ( ! empty( $item['topic'] ) ) {
			$term = get_term_by( 'name', (string) $item['topic'], 'er_guide_topic' );
			if ( $term && ( ! function_exists( 'pll_get_term_language' ) || pll_get_term_language( $term->term_id ) === $default ) ) {
				wp_set_object_terms( $id, (int) $term->term_id, 'er_guide_topic' );
			}
		}
		++$counts['created'];
		$lines[] = "Created {$seed_id} ({$default}, #{$id}): " . get_permalink( $id );
	}
}

function er_guide_translations_sync( bool $dry = true ): array {
	$lines  = [];
	$counts = [ 'created' => 0, 'updated' => 0, 'current' => 0, 'kept' => 0, 'skipped' => 0 ];
	if ( ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return [ 'lines' => [ 'Polylang is not active.' ], 'counts' => $counts ];
	}
	$default = (string) pll_default_language();
	er_guide_create_english( $default, $dry, $lines, $counts );
	foreach ( (array) pll_languages_list() as $lang ) {
		if ( $lang === $default ) {
			continue;
		}
		$dir   = ER_CORE_DIR . 'data/editorial/' . $lang . '/';
		$index = is_readable( $dir . 'index.json' ) ? ( json_decode( (string) file_get_contents( $dir . 'index.json' ), true ) ?: [] ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
		foreach ( $index as $seed_id => $item ) {
			if ( 'er_guide' !== ( $item['type'] ?? '' ) || empty( $item['title'] ) || ! is_readable( $dir . $seed_id . '.html' ) ) {
				continue;
			}
			$ids = get_posts( [ 'post_type' => 'er_guide', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_er_seed_id', 'meta_value' => (string) $seed_id, 'lang' => '' ] );
			$en  = 0;
			foreach ( $ids as $id ) {
				if ( pll_get_post_language( (int) $id ) === $default ) {
					$en = (int) $id;
				}
			}
			if ( ! $en || 'publish' !== get_post_status( $en ) ) {
				++$counts['skipped'];
				$lines[] = "Skipped {$seed_id} ({$lang}): the English guide is not published.";
				continue;
			}
			if ( empty( $item['current'] ) ) {
				++$counts['skipped'];
				$lines[] = "Skipped {$seed_id} ({$lang}): the English changed since it was translated.";
				continue;
			}
			$reviewed = 'approved' === ( $item['review'] ?? '' );
			$note     = er_guide_tr_note( $lang, (string) get_permalink( $en ), $reviewed );
			$body     = ( '' !== $note ? $note . "\n\n" : '' ) . er_guide_tr_links( trim( (string) file_get_contents( $dir . $seed_id . '.html' ) ), $lang ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$title    = (string) $item['title'];
			$excerpt  = (string) ( $item['excerpt'] ?? '' );
			$tr       = (int) pll_get_post( $en, $lang );
			if ( $tr && pll_get_post_language( $tr ) === $lang ) {
				$content = trim( (string) get_post_field( 'post_content', $tr ) );
				$hash    = (string) get_post_meta( $tr, '_er_guide_tr_hash', true );
				if ( $content === trim( $body ) && get_the_title( $tr ) === $title ) {
					++$counts['current'];
				} elseif ( $hash && sha1( $content ) === $hash ) {
					if ( ! $dry ) {
						wp_update_post( wp_slash( [ 'ID' => $tr, 'post_title' => $title, 'post_content' => $body, 'post_excerpt' => $excerpt ] ) );
						update_post_meta( $tr, '_er_guide_tr_hash', sha1( trim( (string) get_post_field( 'post_content', $tr ) ) ) );
					}
					++$counts['updated'];
					$lines[] = ( $dry ? 'Would update' : 'Updated' ) . " {$seed_id} ({$lang}, #{$tr}).";
				} else {
					++$counts['kept'];
					$lines[] = "Kept {$seed_id} ({$lang}, #{$tr}): edited in WordPress or not created by this sync.";
				}
				continue;
			}
			if ( $dry ) {
				++$counts['created'];
				$lines[] = "Would create {$seed_id} ({$lang}): \"{$title}\"";
				continue;
			}
			$id = wp_insert_post( wp_slash( [
				'post_type'    => 'er_guide',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => $body,
				'post_excerpt' => $excerpt,
				'post_author'  => (int) get_post_field( 'post_author', $en ),
			] ), true );
			if ( is_wp_error( $id ) ) {
				$lines[] = "Could not create {$seed_id} ({$lang}): " . $id->get_error_message();
				continue;
			}
			$id = (int) $id;
			pll_set_post_language( $id, $lang );
			$group             = (array) pll_get_post_translations( $en );
			$group[ $default ] = $en;
			$group[ $lang ]    = $id;
			pll_save_post_translations( $group );
			// Same identity, photo and indexing state as the English guide; relations are read from the English.
			update_post_meta( $id, '_er_seed_id', (string) $seed_id );
			$thumb = (int) get_post_thumbnail_id( $en );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
			if ( get_post_meta( $en, '_er_indexable', true ) ) {
				update_post_meta( $id, '_er_indexable', '1' );
			}
			update_post_meta( $id, '_er_guide_tr_hash', sha1( trim( (string) get_post_field( 'post_content', $id ) ) ) );
			++$counts['created'];
			$lines[] = "Created {$seed_id} ({$lang}, #{$id}): " . get_permalink( $id );
		}
	}
	return [ 'lines' => $lines, 'counts' => $counts ];
}

/* -------------------------------------------------------------------------- */
/* wp-admin: Egypt Roamer → Guide translations                                 */
/* -------------------------------------------------------------------------- */

add_action( 'admin_menu', static function () {
	add_submenu_page( 'egypt-roamer', __( 'Guide translations', 'egypt-roamer-core' ), __( 'Guide translations', 'egypt-roamer-core' ), 'manage_options', 'er-guide-translations', 'er_render_guide_translations_page' );
}, 21 );

function er_render_guide_translations_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$key     = 'er_guide_tr_report_' . get_current_user_id();
	$report  = get_transient( $key );
	$preview = er_guide_translations_sync( true );
	echo '<div class="wrap"><h1>' . esc_html__( 'Guide translations', 'egypt-roamer-core' ) . '</h1>';
	echo '<p>' . esc_html__( 'Publishes the translated guides shipped with Egypt Roamer Core and links them in Polylang. Translations not yet approved by a native speaker open with a note saying so, linking the English original. Translations edited in WordPress are never overwritten.', 'egypt-roamer-core' ) . '</p>';
	if ( $report ) {
		delete_transient( $key );
		echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Done.', 'egypt-roamer-core' ) . '</strong></p><pre>' . esc_html( implode( "\n", (array) $report ) ) . '</pre></div>';
	}
	echo '<h2>' . esc_html__( 'Preview', 'egypt-roamer-core' ) . '</h2><pre>' . esc_html( implode( "\n", $preview['lines'] ) ?: __( 'Everything is up to date.', 'egypt-roamer-core' ) ) . '</pre>';
	printf( '<p>%s</p>', esc_html( wp_json_encode( $preview['counts'] ) ) );
	if ( $preview['counts']['created'] || $preview['counts']['updated'] ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'er_guide_tr_sync' );
		echo '<input type="hidden" name="action" value="er_guide_tr_sync" />';
		submit_button( __( 'Publish / update guide translations', 'egypt-roamer-core' ) );
		echo '</form>';
	}
	echo '</div>';
}

add_action( 'admin_post_er_guide_tr_sync', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'egypt-roamer-core' ), 403 );
	}
	check_admin_referer( 'er_guide_tr_sync' );
	$result = er_guide_translations_sync( false );
	set_transient( 'er_guide_tr_report_' . get_current_user_id(), array_merge( $result['lines'], [ wp_json_encode( $result['counts'] ) ] ), 300 );
	wp_safe_redirect( admin_url( 'admin.php?page=er-guide-translations' ) );
	exit;
} );
