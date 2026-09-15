<?php
/**
 * Written page body — the single renderer for any page driven by a
 * canonical v2 content file.
 *
 * Renders a location or service page from its content/{type}/{slug}.json
 * file instead of from the homepage sections. The section list in that
 * file drives the order and the headings, so the page structure lives
 * with the copy rather than being fixed here — which is the whole point
 * of writing a page rather than generating one.
 *
 * Nothing here knows whether it is drawing a location or a service. The
 * content contract is identical for both (see
 * docs/architecture/content-schema.md), so the caller supplies the one
 * piece that genuinely differs — the FAQ heading to fall back on — and
 * this file stays free of page-type branching.
 *
 * @param array $args {
 *     @type array  $content      Parsed content file for this page.
 *     @type string $page_slug    Page slug, used for unique section ids
 *                                and to look for a page-specific hero.
 *     @type array  $hero         Hero config, for the fallback image.
 *     @type string $faq_fallback Heading to use when the content file
 *                                sets no faq_heading of its own.
 * }
 */

$content      = $args['content'] ?? array();
$page_slug    = $args['page_slug'] ?? '';
$hero         = $args['hero'] ?? array();
$faq_fallback = $args['faq_fallback'] ?? '';

$sections = $content['sections'] ?? array();

/*
 * Prefer an image named for this city, so a page gets its own hero the
 * moment one is added to the manifest, and falls back to the shared
 * homepage hero until then.
 */
$hero_image_id = lvjcb_get_attachment_id_by_slug( 'hero-' . $page_slug . '-01' );
if ( ! $hero_image_id ) {
	$hero_image_id = lvjcb_get_attachment_id_by_slug( $hero['image_slug'] ?? '' );
}
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $content['hero_heading'] ?? '',
		'description' => $content['hero_description'] ?? '',
		'eyebrow'     => $hero['eyebrow'] ?? '',
		'image_id'    => $hero_image_id,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php get_template_part( 'template-parts/sections-loop', null, array(
		'sections'  => $sections,
		'id_prefix' => $page_slug,
	) ); ?>

	<?php
	/*
	 * Tier 1 components (ADR-0004). Purchased inventory and customer
	 * reviews are site-wide facts owned by business-config.php, so they
	 * render on every written page and are deliberately not expressible
	 * in a content file — a per-page review list is how fabricated
	 * reviews get written, which master-website-workflow.md Phase 07
	 * forbids outright.
	 *
	 * They sit here, after the page's own sections and before the FAQ,
	 * matching the order the homepage and the generic layouts use.
	 */
	$vehicles = lvjcb_get_config( 'vehicles' );

	get_template_part( 'template-parts/sections/recently-purchased-vehicles', null, array(
		'id'       => $page_slug . '-vehicles',
		'eyebrow'  => $vehicles['eyebrow'] ?? '',
		'heading'  => $vehicles['heading'] ?? '',
		'intro'    => $vehicles['intro'] ?? '',
		'vehicles' => array_map(
			function ( $vehicle ) {
				return array_merge(
					$vehicle,
					array( 'image_id' => lvjcb_get_attachment_id_by_slug( $vehicle['image_slug'] ?? '' ) )
				);
			},
			$vehicles['items'] ?? array()
		),
	) );

	$testimonials = lvjcb_get_config( 'testimonials' );

	get_template_part( 'template-parts/sections/testimonials', null, array(
		'id'               => $page_slug . '-testimonials',
		'eyebrow'          => $testimonials['eyebrow'] ?? '',
		'heading'          => $testimonials['heading'] ?? '',
		'aggregate_rating' => $testimonials['aggregate_rating'] ?? 0,
		'review_count'     => $testimonials['review_count'] ?? 0,
		'testimonials'     => $testimonials['items'] ?? array(),
	) );
	?>

	<?php
	/*
	 * Nearby service areas — a fallback, not a fixed slot.
	 *
	 * The generic location layout carried this block and written pages lost
	 * it, which cost every written page its sideways internal linking. It is
	 * navigation rather than content, so it belongs to the template; but a
	 * content file that places its own 'areas' section has said where it
	 * wants those links, and the template must not then repeat them. Pages
	 * keep control of their own structure (ADR-0004 Tier 3); this only
	 * covers the case where the page expressed no preference.
	 */
	$declares_areas = false;
	foreach ( $sections as $section ) {
		if ( 'areas' === ( $section['type'] ?? '' ) ) {
			$declares_areas = true;
			break;
		}
	}

	if ( ! $declares_areas ) {
		$nearby = lvjcb_get_service_area_cards( $page_slug );

		if ( $nearby ) {
			get_template_part( 'template-parts/sections/service-areas', null, array(
				'id'        => $page_slug . '-areas',
				'eyebrow'   => lvjcb_get_config( 'service_areas' )['eyebrow'] ?? '',
				'heading'   => __( 'We also collect from these areas', 'lvjcb' ),
				'locations' => $nearby,
			) );
		}
	}
	?>

	<?php if ( ! empty( $content['faq'] ) ) : ?>
		<?php get_template_part( 'template-parts/sections/faq', null, array(
			'id'      => $page_slug . '-faq',
			'eyebrow' => lvjcb_get_config( 'faq' )['eyebrow'] ?? '',
			'heading' => $content['faq_heading'] ?? $faq_fallback,
			'items'   => $content['faq'],
		) ); ?>
	<?php endif; ?>

	<?php
	/*
	 * final_cta is optional. Without a fallback the banner rendered with
	 * an empty <h2> that its own aria-labelledby pointed at — a heading
	 * with no accessible name. Content files that write their own
	 * closing CTA get it; the rest fall back to the configured one, the
	 * same banner the generic templates use.
	 */
	$final = $content['final_cta'] ?? array();

	$final_args = ! empty( $final['heading'] )
		? array(
			'id'       => $page_slug . '-late-page',
			'heading'  => $final['heading'],
			'intro'    => $final['intro'] ?? '',
			'cta_text' => lvjcb_get_phone_number( 'display' ),
			'cta_url'  => 'tel:' . lvjcb_get_phone_number( 'e164' ),
		)
		: lvjcb_get_cta_banner_args( $page_slug . '-late-page', 'late_page' );

	get_template_part( 'template-parts/sections/cta-banner', null, $final_args );
	?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>
