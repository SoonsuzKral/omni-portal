<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\IndexingService;
use App\Services\PingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContentWaveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    public function __construct(
        public int $waveSize = 500,
        public int $waveDelayMinutes = 30
    ) {}

    public function handle(IndexingService $indexingService, PingService $pingService): void
    {
        $waveKey = 'content_wave_offset';
        $offset = Cache::get($waveKey, 0);

        $batch = ContentNode::whereNotNull('publish_date')
            ->orderBy('publish_date', 'asc')
            ->skip($offset)
            ->limit($this->waveSize)
            ->pluck('id');

        if ($batch->isEmpty()) {
            Log::info('ContentWaveJob: All content processed, no more waves.');
            Cache::forget($waveKey);
            return;
        }

        $indexingService->queueForIndexing($batch->toArray());

        $totalProcessed = $offset + $batch->count();
        Cache::put($waveKey, $totalProcessed, now()->addDays(7));

        Log::info("ContentWaveJob: Wave dispatched", [
            'wave_size' => $batch->count(),
            'offset' => $offset,
            'total_processed' => $totalProcessed,
        ]);

        if (ContentNode::whereNotNull('publish_date')->count() > $totalProcessed) {
            self::dispatch($this->waveSize, $this->waveDelayMinutes)
                ->delay(now()->addMinutes($this->waveDelayMinutes));
        } else {
            Cache::forget($waveKey);
            Log::info('ContentWaveJob: All content waves completed.');
        }

        try {
            $pingService->pingSearchEngines();
        } catch (\Exception $e) {
            Log::warning('ContentWaveJob: Ping failed', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ContentWaveJob failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
