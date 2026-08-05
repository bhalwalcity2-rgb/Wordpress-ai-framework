# Changelog

All notable changes to the WordPress AI Framework are documented in this file.

The format follows the structure defined in `ai/DEPLOYMENT_STANDARDS.md` §5.

---

## [0.1.0] — 2026-08-05

### Added
- Initial framework structure: `ai/`, `docs/`, `templates/`, `scripts/`, `wordpress/` directories
- Full AI-facing standards documentation (`PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md`, `SEO_STANDARDS.md`, `KADENCE_STANDARDS.md`, `ELEMENTOR_STANDARDS.md`, `PLUGIN_STANDARDS.md`, `DEPLOYMENT_STANDARDS.md`)
- Convention and quick-reference files under `ai/conventions/` and `ai/memory/`
- Initial Kadence child theme scaffold (`wordpress/themes/kadence-child-framework/`)

### Fixed
- Moved `functions.php` from `templates/` to the child theme root so WordPress can load it
- Replaced placeholder `CHANGELOG.md` and `VERSION` directories with real files
- Removed duplicate `screenshot.png` from the theme's `templates/` directory
