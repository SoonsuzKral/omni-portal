@php
use App\Services\SeoService;
use App\Helpers\PlaceholderResolver;

$location = $location ?? null;
$seoService = app(SeoService::class);

// Resolve placeholders in title
$resolvedTitle = PlaceholderResolver::resolve($taxonomy->name, $location, null);
$seoData = $seoService->generateTaxonomySeo($taxonomy, $location, $contents->total());
$resolvedDescription = PlaceholderResolver::resolve($seoData['description'], $location, $taxonomy);

$locationsWithContent = \App\Models\Location::whereHas('contentNodes', function($q) use ($taxonomy) {
    $q->where('taxonomy_id', $taxonomy->id);
})->withCount(['contentNodes' => function($q) use ($taxonomy) {
    $q->where('taxonomy_id', $taxonomy->id);
}])->orderBy('content_nodes_count', 'desc')->limit(20)->get();

$childTaxonomies = \App\Models\Taxonomy::where('parent_id', $taxonomy->id)->get();
$siblingTaxonomies = $taxonomy->parent ? \App\Models\Taxonomy::where('parent_id', $taxonomy->parent_id)->where('id', '!=', $taxonomy->id)->get() : collect();
@endphp

@extends('layouts.app')

@section('title', $resolvedTitle . ($location ? ' - ' . $location->name : '') . ' - ' . config('app.name'))
@section('meta_description', Str::limit($resolvedDescription, 160))

@push('head')
<link rel="canonical" href="{{ url()->current() }}" />
@if($contents->currentPage() > 1)
    <meta name="robots" content="noindex, follow">
    <link rel="prev" href="{{ $contents->previousPageUrl() }}">
    @if($contents->hasMorePages())
        <link rel="next" href="{{ $contents->nextPageUrl() }}">
    @endif
@endif
<style>
    .dark .bg-card { background-color: #1e293b; }
    .location-chip { transition: all 0.2s ease; }
    .location-chip:hover { transform: translateY(-2px); }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center flex-wrap gap-2">
            <li><a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            @if($taxonomy->parent)
                <li><a href="{{ url('/' . $taxonomy->parent->slug) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ $taxonomy->parent->name }}</a></li>
                <li><span class="text-gray-400">/</span></li>
            @endif
            @if($location)
                <li><a href="{{ url('/' . $taxonomy->slug) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ $resolvedTitle }}</a></li>
                <li><span class="text-gray-400">/</span></li>
                <li class="text-gray-600 dark:text-gray-300 font-medium">{{ $location->name }}</li>
            @else
                <li class="text-gray-600 dark:text-gray-300 font-medium">{{ $resolvedTitle }}</li>
            @endif
        </ol>
    </nav>

    <header class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 md:p-8 mb-6 border border-gray-100 dark:border-slate-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                @if($taxonomy->parent)
                    <p class="text-sm text-indigo-600 dark:text-indigo-400 mb-1">{{ $taxonomy->parent->name }}</p>
                @endif
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
                    {{ $resolvedTitle }}
                    @if($location)
                        <span class="text-indigo-600 dark:text-indigo-400">{{ __('taxonomy.in_location') }} {{ $location->name }}</span>
                    @endif
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    {{ $contents->total() }} {{ __('common.articles') }}
                    @if($location)
                        {{ __('taxonomy.in_location') }} {{ $location->name }}
                    @endif
                </p>
            </div>
            @if($location)
            <div class="mt-4 md:mt-0 text-right">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.current_location') }}</span>
                <p class="text-xl font-semibold text-indigo-600 dark:text-indigo-400">{{ $location->name }}</p>
            </div>
            @endif
        </div>
    </header>

    @if(!$location && $locationsWithContent->count() > 0)
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-slate-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
            {{ __('common.browse_by_location') }}
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach($locationsWithContent as $loc)
            <a href="{{ url('/' . $taxonomy->slug . '/' . $loc->slug) }}"
               class="location-chip flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-700 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $loc->name }}</span>
                <span class="text-xs bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded">{{ $loc->content_nodes_count }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($childTaxonomies->count() > 0 || $siblingTaxonomies->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        @if($childTaxonomies->count() > 0)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('common.sub_categories') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($childTaxonomies as $child)
                <a href="{{ url('/' . $child->slug) }}" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800 transition">
                    {{ $child->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($siblingTaxonomies->count() > 0)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-gray-100 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('common.related_categories') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($siblingTaxonomies as $sibling)
                <a href="{{ url('/' . $sibling->slug) }}" class="px-3 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600 transition">
                    {{ $sibling->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <div class="space-y-4">
        @forelse($contents as $item)
            <article class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition p-5 border border-gray-100 dark:border-slate-700">
                <a href="{{ url('/' . $taxonomy->slug . '/' . ($item->location?->slug ?? '') . '/' . $item->slug) }}" class="block">
                    <div class="flex flex-col md:flex-row md:items-start">
                        @if($item->featured_image)
                        <div class="md:w-40 h-32 mb-3 md:mb-0 md:mr-4 flex-shrink-0 rounded-lg overflow-hidden">
                            <img src="{{ $item->featured_image }}" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform">
                        </div>
                        @endif
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $item->seo_title ?? $item->title }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">
                                {{ Str::limit(strip_tags($item->body_content), 200) }}
                            </p>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                @if($item->location)
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        {{ $item->location->name }}
                                    </span>
                                @endif
                                @if($item->publish_date)
                                    <span>{{ $item->publish_date->format('F d, Y') }}</span>
                                @endif
                                <span>{{ number_format($item->page_views ?? 0) }} {{ __('common.views') }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8 text-center border border-gray-100 dark:border-slate-700">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ __('taxonomy.no_content_category') }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">{{ __('common.check_back_soon') }}</p>
            </div>
        @endforelse
    </div>

    @if($contents->hasPages())
        <div class="mt-8">
            {{ $contents->links() }}
        </div>
    @endif
</div>
@endsection