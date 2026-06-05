{{-- resources/views/components/related-locations.blade.php --}}
@php
    // $currentLocation is passed from the parent view (may be null)
    $query = \App\Models\ContentNode::query()
        ->whereNotNull('slug')
        ->where('is_restricted_content', false)
        ->orderByDesc('page_views');

    if (isset($currentLocation) && $currentLocation) {
        // Same city (parent) – assume location hierarchy: city -> district -> neighborhood
        $cityId = $currentLocation->parent_id ?? $currentLocation->id;
        $query->where('location_id', $cityId);
    }
    $related = $query->limit(10)->get();
@endphp
<ul class="space-y-1 text-sm">
    @foreach($related as $node)
        <li>
            <a href="{{ route('content.show', [
                'category' => $node->taxonomy->slug,
                'locationSlug' => $node->location->slug ?? 'unknown',
                'slug' => $node->slug,
            ]) }}" class="hover:underline" title="{{ $node->seo_title }}">
                {{ $node->seo_title }}
            </a>
        </li>
    @endforeach
</ul>
