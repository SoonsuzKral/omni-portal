<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_satisfaction_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->constrained()->cascadeOnDelete();
            $table->decimal('dwell_time_score', 5, 2)->default(0);
            $table->decimal('scroll_depth_score', 5, 2)->default(0);
            $table->decimal('interaction_rate_score', 5, 2)->default(0);
            $table->decimal('bounce_behavior_score', 5, 2)->default(0);
            $table->decimal('cta_engagement_score', 5, 2)->default(0);
            $table->decimal('navigation_depth_score', 5, 2)->default(0);
            $table->decimal('engagement_quality_score', 5, 2)->default(0);
            $table->decimal('satisfaction_score', 5, 2)->default(0);
            $table->json('raw_metrics')->nullable();
            $table->json('analysis_details')->nullable();
            $table->timestamps();

            $table->unique('content_node_id');
            $table->index('engagement_quality_score');
            $table->index('satisfaction_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_satisfaction_scores');
    }
};
