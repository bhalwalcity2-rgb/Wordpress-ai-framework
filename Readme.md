# WordPress AI Framework

**A structured, AI-native development framework for building, deploying, and maintaining WordPress websites at agency scale.**

This repository is the foundation of every WordPress project we ship. It encodes our architecture decisions, SEO methodology, deployment pipeline, and documentation standards into a single, version-controlled system — designed from the ground up to be operated by both human developers and AI assistants.

---

## Vision

WordPress development at most agencies is improvised. Every project starts from scratch, accumulates undocumented decisions, and drifts from whatever standards existed on paper. Knowledge lives in people's heads, not in the repository. When someone leaves, the knowledge leaves with them.

This framework exists to solve that.

**The problems it addresses:**

- No standard project structure across websites
- SEO implementation that varies by developer, not by methodology
- Deployment processes that depend on manual steps and tribal knowledge
- Documentation that is written once and never maintained
- AI assistants that receive inconsistent, contradictory instructions across projects

**Why AI-first development matters:**

AI assistants are not optional tools anymore — they are part of the development team. But they are only as effective as the context they receive. An AI working from a well-structured repository with clear conventions, documented decisions, and predictable file locations will produce better work than one working from a blank slate.

This framework treats AI context as a first-class architectural concern. Every folder, every convention, and every document is designed to be legible to both humans and machines.

---

## Features

| Feature | Description |
|---|---|
| **AI Ready** | Structured context files, conventions, and documentation designed for consumption by AI assistants |
| **Reusable Architecture** | Standardized project structure that applies to every WordPress build |
| **SEO Optimized** | Built-in Local SEO methodology, schema standards, and indexing workflows |
| **Deployment Ready** | Git-based deployment pipeline with SSH and GitHub Actions |
| **Documentation Driven** | Every decision, convention, and process is documented in the repository |
| **Performance Focused** | Caching strategy, asset optimization, and Core Web Vitals standards baked in |
| **Security First** | Hardened WordPress configuration, file permissions, and access controls |
| **Scalable** | One framework, unlimited websites — clone, configure, deploy |
| **Version Controlled** | Semantic versioning with changelogs and release management |

---

## Technology Stack

### Core

| Technology | Role |
|---|---|
| WordPress | Content management system |
| Kadence Theme | Lightweight, performance-oriented theme framework |
| Kadence Blocks Pro | Block-based page building and layout system |
| Elementor | Visual page builder for complex layouts and landing pages |

### SEO

| Technology | Role |
|---|---|
| Rank Math Pro | On-page SEO, schema markup, and sitemap management |
| Instant Indexing | Google Indexing API integration for rapid page discovery |

### Performance

| Technology | Role |
|---|---|
| WP Rocket | Page caching, asset minification, and lazy loading |

### Development & Deployment

| Technology | Role |
|---|---|
| Git | Version control |
| GitHub | Repository hosting and collaboration |
| GitHub Actions | Automated workflows for deployment and validation |
| SSH | Secure server deployment |

### AI Assistants

| Assistant | Primary Use |
|---|---|
| ChatGPT | Content generation, planning, and strategy |
| Claude | Architecture decisions, code review, and documentation |
| Codex | Code generation, refactoring, and automation scripts |
| Gemini | Research, analysis, and cross-referencing |
| Cursor | IDE-integrated AI development and real-time code assistance |

---

## Repository Structure

```
wordpress-ai-framework/
│
├── .github/              # GitHub Actions workflows and repository configuration
│
├── ai/                   # AI assistant context files, conventions, and instructions
│
├── docs/                 # Project documentation, architecture decisions, and guides
│
├── scripts/              # Automation scripts for deployment, setup, and maintenance
│
├── templates/            # Reusable WordPress templates, components, and page structures
│
├── wordpress/            # WordPress core configuration, theme files, and plugin settings
│
├── README.md             # This file
├── CHANGELOG.md          # Version history and release notes
├── CONTRIBUTING.md       # Internal contribution guidelines
└── VERSION               # Current framework version
```

### Folder Details

**`.github/`** — Contains GitHub Actions workflow definitions for automated deployment, code validation, and release management. All CI/CD configuration lives here.

**`ai/`** — The AI brain of the framework. This directory holds structured context files that AI assistants read before performing any task. It includes coding conventions, project rules, SEO standards, and architectural constraints. When an AI assistant is given a task, it reads from this directory first.

**`docs/`** — All human-readable documentation. Architecture decision records, development guides, SEO methodology, deployment procedures, and onboarding materials. If it's not in `docs/`, it doesn't exist.

**`scripts/`** — Shell scripts and automation utilities for common operations: project scaffolding, deployment, database management, backup routines, and environment setup.

**`templates/`** — Reusable WordPress components, page layouts, section templates, and content structures that are shared across projects. These are the building blocks — not full pages, but the standardized pieces that compose them.

**`wordpress/`** — WordPress-specific configuration: `wp-config.php` templates, `.htaccess` rules, recommended plugin configurations, theme settings exports, and environment-specific overrides.

---

## Development Principles

These principles are not aspirational — they are constraints. Every pull request, every AI-generated output, and every architectural decision is evaluated against them.

### 1. AI First

Every file, folder, and convention is designed to be legible to AI assistants. If an AI cannot understand the project structure by reading the repository, the structure is wrong.

### 2. Documentation First

No feature ships without documentation. No decision is made without recording the reasoning. Documentation is not a post-launch task — it is a development requirement.

### 3. Performance First

Every page must meet Core Web Vitals thresholds before deployment. Performance is not optimized after the fact — it is designed in from the start.

### 4. SEO First

Local SEO is not an afterthought. Schema markup, meta configuration, internal linking, and indexing are part of the development workflow, not a separate phase.

### 5. Security First

WordPress hardening, file permission standards, and access controls are applied to every project by default, not on request.

### 6. Reusable Components

If something is built once, it should be usable everywhere. Templates, sections, and configurations are extracted into reusable components in the `templates/` directory.

### 7. Version Controlled

Every change is committed with a clear message. Every release has a version number. Every deployment is traceable to a specific commit.

---

## AI Integration

This framework is designed to work with multiple AI assistants simultaneously. Each assistant has different strengths, and the framework assigns clear responsibilities to avoid overlap and contradiction.

### Responsibility Matrix

| Responsibility | Primary Assistant | Supporting |
|---|---|---|
| Architecture & system design | Claude | ChatGPT |
| Code generation & automation | Codex | Cursor |
| Content & copy generation | ChatGPT | Gemini |
| Code review & refactoring | Claude | Codex |
| Research & competitive analysis | Gemini | ChatGPT |
| Real-time development & IDE tasks | Cursor | Codex |
| Documentation & technical writing | Claude | ChatGPT |
| SEO strategy & implementation | ChatGPT | Claude |

### How It Works

All AI assistants read from the same `ai/` directory before performing any task. This directory contains:

- **Project conventions** — coding standards, naming rules, and architectural constraints
- **SEO standards** — schema requirements, meta tag rules, and indexing procedures
- **Component registry** — available templates, their purposes, and usage guidelines
- **Decision log** — past architectural decisions and their rationale, so AI assistants do not revisit settled questions

The framework does not prescribe specific prompts. It provides structured context that any capable AI assistant can interpret and act on, regardless of provider.

---

## Development Workflow

Every project follows this workflow without exception.

```
Planning → Development → Testing → Documentation → Git Commit → Release → Deployment
```

### 1. Planning

Define the project scope, select templates from the component library, identify SEO targets, and establish performance benchmarks. The planning output is a structured document in `docs/`.

### 2. Development

Build using the framework's templates and conventions. AI assistants read from `ai/` for context. All work happens on feature branches.

### 3. Testing

Validate against Core Web Vitals, test responsive behavior, verify schema markup, and confirm SEO configuration. No merge without passing validation.

### 4. Documentation

Update all relevant documentation: component usage, configuration changes, architectural decisions, and deployment notes.

### 5. Git Commit

Commit with clear, descriptive messages following the project's commit conventions. Reference related issues or tasks.

### 6. Release

Tag the release with a semantic version number. Update `CHANGELOG.md` and `VERSION`. Merge to the main branch.

### 7. Deployment

Trigger the deployment pipeline via GitHub Actions. The pipeline handles file transfer, cache invalidation, and post-deployment verification.

---

## Versioning

This framework uses **Semantic Versioning** (`MAJOR.MINOR.PATCH`).

| Version | Meaning |
|---|---|
| `v0.1.0` | Initial framework structure and documentation |
| `v0.2.0` | AI context system and conventions established |
| `v0.x.0` | Pre-release development — breaking changes expected |
| `v1.0.0` | First stable release — production ready |
| `v1.1.0` | New feature added, backward compatible |
| `v1.1.1` | Bug fix or documentation correction |

**Rules:**

- `MAJOR` increments when the framework introduces breaking changes to project structure or conventions
- `MINOR` increments when new features, templates, or capabilities are added
- `PATCH` increments for fixes, corrections, and documentation updates
- All pre-1.0 releases (`v0.x.0`) may include breaking changes without a major version bump

---

## Roadmap

Development is organized into sequential phases. Each phase builds on the previous one.

| Phase | Milestone | Status |
|---|---|---|
| **Phase 1** | Framework Foundation — repository structure, conventions, documentation standards | 🔄 In Progress |
| **Phase 2** | AI Brain — context files, assistant conventions, responsibility matrix | ⬜ Planned |
| **Phase 3** | WordPress Boilerplate — base theme config, plugin settings, security hardening | ⬜ Planned |
| **Phase 4** | Deployment Automation — GitHub Actions pipeline, SSH deployment, rollback support | ⬜ Planned |
| **Phase 5** | SEO System — Local SEO methodology, schema templates, indexing automation | ⬜ Planned |
| **Phase 6** | Website Generator — project scaffolding from templates with a single command | ⬜ Planned |
| **Phase 7** | Agency Operating System — client onboarding, project management, reporting integration | ⬜ Planned |

---

## Contribution

This is an internal repository. All contributions follow these rules:

1. **Branch from `main`.** Every change starts on a feature branch with a descriptive name.
2. **One concern per commit.** Do not bundle unrelated changes in a single commit.
3. **Document everything.** If your change affects conventions, templates, or workflows, update the relevant documentation before requesting review.
4. **Follow the conventions.** Read the `ai/` directory before writing code. The conventions exist for a reason.
5. **Test before committing.** Validate performance, SEO configuration, and responsive behavior.
6. **Write clear commit messages.** The message should explain *what* changed and *why*, not just *how*.

Refer to `CONTRIBUTING.md` for the full contribution guide.

---

## License

This repository is **private** and intended exclusively for internal agency use. All contents — including code, documentation, templates, and AI context files — are proprietary. Unauthorized distribution, reproduction, or use outside the agency is not permitted.

---

*WordPress AI Framework is maintained by the development team and continuously improved with every project we ship.*