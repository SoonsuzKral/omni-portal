<?php

namespace App\Services\QualityEngines;

use App\Models\ContentNode;
use App\Models\HumanizationScore;

class HumanizationEngine
{
    public function analyze(ContentNode $content): HumanizationScore
    {
        $body = strip_tags($content->body_content ?? '');
        $body = preg_replace('/\s+/', ' ', $body);

        $sentences = $this->extractSentences($body);
        $paragraphs = $this->extractParagraphsByNewline($body);

        $rhythmScore = $this->computeSentenceRhythm($sentences);
        $structureScore = $this->computeStructureVariation($sentences, $paragraphs);
        $paragraphScore = $this->computeParagraphDiversity($paragraphs);
        $narrativeScore = $this->computeNarrativeVariation($body, $sentences);
        $toneScore = $this->computeToneAdaptation($body);

        $overallScore = $this->aggregateHumanizationScore([
            'sentence_rhythm' => $rhythmScore,
            'structure_variation' => $structureScore,
            'paragraph_diversity' => $paragraphScore,
            'narrative_variation' => $narrativeScore,
            'tone_adaptation' => $toneScore,
        ]);

        $aiDetectionRisk = $this->computeAiDetectionRisk($overallScore, $sentences, $paragraphs);

        $humanization = HumanizationScore::updateOrCreate(
            ['content_node_id' => $content->id],
            [
                'sentence_rhythm_score' => $rhythmScore,
                'structure_variation_score' => $structureScore,
                'paragraph_diversity_score' => $paragraphScore,
                'narrative_variation_score' => $narrativeScore,
                'tone_adaptation_score' => $toneScore,
                'overall_humanization_score' => $overallScore,
                'analysis_details' => [
                    'sentence_count' => count($sentences),
                    'paragraph_count' => count($paragraphs),
                    'avg_sentence_length' => $this->averageLength($sentences),
                    'sentence_length_stddev' => $this->stddevLength($sentences),
                    'ai_detection_risk_score' => $aiDetectionRisk,
                    'ai_risk_factors' => $this->identifyAiRiskFactors($body, $sentences, $paragraphs),
                ],
            ]
        );

        $content->updateQuietly([
            'humanization_score' => $overallScore,
            'ai_detection_risk_score' => $aiDetectionRisk,
        ]);

        return $humanization;
    }

    protected function extractSentences(string $text): array
    {
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_map('trim', array_filter($sentences, fn($s) => strlen(trim($s)) > 5)));
    }

    protected function extractParagraphsByNewline(string $text): array
    {
        $paragraphs = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_map('trim', array_filter($paragraphs)));
    }

    protected function computeSentenceRhythm(array $sentences): float
    {
        if (count($sentences) < 5) {
            return 100.0;
        }

        $lengths = array_map('strlen', $sentences);
        $avg = array_sum($lengths) / count($lengths);
        $variance = 0;

        foreach ($lengths as $len) {
            $variance += pow($len - $avg, 2);
        }
        $variance /= count($lengths);
        $stddev = sqrt($variance);

        $cv = $avg > 0 ? $stddev / $avg : 0;

        $startTypes = [];
        foreach ($sentences as $sentence) {
            $firstWord = strtok($sentence, ' ');
            if ($firstWord) {
                $startTypes[] = $firstWord;
            }
        }

        $uniqueStarts = array_unique($startTypes);
        $startDiversity = count($startTypes) > 0 ? count($uniqueStarts) / count($startTypes) : 0;

        $rhythmScore = ($cv * 50) + ($startDiversity * 50);

        return round(min(100, $rhythmScore), 2);
    }

    protected function computeStructureVariation(array $sentences, array $paragraphs): float
    {
        if (count($sentences) < 3) {
            return 100.0;
        }

        $sentenceLengths = array_map('strlen', $sentences);

        $shortCount = count(array_filter($sentenceLengths, fn($l) => $l < 50));
        $mediumCount = count(array_filter($sentenceLengths, fn($l) => $l >= 50 && $l < 150));
        $longCount = count(array_filter($sentenceLengths, fn($l) => $l >= 150));

        $total = count($sentenceLengths);
        $shortRatio = $total > 0 ? $shortCount / $total : 0;
        $mediumRatio = $total > 0 ? $mediumCount / $total : 0;
        $longRatio = $total > 0 ? $longCount / $total : 0;

        $idealShort = 0.25;
        $idealMedium = 0.50;
        $idealLong = 0.25;

        $shortDiff = abs($shortRatio - $idealShort);
        $mediumDiff = abs($mediumRatio - $idealMedium);
        $longDiff = abs($longRatio - $idealLong);

        $variationScore = (1 - ($shortDiff + $mediumDiff + $longDiff) / 2) * 100;

        if (count($paragraphs) > 1) {
            $paraLengths = array_map(fn($p) => count($this->extractSentences($p)), $paragraphs);
            $paraAvg = count($paraLengths) > 0 ? array_sum($paraLengths) / count($paraLengths) : 0;
            $paraVariance = 0;
            foreach ($paraLengths as $pl) {
                $paraVariance += pow($pl - $paraAvg, 2);
            }
            $paraVariance /= max(1, count($paraLengths));
            $paraStddev = sqrt($paraVariance);

            $paraVariationBonus = min(20, ($paraAvg > 0 ? $paraStddev / $paraAvg * 50 : 0));
            $variationScore = min(100, $variationScore + $paraVariationBonus);
        }

        return round($variationScore, 2);
    }

    protected function computeParagraphDiversity(array $paragraphs): float
    {
        if (count($paragraphs) < 2) {
            return 100.0;
        }

        $wordCounts = array_map(fn($p) => str_word_count($p), $paragraphs);
        $avgWords = count($wordCounts) > 0 ? array_sum($wordCounts) / count($wordCounts) : 0;

        if ($avgWords === 0) {
            return 100.0;
        }

        $variance = 0;
        foreach ($wordCounts as $wc) {
            $variance += pow($wc - $avgWords, 2);
        }
        $variance /= count($wordCounts);
        $stddev = sqrt($variance);
        $cv = $stddev / $avgWords;

        $paragraphStarts = [];
        foreach ($paragraphs as $p) {
            $firstWords = explode(' ', trim($p));
            $key = implode(' ', array_slice($firstWords, 0, 3));
            $paragraphStarts[] = $key;
        }
        $uniqueStarts = array_unique($paragraphStarts);
        $startDiversity = count($uniqueStarts) / count($paragraphStarts);

        $diversityScore = ($cv * 40) + ($startDiversity * 60);

        return round(min(100, $diversityScore), 2);
    }

    protected function computeNarrativeVariation(string $body, array $sentences): float
    {
        if (count($sentences) < 5) {
            return 100.0;
        }

        $sentenceLengths = array_map('strlen', $sentences);
        $changes = 0;
        for ($i = 1; $i < count($sentenceLengths); $i++) {
            $diff = abs($sentenceLengths[$i] - $sentenceLengths[$i - 1]);
            if ($diff > 20) {
                $changes++;
            }
        }

        $transitionScore = count($sentences) > 1
            ? ($changes / (count($sentences) - 1)) * 100
            : 100;

        $transitionWords = [
            'however', 'moreover', 'furthermore', 'nevertheless', 'meanwhile',
            'therefore', 'thus', 'hence', 'consequently', 'additionally',
            'furthermore', 'besides', 'likewise', 'similarly', 'conversely',
            'alternatively', 'otherwise', 'specifically', 'particularly', 'notably',
            'importantly', 'significantly', 'primarily', 'essentially', 'ultimately',
            'first', 'second', 'third', 'finally', 'next', 'then', 'afterward',
            'subsequently', 'meanwhile', 'simultaneously', 'formerly', 'previously',
            'currently', 'presently', 'recently', 'historically', 'traditionally',
        ];

        $bodyLower = mb_strtolower($body);
        $transitionCount = 0;
        foreach ($transitionWords as $word) {
            $transitionCount += mb_substr_count($bodyLower, $word);
        }

        $transitionDensity = count($sentences) > 0 ? $transitionCount / count($sentences) : 0;
        $transitionBonus = min(20, $transitionDensity * 40);

        $narrativeScore = $transitionScore * 0.6 + $transitionBonus;

        return round(min(100, $narrativeScore), 2);
    }

    protected function computeToneAdaptation(string $body): float
    {
        $bodyLower = mb_strtolower($body);

        $formalPatterns = [
            'furthermore', 'nevertheless', 'consequently', 'heretofore',
            'thereupon', 'wherein', 'whereby', 'thus', 'hence',
        ];

        $informalPatterns = [
            'hey', 'wow', 'cool', 'awesome', 'literally', 'basically',
            'honestly', 'actually', 'pretty', 'quite', ' kinda', 'sorta',
            'gonna', 'wanna', 'gotta', 'super', 'totally', 'really',
        ];

        $conversationalPatterns = [
            'you might', 'you can', 'let\'s', 'imagine', 'picture this',
            'think about', 'have you', 'did you', 'ever wondered',
            'here\'s the thing', 'the truth is', 'the fact is',
        ];

        $formalCount = 0;
        foreach ($formalPatterns as $p) {
            $formalCount += mb_substr_count($bodyLower, $p);
        }

        $informalCount = 0;
        foreach ($informalPatterns as $p) {
            $informalCount += mb_substr_count($bodyLower, $p);
        }

        $conversationalCount = 0;
        foreach ($conversationalPatterns as $p) {
            $conversationalCount += mb_substr_count($bodyLower, $p);
        }

        $hasFormal = $formalCount > 2;
        $hasInformal = $informalCount > 2;
        $hasConversational = $conversationalCount > 1;

        $toneCount = 0;
        if ($hasFormal) $toneCount++;
        if ($hasInformal) $toneCount++;
        if ($hasConversational) $toneCount++;

        $toneScore = ($toneCount / 3) * 100;

        $questionCount = mb_substr_count($body, '?');
        $exclamationCount = mb_substr_count($body, '!');

        $punctuationBonus = min(15, ($questionCount + $exclamationCount) * 2);
        $toneScore = min(100, $toneScore + $punctuationBonus);

        return round($toneScore, 2);
    }

    protected function aggregateHumanizationScore(array $scores): float
    {
        $weights = [
            'sentence_rhythm' => 0.25,
            'structure_variation' => 0.20,
            'paragraph_diversity' => 0.20,
            'narrative_variation' => 0.20,
            'tone_adaptation' => 0.15,
        ];

        $weightedSum = 0;
        foreach ($scores as $key => $score) {
            $weightedSum += $score * ($weights[$key] ?? 0.2);
        }

        return round($weightedSum, 2);
    }

    protected function computeAiDetectionRisk(float $humanizationScore, array $sentences, array $paragraphs): float
    {
        $risk = 100 - $humanizationScore;

        if (count($sentences) > 3) {
            $lengths = array_map('strlen', $sentences);
            $avg = array_sum($lengths) / count($lengths);
            $variance = 0;
            foreach ($lengths as $len) {
                $variance += pow($len - $avg, 2);
            }
            $variance /= count($lengths);
            $stddev = sqrt($variance);

            $cv = $avg > 0 ? $stddev / $avg : 0;
            if ($cv < 0.3) {
                $risk += 15;
            } elseif ($cv > 0.8) {
                $risk -= 10;
            }
        }

        if (count($paragraphs) > 1) {
            $paraLengths = array_map(fn($p) => str_word_count($p), $paragraphs);
            $paraAvg = array_sum($paraLengths) / count($paraLengths);
            $paraVariance = 0;
            foreach ($paraLengths as $pl) {
                $paraVariance += pow($pl - $paraAvg, 2);
            }
            $paraVariance /= count($paraLengths);
            $paraStddev = sqrt($paraVariance);
            $paraCv = $paraAvg > 0 ? $paraStddev / $paraAvg : 0;

            if ($paraCv < 0.25) {
                $risk += 10;
            }
        }

        return round(min(100, max(0, $risk)), 2);
    }

    protected function identifyAiRiskFactors(string $body, array $sentences, array $paragraphs): array
    {
        $factors = [];

        if (count($sentences) > 3) {
            $lengths = array_map('strlen', $sentences);
            $avg = array_sum($lengths) / count($lengths);
            $variance = 0;
            foreach ($lengths as $len) {
                $variance += pow($len - $avg, 2);
            }
            $variance /= count($lengths);
            $stddev = sqrt($variance);
            $cv = $avg > 0 ? $stddev / $avg : 0;

            if ($cv < 0.3) {
                $factors[] = 'Uniform sentence length distribution (low variance)';
            }
        }

        $repeatedStarts = [];
        $starts = [];
        foreach ($sentences as $s) {
            $firstWord = strtok($s, ' ');
            if ($firstWord) {
                $starts[] = $firstWord;
            }
        }
        $startCounts = array_count_values($starts);
        foreach ($startCounts as $word => $count) {
            if ($count > 2) {
                $repeatedStarts[] = "{$word} ({$count}x)";
            }
        }
        if (!empty($repeatedStarts)) {
            $factors[] = 'Repetitive sentence starts: ' . implode(', ', array_slice($repeatedStarts, 0, 3));
        }

        $transitionWordsCount = 0;
        $commonTransitions = ['however', 'moreover', 'furthermore', 'therefore', 'additionally', 'consequently'];
        foreach ($commonTransitions as $tw) {
            $transitionWordsCount += mb_substr_count(mb_strtolower($body), $tw);
        }
        if ($transitionWordsCount > count($sentences) * 0.3) {
            $factors[] = 'High transition word density';
        }

        return $factors;
    }

    protected function averageLength(array $items): float
    {
        if (empty($items)) return 0;
        return array_sum(array_map('strlen', $items)) / count($items);
    }

    protected function stddevLength(array $items): float
    {
        if (count($items) < 2) return 0;
        $avg = $this->averageLength($items);
        $variance = 0;
        foreach ($items as $item) {
            $variance += pow(strlen($item) - $avg, 2);
        }
        $variance /= count($items);
        return sqrt($variance);
    }
}
