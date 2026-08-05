# commits.md

> **This file is machine context, not human documentation.**
> Every AI assistant must follow these Git conventions when generating commit messages, suggesting branch names, preparing PRs, or tagging releases. These are enforced standards, not suggestions.

---

## Purpose

This file defines the exact format for every commit message, branch name, PR description, merge strategy, and release tag in the framework. Inconsistent Git history makes debugging, rollback, and changelog generation unreliable. These conventions prevent that.

---

## Commit Message Format

```
type: short description in imperative mood

Optional body explaining what changed and why.
Wrap body lines at 72 characters.

Refs: #issue-number (if applicable)
```

### Subject Line Rules

| Rule | Standard |
|---|---|
| Format | `type: Description` |
| Length | ≤ 72 characters |
| Tense | Imperative: "Add section" not "Added section" or "Adds section" |
| Capitalization | Lowercase type, capitalize first word of description |
| Punctuation | No period at the end |
| Scope | Describes the change, not the activity: "Add Camden FAQ schema" not "Work on SEO" |

### Commit Types

| Type | When to Use |
|---|---|
| `feat` | New feature, page, section, or component |
| `fix` | Bug fix — something was broken, now it works |
| `docs` | Documentation changes only — no code behavior change |
| `style` | Code formatting, whitespace, semicolons — no logic change |
| `refactor` | Code restructuring — behavior unchanged |
| `perf` | Performance improvement |
| `seo` | SEO changes: schema, meta, headings, internal links |
| `security` | Security hardening |
| `test` | Adding or updating tests |
| `chore` | Maintenance: dependency updates, config changes, tooling |

### Examples

```
feat: Add Camden NJ location page with hero and FAQ

feat: Add mobile sticky CTA bar for service pages

fix: Correct header CTA opening in same tab instead of new tab

fix: Resolve mobile menu z-index overlapping hero section

docs: Update SEO standards for FAQ schema requirements

style: Fix indentation in schema.php

refactor: Extract phone formatter into waif_format_phone() helper

perf: Defer Elementor frontend JS on non-Elementor pages

seo: Add FAQPage schema to plumbing service page

seo: Update meta descriptions for all South Jersey location pages

security: Disable REST API user enumeration for unauthenticated requests

chore: Update WP Rocket to 3.16.1

chore: Add WebP versions of service page hero images
```

### Body Guidelines

The body is optional but recommended for non-trivial changes:

```
feat: Add location page template with dynamic content

Create a reusable Elementor template for location pages that pulls
city name, service area description, and testimonials from ACF fields.
Template saved to library as "Page - Location Template".

Justification for Elementor documented in ai/decisions/.

Refs: #42
```

| Rule | Detail |
|---|---|
| When to include body | Architectural decisions, non-obvious changes, multi-file changes |
| When to skip body | Single-file changes where the subject line says everything |
| Blank line | Required between subject and body |
| Line wrap | 72 characters per line in body |
| References | Link to issue numbers, decision records, or related PRs |

---

## Branch Strategy

```
main                    ← Always deployable. Protected. No direct pushes.
├── feature/*           ← New features, pages, components
├── fix/*               ← Bug fixes
├── hotfix/*            ← Urgent production fixes
├── docs/*              ← Documentation only
├── refactor/*          ← Code restructuring
├── seo/*               ← SEO changes
├── perf/*              ← Performance improvements
└── chore/*             ← Maintenance tasks
```

### Branch Naming

```
{type}/{short-descriptive-name}
```

| Rule | Standard |
|---|---|
| Case | Lowercase only |
| Separator | Hyphens |
| Length | ≤ 50 characters |
| Source | Always branch from `main` |
| Lifetime | Merge within 1–3 days — delete after merge |

### Examples

```
feature/add-camden-location-page
feature/mobile-sticky-cta
fix/header-cta-link-target
fix/faq-schema-validation-error
hotfix/ssl-mixed-content
seo/faq-schema-service-pages
perf/defer-elementor-assets
docs/update-deployment-standards
refactor/extract-schema-helper
chore/update-rank-math-3.0
```

---

## Pull Request Standards

### PR Title

Same format as commit subject: `type: Short description`

### PR Description

```markdown
## What Changed
One paragraph describing the change.

## Why
The problem solved or requirement fulfilled.

## Pages Affected
- /service-area/camden-nj/
- /junk-car-removal/

## How to Test
1. Navigate to [URL]
2. Verify [behavior]
3. Check mobile viewport

## Checklist
- [ ] Follows CODING_STANDARDS.md
- [ ] No core/parent theme files modified
- [ ] Security: validated, sanitized, escaped
- [ ] Responsive: tested at 375px, 768px, 1440px
- [ ] SEO: schema validated, headings correct
- [ ] Documentation updated (if applicable)
- [ ] No secrets in code
```

### PR Rules

| Rule | Detail |
|---|---|
| Every change goes through a PR | No direct commits to main |
| One concern per PR | Do not bundle unrelated changes |
| Fill in the description | Empty descriptions are rejected |
| Squash merge | Preferred — produces one clean commit on main |
| Delete branch after merge | Automated in repository settings |
| Review required | At least one review (human or AI) before merge |

---

## Merge Strategy

| Strategy | When |
|---|---|
| **Squash merge** | Default for all feature, fix, docs, and chore PRs — collapses branch history into one clean commit |
| **Merge commit** | Only for release branches or large multi-phase features where individual commit history matters |
| **Rebase** | Never on shared branches. Permitted on local branches before pushing. |
| **Force push** | Prohibited on main. Permitted on personal feature branches before PR review. |

---

## Release Tags

### Format

```
v{MAJOR}.{MINOR}.{PATCH}
```

### When to Tag

| Version Change | Trigger |
|---|---|
| MAJOR (`v1.0.0` → `v2.0.0`) | Breaking changes to structure or conventions |
| MINOR (`v1.0.0` → `v1.1.0`) | New features, pages, or capabilities |
| PATCH (`v1.1.0` → `v1.1.1`) | Bug fixes, corrections, doc updates |

### Tagging Process

```bash
# Tag the release
git tag -a v1.2.0 -m "Release v1.2.0 — Add South Jersey location pages"

# Push the tag
git push origin v1.2.0
```

### Rules

| Rule | Detail |
|---|---|
| Annotated tags only | `git tag -a` with a message — never lightweight tags |
| Tag after CHANGELOG update | CHANGELOG.md is updated in the release commit, then tagged |
| Tags are immutable | Never delete or move a tag — create a new patch if needed |
| GitHub Release created | For every tag — copy changelog entries into release description |
| Production deploys require a tag | Untagged code does not reach production |

---

## AI Rules

- Generate commit messages in the exact format defined here — no variations.
- Select the correct type from the type table — do not invent new types.
- Write subject lines in imperative mood, ≤ 72 characters, no period.
- Suggest branch names following `{type}/{descriptive-name}` format.
- Never suggest force-pushing to main or merging without a PR.
- Include the PR checklist in every PR description.
- Default to squash merge unless explicitly told otherwise.
- When preparing a release, remind about CHANGELOG update before tagging.

**If this file conflicts with `DEPLOYMENT_STANDARDS.md`, the more detailed document takes precedence.**