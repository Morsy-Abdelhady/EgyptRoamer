<?php
/**
 * Guides — same editorial template as articles (see single.php).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();
	$er_id   = get_the_ID();
	$er_type = get_post_type();
	$er_tax  = 'er_guide' === $er_type ? get_the_terms( $er_id, 'er_guide_topic' ) : get_the_category();
	$er_meta = sprintf(
		'<span>%1$s %2$s</span><span>%3$s</span>%4$s',
		esc_html( er_t( 'By' ) ),
		// No named author: the publication itself, as in the Article structured data (Core seo.php).
		esc_html( get_the_author() ?: 'Egypt Roamer' ),
		'<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>',
		get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' ) ? '<span>' . esc_html( er_t( 'Updated' ) ) . ' <time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time></span>' : ''
	) . '<span>' . er_icon( 'i-clock', 'icon--sm' ) . esc_html( er_t( '{n} min read', [ 'n' => er_read_minutes( $er_id ) ] ) ) . '</span>';

	er_page_hero( [
		'eyebrow' => $er_tax && ! is_wp_error( $er_tax ) ? $er_tax[0]->name : er_type_label( $er_type ),
		'title'   => get_the_title(),
		'intro'   => has_excerpt() ? get_the_excerpt() : '',
		'image'   => (int) get_post_thumbnail_id(),
		// The guide's stand-in photo (its cards already show it) until a featured image is set, as on
		// destinations and experiences. Copied from the article template, guides had a text-only hero.
		'stock'   => 'er_guide' === $er_type && function_exists( 'er_stock_id_for' ) ? er_stock_id_for( (int) $er_id ) : '',
		'meta'    => $er_meta,
		'measure' => true,
	] );
	$er_dests   = 'er_guide' === $er_type ? er_get_related( $er_id, '_er_destination' ) : [];
	$er_related = 'er_guide' === $er_type ? er_get_related( $er_id, '_er_related' ) : [];
	$er_body    = er_body();
	er_section_nav( $er_body['links'], $er_body['label'], true );
	?>
	<div class="page-body container">
		<div class="page-layout page-layout--single">
			<article class="page-main">
				<div class="prose"><?php echo $er_body['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?></div>
				<?php
				$er_bio = get_the_author_meta( 'description' );
				if ( $er_bio ) :
					?>
					<aside class="author-box" aria-label="<?php echo esc_attr( er_t( 'About the author' ) ); ?>">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', '' ); ?>
						<div>
							<p class="t-label"><?php er_e( 'About the author' ); ?></p>
							<p class="author-box__name"><?php the_author(); ?></p>
							<p><?php echo esc_html( $er_bio ); ?></p>
						</div>
					</aside>
				<?php endif; ?>
			</article>
		</div>

		<?php if ( $er_related ) : ?>
			<section class="related" aria-labelledby="rel-title">
				<h2 id="rel-title" class="t-h2"><?php er_e( 'Experiences in this guide' ); ?></h2>
				<?php er_card_grid( wp_list_pluck( $er_related, 'ID' ), 'guide-related' ); ?>
			</section>
		<?php endif; ?>
		<?php if ( $er_dests ) : ?>
			<section class="related" aria-labelledby="dest-title">
				<h2 id="dest-title" class="t-h2"><?php er_e( 'Destinations in this guide' ); ?></h2>
				<?php er_card_grid( wp_list_pluck( $er_dests, 'ID' ), 'guide-destinations' ); ?>
			</section>
		<?php endif; ?>
	</div>
<?php endwhile; ?>
</main>
<?php
get_footer();
