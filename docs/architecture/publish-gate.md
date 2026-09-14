# Publish Gate and Live Verification

| Field | Value |
|---|---|
| Status | Phase 3C-4 — 2026-09-06 |
| Decision | [ADR-0008](../../ai/decisions/ADR-0008.md) |
| Gate | `content-engine/bin/publish-gate.py` |
| Live verifier | `content-engine/bin/verify-live.py` |
| Provenance | `content-engine/bin/verify-content-provenance.py` |
| Self-test | `content-engine/bin/selftest-publish-gate.py` |

Closes the pipeline: research → brief → draft → **promotion** → deploy → **live verification**.

---

## 1. Promotion is not deployment

Two different jobs, deliberately separate:

| | Does | Does not |
|---|---|---|
| **Publish gate** | Copies validated content into `wordpress/themes/{theme}/content/` and writes an audit record | Connect to WordPress, touch FTPS, or upload anything |
| **GitHub Actions + FTPS** | Ships whatever the repository contains | Decide whether content is fit to ship |

The gate changes the repository. The existing deployment pipeline is untouched.

---

## 2. Draft lifecycle

```
authoring_package → drafted → (validated) → PROMOTED → live-verified
                                    ↑
                          human approval required here
```

The draft is never modified or deleted by promotion. It stays in `content-engine/drafts/` as the audit counterpart to the record.

---

## 3. Prerequisites — all re-run at promotion time

A draft's `status`, and a brief's gate verdict, are just fields in files. Trusting them means trusting whoever edited them last, so the gate re-derives everything from the registries:

| Group | Checks |
|---|---|
| Artifacts | draft exists · production brief exists |
| Re-validation | brief validates · draft validates · research validates |
| Existence | page-existence gate `ALLOWED` |
| Angle | exists · belongs to this page · status `approved`/`used` · not superficial · **not claimed by another page** |
| Claims | no blocked claim used · every used claim allowed · all publishable · high-risk authoritatively sourced |
| Content | authored · slug matches · draft angle matches brief · required metadata present |
| Identity | business name, phone and address tokenised |
| Coverage | `must_answer` concepts covered · information gain declared |
| Differentiation | structure similarity to any sibling < 0.85 |
| Links | every internal link slug resolves in `business-config.php` |
| Destination | resolved path inside the theme's content directory |

Any failure: no promotion, exit 1, reasons printed, **draft untouched**.

---

## 4. Approval is separate from QA

Passing every check is not permission. Promotion additionally requires `content-engine/approvals/{slug}.json`:

```json
{ "slug": "...", "decision": "approved", "approved_by": "...",
  "approved_on": "YYYY-MM-DD", "draft_sha256": "...", "note": "..." }
```

The approval names **the exact draft hash**. Re-author the draft and the hash changes, the approval stops applying, and a person has to look again. Approval attaches to one artifact, never to a page in general.

---

## 5. Modes

| Mode | Behaviour |
|---|---|
| default (new) | Refuses if the destination already exists |
| `--update` | Replaces deliberately; records the previous hash |
| `--dry-run` | Reports what would happen, writes nothing |
| `--check` | Prerequisites and approval only |

`--dry-run` still respects the overwrite guard: dry-running over an existing file needs `--update` too, because that is the truth about what promotion would require.

---

## 6. Page classes

| Class | Destination | Live URL |
|---|---|---|
| Primary city | — **refused** | `/` (homepage) |
| Secondary location | `content/locations/{slug}.json` | `/service-areas/{slug}/` |
| Service | `content/services/{slug}.json` | `/cash-for-junk-cars/{slug}/` |
| Homepage | `content/pages/home.json` | `/` |

Promoting a location file for the primary city is refused outright: the homepage owns that intent and the page does not exist (ADR-0004/ADR-0005). URL rules mirror `lvjcb_get_location_url()` rather than restating a convention.

---

## 7. Audit records

`content-engine/reports/promotions/{slug}-{date}.json` plus `{slug}.latest.json`: brief version and path, draft path and hash, angle id, claim ids, source artifacts, approver, every QA check with its result, destination, destination hash, previous destination hash, and mode.

Hashes identify *what* was promoted. They are not a substitute for the QA that decided *whether* it should be.

---

## 8. Destination safety

Slugs must be lowercase and hyphen-separated — `../../etc/passwd`, `Summerlin` and `a/b` are all refused. Paths are resolved first, then checked for containment inside the theme's content directory, because `..` and symlinks are only visible after resolution. Only `.json` destinations are accepted.

---

## 9. Content provenance — closing the bypass

A gate holds only if nothing can walk around it. Nothing stops someone editing a content file directly and committing it; schema validation would pass, because the file would be well-formed — just unaccountable.

`verify-content-provenance.py` requires every theme content file to be explained by either a promotion record whose `destination_sha256` matches the committed bytes, or an entry in `content-engine/config/migration-allowlist.json`.

The allowlist holds the 12 files whose current state predates the gate — Phases 0–3A corrected NAP, migrated the v1 schema fork and repaired rendering long before any gate existed. Pretending they came through it would be a lie; blocking until they are rewritten would be worse.

**Known limit, stated plainly:** the allowlist matches on path, so an allowlisted file can still be hand-edited without detection. That is the cost of acknowledging history, and it shrinks to zero as each page is properly promoted and its entry removed.

---

## 10. Live verification

`verify-live.py` runs after deployment and compares the promoted artifact against the rendered page. Semantic, not byte-for-byte — HTML changes for reasons unrelated to content.

Compared: HTTP status · final URL · canonical · title · meta description (tolerating the 155-char truncation `lvjcb_truncate_for_seo()` applies) · exactly one H1 · section headings present · promoted paragraphs present · rendered word count against a floor · FAQ questions · internal links · JSON-LD types · resolved `{business}`/`{phone}` tokens · absence of the legacy identity literals.

Two failure modes get particular attention because both are invisible in a browser: **content-loader fallback** (a malformed file renders the generic layout) and **empty heading sections** (the shape the v1 schema fork produced for months).

Results: `PASS` / `WARN` / `FAIL` / `SKIP`, written to `content-engine/reports/live/{slug}.json`. The primary city returns `SKIP` with its reason rather than a meaningless mismatch.

```bash
python content-engine/bin/verify-live.py --all --base-url https://example.com
python content-engine/bin/verify-live.py <slug> --base-url ... --from-file saved.html
```

Read-only. It fetches and compares; it changes nothing.

---

## 11. CI

`validate.yml` (already the reusable workflow `deploy-lvjcb.yml` gates on) gained three steps: **content provenance**, **research/brief/draft validation**, and the **publish-gate self-test**. Deployment continues to depend on the whole workflow, so a provenance or QA failure stops the deploy.

---

## 12. Current state

Nothing has been promoted. There are no drafts, and the corpus holds no approved angle, so the gate refuses every page — the same result Phase 3C-3 reported, now enforced one stage later as well.

Live verification against production reports **1 PASS (henderson), 1 SKIP (las-vegas, primary city), 10 FAIL** — every failure being a Phase 0–3A fix that is committed in the working tree but not yet deployed. The verifier independently rediscovered the defects those phases were written to fix.
