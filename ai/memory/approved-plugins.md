# approved-plugins.md

> **This file is machine context, not human documentation.**
> Every AI assistant must check this file before recommending, installing, or evaluating any WordPress plugin. If a plugin is not listed here, it is not approved. If an approved plugin already handles the function, no additional plugin is needed.

---

## Purpose

This file exists to give AI assistants an instant, authoritative answer to the question: *"Which plugins are approved, what does each one own, and what is not allowed?"*

It prevents the most common plugin mistake — installing something that duplicates functionality already provided by the approved stack. Detailed evaluation criteria and configuration standards are in `PLUGIN_STANDARDS.md`. This file is the quick-reference layer.

---

## Approved Core Plugins

| Plugin | Status | Purpose | Required |
|---|---|---|---|
| Kadence Blocks Pro | Core | Default page builder — block-based layouts, headings, buttons, accordions, forms, galleries | Yes — every project |
| Elementor | Conditional | Visual page builder for advanced layouts that Kadence Blocks cannot achieve | No — install only when documented as necessary |
| Rank Math Pro | Core | On-page SEO, schema markup, XML sitemaps, redirects, breadcrumbs, analytics integration, Local SEO | Yes — every project |
| WP Rocket | Core | Page caching, browser caching, CSS/JS minification, lazy loading, database cleanup | Yes — every project |
| Instant Indexing | Core | Google Indexing API integration — rapid page discovery on publish/update | Yes — every project |

**No substitutions.** These five plugins form the locked stack. Alternatives are not evaluated unless one of these is discontinued.

---

## Plugin Responsibilities

Each approved plugin owns specific functions. No other plugin may provide the same function.

### Kadence Blocks Pro — Owns Page Building (Default)

| Function | Kadence Blocks |
|---|---|
| Row/column layouts | ✅ Primary tool |
| Advanced headings | ✅ |
| Buttons and CTAs | ✅ |
| Accordions and tabs | ✅ |
| Icon lists | ✅ |
| Info/icon boxes | ✅ |
| Testimonials | ✅ |
| Image galleries | ✅ |
| Contact forms | ✅ |
| Table of contents | ✅ |
| Count-up statistics | ✅ |

### Elementor — Owns Advanced Layouts Only

| Function | Elementor |
|---|---|
| Complex overlapping layouts | ✅ Only when Kadence cannot deliver |
| Advanced animation sequences | ✅ Use sparingly |
| Dynamic content with ACF fields | ✅ Location page templates |
| Mega menus with rich content | ✅ When Kadence menu is insufficient |
| Headers and footers | ❌ Kadence header/footer builder handles this |
| Simple pages | ❌ Use Kadence |
| Blog posts | ❌ Use block editor with Kadence Blocks |

### Rank Math Pro — Owns All SEO Functions

| Function | Rank Math |
|---|---|
| Title tags and meta descriptions | ✅ |
| Schema markup (JSON-LD) | ✅ |
| XML sitemaps | ✅ |
| 301/302 redirects | ✅ |
| Breadcrumbs | ✅ |
| Open Graph and Twitter Cards | ✅ |
| Local SEO module | ✅ |
| SEO analysis and scoring | ✅ |
| Google Search Console integration | ✅ |
| Google Analytics integration | ✅ |
| 404 monitoring | ✅ |
| Internal link suggestions | ✅ |

### WP Rocket — Owns All Performance Optimization

| Function | WP Rocket |
|---|---|
| Page caching | ✅ |
| Browser caching | ✅ |
| CSS minification | ✅ |
| CSS delivery (remove unused, async) | ✅ |
| JavaScript minification | ✅ |
| JavaScript defer and delay | ✅ |
| Lazy loading (images and iframes) | ✅ |
| Preloading (sitemap, links, fonts) | ✅ |
| Database cleanup | ✅ |
| Heartbeat control | ✅ |
| CDN integration | ✅ |
| GZIP compression | ✅ |

### Instant Indexing — Owns Indexing API

| Function | Instant Indexing |
|---|---|
| Google Indexing API submission | ✅ |
| Auto-submit on publish/update | ✅ |
| Bulk URL submission | ✅ |

---

## Plugin Rules

- Never install a plugin that duplicates a function owned by an approved plugin.
- Never install two plugins from the same category (two SEO plugins, two caching plugins, two builders on one page).
- Never modify plugin source files — use hooks, filters, or companion custom plugins.
- Always configure a plugin fully before use — default settings are rarely optimal.
- Remove (delete, not just deactivate) any plugin that is no longer in use.
- Keep all plugins updated — security updates within 24 hours, feature updates within 14 days.
- Maximum 15 active plugins per project. Audit and consolidate if exceeded.
- Prefer 30 lines of custom code in a MU plugin over installing a full plugin for a single function.
- Export and version-control plugin configurations in `templates/config/`.
- Never store license keys in the repository — use environment variables or wp-config.php.

---

## Plugins Not Recommended

These categories of plugins must not be installed alongside the approved stack:

| Category | Examples to Avoid | Reason |
|---|---|---|
| Alternative SEO plugins | Yoast, AIOSEO, SEOPress, Squirrly | Rank Math Pro handles all SEO functions |
| Alternative caching plugins | W3 Total Cache, LiteSpeed Cache, WP Super Cache | WP Rocket is the only caching solution |
| Additional optimization plugins | Autoptimize, Asset CleanUp, Fast Velocity | WP Rocket handles CSS/JS optimization |
| Additional lazy loading plugins | a3 Lazy Load, Smush lazy load | WP Rocket handles lazy loading |
| Alternative schema plugins | Schema Pro, WP Schema, Schema & Structured Data | Rank Math Pro handles all schema |
| Alternative sitemap plugins | Google XML Sitemaps, XML Sitemap Generator | Rank Math Pro generates sitemaps |
| Alternative redirect plugins | Redirection, Simple 301 Redirects, Safe Redirect Manager | Rank Math Pro manages redirects |
| Alternative breadcrumb plugins | Breadcrumb NavXT, Yoast breadcrumbs | Rank Math or Kadence handles breadcrumbs |
| Alternative page builders (simultaneous) | Beaver Builder, Divi, WPBakery, Brizy | Kadence Blocks + Elementor (conditional) is the stack |
| Heavy popup/modal plugins | OptinMonster, Popup Maker (evaluate carefully) | Significant performance cost — justify before installing |
| Social sharing button plugins | AddToAny, ShareThis, Social Warfare | High CSS/JS cost for minimal value — implement manually if needed |
| All-in-one "suite" plugins | Jetpack (full suite) | Overlaps with caching, SEO, security — install individual tools if specific Jetpack features are needed |

**Principle:** Every function has exactly one owner in the approved stack. A second tool for the same function creates conflicts, bloat, and maintenance burden.

---

## AI Decision Rules

Before recommending or installing any plugin not on the approved list, every AI assistant must verify all of the following:

| Check | Question |
|---|---|
| Duplication | Does an approved plugin already provide this function? If yes → stop. |
| Necessity | Can this be achieved with 30 lines of child theme or MU plugin code? If yes → write the code. |
| Maintenance | Is the plugin actively maintained (updated within 6 months, 10K+ installs)? |
| Compatibility | Is it tested with the current WordPress and PHP versions? |
| Performance | Will it add measurable page weight, HTTP requests, or database queries? |
| Security | Is it free of known unpatched vulnerabilities (check WPScan/Patchstack)? |
| Documentation | Has the decision been recorded in `ai/decisions/` with alternatives considered? |

If any check fails, do not recommend the plugin. Propose an alternative approach.

---

## Reminder

Before suggesting any plugin action:

1. ✅ Check this file — is the function already owned by an approved plugin?
2. ✅ Check `PLUGIN_STANDARDS.md` — does the plugin pass evaluation criteria?
3. ✅ Prefer existing tools — the approved stack covers SEO, caching, building, and indexing.
4. ✅ Document exceptions — any deviation recorded in `ai/decisions/` with justification.
5. ✅ Never install without staging test — no plugin goes to production untested.

**If this file conflicts with `PLUGIN_STANDARDS.md`, the detailed document takes precedence.**