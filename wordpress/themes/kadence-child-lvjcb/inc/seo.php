<?php
/**
 * SEO — Title, Meta Description, Canonical, JSON-LD Schema
 *
 * Fully derived from business-config.php and the current page's
 * template/meta — no page template needed editing to support this, and
 * no per-page SEO fields need adding to the config unless you want to
 * hand-tune one later.
 *
 * If Rank Math is active, this file steps back from title/description/
 * schema output (Rank Math owns that instead) and only ensures Rank
 * Math's own fields are pre-filled — see lvjcb_sync_rank_math_meta() in
 * inc/cli.php, called by the provisioning command.
 *
 * @package LVJCB
 * @since   0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Rank Math is active and should own title/description/schema
 * output instead of this file's native fallback.
 *
 * @since 0.3.0
 *
 * @return bool
 */
function lvjcb_seo_plugin_active() {

	return defined( 'RANK_MATH_VERSION' );
}

/**
 * Find one item in a list of config entries by its 'slug' key.
 *
 * @since 0.3.0
 *
 * @param array  $items List of associative arrays, each with a 'slug' key.
 * @param string $slug  Slug to find.
 * @return array|null The matching item, or null if not found.
 */
function lvjcb_find_by_slug( $items, $slug ) {

	foreach ( (array) $items as $item ) {
		if ( isset( $item['slug'] ) && $item['slug'] === $slug ) {
			return $item;
		}
	}

	return null;
}

/**
 * Truncate text to a maximum length at a word boundary, for meta
 * descriptions.
 *
 * @since 0.3.0
 *
 * @param string $text   Source text (may contain markup — stripped first).
 * @param int    $length Maximum character length.
 * @return string
 */
function lvjcb_truncate_for_seo( $text, $length = 155 ) {

	$text = trim( wp_strip_all_tags( $text ) );

	if ( mb_strlen( $text ) <= $length ) {
		return $text;
	}

	$truncated = mb_substr( $text, 0, $length - 1 );
	$last_space = mb_strrpos( $truncated, ' ' );

	if ( false !== $last_space ) {
		$truncated = mb_substr( $truncated, 0, $last_space );
	}

	return $truncated . '…';
}

/**
 * Compute the SEO title and description for whatever page is currently
 * being requested, derived entirely from business-config.php and the
 * page's own template/meta. Cached per request via a static.
 *
 * @since 0.3.0
 *
 * @return array{title: string, description: string}
 */
function lvjcb_compute_seo() {

	static $seo = null;

	if ( null !== $seo ) {
		return $seo;
	}

	$business = lvjcb_get_config( 'business_name' );
	$config   = lvjcb_get_config();
	$seo      = array(
		'title'       => '',
		'description' => '',
	);

	if ( is_front_page() ) {

		$seo['title']       = $config['hero']['heading'] . ' | ' . $business;
		$seo['description'] = $config['hero']['description'];

	} elseif ( is_page() ) {

		$template = get_page_template_slug();
		$post_id  = get_the_ID();

		switch ( $template ) {

			case 'template-service.php':
				$slug    = get_post_meta( $post_id, 'lvjcb_service_slug', true );
				$content = function_exists( 'lvjcb_get_service_content' ) ? lvjcb_get_service_content( $slug ) : null;
				if ( $content ) {
					$seo['title']       = $content['seo_title'];
					$seo['description'] = $content['seo_description'];
				} else {
					$service = lvjcb_find_by_slug( $config['services']['cards'], $slug );
					if ( $service ) {
						$seo['title']       = sprintf( __( '%s | %s', 'lvjcb' ), $service['heading'], $business );
						$seo['description'] = $service['intro'] ?: $service['description'];
					}
				}
				break;

			case 'template-location.php':
				$slug    = get_post_meta( $post_id, 'lvjcb_location_slug', true );
				$content = function_exists( 'lvjcb_get_location_content' ) ? lvjcb_get_location_content( $slug ) : null;
				if ( $content ) {
					$seo['title']       = $content['seo_title'];
					$seo['description'] = $content['seo_description'];
				} else {
					$location = lvjcb_find_by_slug( $config['service_areas']['items'], $slug );
					if ( $location ) {
						$label = trim( $location['city'] . ( $location['state'] ? ', ' . $location['state'] : '' ) );
						$seo['title']       = sprintf( __( 'We Buy Junk Cars in %1$s | %2$s', 'lvjcb' ), $label, $business );
						$seo['description'] = $location['intro'];
					}
				}
				break;

			case 'template-services-hub.php':
				$seo['title']       = $config['services']['heading'] . ' | ' . $business;
				$seo['description'] = $config['services']['intro'];
				break;

			case 'template-areas-hub.php':
				$seo['title']       = $config['service_areas']['heading'] . ' | ' . $business;
				$seo['description'] = $config['service_areas']['heading'];
				break;

			case 'template-about.php':
				/* translators: %s: business name. */
				$seo['title']       = sprintf( __( 'About Us | %s', 'lvjcb' ), $business );
				$seo['description'] = $config['about']['body'];
				break;

			case 'template-faq.php':
				/* translators: %s: business name. */
				$seo['title']       = sprintf( __( 'Frequently Asked Questions | %s', 'lvjcb' ), $business );
				$seo['description'] = $config['faq']['heading'];
				break;

			case 'template-contact.php':
				/* translators: %s: business name. */
				$seo['title']       = sprintf( __( 'Contact Us | %s', 'lvjcb' ), $business );
				/* translators: 1: business name, 2: business address. */
				$seo['description'] = sprintf( __( 'Contact %1$s — %2$s', 'lvjcb' ), $business, $config['address'] );
				break;
		}
	}

	if ( $seo['description'] ) {
		$seo['description'] = lvjcb_truncate_for_seo( $seo['description'] );
	}

	return $seo;
}

if ( ! lvjcb_seo_plugin_active() ) {

	add_filter( 'pre_get_document_title', 'lvjcb_filter_document_title' );

	/**
	 * Override the document title with the computed SEO title, when one exists.
	 *
	 * @since 0.3.0
	 *
	 * @param string $title WordPress's default computed title.
	 * @return string
	 */
	function lvjcb_filter_document_title( $title ) {

		$seo = lvjcb_compute_seo();

		return $seo['title'] ? $seo['title'] : $title;
	}

	add_action( 'wp_head', 'lvjcb_render_seo_meta_tags', 5 );

	/**
	 * Output the meta description and canonical URL.
	 *
	 * @since 0.3.0
	 *
	 * @return void
	 */
	function lvjcb_render_seo_meta_tags() {

		$seo = lvjcb_compute_seo();

		if ( $seo['description'] ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $seo['description'] ) );
		}

		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( lvjcb_get_canonical_url() ) );
	}

	add_action( 'wp_head', 'lvjcb_render_schema', 6 );

	/**
	 * Output JSON-LD schema: LocalBusiness on every page, plus
	 * Service/FAQPage where applicable.
	 *
	 * @since 0.3.0
	 *
	 * @return void
	 */
	function lvjcb_render_schema() {

		$graph = array( lvjcb_get_local_business_schema() );

		if ( is_page() && 'template-service.php' === get_page_template_slug() ) {
			$slug    = get_post_meta( get_the_ID(), 'lvjcb_service_slug', true );
			$service = lvjcb_find_by_slug( lvjcb_get_config( 'services' )['cards'], $slug );
			if ( $service ) {
				$graph[] = array(
					'@type'       => 'Service',
					'name'        => $service['heading'],
					'description' => $service['intro'] ?: $service['description'],
					'provider'    => array( '@type' => 'LocalBusiness', 'name' => lvjcb_get_config( 'business_name' ) ),
					'areaServed'  => 'Las Vegas, NV',
				);
			}
		}

		$faq_items = lvjcb_get_config( 'faq' )['items'] ?? array();
		if ( is_front_page() || ( is_page() && 'template-faq.php' === get_page_template_slug() ) ) {
			if ( $faq_items ) {
				$graph[] = lvjcb_get_faq_schema( $faq_items );
			}
		}

		if ( is_page() && 'template-location.php' === get_page_template_slug() ) {
			$slug    = get_post_meta( get_the_ID(), 'lvjcb_location_slug', true );
			$content = function_exists( 'lvjcb_get_location_content' ) ? lvjcb_get_location_content( $slug ) : null;
			if ( $content && ! empty( $content['faq'] ) ) {
				$graph[] = lvjcb_get_faq_schema( $content['faq'] );
			}
		}

		if ( is_page() && 'template-service.php' === get_page_template_slug() ) {
			$slug    = get_post_meta( get_the_ID(), 'lvjcb_service_slug', true );
			$content = function_exists( 'lvjcb_get_service_content' ) ? lvjcb_get_service_content( $slug ) : null;
			if ( $content && ! empty( $content['faq'] ) ) {
				$graph[] = lvjcb_get_faq_schema( $content['faq'] );
			}
		}

		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			)
		) . '</script>' . "\n";
	}
}

/**
 * Build the LocalBusiness schema array from business-config.php.
 *
 * Runs regardless of which SEO system owns title/meta output, since
 * lvjcb_get_local_business_schema() is also useful to call directly if
 * you later want to feed it into a plugin instead.
 *
 * @since 0.3.0
 *
 * @return array
 */
function lvjcb_get_local_business_schema() {

	return array(
		'@type'       => 'LocalBusiness',
		'name'        => lvjcb_get_config( 'business_name' ),
		'telephone'   => lvjcb_get_phone_number( 'display' ),
		'email'       => lvjcb_get_config( 'email' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => lvjcb_get_config( 'address' ),
		),
		'url'         => home_url( '/' ),
		'openingHours' => lvjcb_get_config( 'hours' ),
	);
}

/**
 * Build FAQPage schema from a list of FAQ items.
 *
 * @since 0.3.0
 *
 * @param array $items List of ['question' => string, 'answer' => string].
 * @return array
 */
function lvjcb_get_faq_schema( $items ) {

	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => array_map(
			function ( $item ) {
				return array(
					'@type'          => 'Question',
					'name'           => $item['question'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $item['answer'],
					),
				);
			},
			$items
		),
	);
}

/**
 * Get the canonical URL for the current request.
 *
 * @since 0.3.0
 *
 * @return string
 */
function lvjcb_get_canonical_url() {

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_page() ) {
		return get_permalink();
	}

	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}
