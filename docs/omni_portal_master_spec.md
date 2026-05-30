# Omni Portal Master Specification

## Overview
The **Omni‑Portal** is a high‑traffic, programmatic SEO system designed to generate millions of niche landing pages automatically. It combines hierarchical taxonomies, location trees, live data variables, and reusable post templates to produce SEO‑optimized content at scale. The portal drives traffic and revenue by routing ads based on the `is_restricted_content` flag.

## Core Domains (as defined in `portal_master.md`)
- **Taxonomy System** – hierarchical categories (e.g., niche, health, finance). Each taxonomy has `name`, `slug`, optional `parent_id` and is indexed for fast look‑ups.
- **Location System** – city → district → neighbourhood hierarchy with the same self‑referencing structure.
- **Live Data Vault** – key/value store for dynamic values such as exchange rates, weather, gold price, etc. Keys are unique and indexed.
- **Post Templates** – reusable Blade/HTML skeletons bound to a taxonomy (optional). Contains `name`, `slug`, `template_body`.
- **Content Nodes** – the final publishable pages. Fields include UUID, SEO title, slug, body content, `is_restricted_content` (boolean), page views, publish date, and foreign keys to taxonomy, location, and post template. Both `slug` and `is_restricted_content` are indexed for the ad‑routing engine.

## Data Model (Eloquent Relationships)
- `Taxonomy` → `children()` (hasMany), `parent()` (belongsTo), `contentNodes()` (hasMany).
- `Location` → `children()`, `parent()`, `contentNodes()`.
- `PostTemplate` → `taxonomy()` (belongsTo, optional), `contentNodes()` (hasMany).
- `ContentNode` → `taxonomy()`, `location()`, `postTemplate()` (all belongsTo).
- `LiveDataVault` – independent key/value store.

## High‑Performance Requirements
- Indexes on `slug`, `parent_id`, `taxonomy_id`, `location_id`, `is_restricted_content`.
- UUID primary key for `content_nodes` to guarantee global uniqueness.
- Caching layer (Redis) for hot look‑ups of taxonomies, locations, live data, and slugs.
- Cursor‑based pagination for content listings.

## Advertising Logic (Section 5.5 of the spec)
1. When rendering a content node, the system checks `is_restricted_content`.
2. If **false** → serve standard ads (Google/Native).
3. If **true** → serve alternative/ad‑friendly networks or hide ads entirely.
4. The flag is indexed for fast query during ad selection.

## Planned Development Phases
### Phase 3 – Admin Panel (Filament v3)
- CRUD resources for Taxonomy, Location, LiveDataVault, PostTemplate, ContentNode.
- Bulk edit, hierarchical tree view, preview of generated pages.
- Ability to toggle `is_restricted_content` per node.

### Phase 4 – Front‑end Rendering
- Blade templates that combine taxonomy, location, and live data placeholders (e.g., `{usd_try}`).
- Middleware to resolve live data values from the vault at request time.
- Cache rendered pages where possible.

### Phase 5 – API & Bot Ingestion
- Sanctum‑protected API endpoints for external Python bots to push new taxonomies, locations, live data, templates, and content nodes.
- Validation schemas, rate‑limiting, logging.

### Phase 6 – Scaling & Optimisation
- Redis caching strategy, queue jobs for heavy ingest, background indexing tasks.
- Monitoring & alerts for ad‑routing performance.

## Missing Elements (Future Enhancements)
- Elasticsearch/OpenSearch integration for advanced full‑text search.
- Enhanced multi‑portal tenant isolation.
- Advanced analytics dashboard with real‑time metrics.
- AI‑powered content generation suggestions.

---
*Generated from `portal_master.md` and current codebase.*