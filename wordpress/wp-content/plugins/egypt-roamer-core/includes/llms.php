<?php
/**
 * /llms.txt: a plain-text map of the site for AI assistants (llmstxt.org format), built from the
 * published, indexable English pages. Nothing is written by hand except the site summary; every
 * link and description comes from the pages themselves, so new pages are listed as soon as they go live.
 */

defined( 'ABSPATH' ) || exit;

const ER_LLMS_CACHE = 'er_llms_txt';

function er_llms_section( string $type, string $lang ): string {
	$ids = get_posts( [
		'post_type'      => $type,
		'post_status'    => 'publish',
		'has_password'   => false,
		'posts_per_page' => 500,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'ids',
		'lang'           => $lang,
	] );
	$lines = [];
	foreach ( $ids as $id ) {
		if ( ! er_is_indexable( (int) $id ) ) {
			continue;
		}
		$title = trim( wp_strip_all_tags( html_entity_decode( get_the_title( (int) $id ), ENT_QUOTES, 'UTF-8' ) ) );
		$desc  = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( html_entity_decode( get_the_excerpt( (int) $id ), ENT_QUOTES, 'UTF-8' ) ) ) );
		$desc  = wp_html_excerpt( $desc, 220, '…' );
		$lines[] = '- [' . str_replace( [ '[', ']' ], '', $title ) . '](' . get_permalink( (int) $id ) . ')' . ( '' !== $desc ? ': ' . $desc : '' );
	}
	return implode( "\n", $lines );
}

function er_llms_txt(): string {
	$cached = get_transient( ER_LLMS_CACHE );
	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}
	$lang = function_exists( 'pll_default_language' ) ? (string) pll_default_language() : '';
	$out  = "# Egypt Roamer\n\n";
	$out .= "> An independent travel guide to Egypt: places, trips and planning guides. Egypt Roamer takes no bookings and no payments; trip and hotel links go to partner sites (Viator, GetYourGuide, Klook, Tiqets and Trip.com), where travellers book and pay.\n\n";
	$out .= "Place pages are based on the official pages of Egypt's Ministry of Tourism and Antiquities and the Egyptian Environmental Affairs Agency, then UNESCO. The site is published in English, Arabic, German, French, Italian, Spanish, Russian and Chinese; the links below are the English pages, and each page links to its translations.\n\n";
	$sections = [
		'er_guide'       => 'Guides',
		'er_destination' => 'Destinations',
		'er_experience'  => 'Trips and tours',
	];
	foreach ( $sections as $type => $label ) {
		$list = er_llms_section( $type, $lang );
		if ( '' !== $list ) {
			$out .= '## ' . $label . "\n\n" . $list . "\n\n";
		}
	}
	$out .= "## Optional\n\n- [Sitemap](" . home_url( '/wp-sitemap.xml' ) . "): every page in every language\n";
	set_transient( ER_LLMS_CACHE, $out, 12 * HOUR_IN_SECONDS );
	return $out;
}

add_action( 'save_post', static function () {
	delete_transient( ER_LLMS_CACHE );
} );

add_action( 'parse_request', static function () {
	$path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( '/llms.txt' !== $path || '1' !== (string) get_option( 'blog_public' ) ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex', true );
	echo er_llms_txt(); // phpcs:ignore WordPress.Security.EscapeOutput -- plain text built from titles and excerpts
	exit;
}, 0 );
