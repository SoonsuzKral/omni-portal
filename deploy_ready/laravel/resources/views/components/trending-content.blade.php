@if($trendingItems->isNotEmpty())
<section class="mt-12 border-t border-gray-800 pt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
            </svg>
            {{ __('home.trending_now') }}
        </h2>
        <a href="{{ route('keywords.trending') }}" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">{{ __('common.view_all') }} &rarr;</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach($trendingItems as $item)
        <a href="{{ url('/' . ($item->taxonomy?->slug ?? 'x') . '/' . ($item->location?->slug ?? 'x') . '/' . $item->slug) }}"
           class="group bg-slate-800/50 hover:bg-slate-700/50 rounded-xl p-4 border border-slate-700/50 hover:border-orange-500/30 transition-all">
            <div class="flex items-center gap-2 mb-2">
                @if($item->taxonomy)
                <span class="text-xs px-2 py-0.5 bg-indigo-600/30 text-indigo-300 rounded">{{ $item->taxonomy->name }}</span>
                @endif
                <span class="text-xs text-orange-400">&#128293;</span>
            </div>
            <h3 class="text-sm font-medium text-gray-200 group-hover:text-white line-clamp-2">{{ $item->title }}</h3>
            <div class="flex items-center justify-between mt-3 text-xs text-gray-500">
                @if($item->location)
                <span>{{ $item->location->name }}</span>
                @endif
                <span>{{ number_format($item->page_views) }} {{ __('common.views') }}</span>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
