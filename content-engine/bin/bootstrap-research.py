#!/usr/bin/env python3
"""Scaffold research artifacts from the existing corpus. No research performed.

Reads the Phase 3B retro-briefs and differentiation reports and emits, per
page: place/, claims/, angles/, intent/, entities/, queries/.

WHAT THIS TOOL DOES NOT DO
--------------------------
It does not research anything. It has no external access, and inventing a
source would be worse than having none. Everything it extracts comes from
the repository, so everything it writes is marked accordingly:

  * facts from business-config.php   -> business_provided
  * observations about the corpus    -> research_observation
  * assertions found in live copy    -> source_type 'unverified', publishable false
  * situations suggested by evidence -> hypothesis, angle status 'proposed'

A claim being live on the site is not evidence that it is true. Several
claims in this corpus are published and unsourced, which is exactly why the
ledger records them as unverified rather than inheriting their confidence.

The output is a work order for a human or an external research pass. Every
open question is something someone must answer before the corresponding
angle can be approved.

Usage:
    python content-engine/bin/bootstrap-research.py [theme-slug] [--force]
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

import research as rs         # noqa: E402
import textstats as ts        # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
DEFAULT_THEME = "kadence-child-lvjcb"
GENERATED_NOTE = (
    "Scaffolded from the existing corpus by bootstrap-research.py. NO EXTERNAL "
    "RESEARCH WAS PERFORMED. Every item is marked with the evidence level it "
    "actually has; nothing here may be treated as verified until a researcher "
    "answers the open questions and records a real source."
)

CLAIM_TYPE_MAP = {
    "government_legal": "government_legal_regulatory",
    "pricing_payment": "pricing_payment",
    "service_guarantee": "service_guarantee",
    "process_claim": "process_claim",
    "testimonial_review": "testimonial_review",
    "local_fact": "local_fact",
    "unverifiable": "unverifiable",
}

ENTITY_GROUP_LABEL = {
    "user_problem": "problem",
    "business_regulatory": "regulatory body or rule",
    "attributes_condition": "vehicle condition",
    "process": "process step",
}


def slugify(text, limit=6):
    """Slug safe for an id. ts.tokens() keeps apostrophes ("we'll"), which
    the id pattern rejects, so they are stripped rather than hyphenated —
    "wont" reads better than "won-t"."""
    words = []
    for word in ts.tokens(text, drop_stopwords=True)[:limit]:
        cleaned = word.replace("'", "")
        if cleaned:
            words.append(cleaned)
    return "-".join(words) or "item"


def load_json(path):
    return json.loads(path.read_text(encoding="utf-8"))


def write_json(path, data):
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(data, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")


# --------------------------------------------------------------------------
# Claims
# --------------------------------------------------------------------------

def build_claims(brief):
    """Every claim 3B found, recorded with the provenance it actually has."""
    claims, seen = [], set()
    for index, raw in enumerate(brief["current_business_claims"]):
        primary = raw["types"][0]
        claim_type = CLAIM_TYPE_MAP.get(primary, "unverifiable")
        risk = rs.normalise_risk(claim_type, raw.get("risk", "low"))

        cid = "%s-%02d-%s" % (brief["slug"], index, slugify(raw["text"], 4))
        if cid in seen:
            cid += "-%d" % index
        seen.add(cid)

        claims.append({
            "id": cid,
            "claim": raw["text"],
            "claim_type": claim_type,
            "source": "",
            "source_type": "unverified",
            "confidence": "none",
            "risk": risk,
            "status": "unresolved",
            "verification": "NOT_YET_CHECKED",
            "publishable": False,
            "appears_in_existing_copy": True,
            "source_page": brief["slug"],
            "notes": (
                "Extracted from live copy. Being published is not evidence — this "
                "claim has no recorded source. Types detected: %s." % ", ".join(raw["types"])
            ),
        })
    return {
        "slug": brief["slug"],
        "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
        "claims": claims,
    }


# --------------------------------------------------------------------------
# Place dossier
# --------------------------------------------------------------------------

def build_place(brief, diff, claims):
    slug = brief["slug"]
    entities = brief["current_entities"]
    items, questions = {}, []

    def item(group, ident, statement, why, level, status, source=None, source_type="unverified"):
        prov = {"evidence_level": level, "status": status, "source_type": source_type}
        if source:
            prov["source"] = source
        items.setdefault(group, []).append({
            "id": ident, "statement": statement, "why_it_matters": why, "provenance": prov,
        })

    # Business-provided: the service area itself is configuration, not research.
    if brief["page_type"] == "location":
        item("geographic_context", "%s-is-service-area" % slug,
             "%s is a configured service area for this business." % (brief.get("current_primary_topic", slug)),
             "Establishes that the business serves the area at all.",
             "business_provided", "verified",
             source="wordpress/themes/<theme>/inc/business-config.php", source_type="official_business")

    # Research observations: what the corpus itself demonstrates.
    item("service_relevance", "%s-existing-coverage" % slug,
         "The existing page covers %d words across %d sections."
         % (brief["word_count"], len(brief["current_sections"])),
         "Baseline for judging whether a rewrite adds anything.",
         "research_observation", "researched",
         source="content-engine/brief/%s.json" % slug, source_type="research_observation")

    if diff.get("unique_nonlocal_entities"):
        item("business_relevant_situations", "%s-distinct-topics" % slug,
             "The page raises topics no sibling raises: %s."
             % ", ".join(e.split(":", 1)[1] for e in diff["unique_nonlocal_entities"][:8]),
             "These are the only candidate grounds for a differentiated angle.",
             "research_observation", "researched",
             source="content-engine/reports/differentiation/%s.json" % slug,
             source_type="research_observation")

    # Local names are recorded as observations, explicitly not as differentiation.
    if entities.get("local"):
        item("community_context", "%s-named-places" % slug,
             "Places named in existing copy: %s." % ", ".join(entities["local"][:10]),
             "Place names alone do not differentiate a page; recorded for reference only.",
             "research_observation", "researched",
             source="existing page copy", source_type="research_observation")

    # Every unsourced claim becomes an open question.
    for claim in claims["claims"]:
        if claim["risk"] in ("high", "medium"):
            questions.append({
                "id": "q-%s" % claim["id"],
                "question": "What authoritative source supports: \"%s\"?" % claim["claim"][:130],
                "why": "Live copy asserts this with no source. Risk: %s." % claim["risk"],
                "suggested_source_type": ("official_government"
                                          if claim["claim_type"] == "government_legal_regulatory"
                                          else "official_business"),
                "blocks_angle": "",
            })

    # The standing research question for a location.
    if brief["page_type"] == "location":
        questions.insert(0, {
            "id": "q-%s-local-condition" % slug,
            "question": ("What local condition in this area materially changes how or why "
                         "someone sells a junk car, versus elsewhere in the valley?"),
            "why": ("Without an answer this page has no supported angle and should not be "
                    "written. A different landmark is not an answer."),
            "suggested_source_type": "official_government",
            "blocks_angle": "%s-primary" % slug,
        })

    counts = {"researched": 0, "verified": 0, "unresolved": 0, "unsupported": 0, "editorial_opportunity": 0}
    for group in items.values():
        for entry in group:
            counts[entry["provenance"]["status"]] += 1

    return {
        "slug": slug,
        "target_type": brief["page_type"],
        "display_name": brief.get("current_primary_topic", slug),
        "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
        "research_status": {
            "overall": "unresolved",
            "researched_count": counts["researched"],
            "verified_count": counts["verified"],
            "unresolved_count": len([c for c in claims["claims"] if c["status"] == "unresolved"]),
            "unsupported_count": counts["unsupported"],
            "editorial_opportunity_count": counts["editorial_opportunity"],
            "blocking_gaps": [q["id"] for q in questions[:6]],
        },
        "sections": items,
        "open_questions": questions,
        "corpus_context": {
            "phase_3b_assessment": brief["likely_sibling_overlap"]["assessment"],
            "information_gain_verdict": brief["current_information_gain"]["verdict"],
            "rewrite_priority": brief["rewrite_priority"],
            "closest_sibling": brief["likely_sibling_overlap"]["closest_sibling"],
            "known_quality_issues": brief["known_quality_issues"],
        },
    }


# --------------------------------------------------------------------------
# Angles
# --------------------------------------------------------------------------

def build_angles(brief, diff, claims, place):
    """Propose an angle only where evidence could support one."""
    slug = brief["slug"]
    unique = diff.get("unique_nonlocal_entities") or []
    place_terms = set(brief["current_local_entities"]) | {brief.get("current_primary_topic", "")}

    if not unique:
        return {
            "slug": slug,
            "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
            "differentiation_verdict": "NO_SUPPORTED_DIFFERENTIATION",
            "recommendation_if_unsupported": (
                "exclude_from_publication_set" if not brief["renders"] else "create_broader_regional_page"),
            "recommendation_note": (
                "Phase 3B found no topic on this page that its siblings do not also cover: "
                "verdict %s. Nothing in the repository supports a distinct angle, and one "
                "must not be invented. Either research a genuine local condition, or fold "
                "this page into a broader regional page. Fewer useful pages beat more thin ones."
                % brief["current_information_gain"]["verdict"]
            ),
            "angles": [],
        }

    # Group the distinctive entities to suggest an angle type.
    groups = {}
    for entry in unique:
        group, _, value = entry.partition(":")
        groups.setdefault(group, []).append(value)
    best = rs.choose_angle_group(groups)
    angle_type = rs.ANGLE_TYPE_BY_ENTITY_GROUP.get(best, "customer_problem")
    distinctive = sorted(set(groups[best]))[:6]

    statement = (
        "HYPOTHESIS: sellers here face a distinct %s involving %s, which changes what "
        "they need to know or do before the vehicle can be removed. The situation, not "
        "the place name, is what this page would be about."
        % (ENTITY_GROUP_LABEL.get(best, "situation"), ", ".join(distinctive))
    )

    # A condition-only grounding is weak evidence: every junk-car page in the
    # corpus mentions damaged and non-running vehicles, so those words describe
    # the industry rather than this page.
    weak = best == "attributes_condition"

    evidence = [{"claim_id": c["id"], "relevance": "asserted in existing copy; unverified"}
                for c in claims["claims"] if c["risk"] in ("high", "medium")][:5]

    return {
        "slug": slug,
        "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
        "differentiation_verdict": "PENDING_RESEARCH",
        "recommendation_if_unsupported": "not_applicable",
        "recommendation_note": (
            "A candidate angle exists but rests on claims that are live and unsourced. "
            "It stays 'proposed' until the open questions in the dossier are answered."
        ),
        "angles": [{
            "id": "%s-primary" % slug,
            "page": slug,
            "status": "proposed",
            "angle_type": angle_type,
            "statement": statement,
            "evidence": evidence,
            "why_unique": (
                "Phase 3B measured these as topics no sibling page raises (%d distinct "
                "non-local entities). This is the only ground for differentiation the "
                "corpus supports; place names are excluded by definition."
                % len(unique)
            ),
            "must_not_overlap_with": [s for s in diff.get("compared_against", [])],
            "related_entities": sorted(set(v for vals in groups.values() for v in vals))[:15],
            "recommended_questions": brief["current_faq_topics"][:4],
            "recommended_sections": [
                "A section explaining the situation itself, not the city",
                "A section on what the seller must do differently because of it",
            ],
            "blocked_by": [q["id"] for q in place["open_questions"][:4]],
            "notes": " ".join(filter(None, [
                "SUPERFICIAL — collapses under place-name masking."
                if rs.is_superficial_angle(statement, place_terms)
                else "Statement survives place-name masking.",
                ("WEAK GROUNDING: rests only on vehicle-condition words, which every "
                 "page in the corpus uses. Treat as unsupported until research finds a "
                 "genuine local condition." if weak else ""),
            ])),
        }],
    }


# --------------------------------------------------------------------------
# Intent, entities, queries
# --------------------------------------------------------------------------

INTENT_MAP = {"transactional": "transactional", "commercial": "commercial_investigation",
              "informational": "informational", "local": "navigational_local"}


def build_intent(brief, diff):
    primary = INTENT_MAP.get(brief["current_primary_intent"], "transactional")
    secondary = [INTENT_MAP[i] for i in brief["secondary_intents"] if i in INTENT_MAP]

    queries = []
    for question in brief["current_faq_topics"]:
        queries.append({
            "query": " ".join(ts.tokens(question, drop_stopwords=True)[:8]),
            "intent": primary,
            "question": question,
            "satisfied_by": "",
            "provenance": "observed_in_existing_copy",
        })

    risks = []
    if not brief["renders"]:
        risks.append({"with_page": "homepage", "severity": "high",
                      "reason": "Primary city — the homepage already owns this intent."})
    if brief["likely_sibling_overlap"]["assessment"] == "SUPERFICIAL_DIFFERENTIATION":
        risks.append({"with_page": brief["likely_sibling_overlap"]["closest_sibling"] or "", "severity": "high",
                      "reason": "Same situation and FAQ substance as this sibling, differently worded."})

    return {
        "slug": brief["slug"],
        "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
        "primary_intent": primary,
        "secondary_intents": secondary,
        "user_stage": "solution_aware",
        "conversion_goal": "Phone call or instant-offer submission",
        "decision_questions": [
            {"question": q, "required_answer": "TO BE RESEARCHED",
             "status": "unresolved"}
            for q in brief["current_faq_topics"][:6]
        ],
        "objections": [
            {"objection": "Is the quoted price the price I actually get?",
             "response_requirement": "Needs a sourced statement of the firm-offer policy."},
            {"objection": "Can I sell without the title?",
             "response_requirement": "Needs an authoritative Nevada DMV source before answering."},
        ],
        "queries": queries,
        "cannibalisation_risk": risks,
    }


def build_entities(brief, diff):
    ents = brief["current_entities"]
    relationships = []
    for edge, count in brief.get("entity_relationships", {}).items():
        left, _, right = edge.partition("--")
        relationships.append({
            "from": left, "relation": "co-occurs_with", "to": right,
            "evidence_level": "research_observation",
            "observed_cooccurrence": count,
            "notes": ("Co-occurrence in existing copy. Evidence that the page connects these "
                      "ideas — not evidence that the connection holds."),
        })

    # Domain relationships that the research must confirm rather than assume.
    for frm, rel, to in (
        ("vehicle condition", "determines", "towing requirement"),
        ("vehicle ownership", "determines", "title and documentation path"),
        ("community rules", "creates", "vehicle removal pressure"),
        ("location", "determines", "pickup and logistics context"),
    ):
        relationships.append({
            "from": frm, "relation": rel, "to": to,
            "evidence_level": "hypothesis",
            "notes": "Plausible domain relationship, unverified for this page.",
        })

    return {
        "slug": brief["slug"],
        "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
        "primary_entity": ents.get("main_topic", brief["slug"]),
        "related_entities": ents.get("other_proper_nouns", [])[:15],
        "attributes": ents.get("attributes_condition", []),
        "problems": ents.get("user_problem", []),
        "processes": ents.get("process", []),
        "user_types": [],
        "local_entities": ents.get("local", []),
        "business_entities": ents.get("business_regulatory", []),
        "relationships": relationships,
        "coverage_gaps": [e.split(":", 1)[1] for e in (diff.get("unique_nonlocal_entities") or [])][:10],
    }


def build_queries(brief, diff, sibling_faq_terms):
    concepts, seen = [], set()
    for question in brief["current_faq_topics"]:
        key = frozenset(ts.content_words(question))
        if not key or key in seen:
            continue
        seen.add(key)
        shared = len(key & sibling_faq_terms) / len(key) > 0.5 if key else False
        concepts.append({
            "concept": " ".join(sorted(key)[:5]),
            "intent": INTENT_MAP.get(brief["current_primary_intent"], "transactional"),
            "user_need": question,
            "representative_queries": [" ".join(ts.tokens(question, drop_stopwords=True)[:8])],
            "covered_by_existing_page": True,
            "shared_with_siblings": shared,
            "notes": "Derived from an existing FAQ. Not search volume data.",
        })

    distinctive = sum(1 for c in concepts if not c["shared_with_siblings"])
    return {
        "slug": brief["slug"],
        "generated": {"by": "bootstrap-research.py", "note": GENERATED_NOTE},
        "concepts": concepts,
        "coverage_summary": {
            "total_concepts": len(concepts),
            "shared_with_siblings": len(concepts) - distinctive,
            "distinctive_concepts": distinctive,
            "uncovered_by_existing_page": 0,
        },
    }


# --------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("theme", nargs="?", default=DEFAULT_THEME)
    parser.add_argument("--force", action="store_true",
                        help="overwrite artifacts that already exist (hand edits are lost)")
    args = parser.parse_args()

    brief_dir = ENGINE / "brief"
    diff_dir = ENGINE / "reports" / "differentiation"
    if not brief_dir.is_dir():
        print("No retro-briefs. Run analyze-corpus.py first.")
        return 1

    briefs = [load_json(p) for p in sorted(brief_dir.glob("*.json"))]
    diffs = {p.stem: load_json(p) for p in diff_dir.glob("*.json")}

    # FAQ vocabulary across all pages, to spot concepts every sibling shares.
    all_faq_terms = {}
    for brief in briefs:
        all_faq_terms[brief["slug"]] = ts.content_words(" ".join(brief["current_faq_topics"]))

    written, skipped, locked = 0, 0, 0
    unsupported = []

    for brief in briefs:
        slug = brief["slug"]
        diff = diffs.get(slug, {})
        siblings = set()
        for other, terms in all_faq_terms.items():
            if other != slug:
                siblings |= terms

        claims = build_claims(brief)
        place = build_place(brief, diff, claims)
        angles = build_angles(brief, diff, claims, place)
        intent = build_intent(brief, diff)
        entities = build_entities(brief, diff)
        queries = build_queries(brief, diff, siblings)

        if angles["differentiation_verdict"] == "NO_SUPPORTED_DIFFERENTIATION":
            unsupported.append(slug)

        for folder, payload in (("place", place), ("claims", claims), ("angles", angles),
                                ("intent", intent), ("entities", entities), ("queries", queries)):
            path = ENGINE / folder / ("%s.json" % slug)
            if path.exists():
                # A curated file is never overwritten, --force included. Research
                # is expensive and hand-verified; regeneration must not be able
                # to silently discard it.
                try:
                    if json.loads(path.read_text(encoding="utf-8")).get("research_locked"):
                        locked += 1
                        continue
                except (json.JSONDecodeError, OSError):
                    pass
                if not args.force:
                    skipped += 1
                    continue
            write_json(path, payload)
            written += 1

    print("Scaffolded research artifacts for %d pages" % len(briefs))
    print("  written: %d   skipped (exists, use --force): %d   locked (curated, never overwritten): %d"
          % (written, skipped, locked))
    print("  NO EXTERNAL RESEARCH PERFORMED — every claim is unverified by construction")
    if unsupported:
        print("  NO_SUPPORTED_DIFFERENTIATION: %s" % ", ".join(unsupported))
    return 0


if __name__ == "__main__":
    sys.exit(main())
