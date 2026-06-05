<?php

namespace App\Console\Commands;

use App\Jobs\DetectAnomaliesJob;
use App\Services\AnomalyDetectionEngine;
use Illuminate\Console\Command;

class DetectAnomaliesCommand extends Command
{
    protected $signature = 'seo:detect-anomalies
        {--sync : Run inline (not queued)}
        {--no-sitemap : Skip sitemap fetch check}
        {--json : Output as JSON}';

    protected $description = 'Detect search telemetry anomalies';

    public function handle(AnomalyDetectionEngine $anomalyEngine): int
    {
        if ($this->option('sync')) {
            $this->info('Running anomaly detection inline...');
            $results = $anomalyEngine->detectAll();
        } else {
            DetectAnomaliesJob::dispatch(includeSitemapCheck: !$this->option('no-sitemap'));
            $this->info('✓ DetectAnomaliesJob dispatched to queue');
            return self::SUCCESS;
        }

        $total = collect($results)->flatten()->count();
        $stats = $anomalyEngine->getAnomalyStats();

        if ($this->option('json')) {
            $this->line(json_encode([
                'results' => $results,
                'stats' => $stats,
                'detected_at' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║     ANOMALY DETECTION REPORT            ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->line('');

        $this->line("  Total Anomalies Found: {$total}");
        $this->line("  Active:                {$stats['active_total']}");
        $this->line("  Critical:              {$stats['critical']}");
        $this->line("  Warning:               {$stats['warning']}");
        $this->line("  Info:                  {$stats['info']}");
        $this->line("  Last 24h:              {$stats['last_24h']}");

        if (!empty($stats['by_type'])) {
            $this->line('');
            $this->info(' ── By Type ──');
            foreach ($stats['by_type'] as $type => $count) {
                $this->line("  {$type}: {$count}");
            }
        }

        if (!empty($results)) {
            foreach ($results as $type => $anomalies) {
                if (!empty($anomalies)) {
                    $this->line('');
                    $this->info(" ── {$type} (" . count($anomalies) . ") ──");
                    foreach (array_slice($anomalies, 0, 5) as $a) {
                        $this->line("  [{$a['severity']}] {$a['description']}");
                    }
                    if (count($anomalies) > 5) {
                        $this->line("  ... and " . (count($anomalies) - 5) . " more");
                    }
                }
            }
        }

        return self::SUCCESS;
    }
}
