<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_ad_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('global_ad_blocks', 'taxonomy_id')) {
                $table->foreignId('taxonomy_id')->nullable()->after('cpm_note')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('global_ad_blocks', 'is_global')) {
                $table->boolean('is_global')->default(true)->after('taxonomy_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('global_ad_blocks', function (Blueprint $table) {
            $table->dropForeign(['taxonomy_id']);
            $table->dropColumn(['taxonomy_id', 'is_global']);
        });
    }
};