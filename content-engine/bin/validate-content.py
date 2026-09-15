#!/usr/bin/env python3
"""Validate a theme's content files against the canonical v2 contract.

Checks, in order of severity:

  ERROR    malformed JSON                  (previously silent — the page
                                            just rendered the generic
                                            templated layout instead)
  ERROR    schema violation                (missing required field,
                                            unsupported section type,
                                            superseded v1 'body' key)
  ERROR    filename does not match slug
  ERROR    literal business name, phone, or address in content
  WARNING  deprecated key still present
  WARNING  seo_title over 60 / seo_description over 160 characters
  INFO     configured pages with no content file

Business identity must appear as the {business} and {phone} tokens, never
as a literal — business-config.php is the single source of truth, and
literals in content are what put a phone number that was not the
business's onto eleven live pages.

Usage:
    python content-engine/bin/validate-content.py [theme-slug]
    python content-engine/bin/validate-content.py --strict     # warnings fail too

Exit status: 0 clean, 1 errors found (2 with --strict and warnings).
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

from jsonschema_mini import ERROR, WARNING, SchemaValidator  # noqa: E402

SCHEMA_DIR = REPO_ROOT / "content-engine" / "config" / "schema"
DEFAULT_THEME = "kadence-child-lvjcb"

SCHEMA_FOR = {
    "locations": "location.schema.json",
    "services": "service.schema.json",
    # Additive homepage content. Absent on most sites, which is why the
    # loop below skips a directory that does not exist.
    "pages": "home.schema.json",
}

# SEO_STANDARDS.md §3. Warnings, not errors: length is a copy problem, and
# failing the build on it would block the contract freeze on an editorial
# rewrite that belongs to a later phase.
MAX_TITLE = 60
MAX_DESCRIPTION = 160

PHONE_RE = re.compile(r"\(?\d{3}\)?[\s.\-]\d{3}-\d{4}")

# Values known to have reached production before the tokens existed. Named
# explicitly so a reintroduction is reported by name rather than as a
# generic "looks like a phone number".
LEGACY_BRAND = "Las Vegas Junk Car Buyers"
LEGACY_PHONE = "(702) 555-0134"


class Colors:
    def __init__(self, enabled):
        self.err = "\033[31m" if enabled else ""
        self.warn = "\033[33m" if enabled else ""
        self.ok = "\033[32m" if enabled else ""
        self.dim = "\033[2m" if enabled else ""
        self.off = "\033[0m" if enabled else ""


def read_business_config(theme_dir):
    """Pull the identity values out of business-config.php.

    Regex rather than a PHP parse: the three values needed are simple
    single-quoted scalars, and shelling out to PHP would make this script
    unrunnable on a machine without a PHP binary (this one).
    """
    path = theme_dir / "inc" / "business-config.php"
    if not path.is_file():
        return {}

    source = path.read_text(encoding="utf-8")
    config = {}
    for key in ("business_name", "phone_display", "address"):
        match = re.search(r"'%s'\s*=>\s*'([^']*)'" % key, source)
        if match:
            config[key] = match.group(1)

    # Service areas, split by whether the homepage already targets them.
    # The primary city has no location page of its own: lvjcb_get_location_url()
    # resolves it to '/', lvjcb_get_service_area_cards() omits it from the grid,
    # and inc/cli.php skips provisioning one. A content file for that slug can
    # therefore never render.
    config["location_slugs"] = []
    config["primary_slugs"] = []
    for line in source.splitlines():
        match = re.search(
            r"'city'\s*=>\s*'[^']*',\s*'state'\s*=>\s*'[^']*',\s*'slug'\s*=>\s*'([^']+)'",
            line,
        )
        if not match:
            continue
        slug = match.group(1)
        config["location_slugs"].append(slug)
        if re.search(r"'is_primary'\s*=>\s*true", line):
            config["primary_slugs"].append(slug)

    services_block = re.search(r"'services'\s*=>\s*array\((.*?)\n\t\),", source, re.S)
    config["service_slugs"] = (
        re.findall(r"'slug'\s*=>\s*'([^']+)'", services_block.group(1))
        if services_block
        else []
    )

    # Icon names resolve to <use href="#icon-name"> against the sprite in
    # inc/icons.php. lvjcb_icon() builds that reference from whatever string it
    # is given, so an unknown name emits valid markup pointing at nothing and
    # the card renders with an invisible gap where its icon should be.
    icons = theme_dir / "inc" / "icons.php"
    try:
        config["icon_names"] = re.findall(r'id="icon-([a-z0-9-]+)"',
                                          icons.read_text(encoding="utf-8"))
    except OSError:
        config["icon_names"] = []

    # Image slugs come from the manifest rather than the theme: that file is
    # what CI feeds to the Pexels fetcher, so a slug absent from it is never
    # downloaded, never attached, and renders as nothing at all.
    manifest = REPO_ROOT / "scripts" / "image-manifest.json"
    try:
        config["image_slugs"] = [
            entry.get("slug")
            for entry in json.loads(manifest.read_text(encoding="utf-8")).get("images", [])
        ]
    except (OSError, ValueError):
        config["image_slugs"] = []

    return config


def walk_strings(value, path=""):
    """Yield (path, string) for every string in a decoded document."""
    if isinstance(value, str):
        yield path or "(root)", value
    elif isinstance(value, dict):
        for key, item in value.items():
            yield from walk_strings(item, f"{path}.{key}" if path else key)
    elif isinstance(value, list):
        for index, item in enumerate(value):
            yield from walk_strings(item, f"{path}[{index}]")


def check_nap(data, config):
    """No literal business identity anywhere in a content file."""
    findings = []
    business = config.get("business_name", "")
    address = config.get("address", "")

    for path, text in walk_strings(data):
        if LEGACY_BRAND in text:
            findings.append(
                (path, f"contains the superseded business name {LEGACY_BRAND!r} — use {{business}}")
            )
        elif business and business in text:
            findings.append(
                (path, f"contains the business name as a literal — use {{business}}")
            )

        if LEGACY_PHONE in text:
            findings.append(
                (path, f"contains the placeholder phone {LEGACY_PHONE!r} — use {{phone}}")
            )
        else:
            match = PHONE_RE.search(text)
            if match:
                findings.append(
                    (path, f"contains a literal phone number {match.group(0)!r} — use {{phone}}")
                )

        if address and address in text:
            findings.append((path, "contains the business address as a literal"))

    return findings


def check_slug_references(data, config):
    """Every slug a content file names must exist in business-config.php.

    written-page.php resolves 'areas' and 'services' entries, and
    service-linked table cells, by slug and skips anything it cannot find.
    That is the right runtime behaviour — a retired service should not
    fatal a live page — but it means a typo silently drops a card or a
    link. This is where that becomes visible.
    """
    findings = []
    known = {
        "areas": set(config.get("location_slugs", [])),
        "services": set(config.get("service_slugs", [])),
    }
    label = {"areas": "service-area", "services": "service"}

    for index, section in enumerate(data.get("sections", [])):
        kind = section.get("type")

        if kind in known:
            for position, entry in enumerate(section.get("items", [])):
                slug = entry.get("slug")
                if slug and slug not in known[kind]:
                    findings.append((
                        "sections[%d].items[%d].slug" % (index, position),
                        "%r is not a %s slug in business-config.php — the renderer "
                        "would skip it and the card would silently vanish" % (slug, label[kind]),
                    ))

        for field in ("cards", "steps"):
            for position, entry in enumerate(section.get(field, [])):
                icon = entry.get("icon")
                if icon and config.get("icon_names") and icon not in config["icon_names"]:
                    findings.append((
                        "sections[%d].%s[%d].icon" % (index, field, position),
                        "%r is not in the icon sprite — it would render as an invisible "
                        "gap where the icon should be" % icon,
                    ))

        # An image slug missing from the manifest fails exactly the way a bad
        # card slug does — silently. rich-content.php resolves it to 0 and
        # draws the section with no picture, so the page looks merely plain
        # rather than broken, and nobody goes looking.
        image_slug = (section.get("image") or {}).get("slug")
        if image_slug and config.get("image_slugs") and image_slug not in config["image_slugs"]:
            findings.append((
                "sections[%d].image.slug" % index,
                "%r is not in scripts/image-manifest.json — CI would never fetch it "
                "and the section would render with no image" % image_slug,
            ))

        # Table cells may link to a service: {"text": "...", "service": "slug"}.
        for row_index, row in enumerate((section.get("table") or {}).get("rows", [])):
            for cell_index, cell in enumerate(row):
                if not isinstance(cell, dict):
                    continue
                slug = cell.get("service")
                if slug and slug not in known["services"]:
                    findings.append((
                        "sections[%d].table.rows[%d][%d].service" % (index, row_index, cell_index),
                        "%r is not a service slug in business-config.php — the link "
                        "would resolve to a page that does not exist" % slug,
                    ))

    return findings


def resolve_tokens(text, config):
    """Mirror lvjcb_resolve_content_tokens() for length checks."""
    return text.replace("{business}", config.get("business_name", "")).replace(
        "{phone}", config.get("phone_display", "")
    )


def check_lengths(data, config):
    findings = []
    title = resolve_tokens(data.get("seo_title", ""), config)
    description = resolve_tokens(data.get("seo_description", ""), config)

    if len(title) > MAX_TITLE:
        findings.append(
            ("seo_title", f"{len(title)} characters after tokens resolve, over the {MAX_TITLE} in SEO_STANDARDS.md §3")
        )
    if len(description) > MAX_DESCRIPTION:
        findings.append(
            ("seo_description", f"{len(description)} characters after tokens resolve, over {MAX_DESCRIPTION}")
        )
    return findings


def validate_file(path, kind, validator, config):
    """Returns (errors, warnings) as lists of 'path: message' strings."""
    errors, warnings = [], []

    raw = path.read_text(encoding="utf-8")
    try:
        data = json.loads(raw)
    except json.JSONDecodeError as exc:
        return ([f"(file): malformed JSON — {exc}"], [])

    for issue in validator.validate(data, SCHEMA_FOR[kind]):
        (errors if issue.level == ERROR else warnings).append(str(issue))

    if isinstance(data, dict):
        slug = data.get("slug")
        if slug and slug != path.stem:
            errors.append(f"slug: {slug!r} does not match filename {path.stem!r}")

        for where, message in check_nap(data, config):
            errors.append(f"{where}: {message}")
        for where, message in check_slug_references(data, config):
            errors.append(f"{where}: {message}")
        for where, message in check_lengths(data, config):
            warnings.append(f"{where}: {message}")

    return errors, warnings


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("theme", nargs="?", default=DEFAULT_THEME)
    parser.add_argument("--strict", action="store_true", help="treat warnings as failure")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    c = Colors(sys.stdout.isatty() and not args.no_color)

    theme_dir = REPO_ROOT / "wordpress" / "themes" / args.theme
    content_dir = theme_dir / "content"
    if not content_dir.is_dir():
        print(f"{c.err}No content directory: {content_dir}{c.off}")
        return 1

    config = read_business_config(theme_dir)
    if not config.get("business_name"):
        print(f"{c.err}Could not read business_name from business-config.php — NAP checks cannot run{c.off}")
        return 1

    validator = SchemaValidator(SCHEMA_DIR)

    print(f"Validating {args.theme} against the canonical v2 content contract")
    print(f"{c.dim}business: {config['business_name']} · phone: {config.get('phone_display', '?')}{c.off}\n")

    total_errors = total_warnings = 0
    checked = 0

    for kind in ("locations", "services", "pages"):
        directory = content_dir / kind
        if not directory.is_dir():
            continue

        print(f"{kind.upper()}")
        for path in sorted(directory.glob("*.json")):
            checked += 1
            errors, warnings = validate_file(path, kind, validator, config)

            if kind == "locations" and path.stem in config.get("primary_slugs", []):
                warnings.append(
                    "(file): %s is the primary city, which the homepage targets, so it has "
                    "no location page — this content can never render. Fold it into the "
                    "homepage or clear is_primary in business-config.php." % path.stem
                )

            total_errors += len(errors)
            total_warnings += len(warnings)

            if errors:
                print(f"  {c.err}FAIL{c.off}  {path.name}")
            elif warnings:
                print(f"  {c.warn}WARN{c.off}  {path.name}")
            else:
                print(f"  {c.ok}ok{c.off}    {path.name}")

            for message in errors:
                print(f"          {c.err}error{c.off}  {message}")
            for message in warnings:
                print(f"          {c.warn}warn {c.off}  {message}")
        print()

    # Coverage. A configured page with no content file renders the generic
    # templated layout — legitimate during migration, but it should be
    # visible rather than assumed.
    missing = []
    for slug in config.get("location_slugs", []):
        if slug in config.get("primary_slugs", []):
            continue  # No page exists for the primary city by design.
        if not (content_dir / "locations" / f"{slug}.json").is_file():
            missing.append(f"locations/{slug}")
    for slug in config.get("service_slugs", []):
        if not (content_dir / "services" / f"{slug}.json").is_file():
            missing.append(f"services/{slug}")

    if missing:
        print(f"{c.dim}No content file (renders the templated fallback): {', '.join(missing)}{c.off}\n")

    print("-" * 64)
    summary = f"{checked} file(s) checked · {total_errors} error(s) · {total_warnings} warning(s)"
    if total_errors:
        print(f"{c.err}FAIL{c.off}  {summary}")
        return 1
    if total_warnings and args.strict:
        print(f"{c.warn}FAIL (strict){c.off}  {summary}")
        return 2
    print(f"{c.ok}PASS{c.off}  {summary}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
