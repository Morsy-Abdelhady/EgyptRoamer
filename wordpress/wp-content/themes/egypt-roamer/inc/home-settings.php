<?php
/**
 * Appearance → Homepage: every section of the homepage is editable without code.
 *
 * Stored per language (option er_home_{lang}). Text left empty falls back to the
 * translated default copy of the approved design; images, featured items and
 * links fall back to the default language's choices. Cards are never hardcoded:
 * featured lists come from Destinations, Tours/Experiences/Activities, Guides
 * and live Affiliate Offers.
 */

defined( 'ABSPATH' ) || exit;

/** Unsplash photo ids used by the approved design (stand-ins until Media Library images are set). */
function er_home_stock(): array {
	return [
		'hero'     => '1734461255961-6288a28a65f0',
		'hero_b'   => '1678038592492-d73c063bb9e2',
		'scene2'   => '1684100096410-fd39cdff91a3',
		'scene3'   => '1771839534998-d5892d756064',
		'scene4'   => '1682687982049-b3d433368cd1',
		'planner'  => '1761205930594-64096d9d2baa',
		'film'     => [ '1771325676184-44d8035e3cd1', '1761205930594-64096d9d2baa', '1762530162773-c99f38d4d5d3', '1655815226495-68d7d78894c4', '1682686581295-7364cabf5511' ],
		'partners' => [ 'hotels' => '1760261598144-dddd7e1a3ef9', 'tours' => '1559527012-3b0fca356de0', 'cruises' => '1774223146816-8472905a40b8', 'transfers' => '1774425329088-36801b6f09be', 'cars' => '1654676428515-94b5bf7a1ac4' ],
	];
}

/** Field schema (Egypt Roamer Core field engine). Defaults are the approved copy, translated. */
function er_home_fields(): array {
	$em = static fn ( $a, $b ) => esc_html( er_t( $a ) ) . '<br /><em class="serif">' . esc_html( er_t( $b ) ) . '</em>';
	$commercial = [ 'er_tour', 'er_experience', 'er_activity' ];
	$f = [
		'h_hero'          => [ 'type' => 'heading', 'label' => __( 'Hero', 'egypt-roamer' ) ],
		'hero_eyebrow'    => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( "A country you don't just visit" ) ],
		'hero_title'      => [ 'type' => 'text', 'label' => __( 'Headline', 'egypt-roamer' ), 'default' => er_t( 'Egypt' ) ],
		'hero_sub'        => [ 'type' => 'html', 'label' => __( 'Headline, second line (<em> allowed)', 'egypt-roamer' ), 'default' => er_t( 'You <em>Feel</em> It' ) ],
		'hero_copy'       => [ 'type' => 'text', 'label' => __( 'Subtitle', 'egypt-roamer' ), 'default' => er_t( 'Ancient wonders. Endless adventures. Unforgettable moments.' ) ],
		'hero_copy_more'  => [ 'type' => 'text', 'label' => __( 'Subtitle, second line', 'egypt-roamer' ), 'default' => er_t( 'Discover, compare and book the best experiences in Egypt — all in one place.' ) ],
		'hero_cta'        => [ 'type' => 'text', 'label' => __( 'Main button label', 'egypt-roamer' ), 'default' => er_t( 'Explore Egypt' ) ],
		'hero_note'       => [ 'type' => 'lines', 'label' => __( 'Side note (one word per line)', 'egypt-roamer' ), 'default' => implode( "\n", [ er_t( 'Different.' ), er_t( 'Timeless.' ), er_t( 'Unforgettable.' ) ] ) ],
		'hero_image'      => [ 'type' => 'image', 'label' => __( 'Hero image (also scene 1)', 'egypt-roamer' ) ],
		'hero_image_b'    => [ 'type' => 'image', 'label' => __( 'Hero second layer image', 'egypt-roamer' ) ],
		'film_enabled'    => [ 'type' => 'checkbox', 'label' => __( 'Show “Watch the Film” (photo sequence)', 'egypt-roamer' ), 'default' => 1 ],

		'h_finder'        => [ 'type' => 'heading', 'label' => __( 'Hero finder (compare with partners)', 'egypt-roamer' ) ],
		'finder_hotels'   => [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( 'Hotels tab → offer (with search template)', 'egypt-roamer' ) ],
		'finder_tours'    => [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( 'Tours & Activities tab → offer', 'egypt-roamer' ) ],
		'finder_cruises'  => [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( 'Nile Cruises tab → offer', 'egypt-roamer' ) ],
		'finder_transfers' => [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( 'Transfers tab → offer', 'egypt-roamer' ) ],
		'finder_cars'     => [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( 'Car Rental tab → offer', 'egypt-roamer' ), 'help' => __( 'Tabs without a live offer are hidden; with none, the finder is hidden.', 'egypt-roamer' ) ],
		'finder_note'     => [ 'type' => 'text', 'label' => __( 'Finder note', 'egypt-roamer' ), 'default' => er_t( 'You book directly with our partners' ) ],
	];

	$scenes = [
		1 => [ 'Timeless wonders', 'Pyramids', 'Where history still breathes', 'Explore Cairo', '29.9792° N · 31.1342° E — Giza Plateau' ],
		2 => [ 'A river of life', 'Nile', 'More than a river. A story that connects a civilization.', 'Explore Nile Cruises', '24.0889° N · 32.8998° E — Aswan' ],
		3 => [ 'Beyond the ordinary', 'Desert', 'Endless landscapes. Unforgettable adventures.', 'Explore Desert Experiences', '27.0957° N · 27.9880° E — Western Desert' ],
		4 => [ 'A different world', 'Red Sea', 'Dive into crystal-clear waters', 'Explore Red Sea', '27.7333° N · 34.2500° E — Ras Mohammed' ],
	];
	foreach ( $scenes as $n => [ $kicker, $title, $line, $cta, $coords ] ) {
		/* translators: %d: scene number */
		$f[ "h_scene{$n}" ]       = [ 'type' => 'heading', 'label' => sprintf( __( 'Journey scene %d', 'egypt-roamer' ), $n ) ];
		$f[ "scene{$n}_kicker" ]  = [ 'type' => 'text', 'label' => __( 'Kicker', 'egypt-roamer' ), 'default' => er_t( $kicker ) ];
		$f[ "scene{$n}_title" ]   = [ 'type' => 'text', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => er_t( $title ) ];
		$f[ "scene{$n}_line" ]    = [ 'type' => 'text', 'label' => __( 'Line', 'egypt-roamer' ), 'default' => er_t( $line ) ];
		$f[ "scene{$n}_cta" ]     = [ 'type' => 'text', 'label' => __( 'Button label', 'egypt-roamer' ), 'default' => er_t( $cta ) ];
		$f[ "scene{$n}_link" ]    = [ 'type' => 'post', 'post_type' => [ 'er_destination', 'er_tour', 'er_experience', 'er_activity', 'er_guide', 'page' ], 'label' => __( 'Button links to', 'egypt-roamer' ) ];
		$f[ "scene{$n}_coords" ]  = [ 'type' => 'text', 'label' => __( 'Coordinates caption', 'egypt-roamer' ), 'default' => er_t( $coords ) ];
		$f[ "scene{$n}_pick" ]    = [ 'type' => 'post', 'post_type' => 'er_offer', 'label' => __( '“Roamer pick” offer (hidden unless live)', 'egypt-roamer' ) ];
		if ( 1 !== $n ) {
			$f[ "scene{$n}_image" ] = [ 'type' => 'image', 'label' => __( 'Image', 'egypt-roamer' ) ];
		}
	}

	$f += [
		'h_interlude'      => [ 'type' => 'heading', 'label' => __( 'Brand statement', 'egypt-roamer' ) ],
		'interlude_eyebrow' => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'More than a destination' ) ],
		'interlude_quote'  => [ 'type' => 'html', 'label' => __( 'Statement (<em>, <br> allowed)', 'egypt-roamer' ), 'default' => er_t( "Egypt isn't a checklist.<br />It's a story you <em>step&nbsp;into</em>." ) ],
		'interlude_facts'  => [ 'type' => 'lines', 'label' => __( 'Facts (number | text, one per line — verifiable facts only)', 'egypt-roamer' ), 'default' => implode( "\n", [ '5,000 | ' . er_t( 'years of stories, still being told' ), '7 | ' . er_t( 'UNESCO World Heritage Sites' ), '1,200+ | ' . er_t( 'species of fish on Red Sea reefs' ), '1 | ' . er_t( 'place to discover, compare & book it all' ) ] ) ],

		'h_moods'          => [ 'type' => 'heading', 'label' => __( 'Travel styles (“What kind of Egypt”)', 'egypt-roamer' ) ],
		'moods_enabled'    => [ 'type' => 'checkbox', 'label' => __( 'Show this section', 'egypt-roamer' ), 'default' => 1 ],
		'moods_eyebrow'    => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'Find your Egypt' ) ],
		'moods_title'      => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => esc_html( er_t( 'What kind of Egypt' ) ) . '<br />' . esc_html( er_t( 'are you looking for?' ) ), 'help' => __( 'Styles come from Tours → Travel styles (word, icon, image, destination). “What to do” / “Where to stay” use live offers tagged with the style.', 'egypt-roamer' ) ],

		'h_dest'           => [ 'type' => 'heading', 'label' => __( 'Destinations', 'egypt-roamer' ) ],
		'dest_eyebrow'     => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'Destinations' ) ],
		'dest_title'       => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => $em( 'Seven places.', 'Seven different Egypts.' ) ],
		'dest_aside'       => [ 'type' => 'textarea', 'label' => __( 'Intro', 'egypt-roamer' ), 'default' => er_t( "From Cairo's thousand minarets to the silence of Siwa, every region tells its own chapter. Hover a place to step inside it." ) ],
		'dest_featured'    => [ 'type' => 'posts', 'post_type' => 'er_destination', 'label' => __( 'Featured destinations (in order; empty = all published)', 'egypt-roamer' ) ],

		'h_map'            => [ 'type' => 'heading', 'label' => __( 'Map', 'egypt-roamer' ) ],
		'map_enabled'      => [ 'type' => 'checkbox', 'label' => __( 'Show this section', 'egypt-roamer' ), 'default' => 1 ],
		'map_eyebrow'      => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'The map' ) ],
		'map_title'        => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => esc_html( er_t( 'Egypt,' ) ) . ' <em class="serif">' . esc_html( er_t( 'point by point.' ) ) . '</em>' ],
		'map_intro'        => [ 'type' => 'textarea', 'label' => __( 'Intro', 'egypt-roamer' ), 'default' => er_t( 'The Nile draws a single line of life through the desert. Everything else — the coasts, the oases, the ancient cities — is a short flight or a scenic drive away.' ) ],

		'h_partners'       => [ 'type' => 'heading', 'label' => __( 'Partner offers', 'egypt-roamer' ) ],
		'partners_eyebrow' => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'Plan with trusted partners' ) ],
		'partners_title'   => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => $em( 'Everything you need,', 'compared in one place.' ) ],
		'partners_aside'   => [ 'type' => 'textarea', 'label' => __( 'Intro', 'egypt-roamer' ), 'default' => er_t( 'We shortlist offers from booking partners, then send you straight to them to book.' ), 'help' => __( 'Tabs = offer categories with live offers (Egypt Roamer → Affiliate Offers). Hidden when none are live.', 'egypt-roamer' ) ],

		'h_exp'            => [ 'type' => 'heading', 'label' => __( 'Experiences rail', 'egypt-roamer' ) ],
		'exp_eyebrow'      => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'Handpicked experiences' ) ],
		'exp_title'        => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => $em( "Moments we'd", 'book again.' ) ],
		'exp_aside'        => [ 'type' => 'textarea', 'label' => __( 'Intro', 'egypt-roamer' ), 'default' => er_t( 'Handpicked by our editors, bookable with our partners.' ) ],
		'exp_featured'     => [ 'type' => 'posts', 'post_type' => $commercial, 'label' => __( 'Featured tours / experiences / activities (in order; empty = latest)', 'egypt-roamer' ) ],

		'h_guide'          => [ 'type' => 'heading', 'label' => __( 'Journal', 'egypt-roamer' ) ],
		'guide_eyebrow'    => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'The Roamer Journal' ) ],
		'guide_title'      => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => esc_html( er_t( 'Stories, tips &' ) ) . '<br /><em class="serif">' . esc_html( er_t( 'honest advice.' ) ) . '</em>' ],
		'guide_featured'   => [ 'type' => 'posts', 'post_type' => [ 'er_guide', 'post' ], 'label' => __( 'Featured guides & articles (in order; empty = latest)', 'egypt-roamer' ) ],

		'h_planner'        => [ 'type' => 'heading', 'label' => __( 'Trip builder', 'egypt-roamer' ) ],
		'planner_enabled'  => [ 'type' => 'checkbox', 'label' => __( 'Show this section', 'egypt-roamer' ), 'default' => 1 ],
		'planner_eyebrow'  => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'Personal trip builder' ) ],
		'planner_title'    => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => $em( 'Ready for your', 'Egypt story?' ) ],
		'planner_intro'    => [ 'type' => 'textarea', 'label' => __( 'Intro', 'egypt-roamer' ), 'default' => er_t( "Four quick choices. We'll draft a route and pace the nights." ) ],
		'planner_link'     => [ 'type' => 'post', 'post_type' => [ 'er_guide', 'page', 'post' ], 'label' => __( '“Build My Trip” links to (e.g. an itinerary guide). Empty = button hidden.', 'egypt-roamer' ) ],
		'planner_image'    => [ 'type' => 'image', 'label' => __( 'Background image', 'egypt-roamer' ) ],
		'budget_smart'     => [ 'type' => 'text', 'label' => __( 'Budget band per night, Smart (e.g. 90-140, USD)', 'egypt-roamer' ), 'help' => __( 'Leave all three empty to hide the estimate. Only use figures you can stand behind.', 'egypt-roamer' ) ],
		'budget_comfort'   => [ 'type' => 'text', 'label' => __( 'Budget band per night, Comfort', 'egypt-roamer' ) ],
		'budget_luxury'    => [ 'type' => 'text', 'label' => __( 'Budget band per night, Luxury', 'egypt-roamer' ) ],

		'h_letter'         => [ 'type' => 'heading', 'label' => __( 'Newsletter (footer)', 'egypt-roamer' ) ],
		'letter_eyebrow'   => [ 'type' => 'text', 'label' => __( 'Eyebrow', 'egypt-roamer' ), 'default' => er_t( 'Roamer letters' ) ],
		'letter_title'     => [ 'type' => 'html', 'label' => __( 'Title', 'egypt-roamer' ), 'default' => $em( 'One beautiful email', 'a month.' ) ],
		'letter_copy'      => [ 'type' => 'textarea', 'label' => __( 'Copy', 'egypt-roamer' ), 'default' => er_t( 'Seasonal routes, new openings and the deals worth knowing about. No spam, ever.' ) ],
	];
	return $f;
}

/** Language slugs to edit (Polylang languages, else the site language). */
function er_home_languages(): array {
	if ( function_exists( 'pll_languages_list' ) ) {
		$langs = pll_languages_list( [ 'fields' => 'slug' ] );
		if ( $langs ) {
			return $langs;
		}
	}
	return [ substr( get_locale(), 0, 2 ) ];
}

function er_home_default_lang(): string {
	return function_exists( 'pll_default_language' ) && pll_default_language() ? (string) pll_default_language() : er_home_languages()[0];
}

/** One homepage value for the current language, with fallbacks (see file header). */
function er_home( string $key ) {
	static $cache = [];
	$lang = er_lang();
	if ( ! isset( $cache[ $lang ] ) ) {
		$cache[ $lang ] = [ get_option( 'er_home_' . $lang, [] ), get_option( 'er_home_' . er_home_default_lang(), [] ) ];
	}
	[ $own, $base ] = $cache[ $lang ];
	$field = er_home_fields()[ $key ] ?? [ 'type' => 'text' ];
	$textual = in_array( $field['type'], [ 'text', 'textarea', 'html', 'lines' ], true );
	if ( is_array( $own ) && array_key_exists( $key, $own ) && '' !== $own[ $key ] && [] !== $own[ $key ] ) {
		return $own[ $key ];
	}
	if ( is_array( $own ) && array_key_exists( $key, $own ) && 'checkbox' === $field['type'] ) {
		return $own[ $key ];
	}
	if ( ! $textual && is_array( $base ) && array_key_exists( $key, $base ) && '' !== $base[ $key ] && [] !== $base[ $key ] ) {
		return $base[ $key ];
	}
	return $field['default'] ?? ( 'posts' === $field['type'] ? [] : '' );
}

add_action( 'admin_menu', static function () {
	add_theme_page( __( 'Homepage', 'egypt-roamer' ), __( 'Homepage', 'egypt-roamer' ), 'edit_theme_options', 'er-homepage', 'er_render_home_settings' );
} );

function er_render_home_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'er_render_field' ) ) {
		echo '<div class="wrap"><p>' . esc_html__( 'Activate the Egypt Roamer Core plugin to edit the homepage.', 'egypt-roamer' ) . '</p></div>';
		return;
	}
	$langs = er_home_languages();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab selection only
	$lang   = isset( $_GET['er_lang'] ) && in_array( $_GET['er_lang'], $langs, true ) ? sanitize_key( $_GET['er_lang'] ) : $langs[0];
	$stored = get_option( 'er_home_' . $lang, [] );
	echo '<div class="wrap er-wrap"><h1>' . esc_html__( 'Homepage', 'egypt-roamer' ) . '</h1>';
	if ( isset( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Homepage saved.', 'egypt-roamer' ) . '</p></div>';
	}
	if ( count( $langs ) > 1 ) {
		echo '<nav class="nav-tab-wrapper">';
		foreach ( $langs as $l ) {
			printf( '<a class="nav-tab%s" href="%s">%s</a>', $l === $lang ? ' nav-tab-active' : '', esc_url( add_query_arg( [ 'page' => 'er-homepage', 'er_lang' => $l ], admin_url( 'themes.php' ) ) ), esc_html( strtoupper( $l ) ) );
		}
		echo '</nav><p class="description">' . esc_html__( 'Empty text uses the translated default copy. Empty images, links and featured lists use the default language’s choices.', 'egypt-roamer' ) . '</p>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="er-fields er-fields--settings">';
	wp_nonce_field( 'er_save_home' );
	echo '<input type="hidden" name="action" value="er_save_home" /><input type="hidden" name="er_lang" value="' . esc_attr( $lang ) . '" />';
	foreach ( er_home_fields() as $key => $field ) {
		if ( 'heading' === $field['type'] ) {
			echo '<h2 class="er-fields__heading">' . esc_html( $field['label'] ) . '</h2>';
			continue;
		}
		$value = is_array( $stored ) && array_key_exists( $key, $stored ) ? $stored[ $key ] : ( 'checkbox' === $field['type'] ? ( $field['default'] ?? 0 ) : '' );
		if ( in_array( $field['type'], [ 'text', 'textarea', 'html', 'lines' ], true ) && isset( $field['default'] ) ) {
			$field['help'] = trim( ( $field['help'] ?? '' ) . ' ' . __( 'Default:', 'egypt-roamer' ) . ' ' . wp_strip_all_tags( str_replace( '<br />', ' / ', (string) $field['default'] ) ) );
		}
		er_render_field( 'er-h-' . $key, 'er_home[' . $key . ']', $field, $value );
	}
	submit_button( __( 'Save homepage', 'egypt-roamer' ) );
	echo '</form></div>';
}

add_action( 'admin_post_er_save_home', static function () {
	if ( ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'er_sanitize_field' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'egypt-roamer' ), 403 );
	}
	check_admin_referer( 'er_save_home' );
	$lang  = isset( $_POST['er_lang'] ) ? sanitize_key( wp_unslash( $_POST['er_lang'] ) ) : '';
	$lang  = in_array( $lang, er_home_languages(), true ) ? $lang : er_home_languages()[0];
	$input = isset( $_POST['er_home'] ) && is_array( $_POST['er_home'] ) ? wp_unslash( $_POST['er_home'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- per field
	$clean = [];
	foreach ( er_home_fields() as $key => $field ) {
		if ( 'heading' !== $field['type'] ) {
			$clean[ $key ] = er_sanitize_field( $field, $input[ $key ] ?? ( 'posts' === $field['type'] ? [] : '' ) );
		}
	}
	foreach ( [ 'budget_smart', 'budget_comfort', 'budget_luxury' ] as $k ) {
		$clean[ $k ] = preg_match( '/^\s*\d+\s*-\s*\d+\s*$/', (string) $clean[ $k ] ) ? preg_replace( '/\s+/', '', $clean[ $k ] ) : '';
	}
	update_option( 'er_home_' . $lang, $clean, false );
	wp_safe_redirect( add_query_arg( [ 'page' => 'er-homepage', 'er_lang' => $lang, 'updated' => 1 ], admin_url( 'themes.php' ) ) );
	exit;
} );

/** Share the Core admin styles/scripts on this screen. */
add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	if ( 'appearance_page_er-homepage' === $hook && defined( 'ER_CORE_URL' ) ) {
		wp_enqueue_media();
		wp_enqueue_style( 'er-admin', ER_CORE_URL . 'assets/admin.css', [], ER_THEME_VERSION );
		wp_enqueue_script( 'er-admin', ER_CORE_URL . 'assets/admin.js', [], ER_THEME_VERSION, true );
	}
} );
