# DEPLOYMENT_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers, DevOps |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — every deployment must follow this document |
| Companion Documents | `PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md` |

> **Every deployment is automated, reproducible, and reversible.**
> No code reaches production through manual file transfer, FTP, or copy-paste. Every change flows through Git, is reviewed before merge, and is deployed through an automated pipeline. This document defines that pipeline end to end.

---

## 1. Deployment Philosophy

### Governing Principles

| Principle | Detail |
|---|---|
| Git is the single source of truth | If it is not committed, it does not exist |
| Automated over manual | Human memory is unreliable — pipelines are not |
| Staging mirrors production | If it works on staging, it works on production. If staging differs, the test is worthless |
| Every deployment is reversible | Rollback to any tagged release within minutes |
| Secrets never touch Git | Credentials, keys, and tokens live in environment variables — never in the repository |
| Deployments are auditable | Every production change is traceable to a commit, a PR, and a person |
| Zero-downtime target | Deployments must not cause visible downtime for site visitors |

### Deployment Pipeline Overview

```
Developer / AI
    │
    ▼
Feature Branch (local development)
    │
    ▼
Push to GitHub
    │
    ▼
Pull Request (review + approval)
    │
    ▼
Merge to main
    │
    ▼
GitHub Actions (triggered automatically)
    │
    ├── Run validation checks
    ├── Build assets (if applicable)
    └── Deploy via SSH
         │
         ├── Staging (automatic on merge)
         └── Production (manual trigger or tag-based)
              │
              ▼
Post-Deployment Health Check
    │
    ▼
Cache Purge (WP Rocket + CDN)
    │
    ▼
Verification Complete
```

---

## 2. Git Workflow

### Branching Strategy

```
main
│
├── Always deployable — every commit on main can go to production
├── Protected — no direct pushes allowed
└── Changes arrive only through merged Pull Requests
    │
    ├── feature/*    New features, pages, sections, components
    ├── fix/*        Bug fixes
    ├── hotfix/*     Urgent production fixes
    ├── docs/*       Documentation-only changes
    ├── refactor/*   Code restructuring without behavior change
    ├── seo/*        SEO-related changes (schema, meta, structure)
    ├── perf/*       Performance improvements
    └── chore/*      Maintenance tasks (dependency updates, config changes)
```

### Branch Naming Convention

```
{type}/{short-descriptive-name}
```

| Rule | Standard |
|---|---|
| Format | Lowercase, hyphen-separated, prefixed with type |
| Length | Descriptive enough to understand at a glance — under 50 characters total |
| No ticket numbers alone | `feature/add-camden-location` not `feature/TICK-423` |
| No personal names | `fix/mobile-menu-overlap` not `fix/ahtsham-fix` |

| Example | Description |
|---|---|
| `feature/add-camden-location-page` | New location page for Camden |
| `fix/header-cta-link-broken` | Fix a broken CTA link in the header |
| `hotfix/ssl-mixed-content` | Urgent fix for mixed content errors |
| `seo/faq-schema-service-pages` | Add FAQ schema to service pages |
| `perf/defer-elementor-assets` | Defer Elementor JS on non-Elementor pages |
| `docs/update-seo-standards` | Documentation update only |
| `chore/update-wp-rocket-3.16` | Plugin update |

### Branch Lifecycle

```
1. Create branch from main
   └── git checkout -b feature/add-camden-location-page main

2. Develop and commit (multiple commits allowed)
   └── git commit -m "feat: add Camden location page hero section"
   └── git commit -m "seo: add FAQ schema for Camden page"

3. Push branch to GitHub
   └── git push origin feature/add-camden-location-page

4. Open Pull Request on GitHub
   └── PR targets main branch

5. Review and approval
   └── Human or AI review against standards

6. Merge to main
   └── Squash merge preferred — produces one clean commit

7. Delete the branch
   └── GitHub auto-deletes merged branches (configure in repo settings)
```

### Branch Rules

| Rule | Detail |
|---|---|
| Branch from main | Always — never branch from another feature branch |
| Keep branches short-lived | Merge within 1–3 days — long-running branches create merge conflicts |
| One concern per branch | A branch adds a location page or fixes a bug — not both |
| Pull before branching | Always `git pull origin main` before creating a new branch |
| Never force push to main | Force push is prohibited on the main branch |
| Delete after merge | Merged branches are deleted immediately |

---

## 3. Commit Standards

### Commit Message Format

```
type: short description in imperative mood

Optional body explaining what changed and why.
Wrap at 72 characters per line.

Refs: #issue-number (if applicable)
```

### Subject Line Rules

| Rule | Standard |
|---|---|
| Format | `type: description` |
| Case | Lowercase type, capitalize first word of description |
| Tense | Imperative mood: "Add hero section" not "Added hero section" |
| Length | ≤ 72 characters |
| Punctuation | No period at the end |
| Specificity | Describe what changed, not what you did: "Add Camden FAQ schema" not "Work on Camden page" |

### Commit Types

| Type | Purpose | Example |
|---|---|---|
| `feat` | New feature, page, or component | `feat: Add Camden NJ location page` |
| `fix` | Bug fix | `fix: Correct mobile menu z-index overlap` |
| `docs` | Documentation only | `docs: Update SEO standards for FAQ schema` |
| `style` | Code formatting — no logic change | `style: Fix indentation in schema.php` |
| `refactor` | Code restructuring — no behavior change | `refactor: Extract phone formatter to helper function` |
| `perf` | Performance improvement | `perf: Defer Elementor JS on non-builder pages` |
| `security` | Security hardening | `security: Disable XML-RPC via MU plugin` |
| `seo` | SEO changes | `seo: Add FAQPage schema to plumbing service page` |
| `test` | Adding or updating tests | `test: Add validation for schema output` |
| `chore` | Maintenance tasks | `chore: Update WP Rocket to 3.16.1` |

### Commit Rules

| Rule | Detail |
|---|---|
| Atomic commits | Each commit is one logical change — not a day's worth of mixed work |
| No WIP commits on main | Work-in-progress commits stay on feature branches; squash before merge |
| No secrets in commits | Verify no API keys, passwords, or tokens are staged before committing |
| No generated files | `node_modules/`, build artifacts, and compiled CSS are in `.gitignore` |
| Review `git diff` before committing | Always review what you are about to commit |

---

## 4. Pull Request Standards

### PR Title

Same format as commit messages: `type: Short description`

### PR Description

Every PR must include:

```markdown
## What Changed
Brief explanation of the change — what was added, modified, or removed.

## Why
The problem this solves or the requirement it fulfills.

## Pages Affected
List of URLs or page types affected by this change.

## How to Test
1. Navigate to [page URL]
2. Verify [expected behavior]
3. Check mobile viewport
4. Run PageSpeed Insights

## Screenshots
[Before/after screenshots for visual changes — optional but recommended]

## Checklist
- [ ] Code follows CODING_STANDARDS.md
- [ ] No WordPress Core or parent theme files modified
- [ ] Security: inputs validated/sanitized, outputs escaped
- [ ] Performance: no unnecessary queries or asset loads
- [ ] SEO: schema validated, heading hierarchy correct
- [ ] Responsive: tested on desktop, tablet, mobile
- [ ] Documentation updated (if applicable)
- [ ] No secrets or credentials in the commit
```

### PR Review Process

```
PR Opened
    │
    ▼
Automated Checks (GitHub Actions)
    │
    ├── Linting (if configured)
    ├── File validation (no secrets, no core modifications)
    └── Build check (if applicable)
         │
         ▼
Human or AI Review
    │
    ├── Code quality (CODING_STANDARDS.md)
    ├── Security (validation, sanitization, escaping)
    ├── Performance (no regressions)
    ├── SEO (schema, heading, meta)
    └── Standards compliance
         │
         ▼
Approval
    │
    ▼
Squash Merge to main
    │
    ▼
Branch auto-deleted
```

### PR Rules

| Rule | Detail |
|---|---|
| Every change goes through a PR | No direct commits to main — ever |
| One concern per PR | A PR adds one feature, fixes one bug, or makes one improvement |
| Fill in the description | Empty PR descriptions are rejected |
| Respond to review comments | Address every comment — resolve or discuss |
| Squash merge preferred | Produces a clean single commit on main |
| Delete branch after merge | Automated in GitHub repository settings |
| No self-merging on critical changes | Significant architectural changes require a second reviewer |

---

## 5. Releases and Semantic Versioning

### Version Format

```
v{MAJOR}.{MINOR}.{PATCH}
```

| Segment | When to Increment | Example |
|---|---|---|
| MAJOR | Breaking changes to project structure, conventions, or templates | `v1.0.0` → `v2.0.0` |
| MINOR | New features, pages, templates, or capabilities — backward compatible | `v1.0.0` → `v1.1.0` |
| PATCH | Bug fixes, content corrections, documentation updates | `v1.1.0` → `v1.1.1` |

### Pre-1.0 Rules

| Rule | Detail |
|---|---|
| `v0.x.0` releases | May include breaking changes without a major bump |
| `v0.1.0` | Initial framework structure |
| `v0.x.0` | Feature additions during foundation phase |
| `v1.0.0` | First stable production release |

### Release Workflow

```
1. All features for the release are merged to main
2. Update CHANGELOG.md with release notes
3. Update VERSION file with the new version number
4. Commit: "chore: Release v1.2.0"
5. Tag the release:
   └── git tag -a v1.2.0 -m "Release v1.2.0 — Add South Jersey location pages"
   └── git push origin v1.2.0
6. Create GitHub Release from the tag
   └── Copy changelog entries into the release description
7. Deploy to production (tag-triggered or manual dispatch)
```

### CHANGELOG Format

```markdown
## [1.2.0] — 2026-08-01

### Added
- Camden NJ location page with FAQ schema
- Cherry Hill NJ location page with FAQ schema
- Mobile sticky CTA bar for all location pages

### Changed
- Updated hero section padding for mobile consistency
- Improved testimonial slider accessibility

### Fixed
- Header CTA link opening in same tab instead of new tab
- FAQ schema validation error on plumbing service page

### Security
- Added rate limiting to contact form submissions
```

### Release Rules

| Rule | Detail |
|---|---|
| Every production deployment is tagged | No untagged code runs in production |
| CHANGELOG is updated before tagging | The release notes are part of the release commit |
| Tags are annotated | Use `git tag -a` with a message — not lightweight tags |
| Tags are immutable | Never delete or move a tag — if a mistake is tagged, create a new patch release |
| GitHub Release is created | For every tagged release — makes it visible in the repository UI |

---

## 6. GitHub Actions

### Pipeline Architecture

```
.github/
└── workflows/
    ├── deploy-staging.yml       # Auto-deploy to staging on merge to main
    ├── deploy-production.yml    # Deploy to production on tag push or manual trigger
    ├── validate.yml             # Run validation checks on every PR
    └── backup.yml               # Scheduled backup workflow (optional)
```

### Staging Deployment Workflow

```yaml
# .github/workflows/deploy-staging.yml
name: Deploy to Staging

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment: staging

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          port: ${{ secrets.STAGING_PORT }}
          script: |
            cd ${{ secrets.STAGING_PATH }}
            git fetch origin main
            git reset --hard origin/main
            wp cache flush --path=${{ secrets.STAGING_WP_PATH }}
            wp rocket clean --confirm --path=${{ secrets.STAGING_WP_PATH }}

      - name: Health check
        run: |
          STATUS=$(curl -s -o /dev/null -w "%{http_code}" ${{ secrets.STAGING_URL }})
          if [ "$STATUS" != "200" ]; then
            echo "Health check failed — HTTP $STATUS"
            exit 1
          fi
          echo "Health check passed — HTTP 200"
```

### Production Deployment Workflow

```yaml
# .github/workflows/deploy-production.yml
name: Deploy to Production

on:
  push:
    tags: ['v*']
  workflow_dispatch:
    inputs:
      confirm:
        description: 'Type DEPLOY to confirm'
        required: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment: production
    if: >
      github.event_name == 'push' ||
      github.event.inputs.confirm == 'DEPLOY'

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Create pre-deployment backup
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.PRODUCTION_SSH_KEY }}
          port: ${{ secrets.PRODUCTION_PORT }}
          script: |
            cd ${{ secrets.PRODUCTION_PATH }}
            TIMESTAMP=$(date +%Y%m%d_%H%M%S)
            mkdir -p ~/backups
            tar -czf ~/backups/pre-deploy-${TIMESTAMP}.tar.gz \
              --exclude='wp-content/uploads' \
              --exclude='wp-content/cache' \
              .
            wp db export ~/backups/pre-deploy-${TIMESTAMP}.sql \
              --path=${{ secrets.PRODUCTION_WP_PATH }}

      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.PRODUCTION_SSH_KEY }}
          port: ${{ secrets.PRODUCTION_PORT }}
          script: |
            cd ${{ secrets.PRODUCTION_PATH }}
            git fetch origin main
            git reset --hard origin/main
            wp cache flush --path=${{ secrets.PRODUCTION_WP_PATH }}
            wp rocket clean --confirm --path=${{ secrets.PRODUCTION_WP_PATH }}

      - name: Health check
        run: |
          sleep 10
          STATUS=$(curl -s -o /dev/null -w "%{http_code}" ${{ secrets.PRODUCTION_URL }})
          if [ "$STATUS" != "200" ]; then
            echo "CRITICAL: Health check failed — HTTP $STATUS"
            exit 1
          fi
          echo "Health check passed — HTTP 200"

      - name: Notify success
        if: success()
        run: echo "Production deployment successful — $(date)"
```

### Validation Workflow

```yaml
# .github/workflows/validate.yml
name: Validate PR

on:
  pull_request:
    branches: [main]

jobs:
  validate:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Check for secrets in code
        run: |
          if grep -rn "AKIA\|sk_live\|password\s*=" --include="*.php" --include="*.js" --include="*.json" .; then
            echo "FAIL: Possible secrets found in code"
            exit 1
          fi
          echo "PASS: No secrets detected"

      - name: Check for core file modifications
        run: |
          CORE_CHANGES=$(git diff --name-only origin/main | grep -E "^wp-admin/|^wp-includes/" || true)
          if [ -n "$CORE_CHANGES" ]; then
            echo "FAIL: WordPress core files modified"
            echo "$CORE_CHANGES"
            exit 1
          fi
          echo "PASS: No core files modified"

      - name: Verify PHP syntax
        run: |
          find . -name "*.php" -path "*/themes/kadence-child/*" -exec php -l {} \; 2>&1 | grep -i "parse error" && exit 1 || true
          find . -name "*.php" -path "*/mu-plugins/*" -exec php -l {} \; 2>&1 | grep -i "parse error" && exit 1 || true
          echo "PASS: PHP syntax valid"
```

---

## 7. Environment Architecture

### Environment Definitions

| Environment | Purpose | URL | Deployment Trigger |
|---|---|---|---|
| Local | Development and initial testing | `localhost` or `.local` | Manual |
| Staging | Pre-production testing and client review | `staging.domain.com` | Automatic on merge to main |
| Production | Live public-facing website | `domain.com` | Tag push or manual dispatch |

### Environment Parity

Staging must mirror production as closely as possible:

| Element | Must Match |
|---|---|
| PHP version | Identical |
| WordPress version | Identical |
| Plugin versions | Identical |
| Theme version | Identical |
| Server configuration | Same web server (Apache/Nginx), same PHP extensions |
| SSL | Staging must also use HTTPS |
| Database structure | Same schema — content may differ |

| Element | May Differ |
|---|---|
| Domain name | `staging.domain.com` vs `domain.com` |
| Database content | Staging may have test content |
| Email delivery | Staging should intercept outbound emails (use a mail trap) |
| Analytics/tracking | Disabled on staging to avoid polluting data |
| CDN | Optional on staging |

### Environment Configuration

Each environment uses its own `wp-config.php` values. Sensitive values come from environment variables:

```php
// Database — from environment variables
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) );
define( 'DB_USER', getenv( 'WP_DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: 'localhost' );

// Environment identification
define( 'WP_ENVIRONMENT_TYPE', getenv( 'WP_ENV' ) ?: 'production' );

// Debug — enabled on staging, disabled on production
if ( wp_get_environment_type() === 'staging' || wp_get_environment_type() === 'local' ) {
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
} else {
    define( 'WP_DEBUG', false );
}
```

---

## 8. Secrets Management

### What Counts as a Secret

| Category | Examples |
|---|---|
| Database credentials | DB_NAME, DB_USER, DB_PASSWORD, DB_HOST |
| Authentication keys | WordPress salts, API keys, OAuth tokens |
| SSH keys | Deployment private keys |
| Plugin license keys | Rank Math Pro, WP Rocket, Kadence Pro, Elementor Pro |
| Third-party API keys | Google Indexing API, Search Console, Analytics |
| SMTP credentials | Mail server username and password |
| CDN credentials | Cloudflare API token, purge keys |

### Storage Rules

| Location | Allowed | Detail |
|---|---|---|
| GitHub Secrets | Yes | Deployment keys, host credentials, environment variables |
| Server environment variables | Yes | `wp-config.php` reads from `getenv()` |
| `.env` file on server | Yes | Must be outside the web root; never committed to Git |
| `wp-config.php` on server | Yes | Direct values acceptable for non-sensitive config; secrets via `getenv()` |
| Repository files | Never | No secrets in any committed file — no exceptions |
| Commit messages | Never | Do not reference credential values in commit messages |
| PR descriptions | Never | Do not include API keys or passwords in PR text |

### GitHub Secrets Configuration

| Secret Name | Purpose | Environment |
|---|---|---|
| `STAGING_HOST` | Staging server IP or hostname | Staging |
| `STAGING_USER` | SSH username for staging | Staging |
| `STAGING_SSH_KEY` | SSH private key for staging deployment | Staging |
| `STAGING_PORT` | SSH port (default 22) | Staging |
| `STAGING_PATH` | Deployment path on staging server | Staging |
| `STAGING_WP_PATH` | WordPress installation path on staging | Staging |
| `STAGING_URL` | Staging site URL for health checks | Staging |
| `PRODUCTION_HOST` | Production server IP or hostname | Production |
| `PRODUCTION_USER` | SSH username for production | Production |
| `PRODUCTION_SSH_KEY` | SSH private key for production deployment | Production |
| `PRODUCTION_PORT` | SSH port | Production |
| `PRODUCTION_PATH` | Deployment path on production server | Production |
| `PRODUCTION_WP_PATH` | WordPress installation path on production | Production |
| `PRODUCTION_URL` | Production site URL for health checks | Production |

### SSH Key Management

| Rule | Detail |
|---|---|
| Key type | Ed25519 or RSA 4096-bit minimum |
| One key per environment | Staging and production use different key pairs |
| Key rotation | Rotate deployment keys every 12 months |
| Passphrase | Not used for automated deployment keys (GitHub Actions cannot enter passphrases) |
| Server configuration | Deployment user has restricted shell access — only deployment commands |
| Root access | Never use root for deployments — use a dedicated deployment user |

```bash
# Generate a deployment key
ssh-keygen -t ed25519 -C "deploy@project-staging" -f ~/.ssh/deploy_staging -N ""

# Add the public key to the server
ssh-copy-id -i ~/.ssh/deploy_staging.pub deploy@staging-server

# Add the private key to GitHub Secrets as STAGING_SSH_KEY
```

---

## 9. Rollback Strategy

### When to Roll Back

| Situation | Action |
|---|---|
| Site returns 5xx errors after deployment | Immediate rollback |
| Critical functionality broken (forms, phone links, CTA buttons) | Immediate rollback |
| Visual layout severely broken on mobile | Rollback if cannot hotfix within 30 minutes |
| Performance degradation (LCP > 4s) | Investigate first; rollback if not resolvable within 1 hour |
| SEO impact (pages returning 404, schema broken) | Rollback if not fixable within 1 hour |
| Minor visual issue (spacing, color slightly off) | Hotfix — no rollback needed |

### Rollback Methods

#### Method 1: Git Revert (Preferred)

```bash
# Identify the problematic commit
git log --oneline -5

# Revert the commit that caused the issue
git revert abc1234

# Push the revert
git push origin main

# GitHub Actions will auto-deploy the reverted state to staging
# For production — create a hotfix tag
git tag -a v1.2.1 -m "Hotfix: Revert broken hero section"
git push origin v1.2.1
```

#### Method 2: Reset to Previous Tag (Emergency)

```bash
# On the server via SSH
cd /path/to/wordpress
git fetch origin
git checkout v1.1.0    # Previous known-good release
wp cache flush
wp rocket clean --confirm
```

#### Method 3: Database Restore (Last Resort)

```bash
# Restore the pre-deployment database backup
wp db import ~/backups/pre-deploy-20260801_143000.sql --path=/path/to/wordpress

# Restore file backup
cd /path/to/wordpress
tar -xzf ~/backups/pre-deploy-20260801_143000.tar.gz

# Flush caches
wp cache flush
wp rocket clean --confirm
```

### Rollback Rules

| Rule | Detail |
|---|---|
| Pre-deployment backup is mandatory | The production workflow creates a backup before every deploy |
| Test the rollback on staging first | If time permits, verify the rollback procedure on staging |
| Document the incident | Record what went wrong, when, and how it was resolved |
| Notify stakeholders | Inform the team and client (if applicable) about downtime |
| Post-mortem | After resolution, document the root cause and prevention measures |

---

## 10. Backup Strategy

### Backup Schedule

| Backup Type | Frequency | Retention | Storage |
|---|---|---|---|
| Full site (files + database) | Daily (automated) | 30 days | Off-server cloud storage (S3, Google Cloud, or equivalent) |
| Database only | Every 6 hours | 7 days | Server backup directory + off-server |
| Pre-deployment | Before every production deploy | 90 days | Server backup directory |
| Manual snapshot | Before major changes (redesigns, migrations) | Indefinite | Off-server cloud storage |

### Backup Scope

| Included | Excluded |
|---|---|
| `wp-content/themes/kadence-child/` | `wp-content/cache/` |
| `wp-content/plugins/` (custom only) | `node_modules/` |
| `wp-content/mu-plugins/` | `.git/` directory on server |
| `wp-content/uploads/` | Server logs |
| `wp-config.php` | Temporary files |
| Database (full export) | |

### Backup Verification

| Rule | Detail |
|---|---|
| Quarterly restore test | Restore a backup to a test environment at least once per quarter |
| Verify database integrity | Confirm the restored database is readable and complete |
| Verify file integrity | Confirm uploads, theme, and plugins are intact |
| Document test results | Record the date, backup used, and outcome |

---

## 11. Health Checks and Monitoring

### Post-Deployment Health Checks

Automated checks run immediately after every deployment:

| Check | Method | Pass Condition |
|---|---|---|
| HTTP status | `curl -s -o /dev/null -w "%{http_code}" {URL}` | Returns `200` |
| SSL certificate | `curl -sI https://{domain}` | Valid certificate, no errors |
| Homepage loads | Fetch homepage HTML | Contains expected `<title>` tag |
| Critical page spot-check | Fetch 3 key pages | All return `200` |
| WP-CLI status | `wp core is-installed` | Returns success |

### Ongoing Monitoring

| Metric | Tool | Frequency | Alert Threshold |
|---|---|---|---|
| Uptime | UptimeRobot, Pingdom, or equivalent | Every 5 minutes | Any downtime > 1 minute |
| SSL expiration | Monitoring tool or cron job | Daily | < 14 days until expiration |
| Core Web Vitals | Google Search Console CWV report | Weekly review | Any metric in "Poor" category |
| Crawl errors | Google Search Console | Weekly review | Any new 5xx or critical 4xx errors |
| Disk space | Server monitoring or cron job | Daily | > 85% usage |
| PHP errors | `wp-content/debug.log` (staging/development only) | On each deployment | Any new fatal errors |
| Plugin updates available | WordPress admin or ManageWP | Weekly | Security updates pending > 48 hours |

### Monitoring Rules

| Rule | Detail |
|---|---|
| Uptime monitoring is mandatory | Every production site must have automated uptime monitoring |
| Alert notifications | Alerts sent to email and/or Slack within 1 minute of detected downtime |
| SSL monitoring | Automated alert 14 days before certificate expiration |
| Disable debug logging in production | `WP_DEBUG_LOG` must be `false` in production — log files can expose sensitive data |
| Review logs after deployments | Check server error logs within 1 hour of any production deployment |

---

## 12. Incident Response

### Severity Levels

| Level | Definition | Response Time | Examples |
|---|---|---|---|
| **P1 — Critical** | Site is down, data is compromised, or core functionality is broken | Immediate (< 15 minutes) | 500 errors, site hacked, database down |
| **P2 — High** | Major feature broken but site is accessible | Within 1 hour | Forms not submitting, phone links broken, payment failure |
| **P3 — Medium** | Noticeable issue but site is functional | Within 4 hours | Layout broken on one page, mobile menu not closing |
| **P4 — Low** | Minor cosmetic issue | Next business day | Spacing off, wrong font weight, minor alignment |

### Incident Response Workflow

```
Incident Detected (monitoring alert or user report)
    │
    ▼
Assess Severity (P1 / P2 / P3 / P4)
    │
    ├── P1: Immediate response
    │   ├── Attempt quick fix (< 10 minutes)
    │   ├── If not fixable → rollback immediately
    │   └── Notify stakeholders
    │
    ├── P2: Investigate within 1 hour
    │   ├── Identify root cause
    │   ├── Hotfix branch → PR → expedited merge → deploy
    │   └── If risk is high → rollback first, then fix
    │
    ├── P3: Investigate within 4 hours
    │   └── Standard fix branch → PR → merge → deploy
    │
    └── P4: Schedule for next maintenance window
         └── Standard workflow
              │
              ▼
Post-Incident Documentation
    │
    ├── What happened
    ├── When it was detected
    ├── Root cause
    ├── Resolution
    ├── Duration of impact
    └── Prevention measures
```

### Post-Incident Report Template

```markdown
## Incident Report — [Date]

### Summary
Brief description of the incident.

### Timeline
- HH:MM — Incident detected via [source]
- HH:MM — Investigation began
- HH:MM — Root cause identified
- HH:MM — Fix deployed / rollback executed
- HH:MM — Confirmed resolved

### Root Cause
What caused the incident.

### Impact
- Pages affected: [list]
- Duration: [minutes/hours]
- Users impacted: [estimated]

### Resolution
What was done to fix it.

### Prevention
What changes will prevent this from happening again.
```

---

## 13. Files That Must Never Be Deployed

| Path / Pattern | Reason |
|---|---|
| `.git/` | Repository metadata |
| `.github/` | CI/CD configuration |
| `.env` | Environment secrets |
| `node_modules/` | Development dependencies |
| `package.json` / `package-lock.json` | Build tooling |
| `composer.json` / `composer.lock` | PHP dependency manifest |
| `vendor/` (if using Composer) | PHP dependencies — managed separately |
| `README.md`, `CONTRIBUTING.md` | Repository docs, not site files |
| `CHANGELOG.md`, `VERSION` | Repository metadata |
| `tests/` | Test files |
| `*.sql` | Database dumps |
| `*.tar.gz`, `*.zip` | Backup archives |
| `wp-content/debug.log` | Debug log — sensitive information |
| `wp-content/cache/` | Generated cache — rebuild on server |
| `*.map` files | Source maps — not for production (unless debugging) |

### .gitignore

```gitignore
# WordPress Core (managed via updates, not Git)
wp-admin/
wp-includes/
wp-*.php
xmlrpc.php
index.php
license.txt
readme.html

# Uploads (managed outside Git)
wp-content/uploads/

# Cache
wp-content/cache/
wp-content/advanced-cache.php
wp-content/wp-cache-config.php

# Third-party plugins (managed via updates)
wp-content/plugins/*
!wp-content/plugins/waif-*/

# Kadence parent theme (managed via updates)
wp-content/themes/kadence/

# Environment and secrets
.env
.env.*
wp-config.php

# Node and build
node_modules/
package-lock.json
dist/
build/

# OS files
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.swp

# Logs
*.log
wp-content/debug.log

# Backups
*.sql
*.tar.gz
*.zip
```

---

## 14. AI Deployment Rules

### Before Any Deployment Task

1. Read this document (`DEPLOYMENT_STANDARDS.md`) in full
2. Verify changes are committed and pushed — no local-only modifications
3. Verify a PR has been opened, reviewed, and merged
4. Verify no secrets exist in the codebase

### Mandatory Rules for AI Assistants

| Rule | Detail |
|---|---|
| Never deploy directly to production | All deployments flow through the pipeline: branch → PR → merge → GitHub Actions |
| Never commit secrets | Check every file for API keys, passwords, and tokens before staging a commit |
| Never modify WordPress Core files | Validation workflow will catch this — but prevent it at the source |
| Never skip the PR process | Every change — no matter how small — goes through a Pull Request |
| Never force push to main | Force push rewrites history and breaks the deployment pipeline |
| Always create a branch | Work happens on feature branches — never directly on main |
| Always write meaningful commit messages | Follow the commit format: `type: description` |
| Always fill in the PR description | Include what changed, why, pages affected, and how to test |
| Always verify staging before production | If staging deployment succeeds, verify visually and functionally |
| Always tag production releases | Every production deployment has a semantic version tag |
| Always update CHANGELOG.md | Release notes are part of the release |
| Always check health after deployment | Verify the site returns HTTP 200 and key pages load correctly |
| Never deploy on Friday afternoon | If something breaks, weekend response is slower — deploy Monday through Thursday |

### AI-Assisted Deployment Checklist

When an AI assistant is involved in preparing a deployment:

1. Verify all code changes follow `CODING_STANDARDS.md`
2. Verify SEO compliance per `SEO_STANDARDS.md`
3. Verify plugin compliance per `PLUGIN_STANDARDS.md`
4. Verify no hardcoded URLs, paths, or credentials
5. Verify `.gitignore` is correct — no secrets or generated files staged
6. Verify the commit message follows the format
7. Verify the PR description is complete
8. Generate the CHANGELOG entry for the release

---

## 15. QA Checklist

### Pre-Deployment — Every Release

**Git and Process**

- [ ] All changes committed and pushed — no uncommitted local modifications
- [ ] PR opened, reviewed, and approved
- [ ] PR merged to main via squash merge
- [ ] Branch deleted after merge
- [ ] No secrets in the codebase (`grep` check passed)
- [ ] No WordPress Core files modified
- [ ] No parent theme files modified
- [ ] `.gitignore` is correct

**Staging Verification**

- [ ] Staging deployment completed successfully
- [ ] Site loads over HTTPS on staging
- [ ] Homepage returns HTTP 200
- [ ] 3+ key interior pages load correctly
- [ ] Forms submit correctly
- [ ] Mobile rendering verified
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug log

**Production Release**

- [ ] CHANGELOG.md updated
- [ ] VERSION file updated
- [ ] Release tagged with semantic version
- [ ] GitHub Release created
- [ ] Pre-deployment backup created automatically
- [ ] Production deployment completed
- [ ] Health check passed (HTTP 200)
- [ ] Cache purged (WP Rocket + CDN)
- [ ] 3+ key pages verified on production
- [ ] SSL certificate valid
- [ ] Monitoring confirms uptime

---

## 16. Common Deployment Mistakes

| Mistake | Why It Happens | Correct Approach |
|---|---|---|
| Deploying via FTP | Old habits, perceived as faster | Never — all deployments through Git + GitHub Actions |
| Committing `wp-config.php` with credentials | Auto-staging picks it up | `wp-config.php` is in `.gitignore`; credentials from environment variables |
| Pushing directly to main | "Quick fix" urgency | Always branch → PR → merge — even for one-line changes |
| Not testing on staging first | Overconfidence or time pressure | Staging test is mandatory — no exceptions |
| Deploying on Friday at 5pm | Poor planning | Deploy Monday through Thursday during working hours |
| No pre-deployment backup | Assumed hosting handles it | Pipeline creates backup automatically — verify it exists |
| No rollback plan | "It will be fine" | Every deployment must have a documented rollback path |
| Force pushing to main | Fixing a bad merge | Never force push — revert the commit properly |
| Committing `node_modules/` | Forgot to check `.gitignore` | Verify `.gitignore` includes `node_modules/` before first commit |
| Deploying with debug mode enabled | `WP_DEBUG` left as `true` | Production must always have `WP_DEBUG` set to `false` |
| Not clearing cache after deployment | Changes invisible to visitors | Pipeline includes WP Rocket cache flush — verify it runs |
| Using root SSH user for deployment | Default server setup | Create a dedicated deployment user with restricted permissions |
| Not monitoring after deployment | "Deploy and forget" | Health check runs automatically; manual verification within 1 hour |
| Mixing staging and production credentials | Copy-paste error | Separate GitHub Secrets per environment — separate SSH keys, separate database credentials |
| Not rotating SSH keys | Created once, used forever | Rotate deployment keys every 12 months |
| Skipping the CHANGELOG | "Nobody reads it" | CHANGELOG is the deployment history — mandatory for every release |

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — complete deployment standards defined |