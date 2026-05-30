<?php

return [
    'queue' => [
        'connection' => env('QUALITY_QUEUE_CONNECTION', 'default'),
        'analyze_queue' => env('QUALITY_ANALYZE_QUEUE', 'quality'),
        'graph_queue' => env('QUALITY_GRAPH_QUEUE', 'quality'),
        'spam_queue' => env('QUALITY_SPAM_QUEUE', 'quality'),
        'authority_queue' => env('QUALITY_AUTHORITY_QUEUE', 'quality'),
        'enrich_queue' => env('QUALITY_ENRICH_QUEUE', 'quality'),
    ],

    'chunk_size' => (int) env('QUALITY_CHUNK_SIZE', 100),

    'cache' => [
        'ttl_seconds' => (int) env('QUALITY_CACHE_TTL', 3600),
        'prefix' => 'quality:',
    ],

    'semantic' => [
        'similarity_threshold' => (float) env('SEMANTIC_SIMILARITY_THRESHOLD', 0.85),
        'embedding_dimensions' => (int) env('SEMANTIC_EMBEDDING_DIMS', 384),
        'min_entropy' => (float) env('SEMANTIC_MIN_ENTROPY', 0.4),
        'max_template_saturation' => (float) env('SEMANTIC_MAX_TEMPLATE_SATURATION', 0.6),
    ],

    'eeat' => [
        'freshness_days' => (int) env('EEAT_FRESHNESS_DAYS', 90),
        'min_citations' => (int) env('EEAT_MIN_CITATIONS', 2),
        'expertise_weight' => (float) env('EEAT_EXPERTISE_WEIGHT', 0.3),
        'trust_weight' => (float) env('EEAT_TRUST_WEIGHT', 0.35),
        'freshness_weight' => (float) env('EEAT_FRESHNESS_WEIGHT', 0.2),
        'citation_weight' => (float) env('EEAT_CITATION_WEIGHT', 0.15),
    ],

    'humanization' => [
        'min_rhythm_variation' => (float) env('HUMAN_MIN_RHYTHM', 0.3),
        'min_paragraph_diversity' => (float) env('HUMAN_MIN_PARAGRAPH', 0.4),
        'target_tone_variation' => (float) env('HUMAN_TONE_VARIATION', 0.5),
    ],

    'topic_authority' => [
        'min_entity_coverage' => (float) env('TOPIC_MIN_ENTITY_COVERAGE', 0.3),
        'min_supporting_ratio' => (float) env('TOPIC_MIN_SUPPORTING_RATIO', 0.2),
        'cluster_depth_min' => (int) env('TOPIC_CLUSTER_DEPTH_MIN', 2),
    ],

    'satisfaction' => [
        'target_dwell_seconds' => (int) env('SATISFACTION_TARGET_DWELL', 120),
        'min_scroll_depth' => (float) env('SATISFACTION_MIN_SCROLL', 0.5),
        'cta_engagement_weight' => (float) env('SATISFACTION_CTA_WEIGHT', 0.15),
    ],

    'depth' => [
        'min_faq_count' => (int) env('DEPTH_MIN_FAQ', 3),
        'min_word_count' => (int) env('DEPTH_MIN_WORDS', 800),
        'min_sections' => (int) env('DEPTH_MIN_SECTIONS', 4),
    ],

    'spam' => [
        'template_overuse_threshold' => (float) env('SPAM_TEMPLATE_OVERUSE', 0.7),
        'semantic_redundancy_threshold' => (float) env('SPAM_REDUNDANCY', 0.8),
        'doorway_keyword_overlap' => (float) env('SPAM_DOORWAY_OVERLAP', 0.6),
        'thin_content_words' => (int) env('SPAM_THIN_CONTENT_WORDS', 300),
        'over_optimization_density' => (float) env('SPAM_OPTIMIZATION_DENSITY', 0.05),
    ],

    'entity_graph' => [
        'min_relationship_strength' => (float) env('ENTITY_MIN_RELATIONSHIP', 0.1),
        'authority_propagation_depth' => (int) env('ENTITY_PROPAGATION_DEPTH', 3),
        'propagation_decay' => (float) env('ENTITY_PROPAGATION_DECAY', 0.5),
    ],
];
