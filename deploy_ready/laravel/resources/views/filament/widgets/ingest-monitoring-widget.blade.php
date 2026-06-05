@php
    $queueStats = $this->getQueueStats();
    $recentStats = $this->getRecentIngestStats();
@endphp

<div class="fi-wi-stats-overview stats-overview">
    <div class="grid grid-cols-2 gap-4 p-4">
        <div class="p-4 rounded-lg bg-indigo-500/10 border border-indigo-500/20">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-full bg-indigo-500/20">
                    <x-heroicon-o-queue-list class="w-5 h-5 text-indigo-400" />
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Bekleyen İşler</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($queueStats['pending']) }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-full bg-amber-500/20">
                    <x-heroicon-o-arrow-path class="w-5 h-5 text-amber-400 animate-spin" />
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">İşleniyor</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($queueStats['processing']) }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 col-span-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full bg-emerald-500/20">
                        <x-heroicon-o-clock class="w-5 h-5 text-emerald-400" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Son 24 Saat</p>
                        <p class="text-xl font-bold text-white">{{ number_format($recentStats['last_24h']) }} işlem</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Son 1 Saat</p>
                    <p class="text-lg font-semibold text-indigo-400">{{ number_format($recentStats['last_hour']) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>