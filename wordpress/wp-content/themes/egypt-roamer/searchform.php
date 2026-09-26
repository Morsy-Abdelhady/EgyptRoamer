<?php defined( 'ABSPATH' ) || exit; ?>
<form class="search-inline" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="search-inline"><?php er_e( 'Search' ); ?></label>
	<?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<input id="search-inline" type="search" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php echo esc_attr( er_t( 'Search destinations, tours, cruises, guides…' ) ); ?>" />
	<button class="btn btn--primary btn--sm" type="submit"><?php er_e( 'Search' ); ?></button>
</form>
