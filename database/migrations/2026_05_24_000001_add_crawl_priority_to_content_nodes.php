<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->decimal('crawl_priority_score', 5, 2)->default(0)->index()->after('page_views');
            $table->json('crawl_priority_breakdown')->nullable()->after('crawl_priority_score');
        });
    }

    public function down(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->dropColumn(['crawl_priority_score', 'crawl_priority_breakdown']);
        });
    }
};
