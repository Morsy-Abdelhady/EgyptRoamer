<?php
/**
 * Plugin Name:       Egypt Roamer Core
 * Description:       Business logic for Egypt Roamer: content models, affiliate providers & offers, secure /go/ redirects, click tracking, reporting, lead capture and SEO guards. Theme-independent.
 * Version:           1.2.23
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Egypt Roamer
 * License:           GPL-2.0-or-later
 * Text Domain:       egypt-roamer-core
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'ER_CORE_VERSION', '1.2.23' );
define( 'ER_CORE_DB_VERSION', '2' ); // 2: chat tables
define( 'ER_CORE_FILE', __FILE__ );
define( 'ER_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ER_CORE_URL', plugin_dir_url( __FILE__ ) );

// Language lives in the URL (/fr/…), so Polylang's language cookie is unnecessary — and a
// Set-Cookie on every response stops full-page caches from caching pages. This plugin loads
// before Polylang (alphabetical), so the constant is in place when Polylang reads it.
if ( ! defined( 'PLL_COOKIE' ) ) {
	define( 'PLL_COOKIE', false );
}

require_once ER_CORE_DIR . 'includes/fields.php';
require_once ER_CORE_DIR . 'includes/post-types.php';
require_once ER_CORE_DIR . 'includes/meta.php';
require_once ER_CORE_DIR . 'includes/settings.php';
require_once ER_CORE_DIR . 'includes/clicks.php';
require_once ER_CORE_DIR . 'includes/affiliate.php';
require_once ER_CORE_DIR . 'includes/redirect.php';
require_once ER_CORE_DIR . 'includes/reports.php';
require_once ER_CORE_DIR . 'includes/seo.php';
require_once ER_CORE_DIR . 'includes/analytics.php';
require_once ER_CORE_DIR . 'includes/leads.php';
require_once ER_CORE_DIR . 'includes/multilingual.php';
require_once ER_CORE_DIR . 'includes/legal.php';
require_once ER_CORE_DIR . 'includes/assistant.php';
require_once ER_CORE_DIR . 'includes/chat.php';
require_once ER_CORE_DIR . 'includes/freshness.php';
require_once ER_CORE_DIR . 'includes/previews.php';
require_once ER_CORE_DIR . 'includes/api.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once ER_CORE_DIR . 'includes/cli.php';
}

add_action( 'init', static function () {
	load_plugin_textdomain( 'egypt-roamer-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}, 0 );

register_activation_hook( __FILE__, static function () {
	er_register_content_types();
	er_register_redirect_rewrites();
	er_install_tables();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, static function () {
	wp_clear_scheduled_hook( 'er_purge_clicks' );
	wp_clear_scheduled_hook( 'er_chat_purge' );
	flush_rewrite_rules();
} );

// Upgrade the schema in place when the plugin is updated without re-activation.
add_action( 'plugins_loaded', static function () {
	if ( get_option( 'er_core_db_version' ) !== ER_CORE_DB_VERSION ) {
		er_install_tables();
	}
} );
