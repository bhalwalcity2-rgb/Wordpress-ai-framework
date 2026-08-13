# workflow.md

> **This file is machine context, not human documentation.**
> Every AI assistant must follow these workflows when performing any task inside this framework. These are not suggestions — they are the required sequence of operations for every project, every feature, and every deployment.

---

## Purpose

This file defines the official workflows for the WordPress AI Framework. It answers: *"In what order do things happen?"* — from full project builds to individual feature development to SEO implementation. AI assistants that skip stages or reorder them produce inconsistent, unmaintainable output.

> For the full client-facing creative/product process (discovery → brand → UX → components → QA → launch, with niche-conditional functionality), see `ai/memory/master-website-workflow.md`. This file covers git/deployment mechanics; that file covers the design and build process.

---

## Standard Project Workflow

Every project follows this sequence. No stage is skipped.

```
Discovery → Planning → UI/UX → WordPress Setup → Development → SEO → Performance → QA → Git Commit → Deployment → Maintenance
```

| Stage | What Happens |
|---|---|
| **Discovery** | Define client industry, service areas, target keywords, competitors, business goals. Establish NAP (Name, Address, Phone). |
| **Planning** | Select page types, define URL structure, plan content hierarchy, identify schema requirements, choose templates from the framework library. |
| **UI/UX** | Define color palette, typography, layout patterns. Configure Kadence global settings. Establish header/footer structure. |
| **WordPress Setup** | Install WordPress, activate approved plugins, activate Kadence child theme, configure Rank Math, WP Rocket, Instant Indexing. |
| **Development** | Build pages using Kadence Blocks (default) or Elementor (exception). Implement child theme code, MU plugins, custom functionality. |
| **SEO** | Configure metadata, schema, internal linking, heading hierarchy, image alt text, Open Graph tags per page. Validate schema. |
| **Performance** | Optimize images (WebP, compression), configure WP Rocket, defer JS, verify Core Web Vitals targets, audit page weight. |
| **QA** | Run the full QA checklist: responsive, cross-browser, performance, accessibility, SEO, functional, security. |
| **Git Commit** | Commit all changes with proper messages. Push to GitHub. Open Pull Request. |
| **Deployment** | Merge PR → GitHub Actions deploys to staging → verify → tag release → deploy to production → health check. |
| **Maintenance** | Ongoing: plugin updates, content updates, performance monitoring, Search Console review, backup verification. |

---

## AI Workflow

Before starting any task, every AI assistant executes this sequence:

| Step | Action | File |
|---|---|---|
| 1 | Read project architecture and constraints | `PROJECT_CONTEXT.md` |
| 2 | Read AI behavioral rules | `AI_RULES.md` |
| 3 | Read the relevant standards document for the task | `WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md`, `SEO_STANDARDS.md`, `KADENCE_STANDARDS.md`, `ELEMENTOR_STANDARDS.md`, `PLUGIN_STANDARDS.md`, or `DEPLOYMENT_STANDARDS.md` |
| 4 | Read quick-reference files | `tech-stack.md`, `approved-plugins.md` |
| 5 | Check for prior architectural decisions | `ai/decisions/` |
| 6 | Check for existing conventions | `ai/conventions/` |
| 7 | Verify the task uses only approved technologies | `tech-stack.md` |
| 8 | Proceed with the task | Following all applicable standards |

**If any context file contradicts another, precedence is:** `PROJECT_CONTEXT.md` → specific standards document → `tech-stack.md`.

---

## Development Workflow

Every code change follows this sequence:

```
Branch → Develop → Test → Document → Commit → Push → PR → Review → Merge → Deploy
```

| Step | Command / Action | Rules |
|---|---|---|
| Create branch | `git checkout -b feature/description main` | Branch from main. One concern per branch. |
| Develop | Write code following `CODING_STANDARDS.md` | Use approved tech only. Prefix all custom code with `waif_`. |
| Test locally | Verify functionality, responsive, performance | Test at 375px, 768px, 1440px viewports. |
| Update docs | Update relevant documentation if behavior changes | Documentation is part of the change, not a follow-up. |
| Commit | `git commit -m "type: Description"` | Atomic commits. Imperative mood. ≤ 72 chars. |
| Push | `git push origin feature/description` | Push to GitHub. |
| Pull Request | Open PR with description, checklist, test steps | Fill in the PR template completely. |
| Review | Human or AI reviews against standards | Check security, performance, SEO, naming, docs. |
| Merge | Squash merge to main | Delete branch after merge. |
| Deploy | GitHub Actions auto-deploys staging; tag triggers production | Health check post-deploy. Cache purge. |

---

## Website Build Workflow

When building a new WordPress site from scratch, follow this order:

| Order | Task | Tool |
|---|---|---|
| 1 | Install WordPress | Server / WP-CLI |
| 2 | Install and activate Kadence Theme | WordPress admin |
| 3 | Install and activate Kadence Child Theme | WordPress admin |
| 4 | Install core plugins (Kadence Blocks Pro, Rank Math Pro, WP Rocket, Instant Indexing) | WordPress admin |
| 5 | Install Elementor (only if the project requires it) | WordPress admin |
| 6 | Configure Kadence global palette (9 colors) | Customizer |
| 7 | Configure Kadence global typography (body + headings, responsive) | Customizer |
| 8 | Configure Kadence header builder | Customizer |
| 9 | Configure Kadence footer builder | Customizer |
| 10 | Configure Rank Math (wizard, Search Console, schema defaults, Local SEO) | Rank Math settings |
| 11 | Configure WP Rocket (cache, CSS/JS optimization, lazy loading, database) | WP Rocket settings |
| 12 | Configure Instant Indexing (API credentials, auto-submit) | Plugin settings |
| 13 | Set permalink structure to `/%postname%/` | Settings → Permalinks |
| 14 | Create core pages (Home, About, Contact, Services, Privacy, 404) | WordPress admin |
| 15 | Build page layouts using Kadence Blocks (or Elementor where justified) | Block editor / Elementor |
| 16 | Build location pages (if Local SEO project) | Block editor / Elementor |
| 17 | Configure per-page SEO (title, meta, schema, OG tags) via Rank Math | Rank Math per-page |
| 18 | Implement internal linking across all pages | Content editing |
| 19 | Run full QA checklist | Manual + PageSpeed Insights |
| 20 | Deploy via Git pipeline | GitHub → GitHub Actions → SSH |

---

## SEO Workflow

Every page follows this SEO implementation sequence:

```
Research → Plan → Build → Optimize → Link → Index → Verify
```

| Step | Action |
|---|---|
| **Keyword Research** | Identify primary keyword, 3–5 secondary keywords, and related entities for the page. |
| **Content Planning** | Define heading structure (H1 → H2 → H3), content sections, FAQ questions, word count target. |
| **On-Page SEO** | Write unique title tag (≤60 chars), meta description (≤160 chars), set URL slug. |
| **Schema** | Add appropriate JSON-LD: LocalBusiness, Service, FAQPage, Article, BreadcrumbList. Validate via Rich Results Test. |
| **Internal Linking** | Add ≥3 internal links. Link from 3–5 existing pages to the new page. Zero orphan pages. |
| **Image SEO** | Optimize images (WebP, compressed, correct dimensions). Add descriptive alt text. Set width/height attributes. |
| **Indexing** | Submit via Instant Indexing. Verify in Google Search Console URL Inspection. |
| **Verification** | Confirm page is indexed within 7 days. Confirm schema appears in Rich Results. Confirm Rank Math score ≥80. |

---

## AI Decision Rules

| Rule | Detail |
|---|---|
| Never skip planning | Define structure and requirements before writing code or content. |
| Never skip QA | Every output is tested against the relevant QA checklist before delivery. |
| Never skip documentation | If behavior changes, documentation updates are part of the task. |
| Never introduce unapproved technology | Check `tech-stack.md` and `approved-plugins.md` first. |
| Prefer reusable components | Check `templates/` before building from scratch. |
| Follow framework standards | Every task is governed by at least one standards document. |
| Document decisions | New patterns or architectural choices are recorded in `ai/decisions/`. |
| One concern per task | Do not bundle unrelated changes. |

---

## Quick Checklist

Before marking any task complete, verify:

- [ ] Output follows the relevant standards document.
- [ ] Only approved technologies are used.
- [ ] Code is prefixed with `waif_` (functions) or `.waif-` (CSS).
- [ ] No WordPress Core or parent theme files modified.
- [ ] Security: inputs validated/sanitized, outputs escaped, nonces present.
- [ ] Responsive: verified at mobile, tablet, and desktop.
- [ ] SEO: heading hierarchy correct, schema validated, metadata set.
- [ ] Performance: no unnecessary assets loaded, images optimized.
- [ ] Documentation updated (if the task changed behavior or conventions).
- [ ] Commit message follows format: `type: Description`.

**If this file conflicts with a detailed standards document, the standards document takes precedence.**