@extends('layouts.app')

@section('title', __('search.search_results') . ' - ' . $q . ' - ' . config('app.name'))

@section('meta_description', __('search.search_results') . ' - ' . $q . ' - ' . config('app.name'))

@push('head')
<meta name="robots" content="noindex, follow">
<style>
    .search-result-card { transition: all 0.3s ease; }
    .search-result-card:hover { transform: translateY(-2px); }
    .highlight { background: #fef08a; padding: 0 2px; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Search Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                {{ __('search.search_results') }} "<span class="text-indigo-600">{{ $q }}</span>"
            </h1>
            <p class="text-gray-500 dark:text-gray-400">
                {{ $contentNodes->total() }} {{ __('search.results_found') }}
            </p>
        </div>

        <!-- Ad Slot -->
        <div class="mb-6">
            <x-ad-renderer position="top" />
        </div>

        <!-- Quick Filters -->
        @if($taxonomies->count() > 0 || $locations->count() > 0)
        <div class="flex flex-wrap gap-4 mb-8">
            @foreach($taxonomies as $taxonomy)
                <a href="{{ url('/' . $taxonomy->slug) }}" 
                   class="px-4 py-2 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full text-sm hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    {{ $taxonomy->name }}
                </a>
            @endforeach
            @foreach($locations as $location)
                <a href="{{ url('/location/' . $location->slug) }}" 
                   class="px-4 py-2 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded-full text-sm hover:bg-green-200 dark:hover:bg-green-800">
                    {{ $location->name }}
                </a>
            @endforeach
        </div>
        @endif

        <!-- Results Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($contentNodes as $content)
                <a href="{{ url('/' . ($content->taxonomy?->slug ?? 'x') . '/' . ($content->location?->slug ?? 'x') . '/' . $content->slug) }}" 
                   class="search-result-card bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden hover:shadow-xl">
                    @if($content->featured_image)
                        <div class="aspect-video bg-gray-200 dark:bg-slate-700">
                            <img src="{{ $content->featured_image }}" alt="{{ $content->seo_title }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center">
                            <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        @if($content->taxonomy)
                            <span class="inline-block px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs rounded mb-2">
                                {{ $content->taxonomy->name }}
                            </span>
                        @endif
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                            {{ $content->seo_title ?? $content->title }}
                        </h2>
                        @if($content->meta_description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                                {{ $content->meta_description }}
                            </p>
                        @endif
                        <div class="flex items-center text-xs text-gray-400">
                            @if($content->location)
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $content->location->name }}
                            @endif
                            @if($content->publish_date)
                                <span class="ml-auto">{{ $content->publish_date->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('search.no_results') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('search.try_different_keywords') }}</p>
                    <a href="{{ url('/categories') }}" class="mt-4 inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        {{ __('search.browse_categories') }}
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($contentNodes->hasPages())
        <div class="mt-8">
            {{ $contentNodes->appends(request()->query())->links() }}
        </div>
        @endif

    </div>
</div>
@endsection