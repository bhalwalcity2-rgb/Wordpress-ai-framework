<?php
/**
 * "Instant Offer" section — Peddle Publisher Embed.
 *
 * Renders the Peddle widget that lets visitors get an instant cash
 * offer for their vehicle directly on the page.
 *
 * @param array $args {
 *     @type string $id            Optional unique identifier.
 *     @type string $eyebrow       Optional eyebrow label.
 *     @type string $heading       Section heading text.
 *     @type string $intro         Optional supporting paragraph.
 *     @type string $publisher_id  Peddle publisher ID.
 * }
 */
$publisher_id = $args['publisher_id'] ?? '';

if ( empty( $publisher_id ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'instant-offer-' );
}
$heading_id  = $section_id . '-heading';
$widget_id   = $section_id . '-widget';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-section--dark lvjcb-instant-offer" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container lvjcb-instant-offer__inner">

		<div class="lvjcb-instant-offer__copy">
			<?php if ( $eyebrow ) : ?>
				<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
				<?php echo esc_html( $heading ); ?>
			</h2>

			<?php if ( $intro ) : ?>
				<p class="lvjcb-section__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>

		<div class="lvjcb-instant-offer__widget" id="<?php echo esc_attr( $widget_id ); ?>">
		</div>

	</div>
</section>
<script>
	PeddlePublisherEmbedConfig={publisherID:<?php echo wp_json_encode( $publisher_id ); ?>},function(){if("function"!=typeof window.PeddlePublisherEmbed){if(!window.PeddlePublisherEmbedConfig||!window.PeddlePublisherEmbedConfig.publisherID)throw new Error("Unable to bootstrap Peddle Publisher Embed");const e=(d,i)=>{e.queue.push({operation:d,options:i})};e.queue=[],window.PeddlePublisherEmbed=e;const d=()=>{const e=document.createElement("script");e.type="text/javascript",e.async=!0,e.src="https://publisher-embed.peddle.com/api/v1/embed/"+PeddlePublisherEmbedConfig.publisherID;const d=document.getElementsByTagName("script")[0];d.parentNode.insertBefore(e,d)};"complete"===document.readyState?d():window.addEventListener("load",d,!1)}}();
	window.PeddlePublisherEmbed('inline',{target:'#<?php echo esc_js( $widget_id ); ?>'});
</script>
