<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('index_coverage', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique()->index();

            $table->bigInteger('submitted_urls')->default(0);
            $table->bigInteger('indexed_urls')->default(0);
            $table->decimal('coverage_ratio', 6, 4)->default(0);

            $table->decimal('avg_crawl_latency_seconds', 10, 2)->default(0);
            $table->decimal('indexing_velocity', 14, 2)->default(0);

            $table->bigInteger('sitemap_count')->default(0);
            $table->bigInteger('sitemap_indexed')->default(0);
            $table->decimal('sitemap_efficiency', 6, 4)->default(0);

            $table->bigInteger('crawl_requests')->default(0);
            $table->bigInteger('crawl_errors')->default(0);

            $table->json('breakdown')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('index_coverage');
    }
};
