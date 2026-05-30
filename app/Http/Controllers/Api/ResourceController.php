<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\LiveDataVault;
use App\Models\PostTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * GET /api/v1/taxonomies - List all taxonomies
     */
    public function taxonomies(Request $request)
    {
        $query = Taxonomy::withCount('contentNodes');
        
        if ($parentId = $request->get('parent_id')) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $taxonomies = $query->orderBy('name')->paginate(50);

        return response()->json($taxonomies);
    }

    /**
     * POST /api/v1/taxonomies - Create taxonomy
     */
    public function createTaxonomy(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/|unique:taxonomies,slug',
            'parent_slug' => 'sometimes|string|exists:taxonomies,slug',
        ]);

        $parentId = null;
        if (!empty($data['parent_slug'])) {
            $parent = Taxonomy::where('slug', $data['parent_slug'])->firstOrFail();
            $parentId = $parent->id;
        }

        $taxonomy = Taxonomy::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'parent_id' => $parentId,
        ]);

        return response()->json([
            'success' => true,
            'data' => $taxonomy,
        ], 201);
    }

    /**
     * GET /api/v1/taxonomies/{slug} - Get taxonomy
     */
    public function showTaxonomy(string $slug)
    {
        $taxonomy = Taxonomy::with(['parent', 'children', 'contentNodes'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($taxonomy);
    }

    /**
     * PUT /api/v1/taxonomies/{slug} - Update taxonomy
     */
    public function updateTaxonomy(Request $request, string $slug)
    {
        $taxonomy = Taxonomy::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/|unique:taxonomies,slug,' . $taxonomy->id,
            'parent_slug' => 'sometimes|string|nullable|exists:taxonomies,slug',
        ]);

        $parentId = null;
        if (isset($data['parent_slug'])) {
            if ($data['parent_slug']) {
                $parent = Taxonomy::where('slug', $data['parent_slug'])->firstOrFail();
                $parentId = $parent->id;
            }
        } else {
            $parentId = $taxonomy->parent_id;
        }

        $taxonomy->update([
            'name' => $data['name'] ?? $taxonomy->name,
            'slug' => $data['slug'] ?? $taxonomy->slug,
            'parent_id' => $parentId,
        ]);

        return response()->json(['success' => true, 'data' => $taxonomy]);
    }

    /**
     * DELETE /api/v1/taxonomies/{slug} - Delete taxonomy
     */
    public function deleteTaxonomy(string $slug)
    {
        $taxonomy = Taxonomy::where('slug', $slug)->firstOrFail();
        $taxonomy->delete();

        return response()->json(['success' => true, 'message' => 'Taxonomy deleted']);
    }

    /**
     * GET /api/v1/locations - List all locations
     */
    public function locations(Request $request)
    {
        $query = Location::withCount('contentNodes');
        
        if ($parentId = $request->get('parent_id')) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $locations = $query->orderBy('name')->paginate(50);

        return response()->json($locations);
    }

    /**
     * POST /api/v1/locations - Create location
     */
    public function createLocation(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/|unique:locations,slug',
            'parent_slug' => 'sometimes|string|exists:locations,slug',
        ]);

        $parentId = null;
        if (!empty($data['parent_slug'])) {
            $parent = Location::where('slug', $data['parent_slug'])->firstOrFail();
            $parentId = $parent->id;
        }

        $location = Location::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'parent_id' => $parentId,
        ]);

        return response()->json(['success' => true, 'data' => $location], 201);
    }

    /**
     * GET /api/v1/locations/{slug} - Get location
     */
    public function showLocation(string $slug)
    {
        $location = Location::with(['parent', 'children', 'contentNodes'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($location);
    }

    /**
     * PUT /api/v1/locations/{slug} - Update location
     */
    public function updateLocation(Request $request, string $slug)
    {
        $location = Location::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/|unique:locations,slug,' . $location->id,
            'parent_slug' => 'sometimes|string|nullable|exists:locations,slug',
        ]);

        $parentId = null;
        if (isset($data['parent_slug'])) {
            if ($data['parent_slug']) {
                $parent = Location::where('slug', $data['parent_slug'])->firstOrFail();
                $parentId = $parent->id;
            }
        } else {
            $parentId = $location->parent_id;
        }

        $location->update([
            'name' => $data['name'] ?? $location->name,
            'slug' => $data['slug'] ?? $location->slug,
            'parent_id' => $parentId,
        ]);

        return response()->json(['success' => true, 'data' => $location]);
    }

    /**
     * DELETE /api/v1/locations/{slug} - Delete location
     */
    public function deleteLocation(string $slug)
    {
        $location = Location::where('slug', $slug)->firstOrFail();
        $location->delete();

        return response()->json(['success' => true, 'message' => 'Location deleted']);
    }

    /**
     * GET /api/v1/content-nodes - List content nodes
     */
    public function contentNodes(Request $request)
    {
        $query = ContentNode::with(['taxonomy', 'location', 'postTemplate']);

        if ($taxonomySlug = $request->get('taxonomy_slug')) {
            $query->whereHas('taxonomy', fn($q) => $q->where('slug', $taxonomySlug));
        }

        if ($locationSlug = $request->get('location_slug')) {
            $query->whereHas('location', fn($q) => $q->where('slug', $locationSlug));
        }

        if ($isRestricted = $request->get('is_restricted_content')) {
            $query->where('is_restricted_content', $isRestricted === 'true');
        }

        $contentNodes = $query->orderBy('publish_date', 'desc')->paginate(50);

        return response()->json($contentNodes);
    }

    /**
     * GET /api/v1/content-nodes/{slug} - Get content node
     */
    public function showContentNode(string $slug)
    {
        $content = ContentNode::with(['taxonomy', 'location', 'postTemplate'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($content);
    }

    /**
     * PUT /api/v1/content-nodes/{slug} - Update content node
     */
    public function updateContentNode(Request $request, string $slug)
    {
        $content = ContentNode::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'seo_title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/',
            'body_content' => 'sometimes|string',
            'meta_description' => 'sometimes|string|nullable',
            'is_restricted_content' => 'sometimes|boolean',
        ]);

        $content->update($data);

        return response()->json(['success' => true, 'data' => $content]);
    }

    /**
     * DELETE /api/v1/content-nodes/{slug} - Delete content node
     */
    public function deleteContentNode(string $slug)
    {
        $content = ContentNode::where('slug', $slug)->firstOrFail();
        $content->delete();

        return response()->json(['success' => true, 'message' => 'Content node deleted']);
    }

    /**
     * GET /api/v1/keywords - List keywords
     */
    public function keywords(Request $request)
    {
        $query = Keyword::with(['category', 'location']);

        if ($language = $request->get('language')) {
            $query->where('language', $language);
        }

        if ($isTrending = $request->get('is_trending')) {
            $query->where('is_trending', $isTrending === 'true');
        }

        if ($search = $request->get('q')) {
            $query->where('keyword', 'like', "%{$search}%");
        }

        $keywords = $query->orderBy('search_volume', 'desc')->paginate(50);

        return response()->json($keywords);
    }

    /**
     * POST /api/v1/keywords - Create keyword
     */
    public function createKeyword(Request $request)
    {
        $data = $request->validate([
            'keyword' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/|unique:keywords,slug',
            'language' => 'sometimes|string|max:10',
            'category_id' => 'sometimes|exists:taxonomies,id',
            'location_id' => 'sometimes|exists:locations,id',
            'search_volume' => 'sometimes|integer|min:0',
            'difficulty' => 'sometimes|integer|min:0|max:100',
        ]);

        $keyword = Keyword::create([
            'keyword' => $data['keyword'],
            'slug' => $data['slug'] ?? Str::slug($data['keyword']),
            'language' => $data['language'] ?? 'tr',
            'category_id' => $data['category_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'search_volume' => $data['search_volume'] ?? 0,
            'difficulty' => $data['difficulty'] ?? 50,
        ]);

        return response()->json(['success' => true, 'data' => $keyword], 201);
    }

    /**
     * GET /api/v1/keywords/{id} - Get keyword
     */
    public function showKeyword(int $id)
    {
        $keyword = Keyword::with(['category', 'location', 'contentNodes'])
            ->findOrFail($id);

        return response()->json($keyword);
    }

    /**
     * PUT /api/v1/keywords/{id} - Update keyword
     */
    public function updateKeyword(Request $request, int $id)
    {
        $keyword = Keyword::findOrFail($id);

        $data = $request->validate([
            'keyword' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/|unique:keywords,slug,' . $id,
            'language' => 'sometimes|string|max:10',
            'category_id' => 'sometimes|exists:taxonomies,id|nullable',
            'location_id' => 'sometimes|exists:locations,id|nullable',
            'search_volume' => 'sometimes|integer|min:0',
            'difficulty' => 'sometimes|integer|min:0|max:100',
            'is_trending' => 'sometimes|boolean',
            'is_auto_generated' => 'sometimes|boolean',
        ]);

        $keyword->update($data);

        return response()->json(['success' => true, 'data' => $keyword]);
    }

    /**
     * DELETE /api/v1/keywords/{id} - Delete keyword
     */
    public function deleteKeyword(int $id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->delete();

        return response()->json(['success' => true, 'message' => 'Keyword deleted']);
    }

    /**
     * GET /api/v1/live-data - List live data
     */
    public function liveData()
    {
        $data = LiveDataVault::orderBy('key')->get();
        return response()->json($data);
    }

    /**
     * POST /api/v1/live-data - Create/update live data
     */
    public function upsertLiveData(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'value' => 'required|string',
            'display_name' => 'sometimes|string|max:255',
        ]);

        $vault = LiveDataVault::updateOrCreate(
            ['key' => $data['key']],
            ['value' => $data['value'], 'display_name' => $data['display_name'] ?? null]
        );

        return response()->json(['success' => true, 'data' => $vault]);
    }

    /**
     * GET /api/v1/post-templates - List post templates
     */
    public function postTemplates()
    {
        $templates = PostTemplate::with('taxonomy')->orderBy('name')->get();
        return response()->json($templates);
    }

    /**
     * GET /api/v1/stats - Get system statistics
     */
    public function stats()
    {
        return response()->json([
            'taxonomies' => Taxonomy::count(),
            'locations' => Location::count(),
            'content_nodes' => ContentNode::count(),
            'keywords' => Keyword::count(),
            'live_data' => LiveDataVault::count(),
            'post_templates' => PostTemplate::count(),
            'published_content' => ContentNode::whereNotNull('publish_date')->count(),
            'restricted_content' => ContentNode::where('is_restricted_content', true)->count(),
        ]);
    }

    /**
     * POST /api/v1/export - Export data in CSV/JSON format
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'content_nodes');
        $format = $request->get('format', 'json');

        $data = match ($type) {
            'taxonomies' => Taxonomy::with(['parent', 'children'])->get(),
            'locations' => Location::with(['parent', 'children'])->get(),
            'content_nodes' => ContentNode::with(['taxonomy', 'location'])->get(),
            'keywords' => Keyword::with(['category', 'location'])->get(),
            'live_data' => LiveDataVault::all(),
            default => null,
        };

        if (!$data) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        if ($format === 'csv') {
            if ($data->isEmpty()) {
                return response('No data', 200, ['Content-Type' => 'text/csv']);
            }
            
            $headers = array_keys($data->first()->toArray());
            $csv = implode(',', $headers) . "\n";
            
            foreach ($data as $row) {
                $values = array_map(function ($v) {
                    return '"' . str_replace('"', '""', (string) $v) . '"';
                }, array_values($row->toArray()));
                $csv .= implode(',', $values) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$type}_export.csv",
            ]);
        }

        return response()->json([
            'type' => $type,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}