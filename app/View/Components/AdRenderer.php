<?php

namespace App\View\Components;

use App\Models\GlobalAdBlock;
use Illuminate\View\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AdRenderer extends Component
{
    public $content;
    public $position;
    public $restricted = false;
    public $containerId;
    public $containerClass;

    public function __construct($content = null, $position = 'header', $restricted = false)
    {
        $this->content = $content;
        $this->position = $position;
        $this->restricted = $restricted;
        $this->containerId = 'c-item-' . rand(100, 999);
        $this->containerClass = 'wrapper-node-' . Str::random(4);
    }

    public function scripts(): array
    {
        $position = $this->position;
        $cacheKey = 'ad_scripts_' . $position . '_' . ($this->content?->id ?? '0') . '_' . ($this->isRestricted() ? '1' : '0');

        return Cache::remember($cacheKey, 3600, function () use ($position) {
            $type = $this->isRestricted() ? 'Restricted' : 'Safe';

            if (is_null($this->content)) {
                $adBlocks = GlobalAdBlock::where('active', 1)
                    ->where('network_type', $type)
                    ->where('is_global', true)
                    ->where('position', $position)
                    ->pluck('script')
                    ->toArray();
                return is_array($adBlocks) ? $adBlocks : [];
            }

            $taxonomyId = isset($this->content->taxonomy_id) ? $this->content->taxonomy_id : null;

            if ($taxonomyId) {
                $categorySpecificAd = GlobalAdBlock::where('active', 1)
                    ->where('network_type', $type)
                    ->where('taxonomy_id', $taxonomyId)
                    ->where('position', $position)
                    ->first();

                if ($categorySpecificAd) {
                    return [$categorySpecificAd->script];
                }
            }

            $globalAds = GlobalAdBlock::where('active', 1)
                ->where('network_type', $type)
                ->where('is_global', true)
                ->where('position', $position)
                ->pluck('script')
                ->toArray();

            return is_array($globalAds) ? $globalAds : [];
        });
    }

    public function isRestricted(): bool
    {
        if ($this->content && isset($this->content->is_restricted_content)) {
            return $this->content->is_restricted_content;
        }
        return $this->restricted;
    }

    public function isStickyBottom(): bool
    {
        return $this->position === 'sticky_bottom';
    }

    public function render()
    {
        return view('components.ad-renderer', [
            'scripts' => $this->scripts()
        ]);
    }
}