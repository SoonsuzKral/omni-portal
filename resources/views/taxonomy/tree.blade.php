@extends('layouts.app')

@section('title', 'Category Tree - ' . config('app.name'))

@section('content')
<div class="max-w-6xl mx-auto">
    <header class="mb-8">
        <h1 class="text-3xl font-bold">Category Tree</h1>
        <p class="text-gray-600">Hierarchical view of all taxonomy categories.</p>
        <a href="{{ route('taxonomy.index') }}" class="text-indigo-600 hover:underline mt-2 block">← Back to all categories</a>
    </header>

    <!-- Ad Slot -->
    <div class="mb-6">
        <x-ad-renderer position="top" />
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @forelse($rootTaxonomies as $taxonomy)
            <div class="border-b py-4">
                <a href="{{ url('/' . $taxonomy->slug) }}" class="text-xl font-bold text-indigo-700 hover:underline">
                    {{ $taxonomy->name }}
                </a>
                <span class="text-gray-500 text-sm ml-2">({{ $taxonomy->contentNodes->count() }} pages)</span>

                @if($taxonomy->children->count() > 0)
                    <div class="ml-6 mt-3 flex flex-wrap gap-3">
                        @foreach($taxonomy->children as $child)
                            <a href="{{ url('/' . $child->slug) }}" class="bg-gray-100 px-3 py-1 rounded hover:bg-gray-200 text-sm">
                                {{ $child->name }}
                                <span class="text-gray-400">({{ $child->contentNodes->count() }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($taxonomy->children->count() > 0)
                    @foreach($taxonomy->children as $child)
                        @if($child->children->count() > 0)
                            <div class="ml-12 mt-2 flex flex-wrap gap-2">
                                @foreach($child->children as $grandchild)
                                    <a href="{{ url('/' . $grandchild->slug) }}" class="text-xs bg-gray-50 px-2 py-1 rounded hover:bg-gray-100">
                                        {{ $grandchild->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        @empty
            <p class="text-gray-500 text-center py-8">No categories found.</p>
        @endforelse
    </div>
</div>
@endsection