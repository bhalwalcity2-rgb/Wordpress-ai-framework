#!/usr/bin/env python3
"""Validate authored drafts before they can be considered for promotion.

Checks the draft wrapper, the canonical v2 content inside it, and the things
only a draft can get wrong — claims it was not permitted to use, coverage it
asserts but did not deliver, and structure copied from a sibling.

  ERROR  draft outside content-engine/drafts/
  ERROR  content fails the canonical v2 contract
  ERROR  literal business name, phone or address instead of {business}/{phone}
  ERROR  uses a claim id the brief did not allow, or one that is blocked
  ERROR  uses a claim that is not publishable in the ledger
  ERROR  angle_id disagrees with the brief, or is no longer approved
  ERROR  a must_answer concept is listed as not covered
  ERROR  repeats a question the brief marked as answered by a sibling
  ERROR  section structure identical to a sibling's
  ERROR  brief_path or source_artifacts point at missing files
  WARN   draft still an empty authoring package
  WARN   high sibling similarity below the blocking threshold
  WARN   self_check incomplete

Usage:
    python content-engine/bin/validate-draft.py [slug] [--strict]
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

import research as rs                                  # noqa: E402
import textstats as ts                                 # noqa: E402
from jsonschema_mini import ERROR, SchemaValidator     # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
DRAFTS = ENGINE / "drafts"
THEME = REPO_ROOT / "wordpress" / "themes" / "kadence-child-lvjcb"

CONTENT_SCHEMA = {"location": "location.schema.json",
                  "service": "service.schema.json",
                  "home": "home.schema.json"}

PHONE_RE = re.compile(r"\(?\d{3}\)?[\s.\-]\d{3}-\d{4}")

# Calibrated in Phase 3B. Used as an editorial signal, not a blind rule: a
# draft that trips the blocking level is structurally a sibling's twin.
STRUCTURE_BLOCK = 0.85
STRUCTURE_WARN = 0.70


def load(path):
    return json.loads(path.read_text(encoding="utf-8"))


def walk_strings(value, path=""):
    if isinstance(value, str):
        yield path or "(root)", value
    elif isinstance(value, dict):
        for k, v in value.items():
            yield from walk_strings(v, "%s.%s" % (path, k) if path else k)
    elif isinstance(value, list):
        for i, v in enumerate(value):
            yield from walk_strings(v, "%s[%d]" % (path, i))


def business_identity():
    src = (THEME / "inc" / "business-config.php").read_text(encoding="utf-8")
    out = {}
    for key in ("business_name", "phone_display", "address"):
        m = re.search(r"'%s'\s*=>\s*'([^']*)'" % key, src)
        if m:
            out[key] = m.group(1)
    return out


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slug", nargs="?")
    parser.add_argument("--strict", action="store_true")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    colour = sys.stdout.isatty() and not args.no_color
    RED, YEL, GRN, OFF = (("\033[31m", "\033[33m", "\033[32m", "\033[0m") if colour else ("",) * 4)

    if not DRAFTS.is_dir():
        print("No drafts directory — nothing to validate.")
        return 0

    paths = ([DRAFTS / ("%s.json" % args.slug)] if args.slug else sorted(DRAFTS.glob("*.json")))
    if not paths or not any(p.is_file() for p in paths):
        print("No drafts found.")
        return 0

    validator = SchemaValidator(ENGINE / "config" / "schema")
    identity = business_identity()
    errors, warnings = [], []
    checked = 0

    for path in paths:
        if not path.is_file():
            errors.append("%s: not found" % path.name)
            continue
        slug = path.stem
        checked += 1

        if "drafts" not in path.parts or "wordpress" in path.parts:
            errors.append("%s: drafts may only live in content-engine/drafts/" % slug)

        try:
            draft = load(path)
        except json.JSONDecodeError as exc:
            errors.append("%s: malformed JSON — %s" % (slug, exc))
            continue

        for issue in validator.validate(draft, "draft.schema.json"):
            (errors if issue.level == ERROR else warnings).append("drafts/%s %s" % (path.name, issue))

        gen = draft.get("generation", {})
        content = draft.get("content") or {}

        if draft.get("status") == "authoring_package" or not content:
            warnings.append("%s: authoring package not yet written — `content` is empty" % slug)
            continue

        # ---- brief linkage ---------------------------------------------
        brief_path = REPO_ROOT / gen.get("brief_path", "")
        if not brief_path.is_file():
            errors.append("%s: brief_path points at missing %s" % (slug, gen.get("brief_path")))
            continue
        brief = load(brief_path)

        for name, ref in gen.get("source_artifacts", {}).items():
            if not (REPO_ROOT / ref).is_file():
                errors.append("%s: source_artifacts.%s missing (%s)" % (slug, name, ref))

        # ---- canonical v2 content --------------------------------------
        page_type = brief.get("page_type", "location")
        for issue in validator.validate(content, CONTENT_SCHEMA.get(page_type, "location.schema.json")):
            (errors if issue.level == ERROR else warnings).append("drafts/%s content %s" % (path.name, issue))

        # ---- business identity must stay tokenised ----------------------
        for where, text in walk_strings(content):
            if identity.get("business_name") and identity["business_name"] in text:
                errors.append("%s: %s writes the business name literally — use {business}" % (slug, where))
            if PHONE_RE.search(text):
                errors.append("%s: %s writes a literal phone number — use {phone}" % (slug, where))
            if identity.get("address") and identity["address"] in text:
                errors.append("%s: %s writes the address literally" % (slug, where))

        # ---- claim control ---------------------------------------------
        allowed = set(brief.get("claims", {}).get("allowed_claim_ids", []))
        blocked = set(brief.get("claims", {}).get("blocked_claim_ids", []))
        ledger = {}
        for cp in (ENGINE / "claims" / ("%s.json" % slug),
                   ENGINE / "claims" / "_shared-business.json"):
            if cp.is_file():
                ledger.update({c["id"]: c for c in load(cp).get("claims", [])})

        used = set(gen.get("claim_ids_used", []))
        for cid in used - allowed:
            errors.append("%s: uses claim '%s' which the brief did not allow" % (slug, cid))
        for cid in used & blocked:
            errors.append("%s: uses blocked claim '%s'" % (slug, cid))
        for cid in used:
            claim = ledger.get(cid)
            if claim is None:
                errors.append("%s: uses claim '%s' absent from the ledger" % (slug, cid))
                continue
            ok, reason = rs.claim_is_publishable(claim)
            if not ok:
                errors.append("%s: uses claim '%s' but %s" % (slug, cid, reason))

        # ---- angle authority -------------------------------------------
        # A service-area page carries no angle by design (ADR-0009); every
        # other check below still applies to it.
        brief_angle = brief.get("differentiation", {}).get("approved_angle_id")
        service_area = brief.get("page_purpose_class") == "service_area_transactional"

        if (gen.get("angle_id") or "") != (brief_angle or ""):
            errors.append("%s: draft angle '%s' disagrees with brief angle '%s'"
                          % (slug, gen.get("angle_id"), brief_angle))
        if service_area and gen.get("angle_id"):
            errors.append("%s: service-area draft names an angle (%r); angles are the "
                          "informational control" % (slug, gen.get("angle_id")))

        angles_path = ENGINE / "angles" / ("%s.json" % slug)
        if angles_path.is_file() and not service_area:
            angle = next((a for a in load(angles_path).get("angles", [])
                          if a["id"] == gen.get("angle_id")), None)
            if angle is None:
                errors.append("%s: angle '%s' no longer exists" % (slug, gen.get("angle_id")))
            elif angle.get("status") not in ("approved", "used"):
                errors.append("%s: angle '%s' is '%s' — no longer authorises this draft"
                              % (slug, gen.get("angle_id"), angle.get("status")))

        # ---- coverage --------------------------------------------------
        coverage = draft.get("coverage", {})
        not_covered = set(coverage.get("concepts_not_covered", []))
        for group in brief.get("query_model", {}).get("concept_groups", []):
            if group.get("coverage") == "must_answer" and group["concept"] in not_covered:
                errors.append("%s: must_answer concept '%s' declared not covered" % (slug, group["concept"]))

        forbidden_q = {q.lower() for q in brief.get("faq_plan", {}).get("questions_not_to_repeat", [])}
        for item in content.get("faq", []):
            if item.get("question", "").lower() in forbidden_q:
                errors.append("%s: repeats a question the brief marked as answered elsewhere: %r"
                              % (slug, item["question"][:60]))

        if not coverage.get("self_check"):
            warnings.append("%s: no self_check recorded" % slug)

        # ---- structural differentiation --------------------------------
        signature = ts.structure_signature(content.get("sections", []))
        for other in sorted((THEME / "content").rglob("*.json")):
            if other.stem == slug:
                continue
            sibling = load(other)
            similarity = ts.sequence_similarity(signature, ts.structure_signature(sibling.get("sections", [])))
            if similarity >= STRUCTURE_BLOCK:
                errors.append("%s: section structure %.2f identical to %s — derive structure from "
                              "this page's own purpose" % (slug, similarity, other.stem))
            elif similarity >= STRUCTURE_WARN:
                warnings.append("%s: structure %.2f close to %s" % (slug, similarity, other.stem))

    print("Draft validation — %d draft(s)" % checked)
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
