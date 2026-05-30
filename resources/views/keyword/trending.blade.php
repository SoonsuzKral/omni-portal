@extends('layouts.app')

@section('title', 'Trending Keywords - ' . config('app.name'))

@section('content')
<div class="max-w-6xl mx-auto">
    <header class="mb-8">
        <h1 class="text-3xl font-bold">Trending Keywords</h1>
        <p class="text-gray-600">Popular search terms in {{ strtoupper($language) }}</p>

        <!-- Language Filter -->
        <div class="flex flex-wrap gap-2">
            @foreach(['tr', 'en', 'ar', 'ru', 'fa', 'fr'] as $lang)
                <a href="{{ route('keywords.trending', ['lang' => $lang]) }}"
                   class="px-3 py-1 rounded {{ $language === $lang ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' }}">
                    {{ strtoupper($lang) }}
                </a>
            @endforeach
        </div>
    </header>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <!-- Generate More Keywords -->
    <div class="mb-6 flex flex-wrap gap-4">
        <a href="{{ route('keywords.generate', ['lang' => $language]) }}" class="bg-green-600 dark:bg-green-700 text-white px-4 py-2 rounded hover:bg-green-700 dark:hover:bg-green-600 transition">
            Generate More Keywords
        </a>
        <a href="{{ route('keywords.auto-create') }}" class="bg-purple-600 dark:bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-700 dark:hover:bg-purple-600 transition">
            Auto-Create Content
        </a>
    </div>

    @if($language === 'tr' && $turkeyTrends->isNotEmpty())
    <!-- Türkiye Gündemi — Daily Turkey Trends -->
    <section class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">🇹🇷</span>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Türkiye Gündemi</h2>
            <span class="text-sm text-gray-500">Günlük popüler aramalar</span>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($turkeyTrends as $keyword)
            <a href="{{ $keyword->mapped_content ? url('/' . ($keyword->category?->slug ?? 'x') . '/' . ($keyword->location?->slug ?? 'x') . '/' . $keyword->mapped_content->slug) : '#' }}"
               class="bg-white dark:bg-slate-800 rounded-lg shadow p-4 hover:shadow-lg transition border-l-4 border-red-500 {{ $keyword->mapped_content ? '' : 'opacity-60' }}">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-red-700 dark:text-red-400">{{ $keyword->keyword }}</h3>
                    <span class="text-red-500 text-xs">🔥</span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                    <p>Volume: {{ number_format($keyword->search_volume) }}</p>
                    @if($keyword->location)
                        <p>Location: {{ $keyword->location->name }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Keywords Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($keywords as $keyword)
            <a href="{{ $keyword->mapped_content ? url('/' . ($keyword->category?->slug ?? 'x') . '/' . ($keyword->location?->slug ?? 'x') . '/' . $keyword->mapped_content->slug) : '#' }}"
               class="bg-white dark:bg-slate-800 rounded-lg shadow p-4 hover:shadow-lg transition {{ $keyword->mapped_content ? '' : 'opacity-60' }}">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-indigo-700 dark:text-indigo-400">{{ $keyword->keyword }}</h3>
                    @if($keyword->is_trending)
                        <span class="text-red-500 text-xs">🔥 Trending</span>
                    @endif
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                    <p>Volume: {{ number_format($keyword->search_volume) }}</p>
                    <p>Difficulty: {{ $keyword->difficulty }}/100</p>
                    @if($keyword->category)
                        <p>Category: {{ $keyword->category->name }}</p>
                    @endif
                    @if($keyword->location)
                        <p>Location: {{ $keyword->location->name }}</p>
                    @endif
                </div>
                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    {{ $keyword->mapped_content ? '✅ Has Content' : '❌ No Content' }}
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                No trending keywords found.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $keywords->links() }}
    </div>
</div>
@endsection