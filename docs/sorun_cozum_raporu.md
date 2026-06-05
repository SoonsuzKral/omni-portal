# Omvi Portal - Sorun & Çözüm Raporu

**Tarih:** 2026-06-05  
**Hazırlayan:** TrendSense Bot / Akilli Bot Entegrasyon Ekibi  
**Proje:** Omvi Portal (https://omviportal.com)

---

## İçindekiler
1. [Kritik Sorunlar ve Çözümleri](#1-kritik-sorunlar-ve-çözümleri)
2. [Yapılan Değişiklikler](#2-yapılan-değişiklikler)
3. [Bot Çalıştırma Talimatları](#3-bot-çalıştırma-talimatları)
4. [SEO İyileştirmeleri](#4-seo-iyileştirmeleri)
5. [Backlog / Yapılacaklar](#5-backlog--yapılacaklar)

---

## 1. KRİTİK SORUNLAR VE ÇÖZÜMLERİ

### SORUN 1: API 401 Unauthorized (ÇÖZÜLDÜ ✅)

**Belirti:** TrendSense bot log'unda `API error 401: {"message":"Unauthenticated."}`  
**Kök neden:** Bot `Authorization: Bearer <statik_token>` gönderiyordu ama Laravel Sanctum sadece `personal_access_tokens` tablosundaki gerçek token'ları kabul ediyordu.

**Çözüm:** Yeni `ApiTokenAuth` middleware yazıldı (`app/Http/Middleware/ApiTokenAuth.php`):
- Önce Sanctum token dener
- Sanctum başarısız olursa statik `OMNI_API_TOKEN` ile karşılaştırır
- Eşleşirse admin@omviportal.com kullanıcısını oturum açar

**Değişen dosyalar:**
| Dosya | Değişiklik |
|---|---|
| `app/Http/Middleware/ApiTokenAuth.php` | ✨ YENİ — Dual auth middleware |
| `bootstrap/app.php` | `api.token` alias eklendi |
| `config/app.php` | `omni_api_token` config değişkeni eklendi |
| `routes/api.php` | `auth:sanctum` → `api.token` değiştirildi |

---

### SORUN 2: Ingest API Validasyon Eksik (ÇÖZÜLDÜ ✅)

**Belirti:** Bot `language`, `source`, `meta_description` alanlarını gönderiyordu ama API validasyonu bu alanları kabul etmiyordu.

**Çözüm:** `IngestController.php` validasyonuna `meta_description`, `language`, `source` alanları eklendi ve ContentNode create/update'ine `language` ve `source` kaydı eklendi.

---

### SORUN 3: Bot Payload'ında Eksik Alanlar (ÇÖZÜLDÜ ✅)

**Belirti:** TrendSense_Bot.py `_build_payload` fonksiyonu `language` ve `source` alanlarını içermiyordu.

**Çözüm:** `TrendSense_Bot.py`'da `_build_payload`'a `language` ve `source` eklendi.

---

### SORUN 4: Akıllı Bot Hiç Veri Göndermemiş (ÇÖZÜM BEKLİYOR)

**Belirti:** `akilli_bot.log` her zaman `DRY RUN — API çağrısı yapılmayacak` ile bitiyor.  
**Kök neden:** Bot her seferinde `--dry-run` ile çalıştırılmış.  
**Çözüm:** `--dry-run` olmadan çalıştır:
```bash
python bots/akilli_bot.py --quick
```

---

### SORUN 5: `.env.production` Yapılandırma Eksik (SUNUCUDA DÜZELTİLMELİ)

**Durum:** Sunucudaki `.env` dosyasında aşağıdaki değişkenler eksik:

| Değişken | Durum | Yapılması Gereken |
|---|---|---|
| `APP_KEY` | Boş | `php artisan key:generate` ile üret |
| `OMNI_API_TOKEN` | Eksik | `iVLpTL6FVkDfl7IBq0yi6HD4QGUA0erM4bhWHOY6e8f0fd34` ekle |
| `OMNI_BASE_URL` | Eksik | `https://omviportal.com` ekle |
| `DB_DATABASE` | Placeholder | Gerçek DB adını yaz |
| `DB_USERNAME` | Placeholder | Gerçek DB kullanıcısını yaz |
| `DB_PASSWORD` | Placeholder | Gerçek DB şifresini yaz |

---

## 2. YAPILAN DEĞİŞİKLİKLER

### 2.1. Backend (PHP/Laravel)

| # | Dosya | İşlem | Açıklama |
|---|---|---|---|
| 1 | `app/Http/Middleware/ApiTokenAuth.php` | ✨ Yeni | Dual auth middleware (Sanctum + statik token) |
| 2 | `bootstrap/app.php` | ✏️ Düzenle | `api.token` alias eklendi |
| 3 | `config/app.php` | ✏️ Düzenle | `omni_api_token` config eklendi |
| 4 | `routes/api.php` | ✏️ Düzenle | `auth:sanctum` → `api.token` |
| 5 | `app/Http/Controllers/Api/IngestController.php` | ✏️ Düzenle | `language`, `source`, `meta_description` validasyon + kayıt |
| 6 | `.gitignore` | ✏️ Düzenle | Cache, cert, checkpoint, sitemap dosyaları eklendi |

### 2.2. Bot (Python)

| # | Dosya | İşlem | Açıklama |
|---|---|---|---|
| 7 | `TrendSense_Bot.py` | ✏️ Düzenle | `_build_payload`'a `language` + `source` alanları eklendi |

---

## 3. BOT ÇALIŞTIRMA TALİMATLARI

### 3.1. TrendSense Bot
```bash
# Checkpoint sıfırla (gerekirse)
del trendsense_checkpoint.json

# Dry-run test
python TrendSense_Bot.py --dry-run

# LIVE çalıştır
python TrendSense_Bot.py

# Hızlı mod (ilk 10 keyword)
python TrendSense_Bot.py --quick

# Kaldığı yerden devam
python TrendSense_Bot.py --resume
```

### 3.2. Akıllı Bot
```bash
# Hızlı test (500 node)
python bots/akilli_bot.py --quick

# Tam çalıştırma (29M node)
python bots/akilli_bot.py

# Kuru çalıştırma (test için)
python bots/akilli_bot.py --dry-run
```

---

## 4. SEO İYİLEŞTİRMELERİ

### 4.1. Yapılanlar
- [x] API auth çözüldü → botlar veri gönderebilecek
- [x] GitHub'a push → webhook ile otomatik deploy

### 4.2. Yapılması Gerekenler

#### Acil (Bu Hafta)
- [ ] **Google Search Console'a site ekle**: `https://search.google.com/search-console`
- [ ] **DNS'e TXT kaydı** ekleyerek site sahipliğini doğrula
- [ ] **`page_views` sıfırla**: `UPDATE content_nodes SET page_views = 0;`
- [ ] **IndexNow API test**: curl ile manuel URL gönder
- [ ] **Sunucu `.env` düzelt**: APP_KEY, OMNI_API_TOKEN, DB bilgileri

#### Orta Vade (1-2 Hafta)
- [ ] Ana sayfa başlığını Türkçe yap: "Omvi Portal - Şehrini Keşfet"
- [ ] "Featured Cities" kısmını Türk şehirleriyle değiştir (HomeController)
- [ ] `content/show.blade.php`'ye canonical override ekle
- [ ] `SeoService.php`'den fake view interactionStatistic kaldır
- [ ] JSON-LD Article schema düzelt
- [ ] Sosyal medya hesapları aç (Twitter/X, Instagram)

#### Uzun Vade (1 Ay+)
- [ ] Backlink stratejisi: Türk forumları, bloglar, dizin siteleri
- [ ] Google News başvurusu
- [ ] YouTube kanalı + video içerik
- [ ] Görsel ekleme (featured_image)
- [ ] Çoklu dil hreflang implementasyonu

### 4.3. Mevcut Site Durumu
| Metrik | Değer |
|---|---|
| Toplam Sayfa | 118,278 |
| Kategori | 121 |
| Lokasyon | 224 |
| Site Yaşı | ~10 gün |
| Günlük Hit | 0 (Google indekslemedi) |

---

## 5. BACKLOG / YAPILACAKLAR

- [ ] **Bootstrap/cache sorunu**: `.gitignore`'a eklendi ama eski sürümlerde repo'da var. Yeni clone'larda sorun olmaz.
- [ ] **EnhancedRateLimiter**: Middleware var ve çalışıyor. API isteklerini 60/dk ile sınırlar.
- [ ] **Akıllı Bot ilk çalıştırma**: 29M node, tahmini süre ~günler. `--quick` ile başla.
- [ ] **nginx.conf**: Sunucu config'i gözden geçirilmeli.
- [ ] **PHP 8.1**: Hostinger'da PHP 8.1 kullanılıyor, Laravel 12 uyumlu.
- [ ] **Redis eksik**: Paylaşımlı hostingde Redis yok, file cache kullanılıyor.
- [ ] **AdSense**: Hesap onayı gelmedi, pasif.

---

*Bu doküman `docs/sorun_cozum_raporu.md` olarak kaydedilmiştir.  
Her oturum başında okunup kaldığı yerden devam edilmelidir.*
