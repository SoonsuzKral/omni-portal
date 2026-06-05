<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class GoogleSearchConsoleService
{
    const CACHE_KEY_TOKEN = 'gsc_access_token';

    protected array $config;
    protected ?string $accessToken = null;
    protected int $requestCount = 0;
    protected int $rateLimitResetAt = 0;

    public function __construct()
    {
        $this->config = config('search-telemetry.gsc');
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function authenticate(): ?string
    {
        $cached = Cache::get(self::CACHE_KEY_TOKEN);
        if ($cached) {
            $this->accessToken = $cached;
            return $cached;
        }

        $token = match ($this->config['auth_type']) {
            'service_account' => $this->authenticateServiceAccount(),
            'oauth' => $this->authenticateOAuth(),
            default => null,
        };

        if ($token) {
            $this->accessToken = $token;
            Cache::put(self::CACHE_KEY_TOKEN, $token, 3300);
        }

        return $token;
    }

    protected function authenticateServiceAccount(): ?string
    {
        $path = $this->config['service_account_path'];
        if (!$path || !file_exists($path)) {
            Log::warning('GSC service account file not found', ['path' => $path]);
            return null;
        }

        try {
            $json = json_decode(file_get_contents($path), true);
            if (!$json || !isset($json['client_email'])) {
                return null;
            }

            $now = time();
            $header = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            $claims = $this->base64UrlEncode(json_encode([
                'iss' => $json['client_email'],
                'scope' => implode(' ', $this->config['scopes']),
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]));

            $signature = '';
            $pkey = openssl_pkey_get_private($json['private_key']);
            if (!$pkey) {
                return null;
            }
            openssl_sign("{$header}.{$claims}", $signature, $pkey, 'sha256WithRSAEncryption');
            $signature = $this->base64UrlEncode($signature);

            $jwt = "{$header}.{$claims}.{$signature}";

            $response = Http::timeout(30)->asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('GSC service account auth failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('access_token');
        } catch (\Exception $e) {
            Log::error('GSC service account exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function authenticateOAuth(): ?string
    {
        try {
            $response = Http::timeout(30)->asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'refresh_token' => $this->config['refresh_token'],
                'grant_type' => 'refresh_token',
            ]);

            if (!$response->successful()) {
                Log::error('GSC OAuth refresh failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $response->json('access_token');
        } catch (\Exception $e) {
            Log::error('GSC OAuth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function fetchSearchAnalytics(string $startDate, string $endDate, array $options = []): array
    {
        return $this->apiRequest('https://searchconsole.googleapis.com/v1/urlInspection/index:inspect', [
            'siteUrl' => $this->config['site_url'],
            'inspectionUrl' => $options['url'] ?? $this->config['site_url'],
        ]);
    }

    public function fetchQueryAnalytics(string $startDate, string $endDate, int $rowLimit = 5000): array
    {
        $allRows = [];
        $startRow = 0;

        do {
            $body = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'dimensions' => ['query', 'page', 'device', 'country'],
                'rowLimit' => min($rowLimit, 25000),
                'startRow' => $startRow,
                'aggregationType' => 'auto',
            ];

            $data = $this->apiRequest(
                "https://searchconsole.googleapis.com/v1/sites/{$this->encodeSiteUrl()}/searchAnalytics/query",
                $body,
                'POST'
            );

            $rows = $data['rows'] ?? [];
            $allRows = array_merge($allRows, $rows);
            $startRow += count($rows);

        } while (!empty($rows) && $startRow < $rowLimit);

        return $allRows;
    }

    public function inspectUrl(string $url): ?array
    {
        $body = [
            'inspectionUrl' => $url,
            'siteUrl' => $this->config['site_url'],
        ];

        return $this->apiRequest(
            'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect',
            $body,
            'POST'
        );
    }

    public function batchInspectUrls(array $urls): \Generator
    {
        foreach (array_chunk($urls, 100) as $chunk) {
            $results = [];
            foreach ($chunk as $url) {
                $result = $this->inspectUrl($url);
                if ($result) {
                    $results[$url] = $result;
                }
                yield $url => $result;
            }
            Cache::put('gsc_batch_inspect:chunk', $results, 300);
        }
    }

    public function fetchSitemapList(): array
    {
        $data = $this->apiRequest(
            "https://searchconsole.googleapis.com/v1/sites/{$this->encodeSiteUrl()}/sitemaps"
        );

        return $data['sitemap'] ?? [];
    }

    public function fetchCrawlStats(): ?array
    {
        $data = $this->apiRequest(
            "https://searchconsole.googleapis.com/v1/sites/{$this->encodeSiteUrl()}/crawlStats"
        );

        if (isset($data['error'])) {
            return null;
        }

        return [
            'crawl_requests' => $data['crawlStats']['totalCrawlRequests'] ?? 0,
            'crawl_errors' => $data['crawlStats']['crawlErrors'] ?? 0,
        ];
    }

    public function verifySiteOwnership(): bool
    {
        $data = $this->apiRequest(
            "https://searchconsole.googleapis.com/v1/sites/{$this->encodeSiteUrl()}"
        );

        return isset($data['permissionLevel']);
    }

    public function resetRateLimit(): void
    {
        $this->requestCount = 0;
        $this->rateLimitResetAt = time() + 60;
    }

    protected function apiRequest(string $url, ?array $body = null, string $method = 'GET'): ?array
    {
        $token = $this->accessToken ?? $this->authenticate();
        if (!$token) {
            Log::error('GSC: no access token available');
            return null;
        }

        $this->checkRateLimit();

        try {
            $http = Http::timeout(config('search-telemetry.sync.timeout_seconds', 120))
                ->withToken($token)
                ->withHeaders(['Accept' => 'application/json']);

            $response = $body
                ? $http->{$method === 'POST' ? 'post' : 'get'}($url, $body)
                : $http->get($url);

            $this->requestCount++;

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 401) {
                Cache::forget(self::CACHE_KEY_TOKEN);
                $this->accessToken = null;
                $token = $this->authenticate();
                if ($token) {
                    return $this->apiRequest($url, $body, $method);
                }
            }

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 60);
                Log::warning('GSC rate limited', ['retry_after' => $retryAfter]);
                sleep(min($retryAfter, 120));
                $this->resetRateLimit();
                return $this->apiRequest($url, $body, $method);
            }

            Log::warning('GSC API error', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('GSC API exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function checkRateLimit(): void
    {
        $limit = config('search-telemetry.sync.rate_limit_per_minute', 60);
        if ($this->requestCount >= $limit) {
            $sleep = max(1, $this->rateLimitResetAt - time());
            Log::info("GSC rate limit reached, sleeping {$sleep}s");
            sleep($sleep);
            $this->resetRateLimit();
        }
    }

    protected function encodeSiteUrl(): string
    {
        return urlencode($this->config['site_url']);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
