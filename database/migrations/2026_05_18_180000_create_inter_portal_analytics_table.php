<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inter_portal_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id')->index();
            $table->string('portal_name', 100);
            $table->date('date')->index();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();

            $table->unique(['content_id', 'portal_name', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inter_portal_analytics');
    }
};