<?php
/**
 * Site footer — newsletter (real sign-up), WordPress menus, logo artwork,
 * legal links, mobile dock and overlays.
 */

defined( 'ABSPATH' ) || exit;

$er_col = static function ( string $title, string $location ) {
	$menu = er_menu( $location );
	if ( ! $menu ) {
		return;
	}
	echo '<div><h3 class="t-label">' . esc_html( $title ) . '</h3>' . $menu . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_nav_menu output
};
// home_url( '/' ) first, then the anchor: Polylang only localises the bare home URL.
$er_planner_url = (bool) er_home( 'planner_enabled' ) ? ( is_front_page() ? '#planner' : home_url( '/' ) . '#planner' ) : '';
?>
<footer class="footer on-dark" id="footer">
	<div class="container">
		<div class="footer__top">
			<div class="footer__letter" id="newsletter">
				<p class="eyebrow"><?php echo esc_html( er_home( 'letter_eyebrow' ) ); ?></p>
				<h2 class="t-h2"><?php echo wp_kses( er_home( 'letter_title' ), er_inline_kses_safe() ); ?></h2>
				<p class="muted-dark"><?php echo esc_html( er_home( 'letter_copy' ) ); ?></p>
				<?php if ( function_exists( 'er_form_guard_fields' ) ) : ?>
					<form class="subscribe" data-subscribe method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="er_subscribe" />
						<input type="hidden" name="source" value="footer" />
						<input type="hidden" name="lang" value="<?php echo esc_attr( er_lang() ); ?>" />
						<?php echo er_form_guard_fields(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in plugin ?>
						<label class="visually-hidden" for="sub-email"><?php er_e( 'Email address' ); ?></label>
						<?php echo er_icon( 'i-mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<input id="sub-email" type="email" name="email" placeholder="<?php echo esc_attr( er_t( 'Your email address' ) ); ?>" required autocomplete="email" />
						<button class="btn btn--primary btn--sm" type="submit"><?php er_e( 'Subscribe' ); ?></button>
					</form>
					<?php er_form_notice( 'newsletter' ); ?>
					<?php
					$er_privacy = function_exists( 'er_settings' ) ? (int) er_settings( 'privacy_page' ) : 0;
					$er_privacy = $er_privacy && function_exists( 'er_translated_post_id' ) ? er_translated_post_id( $er_privacy ) : $er_privacy;
					if ( $er_privacy && 'publish' === get_post_status( $er_privacy ) ) :
						?>
						<p class="subscribe__note"><?php er_e( 'We only use your email to send the letter. Unsubscribe any time.' ); ?> <a href="<?php echo esc_url( get_permalink( $er_privacy ) ); ?>"><?php er_e( 'Privacy' ); ?></a></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<nav class="footer__cols" aria-label="<?php echo esc_attr( er_t( 'Footer' ) ); ?>">
				<?php
				$er_col( er_t( 'Explore' ), 'footer_explore' );
				$er_col( er_t( 'Plan' ), 'footer_plan' );
				$er_col( er_t( 'Egypt Roamer' ), 'footer_company' );
				?>
			</nav>
		</div>

		<div class="footer__word">
			<img class="footer__logo" src="<?php echo esc_url( er_brand_url( 'egypt-roamer-logo-dark-600.webp' ) ); ?>" srcset="<?php echo esc_attr( er_brand_url( 'egypt-roamer-logo-dark-600.webp' ) . ' 600w, ' . er_brand_url( 'egypt-roamer-logo-dark-1200.webp' ) . ' 1200w, ' . er_brand_url( 'egypt-roamer-logo-dark.webp' ) . ' 1759w' ); ?>" sizes="(max-width: 960px) calc(100vw - 2rem), 880px" width="1759" height="203" alt="<?php echo esc_attr( er_t( 'Egypt Roamer — More than a destination' ) ); ?>" loading="lazy" decoding="async" />
		</div>

		<div class="footer__bottom">
			<p><?php echo esc_html( er_t( '© {year} Egypt Roamer. Independent travel discovery. All rights reserved.', [ 'year' => wp_date( 'Y' ) ] ) ); ?></p>
			<?php
			$er_legal = er_menu( 'legal', '%3$s' );
			if ( $er_legal ) {
				echo '<p class="footer__legal">' . wp_kses_post( str_replace( [ '<li', '</li>' ], [ '<span', '</span>' ], $er_legal ) ) . '</p>';
			}
			?>
		</div>
	</div>
</footer>

<nav class="dock" aria-label="<?php echo esc_attr( er_t( 'Quick actions' ) ); ?>">
	<a href="<?php echo esc_url( get_post_type_archive_link( 'er_destination' ) ?: home_url( '/' ) ); ?>" class="dock__item"><?php echo er_icon( 'i-compass' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Explore' ); ?></span></a>
	<button type="button" class="dock__item" data-open="saved"><?php echo er_icon( 'i-heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Saved' ); ?></span><i class="dock__badge" data-fav-count></i></button>
	<button type="button" class="dock__item" data-open="search"><?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Search' ); ?></span></button>
	<?php if ( $er_planner_url ) : ?>
		<a href="<?php echo esc_url( $er_planner_url ); ?>" class="dock__plan"><?php echo er_icon( 'i-route' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Plan My Trip' ); ?></span></a>
	<?php endif; ?>
</nav>

<div class="menu" id="menu" hidden>
	<div class="menu__inner">
		<?php echo er_menu( 'primary', '<ul class="menu__links">%3$s</ul>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $er_planner_url ) : ?>
			<a href="<?php echo esc_url( $er_planner_url ); ?>" class="btn btn--primary btn--lg btn--block" data-close><?php er_e( 'Plan My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php endif; ?>
		<?php er_lang_switcher( 'mobile' ); ?>
	</div>
</div>

<div class="overlay search" id="search" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( er_t( 'Search' ) ); ?>" hidden>
	<div class="search__box">
		<form class="search__field" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<input type="search" name="s" placeholder="<?php echo esc_attr( er_t( 'Search destinations, tours, cruises, guides…' ) ); ?>" aria-label="<?php echo esc_attr( er_t( 'Search' ) ); ?>" data-search-input />
			<button class="icon-btn" type="button" data-close aria-label="<?php echo esc_attr( er_t( 'Close search' ) ); ?>"><?php echo er_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</form>
		<div class="search__results" data-search-results></div>
	</div>
</div>

<div class="overlay drawer" id="saved" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( er_t( 'Saved' ) ); ?>" hidden>
	<div class="drawer__panel">
		<header class="drawer__head">
			<h2 class="t-h3"><?php er_e( 'Saved for later' ); ?></h2>
			<button class="icon-btn" type="button" data-close aria-label="<?php echo esc_attr( er_t( 'Close saved' ) ); ?>"><?php echo er_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</header>
		<div class="drawer__body" data-saved-list></div>
	</div>
</div>

<?php if ( is_front_page() && er_home( 'film_enabled' ) ) : ?>
	<div class="overlay film" id="film" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( er_t( 'Egypt Roamer film' ) ); ?>" hidden>
		<div class="film__stage" data-film-stage></div>
		<p class="film__caption" data-film-caption></p>
		<span class="film__bar"><i data-film-bar></i></span>
		<button class="icon-btn film__close" type="button" data-close aria-label="<?php echo esc_attr( er_t( 'Close film' ) ); ?>"><?php echo er_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
<?php endif; ?>

<div class="toast" role="status" aria-live="polite" data-toast></div>

<?php wp_footer(); ?>
</body>
</html>
