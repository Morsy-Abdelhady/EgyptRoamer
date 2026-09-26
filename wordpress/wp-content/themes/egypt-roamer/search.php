<?php
/**
 * Internal search results (noindex — Egypt Roamer Core).
 */

defined( 'ABSPATH' ) || exit;

get_header();
er_page_hero( [ 'eyebrow' => er_t( 'Search' ), 'title' => er_t( 'Results for “{q}”', [ 'q' => get_search_query( false ) ] ) ] );
?>
<main id="main" class="page-body container" data-er-search>
	<?php get_search_form(); ?>
	<?php if ( have_posts() ) : ?>
		<?php er_card_grid( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ), 'search' ); ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<div class="empty-state">
			<p class="t-h3"><?php echo esc_html( er_t( 'Nothing for “{q}” yet — try “Luxor”, “diving” or “cruise”.', [ 'q' => get_search_query( false ) ] ) ); ?></p>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
