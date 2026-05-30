@extends('layouts.app')

@section('title', __('common.terms') . ' - ' . config('app.name'))
@section('meta_description', __('common.terms') . ' - ' . config('app.name'))

@section('content')
<article class="max-w-4xl mx-auto px-4 py-8">
    <nav class="flex mb-6 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="/" class="hover:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span>/</span></li>
            <li class="text-gray-400">{{ __('common.terms') }}</li>
        </ol>
    </nav>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <header class="mb-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('common.terms') }}</h1>
        <p class="text-gray-400">Son güncellenme: 21 Mayıs 2026</p>
    </header>

    <div class="prose prose-invert prose-indigo max-w-none text-gray-300 space-y-6">
        <h2 class="text-2xl font-semibold text-white mt-8">1. Kabul</h2>
        <p>OmviPortal web sitesini ("Platform") kullanarak, bu Kullanım Koşullarını ("Koşullar") tamamen kabul etmiş olursunuz. Bu koşulları kabul etmiyorsanız, platformu kullanmayınız. Platforma erişerek, yürürlükteki tüm yerel, ulusal ve uluslararası yasa ve düzenlemelere uymayı kabul edersiniz.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">2. Hizmet Tanımı</h2>
        <p>OmviPortal, Türkiye genelinde şehir ve ilçe bazlı hizmet, ürün ve işletme bilgilerini kullanıcılara sunan bir bilgi platformudur. Platformda yer alan içerikler, kullanıcıların bilgi edinmesi amacıyla otomatize sistemler ve güncel veri kaynakları kullanılarak oluşturulmaktadır.</p>
        <p>Platform, aşağıdaki özellikleri sağlar:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Kategori bazlı hizmet rehberi</li>
            <li>Şehir ve ilçe bazlı lokasyon sayfaları</li>
            <li>Detaylı içerik ve bilgi sayfaları</li>
            <li>Gelişmiş arama fonksiyonları</li>
            <li>XML site haritaları ile indekslenebilir içerik</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">3. Fikri Mülkiyet</h2>
        <p>Platformda yer alan tüm içerik, tasarım, logo, grafik, yazılım ve kodlar, aksi belirtilmedikçe OmviPortal'a aittir ve fikri mülkiyet yasaları kapsamında korunmaktadır. İçeriklerin izinsiz kopyalanması, dağıtılması, değiştirilmesi veya ticari amaçla kullanılması yasaktır.</p>
        <p>Platformda yer alan üçüncü taraf marka isimleri, logoları ve ticari markalar ilgili sahiplerinin mülkiyetindedir.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">4. Kullanıcı Sorumlulukları</h2>
        <p>Platformu kullanırken aşağıdaki kurallara uymayı kabul edersiniz:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Platformu yalnızca yasal amaçlarla kullanmak</li>
            <li>Platformun işleyişini bozacak teknik müdahalelerden kaçınmak</li>
            <li>Otomatik botlar, scriptler veya veri kazıma araçları kullanmamak</li>
            <li>Platform üzerinden yasa dışı, tehditkar, taciz edici veya iftira içerikli materyaller paylaşmamak</li>
            <li>Başka kullanıcıların gizliliğini ihlal etmemek</li>
            <li>Platform güvenlik önlemlerini atlatmaya çalışmamak</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">5. Sorumluluk Reddi</h2>
        <p>Platformdaki içerikler "olduğu gibi" ve "mevcut olduğu şekilde" sunulmaktadır. OmviPortal:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>İçeriklerin doğruluğu, eksiksizliği veya güncelliği konusunda garanti vermez</li>
            <li>Kesintisiz veya hatasız hizmet taahhüdünde bulunmaz</li>
            <li>Üçüncü taraf bağlantılarının içeriğinden sorumlu değildir</li>
            <li>Platform kullanımından kaynaklanan dolaylı veya doğrudan zararlardan sorumlu tutulamaz</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">6. Üçüncü Taraf Hizmetler</h2>
        <p>Platform, aşağıdaki üçüncü taraf hizmetlerini kullanmaktadır:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li><strong>Google AdSense:</strong> Reklam yayıncılığı hizmeti</li>
            <li><strong>Google Analytics:</strong> Kullanıcı istatistiği ve analiz hizmeti</li>
            <li><strong>Google Fonts:</strong> Tipografi hizmeti</li>
            <li><strong>Font Awesome:</strong> İkon kütüphanesi</li>
        </ul>
        <p>Bu hizmetlerin kullanımı, ilgili üçüncü tarafın hizmet koşullarına ve gizlilik politikalarına tabidir.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">7. Hizmet Değişiklikleri</h2>
        <p>OmviPortal, önceden bildirimde bulunmaksızın hizmetlerini değiştirme, askıya alma veya sonlandırma hakkını saklı tutar. Platformda yapılan değişiklikler, bu sayfada güncellenerek yürürlüğe girer.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">8. Uygulanacak Hukuk</h2>
        <p>Bu kullanım koşulları, Türkiye Cumhuriyeti yasalarına tabidir. Koşullardan kaynaklanan uyuşmazlıklarda İstanbul merkezli mahkemeler ve icra daireleri yetkilidir.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">9. İletişim</h2>
        <p>Kullanım koşulları hakkında sorularınız için bizimle iletişime geçebilirsiniz:</p>
        <p class="bg-gray-800 p-4 rounded-lg mt-4">
            <strong>E-posta:</strong> legal@omviportal.com<br>
            <strong>Adres:</strong> OmviPortal Hizmetleri, İstanbul, Türkiye
        </p>
    </div>
</article>
@endsection
