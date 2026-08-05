kadence-agency-framework/
│
├── style.css                          # Theme declaration header only
├── functions.php                      # Loader only — no logic lives here
├── screenshot.png                     # 1200×900px theme screenshot
├── README.md                          # Developer onboarding & usage guide
│
├── assets/
│   ├── css/
│   │   ├── global.css                 # Custom CSS variables, resets, utility classes
│   │   └── editor.css                 # Block editor / Gutenberg admin styles
│   ├── js/
│   │   ├── global.js                  # Site-wide vanilla JS (no jQuery dependency)
│   │   └── admin.js                   # WP Admin panel JS (if needed)
│   └── images/
│       └── .gitkeep                   # Placeholder — project images go here
│
├── inc/
│   ├── setup.php                      # Core theme setup — supports, nav menus, image sizes
│   ├── enqueue.php                    # All wp_enqueue_scripts / styles logic
│   ├── kadence-config.php             # Kadence theme filters & Global Color/Font overrides
│   ├── helpers.php                    # Pure utility functions (no hooks)
│   ├── custom-post-types.php          # CPT & custom taxonomy registrations
│   ├── shortcodes.php                 # Custom shortcode definitions
│   ├── local-seo/
│   │   ├── schema.php                 # JSON-LD schema output (LocalBusiness, Service, etc.)
│   │   └── meta.php                   # Custom meta tags, OG tags, geo meta
│   └── security.php                   # Security hardening hooks (login protection, etc.)
│
├── template-parts/
│   ├── global/
│   │   ├── header-cta.php             # Reusable header CTA partial
│   │   ├── breadcrumbs.php            # Breadcrumb partial
│   │   └── social-icons.php           # Social icon block partial
│   ├── cards/
│   │   ├── service-card.php           # Service listing card partial
│   │   └── location-card.php          # Location page card partial
│   ├── cta/
│   │   └── cta-banner.php             # Full-width CTA section partial
│   └── loops/
│       ├── loop-post.php              # Standard blog post loop item
│       └── loop-location.php          # Location CPT loop item
│
└── templates/
    ├── page-full-width.php            # Full-width page template (no sidebar)
    ├── page-landing.php               # Landing page template (no header/footer nav)
    ├── page-location.php              # Local SEO location page template
    └── page-service.php               # Service area page template