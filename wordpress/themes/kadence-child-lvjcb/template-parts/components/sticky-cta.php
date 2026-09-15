<?php
/**
 * Sticky mobile call bar.
 *
 * On a phone the primary conversion for this business is a call, and until
 * now the only way to place one was to scroll back to the header or all the
 * way down to the footer. This pins both actions to the bottom of the
 * viewport on small screens: the call first because it converts, the online
 * offer second because it leaves the site.
 *
 * Hidden entirely at tablet width and above, where the header's own phone
 * link and CTA are always visible.
 *
 * Deliberately not a popup, an interstitial, or a timed offer — it is the
 * same two actions the page already offers, kept within thumb reach.
 */

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
$quote_url     = lvjcb_get_config( 'instant_quote_url' );

if ( ! $phone_display ) {
	return;
}
?>
<div class="lvjcb-sticky-cta" role="region" aria-label="<?php esc_attr_e( 'Contact actions', 'lvjcb' ); ?>">

	<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-sticky-cta__call">
		<?php echo lvjcb_icon( 'phone', array( 'size' => 20 ) ); ?>
		<span class="lvjcb-sticky-cta__label">
			<span class="lvjcb-sticky-cta__action"><?php esc_html_e( 'Call now', 'lvjcb' ); ?></span>
			<span class="lvjcb-sticky-cta__number"><?php echo esc_html( $phone_display ); ?></span>
		</span>
	</a>

	<?php if ( $quote_url ) : ?>
		<a href="<?php echo esc_url( $quote_url ); ?>" class="lvjcb-sticky-cta__quote" target="_blank" rel="noopener">
			<?php esc_html_e( 'Get offer', 'lvjcb' ); ?>
		</a>
	<?php endif; ?>

</div>
