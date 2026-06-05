# Reklam Yönetim Sistemi

## Genel Bakış

Projede iki katmanlı bir reklam sistemi vardır:

1. **Eski Doğrudan AdSense** — `config/services.php` içindeki (`adsense.*`) sabit kodlu slotlar.
2. **Dinamik DB Tabanlı Sistem** — `GlobalAdBlock` modeli + `<x-ad-slot>` bileşeni ile admin panelinden tam kontrol.

Bu doküman **dinamik `x-ad-slot` sistemini** kapsar (tüm yeni yerleşimler için önerilir).

---

## `x-ad-slot` Nasıl Çalışır

Bileşen bir `name` parametresi alır, bunu `GlobalAdBlock.position` değerine eşler ve o pozisyondaki tüm aktif script'leri render eder.

```blade
<x-ad-slot name="sidebar-right-top" />
```

İç işleyiş:
1. `name` değerini pozisyon map'inde arar (ör. `sidebar-right-top` → `right_sidebar_top`).
2. `GlobalAdBlock WHERE active=1 AND position='right_sidebar_top'` sorgusunu çalıştırır.
3. Sonucu 3600 saniye cache'ler.
4. Her script'i anlamsal bir `<div>` kapsayıcı içinde basar.

---

## Kullanılabilir Slot İsimleri

| Slot İsmi             | DB Pozisyonu        | Konteynır Sınıfı          |
|-----------------------|---------------------|---------------------------|
| `sidebar-right-top`   | `right_sidebar_top` | `.ad-slot.ad-slot-vertical` |
| `sidebar-right-mid`   | `right_sidebar_mid` | `.ad-slot.ad-slot-vertical` |
| `sidebar-right-bottom`| `right_sidebar_bottom` | `.ad-slot.ad-slot-vertical` |
| `sidebar-left-top`    | `left_sidebar_top`  | `.ad-slot.ad-slot-vertical` |
| `sidebar-left-mid`    | `left_sidebar_mid`  | `.ad-slot.ad-slot-vertical` |
| `sidebar-left-bottom` | `left_sidebar_bottom` | `.ad-slot.ad-slot-vertical` |
| `content-top`         | `above_content`     | `.ad-slot`                |
| `content-bottom`      | `below_content`     | `.ad-slot`                |
| `after-h1`            | `under_h1`          | `.ad-slot`                |
| `after-breadcrumb`    | `after_breadcrumb`  | `.ad-slot`                |
| `above-footer`        | `above_footer`      | `.ad-slot`                |
| `below-footer`        | `below_footer`      | `.ad-slot`                |
| `header-left`         | `header_left`       | `.ad-slot`                |
| `header-right`        | `header_right`      | `.ad-slot`                |
| `footer-left`         | `footer_left`       | `.ad-slot`                |
| `footer-right`        | `footer_right`      | `.ad-slot`                |
| `sticky-bottom`       | `sticky_bottom`     | `.ad-slot-sticky.fixed.bottom-0` |
| `sticky-left`         | `sticky_left`       | `.ad-slot-sticky.fixed.left-0` |
| `sticky-right`        | `sticky_right`      | `.ad-slot-sticky.fixed.right-0` |
| `mid-content-1`       | `mid_content_1`     | `.ad-slot`                |
| `mid-content-2`       | `mid_content_2`     | `.ad-slot`                |
| `mid-content-3`       | `mid_content_3`     | `.ad-slot`                |

Eğer isim map'te bulunamazsa, doğrudan DB pozisyonu olarak kullanılır (fallback).

---

## Yeni Reklam Alanı Ekleme

### Adım 1 — DB Pozisyonunu Kaydet

`app/Models/GlobalAdBlock.php` dosyasındaki `POSITIONS` dizisine yeni pozisyon anahtarını ekle:

```php
const POSITIONS = [
    // ... mevcut pozisyonlar ...
    'my_new_spot' => '🆕 Yeni Alanım',
];
```

### Adım 2 — (İsteğe Bağlı) İsim Takma Adı Ekle

Kebab-case bir kısayol istiyorsan, `resources/views/components/ad-slot.blade.php` içindeki `$positionMap` dizisine bir girdi ekle:

```php
$positionMap = [
    // ... mevcut eşleştirmeler ...
    'yeni-alanim' => 'my_new_spot',
];
```

### Adım 3 — Blade'e Yerleştir

```blade
<x-ad-slot name="yeni-alanim" />
```

### Adım 4 — Admin Paneli Kaydı

**Global Ad Blocks** → **Oluştur** sayfasına git ve doldur:
- **Name** — açıklayıcı etiket (örn. "Yeni Alanım")
- **Position** — açılır menüden `my_new_spot` seç
- **Script** — reklam kodunu yapıştır (AdSense `<ins>` + `<script>` veya özel HTML/JS)
- **Network Type** — `Safe` veya `Restricted`
- **Active** — ✅
- **Is Global** — ✅

Kaydet. Reklam bir sonraki sayfa yüklemesinde görünmeye başlar (cache, kaydetme/silme işleminde otomatik temizlenir).

---

## Admin Paneli — Global Ad Blocks

> **Model: `GlobalAdBlock`** (`App\Models\GlobalAdBlock`)
> **Filament Kaynağı:** (yoksa oluştur: `app/Filament/Resources/GlobalAdBlockResource.php`)

Eğer Filament kaynağı henüz yoksa, şu alanlarla manuel olarak oluştur:

| Alan            | Tip          | Notlar                                    |
|-----------------|-------------|-------------------------------------------|
| `name`          | Metin        | Dahili etiket                             |
| `position`      | Seçim       | `POSITIONS` anahtarlarından biri           |
| `script`        | Kod editörü | Ham reklam HTML/JS (AdSense, özel, vb.)   |
| `network_type`  | Seçim       | `Safe` veya `Restricted`                  |
| `active`        | Aç/Kapa     | Silmeden etkinleştir/devre dışı bırak      |
| `is_global`     | Aç/Kapa     | Site geneli vs taksonomiye özel            |
| `taxonomy_id`   | Seçim       | (İsteğe bağlı) Belirli bir kategoriye kısıtla |

### Tinker ile Hızlı CRUD

```bash
php artisan tinker
>>> GlobalAdBlock::create([
    'name' => 'Sağ Sidebar Üst',
    'position' => 'right_sidebar_top',
    'script' => '<ins class="adsbygoogle" ...></ins><script>...</script>',
    'network_type' => 'Safe',
    'active' => true,
    'is_global' => true,
]);
```

---

## Layout'daki Mevcut Yerleşimler

`resources/views/layouts/app.blade.php`:

```
Satır ~152:   <x-ad-renderer position="global_header" />   (eski — dokunma)
Satır ~253:   <x-ad-slot name="content-top" />
Satır ~255:   @yield('content')
Satır ~257:   <x-ad-slot name="content-bottom" />
Satır ~272:   <x-ad-slot name="above-footer" />
Satır ~310:   <x-ad-slot name="footer-left" />  (footer .grid içinde)
Satır ~320:   <x-ad-slot name="footer-right" /> (footer .grid içinde)
```

Alt görünümler (child views) aynı `name` konvansiyonunu kullanarak ihtiyaç duydukları yere ek slot ekleyebilir.
