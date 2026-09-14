"""Text metrics for corpus analysis — Python standard library only.

These are INTERNAL EDITORIAL QA METRICS. None of them is a search engine
ranking factor and none should be described as one. They exist to answer
one question a human editor would otherwise have to answer by reading
twelve pages side by side: are these pages actually different, or are they
the same page with the place names swapped?

The central measure here is `substitution_lift`. Ordinary similarity
scores are easy to defeat by accident — change a city name, a ZIP, and a
landmark, and word-level overlap drops enough to look like real variation.
So each pair is measured twice: once as written, and once with every
place-specific token masked to a common placeholder. If similarity jumps
when the names are removed, the difference between those pages *was* the
names. That gap is the lift, and it is the signal that maps to the failure
this project exists to correct.
"""

import re
import unicodedata
from collections import Counter

# Words carrying no topical signal. Deliberately small: an aggressive stop
# list hides duplication by deleting the boilerplate that constitutes it.
STOPWORDS = frozenset("""
a an the and or but if then than that this these those of in on at to for
with from by as is are was were be been being it its it's we our us you
your they them their he she his her i me my
do does did done can could will would shall should may might must
have has had not no nor so such own same too very just also
""".split())

_WORD_RE = re.compile(r"[a-z0-9']+")
_SENTENCE_RE = re.compile(r"(?<=[.!?])\s+")


def normalize(text):
    """Lowercase, strip accents, collapse whitespace."""
    text = unicodedata.normalize("NFKD", str(text))
    text = "".join(c for c in text if not unicodedata.combining(c))
    return re.sub(r"\s+", " ", text.lower()).strip()


def tokens(text, drop_stopwords=False):
    words = _WORD_RE.findall(normalize(text))
    return [w for w in words if w not in STOPWORDS] if drop_stopwords else words


def sentences(text):
    return [s.strip() for s in _SENTENCE_RE.split(str(text)) if s.strip()]


def shingles(text, n=5, drop_stopwords=False):
    """Set of overlapping n-word phrases."""
    words = tokens(text, drop_stopwords)
    if len(words) < n:
        return {" ".join(words)} if words else set()
    return {" ".join(words[i:i + n]) for i in range(len(words) - n + 1)}


def jaccard(a, b):
    if not a and not b:
        return 0.0
    union = len(a | b)
    return round(len(a & b) / union, 4) if union else 0.0


def containment(a, b):
    """Fraction of `a` also present in `b`. Asymmetric on purpose: a short
    page fully absorbed by a long one scores 1.0, which Jaccard would hide."""
    return round(len(a & b) / len(a), 4) if a else 0.0


def cosine(a_counts, b_counts):
    """Cosine over term frequencies."""
    shared = set(a_counts) & set(b_counts)
    dot = sum(a_counts[t] * b_counts[t] for t in shared)
    na = sum(v * v for v in a_counts.values()) ** 0.5
    nb = sum(v * v for v in b_counts.values()) ** 0.5
    return round(dot / (na * nb), 4) if na and nb else 0.0


def term_counts(text):
    return Counter(tokens(text, drop_stopwords=True))


def mask_terms(text, terms, placeholder="__PLACE__"):
    """Replace every listed term with a placeholder, longest first.

    Longest-first matters: masking 'Las Vegas' before 'North Las Vegas'
    would leave a stray 'North' in front of a placeholder and register as
    a difference that is not there.
    """
    masked = normalize(text)
    for term in sorted({normalize(t) for t in terms if t}, key=len, reverse=True):
        if term:
            masked = re.sub(r"\b%s\b" % re.escape(term), placeholder, masked)
    # ZIP codes and route numbers are place identity too.
    masked = re.sub(r"\b\d{5}\b", placeholder, masked)
    masked = re.sub(r"\b[ir]-\d{1,3}\b", placeholder, masked)
    return masked


def substitution_lift(text_a, text_b, terms_a, terms_b, n=5):
    """How much of the difference between two texts is just place names.

    Returns raw similarity, similarity after masking place terms, and the
    lift between them. A high lift is the machine-visible form of
    'same page + replace city name'.
    """
    raw = jaccard(shingles(text_a, n), shingles(text_b, n))
    all_terms = set(terms_a) | set(terms_b)
    masked = jaccard(
        shingles(mask_terms(text_a, all_terms), n),
        shingles(mask_terms(text_b, all_terms), n),
    )
    return {
        "raw_shingle_jaccard": raw,
        "masked_shingle_jaccard": masked,
        "substitution_lift": round(masked - raw, 4),
    }


def content_words(text):
    """Set of meaning-carrying words — the vocabulary a page draws on.

    At 400-500 words per page, exact 5-word phrase matches are rare even
    between pages built from one template, so n-gram overlap reads as
    'different' when only the wording changed. Vocabulary overlap survives
    rewording and is the more honest lexical signal at this length.
    """
    return set(tokens(text, drop_stopwords=True))


def vocabulary_lift(text_a, text_b, terms_a, terms_b):
    """Substitution lift measured on vocabulary rather than phrases."""
    raw = jaccard(content_words(text_a), content_words(text_b))
    all_terms = set(terms_a) | set(terms_b)
    masked = jaccard(
        content_words(mask_terms(text_a, all_terms)),
        content_words(mask_terms(text_b, all_terms)),
    )
    return {
        "raw_vocabulary_jaccard": raw,
        "masked_vocabulary_jaccard": masked,
        "vocabulary_substitution_lift": round(masked - raw, 4),
    }


def structure_signature(sections):
    """Shape of a page, independent of its words.

    Two pages with identical signatures are built to the same plan even if
    every sentence differs — which word-level metrics cannot see.
    """
    signature = []
    for section in sections:
        kind = section.get("type", "content")
        parts = [kind]
        if section.get("paragraphs"):
            parts.append("p%d" % len(section["paragraphs"]))
        if section.get("blocks"):
            parts.append("b%d" % len(section["blocks"]))
        if section.get("table"):
            parts.append("t%d" % len(section["table"].get("rows", [])))
        if section.get("image"):
            parts.append("img")
        if section.get("items"):
            parts.append("i%d" % len(section["items"]))
        if section.get("steps"):
            parts.append("s%d" % len(section["steps"]))
        signature.append(":".join(parts))
    return signature


def sequence_similarity(a, b):
    """Similarity of two ordered signatures, by longest common subsequence."""
    if not a or not b:
        return 0.0
    rows = len(a) + 1
    cols = len(b) + 1
    table = [[0] * cols for _ in range(rows)]
    for i in range(1, rows):
        for j in range(1, cols):
            table[i][j] = (table[i - 1][j - 1] + 1 if a[i - 1] == b[j - 1]
                           else max(table[i - 1][j], table[i][j - 1]))
    return round(2 * table[-1][-1] / (len(a) + len(b)), 4)


def percentiles(values, points=(10, 25, 50, 75, 90, 95)):
    if not values:
        return {}
    ordered = sorted(values)
    out = {}
    for p in points:
        k = (len(ordered) - 1) * p / 100
        low, high = int(k), min(int(k) + 1, len(ordered) - 1)
        out["p%d" % p] = round(ordered[low] + (ordered[high] - ordered[low]) * (k - low), 4)
    return out


def describe(values):
    if not values:
        return {"count": 0}
    return dict(
        count=len(values),
        min=round(min(values), 4),
        max=round(max(values), 4),
        mean=round(sum(values) / len(values), 4),
        **percentiles(values),
    )
