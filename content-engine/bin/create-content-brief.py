#!/usr/bin/env python3
"""Assemble a production content brief — or refuse, with a reason.

Stage 1 of the production pipeline:

    research -> intent -> entities -> queries -> APPROVED ANGLE
    -> SEMANTIC BRIEF -> draft -> claim extraction -> sibling comparison
    -> content QA -> publish gate

Reads the Phase 3C-1/3C-2 research artifacts and the Phase 3B corpus
analysis, runs the page-existence gate, and writes
content-engine/brief/production/{slug}.json.

The Phase 3B retro-briefs in content-engine/brief/*.json describe what a page
ALREADY does. They are inputs here and are never modified — a brief that
described the current page would instruct the writer to reproduce its
weaknesses.

A BLOCKED brief is still written. It records why the page may not proceed and
what to do instead, which is more useful than no file at all.

Usage:
    python content-engine/bin/create-content-brief.py <slug> [--force]
    python content-engine/bin/create-content-brief.py --all
"""

import argparse
import json
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(REPO_ROOT / "content-engine" / "lib"))

# Windows consoles default to cp1252, which cannot encode the em dashes and
# middle dots used in these reports. Replace rather than crash: a validator
# that dies formatting its own output is worse than one with a "?" in it.
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(errors="replace")

import briefing as bf         # noqa: E402
import promotion as pm        # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
PRODUCTION = ENGINE / "brief" / "production"
BRIEF_VERSION = "1.1.0"
DEFAULT_THEME = "kadence-child-lvjcb"


def load(path, default=None):
    if not path.is_file():
        return default
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return default


def rel(path):
    return path.relative_to(REPO_ROOT).as_posix()


PURPOSE_FILE = ENGINE / "config" / "page-purposes.json"


def page_purpose_for(slug):
    """Purpose is declared, not guessed (ADR-0009). Default keeps the
    stricter informational test, so a page only gets the service-area
    route if someone deliberately puts it there."""
    data = load(PURPOSE_FILE, {}) or {}
    return data.get("pages", {}).get(slug, "differentiated_informational")


def build(slug):
    paths = {
        "angles": ENGINE / "angles" / ("%s.json" % slug),
        "claims": ENGINE / "claims" / ("%s.json" % slug),
        "place": ENGINE / "place" / ("%s.json" % slug),
        "intent": ENGINE / "intent" / ("%s.json" % slug),
        "entities": ENGINE / "entities" / ("%s.json" % slug),
        "queries": ENGINE / "queries" / ("%s.json" % slug),
        "retro_brief": ENGINE / "brief" / ("%s.json" % slug),
        "differentiation_report": ENGINE / "reports" / "differentiation" / ("%s.json" % slug),
        "corpus_summary": ENGINE / "reports" / "corpus-summary.json",
    }
    docs = {k: load(v, {}) for k, v in paths.items()}

    missing = [k for k, v in docs.items() if not v]
    if missing:
        return None, "missing research artifacts: %s (run bootstrap-research.py)" % ", ".join(missing)

    retro = docs["retro_brief"]
    purpose = page_purpose_for(slug)

    # Business-provided facts live once (claims/_shared-business.json) and are
    # merged into every brief, so a changed service promise is corrected in
    # one place rather than in a dozen ledgers.
    shared = load(ENGINE / "claims" / "_shared-business.json", {"claims": []})
    merged_claims = {"claims": list(docs["claims"].get("claims", [])) + list(shared.get("claims", []))}
    docs["claims"] = merged_claims

    config = pm.read_business_config(REPO_ROOT / "wordpress" / "themes" / DEFAULT_THEME)
    gate, angle = bf.run_gate(slug, docs["angles"], merged_claims, docs["place"],
                              retro, docs["differentiation_report"],
                              purpose=purpose, config=config)

    sibling_briefs = {}
    for other in sorted((ENGINE / "brief").glob("*.json")):
        if other.stem == slug:
            continue
        doc = load(other, {})
        if doc.get("page_type") == retro.get("page_type"):
            sibling_briefs[other.stem] = doc

    constraints = bf.sibling_constraints(slug, docs["differentiation_report"],
                                         docs["corpus_summary"], sibling_briefs)
    claims_split = bf.allowed_claims(docs["claims"])

    intent_doc = docs["intent"]
    queries_doc = docs["queries"]
    entities_doc = docs["entities"]
    place_doc = docs["place"]

    question_count = len(intent_doc.get("decision_questions", []))
    depth, target_range = bf.depth_guidance(question_count, intent_doc.get("primary_intent", ""))

    sibling_questions = {q.lower() for q in constraints["questions_already_answered"]}

    concept_groups = []
    for concept in queries_doc.get("concepts", []):
        shared = concept.get("shared_with_siblings", False)
        concept_groups.append({
            "concept": concept["concept"],
            "intent": concept.get("intent", ""),
            "why_needed": concept.get("user_need", ""),
            "coverage": "useful_if_relevant" if shared else "must_answer",
            "representative_queries": concept.get("representative_queries", [])[:6],
            "shared_with_siblings": shared,
        })

    relationships = []
    for rel_item in entities_doc.get("relationships", []):
        relationships.append({
            "chain": [rel_item["from"], rel_item["relation"], rel_item["to"]],
            "evidence_level": rel_item.get("evidence_level", "hypothesis"),
            "claim_id": rel_item.get("claim_id", ""),
            "explain_because": rel_item.get("notes", ""),
        })

    gain_items = []
    for entry in (docs["differentiation_report"].get("unique_nonlocal_entities") or [])[:8]:
        topic = entry.split(":", 1)[-1]
        gain_items.append({
            "information": "Explain what %s means for someone selling a vehicle here." % topic,
            "why_it_is_new": "No sibling page raises this topic (Phase 3B measurement).",
            "evidence_level": "research_observation",
        })
    if purpose == "service_area_transactional" and not gain_items:
        gain_items.append({
            "information": ("Confirm coverage of this service area and set out what happens next, "
                            "accurately."),
            "why_it_is_new": ("Not new, and not required to be. A service-area page is judged on "
                              "clarity and accuracy, not information gain (ADR-0009)."),
            "evidence_level": "business_provided",
        })
    if not gain_items:
        gain_items.append({
            "information": "NO ESTABLISHED INFORMATION GAIN",
            "why_it_is_new": ("Nothing measured or researched distinguishes this page from its "
                              "siblings. This brief is blocked; the gap must be closed by research, "
                              "never by invention."),
            "evidence_level": "unknown_needs_verification",
        })

    brief = {
        "slug": slug,
        "brief_version": BRIEF_VERSION,
        "generated": {
            "by": "create-content-brief.py",
            "note": ("Assembled from research artifacts. Structure is derived from this page's own "
                     "purpose and evidence — there is deliberately no master section template."),
        },
        "page_type": retro.get("page_type", "location"),
        "page_purpose_class": purpose,
        "page_purpose": (
            ("Service-area page for %s. It exists so someone searching for this service here can "
             "confirm the business covers their address and start the process, not to teach them "
             "something their neighbours' pages do not." % retro.get("current_primary_topic", slug))
            if purpose == "service_area_transactional"
            else ((angle or {}).get("statement")
                  or "UNDETERMINED — no approved angle establishes why this page should exist.")),
        "primary_topic": retro.get("current_primary_topic", slug),
        "primary_intent": intent_doc.get("primary_intent", "transactional"),
        "secondary_intents": intent_doc.get("secondary_intents", []),
        "conversion_goal": intent_doc.get("conversion_goal", "Phone call or instant-offer submission"),

        "gate": gate.as_dict(),

        "query_model": {
            "query_network_id": "queries/%s.json" % slug,
            "primary_queries": [q["query"] for q in intent_doc.get("queries", [])[:6]],
            "supporting_queries": [q["query"] for q in intent_doc.get("queries", [])[6:14]],
            "question_map": [
                {
                    "question": q["question"],
                    "why_this_page_answers_it": q.get("required_answer", "TO BE RESEARCHED"),
                    "coverage": "not_relevant" if q["question"].lower() in sibling_questions else "must_answer",
                }
                for q in intent_doc.get("decision_questions", [])
            ],
            "objection_map": [
                {"objection": o["objection"], "required_response": o["response_requirement"]}
                for o in intent_doc.get("objections", [])
            ],
            "concept_groups": concept_groups,
        },

        "entity_model": {
            "primary_entity": entities_doc.get("primary_entity", slug),
            "supporting_entities": entities_doc.get("related_entities", []),
            "local_entities": entities_doc.get("local_entities", []),
            "user_entities": entities_doc.get("user_types", []),
            "process_entities": entities_doc.get("processes", []),
            "attributes": entities_doc.get("attributes", []),
            "entity_relationships": relationships,
        },

        "local_model": {
            "location": retro.get("current_primary_topic", ""),
            "local_context": entities_doc.get("local_entities", [])[:10],
            "local_conditions": [
                {
                    "condition": item["statement"],
                    "evidence_level": item["provenance"]["evidence_level"],
                    "materially_affects": item.get("why_it_matters", ""),
                }
                for group in place_doc.get("sections", {}).values() for item in group
                if item["provenance"]["evidence_level"] in ("verified_fact", "sourced_fact", "business_provided")
            ],
            "geographic_context": [],
            "customer_scenarios": [],
            "decision_factors": [],
        },

        "differentiation": {
            "approved_angle_id": (angle or {}).get("id", ""),
            "angle_statement": (angle or {}).get("statement", ""),
            "angle_evidence": [e["claim_id"] for e in (angle or {}).get("evidence", [])],
            "why_this_page_exists": (angle or {}).get("why_unique",
                "NOT ESTABLISHED — this page has no approved angle."),
            "must_not_overlap_with": (angle or {}).get("must_not_overlap_with",
                                                       constraints["compared_against"]),
            "forbidden_generic_patterns": bf.FORBIDDEN_GENERIC_PATTERNS,
        },

        "information_gain": {
            "required_new_information": gain_items,
            "unique_explanations": [],
            "unique_scenarios": [],
            "unique_questions": [q for q in retro.get("current_faq_topics", [])
                                 if q.lower() not in sibling_questions][:6],
            "unique_decision_factors": [],
            "sibling_coverage_gaps": constraints["topic_opportunities"],
        },

        "content_architecture": {
            "structure_rationale": (
                "Sections must follow this page's own purpose and evidence. Identical structure "
                "across siblings was the strongest templating signal in the corpus (50 of 68 pairs), "
                "so no default plan is supplied here."
                if gate.status == "ALLOWED" else
                "NOT PLANNED — the page is blocked, so no structure is proposed. Proposing one "
                "would invite a page to be written that has no reason to exist."),
            "recommended_sections": (
                [] if gate.status == "BLOCKED" else [{
                    "section_type": "content",
                    "working_title": "(writer composes; derive from the angle, not from a sibling)",
                    "purpose": "Explain the situation the approved angle names.",
                    "entities": entities_doc.get("problems", [])[:6],
                    "queries": [c["concept"] for c in concept_groups if c["coverage"] == "must_answer"][:4],
                    "information_gain": gain_items[0]["information"],
                    "evidence": claims_split["allowed_claim_ids"][:5],
                    "internal_links": [],
                }]
            ) or [{
                "section_type": "content",
                "working_title": "BLOCKED",
                "purpose": "No section plan — this page did not pass the existence gate.",
            }],
        },

        "faq_plan": {
            "questions_to_answer": [q for q in retro.get("current_faq_topics", [])
                                    if q.lower() not in sibling_questions],
            "questions_already_answered_by_siblings": constraints["questions_already_answered"][:20],
            "questions_not_to_repeat": [q for q in retro.get("current_faq_topics", [])
                                        if q.lower() in sibling_questions],
        },

        "claims": claims_split,
        "internal_linking": _linking(slug, docs["retro_brief"]),
        "sibling_constraints": constraints,

        "writing_constraints": {
            "tone": "Plain, direct, and useful to someone with a problem to solve. No sales register.",
            "audience": "A vehicle owner deciding whether and how to sell a non-running or damaged car.",
            "content_depth": depth,
            "target_range_words": target_range,
            "target_range_justification": (
                "Derived from %d distinct decision questions and the page's intent. Not a target to "
                "fill — stopping early is better than padding." % question_count),
            "forbidden_patterns": bf.FORBIDDEN_WRITING_PATTERNS + bf.FORBIDDEN_GENERIC_PATTERNS,
        },

        "source_artifacts": {k: rel(v) for k, v in paths.items()},
    }
    return brief, None


def _linking(slug, retro):
    declared = retro.get("current_internal_links", {}).get("declared_legacy_metadata", {})
    return {
        "relevant_neighbours": [
            {"slug": s, "linking_reason": "Named as an adjacent area in the page's own editorial judgement"}
            for s in declared.get("locations", []) if s != slug
        ],
        "relevant_services": [
            {"slug": s, "linking_reason": "Service judged relevant to this page's audience"}
            for s in declared.get("services", [])
        ],
        "anchor_guidance": (
            "Anchor text follows the sentence it sits in. Vary it. Do not link every page to every "
            "other page — a link needs a reason a reader would recognise (SEO_STANDARDS.md §12)."),
        "do_not_link": [],
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slug", nargs="?")
    parser.add_argument("--all", action="store_true")
    parser.add_argument("--force", action="store_true")
    args = parser.parse_args()

    slugs = ([p.stem for p in sorted((ENGINE / "brief").glob("*.json"))]
             if args.all else ([args.slug] if args.slug else []))
    if not slugs:
        parser.error("give a slug or --all")

    PRODUCTION.mkdir(parents=True, exist_ok=True)
    allowed = blocked = 0

    for slug in slugs:
        brief, error = build(slug)
        if error:
            print("  ERROR  %-18s %s" % (slug, error))
            continue

        path = PRODUCTION / ("%s.json" % slug)
        if path.exists() and not args.force:
            print("  skip   %-18s exists (use --force)" % slug)
            continue
        path.write_text(json.dumps(brief, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")

        gate = brief["gate"]
        if gate["status"] == "ALLOWED":
            allowed += 1
            print("  ALLOWED  %-18s angle=%s" % (slug, brief["differentiation"]["approved_angle_id"]))
        else:
            blocked += 1
            print("  BLOCKED  %-18s %s -> %s"
                  % (slug, gate["blocked_reason"], gate["recommended_action"]))

    print("-" * 64)
    print("%d allowed, %d blocked -> %s" % (allowed, blocked, rel(PRODUCTION)))
    if not allowed:
        print("No page passed the existence gate. Nothing may be written.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
