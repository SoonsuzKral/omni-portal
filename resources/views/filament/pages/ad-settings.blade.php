<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">💰 Ad Settings</h2>
            <p class="text-gray-400 text-sm mt-1">Google AdSense yapılandırmasını yönetin</p>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button wire:click="save" color="primary">
                💾 Değişiklikleri Kaydet
            </x-filament::button>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-white mb-3">📄 ads.txt Önizleme</h3>
        <div class="bg-gray-900 rounded-lg p-4">
                            <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap overflow-x-auto">{{ $this->data['ads_txt'] ?? '' }}</pre>
        </div>
        <p class="text-gray-400 text-xs mt-2">
            Bu içerik <code class="text-indigo-400">/ads.txt</code> adresinde yayınlanır.
            Google AdSense bu dosyayı otomatik olarak kontrol eder.
        </p>
    </div>
</div>
