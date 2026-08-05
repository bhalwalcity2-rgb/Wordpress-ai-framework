# tech-stack.md

> **This file is machine context, not human documentation.**
> Every AI assistant must internalize this file before performing any task. It defines the official, locked technology stack for the WordPress AI Framework. Do not suggest, introduce, or evaluate alternatives unless explicitly asked.

---

## Purpose

This file exists so that any AI assistant — Claude, ChatGPT, Codex, Gemini, Cursor — can instantly resolve questions about which technologies are approved, what role each serves, and what is prohibited. It eliminates ambiguity. If a technology is not listed here, it is not approved.

---

## Core Platform

| Technology | Purpose | Status | Notes |
|---|---|---|---|
| WordPress | Content management system | Required | Never modify core files. Minimum version 6.4. PHP 8.0+. |
| Kadence Theme | Base theme — layout, header/footer, global design system | Required | Always use with a child theme. Never edit parent theme files. |
| Kadence Blocks Pro | Block editor page building — rows, headings, buttons, accordions, forms | Required | Default builder. Disable unused blocks per project. |
| Elementor | Visual page builder for advanced layouts | Conditional | Used only when Kadence cannot achieve the design. One builder per page — never mix. |

**Builder Decision Rule:** Kadence first. Elementor only when Kadence is documented as insufficient for the specific layout.

---

## SEO Stack

| Technology | Purpose | Status | Notes |
|---|---|---|---|
| Rank Math Pro | On-page SEO, schema, sitemaps, redirects, breadcrumbs, analytics | Required | Only SEO plugin. Never install Yoast, AIOSEO, or SEOPress alongside. |
| Instant Indexing | Google Indexing API — rapid page discovery | Required | Auto-submit on publish/update. 200 requests/day limit. |
| Google Search Console | Index monitoring, crawl errors, search performance | Required | Connected via Rank Math. Reviewed weekly. |
| Google Analytics 4 | Traffic, conversions, user behavior | Required | Installed via Rank Math or tag manager. Never inline. |

**SEO Rule:** Rank Math owns all SEO functions — schema, sitemaps, redirects, breadcrumbs. No secondary SEO plugins.

---

## Performance Stack

| Technology | Purpose | Status | Notes |
|---|---|---|---|
| WP Rocket | Page cache, CSS/JS optimization, lazy loading, database cleanup | Required | Only caching/optimization plugin. Never install Autoptimize, LiteSpeed, or W3TC. |
| Cloudflare | CDN, DDoS protection, edge caching | Recommended | Configure per project. Not required for all sites. |
| Image Optimization | WebP conversion, compression, responsive sizing | Required (process) | Use ShortPixel, Imagify, or manual optimization. One tool only. Compress before upload. |

**Performance Targets:**

| Metric | Target | Maximum |
|---|---|---|
| LCP | ≤ 1.5s | ≤ 2.5s |
| INP | ≤ 100ms | ≤ 200ms |
| CLS | ≤ 0.05 | ≤ 0.1 |
| Total page weight | ≤ 1.5 MB | ≤ 3.0 MB |
| PageSpeed mobile | ≥ 90 | ≥ 80 |

---

## Development Stack

| Technology | Purpose | Status | Notes |
|---|---|---|---|
| Git | Version control | Required | Every change committed. Meaningful messages. |
| GitHub | Repository hosting, PRs, code review | Required | Private repositories. Protected main branch. |
| GitHub Actions | CI/CD — automated deployment, validation | Required | Staging auto-deploy on merge. Production on tag. |
| SSH | Secure server deployment | Required | Ed25519 keys. Dedicated deploy user. Never root. |
| VS Code / Cursor | Code editor / AI-assisted IDE | Recommended | Local development environment. |
| WP-CLI | WordPress command-line management | Recommended | Cache flush, database operations, post-deploy tasks. |

---

## AI Stack

| Assistant | Primary Responsibilities |
|---|---|
| **Claude** | Architecture, documentation, code review, planning, standards enforcement |
| **ChatGPT** | SEO strategy, content generation, technical planning, brainstorming |
| **Codex** | Code generation, refactoring, automation scripts, testing |
| **Gemini** | Research, competitive analysis, fact verification, data gathering |
| **Cursor** | Real-time IDE development, inline code completion, live assistance |

**Collaboration Rule:** Assistants complement each other. No assistant replaces another. All read from the same `ai/` context directory.

---

## Programming Languages

| Language | Usage |
|---|---|
| PHP | WordPress theme functions, plugins, MU plugins, hooks, filters, REST API |
| HTML | Semantic markup, templates, template parts |
| CSS | Styling via BEM methodology, CSS variables, mobile-first responsive |
| JavaScript | ES6+ vanilla JS, async/await, Fetch API, DOM manipulation. No jQuery in new code. |
| JSON | Schema markup (JSON-LD), configuration exports, plugin manifests, package files |
| Markdown | Documentation, README files, changelogs, decision records |
| YAML | GitHub Actions workflows |
| Bash | Deployment scripts, automation, WP-CLI commands, server maintenance |
| SQL | Database queries via `$wpdb->prepare()` only. Never raw concatenation. |

---

## Project Rules

- Never replace an approved technology without documented justification in `ai/decisions/`.
- Prefer Kadence over Elementor for every layout decision.
- Always use a Kadence child theme. Never edit the parent theme.
- Never modify WordPress core files (`wp-admin/`, `wp-includes/`).
- Never modify third-party plugin files.
- Extend using child theme, custom plugins, or MU plugins only.
- One SEO plugin (Rank Math). One caching plugin (WP Rocket). One builder per page.
- All deployments through Git → GitHub → GitHub Actions → SSH. Never FTP.
- No secrets in the repository. Credentials in environment variables.
- Prefix all custom code: `waif_` for functions, `.waif-` for CSS classes.
- Maximum 15 active plugins per project.
- Maximum 2 font families per project.
- Follow `CODING_STANDARDS.md` for all code. Follow `SEO_STANDARDS.md` for all pages.

---

## AI Reminder

Before performing any task in this repository, read these files in order:

- [ ] `PROJECT_CONTEXT.md` — architectural context and agency background
- [ ] `AI_RULES.md` — behavioral rules for AI assistants
- [ ] `WORDPRESS_STANDARDS.md` — WordPress development standards
- [ ] `CODING_STANDARDS.md` — code quality and security standards
- [ ] `SEO_STANDARDS.md` — SEO methodology (for any page-related work)
- [ ] `KADENCE_STANDARDS.md` — Kadence usage rules (for any theme/layout work)
- [ ] `ELEMENTOR_STANDARDS.md` — Elementor rules (only if Elementor is involved)
- [ ] `PLUGIN_STANDARDS.md` — plugin decisions (for any plugin-related work)
- [ ] `DEPLOYMENT_STANDARDS.md` — deployment rules (for any Git/release work)
- [ ] `tech-stack.md` — this file (quick reference)

**If this file conflicts with a detailed standards document, the detailed document takes precedence.**