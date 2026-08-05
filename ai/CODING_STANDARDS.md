# CODING_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — every line of code must conform to this document |
| Companion Documents | `PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `AI_RULES.md` |

> **Every AI assistant and developer must read this document before writing any code.**
> This is not a style preference document. These are enforced standards. Code that violates them will be rejected in review.

---

## 1. Coding Philosophy

Code is read far more often than it is written. Every decision in this document prioritizes long-term readability and maintainability over short-term convenience.

### Governing Principles

| Principle | Meaning |
|---|---|
| Clarity over cleverness | Write code that a junior developer or AI assistant can understand on first read |
| Explicit over implicit | State intent clearly — do not rely on language quirks or hidden behavior |
| Consistency over perfection | A consistent codebase with simple patterns beats an inconsistent one with individually "better" solutions |
| Separation of concerns | Each file, function, and class has exactly one responsibility |
| Fail loudly | Errors must be caught, logged, and handled — never silenced |
| Security by default | Every input is untrusted. Every output is escaped. No exceptions. |
| Performance is a feature | Unnecessary database queries, unoptimized loops, and bloated assets are bugs |

### Decision Hierarchy

When standards conflict or a situation is not covered, apply this hierarchy:

```
1. This document (CODING_STANDARDS.md)
2. WordPress Coding Standards (official handbook)
3. PSR-12 (for modern PHP patterns outside WordPress conventions)
4. PROJECT_CONTEXT.md (architectural constraints)
5. Existing codebase conventions (consistency wins)
```

---

## 2. PHP Standards

### Version Requirement

| Requirement | Value |
|---|---|
| Minimum PHP version | 8.0 |
| Target PHP version | 8.2+ |
| Strict typing | Required in all new files |

Every PHP file must begin with:

```php
<?php

declare(strict_types=1);
```

No closing `?>` tag in files that contain only PHP.

### WordPress Coding Standards

The framework follows WordPress Coding Standards as the baseline, with specific extensions documented below.

#### Indentation and Formatting

| Rule | Standard |
|---|---|
| Indentation | Tabs, not spaces (WordPress standard) |
| Line length | Soft limit 120 characters; hard limit 150 characters |
| Braces | Opening brace on the same line as the statement |
| Else/elseif | `} else {` and `} elseif {` — closing and opening on same line |
| Array syntax | Short array syntax `[]`, not `array()` |
| Trailing commas | Required on multi-line arrays, function parameters (PHP 8.0+), and match arms |
| Blank lines | One blank line between methods; two blank lines between class sections (properties, methods) |
| Spaces inside parentheses | Yes, per WordPress standard: `if ( $condition )` not `if ($condition)` |

#### Example

```php
<?php

declare(strict_types=1);

namespace Waif\Services;

class SchemaGenerator {

	private string $site_name;
	private array $locations;

	public function __construct( string $site_name, array $locations = [] ) {
		$this->site_name = $site_name;
		$this->locations = $locations;
	}

	public function generate_local_business( int $location_id ): array {
		$location = $this->get_location( $location_id );

		if ( ! $location ) {
			throw new \InvalidArgumentException(
				sprintf( 'Location ID %d not found.', $location_id )
			);
		}

		return [
			'@context' => 'https://schema.org',
			'@type'    => 'LocalBusiness',
			'name'     => $this->site_name,
			'address'  => $this->format_address( $location ),
		];
	}

	private function get_location( int $id ): ?array {
		return $this->locations[ $id ] ?? null;
	}

	private function format_address( array $location ): array {
		return [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $location['street'] ?? '',
			'addressLocality' => $location['city'] ?? '',
			'addressRegion'   => $location['state'] ?? '',
			'postalCode'      => $location['zip'] ?? '',
		];
	}
}
```

### PSR-12 Extensions

Where WordPress Coding Standards do not cover a pattern (namespaces, interfaces, traits, modern OOP), PSR-12 applies:

| Pattern | Standard |
|---|---|
| Namespace declaration | One blank line after `declare(strict_types=1)`, one blank line after `namespace` |
| Use statements | Grouped by type: classes, functions, constants — one blank line between groups |
| Class structure | Constants → Properties → Constructor → Public methods → Protected methods → Private methods |
| Return types | Required on all methods and functions |
| Type declarations | Required on all parameters (use union types where needed) |
| Null handling | Use nullable types `?string` instead of default `null` where intent is "may not exist" |

### OOP Guidelines

#### When to Use Classes

| Situation | Approach |
|---|---|
| Stateful logic with multiple related methods | Class |
| Simple utility with no state | Static class or namespaced functions |
| WordPress hooks registration for a feature | Class with `register()` or `boot()` method |
| Data transfer between layers | Typed array or DTO class |

#### SOLID Principles

| Principle | Rule | Example |
|---|---|---|
| **S** — Single Responsibility | One class does one thing | `SchemaGenerator` generates schema. It does not also register shortcodes. |
| **O** — Open/Closed | Extend behavior without modifying existing code | Add new schema types by creating new classes, not by adding branches to existing ones |
| **L** — Liskov Substitution | Subtypes must be substitutable for their parent | If `LocalBusinessSchema` extends `BaseSchema`, it must honor the same interface |
| **I** — Interface Segregation | Clients should not depend on methods they do not use | Split fat interfaces into focused ones: `Renderable`, `Cacheable`, `Validatable` |
| **D** — Dependency Inversion | Depend on abstractions, not concretions | Pass interfaces to constructors, not concrete class instances |

#### Interfaces

Define interfaces for any service that may have multiple implementations or that crosses architectural boundaries:

```php
<?php

declare(strict_types=1);

namespace Waif\Contracts;

interface SchemaProvider {

	public function get_type(): string;

	public function generate( int $post_id ): array;

	public function validate( array $schema ): bool;
}
```

| Rule | Detail |
|---|---|
| Naming | Descriptive nouns or adjectives: `Renderable`, `SchemaProvider`, `CacheDriver` |
| Location | `contracts/` or `interfaces/` directory |
| Size | Prefer small, focused interfaces (2-5 methods) |

#### Traits

Use traits only for horizontal code reuse where inheritance is inappropriate:

```php
<?php

declare(strict_types=1);

namespace Waif\Traits;

trait HasMetaFields {

	public function get_meta( int $post_id, string $key, mixed $default = null ): mixed {
		$value = get_post_meta( $post_id, $key, true );
		return $value !== '' ? $value : $default;
	}

	public function update_meta( int $post_id, string $key, mixed $value ): bool {
		return (bool) update_post_meta( $post_id, $key, $value );
	}
}
```

| Rule | Detail |
|---|---|
| Scope | Traits provide reusable method implementations, not state |
| Naming | Prefix with `Has` or `Can`: `HasMetaFields`, `CanBeCached` |
| Limit | Maximum 2 traits per class — if more are needed, reconsider the architecture |

#### Namespaces

```
Waif\                          Root namespace
Waif\Services\                 Business logic services
Waif\Contracts\                Interfaces and abstract contracts
Waif\Traits\                   Shared traits
Waif\Models\                   Data models and DTOs
Waif\Admin\                    Admin-facing functionality
Waif\Frontend\                 Frontend-facing functionality
Waif\Api\                      REST API endpoints
Waif\Schema\                   JSON-LD schema generators
```

Projects override the root: `ClientProject\Services\`, `ClientProject\Schema\`, etc.

#### Dependency Injection

Construct dependencies through the constructor, not by instantiating inside methods:

```php
// Correct — dependency injected
public function __construct( SchemaProvider $schema, CacheDriver $cache ) {
	$this->schema = $schema;
	$this->cache  = $cache;
}

// Wrong — dependency hidden inside the class
public function generate(): array {
	$schema = new LocalBusinessSchema(); // Hard coupling
	return $schema->generate( $this->post_id );
}
```

For WordPress integration where full DI containers are impractical, use a simple service locator or factory pattern registered via `mu-plugins`.

---

## 3. Functions, Constants, and Variables

### Functions

| Rule | Standard |
|---|---|
| Naming | `snake_case` with project prefix: `waif_get_service_areas()` |
| Length | Maximum 30 lines of logic — refactor if longer |
| Parameters | Maximum 4 parameters — use an options array or DTO for more |
| Return type | Always declared |
| Single responsibility | One function does one thing |
| Side effects | Clearly documented — functions that modify state must say so |
| Pure functions | Preferred where possible — same input always produces same output |

```php
function waif_format_phone( string $raw_phone ): string {
	$digits = preg_replace( '/\D/', '', $raw_phone );

	if ( strlen( $digits ) !== 10 ) {
		return $raw_phone;
	}

	return sprintf( '(%s) %s-%s',
		substr( $digits, 0, 3 ),
		substr( $digits, 3, 3 ),
		substr( $digits, 6, 4 ),
	);
}
```

### Constants

| Rule | Standard |
|---|---|
| Naming | `UPPER_SNAKE_CASE` with prefix: `WAIF_VERSION`, `WAIF_MIN_PHP` |
| Definition | Use `const` in classes; `define()` at the application level |
| Magic numbers | Never — extract to named constants |

```php
// Wrong
if ( $count > 5 ) { ... }

// Correct
const WAIF_MAX_REVISIONS = 5;

if ( $count > WAIF_MAX_REVISIONS ) { ... }
```

### Variables

| Rule | Standard |
|---|---|
| Naming | `$snake_case` — descriptive, not abbreviated |
| Booleans | Prefix with `is_`, `has_`, `can_`, `should_`: `$is_published`, `$has_schema` |
| Counters/iterators | `$i`, `$j` only in simple loops; descriptive names for complex logic |
| Globals | Never create global variables — use WordPress options, transients, or class properties |
| Type declarations | Use typed properties in PHP 8.0+: `private string $title;` |

---

## 4. File and Folder Naming

### File Naming

| Type | Convention | Example |
|---|---|---|
| PHP class file | `class-{name}.php` (WordPress) or `{ClassName}.php` (PSR-4) | `class-schema-generator.php` or `SchemaGenerator.php` |
| PHP function file | `{descriptive-name}.php` | `helpers.php`, `enqueue.php` |
| Template file | `{template-name}.php` | `single-service.php` |
| Template part | `{part-name}.php` inside `template-parts/` | `template-parts/hero-section.php` |
| CSS file | `{descriptive-name}.css` | `hero-section.css`, `global.css` |
| JavaScript file | `{descriptive-name}.js` | `mobile-menu.js`, `schema-validator.js` |

### Folder Naming

| Convention | Example |
|---|---|
| All lowercase | `services/`, `contracts/` |
| Hyphen-separated for multi-word | `template-parts/`, `mu-plugins/` |
| Singular for namespaced class directories | `model/`, `service/`, `contract/` |
| Plural for collections of items | `templates/`, `scripts/`, `assets/` |

---

## 5. HTML Standards

### Semantic HTML

Use HTML elements for their semantic meaning, not their default appearance:

| Element | Correct Usage | Wrong Usage |
|---|---|---|
| `<header>` | Page or section header | Generic container |
| `<nav>` | Navigation menus | Any list of links |
| `<main>` | Primary page content (one per page) | Wrapper div |
| `<article>` | Self-contained content (blog post, card) | Generic section |
| `<section>` | Thematic grouping with a heading | Div replacement |
| `<aside>` | Tangentially related content (sidebar) | Layout column |
| `<footer>` | Page or section footer | Bottom div |
| `<figure>` / `<figcaption>` | Image with caption | Div + p |
| `<button>` | Interactive control | Clickable div or span |
| `<a>` | Navigation to a URL | JavaScript trigger (use button) |

### Structure Rules

- Every page has exactly one `<main>` element
- Every page has exactly one `<h1>`
- Headings follow logical order: H1 → H2 → H3 (never skip levels)
- Lists use `<ul>`, `<ol>`, or `<dl>` — never styled divs
- Tables are used for tabular data only, never for layout
- Forms use `<form>` with `<label>` elements linked to inputs via `for`/`id`

### Accessibility (a11y)

| Requirement | Standard |
|---|---|
| Alt text | Every `<img>` has a descriptive `alt` attribute; decorative images use `alt=""` |
| ARIA labels | Interactive elements without visible text must have `aria-label` |
| Focus management | All interactive elements must be keyboard-accessible and show visible focus styles |
| Color contrast | WCAG AA minimum: 4.5:1 for normal text, 3:1 for large text |
| Skip link | "Skip to content" link as the first focusable element on every page |
| Language | `<html lang="en">` attribute set on every page |
| Touch targets | Minimum 44×44px for all tappable elements on mobile |
| Reduced motion | Respect `prefers-reduced-motion` — disable animations when set |
| Form errors | Error messages are associated with inputs via `aria-describedby` |

```html
<!-- Correct: accessible form field -->
<label for="phone">Phone Number</label>
<input
  type="tel"
  id="phone"
  name="phone"
  required
  aria-describedby="phone-error"
  pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
>
<span id="phone-error" class="waif-field-error" role="alert"></span>
```

---

## 6. CSS Standards

### Architecture

```
assets/css/
├── global.css              # Reset, base typography, CSS variables
├── layout.css              # Grid system, containers, spacing
├── components/
│   ├── hero.css            # Hero section styles
│   ├── cta-band.css        # Call-to-action band
│   ├── faq.css             # FAQ accordion
│   ├── testimonials.css    # Testimonial slider/grid
│   └── footer.css          # Footer styles
└── utilities.css           # Helper classes (visually-hidden, clearfix)
```

### BEM Methodology

All custom CSS classes follow BEM (Block Element Modifier):

```
.block {}
.block__element {}
.block--modifier {}
```

| Component | Convention | Example |
|---|---|---|
| Block | The standalone component | `.waif-hero` |
| Element | A part of the block | `.waif-hero__title`, `.waif-hero__cta` |
| Modifier | A variation of block or element | `.waif-hero--dark`, `.waif-hero__cta--primary` |

```css
/* Block */
.waif-hero {
	position: relative;
	padding: var(--waif-spacing-xl) 0;
}

/* Element */
.waif-hero__title {
	font-size: var(--waif-font-size-h1);
	font-weight: 700;
	color: var(--waif-color-text-primary);
}

/* Modifier */
.waif-hero--dark {
	background-color: var(--waif-color-bg-dark);
	color: var(--waif-color-text-inverse);
}
```

### CSS Variables

All design tokens are defined as CSS custom properties on `:root`:

```css
:root {
	/* Colors */
	--waif-color-primary: #1a56db;
	--waif-color-secondary: #057a55;
	--waif-color-accent: #e3a008;
	--waif-color-text-primary: #111827;
	--waif-color-text-secondary: #6b7280;
	--waif-color-text-inverse: #ffffff;
	--waif-color-bg-light: #f9fafb;
	--waif-color-bg-dark: #1f2937;
	--waif-color-border: #e5e7eb;

	/* Typography */
	--waif-font-family-body: 'Inter', system-ui, sans-serif;
	--waif-font-family-heading: 'Inter', system-ui, sans-serif;
	--waif-font-size-base: 1rem;
	--waif-font-size-h1: clamp(2rem, 4vw, 3rem);
	--waif-font-size-h2: clamp(1.5rem, 3vw, 2.25rem);
	--waif-font-size-h3: clamp(1.25rem, 2.5vw, 1.75rem);
	--waif-line-height-body: 1.7;
	--waif-line-height-heading: 1.2;

	/* Spacing */
	--waif-spacing-xs: 0.25rem;
	--waif-spacing-sm: 0.5rem;
	--waif-spacing-md: 1rem;
	--waif-spacing-lg: 2rem;
	--waif-spacing-xl: 4rem;
	--waif-spacing-2xl: 6rem;

	/* Layout */
	--waif-container-max: 1200px;
	--waif-container-padding: 1.5rem;

	/* Borders */
	--waif-border-radius: 0.375rem;
	--waif-border-radius-lg: 0.75rem;

	/* Transitions */
	--waif-transition-fast: 150ms ease;
	--waif-transition-base: 250ms ease;
}
```

Every project overrides these values — never hardcode colors, font sizes, or spacing values in component CSS.

### Responsive Design

| Breakpoint | Width | Target |
|---|---|---|
| Mobile (default) | 0 – 767px | All base styles written mobile-first |
| Tablet | 768px+ | `@media (min-width: 768px)` |
| Desktop | 1024px+ | `@media (min-width: 1024px)` |
| Wide | 1440px+ | `@media (min-width: 1440px)` |

Mobile-first is mandatory. Base styles are mobile. Media queries add complexity upward:

```css
.waif-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: var(--waif-spacing-md);
}

@media (min-width: 768px) {
	.waif-grid {
		grid-template-columns: repeat(2, 1fr);
		gap: var(--waif-spacing-lg);
	}
}

@media (min-width: 1024px) {
	.waif-grid {
		grid-template-columns: repeat(3, 1fr);
	}
}
```

### CSS Rules

| Rule | Standard |
|---|---|
| No `!important` | Use proper specificity instead; `!important` only for utility overrides as a last resort |
| No ID selectors | IDs are for JavaScript hooks and anchor links, not styling |
| No inline styles | All styles in external files, enqueued properly |
| Shorthand usage | Use shorthand for `margin`, `padding`, `border` when setting all sides |
| Units | `rem` for typography and spacing; `px` for borders and shadows; `%` or `vw` for fluid widths |
| Z-index scale | Define named layers: `--z-dropdown: 100`, `--z-modal: 200`, `--z-overlay: 300` |

---

## 7. JavaScript Standards

### Version and Syntax

| Requirement | Standard |
|---|---|
| Minimum syntax | ES6+ (ES2015+) |
| Module format | ES modules (`import`/`export`) for bundled projects; IIFE for WordPress enqueued scripts |
| No jQuery | Vanilla JavaScript only in new code. jQuery permitted only when interacting with legacy WordPress admin APIs. |
| Strict mode | `'use strict';` in every script (automatic in ES modules) |

### Structure

```javascript
'use strict';

/**
 * Mobile menu toggle for the primary navigation.
 *
 * @file mobile-menu.js
 * @since 0.1.0
 */
( function() {
	const toggleBtn = document.querySelector( '.waif-menu-toggle' );
	const navMenu   = document.querySelector( '.waif-nav-primary' );

	if ( ! toggleBtn || ! navMenu ) {
		return;
	}

	const handleToggle = () => {
		const isExpanded = toggleBtn.getAttribute( 'aria-expanded' ) === 'true';
		toggleBtn.setAttribute( 'aria-expanded', String( ! isExpanded ) );
		navMenu.classList.toggle( 'is-open' );
	};

	toggleBtn.addEventListener( 'click', handleToggle );
} )();
```

### Async/Await and Fetch API

All asynchronous operations use `async`/`await` with proper error handling:

```javascript
async function fetchServiceAreas( zipCode ) {
	try {
		const response = await fetch( `/wp-json/waif/v1/service-areas?zip=${ encodeURIComponent( zipCode ) }`, {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': waifData.nonce,
			},
		} );

		if ( ! response.ok ) {
			throw new Error( `HTTP ${ response.status }: ${ response.statusText }` );
		}

		return await response.json();

	} catch ( error ) {
		console.error( '[WAIF] Service area fetch failed:', error.message );
		return null;
	}
}
```

| Rule | Standard |
|---|---|
| Never use raw `.then()` chains | Use `async`/`await` for readability |
| Always handle errors | Every `await` is inside `try`/`catch` or the function returns a handled result |
| Always validate responses | Check `response.ok` before parsing the body |
| Always include nonces | Every authenticated API call must send `X-WP-Nonce` |
| URL encoding | Always encode user input in URLs: `encodeURIComponent()` |

### JavaScript Rules

| Rule | Standard |
|---|---|
| Variable declarations | `const` by default; `let` when reassignment is needed; never `var` |
| String interpolation | Template literals: `` `Hello ${ name }` `` — not concatenation |
| Equality | Always `===` and `!==` — never `==` or `!=` |
| DOM queries | Cache DOM references — never query the same element twice |
| Event delegation | Prefer event delegation on parent elements over individual listeners |
| Enqueuing | Use `wp_enqueue_script()` with `defer` strategy; never inline `<script>` in templates |

---

## 8. Security Standards

### Input Flow Diagram

Every piece of external data follows this path:

```
User Input
    │
    ▼
┌──────────────┐
│  Validation   │  Does this data meet the expected format and constraints?
│  (reject bad) │  wp_verify_nonce(), is_numeric(), in_array(), regex
└──────┬───────┘
       │ Valid
       ▼
┌──────────────┐
│ Sanitization  │  Clean the data — remove anything dangerous
│ (clean input) │  sanitize_text_field(), sanitize_email(), absint()
└──────┬───────┘
       │ Clean
       ▼
┌──────────────┐
│  Processing   │  Business logic, database operations
│  (use data)   │  $wpdb->prepare(), update_post_meta()
└──────┬───────┘
       │ Result
       ▼
┌──────────────┐
│   Escaping    │  Prepare data for safe output in a specific context
│ (safe output) │  esc_html(), esc_attr(), esc_url(), wp_kses()
└──────┴───────┘
       │
       ▼
    Rendered Output
```

### Validation

Validate before processing. Reject anything that does not match expectations:

```php
// Validate nonce
if ( ! wp_verify_nonce( $_POST['waif_nonce'] ?? '', 'waif_save_settings' ) ) {
	wp_die( 'Security check failed.', 403 );
}

// Validate capability
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Insufficient permissions.', 403 );
}

// Validate data type
$post_id = absint( $_POST['post_id'] ?? 0 );
if ( $post_id === 0 ) {
	wp_send_json_error( [ 'message' => 'Invalid post ID.' ], 400 );
}
```

### Sanitization

Clean input data before storing or processing:

| Function | Use Case |
|---|---|
| `sanitize_text_field()` | Single-line text input |
| `sanitize_textarea_field()` | Multi-line text input |
| `sanitize_email()` | Email addresses |
| `sanitize_url()` | URLs |
| `sanitize_title()` | Slugs and identifiers |
| `sanitize_file_name()` | Uploaded file names |
| `absint()` | Positive integers |
| `intval()` | Integers (including negative) |
| `wp_kses_post()` | Rich text / HTML with allowed post tags |
| `wp_kses()` | HTML with custom allowed tags and attributes |

### Escaping

Escape all output immediately before rendering — never earlier:

| Function | Context | Example |
|---|---|---|
| `esc_html()` | Inside HTML elements | `<p><?php echo esc_html( $text ); ?></p>` |
| `esc_attr()` | Inside HTML attributes | `<input value="<?php echo esc_attr( $val ); ?>">` |
| `esc_url()` | Inside `href`, `src`, `action` | `<a href="<?php echo esc_url( $link ); ?>">` |
| `esc_js()` | Inside inline JavaScript | Avoid — enqueue scripts instead |
| `wp_kses()` | HTML output with allowed tags | `echo wp_kses( $html, $allowed_tags );` |
| `wp_kses_post()` | HTML output with standard post tags | `echo wp_kses_post( $content );` |

**Rule: Late escaping.** Escape at the point of output, not at the point of input or storage. Data is stored clean (sanitized). Data is displayed safe (escaped).

### Nonces

Every form submission and AJAX request must include a nonce:

```php
// Generate nonce field in form
wp_nonce_field( 'waif_save_settings', 'waif_nonce' );

// Verify nonce on submission
if ( ! wp_verify_nonce( $_POST['waif_nonce'] ?? '', 'waif_save_settings' ) ) {
	wp_die( 'Invalid security token.' );
}

// For AJAX — localize nonce
wp_localize_script( 'waif-app', 'waifData', [
	'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	'nonce'   => wp_create_nonce( 'waif_ajax_action' ),
] );
```

### Prepared SQL Queries

Never concatenate user input into SQL. Always use `$wpdb->prepare()`:

```php
// Correct
$results = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d",
		'waif_location_id',
		$location_id,
	)
);

// Wrong — SQL injection vulnerability
$results = $wpdb->get_results(
	"SELECT * FROM {$wpdb->postmeta} WHERE meta_key = 'waif_location_id' AND meta_value = {$location_id}"
);
```

| Placeholder | Type |
|---|---|
| `%s` | String |
| `%d` | Integer |
| `%f` | Float |
| `%i` | Identifier (table/column name — WordPress 6.2+) |

---

## 9. Error Handling and Logging

### Error Handling

| Rule | Standard |
|---|---|
| Never suppress errors | No `@` operator, no empty `catch` blocks |
| Catch specific exceptions | Catch the narrowest exception type possible |
| User-facing errors | Human-readable, non-technical messages |
| Developer-facing errors | Detailed, contextual log entries |
| Fatal vs. recoverable | Fail fast on unrecoverable errors; degrade gracefully on recoverable ones |

```php
try {
	$schema = $generator->generate_local_business( $post_id );
} catch ( \InvalidArgumentException $e ) {
	waif_log( 'error', 'Schema generation failed', [
		'post_id' => $post_id,
		'message' => $e->getMessage(),
	] );
	return []; // Degrade gracefully — page renders without schema
}
```

### Logging

All logging uses a centralized function with structured data:

```php
function waif_log( string $level, string $message, array $context = [] ): void {
	if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
		return;
	}

	$entry = sprintf(
		'[WAIF][%s][%s] %s | %s',
		strtoupper( $level ),
		current_time( 'Y-m-d H:i:s' ),
		$message,
		wp_json_encode( $context ),
	);

	error_log( $entry );
}
```

| Level | Usage |
|---|---|
| `error` | Something failed — requires investigation |
| `warning` | Something unexpected happened but execution continued |
| `info` | Significant events (deployment, cache clear, settings change) |
| `debug` | Detailed diagnostic information — development only |

---

## 10. REST API Standards

### Namespace

All custom REST endpoints use the framework namespace:

```
/wp-json/waif/v1/{resource}
```

### Route Registration

```php
add_action( 'rest_api_init', function() {
	register_rest_route( 'waif/v1', '/service-areas', [
		'methods'             => \WP_REST_Server::READABLE,
		'callback'            => 'waif_get_service_areas',
		'permission_callback' => '__return_true', // Public endpoint
		'args'                => [
			'zip' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param ) {
					return preg_match( '/^\d{5}$/', $param );
				},
			],
		],
	] );
} );
```

### API Design Rules

| Rule | Standard |
|---|---|
| Resource naming | Plural nouns: `/service-areas`, `/locations`, `/reviews` |
| HTTP methods | `GET` = read, `POST` = create, `PUT` = full update, `PATCH` = partial update, `DELETE` = remove |
| Authentication | Public endpoints use `__return_true`; admin endpoints check `current_user_can()` |
| Validation | Use `validate_callback` on every parameter |
| Sanitization | Use `sanitize_callback` on every parameter |
| Response format | Always return `WP_REST_Response` or `wp_send_json_success()`/`wp_send_json_error()` |
| Error responses | Include `code`, `message`, and `data` fields; use appropriate HTTP status codes |
| Versioning | Include version in the namespace: `waif/v1`, `waif/v2` |

---

## 11. Naming Conventions Summary

| Element | Convention | Example |
|---|---|---|
| PHP functions | `snake_case` with prefix | `waif_get_locations()` |
| PHP classes | `PascalCase` | `SchemaGenerator` |
| PHP methods | `snake_case` | `$this->get_location()` |
| PHP constants | `UPPER_SNAKE_CASE` | `WAIF_VERSION` |
| PHP variables | `$snake_case` | `$post_id`, `$is_active` |
| PHP interfaces | `PascalCase` (descriptive noun) | `SchemaProvider` |
| PHP traits | `PascalCase` with `Has`/`Can` prefix | `HasMetaFields` |
| PHP namespaces | `PascalCase` | `Waif\Services` |
| CSS classes | BEM with `waif-` prefix | `.waif-hero__title--dark` |
| CSS variables | `--waif-{category}-{name}` | `--waif-color-primary` |
| JS functions | `camelCase` | `initMobileMenu()` |
| JS constants | `UPPER_SNAKE_CASE` | `MAX_RETRIES` |
| JS variables | `camelCase` | `const menuToggle` |
| Files (PHP) | `lowercase-hyphenated.php` | `schema-generator.php` |
| Files (CSS/JS) | `lowercase-hyphenated` | `hero-section.css` |
| Folders | `lowercase-hyphenated` | `template-parts/` |
| Git branches | `type/descriptive-name` | `feature/add-faq-schema` |
| Commits | `type: description` | `feat: add FAQ schema generator` |
| Database options | `waif_{option_name}` | `waif_schema_settings` |
| REST endpoints | `/waif/v1/{plural-resource}` | `/waif/v1/locations` |

---

## 12. Git Commit Standards

### Message Format

```
type: short description in imperative mood (≤ 72 chars)

Optional body explaining what changed and why.
Reference issues or decisions where relevant.
```

### Types

| Type | Purpose |
|---|---|
| `feat` | New feature, page, or component |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Code formatting — no logic change |
| `refactor` | Code restructuring — no behavior change |
| `perf` | Performance improvement |
| `security` | Security hardening |
| `seo` | SEO changes (schema, meta, structure) |
| `test` | Adding or updating tests |
| `chore` | Build tools, dependencies, config |

### Rules

- Use imperative mood: "Add FAQ section" not "Added FAQ section"
- First line ≤ 72 characters
- Capitalize the first word after the type
- No period at the end of the subject line
- Blank line between subject and body
- Body wraps at 72 characters
- Reference task or decision IDs when applicable

---

## 13. Pull Request Standards

### PR Title

Same format as commit messages: `type: Short description`

### PR Description Template

```markdown
## What changed
Brief explanation of the change.

## Why
The problem this solves or the requirement it fulfills.

## How to test
Steps to verify the change works correctly.

## Checklist
- [ ] Code follows CODING_STANDARDS.md
- [ ] No WordPress Core or parent theme files modified
- [ ] Security: input validated, sanitized; output escaped
- [ ] Performance: no unnecessary queries or asset loads
- [ ] Documentation updated (if applicable)
- [ ] Tested on mobile viewport
```

---

## 14. Code Review Checklist

Reviewers (human or AI) evaluate every PR against these criteria:

| Category | Check |
|---|---|
| **Architecture** | Does this code belong where it was placed? (child theme vs. plugin vs. MU plugin) |
| **Architecture** | Does it follow single responsibility? |
| **Architecture** | Does it duplicate existing functionality? |
| **Security** | Are all inputs validated and sanitized? |
| **Security** | Are all outputs escaped with the correct function for the context? |
| **Security** | Are nonces present on forms and AJAX calls? |
| **Security** | Are database queries using `$wpdb->prepare()`? |
| **Security** | Are capabilities checked before privileged actions? |
| **Performance** | Are database queries necessary, or can cached data be used? |
| **Performance** | Are scripts/styles loaded conditionally (only on pages that need them)? |
| **Performance** | Are images optimized and properly sized? |
| **Naming** | Do functions, variables, and classes follow the naming conventions? |
| **Naming** | Is the project prefix used consistently? |
| **Quality** | No `var_dump()`, `print_r()`, `console.log()` in production code |
| **Quality** | No commented-out code blocks |
| **Quality** | No hardcoded URLs, paths, or credentials |
| **Quality** | No `!important` in CSS without documented justification |
| **Documentation** | Are complex functions documented with PHPDoc or JSDoc? |
| **Documentation** | Are architectural decisions recorded if this introduces a new pattern? |

---

## 15. AI Coding Rules

### Before Writing Code

1. Read `PROJECT_CONTEXT.md` for architectural constraints
2. Read this document for coding standards
3. Check `ai/conventions/` for task-specific rules
4. Check `ai/decisions/` for prior decisions on the topic
5. Check existing codebase for established patterns

### Mandatory Rules

| Rule | Detail |
|---|---|
| Follow existing patterns | If the codebase already has a pattern for the task, use it — do not introduce a new one |
| Prefix everything | All custom functions, classes, CSS classes, and options use the project prefix |
| Never modify core or vendor files | WordPress core, parent theme, and third-party plugins are read-only |
| Escape all output | Every `echo` uses the appropriate escaping function |
| Sanitize all input | Every `$_GET`, `$_POST`, `$_REQUEST` value is sanitized before use |
| Type everything | Return types, parameter types, and property types are declared |
| No magic numbers | Extract numeric values to named constants |
| No dead code | Do not leave commented-out code, unused functions, or abandoned experiments |
| Document decisions | If introducing a new pattern or making an architectural choice, record it |

### Code Generation Standards

When AI generates code, it must:

- Include a file-level docblock with `@since`, `@package`, and purpose
- Include PHPDoc on every public function and method
- Include JSDoc on every exported JavaScript function
- Generate code that passes the Code Review Checklist without modification
- Never generate placeholder code (`// TODO: implement later`) without flagging it

---

## 16. Common Coding Mistakes

| Mistake | Why It Happens | Correct Approach |
|---|---|---|
| Echoing unescaped output | Assuming data is safe because it came from the database | Always escape at point of output — database data can be compromised |
| Using `==` instead of `===` | PHP type juggling is overlooked | Always use strict comparison |
| Hardcoding URLs | Faster during development | Use `home_url()`, `get_template_directory_uri()`, `plugin_dir_url()` |
| Writing raw SQL without `prepare()` | Seems simpler for quick queries | Always use `$wpdb->prepare()` with placeholders |
| Putting all code in `functions.php` | Path of least resistance | Split into organized files in `inc/` |
| Using `!important` in CSS | Specificity battle with theme/plugin styles | Increase specificity properly or use a more targeted selector |
| Loading scripts globally | `wp_enqueue_script` in every page load | Use `is_page()`, `is_singular()`, or conditional loading |
| Not verifying nonces | Forms "seem to work" without them | Every form and AJAX request must verify a nonce |
| Suppressing errors with `@` | Hiding a problem feels like solving it | Fix the root cause; log the error |
| Using `var` in JavaScript | Habit from older codebases | `const` by default, `let` when needed |
| Mixing concerns in templates | Writing database queries inside template files | Templates receive data; logic lives in `inc/` |
| Not setting image dimensions | Perceived as unnecessary | Always set `width`/`height` to prevent CLS |
| Creating global variables | Quick data sharing between files | Use WordPress options, transients, or dependency injection |
| Skipping permission checks | Admin-only features "are already behind login" | Always verify `current_user_can()` before privileged operations |

---

## 17. Coding Checklist

Before committing any code, verify every applicable item:

### PHP

- [ ] `declare(strict_types=1)` present
- [ ] No closing `?>` tag
- [ ] All functions have return type declarations
- [ ] All parameters have type declarations
- [ ] All inputs validated and sanitized
- [ ] All outputs escaped with context-appropriate function
- [ ] Nonces present on forms and AJAX
- [ ] `$wpdb->prepare()` used for all custom queries
- [ ] `current_user_can()` checked before privileged actions
- [ ] No hardcoded URLs or paths
- [ ] No magic numbers — constants used
- [ ] No `var_dump`, `print_r`, or `error_log` in production code
- [ ] Functions follow naming conventions with project prefix
- [ ] PHPDoc present on public methods

### HTML

- [ ] Semantic elements used correctly
- [ ] Single `<h1>` per page
- [ ] Heading hierarchy is logical
- [ ] All images have `alt` text
- [ ] All form inputs have `<label>` elements
- [ ] All interactive elements are keyboard-accessible
- [ ] `lang` attribute set on `<html>`

### CSS

- [ ] BEM naming with project prefix
- [ ] CSS variables used for all design tokens
- [ ] Mobile-first responsive approach
- [ ] No `!important` without documented justification
- [ ] No ID selectors for styling
- [ ] No inline styles

### JavaScript

- [ ] `const`/`let` only — no `var`
- [ ] Strict equality `===` only
- [ ] `async`/`await` with `try`/`catch`
- [ ] DOM references cached
- [ ] No `console.log` in production code
- [ ] Nonce included in API calls

### Git

- [ ] Commit message follows format: `type: description`
- [ ] No `node_modules`, `.env`, or secrets committed
- [ ] No commented-out code blocks
- [ ] No unrelated changes in the same commit

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — complete coding standards defined |