<?php
/**
 * Site footer — newsletter (real sign-up), WordPress menus, logo artwork,
 * legal links, mobile dock and overlays.
 */

defined( 'ABSPATH' ) || exit;

$er_footer_links = ''; // the column links, so the legal row does not repeat a page
$er_col          = static function ( string $title, string $location ) use ( &$er_footer_links ) {
	$menu = er_menu( $location );
	if ( ! $menu ) {
		return;
	}
	$er_footer_links .= $menu;
	echo '<div data-footer-col="' . esc_attr( $location ) . '"><h3 class="t-label">' . esc_html( $title ) . '</h3>' . $menu . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_nav_menu output
};
// The language's homepage, then the anchor.
$er_planner_url = (bool) er_home( 'planner_enabled' ) ? ( is_front_page() ? '#planner' : er_home_url() . '#planner' ) : '';
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
			$er_legal = er_legal_links( (string) ( $er_footer_links ?? '' ) );
			if ( $er_legal ) {
				echo '<p class="footer__legal">' . wp_kses_post( str_replace( [ '<li', '</li>' ], [ '<span', '</span>' ], $er_legal ) ) . '</p>';
			}
			?>
		</div>
	</div>
</footer>

<nav class="dock" aria-label="<?php echo esc_attr( er_t( 'Quick actions' ) ); ?>">
	<a href="<?php echo esc_url( get_post_type_archive_link( 'er_destination' ) ?: er_home_url() ); ?>" class="dock__item"><?php echo er_icon( 'i-compass' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Explore' ); ?></span></a>
	<button type="button" class="dock__item" data-open="saved"><?php echo er_icon( 'i-heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Saved' ); ?></span><i class="dock__badge" data-fav-count></i></button>
	<button type="button" class="dock__item" data-open="search"><?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Search' ); ?></span></button>
	<?php if ( $er_planner_url ) : ?>
		<a href="<?php echo esc_url( $er_planner_url ); ?>" class="dock__plan"><?php echo er_icon( 'i-route' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php er_e( 'Plan My Trip' ); ?></span></a>
	<?php endif; ?>
</nav>

<nav class="menu" id="menu" aria-label="<?php echo esc_attr( er_t( 'Primary' ) ); ?>" hidden>
	<div class="menu__inner">
		<?php echo er_menu( 'primary', '<ul class="menu__links">%3$s</ul>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $er_planner_url ) : ?>
			<a href="<?php echo esc_url( $er_planner_url ); ?>" class="btn btn--primary btn--lg btn--block" data-close><?php er_e( 'Plan My Trip' ); ?> <?php echo er_icon( 'i-arrow', 'icon--arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php endif; ?>
		<?php er_lang_switcher( 'mobile' ); ?>
	</div>
</nav>

<div class="overlay search" id="search" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( er_t( 'Search' ) ); ?>" hidden>
	<div class="search__box">
		<form class="search__field" role="search" method="get" action="<?php echo esc_url( er_home_url() ); ?>">
			<?php echo er_icon( 'i-search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<input type="search" name="s" placeholder="<?php echo esc_attr( er_t( 'Search destinations, tours, cruises, guides…' ) ); ?>" aria-label="<?php echo esc_attr( er_t( 'Search' ) ); ?>" data-search-input />
			<button class="icon-btn" type="button" data-close aria-label="<?php echo esc_attr( er_t( 'Close search' ) ); ?>"><?php echo er_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</form>
		<div class="search__results" data-search-results></div>
	</div>
</div>

<div class="overlay drawer" id="saved" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( er_t( 'Saved' ) ); ?>" hidden>
	<div class="drawer__panel">
		<div class="drawer__head">
			<h2 class="t-h3"><?php er_e( 'Saved for later' ); ?></h2>
			<button class="icon-btn" type="button" data-close aria-label="<?php echo esc_attr( er_t( 'Close saved' ) ); ?>"><?php echo er_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</div>
		<div class="drawer__body" data-saved-list></div>
	</div>
</div>

<?php
// Trip assistant (Core): answers from the site's published pages. Its script loads when it is first opened.
$er_assistant = function_exists( 'er_assistant_mode' ) ? er_assistant_mode() : 'off';
if ( 'off' !== $er_assistant ) :
	$er_assistant_data = [
		'endpoint' => esc_url_raw( rest_url( 'egypt-roamer/v1/assistant' ) ),
		'lang'     => er_lang(),
		'script'   => ER_THEME_URI . '/assets/js/assistant.js?ver=' . rawurlencode( (string) er_asset_ver( 'assets/js/assistant.js' ) ),
		'i18n'     => [
			'intro'    => er_t( 'Pages on Egypt Roamer that match your question:' ),
			'none'     => er_t( 'Nothing on Egypt Roamer matches that yet. Try a place or an experience, or browse:' ),
			'busy'     => er_t( 'Searching…' ),
			'retry'    => er_t( 'Please try again in a few minutes.' ),
			'er_destination' => er_t( 'Destinations' ),
			'er_experience'  => er_t( 'Experiences' ),
		],
	];
	// Chat with the team (Core includes/chat.php): same drawer, its own endpoints. Strings for the script.
	$er_chat = function_exists( 'er_chat_enabled' ) && er_chat_enabled();
	if ( $er_chat ) {
		$er_assistant_data['chat'] = [
			'endpoint' => esc_url_raw( rest_url( 'egypt-roamer/v1/chat/' ) ),
			'i18n'     => [
				'online'    => er_t( 'Team is online' ),
				'offline'   => er_t( 'Leave us a message and we’ll get back to you.' ),
				'title'     => er_t( 'Live chat' ),
				'label'     => er_t( 'Your message' ),
				'send'      => er_t( 'Send' ),
				'waiting'   => er_t( 'Waiting for the team…' ),
				'left'      => er_t( 'Thanks! We’ll reply here as soon as we can. You can close this window and come back later.' ),
				'human'     => er_t( 'You’re chatting with the Egypt Roamer team.' ),
				'ai'        => er_t( 'You’re back with the trip assistant. You can ask for the team again at any time.' ),
				'closed'    => er_t( 'This chat is closed. Write again to reopen it.' ),
				'sending'   => er_t( 'Sending…' ),
				'failed'    => er_t( 'Message couldn’t be sent. Try again.' ),
				'retry'     => er_t( 'Retry' ),
				'offline_c' => er_t( 'Connection interrupted. Retrying…' ),
				'team'      => er_t( 'Egypt Roamer team' ),
				'you'       => er_t( 'You' ),
				'assistant' => er_t( 'Trip assistant' ),
				'connect'   => er_t( 'Of course. I can connect you with the Egypt Roamer team.' ),
				'open'      => er_t( 'Chat with Egypt Roamer' ),
				'note'      => er_t( 'Your messages, and your name and email if you add them, are saved so our team can reply.' ),
			],
		];
		$er_privacy = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $er_privacy && function_exists( 'pll_get_post' ) ) {
			$er_privacy = (int) pll_get_post( $er_privacy ) ?: $er_privacy;
		}
		$er_privacy_url = $er_privacy && 'publish' === get_post_status( $er_privacy ) ? get_permalink( $er_privacy ) : '';
	}
	?>
	<button type="button" class="assistant-launch" data-open="assistant" aria-haspopup="dialog" aria-controls="assistant">
		<?php echo er_icon( 'i-sparkle' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span><?php er_e( 'Trip assistant' ); ?></span>
	</button>
	<div class="overlay drawer assistant" id="assistant" role="dialog" aria-modal="true" aria-labelledby="assistant-title" data-assistant="<?php echo esc_attr( wp_json_encode( $er_assistant_data ) ); ?>" hidden>
		<div class="drawer__panel">
			<div class="drawer__head">
				<h2 class="t-h3" id="assistant-title"><?php er_e( 'Trip assistant' ); ?></h2>
				<button class="icon-btn" type="button" data-close aria-label="<?php echo esc_attr( er_t( 'Close assistant' ) ); ?>"><?php echo er_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
			<div class="drawer__body assistant__log" data-assistant-log aria-live="polite">
				<p class="assistant__welcome" data-assistant-welcome><?php er_e( 'Hello! I can point you to the right pages on Egypt Roamer. Ask about a place, a trip or an experience.' ); ?></p>
			</div>
			<div class="assistant__foot">
				<p class="assistant__try" data-assistant-try><span><?php er_e( 'Try:' ); ?></span>
					<?php foreach ( [ 'Pyramids', 'Nile', 'Desert', 'Red Sea' ] as $er_chip ) : ?>
						<button type="button" class="assistant__chip" data-assistant-ask><?php echo esc_html( er_t( $er_chip ) ); ?></button>
					<?php endforeach; ?>
				</p>
				<form class="assistant__form" data-assistant-form>
					<?php // A visible label, not a placeholder: translated prompts are longer than the field on phones (and would be cut). ?>
					<label class="assistant__label" for="assistant-q" data-assistant-label><?php er_e( 'Ask about a place, a trip or an experience' ); ?></label>
					<input id="assistant-q" type="text" name="q" maxlength="300" autocomplete="off" required />
					<button class="btn btn--primary btn--sm" type="submit" data-assistant-submit><?php er_e( 'Search' ); ?></button>
				</form>
				<p class="assistant__note" data-assistant-note><?php echo esc_html( 'ai' === $er_assistant ? er_t( 'Answers are written by AI (Anthropic) from pages published on Egypt Roamer and can contain mistakes. Your question is sent to Anthropic; Egypt Roamer does not save it.' ) : er_t( 'Answers come only from pages published on Egypt Roamer. Your question is not saved.' ) ); ?></p>
				<?php if ( $er_chat ) : ?>
					<div class="assistant__human" data-chat-entry>
						<p><b><?php er_e( 'Need personal help?' ); ?></b> <span data-chat-avail><?php er_e( 'Have a question? Talk to our team.' ); ?></span></p>
						<button type="button" class="btn btn--outline btn--sm" data-chat-open><?php er_e( 'Chat with Egypt Roamer' ); ?></button>
					</div>
					<form class="assistant__start" data-chat-form hidden>
						<p class="assistant__start-intro"><?php er_e( 'Before we connect you with our team:' ); ?></p>
						<label><?php er_e( 'Name (optional)' ); ?> <input type="text" name="name" maxlength="80" autocomplete="name" /></label>
						<label><?php er_e( 'Email (optional, so we can also reply by email)' ); ?> <input type="email" name="email" maxlength="190" autocomplete="email" /></label>
						<label><?php er_e( 'Your message' ); ?> <textarea name="message" rows="3" maxlength="2000" required></textarea></label>
						<div class="er-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off" /></label></div>
						<p class="assistant__note"><?php echo esc_html( er_t( 'Your messages, and your name and email if you add them, are saved so our team can reply.' ) ); ?>
							<?php if ( $er_privacy_url ) : ?><a href="<?php echo esc_url( $er_privacy_url ); ?>"><?php er_e( 'Privacy Policy' ); ?></a><?php endif; ?></p>
						<p class="assistant__start-error" data-chat-error role="alert" hidden></p>
						<div class="assistant__start-actions">
							<button type="submit" class="btn btn--primary btn--sm"><?php er_e( 'Start chat' ); ?></button>
							<button type="button" class="btn btn--outline btn--sm" data-chat-cancel><?php er_e( 'Cancel' ); ?></button>
						</div>
					</form>
					<div class="assistant__chatbar" data-chat-bar hidden>
						<p class="assistant__chatstatus" data-chat-status role="status"></p>
						<button type="button" class="link" data-chat-human hidden><?php er_e( 'Ask for the team' ); ?></button>
						<button type="button" class="link" data-chat-end><?php er_e( 'End chat' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

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
