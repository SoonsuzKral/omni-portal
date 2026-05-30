# OMNI-PORTAL PROJECT SYSTEM GUIDE

Bu doküman, Omni-Portal programmatik SEO sistemini yeni bir geliştirici için hazırlanmış kapsamlı teknik referans dokümanıdır.

---

## 1. PROJE GENEL ÖZETİ

### 1.1 Sistem Nedir?

Omni-Portal, **Laravel 11 + Filament v3** tabanlı, milyonlarca dinamik landing page üretmeyi hedefleyen **Programmatic SEO Engine** sistemidir.

**Temel Özellikleri:**
- **Taxonomy System** - Hiyerarşik kategori ağacı (niche, health, finance vb.)
- **Location System** - İl → İlçe → Mahalle hiyerarşisi
- **Live Data Vault** - Canlı veri kasası (döviz, altın, hava durumu)
- **Post Templates** - Tekrar kullanılabilir Blade/HTML şablonları
- **Content Nodes** - Yayınlanan asıl içerik sayfaları
- **Keyword Tracking** - Arama hacmi, zorluk, trending keywords

### 1.2 Kullanıcı Ne Yapabiliyor?

- Taksonomi ve lokasyon bazlı içeriklere erişim
- Arama yapma (full-text search)
- Sitemap üzerinden içerik keşfetme
- Filament admin panel üzerinden CRUD yönetimi

### 1.3 SEO Analiz Mantığı

Sistem, içeriği render ederken şu adımları izler:
1. ContentNode'dan veri çekilir
2. PlaceholderResolver ile dinamik değerler çözülür (location, taxonomy, live data, spintax)
3. SeoService üzerinden meta tags, OG tags, JSON-LD schema üretilir
4. AdRenderer componenti `is_restricted_content` flag'ine göre reklam seçer
5. Sayfa view count artırılır

### 1.4 AI Destekli Sistemler

- **IndexingService** - Google Indexing API + Bing IndexNow entegrasyonu
- **SemanticLinkMatrix** - İç semantic link üretimi + cross-portal linking
- **ImageHelper** - Otomatik featured image üretimi
- **ExternalDataService** - Redis-cache'li canlı veri çözümleme

---

## 2. SİSTEM AKIŞI

### 2.1 Kullanıcı Request Akışı

```
Kullanıcı Request
      ↓
Web Route (routes/web.php)
      ↓
Middleware Stack (sql.protect)
      ↓
Controller (ContentController/TaxonomyController/LocationController)
      ↓
Model Query (ContentNode/Taxonomy/Location)
      ↓
PlaceholderResolver::resolve() - Dinamik içerik çözümleme
      ↓
SeoService - SEO meta tags üretimi
      ↓
Blade View Render
      ↓
Response
```

### 2.2 Frontend → Backend → API Zinciri

**Frontend (resources/js):**
- `adblock.js` - AdBlock detection (score tabanlı)
- `app.js` - Vite entry point
- `bootstrap.js` - JS bootstrap

**Backend (Laravel):**
- Routes → Controllers → Models → Views
- API: Sanctum token korumalı
- Admin: Filament v3

**API Zinciri:**
- Python botları → `/api/v1/ingest` → Batch insert → Queue job
- Sitemap: `/sitemap.xml` → Smart routing

### 2.3 Vite Build Akışı

```bash
# Development
npm run dev

# Production
npm run build
# Output: public/build/manifest.json
```

Vite config: `vite.config.js`  
Entry: `resources/js/app.js`  
Dependencies: adblock.js, tailwindcss

### 2.4 JS Yüklenme Sistemi

```blade
@vite(['resources/js/adblock.js'])
```

Yüklenen dosyalar:
- `resources/js/bootstrap.js` - Laravel Blade ile entegrasyon
- `resources/js/app.js` - Ana uygulama
- `resources/js/adblock.js` - AdBlock detector

---

## 3. SCRIPT AÇIKLAMALARI

### 3.1 adblock.js

**Konum:** `resources/js/adblock.js`

**Amaç:** AdBlock eklentisi tespiti ve kullanıcıya modal gösterimi

**Konsol Akışı:**
```
[AdBlock] START: Detection started
[AdBlock] STEP: === CHECK 1: Google Script Load ===
[AdBlock] RESULT: Script load: SUCCESS/FAILED
[AdBlock] STEP: === CHECK 2: adsbygoogle Global ===
[AdBlock] RESULT: adsbygoogle: EXISTS/UNDEFINED
[AdBlock] STEP: === CHECK 3: Bait Elements Visibility ===
[AdBlock] RESULT: Bait elements: VISIBLE/HIDDEN
[AdBlock] FINAL: Score: X, Signals: [Y, Z]
[AdBlock] FINAL: Result: BLOCKED/CLEAN
[AdBlock] BACKEND: Sent to Laravel: score=X, result=Y
```

**Score Sistemi:**
- `score >= 2` → BLOCKED (AdBlock aktif)
- `score < 2` → CLEAN (Temiz)

**Signal Sistemi:**
| Signal | Puan | Anlam |
|--------|------|-------|
| ERR_BLOCKED_BY_CLIENT | +1 | AdBlock client-side engelledi |
| adsbygoogle_undefined | +1 | adsbygoogle undefined |
| pauseAdRequests_active | +1 | pauseAdRequests = 1 |
| bait_hidden | +1 | 3+ bait element gizlenmiş |

**Ignore Edilen Error'lar:**
- CORS hataları
- ERR_FAILED (net::ERR_FAILED)
- Timeout (3s sonra resolve)
- 204 No Content

**Script Başarılı Yüklendiğinde Score Reset:**
- `resetScoreOnSuccess()` fonksiyonu çalışır
- Maksimum score 1 azaltılır: `this.score = Math.max(0, this.score - 1)`
- Neden: Script yüklenebildiyse AdBlock tam çalışmıyor demektir, score düşürülür

**Bait Element Mantığı:**
```javascript
// Gizlenme kriterleri
- display: none
- visibility: hidden
- width === 0
- height === 0
```

**Laravel Debug Endpoint:**
```
POST /adblock/debug
Headers: X-CSRF-TOKEN, Content-Type: application/json
Body: { score, signals, result, checks, logs, user_agent, url }
```

**Modal Trigger:**
- `showModal()` - AdBlock tespit edilirse modal gösterir
- "Tekrar kontrol et" ve "Sayfayı yenile" butonları

### 3.2 app.js

**Konum:** `resources/js/app.js`

**Amaç:** Vite entry point, global JS yapılandırması

```javascript
import './bootstrap';
// Custom imports here
```

### 3.3 bootstrap.js

**Konum:** `resources/js/bootstrap.js`

**Amaç:** Laravel ile JS arası köprü, Axios yapılandırması

---

## 4. BLADE COMPONENT YAPISI

### 4.1 Layout Sistemi

**Ana Layout:** `resources/views/layouts/app.blade.php`

**Yapı:**
```blade
<!DOCTYPE html>
<html lang="tr">
<head>
    - Meta tags (csrf-token, title, description)
    - Google Fonts (Montserrat, Poppins)
    - Tailwind CSS (CDN)
    - Font Awesome
    - @stack('head')
</head>
<body>
    <nav> - Sticky navbar, search, theme toggle, mobile menu </nav>
    <main> - @yield('content') </main>
    <footer> - Quick links, API info, copyright </footer>
    <script> - Theme toggle, mobile menu </script>
    @stack('scripts')
    @vite(['resources/js/adblock.js'])
    <x-adblock-modal />
</body>
</html>
```

**Componentler:**
- `<x-adblock-modal />` - AdBlock uyarı modalı
- `<x-ad-renderer />` - Reklam script renderer
- `<x-seo-json-ld />` - JSON-LD schema
- `<x-semantic-link-matrix />` - Cross-linking
- `<x-related-locations />` - İlgili lokasyonlar

### 4.2 Modal Sistemi

**AdBlock Modal** (`resources/views/components/adblock-modal.blade.php`):
- AdBlock tespit edildiğinde gösterilir
- İki buton: "Tekrar kontrol et", "Sayfayı yenile"
- CSS: Absolute positioning, z-index yüksek

### 4.3 Partial Yapıları

| Dosya | Kullanım |
|-------|----------|
| `home.blade.php` | Ana sayfa |
| `content/show.blade.php` | İçerik detay |
| `taxonomy/index.blade.php` | Kategori listesi |
| `location/index.blade.php` | Lokasyon listesi |
| `search/results.blade.php` | Arama sonuçları |

---

## 5. SEO ANALİZ MOTORU

### 5.1 Analiz Hesaplama Mantığı

**SeoService** (`app/Services/SeoService.php`) üzerinden çalışır:

1. **Title Generation:**
   - seo_title + location name + taxonomy name kombinasyonu

2. **Description Generation:**
   - Mevcut meta_description varsa kullan
   - Yoksa body_content'ten ilk cümle çıkarılır
   - 160 karakter limit

3. **OG Data:**
   - Title, description, type, url, image, locale, site_name

4. **Canonical URL:**
   - `/category/location/slug` formatı

5. **JSON-LD Schema:**
   - Article schema
   - FAQPage schema (faq_data varsa)
   - LocalBusiness schema (location varsa)
   - BreadcrumbList schema
   - WebSite schema + SearchAction

### 5.2 Skor Sistemi

- **Page Views:** Her içerik görüntülemede otomatik artış
- **Content Scoring:** placehold'lar çözümlenir, spintax random seçilir

### 5.3 AI Analizleri

- **IndexingService:** Google Indexing API + Bing IndexNow
- **SemanticLinkMatrix:** Cross-portal link juice hesaplama
- **ImageHelper:** OG image auto-generation

### 5.4 Kullanılan Metrikler

| Metrik | Kaynak |
|--------|--------|
| page_views | ContentNode tablosu |
| publish_date | ContentNode tablosu |
| is_restricted_content | Ad policy routing |
| location hierarchy | Location tablosu |
| taxonomy tree | Taxonomy tablosu |

---

## 6. API VE ROUTE AKIŞLARI

### 6.1 Route Listesi

**Web Routes** (`routes/web.php`):

| Route | Controller | Açıklama |
|-------|------------|----------|
| `/` | HomeController@index | Ana sayfa |
| `/search` | ContentController@search | Arama sonuçları |
| `/search/suggest` | SearchController@api | Arama önerileri |
| `/keywords/trending` | KeywordController@trending | Trend keywords |
| `/sitemap.xml` | SitemapController@index | Sitemap index |
| `/categories` | TaxonomyController@index | Kategori listesi |
| `/categories/tree` | TaxonomyController@tree | Hiyerarşik ağaç |
| `/locations` | LocationController@index | Lokasyon listesi |
| `/location/{slug}` | LocationController@show | Tek lokasyon |
| `/p/{slug}` | ContentController@showBySlug | Direkt slug |
| `/{category}` | ContentController@taxonomyIndex | Kategori içerikleri |
| `/{category}/{locationSlug}` | ContentController@index | Kategori+lokasyon |
| `/{category}/{locationSlug}/{slug}` | ContentController@show | Detay içerik |

**API Routes** (`routes/api.php`):

| Route | Method | Auth | Açıklama |
|-------|--------|------|----------|
| `/api/health` | GET | No | Health check |
| `/api/v1/stats` | GET | No | Sistem istatistikleri |
| `/api/v1/ingest/status` | GET | No | Ingest durumu |
| `/api/v1/auth/token` | POST | No | Token üretimi |
| `/api/v1/ingest` | POST | Yes | Bulk import |
| `/api/v1/taxonomies` | GET/POST | Yes | Taxonomy CRUD |
| `/api/v1/locations` | GET/POST | Yes | Location CRUD |
| `/api/v1/content-nodes` | GET | Yes | Content list |
| `/api/v1/keywords` | GET/POST | Yes | Keyword CRUD |
| `/api/v1/live-data` | GET/POST | Yes | Canlı veri |
| `/api/v1/export` | POST | Yes | Export (CSV) |

### 6.2 Controller Bağlantıları

| Controller | Sorumluluk |
|------------|------------|
| HomeController | Ana sayfa render |
| ContentController | İçerik gösterimi, arama |
| TaxonomyController | Kategori yönetimi |
| LocationController | Lokasyon yönetimi |
| SearchController | Arama API |
| KeywordController | Keyword yönetimi |
| SitemapController | XML sitemap |
| AdBlockDebugController | AdBlock debug log |
| Api\AuthController | Token yönetimi |
| Api\IngestController | Bot veri ingest |
| Api\ResourceController | CRUD işlemleri |

### 6.3 Request/Response Mantığı

**Örnek: Content Show**
```php
// Request: /kombi/istanbul/kombi-servisi
public function show($category, $locationSlug, $slug) {
    $taxonomy = Taxonomy::where('slug', $category)->firstOrFail();
    $location = Location::where('slug', $locationSlug)->firstOrFail();
    $content = ContentNode::where('slug', $slug)
        ->where('taxonomy_id', $taxonomy->id)
        ->where('location_id', $location->id)
        ->firstOrFail();
    
    $content->increment('page_views');
    $resolvedBody = PlaceholderResolver::resolve($content->body_content, $location, $taxonomy);
    
    return view('content.show', compact('content', 'taxonomy', 'location', 'resolvedBody'));
}
```

---

## 7. ADBLOCK SİSTEMİ DETAYI

### 7.1 Console Akışı

```
[AdBlock] START: Detection started
[AdBlock] STEP: === CHECK 1: Google Script Load ===
[AdBlock] INFO: adsbygoogle.loaded === true (already loaded)
[AdBlock] SUCCESS: Google script loaded successfully
[AdBlock] RESULT: Script load: SUCCESS
[AdBlock] RESET: Script loaded successfully, score reset to: 0
[AdBlock] STEP: === CHECK 2: adsbygoogle Global ===
[AdBlock] INFO: adsbygoogle exists and ready
[AdBlock] RESULT: adsbygoogle: EXISTS
[AdBlock] STEP: === CHECK 3: Bait Elements Visibility ===
[AdBlock] INFO: 5/5 bait elements visible
[AdBlock] RESULT: Bait elements: VISIBLE
[AdBlock] FINAL: Score: 0, Signals: []
[AdBlock] FINAL: Result: CLEAN
[AdBlock] BACKEND: Sent to Laravel: score=0, result=CLEAN
```

### 7.2 Score Sistemi

| Durum | Score | Sonuç |
|-------|-------|-------|
| ERR_BLOCKED_BY_CLIENT | +1 | BLOCKED |
| adsbygoogle_undefined | +1 | BLOCKED |
| pauseAdRequests_active | +1 | BLOCKED |
| bait_hidden (3+ element) | +1 | BLOCKED |
| Script başarıyla yüklendi | -1 | Score reset |
| Timeout (3s) | 0 | CLEAN (ignore) |
| CORS/ERR_FAILED | 0 | CLEAN (ignore) |

**Formül:**
```
finalScore = sum(signals) - (scriptLoaded ? 1 : 0)
if (finalScore >= 2) → BLOCKED
else → CLEAN
```

### 7.3 Signal Sistemi

**Eklenen sinyaller:**
- `ERR_BLOCKED_BY_CLIENT` - AdBlock client-side engelleme
- `adsbygoogle_undefined` - Google Adsense yüklenemedi
- `pauseAdRequests_active` - AdBlock pauseAdRequests = 1
- `bait_hidden` - 3+ bait element gizlenmiş

**Ignore edilenler:**
- `CORS` - Cross-origin hatası
- `net::ERR_FAILED` - Network hatası
- `timeout` - 3s timeout
- `204` - No content

### 7.4 Neden Score Reset?

Script başarıyla yüklendiğinde score düşürülür çünkü:
- AdBlock tam kapasite çalışmıyor demektir
- Google Ads scripti engellenmemiş
- Sadece fallback detection (bait elements) çalışmış olabilir

### 7.5 Laravel Debug Log Sistemi

**Endpoint:** `POST /adblock/debug`

**Loglama:**
```php
Log::info('[AdBlock Debug] ' . json_encode($logData));
Log::info("[AdBlock Debug] IP: {$ip} | UA: {$userAgent}");
Log::info("[AdBlock Debug] Score: {$score} | Signals: " . implode(', ', $signals));
Log::info("[AdBlock Debug] Result: {$result} | Detected: " . ($adBlockDetected ? 'YES' : 'NO'));
Log::info("[AdBlock Debug] Checks - Script: ..." );
```

**Log konumu:** `storage/logs/laravel.log`

**Gönderilen payload:**
```json
{
  "score": 0,
  "signals": [],
  "result": "CLEAN",
  "checks": {
    "scriptLoaded": true,
    "adsenseExists": true,
    "baitVisible": true
  },
  "logs": [...],
  "user_agent": "Mozilla/5.0...",
  "url": "https://...",
  "adBlockDetected": false
}
```

---

## 8. LOGGING SİSTEMİ

### 8.1 Frontend Console Log Sistemi

**AdBlock Detector:**
```javascript
this.log('START', 'Detection started');
this.log('STEP', '=== CHECK 1: Google Script Load ===');
this.log('SUCCESS', 'Google script loaded successfully');
this.log('BLOCKER', 'ERR_BLOCKED_BY_CLIENT detected');
this.log('IGNORE', 'CORS/Network error - not counting');
this.log('RESET', 'Script loaded successfully, score reset to: X');
this.log('FINAL', 'Score: X, Signals: [Y, Z]');
this.log('BACKEND', 'Sent to Laravel: score=X, result=Y');
```

### 8.2 Laravel.log Sistemi

**Konum:** `storage/logs/laravel.log`

**Log Seviyeleri:**
- `Log::info()` - Genel bilgi
- `Log::warning()` - Uyarılar
- `Log::error()` - Hatalar

**Örnek log entries:**
```
[2026-05-19 12:00:00] local.INFO: [AdBlock Debug] {"ip":"...","score":0,"result":"CLEAN"...}
[2026-05-19 12:00:01] local.INFO: [AdBlock Debug] IP: 127.0.0.1 | UA: Mozilla/5.0...
[2026-05-19 12:00:01] local.INFO: [AdBlock Debug] Score: 0 | Signals: 
```

### 8.3 Debug Akışları

1. **Frontend → Backend:**
   - adblock.js: console.log() + fetch to /adblock/debug
   - Laravel: Log::info() to storage/logs/laravel.log

2. **API Debug:**
   - `php artisan tinker` ile test
   - `Log::debug($variable)` ile manual log

---

## 9. GÜVENLİK YAPISI

### 9.1 Validation

**SQL Injection Koruması:**
- Middleware: `sql.protect` (routes/web.php, routes/api.php)
- Otomatik olarak tehlikeli SQL pattern'leri engeller

**Request Validation:**
- API: FormRequest class'ları ile validation
- Sanitize: strip_tags, preg_replace

### 9.2 CSRF

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

JS'te kullanım:
```javascript
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
}
```

### 9.3 XSS Önlemleri

- Blade'de `{{ $variable }}` otomatik escaping
- `{!! $variable !!}` raw output için dikkatli kullanım
- Input sanitization: `strip_tags()`

### 9.4 Rate Limit Eksikleri

**Mevcut:**
- API: `throttle:60,1` (60 req/dk)
- Auth: `throttle:login` (5 attempt/5 dk)

**Eksikler:**
- Web routes'ta rate limit yok
- IP-based throttle yok (API hariç)
- Honeypot yok

---

## 10. PERFORMANCE YAPISI

### 10.1 Cache Sistemi

**Redis:**
- Location, Taxonomy, LiveData cache
- ExternalDataService Redis kullanır

**Laravel Cache:**
```php
Cache::put($key, $value, $ttl);
Cache::get($key);
```

**TTL:** 3600s (SeoService::CACHE_TTL)

### 10.2 Vite Optimizasyonu

- `npm run build` - Production build
- Code splitting otomatik
- CSS purging (Tailwind)
- Asset hashing

### 10.3 Asset Yükleme Mantığı

1. Vite build → `public/build/`
2. `@vite(['resources/js/adblock.js'])` - Entry point
3. Lazy loading: Gerekli JS only
4. CDN: Tailwind CSS (CDN for dev, build for prod)

---

## 11. PLACEHOLDER SYNTAX SİSTEMİ

### 11.1 Desteklenen Placeholder'lar

| Placeholder | Tip | Örnek |
|-------------|-----|-------|
| `{city}` | Location | İstanbul |
| `{district}` | Location | Kadıköy |
| `{neighborhood}` | Location | Moda |
| `{category}` | Taxonomy | Kombi |
| `{usd_try}` | LiveDataVault | 32.50 |
| `{gold_gram}` | LiveDataVault | 2450 |
| `{weather_istanbul}` | ExternalDataService | Güneşli |
| `{option1\|option2\|option3}` | Spintax | Random |

### 11.2 Resolution Order

1. **Location placeholders** - `{city}`, `{district}`, `{neighborhood}`
2. **Taxonomy placeholders** - `{category}`, `{taxonomy}`
3. **Live Data Vault** - `{key_name}` from database
4. **External Data Service** - exchange rates, weather (cached)
5. **Spintax** - `{option1|option2|option3}`

---

## 12. DEPLOYMENT REHBERİ

### 12.1 Production Build

```bash
# 1. Dependencies
composer install --optimize-autoloader --no-dev

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate --force

# 4. Assets
npm install
npm run build

# 5. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 12.2 Queue Worker

```bash
# Indexing queue
php artisan queue:work --queue=indexing --sleep=3

# Default queue
php artisan queue:work
```

### 12.3 Nginx Önerileri

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/omni-portal/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 12.4 Cron Jobs (Opsiyonel)

```cron
* * * * * php /var/www/omni-portal/artisan schedule:run >> /dev/null 2>&1
```

---

## 13. MODEL YAPISI

### 13.1 Core Models

| Model | Tablo | Açıklama |
|-------|-------|----------|
| Taxonomy | taxonomies | Hiyerarşik kategoriler |
| Location | locations | İl/ilçe/mahalle |
| ContentNode | content_nodes | Yayınlanan içerikler |
| PostTemplate | post_templates | İçerik şablonları |
| LiveDataVault | live_data_vault | Canlı veri anahtar-değer |
| Keyword | keywords | SEO keywords |
| GlobalAdBlock | global_ad_blocks | Reklam scriptleri |
| User | users | Admin kullanıcıları |

### 13.2 İlişkiler

```php
// Taxonomy
$taxonomy->parent()          // belongsTo
$taxonomy->children()       // hasMany
$taxonomy->contentNodes()   // hasMany
$taxonomy->postTemplates()  // hasMany

// Location
$location->parent()         // belongsTo
$location->children()       // hasMany
$location->contentNodes()   // hasMany

// ContentNode
$content->taxonomy()        // belongsTo
$content->location()        // belongsTo
$content->postTemplate()    // belongsTo
$content->relatedNodes()    // hasMany
```

---

## 14. KRİTİK NOTLAR

### 14.1 Riskli Alanlar

1. **Rate Limiting Eksikliği:**
   - Web routes korumasız
   - Sadece API ve auth endpoint'lerde throttle

2. **AdBlock Detection Yanlış Pozitif:**
   - Slow network → timeout → false positive
   - Safari ITP → blocking → false positive

3. **Placeholder Security:**
   - Raw HTML injection riski
   - Spintax içinde XSS potansiyeli

4. **Database Performance:**
   - Büyük content node sayısında pagination yavaş
   - Index'ler kritik

### 14.2 Teknik Borçlar

1. **Test coverage yok** - Unit/feature test eksik
2. **Monitoring yok** - Sentry/Prometheus entegrasyonu yok
3. **Queue monitoring zayıf** - Filament widget var ama detaylı değil
4. **Redis zorunlu değil** - Optional, fallback var

### 14.3 Geliştirilmesi Gereken Yerler

1. **Admin Panel:**
   - Bulk edit özelliği
   - Hierarchical tree view
   - Preview sistemi

2. **SEO:**
   - Daha fazla schema type
   - Canonical tag customizasyonu
   - Internal linking otomasyonu

3. **Performance:**
   - Full-text search (Elasticsearch/Meilisearch)
   - Image optimization (Spatie media library)
   - HTTP/2 push

---

## 15. DOSYA YAPISI ÖZETİ

```
C:\SEO\
├── app\
│   ├── Http\Controllers\
│   │   ├── AdBlockDebugController.php
│   │   ├── ContentController.php
│   │   ├── TaxonomyController.php
│   │   ├── LocationController.php
│   │   └── Api\
│   ├── Models\
│   │   ├── ContentNode.php
│   │   ├── Taxonomy.php
│   │   ├── Location.php
│   │   └── ...
│   ├── Services\
│   │   ├── SeoService.php
│   │   ├── IndexingService.php
│   │   ├── SemanticLinkMatrix.php
│   │   └── ...
│   └── Helpers\
│       └── PlaceholderResolver.php
├── resources\
│   ├── js\
│   │   ├── adblock.js
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views\
│       ├── layouts\app.blade.php
│       ├── components\
│       └── ...
├── routes\
│   ├── web.php
│   └── api.php
├── config\
│   └── ...
└── docs\
    ├── omni_portal_master_spec.md
    ├── roadmap.md
    └── ...
```

---

## 16. PYTHON BOT SİSTEMLERİ

Proje kök dizininde iki adet Python veri enjektör botu bulunmaktadır. Bu botlar Laravel API üzerinden toplu veri yüklemesi yaparlar.

### 16.1 bombardiman.py

**Konum:** `C:\SEO\bombardiman.py`

**Amaç:** Programmatik SEO içerik üretimi - İl × Kategori kombinasyonları ile otomatik içerik oluşturma

**Temel Özellikler:**

| Özellik | Değer |
|---------|-------|
| BASE_URL | http://localhost:8000 |
| CONCURRENT_WORKERS | 15 (paralel thread) |
| API TOKEN | Sanctum token |
| Batch Size | 10 içerik/request |

**Veri Setleri:**

**İller (Örnek):**
```python
iller = [
    {"name": "Adana", "slug": "adana"},
    {"name": "Adıyaman", "slug": "adiyaman"},
    {"name": "İstanbul", "slug": "istanbul"},
    {"name": "İzmir", "slug": "izmir"},
    # ... 81 il
]
```

**Kategoriler:**
```python
kategoriler = [
    {"name": "Klima Servisi", "slug": "klima-servisi", "keywords": ["Klima Servisi", "Klima Montaj", "Klima Bakim"]},
    {"name": "Kombi Servisi", "slug": "kombi-servisi", "keywords": ["Kombi Tamiri", "Kombi Bakimi"]},
    {"name": "Eczane", "slug": "eczane", "keywords": ["Nobetci Eczane", "Acil Eczane"]},
    {"name": "Tesisatci", "slug": "tesisatci", "keywords": ["Su Tesisatcisi", "Tuvalet Tikanikligi"]},
]
```

**Body Templates:**
```python
body_templates = [
    "{il} bölgesinde {keyword} arayanlar için en güncel veritabanını hazırladık.",
    "{il} lokasyonunda {keyword} sorgulamanıza profesyonel cevaplar.",
    "{keyword} için {il} sakinlerine özel kapsamlı bilgilendirme."
]
```

**slugify() Fonksiyonu:**
Türkçe karakterleri normalize eder:
- ı → i, ş → s, ğ → g, ç → c, ö → o, ü → u
- Boşluk ve special char'ları `-` ile değiştirir

**Akış (İşleyiş):**

```
1. setup_basics()
   ├── Taxonomies yükle → /api/v1/ingest (taxonomies[])
   └── Locations yükle → /api/v1/ingest (locations[])

2. process_city(city_data) - Her il için paralel çalışır
   ├── Kategorileri döngüyle gez
   ├── Her kategori için keyword'leri dön
   ├── Title: "{il} {keyword}" (örn: "İstanbul Klima Servisi")
   ├── Slug: "{il}-{keyword}" (örn: "istanbul-klima-servisi")
   ├── Body: Template'ten random seçim + {il}, {keyword} replace
   └── Batch: 10'lu gruplar halinde /api/v1/ingest (content_nodes[])

3. ThreadPoolExecutor
   └── max_workers=15 ile paralel işleme
```

**API Payload Yapısı:**

```python
# Taxonomies
{
    "taxonomies": [
        {"name": "Klima Servisi", "slug": "klima-servisi"},
        {"name": "Kombi Servisi", "slug": "kombi-servisi"}
    ]
}

# Locations
{
    "locations": [
        {"name": "İstanbul", "slug": "istanbul", "type": "city"}
    ]
}

# Content Nodes
{
    "content_nodes": [
        {
            "title": "İstanbul Klima Servisi",
            "slug": "istanbul-klima-servisi",
            "body_content": "İstanbul bölgesinde...",
            "is_restricted_content": False,
            "taxonomy_slug": "klima-servisi",
            "location_slug": "istanbul",
            "published_at": "2026-05-19 12:00:00"
        }
    ]
}
```

**Kullanım:**
```bash
python bombardiman.py
```

**Çıktı:**
```
🔥 Programmatik Motor Marş Bastı!
📋 [1/3] Kategoriler yükleniyor...
Kategoriler Sonuç: 200
📍 [2/3] İller ve Lokasyonlar yükleniyor...
Lokasyonlar Sonuç: 200
🚀 Veri Bombardımanı Başlıyor...
----------------------------------------
🏁 İŞLEM TAMAM!
✅ Toplam Kayıt: 405
📍 Etkilenen İl: 81
```

---

### 16.2 enjektor.py

**Konum:** `C:\SEO\enjektor.py`

**Amaç:** Daha kapsamlı veri enjeksiyonu - 81 il × 20 kategori × 5 keyword = 8100+ içerik

**Temel Özellikler:**

| Özellik | Değer |
|---------|-------|
| BASE_URL | http://localhost:8000 |
| CONCURRENT_WORKERS | 20 (paralel thread) |
| API TOKEN | Sanctum token |
| Batch Size | 1 içerik/request (bireysel) |

**Farkları (bombardiman.py vs enjektor.py):**

| Özellik | bombardiman.py | enjektor.py |
|---------|----------------|-------------|
| İl sayısı | 12 (örnek) | 81 (tam liste) |
| Kategori sayısı | 5 | 20 |
| Keyword/Kategori | 3 | 5 |
| Batch yöntemi | 10'lu gruplar | Tek tek (bireysel) |
| Slug üretimi | `{il}-{keyword}` | `{il}-{keyword}-{random5basamak}` |
| Thread workers | 15 | 20 |
| Body template | 3 adet | 8 adet |

**Kategoriler (enjektor.py - 20 adet):**
```python
kategoriler = [
    {"name": "Klima Servisi", "slug": "klima-servisi"},
    {"name": "Doğalgaz", "slug": "dogalgaz"},
    {"name": "Kombi Servisi", "slug": "kombi-servisi"},
    {"name": "Eczane", "slug": "eczane"},
    {"name": "Altin Fiyatlari", "slug": "altin-fiyatlari"},
    {"name": "Hava Durumu", "slug": "hava-durumu"},
    {"name": "Tesisatci", "slug": "tesisatci"},
    {"name": "Elektrikci", "slug": "elektrikci"},
    {"name": "Böcek İlaçlama", "slug": "boccek-ilaclama"},
    {"name": "Güvenlik Kamerası", "slug": "guvenlik-kamerasi"},
    {"name": "Şofben", "slug": "sofben"},
    {"name": "Petek Temizligi", "slug": "petek-temizligi"},
    {"name": "Nakliye", "slug": "nakliye"},
    {"name": "Oto Kurtarma", "slug": "oto-kurtarma"},
    {"name": "Bebek Bakıcı", "slug": "bebek-bakici"},
    {"name": "Halı Yıkama", "slug": "hali-yikama"},
    {"name": "Marangoz", "slug": "marangoz"},
    {"name": "Nefroloji", "slug": "nefroloji"},
    {"name": "Ortopedi", "slug": "ortopedi"},
    {"name": "Boya Badana", "slug": "boya-badana"},
]
```

**İl Listesi (81 il - tam liste):**
```python
iller = [
    "Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Aksaray", "Amasya",
    "Ankara", "Antalya", "Ardahan", "Artvin", "Aydın", "Balıkesir",
    "Bartın", "Batman", "Bayburt", "Bilecik", "Bingöl", "Bitlis",
    "Bolu", "Burdur", "Bursa", "Çanakkale", "Çankırı", "Çorum",
    "Denizli", "Diyarbakır", "Düzce", "Edirne", "Elazığ", "Erzincan",
    "Erzurum", "Eskişehir", "Gaziantep", "Giresun", "Gümüşhane", "Hakkari",
    "Hatay", "Isparta", "Mersin", "İstanbul", "İzmir", "Kars",
    "Kastamonu", "Kayseri", "Kırklareli", "Kırşehir", "Kilis", "Kocaeli",
    "Konya", "Kütahya", "Malatya", "Manisa", "Kahramanmaraş", "Mardin",
    "Muğla", "Muş", "Nevşehir", "Niğde", "Ordu", "Osmaniye",
    "Rize", "Samsun", "Şanlıurfa", "Şırnak", "Tekirdağ", "Tokat",
    "Trabzon", "Tunceli", "Uşak", "Van", "Yalova", "Zonguldak",
    "Karabük", "Karaman", "Kırıkkale", "Yozgat", "Sinop"
]
```

**Slug Üretimi (Önemli Fark):**

```python
# bombardiman.py - Statik slug (tekrarlandığında aynı)
slug = slugify(f"{il_name}-{keyword}")
# Çıktı: istanbul-klima-servisi

# enjektor.py - Random suffix ile unique slug
slug = slugify(f"{title}-{random.randint(10000, 99999)}")
# Çıktı: istanbul-klima-servisi-84723
```

**Body Template Örnekleri:**
```python
body_templates = [
    "{il} bolgesinde {keyword} arayanlar icin en guncel veritabanini hazirladik. {il} ilcesinde hizmet veren en iyi {keyword} firmalari burada.",
    "{il} lokasyonunda {keyword} hizmeti arayanlara en kapsamli rehberi sunuyoruz. Uzman ekiplerimiz {keyword} konusunda en guncel bilgileri derledik.",
    "Biliyorsunuz ki {il} icinde {keyword} konusu oldukca populer. Sizler icin tum detaylari ve {keyword} hizmeti veren firmalari derledik.",
    "{keyword} icin {il}'de en hizli ve guvenilir hizmeti sunan firmalari bulmak artik cok kolay.",
    # ... toplam 8 template
]
```

**Akış:**

```python
def setup():
    # 1. Tüm kategorileri yükle
    # 2. Tüm illeri yükle
    # 3. İçerik ekleme başla

def process_il(il_data):
    for kat in kategoriler:
        for keyword in kat["keywords"][:5]:  # İlk 5 keyword
            # Title, slug, body üret
            # Tek tek API'ye gönder (retry ile)
    return count
```

**Kullanım:**
```bash
python enjektor.py
```

**Çıktı:**
```
============================================================
🚀 VERI TABANI OLUSTURULUYOR
============================================================
[1/3] Kategoriler: 200
[2/3] Iller: 200
[3/3] Icerikler ekleniyor...
------------------------------------------------------------
============================================================
✨ TAMAMLANDI!
📊 Toplam Icerik: 7452
📍 Il sayisi: 81
📂 Kategori sayisi: 20
🔢 Potansiyel: 8100
```

---

### 16.3 Bot Güvenlik Ayarları

**API Token:**
Her iki bot'ta aynı token formatı kullanılır:
```python
API_TOKEN = "1|F4cGvlwfr1HE9dQ0FqoXcGh6gM9dJJY90wYxI1pz4ada8003"
# Format: {user_id}|{token}
```

**Headers:**
```python
headers = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
```

---

### 16.4 Hata Yönetimi

**send_request() - bombardiman.py:**
```python
def send_request(endpoint, payload):
    try:
        r = requests.post(f"{BASE_URL}/api/v1/ingest", json=payload, headers=headers, timeout=30)
        return r.status_code, r.text
    except Exception as e:
        return 0, str(e)
```

**process_il() - enjektor.py:**
```python
try:
    r = requests.post(f"{BASE_URL}/api/v1/ingest", json={"content_nodes": [node]}, headers=headers, timeout=30)
    if r.status_code == 200:
        count += 1
except:
    pass  # Silent fail - retry yok
```

---

### 16.5 Bot Seçim Kılavuzu

| Senaryo | Bot Önerisi |
|---------|-------------|
| Test/Demo veri (az) | bombardiman.py |
| Tam production (81 il × 20 kat) | enjektor.py |
| Unique slug zorunlu | enjektor.py |
| Batch insert (hızlı) | bombardiman.py |
| 81 il tam listesi | enjektor.py |

---

### 16.6 Ortam Değişkenleri

Bot çalıştırmadan önce güncelle:
```python
# Doğru URL ve Token kullan
BASE_URL = "http://localhost:8000"  # Dev
BASE_URL = "https://siten.com"       # Prod

API_TOKEN = "1|..."  # Laravel Sanctum token
```

Token alma:
```bash
curl -X POST http://localhost:8000/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "password"}'
```

---

## 17. QUICK START

### Geliştirme Ortamı Kurulumu

```bash
# 1. Clone & install
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate
php artisan db:seed

# 4. Run
php artisan serve
npm run dev
```

### Test Hesapları

- Email: `test@example.com`
- Password: `password`

### Admin Panel

- URL: `/admin`
- Giriş yapıldıktan sonra CRUD yönetimi

### API Test

```bash
# Token al
curl -X POST http://localhost:8000/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "password"}'

# Ingest
curl -X POST http://localhost:8000/api/v1/ingest \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"taxonomies": [...]}'
```

### Python Bot Çalıştırma

```bash
# Test/Demo (az veri)
python bombardiman.py

# Full production (8100+ içerik)
python enjektor.py
```

---

**Son Güncelleme:** 2026-05-19  
**Versiyon:** 1.1  
**Durum:** Production Ready