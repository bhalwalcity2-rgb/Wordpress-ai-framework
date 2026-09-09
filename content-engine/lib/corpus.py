"""Corpus model — loads content files and extracts analysable structure.

Standard library only. Extraction here is **lexical, not semantic**: there
is no parser, no model, no embedding. Entities are recognised by matching
against vocabularies drawn from `business-config.php` plus small typed word
lists, and by shape (capitalised phrases, route numbers, ZIPs).

That limit is deliberate and must be stated wherever the output is used. A
lexical entity list is evidence about a page's topic graph, not a
description of it. It is reliable for the question actually being asked —
do these twelve pages cover the same ground in the same way — and
unreliable as a claim about meaning.
"""

import json
import re
from collections import Counter

import textstats as ts

# --- typed vocabularies -------------------------------------------------
# Small and explicit. Each term earns its place by appearing in this
# business's corpus; this is not an attempt at a general ontology.

PROCESS_TERMS = {
    "towing", "tow", "pickup", "pick-up", "title", "paperwork", "quote",
    "offer", "payment", "paid", "scheduling", "schedule", "haul", "flatbed",
    "salvage", "scrap", "recycling", "dismantler", "junkyard", "removal",
    "transfer", "lien", "registration", "appraisal", "inspection",
}
CONDITION_TERMS = {
    "non-running", "running", "wrecked", "damaged", "totaled", "rusted",
    "flooded", "junk", "inoperable", "unregistered", "broken", "blown",
    "overheating", "stripped", "abandoned", "high-mileage", "end-of-life",
}
PROBLEM_TERMS = {
    "hoa", "cc&r", "ccrs", "violation", "fine", "citation", "eyesore",
    "compliance", "warning", "deadline", "notice", "tenant", "landlord",
    "inherited", "estate", "insurance", "payout", "deductible",
}
BUSINESS_ENTITY_TERMS = {
    "dmv", "nrs", "irs", "epa", "bbb", "clark county", "nevada dmv",
    "department of motor vehicles", "state of nevada",
}
VEHICLE_MAKES = {
    "honda", "toyota", "nissan", "chevy", "chevrolet", "ford", "hyundai",
    "ram", "jeep", "dodge", "kia", "subaru", "mazda", "gmc", "buick",
    "chrysler", "volkswagen", "bmw", "mercedes", "lexus", "acura", "infiniti",
}

_PROPER_RE = re.compile(r"\b([A-Z][a-z]+(?:\s+(?:of|the|de|and)\s+|\s+)?(?:[A-Z][a-zA-Z']+\s*){0,3})")
_ZIP_RE = re.compile(r"\b\d{5}\b")
_ROUTE_RE = re.compile(r"\b(?:I-\d{1,3}|US-\d{1,3}|SR-\d{1,3}|Highway \d+|Route \d+)\b", re.I)
_MONEY_RE = re.compile(r"\$[\d,]+(?:\s*(?:to|-|–)\s*\$?[\d,]+)?\+?")

INTENT_MARKERS = {
    "transactional": ["sell", "cash", "get paid", "call", "quote", "offer",
                      "same-day", "same day", "free towing", "pick up", "we buy"],
    "commercial": ["how much", "worth", "value", "compare", "best", "why choose",
                   "vs", "versus", "price", "pays more"],
    "informational": ["how to", "what is", "what happens", "guide", "steps",
                      "can i", "do i need", "explained", "rules", "law"],
    "local": ["near me", "in ", "areas", "neighborhood", "zip", "serving"],
}

CLAIM_PATTERNS = [
    ("pricing_payment", re.compile(r"\$|\bpay(s|ment)?\b|\bcash\b|\bprice|\bworth\b|\bquote\b", re.I)),
    ("service_guarantee", re.compile(r"\bfree\b|\bno fee|\bnever\b|\balways\b|\bguarantee|\bfirm\b|\bno obligation", re.I)),
    # 'liens' was missed by \blien\b, which dropped the highest-risk claim on
    # the Henderson page — a legal assertion about selling without a title.
    # 'without ... title' and model-year conditions are added for the same reason.
    ("government_legal", re.compile(
        r"\bNRS\b|\bDMV\b|\bstatute|\blaw\b|\blegal\b|\bForm [A-Z]{2}-\d+"
        r"|\btitle\b.*\brequire|\bliens?\b|\bwithout\b[^.]*\btitle\b"
        r"|\bmodel year\b|\bsalvage title\b|\bregistration\b", re.I)),
    ("process_claim", re.compile(r"\bsame[- ]day\b|\bwithin \d+|\bin under\b|\b\d+ (?:to \d+ )?minutes\b|\bhours\b|\btwo minutes\b", re.I)),
    ("testimonial_review", re.compile(r"\breview|\bstar|\brating|\bcustomers say", re.I)),
    ("local_fact", re.compile(r"\bpopulation\b|\bsquare miles\b|\bsecond-largest\b|\bzip code|\bcounty\b|\bresidents\b", re.I)),
    ("unverifiable", re.compile(r"\bmost\b|\bbusiest\b|\bone of the\b|\bmany\b|\bevery week\b|\btypically\b|\bmajority\b|\balmost certainly\b", re.I)),
]


def load_pages(content_dir, config):
    """Load every content file into an analysable page record."""
    pages = []
    for kind, page_type in (("locations", "location"), ("services", "service"), ("pages", "home")):
        directory = content_dir / kind
        if not directory.is_dir():
            continue
        for path in sorted(directory.glob("*.json")):
            data = json.loads(path.read_text(encoding="utf-8"))
            pages.append(Page(path, page_type, data, config))
    return pages


class Page:
    def __init__(self, path, page_type, data, config):
        self.path = path
        self.slug = path.stem
        self.page_type = page_type
        self.data = data
        self.config = config
        self.city = data.get("city", "")
        self.renders = self._renders()

    # -- lifecycle -------------------------------------------------------

    def _renders(self):
        """Whether this file reaches a page at all."""
        if self.page_type == "location" and self.slug in self.config.get("primary_slugs", []):
            return False
        return bool(self.data.get("sections"))

    # -- text ------------------------------------------------------------

    def body_text(self):
        """Prose a reader actually sees, excluding metadata."""
        parts = [self.data.get("hero_description", "")]
        for section in self.data.get("sections", []):
            parts += [section.get("intro", ""), section.get("footnote", "")]
            parts += section.get("paragraphs", [])
            for block in section.get("blocks", []):
                parts.append(block.get("heading", ""))
                parts += block.get("paragraphs", [])
            for step in section.get("steps", []):
                parts += [step.get("heading", ""), step.get("description", "")]
            for item in section.get("items", []):
                parts.append(item.get("note", ""))
            for row in (section.get("table") or {}).get("rows", []):
                for cell in row:
                    parts.append(cell.get("text", "") if isinstance(cell, dict) else cell)
        return " ".join(p for p in parts if p)

    def faq_questions(self):
        return [f.get("question", "") for f in self.data.get("faq", [])]

    def faq_answers(self):
        return [f.get("answer", "") for f in self.data.get("faq", [])]

    def faq_text(self):
        return " ".join(self.faq_questions() + self.faq_answers())

    def all_text(self):
        """Joined with sentence terminators, not spaces.

        A bare space let a heading run into the paragraph beneath it, so
        sentence splitting produced one long pseudo-sentence and claim
        extraction treated a hero heading as an assertion.
        """
        parts = [self.data.get("hero_heading", ""), self.body_text(), self.faq_text()]
        return ". ".join(p.strip().rstrip(".") for p in parts if p and p.strip())

    def headings(self):
        out = []
        for section in self.data.get("sections", []):
            if section.get("heading"):
                out.append(section["heading"])
            for block in section.get("blocks", []):
                if block.get("heading"):
                    out.append(block["heading"])
        return out

    def word_count(self):
        return len(ts.tokens(self.all_text()))

    # -- place identity --------------------------------------------------

    def place_terms(self):
        """Every token that names this page's place, for masking."""
        terms = set()
        if self.city:
            terms.add(self.city)
        for item in self.config.get("service_area_names", []):
            terms.add(item)
        terms |= set(self.local_entities())
        return {t for t in terms if t}

    # -- entities --------------------------------------------------------

    def _proper_nouns(self):
        found = Counter()
        for raw in [self.body_text(), " ".join(self.headings()), self.faq_text()]:
            for match in _PROPER_RE.findall(raw):
                phrase = " ".join(match.split()).strip(" ,.")
                if len(phrase) < 3 or phrase.lower() in ts.STOPWORDS:
                    continue
                found[phrase] += 1
        return found

    def local_entities(self):
        """Place names: neighbourhoods, roads, ZIPs, routes."""
        text = self.all_text()
        out = set(_ZIP_RE.findall(text)) | {m.strip() for m in _ROUTE_RE.findall(text)}
        road_words = ("Road", "Rd", "Avenue", "Ave", "Boulevard", "Blvd", "Highway",
                      "Parkway", "Pkwy", "Street", "St", "Drive", "Lane", "Ranch",
                      "Valley", "Hills", "Springs", "Park", "City", "Village", "Manor")
        for phrase, _ in self._proper_nouns().items():
            if any(phrase.endswith(" " + w) or phrase == w for w in road_words):
                out.add(phrase)
            elif phrase in self.config.get("service_area_names", []):
                out.add(phrase)
        return sorted(out)

    def typed_entities(self):
        text = ts.normalize(self.all_text())
        words = set(ts.tokens(text))

        def hits(vocab):
            return sorted({t for t in vocab if (" " in t and t in text) or t in words})

        proper = self._proper_nouns()
        local = set(self.local_entities())
        return {
            "main_topic": self._main_topic(),
            "local": sorted(local),
            "process": hits(PROCESS_TERMS),
            "attributes_condition": hits(CONDITION_TERMS),
            "user_problem": hits(PROBLEM_TERMS),
            "business_regulatory": hits(BUSINESS_ENTITY_TERMS),
            "vehicle_makes": hits(VEHICLE_MAKES),
            "other_proper_nouns": sorted(
                p for p, c in proper.items() if c >= 2 and p not in local
            )[:20],
        }

    def _main_topic(self):
        if self.page_type == "location":
            return "junk car buying in %s" % (self.city or self.slug.replace("-", " "))
        if self.page_type == "home":
            return "junk car buying (primary city)"
        return "junk car buying — %s" % self.slug.replace("-", " ")

    def entity_relationships(self):
        """Typed co-occurrence edges within a sentence.

        Co-occurrence is not a semantic relation. It is recorded as
        evidence of which entity types a page actually connects, which is
        what distinguishes a page that explains something from one that
        lists terms.
        """
        typed = self.typed_entities()
        buckets = {k: set(v) for k, v in typed.items()
                   if k in ("local", "process", "user_problem", "business_regulatory", "attributes_condition")}
        edges = Counter()
        for sentence in ts.sentences(self.all_text()):
            low = ts.normalize(sentence)
            present = [k for k, terms in buckets.items()
                       if any(t.lower() in low for t in terms)]
            for i, a in enumerate(sorted(present)):
                for b in sorted(present)[i + 1:]:
                    edges["%s--%s" % (a, b)] += 1
        return dict(edges.most_common(12))

    # -- intent ----------------------------------------------------------

    def intent(self):
        haystack = ts.normalize(" ".join(
            [self.data.get("seo_title", ""), self.data.get("hero_heading", "")]
            + self.headings() + self.faq_questions()
        ))
        scores = {k: sum(haystack.count(m) for m in markers)
                  for k, markers in INTENT_MARKERS.items()}
        primary = max(scores, key=scores.get) if any(scores.values()) else "transactional"
        secondary = sorted((k for k in scores if k != primary and scores[k] > 0),
                           key=lambda k: -scores[k])
        return {"primary": primary, "signals": scores, "secondary": secondary}

    # -- claims ----------------------------------------------------------

    def claims(self):
        out = []
        for sentence in ts.sentences(self.all_text()):
            if len(sentence.split()) < 4:
                continue
            kinds = [name for name, pattern in CLAIM_PATTERNS if pattern.search(sentence)]
            if not kinds:
                continue
            out.append({
                "text": sentence.strip(),
                "types": kinds,
                "money": _MONEY_RE.findall(sentence),
                "risk": _claim_risk(kinds, sentence),
            })
        return out

    # -- links -----------------------------------------------------------

    def declared_links(self):
        block = self.data.get("internal_links", {}) or {}
        return {
            "services": [x.get("slug") for x in block.get("services", [])],
            "locations": [x.get("slug") for x in
                          block.get("locations", []) + block.get("nearby_locations", [])],
        }

    def rendered_links(self):
        """Links the templates will actually emit."""
        areas, services = [], []
        for section in self.data.get("sections", []):
            if section.get("type") == "areas":
                areas += [i.get("slug") for i in section.get("items", [])]
            if section.get("type") == "services":
                services += [i.get("slug") for i in section.get("items", [])]
            for row in (section.get("table") or {}).get("rows", []):
                for cell in row:
                    if isinstance(cell, dict) and cell.get("service"):
                        services.append(cell["service"])
        # lvjcb_autolink_locations() links the first mention of any city.
        mentioned = [n for n in self.config.get("service_area_names", [])
                     if n and n.lower() in ts.normalize(self.all_text()) and n != self.city]
        return {
            "areas_sections": sorted(set(areas)),
            "services_sections": sorted(set(services)),
            "autolinked_cities": sorted(set(mentioned)),
        }


def _claim_risk(kinds, sentence):
    if "government_legal" in kinds:
        return "high"
    if "testimonial_review" in kinds or "pricing_payment" in kinds:
        return "medium"
    if "unverifiable" in kinds and "local_fact" in kinds:
        return "medium"
    return "low"
