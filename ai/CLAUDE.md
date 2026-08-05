# CLAUDE.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Audience | Claude, when operating in this repository |

> This file defines *how* Claude works in this repository — planning, approval, and communication behavior. It does not define *what* is architecturally allowed; that's `AI_RULES.md` and the standards documents, which apply equally to every assistant. Claude's role assignment (architecture, code review, documentation, planning) is defined once in `PROJECT_CONTEXT.md` §6 — not repeated here.

---

## 1. Planning and Approval

- Never modify a file without first presenting a short implementation plan and getting explicit approval, unless the user has already approved that exact plan in the same turn.
- A plan lists the exact files to be touched. For each: purpose, why it's needed, what's currently wrong (if fixing something), and the intended final state.
- If a task requires a new architectural decision — not just applying an existing standard — explain the decision and alternatives before implementing, and record it in `ai/decisions/` per `ai/decisions/README.md`.
- Never introduce a new technology, plugin, or library without explicit approval, even if it would be the technically correct WordPress solution — flag it and ask first.

## 2. Implementation

- Reuse existing code, patterns, and documentation before writing anything new. Check `templates/`, `ai/conventions/`, and the relevant `inc/` files first.
- Never duplicate functionality that already exists elsewhere in the codebase or documentation — extend or reference it instead.
- Keep every file focused on a single responsibility, per `CODING_STANDARDS.md` §1.
- Follow `CODING_STANDARDS.md` and WordPress Coding Standards for all code output.

## 3. After Every Completed Task

Report:

- Files changed (created, modified, moved, deleted)
- Why each file was changed
- Any follow-up recommendations

## 4. When Uncertain

Ask rather than assume — particularly for naming conflicts, ambiguous scope, or anything not covered by an existing standards document.

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-05 | Initial document — Claude's workflow behavior formalized from session-established rules |
