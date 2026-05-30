@extends('layouts.app')

@section('title', $content->seo_title)

@section('meta_description', $metaDescription)

@push('head')
    <x-seo-json-ld :content="$content" :location="$location" :taxonomy="$taxonomy" />
@endpush

@section('content')
@php
    // Clean title: remove location names that don't match the primary location
    $cleanedTitle = $content->title;
    if ($location) {
        $allLocationNames = \Illuminate\Support\Facades\Cache::remember('all_location_names_for_cleanup', 3600, function () {
            return \App\Models\Location::pluck('name')->toArray();
        });
        $currentLocName = mb_strtolower($location->name);
        foreach ($allLocationNames as $locName) {
            $locLower = mb_strtolower($locName);
            if ($locLower !== $currentLocName && $locLower !== '') {
                $cleanedTitle = preg_replace(
                    '/(?<=[\s,;.\/()!?\-]|^)' . preg_quote($locName, '/') . '(?=[\s,;.\/()!?\-]|$)/ui',
                    '',
                    $cleanedTitle
                );
            }
        }
        $cleanedTitle = trim(preg_replace('/\s+/', ' ', $cleanedTitle));
    }
@endphp
<article class="max-w-4xl mx-auto px-4 py-8">
    <x-ad-renderer :content="$content" position="header" />

    <nav class="flex mb-4 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="/" class="hover:text-indigo-400">{{ __('common.home') }}</a></li>
            <li><span>/</span></li>
            <li><a href="/{{ $taxonomy->slug }}" class="hover:text-indigo-400">{{ $taxonomy->name }}</a></li>
        </ol>
    </nav>

    <header class="mb-8">
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">
            {{ $cleanedTitle }}
        </h1>
        <div class="flex items-center text-gray-400 text-sm space-x-4">
            <span>&#128065; {{ number_format(($content->id * 47) + ($content->page_views * 3) + 1254) }} {{ __('common.views') }}</span>
            <span>&#128994; {{ rand(5, 18) }} {{ __('content.viewing') }}</span>
            <span>&#128197; {{ $content->publish_date->format('d M Y') }} ({{ __('content.last_updated') }}: {{ now()->diffForHumans() }})</span>
        </div>
    </header>

    <div class="prose prose-invert prose-indigo max-w-none mb-12 text-gray-300">
        @php
            // Clean body: remove non-primary location names (same as title cleanup)
            $cleanedBody = $resolvedBody;
            if ($location && isset($allLocationNames)) {
                foreach ($allLocationNames as $locName) {
                    $locLower = mb_strtolower($locName);
                    if ($locLower !== $currentLocName && $locLower !== '') {
                        $cleanedBody = preg_replace(
                            '/(?<=[\s,;.\/()!?\-]|^)' . preg_quote($locName, '/') . '(?=[\s,;.\/()!?\-]|$)/ui',
                            '',
                            $cleanedBody
                        );
                    }
                }
            }
            $paragraphs = preg_split('/(<\/p>)/', $cleanedBody, -1, PREG_SPLIT_DELIM_CAPTURE);
            $pCount = 0;
            $midAdPlaced = false;
        @endphp
        @foreach($paragraphs as $segment)
            @if(str_contains($segment, '</p>'))
                @php $pCount++; @endphp
                {!! $segment !!}
                @if($pCount === 3 && !$midAdPlaced)
                    @php $midAdPlaced = true; @endphp
                    <div class="my-6">
                        <x-ad-renderer :content="$content" position="mid" />
                    </div>
                @endif
            @else
                {!! $segment !!}
            @endif
        @endforeach
        @if(!$midAdPlaced)
            <div class="my-6">
                <x-ad-renderer :content="$content" position="mid" />
            </div>
        @endif
    </div>

    <div class="bg-indigo-900/30 border border-indigo-500/50 rounded-xl p-6 text-center my-8">
        <h3 class="text-xl font-semibold text-white mb-4">{{ __('content.support_title') }}</h3>
        <a href="#" class="inline-flex items-center justify-center px-8 py-3 font-bold text-white bg-indigo-600 rounded-full hover:bg-indigo-500 transition">
            {{ __('content.get_quote') }}
        </a>
    </div>

    <div class="my-6">
        <x-ad-renderer :content="$content" position="bottom" />
    </div>

    <section class="border-t border-gray-800 pt-8 mt-12">
        <h4 class="text-xl font-bold text-white mb-6">{{ __('content.similar_services') }}</h4>
        <x-semantic-link-matrix :content="$content" :location="$location" />
    </section>

    @php
        use App\Models\ContentNode;
        use App\Models\Location;
        use App\Models\Taxonomy;

        $relatedDistricts = collect();
        $relatedCategories = collect();

        if ($location) {
            $parentId = $location->parent_id;
            $siblingIds = Location::where('parent_id', $parentId)
                ->where('id', '!=', $location->id)
                ->whereHas('contentNodes', function ($q) use ($taxonomy) {
                    $q->where('taxonomy_id', $taxonomy->id)->whereNotNull('publish_date');
                })
                ->pluck('id');

            if ($siblingIds->isNotEmpty()) {
                $relatedDistricts = ContentNode::whereIn('location_id', $siblingIds)
                    ->where('taxonomy_id', $taxonomy->id)
                    ->whereNotNull('publish_date')
                    ->with(['location', 'taxonomy'])
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            }

            if ($relatedDistricts->count() < 10) {
                $fallback = ContentNode::where('taxonomy_id', $taxonomy->id)
                    ->where('location_id', '!=', $location->id)
                    ->whereNotNull('publish_date')
                    ->with(['location', 'taxonomy'])
                    ->inRandomOrder()
                    ->limit(10 - $relatedDistricts->count())
                    ->get();
                $relatedDistricts = $relatedDistricts->concat($fallback)->unique('id');
            }
        }

        $relatedCategoryIds = Taxonomy::where('parent_id', $taxonomy->parent_id)
            ->where('id', '!=', $taxonomy->id)
            ->whereHas('contentNodes', function ($q) use ($location) {
                $q->where('location_id', $location?->id)->whereNotNull('publish_date');
            })
            ->pluck('id');

        if ($relatedCategoryIds->isNotEmpty()) {
            $relatedCategories = ContentNode::whereIn('taxonomy_id', $relatedCategoryIds)
                ->where('location_id', $location?->id)
                ->whereNotNull('publish_date')
                ->with(['location', 'taxonomy'])
                ->inRandomOrder()
                ->limit(10)
                ->get();
        }

        if ($relatedCategories->count() < 10) {
            $fallbackCats = ContentNode::where('taxonomy_id', '!=', $taxonomy->id)
                ->where('location_id', $location?->id)
                ->whereNotNull('publish_date')
                ->with(['location', 'taxonomy'])
                ->inRandomOrder()
                ->limit(10 - $relatedCategories->count())
                ->get();
            $relatedCategories = $relatedCategories->concat($fallbackCats)->unique('id');
        }
    @endphp

    @if($relatedDistricts->isNotEmpty() || $relatedCategories->isNotEmpty())
    <section class="mt-16 border-t border-gray-800 pt-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @if($relatedDistricts->isNotEmpty())
            <div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    {{ $taxonomy->name }} - {{ __('content.other_locations') }}
                </h3>
                <ul class="space-y-2">
                    @foreach($relatedDistricts as $node)
                    <li>
                        <a href="{{ url('/' . $node->taxonomy?->slug . '/' . $node->location?->slug . '/' . $node->slug) }}"
                           class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 transition group">
                            <span class="text-sm text-gray-300 group-hover:text-indigo-300 truncate">{{ $node->title }}</span>
                            <span class="text-xs text-gray-500 shrink-0 ml-2">{{ $node->location?->name }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($relatedCategories->isNotEmpty())
            <div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    @if($location){{ $location->name }} - @endif{{ __('content.other_categories') }}
                </h3>
                <ul class="space-y-2">
                    @foreach($relatedCategories as $node)
                    <li>
                        <a href="{{ url('/' . $node->taxonomy?->slug . '/' . $node->location?->slug . '/' . $node->slug) }}"
                           class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 transition group">
                            <span class="text-sm text-gray-300 group-hover:text-purple-300 truncate">{{ $node->title }}</span>
                            <span class="text-xs text-gray-500 shrink-0 ml-2">{{ $node->taxonomy?->name }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </section>
    @endif

</article>

<div class="fixed bottom-0 left-0 right-0 z-50 md:hidden">
    <x-ad-renderer :content="$content" position="sticky" />
</div>

<div class="my-6">
    <x-trending-content :currentContent="$content" />
</div>

@endsection
