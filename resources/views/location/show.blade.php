@php
    use App\Services\SeoService;
    $seoService = app(SeoService::class);
@endphp

@extends('layouts.app')

@section('title', $location->name . ' - ' . config('app.name'))

@section('meta_description', $location->name . ' - ' . $contentNodes->total() . ' ' . __('location.articles_in_location'))

@section('canonical', url()->current())

@if($contentNodes->currentPage() > 1)
    @push('head')
        <meta name="robots" content="noindex, follow">
        <link rel="prev" href="{{ $contentNodes->previousPageUrl() }}">
        @if($contentNodes->hasMorePages())
            <link rel="next" href="{{ $contentNodes->nextPageUrl() }}">
        @endif
    @endpush
@endif

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
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <x-ad-renderer position="above_breadcrumb" />

    <!-- Breadcrumb -->
    <nav class="text-sm mb-4">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ url('/') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('common.home') }}</a></li>
            <li><span class="text-gray-500 dark:text-gray-400">/</span></li>
            <li class="text-gray-600 dark:text-gray-300">{{ $location->name }}</li>
        </ol>
    </nav>

    <x-ad-renderer position="after_breadcrumb" />

    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $location->name }}</h1>
        <p class="text-gray-600 dark:text-gray-300">{{ $contentNodes->total() }} {{ __('location.articles_in_location') }}</p>

        <!-- Parent Location -->
        @if($location->parent)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('location.part_of') }}: <a href="{{ url('/location/' . $location->parent->slug) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $location->parent->name }}</a>
            </p>
        @endif
    </header>

    <x-ad-renderer position="below_title" />

    <!-- Child Locations (Districts) -->
    @if($children->count() > 0)
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow dark:shadow-slate-900/50 p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('location.districts') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($children as $child)
                    <a href="{{ url('/location/' . $child->slug) }}" class="bg-gray-100 dark:bg-slate-700 px-3 py-2 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/30">
                        {{ $child->name }}
                        <span class="text-gray-500 dark:text-gray-400 text-sm">({{ $child->content_nodes_count }})</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer :content="null" position="location_top" />
    </div>

    <x-ad-renderer position="before_content_list" />

    <!-- Content List -->
    <div class="space-y-4">
        @forelse($contentNodes as $content)
            <article class="bg-white dark:bg-slate-800 rounded-lg shadow dark:shadow-slate-900/50 hover:shadow-md dark:hover:shadow-slate-900/70 transition p-5">
                <a href="{{ url('/' . ($content->taxonomy?->slug ?? 'n') . '/' . ($content->location?->slug ?? 'n') . '/' . $content->slug) }}" class="block">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2 hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $content->title }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3 line-clamp-2">
                        {{ Str::limit(strip_tags($content->body_content), 200) }}
                    </p>
                    <div class="flex items-center text-xs text-gray-500 space-x-4">
                        @if($content->taxonomy)
                            <span class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded">{{ $content->taxonomy->name }}</span>
                        @endif
                        @if($content->publish_date)
                            <span class="text-gray-500 dark:text-gray-400">{{ $content->publish_date->format('F d, Y') }}</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400">{{ number_format($content->page_views ?? 0) }} {{ __('common.views') }}</span>
                    </div>
                </a>
            </article>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow dark:shadow-slate-900/50 p-6 text-center">
                <p class="text-gray-500 dark:text-gray-400">{{ __('location.no_content_location') }}</p>
            </div>
        @endforelse
    </div>

    <x-ad-renderer position="after_content_list" />

    <!-- Pagination -->
    @if($contentNodes->hasPages())
        <div class="mt-8">
            {{ $contentNodes->links() }}
        </div>
    @endif

    <x-ad-renderer position="bottom" />
</div>
@endsection