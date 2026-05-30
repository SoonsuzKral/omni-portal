@extends('layouts.app')

@section('title', 'Location Tree - ' . config('app.name'))

@section('content')
<div class="max-w-6xl mx-auto">
    <header class="mb-8">
        <h1 class="text-3xl font-bold">Location Tree</h1>
        <p class="text-gray-600">Hierarchical view of all locations.</p>
        <a href="{{ route('location.index') }}" class="text-indigo-600 hover:underline mt-2 block">← Back to all locations</a>
    </header>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @forelse($rootLocations as $city)
            <div class="border-b py-4">
                <a href="{{ url('/location/' . $city->slug) }}" class="text-xl font-bold text-indigo-700 hover:underline">
                    {{ $city->name }}
                </a>
                <span class="text-gray-500 text-sm ml-2">({{ $city->contentNodes->count() }} articles)</span>

                @if($city->children->count() > 0)
                    <div class="ml-6 mt-3 flex flex-wrap gap-3">
                        @foreach($city->children as $district)
                            <a href="{{ url('/location/' . $district->slug) }}" class="bg-gray-100 px-3 py-1 rounded hover:bg-gray-200 text-sm">
                                {{ $district->name }}
                                <span class="text-gray-400">({{ $district->contentNodes->count() }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="text-gray-500 text-center py-8">No locations found.</p>
        @endforelse
    </div>
</div>
@endsection