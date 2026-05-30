<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->timestamp('gsc_first_discovered_at')->nullable()->after('crawl_priority_breakdown');
            $table->timestamp('gsc_first_crawled_at')->nullable()->after('gsc_first_discovered_at');
            $table->timestamp('gsc_first_indexed_at')->nullable()->after('gsc_first_crawled_at');
            $table->timestamp('gsc_last_impression_at')->nullable()->after('gsc_first_indexed_at');
            $table->timestamp('gsc_last_click_at')->nullable()->after('gsc_last_impression_at');
            $table->decimal('gsc_avg_position', 10, 2)->default(0)->after('gsc_last_click_at');
            $table->bigInteger('gsc_total_impressions')->default(0)->after('gsc_avg_position');
            $table->bigInteger('gsc_total_clicks')->default(0)->after('gsc_total_impressions');
            $table->string('gsc_index_status', 32)->nullable()->after('gsc_total_clicks');
            $table->bigInteger('gsc_index_latency_minutes')->nullable()->after('gsc_index_status');
            $table->timestamp('gsc_last_synced_at')->nullable()->after('gsc_index_latency_minutes');

            $table->index('gsc_index_status');
            $table->index('gsc_last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->dropColumn([
                'gsc_first_discovered_at',
                'gsc_first_crawled_at',
                'gsc_first_indexed_at',
                'gsc_last_impression_at',
                'gsc_last_click_at',
                'gsc_avg_position',
                'gsc_total_impressions',
                'gsc_total_clicks',
                'gsc_index_status',
                'gsc_index_latency_minutes',
                'gsc_last_synced_at',
            ]);
        });
    }
};
