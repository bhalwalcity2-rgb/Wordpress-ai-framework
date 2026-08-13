<?php
/**
 * Trust Strip component.
 *
 * No $args — consumes lvjcb_get_trust_items() directly, the single
 * source of truth also intended for reuse by Hero's own badge row.
 */
$items = lvjcb_get_trust_items();

if ( empty( $items ) ) {
	return;
}
?>
<section class="lvjcb-trust-strip" aria-labelledby="lvjcb-trust-strip-heading">
	<h2 id="lvjcb-trust-strip-heading" class="screen-reader-text">
		<?php esc_html_e( 'Why homeowners trust us', 'lvjcb' ); ?>
	</h2>
	<ul class="lvjcb-trust-strip__list" role="list">
		<?php foreach ( $items as $item ) : ?>
			<li class="lvjcb-trust-strip__item">
				<?php echo lvjcb_icon( $item['icon'], array( 'class' => 'lvjcb-trust-strip__icon', 'size' => 20 ) ); ?>
				<span><?php echo esc_html( $item['label'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
