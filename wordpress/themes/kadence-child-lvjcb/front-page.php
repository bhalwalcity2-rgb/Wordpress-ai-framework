<?php
/**
 * Homepage template.
 *
 * Assembles Header, Hero, and Sections in the exact frozen order from
 * the Master Website Creation Workflow. Every piece of content below
 * comes from lvjcb_get_config() — this file has no business-specific
 * data of its own, so it does not need to change when this theme is
 * reused for a different business. Only inc/business-config.php does.
 *
 * @package LVJCB
 */

get_header();

$hero          = lvjcb_get_config( 'hero' );
$services      = lvjcb_get_config( 'services' );
$how_it_works  = lvjcb_get_config( 'how_it_works' );
$why_choose_us = lvjcb_get_config( 'why_choose_us' );
$vehicles      = lvjcb_get_config( 'vehicles' );
$testimonials  = lvjcb_get_config( 'testimonials' );
$service_areas = lvjcb_get_config( 'service_areas' );
$faq           = lvjcb_get_config( 'faq' );

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
$email         = lvjcb_get_config( 'email' );
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
?>

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

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $hero['heading'],
		'description' => $hero['description'],
		'image_id'    => lvjcb_get_attachment_id_by_slug( $hero['image_slug'] ?? '' ),
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php get_template_part( 'template-parts/sections/what-we-buy', null, array(
		'eyebrow' => $services['eyebrow'],
		'heading' => $services['heading'],
		'intro'   => $services['intro'],
		'cards'   => array_map(
			function ( $card ) {
				return array(
					'icon'        => $card['icon'],
					'heading'     => $card['heading'],
					'description' => $card['description'],
					'url'         => home_url( '/cash-for-junk-cars/' . $card['slug'] . '/' ),
				);
			},
			$services['cards']
		),
	) ); ?>

	<?php
	$steps = array();
	foreach ( $how_it_works['steps'] as $index => $step ) {
		$steps[] = array_merge( $step, array( 'step_number' => $index + 1 ) );
	}
	get_template_part( 'template-parts/sections/how-it-works', null, array(
		'eyebrow' => $how_it_works['eyebrow'],
		'heading' => $how_it_works['heading'],
		'intro'   => $how_it_works['intro'],
		'steps'   => $steps,
	) );
	?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'home-mid-page', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/why-choose-us', null, array(
		'eyebrow' => $why_choose_us['eyebrow'],
		'heading' => $why_choose_us['heading'],
		'intro'   => $why_choose_us['intro'],
		'cards'   => $why_choose_us['cards'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/recently-purchased-vehicles', null, array(
		'eyebrow'  => $vehicles['eyebrow'],
		'heading'  => $vehicles['heading'],
		'intro'    => $vehicles['intro'],
		'vehicles' => array_map(
			function ( $vehicle ) {
				return array_merge(
					$vehicle,
					array( 'image_id' => lvjcb_get_attachment_id_by_slug( $vehicle['image_slug'] ?? '' ) )
				);
			},
			$vehicles['items']
		),
	) ); ?>

	<?php get_template_part( 'template-parts/sections/testimonials', null, array(
		'eyebrow'      => $testimonials['eyebrow'],
		'heading'      => $testimonials['heading'],
		'testimonials' => $testimonials['items'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/service-areas', null, array(
		'eyebrow'   => $service_areas['eyebrow'],
		'heading'   => $service_areas['heading'],
		'locations' => array_map(
			function ( $location ) {
				return array(
					'city'  => $location['city'],
					'state' => $location['state'],
					'url'   => home_url( '/service-areas/' . $location['slug'] . '/' ),
				);
			},
			$service_areas['items']
		),
	) ); ?>

	<?php get_template_part( 'template-parts/sections/faq', null, array(
		'eyebrow' => $faq['eyebrow'],
		'heading' => $faq['heading'],
		'items'   => $faq['items'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'home-late-page', 'late_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>

<?php get_footer(); ?>
