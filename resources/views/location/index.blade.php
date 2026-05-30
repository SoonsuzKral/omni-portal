@php
    use App\Services\SeoService;
    $seoService = app(SeoService::class);
    $seoData = $seoService->generateTaxonomySeo(null, $location, $contents->total());
@endphp

@extends('layouts.app')

@section('title', $location->name . ' - ' . config('app.name'))
@section('meta_description', $location->name . ' - ' . $contents->total() . ' ' . __('location.articles_in_location'))

@push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "{{ $location->name }}",
        "description": "All content in {{ $location->name }}",
        "url": "{{ url()->current() }}"
    }
    </script>
    <link rel="canonical" href="{{ url()->current() }}" />
    <style>
        .dark .bg-card { background-color: #1e293b; }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="text-sm mb-6">
        <ol class="flex items-center flex-wrap gap-2">
            <li><a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li class="text-gray-600 dark:text-gray-300 font-medium">{{ $location->name }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-slate-700">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold">
                {{ substr($location->name, 0, 2) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $location->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ $contents->total() }} {{ __('location.articles_in_location') }}</p>
            </div>
        </div>
    </header>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer :content="null" position="location_top" />
    </div>

    <!-- Content List -->
    <div class="space-y-4">
        @forelse($contents as $content)
            <article class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition p-5 border border-gray-100 dark:border-slate-700">
                <a href="{{ url('/' . ($content->taxonomy?->slug ?? '') . '/' . ($content->location?->slug ?? '') . '/' . $content->slug) }}" class="block">
                    <div class="flex flex-col md:flex-row md:items-start">
                        @if($content->featured_image)
                        <div class="md:w-40 h-32 mb-3 md:mb-0 md:mr-4 flex-shrink-0 rounded-lg overflow-hidden">
                            <img src="{{ $content->featured_image }}" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform">
                        </div>
                        @endif
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $content->seo_title ?? $content->title }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">
                                {{ Str::limit(strip_tags($content->body_content), 200) }}
                            </p>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                @if($content->taxonomy)
                                    <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded">{{ $content->taxonomy->name }}</span>
                                @endif
                                @if($content->publish_date)
                                    <span>{{ $content->publish_date->format('F d, Y') }}</span>
                                @endif
                                <span>{{ number_format($content->page_views ?? 0) }} {{ __('common.views') }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8 text-center border border-gray-100 dark:border-slate-700">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ __('location.no_content_location') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($contents->hasPages())
        <div class="mt-8">
            {{ $contents->links() }}
        </div>
    @endif
</div>
@endsection