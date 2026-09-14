#!/usr/bin/env python3
"""End-to-end self-test for the production pipeline.

The corpus currently has no approved angle, so the real pipeline refuses
every page — which is the correct behaviour and is itself asserted below.
But "it refuses everything" would also be true of a pipeline that is simply
broken, so the allowed path has to be exercised too.

This injects a clearly-marked FIXTURE angle and claim, runs brief -> validate
-> write -> author -> validate, asserts the negative paths still fail, and
restores every touched file in a finally block. No fixture survives the run,
and no page is ever approved in the repository as a side effect.

Usage:
    python content-engine/bin/selftest-pipeline.py [--keep-going]
"""

import argparse
import json
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
ENGINE = REPO_ROOT / "content-engine"
BIN = ENGINE / "bin"
FIXTURE_SLUG = "summerlin"
FIXTURE_ANGLE = "summerlin-selftest-fixture"
FIXTURE_CLAIM = "summerlin-selftest-fixture-claim"
CLAIM = FIXTURE_CLAIM
SLUG = FIXTURE_SLUG

TOUCHED = [
    ENGINE / "angles" / ("%s.json" % FIXTURE_SLUG),
    ENGINE / "claims" / ("%s.json" % FIXTURE_SLUG),
    ENGINE / "brief" / "production" / ("%s.json" % FIXTURE_SLUG),
    ENGINE / "drafts" / ("%s.json" % FIXTURE_SLUG),
]

results = []


def record(name, passed, detail=""):
    results.append((name, passed, detail))
    line = "  %s  %s%s" % ("PASS" if passed else "FAIL", name, ("  - " + detail) if detail else "")
    print(line.encode("ascii", "replace").decode("ascii"))
    return passed


def run(script, *argv):
    """Run a pipeline script. Only the validators take --no-color."""
    cmd = [sys.executable, str(BIN / script), *argv]
    if script.startswith("validate-"):
        cmd.append("--no-color")
    out = subprocess.run(cmd, capture_output=True, text=True, encoding="utf-8", errors="replace")
    return out.returncode, (out.stdout or "") + (out.stderr or "")


def load(p):
    return json.loads(p.read_text(encoding="utf-8"))


def save(p, d):
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(json.dumps(d, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")


def inject_fixture():
    """Approve a fixture angle and one publishable claim, clearly labelled."""
    claims_path = ENGINE / "claims" / ("%s.json" % FIXTURE_SLUG)
    claims = load(claims_path)
    claims["claims"].append({
        "id": FIXTURE_CLAIM,
        "claim": "SELFTEST FIXTURE — a business-provided process fact used only by the self-test.",
        "claim_type": "business_fact",
        "source": "content-engine/bin/selftest-pipeline.py",
        "source_type": "official_business",
        "verified_on": "2026-09-05",
        "review_by": "2099-01-01",
        "confidence": "high",
        "risk": "low",
        "status": "verified",
        "verification": "SUPPORTED",
        "publishable": True,
        "appears_in_existing_copy": False,
        "notes": "FIXTURE. Removed when the self-test finishes.",
    })
    save(claims_path, claims)

    angles_path = ENGINE / "angles" / ("%s.json" % FIXTURE_SLUG)
    angles = load(angles_path)
    angles["differentiation_verdict"] = "SUPPORTED"
    angles["angles"].append({
        "id": FIXTURE_ANGLE,
        "page": FIXTURE_SLUG,
        "status": "approved",
        "angle_type": "logistical_situation",
        "statement": ("SELFTEST FIXTURE — sellers in gated master-planned developments must arrange "
                      "guard-gate access before a flatbed can reach the vehicle, which changes the "
                      "scheduling conversation and what the seller must prepare in advance."),
        "evidence": [{"claim_id": FIXTURE_CLAIM, "relevance": "fixture"}],
        "why_unique": "FIXTURE for the self-test only.",
        "must_not_overlap_with": ["paradise", "enterprise"],
        "related_entities": ["access", "scheduling", "flatbed"],
        "recommended_questions": [],
        "recommended_sections": [],
        "notes": "FIXTURE. Removed when the self-test finishes.",
    })
    save(angles_path, angles)


def author_draft():
    """Fill the authoring package with minimal valid canonical v2 content."""
    path = ENGINE / "drafts" / ("%s.json" % FIXTURE_SLUG)
    draft = load(path)
    draft["status"] = "drafted"
    draft["generation"]["claim_ids_used"] = [FIXTURE_CLAIM]
    draft["content"] = {
        "slug": FIXTURE_SLUG,
        "city": "Summerlin",
        "state": "NV",
        "seo_title": "Selling a Junk Car Behind a Guard Gate | {business}",
        "seo_description": ("What to arrange before a tow truck can reach a vehicle parked inside a "
                            "gated community, and how it changes scheduling. Call {phone}."),
        "hero_heading": "Selling a Junk Car From a Gated Community",
        "hero_description": ("Access is the part people forget. Sorting it out before the truck is "
                             "dispatched is what turns a two-visit job into one."),
        "sections": [
            {
                "type": "content",
                "heading": "Why Gate Access Changes the Scheduling Conversation",
                "paragraphs": [
                    ("A flatbed cannot be waved through on arrival the way a delivery van often is. "
                     "Somebody has to be expecting it, which means the access arrangement is settled "
                     "before a time is agreed rather than after."),
                    ("That is why the scheduling call asks about entry first. Knowing whether the "
                     "driver needs a guest code, a call to the guard house, or an escort decides "
                     "which slot is realistic."),
                ],
            },
            {
                "type": "steps",
                "heading": "Arranging Access Before Pickup",
                "steps": [
                    {"heading": "Confirm who authorises entry",
                     "description": "A resident, a management office, or a guard house — whichever applies where you live."},
                    {"heading": "Register the pickup window",
                     "description": "Most gated developments accept a named visitor for a stated window rather than an exact minute."},
                    {"heading": "Tell us what the driver will meet",
                     "description": "Call {phone} with the entry arrangement so the driver arrives expecting it."},
                ],
            },
        ],
        "faq_heading": "Access Questions Before a Pickup",
        "faq": [
            {"question": "Does the driver need a guest code in advance?",
             "answer": ("Usually yes. Where entry is controlled by a code or a visitor list, it has "
                        "to be arranged before dispatch, not at the gate.")},
            {"question": "What if the guard house will not admit a tow truck?",
             "answer": ("Then the vehicle is met at a agreed point inside or just outside the "
                        "development. Tell us when scheduling and the driver plans for it.")},
        ],
    }
    draft["coverage"] = {
        "concepts_covered": [],
        "concepts_not_covered": [],
        "questions_answered": ["Does the driver need a guest code in advance?"],
        "entities_explained": ["access", "scheduling", "flatbed"],
        "information_gain_delivered": ["Gate access as a scheduling constraint"],
        "self_check": {
            "search_intent": "Transactional-local; the page answers an access question then routes to contact.",
            "semantic_coverage": "Access, scheduling and vehicle recovery explained as a chain.",
            "information_gain": "Access logistics, which no sibling covers.",
            "differentiation": "Structure follows the access situation rather than a generic plan.",
            "local_relevance": "Applies specifically to gated developments.",
            "naturalness": "No forced keyword variants.",
            "utility": "A reader learns what to arrange before calling.",
            "claims": "One fixture claim; no other factual assertions.",
            "trust": "No invented experience, reviews or history.",
        },
    }
    save(path, draft)


def ensure_fixture_gain():
    """Give the fixture brief its own information gain.

    The brief derives gain from whatever the corpus currently measures, which
    legitimately moves whenever content changes. A test that depends on that
    is testing the corpus, not the gate — it broke once already when a
    correction pass shifted the entity distribution. The fixture states its
    own gain so the gate mechanics are exercised deterministically.
    """
    path = ENGINE / "brief" / "production" / ("%s.json" % SLUG)
    brief = load(path)
    brief["information_gain"]["required_new_information"] = [{
        "information": "How guard-gate entry has to be arranged before a flatbed is dispatched.",
        "why_it_is_new": "FIXTURE: no sibling covers access logistics.",
        "evidence_level": "business_provided",
        "claim_id": CLAIM,
    }]
    save(path, brief)

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--keep-going", action="store_true")
    parser.parse_args()

    backup = Path(tempfile.mkdtemp(prefix="pipeline-selftest-"))
    existed = {}
    for p in TOUCHED:
        existed[p] = p.is_file()
        if p.is_file():
            shutil.copy2(p, backup / ("%s__%s" % (p.parent.name, p.name)))

    try:
        print("A. Refusal path - the real corpus has no approved angle")
        code, out = run("write-content.py", "--all")
        record("writer refuses every page", "0 authoring package(s) produced" in out)
        record("reports NO_APPROVED_ANGLE_FOR_END_TO_END_GENERATION",
               "NO_APPROVED_ANGLE_FOR_END_TO_END_GENERATION" in out)
        record("blocked pages carry a recommended action", "recommended:" in out)

        print("\nB. Allowed path — fixture angle injected (removed afterwards)")
        inject_fixture()
        code, out = run("create-content-brief.py", FIXTURE_SLUG, "--force")
        record("brief gate returns ALLOWED", "ALLOWED" in out and "1 allowed" in out)

        ensure_fixture_gain()
        code, out = run("validate-content-brief.py", FIXTURE_SLUG)
        record("brief passes validation", code == 0, out.strip().splitlines()[-1] if out.strip() else "")

        code, out = run("write-content.py", FIXTURE_SLUG, "--force")
        record("writer produces an authoring package", "PACKAGE" in out)

        pkg = load(ENGINE / "drafts" / ("%s.json" % FIXTURE_SLUG))
        record("package carries angle, claims and sibling constraints",
               bool(pkg["_authoring_package"]["allowed_claims"])
               and bool(pkg["_authoring_package"]["sibling_constraints"]["compared_against"])
               and pkg["generation"]["angle_id"] == FIXTURE_ANGLE)

        code, out = run("validate-draft.py", FIXTURE_SLUG)
        record("empty package warns rather than passing silently", "not yet written" in out)

        author_draft()
        code, out = run("validate-draft.py", FIXTURE_SLUG)
        record("authored draft passes validation", code == 0,
               out.strip().splitlines()[-1] if out.strip() else "")

        print("\nC. Negative paths — the draft validator must refuse these")
        path = ENGINE / "drafts" / ("%s.json" % FIXTURE_SLUG)
        good = load(path)

        bad = json.loads(json.dumps(good))
        bad["generation"]["claim_ids_used"] = ["some-claim-never-allowed"]
        save(path, bad)
        code, out = run("validate-draft.py", FIXTURE_SLUG)
        record("rejects a claim the brief did not allow", "did not allow" in out)

        bad = json.loads(json.dumps(good))
        bad["content"]["hero_description"] = "Call us on (702) 555-0134 today."
        save(path, bad)
        code, out = run("validate-draft.py", FIXTURE_SLUG)
        record("rejects a literal phone number", "literal phone number" in out)

        bad = json.loads(json.dumps(good))
        bad["generation"]["angle_id"] = "not-the-brief-angle"
        save(path, bad)
        code, out = run("validate-draft.py", FIXTURE_SLUG)
        record("rejects an angle that disagrees with the brief", "disagrees with brief angle" in out)

        bad = json.loads(json.dumps(good))
        # Mirrors the measured signature of boulder-city / enterprise
        # (content:p2, content:p2, content:p1) so the clone check has
        # something real to match rather than an arbitrary shape.
        bad["content"]["sections"] = [
            {"type": "content", "heading": "A", "paragraphs": ["x y z", "p q r"]},
            {"type": "content", "heading": "B", "paragraphs": ["x y z", "p q r"]},
            {"type": "content", "heading": "C", "paragraphs": ["x y z"]},
        ]
        save(path, bad)
        code, out = run("validate-draft.py", FIXTURE_SLUG)
        record("flags structure cloned from a sibling", "identical to" in out or "close to" in out)

    finally:
        for p in TOUCHED:
            src = backup / ("%s__%s" % (p.parent.name, p.name))
            if existed.get(p) and src.is_file():
                shutil.copy2(src, p)
            elif p.is_file():
                p.unlink()
        shutil.rmtree(backup, ignore_errors=True)
        print("\n  -- fixtures removed, all touched files restored --")

    failed = [n for n, ok, _ in results if not ok]
    print("-" * 64)
    print("%d/%d checks passed" % (len(results) - len(failed), len(results)))
    if failed:
        print("FAILED: %s" % ", ".join(failed))
        return 1
    print("Pipeline self-test PASSED - refuses without evidence, works with it.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
