<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">💰 Ad Settings Dashboard</h2>
            <p class="text-gray-400 text-sm mt-1">Google AdSense yapılandırmasını yönetin ve tüm reklam pozisyonlarını görüntüleyin</p>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        use App\Models\GlobalAdBlock;
        $totalBlocks = GlobalAdBlock::count();
        $activeBlocks = GlobalAdBlock::where('active', 1)->count();
        $sidebarBlocks = GlobalAdBlock::whereIn('position', GlobalAdBlock::getSidebarPositions())->count();
        $sidebarActive = GlobalAdBlock::whereIn('position', GlobalAdBlock::getSidebarPositions())->where('active', 1)->count();
        $positionCount = GlobalAdBlock::distinct('position')->count('position');
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-3xl font-bold text-white">{{ $totalBlocks }}</p>
            <p class="text-xs text-gray-400 mt-1">Total Ad Blocks</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-3xl font-bold text-green-400">{{ $activeBlocks }}</p>
            <p class="text-xs text-gray-400 mt-1">Active</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-3xl font-bold text-indigo-400">{{ $sidebarActive }}/{{ $sidebarBlocks }}</p>
            <p class="text-xs text-gray-400 mt-1">Sidebar Ads Active</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-3xl font-bold text-yellow-400">{{ $positionCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Positions Used</p>
        </div>
    </div>

    <!-- Position Status Grid -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-white mb-4">📌 All Ad Positions</h3>
        @php
            $allPositions = GlobalAdBlock::POSITIONS;
            $positionBlocks = GlobalAdBlock::selectRaw('position, COUNT(*) as total, SUM(active) as active_count')
                ->groupBy('position')
                ->pluck('active_count', 'position');
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($allPositions as $key => $label)
                @php
                    if (in_array($key, ['ga4_tracking', 'clarity_tracking', 'head_script'])) continue;
                    $count = (int) ($positionBlocks[$key] ?? 0);
                    $active = (int) ($positionBlocks[$key] ?? 0);
                    $status = $active > 0 ? '🟢' : ($count > 0 ? '🟡' : '⚪');
                @endphp
                <div class="bg-gray-900/50 rounded-lg p-3 border {{ $active > 0 ? 'border-green-700/50' : ($count > 0 ? 'border-yellow-700/50' : 'border-gray-700/30') }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-300 truncate" title="{{ $label }}">{{ $label }}</span>
                        <span class="text-xs">{{ $status }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $active }} active / {{ $count }} total</p>
                </div>
            @endforeach
        </div>
        <div class="mt-4 text-xs text-gray-500">
            <span class="mr-4">🟢 Active</span>
            <span class="mr-4">🟡 Inactive</span>
            <span>⚪ Not Configured</span>
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
        <div class="bg-gray-900 rounded-lg p-4 max-h-64 overflow-y-auto">
            <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ $this->data['ads_txt'] ?? '' }}</pre>
        </div>
        <p class="text-gray-400 text-xs mt-2">
            Bu içerik <code class="text-indigo-400">/ads.txt</code> adresinde yayınlanır.
            Google AdSense bu dosyayı otomatik olarak kontrol eder.
        </p>
    </div>

    <div class="bg-gray-800/50 border border-gray-700/50 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-gray-300 mb-2">🔗 Quick Links</h4>
        <div class="flex flex-wrap gap-3 text-sm">
            <a href="{{ url('/admin/global-ad-blocks') }}" class="text-indigo-400 hover:text-indigo-300 hover:underline">→ Manage Ad Blocks</a>
            <a href="{{ url('/ads.txt') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 hover:underline">→ View ads.txt</a>
            <a href="{{ url('/admin/env-variables') }}" class="text-indigo-400 hover:text-indigo-300 hover:underline">→ Env Variables</a>
        </div>
    </div>
</div>
