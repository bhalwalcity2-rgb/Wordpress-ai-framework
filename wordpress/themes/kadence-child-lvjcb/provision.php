<?php
/**
 * Page provisioning script — fixes page hierarchy and internal links.
 * Visit this URL while logged in as admin:
 * https://junkcarbuyerslasvegas.com/wp-content/themes/kadence-child-lvjcb/provision.php
 *
 * DELETE THIS FILE after running it.
 */

require_once dirname( __FILE__ ) . '/../../../wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You must be logged in as an administrator to run this script.' );
}

header( 'Content-Type: text/html; charset=utf-8' );
echo '<html><head><title>LVJCB Provisioning — Fix</title>';
echo '<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px;line-height:1.6}';
echo '.ok{color:#16a34a}.skip{color:#d97706}.del{color:#dc2626}h1{color:#1C3144}code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.9em}</style>';
echo '</head><body>';
echo '<h1>Las Vegas Junk Car Buyers — Fix Page Hierarchy</h1>';

// Step 1: Ensure hub pages exist and get their IDs
$hubs = array(
	'cash-for-junk-cars' => array(
		'title'    => 'Cash for Junk Cars',
		'template' => 'template-services-hub.php',
	),
	'service-areas' => array(
		'title'    => 'Service Areas',
		'template' => 'template-areas-hub.php',
	),
);

$hub_ids = array();
foreach ( $hubs as $slug => $hub ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		$hub_ids[ $slug ] = $existing->ID;
		update_post_meta( $existing->ID, '_wp_page_template', $hub['template'] );
		echo '<p class="skip">⟳ Hub: <strong>' . $hub['title'] . '</strong> — exists (ID ' . $existing->ID . ')</p>';
	} else {
		$id = wp_insert_post( array(
			'post_title'  => $hub['title'],
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
		update_post_meta( $id, '_wp_page_template', $hub['template'] );
		$hub_ids[ $slug ] = $id;
		echo '<p class="ok">✓ Hub: <strong>' . $hub['title'] . '</strong> — created (ID ' . $id . ')</p>';
	}
}

// Step 2: Delete Las Vegas location page
$lv_page = get_page_by_path( 'las-vegas' );
if ( ! $lv_page ) {
	$lv_page = get_page_by_path( 'service-areas/las-vegas' );
}
if ( $lv_page ) {
	wp_delete_post( $lv_page->ID, true );
	echo '<p class="del">✗ Deleted: <strong>Las Vegas location page</strong> (homepage covers Las Vegas)</p>';
} else {
	echo '<p class="skip">⟳ Las Vegas location page — already removed</p>';
}

// Step 3: Create/update service pages as children of "Cash for Junk Cars"
$service_pages = array(
	array( 'title' => 'Cash for Junk Sedans & Coupes in Las Vegas', 'slug' => 'sedans', 'template' => 'template-service.php' ),
	array( 'title' => 'Cash for Junk Trucks & SUVs in Las Vegas', 'slug' => 'trucks-suvs', 'template' => 'template-service.php' ),
	array( 'title' => 'Cash for Accident & Storm-Damaged Cars in Las Vegas', 'slug' => 'damaged', 'template' => 'template-service.php' ),
	array( 'title' => 'Cash for Non-Running Cars in Las Vegas', 'slug' => 'non-running', 'template' => 'template-service.php' ),
);

echo '<h2>Service Pages (under /cash-for-junk-cars/)</h2>';
foreach ( $service_pages as $page ) {
	// Check if exists at flat URL
	$existing = get_page_by_path( $page['slug'] );
	// Also check if already nested
	if ( ! $existing ) {
		$existing = get_page_by_path( 'cash-for-junk-cars/' . $page['slug'] );
	}

	if ( $existing ) {
		wp_update_post( array(
			'ID'          => $existing->ID,
			'post_parent' => $hub_ids['cash-for-junk-cars'],
			'post_name'   => $page['slug'],
		) );
		update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
		echo '<p class="ok">✓ <strong>' . esc_html( $page['title'] ) . '</strong> — moved to <code>/cash-for-junk-cars/' . $page['slug'] . '/</code></p>';
	} else {
		$id = wp_insert_post( array(
			'post_title'  => $page['title'],
			'post_name'   => $page['slug'],
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_parent' => $hub_ids['cash-for-junk-cars'],
		) );
		update_post_meta( $id, '_wp_page_template', $page['template'] );
		echo '<p class="ok">✓ <strong>' . esc_html( $page['title'] ) . '</strong> — created at <code>/cash-for-junk-cars/' . $page['slug'] . '/</code></p>';
	}
}

// Step 4: Create/update location pages as children of "Service Areas"
$location_pages = array(
	array( 'title' => 'Junk Car Buyers Henderson, NV', 'slug' => 'henderson', 'template' => 'template-location.php' ),
	array( 'title' => 'Junk Car Buyers North Las Vegas, NV', 'slug' => 'north-las-vegas', 'template' => 'template-location.php' ),
	array( 'title' => 'Junk Car Buyers Summerlin, NV', 'slug' => 'summerlin', 'template' => 'template-location.php' ),
	array( 'title' => 'Junk Car Buyers Spring Valley, NV', 'slug' => 'spring-valley', 'template' => 'template-location.php' ),
	array( 'title' => 'Junk Car Buyers Paradise, NV', 'slug' => 'paradise', 'template' => 'template-location.php' ),
	array( 'title' => 'Junk Car Buyers Enterprise, NV', 'slug' => 'enterprise', 'template' => 'template-location.php' ),
	array( 'title' => 'Junk Car Buyers Boulder City, NV', 'slug' => 'boulder-city', 'template' => 'template-location.php' ),
);

echo '<h2>Location Pages (under /service-areas/)</h2>';
foreach ( $location_pages as $page ) {
	$existing = get_page_by_path( $page['slug'] );
	if ( ! $existing ) {
		$existing = get_page_by_path( 'service-areas/' . $page['slug'] );
	}

	if ( $existing ) {
		wp_update_post( array(
			'ID'          => $existing->ID,
			'post_parent' => $hub_ids['service-areas'],
			'post_name'   => $page['slug'],
		) );
		update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
		echo '<p class="ok">✓ <strong>' . esc_html( $page['title'] ) . '</strong> — moved to <code>/service-areas/' . $page['slug'] . '/</code></p>';
	} else {
		$id = wp_insert_post( array(
			'post_title'  => $page['title'],
			'post_name'   => $page['slug'],
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_parent' => $hub_ids['service-areas'],
		) );
		update_post_meta( $id, '_wp_page_template', $page['template'] );
		echo '<p class="ok">✓ <strong>' . esc_html( $page['title'] ) . '</strong> — created at <code>/service-areas/' . $page['slug'] . '/</code></p>';
	}
}

// Step 5: Standalone pages
$standalone = array(
	array( 'title' => 'About', 'slug' => 'about', 'template' => 'template-about.php' ),
	array( 'title' => 'FAQ', 'slug' => 'faq', 'template' => 'template-faq.php' ),
	array( 'title' => 'Contact', 'slug' => 'contact', 'template' => 'template-contact.php' ),
);

echo '<h2>Standalone Pages</h2>';
foreach ( $standalone as $page ) {
	$existing = get_page_by_path( $page['slug'] );
	if ( $existing ) {
		update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
		echo '<p class="skip">⟳ <strong>' . esc_html( $page['title'] ) . '</strong> — exists</p>';
	} else {
		$id = wp_insert_post( array(
			'post_title'  => $page['title'],
			'post_name'   => $page['slug'],
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
		update_post_meta( $id, '_wp_page_template', $page['template'] );
		echo '<p class="ok">✓ <strong>' . esc_html( $page['title'] ) . '</strong> — created</p>';
	}
}

// Step 6: Set homepage
echo '<h2>Settings</h2>';
$front = get_page_by_path( 'home' );
if ( ! $front ) {
	$front_id = wp_insert_post( array(
		'post_title'  => 'Home',
		'post_name'   => 'home',
		'post_status' => 'publish',
		'post_type'   => 'page',
	) );
	echo '<p class="ok">✓ Home page created</p>';
} else {
	$front_id = $front->ID;
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $front_id );
echo '<p class="ok">✓ Homepage set</p>';

update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules();
echo '<p class="ok">✓ Permalinks set to /%postname%/</p>';

// Step 7: Import Pexels images into media library
echo '<h2>Images</h2>';

$pexels_dir = get_stylesheet_directory() . '/assets/images/pexels';
if ( is_dir( $pexels_dir ) ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$image_files = glob( $pexels_dir . '/*.jpg' );
	if ( empty( $image_files ) ) {
		echo '<p class="skip">⟳ No images found in pexels directory</p>';
	}

	foreach ( $image_files as $image_path ) {
		$slug  = pathinfo( $image_path, PATHINFO_FILENAME );
		$title = ucwords( str_replace( '-', ' ', $slug ) );

		$existing = get_posts( array(
			'post_type'   => 'attachment',
			'name'        => $slug,
			'numberposts' => 1,
		) );

		if ( ! empty( $existing ) ) {
			echo '<p class="skip">⟳ Image: <strong>' . esc_html( $slug ) . '</strong> — already imported (ID ' . $existing[0]->ID . ')</p>';
			continue;
		}

		$upload = wp_upload_bits( $slug . '.jpg', null, file_get_contents( $image_path ) );

		if ( ! empty( $upload['error'] ) ) {
			echo '<p class="del">✗ Image: <strong>' . esc_html( $slug ) . '</strong> — ' . esc_html( $upload['error'] ) . '</p>';
			continue;
		}

		$attachment_id = wp_insert_attachment( array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => '',
			'post_status'    => 'inherit',
		), $upload['file'] );

		if ( is_wp_error( $attachment_id ) ) {
			echo '<p class="del">✗ Image: <strong>' . esc_html( $slug ) . '</strong> — ' . esc_html( $attachment_id->get_error_message() ) . '</p>';
			continue;
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		echo '<p class="ok">✓ Image: <strong>' . esc_html( $slug ) . '</strong> — imported (ID ' . $attachment_id . ')</p>';
	}
} else {
	echo '<p class="skip">⟳ No pexels images directory — images will appear after next deploy</p>';
}

echo '<hr>';
echo '<h2>All Done!</h2>';
echo '<p>Internal links now work:</p>';
echo '<ul>';
echo '<li><code>/cash-for-junk-cars/sedans/</code></li>';
echo '<li><code>/cash-for-junk-cars/trucks-suvs/</code></li>';
echo '<li><code>/service-areas/henderson/</code></li>';
echo '<li><code>/service-areas/summerlin/</code></li>';
echo '<li>etc.</li>';
echo '</ul>';
echo '<p class="del"><strong>⚠ DELETE this file from the server after running!</strong></p>';
echo '<p><a href="' . esc_url( home_url( '/' ) ) . '">→ View site</a> | <a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">→ All pages</a></p>';
echo '</body></html>';
