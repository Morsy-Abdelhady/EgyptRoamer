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
	$post_lang = function_exists( 'er_post_lang' ) ? er_post_lang( $post_id ) : 'en';
	return (array) apply_filters( 'er_preview', [
		'title'   => $text( $entry['title'] ?? '' ),
		'intro'   => $text( $entry['intro'] ?? '' ),
		'lang'    => $lang,
		'video'   => $video,
		'moments' => $moments,
		'story'   => er_preview_story( (array) ( $entry['story'] ?? [] ), $post_lang, $moments, $text( $entry['title'] ?? '' ), $text( $entry['intro'] ?? '' ) ),
	], $post_id );
}

/**
 * The "Experience Immersion" story of an entry: the experience as a journey (before → arrival → inside →
 * highlight → after), what it feels like, and what to know, built on the entry's moments.
 *
 *     [ 'full' (bool), 'label', 'title', 'lede',
 *       'stages' => [ [ 'eyebrow', 'title', 'text', 'tone', 'photo', 'alt', 'photographer', 'source', 'generated' ], … ],
 *       'feel'   => [ 'title', 'note', 'items' => [ [ 'label', 'text' ], … ] ],
 *       'know'   => [ 'title', 'more', 'items' => [ [ 'label', 'text' ], … ] ] ]
 *
 * The story's own text is never borrowed from another language. A language that has it ('full') gets the
 * whole journey; one that does not gets the same sequence of photographs with their translated moment
 * titles and captions (no text-only stages, no feel/know), so a page never mixes two languages and no
 * translation is invented. [] when the entry has no story (the theme then keeps the photo strip).
 */
function er_preview_story( array $story, string $lang, array $moments, string $title, string $intro ): array {
	if ( ! $story || empty( $story['stages'] ) ) {
		return [];
	}
	$own  = static fn ( $v ): string => is_array( $v ) ? trim( (string) ( $v[ $lang ] ?? '' ) ) : '';
	$full = '' !== $own( $story['title'] ?? '' ) && '' !== $own( $story['lede'] ?? '' );

	$stages = [];
	foreach ( (array) $story['stages'] as $s ) {
		$m = isset( $s['moment'] ) ? ( $moments[ (int) $s['moment'] ] ?? null ) : null;
		if ( ! $m && ! $full ) {
			continue; // A text-only stage exists only in the story's own languages.
		}
		$stages[] = [
			'eyebrow'      => $full ? $own( $s['eyebrow'] ?? '' ) : '',
			'title'        => $full && '' !== $own( $s['title'] ?? '' ) ? $own( $s['title'] ) : (string) ( $m['title'] ?? '' ),
			'text'         => $full && '' !== $own( $s['text'] ?? '' ) ? $own( $s['text'] ) : (string) ( $m['caption'] ?? '' ),
			'tone'         => $m ? '' : sanitize_key( (string) ( $s['tone'] ?? '' ) ),
			'photo'        => (string) ( $m['photo'] ?? '' ),
			'alt'          => (string) ( $m['alt'] ?? '' ),
			'photographer' => (string) ( $m['photographer'] ?? '' ),
			'source'       => (string) ( $m['source'] ?? '' ),
			'generated'    => ! empty( $m['generated'] ),
		];
	}
	if ( ! array_filter( array_column( $stages, 'photo' ) ) ) {
		return [];
	}
	$list = static function ( $block ) use ( $own ): array {
		$items = [];
		foreach ( (array) ( $block['items'] ?? [] ) as $i ) {
			if ( '' !== $own( $i['label'] ?? '' ) && '' !== $own( $i['text'] ?? '' ) ) {
				$items[] = [ 'label' => $own( $i['label'] ), 'text' => $own( $i['text'] ) ];
			}
		}
		return $items ? [ 'title' => $own( $block['title'] ?? '' ), 'note' => $own( $block['note'] ?? '' ), 'more' => $own( $block['more'] ?? '' ), 'items' => $items ] : [];
	};
	return [
		'full'   => $full,
		'label'  => $full ? $own( $story['label'] ?? '' ) : '',
		'title'  => $full ? $own( $story['title'] ) : $title,
		'lede'   => $full ? $own( $story['lede'] ) : $intro,
		'stages' => $stages,
		'feel'   => $full ? $list( $story['feel'] ?? [] ) : [],
		'know'   => $full ? $list( $story['know'] ?? [] ) : [],
	];
}
