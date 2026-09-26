<?php
/**
 * WP-CLI: migrate the static prototype's content and run health checks.
 *
 *   wp egypt-roamer seed [--with-images] [--translations]
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
		$id   = $this->find( $type, $seed_id );
		$post = array_merge( [ 'post_type' => $type ], $post );
		if ( $id ) {
			$post['ID'] = $id;
			unset( $post['post_status'] ); // never flip an editor's status decision
			wp_update_post( wp_slash( $post ) );
		} else {
			$id = (int) wp_insert_post( wp_slash( $post ), true );
			if ( ! $id || is_wp_error( $id ) ) {
				WP_CLI::warning( "Could not create {$type} {$seed_id}" );
				return 0;
			}
			update_post_meta( $id, '_er_seed_id', $seed_id );
		}
		foreach ( $meta as $key => $value ) {
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

	/** ASCII slug: arrows, middots and symbols in titles must not leak into URLs. */
	private function slug( string $title ): string {
		return sanitize_title( preg_replace( '/[^\x20-\x7E]+/', ' ', remove_accents( $title ) ) );
	}

	/** Download an Unsplash photo into the Media Library with a descriptive filename and alt text. */
	private function sideload( string $photo_id, string $title, int $parent = 0 ): int {
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
			$this->translations( $dest, $exps );
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
		if ( in_array( get_option( 'permalink_structure' ), [ '', '/%postname%/' ], true ) ) {
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
		WP_CLI::log( 'Menus: 5 (links to unpublished pages resolve once those pages are published)' );
	}

	/** Draft translations from the prototype's locale files, linked in Polylang. */
	private function translations( array $dest, array $exps ): void {
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
				if ( isset( $group[ $lang ] ) ) {
					continue;
				}
				$locale = '';
				foreach ( PLL()->model->get_languages_list() as $l ) {
					$locale = $l->slug === $lang ? $l->locale : $locale;
				}
				switch_to_locale( $locale );
				$title = (int) get_option( 'page_for_posts' ) === $page_id ? translate( 'Journal', 'egypt-roamer' ) : translate( 'Home', 'egypt-roamer' ); // phpcs:ignore WordPress.WP.I18n
				restore_previous_locale();
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
					'post_excerpt' => $d['desc'] ?? '',
					'post_content' => isset( $d['desc'] ) ? '<!-- wp:paragraph --><p>' . esc_html( $d['desc'] ) . '</p><!-- /wp:paragraph -->' : '',
					'post_status'  => 'draft',
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
					'post_status' => 'draft',
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
			WP_CLI::log( "Draft translations created for {$lang} (review by a native speaker before publishing)." );
		}
	}

	/**
	 * Print content & affiliate health checks.
	 *
	 * @when after_wp_load
	 */
	public function health() {
		$issues = er_health_checks();
		foreach ( $issues as [ $sev, $msg ] ) {
			WP_CLI::log( strtoupper( $sev ) . ': ' . $msg );
		}
		WP_CLI::success( count( $issues ) . ' item(s).' );
	}
}

WP_CLI::add_command( 'egypt-roamer', 'ER_CLI' );
