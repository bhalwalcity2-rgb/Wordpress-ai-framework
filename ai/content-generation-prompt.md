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
6. SEO title format: "[Primary Keyword] in [City, State] | [Business Name]"
7. SEO description: 150-155 characters, include city name and a call to action
8. Do NOT use the phrases: "look no further", "hassle-free", "we've got you covered", "peace of mind", "at the end of the day"
9. Write from the business's perspective using "we" and "our"
10. Phone number appears naturally in the "how it works" section, nowhere else

## Output Format

Output one JSON file per page. Use this exact structure:

{
  "slug": "city-slug",
  "city": "City Name",
  "state": "ST",
  "seo_title": "We Buy Junk Cars in City Name, ST | Business Name",
  "seo_description": "150 chars max with city name and CTA",
  "hero_heading": "We Buy Junk Cars in City Name",
  "hero_description": "2-3 sentences, locally specific, mentions free towing and same-day service",
  "sections": [
    {
      "type": "content",
      "heading": "Section heading with city name",
      "body": "2-3 paragraphs of locally specific content. Use \\n\\n between paragraphs."
    }
  ],
  "faq": [
    {
      "question": "Locally specific question about selling junk cars in [City]?",
      "answer": "Direct answer, 1-3 sentences."
    }
  ],
  "internal_links": {
    "services": [
      {"label": "Service Name", "slug": "service-slug"}
    ],
    "nearby_locations": [
      {"label": "Nearby City", "slug": "nearby-slug"}
    ]
  }
}

## Pages to Generate

Generate content for these LOCATION pages:

[LIST THE LOCATIONS YOU WANT GENERATED]

---

For SERVICE pages, use this structure instead:

{
  "slug": "service-slug",
  "seo_title": "Service Heading in [Region] | Business Name",
  "seo_description": "150 chars max",
  "hero_heading": "Service Heading",
  "hero_description": "2-3 sentences about this service",
  "sections": [
    {
      "type": "content",
      "heading": "Section heading",
      "body": "Content paragraphs"
    }
  ],
  "faq": [
    {
      "question": "Question about this service?",
      "answer": "Answer"
    }
  ],
  "internal_links": {
    "services": [
      {"label": "Other Service", "slug": "other-slug"}
    ],
    "locations": [
      {"label": "City, ST", "slug": "city-slug"}
    ]
  }
}

Generate content for these SERVICE pages:

[LIST THE SERVICES YOU WANT GENERATED]
```

---

## Example: Las Vegas Junk Car Buyers

Here's the filled-in version for the current business:

```
Business Name: Las Vegas Junk Car Buyers
Phone: (702) 555-0134
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
