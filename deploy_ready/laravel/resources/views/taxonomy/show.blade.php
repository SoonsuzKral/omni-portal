@extends('layouts.app')

@section('title', $taxonomy->name . ' - ' . config('app.name'))

@section('meta_description', $taxonomy->name . ' ' . __('taxonomy.pages_in_category'))

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

@section('content')
<div class="max-w-5xl mx-auto">
    <x-ad-renderer position="above_breadcrumb" />

    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center flex-wrap gap-2">
            <li><a href="/" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li><a href="{{ url('/categories') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('common.categories') }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li class="text-gray-600 dark:text-gray-300 font-medium">{{ $taxonomy->name }}</li>
        </ol>
    </nav>

    <x-ad-renderer position="after_breadcrumb" />

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <header class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-slate-700">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold">
                {{ substr($taxonomy->name, 0, 2) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $taxonomy->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ $contentNodes->total() }} {{ __('taxonomy.pages_in_category') }}</p>
                @if($taxonomy->parent)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('taxonomy.parent_category') }}: <a href="{{ url('/' . $taxonomy->parent->slug) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $taxonomy->parent->name }}</a>
                    </p>
                @endif
            </div>
        </div>
    </header>

    <x-ad-renderer position="below_title" />

    @if($taxonomy->children->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-slate-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('common.sub_categories') }}</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($taxonomy->children as $child)
                <a href="{{ url('/' . $child->slug) }}" class="px-3 py-2 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800 transition text-sm">
                    {{ $child->name }}
                    <span class="ml-1 text-xs opacity-75">({{ $child->contentNodes_count ?? 0 }})</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <x-ad-renderer position="before_content_list" />

    <div class="space-y-4">
        @forelse($contentNodes as $node)
            <article class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition p-5 border border-gray-100 dark:border-slate-700">
                <a href="{{ url('/' . $taxonomy->slug . '/' . ($node->location?->slug ?? '') . '/' . $node->slug) }}" class="block">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $node->seo_title ?? $node->title }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">
                        {{ Str::limit(strip_tags($node->body_content), 200) }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        @if($node->location)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $node->location->name }}
                            </span>
                        @endif
                        @if($node->publish_date)
                            <span>{{ $node->publish_date->format('d M Y') }}</span>
                        @endif
                    </div>
                </a>
            </article>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8 text-center border border-gray-100 dark:border-slate-700">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ __('taxonomy.no_content_category') }}</p>
            </div>
        @endforelse
    </div>

    <x-ad-renderer position="after_content_list" />

    @if($contentNodes->hasPages())
        <div class="mt-8">
            {{ $contentNodes->links() }}
        </div>
    @endif

    <x-ad-renderer position="bottom" />
</div>
@endsection
