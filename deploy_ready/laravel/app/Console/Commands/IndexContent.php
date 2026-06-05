<?php

namespace App\Console\Commands;

use App\Models\ContentNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexContent extends Command
{
    protected $signature = 'content:index 
                            {--limit=100 : Number of recent content to index}
                            {--dry-run : Show URLs without actually submitting}';

    protected $description = 'Submit new content to Google Indexing API';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("🔍 Finding {$limit} most recent published content...");

        $contents = ContentNode::whereNotNull('publish_date')
            ->where('is_indexed', '!=', true)
            ->orWhereNull('is_indexed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($contents->isEmpty()) {
            $this->info('✅ No new content to index.');
            return self::SUCCESS;
        }

        $this->info("Found " . $contents->count() . " content items to index.");

        $indexed = 0;
        $failed = 0;

        foreach ($contents as $content) {
            $taxonomySlug = $content->taxonomy?->slug ?? '';
            $locationSlug = $content->location?->slug ?? '';
            $url = url('/' . $taxonomySlug . '/' . $locationSlug . '/' . $content->slug);

            if ($this->option('dry-run')) {
                $this->line("  📄 {$url}");
                continue;
            }

            $result = $this->submitToIndexing($url);

            if ($result) {
                $content->update(['is_indexed' => true, 'indexed_at' => now()]);
                $indexed++;
                $this->line("  ✅ {$url}");
            } else {
                $failed++;
                $this->line("  ❌ {$url}");
            }
        }

        $this->info("\n📊 Indexing Summary:");
        $this->info("   ✅ Indexed: {$indexed}");
        $this->info("   ❌ Failed: {$failed}");

        Log::info('Content indexing completed', [
            'indexed' => $indexed,
            'failed' => $failed,
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function submitToIndexing(string $url): bool
    {
        $credentials = config('services.google_indexing');

        if (!$credentials || !isset($credentials['key_file'])) {
            $this->warn("\n⚠️  Google Indexing API not configured.");
            $this->warn("   Set GOOGLE_INDEXING_KEY_FILE in .env");
            $this->warn("   Requires: Google Cloud project with Indexing API enabled");
            return false;
        }

        try {
            $accessToken = $this->getAccessToken($credentials);
            
            $response = Http::withToken($accessToken)
                ->post('https://indexing.googleapis.com/v3/urlNotifications:publish', [
                    'url' => $url,
                    'type' => 'URL_UPDATED',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Indexing API error: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken(array $credentials): string
    {
        $keyFile = storage_path('app/' . $credentials['key_file']);
        
        if (!file_exists($keyFile)) {
            throw new \Exception('Service account key file not found: ' . $keyFile);
        }

        $credentialsData = json_decode(file_get_contents($keyFile), true);
        
        $jwt = $this->createJwt($credentialsData);
        
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->json('access_token');
    }

    private function createJwt(array $credentials): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'RS256']));
        
        $payload = base64_encode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => time(),
            'exp' => time() + 3600,
        ]));

        $signature = '';
        
        return $header . '.' . $payload . '.' . $signature;
    }
}