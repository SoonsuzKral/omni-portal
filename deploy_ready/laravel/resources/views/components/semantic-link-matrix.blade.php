@php
    use App\Services\SemanticLinkMatrix;
    $matrix = app(SemanticLinkMatrix::class);
    $links = $matrix->generateLinks($content, 8);
@endphp

@if(array_filter($links))
<div class="mt-12 bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 md:p-8 border border-gray-100 dark:border-slate-700">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 flex items-center">
        <svg class="w-7 h-7 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        <span class="font-[Montserrat]">{{ __('common.internal_link_matrix') }}</span>
    </h2>

    {{-- Same Category, Different Location - Stylish Grid --}}
    @if(!empty($links['same_category_different_location']))
    <div class="mb-10">
        <h3 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400 mb-4 flex items-center font-[Montserrat]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            {{ __('common.same_category_diff_location') }}
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($links['same_category_different_location'] as $link)
            <a href="{{ $link['url'] }}" class="group block p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-700 dark:to-slate-600 rounded-xl hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-900/30 dark:hover:to-purple-900/30 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <span class="text-sm font-medium text-gray-800 dark:text-gray-200 line-clamp-2 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 font-[Poppins]">{{ $link['title'] }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">{{ $link['location'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Same Location, Different Category --}}
        @if(!empty($links['same_location_different_category']))
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-5">
            <h3 class="text-lg font-semibold text-indigo-700 dark:text-indigo-300 mb-4 flex items-center font-[Montserrat]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ $content->location?->name ?? '' }} - {{ __('common.more_in_location') }}
            </h3>
            <ul class="space-y-3">
                @foreach($links['same_location_different_category'] as $link)
                <li>
                    <a href="{{ $link['url'] }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-[Poppins] flex items-center">
                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full mr-2"></span>
                        {{ $link['title'] }}
                        <span class="text-gray-400 dark:text-gray-500 ml-2 text-xs">{{ $link['category'] }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Popular in Niche --}}
        @if(!empty($links['popular_in_niche']))
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl p-5">
            <h3 class="text-lg font-semibold text-amber-700 dark:text-amber-300 mb-4 flex items-center font-[Montserrat]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                </svg>
                {{ $content->taxonomy?->name ?? '' }} - {{ __('common.popular_in') }}
            </h3>
            <ul class="space-y-3">
                @foreach($links['popular_in_niche'] as $link)
                <li>
                    <a href="{{ $link['url'] }}" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 text-sm font-[Poppins] flex items-center">
                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-2"></span>
                        {{ $link['title'] }}
                        <span class="text-gray-400 text-xs ml-2">({{ number_format($link['views']) }} {{ __('common.views_count') }})</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    {{-- Sibling Locations --}}
    @if(!empty($links['sibling_locations']))
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-600">
        <h3 class="text-lg font-semibold text-indigo-700 dark:text-indigo-300 mb-4 font-[Montserrat]">{{ __('common.nearby_locations') }}</h3>
        <div class="flex flex-wrap gap-3">
            @foreach($links['sibling_locations'] as $link)
            <a href="{{ $link['url'] }}" class="px-4 py-2 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/40 dark:to-purple-900/40 text-indigo-700 dark:text-indigo-300 rounded-full text-sm hover:from-indigo-200 hover:to-purple-200 dark:hover:from-indigo-800/50 dark:hover:to-purple-800/50 transition-all font-[Poppins]">
                📍 {{ $link['location'] }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Benzer Lokasyonlardaki Trend Haberler - Stylish Grid --}}
    @if(!empty($links['related_categories']))
    <div class="mt-10 pt-6 border-t border-gray-200 dark:border-slate-600">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center font-[Montserrat]">
            <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
            </svg>
            {{ __('common.trending_news') }}
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach(array_slice($links['related_categories'], 0, 6) as $link)
            <a href="{{ $link['url'] }}" class="group relative overflow-hidden rounded-xl shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-4">
                    <span class="inline-block px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-semibold rounded mb-2">🔥 Trend</span>
                    <h4 class="font-semibold text-gray-900 dark:text-white line-clamp-2 font-[Montserrat] group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $link['title'] }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-[Poppins]">{{ $link['category'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif