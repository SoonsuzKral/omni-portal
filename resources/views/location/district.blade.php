@extends('layouts.app')

@section('title', $district->name . ', ' . $city->name . ' - ' . config('app.name'))

@section('meta_description', $district->name . ', ' . $city->name . ' - ' . __('location.articles_in_district'))

@section('content')
<div class="max-w-5xl mx-auto">
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center flex-wrap gap-2">
            <li><a href="/" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li><a href="{{ url('/locations') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('common.locations') }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li><a href="{{ url('/location/' . $city->slug) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ $city->name }}</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li class="text-gray-600 dark:text-gray-300 font-medium">{{ $district->name }}</li>
        </ol>
    </nav>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="location_top" />
    </div>

    <header class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-slate-700">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold">
                {{ substr($district->name, 0, 2) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $district->name }}, {{ $city->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ $contentNodes->total() }} {{ __('location.articles_in_district') }}</p>
            </div>
        </div>
    </header>

    <div class="space-y-4">
        @forelse($contentNodes as $node)
            <article class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition p-5 border border-gray-100 dark:border-slate-700">
                <a href="{{ url('/' . ($node->taxonomy?->slug ?? '') . '/' . $district->slug . '/' . $node->slug) }}" class="block">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $node->seo_title ?? $node->title }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2">
                        {{ Str::limit(strip_tags($node->body_content), 200) }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        @if($node->taxonomy)
                            <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded">{{ $node->taxonomy->name }}</span>
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
                <p class="text-gray-500 dark:text-gray-400">{{ __('location.no_content_district') }}</p>
            </div>
        @endforelse
    </div>

    @if($contentNodes->hasPages())
        <div class="mt-8">
            {{ $contentNodes->links() }}
        </div>
    @endif
</div>
@endsection
