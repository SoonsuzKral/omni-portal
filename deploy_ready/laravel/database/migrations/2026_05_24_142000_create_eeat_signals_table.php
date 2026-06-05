<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eeat_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->constrained()->cascadeOnDelete();
            $table->string('signal_type'); // author_expertise, editorial_review, citation_quality, source_trust, factual_confidence, content_freshness
            $table->decimal('signal_score', 5, 2)->default(0);
            $table->text('signal_evidence')->nullable();
            $table->json('signal_details')->nullable();
            $table->timestamps();

            $table->index('content_node_id');
            $table->index('signal_type');
            $table->index('signal_score');
            $table->unique(['content_node_id', 'signal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eeat_signals');
    }
};
