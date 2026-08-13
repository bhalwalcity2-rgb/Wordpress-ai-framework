<?php
/**
 * Footer section.
 *
 * Not part of the original frozen Sections 01-10 set — built now under
 * explicit authorization to proceed with a simple, project-specific
 * Footer using the same design language, rather than a full multi-round
 * freeze cycle. Utility content only, deliberately not another sales
 * moment.
 *
 * @param array $args {
 *     @type string $business_name Optional override. Defaults to the business config.
 *     @type string $tagline       Optional override. Defaults to the business config.
 *     @type string $address       Optional override. Defaults to the business config.
 *     @type string $email         Optional override. Defaults to the business config.
 * }
 */
$business_name = $args['business_name'] ?? lvjcb_get_config( 'business_name' );
$tagline       = $args['tagline'] ?? lvjcb_get_config( 'tagline' );
$address       = $args['address'] ?? lvjcb_get_config( 'address' );
$email         = $args['email'] ?? lvjcb_get_config( 'email' );

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
?>
<footer class="lvjcb-footer">
	<div class="lvjcb-section__container lvjcb-footer__grid">

		<div class="lvjcb-footer__brand">
			<span class="lvjcb-footer__logo">
				<?php echo lvjcb_icon( 'truck', array( 'size' => 18 ) ); ?>
				<?php echo esc_html( $business_name ); ?>
			</span>
			<?php if ( $tagline ) : ?>
				<p><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
		</div>

		<nav class="lvjcb-footer__col" aria-label="<?php esc_attr_e( 'Company', 'lvjcb' ); ?>">
			<p class="lvjcb-footer__col-title"><?php esc_html_e( 'Company', 'lvjcb' ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '<ul>%3$s</ul>',
						'depth'          => 1,
					)
				);
			} else {
				?>
				<ul>
					<?php foreach ( lvjcb_get_config( 'nav' ) ?? array() as $item ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/' . $item['slug'] . '/' ) ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php
			}
			?>
		</nav>

		<div class="lvjcb-footer__col">
			<p class="lvjcb-footer__col-title"><?php esc_html_e( 'Contact', 'lvjcb' ); ?></p>
			<ul>
				<li><a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone_display ); ?></a></li>
				<?php if ( $email ) : ?>
					<li><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
				<?php endif; ?>
				<?php if ( $address ) : ?>
					<li><?php echo esc_html( $address ); ?></li>
				<?php endif; ?>
			</ul>
		</div>

	</div>

	<div class="lvjcb-section__container lvjcb-footer__bottom">
		<span>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: current year, 2: business name. */
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
