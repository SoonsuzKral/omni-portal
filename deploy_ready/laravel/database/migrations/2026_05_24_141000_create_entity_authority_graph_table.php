<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_authority_graph', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // city, service, tool, technology, company, industry, trend, person
            $table->string('entity_name');
            $table->string('entity_slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('entity_authority_score', 5, 2)->default(0);
            $table->decimal('topical_relevance_score', 5, 2)->default(0);
            $table->unsignedInteger('inbound_link_count')->default(0);
            $table->unsignedInteger('outbound_link_count')->default(0);
            $table->unsignedInteger('mention_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('entity_type');
            $table->index('entity_authority_score');
            $table->index('topical_relevance_score');
            $table->index(['entity_type', 'entity_slug']);
        });

        Schema::create('entity_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_entity_id')->constrained('entity_authority_graph')->cascadeOnDelete();
            $table->foreignId('target_entity_id')->constrained('entity_authority_graph')->cascadeOnDelete();
            $table->string('relationship_type'); // related_to, located_in, serves, uses, part_of, etc.
            $table->decimal('relationship_strength', 5, 2)->default(0);
            $table->text('context')->nullable();
            $table->timestamps();

            $table->unique(['source_entity_id', 'target_entity_id', 'relationship_type'], 'entity_rel_src_tgt_type_unique');
            $table->index('relationship_strength');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_relationships');
        Schema::dropIfExists('entity_authority_graph');
    }
};
