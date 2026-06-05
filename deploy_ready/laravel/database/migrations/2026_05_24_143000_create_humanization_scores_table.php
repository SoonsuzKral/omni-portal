<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('humanization_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->constrained()->cascadeOnDelete();
            $table->decimal('sentence_rhythm_score', 5, 2)->default(0);
            $table->decimal('structure_variation_score', 5, 2)->default(0);
            $table->decimal('paragraph_diversity_score', 5, 2)->default(0);
            $table->decimal('narrative_variation_score', 5, 2)->default(0);
            $table->decimal('tone_adaptation_score', 5, 2)->default(0);
            $table->decimal('overall_humanization_score', 5, 2)->default(0);
            $table->json('analysis_details')->nullable();
            $table->timestamps();

            $table->unique('content_node_id');
            $table->index('overall_humanization_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('humanization_scores');
    }
};
