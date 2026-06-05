<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url', 1024)->nullable();
            $table->string('anomaly_type', 64)->index();
            $table->string('severity', 16)->default('warning')->index();

            $table->decimal('current_value', 14, 4)->nullable();
            $table->decimal('previous_value', 14, 4)->nullable();
            $table->decimal('threshold', 14, 4)->nullable();
            $table->decimal('deviation', 14, 4)->nullable();

            $table->text('description')->nullable();
            $table->json('context')->nullable();

            $table->timestamp('detected_at')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->index(['anomaly_type', 'detected_at']);
            $table->index(['content_node_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_detections');
    }
};
