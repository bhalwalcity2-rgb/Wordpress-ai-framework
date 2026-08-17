<?php
/**
 * Business Config — Las Vegas Junk Car Buyers
 *
 * Every piece of business-specific data for this site lives here, and
 * only here. To build a new site from this theme template: copy the
 * theme folder, replace the contents of this one file, leave every
 * other file untouched.
 *
 * @package LVJCB
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'business_name' => 'First Choice Junk Car',
	'tagline'       => 'Locally owned. Buying junk, damaged, and non-running vehicles across the Las Vegas valley since day one.',
	'address'       => '4820 W Sahara Ave, Las Vegas, NV 89102',
	'email'         => 'offers@junkcarbuyerslasvegas.com',
	'hours'         => 'Mon–Sat: 7:00 AM – 7:00 PM · Sun: 9:00 AM – 4:00 PM',
	'map_embed'     => '',

	'phone_e164'    => '+18667483697',
	'phone_display' => '(866) 748-3697',
	'instant_quote_url' => 'https://sell.peddle.com/instant-offer?pub_id=5173180',

	// Five raw brand colors — every other color token is derived
	// automatically from these by inc/colors.php. Pick any hex values
	// here; accessibility-safe shades are computed, not hand-picked.
	'colors' => array(
		'primary'   => '#FFB800',
		'accent'    => '#FFB800',
		'dark'      => '#111111',
		'neutral'   => '#9CA3AF',
		'secondary' => '#374151',
	),

	'nav' => array(
		array( 'label' => 'Cash for Junk Cars', 'slug' => 'cash-for-junk-cars' ),
		array( 'label' => 'Service Areas', 'slug' => 'service-areas' ),
		array( 'label' => 'About', 'slug' => 'about' ),
		array( 'label' => 'FAQ', 'slug' => 'faq' ),
		array( 'label' => 'Contact', 'slug' => 'contact' ),
	),

	'trust_items' => array(
		array( 'icon' => 'truck', 'label' => 'Free towing, every time' ),
		array( 'icon' => 'clock', 'label' => 'Same-day pickup available' ),
		array( 'icon' => 'shield', 'label' => 'No hidden fees, ever' ),
		array( 'icon' => 'document', 'label' => 'No title? We can still help' ),
	),

	'hero' => array(
		'heading'     => "We Buy Junk Cars \u{2014}in Las Vegas, Get Cash Today",
		'description' => "Running or not, damaged or complete — free towing, an offer in minutes, and cash in hand the same day. No fees, no hassle.",
		'eyebrow'     => 'Serving Las Vegas & Clark County',
		// Resolved to an attachment ID at render time via
		// lvjcb_get_attachment_id_by_slug() — import the matching file
		// with scripts/import-images.sh, no numeric ID needed here.
		'image_slug'  => 'hero-homepage-01',
	),

	'services' => array(
		'eyebrow' => 'What we buy',
		'heading' => 'Any vehicle, any condition',
		'intro'   => "If it's sitting in your driveway costing you space, we're interested — running or not.",
		'cards'   => array(
			array(
				'icon'        => 'car-sedan',
				'heading'     => 'Sedans & Coupes',
				'description' => "Old, worn, or no longer running — we'll still make an offer.",
				'slug'        => 'sedans',
				'intro'       => "Selling a sedan or coupe that's past its prime? We buy sedans and coupes of any age, mileage, or condition across the Las Vegas valley — faded paint, worn interior, and high mileage never disqualify a vehicle from an offer.",
			),
			array(
				'icon'        => 'car-suv',
				'heading'     => 'Trucks & SUVs',
				'description' => "Full-size, mid-size, damaged or complete — condition doesn't disqualify you.",
				'slug'        => 'trucks-suvs',
				'intro'       => "Trucks and SUVs are some of the most common vehicles we buy — full-size or mid-size, two-wheel or four-wheel drive, running or not. A larger vehicle usually means a larger offer, even in rough condition.",
			),
			array(
				'icon'        => 'damage',
				'heading'     => 'Accident & Storm-Damaged',
				'description' => "Collision, hail, or flood damage — we'll still take a look.",
				'slug'        => 'damaged',
				'intro'       => "A collision, hailstorm, or flood doesn't mean your vehicle is worthless. We regularly buy accident and storm-damaged vehicles across the valley — no repair required before we make an offer.",
			),
			array(
				'icon'        => 'wrench',
				'heading'     => 'Non-Running & Mechanical Failure',
				'description' => "Won't start, blown engine, transmission gone — no problem at all.",
				'slug'        => 'non-running',
				'intro'       => "A vehicle that won't start is exactly the kind of car we specialize in. Blown engines, failed transmissions, electrical issues — none of it needs to be diagnosed or fixed before you call us.",
			),
		),
	),

	'how_it_works' => array(
		'eyebrow' => 'How it works',
		'heading' => 'Three steps, one call away',
		'intro'   => 'Most vehicles are picked up and paid for the same day you call.',
		'steps'   => array(
			array( 'icon' => 'phone', 'heading' => 'Call or request a quote', 'description' => 'Tell us about the vehicle — year, make, model, and condition. Takes about two minutes.' ),
			array( 'icon' => 'cash', 'heading' => 'Get a firm cash offer', 'description' => 'No lowball games. The number we give you is the number you get.' ),
			array( 'icon' => 'truck', 'heading' => 'We tow it, you get paid', 'description' => 'Free pickup at a time that works for you, cash in hand before the truck leaves.' ),
		),
	),

	'why_choose_us' => array(
		'eyebrow' => 'Why choose us',
		'heading' => 'A local company, not a call center',
		'intro'   => 'We buy directly — no brokers, no middlemen, no surprise deductions on pickup day.',
		'cards'   => array(
			array( 'icon' => 'handshake', 'heading' => 'We pay top dollar', 'description' => 'Direct buyer means no middleman markup eating into your offer.' ),
			array( 'icon' => 'bolt', 'heading' => 'Fast, same-day service', 'description' => 'Most calls turn into a completed pickup within hours, not days.' ),
			array( 'icon' => 'no-fee', 'heading' => 'No fees, ever', 'description' => 'Towing, paperwork, and pickup are always included — free.' ),
			array( 'icon' => 'map-pin', 'heading' => 'Locally owned in Las Vegas', 'description' => 'Real people who know the valley — not a national lead-gen line.' ),
		),
	),

	'vehicles' => array(
		'eyebrow' => 'Recently purchased',
		'heading' => "Vehicles we've bought this month",
		'intro'   => 'A real sample of what we pick up — running, wrecked, and everything between.',
		'items'   => array(
			array( 'image_slug' => 'vehicle-sedan-01', 'image_alt' => '2011 Honda Accord, faded paint, parked in a driveway', 'vehicle' => '2011 Honda Accord', 'condition' => 'Purchased in Henderson, NV' ),
			array( 'image_slug' => 'vehicle-accident-car-01', 'image_alt' => '2015 Ford F-150 with front-end collision damage', 'vehicle' => '2015 Ford F-150', 'condition' => 'Purchased in Las Vegas, NV' ),
			array( 'image_slug' => 'vehicle-non-running-01', 'image_alt' => '2007 Toyota Camry, non-running, resting on blocks', 'vehicle' => '2007 Toyota Camry', 'condition' => 'Purchased in Spring Valley, NV' ),
			array( 'image_slug' => 'vehicle-rust-damage-01', 'image_alt' => '2003 Chevrolet Tahoe with heavy rust on the wheel wells', 'vehicle' => '2003 Chevrolet Tahoe', 'condition' => 'Purchased in North Las Vegas, NV' ),
			array( 'image_slug' => 'vehicle-flat-tires-01', 'image_alt' => '2009 Nissan Altima, flat tire, curbside', 'vehicle' => '2009 Nissan Altima', 'condition' => 'Purchased in Summerlin, NV' ),
		),
	),

	'testimonials' => array(
		'eyebrow' => 'Reviews',
		'heading' => 'What our customers say',
		'aggregate_rating' => 4.9,
		'review_count'     => 127,
		'items'   => array(
			array( 'rating' => 5, 'quote' => "Called at 9am, cash in hand by noon. Didn't expect it to be that easy.", 'customer_name' => 'Marcus D.', 'customer_location' => 'Henderson, NV', 'date' => '2 months ago' ),
			array( 'rating' => 5, 'quote' => 'No title, no problem — they still handled everything and paid fairly.', 'customer_name' => 'Priya S.', 'customer_location' => 'Las Vegas, NV', 'date' => '3 weeks ago' ),
			array( 'rating' => 4, 'quote' => 'Truck showed up right on time. Straightforward, professional, no surprises.', 'customer_name' => 'Devon W.', 'customer_location' => 'Summerlin, NV', 'date' => '1 month ago' ),
			array( 'rating' => 5, 'quote' => 'Had an old Civic sitting in the driveway for over a year. One call and they came same day with cash.', 'customer_name' => 'James R.', 'customer_location' => 'North Las Vegas, NV', 'date' => '2 weeks ago' ),
			array( 'rating' => 5, 'quote' => 'Super easy process. They gave me more than I expected for a car that barely ran.', 'customer_name' => 'Linda K.', 'customer_location' => 'Spring Valley, NV', 'date' => '5 days ago' ),
			array( 'rating' => 5, 'quote' => 'Friendly guys, fair price, picked up the same afternoon. Would definitely recommend.', 'customer_name' => 'Carlos M.', 'customer_location' => 'Paradise, NV', 'date' => '1 week ago' ),
			array( 'rating' => 4, 'quote' => "Wasn't sure if my truck was worth anything. They made a solid offer and handled all the paperwork.", 'customer_name' => 'Tamika J.', 'customer_location' => 'Enterprise, NV', 'date' => '3 months ago' ),
			array( 'rating' => 5, 'quote' => 'Best junk car service in Vegas. Fast, honest, and no hidden fees.', 'customer_name' => 'Ryan P.', 'customer_location' => 'Las Vegas, NV', 'date' => '6 days ago' ),
		),
	),

	'service_areas' => array(
		'eyebrow' => 'Service areas',
		'heading' => 'Serving the entire Las Vegas valley',
		'items'   => array(
			array( 'city' => 'Henderson', 'state' => 'NV', 'slug' => 'henderson', 'intro' => "Henderson homeowners call us for the same reason people across the valley do: a fair offer and free pickup, without the runaround." ),
			array( 'city' => 'North Las Vegas', 'state' => 'NV', 'slug' => 'north-las-vegas', 'intro' => 'We buy junk cars throughout North Las Vegas, from vehicles that just need to go to ones that have been sitting for years.' ),
			array( 'city' => 'Summerlin', 'state' => 'NV', 'slug' => 'summerlin', 'intro' => "Whether it's tucked in a garage or sitting on the street, we'll come to your Summerlin address and make an offer on the spot." ),
			array( 'city' => 'Spring Valley', 'state' => 'NV', 'slug' => 'spring-valley', 'intro' => 'Spring Valley pickups are usually completed the same day — call in the morning, have cash by the afternoon.' ),
			array( 'city' => 'Paradise', 'state' => 'NV', 'slug' => 'paradise', 'intro' => 'Serving the Paradise area with free towing and same-day offers on junk and non-running vehicles.' ),
			array( 'city' => 'Enterprise', 'state' => 'NV', 'slug' => 'enterprise', 'intro' => 'We regularly buy vehicles throughout Enterprise — running or not, any make or model.' ),
			array( 'city' => 'Boulder City', 'state' => 'NV', 'slug' => 'boulder-city', 'intro' => 'Boulder City is a bit further out, but we still offer free towing and the same fast, fair process.' ),
		),
	),

	'faq' => array(
		'eyebrow' => 'FAQ',
		'heading' => 'Questions people ask before calling',
		'items'   => array(
			array( 'id' => 'title', 'question' => 'Do I need a title to sell my car?', 'answer' => "In most cases, no — we can often still buy your vehicle without a title. Let us know your situation on the phone and we'll walk you through it." ),
			array( 'id' => 'speed', 'question' => 'How fast can you actually pick it up?', 'answer' => 'Most pickups happen the same day you call, sometimes within a couple of hours depending on your location in the valley.' ),
			array( 'id' => 'towing', 'question' => 'Is the towing really free?', 'answer' => "Yes, always. Towing is included in every offer — it's never deducted from your payment." ),
			array( 'id' => 'non-running', 'question' => "What if my car doesn't run at all?", 'answer' => "That's most of what we buy. Non-running vehicles, blown engines, and total mechanical failures are all fine." ),
		),
	),

	'cta_banner' => array(
		'mid_page'  => array(
			'heading' => 'Ready to turn that car into cash?',
			'intro'   => 'Call now for a same-day quote — no obligation, no pressure.',
		),
		'late_page' => array(
			'heading' => 'Still have questions? Just call.',
			'intro'   => 'A real person answers — no call center, no scripts.',
		),
	),

	'contact' => array(
		'eyebrow' => 'Contact',
		'heading' => 'Reach us directly',
	),

	'about' => array(
		'eyebrow' => 'About us',
		'heading' => 'A local company, not a national lead-gen line',
		'body'    => "Las Vegas Junk Car Buyers is locally owned and operated across the Las Vegas valley. We buy vehicles directly — running or not, damaged or complete — and handle towing, paperwork, and payment ourselves, without routing your call through a broker or national call center.",
	),

);
