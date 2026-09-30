<?php
/**
 * Pages (About, Contact, FAQ, legal).
 *
 * Documents (legal pages) read on the centred column with section tabs when they have three or more
 * sections. A page whose content is an `.er-contact` layout (the Contact page) uses the full container,
 * with the hero on the container edge, so its two columns and the hero share one axis.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();
	$er_wide = str_contains( (string) get_post_field( 'post_content', get_the_ID() ), 'er-contact' );
	er_page_hero( [
		'title'   => get_the_title(),
		'intro'   => has_excerpt() ? get_the_excerpt() : '',
		'image'   => (int) get_post_thumbnail_id(),
		'measure' => ! $er_wide,
	] );
	$er_body = er_body( [ 'cards' => false ] );
	if ( ! $er_wide ) {
		er_section_nav( $er_body['links'], $er_body['label'], true );
	}
	?>
	<div class="page-body container">
		<div class="page-layout <?php echo $er_wide ? 'page-layout--wide' : 'page-layout--single'; ?>">
			<article class="page-main prose prose--doc"><?php echo $er_body['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></article>
		</div>
	</div>
<?php endwhile; ?>
</main>
<?php
get_footer();
