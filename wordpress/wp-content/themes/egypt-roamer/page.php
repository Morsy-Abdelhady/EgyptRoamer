<?php
/**
 * Pages (About, Contact, FAQ, legal).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();
	er_page_hero( [
		'title' => get_the_title(),
		'intro' => has_excerpt() ? get_the_excerpt() : '',
		'image' => (int) get_post_thumbnail_id(),
		'measure' => true,
	] );
	?>
	<div class="page-body container">
		<div class="page-layout page-layout--single">
			<article class="page-main prose"><?php the_content(); ?></article>
		</div>
	</div>
<?php endwhile; ?>
</main>
<?php
get_footer();
