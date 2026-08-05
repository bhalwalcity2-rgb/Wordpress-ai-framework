# architecture.md

> **This file is machine context, not human documentation.**
> Every AI assistant must read this file to understand how the framework's components relate to each other before making any architectural decision. It defines the layers, dependencies, and boundaries of the system.

---

## Purpose

This file gives AI assistants a structural map of the entire framework. It answers: *"What are the components, how do they connect, and what are the boundaries?"* Without this context, AI assistants make isolated decisions that conflict with the larger system.

---

## Architecture Overview

The framework is a five-layer stack. Each layer depends on the layer below it.

```
┌─────────────────────────────────────────────┐
│              AI Layer                        │
│   Claude · ChatGPT · Codex · Gemini · Cursor │
│   Context files, conventions, decisions      │
├─────────────────────────────────────────────┤
│           Framework Standards                │
│   CODING · SEO · WORDPRESS · PLUGIN          │
│   KADENCE · ELEMENTOR · DEPLOYMENT           │
├─────────────────────────────────────────────┤
│           Templates & Components             │
│   Page templates · Section templates         │
│   Schema templates · Config exports          │
├─────────────────────────────────────────────┤
│            WordPress Layer                   │
│   Core · Kadence · Child Theme · Plugins     │
│   MU Plugins · Custom Plugins                │
├─────────────────────────────────────────────┤
│           Deployment Layer                   │
│   Git · GitHub · GitHub Actions · SSH        │
│   Staging · Production · Backups             │
└─────────────────────────────────────────────┘
```

| Layer | Responsibility |
|---|---|
| **AI Layer** | Provides context, conventions, and decision records so AI assistants produce consistent output |
| **Framework Standards** | Defines rules for code, SEO, themes, plugins, and deployment — the rulebook |
| **Templates & Components** | Reusable building blocks — page structures, section patterns, schema templates, config exports |
| **WordPress Layer** | The runtime platform — WordPress core, theme, plugins, and custom code |
| **Deployment Layer** | Moves code from repository to server — Git flow, CI/CD, environments, rollback |

**Direction of dependency:** Upper layers depend on lower layers. Lower layers never reference upper layers. WordPress does not know about the AI layer. Templates do not depend on deployment configuration.

---

## Core Components

| Component | Responsibility | Location | Depends On |
|---|---|---|---|
| **AI Context** | Provides conventions, decisions, and rules for AI assistants | `ai/` | Framework Standards |
| **Documentation** | Defines all standards, processes, and architectural decisions | `docs/` | None (foundational) |
| **Templates** | Reusable page layouts, sections, schema patterns, plugin configs | `templates/` | WordPress Layer |
| **Scripts** | Automation for setup, deployment, maintenance | `scripts/` | Deployment Layer |
| **WordPress** | Core configuration, child theme, MU plugins, security | `wordpress/` | WordPress Core |
| **GitHub Config** | CI/CD workflows, PR templates, branch protection | `.github/` | Git, SSH |
| **Deployment** | Pipeline from commit to production | `.github/workflows/` | GitHub, SSH, server |

---

## Repository Architecture

```
wordpress-ai-framework/
├── ai/                    # AI assistant context
│   ├── conventions/       # Coding and naming rules
│   ├── decisions/         # Architectural decision records
│   └── *.md              # Quick-reference files (tech-stack, approved-plugins, workflow)
├── docs/                  # Standards documents
│   ├── architecture/      # System design guides
│   ├── development/       # Development workflow guides
│   ├── deployment/        # Deployment procedures
│   └── seo/              # SEO methodology
├── templates/             # Reusable components
│   ├── pages/            # Full page templates
│   ├── sections/         # Section patterns (hero, CTA, FAQ)
│   ├── schema/           # JSON-LD schema templates
│   ├── config/           # Plugin/theme configuration exports
│   └── elementor/        # Elementor templates and kits
├── scripts/               # Automation
│   ├── setup/            # Project scaffolding
│   ├── deploy/           # Deployment utilities
│   └── maintenance/      # Backup, cleanup routines
├── wordpress/             # WordPress configuration
│   ├── config/           # wp-config templates, environment overrides
│   ├── security/         # .htaccess, hardening rules
│   ├── mu-plugins/       # Must-Use plugins
│   └── child-theme/      # Kadence child theme base
└── .github/               # CI/CD
    └── workflows/        # GitHub Actions definitions
```

---

## WordPress Architecture

```
WordPress Core (untouchable)
    │
    ├── Kadence Parent Theme (untouchable)
    │   └── Kadence Child Theme (all theme modifications)
    │       ├── functions.php → requires inc/*.php
    │       ├── inc/hooks.php, enqueue.php, helpers.php, schema.php
    │       ├── assets/css/, assets/js/
    │       └── template-parts/, templates/
    │
    ├── Approved Plugins (untouchable source code)
    │   ├── Kadence Blocks Pro → default page builder
    │   ├── Elementor → conditional advanced layouts
    │   ├── Rank Math Pro → all SEO functions
    │   ├── WP Rocket → all performance optimization
    │   └── Instant Indexing → Google Indexing API
    │
    ├── MU Plugins (always active, framework-level)
    │   ├── waif-security.php → security hardening
    │   ├── waif-performance.php → performance rules
    │   └── waif-cleanup.php → WordPress bloat removal
    │
    └── Custom Plugins (project-specific functionality)
        └── waif-{name}/ → theme-independent features
```

| Component | Modifiable | Extension Method |
|---|---|---|
| WordPress Core | Never | N/A |
| Kadence Parent Theme | Never | Child theme overrides |
| Kadence Child Theme | Yes | Primary customization point |
| Approved Plugins | Never (source) | Hooks, filters, companion plugins |
| MU Plugins | Yes | Framework-level code |
| Custom Plugins | Yes | Project-specific functionality |

**Interaction rule:** Child theme and MU plugins interact with WordPress and plugins exclusively through hooks and filters. Direct function calls into plugin internals create fragile coupling.

---

## AI Architecture

Each AI assistant has a defined role. Roles do not overlap.

```
┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│  Claude   │  │ ChatGPT  │  │  Codex   │  │  Gemini  │  │  Cursor  │
│           │  │          │  │          │  │          │  │          │
│ Architect │  │Strategist│  │  Builder │  │Researcher│  │ IDE Dev  │
│ Reviewer  │  │ SEO Lead │  │ Coder    │  │ Analyst  │  │ Realtime │
│ Documenter│  │ Content  │  │ Automator│  │ Verifier │  │ Completer│
└─────┬─────┘  └─────┬────┘  └─────┬────┘  └────┬─────┘  └────┬─────┘
      │              │             │             │             │
      └──────────────┴─────────────┴─────────────┴─────────────┘
                              │
                    Shared Context Layer
                         ai/ directory
```

| Assistant | Owns | Does Not Own |
|---|---|---|
| **Claude** | Architecture, documentation, code review, planning | Content writing, keyword research |
| **ChatGPT** | SEO strategy, content, technical planning | Final architecture decisions |
| **Codex** | Code generation, refactoring, automation | Architecture, content |
| **Gemini** | Research, competitive analysis, verification | Code generation, architecture |
| **Cursor** | Real-time IDE assistance, inline completion | Planning, documentation |

**All assistants read from the same `ai/` directory.** No assistant maintains private context outside the repository.

---

## Dependency Rules

- Never modify WordPress Core — updates overwrite all changes.
- Never modify approved plugin source code — use hooks and filters.
- Always extend via child theme, custom plugin, or MU plugin.
- Prefer reusable components from `templates/` before building new ones.
- Documentation is part of the architecture — undocumented components do not exist.
- One builder per page — Kadence Blocks or Elementor, never both.
- One tool per function — no duplicate SEO, caching, or optimization plugins.
- Secrets never enter the repository — environment variables only.
- All custom code uses the `waif_` prefix.
- New patterns require a decision record in `ai/decisions/`.

---

## Project Lifecycle

```
Planning → Development → QA → Git → Deployment → Maintenance
   │           │          │     │        │            │
   │           │          │     │        │            └── Updates, monitoring,
   │           │          │     │        │                backups, SEO review
   │           │          │     │        └── GitHub Actions → SSH → health check
   │           │          │     └── Branch → commit → PR → merge
   │           │          └── Responsive, performance, SEO, security, accessibility
   │           └── Build with Kadence/Elementor, child theme, plugins
   └── Define scope, pages, URLs, keywords, schema, templates
```

Every stage produces artifacts. No stage is skipped.

---

## AI Reminder

Before making any architectural decision:

- [ ] Read `PROJECT_CONTEXT.md` — understand constraints and agency context.
- [ ] Read `AI_RULES.md` — understand behavioral boundaries.
- [ ] Read this file (`architecture.md`) — understand system structure.
- [ ] Check `ai/decisions/` — verify this decision has not already been made.
- [ ] Follow framework standards — `CODING_STANDARDS.md`, `WORDPRESS_STANDARDS.md`, and others.
- [ ] Prefer existing patterns — check `templates/` and `ai/conventions/` before inventing.

**If this file conflicts with a detailed standards document, the standards document takes precedence.**