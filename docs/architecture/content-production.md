# Content Production Pipeline

| Field | Value |
|---|---|
| Status | Phase 3C-3 — 2026-09-05 |
| Decision | [ADR-0007](../../ai/decisions/ADR-0007.md) |
| Contracts | `production-brief.schema.json`, `draft.schema.json` |
| Gate | `content-engine/lib/briefing.py` |
| Self-test | `content-engine/bin/selftest-pipeline.py` |

Stages 5–8 of the engine. Research (3C-1/3C-2) supplies the evidence; this turns it into a brief and a reviewable draft. Nothing here publishes.

```
research → intent → entities → queries → APPROVED ANGLE
   → [ SEMANTIC BRIEF → DRAFT ] ← this phase
   → claim extraction → sibling comparison → content QA → publish gate
```

---

## 1. Workflow

| Step | Command |
|---|---|
| 1. Create brief | `python content-engine/bin/create-content-brief.py <slug> [--all] [--force]` |
| 2. Validate brief | `python content-engine/bin/validate-content-brief.py [slug] [--strict]` |
| 3. Write | `python content-engine/bin/write-content.py <slug> [--all] [--force]` |
| 4. Validate draft | `python content-engine/bin/validate-draft.py [slug] [--strict]` |
| — | `python content-engine/bin/selftest-pipeline.py` |

Standard library only, matching the existing `bin/` scripts. No new dependency (`AI_RULES.md` §4).

---

## 2. Two kinds of brief

| | Retro-brief (3B) | Production brief (3C-3) |
|---|---|---|
| Path | `content-engine/brief/{slug}.json` | `content-engine/brief/production/{slug}.json` |
| Describes | what the page **already does** | what a page **should do** |
| Role | analytical input | the writer's instruction contract |
| Written by | `analyze-corpus.py` | `create-content-brief.py` |

Retro-briefs are inputs and are **never overwritten**. A brief describing the current page would instruct the writer to reproduce its weaknesses.

---

## 3. The page-existence gate

A brief is `ALLOWED` only when every check passes:

| Check | Blocks with |
|---|---|
| `differentiation_supported` | `NO_SUPPORTED_DIFFERENTIATION` |
| `approved_angle_exists` | `NO_APPROVED_ANGLE` |
| `angle_belongs_to_page` | — |
| `angle_not_superficial` | — |
| `publishable_claims_exist` | — |
| `high_risk_claims_authoritative` | — |
| `information_gain_establishable` | `NO_ESTABLISHABLE_INFORMATION_GAIN` |
| `angle_not_blocked` | `ANGLE_BLOCKED_BY_OPEN_RESEARCH` |

Blocked briefs are still written, carrying a required action: merge, broaden, repurpose, or exclude. The gate runs again inside `write-content.py` — a brief is a file, and a file can be edited.

**Current state: 0 of 12 pages pass.** 8 `NO_SUPPORTED_DIFFERENTIATION`, 4 `NO_APPROVED_ANGLE`.

---

## 4. What the writer receives — and never receives

**Receives:** the approved angle and why it is unique; allowed claims (boundary statements where a claim was narrowed); the query model as concepts with coverage levels; typed entity relationships with evidence levels; sibling constraints; information-gain requirements; internal-link reasons; forbidden patterns.

**Never receives:** sibling page text. A sibling is the most dangerous input available — the fastest route to a passable page is to paraphrase one. Siblings appear only as constraints: structures to avoid, questions already answered.

**Never receives:** a section template. Structure is derived per page. Identical structure was the strongest templating signal in the corpus (50 of 68 pairs), so `recommended_sections` carries working notes and purposes, not headings.

### Claims are the entire factual budget

Only ids in `allowed_claim_ids` may be stated as fact. Where a claim was narrowed in 3C-2, the package supplies the **boundary statement** rather than the original — the Nevada duplicate-title fee reaches the writer as "$20 title fee plus $8.25 processing = $28.25", not the "$20 fee" the live page asserts.

---

## 5. The writer

The repository takes on no AI API, so `write-content.py` is the control layer, not the author. It re-runs the gate, assembles the authoring package, and writes `content-engine/drafts/{slug}.json` with status `authoring_package`. An author then fills the `content` block against that package, and `validate-draft.py` checks the result.

The split is deliberate: constraints are enforced by code that cannot be talked out of them, and prose is written by something that can read.

**Drafts never leave `content-engine/drafts/`.** Promotion belongs to the publish gate (3C-4). A draft written where the renderer reads is published, whatever it is called.

---

## 6. Validation

**Brief:** gate consistency · angle exists, belongs to the page, is approved, is not claimed by another brief · allowed claims are actually publishable · no claim both allowed and blocked · information gain exists and is not place-name substitution · query model, entity relationships and sibling constraints non-empty · no hypothesis cited as established · source artifacts exist.

**Draft:** canonical v2 conformance (same contract production content must satisfy) · business identity tokenised · no claim outside the allowed list · angle still approved · `must_answer` concepts covered · no question the brief reserved to a sibling · **structure similarity ≥ 0.85 to any sibling is an error**, ≥ 0.70 a warning · draft is in `drafts/` only.

Thresholds come from the Phase 3B corpus and travel in the brief as calibrated editorial signals — re-measure after any rewrite rather than inheriting them by habit.

---

## 7. Self-test

`selftest-pipeline.py` asserts both halves, because "it refuses everything" is also true of a pipeline that is simply broken:

- **Refusal** — the real corpus is refused, reports `NO_APPROVED_ANGLE_FOR_END_TO_END_GENERATION`, and every block carries an action.
- **Allowed path** — a clearly-marked fixture angle and claim are injected, the full brief → validate → write → author → validate chain runs green, then every touched file is restored in a `finally` block.
- **Negative paths** — a claim the brief did not allow, a literal phone number, an angle disagreeing with the brief, and a structure cloned from a sibling are each rejected.

No fixture survives the run, and no page is approved in the repository as a side effect.

Current result: **13/13**.
