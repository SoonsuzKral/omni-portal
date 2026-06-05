<?php

namespace App\Http\Controllers;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display search results with fuzzy matching.
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $location = $request->get('location', '');
        $suggestedQuery = null;

        if (empty($query) && empty($location)) {
            return redirect('/');
        }

        $contentQuery = ContentNode::whereNotNull('publish_date')
            ->with(['taxonomy', 'location']);

        if (!empty($query)) {
            $contentQuery->where(function ($q) use ($query) {
                $q->where('seo_title', 'like', "%{$query}%")
                  ->orWhere('body_content', 'like', "%{$query}%")
                  ->orWhere('meta_description', 'like', "%{$query}%");
            });
        }

        if (!empty($location)) {
            $locationModel = Location::where('name', 'like', "%{$location}%")
                ->orWhere('slug', 'like', "%{$location}%")
                ->first();
            
            if ($locationModel) {
                $contentQuery->where('location_id', $locationModel->id);
            }
        }

        $contentNodes = $contentQuery->orderBy('page_views', 'desc')->paginate(20);

        if ($contentNodes->isEmpty() && !empty($query)) {
            $similarQuery = $this->findSimilarContent($query);
            if ($similarQuery) {
                $suggestedQuery = $similarQuery;
                $contentNodes = ContentNode::whereNotNull('publish_date')
                    ->where('seo_title', 'like', "%{$similarQuery}%")
                    ->with(['taxonomy', 'location'])
                    ->orderBy('page_views', 'desc')
                    ->paginate(20);
            }
        }

        $taxonomies = collect();
        if (!empty($query)) {
        $taxonomies = Taxonomy::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%");
            })
            ->paginate(20);
        }

        $locations = collect();
        if (!empty($query)) {
            $locations = Location::where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('slug', 'like', "%{$query}%");
                })
                ->paginate(20);
        }

        return view('search.results', compact('query', 'location', 'contentNodes', 'taxonomies', 'locations', 'suggestedQuery'));
    }

    protected function findSimilarContent(string $query): ?string
    {
        $words = explode(' ', strtolower(trim($query)));
        $meaningfulWords = array_filter($words, fn($w) => strlen($w) > 3);

        if (empty($meaningfulWords)) {
            return null;
        }

        $taxonomyMatch = Taxonomy::where(function ($q) use ($meaningfulWords) {
            foreach ($meaningfulWords as $word) {
                $q->orWhere('name', 'like', "%{$word}%");
            }
        })->first();

        if ($taxonomyMatch) {
            return $taxonomyMatch->name;
        }

        $contentMatch = ContentNode::whereNotNull('publish_date')
            ->where(function ($q) use ($meaningfulWords) {
                foreach ($meaningfulWords as $word) {
                    $q->orWhere('seo_title', 'like', "%{$word}%");
                }
            })
            ->orderBy('page_views', 'desc')
            ->first();

        if ($contentMatch) {
            $words = explode(' ', $contentMatch->seo_title);
            return $words[0] ?? null;
        }

        return null;
    }

    /**
     * API: Quick search for autocomplete.
     */
    public function api(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $contentNodes = ContentNode::where('seo_title', 'like', "%{$query}%")
            ->with(['taxonomy:id,slug,name', 'location:id,slug,name'])
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'type' => 'content',
                'label' => $c->seo_title,
                'url' => url('/' . ($c->taxonomy?->slug ?? 'x') . '/' . ($c->location?->slug ?? 'x') . '/' . $c->slug),
                'subtitle' => ($c->taxonomy?->name ?? '') . ($c->location?->name ? ' - ' . $c->location->name : ''),
            ]);

        $taxonomies = Taxonomy::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->map(fn($t) => [
                'type' => 'category',
                'label' => $t->name,
                'url' => url('/' . $t->slug),
                'subtitle' => 'Category',
            ]);

        $locations = Location::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->map(fn($l) => [
                'type' => 'location',
                'label' => $l->name,
                'url' => url('/location/' . $l->slug),
                'subtitle' => 'Location',
            ]);

        $all = collect()->concat($contentNodes)->concat($taxonomies)->concat($locations)->take(10);

        return response()->json($all);
    }
}
