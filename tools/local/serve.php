<?php
// Router for the `php -S` workers started by proxy.py: static files as they are, PHP files directly,
// everything else through WordPress's index.php (pretty permalinks).
$root = __DIR__ . '/wordpress';
$path = rawurldecode( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
$file = $root . $path;

if ( is_dir( $file ) && is_file( rtrim( $file, '/' ) . '/index.php' ) ) {
	$file = rtrim( $file, '/' ) . '/index.php';
}
if ( is_file( $file ) && ! str_ends_with( $file, '.php' ) ) {
	return false;
}
if ( ! is_file( $file ) ) {
	$file = $root . '/index.php';
}
$_SERVER['SCRIPT_FILENAME'] = $file;
$_SERVER['SCRIPT_NAME']     = substr( $file, strlen( $root ) );
$_SERVER['PHP_SELF']        = $_SERVER['SCRIPT_NAME'];
chdir( dirname( $file ) );
require $file;
