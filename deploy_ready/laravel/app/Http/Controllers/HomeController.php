<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $taxonomies = Cache::remember('home_taxonomies_all', 3600, function () {
            return Taxonomy::withCount('contentNodes')
                ->having('content_nodes_count', '>', 0)
                ->orderBy('content_nodes_count', 'desc')
                ->limit(12)
                ->get();
        });

        $locations = Cache::remember('home_locations_all', 3600, function () {
            return Location::whereNull('parent_id')
                ->withCount('contentNodes')
                ->having('content_nodes_count', '>', 0)
                ->orderBy('content_nodes_count', 'desc')
                ->limit(8)
                ->get();
        });

        $recentContent = Cache::remember('home_recent_content_all', 600, function () {
            return ContentNode::whereNotNull('publish_date')
                ->with(['taxonomy', 'location'])
                ->orderBy('publish_date', 'desc')
                ->limit(8)
                ->get();
        });

        $popularContent = Cache::remember('home_popular_content_all', 1800, function () {
            return ContentNode::whereNotNull('publish_date')
                ->with(['taxonomy', 'location'])
                ->orderBy('page_views', 'desc')
                ->limit(5)
                ->get();
        });

        $trendingTopics = Cache::remember('home_trending_topics_all', 600, function () {
            return Keyword::trending()
                ->with(['category', 'location'])
                ->limit(12)
                ->get();
        });

        $trendingContent = Cache::remember('home_trending_content_all', 600, function () {
            return ContentNode::whereNotNull('publish_date')
                ->whereHas('taxonomy', function ($q) {
                    $q->whereIn('id', Keyword::trending()->pluck('category_id'));
                })
                ->with(['taxonomy', 'location'])
                ->orderBy('page_views', 'desc')
                ->limit(8)
                ->get();
        });

        $stats = [
            'total_content' => ContentNode::count(),
            'total_taxonomies' => Taxonomy::count(),
            'total_locations' => Location::count(),
        ];

        return view('home', compact(
            'taxonomies',
            'locations',
            'recentContent',
            'popularContent',
            'trendingTopics',
            'trendingContent',
            'stats'
        ));
    }
}
