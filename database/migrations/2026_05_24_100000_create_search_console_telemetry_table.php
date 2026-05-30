<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_console_telemetry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_node_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url', 1024)->index();
            $table->timestamp('date')->index();

            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->decimal('ctr', 8, 6)->default(0);
            $table->decimal('avg_position', 10, 2)->default(0);

            $table->timestamp('first_discovered_at')->nullable();
            $table->timestamp('first_crawled_at')->nullable();
            $table->timestamp('first_indexed_at')->nullable();
            $table->timestamp('last_impression_at')->nullable();
            $table->timestamp('last_click_at')->nullable();

            $table->string('index_status', 32)->nullable()->index();
            $table->bigInteger('index_latency_minutes')->nullable();

            $table->string('source', 32)->default('gsc')->index();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->unique(['url', 'date']);
            $table->index('content_node_id');
            $table->index('index_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_console_telemetry');
    }
};
