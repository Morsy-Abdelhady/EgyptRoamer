<?php
/**
 * Archives: destinations, tours, experiences, activities, guides, articles.
 *
 * Filters are a GET form (not crawlable links); filtered URLs are noindex
 * (Egypt Roamer Core), so they never multiply into thin indexable pages.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$er_type  = is_post_type_archive() ? (string) get_query_var( 'post_type' ) : ( is_home() ? 'post' : '' );
$er_title = $er_type ? er_type_label( $er_type ) : wp_strip_all_tags( get_the_archive_title() );
$er_intro = '';
if ( $er_type && function_exists( 'er_settings' ) && 'post' !== $er_type ) {
	// Only a real translation outside the default language (no English intro on a translated archive).
	$er_intro = function_exists( 'er_translate_string_strict' ) ? er_translate_string_strict( (string) er_settings( 'archive_intro_' . $er_type ) ) : er_translate_string( (string) er_settings( 'archive_intro_' . $er_type ) );
} elseif ( is_home() && (int) get_option( 'page_for_posts' ) ) {
	$er_intro = get_the_excerpt( (int) get_option( 'page_for_posts' ) );
} else {
	$er_intro = wp_strip_all_tags( get_the_archive_description() );
}
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters
$er_f_dest  = isset( $_GET['destination'] ) ? absint( $_GET['destination'] ) : 0;
$er_f_style = isset( $_GET['style'] ) ? sanitize_key( wp_unslash( $_GET['style'] ) ) : '';
$er_f_topic = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( $_GET['topic'] ) ) : '';
// phpcs:enable
// A filter over an empty archive offers nothing to choose from; it stays once a filter is active.
$er_filterable = in_array( $er_type, [ 'er_tour', 'er_experience', 'er_activity', 'er_guide' ], true ) && ( have_posts() || $er_f_dest || $er_f_style || $er_f_topic );

echo '<main id="main">';
er_page_hero( [ 'eyebrow' => er_t( 'Egypt Roamer' ), 'title' => $er_title, 'intro' => $er_intro ] );
?>
<div class="page-body container">
	<?php if ( $er_filterable ) : ?>
		<form class="filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( $er_type ) ); ?>" aria-label="<?php echo esc_attr( er_t( 'Filter' ) ); ?>">
			<?php if ( 'er_guide' === $er_type ) : ?>
				<label><span><?php er_e( 'Topic' ); ?></span>
					<select name="topic" data-er-filter="topic">
						<option value=""><?php er_e( 'All' ); ?></option>
						<?php foreach ( get_terms( [ 'taxonomy' => 'er_guide_topic', 'hide_empty' => true ] ) as $er_term ) : ?>
							<option value="<?php echo esc_attr( $er_term->slug ); ?>"<?php selected( $er_f_topic, $er_term->slug ); ?>><?php echo esc_html( $er_term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php else : ?>
				<label><span><?php er_e( 'Destination' ); ?></span>
					<select name="destination" data-er-filter="destination">
						<option value=""><?php er_e( 'Anywhere in Egypt' ); ?></option>
						<?php foreach ( get_posts( [ 'post_type' => 'er_destination', 'post_status' => 'publish', 'numberposts' => 50, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] ) as $er_d ) : ?>
							<option value="<?php echo (int) $er_d->ID; ?>"<?php selected( $er_f_dest, $er_d->ID ); ?>><?php echo esc_html( $er_d->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php $er_styles = get_terms( [ 'taxonomy' => 'er_travel_style', 'hide_empty' => true ] ); ?>
				<?php if ( $er_styles && ! is_wp_error( $er_styles ) ) : ?>
					<label><span><?php er_e( 'Travel style' ); ?></span>
						<select name="style" data-er-filter="style">
							<option value=""><?php er_e( 'All' ); ?></option>
							<?php foreach ( $er_styles as $er_term ) : ?>
								<option value="<?php echo esc_attr( $er_term->slug ); ?>"<?php selected( $er_f_style, $er_term->slug ); ?>><?php echo esc_html( $er_term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
			<?php endif; ?>
			<button class="btn btn--outline btn--sm" type="submit"><?php er_e( 'Apply' ); ?></button>
		</form>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php er_card_grid( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ), 'archive-' . str_replace( 'er_', '', $er_type ?: 'list' ), 'h2' ); ?>
		<?php
		the_posts_pagination( [
			'prev_text' => er_icon( 'i-arrow-left' ) . '<span class="visually-hidden">' . esc_html( er_t( 'Previous' ) ) . '</span>',
			'next_text' => '<span class="visually-hidden">' . esc_html( er_t( 'Next' ) ) . '</span>' . er_icon( 'i-arrow' ),
		] );
		?>
	<?php else : ?>
		<div class="empty-state">
			<p class="t-h3"><?php er_e( 'Nothing published here yet.' ); ?></p>
			<p class="muted"><?php er_e( 'We only publish pages when they are genuinely useful. Explore what is ready:' ); ?></p>
			<p><a class="btn btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'er_destination' ) ?: home_url( '/' ) ); ?>"><?php er_e( 'Destinations' ); ?></a></p>
		</div>
	<?php endif; ?>
</div>
</main>
<?php
get_footer();
