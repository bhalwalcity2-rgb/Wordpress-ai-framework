# Content Generation Prompt Template

Use this prompt in a Claude conversation to batch-generate content files for all location and service pages. Copy the prompt below, fill in the business details from `business-config.php`, and Claude will output JSON files ready to drop into `content/locations/` and `content/services/`.

---

## How to use

1. Open a new Claude conversation
2. Paste the prompt below (with your business details filled in)
3. Claude outputs JSON for each page
4. Save each JSON file to the correct directory
5. Push to GitHub → CI/CD deploys → pages go live with full content

You can generate pages in batches (e.g., 5 locations at a time) to avoid hitting message limits.

---

## The Prompt

```
You are a local SEO content writer for a junk car buying business. Generate structured JSON content files for WordPress pages. Each file powers a full page with SEO-optimized content, internal links, and local FAQ.

## Business Details

- Business Name: [FROM CONFIG: business_name]
- Phone: [FROM CONFIG: phone_display]
- City/Region: [FROM CONFIG: primary city]
- State: [FROM CONFIG: state]
- Address: [FROM CONFIG: address]

## All Locations (for internal linking)

[LIST ALL service_areas items with city, state, slug]

## All Services (for internal linking)

[LIST ALL services cards with heading, slug]

## Content Rules

1. Write naturally — no keyword stuffing, no filler, no "look no further" clichés
2. Every page must be locally specific — mention actual neighborhoods, landmarks, or geography unique to that city/area
3. Target 800-1200 words total across all sections
4. Include 4-6 local FAQ items per page (unique to that location, not copy-pasted)
5. Internal links: link to 3-5 nearby locations and all services
6. SEO title format: "[Primary Keyword] in [City, State] | {business}"
7. SEO description: 150-155 characters, include city name and a call to action
8. Do NOT use the phrases: "look no further", "hassle-free", "we've got you covered", "peace of mind", "at the end of the day"
9. Write from the business's perspective using "we" and "our"
10. Phone number appears naturally in the "how it works" section, nowhere else — always as the token `{phone}`
11. NEVER write the business name or phone number as a literal, in any field, including `seo_title` and `seo_description`. Write `{business}` and `{phone}`; `inc/content-loader.php` resolves both from `business-config.php` at load. A literal is a NAP defect and `content-engine/bin/validate-content.py` rejects the file. This rule exists because literal values did reach production and shipped a phone number that was not the business's.

## Output Format

The canonical contract is `content-engine/config/schema/location.schema.json`, documented in `docs/architecture/content-schema.md`. Output must validate against it:

```
python content-engine/bin/validate-content.py kadence-child-lvjcb
```

Output one JSON file per page:

{
  "slug": "city-slug",
  "city": "City Name",
  "state": "ST",
  "seo_title": "We Buy Junk Cars in City Name, ST | {business}",
  "seo_description": "Up to 160 chars, city name and a CTA. Use {phone}, never a literal number.",
  "hero_heading": "We Buy Junk Cars in City Name",
  "hero_description": "2-3 sentences, locally specific, mentions free towing and same-day service",
  "sections": [
    {
      "type": "content",
      "eyebrow": "Optional short label",
      "heading": "Section heading with city name",
      "intro": "Optional single supporting sentence.",
      "paragraphs": [
        "One string per paragraph. Plain prose only — no markup, no URLs.",
        "Templates escape this, link city mentions, and turn {phone} into a tel: link."
      ]
    }
  ],
  "faq_heading": "Questions [City] Residents Ask Before Selling",
  "faq": [
    {
      "question": "Locally specific question about selling junk cars in [City]?",
      "answer": "Direct answer, 1-3 sentences."
    }
  ],
  "final_cta": {
    "heading": "Closing call to action naming the city",
    "intro": "One or two sentences."
  }
}

**`sections[].type` must be one of `content`, `steps`, `cta`, `areas`, `services`.** Any other value fails validation. See `common.schema.json` for the shape of each, and `content/locations/henderson.json` for a worked example.

`areas` and `services` name entries by **slug only** — city, state, heading, description, icon, and URL all resolve from `business-config.php` at render. Never restate them. A slug that is not in the config fails validation. An optional `note` per entry is the one place the page speaks about that area or service in its own words:

```
{ "type": "services", "heading": "Our Services in [City]",
  "items": [ { "slug": "sedans", "note": "The most common pickup here." } ] }
```

**Do NOT write these — they render automatically on every page** (Tier 1, ADR-0004): the trust strip, recently-purchased vehicles, testimonials/reviews, contact information, and the closing CTA. Their data belongs to `business-config.php`. Never invent reviews, ratings, review counts, or vehicle records — `ai/memory/master-website-workflow.md` Phase 07 prohibits it outright.

There is deliberately no "why choose us" section type. If the page needs that argument, make it in prose in a `content` section, in this page's own words.

**DEPRECATED — do not emit:**

| Key | Why |
|---|---|
| `sections[].body` | The superseded v1 shape. A `body` string reaches no renderer; the section publishes as an empty heading. Use `paragraphs` (a list). |
| `internal_links` | No PHP reads it. Links are generated at render time. |

## Pages to Generate

Generate content for these LOCATION pages:

[LIST THE LOCATIONS YOU WANT GENERATED]

---

For SERVICE pages, use `content-engine/config/schema/service.schema.json` — the same structure without `city` and `state`:

{
  "slug": "service-slug",
  "seo_title": "Service Heading in [Region] | {business}",
  "seo_description": "Up to 160 chars. Use {phone}, never a literal number.",
  "hero_heading": "Service Heading",
  "hero_description": "2-3 sentences about this service",
  "sections": [
    {
      "type": "content",
      "heading": "Section heading",
      "paragraphs": [
        "One string per paragraph."
      ]
    }
  ],
  "faq": [
    {
      "question": "Question about this service?",
      "answer": "Answer"
    }
  ]
}

> Note: `template-service.php` does not yet read these sections — it renders the config-driven layout, so currently only `seo_title`, `seo_description`, and `faq` from a service file reach a page. Write the sections anyway; they are contract-valid and will render once that template is wired up.

Generate content for these SERVICE pages:

[LIST THE SERVICES YOU WANT GENERATED]
```

---

## Example: Las Vegas Junk Car Buyers

Here's the filled-in version for the current business:

> These values are copied from `wordpress/themes/kadence-child-lvjcb/inc/business-config.php`. Re-read that file before using them — it is the source of truth, and this block is only a convenience copy. Never invent business data to fill a gap here.

```
Business Name: First Choice Junk Car
Phone: (866) 748-3697
City/Region: Las Vegas
State: NV
Address: 4820 W Sahara Ave, Las Vegas, NV 89102

All Locations:
- Las Vegas, NV (slug: las-vegas)
- Henderson, NV (slug: henderson)
- North Las Vegas, NV (slug: north-las-vegas)
- Summerlin, NV (slug: summerlin)
- Spring Valley, NV (slug: spring-valley)
- Paradise, NV (slug: paradise)
- Enterprise, NV (slug: enterprise)
- Boulder City, NV (slug: boulder-city)

All Services:
- Sedans & Coupes (slug: sedans)
- Trucks & SUVs (slug: trucks-suvs)
- Accident & Storm-Damaged (slug: damaged)
- Non-Running & Mechanical Failure (slug: non-running)

Generate content for ALL 8 location pages and ALL 4 service pages.
```

---

## After Generation

1. Save each JSON file:
   - Location files → `content/locations/{slug}.json`
   - Service files → `content/services/{slug}.json`
2. Commit and push to GitHub
3. CI/CD deploys automatically
4. WP-CLI provisioning creates/updates all pages

To add more locations later: add to `business-config.php`, generate the content file, push. The new page appears automatically.
