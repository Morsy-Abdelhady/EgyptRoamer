<?php
/**
 * Every public URL of the site, in every language, as JSON for site-crawl.mjs:
 * published entries of every public post type, every archive in every language, every public term,
 * plus search (results / no results) and a 404 per language.
 *
 *   wp eval-file tools/qa/site-inventory.php > urls.json
 */

$out   = [];
$langs = function_exists( 'pll_languages_list' ) ? pll_languages_list() : [ 'en' ];
$def   = function_exists( 'pll_default_language' ) ? pll_default_language() : 'en';
$rel   = static fn( $u ) => wp_make_link_relative( (string) $u );

foreach ( get_post_types( [ 'public' => true ] ) as $pt ) {
	if ( 'attachment' === $pt ) {
		continue;
	}
	foreach ( get_posts( [ 'post_type' => $pt, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '' ] ) as $id ) {
		$out[] = [ 'type' => $pt, 'id' => $id, 'lang' => function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : $def, 'url' => $rel( get_permalink( $id ) ) ];
	}
}
foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $type ) {
	if ( ! is_string( $type->has_archive ) ) {
		continue;
	}
	foreach ( $langs as $l ) {
		// WP-CLI has no current language, so build the directory URL (/de/tours/) explicitly.
		$out[] = [ 'type' => 'archive:' . $type->name, 'id' => 0, 'lang' => $l, 'url' => ( $l === $def ? '/' : "/$l/" ) . $type->has_archive . '/' ];
	}
}
foreach ( get_taxonomies( [ 'public' => true ] ) as $tx ) {
	$terms = get_terms( [ 'taxonomy' => $tx, 'hide_empty' => false, 'lang' => '' ] );
	foreach ( is_wp_error( $terms ) ? [] : $terms as $t ) {
		$out[] = [ 'type' => 'tax:' . $tx, 'id' => $t->term_id, 'lang' => function_exists( 'pll_get_term_language' ) ? (string) pll_get_term_language( $t->term_id ) : $def, 'url' => $rel( get_term_link( $t ) ) ];
	}
}
foreach ( $langs as $l ) {
	$p     = $l === $def ? '' : "/$l";
	$out[] = [ 'type' => 'search', 'id' => 0, 'lang' => $l, 'url' => "$p/?s=cairo" ];
	$out[] = [ 'type' => 'search-empty', 'id' => 0, 'lang' => $l, 'url' => "$p/?s=zzqxv" ];
	$out[] = [ 'type' => '404', 'id' => 0, 'lang' => $l, 'url' => "$p/no-such-page-xyz/" ];
}
echo wp_json_encode( $out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
