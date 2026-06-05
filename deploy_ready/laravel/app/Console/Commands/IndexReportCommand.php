<?php

namespace App\Console\Commands;

use App\Services\IndexCoverageMonitor;
use App\Services\SearchTelemetryEngine;
use Illuminate\Console\Command;

class IndexReportCommand extends Command
{
    protected $signature = 'seo:index-report
        {--days=30 : Number of days of history to show}
        {--snapshot : Capture a fresh snapshot before report}
        {--json : Output as JSON}';

    protected $description = 'Generate index coverage report';

    public function handle(
        IndexCoverageMonitor $coverageMonitor,
        SearchTelemetryEngine $telemetryEngine,
    ): int {
        if ($this->option('snapshot')) {
            $this->info('Capturing fresh snapshot...');
            $coverageMonitor->captureSnapshot();
        }

        $days = (int) $this->option('days');

        $coverage = $telemetryEngine->getAggregateStats();
        $ratio = $coverageMonitor->getSubmittedVsIndexedRatio();
        $avgLatency = $coverageMonitor->getAverageIndexingLatency();
        $crawlEfficiency = $coverageMonitor->getCrawlEfficiency();
        $sitemapEfficiency = $coverageMonitor->getSitemapEfficiency();

        if ($this->option('json')) {
            $this->line(json_encode([
                'coverage' => $coverage,
                'submitted_vs_indexed_ratio' => $ratio,
                'avg_indexing_latency_minutes' => $avgLatency,
                'crawl_efficiency_percent' => $crawlEfficiency,
                'sitemap_efficiency_percent' => $sitemapEfficiency,
                'report_date' => now()->toIso8601String(),
                'history_days' => $days,
            ], JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║     INDEX COVERAGE REPORT               ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->line('');

        $this->line("  Total URLs:          {$coverage['total_urls']}");
        $this->line("  Indexed URLs:        {$coverage['indexed_urls']}");
        $this->line("  Never Indexed:       {$coverage['never_indexed']}");
        $this->line("  Synced with GSC:     {$coverage['synced_urls']}");
        $this->line('');
        $this->info(' ── Coverage Metrics ──');
        $this->line("  Coverage %:          {$coverage['coverage_percentage']}%");
        $this->line("  Submitted/Indexed:   {$ratio}%");
        $this->line("  Avg Position:        {$coverage['avg_position']}");
        $this->line("  Avg Index Latency:   {$avgLatency} min");
        $this->line('');
        $this->info(' ── Performance Metrics ──');
        $this->line("  Total Impressions:   {$coverage['total_impressions']}");
        $this->line("  Total Clicks:        {$coverage['total_clicks']}");
        $this->line("  Overall CTR:         {$coverage['overall_ctr']}%");
        $this->line("  Crawl Efficiency:    {$crawlEfficiency}%");
        $this->line("  Sitemap Efficiency:  {$sitemapEfficiency}%");
        $this->line('');
        $this->info(' ── Anomalies ──');
        $this->line("  Pages Losing Impressions: {$coverage['pages_losing_impressions']}");

        if (!empty($coverage['top_performing_urls'])) {
            $this->line('');
            $this->info(' ── Top Performing URLs ──');
            foreach ($coverage['top_performing_urls'] as $i => $page) {
                $ctr = $page['gsc_total_impressions'] > 0
                    ? round(($page['gsc_total_clicks'] / $page['gsc_total_impressions']) * 100, 2)
                    : 0;
                $this->line("  " . ($i + 1) . ". {$page['seo_title']}");
                $this->line("     CTR: {$ctr}% | Pos: {$page['gsc_avg_position']} | Imp: {$page['gsc_total_impressions']}");
            }
        }

        $this->line('');
        $this->line("  Report generated: {$coverage['calculated_at']}");

        return self::SUCCESS;
    }
}
