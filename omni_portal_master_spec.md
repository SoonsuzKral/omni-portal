# Programmatic Ecosystem / Omni-Portal
## Master Product & Technical Specification

**Sürüm:** 1.0  
**Amaç:** Claude Code veya benzeri bir kod ajanının sıfırdan uygulamayı inşa edebilmesi için, proje kapsamını, veri modelini, iş kurallarını, mimariyi, içerik üretim mantığını, reklam karar mekanizmasını ve geliştirme sırasını tek belgede tanımlamak.

---

## 1. Projenin Özeti

Bu proje klasik bir blog sitesi değil, **programatik SEO odaklı devasa bir bilgi ve otomasyon portalıdır**. Ana hedef; Türkiye odaklı ve mümkün olduğunda çok daha geniş uzun kuyruklu arama sorgularını, sistematik şekilde sayfalara dönüştürmek, bu sayfaları ölçeklenebilir biçimde yayınlamak, canlı verilerle güncellemek ve ziyaretçi trafiğini gelir üreten bir yapıya çevirmektir.

Sistem; içerik, lokasyon, taksonomi, canlı veri, şablonlar, reklam blokları ve otomasyon botlarını tek bir ekosistemde toplar. İçerikler manuel olarak tek tek yazılmak yerine, veri + şablon + otomasyon yaklaşımıyla üretilir.

---

## 2. Temel Ürün Vizyonu

Portalın hedefi sadece okunmak değil, aynı zamanda:

- arama motorlarından geniş hacimde trafik almak,
- çok sayıda niş sorguya cevap vermek,
- lokasyon bazlı ve veri bazlı sayfalar üretmek,
- canlı verilerle sayfaları güncel tutmak,
- reklam yerleşimlerini sayfa türüne göre akıllı biçimde yönetmek,
- ileride büyüyebilecek bir içerik altyapısı oluşturmak.

Bu sistemin çekirdeği şudur:

1. **Taksonomi ağacı** ile konu kümeleri oluştur.
2. **Lokasyon ağacı** ile şehir / ilçe / mahalle hiyerarşisi kur.
3. **Canlı veri kasası** ile anlık değerleri sakla.
4. **Makale şablonları** ile yüzlerce binlerce sayfa formatı üret.
5. **Content node** tablosunda asıl yayınlanan sayfaları tut.
6. **Global ad blocks** ile reklamları sayfa türüne göre yönlendir.

---

## 3. Ana İş Modeli

Bu portalın amacı içerik üretmek değil, **içeriği trafik ve gelir motoruna çevirmektir**.

### 3.1 Trafik Stratejisi
- Long-tail SEO sorgularını hedeflemek.
- Lokasyonlu sorgular üretmek.
- Canlı veri içeren aramaları hedeflemek.
- Şablon tabanlı geniş ölçekli sayfalar üretmek.
- Her konu kümesini binlerce varyasyona açmak.

### 3.2 Gelir Stratejisi
Sistem reklam yerleşiminde içerik türüne göre karar verir:

- **Temiz / kurumsal / güvenli içerikler:** standart display/native reklam ağları.
- **Hassas / yetişkin / riskli içerikler:** uygun uyumlulukla farklı reklam ağı veya bu içeriklerde reklamın kapatılması / değiştirilmesi.
- Sayfa türüne göre farklı reklam blokları seçilir.
- Reklam blokları panelden yönetilir, kod içine gömülmez.

> Not: Bu doküman reklam ağı seçiminin iş mantığını anlatır; gerçek entegrasyon aşamasında uyumluluk, platform politikaları ve yerel mevzuat kontrol edilmelidir.

---

## 4. Teknoloji Yığını

### Backend
- **Laravel 11**
- PHP 8.3+
- Eloquent ORM
- Queue / Job sistemi
- Cache katmanı: Redis veya Memcached
- API katmanı: Sanctum ile güvenli giriş

### Admin Panel
- **Filament v3**
- CRUD yönetimi
- İçerik, taksonomi, lokasyon, canlı veri, şablon, reklam blokları yönetimi

### Frontend
- **Blade**
- **Tailwind CSS**
- Hafif Vanilla JS
- SEO dostu, hızlı, minimal ama yoğun içerik odaklı arayüz

### Bot / Otomasyon
- Python tabanlı harici veri itici
- Laravel API üzerinden içerik gönderimi
- Büyük hacimli veri ingest işlemleri
- Rate limit, token güvenliği ve doğrulama ile çalışır

---

## 5. Sistem Bileşenleri

## 5.1 Taksonomi Sistemi
Taksonomi yapısı, portalın konu haritasıdır. Tek bir kategori ağacı yerine, çok katmanlı ve esnek bir yapı gerekir.

### Amaç
- Niche kategoriler
- Etiketler
- Alışveriş kümeleri
- Sağlık kümeleri
- Finans kümeleri
- Yerel kümeler

### Özellikler
- Parent / child ilişkisi
- Slug ile erişim
- Tür bazlı filtreleme
- SEO sayfa üretimi için kaynak

### Kullanım
- Kategori sayfaları
- Tag sayfaları
- Konu kümeleri
- Programatik landing page üretimi

---

## 5.2 Lokasyon Sistemi
Lokasyonlar il / ilçe / mahalle yapısında tek tabloda tutulur.

### Amaç
- Şehir bazlı sayfalar
- İlçe bazlı sayfalar
- Mahalle bazlı sayfalar
- Yerel hizmet veya yerel bilgi sayfaları

### Özellikler
- Parent / child ilişkisi
- City, district, neighborhood tipleri
- Lokasyon bazlı slug
- Konu + lokasyon birleşimiyle sayfa üretimi

### Kullanım
Örnek:
- “İstanbul’da su tesisatı”
- “Kadıköy’de diş klinikleri”
- “Bornova nöbetçi eczane rehberi”

---

## 5.3 Canlı Veri Kasası
Canlı veriler, sayfa içinde güncellenen dinamik değerleri temsil eder.

### Örnek veriler
- Döviz kuru
- Altın fiyatı
- Hava durumu
- Endeks değeri
- API’den çekilen kısa güncel değerler

### Amaç
- Makaleleri güncel tutmak
- Dinamik SEO içerikleri üretmek
- Şablon içinde değişken kullanmak

### Mantık
İçerikte bir değişken örneği:
- `{gram_altin}`
- `{usd_try}`
- `{hava_durumu_istanbul}`

Bu değişkenler render aşamasında canlı veri kasasından çözülür.

---

## 5.4 Post Template Sistemi
Şablonlar, makale iskeletleridir.

### Amaç
- Tek bir şablondan yüzlerce içerik üretmek
- Başlık varyasyonları oluşturmak
- Body markup içinde blokları tekrar kullanmak

### İçerik yapısı
- Slug mask
- Title mask
- Body markup
- Taksonomi ile ilişki

### Kullanım
Bir şablon, farklı veri kombinasyonlarıyla farklı sayfalara dönüşür. Örneğin:
- şehir + hizmet
- ürün + kategori
- konu + canlı veri + lokasyon

---

## 5.5 Content Node Sistemi
Burası sitenin asıl yayınlanan içerik tablosudur. Trafik, index ve gelir burada birikir.

### İçerik alanları
- UUID
- Şablon referansı
- Taksonomi referansı
- Lokasyon referansı
- SEO başlığı
- Slug
- Body content
- Adult warning flag
- Page views
- Publish date

### Rolü
- Gerçek yayınlanan sayfalar burada tutulur
- Bot ve panel bu tabloya içerik yazar
- Frontend bu tablodan render eder
- Reklam mantığı bu tablo üzerinden karar verir

### İş kuralları
- Her içerik benzersiz slug taşımalı
- UUID unique olmalı
- Sayfa görüntüleme sayısı ayrı tutulmalı
- Yayın tarihi ile sıralama yapılmalı
- Yetişkin / hassas içeriklerde özel reklam davranışı uygulanmalı

---

## 5.6 Global Ad Blocks Sistemi
Reklam scriptleri ve yerleşim davranışı bu tablodan yönetilir.

### Amaç
- Reklam kodunu panelden değiştirmek
- Sayfa tipine göre reklam seçmek
- Yetişkin / normal içerik ayrımı yapmak
- Header, sidebar, in-article, sticky footer gibi alanları yönetmek

### Alanlar
- Network type
- Position key
- Ad script HTML
- Active flag

### Reklam Karar Mantığı
1. Sayfa content node’dan okunur.
2. `is_adult_warning` kontrol edilir.
3. İçerik güvenliyse normal reklam blokları seçilir.
4. İçerik hassassa uygun alternatif reklam mantığı uygulanır veya reklam kapatılır.
5. Position key’e göre blok frontend’de ilgili alana basılır.

---

## 6. Veri Modeli ve İlişki Mantığı

## 6.1 Taxonomies
- Self-referencing parent_id
- Child taxonomies mümkün
- Content node’lar ile ilişkili
- Template’ler ile ilişkili

## 6.2 Locations
- Self-referencing parent_id
- City -> district -> neighborhood yapısı
- Content node’lar ile ilişkili

## 6.3 Live Data Vault
- Variable name unique
- Value string olarak tutulabilir
- Render sırasında tip dönüşümü yapılabilir

## 6.4 Post Templates
- Bir taxonomy’ye bağlı olabilir
- Birden fazla content node üretmek için kaynak görevi görür

## 6.5 Content Nodes
- Bir post template’e bağlı
- Bir taxonomy’ye bağlı
- Opsiyonel location’a bağlı
- Frontend’in ana veri kaynağı

## 6.6 Global Ad Blocks
- Sayfa ve pozisyon bazlı reklam yönetimi
- Panelden aktif/pasif yapılabilir
- HTML script saklayabilir

---

## 7. Ölçeklenebilirlik ve Performans İlkeleri

Bu sistem milyonlarca satıra yaklaşabilecek şekilde tasarlanmalıdır.

### Temel prensipler
- Slug alanları indeksli olmalı
- Parent_id alanları indeksli olmalı
- Taxonomy / location / publish_date kombinasyonları indekslenmeli
- Sık sorgulanan canlı veriler cache üzerinden okunmalı
- İçerik listeleri cursor pagination veya optimized pagination ile çekilmeli
- Ağır işlemler queue üzerinden çalışmalı

### Performans hedefi
- İçerik render hızlı olmalı
- Sayfa başına minimum sorgu
- Reklam blokları mümkün olduğunca cache’lenmeli
- Bot ingest işlemleri toplu ve güvenli olmalı

---

## 8. Frontend Davranış Tasarımı

Frontend sade, hızlı ve içerik odaklı olmalı.

### Gereksinimler
- Mobile-first tasarım
- Okunabilir tipografi
- SEO uyumlu heading yapısı
- Breadcrumb yapısı
- Related content blokları
- Reklam alanları için yerleşim noktaları
- Sticky footer / side banner alanları
- İçeriğin arasında reklam slotları

### Sayfa Türleri
- Ana sayfa
- Taksonomi sayfası
- Lokasyon sayfası
- Taksonomi + lokasyon birleşik sayfası
- Content node detay sayfası
- Arama sayfası
- Etiket sayfası

---

## 9. İçerik Oluşturma ve Render Mantığı

Sistem içerik üretiminde üç veri sınıfını birleştirir:

### A. Lokasyon Değişkenleri
- İl
- İlçe
- Mahalle

### B. Canlı Veri
- Döviz
- Altın
- Hava durumu
- Diğer API değerleri

### C. Statik Trend Yazılar
- Genel bilgi
- Rehber içerik
- Sağlık / alışveriş / kurumsal konu içerikleri

### Render prensibi
- Başlık şablondan türetilir
- Body markup şablondan gelir
- Değişkenler render anında çözülür
- Reklam blokları render aşamasında yerleştirilir
- Yetişkin uyarısı varsa ilgili reklam politikası uygulanır

---

## 10. Bot / Ingest Sistemi

Python botun ana görevi içerik üretmek değil, **Laravel’e veri göndermek**tir.

### Görevleri
- JSON payload hazırlamak
- İçerik veya veri seti göndermek
- Toplu ingest yapmak
- Hata durumlarında retry denemek
- Token ile kimlik doğrulamak

### Endpoint Mantığı
- Güvenli giriş
- İmzalı veya token tabanlı doğrulama
- JSON schema validation
- Rate limiting
- Loglama

### Ingest edilen veri türleri
- Yeni content node
- Güncellenmiş live data
- Yeni taxonomy / location verisi
- Template varyasyonları

---

## 11. Admin Panel Gereksinimleri

Bu aşamada sadece ürün tanımı yapılıyor; kod değil.

### Yönetilecek alanlar
- Taxonomies
- Locations
- Live Data Vault
- Post Templates
- Content Nodes
- Global Ad Blocks

### Panel işlevleri
- CRUD
- Toplu düzenleme
- Aktif / pasif durum yönetimi
- Önizleme
- Slug / title helper
- İçerik güncelleme
- Reklam pozisyon yönetimi

---

## 12. SEO Mimarisi

Bu portal programatik SEO için tasarlanır.

### SEO ilkeleri
- Benzersiz slug
- Temiz heading yapısı
- Canonical yaklaşımı
- Internal linking
- Breadcrumb
- İlgili içerik blokları
- Hızlı yükleme
- Çok sayıda long-tail landing page

### Programatik SEO kullanım örnekleri
- “{şehir} {hizmet}”
- “{ilçe} {ürün}”
- “{konu} nedir”
- “{canlı veri} bugün ne kadar”
- “{lokasyon} rehberi”
- “{kategori} karşılaştırması”

### İçerik tipi çeşitleri
- Rehber
- Karşılaştırma
- Liste
- Soru / cevap
- Canlı veri sayfası
- Lokasyon sayfası
- Niche konu sayfası

---

## 13. Güvenlik İlkeleri

### API güvenliği
- Sanctum token
- Rate limit
- Payload validation
- IP / token loglama
- Yetkisiz erişim engeli

### Panel güvenliği
- Yetki bazlı erişim
- Admin rol ayrımı
- Silme işlemleri için doğrulama
- Log kayıtları

### İçerik güvenliği
- HTML injection kontrolü
- Script injection kontrolü
- Ad block script alanlarını kontrollü yönetim
- Render edilen değişkenlerin güvenli çözümü

---

## 14. Reklam Mantığı: Davranış Kuralları

Bu bölüm sistemin gelir motorunun kurallarını tanımlar.

### İçerik sınıfı
- `is_adult_warning = false` → standart reklam akışı
- `is_adult_warning = true` → alternatif reklam akışı veya reklam devre dışı

### Reklam pozisyonları
- Header
- Before content
- After H1
- In-article
- Sidebar
- Sticky footer
- Related content üstü / altı

### Yönetim
- Hangi pozisyonda hangi script kullanılacağı panelden değiştirilebilir.
- Kod içine sabit reklam scripti gömülmez.
- Reklam stratejisi sayfa türüne göre merkezi yönetilir.

---

## 15. Geliştirme Sırası

Claude Code’un aşağıdaki sırayla üretim yapması istenir:

### Aşama 1 — Veritabanı ve Model Katmanı
- Tablolar
- İlişkiler
- Indeksler
- Model alanları
- Cast’ler
- Boot logic

### Aşama 2 — Admin Panel
- Filament resource’lar
- CRUD ekranları
- Form ve table yapılandırmaları
- Reklam blok yönetimi

### Aşama 3 — Frontend Render ve Reklam Algoritması
- Blade sayfalar
- Content node render
- Reklam yerleşim mantığı
- Canlı veri çözümleme
- SEO component’leri

### Aşama 4 — API ve Bot Intake
- Sanctum korumalı endpoint
- Python ingest uyumu
- Validation
- Batch ingest
- Logging

### Aşama 5 — Ölçekleme ve Optimizasyon
- Cache stratejileri
- Queue job’lar
- Index tuning
- Search optimization
- Monitoring

---

## 16. Kabul Kriterleri

Sistem tamamlandı sayılabilmesi için:

- Taksonomi ve lokasyon ağaçları düzgün çalışmalı
- Content node’lar benzersiz ve hızlı erişilebilir olmalı
- Şablon sistemi değişkenleri doğru çözmeli
- Live data içerik içinde kullanılabilmeli
- Reklam blokları sayfa tipine göre seçilebilmeli
- Adult warning flag reklam kararını etkileyebilmeli
- Bot güvenli biçimde veri gönderebilmeli
- Admin panel ile her ana tablo yönetilebilmeli
- Sistem milyonlarca kayıt için tasarlanmış olmalı

---

## 17. Claude Code İçin Uygulama Talimatı

Bu projede üretim yaparken:

1. Önce veri modelini kur.
2. Sonra modelleri ve ilişkileri oluştur.
3. Ardından admin panel CRUD’larına geç.
4. Sonra frontend render ve reklam karar motorunu kur.
5. En son bot/API girişini ekle.
6. Kodda sade, sürdürülebilir ve yüksek performanslı yapı kullan.
7. Her modülü mümkün olduğunca küçük ve ayrık tut.
8. Programatik SEO mantığını merkezde tut.
9. Tüm render akışını cache-friendly tasarla.
10. Her içerik türünü tekrar üretilebilir bir şablona bağla.

---

## 18. Son Hedef

Bu sistemin nihai hali:

- içerik üretimini ölçekler,
- lokasyon ve taksonomi tabanlı arama trafiğini toplar,
- canlı verilerle güncel kalır,
- reklam gelirini merkezi olarak yönetir,
- çok büyük içerik hacmini sürdürebilir,
- ileride yeni veri kaynakları eklenerek büyüyebilir.

Bu proje bir blog değil, bir **programatik içerik işletim sistemi**dir.
