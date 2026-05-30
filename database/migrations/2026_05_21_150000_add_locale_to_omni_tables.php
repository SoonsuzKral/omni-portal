<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->string('locale', 10)->default('TR')->index();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->string('locale', 10)->default('TR')->index();
        });

        Schema::table('taxonomies', function (Blueprint $table) {
            $table->string('locale', 10)->default('TR')->index();
        });
    }

    public function down(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('taxonomies', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
