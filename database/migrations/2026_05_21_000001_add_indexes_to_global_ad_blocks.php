<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_ad_blocks', function (Blueprint $table) {
            $table->index('position');
            $table->index(['active', 'position', 'network_type']);
            $table->index(['active', 'position', 'is_global']);
        });
    }

    public function down(): void
    {
        Schema::table('global_ad_blocks', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropIndex(['active', 'position', 'network_type']);
            $table->dropIndex(['active', 'position', 'is_global']);
        });
    }
};
