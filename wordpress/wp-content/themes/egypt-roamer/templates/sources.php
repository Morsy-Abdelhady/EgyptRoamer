<?php
/**
 * Template Name: Sources
 *
 * The public list of sources behind the guides (Core data/sources.json, built from docs/CONTENT-SOURCES.md by
 * tools/sources.py): each checked fact, the published sources it was checked against, and when.
 */

defined( 'ABSPATH' ) || exit;

get_header();
$er_file = defined( 'ER_CORE_DIR' ) ? ER_CORE_DIR . 'data/sources.json' : '';
$er_data = $er_file && is_readable( $er_file ) ? (array) json_decode( (string) file_get_contents( $er_file ), true ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions
$er_rows = (array) ( $er_data['rows'] ?? [] );
?>
<main id="main">
	<?php er_page_hero( [ 'eyebrow' => er_t( 'Egypt Roamer' ), 'title' => get_the_title(), 'intro' => er_t( 'Every fact in our guides is checked against a published source. These are the sources, with the date each one was read.' ) ] ); ?>
	<div class="page-body container">
		<div class="srcs">
			<p class="srcs__note"><?php er_e( 'The sources are listed as published, in their original language.' ); ?></p>
			<ol class="srcs__list">
				<?php foreach ( $er_rows as $er_r ) : ?>
					<li>
						<p class="srcs__claim" lang="en"><?php echo esc_html( (string) $er_r['claim'] ); ?></p>
						<p class="srcs__links">
							<?php foreach ( (array) $er_r['links'] as $er_l ) : ?>
								<a href="<?php echo esc_url( (string) $er_l['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( (string) $er_l['name'] ); ?> ↗</a>
							<?php endforeach; ?>
							<small><?php echo esc_html( er_t( 'Checked {date}', [ 'date' => date_i18n( get_option( 'date_format' ), strtotime( (string) $er_r['checked'] ) ) ] ) ); ?></small>
						</p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</main>
<?php
get_footer();
