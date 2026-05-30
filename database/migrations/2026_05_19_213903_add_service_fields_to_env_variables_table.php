<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('env_variables', function (Blueprint $table) {
            $table->string('service_name')->nullable()->after('category');
            $table->boolean('is_service_enabled')->default(false)->after('service_name');
            $table->text('service_help')->nullable()->after('is_service_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('env_variables', function (Blueprint $table) {
            $table->dropColumn(['service_name', 'is_service_enabled', 'service_help']);
        });
    }
};