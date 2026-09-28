<?php
/**
 * Template tags — small, escaped building blocks shared by all templates.
 */

defined( 'ABSPATH' ) || exit;

/** Inline markup allowed in editable headlines (<em class="serif">, <br>). */
function er_inline_kses_safe(): array {
	return [ 'em' => [ 'class' => [] ], 'span' => [ 'class' => [] ], 'br' => [], 'strong' => [], 'b' => [], 'i' => [] ];
}

/** Inline sprite icon (the sprite is printed once in header.php). */
function er_icon( string $id, string $class = '' ): string {
	return sprintf( '<svg class="icon %s" aria-hidden="true"><use href="#%s"/></svg>', esc_attr( $class ), esc_attr( $id ) );
}

function er_brand_url( string $file ): string {
	return ER_THEME_URI . '/assets/img/brand/' . $file;
}

/**
 * Responsive image from the Media Library. Alt text falls back to the parent
 * post title so no content image ships without alt text.
 */
function er_img( int $attachment_id, string $size = 'large', array $attrs = [] ): string {
	if ( ! $attachment_id ) {
		return '';
	}
	$attrs += [ 'loading' => 'lazy', 'decoding' => 'async' ];
	if ( ! isset( $attrs['alt'] ) ) {
		$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( '' === $alt ) {
			$parent = wp_get_post_parent_id( $attachment_id );
			$alt    = $parent ? get_the_title( $parent ) : get_the_title( $attachment_id );
		}
		$attrs['alt'] = $alt;
	}
	return (string) wp_get_attachment_image( $attachment_id, $size, false, $attrs );
}

/**
 * The prototype's photography as a stand-in until an editor sets an image in
 * the Media Library. Returns a responsive <img> for an Unsplash photo id.
 */
function er_stock_img( string $photo_id, string $alt, array $attrs = [], array $widths = [ 900, 1400, 2000 ] ): string {
	$url    = static fn ( $w ) => 'https://images.unsplash.com/photo-' . rawurlencode( $photo_id ) . '?auto=format&fit=crop&w=' . $w . '&q=76';
	$srcset = implode( ', ', array_map( static fn ( $w ) => $url( $w ) . ' ' . $w . 'w', $widths ) );
	$attrs += [ 'sizes' => '100vw', 'loading' => 'lazy', 'decoding' => 'async' ];
	$html   = sprintf( '<img src="%s" srcset="%s" alt="%s"', esc_url( $url( $widths[ min( 1, count( $widths ) - 1 ) ] ) ), esc_attr( $srcset ), esc_attr( $alt ) );
	foreach ( $attrs as $k => $v ) {
		$html .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( (string) $v ) );
	}
	return $html . ' />';
}

/** Post image: featured image, else the approved stock stand-in its seed entry names, else nothing. */
function er_post_img( int $post_id, string $size = 'er-card', array $attrs = [] ): string {
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		return er_img( (int) $thumb, $size, $attrs );
	}
	$stock = function_exists( 'er_stock_id_for' ) ? er_stock_id_for( $post_id ) : '';
	return $stock ? er_stock_img( $stock, (string) ( $attrs['alt'] ?? '' ), array_diff_key( $attrs, [ 'alt' => 1 ] ), [ 480, 720, 960 ] ) : '';
}

/** A registered menu, or nothing (editors manage navigation under Appearance → Menus). */
function er_menu( string $location, string $wrap = '<ul>%3$s</ul>' ): string {
	if ( ! has_nav_menu( $location ) ) {
		return '';
	}
	return (string) wp_nav_menu( [
		'theme_location' => $location,
		'container'      => false,
		'items_wrap'     => $wrap,
		'depth'          => 1,
		'echo'           => false,
		'fallback_cb'    => false,
	] );
}

/** Whether a post type has at least one published entry in the current language (cached per request). */
function er_has_published( string $post_type ): bool {
	static $cache = [];
	if ( ! isset( $cache[ $post_type ] ) ) {
		$cache[ $post_type ] = (bool) get_posts( [ 'post_type' => $post_type, 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'suppress_filters' => false ] );
	}
	return $cache[ $post_type ];
}

/** Only working links: drop menu items whose target is unpublished, does not exist or is an empty archive. */
add_filter( 'wp_nav_menu_objects', static function ( $items ) {
	// Page links (/about/, /contact/ …): look up every page slug in this menu with one query.
	static $pages = [];
	$names = [];
	foreach ( $items as $item ) {
		$slug = 'custom' === $item->type && str_starts_with( (string) $item->url, home_url( '/' ) ) ? trim( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ), '/' ) : '';
		if ( '' !== $slug && ! str_contains( $slug, '/' ) && ! array_key_exists( $slug, $pages ) ) {
			$names[]        = $slug;
			$pages[ $slug ] = null;
		}
	}
	if ( $names ) {
		$found = get_posts( [ 'post_type' => 'page', 'post_name__in' => $names, 'post_parent' => 0, 'post_status' => 'any', 'numberposts' => count( $names ), 'no_found_rows' => true, 'update_post_meta_cache' => false, 'update_post_term_cache' => false, 'lang' => '' ] );
		foreach ( $found as $page ) {
			$pages[ $page->post_name ] = 'publish' === $pages[ $page->post_name ] ? 'publish' : $page->post_status;
		}
	}
	return array_filter( $items, static function ( $item ) use ( $pages ) {
		if ( 'post_type' === $item->type ) {
			$published = 'publish' === get_post_status( (int) $item->object_id );
			// The Journal (posts page, in any language) only while it has articles.
			$posts_page = (int) get_option( 'page_for_posts' );
			if ( $published && $posts_page && (int) $item->object_id === er_translated_post_id( $posts_page ) ) {
				return er_has_published( 'post' );
			}
			return $published;
		}
		$url = (string) $item->url;
		if ( 'custom' !== $item->type || ! str_starts_with( $url, home_url( '/' ) ) || str_contains( $url, '#' ) ) {
			return true; // external links and in-page anchors are the editor's call
		}
		$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
		$full = $path;
		if ( function_exists( 'pll_languages_list' ) && pll_languages_list() ) {
			// Polylang directory URLs: /fr/destinations/ is the destinations archive.
			$path = (string) preg_replace( '#^(?:' . implode( '|', array_map( 'preg_quote', pll_languages_list() ) ) . ')(?:/|$)#', '', $path );
		}
		if ( '' === $path ) {
			return true;
		}
		foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $type ) {
			if ( $type->has_archive && $path === $type->has_archive ) {
				return er_has_published( $type->name ); // no link to an archive that would only say "nothing yet"
			}
		}
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page && untrailingslashit( $url ) === untrailingslashit( (string) get_permalink( er_translated_post_id( $posts_page ) ) ) ) {
			return 'publish' === get_post_status( $posts_page ) && er_has_published( 'post' );
		}
		// The same link often sits in several menus: resolve each URL once per request.
		static $resolved = [];
		if ( ! isset( $resolved[ $url ] ) ) {
			// Known page slugs were looked up above; url_to_postid() (several queries) only for anything else.
			if ( $path === $full && isset( $pages[ $path ] ) ) {
				$resolved[ $url ] = 'publish' === $pages[ $path ];
			} else {
				$id               = url_to_postid( $url );
				$resolved[ $url ] = $id && 'publish' === get_post_status( $id );
			}
		}
		return $resolved[ $url ];
	} );
} );

/* -------------------------------------------------------------------------- */
/* Breadcrumbs                                                                 */
/* -------------------------------------------------------------------------- */

/** Trail as [ [label, url|null], … ] */
function er_breadcrumb_trail(): array {
	$trail = [ [ er_t( 'Home' ), home_url( '/' ) ] ];
	if ( is_singular() ) {
		$post = get_queried_object();
		$type = get_post_type_object( $post->post_type );
		if ( 'post' === $post->post_type && (int) get_option( 'page_for_posts' ) ) {
			$trail[] = [ get_the_title( (int) get_option( 'page_for_posts' ) ), get_permalink( (int) get_option( 'page_for_posts' ) ) ];
		} elseif ( $type && $type->has_archive ) {
			$trail[] = [ er_type_label( $post->post_type ), get_post_type_archive_link( $post->post_type ) ];
		}
		foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
			$trail[] = [ get_the_title( $ancestor ), get_permalink( $ancestor ) ];
		}
		$trail[] = [ get_the_title( $post ), null ];
	} elseif ( is_post_type_archive() ) {
		$trail[] = [ er_type_label( (string) get_query_var( 'post_type' ) ), null ];
	} elseif ( is_home() ) {
		$trail[] = [ single_post_title( '', false ), null ];
	} elseif ( is_search() ) {
		$trail[] = [ er_t( 'Search' ), null ];
	} elseif ( is_archive() ) {
		$trail[] = [ wp_strip_all_tags( get_the_archive_title() ), null ];
	}
	return $trail;
}

function er_breadcrumbs(): void {
	$trail = er_breadcrumb_trail();
	if ( count( $trail ) < 2 ) {
		return;
	}
	echo '<nav class="crumbs" aria-label="' . esc_attr( er_t( 'Breadcrumb' ) ) . '"><ol>';
	foreach ( $trail as [ $label, $url ] ) {
		echo $url
			? '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>'
			: '<li aria-current="page">' . esc_html( $label ) . '</li>';
	}
	echo '</ol></nav>';

	// BreadcrumbList data only when no SEO plugin provides it (Rank Math does).
	if ( function_exists( 'er_seo_plugin_active' ) && ! er_seo_plugin_active() ) {
		$items = [];
		foreach ( $trail as $i => [ $label, $url ] ) {
			$items[] = array_filter( [ '@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => $url ?: ( is_singular() ? get_permalink() : null ) ] );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	}
}

/** Translated plural label for our content types. */
function er_type_label( string $post_type ): string {
	$labels = [
		'er_destination' => er_t( 'Destinations' ),
		'er_tour'        => er_t( 'Tours' ),
		'er_experience'  => er_t( 'Experiences' ),
		'er_activity'    => er_t( 'Activities' ),
		'er_guide'       => er_t( 'Travel Guide' ),
		'post'           => er_t( 'Journal' ),
	];
	if ( isset( $labels[ $post_type ] ) ) {
		return $labels[ $post_type ];
	}
	$obj = get_post_type_object( $post_type );
	return $obj ? $obj->labels->name : '';
}

/* -------------------------------------------------------------------------- */
/* Page hero (dark band, keeps the transparent-nav look of the homepage)       */
/* -------------------------------------------------------------------------- */

function er_page_hero( array $args ): void {
	$args += [ 'eyebrow' => '', 'title' => '', 'intro' => '', 'image' => 0, 'stock' => '', 'meta' => '' ];
	$media = '';
	if ( $args['image'] ) {
		$media = er_img( (int) $args['image'], 'er-hero', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'alt' => '' ] );
	} elseif ( $args['stock'] ) {
		$media = er_stock_img( (string) $args['stock'], '', [ 'loading' => 'eager', 'fetchpriority' => 'high' ] );
	}
	?>
	<div class="page-hero on-dark<?php echo $media ? ' page-hero--image' : ''; ?>">
		<?php if ( $media ) : ?>
			<div class="page-hero__media" aria-hidden="true">
				<?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by wp_get_attachment_image / er_stock_img ?>
			</div>
		<?php endif; ?>
		<div class="container page-hero__inner">
			<?php er_breadcrumbs(); ?>
			<?php if ( $args['eyebrow'] ) : ?>
				<p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h1 class="page-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['intro'] ) : ?>
				<p class="page-hero__intro"><?php echo esc_html( $args['intro'] ); ?></p>
			<?php endif; ?>
			<?php if ( $args['meta'] ) : ?>
				<div class="page-hero__meta"><?php echo $args['meta']; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts by callers ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------- */
/* Cards                                                                        */
/* -------------------------------------------------------------------------- */

/** Editorial card for grids (destinations, tours, experiences, activities, guides, articles). */
function er_card( int $post_id, string $placement = 'card', string $heading = 'h3' ): string {
	$heading  = in_array( $heading, [ 'h2', 'h3' ], true ) ? $heading : 'h3';
	$type     = get_post_type( $post_id );
	$url      = get_permalink( $post_id );
	$title    = get_the_title( $post_id );
	$location = (string) get_post_meta( $post_id, 'er_destination' === $type ? '_er_region_label' : '_er_location', true );
	$duration = (string) get_post_meta( $post_id, '_er_duration', true );
	$badge    = (string) get_post_meta( $post_id, '_er_badge', true );
	$image    = er_post_img( $post_id, 'er-card', [ 'sizes' => '(max-width: 700px) 90vw, 360px', 'alt' => '' ] );
	$offers   = function_exists( 'er_offers_for_post' ) && in_array( $type, [ 'er_tour', 'er_experience', 'er_activity' ], true ) ? er_offers_for_post( $post_id, 1 ) : [];

	ob_start();
	?>
	<li class="card" data-reveal>
		<a href="<?php echo esc_url( $url ); ?>" class="media media--hover" tabindex="-1" aria-hidden="true">
			<?php echo $image ?: '<span class="media__empty"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
		<div class="card__top">
			<?php echo $badge ? '<span class="chip chip--gold card__badge">' . esc_html( $badge ) . '</span>' : '<span></span>'; ?>
		</div>
		<div class="card__body">
			<?php if ( $location ) : ?>
				<span class="card__loc"><?php echo er_icon( 'i-pin', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $location ); ?></span>
			<?php elseif ( in_array( $type, [ 'er_guide', 'post' ], true ) ) : ?>
				<span class="card__loc"><?php echo esc_html( er_t( '{n} min read', [ 'n' => function_exists( 'er_read_minutes' ) ? er_read_minutes( $post_id ) : 1 ] ) ); ?></span>
			<?php endif; ?>
			<<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput -- whitelisted ?> class="card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<?php if ( $duration ) : ?>
				<div class="card__meta"><span><?php echo er_icon( 'i-clock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $duration ); ?></span></div>
			<?php elseif ( has_excerpt( $post_id ) && in_array( $type, [ 'er_destination', 'er_guide', 'post', 'er_activity' ], true ) ) : ?>
				<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 22, '…' ) ); ?></p>
			<?php endif; ?>
			<div class="card__foot">
				<span></span>
				<?php
				if ( $offers ) {
					echo er_offer_cta_html( $offers[0], [ 'placement' => $placement, 'class' => 'link', 'icon' => ' ' . er_icon( 'i-arrow-ur', 'icon--sm' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- built with esc_* in the plugin
				} else {
					printf( '<a class="link" href="%s">%s %s</a>', esc_url( $url ), esc_html( er_t( 'Discover' ) ), er_icon( 'i-arrow', 'icon--sm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		</div>
	</li>
	<?php
	return (string) ob_get_clean();
}

/** Grid of cards. */
function er_card_grid( array $post_ids, string $placement = 'card', string $heading = 'h3' ): void {
	if ( ! $post_ids ) {
		return;
	}
	echo '<ul class="grid-cards">';
	foreach ( $post_ids as $id ) {
		echo er_card( (int) $id, $placement, $heading ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside
	}
	echo '</ul>';
}

/* -------------------------------------------------------------------------- */
/* Affiliate offers block (conversion layer under editorial content)           */
/* -------------------------------------------------------------------------- */

function er_offer_rows( array $offer_ids, string $placement ): void {
	if ( ! $offer_ids || ! function_exists( 'er_offer_data' ) ) {
		return;
	}
	$source = (int) get_queried_object_id();
	echo '<div class="offers">';
	foreach ( $offer_ids as $offer_id ) {
		$o = er_offer_data( (int) $offer_id, $placement, $source );
		?>
		<article class="offer">
			<div class="offer__img">
				<?php echo $o['image_id'] ? er_img( $o['image_id'], 'medium', [ 'alt' => '' ] ) : '<span class="media__empty"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $o['badge'] ) : ?>
					<span class="chip chip--gold"><?php echo esc_html( $o['badge'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="offer__body">
				<?php if ( $o['location'] ) : ?>
					<span class="offer__loc"><?php echo er_icon( 'i-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $o['location'] ); ?></span>
				<?php endif; ?>
				<h3 class="offer__name"><?php echo esc_html( $o['title'] ); ?></h3>
				<div class="offer__meta">
					<?php if ( $o['meta'] ) : ?>
						<span><?php echo esc_html( $o['meta'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="offer__side">
				<?php if ( $o['price_text'] ) : ?>
					<p class="price"><?php er_e( 'from' ); ?><b><?php echo esc_html( $o['price_text'] ); ?> <small>/ <?php echo esc_html( $o['unit_text'] ); ?></small></b></p>
					<span class="via"><?php echo esc_html( er_t( 'Price checked {date}', [ 'date' => date_i18n( get_option( 'date_format' ), strtotime( $o['price']['checked'] ) ) ] ) ); ?></span>
				<?php endif; ?>
				<?php echo er_offer_cta_html( (int) $offer_id, [ 'placement' => $placement, 'source' => $source, 'class' => 'btn btn--outline btn--sm', 'icon' => ' ' . er_icon( 'i-arrow-ur', 'icon--sm' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $o['provider'] ) : ?>
					<span class="via"><?php echo esc_html( er_t( 'via {partner}', [ 'partner' => $o['provider'] ] ) ); ?></span>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}
	echo '</div>';
}

/** Disclosure line with the shield icon, next to every affiliate block. */
function er_disclosure(): void {
	if ( ! function_exists( 'er_disclosure_html' ) ) {
		return;
	}
	$html = er_disclosure_html( 'disclosure' );
	if ( $html ) {
		// Icon + one text span, so the policy link flows inline with the sentence.
		$html = preg_replace( '#^<p class="disclosure">(.*)</p>$#s', '<p class="disclosure">' . er_icon( 'i-shield', 'icon--sm' ) . '<span>$1</span></p>', $html );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in plugin
	}
}

/* -------------------------------------------------------------------------- */
/* Language switcher (Polylang) — real links, crawlable, no JS required        */
/* -------------------------------------------------------------------------- */

function er_languages(): array {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return [];
	}
	$langs = pll_the_languages( [ 'raw' => 1, 'hide_if_empty' => 1, 'hide_if_no_translation' => 0 ] );
	return is_array( $langs ) && count( $langs ) > 1 ? $langs : [];
}

function er_lang_switcher( string $variant = 'desktop' ): void {
	$langs = er_languages();
	if ( ! $langs ) {
		return;
	}
	$current = null;
	foreach ( $langs as $l ) {
		if ( ! empty( $l['current_lang'] ) ) {
			$current = $l;
		}
	}
	$short = static fn ( $slug ) => [ 'zh' => '中', 'ar' => 'ع' ][ $slug ] ?? strtoupper( $slug );
	if ( 'mobile' === $variant ) {
		echo '<div class="menu__lang" role="group" aria-label="' . esc_attr( er_t( 'Language' ) ) . '">' . er_icon( 'i-globe' ); // phpcs:ignore WordPress.Security.EscapeOutput
		foreach ( $langs as $l ) {
			printf(
				'<a href="%1$s" hreflang="%2$s" lang="%2$s" data-lang-url="%3$s"%4$s>%5$s</a>',
				esc_url( $l['url'] ),
				esc_attr( str_replace( '_', '-', $l['locale'] ) ),
				esc_attr( $l['slug'] ),
				! empty( $l['current_lang'] ) ? ' aria-current="true"' : '',
				esc_html( $l['name'] )
			);
		}
		echo '</div>';
		return;
	}
	?>
	<div class="lang" data-lang>
		<button class="lang__toggle" type="button" aria-expanded="false" aria-label="<?php echo esc_attr( er_t( 'Language' ) ); ?>">
			<?php echo er_icon( 'i-globe' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span data-lang-current><?php echo esc_html( $short( $current['slug'] ?? er_lang() ) ); ?></span>
			<?php echo er_icon( 'i-chevron', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</button>
		<ul class="lang__menu" aria-label="<?php echo esc_attr( er_t( 'Language' ) ); ?>">
			<?php foreach ( $langs as $l ) : ?>
				<li lang="<?php echo esc_attr( str_replace( '_', '-', $l['locale'] ) ); ?>"<?php echo 'ar' === $l['slug'] ? ' dir="rtl"' : ''; ?>>
					<a href="<?php echo esc_url( $l['url'] ); ?>" hreflang="<?php echo esc_attr( str_replace( '_', '-', $l['locale'] ) ); ?>" data-lang-url="<?php echo esc_attr( $l['slug'] ); ?>" data-value="<?php echo esc_attr( $l['slug'] ); ?>"<?php echo ! empty( $l['current_lang'] ) ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $l['name'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/** Status notice for forms that post back (?er=…). */
function er_form_notice( string $form ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display state only
	$state = isset( $_GET['er'] ) ? sanitize_key( wp_unslash( $_GET['er'] ) ) : '';
	$messages = [
		'newsletter' => [
			'subscribed-pending' => [ 'ok', er_t( 'Thank you — please check your inbox to confirm your subscription.' ) ],
			'subscribed'         => [ 'ok', er_t( "You're on the list — first letter arrives next month." ) ],
			'subscribe-invalid' => [ 'error', er_t( 'Please enter a valid email address.' ) ],
			'subscribe-error'   => [ 'error', er_t( 'Sorry, that did not work. Please try again in a moment.' ) ],
		],
	];
	if ( isset( $messages[ $form ][ $state ] ) ) {
		[ $kind, $text ] = $messages[ $form ][ $state ];
		printf( '<p class="form-notice form-notice--%s" role="%s">%s</p>', esc_attr( $kind ), 'ok' === $kind ? 'status' : 'alert', esc_html( $text ) );
	}
}

/** Lines field → escaped <li> list with check icons. */
function er_check_list( array $lines ): string {
	if ( ! $lines ) {
		return '';
	}
	$out = '<ul class="checks">';
	foreach ( $lines as $line ) {
		$out .= '<li>' . er_icon( 'i-check' ) . '<span>' . esc_html( $line ) . '</span></li>';
	}
	return $out . '</ul>';
}
