"""Provenance and promotion rules for the research system.

One place decides what may be stated as fact, so the bootstrapper and the
validator cannot drift apart on the question that matters most.

The governing principle: **nothing is promoted without evidence.** A claim
already live on the site is not thereby verified — several claims in this
corpus are published and unsourced. An LLM-generated statement is never a
source; at best it is a hypothesis to be researched.
"""

import re

# Evidence levels a future writer may state as fact.
STATABLE_AS_FACT = frozenset({"verified_fact", "sourced_fact", "business_provided"})

# Levels that must never be written as fact — only researched, or written
# as explicitly attributed uncertainty.
NOT_STATABLE = frozenset({"hypothesis", "editorial_opportunity", "unknown_needs_verification"})

# Statuses that block publication outright.
BLOCKING_STATUS = frozenset({"unresolved", "unsupported"})

# A claim carrying legal or regulatory weight needs an authority behind it.
# Reputable secondary reporting is not sufficient for a statute or a fee.
AUTHORITATIVE_SOURCES = frozenset({"official_government", "primary_source"})

REQUIRED_SOURCES_BY_RISK = {
    "high": AUTHORITATIVE_SOURCES,
    "medium": AUTHORITATIVE_SOURCES | {"official_business", "reputable_secondary"},
    "low": AUTHORITATIVE_SOURCES | {"official_business", "reputable_secondary", "research_observation"},
}

# Claim types that are always high risk regardless of how they are phrased.
ALWAYS_HIGH_RISK = frozenset({"government_legal_regulatory"})

# Entity groups that can ground an angle, mapped to the angle type they
# suggest. Place names are deliberately absent: naming a different street
# is not a different situation.
ANGLE_TYPE_BY_ENTITY_GROUP = {
    "user_problem": "customer_problem",
    "business_regulatory": "compliance_process",
    "attributes_condition": "vehicle_situation",
    "process": "logistical_situation",
}

# Which distinctive entity group should ground an angle, most situation-like
# first. Count alone is the wrong selector: every junk-car page mentions
# rusted, totaled and non-running vehicles, so a large condition group
# describes the industry, not this page. A single mention of an HOA
# enforcement rule differentiates more than five condition adjectives.
ANGLE_GROUP_PRIORITY = ("user_problem", "business_regulatory", "process", "attributes_condition")


def choose_angle_group(groups):
    """Pick the entity group most likely to carry a real situation."""
    for group in ANGLE_GROUP_PRIORITY:
        if groups.get(group):
            return group
    return max(groups, key=lambda g: len(groups[g])) if groups else None

# Words that, alone, describe no situation. An angle statement made only of
# these plus a place name is a renamed template, not an angle.
SUPERFICIAL_ONLY = frozenset("""
junk car cars vehicle vehicles sell selling sold buy buying buyer buyers
cash removal remove tow towing free service services area areas near local
we our you your best top fast quick same day
""".split())


def claim_is_publishable(claim):
    """Whether a claim may be stated as fact. Returns (bool, reason)."""
    risk = claim.get("risk", "high")
    status = claim.get("status", "unresolved")
    source_type = claim.get("source_type", "unverified")

    if status in BLOCKING_STATUS:
        return False, "status is '%s'" % status
    if source_type == "unverified":
        return False, "no source"
    allowed = REQUIRED_SOURCES_BY_RISK.get(risk, AUTHORITATIVE_SOURCES)
    if source_type not in allowed:
        return False, "%s risk requires one of: %s" % (risk, ", ".join(sorted(allowed)))
    if risk == "high" and not claim.get("verified_on"):
        return False, "high risk requires verified_on"
    return True, "ok"


def normalise_risk(claim_type, current):
    return "high" if claim_type in ALWAYS_HIGH_RISK else current


def is_superficial_angle(statement, place_terms):
    """True when a statement collapses to nothing once place names are removed.

    This is the machine-checkable form of the rule that a landmark, ZIP, or
    city name is not an angle. Masking the place and stripping generic
    junk-car vocabulary should still leave a describable situation; if it
    does not, the statement was a label.
    """
    text = statement.lower()
    for term in sorted({t.lower() for t in place_terms if t}, key=len, reverse=True):
        text = re.sub(r"\b%s\b" % re.escape(term), " ", text)
    text = re.sub(r"\b\d{5}\b", " ", text)
    words = [w for w in re.findall(r"[a-z']+", text) if w not in SUPERFICIAL_ONLY and len(w) > 2]
    return len(set(words)) < 4


def angle_can_be_approved(angle, claims_by_id):
    """An angle needs at least one publishable claim behind it."""
    evidence = angle.get("evidence") or []
    if not evidence:
        return False, "no evidence cited"
    for ref in evidence:
        claim = claims_by_id.get(ref.get("claim_id"))
        if not claim:
            continue
        ok, _ = claim_is_publishable(claim)
        if ok:
            return True, "ok"
    return False, "no cited claim is publishable"
