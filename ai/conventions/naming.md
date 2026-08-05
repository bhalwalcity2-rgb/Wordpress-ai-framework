# naming.md

> **This file is machine context, not human documentation.**
> Every AI assistant must apply these naming conventions to all code, files, branches, and commits. Consistency across the framework is non-negotiable. If a convention exists here, follow it. Do not invent alternatives.

---

## Purpose

This file eliminates naming ambiguity. It defines the exact casing, prefix, and pattern for every nameable element in the framework — PHP, CSS, JavaScript, files, folders, Git branches, and commits. One document, one answer, zero guesswork.

---

## General Rules

| Rule | Detail |
|---|---|
| Project prefix | `waif` (WordPress AI Framework) — used on all custom code |
| Descriptive names | Names describe purpose, not implementation |
| No abbreviations | `get_service_areas()` not `get_sa()` — unless universally understood (`url`, `id`, `css`, `js`) |
| No generic names | `$data`, `$result`, `$temp` are prohibited — name what it holds |
| English only | All names, comments, and documentation in English |

---

## File Naming

| Type | Convention | Example |
|---|---|---|
| PHP (WordPress) | `lowercase-hyphenated.php` | `schema-generator.php` |
| PHP (class, WordPress) | `class-{name}.php` | `class-schema-generator.php` |
| PHP (class, PSR-4) | `PascalCase.php` | `SchemaGenerator.php` |
| PHP (MU plugin) | `waif-{purpose}.php` | `waif-security.php` |
| PHP (template) | `{template-name}.php` | `single-service.php` |
| PHP (template part) | `{part-name}.php` in `template-parts/` | `template-parts/hero-section.php` |
| CSS | `lowercase-hyphenated.css` | `hero-section.css` |
| JavaScript | `lowercase-hyphenated.js` | `mobile-menu.js` |
| Markdown | `UPPER-CASE.md` (standards) or `lowercase.md` (AI context) | `CODING_STANDARDS.md`, `workflow.md` |
| JSON | `lowercase-hyphenated.json` | `rank-math-settings.json` |
| YAML | `lowercase-hyphenated.yml` | `deploy-staging.yml` |

---

## Folder Naming

| Convention | Example |
|---|---|
| All lowercase, hyphen-separated | `template-parts/`, `mu-plugins/` |
| Plural for collections | `templates/`, `scripts/`, `assets/` |
| Singular for namespaced class dirs | `service/`, `contract/`, `model/` |
| Plugin folders | `waif-{plugin-name}/` |

---

## PHP Naming

| Element | Convention | Example |
|---|---|---|
| Functions | `waif_{descriptive_name}()` | `waif_get_service_areas()` |
| Classes | `PascalCase` | `SchemaGenerator`, `CacheDriver` |
| Methods | `snake_case` | `$this->get_location()` |
| Properties | `$snake_case` | `$this->site_name` |
| Variables | `$snake_case` | `$post_id`, `$is_active` |
| Booleans | `$is_`, `$has_`, `$can_`, `$should_` | `$is_published`, `$has_schema` |
| Constants | `WAIF_UPPER_SNAKE` | `WAIF_VERSION`, `WAIF_MIN_PHP` |
| Interfaces | `PascalCase` (noun/adjective) | `SchemaProvider`, `Renderable` |
| Traits | `PascalCase` with `Has`/`Can` | `HasMetaFields`, `CanBeCached` |
| Namespaces | `PascalCase` | `Waif\Services`, `Waif\Schema` |
| Hooks (custom) | `waif/{scope}/{action}` | `waif/schema/before_output` |
| Filters (custom) | `waif/{scope}/{filter}` | `waif/seo/title_separator` |
| Options (db) | `waif_{option}` | `waif_schema_default_type` |
| Transients | `waif_{transient}` | `waif_schema_cache_home` |
| Database tables | `{$wpdb->prefix}waif_{table}` | `wp_waif_schema_log` |
| Text domain | `waif-{plugin-name}` | `waif-custom-schema` |
| Script/style handles | `waif-{name}` | `waif-custom`, `waif-mobile-menu` |

---

## CSS Naming

BEM methodology with `waif-` prefix:

```
.waif-{block}
.waif-{block}__{element}
.waif-{block}--{modifier}
```

| Component | Convention | Example |
|---|---|---|
| Block | `.waif-{name}` | `.waif-hero` |
| Element | `.waif-{block}__{element}` | `.waif-hero__title` |
| Modifier | `.waif-{block}--{modifier}` | `.waif-hero--dark` |
| Element + Modifier | `.waif-{block}__{element}--{modifier}` | `.waif-hero__cta--primary` |
| CSS variables | `--waif-{category}-{name}` | `--waif-color-primary` |
| Utility classes | `.waif-u-{utility}` | `.waif-u-visually-hidden` |
| State classes | `.is-{state}` | `.is-open`, `.is-active` |

---

## JavaScript Naming

| Element | Convention | Example |
|---|---|---|
| Functions | `camelCase` | `initMobileMenu()` |
| Variables | `camelCase` with `const`/`let` | `const menuToggle` |
| Booleans | `is`, `has`, `can` prefix | `const isExpanded` |
| Constants | `UPPER_SNAKE_CASE` | `const MAX_RETRIES = 3` |
| Classes | `PascalCase` | `class FormValidator {}` |
| Modules | `camelCase` filename, named exports | `export function initMenu()` |
| DOM references | `camelCase`, suffix with `El` or `Btn` | `const submitBtn`, `const heroEl` |
| Event handlers | `handle` + `Event` | `handleClick()`, `handleSubmit()` |

---

## Git Branch Naming

```
{type}/{short-descriptive-name}
```

| Type | Purpose | Example |
|---|---|---|
| `feature/` | New feature, page, component | `feature/add-camden-location` |
| `fix/` | Bug fix | `fix/mobile-menu-overlap` |
| `hotfix/` | Urgent production fix | `hotfix/ssl-mixed-content` |
| `docs/` | Documentation only | `docs/update-seo-standards` |
| `refactor/` | Code restructuring | `refactor/extract-schema-helper` |
| `seo/` | SEO changes | `seo/faq-schema-service-pages` |
| `perf/` | Performance improvement | `perf/defer-elementor-assets` |
| `chore/` | Maintenance | `chore/update-wp-rocket` |

Rules: lowercase, hyphen-separated, ≤ 50 characters, descriptive.

---

## Commit Prefixes

```
{type}: Short description in imperative mood
```

| Prefix | Purpose | Example |
|---|---|---|
| `feat` | New feature | `feat: Add Camden location page` |
| `fix` | Bug fix | `fix: Correct header CTA link target` |
| `docs` | Documentation | `docs: Update SEO standards for FAQ` |
| `perf` | Performance | `perf: Defer Elementor JS on non-builder pages` |
| `seo` | SEO change | `seo: Add FAQPage schema to service pages` |
| `refactor` | Restructure | `refactor: Extract phone formatter to helper` |
| `style` | Formatting only | `style: Fix indentation in schema.php` |
| `security` | Security hardening | `security: Disable REST API user enumeration` |
| `test` | Tests | `test: Add schema output validation` |
| `chore` | Maintenance | `chore: Update WP Rocket to 3.16.1` |

Rules: imperative mood, ≤ 72 characters, no period, capitalize first word after prefix.

---

## AI Rules

- Never invent a naming convention that contradicts this document.
- Check this file before naming any function, class, file, branch, or CSS class.
- If a pattern exists here, use it exactly — do not create "improved" variants.
- If a situation is not covered, propose a convention following the existing patterns and document it in `ai/conventions/`.
- Consistency is more important than any individual preference.

**If this file conflicts with `CODING_STANDARDS.md`, the more detailed document takes precedence.**