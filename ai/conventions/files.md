# files.md

> **This file is machine context, not human documentation.**
> Every AI assistant must consult this file for file-level size and documentation-header expectations. For naming, see `naming.md`. For placement, see `folders.md`. This file does not repeat either.

---

## Purpose

Covers what `naming.md` (casing/patterns) and `folders.md` (placement) don't: when a file has grown too large, and what documentation header it needs. Kept intentionally short — most of "file organization" is already answered by those two files.

---

## Size Thresholds

| File type | Refactor when... | Source |
|---|---|---|
| PHP function | Exceeds 30 lines of logic | `CODING_STANDARDS.md` §3 |
| PHP file (`inc/*.php`) | Covers more than one concern (e.g. hooks mixed with helpers) | `CODING_STANDARDS.md` §1, `WORDPRESS_STANDARDS.md` §5 |
| CSS file (component) | Exceeds ~200 lines or covers more than one component | `CODING_STANDARDS.md` §6 |
| JS file (bundle) | Exceeds 50 KB gzipped | `CODING_STANDARDS.md` §7 |
| Row Layout / container nesting | Exceeds 3 levels deep | `KADENCE_STANDARDS.md` §9, `ELEMENTOR_STANDARDS.md` §4 |

## Documentation Header Requirements

| File type | Required header |
|---|---|
| PHP (theme/plugin file) | `@package`, `@since`, one-line purpose — see example in `CODING_STANDARDS.md` §2 |
| PHP (public function/method) | PHPDoc with params, return type, `@since` |
| JS (exported function) | JSDoc |
| Markdown (standards doc) | Document Information table (Version, Status, Owner, Audience, Last Updated) — see any file in `ai/` root for the pattern |

## AI Rules

- Check `naming.md` for what to call a file, `folders.md` for where it goes, this file only for when to split it and what header it needs.
- If a file exceeds its threshold, split it — don't just note the violation and move on.
- Do not duplicate the tables in `naming.md` or `folders.md` here — if something looks missing, it likely belongs in one of those files instead.
