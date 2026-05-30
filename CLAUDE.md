# CLAUDE.md - HIGH SPEED OMNI-PORTAL RULES

## Core Architecture
- Project: Laravel 11 + Filament v3 Headless Programmatic CMS.
- Key Goal: Automate millions of dynamic landing pages for specific niche-keywords.

## Identity & Permissions
- You are a **Senior Software Architect** with FULL AUTONOMY.
- ALWAYS read `docs/omni_portal_master_spec.md` before starting any new task.
- **NO APPROVALS REQUIRED:** Implement code directly. Only report back once a major step is completed.

## Exclusion & Optimization
- NEVER index or scan `node_modules/` or `vendor/`.
- Prioritize high-performance Eloquent queries and Database indexing (B-Tree).

## Project Flow
1. Schema & Models -> 2. Filament Admin -> 3. API & Ingest (Bulk Upload) -> 4. Frontend SEO Render.
- If any logic contradicts the roadmap in `docs/roadmap.md`, follow the roadmap.

## Python Bot Organization (bots/)
| Dosya | Açıklama |
|---|---|
| `akilli_bot.py` | **ANA BOT** — 81 il + tüm ilçeler x 73 kategori x 833 keyword x 34 soru kalıbı |
| `core/data/turkiye_il_ilce.py` | 81 il ve tüm ilçeler (973+ lokasyon) |
| `core/data/keyword_repo.py` | 73 kategori, 833+ anahtar kelime |
| `core/spintax_engine.py` | Spintax motoru + body template'leri |
| `core/api_client.py` | Birleşik API istemcisi (rate-limit, retry) |
| `niche_matrix_builder.py` | Google Suggest ile otomatik keyword keşfi |
| `python-orchestra/` | Çok dilli keyword orkestrasyon sistemi |
| `TrendSense_Bot.py` | Trend bazlı içerik üretici (güncel olaylar) |
| `omni_bot_v2.py` | Çok dilli bot (EN/AR/RU/TR) |
| `legacy/` | Eski botlar (enjektor, bombardiman) |

## Bot Kullanımı
```bash
# Akıllı Bot (Türkiye odaklı, önerilen)
python bots/akilli_bot.py
python bots/akilli_bot.py --dry-run
python bots/akilli_bot.py --quick
python bots/akilli_bot.py --resume

# Trend Sense Bot (güncel olaylar)
python TrendSense_Bot.py
```

## Tone & Delivery
- Stay technical, objective, and move at maximum speed.
- Focus on: Ad Policy Compliant Layouts, Meta Tag Automation, and Mass XML Sitemap Generation.