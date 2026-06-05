<?php

namespace App\Http\Middleware;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrafficLeakPrevention
{
    const CACHE_TTL = 1800;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->getStatusCode() === 404) {
            $this->handle404($request);
        }

        return $response;
    }

    protected function handle404(Request $request): void
    {
        $path = $request->getPathInfo();

        Log::warning('404 intercepted - Traffic Leak Prevention', [
            'path' => $path,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $redirectUrl = $this->findBestRedirect($path);

        if ($redirectUrl) {
            Log::info('Redirecting to trending content', ['from' => $path, 'to' => $redirectUrl]);

            session()->put('404_redirect_from', $path);
            session()->put('404_redirect_reason', 'page_not_found');
        }
    }

    protected function findBestRedirect(string $path): ?string
    {
        $trendingContent = $this->getTrendingContent();

        if (empty($trendingContent)) {
            return $this->getFallbackRedirect();
        }

        $pathParts = array_filter(explode('/', trim($path, '/')));
        $pathKeywords = array_slice($pathParts, -2);

        foreach ($trendingContent as $content) {
            if ($this->isRelevantRedirect($content, $pathKeywords)) {
                return $content['url'];
            }
        }

        return $trendingContent[0]['url'] ?? $this->getFallbackRedirect();
    }

    protected function getTrendingContent(): array
    {
        $cacheKey = 'trending_redirect_content';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return ContentNode::whereNotNull('publish_date')
                ->where('page_views', '>', 0)
                ->with(['taxonomy', 'location'])
                ->orderBy('page_views', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($node) {
                    $taxonomySlug = $node->taxonomy?->slug ?? '';
                    $locationSlug = $node->location?->slug ?? '';
                    return [
                        'url' => "/{$taxonomySlug}/{$locationSlug}/{$node->slug}",
                        'title' => $node->seo_title,
                        'taxonomy' => $node->taxonomy?->name,
                        'views' => $node->page_views,
                    ];
                })
                ->toArray();
        });
    }

    protected function isRelevantRedirect(array $content, array $pathKeywords): bool
    {
        $contentTitle = strtolower($content['title'] ?? '');
        $contentTaxonomy = strtolower($content['taxonomy'] ?? '');

        foreach ($pathKeywords as $keyword) {
            if (str_contains($contentTitle, $keyword) || str_contains($contentTaxonomy, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function getFallbackRedirect(): ?string
    {
        $popularTaxonomy = Taxonomy::whereNotNull('parent_id')
            ->withCount('contentNodes')
            ->orderByDesc('content_nodes_count')
            ->first();

        if ($popularTaxonomy) {
            return "/{$popularTaxonomy->slug}";
        }

        return url('/');
    }
}