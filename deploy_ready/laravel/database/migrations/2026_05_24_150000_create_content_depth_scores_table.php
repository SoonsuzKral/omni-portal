<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_depth_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->constrained()->cascadeOnDelete();
            $table->decimal('depth_score', 5, 2)->default(0);
            $table->decimal('richness_score', 5, 2)->default(0);
            $table->unsignedInteger('faq_count')->default(0);
            $table->unsignedInteger('semantic_expansion_count')->default(0);
            $table->unsignedInteger('related_entity_count')->default(0);
            $table->unsignedInteger('supporting_data_blocks')->default(0);
            $table->unsignedInteger('comparison_sections')->default(0);
            $table->json('enrichment_suggestions')->nullable();
            $table->json('analysis_details')->nullable();
            $table->timestamps();

            $table->unique('content_node_id');
            $table->index('depth_score');
            $table->index('richness_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_depth_scores');
    }
};
