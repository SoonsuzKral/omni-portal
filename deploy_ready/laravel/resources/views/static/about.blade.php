@extends('layouts.app')

@section('title', __('common.about') . ' - ' . config('app.name'))
@section('meta_description', __('common.about') . ' - ' . config('app.name'))

@section('content')
<article class="max-w-4xl mx-auto px-4 py-8">
    <x-ad-renderer position="above_breadcrumb" />

    <nav class="flex mb-6 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="/" class="hover:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span>/</span></li>
            <li class="text-gray-400">{{ __('common.about') }}</li>
        </ol>
    </nav>

    <x-ad-renderer position="after_breadcrumb" />

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <header class="mb-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('common.about') }}</h1>
        <p class="text-xl text-gray-400">Türkiye'nin lokasyon bazlı akıllı hizmet rehberi</p>
    </header>

    <x-ad-renderer position="under_h1" />

    <div class="prose prose-invert prose-indigo max-w-none text-gray-300 space-y-6">
        <div class="bg-indigo-900/20 border border-indigo-500/30 rounded-xl p-8 mb-8 text-center">
            <p class="text-lg font-semibold text-indigo-300">81 şehir, binlerce ilçe, milyonlarca içerik — tek bir platformda.</p>
        </div>

        <h2 class="text-2xl font-semibold text-white mt-8">Misyonumuz</h2>
        <p>OmviPortal olarak misyonumuz, Türkiye genelinde her şehir ve ilçede yaşayan insanların ihtiyaç duydukları hizmet, ürün ve işletme bilgilerine en hızlı ve doğru şekilde ulaşmalarını sağlamaktır. Kapsamlı ve güncel veritabanımız ile kullanıcılarımıza lokasyon bazlı, organize ve anlaşılır bir bilgi deneyimi sunuyoruz.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">Vizyonumuz</h2>
        <p>Türkiye'nin en kapsamlı lokasyon bazlı bilgi platformu olmak ve her kullanıcının bulunduğu şehir veya ilçede ihtiyaç duyduğu her hizmete tek tıkla ulaşabildiği bir ekosistem oluşturmak. Yapay zeka destekli içerik yönetimimiz ile sürekli büyüyen ve güncellenen bir bilgi kaynağı olmayı hedefliyoruz.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">Nasıl Çalışır?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
            <div class="bg-gray-800/50 p-6 rounded-xl text-center">
                <div class="text-4xl mb-3">🔍</div>
                <h3 class="text-lg font-semibold text-white mb-2">Keşfedin</h3>
                <p class="text-sm text-gray-400">Kategori ve lokasyon bazlı içeriklerimizi keşfedin</p>
            </div>
            <div class="bg-gray-800/50 p-6 rounded-xl text-center">
                <div class="text-4xl mb-3">🎯</div>
                <h3 class="text-lg font-semibold text-white mb-2">Bulun</h3>
                <p class="text-sm text-gray-400">Aradığınız hizmeti şehrinizde ve ilçenizde bulun</p>
            </div>
            <div class="bg-gray-800/50 p-6 rounded-xl text-center">
                <div class="text-4xl mb-3">📞</div>
                <h3 class="text-lg font-semibold text-white mb-2">Bağlanın</h3>
                <p class="text-sm text-gray-400">Hizmet sağlayıcılarla hızlıca iletişime geçin</p>
            </div>
        </div>

        <h2 class="text-2xl font-semibold text-white mt-8">Neden OmviPortal?</h2>
        <ul class="list-disc pl-6 space-y-3">
            <li><strong>Kapsamlı Veritabanı:</strong> 81 şehir ve binlerce ilçede, yüzlerce kategori altında milyonlarca içerik</li>
            <li><strong>Lokasyon Bazlı:</strong> Tüm içerikler şehir ve ilçe bazında organize edilmiştir</li>
            <li><strong>Güncel Bilgi:</strong> Otomatize sistemler ile sürekli güncellenen veri tabanı</li>
            <li><strong>Hızlı Erişim:</strong> Optimize edilmiş arama ve gezinme deneyimi</li>
            <li><strong>Kullanıcı Dostu:</strong> Modern, hızlı ve mobil uyumlu arayüz</li>
            <li><strong>Tamamen Ücretsiz:</strong> Platformdaki tüm içeriklere ücretsiz erişim</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">Teknolojimiz</h2>
        <p>OmviPortal, modern web teknolojileri kullanılarak inşa edilmiştir:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Laravel 12 (PHP framework)</li>
            <li>Tailwind CSS (modern, responsive tasarım)</li>
            <li>Filament Admin (gelişmiş yönetim paneli)</li>
            <li>MySQL veritabanı (optimize edilmiş sorgular)</li>
            <li>Google AdSense (reklam entegrasyonu)</li>
            <li>XML Sitemap (SEO optimizasyonu)</li>
            <li>JSON-LD yapılandırılmış veri</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">İletişim</h2>
        <p>Soru, öneri ve iş birliği talepleriniz için bizimle iletişime geçebilirsiniz:</p>
        <p class="bg-gray-800 p-4 rounded-lg mt-4">
            <strong>E-posta:</strong> info@omviportal.com<br>
            <strong>Adres:</strong> OmviPortal Hizmetleri, İstanbul, Türkiye
        </p>
    </div>

    <x-ad-renderer position="bottom" />
</article>
@endsection
