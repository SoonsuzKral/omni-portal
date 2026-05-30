# Live Data Placeholder Syntax

## Overview
The Omni-Portal supports dynamic placeholders in content templates and post templates. These placeholders are resolved at request time using location data, taxonomy data, and live data from the vault.

## Syntax Types

### 1. Location-Based Placeholders

| Placeholder | Description | Example |
|------------|-------------|---------|
| `{city}` | Current location name | İstanbul |
| `{district}` | Parent location name (if exists) | Kadıköy |
| `{neighborhood}` | First child location name | Moda |

**Usage in templates:**
```html
<p>En iyi {category} hizmeti {city} genelinde.</p>
<!-- Output: "En iyi Kombi hizmeti İstanbul genelinde." -->
```

### 2. Taxonomy Placeholders

| Placeholder | Description | Example |
|------------|-------------|---------|
| `{category}` | Current taxonomy name | Kombi |
| `{taxonomy}` | Alias for category | Kombi |

**Usage:**
```html
<h1>{city} {category} Servisi</h1>
<!-- Output: "İstanbul Kombi Servisi" -->
```

### 3. Live Data Vault Placeholders

Access values stored in the LiveDataVault table. Keys are lowercase with underscores.

| Placeholder | Description | Key Example |
|------------|-------------|-------------|
| `{usd_try}` | USD to TRY rate | `usd_try` |
| `{eur_try}` | EUR to TRY rate | `eur_try` |
| `{gold_gram}` | Gold price per gram | `gold_gram` |
| `{weather_istanbul}` | Weather for Istanbul | `weather_istanbul` |

**Add data via API:**
```json
{
  "live_data": [
    {"key": "usd_try", "value": "32.50", "display_name": "Dolar TL"},
    {"key": "gold_gram", "value": "2450", "display_name": "Altın Gram"}
  ]
}
```

**Usage in templates:**
```html
<p>Güncel dolar kuru: {usd_try} TL</p>
<p>Altın fiyatı: {gold_gram} TL</p>
```

### 4. External Data Service (Redis-Cached)

The ExternalDataService provides additional placeholders with built-in caching:

- Exchange rates (automatically fetched)
- Weather data
- Stock indices

These are resolved through `ExternalDataService::resolvePlaceholders()`.

### 5. Spintax (Randomized Content)

Spintax allows random selection from multiple options.

**Syntax:** `{option1|option2|option3}`

**Example:**
```html
<h1>En {iyi|güvenilir|popüler} {category} servisi {city}'de</h1>
<!-- Possible outputs:
  - "En iyi Kombi servisi İstanbul'da"
  - "En güvenilir Kombi servisi İstanbul'da"
  - "En popüler Kombi servisi İstanbul'da"
-->
```

## Resolution Order

1. **Location placeholders** - `{city}`, `{district}`, `{neighborhood}`
2. **Taxonomy placeholders** - `{category}`, `{taxonomy}`
3. **Live Data Vault** - `{key_name}` from database
4. **External Data Service** - exchange rates, weather (cached)
5. **Spintax** - `{option1|option2|option3}`

## Caching

Live data vault values are cached in Redis for performance. Clear cache via:
```php
PlaceholderResolver::clearCache();
```

Or via Artisan:
```bash
php artisan cache:clear
```

## Template Example

```html
<!-- Full template example -->
<article>
  <h1>{city} {category} Servisi - 7/24 Hizmet</h1>
  <p>En {iyi|kaliteli|güvenilir} kombi servisi olarak {district} bölgesinde hizmet vermekteyiz.</p>
  <p>Güncel Dolar Kuru: {usd_try} TL</p>
  <p>Altın Fiyatı: {gold_gram} TL</p>
  <p>Şu an {istanbul|ankara|izmir}'de { aktif|bulunuyor|hizmet veriyor }.</p>
</article>
```

## API Integration

### Ingest Live Data
```bash
curl -X POST https://api.omni-portal.com/api/v1/ingest \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "live_data": [
      {"key": "usd_try", "value": "32.50", "display_name": "Dolar TL"},
      {"key": "eur_try", "value": "35.20", "display_name": "Euro TL"}
    ]
  }'
```

## Notes

- Placeholders are **case-insensitive** for built-in types
- Custom vault keys must be lowercase with underscores (regex: `^[a-z0-9_]+$`)
- Spintax pipes cannot be nested
- Unresolved placeholders remain as-is in output