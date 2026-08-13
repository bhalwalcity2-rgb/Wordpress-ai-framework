<?php
/**
 * Footer section.
 *
 * Four-column layout: brand, services, service areas, company/contact.
 * Deep navy background with gold link hovers.
 */
$business_name = $args['business_name'] ?? lvjcb_get_config( 'business_name' );
$tagline       = $args['tagline'] ?? lvjcb_get_config( 'tagline' );
$address       = $args['address'] ?? lvjcb_get_config( 'address' );
$email         = $args['email'] ?? lvjcb_get_config( 'email' );

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );

$services      = lvjcb_get_config( 'services' );
$service_areas = lvjcb_get_config( 'service_areas' );
?>
<footer class="lvjcb-footer">
	<div class="lvjcb-section__container lvjcb-footer__grid">

		<div class="lvjcb-footer__brand">
			<span class="lvjcb-footer__logo">
				<span class="lvjcb-footer__logo-mark">LV</span>
				<?php echo esc_html( $business_name ); ?>
			</span>
			<?php if ( $tagline ) : ?>
				<p><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
		</div>

		<div class="lvjcb-footer__col">
			<p class="lvjcb-footer__col-title"><?php esc_html_e( 'Services', 'lvjcb' ); ?></p>
			<ul>
				<?php if ( ! empty( $services['cards'] ) ) : ?>
					<?php foreach ( $services['cards'] as $card ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/cash-for-junk-cars/' . $card['slug'] . '/' ) ); ?>"><?php echo esc_html( $card['heading'] ); ?></a></li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>

		<div class="lvjcb-footer__col">
			<p class="lvjcb-footer__col-title"><?php esc_html_e( 'Service Areas', 'lvjcb' ); ?></p>
			<ul>
				<?php if ( ! empty( $service_areas['items'] ) ) : ?>
					<?php foreach ( $service_areas['items'] as $area ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/service-areas/' . $area['slug'] . '/' ) ); ?>"><?php echo esc_html( $area['city'] ); ?></a></li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>

		<div class="lvjcb-footer__col">
			<p class="lvjcb-footer__col-title"><?php esc_html_e( 'Company', 'lvjcb' ); ?></p>
			<ul>
				<?php foreach ( lvjcb_get_config( 'nav' ) ?? array() as $item ) : ?>
					<li><a href="<?php echo esc_url( home_url( '/' . $item['slug'] . '/' ) ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone_display ); ?></a></li>
				<?php if ( $email ) : ?>
					<li><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

	</div>

	<div class="lvjcb-section__container lvjcb-footer__bottom">
		<span>
			<?php
			echo esc_html(
				sprintf(
					__( '© %1$s %2$s. All rights reserved.', 'lvjcb' ),
					gmdate( 'Y' ),
					$business_name
				)
			);
			?>
		</span>
		<span class="lvjcb-footer__legal">
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'lvjcb' ); ?></a>
			·
			<a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'lvjcb' ); ?></a>
		</span>
	</div>
</footer>
