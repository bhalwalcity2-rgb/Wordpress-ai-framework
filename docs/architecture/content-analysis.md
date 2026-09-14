# Corpus Analysis — Method and Findings

| Field | Value |
|---|---|
| Status | Phase 3B, calibration — 2026-09-03 |
| Tool | `content-engine/bin/analyze-corpus.py` (standard library only) |
| Outputs | `content-engine/brief/{slug}.json`, `reports/corpus-summary.json`, `reports/differentiation/{slug}.json` |
| Corpus | 12 content files, 11 rendering, 68 sibling pair comparisons |

> **These are internal editorial QA metrics.** None is a search engine ranking factor and none may be presented as one. They exist to replace reading twelve pages side by side. Extraction is **lexical, not semantic** — vocabulary matching and shape, no parser or model. That makes the output reliable for "do these pages cover the same ground the same way" and unreliable as a claim about meaning.

---

## 1. The instrument was wrong the first time

The first pass measured 5-word phrase overlap, the metric proposed before any data existed. It found almost nothing: **maximum masked similarity 0.040**, and every page scored `REAL_DIFFERENTIATION`.

That was false. At 400–500 words per page, exact five-word matches are rare even between pages built from one template — these pages were *reworded*, not copied. The metric was measuring the instrument, not the corpus.

This is the argument for calibrating before choosing a threshold. Had the previously discussed 0.18 Jaccard cut-off been adopted, every page in this corpus would have passed, and the engine would have certified a templated corpus as original.

**Metrics that survive rewording** — and are therefore the ones recommended:

| Signal | What it resists |
|---|---|
| Structure similarity | rewording, synonym swaps |
| Non-local entity overlap | rewording, landmark swaps |
| FAQ topic similarity | rewritten answers with identical substance |
| Vocabulary overlap | sentence restructuring |

---

## 2. What the corpus actually looks like

| Metric | p25 | p50 | p75 | p90 | max |
|---|---|---|---|---|---|
| Structure similarity | 0.750 | **0.857** | 1.000 | 1.000 | 1.000 |
| Non-local entity overlap | 0.277 | 0.416 | 0.526 | 0.600 | 0.733 |
| FAQ topic similarity | 0.171 | 0.227 | 0.286 | 0.333 | 0.417 |
| Vocabulary overlap | 0.188 | 0.230 | 0.262 | 0.280 | 0.311 |
| 5-gram phrase overlap | — | 0.007 | — | 0.017 | 0.025 |
| Vocabulary substitution lift | — | 0.004 | — | 0.012 | **0.015** |

Flag frequency across 68 pairs: `IDENTICAL_STRUCTURE` **50**, `SHARED_ENTITY_FRAME` 20, `SHARED_FAQ_TOPICS` 18, `SHARED_VOCABULARY` 12, `SHARED_HEADING_PLAN` 10, `LEXICAL_ECHO` 10.

### The failure mode is not the one that was expected

`vocabulary_substitution_lift` maxes at **0.015**. Masking every place name barely changes similarity, which means these pages do **not** share wording that a city name was swapped into.

The corpus does not exhibit *"same page + replace city name."* It exhibits **same brief, different words** — pages independently written to one section plan, covering one topic frame, answering the same four questions. That is harder to detect and no more useful to a reader. The measure is retained because it is cheap and would catch a regression to copy-and-swap, but structure and entity frame are what actually discriminate here.

---

## 3. Information gain: names are not facts

The first gain rule counted unique phrases. Paradise scored 85% unique phrases while tripping 5 of 5 clone signals — its "unique" facts were *Maryland Parkway*, *Paradise Road*, *Sunrise Manor*. Street names.

Swapping a landmark is named as superficial in this phase's own brief, so unique place names alone cannot earn a gain verdict. Gain is now judged on **unique non-local information**: topics a page raises that no sibling does, or content structure carrying facts prose would not.

| Verdict | Pages | Meaning |
|---|---|---|
| `HAS_INFORMATION_GAIN` | 4 | henderson, damaged, sedans, summerlin |
| `LOCAL_NAME_SUBSTITUTION_ONLY` | **6** | Different place names, identical substance |
| `WORDING_ONLY_GAIN` | 2 | non-running, trucks-suvs |

Where nothing survives, the record says `NO_MEANINGFUL_INFORMATION_GAIN` rather than promoting a rephrased sentence into an insight.

---

## 4. Assessments and priority

| Assessment | Count |
|---|---|
| SUPERFICIAL_DIFFERENTIATION | 5 |
| PARTIAL_DIFFERENTIATION | 3 |
| REAL_DIFFERENTIATION | 3 |
| ORPHAN | 1 |

| Verdict | Pages |
|---|---|
| KEEP | henderson |
| REFINE | damaged, sedans |
| REWRITE | enterprise, north-las-vegas, paradise, spring-valley, summerlin, boulder-city, non-running, trucks-suvs |
| CONSIDER REMOVAL | las-vegas *(orphan — see ADR-0005)* |

Scores are derived from measurements, not opinion, so the matrix is reproducible and its inputs auditable. `las-vegas` is the corpus's similarity hub — closest sibling to five other pages — because it is the generic "whole valley" page the others were written against.

---

## 5. Intent and cannibalisation

Location pages classify as **local** intent with transactional and commercial secondaries; service pages as **transactional**. That split is correct and no page targets the wrong format.

The real cannibalisation risk is not intent, it is substance. Six location pages answer the same four questions with the same substance in different words. They compete with each other, and `las-vegas` — whose intent the homepage already owns — competes with the homepage.

---

## 6. Internal linking

| Finding | Detail |
|---|---|
| Declared judgements are real | Each page names 4–5 neighbours out of 7, and the sets differ — this asymmetry is why `internal_links` was retained rather than deleted |
| They reach no page | **Only Henderson renders neighbour links (7).** The other 11 declare 4–5 and render 0 |
| No contextual service links | 11 of 12 pages render none |
| Near-orphan | `boulder-city` is named as a neighbour by only one page |
| Hub | `las-vegas` is named by 11 |

A future engine must not link every page to every area: eight areas linked pairwise is a uniform mesh with no editorial signal, and contradicts the anchor-text variety rule in `SEO_STANDARDS.md` §12.

---

## 7. Claim inventory

257 claims extracted across the corpus.

| Type | Count |
|---|---|
| pricing / payment | 89 |
| unverifiable | 58 |
| service guarantee | 54 |
| process claim | 34 |
| local fact | 13 |
| testimonial / review | 9 |
| government / legal | 6 |

**6 high-risk claims** need sourcing before any rewrite — they assert law or regulator process:

- Henderson: HOA-coordinated towing and daily fines under Nevada statute
- Henderson: Nevada DMV duplicate title via Form VP-012, $20 fee, model year ≤ 2010
- Las Vegas / Enterprise: "we handle all the paperwork, including the DMV title transfer"; "we can often still purchase the vehicle using alternative documentation"

The 58 `unverifiable` claims are mostly hedges — *most*, *typically*, *one of the busiest*. Individually harmless; collectively they are what a page says instead of a fact.

---

## 8. Recommended differentiation model

> **What should make Henderson meaningfully different from Paradise, Summerlin, or North Las Vegas — without inventing facts or forcing keyword variations?**

The corpus already contains the answer. Henderson is the one page with real gain, and the reason is measurable: **15 unique non-local entities** and a reference table. It does not merely mention Henderson more — it raises subjects no sibling raises.

Henderson's differentiator is not the name *Green Valley*. It is that Henderson is dense with HOA-governed communities, so a junk car there is a **compliance deadline**, not an inconvenience. That reframes the reader's problem, changes which questions matter, and changes what the page must explain. Every sibling could name its own neighbourhoods and still say nothing new.

**The rule:** a page earns its place by the *situation* it addresses, not the *place* it names.

| Page | Candidate situation — the local condition that changes the seller's problem |
|---|---|
| Henderson | HOA / CC&R enforcement — removal is a compliance deadline *(established)* |
| Paradise | Renter and short-term-tenancy density near UNLV and the Strip corridor — who may legally sell a car parked at a property they do not own |
| Summerlin | Master-planned, gated, HOA-governed — access logistics: guard gates, guest codes, narrow driveways |
| North Las Vegas | Separate municipality with its own ordinances, plus older industrial-adjacent housing stock |
| Spring Valley | Dense apartment and multi-family parking — towing from shared or assigned spaces |
| Enterprise | Unincorporated Clark County — different enforcement authority from an incorporated city |
| Boulder City | Distance and its own municipal rules; tow logistics genuinely differ |

**These are hypotheses, not facts.** Each must be researched and sourced in Phase 3C before it is written. That is the point: the differentiator is discovered, not assigned.

### What the engine must enforce

1. **One situation per page, not reused.** Two pages may not claim the same angle.
2. **Gain measured on non-local information.** Unique place names never satisfy the gate.
3. **Structure follows the situation.** A page whose angle is access logistics needs a different section plan from one about compliance deadlines. `IDENTICAL_STRUCTURE` at 50/68 pairs is the strongest evidence of templating in this corpus.
4. **FAQs from the situation.** Four questions with the same substance in different words is the same page.
5. **Claims carry sources.** Every regulatory assertion needs provenance before publication.

### Candidate thresholds

Percentiles of *this* corpus. Re-run after any rewrite — a corpus that improves should move these.

| Signal | Warn | Block |
|---|---|---|
| Clone-signal count *(recommended primary gate)* | 2 | 3 |
| Structure similarity | 0.70 | 0.85 |
| Non-local entity overlap | 0.50 | 0.65 |
| FAQ topic similarity | 0.28 | 0.40 |
| Masked vocabulary overlap | 0.27 | 0.32 |
| Vocabulary substitution lift | 0.05 | 0.10 |

**No single metric gates publication.** A page must not pass merely because its words differ, if its structure, entities, FAQ substance and information gain match a sibling. Nothing in this phase fails a build; these are recommendations for Phase 3C to adopt deliberately.
