@extends('layouts.app')

@section('title', __('common.contact') . ' - ' . config('app.name'))
@section('meta_description', __('common.contact') . ' - ' . config('app.name'))

@section('content')
<article class="max-w-4xl mx-auto px-4 py-8">
    <x-ad-renderer position="above_breadcrumb" />

    <nav class="flex mb-6 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="/" class="hover:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span>/</span></li>
            <li class="text-gray-400">{{ __('common.contact') }}</li>
        </ol>
    </nav>

    <x-ad-renderer position="after_breadcrumb" />

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <header class="mb-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('common.contact') }}</h1>
        <p class="text-xl text-gray-400">Sorularınız mı var? Size yardımcı olmaktan mutluluk duyarız.</p>
    </header>

    <x-ad-renderer position="under_h1" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div>
            <div class="prose prose-invert prose-indigo max-w-none text-gray-300">
                <h2 class="text-2xl font-semibold text-white">Bize Ulaşın</h2>
                <p>Aşağıdaki kanallar aracılığıyla bizimle iletişime geçebilirsiniz. En kısa sürede size dönüş yapacağız.</p>

                <div class="space-y-6 mt-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-indigo-600/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">E-posta</h3>
                            <p class="text-gray-400">info@omviportal.com</p>
                            <p class="text-gray-500 text-sm">24 saat içinde yanıt alırsınız</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-indigo-600/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Adres</h3>
                            <p class="text-gray-400">OmviPortal Hizmetleri<br>İstanbul, Türkiye</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-indigo-600/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Gizlilik</h3>
                            <p class="text-gray-400">Kişisel verileriniz KVKK kapsamında korunmaktadır.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-gray-800 rounded-lg">
                    <p class="text-sm text-gray-400">
                        <strong>İş birliği talepleri:</strong> partnership@omviportal.com<br>
                        <strong>Yasal bildirimler:</strong> legal@omviportal.com<br>
                        <strong>Reklam ve sponsorluk:</strong> ads@omviportal.com
                    </p>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-2xl font-semibold text-white mb-6">Mesaj Gönderin</h2>
                <form action="mailto:info@omviportal.com" method="GET" class="space-y-4">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-300 mb-1">Konu</label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="Mesajınızın konusu">
                    </div>
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-300 mb-1">Mesajınız</label>
                        <textarea id="body" name="body" rows="6" required
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="Mesajınızı buraya yazın..."></textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition duration-200">
                        Mesajı Gönder
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-ad-renderer position="bottom" />
</article>
@endsection
