<?php
/**
 * Front-end assets.
 *
 * - Fonts are self-hosted (no Google Fonts request); Arabic faces load only on
 *   Arabic pages; Chinese pages fall back to Google's Noto SC (too large to self-host).
 * - The cinematic homepage bundle (+ GSAP/Lenis, vendored) loads on the front page only;
 *   inner pages get a small "site" bundle.
 * - Only the current language's UI dictionary is sent.
 */

defined( 'ABSPATH' ) || exit;

function er_asset_ver( string $rel ): string {
	$file = ER_THEME_DIR . '/' . $rel;
	return file_exists( $file ) ? (string) filemtime( $file ) : ER_THEME_VERSION;
}

add_action( 'wp_enqueue_scripts', static function () {
	$css  = static fn ( $name ) => ER_THEME_URI . '/assets/css/' . $name . '.css';
	$home = is_front_page();
	$lang = er_lang();

	wp_enqueue_style( 'er-fonts', $css( 'fonts' ), [], er_asset_ver( 'assets/css/fonts.css' ) );
	if ( 'ar' === $lang ) {
		wp_enqueue_style( 'er-fonts-ar', $css( 'fonts-arabic' ), [], er_asset_ver( 'assets/css/fonts-arabic.css' ) );
	} elseif ( 'zh' === $lang ) {
		wp_enqueue_style( 'er-fonts-zh', 'https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;500;600&family=Noto+Sans+SC:wght@300;400;500;600&display=swap', [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	$deps = [ 'er-fonts' ];
	foreach ( [ 'tokens', 'base', 'layout' ] as $name ) {
		wp_enqueue_style( 'er-' . $name, $css( $name ), $deps, er_asset_ver( "assets/css/{$name}.css" ) );
		$deps = [ 'er-' . $name ];
	}
	if ( $home ) {
		wp_enqueue_style( 'er-journey', $css( 'journey' ), $deps, er_asset_ver( 'assets/css/journey.css' ) );
		$deps = [ 'er-journey' ];
	}
	wp_enqueue_style( 'er-sections', $css( 'sections' ), $deps, er_asset_ver( 'assets/css/sections.css' ) );
	wp_enqueue_style( 'er-pages', $css( 'pages' ), [ 'er-sections' ], er_asset_ver( 'assets/css/pages.css' ) );
	if ( is_rtl() || in_array( $lang, [ 'ar', 'zh' ], true ) ) {
		wp_enqueue_style( 'er-rtl', $css( 'rtl' ), [ 'er-pages' ], er_asset_ver( 'assets/css/rtl.css' ) );
	}

	// Scripts: the language dictionary and the CMS payload run before the bundle.
	$bundle = $home ? 'home' : 'site';
	$deps   = [];
	if ( $home ) {
		foreach ( [ 'gsap' => 'gsap.min.js', 'gsap-st' => 'ScrollTrigger.min.js', 'lenis' => 'lenis.min.js' ] as $handle => $file ) {
			wp_enqueue_script( 'er-' . $handle, ER_THEME_URI . '/assets/vendor/' . $file, 'gsap-st' === $handle ? [ 'er-gsap' ] : [], er_asset_ver( 'assets/vendor/' . $file ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
			$deps[] = 'er-' . $handle;
		}
	}
	if ( 'en' !== $lang && in_array( $lang, er_js_languages(), true ) ) {
		wp_enqueue_script( 'er-i18n', ER_THEME_URI . "/assets/js/locales/{$lang}.js", [], er_asset_ver( "assets/js/locales/{$lang}.js" ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
		$deps[] = 'er-i18n';
	}
	wp_enqueue_script( 'er-app', ER_THEME_URI . "/assets/js/{$bundle}.js", $deps, er_asset_ver( "assets/js/{$bundle}.js" ), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	wp_add_inline_script( 'er-app', 'window.ER_DATA = ' . wp_json_encode( er_payload( $home ? 'home' : 'site' ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . ';', 'before' );
} );

/** Every page shows the approved stock photos (hero or cards) until featured images exist: connect early. */
add_action( 'wp_head', static function () {
	echo '<link rel="preconnect" href="https://images.unsplash.com" />' . "\n";
}, 2 );

/** The homepage LCP image: preload it with high priority (Media Library image or approved stock photo). */
add_action( 'wp_head', static function () {
	if ( ! is_front_page() ) {
		return;
	}
	$id = (int) er_home( 'hero_image' );
	if ( $id ) {
		$src    = wp_get_attachment_image_url( $id, 'er-hero' );
		$srcset = wp_get_attachment_image_srcset( $id, 'er-hero' );
		printf( '<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="100vw" fetchpriority="high" />' . "\n", esc_url( (string) $src ), esc_attr( (string) $srcset ) );
		return;
	}
	$photo = er_home_stock()['hero'];
	$url   = static fn ( $w ) => 'https://images.unsplash.com/photo-' . $photo . '?auto=format&fit=crop&w=' . $w . '&q=76';
	printf( '<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="100vw" fetchpriority="high" />' . "\n", esc_url( $url( 2000 ) ), esc_attr( $url( 900 ) . ' 900w, ' . $url( 1400 ) . ' 1400w, ' . $url( 2000 ) . ' 2000w, ' . $url( 2800 ) . ' 2800w' ) );
}, 3 );

/** Brand icons and theme colour. */
add_action( 'wp_head', static function () {
	if ( has_site_icon() ) {
		return; // an icon set in Settings → General wins
	}
	printf( '<link rel="icon" href="%s" sizes="any" />' . "\n", esc_url( er_brand_url( 'favicon.ico' ) ) );
	printf( '<link rel="icon" href="%s" type="image/png" sizes="32x32" />' . "\n", esc_url( er_brand_url( 'favicon-32.png' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s" />' . "\n", esc_url( er_brand_url( 'apple-touch-icon.png' ) ) );
}, 4 );
add_action( 'wp_head', static fn () => print( '<meta name="theme-color" content="#101820" />' . "\n" ), 4 );

/** Default share image for pages without a featured image (used by the Core fallback; set Rank Math's default to the same file). */
add_filter( 'er_default_share_image', static fn () => er_brand_url( 'og-image.jpg' ) );

/** Logo for the Organization structured data (Core): the dark wordmark, made for light backgrounds. */
add_filter( 'er_brand_logo', static fn () => er_brand_url( 'egypt-roamer-logo-dark-600.png' ) );

/** JS flag before first paint (the design's no-js/js states). */
add_action( 'wp_head', static fn () => print( "<script>document.documentElement.classList.replace('no-js','js');</script>\n" ), 0 );

/*
 * GoDaddy's mu-plugins (godaddy-launch) enqueue WordPress's admin component styles and their own
 * stylesheet on the front end. The theme uses none of them, and they block rendering for every
 * visitor, so they are dropped for logged-out visitors (logged-in users keep GoDaddy's UI intact).
 */
add_action( 'wp_enqueue_scripts', static function () {
	if ( is_user_logged_in() ) {
		return;
	}
	foreach ( [ 'wp-components', 'wp-theme', 'godaddy-styles' ] as $handle ) {
		wp_dequeue_style( $handle );
	}
}, 100 );
