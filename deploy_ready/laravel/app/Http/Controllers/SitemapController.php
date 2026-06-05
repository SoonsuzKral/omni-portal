<?php

namespace App\Http\Controllers;

use App\Services\SitemapGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapGenerator $sitemapGenerator
    ) {}

    public function index()
    {
        $xml = $this->sitemapGenerator->generateIndex();
        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function contentShard($page = 1)
    {
        $page = max(1, (int) $page);
        $xml = $this->sitemapGenerator->generateContentShard($page);

        if (!$xml) {
            return Response::make('Sitemap not found', 404);
        }

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function categories()
    {
        $xml = $this->sitemapGenerator->generateCategories();
        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function locations()
    {
        $xml = $this->sitemapGenerator->generateLocations();
        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
