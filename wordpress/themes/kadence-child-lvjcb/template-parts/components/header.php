<?php
/**
 * Header component.
 *
 * Two-tier navy header: top info bar (hours, email) visible on desktop,
 * main row with logo, nav, and phone CTA. Red accent border at bottom.
 */

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
$email         = lvjcb_get_config( 'email' );
$hours         = lvjcb_get_config( 'hours' );

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

	<div class="lvjcb-header__top">
		<div class="lvjcb-header__top-inner">
			<div class="lvjcb-header__top-info">
				<?php if ( $hours ) : ?>
					<span><?php echo esc_html( $hours ); ?></span>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php endif; ?>
			</div>
			<span><?php echo esc_html( lvjcb_get_config( 'hero' )['eyebrow'] ?? '' ); ?></span>
		</div>
	</div>

	<div class="lvjcb-header__row">

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lvjcb-header__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="lvjcb-header__logo-mark">LV</span>
				<span class="lvjcb-header__logo-text">
					<span class="lvjcb-header__logo-name"><?php bloginfo( 'name' ); ?></span>
					<span class="lvjcb-header__logo-sub"><?php esc_html_e( 'Cash for cars · Free towing', 'lvjcb' ); ?></span>
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

			<button type="button" class="lvjcb-btn lvjcb-btn--secondary lvjcb-btn--on-dark lvjcb-header__instant-offer" data-lvjcb-reveal-quote-form>
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
		<button type="button" class="lvjcb-btn lvjcb-btn--secondary lvjcb-btn--on-dark lvjcb-btn--block" data-lvjcb-reveal-quote-form>
			<?php esc_html_e( 'Instant Offer', 'lvjcb' ); ?>
		</button>
	</div>
</div>

<div class="lvjcb-header-sentinel" id="lvjcb-header-sentinel" aria-hidden="true"></div>
