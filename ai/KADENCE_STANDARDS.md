# KADENCE_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — Kadence is the primary theme and layout system for every project |
| Companion Documents | `PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `PLUGIN_STANDARDS.md`, `CODING_STANDARDS.md` |

> **Kadence is the default.**
> Every layout, every header, every footer, every page template starts with Kadence. Elementor is the exception — used only when Kadence cannot deliver a specific design requirement. This document defines how Kadence is configured, extended, and maintained across every project.

---

## 1. Kadence Philosophy

Kadence Theme and Kadence Blocks Pro together form the primary build system for this framework. They are selected for specific technical reasons, not brand preference.

### Why Kadence

| Reason | Detail |
|---|---|
| Performance | Kadence generates lightweight, standards-compliant HTML with minimal render-blocking assets |
| Block-native | Built for the WordPress Block Editor — no proprietary page builder dependency |
| Header/Footer builder | Visual header and footer construction without plugins |
| Global design system | Centralized color palette and typography with CSS variable output |
| Kadence Elements | Template injection system using hooks — eliminates the need for template file overrides |
| Theme customizer + block editor | Design control through both the Customizer and the block editor |
| Developer-friendly | Clean hook system, filterable output, child theme compatible |

### Decision Hierarchy

When building any layout, follow this order:

```
1. Can Kadence Theme settings handle it?
   └── Yes → Use Theme Customizer (header builder, footer builder, global settings)

2. Can Kadence Blocks handle it?
   └── Yes → Use Kadence Blocks in the block editor

3. Can Kadence Elements handle it?
   └── Yes → Use Kadence Elements (hook-based template injection)

4. Can Child Theme code handle it?
   └── Yes → Use child theme template overrides, hooks, or custom CSS

5. None of the above?
   └── Only then → Use Elementor for that specific page
```

Elementor enters the workflow at step 5 — never earlier.

---

## 2. Theme Settings

### General Settings

| Setting | Standard |
|---|---|
| Site identity | Logo uploaded as SVG or optimized WebP; site title and tagline configured |
| Logo dimensions | Define exact `width` and `height` in the Customizer to prevent CLS |
| Favicon | 512×512px PNG uploaded as site icon |
| Container width | 1200px default; override per-project as needed |
| Content width | 1140px (within container padding) |
| Sidebar | Disabled globally by default; enabled per-page only when required |
| Scroll to top | Disabled by default — enable only if client requires it |
| Comments | Disabled on pages; enabled on posts only if the project uses blog comments |
| Page layout | Full width — no sidebar — for service pages, location pages, and landing pages |
| Post layout | Content + sidebar if blog is active; full width if no blog |

### Colors — Global Palette

Kadence uses a global color palette that outputs CSS custom properties. Every project defines its palette in the Customizer, and all design references these palette colors — never hardcoded hex values.

| Palette Slot | Purpose | CSS Variable |
|---|---|---|
| Palette Color 1 | Primary brand color | `--global-palette1` |
| Palette Color 2 | Secondary brand color / hover state | `--global-palette2` |
| Palette Color 3 | Accent / CTA color | `--global-palette3` |
| Palette Color 4 | Accent hover / secondary action | `--global-palette4` |
| Palette Color 5 | Highlight / subtle accent | `--global-palette5` |
| Palette Color 6 | Body text (darkest gray or near-black) | `--global-palette6` |
| Palette Color 7 | Light background / card backgrounds | `--global-palette7` |
| Palette Color 8 | Secondary text / muted text | `--global-palette8` |
| Palette Color 9 | Lightest background / page background | `--global-palette9` |

### Color Rules

| Rule | Detail |
|---|---|
| Never hardcode hex values in blocks or custom CSS | Always reference `var(--global-palette1)` through `var(--global-palette9)` |
| Define the full palette before building any page | Changing colors mid-project cascades everywhere — set them first |
| Test contrast ratios | Verify WCAG AA compliance (4.5:1 for text) between all text/background combinations |
| CTA buttons | Use Palette 3 (accent) as default button background; Palette 4 as hover |
| Document the palette | Export palette values to `templates/config/kadence-theme-settings.json` |

### Typography — Global Settings

| Setting | Standard |
|---|---|
| Base font size | 17px (1.0625rem) — adjustable per project |
| Body font family | System stack or one loaded web font (max weight: 400, 500, 700) |
| Heading font family | Same as body or one additional font family — never more than 2 families total |
| Line height (body) | 1.7 |
| Line height (headings) | 1.2 — 1.3 |
| Font loading | `font-display: swap` enforced |
| Heading scale | Configure H1 through H6 sizes in the Customizer with responsive values |
| Paragraph spacing | Defined globally — consistent across all pages |

### Typography Scale

| Heading | Desktop | Tablet | Mobile |
|---|---|---|---|
| H1 | 38–48px | 32–38px | 28–32px |
| H2 | 30–36px | 26–30px | 24–28px |
| H3 | 24–28px | 22–26px | 20–24px |
| H4 | 20–24px | 18–22px | 18–20px |
| H5 | 18–20px | 16–18px | 16–18px |
| H6 | 16–18px | 16px | 16px |

These are configured in the Kadence Customizer under Typography → Headings, with responsive controls per level.

### Button Global Styles

| Setting | Standard |
|---|---|
| Background | `var(--global-palette3)` (accent) |
| Text color | White or contrast-appropriate color |
| Hover background | `var(--global-palette4)` (accent hover) |
| Border radius | 4–6px (consistent across all buttons) |
| Padding | 12px 24px (desktop); adjust proportionally for mobile |
| Font weight | 600 or 700 |
| Font size | Match body or slightly smaller |
| Transition | Smooth color transition (200–300ms ease) |

Configure these globally in the Kadence Customizer under General → Buttons so every button inherits the same style by default.

---

## 3. Header Builder

Kadence's visual header builder replaces the need for header plugins or Elementor headers.

### Header Structure

```
Top Bar (optional)
├── Phone number, email, hours, social icons
├── Background: dark or accent color
└── Text: small, muted

Main Header
├── Logo (left or center)
├── Primary Navigation (right or center)
├── CTA Button (right)
└── Mobile trigger (hamburger)

Sticky Header (optional)
├── Shrunk logo
├── Condensed navigation
└── Same CTA button
```

### Header Configuration

| Setting | Standard |
|---|---|
| Layout | Logo left, navigation right — unless design requires otherwise |
| Top bar | Use only when client has phone/email/hours to display |
| Transparent header | Use on homepage hero only; solid header on all interior pages |
| Sticky header | Enable only if the design calls for it; use shrink behavior |
| Mobile breakpoint | 1024px — navigation collapses to mobile menu below this |
| Mobile menu | Full-width slide-down or off-canvas drawer |
| CTA in header | One CTA button in the header — primary conversion action |
| Logo size | Set explicit max-width and max-height to prevent CLS |
| Header elements | Use Kadence's drag-and-drop header builder — no custom HTML widgets for core navigation |

### Header Rules

| Rule | Detail |
|---|---|
| Never build the header in Elementor | Kadence header builder handles all standard header layouts |
| Always set the mobile breakpoint | Default is 1024px — verify it works for the specific navigation length |
| Always include a mobile CTA | Phone number or primary CTA visible in the mobile header |
| Keep navigation to one level on mobile | Avoid deep nested dropdowns on mobile — simplify if possible |
| Test sticky header on long pages | Verify it does not overlap content or cause layout shifts |
| Logo must have width/height | Prevent CLS during load |

---

## 4. Footer Builder

Kadence's footer builder works identically to the header builder — visual, drag-and-drop, row-based.

### Footer Structure

```
Footer Top Row (optional)
├── CTA band or newsletter signup
└── Full-width accent background

Footer Middle Row
├── Column 1: Logo + description
├── Column 2: Quick links
├── Column 3: Services
├── Column 4: Contact info (NAP)
└── Responsive: stack columns on mobile

Footer Bottom Row
├── Copyright text
├── Privacy policy / terms links
└── Credit link (optional)
```

### Footer Configuration

| Setting | Standard |
|---|---|
| Column layout | 4 columns on desktop; 2 on tablet; 1 (stacked) on mobile |
| Background | Dark background (`var(--global-palette6)` or project-specific dark color) |
| Text color | Light text (`var(--global-palette9)` or white) |
| Link color | Accent or light color with hover state |
| NAP display | Business name, address, phone — must match GMB exactly |
| Phone number | Clickable `tel:` link |
| Email | Clickable `mailto:` link |
| Copyright | Dynamic year: `© {year} Business Name. All Rights Reserved.` |
| Social icons | Kadence social icons element — link to all active profiles |
| Schema | LocalBusiness or Organization schema references the footer NAP |

### Footer Rules

| Rule | Detail |
|---|---|
| Never build the footer in Elementor | Kadence footer builder handles all standard layouts |
| Always include NAP in the footer | Name, address, phone — consistent with site-wide NAP and GMB |
| Always include privacy policy link | Legal requirement for most sites |
| Dynamic copyright year | Use Kadence's dynamic date or a short PHP snippet — never hardcode the year |
| Footer links must work | Test every footer link before launch |

---

## 5. Kadence Elements

Kadence Elements is a template injection system that allows content to be inserted at WordPress hook points without editing template files. This is one of the most powerful features in the framework.

### What Kadence Elements Does

| Feature | Description |
|---|---|
| Hook-based injection | Insert content at any WordPress or Kadence hook point without touching PHP files |
| Conditional display | Show/hide content based on page type, specific page, post type, user role |
| Custom code blocks | Insert PHP, HTML, CSS, or JavaScript via the block editor |
| Replace templates | Override headers, footers, or content areas for specific pages |

### When to Use Kadence Elements

| Use Case | Example |
|---|---|
| CTA band after content | Insert a call-to-action section after `the_content` on all service pages |
| Custom schema per page type | Inject JSON-LD schema into `wp_head` for specific page types |
| Announcement bar | Show a site-wide announcement above the header |
| Page-specific CSS | Load custom CSS only on a specific page |
| Mobile sticky bar | Inject a sticky phone CTA at the bottom of mobile screens |
| Exit intent or popup trigger | Inject script before `</body>` |
| Template override | Replace the default 404 page template |

### Kadence Elements vs. Child Theme Code

| Scenario | Approach |
|---|---|
| Content injection visible to content editors | Kadence Elements — manageable in WordPress admin |
| Logic-heavy functionality with conditionals and data processing | Child theme code in `inc/hooks.php` |
| One-off content sections on specific pages | Kadence Elements |
| Reusable functional patterns across multiple projects | Child theme code (portable across projects) |
| Quick CTA or banner additions by non-developers | Kadence Elements |

### Kadence Elements Rules

| Rule | Detail |
|---|---|
| Name every element descriptively | "Mobile Sticky CTA - All Service Pages" not "Element 1" |
| Set display conditions precisely | Never leave an element showing globally unless that is the intent |
| Test responsive behavior | Elements injected via hooks may render differently on mobile |
| Document elements | Maintain a list of active Kadence Elements and their purpose in project docs |
| Priority matters | When multiple elements target the same hook, set priorities to control order |

---

## 6. Kadence Blocks

Kadence Blocks Pro is the primary page-building tool in the block editor.

### Approved Block Usage

| Block | Use Case |
|---|---|
| **Row Layout** | Multi-column layouts, section containers |
| **Advanced Heading** | All headings with fine-tuned typography, spacing, and responsive controls |
| **Advanced Text** | Body text with extended formatting options |
| **Advanced Button** | CTA buttons with styling, icons, and hover effects |
| **Advanced Image** | Images with overlay, hover effects, and responsive sizing |
| **Icon List** | Feature lists, service lists, benefit lists |
| **Info Box** | Service cards, feature cards with icon + title + description |
| **Tabs / Accordion** | FAQ sections, tabbed content, collapsible sections |
| **Testimonial** | Customer review display |
| **Advanced Gallery** | Image galleries, before/after, portfolio |
| **Form** | Contact forms, quote request forms |
| **Count Up** | Statistics, numbers, social proof counters |
| **Table of Contents** | Blog posts, long-form content |
| **Spacer** | Controlled vertical spacing between sections |
| **Lottie Animation** | Lightweight animations (use sparingly — performance impact) |

### Block Configuration Standards

| Setting | Standard |
|---|---|
| Section padding | Use consistent vertical padding across all sections: 60–80px desktop, 40–60px tablet, 30–40px mobile |
| Section max width | Match the global container width (1200px default) |
| Block spacing | Use Kadence's built-in spacing controls — never add empty paragraphs or `<br>` tags for spacing |
| Colors | Always select from the global palette — never use the custom color picker to input hex values |
| Typography | Use theme defaults from the Customizer wherever possible; override in blocks only when a specific design requires it |
| Responsive controls | Always check and adjust the tablet and mobile views for every block |

### Row Layout Standards

| Setting | Standard |
|---|---|
| Columns | Defined per layout need: 2-column (60/40, 50/50), 3-column (33/33/33), 4-column (25/25/25/25) |
| Column gap | 30px default (desktop); reduce on mobile |
| Mobile stacking | Columns stack to full width on mobile by default |
| Stack order | Content column first on mobile (verify reading order makes sense) |
| Inner column padding | Consistent within a page — 20–30px |
| Background | Use palette colors, gradients, or images — never hardcoded hex |
| Dividers | Use Kadence section dividers sparingly — only when the design requires visual separation |

---

## 7. Layout Standards

### Page Section Anatomy

Every page is composed of repeating section patterns. Each section follows this structure:

```
Section (Row Layout block)
├── Background (color, image, or gradient from palette)
├── Padding (top/bottom: 60-80px desktop)
├── Max Width (container width)
│
├── Content Area
│   ├── Heading (Advanced Heading block)
│   ├── Body text (Advanced Text or paragraph blocks)
│   ├── Supporting elements (icons, images, buttons)
│   └── CTA (Advanced Button block)
│
└── Responsive overrides
    ├── Tablet padding reduced
    ├── Mobile padding further reduced
    └── Mobile-specific visibility toggles
```

### Section Spacing System

Maintain consistent vertical rhythm across all pages:

| Spacing Type | Desktop | Tablet | Mobile |
|---|---|---|---|
| Section padding (top/bottom) | 70–80px | 50–60px | 35–45px |
| Between elements within a section | 20–30px | 16–24px | 12–20px |
| Between heading and content | 16–20px | 14–16px | 12–14px |
| Between paragraphs | Theme default (1.7 line height handles this) | Same | Same |
| CTA button margin top | 24–32px | 20–24px | 16–20px |

### Content Width Standards

| Layout | Width |
|---|---|
| Full-width section background | 100% viewport |
| Content within full-width section | Container max width (1200px) |
| Blog post content | 750–800px for readability |
| Landing page sections | Container max width (1200px) |
| Narrow content (forms, single-column text) | 600–750px centered |

---

## 8. Responsive Standards

### Breakpoints

Kadence uses these breakpoints in its responsive controls:

| Device | Breakpoint | Notes |
|---|---|---|
| Desktop | ≥ 1025px | Default view — all base settings |
| Tablet | 769px – 1024px | Kadence tablet breakpoint |
| Mobile | ≤ 768px | Kadence mobile breakpoint |

### Responsive Rules

| Rule | Detail |
|---|---|
| Check every section at all 3 breakpoints | Kadence provides responsive preview — use it for every section |
| Font sizes must scale | Headings that look good at 48px desktop may need 28px on mobile |
| Column stacking | Multi-column layouts must stack cleanly on mobile — verify reading order |
| Touch targets | Buttons and links minimum 44×44px on mobile |
| Horizontal overflow | Test for horizontal scroll on mobile — zero tolerance |
| Image sizing | Images must not overflow their container on any viewport |
| Navigation | Must collapse to mobile menu at or before 1024px |
| Forms | Form inputs must be full-width on mobile — no side-by-side fields on small screens |
| Padding reduction | Section padding reduces proportionally — see spacing system above |
| Hide/show elements | Use Kadence's responsive visibility controls to show mobile-specific CTAs or hide desktop-only elements |

### Mobile-Specific Elements

| Element | Implementation |
|---|---|
| Sticky mobile CTA | Kadence Element hooked to `wp_footer`, visible only on mobile, with `position: fixed` at bottom |
| Click-to-call button | Phone number as a prominent button in mobile header and sticky bar |
| Simplified navigation | Fewer items on mobile if desktop menu is long — prioritize conversion pages |
| Mobile hero | Verify hero image crops correctly; text remains readable; CTA is prominent |

---

## 9. Performance

### Kadence Performance Advantages

Kadence generates cleaner, lighter HTML than most themes and page builders. Preserve this advantage:

| Rule | Detail |
|---|---|
| Disable unused Kadence Blocks | In Kadence Blocks settings, deactivate blocks not used on the project — each block registers assets |
| Use Kadence over Elementor | Kadence produces less DOM, fewer scripts, and smaller CSS than Elementor |
| Minimize block nesting | Deeply nested Row Layouts increase DOM complexity — keep nesting under 3 levels |
| Limit custom fonts | 2 font families maximum; load only used weights |
| Use global palette | Global palette CSS variables are loaded once — custom colors per block add inline CSS |
| Avoid Lottie on mobile | Lottie animations add JavaScript weight — disable on mobile or use very sparingly |
| Defer third-party scripts | Any script added via Kadence Elements should be deferred or delayed |

### Performance Targets (Kadence-Specific)

| Metric | Target with Kadence | Red Flag |
|---|---|---|
| DOM elements | ≤ 1500 | > 2500 indicates excessive nesting |
| CSS file count | ≤ 5 (after WP Rocket optimization) | > 10 means unused blocks or conflicting styles |
| JS file count | ≤ 6 (after WP Rocket optimization) | > 12 means too many interactive blocks or features |
| First Contentful Paint | ≤ 1.0s | > 1.8s investigate render-blocking resources |
| Total page weight | ≤ 1.5 MB | > 3 MB audit images and assets |

---

## 10. Template Standards

### Page Templates Available

| Template | Use Case | Set In |
|---|---|---|
| Default | Standard pages with header, content, footer | Editor → Page Attributes |
| Full Width | No sidebar, standard container | Editor → Page Attributes |
| Canvas (no header/footer) | Landing pages, standalone pages | Editor → Page Attributes |
| Left Sidebar | Content + left sidebar (rare) | Editor → Page Attributes |
| Right Sidebar | Content + right sidebar (blog) | Editor → Page Attributes |

### Template Selection by Page Type

| Page Type | Template | Sidebar |
|---|---|---|
| Homepage | Default or Full Width | None |
| Service page | Full Width | None |
| Location page | Full Width | None |
| Blog post | Default | Right sidebar (optional) |
| Blog archive | Default | Right sidebar (optional) |
| About page | Full Width | None |
| Contact page | Full Width | None |
| Landing page | Canvas | None |
| Thank you page | Full Width | None |
| 404 page | Full Width | None |
| Privacy policy | Default | None |

### Custom Templates in Child Theme

When Kadence's built-in templates are insufficient:

```
themes/kadence-child/
└── templates/
    ├── template-landing.php     # Custom landing page template
    ├── template-no-title.php    # Full width without page title
    └── template-narrow.php      # Narrow content width for text-heavy pages
```

Template file header:

```php
<?php
/**
 * Template Name: Landing Page
 * Template Post Type: page
 *
 * @package Waif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();
?>

<main id="primary" class="site-main waif-landing">
    <?php
    while ( have_posts() ) {
        the_post();
        the_content();
    }
    ?>
</main>

<?php
get_footer();
```

---

## 11. Child Theme Rules

### Structure

```
themes/kadence-child/
├── style.css                # Theme header + minimal critical styles
├── functions.php            # Bootstrap file — requires inc/ files
├── screenshot.png           # Theme screenshot (1200×900px)
├── inc/
│   ├── hooks.php            # Custom actions and filters
│   ├── enqueue.php          # Script and style registration
│   ├── helpers.php          # Utility functions
│   ├── schema.php           # Custom JSON-LD schema output
│   └── customizer.php       # Customizer extensions (rare)
├── assets/
│   ├── css/
│   │   ├── custom.css       # Global custom styles
│   │   └── components/      # Per-component CSS files
│   ├── js/
│   │   ├── custom.js        # Global custom scripts
│   │   └── components/      # Per-component JS files
│   └── images/              # Theme-level images
├── template-parts/          # Reusable template partials
└── templates/               # Custom page templates
```

### style.css Header

```css
/*
Theme Name:   Kadence Child - Project Name
Theme URI:    https://github.com/agency/project-name
Description:  Child theme for Project Name built on the WordPress AI Framework.
Author:       Agency Name
Author URI:   https://agency-domain.com
Template:     flavor/flavor
Version:      1.0.0
License:      Proprietary
Text Domain:  waif-project
*/
```

### functions.php Pattern

```php
<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WAIF_CHILD_VERSION', '1.0.0' );
define( 'WAIF_CHILD_PATH', get_stylesheet_directory() );
define( 'WAIF_CHILD_URL', get_stylesheet_directory_uri() );

// Load modular includes
require_once WAIF_CHILD_PATH . '/inc/enqueue.php';
require_once WAIF_CHILD_PATH . '/inc/hooks.php';
require_once WAIF_CHILD_PATH . '/inc/helpers.php';
require_once WAIF_CHILD_PATH . '/inc/schema.php';
```

### Child Theme Rules

| Rule | Detail |
|---|---|
| Never edit parent theme files | All modifications in the child theme |
| Keep functions.php clean | Only `define` constants and `require` includes — no logic in functions.php |
| Split by concern | Hooks in `hooks.php`, enqueueing in `enqueue.php`, utilities in `helpers.php` |
| Enqueue styles properly | Use `wp_enqueue_style` with parent theme as dependency |
| Enqueue scripts with defer | `wp_enqueue_script` with `in_footer: true` and `strategy: 'defer'` |
| Prefix everything | `waif_` prefix on all functions, hooks, and handles |
| Template overrides | Only override parent templates when absolutely necessary — prefer hooks |

### Enqueuing Assets

```php
<?php

declare(strict_types=1);

add_action( 'wp_enqueue_scripts', 'waif_enqueue_assets' );

function waif_enqueue_assets(): void {
    // Parent theme styles (loaded by Kadence automatically)

    // Child theme custom CSS
    wp_enqueue_style(
        'waif-custom',
        WAIF_CHILD_URL . '/assets/css/custom.css',
        [],
        WAIF_CHILD_VERSION,
    );

    // Custom JavaScript — deferred, loaded in footer
    wp_enqueue_script(
        'waif-custom',
        WAIF_CHILD_URL . '/assets/js/custom.js',
        [],
        WAIF_CHILD_VERSION,
        [
            'in_footer' => true,
            'strategy'  => 'defer',
        ],
    );
}
```

---

## 12. Custom CSS Standards

### Where Custom CSS Goes

| Location | When |
|---|---|
| Child theme `assets/css/custom.css` | Global styles that apply site-wide — properly enqueued |
| Child theme `assets/css/components/` | Component-specific styles loaded conditionally |
| Kadence Elements (CSS block) | Page-specific CSS that only loads on that page via Kadence Elements display conditions |
| Kadence Customizer → Additional CSS | Quick tweaks during development — move to child theme CSS file before launch |

### Where Custom CSS Must Never Go

| Location | Why Not |
|---|---|
| Block-level "Additional CSS Class" fields (for writing CSS) | Kadence class fields are for adding class names, not writing CSS rules |
| Inline `<style>` tags in content | Not maintainable, not cacheable, breaks separation of concerns |
| Parent theme stylesheet | Will be overwritten on update |
| Plugin custom CSS fields | Scattered, hard to find, not version controlled |

### CSS Rules

| Rule | Standard |
|---|---|
| Use Kadence global palette variables | `var(--global-palette1)` not `#1a56db` |
| BEM naming with project prefix | `.waif-hero__title` not `.hero-title` or `.my-heading` |
| Mobile-first media queries | Base styles are mobile; `min-width` queries add desktop complexity |
| No `!important` | Override specificity properly; `!important` only as documented last resort |
| One file per component | Large projects split CSS by component in `assets/css/components/` |
| Comments in CSS | Section headers, non-obvious overrides, and specificity workarounds get comments |

---

## 13. Hooks and Filters

### Kadence Theme Hooks

Kadence provides a comprehensive hook system. Use these for content injection instead of editing template files:

| Hook | Location | Use Case |
|---|---|---|
| `kadence_before_header` | Before the entire header | Announcement bars, notification banners |
| `kadence_after_header` | After the entire header | Breadcrumbs (if not using Kadence/Rank Math default), secondary navigation |
| `kadence_before_main_content` | Before the main content area | Page-level banners, hero overrides |
| `kadence_after_main_content` | After the main content area | CTA bands, related content, newsletter signup |
| `kadence_before_footer` | Before the footer | Pre-footer CTA section |
| `kadence_after_footer` | After the footer | Sticky mobile bars, tracking scripts, popups |
| `kadence_single_before_inner_content` | Before post/page inner content | Custom breadcrumbs, post metadata |
| `kadence_single_after_inner_content` | After post/page inner content | Author box, related posts, CTA |
| `kadence_hero_header` | Inside the page hero/title area | Custom hero layouts |
| `kadence_404_before_inner_content` | Before 404 page content | Custom 404 messaging |

### Using Kadence Hooks

```php
// Add a CTA band after the main content on all service pages
add_action( 'kadence_after_main_content', 'waif_add_service_cta_band' );

function waif_add_service_cta_band(): void {
    if ( ! is_singular( 'page' ) ) {
        return;
    }

    // Check if this page uses a specific template or has a meta flag
    $show_cta = get_post_meta( get_the_ID(), '_waif_show_cta_band', true );

    if ( $show_cta !== 'yes' ) {
        return;
    }

    get_template_part( 'template-parts/cta-band' );
}
```

### Kadence Filters

| Filter | Purpose | Example |
|---|---|---|
| `kadence_logo_url` | Modify the logo link URL | Point logo to a different page |
| `kadence_breadcrumb_args` | Modify breadcrumb configuration | Change separator, home text |
| `kadence_post_layout` | Change post layout programmatically | Force full-width for specific categories |
| `kadence_dynamic_css` | Inject CSS into Kadence's dynamic stylesheet | Add custom CSS variables |

### Hooks vs. Kadence Elements

| Situation | Use |
|---|---|
| Content that content editors should manage visually | Kadence Elements (block editor interface) |
| Logic-dependent injections with PHP conditionals | Child theme hooks in `inc/hooks.php` |
| Injections that must work identically across all framework projects | Child theme hooks (portable, version controlled) |
| Quick, per-project content additions | Kadence Elements (faster to implement) |

---

## 14. Accessibility

### Kadence Accessibility Features

Kadence includes built-in accessibility support. Verify these are configured correctly:

| Feature | Setting |
|---|---|
| Skip to content link | Built-in — verify it is visible on focus |
| Focus styles | Kadence provides default focus outlines — do not remove them |
| ARIA landmarks | `<header>`, `<main>`, `<footer>`, `<nav>` are semantic and ARIA-compatible |
| Mobile menu | Keyboard navigable, with proper `aria-expanded` states |
| Dropdown menus | Keyboard accessible, with `aria-haspopup` on parent items |

### Accessibility Standards for Kadence Projects

| Requirement | Implementation |
|---|---|
| Color contrast | Verify all global palette text/background combinations meet WCAG AA (4.5:1) |
| Heading hierarchy | One H1, logical H2–H6 sequence — configure in Kadence Advanced Heading blocks |
| Image alt text | Every image block must have alt text filled |
| Button text | Every button has descriptive text — no icon-only buttons without `aria-label` |
| Form labels | Kadence Form block includes labels — never hide them visually without `aria-label` |
| Animations | If using Kadence entrance animations, respect `prefers-reduced-motion` |
| Link distinction | Links must be visually distinguishable from surrounding text (underline or color + additional indicator) |

---

## 15. AI Rules for Kadence

### Before Working with Kadence

1. Read this document (`KADENCE_STANDARDS.md`) in full
2. Check if Kadence can handle the requirement before reaching for Elementor
3. Follow the Decision Hierarchy (Section 1)
4. Use the global palette — never hardcode colors
5. Check responsive rendering at all three breakpoints

### Mandatory Rules for AI Assistants

| Rule | Detail |
|---|---|
| Never edit the Kadence parent theme | All modifications through child theme or Kadence Elements |
| Never recommend Elementor when Kadence can do the job | Follow the decision hierarchy strictly |
| Always use the global palette | Reference `var(--global-palette1)` through `var(--global-palette9)` |
| Always check responsive | Provide responsive values for padding, font sizes, and layouts |
| Never use Spacer blocks for layout | Use proper padding and margin on sections |
| Never use empty paragraphs for spacing | Use block spacing controls or CSS |
| Never nest Row Layouts deeper than 3 levels | Excessive nesting bloats the DOM |
| Always name Kadence Elements | Descriptive names with page/section context |
| Always set display conditions on Kadence Elements | Never leave them displaying globally unintentionally |
| Prefer Kadence hooks over template file overrides | Hooks are cleaner and survive theme updates |
| Document custom hooks | Record in `ai/conventions/` when introducing new hook patterns |

### AI Code Generation for Kadence

When generating child theme code that interacts with Kadence:

- Use Kadence's hook names exactly (check spelling — `kadence_after_main_content` not `kadence_after_content`)
- Check Kadence's active hooks by reviewing the parent theme source or documentation
- Generate conditional logic to target specific page types (do not output globally without conditions)
- Test that generated CSS uses palette variables, not hardcoded colors

---

## 16. QA Checklist

### Global Configuration

- [ ] Global color palette defined (all 9 slots populated with project colors)
- [ ] Global typography configured (body + headings, responsive sizes)
- [ ] Global button styles set (background, hover, border radius, padding)
- [ ] Container width set (1200px or project-specific)
- [ ] Sidebar disabled globally (unless project requires it)
- [ ] Kadence configuration exported to `templates/config/kadence-theme-settings.json`

### Header

- [ ] Logo uploaded with explicit width/height
- [ ] Favicon uploaded (512×512px)
- [ ] Primary navigation assigned and complete
- [ ] CTA button in header with correct link and text
- [ ] Mobile menu works correctly (opens, closes, all links work)
- [ ] Mobile CTA visible (phone number or primary action)
- [ ] Transparent header on homepage only (if applicable)
- [ ] Sticky header behaves correctly (no content overlap, no CLS)
- [ ] Header renders correctly on desktop, tablet, and mobile

### Footer

- [ ] Footer columns display correctly on all viewports
- [ ] NAP information matches GMB exactly
- [ ] Phone number is a clickable `tel:` link
- [ ] Email is a clickable `mailto:` link
- [ ] All footer links work
- [ ] Copyright year is dynamic
- [ ] Privacy policy link present
- [ ] Social icons link to correct profiles and open in new tabs

### Pages (per-page checks)

- [ ] Correct page template selected
- [ ] Section padding is consistent with spacing system
- [ ] Colors reference global palette (no hardcoded hex)
- [ ] Typography matches global settings (no per-block overrides without reason)
- [ ] All images have alt text
- [ ] All buttons link correctly and have hover states
- [ ] Page renders correctly on desktop (1440px)
- [ ] Page renders correctly on tablet (768px)
- [ ] Page renders correctly on mobile (375px)
- [ ] No horizontal scrolling on any viewport
- [ ] No excessive DOM nesting (Row Layout depth ≤ 3)

### Kadence Elements

- [ ] Every element has a descriptive name
- [ ] Display conditions are set correctly (not showing globally unintentionally)
- [ ] Elements render correctly on target pages
- [ ] Mobile sticky CTA works on mobile (if applicable)
- [ ] No Kadence Elements conflicting with each other

### Performance

- [ ] Unused Kadence Blocks disabled in settings
- [ ] Font families ≤ 2
- [ ] Font weights ≤ 3 per family
- [ ] No Lottie animations on mobile (or used very sparingly)
- [ ] DOM element count ≤ 1500
- [ ] Page passes Core Web Vitals targets

### Accessibility

- [ ] Skip-to-content link visible on keyboard focus
- [ ] All heading hierarchy is logical (H1 → H2 → H3)
- [ ] Color contrast passes WCAG AA
- [ ] All form fields have visible labels
- [ ] All interactive elements are keyboard accessible
- [ ] Focus styles are visible and not removed

---

## 17. Common Mistakes

| Mistake | Why It Happens | Correct Approach |
|---|---|---|
| Building the header in Elementor | Habit from Elementor-only workflows | Use Kadence header builder — it handles all standard layouts |
| Building the footer in Elementor | Same as above | Use Kadence footer builder |
| Hardcoding colors in blocks | Using the custom color picker instead of palette | Always select from the global palette swatches |
| Using empty paragraphs for spacing | Quick visual fix | Use Kadence block spacing controls or the Spacer block with defined values |
| Nesting Row Layouts 4-5 levels deep | Trying to achieve complex layouts | Flatten the structure; 3 levels maximum |
| Not checking tablet view | Desktop and mobile are tested, tablet is skipped | Always verify all three breakpoints |
| Installing a header/footer plugin | Not knowing Kadence has built-in builders | Kadence header/footer builder eliminates the need for plugins |
| Writing CSS directly in the Customizer Additional CSS | Quick tweaks during development | Move to child theme `assets/css/custom.css` before launch |
| Not setting image dimensions in Kadence Image blocks | Assumed to be automatic | Set width and height to prevent CLS |
| Leaving Kadence Elements without display conditions | Created during development, conditions forgotten | Always set specific display conditions |
| Overriding global typography per block | "This heading should be slightly different" | Use global typography; override only with documented design justification |
| Not disabling unused Kadence Blocks | Default installs all blocks active | Deactivate every block not used in the project |
| Using Kadence when Elementor is already on the page | Mixing builders on the same page | One builder per page — do not mix Kadence Blocks and Elementor on the same page |
| Not exporting Kadence settings | Settings exist only in the database | Export to `templates/config/` for reproducibility and version control |
| Using the Kadence starter templates without modification | Starting from a pre-built template and not fully customizing | Starter templates are references only — build from the framework's own structure |

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — complete Kadence development standards defined |