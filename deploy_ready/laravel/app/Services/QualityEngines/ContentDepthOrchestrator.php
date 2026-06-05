<?php

namespace App\Services\QualityEngines;

use App\Models\ContentDepthScore;
use App\Models\ContentNode;
use Illuminate\Support\Str;

class ContentDepthOrchestrator
{
    public function analyze(ContentNode $content): ContentDepthScore
    {
        $body = $content->body_content ?? '';
        $plainText = strip_tags($body);

        $faqCount = $this->countFaqs($body);
        $semanticExpansions = $this->countSemanticExpansions($body, $content);
        $relatedEntities = $this->countRelatedEntities($body, $content);
        $dataBlocks = $this->countSupportingDataBlocks($body);
        $comparisons = $this->countComparisonSections($body);

        $suggestions = $this->generateEnrichmentSuggestions($content, $plainText, [
            'faq_count' => $faqCount,
            'semantic_expansion_count' => $semanticExpansions,
            'related_entity_count' => $relatedEntities,
            'supporting_data_blocks' => $dataBlocks,
            'comparison_sections' => $comparisons,
        ]);

        $depthScore = $this->computeDepthScore($plainText, [
            'faq_count' => $faqCount,
            'semantic_expansion_count' => $semanticExpansions,
            'related_entity_count' => $relatedEntities,
            'supporting_data_blocks' => $dataBlocks,
            'comparison_sections' => $comparisons,
        ]);

        $richnessScore = $this->computeRichnessScore($plainText, [
            'faq_count' => $faqCount,
            'semantic_expansion_count' => $semanticExpansions,
            'related_entity_count' => $relatedEntities,
            'supporting_data_blocks' => $dataBlocks,
            'comparison_sections' => $comparisons,
        ]);

        $score = ContentDepthScore::updateOrCreate(
            ['content_node_id' => $content->id],
            [
                'depth_score' => $depthScore,
                'richness_score' => $richnessScore,
                'faq_count' => $faqCount,
                'semantic_expansion_count' => $semanticExpansions,
                'related_entity_count' => $relatedEntities,
                'supporting_data_blocks' => $dataBlocks,
                'comparison_sections' => $comparisons,
                'enrichment_suggestions' => $suggestions,
                'analysis_details' => [
                    'word_count' => str_word_count($plainText),
                    'heading_count' => $this->countHeadings($body),
                    'image_count' => $this->countImages($body),
                    'list_count' => $this->countLists($body),
                    'link_count' => $this->countLinks($body),
                    'table_count' => $this->countTables($body),
                ],
            ]
        );

        $content->updateQuietly([
            'depth_score' => $depthScore,
            'richness_score' => $richnessScore,
        ]);

        return $score;
    }

    protected function countFaqs(string $html): int
    {
        $count = 0;

        if (preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>.*?"@type"\s*:\s*"FAQPage".*?<\/script>/si', $html)) {
            $count++;
        }

        preg_match_all('/<div[^>]*class=["\'][^"\']*faq[^"\']*["\']/si', $html, $faqDivs);
        $count += count($faqDivs[0]);

        preg_match_all('/<details[^>]*>.*?<summary[^>]*>.*?<\/summary>.*?<\/details>/si', $html, $details);
        $count += count($details[0]);

        preg_match_all('/<h[1-6][^>]*>\s*(?:FAQ|Frequently Asked Questions|Common Questions)\s*<\/h[1-6]>/si', $html, $faqHeadings);
        if (!empty($faqHeadings[0])) {
            $count += count($faqHeadings[0]);
        }

        return $count;
    }

    protected function countSemanticExpansions(string $html, ContentNode $content): int
    {
        $count = 0;
        $plainText = strip_tags($html);

        $expansionTriggers = [
            'for example', 'for instance', 'such as', 'including', 'specifically',
            'in particular', 'notably', 'particularly', 'e.g.', 'i.e.',
            'in other words', 'that is', 'to illustrate', 'as an example',
            'this includes', 'these include', 'namely',
        ];

        $bodyLower = mb_strtolower($plainText);
        foreach ($expansionTriggers as $trigger) {
            $count += mb_substr_count($bodyLower, $trigger);
        }

        if ($content->taxonomy) {
            $childTaxonomies = \App\Models\Taxonomy::where('parent_id', $content->taxonomy->id)->count();
            if ($childTaxonomies > 0) {
                $count += min(3, $childTaxonomies);
            }
        }

        preg_match_all('/<ul[^>]*>.*?<\/ul>/si', $html, $lists);
        foreach ($lists[0] as $list) {
            preg_match_all('/<li[^>]*>.*?<\/li>/si', $list, $items);
            if (count($items[0]) >= 3) {
                $count++;
            }
        }

        return $count;
    }

    protected function countRelatedEntities(string $html, ContentNode $content): int
    {
        $count = 0;

        preg_match_all('/<a[^>]*href=["\']https?:\/\/[^"\']+["\'][^>]*>/i', $html, $links);
        $externalLinks = array_filter($links[1] ?? [], function ($url) {
            return !str_contains($url, parse_url(config('app.url'), PHP_URL_HOST));
        });
        $count += count($externalLinks);

        if ($content->location) {
            $locationMentions = mb_substr_count(mb_strtolower(strip_tags($html)), mb_strtolower($content->location->name));
            if ($locationMentions > 1) {
                $count++;
            }
        }

        if ($content->taxonomy) {
            $taxonomyMentions = mb_substr_count(mb_strtolower(strip_tags($html)), mb_strtolower($content->taxonomy->name));
            if ($taxonomyMentions > 3) {
                $count++;
            }
        }

        return $count;
    }

    protected function countSupportingDataBlocks(string $html): int
    {
        $count = 0;

        preg_match_all('/<table[^>]*>.*?<\/table>/si', $html, $tables);
        $count += count($tables[0]);

        preg_match_all('/<figure[^>]*>.*?<\/figure>/si', $html, $figures);
        $count += count($figures[0]);

        preg_match_all('/<blockquote[^>]*>.*?<\/blockquote>/si', $html, $blockquotes);
        $count += count($blockquotes[0]);

        preg_match_all('/<img[^>]*>/si', $html, $images);
        $count += count($images[0]);

        preg_match_all('/<pre[^>]*>.*?<\/pre>/si', $html, $preBlocks);
        $count += count($preBlocks[0]);

        preg_match_all('/<code[^>]*>.*?<\/code>/si', $html, $codeBlocks);
        $count += count($codeBlocks[0]);

        preg_match_all('/<aside[^>]*>.*?<\/aside>/si', $html, $asides);
        $count += count($asides[0]);

        return $count;
    }

    protected function countComparisonSections(string $html): int
    {
        $count = 0;
        $plainText = strip_tags($html);
        $bodyLower = mb_strtolower($plainText);

        $comparisonTriggers = [
            'comparison', 'compare', 'vs ', 'versus', 'alternative',
            'better than', 'worse than', 'similar to', 'different from',
            'pros and cons', 'advantages and disadvantages', 'strengths and weaknesses',
            'side by side', 'feature comparison', 'price comparison',
        ];

        foreach ($comparisonTriggers as $trigger) {
            if (mb_strpos($bodyLower, $trigger) !== false) {
                $count++;
            }
        }

        preg_match_all('/<table[^>]*>.*?<tr[^>]*>.*?<th[^>]*>/si', $html, $tablesWithHeaders);
        if (!empty($tablesWithHeaders[0])) {
            $count += count($tablesWithHeaders[0]);
        }

        return $count;
    }

    protected function generateEnrichmentSuggestions(ContentNode $content, string $plainText, array $current): array
    {
        $suggestions = [];
        $minFaq = config('quality-engine.depth.min_faq_count', 3);
        $minWords = config('quality-engine.depth.min_word_count', 800);
        $minSections = config('quality-engine.depth.min_sections', 4);
        $wordCount = str_word_count($plainText);
        $headingCount = $this->countHeadings($content->body_content ?? '');

        if ($current['faq_count'] < $minFaq) {
            $suggestions[] = [
                'type' => 'faq',
                'priority' => 'high',
                'message' => "Add FAQ section (currently {$current['faq_count']}, recommend {$minFaq}+)",
                'expected_impact' => '+0.15 depth_score',
            ];
        }

        if ($wordCount < $minWords) {
            $suggestions[] = [
                'type' => 'expansion',
                'priority' => 'high',
                'message' => "Expand content length ({$wordCount} words, recommend {$minWords}+)",
                'expected_impact' => '+0.20 depth_score',
            ];
        }

        if ($headingCount < $minSections) {
            $suggestions[] = [
                'type' => 'structure',
                'priority' => 'medium',
                'message' => "Add more sections (currently {$headingCount}, recommend {$minSections}+)",
                'expected_impact' => '+0.10 depth_score',
            ];
        }

        if ($current['semantic_expansion_count'] < 3) {
            $suggestions[] = [
                'type' => 'semantic',
                'priority' => 'medium',
                'message' => 'Add semantic expansions (examples, elaborations, detailed explanations)',
                'expected_impact' => '+0.10 richness_score',
            ];
        }

        if ($current['related_entity_count'] < 2) {
            $suggestions[] = [
                'type' => 'entity',
                'priority' => 'medium',
                'message' => 'Link to related entities and authoritative external sources',
                'expected_impact' => '+0.10 richness_score',
            ];
        }

        if ($current['supporting_data_blocks'] < 3) {
            $suggestions[] = [
                'type' => 'data',
                'priority' => 'low',
                'message' => 'Add supporting data blocks (tables, images, blockquotes, code)',
                'expected_impact' => '+0.08 richness_score',
            ];
        }

        if ($current['comparison_sections'] < 1) {
            $suggestions[] = [
                'type' => 'comparison',
                'priority' => 'low',
                'message' => 'Add comparison section (alternatives, pros/cons, vs content)',
                'expected_impact' => '+0.05 depth_score',
            ];
        }

        return $suggestions;
    }

    protected function computeDepthScore(string $plainText, array $current): float
    {
        $wordCount = str_word_count($plainText);
        $minWords = config('quality-engine.depth.min_word_count', 800);
        $wordScore = min(100, ($wordCount / $minWords) * 100);

        $faqScore = min(100, $current['faq_count'] * 20);
        $expansionScore = min(100, $current['semantic_expansion_count'] * 15);
        $comparisonScore = min(100, $current['comparison_sections'] * 25);

        $depthScore = ($wordScore * 0.40)
            + ($faqScore * 0.25)
            + ($expansionScore * 0.20)
            + ($comparisonScore * 0.15);

        return round($depthScore, 2);
    }

    protected function computeRichnessScore(string $plainText, array $current): float
    {
        $entityScore = min(100, $current['related_entity_count'] * 12);
        $dataScore = min(100, $current['supporting_data_blocks'] * 8);

        $imageCount = $this->countImages('');
        $imageScore = min(100, $imageCount * 15);

        $linkCount = $this->countLinks('');
        $linkScore = min(100, $linkCount * 6);

        $listCount = $this->countLists('');
        $listScore = min(100, $listCount * 12);

        $richnessScore = ($entityScore * 0.25)
            + ($dataScore * 0.30)
            + ($imageScore * 0.20)
            + ($linkScore * 0.10)
            + ($listScore * 0.15);

        return round($richnessScore, 2);
    }

    protected function countHeadings(string $html): int
    {
        preg_match_all('/<h[1-6][^>]*>/i', $html, $matches);
        return count($matches[0]);
    }

    protected function countImages(string $html): int
    {
        preg_match_all('/<img[^>]*>/si', $html, $matches);
        return count($matches[0]);
    }

    protected function countLists(string $html): int
    {
        preg_match_all('/<(?:ul|ol)[^>]*>/i', $html, $matches);
        return count($matches[0]);
    }

    protected function countLinks(string $body): int
    {
        preg_match_all('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\']/i', $body, $matches);
        return count($matches[1] ?? []);
    }

    protected function countTables(string $html): int
    {
        preg_match_all('/<table[^>]*>/i', $html, $matches);
        return count($matches[0]);
    }
}
