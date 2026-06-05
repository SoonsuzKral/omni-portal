<?php

namespace App\View\Components;

use App\Models\ContentNode;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;

class TrendingContent extends Component
{
    public $currentContent;
    public $trendingItems;

    public function __construct($currentContent = null)
    {
        $this->currentContent = $currentContent;

        $this->trendingItems = Cache::remember('trending_content_global', 1800, function () {
            return ContentNode::where('locale', app()->getLocale())
                ->whereNotNull('publish_date')
                ->with(['taxonomy', 'location'])
                ->orderBy('page_views', 'desc')
                ->limit(10)
                ->get();
        });
    }

    public function render()
    {
        return view('components.trending-content');
    }
}
