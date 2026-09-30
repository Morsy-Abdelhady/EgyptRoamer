<?php
/**
 * Egypt Roamer theme — presentation layer.
 *
 * Business logic (content types, affiliate offers, /go/ redirects, tracking,
 * reports, leads, SEO guards) lives in the Egypt Roamer Core plugin; this theme
 * only renders it. If the theme is replaced, all content and data stay intact.
 */

defined( 'ABSPATH' ) || exit;

define( 'ER_THEME_VERSION', '1.2.4' );
define( 'ER_THEME_DIR', get_template_directory() );
define( 'ER_THEME_URI', get_template_directory_uri() );

require_once ER_THEME_DIR . '/inc/setup.php';
require_once ER_THEME_DIR . '/inc/i18n.php';
require_once ER_THEME_DIR . '/inc/template-tags.php';
require_once ER_THEME_DIR . '/inc/sections.php';
require_once ER_THEME_DIR . '/inc/home-settings.php';
require_once ER_THEME_DIR . '/inc/payload.php';
require_once ER_THEME_DIR . '/inc/assets.php';
