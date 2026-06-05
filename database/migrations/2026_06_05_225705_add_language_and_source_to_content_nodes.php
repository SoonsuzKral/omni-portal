<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            if (!Schema::hasColumn('content_nodes', 'language')) {
                $table->string('language', 5)->default('tr')->index()->after('locale');
            }
            if (!Schema::hasColumn('content_nodes', 'source')) {
                $table->string('source', 50)->default('api')->index()->after('language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->dropColumn(['language', 'source']);
        });
    }
};
