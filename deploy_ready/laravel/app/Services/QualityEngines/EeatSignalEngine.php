<?php

namespace App\Services\QualityEngines;

use App\Models\ContentNode;
use App\Models\EeatSignal;
use Illuminate\Support\Facades\DB;

class EeatSignalEngine
{
    public function analyze(ContentNode $content): array
    {
        $signals = [];

        $signals['author_expertise'] = $this->assessAuthorExpertise($content);
        $signals['editorial_review'] = $this->assessEditorialReview($content);
        $signals['citation_quality'] = $this->assessCitationQuality($content);
        $signals['source_trust'] = $this->assessSourceTrust($content);
        $signals['factual_confidence'] = $this->assessFactualConfidence($content);
        $signals['content_freshness'] = $this->assessContentFreshness($content);

        foreach ($signals as $type => $data) {
            EeatSignal::updateOrCreate(
                ['content_node_id' => $content->id, 'signal_type' => $type],
                [
                    'signal_score' => $data['score'],
                    'signal_evidence' => $data['evidence'],
                    'signal_details' => $data['details'] ?? null,
                ]
            );
        }

        $aggregateScores = $this->aggregateEeatScores($signals);

        $content->updateQuietly([
            'eeat_score' => $aggregateScores['eeat_score'],
            'trust_score' => $aggregateScores['trust_score'],
            'expertise_score' => $aggregateScores['expertise_score'],
        ]);

        return [
            'signals' => $signals,
            'aggregate' => $aggregateScores,
        ];
    }

    protected function assessAuthorExpertise(ContentNode $content): array
    {
        $score = 50;
        $evidence = [];
        $details = [];

        if ($content->user_id ?? false) {
            $score += 20;
            $evidence[] = 'Has associated author';
        }

        $body = strip_tags($content->body_content ?? '');

        $expertiseKeywords = ['expert', 'specialist', 'professional', 'certified', 'licensed',
            'experienced', 'qualified', 'accredited', 'authority', 'recognized',
            'published', 'research', 'study', 'analysis', 'in-depth',
            'comprehensive', 'thorough', 'extensive', 'detailed', 'definitive'];

        $foundKeywords = [];
        foreach ($expertiseKeywords as $keyword) {
            if (mb_stripos($body, $keyword) !== false) {
                $foundKeywords[] = $keyword;
            }
        }

        $keywordBoost = min(20, count($foundKeywords) * 3);
        $score += $keywordBoost;
        if (!empty($foundKeywords)) {
            $evidence[] = 'Contains expertise signals: ' . implode(', ', array_slice($foundKeywords, 0, 5));
            $details['found_keywords'] = $foundKeywords;
        }

        $wordCount = str_word_count($body);
        if ($wordCount > 1500) {
            $score += 10;
            $evidence[] = 'Comprehensive content length';
        } elseif ($wordCount > 800) {
            $score += 5;
            $evidence[] = 'Adequate content depth';
        }

        if ($wordCount > 0) {
            $details['word_count'] = $wordCount;
        }

        $score = min(100, max(0, $score));

        return [
            'score' => round($score, 2),
            'evidence' => implode('; ', $evidence),
            'details' => $details,
        ];
    }

    protected function assessEditorialReview(ContentNode $content): array
    {
        $score = 40;
        $evidence = [];
        $details = [];

        $hasReview = $content->editorial_reviewed_at ?? false;
        $reviewerId = $content->reviewed_by ?? false;

        if ($hasReview) {
            $score += 30;
            $evidence[] = 'Content has been editorially reviewed';
            $details['reviewed_at'] = $content->editorial_reviewed_at;
        }

        if ($reviewerId) {
            $score += 20;
            $evidence[] = 'Reviewer assigned';
            $details['reviewer_id'] = $reviewerId;
        }

        $body = strip_tags($content->body_content ?? '');

        if (preg_match('/\b(disclaimer|editor\'s note|fact-checked|verified|sources|references|updated|reviewed)\b/i', $body)) {
            $score += 10;
            $evidence[] = 'Contains editorial markers';
        }

        if ($content->updated_at && $content->created_at) {
            $revisions = $content->updated_at->gt($content->created_at);
            if ($revisions) {
                $score += 10;
                $evidence[] = 'Content has been updated (revision history)';
                $details['last_updated'] = $content->updated_at;
            }
        }

        $score = min(100, max(0, $score));

        return [
            'score' => round($score, 2),
            'evidence' => implode('; ', $evidence),
            'details' => $details,
        ];
    }

    protected function assessCitationQuality(ContentNode $content): array
    {
        $score = 30;
        $evidence = [];
        $details = [];
        $body = $content->body_content ?? '';

        preg_match_all('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $body, $links);
        $externalLinks = array_filter($links[1], fn($url) => !str_contains($url, parse_url(config('app.url'), PHP_URL_HOST)));

        $citationCount = count($externalLinks);
        $details['external_link_count'] = $citationCount;

        if ($citationCount >= 5) {
            $score += 35;
            $evidence[] = "Excellent citation count ({$citationCount} external references)";
        } elseif ($citationCount >= 3) {
            $score += 25;
            $evidence[] = "Good citation count ({$citationCount} external references)";
        } elseif ($citationCount >= 1) {
            $score += 10;
            $evidence[] = "Has external references ({$citationCount})";
        } else {
            $evidence[] = 'No external citations found';
        }

        $highTrustDomains = [
            '.gov', '.edu', '.org', 'wikipedia.org', 'who.int', 'un.org',
            'scholar.google.com', 'pubmed.ncbi.nlm.nih.gov', 'doi.org',
            'researchgate.net', 'sciencedirect.com', 'springer.com',
        ];

        $trustedDomainsCount = 0;
        foreach ($externalLinks as $link) {
            foreach ($highTrustDomains as $domain) {
                if (str_contains($link, $domain)) {
                    $trustedDomainsCount++;
                    break;
                }
            }
        }

        $details['trusted_domain_count'] = $trustedDomainsCount;

        if ($trustedDomainsCount > 0) {
            $trustBonus = min(25, $trustedDomainsCount * 8);
            $score += $trustBonus;
            $evidence[] = "{$trustedDomainsCount} citations from trusted domains";
        }

        preg_match_all('/<blockquote[^>]*>/i', $body, $blockquotes);
        $blockquoteCount = count($blockquotes[0]);
        $details['blockquote_count'] = $blockquoteCount;

        if ($blockquoteCount > 0) {
            $score += 5;
            $evidence[] = 'Uses blockquote citations';
        }

        preg_match('/\b(according to|source|reference|citation|study found|research shows|data suggests)\b/i', $body, $citationPhrases);
        if (!empty($citationPhrases)) {
            $score += 10;
            $evidence[] = 'Uses attribution phrases';
        }

        $score = min(100, max(0, $score));

        return [
            'score' => round($score, 2),
            'evidence' => implode('; ', $evidence),
            'details' => $details,
        ];
    }

    protected function assessSourceTrust(ContentNode $content): array
    {
        $score = 50;
        $evidence = [];
        $details = [];
        $body = $content->body_content ?? '';

        preg_match_all('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $body, $links);
        $allLinks = $links[1] ?? [];

        $externalLinks = array_filter($allLinks, fn($url) => !str_contains($url, parse_url(config('app.url'), PHP_URL_HOST)));
        $details['total_external_links'] = count($externalLinks);

        $lowTrustPatterns = ['spam', 'click here', 'buy now', 'casino', 'payday', 'viagra', 'crypto'];
        $lowTrustCount = 0;
        foreach ($externalLinks as $link) {
            foreach ($lowTrustPatterns as $pattern) {
                if (str_contains(mb_strtolower($link), $pattern)) {
                    $lowTrustCount++;
                    break;
                }
            }
        }

        if ($lowTrustCount > 0) {
            $penalty = min(30, $lowTrustCount * 10);
            $score -= $penalty;
            $evidence[] = "{$lowTrustCount} links to low-trust domains";
            $details['low_trust_links'] = $lowTrustCount;
        }

        if ($content->gsc_total_impressions > 1000 && $content->gsc_avg_position < 20) {
            $score += 15;
            $evidence[] = 'Google-validated content (high impressions, good position)';
            $details['gsc_avg_position'] = $content->gsc_avg_position;
        }

        if ($content->page_views > 1000) {
            $score += 10;
            $evidence[] = 'High page views indicating user trust';
            $details['page_views'] = $content->page_views;
        }

        if ($content->gsc_index_status === 'INDEXED') {
            $score += 15;
            $evidence[] = 'Indexed by Google (editorial validation)';
        }

        $score = min(100, max(0, $score));

        return [
            'score' => round($score, 2),
            'evidence' => implode('; ', $evidence),
            'details' => $details,
        ];
    }

    protected function assessFactualConfidence(ContentNode $content): array
    {
        $score = 40;
        $evidence = [];
        $details = [];
        $body = strip_tags($content->body_content ?? '');

        $dataPatterns = [
            'number' => '/\b\d+[.,]?\d*%?\b/',
            'date' => '/\b\d{4}\b/',
            'stat' => '/\b(statistics|data|percent|rate|ratio|average|median|total)\b/i',
            'comparison' => '/\b(compared|versus|vs\.|higher than|lower than|more than|less than)\b/i',
            'specificity' => '/\b(specifically|precisely|exactly|approximately|roughly|estimated)\b/i',
        ];

        $foundPatterns = [];
        $patternCount = 0;
        foreach ($dataPatterns as $key => $pattern) {
            preg_match_all($pattern, $body, $matches);
            $count = count($matches[0]);
            if ($count > 0) {
                $foundPatterns[$key] = $count;
                $patternCount += $count;
            }
        }

        $details['data_patterns'] = $foundPatterns;

        if ($patternCount > 20) {
            $score += 30;
            $evidence[] = 'Rich with data points and specific claims';
        } elseif ($patternCount > 10) {
            $score += 20;
            $evidence[] = 'Contains substantial data and specifics';
        } elseif ($patternCount > 5) {
            $score += 10;
            $evidence[] = 'Some data points present';
        } else {
            $evidence[] = 'Few data points or specific claims';
        }

        if (preg_match('/\b(according to|research|study|survey|report|analysis|findings|conclusion)\b/i', $body)) {
            $score += 15;
            $evidence[] = 'References authoritative sources';
        }

        $hedgeWords = ['might', 'maybe', 'perhaps', 'possibly', 'could be', 'seems', 'appears', 'likely', 'probably', 'suggests'];
        $hedgeCount = 0;
        foreach ($hedgeWords as $word) {
            $hedgeCount += mb_substr_count(mb_strtolower($body), $word);
        }
        $details['hedge_word_count'] = $hedgeCount;

        if ($hedgeCount > 5) {
            $score -= 10;
            $evidence[] = 'High uncertainty language detected';
        }

        $score = min(100, max(0, $score));

        return [
            'score' => round($score, 2),
            'evidence' => implode('; ', $evidence),
            'details' => $details,
        ];
    }

    protected function assessContentFreshness(ContentNode $content): array
    {
        $score = 30;
        $evidence = [];
        $details = [];

        if (!$content->publish_date) {
            return [
                'score' => 0,
                'evidence' => 'No publish date available',
                'details' => ['published' => false],
            ];
        }

        $daysSincePublish = $content->publish_date->diffInDays(now());
        $daysSinceUpdate = $content->updated_at ? $content->updated_at->diffInDays(now()) : $daysSincePublish;

        $details['days_since_publish'] = $daysSincePublish;
        $details['days_since_update'] = $daysSinceUpdate;

        if ($daysSincePublish <= 7) {
            $score = 95;
            $evidence[] = 'Published within the last week';
        } elseif ($daysSincePublish <= 30) {
            $score = 85;
            $evidence[] = 'Published within the last month';
        } elseif ($daysSincePublish <= 90) {
            $score = 70;
            $evidence[] = 'Published within the last quarter';
        } elseif ($daysSincePublish <= 180) {
            $score = 50;
            $evidence[] = 'Published within the last 6 months';
        } elseif ($daysSincePublish <= 365) {
            $score = 30;
            $evidence[] = 'Published within the last year';
        } else {
            $score = 15;
            $evidence[] = 'Published over a year ago';
        }

        if ($daysSinceUpdate < $daysSincePublish) {
            $updateBonus = max(0, 20 - ($daysSinceUpdate / 30));
            $score += $updateBonus;
            $evidence[] = 'Recently updated';
            $details['update_bonus'] = round($updateBonus, 2);
        }

        if ($content->gsc_last_impression_at) {
            $daysSinceImpression = $content->gsc_last_impression_at->diffInDays(now());
            if ($daysSinceImpression <= 7) {
                $score += 10;
                $evidence[] = 'Recent Google impressions (active in index)';
            }
        }

        $score = min(100, max(0, $score));

        return [
            'score' => round($score, 2),
            'evidence' => implode('; ', $evidence),
            'details' => $details,
        ];
    }

    protected function aggregateEeatScores(array $signals): array
    {
        $expertiseWeight = config('quality-engine.eeat.expertise_weight', 0.30);
        $trustWeight = config('quality-engine.eeat.trust_weight', 0.35);
        $freshnessWeight = config('quality-engine.eeat.freshness_weight', 0.20);
        $citationWeight = config('quality-engine.eeat.citation_weight', 0.15);

        $authorScore = $signals['author_expertise']['score'] ?? 50;
        $editorialScore = $signals['editorial_review']['score'] ?? 50;
        $citationScore = $signals['citation_quality']['score'] ?? 50;
        $sourceTrustScore = $signals['source_trust']['score'] ?? 50;
        $factualScore = $signals['factual_confidence']['score'] ?? 50;
        $freshnessScore = $signals['content_freshness']['score'] ?? 50;

        $expertiseScore = ($authorScore * 0.6) + ($editorialScore * 0.25) + ($factualScore * 0.15);
        $trustScore = ($sourceTrustScore * 0.5) + ($citationScore * 0.3) + ($editorialScore * 0.2);
        $eeatScore = ($expertiseScore * $expertiseWeight)
            + ($trustScore * $trustWeight)
            + ($freshnessScore * $freshnessWeight)
            + ($citationScore * $citationWeight);

        return [
            'eeat_score' => round($eeatScore, 2),
            'trust_score' => round($trustScore, 2),
            'expertise_score' => round($expertiseScore, 2),
        ];
    }
}
