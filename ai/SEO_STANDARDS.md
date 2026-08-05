# SEO_STANDARDS.md

## Document Information

| Field | Value |
|---|---|
| Version | 0.1.0 |
| Status | Active |
| Owner | WordPress AI Framework — Internal Agency |
| Audience | AI Assistants (Claude, ChatGPT, Codex, Gemini, Cursor), Developers, SEO Team |
| Last Updated | 2026-08-01 |
| Priority | Mandatory — every page deployed to production must comply |
| Companion Documents | `PROJECT_CONTEXT.md`, `WORDPRESS_STANDARDS.md`, `CODING_STANDARDS.md` |
| SEO Plugin | Rank Math Pro |
| Indexing Plugin | Instant Indexing |

> **SEO is a development discipline.**
> It is implemented during development, not after launch. Every page, every template, and every component is built with search visibility as a first-class requirement. This document defines how.

---

## 1. SEO Philosophy

### Core Beliefs

Search engine optimization at this agency is not a service bolt-on. It is embedded into the development workflow at the architectural level.

| Belief | Implication |
|---|---|
| SEO starts at the first commit | Page structure, URLs, headings, and schema are defined before content is written |
| Google rewards clarity | Clean markup, logical structure, and fast delivery outperform tricks |
| Local SEO is a system | It is not a set of one-time tasks — it is an ongoing, interconnected system of signals |
| Content must earn its ranking | Thin pages, duplicate content, and keyword stuffing degrade the entire domain |
| Technical SEO is the foundation | No amount of content quality compensates for crawlability failures, slow load times, or broken schema |
| Entities matter more than keywords | Google understands entities, relationships, and context — optimize for meaning, not just strings |

### The Agency SEO Model

```
Technical Foundation
    │
    ├── Crawlability (robots.txt, sitemap, internal links)
    ├── Performance (Core Web Vitals, page speed)
    ├── Security (HTTPS, headers)
    └── Structured Data (schema markup)
         │
         ▼
On-Page Optimization
    │
    ├── Heading hierarchy
    ├── Metadata (title, description, OG)
    ├── Content quality and depth
    ├── Image optimization
    └── Internal linking
         │
         ▼
Local SEO System
    │
    ├── Google Business Profile
    ├── Location pages
    ├── Service area pages
    ├── NAP consistency
    ├── Citations and backlinks
    └── Reviews and reputation
         │
         ▼
Authority Building
    │
    ├── Topical authority
    ├── E-E-A-T signals
    ├── Entity optimization
    ├── Semantic content clusters
    └── Link acquisition
```

Every project moves through these layers in order. Technical SEO is never skipped.

---

## 2. Technical SEO Standards

### Site-Level Requirements

| Requirement | Standard |
|---|---|
| Protocol | HTTPS enforced — HTTP 301 redirects to HTTPS |
| WWW consistency | Choose one (www or non-www) and 301 redirect the other |
| Trailing slash | Choose one pattern and enforce consistently |
| Site speed | PageSpeed Insights mobile score ≥ 80 (target ≥ 90) |
| Mobile rendering | Fully responsive — Google uses mobile-first indexing |
| Crawl errors | Zero 5xx errors; zero unintentional 4xx errors |
| Render blocking | No CSS or JS blocking above-the-fold render |
| HSTS | Strict-Transport-Security header enabled |
| Clean HTML | Valid, semantic HTML — no parsing errors in critical elements |

### Crawlability

Google must be able to discover, crawl, render, and index every important page.

| Check | Standard |
|---|---|
| Internal links | Every indexable page is linked from at least one other page |
| Orphan pages | Zero orphan pages — every page is reachable through site navigation or content links |
| Redirect chains | Maximum one redirect hop; never chain redirects |
| Soft 404s | Pages returning 200 status but showing error content must be fixed or properly 404'd |
| JavaScript rendering | Critical content must be in the initial HTML, not exclusively rendered by JavaScript |
| Pagination | Use proper `rel="next"` and `rel="prev"` where applicable |
| Crawl budget | Block non-essential paths (admin, feeds, search results) from crawling |

### Robots.txt

Located at the site root: `https://domain.com/robots.txt`

```
User-agent: *
Allow: /
Disallow: /wp-admin/
Disallow: /wp-login.php
Disallow: /cart/
Disallow: /checkout/
Disallow: /my-account/
Disallow: /?s=
Disallow: /search/
Disallow: /*?*utm_
Disallow: /*?*fbclid
Disallow: /wp-json/

# Allow CSS and JS for rendering
Allow: /wp-content/uploads/
Allow: /wp-content/themes/
Allow: /wp-content/plugins/
Allow: /wp-includes/

# Sitemap
Sitemap: https://domain.com/sitemap_index.xml
```

| Rule | Detail |
|---|---|
| Never block CSS or JS | Google needs these to render the page |
| Block search result pages | Internal search creates infinite crawl paths |
| Block UTM parameters | Prevents duplicate content from tracked URLs |
| Block wp-admin | No reason for search engines to crawl the admin |
| Include sitemap reference | Helps crawlers find the XML sitemap |
| Allow AI crawlers selectively | Block scraper bots; allow major AI crawlers (GPTBot, ClaudeBot, GoogleOther) based on project needs |

### XML Sitemap

Generated by Rank Math Pro. Configuration:

| Setting | Standard |
|---|---|
| Auto-generate | Enabled |
| Include posts | Yes — published posts only |
| Include pages | Yes — published, indexable pages only |
| Include images | Yes |
| Exclude noindex pages | Automatic — Rank Math handles this |
| Max URLs per sitemap | 1000 (Rank Math default) |
| Submission | Submit to Google Search Console and reference in robots.txt |
| Update frequency | Automatic on content changes |

Pages that must never appear in the sitemap: thank-you pages, landing pages with `noindex`, staging URLs, author archives (unless used), date-based archives.

### Canonical Tags

| Rule | Standard |
|---|---|
| Default | Every page has a self-referencing canonical: `<link rel="canonical" href="https://domain.com/page-slug/">` |
| Duplicate content | Point all duplicate or near-duplicate URLs to the canonical version |
| Pagination | Page 1 of a paginated set is the canonical; subsequent pages are self-canonical |
| URL parameters | Canonical always points to the clean URL without query parameters |
| Protocol and domain | Canonical must include `https://` and the chosen www/non-www variant |
| Trailing slash | Canonical must match the site's trailing slash convention |

### Breadcrumbs

| Standard | Detail |
|---|---|
| Implementation | Rank Math breadcrumbs or Kadence theme breadcrumbs |
| Schema | `BreadcrumbList` JSON-LD generated automatically |
| Structure | Home → Parent Page → Current Page |
| Display | Visible on all interior pages (not homepage) |
| Separator | `>` or `›` — consistent across all pages |

---

## 3. On-Page SEO Standards

### Heading Standards

| Rule | Standard |
|---|---|
| H1 count | Exactly one H1 per page |
| H1 content | Contains the primary keyword naturally; describes the page topic |
| H1 length | 20–70 characters |
| Heading hierarchy | Strictly sequential: H1 → H2 → H3 → H4 (never skip levels) |
| H2 usage | Major content sections — each H2 targets a secondary keyword or subtopic |
| H3 usage | Subsections within an H2 block |
| H4–H6 | Rare — used only for deeply nested content structures |
| Keyword placement | Primary keyword in H1; secondary keywords in H2s; related terms in H3s |
| Styling | Never use heading tags for visual styling — use CSS classes instead |

```
H1: Junk Car Removal in Philadelphia
  H2: How Our Junk Car Removal Works
    H3: Step 1 — Get an Instant Offer
    H3: Step 2 — Schedule Free Pickup
    H3: Step 3 — Get Paid on the Spot
  H2: Service Areas in Philadelphia
    H3: North Philadelphia
    H3: South Philadelphia
    H3: West Philadelphia
  H2: Why Choose Us for Junk Car Removal
  H2: Frequently Asked Questions
    H3: How much is my junk car worth?
    H3: Do you pick up cars with no title?
```

### Metadata Standards

#### Title Tags

| Rule | Standard |
|---|---|
| Length | 50–60 characters (max 60 to avoid truncation) |
| Structure | `Primary Keyword | Brand` or `Primary Keyword - City | Brand` |
| Uniqueness | Every page must have a unique title tag |
| Keyword placement | Primary keyword as close to the beginning as possible |
| Brand | Append brand name at the end, separated by `|` or `-` |
| No keyword stuffing | Maximum 2 keyword variants per title |

**Patterns by page type:**

| Page Type | Title Pattern | Example |
|---|---|---|
| Homepage | `Primary Service in City | Brand` | `Junk Car Buyers in Philadelphia | BrandName` |
| Service page | `Service Name in City | Brand` | `Junk Car Removal in Philadelphia | BrandName` |
| Location page | `Service in Location | Brand` | `Junk Car Buyers in Camden NJ | BrandName` |
| Blog post | `Post Title | Brand` | `How to Sell a Car Without a Title | BrandName` |
| About page | `About Brand - City Service Provider` | `About BrandName - Philadelphia Junk Car Buyers` |
| Contact page | `Contact Brand - City, State` | `Contact BrandName - Philadelphia, PA` |

#### Meta Descriptions

| Rule | Standard |
|---|---|
| Length | 140–160 characters (max 160) |
| Content | Action-oriented, includes primary keyword, includes CTA or value proposition |
| Uniqueness | Every page must have a unique meta description |
| CTA inclusion | End with a call to action: "Call today", "Get a free quote", "Schedule now" |
| No quotes | Avoid quotation marks — they can cause truncation in SERPs |

### URL Standards

| Rule | Standard |
|---|---|
| Format | Lowercase, hyphen-separated, descriptive |
| Length | As short as possible while remaining descriptive — under 75 characters |
| Keywords | Include primary keyword in the URL slug |
| Stop words | Remove unnecessary stop words (a, the, in, of, and) unless they aid readability |
| Numbers | Avoid dates or numbers that will become outdated |
| Nesting | Maximum 2 levels deep: `/service/sub-service/` |
| Trailing slash | Consistent — match site-wide convention |
| Special characters | No underscores, spaces, uppercase, or encoded characters |

**URL patterns:**

| Page Type | URL Pattern | Example |
|---|---|---|
| Service page | `/service-name/` | `/junk-car-removal/` |
| Location page | `/service-area/city-name/` | `/service-area/camden-nj/` |
| Blog post | `/blog/post-title/` | `/blog/sell-car-without-title/` |
| About | `/about/` | `/about/` |
| Contact | `/contact/` | `/contact/` |

---

## 4. Local SEO Standards

### NAP Consistency

| Element | Rule |
|---|---|
| Business Name | Identical everywhere — website, GMB, citations, directories |
| Address | Identical format everywhere — same abbreviations, suite numbers, formatting |
| Phone | Primary phone number consistent across all platforms |
| Variations | Zero variations. One canonical version of NAP used across the entire web |

NAP inconsistencies are one of the most common Local SEO failures. Every citation, directory listing, and on-site reference must match exactly.

### Google Business Profile Standards

| Element | Standard |
|---|---|
| Business name | Exact legal business name — no keyword stuffing |
| Primary category | Most specific category available |
| Secondary categories | Up to 9 additional relevant categories |
| Description | 750 characters; include primary keywords naturally; describe services and areas |
| Address | Matches website and all citations exactly |
| Phone | Primary business phone — must match website |
| Website URL | Homepage URL |
| Hours | Accurate and updated — including holiday hours |
| Service area | Defined with specific cities/regions; max 20 areas |
| Services | All services listed with descriptions |
| Products | Listed if applicable |
| Photos | Minimum 10 high-quality photos; cover, logo, interior, exterior, team, work samples |
| Posts | Minimum 1 post per week — offers, updates, events |
| Reviews | Active review generation strategy; respond to all reviews within 48 hours |
| Q&A | Seed 5-10 common questions with accurate answers |
| Attributes | All relevant attributes filled |

### Review Strategy

| Rule | Standard |
|---|---|
| Platform priority | Google first, then Yelp, then industry-specific |
| Response time | Within 48 hours for all reviews |
| Positive reviews | Thank the reviewer, mention the service, include location naturally |
| Negative reviews | Acknowledge, apologize, offer resolution offline — never argue |
| Review velocity | Consistent flow preferred over bursts |
| Review links | Create a short URL for easy review requests |
| Never incentivize | No discounts, gifts, or payments for reviews — violates Google guidelines |

---

## 5. Service Pages

Service pages are the primary conversion and ranking pages for the business.

### Structure

```
Hero Section
    H1: Service Name in City
    Subheading: Value proposition
    Primary CTA button

Service Description
    H2: About [Service Name]
    Detailed description (300-500 words)
    Naturally include primary and secondary keywords

How It Works
    H2: How [Service Name] Works
    H3: Step 1 / Step 2 / Step 3
    Clear process explanation

Service Areas
    H2: [Service Name] Service Areas
    List of areas served with links to location pages

Why Choose Us
    H2: Why Choose [Brand] for [Service Name]
    Trust signals, differentiators, experience

Testimonials
    H2: What Our Customers Say
    2-4 real reviews with name, location

FAQ Section
    H2: Frequently Asked Questions
    5-8 questions with FAQPage schema

CTA Band
    Final call to action with phone number and button
```

### Content Requirements

| Element | Standard |
|---|---|
| Word count | Minimum 800 words; target 1200–1500 for competitive markets |
| Primary keyword density | 1–2% — natural usage, not forced |
| Secondary keywords | 3–5 related terms woven into content |
| Internal links | Minimum 3 links to other service pages, location pages, or blog posts |
| External links | 0–2 links to authoritative sources where relevant |
| Images | Minimum 2 relevant images with optimized alt text |
| CTA count | Minimum 2 — one above the fold, one at the bottom |
| Schema | Service schema + FAQPage schema + LocalBusiness or Organization reference |

---

## 6. Location Pages

Location pages target specific geographic areas for Local SEO.

### Structure

```
Hero Section
    H1: [Service Name] in [City/Neighborhood]
    Subheading with location-specific value prop
    Primary CTA

Local Introduction
    H2: [Service] in [Location]
    250-400 words of unique, location-specific content
    Mention local landmarks, neighborhoods, geography

Services Available
    H2: Our Services in [Location]
    List of services with links to service pages

Service Process
    H2: How It Works in [Location]
    Same process, localized language

Testimonials
    Location-specific reviews when available

Also Serving / Nearby Areas
    H2: Also Serving Nearby Areas
    Links to adjacent location pages

FAQ Section
    H2: [Location] FAQs
    4-6 location-specific questions with FAQPage schema

CTA Band
    Phone number, CTA button
```

### Content Rules

| Rule | Standard |
|---|---|
| Unique content | Every location page must have unique content — no copy/paste between locations |
| Local references | Mention local landmarks, highways, neighborhoods, zip codes naturally |
| Word count | Minimum 500 words of unique content per location page |
| Thin content prevention | Never create location pages with only the city name swapped — Google penalizes this |
| Internal linking | Link to parent service page, adjacent location pages, and relevant blog posts |
| Schema | LocalBusiness schema with geo-coordinates specific to that location |
| Title tag | `[Service] in [City, State] | Brand` |
| Meta description | Unique, location-specific, includes CTA |
| CTA buttons | Must use consistent agency CTA text and link (see `PROJECT_CONTEXT.md` for CTA rules) |

### Location Page Taxonomy

```
/service-area/
    /service-area/philadelphia-pa/
    /service-area/camden-nj/
    /service-area/cherry-hill-nj/
    /service-area/north-philadelphia/
```

---

## 7. Homepage SEO

| Element | Standard |
|---|---|
| H1 | Primary service + primary city: "Junk Car Buyers in Philadelphia" |
| Title tag | `Primary Service in City | Brand Name` |
| Meta description | Brand value proposition + primary service + CTA |
| Schema | Organization or LocalBusiness (primary entity) + WebSite schema with SearchAction |
| Content | Minimum 500 words visible on the homepage |
| Service links | Links to all primary service pages |
| Area links | Links to primary location/service area pages |
| Trust signals | Reviews count, years in business, certifications |
| Internal links | Links to 5+ interior pages minimum |

---

## 8. Blog SEO

| Element | Standard |
|---|---|
| Purpose | Topical authority, informational keyword targeting, internal link equity distribution |
| Word count | Minimum 1000 words; target 1500–2500 for competitive topics |
| Heading structure | H1 (title) → H2 (major sections) → H3 (subsections) |
| Title tag | `Blog Post Title | Brand` |
| Meta description | Summarize the post value, include target keyword, CTA to read |
| URL | `/blog/descriptive-slug/` |
| Internal links | Minimum 3 links to service pages, location pages, or other blog posts |
| Images | Minimum 1 featured image; additional images as content requires |
| Schema | Article schema with author, datePublished, dateModified |
| Categories | Assign to one primary category |
| Tags | 3–5 relevant tags maximum |
| Publishing cadence | Minimum 2 posts per month for active sites |

### Blog Content Strategy

Blog posts should support the commercial pages, not compete with them:

| Blog Targets | Commercial Targets |
|---|---|
| "How to sell a junk car" (informational) | "Junk car buyers in Philadelphia" (commercial) |
| "Signs your AC needs replacement" (informational) | "HVAC repair in Austin" (commercial) |
| "What to do after a tree falls" (informational) | "Emergency tree removal in Dallas" (commercial) |

The blog builds topical relevance and passes link equity to service and location pages through internal links.

---

## 9. FAQ Standards

### Implementation

- Implement as an accordion or visible Q&A section on the page
- Each FAQ page section requires FAQPage JSON-LD schema
- Questions must be real questions people search for — not fabricated
- Answers must be concise (40–80 words ideal) but complete

### Schema

```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How much is my junk car worth?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Junk car values typically range from $100 to $15,000 depending on the year, make, model, condition, and current scrap metal prices. Get an instant offer by calling us or using our online form."
      }
    }
  ]
}
```

### FAQ Rules

| Rule | Standard |
|---|---|
| Questions per page | 4–8 on service/location pages; up to 15 on dedicated FAQ pages |
| Question format | Natural language — how people actually search |
| Answer length | 40–80 words — concise and complete |
| Keyword usage | Include target keywords naturally in answers |
| Internal links | Include 1–2 internal links within FAQ answers where relevant |
| Unique per page | FAQ questions should be tailored to the specific page — no global FAQ block copied everywhere |
| Schema validation | Validate via Google Rich Results Test before deployment |

---

## 10. Schema Standards

### Required Schema by Page Type

| Page Type | Required Schema Types |
|---|---|
| Homepage | `Organization` or `LocalBusiness`, `WebSite` with `SearchAction` |
| Service page | `Service`, `FAQPage`, `BreadcrumbList` |
| Location page | `LocalBusiness` (with geo), `FAQPage`, `BreadcrumbList` |
| Blog post | `Article`, `BreadcrumbList` |
| About page | `Organization` or `AboutPage`, `BreadcrumbList` |
| Contact page | `LocalBusiness` (with contactPoint), `BreadcrumbList` |
| FAQ page | `FAQPage`, `BreadcrumbList` |

### LocalBusiness Schema

Required fields for every LocalBusiness entity:

```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Business Name",
  "image": "https://domain.com/logo.webp",
  "url": "https://domain.com",
  "telephone": "+1-215-555-0100",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "123 Main Street",
    "addressLocality": "Philadelphia",
    "addressRegion": "PA",
    "postalCode": "19101",
    "addressCountry": "US"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 39.9526,
    "longitude": -75.1652
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "08:00",
      "closes": "18:00"
    }
  ],
  "areaServed": [
    {
      "@type": "City",
      "name": "Philadelphia"
    }
  ],
  "priceRange": "$$",
  "sameAs": [
    "https://www.facebook.com/businessname",
    "https://www.instagram.com/businessname"
  ]
}
```

### Schema Rules

| Rule | Standard |
|---|---|
| Format | JSON-LD only — not Microdata or RDFa |
| Placement | In `<head>` or at the end of `<body>` via `wp_head` or `wp_footer` hook |
| Validation | Every schema block validated via Google Rich Results Test before deployment |
| No fabrication | Never include fake reviews, ratings, or data in schema |
| Consistency | Schema data must match visible page content exactly |
| Tool | Rank Math handles most schema; custom schema added via child theme `schema.php` |

---

## 11. Image SEO

| Standard | Requirement |
|---|---|
| File name | Descriptive, hyphenated, keyword-relevant: `junk-car-removal-philadelphia.webp` |
| Alt text | Descriptive sentence including keyword naturally: "Junk car being towed in Philadelphia" |
| Format | WebP primary; JPEG fallback; PNG only for graphics requiring transparency |
| Compression | Lossy 75–85% quality for photos; lossless for graphics and logos |
| Dimensions | Serve at exact rendered size — never rely on CSS to downscale a 4000px image |
| Lazy loading | All images below the fold: `loading="lazy"` |
| Above-fold images | `loading="eager"` and `fetchpriority="high"` on LCP image |
| Width/height | Always defined to prevent Cumulative Layout Shift |
| File size | Hero images ≤ 150 KB; content images ≤ 100 KB; thumbnails ≤ 30 KB |
| Title attribute | Optional — use only when it adds information beyond the alt text |
| Caption | Include when it provides context (especially for before/after images) |
| Unique images | Avoid generic stock photos — use real work photos, team photos, local images |

---

## 12. Internal Linking Strategy

Internal linking distributes authority, establishes content hierarchy, and helps Google understand topic relationships.

### Link Architecture

```
Homepage (highest authority)
    │
    ├── Service Pages (primary commercial targets)
    │   ├── Link to related service pages (sibling links)
    │   ├── Link to location pages (geographic relevance)
    │   └── Link to supporting blog posts (topical relevance)
    │
    ├── Location Pages (geographic targets)
    │   ├── Link to parent service page
    │   ├── Link to nearby location pages (geographic cluster)
    │   └── Link to relevant blog posts
    │
    └── Blog Posts (informational, authority building)
        ├── Link to relevant service pages (pass authority upward)
        ├── Link to related blog posts (topical clustering)
        └── Link to location pages where relevant
```

### Internal Linking Rules

| Rule | Standard |
|---|---|
| Minimum per page | 3 internal links on every content page |
| Anchor text | Descriptive, keyword-relevant — not "click here" or "read more" |
| Anchor text variety | Vary anchor text — do not use the same exact anchor for the same target across all pages |
| Contextual placement | Links within body content carry more weight than footer or sidebar links |
| Orphan pages | Zero — every page reachable through at least one content link |
| Reciprocal linking | Service ↔ Location pages should link to each other |
| Blog → Commercial | Every blog post links to at least one service or location page |
| Broken links | Zero tolerance — check monthly |
| New page protocol | When a new page is published, add internal links from 3–5 existing relevant pages |

### External Linking

| Rule | Standard |
|---|---|
| Outbound links | 0–2 per page to authoritative, relevant sources |
| Target quality | Government sites, industry associations, educational institutions, authoritative publications |
| Rel attribute | No `nofollow` on editorial outbound links; `nofollow` on sponsored or user-generated links |
| Open in new tab | External links open in a new tab (`target="_blank"` with `rel="noopener"`) |
| Never link to competitors | Do not link to competing businesses or their content |
| Affiliate/sponsored | Always include `rel="sponsored"` or `rel="nofollow"` |

---

## 13. Entity SEO and Semantic SEO

### Entity SEO

Entity SEO focuses on establishing the business as a recognized entity in Google's Knowledge Graph.

| Action | Implementation |
|---|---|
| Consistent entity name | Use the exact same business name across the website, GMB, citations, social profiles, and schema |
| Entity schema | `Organization` or `LocalBusiness` schema on the homepage with `sameAs` linking all official profiles |
| Knowledge Panel | Build toward a Knowledge Panel by establishing consistent entity signals across the web |
| Wikipedia/Wikidata | For established businesses, a Wikidata entry strengthens entity recognition |
| Brand mentions | Unlinked brand mentions on authoritative sites contribute to entity recognition |

### Semantic SEO

Optimize content for topics and intent, not just individual keywords.

| Practice | Implementation |
|---|---|
| Topic clusters | Group related content into clusters with a pillar page (service page) and supporting content (blog posts) |
| NLP-friendly content | Use natural language, complete sentences, and clear definitions |
| Related entities | Mention related entities naturally: for "junk car removal," mention "towing," "scrap metal," "auto salvage," "title transfer" |
| Contextual depth | Cover subtopics comprehensively — Google rewards pages that thoroughly address a query |
| People Also Ask | Target PAA questions as FAQ entries or blog post headings |
| Search intent matching | Match the content format to the search intent: transactional → service page; informational → blog post; navigational → homepage or brand page |

---

## 14. E-E-A-T Standards

Experience, Expertise, Authoritativeness, and Trustworthiness.

| Signal | Implementation |
|---|---|
| **Experience** | Show real work: before/after photos, case studies, project galleries |
| **Experience** | Include customer testimonials with names and locations |
| **Expertise** | Detail credentials, certifications, and years of experience on About page |
| **Expertise** | Write blog content demonstrating deep knowledge of the industry |
| **Authoritativeness** | Earn backlinks from local news, industry sites, and community organizations |
| **Authoritativeness** | Maintain active and optimized Google Business Profile |
| **Trustworthiness** | Display business address, phone number, and contact information prominently |
| **Trustworthiness** | Include privacy policy, terms of service, and business licensing |
| **Trustworthiness** | Use HTTPS, display trust badges, show BBB accreditation if applicable |
| **Trustworthiness** | Respond to all reviews — positive and negative |

---

## 15. Topical Authority

Topical authority is built by comprehensively covering a subject area across multiple pages.

### Building Topical Authority

```
Pillar Page (Service Page)
    "Junk Car Removal in Philadelphia"
    │
    ├── Supporting Post: "How Much Is My Junk Car Worth in 2026?"
    ├── Supporting Post: "How to Sell a Car Without a Title in PA"
    ├── Supporting Post: "What Happens to Junk Cars After They're Towed?"
    ├── Supporting Post: "Junk Car Removal vs. Private Sale: Which Pays More?"
    ├── Location Page: "Junk Car Removal in North Philadelphia"
    ├── Location Page: "Junk Car Removal in Camden NJ"
    └── FAQ Page: "Junk Car Removal FAQs"
```

| Rule | Standard |
|---|---|
| Coverage | Every primary service should have 5–10 supporting content pieces |
| Interlinking | All supporting content links back to the pillar page |
| Pillar → support | Pillar page links to all supporting content |
| Publishing cadence | Build topical clusters over time — not all at once |
| Content depth | Each supporting piece is 800+ words and targets a specific long-tail query |
| No cannibalization | Each page targets a distinct keyword — no two pages compete for the same query |

---

## 16. Open Graph and Twitter Cards

### Open Graph Tags

Required on every page:

```html
<meta property="og:title" content="Junk Car Removal in Philadelphia | BrandName">
<meta property="og:description" content="Get top dollar for your junk car. Free pickup, instant payment. Call now.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://domain.com/junk-car-removal/">
<meta property="og:image" content="https://domain.com/images/junk-car-removal-og.webp">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="en_US">
<meta property="og:site_name" content="BrandName">
```

### Twitter Card Tags

```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Junk Car Removal in Philadelphia | BrandName">
<meta name="twitter:description" content="Get top dollar for your junk car. Free pickup, instant payment.">
<meta name="twitter:image" content="https://domain.com/images/junk-car-removal-og.webp">
```

### OG Image Standards

| Standard | Requirement |
|---|---|
| Dimensions | 1200×630 pixels |
| Format | WebP or JPEG |
| File size | Under 300 KB |
| Content | Brand logo, page title text, relevant imagery |
| Default | Every site must have a default OG image used when page-specific images are not set |

---

## 17. Indexing Workflow

### New Page Indexing Process

```
1. Page passes QA checklist
2. Verify schema via Google Rich Results Test
3. Submit URL via Instant Indexing plugin (Google Indexing API)
4. Verify in Google Search Console → URL Inspection
5. Add internal links from 3–5 existing pages
6. Monitor indexing status for 7 days
7. If not indexed after 7 days → re-submit and investigate
```

### Rank Math Configuration

| Setting | Value |
|---|---|
| Global meta | Configured per page type |
| Schema | Enabled — configure per page type |
| Sitemap | Enabled — auto-generated |
| Breadcrumbs | Enabled |
| 404 monitor | Enabled |
| Redirections | Enabled — manage all redirects through Rank Math |
| Analytics | Connected to Google Search Console and Analytics |
| Instant Indexing | Enabled — connected to Indexing API |
| Social meta | Open Graph and Twitter Cards enabled |
| Local SEO module | Enabled — LocalBusiness schema configured |

### Rank Math Score

| Threshold | Action |
|---|---|
| Score ≥ 80 | Page can be published |
| Score 60–79 | Review and fix issues before publishing |
| Score < 60 | Do not publish — address all flagged issues |

### Instant Indexing Workflow

| Step | Detail |
|---|---|
| Install | Instant Indexing plugin installed and configured with Google Indexing API credentials |
| Auto-submit | Enabled for posts and pages on publish/update |
| Manual submit | Available for bulk submission of new location pages or content batches |
| Rate limits | Google allows 200 requests per day — batch accordingly |
| Monitor | Check Search Console for indexing status within 48 hours |

### Google Search Console Workflow

| Task | Frequency |
|---|---|
| Check index coverage report | Weekly |
| Review crawl errors | Weekly |
| Monitor Core Web Vitals | Weekly |
| Check manual actions | Monthly |
| Review search performance | Weekly |
| Submit new sitemaps | When major content is added |
| URL inspection for new pages | On publish |
| Review mobile usability | Monthly |

---

## 18. Core Web Vitals for SEO

Core Web Vitals directly affect search rankings. These are not just performance metrics — they are SEO metrics.

| Metric | SEO Impact | Target |
|---|---|---|
| LCP (Largest Contentful Paint) | Slow LCP = lower rankings in competitive SERPs | ≤ 2.5s (target ≤ 1.5s) |
| INP (Interaction to Next Paint) | Poor interactivity signals low page quality | ≤ 200ms (target ≤ 100ms) |
| CLS (Cumulative Layout Shift) | Layout shifts frustrate users and increase bounce | ≤ 0.1 (target ≤ 0.05) |

### CWV Optimization for SEO

| Optimization | Implementation |
|---|---|
| LCP improvement | Optimize hero image (WebP, compressed, preloaded); reduce server response time; inline critical CSS |
| INP improvement | Defer non-essential JS; minimize main thread work; break up long tasks |
| CLS prevention | Set explicit dimensions on images/embeds; avoid injecting content above existing content; preload fonts |
| Measurement | Google PageSpeed Insights (lab data) + Search Console CWV report (field data) |
| Field data priority | Field data from real users (CrUX) is what Google uses for ranking — prioritize field data over lab data |

---

## 19. SEO QA Checklist

Every page must pass this checklist before deployment.

### Technical

- [ ] Page loads over HTTPS
- [ ] No mixed content warnings
- [ ] Page returns 200 status code
- [ ] Canonical tag present and correct
- [ ] Page is in XML sitemap
- [ ] No `noindex` tag (unless intentional)
- [ ] Breadcrumbs display correctly
- [ ] No broken internal links
- [ ] No redirect chains

### On-Page

- [ ] H1 present — exactly one, contains primary keyword
- [ ] Heading hierarchy is logical (H1 → H2 → H3)
- [ ] Title tag unique, ≤ 60 characters, contains primary keyword
- [ ] Meta description unique, ≤ 160 characters, contains CTA
- [ ] URL is short, descriptive, contains keyword
- [ ] Minimum 3 internal links
- [ ] Images have descriptive alt text
- [ ] Content meets minimum word count for page type
- [ ] No thin or duplicate content
- [ ] Primary keyword used naturally at 1–2% density

### Schema

- [ ] Appropriate schema type for page type
- [ ] Schema validates in Google Rich Results Test
- [ ] FAQPage schema present (if FAQ section exists)
- [ ] LocalBusiness schema includes geo-coordinates (location pages)
- [ ] BreadcrumbList schema present
- [ ] Schema data matches visible page content

### Local SEO

- [ ] NAP matches GMB exactly
- [ ] Location-specific content is unique (not templated)
- [ ] Internal links to adjacent location pages
- [ ] Internal links to parent service page
- [ ] CTA buttons use correct text and link

### Performance

- [ ] PageSpeed Insights mobile ≥ 80
- [ ] LCP ≤ 2.5s
- [ ] CLS ≤ 0.1
- [ ] Images are WebP and compressed
- [ ] Hero image is preloaded with `fetchpriority="high"`
- [ ] No render-blocking JavaScript

### Social

- [ ] Open Graph tags present (title, description, image)
- [ ] OG image is 1200×630
- [ ] Twitter Card tags present

---

## 20. AI SEO Rules

### Before Any SEO Task

1. Read this document (`SEO_STANDARDS.md`) in full
2. Read `PROJECT_CONTEXT.md` for agency context and CTA rules
3. Check existing pages for established patterns
4. Verify NAP data matches across all references

### Mandatory Rules for AI Assistants

| Rule | Detail |
|---|---|
| Never publish without schema | Every page type requires its defined schema |
| Never publish without metadata | Title tag and meta description are mandatory |
| Never create thin location pages | Every location page needs 500+ words of unique content |
| Never duplicate content across pages | Content must be written uniquely for each page |
| Never skip internal linking | Every page gets minimum 3 internal links |
| Never stuff keywords | Natural language only — 1–2% density maximum |
| Never use heading tags for styling | Headings define document structure, not visual appearance |
| Always validate schema | Use Google Rich Results Test before deployment |
| Always check URL format | Lowercase, hyphenated, keyword-inclusive |
| Always match search intent | Informational queries → blog; commercial queries → service pages |
| Always follow CTA conventions | Use the CTA text and link defined in `PROJECT_CONTEXT.md` |
| Never create orphan pages | Link from existing content immediately on publish |

---

## 21. Common SEO Mistakes

| Mistake | Why It Happens | Correct Approach |
|---|---|---|
| Multiple H1 tags | Page builders add H1s inside widgets or sections | Audit heading hierarchy — one H1 per page |
| Duplicate title tags | Copy-pasting pages without updating metadata | Write unique title for every page |
| Duplicate meta descriptions | Same as above | Write unique description for every page |
| Thin location pages | Swapping only the city name across identical templates | Write unique 500+ word content per location |
| Missing schema markup | Treated as optional or "will add later" | Schema is mandatory — add during development |
| Broken internal links | Pages deleted or URLs changed without redirects | Check links monthly; add redirects for all URL changes |
| Keyword-stuffed titles | Trying to target too many keywords in one title | Maximum 2 keyword variants per title |
| No alt text on images | Uploaded quickly without filling in alt field | Every image gets descriptive alt text before publishing |
| Ignoring mobile rendering | Desktop-first development | Mobile-first — always test on mobile viewport before publishing |
| Missing canonical tags | Assumed to be handled automatically | Verify every page has a correct self-referencing canonical |
| Date-based URLs | WordPress default permalink structure | Use post name structure: `/%postname%/` |
| Publishing without GSC verification | Assumed Google will find the page eventually | Submit via Instant Indexing and verify in Search Console |
| Orphan pages | Published but never linked from other content | Add internal links from 3–5 existing pages immediately |
| Ignoring Core Web Vitals | "SEO is about content, not speed" | CWV is a ranking factor — performance is SEO |
| Inconsistent NAP | Different phone format, address variation, abbreviation differences | One canonical NAP used everywhere — zero variations |
| Schema data mismatch | Schema says one thing, page content says another | Schema must exactly match what is visible on the page |
| Not responding to reviews | Low priority task | Respond within 48 hours — it affects Local SEO rankings |
| Over-optimizing anchor text | Every internal link uses exact match keyword | Vary anchor text — mix branded, descriptive, and keyword-rich |

---

## Document Changelog

| Version | Date | Changes |
|---|---|---|
| 0.1.0 | 2026-08-01 | Initial document — complete SEO methodology defined |