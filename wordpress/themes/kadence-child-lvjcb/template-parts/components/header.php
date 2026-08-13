<?php
/**
 * Header component.
 *
 * Niche Functionality Library — Junk Car Buyers: the phone CTA displays
 * the actual business phone number (never "Call Now"), paired with a
 * secondary "Instant Offer" CTA. Both are specific to this niche per
 * ai/memory/master-website-workflow.md and must not be copied into a
 * generic/global component pattern.
 *
 * No $args — Header has no per-instance configuration; all of its data
 * (phone number, nav links) comes from single sources of truth.
 */

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );

$nav_items = array_map(
	function ( $item ) {
		return array(
			'label' => $item['label'],
			'url'   => home_url( '/' . $item['slug'] . '/' ),
		);
	},
	lvjcb_get_config( 'nav' ) ?? array()
);
?>
<header class="lvjcb-header" id="lvjcb-header">
	<div class="lvjcb-header__row">

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lvjcb-header__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="lvjcb-header__logo-mark"><?php echo lvjcb_icon( 'truck', array( 'size' => 19 ) ); ?></span>
				<span class="lvjcb-header__logo-text">
					<?php bloginfo( 'name' ); ?>
					<span class="lvjcb-header__logo-sub"><?php esc_html_e( 'Cash for cars, same day', 'lvjcb' ); ?></span>
				</span>
			<?php endif; ?>
		</a>

		<nav class="lvjcb-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'lvjcb' ); ?>">
			<ul>
				<?php foreach ( $nav_items as $item ) : ?>
					<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="lvjcb-header__actions">

			<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-header__phone">
				<?php echo lvjcb_icon( 'phone', array( 'size' => 18 ) ); ?>
				<?php echo esc_html( $phone_display ); ?>
			</a>

			<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-header__phone-icon-only" aria-label="<?php echo esc_attr( $phone_display ); ?>">
				<?php echo lvjcb_icon( 'phone', array( 'size' => 20 ) ); ?>
			</a>

			<button type="button" class="lvjcb-btn lvjcb-btn--secondary lvjcb-header__instant-offer" data-lvjcb-reveal-quote-form>
				<?php esc_html_e( 'Instant Offer', 'lvjcb' ); ?>
			</button>

			<button type="button" class="lvjcb-header__hamburger" id="lvjcb-menu-open" aria-expanded="false" aria-controls="lvjcb-mobile-nav" aria-label="<?php esc_attr_e( 'Open menu', 'lvjcb' ); ?>">
				<?php echo lvjcb_icon( 'menu', array( 'size' => 22 ) ); ?>
			</button>

		</div>
	</div>
</header>

<div class="lvjcb-mobile-nav" id="lvjcb-mobile-nav" hidden>
	<div class="lvjcb-mobile-nav__head">
		<span class="lvjcb-mobile-nav__title"><?php esc_html_e( 'Menu', 'lvjcb' ); ?></span>
		<button type="button" class="lvjcb-mobile-nav__close" id="lvjcb-menu-close" aria-label="<?php esc_attr_e( 'Close menu', 'lvjcb' ); ?>">
			<?php echo lvjcb_icon( 'close', array( 'size' => 20 ) ); ?>
		</button>
	</div>
	<ul>
		<?php foreach ( $nav_items as $item ) : ?>
			<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<div class="lvjcb-mobile-nav__cta">
		<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-btn lvjcb-btn--primary lvjcb-btn--block">
			<?php echo esc_html( $phone_display ); ?>
		</a>
		<button type="button" class="lvjcb-btn lvjcb-btn--secondary lvjcb-btn--block" data-lvjcb-reveal-quote-form>
			<?php esc_html_e( 'Instant Offer', 'lvjcb' ); ?>
		</button>
	</div>
</div>

<div class="lvjcb-header-sentinel" id="lvjcb-header-sentinel" aria-hidden="true"></div>
