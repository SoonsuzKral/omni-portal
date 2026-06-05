@php
    $revenueData = $this->getRevenueData();
    $topLocations = $this->getTopLocations();
@endphp

<div class="fi-wi-stats-overview revenue-status-widget">
    <div class="grid grid-cols-2 gap-4 p-4">
        <div class="p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 rounded-full bg-emerald-500/20">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-emerald-400" />
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Güvenli Reklam</p>
                    <p class="text-lg font-bold text-emerald-400">Safe Ads (Adsense)</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Görüntülenme</span>
                    <span class="text-sm font-semibold text-white">{{ number_format($revenueData['safe']['views']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Tahmini Gelir</span>
                    <span class="text-sm font-bold text-emerald-400">${{ number_format($revenueData['safe']['revenue'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Est. CPC</span>
                    <span class="text-sm text-gray-300">${{ $revenueData['safe']['cpc'] }}</span>
                </div>
            </div>
        </div>

        <div class="p-4 rounded-lg bg-rose-500/10 border border-rose-500/20">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 rounded-full bg-rose-500/20">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-400" />
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Kısıtlı Reklam</p>
                    <p class="text-lg font-bold text-rose-400">Tier-2 Ads</p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Görüntülenme</span>
                    <span class="text-sm font-semibold text-white">{{ number_format($revenueData['restricted']['views']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Tahmini Gelir</span>
                    <span class="text-sm font-bold text-rose-400">${{ number_format($revenueData['restricted']['revenue'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Est. CPC</span>
                    <span class="text-sm text-gray-300">${{ $revenueData['restricted']['cpc'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-span-2 p-4 rounded-lg bg-indigo-500/10 border border-indigo-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full bg-indigo-500/20">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-indigo-400" />
                    </div>
                    <span class="text-sm font-semibold text-white">Toplam Gelir Potansiyeli</span>
                </div>
                <span class="text-2xl font-bold text-indigo-400">${{ number_format($revenueData['total_revenue'], 2) }}</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2">
                <div class="flex h-2 rounded-full overflow-hidden">
                    <div class="bg-emerald-500" style="width: {{ $revenueData['total_views'] > 0 ? ($revenueData['safe']['views'] / $revenueData['total_views'] * 100) : 0 }}%"></div>
                    <div class="bg-rose-500" style="width: {{ $revenueData['total_views'] > 0 ? ($revenueData['restricted']['views'] / $revenueData['total_views'] * 100) : 0 }}%"></div>
                </div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-400">
                <span>{{ number_format($revenueData['safe']['views']) }} Safe</span>
                <span>{{ number_format($revenueData['restricted']['views']) }} Restricted</span>
            </div>
        </div>
    </div>
</div>