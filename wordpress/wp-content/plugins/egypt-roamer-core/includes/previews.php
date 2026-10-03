<?php
/**
 * Experience previews: a short photo story ("moment by moment") and, later, a video clip, shown on an
 * experience page after its introduction. Prototype: one experience (data/previews.json).
 *
 * The media is language-neutral (one photo or clip for all eight languages); titles, captions and alt
 * text are per language. Photos are hot-linked stand-ins like the rest of the seed photography, each with
 * its source and location tag recorded in the data file. Media marked `generated` is an illustration:
 * the theme labels it as such and it is never presented as footage of the place.
 *
 * No database tables or post meta: the entries are versioned with Core and keyed by seed id.
 *
 * @package EgyptRoamerCore
 */

defined( 'ABSPATH' ) || exit;

/** Every preview entry, by experience seed id. */
function er_previews(): array {
	static $all = null;
	if ( null === $all ) {
		$file = ER_CORE_DIR . 'data/previews.json';
		$all  = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$all  = is_array( $all ) ? array_filter( $all, 'is_array' ) : [];
	}
	return $all;
}

/**
 * The preview of a post in its own language, or [] when it has none.
 *
 *     [ 'title', 'intro', 'lang' (of the text), 'video' => [...]|null,
 *       'moments' => [ [ 'photo', 'source', 'photographer', 'generated', 'title', 'caption', 'alt' ], … ] ]
 *
 * Text falls back to English when a language is missing (and 'lang' says so).
 */
function er_preview_for( int $post_id ): array {
	$seed_id = (string) get_post_meta( $post_id, '_er_seed_id', true );
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		$source  = (int) pll_get_post( $post_id, (string) pll_default_language() );
		$seed_id = $source ? (string) get_post_meta( $source, '_er_seed_id', true ) : $seed_id;
	}
	$entry = er_previews()[ preg_replace( '/-(de|fr|it|es|ru|zh|ar)$/', '', $seed_id ) ] ?? null;
	if ( ! $entry || ( empty( $entry['moments'] ) && empty( $entry['video'] ) ) ) {
		return [];
	}
	$lang = function_exists( 'er_post_lang' ) ? er_post_lang( $post_id ) : 'en';
	$lang = isset( $entry['title'][ $lang ] ) ? $lang : 'en';
	$text = static fn ( $v ): string => is_array( $v ) ? (string) ( $v[ $lang ] ?? $v['en'] ?? '' ) : (string) $v;

	$moments = [];
	foreach ( (array) ( $entry['moments'] ?? [] ) as $m ) {
		if ( empty( $m['photo'] ) ) {
			continue;
		}
		$moments[] = [
			'photo'        => (string) $m['photo'],
			'source'       => (string) ( $m['source'] ?? '' ),
			'photographer' => (string) ( $m['photographer'] ?? '' ),
			'generated'    => ! empty( $m['generated'] ),
			'title'        => $text( $m['title'] ?? '' ),
			'caption'      => $text( $m['caption'] ?? '' ),
			'alt'          => $text( $m['alt'] ?? '' ),
		];
	}
	$video = null;
	if ( ! empty( $entry['video'] ) && is_array( $entry['video'] ) && ( ! empty( $entry['video']['mp4'] ) || ! empty( $entry['video']['webm'] ) ) ) {
		$v     = $entry['video'];
		$video = [
			'mp4'       => (string) ( $v['mp4'] ?? '' ),
			'webm'      => (string) ( $v['webm'] ?? '' ),
			'poster'    => (string) ( $v['poster'] ?? ( $moments[0]['photo'] ?? '' ) ),
			'duration'  => (int) ( $v['duration'] ?? 0 ),
			'captions'  => (string) ( $v['captions'][ $lang ] ?? '' ),
			'generated' => ! empty( $v['generated'] ),
		];
	}
	return (array) apply_filters( 'er_preview', [
		'title'   => $text( $entry['title'] ?? '' ),
		'intro'   => $text( $entry['intro'] ?? '' ),
		'lang'    => $lang,
		'video'   => $video,
		'moments' => $moments,
	], $post_id );
}
