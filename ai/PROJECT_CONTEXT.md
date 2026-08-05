# PROJECT_CONTEXT.md
# Document Information

| Field | Value |
|--------|-------|
| Document | PROJECT_CONTEXT.md |
| Framework | WordPress AI Framework |
| Version | 0.1.0 |
| Status | Active |
| Audience | AI Assistants |
| Last Updated | YYYY-MM-DD |
| Owner | Ahtsham Afzal |
| Priority | Critical |
> **This document is written for AI assistants, not humans.**
> Every AI assistant (Claude, ChatGPT, Codex, Gemini, Cursor) must read this file in full before performing any task inside this repository.
> This is the permanent memory and architectural context of the project. Do not assume, infer, or invent anything that contradicts what is documented here.

---

## 1. Project Identity

| Field | Value |
|---|---|
| Project Name | WordPress AI Framework |
| Repository Type | Private, internal agency use only |
| Classification | Development framework — not a theme, not a plugin, not a starter site |
| Current Phase | Foundation (pre-1.0) |
| Versioning | Semantic Versioning (`MAJOR.MINOR.PATCH`) |

---

## 2. What This Project Is

This repository is a complete development framework that standardizes how the agency builds, documents, deploys, and maintains WordPress websites.

Every future WordPress project built by this agency will use this framework as its foundation.

The framework standardizes:

- Project structure and file organization
- Development conventions and coding standards
- Local SEO methodology and implementation
- Deployment pipelines and release management
- Performance benchmarks and optimization
- Security hardening and access controls
- AI collaboration workflows and context management
- Documentation standards and decision records

**This is not:**

- A WordPress theme or child theme
- A WordPress plugin
- A starter website or boilerplate that gets cloned and edited
- A design system or UI library

It is an operational system that governs how websites are built.

---

## 3. Agency Context

### Primary Focus

The agency builds **Local SEO websites** for service-based businesses. SEO is not a bolt-on service — it is embedded into the development process from the first commit.

### Target Industries

The framework is designed primarily for these client verticals:

| Industry | Typical Site Type |
|---|---|
| Junk Car Buyers | Lead generation, multi-location |
| HVAC | Service area pages, seasonal content |
| Plumbing | Emergency service, local landing pages |
| Roofing | Lead generation, before/after galleries |
| Tree Service | Service area coverage, seasonal |
| Landscaping | Portfolio-driven, service pages |
| Pest Control | Service area, seasonal campaigns |
| Electricians | Emergency service, commercial/residential split |
| Garage Door | Service + product hybrid |
| Locksmith | Emergency service, trust signals |
| Home Services | General contractor, multi-service |
| Medical Spa | Appointment-driven, treatment pages |
| Dental | Patient acquisition, procedure pages |
| Local Businesses | General local presence, GMB integration |

The framework must remain flexible enough to support industries beyond this list without architectural changes.

### Common Site Patterns

Across these industries, most websites share these structural elements:

- Homepage with hero, services overview, trust signals, and CTA
- Individual service pages with schema markup
- Location/service area pages optimized for Local SEO
- About page with team/company information
- Contact page with structured data
- Blog/resource section for content marketing
- Testimonials and review integration
- FAQ sections with FAQPage schema

AI assistants should recognize these patterns and apply them consistently.

---

## 4. Official Technology Stack

The following technologies are approved for use in this framework. Do not introduce alternatives without documented justification.

### Core Platform

| Component | Technology | Role |
|---|---|---|
| CMS | WordPress | Content management system |
| Theme | Kadence Theme | Lightweight, performance-oriented base theme |
| Blocks | Kadence Blocks Pro | Block-based page building and layout components |
| Page Builder | Elementor | Visual page builder for advanced layouts |

### SEO

| Component | Technology | Role |
|---|---|---|
| On-Page SEO | Rank Math Pro | Meta tags, schema, sitemaps, redirects |
| Indexing | Instant Indexing | Google Indexing API for rapid page discovery |

### Performance

| Component | Technology | Role |
|---|---|---|
| Caching | WP Rocket | Page caching, asset optimization, lazy loading |

### Development and Deployment

| Component | Technology | Role |
|---|---|---|
| Version Control | Git | Source control |
| Repository | GitHub | Hosting, collaboration, code review |
| Automation | GitHub Actions | Deployment pipelines, validation workflows |
| Server Access | SSH | Secure deployment to production/staging |

### AI Assistants

| Assistant | Status |
|---|---|
| Claude | Active |
| ChatGPT | Active |
| Codex | Active |
| Gemini | Active |
| Cursor | Active |

---

## 5. Development Principles

These are not guidelines — they are constraints. Every decision, every pull request, and every AI-generated output must conform to them.

### Documentation First

No feature, convention, or architectural decision exists unless it is documented in this repository. If documentation is missing, the correct action is to create it — not to proceed without it.

### AI First

Every file, folder, naming convention, and document is designed to be legible to AI assistants. If an AI assistant cannot understand the project structure by reading the repository, the structure is wrong.

### SEO First

SEO is part of the development process, not a post-launch task. Every page is built with heading structure, schema, internal linking, metadata, and crawlability considered from the start.

### Performance First

Every page must meet Core Web Vitals thresholds before deployment. Visual effects that degrade performance are not permitted.

### Security First

WordPress hardening, file permissions, access controls, and input sanitization are applied by default on every project.

### Reusable Architecture

If a component, template, or configuration is built once, it must be extracted into a reusable form. Duplication of logic is not permitted.

### Version Controlled

Every change is committed with a descriptive message. Every release is tagged. Every deployment is traceable to a specific commit.

### Automation First

Manual processes that can be automated must be automated. Deployment, validation, and repetitive setup tasks should not depend on human memory.

### Long-Term Over Short-Term

Never compromise long-term maintainability for short-term speed. A properly documented, well-structured solution that takes longer is always preferred over a quick fix that creates technical debt.

---

## 6. AI Collaboration Model

### Foundational Rule

All AI assistants work together. No assistant replaces another. Each has defined responsibilities based on its strengths.

### Responsibility Assignment

**Claude**

- Architecture decisions and system design
- Documentation and technical writing
- Code review and quality assessment
- Project planning and structural decisions
- Convention definition and enforcement

**ChatGPT**

- SEO strategy and keyword research
- Content generation and copywriting
- Technical planning and brainstorming
- Client-facing documentation and proposals

**Codex**

- Code generation and implementation
- Refactoring and code optimization
- Automation scripts and tooling
- Test generation and validation

**Gemini**

- Research and data gathering
- Competitive analysis
- Fact verification and cross-referencing
- Market and industry analysis

**Cursor**

- Real-time IDE-integrated development
- Live code assistance during active development
- Inline code generation and completion

### Context Protocol

Before performing any task, every AI assistant must:

1. Read `PROJECT_CONTEXT.md` (this file) for architectural context
2. Read any relevant files in the `ai/` directory for conventions and rules
3. Check `docs/` for existing documentation on the topic
4. Prefer existing conventions over inventing new ones
5. If a convention does not exist for the task at hand, propose one and document it

### Conflict Resolution

If an AI assistant encounters a situation where:

- Two conventions appear to conflict → follow the one documented more recently
- No convention exists → propose a convention, document it, and proceed
- A previous AI output contradicts this document → this document takes precedence

---

## 7. Repository Rules

### Single Source of Truth

This repository is the single source of truth for the framework. If something is not documented inside the repository, it should be treated as undefined.

### Decision Records

Every significant architectural decision must be documented with:

- The decision made
- The alternatives considered
- The reasoning behind the choice
- The date of the decision

### Feature Documentation

Every major feature, template, or system must have corresponding documentation in `docs/` before it is considered complete.

### Convention Precedence

AI assistants must always prefer existing conventions over inventing new ones. Consistency across the framework is more important than any individual improvement.

---

## 8. Coding Philosophy

### WordPress Core Rules

| Rule | Explanation |
|---|---|
| Never modify WordPress Core | Updates will overwrite changes — all customization must be external |
| Never modify third-party plugins | Same reason — use hooks, filters, or wrapper plugins |
| Never modify Kadence Parent Theme | Extend via child theme only |

### Extension Methods

All customization must use one of these approved methods:

- **Child Theme** — for theme-level overrides and template customization
- **Custom Plugins** — for functionality that is independent of the theme
- **MU Plugins** — for functionality that must always be active regardless of theme

### Code Standards

- Never duplicate logic — extract into reusable functions or components
- Prefer configuration over hardcoding — values that change per project belong in configuration files, not in code
- Follow WordPress Coding Standards for PHP, HTML, CSS, and JavaScript
- Use meaningful, descriptive names for functions, variables, files, and directories

---

## 9. WordPress Architecture Rules

### Theme Usage

| Builder | When to Use |
|---|---|
| Kadence (blocks, theme options) | Default for all standard layouts — headers, footers, pages, posts, archives |
| Elementor | Only when advanced layout requirements exceed what Kadence can deliver |

Kadence is the default. Elementor is the exception. Do not use Elementor for layouts that Kadence can handle.

### Performance Rules

- Never sacrifice page speed for visual effects
- Minimize plugin count — every plugin adds load
- Optimize images before upload — do not rely solely on lazy loading
- Defer non-critical JavaScript
- Inline critical CSS where possible

---

## 10. SEO Architecture

### Core Principle

SEO is a development discipline, not a marketing task. It is implemented during development, not after.

### Page-Level Requirements

Every page deployed to production must include:

| Requirement | Standard |
|---|---|
| Heading structure | Single H1, logical H2-H6 hierarchy |
| Title tag | Unique, keyword-targeted, under 60 characters |
| Meta description | Unique, action-oriented, under 160 characters |
| Schema markup | Appropriate type (LocalBusiness, FAQPage, Service, etc.) |
| Internal linking | Minimum 3 relevant internal links per page |
| Image optimization | Compressed, descriptive alt text, proper dimensions |
| URL structure | Short, descriptive, lowercase, hyphen-separated |
| Canonical tag | Self-referencing unless intentionally consolidated |
| Open Graph tags | Title, description, image for social sharing |

### Local SEO Requirements

For location-based pages:

| Requirement | Standard |
|---|---|
| NAP consistency | Name, Address, Phone identical across all pages and citations |
| LocalBusiness schema | Fully populated with geo-coordinates, service area, hours |
| Location pages | Unique content per location — no thin or duplicate pages |
| Service area pages | Dedicated pages per service area with localized content |
| GMB alignment | Website content must align with Google Business Profile data |

---

## 11. Deployment Architecture

### Core Principle

Deployment is always automated. Manual FTP deployment is not permitted.

### Pipeline

```
Feature Branch → Pull Request → Review → Merge to Main → GitHub Actions → SSH Deploy → Verification
```

### Rules

- Every deployment must be triggered by a merge to the main branch
- Every deployment must be reproducible from the commit history
- Rollback must be possible by reverting to a previous tagged release
- Staging environments must mirror production configuration
- Post-deployment verification must confirm the site is operational

---

## 12. Documentation Architecture

### Core Principle

Documentation is part of development. It is not a separate phase, and it is not optional.

### Standards

- All documentation lives in `docs/` or in the relevant directory as a `README.md`
- Documentation is written in Markdown
- Every document has a clear title, purpose statement, and last-updated date
- AI assistants must update documentation when they change the system it describes
- Outdated documentation is worse than no documentation — keep it current

### AI-Specific Documentation

The `ai/` directory contains files written specifically for AI assistant consumption:

- Coding conventions and standards
- Naming rules and patterns
- Component registry and usage guidelines
- SEO implementation standards
- Architectural constraints and boundaries
- Decision log for settled questions

AI assistants must check `ai/` before performing any task that involves code generation, architecture decisions, or convention-setting.

---

## 13. Repository Structure

```
wordpress-ai-framework/
│
├── .github/              # GitHub Actions workflows and repository configuration
│
├── ai/                   # AI assistant context — conventions, rules, constraints
│   ├── conventions/      # Coding standards, naming rules, file patterns
│   ├── seo/              # SEO implementation standards and schema templates
│   └── decisions/        # Architectural decision records
│
├── docs/                 # Human-readable documentation
│   ├── architecture/     # System design and architectural guides
│   ├── development/      # Development workflow and setup guides
│   ├── deployment/       # Deployment procedures and configuration
│   └── seo/              # SEO methodology and implementation guides
│
├── scripts/              # Automation scripts
│   ├── setup/            # Project scaffolding and environment setup
│   ├── deploy/           # Deployment utilities
│   └── maintenance/      # Backup, cleanup, and maintenance routines
│
├── templates/            # Reusable WordPress components
│   ├── pages/            # Full page templates
│   ├── sections/         # Reusable page sections (hero, CTA, FAQ, etc.)
│   ├── schema/           # JSON-LD schema templates
│   └── config/           # Plugin and theme configuration exports
│
├── wordpress/            # WordPress-specific configuration
│   ├── config/           # wp-config.php templates and environment overrides
│   ├── security/         # .htaccess rules, file permissions, hardening
│   ├── mu-plugins/       # Must-use plugins
│   └── child-theme/      # Kadence child theme base
│
├── PROJECT_CONTEXT.md    # This file — AI permanent memory
├── README.md             # Human-facing project overview
├── CHANGELOG.md          # Version history and release notes
├── CONTRIBUTING.md       # Internal contribution guidelines
└── VERSION               # Current framework version
```

---

## 14. Long-Term Vision

The final objective of this framework is to evolve into an **AI-powered Agency Operating System**.

### Target Capabilities

| Capability | Description |
|---|---|
| One-command project creation | Scaffold a complete WordPress project from templates with a single command |
| Automatic deployment | Push to main triggers full deployment without manual intervention |
| AI project memory | Every project maintains its own context that AI assistants read automatically |
| Reusable SEO systems | Local SEO methodology encoded as repeatable, automated workflows |
| Reusable design systems | Standardized visual components that maintain brand consistency across projects |
| Reusable WordPress boilerplates | Pre-configured WordPress setups for each industry vertical |
| Reusable Local SEO workflows | Location page generation, schema templating, citation management |
| AI-assisted maintenance | Routine maintenance tasks (updates, backups, monitoring) handled by AI workflows |

### Success Metric

The framework should reduce the setup time of a new project from hours to minutes while maintaining enterprise-level quality, documentation, and SEO standards.

---

## 15. What AI Assistants Must Never Do

| Constraint | Reason |
|---|---|
| Never modify WordPress Core files | Updates will overwrite all changes |
| Never modify third-party plugin files | Same — use hooks and filters |
| Never modify the Kadence parent theme | Extend via child theme only |
| Never introduce a new technology without documented justification | Stack consistency is a priority |
| Never invent a convention when one already exists | Check `ai/` and `docs/` first |
| Never skip documentation when making structural changes | Undocumented changes do not exist |
| Never optimize for speed of delivery over long-term maintainability | Technical debt is not acceptable |
| Never hardcode values that should be configurable | Use configuration files |
| Never deploy without going through the defined pipeline | No manual deployments |

---

## 16. How to Use This Document

1. **Before starting any task**, read this document in full
2. **Before writing code**, check `ai/conventions/` for applicable standards
3. **Before making an architectural decision**, check `ai/decisions/` for prior rulings
4. **Before creating documentation**, check `docs/` for existing coverage
5. **When in doubt**, default to the principle that is most conservative and most documented
6. **If this document conflicts with any other file**, this document takes precedence

This is the permanent memory of the project. Treat it as such.
## AI Reading Order

Every AI assistant should follow this order before starting work:

1. PROJECT_CONTEXT.md
2. AI_RULES.md
3. WORDPRESS_STANDARDS.md
4. CODING_STANDARDS.md
5. SEO_STANDARDS.md
6. Relevant documentation in `/docs`
7. Task-specific files