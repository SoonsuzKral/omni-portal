<?php

namespace App\Console\Commands;

use App\Services\PingService;
use Illuminate\Console\Command;

class SitemapPing extends Command
{
    protected $signature = 'sitemap:ping';
    protected $description = 'Ping Google and Bing about sitemap updates';

    public function handle(PingService $pingService): int
    {
        $this->info('Pinging search engines...');
        $results = $pingService->pingSearchEngines();
        foreach ($results as $engine => $success) {
            $this->info(ucfirst($engine) . ': ' . ($success ? 'OK' : 'FAILED'));
        }
        return self::SUCCESS;
    }
}
