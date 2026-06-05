<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display all locations.
     */
    public function index()
    {
        $cities = Location::whereNull('parent_id')
            ->withCount('contentNodes')
            ->orderBy('content_nodes_count', 'desc')
            ->paginate(20);

        return view('location.all', compact('cities'));
    }

    public function tree()
    {
        $rootLocations = Location::whereNull('parent_id')
            ->with(['children', 'contentNodes'])
            ->get();

        return view('location.tree', compact('rootLocations'));
    }

    public function show(string $slug)
    {
        $location = Location::where('slug', $slug)->firstOrFail();

        $contentNodes = $location->contentNodes()
            ->whereNotNull('publish_date')
            ->with('taxonomy')
            ->orderBy('publish_date', 'desc')
            ->paginate(20);

        $children = $location->children()->withCount('contentNodes')->get();

        return view('location.show', compact('location', 'contentNodes', 'children'));
    }

    /**
     * Display content from a specific district within a city.
     */
    public function district(string $citySlug, string $districtSlug)
    {
        $city = Location::where('slug', $citySlug)->firstOrFail();
        $district = Location::where('slug', $districtSlug)
            ->where('parent_id', $city->id)
            ->firstOrFail();

        $contentNodes = $district->contentNodes()
            ->whereNotNull('publish_date')
            ->with('taxonomy')
            ->orderBy('publish_date', 'desc')
            ->paginate(20);

        return view('location.district', compact('city', 'district', 'contentNodes'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $locations = Location::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%");
            })
            ->paginate(20);

        return response()->json($locations);
    }

    /**
     * API: Get location tree.
     */
    public function apiTree()
    {
        $locations = Location::whereNull('parent_id')
            ->with('children.children')
            ->get();

        return response()->json($locations);
    }
}
