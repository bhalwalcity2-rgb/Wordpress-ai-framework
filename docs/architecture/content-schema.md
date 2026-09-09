# Content Schema — Canonical Contract

| Field | Value |
|---|---|
| Version | 2.0 (canonical) |
| Status | Frozen — [ADR-0003](../../ai/decisions/ADR-0003.md) |
| Date | 2026-09-03 |
| Applies to | `wordpress/themes/{theme}/content/locations/*.json`, `content/services/*.json` |
| Enforced by | `content-engine/bin/validate-content.py` (CI, every PR and push to `main`) |
| Schemas | `content-engine/config/schema/{location,service,common}.schema.json` |

---

## 1. Canonical vs deprecated

| | v2 — canonical | v1 — superseded |
|---|---|---|
| Prose | `sections[].paragraphs` (array of strings) | `sections[].body` (one string, `\n\n` separated) |
| Section types | `content`, `steps`, `cta`, `areas` | `content` only |
| Rich structure | `blocks[]`, `table{}`, `image{}`, `eyebrow`, `intro`, `footnote` | none |
| Page furniture | `faq_heading`, `final_cta` | none |
| Business identity | `{business}` / `{phone}` tokens | literal strings |
| Status | Required | **Rejected by validation** |

v1 is not merely discouraged — a file containing `body` fails the build. That key produced no output at all: `template-parts/written-page.php` reads `paragraphs`, and `rich-content.php` returns early only when the heading is *also* missing, so a v1 section published as an empty `<h2>`.

---

## 2. Where each field is consumed

The contract mirrors the renderer. If they disagree, the contract is wrong.

| Field | Read by |
|---|---|
| `seo_title`, `seo_description` | `inc/seo.php` → `lvjcb_compute_seo()`; `inc/cli.php` → Rank Math meta on provision |
| `hero_heading`, `hero_description` | `template-parts/components/hero.php` |
| `sections[]` | `template-parts/written-page.php`, dispatched on `type` |
| `faq`, `faq_heading` | `template-parts/sections/faq.php`; also `lvjcb_get_faq_schema()` for FAQPage JSON-LD |
| `final_cta` | `template-parts/sections/cta-banner.php` |
| `city`, `state`, `slug` | `inc/seo.php` for LocalBusiness/Service schema and breadcrumbs |
| `internal_links` | **Nothing.** Deprecated — see §5. |

### Section types

| `type` | Template | Required keys |
|---|---|---|
| `content` | `sections/rich-content.php` | `heading`, plus at least one of `paragraphs` / `blocks` / `table` |
| `steps` | `sections/how-it-works.php` | `heading`, `steps[]{heading, description}` |
| `cta` | `sections/cta-banner.php` | `heading` |
| `areas` | `sections/service-areas.php` | `heading`, `items[]{slug}` |
| `services` | `sections/what-we-buy.php` | `heading`; `items[]{slug}` optional — omit for all services |

An unrecognised `type` is an error. The renderer's `default:` branch would otherwise draw it as a content section without complaint — silently wrong output, which is the failure mode this contract exists to remove.

Every slug named in an `areas` or `services` entry, and in a table cell's `service` key, must exist in `business-config.php`. The renderer skips what it cannot resolve — correct at runtime, since a retired service should not fatal a live page — so validation is what stops a typo from silently deleting a card or a link.

---

## 2a. Three tiers of page composition

Per [ADR-0004](../../ai/decisions/ADR-0004.md), every component belongs to exactly one tier, decided by where its truth lives.

| Tier | What | Who owns the data | Content file can… |
|---|---|---|---|
| **1 — Global** | hero, trust strip, recently-purchased vehicles, testimonials, closing CTA, contact information | `business-config.php` | nothing — always rendered, cannot be declared or suppressed |
| **2 — Content-driven** | `content`, `steps`, `cta` | the content file | everything: prose, order, headings |
| **3 — Opt-in by slug** | `areas`, `services` | `business-config.php` | choose inclusion, placement, subset, and a per-entry `note` |

**Why testimonials and vehicles are Tier 1.** They are site-wide facts with one owner. A per-page review list is the mechanism by which fabricated reviews get written, which `ai/memory/master-website-workflow.md` Phase 07 prohibits outright. Making them global also means a page cannot lose its social proof by omission — the defect that prompted this tier split, when writing Henderson stripped the vehicles and testimonials the generic layout had shown.

**Why `why_choose_us` is deliberately absent.** Its content is generic differentiator copy that reads identically on every page. Offering it as one line of JSON would make the templated pattern the path of least resistance. A written page that needs the argument makes it in its own prose, in a `content` section. It remains available through the generic layout for pages with no content file.

---

## 3. Business identity is tokenised

Content files carry `{business}` and `{phone}`, never literals. `lvjcb_resolve_content_tokens()` (`inc/content-loader.php`) resolves them once at load, so SEO, provisioning, and templates all receive real values from `business-config.php`.

This is enforced, not advisory. Eleven files once carried a hardcoded business name and phone number that belonged to no one, and both were served live in `<title>` and `<meta name="description">`. The validator now rejects any literal business name, phone-shaped string, or address in a content file.

---

## 4. Content cannot fail silently

Three paths used to degrade a page to the generic templated layout with no signal. All three are now observable:

| Path | Before | Now |
|---|---|---|
| Malformed JSON | `json_decode` returned `null`, page rendered generic | `lvjcb_content_error()` writes to the error log, the screen under `WP_DEBUG`, and the WP-CLI console; CI fails the build |
| `sections` empty or absent | Fell through to the templated layout | `minItems: 1` — validation error |
| Prose in an unrendered key | Published an empty heading | `additionalProperties: false` — validation error naming `body` explicitly |

A missing content file is still a legitimate fallback — that is what lets pages be migrated one at a time — but the validator reports which pages are in that state rather than leaving it to be assumed.

---

## 5. Deprecated: `internal_links` — retained deliberately

Present in every content file; read by no PHP. Internal links are produced at render time by `lvjcb_autolink_locations()`, by `areas` sections, and by service-linked table cells.

**Decision (2026-09-03): retained as deprecated metadata, not removed.** The three functions that once gave it a purpose — `lvjcb_render_internal_links()`, `lvjcb_build_location_links()`, `lvjcb_build_service_links()` — were deleted, along with the `internal-links.php` section that never rendered. The key itself was kept, because it records an editorial judgement about which services and neighbouring areas relate to each page. That judgement is an input the internal-linking stage will want, and re-deriving it later is strictly harder than keeping it now.

Behaviour is therefore explicit:

| | |
|---|---|
| Schema | Optional, `x-deprecated` |
| Validator | **Warning**, never an error |
| Renderer | Nothing reads it |
| New content | Do not add it |
| Existing content | Left in place; removing it is safe but unnecessary |

### What a future content brief should take from it

The internal-linking engine is not built. When it is, this is the signal already sitting in the repository, and the constraint it must respect.

`internal_links.nearby_locations` is **not** a list of every service area — it is a per-page editorial judgement about which areas are genuinely adjacent. Henderson names four of the seven; Paradise names a different four. That asymmetry is the useful part.

| Source | What it means |
|---|---|
| `internal_links.nearby_locations` | Which areas a human judged geographically or commercially adjacent to this page |
| `internal_links.services` | Which services were judged most relevant to this page's audience |
| `areas[].items[].note` | Why that area is adjacent, in the page's own words — the anchor-text seed |
| Table cells with `service` | Services this page already links contextually, in prose |

**The engine must not link every page to every location.** Eight areas × eight areas is a footer-link mesh: uniform anchor text, no editorial signal, and precisely the pattern `SEO_STANDARDS.md` §12 warns about under anchor-text variety and over-optimisation. The existing per-page subsets are the counter-evidence to that instinct, which is the reason this key was retained rather than deleted.

---

## 5a. The primary city has no location page

`business-config.php` marks one service area `is_primary` — the city the homepage itself targets. Three places already agreed on the consequence: `lvjcb_get_location_url()` resolves it to `/`, `lvjcb_get_service_area_cards()` omits it from the grid, and `provision.php` deleted any page found at its slug.

`inc/cli.php` did not, and provisioned a page anyway — one nothing linked to, competing with the homepage for the same query. That is why `/service-areas/las-vegas/` existed only long enough to be deleted by hand, and why the URL returns 404.

`wp lvjcb provision` now skips primary cities, and warns if a stray page exists rather than deleting one silently. The validator warns when a content file exists for a primary slug, since that content can never render.

### The three page classes

The distinction is now enforced in code, not just convention:

| Class | Defined by | URL | Content source | Enforced by |
|---|---|---|---|---|
| **Primary city** | `service_areas` item with `is_primary` | `/` (homepage) | `business-config.php` | `lvjcb_get_location_url()` → `/`; `cli.php` skips provisioning; omitted from the areas grid |
| **Secondary location** | any other `service_areas` item | `/service-areas/{slug}/` | `content/locations/{slug}.json` | `cli.php` provisions; `template-location.php` |
| **Service** | `services.cards` item | `/cash-for-junk-cars/{slug}/` | `content/services/{slug}.json` | `cli.php` provisions; `template-service.php` |

### Recommendation for `las-vegas.json` — 363 orphan words

| Option | Assessment |
|---|---|
| **A. Fold into a homepage content model** | **Recommended.** The homepage *is* the Las Vegas page — it already targets that query. Folding the copy in puts real prose behind the site's most valuable term, with no second URL. Requires extending the written-content branch to `front-page.php`, which the contract already supports: the homepage needs the same section types, minus `city`/`state`. |
| **B. Retain, prevented from being silently dead** | **Current state, and the right interim.** The validator warns that the file can never render, so it is visible rather than forgotten. Not a resolution — the prose still reaches nobody. |
| **C. Clear `is_primary`, publish a Las Vegas page** | **Rejected.** Creates `/service-areas/las-vegas/` competing with the homepage for the identical primary query. Both would carry an H1 of the same intent — textbook cannibalisation, and it would undo the rule the other three call sites already follow. |

Option A is deferred to a later phase because it means giving the homepage a content file, which is a contract extension in its own right, not a content edit. Until then B holds and the warning stands.

---

## 6. Migration expectations

**Completed (2026-09-03).** All eleven v1 files were migrated mechanically: `body` split on blank lines into `paragraphs[]`, recovering 3,207 words across 40 sections. Prose was preserved byte-for-byte and verified against the previous commit. No copy was rewritten.

**For any new file:** write v2 directly. `ai/content-generation-prompt.md` emits it.

**Service rendering (closed 2026-09-03).** `template-service.php` previously ignored content files and assembled the config-driven layout, so all four service pages published identical bodies and only `seo_title`, `seo_description`, and `faq` reached a page. It now takes the same written-content branch as `template-location.php`, publishing 1,383 words that already existed in the repository.

**One renderer.** `template-parts/location-written.php` was renamed `template-parts/written-page.php` and generalised. Nothing in it knows whether it is drawing a location or a service — the contract is identical for both, so the caller passes the one thing that differs (the FAQ heading to fall back on). Both templates keep their existing fallback: a page with no content file, or with no `sections`, still renders the generic layout.

---

## 7. Adding a component

First decide its tier (§2a). That decision is the design; the mechanics follow from it.

- **Tier 1 (global)** — changes every written page at once. Requires a new ADR. Add the `get_template_part()` call to `written-page.php` outside the section loop, sourcing data from `business-config.php`. Nothing goes in the schema, because a content file must not be able to declare or suppress it.
- **Tier 3 (opt-in by slug)** — the data stays in config; only placement and subset are the page's choice. Follow all five steps below.
- **Tier 2 (content-driven)** — reserved for genuinely page-specific prose or structure. If the same words would appear on every page, it is not Tier 2, and probably should not exist.

For a Tier 2 or Tier 3 type:

1. Build or identify the template part in `template-parts/sections/`.
2. Add a dispatch case in `template-parts/written-page.php`.
3. Add a definition to `common.schema.json`, register it under `section.select`, and add it to the `type` enum.
4. If it references config by slug, extend `check_slug_references()` in `validate-content.py` so an unknown slug fails.
5. Run `python content-engine/bin/validate-content.py`.

Steps 2 and 3 go together. A type the renderer handles but the schema rejects blocks valid content; a type the schema allows but the renderer does not handle reintroduces exactly the silent-fallthrough bug this contract was written to prevent. Step 4 matters for the same reason — the renderer skips slugs it cannot resolve, so without it a typo deletes a card in silence.

Resist adding a type because it is possible. The test is whether a page needs to say something the contract cannot currently express — not whether a component exists that could be wired up.
