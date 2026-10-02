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

	if ( 'ar' === $lang ) {
		wp_enqueue_style( 'er-fonts-ar', $css( 'fonts-arabic' ), [], er_asset_ver( 'assets/css/fonts-arabic.css' ) );
	} elseif ( 'zh' === $lang ) {
		wp_enqueue_style( 'er-fonts-zh', 'https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;500;600&family=Noto+Sans+SC:wght@300;400;500;600&display=swap', [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	// One stylesheet per template, built from the sources below by tools/build.py (CSS_BUNDLES): same rules,
	// same order, one render-blocking request instead of six or seven (docs/PERFORMANCE-2026-10-02.md).
	// Without a bundle (a checkout that was never built) the sources load one by one, as before.
	$bundle_css = $home ? 'bundle-home' : 'bundle-site';
	if ( file_exists( ER_THEME_DIR . "/assets/css/{$bundle_css}.css" ) ) {
		wp_enqueue_style( 'er-pages', $css( $bundle_css ), [], er_asset_ver( "assets/css/{$bundle_css}.css" ) );
	} else {
		wp_enqueue_style( 'er-fonts', $css( 'fonts' ), [], er_asset_ver( 'assets/css/fonts.css' ) );
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
	}
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

/**
 * The homepage's first view is drawn in these faces (the display serif and its italic, the sans in four
 * weights; per script). Preloading them starts the downloads with the stylesheet instead of after it (measured: docs/PERFORMANCE-2026-10-02.md). Faces for the page's script only; Chinese uses
 * Google's fonts, which are not preloaded.
 */
add_action( 'wp_head', static function () {
	if ( ! is_front_page() ) {
		return;
	}
	// Every face the phone's first view draws (measured: title, "Feel", hero text, labels, dock, buttons).
	$latin = [ 'playfair-display-latin-400-normal', 'inter-latin-300-normal', 'playfair-display-latin-400-italic', 'inter-latin-500-normal', 'inter-latin-400-normal', 'inter-latin-600-normal' ];
	$faces = [
		// Arabic text also needs the Latin faces: the stacks start with the Latin family, so its spaces and digits
		// are drawn (and fetched) from Inter/Playfair; without a preload they were found only after the CSS.
		'ar' => array_merge( [ 'noto-naskh-arabic-arabic-400-normal', 'ibm-plex-sans-arabic-arabic-300-normal', 'ibm-plex-sans-arabic-arabic-500-normal', 'ibm-plex-sans-arabic-arabic-400-normal', 'ibm-plex-sans-arabic-arabic-600-normal' ], $latin ),
		'ru' => [ 'playfair-display-cyrillic-400-normal', 'inter-cyrillic-300-normal', 'playfair-display-cyrillic-400-italic', 'inter-cyrillic-500-normal', 'inter-cyrillic-400-normal', 'inter-cyrillic-600-normal' ],
		'zh' => [],
	][ er_lang() ] ?? $latin;
	foreach ( $faces as $face ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n", esc_url( ER_THEME_URI . '/assets/fonts/' . $face . '.woff2' ) );
	}
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

/**
 * Stale-HTML guard (Core includes/freshness.php, docs/CACHE-2026-10-01.md): the page's build id, and a check
 * against the current one. The host's edge makes browsers keep pages for 31 days; a page older than the
 * site reloads once (or is fetched once past the caches with ?nocache=…). At most one request per browser
 * every 30 minutes while pages are current; at once when a page's id is not the last one seen. The check runs
 * after the first contentful paint, at low priority: it decides about a reload, never about what is drawn,
 * so it stays off the first view's critical path (PageSpeed listed it there, 2026-10-02).
 */
add_filter( 'er_build_id_parts', static function ( array $parts ): array {
	$parts['theme']  = ER_THEME_VERSION;
	$parts['assets'] = er_asset_ver( 'style.css' ) . '.' . er_asset_ver( 'assets/js/home.js' ) . '.' . er_asset_ver( 'assets/js/site.js' ) . '.' . er_asset_ver( 'assets/css/pages.css' ) . '.' . er_asset_ver( 'assets/css/bundle-home.css' ) . '.' . er_asset_ver( 'assets/css/bundle-site.css' );
	return $parts;
} );
add_action( 'wp_head', static function () {
	if ( ! function_exists( 'er_build_id' ) || is_customize_preview() ) {
		return;
	}
	printf( '<meta name="er-build" content="%s" />' . "\n", esc_attr( er_build_id() ) );
	$endpoint = wp_json_encode( esc_url_raw( rest_url( 'egypt-roamer/v1/build' ) ) );
	// The edge bypasses its cache only when the query string STARTS with "nocache", so it goes first.
	// phpcs:ignore WordPress.Security.EscapeOutput -- fixed script; the endpoint is JSON-encoded
	echo "<script>(function(){var q=location.search,u;if(/[?&]nocache=/.test(q)){u=new URL(location.href);u.searchParams.delete('nocache');history.replaceState(null,'',u)}var m=document.querySelector('meta[name=er-build]');if(!m||!window.fetch||!window.JSON)return;var b=m.content,K='er-build',s={};try{s=JSON.parse(localStorage.getItem(K)||'{}')||{}}catch(e){}if(s.cur===b&&Date.now()-s.at<18e5)return;var go=function(){fetch({$endpoint},{cache:'no-store',credentials:'omit',priority:'low'}).then(function(r){return r.ok?r.json():null}).then(function(d){if(!d||!d.build)return;try{localStorage.setItem(K,JSON.stringify({cur:d.build,at:Date.now()}))}catch(e){}if(d.build===b)return;var R='er-build-reload',done=null;try{done=sessionStorage.getItem(R);sessionStorage.setItem(R,b)}catch(e){return}if(done!==b){location.reload();return}if(!/[?&]nocache=/.test(q)){location.replace(location.pathname+'?nocache='+d.build+(q?'&'+q.slice(1):'')+location.hash)}})['catch'](function(){})},once=false,run=function(){if(!once){once=true;go()}};try{new PerformanceObserver(function(l){if(l.getEntriesByName('first-contentful-paint').length)run()}).observe({type:'paint',buffered:true})}catch(e){run()}setTimeout(run,1500)})();</script>\n";
}, 1 );

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

/**
 * Homepage scripts on phones run after the first paint (launch gate, 2026-10-02).
 * The homepage bundle and its libraries (GSAP, ScrollTrigger, Lenis, the language file) were deferred
 * scripts: they ran before the first paint, so phones showed nothing until ~86 KB of JavaScript had run.
 * Their tags are kept as inert placeholders; a small runner at the end of the page starts them in order,
 * at once on desktop (the cinematic intro is unchanged); on phones (the hero's phone layout, ≤ 900 px), where
 * the hero is content-first (pages.css), once the first view is painted in its final fonts (first contentful
 * paint and document.fonts.ready; at most 2 s), so script work never delays the hero's paint. Downloads still begin early
 * (preload in <head>), so only the moment they run moves. Without the runner (an error, or no JS) the
 * homepage still works as the stacked, static fallback.
 */
function er_home_deferred_handles(): array {
	return [ 'er-gsap', 'er-gsap-st', 'er-lenis', 'er-i18n', 'er-app' ];
}
add_filter( 'script_loader_tag', static function ( string $tag, string $handle, string $src ): string {
	if ( ! is_front_page() || ! in_array( $handle, er_home_deferred_handles(), true ) ) {
		return $tag;
	}
	// Only the external <script src> becomes a placeholder; inline "before" scripts (ER_DATA) still run in place.
	return (string) preg_replace(
		'#<script\b[^>]*\bsrc=(["\'])' . preg_quote( $src, '#' ) . '\1[^>]*>\s*</script>#',
		sprintf( '<script type="text/plain" data-er-run="%s" id="%s-js"></script>', esc_url( $src ), esc_attr( $handle ) ),
		$tag,
		1
	);
}, 10, 3 );
add_action( 'wp_head', static function () {
	if ( ! is_front_page() ) {
		return;
	}
	$scripts = wp_scripts();
	foreach ( er_home_deferred_handles() as $handle ) {
		if ( ! wp_script_is( $handle, 'enqueued' ) || empty( $scripts->registered[ $handle ] ) ) {
			continue;
		}
		$dep = $scripts->registered[ $handle ];
		$url = $dep->ver ? add_query_arg( 'ver', $dep->ver, $dep->src ) : $dep->src;
		printf( '<link rel="preload" href="%s" as="script" />' . "\n", esc_url( $url ) );
	}
}, 4 );
add_action( 'wp_footer', static function () {
	if ( ! is_front_page() ) {
		return;
	}
	// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- runs the enqueued scripts above
	echo "<script>(function(){function run(){document.querySelectorAll('script[data-er-run]').forEach(function(p){var s=document.createElement('script');s.src=p.getAttribute('data-er-run');s.async=false;p.parentNode.replaceChild(s,p)})}if(!(window.matchMedia&&matchMedia('(max-width: 900px)').matches)){run();return}var done=false;function go(){if(done)return;done=true;requestAnimationFrame(function(){setTimeout(run,0)})}function painted(){if(document.fonts&&document.fonts.ready)document.fonts.ready.then(go,go);else go()}try{new PerformanceObserver(function(l){if(l.getEntriesByName('first-contentful-paint').length)painted()}).observe({type:'paint',buffered:true})}catch(e){painted()}setTimeout(go,2000)})();</script>\n";
}, 100 );
