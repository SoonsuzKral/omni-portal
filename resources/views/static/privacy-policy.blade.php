@extends('layouts.app')

@section('title', __('common.privacy') . ' - ' . config('app.name'))
@section('meta_description', __('common.privacy') . ' - ' . config('app.name'))

@section('content')
<article class="max-w-4xl mx-auto px-4 py-8">
    <nav class="flex mb-6 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="/" class="hover:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span>/</span></li>
            <li class="text-gray-400">{{ __('common.privacy') }}</li>
        </ol>
    </nav>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <header class="mb-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('common.privacy') }}</h1>
        <p class="text-gray-400">Son güncellenme: 21 Mayıs 2026</p>
    </header>

    <div class="prose prose-invert prose-indigo max-w-none text-gray-300 space-y-6">
        <h2 class="text-2xl font-semibold text-white mt-8">1. Giriş</h2>
        <p>OmviPortal ("biz", "bizim" veya "platform") olarak, kişisel verilerinizin gizliliğine büyük önem vermekteyiz. Bu Gizlilik Politikası, platformumuzu ziyaret ettiğinizde veya hizmetlerimizi kullandığınızda kişisel verilerinizin nasıl toplandığını, kullanıldığını, işlendiğini ve korunduğunu açıklamaktadır.</p>
        <p>6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) ve Avrupa Birliği Genel Veri Koruma Tüzüğü (GDPR) kapsamında, veri sorumlusu sıfatıyla hareket etmekteyiz.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">2. Toplanan Veriler</h2>
        <p>Platformumuzu kullanımınız sırasında aşağıdaki kategorilerde veriler toplanabilir:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li><strong>Kimlik Bilgileri:</strong> Ad, soyadı, e-posta adresi (iletişim formu doldurulması durumunda)</li>
            <li><strong>İletişim Bilgileri:</strong> E-posta adresi, telefon numarası (yalnızca gönüllü olarak sağlandığında)</li>
            <li><strong>Kullanım Verileri:</strong> IP adresi, tarayıcı türü, işletim sistemi, sayfa görüntüleme süreleri, tıklama istatistikleri</li>
            <li><strong>Çerez Verileri:</strong> Site işlevselliği ve analitik amaçlı çerezler</li>
            <li><strong>Konum Verileri:</strong> Yaklaşık coğrafi konum (IP adresi üzerinden)</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">3. Verilerin Toplanma Amaçları</h2>
        <p>Kişisel verileriniz aşağıdaki amaçlarla işlenmektedir:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Platform hizmetlerinin sağlanması ve kişiselleştirilmesi</li>
            <li>Kullanıcı deneyiminin iyileştirilmesi</li>
            <li>Reklam ve içerik önerilerinin kişiselleştirilmesi</li>
            <li>Yasal yükümlülüklerin yerine getirilmesi</li>
            <li>Kötüye kullanımın ve dolandırıcılığın önlenmesi</li>
            <li>İletişim taleplerine yanıt verilmesi</li>
            <li>Analitik ve istatistiksel değerlendirmeler</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">4. Çerez Politikası</h2>
        <p>Platformumuz, kullanıcı deneyimini iyileştirmek ve hizmet kalitemizi artırmak için çerezler (cookies) kullanmaktadır. Kullanılan çerez türleri:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li><strong>Zorunlu Çerezler:</strong> Site temel işlevlerinin çalışması için gereklidir</li>
            <li><strong>Analitik Çerezler:</strong> Kullanım istatistiklerini anonim olarak toplar</li>
            <li><strong>Reklam Çerezleri:</strong> Google AdSense tarafından, ilgi alanına dayalı reklamlar göstermek için kullanılır</li>
            <li><strong>İşlevsel Çerezler:</strong> Tercihlerinizi hatırlayarak kişiselleştirilmiş deneyim sunar</li>
        </ul>
        <p>Tarayıcı ayarlarınızdan çerezleri yönetebilir veya tamamen devre dışı bırakabilirsiniz. Ancak, bazı çerezlerin devre dışı bırakılması site işlevselliğini etkileyebilir.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">5. Verilerin Paylaşımı ve Aktarımı</h2>
        <p>Kişisel verileriniz, aşağıdaki durumlar haricinde üçüncü taraflarla paylaşılmaz:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Açık rızanızın bulunması halinde</li>
            <li>Yasal bir zorunluluk olması durumunda</li>
            <li>Hizmet sağlayıcılarımız (hosting, analitik, reklam hizmetleri) ile sınırlı kapsamda</li>
            <li>Bir hakkın tesisi, kullanılması veya korunması için gerekli olması halinde</li>
        </ul>
        <p>Google AdSense ve Google Analytics dahil olmak üzere üçüncü taraf hizmet sağlayıcıları, kendi gizlilik politikalarına tabidir. Veriler, ABD ve AB merkezli sunuculara aktarılabilir.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">6. KVKK Kapsamında Haklarınız</h2>
        <p>KVKK'nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
            <li>İşlenmişse buna ilişkin bilgi talep etme</li>
            <li>İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme</li>
            <li>Yurt içinde veya yurt dışında aktarıldığı üçüncü kişileri bilme</li>
            <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme</li>
            <li>Silinmesini veya yok edilmesini talep etme</li>
            <li>İtiraz etme ve zararın giderilmesini talep etme</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">7. Veri Güvenliği</h2>
        <p>Kişisel verilerinizin güvenliğini sağlamak için uygun teknik ve idari tedbirleri almaktayız. SSL/TLS şifreleme, güvenlik duvarları ve düzenli güvenlik denetimleri uygulanmaktadır. Ancak, internet üzerinden veri iletiminin tam güvenliğini garanti edemeyiz.</p>

        <h2 class="text-2xl font-semibold text-white mt-8">8. Google AdSense ve Reklam</h2>
        <p>Platformumuz, Google AdSense hizmetini kullanmaktadır. Google:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>İlgi alanına dayalı reklamlar sunmak için çerezler kullanır</li>
            <li>Kullanıcı verilerini Google'ın <a href="https://policies.google.com/privacy" class="text-indigo-400 hover:underline" target="_blank" rel="noopener">Gizlilik Politikası</a> kapsamında işler</li>
            <li>Kullanıcılar, <a href="https://adssettings.google.com" class="text-indigo-400 hover:underline" target="_blank" rel="noopener">Reklam Ayarları</a> sayfasından kişiselleştirilmiş reklamları devre dışı bırakabilir</li>
        </ul>

        <h2 class="text-2xl font-semibold text-white mt-8">9. İletişim</h2>
        <p>Gizlilik politikamız hakkında sorularınız veya KVKK kapsamındaki başvurularınız için bizimle iletişime geçebilirsiniz:</p>
        <p class="bg-gray-800 p-4 rounded-lg mt-4">
            <strong>E-posta:</strong> privacy@omviportal.com<br>
            <strong>Adres:</strong> OmviPortal Hizmetleri, İstanbul, Türkiye
        </p>

        <h2 class="text-2xl font-semibold text-white mt-8">10. Politika Güncellemeleri</h2>
        <p>Bu gizlilik politikası zaman zaman güncellenebilir. Değişiklikler bu sayfada yayınlandığı anda yürürlüğe girer. Önemli değişiklikler durumunda kullanıcılarımıza bildirim yapılacaktır.</p>
    </div>
</article>
@endsection
