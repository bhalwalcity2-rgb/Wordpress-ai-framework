# Elementor Usage: Homepage

| Field | Value |
|---|---|
| Status | Accepted |
| Date | 2026-09-19 |
| Page | `/` |
| Builder | Elementor Flexbox Containers |

## Decision

The homepage body is managed in Elementor and stored in WordPress. The
child theme continues to provide the header, footer, global design tokens,
SEO integration, and performance controls.

## Justification

The site owner requires direct, database-backed editing through the
WordPress MCP connection. The prior homepage was assembled entirely by
`front-page.php` and configuration files, so routine content and layout
changes required a GitHub deployment. Elementor's editable container
structure makes those updates possible without modifying theme files.

## Constraints

- Use Flexbox Containers only; do not create legacy Elementor sections.
- Keep the custom header and footer outside Elementor.
- Use theme typography and do not load Elementor Google Fonts.
- Use optimized images with descriptive alternative text and explicit sizes.
- Keep one H1 and maintain a logical heading hierarchy.
