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

	/*
	 * The same address broken into its parts, for schema.org PostalAddress.
	 * 'address' above stays the human-readable one-liner used on-page.
	 */
	'address_parts' => array(
		'street'   => '4820 W Sahara Ave',
		'locality' => 'Las Vegas',
		'region'   => 'NV',
		'postal'   => '89102',
		'country'  => 'US',
	),

	/*
	 * Machine-readable opening hours for schema.org
	 * openingHoursSpecification. Keep in sync with 'hours' above.
	 */
	'hours_spec' => array(
		array(
			'days'   => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
			'opens'  => '07:00',
			'closes' => '19:00',
		),
		array(
			'days'   => array( 'Sunday' ),
			'opens'  => '09:00',
			'closes' => '16:00',
		),
	),

	'price_range' => '$$',

	/*
	 * schema.org type for this business. AutomotiveBusiness is a
	 * LocalBusiness subtype, so it inherits everything LocalBusiness
	 * supports while telling search engines specifically what kind of
	 * business this is. Not AutoDealer — that type describes selling
	 * vehicles, and this business buys them. Set to 'LocalBusiness'
	 * for a business with no better-fitting subtype.
	 */
	'schema_type' => 'AutomotiveBusiness',

	/*
	 * Homepage search-result copy. Kept separate from the hero heading
	 * because the hero heading carries a structural split marker and is
	 * written for the page, not for a 60-character SERP listing.
	 */
	'seo' => array(
		'home_title'       => 'Cash for Junk Cars Las Vegas | Free Towing & Same-Day Pickup',
		'home_description' => 'First Choice Junk Car buys junk, damaged, and non-running cars in Las Vegas. Free towing, same-day pickup, and cash on the spot. Call for an offer today.',
		'og_image_slug'    => 'hero-homepage-01',
	),

	'phone_e164'    => '+18667483697',
	'phone_display' => '(866) 748-3697',
	'instant_quote_url' => 'https://sell.peddle.com/instant-offer?pub_id=5173180',
	'peddle_publisher_id' => '5173180',

	'instant_offer' => array(
		'eyebrow' => 'Instant Offer',
		'heading' => 'Get a cash offer in under 2 minutes',
		'intro'   => 'Enter your vehicle details and get an instant, no-obligation offer. No phone call needed.',
	),

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
		'description' => "Running or not, damaged or complete. Get a firm cash offer in minutes, free towing to your door, and payment in hand the same day. No fees, no middlemen, no games.",
		'eyebrow'     => 'Serving Las Vegas & Clark County',
		'image_slug'  => 'hero-homepage-01',
	),

	'homepage_intro' => array(
		'eyebrow' => 'Who we are',
		'heading' => 'Why Las Vegas Vehicle Owners Choose Us',
		'image_slug' => 'who-we-are-01',
		'image_alt'  => 'First Choice Junk Car team member handing cash to a vehicle owner in Las Vegas',
		'paragraphs' => array(
			"Getting rid of a junk car in the Las Vegas valley should not take weeks of phone calls, lowball offers, and surprise towing charges. That is exactly why First Choice Junk Car exists. We give vehicle owners across Las Vegas, Henderson, Summerlin, and North Las Vegas a fast, honest way to turn an unwanted vehicle into cash without lifting a finger.",
			"When you call, you are talking to someone who knows the valley, from the east side of Boulder Highway to the neighborhoods off Durango and Flamingo. That local knowledge matters more than most people expect, because what your vehicle is worth here depends on what Las Vegas dismantlers and scrap yards are paying for its parts and metal that week. We price every offer against your vehicle's actual year, make, model, and condition, then quote you a firm number.",
			"Whether your vehicle is sitting on blocks in the driveway, collecting dust in the garage, or parked curbside with a flat tire, we will buy it. Running, not running, wrecked, flood-damaged, or mechanically totaled. Every vehicle has value, and we are here to pay you for it.",
		),
	),

	'services' => array(
		'eyebrow' => 'What we buy',
		'heading' => 'Any Vehicle, Any Condition. We Buy It All',
		'intro'   => 'We purchase cars, trucks, SUVs, vans, and commercial vehicles across every condition level. If it has a VIN, we want to see it. Here is what we pick up on a regular basis across Clark County:',
		'cards'   => array(
			array(
				'icon'        => 'car-sedan',
				'heading'     => 'Sedans & Coupes',
				'description' => "Honda Accords, Toyota Camrys, Nissan Altimas, Chevy Malibus. Sedans are the most common junk car we buy in the Las Vegas area. Whether the engine has given out, the transmission slips, or the body damage makes it impossible to drive, we will make you a fair cash offer and tow it for free.",
				'slug'        => 'sedans',
				'intro'       => "Selling a sedan or coupe that's past its prime? We buy sedans and coupes of any age, mileage, or condition across the Las Vegas valley. Faded paint, worn interior, and high mileage never disqualify a vehicle from an offer.",
			),
			array(
				'icon'        => 'car-suv',
				'heading'     => 'Trucks & SUVs',
				'description' => "Full-size pickups, mid-size SUVs, and heavy-duty work trucks all hold salvage and parts value. We buy Ford F-150s, Chevy Silverados, Dodge Rams, Toyota 4Runners, and everything in between. Condition does not disqualify you. High mileage, rusted frames, blown motors, and missing catalytic converters are all fine.",
				'slug'        => 'trucks-suvs',
				'intro'       => "Trucks and SUVs are some of the most common vehicles we buy. Full-size or mid-size, two-wheel or four-wheel drive, running or not. A larger vehicle usually means a larger offer, even in rough condition.",
			),
			array(
				'icon'        => 'damage',
				'heading'     => 'Accident & Storm-Damaged',
				'description' => "Las Vegas sees its share of rear-end collisions on the I-15, T-bone crashes at intersections along Charleston and Sahara, and monsoon-season flash flood damage. If your insurance totaled it out or you do not have coverage, we will still make an offer and handle the removal.",
				'slug'        => 'damaged',
				'intro'       => "A collision, hailstorm, or flood doesn't mean your vehicle is worthless. We regularly buy accident and storm-damaged vehicles across the valley. No repair required before we make an offer.",
			),
			array(
				'icon'        => 'wrench',
				'heading'     => 'Non-Running & Mechanical Failure',
				'description' => "Dead battery, seized engine, bad transmission, overheating problems. Whatever the reason your car will not start, we still want it. We pick up non-running vehicles every day across the valley, and we never charge for towing. Period.",
				'slug'        => 'non-running',
				'intro'       => "A vehicle that won't start is exactly the kind of car we specialize in. Blown engines, failed transmissions, electrical issues. None of it needs to be diagnosed or fixed before you call us.",
			),
		),
	),

	'how_it_works' => array(
		'eyebrow' => 'How it works',
		'heading' => 'How Selling Your Junk Car Works. Three Steps, One Call',
		'intro'   => 'Most vehicles we buy are picked up and paid for the same day the owner calls. The process takes less time than most people expect.',
		'steps'   => array(
			array(
				'icon'        => 'phone',
				'heading'     => 'Call Us or Request a Quote Online',
				'description' => "Tell us the year, make, model, and condition. Takes about two minutes, no obligation.",
			),
			array(
				'icon'        => 'cash',
				'heading'     => 'Accept a Firm Cash Offer',
				'description' => "The price we quote is the price we pay. No inspection-day surprises, no last-minute deductions.",
			),
			array(
				'icon'        => 'truck',
				'heading'     => 'We Pick It Up and Pay You on the Spot',
				'description' => "Our tow truck comes to you. The driver completes the paperwork and hands you cash before the truck leaves. Towing is always free.",
			),
		),
	),

	'why_choose_us' => array(
		'eyebrow' => 'Why choose us',
		'heading' => 'A Local Junk Car Buyer, Not a Call Center',
		'intro'   => "Las Vegas has no shortage of \"we buy junk cars\" ads, but most of them route to national lead generators that sell your information to the highest bidder. First Choice Junk Car is different. We buy directly. No brokers, no middlemen, no surprise deductions.",
		'cards'   => array(
			array( 'icon' => 'handshake', 'heading' => 'We Pay Top Dollar', 'description' => "No middleman markup eating into your offer. We are the buyer, so the cash we quote goes straight to you." ),
			array( 'icon' => 'bolt', 'heading' => 'Fast, Same-Day Service', 'description' => "Most calls turn into a completed pickup within hours, not days. There is almost always a driver nearby." ),
			array( 'icon' => 'no-fee', 'heading' => 'No Fees, Ever', 'description' => "Towing, paperwork, and pickup are always free. The price you accept is the price you receive, in full, on the spot." ),
			array( 'icon' => 'document', 'heading' => 'No Title? We Can Still Help', 'description' => "Lost your title? In many cases we can still buy your vehicle. Call us and we will walk you through your options." ),
		),
	),

	'vehicles' => array(
		'eyebrow' => 'Recently purchased',
		'heading' => 'Junk Cars We Bought This Month in Las Vegas',
		'intro'   => 'Real pickups from across the Las Vegas valley. Running, wrecked, and everything in between.',
		'items'   => array(
			array( 'image_slug' => 'vehicle-sedan-01', 'image_alt' => '2011 Honda Accord with faded paint parked in a Henderson driveway', 'vehicle' => '2011 Honda Accord', 'condition' => 'Purchased in Henderson, NV' ),
			array( 'image_slug' => 'vehicle-accident-car-01', 'image_alt' => '2015 Ford F-150 with front-end collision damage in Las Vegas', 'vehicle' => '2015 Ford F-150', 'condition' => 'Purchased in Las Vegas, NV' ),
			array( 'image_slug' => 'vehicle-non-running-01', 'image_alt' => '2007 Toyota Camry non-running sitting on blocks in Spring Valley', 'vehicle' => '2007 Toyota Camry', 'condition' => 'Purchased in Spring Valley, NV' ),
			array( 'image_slug' => 'vehicle-rust-damage-01', 'image_alt' => '2003 Chevrolet Tahoe with heavy rust and mechanical issues in North Las Vegas', 'vehicle' => '2003 Chevrolet Tahoe', 'condition' => 'Purchased in North Las Vegas, NV' ),
			array( 'image_slug' => 'vehicle-flat-tires-01', 'image_alt' => '2009 Nissan Altima with flat tires curbside pickup in Summerlin', 'vehicle' => '2009 Nissan Altima', 'condition' => 'Purchased in Summerlin, NV' ),
		),
	),

	'testimonials' => array(
		'eyebrow' => 'Reviews',
		'heading' => 'What Our Customers Say',
		'aggregate_rating' => 4.9,
		'review_count'     => 127,
		'items'   => array(
			array( 'rating' => 5, 'quote' => "Called at 9am, cash in hand by noon. Didn't expect it to be that easy.", 'customer_name' => 'Marcus D.', 'customer_location' => 'Henderson, NV', 'date' => '2 months ago' ),
			array( 'rating' => 5, 'quote' => 'No title, no problem. They still handled everything and paid fairly.', 'customer_name' => 'Priya S.', 'customer_location' => 'Las Vegas, NV', 'date' => '3 weeks ago' ),
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
		'heading' => 'Serving the Entire Las Vegas Valley and Clark County',
		'intro'   => 'First Choice Junk Car provides free towing and same-day junk car removal across the greater Las Vegas metro area. Whether you are in a residential neighborhood off Eastern Avenue, a gated community in Summerlin, or an apartment complex in North Las Vegas, our trucks reach you.',
		'items'   => array(
			// is_primary: the homepage targets this city, so it has no
			// location page of its own and links resolve to '/'.
			array( 'city' => 'Las Vegas', 'state' => 'NV', 'slug' => 'las-vegas', 'is_primary' => true, 'intro' => "All zip codes, east side to west side, the Strip corridor neighborhoods to the older residential pockets near Downtown and Fremont. We buy junk cars across every part of Las Vegas with free towing and same-day cash offers." ),
			array( 'city' => 'Henderson', 'state' => 'NV', 'slug' => 'henderson', 'intro' => "Green Valley, Anthem, Lake Las Vegas, Inspirada, and the industrial corridor along Gibson Road. Henderson residents get the same free towing and same-day pickup as anywhere in the valley." ),
			array( 'city' => 'North Las Vegas', 'state' => 'NV', 'slug' => 'north-las-vegas', 'intro' => "Aliante, Eldorado, Sunrise Manor, and the neighborhoods off Craig Road and Las Vegas Boulevard North. We pick up junk and non-running vehicles throughout North Las Vegas every day." ),
			array( 'city' => 'Summerlin', 'state' => 'NV', 'slug' => 'summerlin', 'intro' => "Summerlin South, The Trails, Red Rock Country Club area, and surrounding master-planned communities. Whether it is tucked in a garage or sitting on the street, we will come to your Summerlin address and make an offer." ),
			array( 'city' => 'Spring Valley', 'state' => 'NV', 'slug' => 'spring-valley', 'intro' => "The neighborhoods between Flamingo, Tropicana, Durango, and Rainbow. One of the most common areas we pick up from. Spring Valley pickups are usually completed the same day." ),
			array( 'city' => 'Paradise', 'state' => 'NV', 'slug' => 'paradise', 'intro' => "Including the areas around UNLV, the Convention Center corridor, and the neighborhoods east of the Strip. We serve Paradise with free towing and same-day offers on junk and non-running vehicles." ),
			array( 'city' => 'Enterprise', 'state' => 'NV', 'slug' => 'enterprise', 'intro' => "South of the Strip toward the M Resort, Southern Highlands, and Mountain's Edge. We regularly buy vehicles throughout Enterprise, running or not, any make or model." ),
			array( 'city' => 'Boulder City', 'state' => 'NV', 'slug' => 'boulder-city', 'intro' => "Including homes near Lake Mead and the historic downtown area. Boulder City is a bit further out, but we still offer free towing and the same fast, fair process." ),
		),
	),

	'faq' => array(
		'eyebrow' => 'FAQ',
		'heading' => 'Questions People Ask Before Selling a Junk Car in Las Vegas',
		'items'   => array(
			array(
				'id'       => 'title',
				'question' => 'Do I need a title to sell my junk car in Las Vegas?',
				'answer'   => "Not always. Nevada allows junk car sales without the original title in some situations, particularly when the vehicle was last titled in Nevada, is model year 2010 or older, and has no outstanding liens. You may also qualify using a registration, bill of sale, or by applying for a duplicate title through the Nevada DMV (Form VP-012, \$20 fee). We handle cars with and without titles every week. Call us and we will walk you through your specific situation.",
			),
			array(
				'id'       => 'speed',
				'question' => 'How fast can you actually pick up my car?',
				'answer'   => "In most cases, same day. If you call during business hours and your vehicle is located within the Las Vegas valley, we can usually have a tow truck at your location within a few hours. Weekend and Sunday pickups are available too. We operate seven days a week.",
			),
			array(
				'id'       => 'towing',
				'question' => 'Is the towing really free?',
				'answer'   => "Yes. We never charge for towing, and we never deduct towing costs from your cash offer. Free pickup is included on every vehicle we buy, regardless of whether it runs or not and regardless of where it is located in our service area.",
			),
			array(
				'id'       => 'vehicle-types',
				'question' => 'What types of vehicles do you buy?',
				'answer'   => "Cars, trucks, SUVs, vans, minivans, and commercial vehicles. Running, non-running, wrecked, flood-damaged, fire-damaged, salvage title, high mileage, missing parts. We buy all of it. If you are unsure whether your vehicle qualifies, the fastest way to find out is to call (866) 748-3697 and describe what you have.",
			),
			array(
				'id'       => 'value',
				'question' => 'How much will I get for my junk car?',
				'answer'   => "It depends on the year, make, model, condition, and the current market value for salvageable parts and scrap metal. Vehicles with working engines, intact transmissions, and in-demand parts bring higher offers. We quote a firm price based on what you describe. No guesswork, no lowballing.",
			),
			array(
				'id'       => 'non-running',
				'question' => 'What if my car does not run at all?',
				'answer'   => "No problem. We buy non-running vehicles every day. Our flatbed tow trucks are equipped to load vehicles that cannot move under their own power, including cars on blocks, cars with flat tires, and cars with seized engines. You do not need to do anything to prepare the vehicle other than make sure we can access it.",
			),
			array(
				'id'       => 'salvage-title',
				'question' => 'Do you buy cars with salvage or rebuilt titles?',
				'answer'   => "Yes. We purchase vehicles with salvage titles, rebuilt titles, flood titles, and junk titles. Title status does not disqualify a vehicle from receiving a cash offer.",
			),
			array(
				'id'       => 'payment',
				'question' => 'How do I get paid?',
				'answer'   => "Cash or check, handed to you by our driver at the time of pickup. Payment happens before the tow truck leaves your location, not days later, not through the mail. You get paid on the spot.",
			),
		),
	),

	'cta_banner' => array(
		'mid_page'  => array(
			'heading' => 'Ready to Turn That Car Into Cash?',
			'intro'   => 'Call now for a same-day quote. No obligation, no pressure. We answer the phone seven days a week.',
		),
		'late_page' => array(
			'heading' => 'Still Have Questions? Call a Real Person.',
			'intro'   => 'No automated phone tree. No chatbot. A real team member answers every call and can give you a quote in under two minutes.',
		),
	),

	'contact' => array(
		'eyebrow' => 'Contact',
		'heading' => 'Get in Touch',
	),

	'about' => array(
		'eyebrow' => 'About us',
		'heading' => 'A local company, not a national lead-gen line',
		'body'    => "Las Vegas Junk Car Buyers is locally owned and operated across the Las Vegas valley. We buy vehicles directly, running or not, damaged or complete, and handle towing, paperwork, and payment ourselves without routing your call through a broker or national call center.",
	),

);
