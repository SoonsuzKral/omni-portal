<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_ad_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('global_ad_blocks', 'forbidden_locations')) {
                $table->json('forbidden_locations')->nullable()->after('position');
            }
            if (!Schema::hasColumn('global_ad_blocks', 'cpm_note')) {
                $table->string('cpm_note')->nullable()->after('script');
            }
        });
    }

    public function down(): void
    {
        Schema::table('global_ad_blocks', function (Blueprint $table) {
            $table->dropColumn(['forbidden_locations', 'cpm_note']);
        });
    }
};
