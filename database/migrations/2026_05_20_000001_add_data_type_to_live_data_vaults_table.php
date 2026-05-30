<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_data_vaults', function (Blueprint $table) {
            $table->enum('data_type', ['string', 'json', 'array', 'integer', 'boolean'])
                ->default('string')
                ->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('live_data_vaults', function (Blueprint $table) {
            $table->dropColumn('data_type');
        });
    }
};