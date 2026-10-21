<?php
/**
 * New journey, proposal 4, slice 5 (owner, 17 Oct): the guides, previewed with ?ux=2 only.
 * From docs/prototypes/journey-v4/ (v4-tourist2.py §7, guides-fix.py, v4-guides.py) and the reviews:
 * - the Travel Guide index showed the same photo on neighbouring guides (Coptic Cairo and Inside the Pyramids, Saqqara
 *   and the Grand Egyptian Museum, three guides with the Valley of the Kings photo). Each guide now has a photo of
 *   its own place: free Unsplash photos whose location tag names the place (photo policy of 2026-10-03), chosen one
 *   by one in the prototype (guide-photos.py) or already approved in Core data; the card and the guide's hero match;
 * - a guide covering several trips listed up to 4 partner options of different trips, each with a gold "Book", so a
 *   reader could not tell which option belonged to which trip. It now lists the trips, each with its length and
 *   number of options, opening the trip page where the options are compared. A guide about one trip keeps its
 *   options and "Our pick".
 */

defined( 'ABSPATH' ) || exit;

/** A distinct photo per guide (default-language slug → Unsplash photo id). */
const ER_UX2_GUIDE_PHOTOS = [
	'luxor-hot-air-balloon' => '1669521762357-966bfdcbde1d', // Temple of Hatshepsut, Kurna, Hongbin
	'cairo-museums-which-to-visit' => '1553835568-c48d3b90ed70', // Museum of Egypt, Cairo, Spencer Davis
	'islamic-cairo-guide' => '1595979904086-471704dc0e81', // Al-Rifa'i Mosque, Cairo, Omar Adel
	'dendera-and-abydos-temples' => '1723997326348-d4953661a61a', // Dandarah, Qena, dleonard1210
	'el-gouna-guide' => '1511371241697-fbcaef9dd576', // El Gouna, Levi Morsy
	'luxor-west-bank-temples-and-tombs'     => '1623417765161-c71d6c83cfee',
	'mount-sinai-saint-catherine'           => '1603889947015-f3305fc399ea',
	'saqqara-and-dahshur-pyramids'          => '1678038592327-c5730737f867',
	'red-sea-famous-dive-sites'             => '1778400283418-b419be4be7a5',
	'coptic-cairo-guide'                    => '1679238211153-546821b75f98',
	'inside-the-pyramids-of-giza'           => '1761561044700-4262beec7d63', // Giza Pyramid Complex, Thomas K
	'great-sand-sea'                        => '1771839535027-865f757ae09c',
	'grand-egyptian-museum-visit'           => '1761688179162-4c42356857ee', // Pyramids of Giza, Fatih Beki
	'karnak-and-luxor-temple'               => '1761056981183-3faf66a62d6e',
	'14-days-in-egypt-itinerary'            => '1644517270263-4112379d97ca',
	'siwa-or-white-desert'                  => '1771236469626-323bb7a34bac',
	'valley-of-the-kings-tombs-and-tickets' => '1683891043389-31a5ca0692f8', // Hatshepsut's temple (card-photos.json)
	'cairo-at-night'                        => '1761421852464-463fb31f2dc0',
	'temples-of-aswan-and-lake-nasser'      => '1738580426867-03fa8c8b5288', // Philae, Aswan, Martijn Vonk
	'10-days-in-egypt-itinerary'            => '1610331800877-462a21600420', // Luxor Temple, Artem Bryzgalov
	'white-desert-night'                    => '1691591403777-128b9a842b8b',
	'snorkelling-hurghada-or-sharm'         => '1722264222007-3e4f1808db3e',
	'nile-cruise-or-hotels-luxor-aswan'     => '1778402153163-fc9a5fd0131a',
	'day-trips-from-cairo'                  => '1738512156462-408182814b21', // Pyramid of Djoser, Martijn Vonk
	'abu-simbel-from-aswan'                 => '1633163893862-4cdc62de7d82',
	'best-time-to-visit-egypt'              => '1762530162773-c99f38d4d5d3',
	'7-days-in-egypt-itinerary'             => '1767797865870-0fd4d82608f7',
	'cairo-travel-guide'                    => '1647108694450-67be2c64ba3e',
	'hidden-gems-in-egypt'                  => '1714229722648-3a90c3ef229f',
];

/** The guide's own photo in the preview, '' when none is set (any language: its default-language version's). */
function er_ux2_guide_photo( int $post_id ): string {
	if ( ! er_ux2() || 'er_guide' !== get_post_type( $post_id ) ) {
		return '';
	}
	$src = $post_id;
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		$src = (int) pll_get_post( $post_id, (string) pll_default_language() ) ?: $post_id;
	}
	return ER_UX2_GUIDE_PHOTOS[ (string) get_post_field( 'post_name', $src ) ] ?? '';
}

/** A guide covering several trips: one row per trip (photo, name, length, options) opening the trip page. */
function er_ux2_trip_group( array $trip_ids ): void {
	echo '<div class="bp__group ux2-trips"><ul class="bp__list">';
	foreach ( $trip_ids as $id ) {
		$id     = (int) $id;
		$offers = function_exists( 'er_offers_for_post' ) ? er_offers_for_post( $id, 6 ) : [];
		$n      = count( $offers );
		$len    = function_exists( 'er_ux2_offer_length' ) ? er_ux2_offer_length( $offers ) : '';
		$img    = er_post_img( $id, 'thumbnail', [ 'alt' => '', 'sizes' => '80px' ] );
		echo '<li class="bp__row ux2-trips__row">' . ( $img ? '<span class="bp__img">' . $img . '</span>' : '' ) . '<div class="bp__main">'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<span class="bp__name">' . esc_html( get_the_title( $id ) ) . '</span><span class="bp__meta">';
		if ( '' !== $len ) {
			echo '<span>' . esc_html( $len ) . '</span>';
		}
		if ( $n ) {
			echo '<span>' . esc_html( 1 === $n ? er_t( '1 option' ) : er_t( '{n} options', [ 'n' => (string) $n ] ) ) . '</span>';
		}
		echo '</span></div>';
		echo '<a class="btn btn--outline btn--sm" href="' . esc_url( get_permalink( $id ) ) . '" aria-label="' . esc_attr( er_t( 'See options' ) . ': ' . get_the_title( $id ) ) . '"><span class="ux2-trips__cta">' . esc_html( er_t( 'See options' ) ) . '</span> ' . er_icon( 'i-arrow', 'icon--sm' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</li>';
	}
	echo '</ul></div>';
}
