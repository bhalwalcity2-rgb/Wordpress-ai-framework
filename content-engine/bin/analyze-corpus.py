#!/usr/bin/env python3
"""Measure the existing content corpus. Analysis only — writes no content.

Produces, under content-engine/:

  brief/{slug}.json                    retro-brief: what the page does today
  reports/differentiation/{slug}.json  per-page similarity against siblings
  reports/corpus-summary.json          distributions, clusters, priority matrix

Every number here is an INTERNAL EDITORIAL QA METRIC. None is a search
engine ranking factor and none should be presented as one. They exist to
replace reading twelve pages side by side.

No threshold in this file fails a build. Phase 3B is calibration: the point
is to learn the corpus's actual distribution before choosing a cut-off,
because a threshold picked in advance measures the guess, not the corpus.

Usage:
    python content-engine/bin/analyze-corpus.py [theme-slug]
"""

import argparse
import json
import re
import sys
from collections import Counter
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

import corpus as cp           # noqa: E402
import textstats as ts        # noqa: E402

ENGINE = REPO_ROOT / "content-engine"
DEFAULT_THEME = "kadence-child-lvjcb"
SHINGLE_N = 5

# A phrase on this fraction of pages or more is corpus boilerplate.
BOILERPLATE_SHARE = 0.5


def read_config(theme_dir):
    """Business config values the analysis needs."""
    source = (theme_dir / "inc" / "business-config.php").read_text(encoding="utf-8")
    config = {"location_slugs": [], "primary_slugs": [], "service_area_names": []}
    for line in source.splitlines():
        m = re.search(r"'city'\s*=>\s*'([^']*)',\s*'state'\s*=>\s*'[^']*',\s*'slug'\s*=>\s*'([^']+)'", line)
        if not m:
            continue
        config["service_area_names"].append(m.group(1))
        config["location_slugs"].append(m.group(2))
        if re.search(r"'is_primary'\s*=>\s*true", line):
            config["primary_slugs"].append(m.group(2))
    block = re.search(r"'services'\s*=>\s*array\((.*?)\n\t\),", source, re.S)
    config["service_slugs"] = re.findall(r"'slug'\s*=>\s*'([^']+)'", block.group(1)) if block else []
    return config


# --------------------------------------------------------------------------
# Information gain
# --------------------------------------------------------------------------

def information_gain(pages):
    """Split every page's phrases into unique, shared, and boilerplate.

    A phrase carried by half the corpus is not information the page gives
    a reader; it is the template speaking. Unique phrases are candidate
    information gain — candidate, because uniqueness is necessary for gain
    and nowhere near sufficient.
    """
    per_page = {p.slug: ts.shingles(p.all_text(), SHINGLE_N) for p in pages}
    counts = Counter()
    for shingle_set in per_page.values():
        counts.update(shingle_set)

    total = len(per_page)
    threshold = max(2, int(total * BOILERPLATE_SHARE))
    out = {}
    for slug, shingle_set in per_page.items():
        unique = {s for s in shingle_set if counts[s] == 1}
        boiler = {s for s in shingle_set if counts[s] >= threshold}
        shared = shingle_set - unique - boiler
        out[slug] = {
            "unique_phrase_count": len(unique),
            "shared_phrase_count": len(shared),
            "boilerplate_phrase_count": len(boiler),
            "unique_share": round(len(unique) / len(shingle_set), 4) if shingle_set else 0.0,
            "boilerplate_share": round(len(boiler) / len(shingle_set), 4) if shingle_set else 0.0,
            "sample_unique_phrases": sorted(unique)[:8],
            "sample_boilerplate_phrases": sorted(boiler)[:5],
        }
    return out


def gain_verdict(stats, unique_local_names, unique_nonlocal, unique_structure):
    """Whether a page tells a reader anything its siblings do not.

    The distinction that matters is between a page that names a different
    landmark and one that explains a different situation. Swapping
    "Maryland Parkway" for "Boulder Highway" is a new string, not new
    information — the brief for this phase names landmark-swapping as
    superficial, so unique place names alone cannot earn a gain verdict.

    Real gain requires either a topic this page raises and its siblings do
    not (HOA enforcement, a state title rule, a 55+ community) or content
    structure carrying facts prose alone would not (a ZIP-and-access table).

    Deliberately conservative: when nothing survives, the record says so
    rather than promoting a rephrased sentence into an insight.
    """
    if stats["unique_phrase_count"] == 0:
        return "NO_MEANINGFUL_INFORMATION_GAIN"
    if unique_nonlocal or unique_structure:
        return "HAS_INFORMATION_GAIN"
    if unique_local_names:
        # Different names for the same kind of fact.
        return "LOCAL_NAME_SUBSTITUTION_ONLY"
    if stats["unique_share"] >= 0.35:
        return "WORDING_ONLY_GAIN"
    return "NO_MEANINGFUL_INFORMATION_GAIN"


# --------------------------------------------------------------------------
# Pairwise comparison
# --------------------------------------------------------------------------

def compare(a, b):
    body = ts.substitution_lift(a.all_text(), b.all_text(), a.place_terms(), b.place_terms(), SHINGLE_N)
    faq_q = ts.jaccard(ts.shingles(" ".join(a.faq_questions()), 3),
                       ts.shingles(" ".join(b.faq_questions()), 3))
    faq_a = ts.jaccard(ts.shingles(" ".join(a.faq_answers()), 4),
                       ts.shingles(" ".join(b.faq_answers()), 4))
    ea, eb = a.typed_entities(), b.typed_entities()

    def ent(key):
        return ts.jaccard(set(ea.get(key, [])), set(eb.get(key, [])))

    non_local = set()
    for key in ("process", "attributes_condition", "user_problem", "business_regulatory", "vehicle_makes"):
        non_local |= {"%s:%s" % (key, v) for v in ea.get(key, [])}
    non_local_b = set()
    for key in ("process", "attributes_condition", "user_problem", "business_regulatory", "vehicle_makes"):
        non_local_b |= {"%s:%s" % (key, v) for v in eb.get(key, [])}

    return {
        "with": b.slug,
        **body,
        **ts.vocabulary_lift(a.all_text(), b.all_text(), a.place_terms(), b.place_terms()),
        "trigram_jaccard": ts.jaccard(ts.shingles(a.all_text(), 3), ts.shingles(b.all_text(), 3)),
        # FAQ compared by topic, not phrasing: rewording an answer while
        # keeping its substance is explicitly a superficial difference.
        "faq_topic_similarity": ts.jaccard(
            ts.content_words(" ".join(a.faq_questions())),
            ts.content_words(" ".join(b.faq_questions()))),
        "faq_answer_topic_similarity": ts.jaccard(
            ts.content_words(" ".join(a.faq_answers())),
            ts.content_words(" ".join(b.faq_answers()))),
        "token_cosine": ts.cosine(ts.term_counts(a.all_text()), ts.term_counts(b.all_text())),
        "containment_in_sibling": ts.containment(
            ts.shingles(a.all_text(), SHINGLE_N), ts.shingles(b.all_text(), SHINGLE_N)),
        "heading_similarity": ts.jaccard(ts.shingles(" ".join(a.headings()), 2),
                                         ts.shingles(" ".join(b.headings()), 2)),
        "structure_similarity": ts.sequence_similarity(
            ts.structure_signature(a.data.get("sections", [])),
            ts.structure_signature(b.data.get("sections", []))),
        "faq_question_similarity": faq_q,
        "faq_answer_similarity": faq_a,
        "entity_similarity_nonlocal": ts.jaccard(non_local, non_local_b),
        "entity_similarity_local": ent("local"),
        "entity_similarity_process": ent("process"),
    }


# Thresholds derived from this corpus's observed percentiles, not chosen in
# advance. Each cites the percentile it sits at, so a later corpus can be
# re-measured and these moved rather than inherited by habit.
#
# The first pass used 5-gram phrase overlap and found nothing: at 400-500
# words per page, exact five-word matches are rare even between pages built
# from one template. That was a property of the instrument, not of the
# corpus. Vocabulary, structure, entity frame, and FAQ topic all survive
# rewording, and are what actually discriminate here.
FLAG_RULES = (
    ("IDENTICAL_STRUCTURE",              "structure_similarity",         0.85, "corpus p50 = 0.857"),
    ("SHARED_ENTITY_FRAME",              "entity_similarity_nonlocal",   0.50, "corpus p75 = 0.526"),
    ("SHARED_FAQ_TOPICS",                "faq_topic_similarity",         0.28, "corpus p75 = 0.286"),
    ("SHARED_FAQ_ANSWER_SUBSTANCE",      "faq_answer_topic_similarity",  0.20, "corpus p90 = 0.221"),
    ("LEXICAL_ECHO",                     "token_cosine",                 0.45, "corpus p90 = 0.455"),
    ("SHARED_VOCABULARY",                "raw_vocabulary_jaccard",       0.27, "corpus p75 = 0.262"),
    ("NEAR_IDENTICAL_ONCE_PLACES_MASKED", "masked_vocabulary_jaccard",   0.30, "corpus p90 = 0.286"),
    ("PLACE_NAME_SUBSTITUTION",          "vocabulary_substitution_lift", 0.05, "corpus max = 0.015"),
    ("SHARED_HEADING_PLAN",              "heading_similarity",           0.16, "corpus p90 = 0.163"),
)

# Signals that together mean "same brief, different words". Structure,
# entities, and FAQ substance are weighted as clone evidence because a page
# can defeat every lexical measure by rewording while changing nothing a
# reader would notice.
CLONE_SIGNALS = (
    "IDENTICAL_STRUCTURE",
    "SHARED_ENTITY_FRAME",
    "SHARED_FAQ_TOPICS",
    "SHARED_FAQ_ANSWER_SUBSTANCE",
    "LEXICAL_ECHO",
)


def superficial_flags(pair):
    """Name the specific pattern a pair exhibits, not just a score."""
    return [name for name, key, cut, _ in FLAG_RULES if pair.get(key, 0) >= cut]


def clone_signal_count(pair):
    return sum(1 for f in pair["superficial_flags"] if f in CLONE_SIGNALS)


# --------------------------------------------------------------------------
# Retro-brief
# --------------------------------------------------------------------------

def build_brief(page, gain, diff, config):
    entities = page.typed_entities()
    intent = page.intent()
    claims = page.claims()
    links = page.rendered_links()
    declared = page.declared_links()

    unique_local = sorted(set(entities["local"]) - diff["local_entities_shared_with_siblings"])
    verdict = gain_verdict(gain, unique_local, diff["unique_nonlocal_entities"], diff["has_unique_structure"])

    generic, unique_sections = [], []
    for index, section in enumerate(page.data.get("sections", [])):
        label = "%s[%d]: %s" % (section.get("type", "content"), index, section.get("heading", "")[:60])
        (unique_sections if section.get("table") or section.get("blocks") else generic).append(label)

    return {
        "_generated_by": "content-engine/bin/analyze-corpus.py",
        "_purpose": (
            "Analytical record of what this page does TODAY. Not a writing "
            "instruction — it must never be used to reproduce the weaknesses "
            "it documents."
        ),
        "_method": "Lexical extraction, standard library only. No semantic model.",

        "slug": page.slug,
        "page_type": page.page_type,
        "renders": page.renders,
        "word_count": page.word_count(),

        "current_primary_topic": entities["main_topic"],
        "current_primary_intent": intent["primary"],
        "secondary_intents": intent["secondary"],
        "current_keyword_focus": {
            "seo_title": page.data.get("seo_title", ""),
            "hero_heading": page.data.get("hero_heading", ""),
            "top_terms": [t for t, _ in ts.term_counts(page.all_text()).most_common(12)],
        },
        "current_query_entities": page.faq_questions(),
        "current_entities": entities,
        "current_local_entities": entities["local"],
        "entity_relationships": page.entity_relationships(),

        "current_sections": [
            {"type": s.get("type", "content"), "heading": s.get("heading", ""),
             "has_table": bool(s.get("table")), "has_blocks": bool(s.get("blocks")),
             "has_image": bool(s.get("image"))}
            for s in page.data.get("sections", [])
        ],
        "current_faq_topics": [q for q in page.faq_questions()],

        "current_internal_links": {
            "declared_legacy_metadata": declared,
            "actually_rendered": links,
            "declared_but_not_rendered": sorted(
                (set(declared["locations"]) | set(declared["services"]))
                - set(links["areas_sections"]) - set(links["services_sections"])
                - set(config.get("service_area_names", []))
            ),
        },

        "current_business_claims": claims,
        "claim_counts": dict(Counter(t for c in claims for t in c["types"])),

        "current_information_gain": {
            "verdict": verdict,
            **gain,
            "unique_local_entities": unique_local,
            "unique_nonlocal_entities": diff["unique_nonlocal_entities"],
            "has_unique_structure": diff["has_unique_structure"],
        },
        "current_unique_elements": {
            "sections_with_structure": unique_sections,
            "unique_local_entities": unique_local,
            "unique_phrase_share": gain["unique_share"],
        },
        "current_generic_elements": {
            "prose_only_sections": generic,
            "boilerplate_phrase_share": gain["boilerplate_share"],
            "shared_local_entities": sorted(diff["local_entities_shared_with_siblings"])[:15],
        },

        "likely_sibling_overlap": {
            "max_masked_vocabulary_similarity": diff["max_masked_vocabulary_similarity"],
            "max_clone_signals": diff["max_clone_signals"],
            "closest_sibling": diff["closest_sibling"],
            "superficial_differentiation_flags": diff["aggregate_flags"],
            "assessment": diff["assessment"],
        },
        "known_quality_issues": diff["quality_issues"],
        "rewrite_priority": diff["priority"],
    }


# --------------------------------------------------------------------------
# Priority matrix
# --------------------------------------------------------------------------

def score_page(page, gain, pair_stats, config, verdict_gain):
    """Scores 0-5. Derived from measurements, not opinion, so the matrix is
    reproducible and its inputs are auditable."""
    max_masked = max((p["masked_vocabulary_jaccard"] for p in pair_stats), default=0.0)
    max_struct = max((p["structure_similarity"] for p in pair_stats), default=0.0)
    max_faq = max((p["faq_answer_topic_similarity"] for p in pair_stats), default=0.0)
    words = page.word_count()

    business_value = 5 if page.page_type == "home" else (4 if page.page_type == "service" else 3)
    if page.slug in config.get("primary_slugs", []):
        business_value = 1

    organic_opportunity = 4 if page.page_type == "location" else 3
    current_quality = min(5, max(0, round(words / 300))) if words else 0
    max_signals = max((clone_signal_count(dict(p, superficial_flags=superficial_flags(p)))
                       for p in pair_stats), default=0)
    duplication_risk = round(min(5, max_signals + max_struct * 1.0 + max_masked * 2.0), 1)
    local_relevance = min(5, len(page.typed_entities()["local"]))
    gain_score = {"HAS_INFORMATION_GAIN": 4, "WORDING_ONLY_GAIN": 2,
                  "LOCAL_NAME_SUBSTITUTION_ONLY": 1,
                  "NO_MEANINGFUL_INFORMATION_GAIN": 0}[verdict_gain]
    cannibalization = 4 if not page.renders else (3 if max_faq >= 0.3 else 1)
    technical_health = 5 if page.renders else 0

    if not page.renders:
        verdict = "CONSIDER REMOVAL"
    elif duplication_risk >= 3.0 or gain_score == 0:
        verdict = "REWRITE"
    elif duplication_risk >= 1.8 or current_quality <= 2:
        verdict = "REFINE"
    else:
        verdict = "KEEP"

    return {
        "slug": page.slug,
        "page_type": page.page_type,
        "business_value": business_value,
        "organic_opportunity": organic_opportunity,
        "current_quality": current_quality,
        "duplication_risk": duplication_risk,
        "local_relevance": local_relevance,
        "information_gain": gain_score,
        "cannibalization_risk": cannibalization,
        "technical_health": technical_health,
        "word_count": words,
        "verdict": verdict,
    }


# --------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("theme", nargs="?", default=DEFAULT_THEME)
    args = parser.parse_args()

    theme_dir = REPO_ROOT / "wordpress" / "themes" / args.theme
    config = read_config(theme_dir)
    pages = cp.load_pages(theme_dir / "content", config)
    gain = information_gain(pages)

    brief_dir = ENGINE / "brief"
    diff_dir = ENGINE / "reports" / "differentiation"
    brief_dir.mkdir(parents=True, exist_ok=True)
    diff_dir.mkdir(parents=True, exist_ok=True)

    by_type = {}
    for page in pages:
        by_type.setdefault(page.page_type, []).append(page)

    all_pairs, priority, briefs = [], [], []

    for page in pages:
        siblings = [s for s in by_type[page.page_type] if s.slug != page.slug]
        pair_stats = [compare(page, s) for s in siblings]
        for pair in pair_stats:
            pair["superficial_flags"] = superficial_flags(pair)

        shared_local = set()
        for sibling in siblings:
            shared_local |= set(page.typed_entities()["local"]) & set(sibling.typed_entities()["local"])

        # Topics this page raises that no sibling does. These, not place
        # names, are what separate explaining a situation from renaming one.
        NONLOCAL_KEYS = ("user_problem", "business_regulatory", "attributes_condition", "process")
        mine = {"%s:%s" % (k, v) for k in NONLOCAL_KEYS for v in page.typed_entities().get(k, [])}
        theirs = set()
        for sibling in siblings:
            theirs |= {"%s:%s" % (k, v) for k in NONLOCAL_KEYS for v in sibling.typed_entities().get(k, [])}
        unique_nonlocal = sorted(mine - theirs)

        # Structure carrying facts prose alone would not (a reference table,
        # named sub-blocks) that siblings lack.
        def structured(pg):
            return any(sec.get("table") or sec.get("blocks") for sec in pg.data.get("sections", []))
        unique_structure = structured(page) and not any(structured(s) for s in siblings)

        ranked = sorted(pair_stats, key=lambda p: (-clone_signal_count(p), -p["masked_vocabulary_jaccard"]))
        closest = ranked[0] if ranked else None
        flags = sorted({f for p in pair_stats for f in p["superficial_flags"]})

        issues = []
        if not page.renders:
            issues.append("DOES_NOT_RENDER — no page exists for this file")
        if page.word_count() < 500 and page.page_type != "home":
            issues.append("BELOW_500_WORDS — SEO_STANDARDS.md §6 minimum for a location page")
        if gain[page.slug]["unique_share"] < 0.35:
            issues.append("LOW_UNIQUE_PHRASE_SHARE")
        if "IDENTICAL_STRUCTURE" in flags:
            issues.append("STRUCTURE_MATCHES_SIBLINGS")
        if "SHARED_FAQ_ANSWER_SUBSTANCE" in flags or "SHARED_FAQ_TOPICS" in flags:
            issues.append("FAQ_SUBSTANCE_SHARED_WITH_SIBLINGS")
        if "SHARED_ENTITY_FRAME" in flags:
            issues.append("SAME_TOPIC_FRAME_AS_SIBLINGS")
        if not page.data.get("final_cta"):
            issues.append("NO_PAGE_SPECIFIC_CLOSING_CTA")

        max_masked = closest["masked_vocabulary_jaccard"] if closest else 0.0
        max_signals = max((clone_signal_count(p) for p in pair_stats), default=0)
        verdict_gain = gain_verdict(gain[page.slug], sorted(set(page.typed_entities()["local"]) - shared_local),
                                    unique_nonlocal, unique_structure)

        if not page.renders:
            assessment = "ORPHAN"
        elif max_signals >= 3:
            # Different words, same plan — the failure this corpus exhibits.
            assessment = "SUPERFICIAL_DIFFERENTIATION"
        elif max_signals == 2 or verdict_gain != "HAS_INFORMATION_GAIN":
            assessment = "PARTIAL_DIFFERENTIATION"
        else:
            assessment = "REAL_DIFFERENTIATION"

        diff = {
            "slug": page.slug,
            "unique_nonlocal_entities": unique_nonlocal,
            "has_unique_structure": unique_structure,
            "page_type": page.page_type,
            "renders": page.renders,
            "compared_against": [s.slug for s in siblings],
            "max_masked_vocabulary_similarity": max_masked,
            "max_clone_signals": max_signals,
            "closest_sibling": closest["with"] if closest else None,
            "aggregate_flags": flags,
            "assessment": assessment,
            "quality_issues": issues,
            "local_entities_shared_with_siblings": shared_local,
            "pairs": ranked,
        }
        score = score_page(page, gain[page.slug], pair_stats, config, verdict_gain)
        diff["priority"] = score["verdict"]
        priority.append(score)

        serialisable = dict(diff, local_entities_shared_with_siblings=sorted(shared_local))
        (diff_dir / ("%s.json" % page.slug)).write_text(
            json.dumps(serialisable, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")

        brief = build_brief(page, gain[page.slug], diff, config)
        (brief_dir / ("%s.json" % page.slug)).write_text(
            json.dumps(brief, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")
        briefs.append(brief)
        all_pairs += [dict(p, of=page.slug) for p in pair_stats]

    # -- corpus summary --------------------------------------------------
    def dist(key):
        return ts.describe([p[key] for p in all_pairs])

    clusters = {}
    for pair in all_pairs:
        if clone_signal_count(pair) >= 3:
            clusters.setdefault(pair["of"], []).append(pair["with"])

    summary = {
        "_purpose": "Internal editorial QA metrics. Not search ranking factors.",
        "_calibration_note": (
            "No threshold here fails a build. The distributions below exist so a "
            "cut-off can be chosen from this corpus rather than guessed in advance."
        ),
        "theme": args.theme,
        "pages_analysed": len(pages),
        "pages_that_render": sum(1 for p in pages if p.renders),
        "pair_comparisons": len(all_pairs),
        "shingle_size": SHINGLE_N,
        "distributions": {
            "raw_shingle_jaccard": dist("raw_shingle_jaccard"),
            "masked_shingle_jaccard": dist("masked_shingle_jaccard"),
            "substitution_lift": dist("substitution_lift"),
            "trigram_jaccard": dist("trigram_jaccard"),
            "raw_vocabulary_jaccard": dist("raw_vocabulary_jaccard"),
            "masked_vocabulary_jaccard": dist("masked_vocabulary_jaccard"),
            "vocabulary_substitution_lift": dist("vocabulary_substitution_lift"),
            "faq_topic_similarity": dist("faq_topic_similarity"),
            "faq_answer_topic_similarity": dist("faq_answer_topic_similarity"),
            "structure_similarity": dist("structure_similarity"),
            "faq_answer_similarity": dist("faq_answer_similarity"),
            "entity_similarity_nonlocal": dist("entity_similarity_nonlocal"),
            "heading_similarity": dist("heading_similarity"),
            "token_cosine": dist("token_cosine"),
        },
        "flag_frequency": dict(Counter(f for p in all_pairs for f in p["superficial_flags"]).most_common()),
        "assessments": dict(Counter(b["likely_sibling_overlap"]["assessment"] for b in briefs)),
        "templated_clusters": clusters,
        "information_gain_verdicts": dict(Counter(
            b["current_information_gain"]["verdict"] for b in briefs)),
        "word_counts": ts.describe([b["word_count"] for b in briefs]),
        "priority_matrix": sorted(priority, key=lambda s: (-s["duplication_risk"], s["slug"])),
        "priority_verdicts": dict(Counter(s["verdict"] for s in priority)),
        "claim_totals": dict(Counter(
            t for b in briefs for c in b["current_business_claims"] for t in c["types"])),
        "high_risk_claims": [
            {"slug": b["slug"], "text": c["text"]}
            for b in briefs for c in b["current_business_claims"] if c["risk"] == "high"
        ],
        "candidate_thresholds": _candidate_thresholds(all_pairs),
        "internal_link_analysis": _link_analysis(pages, briefs, config),
    }
    (ENGINE / "reports").mkdir(parents=True, exist_ok=True)
    (ENGINE / "reports" / "corpus-summary.json").write_text(
        json.dumps(summary, indent="\t", ensure_ascii=False) + "\n", encoding="utf-8")

    print("Analysed %d pages (%d render) — %d pair comparisons"
          % (len(pages), summary["pages_that_render"], len(all_pairs)))
    print("  briefs  -> content-engine/brief/")
    print("  reports -> content-engine/reports/")
    for name, count in summary["assessments"].items():
        print("  %-28s %d" % (name, count))
    return 0


def _link_analysis(pages, briefs, config):
    """What the existing link signals say. Analysis only — no engine built.

    The useful signal is the asymmetry: pages name different neighbours,
    and a page that names all of them has expressed no judgement at all.
    """
    total_areas = len(config.get("location_slugs", []))
    per_page, inbound = {}, Counter()

    for brief in briefs:
        links = brief["current_internal_links"]
        declared = set(links["declared_legacy_metadata"]["locations"])
        rendered = links["actually_rendered"]
        contextual = set(rendered["areas_sections"]) | set(rendered["autolinked_cities"])
        for target in declared:
            inbound[target] += 1

        notes = []
        if len(declared) >= total_areas - 1:
            notes.append("LINKS_TO_NEARLY_EVERY_AREA — expresses no adjacency judgement")
        if declared and not rendered["areas_sections"]:
            notes.append("DECLARED_BUT_NOT_RENDERED — the judgement exists but reaches no page")
        if not contextual:
            notes.append("NO_CONTEXTUAL_LOCATION_LINKS")
        if not rendered["services_sections"]:
            notes.append("NO_CONTEXTUAL_SERVICE_LINKS")

        per_page[brief["slug"]] = {
            "declared_neighbours": sorted(declared),
            "declared_count": len(declared),
            "rendered_area_links": rendered["areas_sections"],
            "rendered_service_links": rendered["services_sections"],
            "autolinked_cities": rendered["autolinked_cities"],
            "notes": notes,
        }

    orphans = [s for s in config.get("location_slugs", [])
               if inbound[s] == 0 and s not in config.get("primary_slugs", [])]

    return {
        "_scope": "Analysis only. No linking engine built in this phase.",
        "per_page": per_page,
        "inbound_declared_mentions": dict(inbound.most_common()),
        "never_named_as_a_neighbour": orphans,
        "observations": [
            "Declared neighbour sets are asymmetric and mostly 4 of 7 — a real "
            "editorial judgement, and the reason internal_links was retained.",
            "Only pages with an 'areas' section render those neighbour links; the "
            "rest rely on lvjcb_autolink_locations() catching a city named in prose.",
            "A future engine must not link every page to every area: %d areas linked "
            "pairwise is a uniform mesh with no signal, and contradicts the anchor-text "
            "variety rule in SEO_STANDARDS.md §12." % total_areas,
        ],
    }


def _candidate_thresholds(pairs):
    """Recommend cut-offs from the observed distribution, with reasoning."""
    def p(key):
        return ts.percentiles([x[key] for x in pairs])

    signals = [sum(1 for f in x["superficial_flags"] if f in CLONE_SIGNALS) for x in pairs]

    return {
        "_method": (
            "Percentiles of THIS corpus. Re-run after any rewrite; a corpus that "
            "improves should move these, and a fixed number would hide that."
        ),
        "_instrument_note": (
            "5-gram phrase overlap was tried first and found almost nothing "
            "(max 0.040) because these pages are 400-500 words and were reworded "
            "rather than copied. That is a limit of the instrument, not evidence of "
            "originality. Vocabulary, structure, entity frame, and FAQ topic survive "
            "rewording and are the metrics recommended below."
        ),
        "structure_similarity": {
            "observed": p("structure_similarity"),
            "candidate_warn": 0.70, "candidate_block": 0.85,
            "note": "Strongest single signal in this corpus — p50 is 0.857, so more "
                    "than half of all sibling pairs are built to the same plan.",
        },
        "entity_similarity_nonlocal": {
            "observed": p("entity_similarity_nonlocal"),
            "candidate_warn": 0.50, "candidate_block": 0.65,
            "note": "Two pages covering the identical non-local topic frame differ "
                    "only in place names, whatever their wording.",
        },
        "faq_topic_similarity": {
            "observed": p("faq_topic_similarity"),
            "candidate_warn": 0.28, "candidate_block": 0.40,
            "note": "Compared by topic, not phrasing — rewording an answer while "
                    "keeping its substance is explicitly superficial.",
        },
        "masked_vocabulary_jaccard": {
            "observed": p("masked_vocabulary_jaccard"),
            "candidate_warn": 0.27, "candidate_block": 0.32,
            "note": "Vocabulary overlap after place names are masked.",
        },
        "vocabulary_substitution_lift": {
            "observed": p("vocabulary_substitution_lift"),
            "candidate_warn": 0.05, "candidate_block": 0.10,
            "note": "Near zero across this corpus (max 0.015). Literal place-name "
                    "substitution is NOT this corpus's failure mode. Retained because "
                    "it is cheap and would catch a regression to copy-and-swap.",
        },
        "clone_signal_count": {
            "observed": ts.describe(signals),
            "candidate_warn": 2, "candidate_block": 3,
            "note": "The recommended primary gate.",
        },
        "_composite_rule": (
            "No single metric should gate publication. Count how many of "
            "IDENTICAL_STRUCTURE, SHARED_ENTITY_FRAME, SHARED_FAQ_TOPICS, "
            "SHARED_FAQ_ANSWER_SUBSTANCE and LEXICAL_ECHO a pair trips: 2 warns, "
            "3 blocks. A page must not pass merely because its words differ, if its "
            "structure, entities, FAQ substance and information gain match a sibling."
        ),
    }


if __name__ == "__main__":
    sys.exit(main())
