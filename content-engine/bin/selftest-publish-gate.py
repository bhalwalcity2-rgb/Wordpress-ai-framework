#!/usr/bin/env python3
"""Self-test for the publish gate.

Proves the gate refuses everything it must, then proves it promotes correctly
once — using a clearly-marked FIXTURE and restoring every touched file in a
finally block.

The production corpus is never modified: the fixture promotes to a fixture
slug that does not exist in business-config.php, so no real page can be
overwritten even if the test aborts mid-run.

Usage:
    python content-engine/bin/selftest-publish-gate.py
"""

import json
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(REPO_ROOT / "content-engine" / "lib"))

import promotion as pm         # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
BIN = ENGINE / "bin"
THEME = "kadence-child-lvjcb"

# summerlin is a real configured location, so destination resolution works,
# but every artifact touched is backed up and restored.
SLUG = "summerlin"
ANGLE = "summerlin-gate-fixture"
CLAIM = "summerlin-gate-fixture-claim"

TOUCHED = [
    ENGINE / "angles" / ("%s.json" % SLUG),
    ENGINE / "claims" / ("%s.json" % SLUG),
    ENGINE / "brief" / "production" / ("%s.json" % SLUG),
    ENGINE / "drafts" / ("%s.json" % SLUG),
    ENGINE / "approvals" / ("%s.json" % SLUG),
    REPO_ROOT / "wordpress" / "themes" / THEME / "content" / "locations" / ("%s.json" % SLUG),
]

results = []


def record(name, ok, detail=""):
    results.append((name, ok))
    line = "  %s  %s%s" % ("PASS" if ok else "FAIL", name, ("  - " + detail) if detail else "")
    print(line.encode("ascii", "replace").decode("ascii"))
    return ok


def gate(*argv):
    out = subprocess.run([sys.executable, str(BIN / "publish-gate.py"), SLUG, *argv, "--no-color"],
                         capture_output=True, text=True, encoding="utf-8", errors="replace")
    return out.returncode, (out.stdout or "") + (out.stderr or "")


def load(p):
    return json.loads(p.read_text(encoding="utf-8"))


def save(p, d):
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(json.dumps(d, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")


def build_fixture():
    """A complete, valid, approved chain — every part marked FIXTURE."""
    claims = load(ENGINE / "claims" / ("%s.json" % SLUG))
    claims["claims"].append({
        "id": CLAIM,
        "claim": "GATE FIXTURE - a business-provided process fact used only by the self-test.",
        "claim_type": "business_fact", "source": "selftest-publish-gate.py",
        "source_type": "official_business", "verified_on": "2026-09-06",
        "source_ids": ["waif-business-config"],
        "review_by": "2099-01-01", "confidence": "high", "risk": "low",
        "status": "verified", "verification": "SUPPORTED", "publishable": True,
        "appears_in_existing_copy": False, "notes": "FIXTURE.",
    })
    save(ENGINE / "claims" / ("%s.json" % SLUG), claims)

    angles = load(ENGINE / "angles" / ("%s.json" % SLUG))
    angles["differentiation_verdict"] = "SUPPORTED"
    angles["angles"].append({
        "id": ANGLE, "page": SLUG, "status": "approved", "angle_type": "logistical_situation",
        "statement": ("GATE FIXTURE - arranging guard-gate entry before dispatch changes what a "
                      "seller must prepare and which collection slots are realistic."),
        "evidence": [{"claim_id": CLAIM, "relevance": "fixture"}],
        "why_unique": "FIXTURE.", "must_not_overlap_with": ["paradise"],
        "related_entities": ["access", "scheduling"], "notes": "FIXTURE.",
    })
    save(ENGINE / "angles" / ("%s.json" % SLUG), angles)

    subprocess.run([sys.executable, str(BIN / "create-content-brief.py"), SLUG, "--force"],
                   capture_output=True, text=True)
    ensure_fixture_gain()
    subprocess.run([sys.executable, str(BIN / "write-content.py"), SLUG, "--force"],
                   capture_output=True, text=True)

    draft_path = ENGINE / "drafts" / ("%s.json" % SLUG)
    draft = load(draft_path)
    draft["status"] = "drafted"
    draft["generation"]["claim_ids_used"] = [CLAIM]
    draft["content"] = {
        "slug": SLUG, "city": "Summerlin", "state": "NV",
        "seo_title": "Selling a Junk Car Behind a Guard Gate | {business}",
        "seo_description": ("What to arrange before a tow truck can reach a vehicle inside a gated "
                            "community, and how it changes scheduling. Call {phone}."),
        "hero_heading": "Selling a Junk Car From a Gated Community",
        "hero_description": "Access is the part people forget, and sorting it out first saves a second visit.",
        "sections": [
            {"type": "content", "heading": "Why Gate Access Changes the Scheduling Conversation",
             "paragraphs": [
                 ("A flatbed cannot be waved through on arrival. Somebody has to be expecting it, "
                  "which means entry is settled before a time is agreed rather than after."),
                 ("That is why the scheduling call asks about entry first: a guest code, a call to "
                  "the guard house, or an escort each imply a different realistic slot."),
                 ("Knowing which applies before dispatch is the difference between one visit and two.")]},
            {"type": "steps", "heading": "Arranging Access Before Pickup",
             "steps": [
                 {"heading": "Confirm who authorises entry",
                  "description": "A resident, a management office, or a guard house, depending on the development."},
                 {"heading": "Register the collection window",
                  "description": "Most gated developments accept a named visitor for a stated window."},
                 {"heading": "Tell us what the driver will meet",
                  "description": "Call {phone} with the arrangement so the driver arrives expecting it."}]},
        ],
        "faq_heading": "Access Questions Before a Pickup",
        "faq": [
            {"question": "Does the driver need a guest code in advance?",
             "answer": "Usually. Where entry is controlled by a code or visitor list it must be arranged before dispatch."},
            {"question": "What if the guard house will not admit a tow truck?",
             "answer": "The vehicle is met at an agreed point instead. Say so when scheduling and the driver plans for it."},
        ],
    }
    draft["coverage"]["information_gain_delivered"] = ["Gate access as a scheduling constraint"]
    draft["coverage"]["self_check"] = {"search_intent": "ok", "semantic_coverage": "ok",
                                       "information_gain": "ok", "differentiation": "ok",
                                       "local_relevance": "ok", "naturalness": "ok",
                                       "utility": "ok", "claims": "one fixture claim", "trust": "ok"}
    save(draft_path, draft)
    return draft_path


def approve(draft_path, wrong_hash=False):
    save(ENGINE / "approvals" / ("%s.json" % SLUG), {
        "slug": SLUG, "decision": "approved", "approved_by": "selftest",
        "approved_on": "2026-09-06",
        "draft_sha256": ("0" * 64) if wrong_hash else pm.sha256_file(draft_path),
        "note": "FIXTURE approval.",
    })


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
    backup = Path(tempfile.mkdtemp(prefix="gate-selftest-"))
    existed = {}
    for p in TOUCHED:
        existed[p] = p.is_file()
        if p.is_file():
            shutil.copy2(p, backup / ("%s__%s" % (p.parent.name, p.name)))

    try:
        print("A. Refusal without prerequisites")
        code, out = gate()
        record("refuses with no draft", code == 1 and "BLOCKED" in out)

        print("\nB. Destination and slug safety")
        for bad in ("../../etc/passwd", "Summerlin", "a/b"):
            r = subprocess.run([sys.executable, str(BIN / "publish-gate.py"), bad, "--no-color"],
                               capture_output=True, text=True, encoding="utf-8", errors="replace")
            if not record("rejects unsafe slug %r" % bad,
                          r.returncode == 1 and "invalid slug" in (r.stdout + r.stderr)):
                break
        try:
            pm.resolve_destination(REPO_ROOT, THEME, "location", "las-vegas", ["las-vegas"])
            record("rejects a location file for the primary city", False)
        except pm.PromotionError as exc:
            record("rejects a location file for the primary city", "primary city" in str(exc))

        print("\nC. Full chain with an approved FIXTURE")
        draft_path = build_fixture()
        code, out = gate("--check")
        record("blocks when not yet approved", code == 1 and "NOT APPROVED" in out)

        approve(draft_path, wrong_hash=True)
        code, out = gate("--check")
        record("rejects approval naming a different draft hash",
               code == 1 and "must be re-approved" in out)

        approve(draft_path)
        # This fixture is a deliberately minimal draft standing in for a real
        # one, so promoting it over the live summerlin page drops sections the
        # real page has. That is a genuine regression and the gate is right to
        # refuse it; these cases carry --allow-regression to get past the
        # guard, and the guard itself is tested on its own below.
        code, out = gate("--check", "--allow-regression")
        record("passes prerequisites once genuinely approved", code == 0 and "READY" in out, out.strip().splitlines()[-1])

        destination = REPO_ROOT / "wordpress" / "themes" / THEME / "content" / "locations" / ("%s.json" % SLUG)
        before = pm.sha256_file(destination) if destination.is_file() else None

        code, out = gate("--check")
        record("refuses to promote a draft that drops live components",
               code == 1 and "refusing to silently downgrade" in out)

        code, out = gate("--check", "--allow-regression")
        record("--allow-regression overrides the downgrade guard",
               code == 0 and "ALLOWED REGRESSION" in out and "READY" in out)

        # The destination already exists, so a dry run must carry --update for
        # the same reason a real promotion does: the overwrite guard runs first.
        code, out = gate("--dry-run", "--update", "--allow-regression")
        after_dry = pm.sha256_file(destination) if destination.is_file() else None
        record("dry run promotes nothing",
               code == 0 and "DRY RUN" in out and after_dry == before)

        code, out = gate("--allow-regression")
        record("refuses to overwrite without --update",
               code == 1 and "already exists" in out if before else True)

        code, out = gate("--update", "--allow-regression")
        record("promotes with --update", code == 0 and "PROMOTED" in out)

        promoted = json.loads(destination.read_text(encoding="utf-8"))
        record("destination holds the draft's canonical content",
               promoted.get("hero_heading") == "Selling a Junk Car From a Gated Community")

        rec_path = ENGINE / "reports" / "promotions" / ("%s.latest.json" % SLUG)
        rec = load(rec_path) if rec_path.is_file() else {}
        record("promotion record written with audit fields",
               rec.get("angle_id") == ANGLE and rec.get("claim_ids") == [CLAIM]
               and rec.get("mode") == "update" and bool(rec.get("destination_sha256"))
               and rec.get("previous_destination_sha256") == before)

        draft_after = load(draft_path)
        record("source draft left unmodified", draft_after["content"]["slug"] == SLUG
               and draft_path.is_file())

        print("\nD. Content-level refusals")
        good = load(draft_path)

        bad = json.loads(json.dumps(good))
        bad["content"]["hero_description"] = "Call First Choice Junk Car on (866) 748-3697."
        save(draft_path, bad); approve(draft_path)
        code, out = gate("--update", "--allow-regression")
        record("rejects untokenised business identity",
               code == 1 and ("tokenised" in out or "literal phone" in out))

        bad = json.loads(json.dumps(good))
        bad["generation"]["claim_ids_used"] = ["never-allowed-claim"]
        save(draft_path, bad); approve(draft_path)
        code, out = gate("--update", "--allow-regression")
        record("rejects a claim the brief did not allow", code == 1)

        bad = json.loads(json.dumps(good))
        bad["content"]["sections"] = [
            {"type": "content", "heading": "A", "paragraphs": ["x y z", "p q r"]},
            {"type": "content", "heading": "B", "paragraphs": ["x y z", "p q r"]},
            {"type": "content", "heading": "C", "paragraphs": ["x y z"]}]
        save(draft_path, bad); approve(draft_path)
        code, out = gate("--update", "--allow-regression")
        record("rejects structure cloned from a sibling", code == 1)

        bad = json.loads(json.dumps(good))
        del bad["content"]["seo_title"]
        save(draft_path, bad); approve(draft_path)
        code, out = gate("--update", "--allow-regression")
        record("rejects malformed content missing required metadata", code == 1)

        angles = load(ENGINE / "angles" / ("%s.json" % SLUG))
        for a in angles["angles"]:
            if a["id"] == ANGLE:
                a["status"] = "proposed"
        save(ENGINE / "angles" / ("%s.json" % SLUG), angles)
        save(draft_path, good); approve(draft_path)
        code, out = gate("--update", "--allow-regression")
        record("rejects an angle demoted from approved", code == 1 and "approved" in out)

    finally:
        for p in TOUCHED:
            src = backup / ("%s__%s" % (p.parent.name, p.name))
            if existed.get(p) and src.is_file():
                shutil.copy2(src, p)
            elif p.is_file():
                p.unlink()
        for stray in (ENGINE / "reports" / "promotions").glob("%s*.json" % SLUG):
            stray.unlink()
        shutil.rmtree(backup, ignore_errors=True)
        print("\n  -- fixtures removed, production content restored --")

    failed = [n for n, ok in results if not ok]
    print("-" * 64)
    print("%d/%d checks passed" % (len(results) - len(failed), len(results)))
    if failed:
        print("FAILED: %s" % ", ".join(failed))
        return 1
    print("Publish-gate self-test PASSED")
    return 0


if __name__ == "__main__":
    sys.exit(main())
