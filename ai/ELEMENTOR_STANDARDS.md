# ELEMENTOR_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — applies to every page where Elementor is used |
| Companion Documents | `KADENCE_STANDARDS.md`, `WORDPRESS_STANDARDS.md`, `PLUGIN_STANDARDS.md`, `CODING_STANDARDS.md` |

> **Elementor is the exception, not the default.**
> Kadence Theme and Kadence Blocks Pro are the primary build system. Elementor is introduced only when a specific layout requirement exceeds what Kadence can deliver. This document governs how Elementor is used when it is used — and how to prevent it from degrading the performance and maintainability standards the framework requires.

---

## 1. Elementor Philosophy

Elementor is a powerful visual page builder. It is also the single largest source of frontend bloat, excessive DOM depth, render-blocking assets, and maintenance complexity in WordPress projects.

This framework uses Elementor deliberately and restrictively.

### Core Beliefs

| Belief | Implication |
|---|---|
| Elementor is a precision tool, not a default | It solves specific layout problems that Kadence cannot — nothing more |
| Every Elementor page is a performance liability | Elementor adds 200–500 KB of CSS/JS minimum — this cost must be justified |
| Mixing builders on a single page is prohibited | A page is either Kadence Blocks or Elementor — never both |
| Elementor decisions must be documented | Every page built with Elementor must have a recorded justification |
| Reuse eliminates waste | Elementor templates, saved sections, and global widgets prevent rebuild and inconsistency |
| Elementor must be invisible to the visitor | The output must be indistinguishable from a hand-coded page in terms of speed and cleanliness |

### The Cost of Elementor

Every page built with Elementor inherits this baseline cost before any content is added:

```
Elementor Page Baseline
├── elementor-frontend.min.css        ~120 KB
├── elementor-frontend.min.js         ~150 KB
├── elementor-pro (if Pro)            ~80 KB CSS + ~100 KB JS
├── Google Fonts (if not disabled)    ~50-100 KB
├── Swiper.js (if sliders used)       ~40 KB
├── Dialog.js                         ~10 KB
├── Motion effects (if used)          ~30 KB
└── DOM overhead                      ~200-800 additional DOM nodes
```

This cost is acceptable only when the layout requires capabilities Kadence does not have.

---

## 2. When Elementor Is Allowed

Elementor is permitted only when the layout requirement meets one or more of these criteria:

| Criterion | Example |
|---|---|
| Complex multi-column layouts with overlapping elements | Hero sections with layered images, text, and decorative elements that overlap using absolute positioning |
| Advanced animation sequences | Multi-step scroll-triggered animations coordinated across elements |
| Mega menus with rich content | Navigation dropdowns containing images, columns, icons, and CTAs |
| Template-driven location pages at scale | Location pages using Elementor's dynamic content with ACF or custom fields for variable content per location |
| Interactive elements beyond Kadence's capability | Custom tab systems, filterable portfolios, or multi-step forms that Kadence Blocks cannot produce |
| Client-requested design that specifically requires Elementor widgets | A design deliverable that maps to Elementor Pro widgets with no Kadence equivalent |
| Existing Elementor site under management | Sites already built with Elementor where rebuilding in Kadence is not justified |

### Decision Documentation

Every page built with Elementor must have a brief justification recorded:

```
File: ai/decisions/elementor-usage-{page-slug}.md

Page: /service-area/camden-nj/
Builder: Elementor
Justification: Location pages use a shared Elementor template with dynamic
content fields (ACF) for location-specific hero text, testimonials, and
service area data. Kadence Blocks does not support dynamic field insertion
at this level without custom block development.
Date: 2026-08-01
```

---

## 3. When Kadence Should Be Used Instead

If any of the following are true, Kadence is the correct choice. Do not use Elementor.

| Scenario | Why Kadence |
|---|---|
| Standard page layout (hero, content sections, CTA) | Kadence Row Layout + Advanced Heading + Advanced Button handles this natively |
| Blog posts and archives | Kadence theme templates and block editor are designed for this |
| Simple landing pages | Kadence Full Width or Canvas template with blocks |
| About pages, contact pages, privacy pages | Standard content pages — no builder complexity needed |
| Headers and footers | Kadence header/footer builder is purpose-built and lighter |
| Pages with only text, images, and buttons | Zero justification for Elementor overhead |
| Service pages with standard structure | Hero → description → process → FAQ → CTA — all achievable in Kadence |
| FAQ sections | Kadence Accordion block handles this with proper schema support |
| Testimonial display | Kadence Testimonial block covers standard layouts |
| Simple forms | Kadence Form block or a lightweight form plugin |

### The One-Question Test

Before choosing Elementor, answer this question:

> *Can Kadence Blocks, combined with the child theme, achieve this layout to an acceptable quality level?*

If the answer is **yes** or **probably** — use Kadence. Elementor enters only when the answer is a clear, documented **no**.

---

## 4. Container Architecture

### Flexbox Containers Only

Elementor's legacy Sections and Columns system is deprecated. All new builds must use Flexbox Containers exclusively.

| Rule | Standard |
|---|---|
| Container type | Flexbox Container only — never legacy Sections/Columns |
| Experiment status | Flexbox Container must be set to "Active" in Elementor → Settings → Features |
| Legacy conversion | Existing legacy layouts should be converted to containers during maintenance cycles |

### Container Structure

```
Page
└── Container (full-width, acts as section)
    ├── direction: column (default) or row
    ├── max-width: 1200px (content constraint)
    ├── padding: section spacing values
    │
    └── Container (inner, acts as content row)
        ├── direction: row
        ├── gap: column spacing
        ├── wrap: wrap (for responsive stacking)
        │
        ├── Container (column 1)
        │   └── Widgets
        ├── Container (column 2)
        │   └── Widgets
        └── Container (column 3)
            └── Widgets
```

### Container Rules

| Rule | Detail |
|---|---|
| Maximum nesting depth | 3 levels — outer section → inner row → column containers |
| Width strategy | Outer container: full width with background. Inner container: max-width 1200px centered |
| Flex direction | `row` for multi-column layouts; `column` for stacked single-column sections |
| Gap | Use CSS gap property instead of margin on child elements — consistent and cleaner |
| Align and justify | Use flex alignment controls — never add empty spacer widgets for positioning |
| Min-height | Set only when the section must occupy a specific viewport height (hero sections) |
| Overflow | Default `hidden` on outer containers to prevent horizontal scroll from positioned elements |

### Flexbox Configuration

| Property | Standard Usage |
|---|---|
| `flex-direction` | `row` for columns; `column` for vertical stacking |
| `justify-content` | `space-between` for evenly distributed columns; `center` for centered content |
| `align-items` | `stretch` for equal-height columns; `center` for vertically centered content |
| `flex-wrap` | `wrap` — always, to allow responsive stacking |
| `gap` | Replace individual margin with gap: `20px` to `40px` depending on context |
| `flex-grow` / `flex-basis` | Control column widths: `flex-basis: 50%` for half-width, `flex-grow: 1` for equal distribution |

---

## 5. Global Colors and Fonts

### Global Colors

Elementor has its own Global Colors system. When Elementor is active alongside Kadence, color management must be coordinated.

| Rule | Standard |
|---|---|
| Primary source of truth | Kadence global palette (`--global-palette1` through `--global-palette9`) |
| Elementor global colors | Must mirror the Kadence palette exactly — same hex values, same roles |
| Usage in Elementor | Always select from Elementor Global Colors — never use the hex picker directly |
| Naming | Name Elementor global colors to match their function: "Primary", "Secondary", "Accent", "Text", "Background" |

### Elementor Global Color Configuration

| Elementor Color | Maps To | Kadence Equivalent |
|---|---|---|
| Primary | Brand primary | `--global-palette1` |
| Secondary | Brand secondary | `--global-palette2` |
| Accent | CTA / action color | `--global-palette3` |
| Text | Body text | `--global-palette6` |

Additional project colors are added as custom global colors, named descriptively.

### Global Fonts

| Rule | Standard |
|---|---|
| Elementor Google Fonts | Disabled — Elementor must not load its own fonts |
| Font management | Fonts loaded by Kadence theme or child theme `enqueue.php` only |
| Elementor typography | Set via Global Fonts referencing the same families loaded by the theme |
| Elementor Default Fonts setting | Set to the project's body font so widgets inherit correctly |

### Disabling Elementor Font Loading

```php
// Disable Elementor's Google Fonts loading — fonts managed by Kadence/child theme
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
```

This filter must be present in the child theme `inc/hooks.php` on every project that uses Elementor.

---

## 6. Reusable Templates and Template Kits

### Saved Templates

Elementor's template library is central to the framework's reuse strategy.

| Template Type | Purpose | When to Create |
|---|---|---|
| **Saved Section** | A single reusable section (hero, CTA band, FAQ, testimonial) | When the same section appears on 3+ pages |
| **Saved Page** | A full page layout as a reusable starting point | When building multiple pages with the same structure (location pages) |
| **Global Widget** | A widget instance shared across pages — edit once, updates everywhere | When the same content block (phone CTA, announcement) appears site-wide |

### Template Naming Convention

```
[Type] - [Section/Page] - [Variant]
```

| Example | Description |
|---|---|
| `Section - Hero - Service Page` | Hero section used on service pages |
| `Section - CTA Band - Dark` | Dark-background CTA band |
| `Section - FAQ - With Schema` | FAQ accordion section with FAQPage schema |
| `Section - Testimonials - Slider` | Testimonial slider section |
| `Page - Location Template` | Full location page template |
| `Global - Mobile Sticky CTA` | Global widget: sticky mobile call button |

### Template Rules

| Rule | Detail |
|---|---|
| Name every template descriptively | Follow the naming convention above — no "Template 1" or "My Section" |
| Organize in folders | Use Elementor's template folders: "Sections", "Pages", "Global Widgets" |
| Export templates | Save JSON exports to `templates/elementor/` in the repository |
| Document templates | Maintain a template registry listing all saved templates and their purpose |
| Use Global Widgets for shared content | Phone numbers, addresses, and CTAs that appear on every page should be Global Widgets — edit once, update everywhere |
| Delete unused templates | Templates that are no longer in use must be deleted to reduce database clutter |

### Template Kits

For multi-site projects (location-based lead gen), Template Kits allow exporting a complete set of templates for import into new installations.

| Rule | Detail |
|---|---|
| Kit contents | All saved sections, page templates, global widgets, theme styles, and site settings |
| Export format | Elementor Kit export (ZIP file) |
| Storage | `templates/elementor/kits/` in the repository |
| Version tracking | Name kits with version: `agency-kit-v1.0.0.zip` |
| Import workflow | Import kit → update Global Colors to project palette → update Global Widgets with project content |

---

## 7. Widget Standards

### Approved Widgets

Use only the widgets necessary for the layout. Every unused widget that registers assets is dead weight.

| Widget | Use Case | Performance Notes |
|---|---|---|
| **Heading** | Page and section headings | Lightweight |
| **Text Editor** | Body content paragraphs | Lightweight |
| **Button** | CTA buttons | Lightweight |
| **Image** | Content images, hero images | Set dimensions to prevent CLS |
| **Icon List** | Feature lists, service lists | Lightweight |
| **Image Box / Icon Box** | Service cards, feature cards | Moderate |
| **Accordion / Toggle** | FAQ sections | Loads minimal JS |
| **Tabs** | Tabbed content sections | Loads minimal JS |
| **Testimonial / Testimonial Carousel** | Customer reviews | Carousel loads Swiper.js (~40 KB) |
| **Counter** | Statistics, social proof numbers | Loads Waypoints.js |
| **Form (Pro)** | Contact forms, quote requests | Loads form CSS/JS |
| **Google Maps** | Location maps | Loads Google Maps API — significant performance cost |
| **Posts (Pro)** | Blog post grids, related posts | Can generate heavy queries on large sites |
| **Slides** | Hero sliders (use sparingly) | Loads Swiper.js |
| **Lottie** | Lightweight animations | Loads Lottie player — use sparingly |

### Widgets to Avoid

| Widget | Reason | Alternative |
|---|---|---|
| **Image Carousel** (basic) | Loads Swiper for minimal benefit | Static image grid or Kadence Gallery |
| **Alert** | Available in native blocks | WordPress block or styled div |
| **SoundCloud / Spotify embeds** | Load external iframes and scripts | Link to the service instead |
| **Social Icons** | Kadence handles this in header/footer builder | Use Kadence social icons |
| **Menu Anchor** | Fragile and adds unnecessary elements | Standard HTML `id` attributes |
| **Star Rating** | Static visual — adds no SEO value | Schema markup handles review ratings |
| **Progress Bar / Skills** | Rarely justified; adds JS overhead | Custom CSS or omit |

### Widget Usage Rules

| Rule | Detail |
|---|---|
| Set image dimensions on every Image widget | Width and height must be explicit to prevent CLS |
| Lazy-load images below the fold | Verify `loading="lazy"` is present |
| Hero images must not be lazy-loaded | Set hero image to `loading="eager"` or disable lazy loading on above-fold images |
| Heading widget hierarchy | Follow H1 → H2 → H3 structure — never skip levels or use visual-only heading sizes |
| Button widget links | Every button must have a valid URL, proper text, and correct target (new tab for external) |
| Form widget | Configure email notifications, required fields, success message, and honeypot spam protection |
| Google Maps widget | Replace with a static map image where possible — the Maps API is expensive to load |

---

## 8. Performance Rules

### Asset Loading

| Rule | Implementation |
|---|---|
| Improved Asset Loading | Enable in Elementor → Settings → Performance (reduces CSS/JS to what's actually used per page) |
| Dequeue on non-Elementor pages | Elementor must not load assets on pages where it is not used (see `PLUGIN_STANDARDS.md` for dequeue code) |
| Disable Google Fonts | `add_filter( 'elementor/frontend/print_google_fonts', '__return_false' )` — fonts managed by theme |
| Disable Font Awesome | If not used: Elementor → Settings → Advanced → Load Font Awesome: None |
| Disable eicons where possible | Replace with SVG icons loaded from the child theme to eliminate the eicons font |
| CSS print method | External File — not inline (Elementor → Settings → Advanced) |

### DOM Optimization

| Rule | Standard |
|---|---|
| Maximum container nesting | 3 levels |
| DOM element target | ≤ 2000 elements per Elementor page (higher than Kadence due to Elementor's wrapper divs) |
| Unnecessary wrappers | Remove empty containers that exist only for spacing — use padding instead |
| Widget spacing | Use margin/padding on widgets directly instead of adding Spacer widgets |
| Spacer widget | Avoid — use container padding or widget margin for vertical spacing |

### Performance Targets for Elementor Pages

Elementor pages have a higher baseline cost. Targets are adjusted accordingly but must still meet acceptable thresholds:

| Metric | Elementor Target | Maximum Acceptable | Red Flag |
|---|---|---|---|
| LCP | ≤ 2.0s | ≤ 2.5s | > 3.0s |
| INP | ≤ 150ms | ≤ 200ms | > 300ms |
| CLS | ≤ 0.05 | ≤ 0.1 | > 0.15 |
| Total page weight | ≤ 2.0 MB | ≤ 3.0 MB | > 4.0 MB |
| DOM elements | ≤ 2000 | ≤ 2500 | > 3000 |
| CSS files | ≤ 8 (after optimization) | ≤ 12 | > 15 |
| JS files | ≤ 10 (after optimization) | ≤ 14 | > 18 |

### Optimization Workflow

```
1. Build the page
2. Preview on frontend (not in editor)
3. Run PageSpeed Insights on the live preview
4. If LCP > 2.5s → optimize hero image, preload critical resources
5. If CLS > 0.1 → set explicit dimensions on all images and embeds
6. If DOM > 2500 → audit container nesting, remove empty wrappers
7. If page weight > 3 MB → audit images, remove unused widgets, check for redundant CSS
8. Enable WP Rocket optimization
9. Retest after caching
10. Document results
```

---

## 9. Responsive Standards

### Elementor Breakpoints

Elementor's breakpoints must be configured to align with the framework:

| Device | Elementor Setting | Breakpoint |
|---|---|---|
| Widescreen | Additional breakpoint (if enabled) | ≥ 1440px |
| Desktop | Default | ≥ 1025px |
| Tablet | Tablet breakpoint | ≤ 1024px |
| Mobile Landscape | Additional breakpoint (optional) | ≤ 880px |
| Mobile | Mobile breakpoint | ≤ 767px |

Configure at Elementor → Settings → Style → Breakpoints.

### Responsive Rules

| Rule | Detail |
|---|---|
| Preview every section at all breakpoints | Use Elementor's responsive preview for every section before publishing |
| Adjust typography per breakpoint | Headings must scale down on tablet and mobile — never leave desktop-sized headings on mobile |
| Adjust padding per breakpoint | Section padding reduces: 80px desktop → 50px tablet → 35px mobile |
| Stack columns on mobile | Multi-column containers must stack to full width on mobile |
| Verify reading order | When columns stack, verify the visual order matches logical reading order |
| Touch targets | Buttons minimum 44×44px on mobile |
| No horizontal overflow | Zero tolerance — test every page at 375px width |
| Mobile-specific visibility | Use Elementor's responsive visibility to hide desktop-only decorative elements on mobile |
| Mobile-specific content | Show mobile-specific CTAs (click-to-call) hidden on desktop |
| Image responsive sizing | Verify images do not overflow containers and scale correctly |

### Responsive Visibility

Elementor allows hiding elements per breakpoint. Use this for:

| Use Case | Implementation |
|---|---|
| Desktop-only decorative images | Hide on tablet and mobile |
| Mobile click-to-call button | Hide on desktop, show on tablet and mobile |
| Complex multi-column layouts | Show on desktop; replace with simplified layout on mobile |
| Large data tables | Show on desktop; replace with a card-based layout on mobile |

Never use responsive visibility to hide content from Google. Hidden content is still in the DOM and is still crawled.

---

## 10. Motion Effects

### Policy

Motion effects (entrance animations, scroll-triggered animations, parallax) are allowed only when they serve a functional purpose and do not degrade performance.

### Allowed Motion Effects

| Effect | When Acceptable | Performance Cost |
|---|---|---|
| Fade In | Section entrance on scroll — once per page load | Low — CSS animation only |
| Slide Up | Content revealing on scroll | Low — CSS animation only |
| Fade In Up | Combined fade and slide | Low |
| Parallax background | Hero section background image | Medium — requires JS scroll listener |
| Sticky elements | Sidebar CTA or navigation on long pages | Medium — requires JS position tracking |

### Prohibited Motion Effects

| Effect | Why |
|---|---|
| Rotating elements | Distracting, unprofessional for service businesses |
| Bouncing elements | Same — inappropriate for the agency's client verticals |
| Complex multi-step sequences | Heavy JS overhead, distract from conversion |
| Parallax on every section | Cumulative performance degradation |
| Infinite animations | CPU drain, accessibility issue |
| Zoom animations on scroll | Often causes CLS and jank |

### Motion Effect Rules

| Rule | Detail |
|---|---|
| Maximum animated elements per page | 5 — not every section needs an animation |
| Animation duration | 300–600ms — fast enough to feel responsive, not slow enough to feel sluggish |
| Animation delay | 0–200ms — stagger only when a sequence is intentional |
| Disable on mobile | Consider disabling animations on mobile to improve performance |
| Respect `prefers-reduced-motion` | Add custom CSS to disable animations for users who request it |
| Never animate LCP element | The hero image or heading that is the LCP candidate must render immediately |

### Reduced Motion Support

Add to the child theme `assets/css/custom.css`:

```css
@media ( prefers-reduced-motion: reduce ) {
	.elementor-element {
		animation: none !important;
		transition: none !important;
	}

	.elementor-motion-effects-element {
		transform: none !important;
	}
}
```

---

## 11. CSS Strategy

### Where Elementor CSS Lives

| Location | What Goes Here |
|---|---|
| Elementor Global Styles | Global colors and fonts that mirror Kadence palette |
| Widget-level styling | Per-widget styling using Elementor's Style tab |
| Custom CSS field (per widget) | Small overrides specific to one widget instance |
| Custom CSS field (per page) | Page-level overrides in Elementor → Page Settings → Advanced → Custom CSS |
| Child theme CSS | Shared custom styles, BEM components, framework-level overrides |

### CSS Rules for Elementor

| Rule | Detail |
|---|---|
| Use Global Colors | Select from Elementor global colors in every color picker — never type hex values |
| Minimize Custom CSS fields | Use Elementor's visual controls first; Custom CSS only for what the controls cannot achieve |
| Prefix custom classes | Add custom CSS classes with `waif-` prefix in the Advanced tab |
| Never use inline styles via HTML widget | Write CSS in the Custom CSS field or child theme — never in `<style>` tags |
| Target with Elementor selectors | Use `.elementor-element-{id}` selectors in Custom CSS — these are stable |
| Avoid `!important` | If specificity battles occur, increase selector specificity rather than using `!important` |

### Custom CSS Class Naming

When adding custom CSS classes to Elementor widgets (Advanced → CSS Classes):

```
waif-{section}-{element}
```

| Example | Purpose |
|---|---|
| `waif-hero-container` | Hero section outer container |
| `waif-service-card` | Service info box card |
| `waif-cta-primary` | Primary CTA button |
| `waif-faq-section` | FAQ accordion section |
| `waif-location-hero` | Location page hero |

---

## 12. JavaScript Strategy

### Rules

| Rule | Detail |
|---|---|
| No inline JavaScript | Never use HTML widgets to add `<script>` tags — use child theme `enqueue.php` or Kadence Elements |
| Defer all custom scripts | Load via `wp_enqueue_script` with `strategy: 'defer'` and `in_footer: true` |
| Minimize third-party scripts | Every external script (chat widgets, analytics, pixels) is loaded delayed via WP Rocket |
| No jQuery in new code | Use vanilla JavaScript; Elementor's frontend does not require jQuery |
| Conditional loading | If a script is only needed on Elementor pages, enqueue conditionally |

### Conditional Enqueuing

```php
add_action( 'wp_enqueue_scripts', 'waif_enqueue_elementor_scripts' );

function waif_enqueue_elementor_scripts(): void {
	if ( ! waif_is_elementor_page() ) {
		return;
	}

	wp_enqueue_script(
		'waif-elementor-custom',
		WAIF_CHILD_URL . '/assets/js/elementor-custom.js',
		[],
		WAIF_CHILD_VERSION,
		[ 'in_footer' => true, 'strategy' => 'defer' ],
	);
}

function waif_is_elementor_page(): bool {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
	return $document && $document->is_built_with_elementor();
}
```

---

## 13. Naming Standards

### Widget and Container Labels

Every container and important widget must be labeled in the Navigator panel:

| Element | Naming Convention | Example |
|---|---|---|
| Outer section container | `Section: {purpose}` | `Section: Hero` |
| Inner row container | `Row: {purpose}` | `Row: Service Cards` |
| Column container | `Col: {purpose}` | `Col: Left Content` |
| Heading widget | `H{n}: {text summary}` | `H2: How It Works` |
| Button widget | `CTA: {action}` | `CTA: Get Instant Offer` |
| Image widget | `Img: {description}` | `Img: Hero Background` |
| Form widget | `Form: {purpose}` | `Form: Quote Request` |
| Accordion widget | `FAQ: {topic}` | `FAQ: Service Questions` |
| Global Widget | `Global: {purpose}` | `Global: Mobile Sticky CTA` |

### Navigator Organization

The Elementor Navigator panel must be readable at a glance. If someone opens the Navigator and sees "Container, Container, Container, Heading, Heading, Button" — the labeling standard is not followed.

| Rule | Detail |
|---|---|
| Label every container | No unnamed containers |
| Label key widgets | Headings, buttons, forms, and images — at minimum |
| Collapse deep structures | Use Navigator folders to keep the tree manageable |
| Consistent naming | Follow the conventions above across all pages and projects |

---

## 14. Accessibility

### Elementor Accessibility Rules

| Requirement | Implementation |
|---|---|
| Heading hierarchy | H1 → H2 → H3 in logical order — configure via Heading widget HTML tag, not font size |
| Image alt text | Every Image widget must have alt text populated |
| Button links | Every button has descriptive text — no "Click Here" or empty buttons |
| Form labels | Kadence Forms or Elementor Forms must have visible labels associated with inputs |
| Focus styles | Do not remove focus outlines on interactive elements |
| Color contrast | Verify all text/background combinations against WCAG AA (4.5:1) |
| Link text | Links within Text Editor widgets must be descriptive — not "read more" or raw URLs |
| ARIA attributes | Use Custom Attributes (Advanced tab) to add `aria-label` where visible text is insufficient |
| Keyboard navigation | Tabs, accordions, and forms must be fully navigable via keyboard |
| Reduced motion | Custom CSS to respect `prefers-reduced-motion` (see Motion Effects section) |
| Skip link | Kadence theme provides skip-to-content — verify it targets `#primary` or the main content container |

### Heading vs. Visual Size

Elementor allows setting the heading HTML tag independently from its visual size. This is critical for accessibility and SEO:

```
Correct:
  HTML Tag: H2
  Size: 30px (styled to look appropriate)

Wrong:
  HTML Tag: H3 (because H2 "looks too big" at default size)
  Size: 30px (overridden to look like H2)
```

Set the correct semantic heading level first. Then adjust the visual size to match the design. Never choose a heading level for its default appearance.

---

## 15. AI Rules for Elementor

### Before Any Elementor Task

1. Verify Elementor is justified for this page (check Section 2 — When Elementor Is Allowed)
2. If the layout can be built with Kadence — use Kadence instead
3. Read this document in full before generating Elementor configurations
4. Read `KADENCE_STANDARDS.md` to understand the primary build system
5. Check existing templates in the template library before building new sections

### Mandatory Rules for AI Assistants

| Rule | Detail |
|---|---|
| Never recommend Elementor as the default | Always attempt Kadence first — Elementor is the fallback |
| Never mix builders on one page | A page is Kadence Blocks or Elementor — never both |
| Always use Flexbox Containers | Never use legacy Sections and Columns |
| Always use Global Colors | Never hardcode hex values — select from global color palette |
| Always disable Elementor Google Fonts | Fonts are managed by the theme |
| Always check responsive rendering | Verify desktop, tablet, and mobile before publishing |
| Always label elements in Navigator | Follow the naming conventions in Section 13 |
| Never nest containers beyond 3 levels | Outer section → inner row → columns — maximum |
| Never use Spacer widgets for layout | Use container padding and widget margins |
| Never add inline scripts via HTML widget | Use child theme `enqueue.php` or Kadence Elements |
| Always document Elementor usage | Record justification in `ai/decisions/` |
| Always export templates | Save reusable sections and page templates to the library |
| Always dequeue Elementor on non-Elementor pages | Verify the dequeue filter is in the child theme |
| Never build headers or footers in Elementor | Kadence header/footer builder is the standard |
| Always set image dimensions | Width and height on every Image widget to prevent CLS |

### AI Template Generation

When AI generates an Elementor page structure, provide it as a documented blueprint:

```
Page: /service-area/camden-nj/
Builder: Elementor
Template: Page - Location Template

Structure:
├── Section: Hero
│   ├── H1: Junk Car Buyers in Camden NJ
│   ├── Text: Location-specific intro paragraph
│   └── CTA: Get Instant Offer (link: peddle.com offer URL, new tab)
│
├── Section: Local Content
│   ├── H2: Junk Car Removal in Camden
│   └── Text: 300-400 words unique local content
│
├── Section: How It Works
│   ├── H2: How It Works
│   └── 3x Icon Boxes: Step 1, Step 2, Step 3
│
├── Section: Testimonials
│   └── Testimonial Slider: 3 reviews
│
├── Section: FAQ
│   ├── H2: Camden Junk Car FAQs
│   └── Accordion: 5 questions with FAQPage schema
│
├── Section: Also Serving
│   ├── H2: Also Serving Nearby Areas
│   └── Internal links to adjacent location pages
│
└── Section: CTA Band
    ├── H2: Ready to Sell Your Junk Car?
    ├── Phone: clickable tel: link
    └── CTA: Get Instant Offer
```

---

## 16. QA Checklist

### Pre-Publish — Every Elementor Page

**Architecture**

- [ ] Elementor usage is justified and documented in `ai/decisions/`
- [ ] Builders are not mixed on this page (100% Elementor or 100% Kadence)
- [ ] Only Flexbox Containers used — no legacy Sections/Columns
- [ ] Container nesting ≤ 3 levels deep
- [ ] All containers and key widgets labeled in Navigator

**Design Consistency**

- [ ] All colors selected from Global Colors (no hardcoded hex)
- [ ] Typography matches Kadence global settings (fonts not loaded by Elementor)
- [ ] Button styles match global button configuration
- [ ] Section padding follows the spacing system (80/50/35 responsive scale)
- [ ] Layout is consistent with other pages on the site

**Responsive**

- [ ] Page renders correctly at desktop (1440px)
- [ ] Page renders correctly at tablet (1024px)
- [ ] Page renders correctly at mobile (375px)
- [ ] No horizontal scrolling at any viewport
- [ ] Columns stack correctly on mobile — reading order verified
- [ ] Touch targets ≥ 44×44px on mobile
- [ ] Font sizes scale appropriately per breakpoint
- [ ] Mobile-specific CTAs visible (click-to-call, sticky bar)

**Performance**

- [ ] LCP ≤ 2.5s on mobile
- [ ] CLS ≤ 0.1
- [ ] DOM elements ≤ 2500
- [ ] Total page weight ≤ 3 MB
- [ ] Hero image is not lazy-loaded
- [ ] All below-fold images are lazy-loaded
- [ ] All images have explicit width and height
- [ ] No unused widgets loading assets
- [ ] Elementor Google Fonts disabled
- [ ] Elementor assets dequeued on non-Elementor pages

**Accessibility**

- [ ] One H1, logical heading hierarchy
- [ ] All images have alt text
- [ ] All buttons have descriptive text
- [ ] Color contrast meets WCAG AA
- [ ] Forms have visible labels
- [ ] Keyboard navigation works for interactive elements
- [ ] `prefers-reduced-motion` CSS present

**SEO**

- [ ] Schema markup present and validated
- [ ] Internal links present (≥ 3)
- [ ] CTA buttons use correct text and link (per `PROJECT_CONTEXT.md`)
- [ ] Title tag and meta description set via Rank Math
- [ ] Canonical tag present

**Templates**

- [ ] Reusable sections saved to template library
- [ ] Template exports saved to `templates/elementor/`
- [ ] Global Widgets used for shared content (NAP, CTAs)

---

## 17. Common Mistakes

| Mistake | Why It Happens | Correct Approach |
|---|---|---|
| Using Elementor for a simple page that Kadence handles | Habit or comfort with Elementor's visual editor | Follow the decision hierarchy — Kadence first |
| Building headers and footers in Elementor | Previous workflow without Kadence | Kadence header/footer builder — always |
| Using legacy Sections and Columns | Old projects or outdated tutorials | Flexbox Containers only — convert legacy layouts |
| Nesting containers 4–5 levels deep | Attempting complex overlapping layouts | Flatten structure to ≤ 3 levels; redesign if needed |
| Hardcoding colors instead of using Global Colors | Faster to type hex than select from palette | Always use Global Colors — consistency and maintainability |
| Letting Elementor load Google Fonts | Default behavior not disabled | Add the `__return_false` filter in child theme |
| Not labeling elements in Navigator | Seems unnecessary during building | Label everything — future editors (including AI) need readable structure |
| Using Spacer widgets for vertical spacing | Quick visual fix | Container padding or widget margin |
| Adding `<style>` and `<script>` in HTML widgets | Quick prototyping | Child theme CSS/JS files, properly enqueued |
| Not checking tablet viewport | Desktop and mobile tested, tablet skipped | Always check all three breakpoints |
| Leaving Elementor assets loading site-wide | Not implementing dequeue filter | Add conditional dequeue in child theme `inc/hooks.php` |
| Mixing Kadence Blocks and Elementor on one page | Attempting to use "best of both" | One builder per page — choose based on the layout needs |
| Using motion effects on every section | "It looks more dynamic" | Maximum 5 animated elements per page; zero on LCP element |
| Not saving reusable sections as templates | Building from scratch every time | Save and reuse — template library is a core efficiency tool |
| Using Elementor for blog posts | Editor familiarity | Blog posts use the block editor with Kadence Blocks — always |
| Not exporting Elementor settings | Settings only in database | Export Global Colors, Global Fonts, and templates for version control |
| Ignoring Elementor DOM count | "It renders fine on my machine" | Check DOM count — Elementor pages over 3000 nodes need restructuring |

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — complete Elementor usage standards defined |