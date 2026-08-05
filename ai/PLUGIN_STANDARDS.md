# PLUGIN_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — every plugin decision must follow this document |
| Companion Documents | `PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md` |

> **Plugins are the most common source of performance degradation, security vulnerabilities, and maintenance burden in WordPress.**
> This document defines which plugins are approved, how new plugins are evaluated, how custom plugins are built, and how the plugin ecosystem is maintained across every project.

---

## 1. Plugin Philosophy

Every plugin installed on a project introduces cost. That cost is not just the license fee — it is the cumulative weight of additional HTTP requests, database queries, update maintenance, security surface, and potential conflicts.

### Governing Rules

| Rule | Detail |
|---|---|
| Every plugin must justify its existence | If the functionality can be achieved with the theme, a hook, or a few lines of code, a plugin is not needed |
| One tool per job | Never install two plugins that do the same thing, even partially |
| Core stack is locked | The approved plugin list is the default for every project — deviations require documented approval |
| Never modify plugin files | All customization through hooks, filters, or companion custom plugins |
| Fewer is better | Target fewer than 15 active plugins per project |
| Premium does not mean better | Evaluate every plugin on merit, not on price or marketing |

### Plugin Cost Model

Every plugin adds some combination of these costs:

```
Plugin Installed
    │
    ├── Frontend Cost
    │   ├── CSS files loaded (render blocking)
    │   ├── JavaScript files loaded (main thread)
    │   └── Additional DOM elements
    │
    ├── Backend Cost
    │   ├── Database queries on every page load
    │   ├── Autoloaded options in wp_options
    │   ├── Cron jobs and scheduled tasks
    │   └── REST API endpoints registered
    │
    ├── Maintenance Cost
    │   ├── Updates (monthly or more frequent)
    │   ├── Compatibility testing after WordPress core updates
    │   ├── Compatibility testing after PHP updates
    │   └── License renewal and cost tracking
    │
    └── Security Cost
        ├── Additional attack surface
        ├── Dependency on third-party developer's security practices
        └── Vulnerability response time outside our control
```

A plugin is approved only when its value clearly exceeds its total cost.

---

## 2. Approved Plugin List

These plugins are pre-approved for every project. No evaluation or justification is needed to include them.

### Core Stack

| Plugin | Purpose | Status | License |
|---|---|---|---|
| **Kadence Blocks Pro** | Block-based page building, advanced layout components, row layouts, advanced gallery, dynamic content | Core — always installed | Commercial (annual) |
| **Elementor** | Visual page builder for complex layouts that exceed Kadence Blocks capabilities | Conditional — install only when required | Free or Pro (commercial) |
| **Rank Math Pro** | On-page SEO, schema markup, sitemaps, redirects, breadcrumbs, analytics integration, Local SEO module | Core — always installed | Commercial (annual) |
| **WP Rocket** | Page caching, browser caching, GZIP compression, CSS/JS minification, lazy loading, database cleanup | Core — always installed | Commercial (annual) |
| **Instant Indexing** | Google Indexing API integration for rapid page discovery and re-indexing | Core — always installed | Free |

### Plugin Responsibility Matrix

No two plugins should share responsibility for the same function:

| Function | Responsible Plugin | Never Use |
|---|---|---|
| Page caching | WP Rocket | W3 Total Cache, LiteSpeed, Autoptimize |
| CSS/JS optimization | WP Rocket | Autoptimize, Fast Velocity, Asset CleanUp |
| Lazy loading | WP Rocket (or native browser) | a3 Lazy Load, Smush lazy load |
| Image optimization | WP Rocket (or external: ShortPixel/Imagify) | Multiple image plugins simultaneously |
| SEO meta tags | Rank Math Pro | Yoast, All in One SEO, SEOPress |
| Schema markup | Rank Math Pro | Schema Pro, WP Schema, manual JSON-LD plugins |
| XML sitemap | Rank Math Pro | Google XML Sitemaps, Yoast sitemap |
| Redirects | Rank Math Pro | Redirection plugin, Simple 301 Redirects |
| Breadcrumbs | Rank Math Pro (or Kadence) | Breadcrumb NavXT, Yoast breadcrumbs |
| Block editing | Kadence Blocks Pro | Stackable, Spectra, GenerateBlocks |
| Visual page building | Elementor (when needed) | Beaver Builder, Divi, WPBakery |

**If functionality is already handled by an approved plugin, do not install another plugin for the same purpose.**

---

## 3. Approved Plugin Configuration

### Kadence Blocks Pro

| Setting | Configuration |
|---|---|
| Blocks enabled | Only blocks actively used on the project — disable unused blocks to reduce asset loading |
| Global styles | Configured through Kadence theme global palette — never override per-block |
| Custom CSS | Use child theme stylesheets, not Kadence custom CSS fields |
| Dynamic content | Use for repeatable content patterns where Kadence supports it natively |
| Header/Footer builder | Use Kadence header/footer builder as default; Elementor header/footer only if Kadence cannot deliver the design |

**Configuration export location:** `templates/config/kadence-blocks-settings.json`

### Elementor

| Setting | Configuration |
|---|---|
| Usage scope | Only pages where Kadence Blocks cannot achieve the required layout |
| Default colors/fonts | Disabled — use Kadence global palette to avoid conflicts |
| Elementor fonts | Disabled — fonts managed via Kadence or child theme |
| Unused widgets | Disabled via Elementor → Settings → Features/Experiments |
| DOM optimization | Enabled (Improved Asset Loading) |
| Flexbox container | Enabled — use containers instead of legacy sections |
| CSS print method | External file (not internal embedding) |
| Google Fonts | Disabled — managed centrally via theme |
| Editor loader method | Set to improve editor load time |

**Performance rule:** When Elementor is installed, verify that it does not load its CSS and JS on pages where it is not used. Use a conditional loading plugin or child theme filter if necessary:

```php
// Dequeue Elementor assets on non-Elementor pages
add_action( 'wp_enqueue_scripts', function() {
    if ( ! \Elementor\Plugin::$instance->documents->get( get_the_ID() )?->is_built_with_elementor() ) {
        wp_dequeue_style( 'elementor-frontend' );
        wp_dequeue_style( 'elementor-global' );
        wp_dequeue_script( 'elementor-frontend' );
    }
}, 999 );
```

### Rank Math Pro

| Setting | Configuration |
|---|---|
| Setup wizard | Complete on initial install — connect Search Console and Analytics |
| Title separator | `|` (pipe) |
| Schema type defaults | Organization on homepage; Article on blog posts; configured per post type |
| Local SEO module | Enabled — LocalBusiness schema configured with accurate NAP and geo data |
| Sitemap | Enabled — include posts, pages; exclude noindex, author archives, date archives |
| 404 monitor | Enabled — review weekly |
| Redirections | Enabled — manage all 301/302 redirects through Rank Math |
| Breadcrumbs | Enabled — unless Kadence breadcrumbs are used |
| Analytics | Connected to Google Search Console and GA4 |
| Instant Indexing | Enabled — auto-submit on publish/update |
| SEO score threshold | ≥ 80 before publishing |

**Configuration export location:** `templates/config/rank-math-settings.json`

### WP Rocket

| Setting | Configuration |
|---|---|
| Page caching | Enabled |
| Cache for mobile | Separate cache files for mobile devices |
| User cache | Disabled (unless membership/ecommerce site) |
| Cache lifespan | 10 hours |
| CSS minification | Enabled |
| CSS delivery | Remove unused CSS enabled; load CSS asynchronously |
| JS minification | Enabled |
| JS defer | Enabled for all non-critical JavaScript |
| JS delay | Enabled for analytics, chat widgets, and non-essential third-party scripts |
| Lazy loading | Enabled for images and iframes |
| Excluded images | Above-the-fold images excluded from lazy loading |
| Preloading | Sitemap-based preloading enabled; preload links enabled |
| Database cleanup | Enabled — auto-clean revisions, drafts, spam, transients on a weekly schedule |
| Heartbeat control | Reduce or disable on frontend; reduce in admin |
| CDN | Configure if CDN is active |

**Configuration export location:** `templates/config/wp-rocket-settings.json`

**Critical rule:** WP Rocket must be the only caching and optimization plugin. Never install Autoptimize, LiteSpeed Cache, W3 Total Cache, or any other optimization plugin alongside WP Rocket.

### Instant Indexing

| Setting | Configuration |
|---|---|
| API connection | Google Indexing API credentials configured |
| Auto-submit | Enabled for posts and pages on publish and update |
| Post types | Posts, pages, and any custom post type that should be indexed |
| Rate limit awareness | Google allows 200 publish/update notifications per day — batch large content operations accordingly |

---

## 4. Plugin Evaluation Process

When a project requires functionality not provided by the approved stack, a new plugin must be evaluated before installation.

### Evaluation Criteria

| Criterion | Minimum Standard | Weight |
|---|---|---|
| Active installations | ≥ 10,000 (exceptions for niche tools with documented justification) | High |
| Last updated | Within the past 6 months | High |
| WordPress compatibility | Tested with the current major WordPress version | Critical |
| PHP compatibility | Compatible with PHP 8.0+ | Critical |
| Rating | ≥ 4.0 stars on wordpress.org (for free plugins) | Medium |
| Support responsiveness | Active support forum with developer responses within 7 days | Medium |
| Codebase quality | No known unpatched vulnerabilities; follows WordPress coding standards | Critical |
| Performance impact | Does not degrade Core Web Vitals below target thresholds | Critical |
| Duplicate functionality | Does not replicate functionality already provided by an approved plugin | Critical |
| Data portability | Data can be exported or migrated if the plugin is removed | Medium |
| Update frequency | Regular updates indicating active maintenance | Medium |
| Dependencies | Minimal external dependencies; no required companion plugins | Medium |

### Evaluation Workflow

```
1. Identify Need
   └── Document what functionality is required and why
        │
2. Check Approved Stack
   └── Can an approved plugin already do this?
        │ No
        ▼
3. Research Candidates
   └── Find 2-3 candidate plugins that meet the evaluation criteria
        │
4. Test in Staging
   └── Install on staging; test functionality, performance impact, and conflicts
        │
5. Performance Benchmark
   └── Run PageSpeed Insights before and after installation
   └── Compare Core Web Vitals, page weight, and request count
        │
6. Security Review
   └── Check WPScan vulnerability database
   └── Check plugin changelog for security-related fixes
   └── Verify permissions and capability checks in the code
        │
7. Document Decision
   └── Record in ai/decisions/ — plugin name, reason, alternatives considered, test results
        │
8. Approve or Reject
   └── If approved → add to project's plugin list with configuration notes
   └── If rejected → document why and what alternative was chosen
```

### Performance Impact Assessment

Before approving any plugin, measure its impact:

| Metric | Measurement Method | Threshold |
|---|---|---|
| HTTP requests added | Browser DevTools Network tab — compare before/after | ≤ 3 additional requests |
| Page weight increase | Total transfer size before/after | ≤ 50 KB increase |
| TTFB impact | Server response time before/after | ≤ 50ms increase |
| LCP impact | PageSpeed Insights before/after | No regression below target |
| Database queries | Query Monitor plugin — compare before/after | ≤ 5 additional queries per page load |
| Autoloaded data | Check `wp_options` autoloaded size increase | ≤ 50 KB increase |

If a plugin fails any threshold, it requires documented justification to proceed.

### Security Review

| Check | Method |
|---|---|
| Known vulnerabilities | Search WPScan Vulnerability Database and Patchstack |
| Code review (critical plugins) | Review `readme.txt` changelog for security fixes; check sanitization and escaping in core files |
| Permissions | Verify the plugin checks `current_user_can()` before privileged operations |
| Data handling | Verify the plugin uses `$wpdb->prepare()` for database queries |
| External connections | Identify any external API calls, tracking pixels, or phone-home behavior |
| File uploads | If the plugin handles uploads, verify it validates file types and sanitizes filenames |

---

## 5. Plugin Update Workflow

### Update Schedule

| Update Type | Timeline | Process |
|---|---|---|
| Security updates | Within 24 hours of release | Test on staging → deploy to production immediately |
| Bug fix updates | Within 7 days of release | Test on staging → deploy during next maintenance window |
| Feature updates | Within 14 days of release | Test on staging → evaluate new features → deploy when stable |
| WordPress core updates (minor) | Within 7 days | Auto-update enabled; verify no conflicts |
| WordPress core updates (major) | Within 14 days | Test all plugins on staging with new core version first |

### Update Process

```
1. Notification
   └── Monitor plugin update notifications via WordPress admin or ManageWP
        │
2. Changelog Review
   └── Read the changelog — identify breaking changes, security fixes, new features
        │
3. Staging Update
   └── Update on staging environment first — never directly on production
        │
4. Functional Testing
   └── Verify core functionality still works
   └── Check pages built with the plugin render correctly
   └── Test forms, schema output, caching behavior
        │
5. Performance Testing
   └── Run PageSpeed Insights on key pages
   └── Compare to pre-update baseline
        │
6. Production Deployment
   └── If staging passes → update production
   └── Document the update in CHANGELOG.md
        │
7. Post-Update Verification
   └── Verify production site is functional
   └── Clear WP Rocket cache
   └── Spot-check 3-5 key pages
```

### Rollback Protocol

If a plugin update causes issues:

1. Revert to the previous plugin version (keep a copy of all plugin ZIPs before updating)
2. Document the issue — plugin name, version, error description
3. Report the bug to the plugin developer
4. Monitor for a patched release
5. Do not re-update until the issue is confirmed fixed

---

## 6. Licensing

### License Tracking

| Field | Tracked |
|---|---|
| Plugin name | Yes |
| License type | Free / Commercial (annual) / Commercial (lifetime) |
| License key location | Environment variable or secure password manager — never in Git |
| Renewal date | Yes — tracked in agency operations calendar |
| Licensed domains | Yes — verify each production site is covered |
| Cost per year | Yes — tracked for agency budgeting |

### License Rules

| Rule | Detail |
|---|---|
| Never commit license keys to Git | Store in `wp-config.php` via environment variables or in the WordPress admin |
| Track renewal dates | Set reminders 30 days before expiration |
| Verify domain coverage | Ensure the license covers the number of active sites |
| Budget annually | Include plugin costs in project budgets and agency operating costs |
| Have fallback plans | For every commercial plugin, document the fallback if the license lapses or the plugin is discontinued |

### License Registry Template

Maintain a license registry per project (stored securely outside Git):

| Plugin | License Type | Renewal Date | Domains Covered | Annual Cost | Fallback |
|---|---|---|---|---|---|
| Kadence Blocks Pro | Commercial (annual) | YYYY-MM-DD | Unlimited / N sites | $XX | Kadence free blocks + custom blocks |
| Rank Math Pro | Commercial (annual) | YYYY-MM-DD | N sites | $XX | Rank Math Free (reduced features) |
| WP Rocket | Commercial (annual) | YYYY-MM-DD | N sites | $XX | WP Super Cache (free, reduced features) |
| Elementor Pro | Commercial (annual) | YYYY-MM-DD | N sites | $XX | Kadence Blocks (rebuild layouts) |

---

## 7. Version Control for Plugins

### What Goes Into Git

| Item | Git Tracked | Reason |
|---|---|---|
| Custom plugins (agency-built) | Yes | Our code — version controlled |
| MU plugins (agency-built) | Yes | Our code — version controlled |
| Third-party plugin files | No | Managed via updates, not version control |
| Plugin configuration exports | Yes | Reproducible setup across environments |
| Plugin list with versions | Yes | Documented in project manifest |

### Plugin Manifest

Every project maintains a `plugins.json` or `plugins.md` manifest documenting the exact plugin stack:

```json
{
  "plugins": [
    {
      "name": "Kadence Blocks Pro",
      "slug": "kadence-blocks-pro",
      "version": "2.x.x",
      "status": "active",
      "source": "commercial",
      "required": true
    },
    {
      "name": "Rank Math Pro",
      "slug": "seo-by-rank-math-pro",
      "version": "3.x.x",
      "status": "active",
      "source": "commercial",
      "required": true
    },
    {
      "name": "WP Rocket",
      "slug": "wp-rocket",
      "version": "3.x.x",
      "status": "active",
      "source": "commercial",
      "required": true
    },
    {
      "name": "Instant Indexing",
      "slug": "flavor/flavor-flavor",
      "version": "1.x.x",
      "status": "active",
      "source": "free",
      "required": true
    }
  ]
}
```

### Configuration Exports

Approved plugins with exportable settings must have their configurations saved in the repository:

```
templates/config/
├── kadence-blocks-settings.json
├── kadence-theme-settings.json
├── rank-math-settings.json
├── wp-rocket-settings.json
└── elementor-settings.json (when Elementor is used)
```

These exports allow any new project to be configured identically by importing saved settings, eliminating manual configuration.

---

## 8. Custom Plugin Development

When functionality cannot be achieved by an approved plugin and is independent of the theme, a custom plugin is the correct approach.

### When to Build a Custom Plugin

| Scenario | Approach |
|---|---|
| Functionality tied to the theme's presentation | Child theme (`functions.php` or `inc/`) |
| Functionality independent of the theme (persists across theme changes) | Custom plugin |
| Functionality that must always be active and cannot be deactivated | MU plugin |
| Simple hook or filter (under 30 lines) | MU plugin or child theme `inc/hooks.php` |
| Complex system with multiple files and its own admin UI | Custom plugin |

### Custom Plugin Structure

```
wp-content/plugins/waif-{plugin-name}/
├── waif-{plugin-name}.php          # Main plugin file with header
├── readme.txt                       # Plugin description and changelog
├── includes/
│   ├── class-main.php              # Primary class
│   ├── class-admin.php             # Admin functionality
│   └── class-frontend.php          # Frontend functionality
├── assets/
│   ├── css/
│   └── js/
├── templates/                       # Template overrides (if applicable)
└── languages/                       # Translation files
```

### Plugin Header

```php
<?php
/**
 * Plugin Name: WAIF Custom Schema
 * Plugin URI:  https://github.com/agency/waif-custom-schema
 * Description: Custom JSON-LD schema generation for the WordPress AI Framework.
 * Version:     1.0.0
 * Author:      Agency Name
 * Author URI:  https://agency-domain.com
 * License:     Proprietary
 * Text Domain: waif-custom-schema
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WAIF_CUSTOM_SCHEMA_VERSION', '1.0.0' );
define( 'WAIF_CUSTOM_SCHEMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'WAIF_CUSTOM_SCHEMA_URL', plugin_dir_url( __FILE__ ) );
```

### Custom Plugin Rules

| Rule | Detail |
|---|---|
| Prefix everything | All functions, classes, constants, and hooks use the `waif_` or plugin-specific prefix |
| No global functions | Encapsulate in classes or namespaced functions |
| Activation/deactivation hooks | Register cleanup and setup routines properly |
| Uninstall hook | Clean up database tables, options, and transients on uninstall |
| Follow CODING_STANDARDS.md | All coding standards apply to custom plugins |
| Version the plugin | Semantic versioning in the plugin header and a `WAIF_*_VERSION` constant |
| Document the plugin | Include a `readme.txt` and inline PHPDoc |

---

## 9. MU Plugins

Must-Use plugins live in `wp-content/mu-plugins/` and are always active. They cannot be deactivated from the admin panel.

### When to Use MU Plugins

| Use Case | Example |
|---|---|
| Security hardening that must never be disabled | Disable XML-RPC, force strong passwords, remove version leaks |
| Performance rules that apply universally | Disable emojis, remove jQuery migrate, limit post revisions |
| Framework-level hooks that must persist | Custom login URL, admin bar removal for non-admins |
| Features the client must not accidentally deactivate | Custom post type registration for critical content types |

### MU Plugin Structure

```
wp-content/mu-plugins/
├── waif-security.php          # Security hardening
├── waif-performance.php       # Performance optimizations
├── waif-cleanup.php           # WordPress cleanup (remove bloat)
└── waif-custom-post-types.php # CPT registrations (if critical)
```

### Example: Security MU Plugin

```php
<?php
/**
 * WAIF Security Hardening
 *
 * Must-Use plugin that enforces security standards.
 * Cannot be deactivated from the admin panel.
 *
 * @package Waif
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Disable XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );

// Remove WordPress version from head and feeds
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Disable file editing from admin
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

// Remove really simple discovery link
remove_action( 'wp_head', 'rsd_link' );

// Remove Windows Live Writer manifest link
remove_action( 'wp_head', 'wlwmanifest_link' );

// Remove shortlink
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

// Disable REST API user enumeration for unauthenticated requests
add_filter( 'rest_endpoints', function( array $endpoints ): array {
    if ( ! is_user_logged_in() ) {
        unset( $endpoints['/wp/v2/users'] );
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
} );
```

### Example: Performance MU Plugin

```php
<?php
/**
 * WAIF Performance Optimizations
 *
 * Must-Use plugin that enforces performance standards.
 *
 * @package Waif
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Disable WordPress emoji scripts and styles
add_action( 'init', function(): void {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    add_filter( 'emoji_svg_url', '__return_false' );
} );

// Disable self-pingbacks
add_action( 'pre_ping', function( array &$links ): void {
    $home = home_url();
    foreach ( $links as $key => $link ) {
        if ( str_starts_with( $link, $home ) ) {
            unset( $links[ $key ] );
        }
    }
} );

// Remove jQuery Migrate on frontend
add_action( 'wp_default_scripts', function( \WP_Scripts $scripts ): void {
    if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ],
        );
    }
} );

// Limit post revisions
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
    define( 'WP_POST_REVISIONS', 5 );
}

// Disable heartbeat on frontend
add_action( 'init', function(): void {
    if ( ! is_admin() ) {
        wp_deregister_script( 'heartbeat' );
    }
} );
```

### MU Plugin Rules

| Rule | Detail |
|---|---|
| File naming | `waif-{purpose}.php` — one file per concern |
| No subdirectories | WordPress only auto-loads PHP files in the root of `mu-plugins/`. Subdirectories require a loader file. |
| Git tracked | All MU plugins are version controlled |
| No admin UI | MU plugins should be invisible — no settings pages, no dashboard widgets |
| Minimal | Each file focuses on a single concern — security, performance, cleanup |
| Documented | Inline comments explaining every hook and filter |

---

## 10. Plugin Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Plugin folder | `waif-{descriptive-name}` | `waif-custom-schema` |
| Main plugin file | `waif-{descriptive-name}.php` | `waif-custom-schema.php` |
| PHP classes | `Waif_{Descriptive_Name}` or `Waif\PluginName\ClassName` | `Waif_Custom_Schema` |
| Functions | `waif_{plugin}_{function}()` | `waif_schema_generate_local()` |
| Constants | `WAIF_{PLUGIN}_CONSTANT` | `WAIF_CUSTOM_SCHEMA_VERSION` |
| Options | `waif_{plugin}_{option}` | `waif_schema_default_type` |
| Transients | `waif_{plugin}_{transient}` | `waif_schema_cache_homepage` |
| Hooks (custom) | `waif/{plugin}/{hook}` | `waif/schema/before_output` |
| Text domain | `waif-{plugin-name}` | `waif-custom-schema` |
| Database tables (rare) | `{$wpdb->prefix}waif_{table}` | `wp_waif_schema_log` |
| MU plugins | `waif-{purpose}.php` | `waif-security.php` |

---

## 11. Plugin Documentation

Every custom plugin and every approved plugin configuration must be documented.

### Custom Plugin Documentation

Each custom plugin must include:

| Document | Location | Content |
|---|---|---|
| Plugin header | Main plugin file | Name, description, version, author, requirements |
| `readme.txt` | Plugin root | Description, installation, FAQ, changelog |
| Inline PHPDoc | Every public method | Parameters, return type, description, `@since` |
| Decision record | `ai/decisions/` | Why this plugin was built, alternatives considered |

### Approved Plugin Documentation

For each approved plugin, the repository contains:

| Document | Location | Content |
|---|---|---|
| Configuration export | `templates/config/` | Importable settings file |
| Configuration notes | `docs/plugins/` | Non-obvious settings choices and their reasoning |
| Customization hooks | `docs/plugins/` | Hooks and filters used to extend the plugin |

---

## 12. Plugin Removal Process

Removing a plugin is not as simple as deactivating and deleting. Improper removal creates orphaned data, broken functionality, and potential errors.

### Removal Workflow

```
1. Impact Assessment
   └── Identify all pages, features, and shortcodes that depend on the plugin
        │
2. Dependency Check
   └── Check if other plugins depend on this one
   └── Check if any child theme code references this plugin's functions or classes
        │
3. Content Migration
   └── Export any content stored by the plugin (custom post types, meta, settings)
   └── Migrate if replacement is planned
        │
4. Staged Removal
   └── Deactivate on staging first
   └── Test all affected pages
   └── Check error logs
        │
5. Database Cleanup
   └── Remove orphaned options from wp_options
   └── Remove orphaned post meta and term meta
   └── Drop custom database tables (if any)
   └── Clean orphaned transients
        │
6. File Removal
   └── Delete plugin files from staging
   └── Verify no errors
        │
7. Production Deployment
   └── Repeat deactivation and deletion on production
   └── Clear all caches (WP Rocket, object cache, CDN)
        │
8. Documentation
   └── Update plugin manifest
   └── Document removal reason in ai/decisions/
   └── Update any documentation that referenced the plugin
```

### Database Cleanup Queries

After deactivating a plugin, check for orphaned data:

```sql
-- Find plugin options
SELECT option_name, LENGTH(option_value) AS size
FROM wp_options
WHERE option_name LIKE '%plugin_slug%'
ORDER BY size DESC;

-- Find plugin post meta
SELECT DISTINCT meta_key
FROM wp_postmeta
WHERE meta_key LIKE '%plugin_prefix%';

-- Find plugin transients
SELECT option_name
FROM wp_options
WHERE option_name LIKE '_transient_%plugin_slug%'
   OR option_name LIKE '_site_transient_%plugin_slug%';

-- Check for custom tables
SHOW TABLES LIKE '%plugin_prefix%';
```

---

## 13. AI Plugin Rules

### Before Any Plugin Decision

1. Read this document (`PLUGIN_STANDARDS.md`) in full
2. Check the Approved Plugin List — the functionality may already be covered
3. Check the Plugin Responsibility Matrix — do not duplicate functionality
4. Read `WORDPRESS_STANDARDS.md` Section 4 (Plugin Standards) for additional context

### Mandatory Rules for AI Assistants

| Rule | Detail |
|---|---|
| Never install a plugin without checking the approved list first | The approved stack covers SEO, caching, page building, and indexing — most needs are already met |
| Never install two plugins that do the same thing | Check the Responsibility Matrix before suggesting any plugin |
| Never modify third-party plugin files | Use hooks, filters, or companion custom plugins |
| Never recommend a plugin without evaluating it | Follow the evaluation criteria before suggesting installation |
| Always check performance impact | A plugin that degrades Core Web Vitals below targets is not acceptable |
| Always document plugin decisions | Record in `ai/decisions/` — plugin name, reason, alternatives, test results |
| Never store license keys in Git | Environment variables or wp-config.php only |
| Always dequeue unused assets | If a plugin loads assets on pages where it is not used, dequeue them |
| Never skip the removal process | Deactivating is not enough — follow the full removal workflow |
| Prefer the theme over a plugin | If Kadence can do it, do not install a plugin for it |
| Prefer custom code over a plugin | If the functionality is 30 lines of code, write it — do not install a 50-file plugin |

### Plugin Suggestion Format

When an AI assistant recommends a plugin, it must provide:

```
Plugin: [Name]
Purpose: [What it does and why it's needed]
Alternatives Considered: [What else was evaluated]
Approved Stack Conflict: [Does it overlap with an approved plugin? If so, why is it still needed?]
Performance Impact: [Expected HTTP requests, page weight, query additions]
Security Check: [Vulnerability database clear? Last updated?]
Recommendation: Install / Do Not Install
```

---

## 14. Common Plugin Mistakes

| Mistake | Why It Happens | Correct Approach |
|---|---|---|
| Installing a caching plugin alongside WP Rocket | "Two caches are better than one" | WP Rocket is the only caching solution — remove all others |
| Installing Yoast alongside Rank Math | Migration confusion or habit from past projects | One SEO plugin only — Rank Math Pro is the standard |
| Installing an image optimization plugin and a lazy loading plugin and WP Rocket lazy loading | Each seems to solve a different piece | WP Rocket handles lazy loading; use one image optimizer at most |
| Not dequeuing Elementor on non-Elementor pages | Default behavior loads assets globally | Use conditional dequeuing in child theme |
| Installing a redirect plugin when Rank Math handles redirects | Not knowing Rank Math has a redirect module | Use Rank Math Redirections module |
| Installing a schema plugin when Rank Math handles schema | Same as above | Use Rank Math Schema module; custom schema via child theme code |
| Leaving deactivated plugins installed | "Might need it later" | Deactivated plugins are a security risk — delete them |
| Installing plugins on production without testing | Urgency or overconfidence | Always test on staging first |
| Updating all plugins at once in production | Convenience | Update one at a time on staging; deploy incrementally |
| Not reading the changelog before updating | Assuming updates are always safe | Always review the changelog for breaking changes |
| Installing a plugin for 10 lines of functionality | Path of least resistance | Write a simple MU plugin or child theme function |
| Not cleaning up after plugin removal | "Deleting the files is enough" | Follow the full removal process including database cleanup |
| Using nulled/pirated premium plugins | Cost savings | Never — security risk, no updates, no support, legal liability |
| Storing license keys in the repository | Quick setup | Never — use environment variables or secure storage |
| Installing plugins recommended by compromised or spam sites | SEO research leads to plugin recommendation sites | Only evaluate plugins from wordpress.org, vendor sites, or trusted sources |

---

## 15. Plugin QA Checklist

Before a project goes to production, verify every plugin item:

### Installation and Configuration

- [ ] Only approved plugins are installed (deviations documented and approved)
- [ ] No two plugins serve the same function
- [ ] All plugins are up to date
- [ ] All plugin configurations match the documented standards
- [ ] Configuration exports are saved in `templates/config/`
- [ ] Plugin manifest (`plugins.json` or `plugins.md`) is current

### Performance

- [ ] No plugin loads assets on pages where it is not used
- [ ] Elementor assets dequeued on non-Elementor pages (if applicable)
- [ ] Total active plugin count ≤ 15
- [ ] WP Rocket is the only caching plugin
- [ ] No duplicate optimization plugins (lazy loading, minification, etc.)
- [ ] Core Web Vitals not degraded below targets

### Security

- [ ] No deactivated plugins left installed
- [ ] No nulled or pirated plugins installed
- [ ] License keys stored outside Git (environment variables or wp-config)
- [ ] All plugins from verified sources (wordpress.org or vendor)
- [ ] XML-RPC disabled via MU plugin (unless specifically required)
- [ ] File editor disabled
- [ ] MU security plugin active

### SEO

- [ ] Rank Math Pro is the only SEO plugin
- [ ] Rank Math sitemap is the only sitemap
- [ ] Rank Math breadcrumbs or Kadence breadcrumbs (not both)
- [ ] Rank Math redirections managing all 301 redirects
- [ ] Instant Indexing connected and auto-submitting

### Maintenance

- [ ] Update workflow documented
- [ ] License renewal dates tracked
- [ ] Staging environment mirrors production plugin stack
- [ ] Rollback process documented and tested

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — complete plugin management standards defined |