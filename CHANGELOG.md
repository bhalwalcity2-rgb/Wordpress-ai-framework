# Changelog

All notable changes to the WordPress AI Framework are documented in this file.

The format follows the structure defined in `ai/DEPLOYMENT_STANDARDS.md` §5.

---

## [0.10.0] — 2026-09-19

### Changed
- Homepage body for the Las Vegas Junk Car Buyers theme is now managed in
  WordPress with Elementor Flexbox Containers. Routine homepage content,
  image, CTA, FAQ, and layout updates no longer require editing or deploying
  `front-page.php`.
- The child theme now acts as the homepage shell and continues to provide the
  existing header, footer, site tokens, and performance controls.

### Added
- `ai/decisions/elementor-usage-home.md` documents the owner-approved
  Elementor exception and its performance constraints.
- Elementor Google Font loading is disabled so the homepage uses the child
  theme's existing typography.

---

## [0.9.0] — 2026-09-06

### Added
- **Publish gate** (ADR-0008) — `publish-gate.py`, the only sanctioned path into `wordpress/themes/{theme}/content/`. Re-runs every prerequisite at promotion time rather than trusting a status field, requires an approval record naming the exact draft hash, writes an audit record, and never modifies or deletes the source draft.
- **Live verification** — `verify-live.py`. Semantic DOM-level comparison of the promoted artifact against the rendered page: title, description, canonical, single H1, section headings, promoted prose, word-count floor, FAQ, internal links, JSON-LD types, token resolution and forbidden legacy literals. Watches specifically for content-loader fallback and empty heading sections.
- **Content provenance** — `verify-content-provenance.py` closes the one bypass a schema check cannot see: a content file edited directly and committed. Every theme content file must trace to a promotion record or the recorded pre-gate migration allowlist.
- `lib/promotion.py` (destination safety, URL rules mirroring the PHP, approval binding), `selftest-publish-gate.py` (19 checks), `config/migration-allowlist.json`, `docs/architecture/publish-gate.md`.
- Source registry gained `waif-business-config` — business-provided claims now cite an actual source instead of being sourceless.

### Changed
- `validate.yml` gained three steps inside the existing reusable workflow: content provenance, research/brief/draft validation, and the publish-gate self-test. Deployment continues to depend on the workflow, so a provenance failure blocks the deploy.
- All `content-engine/bin` scripts reconfigure stdout to replace unencodable characters. A validator that crashed formatting its own em dash was masking ten real self-test results on Windows.

### Behaviour
- Nothing has been promoted: no approved angle exists, so the gate refuses every page.
- Live verification against production: **1 PASS, 1 SKIP, 10 FAIL** — every failure a Phase 0–3A fix that is committed locally but not yet deployed. The verifier independently rediscovered the defects those phases fixed.

### Notes
- Promotion and deployment stay separate. The gate changes the repository; the existing GitHub Actions/FTPS pipeline ships it. FTPS, WP-CLI and the hosting model are untouched.
- The migration allowlist matches on path, so an allowlisted file can still be hand-edited without detection. Stated openly in ADR-0008; it shrinks to zero as pages are properly promoted.

---

## [0.8.0] — 2026-09-05

### Added
- **Production pipeline stages 5–8** (ADR-0007): `create-content-brief.py`, `validate-content-brief.py`, `write-content.py`, `validate-draft.py`, plus `selftest-pipeline.py`. Standard library only.
- `production-brief.schema.json` — the writer's full instruction contract: query model, entity relationships with evidence levels, local model, differentiation, information gain, section plan, FAQ plan, claim budget, internal-linking reasons, sibling constraints, writing constraints.
- `draft.schema.json` — canonical v2 content plus generation provenance, coverage map and writer self-check.
- `content-engine/lib/briefing.py` — the page-existence gate.
- `content-engine/brief/production/` (12 briefs) and `content-engine/drafts/`.
- `docs/architecture/content-production.md`.

### Behaviour
- **The gate refuses all 12 pages**: 8 `NO_SUPPORTED_DIFFERENTIATION`, 4 `NO_APPROVED_ANGLE`. Every refusal carries a recommended action. `write-content.py` reports `NO_APPROVED_ANGLE_FOR_END_TO_END_GENERATION`.
- Only `approved` / `used` angles authorise writing. The gate runs at brief time **and again** at write time — a brief is a file, and a file can be edited.
- Sibling pages reach the writer as constraints only, never as text. No section template is supplied; structure is derived per page, because identical structure was the strongest templating signal in the corpus.
- Narrowed claims reach the writer as their **boundary statement**, so the duplicate-title fee arrives as "$20 + $8.25 = $28.25" rather than the "$20 fee" the live page asserts.
- Drafts are confined to `content-engine/drafts/`; the writer raises rather than write elsewhere.

### Validation
- Self-test **13/13** — asserts refusal on the real corpus, a green run on an injected fixture, and rejection of four negative paths. Fixtures are removed in a `finally` block; no page is approved as a side effect.

---

## [0.7.0] — 2026-09-05

### Added
- **External research executed** (Phase 3C-2). `content-engine/sources/registry.json` — 8 sourced entries with URL, publisher, source type, accessed date, exact claims supported, and limitations. Claims cite `source_ids`; citations are never restated.
- Claim contract extended: `verification`, `source_ids`, `claim_boundary`, `contradiction`, `review_by`, `research_locked`.
- Angle lifecycle extended: `proposed → researched → evidence_backed → approved → used`, plus `unsupported` and `retired` with a required `retirement_reason` and `distinctiveness_assessment`.
- Validator now enforces: CONTRADICTED/UNSUPPORTED claims can never be publishable; PARTIALLY_SUPPORTED requires a `claim_boundary`; CONTRADICTED requires the contradiction stated; publishable claims need `source_ids` and a live `review_by`; stale evidence is flagged; cited sources must exist in the registry.
- `research_locked` — curated files survive `bootstrap-research.py --force`. Verified by checksum.

### Findings
- **Henderson's live HOA towing claim is CONTRADICTED.** NRS 116.3102 limits association removal to association property or a road/street/alley/thoroughfare within the community — not the owner's driveway. NRS 116.31031 allows an additional fine per 7-day period after a 14-day cure window, capped at $100 per violation, not "daily fines".
- **"Sell without title if model year 2010 or older" is UNSUPPORTED.** No such provision found in NRS 487, NRS 482 or the DMV title/salvage pages. This is the highest-risk claim on the page and it was *missed entirely* by the Phase 3C-1 extractor.
- **Henderson's angle retired as `unsupported`.** Not merely unproven: NRS 116 is statewide, so the HOA framing is common to every sibling page and cannot differentiate any of them. Its Henderson-specific premise (distinctive HOA density) has no source.
- **Jurisdiction is the one real structural difference** — unincorporated Clark County (Title 11) vs incorporated cities with their own codes. It separates the group, not the pages within it, so the evidence argues for consolidating Paradise, Spring Valley and Enterprise rather than differentiating them.
- Corpus state: **0 approved angles, 0 publishable claims**, 8 `NO_SUPPORTED_DIFFERENTIATION`, 11 high-risk claims still unresolved.

### Fixed
- Claim extraction missed `liens` (the `lien` pattern excluded the plural) and model-year/without-title phrasing, dropping the page's most dangerous legal claim. Henderson's high-risk count went from 1 to 5 after the fix.

---

## [0.6.0] — 2026-09-03

### Added
- **Research and evidence system** (ADR-0006). Six schema-validated artifact types per page — `place/`, `claims/`, `angles/`, `intent/`, `entities/`, `queries/` — that the future writer will be required to consume.
- Seven contracts: `research-common`, `place`, `claim`, `angle`, `intent`, `entities`, `queries`.
- `content-engine/lib/research.py` — one place decides what may be stated as fact, so the bootstrapper and validator cannot drift.
- `bootstrap-research.py` — scaffolds artifacts from the Phase 3B corpus. **Performs no research**; produces a work order of open questions.
- `validate-research.py` — enforces the cross-artifact rules: authoritative sourcing for legal/regulatory claims, unique angle ids, no reuse of an approved angle, no approval without publishable evidence, rejection of angles that collapse under place-name masking.
- 72 research artifacts + `docs/architecture/research-system.md`.

### Findings
- **8 of 12 pages return `NO_SUPPORTED_DIFFERENTIATION`**, each with a required recommendation. A first-class outcome: asked to differentiate twelve pages, a generator will always produce twelve differentiations, so "no supported angle" must be recordable.
- **Henderson: 39 claims, 0 publishable.** Its HOA/compliance concept is held at `proposed` / `PENDING_RESEARCH`, blocked by 4 open questions — hypothesis → research → evidence → approval, per the phase brief. Publication is not evidence.
- Validation: 0 errors, 208 warnings. The warnings are the work queue, not noise.

### Fixed
- `corpus.py` joined heading and body with a space, so sentence splitting produced one run-on pseudo-sentence and claim extraction treated hero headings as assertions. Now joined with sentence terminators.
- Angle grounding selected the entity group with the most members, which picked generic vehicle-condition words every page shares. Now selects by situation priority (`user_problem` > `business_regulatory` > `process` > `attributes_condition`), and flags condition-only grounding as `WEAK GROUNDING`.
- `slugify()` preserved apostrophes, producing claim ids that failed their own pattern — caught by the new validator on its first run.

---

## [0.5.0] — 2026-09-03

### Added
- **Homepage content contract** (ADR-0005). `content/pages/home.json` is optional and **additive** — `front-page.php` keeps its frozen section order and renders these sections into it. No `city`, `state`, or `slug`, so the homepage cannot drift into being a location page. No file is shipped, so the homepage renders exactly as before.
- `template-parts/sections-loop.php` — section dispatch extracted from `written-page.php`, so the homepage and written pages render canonical sections through one code path.
- **Corpus analysis** (`content-engine/bin/analyze-corpus.py`, `lib/textstats.py`, `lib/corpus.py`). Standard library only. Produces retro-briefs, per-page differentiation reports, and a corpus summary with distributions, clusters, claim inventory, internal-link analysis, and a priority matrix.
- 12 retro-briefs and 13 report files, committed so corpus changes show up as reviewable diffs.
- `docs/architecture/content-analysis.md` — method, findings, and the recommended differentiation model.

### Findings
- **The proposed 0.18 Jaccard threshold would have passed every page.** 5-gram overlap maxes at 0.040 on 400–500-word pages that were reworded rather than copied — it measured the instrument, not the corpus. This is why the phase calibrated before choosing a cut-off.
- **The failure mode is not "same page + replace city name."** Vocabulary substitution lift maxes at 0.015. It is *same brief, different words*: structure similarity p50 = 0.857, with `IDENTICAL_STRUCTURE` on 50 of 68 sibling pairs.
- **Information gain now measured on non-local information.** Counting unique phrases scored Paradise 85% "unique" on street names alone. 6 pages are `LOCAL_NAME_SUBSTITUTION_ONLY`.
- Priority: 1 KEEP (henderson), 2 REFINE, 8 REWRITE, 1 CONSIDER REMOVAL (las-vegas orphan).
- **Only Henderson renders internal neighbour links.** The other 11 pages declare 4–5 and render none.
- 257 claims inventoried; **6 high-risk** regulatory claims need sourcing before any rewrite.

### Changed
- `inc/seo.php` — homepage title/description may be overridden by `home.json`, still falling back to config.
- `validate-content.py` — validates `content/pages/` against `home.schema.json`.
- `content-engine/lib/textstats.py` — added vocabulary-based metrics that survive rewording.

---

## [0.4.0] — 2026-09-03

### Fixed
- **Written pages had no social proof.** Giving a page a content file replaced the entire generic layout, so Henderson lost the recently-purchased-vehicles slider and testimonials the generic location page had shown — and Phase 2 extended that loss to four service pages. Both now render on every written page, sourced from `business-config.php`.
- An `areas` or `services` entry naming a slug absent from the config was skipped silently by the renderer, deleting a card with no signal. Now a validation error, including for table cells that link to a service.

### Added
- `sections[].type: "services"` — renders `what-we-buy.php` from `services.cards`. The page chooses placement, subset, and an optional per-entry `note`; every other field resolves from config, so a retired service cannot leave a stale card.
- `ADR-0004` — three-tier page composition: global verified components, content-driven sections, and opt-in-by-slug sections.
- `docs/deployment/provisioning.md` — the two provisioning paths, why the FTPS workflow cannot reach WP-CLI, and the exact steps if SSH turns out to be available.
- `check_slug_references()` in `validate-content.py`.

### Changed
- `written-page.php` renders vehicles and testimonials globally, after the page's own sections and before the FAQ, matching homepage order.
- `docs/architecture/content-schema.md` — three-tier model, the `services` type, the three page classes, the Las Vegas recommendation, and what a future content brief should take from `internal_links`.

### Notes
- **No `why_choose_us` section type**, deliberately. Its content is generic differentiator copy; offering it as one line of JSON would make the templated pattern the path of least resistance. Written pages make that argument in their own prose.
- Testimonials and vehicles are not expressible in a content file. Per-page review lists are how fabricated reviews get written, which `master-website-workflow.md` Phase 07 prohibits.
- FTPS provisioning remains **blocked** — no SSH secret exists and `provision.php` requires an authenticated admin session. System left unchanged.

---

## [0.3.0] — 2026-09-03

### Fixed
- **Service pages ignored their content files.** `template-service.php` assembled the config-driven homepage layout, so all four service pages published identical bodies while 1,383 written words sat unread. They now take the same written-content branch location pages use.
- **Empty final CTA heading.** `written-page.php` passed `final_cta.heading` straight through, but only Henderson defines one — every other page rendered a bare `<h2>` that its own `aria-labelledby` pointed at. Pages without their own closing CTA now fall back to the configured `late_page` banner, and `cta-banner.php` refuses to render without a heading.
- **`/service-areas/las-vegas/` 404.** `inc/cli.php` provisioned a page for the primary city, contradicting `lvjcb_get_location_url()`, `lvjcb_get_service_area_cards()`, and `provision.php`, which all treat the homepage as covering it. Provisioning now skips primary cities and warns if a stray page exists.
- Deployment could not be blocked by validation: `needs:` cannot span workflow files. `validate.yml` is now callable and `deploy-lvjcb.yml` gates on it.

### Changed
- `template-parts/location-written.php` → `template-parts/written-page.php`, generalised to render locations and services from one path. The caller supplies the FAQ heading fallback, so the renderer holds no page-type branching.
- `validate-content.py` warns when a content file exists for a primary city, and no longer reports the primary city as missing content.

### Removed
- `template-parts/sections/content-block.php` and `assets/css/sections/content-block.css` — the only renderer that read the superseded `body` key; unreferenced and never enqueued.
- `template-parts/sections/internal-links.php` and `assets/css/sections/internal-links.css` — never included, never enqueued.
- `lvjcb_render_internal_links()`, `lvjcb_build_location_links()`, `lvjcb_build_service_links()` — zero call sites.

### Notes
- `internal_links` is **retained** as deprecated metadata. It records which services and neighbouring areas relate to each page — an input the internal-linking stage will want. Warns, never fails.
- `provision.php` is **retained**. It is the only thing that creates pages and imports media on the FTPS deployment path, which runs no WP-CLI. Its docblock previously said to delete it; following that would have left that path unable to provision.

---

## [0.2.0] — 2026-09-03

### Fixed
- **Live NAP defect.** Eleven content files carried a business name and phone number belonging to no one — `Las Vegas Junk Car Buyers` and `(702) 555-0134` — against a configured identity of `First Choice Junk Car` / `(866) 748-3697`. Both were served in `<title>` and `<meta name="description">` on eleven production pages. 12 name and 22 phone occurrences replaced with the `{business}` and `{phone}` tokens.
- `business-config.php` — the `about.body` copy named the wrong business, so the source of truth was itself wrong. This string is also the About page's meta description.
- **Content silently not rendering.** Eleven files stored prose in `sections[].body`, which no renderer reads; those sections published as empty headings. Migrated to `paragraphs[]`, recovering 3,207 words across 40 sections. Prose preserved byte-for-byte and verified against the previous commit.
- `validate.yml` linted `kadence-child-framework` but not `kadence-child-lvjcb`, so the theme that actually deploys was never syntax-checked.
- `validate.yml` ran only on `pull_request`, while every commit in this repository's history has gone straight to `main` — the checks had never run.

### Added
- `content-engine/` — content contracts and validation (ADR-0002). Standard library only; no new dependency.
- Canonical v2 JSON Schemas: `location.schema.json`, `service.schema.json`, `common.schema.json` (ADR-0003).
- `content-engine/lib/jsonschema_mini.py` — draft-07 subset validator, since `jsonschema` may not be installed.
- `content-engine/bin/validate-content.py` — schema, NAP-literal, slug, and length checking; wired into CI.
- `lvjcb_resolve_content_tokens()` and `lvjcb_content_error()` in `inc/content-loader.php`.
- `docs/architecture/content-schema.md` — canonical contract, deprecations, migration expectations.
- `ADR-0002` (content-engine directory), `ADR-0003` (v2 schema frozen).

### Changed
- Content files must express business identity as `{business}` / `{phone}`; literals now fail validation.
- Malformed content JSON reports to the error log, the screen under `WP_DEBUG`, and the WP-CLI console instead of silently degrading the page to the templated layout.
- `ai/content-generation-prompt.md` emits the canonical v2 shape and names the deprecated keys.
- `ai/memory/master-website-workflow.md` no longer carries a sample phone number — that placeholder is how a fake number reached production.

### Deprecated
- `internal_links` in content files. No PHP reads it; links are generated at render time. Warns rather than fails so the contract could be frozen without a content sweep.

---

## [0.1.0] — 2026-08-05

### Added
- Initial framework structure: `ai/`, `docs/`, `templates/`, `scripts/`, `wordpress/` directories
- Full AI-facing standards documentation (`PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md`, `SEO_STANDARDS.md`, `KADENCE_STANDARDS.md`, `ELEMENTOR_STANDARDS.md`, `PLUGIN_STANDARDS.md`, `DEPLOYMENT_STANDARDS.md`)
- Convention and quick-reference files under `ai/conventions/` and `ai/memory/`
- Initial Kadence child theme scaffold (`wordpress/themes/kadence-child-framework/`)

### Fixed
- Moved `functions.php` from `templates/` to the child theme root so WordPress can load it
- Replaced placeholder `CHANGELOG.md` and `VERSION` directories with real files
- Removed duplicate `screenshot.png` from the theme's `templates/` directory
