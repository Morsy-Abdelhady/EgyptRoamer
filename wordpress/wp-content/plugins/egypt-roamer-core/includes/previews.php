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
		'story'   => er_preview_story( (array) ( $entry['story'] ?? [] ), $post_lang, $moments ),
	], $post_id );
}

/**
 * The "Experience story" of an entry: how the experience page is told as one narrative. The story only
 * arranges approved material; it adds no facts of its own:
 *
 *     [ 'full' (bool), 'label', 'title',
 *       'sections' => [ 'opening' => body anchor, 'fit' => …, 'plan' => …, 'combine' => …, 'faq' => …, 'absorbed' => [ anchors ] ],
 *       'labels'   => [ key => structural label in this language ], 'feel' => [ 'photo', 'alt', 'photographer', 'source', 'generated' ],
 *       'stages'   => [ [ 'layout', 'tone', 'from', 'eyebrow', 'title', 'text', 'photo', 'alt', 'photographer', 'source', 'generated' ], … ] ]
 *
 * - `sections` names, by heading anchor, where each section of the page's own (approved, translated) body goes. The body
 *   sections listed in `absorbed` are told by the journey instead, through each stage's `from` ("why#1": the first item of
 *   the "why" section; "sun-festival": that whole section), so every sentence has exactly one home in every language.
 * - `layout` sets each stage's weight in the sequence: text (a dark chapter break), reveal (the full-bleed moment), split
 *   (photo held beside the text), peak (the highlight), band (photo edge to edge beside its text), detail (a smaller beat).
 * - The story's own words (label, title, eyebrows, stage titles) are never borrowed from another language. A language that
 *   has them ('full') gets them; one that does not gets the moments' approved titles and the body text, and text-only stages
 *   without body text are left out. A page never mixes two languages.
 *
 * [] when the entry has no story (the theme then keeps the photo strip).
 */
function er_preview_story( array $story, string $lang, array $moments ): array {
	if ( ! $story || empty( $story['stages'] ) ) {
		return [];
	}
	$own  = static fn ( $v ): string => is_array( $v ) ? trim( (string) ( $v[ $lang ] ?? '' ) ) : '';
	$full = '' !== $own( $story['title'] ?? '' );

	$stages = [];
	foreach ( (array) $story['stages'] as $s ) {
		$m    = isset( $s['moment'] ) ? ( $moments[ (int) $s['moment'] ] ?? null ) : null;
		$from = sanitize_text_field( (string) ( $s['from'] ?? '' ) );
		if ( ! $m && ! $full && '' === $from ) {
			continue; // A text-only stage needs words of its own or of the body.
		}
		$stages[] = [
			'layout'       => sanitize_key( (string) ( $s['layout'] ?? ( $m ? 'split' : 'text' ) ) ),
			'tone'         => sanitize_key( (string) ( $s['tone'] ?? '' ) ),
			'from'         => $from,
			'eyebrow'      => $full ? $own( $s['eyebrow'] ?? '' ) : '',
			'title'        => $full && '' !== $own( $s['title'] ?? '' ) ? $own( $s['title'] ) : (string) ( $m['title'] ?? '' ),
			'text'         => $full ? $own( $s['text'] ?? '' ) : '',
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
	$sections = array_map( static fn ( $v ) => is_array( $v ) ? array_map( 'sanitize_title', $v ) : sanitize_title( (string) $v ), (array) ( $story['sections'] ?? [] ) );
	// The image beside "what it feels like": one of the entry's moments (photos are the same in every language).
	$fm   = isset( $story['feel']['moment'] ) ? ( $moments[ (int) $story['feel']['moment'] ] ?? null ) : null;
	$feel = $fm ? [ 'photo' => $fm['photo'], 'alt' => $fm['alt'], 'photographer' => $fm['photographer'], 'source' => $fm['source'], 'generated' => $fm['generated'] ] : [];
	// Structural labels (eyebrows and headings the page adds around the approved text), in the story's own languages only.
	$labels = [];
	foreach ( (array) ( $story['labels'] ?? [] ) as $key => $value ) {
		$labels[ sanitize_key( (string) $key ) ] = $own( $value );
	}
	// Facts the hero adds to location and duration, condensed from the approved text (the story's languages only).
	$hero_meta = [];
	foreach ( (array) ( $story['hero_meta'] ?? [] ) as $hm ) {
		if ( '' !== $own( $hm['label'] ?? '' ) && '' !== $own( $hm['text'] ?? '' ) ) {
			$hero_meta[] = [ 'icon' => sanitize_key( (string) ( $hm['icon'] ?? '' ) ), 'label' => $own( $hm['label'] ), 'text' => $own( $hm['text'] ) ];
		}
	}
	// Which sentences of the opening section open the page and which introduce the journey (1-based).
	$split = [];
	foreach ( (array) ( $story['split'] ?? [] ) as $key => $nums ) {
		$split[ sanitize_key( (string) $key ) ] = array_map( 'intval', (array) $nums );
	}
	return [
		'full'      => $full,
		'label'     => $full ? $own( $story['label'] ?? '' ) : '',
		'title'     => $full ? $own( $story['title'] ) : '',
		'labels'    => array_filter( $labels ),
		'hero_meta' => $hero_meta,
		'split'     => $split,
		'sections' => $sections,
		'feel'     => $feel,
		'stages'   => $stages,
	];
}
