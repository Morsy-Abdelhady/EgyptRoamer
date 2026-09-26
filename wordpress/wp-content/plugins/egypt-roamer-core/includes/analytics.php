<?php
/**
 * Google Tag Manager (with Consent Mode defaults) and the dataLayer events:
 * affiliate_click, booking_click, search, filter_use, newsletter_signup,
 * guide_download, contact_submit. No personal data is pushed.
 *
 * GA4 is configured inside GTM. Search Console verification is done in
 * Rank Math (or by DNS) — not duplicated here.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', static function () {
	$gtm     = (string) er_settings( 'gtm_id' );
	$consent = (string) er_settings( 'consent_default' );
	echo "<script>window.dataLayer=window.dataLayer||[];";
	if ( 'off' !== $consent && $gtm ) {
		$state = 'granted' === $consent ? 'granted' : 'denied';
		echo "function gtag(){dataLayer.push(arguments);}gtag('consent','default',{ad_storage:'" . esc_js( $state ) . "',ad_user_data:'" . esc_js( $state ) . "',ad_personalization:'" . esc_js( $state ) . "',analytics_storage:'" . esc_js( $state ) . "',wait_for_update:500});";
	}
	echo "</script>\n";
	if ( $gtm ) {
		printf(
			"<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>\n",
			esc_js( $gtm )
		);
	}
}, 1 );

add_action( 'wp_body_open', static function () {
	$gtm = (string) er_settings( 'gtm_id' );
	if ( $gtm ) {
		printf( '<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>', esc_url( 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $gtm ) ) );
	}
} );

add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_script( 'er-track', ER_CORE_URL . 'assets/track.js', [], ER_CORE_VERSION, [ 'in_footer' => true, 'strategy' => 'defer' ] );
	wp_localize_script( 'er-track', 'erTrack', [
		'pageType' => er_page_type(),
		'search'   => is_search() ? er_redact( get_search_query( false ) ) : '',
		'results'  => is_search() ? (int) $GLOBALS['wp_query']->found_posts : 0,
	] );
} );

/** A coarse page type for reporting (no IDs, no personal data). */
function er_page_type(): string {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_search() ) {
		return 'search';
	}
	if ( is_singular() ) {
		$type = get_post_type();
		return 'post' === $type ? 'article' : ( 'page' === $type ? 'page' : str_replace( 'er_', '', (string) $type ) );
	}
	if ( is_post_type_archive() ) {
		return str_replace( 'er_', '', (string) get_query_var( 'post_type' ) ) . '_archive';
	}
	if ( is_home() || is_archive() ) {
		return 'article_archive';
	}
	return is_404() ? '404' : 'other';
}

/** Strip anything that looks like an email or a long number before a search term reaches analytics. */
function er_redact( string $text ): string {
	$text = preg_replace( '/[^\s@]+@[^\s@]+/', '[email]', $text );
	$text = preg_replace( '/\d{6,}/', '[number]', (string) $text );
	return mb_substr( trim( (string) $text ), 0, 100 );
}
