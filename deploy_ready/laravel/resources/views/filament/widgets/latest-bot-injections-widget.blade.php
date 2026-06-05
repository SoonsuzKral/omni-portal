@php
    $injections = $this->getRecentInjections();
@endphp

<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-sparkles class="w-5 h-5 text-indigo-400" />
            <span class="font-semibold text-white">Son Bot Enjeksiyonları</span>
        </div>
        <span class="text-xs text-gray-400">Real-time</span>
    </div>

    <div class="space-y-2">
        @forelse($injections as $injection)
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-800/50 border border-gray-700/50 hover:border-indigo-500/30 transition-all">
                <div class="flex items-center gap-3">
                    @if($injection['location_type'] === 'city')
                        <div class="p-1.5 rounded-full bg-emerald-500/20">
                            <x-heroicon-o-map-pin class="w-4 h-4 text-emerald-400" />
                        </div>
                    @else
                        <div class="p-1.5 rounded-full bg-amber-500/20">
                            <x-heroicon-o-building-office-2 class="w-4 h-4 text-amber-400" />
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate max-w-[200px]">{{ $injection['location'] }}</p>
                        <p class="text-xs text-gray-400 truncate max-w-[200px]">{{ $injection['taxonomy'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($injection['is_restricted'])
                        <span class="px-2 py-0.5 text-xs rounded-full bg-rose-500/20 text-rose-400">Restricted</span>
                    @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-500/20 text-emerald-400">Safe</span>
                    @endif
                    <span class="text-xs text-gray-500">{{ $injection['created_at']->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-2 opacity-50" />
                <p class="text-sm">Henüz bot enjeksiyonu yok</p>
            </div>
        @endforelse
    </div>
</div>