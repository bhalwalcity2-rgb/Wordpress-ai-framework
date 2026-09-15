#!/usr/bin/env python3
"""The only sanctioned path from a draft into a theme's content directory.

Promotes an already-authored, validated, human-approved draft. It does NOT
generate content, and it does NOT deploy: promotion puts a validated artifact
into the repository, and the existing GitHub Actions / FTPS pipeline ships
whatever the repository contains. Those are different jobs and this script
does only the first.

WHY IT RE-RUNS EVERYTHING
-------------------------
A draft carries a status field, a brief carries a gate verdict, and both are
just files. Trusting them would mean trusting whoever edited them last, so
every prerequisite is re-checked here against the registries rather than read
back from an earlier run's conclusion.

APPROVAL IS NOT A QA RESULT
---------------------------
Passing every automated check does not authorise publication. A promotion
additionally requires an approval record naming the exact draft hash, so
approval attaches to one artifact rather than to a page in general — re-author
the draft and the approval stops applying.

Usage:
    python content-engine/bin/publish-gate.py <slug> [--theme T] [--update] [--dry-run]
    python content-engine/bin/publish-gate.py --check <slug>     # prerequisites only
"""

import argparse
import json
import subprocess
import sys
from datetime import date
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

import briefing as bf                 # noqa: E402
import promotion as pm                # noqa: E402
import research as rs                 # noqa: E402
import textstats as ts                # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
BIN = ENGINE / "bin"
DEFAULT_THEME = "kadence-child-lvjcb"

# Calibrated in Phase 3B; identical to validate-draft.py so a draft cannot
# pass one check and fail the other.
STRUCTURE_BLOCK = 0.85


def load(path, default=None):
    if not path.is_file():
        return default
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return default


def run_validator(script, *argv):
    out = subprocess.run([sys.executable, str(BIN / script), *argv, "--no-color"],
                         capture_output=True, text=True, encoding="utf-8", errors="replace")
    return out.returncode, (out.stdout or "") + (out.stderr or "")


def component_regressions(old, new):
    """What a promotion would remove from a page that is already live.

    Promotion writes the draft's content over the destination wholesale, so
    anything the live page had and the draft does not simply disappears. That
    is not hypothetical: promoting the Henderson pilot dropped four section
    photographs and the 'we also serve' block, because the draft was authored
    from evidence and nobody had carried the presentation across. The page got
    better copy and a worse page, and no check noticed.

    Section types and images only. Prose is meant to be replaced — that is what
    a rewrite is — so comparing it would block every legitimate update.
    """
    def inventory(content):
        sections = content.get("sections", [])
        return (
            [s.get("type", "content") for s in sections],
            sum(1 for s in sections if (s.get("image") or {}).get("slug")),
            bool(content.get("faq")),
        )

    old_types, old_images, old_faq = inventory(old)
    new_types, new_images, new_faq = inventory(new)

    lost = []
    for kind in set(old_types):
        before, after = old_types.count(kind), new_types.count(kind)
        if after < before:
            lost.append("%d fewer %r section(s): %d -> %d" % (before - after, kind, before, after))
    if new_images < old_images:
        lost.append("%d fewer section image(s): %d -> %d" % (old_images - new_images, old_images, new_images))
    if old_faq and not new_faq:
        lost.append("the FAQ block is gone")
    return lost


class Gate:
    def __init__(self):
        self.failures = []
        self.checks = []

    def require(self, label, ok, detail=""):
        self.checks.append({"check": label, "passed": bool(ok), "detail": detail})
        if not ok:
            self.failures.append("%s%s" % (label, (" — " + detail) if detail else ""))
        return ok

    @property
    def passed(self):
        return not self.failures


def evaluate(slug, theme):
    """Re-run every prerequisite. Returns (gate, context)."""
    gate = Gate()
    ctx = {"slug": slug, "theme": theme}

    theme_dir = REPO_ROOT / "wordpress" / "themes" / theme
    try:
        config = pm.read_business_config(theme_dir)
    except pm.PromotionError as exc:
        gate.require("theme readable", False, str(exc))
        return gate, ctx
    ctx["config"] = config

    # --- A. artifacts exist ------------------------------------------------
    paths = {
        "brief": ENGINE / "brief" / "production" / ("%s.json" % slug),
        "draft": ENGINE / "drafts" / ("%s.json" % slug),
        "angles": ENGINE / "angles" / ("%s.json" % slug),
        "claims": ENGINE / "claims" / ("%s.json" % slug),
        "place": ENGINE / "place" / ("%s.json" % slug),
        "approval": ENGINE / "approvals" / ("%s.json" % slug),
    }
    ctx["paths"] = paths

    if not gate.require("draft exists", paths["draft"].is_file(),
                        "no content-engine/drafts/%s.json" % slug):
        return gate, ctx
    if not gate.require("production brief exists", paths["brief"].is_file(),
                        "run create-content-brief.py"):
        return gate, ctx

    draft = load(paths["draft"], {})
    brief = load(paths["brief"], {})
    angles = load(paths["angles"], {"angles": []})
    ledger = {c["id"]: c for c in (load(paths["claims"], {}) or {}).get("claims", [])}
    _shared = load(ENGINE / "claims" / "_shared-business.json", {"claims": []})
    ledger.update({c["id"]: c for c in _shared.get("claims", [])})
    ctx.update(draft=draft, brief=brief)

    # --- B. brief + research + draft revalidated by their own validators ----
    for label, script in (("production brief validates", "validate-content-brief.py"),
                          ("draft validates", "validate-draft.py"),
                          ("research validates", "validate-research.py")):
        argv = (slug,) if script != "validate-research.py" else ()
        code, out = run_validator(script, *argv)
        tail = next((l.strip() for l in reversed(out.splitlines()) if l.strip()), "")
        gate.require(label, code == 0, tail)

    # --- C. page-existence gate --------------------------------------------
    brief_gate = brief.get("gate", {})
    gate.require("page-existence gate ALLOWED", brief_gate.get("status") == "ALLOWED",
                 brief_gate.get("blocked_reason", ""))

    # --- D. angle authority, re-derived ------------------------------------
    # A service-area page carries no angle by design (ADR-0009). It is not
    # exempt from anything else: identity, claims, coverage, structural
    # differentiation and destination safety all still apply below.
    angle_id = brief.get("differentiation", {}).get("approved_angle_id", "")
    service_area = brief.get("page_purpose_class") == "service_area_transactional"
    angle = next((a for a in angles.get("angles", []) if a["id"] == angle_id), None)

    if service_area:
        gate.require("service-area page declares no angle", not angle_id,
                     "angles are the informational control; this page should not claim one")
        gate.require("slug is a configured service area",
                     slug in config.get("location_slugs", []) and slug not in config.get("primary_slugs", []),
                     "not a non-primary configured service area")
        business = [c for c in ledger.values()
                    if c.get("publishable") and c.get("source_type") == "official_business"]
        gate.require("publishable business claims exist", bool(business),
                     "%d found" % len(business))
    else:
        gate.require("angle exists", angle is not None, "brief names %r" % angle_id)
    if angle and not service_area:
        gate.require("angle belongs to this page", angle.get("page") == slug,
                     "angle.page=%r" % angle.get("page"))
        gate.require("angle is approved", angle.get("status") in bf.WRITABLE_ANGLE_STATUS,
                     "status=%r" % angle.get("status"))
        gate.require("angle not superficial",
                     not rs.is_superficial_angle(angle.get("statement", ""),
                                                 set(brief.get("entity_model", {}).get("local_entities", []))),
                     "collapses under place-name masking")
        # No other page may hold it.
        others = []
        for other in sorted((ENGINE / "angles").glob("*.json")):
            if other.stem == slug:
                continue
            for a in (load(other, {}) or {}).get("angles", []):
                if a["id"] == angle_id or (
                        a.get("status") in bf.WRITABLE_ANGLE_STATUS
                        and a.get("statement", "").strip().lower()
                        == angle.get("statement", "").strip().lower()):
                    others.append(other.stem)
        gate.require("angle not claimed by another page", not others, ", ".join(others))
    ctx["angle_id"] = angle_id

    # --- E. claim provenance, re-derived from the ledger --------------------
    used = list(draft.get("generation", {}).get("claim_ids_used", []))
    allowed = set(brief.get("claims", {}).get("allowed_claim_ids", []))
    blocked = set(brief.get("claims", {}).get("blocked_claim_ids", []))
    ctx["claim_ids"] = used

    gate.require("no blocked claim used", not (set(used) & blocked),
                 ", ".join(sorted(set(used) & blocked)))
    gate.require("every used claim was allowed", set(used) <= allowed,
                 ", ".join(sorted(set(used) - allowed)))

    unpublishable, weak_high_risk = [], []
    for cid in used:
        claim = ledger.get(cid)
        if claim is None:
            unpublishable.append("%s (absent from ledger)" % cid)
            continue
        ok, reason = rs.claim_is_publishable(claim)
        if not ok:
            unpublishable.append("%s (%s)" % (cid, reason))
        if claim.get("risk") == "high" and claim.get("source_type") not in rs.AUTHORITATIVE_SOURCES:
            weak_high_risk.append(cid)
    gate.require("all used claims publishable", not unpublishable, "; ".join(unpublishable))
    gate.require("high-risk claims authoritatively sourced", not weak_high_risk,
                 ", ".join(weak_high_risk))

    # --- F. content, identity, coverage, differentiation --------------------
    content = draft.get("content") or {}
    gate.require("draft has authored content", bool(content),
                 "still an authoring package" if draft.get("status") == "authoring_package" else "")

    page_type = brief.get("page_type", "location")
    ctx["page_type"] = page_type

    gate.require("content slug matches", content.get("slug", slug) == slug,
                 "content.slug=%r" % content.get("slug"))
    gate.require("draft angle matches brief",
                 (draft.get("generation", {}).get("angle_id") or "") == (angle_id or ""),
                 "draft=%r brief=%r" % (draft.get("generation", {}).get("angle_id"), angle_id))

    for field in ("seo_title", "seo_description", "hero_heading"):
        if page_type != "home":
            gate.require("metadata: %s present" % field, bool(content.get(field)))

    identity = [config.get("business_name", ""), config.get("phone_display", ""),
                config.get("address", "")]
    blob = json.dumps(content, ensure_ascii=False)
    literals = [v for v in identity if v and v in blob]
    gate.require("business identity tokenised", not literals,
                 "literal(s) present: %s" % ", ".join(literals))

    must_answer = [g["concept"] for g in brief.get("query_model", {}).get("concept_groups", [])
                   if g.get("coverage") == "must_answer"]
    not_covered = set(draft.get("coverage", {}).get("concepts_not_covered", []))
    gate.require("must-answer concepts covered", not (set(must_answer) & not_covered),
                 ", ".join(sorted(set(must_answer) & not_covered)))
    gate.require("information gain declared",
                 bool(draft.get("coverage", {}).get("information_gain_delivered")),
                 "coverage.information_gain_delivered is empty")

    signature = ts.structure_signature(content.get("sections", []))
    twins = []
    for other in sorted((theme_dir / "content").rglob("*.json")):
        if other.stem == slug:
            continue
        sim = ts.sequence_similarity(signature, ts.structure_signature(
            (load(other, {}) or {}).get("sections", [])))
        if sim >= STRUCTURE_BLOCK:
            twins.append("%s (%.2f)" % (other.stem, sim))
    gate.require("structure differs from siblings", not twins, ", ".join(twins))

    links = [i.get("slug") for s in content.get("sections", []) if s.get("type") in ("areas", "services")
             for i in s.get("items", [])]
    known = set(config["location_slugs"]) | set(config["service_slugs"])
    gate.require("internal links resolve", all(l in known for l in links),
                 ", ".join(sorted(set(links) - known)))

    # --- G. destination safety ---------------------------------------------
    try:
        destination, rel = pm.resolve_destination(REPO_ROOT, theme, page_type, slug,
                                                  config["primary_slugs"])
        ctx["destination"], ctx["destination_rel"] = destination, rel
        gate.require("destination is safe and correct", True, rel)
    except pm.PromotionError as exc:
        gate.require("destination is safe and correct", False, str(exc))

    return gate, ctx


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slug")
    parser.add_argument("--theme", default=DEFAULT_THEME)
    parser.add_argument("--update", action="store_true",
                        help="permit overwriting an existing production file")
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--check", action="store_true", help="prerequisites only, never promote")
    parser.add_argument("--allow-regression", action="store_true",
                        help="promote even though the new content drops components the live page has")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    colour = sys.stdout.isatty() and not args.no_color
    RED, YEL, GRN, OFF = (("\033[31m", "\033[33m", "\033[32m", "\033[0m") if colour else ("",) * 4)

    try:
        pm.validate_slug(args.slug)
    except pm.PromotionError as exc:
        print("%sREFUSED%s  %s" % (RED, OFF, exc))
        return 1

    gate, ctx = evaluate(args.slug, args.theme)

    print("Publish gate — %s (theme: %s)" % (args.slug, args.theme))
    for check in gate.checks:
        mark = "%sok  %s" % (GRN, OFF) if check["passed"] else "%sFAIL%s" % (RED, OFF)
        # Detail is failure context. Printing it beside a passing check reads
        # as a contradiction ("ok draft exists  no draft").
        detail = ("  %s" % check["detail"]) if (check["detail"] and not check["passed"]) else ""
        print("  %s %s%s" % (mark, check["check"], detail))

    if not gate.passed:
        print("-" * 64)
        print("%sBLOCKED%s  %d prerequisite(s) failed. The draft is untouched."
              % (RED, OFF, len(gate.failures)))
        return 1

    # --- approval: deliberate, and tied to this exact draft ----------------
    draft_hash = pm.sha256_file(ctx["paths"]["draft"])
    approval = load(ctx["paths"]["approval"])
    ok, reason = pm.approval_is_valid(approval, args.slug, draft_hash)
    if not ok:
        print("-" * 64)
        print("%sNOT APPROVED%s  %s" % (YEL, OFF, reason))
        print("Every automated check passed. That is not permission to publish.")
        print("Create content-engine/approvals/%s.json:" % args.slug)
        print(json.dumps({"slug": args.slug, "decision": "approved",
                          "approved_by": "<name>", "approved_on": date.today().isoformat(),
                          "draft_sha256": draft_hash, "note": "<why>"}, indent=2))
        return 1

    destination = ctx["destination"]
    exists = destination.is_file()

    # Computed early so --check can report it while the draft is still being
    # worked on, rather than springing it at the moment of promotion.
    lost = component_regressions(load(destination, {}), ctx["draft"]["content"]) if exists else []
    if lost:
        print("%s%s%s  promoting this draft would remove from the live page:"
              % (YEL if args.allow_regression else RED,
                 "ALLOWED REGRESSION" if args.allow_regression else "REGRESSION", OFF))
        for item in lost:
            print("    - %s" % item)

    if args.check:
        print("-" * 64)
        if lost and not args.allow_regression:
            print("%sBLOCKED%s  refusing to silently downgrade %s" % (RED, OFF, ctx["destination_rel"]))
            return 1
        print("%sREADY%s  prerequisites and approval satisfied (--check: nothing promoted)" % (GRN, OFF))
        return 0

    # Ordering matters: "you did not ask to replace this file" is a more
    # fundamental objection than "your replacement drops things", and giving
    # the regression message first would send someone looking for missing
    # sections when the real answer is that they forgot --update.
    if exists and not args.update:
        print("-" * 64)
        print("%sBLOCKED%s  %s already exists. Pass --update to replace it deliberately."
              % (RED, OFF, ctx["destination_rel"]))
        return 1

    if lost and not args.allow_regression:
        print("  Add them to the draft, or pass --allow-regression if the loss is intended.")
        print("-" * 64)
        print("%sBLOCKED%s  refusing to silently downgrade %s" % (RED, OFF, ctx["destination_rel"]))
        return 1

    mode = "update" if exists else "new"
    previous_hash = pm.sha256_file(destination) if exists else None
    payload = json.dumps(ctx["draft"]["content"], indent="\t", ensure_ascii=False) + "\n"

    if args.dry_run:
        print("-" * 64)
        print("%sDRY RUN%s  would %s %s" % (YEL, OFF, mode, ctx["destination_rel"]))
        return 0

    destination.parent.mkdir(parents=True, exist_ok=True)
    destination.write_text(payload, encoding="utf-8", newline="")

    record = {
        "slug": args.slug,
        "theme": args.theme,
        "page_type": ctx["page_type"],
        "mode": mode,
        "promoted_at": date.today().isoformat(),
        "brief_version": ctx["brief"].get("brief_version"),
        "brief_path": ctx["paths"]["brief"].relative_to(REPO_ROOT).as_posix(),
        "draft_path": ctx["paths"]["draft"].relative_to(REPO_ROOT).as_posix(),
        "draft_sha256": draft_hash,
        "angle_id": ctx["angle_id"],
        "claim_ids": ctx["claim_ids"],
        "source_artifacts": ctx["draft"].get("generation", {}).get("source_artifacts", {}),
        "approval": {k: approval.get(k) for k in ("approved_by", "approved_on", "note")},
        "components_removed": lost,
        "qa_result": "all prerequisites re-verified at promotion time",
        "qa_checks": gate.checks,
        "destination": ctx["destination_rel"],
        "destination_sha256": pm.sha256_file(destination),
        # Canonical-JSON hash of the content itself. The raw hash above is
        # line-ending sensitive, so it changes whenever git checks the file
        # out under core.autocrlf - which makes it useless as a provenance
        # check on a Windows working copy. This one is stable across
        # platforms and formatting, and is what verification compares.
        "destination_content_sha256": pm.sha256_bytes(
            pm.canonical_json_bytes(json.loads(destination.read_text(encoding="utf-8")))),
        "previous_destination_sha256": previous_hash,
        "_note": ("Hashes identify what was promoted; they are not a substitute for the QA above. "
                  "The source draft is deliberately left unmodified and undeleted."),
    }
    reports = ENGINE / "reports" / "promotions"
    reports.mkdir(parents=True, exist_ok=True)
    (reports / ("%s-%s.json" % (args.slug, record["promoted_at"]))).write_text(
        json.dumps(record, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")
    (reports / ("%s.latest.json" % args.slug)).write_text(
        json.dumps(record, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")

    print("-" * 64)
    print("%sPROMOTED%s  %s (%s)" % (GRN, OFF, ctx["destination_rel"], mode))
    print("  record: content-engine/reports/promotions/%s.latest.json" % args.slug)
    print("  draft left untouched. Deployment remains the existing FTPS pipeline's job.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
