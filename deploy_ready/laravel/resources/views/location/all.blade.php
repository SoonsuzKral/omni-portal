@extends('layouts.app')

@section('title', 'All Locations - ' . config('app.name'))

@section('content')
<div class="max-w-6xl mx-auto">
    <x-ad-renderer position="above_breadcrumb" />

    <header class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-slate-700">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">All Locations</h1>
        <p class="text-gray-600 dark:text-slate-400">Browse all locations (cities, districts, neighborhoods).</p>
    </header>

    <x-ad-renderer position="below_title" />

    <!-- Tree View Link -->
    <div class="mb-6">
        <a href="{{ route('location.tree') }}" class="text-indigo-600 hover:underline">View as Tree →</a>
    </div>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <x-ad-renderer position="before_content_list" />

    <!-- Locations Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($cities as $city)
            <a href="{{ url('/location/' . $city->slug) }}" class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-xl shadow-sm hover:shadow-lg p-6 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                <h2 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">{{ $city->name }}</h2>
                <p class="text-gray-500 dark:text-slate-400 text-sm">{{ $city->content_nodes_count }} articles</p>
            </a>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-slate-400">
                No locations found.
            </div>
        @endforelse
    </div>

    <x-ad-renderer position="after_content_list" />

    <!-- Pagination -->
    <div class="mt-8">
        {{ $cities->links() }}
    </div>

    <x-ad-renderer position="bottom" />
</div>
@endsection