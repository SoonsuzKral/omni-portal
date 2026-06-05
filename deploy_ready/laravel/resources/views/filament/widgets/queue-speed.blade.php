<div class="fi-wi-stats-overview queue-speed-widget">
    <div class="p-4 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($stats['health_status'] === 'healthy')
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-sm font-semibold text-emerald-400">System Healthy</span>
                @elseif($stats['health_status'] === 'warning')
                    <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                    <span class="text-sm font-semibold text-yellow-400">Warning</span>
                @else
                    <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                    <span class="text-sm font-semibold text-rose-400">Critical</span>
                @endif
            </div>
            <span class="text-xs text-gray-400">{{ $stats['pending_jobs'] }} pending</span>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="p-3 rounded-lg bg-gray-800/50">
                <p class="text-xs text-gray-400 mb-1">Per Second</p>
                <p class="text-lg font-bold text-white">{{ $stats['processing_rate_per_second'] }}</p>
            </div>
            <div class="p-3 rounded-lg bg-gray-800/50">
                <p class="text-xs text-gray-400 mb-1">Per Minute</p>
                <p class="text-lg font-bold text-white">{{ $stats['processing_rate_per_minute'] }}</p>
            </div>
            <div class="p-3 rounded-lg bg-gray-800/50">
                <p class="text-xs text-gray-400 mb-1">Per Hour</p>
                <p class="text-lg font-bold text-white">{{ $stats['processing_rate_per_hour'] }}</p>
            </div>
        </div>

        <div class="space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-400">Last Hour Content</span>
                <span class="text-white font-medium">{{ number_format($stats['last_hour_content']) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Last 24h Content</span>
                <span class="text-white font-medium">{{ number_format($stats['last_24h_content']) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Failed Jobs (24h)</span>
                <span class="{{ $stats['failed_last_24h'] > 0 ? 'text-rose-400' : 'text-emerald-400' }} font-medium">{{ $stats['failed_last_24h'] }}</span>
            </div>
        </div>

        <div class="pt-2 border-t border-gray-700">
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Max Throughput</span>
                <span class="text-indigo-400 font-medium">{{ $stats['estimated_throughput'] }} URLs/sec</span>
            </div>
        </div>
    </div>
</div>