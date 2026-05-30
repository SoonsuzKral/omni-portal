<?php

namespace App\Http\Controllers;

use App\Models\Taxonomy;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    public function index()
    {
        $taxonomies = Taxonomy::withCount('contentNodes')
            ->orderBy('content_nodes_count', 'desc')
            ->paginate(20);

        return view('taxonomy.all', compact('taxonomies'));
    }

    public function tree()
    {
        $rootTaxonomies = Taxonomy::whereNull('parent_id')
            ->with(['children', 'contentNodes'])
            ->get();

        return view('taxonomy.tree', compact('rootTaxonomies'));
    }

    public function show(string $slug)
    {
        $taxonomy = Taxonomy::where('slug', $slug)
            ->firstOrFail();

        $contentNodes = $taxonomy->contentNodes()
            ->whereNotNull('publish_date')
            ->orderBy('publish_date', 'desc')
            ->paginate(20);

        $taxonomy->loadCount('children');

        return view('taxonomy.show', compact('taxonomy', 'contentNodes'));
    }

    public function apiTree()
    {
        $taxonomies = Taxonomy::whereNull('parent_id')
            ->with('children.children')
            ->get();

        return response()->json($taxonomies);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $taxonomies = Taxonomy::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%");
            })
            ->paginate(20);

        return response()->json($taxonomies);
    }
}
