<?php

namespace App\Services\QualityEngines;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\TopicAuthorityScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopicAuthorityEngine
{
    public function analyzeContentNode(ContentNode $content): TopicAuthorityScore
    {
        $body = strip_tags($content->body_content ?? '');
        $taxonomy = $content->taxonomy;

        $coverageScore = $this->computeTopicCoverage($content, $taxonomy, $body);
        $entityCompleteness = $this->computeEntityCompleteness($content, $body);
        $clusterDepth = $this->computeSemanticClusterDepth($content, $taxonomy);
        $supportingRatio = $this->computeSupportingContentRatio($content, $taxonomy);
        $internalLinksScore = $this->computeInternalTopicalLinks($content, $body);

        $authorityClusterScore = $this->computeAuthorityClusterScore([
            'topic_coverage' => $coverageScore,
            'entity_completeness' => $entityCompleteness,
            'cluster_depth' => $clusterDepth,
            'supporting_ratio' => $supportingRatio,
            'internal_links' => $internalLinksScore,
        ]);

        $clusterMembers = $this->identifyClusterMembers($content, $taxonomy);

        $score = TopicAuthorityScore::updateOrCreate(
            ['topicable_type' => ContentNode::class, 'topicable_id' => $content->id],
            [
                'topic_coverage_score' => $coverageScore,
                'entity_completeness_score' => $entityCompleteness,
                'semantic_cluster_depth' => $clusterDepth,
                'supporting_content_ratio' => $supportingRatio,
                'internal_topical_links_score' => $internalLinksScore,
                'authority_cluster_score' => $authorityClusterScore,
                'cluster_members' => $clusterMembers,
                'analysis_details' => [
                    'word_count' => str_word_count($body),
                    'taxonomy_id' => $taxonomy?->id,
                    'related_content_count' => $this->getRelatedContentCount($content, $taxonomy),
                    'entity_types_found' => $this->getEntityTypesFound($body),
                ],
            ]
        );

        $content->updateQuietly([
            'topic_coverage_score' => $coverageScore,
            'authority_cluster_score' => $authorityClusterScore,
        ]);

        return $score;
    }

    public function analyzeTaxonomy(Taxonomy $taxonomy): TopicAuthorityScore
    {
        $nodes = ContentNode::where('taxonomy_id', $taxonomy->id)->whereNotNull('body_content');

        $totalNodes = $nodes->count();
        if ($totalNodes === 0) {
            return TopicAuthorityScore::updateOrCreate(
                ['topicable_type' => Taxonomy::class, 'topicable_id' => $taxonomy->id],
                [
                    'topic_coverage_score' => 0,
                    'entity_completeness_score' => 0,
                    'semantic_cluster_depth' => 0,
                    'supporting_content_ratio' => 0,
                    'internal_topical_links_score' => 0,
                    'authority_cluster_score' => 0,
                    'cluster_members' => [],
                ]
            );
        }

        $uniqueLocations = $nodes->whereNotNull('location_id')->distinct('location_id')->count('location_id');
        $locationCoverage = min(100, ($uniqueLocations / max(1, $totalNodes)) * 100);

        $bodies = $nodes->pluck('body_content')->map(fn($b) => strip_tags($b));
        $totalWords = $bodies->sum(fn($b) => str_word_count($b));
        $avgWordsPerNode = $totalNodes > 0 ? $totalWords / $totalNodes : 0;

        $wordDepthScore = min(100, ($avgWordsPerNode / 1000) * 100);

        $internalLinks = $bodies->sum(function ($body) {
            return preg_match_all('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\']/i', $body);
        });
        $internalLinkScore = min(100, $totalNodes > 0 ? ($internalLinks / $totalNodes) * 10 : 0);

        $coverageScore = ($locationCoverage * 0.4) + ($wordDepthScore * 0.4) + ($internalLinkScore * 0.2);

        return TopicAuthorityScore::updateOrCreate(
            ['topicable_type' => Taxonomy::class, 'topicable_id' => $taxonomy->id],
            [
                'topic_coverage_score' => round($coverageScore, 2),
                'entity_completeness_score' => round($locationCoverage, 2),
                'semantic_cluster_depth' => round($wordDepthScore, 2),
                'supporting_content_ratio' => round(min(100, $totalNodes * 5), 2),
                'internal_topical_links_score' => round($internalLinkScore, 2),
                'authority_cluster_score' => round(($coverageScore + $wordDepthScore + $internalLinkScore) / 3, 2),
                'cluster_members' => $nodes->pluck('id')->toArray(),
            ]
        );
    }

    protected function computeTopicCoverage(ContentNode $content, ?Taxonomy $taxonomy, string $body): float
    {
        $score = 50;

        if (!$taxonomy) {
            return $score;
        }

        $bodyLower = mb_strtolower($body);
        $taxonomyName = mb_strtolower($taxonomy->name);

        $keywordVariants = [
            $taxonomyName,
            $taxonomyName . 's',
            $taxonomyName . 'ing',
            $taxonomyName . 'er',
            $taxonomyName . 'ers',
            $taxonomyName . ' service',
            $taxonomyName . ' services',
        ];

        $foundVariants = 0;
        foreach ($keywordVariants as $variant) {
            if (mb_strpos($bodyLower, $variant) !== false) {
                $foundVariants++;
            }
        }
        $score += min(20, $foundVariants * 4);

        $questionWords = ['what', 'why', 'how', 'when', 'where', 'which', 'who',
            'benefits', 'cost', 'price', 'best', 'top', 'guide', 'tips',
            'tutorial', 'review', 'comparison', 'vs', 'versus', 'alternative'];
        $foundQuestions = 0;
        foreach ($questionWords as $qw) {
            if (mb_strpos($bodyLower, $qw) !== false) {
                $foundQuestions++;
            }
        }
        $score += min(15, $foundQuestions * 2);

        if ($content->location) {
            $locationName = mb_strtolower($content->location->name);
            if (mb_strpos($bodyLower, $locationName) !== false) {
                $score += 10;
            }
        }

        $bodyLength = strlen($body);
        if ($bodyLength > 3000) {
            $score += 10;
        } elseif ($bodyLength > 1500) {
            $score += 5;
        }

        return round(min(100, $score), 2);
    }

    protected function computeEntityCompleteness(ContentNode $content, string $body): float
    {
        $bodyLower = mb_strtolower($body);
        $entityCategories = [
            'location_specific' => $content->location ? 1 : 0,
            'pricing' => (int) preg_match('/\b(price|cost|fee|rate|pricing|\$|€|£|tl|try)\b/i', $body),
            'contact' => (int) preg_match('/\b(phone|email|address|contact|visit|location|map|direction)\b/i', $body),
            'reviews' => (int) preg_match('/\b(review|rating|star|testimonial|feedback|experience)\b/i', $body),
            'comparison' => (int) preg_match('/\b(compare|comparison|vs|versus|alternative|better|best|worst)\b/i', $body),
            'benefits' => (int) preg_match('/\b(benefit|advantage|pros|feature|why choose|reason)\b/i', $body),
            'process' => (int) preg_match('/\b(step|process|how to|guide|tutorial|instruction|method)\b/i', $body),
            'faq' => (int) preg_match('/\bfaq|frequently asked|common question|question\??\s/i', $body),
            'timing' => (int) preg_match('/\b(hour|minute|day|week|month|year|duration|schedule|open|close)\b/i', $body),
            'requirements' => (int) preg_match('/\b(require|need|must|prerequisite|qualification|document|need to)\b/i', $body),
        ];

        $foundCount = array_sum($entityCategories);
        $totalCategories = count($entityCategories);

        return round(($foundCount / $totalCategories) * 100, 2);
    }

    protected function computeSemanticClusterDepth(ContentNode $content, ?Taxonomy $taxonomy): float
    {
        if (!$taxonomy) {
            return 0;
        }

        $depth = 0;

        $parent = $taxonomy->parent;
        if ($parent) {
            $depth++;
            if ($parent->parent) {
                $depth++;
            }
        }

        $children = Taxonomy::where('parent_id', $taxonomy->id)->count();
        if ($children > 0) {
            $depth++;
        }

        $siblings = Taxonomy::where('parent_id', $taxonomy->parent_id)
            ->where('id', '!=', $taxonomy->id)
            ->count();
        if ($siblings > 0) {
            $depth += 0.5;
        }

        $minDepth = config('quality-engine.topic_authority.cluster_depth_min', 2);

        return round(min(100, ($depth / $minDepth) * 100), 2);
    }

    protected function computeSupportingContentRatio(ContentNode $content, ?Taxonomy $taxonomy): float
    {
        if (!$taxonomy) {
            return 0;
        }

        $totalInTaxonomy = ContentNode::where('taxonomy_id', $taxonomy->id)->count();
        if ($totalInTaxonomy <= 1) {
            return 0;
        }

        $sameTemplate = ContentNode::where('taxonomy_id', $taxonomy->id)
            ->where('post_template_id', $content->post_template_id)
            ->count();

        $differentTemplate = $totalInTaxonomy - $sameTemplate;
        $ratio = $totalInTaxonomy > 0 ? $differentTemplate / $totalInTaxonomy : 0;

        return round($ratio * 100, 2);
    }

    protected function computeInternalTopicalLinks(ContentNode $content, string $body): float
    {
        preg_match_all('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\']/i', $body, $links);
        $allLinks = $links[1] ?? [];

        $internalLinks = array_filter($allLinks, function ($url) {
            return str_contains($url, parse_url(config('app.url'), PHP_URL_HOST));
        });

        $internalCount = count($internalLinks);

        if ($internalCount >= 5) {
            return 100;
        } elseif ($internalCount >= 3) {
            return 80;
        } elseif ($internalCount >= 1) {
            return 50;
        }

        return 0;
    }

    protected function computeAuthorityClusterScore(array $scores): float
    {
        $weights = [
            'topic_coverage' => 0.30,
            'entity_completeness' => 0.20,
            'cluster_depth' => 0.15,
            'supporting_ratio' => 0.15,
            'internal_links' => 0.20,
        ];

        $weightedSum = 0;
        foreach ($scores as $key => $score) {
            $weightedSum += $score * ($weights[$key] ?? 0.2);
        }

        return round($weightedSum, 2);
    }

    protected function identifyClusterMembers(ContentNode $content, ?Taxonomy $taxonomy): array
    {
        if (!$taxonomy) {
            return [];
        }

        $relatedIds = ContentNode::where('taxonomy_id', $taxonomy->id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->orderBy('page_views', 'desc')
            ->limit(20)
            ->pluck('id');

        return $relatedIds->toArray();
    }

    protected function getRelatedContentCount(ContentNode $content, ?Taxonomy $taxonomy): int
    {
        if (!$taxonomy) {
            return 0;
        }

        return ContentNode::where('taxonomy_id', $taxonomy->id)
            ->where('id', '!=', $content->id)
            ->count();
    }

    protected function getEntityTypesFound(string $body): array
    {
        $types = [];
        $bodyLower = mb_strtolower($body);

        $typePatterns = [
            'location' => '/\b(city|town|village|district|neighborhood|area|region|state|country)\b/i',
            'service' => '/\b(service|provider|agency|company|firm|shop|store|center|studio|salon)\b/i',
            'price' => '/\b(price|cost|fee|rate|pricing|\$|€|£|tl|try)\b/i',
            'time' => '/\b(hour|minute|day|week|month|year|open|close|schedule|appointment)\b/i',
            'quality' => '/\b(best|top|leading|premier|excellent|quality|premium|superior|professional)\b/i',
            'review' => '/\b(review|rating|star|score|feedback|testimonial|recommend)\b/i',
        ];

        foreach ($typePatterns as $type => $pattern) {
            if (preg_match($pattern, $bodyLower)) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
