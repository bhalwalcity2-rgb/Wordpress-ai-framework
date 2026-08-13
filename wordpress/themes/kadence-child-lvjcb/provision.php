<?php
/**
 * One-time page provisioning script.
 * Visit this URL while logged in as admin to create all pages:
 * https://junkcarbuyerslasvegas.com/wp-content/themes/kadence-child-lvjcb/provision.php
 *
 * DELETE THIS FILE after running it.
 */

require_once dirname( __FILE__ ) . '/../../../wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You must be logged in as an administrator to run this script.' );
}

header( 'Content-Type: text/html; charset=utf-8' );
echo '<html><head><title>LVJCB Provisioning</title>';
echo '<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px;line-height:1.6}';
echo '.ok{color:#16a34a}.skip{color:#d97706}.err{color:#dc2626}h1{color:#1C3144}code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.9em}</style>';
echo '</head><body>';
echo '<h1>Las Vegas Junk Car Buyers — Page Provisioning</h1>';

$pages = array(
	// Hub pages
	array(
		'title'    => 'Cash for Junk Cars',
		'slug'     => 'cash-for-junk-cars',
		'template' => 'template-services-hub.php',
	),
	array(
		'title'    => 'Service Areas',
		'slug'     => 'service-areas',
		'template' => 'template-areas-hub.php',
	),
	// Service pages
	array(
		'title'    => 'Cash for Junk Sedans & Coupes in Las Vegas',
		'slug'     => 'sedans',
		'template' => 'template-service.php',
	),
	array(
		'title'    => 'Cash for Junk Trucks & SUVs in Las Vegas',
		'slug'     => 'trucks-suvs',
		'template' => 'template-service.php',
	),
	array(
		'title'    => 'Cash for Accident & Storm-Damaged Cars in Las Vegas',
		'slug'     => 'damaged',
		'template' => 'template-service.php',
	),
	array(
		'title'    => 'Cash for Non-Running Cars in Las Vegas',
		'slug'     => 'non-running',
		'template' => 'template-service.php',
	),
	// Location pages
	array(
		'title'    => 'Junk Car Buyers Las Vegas, NV',
		'slug'     => 'las-vegas',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers Henderson, NV',
		'slug'     => 'henderson',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers North Las Vegas, NV',
		'slug'     => 'north-las-vegas',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers Summerlin, NV',
		'slug'     => 'summerlin',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers Spring Valley, NV',
		'slug'     => 'spring-valley',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers Paradise, NV',
		'slug'     => 'paradise',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers Enterprise, NV',
		'slug'     => 'enterprise',
		'template' => 'template-location.php',
	),
	array(
		'title'    => 'Junk Car Buyers Boulder City, NV',
		'slug'     => 'boulder-city',
		'template' => 'template-location.php',
	),
	// Standalone pages
	array(
		'title'    => 'About',
		'slug'     => 'about',
		'template' => 'template-about.php',
	),
	array(
		'title'    => 'FAQ',
		'slug'     => 'faq',
		'template' => 'template-faq.php',
	),
	array(
		'title'    => 'Contact',
		'slug'     => 'contact',
		'template' => 'template-contact.php',
	),
);

$created = 0;
$skipped = 0;

foreach ( $pages as $page ) {
	$existing = get_page_by_path( $page['slug'] );

	if ( $existing ) {
		update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
		echo '<p class="skip">⟳ <strong>' . esc_html( $page['title'] ) . '</strong> — already exists (template updated) <code>/' . esc_html( $page['slug'] ) . '/</code></p>';
		$skipped++;
		continue;
	}

	$post_id = wp_insert_post( array(
		'post_title'   => $page['title'],
		'post_name'    => $page['slug'],
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	) );

	if ( is_wp_error( $post_id ) ) {
		echo '<p class="err">✗ <strong>' . esc_html( $page['title'] ) . '</strong> — error: ' . esc_html( $post_id->get_error_message() ) . '</p>';
		continue;
	}

	update_post_meta( $post_id, '_wp_page_template', $page['template'] );
	echo '<p class="ok">✓ <strong>' . esc_html( $page['title'] ) . '</strong> — created <code>/' . esc_html( $page['slug'] ) . '/</code></p>';
	$created++;
}

// Set homepage
$front = get_page_by_path( 'home' );
if ( ! $front ) {
	$front_id = wp_insert_post( array(
		'post_title'   => 'Home',
		'post_name'    => 'home',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	) );
	echo '<p class="ok">✓ <strong>Home</strong> — created</p>';
	$created++;
} else {
	$front_id = $front->ID;
	echo '<p class="skip">⟳ <strong>Home</strong> — already exists</p>';
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $front_id );

// Set permalinks to post name
update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules();

echo '<hr>';
echo '<h2>Done!</h2>';
echo '<p><strong>' . $created . ' pages created</strong>, ' . $skipped . ' already existed.</p>';
echo '<p>Homepage set. Permalinks set to <code>/%postname%/</code>.</p>';
echo '<p style="color:#dc2626;font-weight:bold;">⚠ DELETE this file from the server after running it!</p>';
echo '<p><a href="' . esc_url( home_url( '/' ) ) . '">→ View your site</a> | <a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">→ View all pages</a></p>';
echo '</body></html>';
