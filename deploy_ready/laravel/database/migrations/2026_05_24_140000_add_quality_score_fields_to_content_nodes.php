<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->decimal('uniqueness_score', 5, 2)->nullable()->after('crawl_priority_score');
            $table->decimal('eeat_score', 5, 2)->nullable()->after('uniqueness_score');
            $table->decimal('trust_score', 5, 2)->nullable()->after('eeat_score');
            $table->decimal('expertise_score', 5, 2)->nullable()->after('trust_score');
            $table->decimal('humanization_score', 5, 2)->nullable()->after('expertise_score');
            $table->decimal('ai_detection_risk_score', 5, 2)->nullable()->after('humanization_score');
            $table->decimal('topic_coverage_score', 5, 2)->nullable()->after('ai_detection_risk_score');
            $table->decimal('authority_cluster_score', 5, 2)->nullable()->after('topic_coverage_score');
            $table->decimal('engagement_quality_score', 5, 2)->nullable()->after('authority_cluster_score');
            $table->decimal('satisfaction_score', 5, 2)->nullable()->after('engagement_quality_score');
            $table->decimal('depth_score', 5, 2)->nullable()->after('satisfaction_score');
            $table->decimal('richness_score', 5, 2)->nullable()->after('depth_score');
            $table->decimal('spam_risk_score', 5, 2)->nullable()->after('richness_score');
            $table->decimal('doorway_risk_score', 5, 2)->nullable()->after('spam_risk_score');
            $table->json('quality_breakdown')->nullable()->after('doorway_risk_score');
            $table->timestamp('last_quality_analyzed_at')->nullable()->after('quality_breakdown');

            $table->index('uniqueness_score');
            $table->index('eeat_score');
            $table->index('spam_risk_score');
            $table->index('humanization_score');
            $table->index('topic_coverage_score');
            $table->index('satisfaction_score');
        });
    }

    public function down(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->dropColumn([
                'uniqueness_score',
                'eeat_score',
                'trust_score',
                'expertise_score',
                'humanization_score',
                'ai_detection_risk_score',
                'topic_coverage_score',
                'authority_cluster_score',
                'engagement_quality_score',
                'satisfaction_score',
                'depth_score',
                'richness_score',
                'spam_risk_score',
                'doorway_risk_score',
                'quality_breakdown',
                'last_quality_analyzed_at',
            ]);
        });
    }
};
