<?php

namespace App\Services\QualityEngines;

use App\Models\AntiSpamRiskScore;
use App\Models\ContentNode;
use Illuminate\Support\Str;

class AntiSpamRiskEngine
{
    public function analyze(ContentNode $content): AntiSpamRiskScore
    {
        $body = $content->body_content ?? '';
        $plainText = strip_tags($body);
        $words = $this->extractWords($plainText);

        $abuseScore = $this->detectScaledContentAbuse($content);
        $templateOveruse = $this->detectTemplateOveruse($content, $plainText);
        $redundancyScore = $this->detectSemanticRedundancy($content, $plainText);
        $doorwayScore = $this->detectDoorwayPageRisk($content, $plainText);
        $thinScore = $this->detectThinContent($content, $plainText);
        $optimizationScore = $this->detectOverOptimization($content, $plainText);

        $overallScore = $this->aggregateSpamRiskScore([
            'scaled_abuse' => $abuseScore,
            'template_overuse' => $templateOveruse,
            'semantic_redundancy' => $redundancyScore,
            'doorway_risk' => $doorwayScore,
            'thin_content' => $thinScore,
            'over_optimization' => $optimizationScore,
        ]);

        $riskFactors = $this->identifyRiskFactors($content, $plainText, [
            'abuse' => $abuseScore,
            'template' => $templateOveruse,
            'redundancy' => $redundancyScore,
            'doorway' => $doorwayScore,
            'thin' => $thinScore,
            'optimization' => $optimizationScore,
        ]);

        $score = AntiSpamRiskScore::updateOrCreate(
            ['content_node_id' => $content->id],
            [
                'scaled_content_abuse_score' => $abuseScore,
                'template_overuse_score' => $templateOveruse,
                'semantic_redundancy_score' => $redundancyScore,
                'doorway_page_risk_score' => $doorwayScore,
                'thin_content_risk_score' => $thinScore,
                'over_optimization_score' => $optimizationScore,
                'overall_spam_risk_score' => $overallScore,
                'risk_factors' => $riskFactors,
                'analysis_details' => [
                    'word_count' => count($words),
                    'has_template' => $content->post_template_id !== null,
                    'taxonomy_id' => $content->taxonomy_id,
                    'location_id' => $content->location_id,
                    'heading_count' => $this->countHeadings($body),
                    'link_density' => $this->computeLinkDensity($body, $plainText),
                ],
            ]
        );

        $content->updateQuietly([
            'spam_risk_score' => $overallScore,
            'doorway_risk_score' => $doorwayScore,
        ]);

        return $score;
    }

    protected function extractWords(string $text): array
    {
        $words = str_word_count($text, 1);
        return array_map('mb_strtolower', $words);
    }

    protected function detectScaledContentAbuse(ContentNode $content): float
    {
        $score = 0;

        $peerCount = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('post_template_id', $content->post_template_id)
            ->count();

        if ($peerCount > 100) {
            $score += 40;
        } elseif ($peerCount > 50) {
            $score += 25;
        } elseif ($peerCount > 20) {
            $score += 10;
        }

        if ($content->post_template_id) {
            $templateUsage = ContentNode::where('post_template_id', $content->post_template_id)->count();
            if ($templateUsage > 200) {
                $score += 30;
            } elseif ($templateUsage > 100) {
                $score += 20;
            } elseif ($templateUsage > 50) {
                $score += 10;
            }
        }

        $locationsUsingTemplate = ContentNode::where('post_template_id', $content->post_template_id)
            ->distinct('location_id')
            ->count('location_id');

        if ($locationsUsingTemplate > 50) {
            $score += 20;
        } elseif ($locationsUsingTemplate > 20) {
            $score += 10;
        }

        return round(min(100, $score), 2);
    }

    protected function detectTemplateOveruse(ContentNode $content, string $plainText): float
    {
        $score = 0;

        if (!$content->post_template_id) {
            return 0;
        }

        $siblings = ContentNode::where('post_template_id', $content->post_template_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->limit(20)
            ->pluck('body_content');

        if ($siblings->isEmpty()) {
            return 0;
        }

        $currentParagraphs = $this->extractParagraphs($content->body_content ?? '');

        $paragraphSignatures = [];
        foreach ($siblings as $sibling) {
            $siblingParagraphs = $this->extractParagraphs($sibling);
            foreach ($siblingParagraphs as $sp) {
                $sig = $this->paragraphSignature($sp);
                if ($sig) {
                    $paragraphSignatures[$sig] = ($paragraphSignatures[$sig] ?? 0) + 1;
                }
            }
        }

        if (empty($paragraphSignatures)) {
            return 0;
        }

        $matchedCount = 0;
        foreach ($currentParagraphs as $cp) {
            $sig = $this->paragraphSignature($cp);
            if ($sig && isset($paragraphSignatures[$sig]) && $paragraphSignatures[$sig] >= 3) {
                $matchedCount++;
            }
        }

        $matchRatio = count($currentParagraphs) > 0 ? $matchedCount / count($currentParagraphs) : 0;
        $threshold = config('quality-engine.spam.template_overuse_threshold', 0.7);

        if ($matchRatio >= $threshold) {
            $score = min(100, ($matchRatio / $threshold) * 100);
        }

        $totalTemplateUsage = ContentNode::where('post_template_id', $content->post_template_id)->count();
        if ($totalTemplateUsage > 500) {
            $score += 15;
        } elseif ($totalTemplateUsage > 200) {
            $score += 10;
        }

        return round(min(100, $score), 2);
    }

    protected function paragraphSignature(string $paragraph): ?string
    {
        $clean = preg_replace('/[^\w\s]/u', '', $paragraph);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim(mb_strtolower($clean));

        if (strlen($clean) < 30) {
            return null;
        }

        $words = explode(' ', $clean);
        $words = array_slice($words, 0, 8);
        sort($words);

        return md5(implode('', $words));
    }

    protected function extractParagraphs(string $html): array
    {
        preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $html, $matches);
        return array_map('strip_tags', $matches[1] ?? []);
    }

    protected function detectSemanticRedundancy(ContentNode $content, string $plainText): float
    {
        $score = 0;
        $words = $this->extractWords($plainText);

        if (count($words) < 20) {
            return 0;
        }

        $wordFreq = array_count_values($words);
        $totalWords = count($words);
        $uniqueWords = count($wordFreq);

        $typeTokenRatio = $totalWords > 0 ? $uniqueWords / $totalWords : 1;
        if ($typeTokenRatio < 0.3) {
            $score += 30;
        } elseif ($typeTokenRatio < 0.4) {
            $score += 15;
        } elseif ($typeTokenRatio < 0.5) {
            $score += 5;
        }

        $maxFreq = max($wordFreq);
        $maxFreqRatio = $totalWords > 0 ? $maxFreq / $totalWords : 0;
        if ($maxFreqRatio > 0.15) {
            $score += 20;
        } elseif ($maxFreqRatio > 0.10) {
            $score += 10;
        }

        $sentences = preg_split('/[.!?]+/', $plainText, -1, PREG_SPLIT_NO_EMPTY);
        if (count($sentences) > 2) {
            $similarPairs = 0;
            $totalPairs = 0;
            for ($i = 0; $i < count($sentences); $i++) {
                for ($j = $i + 1; $j < count($sentences); $j++) {
                    $totalPairs++;
                    $s1 = explode(' ', trim($sentences[$i]));
                    $s2 = explode(' ', trim($sentences[$j]));
                    $common = array_intersect($s1, $s2);
                    $union = array_unique(array_merge($s1, $s2));
                    $jaccard = count($union) > 0 ? count($common) / count($union) : 0;
                    if ($jaccard > 0.7) {
                        $similarPairs++;
                    }
                }
            }

            $redundancyRatio = $totalPairs > 0 ? $similarPairs / $totalPairs : 0;
            $threshold = config('quality-engine.spam.semantic_redundancy_threshold', 0.8);
            if ($redundancyRatio >= $threshold) {
                $score += 25;
            } elseif ($redundancyRatio > $threshold * 0.7) {
                $score += 10;
            }
        }

        $bodyLower = mb_strtolower($plainText);
        $repeatedPhrases = ['click here', 'read more', 'learn more', 'find out more',
            'get started', 'sign up', 'contact us', 'call now', 'buy now',
            'limited time', 'act now', 'don\'t miss', 'exclusive offer',
        ];
        $phraseHits = 0;
        foreach ($repeatedPhrases as $phrase) {
            $phraseHits += mb_substr_count($bodyLower, $phrase);
        }
        if ($phraseHits > 3) {
            $score += 15;
        }

        return round(min(100, $score), 2);
    }

    protected function detectDoorwayPageRisk(ContentNode $content, string $plainText): float
    {
        $score = 0;
        $bodyLower = mb_strtolower($plainText);

        if ($content->location_id && $content->taxonomy_id) {
            $similarCount = ContentNode::where('taxonomy_id', $content->taxonomy_id)
                ->where('location_id', '!=', $content->location_id)
                ->count();

            if ($similarCount > 200) {
                $score += 20;
            } elseif ($similarCount > 100) {
                $score += 10;
            }

            $sameLocationCount = ContentNode::where('location_id', $content->location_id)
                ->where('taxonomy_id', '!=', $content->taxonomy_id)
                ->count();
            if ($sameLocationCount > 200) {
                $score += 15;
            }
        }

        $wordCount = str_word_count($plainText);
        if ($wordCount < 200) {
            $score += 25;
        } elseif ($wordCount < 400) {
            $score += 10;
        }

        $thinThreshold = config('quality-engine.spam.thin_content_words', 300);
        if ($wordCount < $thinThreshold) {
            $score += 15;
        }

        $keywordOverlap = $this->computeKeywordOverlap($content, $plainText);
        $doorwayThreshold = config('quality-engine.spam.doorway_keyword_overlap', 0.6);
        if ($keywordOverlap >= $doorwayThreshold) {
            $score += 20;
        }

        $locationMentionedInBody = $content->location
            ? mb_strpos($bodyLower, mb_strtolower($content->location->name)) !== false
            : false;

        if ($content->location_id && !$locationMentionedInBody) {
            $score += 25;
        }

        $titles = [
            'best', 'top', 'cheap', 'affordable', 'professional',
            'quality', 'expert', 'service', 'near me', 'in ',
        ];
        $titleLower = mb_strtolower($content->seo_title ?? '');
        $titleKeywordCount = 0;
        foreach ($titles as $t) {
            if (mb_strpos($titleLower, $t) !== false) {
                $titleKeywordCount++;
            }
        }
        if ($titleKeywordCount >= 3) {
            $score += 10;
        }

        return round(min(100, $score), 2);
    }

    protected function computeKeywordOverlap(ContentNode $content, string $plainText): float
    {
        $taxonomyName = $content->taxonomy?->name ?? '';
        $locationName = $content->location?->name ?? '';
        $title = $content->seo_title ?? '';

        if (empty($taxonomyName) && empty($locationName)) {
            return 0;
        }

        $bodyLower = mb_strtolower($plainText);
        $overlapCount = 0;
        $totalKeywords = 0;

        $keywords = array_unique(array_merge(
            explode(' ', mb_strtolower($taxonomyName)),
            explode(' ', mb_strtolower($locationName)),
            explode(' ', mb_strtolower($title))
        ));

        $keywords = array_filter($keywords, fn($k) => strlen($k) > 2);

        foreach ($keywords as $keyword) {
            $totalKeywords++;
            if (mb_strpos($bodyLower, $keyword) !== false) {
                $overlapCount++;
            }
        }

        return $totalKeywords > 0 ? $overlapCount / $totalKeywords : 0;
    }

    protected function detectThinContent(ContentNode $content, string $plainText): float
    {
        $score = 0;
        $wordCount = str_word_count($plainText);
        $thinThreshold = config('quality-engine.spam.thin_content_words', 300);

        if ($wordCount < $thinThreshold) {
            $score += 40;
        } elseif ($wordCount < $thinThreshold * 1.5) {
            $score += 20;
        } elseif ($wordCount < $thinThreshold * 2) {
            $score += 10;
        }

        $headings = $this->countHeadings($content->body_content ?? '');
        if ($headings === 0) {
            $score += 15;
        } elseif ($headings === 1) {
            $score += 5;
        }

        $images = $this->countImages($content->body_content ?? '');
        if ($images === 0 && $wordCount > 0) {
            $score += 10;
        }

        $lists = $this->countLists($content->body_content ?? '');
        if ($lists === 0) {
            $score += 5;
        }

        $links = $this->countLinks($content->body_content ?? '');
        if ($links === 0) {
            $score += 5;
        }

        return round(min(100, $score), 2);
    }

    protected function detectOverOptimization(ContentNode $content, string $plainText): float
    {
        $score = 0;
        $words = $this->extractWords($plainText);

        if (count($words) < 50) {
            return 0;
        }

        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        $topWords = array_slice($wordFreq, 0, 10);

        $bodyLower = mb_strtolower($plainText);
        $densitySum = 0;
        $densityCount = 0;

        foreach ($topWords as $word => $count) {
            if (strlen($word) < 3) continue;
            $density = $count / count($words);
            $densitySum += $density;
            $densityCount++;
        }

        $avgDensity = $densityCount > 0 ? $densitySum / $densityCount : 0;
        $threshold = config('quality-engine.spam.over_optimization_density', 0.05);

        if ($avgDensity > $threshold * 2) {
            $score += 30;
        } elseif ($avgDensity > $threshold) {
            $score += 15;
        }

        $titleLower = mb_strtolower($content->seo_title ?? '');
        $titleWords = explode(' ', $titleLower);
        $titleWordCount = 0;
        $titleInBody = 0;
        foreach ($titleWords as $tw) {
            if (strlen($tw) < 3) continue;
            $titleWordCount++;
            if (mb_strpos($bodyLower, $tw) !== false) {
                $titleInBody++;
            }
        }

        if ($titleWordCount > 0) {
            $titleDensity = $titleInBody / $titleWordCount;
            if ($titleDensity > 0.8) {
                $score += 15;
            }
        }

        $seoKeywords = [
            'best', 'top', 'review', 'guide', 'ultimate', 'comprehensive',
            'complete', 'essential', 'expert', 'professional', 'premium',
            'quality', 'affordable', 'cheap', 'discount', 'deal', 'offer',
            'free', 'guaranteed', 'results', 'proven', 'effective',
        ];

        $seoWordCount = 0;
        foreach ($seoKeywords as $sk) {
            $seoWordCount += mb_substr_count($bodyLower, $sk);
        }

        $seoDensity = count($words) > 0 ? $seoWordCount / count($words) : 0;
        if ($seoDensity > 0.08) {
            $score += 20;
        } elseif ($seoDensity > 0.05) {
            $score += 10;
        }

        if ($content->meta_description) {
            $metaLength = strlen($content->meta_description);
            if ($metaLength > 170) {
                $score += 5;
            }
        }

        return round(min(100, $score), 2);
    }

    protected function aggregateSpamRiskScore(array $scores): float
    {
        $weights = [
            'scaled_abuse' => 0.20,
            'template_overuse' => 0.20,
            'semantic_redundancy' => 0.15,
            'doorway_risk' => 0.20,
            'thin_content' => 0.15,
            'over_optimization' => 0.10,
        ];

        $weightedSum = 0;
        foreach ($scores as $key => $score) {
            $weightedSum += $score * ($weights[$key] ?? 0.15);
        }

        return round($weightedSum, 2);
    }

    protected function identifyRiskFactors(ContentNode $content, string $plainText, array $scores): array
    {
        $factors = [];

        if ($scores['abuse'] >= 50) {
            $factors[] = 'High scaled content abuse risk (many peers sharing same template/location pattern)';
        }
        if ($scores['template'] >= 50) {
            $factors[] = 'Significant template overuse detected (paragraphs match template patterns)';
        }
        if ($scores['redundancy'] >= 50) {
            $factors[] = 'High semantic redundancy (low lexical diversity, repetitive content)';
        }
        if ($scores['doorway'] >= 50) {
            $factors[] = 'Doorway page pattern detected (thin location-targeted content)';

            if ($content->location && !mb_strpos(mb_strtolower($plainText), mb_strtolower($content->location->name))) {
                $factors[] = 'Location in metadata but not mentioned in body content';
            }
        }
        if ($scores['thin'] >= 40) {
            $wordCount = str_word_count($plainText);
            $factors[] = "Thin content ({$wordCount} words)";
        }
        if ($scores['optimization'] >= 40) {
            $factors[] = 'Over-optimization detected (high keyword density / SEO keyword stuffing)';
        }

        return $factors;
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

    protected function computeLinkDensity(string $html, string $plainText): float
    {
        $linkCount = $this->countLinks($html);
        $wordCount = str_word_count($plainText);
        return $wordCount > 0 ? round($linkCount / $wordCount, 4) : 0;
    }
}
