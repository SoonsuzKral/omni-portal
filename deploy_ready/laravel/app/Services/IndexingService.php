<?php

namespace App\Services;

use App\Models\ContentNode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;

class IndexingService
{
    private const GOOGLE_INDEXING_API = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    private const BING_INDEX_API = 'https://www.bingindex.com/api/v1/indexing/ping';
    private const CACHE_PREFIX = 'indexing:';
    private const CACHE_TTL = 86400;
    private const BATCH_SIZE = 100;
    private const RATE_LIMIT_PER_SECOND = 100;

    protected ?string $googleServiceAccountJson = null;
    protected ?string $googleAccessToken = null;
    protected ?string $googleAccessTokenExpiry = null;
    protected static int $requestsThisSecond = 0;
    protected static ?int $lastRequestTimestamp = null;

    public function __construct()
    {
        $this->googleServiceAccountJson = config('services.google.indexing_service_account');
    }

    public function indexContentInstant(ContentNode $content): array
    {
        return $this->indexContent($content);
    }

    public function queueForIndexing(array $contentIds): void
    {
        $chunks = array_chunk($contentIds, self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            \App\Jobs\IndexContentBatch::dispatch($chunk);
        }

        Log::info("Queued {$contentIds} URLs for indexing", ['total' => count($contentIds)]);
    }

    public function processBatchInstant(array $contentIds): array
    {
        $results = [
            'total' => count($contentIds),
            'successful' => 0,
            'failed' => 0,
            'urls' => [],
        ];

        $startTime = microtime(true);

        foreach ($contentIds as $id) {
            $this->throttle();

            $content = ContentNode::find($id);
            if (!$content) {
                $results['failed']++;
                continue;
            }

            $result = $this->indexContent($content);

            if ($result['google']['success'] || $result['bing']['success']) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }

            $results['urls'][] = [
                'id' => $id,
                'result' => $result,
            ];
        }

        $elapsed = microtime(true) - $startTime;
        $results['elapsed_seconds'] = round($elapsed, 3);
        $results['urls_per_second'] = count($contentIds) / max($elapsed, 0.001);

        Log::info("Batch indexing completed", $results);

        return $results;
    }

    protected function throttle(): void
    {
        $now = microtime(true);

        if (self::$lastRequestTimestamp !== null && ($now - self::$lastRequestTimestamp) >= 1.0) {
            self::$requestsThisSecond = 0;
            self::$lastRequestTimestamp = null;
        }

        if (self::$requestsThisSecond >= self::RATE_LIMIT_PER_SECOND) {
            usleep(10000);
            return;
        }

        self::$requestsThisSecond++;
        self::$lastRequestTimestamp = $now;
    }

    public function getQueueStats(): array
    {
        $pending = DB::table('jobs')
            ->where('payload', 'like', '%IndexContentBatch%')
            ->count();

        $recentIndexing = DB::table('content_nodes')
            ->whereNotNull('publish_date')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return [
            'pending_jobs' => $pending,
            'last_hour_content' => $recentIndexing,
            'estimated_throughput' => self::RATE_LIMIT_PER_SECOND,
        ];
    }

    public function indexContent(ContentNode $content): array
    {
        $results = [
            'google' => ['success' => false, 'message' => ''],
            'bing' => ['success' => false, 'message' => ''],
        ];

        $url = $this->buildContentUrl($content);

        if (!$url) {
            return ['error' => 'Could not generate URL for content'];
        }

        $cacheKey = self::CACHE_PREFIX . md5($url);
        if (Cache::has($cacheKey)) {
            Log::info("Content already indexed recently", ['url' => $url]);
            return ['cached' => true, 'url' => $url];
        }

        $results['google'] = $this->sendToGoogle($url);
        $results['bing'] = $this->sendToBing($url);

        if ($results['google']['success'] || $results['bing']['success']) {
            Cache::put($cacheKey, true, self::CACHE_TTL);
        }

        Log::info('Indexing completed', [
            'content_id' => $content->id,
            'url' => $url,
            'results' => $results
        ]);

        return $results;
    }

    public function removeFromIndex(ContentNode $content): array
    {
        $url = $this->buildContentUrl($content);

        if (!$url) {
            return ['error' => 'Could not generate URL for content'];
        }

        $result = $this->sendToGoogle($url, 'URL_DELETED');

        if ($result['success']) {
            Cache::forget(self::CACHE_PREFIX . md5($url));
        }

        return $result;
    }

    protected function buildContentUrl(ContentNode $content): ?string
    {
        $location = $content->location;
        $taxonomy = $content->taxonomy;

        if ($taxonomy && $location) {
            return url("/{$taxonomy->slug}/{$location->slug}/{$content->slug}");
        } elseif ($location) {
            return url("/location/{$location->slug}/{$content->slug}");
        } elseif ($taxonomy) {
            return url("/{$taxonomy->slug}/{$content->slug}");
        }

        return null;
    }

    protected function sendToGoogle(string $url, string $type = 'URL_UPDATED'): array
    {
        $credentials = config('services.google.indexing_credentials');

        if (empty($credentials) && empty($this->googleServiceAccountJson)) {
            return [
                'success' => false,
                'message' => 'Google Indexing API credentials not configured'
            ];
        }

        try {
            $token = $this->getGoogleAccessToken();

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain Google access token'
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post(self::GOOGLE_INDEXING_API, [
                'url' => $url,
                'type' => $type,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Successfully sent to Google Indexing API'
                ];
            }

            return [
                'success' => false,
                'message' => 'Google API error: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Google Indexing error', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function sendToBing(string $url): array
    {
        $bingApiKey = config('services.bing.indexing_api_key');

        if (empty($bingApiKey)) {
            return [
                'success' => false,
                'message' => 'Bing Indexing API key not configured'
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $bingApiKey,
                'Content-Type' => 'application/json',
            ])->post(self::BING_INDEX_API, [
                'url' => $url
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Successfully sent to Bing Indexing API'
                ];
            }

            return [
                'success' => false,
                'message' => 'Bing API error: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Bing Indexing error', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function getGoogleAccessToken(): ?string
    {
        $cacheKey = 'google_indexing_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $credentials = config('services.google.indexing_credentials');

        if (empty($credentials)) {
            return null;
        }

        try {
            $jwt = $this->createJwt($credentials);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $expiresIn = $data['expires_in'] ?? 3600;
                Cache::put($cacheKey, $data['access_token'], $expiresIn - 60);
                return $data['access_token'];
            }

        } catch (\Exception $e) {
            Log::error('Failed to get Google access token', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function createJwt(array $credentials): string
    {
        $header = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));

        $now = time();
        $payload = base64_encode(json_encode([
            'iss' => $credentials['client_email'] ?? '',
            'sub' => $credentials['client_email'] ?? '',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ]));

        $privateKey = $credentials['private_key'] ?? '';

        openssl_sign(
            $header . '.' . $payload,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        return $header . '.' . $payload . '.' . base64_encode($signature);
    }

    public function batchIndex(array $contentIds): array
    {
        $results = [];

        foreach ($contentIds as $id) {
            $content = ContentNode::find($id);
            if ($content) {
                $results[$id] = $this->indexContent($content);
            }
        }

        return $results;
    }
}