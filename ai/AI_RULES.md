# AI_RULES.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Audience | Every AI assistant working in this repository |
| Scope | Permanent, framework-agnostic rules only |

> This file does not repeat content that already lives in another standards document, and it does not contain assistant-specific or session-specific workflow behavior. For Claude's workflow (planning, approval gates, communication style), see `ai/CLAUDE.md`.

---

## 1. What This File Is

The top-level behavioral contract for any AI assistant operating in this repository. The standards documents already each contain their own domain-specific "AI Rules" section — this file is the index that ties them together, plus a small number of cross-cutting rules that don't belong to any single document.

## 2. Reading Order

Before any task, in order:

1. `PROJECT_CONTEXT.md` — architecture, agency context, constraints
2. This file
3. The standards document relevant to the task (§3 below)
4. `ai/conventions/` and `ai/decisions/` — check for existing conventions or prior rulings on the topic
5. The assistant-specific file (`ai/CLAUDE.md`, `ai/CHATGPT.md`, `ai/CODEX.md`), if one exists for the assistant performing the task

## 3. Which Document Governs Which Task

| Task involves... | Read |
|---|---|
| WordPress structure, theme/plugin boundaries | `WORDPRESS_STANDARDS.md` |
| Writing PHP, CSS, or JS | `CODING_STANDARDS.md` |
| Page content, metadata, or schema | `SEO_STANDARDS.md` |
| Layout, blocks, theme settings | `KADENCE_STANDARDS.md` |
| A page that may need Elementor | `ELEMENTOR_STANDARDS.md` |
| Installing or evaluating a plugin | `PLUGIN_STANDARDS.md` (quick reference: `ai/memory/approved-plugins.md`) |
| Git, CI/CD, releases | `DEPLOYMENT_STANDARDS.md` |
| Naming anything | `ai/conventions/naming.md` |
| Where a file belongs | `ai/conventions/folders.md` |

Each of these documents has its own "AI Rules" / "Mandatory Rules" section specific to that domain. Follow those directly — they are not reproduced here.

## 4. Absolute Constraints

Apply regardless of task. Full reasoning already lives in `PROJECT_CONTEXT.md` §15 and `WORDPRESS_STANDARDS.md` §1 — not repeated here:

- Never modify WordPress Core, the Kadence parent theme, or third-party plugin files.
- Never introduce a technology, plugin, or library outside the approved stack (`ai/memory/tech-stack.md`, `ai/memory/approved-plugins.md`) without a recorded decision.
- Never invent a convention that already exists — check `ai/conventions/` first.
- Never skip documentation when making a structural or architectural change — record it in `ai/decisions/` (see `ai/decisions/README.md`).

## 5. Conflict Resolution

If two documents conflict, follow `PROJECT_CONTEXT.md` §6 ("Conflict Resolution"). If a specific standards document states its own precedence rule (most end with a line such as "if this file conflicts with X, the more detailed document takes precedence"), that local rule wins within that document's scope.

## 6. Assistant Roles

Role assignments — who owns architecture, content, code generation, research, IDE work — are defined once in `PROJECT_CONTEXT.md` §6. Not repeated here.

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-05 | Initial document — framework-agnostic AI rules established |
