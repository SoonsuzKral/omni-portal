<?php

namespace App\Services\QualityEngines;

use App\Models\ContentNode;
use App\Models\SemanticUniquenessScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SemanticUniquenessEngine
{
    public function analyze(ContentNode $content): SemanticUniquenessScore
    {
        $body = strip_tags($content->body_content ?? '');
        $body = preg_replace('/\s+/', ' ', $body);

        $sentences = $this->extractSentences($body);
        $words = $this->extractWords($body);
        $headings = $this->extractHeadings($content->body_content ?? '');
        $paragraphs = $this->extractParagraphs($content->body_content ?? '');

        $similarityScore = $this->computeSemanticSimilarity($content, $body);
        $entropyScore = $this->computeSentenceEntropy($sentences);
        $lexicalScore = $this->computeLexicalDiversity($words);
        $templateScore = $this->computeTemplateSaturation($content, $paragraphs);
        $embeddingScore = $this->computeEmbeddingUniqueness($content, $body);
        $headingScore = $this->computeHeadingDuplication($content, $headings);

        $overallScore = $this->aggregateUniquenessScore([
            'semantic_similarity' => $similarityScore,
            'sentence_entropy' => $entropyScore,
            'lexical_diversity' => $lexicalScore,
            'template_saturation' => $templateScore,
            'embedding_uniqueness' => $embeddingScore,
            'heading_duplication' => $headingScore,
        ]);

        $similarPages = $this->detectNearDuplicates($content, $body);

        return SemanticUniquenessScore::updateOrCreate(
            ['content_node_id' => $content->id],
            [
                'semantic_similarity_score' => $similarityScore,
                'sentence_entropy_score' => $entropyScore,
                'lexical_diversity_score' => $lexicalScore,
                'template_saturation_score' => $templateScore,
                'embedding_uniqueness_score' => $embeddingScore,
                'heading_duplication_score' => $headingScore,
                'overall_uniqueness_score' => $overallScore,
                'similar_pages' => $similarPages,
                'analysis_details' => [
                    'sentence_count' => count($sentences),
                    'word_count' => count($words),
                    'heading_count' => count($headings),
                    'paragraph_count' => count($paragraphs),
                    'body_length' => strlen($body),
                ],
            ]
        );
    }

    protected function extractSentences(string $text): array
    {
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_map('trim', array_filter($sentences, fn($s) => strlen(trim($s)) > 10));
    }

    protected function extractWords(string $text): array
    {
        $words = str_word_count($text, 1);
        return array_map('strtolower', $words);
    }

    protected function extractHeadings(string $html): array
    {
        preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/si', $html, $matches);
        return array_map('strip_tags', $matches[1] ?? []);
    }

    protected function extractParagraphs(string $html): array
    {
        preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $html, $matches);
        return array_map('strip_tags', $matches[1] ?? []);
    }

    protected function computeSemanticSimilarity(ContentNode $content, string $body): float
    {
        $peers = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->limit(20)
            ->pluck('body_content');

        if ($peers->isEmpty()) {
            return 100.0;
        }

        $currentTokens = $this->tokenize($body);
        if (empty($currentTokens)) {
            return 100.0;
        }

        $maxSimilarity = 0;
        foreach ($peers as $peer) {
            $peerBody = strip_tags($peer);
            $peerTokens = $this->tokenize($peerBody);
            if (empty($peerTokens)) {
                continue;
            }

            $intersection = array_intersect($currentTokens, $peerTokens);
            $union = array_unique(array_merge($currentTokens, $peerTokens));
            $jaccard = count($union) > 0 ? count($intersection) / count($union) : 0;

            $maxSimilarity = max($maxSimilarity, $jaccard);
        }

        $threshold = config('quality-engine.semantic.similarity_threshold', 0.85);
        $score = (1 - $maxSimilarity) * 100;
        $score = max(0, min(100, $score));

        return round($score, 2);
    }

    protected function computeSentenceEntropy(array $sentences): float
    {
        if (count($sentences) < 2) {
            return 100.0;
        }

        $lengths = array_map('strlen', $sentences);
        $total = array_sum($lengths);
        if ($total === 0) {
            return 100.0;
        }

        $probabilities = array_map(fn($l) => $l / $total, $lengths);
        $entropy = 0;
        foreach ($probabilities as $p) {
            if ($p > 0) {
                $entropy -= $p * log($p);
            }
        }

        $maxEntropy = log(count($sentences));
        $normalized = $maxEntropy > 0 ? $entropy / $maxEntropy : 0;

        return round($normalized * 100, 2);
    }

    protected function computeLexicalDiversity(array $words): float
    {
        if (empty($words)) {
            return 100.0;
        }

        $uniqueWords = array_unique($words);
        $ratio = count($uniqueWords) / count($words);

        return round($ratio * 100, 2);
    }

    protected function computeTemplateSaturation(ContentNode $content, array $paragraphs): float
    {
        if (empty($paragraphs)) {
            return 100.0;
        }

        $peers = ContentNode::where('post_template_id', $content->post_template_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->limit(30)
            ->pluck('body_content');

        if ($peers->isEmpty()) {
            return 100.0;
        }

        $currentParagraphs = $paragraphs;
        $templatePatterns = [];

        foreach ($peers as $peer) {
            preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $peer, $m);
            $peerParagraphs = array_map('strip_tags', $m[1] ?? []);

            foreach ($peerParagraphs as $pp) {
                $fingerprint = $this->paragraphFingerprint($pp);
                if ($fingerprint) {
                    $templatePatterns[$fingerprint] = ($templatePatterns[$fingerprint] ?? 0) + 1;
                }
            }
        }

        if (empty($templatePatterns)) {
            return 100.0;
        }

        $saturatedCount = 0;
        foreach ($currentParagraphs as $cp) {
            $fp = $this->paragraphFingerprint($cp);
            if ($fp && isset($templatePatterns[$fp]) && $templatePatterns[$fp] > 2) {
                $saturatedCount++;
            }
        }

        $saturationRatio = count($currentParagraphs) > 0
            ? $saturatedCount / count($currentParagraphs)
            : 0;

        $score = (1 - $saturationRatio) * 100;

        return round($score, 2);
    }

    protected function paragraphFingerprint(string $paragraph): ?string
    {
        $clean = preg_replace('/[^\w\s]/u', '', $paragraph);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim(mb_strtolower($clean));

        if (strlen($clean) < 20) {
            return null;
        }

        $words = explode(' ', $clean);
        $words = array_slice($words, 0, 10);
        sort($words);

        return md5(implode('', $words));
    }

    protected function computeEmbeddingUniqueness(ContentNode $content, string $body): float
    {
        $peers = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->limit(50)
            ->pluck('body_content');

        if ($peers->isEmpty()) {
            return 100.0;
        }

        $currentFeatures = $this->extractFeatures($body);
        if (empty($currentFeatures)) {
            return 100.0;
        }

        $maxSimilarity = 0;
        foreach ($peers as $peer) {
            $peerFeatures = $this->extractFeatures(strip_tags($peer));
            if (empty($peerFeatures)) {
                continue;
            }

            $similarity = $this->cosineSimilarity($currentFeatures, $peerFeatures);
            $maxSimilarity = max($maxSimilarity, $similarity);
        }

        $score = (1 - $maxSimilarity) * 100;

        return round($score, 2);
    }

    protected function extractFeatures(string $text): array
    {
        $text = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($text));
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($w) => mb_strlen($w) > 2);
        $words = array_slice($words, 0, 500);

        $stopWords = ['the','a','an','in','on','at','to','for','of','by','with','and','or','is','are','was','were','be','been','being','have','has','had','do','does','did','will','would','could','should','may','might','shall','can','need','dare','ought','used','this','that','these','those','it','its','it\'s','i','you','he','she','we','they','me','him','her','us','them','my','your','his','its','our','their','mine','yours','his','hers','ours','theirs','not','no','nor','but','so','if','as','because','while','when','where','why','how','all','each','every','both','few','more','most','some','any','none','one','two','other','another','such','only','own','same','here','there','then','than','very','just','also','too','well','still','even','always','never','often','sometimes','usually','thus','hence','however','otherwise','nevertheless','meanwhile','therefore','furthermore','moreover','besides','indeed','certainly','definitely','absolutely','surely','undoubtedly'];
        $words = array_diff($words, $stopWords);
        $words = array_values($words);

        if (empty($words)) {
            return [];
        }

        $tf = array_count_values($words);
        $features = [];
        foreach (array_slice($tf, 0, 100) as $word => $count) {
            $features[md5($word)] = $count / count($words);
        }

        return $features;
    }

    protected function cosineSimilarity(array $vecA, array $vecB): float
    {
        $intersection = array_intersect_key($vecA, $vecB);
        if (empty($intersection)) {
            return 0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        foreach ($vecA as $key => $val) {
            $normA += $val * $val;
            if (isset($vecB[$key])) {
                $dotProduct += $val * $vecB[$key];
            }
        }

        foreach ($vecB as $val) {
            $normB += $val * $val;
        }

        $denom = sqrt($normA) * sqrt($normB);
        if ($denom === 0.0) {
            return 0;
        }

        return $dotProduct / $denom;
    }

    protected function computeHeadingDuplication(ContentNode $content, array $headings): float
    {
        if (empty($headings)) {
            return 100.0;
        }

        $peers = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->limit(20)
            ->pluck('body_content');

        if ($peers->isEmpty()) {
            return 100.0;
        }

        $peerHeadingSets = [];
        foreach ($peers as $peer) {
            preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/si', $peer, $m);
            $peerHeadingSets[] = array_map(fn($h) => trim(mb_strtolower(strip_tags($h))), $m[1] ?? []);
        }

        if (empty($peerHeadingSets)) {
            return 100.0;
        }

        $currentHeadings = array_map(fn($h) => trim(mb_strtolower(strip_tags($h))), $headings);
        $duplicateCount = 0;

        foreach ($currentHeadings as $ch) {
            foreach ($peerHeadingSets as $phs) {
                if (in_array($ch, $phs)) {
                    $duplicateCount++;
                    break;
                }
            }
        }

        $duplicationRatio = count($currentHeadings) > 0
            ? $duplicateCount / count($currentHeadings)
            : 0;

        $score = (1 - $duplicationRatio) * 100;

        return round($score, 2);
    }

    protected function aggregateUniquenessScore(array $scores): float
    {
        $weights = [
            'semantic_similarity' => 0.25,
            'sentence_entropy' => 0.15,
            'lexical_diversity' => 0.20,
            'template_saturation' => 0.15,
            'embedding_uniqueness' => 0.15,
            'heading_duplication' => 0.10,
        ];

        $weightedSum = 0;
        foreach ($scores as $key => $score) {
            $weightedSum += $score * ($weights[$key] ?? 0.1);
        }

        return round($weightedSum, 2);
    }

    protected function tokenize(string $text): array
    {
        $text = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($text));
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($w) => mb_strlen($w) > 2);

        $stopWords = ['the','a','an','in','on','at','to','for','of','by','with','and','or','is','are','was','were','be','been','being','have','has','had','do','does','did','will','would','could','should','may','might','shall','can','need','dare','ought','used','this','that','these','those','it','its','it\'s','i','you','he','she','we','they','me','him','her','us','them','my','your','his','its','our','their','mine','yours','his','hers','ours','theirs','not','no','nor','but','so','if','as','because','while','when','where','why','how','all','each','every','both','few','more','most','some','any','none','one','two','other','another','such','only','own','same','here','there','then','than','very','just','also','too','well','still','even','always','never','often','sometimes','usually','thus','hence','however','otherwise','nevertheless','meanwhile','therefore','furthermore','moreover','besides','indeed','certainly','definitely','absolutely','surely','undoubtedly'];
        $words = array_diff($words, $stopWords);

        return array_slice(array_values($words), 0, 200);
    }

    protected function detectNearDuplicates(ContentNode $content, string $body): array
    {
        $similarPages = [];

        $candidates = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('body_content')
            ->limit(50)
            ->get(['id', 'seo_title', 'slug', 'body_content']);

        $currentTokens = $this->tokenize($body);
        if (empty($currentTokens)) {
            return [];
        }

        $threshold = config('quality-engine.semantic.similarity_threshold', 0.85);

        foreach ($candidates as $candidate) {
            $candidateBody = strip_tags($candidate->body_content ?? '');
            $candidateTokens = $this->tokenize($candidateBody);
            if (empty($candidateTokens)) {
                continue;
            }

            $intersection = array_intersect($currentTokens, $candidateTokens);
            $union = array_unique(array_merge($currentTokens, $candidateTokens));
            $jaccard = count($union) > 0 ? count($intersection) / count($union) : 0;

            if ($jaccard >= $threshold) {
                $similarPages[] = [
                    'id' => $candidate->id,
                    'title' => $candidate->seo_title,
                    'slug' => $candidate->slug,
                    'similarity' => round($jaccard * 100, 2),
                ];
            }
        }

        usort($similarPages, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($similarPages, 0, 10);
    }
}
