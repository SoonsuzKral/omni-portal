<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anti_spam_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->constrained()->cascadeOnDelete();
            $table->decimal('scaled_content_abuse_score', 5, 2)->default(0);
            $table->decimal('template_overuse_score', 5, 2)->default(0);
            $table->decimal('semantic_redundancy_score', 5, 2)->default(0);
            $table->decimal('doorway_page_risk_score', 5, 2)->default(0);
            $table->decimal('thin_content_risk_score', 5, 2)->default(0);
            $table->decimal('over_optimization_score', 5, 2)->default(0);
            $table->decimal('overall_spam_risk_score', 5, 2)->default(0);
            $table->json('risk_factors')->nullable();
            $table->json('analysis_details')->nullable();
            $table->timestamps();

            $table->unique('content_node_id');
            $table->index('overall_spam_risk_score');
            $table->index('doorway_page_risk_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anti_spam_risk_scores');
    }
};
