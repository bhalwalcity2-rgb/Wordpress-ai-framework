"""Destination resolution, path safety, and live-URL rules for promotion.

Two things live here because getting either wrong is unrecoverable.

Destination safety: a CLI argument must never be able to make the gate write
outside a theme's content directory. Every path is resolved and then checked
to be inside the intended root — checked after resolution, because ".." and
symlinks are only visible once resolved.

URL rules: these mirror the PHP the site actually uses
(lvjcb_get_location_url / lvjcb_get_service_url) rather than restating a
convention. The primary city is the case that matters — it has no location
page, so asking for its location URL is a bug, not a lookup.
"""

import hashlib
import re

SLUG_RE = re.compile(r"^[a-z0-9]+(-[a-z0-9]+)*$")

PAGE_CLASSES = ("home", "location", "service")

# Mirrors inc/helpers.php. A change here without a change there silently
# promotes content to a path the renderer does not read.
CONTENT_DIR_FOR = {"home": "pages", "location": "locations", "service": "services"}
URL_PREFIX_FOR = {"location": "/service-areas/", "service": "/cash-for-junk-cars/"}


class PromotionError(Exception):
    """Refusal. Carries a reason a person can act on."""


def sha256_bytes(data):
    return hashlib.sha256(data).hexdigest()


def sha256_file(path):
    return sha256_bytes(path.read_bytes())


def canonical_json_bytes(value):
    """Stable bytes for hashing content independently of key order."""
    import json
    return json.dumps(value, sort_keys=True, ensure_ascii=False, separators=(",", ":")).encode("utf-8")


def validate_slug(slug):
    if not slug or not SLUG_RE.match(slug):
        raise PromotionError(
            "invalid slug %r — must be lowercase, hyphen-separated, no path characters" % slug)
    return slug


def resolve_destination(repo_root, theme, page_type, slug, primary_slugs):
    """Where this page's content belongs, or a refusal explaining why not.

    Returns (path, relative_posix). Raises PromotionError on anything unsafe
    or architecturally forbidden.
    """
    validate_slug(slug)
    validate_slug(theme)

    if page_type not in PAGE_CLASSES:
        raise PromotionError("unknown page_type %r — expected one of %s"
                             % (page_type, ", ".join(PAGE_CLASSES)))

    if page_type == "location" and slug in primary_slugs:
        raise PromotionError(
            "%r is the primary city. The homepage owns that intent and no location page exists "
            "for it (ADR-0004/ADR-0005). Promote homepage content as page_type 'home' instead."
            % slug)

    if page_type == "home" and slug != "home":
        raise PromotionError("homepage content must use slug 'home', got %r" % slug)

    theme_root = (repo_root / "wordpress" / "themes" / theme).resolve()
    if not theme_root.is_dir():
        raise PromotionError("theme %r does not exist" % theme)

    content_root = (theme_root / "content").resolve()
    destination = (content_root / CONTENT_DIR_FOR[page_type] / ("%s.json" % slug)).resolve()

    # Resolved-path containment. Checked after resolution so '..' and symlinks
    # cannot smuggle a write outside the content directory.
    if content_root not in destination.parents:
        raise PromotionError("destination %s escapes %s" % (destination, content_root))
    if destination.suffix != ".json":
        raise PromotionError("destination must be a .json file, got %s" % destination.name)

    return destination, destination.relative_to(repo_root).as_posix()


def live_url(base_url, page_type, slug, primary_slugs):
    """Public URL for a promoted page.

    The primary city resolves to the homepage, matching
    lvjcb_get_location_url(). Verifying /service-areas/<primary>/ would assert
    a 404 the architecture deliberately produces.
    """
    base = base_url.rstrip("/")
    if page_type == "home" or (page_type == "location" and slug in primary_slugs):
        return base + "/"
    return base + URL_PREFIX_FOR[page_type] + slug + "/"


def read_business_config(theme_dir):
    """Identity and service-area facts the gate and verifier both need."""
    path = theme_dir / "inc" / "business-config.php"
    if not path.is_file():
        raise PromotionError("no business-config.php under %s" % theme_dir)

    source = path.read_text(encoding="utf-8")
    config = {"primary_slugs": [], "location_slugs": [], "service_slugs": []}

    for key in ("business_name", "phone_display", "address", "email"):
        match = re.search(r"'%s'\s*=>\s*'([^']*)'" % key, source)
        if match:
            config[key] = match.group(1)

    for line in source.splitlines():
        match = re.search(
            r"'city'\s*=>\s*'([^']*)',\s*'state'\s*=>\s*'[^']*',\s*'slug'\s*=>\s*'([^']+)'", line)
        if not match:
            continue
        config["location_slugs"].append(match.group(2))
        if re.search(r"'is_primary'\s*=>\s*true", line):
            config["primary_slugs"].append(match.group(2))

    block = re.search(r"'services'\s*=>\s*array\((.*?)\n\t\),", source, re.S)
    config["service_slugs"] = re.findall(r"'slug'\s*=>\s*'([^']+)'", block.group(1)) if block else []
    return config


def approval_is_valid(approval, slug, draft_hash):
    """Whether an approval authorises THIS draft.

    Approval is of a specific artifact, not a standing permission. Re-author
    the draft and the hash changes, so the approval no longer applies and a
    person has to look again. That is the point: automated QA passing is not
    the same as someone deciding to publish.
    """
    if not approval:
        return False, "no approval record"
    if approval.get("slug") != slug:
        return False, "approval is for %r" % approval.get("slug")
    if approval.get("decision") != "approved":
        return False, "decision is %r" % approval.get("decision")
    approved_hash = approval.get("draft_sha256")
    if not approved_hash:
        return False, "approval names no draft_sha256"
    if approved_hash != draft_hash:
        return False, ("approval is for draft %s… but the draft is now %s… — it changed after "
                       "approval and must be re-approved" % (approved_hash[:12], draft_hash[:12]))
    if not approval.get("approved_by"):
        return False, "approval names no approver"
    return True, "ok"
