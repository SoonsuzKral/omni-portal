<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_authority_scores', function (Blueprint $table) {
            $table->id();
            $table->string('topicable_type'); // taxonomy, content_node, entity
            $table->unsignedBigInteger('topicable_id');
            $table->decimal('topic_coverage_score', 5, 2)->default(0);
            $table->decimal('entity_completeness_score', 5, 2)->default(0);
            $table->decimal('semantic_cluster_depth', 5, 2)->default(0);
            $table->decimal('supporting_content_ratio', 5, 2)->default(0);
            $table->decimal('internal_topical_links_score', 5, 2)->default(0);
            $table->decimal('authority_cluster_score', 5, 2)->default(0);
            $table->json('cluster_members')->nullable();
            $table->json('analysis_details')->nullable();
            $table->timestamps();

            $table->index(['topicable_type', 'topicable_id']);
            $table->index('topic_coverage_score');
            $table->index('authority_cluster_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_authority_scores');
    }
};
