# Research System — Evidence, Angles, and Provenance

| Field | Value |
|---|---|
| Status | Phase 3C-1 — 2026-09-03 |
| Decision | [ADR-0006](../../ai/decisions/ADR-0006.md) |
| Tooling | `content-engine/bin/bootstrap-research.py`, `validate-research.py` |
| Contracts | `content-engine/config/schema/{research-common,place,claim,angle,intent,entities,queries}.schema.json` |
| Inputs | Phase 3B retro-briefs and differentiation reports |

> **No external research was performed in this phase.** The bootstrapper has no external access, and inventing a source would be worse than having none. Every artifact is a scaffold marked with the evidence level it actually has, plus a list of open questions someone must answer. It is a work order, not findings.

---

## 1. The six artifacts

Per page, all schema-validated:

| Path | Holds |
|---|---|
| `place/{slug}.json` | Research dossier, grouped by dimension; open questions |
| `claims/{slug}.json` | Every externally verifiable statement, with provenance |
| `angles/{slug}.json` | The page's distinct situation, or `NO_SUPPORTED_DIFFERENTIATION` |
| `intent/{slug}.json` | Queries mapped to questions, objections, conversion goal |
| `entities/{slug}.json` | Typed entities and relationships |
| `queries/{slug}.json` | Query network organised by concept, not phrasing |

---

## 2. Provenance vocabulary

The spine of the system. An item's evidence level decides whether the future writer may state it.

| Evidence level | May be stated as fact |
|---|---|
| `verified_fact` | yes |
| `sourced_fact` | yes |
| `business_provided` | yes |
| `research_observation` | only about the corpus, never about the world |
| `hypothesis` | **no** |
| `editorial_opportunity` | **no** |
| `unknown_needs_verification` | **no** |

Research status: `researched`, `verified`, `unresolved`, `unsupported`, `editorial_opportunity`. The last two block publication.

Source types: `official_government`, `official_business`, `primary_source`, `reputable_secondary`, `research_observation`, `unverified`. **`unverified` is the absence of a source, not a weak one.** A model-generated statement is never a source; at best it is a hypothesis to research.

### Risk gating

| Risk | Acceptable sources |
|---|---|
| high | `official_government`, `primary_source` — **plus `verified_on`** |
| medium | the above, or `official_business`, `reputable_secondary` |
| low | the above, or `research_observation` |

`claim_type: government_legal_regulatory` is forced to high risk regardless of phrasing. Reputable secondary reporting is not sufficient for a statute or a fee.

**Being live on the site confers nothing.** Every claim scaffolded from existing copy is `source_type: unverified`, `publishable: false`.

---

## 3. The angle registry — anti-templating control

An angle is a materially different customer situation, constraint, or process concern. A place name, landmark, ZIP, synonym, or reworded heading is **not** an angle.

| Rule | Enforcement |
|---|---|
| Angle ids globally unique | duplicate id → error |
| An approved/used angle belongs to one page | second page claiming it → error |
| Approval requires publishable evidence | `approved` with no publishable claim → error |
| Cited claims must exist | unknown `claim_id` → error |
| Statement must survive place-name masking | collapses → error, at any status |

The masking test is the machine-checkable form of "a landmark is not an angle": strip place names and generic junk-car vocabulary, and a real situation still has something left to describe.

### Grounding selection

The entity group that grounds an angle is chosen by **priority, not count**:

```
user_problem  >  business_regulatory  >  process  >  attributes_condition
```

Counting was wrong. Henderson's largest distinctive group was vehicle-condition words — *rusted, totaled, overheating, stripped* — which every page in the corpus uses. Those describe the industry, not the page. One mention of an HOA enforcement rule differentiates more than five condition adjectives. An angle grounded only in condition words is flagged `WEAK GROUNDING`.

---

## 4. `NO_SUPPORTED_DIFFERENTIATION`

A first-class outcome, not a failure. Asked to differentiate twelve pages, a generator will always produce twelve differentiations, because producing nothing is not a behaviour it falls into. Making "no supported angle" an expected, recordable result is what stops that.

It requires a recommendation: `merge_with_another_page`, `change_page_purpose`, `create_broader_regional_page`, or `exclude_from_publication_set`. Omitting one is an error.

**Current state — 8 of 12 pages:**

| Page | Recommendation |
|---|---|
| boulder-city, enterprise, north-las-vegas, paradise, spring-valley | `create_broader_regional_page` |
| non-running, trucks-suvs | `create_broader_regional_page` |
| las-vegas | `exclude_from_publication_set` *(orphan — ADR-0005)* |

Fewer useful pages beat more thin ones.

---

## 4a. Phase 3C-2 — research executed (2026-09-05)

External research was performed against Nevada statute, DMV, Census and municipal code. Sources are recorded in `content-engine/sources/registry.json`; claims cite `source_ids` rather than restating citations.

**Retrieval note.** `leg.state.nv.us`, `law.justia.com`, `census.gov` and the Nevada Real Estate Division PDFs all returned HTTP 403 or rejected automated retrieval. Where content came via the search index of an official document rather than the document itself, `retrieval_note` says so and the source is rated accordingly. Nothing was written from memory.

### Henderson claim verification

| Claim | Verdict |
|---|---|
| HOA can tow from your driveway; daily fines | **CONTRADICTED** |
| Sell without title if model year ≤ 2010, no liens | **UNSUPPORTED** |
| Duplicate title via VP-012 for $20 | **PARTIALLY_SUPPORTED** — $20 + $8.25 = $28.25 |
| Second-largest city, over 350,000 residents | **PARTIALLY_SUPPORTED** — second-largest yes; 332,141 (ACS 2020-2024), not 350,000+ |
| One of the most HOA-governed cities in Clark County | **UNSUPPORTED** — no ranking source found |
| Covers over 100 square miles | **NEEDS_HUMAN_CONFIRMATION** |

The towing contradiction is specific: NRS 116.3102 limits association removal to vehicles on association property or on a road, street, alley or thoroughfare **within** the community. A car on the owner's own driveway is on the owner's lot. NRS 116.31031 allows an additional fine per **7-day** period after a 14-day cure window, capped at $100 per violation — not daily fines.

### The decisive finding: statewide law cannot differentiate

Henderson's proposed angle rested on HOA compliance pressure. Research retired it, for a reason invisible without research: **NRS 116 is statewide.** Every common-interest community in Nevada operates under identical fines, cure periods and towing limits, so Summerlin, Enterprise and Spring Valley are governed the same way. A regime shared by every sibling differentiates nothing.

The only thing that could differentiate Henderson is a Henderson-specific fact — and the claim asserting one (distinctive HOA density) is exactly the claim no source supports.

**Generalised rule for the writer: a condition created by state law is never a differentiator.**

### What genuinely varies: jurisdiction

The one structural difference found across this corpus is municipal jurisdiction. Paradise, Spring Valley and Enterprise are unincorporated Clark County, where inoperable-vehicle nuisance falls under Clark County Code Title 11 and county code enforcement. Henderson, North Las Vegas and Boulder City are incorporated cities with their own codes and enforcement offices.

This differentiates the **group**, not the pages within it — which is evidence *for* consolidating the three unincorporated pages rather than differentiating them. It is recorded as a `proposed` angle for Henderson, blocked on retrieving the actual Ch. 7.08 provisions, because an office name alone is not information gain.

---

## 5. Henderson as reference

Phase 3B measured Henderson as the one page with real information gain, so it is the reference implementation — **not a source of facts**.

Its HOA/compliance concept is recorded exactly as §10 of the phase brief requires:

```
hypothesis  →  research required  →  evidence  →  approved angle
     ▲                                              (not yet reached)
   currently here
```

| Field | Value |
|---|---|
| `differentiation_verdict` | `PENDING_RESEARCH` |
| angle status | `proposed` |
| angle type | `customer_problem` |
| grounded in | compliance, warning, insurance, payout, inherited |
| `blocked_by` | 4 open questions, incl. the HOA-density claim |
| claims | 39 total, **0 publishable** |
| high-risk claims | 1 — HOA-coordinated towing under Nevada statute, `unverified` |

The page is live and reads authoritatively. That is precisely why its claims are recorded as unverified: publication is not evidence.

---

## 6. Running it

```bash
python content-engine/bin/analyze-corpus.py            # Phase 3B inputs
python content-engine/bin/bootstrap-research.py        # scaffold (--force to overwrite)
python content-engine/bin/validate-research.py         # enforce (--strict to fail on warnings)
```

`bootstrap-research.py` will not overwrite existing artifacts without `--force`, so hand-added research survives a re-run.

Validation is currently **0 errors, 208 warnings**. The warnings are the honest state of the corpus: unsourced live claims, and eight pages with no supported angle. They are not noise to silence — they are the work queue.

---

## 7. What the writer will be required to consume

Not built yet. When it is, it must:

1. Refuse to draft a page whose angle is not `approved`.
2. State only claims with `publishable: true`.
3. Take its section plan from the angle's `recommended_sections`, so structure follows the situation — `IDENTICAL_STRUCTURE` on 50 of 68 pairs was Phase 3B's strongest templating signal.
4. Derive FAQs from `intent/` decision questions, not from a sibling's FAQ list.
5. Emit `NO_SUPPORTED_DIFFERENTIATION` upward rather than writing a page without an angle.
