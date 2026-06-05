@extends('layouts.app')

@section('title', config('app.name') . ' - ' . __('common.discover_your_city'))

@section('meta_description', __('common.hero_subtitle'))

@push('head')
<style>
    .mega-grid-item { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .mega-grid-item:hover { transform: translateY(-4px) scale(1.02); }
    .gradient-mesh { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%); }
    .glass-effect { background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.15); }
    .search-glow:focus-within { box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.4); }
    .trending-pill { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
    .city-card { background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%); border: 1px solid #eef2ff; }
    .dark .city-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; }
    .dark .gradient-mesh { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); }
    .dark .bg-base-100 { background-color: #0f172a; }
    .dark .text-base-content { color: #e2e8f0; }
    @media (max-width: 768px) {
        .mega-grid { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen">
    <x-ad-renderer position="above_breadcrumb" />

    <!-- HERO SECTION -->
    <section class="gradient-mesh relative">
        <div class="absolute inset-0 opacity-30 overflow-hidden">
            <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
            <div class="absolute top-40 right-20 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-20 left-1/3 w-64 h-64 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">
            <div class="text-center mb-10">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 tracking-tight">
                    <span style="color: #e879f9; background: linear-gradient(135deg, #f472b6, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ __('common.discover_your_city') }}</span>
                </h1>
                <p class="text-lg text-indigo-200 max-w-2xl mx-auto">
                    {{ __('common.hero_subtitle') }}
                </p>
            </div>

            <!-- LOCAL SEARCH BAR (Google-like with autocomplete) -->
            <div class="max-w-3xl mx-auto">
                <div class="search-glow relative">
                    <form action="{{ route('search') }}" method="GET" id="home-search-form" autocomplete="off">
                        <div class="glass-effect rounded-2xl p-2 flex gap-2 relative">
                            <div class="flex-1 relative">
                                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="q" id="home-search-input" placeholder="{{ __('common.search_placeholder') }}" 
                                    class="search-input-field w-full bg-transparent text-white placeholder-indigo-300 pl-12 pr-4 py-4 rounded-xl focus:outline-none"
                                    autocomplete="off">
                            </div>
                            <button type="submit" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold px-8 py-4 rounded-xl hover:from-pink-600 hover:to-purple-700 transition-all shadow-lg whitespace-nowrap">
                                {{ __('common.search') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick Stats -->
                <div class="flex justify-center gap-8 mt-6 text-indigo-200 text-sm">
                    <span><strong class="text-white">{{ number_format($stats['total_content'] ?? 0) }}</strong> {{ __('common.pages') }}</span>
                    <span><strong class="text-white">{{ number_format($stats['total_locations'] ?? 0) }}</strong> {{ __('common.locations') }}</span>
                    <span><strong class="text-white">{{ number_format($stats['total_taxonomies'] ?? 0) }}</strong> {{ __('common.categories') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- MEGA GRID CATEGORIES -->
    <section class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ __('home.browse_categories') }}</h2>
            <a href="{{ url('/categories') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ __('common.view_all') }} →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @php
            use App\Helpers\TaxonomyIconHelper;
            @endphp

            @forelse($taxonomies ?? collect() as $taxonomy)
                @php
                    $iconData = TaxonomyIconHelper::getIcon($taxonomy->name);
                    $bgColors = ['bg-red-100', 'bg-blue-100', 'bg-green-100', 'bg-yellow-100', 'bg-purple-100', 'bg-pink-100', 'bg-indigo-100', 'bg-orange-100'];
                    $iconBgSolid = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-orange-500'];
                    $colorIndex = $loop->index % count($bgColors);
                @endphp
                <a href="{{ url('/' . $taxonomy->slug) }}" 
                   class="mega-grid-item group bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-xl">
                    <div class="{{ $bgColors[$colorIndex] }} rounded-xl p-3 w-14 h-14 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm" style="box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                        <svg class="w-6 h-6" fill="none" stroke="{{ $iconData['color'] }}" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconData['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $taxonomy->name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($taxonomy->content_nodes_count ?? 0) }} {{ __('common.listings') }}</p>
                </a>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p>{{ __('common.no_categories_yet') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- FEATURED CITIES -->
    <section class="bg-gray-50 dark:bg-slate-900 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ __('home.featured_cities') }}</h2>
                <a href="{{ url('/locations') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ __('common.view_all') }} →</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                @forelse($locations ?? collect() as $location)
                    <a href="{{ url('/location/' . $location->slug) }}" 
                       class="city-card group relative overflow-hidden rounded-xl p-4 hover:shadow-xl transition-all border border-gray-100 dark:border-slate-700">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="font-semibold text-indigo-700 dark:text-indigo-300 text-sm mb-1 relative z-10">{{ $location->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 relative z-10">{{ number_format($location->content_nodes_count ?? 0) }} {{ __('common.articles') }}</p>
                    </a>
                @empty
                    <div class="col-span-full text-center py-8 text-gray-500">{{ __('common.no_locations_yet') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <x-ad-renderer position="under_h1" />

    <!-- TRENDING TOPICS (dynamic from DB) -->
    <section class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-slate-900 dark:to-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ __('home.trending_topics') }}</h2>
                <a href="{{ route('keywords.trending') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ __('common.view_all') }} →</a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @php
                    $trendColors = [
                        ['from' => '#4F46E5', 'to' => '#7C3AED', 'bg' => 'bg-indigo-50 dark:bg-indigo-950/30', 'border' => 'border-l-indigo-500'],
                        ['from' => '#DC2626', 'to' => '#F87171', 'bg' => 'bg-red-50 dark:bg-red-950/30', 'border' => 'border-l-red-500'],
                        ['from' => '#059669', 'to' => '#34D399', 'bg' => 'bg-green-50 dark:bg-green-950/30', 'border' => 'border-l-green-500'],
                        ['from' => '#D97706', 'to' => '#FBBF24', 'bg' => 'bg-yellow-50 dark:bg-yellow-950/30', 'border' => 'border-l-yellow-500'],
                        ['from' => '#DB2777', 'to' => '#F472B6', 'bg' => 'bg-pink-50 dark:bg-pink-950/30', 'border' => 'border-l-pink-500'],
                        ['from' => '#0891B2', 'to' => '#22D3EE', 'bg' => 'bg-cyan-50 dark:bg-cyan-950/30', 'border' => 'border-l-cyan-500'],
                        ['from' => '#EA580C', 'to' => '#FB923C', 'bg' => 'bg-orange-50 dark:bg-orange-950/30', 'border' => 'border-l-orange-500'],
                        ['from' => '#7C3AED', 'to' => '#A78BFA', 'bg' => 'bg-purple-50 dark:bg-purple-950/30', 'border' => 'border-l-purple-500'],
                    ];
                @endphp
                @forelse($trendingTopics ?? collect() as $topic)
                    @php
                        $searchUrl = $topic->mapped_content
                            ? url('/' . ($topic->category?->slug ?? 'x') . '/' . ($topic->location?->slug ?? 'x') . '/' . $topic->mapped_content->slug)
                            : route('search') . '?q=' . urlencode($topic->keyword);
                        $c = $trendColors[$loop->index % count($trendColors)];
                    @endphp
                    <a href="{{ $searchUrl }}" 
                       class="group block {{ $c['bg'] }} rounded-xl p-4 hover:shadow-lg transition-all border border-gray-200/50 dark:border-slate-700/50 border-l-4 {{ $c['border'] }}"
                       style="border-left-color: {{ $c['from'] }};">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $topic->keyword }}</h3>
                                @if($topic->category)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $topic->category->name }}</p>
                                @endif
                            </div>
                            @if($topic->search_volume)
                                <span class="shrink-0 ml-2 px-2 py-0.5 text-xs font-medium rounded-full text-white" style="background: linear-gradient(135deg, {{ $c['from'] }}, {{ $c['to'] }});">
                                    {{ number_format($topic->search_volume) }}
                                </span>
                            @endif
                        </div>
                        @if($topic->location)
                            <div class="flex items-center gap-1 mt-2 text-xs text-gray-400 dark:text-gray-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $topic->location->name }}
                            </div>
                        @endif
                    </a>
                @empty
                    @php
                    $fallbackTopics = [
                        ['keyword' => 'İstanbul Restaurant', 'category' => 'Restaurant', 'location' => 'İstanbul', 'volume' => 15400],
                        ['keyword' => 'Ankara Otel', 'category' => 'Otel', 'location' => 'Ankara', 'volume' => 12300],
                        ['keyword' => 'İzmir Spa', 'category' => 'Spa', 'location' => 'İzmir', 'volume' => 9800],
                        ['keyword' => 'Bursa Cafe', 'category' => 'Cafe', 'location' => 'Bursa', 'volume' => 7600],
                        ['keyword' => 'Antalya Tur', 'category' => 'Turizm', 'location' => 'Antalya', 'volume' => 11200],
                        ['keyword' => 'Konya Tarih', 'category' => 'Tarih', 'location' => 'Konya', 'volume' => 5400],
                        ['keyword' => 'Adana Kebap', 'category' => 'Restaurant', 'location' => 'Adana', 'volume' => 8900],
                        ['keyword' => 'Trabzon Yayla', 'category' => 'Turizm', 'location' => 'Trabzon', 'volume' => 4300],
                    ];
                    @endphp
                    @foreach($fallbackTopics as $i => $topic)
                        @php $c = $trendColors[$i % count($trendColors)]; @endphp
                        <a href="{{ route('search') }}?q={{ urlencode($topic['keyword']) }}" 
                           class="group block {{ $c['bg'] }} rounded-xl p-4 hover:shadow-lg transition-all border border-gray-200/50 dark:border-slate-700/50 border-l-4 {{ $c['border'] }}"
                           style="border-left-color: {{ $c['from'] }};">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $topic['keyword'] }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $topic['category'] }}</p>
                                </div>
                                <span class="shrink-0 ml-2 px-2 py-0.5 text-xs font-medium rounded-full text-white" style="background: linear-gradient(135deg, {{ $c['from'] }}, {{ $c['to'] }});">
                                    {{ number_format($topic['volume']) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 mt-2 text-xs text-gray-400 dark:text-gray-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $topic['location'] }}
                            </div>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <x-ad-renderer position="mid_content_1" />

    <!-- AD SPACE: LEADERBOARD -->
    <section class="max-w-7xl mx-auto px-4 pb-8">
        <x-ad-renderer position="top" />
    </section>

    <!-- RECENT CONTENT GRID -->
    <section class="max-w-7xl mx-auto px-4 pb-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ __('home.latest_content') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($recentContent ?? collect() as $content)
                @php
                    $imageUrl = $content->featured_image;
                    if (!$imageUrl) {
                        $imageHelper = app(\App\Services\ImageHelper::class);
                        $imageData = $imageHelper->generateFromContentNode($content);
                        $imageUrl = $imageData['url'];
                    }
                @endphp
                <a href="{{ url('/' . ($content->taxonomy?->slug ?? 'x') . '/' . ($content->location?->slug ?? 'x') . '/' . $content->slug) }}" 
                   class="group bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all">
                    <div class="aspect-video bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-slate-700 dark:to-slate-600 relative overflow-hidden">
                        <img src="{{ $imageUrl }}" alt="{{ $content->seo_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @if($content->taxonomy)
                            <span class="absolute top-3 left-3 px-2 py-1 bg-indigo-600 text-white text-xs rounded">{{ $content->taxonomy->name }}</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-2 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $content->seo_title }}</h3>
                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                            @if($content->location)
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $content->location->name }}
                            @endif
                            @if($content->publish_date)
                                <span class="ml-auto">{{ $content->publish_date->format('d M') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">{{ __('home.no_content_yet') }}</div>
            @endforelse
        </div>
    </section>

    <!-- TRENDING SECTION (keywords with content, like Latest Content) -->
    <section class="bg-gray-50 dark:bg-slate-900 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ __('home.trending_now') }} 🔥</h2>
                <a href="{{ route('keywords.trending') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ __('common.view_all') }} →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($trendingContent ?? collect() as $content)
                    @php
                        $imageUrl = $content->featured_image;
                        if (!$imageUrl) {
                            $imageHelper = app(\App\Services\ImageHelper::class);
                            $imageData = $imageHelper->generateFromContentNode($content);
                            $imageUrl = $imageData['url'];
                        }
                    @endphp
                    <a href="{{ url('/' . ($content->taxonomy?->slug ?? 'x') . '/' . ($content->location?->slug ?? 'x') . '/' . $content->slug) }}" 
                       class="group bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all border-2 border-transparent hover:border-indigo-500/30">
                        <div class="aspect-video bg-gradient-to-br from-pink-100 to-purple-100 dark:from-slate-700 dark:to-slate-600 relative overflow-hidden">
                            <img src="{{ $imageUrl }}" alt="{{ $content->seo_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @if($content->taxonomy)
                                <span class="absolute top-3 left-3 px-2 py-1 bg-pink-600 text-white text-xs rounded">{{ $content->taxonomy->name }}</span>
                            @endif
                            <span class="absolute top-3 right-3 px-2 py-1 bg-orange-500 text-white text-xs rounded-full">🔥 {{ __('common.trend') }}</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-2 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $content->seo_title }}</h3>
                            <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                @if($content->location)
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    {{ $content->location->name }}
                                @endif
                                @if($content->publish_date)
                                    <span class="ml-auto">{{ $content->publish_date->format('d M') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    @php
                        $fallbackContent = $recentContent ?? collect();
                    @endphp
                    @foreach($fallbackContent->take(4) as $content)
                        @php
                            $imageUrl = $content->featured_image;
                            if (!$imageUrl) {
                                $imageHelper = app(\App\Services\ImageHelper::class);
                                $imageData = $imageHelper->generateFromContentNode($content);
                                $imageUrl = $imageData['url'];
                            }
                        @endphp
                        <a href="{{ url('/' . ($content->taxonomy?->slug ?? 'x') . '/' . ($content->location?->slug ?? 'x') . '/' . $content->slug) }}" 
                           class="group bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all">
                            <div class="aspect-video bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-slate-700 dark:to-slate-600 relative overflow-hidden">
                                <img src="{{ $imageUrl }}" alt="{{ $content->seo_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @if($content->taxonomy)
                                    <span class="absolute top-3 left-3 px-2 py-1 bg-indigo-600 text-white text-xs rounded">{{ $content->taxonomy->name }}</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-2 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $content->seo_title }}</h3>
                                <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                    @if($content->location)
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        {{ $content->location->name }}
                                    @endif
                                    @if($content->publish_date)
                                        <span class="ml-auto">{{ $content->publish_date->format('d M') }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <x-ad-renderer position="bottom" />

    <!-- STATS FOOTER -->
    <section class="bg-gray-900 dark:bg-slate-950 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-3 gap-8 text-center">
                <div>
                    <p class="text-4xl font-bold text-pink-400">{{ number_format($stats['total_content'] ?? 0) }}</p>
                    <p class="text-gray-400 mt-2">{{ __('common.total_pages') }}</p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-purple-400">{{ number_format($stats['total_taxonomies'] ?? 0) }}</p>
                    <p class="text-gray-400 mt-2">{{ __('common.categories') }}</p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-pink-400">{{ number_format($stats['total_locations'] ?? 0) }}</p>
                    <p class="text-gray-400 mt-2">{{ __('common.locations') }}</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection