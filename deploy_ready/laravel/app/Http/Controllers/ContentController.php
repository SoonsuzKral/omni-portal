<?php

namespace App\Http\Controllers;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\PlaceholderResolver;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    /**
     * Display a content node based on full URL structure.
     * Route: /{category}/{location_slug}/{slug}
     */
    public function show($category, $locationSlug, $slug)
    {
        // Find the taxonomy (category)
        $taxonomy = Taxonomy::where('slug', $category)->firstOrFail();

        // Find location (optional)
        $location = Location::where('slug', $locationSlug)->firstOrFail();

        $content = ContentNode::where('slug', $slug)
            ->where('taxonomy_id', $taxonomy->id)
            ->where('location_id', $location->id)
            ->with(['taxonomy', 'location'])
            ->firstOrFail();

        // Increment page views
        $content->increment('page_views');

        // Resolve placeholders and spintax in the body content
        $resolvedBody = PlaceholderResolver::resolve($content->body_content, $location, $taxonomy);
        // Generate meta description (first 160 chars)
        $metaDescription = PlaceholderResolver::metaDescription($resolvedBody);
        // Pass resolved content and meta data to view
        return view('content.show', [
            'content' => $content,
            'taxonomy' => $taxonomy,
            'location' => $location,
            'resolvedBody' => $resolvedBody,
            'metaDescription' => $metaDescription,
        ]);
    }

    /**
     * List contents for a taxonomy + location.
     * Route: /{category}/{locationSlug}
     */
    public function index($category, $locationSlug = null)
    {
        $taxonomy = Taxonomy::where('slug', $category)->firstOrFail();
        $location = null;

        $query = ContentNode::where('taxonomy_id', $taxonomy->id)
            ->with(['taxonomy', 'location']);

        if ($locationSlug) {
            $location = Location::where('slug', $locationSlug)->firstOrFail();
            $query->where('location_id', $location->id);
        }

        $contents = $query->orderBy('publish_date', 'desc')->paginate(20);

        return view('taxonomy.index', compact('taxonomy', 'location', 'contents'));
    }

    /**
     * List contents for a taxonomy only.
     * Route: /{category}
     */
    public function taxonomyIndex($category)
    {
        $taxonomy = Taxonomy::where('slug', $category)->firstOrFail();

        $contents = ContentNode::where('taxonomy_id', $taxonomy->id)
            ->with(['taxonomy', 'location'])
            ->orderBy('publish_date', 'desc')
            ->paginate(20);

        return view('taxonomy.index', compact('taxonomy', 'contents'));
    }

    /**
     * List contents for a location only.
     * Route: /location/{slug}
     */
    public function locationIndex($slug)
    {
        $location = Location::where('slug', $slug)->firstOrFail();

        $contents = ContentNode::where('location_id', $location->id)
            ->with('taxonomy')
            ->orderBy('publish_date', 'desc')
            ->paginate(20);

        return view('location.index', compact('location', 'contents'));
    }

    public function showBySlug($slug)
    {
        $content = ContentNode::where('slug', $slug)
            ->with(['taxonomy', 'location'])
            ->firstOrFail();
        
        $content->increment('page_views');
        
        $location = $content->location;
        $taxonomy = $content->taxonomy;
        
        $resolvedBody = PlaceholderResolver::resolve($content->body_content, $location, $taxonomy);
        $metaDescription = PlaceholderResolver::metaDescription($resolvedBody);
        
        return view('content.show', [
            'content' => $content,
            'taxonomy' => $taxonomy,
            'location' => $location,
            'resolvedBody' => $resolvedBody,
            'metaDescription' => $metaDescription,
        ]);
    }

    /**
     * Full-text search for content.
     * Route: /search (content.search)
     * Searches in: title, body_content, seo_title, meta_description
     * Supports: location_id and taxonomy_id filters
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $locationFilter = $request->get('location_id', '');
        $taxonomyFilter = $request->get('taxonomy_id', '');

        if (empty($q)) {
            return redirect('/');
        }

        $query = ContentNode::whereNotNull('publish_date')
            ->with(['taxonomy', 'location']);

        // Search in title and body_content with LIKE
        $query->where(function ($builder) use ($q) {
            $builder->where('seo_title', 'like', '%' . $q . '%')
                ->orWhere('body_content', 'like', '%' . $q . '%')
                ->orWhere('meta_description', 'like', '%' . $q . '%')
                ->orWhere('slug', 'like', '%' . \Illuminate\Support\Str::slug($q) . '%');
        });

        // Filter by location_id
        if (!empty($locationFilter)) {
            $query->where('location_id', $locationFilter);
        }

        // Filter by taxonomy_id
        if (!empty($taxonomyFilter)) {
            $query->where('taxonomy_id', $taxonomyFilter);
        }

        $contentNodes = $query->orderBy('page_views', 'desc')->paginate(20);

        $taxonomies = \App\Models\Taxonomy::where(function ($b) use ($q) {
                $b->where('name', 'like', '%' . $q . '%')
                  ->orWhere('slug', 'like', '%' . \Illuminate\Support\Str::slug($q) . '%');
            })
            ->paginate(20);

        $locations = \App\Models\Location::where(function ($b) use ($q) {
                $b->where('name', 'like', '%' . $q . '%')
                  ->orWhere('slug', 'like', '%' . \Illuminate\Support\Str::slug($q) . '%');
            })
            ->paginate(20);

        return view('search.results', compact('contentNodes', 'q', 'taxonomies', 'locations'));
    }

    /**
     * API: Google-style autocomplete suggestions.
     * Route: GET /api/v1/search/suggestions?q=...
     * Searches locations, taxonomies, and content_nodes simultaneously.
     */
    public function suggestions(Request $request)
    {
        $q = $request->get('q', '');

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $like = '%' . $q . '%';

        $results = collect();

        // Locations
        $locations = Location::where('name', 'like', $like)
            ->limit(10)
            ->get()
            ->map(fn($l) => [
                'type' => 'location',
                'label' => $l->name,
                'url' => url('/location/' . $l->slug),
            ]);

        // Taxonomies (categories)
        $taxonomies = Taxonomy::where('name', 'like', $like)
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'type' => 'category',
                'label' => $t->name,
                'url' => url('/' . $t->slug),
            ]);

        // Content nodes
        $contentNodes = ContentNode::where('seo_title', 'like', $like)
            ->whereNotNull('publish_date')
            ->with(['taxonomy:id,slug,name', 'location:id,slug,name'])
            ->orderBy('page_views', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'type' => 'post',
                'label' => $c->seo_title,
                'url' => url('/' . ($c->taxonomy?->slug ?? 'x') . '/' . ($c->location?->slug ?? 'x') . '/' . $c->slug),
            ]);

        $results = $locations->concat($taxonomies)->concat($contentNodes)->take(10)->values();

        return response()->json($results);
    }
}
