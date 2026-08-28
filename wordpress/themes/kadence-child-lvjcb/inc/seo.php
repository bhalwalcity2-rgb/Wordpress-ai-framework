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
 * Get the config slug the current page stands for.
 *
 * inc/cli.php writes lvjcb_location_slug / lvjcb_service_slug when it
 * provisions a page, but a page created by hand — or one provisioned
 * before that meta existed — has neither, and every SEO lookup keyed on
 * it then silently resolves to nothing. The page slug is the same value
 * by construction, so it is the fallback.
 *
 * @since 0.4.0
 *
 * @param string $meta_key 'lvjcb_location_slug' or 'lvjcb_service_slug'.
 * @return string
 */
function lvjcb_get_page_entity_slug( $meta_key ) {

	$post_id = get_the_ID();
	$slug    = (string) get_post_meta( $post_id, $meta_key, true );

	if ( '' !== $slug ) {
		return $slug;
	}

	return (string) get_post_field( 'post_name', $post_id );
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

		/*
		 * Deliberately not derived from the hero heading: that string
		 * carries the em-dash split marker hero.php uses to colour the
		 * second half, and it is written to be read on the page rather
		 * than to fit a ~60-character search result.
		 */
		$seo['title']       = $config['seo']['home_title'] ?? $config['hero']['heading'] . ' | ' . $business;
		$seo['description'] = $config['seo']['home_description'] ?? $config['hero']['description'];

	} elseif ( is_page() ) {

		$template = get_page_template_slug();
		$post_id  = get_the_ID();

		switch ( $template ) {

			case 'template-service.php':
				$slug    = lvjcb_get_page_entity_slug( 'lvjcb_service_slug' );
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
				$slug    = lvjcb_get_page_entity_slug( 'lvjcb_location_slug' );
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
	 * Output the JSON-LD graph for the current request.
	 *
	 * Only registered when no SEO plugin owns schema output — when one
	 * does, the same graph reaches the page through that plugin instead
	 * (see lvjcb_merge_schema_into_rank_math()) so the page never ends
	 * up with two competing ld+json blocks.
	 *
	 * @since 0.3.0
	 *
	 * @return void
	 */
	function lvjcb_render_schema() {

		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => lvjcb_get_schema_graph(),
			)
		) . '</script>' . "\n";
	}

	add_action( 'wp_head', 'lvjcb_render_open_graph_tags', 7 );

	/**
	 * Output Open Graph and Twitter Card tags, so a shared link renders
	 * with the page's own title, description, and hero image rather
	 * than whatever the receiving platform scrapes off the markup.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	function lvjcb_render_open_graph_tags() {

		$seo   = lvjcb_compute_seo();
		$title = $seo['title'] ? $seo['title'] : wp_get_document_title();
		$image = lvjcb_get_social_image_url();

		$tags = array(
			'og:type'        => is_front_page() ? 'website' : 'article',
			'og:site_name'   => lvjcb_get_config( 'business_name' ),
			'og:locale'      => get_locale(),
			'og:title'       => $title,
			'og:url'         => lvjcb_get_canonical_url(),
			'og:description' => $seo['description'],
			'og:image'       => $image,
		);

		foreach ( $tags as $property => $content ) {
			if ( $content ) {
				printf(
					'<meta property="%1$s" content="%2$s">' . "\n",
					esc_attr( $property ),
					esc_attr( $content )
				);
			}
		}

		printf(
			'<meta name="twitter:card" content="%s">' . "\n",
			$image ? 'summary_large_image' : 'summary'
		);
	}
}

if ( lvjcb_seo_plugin_active() ) {

	add_filter( 'rank_math/frontend/title', 'lvjcb_filter_rank_math_title' );

	/**
	 * Supply the computed title to Rank Math when the page has no
	 * hand-set one.
	 *
	 * Rank Math's own default for an untouched page is the bare post
	 * title ("Home - Business Name"), which carries none of the terms
	 * the page is actually about. This fills that gap while still
	 * yielding to anything typed into Rank Math's editor UI, so a
	 * hand-tuned title is never overwritten.
	 *
	 * @since 0.4.0
	 *
	 * @param string $title Title Rank Math computed.
	 * @return string
	 */
	function lvjcb_filter_rank_math_title( $title ) {

		if ( lvjcb_has_hand_set_seo( 'rank_math_title' ) ) {
			return $title;
		}

		$seo = lvjcb_compute_seo();

		return $seo['title'] ? $seo['title'] : $title;
	}

	add_filter( 'rank_math/frontend/description', 'lvjcb_filter_rank_math_description' );

	/**
	 * Supply the computed meta description to Rank Math when the page
	 * has no hand-set one.
	 *
	 * @since 0.4.0
	 *
	 * @param string $description Description Rank Math computed.
	 * @return string
	 */
	function lvjcb_filter_rank_math_description( $description ) {

		if ( lvjcb_has_hand_set_seo( 'rank_math_description' ) ) {
			return $description;
		}

		$seo = lvjcb_compute_seo();

		return $seo['description'] ? $seo['description'] : $description;
	}

	/*
	 * Rank Math has changed the property segment of its Open Graph
	 * filter names between versions ('og:title' vs 'og_title'), so both
	 * spellings are registered. Whichever one this install doesn't use
	 * simply never fires.
	 */
	foreach ( array( 'title', 'description', 'image' ) as $lvjcb_og_tag ) {
		foreach ( array( 'og:' . $lvjcb_og_tag, 'og_' . $lvjcb_og_tag ) as $lvjcb_og_property ) {
			add_filter(
				'rank_math/opengraph/facebook/' . $lvjcb_og_property,
				'lvjcb_filter_rank_math_og_' . $lvjcb_og_tag
			);
		}
	}
	unset( $lvjcb_og_tag, $lvjcb_og_property );

	/**
	 * Supply the computed title to Rank Math's og:title when empty.
	 *
	 * @since 0.4.0
	 *
	 * @param string $value Value Rank Math computed.
	 * @return string
	 */
	function lvjcb_filter_rank_math_og_title( $value ) {

		if ( $value || lvjcb_has_hand_set_seo( 'rank_math_facebook_title' ) ) {
			return $value;
		}

		$seo = lvjcb_compute_seo();

		return $seo['title'] ? $seo['title'] : $value;
	}

	/**
	 * Supply the computed description to Rank Math's og:description
	 * when empty.
	 *
	 * @since 0.4.0
	 *
	 * @param string $value Value Rank Math computed.
	 * @return string
	 */
	function lvjcb_filter_rank_math_og_description( $value ) {

		if ( $value || lvjcb_has_hand_set_seo( 'rank_math_facebook_description' ) ) {
			return $value;
		}

		$seo = lvjcb_compute_seo();

		return $seo['description'] ? $seo['description'] : $value;
	}

	/**
	 * Supply the hero image to Rank Math's og:image when none is set.
	 *
	 * @since 0.4.0
	 *
	 * @param mixed $value Value Rank Math computed.
	 * @return mixed
	 */
	function lvjcb_filter_rank_math_og_image( $value ) {

		if ( $value ) {
			return $value;
		}

		$image = lvjcb_get_social_image_url();

		return $image ? $image : $value;
	}

	add_action( 'rank_math/opengraph/facebook', 'lvjcb_render_rank_math_og_image', 50 );
	add_action( 'rank_math/opengraph/twitter', 'lvjcb_render_rank_math_og_image', 50 );

	/**
	 * Print og:image inside Rank Math's own Open Graph block.
	 *
	 * Rank Math only derives an image from the page's featured image or
	 * from a default set in its options. A page built from templates
	 * has neither, which is why the homepage was shipping with no
	 * og:image at all and links to it previewed as a bare URL.
	 *
	 * The image filters above are the tidier route, but the property
	 * segment of those filter names has moved between Rank Math
	 * versions, so this action is the reliable backstop. It runs inside
	 * the block Rank Math is already rendering, and is skipped whenever
	 * Rank Math has an image of its own to print.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	function lvjcb_render_rank_math_og_image() {

		static $done = false;

		if ( $done || lvjcb_rank_math_has_own_image() ) {
			return;
		}

		$image = lvjcb_get_social_image_url();

		if ( ! $image ) {
			return;
		}

		$done = true;

		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}

	/**
	 * Whether Rank Math already has an Open Graph image for this page,
	 * in which case this theme must not print a competing one.
	 *
	 * @since 0.4.0
	 *
	 * @return bool
	 */
	function lvjcb_rank_math_has_own_image() {

		if ( is_singular() && has_post_thumbnail() ) {
			return true;
		}

		if ( lvjcb_has_hand_set_seo( 'rank_math_facebook_image' ) ) {
			return true;
		}

		if ( class_exists( 'RankMath\Helper' ) && method_exists( 'RankMath\Helper', 'get_settings' ) ) {
			return (bool) RankMath\Helper::get_settings( 'titles.open_graph_image' );
		}

		return false;
	}

	add_filter( 'rank_math/json_ld', 'lvjcb_merge_schema_into_rank_math', 99, 2 );

	/**
	 * Merge this theme's graph into Rank Math's, rather than printing a
	 * second ld+json block alongside it.
	 *
	 * Also drops Rank Math's Article entity on the front page. Rank
	 * Math types every page as an Article by default, which is wrong
	 * for a local business landing page and competes with the
	 * LocalBusiness entity this theme emits.
	 *
	 * @since 0.4.0
	 *
	 * @param array $data   Rank Math's schema entities, keyed by id.
	 * @param mixed $jsonld Rank Math's JsonLD instance (unused).
	 * @return array
	 */
	function lvjcb_merge_schema_into_rank_math( $data, $jsonld ) {

		if ( is_front_page() ) {

			/*
			 * Matched on @type rather than on Rank Math's own array
			 * keys, which are an internal detail that has changed
			 * between versions.
			 */
			foreach ( $data as $key => $entity ) {

				$type = (array) ( $entity['@type'] ?? '' );

				if ( array_intersect( $type, array( 'Article', 'BlogPosting', 'NewsArticle' ) ) ) {
					unset( $data[ $key ] );
					continue;
				}

				/*
				 * Rank Math's placeholder entity describes the site
				 * owner, not the business. Point the WebPage at the
				 * business entity this theme emits instead, so the
				 * graph has one subject rather than two.
				 */
				if ( in_array( 'WebPage', $type, true ) ) {
					$data[ $key ]['about'] = array( '@id' => lvjcb_get_business_schema_id() );
				}
			}
		}

		foreach ( lvjcb_get_schema_graph() as $index => $entity ) {
			$data[ 'lvjcb_' . $index ] = $entity;
		}

		return $data;
	}
}

/**
 * Build the JSON-LD graph for the current request.
 *
 * Kept separate from output so the same graph can be printed directly
 * or handed to whichever SEO plugin owns schema on this install.
 *
 * @since 0.4.0
 *
 * @return array[]
 */
function lvjcb_get_schema_graph() {

	$graph    = array( lvjcb_get_local_business_schema() );
	$template = is_page() ? get_page_template_slug() : '';

	if ( is_front_page() ) {
		$graph[] = lvjcb_get_how_to_schema();
	}

	if ( 'template-service.php' === $template ) {
		$slug    = lvjcb_get_page_entity_slug( 'lvjcb_service_slug' );
		$service = lvjcb_find_by_slug( lvjcb_get_config( 'services' )['cards'], $slug );
		if ( $service ) {
			$graph[] = array(
				'@type'       => 'Service',
				'name'        => $service['heading'],
				'description' => $service['intro'] ?: $service['description'],
				'provider'    => array( '@id' => lvjcb_get_business_schema_id() ),
				'areaServed'  => lvjcb_get_areas_served(),
			);
		}
	}

	$faq_items = lvjcb_get_config( 'faq' )['items'] ?? array();
	if ( is_front_page() || 'template-faq.php' === $template ) {
		if ( $faq_items ) {
			$graph[] = lvjcb_get_faq_schema( $faq_items );
		}
	}

	if ( 'template-location.php' === $template ) {

		$slug     = lvjcb_get_page_entity_slug( 'lvjcb_location_slug' );
		$content  = function_exists( 'lvjcb_get_location_content' ) ? lvjcb_get_location_content( $slug ) : null;
		$location = lvjcb_find_by_slug( lvjcb_get_config( 'service_areas' )['items'], $slug );

		if ( $content && ! empty( $content['faq'] ) ) {
			$graph[] = lvjcb_get_faq_schema( $content['faq'] );
		}

		if ( $location ) {

			/*
			 * areaServed here is deliberately this one city rather than
			 * the full service-area list used on the homepage and the
			 * service pages: the point of a location page is to tell
			 * search engines which city this particular page is about.
			 */
			$graph[] = array(
				'@type'       => 'Service',
				'serviceType' => 'Junk Car Removal',
				'name'        => sprintf( 'Junk Car Removal in %s, %s', $location['city'], $location['state'] ),
				'description' => $content['seo_description'] ?? $location['intro'],
				'provider'    => array( '@id' => lvjcb_get_business_schema_id() ),
				'areaServed'  => array(
					'@type'          => 'City',
					'name'           => $location['city'],
					'containedInPlace' => array(
						'@type' => 'State',
						'name'  => $location['state'],
					),
				),
			);

			$graph[] = lvjcb_get_breadcrumb_schema( array(
				array( 'name' => 'Home', 'url' => home_url( '/' ) ),
				array( 'name' => 'Service Areas', 'url' => home_url( '/service-areas/' ) ),
				array( 'name' => $location['city'], 'url' => lvjcb_get_location_url( $location ) ),
			) );
		}
	}

	if ( 'template-service.php' === $template ) {
		$slug    = lvjcb_get_page_entity_slug( 'lvjcb_service_slug' );
		$content = function_exists( 'lvjcb_get_service_content' ) ? lvjcb_get_service_content( $slug ) : null;
		if ( $content && ! empty( $content['faq'] ) ) {
			$graph[] = lvjcb_get_faq_schema( $content['faq'] );
		}
	}

	return $graph;
}

/**
 * Whether the current page has an SEO field filled in by hand in the
 * plugin's editor UI, which this theme must not overwrite.
 *
 * @since 0.4.0
 *
 * @param string $meta_key Post meta key to check.
 * @return bool
 */
function lvjcb_has_hand_set_seo( $meta_key ) {

	if ( ! is_singular() ) {
		return false;
	}

	return '' !== trim( (string) get_post_meta( get_the_ID(), $meta_key, true ) );
}

/**
 * The stable @id for the business entity, so other entities can point
 * at it instead of restating the whole business inline.
 *
 * @since 0.4.0
 *
 * @return string
 */
function lvjcb_get_business_schema_id() {

	return home_url( '/#business' );
}

/**
 * The list of places the business serves, from the configured service
 * areas.
 *
 * @since 0.4.0
 *
 * @return array[]
 */
function lvjcb_get_areas_served() {

	$items = lvjcb_get_config( 'service_areas' )['items'] ?? array();
	$areas = array();

	foreach ( $items as $item ) {
		if ( empty( $item['city'] ) ) {
			continue;
		}
		$areas[] = array(
			'@type' => 'City',
			'name'  => trim( $item['city'] . ( ! empty( $item['state'] ) ? ', ' . $item['state'] : '' ) ),
		);
	}

	return $areas;
}

/**
 * The absolute URL of the image to represent this page when shared.
 *
 * @since 0.4.0
 *
 * @return string
 */
function lvjcb_get_social_image_url() {

	$slug = lvjcb_get_config( 'seo' )['og_image_slug'] ?? '';

	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		if ( $url ) {
			return $url;
		}
	}

	$id = $slug ? lvjcb_get_attachment_id_by_slug( $slug ) : 0;

	return $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
}

/**
 * Build HowTo schema from the configured "how it works" steps.
 *
 * @since 0.4.0
 *
 * @return array
 */
function lvjcb_get_how_to_schema() {

	$how   = lvjcb_get_config( 'how_it_works' ) ?? array();
	$steps = $how['steps'] ?? array();

	$schema = array(
		'@type' => 'HowTo',
		'name'  => $how['heading'] ?? '',
		'step'  => array(),
	);

	foreach ( $steps as $index => $step ) {
		$schema['step'][] = array(
			'@type'    => 'HowToStep',
			'position' => $index + 1,
			'name'     => $step['heading'] ?? '',
			'text'     => $step['description'] ?? '',
		);
	}

	return $schema;
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

	$parts = lvjcb_get_config( 'address_parts' ) ?? array();
	$image = lvjcb_get_social_image_url();

	$schema = array(
		'@type'       => lvjcb_get_config( 'schema_type' ) ?: 'LocalBusiness',
		'@id'         => lvjcb_get_business_schema_id(),
		'name'        => lvjcb_get_config( 'business_name' ),
		'description' => lvjcb_get_config( 'tagline' ),
		'telephone'   => lvjcb_get_phone_number( 'display' ),
		'email'       => lvjcb_get_config( 'email' ),
		'url'         => home_url( '/' ),
	);

	/*
	 * Fall back to the one-line address when the parts aren't filled
	 * in, so a site built from this theme without address_parts still
	 * produces a valid PostalAddress rather than an empty one.
	 */
	$schema['address'] = $parts
		? array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $parts['street'] ?? '',
			'addressLocality' => $parts['locality'] ?? '',
			'addressRegion'   => $parts['region'] ?? '',
			'postalCode'      => $parts['postal'] ?? '',
			'addressCountry'  => $parts['country'] ?? '',
		)
		: array(
			'@type'         => 'PostalAddress',
			'streetAddress' => lvjcb_get_config( 'address' ),
		);

	if ( $image ) {
		$schema['image'] = $image;
		$schema['logo']  = $image;
	}

	$price_range = lvjcb_get_config( 'price_range' );
	if ( $price_range ) {
		$schema['priceRange'] = $price_range;
	}

	$areas = lvjcb_get_areas_served();
	if ( $areas ) {
		$schema['areaServed'] = $areas;
	}

	$hours_spec = lvjcb_get_config( 'hours_spec' ) ?? array();
	if ( $hours_spec ) {
		$schema['openingHoursSpecification'] = array_map(
			function ( $spec ) {
				return array(
					'@type'     => 'OpeningHoursSpecification',
					'dayOfWeek' => $spec['days'],
					'opens'     => $spec['opens'],
					'closes'    => $spec['closes'],
				);
			},
			$hours_spec
		);
	} else {
		$schema['openingHours'] = lvjcb_get_config( 'hours' );
	}

	return $schema;
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
 * Build BreadcrumbList schema from an ordered trail.
 *
 * The site has no visible breadcrumb trail; this exists so search
 * results can show the page's place in the hierarchy
 * (Home > Service Areas > Henderson) rather than a bare URL.
 *
 * @since 0.4.0
 *
 * @param array $trail Ordered list of ['name' => string, 'url' => string].
 * @return array
 */
function lvjcb_get_breadcrumb_schema( $trail ) {

	$items = array();

	foreach ( $trail as $position => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position + 1,
			'name'     => $crumb['name'],
			'item'     => $crumb['url'],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
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
