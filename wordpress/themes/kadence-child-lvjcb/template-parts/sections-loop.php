<?php
/**
 * Section loop — renders an ordered list of canonical v2 content sections.
 *
 * Extracted from written-page.php so the homepage can render the same
 * section types without a second copy of this dispatch. Any page that
 * holds canonical v2 sections renders them through here, which is what
 * keeps "the schema allows it" and "a template draws it" from drifting
 * apart (ADR-0003).
 *
 * Tier 1 components are deliberately absent — they are the page
 * template's responsibility, not a section's (ADR-0004).
 *
 * @param array $args {
 *     @type array  $sections  Ordered list of canonical v2 sections.
 *     @type string $id_prefix Prefix for the generated section ids, so two
 *                             pages never emit the same anchor.
 * }
 */

$sections  = $args['sections'] ?? array();
$id_prefix = $args['id_prefix'] ?? '';

if ( ! $sections ) {
	return;
}

$index = 0;

foreach ( $sections as $section ) :

	$index++;
	$section_id = $id_prefix . '-section-' . $index;
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

		case 'services':
			/*
			 * Content files name services by slug only; heading,
			 * description, icon, and URL are resolved from the config
			 * here, so a renamed or retired service cannot leave a
			 * stale card. An optional per-entry note replaces the
			 * configured description where the page has something of
			 * its own to say about that service.
			 */
			$cards = array();

			$chosen = $section['items'] ?? array();

			if ( ! $chosen ) {
				foreach ( lvjcb_get_config( 'services' )['cards'] ?? array() as $card ) {
					$chosen[] = array( 'slug' => $card['slug'] );
				}
			}

			foreach ( $chosen as $entry ) {

				$card = lvjcb_find_by_slug( lvjcb_get_config( 'services' )['cards'] ?? array(), $entry['slug'] ?? '' );

				if ( ! $card ) {
					continue;
				}

				$cards[] = array(
					'icon'        => $card['icon'] ?? '',
					'heading'     => $card['heading'],
					'description' => $entry['note'] ?? $card['description'],
					'url'         => lvjcb_get_service_url( $card['slug'] ),
				);
			}

			get_template_part( 'template-parts/sections/what-we-buy', null, array(
				'id'      => $section_id,
				'eyebrow' => $section['eyebrow'] ?? '',
				'heading' => $section['heading'] ?? '',
				'intro'   => $section['intro'] ?? '',
				'cards'   => $cards,
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
