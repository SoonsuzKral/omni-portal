<div class="fi-wi-stats-overview most-profitable-widget">
    <div class="p-4 space-y-4">
        @forelse($locations as $index => $loc)
            @php
                $maxScore = $locations->max('revenue_score');
                $percentage = $maxScore > 0 ? ($loc->revenue_score / $maxScore) * 100 : 0;
            @endphp
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 w-5">#{{ $index + 1 }}</span>
                        <span class="text-sm font-medium text-gray-200 truncate max-w-[140px]">{{ $loc->name }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-gray-400">{{ number_format($loc->total_views) }} views</span>
                        <span class="font-bold text-emerald-400">${{ number_format($loc->revenue_score / 1000, 1) }}K</span>
                    </div>
                </div>
                <div class="w-full bg-gray-700/50 rounded-full h-1.5">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-1.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-4">
                <p class="text-sm">No location data available</p>
            </div>
        @endforelse
    </div>
</div>