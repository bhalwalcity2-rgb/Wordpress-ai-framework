<?php
/**
 * WP-CLI Provisioning Command
 *
 * `wp lvjcb provision` reads inc/business-config.php and content files
 * to create or update every WordPress Page this theme needs —
 * idempotently, so running it again after adding new locations or
 * services only creates the new pages and updates existing ones.
 *
 * @package LVJCB
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Provisions all WordPress Pages for the current business config.
 *
 * @since 0.2.0
 */
class LVJCB_Provision_Command {

	/**
	 * Create or update every page this theme needs, from business-config.php
	 * and content JSON files.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lvjcb provision
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {

		$config   = lvjcb_get_config();
		$business = $config['business_name'];

		WP_CLI::log( 'Provisioning pages for: ' . $business );

		// Homepage.
		$home_id = $this->upsert_page( 'home', __( 'Home', 'lvjcb' ), 0, '', array(
			'title'       => $config['hero']['heading'] . ' | ' . $business,
			'description' => $config['hero']['description'],
		) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );

		// Hub pages.
		$services_hub_id = $this->upsert_page( 'cash-for-junk-cars', $config['services']['heading'], 0, 'template-services-hub.php', array(
			'title'       => $config['services']['heading'] . ' | ' . $business,
			'description' => $config['services']['intro'],
		) );
		$areas_hub_id = $this->upsert_page( 'service-areas', $config['service_areas']['heading'], 0, 'template-areas-hub.php', array(
			'title'       => $config['service_areas']['heading'] . ' | ' . $business,
			'description' => $config['service_areas']['heading'],
		) );

		// Standalone pages.
		$this->upsert_page( 'about', $config['about']['heading'], 0, 'template-about.php', array(
			'title'       => sprintf( __( 'About Us | %s', 'lvjcb' ), $business ),
			'description' => $config['about']['body'],
		) );
		$this->upsert_page( 'faq', $config['faq']['heading'], 0, 'template-faq.php', array(
			'title'       => sprintf( __( 'Frequently Asked Questions | %s', 'lvjcb' ), $business ),
			'description' => $config['faq']['heading'],
		) );
		$this->upsert_page( 'contact', $config['contact']['heading'], 0, 'template-contact.php', array(
			'title'       => sprintf( __( 'Contact Us | %s', 'lvjcb' ), $business ),
			'description' => sprintf( __( 'Contact %1$s - %2$s', 'lvjcb' ), $business, $config['address'] ),
		) );

		// Service pages.
		foreach ( $config['services']['cards'] as $service ) {
			$content = lvjcb_get_service_content( $service['slug'] );
			$seo = array(
				'title'       => $content['seo_title'] ?? ( $service['heading'] . ' | ' . $business ),
				'description' => $content['seo_description'] ?? ( $service['intro'] ?: $service['description'] ),
			);

			$id = $this->upsert_page( $service['slug'], $service['heading'], $services_hub_id, 'template-service.php', $seo );
			update_post_meta( $id, 'lvjcb_service_slug', $service['slug'] );
			$this->maybe_set_image( $id, $service['image_slug'] ?? '' );

			$has_content = ! empty( $content['sections'] );
			WP_CLI::log( $has_content ? '  + Content file loaded' : '  - No content file (using config fallback)' );
		}

		// Location pages.
		foreach ( $config['service_areas']['items'] as $location ) {
			$label   = trim( $location['city'] . ( $location['state'] ? ', ' . $location['state'] : '' ) );
			$content = lvjcb_get_location_content( $location['slug'] );
			$seo = array(
				'title'       => $content['seo_title'] ?? sprintf( __( 'We Buy Junk Cars in %1$s | %2$s', 'lvjcb' ), $label, $business ),
				'description' => $content['seo_description'] ?? $location['intro'],
			);

			$id = $this->upsert_page( $location['slug'], $label, $areas_hub_id, 'template-location.php', $seo );
			update_post_meta( $id, 'lvjcb_location_slug', $location['slug'] );

			$has_content = ! empty( $content['sections'] );
			WP_CLI::log( $has_content ? '  + Content file loaded' : '  - No content file (using config fallback)' );
		}

		flush_rewrite_rules();

		if ( defined( 'RANK_MATH_VERSION' ) ) {
			WP_CLI::log( "Rank Math detected - rank_math_title/rank_math_description were set on every page." );
		} else {
			WP_CLI::log( "Rank Math not detected - this theme's own native SEO output (inc/seo.php) is handling title/description/schema instead." );
		}

		$loc_count = count( $config['service_areas']['items'] );
		$svc_count = count( $config['services']['cards'] );
		WP_CLI::success( "Provisioning complete: {$loc_count} locations, {$svc_count} services, 5 standalone pages." );
	}

	/**
	 * Create a page if it doesn't exist (matched by slug), or update it.
	 *
	 * @param string $slug     Page slug.
	 * @param string $title    Page title.
	 * @param int    $parent   Parent page ID, 0 for top-level.
	 * @param string $template Page template filename, or '' for default.
	 * @param array  $seo      Optional array{title: string, description: string}.
	 * @return int The page ID.
	 */
	private function upsert_page( $slug, $title, $parent, $template = '', $seo = array() ) {

		$existing = get_page_by_path( $slug, OBJECT, 'page' );

		$post_data = array(
			'post_title'  => $title,
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_parent' => $parent,
		);

		if ( $existing ) {
			$post_data['ID'] = $existing->ID;
			$id               = wp_update_post( $post_data, true );
		} else {
			$id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $id ) ) {
			WP_CLI::error( "Failed to provision /{$slug}/: " . $id->get_error_message(), false );
			return 0;
		}

		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		} else {
			delete_post_meta( $id, '_wp_page_template' );
		}

		if ( defined( 'RANK_MATH_VERSION' ) ) {
			if ( ! empty( $seo['title'] ) ) {
				update_post_meta( $id, 'rank_math_title', $seo['title'] );
			}
			if ( ! empty( $seo['description'] ) ) {
				update_post_meta( $id, 'rank_math_description', lvjcb_truncate_for_seo( $seo['description'] ) );
			}
		}

		WP_CLI::log( ( $existing ? 'Updated' : 'Created' ) . ": /{$slug}/ (ID {$id})" );

		return (int) $id;
	}

	/**
	 * Resolve an image slug to a media library attachment ID and store it.
	 *
	 * @param int    $post_id    Page ID.
	 * @param string $image_slug Image slug to resolve.
	 */
	private function maybe_set_image( $post_id, $image_slug ) {

		if ( ! $image_slug ) {
			return;
		}

		$attachment_id = lvjcb_get_attachment_id_by_slug( $image_slug );

		if ( $attachment_id ) {
			update_post_meta( $post_id, 'lvjcb_image_id', $attachment_id );
		}
	}
}

WP_CLI::add_command( 'lvjcb provision', 'LVJCB_Provision_Command' );
