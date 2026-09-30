<?php
/**
 * 404 — helpful way back, never a dead end.
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="main">';
er_page_hero( [ 'eyebrow' => '404', 'title' => er_t( 'This page wandered off' ), 'intro' => er_t( 'The link may be old, or the page is not published yet. Try a search, or start from one of these.' ) ] );
?>
<div class="page-body container">
	<?php get_search_form(); ?>
	<p class="links-row">
		<a class="btn btn--outline" href="<?php echo esc_url( er_home_url() ); ?>"><?php er_e( 'Home' ); ?></a>
		<a class="btn btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'er_destination' ) ?: er_home_url() ); ?>"><?php er_e( 'Destinations' ); ?></a>
		<a class="btn btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'er_experience' ) ?: er_home_url() ); ?>"><?php er_e( 'Experiences' ); ?></a>
	</p>
</div>
</main>
<?php
get_footer();
