<?php
/**
 * Single commercial item — see template-parts/single-commercial.php.
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="main">';
while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/single-commercial' );
}
echo '</main>';
get_footer();
