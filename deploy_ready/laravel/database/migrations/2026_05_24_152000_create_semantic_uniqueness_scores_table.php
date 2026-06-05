<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semantic_uniqueness_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->constrained()->cascadeOnDelete();
            $table->decimal('semantic_similarity_score', 5, 2)->default(0);
            $table->decimal('sentence_entropy_score', 5, 2)->default(0);
            $table->decimal('lexical_diversity_score', 5, 2)->default(0);
            $table->decimal('template_saturation_score', 5, 2)->default(0);
            $table->decimal('embedding_uniqueness_score', 5, 2)->default(0);
            $table->decimal('heading_duplication_score', 5, 2)->default(0);
            $table->decimal('overall_uniqueness_score', 5, 2)->default(0);
            $table->json('similar_pages')->nullable();
            $table->json('analysis_details')->nullable();
            $table->timestamps();

            $table->unique('content_node_id');
            $table->index('overall_uniqueness_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semantic_uniqueness_scores');
    }
};
