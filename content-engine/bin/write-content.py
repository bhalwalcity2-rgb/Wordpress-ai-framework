#!/usr/bin/env python3
"""Controlled writer — assembles an authoring package, or refuses.

WHAT THIS IS
------------
The repository may not take on an external AI API (AI_RULES.md §4,
PROJECT_CONTEXT.md §15), so this stage does not author prose itself. It is
the control layer around authoring: it re-runs the gate, assembles the
complete instruction package a writer is permitted to see, and writes it to
content-engine/drafts/{slug}.json with status 'authoring_package'.

An assistant then authors canonical v2 content into the draft's `content`
block against that package, and validate-draft.py checks the result. The
separation is deliberate — the constraints are enforced by code that cannot
be talked out of them, and the prose is written by something that can read.

WHAT IT REFUSES
---------------
Generation is blocked unless the brief's gate says ALLOWED and the named
angle is genuinely approved. It re-checks rather than trusting the brief: a
brief is a file, and a file can be edited.

Drafts are written ONLY to content-engine/drafts/. Nothing here may write to
wordpress/themes/*/content/ — promotion is the publish gate's job.

Usage:
    python content-engine/bin/write-content.py <slug>
    python content-engine/bin/write-content.py --all
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

ENGINE = REPO_ROOT / "content-engine"
PRODUCTION = ENGINE / "brief" / "production"
DRAFTS = ENGINE / "drafts"

# Hard guarantee: this stage cannot write into the theme.
FORBIDDEN_OUTPUT = "wordpress"


def load(path):
    return json.loads(path.read_text(encoding="utf-8"))


def refuse(slug, reason, action):
    print("  REFUSED  %-18s %s" % (slug, reason))
    if action:
        print("           recommended: %s" % action)
    return False


def build_package(slug, brief):
    """The complete, and only, instruction set a writer may work from."""
    angles_path = ENGINE / "angles" / ("%s.json" % slug)
    claims_path = ENGINE / "claims" / ("%s.json" % slug)
    angle_id = brief["differentiation"]["approved_angle_id"]

    angles = load(angles_path) if angles_path.is_file() else {"angles": []}
    shared = ENGINE / "claims" / "_shared-business.json"
    ledger = {c["id"]: c for c in (load(claims_path).get("claims", []) if claims_path.is_file() else [])}
    if shared.is_file():
        ledger.update({c["id"]: c for c in load(shared).get("claims", [])})
    angle = next((a for a in angles.get("angles", []) if a["id"] == angle_id), None)

    allowed = []
    for cid in brief["claims"]["allowed_claim_ids"]:
        claim = ledger.get(cid)
        if not claim:
            continue
        allowed.append({
            "id": cid,
            "statement": claim.get("claim_boundary") or claim["claim"],
            "risk": claim["risk"],
            "verification": claim.get("verification"),
            "source_ids": claim.get("source_ids", []),
            "note": ("Use the boundary statement, not the original — the original overstates what "
                     "the evidence supports." if claim.get("claim_boundary") else ""),
        })

    return {
        "_instructions": [
            "Author canonical v2 content into this draft's `content` block.",
            "State as fact ONLY what appears in allowed_claims below. Nothing else.",
            "Derive the section plan from the angle and this page's purpose. Do not reuse a "
            "sibling's structure, and do not open a sibling page for reference.",
            "Write FAQs from this page's own questions. Questions listed under "
            "sibling_constraints.questions_already_answered are off limits.",
            "Business identity is tokenised: write {business} and {phone}, never literals.",
            "Do not invent statistics, reviews, testimonials, first-hand experience, staff, "
            "years in business, or local ownership.",
            "Do not put source URLs or citations into prose.",
            "Record every claim id you relied on in generation.claim_ids_used.",
            "Complete coverage.self_check honestly, including where the draft falls short.",
        ],
        "angle": {
            "id": angle_id,
            "statement": (angle or {}).get("statement", ""),
            "why_unique": (angle or {}).get("why_unique", ""),
            "recommended_sections": (angle or {}).get("recommended_sections", []),
        },
        "allowed_claims": allowed,
        "blocked_claim_count": len(brief["claims"]["blocked_claim_ids"]),
        "query_model": brief["query_model"],
        "entity_model": brief["entity_model"],
        "local_model": brief.get("local_model", {}),
        "information_gain": brief["information_gain"],
        "content_architecture": brief["content_architecture"],
        "faq_plan": brief["faq_plan"],
        "internal_linking": brief["internal_linking"],
        "sibling_constraints": brief["sibling_constraints"],
        "writing_constraints": brief["writing_constraints"],
    }


def write_one(slug, force):
    path = PRODUCTION / ("%s.json" % slug)
    if not path.is_file():
        return refuse(slug, "no production brief — run create-content-brief.py", None)

    brief = load(path)
    gate = brief.get("gate", {})

    if gate.get("status") != "ALLOWED":
        return refuse(slug, gate.get("blocked_reason", "gate BLOCKED"),
                      gate.get("recommended_action"))

    # Re-verify against the registry rather than trusting the brief. A
    # service-area page carries no angle by design (ADR-0009), so the angle
    # checks apply only to informational pages.
    angle_id = brief["differentiation"]["approved_angle_id"]
    service_area = brief.get("page_purpose_class") == "service_area_transactional"

    if not service_area:
        angles_path = ENGINE / "angles" / ("%s.json" % slug)
        if not angles_path.is_file():
            return refuse(slug, "no angle registry for this page", "complete_research")

        angle = next((a for a in load(angles_path).get("angles", []) if a["id"] == angle_id), None)
        if angle is None:
            return refuse(slug, "brief names angle '%s' which does not exist" % angle_id, "complete_research")
        if angle.get("status") not in bf.WRITABLE_ANGLE_STATUS:
            return refuse(slug, "angle '%s' has status '%s' — not approved"
                          % (angle_id, angle.get("status")), "complete_research")
    if not brief["claims"]["allowed_claim_ids"]:
        return refuse(slug, "no publishable claims — the writer would have no factual budget",
                      "complete_research")

    out = DRAFTS / ("%s.json" % slug)
    if FORBIDDEN_OUTPUT in out.parts:
        raise RuntimeError("refusing to write outside content-engine/drafts/")
    if out.exists() and not force:
        print("  skip     %-18s draft exists (use --force)" % slug)
        return False

    draft = {
        "slug": slug,
        "status": "authoring_package",
        "generation": {
            "generated_by": "write-content.py",
            "brief_version": brief["brief_version"],
            "brief_path": path.relative_to(REPO_ROOT).as_posix(),
            "angle_id": angle_id,
            "claim_ids_used": [],
            "source_artifacts": brief["source_artifacts"],
            "notes": ("Authoring package. The `content` block is empty until an author fills it "
                      "against the package, then validate-draft.py checks the result."),
        },
        "coverage": {"concepts_covered": [], "concepts_not_covered": [], "questions_answered": [],
                     "entities_explained": [], "information_gain_delivered": []},
        "content": {},
        "_authoring_package": build_package(slug, brief),
    }
    DRAFTS.mkdir(parents=True, exist_ok=True)
    out.write_text(json.dumps(draft, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")
    print("  PACKAGE  %-18s angle=%s, %d allowed claim(s) -> %s"
          % (slug, angle_id, len(brief["claims"]["allowed_claim_ids"]),
             out.relative_to(REPO_ROOT).as_posix()))
    return True


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slug", nargs="?")
    parser.add_argument("--all", action="store_true")
    parser.add_argument("--force", action="store_true")
    args = parser.parse_args()

    slugs = ([p.stem for p in sorted(PRODUCTION.glob("*.json"))]
             if args.all else ([args.slug] if args.slug else []))
    if not slugs:
        parser.error("give a slug or --all")

    produced = sum(1 for s in slugs if write_one(s, args.force))
    print("-" * 64)
    print("%d authoring package(s) produced, %d refused" % (produced, len(slugs) - produced))
    if not produced:
        print("NO_APPROVED_ANGLE_FOR_END_TO_END_GENERATION")
        print("The writer refused every page. This is the system working: no approved angle, "
              "no publishable claims, no generation.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
