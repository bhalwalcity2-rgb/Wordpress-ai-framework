#!/usr/bin/env python3
"""Every content file in a theme must be traceable to a promotion.

The publish gate is the sanctioned path into a theme's content directory, but
a gate only holds if nothing can walk around it. Nothing stops someone editing
wordpress/themes/*/content/*.json in an editor and committing it, and schema
validation would pass — the file would be well-formed, just unaccountable.

This closes that. A changed content file must be explained by either:

  * a promotion record whose destination_sha256 matches the committed bytes, or
  * an entry in the migration allowlist, for files whose current state predates
    the publish gate.

The allowlist exists because history is real: Phases 0-3A corrected NAP,
migrated the v1 schema fork and repaired rendering long before a gate existed.
Pretending those files came through it would be a lie; refusing to boot until
they are rewritten would be worse. Everything after that has no excuse.

Usage:
    python content-engine/bin/verify-content-provenance.py            # working tree
    python content-engine/bin/verify-content-provenance.py --against origin/main
"""

import argparse
import json
import subprocess
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(REPO_ROOT / "content-engine" / "lib"))

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(errors="replace")

import promotion as pm         # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
PROMOTIONS = ENGINE / "reports" / "promotions"
ALLOWLIST = ENGINE / "config" / "migration-allowlist.json"


def load(path, default=None):
    if not path.is_file():
        return default
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return default


def changed_files(against):
    """Content files changed against a ref, or all of them if no ref given."""
    if not against:
        return sorted(
            p.relative_to(REPO_ROOT).as_posix()
            for p in (REPO_ROOT / "wordpress" / "themes").rglob("content/**/*.json"))
    try:
        out = subprocess.run(["git", "diff", "--name-only", "%s...HEAD" % against],
                             cwd=REPO_ROOT, capture_output=True, text=True, check=True).stdout
    except (subprocess.CalledProcessError, FileNotFoundError) as exc:
        print("  could not diff against %s: %s" % (against, exc))
        return []
    return [line for line in out.splitlines()
            if "/content/" in line and line.endswith(".json") and line.startswith("wordpress/themes/")]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--against", help="git ref to diff against (e.g. origin/main)")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    colour = sys.stdout.isatty() and not args.no_color
    RED, YEL, GRN, OFF = (("\033[31m", "\033[33m", "\033[32m", "\033[0m") if colour else ("",) * 4)

    allow = set((load(ALLOWLIST, {}) or {}).get("files", []))

    promoted = {}
    if PROMOTIONS.is_dir():
        for record_path in PROMOTIONS.glob("*.json"):
            record = load(record_path, {})
            if record.get("destination") and record.get("destination_sha256"):
                promoted.setdefault(record["destination"], set()).add(record["destination_sha256"])

    files = changed_files(args.against)
    if not files:
        print("No theme content files to check.")
        return 0

    errors, allowed, verified = [], [], []
    for rel in files:
        path = REPO_ROOT / rel
        if not path.is_file():
            continue  # deleted; deletion is not a promotion concern
        digest = pm.sha256_file(path)

        if digest in promoted.get(rel, set()):
            verified.append(rel)
        elif rel in allow:
            allowed.append(rel)
        elif rel in promoted:
            errors.append("%s has a promotion record, but the committed bytes do not match it "
                          "(%s…). The file was edited after promotion." % (rel, digest[:12]))
        else:
            errors.append("%s has no promotion record and is not in the migration allowlist. "
                          "Content reaches a theme through content-engine/bin/publish-gate.py." % rel)

    print("Content provenance — %d file(s) checked%s"
          % (len(files), (" against %s" % args.against) if args.against else ""))
    for rel in verified:
        print("  %sok  %s promoted  %s" % (GRN, OFF, rel))
    for rel in allowed:
        print("  %sallow%s   pre-gate migration  %s" % (YEL, OFF, rel))
    for message in errors:
        print("  %sERROR%s   %s" % (RED, OFF, message))

    print("-" * 64)
    if errors:
        print("%sFAIL%s  %d file(s) without valid provenance" % (RED, OFF, len(errors)))
        return 1
    print("%sPASS%s  %d promoted, %d pre-gate migration" % (GRN, OFF, len(verified), len(allowed)))
    return 0


if __name__ == "__main__":
    sys.exit(main())
