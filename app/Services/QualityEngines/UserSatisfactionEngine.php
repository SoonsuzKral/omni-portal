<?php

namespace App\Services\QualityEngines;

use App\Models\ContentNode;
use App\Models\UserSatisfactionScore;

class UserSatisfactionEngine
{
    public function analyze(ContentNode $content, array $engagementData = []): UserSatisfactionScore
    {
        $metrics = $this->prepareMetrics($content, $engagementData);

        $dwellScore = $this->computeDwellTimeScore($metrics);
        $scrollScore = $this->computeScrollDepthScore($metrics);
        $interactionScore = $this->computeInteractionRateScore($metrics);
        $bounceScore = $this->computeBounceBehaviorScore($metrics);
        $ctaScore = $this->computeCtaEngagementScore($metrics);
        $navScore = $this->computeNavigationDepthScore($metrics);

        $engagementScore = $this->aggregateEngagementScore([
            'dwell_time' => $dwellScore,
            'scroll_depth' => $scrollScore,
            'interaction_rate' => $interactionScore,
            'bounce_behavior' => $bounceScore,
            'cta_engagement' => $ctaScore,
            'navigation_depth' => $navScore,
        ]);

        $satisfactionScore = $this->computeSatisfactionScore(
            $engagementScore, $metrics
        );

        $score = UserSatisfactionScore::updateOrCreate(
            ['content_node_id' => $content->id],
            [
                'dwell_time_score' => $dwellScore,
                'scroll_depth_score' => $scrollScore,
                'interaction_rate_score' => $interactionScore,
                'bounce_behavior_score' => $bounceScore,
                'cta_engagement_score' => $ctaScore,
                'navigation_depth_score' => $navScore,
                'engagement_quality_score' => $engagementScore,
                'satisfaction_score' => $satisfactionScore,
                'raw_metrics' => $metrics,
                'analysis_details' => [
                    'signals_available' => $this->identifyAvailableSignals($metrics),
                    'confidence_level' => $this->computeConfidenceLevel($metrics),
                ],
            ]
        );

        $content->updateQuietly([
            'engagement_quality_score' => $engagementScore,
            'satisfaction_score' => $satisfactionScore,
        ]);

        return $score;
    }

    protected function prepareMetrics(ContentNode $content, array $engagementData): array
    {
        $body = strip_tags($content->body_content ?? '');
        $estimatedReadTime = max(1, str_word_count($body) / 200);

        return [
            'dwell_time_seconds' => $engagementData['dwell_time_seconds'] ?? null,
            'scroll_depth_percent' => $engagementData['scroll_depth_percent'] ?? null,
            'interaction_count' => $engagementData['interaction_count'] ?? null,
            'bounce_rate' => $engagementData['bounce_rate'] ?? null,
            'cta_clicks' => $engagementData['cta_clicks'] ?? null,
            'navigation_depth' => $engagementData['navigation_depth'] ?? null,
            'page_views' => $content->page_views ?? 0,
            'estimated_read_time_minutes' => $estimatedReadTime,
            'body_length' => strlen($body),
            'cta_count' => $this->countCtas($content->body_content ?? ''),
            'internal_link_count' => $this->countInternalLinks($content->body_content ?? ''),
            'gsc_clicks' => $content->gsc_total_clicks ?? 0,
            'gsc_impressions' => $content->gsc_total_impressions ?? 0,
            'gsc_ctr' => $content->gsc_total_impressions > 0
                ? ($content->gsc_total_clicks / $content->gsc_total_impressions) * 100
                : 0,
            'gsc_avg_position' => $content->gsc_avg_position ?? 0,
        ];
    }

    protected function computeDwellTimeScore(array $metrics): float
    {
        if ($metrics['dwell_time_seconds'] !== null) {
            $targetDwell = config('quality-engine.satisfaction.target_dwell_seconds', 120);
            $ratio = $metrics['dwell_time_seconds'] / $targetDwell;
            return round(min(100, $ratio * 100), 2);
        }

        if ($metrics['page_views'] > 0 && $metrics['gsc_clicks'] > 0) {
            $ctrRatio = $metrics['gsc_ctr'] / 5;
            return round(min(100, $ctrRatio * 100), 2);
        }

        $estimatedReadTime = $metrics['estimated_read_time_minutes'];
        if ($estimatedReadTime >= 5) {
            return 80;
        } elseif ($estimatedReadTime >= 3) {
            return 60;
        } elseif ($estimatedReadTime >= 1) {
            return 40;
        }

        return 25;
    }

    protected function computeScrollDepthScore(array $metrics): float
    {
        if ($metrics['scroll_depth_percent'] !== null) {
            $minScroll = config('quality-engine.satisfaction.min_scroll_depth', 0.5);
            $ratio = $metrics['scroll_depth_percent'] / ($minScroll * 100);
            return round(min(100, $ratio * 100), 2);
        }

        $bodyLength = $metrics['body_length'];
        if ($bodyLength > 5000) {
            return 75;
        } elseif ($bodyLength > 2000) {
            return 60;
        } elseif ($bodyLength > 800) {
            return 45;
        }

        return 25;
    }

    protected function computeInteractionRateScore(array $metrics): float
    {
        if ($metrics['interaction_count'] !== null) {
            return round(min(100, $metrics['interaction_count'] * 20), 2);
        }

        $ctaCount = $metrics['cta_count'];
        if ($ctaCount > 3) {
            return 70;
        } elseif ($ctaCount > 1) {
            return 50;
        } elseif ($ctaCount > 0) {
            return 30;
        }

        return 20;
    }

    protected function computeBounceBehaviorScore(array $metrics): float
    {
        if ($metrics['bounce_rate'] !== null) {
            $bounceScore = 100 - ($metrics['bounce_rate'] * 100);
            return round(max(0, $bounceScore), 2);
        }

        if ($metrics['page_views'] > 0 && $metrics['gsc_clicks'] > 0) {
            $clickRate = $metrics['gsc_clicks'] / max(1, $metrics['page_views']);
            return round(min(100, $clickRate * 500), 2);
        }

        $readTime = $metrics['estimated_read_time_minutes'];
        if ($readTime > 3) {
            return 65;
        } elseif ($readTime > 1) {
            return 45;
        }

        return 30;
    }

    protected function computeCtaEngagementScore(array $metrics): float
    {
        $ctaCount = $metrics['cta_count'];
        if ($ctaCount === 0) {
            return 0;
        }

        if ($metrics['cta_clicks'] !== null) {
            $clickRate = $metrics['cta_clicks'] / $ctaCount;
            return round(min(100, $clickRate * 100), 2);
        }

        $internalLinks = $metrics['internal_link_count'];
        if ($internalLinks > 5) {
            return 65;
        } elseif ($internalLinks > 3) {
            return 50;
        } elseif ($internalLinks > 0) {
            return 30;
        }

        return 15;
    }

    protected function computeNavigationDepthScore(array $metrics): float
    {
        if ($metrics['navigation_depth'] !== null) {
            return round(min(100, $metrics['navigation_depth'] * 25), 2);
        }

        $internalLinks = $metrics['internal_link_count'];
        if ($internalLinks >= 8) {
            return 80;
        } elseif ($internalLinks >= 5) {
            return 60;
        } elseif ($internalLinks >= 3) {
            return 40;
        } elseif ($internalLinks > 0) {
            return 20;
        }

        return 0;
    }

    protected function aggregateEngagementScore(array $scores): float
    {
        $weights = [
            'dwell_time' => 0.25,
            'scroll_depth' => 0.15,
            'interaction_rate' => 0.15,
            'bounce_behavior' => 0.20,
            'cta_engagement' => 0.15,
            'navigation_depth' => 0.10,
        ];

        $weightedSum = 0;
        foreach ($scores as $key => $score) {
            $weightedSum += $score * ($weights[$key] ?? 0.15);
        }

        return round($weightedSum, 2);
    }

    protected function computeSatisfactionScore(float $engagementScore, array $metrics): float
    {
        $satisfaction = $engagementScore * 0.7;

        if ($metrics['gsc_ctr'] > 5) {
            $satisfaction += 15;
        } elseif ($metrics['gsc_ctr'] > 2) {
            $satisfaction += 8;
        }

        if ($metrics['gsc_avg_position'] > 0 && $metrics['gsc_avg_position'] <= 5) {
            $satisfaction += 10;
        } elseif ($metrics['gsc_avg_position'] <= 10) {
            $satisfaction += 5;
        }

        if ($metrics['estimated_read_time_minutes'] >= 3) {
            $satisfaction += 5;
        }

        return round(min(100, $satisfaction), 2);
    }

    protected function countCtas(string $html): int
    {
        $count = 0;

        $buttonPatterns = [
            '/<button[^>]*>.*?<\/button>/si',
            '/<a[^>]*class=["\'][^"\']*(?:btn|button|cta)[^"\']*["\'][^>]*>.*?<\/a>/si',
            '/class=["\'][^"\']*(?:cta|cTA|call-to-action)[^"\']*["\']/i',
        ];

        foreach ($buttonPatterns as $pattern) {
            preg_match_all($pattern, $html, $matches);
            $count += count($matches[0]);
        }

        return $count;
    }

    protected function countInternalLinks(string $html): int
    {
        preg_match_all('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\']/i', $html, $matches);
        $links = $matches[1] ?? [];

        return count(array_filter($links, function ($url) {
            return str_contains($url, parse_url(config('app.url'), PHP_URL_HOST));
        }));
    }

    protected function identifyAvailableSignals(array $metrics): array
    {
        $signals = [];
        if ($metrics['dwell_time_seconds'] !== null) $signals[] = 'dwell_time';
        if ($metrics['scroll_depth_percent'] !== null) $signals[] = 'scroll_depth';
        if ($metrics['interaction_count'] !== null) $signals[] = 'interactions';
        if ($metrics['bounce_rate'] !== null) $signals[] = 'bounce_rate';
        if ($metrics['cta_clicks'] !== null) $signals[] = 'cta_clicks';
        if ($metrics['navigation_depth'] !== null) $signals[] = 'navigation_depth';
        if ($metrics['gsc_impressions'] > 0) $signals[] = 'gsc_data';

        return $signals;
    }

    protected function computeConfidenceLevel(array $metrics): string
    {
        $signalCount = count($this->identifyAvailableSignals($metrics));
        if ($signalCount >= 5) return 'high';
        if ($signalCount >= 3) return 'medium';
        return 'low';
    }
}
