# 🔷 Keyword Orchestra - Multi Country Keyword Harvesting System

## Quick Start

### 1. Laravel Sunucu Kurulumu

API token oluşturmak için sunucuda çalıştır:

```bash
php artisan tinker
```

Sonra konsolda:

```php
$user = \App\Models\User::first();
echo $user->createToken('orchestra')->plainTextToken;
```

Bu token'ı bir yere kaydet.

### 2. Python Kurulumu

```bash
cd python-orchestra
pip install -r requirements.txt
```

### 3. Konfigürasyon

`.env` dosyasını düzenle:

```env
API_BASE_URL=https://sunteci.com  # Sunucu adresin
API_TOKEN=your_generated_token    # Yukarıda aldığın token
```

### 4. Test Et

```bash
python main.py test
```

## Komutlar

### Tam Orkestrasyon (Tüm Ülkeler)
```bash
python main.py run --keywords 100
```
- Avrupa: 28 ülke
- Global: 26 ülke
- Toplam: 54 ülke

### Sadece Avrupa
```bash
python main.py run-europe --keywords 50
```

### Sadece Global
```bash
python main.py run-global --keywords 50
```

### Belirli Ülke
```bash
python main.py run-country TR --keywords 100
python main.py run-country US --keywords 100
python main.py run-country DE --keywords 100
```

### Durum Kontrolü
```bash
python main.py status
python main.py sync
```

## Ülkeler Listesi

### Avrupa (28)
TR, GB, DE, FR, ES, IT, NL, BE, AT, CH, PL, SE, NO, DK, FI, PT, GR, IE, CZ, HU, RO, BG, SK, HR, SI, RS, UA, RU

### Global (26)
US, CA, BR, AR, MX, CO, CL, PE, AU, NZ, JP, KR, IN, ID, TH, VN, MY, SG, PH, PK, EG, SA, AE, IL, ZA, NG, KE

## Uzaktan Bağlanma (Local'den Sunucuya)

Local makineden sunucudaki API'ye bağlanmak için:

```bash
python main.py run --url https://sunteci.com --token YOUR_TOKEN
```

Veya `.env` dosyasını düzenle:

```env
API_BASE_URL=https://sunteci.com
API_TOKEN=your_token
```

## Özellikler

- ✅ Tüm Avrupa ülkeleri (28)
- ✅ Tüm Global ülkeler (26)
- ✅ Batch processing (100 keyword/batch)
- ✅ Retry mekanizması (max 3 deneme)
- ✅ Rate limiting desteği
- ✅ Keyword varyasyon üretimi
- ✅ Search volume & difficulty tahmini
- ✅ Real-time ilerleme göstergesi

## API Endpoints

| Endpoint | Method | Açıklama |
|----------|--------|----------|
| `/api/v1/orchestra/keywords` | POST | Keyword import |
| `/api/v1/orchestra/sync-countries` | GET | Ülke sync |
| `/api/v1/orchestra/status` | GET | Durum kontrolü |

## Örnek Çıktı

```
============================================================
🔷 KEYWORD ORCHESTRATOR - MULTI COUNTRY SYSTEM
============================================================

🔄 Syncing countries with Laravel API...
✓ Countries synced: 54 total, 0 new

============================================================
🚀 STARTING FULL ORCHESTRATION
============================================================

------------------------------------------------------------
🌍 PROCESSING EUROPE (28 countries)
------------------------------------------------------------

▶ Processing: Turkey (TR) - Language: tr
  Generated 100 keywords
  ✓ Imported: 100, Updated: 0

▶ Processing: Germany (DE) - Language: de
  Generated 100 keywords
  ✓ Imported: 98, Updated: 2
...
```

## Sorun Giderme

**Connection refused:**
- Sunucunun çalıştığını kontrol et: `php artisan serve`
- URL'nin doğru olduğunu kontrol et

**401 Unauthorized:**
- Token'ın geçerli olduğunu kontrol et
- Token'ı yeniden oluştur: `php artisan tinker`

**Rate limit hatası:**
- `MAX_RETRIES` değerini artır veya bekleme süresini uzat