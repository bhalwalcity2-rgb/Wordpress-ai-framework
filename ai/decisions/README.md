# ai/decisions/ — Architectural Decision Records

> Machine and human context. Read before writing a new decision record.

## Purpose

Records *why* a non-obvious architectural choice was made, so it isn't relitigated by a future session (human or AI) that lacks the original context. Referenced as a requirement in `KADENCE_STANDARDS.md`, `ELEMENTOR_STANDARDS.md`, and `PLUGIN_STANDARDS.md`.

## When to Write One

Write an ADR when:

- Choosing Elementor over Kadence for a specific page (mandatory per `ELEMENTOR_STANDARDS.md` §2)
- Approving a plugin outside the core stack (mandatory per `PLUGIN_STANDARDS.md` §4)
- Introducing a new top-level directory or restructuring an existing one (mandatory per `ai/conventions/folders.md`)
- Making any other choice a future session would otherwise have to re-derive, or could reverse without knowing why

Do not write one for routine work already covered by an existing standards document.

## Naming Convention

```
ai/decisions/ADR-{NNNN}.md
```

Sequential, zero-padded to 4 digits, never reused: `ADR-0001.md`, `ADR-0002.md`, etc. A superseded decision gets a new ADR that references the old one — it does not reuse or delete the old number.

## Required Sections

| Section | Content |
|---|---|
| Status | `Proposed` / `Accepted` / `Superseded by ADR-XXXX` |
| Date | `YYYY-MM-DD` |
| Decision | The single sentence stating what was decided |
| Context | The problem that required a decision |
| Alternatives Considered | What else was evaluated and why it was rejected |
| Consequences | What this decision commits the framework to |

Keep each ADR short — a decision record, not a design document. Link to the relevant standards document for implementation detail rather than repeating it.

## AI Rules

- Check this directory before making an architectural call — the decision may already exist.
- Write a new ADR immediately when making one of the decisions listed above; do not defer it.
- Never edit a past ADR's Decision/Context/Alternatives after it's Accepted — supersede it with a new ADR instead.
