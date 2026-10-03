<?php
/**
 * WP-CLI: migrate the static prototype's content and run health checks.
 *
 *   wp egypt-roamer seed [--with-images] [--translations [--publish-translations]]
 *   wp egypt-roamer languages
 *   wp egypt-roamer editorial [--dry-run]   (English bodies/excerpts from data/editorial/en, never overwrites edits)
 *   wp egypt-roamer health
 *
 * Seeding is idempotent (records are matched on _er_seed_id) and conservative:
 * - destinations, experiences and travel styles are published but NOT indexable
 *   (their copy is short — editors expand it, then tick "Ready to index");
 * - guides have titles only, so they are created as drafts;
 * - the prototype's partner shortlists become DRAFT offers without provider or
 *   URL — they cannot go live until a real affiliate link is added;
 * - no prices, ratings, review counts or booking claims are imported;
 * - legal/trust pages are drafts with an editorial note, never published copy.
 */

defined( 'ABSPATH' ) || exit;

class ER_CLI {

	/** @var array */
	private $seed;

	private function seed_data(): array {
		if ( null === $this->seed ) {
			$json       = file_get_contents( ER_CORE_DIR . 'data/seed.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$this->seed = json_decode( (string) $json, true ) ?: [];
		}
		return $this->seed;
	}

	private function find( string $type, string $seed_id ): int {
		$ids = get_posts( [ 'post_type' => $type, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_er_seed_id', 'meta_value' => $seed_id, 'lang' => '' ] );
		return $ids ? (int) $ids[0] : 0;
	}

	private function upsert( string $type, string $seed_id, array $post, array $meta = [] ): int {
		$id       = $this->find( $type, $seed_id );
		$post     = array_merge( [ 'post_type' => $type ], $post );
		$existing = (bool) $id;
		if ( $existing ) {
			// Re-runs (e.g. `seed --translations` after launch) must never overwrite what editors
			// wrote: existing items keep their title, content, status and every meta value they have.
		} else {
			$id = (int) wp_insert_post( wp_slash( $post ), true );
			if ( ! $id || is_wp_error( $id ) ) {
				WP_CLI::warning( "Could not create {$type} {$seed_id}" );
				return 0;
			}
			update_post_meta( $id, '_er_seed_id', $seed_id );
		}
		foreach ( $meta as $key => $value ) {
			if ( $existing && metadata_exists( 'post', $id, $key ) ) {
				continue; // only fill fields that are still missing
			}
			if ( is_array( $value ) ) {
				delete_post_meta( $id, $key );
				foreach ( $value as $v ) {
					add_post_meta( $id, $key, $v );
				}
			} elseif ( '' !== $value && null !== $value ) {
				update_post_meta( $id, $key, $value );
			}
		}
		return $id;
	}

	/**
	 * Slug for a new translation. Latin-script titles get an ASCII slug (le-caire, alejandria);
	 * other scripts keep their own (Cyrillic, Chinese, Arabic). Post slugs are unique per post type
	 * across all languages, so a slug already taken ("luxor", "siwa") becomes "luxor-it" rather than
	 * WordPress's "luxor-2".
	 */
	private function translation_slug( string $title, string $type, string $lang ): string {
		global $wpdb;
		$latin = ! preg_match( '/[^\p{Latin}\P{L}]/u', $title ); // every letter is Latin script (symbols like × are fine)
		$slug  = $latin ? $this->slug( $title ) : sanitize_title( $title );
		$taken = static fn ( $s ) => (bool) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s LIMIT 1", $type, $s ) ); // phpcs:ignore WordPress.DB
		if ( '' === $slug ) {
			return $lang;
		}
		return $taken( $slug ) ? $slug . '-' . $lang : $slug;
	}

	/** ASCII slug: arrows, middots and symbols in titles must not leak into URLs. */
	private function slug( string $title ): string {
		return sanitize_title( preg_replace( '/[^\x20-\x7E]+/', ' ', remove_accents( $title ) ) );
	}

	/** Download an Unsplash photo into the Media Library with a descriptive filename and alt text. */
	private function sideload( string $photo_id, string $title, int $parent = 0 ): int {
		if ( '' === $photo_id ) {
			return 0; // a sample whose photo was withdrawn (unverified location)
		}
		$existing = get_posts( [ 'post_type' => 'attachment', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_er_unsplash_id', 'meta_value' => $photo_id ] );
		if ( $existing ) {
			return (int) $existing[0];
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = download_url( 'https://images.unsplash.com/photo-' . rawurlencode( $photo_id ) . '?auto=format&fit=crop&w=2400&q=80&fm=jpg', 60 );
		if ( is_wp_error( $tmp ) ) {
			WP_CLI::warning( "Image {$photo_id}: " . $tmp->get_error_message() );
			return 0;
		}
		$file = [ 'name' => sanitize_title( $title ) . '.jpg', 'tmp_name' => $tmp ];
		$id   = media_handle_sideload( $file, $parent, $title );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			WP_CLI::warning( "Image {$photo_id}: " . $id->get_error_message() );
			return 0;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $title );
		update_post_meta( $id, '_er_unsplash_id', $photo_id );
		update_post_meta( $id, '_er_credit', 'Unsplash (photo ' . $photo_id . ')' );
		return (int) $id;
	}

	/**
	 * Import the static prototype's content.
	 *
	 * [--with-images]
	 * : Download the prototype's Unsplash photos into the Media Library.
	 *
	 * [--translations]
	 * : With Polylang active, create draft translations from the prototype's locale files (review before publishing).
	 *
	 * [--publish-translations]
	 * : With --translations: publish new destination and experience translations instead of saving drafts.
	 *   Only for copy taken verbatim from the static prototype's locale files (the approved reference).
	 *   Like the English items they stay noindex until "Ready to index" is ticked. Existing translations
	 *   keep their status (re-runs never change editors' work).
	 *
	 * @when after_wp_load
	 */
	public function seed( $args, $assoc ) {
		$s      = $this->seed_data();
		$images = ! empty( $assoc['with-images'] );
		$dest   = [];

		foreach ( $s['destinations'] as $i => $d ) {
			$id = $this->upsert( 'er_destination', 'dest-' . $d['id'], [
				'post_title'   => $d['name'],
				'post_name'    => $d['id'],
				'post_excerpt' => $d['desc'],
				'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $d['desc'] ) . '</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
				'menu_order'   => array_search( $d['id'], $s['routeOrder'], true ) ?: $i,
			], [
				'_er_tagline'       => $d['tagline'],
				'_er_region_label'  => $d['region'],
				'_er_best_time'     => $d['best'],
				'_er_getting_there' => $d['reach'],
				'_er_map_reach'     => $d['mapReach'],
				'_er_highlights'    => implode( "\n", $d['highlights'] ),
				'_er_lng'           => $d['coords'][0],
				'_er_lat'           => $d['coords'][1],
				'_er_nights'        => $s['nightWeights'][ $d['id'] ] ?? 2,
			] );
			$dest[ $d['id'] ] = $id;
			if ( $images && $id && ! has_post_thumbnail( $id ) ) {
				$img = $this->sideload( $d['image'], $d['name'] . ', Egypt', $id );
				$img && set_post_thumbnail( $id, $img );
			}
		}
		WP_CLI::log( 'Destinations: ' . count( $dest ) );

		// Travel styles (homepage moods).
		foreach ( $s['moods'] as $i => $m ) {
			$term = term_exists( $m['id'], 'er_travel_style' ) ?: wp_insert_term( $m['label'], 'er_travel_style', [ 'slug' => $m['id'], 'description' => $m['desc'] ] );
			if ( is_wp_error( $term ) ) {
				continue;
			}
			$tid  = (int) $term['term_id'];
			$tint = preg_replace( '/^var\(--(\w+)\)$/', '$1', $m['tint'] );
			update_term_meta( $tid, '_er_word', $m['label'] );
			update_term_meta( $tid, '_er_icon', $m['icon'] );
			update_term_meta( $tid, '_er_tint', $tint );
			update_term_meta( $tid, '_er_order', $i );
			if ( ! empty( $dest[ $m['dest'] ] ) ) {
				update_term_meta( $tid, '_er_destination', $dest[ $m['dest'] ] );
			}
			if ( $images && ! get_term_meta( $tid, '_er_image', true ) ) {
				$img = $this->sideload( $m['image'], $m['label'] . ' Egypt' );
				$img && update_term_meta( $tid, '_er_image', $img );
			}
		}
		WP_CLI::log( 'Travel styles: ' . count( $s['moods'] ) );

		// Experiences (no prices/ratings/partners — those belong to offers with real links).
		$exp_dest = [ 'exp-giza' => 'cairo', 'exp-dinner' => 'cairo', 'exp-abu' => 'aswan', 'exp-dive' => 'hurghada', 'exp-food' => 'cairo', 'exp-valley' => 'luxor', 'exp-siwa' => 'siwa' ];
		$exps     = [];
		foreach ( $s['experiences'] as $i => $x ) {
			$id = $this->upsert( 'er_experience', $x['id'], [
				'post_title'  => $x['title'],
				'post_name'   => $this->slug( $x['title'] ),
				'post_status' => 'publish',
				'menu_order'  => $i,
			], [
				'_er_location'    => $x['location'],
				'_er_duration'    => $x['duration'],
				'_er_destination' => isset( $exp_dest[ $x['id'] ], $dest[ $exp_dest[ $x['id'] ] ] ) ? [ $dest[ $exp_dest[ $x['id'] ] ] ] : [],
				'_er_badge'       => $x['badge'] ?? '',
			] );
			$exps[ $x['id'] ] = $id;
			if ( $images && $id && ! has_post_thumbnail( $id ) ) {
				$img = $this->sideload( $x['image'], $x['title'], $id );
				$img && set_post_thumbnail( $id, $img );
			}
		}
		WP_CLI::log( 'Experiences: ' . count( $exps ) );

		// Guides: titles only in the prototype → drafts.
		foreach ( $s['guides'] as $i => $g ) {
			$topic = term_exists( sanitize_title( $g['cat'] ), 'er_guide_topic' ) ?: wp_insert_term( $g['cat'], 'er_guide_topic' );
			$id    = $this->upsert( 'er_guide', $g['id'], [
				'post_title'   => $g['title'],
				'post_name'    => $g['slug'],
				'post_excerpt' => $g['excerpt'] ?? '',
				'post_status'  => 'draft',
				'menu_order'   => $i,
			] );
			if ( $id && ! is_wp_error( $topic ) ) {
				wp_set_object_terms( $id, (int) $topic['term_id'], 'er_guide_topic' );
			}
			if ( $images && $id && ! has_post_thumbnail( $id ) ) {
				$img = $this->sideload( $g['image'], $g['title'], $id );
				$img && set_post_thumbnail( $id, $img );
			}
		}
		WP_CLI::log( 'Guides (draft): ' . count( $s['guides'] ) );

		// Partner shortlists → draft offers, no provider/URL/price.
		$n = 0;
		foreach ( $s['partnerCategories'] as $cat ) {
			$term = get_term_by( 'slug', $cat['id'], 'er_offer_type' );
			if ( $term ) {
				update_term_meta( $term->term_id, '_er_icon', $cat['icon'] );
				update_term_meta( $term->term_id, '_er_headline', $cat['headline'] );
				update_term_meta( $term->term_id, '_er_copy', $cat['copy'] );
				update_term_meta( $term->term_id, '_er_compare_label', $cat['compare'] );
			}
			foreach ( $cat['items'] as $k => $o ) {
				$id = $this->upsert( 'er_offer', $cat['id'] . '-' . $k, [
					'post_title'  => $o['name'],
					'post_name'   => $this->slug( $o['name'] ),
					'post_status' => 'draft',
				], [
					'_er_location'  => $o['location'],
					'_er_meta_line' => $o['meta'],
					'_er_badge'     => $o['badge'] ?? '',
					'_er_status'    => 'paused',
					'_er_priority'  => 10 - $k,
					'_er_price_unit' => in_array( $cat['id'], [ 'hotels' ], true ) ? 'night' : ( 'transfers' === $cat['id'] ? 'car' : ( 'cars' === $cat['id'] ? 'day' : 'person' ) ),
					'_er_seed_partner_note' => 'Prototype named partner: ' . $o['partner'] . ' (unverified — add a real provider and link)',
				] );
				if ( $id && $term ) {
					wp_set_object_terms( $id, (int) $term->term_id, 'er_offer_type' );
				}
				++$n;
			}
		}
		WP_CLI::log( "Offer drafts (paused, no link): {$n}" );

		$this->remove_wordpress_samples();
		$this->pages();
		$this->menus( $dest );

		if ( ! empty( $assoc['translations'] ) ) {
			$this->translations( $dest, $exps, ! empty( $assoc['publish-translations'] ) );
		}
		flush_rewrite_rules();
		WP_CLI::success( 'Seed complete. Review drafts, add real affiliate providers/offers, then tick “Ready to index” page by page.' );
	}

	/** WordPress's install placeholders ("Sample Page", "Hello world!") are not content: trash them if untouched. */
	private function remove_wordpress_samples(): void {
		foreach ( [ [ 'sample-page', 'page', 'This is an example page' ], [ 'hello-world', 'post', 'Welcome to WordPress' ] ] as [ $slug, $type, $marker ] ) {
			$p = get_page_by_path( $slug, OBJECT, $type );
			if ( $p && 'trash' !== $p->post_status && str_contains( $p->post_content, $marker ) ) {
				wp_trash_post( $p->ID );
				WP_CLI::log( "Trashed WordPress placeholder: {$slug}" );
			}
		}
	}

	/** Trust & legal pages as drafts with an editorial note — never auto-published. */
	private function pages(): void {
		$note  = static fn ( $text ) => '<!-- wp:paragraph {"className":"er-editorial-note"} --><p class="er-editorial-note"><strong>Editorial draft — not published.</strong> ' . esc_html( $text ) . '</p><!-- /wp:paragraph -->';
		$pages = [
			'about'                => [ 'Our Story', $note( 'Write who runs Egypt Roamer, why, and how you research. Do not invent credentials.' ) ],
			'how-we-choose'        => [ 'How We Choose', $note( 'Explain how experiences and partners are selected and how affiliate relationships never change a recommendation.' ) ],
			'partner-with-us'      => [ 'Partner With Us', $note( 'Explain what kind of partnerships you accept and how to reach you.' ) ],
			'contact'              => [ 'Contact', $note( 'Add a short intro. The form below works: messages are stored under Egypt Roamer → Contact messages and emailed to the address in Settings.' ) . "\n<!-- wp:shortcode -->[er_contact_form]<!-- /wp:shortcode -->" ],
			'faq'                  => [ 'FAQ', $note( 'Add real questions travellers ask you (booking happens with partners, cancellations are handled by the partner, etc.).' ) ],
			'affiliate-disclosure' => [ 'Affiliate Disclosure', $note( 'Review with your adviser before publishing.' ) . "\n<!-- wp:paragraph --><p>Egypt Roamer is an independent travel publication. Some links on this site are affiliate links: when you book through them, the booking partner may pay us a commission, at no extra cost to you.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>We do not sell travel ourselves. Availability, prices, payment, changes and cancellations are handled by the partner you book with, under their terms.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Affiliate links are marked for search engines with rel=\"sponsored\", and we show a short notice next to partner offers. Commission never influences what we recommend.</p><!-- /wp:paragraph -->" ],
			'terms'                => [ 'Terms of Use', $note( 'Add your terms of use (have them reviewed).' ) ],
			'cookies'              => [ 'Cookie Policy', $note( 'List the cookies set by the consent tool, Google Tag Manager/GA4 and any embedded services.' ) ],
		];
		$ids = [];
		foreach ( $pages as $slug => [ $title, $content ] ) {
			$existing = get_page_by_path( $slug );
			$ids[ $slug ] = $existing ? $existing->ID : (int) wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'draft', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content ] );
		}
		// Static front page + "Journal" posts page, so articles live at /journal/{slug}/.
		if ( 'page' !== get_option( 'show_on_front' ) ) {
			$front   = get_page_by_path( 'home' );
			$journal = get_page_by_path( 'journal' );
			$front   = $front ? $front->ID : (int) wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'home', 'post_title' => 'Home' ] );
			$journal = $journal ? $journal->ID : (int) wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'journal', 'post_title' => 'Journal', 'post_excerpt' => 'Stories, tips and honest advice for travelling in Egypt.' ] );
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front );
			update_option( 'page_for_posts', $journal );
			WP_CLI::log( 'Reading settings: static front page + Journal posts page.' );
		}
		// Repeated slashes are collapsed first: production had "//%postname%/", the default in disguise,
		// which was left in place and would double the slash in every article URL.
		$structure = (string) preg_replace( '#/{2,}#', '/', (string) get_option( 'permalink_structure' ) );
		if ( in_array( $structure, [ '', '/', '/%postname%/' ], true ) ) {
			update_option( 'permalink_structure', '/journal/%postname%/' );
			WP_CLI::log( 'Permalinks: articles at /journal/{slug}/ (content types keep /destinations/, /tours/ …).' );
		}

		$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $privacy || ! get_post( $privacy ) ) {
			$privacy = (int) wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'draft', 'post_name' => 'privacy-policy', 'post_title' => 'Privacy Policy', 'post_content' => $note( 'Write your privacy policy (Settings → Privacy offers a guide). Cover: newsletter and contact data, Google Tag Manager/GA4, the consent tool, and that affiliate partners process bookings under their own policies.' ) ] );
			update_option( 'wp_page_for_privacy_policy', $privacy );
		}
		$settings = get_option( 'er_settings', [] );
		$settings = is_array( $settings ) ? $settings : [];
		$settings += [ 'disclosure_page' => $ids['affiliate-disclosure'], 'privacy_page' => $privacy ];
		update_option( 'er_settings', $settings, false );
		WP_CLI::log( 'Trust & legal pages (draft): ' . count( $ids ) );
	}

	/** Menus for the theme's locations, built from archives and pages. */
	private function menus( array $dest ): void {
		$make = static function ( string $name, array $items ) {
			$menu = wp_get_nav_menu_object( $name );
			if ( $menu ) {
				return (int) $menu->term_id;
			}
			$menu_id = wp_create_nav_menu( $name );
			foreach ( $items as [ $title, $url ] ) {
				wp_update_nav_menu_item( $menu_id, 0, [ 'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ] );
			}
			return (int) $menu_id;
		};
		$home      = home_url( '/' );
		$locations = get_theme_mod( 'nav_menu_locations', [] );
		$locations['primary'] = $make( 'Primary', [
			[ 'Destinations', $home . 'destinations/' ],
			[ 'Experiences', $home . 'experiences/' ],
			[ 'Tours', $home . 'tours/' ],
			[ 'Activities', $home . 'activities/' ],
			[ 'Travel Guide', $home . 'guides/' ],
		] );
		$locations['footer_explore'] = $make( 'Footer — Explore', [
			[ 'Destinations', $home . 'destinations/' ],
			[ 'Experiences', $home . 'experiences/' ],
			[ 'Tours', $home . 'tours/' ],
			[ 'Activities', $home . 'activities/' ],
			[ 'Travel Guide', $home . 'guides/' ],
			[ 'Journal', $home . 'journal/' ],
		] );
		$locations['footer_plan'] = $make( 'Footer — Plan', [
			[ 'Trip Builder', $home . '#planner' ],
			[ 'Best Time to Visit', $home . 'guides/best-time-to-visit-egypt/' ],
			[ 'Egypt Travel Costs', $home . 'guides/egypt-travel-costs/' ],
		] );
		$locations['footer_company'] = $make( 'Footer — Egypt Roamer', [
			[ 'Our Story', $home . 'about/' ],
			[ 'How We Choose', $home . 'how-we-choose/' ],
			[ 'Partner With Us', $home . 'partner-with-us/' ],
			[ 'Affiliate Disclosure', $home . 'affiliate-disclosure/' ],
			[ 'Contact', $home . 'contact/' ],
		] );
		$locations['legal'] = $make( 'Legal', [
			[ 'Privacy', $home . 'privacy-policy/' ],
			[ 'Terms', $home . 'terms/' ],
			[ 'Cookies', $home . 'cookies/' ],
		] );
		set_theme_mod( 'nav_menu_locations', $locations );
		if ( function_exists( 'pll_default_language' ) && pll_default_language() ) {
			$this->polylang_menu_locations( [ pll_default_language() => $locations ] );
		}
		WP_CLI::log( 'Menus: 5 (links to unpublished pages resolve once those pages are published)' );
	}

	/** The theme's translations for a locale (WP-CLI does not load theme text domains per locale). */
	private function theme_messages( string $locale ): array {
		$file = get_template_directory() . '/languages/' . $locale . '.l10n.php';
		return is_readable( $file ) ? (array) ( ( include $file )['messages'] ?? [] ) : [];
	}

	/**
	 * Polylang keeps menu locations per language and ignores the theme's own locations,
	 * so without this every menu renders empty once Polylang is active. Existing
	 * assignments made in Appearance → Menus are kept.
	 */
	private function polylang_menu_locations( array $by_lang ): void {
		if ( ! function_exists( 'PLL' ) || ! isset( PLL()->options ) ) {
			return;
		}
		$theme = get_stylesheet();
		$menus = PLL()->options['nav_menus'];
		$menus = is_array( $menus ) ? $menus : [];
		foreach ( $by_lang as $lang => $locations ) {
			foreach ( $locations as $location => $menu_id ) {
				$current = (int) ( $menus[ $theme ][ $location ][ $lang ] ?? 0 );
				if ( $menu_id && ( ! $current || ! wp_get_nav_menu_object( $current ) ) ) {
					$menus[ $theme ][ $location ][ $lang ] = (int) $menu_id;
				}
			}
		}
		PLL()->options['nav_menus'] = $menus;
		PLL()->options->save();
	}

	/**
	 * One menu per language and location, mirroring the default-language menus:
	 * archive links point to the language's own archive (label from the theme's translations),
	 * page links point to the page's translation (label = that page's title). Items with no
	 * translation are left out rather than shown in English.
	 */
	private function language_menus( array $languages, string $default ): void {
		$base   = (array) get_theme_mod( 'nav_menu_locations', [] );
		$home   = home_url( '/' );
		$assign = [ $default => $base ];
		foreach ( $languages as $lang ) {
			if ( $lang === $default ) {
				continue;
			}
			$language  = PLL()->model->get_language( $lang );
			$lang_home = trailingslashit( pll_home_url( $lang ) );
			$messages  = $this->theme_messages( $language->locale );
			foreach ( $base as $location => $menu_id ) {
				if ( str_starts_with( (string) $location, 'footer_' ) ) {
					continue; // the theme renders the default-language footer menus, localized, in every language
				}
				$menu = $menu_id ? wp_get_nav_menu_object( (int) $menu_id ) : null;
				if ( ! $menu ) {
					continue;
				}
				$name = $menu->name . ' (' . $lang . ')';
				if ( wp_get_nav_menu_object( $name ) ) {
					$assign[ $lang ][ $location ] = (int) wp_get_nav_menu_object( $name )->term_id;
					continue;
				}
				$items = [];
				foreach ( (array) wp_get_nav_menu_items( $menu ) as $item ) {
					$target = 'post_type' === $item->type ? (int) $item->object_id : 0;
					if ( ! $target && untrailingslashit( (string) $item->url ) === untrailingslashit( (string) get_permalink( (int) get_option( 'page_for_posts' ) ) ) ) {
						$target = (int) get_option( 'page_for_posts' );
					} elseif ( ! $target && untrailingslashit( (string) $item->url ) !== untrailingslashit( $home ) ) {
						$target = url_to_postid( (string) $item->url );
						$target = (int) get_option( 'page_on_front' ) === $target ? 0 : $target;
					}
					if ( $target ) {
						$tr = (int) pll_get_post( $target, $lang );
						if ( $tr ) {
							$items[] = [ 'menu-item-type' => 'post_type', 'menu-item-object' => get_post_type( $tr ), 'menu-item-object-id' => $tr ];
						}
						continue;
					}
					$url = (string) $item->url;
					if ( ! str_starts_with( $url, $home ) || str_contains( $url, '#' ) ) {
						continue; // external links and anchors: the editor decides per language
					}
					$title = $messages[ $item->title ] ?? ( str_starts_with( $language->locale, 'en' ) ? $item->title : '' );
					if ( '' === $title ) {
						continue; // no translation for this label
					}
					$items[] = [ 'menu-item-type' => 'custom', 'menu-item-title' => $title, 'menu-item-url' => $lang_home . substr( $url, strlen( $home ) ) ];
				}
				if ( ! $items ) {
					continue;
				}
				$new_id = wp_create_nav_menu( $name );
				foreach ( $items as $args ) {
					wp_update_nav_menu_item( $new_id, 0, $args + [ 'menu-item-status' => 'publish' ] );
				}
				$assign[ $lang ][ $location ] = (int) $new_id;
			}
		}
		$this->polylang_menu_locations( $assign );
		WP_CLI::log( 'Menus per language: ' . ( count( $assign ) - 1 ) . ' languages (items without a translation are left out).' );
	}

	/** Draft translations from the prototype's locale files, linked in Polylang. */
	private function translations( array $dest, array $exps, bool $publish = false ): void {
		if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_save_post_translations' ) ) {
			WP_CLI::warning( 'Polylang is not active — skipping translations.' );
			return;
		}
		$s         = $this->seed_data();
		$languages = pll_languages_list( [ 'fields' => 'slug' ] );
		$default   = pll_default_language( 'slug' );
		// Content created before Polylang has no language and would vanish from language-filtered
		// queries and sitemaps: give every untagged translatable post the default language.
		$untagged = get_posts( [ 'post_type' => pll_languages_list() ? array_values( PLL()->model->get_translated_post_types() ) : [], 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '' ] );
		foreach ( $untagged as $post_id ) {
			pll_get_post_language( $post_id ) || pll_set_post_language( $post_id, $default );
		}
		// Display taxonomies: default language for untagged terms, then translated terms
		// (travel styles = homepage moods, offer categories = partner tabs), linked in Polylang.
		foreach ( [ 'er_travel_style' => 'moods', 'er_offer_type' => 'partners', 'er_guide_topic' => '', 'er_region' => '' ] as $tax => $key ) {
			foreach ( get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false, 'lang' => '' ] ) as $term ) {
				if ( ! pll_get_term_language( $term->term_id ) ) {
					pll_set_term_language( $term->term_id, $default );
				}
				if ( ! $key || pll_get_term_language( $term->term_id ) !== $default ) {
					continue;
				}
				$group = pll_get_term_translations( $term->term_id ) ?: [ $default => $term->term_id ];
				foreach ( $languages as $lang ) {
					$tr = $s['translations'][ $lang ][ $key ][ $term->slug ] ?? null;
					if ( $lang === $default || isset( $group[ $lang ] ) || ! $tr ) {
						continue;
					}
					$new = wp_insert_term( $tr['label'] ?? $term->name, $tax, [ 'slug' => $term->slug . '-' . $lang, 'description' => $tr['desc'] ?? $term->description ] );
					if ( is_wp_error( $new ) ) {
						WP_CLI::warning( "{$tax} {$term->slug} ({$lang}): " . $new->get_error_message() );
						continue;
					}
					$tid = (int) $new['term_id'];
					pll_set_term_language( $tid, $lang );
					foreach ( get_term_meta( $term->term_id ) as $meta_key => $values ) {
						update_term_meta( $tid, $meta_key, maybe_unserialize( $values[0] ) );
					}
					$overrides = 'moods' === $key
						? [ '_er_word' => $tr['word'] ?? '' ]
						: [ '_er_headline' => $tr['headline'] ?? '', '_er_copy' => $tr['copy'] ?? '', '_er_compare_label' => $tr['compare'] ?? '' ];
					foreach ( array_filter( $overrides ) as $meta_key => $value ) {
						update_term_meta( $tid, $meta_key, $value );
					}
					$group[ $lang ] = $tid;
				}
				pll_save_term_translations( $group );
			}
		}

		// Structural pages: every language needs its own front page and Journal page,
		// otherwise /{lang}/ falls back to the posts listing.
		foreach ( [ (int) get_option( 'page_on_front' ), (int) get_option( 'page_for_posts' ) ] as $page_id ) {
			if ( ! $page_id ) {
				continue;
			}
			pll_get_post_language( $page_id ) || pll_set_post_language( $page_id, $default );
			$group = pll_get_post_translations( $page_id );
			foreach ( $languages as $lang ) {
				$source = (int) get_option( 'page_for_posts' ) === $page_id ? 'Journal' : 'Home';
				$title  = $this->theme_messages( PLL()->model->get_language( $lang )->locale )[ $source ] ?? $source;
				if ( isset( $group[ $lang ] ) ) {
					if ( $lang !== $default && $source === get_the_title( $group[ $lang ] ) && $title !== $source ) {
						wp_update_post( [ 'ID' => $group[ $lang ], 'post_title' => $title ] ); // earlier seeds left the English title
					}
					continue;
				}
				$slug = preg_match( '/^[\x20-\x7E]+$/', $title ) ? sanitize_title( $title ) . '-' . $lang : get_post_field( 'post_name', $page_id ) . '-' . $lang;
				$tid  = (int) wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ] );
				pll_set_post_language( $tid, $lang );
				$group[ $lang ] = $tid;
			}
			pll_save_post_translations( $group );
		}

		foreach ( $languages as $lang ) {
			if ( $lang === $default || empty( $s['translations'][ $lang ] ) ) {
				continue;
			}
			$tr = $s['translations'][ $lang ];
			foreach ( $dest as $seed_id => $post_id ) {
				$d = $tr['destinations'][ $seed_id ] ?? null;
				if ( ! $d ) {
					continue;
				}
				$tid = $this->upsert( 'er_destination', 'dest-' . $seed_id . '-' . $lang, [
					'post_title'   => $d['name'] ?? get_the_title( $post_id ),
					'post_name'    => $this->translation_slug( $d['name'] ?? get_the_title( $post_id ), 'er_destination', $lang ),
					'post_excerpt' => $d['desc'] ?? '',
					'post_content' => isset( $d['desc'] ) ? '<!-- wp:paragraph --><p>' . esc_html( $d['desc'] ) . '</p><!-- /wp:paragraph -->' : '',
					'post_status'  => $publish ? 'publish' : 'draft',
					'menu_order'   => (int) get_post_field( 'menu_order', $post_id ),
				], [
					'_er_tagline'       => $d['tagline'] ?? '',
					'_er_region_label'  => $d['region'] ?? '',
					'_er_best_time'     => $d['best'] ?? '',
					'_er_getting_there' => $d['reach'] ?? '',
					'_er_map_reach'     => $d['mapReach'] ?? '',
					'_er_highlights'    => isset( $d['highlights'] ) ? implode( "\n", $d['highlights'] ) : '',
					'_er_lat'           => get_post_meta( $post_id, '_er_lat', true ),
					'_er_lng'           => get_post_meta( $post_id, '_er_lng', true ),
					'_er_nights'        => get_post_meta( $post_id, '_er_nights', true ),
				] );
				if ( $tid ) {
					pll_set_post_language( $tid, $lang );
					if ( has_post_thumbnail( $post_id ) ) {
						set_post_thumbnail( $tid, get_post_thumbnail_id( $post_id ) );
					}
					pll_save_post_translations( array_merge( pll_get_post_translations( $post_id ), [ $lang => $tid ] ) );
				}
			}
			foreach ( $exps as $seed_id => $post_id ) {
				$x = $tr['experiences'][ $seed_id ] ?? null;
				if ( ! $x ) {
					continue;
				}
				$tid = $this->upsert( 'er_experience', $seed_id . '-' . $lang, [
					'post_title'  => $x['title'] ?? get_the_title( $post_id ),
					'post_name'   => $this->translation_slug( $x['title'] ?? get_the_title( $post_id ), 'er_experience', $lang ),
					'post_status' => $publish ? 'publish' : 'draft',
					'menu_order'  => (int) get_post_field( 'menu_order', $post_id ),
				], [
					'_er_location'    => $x['location'] ?? '',
					'_er_duration'    => $x['duration'] ?? '',
					'_er_badge'       => $x['badge'] ?? '',
					'_er_destination' => array_map( 'intval', get_post_meta( $post_id, '_er_destination', false ) ),
				] );
				if ( $tid ) {
					pll_set_post_language( $tid, $lang );
					if ( has_post_thumbnail( $post_id ) ) {
						set_post_thumbnail( $tid, get_post_thumbnail_id( $post_id ) );
					}
					pll_save_post_translations( array_merge( pll_get_post_translations( $post_id ), [ $lang => $tid ] ) );
				}
			}
			WP_CLI::log( $publish
				? "Translations for {$lang}: new items published from the static prototype's copy (noindex until \"Ready to index\")."
				: "Draft translations created for {$lang} (review by a native speaker before publishing)." );
		}
		$this->language_menus( $languages, $default );
	}

	/**
	 * Fill the editorial bodies and excerpts from data/editorial/<lang>/ (English by default).
	 *
	 * A body or excerpt is written only while it is empty or still exactly the one-line text the seed
	 * created (the prototype's `desc`, in that language). Anything an editor has changed is left alone, and so
	 * are titles, slugs, statuses, meta, relations and the "Ready to index" flag. Guides stay drafts until an
	 * editor publishes them.
	 *
	 * With --lang, only that language's translation of each item is written, never the English original.
	 * Only files marked `review: approved` and translated from the current English are imported; a dry run
	 * also reports the rest. Internal links are pointed at the translation, or left as text when it doesn't
	 * exist. Translated guides are not created by this command.
	 *
	 * ## OPTIONS
	 *
	 * [--lang=<code>]
	 * : Language to import, e.g. ar. Default: the site's default language.
	 *
	 * [--dry-run]
	 * : Report what would change without writing.
	 *
	 * @when after_wp_load
	 */
	public function editorial( $args, $assoc ) {
		$default = function_exists( 'pll_default_language' ) ? (string) pll_default_language() : 'en';
		$lang    = sanitize_key( (string) ( $assoc['lang'] ?? $default ) );
		$is_tr   = $lang !== $default && 'en' !== $lang;
		$dir     = ER_CORE_DIR . 'data/editorial/' . ( $is_tr ? $lang : 'en' ) . '/';
		if ( ! is_readable( $dir . 'index.json' ) ) {
			WP_CLI::error( "No editorial content for {$lang} (expected {$dir}index.json)." );
		}
		if ( $is_tr && ( ! function_exists( 'pll_get_post' ) || ! in_array( $lang, (array) pll_languages_list(), true ) ) ) {
			WP_CLI::error( "Polylang has no language {$lang}." );
		}
		$index = json_decode( (string) file_get_contents( $dir . 'index.json' ), true ) ?: []; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$dry   = ! empty( $assoc['dry-run'] );
		$data  = $this->seed_data();
		$seed  = [];
		foreach ( (array) ( $data['destinations'] ?? [] ) as $d ) {
			$seed[ 'dest-' . $d['id'] ] = (string) ( $is_tr ? ( $data['translations'][ $lang ]['destinations'][ $d['id'] ]['desc'] ?? '' ) : ( $d['desc'] ?? '' ) );
		}
		$is_seed_text = static function ( string $value, string $seed_id ) use ( $seed ): bool {
			$plain = trim( html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES, 'UTF-8' ) );
			return '' === $plain || ( ! empty( $seed[ $seed_id ] ) && trim( $seed[ $seed_id ] ) === $plain );
		};
		$counts = [ 'body' => 0, 'excerpt' => 0, 'link' => 0, 'kept' => 0, 'missing' => 0, 'unreviewed' => 0, 'guide' => 0, 'relinked' => 0, 'unlinked' => 0 ];
		foreach ( $index as $seed_id => $item ) {
			$seed_id = (string) $seed_id;
			if ( $is_tr && 'er_guide' === $item['type'] ) {
				++$counts['guide'];
				WP_CLI::log( "Skipped {$seed_id}: translated guides are not created by this command." );
				continue;
			}
			$approved = ! $is_tr || ( 'approved' === ( $item['review'] ?? '' ) && ! empty( $item['current'] ) );
			if ( ! $approved ) {
				++$counts['unreviewed'];
				if ( ! $dry ) {
					WP_CLI::log( "Skipped {$seed_id}: " . ( empty( $item['current'] ) ? 'the English changed since it was translated.' : 'not approved yet.' ) );
					continue;
				}
			}
			$id = $this->find( (string) $item['type'], $seed_id );
			if ( $id && function_exists( 'pll_get_post' ) ) {
				$id = (int) pll_get_post( $id, $default ) ?: $id; // the English original
				if ( $is_tr ) {
					$id = (int) pll_get_post( $id, $lang ); // its translation, never the original
					if ( $id && pll_get_post_language( $id ) !== $lang ) {
						$id = 0;
					}
				}
			}
			if ( ! $id ) {
				++$counts['missing'];
				WP_CLI::warning( "No {$lang} {$item['type']} for seed id {$seed_id}" );
				continue;
			}
			$file   = $dir . $seed_id . '.html';
			$body   = is_readable( $file ) ? trim( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
			$post   = get_post( $id );
			$update = [];
			$new_body = $body ? ( $is_tr ? $this->editorial_links( $body, $lang, $counts ) : $body ) : '';
			// The same words as the new version (only links or markup differ): an earlier import nobody has
			// rewritten since, so the new links can be added. Any change to the wording keeps the editor's body.
			$plain    = static fn ( string $h ): string => trim( (string) preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $h ), ENT_QUOTES, 'UTF-8' ) ) );
			$same_txt = $new_body && $plain( (string) $post->post_content ) === $plain( $new_body ) && (string) $post->post_content !== $new_body;
			if ( $body && ( $is_seed_text( (string) $post->post_content, $seed_id ) || $same_txt ) ) {
				$update['post_content'] = $new_body;
				++$counts['body'];
			} elseif ( $body ) {
				++$counts['kept'];
				WP_CLI::log( "Kept editor body: {$seed_id}" );
			}
			if ( ! empty( $item['excerpt'] ) && $is_seed_text( (string) $post->post_excerpt, $seed_id ) ) {
				$update['post_excerpt'] = (string) $item['excerpt'];
				++$counts['excerpt'];
			}
			// Guides link to their destination so they appear under "Plan your trip to …" once published.
			if ( ! $is_tr && ! empty( $item['destination'] ) && ! get_post_meta( $id, '_er_destination', true ) ) {
				$dest = $this->find( 'er_destination', 'dest-' . $item['destination'] );
				if ( $dest ) {
					if ( ! $dry ) {
						update_post_meta( $id, '_er_destination', $dest );
					}
					++$counts['link'];
				}
			}
			if ( $update && ! $dry ) {
				wp_update_post( wp_slash( [ 'ID' => $id ] + $update ) );
			}
			WP_CLI::log( sprintf( '%s %s (#%d)%s: %s', $dry ? 'Would update' : 'Updated', $seed_id, $id, $approved ? '' : ' [not approved: skipped in a real run]', $update ? implode( ', ', array_keys( $update ) ) : 'nothing' ) );
		}
		$summary = sprintf( '%s%s: bodies %d, excerpts %d, editor bodies kept %d, missing %d', $dry ? '(dry run) ' : '', $lang, $counts['body'], $counts['excerpt'], $counts['kept'], $counts['missing'] );
		$summary .= $is_tr
			? sprintf( ', not approved %d, guides skipped %d, links pointed at %s %d, links left as text %d.', $counts['unreviewed'], $counts['guide'], $lang, $counts['relinked'], $counts['unlinked'] )
			: sprintf( ', destination links %d.', $counts['link'] );
		WP_CLI::success( $summary );
	}

	/**
	 * Create or update the translations of the published legal/trust pages (see includes/legal.php).
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Report what would change without writing.
	 *
	 * @when after_wp_load
	 */
	public function legal( $args, $assoc ) {
		$dry    = ! empty( $assoc['dry-run'] );
		$result = er_legal_sync( $dry );
		foreach ( $result['lines'] as $line ) {
			WP_CLI::log( $line );
		}
		WP_CLI::success( ( $dry ? '(dry run) ' : '' ) . wp_json_encode( $result['counts'] ) );
	}

	/**
	 * Point a translated body's internal links (English paths such as /destinations/luxor/#when) at the
	 * published translation in $lang, keeping the #anchor. A link with no published translation becomes text.
	 */
	private function editorial_links( string $body, string $lang, array &$counts ): string {
		return (string) preg_replace_callback(
			'~<a href="(/[^"#]*)(#[^"]*)?">(.*?)</a>~s',
			static function ( $m ) use ( $lang, &$counts ) {
				$source = url_to_postid( home_url( $m[1] ) );
				$target = $source ? (int) pll_get_post( $source, $lang ) : 0;
				if ( $target && 'publish' === get_post_status( $target ) ) {
					++$counts['relinked'];
					return '<a href="' . esc_attr( wp_make_link_relative( (string) get_permalink( $target ) ) . ( $m[2] ?? '' ) ) . '">' . $m[3] . '</a>';
				}
				++$counts['unlinked'];
				WP_CLI::log( "  Link left as text (no published {$lang} page): {$m[1]}" );
				return $m[3];
			},
			$body
		);
	}

	/**
	 * Register the prototype's eight languages in Polylang and apply the URL settings from SETUP task 15.
	 *
	 * The list and order come from the static prototype (`LANGS` in Egypt Roamer/assets/js/i18n.js):
	 * English first and default, Arabic last. Idempotent: existing languages are kept as they are.
	 *
	 * @when after_wp_load
	 */
	public function languages() {
		if ( ! function_exists( 'PLL' ) || ! isset( PLL()->model ) ) {
			WP_CLI::error( 'Polylang is not active. Run: wp plugin install polylang --activate' );
		}
		$wanted = [
			[ 'en', 'en_US', 'English', false, 'us' ],
			[ 'de', 'de_DE', 'Deutsch', false, 'de' ],
			[ 'fr', 'fr_FR', 'Français', false, 'fr' ],
			[ 'it', 'it_IT', 'Italiano', false, 'it' ],
			[ 'es', 'es_ES', 'Español', false, 'es' ],
			[ 'ru', 'ru_RU', 'Русский', false, 'ru' ],
			[ 'zh', 'zh_CN', '中文', false, 'cn' ],
			[ 'ar', 'ar', 'العربية', true, 'eg' ],
		];
		$model    = PLL()->model;
		$existing = array_map( static fn ( $l ) => $l->slug, $model->get_languages_list() );
		foreach ( $wanted as $order => [ $slug, $locale, $name, $rtl, $flag ] ) {
			if ( in_array( $slug, $existing, true ) ) {
				WP_CLI::log( "Language {$slug}: exists" );
				continue;
			}
			$args = [ 'name' => $name, 'slug' => $slug, 'locale' => $locale, 'rtl' => $rtl, 'term_group' => $order, 'flag' => $flag, 'no_default_cat' => true ];
			// Polylang ≥ 3.7 moved add_language() to the languages sub-model.
			$result = isset( $model->languages ) && method_exists( $model->languages, 'add' ) ? $model->languages->add( $args ) : $model->add_language( $args );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( "Language {$slug}: " . $result->get_error_message() );
			}
			WP_CLI::log( "Language {$slug}: added" );
		}
		// Directory URLs without /language/, English at the root, /fr/ etc. for the others, the language
		// code kept on each front page, and no browser-language redirect (pages stay cacheable).
		$options = PLL()->options;
		foreach ( [ 'force_lang' => 1, 'rewrite' => true, 'hide_default' => true, 'redirect_lang' => true, 'browser' => false, 'default_lang' => 'en' ] as $key => $value ) {
			$options[ $key ] = $value;
		}
		if ( method_exists( $options, 'save' ) ) {
			$options->save();
		} else {
			update_option( 'polylang', $options );
		}
		if ( method_exists( $model, 'clean_languages_cache' ) ) {
			$model->clean_languages_cache();
		}
		flush_rewrite_rules();
		WP_CLI::success( 'Languages: ' . implode( ', ', array_column( $wanted, 0 ) ) . ' (default en). Next: wp egypt-roamer seed --translations --publish-translations' );
	}

	/**
	 * The launch indexing switch, step 1: review (default) or tick "Ready to index" on published pages, in every
	 * language. The decision is quality-based, not a word count. A page passes when:
	 *
	 * - it has a real body and its own description (excerpt);
	 * - a translation is complete against its English original: same sections (h2), FAQ items and list items,
	 *   and at least as many in-content links (a shortened or partial translation fails);
	 * - the body links to other pages of the site (no dead end);
	 * - its text is in the page's language (Arabic, Russian and Chinese bodies are checked for their script).
	 *
	 * It never touches "Discourage search engines" (step 2, Settings → Reading, the owner) and never unticks.
	 *
	 * ## OPTIONS
	 *
	 * [--type=<types>]
	 * : Comma-separated content types. Default: er_destination,er_experience.
	 *
	 * [--apply]
	 * : Tick the pages that pass. Without it, nothing is written.
	 *
	 * [--format=<format>]
	 * : table (default), csv or json.
	 *
	 * @when after_wp_load
	 */
	public function index( $args, $assoc ) {
		$types = array_intersect( array_map( 'trim', explode( ',', (string) ( $assoc['type'] ?? 'er_destination,er_experience' ) ) ), er_gated_types() );
		$apply = ! empty( $assoc['apply'] );
		if ( ! $types ) {
			WP_CLI::error( 'No gated content type given (' . implode( ', ', er_gated_types() ) . ').' );
		}
		$home  = preg_quote( untrailingslashit( home_url() ), '/' );
		$shape = static function ( string $html ) use ( $home ): array {
			return [
				'h2'    => (int) preg_match_all( '/<h2[\s>]/i', $html ),
				'faq'   => (int) preg_match_all( '/<(?:dt|summary|h3)[\s>]/i', $html ),
				'li'    => (int) preg_match_all( '/<li[\s>]/i', $html ),
				'links' => (int) preg_match_all( '/<a\s[^>]*href=["\'](?:\/(?!\/)|' . $home . ')/i', $html ),
				'text'  => trim( html_entity_decode( wp_strip_all_tags( strip_shortcodes( $html ) ), ENT_QUOTES, 'UTF-8' ) ),
			];
		};
		// Languages that don't use the Latin alphabet: most of the body's letters must be in their script.
		$scripts = [ 'ar' => '\p{Arabic}', 'ru' => '\p{Cyrillic}', 'zh' => '\p{Han}' ];
		$default = function_exists( 'pll_default_language' ) ? (string) pll_default_language() : 'en';
		$counts  = [ 'pass' => 0, 'fail' => 0, 'ticked' => 0, 'already' => 0 ];
		$rows    = [];
		foreach ( $types as $type ) {
			$ids = get_posts( [ 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'orderby' => 'ID', 'order' => 'ASC' ] );
			foreach ( $ids as $id ) {
				$lang  = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : $default;
				$s     = $shape( (string) get_post_field( 'post_content', $id ) );
				$fails = [];
				if ( '' === $s['text'] ) {
					$fails[] = 'no body';
				}
				if ( '' === trim( (string) get_post_field( 'post_excerpt', $id ) ) ) {
					$fails[] = 'no description (excerpt)';
				}
				if ( ! $s['links'] ) {
					$fails[] = 'no in-content links';
				}
				$source = $lang !== $default && function_exists( 'pll_get_post' ) ? (int) pll_get_post( $id, $default ) : 0;
				if ( $source ) {
					$o = $shape( (string) get_post_field( 'post_content', $source ) );
					foreach ( [ 'h2' => 'sections', 'faq' => 'FAQ items', 'li' => 'list items' ] as $k => $label ) {
						if ( $s[ $k ] !== $o[ $k ] ) {
							$fails[] = "{$label} {$s[ $k ]} vs {$o[ $k ]} in English";
						}
					}
					if ( $s['links'] < $o['links'] ) {
						$fails[] = "links {$s['links']} vs {$o['links']} in English";
					}
				} elseif ( $lang !== $default ) {
					$fails[] = 'no English original linked';
				}
				if ( isset( $scripts[ $lang ] ) && '' !== $s['text'] ) {
					$letters = max( 1, (int) preg_match_all( '/\p{L}/u', $s['text'] ) );
					$own     = (int) preg_match_all( '/' . $scripts[ $lang ] . '/u', $s['text'] );
					if ( $own / $letters < 0.6 ) {
						$fails[] = sprintf( 'only %d%% of the letters in %s script', round( 100 * $own / $letters ), $lang );
					}
				}
				$ready = (bool) get_post_meta( $id, '_er_indexable', true );
				if ( $ready ) {
					++$counts['already'];
				} elseif ( ! $fails ) {
					++$counts['pass'];
					if ( $apply ) {
						update_post_meta( $id, '_er_indexable', 1 );
						++$counts['ticked'];
					}
				} else {
					++$counts['fail'];
				}
				$rows[] = [
					'id'     => $id,
					'type'   => $type,
					'lang'   => $lang,
					'h2'     => $s['h2'],
					'links'  => $s['links'],
					'result' => $ready ? 'already ready' : ( $fails ? 'stays noindex: ' . implode( '; ', $fails ) : ( $apply ? 'ticked' : 'would tick' ) ),
					'url'    => rawurldecode( wp_make_link_relative( (string) get_permalink( $id ) ) ),
				];
			}
		}
		WP_CLI\Utils\format_items( (string) ( $assoc['format'] ?? 'table' ), $rows, [ 'id', 'type', 'lang', 'h2', 'links', 'result', 'url' ] );
		WP_CLI::success( sprintf( '%s%d pass, %d stay noindex, %d already ready, %d ticked. Indexing also needs "Discourage search engines" unticked (Settings → Reading).', $apply ? '' : '(dry run) ', $counts['pass'], $counts['fail'], $counts['already'], $counts['ticked'] ) );
	}

	/**
	 * Print content & affiliate health checks.
	 *
	 * @when after_wp_load
	 */
	/**
	 * Finds slugs cut inside a word by WordPress's 200-byte limit (long Arabic and Russian titles) and shortens
	 * them. A dry run unless --apply. WordPress keeps each old slug, so old URLs redirect (301) to the new one.
	 *
	 * ## OPTIONS
	 *
	 * [--set=<pairs>]
	 * : Explicit slugs, "ID:slug,ID:slug" (words of the title); the others get the last whole word kept.
	 *
	 * [--apply]
	 * : Change the slugs.
	 *
	 * @when after_wp_load
	 */
	public function slugs( $args, $assoc ) {
		$set = [];
		foreach ( array_filter( explode( ',', (string) ( $assoc['set'] ?? '' ) ) ) as $pair ) {
			[ $id, $slug ] = array_map( 'trim', explode( ':', $pair, 2 ) ) + [ '', '' ];
			if ( (int) $id && '' !== $slug ) {
				$set[ (int) $id ] = sanitize_title( $slug );
			}
		}
		$apply   = ! empty( $assoc['apply'] );
		$changed = 0;
		$posts   = get_posts( [ 'post_type' => array_merge( [ 'page', 'post' ], er_gated_types() ), 'post_status' => [ 'publish', 'draft', 'pending', 'future' ], 'numberposts' => -1, 'lang' => '' ] );
		foreach ( $posts as $p ) {
			$auto = er_slug_trim_partial( $p->post_name, $p->post_title );
			if ( $auto === $p->post_name && ! isset( $set[ $p->ID ] ) ) {
				continue;
			}
			$new = $set[ $p->ID ] ?? $auto;
			WP_CLI::log( sprintf( '%d	%s	%s  →  %s%s', $p->ID, function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $p->ID ) : '', urldecode( $p->post_name ), urldecode( $new ), $apply ? '' : '  (dry run)' ) );
			if ( $apply ) {
				wp_update_post( [ 'ID' => $p->ID, 'post_name' => $new ] );
				$changed++;
			}
		}
		WP_CLI::success( $apply ? "$changed slugs changed; the old ones redirect." : 'Dry run. Add --apply to change them.' );
	}

	public function health() {
		$issues = er_health_checks();
		foreach ( $issues as [ $sev, $msg ] ) {
			WP_CLI::log( strtoupper( $sev ) . ': ' . $msg );
		}
		WP_CLI::success( count( $issues ) . ' item(s).' );
	}
}

WP_CLI::add_command( 'egypt-roamer', 'ER_CLI' );
