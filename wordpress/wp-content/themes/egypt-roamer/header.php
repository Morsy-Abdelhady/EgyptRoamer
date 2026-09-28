<?php
/**
 * Site header — the approved navigation, with WordPress-managed menus,
 * real language URLs and the supplied logo artwork.
 */

defined( 'ABSPATH' ) || exit;

$er_brand_imgs = static function () {
	$set = static function ( string $variant ) {
		$base = 'egypt-roamer-logo-' . $variant;
		return sprintf( '%1$s 600w, %2$s 1200w, %3$s 1759w', er_brand_url( $base . '-600.webp' ), er_brand_url( $base . '-1200.webp' ), er_brand_url( $base . '.webp' ) );
	};
	foreach ( [ 'dark', 'light' ] as $v ) {
		printf(
			'<img class="brand__logo brand__logo--%1$s" src="%2$s" srcset="%3$s" sizes="(max-width: 900px) 243px, 295px" width="1759" height="203" alt="" />',
			esc_attr( $v ),
			esc_url( er_brand_url( 'egypt-roamer-logo-' . $v . '-600.webp' ) ),
			esc_attr( $set( $v ) )
		);
	}
	foreach ( [ 'dark', 'light' ] as $v ) {
		printf(
			'<img class="brand__mark brand__mark--%1$s" src="%2$s" srcset="%2$s 256w, %3$s 525w" sizes="84px" width="525" height="187" alt="" />',
			esc_attr( $v ),
			esc_url( er_brand_url( 'egypt-roamer-mark-' . $v . '-256.webp' ) ),
			esc_url( er_brand_url( 'egypt-roamer-mark-' . $v . '.webp' ) )
		);
	}
};
// home_url( '/' ) first, then the anchor: Polylang only localises the bare home URL.
$er_planner_url = (bool) er_home( 'planner_enabled' ) ? ( is_front_page() ? '#planner' : home_url( '/' ) . '#planner' ) : '';
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'template-parts/sprite' ); ?>

<a class="skip-link" href="#main"><?php er_e( 'Skip to content' ); ?></a>

<?php if ( is_front_page() ) : ?>
	<div class="loader" id="loader" aria-hidden="true">
		<div class="loader__inner">
			<img class="loader__logo" src="<?php echo esc_url( er_brand_url( 'egypt-roamer-logo-dark-600.webp' ) ); ?>" srcset="<?php echo esc_attr( er_brand_url( 'egypt-roamer-logo-dark-600.webp' ) . ' 600w, ' . er_brand_url( 'egypt-roamer-logo-dark-1200.webp' ) . ' 1200w, ' . er_brand_url( 'egypt-roamer-logo-dark.webp' ) . ' 1759w' ); ?>" sizes="min(320px, 70vw)" width="1759" height="203" alt="" />
			<span class="loader__bar"><i></i></span>
		</div>
	</div>
<?php endif; ?>

<div class="scroll-progress" aria-hidden="true"><i id="scroll-progress"></i></div>

<header class="nav" id="nav" data-state="top">
	<div class="nav__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" aria-label="<?php echo esc_attr( er_t( 'Egypt Roamer — home' ) ); ?>" rel="home">
			<?php $er_brand_imgs(); ?>
		</a>

		<nav class="nav__links" aria-label="<?php echo esc_attr( er_t( 'Primary' ) ); ?>">
			<?php echo er_menu( 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_nav_menu output ?>
			<span class="nav__indicator" aria-hidden="true"></span>
		</nav>

		<div class="nav__actions">
			<button class="icon-btn" type="button" data-open="search" aria-label="<?php echo esc_attr( er_t( 'Search Egypt Roamer' ) ); ?>">
				<?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<button class="icon-btn" type="button" data-open="saved" aria-label="<?php echo esc_attr( er_t( 'Saved places and experiences' ) ); ?>">
				<?php echo er_icon( 'i-heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="icon-btn__badge" data-fav-count></span>
			</button>
			<?php er_lang_switcher(); ?>
			<?php if ( $er_planner_url ) : ?>
				<a href="<?php echo esc_url( $er_planner_url ); ?>" class="btn btn--primary btn--sm nav__cta" data-magnetic>
					<?php er_e( 'Plan My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endif; ?>
			<button class="icon-btn nav__burger" type="button" data-open="menu" aria-label="<?php echo esc_attr( er_t( 'Open menu' ) ); ?>" aria-expanded="false" aria-controls="menu">
				<?php echo er_icon( 'i-menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>
	</div>
</header>
