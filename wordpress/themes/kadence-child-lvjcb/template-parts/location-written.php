<?php
/**
 * Written location page body.
 *
 * Renders a location page from its content/locations/{slug}.json file
 * instead of from the homepage sections. The section list in that file
 * drives the order and the headings, so the page structure lives with
 * the copy rather than being fixed here — which is the whole point of
 * writing a city page rather than generating one.
 *
 * @param array $args {
 *     @type array  $content   Parsed content file for this location.
 *     @type string $page_slug Location slug, used for unique section ids.
 *     @type string $city      City name.
 *     @type array  $hero      Hero config, for the fallback background image.
 * }
 */

$content   = $args['content'] ?? array();
$page_slug = $args['page_slug'] ?? '';
$city      = $args['city'] ?? '';
$hero      = $args['hero'] ?? array();

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
		'image_id'    => $hero_image_id,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php
	$index = 0;

	foreach ( $sections as $section ) :

		$index++;
		$section_id = $page_slug . '-section-' . $index;
		$type       = $section['type'] ?? 'content';

		switch ( $type ) {

			case 'steps':
				$steps = array();
				foreach ( $section['steps'] ?? array() as $position => $step ) {
					$steps[] = array_merge( $step, array( 'step_number' => $position + 1 ) );
				}
				get_template_part( 'template-parts/sections/how-it-works', null, array(
					'id'      => $section_id,
					'eyebrow' => $section['eyebrow'] ?? '',
					'heading' => $section['heading'] ?? '',
					'intro'   => $section['intro'] ?? '',
					'steps'   => $steps,
				) );
				break;

			case 'cta':
				get_template_part( 'template-parts/sections/cta-banner', null, array(
					'id'       => $section_id,
					'heading'  => $section['heading'] ?? '',
					'intro'    => $section['intro'] ?? '',
					'cta_text' => lvjcb_get_phone_number( 'display' ),
					'cta_url'  => 'tel:' . lvjcb_get_phone_number( 'e164' ),
				) );
				break;

			case 'areas':
				/*
				 * Content files name nearby cities by slug only; the city,
				 * state, and URL are resolved from the config here so a
				 * renamed or retired service area cannot leave a stale link.
				 */
				$locations = array();

				foreach ( $section['items'] ?? array() as $entry ) {
					$area = lvjcb_get_location_by_slug( $entry['slug'] ?? '' );
					if ( ! $area ) {
						continue;
					}
					$locations[] = array(
						'city'        => $area['city'],
						'state'       => $area['state'],
						'description' => $entry['note'] ?? '',
						'url'         => lvjcb_get_location_url( $area ),
					);
				}

				get_template_part( 'template-parts/sections/service-areas', null, array(
					'id'        => $section_id,
					'eyebrow'   => $section['eyebrow'] ?? '',
					'heading'   => $section['heading'] ?? '',
					'intro'     => $section['intro'] ?? '',
					'locations' => $locations,
				) );
				break;

			default:
				get_template_part( 'template-parts/sections/rich-content', null, array(
					'id'         => $section_id,
					'eyebrow'    => $section['eyebrow'] ?? '',
					'heading'    => $section['heading'] ?? '',
					'intro'      => $section['intro'] ?? '',
					'paragraphs' => $section['paragraphs'] ?? array(),
					'blocks'     => $section['blocks'] ?? array(),
					'table'      => $section['table'] ?? array(),
					'footnote'   => $section['footnote'] ?? '',
					'image'      => $section['image'] ?? array(),
					'variant'    => $section['variant'] ?? ( 0 === $index % 2 ? 'alt' : '' ),
				) );
		}

	endforeach;
	?>

	<?php if ( ! empty( $content['faq'] ) ) : ?>
		<?php get_template_part( 'template-parts/sections/faq', null, array(
			'id'      => $page_slug . '-faq',
			'eyebrow' => lvjcb_get_config( 'faq' )['eyebrow'] ?? '',
			'heading' => $content['faq_heading'] ?? sprintf(
				/* translators: %s: city name. */
				__( 'Questions %s Residents Ask', 'lvjcb' ),
				$city
			),
			'items'   => $content['faq'],
		) ); ?>
	<?php endif; ?>

	<?php
	$final = $content['final_cta'] ?? array();
	get_template_part( 'template-parts/sections/cta-banner', null, array(
		'id'       => $page_slug . '-late-page',
		'heading'  => $final['heading'] ?? '',
		'intro'    => $final['intro'] ?? '',
		'cta_text' => lvjcb_get_phone_number( 'display' ),
		'cta_url'  => 'tel:' . lvjcb_get_phone_number( 'e164' ),
	) );
	?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>
