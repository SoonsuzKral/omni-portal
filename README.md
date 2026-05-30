# Omni-Portal - Programmatic SEO Platform

## Overview

Omni-Portal is a high-performance programmatic SEO system designed to generate millions of niche landing pages automatically. It combines hierarchical taxonomies, location trees, live data variables, and reusable post templates to produce SEO-optimized content at scale.

## Features

- **Taxonomy System** - Hierarchical categories (niche, health, finance, etc.)
- **Location System** - City → District → Neighbourhood hierarchy
- **Live Data Vault** - Dynamic values (exchange rates, weather, gold price, etc.)
- **Post Templates** - Reusable Blade/HTML skeletons
- **Content Nodes** - The final publishable pages with SEO fields
- **Keyword Tracking** - Search volume, difficulty, trending keywords
- **Ad Policy Routing** - `is_restricted_content` flag for ad network selection

## Tech Stack

- **Framework:** Laravel 12
- **Admin Panel:** Filament v3
- **API:** Laravel Sanctum
- **Database:** SQLite (dev) / MySQL (prod)
- **Caching:** Redis (optional)

## Installation

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Build assets
npm install && npm run build

# Start development server
php artisan serve
```

## Admin Panel

Access the admin panel at: `/admin`

Default login credentials (after seeding):
- Email: `test@example.com`
- Password: `password`

### Admin Resources

| Resource | Description |
|----------|-------------|
| Taxonomy | Hierarchical categories |
| Location | City/district/neighbourhood |
| Content Nodes | Landing pages |
| Keywords | SEO keywords |
| Live Data | Dynamic variables |
| Post Templates | Content templates |
| Global Ad Blocks | Ad configurations |

## API Documentation

Full API documentation is available in `docs/openapi.json` (OpenAPI/Swagger format).

### Base URL

```
Production: https://yourdomain.com/api
Development: http://localhost:8000/api
```

### Authentication

1. **Get Token:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "password"}'
```

2. **Use Token:**
```bash
curl -X GET http://localhost:8000/api/v1/taxonomies \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | Health check |
| GET | `/api/v1/stats` | System statistics |
| GET | `/api/v1/ingest/status` | Ingest status |
| POST | `/api/v1/auth/token` | Generate token |

### Protected Endpoints

All protected endpoints require `Authorization: Bearer {token}` header.

#### Taxonomies
- `GET /api/v1/taxonomies` - List all
- `POST /api/v1/taxonomies` - Create
- `GET /api/v1/taxonomies/{slug}` - Get by slug
- `PUT /api/v1/taxonomies/{slug}` - Update
- `DELETE /api/v1/taxonomies/{slug}` - Delete

#### Locations
- `GET /api/v1/locations` - List all
- `POST /api/v1/locations` - Create
- `GET /api/v1/locations/{slug}` - Get by slug
- `PUT /api/v1/locations/{slug}` - Update
- `DELETE /api/v1/locations/{slug}` - Delete

#### Content Nodes
- `GET /api/v1/content-nodes` - List with filters
- `GET /api/v1/content-nodes/{slug}` - Get by slug
- `PUT /api/v1/content-nodes/{slug}` - Update
- `DELETE /api/v1/content-nodes/{slug}` - Delete

#### Keywords
- `GET /api/v1/keywords` - List with filters
- `POST /api/v1/keywords` - Create
- `GET /api/v1/keywords/{id}` - Get by ID
- `PUT /api/v1/keywords/{id}` - Update
- `DELETE /api/v1/keywords/{id}` - Delete

#### Live Data
- `GET /api/v1/live-data` - List all
- `POST /api/v1/live-data` - Create/Update

#### Bulk Ingest
- `POST /api/v1/ingest` - Bulk import (taxonomies, locations, content, live_data, templates)

#### Export
- `POST /api/v1/export?type=content_nodes&format=csv` - Export data

## Placeholder Syntax

The system supports dynamic placeholders in content templates:

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{city}` | Current location name | İstanbul |
| `{district}` | Parent location name | Kadıköy |
| `{category}` | Taxonomy name | Kombi |
| `{usd_try}` | Live data value | 32.50 |
| `{option1\|option2\|option3}` | Spintax (random) | Random selection |

## Security Features

### SQL Injection Protection
Automatically blocks dangerous SQL patterns in requests.

### Rate Limiting
- API: 60 requests/minute
- Auth: 5 attempts/5 minutes

### IP Whitelist (Optional)
Restrict admin panel access to specific IPs via `.env`:
```
ADMIN_WHITELIST=192.168.1.100,10.0.0.0/24
```

### Security Headers
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security: max-age=31536000

## Sitemap

XML sitemaps are auto-generated:
- `/sitemap.xml` - Main sitemap (smart routing)
- `/sitemap-content.xml` - Content pages
- `/sitemap-categories.xml` - Categories
- `/sitemap-locations.xml` - Locations

## Environment Variables

```env
APP_NAME=OmniPortal
APP_ENV=local
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

ADMIN_WHITELIST=           # Comma-separated IPs (optional)
API_WHITELIST=             # Comma-separated IPs (optional)
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=IngestApiTest
```

## Docker

```bash
# Build and run
docker-compose up -d

# Access at http://localhost:8000
```

## Roadmap

- [x] Phase 1-2: Schema & Models
- [x] Phase 3: Filament Admin
- [x] Phase 4: Frontend Rendering
- [x] Phase 5: API & Bot Ingestion
- [ ] Phase 6: Scaling & Optimization (Redis, queues)

## License

MIT License