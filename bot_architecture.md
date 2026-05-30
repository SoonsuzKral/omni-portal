# Omni-Bot Matrix Injector — Architecture Document

## Overview

Omni-Bot is a high‑performance Python automation system designed for **Programmatic SEO**. It generates hundreds of thousands of unique, indexable landing pages by cross‑joining **Locations** × **Keywords/Trends** × **Question Patterns** — then injecting them into the Omni‑Portal Laravel CMS via a REST API.

---

## 1. Data Pipeline

```
Google Trends RSS
       │
       ▼
┌──────────────────┐     ┌───────────────────┐     ┌──────────────────┐
│   Data Sources   │────▶│   Matrix Engine   │────▶│   Spintax Engine │
│  • Trends (live) │     │  Location × KW ×  │     │  Word-level NLP  │
│  • 81 Provinces  │     │  Question Pattern │     │  {opt|opt}       │
│  • 28 Niches     │     │  → Unique Nodes   │     │  → Unique Body   │
│  • Question Pool │     └───────────────────┘     └──────────────────┘
└──────────────────┘                                        │
                                                             ▼
┌──────────────────┐     ┌───────────────────┐     ┌──────────────────┐
│   Checkpoint     │◀────│   Thread Pool     │◀────│   Batch Builder  │
│   Resume/Recover │     │  20-50 Workers    │     │  50 nodes/packet │
└──────────────────┘     └────────┬──────────┘     └──────────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │  Laravel /api/v1/ingest  │
                    │  Sanctum Bearer Auth    │
                    │  Queue: async bulk job  │
                    └─────────────────────────┘
```

---

## 2. Dynamic Data Sources

### Google Trends Integration (`TrendsFetcher`)

The bot pulls live trending searches from two channels:

- **Google Trends RSS Feed** — parses `trends.google.com/trending/rss?geo=TR` for Turkish daily trends, falls back to `?geo=US` (global).
- **pytrends library** (fallback) — unofficial Python client if RSS is unavailable.

Trending topics are injected into the matrix as a synthetic "Trend Konular" taxonomy category, generating pages that ride current search waves.

### Keyword & Niche Repository

**28+ verticals** are embedded directly in the code, each with **10–12 keywords**:

| Sector | Example Keywords |
|---|---|
| Klima Servisi | Klima Bakım, Klima Gaz Dolum, İnverter Klima |
| Kombi Servisi | Kombi Tamir, Kombi Eşanjör, Yoğuşmalı Kombi |
| Doğalgaz | Doğalgaz Abonelik, Doğalgaz Projesi, Doğalgaz Dönüşümü |
| Altın Fiyatları | Gram Altın, Çeyrek Altın, Reşat Altın |
| Eczane | Nöbetçi Eczane, Online Eczane, Veteriner Eczanesi |
| ... 24 more sectors | ... ~280+ keywords total |

### Question Pattern Pool

**48 search-intent question patterns** such as:

- `{keyword} {il} fiyatları 2026`
- `{keyword} {il} en iyi hizmet`
- `{keyword} {il} telefon numarası`
- `{keyword} {il} nasıl gidilir`
- `{keyword} {il} acil servis`

Each keyword × location combination is expanded into every pattern, generating unique SEO titles.

---

## 3. The Matrix Multiplier

### Cartesian Product

```
Total Nodes = |Locations| × Σ|Keywords_per_Niche| × |QuestionPatterns|

Example:
  131 (locations) × 280 (keywords) × 48 (patterns) = ~1,761,000+
```

### Uniqueness Guarantee

Each node receives a **deterministic unique slug** derived from `MD5(location_slug + niche_slug + keyword + question_pattern)`, preventing duplicates while remaining reproducible for resumption.

---

## 4. Spintax Engine (Uniqueness Layer)

The `SpintaxEngine` applies **two levels** of variation to body content:

### Level 1 — Word‑level Synonym Replacement

A thesaurus of **35+ word groups** with 4–5 synonyms each:

```python
"hizmet" → ["servis", "destek", "yardım", "çözüm", "bakım"]
"kaliteli" → ["başarılı", "güvenilir", "nitelikli", "seçkin", "premium"]
```

Every content body passes through synonym substitution with ~35% intensity per word, producing statistically unique text each time.

### Level 2 — Spintax Syntax `{option1|option2|option3}`

Body templates contain explicit spintax markers. The engine parses these and randomly selects one option:

```
{il} şehrinde {kaliteli|başarılı|güvenilir} {hizmet|destek|çözüm}
```

### 8 Distinct Base Templates

Each template is ~3 paragraphs of Turkish SEO prose. Combined with spintax, every generated page has a unique word‑level fingerprint.

---

## 5. API Ingestion Pipeline

### Endpoint

| Method | Path | Auth |
|---|---|---|
| `POST` | `/api/v1/ingest` | `Authorization: Bearer {token}` |

### Batching

- Nodes are collected into **batches of 50** (`OMNI_BATCH_SIZE`).
- Each batch is a single HTTP POST with `{"content_nodes": [...]}`.
- The Laravel backend automatically routes batches >50 items to the async queue (`ProcessBulkIngestJob`).

### Concurrency

- Uses `concurrent.futures.ThreadPoolExecutor` with **30 workers** (`OMNI_WORKERS`).
- Each worker sends batches concurrently.
- A small `RATE_LIMIT_SLEEP` (0.05s) prevents 429 responses.

### Retry Logic

- **3 retries** (`OMNI_MAX_RETRIES`) with exponential backoff.
- Handles 429 (rate limit), 5xx (server errors), ConnectionError, and Timeout.
- Failed batches are counted but do not halt the pipeline.

### Checkpoint / Resume

- Every 50 batches, a checkpoint file (`omni_checkpoint.json`) is written listing processed slugs.
- Running with `--resume` skips already‑processed slugs, allowing restart without duplication.

---

## 6. Setup & Configuration

### Environment Variables

| Variable | Default | Description |
|---|---|---|
| `OMNI_BASE_URL` | `http://localhost:8000` | Laravel backend URL |
| `OMNI_API_TOKEN` | *(empty)* | Sanctum Bearer token |
| `OMNI_WORKERS` | `30` | Thread pool size |
| `OMNI_BATCH_SIZE` | `50` | Nodes per request |
| `OMNI_MAX_RETRIES` | `3` | HTTP retry attempts |
| `OMNI_RATE_SLEEP` | `0.05` | Seconds between requests |
| `OMNI_TRENDS` | `true` | Enable/disable live trends |

### Token Generation

```bash
# Via artisan (on the server)
php artisan tinker
> $user = User::where('email', 'bot@example.com')->first();
> $user->createToken('OmniBot', ['ingest'])->plainTextToken;

# Or via API
curl -X POST http://localhost:8000/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email":"bot@example.com","password":"..."}'
```

---

## 7. Deployment & Scheduling

### Option A: Cron (Linux VDS)

```bash
# Run every 6 hours
0 */6 * * * cd /var/www/omni-bot && /usr/bin/python3 omni_bot.py >> /var/log/omni_bot.log 2>&1
```

### Option B: pm2 (Node.js process manager)

```bash
npm install -g pm2

# ecosystem.omni.config.js
module.exports = {
  apps: [{
    name: "omni-bot",
    script: "omni_bot.py",
    interpreter: "python3",
    args: "--resume",
    cron_restart: "0 */4 * * *",
    autorestart: true,
    max_memory_restart: "512M",
    error_file: "logs/omni_err.log",
    out_file: "logs/omni_out.log",
  }]
};

pm2 start ecosystem.omni.config.js
pm2 save
pm2 startup
```

### Option C: systemd Service

```ini
# /etc/systemd/system/omni-bot.service
[Unit]
Description=Omni-Portal SEO Bot
After=network.target

[Service]
Type=oneshot
WorkingDirectory=/var/www/omni-bot
ExecStart=/usr/bin/python3 /var/www/omni-bot/omni_bot.py --resume
Environment=OMNI_BASE_URL=https://yourdomain.com
Environment=OMNI_API_TOKEN=your_token_here
Restart=on-failure
User=www-data

[Install]
WantedBy=multi-user.target
```

Then a timer:
```ini
# /etc/systemd/system/omni-bot.timer
[Unit]
Description=Run Omni-Bot every 6 hours

[Timer]
OnCalendar=*-*-* 00,06,12,18:00:00
Persistent=true

[Install]
WantedBy=timers.target
```

---

## 8. Performance Expectations

| Metric | Expected |
|---|---|
| Nodes/sec | ~500–1,500 (depends on network & Laravel queue) |
| Full matrix | 1.7M+ unique nodes |
| First run | 30–60 min |
| API payload | 50 nodes = ~150–300 KB |

Run `php artisan queue:work --queue=high,default` on the Laravel side to consume the ingested jobs.

---

## 9. CLI Usage

```bash
# Full production run
python omni_bot.py

# Preview only (no API calls)
python omni_bot.py --dry-run

# Quick test (500 nodes)
python omni_bot.py --quick

# Resume from last checkpoint
python omni_bot.py --resume

# Combined: test + resume
python omni_bot.py --quick --resume
```

---

## 10. File Structure

```
c:\seo\
├── omni_bot.py              # Main bot (this file)
├── bot_architecture.md       # This document
├── omni_checkpoint.json      # Auto-generated checkpoint
├── omni_bot.log              # Auto-generated log
├── bombardiman.py            # Legacy bot (v1)
├── enjektor.py               # Legacy bot (v1)
└── python-orchestra/         # Keyword orchestra (separate)
```
