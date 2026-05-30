<?php

namespace App\Observers;

use App\Models\ContentNode;
use App\Services\IndexingService;
use Illuminate\Support\Facades\Log;

class ContentNodeObserver
{
    protected IndexingService $indexingService;

    public function __construct(IndexingService $indexingService)
    {
        $this->indexingService = $indexingService;
    }

    public function created(ContentNode $content): void
    {
        if (!$content->is_published) {
            return;
        }

        try {
            $result = $this->indexingService->indexContent($content);

            Log::info('Content auto-indexed on creation', [
                'content_id' => $content->id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Auto-indexing failed on creation', [
                'content_id' => $content->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updated(ContentNode $content): void
    {
        if (!$content->is_published) {
            return;
        }

        if ($content->wasChanged('is_published') || $content->wasChanged('slug')) {
            try {
                $result = $this->indexingService->indexContent($content);

                Log::info('Content re-indexed on update', [
                    'content_id' => $content->id,
                    'result' => $result
                ]);
            } catch (\Exception $e) {
                Log::error('Auto-indexing failed on update', [
                    'content_id' => $content->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function deleted(ContentNode $content): void
    {
        try {
            $result = $this->indexingService->removeFromIndex($content);

            Log::info('Content removed from index on deletion', [
                'content_id' => $content->id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Remove from index failed on deletion', [
                'content_id' => $content->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}