#!/usr/bin/env python3
"""Compare a promoted content artifact against the rendered live page.

Runs after deployment. Promotion puts an artifact in the repository; this
answers the different question of whether the page a visitor actually gets
matches it.

Semantic, not byte-for-byte. HTML changes for reasons that have nothing to do
with content — plugin markup, cache comments, asset versions — so comparing
markup would produce noise instead of signal. What is compared is what the
artifact actually promises: title, description, canonical, headings, the
presence of the prose, word count, sections, FAQ, internal links, schema
signals, and resolved identity tokens.

Two failure modes get special attention because both are silent in the
browser: content-loader fallback (a malformed file renders the generic
templated layout) and empty heading sections (a heading with nothing under
it, which is how the v1 schema fork presented for months).

Standard library only — urllib and html.parser.

Usage:
    python content-engine/bin/verify-live.py <slug> --base-url https://example.com
    python content-engine/bin/verify-live.py --all --base-url https://example.com
    python content-engine/bin/verify-live.py <slug> --base-url ... --from-file page.html
"""

import argparse
import json
import re
import sys
import urllib.error
import urllib.request
from datetime import date
from html.parser import HTMLParser
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

import promotion as pm         # noqa: E402
import textstats as ts         # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
DEFAULT_THEME = "kadence-child-lvjcb"
USER_AGENT = "waif-content-engine/verify-live (+repository tooling)"
TIMEOUT = 45

# Below this, a page is almost certainly rendering fallback rather than its
# own prose. Chosen from the corpus: the thinnest real page carries ~395 words.
MIN_BODY_WORDS = 250


class PageParser(HTMLParser):
    """Extracts the semantic properties the artifact makes promises about."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.title = ""
        self.meta = {}
        self.canonical = ""
        self.headings = {"h1": [], "h2": [], "h3": []}
        self.links = []
        self.jsonld = []
        self.text_parts = []
        self._stack = []
        self._capture = None
        self._in_main = 0
        self._skip = 0
        # Heading text arrives in several handle_data calls when it contains
        # an entity or inline markup, so it is buffered and flushed once on
        # the closing tag. Appending each chunk counted one <h1> as several.
        self._buffer = []

    def handle_starttag(self, tag, attrs):
        attr = dict(attrs)
        self._stack.append(tag)

        if tag in ("script", "style", "noscript"):
            if tag == "script" and attr.get("type") == "application/ld+json":
                self._capture = "jsonld"
            else:
                self._skip += 1
            return
        if tag == "title":
            self._capture = "title"
        elif tag == "meta":
            name = attr.get("name") or attr.get("property")
            if name:
                self.meta[name.lower()] = attr.get("content", "")
        elif tag == "link" and (attr.get("rel") or "").lower() in ("canonical", "['canonical']"):
            self.canonical = attr.get("href", "")
        elif tag in self.headings:
            self._capture = tag
            self._buffer = []
        elif tag == "main":
            self._in_main += 1
        elif tag == "a" and attr.get("href"):
            self.links.append(attr["href"])

    def handle_endtag(self, tag):
        if tag in ("script", "style", "noscript") and self._skip:
            self._skip -= 1
        if tag == "main" and self._in_main:
            self._in_main -= 1
        if self._capture == tag and tag in self.headings:
            text = re.sub(r"\s+", " ", "".join(self._buffer)).strip()
            if text:
                self.headings[tag].append(text)
            self._buffer = []
            self._capture = None
        elif self._capture == tag or (self._capture == "jsonld" and tag == "script"):
            self._capture = None
        if self._stack and self._stack[-1] == tag:
            self._stack.pop()

    def handle_data(self, data):
        if self._skip:
            return
        if self._capture == "title":
            self.title += data
        elif self._capture == "jsonld":
            self.jsonld.append(data)
        elif self._capture in self.headings:
            self._buffer.append(data)
        if self._in_main:
            self.text_parts.append(data)

    @property
    def body_text(self):
        return re.sub(r"\s+", " ", " ".join(self.text_parts)).strip()


def fetch(url):
    request = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
    with urllib.request.urlopen(request, timeout=TIMEOUT) as response:
        raw = response.read()
        charset = response.headers.get_content_charset() or "utf-8"
        return response.status, response.geturl(), raw.decode(charset, errors="replace")


def resolve_tokens(text, config):
    return (text.replace("{business}", config.get("business_name", ""))
                .replace("{phone}", config.get("phone_display", "")))


def expected_from_artifact(content, config):
    """What the promoted artifact promises a visitor will get."""
    paragraphs = []
    for section in content.get("sections", []):
        paragraphs += section.get("paragraphs", [])
        for block in section.get("blocks", []):
            paragraphs += block.get("paragraphs", [])
    return {
        "title": resolve_tokens(content.get("seo_title", ""), config),
        "description": resolve_tokens(content.get("seo_description", ""), config),
        "h1": resolve_tokens(content.get("hero_heading", ""), config),
        "section_headings": [resolve_tokens(s.get("heading", ""), config)
                             for s in content.get("sections", []) if s.get("heading")],
        "faq_questions": [f.get("question", "") for f in content.get("faq", [])],
        "internal_link_slugs": sorted({i.get("slug") for s in content.get("sections", [])
                                       if s.get("type") in ("areas", "services")
                                       for i in s.get("items", []) if i.get("slug")}),
        "sample_sentences": [resolve_tokens(p, config)[:110] for p in paragraphs[:6]],
        "expected_word_floor": max(MIN_BODY_WORDS, int(len(ts.tokens(" ".join(paragraphs))) * 0.6)),
    }


def compare(expected, page, config, url, final_url):
    blocking, warnings, observed = [], [], {}

    observed["title"] = page.title.strip()
    observed["description"] = page.meta.get("description", "").strip()
    observed["canonical"] = page.canonical
    observed["h1"] = page.headings["h1"][0] if page.headings["h1"] else ""
    observed["h2_count"] = len(page.headings["h2"])
    observed["body_words"] = len(ts.tokens(page.body_text))
    observed["jsonld_blocks"] = len(page.jsonld)
    observed["final_url"] = final_url

    if expected["title"] and observed["title"] != expected["title"]:
        blocking.append("title mismatch — expected %r, got %r"
                        % (expected["title"], observed["title"]))
    if expected["description"] and observed["description"] != expected["description"]:
        # Descriptions are truncated at 155 chars by lvjcb_truncate_for_seo().
        if not observed["description"].rstrip("…").strip() or \
           not expected["description"].startswith(observed["description"].rstrip("…").strip()[:80]):
            blocking.append("meta description mismatch — expected %r, got %r"
                            % (expected["description"][:70], observed["description"][:70]))
        else:
            warnings.append("meta description truncated on the live page (expected behaviour)")

    if not observed["canonical"]:
        blocking.append("no canonical URL")
    elif observed["canonical"].rstrip("/") != final_url.rstrip("/"):
        warnings.append("canonical %r differs from final URL %r" % (observed["canonical"], final_url))

    if len(page.headings["h1"]) != 1:
        blocking.append("expected exactly one H1, found %d" % len(page.headings["h1"]))
    elif expected["h1"] and observed["h1"] != expected["h1"]:
        warnings.append("H1 %r differs from hero_heading %r" % (observed["h1"], expected["h1"]))

    if observed["body_words"] < expected["expected_word_floor"]:
        blocking.append("rendered body is %d words, below the %d floor — the page is probably "
                        "rendering the templated fallback rather than its own content"
                        % (observed["body_words"], expected["expected_word_floor"]))

    missing_sections = [h for h in expected["section_headings"]
                        if h and h not in page.headings["h2"] and h not in page.headings["h3"]]
    if missing_sections:
        blocking.append("section heading(s) absent from the page: %s"
                        % "; ".join(missing_sections[:3]))

    body_lower = page.body_text.lower()
    missing_prose = [s for s in expected["sample_sentences"] if s and s.lower() not in body_lower]
    if missing_prose:
        blocking.append("%d promoted paragraph(s) do not appear in the rendered body"
                        % len(missing_prose))

    # Empty heading sections: a heading with no prose beneath it is how the v1
    # schema fork presented, and it is invisible without this check.
    empty = [h for h in page.headings["h2"]
             if h and body_lower.count(h.lower()) and len(body_lower.split(h.lower())[-1].strip()) < 40]
    if empty:
        warnings.append("heading(s) with little or no following text: %s" % "; ".join(empty[:3]))

    missing_faq = [q for q in expected["faq_questions"] if q and q.lower() not in body_lower]
    if missing_faq:
        blocking.append("%d FAQ question(s) missing from the page" % len(missing_faq))

    hrefs = " ".join(page.links)
    missing_links = [s for s in expected["internal_link_slugs"] if s and s not in hrefs]
    if missing_links:
        blocking.append("internal link(s) missing: %s" % ", ".join(missing_links))

    if not page.jsonld:
        warnings.append("no JSON-LD structured data found")
    else:
        types = set()
        for block in page.jsonld:
            try:
                types.update(re.findall(r'"@type"\s*:\s*"([^"]+)"', block))
            except Exception:  # noqa: BLE001 - malformed ld+json is a warning, not a crash
                pass
        observed["schema_types"] = sorted(types)
        if not types:
            warnings.append("JSON-LD present but no @type could be read")

    # Identity: tokens must have resolved, and forbidden literals must be gone.
    if "{business}" in page.body_text or "{phone}" in page.body_text:
        blocking.append("unresolved {business}/{phone} token visible on the page")
    for literal in ("Las Vegas Junk Car Buyers", "(702) 555-0134"):
        if literal in page.body_text:
            blocking.append("forbidden legacy identity present on the page: %r" % literal)
    if config.get("phone_display") and config["phone_display"] not in page.body_text:
        warnings.append("configured phone number does not appear in the rendered body")

    return blocking, warnings, observed


def verify(slug, base_url, theme, from_file=None):
    theme_dir = REPO_ROOT / "wordpress" / "themes" / theme
    config = pm.read_business_config(theme_dir)

    record_path = ENGINE / "reports" / "promotions" / ("%s.latest.json" % slug)
    record = json.loads(record_path.read_text(encoding="utf-8")) if record_path.is_file() else None

    if record:
        page_type, destination = record["page_type"], REPO_ROOT / record["destination"]
    else:
        # No promotion record: verify whatever is live against the committed
        # artifact. This is the read-only mode used for existing pages.
        page_type = ("service" if (theme_dir / "content" / "services" / ("%s.json" % slug)).is_file()
                     else "location")
        destination = theme_dir / "content" / pm.CONTENT_DIR_FOR[page_type] / ("%s.json" % slug)

    if not destination.is_file():
        return {"slug": slug, "status": "FAIL", "blocking_issues": ["no promoted artifact at %s" % destination],
                "warnings": [], "verified_at": date.today().isoformat()}

    content = json.loads(destination.read_text(encoding="utf-8"))

    # The primary city has no page of its own — its intent belongs to the
    # homepage (ADR-0004/ADR-0005). Comparing a homepage against an orphan
    # location artifact produces a mismatch that means nothing, so it is
    # reported as SKIP with the reason rather than a misleading FAIL.
    if page_type == "location" and slug in config["primary_slugs"]:
        return {
            "slug": slug, "status": "SKIP", "page_type": page_type,
            "artifact": destination.relative_to(REPO_ROOT).as_posix(),
            "url": pm.live_url(base_url, page_type, slug, config["primary_slugs"]),
            "blocking_issues": [], "warnings": [
                "%r is the primary city: the homepage owns this intent and no location page "
                "exists. This artifact is a known orphan (ADR-0005) and there is nothing to "
                "verify it against. Supply homepage content as content/pages/home.json to "
                "verify the homepage." % slug],
            "expected": {}, "observed": {}, "verified_at": date.today().isoformat(),
        }

    url = pm.live_url(base_url, page_type, slug, config["primary_slugs"])
    expected = expected_from_artifact(content, config)

    if from_file:
        status, final_url, html = 200, url, Path(from_file).read_text(encoding="utf-8", errors="replace")
    else:
        try:
            status, final_url, html = fetch(url)
        except (urllib.error.URLError, urllib.error.HTTPError, OSError) as exc:
            return {"slug": slug, "url": url, "status": "FAIL", "http_status": None,
                    "blocking_issues": ["could not fetch: %s" % exc],
                    "warnings": [], "expected": expected, "observed": {},
                    "verified_at": date.today().isoformat(),
                    "note": "Network access may be unavailable in this environment; "
                            "the verifier is intended for CI or a machine with public access."}

    if status != 200:
        return {"slug": slug, "url": url, "status": "FAIL", "http_status": status,
                "blocking_issues": ["HTTP %s" % status], "warnings": [],
                "expected": expected, "observed": {}, "verified_at": date.today().isoformat()}

    parser = PageParser()
    parser.feed(html)
    blocking, warnings, observed = compare(expected, parser, config, url, final_url)

    return {
        "slug": slug, "url": url, "http_status": status, "final_url": final_url,
        "page_type": page_type,
        "artifact": destination.relative_to(REPO_ROOT).as_posix(),
        "promotion_record": record_path.relative_to(REPO_ROOT).as_posix() if record else None,
        "status": "FAIL" if blocking else ("WARN" if warnings else "PASS"),
        "expected": expected, "observed": observed,
        "blocking_issues": blocking, "warnings": warnings,
        "verified_at": date.today().isoformat(),
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slug", nargs="?")
    parser.add_argument("--all", action="store_true")
    parser.add_argument("--base-url", required=True)
    parser.add_argument("--theme", default=DEFAULT_THEME)
    parser.add_argument("--from-file", help="verify a saved HTML file instead of fetching")
    parser.add_argument("--no-color", action="store_true")
    args = parser.parse_args()

    colour = sys.stdout.isatty() and not args.no_color
    RED, YEL, GRN, OFF = (("\033[31m", "\033[33m", "\033[32m", "\033[0m") if colour else ("",) * 4)

    theme_dir = REPO_ROOT / "wordpress" / "themes" / args.theme
    if args.all:
        slugs = sorted(p.stem for p in (theme_dir / "content").rglob("*.json"))
    elif args.slug:
        slugs = [args.slug]
    else:
        parser.error("give a slug or --all")

    out_dir = ENGINE / "reports" / "live"
    out_dir.mkdir(parents=True, exist_ok=True)
    worst = 0

    for slug in slugs:
        try:
            pm.validate_slug(slug)
            result = verify(slug, args.base_url, args.theme, args.from_file)
        except pm.PromotionError as exc:
            result = {"slug": slug, "status": "FAIL", "blocking_issues": [str(exc)],
                      "warnings": [], "verified_at": date.today().isoformat()}

        (out_dir / ("%s.json" % slug)).write_text(
            json.dumps(result, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")

        colour_for = {"PASS": GRN, "WARN": YEL, "SKIP": YEL, "FAIL": RED}[result["status"]]
        print("  %s%-4s%s %-20s %s" % (colour_for, result["status"], OFF, slug,
                                       result.get("url", "")))
        for issue in result.get("blocking_issues", []):
            print("         %sblocking%s %s" % (RED, OFF, issue))
        for warn in result.get("warnings", [])[:4]:
            print("         %swarn    %s %s" % (YEL, OFF, warn))
        worst = max(worst, {"PASS": 0, "WARN": 0, "SKIP": 0, "FAIL": 1}[result["status"]])

    print("-" * 64)
    print("reports -> content-engine/reports/live/")
    return worst


if __name__ == "__main__":
    sys.exit(main())
