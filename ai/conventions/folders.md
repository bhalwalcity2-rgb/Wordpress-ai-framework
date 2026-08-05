# folders.md

> **This file is machine context, not human documentation.**
> Every AI assistant must consult this file before creating, moving, or organizing any file. If a folder is not listed here, it does not exist in the framework. Do not create new top-level directories without documented approval.

---

## Purpose

This file maps every directory in the framework to its responsibility, contents, and rules. It answers: *"Where does this file go?"* — eliminating misplaced files, duplicate structures, and ad-hoc folder creation.

---

## Root Structure

```
wordpress-ai-framework/
├── ai/                   # AI assistant context
├── docs/                 # Standards and documentation
├── templates/            # Reusable components
├── scripts/              # Automation utilities
├── wordpress/            # WordPress configuration
├── .github/              # CI/CD and repository config
├── PROJECT_CONTEXT.md    # AI permanent memory (root)
├── README.md             # Human-facing overview (root)
├── CHANGELOG.md          # Release history (root)
├── CONTRIBUTING.md       # Contribution rules (root)
└── VERSION               # Current version number (root)
```

---

## Directory Definitions

### `ai/` — AI Context Layer

| Subdirectory | Contents |
|---|---|
| `ai/conventions/` | Coding rules, naming patterns, file organization standards |
| `ai/decisions/` | Architectural decision records (one file per decision) |
| `ai/` (root) | Quick-reference files: `tech-stack.md`, `approved-plugins.md`, `workflow.md`, `architecture.md`, `naming.md`, `folders.md` |

| Rule | Detail |
|---|---|
| Who writes here | AI assistants and architects |
| File format | Markdown only |
| Naming | `lowercase-hyphenated.md` |
| New files allowed | Yes — conventions and decisions can be added as needed |
| Git tracked | Yes |

**Decision records format:** `ai/decisions/ADR-{NNNN}.md` (sequential, zero-padded to 4 digits, never reused)
Example: `ai/decisions/ADR-0001.md`, `ai/decisions/ADR-0002.md`

---

### `docs/` — Standards and Documentation

| Subdirectory | Contents |
|---|---|
| `docs/` (root) | Major standards documents (`WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md`, `SEO_STANDARDS.md`, etc.) |
| `docs/architecture/` | System design guides, component diagrams |
| `docs/development/` | Development workflow guides, setup instructions |
| `docs/deployment/` | Deployment procedures, environment configuration |
| `docs/seo/` | SEO methodology guides, schema references |
| `docs/plugins/` | Per-plugin configuration notes and customization hooks |

| Rule | Detail |
|---|---|
| Who writes here | AI assistants, architects, developers |
| File format | Markdown only |
| Naming | `UPPER_SNAKE_CASE.md` for standards; `lowercase-hyphenated.md` for guides |
| New files allowed | Yes — new standards and guides as the framework grows |
| Git tracked | Yes |

---

### `templates/` — Reusable Components

| Subdirectory | Contents |
|---|---|
| `templates/pages/` | Full page templates (service page, location page, landing page) |
| `templates/sections/` | Reusable section patterns (hero, CTA band, FAQ, testimonials) |
| `templates/schema/` | JSON-LD schema templates (LocalBusiness, FAQPage, Service, Article) |
| `templates/config/` | Plugin and theme configuration exports (Rank Math, WP Rocket, Kadence settings) |
| `templates/elementor/` | Elementor saved templates and kit exports |

| Rule | Detail |
|---|---|
| Who writes here | Developers, AI assistants building reusable patterns |
| File format | PHP (templates), JSON (schema, config), ZIP (Elementor kits) |
| Naming | `lowercase-hyphenated` matching the component name |
| New files allowed | Yes — new templates encouraged when patterns repeat across 3+ pages |
| Git tracked | Yes |

---

### `scripts/` — Automation Utilities

| Subdirectory | Contents |
|---|---|
| `scripts/setup/` | Project scaffolding, environment initialization |
| `scripts/deploy/` | Deployment helpers, cache purge scripts |
| `scripts/maintenance/` | Backup routines, database cleanup, update checks |

| Rule | Detail |
|---|---|
| Who writes here | DevOps, AI assistants building automation |
| File format | Bash (`.sh`), PHP, Node.js |
| Naming | `lowercase-hyphenated.sh` |
| New files allowed | Yes — automation scripts reduce manual work |
| Git tracked | Yes |
| Executable | Scripts must have `chmod +x` and a shebang line (`#!/bin/bash`) |

---

### `wordpress/` — WordPress Configuration

| Subdirectory | Contents |
|---|---|
| `wordpress/config/` | `wp-config.php` templates, environment-specific overrides |
| `wordpress/security/` | `.htaccess` rules, file permission scripts, hardening guides |
| `wordpress/mu-plugins/` | Must-Use plugins (`waif-security.php`, `waif-performance.php`, `waif-cleanup.php`) |
| `wordpress/child-theme/` | Kadence child theme base (starter files for new projects) |

| Rule | Detail |
|---|---|
| Who writes here | Architects, developers |
| File format | PHP, Apache config, shell scripts |
| New files allowed | Yes — with documented justification for new MU plugins or config variants |
| Git tracked | Yes (except secrets — `wp-config.php` with live credentials is never committed) |

---

### `.github/` — CI/CD and Repository Configuration

| Subdirectory | Contents |
|---|---|
| `.github/workflows/` | GitHub Actions YAML files (`deploy-staging.yml`, `deploy-production.yml`, `validate.yml`) |
| `.github/` (root) | PR templates, issue templates, CODEOWNERS |

| Rule | Detail |
|---|---|
| Who writes here | DevOps, architects |
| File format | YAML, Markdown |
| New files allowed | With approval — workflow changes affect the entire pipeline |
| Git tracked | Yes |

---

## File Placement Decision Table

| You need to create... | Put it in... |
|---|---|
| A coding or naming convention | `ai/conventions/` |
| An architectural decision record | `ai/decisions/` |
| A quick-reference for AI assistants | `ai/` (root) |
| A standards document | `docs/` |
| A development or deployment guide | `docs/development/` or `docs/deployment/` |
| A reusable page or section template | `templates/pages/` or `templates/sections/` |
| A JSON-LD schema template | `templates/schema/` |
| A plugin configuration export | `templates/config/` |
| An Elementor template export | `templates/elementor/` |
| A setup or deployment script | `scripts/setup/` or `scripts/deploy/` |
| A maintenance automation | `scripts/maintenance/` |
| A MU plugin | `wordpress/mu-plugins/` |
| A child theme file | `wordpress/child-theme/` |
| A wp-config template | `wordpress/config/` |
| A GitHub Actions workflow | `.github/workflows/` |

---

## Folders AI Must Never Create Without Approval

| Action | Requires Approval |
|---|---|
| New top-level directory (sibling to `ai/`, `docs/`, etc.) | Yes — document in `ai/decisions/` first |
| New subdirectory inside `wordpress/` | Yes — structural change to WordPress layer |
| New workflow in `.github/workflows/` | Yes — affects deployment pipeline |
| New subdirectory inside `ai/` or `docs/` | No — extend as needed following naming conventions |
| New subdirectory inside `templates/` | No — new template categories are expected |
| New subdirectory inside `scripts/` | No — new automation categories are expected |

---

## AI Rules

- Check this file before creating any file or folder.
- Never create a top-level directory without a decision record.
- Never place files in the wrong directory — use the placement table.
- Never create duplicate structures that mirror existing directories.
- If unsure where a file belongs, default to `docs/` for documentation or `ai/conventions/` for rules.

**If this file conflicts with `architecture.md`, the more detailed document takes precedence.**