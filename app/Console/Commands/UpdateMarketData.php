<?php

namespace App\Console\Commands;

use App\Services\ExternalDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateMarketData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'market:update
                            {--force : Force update even if cache is fresh}
                            {--dry-run : Preview what would be updated without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Update market data (USD, Gold, Weather) from external APIs and LiveDataVault';

    /**
     * Execute the console command.
     */
    public function handle(ExternalDataService $externalDataService): int
    {
        $this->info('🔄 Starting market data update...');

        $startTime = microtime(true);
        $updated = [];

        // Get all live data vault keys
        $vaultKeys = $externalDataService->getAvailablePlaceholders();
        $allKeys = array_merge(
            $vaultKeys['internal'] ?? [],
            ['usd', 'usd_try', 'eur', 'eur_try', 'gold']
        );

        foreach ($allKeys as $key) {
            if ($this->option('dry-run')) {
                $this->line("  Would refresh: {$key}");
                continue;
            }

            // Skip if not force and cache is fresh
            if (!$this->option('force')) {
                $cacheKey = "placeholder:{$key}:global";
                if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    continue;
                }
            }

            try {
                // Force resolution to refresh cache
                $value = $externalDataService->resolvePlaceholder($key);
                if ($value !== null) {
                    $updated[$key] = $value;
                    $this->line("  ✓ Updated: {$key} = {$value}");
                }
            } catch (\Exception $e) {
                $this->warn("  ✗ Failed: {$key} - " . $e->getMessage());
                Log::warning("Market data update failed for key: {$key}", ['error' => $e->getMessage()]);
            }
        }

        // Clear entire cache if force flag is set
        if ($this->option('force') && !$this->option('dry-run')) {
            $externalDataService->clearCache();
            $this->info('🧹 Cache cleared (force mode)');
        }

        $duration = round(microtime(true) - $startTime, 2);

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info("📊 Dry run complete. Would update " . count($allKeys) . " keys.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("✅ Market data update complete!");
        $this->info("   Updated: " . count($updated) . " values");
        $this->info("   Duration: {$duration}s");

        Log::info('Market data update completed', [
            'updated_count' => count($updated),
            'duration_seconds' => $duration,
        ]);

        return self::SUCCESS;
    }
}