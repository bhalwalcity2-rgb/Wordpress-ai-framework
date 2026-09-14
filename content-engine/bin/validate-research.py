#!/usr/bin/env python3
"""Validate the research artifacts and the rules that span them.

Schema validation catches a malformed file. The rules that matter here are
the cross-artifact ones, because they are the controls that stop a
templated corpus from being produced with confidence:

  ERROR  a high-risk legal/regulatory claim without authoritative sourcing
  ERROR  a claim marked publishable that its own provenance cannot support
  ERROR  an unresolved or unsupported claim marked publishable
  ERROR  duplicate angle ids anywhere in the registry
  ERROR  the same approved/used angle claimed by two pages
  ERROR  an approved angle with no publishable evidence behind it
  ERROR  an angle whose statement collapses once place names are masked
  ERROR  an angle citing a claim id that does not exist
  ERROR  a page with no research artifacts at all
  WARN   NO_SUPPORTED_DIFFERENTIATION (expected, and not a failure)
  WARN   an unverified claim still live in published copy
  WARN   a page whose query network is entirely shared with its siblings

Usage:
    python content-engine/bin/validate-research.py [theme-slug]
    python content-engine/bin/validate-research.py --strict
"""

import argparse
import datetime
import json
import sys
from collections import defaultdict
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

import research as rs                                    # noqa: E402
from jsonschema_mini import ERROR, SchemaValidator       # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
SCHEMA_DIR = ENGINE / "config" / "schema"
DEFAULT_THEME = "kadence-child-lvjcb"

SOURCES = ENGINE / "sources" / "registry.json"

ARTIFACTS = (
    ("place", "place.schema.json"),
    ("claims", "claim.schema.json"),
    ("angles", "angle.schema.json"),
    ("intent", "intent.schema.json"),
    ("entities", "entities.schema.json"),
    ("queries", "queries.schema.json"),
)


def load(path):
    return json.loads(path.read_text(encoding="utf-8"))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("theme", nargs="?", default=DEFAULT_THEME)
    parser.add_argument("--strict", action="store_true")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    colour = sys.stdout.isatty() and not args.no_color
    RED, YEL, GRN, OFF = (("\033[31m", "\033[33m", "\033[32m", "\033[0m") if colour else ("",) * 4)

    validator = SchemaValidator(SCHEMA_DIR)
    errors, warnings = [], []
    today = datetime.date.today().isoformat()

    known_sources = {}
    if SOURCES.is_file():
        try:
            known_sources = {s["id"]: s for s in load(SOURCES).get("sources", [])}
        except (json.JSONDecodeError, KeyError) as exc:
            errors.append("sources/registry.json: unreadable — %s" % exc)
    else:
        warnings.append("sources/registry.json: absent — no claim can cite a source")

    slugs = sorted(p.stem for p in (ENGINE / "brief").glob("*.json"))
    if not slugs:
        print("No retro-briefs found — run analyze-corpus.py then bootstrap-research.py.")
        return 1

    # -- per-artifact schema validation ----------------------------------
    documents = defaultdict(dict)
    for folder, schema in ARTIFACTS:
        directory = ENGINE / folder
        for slug in slugs:
            path = directory / ("%s.json" % slug)
            if not path.is_file():
                errors.append("%s: missing %s/%s.json" % (slug, folder, slug))
                continue
            try:
                data = load(path)
            except json.JSONDecodeError as exc:
                errors.append("%s/%s.json: malformed JSON — %s" % (folder, slug, exc))
                continue
            documents[folder][slug] = data
            for issue in validator.validate(data, schema):
                target = errors if issue.level == ERROR else warnings
                target.append("%s/%s.json %s" % (folder, slug, issue))

    # -- claims: provenance must justify publishability -------------------
    claims_by_page = {}
    for slug, doc in documents["claims"].items():
        by_id = {}
        for claim in doc.get("claims", []):
            by_id[claim["id"]] = claim
            ok, reason = rs.claim_is_publishable(claim)

            if claim.get("publishable") and not ok:
                errors.append("claims/%s.json: claim '%s' is marked publishable but %s"
                              % (slug, claim["id"], reason))

            if claim["claim_type"] == "government_legal_regulatory":
                if claim.get("source_type") not in rs.AUTHORITATIVE_SOURCES:
                    (errors if claim.get("publishable") else warnings).append(
                        "claims/%s.json: legal/regulatory claim '%s' has source_type '%s' — "
                        "requires an official government or primary source"
                        % (slug, claim["id"], claim.get("source_type")))

            verification = claim.get("verification", "NOT_YET_CHECKED")

            # A refuted or unfound claim can never be publishable.
            if verification in ("CONTRADICTED", "UNSUPPORTED") and claim.get("publishable"):
                errors.append("claims/%s.json: '%s' is %s yet marked publishable"
                              % (slug, claim["id"], verification))

            # A narrowed claim must state its boundary, or the stronger version
            # is what a writer will reach for.
            if verification == "PARTIALLY_SUPPORTED" and not claim.get("claim_boundary"):
                errors.append("claims/%s.json: '%s' is PARTIALLY_SUPPORTED but has no claim_boundary "
                              "— the narrowed statement the evidence actually reaches"
                              % (slug, claim["id"]))

            if verification == "CONTRADICTED" and not claim.get("contradiction"):
                errors.append("claims/%s.json: '%s' is CONTRADICTED but does not say what the "
                              "source states instead" % (slug, claim["id"]))

            # Cited sources must exist in the registry.
            for sid in claim.get("source_ids", []):
                if sid not in known_sources:
                    errors.append("claims/%s.json: '%s' cites unknown source '%s'"
                                  % (slug, claim["id"], sid))

            # Publishable claims need a live review date; stale evidence is
            # not evidence.
            if claim.get("publishable"):
                if not claim.get("source_ids"):
                    errors.append("claims/%s.json: '%s' is publishable but cites no source_ids"
                                  % (slug, claim["id"]))
                if not claim.get("review_by"):
                    errors.append("claims/%s.json: '%s' is publishable but has no review_by date"
                                  % (slug, claim["id"]))
            for field in ("review_by", "expires_on"):
                when = claim.get(field)
                if when and when < today:
                    (errors if claim.get("publishable") else warnings).append(
                        "claims/%s.json: '%s' %s %s has passed — evidence is stale and must be "
                        "re-checked" % (slug, claim["id"], field, when))

            if claim.get("source_type") == "unverified" and claim.get("appears_in_existing_copy"):
                warnings.append("claims/%s.json: '%s' is live on the site with no source"
                                % (slug, claim["id"]))
        claims_by_page[slug] = by_id

    # -- angles: uniqueness and evidence ---------------------------------
    seen_ids = {}
    claimed_angles = {}
    for slug, doc in documents["angles"].items():
        verdict = doc.get("differentiation_verdict")
        if verdict == "NO_SUPPORTED_DIFFERENTIATION":
            warnings.append(
                "angles/%s.json: NO_SUPPORTED_DIFFERENTIATION — recommendation '%s'. "
                "Expected outcome, not a failure." % (slug, doc.get("recommendation_if_unsupported")))
            if doc.get("recommendation_if_unsupported") in (None, "not_applicable"):
                errors.append("angles/%s.json: NO_SUPPORTED_DIFFERENTIATION requires a recommendation"
                              % slug)

        for angle in doc.get("angles", []):
            aid = angle["id"]

            if aid in seen_ids:
                errors.append("angles/%s.json: duplicate angle id '%s' (also in %s)"
                              % (slug, aid, seen_ids[aid]))
            seen_ids[aid] = slug

            if angle.get("page") != slug:
                errors.append("angles/%s.json: angle '%s' declares page '%s'"
                              % (slug, aid, angle.get("page")))

            # An approved or used angle is owned by exactly one page.
            if angle["status"] in ("approved", "used"):
                key = angle["statement"].strip().lower()
                if key in claimed_angles and claimed_angles[key] != slug:
                    errors.append(
                        "angles/%s.json: angle '%s' reuses a situation already %s by '%s'. "
                        "A used angle cannot be reassigned without a documented override."
                        % (slug, aid, angle["status"], claimed_angles[key]))
                claimed_angles[key] = slug

            # Cited evidence must exist.
            for ref in angle.get("evidence", []):
                if ref["claim_id"] not in claims_by_page.get(slug, {}):
                    errors.append("angles/%s.json: angle '%s' cites unknown claim '%s'"
                                  % (slug, aid, ref["claim_id"]))

            # Approval requires publishable evidence.
            if angle["status"] in ("approved", "used"):
                ok, reason = rs.angle_can_be_approved(angle, claims_by_page.get(slug, {}))
                if not ok:
                    errors.append("angles/%s.json: angle '%s' is '%s' but %s"
                                  % (slug, aid, angle["status"], reason))

            if angle["status"] in ("unsupported", "retired") and not angle.get("retirement_reason"):
                errors.append("angles/%s.json: angle '%s' is '%s' but gives no retirement_reason"
                              % (slug, aid, angle["status"]))

            for sid in angle.get("source_ids", []):
                if sid not in known_sources:
                    errors.append("angles/%s.json: angle '%s' cites unknown source '%s'"
                                  % (slug, aid, sid))

            # Superficial angles are rejected regardless of status.
            place_terms = set(documents["entities"].get(slug, {}).get("local_entities", []))
            if rs.is_superficial_angle(angle["statement"], place_terms):
                errors.append(
                    "angles/%s.json: angle '%s' collapses when place names are masked — "
                    "a landmark, ZIP or city name is not an angle" % (slug, aid))

    # -- corpus integration ----------------------------------------------
    for slug, doc in documents["queries"].items():
        summary = doc.get("coverage_summary", {})
        if summary.get("total_concepts") and not summary.get("distinctive_concepts"):
            warnings.append("queries/%s.json: every concept is shared with siblings — "
                            "the query network describes a templated page" % slug)

    for slug, doc in documents["place"].items():
        if doc.get("research_status", {}).get("overall") == "verified":
            unresolved = [c for c in documents["claims"].get(slug, {}).get("claims", [])
                          if c["status"] in rs.BLOCKING_STATUS]
            if unresolved:
                errors.append("place/%s.json: marked 'verified' while %d claims remain unresolved"
                              % (slug, len(unresolved)))

    # -- report ----------------------------------------------------------
    print("Research validation — %d pages, %d artifacts"
          % (len(slugs), sum(len(v) for v in documents.values())))
    for message in errors:
        print("  %sERROR%s  %s" % (RED, OFF, message))
    for message in warnings:
        print("  %sWARN %s  %s" % (YEL, OFF, message))

    print("-" * 64)
    summary = "%d error(s) · %d warning(s)" % (len(errors), len(warnings))
    if errors:
        print("%sFAIL%s  %s" % (RED, OFF, summary))
        return 1
    if warnings and args.strict:
        print("%sFAIL (strict)%s  %s" % (YEL, OFF, summary))
        return 2
    print("%sPASS%s  %s" % (GRN, OFF, summary))
    return 0


if __name__ == "__main__":
    sys.exit(main())
