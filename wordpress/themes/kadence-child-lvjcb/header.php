<?php
/**
 * Theme header template (WordPress template hierarchy root file).
 *
 * Provides the HTML document skeleton and the site-wide header
 * component (logo, nav, phone, mobile nav drawer).
 *
 * @package LVJCB
 * @since   0.1.0
 */

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
$hours         = lvjcb_get_config( 'hours' );
$quote_url     = lvjcb_get_config( 'instant_quote_url' );
$nav_items = array_map(
	function ( $item ) {
		return array(
			'label' => $item['label'],
			'url'   => home_url( '/' . $item['slug'] . '/' ),
		);
	},
	lvjcb_get_config( 'nav' ) ?? array()
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="lvjcb-header" id="lvjcb-header" style="background:#111111;border-bottom:3px solid #FFB800">
	<div class="lvjcb-header__top" style="background:rgba(0,0,0,0.3);color:#9CA3AF">
		<div class="lvjcb-header__top-inner">
			<div class="lvjcb-header__top-info">
				<?php if ( $hours ) : ?>
					<span><?php echo esc_html( $hours ); ?></span>
				<?php endif; ?>
			</div>
			<span><?php echo esc_html( lvjcb_get_config( 'hero' )['eyebrow'] ?? '' ); ?></span>
		</div>
	</div>
	<div class="lvjcb-header__row">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lvjcb-header__logo" style="color:#F5F7F9;text-decoration:none">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="lvjcb-header__logo-mark">FC</span>
				<span class="lvjcb-header__logo-text">
					<span class="lvjcb-header__logo-name"><?php echo esc_html( lvjcb_get_config( 'business_name' ) ); ?></span>
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
			<?php if ( $quote_url ) : ?>
				<a href="<?php echo esc_url( $quote_url ); ?>" class="lvjcb-btn lvjcb-btn--gold lvjcb-header__instant-offer" target="_blank" rel="noopener">
					<?php esc_html_e( 'Instant Offer', 'lvjcb' ); ?>
				</a>
			<?php endif; ?>
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
		<?php if ( $quote_url ) : ?>
			<a href="<?php echo esc_url( $quote_url ); ?>" class="lvjcb-btn lvjcb-btn--secondary lvjcb-btn--on-dark lvjcb-btn--block" target="_blank" rel="noopener">
				<?php esc_html_e( 'Instant Offer', 'lvjcb' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>

<div class="lvjcb-header-sentinel" id="lvjcb-header-sentinel" aria-hidden="true"></div>
