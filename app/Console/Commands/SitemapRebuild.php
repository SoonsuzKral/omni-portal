<?php

namespace App\Console\Commands;

use App\Jobs\SitemapRefreshJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SitemapRebuild extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sitemap:rebuild
                            {--now : Run immediately instead of dispatching job}';

    /**
     * The console command description.
     */
    protected $description = 'Rebuild and cache sitemap data for SEO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🗺️ Starting sitemap rebuild...');

        if ($this->option('now')) {
            // Run immediately
            $job = new class extends SitemapRefreshJob {
                public function __construct() {}
                public function handle(): void {
                    $this->prewarmSitemapCache();
                }
            };
            (new class extends SitemapRefreshJob {
                public function handle(): void {
                    $this->prewarmSitemapCache();
                }
            })();

            $this->info('✅ Sitemap rebuilt and cached immediately.');
        } else {
            // Dispatch to queue
            SitemapRefreshJob::dispatch();
            $this->info('✅ Sitemap refresh job dispatched to queue.');
            $this->info('   Run with --now for immediate rebuild.');
        }

        Log::info('Sitemap rebuild command executed', [
            'mode' => $this->option('now') ? 'immediate' : 'queued',
        ]);

        return self::SUCCESS;
    }
}