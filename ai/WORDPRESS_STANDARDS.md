# WORDPRESS_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — applies to every project built with this framework |

> **This document is the single source of truth for all WordPress development decisions.**
> No AI assistant or developer should guess how a WordPress project should be built. If this document does not cover a specific case, raise it — do not improvise.

---

## 1. WordPress Philosophy

WordPress is the platform. It is not the product. The product is the website we deliver to the client. WordPress is the engine underneath — and the engine must never be modified.

### Core Rules

| Rule | Enforcement |
|---|---|
| Never modify WordPress Core files | Absolute — no exceptions |
| Never modify third-party plugin files | Absolute — no exceptions |
| Never modify the Kadence Parent Theme | Absolute — use child theme only |
| Never add code to `functions.php` of the parent theme | Absolute — child theme only |

### Approved Extension Methods

All customization must use one of these methods:

| Method | When to Use |
|---|---|
| **Kadence Child Theme** | Theme-level overrides: templates, styles, theme functions |
| **Custom Plugins** | Functionality independent of the theme that should persist across theme changes |
| **MU Plugins** | Functionality that must always be active, cannot be deactivated by the client |
| **Hooks (`add_action`)** | Injecting behavior at specific WordPress execution points |
| **Filters (`add_filter`)** | Modifying data as it passes through WordPress processing |

### Why This Matters

WordPress updates overwrite core files. Plugin updates overwrite plugin files. Theme updates overwrite parent theme files. Any modification to these files will be lost on the next update and will create security vulnerabilities if updates are delayed to preserve modifications.

---

## 2. Project Structure

Every WordPress project follows this directory structure. AI assistants and developers must understand the purpose of each directory.

```
wp-content/
│
├── themes/
│   ├── flavflavor/flavor/             # Kadence Parent Theme — NEVER MODIFY
│   └── flavor-child/          # Kadence Child Theme — all theme customization here
│       ├── functions.php      # Theme functions, hooks, filters, enqueues
│       ├── style.css          # Child theme stylesheet and header declaration
│       ├── template-parts/    # Reusable template partials
│       ├── templates/         # Custom page templates
│       ├── assets/
│       │   ├── css/           # Custom stylesheets
│       │   ├── js/            # Custom scripts
│       │   ├── images/        # Theme-level images (logos, icons)
│       │   └── fonts/         # Self-hosted fonts (if not using Google Fonts)
│       └── inc/               # PHP includes — organized by concern
│           ├── hooks.php      # Action and filter registrations
│           ├── helpers.php    # Utility functions
│           ├── enqueue.php    # Script and style registration
│           └── customizer.php # Theme customizer extensions
│
├── plugins/                   # Standard plugins — managed via admin or composer
│
├── mu-plugins/                # Must-Use plugins — always active, no admin toggle
│   ├── framework-core.php     # Framework-level functionality
│   └── security-hardening.php # Security rules that must never be disabled
│
├── uploads/                   # Media library — NEVER commit to Git
│
└── languages/                 # Translation files
```

### Directory Rules

| Directory | Git Tracked | Modifiable | Notes |
|---|---|---|---|
| `themes/kadence/` | No | Never | Parent theme managed by updates |
| `themes/kadence-child/` | Yes | Yes | All theme customization lives here |
| `plugins/` | Selectively | Never modify plugin files | Track custom plugins only |
| `mu-plugins/` | Yes | Yes | Framework-level code |
| `uploads/` | Never | N/A | Media managed outside Git |
| `languages/` | Optional | Yes | Translation overrides |

---

## 3. Theme Standards

### Default Theme

| Component | Technology | Status |
|---|---|---|
| Parent Theme | Kadence Theme | Mandatory — never change |
| Child Theme | Kadence Child Theme | Mandatory — must be active on every project |
| Block Editor | Kadence Blocks Pro | Default for all standard layouts |
| Page Builder | Elementor | Exception only — used when Kadence Blocks cannot deliver the design |

### When to Use Kadence vs. Elementor

| Scenario | Builder |
|---|---|
| Standard page layouts (service pages, about, contact) | Kadence |
| Blog posts and archives | Kadence |
| Headers and footers | Kadence |
| Simple landing pages | Kadence |
| Complex multi-column layouts with advanced animations | Elementor |
| Pages requiring Elementor-specific widgets or integrations | Elementor |
| Location pages with intricate design requirements | Elementor |

**Default is always Kadence.** Elementor is only introduced when there is a documented reason that Kadence Blocks cannot achieve the required result.

### Template Hierarchy

WordPress loads templates in a specific order of precedence. AI assistants must understand this hierarchy when creating or modifying templates in the child theme.

```
1. Page Template (assigned in editor)     → templates/template-custom.php
2. Specific Template                       → page-{slug}.php or page-{id}.php
3. General Template                        → page.php
4. Singular Template                       → singular.php
5. Index Fallback                          → index.php
```

For custom post types:

```
1. single-{post-type}-{slug}.php
2. single-{post-type}.php
3. single.php
4. singular.php
5. index.php
```

For archives:

```
1. archive-{post-type}.php
2. archive.php
3. index.php
```

### Child Theme Rules

- The child theme `style.css` must contain the proper theme header referencing Kadence as the parent
- `functions.php` must not become a monolith — split logic into files in the `inc/` directory and require them
- All custom CSS goes in `assets/css/`, enqueued properly — never inline large blocks of CSS in `functions.php`
- All custom JavaScript goes in `assets/js/`, enqueued with proper dependencies and `defer` or `async` attributes
- Template overrides must mirror the parent theme's file path exactly

---

## 4. Plugin Standards

### Approved Plugin List

These plugins are pre-approved for every project. No justification is needed to include them.

| Plugin | Purpose | Status |
|---|---|---|
| Kadence Blocks Pro | Block-based page building | Core — always installed |
| Elementor | Advanced visual page building | Conditional — install only when needed |
| Rank Math Pro | SEO management, schema, sitemaps | Core — always installed |
| WP Rocket | Performance optimization and caching | Core — always installed |
| Instant Indexing | Google Indexing API integration | Core — always installed |

### Plugin Evaluation Criteria

Before any new plugin is added to a project, it must pass these checks:

| Criterion | Minimum Standard |
|---|---|
| Active installations | 10,000+ (exceptions for niche tools with documented justification) |
| Last updated | Within the past 6 months |
| WordPress compatibility | Tested with the current major WordPress version |
| PHP compatibility | Compatible with PHP 8.0+ |
| Performance impact | Must not degrade Core Web Vitals below target thresholds |
| Duplicate functionality | Must not replicate functionality already provided by an approved plugin |
| Security history | No unpatched critical vulnerabilities |
| Support responsiveness | Active support forum or documented issue resolution |

### Plugin Approval Process

1. Identify the need — document what functionality is required
2. Check if an approved plugin already provides this functionality
3. If not, evaluate candidate plugins against the criteria above
4. Document the evaluation in `docs/decisions/`
5. Install and test in staging before production
6. Add to the project's approved plugin list if accepted

### Plugin Removal

Plugins that are deactivated must be deleted, not left installed. Inactive plugins are a security risk and add unnecessary file weight. Before removing a plugin:

1. Verify no shortcodes, widgets, or custom post types depend on it
2. Check for orphaned database tables — clean them
3. Document the removal and reason

### Plugin Count Target

Aim for fewer than 15 active plugins per project. Every plugin adds:

- HTTP requests (scripts, styles)
- Database queries
- Potential security surface
- Update maintenance burden

If the plugin count exceeds 15, audit the list and consolidate or remove.

---

## 5. Development Standards

### Hooks and Filters

WordPress extensibility is built on hooks. All custom behavior must use them.

**Actions** — execute code at a specific point:

```php
add_action('wp_enqueue_scripts', 'theme_enqueue_assets');
add_action('init', 'register_custom_post_types');
add_action('wp_head', 'output_custom_schema', 99);
```

**Filters** — modify data before it is used:

```php
add_filter('the_content', 'append_cta_to_posts');
add_filter('upload_mimes', 'allow_svg_uploads');
add_filter('excerpt_length', 'set_custom_excerpt_length');
```

### Priority and Load Order

- Use priority `10` (default) for standard operations
- Use priority `99` or higher when code must execute after all other hooks
- Use priority `1` when code must execute before everything else
- Document non-default priorities with inline comments explaining why

### Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Functions | `{project_prefix}_{descriptive_name}` | `waif_enqueue_scripts()` |
| Hooks (custom) | `{project_prefix}/{hook_name}` | `waif/after_hero_section` |
| CSS classes | `{prefix}-{block}-{element}` (BEM-like) | `waif-hero-title` |
| JavaScript functions | camelCase | `initMobileMenu()` |
| PHP files | lowercase, hyphen-separated | `custom-post-types.php` |
| Template parts | lowercase, hyphen-separated | `template-parts/hero-section.php` |
| Constants | UPPER_SNAKE_CASE | `WAIF_VERSION` |
| Database options | `{prefix}_{option_name}` | `waif_schema_settings` |

`waif` = WordPress AI Framework (project prefix). Projects may define their own prefix.

### File Organization

```
inc/
├── hooks.php            # All add_action / add_filter registrations
├── helpers.php          # Utility functions (formatting, sanitization, etc.)
├── enqueue.php          # wp_enqueue_script / wp_enqueue_style calls
├── customizer.php       # Theme Customizer API extensions
├── post-types.php       # Custom Post Type registrations
├── taxonomies.php       # Custom Taxonomy registrations
├── shortcodes.php       # Shortcode definitions (if required)
├── widgets.php          # Custom widget registrations (if required)
├── schema.php           # JSON-LD structured data output
└── admin/               # Admin-only functionality
    ├── columns.php      # Custom admin list table columns
    └── meta-boxes.php   # Custom meta box registrations
```

### Code Quality Rules

- No `?>` closing PHP tag in files that are pure PHP
- No inline `<style>` or `<script>` blocks in templates — enqueue properly
- No direct database queries without `$wpdb->prepare()` for parameterized input
- No `echo` of unescaped user input — use `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses()`
- No hardcoded URLs — use `home_url()`, `get_template_directory_uri()`, `plugin_dir_url()`
- No hardcoded text — use translation functions (`__()`, `_e()`, `esc_html__()`) for user-facing strings

### Separation of Concerns

| Layer | Responsibility | Location |
|---|---|---|
| Presentation | HTML structure and CSS styling | Templates, `assets/css/` |
| Logic | PHP processing, data retrieval | `inc/` files, plugins |
| Data | Database interaction, API calls | Models, `$wpdb`, REST API |

Never mix database queries into template files. Never put HTML rendering inside logic files.

---

## 6. Performance Standards

### Core Web Vitals Targets

| Metric | Target | Maximum Acceptable |
|---|---|---|
| Largest Contentful Paint (LCP) | ≤ 1.5s | ≤ 2.5s |
| Interaction to Next Paint (INP) | ≤ 100ms | ≤ 200ms |
| Cumulative Layout Shift (CLS) | ≤ 0.05 | ≤ 0.1 |
| First Contentful Paint (FCP) | ≤ 1.0s | ≤ 1.8s |
| Time to First Byte (TTFB) | ≤ 400ms | ≤ 800ms |

These are measured on mobile using Google PageSpeed Insights under real-world conditions.

### Page Weight Targets

| Component | Target | Maximum |
|---|---|---|
| Total page weight | ≤ 1.5 MB | ≤ 3.0 MB |
| HTML document | ≤ 50 KB | ≤ 100 KB |
| Total CSS | ≤ 100 KB | ≤ 200 KB |
| Total JavaScript | ≤ 200 KB | ≤ 400 KB |
| Hero image | ≤ 150 KB | ≤ 300 KB |

### Image Optimization

| Standard | Requirement |
|---|---|
| Format | WebP as primary; AVIF where supported; JPEG/PNG as fallback only |
| Compression | Lossy compression with quality 75-85% for photographs |
| Dimensions | Serve images at the exact rendered size — no oversized images |
| Lazy loading | All images below the fold must use `loading="lazy"` |
| Above-the-fold images | Must NOT be lazy loaded — use `loading="eager"` or `fetchpriority="high"` |
| Alt text | Every image must have descriptive, keyword-relevant alt text |
| Aspect ratio | Always define `width` and `height` attributes to prevent CLS |

### Font Optimization

| Standard | Requirement |
|---|---|
| Font count | Maximum 2 font families per project |
| Font weights | Load only the weights actually used (typically 400, 500, 700) |
| Loading strategy | `font-display: swap` to prevent invisible text during load |
| Self-hosting | Preferred over Google Fonts CDN for privacy and performance |
| Preloading | Preload critical font files used above the fold |

### JavaScript Optimization

| Standard | Requirement |
|---|---|
| Defer non-critical JS | All JavaScript not required for above-the-fold rendering |
| Async analytics | Analytics and tracking scripts loaded asynchronously |
| No jQuery dependency | Avoid jQuery in new code — use vanilla JavaScript |
| Bundle size | Individual scripts should not exceed 50 KB gzipped |
| Inline critical JS | Only for scripts under 1 KB that block rendering |

### CSS Optimization

| Standard | Requirement |
|---|---|
| Critical CSS | Inline critical CSS for above-the-fold content |
| Unused CSS removal | Remove unused CSS rules — WP Rocket handles this partially |
| No `!important` abuse | Use specificity properly instead of `!important` overrides |
| CSS architecture | Use a consistent methodology (BEM or utility-based) |

### Caching Standards

| Layer | Tool | Configuration |
|---|---|---|
| Page cache | WP Rocket | Enabled — separate cache for mobile |
| Browser cache | `.htaccess` / server config | Static assets cached for 1 year with versioned filenames |
| Object cache | Redis / Memcached (if available) | Recommended for database-heavy sites |
| CDN | Cloudflare or provider CDN | Recommended for all production sites |

### Database Optimization

- Remove post revisions beyond the most recent 5 per post (`define('WP_POST_REVISIONS', 5)`)
- Clean transient data regularly
- Remove spam and trashed comments
- Optimize autoloaded options — keep total autoloaded data under 800 KB
- Remove orphaned post meta and term meta

---

## 7. Security Standards

### File Permissions

| Path | Permission | Notes |
|---|---|---|
| Directories | `755` | Read + execute for all, write for owner only |
| Files | `644` | Read for all, write for owner only |
| `wp-config.php` | `440` or `400` | Read-only — most restrictive possible |
| `.htaccess` | `644` | Required by WordPress for permalink rewrites |
| `uploads/` | `755` | Write access required for media uploads |

### wp-config.php Security

The following constants must be set in `wp-config.php` for every project:

```php
// Disable file editing from admin panel
define('DISALLOW_FILE_EDIT', true);

// Limit post revisions
define('WP_POST_REVISIONS', 5);

// Enforce SSL for admin
define('FORCE_SSL_ADMIN', true);

// Disable automatic updates for major versions (managed via deployment)
define('WP_AUTO_UPDATE_CORE', 'minor');

// Move wp-content if server allows
// define('WP_CONTENT_DIR', '/path/to/wp-content');

// Set cookie domain for multi-subdomain setups
// define('COOKIE_DOMAIN', '.example.com');
```

### Authentication Security

| Standard | Requirement |
|---|---|
| Admin username | Never use `admin`, `administrator`, or the site name |
| Password strength | Minimum 16 characters, generated randomly |
| Login attempts | Limit to 5 failed attempts per 15-minute window |
| Two-factor authentication | Recommended for all admin accounts |
| Session duration | Auto-logout after 24 hours of inactivity |

### Access Control

| Principle | Implementation |
|---|---|
| Least privilege | Every user account has the minimum role needed — no Editor accounts with admin capabilities |
| XML-RPC | Disabled unless specifically required (Jetpack, mobile apps) |
| REST API | Restrict unauthenticated access to sensitive endpoints |
| Admin path | Consider non-standard login URL (via plugin, not by renaming wp-login.php) |
| File editor | Disabled via `DISALLOW_FILE_EDIT` |

### Security Headers

The following headers should be set via `.htaccess` or server configuration:

| Header | Value | Purpose |
|---|---|---|
| `X-Content-Type-Options` | `nosniff` | Prevent MIME type sniffing |
| `X-Frame-Options` | `SAMEORIGIN` | Prevent clickjacking |
| `X-XSS-Protection` | `1; mode=block` | Enable XSS filter |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Control referrer information |
| `Permissions-Policy` | `geolocation=(), camera=(), microphone=()` | Restrict browser feature access |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Enforce HTTPS |

### Secrets Management

| Rule | Detail |
|---|---|
| Never commit secrets to Git | No API keys, passwords, or tokens in repository files |
| Use environment variables | Store sensitive values in server environment or `.env` files |
| `.env` in `.gitignore` | Always — verify before every commit |
| Rotate credentials | Change passwords and API keys on a regular schedule |
| Separate credentials per environment | Staging and production must never share database credentials |

### Backup Standards

| Standard | Requirement |
|---|---|
| Frequency | Daily automated backups for production sites |
| Scope | Full site: files + database |
| Retention | Minimum 30 days of daily backups |
| Storage | Off-server — cloud storage (S3, Google Cloud, or equivalent) |
| Testing | Restore from backup at least once per quarter to verify integrity |

---

## 8. SEO Standards Overview

> **Detailed SEO implementation standards are defined in `SEO_STANDARDS.md`.**
> This section provides the architectural overview only.

### Core SEO Requirements

Every page deployed to production must include:

| Requirement | Standard |
|---|---|
| Heading hierarchy | Single H1 per page, logical H2–H6 nesting |
| Title tag | Unique, keyword-targeted, ≤ 60 characters |
| Meta description | Unique, action-oriented, ≤ 160 characters |
| Schema markup | Appropriate JSON-LD type per page (LocalBusiness, FAQPage, Service, etc.) |
| Internal linking | Minimum 3 relevant internal links per content page |
| Canonical tag | Self-referencing by default |
| Open Graph tags | Title, description, image for social platforms |
| XML Sitemap | Auto-generated by Rank Math, submitted to Search Console |
| Robots.txt | Properly configured — allow crawling, block admin and irrelevant paths |
| Image alt text | Descriptive, keyword-relevant, present on every image |
| URL structure | Short, lowercase, hyphen-separated, descriptive |
| Mobile-first | All pages fully responsive and optimized for mobile-first indexing |

### Indexing Workflow

1. Page is built and passes QA checklist
2. Schema markup is validated using Google Rich Results Test
3. Page is submitted via Instant Indexing plugin (Google Indexing API)
4. Indexing status is verified in Google Search Console
5. Page is added to internal linking structure

### SEO Tool Configuration

| Tool | Responsibility |
|---|---|
| Rank Math Pro | On-page SEO, schema, sitemaps, redirects, breadcrumbs |
| Instant Indexing | Rapid page submission to Google Indexing API |
| Google Search Console | Index monitoring, crawl error detection, performance tracking |
| Google Analytics 4 | Traffic analysis, conversion tracking, user behavior |

---

## 9. Deployment Standards

### Branching Strategy

```
main              ← Production-ready code. Every commit is deployable.
  └── feature/*   ← Feature branches. One branch per task or feature.
  └── fix/*       ← Bug fix branches.
  └── hotfix/*    ← Urgent production fixes. Merged directly to main.
```

### Workflow

```
1. Create feature branch from main     →  feature/add-service-page
2. Develop and test locally             →  Local dev environment
3. Commit with descriptive message      →  git commit -m "Add plumbing service page with schema"
4. Push and open Pull Request           →  GitHub PR with description
5. Code review (human or AI)            →  Check against this document
6. Merge to main                        →  Squash merge preferred
7. GitHub Actions triggers deployment   →  Automated SSH deployment
8. Post-deployment verification         →  Confirm site is operational
```

### Commit Message Format

```
[type]: Short description (imperative mood, ≤ 72 chars)

Optional body explaining what changed and why.
```

| Type | Usage |
|---|---|
| `feat` | New feature or page |
| `fix` | Bug fix |
| `docs` | Documentation changes only |
| `style` | Formatting, whitespace — no logic changes |
| `refactor` | Code restructuring without changing behavior |
| `perf` | Performance improvement |
| `security` | Security hardening |
| `seo` | SEO-related changes (schema, meta, structure) |
| `chore` | Maintenance (dependency updates, config changes) |

### Release Tagging

Every production deployment should be tagged with a semantic version:

```
git tag -a v0.1.0 -m "Framework foundation and documentation"
git push origin v0.1.0
```

### Rollback Procedure

1. Identify the last known good release tag
2. Revert main to that tag: `git revert` or `git reset` depending on situation
3. Trigger deployment pipeline
4. Verify site is operational
5. Document the incident and root cause

### Files That Must Never Be Deployed

| Path | Reason |
|---|---|
| `.env` | Contains secrets |
| `.git/` | Repository metadata |
| `node_modules/` | Development dependencies |
| `package.json` / `package-lock.json` | Build tooling — not runtime |
| `README.md`, `CONTRIBUTING.md` | Repository documentation, not site files |
| `tests/` | Test files |
| `.github/` | CI/CD configuration |

---

## 10. Quality Assurance Checklist

Every page must pass this checklist before deployment to production.

### Responsive Design

- [ ] Page renders correctly on mobile (375px width)
- [ ] Page renders correctly on tablet (768px width)
- [ ] Page renders correctly on desktop (1440px width)
- [ ] No horizontal scrolling on any viewport
- [ ] Touch targets are minimum 44×44px on mobile
- [ ] Text is readable without zooming on mobile

### Cross-Browser Compatibility

- [ ] Chrome (latest 2 versions)
- [ ] Firefox (latest 2 versions)
- [ ] Safari (latest 2 versions)
- [ ] Edge (latest 2 versions)
- [ ] Safari on iOS (latest 2 versions)
- [ ] Chrome on Android (latest 2 versions)

### Performance

- [ ] LCP ≤ 2.5s on mobile (target ≤ 1.5s)
- [ ] INP ≤ 200ms (target ≤ 100ms)
- [ ] CLS ≤ 0.1 (target ≤ 0.05)
- [ ] Total page weight ≤ 3 MB (target ≤ 1.5 MB)
- [ ] No render-blocking resources in critical path
- [ ] Images optimized and served in modern formats

### Accessibility

- [ ] All images have alt text
- [ ] Heading hierarchy is logical (H1 → H2 → H3)
- [ ] Links have descriptive text (no "click here")
- [ ] Color contrast meets WCAG AA standards (4.5:1 for normal text)
- [ ] Forms have labeled inputs
- [ ] Keyboard navigation works for all interactive elements
- [ ] Skip-to-content link present

### SEO

- [ ] Unique title tag (≤ 60 chars)
- [ ] Unique meta description (≤ 160 chars)
- [ ] Single H1 tag
- [ ] Schema markup present and validated
- [ ] Internal links present (minimum 3)
- [ ] Canonical tag present
- [ ] Open Graph tags present
- [ ] Page is in XML sitemap
- [ ] No orphan pages (every page linked from at least one other page)

### Functional

- [ ] All forms submit correctly and send notifications
- [ ] Thank you / confirmation page or message works
- [ ] 404 page exists and is styled
- [ ] All internal links work — no broken links
- [ ] All external links open in new tab
- [ ] Phone numbers are clickable (`tel:` links)
- [ ] Email addresses are clickable (`mailto:` links)
- [ ] Maps load correctly (if applicable)
- [ ] All CTAs link to correct destinations

### Content and Typography

- [ ] No placeholder or lorem ipsum text
- [ ] No spelling or grammar errors
- [ ] Typography is consistent across pages
- [ ] Spacing is consistent across sections
- [ ] Brand colors are applied consistently
- [ ] Logo displays correctly on all viewports

### Tracking and Monitoring

- [ ] Google Analytics 4 installed and receiving data
- [ ] Google Search Console verified
- [ ] Rank Math SEO audit score ≥ 80
- [ ] WP Rocket caching enabled and tested
- [ ] Sitemap submitted to Search Console

### Security

- [ ] SSL certificate active and valid
- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] `wp-config.php` secured
- [ ] File editor disabled
- [ ] Login attempts limited
- [ ] No default `admin` username
- [ ] Security headers configured

---

## 11. AI Development Rules

### Before Starting Any Task

1. Read `PROJECT_CONTEXT.md` — understand the project architecture and constraints
2. Read this document (`WORDPRESS_STANDARDS.md`) — understand development standards
3. Read `AI_RULES.md` (when available) — understand AI-specific behavioral rules
4. Check `ai/conventions/` — check for task-specific conventions
5. Check `ai/decisions/` — check for prior architectural decisions on the topic

### Mandatory Rules for AI Assistants

| Rule | Detail |
|---|---|
| Never edit WordPress Core | No modifications to any file in `wp-admin/` or `wp-includes/` |
| Never edit Parent Theme | No modifications to `themes/kadence/` |
| Never edit third-party plugins | No modifications to files inside `plugins/{plugin-name}/` |
| Always use child theme | All theme modifications go in `themes/kadence-child/` |
| Prefer reusable code | Extract repeated patterns into functions, template parts, or components |
| Document decisions | Any architectural decision must be recorded in `ai/decisions/` |
| Update documentation | When changing behavior, update the corresponding documentation |
| Follow naming conventions | Use the project prefix and naming patterns defined in this document |
| Test before committing | Verify changes work in a local or staging environment |
| Never hardcode | URLs, paths, credentials, and environment-specific values go in configuration |

### When Creating New Functionality

1. Check if the functionality already exists in an approved plugin
2. Check if a reusable component already exists in `templates/`
3. If building new — determine the correct extension method (child theme vs. custom plugin vs. MU plugin)
4. Follow the file organization patterns defined in this document
5. Add documentation for the new functionality

---

## 12. Common Mistakes

AI assistants and developers must avoid these patterns. Each is a recurring issue that degrades project quality.

### Architecture Mistakes

| Mistake | Correct Approach |
|---|---|
| Installing a plugin for functionality that already exists in an approved plugin | Check the approved plugin list first — Rank Math handles SEO, WP Rocket handles caching |
| Editing the parent theme "because it's faster" | Always use the child theme — no exceptions |
| Adding all custom code to `functions.php` as one large file | Split into organized files in `inc/` and require them |
| Creating a custom plugin for something that belongs in the theme | Theme-dependent presentation logic goes in the child theme; standalone functionality goes in plugins |
| Installing multiple plugins that do the same thing | One tool per job — audit and remove duplicates |

### Performance Mistakes

| Mistake | Correct Approach |
|---|---|
| Uploading full-size images without compression | Compress and resize before upload; serve WebP |
| Loading all scripts on every page | Conditionally enqueue — load scripts only on pages that need them |
| Using `@import` in CSS | Use `wp_enqueue_style` with proper dependencies |
| Adding Google Fonts via `<link>` in header.php | Self-host fonts or enqueue properly with `preload` |
| Ignoring CLS by not setting image dimensions | Always set `width` and `height` attributes on images |
| Not deferring JavaScript | Defer all non-critical JS; async for analytics |

### Security Mistakes

| Mistake | Correct Approach |
|---|---|
| Committing `.env` or `wp-config.php` with credentials | Add to `.gitignore`; use environment variables on the server |
| Using `admin` as the admin username | Use a unique, non-obvious admin username |
| Leaving the file editor enabled | Set `DISALLOW_FILE_EDIT` to `true` |
| Running PHP 7.x in production | Require PHP 8.0+ for security and performance |
| Not limiting login attempts | Install and configure brute-force protection |

### SEO Mistakes

| Mistake | Correct Approach |
|---|---|
| Multiple H1 tags on a page | Exactly one H1 per page |
| Missing or duplicate meta descriptions | Unique meta description for every indexable page |
| No schema markup | Every page type gets appropriate JSON-LD schema |
| Orphan pages with no internal links | Every page must be linked from at least one other page |
| Publishing without checking mobile rendering | Test mobile view before publishing — Google uses mobile-first indexing |
| Blocking CSS/JS in robots.txt | Never block rendering resources — Google needs them to render pages |

### Documentation Mistakes

| Mistake | Correct Approach |
|---|---|
| Making structural changes without updating docs | Documentation is part of the change — not a follow-up task |
| Assuming other AI assistants know what you did | Document it — future AI sessions have no memory of past sessions |
| Writing documentation after the project is complete | Write documentation during development, not after |

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — full WordPress development standards defined |