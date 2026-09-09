# content-engine/

> Machine and human context. Approved by [ADR-0002](../ai/decisions/ADR-0002.md); the content contract it enforces is [ADR-0003](../ai/decisions/ADR-0003.md).

The content supply chain that runs **before commit**. Nothing here is ever deployed — `deploy-lvjcb.yml` syncs only the theme directory.

The engine now spans the full chain: corpus analysis, research and evidence, semantic briefing, a controlled writer, and the publish gate. What it does **not** do is deploy — promotion changes the repository, and the existing GitHub Actions / FTPS pipeline ships it.

---

## Why this exists

Two incompatible content schemas were live at the same time. The renderer had been upgraded to read `paragraphs[]`; eleven of twelve content files still stored prose under `body`. Nothing mapped one to the other and nothing noticed, so those pages published their headings with the paragraphs silently missing — about 3,200 words that existed in the repository and reached no page.

Separately, eleven files carried a hardcoded business name and phone number that were not the business's. Both reached production and were served in `<title>` and `<meta name="description">`.

Neither failure was exotic. Both were invisible because no check existed. That is what this directory is for.

---

## Layout

```
content-engine/
├── config/schema/   JSON Schema contracts (content, research, brief, draft)
├── config/          migration-allowlist.json
├── lib/             jsonschema_mini, textstats, corpus, research, briefing, promotion
├── bin/             validators, pipeline stages, publish gate, self-tests
├── brief/           Phase 3B retro-briefs · brief/production/ writer contracts
├── place|claims|angles|intent|entities|queries/   research artifacts
├── sources/         shared source registry
├── approvals/       deliberate human approval records
├── drafts/          writer output, pre-promotion
└── reports/         corpus-summary · differentiation/ · promotions/ · live/
```

---

## The production pipeline

```
research -> brief -> draft -> PROMOTION -> deploy -> live verification
```

| Stage | Command |
|---|---|
| Analyse the corpus | `analyze-corpus.py` |
| Scaffold research | `bootstrap-research.py` |
| Validate research | `validate-research.py` |
| Create a brief | `create-content-brief.py <slug>` |
| Validate the brief | `validate-content-brief.py [slug]` |
| Assemble the authoring package | `write-content.py <slug>` |
| Validate the draft | `validate-draft.py [slug]` |
| **Promote** | `publish-gate.py <slug> [--update] [--dry-run] [--check]` |
| Verify provenance | `verify-content-provenance.py [--against origin/main]` |
| Verify the live page | `verify-live.py <slug> --base-url https://...` |
| Self-tests | `selftest-pipeline.py`, `selftest-publish-gate.py` |

`publish-gate.py` is the only sanctioned path into a theme's `content/`, it requires a deliberate approval record naming the draft's hash, and it does not deploy — see `docs/architecture/publish-gate.md`.

## Running it

```bash
python content-engine/bin/validate-content.py                    # defaults to kadence-child-lvjcb
python content-engine/bin/validate-content.py <theme-slug>
python content-engine/bin/validate-content.py --strict           # warnings fail too
```

Exit status: `0` clean · `1` errors · `2` warnings under `--strict`.

Runs in CI on every pull request and every push to `main` via `.github/workflows/validate.yml`.

---

## What it checks

| Level | Check |
|---|---|
| Error | Malformed JSON |
| Error | Schema violation — missing required field, unsupported `sections[].type`, superseded `body` key |
| Error | `slug` disagrees with the filename |
| Error | An `areas` / `services` entry, or a table cell's `service`, names a slug absent from `business-config.php` |
| Error | Literal business name, phone number, or address anywhere in a content file |
| Warning | Deprecated key still present (`internal_links`) |
| Warning | A content file exists for the primary city, which has no location page |
| Warning | `seo_title` over 60 or `seo_description` over 160 characters once tokens resolve |
| Info | Configured pages with no content file — these render the templated fallback |

Length limits are warnings on purpose. They are copy problems, and failing the build on them would have blocked freezing the contract behind an editorial rewrite.

---

## Business identity is tokenised

Content files must never contain the business name, phone number, or address as literals. Write:

- `{business}` → `business_name`
- `{phone}` → `phone_display`

`lvjcb_resolve_content_tokens()` in `inc/content-loader.php` resolves both once at load, so every downstream consumer — SEO titles, meta descriptions, Rank Math provisioning, and the templates — receives real values. `business-config.php` stays the single source of truth, and a changed phone number cannot leave stale copies behind in a dozen JSON files.

---

## No dependencies

Python 3 standard library only. `jsonschema` is not installed and must not be added (`AI_RULES.md` §4, `PROJECT_CONTEXT.md` §15). `lib/jsonschema_mini.py` implements the draft-07 subset the contracts use.

It adds two documented keywords:

| Keyword | Purpose |
|---|---|
| `selectOn` / `select` | Discriminated union. Standard `oneOf` can only report *"matched none of 4 schemas"*; this picks the branch by `type` first, so a section one field short is reported as exactly that. A standard validator ignores unknown keywords and still validates the schema, more weakly, rather than erroring. |
| `x-deprecated` | Marks a still-permitted key as superseded — warns instead of failing. This is what allowed the contract to be frozen before the sweep that removes `internal_links`. |

---

## Scope

**Built:** content contract and validator · corpus analysis and differentiation measurement · research, evidence and claim provenance · angle registry · semantic production brief · controlled writer · publish gate · live verification · CI enforcement.

**Current state:** nothing has been promoted. The corpus holds no approved angle, so the brief gate, the writer and the publish gate all refuse every page. That is the system working — see `docs/architecture/publish-gate.md`.

The extension point is `bin/`: each stage is a separate executable writing a separate artifact, so stages can be added without disturbing the contract.

**Explicitly excluded, permanently:** AI-detection scoring. It penalises clear prose and measures nothing about whether a page is accurate, original, locally relevant, or useful.
