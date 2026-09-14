"""Page-existence gate and production-brief assembly.

The gate is the load-bearing part. Everything downstream — the brief, the
authoring package, the draft — is refused unless a page has earned the right
to exist, and "earned" means evidence, not intent.

A generator asked to differentiate twelve pages will always produce twelve
differentiations. Refusing is not a behaviour it falls into on its own, so
refusal has to live here, in code, ahead of any writing.
"""

import json

import research as rs
import textstats as ts

# Only an approved angle authorises a page. Everything else is work in
# progress, and work in progress is not a licence to publish.
WRITABLE_ANGLE_STATUS = frozenset({"approved", "used"})

# Failure modes measured in Phase 3B, restated as instructions to the writer.
FORBIDDEN_GENERIC_PATTERNS = [
    "Swapping a city, neighbourhood, ZIP or landmark name for a sibling's and calling it different",
    "Reusing a sibling's section order — identical structure was 50 of 68 sibling pairs",
    "Restating a sibling's FAQ substance in new wording",
    "Explaining the same generic process every sibling already explains",
    "Inserting keyword variants that a person would not say out loud",
    "Padding to reach a word count",
]

FORBIDDEN_WRITING_PATTERNS = [
    "Invented statistics, reviews, testimonials or customer stories",
    "Invented first-hand experience, years in business, staff, or local ownership",
    "Fabricated citations, or source text quoted into prose to look authoritative",
    "Any factual statement not traceable to an allowed claim id",
    "Presenting a hypothesis or unverified claim as established fact",
    "Rewriting, spinning or paraphrasing a sibling page",
]


class GateResult:
    def __init__(self):
        self.checks = []
        self.blocked_reason = None
        self.recommended_action = "not_applicable"

    def check(self, name, passed, detail):
        self.checks.append({"check": name, "passed": bool(passed), "detail": detail})
        return passed

    @property
    def status(self):
        return "ALLOWED" if all(c["passed"] for c in self.checks) else "BLOCKED"

    def as_dict(self):
        out = {"status": self.status, "checks": self.checks}
        if self.status == "BLOCKED":
            out["blocked_reason"] = self.blocked_reason or "; ".join(
                c["check"] for c in self.checks if not c["passed"])
            out["recommended_action"] = self.recommended_action
        return out


def run_gate(slug, angles_doc, claims_doc, place_doc, retro_brief, diff_report,
             purpose="differentiated_informational", config=None):
    """Decide whether this page may proceed to a production brief.

    Branches on page purpose (ADR-0009). An informational page must earn an
    angle and information gain. A service-area page must be a configured
    area resting on business-provided claims - judging it on information
    gain asks a question it is not trying to answer. Structural
    differentiation applies to both, so neither route permits a clone.
    """
    if purpose == "service_area_transactional":
        return _run_service_area_gate(slug, claims_doc, config or {}), None

    gate = GateResult()

    verdict = angles_doc.get("differentiation_verdict")
    angle_list = angles_doc.get("angles", [])
    approved = [a for a in angle_list if a.get("status") in WRITABLE_ANGLE_STATUS]

    # 1. Research must not have concluded the page has no reason to exist.
    if not gate.check(
        "differentiation_supported",
        verdict != "NO_SUPPORTED_DIFFERENTIATION",
        "differentiation_verdict is %r" % verdict,
    ):
        gate.blocked_reason = "NO_SUPPORTED_DIFFERENTIATION"
        gate.recommended_action = {
            "merge_with_another_page": "merge_with_another_page",
            "create_broader_regional_page": "broaden_regional_scope",
            "change_page_purpose": "change_page_purpose",
            "exclude_from_publication_set": "keep_out_of_publication_set",
        }.get(angles_doc.get("recommendation_if_unsupported"), "keep_out_of_publication_set")

    # 2. An approved angle must exist and belong to this page.
    if not gate.check(
        "approved_angle_exists",
        bool(approved),
        "angle statuses present: %s" % (", ".join(sorted({a.get("status", "?") for a in angle_list})) or "none"),
    ):
        if gate.blocked_reason is None:
            gate.blocked_reason = "NO_APPROVED_ANGLE"
            gate.recommended_action = "complete_research"

    angle = approved[0] if approved else None

    if angle:
        gate.check("angle_belongs_to_page", angle.get("page") == slug,
                   "angle.page=%r, brief slug=%r" % (angle.get("page"), slug))
        gate.check("angle_not_superficial",
                   not rs.is_superficial_angle(angle.get("statement", ""),
                                               set(place_doc.get("sections", {}).get("community_context", []) and [])),
                   "statement survives place-name masking")

    # 3. Evidence: at least one publishable claim, and no high-risk claim
    #    smuggled in without authoritative support.
    claims = claims_doc.get("claims", [])
    publishable = [c for c in claims if c.get("publishable")]
    gate.check("publishable_claims_exist", bool(publishable),
               "%d of %d claims are publishable" % (len(publishable), len(claims)))

    bad_high_risk = [
        c["id"] for c in publishable
        if c.get("risk") == "high" and c.get("source_type") not in rs.AUTHORITATIVE_SOURCES
    ]
    gate.check("high_risk_claims_authoritative", not bad_high_risk,
               "offending: %s" % (", ".join(bad_high_risk) if bad_high_risk else "none"))

    # 4. Information gain must be establishable from measurement, not asserted.
    gain_verdict = (retro_brief or {}).get("current_information_gain", {}).get("verdict")
    unique_nonlocal = (diff_report or {}).get("unique_nonlocal_entities") or []
    has_gain = bool(publishable) or bool(unique_nonlocal)
    if not gate.check(
        "information_gain_establishable", has_gain,
        "retro-brief verdict=%r, unique non-local entities=%d" % (gain_verdict, len(unique_nonlocal)),
    ):
        if gate.blocked_reason is None:
            gate.blocked_reason = "NO_ESTABLISHABLE_INFORMATION_GAIN"
            gate.recommended_action = "merge_with_another_page"

    # 5. Research must not still be open on anything the angle depends on.
    if angle and angle.get("blocked_by"):
        gate.check("angle_not_blocked", False,
                   "blocked_by: %s" % ", ".join(angle["blocked_by"]))
        if gate.blocked_reason is None:
            gate.blocked_reason = "ANGLE_BLOCKED_BY_OPEN_RESEARCH"
            gate.recommended_action = "complete_research"

    return gate, angle


def _run_service_area_gate(slug, claims_doc, config):
    """Existence test for a service-area page.

    Deliberately narrow: the page exists because the business serves the
    area and can say true things about the service. It is NOT exempt from
    the differentiation checks, which run later against the draft - that is
    what stops this becoming the route back to a templated corpus.
    """
    gate = GateResult()

    areas = set(config.get("location_slugs", []))
    primary = set(config.get("primary_slugs", []))

    if not gate.check("is_configured_service_area", slug in areas,
                      "not present in business-config.php service_areas"):
        gate.blocked_reason = "NOT_A_CONFIGURED_SERVICE_AREA"
        gate.recommended_action = "keep_out_of_publication_set"

    if not gate.check("not_the_primary_city", slug not in primary,
                      "the homepage owns the primary city's intent"):
        gate.blocked_reason = "PRIMARY_CITY_HAS_NO_LOCATION_PAGE"
        gate.recommended_action = "change_page_purpose"

    publishable = [c for c in claims_doc.get("claims", []) if c.get("publishable")]
    business = [c for c in publishable if c.get("source_type") == "official_business"]
    if not gate.check("publishable_business_claims_exist", bool(business),
                      "%d publishable claim(s), %d business-provided"
                      % (len(publishable), len(business))):
        gate.blocked_reason = "NO_PUBLISHABLE_BUSINESS_CLAIMS"
        gate.recommended_action = "complete_research"

    bad = [c["id"] for c in publishable
           if c.get("risk") == "high" and c.get("source_type") not in rs.AUTHORITATIVE_SOURCES]
    gate.check("high_risk_claims_authoritative", not bad, ", ".join(bad))

    return gate


def sibling_constraints(slug, diff_report, corpus_summary, sibling_briefs):
    """What the writer must avoid, assembled BEFORE writing rather than checked after."""
    pairs = (diff_report or {}).get("pairs", [])
    compared = (diff_report or {}).get("compared_against", [])

    structures, questions, generic, shared_entities, shared_claims = [], [], [], set(), set()

    for pair in pairs:
        flags = pair.get("superficial_flags", [])
        if "IDENTICAL_STRUCTURE" in flags:
            structures.append("%s — same section plan; do not reuse its order or section mix" % pair["with"])
        if "SHARED_FAQ_TOPICS" in flags or "SHARED_FAQ_ANSWER_SUBSTANCE" in flags:
            questions.append("%s — FAQ substance already overlaps; ask different questions" % pair["with"])
        if "SHARED_ENTITY_FRAME" in flags:
            generic.append("%s — same non-local topic frame; explaining it again adds nothing" % pair["with"])

    for other in sibling_briefs.values():
        shared_entities.update(other.get("current_entities", {}).get("process", [])[:8])
        for question in other.get("current_faq_topics", []):
            questions.append("already answered on %s: %s" % (other["slug"], question))
        for claim in other.get("current_business_claims", [])[:5]:
            shared_claims.add(claim["text"][:90])

    thresholds = (corpus_summary or {}).get("candidate_thresholds", {})
    return {
        "compared_against": compared,
        "structures_to_avoid": structures or ["No structural overlap measured — still derive structure from this page's own purpose"],
        "questions_already_answered": questions[:40],
        "generic_explanations_to_avoid": generic,
        "shared_entities": sorted(shared_entities),
        "shared_claims": sorted(shared_claims)[:20],
        "topic_opportunities": (diff_report or {}).get("unique_nonlocal_entities", []),
        "calibrated_signals": {
            "_note": (
                "Phase 3B corpus percentiles, carried as editorial signals rather than blind rules. "
                "They describe this corpus and should be re-measured after any rewrite."),
            "clone_signal_count": thresholds.get("clone_signal_count", {}),
            "structure_similarity": thresholds.get("structure_similarity", {}),
            "entity_similarity_nonlocal": thresholds.get("entity_similarity_nonlocal", {}),
            "faq_topic_similarity": thresholds.get("faq_topic_similarity", {}),
        },
    }


def allowed_claims(claims_doc):
    """Split the ledger into what a writer may and may not state."""
    allowed, blocked, high_risk, sources = [], [], [], set()
    for claim in claims_doc.get("claims", []):
        if claim.get("publishable"):
            allowed.append(claim["id"])
            if claim.get("risk") == "high":
                high_risk.append(claim["id"])
            sources.update(claim.get("source_ids", []))
        else:
            blocked.append(claim["id"])
    return {
        "allowed_claim_ids": allowed,
        "blocked_claim_ids": blocked,
        "high_risk_claims": high_risk,
        "required_source_ids": sorted(sources),
    }


def depth_guidance(question_count, intent):
    """Depth from need, not a universal target. Longer is not better."""
    if intent == "informational" and question_count >= 8:
        return "Substantial — many distinct questions to resolve.", [900, 1400]
    if question_count >= 5:
        return "Moderate — enough to answer the questions properly and stop.", [600, 1000]
    return ("Short. This page has few distinct questions; padding it would add "
            "the filler the corpus already has too much of.", [350, 700])
