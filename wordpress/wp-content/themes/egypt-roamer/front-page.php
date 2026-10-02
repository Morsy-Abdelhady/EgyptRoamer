<?php
/**
 * Homepage — the approved cinematic design, driven by WordPress content.
 *
 * Every section reads Appearance → Homepage settings and CMS content. Lists the
 * scripts enhance (destinations, experiences, journal) are also rendered on the
 * server, so crawlers and visitors without JavaScript get real links.
 * Sections without real content (no live offers, no published items) are omitted.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$er_data  = er_payload( 'home' );
$er_stock = er_home_stock();

/** A scene's button target: chosen page, else a sensible in-page default. */
$er_scene_link = static function ( int $n ) use ( $er_data ): array {
	$id = (int) er_home( "scene{$n}_link" );
	$id = $id ? er_translated_post_id( $id ) : 0;
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return [ get_permalink( $id ), '' ];
	}
	$dest_url = static function ( string $slug ) use ( $er_data ) {
		foreach ( $er_data['destinations'] as $d ) {
			if ( $d['id'] === $slug ) {
				return $d['url'];
			}
		}
		return '#destinations';
	};
	return [
		1 => [ $dest_url( 'cairo' ), '' ],
		2 => [ $er_data['partnerCategories'] ? '#partners' : ( er_has_published( 'er_tour' ) ? get_post_type_archive_link( 'er_tour' ) : $dest_url( 'aswan' ) ), $er_data['partnerCategories'] ? 'data-partner-tab="cruises"' : '' ],
		3 => [ '#experiences', 'data-exp-filter="all"' ],
		4 => [ $dest_url( 'hurghada' ), '' ],
	][ $n ];
};

/** Scene image: Media Library choice, else the approved photograph. */
$er_scene_img = static function ( int $n, string $class, array $extra = [] ) use ( $er_stock ): string {
	$key  = 1 === $n ? 'hero_image' : "scene{$n}_image";
	$id   = (int) er_home( $key );
	$alts = [ 1 => 'The pyramids of Giza at sunset', 2 => 'A felucca sailing on the Nile at sunset', 3 => "Golden sand dunes rolling under a clear sky in Egypt's Western Desert", 4 => 'A scuba diver gliding over a vivid coral reef in the Red Sea' ];
	$attrs = array_merge( [ 'class' => $class, 'sizes' => 3 === $n ? '120vw' : '100vw', 'loading' => 1 === $n ? 'eager' : 'lazy', 'fetchpriority' => 1 === $n ? 'high' : 'low' ], $extra );
	if ( $id ) {
		return er_img( $id, 'er-hero', $attrs );
	}
	$photo = [ 1 => $er_stock['hero'], 2 => $er_stock['scene2'], 3 => $er_stock['scene3'], 4 => $er_stock['scene4'] ][ $n ];
	return er_stock_img( $photo, er_t( $alts[ $n ] ), $attrs, 3 === $n ? [ 1000, 2400, 3200 ] : [ 900, 1400, 2000, 2800 ] );
};

/** "Roamer pick" — only for a live offer; price only if verified. */
$er_pick = static function ( int $n ) {
	$offer = (int) er_home( "scene{$n}_pick" );
	if ( ! $offer || ! er_offer_is_live( $offer ) ) {
		return;
	}
	$o = er_offer_data( $offer, 'home-scene-' . $n, (int) get_option( 'page_on_front' ) );
	?>
	<section class="scene__pick" aria-label="<?php echo esc_attr( er_t( 'Roamer pick' ) . ': ' . $o['title'] ); ?>">
		<span class="t-label"><?php er_e( 'Roamer pick' ); ?></span>
		<p class="scene__pick-title"><?php echo esc_html( $o['title'] ); ?></p>
		<?php if ( $o['price_text'] || $o['provider'] ) : ?>
			<p class="scene__pick-meta">
				<?php echo esc_html( implode( ' · ', array_filter( [ $o['price_text'] ? er_t( 'from' ) . ' ' . $o['price_text'] : '', $o['provider'] ? er_t( 'via {partner}', [ 'partner' => $o['provider'] ] ) : '' ] ) ) ); ?>
			</p>
		<?php endif; ?>
		<?php echo er_offer_cta_html( $offer, [ 'placement' => 'home-scene-' . $n, 'class' => 'link', 'icon' => ' ' . er_icon( 'i-arrow', 'icon--sm' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</section>
	<?php
};

$er_scene_head = static function ( int $n, string $btn_class ) use ( $er_scene_link ) {
	[ $url, $attr ] = $er_scene_link( $n );
	?>
	<div class="scene__content container">
		<p class="scene__kicker"><span><?php echo esc_html( sprintf( '%02d', $n ) ); ?></span> <?php echo esc_html( er_home( "scene{$n}_kicker" ) ); ?></p>
		<h2 class="scene__title" data-split><?php echo esc_html( er_home( "scene{$n}_title" ) ); ?></h2>
		<p class="scene__line"><?php echo esc_html( er_home( "scene{$n}_line" ) ); ?></p>
		<a href="<?php echo esc_url( $url ); ?>" class="btn <?php echo esc_attr( $btn_class ); ?>" data-magnetic <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed literals above ?>>
			<?php echo esc_html( er_home( "scene{$n}_cta" ) ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
	</div>
	<?php
};

// Finder tabs: only categories whose offer is live.
$er_finder_tabs = [];
foreach ( [ 'hotels' => [ 'i-bed', 'Hotels' ], 'tours' => [ 'i-compass', 'Tours & Activities' ], 'cruises' => [ 'i-ship', 'Nile Cruises' ], 'transfers' => [ 'i-route', 'Transfers' ], 'cars' => [ 'i-car', 'Car Rental' ] ] as $er_key => [ $er_icon, $er_label ] ) {
	$er_offer = (int) er_home( 'finder_' . $er_key );
	if ( $er_offer && er_offer_is_live( $er_offer ) ) {
		$er_finder_tabs[ $er_key ] = [ $er_icon, er_t( $er_label ), er_offer_go_url( $er_offer, 'home-finder', (int) get_option( 'page_on_front' ) ), (string) get_post_field( 'post_name', $er_offer ) ];
	}
}
$er_planner_on = (bool) er_home( 'planner_enabled' );
?>

<aside class="rail" id="rail" aria-label="<?php echo esc_attr( er_t( 'Journey chapters' ) ); ?>">
	<ol class="rail__list">
		<?php foreach ( [ 1 => 'pyramids', 2 => 'nile', 3 => 'desert', 4 => 'redsea' ] as $er_n => $er_id ) : ?>
			<li><a href="#scene-<?php echo esc_attr( $er_id ); ?>" data-rail="<?php echo (int) $er_n - 1; ?>"><i class="rail__dot" aria-hidden="true"></i><span class="rail__num"><?php echo esc_html( sprintf( '%02d', $er_n ) ); ?></span><span class="rail__name"><?php echo esc_html( er_home( "scene{$er_n}_title" ) ); ?></span></a></li>
		<?php endforeach; ?>
	</ol>
	<span class="rail__track" aria-hidden="true"><i id="rail-fill"></i></span>
	<p class="rail__scroll"><?php echo er_icon( 'i-mouse' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Scroll to explore' ); ?></span></p>
</aside>

<main id="main">
	<section class="journey" id="top" aria-label="<?php echo esc_attr( er_t( 'Egypt as a journey' ) ); ?>">
		<?php // The pin's spacer is part of the markup: ScrollTrigger uses it instead of re-parenting the stage, which made Chrome re-report the hero as a new Largest Contentful Paint (journey.js). ?>
		<div class="journey__spacer">
		<div class="journey__stage" id="journey-stage">

			<article class="scene scene--pyramids" id="scene-pyramids" data-scene="0">
				<div class="scene__media" data-depth>
					<?php echo $er_scene_img( 1, 'scene__img scene__img--a' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php
					$er_b = (int) er_home( 'hero_image_b' );
					echo $er_b ? er_img( $er_b, 'er-hero', [ 'class' => 'scene__img scene__img--b', 'sizes' => '100vw', 'alt' => '', 'fetchpriority' => 'low' ] ) : er_stock_img( $er_stock['hero_b'], '', [ 'class' => 'scene__img scene__img--b', 'fetchpriority' => 'low' ], [ 900, 2000 ] ); // phpcs:ignore WordPress.Security.EscapeOutput
					?>
				</div>
				<div class="scene__sun" aria-hidden="true"></div>
				<svg class="scene__fg scene__fg--dune" viewBox="0 0 1440 260" preserveAspectRatio="none" aria-hidden="true"><path d="M0 150C180 110 320 90 520 118s380 70 560 44 260-58 360-54V260H0Z" /></svg>
				<div class="scene__shade" aria-hidden="true"></div>
				<?php $er_scene_head( 1, 'btn--primary' ); ?>
				<?php $er_pick( 1 ); ?>
				<p class="scene__coords"><?php echo esc_html( er_home( 'scene1_coords' ) ); ?></p>
			</article>

			<article class="scene scene--nile" id="scene-nile" data-scene="1">
				<div class="scene__media" data-depth><?php echo $er_scene_img( 2, 'scene__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="scene__shimmer" aria-hidden="true"></div>
				<div class="scene__shade" aria-hidden="true"></div>
				<?php $er_scene_head( 2, 'btn--ghost' ); ?>
				<figure class="river-cue" aria-label="<?php echo esc_attr( er_t( 'Classic Nile cruise route from Aswan to Luxor' ) ); ?>">
					<figcaption>
						<span class="t-label"><?php er_e( 'The classic route' ); ?></span>
						<span class="river-cue__route"><?php er_e( 'Aswan → Luxor' ); ?></span>
					</figcaption>
					<svg viewBox="0 0 120 220" class="river-cue__svg" aria-hidden="true">
						<path class="river-cue__base" d="M70 210C58 188 80 170 72 150s-24-26-16-50 30-30 22-56S54 22 60 10" />
						<path class="river-cue__draw" d="M70 210C58 188 80 170 72 150s-24-26-16-50 30-30 22-56S54 22 60 10" pathLength="1" />
						<g class="river-cue__stops">
							<circle cx="70" cy="210" r="3.5" /><text x="82" y="213"><?php er_e( 'Aswan' ); ?></text>
							<circle cx="72" cy="150" r="3" /><text x="84" y="153"><?php er_e( 'Kom Ombo' ); ?></text>
							<circle cx="57" cy="104" r="3" /><text x="69" y="107"><?php er_e( 'Edfu' ); ?></text>
							<circle cx="60" cy="10" r="3.5" /><text x="72" y="13"><?php er_e( 'Luxor' ); ?></text>
						</g>
					</svg>
					<p class="river-cue__meta"><?php er_e( '4 nights · 210 km · 5 temples' ); ?></p>
				</figure>
				<?php $er_pick( 2 ); ?>
				<p class="scene__coords"><?php echo esc_html( er_home( 'scene2_coords' ) ); ?></p>
			</article>

			<article class="scene scene--desert" id="scene-desert" data-scene="2">
				<div class="scene__media scene__media--wide" data-depth><?php echo $er_scene_img( 3, 'scene__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="scene__heat" aria-hidden="true"></div>
				<canvas class="scene__canvas" data-fx="sand" aria-hidden="true"></canvas>
				<div class="scene__shade" aria-hidden="true"></div>
				<?php $er_scene_head( 3, 'btn--ghost' ); ?>
				<?php $er_pick( 3 ); ?>
				<p class="scene__coords"><?php echo esc_html( er_home( 'scene3_coords' ) ); ?></p>
			</article>

			<article class="scene scene--redsea" id="scene-redsea" data-scene="3">
				<svg class="scene__surface" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
					<defs><linearGradient id="surface-grad" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".75" /><stop offset=".35" stop-color="#bfe6ea" stop-opacity=".35" /><stop offset="1" stop-color="#0F6B7A" stop-opacity="0" /></linearGradient></defs>
					<path d="M0 40c120-26 240-26 360 0s240 26 360 0 240-26 360 0 240 26 360 0v80H0Z" />
				</svg>
				<div class="scene__media" data-depth><?php echo $er_scene_img( 4, 'scene__img scene__img--flip' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="scene__rays" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
				<canvas class="scene__canvas" data-fx="bubbles" aria-hidden="true"></canvas>
				<div class="scene__shade" aria-hidden="true"></div>
				<?php $er_scene_head( 4, 'btn--ghost' ); ?>
				<?php $er_pick( 4 ); ?>
				<p class="scene__coords"><?php echo esc_html( er_home( 'scene4_coords' ) ); ?></p>
			</article>

			<div class="journey__grade" aria-hidden="true"></div>
			<div class="journey__letterbox" aria-hidden="true"><i></i><i></i></div>

			<div class="hero" id="hero">
				<div class="hero__content container">
					<p class="hero__eyebrow t-label"><span class="split-line"><span><?php echo esc_html( er_home( 'hero_eyebrow' ) ); ?></span></span></p>
					<h1 class="hero__title">
						<span class="split-line"><span class="t-display"><?php echo esc_html( er_home( 'hero_title' ) ); ?></span></span>
						<span class="split-line"><span class="hero__sub"><?php echo er_spaced_breaks( wp_kses( er_home( 'hero_sub' ), er_inline_kses_safe() ) ); ?></span></span>
					</h1>
					<p class="hero__copy" data-hero-fade>
						<?php echo esc_html( er_home( 'hero_copy' ) ); ?><br />
						<span class="hero__copy-more"><?php echo esc_html( er_home( 'hero_copy_more' ) ); ?></span>
					</p>
					<div class="hero__ctas" data-hero-fade>
						<a href="#journey-start" class="btn btn--primary btn--lg" data-magnetic data-scroll-to="journey">
							<?php echo esc_html( er_home( 'hero_cta' ) ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
						<?php if ( er_home( 'film_enabled' ) ) : ?>
							<button class="play" type="button" data-open="film" aria-label="<?php echo esc_attr( er_t( 'Watch the Film' ) ); ?>">
								<span class="play__ring"><?php echo er_icon( 'i-play' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<span class="play__label"><?php er_e( 'Watch the Film' ); ?></span>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<p class="hero__note" data-hero-fade aria-hidden="true">
					<?php echo implode( '<br />', array_map( static fn ( $l ) => '<em>' . esc_html( $l ) . '</em>', preg_split( '/\r\n|\r|\n/', (string) er_home( 'hero_note' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>

				<?php if ( $er_finder_tabs ) : ?>
					<form class="finder" id="finder" data-hero-fade aria-label="<?php echo esc_attr( er_t( 'Compare options with our partners' ) ); ?>">
						<div class="finder__tabs" role="group" aria-label="<?php echo esc_attr( er_t( 'What are you looking for?' ) ); ?>">
							<?php $er_first = true; foreach ( $er_finder_tabs as $er_key => [ $er_icon, $er_label, $er_go, $er_slug ] ) : ?>
								<button type="button" aria-pressed="<?php echo $er_first ? 'true' : 'false'; ?>" data-finder="<?php echo esc_attr( $er_key ); ?>" data-go="<?php echo esc_url( $er_go ); ?>" data-offer="<?php echo esc_attr( $er_slug ); ?>"><?php echo er_icon( $er_icon ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $er_label ); ?></button>
							<?php $er_first = false; endforeach; ?>
						</div>
						<div class="finder__fields">
							<label class="finder__field">
								<?php echo er_icon( 'i-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="finder__label"><?php er_e( 'Where' ); ?></span>
								<select name="where" aria-label="<?php echo esc_attr( er_t( 'Destination' ) ); ?>">
									<option value=""><?php er_e( 'Anywhere in Egypt' ); ?></option>
									<?php foreach ( $er_data['destinations'] as $er_d ) : ?>
										<option value="<?php echo esc_attr( $er_d['name'] ); ?>"><?php echo esc_html( $er_d['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<label class="finder__field">
								<?php echo er_icon( 'i-calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="finder__label"><?php er_e( 'When' ); ?></span>
								<select name="when" aria-label="<?php echo esc_attr( er_t( 'Travel month' ) ); ?>" data-months><option value=""><?php er_e( 'Flexible dates' ); ?></option></select>
							</label>
							<label class="finder__field">
								<?php echo er_icon( 'i-users' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="finder__label"><?php er_e( 'Who' ); ?></span>
								<select name="who" aria-label="<?php echo esc_attr( er_t( 'Travellers' ) ); ?>">
									<option value="2"><?php er_e( '2 adults' ); ?></option>
									<option value="1"><?php er_e( '1 adult' ); ?></option>
									<option value="2"><?php er_e( '2 adults, 1 child' ); ?></option>
									<option value="2"><?php er_e( '2 adults, 2 children' ); ?></option>
									<option value="5"><?php er_e( 'Group (5+)' ); ?></option>
								</select>
							</label>
							<button class="btn btn--primary finder__go" type="submit" aria-label="<?php echo esc_attr( er_t( 'Compare Options' ) ); ?>">
								<?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span><?php er_e( 'Compare Options' ); ?></span>
							</button>
						</div>
						<p class="finder__note"><?php echo er_icon( 'i-shield', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( er_home( 'finder_note' ) ); ?></p>
					</form>
				<?php endif; ?>
			</div>

			<p class="journey__counter" aria-hidden="true"><span id="journey-count">01</span> / 04</p>
		</div>
		</div>
	</section>
	<span id="journey-start" aria-hidden="true"></span>

	<section class="interlude on-dark" aria-label="<?php echo esc_attr( er_home( 'interlude_eyebrow' ) ); ?>">
		<div class="container interlude__inner">
			<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'interlude_eyebrow' ) ); ?></p>
			<p class="interlude__quote" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'interlude_quote' ), er_inline_kses_safe() ) ); ?></p>
			<dl class="interlude__facts">
				<?php
				$er_delay = 120;
				foreach ( preg_split( '/\r\n|\r|\n/', (string) er_home( 'interlude_facts' ) ) as $er_line ) :
					if ( ! str_contains( $er_line, '|' ) ) {
						continue;
					}
					[ $er_num, $er_text ] = array_map( 'trim', explode( '|', $er_line, 2 ) );
					?>
					<div data-reveal style="--d: <?php echo (int) $er_delay; ?>ms"><dt><?php echo esc_html( $er_num ); ?></dt><dd><?php echo esc_html( $er_text ); ?></dd></div>
					<?php
					$er_delay += 80;
				endforeach;
				?>
			</dl>
		</div>
	</section>

	<?php if ( $er_data['moods'] ) : ?>
		<section class="moods on-dark" id="moods" aria-labelledby="moods-title">
			<div class="moods__bg" aria-hidden="true" data-mood-bg></div>
			<div class="moods__veil" aria-hidden="true"></div>
			<div class="container moods__inner">
				<header class="moods__head">
					<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'moods_eyebrow' ) ); ?></p>
					<h2 class="t-h2" id="moods-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'moods_title' ), er_inline_kses_safe() ) ); ?></h2>
				</header>
				<div class="moods__dial" role="tablist" aria-label="<?php echo esc_attr( er_t( 'Travel moods' ) ); ?>" data-mood-list data-reveal style="--d: 160ms"></div>
				<div class="moods__body" id="mood-panel" role="tabpanel" aria-labelledby="moods-title">
					<div class="moods__statement" aria-live="polite">
						<p class="moods__word" data-mood-word data-prefix="<?php echo esc_attr( er_t( 'An Egypt' ) ); ?>"><?php echo esc_html( $er_data['moods'][0]['word'] ); ?></p>
						<p class="moods__desc" data-mood-desc><?php echo esc_html( $er_data['moods'][0]['desc'] ); ?></p>
						<?php if ( $er_planner_on ) : ?>
							<div class="moods__ctas">
								<a href="#planner" class="btn btn--primary" data-magnetic data-mood-build><?php er_e( 'Build My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
								<span class="moods__hint"><?php er_e( "We'll pre-fill your planner with this mood." ); ?></span>
							</div>
						<?php endif; ?>
					</div>
					<div class="moods__recs" data-mood-recs aria-live="polite"></div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_data['destinations'] ) : ?>
		<section class="section destinations" id="destinations" aria-labelledby="dest-title">
			<div class="container">
				<header class="section-head section-head--split">
					<div>
						<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'dest_eyebrow' ) ); ?></p>
						<h2 class="t-h2" id="dest-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'dest_title' ), er_inline_kses_safe() ) ); ?></h2>
					</div>
					<div class="section-head__aside" data-reveal style="--d: 160ms">
						<p class="muted"><?php echo esc_html( er_home( 'dest_aside' ) ); ?></p>
						<a class="link" href="<?php echo esc_url( get_post_type_archive_link( 'er_destination' ) ); ?>"><?php er_e( 'All destinations' ); ?> <?php echo er_icon( 'i-arrow', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					</div>
				</header>
				<div class="dest">
					<ol class="dest__index" data-dest-list>
						<?php foreach ( $er_data['destinations'] as $er_i => $er_d ) : ?>
							<li class="dest__item"><a class="dest__btn" href="<?php echo esc_url( $er_d['url'] ); ?>"><span class="dest__num"><?php echo esc_html( sprintf( '%02d', $er_i + 1 ) ); ?></span><span class="dest__name"><?php echo esc_html( $er_d['name'] ); ?></span><span class="dest__region"><?php echo esc_html( $er_d['region'] ); ?></span></a></li>
						<?php endforeach; ?>
					</ol>
					<div class="dest__stage" data-dest-stage aria-live="polite">
						<div class="dest__frames" data-dest-frames></div>
						<div class="dest__panel" data-dest-panel></div>
						<span class="dest__count" data-dest-count>01 / <?php echo esc_html( sprintf( '%02d', count( $er_data['destinations'] ) ) ); ?></span>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( er_home( 'map_enabled' ) && count( $er_data['destinations'] ) > 1 ) : ?>
		<section class="section map section--dark" id="map" aria-labelledby="map-title">
			<div class="container map__grid">
				<div class="map__copy">
					<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'map_eyebrow' ) ); ?></p>
					<h2 class="t-h2" id="map-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'map_title' ), er_inline_kses_safe() ) ); ?></h2>
					<p class="map__intro" data-reveal style="--d: 160ms"><?php echo esc_html( er_home( 'map_intro' ) ); ?></p>
					<ul class="map__list" data-map-list></ul>
					<p class="map__legend t-meta">
						<span><i class="dot dot--gold"></i><?php er_e( 'Destination' ); ?></span>
						<span><i class="dot dot--teal"></i><?php er_e( 'The Nile' ); ?></span>
						<span><i class="dot dot--dash"></i><?php er_e( 'Popular connection' ); ?></span>
					</p>
				</div>
				<div class="map__canvas" data-map>
					<svg class="map__svg" viewBox="0 0 560 470" role="group" aria-labelledby="map-svg-title" data-map-svg>
						<title id="map-svg-title"><?php echo esc_html( er_t( 'Map of Egypt showing {n} destinations', [ 'n' => count( $er_data['destinations'] ) ] ) ); ?></title>
						<defs>
							<radialGradient id="map-glow" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#C9A227" stop-opacity=".55" /><stop offset="1" stop-color="#C9A227" stop-opacity="0" /></radialGradient>
							<linearGradient id="nile-grad" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#0F6B7A" /><stop offset="1" stop-color="#3fb4c4" /></linearGradient>
							<pattern id="map-dots" width="8" height="8" patternUnits="userSpaceOnUse"><circle cx="1" cy="1" r=".7" fill="#fff" opacity=".13" /></pattern>
						</defs>
						<g class="map__world" data-map-world></g>
					</svg>
					<div class="map__card" data-map-card hidden></div>
					<div class="map__tools">
						<button class="icon-btn" type="button" data-map-reset aria-label="<?php echo esc_attr( er_t( 'Reset map view' ) ); ?>"><?php echo er_icon( 'i-compass' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					</div>
					<p class="map__scale t-meta" aria-hidden="true"><?php er_e( 'N ↑ · 0 — 200 km' ); ?></p>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_data['partnerCategories'] ) : ?>
		<section class="section partners" id="partners" aria-labelledby="partners-title">
			<div class="container">
				<header class="section-head section-head--split">
					<div>
						<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'partners_eyebrow' ) ); ?></p>
						<h2 class="t-h2" id="partners-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'partners_title' ), er_inline_kses_safe() ) ); ?></h2>
					</div>
					<div class="section-head__aside" data-reveal style="--d: 160ms"><p class="muted"><?php echo esc_html( er_home( 'partners_aside' ) ); ?></p></div>
				</header>
				<div class="partners__tabs" role="tablist" aria-label="<?php echo esc_attr( er_t( 'Booking categories' ) ); ?>" data-partner-tabs data-reveal>
					<span class="partners__ink" aria-hidden="true"></span>
				</div>
				<div class="partners__panel" data-partner-panel role="tabpanel" aria-live="polite"></div>
				<?php er_disclosure(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_data['experiences'] ) : ?>
		<section class="section experiences" id="experiences" aria-labelledby="exp-title">
			<div class="container">
				<header class="section-head section-head--split">
					<div>
						<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'exp_eyebrow' ) ); ?></p>
						<h2 class="t-h2" id="exp-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'exp_title' ), er_inline_kses_safe() ) ); ?></h2>
					</div>
					<div class="section-head__aside" data-reveal style="--d: 160ms">
						<p class="muted"><?php echo esc_html( er_home( 'exp_aside' ) ); ?></p>
						<div class="exp__filters" role="group" aria-label="<?php echo esc_attr( er_t( 'Filter experiences' ) ); ?>" data-exp-filters></div>
					</div>
				</header>
			</div>
			<div class="rail-scroller" data-rail-scroller>
				<ul class="exp__track" data-exp-track>
					<?php
					foreach ( er_home_experience_ids() as $er_id ) {
						echo er_card( $er_id, 'home-experiences' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside
					}
					?>
				</ul>
			</div>
			<div class="container exp__controls">
				<span class="exp__progress" aria-hidden="true"><i data-exp-progress></i></span>
				<div class="exp__arrows">
					<button class="round-btn" type="button" data-exp-prev aria-label="<?php echo esc_attr( er_t( 'Previous experiences' ) ); ?>"><?php echo er_icon( 'i-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					<button class="round-btn" type="button" data-exp-next aria-label="<?php echo esc_attr( er_t( 'Next experiences' ) ); ?>"><?php echo er_icon( 'i-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'er_experience' ) ); ?>" class="link"><?php er_e( 'View all experiences' ); ?> <?php echo er_icon( 'i-arrow', 'icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_data['guides'] ) : ?>
		<section class="section guide" id="guide" aria-labelledby="guide-title">
			<div class="container">
				<header class="guide__masthead">
					<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'guide_eyebrow' ) ); ?></p>
					<h2 class="t-h1" id="guide-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'guide_title' ), er_inline_kses_safe() ) ); ?></h2>
					<div class="guide__cats" role="group" aria-label="<?php echo esc_attr( er_t( 'Filter stories' ) ); ?>" data-guide-cats data-reveal style="--d: 160ms"></div>
				</header>
				<div class="guide__layout">
					<div class="guide__feature" data-guide-feature></div>
					<ol class="guide__list" data-guide-list>
						<?php foreach ( $er_data['guides'] as $er_i => $er_g ) : ?>
							<li class="story"><a href="<?php echo esc_url( $er_g['href'] ); ?>"><span class="story__num"><?php echo esc_html( sprintf( '%02d', $er_i + 1 ) ); ?></span><span class="story__body"><span class="story__meta"><?php echo esc_html( $er_g['cat'] ); ?></span><span class="story__title"><?php echo esc_html( $er_g['title'] ); ?></span></span></a></li>
						<?php endforeach; ?>
					</ol>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $er_planner_on && $er_data['destinations'] ) : ?>
		<?php
		$er_plan_link = (int) er_home( 'planner_link' );
		$er_plan_link = $er_plan_link ? er_translated_post_id( $er_plan_link ) : 0;
		$er_plan_href = $er_plan_link && 'publish' === get_post_status( $er_plan_link ) ? get_permalink( $er_plan_link ) : '';
		$er_plan_img  = (int) er_home( 'planner_image' );
		?>
		<section class="planner on-dark" id="planner" aria-labelledby="planner-title">
			<div class="planner__bg" aria-hidden="true">
				<?php echo $er_plan_img ? er_img( $er_plan_img, 'er-hero', [ 'alt' => '', 'sizes' => '100vw' ] ) : er_stock_img( $er_stock['planner'], '', [], [ 1200, 2000 ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<div class="container planner__inner">
				<header class="planner__head">
					<p class="eyebrow" data-reveal><?php echo esc_html( er_home( 'planner_eyebrow' ) ); ?></p>
					<h2 class="t-h1" id="planner-title" data-reveal style="--d: 80ms"><?php echo er_spaced_breaks( wp_kses( er_home( 'planner_title' ), er_inline_kses_safe() ) ); ?></h2>
					<p class="planner__intro" data-reveal style="--d: 160ms"><?php echo esc_html( er_home( 'planner_intro' ) ); ?></p>
				</header>
				<form class="builder" data-builder aria-label="<?php echo esc_attr( er_t( 'Trip builder' ) ); ?>"<?php echo $er_plan_href ? ' data-href="' . esc_url( $er_plan_href ) . '"' : ''; ?>>
					<div class="builder__steps">
						<fieldset class="builder__step">
							<legend><span>01</span> <?php er_e( 'How long?' ); ?></legend>
							<div class="builder__duration"><output data-b-days-out>8</output><span data-b-days-unit><?php er_e( 'nights' ); ?></span></div>
							<input type="range" min="3" max="21" value="8" step="1" name="days" data-b-days aria-label="<?php echo esc_attr( er_t( 'Trip length in nights' ) ); ?>" />
							<div class="builder__scale"><span>3</span><span><?php er_e( 'A long weekend → a grand tour' ); ?></span><span>21</span></div>
						</fieldset>
						<fieldset class="builder__step">
							<legend><span>02</span> <?php er_e( 'What moves you?' ); ?></legend>
							<div class="builder__chips" data-b-interests></div>
						</fieldset>
						<fieldset class="builder__step">
							<legend><span>03</span> <?php er_e( 'Where to?' ); ?></legend>
							<div class="builder__chips" data-b-dests></div>
						</fieldset>
						<fieldset class="builder__step">
							<legend><span>04</span> <?php er_e( 'Travel style' ); ?></legend>
							<div class="builder__seg" data-b-style role="radiogroup" aria-label="<?php echo esc_attr( er_t( 'Travel style' ) ); ?>">
								<label><input type="radio" name="style" value="smart" /><span><?php er_e( 'Smart' ); ?></span></label>
								<label><input type="radio" name="style" value="comfort" checked /><span><?php er_e( 'Comfort' ); ?></span></label>
								<label><input type="radio" name="style" value="luxury" /><span><?php er_e( 'Luxury' ); ?></span></label>
								<i aria-hidden="true"></i>
							</div>
						</fieldset>
					</div>
					<section class="itin" aria-live="polite" aria-label="<?php echo esc_attr( er_t( 'Your draft itinerary' ) ); ?>">
						<div class="itin__head">
							<span class="t-label"><?php er_e( 'Your Egypt, drafted' ); ?></span>
							<p class="itin__title" data-itin-title></p>
						</div>
						<ol class="itin__route" data-itin-route></ol>
						<dl class="itin__facts">
							<div<?php echo $er_data['styleRates'] ? '' : ' hidden'; ?>><dt><?php er_e( 'Est. per person' ); ?></dt><dd data-itin-budget>—</dd></div>
							<div><dt><?php er_e( 'Best months' ); ?></dt><dd data-itin-season>—</dd></div>
						</dl>
						<div class="itin__ctas">
							<?php if ( $er_plan_href ) : ?>
								<button type="submit" class="btn btn--primary btn--lg btn--block" data-magnetic><?php er_e( 'Build My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
							<?php endif; ?>
							<a href="<?php echo esc_url( get_post_type_archive_link( 'er_destination' ) ); ?>" class="btn btn--ghost btn--block"><?php er_e( 'Explore Egypt' ); ?></a>
						</div>
					</section>
				</form>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
