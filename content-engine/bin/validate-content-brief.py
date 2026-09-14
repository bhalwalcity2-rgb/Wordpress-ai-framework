#!/usr/bin/env python3
"""Validate production briefs before anything is written from them.

Schema conformance is the easy half. The rules that matter are the ones a
brief could satisfy structurally while still authorising a templated page:

  ERROR  brief claims ALLOWED but its own gate checks did not all pass
  ERROR  names an angle that does not exist, or belongs to another page
  ERROR  names an angle whose status is not approved/used
  ERROR  the same approved angle is claimed by two briefs
  ERROR  a blocked claim id also appears in allowed_claim_ids
  ERROR  an allowed claim is not actually publishable in the ledger
  ERROR  cites a claim id that is not in the page's ledger
  ERROR  no information gain, or gain that is only place-name substitution
  ERROR  empty query map, entity relationships, or sibling constraints
  ERROR  presents a hypothesis relationship as evidence for a factual claim
  ERROR  source_artifacts point at files that do not exist
  WARN   BLOCKED brief (expected; records why and what to do instead)
  WARN   every concept group shared with siblings

Usage:
    python content-engine/bin/validate-content-brief.py [slug] [--strict]
"""

import argparse
import json
import re
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

import briefing as bf                                  # noqa: E402
import research as rs                                  # noqa: E402
from jsonschema_mini import ERROR, SchemaValidator     # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
PRODUCTION = ENGINE / "brief" / "production"

# Gain phrased only as a place, ZIP or landmark is the failure this whole
# system exists to prevent, so it is matched explicitly.
PLACE_ONLY_GAIN = re.compile(
    r"^\s*(?:covers?|serving|located|near|around|includes?)\b|^\s*\d{5}\b", re.I)


def load(path):
    return json.loads(path.read_text(encoding="utf-8"))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slug", nargs="?")
    parser.add_argument("--strict", action="store_true")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    colour = sys.stdout.isatty() and not args.no_color
    RED, YEL, GRN, OFF = (("\033[31m", "\033[33m", "\033[32m", "\033[0m") if colour else ("",) * 4)

    if not PRODUCTION.is_dir():
        print("No production briefs. Run create-content-brief.py first.")
        return 1

    paths = ([PRODUCTION / ("%s.json" % args.slug)] if args.slug
             else sorted(PRODUCTION.glob("*.json")))
    validator = SchemaValidator(ENGINE / "config" / "schema")
    errors, warnings = [], []
    claimed_angles = {}
    allowed_count = 0

    for path in paths:
        if not path.is_file():
            errors.append("%s: not found" % path.name)
            continue
        slug = path.stem
        try:
            brief = load(path)
        except json.JSONDecodeError as exc:
            errors.append("%s: malformed JSON — %s" % (path.name, exc))
            continue

        for issue in validator.validate(brief, "production-brief.schema.json"):
            (errors if issue.level == ERROR else warnings).append("brief/production/%s %s" % (path.name, issue))

        gate = brief.get("gate", {})
        status = gate.get("status")

        # The gate's own verdict must match its checks.
        failed = [c["check"] for c in gate.get("checks", []) if not c["passed"]]
        if status == "ALLOWED" and failed:
            errors.append("%s: gate says ALLOWED but these checks failed: %s"
                          % (slug, ", ".join(failed)))
        if status == "BLOCKED":
            warnings.append("%s: BLOCKED — %s -> %s"
                            % (slug, gate.get("blocked_reason"), gate.get("recommended_action")))
            if not gate.get("recommended_action") or gate.get("recommended_action") == "not_applicable":
                errors.append("%s: BLOCKED brief gives no recommended action" % slug)

        # Artifacts must exist.
        for name, ref in brief.get("source_artifacts", {}).items():
            if not (REPO_ROOT / ref).is_file():
                errors.append("%s: source_artifacts.%s points at missing %s" % (slug, name, ref))

        # ---- angle authority -------------------------------------------
        angles_doc = {}
        angles_path = ENGINE / "angles" / ("%s.json" % slug)
        if angles_path.is_file():
            angles_doc = load(angles_path)
        by_id = {a["id"]: a for a in angles_doc.get("angles", [])}

        angle_id = brief.get("differentiation", {}).get("approved_angle_id", "")
        if status == "ALLOWED":
            allowed_count += 1
            if not angle_id:
                errors.append("%s: ALLOWED with no approved_angle_id" % slug)
            elif angle_id not in by_id:
                errors.append("%s: names angle '%s' which does not exist" % (slug, angle_id))
            else:
                angle = by_id[angle_id]
                if angle.get("page") != slug:
                    errors.append("%s: angle '%s' belongs to page '%s'"
                                  % (slug, angle_id, angle.get("page")))
                if angle.get("status") not in bf.WRITABLE_ANGLE_STATUS:
                    errors.append(
                        "%s: angle '%s' has status '%s' — only %s authorise writing"
                        % (slug, angle_id, angle.get("status"),
                           " or ".join(sorted(bf.WRITABLE_ANGLE_STATUS))))
                if angle_id in claimed_angles:
                    errors.append("%s: angle '%s' is already used by '%s'"
                                  % (slug, angle_id, claimed_angles[angle_id]))
                claimed_angles[angle_id] = slug
                if rs.is_superficial_angle(
                        angle.get("statement", ""),
                        set(brief.get("entity_model", {}).get("local_entities", []))):
                    errors.append("%s: angle '%s' collapses when place names are masked"
                                  % (slug, angle_id))

        # ---- claim control ---------------------------------------------
        ledger = {}
        claims_path = ENGINE / "claims" / ("%s.json" % slug)
        if claims_path.is_file():
            ledger = {c["id"]: c for c in load(claims_path).get("claims", [])}

        claims = brief.get("claims", {})
        allowed_ids = set(claims.get("allowed_claim_ids", []))
        blocked_ids = set(claims.get("blocked_claim_ids", []))

        for cid in allowed_ids & blocked_ids:
            errors.append("%s: claim '%s' is both allowed and blocked" % (slug, cid))
        for cid in allowed_ids:
            if cid not in ledger:
                errors.append("%s: allows claim '%s' which is not in the ledger" % (slug, cid))
            elif not ledger[cid].get("publishable"):
                errors.append("%s: allows claim '%s' but the ledger marks it not publishable"
                              % (slug, cid))
            else:
                ok, reason = rs.claim_is_publishable(ledger[cid])
                if not ok:
                    errors.append("%s: allows claim '%s' but %s" % (slug, cid, reason))

        # ---- semantic completeness -------------------------------------
        if status == "ALLOWED":
            if not brief.get("query_model", {}).get("concept_groups"):
                errors.append("%s: empty query model" % slug)
            if not brief.get("entity_model", {}).get("entity_relationships"):
                errors.append("%s: no entity relationships — a keyword list is not an entity map" % slug)
            if not brief.get("sibling_constraints", {}).get("compared_against"):
                errors.append("%s: no sibling constraints — the writer would have no idea what to avoid" % slug)

            gain = brief.get("information_gain", {}).get("required_new_information", [])
            real = [g for g in gain
                    if g["evidence_level"] not in ("unknown_needs_verification",)
                    and not PLACE_ONLY_GAIN.match(g["information"])]
            if not real:
                errors.append("%s: no established information gain — place, ZIP and landmark "
                              "substitution never qualify" % slug)

            for item in gain:
                if item["evidence_level"] in rs.NOT_STATABLE and not item.get("claim_id"):
                    warnings.append("%s: information gain '%s' rests on a %s and cites no claim"
                                    % (slug, item["information"][:50], item["evidence_level"]))

            for rel_item in brief.get("entity_model", {}).get("entity_relationships", []):
                if rel_item["evidence_level"] in rs.NOT_STATABLE and rel_item.get("claim_id"):
                    errors.append("%s: relationship %s is a %s but cites a claim as if established"
                                  % (slug, " -> ".join(rel_item["chain"]), rel_item["evidence_level"]))

            groups = brief["query_model"]["concept_groups"]
            if groups and all(g.get("shared_with_siblings") for g in groups):
                warnings.append("%s: every concept group is shared with siblings — this page would "
                                "answer nothing its neighbours do not" % slug)

    print("Brief validation — %d brief(s), %d allowed" % (len(paths), allowed_count))
    for m in errors:
        print("  %sERROR%s  %s" % (RED, OFF, m))
    for m in warnings:
        print("  %sWARN %s  %s" % (YEL, OFF, m))

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
